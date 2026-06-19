<?php 
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
include 'includes/connect.php'; 
include 'includes/head.php'; 
$category_idds = 0;
?>
	<title>Show Branch Stock - <?php echo $company_trademark; ?></title>
	<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
</head>

<body class="background_image_ycdo">

<div>

	<div class="" style="margin: 10px 15px;">

	<div class="row">
<?php
$data = '';
if(isset($_POST['category_idds']) && $_POST['category_idds'] != '')
{
    if($_POST['category_idds'] > 0)
    {
        $category_idds = $_POST['category_idds'];
        $data .= "SELECT * FROM `items` WHERE `category_id` = '$category_idds' AND `status` = '1' ";
    }
    else
    {
        $data .= "SELECT * FROM `items` WHERE `status` = '1' AND category_id IN (1,4,5,6,7,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25,26,27)  ";
    }
}
else
{
    $data = "SELECT * FROM `items` WHERE `status` = '1' AND category_id IN (1,4,5,6,7,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25,26,27) ";
}
$select = mysqli_query($con, $data);
?>

			<div class="col-md-12 noprint" style="text-align: center;">
			    <?php include 'top_row.php'; ?>
			</div>			
			<div class="col-md-12" style="text-align: center;">

			</div>
		<table class="table table-bordered table-hover">
			<thead>
				<caption style="caption-side: top;text-align: center;">
					<h3>SHOW ITEMS</h3>
				</caption>                      
				<tr>
					<th colspan="2">
					    <a class = "btn btn-success" href = "dashboard.php">Dashboard</a>
    			    </th>
                    <th>
                        <form method = "POST">
                        <select class = "form-control bg-light" onchange="this.form.submit()" name="category_idds">
                        <option <?php if($category_idds == 0){echo " SELECTED ";} ?> value = "0">ALL</option>
                        <?php
                            $select_category = "SELECT * FROM categories WHERE id IN (1,4,5,6,7,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25,26,27) AND status = '1' ORDER BY name ";
                            $run_category = mysqli_query($con, $select_category);
                            if(mysqli_num_rows($run_category) > 0)
                            {
                                while($row_category = mysqli_fetch_array($run_category))
                                {
                                $category_id = $row_category['id'];
                                $category_title = $row_category['name'];
                                if($category_id == $category_idds)
                                {
                                echo '<option SELECTED value = "'.$category_id.'" >'.$category_title.'</option>';
                                }
                            else
                            {
                            echo '<option value = "'.$category_id.'" >'.$category_title.'</option>';
                            }
                            }
                            }
                        ?>
                        </select>
                        </form>
                    </th>
                    <th colspan = "3">                
                        <div class="mb-3">
                            <input type="text" id="tableSearch" class="form-control" placeholder="Type to search items instantly...">
                        </div>
                    </th>
                </tr>
				<tr>
					<th>S #</th>
					<th>Item Name</th>
					<th>Category</th>
					<th>Min Limit</th>
					<th>Max Limit</th>
					<th>Box Size</th>
				</tr>
			</thead>
			<tbody id="itemTableBody">
<?php
$s = 0;
if (mysqli_num_rows($select) > 0) 
{
	while ($row = mysqli_fetch_array($select)) 
	{
		$s = $s + 1;
		$id = $row['id'];
		$quantity = $row['quantity'];
		$item_name = $row['name'];
		$category_name = show_category_name($row['category_id']);
		$min_limit = $row['min_limit'];
		$max_limit = $row['max_limit'];
		$item_box_size = $row['item_box_size'];

echo '
<tr>
	<td id = "id'.$id.'">'.$s.'</td>
	<td>'.$item_name.'</td>
	<td>'.$category_name.'</td>
	<td>'.$min_limit.'</td>
	<td>'.$max_limit.'</td>
    <td>
        <form class="update-box-form">
            <input type="hidden" name="item_id" value="'.$id.'" />
            <div class="input-group">
                <input type="number" name="item_box_size" value="'.$item_box_size.'" class="form-control" required />
                <button type="submit" class="btn btn-primary">UPDATE</button>
            </div>
            <small class="status-msg" style="display:none; color:green;">Updated!</small>
        </form>
    </td>
</tr>
';
	}
}
?>
			</tbody>
		</table>

	</div>
</div>

</div>
<script>
$(document).ready(function() {
    $('.update-box-form').on('submit', function(e) {
        e.preventDefault(); // Stop page reload

        var form = $(this);
        var formData = form.serialize(); // Collects item_id and item_box_size
        var statusMsg = form.find('.status-msg');

        $.ajax({
            url: 'process_update_store_item_box_size.php', // The PHP file that runs the SQL
            type: 'POST',
            data: formData,
            success: function(response) {
                if(response == "success") {
                    statusMsg.fadeIn().delay(1000).fadeOut(); // Show success indicator
                } else {
                    alert("Error updating record: " + response);
                }
            },
            error: function() {
                alert("Request failed.");
            }
        });
    });
    $("#tableSearch").on("keyup", function() {
        var value = $(this).val().toLowerCase(); // Get what user typed
        
        // Target the rows inside your specific table body
        $("#itemTableBody tr").filter(function() {
            // This line toggles the visibility: 
            // If the text is found (index > -1), show it. If not, hide it.
            $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1);
        });
    });
});    
</script>
</body>
</html>
<script type="text/javascript" src="js/bootstrap.min.js"></script>