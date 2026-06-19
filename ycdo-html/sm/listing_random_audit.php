<?php include 'includes/connect.php'; ?>
<?php include 'includes/head.php'; ?>
	<title>Dashboard - <?php echo $company_trademark; ?></title>
<style>
        .item-select { height: 300px !important; }
</style>
</head>

<body class="background_image">

<div class="row" style="margin: 0px;">
	<div class="col-md-12" style="text-align: center;background: lightgreen;">
		<label><h1>YCDO </h1></label>
	</div>
    <div class="col-md-12">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="bi bi-list-check me-2"></i>Audit History Summary</h5>
                <a href="random_audit_form.php" class="btn btn-sm btn-light">New Audit</a>
                <a href="dashboard.php" class="btn btn-sm btn-light">Dashboard</a>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Audit No.</th>
                                <th>Date</th>
                                <th class="text-center">Total Items</th>
                                <th># of Branch</th>
                                <th class="text-center">Total Diff Amount</th>
                                <th>Status</th>
                                <th class="text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $summary_query = "SELECT 
                                inventory_audit_no, 
                                inventory_audit_date,
                                COUNT(DISTINCT branch_id) as total_branchs,
                                COUNT(DISTINCT item_id) as total_items,
                                /* Calculate only Surplus (Difference > 0) */
                                SUM(CASE 
                                    WHEN inventory_audit_difference > 0 
                                    THEN (inventory_audit_item_poor * inventory_audit_difference) 
                                    ELSE 0 
                                END) as total_surplus_amount,
                                /* Calculate only Shortage (Difference < 0) */
                                SUM(CASE 
                                    WHEN inventory_audit_difference < 0 
                                    THEN (inventory_audit_item_poor * inventory_audit_difference) 
                                    ELSE 0 
                                END) as total_shortage_amount,
                                /* Overall Net Difference */
                                SUM(inventory_audit_item_poor * inventory_audit_difference) as net_difference
                                FROM inventory_audits 
                                GROUP BY inventory_audit_no 
                                ORDER BY inventory_audit_date DESC";
                            
                            $summary_result = mysqli_query($con, $summary_query);
    
                            while($report = mysqli_fetch_array($summary_result)):
                            ?>
                            <tr>
                                <td>#<?= $report['inventory_audit_no']; ?></td>
                                <td class="text-center"><?= date_format(date_create($report['inventory_audit_date']), "d-M-Y"); ?></td>
                                <td class="text-center"><?= $report['total_items']; ?> Items</td>
                                <td class="text-center"><?= $report['total_branchs']; ?></td>
                                
                                <!--<td class="text-end text-success fw-bold">-->
                                <!--    +<?= number_format($report['total_surplus_amount'], 2); ?>-->
                                <!--</td>-->
                            
                                <td class="text-end fw-bold">
                                    <?= number_format($report['net_difference'], 2); ?>
                                </td>
                                <td>
                                    <span class="badge bg-success">Completed</span>
                                </td>
                                <td class="text-center">
                                    <a href="view_random_audit.php?id=<?= $report['inventory_audit_no']; ?>" class="btn btn-outline-primary btn-sm">
                                        <i class="bi bi-eye"></i> View Details
                                    </a>
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

</body>
</html>