<?php 
include 'includes/connect.php'; 
include 'includes/head.php';  ?>
	<title>Notified Medicine Purchase Price - <?php echo $company_trademark; ?></title>
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
    display: none !important;
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
			<div class="col-md-12" style="text-align: center;">
				<label><h1>Notified Medicine Purchase Item - Form</h1></label>
			</div>
			<div class="col-md-12 noprint">
				<form method = "POST">
					<div class="row">
						<div class="col-md-12">
							<label>PARTY Name</label>
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
						<div class="col-md-12" style="margin: 20px 0px;">

							<input type="submit" name="save" value="SELECT PARTY" class="btn btn-success">

							<input type="reset" name="clear" value="CLEAR FORM" class="btn btn-warning">
			<button type="button" class="btn btn-primary" data-toggle="modal" data-target="#addItemModal">
                <i class="fa fa-plus"></i> Add New Item Price
            </button>
						</div>
					</div>
				</form>
			</div>
		</div>
		<div class ="col-md-12">
            <div id="medicine_table_container" style="margin-top: 20px;">
                <table class="table table-bordered">
                <caption id="table_caption" style="caption-side: top; font-weight: bold; font-size: 1.2em; color: #333; margin-bottom: 10px;">
                    Statement for: <span id="display_party_name">Select a Party</span> 
                    <span style="float: right;">Date: <span id="current_date"><?php echo date('d-m-Y'); ?></span></span>
                </caption>
                    <thead>
                        <tr>
                            <th>Item ID</th>
                            <th>Retail Price</th>
                            <th>Purchase Price</th>
                            <th>Discount</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody id="medicine_data">
                        <!-- AJAX will inject rows here -->
                    </tbody>
                </table>
            </div>		
        </div>
	</div>
	
<div class="modal fade" id="editItemModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Update Medicine Price</h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <form id="updateItemForm">
                <div class="modal-body">
                    <!-- Hidden ID field to know which record to update -->
                    <input type="hidden" name="price_id" id="edit_price_id">
                    
                    <div class="form-group">
                        <label>Medicine Item ID</label>
                        <input type="text" id="edit_item_id" class="form-control" readonly>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <label>Purchase Price</label>
                            <input type="number" step="0.01" name="purchase_price" id="edit_purchase" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label>Retail Price</label>
                            <input type="number" step="0.01" name="retail_price" id="edit_retail" class="form-control" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Status</label>
                        <select name="status" id="edit_status" class="form-control">
                            <option value="1">Active</option>
                            <option value="2">Discontinue</option>
                            <option value="3">Block</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Update Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>	
	
<div class="modal fade" id="addItemModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Medicine Price</h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <form id="saveItemForm">
                <div class="modal-body">
                    <!-- Party (Read Only) -->
                    <div class="form-group">
                        <label>Selected Party</label>
                        <input type="text" id="modal_party_name" class="form-control" readonly>
                        <input type="hidden" name="party_id" id="modal_party_id">
                    </div>

                    <!-- Medicine Item Selection -->
                    <div class="form-group">
                        <label>Select Medicine</label>
                        <select name="item_id" class="form-control" required>
                            <option value="">-- Select Medicine --</option>
                            <?php
                            $items = mysqli_query($con, "SELECT id, name FROM items WHERE status = 1 ORDER BY name");
                            while($i = mysqli_fetch_assoc($items)) 
                            {
                                echo "<option value='{$i['id']}'>{$i['name']}</option>";
                            }
                            ?>
                        </select>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <label>Purchase Price</label>
                            <input type="number" step="0.01" name="purchase_price" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label>Retail Price</label>
                            <input type="number" step="0.01" name="retail_price" class="form-control" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-success">Save Price</button>
                </div>
            </form>
        </div>
    </div>
</div>
</div>
<!-- 1. jQuery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<!-- 2. Bootstrap -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
$(document).ready(function() {
    $('#party_id').on('input', function() {
        var party_id = $(this).val();
        var options = $('#party option');
        var party_name = "";

        // 1. Find the Name corresponding to the selected ID in the datalist
        for (var i = 0; i < options.length; i++) {
            if ($(options[i]).val() === party_id) {
                // Extracts the Name part (e.g., "ABC Pharma") from the option text
                party_name = $(options[i]).text();
                break;
            }
        }

        if (party_id !== "") {
            // 2. Update the Caption immediately
            if(party_name !== "") {
                $('#display_party_name').text(party_name);
            }

            // 3. Run the AJAX
            $.ajax({
                url: "fetch_notified_medicine_purchase_items.php",
                method: "POST",
                data: { party_id: party_id },
                success: function(data) {
                    $('#medicine_data').html(data);
                },
                error: function() {
                    $('#medicine_data').html('<tr><td colspan="6">Error loading data.</td></tr>');
                }
            });
        }
    });
// When the modal is about to show
    $('#addItemModal').on('show.bs.modal', function () {
        var currentPartyId = $('#party_id').val();
        var currentPartyName = $('#display_party_name').text();

        if(currentPartyId == "") {
            alert("Please select a party first!");
            return false; // Prevents modal from opening
        }

        $('#modal_party_id').val(currentPartyId);
        $('#modal_party_name').val(currentPartyName);
    });

    // Handle Form Submission
    $('#saveItemForm').on('submit', function(e) {
        e.preventDefault();
        
        $.ajax({
            url: "process_notificed_medicine_purhcase_items.php",
            method: "POST",
            data: $(this).serialize(),
            success: function(response) {
                alert("Data saved successfully!");
                $('#addItemModal').modal('hide');
                $('#saveItemForm')[0].reset();
                // Refresh the table
                $('#party_id').trigger('input'); 
            },
            error: function() {
                alert("Error saving data.");
            }
        });
    });    
    
// 1. When Update button is clicked, fill the modal
$(document).on('click', '.edit-btn', function() {
    var id = $(this).data('id');
    var retail = $(this).data('retail');
    var purchase = $(this).data('purchase');
    var status = $(this).data('status'); // Get status ID

    $('#edit_price_id').val(id);
    $('#edit_retail').val(retail);
    $('#edit_purchase').val(purchase);
    $('#edit_status').val(status); // Set the dropdown to match

    $('#editItemModal').modal('show');
});

    // 2. Handle the Update Form submission
    $('#updateItemForm').on('submit', function(e) {
        e.preventDefault();
        $.ajax({
            url: "update_notified_medicine_purchase_items.php",
            method: "POST",
            data: $(this).serialize(),
            success: function(response) {
                alert("Price updated successfully!");
                $('#editItemModal').modal('hide');
                $('#party_id').trigger('input'); // Refresh the table automatically
            }
        });
    });    
});
</script>
</body>
</html>