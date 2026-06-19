<?php include 'includes/connect.php'; 
if (isset($_POST['save'])) 
{
	$party_id = $_POST['party_id'];
	$company_id = $_POST['company_id'];
	$item_id = $_POST['item_id'];
	$invoice_no = $_POST['invoice_no'];
	$party_invoice_no = $_POST['party_invoice_no'];
	$ingredient = $_POST['ingredient'];
	$total_price = $_POST['total_price'];
	$quantity = $_POST['quantity'];
	$per_item_price = $_POST['cash'];

// --- NEW VALIDATION START ---
    // Check if there is a notified price for this item and party
    $check_notified = mysqli_query($con, "SELECT notified_medicine__purchase_price_purchase_price 
                                          FROM notified_medicine__purchase_prices 
                                          WHERE item_id = '$item_id' AND party_id = '$party_id'");
    
    if (mysqli_num_rows($check_notified) > 0) 
    {
        $notified_row = mysqli_fetch_array($check_notified);
        $notified_rate = $notified_row['notified_medicine__purchase_price_purchase_price'];

        // Compare the submitted rate with the notified rate
        // We use floatval to ensure numeric comparison
        if (floatval($per_item_price) != floatval($notified_rate)) 
        {
            ?>
            <script>
                alert('ERROR: Purchase Rate (<?php echo $per_item_price; ?>) does not match the Notified Rate (<?php echo $notified_rate; ?>) for this Party. Data NOT saved.');
                window.history.back();
            </script>
            <?php
            exit(); // Stop further execution
        }
    }
    // --- NEW VALIDATION END ---
    
	$insert = "INSERT INTO `purchase_items`
	( `party_id`, `company_id`, `item_id`, `total_price`, `quantity`, `per_item_price`, `mm_id`, `invoice_no`, `party_invoice_no`, `ingredient`, `created`) 
	VALUES 
	('$party_id', '$company_id', '$item_id', '$total_price', '$quantity', '$per_item_price', '$user_id', '$invoice_no', '$party_invoice_no', '$ingredient', '$current_date')";
	$run = mysqli_query($con, $insert);
	if ($run) 
	{
		mysqli_query($con, "
			UPDATE items SET 
			`purchase` = '$per_item_price'
			WHERE id = '$item_id'
			");
	?>
 	<script>
		alert('DATA SAVE SUCCESSFULLY');
	</script>
	<?php
	}
	else
	{
		echo $con->error;
	}
}
?>
<?php 
include 'includes/head.php'; 

$current_purchase_no = mysqli_num_rows(mysqli_query($con, "SELECT DISTINCT `invoice_no` FROM `purchase_items`"));
$current_bill_no = get_party_bill_no($current_purchase_no);
?>
	<title>Add Item Purchase - <?php echo $company_trademark; ?></title>
<style>
@page 
{
  size: A4;
  margin: 10px 0px 10px 0px;
}
@media print 
{
html, body 
{
    width: 210mm;
    height: 297mm;
    font-size: 9px;
}
.noprint
{
    display: none;
}
}    
</style>	
</head>

<body class="background_image_ycdo">

<div>

	<div class="" style="margin: 10px 15px;">

		<div class="row">
		    
			<div class="col-md-12 noprint" style="text-align: center;">
			    <?php include 'top_row.php'; ?>
			</div>
			
			<div class="col-md-12 noprint" style="text-align: center;">
				<label><h1>Add Item Purchase Form</h1></label>
			</div>
			<div class="col-md-12 noprint">

				<form method = "POST">

					<div class="row">

						<div class="col-md-2">
							<label for="invoice_no">Receive No</label>
							<input type="number" min="0" max = "<?php echo intval($current_purchase_no+1); ?>" id="invoice_no" value = "<?php echo $current_purchase_no; ?>" required name="invoice_no" class="form-control">
						</div>
						<div class="col-md-2">
							<label for="party_invoice_no">Invoice No</label>
							<input type="text" id="party_invoice_no" value = "<?php echo $current_bill_no; ?>"  required name="party_invoice_no" class="form-control">
						</div>
						<div class="col-md-2">
							<label>Party</label>
                            <input list="party" name="party_id" id="party_id" class = "form-control">
                                <datalist id="party">
    								<?php
    								$categories = mysqli_query($con, "SELECT * FROM `parties` WHERE status = '1' ORDER BY name");
    								if (mysqli_num_rows($categories) > 0) {
    									while ($row_category = mysqli_fetch_array($categories)) {
    										$cat_id = $row_category['id'];
    										$cat_name = $row_category['name'];
    										$cat_address = $row_category['address'];
    										echo '<option value="'.$cat_id.'">'.$cat_name.' ('.$cat_address.')</option>';
    									}
    								}
    								?>
                                </datalist>				
                        </div>
						<div class="col-md-2">
							<label>Company</label>
							<select name="company_id" class="form-control" required>
								<option value="">Select Company</option>
								<?php
								$compaines = mysqli_query($con, "SELECT * FROM `item_companies` WHERE status = '1' ORDER BY name");
								if (mysqli_num_rows($compaines) > 0) {
									while ($row_company = mysqli_fetch_array($compaines)) {
										$com_id = $row_company['id'];
										$com_name = $row_company['name'];
										echo '<option value="'.$com_id.'">'.$com_name.'</option>';
									}
								}
								?>
							</select>
						</div>
						<div class="col-md-4">
                            <label>Item</label>
                            <input required list="browsers" name="item_id" id="item_id" class = "form-control" onchange="myFunction(this)">
                            <datalist id="browsers">
                                <?php
                                $items = mysqli_query($con, "SELECT * FROM `items` WHERE status = '1' ORDER BY name");
                                if (mysqli_num_rows($items) > 0) {
                                while ($row_item = mysqli_fetch_array($items)) {
                                $item_id = $row_item['id'];
                                $item_name = $row_item['name'];
                                $category_id = $row_item['category_id'];
                                $categories = mysqli_query($con, "SELECT name FROM `categories` WHERE id = '$category_id' ");
                                if (mysqli_num_rows($categories) == 1) 
                                {
                                while ($row_category = mysqli_fetch_array($categories)) 
                                {
                                $cat_name = $row_category['name'];
                                }
                                }
                                echo '<option value="'.$item_id.'">'.$item_name.' - '.$cat_name.'</option>';
                                }
                                }
                                ?>
                            </datalist>
						</div>
                        <div class="col-md-2">
                            <label for="total_price">Total Amount</label>
                            <input oninput="myFunction1()" type="number" step="0.01" min="0" id="total_price" name="total_price" class="form-control" value="1">
                        </div>
                        
                        <div class="col-md-2">
                            <label for="quantity">Quantity</label>
                            <input oninput="myFunction1()" type="number" min="1" id="quantity" name="quantity" class="form-control" value="1">
                        </div>
                        
                        <div class="col-md-2">
                            <label for="cash">PIC (Current)</label>
                            <input type="text" name="cash" id="cash" class="form-control" readonly value="1.00">
                        </div>
                        <div class="col-md-2">
                            <label for="cash_1">Rate (Last)</label>
                            <input type="text" id="cash_1" class="form-control" readonly value="0">
                        </div>
                        <div class="col-md-2">
                            <label for="cash_2">Rate (2nd Last)</label>
                            <input type="text" id="cash_2" class="form-control" readonly value="0">
                        </div>
						<div class="col-md-2">
							<label for="ingredient">Trade Name / Ingredient</label>
							<input type="text" id="ingredient" name="ingredient" class="form-control">
						</div>
						<div class="col-md-12" style="margin: 20px 0px;">

							<input type="submit" name="save" value="SAVE ITEM" class="btn btn-success">

							<input type="reset" name="clear" value="CLEAR FORM" class="btn btn-warning">
							<a class = "btn btn-primary" href = "purchase_return.php">Purchase Return</a>
							<a class = "btn btn-info" href = "notified_medicine_purchase_prices.php">Notified Medicines</a>
						</div>
					</div>

				</form>
			</div>

			<div class="col-md-12" style="text-align: center;">
				<label><h1>Item Purchase LIST</h1></label>
				<div class = "table">
				    <table class = "table" border = "solid">
				        <thead>
				            <tr class = "noprint">
				                <form method = "GET">
				                    <td colspan = "2"><h3>ENTER BILL NO</h3></td>
				                    <td colspan = "10"><input required type = "number" min = "1" value = "<?php echo ($_GET['bill_no']>0 ? $_GET['bill_no'] : 0); ?>" max = "<?php echo $current_purchase_no; ?>" name = "bill_no" class = "form-control" /></td>
				                    <td><input type = "submit" value = "SEARCH" class = "btn btn-sm btn-info" /></td>
				                </form>
				            </tr>
				            <tr>
				                <th>#</th>
				                <th>Bill</th>
				                <th>Invoice</th>
				                <th>Item </th>
				                <th>Category </th>
				                <th>Party </th>
				                <th class = "noprint">Batch</th>
				                <th class = "noprint">Drap</th>
				                <th class = "noprint">MFG</th>
				                <th>EXPIRY</th>
				                <th>RATE</th>
				                <th>QTY</th>
				                <th>AMOUNT</th>
				                <th class = "noprint">Action</th>
				            </tr>
				        </thead>
				        <tboby>
<?php
$s = 0;
$total_amount = 0;
if(isset($_GET['bill_no']) && $_GET['bill_no'] != '')
{
    $bill_no = $_GET['bill_no'];
    $select = "SELECT purchase_items.invoice_no, purchase_items.party_invoice_no, purchase_items.batch_no, purchase_items.warrantee, purchase_items.mfg_date, purchase_items.expiry_date, purchase_items.per_item_price, purchase_items.quantity, items.name AS item_name, categories.name AS category_name, parties.name AS party_name, notified_medicine__purchase_price_purchase_price FROM purchase_items LEFT JOIN items ON purchase_items.item_id = items.id INNER JOIN categories ON items.category_id = categories.id LEFT JOIN notified_medicine__purchase_prices ON items.id = notified_medicine__purchase_prices.item_id AND notified_medicine__purchase_prices.party_id = purchase_items.party_id INNER JOIN parties ON purchase_items.party_id = parties.id WHERE invoice_no  = '$bill_no' ";
}
else
{
    $select = "SELECT purchase_items.invoice_no, purchase_items.party_invoice_no, purchase_items.batch_no, purchase_items.warrantee, purchase_items.mfg_date, purchase_items.expiry_date, purchase_items.per_item_price, purchase_items.quantity, items.name AS item_name, categories.name AS category_name, parties.name AS party_name, notified_medicine__purchase_price_purchase_price FROM purchase_items LEFT JOIN items ON purchase_items.item_id = items.id INNER JOIN categories ON items.category_id = categories.id LEFT JOIN notified_medicine__purchase_prices ON items.id = notified_medicine__purchase_prices.item_id AND notified_medicine__purchase_prices.party_id = purchase_items.party_id INNER JOIN parties ON purchase_items.party_id = parties.id WHERE invoice_no  = 0 ";
}
$run = mysqli_query($con, $select);
if(mysqli_num_rows($run) > 0)
{
    while($row = mysqli_fetch_array($run))
    {
        $s = $s + 1;
        $id = $row['id'];
        $notified_medicine__purchase_price_purchase_price = $row['notified_medicine__purchase_price_purchase_price'];
        $amount = $row['quantity']*$row['per_item_price'];
        $total_amount = $total_amount + $amount;
        if(is_null($notified_medicine__purchase_price_purchase_price))
        {
            echo '				            
            <tr>
                <td>'.$s.'</td>
                <td>'.$row['invoice_no'].'</td>
                <td>'.$row['party_invoice_no'].'</td>
                <td style = "text-align: left">'.$row['item_name'].'</td>
                <td style = "text-align: left">'.$row['category_name'].'</td>
                <td style = "text-align: left">'.$row['party_name'].'</td>
                <td class = "noprint">'.$row['batch_no'].'</td>
                <td class = "noprint">'.$row['warrantee'].'</td>
                <td class = "noprint">'.$row['mfg_date'].'</td>
                <td>'.$row['expiry_date'].'</td>
                <td style = "text-align: right;">'.number_format($row['per_item_price'], 2).'</td>
                <td style = "text-align: right;">'.$row['quantity'].'</td>
                <th style = "text-align: right;">'.number_format($amount).'</th>
                <td class="noprint">
                </td>
            </tr>';
        }
        else
        {
            echo '				            
            <tr>
                <td>'.$s.'</td>
                <td>'.$row['invoice_no'].'</td>
                <td>'.$row['party_invoice_no'].'</td>
                <td style = "text-align: left">'.$row['item_name'].'<br><span style = "font-size: 0.8em;color: red;">Notified Rate: '.$notified_medicine__purchase_price_purchase_price.'</span></td>
                <td style = "text-align: left">'.$row['category_name'].'</td>
                <td style = "text-align: left">'.$row['party_name'].'</td>
                <td class = "noprint">'.$row['batch_no'].'</td>
                <td class = "noprint">'.$row['warrantee'].'</td>
                <td class = "noprint">'.$row['mfg_date'].'</td>
                <td>'.$row['expiry_date'].'</td>
                <td style = "text-align: right;">'.number_format($row['per_item_price'], 2).'</td>
                <td style = "text-align: right;">'.$row['quantity'].'</td>
                <th style = "text-align: right;">'.number_format($amount).'</th>
                <td class="noprint">
                </td>
            </tr>';
        }

                // <a href="add_party.php?u_id='.$id.'" class = "btn btn-sm btn-primary">update</a>
    }
}
?>
        <tr>
            <th colspan = "7"></th>
            <th colspan = "3" class = "noprint"></th>
            <th colspan = "2" style = "text-align: right;"><?php echo intval($total_amount); ?></th>
            <td class="noprint">
        </tr>
				        </tboby>
				    </table>
				</div>
			</div>
			
		</div>

	</div>

</div>


</body>
</html>
<script type="text/javascript" src="js/bootstrap.min.js"></script>
<script>
function myFunction(element) {
    var itemId = element.value;
    
    if (itemId) {
        fetch('get_last_price.php?item_id=' + itemId)
            .then(response => response.json())
            .then(data => {
                if(data.success) {
                    // Update both fields
                    document.getElementById('cash_1').value = data.last_price;
                    document.getElementById('cash_2').value = data.prev_price;
                } else {
                    document.getElementById('cash_1').value = "0";
                    document.getElementById('cash_2').value = "0";
                }
            })
            .catch(error => {
                console.error('Error fetching price:', error);
                document.getElementById('cash_1').value = "0";
                document.getElementById('cash_2').value = "0";
            });
    }
}
function myFunction1() {
    // Get the values from the inputs
    var totalAmount = document.getElementById('total_price').value;
    var quantity = document.getElementById('quantity').value;
    var picCurrent = document.getElementById('cash');

    // Convert to numbers
    var total = parseFloat(totalAmount);
    var qty = parseFloat(quantity);

    // Calculate only if both numbers are valid and quantity is greater than 0
    if (!isNaN(total) && !isNaN(qty) && qty > 0) {
        var result = total / qty;
        
        // Update the PIC(Current) field - fixed to 2 decimal places
        picCurrent.value = result.toFixed(2);
    } else {
        picCurrent.value = "0";
    }
}
</script>