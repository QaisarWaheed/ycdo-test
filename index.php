<?php 
include 'includes/connect.php';
if(isset($_SESSION['fr_id']))
{
    header('location: fr/dashboard.php');
}
elseif(isset($_SESSION['mm_id']))
{
    header('location: mm/dashboard.php');
}
elseif(isset($_SESSION['sm_id']))
{
    header('location: sm/dashboard.php');
}
elseif(isset($_SESSION['ph_id']))
{
    header('location: pharmecy/dashboard.php');
}
elseif(isset($_SESSION['lab_user_id']))
{
    header('location: lab/dashboard.php');
}
elseif(isset($_SESSION['hr_id']))
{
    header('location: hr/dashboard.php');
}
elseif(isset($_SESSION['dr_id']))
{
    header('location: dr/dashboard.php');
}
function get_client_ip() {
    $ipaddress = '';
    if (getenv('HTTP_CLIENT_IP'))
        $ipaddress = getenv('HTTP_CLIENT_IP');
    else if(getenv('HTTP_X_FORWARDED_FOR'))
        $ipaddress = getenv('HTTP_X_FORWARDED_FOR');
    else if(getenv('HTTP_X_FORWARDED'))
        $ipaddress = getenv('HTTP_X_FORWARDED');
    else if(getenv('HTTP_FORWARDED_FOR'))
        $ipaddress = getenv('HTTP_FORWARDED_FOR');
    else if(getenv('HTTP_FORWARDED'))
       $ipaddress = getenv('HTTP_FORWARDED');
    else if(getenv('REMOTE_ADDR'))
        $ipaddress = getenv('REMOTE_ADDR');
    else
        $ipaddress = 'UNKNOWN';
    return $ipaddress;
}
$ip_address = get_client_ip();
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="facebook-domain-verification" content="801c19kvjz4y4w5wk00ay7ro0uaey9" />
    <link rel="manifest" href="manifest.json">
    <script>
        //if browser support service worker
        if ('serviceWorker' in navigator) {
          navigator.serviceWorker.register('sw.js?v=3').then(function () {
            return navigator.serviceWorker.ready;
          }).then(function (reg) {
            if (reg && reg.update) {
              reg.update();
            }
          });
        }
      </script>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" type="text/css" href="css/bootstrap.min.css">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <title>BRANCH CHECKING</title>
    <style>
        body {
            background: skyblue url('images/logo.jpg') no-repeat center center;
            background-size: cover;
            min-height: 100vh;
            padding: 24px 32px;
        }
        .page-header {
            text-align: right;
            margin-bottom: 32px;
        }
        .page-header h1,
        .page-header h2,
        .page-header h3,
        .page-header p {
            margin: 0 0 6px;
        }
        .page-header p {
            color: maroon;
        }
        .page-header h2 {
            color: red;
        }
        .verification-card {
            max-width: 520px;
            margin: 0 auto;
            padding: 40px 36px;
            border: 4px solid #111;
            background: whitesmoke;
            border-radius: 24px;
            box-shadow: 0 12px 32px rgba(0, 0, 0, 0.15);
        }
        .verification-card h1 {
            color: skyblue;
            text-align: center;
            margin: 0 0 8px;
            font-size: 1.75rem;
        }
        .verification-card h3 {
            text-align: center;
            margin: 0 0 28px;
            font-size: 1.1rem;
            letter-spacing: 0.04em;
        }
        .verification-form .form-group {
            margin-bottom: 20px;
        }
        .verification-form label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            font-size: 0.85rem;
            letter-spacing: 0.03em;
        }
        .verification-form .select2-container {
            width: 100% !important;
        }
        .verification-form .select2-container--default .select2-selection--single {
            min-height: 44px;
            border-radius: 12px;
            border: 1px solid #ced4da;
            padding: 6px 12px;
        }
        .verification-form .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 30px;
            text-transform: uppercase;
        }
        .verification-form .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 42px;
        }
        .verification-form .form-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            margin-top: 28px;
        }
        .verification-form .btn {
            border-radius: 12px;
            padding: 10px 20px;
            font-weight: 600;
            letter-spacing: 0.03em;
        }
        .verification-form .alert-msg {
            color: red;
            text-align: center;
            margin-bottom: 16px;
        }
    </style>
</head>
<body>
<div class="page-header">
    <h1>Youth Community Development Organization</h1>
    <p>Serve Humanity</p>
    <h2>YCDO Central Hospital</h2>
    <h3>UAN : 0304-1110222, Multan</h3>
</div>
    <div class="verification-card">
            <h1>WELCOME TO YCDO</h1>
            <h3>BRANCH VERIFICATION</h3>
            <?php if(isset($msg)){echo '<p class="alert-msg">'.$msg.'</p>';}  ?>
            <form class="verification-form" method="POST" autocomplete="off" action="<?php echo htmlspecialchars(ycdo_form_action_url('action_login.php'), ENT_QUOTES, 'UTF-8'); ?>">
                <div class="form-group">
                <label for="branch_select">SELECT BRANCH</label>
                <select id="branch_select" class="form-control" name="branch_id">
<?php 
echo '<option value=""></option>';
$branch = "SELECT * FROM branchs WHERE id != '0' AND status = '1' ORDER BY `address` ASC ";
$run_branch = mysqli_query($con, $branch);
if (mysqli_num_rows($run_branch) > 0) 
{
    while ($row_branch = mysqli_fetch_array($run_branch)) {
        echo '<option value="'.$row_branch['id'].'">'.$row_branch['address'].'</option>';
    }
}
else
{
    echo '<option value="">Add Doctors Data</option>';
}
?>
                </select>
                </div>
                <div class="form-group">
                <label for="role_select">SELECT ROLE</label>
                <select id="role_select" class="form-control" name="role_id">
<?php 
echo '<option value=""></option>';
$user_role = "SELECT * FROM roles WHERE status = '1' ORDER BY `title` ASC ";
$run_user_role = mysqli_query($con, $user_role);
if (mysqli_num_rows($run_user_role) > 0) 
{
    while ($row_user_role = mysqli_fetch_array($run_user_role)) {
        echo '<option value="'.$row_user_role['id'].'">'.$row_user_role['title'].'</option>';
    }
}
else
{
    echo '<option value="">Add Doctors Data</option>';
}
?>
                </select>
                </div>

<!--                 <label>IP ADDRESS</label>
                <input class="form-control" type="text" autocomplete="off" required name="ip_address" value="<?php echo $ip_address; ?>" /> -->
                <div class="form-actions">
            <input class="btn btn-primary" type="submit" name="verify" value="VERIFICATION">
            <input type="reset" name="reset" class="btn btn-warning" value="CLEAR FORM">
                </div>
            </form>
    </div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
$(document).ready(function() {
    $('#branch_select').select2({
        placeholder: 'Type to search branch...',
        allowClear: true,
        width: '100%'
    });
    $('#role_select').select2({
        placeholder: 'Type to search role...',
        allowClear: true,
        width: '100%'
    });
});
</script>
</body>
</html>