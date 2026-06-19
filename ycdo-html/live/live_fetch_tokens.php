<?php
include 'config.php';
session_start();
$branch_id = $_SESSION['branch_id'];

if (isset($_POST['search_query'])) 
{
    // 3800000
    $search = mysqli_real_escape_string($con, $_POST['search_query']);
// 1. Check if the value is numeric and greater than 2,311,000
    if (is_numeric($search) && (int)$search >= 2311000) 
    {
        // Optimized Query with LIMIT 50
        $select = "SELECT t.id, p.name 
                   FROM tokans t 
                   INNER JOIN patients p ON t.patient_id = p.id 
                   INNER JOIN item_by_doctor ibd ON t.id = ibd.tokan_no 
                   WHERE ibd.branch_id = $branch_id
                   AND t.id > 2311000 
                   AND t.id LIKE '$search%' 
                   AND ibd.category_id = 41
                   GROUP BY t.id 
                   ORDER BY t.id DESC 
                   LIMIT 20";
    
        $run = mysqli_query($con, $select);
    
        if (mysqli_num_rows($run) > 0) {
            while ($row = mysqli_fetch_assoc($run)) {
                echo '<option value="' . $row['id'] . '">' . $row['id'] . ' - ' . $row['name'] . '</option>';
            }
        } else {
            echo '<option value="">No matches found</option>';
        }
    }
    else {
        // 2. Optional: Provide feedback if the number is too small
        echo '<option value="">Keep typing (Min ID: 2,311,000)...</option>';
    }
}
?>