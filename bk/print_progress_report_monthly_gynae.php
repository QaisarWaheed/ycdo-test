<?php 
include 'includes/connect.php'; 

if(isset($_GET['date'])) {
    $date = $_GET['date'];
    $br_id = $_GET['br_id'];
} else {
    exit("Required parameters missing.");
}

// Ensure the date filter is correctly formatted (YYYY-MM%)
$date_filter = mysqli_real_escape_string($con, $date) . '%';
?>
<html>
<head>
    <title>GYNAE PROGRESS - <?php echo date_format(date_create($date), "F Y"); ?></title>
    <style>
        table { border-collapse: collapse; width: 100%; font-family: Segoe UI, Tahoma, sans-serif; font-size: 12px; }
        th, td { border: 1px solid #000; padding: 6px; text-align: center; }
        .text-left { text-align: left; padding-left: 10px; }
        thead, tfoot { background-color: #f2f2f2; font-weight: bold; }
        tr:hover { background-color: #f9f9f9; }
    </style>
</head>
<body>
    
<table>
<caption>
    <h2 style="margin:0;"><?php echo $company_name; ?></h2>
    <h3 style="margin:5px 0;"><?php echo get_branch_name_by($br_id); ?> (Month: <?php echo date_format(date_create($date), "F Y"); ?>)</h3>
</caption>
    <thead>
        <tr>
            <th>S#</th>
            <th>NAME</th>
            <th>OPD</th>
            <th>CONS</th>
            <th>SVD & DNC</th>
            <th>PROCEDURES</th>
            <th>GYNAE TOKEN</th>
            <th>GYNAE FILES</th>
            <th>REFERED PATIENTS</th>
        </tr>
    </thead>
    <tbody>
<?php
// Initialize Master Doctor ID Array (We will collect IDs from ALL sources)
$active_dr_ids = [];

// 1. Fetch OPD totals
$opd_counts = [];
$q_opd = mysqli_query($con, "SELECT doctor_id, COUNT(*) as total FROM tokans WHERE branch_id = '$br_id' AND created LIKE '$date_filter' AND tokan_type_id <= 10 AND status = 1 GROUP BY doctor_id");
while($r = mysqli_fetch_assoc($q_opd)) { 
    $opd_counts[$r['doctor_id']] = $r['total']; 
    $active_dr_ids[] = $r['doctor_id'];
}

// 2. Fetch Gynae Register totals (Using COUNT(*) because 'id' might be missing)
$gynae_file_counts = [];
$q_files = mysqli_query($con, "SELECT doctor_id, COUNT(*) as total FROM gynae_register WHERE branch_id = '$br_id' AND created LIKE '$date_filter' GROUP BY doctor_id");
while($r = mysqli_fetch_assoc($q_files)) { 
    $gynae_file_counts[$r['doctor_id']] = $r['total']; 
    $active_dr_ids[] = $r['doctor_id'];
}

// 3. Fetch Referral totals
$ref_counts = [];
$q_ref = mysqli_query($con, "SELECT from_user_id, COUNT(*) as total FROM referral_patients WHERE branch_id = '$br_id' AND referral_patient_created LIKE '$date_filter' AND referral_patient_status > 1 GROUP BY from_user_id");
while($r = mysqli_fetch_assoc($q_ref)) { 
    $ref_counts[$r['from_user_id']] = $r['total']; 
    $active_dr_ids[] = $r['from_user_id'];
}

// 4. Item-based counts
$item_counts = [];
$sql_items = "SELECT ibd.doctor_id,
    SUM(CASE WHEN i.category_id = '29' THEN 1 ELSE 0 END) as cons_count,
    SUM(CASE WHEN irb.item_id IN (483, 1159, 1321, 1414) THEN 1 ELSE 0 END) as gynae_tok_count,
    SUM(CASE WHEN irb.item_id IN (472, 473, 1118, 1119, 1313, 1314) THEN 1 ELSE 0 END) as svd_count,
    SUM(CASE WHEN i.category_id = '3' THEN 1 ELSE 0 END) as proc_count
    FROM item_by_doctor ibd
    LEFT JOIN item_register_to_branches irb ON ibd.item_id = irb.id
    LEFT JOIN items i ON irb.item_id = i.id
    WHERE ibd.branch_id = '$br_id' AND ibd.created LIKE '$date_filter' AND ibd.status = 2
    GROUP BY ibd.doctor_id";

$q_items = mysqli_query($con, $sql_items);
while($r = mysqli_fetch_assoc($q_items)) { 
    $item_counts[$r['doctor_id']] = $r; 
    $active_dr_ids[] = $r['doctor_id'];
}

// Filter out duplicates and empty IDs
$active_dr_ids = array_unique(array_filter($active_dr_ids));

// 5. Main Loop: Get names for ONLY the doctors that actually have data
$s = 0;
$total_vals = ['opd'=>0, 'cons'=>0, 'svd'=>0, 'proc'=>0, 'g_tok'=>0, 'g_file'=>0, 'ref'=>0];

if(!empty($active_dr_ids)) {
    $ids_string = implode("','", $active_dr_ids);
    // Removed role_id = 3 check here to ensure NO doctor is missed
    $res_dr = mysqli_query($con, "SELECT id, u_name FROM users WHERE id IN ('$ids_string') ORDER BY u_name ASC");

    while($row_dr = mysqli_fetch_assoc($res_dr)) {
        $dr_id = $row_dr['id'];
        
        $d_opd   = $opd_counts[$dr_id] ?? 0;
        $d_file  = $gynae_file_counts[$dr_id] ?? 0;
        $d_ref   = $ref_counts[$dr_id] ?? 0;
        $d_cons  = $item_counts[$dr_id]['cons_count'] ?? 0;
        $d_svd   = $item_counts[$dr_id]['svd_count'] ?? 0;
        $d_proc  = $item_counts[$dr_id]['proc_count'] ?? 0;
        $d_gtok  = $item_counts[$dr_id]['gynae_tok_count'] ?? 0;

        $s++;
        $total_vals['opd']    += $d_opd;
        $total_vals['cons']   += $d_cons;
        $total_vals['svd']    += $d_svd;
        $total_vals['proc']   += $d_proc;
        $total_vals['g_tok']  += $d_gtok;
        $total_vals['g_file'] += $d_file;
        $total_vals['ref']    += $d_ref;

        echo '<tr>
            <td>'.$s.'</td>
            <td class="text-left">'.$row_dr['u_name'].'</td>
            <td>'.$d_opd.'</td>
            <td>'.$d_cons.'</td>
            <td>'.$d_svd.'</td>
            <td>'.$d_proc.'</td>
            <td>'.$d_gtok.'</td>
            <td>'.$d_file.'</td>
            <td>'.$d_ref.'</td>
        </tr>';
    }
} else {
    echo "<tr><td colspan='9'>No data found for this month and branch.</td></tr>";
}
?>
    </tbody>
    <tfoot>
        <tr>
            <th colspan="2">TOTAL</th>
            <th><?php echo $total_vals['opd']; ?></th>
            <th><?php echo $total_vals['cons']; ?></th>
            <th><?php echo $total_vals['svd']; ?></th>
            <th><?php echo $total_vals['proc']; ?></th>
            <th><?php echo $total_vals['g_tok']; ?></th>
            <th><?php echo $total_vals['g_file']; ?></th>
            <th><?php echo $total_vals['ref']; ?></th>
        </tr>
    </tfoot>
</table>
</body>
</html>
<?php mysqli_close($con); ?>