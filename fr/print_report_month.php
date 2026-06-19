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
$br_sql = (int) $br_id;

$monthDt = date_create($year . '-' . $month . '-01');
$monthLabel = $monthDt ? $monthDt->format('F Y') : $date;
?>
<html>
<head>
    <title>Month Report</title>
    <style>table{font-size:11px;} th,td{padding:3px 5px;}</style>
</head>
<body>
<p><a href="report_month.php">← Back</a></p>
<table border="solid">
<caption>
    <h2><?php echo htmlspecialchars($company_name, ENT_QUOTES, 'UTF-8'); ?></h2>
    <h2><?php echo htmlspecialchars(get_branch_name_by($br_id), ENT_QUOTES, 'UTF-8'); ?></h2>
    <h4>Month Report — <?php echo htmlspecialchars($monthLabel, ENT_QUOTES, 'UTF-8'); ?></h4>
</caption>
    <thead>
        <tr>
            <th>S#</th>
            <th>DATE</th>
            <th>TOKEN CASH</th>
            <th>RETURN TOKEN</th>
            <th>COLLECTION</th>
            <th>LOGIN RECEIVED</th>
            <th>LOGIN EXTRA</th>
            <th>LOGIN SHORT</th>
            <th>LOGIN TOTAL</th>
        </tr>
    </thead>
    <tbody>
<?php
$s = 0;
$total_cash = 0;
$total_login = 0;
$total_extra_amount = 0;
$total_short_amount = 0;
$total_collection = 0;
$total_return_token = 0;

for ($day = 1; $day <= $days; $day++) {
	$s++;
	$dayStr = sprintf('%02d', $day);
	$dayLike = $monthPrefix . $dayStr . '%';
	$select_date = $dayStr . '-' . $month . '-' . $year;

	$cash_amount = 0;
	$collection_amount = 0;
	$return_token_amount = 0;
	$received_amount = 0;
	$extra_amount = 0;
	$short_amount = 0;

	$run_collection = mysqli_query(
		$con,
		"SELECT SUM(cash_received) AS sr, SUM(cash) AS sc FROM tokans
            WHERE branch_id = '$br_sql' AND created LIKE '$dayLike' AND status = 1"
	);
	if ($run_collection && ($row_collection = mysqli_fetch_assoc($run_collection))) {
		$collection_amount = (float) ($row_collection['sr'] ?? 0);
		$cash_amount = (float) ($row_collection['sc'] ?? 0);
		$total_collection += $collection_amount;
		$total_cash += $cash_amount;
	}

	$run_return = mysqli_query(
		$con,
		"SELECT SUM(cash_received) AS s FROM tokans WHERE branch_id = '$br_sql' AND created LIKE '$dayLike' AND status = 3"
	);
	if ($run_return && ($row_return = mysqli_fetch_assoc($run_return))) {
		$return_token_amount = (float) ($row_return['s'] ?? 0);
		$total_return_token += $return_token_amount;
	}

	$run_users = mysqli_query(
		$con,
		"SELECT SUM(received_amount) AS ra, SUM(extra_amount) AS ea, SUM(short_amount) AS sa
            FROM summary_details
            WHERE login_id IN (
                SELECT id FROM logins_detail
                WHERE branch_id = '$br_sql' AND login_at LIKE '$dayLike' AND status = '2'
            )"
	);
	if ($run_users && ($row_users = mysqli_fetch_assoc($run_users))) {
		$received_amount = (float) ($row_users['ra'] ?? 0);
		$extra_amount = (float) ($row_users['ea'] ?? 0);
		$short_amount = (float) ($row_users['sa'] ?? 0);
		$total_login += $received_amount;
		$total_extra_amount += $extra_amount;
		$total_short_amount += $short_amount;
	}

	echo '<tr style="text-align: right;">';
	echo '<td>' . $s . '</td>';
	echo '<td>' . htmlspecialchars($select_date, ENT_QUOTES, 'UTF-8') . '</td>';
	echo '<td>' . report_safe_number_format($cash_amount) . '</td>';
	echo '<td>' . report_safe_number_format($return_token_amount) . '</td>';
	echo '<td>' . report_safe_number_format($collection_amount) . '</td>';
	echo '<td>' . report_safe_number_format($received_amount) . '</td>';
	echo '<td>' . report_safe_number_format($extra_amount) . '</td>';
	echo '<td>' . report_safe_number_format($short_amount) . '</td>';
	echo '<td>' . report_safe_number_format($received_amount + $extra_amount) . '</td>';
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
            <th><?php echo report_safe_number_format($total_cash); ?></th>
            <th><?php echo report_safe_number_format($total_return_token); ?></th>
            <th><?php echo report_safe_number_format($total_collection); ?></th>
            <th><?php echo report_safe_number_format($total_login); ?></th>
            <th><?php echo report_safe_number_format($total_extra_amount); ?></th>
            <th><?php echo report_safe_number_format($total_short_amount); ?></th>
            <th><?php echo report_safe_number_format($total_login + $total_extra_amount); ?></th>
        </tr>
    </tfoot>
</table>
</body>
</html>
