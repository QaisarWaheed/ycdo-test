<?php 
include '../lab/includes/config.php';
include 'connect.php'; 
include '../lab/includes/head.php'; 

// Initialize variables to avoid undefined errors
$invoice_purchase_lab_item_bill_no = "";
$invoice_purchase_lab_item_date = "";
$invoice_party_bill_no = "";
$invoice_party_name = "";
$invoice_user_name = "";

// Check if a search has been performed
if(isset($_POST['search_bill'])) 
{
    $invoice_purchase_lab_item_bill_no = mysqli_real_escape_string($con, $_POST['bill_no']);
    
    // Fetch header info for the specific bill
    $header_query = "SELECT invoice_purchase_lab_items.invoice_purchase_lab_item_date, 
                            invoice_purchase_lab_items.invoice_party_bill_no, 
                            users.u_name, 
                            parties.name 
                     FROM invoice_purchase_lab_items 
                     INNER JOIN parties ON invoice_purchase_lab_items.invoice_party_id = parties.id 
                     INNER JOIN users ON invoice_purchase_lab_items.invoice_purchase_lab_item_created_by = users.id 
                     WHERE invoice_purchase_lab_item_bill_no = '$invoice_purchase_lab_item_bill_no' LIMIT 1";
    
    $header_run = mysqli_query($con, $header_query);
    if($header_row = mysqli_fetch_array($header_run)) 
    {
        $invoice_purchase_lab_item_date = $header_row['invoice_purchase_lab_item_date'];
        $invoice_party_bill_no = $header_row['invoice_party_bill_no'];
        $invoice_party_name = $header_row['name'];
        $invoice_user_name = $header_row['u_name'];
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
    <title>SEARCH PURCHASE RECORDS - <?php echo $company_trademark; ?></title>
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
            <!-- Search Form Section -->
            <div class="card my-3 nodisplay_print" style="padding: 15px; background: #f8f9fa;">
                <form method="POST" action="">
                    <div class="row align-items-end">
                        <div class="col-md-4">
                            <label>Enter Bill Number:</label>
                            <input type="text" name="bill_no" class="form-control" placeholder="e.g. 1001" value="<?php echo $invoice_purchase_lab_item_bill_no; ?>" required>
                        </div>
                        <div class="col-md-2">
                            <button type="submit" name="search_bill" class="btn btn-primary btn-block">Search Record</button>
                        </div>
                    </div>
                </form>
            </div>

            <?php if($invoice_purchase_lab_item_bill_no != ""): ?>
            <div class="row">
                <div class="col-sm-12">
                    <table class="table table-sm table-bordered bg-white">
                        <caption style="caption-side: top; color: black;">
                            <h2 align="center">PURCHASE LAB ITEMS DETAILS</h2>
                            <div class="row border-bottom pb-2">
                                <div class="col">DATE: <strong><?php echo ($invoice_purchase_lab_item_date != "") ? date_format(date_create($invoice_purchase_lab_item_date),"d-M-Y") : "N/A"; ?></strong></div>
                                <div class="col">BILL #: <strong><?php echo $invoice_purchase_lab_item_bill_no; ?></strong></div>
                                <div class="col">PARTY BILL #: <strong><?php echo $invoice_party_bill_no; ?></strong></div>
                                <div class="col">PARTY NAME: <strong><?php echo $invoice_party_name; ?></strong></div>
                                <div class="col">LAB MANAGER: <strong><?php echo $invoice_user_name; ?></strong></div>
                            </div>
                        </caption>
                        <thead class="thead-light text-center">
                            <tr>
                                <th>S #</th>
                                <th>ITEM NAME</th>
                                <th>RATE</th>
                                <th>QUANTITY</th>
                                <th>AMOUNT</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $s = 0;
                            $total = 0;
                            $select = "SELECT purchase_lab_items.purchase_lab_item_id, 
                                              purchase_lab_items.invoice_purchase_lab_item_bill_no, 
                                              purchase_lab_items.purchase_lab_item_date, 
                                              items.name AS item_name, 
                                              purchase_lab_items.purchase_lab_item_rate, 
                                              purchase_lab_items.purchase_lab_item_quantity, 
                                              purchase_lab_items.purchase_lab_item_price, 
                                              purchase_lab_items.purchase_lab_item_status 
                                       FROM `purchase_lab_items` 
                                       INNER JOIN items ON purchase_lab_items.item_id = items.id 
                                       WHERE purchase_lab_items.invoice_purchase_lab_item_bill_no = '$invoice_purchase_lab_item_bill_no' 
                                       AND purchase_lab_items.purchase_lab_item_status > 0";
                            
                            $run = mysqli_query($con, $select);
                            if(mysqli_num_rows($run) > 0) {
                                while($row = mysqli_fetch_array($run)) {
                                    $s++; 
                                    $total += $row['purchase_lab_item_price'];
                                    ?>
                                    <tr>
                                        <td class="text-center"><?php echo $s; ?></td>
                                        <td><?php echo $row['item_name']; ?></td>
                                        <td class="text-right"><?php echo number_format($row['purchase_lab_item_rate'], 2); ?></td>
                                        <td class="text-center"><?php echo $row['purchase_lab_item_quantity']; ?></td>
                                        <td class="text-right"><?php echo number_format($row['purchase_lab_item_price'], 2); ?></td>
                                    </tr>
                                <?php }
                            } else {
                                echo "<tr><td colspan='5' class='text-center text-danger'>No records found for this Bill Number.</td></tr>";
                            } ?>
                        </tbody>
                        <tfoot>
                            <tr class="table-secondary">
                                <th colspan="4" class="text-right">GRAND TOTAL:</th>
                                <th class="text-right"><?php echo number_format($total, 2); ?></th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
            <?php else: ?>
                <div class="alert alert-info mt-5 text-center">Please enter a Bill Number above to view record details.</div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>