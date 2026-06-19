<?php
include 'includes/connect.php';
require_once __DIR__ . '/../includes/summary_form_actions.php';
require_once __DIR__ . '/../includes/report_helpers.php';
?>
<?php include 'includes/head.php'; ?>
<title>Account Summary - <?php echo $company_trademark; ?></title>
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
		<?php fr_report_form_open('print_account_summary.php', 'account_summary.php'); ?>
			<div class="row">
				<div class="col-md-12 col-sm-12 col-xs-12">
					<label for="br_id">Branch</label>
					<select class="form-control" style="min-width: 200px;text-transform: uppercase;" name="br_id" required>
<?php
$account_br_selected = (int) ($_GET['br_id'] ?? $branch_id);
echo fr_branch_select_options($con, (int) $branch_id, (int) $is_admin, (int) $is_incharge, $account_br_selected, 'br_id');
?>
					</select>
				</div>
				<div class="col-md-12 col-sm-12 col-xs-12">
					<label for="month">Month</label>
					<input type="month" name="month" class="form-control" required id="month" value="<?php echo date('Y-m'); ?>">
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
