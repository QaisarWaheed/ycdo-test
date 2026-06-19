<?php

/**
 * Tokens eligible for new gynae registration (gynae item, not already registered).
 *
 * @return mysqli_result|false
 */
function ycdo_gynae_eligible_tokens_result($con, $branch_id, $limit = 300)
{
    $branch_id = (int) $branch_id;
    $limit = max(1, min(1000, (int) $limit));

    $sql = "SELECT DISTINCT t.id AS token_no, p.name AS patient_name
        FROM tokans t
        INNER JOIN patients p ON p.id = t.patient_id
        INNER JOIN item_by_doctor ibd
            ON ibd.tokan_no = t.id
            AND ibd.branch_id = t.branch_id
            AND ibd.status = 2
            AND (
                ibd.category_id = 41
                OR ibd.item_id IN (
                    SELECT irb.id FROM item_register_to_branches irb
                    WHERE irb.branch_id = $branch_id
                        AND irb.item_id IN (483, 1159, 1321, 1414, 1576)
                )
            )
        WHERE t.branch_id = $branch_id
            AND t.status = 1
            AND NOT EXISTS (
                SELECT 1 FROM gynae_register g
                WHERE g.token_no = t.id AND g.status = 1
            )
        ORDER BY t.id DESC
        LIMIT $limit";

    return mysqli_query($con, $sql);
}

/**
 * @return array<int, array{token_no: int, patient_name: string}>
 */
function ycdo_gynae_eligible_tokens_list($con, $branch_id, $limit = 300)
{
    $rows = array();
    $run = ycdo_gynae_eligible_tokens_result($con, $branch_id, $limit);
    if (!$run) {
        return $rows;
    }
    while ($row = mysqli_fetch_assoc($run)) {
        $rows[] = array(
            'token_no' => (int) $row['token_no'],
            'patient_name' => (string) $row['patient_name'],
        );
    }

    return $rows;
}

/**
 * Sensible default for a gynae_register column missing from INSERT (strict MySQL).
 */
function ycdo_gynae_register_default_for_column($field, $type, $created_at, $user_id, $branch_id, $doctor_id)
{
    $field = (string) $field;
    $type = strtolower((string) $type);

    if ($field === 'status') {
        return '1';
    }
    if ($field === 'update_by' || $field === 'user_id') {
        return (string) max(0, (int) $user_id);
    }
    if ($field === 'branch_id') {
        return (string) max(0, (int) $branch_id);
    }
    if ($field === 'doctor_id' || $field === 'register_by_doctor') {
        return (string) max(0, (int) $doctor_id);
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
 * Insert gynae_register with all NOT NULL columns (strict MySQL).
 *
 * @return int New row id, or 0 on failure (see ycdo_gynae_register_insert_error).
 */
function ycdo_gynae_register_insert($con, array $params)
{
    $token_no = (int) ($params['token_no'] ?? 0);
    $user_id = (int) ($params['user_id'] ?? 0);
    $branch_id = (int) ($params['branch_id'] ?? 0);
    $doctor_id = (int) ($params['doctor_id'] ?? 0);
    if ($token_no < 1 || $user_id < 1 || $branch_id < 1 || $doctor_id < 1) {
        $GLOBALS['ycdo_gynae_register_last_error'] = 'Missing token, user, branch, or doctor.';
        return 0;
    }

    $created_at = (string) ($params['created'] ?? $params['created_at'] ?? date('Y-m-d H:i:s'));
    $register_by_doctor = (int) ($params['register_by_doctor'] ?? $doctor_id);

    $columns = array(
        'token_no' => (string) $token_no,
        'phone' => (string) ($params['phone'] ?? ''),
        'weeks' => (string) ($params['weeks'] ?? ''),
        'gravide' => (string) ($params['gravide'] ?? $params['gravida'] ?? ''),
        'next_visit_date' => (string) ($params['next_visit_date'] ?? ''),
        'update_by' => (string) $user_id,
        'status' => '1',
        'remarks' => (string) ($params['remarks'] ?? ''),
        'created' => $created_at,
        'branch_id' => (string) $branch_id,
        'doctor_id' => (string) $doctor_id,
        'user_id' => (string) $user_id,
        'husband_name' => (string) ($params['husband_name'] ?? ''),
        'husband_phone' => (string) ($params['husband_phone'] ?? ''),
        'lmp' => (string) ($params['lmp'] ?? ''),
        'years_marriage' => (string) ($params['years_marriage'] ?? '0'),
        'height' => (string) ($params['height'] ?? '0'),
        'weight' => (string) ($params['weight'] ?? '0'),
        'blood_group' => (string) ($params['blood_group'] ?? ''),
        'husband_blood_group' => (string) ($params['husband_blood_group'] ?? ''),
        'menstrual_cycle' => (string) ($params['menstrual_cycle'] ?? ''),
        'psh' => (string) ($params['psh'] ?? ''),
        'pmh' => (string) ($params['pmh'] ?? ''),
        'register_by_doctor' => (string) $register_by_doctor,
        'usg_report' => (string) ($params['usg_report'] ?? ''),
    );

    static $gynae_register_schema = null;
    if ($gynae_register_schema === null) {
        $gynae_register_schema = array();
        $schema_run = mysqli_query($con, 'SHOW COLUMNS FROM `gynae_register`');
        if ($schema_run) {
            while ($schema_row = mysqli_fetch_assoc($schema_run)) {
                $gynae_register_schema[] = $schema_row;
            }
        }
    }

    $allowed_fields = array();
    foreach ($gynae_register_schema as $schema_row) {
        $field = $schema_row['Field'];
        if ($field === 'id') {
            continue;
        }
        $allowed_fields[$field] = true;

        $needs_value = ($schema_row['Null'] === 'NO')
            && ($schema_row['Default'] === null)
            && (stripos((string) $schema_row['Extra'], 'auto_increment') === false);

        if ($needs_value && !isset($columns[$field])) {
            $columns[$field] = ycdo_gynae_register_default_for_column(
                $field,
                $schema_row['Type'],
                $created_at,
                $user_id,
                $branch_id,
                $doctor_id
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
        $GLOBALS['ycdo_gynae_register_last_error'] = 'gynae_register schema unavailable.';
        return 0;
    }

    $sql = 'INSERT INTO `gynae_register` (' . implode(', ', $insert_columns) . ') VALUES (' . implode(', ', $insert_values) . ')';
    if (!mysqli_query($con, $sql)) {
        $GLOBALS['ycdo_gynae_register_last_error'] = mysqli_error($con);
        if ($GLOBALS['ycdo_gynae_register_last_error'] === '') {
            $GLOBALS['ycdo_gynae_register_last_error'] = 'Insert into gynae_register failed.';
        }
        return 0;
    }

    $GLOBALS['ycdo_gynae_register_last_error'] = '';
    return (int) mysqli_insert_id($con);
}

/**
 * Last error from ycdo_gynae_register_insert().
 */
function ycdo_gynae_register_insert_error()
{
    return (string) ($GLOBALS['ycdo_gynae_register_last_error'] ?? '');
}

/**
 * Insert gynae_register_history with all NOT NULL columns (strict MySQL).
 *
 * @return bool
 */
function ycdo_gynae_register_history_insert($con, array $params)
{
    $gynae_register_id = (int) ($params['gynae_register_id'] ?? $params['registeration_id'] ?? 0);
    $user_id = (int) ($params['user_id'] ?? 0);
    $branch_id = (int) ($params['branch_id'] ?? 0);
    $doctor_id = (int) ($params['doctor_id'] ?? $params['old_doctor_id'] ?? 0);
    if ($gynae_register_id < 1 || $user_id < 1) {
        $GLOBALS['ycdo_gynae_register_last_error'] = 'Missing registration id or user.';
        return false;
    }

    $created_at = (string) ($params['created'] ?? $params['created_at'] ?? date('Y-m-d H:i:s'));

    $columns = array(
        'gynae_register_id' => (string) $gynae_register_id,
        'last_visit_date' => (string) ($params['last_visit_date'] ?? ''),
        'previous_update_by' => (string) ($params['previous_update_by'] ?? '0'),
        'weeks_visit_time' => (string) ($params['weeks_visit_time'] ?? ''),
        'user_id' => (string) $user_id,
        'created' => $created_at,
        'branch_id' => (string) max(0, $branch_id),
        'doctor_id' => (string) max(0, $doctor_id),
        'duration_pregnancy' => (string) ($params['duration_pregnancy'] ?? ''),
        'sfh' => (string) ($params['sfh'] ?? ''),
        'lie' => (string) ($params['lie'] ?? ''),
        'presentation' => (string) ($params['presentation'] ?? ''),
        'fhr' => (string) ($params['fhr'] ?? ''),
        'bp' => (string) ($params['bp'] ?? ''),
        'temp' => (string) ($params['temp'] ?? ''),
        'pulse' => (string) ($params['pulse'] ?? ''),
        'v_m' => (string) ($params['v_m'] ?? ''),
        'rbs' => (string) ($params['rbs'] ?? ''),
        'rr' => (string) ($params['rr'] ?? ''),
        'edema_feet' => (string) ($params['edema_feet'] ?? ''),
        'cue' => (string) ($params['cue'] ?? ''),
        'cbc' => (string) ($params['cbc'] ?? ''),
        'others' => (string) ($params['others'] ?? ''),
        'usg_report' => (string) ($params['usg_report'] ?? ''),
        'visit_date' => (string) ($params['visit_date'] ?? substr($created_at, 0, 10)),
        'next_visit_date' => (string) ($params['next_visit_date'] ?? ''),
    );

    static $gynae_history_schema = null;
    if ($gynae_history_schema === null) {
        $gynae_history_schema = array();
        $schema_run = mysqli_query($con, 'SHOW COLUMNS FROM `gynae_register_history`');
        if ($schema_run) {
            while ($schema_row = mysqli_fetch_assoc($schema_run)) {
                $gynae_history_schema[] = $schema_row;
            }
        }
    }

    $allowed_fields = array();
    foreach ($gynae_history_schema as $schema_row) {
        $field = $schema_row['Field'];
        if ($field === 'id') {
            continue;
        }
        $allowed_fields[$field] = true;

        $needs_value = ($schema_row['Null'] === 'NO')
            && ($schema_row['Default'] === null)
            && (stripos((string) $schema_row['Extra'], 'auto_increment') === false);

        if ($needs_value && !isset($columns[$field])) {
            $columns[$field] = ycdo_gynae_register_default_for_column(
                $field,
                $schema_row['Type'],
                $created_at,
                $user_id,
                $branch_id,
                $doctor_id
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
        $GLOBALS['ycdo_gynae_register_last_error'] = 'gynae_register_history schema unavailable.';
        return false;
    }

    $sql = 'INSERT INTO `gynae_register_history` (' . implode(', ', $insert_columns) . ') VALUES (' . implode(', ', $insert_values) . ')';
    if (!mysqli_query($con, $sql)) {
        $GLOBALS['ycdo_gynae_register_last_error'] = mysqli_error($con);
        if ($GLOBALS['ycdo_gynae_register_last_error'] === '') {
            $GLOBALS['ycdo_gynae_register_last_error'] = 'Insert into gynae_register_history failed.';
        }
        return false;
    }

    $GLOBALS['ycdo_gynae_register_last_error'] = '';
    return true;
}
