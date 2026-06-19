<?php
// $con = mysqli_connect('localhost', 'ycdoeh1', 'ycdoeh1', 'ycdomlt');

require_once __DIR__ . '/../includes/ycdo_bootstrap.php';
date_default_timezone_set("Asia/Karachi");
$con = ycdo_db_connect();
$GLOBALS['con'] = $con;
?>