<?php
require 'includes/config.php';

if (isset($_POST['item_id']) && isset($_POST['item_box_size'])) 
{
    $item_id = $_POST['item_id'];
    $box_size = $_POST['item_box_size'];

    $safe_item_id  = mysqli_real_escape_string($con, $item_id);
    $safe_box_size = mysqli_real_escape_string($con, $box_size);
    
    $stmt = "UPDATE items SET item_box_size = $safe_box_size, updated_at = '$current_date', updated_by = '$user_id' WHERE id = $safe_item_id ";
    if (mysqli_query($con, $stmt)) 
    {
        echo "success"; 
    } 
    else 
    {
        echo "error: " . mysqli_error($con);
    }
    mysqli_close($con);
}
?>