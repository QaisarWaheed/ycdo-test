<?php
include 'includes/connect.php'; 

if (isset($_POST['party_id'])) {
    $party_id = mysqli_real_escape_string($con, $_POST['party_id']);

    $sql = "SELECT npp.*, i.name as item_name 
            FROM notified_medicine__purchase_prices npp
            INNER JOIN items i ON npp.item_id = i.id 
            WHERE npp.party_id = '$party_id'";

    $result = mysqli_query($con, $sql);

    if (mysqli_num_rows($result) > 0) 
    {
        while ($row = mysqli_fetch_assoc($result)) 
        {
            // Logic for status badge color
            if($row['notified_medicine__purchase_price_status'] == '1' )
            {
                $status_class = 'badge-success';
                $status = "ACTIVE";
            }
            elseif($row['notified_medicine__purchase_price_status'] == '3' )
            {
                $status_class = 'badge-danger';
                $status = "BLOCKED";
            }
            else
            {
                $status_class = 'badge-warning';
                $status = "DISCOUNTINUE";
            }
            
            echo "<tr>";
            echo "<td>" . $row['item_name'] . "</td>";
            echo "<td>" . number_format($row['notified_medicine__purchase_price_retail_price'], 2) . "</td>";
            echo "<td>" . number_format($row['notified_medicine__purchase_price_purchase_price'], 2) . "</td>";
            echo "<td>" . number_format($row['notified_medicine__purchase_price_discount'], 2) . "</td>";
            echo "<td><span class='badge $status_class'>" . $status . "</span></td>";
            echo "<td>
                <button type='button' 
                    class='btn btn-sm btn-info edit-btn' 
                    data-id='{$row['notified_medicine__purchase_price_id']}'
                    data-retail='{$row['notified_medicine__purchase_price_retail_price']}'
                    data-purchase='{$row['notified_medicine__purchase_price_purchase_price']}'
                    data-status='{$row['notified_medicine__purchase_price_status']}'
                    <i class='fa fa-edit'></i> Update
                </button>
                </td>";
            echo "</tr>";
        }
    } 
    else 
    {
        echo "<tr><td colspan='6' class='text-center'>No records found for this party.</td></tr>";
    }
}
?>