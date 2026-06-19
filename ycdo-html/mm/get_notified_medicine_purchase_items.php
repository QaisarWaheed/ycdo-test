<?php
include 'includes/connect.php'; 

if(isset($_POST['party_id'])) {
    $party_id = mysqli_real_escape_string($con, $_POST['party_id']);

    // Select items that are NOT already in the price table for this specific party
    $query = "SELECT id, name FROM items 
              WHERE status = 1 
              AND id NOT IN (
                  SELECT item_id FROM notified_medicine__purchase_prices 
                  WHERE party_id = '$party_id'
              ) 
              ORDER BY name";
              
    $result = mysqli_query($con, $query);

    echo '<option value="">-- Select Medicine --</option>';
    while($row = mysqli_fetch_assoc($result)) {
        echo "<option value='{$row['id']}'>{$row['name']}</option>";
    }
}
?>