<?php
include 'config.php';

$date1 = isset($_GET['start_date']) ? mysqli_real_escape_string($con, $_GET['start_date']) : date('Y-m-d');
$date2 = isset($_GET['end_date'])   ? mysqli_real_escape_string($con, $_GET['end_date'])   : date('Y-m-d');

$sql = "SELECT 
    b.id AS branch_id,
    b.tag_name AS branch_name,
    IFNULL(d1.opd, 0) AS d1_opd,
    IFNULL(d1.diagnoses, 0) AS d1_diagnoses, -- Added
    IFNULL(d1.lab_count, 0) AS d1_lab, 
    IFNULL(d1.lab_amount, 0) AS d1_lab_amount,
    IFNULL(d1.op, 0) AS d1_op,
    IFNULL(d1.gyn, 0) AS d1_gyn,
    IFNULL(d1.xray, 0) AS d1_xray,
    IFNULL(d1.ecg, 0) AS d1_ecg,
    IFNULL(d1.coll, 0) AS d1_coll,
    
    IFNULL(d2.opd, 0) AS d2_opd,
    IFNULL(d2.diagnoses, 0) AS d2_diagnoses, -- Added
    IFNULL(d2.lab_count, 0) AS d2_lab,
    IFNULL(d2.lab_amount, 0) AS d2_lab_amount,
    IFNULL(d2.op, 0) AS d2_op,
    IFNULL(d2.gyn, 0) AS d2_gyn,
    IFNULL(d2.xray, 0) AS d2_xray,
    IFNULL(d2.ecg, 0) AS d2_ecg,
    IFNULL(d2.coll, 0) AS d2_coll
FROM branchs b
LEFT JOIN (
    SELECT t.branch_id,
        COUNT(DISTINCT CASE WHEN t.tokan_type_id < 100 THEN t.id END) AS opd,
        -- Change '5' to your real Diagnoses category_id
        COUNT(DISTINCT CASE WHEN i.category_id = 2 THEN t.id END) AS diagnoses, 
        SUM(t.cash / (SELECT COUNT(*) FROM item_by_doctor i2 WHERE i2.tokan_no = t.id)) as coll_fix,
        (SELECT SUM(cash) FROM tokans t3 WHERE t3.branch_id = t.branch_id AND t3.created >= '$date1 00:00:00' AND t3.created <= '$date1 23:59:59') as coll,
        COUNT(CASE WHEN i.category_id = 2 THEN 1 END) AS lab_count,
        IFNULL(SUM(CASE WHEN i.category_id = 2 THEN i.sale_price END), 0) AS lab_amount,
        COUNT(CASE WHEN i.category_id IN (3,36) THEN 1 END) AS op,
        COUNT(CASE WHEN i.category_id = 41 THEN 1 END) AS gyn,
        COUNT(CASE WHEN i.category_id = 45 THEN 1 END) AS xray,
        COUNT(CASE WHEN i.category_id = 44 THEN 1 END) AS ecg
    FROM tokans t
    LEFT JOIN item_by_doctor i ON t.id = i.tokan_no
    WHERE t.created >= '$date1 00:00:00' AND t.created <= '$date1 23:59:59'
    GROUP BY t.branch_id
) d1 ON b.id = d1.branch_id
LEFT JOIN (
    SELECT t.branch_id,
        COUNT(DISTINCT CASE WHEN t.tokan_type_id < 100 THEN t.id END) AS opd,
        -- Change '5' to your real Diagnoses category_id
        COUNT(DISTINCT CASE WHEN i.category_id = 2 THEN t.id END) AS diagnoses,
        (SELECT SUM(cash) FROM tokans t3 WHERE t3.branch_id = t.branch_id AND t3.created >= '$date2 00:00:00' AND t3.created <= '$date2 23:59:59') as coll,
        COUNT(CASE WHEN i.category_id = 2 THEN 1 END) AS lab_count,
        IFNULL(SUM(CASE WHEN i.category_id = 2 THEN i.sale_price END), 0) AS lab_amount,
        COUNT(CASE WHEN i.category_id IN (3,36) THEN 1 END) AS op,
        COUNT(CASE WHEN i.category_id = 41 THEN 1 END) AS gyn,
        COUNT(CASE WHEN i.category_id = 45 THEN 1 END) AS xray,
        COUNT(CASE WHEN i.category_id = 44 THEN 1 END) AS ecg
    FROM tokans t
    LEFT JOIN item_by_doctor i ON t.id = i.tokan_no
    WHERE t.created >= '$date2 00:00:00' AND t.created <= '$date2 23:59:59'
    GROUP BY t.branch_id
) d2 ON b.id = d2.branch_id
ORDER BY b.id ASC ";

$run = mysqli_query($con, $sql);
$data = [];
while ($row = mysqli_fetch_assoc($run)) {
    $data[] = $row;
}

header('Content-Type: application/json');
echo json_encode(["status" => "success", "data" => $data]);