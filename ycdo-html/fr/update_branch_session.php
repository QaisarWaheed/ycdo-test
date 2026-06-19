<?php
if (isset($_POST['change_branch_id']) && $_POST['change_branch_id'] != '') {
    require_once __DIR__ . '/../includes/db_connect.php';

    $branch_id = (int) $_POST['change_branch_id'];
    $branch_name = '';
    $branch_address = '';
    $branch_phone = '';

    $select_branch = mysqli_query($con, "SELECT * FROM `branchs` WHERE `id` = '$branch_id' ");
    if ($select_branch && mysqli_num_rows($select_branch) == 1) {
        while ($row_branch = mysqli_fetch_array($select_branch)) {
            $branch_name = $row_branch['name'];
            $branch_address = $row_branch['address'];
            $branch_phone = $row_branch['phone'];
        }
    }

    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    $_SESSION['branch_id'] = $branch_id;
    $_SESSION['branch_name'] = $branch_name;
    $_SESSION['branch_address'] = $branch_address;
    $_SESSION['branch_phone'] = $branch_phone;

    header('Location: dashboard.php');
    exit;
}

header('Location: logout.php');
exit;