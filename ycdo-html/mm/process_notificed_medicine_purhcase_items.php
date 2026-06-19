<?php
include 'includes/connect.php'; 

if(isset($_POST['item_id'])) 
{
    $party_id = mysqli_real_escape_string($con, $_POST['party_id']);
    $item_id = mysqli_real_escape_string($con, $_POST['item_id']);
    $purchase = mysqli_real_escape_string($con, $_POST['purchase_price']);
    $retail = mysqli_real_escape_string($con, $_POST['retail_price']);
    
    $sql = "INSERT INTO notified_medicine__purchase_prices 
            (party_id, item_id, notified_medicine__purchase_price_purchase_price, notified_medicine__purchase_price_retail_price, notified_medicine__purchase_price_status, notified_medicine__purchase_price_created_by, notified_medicine__purchase_price_created_at) 
            VALUES ('$party_id', '$item_id', '$purchase', '$retail', '1', '$current_date', '$user_id')";

    if(mysqli_query($con, $sql)) 
    {
        echo "success";
    } 
    else 
    {
        header('HTTP/1.1 500 Internal Server Error');
        echo "Error: " . mysqli_error($con);
    }
}
?>