<?php
include 'includes/connect.php';
require_once __DIR__ . '/../includes/report_helpers.php';

function fr_report_account_redirect_to_print(array $params): void
{
	$date = isset($params['date']) ? (string) $params['date'] : '';
	if (!preg_match('/^\d{4}-\d{2}/', $date)) {
		$date = date('Y-m');
	} else {
		$date = substr($date, 0, 7);
	}
	$br_id = (int) ($params['br_id'] ?? 0);
	if ($br_id < 1) {
		http_response_code(400);
		exit('Branch is required.');
	}
	header('Location: print_report_account.php?' . http_build_query(array('date' => $date, 'br_id' => $br_id)));
	exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['date']) && isset($_POST['br_id'])) {
	fr_report_account_redirect_to_print($_POST);
}
if (!empty($_GET['date']) && isset($_GET['br_id']) && (isset($_GET['go']) || isset($_GET['progress']))) {
	fr_report_account_redirect_to_print($_GET);
}

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
	<title>Accounts Report - <?php echo $company_trademark; ?></title>
</head>

<body class="background_image">

<div class="row" style="margin: 0px;">
	<div class="col-md-12" style="text-align: center;background: lightgreen;"><label><h1><?php echo $company_name; ?> </h1></label></div>
	<div class="col-md-3 background_whitesmoke">	<?php include 'left_navigation.php'; ?>
    	<h3 style="margin-top: 350px;text-align: center;"><?php echo $_SESSION['fr_name'];if($_SESSION['is_incharge'] == 2){ echo " Incharge ";} ?>(<?php echo $role_title; ?>)</h3>
    </div>
    <div class="col-md-9" style="padding:20px;">
        <p><strong>Accounts Report</strong> — choose branch and month, then click Progress. The report opens in this window.</p>
        <form action="print_report_account.php" method="GET" class="container-fluid">
        <div class="row">
            <div class="col-md-6">
                <label>BRANCH</label>
                <select name="br_id" class="form-control" required>
<?php
$progress_br_selected = (int) ($_GET['br_id'] ?? $branch_id);
echo fr_branch_select_options($con, (int) $branch_id, (int) $is_admin, (int) $is_incharge, $progress_br_selected, 'br_id');
?>
                </select>
            </div>
            <div class="col-md-6">
                <label>MONTH</label>
                <input required type="month" value="<?php echo htmlspecialchars(date('Y-m'), ENT_QUOTES, 'UTF-8'); ?>" name="date" class="form-control" />
                <button type="submit" class="btn btn-primary" style="margin-top:10px;">PROGRESS</button>
                <button type="reset" class="btn btn-danger" style="margin-top:10px;">CLEAR</button>
            </div>
        </div>
        </form>
    </div>
</div>
</body>
</html>
