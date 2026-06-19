<?php 
include 'includes/connect.php';
include 'includes/head.php'; 

// 1. Session & Access Control
if(!isset($_SESSION['ao_id'])) {
    header('location: logout.php');
    exit();
}

$user_id = $_SESSION['ao_id'];

if(isset($_GET['audit_id']) && $_GET['audit_id'] != '') {
    $audit_id = mysqli_real_escape_string($con, $_GET['audit_id']);
    $br_id = mysqli_real_escape_string($con, $_GET['br_id']);
    
    // Security check: Ensure this audit belongs to the logged-in officer (unless Admin ID 1)
    // if($user_id != 1) 
    // {
    //     $select_check = "SELECT * FROM `audit_branch_form` WHERE `audit_officer_id` = '$user_id' AND `id` = '$audit_id' ";
    //     $run_check = mysqli_query($con, $select_check);
    //     if(mysqli_num_rows($run_check) != 1) {
    //         echo "<script>alert('Access Denied: You do not have permission for this audit.'); window.location='dashboard.php';</script>";
    //         exit(0);
    //     }
    // }
} else {
    header('location: logout.php');
    exit();
}
?>
<title>AUDIT BRANCH FORM - <?php echo $company_trademark; ?></title>

<!-- Modern UI Assets -->
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<style>
    /* Reset & General */
    body { font-family: 'Inter', sans-serif; background-color: #fcfcfc; }
    @page { size: A4; margin: 10px 0px; }
    @media print { .noprint { display: none !important; } }

    /* Professional Modal CSS */
    .custom-modal {
        display: none;
        position: fixed;
        z-index: 9999;
        left: 0; top: 0;
        width: 100%; height: 100%;
        background-color: rgba(15, 23, 42, 0.6); 
        backdrop-filter: blur(8px);
        animation: fadeIn 0.3s ease;
    }

    .modal-content-box {
        background-color: #ffffff;
        margin: 4% auto;
        width: 90%;
        max-width: 900px;
        border-radius: 16px;
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
        overflow: hidden;
        border: 1px solid rgba(255, 255, 255, 0.1);
        animation: slideUp 0.4s cubic-bezier(0.16, 1, 0.3, 1);
    }

    .modal-header {
        padding: 20px 30px;
        background: #ffffff;
        border-bottom: 1px solid #f1f5f9;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .modal-header h3 { margin: 0; font-size: 1.15rem; color: #1e293b; font-weight: 600; }
    .close-btn { font-size: 24px; color: #94a3b8; cursor: pointer; transition: 0.2s; }
    .close-btn:hover { color: #ef4444; }

    .modal-body { padding: 30px; max-height: 65vh; overflow-y: auto; background: #ffffff; }

    /* Loading Spinner */
    .professional-loader { display: flex; flex-direction: column; align-items: center; padding: 50px 0; }
    .spinner-ring {
        width: 40px; height: 40px;
        border: 3px solid #f1f5f9;
        border-top: 3px solid #3b82f6;
        border-radius: 50%;
        animation: spin 0.8s linear infinite;
    }

    @keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
    @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
    @keyframes slideUp { from { transform: translateY(40px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }
</style>
</head>

<body>
<div class="col-md-12 noprint" style="text-align: center; margin-bottom: 10px;">
    <?php include 'top_row.php'; ?>
</div>

<div class="container-fluid">
    <table class="table" border="1" style="font-size: 15px; width: 100%; border-collapse: collapse;">
        <thead>
            <tr style="background: #f8f9fa;">
                <th colspan="10">
                    <h3 align="center" style="margin: 10px 0;"><?php echo $branch_address; ?></h3>
                </th>
            </tr>
            <tr style="background: #eee; font-size: 13px;">
                <th>SR</th>
                <th>ITEM NAME</th>
                <th>PRICE</th>
                <th>CATEGORY</th>
                <th>UPDATED AT</th>
                <th>COMPUTER</th>
                <th>MANUAL</th>
                <th>ISSUED</th>
                <th>CONSUMED</th>
                <th>CURRENT STOCK</th>
            </tr>
        </thead>
        <tbody>
    <?php
    $s = 0;
    // OPTIMIZED SQL: Single Trip Database Query
    $select = "SELECT 
        abd.id, 
        abd.branch_item_id, 
        irb.quantity AS available_quantity, 
        items.name AS item_name, 
        cats.name AS cat_name, 
        abd.computer_quantity, 
        abd.manual_quantity, 
        abd.item_poor_price, 
        abd.updated_at,
        (SELECT SUM(quantity) FROM item_register_branchs_by_sm 
         WHERE branch_item_id = abd.branch_item_id AND created > abd.updated_at) AS issued_quantity
    FROM audit_branch_detail abd
    INNER JOIN item_register_to_branches irb ON abd.branch_item_id = irb.id 
    INNER JOIN items ON irb.item_id = items.id 
    INNER JOIN categories cats ON items.category_id = cats.id
    WHERE abd.audit_id = '$audit_id' AND irb.branch_id = '$br_id'";

    $run = mysqli_query($con, $select);

    if(mysqli_num_rows($run) > 0) {
        while($row = mysqli_fetch_array($run)) {
            $s++;
            $branch_item_id = $row['branch_item_id'];
            $updated_at = $row['updated_at'];
            $issued_q = $row['issued_quantity'] ?? 0;

            echo '<tr>
                <td>'.$s.'</td>
                <td><strong>'.$branch_item_id.'</strong> - '.$row['item_name'].'</td>
                <td>'.number_format($row['item_poor_price'], 2).'</td>
                <td>'.$row['cat_name'].'</td>
                <td>'.date("d-M-Y", strtotime($updated_at)).'</td>
                <td>'.$row['computer_quantity'].'</td>
                <td>'.$row['manual_quantity'].'</td>
                <td style="color: blue; font-weight: bold;">'.$issued_q.'</td>
                <td>
                    <button type="button" class="view-consumed-data" 
                            data-brid="'.$br_id.'" 
                            data-itemid="'.$branch_item_id.'" 
                            data-updated="'.$updated_at.'"
                            style="cursor:pointer; background:#3b82f6; color:white; border:none; border-radius:6px; padding:6px 12px; font-size:12px; font-weight:600;">
                        VIEW DETAILS
                    </button>
                </td>
                <td style="background: #fff9e6; font-weight:bold;">'.$row['available_quantity'].'</td>
            </tr>';
        }
    } else {
        echo '<tr><td colspan="10" align="center">No audit records found.</td></tr>';
    }
    ?>
        </tbody>
    </table>
</div>

<!-- Ideal Professional Modal Structure -->
<div id="dataModal" class="custom-modal">
    <div class="modal-content-box">
        <div class="modal-header">
            <div>
                <h3>Consumed Item Records</h3>
                <p style="margin:0; font-size:13px; color:#64748b; font-weight:500;">
                    Last Audit Update: <span id="lastUpdatedSpan" style="color: #3b82f6; font-weight: 600;"></span>
                </p>
            </div>
            <span id="closeModal" class="close-btn">&times;</span>
        </div>
        <div class="modal-body" id="modalContent">
            <!-- AJAX Response Inject Here -->
        </div>
        <div style="padding: 15px 30px; background: #f8fafc; border-top: 1px solid #f1f5f9; text-align: right;">
            <button type="button" onclick="$('#dataModal').fadeOut(250);" style="padding: 8px 18px; background: white; border: 1px solid #e2e8f0; border-radius: 6px; cursor: pointer; font-weight: 600; color: #475569;">Close Window</button>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    $('.view-consumed-data').on('click', function() {
        var br_id = $(this).data('brid');
        var branch_item_id = $(this).data('itemid');
        var updated_at = $(this).data('updated');
        
        // Format the date for the header (e.g., 2026-05-05 to 05-May-2026)
        var dateObj = new Date(updated_at);
        var formattedDate = dateObj.toLocaleDateString('en-GB', {
            day: '2-digit', month: 'short', year: 'numeric'
        });

        // 1. Set the date in the modal header
        $('#lastUpdatedSpan').text(formattedDate);

        // 2. Open Modal and show loader
        $('#dataModal').fadeIn(300);
        $('#modalContent').html(`
            <div class="professional-loader">
                <div class="spinner-ring"></div>
                <p style="margin-top:20px; color:#64748b; font-size:14px; font-weight:500;">Analyzing consumption since ${formattedDate}...</p>
            </div>
        `);

        // 3. Fetch Consumption Data
        $.ajax({
            url: 'show_consumed_item_records.php',
            type: 'POST',
            data: {
                show_consumed_data: true,
                br_id: br_id,
                branch_item_id: branch_item_id,
                updated_at: updated_at
            },
            success: function(response) {
                // Ensure response is just the table/data
                $('#modalContent').hide().html(response).fadeIn(300);
            },
            error: function() {
                $('#modalContent').html('<div style="text-align:center; padding:40px; color:#ef4444; font-weight:600;">System Error: Could not connect to data source.</div>');
            }
        });
    });

    // Close Logic
    $('#closeModal').on('click', function() { $('#dataModal').fadeOut(250); });
    $(window).on('click', function(e) {
        if ($(e.target).hasClass('custom-modal')) {
            $('#dataModal').fadeOut(250);
        }
    });
});
</script>

</body>
</html>
<?php mysqli_close($con); ?>