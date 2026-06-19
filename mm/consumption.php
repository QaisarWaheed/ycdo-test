<?php
require_once __DIR__ . '/includes/connect_report.php';
require_once __DIR__ . '/../includes/consumption_report_helpers.php';
require_once __DIR__ . '/../includes/summary_form_actions.php';

if (isset($_GET['generate']) || isset($_POST['generate'])) {
    $from_date = (string) ($_GET['from_date'] ?? $_POST['from_date'] ?? '');
    $to_date = (string) ($_GET['to_date'] ?? $_POST['to_date'] ?? '');
    $report_branch_id = (int) ($_GET['br_id'] ?? $_POST['br_id'] ?? $branch_id);

    if ($is_admin < 2 && $report_branch_id !== $branch_id) {
        $report_branch_id = $branch_id;
    }

    $validation = consumption_validate_request($from_date, $to_date);
    if (!$validation['ok']) {
        http_response_code(400);
        exit($validation['message']);
    }
    if ($report_branch_id < 1) {
        http_response_code(400);
        exit('Please select a branch.');
    }

    $print_url = 'print_consumption.php?' . http_build_query(array(
        'from' => $from_date,
        'to' => $to_date,
        'br_id' => $report_branch_id,
    ));
    fr_summary_print_redirect($print_url, 'consumption.php');
    exit;
}

$report_branch_id = $branch_id;
$from_date = date('Y-m-d', strtotime('-7 days'));
$to_date = date('Y-m-d');

$branch_options = array();
$branch_run = mysqli_query($con, "SELECT id, tag_name, address FROM branchs WHERE status = '1' ORDER BY address ASC");
if ($branch_run) {
    while ($b = mysqli_fetch_assoc($branch_run)) {
        $branch_options[] = $b;
    }
}
?>
<?php include 'includes/head.php'; ?>
<title>Category Consumption Report - <?php echo htmlspecialchars($company_trademark, ENT_QUOTES, 'UTF-8'); ?></title>
</head>
<body class="background_image">
<div class="row" style="margin: 0px;">
    <div class="col-md-12" style="text-align: center;background: lightgreen;">
        <label><h1>YCDO</h1></label>
    </div>
    <div class="col-md-3 background_whitesmoke" style="min-height: 450px">
        <?php include 'left_navigation.php'; ?>
    </div>
    <?php fr_summary_content_open(); ?>
    <div class="row">
        <div class="col-md-12 col-sm-12 col-xs-12">
            <h2 style="text-align: center;">Category Consumption Report</h2>
            <form method="get" class="container-fluid">
                <div class="row">
                    <div class="col-md-6 col-sm-6 col-xs-12">
                        <label>SELECT BRANCH</label>
                        <select name="br_id" class="form-control" required>
<?php if ($is_admin >= 2) { ?>
                            <?php foreach ($branch_options as $b) {
                                $sel = ((int) $b['id'] === $report_branch_id) ? ' selected' : '';
                                $label = $b['tag_name'] !== '' ? $b['tag_name'] : $b['address'];
                                echo '<option value="' . (int) $b['id'] . '"' . $sel . '>'
                                    . htmlspecialchars($label, ENT_QUOTES, 'UTF-8') . '</option>';
                            } ?>
<?php } else { ?>
                            <option value="<?php echo (int) $branch_id; ?>"><?php echo htmlspecialchars($branch_address, ENT_QUOTES, 'UTF-8'); ?></option>
<?php } ?>
                        </select>
                    </div>
                    <div class="col-md-6 col-sm-6 col-xs-12">
                        <label>FROM DATE</label>
                        <input type="date" name="from_date" class="form-control" required value="<?php echo htmlspecialchars(ycdo_date_input_value($from_date), ENT_QUOTES, 'UTF-8'); ?>" />
                    </div>
                    <div class="col-md-6 col-sm-6 col-xs-12">
                        <label>TO DATE</label>
                        <input type="date" name="to_date" class="form-control" required value="<?php echo htmlspecialchars(ycdo_date_input_value($to_date), ENT_QUOTES, 'UTF-8'); ?>" />
                    </div>
                    <?php fr_summary_form_actions('generate', 'PRINT REPORT'); ?>
                </div>
            </form>
        </div>
    </div>
    </div>
</div>
</body>
</html>
<script type="text/javascript" src="js/bootstrap.min.js"></script>
