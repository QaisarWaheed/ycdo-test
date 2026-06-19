<?php
include 'includes/connect.php';
require_once __DIR__ . '/../includes/report_helpers.php';
require_once __DIR__ . '/../includes/summary_form_actions.php';

$role_title = '';
$roles = "SELECT * FROM roles WHERE id IN (SELECT role_id FROM users WHERE id = '$fr_id') ";
$run_roles = mysqli_query($con, $roles);
if ($run_roles && mysqli_num_rows($run_roles) === 1) {
	while ($row_role = mysqli_fetch_array($run_roles)) {
		$role_title = $row_role['title'];
	}
}

include 'includes/head.php';
?>
	<title>Month Report - <?php echo $company_trademark; ?></title>
</head>

<body class="background_image">

<div class="row" style="margin: 0px;">
	<div class="col-md-12" style="text-align: center;background: lightgreen;"><label><h1><?php echo $company_name; ?> </h1></label></div>
	<div class="col-md-3 background_whitesmoke">	<?php include 'left_navigation.php'; ?>	
    	<h3 style="margin-top: 350px;text-align: center;"><?php echo $_SESSION['fr_name'];if($_SESSION['is_incharge'] == 2){ echo " Incharge ";} ?>(<?php echo $role_title; ?>)</h3>
    </div>
    <div class="col-md-9">
        <?php fr_report_form_open('print_report_month.php', 'report_month.php'); ?>
        <div class="row container">
            <div class="col">
                <label>BRANCH</label>
                <select name="br_id" class="form-control" required>
<?php
$progress_br_selected = (int) ($_GET['br_id'] ?? $branch_id);
echo fr_branch_select_options($con, (int) $branch_id, (int) $is_admin, (int) $is_incharge, $progress_br_selected, 'br_id');
?>
                </select>
            </div>
            <div class="col">
                <label>MONTH</label>
                <input required type="month" value="<?php echo date('Y-m'); ?>" name="date" id="date" class="form-control" />
                <input type="submit" name="go" value="PROGRESS" class="btn btn-sm btn-info" style="margin-top:8px;" />
                <input type="reset" value="CLEAR" class="btn btn-sm btn-danger" style="margin-top:8px;" />
            </div>
        </div>
        </form>
    </div>
</div>
</body>
</html>
