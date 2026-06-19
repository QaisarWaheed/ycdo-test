<?php
include 'includes/connect.php';
require_once __DIR__ . '/../includes/summary_form_actions.php';
?>
<?php include 'includes/head.php'; ?>
<title>Complete Summary - <?php echo $company_trademark; ?></title>
</head>

<body class="">
<div class="row" style="margin: 0px;">
	<div class="col-md-12" style="text-align: center;background: lightgreen;">
		<label><h1>YCDO </h1></label>
	</div>
	<div class="col-md-3 background_whitesmoke" style="min-height: 450px">
		<?php include 'left_navigation.php'; ?>
	</div>
	<?php fr_summary_content_open(); ?>
	<div class="row">
		<div class="col-md-12 col-sm-12 col-xs-12">
		<?php fr_report_form_open('print_complete_summery.php', 'user_complete_summary.php'); ?>
			<input type="hidden" name="br_id" value="<?php echo (int) $branch_id; ?>" />
			<input type="hidden" name="un" value="ALL" />
			<div class="row">
				<div class="col-md-6 col-sm-6 col-xs-12">
					<label for="s">From:</label>
					<input type="date" name="s" class="form-control" required id="from_date" value="<?php echo date('Y-m-d'); ?>">
				</div>
				<div class="col-md-6 col-sm-6 col-xs-12">
					<label for="e">To:</label>
					<input type="date" name="e" class="form-control" required id="to_date" value="<?php echo date('Y-m-d'); ?>">
				</div>
				<div class="col-md-12 col-sm-12 col-xs-12">
					<label>SELECT USER</label>
					<select class="form-control" style="min-width: 200px;text-transform: uppercase;" name="u">
						<option value="0">ALL</option>
<?php
$user = "SELECT * FROM users WHERE role_id IN (1, 2, 7) AND status = 1 AND branch_id = '$branch_id' ORDER BY `u_name` ASC ";
$run_user = mysqli_query($con, $user);
if ($run_user && mysqli_num_rows($run_user) > 0) {
    while ($row_user = mysqli_fetch_array($run_user)) {
        echo '<option value="'.$row_user['id'].'">'.htmlspecialchars($row_user['u_name']).'</option>';
    }
} else {
    echo '<option value="">No users found</option>';
}
?>
					</select>
				</div>
				<?php fr_summary_form_actions('go', 'PRINT SUMMARY'); ?>
			</div>
		</form>
		</div>
	</div>
	</div>
</div>
</body>
</html>
<script type="text/javascript" src="js/bootstrap.min.js"></script>
