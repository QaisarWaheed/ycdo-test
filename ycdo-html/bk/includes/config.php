<?php
require_once __DIR__ . '/../../includes/ycdo_bootstrap.php';
date_default_timezone_set("Asia/Karachi");
$current_date = date('Y-m-d h:i:s A');
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$con = ycdo_db_connect();
$GLOBALS['con'] = $con;
$user_id = $bk_id;
$branch_id = $bk_branch_id;
$branch_name = $bk_branch_name;
?>