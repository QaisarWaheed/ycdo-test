<?php 
include 'includes/config.php'; 
include 'includes/connect.php'; 
// if(isset($_GET['save_sample']) )
// {
//     print_r($_GET);
//     exit(0);
// }
    if(isset($_POST['save_sample']) )
{
    $token_id = $_POST['token_id'];
    $check_data = "SELECT * FROM `lab_tests` WHERE `token_no` = '$token_id' ";
    $check_run = mysqli_query($con, $check_data);
    if ($check_run && mysqli_num_rows($check_run) > 0)
    {
        $redirect_token = (int) $token_id;
        header('Location: patient_lab_by_token.php?msg=duplicate&token_id=' . $redirect_token);
        exit;
    }
    
    $select_vails = "SELECT item_register_to_branches.id AS branch_register_item_id, item_register_to_branches.quantity, items.name, items.id, categories.name as title FROM `item_register_to_branches` INNER JOIN items ON item_register_to_branches.item_id = items.id INNER JOIN categories ON items.category_id = categories.id WHERE (item_register_to_branches.item_id IN (SELECT id FROM items WHERE category_id = 7) OR item_register_to_branches.item_id = 917) AND item_register_to_branches.branch_id = '$lab_login_branch_id' ";
    $run_vails = mysqli_query($con, $select_vails);
    if ($run_vails && mysqli_num_rows($run_vails) > 0)
    {
        while($row_vail = mysqli_fetch_array($run_vails))
        {
            $get_id = 'vail'.$row_vail['id'];
            $item_id = $row_vail['id'];
            $branch_register_item_id = $row_vail['branch_register_item_id'];
            if (!empty($_POST[$get_id]))
            {
                $insert_vail = "INSERT INTO `lab_test_vails`
                (`lab_test_vail_id`, `item_id`, `lab_test_vail_no`, `lab_test_vail_created_at`, `lab_test_vail_created_by`, `lab_test_vail_status`, `branch_register_item_id`, `token_id`, `branch_id`) 
                VALUES
                (NULL, '$item_id', '".$_POST[$get_id]."', '$current_date', '$lab_user_id', '1', '$branch_register_item_id', '$token_id', '$lab_login_branch_id')";
                mysqli_query($con, $insert_vail);
                $update_branch_item = "UPDATE `item_register_to_branches` SET `quantity` = `quantity` - '1' WHERE `id` = '$branch_register_item_id' ";
                mysqli_query($con, $update_branch_item);
            }
        }
    }
    
    $token_branch_id = (int) $lab_login_branch_id;
    $branch_run = mysqli_query($con, "SELECT branch_id FROM tokans WHERE id = '" . (int) $token_id . "' LIMIT 1");
    if ($branch_run && ($branch_row = mysqli_fetch_assoc($branch_run))) {
        $token_branch_id = (int) $branch_row['branch_id'];
    }

    $run = mysqli_query($con, "SELECT item_by_doctor.id AS record_id, items.id AS item_id, items.name AS item_name, items.poor, items.member, items.general, tokans.tokan_type_id FROM `item_by_doctor` INNER JOIN item_register_to_branches ON item_by_doctor.item_id = item_register_to_branches.id INNER JOIN items ON item_register_to_branches.item_id = items.id INNER JOIN tokans ON item_by_doctor.tokan_no = tokans.id WHERE `tokan_no` = '$token_id' AND items.category_id = 2 ");
    if ($run && mysqli_num_rows($run) > 0) 
    {
        $insert_error = '';
        while ($row = mysqli_fetch_array($run)) 
        {
            $item_id = $row['item_id'];
            $record_id = $row['record_id'];
            $comments_key = 'comments' . $record_id;
            $reporting_date_time = $_POST[$record_id] ?? $current_date;
            $sample_comments = $_POST[$comments_key] ?? '';

            $lab_test_rate = $row['general'];
            if ($row['tokan_type_id'] == 102) {
                $lab_test_rate = $row['poor'];
            } elseif ($row['tokan_type_id'] == 103) {
                $lab_test_rate = $row['member'];
            }

            if (!lab_insert_test_sample($con, array(
                'token_no' => $token_id,
                'item_id' => $item_id,
                'user_id' => $lab_user_id,
                'branch_id' => $token_branch_id,
                'created_at' => $current_date,
                'reporting_date_time' => $reporting_date_time,
                'lab_test_rate' => $lab_test_rate,
                'sample_comments' => $sample_comments,
            ))) {
                $insert_error = mysqli_error($con);
                if ($insert_error === '') {
                    $insert_error = 'Lab test insert failed for item id ' . (int) $item_id . '.';
                }
                break;
            }
        }

        if ($insert_error === '') {
            $print_url = ycdo_absolute_url('print_test_report_slip.php', 'token_no=' . (int) $token_id);
            echo '<script type="text/javascript">window.open(' . json_encode($print_url) . ');</script>';
            echo '<script>window.location.href = ' . json_encode('patient_lab_by_token.php?msg=success&token_id=' . (int) $token_id) . ';</script>';
            exit(0);
        }

        $err_qs = 'msg=error&token_id=' . (int) $token_id . '&err=' . rawurlencode($insert_error);
        header('Location: patient_lab_by_token.php?' . $err_qs);
        exit;
    }
    else
    {
        $err_qs = 'msg=error&err=' . rawurlencode('No lab tests found for this token.');
        header('Location: patient_lab_by_token.php?' . $err_qs);
        exit;
    }
    exit(0);    
}
$roles = "SELECT * FROM roles WHERE id IN (SELECT role_id FROM users WHERE id = '$lab_user_id') ";
$run_roles = mysqli_query($con, $roles);
if(mysqli_num_rows($run_roles) == 1)
{
    while($row_role = mysqli_fetch_array($run_roles))
    {
        $role_title = $row_role['title'];
    }
}
else
{
    $role_title = '';
}
?>
	<title>1st Turn - Lab - <?php echo $company_trademark; ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
<style>
.background_image{
	background-image: url('../images/background.png');
	background-size: cover;
}
</style>
</head>

<body class="background_image">
		<?php include 'top_navigation.php'; ?>
<?php if (isset($_GET['msg']) && $_GET['msg'] === 'success') { ?>
    <div class="alert alert-success m-2">Sample saved successfully<?php if (!empty($_GET['token_id'])) { echo ' for token ' . (int) $_GET['token_id']; } ?>.</div>
<?php } elseif (isset($_GET['msg']) && $_GET['msg'] === 'duplicate') { ?>
    <div class="alert alert-warning m-2">Sample was already saved for this token<?php if (!empty($_GET['token_id'])) { echo ' (' . (int) $_GET['token_id'] . ')'; } ?>.</div>
<?php } elseif (isset($_GET['msg']) && $_GET['msg'] === 'error') { ?>
    <div class="alert alert-danger m-2"><strong>Sample could not be saved.</strong>
    <?php if (!empty($_GET['err'])) { ?>
        <?php echo htmlspecialchars((string) $_GET['err'], ENT_QUOTES, 'UTF-8'); ?>
    <?php } else { ?>
        Try again or contact support.
    <?php } ?>
    </div>
<?php } ?>
<div class="row" style="">
	<div class="col-md-12">
        <form method = "POST">
            <div class="col-md-12">
            <fieldset class="border p-2">
            <legend style="font-size: 14px;" class="w-auto">SEARCH PATIENT TOKEN </legend>
            <div class="row">
                <div class="col-md-12">
                    <div class="form-group row">   
                        <div class="col-sm-10">
                            <input autocomplete="off" autofocus  type="number" id="token_id" required name="token_id" class = "form-control" pattern="[0-9]{1,}" title="ENTER PATIENT TOKEN NO" placeholder = "ENTER PATIENT TOKEN NO">
                        </div> 
                        <div class="col-sm-2">
                            <input type="submit" class="btn btn-outline-success" value="SEARCH" />
                        </div>
                    </div>  
                </div>  
            </div>
            </fieldset> 
            </div>
        </form>
<?php
if(isset($_POST['token_id']) && $_POST['token_id'] != '')
{ ?>
	    <div class="row">
        	<div class="col-md-10">
            	    <?php
            	    if(isset($_POST['token_id']) && $_POST['token_id'] != '')
            	    {
            	        $token_id = $_POST['token_id'];
            	        $select_token = "SELECT * FROM tokans WHERE id = '$token_id' ";
            	        $run_token = mysqli_query($con, $select_token);
            	        if(mysqli_num_rows($run_token) == 1)
            	        {
            	            while($row_token = mysqli_fetch_array($run_token))
            	            {
            	                $token_date = date_format(date_create($row_token['created']), 'd-F-Y');
            	                $token_time = date_format(date_create($row_token['created']), 'h:i:s A');
            	                $token_branch_tag_name = get_branch_tag_by($row_token['branch_id']);
            	                $docotr_id = $row_token['doctor_id'];
            	                $cash = $row_token['cash'];
            	                $cash_received = $row_token['cash_received'];
            	                $token_type_id = $row_token['tokan_type_id'];
                	                $token_type = "SELECT title FROM `tokan_types` WHERE id = '$token_type_id' ";
                	                $run_token_type = mysqli_query($con, $token_type);
                                    if (mysqli_num_rows($run_token_type) == 1) 
                                    {
                                        while ($row_token_type = mysqli_fetch_array($run_token_type)) 
                                        {
                                            $token_type_title = $row_token_type['title'];
                                        }
                                    }
            	                $token_by = get_uname_by_id($row_token['user_id']);
            	                $docotr_name = get_uname_by_id($docotr_id);
            	                $patient_id = $row_token['patient_id'];
            	                    $get_patient = mysqli_query($con, "SELECT * FROM patients WHERE id = '$patient_id' ");
                                    if (mysqli_num_rows($get_patient) == 1) 
                                    {
                                        while ($row_patient = mysqli_fetch_array($get_patient)) 
                                        {
                                            $name = $row_patient['name'];
                                            $age = $row_patient['age'];
                                            $cnic = $row_patient['cnic'];
                                            if($cnic == ''){$cnic = 'N/A';}
                                            $phone = $row_patient['phone'];
                                            if($phone == ''){$phone = 'N/A';}
                                            $gender = $row_patient['gender'];
                                            if($gender == '1'){$gender = 'Female';}elseif($gender == '2'){$gender = 'Male';}else{$gender = 'Transgender';}
                                        }
                                    }
            	   ?>  
                    </div>
                </div>
            	   <?php
            	            }
            	        }
            	    }
?>
        <form method = "POST">
            <input type="hidden" name="token_id" value="<?php echo $_POST['token_id']; ?>" />
            <div class="col-md-12">
            <fieldset class="border p-2">
            <legend style="font-size: 14px;" class="w-auto">SELECTED TEST FOR TOKEN <strong><?php echo $token_id; ?></strong></legend>
            <div class="row">
                <div class="col-md-12">
                    <table class = "table table-hover">
                        <caption style = "caption-side: top;color: black;">
                            <table class = "table">
                                <tr>
                                    <td>Name</td>
                                    <th><u><?php echo $name; ?></u> / <u><?php echo $age; ?></u></th>
                                    <td>Gender</td>
                                    <th><u><?php echo $gender; ?></u></th>
                                    <td>Phone</td>
                                    <th><u><?php echo $phone; ?></u></th>
                                    <td>Token Date & Time</td>
                                    <th colspan = "2"><u><?php echo $token_time; ?></u> <u><?php echo $token_date; ?></u></th>
                                </tr>
                                <tr>
                                    <td>Cash</td>
                                    <th><u><?php echo $cash; ?></u></th>
                                    <td>Received</td>
                                    <th><u><?php echo $cash_received; ?></u></th>
                                    <td>Token By</td>
                                    <th><u><?php echo $token_by; ?></u></th>
                                    <th>Doctor:<u><?php echo $docotr_name; ?></u></th>
                                    <td>Type:<u><?php echo $token_type_title; ?></u></td>
                                    <th>Branch:<u><?php echo $token_branch_tag_name; ?></u></th>
                                </tr>
                                <tr>
                                    <td colspan = "9">
                                            <div class = "row">
                                    <?php
                                    $select_vails = "SELECT item_register_to_branches.id AS branch_register_item_id, item_register_to_branches.quantity, items.name, items.id, categories.name as title FROM `item_register_to_branches` INNER JOIN items ON item_register_to_branches.item_id = items.id INNER JOIN categories ON items.category_id = categories.id WHERE (item_register_to_branches.item_id IN (SELECT id FROM items WHERE category_id = 7) OR item_register_to_branches.item_id = 917) AND item_register_to_branches.branch_id = '$lab_login_branch_id' ";
                                    $run_vails = mysqli_query($con, $select_vails);
                                    if(mysqli_num_rows($run_vails) > 0)
                                    {
                                        while($row_vail = mysqli_fetch_array($run_vails))
                                        { ?>
                                                <div class = "col">
                                                    <label><?php echo $row_vail['name'].' - '.$row_vail['title']; ?> </label>
                                                    <input type = "text" name = "vail<?php echo $row_vail['id']; ?>" class = "form-control" />
                                                </div>
                                        <?php }
                                    }
                                    ?>
                                            </div>
                                    </td>
                                </tr>
                            </table>
                        </caption>
                        <thead>
                            <tr>
                                <th>S #</th>
                                <th>Test Name</th>
                                <th>Reporting Time</th>
                                <th>Comments</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php echo get_given_services_by_token_no($token_id); ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan = "4">
                                    <input type = "submit" name = "save_sample" value = "SAVE SAMPLE" class = "btn btn-xs btn-success" />
                                    <input type = "reset" value = "CLEAR" class = "btn btn-xs btn-warning" />
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
            </fieldset>
            </div>
            </div>
        </form>
<?php } ?>
	</div>
</div>
    <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.12.9/dist/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>
</body>
</html>
</html>
