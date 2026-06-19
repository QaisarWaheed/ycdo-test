<?php
include 'includes/config.php';
// Turn on error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (!$con) {
    header('Content-Type: application/json');
    exit(json_encode(['error' => 'Connection failed: ' . mysqli_connect_error()]));
}

// 1. Inputs
$br_id = !empty($_GET['br_id']) ? (int)$_GET['br_id'] : 0;
$fromDate = isset($_GET['fromDate']) ? mysqli_real_escape_string($con, $_GET['fromDate']) : date('Y-m-d');
$toDate = isset($_GET['toDate']) ? mysqli_real_escape_string($con, $_GET['toDate']) : date('Y-m-d');

$start = $fromDate . " 00:00:00";
$end = $toDate . " 23:59:59";

// 2. Branch Filter String (Standardizing for subqueries safely)
$br_filter = ($br_id > 0) ? "AND branch_id = $br_id" : "";

// 3. Optimized Query
$sql = "SELECT 
            u.u_name AS doctor_name,
            b.tag_name AS branch_name,
            master_list.doctor_id,
            IFNULL(t_stats.opd, 0) AS opd,
            IFNULL(t_stats.collection, 0) AS collection,
            IFNULL(i_stats.labs, 0) AS labs,
            IFNULL(i_stats.amount_labs, 0) AS amount_labs,
            IFNULL(i_stats.procedures, 0) AS procedures,
            IFNULL(i_stats.amount_procedures, 0) AS amount_procedures,
            IFNULL(i_stats.cons_opds, 0) AS cons_opds,
            IFNULL(i_stats.dentals, 0) AS dentals,
            IFNULL(i_stats.skins, 0) AS skins,
            IFNULL(i_stats.eyes, 0) AS eyes,
            IFNULL(i_stats.svds, 0) AS svds,
            IFNULL(i_stats.dncs, 0) AS dncs,
            IFNULL(i_stats.usgs, 0) AS usgs,
            IFNULL(i_stats.admissions, 0) AS admissions,
            IFNULL(i_stats.gynaes, 0) AS gynaes,
            IFNULL(i_stats.emergency, 0) AS emergency,
            IFNULL(i_stats.ecgs, 0) AS ecgs,
            IFNULL(g_stats.gynae_registrations, 0) AS gynae_registrations,
            IFNULL(r_stats.referral_from, 0) AS ref_from,
            IFNULL(r_stats.referral_to, 0) AS ref_to
        FROM (
            /* Master List: Union of all activity tables */
            SELECT doctor_id FROM tokans WHERE created BETWEEN '$start' AND '$end' $br_filter
            UNION
            SELECT doctor_id FROM item_by_doctor WHERE created BETWEEN '$start' AND '$end' $br_filter
            UNION
            SELECT doctor_id FROM gynae_register WHERE created BETWEEN '$start' AND '$end' $br_filter
            UNION
            SELECT rp.from_user_id AS doctor_id FROM referral_patients rp INNER JOIN users us ON rp.from_user_id = us.id WHERE rp.referral_patient_created BETWEEN '$start' AND '$end' " . (($br_id > 0) ? "AND us.branch_id = $br_id" : "") . "
            UNION
            SELECT rp.to_user_id AS doctor_id FROM referral_patients rp INNER JOIN users us ON rp.to_user_id = us.id WHERE rp.referral_patient_created BETWEEN '$start' AND '$end' " . (($br_id > 0) ? "AND us.branch_id = $br_id" : "") . "
        ) AS master_list
        LEFT JOIN (
            SELECT doctor_id, 
                   COUNT(CASE WHEN tokan_type_id < 100 THEN 1 END) AS opd,
                   SUM(cash) AS collection 
            FROM tokans 
            WHERE created BETWEEN '$start' AND '$end' $br_filter 
            GROUP BY doctor_id
        ) AS t_stats ON master_list.doctor_id = t_stats.doctor_id
        LEFT JOIN (
            SELECT doctor_id,
                COUNT(DISTINCT CASE WHEN category_id = 2 THEN tokan_no END) AS labs,
                SUM(CASE WHEN category_id = 2 THEN sale_price ELSE 0 END) AS amount_labs,
                COUNT(CASE WHEN category_id = 3 THEN 1 END) AS procedures,
                SUM(CASE WHEN category_id = 3 THEN sale_price ELSE 0 END) AS amount_procedures,
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
            WHERE created BETWEEN '$start' AND '$end' $br_filter
            GROUP BY doctor_id
        ) AS i_stats ON master_list.doctor_id = i_stats.doctor_id
        LEFT JOIN (
            SELECT doctor_id, COUNT(id) AS gynae_registrations
            FROM gynae_register
            WHERE created BETWEEN '$start' AND '$end' $br_filter
            GROUP BY doctor_id
        ) AS g_stats ON master_list.doctor_id = g_stats.doctor_id
        LEFT JOIN (
            SELECT d.doctor_id, SUM(d.is_from) AS referral_from, SUM(d.is_to) AS referral_to
            FROM (
                SELECT rp.from_user_id AS doctor_id, 1 AS is_from, 0 AS is_to 
                FROM referral_patients rp INNER JOIN users us ON rp.from_user_id = us.id
                WHERE rp.referral_patient_created BETWEEN '$start' AND '$end' " . (($br_id > 0) ? "AND us.branch_id = $br_id" : "") . "
                UNION ALL
                SELECT rp.to_user_id AS doctor_id, 0 AS is_from, 1 AS is_to 
                FROM referral_patients rp INNER JOIN users us ON rp.to_user_id = us.id
                WHERE rp.referral_patient_created BETWEEN '$start' AND '$end' " . (($br_id > 0) ? "AND us.branch_id = $br_id" : "") . "
            ) d GROUP BY d.doctor_id
        ) AS r_stats ON master_list.doctor_id = r_stats.doctor_id
        INNER JOIN users u ON master_list.doctor_id = u.id
        LEFT JOIN branchs b ON u.branch_id = b.id
        ORDER BY master_list.doctor_id ASC";

$run = mysqli_query($con, $sql);

if (!$run) {
    header('Content-Type: application/json');
    exit(json_encode(['error' => mysqli_error($con)]));
}

$results = [];
while ($row = mysqli_fetch_assoc($run)) {
    foreach ($row as $key => $val) {
        if ($key != 'doctor_name' && $key != 'branch_name') {
            $row[$key] = ($val === null) ? 0 : (float)$val;
        }
    }
    $results[] = $row;
}

header('Content-Type: application/json');
echo json_encode($results);
?>