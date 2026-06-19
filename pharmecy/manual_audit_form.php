<?php 
include 'includes/connect.php'; 
include 'includes/head.php'; 
?>
	<title>Manual Audir Form - <?php echo $company_trademark; ?></title>
</head>

<body class="background_image" oncontextmenu="return false;">
<div id="loadingSpinner" style="display: none;">
    <div class = "container">
        <div class = "row p-5 g-5">
            <div class = "col text-center">
                <div aria-busy="true" aria-describedby="progress-bar">
                    <h2>LOADING...</h2>
                    <p>Please Wait Untill Processing Completed.</p>
                    <p>Data Processing...</p>
                </div>
                <progress id="progress-bar" aria-label="Content loading…"></progress>    
                
            </div>
        </div>        
    </div>
</div>
<div class="row" style="margin: 0px;" id = "submitBody">
	<div class="col-md-12" style="text-align: center;background: lightgreen;">
		<label><h1><?php echo $company_name?> </h1></label>
	</div>
	<div class="col-md-3 background_whitesmoke" style = "text-transform: uppercase;">
		<?php include 'left_navigation.php'; ?>
	</div>
    <div class="col-md-9">
        <div class="card shadow">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">Manual Audit</h5>
            </div>
            <div class="card-body">
                    <table class="table table-bordered align-middle">
                        <thead class="table-light text-center">
                            <tr>
                                <th>#</th>
                                <th>Item Name</th>
                                <th>Category</th>
                                <th>Actual (Staff)</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $s = 0;
                            $select = "SELECT inventory_audits.inventory_audit_id, items.name, items.poor, categories.name AS category_name, item_register_to_branches.quantity FROM inventory_audits INNER JOIN items ON inventory_audits.item_id = items.id INNER JOIN categories ON items.category_id = categories.id INNER JOIN item_register_to_branches ON inventory_audits.reg_item_id = item_register_to_branches.id WHERE inventory_audits.branch_id = $branch_id AND inventory_audits.inventory_audit_status = 1; ";
                            $result = mysqli_query($con, $select);
                    
                            while($row = mysqli_fetch_array($result)):
                                $s++; 
                            ?>
                            <tr class="audit-row" id="row-<?= $row['inventory_audit_id']; ?>">
                                <td class="text-center"><?= $s; ?></td>
                                <td>
                                    <?= $row['name']; ?>
                                </td>
                                <td><?= $row['category_name']; ?></td>
                                <td>
                                    <input type="number" class="form-control border-primary manual-qty text-center" placeholder="0">
                                </td>
                                <td class="text-center">
                                    
                                    <input type="hidden" class="audit-id" value="<?= $row['inventory_audit_id']; ?>">
                                    <input type="hidden" class="computer-qty" value="<?= $row['quantity']; ?>">
                                    <input type="hidden" class="qty-diff" value="0">
                                    <input type="hidden" class="diff-amount" value="0">
                                    <input type="hidden" class="unit-price" value="<?= $row['poor']; ?>">
                                    <button type="button" class="btn btn-primary btn-sm btn-update">
                                        <i class="bi bi-save"></i> Update
                                    </button>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.querySelectorAll('.btn-update').forEach(button => {
    button.addEventListener('click', function() {
        const row = this.closest('tr');
        
        // Get values for calculation
        const auditId = row.querySelector('.audit-id').value;
        const manualQty = row.querySelector('.manual-qty').value;
        const computerQty = row.querySelector('.computer-qty').value;
        const unitPrice = row.querySelector('.unit-price').value;
        
        if (manualQty === "") {
            alert("Please enter a quantity first.");
            return;
        }

        // Calculate Difference: Manual - Computer
        const diff = parseInt(manualQty) - parseInt(computerQty);

        // UI Feedback: Disable button and show loading
        const originalBtnText = this.innerHTML;
        this.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Saving...';
        this.disabled = true;

        // Send data to PHP
        fetch('update_single_row.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `audit_id=${auditId}&manual_qty=${manualQty}&computer_qty=${computerQty}&unit_price=${unitPrice}&diff=${diff}`
        })
        .then(response => response.text())
        .then(data => {
            if (data.trim() === "success") {
                // SUCCESS LOGIC:
                this.classList.replace('btn-primary', 'btn-success');
                this.innerHTML = "Success!";
                
                // 1. Add a fade-out effect using Bootstrap class or CSS
                row.style.transition = "all 0.5s ease";
                row.style.opacity = "0";
                row.style.background = "#d1e7dd"; // Light green success color

                // 2. Remove from HTML after the animation finishes (500ms)
                setTimeout(() => {
                    row.remove();
                    
                    // Optional: Check if table is empty to show a "All Done" message
                    const remainingRows = document.querySelectorAll('.audit-row').length;
                    if (remainingRows === 0) {
                        document.querySelector('tbody').innerHTML = '<tr><td colspan="5" class="text-center text-success"><h5>All items audited successfully!</h5></td></tr>';
                    }
                }, 500);

            } else {
                alert("Error from server: " + data);
                this.disabled = false;
                this.innerHTML = "Retry";
                this.classList.replace('btn-primary', 'btn-danger');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert("Connection error. Please try again.");
            this.disabled = false;
            this.innerHTML = "Retry";
        });
    });
});
</script>

</body>
</html>
<?php mysqli_close($con); ?>