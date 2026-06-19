<?php
include 'includes/connect.php';
require_once __DIR__ . '/../includes/gynae_helpers.php';

if (isset($_POST['save'])) {
    $token_no = (int) ($_POST['token_no'] ?? 0);
    $doctor_id = (int) ($_POST['doctor_id'] ?? 0);
    $last_id = ycdo_gynae_register_insert($con, array(
        'token_no' => $token_no,
        'weeks' => $_POST['weeks'] ?? '',
        'remarks' => $_POST['remarks'] ?? '',
        'phone' => $_POST['phone'] ?? '',
        'lmp' => $_POST['lmp'] ?? '',
        'years_marriage' => $_POST['years_marriage'] ?? '0',
        'height' => $_POST['height'] ?? '0',
        'weight' => $_POST['weight'] ?? '0',
        'blood_group' => $_POST['blood_group'] ?? '',
        'husband_blood_group' => $_POST['husband_blood_group'] ?? '',
        'menstrual_cycle' => $_POST['menstrual_cycle'] ?? '',
        'psh' => $_POST['psh'] ?? '',
        'pmh' => $_POST['pmh'] ?? '',
        'husband_name' => $_POST['husband_name'] ?? '',
        'husband_phone' => $_POST['husband_phone'] ?? '',
        'gravida' => $_POST['gravida'] ?? '',
        'next_visit_date' => $_POST['next_visit_date'] ?? '',
        'doctor_id' => $doctor_id,
        'user_id' => $user_id,
        'branch_id' => $branch_id,
        'created' => $current_date,
        'register_by_doctor' => $doctor_id,
    ));
    if ($last_id > 0) {
        $print_url = 'gynae_registeration_file_print.php?reg_id=' . $last_id;
        ?>
<!DOCTYPE html>
<html>
<head><meta charset="utf-8"><title>Saving…</title></head>
<body>
<script>
window.open(<?php echo json_encode(ycdo_absolute_url_if_relative($print_url)); ?>, '_blank', 'toolbar=no,scrollbars=no,resizable=no,width=400,height=400');
window.location.replace('gynae_registeration.php');
</script>
</body>
</html>
<?php
        exit;
    }
    $err_qs = 'msg=error&token_no=' . $token_no . '&err=' . rawurlencode(ycdo_gynae_register_insert_error());
    header('Location: gynae_registeration_file_add.php?' . $err_qs);
    exit;
}

$preselect_token = (int) ($_GET['token_no'] ?? 0);
$form_error = (isset($_GET['msg']) && $_GET['msg'] === 'error' && !empty($_GET['err']))
    ? (string) $_GET['err']
    : '';

include 'includes/head.php';
?>
	<title>Patient Registeration - <?php echo $company_trademark; ?></title>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/selectize.js/0.12.6/js/standalone/selectize.min.js" integrity="sha256-+C0A5Ilqmu4QcSPxrlGpaZxJ04VjsRjKu+G82kl5UJk=" crossorigin="anonymous"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/selectize.js/0.12.6/css/selectize.bootstrap3.min.css" integrity="sha256-ze/OEYGcFbPRmvCnrSeKbRTtjG4vGLHXgOqsyLFTRjg=" crossorigin="anonymous" />
<script>
  $(document).ready(function () {
      $('#token_no_select').selectize({ sortField: 'text' });
      $('#blood_group_select').selectize({ sortField: 'text' });
      $('#husband_blood_group_select').selectize({ sortField: 'text' });
  });
  function syncGynaeFileForm() {
      var sel = document.getElementById('token_no_select');
      if (sel && sel.selectize) {
          var val = sel.selectize.getValue();
          if (val) { sel.value = val; }
      }
      return true;
  }
</script>
</head>

<body class="background_image_ycdo" onkeydown="return (event.keyCode != 116)">
	<div class="col-md-12" style="text-align: center;background: lightgreen;">
		<label><h1><?php echo $company_name?> </h1></label>
	</div>
<div>

	<div class="">

		<div class="row" style="margin: 0px;">

        	<div class="col-md-3 background_whitesmoke">
        		<?php include 'left_navigation.php'; ?>
        	</div>
			<div class="col-md-9">
<?php if ($form_error !== '') { ?>
			    <div class="alert alert-danger"><strong>Registration could not be saved.</strong> <?php echo htmlspecialchars($form_error, ENT_QUOTES, 'UTF-8'); ?></div>
<?php } ?>
			    <table class = "table">
			            <tr>
		                <form method="POST" id="gynae_file_form" onsubmit="return syncGynaeFileForm();">
			                <th colspan = "13">
			                    <div class = "row">
			                        <div class = "col-md-4">
			                            <label>TOKEN NO</label>
        			                    <select name="token_no" id="token_no_select" class="form-control" required>
        			                    <?php
        			                    $eligible_tokens = ycdo_gynae_eligible_tokens_list($con, $branch_id);
        			                    if (count($eligible_tokens) === 0) {
        			                        echo '<option value="">No gynae tokens available</option>';
        			                    }
        			                    foreach ($eligible_tokens as $row) {
        			                        $token_no = (int) $row['token_no'];
        			                        $patinet_name = htmlspecialchars($row['patient_name'], ENT_QUOTES, 'UTF-8');
        			                        $selected = ($preselect_token === $token_no) ? ' selected' : '';
        			                        echo '<option value="' . $token_no . '"' . $selected . '>' . $token_no . ' - ' . $patinet_name . '</option>';
        			                    }
        			                    ?>
        			                    </select>
			                        </div>
			                        <div class = "col-md-4">
        			                    <label title = "Last Menstrual Period">L.M.P</label>
        			                    <input type = "date" name = "lmp" required class = "form-control" />
			                        </div>
			                        <div class = "col-md-4">
        			                    <label title = "ESTIMATE DELIVERY DATE">E.D.D</label>
        			                    <input type = "date" name = "weeks" required class = "form-control" />
			                        </div>
			                        <div class = "col-md-4">
        			                    <label for = "years_marriage">YEARS MARRIAGE</label>
        			                    <input type = "number" name = "years_marriage" id = "years_marriage" min="0" max = "999" value = "0" required class = "form-control" />
			                        </div>
			                        <div class = "col-md-4">
        			                    <label for = "height">HEIGHT</label>
        			                    <input type = "number" name = "height" id = "height" min="0" max = "999" value = "0" class = "form-control" />
			                        </div>
			                        <div class = "col-md-4">
        			                    <label for = "weight">WEIGHT</label>
        			                    <input type = "number" name = "weight" id = "weight" min="0" max = "999" value = "0" class = "form-control" />
			                        </div>
			                        <div class = "col-md-4">
        			                    <label>GRAVIDA</label>
        			                    <input type = "TEXT" name = "gravida" maxlength="10" required class = "form-control" />
			                        </div>
			                        <div class = "col-md-4">
        			                    <label for = "blood_group">BLOOD GROUP</label>
                                        <select name="blood_group" id="blood_group_select" class="form-control">
                                            <option value="">Select a Blood Group...</option>
                                            <option value="A+">A+</option>
                                            <option value="AB+">AB+</option>
                                            <option value="B+">B+</option>
                                            <option value="O+">O+</option>
                                            <option value="A-">A-</option>
                                            <option value="AB-">AB-</option>
                                            <option value="B-">B-</option>
                                            <option value="O-">O-</option>
                                        </select>
			                        </div>
			                        <div class = "col-md-4">
        			                    <label>PHONE</label>
        			                    <input type = "text" name = "phone" pattern="03[0-9]{9}" title="11-digit phone starting with 03" required class = "form-control" />
			                        </div>
			                        <div class = "col-md-4">
        			                    <label>HUSBAND NAME</label>
        			                    <input type = "TEXT" name = "husband_name" maxlength="30" required class = "form-control" />
			                        </div>
			                        <div class = "col-md-4">
        			                    <label for = "husband_blood_group">HUSBAND BLOOD GROUP</label>
                                        <select name="husband_blood_group" id="husband_blood_group_select" class="form-control">
                                            <option value="">Select a Blood Group...</option>
                                            <option value="A+">A+</option>
                                            <option value="AB+">AB+</option>
                                            <option value="B+">B+</option>
                                            <option value="O+">O+</option>
                                            <option value="A-">A-</option>
                                            <option value="AB-">AB-</option>
                                            <option value="B-">B-</option>
                                            <option value="O-">O-</option>
                                        </select>
			                        </div>
			                        <div class = "col-md-4">
        			                    <label>HUSBAND PHONE</label>
        			                    <input type = "text" name = "husband_phone" pattern="03[0-9]{9}" title="11-digit phone starting with 03" class = "form-control" />
			                        </div>
			                        <div class = "col-md-4">
        			                    <label>NEXT VISIT DATE</label>
        			                    <input type = "date" name = "next_visit_date" required class = "form-control" />
			                        </div>
			                        <div class = "col-md-4">
        			                    <label>DOCTOR</label>
        			                    <select name = 'doctor_id' class = "form-control" required>
        			                    <?php
        			                    $select_dr = "SELECT * FROM users WHERE role_id = 3 AND branch_id = '$branch_id' AND status = '1' ";
        			                    $run_dr = mysqli_query($con, $select_dr);
        			                    if(mysqli_num_rows($run_dr) > 0)
        			                    {
        			                        while($row_dr = mysqli_fetch_array($run_dr))
        			                        {
        			                            $doctor_id = $row_dr['id'];
        			                            $doctor_name = $row_dr['u_name'];
        			                            echo '<option value = "'.$doctor_id.'">'.$doctor_name.'</option>';
        			                        }
        			                    }
        			                    ?>
        			                    </select>
			                        </div>
			                        <div class = "col-md-4">
        			                    <label for = "menstrual_cycle">MENSTRUAL CYCLE</label>
        			                    <input type = "TEXT" name = "menstrual_cycle" id = "menstrual_cycle" maxlength="50" required class = "form-control" />
			                        </div>
			                        <div class = "col-md-4">
        			                    <label for = "psh">PAST SURGICAL HISTORY</label>
        			                    <input type = "TEXT" name = "psh" id = "psh" maxlength="50" required class = "form-control" />
			                        </div>
			                        <div class = "col-md-4">
        			                    <label for = "pmh">PAST MEDICAL HISTORY</label>
        			                    <input type = "TEXT" name = "pmh" id = "pmh" maxlength="50" required class = "form-control" />
			                        </div>
			                        <div class = "col-md-4">
        			                    <label>REMARKS</label>
        			                    <input type = "TEXT" name = "remarks" required class = "form-control" />
			                        </div>
			                        <div class = "col p-4">
        			                    <input type = "submit" name = "save" value = "SUBMIT FILE" required class = "btn btn-info" />
			                        </div>
			                    </div>
			                </th>
		                </form>
			            </tr>
			    </table>
			</div>
		</div>

	</div>

</div>


</body>
</html>
<script type="text/javascript" src="js/bootstrap.min.js"></script>
<script type = "text/javascript" >  
    function preventBack() { window.history.forward(); }  
    setTimeout("preventBack()", 0);  
    window.onunload = function () { null };  
</script> 
<script type="text/javascript">
        // setTimeout(function () { window.close(); }, 120000);
</script>
<script>
function myDisplayGone() {
  document.getElementById("clear").style.display = "none";
}
</script> 
<script>
function myDisplayGoneAdd() {
  document.getElementById("add").style.display = "none";
}
</script> 
<script>
function myDisplayGoneSave() {
  document.getElementById("save").style.display = "none";
}
</script>
<?php mysqli_close($con); ?>