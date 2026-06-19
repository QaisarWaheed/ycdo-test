<?php
include 'includes/connect.php';
// if(isset($_POST['branch_item_id']) && $_POST['show_consumed_data'] != '')
// {
//     $branch_item_id = $_POST['branch_item_id'];
//     $updated_at = $_POST['updated_at'];
//     $select_consume = "SELECT SUM(`sale_quantity`) FROM `item_by_doctor` WHERE `item_id` = '$branch_item_id' AND `created` > '$updated_at' ";
//     $run_consume = mysqli_query($con, $select_consume);
//     if(mysqli_num_rows($run_consume) == 1)
//     {
//         while($row_consume = mysqli_fetch_array($run_consume))
//         {
//             $consumed_quantity = $row_consume['0'];
//             if (is_null($consumed_quantity)) 
//             {
//                 $consumed_quantity =  0;
//             }
//         }
//     }
// }
// else
// {
    
// }
// echo $consumed_quantity;

// Inside show_consumed_item_records.php
if(isset($_POST['show_consumed_data'])) {
    $br_id = $_POST['br_id'];
    $item_id = $_POST['branch_item_id'];
    $updated_at = $_POST['updated_at'];

    // Query to find items used AFTER the updated_at date
    $query = "SELECT items.name, SUM(`sale_quantity`) AS quantity FROM `item_by_doctor` INNER JOIN item_register_to_branches ON item_by_doctor.item_id = item_register_to_branches.id INNER JOIN items ON item_register_to_branches.item_id = items.id WHERE item_by_doctor.item_id = '$item_id' AND item_by_doctor.created > '$updated_at' ";
    
    $result = mysqli_query($con, $query);
    
    echo "<table class='table'>
            <thead>
                <tr><th>Date</th><th>Item</th><th>Qty Consumed</th></tr>
            </thead>
            <tbody>";
    
    if(mysqli_num_rows($result) > 0) 
    {
        while($row = mysqli_fetch_assoc($result)) 
        {
            echo "<tr>
                    <td>".date('d-M-Y', strtotime($updated_at))."</td>
                    <td>".$row['name']."</td>
                    <td>".$row['quantity']."</td>
                  </tr>";
        }
    } else {
        echo "<tr><td colspan='3' align='center'>No consumption recorded after this date.</td></tr>";
    }
    echo "</tbody></table>";
}
?>