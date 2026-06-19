<?php 
include '../lab/includes/config.php';
include 'connect.php'; 
include '../lab/includes/head.php'; 

// Initialize variables to avoid undefined errors
$search_item_id = "";
$search_item_name = "";

// Check if a search has been performed
if(isset($_POST['search_item'])) 
{
    $search_item_id = mysqli_real_escape_string($con, $_POST['item_id']);
    
    // Fetch the item name to display in the header
    $item_info_query = "SELECT name FROM items WHERE id = '$search_item_id' LIMIT 1";
    $item_info_run = mysqli_query($con, $item_info_query);
    if($item_info_row = mysqli_fetch_array($item_info_run)) 
    {
        $search_item_name = $item_info_row['name'];
    }
}
?>
    <link rel="stylesheet" type="text/css" href="../lab/css/nav_style.css"> 
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/selectize.js/0.12.6/js/standalone/selectize.min.js"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/selectize.js/0.12.6/css/selectize.bootstrap3.min.css" />
<script>
$(document).ready(function() {
  $('select').selectize({
    sortField: 'text'
  });
});    
</script>
    <title>SEARCH PURCHASE RECORDS BY ITEM - <?php echo $company_trademark; ?></title>
</head>
<body class="background_image">
    <div class="row" style="margin: 0px;">
        <div class="col-md-12" style="text-align: center; background: lightgreen; padding: 10px;">
            <label><h1><?php echo $company_name; ?> </h1></label>
        </div>
        
        <div class="col-md-2 background_whitesmoke nodisplay_print">
            <?php include 'left_navigation.php'; ?>
        </div>
        
        <div class="col-md-10">
            <div class="card my-3 nodisplay_print" style="padding: 15px; background: #f8f9fa;">
                <form method="POST" action="">
                    <div class="row align-items-end">
                        <div class="col-md-6">
                            <label>Select / Search Item Name:</label>
                            <select name="item_id" class="form-control" required>
                                <option value="">-- Type to Search Item --</option>
                                <?php 
                                $get_items = mysqli_query($con, "SELECT id, name FROM items WHERE category_id IN (28,43) ORDER BY name ASC");
                                while($item_row = mysqli_fetch_array($get_items)){
                                    $selected = ($item_row['id'] == $search_item_id) ? "selected" : "";
                                    echo "<option value='".$item_row['id']."' ".$selected.">".$item_row['name']."</option>";
                                }
                                ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <button type="submit" name="search_item" class="btn btn-primary btn-block">Search Record</button>
                        </div>
                    </div>
                </form>
            </div>

            <?php if($search_item_id != ""): ?>
            <div class="row">
                <div class="col-sm-12">
                    <table class="table table-sm table-bordered bg-white">
                        <caption style="caption-side: top; color: black;">
                            <h2 align="center">PURCHASE DETAILS FOR: <span style="color: #007bff;"><?php echo strtoupper($search_item_name); ?></span></h2>
                        </caption>
                        <thead class="thead-light text-center">
                            <tr>
                                <th>S #</th>
                                <th>DATE</th>
                                <th>BILL NO</th>
                                <th>PARTY NAME</th>
                                <th>RATE</th>
                                <th>QUANTITY</th>
                                <th>AMOUNT</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $s = 0;
                            $total = 0;
                            
                            // Query modified to grab purchase details filtered by the chosen item_id
                            $select = "SELECT 
                                        purchase_lab_items.purchase_lab_item_date, 
                                        purchase_lab_items.invoice_purchase_lab_item_bill_no,
                                        purchase_lab_items.purchase_lab_item_rate, 
                                        purchase_lab_items.purchase_lab_item_quantity, 
                                        purchase_lab_items.purchase_lab_item_price,
                                        parties.name AS party_name
                                       FROM `purchase_lab_items` 
                                       INNER JOIN invoice_purchase_lab_items ON purchase_lab_items.invoice_purchase_lab_item_bill_no = invoice_purchase_lab_items.invoice_purchase_lab_item_bill_no
                                       INNER JOIN parties ON invoice_purchase_lab_items.invoice_party_id = parties.id
                                       WHERE purchase_lab_items.item_id = '$search_item_id' 
                                       AND purchase_lab_items.purchase_lab_item_status > 0
                                       ORDER BY purchase_lab_items.purchase_lab_item_date DESC";
                            
                            $run = mysqli_query($con, $select);
                            if(mysqli_num_rows($run) > 0) {
                                while($row = mysqli_fetch_array($run)) {
                                    $s++; 
                                    $total += $row['purchase_lab_item_price'];
                                    ?>
                                    <tr>
                                        <td class="text-center"><?php echo $s; ?></td>
                                        <td class="text-center"><?php echo date_format(date_create($row['purchase_lab_item_date']),"d-M-Y"); ?></td>
                                        <td class="text-center"><strong><?php echo $row['invoice_purchase_lab_item_bill_no']; ?></strong></td>
                                        <td><?php echo $row['party_name']; ?></td>
                                        <td class="text-right"><?php echo number_format($row['purchase_lab_item_rate'], 2); ?></td>
                                        <td class="text-center"><?php echo $row['purchase_lab_item_quantity']; ?></td>
                                        <td class="text-right"><?php echo number_format($row['purchase_lab_item_price'], 2); ?></td>
                                    </tr>
                                <?php }
                            } else {
                                echo "<tr><td colspan='7' class='text-center text-danger'>No purchase records found for this item.</td></tr>";
                            } ?>
                        </tbody>
                        <tfoot>
                            <tr class="table-secondary">
                                <th colspan="6" class="text-right">TOTAL AMOUNT SPENT ON THIS ITEM:</th>
                                <th class="text-right"><?php echo number_format($total, 2); ?></th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
            <?php else: ?>
                <div class="alert alert-info mt-5 text-center">Please select an Item Name above to look up transaction history.</div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>