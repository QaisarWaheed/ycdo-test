<?php
include '../lab/includes/config.php';
include 'connect.php'; 

// 1. Start the query selecting ONLY the three required fields
// token_no is in 'lt', parameter_name is in 'lrt', u_name is in 'd'
$query = "SELECT 
            lt.token_no, 
            t.created,
            lrt.parameter_name, 
            d.u_name as doctor_name
          FROM lab_tests lt 
          INNER JOIN lab_test_reports ltr ON lt.lab_test_id = ltr.lab_test_id 
          INNER JOIN tokans t ON lt.token_no = t.id 
          INNER JOIN users d ON t.doctor_id = d.id 
          INNER JOIN lab_reporting_tests lrt ON lt.item_id = lrt.item_id 
          WHERE 1=1";

// 2. Apply Date Filters (Required for performance with 3.3M rows)
if(empty($_POST['from_date'])) {
    $today = date('Y-m-d');
    $query .= " AND ltr.lab_test_report_created_at >= '$today 00:00:00'";
} else {
    $from = $_POST['from_date'];
    $to = $_POST['to_date'];
    $query .= " AND ltr.lab_test_report_created_at BETWEEN '$from 00:00:00' AND '$to 23:59:59'";
}

// 3. Apply Optional Filters
if(!empty($_POST['branch_id'])) {
    $query .= " AND t.branch_id = '".$_POST['branch_id']."'";
}
if(!empty($_POST['doctor_id'])) {
    $query .= " AND t.doctor_id = '".$_POST['doctor_id']."'";
}

// 4. Add Order and Limit at the very end
$query .= " ORDER BY ltr.lab_test_report_created_at DESC LIMIT 100";

$result = mysqli_query($con, $query);

// 5. Output the Table
if(mysqli_num_rows($result) > 0) {
    echo '<table class="table table-striped table-bordered">
            <thead>
                <tr>
                    <th>Token No</th>
                    <th>Token Date</th>
                    <th>Test Name</th>
                    <th>Doctor Name</th>
                </tr>
            </thead>
            <tbody>';
    while($row = mysqli_fetch_array($result)) {
        echo "<tr>
                <td>".$row['token_no']."</td>
                <td>".$row['created']."</td>
                <td>".$row['parameter_name']."</td>
                <td>".$row['doctor_name']."</td>
              </tr>";
    }
    echo '</tbody></table>';
} else {
    echo "<div class='alert alert-warning text-center'>No records found for the selected criteria.</div>";
}
?>