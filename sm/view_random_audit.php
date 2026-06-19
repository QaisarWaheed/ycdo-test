<?php 
include 'includes/config.php'; 
include 'includes/head.php'; 

$audit_no = isset($_GET['id']) ? mysqli_real_escape_string($con, $_GET['id']) : 0;
$header_query = "SELECT `inventory_audit_detail_id`, `inventory_audit_detail_created`, `inventory_audit_detail_status`, users.u_name FROM `inventory_audit_details` INNER JOIN users ON inventory_audit_details.user_id = users.id WHERE inventory_audit_detail_id = $audit_no ";
$header_res = mysqli_query($con, $header_query);
$header = mysqli_fetch_assoc($header_res);
?>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">

<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<title>View Audit Details - #<?php echo $audit_no; ?></title>
</head>

<body class="background_image">
<div class="container-fluid mt-4">
<a class="btn btn-sm btn-success" href = "dashboard.php">Dashboard</a>
    <div class="row">
        <div class="col-md-12">
        <?php 
        $s = 0;
        $audit_no = $_GET['id'];
        
        $query = "SELECT item_id, reg_item_id, items.name AS item_name, users.u_name, branchs.tag_name, inventory_audit_difference, inventory_audit_item_poor,inventory_audit_computer_quantity ,inventory_audit_manual_quantity FROM inventory_audits INNER JOIN items ON inventory_audits.item_id = items.id LEFT JOIN branchs ON inventory_audits.branch_id = branchs.id LEFT JOIN users ON inventory_audits.inventory_audit_updated_by = users.id WHERE inventory_audit_no = '$audit_no' AND inventory_audit_status = 2 ";
        $result = mysqli_query($con, $query);
        ?>
        
        <div class="card">
            <div class="card-header bg-primary text-white">
                <div class = "row">
                    <div class = "col">
                        Audit #<?php echo $audit_no; ?>
                    </div>
                    <div class = "col">
                        Created By: <?php echo $header['u_name']; ?>
                    </div>
                    <div class = "col">
                        Created At: <?php echo $header['inventory_audit_detail_created']; ?>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <table id="auditDetailsTable" class="table table-bordered table-hover">
                    <thead>
                        <tr>
                            <th>Ser #</th>
                            <th>Id</th>
                            <th>Branch</th>
                            <th>Item</th>
                            <th class="text-center">Computer</th>
                            <th class="text-center">Manual</th>
                            <th class="text-center">Difference</th>
                            <th class="text-end">Rate</th>
                            <th class="text-end">Total Diff</th>
                            <th class="">Staff</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $total_qty_diff = 0;
                        $grand_total_amount = 0;
                        
                        while($row = mysqli_fetch_array($result)): 
                            $s++; 
                            $current_diff = $row['inventory_audit_difference'];
                            $current_rate = $row['inventory_audit_item_poor'];
                            $row_total = $current_diff * $current_rate;
                
                            // Add to Grand Totals
                            $total_qty_diff += $current_diff;
                            $grand_total_amount += $row_total;
                        ?>
                        <tr>
                            <td class="index_col"></td>
                            <td><?= $row['reg_item_id']; ?></td>
                            <td><?= $row['tag_name']; ?></td>
                            <td><?= $row['item_name']; ?></td>
                            <td class="text-center"><?= $row['inventory_audit_computer_quantity']; ?></td>
                            <td class="text-center"><?= $row['inventory_audit_manual_quantity']; ?></td>
                            <td class="text-center"><?= $current_diff; ?></td>
                            <td class="text-end"><?= number_format($current_rate, 2); ?></td>
                            <td class="text-end <?= $current_diff < 0 ? 'text-danger' : 'text-success' ?>">
                                <?= number_format($row_total, 2); ?>
                            </td>
                            <td><?= $row['u_name']; ?></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                    <tfoot class="table-dark">
                        <tr>
                            <th colspan="6" class="text-end">Filtered Total:</th>
                            <th class="text-center" id="footer-qty-diff">0</th>
                            <th></th> 
                            <th class="text-end">
                                <span class="text-success" id="pos-sum">0.00</span><br>
                                <span class="text-danger" id="neg-sum">0.00</span>
                            </th>
                            <th class="text-end" id="footer-grand-total">0.00</th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
        </div>
    </div>
</div>

<style>
    @media print {
        .col-md-3, .btn, .card-footer { display: none !important; }
        .col-md-9 { width: 100% !important; }
        .card { border: none !important; shadow: none !important; }
    }
</style>
<script>
$(document).ready(function() {
    // Initialize DataTable
    var t = $('#auditDetailsTable').DataTable({
        "pageLength": -1,
        "paging": false,
        "searching": true,
        "ordering": true,
        "info": true,
        "autoWidth": false,
        // Default sort by the second column (ID) so Ser # can stay sequential
        "order": [[1, 'asc']], 
        "columnDefs": [{
            "searchable": false,
            "orderable": false,
            "targets": 0 // Target the first column (Ser #)
        }],
        "language": {
            "search": "Quick Filter:",
        },
        "footerCallback": function (row, data, start, end, display) {
            var api = this.api();
            
            var intVal = function (i) {
                if (typeof i === 'number') return i;
                var clean = i.replace(/<.*?>/g, '').replace(/[^\d.-]/g, ''); 
                return parseFloat(clean) || 0;
            };
        
            // 1. Total Quantity Difference (Column 6)
            var qtyTotal = api.column(6, { search: 'applied' }).data()
                .reduce(function (a, b) { return intVal(a) + intVal(b); }, 0);
        
            // 2. Separate Positive and Negative sums for Total Diff (Column 8)
            var posTotal = 0;
            var negTotal = 0;
        
            // Get all data from column 8 that is currently filtered
            api.column(8, { search: 'applied' }).data().each(function (val, index) {
                var num = intVal(val);
                if (num > 0) {
                    posTotal += num;
                } else if (num < 0) {
                    negTotal += num;
                }
            });
        
            // Update Footer for Quantity
            $(api.column(6).footer()).html(qtyTotal);
        
            // Update Footer for Positive/Negative Amounts
            var formatConfig = { minimumFractionDigits: 2, maximumFractionDigits: 2 };
            
            $('#pos-sum').html('(+) ' + posTotal.toLocaleString('en-US', formatConfig));
            $('#neg-sum').html('(-) ' + Math.abs(negTotal).toLocaleString('en-US', formatConfig));
        }        
        //     // Calculate Difference Total
        //     var qtyTotal = api.column(6, { search: 'applied' }).data()
        //         .reduce(function (a, b) { return intVal(a) + intVal(b); }, 0);

        //     // Calculate Grand Total Amount
        //     var amountTotal = api.column(8, { search: 'applied' }).data()
        //         .reduce(function (a, b) { return intVal(a) + intVal(b); }, 0);

        //     $(api.column(6).footer()).html(qtyTotal);
        //     $(api.column(8).footer()).html(
        //         amountTotal.toLocaleString('en-US', {
        //             minimumFractionDigits: 2, 
        //             maximumFractionDigits: 2
        //         })
        //     );
        // }
    });

    // This function forces the Ser # to recalculate whenever you search or sort
    t.on('draw.dt', function () {
        var PageInfo = $('#auditDetailsTable').DataTable().page.info();
        t.column(0, { page: 'current' }).nodes().each(function (cell, i) {
            cell.innerHTML = i + 1 + PageInfo.start;
        });
    }).draw();
});
</script>
</body>
</html>