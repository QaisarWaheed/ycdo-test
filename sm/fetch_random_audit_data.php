<?php
include 'includes/config.php'; 

$audit_id = mysqli_real_escape_string($con, $_POST['audit_id']);
$search = mysqli_real_escape_string($con, $_POST['query']);

$sql = "SELECT item_id, reg_item_id, items.name AS item_name, branchs.tag_name, 
               inventory_audit_difference, inventory_audit_item_poor, 
               inventory_audit_computer_quantity, inventory_audit_manual_quantity 
        FROM inventory_audits 
        INNER JOIN items ON inventory_audits.item_id = items.id 
        INNER JOIN branchs ON inventory_audits.branch_id = branchs.id 
        WHERE inventory_audit_no = '$audit_id'";

if (!empty($search)) {
    $sql .= " AND (items.name LIKE '%$search%' OR branchs.tag_name LIKE '%$search%' OR reg_item_id LIKE '%$search%' OR item_id LIKE '%$search%')";
}

$result = mysqli_query($con, $sql);
$s = 0;

if (mysqli_num_rows($result) > 0) {
    while($row = mysqli_fetch_array($result)) {
        $s++;
        $diff = $row['inventory_audit_difference'];
        $rate = $row['inventory_audit_item_poor'];
        $total = $diff * $rate;
        $class = ($diff < 0) ? 'text-danger' : 'text-success';
        
        // CRITICAL: Ensure you are outputting <tr> and <td> tags
        echo "<tr>";
        echo "<td>" . $s . "</td>";
        echo "<td>" . $row['reg_item_id'] . "</td>";
        echo "<td>" . $row['item_id'] . "</td>";
        echo "<td>" . $row['tag_name'] . "</td>";
        echo "<td>" . $row['item_name'] . "</td>";
        echo "<td>" . $row['inventory_audit_computer_quantity'] . "</td>";
        echo "<td>" . $row['inventory_audit_manual_quantity'] . "</td>";
        echo "<td>" . $diff . "</td>";
        echo "<td>" . number_format($rate, 2) . "</td>";
        echo "<td class='$class'>" . number_format($total, 2) . "</td>";
        echo "</tr>";
    }
} else {
    echo "<tr><td colspan='10' class='text-center'>No items found.</td></tr>";
}
?>