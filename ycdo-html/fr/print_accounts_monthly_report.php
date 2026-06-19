<?php
set_time_limit(180);

include 'includes/connect.php';
require_once __DIR__ . '/../includes/report_helpers.php';

if (!isset($_GET['date'], $_GET['br_id']) || $_GET['date'] === '') {
	http_response_code(400);
	exit('Date and branch are required.');
}

$date = (string) $_GET['date'];
$br_id = (int) $_GET['br_id'];
$ym = ycdo_parse_year_month($date);
$year = $ym['year'];
$month = $ym['month'];
$days = $ym['days'];
$monthPrefix = $year . '-' . $month . '-';

$monthDt = date_create($year . '-' . $month . '-01');
$monthLabel = $monthDt ? $monthDt->format('F Y') : $date;

$countDay = static function ($con, $sql) {
	$run = mysqli_query($con, $sql);
	if (!$run) {
		return 0;
	}
	$row = mysqli_fetch_row($run);
	return (int) ($row[0] ?? 0);
};

$sumDay = static function ($con, $sql) {
	$run = mysqli_query($con, $sql);
	if (!$run) {
		return 0.0;
	}
	$row = mysqli_fetch_row($run);
	return (float) ($row[0] ?? 0);
};
?>
<html>
<head>
    <title>Accounts Monthly Report</title>
    <style>table{font-size:11px;} th,td{padding:3px 5px;}</style>
</head>
<body>
<p class="noprint"><a href="accounts_monthly_report.php">← Back</a></p>
<table border="solid">
<caption>
    <h2><?php echo htmlspecialchars($company_name, ENT_QUOTES, 'UTF-8'); ?></h2>
    <h2><?php echo htmlspecialchars(get_branch_name_by($br_id), ENT_QUOTES, 'UTF-8'); ?></h2>
    <h4>Progress For The Month Of <?php echo htmlspecialchars($monthLabel, ENT_QUOTES, 'UTF-8'); ?></h4>
</caption>
    <thead>
        <tr>
            <th>S#</th>
            <th>DATE</th>
            <th>POOR</th>
            <th>GENERAL</th>
            <th>PRIVATE</th>
            <th>URGENT</th>
            <th>TOTAL</th>
            <th>CONSULTANT</th>
            <th>PROCEDURE</th>
            <th>MEDICINE</th>
            <th>LAB</th>
            <th>COLLECTION</th>
        </tr>
    </thead>
    <tbody>
<?php
$s = 0;
$total_procedure = 0;
$total_medicine = 0;
$total_collection = 0;
$total_poor = 0;
$total_lab = 0;
$total_general = 0;
$total_private = 0;
$total_urgent = 0;
$total_consultent = 0;
$br_sql = (int) $br_id;

for ($day = 1; $day <= $days; $day++) {
	$dayStr = sprintf('%02d', $day);
	$dayLike = $monthPrefix . $dayStr . '%';
	$select_date = $dayStr . '-' . $month . '-' . $year;
	$s++;

	$collection_amount = $sumDay(
		$con,
		"SELECT COALESCE(SUM(cash_received), 0) FROM tokans WHERE branch_id = '$br_sql' AND created LIKE '$dayLike' AND status = 1"
	);
	$total_collection += $collection_amount;

	$count_poor = $countDay(
		$con,
		"SELECT COUNT(*) FROM tokans WHERE branch_id = '$br_sql' AND created LIKE '$dayLike' AND tokan_type_id = '1' AND status = 1"
	);
	$count_general = $countDay(
		$con,
		"SELECT COUNT(*) FROM tokans WHERE branch_id = '$br_sql' AND created LIKE '$dayLike' AND tokan_type_id = '2' AND status = 1"
	);
	$count_private = $countDay(
		$con,
		"SELECT COUNT(*) FROM tokans WHERE branch_id = '$br_sql' AND created LIKE '$dayLike' AND tokan_type_id = '3' AND status = 1"
	);
	$count_urgent = $countDay(
		$con,
		"SELECT COUNT(*) FROM tokans WHERE branch_id = '$br_sql' AND created LIKE '$dayLike' AND tokan_type_id = '4' AND status = 1"
	);
	$total_poor += $count_poor;
	$total_general += $count_general;
	$total_private += $count_private;
	$total_urgent += $count_urgent;

	$count_consultent = $countDay(
		$con,
		"SELECT COUNT(*) FROM tokans WHERE id IN (
            SELECT tokan_no FROM item_by_doctor
            WHERE tokan_no IN (SELECT id FROM tokans WHERE created LIKE '$dayLike' AND status = 1)
            AND branch_id = '$br_sql' AND status = 2
            AND item_id IN (SELECT id FROM item_register_to_branches WHERE item_id = 489)
        )"
	);
	$total_consultent += $count_consultent;

	$cash_received_procedure = $sumDay(
		$con,
		"SELECT COALESCE(SUM(cash_received), 0) FROM tokans WHERE id IN (
            SELECT tokan_no FROM item_by_doctor
            WHERE tokan_no IN (SELECT id FROM tokans WHERE created LIKE '$dayLike' AND status = 1)
            AND branch_id = '$br_sql' AND status = 2
            AND item_id IN (SELECT id FROM item_register_to_branches WHERE item_id IN (SELECT id FROM items WHERE category_id = 3))
        )"
	);
	$total_procedure += $cash_received_procedure;

	$cash_received_medicine = $sumDay(
		$con,
		"SELECT COALESCE(SUM(cash_received), 0) FROM tokans WHERE id IN (
            SELECT tokan_no FROM item_by_doctor
            WHERE tokan_no IN (SELECT id FROM tokans WHERE created LIKE '$dayLike' AND status = 1)
            AND branch_id = '$br_sql' AND status = 2
            AND item_id IN (SELECT id FROM item_register_to_branches WHERE item_id IN (SELECT id FROM items WHERE category_id NOT IN (2,3,8,17,20,28)))
        )"
	);
	$total_medicine += $cash_received_medicine;

	$cash_received_lab = $sumDay(
		$con,
		"SELECT COALESCE(SUM(cash_received), 0) FROM tokans WHERE id IN (
            SELECT tokan_no FROM item_by_doctor
            WHERE tokan_no IN (SELECT id FROM tokans WHERE created LIKE '$dayLike' AND status = 1)
            AND branch_id = '$br_sql' AND status = 2
            AND item_id IN (SELECT id FROM item_register_to_branches WHERE item_id IN (SELECT id FROM items WHERE category_id = 2))
        )"
	);
	$total_lab += $cash_received_lab;

	$row_total = $count_poor + $count_general + $count_private + $count_urgent;
	echo '<tr style="text-align: right;">';
	echo '<td>' . $s . '</td>';
	echo '<td>' . htmlspecialchars($select_date, ENT_QUOTES, 'UTF-8') . '</td>';
	echo '<td>' . $count_poor . '</td>';
	echo '<td>' . $count_general . '</td>';
	echo '<td>' . $count_private . '</td>';
	echo '<td>' . $count_urgent . '</td>';
	echo '<td>' . $row_total . '</td>';
	echo '<td>' . $count_consultent . '</td>';
	echo '<td>' . report_safe_number_format($cash_received_procedure) . '</td>';
	echo '<td>' . report_safe_number_format($cash_received_medicine) . '</td>';
	echo '<td>' . report_safe_number_format($cash_received_lab) . '</td>';
	echo '<td>' . report_safe_number_format($collection_amount) . '</td>';
	echo '</tr>';

	if ($day % 5 === 0) {
		flush();
	}
}
?>
    </tbody>
    <tfoot>
        <tr style="text-align: right;">
            <th colspan="2">TOTAL</th>
            <th><?php echo $total_poor; ?></th>
            <th><?php echo $total_general; ?></th>
            <th><?php echo $total_private; ?></th>
            <th><?php echo $total_urgent; ?></th>
            <th><?php echo $total_poor + $total_general + $total_private + $total_urgent; ?></th>
            <th><?php echo $total_consultent; ?></th>
            <th><?php echo report_safe_number_format($total_procedure); ?></th>
            <th><?php echo report_safe_number_format($total_medicine); ?></th>
            <th><?php echo report_safe_number_format($total_lab); ?></th>
            <th><?php echo report_safe_number_format($total_collection); ?></th>
        </tr>
    </tfoot>
</table>
</body>
</html>
