<?php
set_time_limit(120);

include 'includes/connect.php';
require_once __DIR__ . '/../includes/report_helpers.php';

if (!isset($_GET['date'], $_GET['br_id']) || $_GET['date'] === '') {
	http_response_code(400);
	exit('Date and branch are required.');
}

$date = (string) $_GET['date'];
$br_id = (int) $_GET['br_id'];
$ym = ycdo_parse_year_month($date);
$year = (int) $ym['year'];
$month = $ym['month'];
$days = (int) $ym['days'];
$br_sql = (int) $br_id;

$start = sprintf('%04d-%s-01 00:00:00', $year, $month);
$endMonth = (int) $month + 1;
$endYear = $year;
if ($endMonth > 12) {
	$endMonth = 1;
	$endYear++;
}
$end = sprintf('%04d-%02d-01 00:00:00', $endYear, $endMonth);

$monthDt = date_create($year . '-' . $month . '-01');
$monthLabel = $monthDt ? $monthDt->format('F Y') : $date;

$minorItemIds = '434, 435, 436, 437, 853, 864, 867, 868, 869, 870, 871, 872, 873, 874, 875, 876, 877, 878, 879, 880, 881, 882, 883, 884, 885, 886, 887, 888, 889, 890, 891, 892, 893, 899, 907, 908, 909, 910, 911, 912, 913, 914';
$usgItemIds = '476, 477, 478, 1411, 1435';
$gynaeItemIds = '483, 1159, 1321, 1414';
$admissionItemIds = '444, 448, 452, 456, 460, 945';

/** @return array<int, float> */
function fr_report_account_sum_by_day($con, string $sql): array
{
	$out = array();
	$run = mysqli_query($con, $sql);
	if (!$run) {
		return $out;
	}
	while ($row = mysqli_fetch_assoc($run)) {
		$out[(int) $row['d']] = (float) $row['v'];
	}
	return $out;
}

/** @return array<int, int> */
function fr_report_account_count_by_day($con, string $sql): array
{
	$out = array();
	$run = mysqli_query($con, $sql);
	if (!$run) {
		return $out;
	}
	while ($row = mysqli_fetch_assoc($run)) {
		$out[(int) $row['d']] = (int) $row['v'];
	}
	return $out;
}

$dayTok = 'DAY(created)';
$rangeTok = "branch_id = '$br_sql' AND created >= '$start' AND created < '$end'";

$collections = fr_report_account_sum_by_day(
	$con,
	"SELECT $dayTok AS d, COALESCE(SUM(cash_received), 0) AS v FROM tokans
        WHERE $rangeTok AND status = 1 GROUP BY $dayTok"
);

$byType = array();
$runTypes = mysqli_query(
	$con,
	"SELECT $dayTok AS d,
        SUM(CASE WHEN tokan_type_id = 1 THEN 1 ELSE 0 END) AS poor,
        SUM(CASE WHEN tokan_type_id = 2 THEN 1 ELSE 0 END) AS general,
        SUM(CASE WHEN tokan_type_id = 3 THEN 1 ELSE 0 END) AS private,
        SUM(CASE WHEN tokan_type_id >= 4 AND tokan_type_id <= 10 THEN 1 ELSE 0 END) AS urgent
        FROM tokans WHERE $rangeTok AND status = 1 GROUP BY $dayTok"
);
if ($runTypes) {
	while ($row = mysqli_fetch_assoc($runTypes)) {
		$d = (int) $row['d'];
		$byType[$d] = array(
			'poor' => (int) $row['poor'],
			'general' => (int) $row['general'],
			'private' => (int) $row['private'],
			'urgent' => (int) $row['urgent'],
		);
	}
}

$dayT = 'DAY(t.created)';
$ibdBase = "ibd.branch_id = '$br_sql' AND ibd.status = 2
    AND t.branch_id = '$br_sql' AND t.created >= '$start' AND t.created < '$end' AND t.status = 1";

$itemCountSql = static function (string $itemFilter) use ($dayT, $ibdBase): string {
	return "SELECT $dayT AS d, COUNT(ibd.tokan_no) AS v
        FROM item_by_doctor ibd
        INNER JOIN tokans t ON t.id = ibd.tokan_no
        INNER JOIN item_register_to_branches irb ON ibd.item_id = irb.id
        WHERE $ibdBase AND $itemFilter
        GROUP BY $dayT";
};

$consultents = fr_report_account_count_by_day(
	$con,
	"SELECT $dayT AS d, COUNT(DISTINCT t.id) AS v
        FROM tokans t
        INNER JOIN item_by_doctor ibd ON ibd.tokan_no = t.id
        INNER JOIN item_register_to_branches irb ON ibd.item_id = irb.id
        INNER JOIN items i ON irb.item_id = i.id
        WHERE $ibdBase AND i.category_id = 29
        GROUP BY $dayT"
);

$usg = fr_report_account_count_by_day($con, $itemCountSql("irb.item_id IN ($usgItemIds)"));
$gynae = fr_report_account_count_by_day($con, $itemCountSql("irb.item_id IN ($gynaeItemIds)"));
$admission = fr_report_account_count_by_day($con, $itemCountSql("irb.item_id IN ($admissionItemIds)"));

$minorProcedure = fr_report_account_count_by_day(
	$con,
	"SELECT $dayT AS d, COUNT(DISTINCT t.id) AS v
        FROM tokans t
        INNER JOIN item_by_doctor ibd ON ibd.tokan_no = t.id
        INNER JOIN item_register_to_branches irb ON ibd.item_id = irb.id
        INNER JOIN items i ON irb.item_id = i.id
        WHERE $ibdBase AND i.category_id = 3 AND i.id IN ($minorItemIds)
        GROUP BY $dayT"
);

$majorProcedure = fr_report_account_count_by_day(
	$con,
	"SELECT $dayT AS d, COUNT(DISTINCT t.id) AS v
        FROM tokans t
        INNER JOIN item_by_doctor ibd ON ibd.tokan_no = t.id
        INNER JOIN item_register_to_branches irb ON ibd.item_id = irb.id
        INNER JOIN items i ON irb.item_id = i.id
        WHERE $ibdBase AND i.category_id = 3 AND i.id NOT IN ($minorItemIds)
        GROUP BY $dayT"
);
?>
<html>
<head>
    <title>Accounts Report</title>
    <style>table{font-size:11px;} th,td{padding:3px 5px;text-align:right;} td:nth-child(2){text-align:left;}</style>
</head>
<body>
<p><a href="report_account.php">← Back to form</a></p>
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
            <th>MINOR</th>
            <th>PROCEDURE</th>
            <th>USG</th>
            <th>GYNAE</th>
            <th>ADDMISSIOM</th>
            <th>COLLECTION</th>
        </tr>
    </thead>
    <tbody>
<?php
$s = 0;
$totals = array(
	'poor' => 0, 'general' => 0, 'private' => 0, 'urgent' => 0,
	'consult' => 0, 'minor' => 0, 'proc' => 0, 'usg' => 0, 'gynae' => 0, 'adm' => 0, 'col' => 0,
);

for ($day = 1; $day <= $days; $day++) {
	$s++;
	$dayStr = sprintf('%02d', $day);
	$select_date = $dayStr . '-' . $month . '-' . $year;

	$types = $byType[$day] ?? array('poor' => 0, 'general' => 0, 'private' => 0, 'urgent' => 0);
	$count_poor = $types['poor'];
	$count_general = $types['general'];
	$count_private = $types['private'];
	$count_urgent = $types['urgent'];
	$row_total = $count_poor + $count_general + $count_private + $count_urgent;
	$count_consultent = $consultents[$day] ?? 0;
	$count_minor_procedure = $minorProcedure[$day] ?? 0;
	$count_procedure = $majorProcedure[$day] ?? 0;
	$count_usg = $usg[$day] ?? 0;
	$count_gynae = $gynae[$day] ?? 0;
	$count_addmission = $admission[$day] ?? 0;
	$collection_amount = $collections[$day] ?? 0;

	$totals['poor'] += $count_poor;
	$totals['general'] += $count_general;
	$totals['private'] += $count_private;
	$totals['urgent'] += $count_urgent;
	$totals['consult'] += $count_consultent;
	$totals['minor'] += $count_minor_procedure;
	$totals['proc'] += $count_procedure;
	$totals['usg'] += $count_usg;
	$totals['gynae'] += $count_gynae;
	$totals['adm'] += $count_addmission;
	$totals['col'] += $collection_amount;

	echo '<tr>';
	echo '<td>' . $s . '</td>';
	echo '<td>' . htmlspecialchars($select_date, ENT_QUOTES, 'UTF-8') . '</td>';
	echo '<td>' . $count_poor . '</td>';
	echo '<td>' . $count_general . '</td>';
	echo '<td>' . $count_private . '</td>';
	echo '<td>' . $count_urgent . '</td>';
	echo '<td>' . $row_total . '</td>';
	echo '<td>' . $count_consultent . '</td>';
	echo '<td>' . $count_minor_procedure . '</td>';
	echo '<td>' . $count_procedure . '</td>';
	echo '<td>' . $count_usg . '</td>';
	echo '<td>' . $count_gynae . '</td>';
	echo '<td>' . $count_addmission . '</td>';
	echo '<td>' . report_safe_number_format($collection_amount) . '</td>';
	echo '</tr>';
}
$grand_token_total = $totals['poor'] + $totals['general'] + $totals['private'] + $totals['urgent'];
?>
    </tbody>
    <tfoot>
        <tr>
            <th colspan="2">TOTAL</th>
            <th><?php echo $totals['poor']; ?></th>
            <th><?php echo $totals['general']; ?></th>
            <th><?php echo $totals['private']; ?></th>
            <th><?php echo $totals['urgent']; ?></th>
            <th><?php echo $grand_token_total; ?></th>
            <th><?php echo $totals['consult']; ?></th>
            <th><?php echo $totals['minor']; ?></th>
            <th><?php echo $totals['proc']; ?></th>
            <th><?php echo $totals['usg']; ?></th>
            <th><?php echo $totals['gynae']; ?></th>
            <th><?php echo $totals['adm']; ?></th>
            <th><?php echo report_safe_number_format($totals['col']); ?></th>
        </tr>
    </tfoot>
</table>
</body>
</html>
