<?php
require_once __DIR__ . '/includes/connect_report.php';
require_once __DIR__ . '/../includes/consumption_report_helpers.php';

@set_time_limit(300);

$from_date = (string) ($_GET['from'] ?? $_GET['from_date'] ?? '');
$to_date = (string) ($_GET['to'] ?? $_GET['to_date'] ?? '');
$br_id = (int) ($_GET['br_id'] ?? $branch_id);

if ($is_admin < 2 && $br_id !== $branch_id) {
    $br_id = $branch_id;
}

$validation = consumption_validate_request($from_date, $to_date);
if (!$validation['ok']) {
    http_response_code(400);
    exit($validation['message']);
}
if ($br_id < 1) {
    http_response_code(400);
    exit('Branch is required.');
}

$bounds = $validation['bounds'];
$report_rows = consumption_fetch_category_totals($con, $br_id, $bounds['start'], $bounds['end']);
$branch_header = consumption_branch_header($con, $br_id);
$branch_title = $branch_header['tag_name'] !== '' ? $branch_header['tag_name'] : $branch_header['name'];
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Category Consumption Report - <?php echo htmlspecialchars($branch_title, ENT_QUOTES, 'UTF-8'); ?></title>
    <style>
        * { font-size: 14px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #000; padding: 4px 8px; }
        th { background: #f0f0f0; }
        .text-right { text-align: right; }
        @media print {
            .noprint { display: none; }
        }
    </style>
</head>
<body onload="window.print()">
<table>
    <thead>
        <tr>
            <th colspan="5" style="text-align: center; border: none;">
                <h2 style="margin: 0;"><?php echo htmlspecialchars($company_name, ENT_QUOTES, 'UTF-8'); ?></h2>
                <h3 style="margin: 4px 0;"><?php echo htmlspecialchars($branch_title, ENT_QUOTES, 'UTF-8'); ?></h3>
                <?php if ($branch_header['address'] !== '') { ?>
                <h6 style="margin: 4px 0;"><?php echo htmlspecialchars($branch_header['address'], ENT_QUOTES, 'UTF-8'); ?></h6>
                <?php } ?>
                <h4 style="margin: 8px 0;">Category Consumption Report</h4>
                <div style="text-align: left;">
                    <strong>Date:</strong>
                    <?php echo htmlspecialchars(date('d-m-Y', strtotime($from_date)), ENT_QUOTES, 'UTF-8'); ?>
                    to
                    <?php echo htmlspecialchars(date('d-m-Y', strtotime($to_date)), ENT_QUOTES, 'UTF-8'); ?>
                </div>
                <div style="text-align: right;">
                    Print Date: <?php echo date('d-m-Y'); ?>
                    &nbsp;|&nbsp;
                    Print Time: <?php echo date('h:i:s A'); ?>
                </div>
            </th>
        </tr>
        <tr>
            <th>S #</th>
            <th>Category</th>
            <th class="text-right">Consumed Qty</th>
            <th class="text-right">Purchase Total</th>
            <th class="text-right">Sale Total</th>
        </tr>
    </thead>
    <tbody>
<?php
if (empty($report_rows)) {
    echo '<tr><td colspan="5" style="text-align: center;">No consumption found for this branch and date range.</td></tr>';
} else {
    $sr = 0;
    $grand_qty = 0.0;
    $grand_purchase = 0.0;
    $grand_sale = 0.0;
    foreach ($report_rows as $row) {
        $sr++;
        $grand_qty += $row['consumed_qty'];
        $grand_purchase += $row['purchase_total'];
        $grand_sale += $row['sale_total'];
        echo '<tr>';
        echo '<td>' . $sr . '</td>';
        echo '<td>' . htmlspecialchars($row['category_name'], ENT_QUOTES, 'UTF-8') . '</td>';
        echo '<td class="text-right">' . htmlspecialchars(number_format($row['consumed_qty'], 2), ENT_QUOTES, 'UTF-8') . '</td>';
        echo '<td class="text-right">' . htmlspecialchars(number_format($row['purchase_total'], 2), ENT_QUOTES, 'UTF-8') . '</td>';
        echo '<td class="text-right">' . htmlspecialchars(number_format($row['sale_total'], 2), ENT_QUOTES, 'UTF-8') . '</td>';
        echo '</tr>';
    }
    echo '<tr style="font-weight: bold;">';
    echo '<td colspan="2">Total</td>';
    echo '<td class="text-right">' . htmlspecialchars(number_format($grand_qty, 2), ENT_QUOTES, 'UTF-8') . '</td>';
    echo '<td class="text-right">' . htmlspecialchars(number_format($grand_purchase, 2), ENT_QUOTES, 'UTF-8') . '</td>';
    echo '<td class="text-right">' . htmlspecialchars(number_format($grand_sale, 2), ENT_QUOTES, 'UTF-8') . '</td>';
    echo '</tr>';
}
?>
    </tbody>
</table>
<p class="noprint" style="text-align: center; margin-top: 16px;">
    <button type="button" onclick="window.print()">Print again</button>
</p>
</body>
</html>
