<?php
header('Content-Type: application/json');
include('config.php');
error_reporting(0); 

$response = [
    'success' => false, 
    'last_price' => 0, 
    'prev_price' => 0 // New field for the 2nd last price
];

if (isset($_GET['item_id']) && !empty($_GET['item_id'])) 
{
    $item_id = mysqli_real_escape_string($con, $_GET['item_id']);
    
    // Fetch the last 2 records
    $query = "SELECT per_item_price FROM `purchase_items` 
              WHERE item_id = '$item_id' AND per_item_price > 0 
              ORDER BY id DESC LIMIT 2";

    $result = mysqli_query($con, $query);

    if ($result && mysqli_num_rows($result) > 0) 
    {
        $response['success'] = true;
        $prices = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $prices[] = $row['per_item_price'];
        }
        
        $response['last_price'] = $prices[0]; // Most recent
        // Check if a second record exists
        $response['prev_price'] = isset($prices[1]) ? $prices[1] : 0; 
    }
}

echo json_encode($response);
exit;
?>