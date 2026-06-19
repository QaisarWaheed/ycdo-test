<?php
include 'includes/connect.php';

$msg = '';
$msg_type = 'success';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_branches'])) {
    $branch_addresses = $_POST['branch_address'] ?? [];
    $updated = 0;

    foreach ($branch_addresses as $branch_id => $address) {
        $branch_id = (int) $branch_id;
        $address = trim($address);

        if ($branch_id < 1 || $address === '') {
            continue;
        }

        $address = mysqli_real_escape_string($con, $address);
        $update = "UPDATE `branchs` SET `address` = '$address' WHERE `id` = '$branch_id' AND `status` = '1'";

        if (mysqli_query($con, $update) && mysqli_affected_rows($con) > 0) {
            $updated++;
        }
    }

    if ($updated > 0) {
        $msg = 'Branch names updated. Changes will appear on the login screen immediately.';
    } else {
        $msg = 'No branch names were changed.';
        $msg_type = 'warning';
    }
}

$branch_query = "SELECT `id`, `name`, `tag_name`, `address` FROM `branchs` WHERE `id` != '0' AND `status` = '1' ORDER BY `address` ASC";
$branch_result = mysqli_query($con, $branch_query);
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" type="text/css" href="css/bootstrap.min.css">
    <title>Change Branch Names</title>
</head>
<body style="background: skyblue; background-image: url('images/logo.jpg'); background-repeat: no-repeat; background-size: cover;">
    <div class="container py-4">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="card shadow-sm">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">Change Branch Names</h4>
                        <a href="index.php" class="btn btn-sm btn-secondary">Back to Login</a>
                    </div>
                    <div class="card-body">
                        <p class="text-muted">
                            Edit the branch names shown in the <strong>SELECT BRANCH</strong> dropdown on the login page.
                        </p>

                        <?php if ($msg !== '') { ?>
                            <div class="alert alert-<?php echo htmlspecialchars($msg_type, ENT_QUOTES, 'UTF-8'); ?>">
                                <?php echo htmlspecialchars($msg, ENT_QUOTES, 'UTF-8'); ?>
                            </div>
                        <?php } ?>

                        <?php if ($branch_result && mysqli_num_rows($branch_result) > 0) { ?>
                            <form method="POST" autocomplete="off">
                                <div class="table-responsive">
                                    <table class="table table-bordered table-hover">
                                        <thead class="thead-light">
                                            <tr>
                                                <th style="width: 70px;">ID</th>
                                                <th style="width: 90px;">Tag</th>
                                                <th>Registered Name</th>
                                                <th>Name on Login Screen</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php while ($row = mysqli_fetch_assoc($branch_result)) { ?>
                                                <tr>
                                                    <td><?php echo (int) $row['id']; ?></td>
                                                    <td><?php echo htmlspecialchars($row['tag_name'], ENT_QUOTES, 'UTF-8'); ?></td>
                                                    <td><?php echo htmlspecialchars($row['name'], ENT_QUOTES, 'UTF-8'); ?></td>
                                                    <td>
                                                        <input
                                                            type="text"
                                                            class="form-control"
                                                            name="branch_address[<?php echo (int) $row['id']; ?>]"
                                                            value="<?php echo htmlspecialchars($row['address'], ENT_QUOTES, 'UTF-8'); ?>"
                                                            required
                                                        >
                                                    </td>
                                                </tr>
                                            <?php } ?>
                                        </tbody>
                                    </table>
                                </div>
                                <button type="submit" name="save_branches" value="1" class="btn btn-primary">
                                    Save Branch Names
                                </button>
                            </form>
                        <?php } else { ?>
                            <div class="alert alert-warning mb-0">No active branches found.</div>
                        <?php } ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
