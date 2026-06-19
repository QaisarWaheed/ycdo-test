<?php
include 'includes/connect.php';

$role_title = '';
$roles = "SELECT * FROM roles WHERE id IN (SELECT role_id FROM users WHERE id = '$fr_id') ";
$run_roles = mysqli_query($con, $roles);
if ($run_roles && mysqli_num_rows($run_roles) === 1) {
	while ($row_role = mysqli_fetch_array($run_roles)) {
		$role_title = $row_role['title'];
	}
}

if (isset($_POST['date']) && $_POST['date'] !== '' && isset($_POST['br_id'])) {
	$date = preg_match('/^\d{4}-\d{2}-\d{2}$/', (string) $_POST['date']) ? $_POST['date'] : date('Y-m-d');
	$br_id = (int) $_POST['br_id'];
	$print_url = 'print_progess_report.php?' . http_build_query(array(
		'date' => $date,
		'br_id' => $br_id,
	));
	?>
<!DOCTYPE html>
<html>
<head><title>Opening progress report...</title></head>
<body>
<script>
window.open(<?php echo json_encode($print_url); ?>, '_blank');
window.location.replace('progress_report.php');
</script>
</body>
</html>
<?php
	exit;
}

include 'includes/head.php';
?>
	<title>Dashboard - <?php echo $company_trademark; ?></title>
<script src="js/jquery.min.js"></script>
<script src="js/selectize.min.js" integrity="sha256-+C0A5Ilqmu4QcSPxrlGpaZxJ04VjsRjKu+G82kl5UJk=" crossorigin="anonymous"></script>
<link rel="stylesheet" href="css/selectize.bootstrap3.min.css" integrity="sha256-ze/OEYGcFbPRmvCnrSeKbRTtjG4vGLHXgOqsyLFTRjg=" crossorigin="anonymous" />

</head>

<body class="background_image">

<div class="row" style="margin: 0px;">
	<div class="col-md-12" style="text-align: center;background: lightgreen;"><label><h1><?php echo $company_name; ?> </h1></label></div>
	<div class="col-md-3 background_whitesmoke">	<?php include 'left_navigation.php'; ?>	
    	<h3 style="margin-top: 350px;text-align: center;"><?php echo $_SESSION['fr_name'];if($_SESSION['is_incharge'] == 2){ echo " Incharge ";} ?>(<?php echo $role_title; ?>)</h3>
    </div>
    <div class = "col-md-9">
        <form method="POST" class = "container">
        <div class = "row">
            <div class = "col">
                <label>BRANCH</label>
                <select name = "br_id" class = "form-control" required>
<?php
require_once __DIR__ . '/../includes/report_helpers.php';
$progress_br_selected = (int) ($_POST['br_id'] ?? $branch_id);
echo fr_branch_select_options($con, (int) $branch_id, (int) $is_admin, (int) $is_incharge, $progress_br_selected, 'br_id');
?>
                </select>
            </div>
            <div class = "col">
                <label>DATE</label>
                <input required type = "date" value = "<?php echo date('Y-m-d'); ?>" name = "date" id = "date" class = "form-control" />
                <input type = "submit" name = "progress" value = "PROGRESS" class = "btn btn-sm btn-info" />
                <input type = "reset" name = "reset" value = "CLEAR" class = "btn btn-sm btn-danger" />
            </div>
        </div>
        </form>
    </div>
</div>
</body>
</html>
