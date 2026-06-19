<?php 
include '../lab/includes/config.php';
include 'connect.php'; 
if(!empty($_POST["branch_id"])) 
{
    $branch_id = $_POST['branch_id'];
    
    // Adjust this query to match how your doctors are linked to branches
    // Assuming your 'users' table has a 'branch_id' column
    $query = mysqli_query($con, "SELECT id, u_name FROM users 
                                 WHERE branch_id = '$branch_id' 
                                 AND role_id = 3 AND status = 1
                                 ORDER BY u_name ASC");
    
    echo '<option value="">All Doctors</option>';
    if(mysqli_num_rows($query) > 0) {
        while($row = mysqli_fetch_array($query)) {
            echo '<option value="'.$row['id'].'">'.$row['u_name'].'</option>';
        }
    } else {
        echo '<option value="">No Doctors Found</option>';
    }
}
?>