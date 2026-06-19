<?php
include 'includes/connect.php';

if ((int) $is_admin !== 2) {
    header('Location: logout.php');
    exit;
}

$flash = '';
$flash_type = 'success';
$search = trim((string) ($_GET['q'] ?? ''));
$search_esc = mysqli_real_escape_string($con, $search);

/**
 * Full column metadata for procedure_medicine_limits.
 *
 * @return array<int, array<string, mixed>>
 */
function pmla_table_schema($con)
{
    static $schema = null;
    if ($schema !== null) {
        return $schema;
    }
    $schema = array();
    $run = mysqli_query($con, 'SHOW COLUMNS FROM `procedure_medicine_limits`');
    if ($run) {
        while ($row = mysqli_fetch_assoc($run)) {
            $schema[] = $row;
        }
    }
    return $schema;
}

/**
 * Detect columns on procedure_medicine_limits (schema varies).
 *
 * @return array<int, string>
 */
function pmla_table_columns($con)
{
    $cols = array();
    foreach (pmla_table_schema($con) as $row) {
        $cols[] = (string) $row['Field'];
    }
    return $cols;
}

function pmla_default_for_column($field, $type, $now, $user_id, $catalog_item_id, $medicine_limit)
{
    $field = (string) $field;
    $type = strtolower((string) $type);

    if ($field === 'item_id') {
        return (string) (int) $catalog_item_id;
    }
    if ($field === 'medicine_limit') {
        return (string) (float) $medicine_limit;
    }
    if ($field === 'status') {
        return '1';
    }
    if ($field === 'user_id' || $field === 'created_by' || $field === 'updated_by') {
        return (string) max(0, (int) $user_id);
    }
    if (strpos($type, 'int') !== false || strpos($type, 'decimal') !== false || strpos($type, 'float') !== false || strpos($type, 'double') !== false) {
        return '0';
    }
    if (strpos($type, 'datetime') !== false || strpos($type, 'timestamp') !== false) {
        return (string) $now;
    }
    if (strpos($type, 'date') !== false) {
        return substr((string) $now, 0, 10);
    }

    return '';
}

/**
 * Save medicine limit keyed by catalog items.id (insert if missing, update if exists).
 *
 * @return array{ok:bool, error:string, action:string}
 */
function pmla_save_medicine_limit($con, $catalog_item_id, $medicine_limit, $user_id)
{
    $catalog_item_id = (int) $catalog_item_id;
    $medicine_limit = (float) $medicine_limit;
    $user_id = (int) $user_id;

    if ($catalog_item_id < 1) {
        return array('ok' => false, 'error' => 'Invalid procedure item id.', 'action' => '');
    }
    if ($medicine_limit < 0) {
        return array('ok' => false, 'error' => 'Medicine limit cannot be negative.', 'action' => '');
    }

    $schema = pmla_table_schema($con);
    if (empty($schema)) {
        return array('ok' => false, 'error' => 'procedure_medicine_limits table was not found.', 'action' => '');
    }

    $field_names = array();
    foreach ($schema as $row) {
        $field_names[] = (string) $row['Field'];
    }
    if (!in_array('item_id', $field_names, true) || !in_array('medicine_limit', $field_names, true)) {
        return array('ok' => false, 'error' => 'procedure_medicine_limits is missing item_id or medicine_limit column.', 'action' => '');
    }

    $existing = mysqli_query(
        $con,
        "SELECT item_id FROM `procedure_medicine_limits` WHERE item_id = '$catalog_item_id' LIMIT 1"
    );
    $is_update = ($existing && mysqli_num_rows($existing) > 0);

    $now = date('Y-m-d H:i:s');
    $limit_sql = mysqli_real_escape_string($con, (string) $medicine_limit);

    if ($is_update) {
        $set = "`medicine_limit` = '$limit_sql'";
        if (in_array('updated_at', $field_names, true)) {
            $set .= ", `updated_at` = '" . mysqli_real_escape_string($con, $now) . "'";
        }
        if (in_array('updated_by', $field_names, true) && $user_id > 0) {
            $set .= ", `updated_by` = '$user_id'";
        }
        if (!mysqli_query($con, "UPDATE `procedure_medicine_limits` SET $set WHERE item_id = '$catalog_item_id'")) {
            $err = mysqli_error($con);
            return array('ok' => false, 'error' => $err !== '' ? $err : 'Update failed.', 'action' => '');
        }
        return array('ok' => true, 'error' => '', 'action' => 'updated');
    }

    $columns = array(
        'item_id' => (string) $catalog_item_id,
        'medicine_limit' => (string) $medicine_limit,
    );
    if (in_array('created_at', $field_names, true)) {
        $columns['created_at'] = $now;
    }
    if (in_array('updated_at', $field_names, true)) {
        $columns['updated_at'] = $now;
    }
    if (in_array('created_by', $field_names, true) && $user_id > 0) {
        $columns['created_by'] = (string) $user_id;
    }
    if (in_array('updated_by', $field_names, true) && $user_id > 0) {
        $columns['updated_by'] = (string) $user_id;
    }

    foreach ($schema as $schema_row) {
        $field = (string) $schema_row['Field'];
        if ($field === 'id') {
            continue;
        }

        $needs_value = ($schema_row['Null'] === 'NO')
            && ($schema_row['Default'] === null)
            && (stripos((string) $schema_row['Extra'], 'auto_increment') === false);

        if ($needs_value && !isset($columns[$field])) {
            $columns[$field] = pmla_default_for_column(
                $field,
                $schema_row['Type'],
                $now,
                $user_id,
                $catalog_item_id,
                $medicine_limit
            );
        }
    }

    $insert_columns = array();
    $insert_values = array();
    foreach ($columns as $name => $value) {
        if (!in_array($name, $field_names, true)) {
            continue;
        }
        $insert_columns[] = '`' . $name . '`';
        $insert_values[] = "'" . mysqli_real_escape_string($con, (string) $value) . "'";
    }

    if (empty($insert_columns)) {
        return array('ok' => false, 'error' => 'Nothing to insert into procedure_medicine_limits.', 'action' => '');
    }

    $sql = 'INSERT INTO `procedure_medicine_limits` (' . implode(', ', $insert_columns) . ') VALUES (' . implode(', ', $insert_values) . ')';
    if (!mysqli_query($con, $sql)) {
        $err = mysqli_error($con);
        return array('ok' => false, 'error' => $err !== '' ? $err : 'Insert failed.', 'action' => '');
    }

    return array('ok' => true, 'error' => '', 'action' => 'added');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_limit'])) {
    $catalog_item_id = (int) ($_POST['catalog_item_id'] ?? 0);
    $medicine_limit = (float) ($_POST['medicine_limit'] ?? 0);
    $post_search = trim((string) ($_POST['q'] ?? ''));
    $save_result = pmla_save_medicine_limit($con, $catalog_item_id, $medicine_limit, (int) $user_id);

    $redirect = 'procedure_medicine_limits_admin.php?';
    if ($post_search !== '') {
        $redirect .= 'q=' . rawurlencode($post_search) . '&';
    }
    if ($save_result['ok']) {
        $redirect .= 'msg=' . ($save_result['action'] === 'added' ? 'added' : 'saved');
    } else {
        $redirect .= 'msg=error&err=' . rawurlencode($save_result['error']);
    }
    header('Location: ' . $redirect);
    exit;
}

if (isset($_GET['msg']) && $_GET['msg'] === 'added') {
    $flash = 'New medicine limit added to procedure_medicine_limits.';
    $flash_type = 'success';
} elseif (isset($_GET['msg']) && $_GET['msg'] === 'saved') {
    $flash = 'Medicine limit updated.';
    $flash_type = 'success';
} elseif (isset($_GET['msg']) && $_GET['msg'] === 'error' && !empty($_GET['err'])) {
    $flash = (string) $_GET['err'];
    $flash_type = 'danger';
}

$procedure_filter = "
    i.status = '1'
    AND (
        i.category_id IN (3, 31, 32, 37, 38, 41)
        OR UPPER(c.name) LIKE '%PROCEDURE%'
        OR UPPER(c.name) LIKE '%OPERATION%'
        OR UPPER(c.name) LIKE '%SURGERY%'
    )";

$search_sql = '';
if ($search !== '') {
    $search_sql = " AND i.name LIKE '%$search_esc%' ";
}

$rows = array();
$list_sql = "
    SELECT
        i.id AS catalog_item_id,
        i.name AS item_name,
        c.name AS category_name,
        (
            SELECT pml.medicine_limit
            FROM procedure_medicine_limits pml
            WHERE pml.item_id = i.id
            LIMIT 1
        ) AS medicine_limit,
        (
            SELECT irb.id
            FROM item_register_to_branches irb
            WHERE irb.item_id = i.id
              AND irb.branch_id = '" . (int) $branch_id . "'
              AND irb.status = '1'
            LIMIT 1
        ) AS branch_register_id
    FROM items i
    INNER JOIN categories c ON i.category_id = c.id
    WHERE $procedure_filter
    $search_sql
    ORDER BY i.name ASC
    LIMIT 500";

$list_run = mysqli_query($con, $list_sql);
if ($list_run) {
    while ($row = mysqli_fetch_assoc($list_run)) {
        $rows[] = $row;
    }
}

$table_exists = count(pmla_table_columns($con)) > 0;

include 'includes/head.php';
?>
<title>Procedure Medicine Limits - <?php echo htmlspecialchars($company_trademark, ENT_QUOTES, 'UTF-8'); ?></title>
</head>
<body class="background_image_ycdo">
<div class="col-md-12" style="text-align: center;background: lightgreen;">
    <label><h1><?php echo htmlspecialchars($company_name, ENT_QUOTES, 'UTF-8'); ?></h1></label>
</div>

<div class="container-fluid py-3">
    <div class="row mb-3">
        <div class="col-md-8">
            <h3 class="mb-1">Procedure Medicine Limits</h3>
            <p class="text-muted mb-0 small">
                Search by procedure name (e.g. C-Section). Limits are saved against catalog item id
                (<code>items.id</code>) — the same id used when checking limits on procedure tokens.
            </p>
        </div>
        <div class="col-md-4 text-md-right">
            <a class="btn btn-outline-secondary btn-sm" href="dashboard.php">Back to dashboard</a>
        </div>
    </div>

<?php if (!$table_exists) { ?>
    <div class="alert alert-danger">Table <code>procedure_medicine_limits</code> was not found on this database.</div>
<?php } ?>

<?php if ($flash !== '') { ?>
    <div class="alert alert-<?php echo htmlspecialchars($flash_type, ENT_QUOTES, 'UTF-8'); ?>">
        <?php echo htmlspecialchars($flash, ENT_QUOTES, 'UTF-8'); ?>
    </div>
<?php } ?>

    <form method="get" class="card card-body mb-3">
        <div class="form-row align-items-end">
            <div class="col-md-9">
                <label for="q">Search procedure name</label>
                <input
                    type="text"
                    class="form-control"
                    id="q"
                    name="q"
                    value="<?php echo htmlspecialchars($search, ENT_QUOTES, 'UTF-8'); ?>"
                    placeholder="e.g. C-Section, D&C, Hernia"
                    autofocus
                />
            </div>
            <div class="col-md-3 mt-2 mt-md-0">
                <button type="submit" class="btn btn-primary btn-block">Search</button>
            </div>
        </div>
        <small class="text-muted d-block mt-2">
            Leave empty and search to list all mapped procedures. Partial matches are supported.
        </small>
    </form>

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span>Matching procedures</span>
            <span class="badge badge-secondary"><?php echo count($rows); ?> row(s)</span>
        </div>
        <div class="table-responsive">
            <table class="table table-hover table-bordered mb-0">
                <thead class="thead-light">
                    <tr>
                        <th>S #</th>
                        <th>Procedure name</th>
                        <th>Category</th>
                        <th>Catalog item id</th>
                        <th>Branch register id</th>
                        <th>Current limit</th>
                        <th>Update</th>
                    </tr>
                </thead>
                <tbody>
<?php
if (count($rows) === 0) {
    echo '<tr><td colspan="7" class="text-center text-muted py-4">';
    echo $search !== ''
        ? 'No procedures found matching &ldquo;' . htmlspecialchars($search, ENT_QUOTES, 'UTF-8') . '&rdquo;.'
        : 'No procedures found. Try a search term such as C-Section.';
    echo '</td></tr>';
} else {
    $s = 0;
    foreach ($rows as $row) {
        $s++;
        $catalog_id = (int) $row['catalog_item_id'];
        $branch_reg = (int) ($row['branch_register_id'] ?? 0);
        $current_limit = (float) ($row['medicine_limit'] ?? 0);
        $item_name = htmlspecialchars((string) $row['item_name'], ENT_QUOTES, 'UTF-8');
        $category_name = htmlspecialchars((string) $row['category_name'], ENT_QUOTES, 'UTF-8');
        echo '<tr>';
        echo '<td>' . $s . '</td>';
        echo '<td>' . $item_name . '</td>';
        echo '<td>' . $category_name . '</td>';
        echo '<td><code>' . $catalog_id . '</code></td>';
        echo '<td>' . ($branch_reg > 0 ? (int) $branch_reg : '<span class="text-muted">—</span>') . '</td>';
        echo '<td>' . ($current_limit > 0 ? (int) round($current_limit) : '<span class="text-muted">Not set</span>') . '</td>';
        echo '<td>';
        echo '<form method="post" class="form-inline">';
        echo '<input type="hidden" name="catalog_item_id" value="' . $catalog_id . '" />';
        echo '<input type="hidden" name="q" value="' . htmlspecialchars($search, ENT_QUOTES, 'UTF-8') . '" />';
        echo '<input type="number" step="1" min="0" name="medicine_limit" class="form-control form-control-sm mr-2" style="width:120px;" value="' . ($current_limit > 0 ? (int) round($current_limit) : '') . '" placeholder="Amount" required />';
        $btn_label = ($current_limit > 0) ? 'Update limit' : 'Add limit';
        echo '<button type="submit" name="save_limit" value="1" class="btn btn-sm btn-success">' . $btn_label . '</button>';
        echo '</form>';
        echo '</td>';
        echo '</tr>';
    }
}
?>
                </tbody>
            </table>
        </div>
    </div>

    <p class="text-muted small mt-3 mb-0">
        Branch register id is shown for reference only (your branch: <?php echo (int) $branch_id; ?>).
        Saving always uses catalog item id so the same limit applies on every branch for that procedure type.
    </p>
</div>

<script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
</body>
</html>
<?php mysqli_close($con); ?>
