<?php
include('includes/db_connection.php'); 
if (isset($_POST['audit_id'])) 
{
    $id = mysqli_real_escape_string($con, $_POST['audit_id']);
    $m_qty = mysqli_real_escape_string($con, $_POST['manual_qty']);
    $c_qty = mysqli_real_escape_string($con, $_POST['computer_qty']);
    $diff = mysqli_real_escape_string($con, $_POST['diff']);
    $unit_price = mysqli_real_escape_string($con, $_POST['unit_price']);

    $sql = "UPDATE `inventory_audits` SET 
            `inventory_audit_manual_quantity` = '$m_qty', 
            `inventory_audit_computer_quantity` = '$c_qty', 
            `inventory_audit_item_poor` = '$unit_price',
            `inventory_audit_difference` = '$diff',
            `inventory_audit_updated_by` = '$user_id',
            `inventory_audit_updated_at` = '$current_date',
            `inventory_audit_status` = 2
            WHERE `inventory_audit_id` = '$id' AND `inventory_audit_status` = 1 ";

    if (mysqli_query($con, $sql)) 
    {
        echo "success";
    } 
    else 
    {
        echo "Database error: " . mysqli_error($con);
    }
}
?>