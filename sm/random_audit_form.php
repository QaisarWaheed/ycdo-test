<?php include 'includes/connect.php'; ?>
<?php include 'includes/head.php'; ?>
	<title>Dashboard - <?php echo $company_trademark; ?></title>
<style>
        .item-select { height: 300px !important; }
</style>
</head>

<body class="background_image">

<div class="row" style="margin: 0px;">
	<div class="col-md-12" style="text-align: center;background: lightgreen;">
		<label><h1>YCDO </h1></label>
	</div>
	<div class="col-md-12">
        <div class=" mt-5">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h4>Select Items for Stock Check</h4>
                </div>
                <div class="card-body">
                    <form action="action_random_audit_form.php" method="POST">
                        <div class="form-group">
                            <label>Select Items (Hold Ctrl to select multiple):</label>
                            <select name="selected_items[]" class="form-control item-select" multiple required>
                                <?php
                                $query = mysqli_query($con, "SELECT items.id, items.name, categories.name AS category_name FROM items INNER JOIN categories ON items.category_id = categories.id WHERE items.status = '1' AND items.category_id IN (1,4,5,6,7,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25,26,27) ORDER BY items.category_id ");
                                while ($row = mysqli_fetch_assoc($query)) {
                                    echo "<option value='".$row['id']."'>".$row['name']." - ".$row['category_name']."</option>";
                                }
                                ?>
                            </select>
                            <small class="form-text text-muted">Pick 10 items to audit across branches.</small>
                        </div>
                        <button type="submit" name="submit_items" class="btn btn-success">Generate Branch Audit Sheet</button>
                        <a href = "listing_random_audit.php" class="btn btn-info">Listing </a>
                        <a href = "dashboard.php" class="btn btn-outline-dark">Dashboard </a>
                    </form>
                </div>
            </div>
        </div>
	</div>
</div>

</body>
</html>