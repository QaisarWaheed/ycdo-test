<?php
include 'config.php';

$branch_id = isset($_GET['branch_id']) ? (int)$_GET['branch_id'] : 0;
$month = isset($_GET['month']) ? mysqli_real_escape_string($con, $_GET['month']) : '2026-03';

// Capture the user-selected times
$start_time = isset($_GET['start_time']) ? mysqli_real_escape_string($con, $_GET['start_time']) : '00:00';
$end_time = isset($_GET['end_time']) ? mysqli_real_escape_string($con, $_GET['end_time']) : '06:00';

$first_day = $month . "-01";
$last_day = date("Y-m-t", strtotime($first_day));

$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 25; 
$offset = ($page - 1) * $limit;
$sql = "SELECT 
            u.u_name AS doctor_name,
            b.tag_name AS branch_name, 
            t_stats.doctor_id,
            t_stats.collection,
            t_stats.opd,
            i_stats.test_tokens,
            i_stats.tests,
            i_stats.procedures,
            i_stats.consultants,
            i_stats.dentals,
            i_stats.skins,
            i_stats.eyes,
            i_stats.physiotherapies,
            i_stats.minir_procedures,
            i_stats.svds,
            i_stats.dncs,
            i_stats.usgs,
            i_stats.admissions,
            i_stats.gyneas,
            i_stats.emergency,
            i_stats.ecgs,
            ROUND(IFNULL((i_stats.test_tokens / NULLIF(t_stats.opd + i_stats.consultants, 0)) * 100, 0), 2) AS diagnostic_percentage,
            ROUND(IFNULL((i_stats.admissions / NULLIF(t_stats.opd + i_stats.consultants, 0)) * 100, 0), 2) AS admission_percentage
        FROM (
            /* Subquery 1: Tokens & Collection */
            SELECT doctor_id, 
                   SUM(cash) AS collection, 
                   COUNT(CASE WHEN tokan_type_id < 100 THEN id END) AS opd 
            FROM tokans 
            WHERE (created >= '$first_day 00:00:00' AND created <= '$last_day 23:59:59')
            AND TIME(created) BETWEEN '$start_time' AND '$end_time'
            AND branch_id = $branch_id 
            GROUP BY doctor_id
        ) AS t_stats
        LEFT JOIN (
            /* Subquery 2: Item Categories */
            SELECT doctor_id,
                SUM(CASE WHEN category_id = 2 THEN sale_price END) AS tests,
                COUNT(DISTINCT CASE WHEN category_id = 2 THEN tokan_no END) AS test_tokens,
                COUNT(CASE WHEN category_id = 3 THEN 1 END) AS procedures,
                COUNT(CASE WHEN category_id = 29 THEN 1 END) AS consultants,
                COUNT(CASE WHEN category_id = 31 THEN 1 END) AS dentals,
                COUNT(CASE WHEN category_id = 32 THEN 1 END) AS skins,
                COUNT(CASE WHEN category_id = 33 THEN 1 END) AS eyes,
                COUNT(CASE WHEN category_id = 34 THEN 1 END) AS physiotherapies,
                COUNT(CASE WHEN category_id = 36 THEN 1 END) AS minir_procedures,
                COUNT(CASE WHEN category_id = 37 THEN 1 END) AS svds,
                COUNT(CASE WHEN category_id = 38 THEN 1 END) AS dncs,
                COUNT(CASE WHEN category_id = 39 THEN 1 END) AS usgs,
                COUNT(CASE WHEN category_id = 40 THEN 1 END) AS admissions,
                COUNT(CASE WHEN category_id = 41 THEN 1 END) AS gyneas,
                COUNT(CASE WHEN category_id = 42 THEN 1 END) AS emergency,
                COUNT(CASE WHEN category_id = 44 THEN 1 END) AS ecgs
            FROM item_by_doctor
            WHERE (created >= '$first_day 00:00:00' AND created <= '$last_day 23:59:59')
            AND TIME(created) BETWEEN '$start_time' AND '$end_time'
            AND branch_id = $branch_id
            GROUP BY doctor_id
        ) AS i_stats ON t_stats.doctor_id = i_stats.doctor_id
        INNER JOIN users u ON t_stats.doctor_id = u.id
        LEFT JOIN branchs b ON u.branch_id = b.id
        ORDER BY t_stats.doctor_id ASC
        LIMIT $offset, $limit";

$run = mysqli_query($con, $sql);

$data = [];
if ($run) {
    while ($row = mysqli_fetch_assoc($run)) {
        foreach ($row as $key => $value) {
            if ($value === null) $row[$key] = "0";
        }
        $data[] = $row;
    }
}

header('Content-Type: application/json');
echo json_encode($data);
?>