<?php include 'includes/connect.php';
if ($fr_id != 1 && $fr_id != 350) {
    header('Location: logout.php');
    exit;
}
require_once __DIR__ . '/../includes/report_helpers.php';
$today = fr_branch_summery_resolve_date($_POST);
$summaryRows = fr_branch_summery_rows_for_date($con, $today);
include 'includes/head.php';
?>
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

	<title>Dashboard - <?php echo $company_trademark; ?></title>
</head>

<body class="background_image">

<div class="row" style="margin: 0px;">
	<div class="col-md-12 noprint" style="text-align: center;background: lightgreen;">
		<label><h1><?php echo $company_name; ?> </h1></label>
        <?php include 'navigation_top.php'; ?>
	</div>
	<div class="col-md-12">
	    <table class = "table table-bordered">
	        <caption class = "h2" style = "caption-side: top;text-align: center;">Branch's Summery <?php echo htmlspecialchars(date('d-m-Y', strtotime($today)), ENT_QUOTES, 'UTF-8'); ?></caption>
	        <thead>
	            <tr class = "noprint">
	                <th colspan = "3"></th>
	                <th colspan = "2" style = "text-align: right">
	                    <form method = "POST">
	                        <input type = "date" name = "date" value = "<?php echo htmlspecialchars($today, ENT_QUOTES, 'UTF-8'); ?>" required onchange = "this.form.submit()" />
	                    </form>
	                </th>
	            </tr>
	            <tr style = "text-align: center;">
	                <th>S #</th>
	                <th>Branch Name</th>
	                <th>OPD</th>
	                <th>Lab</th>
	                <th>USG</th>
	                <th>Admission</th>
	                <th>SVD / DNC</th>
	                <th>Procedure</th>
	                <th>Total Amount</th>
	            </tr>
	        </thead>
	        <tbody>
        <?php
        $s = 0;
        $total_patient = 0;
        $total_lab = 0;
        $total_usg = 0;
        $total_admission = 0;
        $total_svd = 0;
        $total_procedure = 0;
        $total_cash_received = 0;

        foreach ($summaryRows as $row) {
            $s++;
            $total_patient += (int) $row['opd'];
            $total_lab += (float) $row['lab'];
            $total_usg += (int) $row['usg'];
            $total_admission += (int) $row['admission'];
            $total_svd += (int) $row['svd'];
            $total_procedure += (int) $row['procedure'];
            $total_cash_received += (float) $row['cash_received'];
            echo '
                <tr>
                    <td>' . $s . '</td>
                    <td>' . htmlspecialchars((string) $row['address'], ENT_QUOTES, 'UTF-8') . '</td>
                    <td style = "text-align: right">' . report_safe_number_format($row['opd']) . '</td>
                    <td style = "text-align: right">' . report_safe_number_format($row['lab']) . '</td>
                    <td style = "text-align: right">' . report_safe_number_format($row['usg']) . '</td>
                    <td style = "text-align: right">' . report_safe_number_format($row['admission']) . '</td>
                    <td style = "text-align: right">' . report_safe_number_format($row['svd']) . '</td>
                    <td style = "text-align: right">' . report_safe_number_format($row['procedure']) . '</td>
                    <td style = "text-align: right">' . report_safe_number_format($row['cash_received']) . '</td>
                </tr>
                ';
        }

        if ($s > 0) {
            echo '
                <tr>
                    <th style = "text-align: right;" colspan = "2">GRAND TOTAL</th>
                    <th style = "text-align: right;">' . report_safe_number_format($total_patient) . '</th>
                    <th style = "text-align: right;">' . report_safe_number_format($total_lab) . '</th>
                    <th style = "text-align: right;">' . report_safe_number_format($total_usg) . '</th>
                    <th style = "text-align: right;">' . report_safe_number_format($total_admission) . '</th>
                    <th style = "text-align: right;">' . report_safe_number_format($total_svd) . '</th>
                    <th style = "text-align: right;">' . report_safe_number_format($total_procedure) . '</th>
                    <th style = "text-align: right;">' . report_safe_number_format($total_cash_received) . '</th>
                </tr>
            ';
        }
        ?>
	            
	        </tbody>
	    </table>
	</div>
</div>
</body>
</html>
