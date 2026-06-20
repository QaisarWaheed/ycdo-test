<?php include 'includes/connect.php'; ?>
<?php include 'includes/head.php'; 

$roles = "SELECT * FROM roles WHERE id IN (SELECT role_id FROM users WHERE id = '$user_id') ";
$run_roles = mysqli_query($con, $roles);
if(mysqli_num_rows($run_roles) == 1)
{
    while($row_role = mysqli_fetch_array($run_roles))
    {
        $role_title = $row_role['title'];
    }
}
else
{
    $role_title = '';
}
?>
	<title>Dashboard - <?php echo $company_trademark; ?></title>
	<link rel="stylesheet" type="text/css" href="css/dashboard_info.css?v=2">
</head>

<body class="reception-dashboard-body" oncontextmenu="return false;">
<div id="loadingSpinner" style="display: none;">
    <div class = "container">
        <div class = "row p-5 g-5">
            <div class = "col text-center">
                <div aria-busy="true" aria-describedby="progress-bar">
                    <h2>LOADING...</h2>
                    <p>Please Wait Untill Processing Completed.</p>
                    <p>Data Processing...</p>
                </div>
                <progress id="progress-bar" aria-label="Content loading…"></progress>    
                
            </div>
        </div>        
    </div>
</div>
<div class="row" style="margin: 0px;" id = "submitBody">
	<div class="col-md-12" style="text-align: center;background: lightgreen;">
		<label><h1><?php echo $company_name?> </h1></label>
	</div>
	<div class="col-md-3 background_whitesmoke" style = "text-transform: uppercase;">
		<?php include 'left_navigation.php'; ?>
	</div>
	<div class="col-md-9">
		<?php include 'includes/dashboard_info_panel.php'; ?>
	</div>
</div>

</body>
</html>
<script>
function showProgress() {
  document.getElementById('submitBody').style.display = 'none';
//   document.getElementById('submitButton').style.display = 'none';
  document.getElementById('loadingSpinner').style.display = 'block';
}    
</script>
<?php mysqli_close($con); ?>
