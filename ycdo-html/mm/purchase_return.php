<?php 
include 'includes/connect.php'; 
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
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body class="background_image_ycdo">

<div>

	<div class="" style="margin: 10px 15px;">

		<div class="row">
		    
			<div class="col-md-12 noprint" style="text-align: center;">
			    <?php include 'top_row.php'; ?>
			</div>
			
			<div class="col-md-12 noprint" style="text-align: center;">
				<label><h1>Reutrn Purchase Items Form</h1></label>
			</div>

			<div class="col-md-12" style="text-align: center;">
				<label><h2>Purchase LIST</h2></label>
				<div class = "table">
				    <table class = "table" border = "solid">
				        <thead>
				            <tr class = "noprint">
				                <form method = "GET">
				                    <td colspan = "2"><h3>ENTER BILL NO</h3></td>
				                    <td colspan = "7"><input required type = "number" min = "1" value = "<?php if($_GET['bill_no'] != ''){echo $_GET['bill_no'];} ?>" max = "<?php echo $current_purchase_no; ?>" name = "bill_no" class = "form-control" /></td>
				                    <td><input type = "submit" value = "SEARCH" class = "btn btn-sm btn-info" /></td>
				                </form>
				            </tr>
				            <tr>
				                <th>#</th>
				                <th>Item </th>
				                <th>Category </th>
				                <th class = "noprint">Batch</th>
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
$select = "SELECT purchase_items.id, purchase_items.item_id, purchase_items.`invoice_no`, purchase_items.`batch_no`, purchase_items.`warrantee`, purchase_items.`mfg_date`, purchase_items.`expiry_date`, purchase_items.`total_price`, purchase_items.`quantity`, purchase_items.`per_item_price`, purchase_items.`tries`, purchase_items.`party_invoice_no`, items.name, categories.name AS category_name, parties.name AS party_name FROM purchase_items INNER JOIN items ON purchase_items.item_id = items.id INNER JOIN parties ON purchase_items.party_id = parties.id INNER JOIN categories ON items.category_id = categories.id WHERE purchase_items.invoice_no =  $bill_no AND purchase_items.item_id NOT IN (SELECT invoice_no FROM return_purchase_items WHERE return_purchase_items.purchase_id = $bill_no)  ";
$run = mysqli_query($con, $select);
if(mysqli_num_rows($run) > 0)
{
    while($row = mysqli_fetch_array($run))
    {
        $s = $s + 1;
        $id = $row['id'];
        $item_id = $row['item_id'];
        $invoice_no =$row['party_invoice_no'];
        $item_name = $row['name'];
        $category_name = $row['category_name'];
        $party_name = $row['party_name'];
        $amount = $row['quantity']*$row['per_item_price'];
        $total_amount = $total_amount + $amount;
        echo '	
        <tr>
            <td>'.$s.'</td>
            <td style = "text-align: left">'.$item_name.'</td>
            <td style = "text-align: left">'.$category_name.'</td>
            <td class = "noprint">'.$row['batch_no'].'</td>
            <td class = "noprint">'.$row['mfg_date'].'</td>
            <td>'.$row['expiry_date'].'</td>
            <td style = "text-align: right;">'.number_format($row['per_item_price'], 2).'</td>
            <td style = "text-align: right;">'.$row['quantity'].'</td>
            <th style = "text-align: right;">'.number_format($amount).'</th>
            <th>
                <button type="button" 
                        class="btn btn-danger btn-sm" 
                        onclick="openReturnModal(this)"
                        data-purchase-id="'.$id.'"
                        data-item-id="'.$item_id.'"
                        data-item="'.$item_name.'"
                        data-batch="'.$row['batch_no'].'"
                        data-rate="'.number_format($row['per_item_price'], 2).'"
                        data-maxqty="'.$row['quantity'].'">
                    Return
                </button>
            </th>
        </tr>
';

                // <a href="add_party.php?u_id='.$id.'" class = "btn btn-sm btn-primary">update</a>
    }
}
?>
        <tr>
            <th colspan = "4"></th>
            <th colspan = "3" class = "noprint"></th>
            <th colspan = "2" style = "text-align: right;"><?php echo intval($total_amount); ?></th>
            <td class="noprint">
        </tr>
				        </tboby>
<?php } ?>
<caption style = "caption-side: top; color: black;">
    <table class = "table table-bordered">
        <tr>
            <td>BILL NO</td>
            <th><?php echo $bill_no; ?></th>
            <td>INVOICE NO</td>
            <th><?php echo $invoice_no; ?></th>
            <td>PARTY</td>
            <th><?php echo $party_name; ?></th>
        </tr>
    </table>
</caption>
				    </table>
				</div>
			</div>
		</div>
	</div>
</div>

<div class="modal fade" id="returnModal" tabindex="-1" role="dialog">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Confirm Return</h5>
      </div>
      <div class="modal-body">
        <input type="hidden" id="modal_item_id_input">
        <input type="hidden" id="modal_item_rate_input">
        <input type="hidden" id="modal_purchase_id_input">       
        <p><strong>Item Id:</strong> <span id="modal_item_id"></span></p>
        <p><strong>Purchase Id:</strong> <span id="modal_purchase_id"></span></p>
        <p><strong>Rate:</strong> <span id="modal_item_rate_display"></span></p>
        <p><strong>Item Name:</strong> <span id="modal_item_name"></span></p>
        <p><strong>Batch:</strong> <span id="modal_batch"></span></p>
        <hr>
        <div class="form-group">
            <label>Quantity to Return (Max: <span id="modal_max_qty"></span>)</label>
            <input type="number" id="return_qty" class="form-control" min="1">
        </div>
        <div class="form-group">
            <label>Remarks</label>
            <input type="text" id="return_remarks" class="form-control" placeholder="Reason for return">
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-primary" onclick="submitReturn()">Confirm Submission</button>
      </div>
    </div>
  </div>
</div>

<script>
function openReturnModal(button) {
    const itemNameId = $(button).data('item-id');
    const itemPurchaseId = $(button).data('purchase-id'); // This gets the ID from the button
    const itemName = $(button).data('item');
    const batch = $(button).data('batch');
    const maxQty = $(button).data('maxqty');
    const rate = $(button).data('rate');

    // Populate the Hidden Inputs
    $('#modal_item_id_input').val(itemNameId); 
    $('#modal_item_rate_input').val(rate);
    $('#modal_purchase_id_input').val(itemPurchaseId); // SAVE THE PURCHASE ID HERE

    // Populate the Display Text
    $('#modal_item_id').text(itemNameId);
    $('#modal_purchase_id').text(itemPurchaseId);
    $('#modal_item_name').text(itemName);
    $('#modal_batch').text(batch);
    $('#modal_max_qty').text(maxQty);
    $('#modal_item_rate_display').text(rate);

    $('#return_qty').attr('max', maxQty).val('');
    $('#return_remarks').val('');

    $('#returnModal').modal('show');
}
function submitReturn() {
    const qty = $('#return_qty').val();
    
    if (!qty || qty <= 0) {
        Swal.fire('Input Error', 'Please enter a valid quantity', 'warning');
        return;
    }

    Swal.fire({
        title: 'Are you sure?',
        text: "You are about to return " + qty + " items.",
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Yes, return it!'
    }).then((result) => {
        if (result.isConfirmed) {
    // Get values from our hidden inputs
    const item_id = $('#modal_item_id_input').val();
    const purchase_id = $('#modal_purchase_id_input').val(); // ADD THIS LINE
    const rate_raw = $('#modal_item_rate_input').val().replace(/,/g, ''); // Remove commas if any
    const rate = parseFloat(rate_raw);
    const qty = parseInt($('#return_qty').val());

    if (!qty || qty <= 0) {
        alert("Please enter a valid quantity.");
        return;
    }

    // Calculate amount
    const totalAmount = (rate * qty).toFixed(2);

    const returnData = {
        invoice_no: $('input[name="bill_no"]').val(), 
        purchase_id: purchase_id, // Now this variable is defined!
        item_id: item_id,          
        qty: qty,
        rate: rate,
        amount: totalAmount,
        user_id: 1, 
        remarks: $('#return_remarks').val()
    };

    // AJAX call remains the same...
    $.ajax({
        url: 'process_purchase_return.php',
        method: 'POST',
        data: returnData,
        dataType: 'json',
        success: function(response) {
            if (response.status === 'success') {
                Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    text: response.message,
                    confirmButtonColor: '#3085d6',
                    confirmButtonText: 'Great!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $('#returnModal').modal('hide');
                        location.reload(); 
                    }
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Oops...',
                    text: response.message,
                });
            }
        },
        error: function(xhr) {
            Swal.fire({
                icon: 'warning',
                title: 'Connection Error',
                text: 'The server sent back an invalid response.',
                footer: '<a href="#">Why do I have this issue?</a>'
            });
        }
    });
}
    });
}
</script>
</body>
</html>
<script type="text/javascript" src="js/bootstrap.min.js"></script>
