<?php
include 'config.php';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 5; // Records per page
$offset = ($page - 1) * $limit;
$sql = "SELECT DISTINCT tokans.doctor_id AS id, users.u_name, branchs.tag_name AS name, SUM(cash) AS collection, COUNT(CASE WHEN tokan_type_id < 100 THEN tokans.id END) AS opd FROM `tokans` INNER JOIN users ON tokans.doctor_id = users.id INNER JOIN branchs ON tokans.branch_id = branchs.id WHERE tokans.created LIKE '%2026-03%' AND tokans.branch_id = 1 GROUP BY tokans.doctor_id ";
$sql .= " ORDER BY `id` ASC LIMIT $offset, $limit";

$result = mysqli_query($con, $sql);
$data = [];
while($row = mysqli_fetch_assoc($result)) 
{
    $data[] = $row;
}
header('Content-Type: application/json');
echo json_encode($data);
?>