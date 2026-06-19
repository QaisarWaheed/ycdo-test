<?php 
include 'includes/connect.php'; 

if(isset($_GET['date'])) {
    $date = $_GET['date'];
    $br_id = $_GET['br_id'];
} else {
    exit(0);
}

// Optimization: Get current month and start date for "All Records"
$current_month = substr($date, 0, 7); // YYYY-MM
$all_records_start = '2025-03-31';

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" type="text/css" href="css/nav_style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" type="text/css" href="css/bootstrap.min.css"> 
    <script src="js/jquery.min.js"></script>    
    <title>GYNAE PROGRESS <?php echo date("d-m-Y", strtotime($date)); ?> - <?php echo get_branch_tag_name_by_id($br_id); ?></title>
    <style>
        @media print {  
            @page { size: A4; margin: 10mm; }    
            body { font-size: 10px; }
            .no-print { display: none; }
        }
        table th, table td { text-align: center; vertical-align: middle !important; }
        .text-left { text-align: left; }
    </style>
</head>
<body>
<div class="row no-print">
    <div class="col-md-12" style="text-align: center; background: lightgreen; padding: 10px;">
        <h1>YCDO</h1>
    </div>
    <div class="col-md-12 background_whitesmoke">
        <?php include 'navigation_top.php'; ?>
    </div>
</div>    

<div class="container-fluid">
    <table class="table table-bordered table-hover" style="margin-top: 20px;">
        <caption style="text-align: center; caption-side: top; color: black;">
            <h2><?php echo $company_name; ?></h2>
            <h2><?php echo get_branch_name_by($br_id); ?></h2>
            <h3>PROGRESS DAILY <?php echo date("d-m-Y", strtotime($date)); ?></h3>
        </caption>
        <thead class="thead-light">
            <tr>
                <th colspan="3"></th>
                <th colspan="3" class="bg-info text-white">TODAY</th>
                <th colspan="3" class="bg-primary text-white">CURRENT MONTH</th>
                <th colspan="3" class="bg-dark text-white">ALL RECORDS</th>
            </tr>
            <tr>
                <th>S#</th>
                <th>NAME</th>
                <th>OPD</th>
                <th>TOKEN</th>
                <th>ONLINE</th>
                <th>DIFF</th>
                <th>TOKEN</th>
                <th>ONLINE</th>
                <th>DIFF</th>
                <th>TOKEN</th>
                <th>ONLINE</th>
                <th>DIFF</th>
            </tr>
        </thead>
        <tbody>
        <?php
        $s = 0;
        // Global Totals
        $t_opd = 0;
        $t_today_tok = 0; $t_today_reg = 0;
        $t_month_tok = 0; $t_month_reg = 0;
        $t_all_tok = 0;   $t_all_reg = 0;

        // Optimized Query: Fetch doctor names and basic stats
        $select_dr = "SELECT u.id, u.u_name FROM `item_by_doctor` ibd 
                      INNER JOIN users u ON ibd.doctor_id = u.id 
                      WHERE ibd.branch_id = '$br_id' 
                      AND ibd.created >= '$current_month-01' 
                      AND ibd.category_id = '41' 
                      GROUP BY u.id";
        
        $run_dr = mysqli_query($con, $select_dr);

        while($dr = mysqli_fetch_array($run_dr)) {
            $dr_id = $dr['id'];
            $s++;

            // 1. Fetch Today's OPD count
            $q_opd = mysqli_query($con, "SELECT COUNT(id) FROM tokans WHERE doctor_id = '$dr_id' AND branch_id = '$br_id' AND created LIKE '$date%' AND tokan_type_id <= 10 AND status = 1");
            $row_opd = mysqli_fetch_row($q_opd);
            $opd_count = $row_opd[0];

            // 2. Optimized Token Aggregation (Today, Month, All) in ONE query
            $q_tokens = mysqli_query($con, "SELECT 
                SUM(CASE WHEN created LIKE '$date%' THEN 1 ELSE 0 END) as today,
                SUM(CASE WHEN created LIKE '$current_month%' THEN 1 ELSE 0 END) as month,
                SUM(CASE WHEN created > '$all_records_start' THEN 1 ELSE 0 END) as total
                FROM item_by_doctor WHERE doctor_id = '$dr_id' AND branch_id = '$br_id' AND category_id = '41'");
            $res_tok = mysqli_fetch_assoc($q_tokens);

            // 3. Optimized Gynae Register Aggregation in ONE query
            $q_reg = mysqli_query($con, "SELECT 
                SUM(CASE WHEN created LIKE '$date%' THEN 1 ELSE 0 END) as today,
                SUM(CASE WHEN created LIKE '$current_month%' THEN 1 ELSE 0 END) as month,
                SUM(CASE WHEN created > '$all_records_start' THEN 1 ELSE 0 END) as total
                FROM gynae_register WHERE doctor_id = '$dr_id' AND branch_id = '$br_id'");
            $res_reg = mysqli_fetch_assoc($q_reg);

            // Calculations
            $today_diff = $res_reg['today'] - $res_tok['today'];
            $month_diff = $res_reg['month'] - $res_tok['month'];
            $all_diff   = $res_reg['total'] - $res_tok['total'];

            // Add to totals
            $t_opd += $opd_count;
            $t_today_tok += $res_tok['today']; $t_today_reg += $res_reg['today'];
            $t_month_tok += $res_tok['month']; $t_month_reg += $res_reg['month'];
            $t_all_tok += $res_tok['total'];   $t_all_reg += $res_reg['total'];

            echo "<tr>
                <td>$s</td>
                <td class='text-left'>{$dr['u_name']}</td>
                <td>$opd_count</td>
                <td>{$res_tok['today']}</td>
                <td>{$res_reg['today']}</td>
                <td class='".($today_diff != 0 ? 'text-danger font-weight-bold' : '')."'>$today_diff</td>
                <td>{$res_tok['month']}</td>
                <td>{$res_reg['month']}</td>
                <td>$month_diff</td>
                <td>{$res_tok['total']}</td>
                <td>{$res_reg['total']}</td>
                <td>$all_diff</td>
            </tr>";
        }
        ?>
        </tbody>
        <tfoot class="bg-light font-weight-bold">
            <tr>
                <th colspan="2">GRAND TOTAL</th>
                <th><?php echo $t_opd; ?></th>
                <th><?php echo $t_today_tok; ?></th>
                <th><?php echo $t_today_reg; ?></th>
                <th><?php echo $t_today_reg - $t_today_tok; ?></th>
                <th><?php echo $t_month_tok; ?></th>
                <th><?php echo $t_month_reg; ?></th>
                <th><?php echo $t_month_reg - $t_month_tok; ?></th>
                <th><?php echo $t_all_tok; ?></th>
                <th><?php echo $t_all_reg; ?></th>
                <th><?php echo $t_all_reg - $t_all_tok; ?></th>
            </tr>
        </tfoot>
    </table>
</div>
</body>
</html>
<?php mysqli_close($con); ?>