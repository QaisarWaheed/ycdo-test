<?php
require_once __DIR__ . '/../../includes/ycdo_bootstrap.php';
date_default_timezone_set("Asia/Karachi");
$current_date = date('Y-m-d H:i:s');
$ip_address = $_SERVER['SERVER_ADDR'] ?? '';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (empty($_SESSION['lab_user_id'])) {
    header('Location: logout.php');
    exit;
}

$lab_user_id = (int) $_SESSION['lab_user_id'];
$lab_user_name = $_SESSION['lab_user_name'] ?? '';
$lab_user_phone = $_SESSION['lab_user_phone'] ?? '';
$lab_login_branch_id = $_SESSION['lab_login_branch_id'] ?? 0;
$lab_login_is_admin = $_SESSION['lab_login_is_admin'] ?? 0;
$lab_login_is_incharge = $_SESSION['lab_login_is_incharge'] ?? 0;
$lab_login_branch_name = $_SESSION['lab_login_branch_name'] ?? '';
$lab_login_branch_address = $_SESSION['lab_login_branch_address'] ?? '';
$lab_login_branch_phone = $_SESSION['lab_login_branch_phone'] ?? '';

if ($lab_user_id < 1) {
    header('Location: logout.php');
    exit;
}

$con = ycdo_db_connect();

include 'company_info.php';

function get_branch_tag_by($id)
{
    $con = $GLOBALS['con'];
    $output = '';
    $query = "SELECT tag_name FROM branchs WHERE id = '$id' ";
    $run = mysqli_query($con,  $query);
    if (mysqli_num_rows($run) > 0) 
    {
        while ( $row = mysqli_fetch_array($run) ) 
        {
            $output .= $row['tag_name'];
        }    
    }    
        return $output;
}

function get_uname_by_id($id)
{
    $output = '';
    $run = mysqli_query($GLOBALS['con'], "SELECT u_name FROM `users` WHERE `id` = '$id' ");
    if (mysqli_num_rows($run) == 1) 
    {
        while ($row = mysqli_fetch_array($run)) 
        {
            $output .= $row['u_name'];
        }    
    }    
    return $output;
}

function get_branch_name_by($id)
{
    $con = $GLOBALS['con'];
    $output = '';
    $query = "SELECT address FROM branchs WHERE id = '$id' ";
    $run = mysqli_query($con,  $query);
    if (mysqli_num_rows($run) > 0) 
    {
        while ( $row = mysqli_fetch_array($run) ) 
        {
            $output .= $row['address'];
        }    
    }    
        return $output;
}
function get_item_name_by_register_item_id($register_item_id)
{
    $output = '';
    $run = mysqli_query($GLOBALS['con'], "SELECT name FROM `items` WHERE `id` iN (SELECT item_id FROM `item_register_to_branches` WHERE id = '$register_item_id') ");
    if (mysqli_num_rows($run) == 1) 
    {
        while ($row = mysqli_fetch_array($run)) 
        {
            $output .= $row['name'];
        }
    }
    else
    {
        $output = 0;
    }    
    return $output;
}

function print_services_by_token_no($token_no)
{
    $quanity = '';
    $ser = 0;
    $run = mysqli_query($GLOBALS['con'], "SELECT lab_tests.lab_test_id, item_id, items.name, lab_tests.lab_test_rate FROM lab_tests INNER JOIN items ON lab_tests.item_id = items.id WHERE lab_tests.token_no = '$token_no' ");
    if (mysqli_num_rows($run) > 0) 
    {
        while ($row = mysqli_fetch_array($run)) 
        {
            $ser = $ser + 1;
            $quanity .= '<tr class = "td1"><td class = "td1" style = "text-align: center;">'.$ser.'</td><td class = "td1" colspan = "2">' .$row['name'] . '</td><td style = "text-align: right;" class = "td1">' .$row['lab_test_rate'] . '</td></tr>';
        }    
    }  
    else
    {
        $quanity .= '<tr class = "td1"><td class = "td1" colspan = "4">NOT A TEST TOKEN</td></tr>';
    }
    return $quanity;
}

function get_given_services_by_token_no($token_no)
{
    $quanity = '';
    $ser = 0;
    $run = mysqli_query($GLOBALS['con'], "SELECT DISTINCT item_by_doctor.id AS record_id, items.id AS item_id, items.name AS item_name FROM `item_by_doctor` INNER JOIN item_register_to_branches ON item_by_doctor.item_id = item_register_to_branches.id INNER JOIN items ON item_register_to_branches.item_id = items.id WHERE `tokan_no` = '$token_no' AND items.category_id = 2");
    if (mysqli_num_rows($run) > 0) 
    {
        while ($row = mysqli_fetch_array($run)) 
        {
            $ser = $ser + 1;
            $date_data = date('Y-m-d\Th:i');
            $record_id = $row['record_id'];
            $newTime = date('Y-m-d\TH:i', strtotime('+60 minutes'));

            $quanity .= '<tr class = "td1">
                <td class = "td1" style = "text-align: center;">'.$ser.'</td><td class = "td1">' .$row['item_name'] . '</td>
                <td><input required min = "'.$date_data.'" value = "'.$newTime.'" class = "form-control" type = "datetime-local" name = "'.$record_id.'" /></td>
                <td><input class = "form-control" type = "text" name = "comments'.$record_id.'" /></td>
            </tr>';
        }    
    }  
    else
    {
        $quanity .= '<tr class = "td1"><td class = "td1" colspan = "4">NOT A TEST TOKEN</td></tr>';
    }
    return $quanity;
}

function insert_test_by_token_no($token_no)
{
    $quanity = '';
    $ser = 0;
    $user_id = $GLOBALS['lab_user_id'];
    $run = mysqli_query($GLOBALS['con'], "SELECT item_by_doctor.id AS record_id, items.id AS item_id, items.name AS item_name FROM `item_by_doctor` INNER JOIN item_register_to_branches ON item_by_doctor.item_id = item_register_to_branches.id INNER JOIN items ON item_register_to_branches.item_id = items.id WHERE `tokan_no` = '$token_no' AND items.category_id = 2");
    if (mysqli_num_rows($run) > 0) 
    {
        while ($row = mysqli_fetch_array($run)) 
        {
            $ser = $ser + 1;
            $item_id = $row['item_id'];
            $insert = "INSERT INTO `lab_tests`
            (`lab_test_id`, `token_no`, `item_id`, `lab_test_status`, `user_id`) 
            VALUES
            (NULL, '$token_no', '$item_id', '1', '$user_id')";
            mysqli_query($GLOBALS['con'], $insert);
        }    
    }
    return $quanity;
}

/**
 * Normalize datetime-local / legacy values for MySQL DATETIME (strict mode).
 */
function lab_normalize_datetime($value, $fallback)
{
    $fallback = (string) $fallback;
    $value = str_replace('T', ' ', trim((string) $value));
    if ($value === '') {
        return $fallback;
    }
    if (preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}$/', $value)) {
        $value .= ':00';
    }
    $dt = date_create($value);

    return $dt ? $dt->format('Y-m-d H:i:s') : $fallback;
}

/**
 * Sensible default for a lab_tests column missing from INSERT (strict MySQL).
 */
function lab_insert_default_for_column($field, $type, $created_at, $user_id, $branch_id)
{
    $field = (string) $field;
    $type = strtolower((string) $type);

    if ($field === 'lab_test_status_id') {
        return '2';
    }
    if ($field === 'lab_test_status') {
        return '1';
    }
    if ($field === 'user_id' || $field === 'lab_test_collected_created_by') {
        return (string) (int) $user_id;
    }
    if ($field === 'branch_id') {
        return (string) max(0, (int) $branch_id);
    }
    if (substr($field, -3) === '_by' || substr($field, -9) === '_created_by') {
        return '0';
    }
    if (strpos($type, 'int') !== false || strpos($type, 'decimal') !== false || strpos($type, 'float') !== false || strpos($type, 'double') !== false) {
        return '0';
    }
    if (strpos($type, 'datetime') !== false || strpos($type, 'timestamp') !== false) {
        return (string) $created_at;
    }
    if (strpos($type, 'date') !== false) {
        return substr((string) $created_at, 0, 10);
    }
    if (strpos($type, 'time') !== false) {
        return substr((string) $created_at, 11, 8) ?: '00:00:00';
    }

    return '';
}

/**
 * Insert lab sample row with all NOT NULL columns (strict MySQL).
 *
 * @return bool
 */
function lab_insert_test_sample($con, array $params)
{
    $token_no = (int) ($params['token_no'] ?? 0);
    $item_id = (int) ($params['item_id'] ?? 0);
    $user_id = (int) ($params['user_id'] ?? 0);
    $branch_id = (int) ($params['branch_id'] ?? 0);
    if ($token_no < 1 || $item_id < 1 || $user_id < 1) {
        return false;
    }

    $created_at = (string) ($params['created_at'] ?? date('Y-m-d H:i:s'));
    $reporting_date_time = lab_normalize_datetime($params['reporting_date_time'] ?? '', $created_at);
    $lab_test_rate = (string) ($params['lab_test_rate'] ?? '0');
    $sample_comments = (string) ($params['sample_comments'] ?? '');

    $columns = array(
        'token_no' => (string) $token_no,
        'item_id' => (string) $item_id,
        'lab_test_status' => '1',
        'user_id' => (string) $user_id,
        'sample_date_time' => $created_at,
        'reporting_date_time' => $reporting_date_time,
        'lab_test_rate' => $lab_test_rate,
        'sample_time_comments' => $sample_comments,
        'lab_test_collected_comments' => $sample_comments,
        'lab_test_collected_created_by' => (string) $user_id,
        'lab_test_collected_created_at' => $created_at,
        'lab_test_received_sample_comments' => '',
        'lab_test_processed_comments' => '',
        'lab_test_processed_created_by' => '0',
        'lab_test_processed_created_at' => $created_at,
        'lab_test_conducted_comments' => '',
        'lab_test_conducted_created_by' => '0',
        'lab_test_conducted_created_at' => $created_at,
        'lab_test_approved_comments' => '',
        'lab_test_approved_created_by' => '0',
        'lab_test_approved_created_at' => $created_at,
        'lab_test_print_comments' => '',
        'lab_test_print_created_by' => '0',
        'lab_test_print_created_at' => $created_at,
        'lab_test_status_id' => '2',
        'test_result' => '',
        'test_normal_value' => '',
    );
    if ($branch_id > 0) {
        $columns['branch_id'] = (string) $branch_id;
    }

    static $lab_tests_schema = null;
    if ($lab_tests_schema === null) {
        $lab_tests_schema = array();
        $schema_run = mysqli_query($con, 'SHOW COLUMNS FROM `lab_tests`');
        if ($schema_run) {
            while ($schema_row = mysqli_fetch_assoc($schema_run)) {
                $lab_tests_schema[] = $schema_row;
            }
        }
    }

    $allowed_fields = array();
    foreach ($lab_tests_schema as $schema_row) {
        $field = $schema_row['Field'];
        if ($field === 'lab_test_id') {
            continue;
        }
        $allowed_fields[$field] = true;

        $needs_value = ($schema_row['Null'] === 'NO')
            && ($schema_row['Default'] === null)
            && (stripos((string) $schema_row['Extra'], 'auto_increment') === false);

        if ($needs_value && !isset($columns[$field])) {
            $columns[$field] = lab_insert_default_for_column(
                $field,
                $schema_row['Type'],
                $created_at,
                $user_id,
                $branch_id
            );
        }
    }

    $insert_columns = array();
    $insert_values = array();
    foreach ($columns as $name => $value) {
        if (!isset($allowed_fields[$name])) {
            continue;
        }
        $insert_columns[] = '`' . $name . '`';
        $insert_values[] = "'" . mysqli_real_escape_string($con, (string) $value) . "'";
    }

    if (empty($insert_columns)) {
        return false;
    }

    $sql = 'INSERT INTO `lab_tests` (' . implode(', ', $insert_columns) . ') VALUES (' . implode(', ', $insert_values) . ')';

    return (bool) mysqli_query($con, $sql);
}
