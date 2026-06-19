<?php
include('includes/connect.php');

if (isset($_POST['audit_id'])) 
{
    $id = mysqli_real_escape_string($con, $_POST['audit_id']);
    $m_qty = mysqli_real_escape_string($con, $_POST['manual_qty']);
    $diff = mysqli_real_escape_string($con, $_POST['diff']);

    $sql = "UPDATE `inventory_audits` SET 
            `inventory_audit_manual_quantity` = '$m_qty', 
            `inventory_audit_difference` = '$diff',
            `inventory_audit_date` = NOW() 
            WHERE `inventory_audit_id` = '$id'";

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