<?php
set_time_limit(120);

include 'includes/connect.php';
require_once __DIR__ . '/../includes/report_helpers.php';

$month = (string) ($_GET['month'] ?? $_GET['select_month'] ?? '');
if ($month === '') {
	http_response_code(400);
	exit('Month is required.');
}

$br_id = (int) ($_GET['br_id'] ?? $branch_id);
$ym = ycdo_parse_year_month($month);
$year = $ym['year'];
$total_days_of_month = $ym['days'];
$month = $year . '-' . $ym['month'];

$branch_name_print = $company_name;
$branch_address_print = $branch_address;
$br_lookup = mysqli_query($con, "SELECT name, address FROM branchs WHERE id = '$br_id' LIMIT 1");
if ($br_lookup && ($br_row = mysqli_fetch_assoc($br_lookup))) {
	if (!empty($br_row['name'])) {
		$branch_name_print = $br_row['name'];
	}
	$branch_address_print = (string) ($br_row['address'] ?? '');
}

$monthDt = date_create($month . '-01');
$monthLabel = $monthDt ? $monthDt->format('F Y') : $month;
?>
<?php include 'includes/head.php'; ?>
	<title>Print Account Summary - <?php echo $company_trademark; ?></title>
<style>
*{
    font-size: 16px;
}
</style>
</head>

<body onload="window.print()">
<p><a href="account_summary.php">← Back</a></p>
<table class="table" style="font-size: 8px">

	<thead>
	<tr style="caption-side: top;text-align: center;">
	    <td colspan="9">
	    <?php echo htmlspecialchars($branch_name_print, ENT_QUOTES, 'UTF-8'); ?>
    	<h6><?php echo htmlspecialchars($branch_address_print, ENT_QUOTES, 'UTF-8'); ?></h6>
    	<h5>Account Summary - <span style="text-align: left;font-size: 25px;"><?php echo htmlspecialchars($monthLabel, ENT_QUOTES, 'UTF-8'); ?></span></h5>

         <div style="float:left">Print Time: <?php echo date('h:i:s A'); ?></div>
         <div style="float:right">Print Date:<?php echo date('d-m-Y'); ?></div>
         <br>
         <div style="float:left">Print By: <?php echo htmlspecialchars($user_name, ENT_QUOTES, 'UTF-8'); ?></div>
         </td>

	</tr>
		<tr>
			<th>Date</th>
			<th>Total Cash</th>
			<th>Pending</th>
			<th>Pending Received</th>
			<th colspan="5">Received Amount</th>
		</tr>
	</thead>
	<tbody>
    <?php
    $total_cash = 0;
    $total_cash_received = 0;
    $total_pending = 0;
    $total_pending_receive = 0;

    for ($day = 1; $day <= $total_days_of_month; $day++) {
        $dayStr = sprintf('%02d', $day);
        $select_date = $month . '-' . $dayStr;
        $pending = 0;
        $pending_receive = 0;

        $select_pending = "SELECT SUM(`cash`) AS sc, SUM(`cash_received`) AS sr FROM `tokans`
            WHERE branch_id = '$br_id' AND `created` LIKE '$select_date%' AND status = 1 AND `cash` > `cash_received`";
        $run_pending = mysqli_query($con, $select_pending);
        if ($run_pending && ($row_pending = mysqli_fetch_assoc($run_pending))) {
            $pending = (float) ($row_pending['sc'] ?? 0) - (float) ($row_pending['sr'] ?? 0);
            $total_pending += $pending;
        }

        $select_pending_receive = "SELECT SUM(`cash`) AS sc, SUM(`cash_received`) AS sr FROM `tokans`
            WHERE branch_id = '$br_id' AND `created` LIKE '$select_date%' AND status = 1 AND `cash` = 0";
        $run_pending_receive = mysqli_query($con, $select_pending_receive);
        if ($run_pending_receive && ($row_pending_receive = mysqli_fetch_assoc($run_pending_receive))) {
            $pending_receive = abs((float) ($row_pending_receive['sc'] ?? 0) - (float) ($row_pending_receive['sr'] ?? 0));
            $total_pending_receive += $pending_receive;
        }

        $select_total = "SELECT SUM(`cash`) AS sc, SUM(`cash_received`) AS sr FROM `tokans`
            WHERE branch_id = '$br_id' AND `created` LIKE '$select_date%' AND status = 1";
        $run_total = mysqli_query($con, $select_total);
        if ($run_total && ($row_total = mysqli_fetch_assoc($run_total))) {
            $cash = (float) ($row_total['sc'] ?? 0);
            $cash_received = (float) ($row_total['sr'] ?? 0);
            if ($cash != 0 && $cash_received != 0) {
                $total_cash += $cash;
                $total_cash_received += $cash_received;
                $dayDt = date_create($select_date);
                $dayLabel = $dayDt ? $dayDt->format('d-m-Y') : $select_date;
                echo '
                        <tr>
                            <td>' . htmlspecialchars($dayLabel, ENT_QUOTES, 'UTF-8') . '</td>
                            <td>' . number_format($cash) . '</td>
                            <td>' . number_format($pending) . '</td>
                            <td>' . number_format($pending_receive) . '</td>
                            <td>' . number_format($cash_received) . '</td>
                        </tr>
                ';
            }
        }
    }
    echo '
                    <tr>
                        <th></th>
                        <th>' . number_format($total_cash) . '</th>
                        <th>' . number_format($total_pending) . '</th>
                        <th>' . number_format($total_pending_receive) . '</th>
                        <th>' . number_format($total_cash_received) . '</th>
                    </tr>
                ';
    ?>
    </tbody>
</table>

</body>
</html>
