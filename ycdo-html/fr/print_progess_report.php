<?php
include 'includes/connect.php';
require_once __DIR__ . '/../bk/includes/progress_report_params.php';

$req = progress_report_resolve_request($con);
$date = $req['date'];
$br_id = $req['br_id'];
$like = $req['like'];

$opds = progress_opd_count_by_doctor($con, $br_id, $like);
$cons_opds = progress_item_count_by_doctor($con, $br_id, $like, '(SELECT id FROM items WHERE category_id = 29)');
$admissions = progress_item_count_by_doctor($con, $br_id, $like, '444, 448, 452, 456, 457, 460, 461, 945, 1124, 1125, 1128, 1131, 1132, 1145, 1186, 1285, 1289, 1293, 1297, 1301');
$svds = progress_item_count_by_doctor($con, $br_id, $like, '472, 1118, 1313');
$dncs = progress_item_count_by_doctor($con, $br_id, $like, '473, 1119, 1314');
$usgs = progress_item_count_by_doctor($con, $br_id, $like, '1138, 1185, 476, 477, 478, 1161, 1162, 1163, 1184, 1317, 1318, 1411');
$procedures = progress_item_count_by_doctor($con, $br_id, $like, '(SELECT id FROM items WHERE id NOT IN (473, 1119, 1314, 472, 1118, 1313) AND category_id = 3)');
$lab_stats = progress_lab_stats_by_doctor($con, $br_id, $like);
$category_stats = progress_category_stats_by_doctor($con, $br_id, $like);
$gynae_register = progress_gynae_register_count_by_doctor($con, $br_id, $like);
$refer_from = progress_referral_from_count_by_branch($con, $br_id, $like);
$refer_to = progress_referral_to_count_by_doctor($con, $like);

if (!function_exists('fr_progress_cat_count')) {
	function fr_progress_cat_count(array $stats, int $doctor, int $categoryId): int
	{
		return isset($stats[$doctor][$categoryId]) ? (int) $stats[$doctor][$categoryId]['count_token'] : 0;
	}
}

$doctor_ids = array();
$run = mysqli_query($con, "SELECT DISTINCT doctor_id FROM tokans WHERE created LIKE '$like' AND branch_id = '$br_id' AND doctor_id > 0 ORDER BY doctor_id");
if ($run) {
	while ($row = mysqli_fetch_assoc($run)) {
		$doctor_ids[] = (int) $row['doctor_id'];
	}
}
$doctor_ids = array_values(array_unique(array_merge(
	$doctor_ids,
	array_keys($opds),
	array_keys($cons_opds),
	array_keys($lab_stats),
	array_keys($gynae_register),
	array_keys($refer_from),
	array_keys($refer_to)
)));
sort($doctor_ids, SORT_NUMERIC);

$user_names = array();
if (count($doctor_ids) > 0) {
	$ids = implode(',', $doctor_ids);
	$run_names = mysqli_query($con, "SELECT id, u_name FROM users WHERE id IN ($ids)");
	if ($run_names) {
		while ($row = mysqli_fetch_assoc($run_names)) {
			$user_names[(int) $row['id']] = $row['u_name'];
		}
	}
}

$dateHeading = $date;
$dateDt = date_create($date);
if ($dateDt !== false) {
	$dateHeading = $dateDt->format(' d F Y');
}

$colCount = 15;
?>
<html>
<head>
    <title>PRINT PROGRESS REPORT</title>
    <style>
        table { font-size: 11px; }
        th, td { padding: 2px 4px; }
    </style>
</head>
<body>

<table border="solid">
<caption>
    <h2><?php echo htmlspecialchars($company_name, ENT_QUOTES, 'UTF-8'); ?></h2>
    <h2><?php echo htmlspecialchars(get_branch_name_by($br_id), ENT_QUOTES, 'UTF-8'); ?></h2>
    <h3>PROGRESS DATE <?php echo htmlspecialchars($dateHeading, ENT_QUOTES, 'UTF-8'); ?></h3>
</caption>
    <thead>
        <tr>
            <th>S#</th>
            <th>NAME</th>
            <th>OPD</th>
            <th>CONS</th>
            <th>LAB</th>
            <th>USG</th>
            <th>SVD</th>
            <th>D&amp;C</th>
            <th>PROCEDURE</th>
            <th>ADMISSION</th>
            <th>ECG</th>
            <th>GYNAE TOKEN</th>
            <th>GYNAE REGISTRATION</th>
            <th>REFER FROM</th>
            <th>REFER TO</th>
        </tr>
    </thead>
<?php
$s = 0;
$totals = array(
	'opds' => 0,
	'cons' => 0,
	'lab' => 0,
	'usg' => 0,
	'svd' => 0,
	'dnc' => 0,
	'proc' => 0,
	'adm' => 0,
	'ecg' => 0,
	'gynae_token' => 0,
	'gynae_reg' => 0,
	'refer_from' => 0,
	'refer_to' => 0,
);

if (count($doctor_ids) > 0) {
	echo '<tbody>';
	foreach ($doctor_ids as $doctor) {
		$s++;
		$opd = $opds[$doctor] ?? 0;
		$cons = $cons_opds[$doctor] ?? 0;
		$adm = $admissions[$doctor] ?? 0;
		$svd = $svds[$doctor] ?? 0;
		$dnc = $dncs[$doctor] ?? 0;
		$usg = $usgs[$doctor] ?? 0;
		$proc = $procedures[$doctor] ?? 0;
		$lab_cash = $lab_stats[$doctor]['cash'] ?? 0;
		$labs = ($lab_cash == 0) ? 'N/A' : $lab_cash;
		$ecg = fr_progress_cat_count($category_stats, $doctor, 44);
		$gynae_token = fr_progress_cat_count($category_stats, $doctor, 41);
		$gynae_reg = $gynae_register[$doctor] ?? 0;
		$ref_from = $refer_from[$doctor] ?? 0;
		$ref_to = $refer_to[$doctor] ?? 0;

		$totals['opds'] += $opd;
		$totals['cons'] += $cons;
		$totals['lab'] += is_numeric($labs) ? (float) $labs : 0;
		$totals['usg'] += $usg;
		$totals['svd'] += $svd;
		$totals['dnc'] += $dnc;
		$totals['proc'] += $proc;
		$totals['adm'] += $adm;
		$totals['ecg'] += $ecg;
		$totals['gynae_token'] += $gynae_token;
		$totals['gynae_reg'] += $gynae_reg;
		$totals['refer_from'] += $ref_from;
		$totals['refer_to'] += $ref_to;

		$doctor_name = $user_names[$doctor] ?? get_uname_by_id($doctor);
		echo '<tr style="text-align: right;">';
		echo '<td>' . $s . '</td>';
		echo '<td style="text-align: left;">' . htmlspecialchars($doctor_name, ENT_QUOTES, 'UTF-8') . '</td>';
		echo '<td>' . $opd . '</td>';
		echo '<td>' . $cons . '</td>';
		echo '<td>' . (is_numeric($labs) ? number_format((float) $labs, 0) : $labs) . '</td>';
		echo '<td>' . $usg . '</td>';
		echo '<td>' . $svd . '</td>';
		echo '<td>' . $dnc . '</td>';
		echo '<td>' . $proc . '</td>';
		echo '<td>' . $adm . '</td>';
		echo '<td>' . $ecg . '</td>';
		echo '<td>' . $gynae_token . '</td>';
		echo '<td>' . $gynae_reg . '</td>';
		echo '<td>' . $ref_from . '</td>';
		echo '<td>' . $ref_to . '</td>';
		echo '</tr>';
	}
	echo '</tbody><tfoot><tr style="text-align: right;"><th></th><th></th>';
	echo '<th>' . $totals['opds'] . '</th>';
	echo '<th>' . $totals['cons'] . '</th>';
	echo '<th>' . number_format($totals['lab'], 0) . '</th>';
	echo '<th>' . $totals['usg'] . '</th>';
	echo '<th>' . $totals['svd'] . '</th>';
	echo '<th>' . $totals['dnc'] . '</th>';
	echo '<th>' . $totals['proc'] . '</th>';
	echo '<th>' . $totals['adm'] . '</th>';
	echo '<th>' . $totals['ecg'] . '</th>';
	echo '<th>' . $totals['gynae_token'] . '</th>';
	echo '<th>' . $totals['gynae_reg'] . '</th>';
	echo '<th>' . $totals['refer_from'] . '</th>';
	echo '<th>' . $totals['refer_to'] . '</th>';
	echo '</tr></tfoot>';
} else {
	echo '<tbody><tr><td colspan="' . $colCount . '">NO DATA FOUND</td></tr></tbody>';
}
?>
</table>

</body>
</html>
