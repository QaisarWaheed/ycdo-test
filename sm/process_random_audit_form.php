<?php
include('includes/config.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['audit'])) 
{
    echo $insert = "INSERT INTO `inventory_audit_details`(`inventory_audit_detail_id`, `user_id`, `inventory_audit_detail_status`, `inventory_audit_detail_created`) VALUES (NULL, $user_id, '1', '$current_date')";
    if(mysqli_query($con, $insert))
    {
        $inventory_audit_no = mysqli_insert_id($con);
        foreach ($_POST['audit'] as $branch_id => $items) 
        {
            foreach ($items as $item_id => $data) 
            {
                
                $reg_id = mysqli_real_escape_string($con, $data['reg_id']);
                $system_qty = intval($data['system']);
                $physical_qty = intval($data['physical']);
                $difference = $physical_qty - $system_qty;
                
                if ($reg_id != "N/A" && $reg_id > 0) 
                {
                    
                    $query = "INSERT INTO inventory_audits 
                              (branch_id, item_id, reg_item_id, inventory_audit_computer_quantity, inventory_audit_manual_quantity, inventory_audit_difference, inventory_audit_no, inventory_audit_date) 
                              VALUES 
                              ($branch_id, $item_id, $reg_id, 0, 0, $difference, $inventory_audit_no, '$current_date')";
                    
                    mysqli_query($con, $query);
                }
            }
        }        
    }
    else
    {
        header("Location: dashboard.php?msg=Error");
        exit();
    }

    
    header("Location: dashboard.php?msg=AuditSaved");
    exit();
}
?>
</html>