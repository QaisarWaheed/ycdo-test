<?php

/**
 * Pure helpers for summary / progress report pages (unit-testable).
 */

function summary_resolve_branch_id(array $get, array $post, int $sessionDefault = 0): int
{
    foreach (array('b_id', 'br_id') as $key) {
        if (isset($get[$key]) && $get[$key] !== '') {
            return (int) $get[$key];
        }
        if (isset($post[$key]) && $post[$key] !== '') {
            return (int) $post[$key];
        }
    }

    return $sessionDefault;
}

/** Legacy login summary URLs pass branch as u= */
function summary_login_branch_id(array $get, array $post, int $sessionDefault = 0): int
{
    foreach (array('b_id', 'u', 'br_id') as $key) {
        if (isset($get[$key]) && $get[$key] !== '') {
            return (int) $get[$key];
        }
        if (isset($post[$key]) && $post[$key] !== '') {
            return (int) $post[$key];
        }
    }

    return $sessionDefault;
}

function summary_gender_code($gender): string
{
    if ((int) $gender === 1) {
        return 'F';
    }
    if ((int) $gender === 2) {
        return 'M';
    }

    return 'O';
}

function summary_lab_conversion_percent(int $opd, int $diaPatients): int
{
    if ($opd <= 0 || $diaPatients <= 0) {
        return 0;
    }
    if ($opd >= $diaPatients) {
        return (int) (($diaPatients / $opd) * 100);
    }

    return 100;
}

function summary_previous_tokan_display($previousTokanNo): string
{
    if ($previousTokanNo === null || $previousTokanNo === '' || $previousTokanNo === 'NULL') {
        return 'NULL';
    }

    return (string) $previousTokanNo;
}

/**
 * @return array{from: string, to: string, branch_id: int, user_id: int, user_name: string}|null
 */
function summary_token_report_params(array $get, array $post): ?array
{
    if (isset($get['s']) && $get['s'] !== '') {
        $from = (string) $get['s'];
        $to = (string) ($get['e'] ?? '');
        $userId = (int) ($get['u'] ?? 0);
        $userName = (string) ($get['un'] ?? 'ALL');
        $branchId = summary_resolve_branch_id($get, $post, 0);
    } elseif (isset($post['s']) && $post['s'] !== '') {
        $from = (string) $post['s'];
        $to = (string) ($post['e'] ?? '');
        $userId = (int) ($post['u'] ?? 0);
        $userName = (string) ($post['un'] ?? 'ALL');
        $branchId = summary_resolve_branch_id($get, $post, 0);
    } else {
        return null;
    }

    if ($from === '' || $to === '') {
        return null;
    }

    return array(
        'from' => $from,
        'to' => $to,
        'branch_id' => $branchId,
        'user_id' => $userId,
        'user_name' => $userName,
    );
}

/**
 * @return array{from: string, to: string, branch_id: int}|null
 */
function summary_login_report_params(array $get, array $post, int $sessionBranchId = 0): ?array
{
    if (isset($get['s']) && $get['s'] !== '') {
        $from = (string) $get['s'];
        $to = (string) ($get['e'] ?? '');
        $branchId = summary_login_branch_id($get, $post, $sessionBranchId);
    } elseif (isset($post['s']) && $post['s'] !== '') {
        $from = (string) $post['s'];
        $to = (string) ($post['e'] ?? '');
        $branchId = summary_login_branch_id($get, $post, $sessionBranchId);
    } else {
        return null;
    }

    if ($from === '' || $to === '') {
        return null;
    }

    return array(
        'from' => $from,
        'to' => $to,
        'branch_id' => $branchId,
    );
}

function progress_tokans_subquery_sql(int $br_id, string $like): string
{
    $br_id = (int) $br_id;
    return "(SELECT id FROM tokans WHERE branch_id = '$br_id' AND status = 1 AND created LIKE '$like')";
}

/**
 * Days in month without PHP calendar extension (replaces cal_days_in_month).
 */
function ycdo_days_in_month(int $year, int $month): int
{
    if ($month < 1 || $month > 12) {
        return 0;
    }

    return (int) date('t', mktime(0, 0, 0, $month, 1, $year));
}

/**
 * Parse YYYY-MM or YYYY-MM-DD into year, zero-padded month, and day count.
 *
 * @return array{year: int, month: string, month_int: int, days: int}
 */
function ycdo_parse_year_month(string $date): array
{
    $dt = date_create($date);
    if ($dt === false) {
        $year = (int) date('Y');
        $monthInt = (int) date('m');
    } else {
        $year = (int) $dt->format('Y');
        $monthInt = (int) $dt->format('m');
    }

    return array(
        'year' => $year,
        'month' => sprintf('%02d', $monthInt),
        'month_int' => $monthInt,
        'days' => ycdo_days_in_month($year, $monthInt),
    );
}

/**
 * @return array<int, array{id: int, address: string}>
 */
function summary_active_branches($con, bool $allBranches = true, int $sessionBranchId = 0): array
{
    if ($allBranches) {
        $sql = "SELECT id, address FROM branchs WHERE status = '1' ORDER BY address";
    } else {
        $sessionBranchId = (int) $sessionBranchId;
        $sql = "SELECT id, address FROM branchs WHERE status = '1' AND id = '$sessionBranchId' ORDER BY address";
    }

    $branches = array();
    $run = mysqli_query($con, $sql);
    if ($run) {
        while ($row = mysqli_fetch_assoc($run)) {
            $branches[] = array(
                'id' => (int) $row['id'],
                'address' => (string) $row['address'],
            );
        }
    }

    return $branches;
}

/**
 * Whether the user may pick any active branch (not only session branch).
 * FR sidebar admin uses is_admin == 2; incharge uses is_incharge == 2; legacy admin uses is_admin == 1.
 */
function summary_branch_may_select_all(int $isAdmin = 0, int $isIncharge = 0): bool
{
    return $isAdmin == 1 || $isAdmin == 2 || $isIncharge == 2;
}

/**
 * FR/BK summary forms: branch <option> list (respects admin / incharge).
 */
function fr_branch_select_options($con, int $sessionBranchId, int $isAdmin, int $isIncharge, int $selectedId = 0, string $name = 'br_id'): string
{
    if ($selectedId < 1) {
        $selectedId = $sessionBranchId;
    }

    return summary_branch_select_html(
        $con,
        $selectedId,
        $sessionBranchId,
        summary_branch_may_select_all($isAdmin, $isIncharge),
        $name
    );
}

function summary_branch_select_html($con, int $selectedId, int $sessionBranchId, bool $allBranches, string $name = 'br_id'): string
{
    $html = '';
    foreach (summary_active_branches($con, $allBranches, $sessionBranchId) as $branch) {
        $selected = ((int) $branch['id'] === $selectedId) ? ' selected' : '';
        $html .= '<option value="' . (int) $branch['id'] . '"' . $selected . '>'
            . htmlspecialchars($branch['address'], ENT_QUOTES, 'UTF-8') . '</option>';
    }

    if ($html === '') {
        $html = '<option value="">No branch found</option>';
    }

    return $html;
}

/**
 * @return array{br_id: int, date: string}
 */
function gynae_report_resolve_params(array $get, array $post, int $sessionBranchId = 0): array
{
    $br_id = summary_resolve_branch_id($get, $post, $sessionBranchId);
    $date = date('Y-m-d');
    if (isset($get['date']) && $get['date'] !== '') {
        $date = (string) $get['date'];
    } elseif (isset($post['date']) && $post['date'] !== '') {
        $date = (string) $post['date'];
    }

    return array(
        'br_id' => $br_id,
        'date' => $date,
    );
}

function report_safe_number_format($value, int $decimals = 0): string
{
    if ($value === null || $value === '') {
        return number_format(0, $decimals);
    }

    return number_format((float) $value, $decimals);
}

/** datetime-local / ISO input → MySQL-friendly datetime string */
function summary_normalize_datetime_for_db(string $value): string
{
    $value = trim(str_replace('T', ' ', $value));
    if (preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}$/', $value)) {
        return $value . ':00';
    }
    if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
        return $value . ' 00:00:00';
    }

    return $value;
}

function summary_format_datetime_display(string $value): string
{
    $dt = date_create(summary_normalize_datetime_for_db($value));
    if ($dt === false) {
        return $value;
    }

    return $dt->format('d-m-Y h:i:s A');
}

function summary_escape_datetime($con, string $value): string
{
    return mysqli_real_escape_string($con, summary_normalize_datetime_for_db($value));
}

function fr_branch_summery_resolve_date(array $post): string
{
    if (isset($post['date']) && preg_match('/^\d{4}-\d{2}-\d{2}$/', (string) $post['date'])) {
        return (string) $post['date'];
    }

    return date('Y-m-d');
}

function fr_branch_summery_sql_int_list(array $ids): string
{
    $ids = array_values(array_unique(array_filter(array_map('intval', $ids))));

    return $ids ? implode(',', $ids) : '0';
}

/**
 * @return array{lab: int[], usg: int[], admission: int[], svd: int[], procedure: int[]}
 */
function fr_branch_summery_irb_sets($con): array
{
    static $sets = null;
    if ($sets !== null) {
        return $sets;
    }

    $fetch = static function (string $where) use ($con): array {
        $ids = array();
        $sql = 'SELECT irb.id FROM item_register_to_branches irb INNER JOIN items i ON irb.item_id = i.id WHERE ' . $where;
        $run = mysqli_query($con, $sql);
        if ($run) {
            while ($row = mysqli_fetch_assoc($run)) {
                $ids[] = (int) $row['id'];
            }
        }

        return $ids;
    };

    $svdItemIds = array(472, 1118, 1313, 473, 1119, 1314);
    $usgItemIds = array(476, 477, 478, 1161, 1162, 1163, 1184, 1317, 1318, 1138, 1185, 1411);
    $admissionItemIds = array(444, 448, 452, 456, 457, 460, 461, 945, 1124, 1125, 1128, 1131, 1132, 1145, 1186, 1285, 1289, 1293, 1297, 1301);

    $sets = array(
        'lab' => $fetch('i.category_id = 2'),
        'usg' => $fetch('i.id IN (' . fr_branch_summery_sql_int_list($usgItemIds) . ')'),
        'admission' => $fetch('i.id IN (' . fr_branch_summery_sql_int_list($admissionItemIds) . ')'),
        'svd' => $fetch('i.id IN (' . fr_branch_summery_sql_int_list($svdItemIds) . ')'),
        'procedure' => $fetch(
            'i.category_id = 3 AND i.id NOT IN (' . fr_branch_summery_sql_int_list($svdItemIds) . ')'
        ),
    );

    return $sets;
}

/**
 * @return array<int, array<string, float|int|string>>
 */
function fr_branch_summery_query_branch_map($con, string $sql, string $valueKey): array
{
    $map = array();
    $run = mysqli_query($con, $sql);
    if (!$run) {
        return $map;
    }

    while ($row = mysqli_fetch_assoc($run)) {
        $branchId = (int) $row['branch_id'];
        $map[$branchId] = isset($row[$valueKey]) ? $row[$valueKey] : 0;
    }

    return $map;
}

/**
 * Branch's Summery (FR): all metrics for one day in a handful of queries (avoids 504).
 *
 * @return list<array{branch_id: int, address: string, cash: float, cash_received: float, opd: int, lab: float, usg: int, admission: int, svd: int, procedure: int}>
 */
function fr_branch_summery_rows_for_date($con, string $date): array
{
    $start = mysqli_real_escape_string($con, $date . ' 00:00:00');
    $end = mysqli_real_escape_string($con, date('Y-m-d H:i:s', strtotime($date . ' +1 day')));

    $tokanDay = "t.status = 1 AND t.branch_id != 0 AND t.created >= '$start' AND t.created < '$end'";
    $irb = fr_branch_summery_irb_sets($con);
    $labList = fr_branch_summery_sql_int_list($irb['lab']);
    $usgList = fr_branch_summery_sql_int_list($irb['usg']);
    $admissionList = fr_branch_summery_sql_int_list($irb['admission']);
    $svdList = fr_branch_summery_sql_int_list($irb['svd']);
    $procedureList = fr_branch_summery_sql_int_list($irb['procedure']);

    $rows = array();
    $baseSql = "SELECT t.branch_id, SUM(t.cash) AS cash, SUM(t.cash_received) AS cash_received
        FROM tokans t WHERE $tokanDay GROUP BY t.branch_id";
    $baseRun = mysqli_query($con, $baseSql);
    if ($baseRun) {
        while ($row = mysqli_fetch_assoc($baseRun)) {
            $branchId = (int) $row['branch_id'];
            $rows[$branchId] = array(
                'branch_id' => $branchId,
                'address' => '',
                'cash' => (float) $row['cash'],
                'cash_received' => (float) $row['cash_received'],
                'opd' => 0,
                'lab' => 0.0,
                'usg' => 0,
                'admission' => 0,
                'svd' => 0,
                'procedure' => 0,
            );
        }
    }

    if ($rows === array()) {
        return array();
    }

    $opdMap = fr_branch_summery_query_branch_map(
        $con,
        "SELECT t.branch_id, COUNT(*) AS metric FROM tokans t
            WHERE t.tokan_type_id >= 1 AND t.tokan_type_id <= 10 AND $tokanDay
            GROUP BY t.branch_id",
        'metric'
    );
    $labMap = fr_branch_summery_query_branch_map(
        $con,
        "SELECT t.branch_id, SUM(t.cash_received) AS metric FROM tokans t
            INNER JOIN item_by_doctor ibd ON ibd.tokan_no = t.id
            WHERE $tokanDay AND ibd.item_id IN ($labList)
            GROUP BY t.branch_id",
        'metric'
    );
    $countJoin = static function (string $irbList) use ($con, $tokanDay): array {
        return fr_branch_summery_query_branch_map(
            $con,
            "SELECT t.branch_id, COUNT(DISTINCT t.id) AS metric FROM tokans t
                INNER JOIN item_by_doctor ibd ON ibd.tokan_no = t.id
                WHERE $tokanDay AND ibd.item_id IN ($irbList)
                GROUP BY t.branch_id",
            'metric'
        );
    };

    $usgMap = $countJoin($usgList);
    $admissionMap = $countJoin($admissionList);
    $svdMap = $countJoin($svdList);
    $procedureMap = $countJoin($procedureList);

    foreach ($rows as $branchId => $row) {
        $rows[$branchId]['opd'] = (int) ($opdMap[$branchId] ?? 0);
        $rows[$branchId]['lab'] = (float) ($labMap[$branchId] ?? 0);
        $rows[$branchId]['usg'] = (int) ($usgMap[$branchId] ?? 0);
        $rows[$branchId]['admission'] = (int) ($admissionMap[$branchId] ?? 0);
        $rows[$branchId]['svd'] = (int) ($svdMap[$branchId] ?? 0);
        $rows[$branchId]['procedure'] = (int) ($procedureMap[$branchId] ?? 0);
    }

    $branchIdList = fr_branch_summery_sql_int_list(array_keys($rows));
    $addrRun = mysqli_query($con, "SELECT id, address FROM branchs WHERE id IN ($branchIdList)");
    if ($addrRun) {
        while ($row = mysqli_fetch_assoc($addrRun)) {
            $id = (int) $row['id'];
            if (isset($rows[$id])) {
                $rows[$id]['address'] = (string) $row['address'];
            }
        }
    }

    $list = array_values($rows);
    usort($list, static function ($a, $b) {
        return strcasecmp((string) $a['address'], (string) $b['address']);
    });

    return $list;
}
