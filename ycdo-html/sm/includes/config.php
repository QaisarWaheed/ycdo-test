<?php
require_once __DIR__ . '/../../includes/ycdo_bootstrap.php';
date_default_timezone_set("Asia/Karachi");
$current_date = date('Y-m-d G:i:s A');
session_start();
if (isset($_SESSION['sm_id'])) 
{
    $user_id = $_SESSION['sm_id'];
    $user_name = $_SESSION['sm_name'];
    $branch_id = $_SESSION['branch_id'];
    $is_admin = $_SESSION['is_admin'];
    $is_incharge = $_SESSION['is_incharge'];
    $role_id = $_SESSION['role_id'];
    $branch_name = $_SESSION['branch_name'];
    $branch_address = $_SESSION['branch_address'];
    $branch_phone = $_SESSION['branch_phone'];
}
else
{
    header('location: logout.php'); 
}

include 'company_info.php'; 
$con = ycdo_db_connect();
// $con = mysqli_connect('localhost', 'ycdoeh1', 'ycdoeh1', 'ycdomlt');
// if(!$con)
//     {
//         echo $con->error;
//     }
?>