<?php
include 'config.php';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 5; // Records per page
$offset = ($page - 1) * $limit;

$sql = "SELECT `id`,`name`,`tag_name`,`phone`,`address` FROM `branchs` WHERE `status` = 1";
if (!empty($search)) {
    $sql .= " AND (`name` LIKE '%$search%' OR `phone` LIKE '%$search%')";
}

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