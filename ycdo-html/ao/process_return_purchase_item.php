<?php
include 'includes/connect.php';
// Adds 12 hours to the current time
$current = date('Y-m-d H:i:s', strtotime('+12 hours'));
if ($_SERVER['REQUEST_METHOD'] === 'POST') 
{
    $return_id   = mysqli_real_escape_string($con, $_POST['return_id']);
    $action      = $_POST['action'];
    $admin_note  = mysqli_real_escape_string($con, $_POST['note'] ?? '');

    $authorized_by = $_SESSION['ao_id'] ?? 0; 
    $new_status = ($action === 'approved') ? 2 : 0;
    mysqli_begin_transaction($con);

    try {
        echo $update_sql = "UPDATE return_purchase_items 
                       SET return_purchase_item_status = $new_status, 
                           return_approved_by = $authorized_by, 
                           return_approved_at = '$current',
                           return_admin_note = '$admin_note'
                       WHERE return_purchase_item_id = $return_id ";

        $stmt = mysqli_query($con, $update_sql);
        mysqli_commit($con);
        header("Location: show_return_purchase_item.php?msg=success&id=" . $return_id);
        exit();

    } catch (Exception $e) {
        mysqli_rollback($con);
        header("Location: show_return_purchase_item.php?msg=error");
        exit();
    }
} else {
    header("Location: show_return_purchase_item.php");
    exit();
}
?>