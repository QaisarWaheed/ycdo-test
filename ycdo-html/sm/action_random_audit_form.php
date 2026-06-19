<?php
include('includes/config.php');

if (!isset($_POST['selected_items'])) 
{
    header("Location: dashboard.php");
    exit();
}

$item_ids = $_POST['selected_items'];
$ids_string = implode(',', array_map('intval', $item_ids));

$items_query = mysqli_query($con, "SELECT id, name, quantity FROM items WHERE id IN ($ids_string)");
$items = mysqli_fetch_all($items_query, MYSQLI_ASSOC);

// 2. Get Branch Stock Data
$branches_query = mysqli_query($con, "SELECT 
    irb.id, 
    irb.item_id, 
    b.id AS branch_id, 
    irb.quantity, 
    b.tag_name AS name 
    FROM item_register_to_branches irb
    INNER JOIN branchs b ON irb.branch_id = b.id 
    WHERE irb.item_id IN ($ids_string)");

$branch_data = mysqli_fetch_all($branches_query, MYSQLI_ASSOC);
$stock_map = [];
$unique_branches = [];

foreach ($branch_data as $row) 
{
    // Store both pieces of info in a sub-array
    $stock_map[$row['branch_id']][$row['item_id']] = [
        'qty' => $row['quantity'],
        'reg_id' => $row['id']
    ];
    $unique_branches[$row['branch_id']] = $row['name'];
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Branch Stock Entry</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
</head>
<body>
<div class="container-fluid mt-5">
    <h3>Manual Quantity Audit</h3>
    <form action="process_random_audit_form.php" method="POST">
<table class="table table-bordered table-striped bg-white">
    <thead class="thead-dark">
        <tr>
            <th>Branch / Item</th>
            <?php foreach ($items as $item): ?>
                <th>
                    <?php echo $item['name']; ?><br>
                    <small>Item Id: <?php echo $item['id']; ?></small><br>
                    <small>(Total: <?php echo $item['quantity']; ?>)</small>
                </th>
            <?php endforeach; ?>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($unique_branches as $b_id => $b_name): ?>
        <tr>
            <td class="font-weight-bold"><?php echo $b_name; ?></td>
            <?php foreach ($items as $item): ?>
                <?php 
                    $i_id = $item['id'];
                    
                    if (isset($stock_map[$b_id][$i_id])) {
                        $reg_id = $stock_map[$b_id][$i_id]['reg_id'];
                        $current_qty = $stock_map[$b_id][$i_id]['qty'];
                    } else {
                        $reg_id = "N/A"; 
                        $current_qty = 0;
                    }
                ?>
            <td>
                <small class="text-muted">Reg ID: <?php echo $reg_id; ?></small>
                
                <input type="number" 
                       name="audit[<?php echo $b_id; ?>][<?php echo $i_id; ?>][physical]" 
                       class="form-control" 
                       value="<?php echo $current_qty; ?>">
            
                <input type="hidden" name="audit[<?php echo $b_id; ?>][<?php echo $i_id; ?>][system]" value="<?php echo $current_qty; ?>">
                <input type="hidden" name="audit[<?php echo $b_id; ?>][<?php echo $i_id; ?>][reg_id]" value="<?php echo $reg_id; ?>">
            </td>
            <?php endforeach; ?>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>
        <div class="text-right mb-5">
            <button type="submit" class="btn btn-primary btn-lg">Save Audit Data</button>
        </div>
    </form>
</div>
</body>