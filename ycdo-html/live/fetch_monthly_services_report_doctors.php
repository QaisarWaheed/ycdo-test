<?php
include 'config.php';

// 1. Get the date from the user
$selected_date = isset($_GET['selected_date']) ? mysqli_real_escape_string($con, $_GET['selected_date']) : date('Y-m-d');

// 2. Calculate range: First of month up to the selected date
$first_day = date("Y-m-01", strtotime($selected_date));
$last_day  = $selected_date; // We only care about data up to the selected day

$branch_id = isset($_GET['branch_id']) ? (int)$_GET['branch_id'] : 0;
$branch_condition = ($branch_id == -1) ? "1=1" : "branch_id = $branch_id";
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 50; 
$offset = ($page - 1) * $limit;

$sql = "SELECT 
            u.u_name AS doctor_name, 
            b.tag_name AS branch_name,
            t_stats.*, 
            i_stats.*,
            (t_stats.opd_today + t_stats.opd_prev) AS opd_total,
            (i_stats.lab_today + i_stats.lab_prev) AS lab_total,
            (i_stats.usg_today + i_stats.usg_prev) AS usg_total,
            (i_stats.proc_today + i_stats.proc_prev) AS proc_total,
            (i_stats.adm_today + i_stats.adm_prev) AS adm_total,
            (i_stats.ecg_today + i_stats.ecg_prev) AS ecg_total,
            (i_stats.gyn_today + i_stats.gyn_prev) AS gyn_total
        FROM (
            SELECT doctor_id,
                COUNT(CASE WHEN DATE(created) = '$selected_date' AND tokan_type_id < 100 THEN 1 END) AS opd_today,
                COUNT(CASE WHEN DATE(created) < '$selected_date' AND tokan_type_id < 100 THEN 1 END) AS opd_prev
            FROM tokans 
            WHERE created BETWEEN '$first_day 00:00:00' AND '$selected_date 23:59:59'
            AND $branch_condition
            GROUP BY doctor_id
        ) AS t_stats
        LEFT JOIN (
            SELECT doctor_id,
                SUM(CASE WHEN category_id = 2 AND DATE(created) = '$selected_date' THEN sale_price ELSE 0 END) AS lab_today,
                SUM(CASE WHEN category_id = 2 AND DATE(created) < '$selected_date' THEN sale_price ELSE 0 END) AS lab_prev,
                
                
                COUNT(CASE WHEN category_id = 39 AND DATE(created) = '$selected_date' THEN 1 END) AS usg_today,
                COUNT(CASE WHEN category_id = 39 AND DATE(created) < '$selected_date' THEN 1 END) AS usg_prev,
                
                COUNT(CASE WHEN category_id = 3 AND DATE(created) = '$selected_date' THEN 1 END) AS proc_today,
                COUNT(CASE WHEN category_id = 3 AND DATE(created) < '$selected_date' THEN 1 END) AS proc_prev,
                
                COUNT(CASE WHEN category_id = 40 AND DATE(created) = '$selected_date' THEN 1 END) AS adm_today,
                COUNT(CASE WHEN category_id = 40 AND DATE(created) < '$selected_date' THEN 1 END) AS adm_prev,
                
                COUNT(CASE WHEN category_id = 44 AND DATE(created) = '$selected_date' THEN 1 END) AS ecg_today,
                COUNT(CASE WHEN category_id = 44 AND DATE(created) < '$selected_date' THEN 1 END) AS ecg_prev,
                
                COUNT(CASE WHEN category_id = 41 AND DATE(created) = '$selected_date' THEN 1 END) AS gyn_today,
                COUNT(CASE WHEN category_id = 41 AND DATE(created) < '$selected_date' THEN 1 END) AS gyn_prev
            FROM item_by_doctor
            WHERE created BETWEEN '$first_day 00:00:00' AND '$selected_date 23:59:59'
            AND $branch_condition
            GROUP BY doctor_id
        ) AS i_stats ON t_stats.doctor_id = i_stats.doctor_id
        INNER JOIN users u ON t_stats.doctor_id = u.id
        LEFT JOIN branchs b ON u.branch_id = b.id
        ORDER BY t_stats.doctor_id ASC
        LIMIT $offset, $limit";

$run = mysqli_query($con, $sql);
$data = [];
$ser = $offset + 1;

if ($run) {
    while ($row = mysqli_fetch_assoc($run)) {
        $row['ser'] = $ser++; // Adding serial number
        foreach ($row as $key => $value) {
            if ($value === null) $row[$key] = "0";
        }
        $data[] = $row;
    }
}

header('Content-Type: application/json');
echo json_encode($data);
?>