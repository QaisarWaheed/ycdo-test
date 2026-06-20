<?php
$user_label = htmlspecialchars($user_name, ENT_QUOTES, 'UTF-8');
if ((int) $is_incharge === 2) {
    $user_label .= ' Incharge';
}
$role_label = htmlspecialchars($role_title, ENT_QUOTES, 'UTF-8');
$last_token = htmlspecialchars((string) last_token_by_user($user_id), ENT_QUOTES, 'UTF-8');
?>
<div class="reception-info-panel">
    <?php if ((int) $branch_id === 15) { ?>
        <img class="reception-info-branch-image" src="images/city_police_multan_2.png" alt="POLICE &amp; YCDO DRUG REHABILITATION HOSPITAL">
    <?php } else { ?>
        <div class="reception-info-panel__header">
            <h2><?php echo htmlspecialchars($company_name, ENT_QUOTES, 'UTF-8'); ?></h2>
            <p><?php echo htmlspecialchars($company_ambition, ENT_QUOTES, 'UTF-8'); ?></p>
        </div>
    <?php } ?>

    <table class="table reception-info-table">
        <tbody>
            <?php if ((int) $branch_id !== 15) { ?>
            <tr>
                <th scope="row">Branch</th>
                <td><?php echo htmlspecialchars($branch_name, ENT_QUOTES, 'UTF-8'); ?></td>
            </tr>
            <tr>
                <th scope="row">Address</th>
                <td><?php echo htmlspecialchars($branch_address, ENT_QUOTES, 'UTF-8'); ?></td>
            </tr>
            <?php } ?>
            <tr>
                <th scope="row">UAN</th>
                <td><?php echo htmlspecialchars($company_phone, ENT_QUOTES, 'UTF-8'); ?></td>
            </tr>
            <tr>
                <th scope="row">User</th>
                <td><?php echo $user_label; ?><?php if ($role_label !== '') { echo ' (' . $role_label . ')'; } ?></td>
            </tr>
            <tr class="reception-info-highlight">
                <th scope="row">Last Token No</th>
                <td><?php echo $last_token; ?></td>
            </tr>
        </tbody>
    </table>
</div>
