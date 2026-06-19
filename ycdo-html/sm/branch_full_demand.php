<?php include 'includes/connect.php'; ?>
<?php include 'includes/head.php'; ?>
	<title><?php echo $branch_name; ?> CHECK / ISSUE FULL DEMAND - <?php echo $company_trademark; ?></title>
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

<body class="background_image">

<div class="row" style="margin: 0px;">
	<div class="col-md-12" style="text-align: center;background: lightgreen;">
		<label><h1>YCDO </h1></label>
	</div>
	<div class="col-md-3 background_whitesmoke noprint">
		<?php include 'left_navigation.php'; ?>
		<h3 style="margin-top: 350px;text-align: center;">USER: <?php echo $_SESSION['sm_name']; ?></h3>
	</div>
	<div class="col-md-9">
	    <div class = "row">
	        <div class = "col-md-12">
	            <form method = "POST">
                    <div class="form-group">
                        <label for="br_id">SELECT BRANCH</label>
                        <select name = "br_id" id = "br_id" class = "form-control">
                            <?php
                                $select = "SELECT * FROM branchs WHERE status = '1' ";
                                $run = mysqli_query($con, $select);
                                if(mysqli_num_rows($run) > 0)
                                {
                                    while($row = mysqli_fetch_array($run))
                                    {
                                        $br_id = $row['id'];
                                        $br_address = $row['address'];
                                        if($_POST['br_id'] == $br_id)
                                        {
                                            echo '<option SELECTED value = "'.$br_id.'">'.$br_address.'</option>';
                                        }
                                        else
                                        {
                                            echo '<option value = "'.$br_id.'">'.$br_address.'</option>';
                                        }
                                    }
                                }
                                else
                                {
                                    echo '<option value = ""></option>';
                                }
                            ?>
                        </select>
                    </div>
                    <div class = "form-group">
                        <input type = "submit" value = "CHECK FULL DEMAND" name = "submit" class = "btn btn-info btn-md" />
                    </div>
	            </form>
	        </div>
	    </div>
<?php
if(isset($_POST['br_id']) && $_POST['br_id'] != '')
{
    $br_id = $_POST['br_id'];
    if($_POST['category_idds'] > 0)
    {
        $category_idds = $_POST['category_idds'];
        $select = "SELECT item_register_to_branches.id AS reg_item_id, items.id AS item_id, categories.name AS category_name, items.name AS item_name, items.quantity AS store_quantity, item_register_to_branches.quantity, item_register_to_branches.min_limit, item_register_to_branches.max_limit, items.item_box_size FROM `item_register_to_branches` INNER JOIN items ON item_register_to_branches.item_id = items.id AND item_register_to_branches.branch_id = $br_id INNER JOIN categories ON items.category_id = categories.id WHERE branch_id = $br_id AND item_id IN (SELECT id FROM items WHERE items.category_id IN ($category_idds)) ORDER BY category_id,`item_name` ASC ";
    }
    else
    {
        $select = "SELECT item_register_to_branches.id AS reg_item_id, items.id AS item_id, categories.name AS category_name, items.name AS item_name, items.quantity AS store_quantity, item_register_to_branches.quantity, item_register_to_branches.min_limit, item_register_to_branches.max_limit, items.item_box_size FROM `item_register_to_branches` INNER JOIN items ON item_register_to_branches.item_id = items.id AND item_register_to_branches.branch_id = $br_id INNER JOIN categories ON items.category_id = categories.id WHERE branch_id = $br_id AND item_id IN (SELECT id FROM items WHERE items.category_id IN (1,4,5,6,7,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25,26,27)) ORDER BY category_id,`item_name` ASC ";
    }
?>
	    <div class = "row">
	        <div class = "col-md-12">
	            <div class = "table table-bordered">
	                <table class = "table table-sm">
	                    <thead>	                        
	                        <tr>
	                            <th colspan = "2"></th>
	                            <th>
	                               <form method = "POST">
	                                   <input type = "hidden" name = "br_id" value = "<?php echo $br_id; ?>" />
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
	                            <th colspan = "4"></th>
	                        </tr>

	                        <tr>
	                            <th>Sr. No</th>
	                            <th>Item Name</th>
	                            <th>Category</th>
	                            <th>Box Size</th>
	                            <th>Store Available</th>
	                            <th>Branch Available</th>
	                            <th>Min Limit</th>
	                            <th>Max Limit</th>
	                            <th>Demand Qty</th>
	                            <th>Issue Qty</th>
	                        </tr>
	                    </thead>
	                    <tbody>
                        <form action = "process_branch_short_demand.php" method = "POST">
<?php
$s = 0;
$run = mysqli_query($con, $select);
if(mysqli_num_rows($run) > 0)
{
    while($row = mysqli_fetch_array($run))
    { 
        $reg_item_id = $row['reg_item_id'];
        $item_box_size = $row['item_box_size'];
        $item_id = $row['item_id'];
        $category = $row['category_name'];
        $item_name = $row['item_name'];
        $quantity = $row['quantity'];
        $store_quantity = $row['store_quantity'];
        $min_limit = $row['min_limit'];
        $max_limit = $row['max_limit'];
        if($quantity < $max_limit)
        {
            $s = $s + 1;
        ?>
                            <tr>
                                <td><?php echo $s; ?></td>
                                <td><?php echo $item_name; ?></td>
                                <td><?php echo $category; ?></td>
                                <td><?php echo $item_box_size; ?></td>
                                <td><?php echo $store_quantity; ?></td>
                                <td><?php echo $quantity; ?></td>
                                <td><?php echo $min_limit; ?></td>
                                <td><?php echo $max_limit; ?></td>
                                <td><?php echo abs($quantity-$max_limit); ?></td>
                                <td>
                                    <?php if($store_quantity < 1)
                                    {
                                        
                                    }                              
                                    elseif($store_quantity > ($max_limit-$quantity))
                                    { ?>
                                        <input type = "hidden" name = "reg_item_id[]" value = "<?php echo $reg_item_id; ?>" />
                                        <input type = "hidden" name = "item_box_size[]" value = "<?php echo $item_box_size; ?>" />
                                        <input type = "hidden" name = "item_id[]" value = "<?php echo $item_id; ?>" />
                                        <input type = "number" class = "form-control" name = "issue_quantity[]" value = "<?php echo $max_limit-$quantity; ?>" max = "<?php echo $store_quantity; ?>"/>
                                    <?php } 
                                    else{ ?>
                                        <input type = "hidden" name = "reg_item_id[]" value = "<?php echo $reg_item_id; ?>" />
                                        <input type = "hidden" name = "item_box_size[]" value = "<?php echo $item_box_size; ?>" />
                                        <input type = "hidden" name = "item_id[]" value = "<?php echo $item_id; ?>" />
                                        <input type = "number" name = "issue_quantity[]" value = "<?php echo $store_quantity; ?>" max = "<?php echo $store_quantity; ?>" class = "form-control"/>
                                    <?php } ?>
                                </td>
                            </tr>
<?php    }
    }
}
?>
	                    </tbody>
	                    <tfoot>
	                        <tr>
	                            <td colspan = "10">
        	                        <div>
                                        <input type = "hidden" name = "br_id" value = "<?php echo $br_id; ?>" />
        	                            <input type = "submit" value = "ISSUE BRANCH FULL DEMAND" id = "submit_btn" class = "btn btn-primary btn-sm" name = "process_branch_short_demand" />
        	                        </div>
	                            </td>
	                        </tr>
	                    </tfoot>
                        </form>
	                </table>
	            </div>
	        </div>
	    </div>
<?php } ?>	    
	</div>
			
	</div>
</div>

</body>
</html>