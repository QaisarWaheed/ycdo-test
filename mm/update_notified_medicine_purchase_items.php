<?php
include 'includes/connect.php'; 

if(isset($_POST['price_id'])) {
    $id = $_POST['price_id'];
    $retail = $_POST['retail_price'];
    $purchase = $_POST['purchase_price'];
    $status = mysqli_real_escape_string($con, $_POST['status']);
    $user_id = 1; // Replace with your session user id

    $sql = "UPDATE notified_medicine__purchase_prices 
            SET notified_medicine__purchase_price_retail_price = '$retail', 
                notified_medicine__purchase_price_purchase_price = '$purchase',
                notified_medicine__purchase_price_status = '$status',
                notified_medicine__purchase_price_updated_by = '$user_id',
                notified_medicine__purchase_price_updated_at = '$current_date'
            WHERE notified_medicine__purchase_price_id = '$id'";

    if(mysqli_query($con, $sql)) {
        echo "success";
    }
}
?>