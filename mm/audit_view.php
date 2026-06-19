<?php
include 'includes/connect.php';
include 'includes/head.php';
?>
<title>Audit Summary</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>

<body class="bg-light">
<div class="container mt-5">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Audit Progress Report</h2>
        <div class="btn-group">
            <a href="dashboard.php" class="btn btn-outline-secondary btn-sm">Goto Dashboard</a>
            <button onclick="loadSummary()" class="btn btn-primary btn-sm">Refresh Data</button>
        </div>
    </div>

    <div class="row g-3 mb-4 bg-white p-3 shadow-sm rounded">
        <div class="col-md-3">
            <label class="form-label">From Date</label>
            <input type="date" value="<?php echo date('Y-m-01'); ?>" id="date_from" class="form-control">
        </div>

        <div class="col-md-3">
            <label class="form-label">To Date</label>
            <input type="date" value="<?php echo date('Y-m-d'); ?>" id="date_to" class="form-control">
        </div>

        <div class="col-md-2">
            <label class="form-label">Branch</label>
            <select id="branch_filter" class="form-select">
                <option value="">All Branches</option>
                <?php
                $sql = "SELECT id, tag_name FROM branchs WHERE status = 1";
                $result = mysqli_query($con, $sql);

                while ($row = mysqli_fetch_assoc($result)) {
                    echo '<option value="'.$row['id'].'">'.$row['id'].' - '.$row['tag_name'].'</option>';
                }
                ?>
            </select>
        </div>

        <div class="col-md-2">
            <label class="form-label">Status</label>
            <select id="status_filter" class="form-select">
                <option value="">All</option>
                <option value="1">Start</option>
                <option value="2">Pending</option>
                <option value="3">Complete</option>
            </select>
        </div>

        <div class="col-md-2 d-flex align-items-end">
            <button onclick="loadSummary()" class="btn btn-primary w-100">Filter Results</button>
        </div>
    </div>

    <div class="table-responsive shadow-sm">
        <table class="table table-bordered bg-white" id="summaryTable">
            <thead class="table-dark">
                <tr>
                    <th>Audit Date</th>
                    <th>Audit ID</th>
                    <th>Branch ID</th>
                    <th>Total Items</th>
                    <th>Total Updated</th>
                    <th>Status</th>
                    <th>Audit By</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td colspan="7" class="text-center">Click Filter Results to load data</td>
                </tr>
            </tbody>
        </table>
    </div>

</div>

<script>
$(document).ready(function () {
    loadSummary();
});

function loadSummary() {
    let dateFrom = $('#date_from').val();
    let dateTo = $('#date_to').val();
    let branch = $('#branch_filter').val();
    let status = $('#status_filter').val();

    $.ajax({
        url: 'processs_audit_view.php',
        type: 'GET',
        dataType: 'json',
        data: {
            date_from: dateFrom,
            date_to: dateTo,
            branch: branch,
            status: status
        },
        success: function (data) {
            let html = '';

            if (!Array.isArray(data) || data.length === 0) {
                html = '<tr><td colspan="7" class="text-center">No records found matching filters</td></tr>';
            } else {
                data.forEach(row => {
                    let totalItems = parseInt(row.total_items) || 0;
                    let updatedItems = parseInt(row.total_updated_items) || 0;

                    let statusClass = updatedItems === totalItems && totalItems > 0 ? 'text-success' : 'text-danger';
                    let statusLabel = '';
                    let statusBadge = '';

                    if (updatedItems === 0) {
                        statusLabel = 'Start Audit';
                        statusBadge = 'bg-secondary';
                    } else if (updatedItems < totalItems) {
                        statusLabel = 'Pending';
                        statusBadge = 'bg-warning text-dark';
                    } else {
                        statusLabel = 'Complete';
                        statusBadge = 'bg-success';
                    }

                    html += `
                        <tr>
                            <td>${row.audit_date ?? ''}</td>
                            <td><strong>#${row.audit_id ?? ''}</strong></td>
                            <td>${row.branch_id ?? ''}</td>
                            <td>${row.total_items ?? 0}</td>
                            <td class="${statusClass} fw-bold">${row.total_updated_items ?? 0}</td>
                            <td><span class="badge ${statusBadge}">${statusLabel}</span></td>
                            <td>${row.audit_by ?? ''}</td>
                        </tr>
                    `;
                });
            }

            $('#summaryTable tbody').html(html);
        },
        error: function (xhr, status, error) {
            console.log("RAW RESPONSE:", xhr.responseText);
            console.log("AJAX STATUS:", status);
            console.log("ERROR:", error);

            $('#summaryTable tbody').html(
                '<tr><td colspan="7" class="text-center text-danger">Could not load data. Check console.</td></tr>'
            );
        }
    });
}
</script>

</body>
</html>