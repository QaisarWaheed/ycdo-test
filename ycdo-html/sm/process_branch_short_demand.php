<?php
include 'includes/config.php';

if (isset($_POST['process_branch_short_demand'])) 
{
    mysqli_begin_transaction($con);
    try 
    {
        $br_id = mysqli_real_escape_string($con, $_POST['br_id']);
        $query = "SELECT `issue_id` FROM `item_register_branchs_by_sm` ORDER BY `issue_id` DESC LIMIT 1 FOR UPDATE";
        $run = mysqli_query($con, $query);
        
        if(mysqli_num_rows($run) >= 1) 
        {
            $row = mysqli_fetch_array($run);
            $issue_id = $row['issue_id'] + 1;
        } 
        else 
        {
            $issue_id = 1; // Start at 1 if table is empty
        }
        foreach ($_POST['item_id'] as $key => $id) 
        {
            $item_id = mysqli_real_escape_string($con, $id);
            $qty = mysqli_real_escape_string($con, $_POST['issue_quantity'][$key]);
            $branch_item_id = mysqli_real_escape_string($con, $_POST['reg_item_id'][$key]);
            $item_box_size = mysqli_real_escape_string($con, $_POST['item_box_size'][$key]);

            // Only process if quantity is greater than 0
            if ($qty > 0) {
                $update_store = "UPDATE `items` SET `quantity` = `quantity` - $qty, `updated_at` = '$current_date', `updated_by` = $user_id WHERE `id` = $item_id";
                $insert_branch_store = "INSERT INTO `item_register_branchs_by_sm` 
                    (`branch_item_id`, `branch_id`, `quantity`, `sm_id`, `status`, `created`, `issue_id`, `pack_size`) 
                    VALUES ('$branch_item_id', '$br_id', '$qty', '$user_id', '1', '$current_date', '$issue_id', '$item_box_size')";

                if (!mysqli_query($con, $update_store) || !mysqli_query($con, $insert_branch_store)) 
                {
                    throw new Exception("Database error on item ID: $item_id");
                }
            }
        }
        mysqli_commit($con);
        $message = urlencode("Success: All records processed under Issue ID: $issue_id");
        header("Location: dashboard.php?msg=$message");
        exit(); // Always call exit() after a header redirect

    } 
    catch (Exception $e) 
    {
        mysqli_rollback($con);
        // Redirect to dashboard with an error message
        $error = urlencode("Transaction Failed: " . $e->getMessage());
        header("Location: dashboard.php?error=$error");
        exit();
    }
}
?>