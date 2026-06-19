<?php

require_once __DIR__ . '/report_helpers.php';

/** Maximum inclusive days allowed for one report (prevents gateway timeouts). */
function consumption_max_range_days()
{
    return 31;
}

/**
 * @return array{start: string, end: string, days: int}
 */
function consumption_range_bounds($from_date, $to_date)
{
    $from = substr((string) $from_date, 0, 10);
    $to = substr((string) $to_date, 0, 10);
    $start = $from . ' 00:00:00';
    $end = date('Y-m-d H:i:s', strtotime($to . ' +1 day'));
    $days = (int) ((strtotime($to) - strtotime($from)) / 86400) + 1;

    return array('start' => $start, 'end' => $end, 'days' => $days);
}

/**
 * @return array{ok: bool, message: string, bounds?: array{start: string, end: string, days: int}}
 */
function consumption_validate_request($from_date, $to_date)
{
    if ($from_date === '' || $to_date === '') {
        return array('ok' => false, 'message' => 'From and to dates are required.');
    }

    $bounds = consumption_range_bounds($from_date, $to_date);
    if ($bounds['days'] < 1) {
        return array('ok' => false, 'message' => 'To date must be on or after from date.');
    }
    if ($bounds['days'] > consumption_max_range_days()) {
        return array(
            'ok' => false,
            'message' => 'Date range is too large. Please use at most ' . consumption_max_range_days() . ' days.',
        );
    }

    return array('ok' => true, 'message' => '', 'bounds' => $bounds);
}

/**
 * Effective quantity per line (same rule as consumed qty aggregation).
 */
function consumption_effective_qty_sql($alias = 'ibd')
{
    $a = preg_replace('/[^a-zA-Z0-9_]/', '', $alias);

    return "CASE WHEN $a.sale_quantity IS NULL OR $a.sale_quantity <= 0 THEN 1 ELSE $a.sale_quantity END";
}

/**
 * Category totals from item_by_doctor (one query for the whole report).
 *
 * @return array<int, array{category_id: int, category_name: string, consumed_qty: float, purchase_total: float, sale_total: float}>
 */
function consumption_fetch_category_totals(mysqli $con, int $branch_id, $start, $end)
{
    $branch_id = (int) $branch_id;
    if ($branch_id < 1) {
        return array();
    }

    $start = mysqli_real_escape_string($con, (string) $start);
    $end = mysqli_real_escape_string($con, (string) $end);
    $qty = consumption_effective_qty_sql('ibd');

    $sql = "SELECT ibd.category_id,
            COALESCE(c.name, CONCAT('Category #', ibd.category_id)) AS category_name,
            SUM($qty) AS consumed_qty,
            COALESCE(SUM($qty * ibd.purchase_price), 0) AS purchase_total,
            COALESCE(SUM(ibd.sale_price), 0) AS sale_total
        FROM item_by_doctor ibd
        LEFT JOIN categories c ON c.id = ibd.category_id
        WHERE ibd.branch_id = '$branch_id'
          AND ibd.status = '2'
          AND ibd.created >= '$start'
          AND ibd.created < '$end'
        GROUP BY ibd.category_id, c.name
        ORDER BY category_name ASC";

    $rows = array();
    $run = mysqli_query($con, $sql);
    if ($run) {
        while ($row = mysqli_fetch_assoc($run)) {
            $rows[] = array(
                'category_id' => (int) $row['category_id'],
                'category_name' => (string) $row['category_name'],
                'consumed_qty' => (float) $row['consumed_qty'],
                'purchase_total' => (float) $row['purchase_total'],
                'sale_total' => (float) $row['sale_total'],
            );
        }
    }

    return $rows;
}

/**
 * @return array{name: string, address: string, tag_name: string}
 */
function consumption_branch_header(mysqli $con, int $branch_id)
{
    $branch_id = (int) $branch_id;
    $out = array('name' => 'YCDO', 'address' => '', 'tag_name' => '');
    if ($branch_id < 1) {
        return $out;
    }

    $run = mysqli_query($con, "SELECT name, address, tag_name FROM branchs WHERE id = '$branch_id' LIMIT 1");
    if ($run && ($row = mysqli_fetch_assoc($run))) {
        $out['name'] = (string) ($row['name'] !== '' ? $row['name'] : 'YCDO');
        $out['address'] = (string) $row['address'];
        $out['tag_name'] = (string) $row['tag_name'];
    }

    return $out;
}
