<?php
include 'includes/connect.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['return_ids'])) 
{
    $return_ids = $_POST['return_ids']; // This is the array from your checkboxes/hidden fields
    $dispatch_date = date('Y-m-d H:i:s');
    $authorized_by = $_SESSION['user_id'] ?? 0;

    // Start transaction to ensure either ALL items update or NONE do
    mysqli_begin_transaction($con);

    try {
        foreach ($return_ids as $id) {
            $id = mysqli_real_escape_string($con, $id);

            // 1. Get the details of the return to know how much to deduct from stock
            $info_sql = "SELECT purchase_id, return_quantity, item_id FROM return_purchase_items WHERE return_purchase_item_id = '$id'";
            $info_res = mysqli_query($con, $info_sql);
            $info = mysqli_fetch_assoc($info_res);

            if ($info) {
                $purchase_id = $info['purchase_id'];
                $item_id = $info['item_id'];
                $qty_to_deduct = $info['return_quantity'];

                // 2. Deduct the physical quantity from the main purchase_items table
                // $update_stock = "UPDATE items 
                //                  SET quantity = quantity - $qty_to_deduct 
                //                  WHERE id = $item_id ";
                // mysqli_query($con, $update_stock);

                // 3. Update the status of the return request to '3' (Dispatched)
                $update_status = "UPDATE return_purchase_items 
                                  SET return_purchase_item_status = 3, 
                                      return_purchase_item_updated_by = '$user_id' ,
                                      return_purchase_item_updated_at = '$dispatch_date' 
                                  WHERE return_purchase_item_id = '$id'";
                mysqli_query($con, $update_status);
            }
        }

        mysqli_commit($con);
        header("Location: final_dispatch.php?msg=dispatch_success");
        exit();

    } 
    catch (Exception $e) 
    {
        mysqli_rollback($con);
        header("Location: final_dispatch.php?msg=error");
        exit();
    }
} 
else 
{
    header("Location: final_dispatch.php");
    exit();
}
?>