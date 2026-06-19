<?php
session_start();
 include 'includes/connect.php';
// include 'includes/config.php'; 
// Sanitize inputs
$branch_id = isset($_SESSION['branch_id']) ? (int)$_SESSION['branch_id']: 0;
$date = isset($_GET['date']) ? mysqli_real_escape_string($con, $_GET['date']) : date('Y-m-d');

$sql = "SELECT 
            u.u_name AS doctor_name,
            b.tag_name AS branch_name,
            t_stats.doctor_id,
            t_stats.opd,
            i_stats.*
        FROM (
            SELECT doctor_id, 
                   COUNT(CASE WHEN tokan_type_id < 9 AND status = 1 THEN 1 END) AS opd 
            FROM tokans 
            WHERE created LIKE '$date%' AND branch_id = $branch_id 
            GROUP BY doctor_id
        ) AS t_stats
        LEFT JOIN (
            /* Aggregate categories from item_by_doctor for the specific day */
            SELECT doctor_id,
                COUNT(DISTINCT CASE WHEN category_id = 2 THEN tokan_no END) AS labs,
                COUNT(CASE WHEN category_id = 3 THEN 1 END) AS procedures,
                COUNT(CASE WHEN category_id = 29 THEN 1 END) AS cons_opds,
                COUNT(CASE WHEN category_id = 31 THEN 1 END) AS dentals,
                COUNT(CASE WHEN category_id = 32 THEN 1 END) AS skins,
                COUNT(CASE WHEN category_id = 33 THEN 1 END) AS eyes,
                COUNT(CASE WHEN category_id = 37 THEN 1 END) AS svds,
                COUNT(CASE WHEN category_id = 38 THEN 1 END) AS dncs,
                COUNT(CASE WHEN category_id = 39 THEN 1 END) AS usgs,
                COUNT(CASE WHEN category_id = 40 THEN 1 END) AS admissions,
                COUNT(CASE WHEN category_id = 41 THEN 1 END) AS gynaes,
                COUNT(CASE WHEN category_id = 42 THEN 1 END) AS emergency,
                COUNT(CASE WHEN category_id = 44 THEN 1 END) AS ecgs
            FROM item_by_doctor
            WHERE created LIKE '$date%' AND branch_id = $branch_id
            GROUP BY doctor_id
        ) AS i_stats ON t_stats.doctor_id = i_stats.doctor_id
        INNER JOIN users u ON t_stats.doctor_id = u.id
        INNER JOIN branchs b ON u.branch_id = b.id
        WHERE u.branch_id = $branch_id
        ORDER BY u.u_name ASC";

$run = mysqli_query($con, $sql);
$results = [];

while ($row = mysqli_fetch_assoc($run)) {
    // Fill NULLs with 0 for consistent JS handling
    foreach ($row as $key => $val) { $row[$key] = $val ?? 0; }
    $results[] = $row;
}

header('Content-Type: application/json');
echo json_encode($results);