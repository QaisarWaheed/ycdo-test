<?php
ob_start();
header('Content-Type: application/json');

include 'includes/connect.php';
error_reporting(0);

$date_from = $_GET['date_from'] ?? date('Y-m-01');
$date_to   = $_GET['date_to'] ?? date('Y-m-d');
$branch    = $_GET['branch'] ?? '';
$status    = $_GET['status'] ?? '';

$where = "WHERE DATE(abf.created) BETWEEN ? AND ?";
$params = [$date_from, $date_to];
$types = "ss";

if ($branch !== '') {
    $where .= " AND abf.branch_id = ?";
    $params[] = $branch;
    $types .= "i";
}

$sql = "
    SELECT
        abf.created AS audit_date,
        abf.id AS audit_id,
        abf.branch_id,
        abf.audit_officer_id AS audit_by,

        (
            SELECT COUNT(*) 
            FROM audit_lab_detail ald 
            WHERE ald.audit_lab_form_id = abf.id
        ) +
        (
            SELECT COUNT(*) 
            FROM audit_store_detail asd 
            WHERE asd.audit_store_form_id = abf.id
        ) AS total_items,

        (
            SELECT COUNT(*) 
            FROM audit_lab_detail ald 
            WHERE ald.audit_lab_form_id = abf.id
            AND ald.audit_lab_detail_status = 1
        ) +
        (
            SELECT COUNT(*) 
            FROM audit_store_detail asd 
            WHERE asd.audit_store_form_id = abf.id
            AND asd.audit_store_detail_status = 1
        ) AS total_updated_items

    FROM audit_branch_form abf
    $where
    ORDER BY abf.created DESC
";

$stmt = mysqli_prepare($con, $sql);

if (!$stmt) {
    ob_clean();
    echo json_encode([
        "error" => true,
        "message" => mysqli_error($con)
    ]);
    exit;
}

mysqli_stmt_bind_param($stmt, $types, ...$params);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);
$data = [];

while ($row = mysqli_fetch_assoc($result)) {
    $total_items = (int)$row['total_items'];
    $total_updated = (int)$row['total_updated_items'];

    if ($total_updated === 0) {
        $calculated_status = 1;
    } elseif ($total_updated < $total_items) {
        $calculated_status = 2;
    } else {
        $calculated_status = 3;
    }

    if ($status !== '' && (int)$status !== $calculated_status) {
        continue;
    }

    $data[] = [
        'audit_date' => $row['audit_date'],
        'audit_id' => $row['audit_id'],
        'branch_id' => $row['branch_id'],
        'total_items' => $total_items,
        'total_updated_items' => $total_updated,
        'audit_by' => $row['audit_by']
    ];
}

ob_clean();
echo json_encode($data);
exit;