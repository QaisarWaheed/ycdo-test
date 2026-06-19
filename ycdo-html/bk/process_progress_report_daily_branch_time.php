<?php 
include 'includes/connect.php';

// Prevent PHP errors from corrupting JSON output
error_reporting(0);
ini_set('display_errors', 0);

// Capture Branch and Pagination
$branch_id = isset($_GET['branch_id']) ? (int)$_GET['branch_id'] : 0;

// Capture the user-selected Dates
$from_date = isset($_GET['from_date']) ? mysqli_real_escape_string($con, $_GET['from_date']) : date('Y-m-01');
$to_date   = isset($_GET['to_date']) ? mysqli_real_escape_string($con, $_GET['to_date']) : date('Y-m-d');

// Capture the user-selected times
$start_time = isset($_GET['start_time']) ? mysqli_real_escape_string($con, $_GET['start_time']) : '00:00';
$end_time   = isset($_GET['end_time']) ? mysqli_real_escape_string($con, $_GET['end_time']) : '06:00';

$sql = "SELECT 
            u.u_name AS doctor_name,
            b.tag_name AS branch_name, 
            all_docs.doctor_id,
            IFNULL(t_stats.collection, 0) as collection,
            IFNULL(t_stats.opd, 0) as opd,
            IFNULL(i_stats.test_tokens, 0) as test_tokens,
            IFNULL(i_stats.tests, 0) as tests,
            IFNULL(i_stats.procedures, 0) as procedures,
            IFNULL(i_stats.consultants, 0) as consultants,
            IFNULL(i_stats.dentals, 0) as dentals,
            IFNULL(i_stats.skins, 0) as skins,
            IFNULL(i_stats.eyes, 0) as eyes,
            IFNULL(i_stats.svds, 0) as svds,
            IFNULL(i_stats.dncs, 0) as dncs,
            IFNULL(i_stats.usgs, 0) as usgs,
            IFNULL(i_stats.admissions, 0) as admissions,
            IFNULL(i_stats.gyneas, 0) as gyneas,
            IFNULL(i_stats.emergency, 0) as emergency,
            IFNULL(i_stats.ecgs, 0) as ecgs,
            ROUND(IFNULL((i_stats.test_tokens / NULLIF(IFNULL(t_stats.opd,0) + IFNULL(i_stats.consultants,0), 0)) * 100, 0), 2) AS diagnostic_percentage,
            ROUND(IFNULL((i_stats.admissions / NULLIF(IFNULL(t_stats.opd,0) + IFNULL(i_stats.consultants,0), 0)) * 100, 0), 2) AS admission_percentage
        FROM (
            /* Get all unique doctors involved in either table */
            SELECT doctor_id FROM tokans WHERE branch_id = $branch_id AND (DATE(created) BETWEEN '$from_date' AND '$to_date')
            UNION
            SELECT doctor_id FROM item_by_doctor WHERE branch_id = $branch_id AND (DATE(created) BETWEEN '$from_date' AND '$to_date')
        ) AS all_docs
        LEFT JOIN (
            /* Subquery 1: Tokens & Collection */
            SELECT doctor_id, 
                   SUM(cash) AS collection, 
                   SUM(CASE WHEN tokan_type_id < 100 THEN 1 ELSE 0 END) AS opd 
            FROM tokans 
            WHERE (DATE(created) BETWEEN '$from_date' AND '$to_date')
            AND (TIME(created) BETWEEN '$start_time' AND '$end_time')
            AND branch_id = $branch_id 
            GROUP BY doctor_id
        ) AS t_stats ON all_docs.doctor_id = t_stats.doctor_id
        LEFT JOIN (
            /* Subquery 2: Item Categories */
            SELECT doctor_id,
                SUM(CASE WHEN category_id = 2 THEN sale_price ELSE 0 END) AS tests,
                COUNT(DISTINCT CASE WHEN category_id = 2 THEN tokan_no END) AS test_tokens,
                SUM(CASE WHEN category_id = 3 THEN 1 ELSE 0 END) AS procedures,
                SUM(CASE WHEN category_id = 29 THEN 1 ELSE 0 END) AS consultants,
                SUM(CASE WHEN category_id = 31 THEN 1 ELSE 0 END) AS dentals,
                SUM(CASE WHEN category_id = 32 THEN 1 ELSE 0 END) AS skins,
                SUM(CASE WHEN category_id = 33 THEN 1 ELSE 0 END) AS eyes,
                SUM(CASE WHEN category_id = 37 THEN 1 ELSE 0 END) AS svds,
                SUM(CASE WHEN category_id = 38 THEN 1 ELSE 0 END) AS dncs,
                SUM(CASE WHEN category_id = 39 THEN 1 ELSE 0 END) AS usgs,
                SUM(CASE WHEN category_id = 40 THEN 1 ELSE 0 END) AS admissions,
                SUM(CASE WHEN category_id = 41 THEN 1 ELSE 0 END) AS gyneas,
                SUM(CASE WHEN category_id = 42 THEN 1 ELSE 0 END) AS emergency,
                SUM(CASE WHEN category_id = 44 THEN 1 ELSE 0 END) AS ecgs
            FROM item_by_doctor
            WHERE (DATE(created) BETWEEN '$from_date' AND '$to_date')
            AND (TIME(created) BETWEEN '$start_time' AND '$end_time')
            AND branch_id = $branch_id
            GROUP BY doctor_id
        ) AS i_stats ON all_docs.doctor_id = i_stats.doctor_id
        INNER JOIN users u ON all_docs.doctor_id = u.id
        LEFT JOIN branchs b ON u.branch_id = b.id
        ORDER BY all_docs.doctor_id ASC";

$run = mysqli_query($con, $sql);

$data = [];
if ($run) {
    while ($row = mysqli_fetch_assoc($run)) {
        $data[] = $row;
    }
}

header('Content-Type: application/json');
echo json_encode($data);
?>