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
    <style>
        .select2-container {
            width: 100% !important;
            min-width: 200px;
        }
        .select2-container--default .select2-selection--single {
            height: calc(1.5em + .75rem + 2px);
            border: 1px solid #ced4da;
            border-radius: .25rem;
        }
        .select2-container--default .select2-selection--single .select2-selection__rendered {
            color: #212529;
            line-height: calc(1.5em + .75rem);
            padding-left: .75rem;
            text-transform: uppercase;
        }
        .select2-container--default .select2-search--dropdown .select2-search__field {
            color: #212529 !important;
            background-color: #fff !important;
            border: 1px solid #ced4da;
            border-radius: .25rem;
            padding: 6px 8px;
            width: 100% !important;
        }
        .select2-container--default .select2-search--dropdown .select2-search__field::placeholder {
            color: #6c757d;
        }
        #branch_select_wrapper .select2-selection {
            display: none;
        }
        #branch_select_wrapper .select2-search--dropdown {
            display: none;
        }
        #branch_search {
            color: #212529;
            background-color: #fff;
            min-width: 200px;
        }
    </style>
    <title>BRANCH CHECKING</title>
</head>
<body style="background: skyblue; background-image: url('images/logo.jpg');background-repeat: no-repeat;background-size: cover;">
<h1 align="right">Youth Community Development Organization</h1>
<p style="color: maroon;text-align: right;">Serve Humanity</p>
<h2 align="right" style="color: red;">YCDO Central Hospital</h2>
<h3 align="right">UAN : 0304-1110222, Multan</h3>
    <div style="padding: 30px;margin: 0% 30%;border: 5px solid black;background: whitesmoke;border-radius: 120px 10px;">

        <div style="">
            <h1 align="center" style="color: skyblue;">WELCOME TO YCDO</h1>
            <h3 align="center">BRANCH VERIFICATION</h3>
            <?php if(isset($msg)){echo '<p style="color: red;text-align: center">'.$msg.'</p>';}  ?>
            <form method="POST" autocomplete="off" action="<?php echo htmlspecialchars(ycdo_form_action_url('action_login.php'), ENT_QUOTES, 'UTF-8'); ?>">
                <label>SELECT BRANCH</label>
                <div id="branch_select_wrapper">
                <input type="text" id="branch_search" class="form-control" placeholder="Type to search branch..." autocomplete="off">
                <select id="branch_select" class="form-control" style="min-width: 200px;" name="branch_id" required>
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
                <label>SELECT ROLE</label>
                <select class="form-control" style="min-width: 200px;text-transform: uppercase;" name="role_id">
<?php 
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

<!--                 <label>IP ADDRESS</label>
                <input class="form-control" type="text" autocomplete="off" required name="ip_address" value="<?php echo $ip_address; ?>" /> -->
                <br>
            <input class="btn btn-sm btn-primary" type="submit" name="verify" value="VERIFICATION">
            <input type="reset" name="reset" class="btn btn-sm btn-warning" value="CLEAR FORM">
            </form>
        </div>
    </div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
$(document).ready(function() {
    var $branchSelect = $('#branch_select');
    var $branchSearch = $('#branch_search');

    $branchSelect.select2({
        placeholder: 'Search branch...',
        allowClear: true,
        minimumResultsForSearch: 0,
        width: '100%',
        dropdownParent: $('#branch_select_wrapper')
    });

    function syncSelect2Search(query) {
        var $search = $('.select2-container--open .select2-search__field');
        if ($search.length) {
            $search.val(query).trigger('input');
        }
    }

    function openBranchDropdown() {
        if (!$branchSelect.data('select2').isOpen()) {
            $branchSelect.select2('open');
        }
    }

    $branchSearch.on('input', function () {
        var query = $(this).val();
        openBranchDropdown();
        setTimeout(function () {
            syncSelect2Search(query);
        }, 0);
    });

    $branchSearch.on('focus click', function () {
        openBranchDropdown();
        setTimeout(function () {
            syncSelect2Search($branchSearch.val());
            $branchSearch.trigger('focus');
        }, 0);
    });

    $branchSelect.on('select2:open', function () {
        setTimeout(function () {
            syncSelect2Search($branchSearch.val());
            $branchSearch.trigger('focus');
        }, 0);
    });

    $branchSelect.on('select2:select', function (e) {
        $branchSearch.val(e.params.data.text);
    });

    $branchSelect.on('select2:clear', function () {
        $branchSearch.val('');
    });

    $branchSelect.on('select2:close', function () {
        if ($branchSelect.val()) {
            $branchSearch.val($branchSelect.find('option:selected').text());
        }
    });

    $('form').on('reset', function () {
        setTimeout(function () {
            $branchSearch.val('');
            $branchSelect.val(null).trigger('change');
        }, 0);
    });
});
</script>
</body>
</html>