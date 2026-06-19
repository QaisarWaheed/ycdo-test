<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Sales Report</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f4f4f4; }
    </style>
</head>
<body>

    <h2>Monthly Sales Report</h2>
    <table id="reportTable">
        <thead>
            <tr>
                <!--<th>S.No</th>-->
                <th>Id</th>
                <th>Tag</th>
                <th>Name</th>
                <th>OPD</th>
                <th>Collection</th>
                <!--<th>Address</th>-->
            </tr>
        </thead>
        <tbody>
            </tbody>
    </table>
<div style="text-align: center; margin: 20px;">
    <button id="loadMoreBtn" style="padding: 10px 20px; cursor: pointer;">
        Load More Records
    </button>
</div>
    <script>
var currentPage = 1;
var limitPerPage = 5; // Must match your PHP limit
var isLoading = false; 

function loadData() {
    // Disable button to prevent double-clicks
    $("#loadMoreBtn").prop("disabled", true).text("Loading...");

    $.ajax({
        url: 'fetch_monthly_report.php',
        type: 'GET',
        // 2. Pass the CURRENT value of the variable
        data: { page: currentPage }, 
        dataType: 'json',
        success: function(data) {
            if (data.length > 0) {
                // Calculate the starting serial number for this batch
                // var serialStart = (currentPage - 1) * limitPerPage;
                var rows = '';
                $.each(data, function(key, value) {
                    // var serialNumber = serialStart + (index + 1);
                    rows += '<tr>';
                    // rows += '<td>' + serialNumber + '</td>';
                    rows += '<td>'+value.id+'</td>';
                    rows += '<td>'+value.name+'</td>';
                    rows += '<td>'+value.u_name+'</td>';
                    rows += '<td>'+value.opd+'</td>';
                    rows += '<td>'+value.collection+'</td>';
                    // rows += '<td>'+value.address+'</td>';
                    rows += '</tr>';
                });
                // Append instead of replace if you want a "Load More" style
                $('#reportTable tbody').append(rows);

                // 3. INCREMENT ONLY ON SUCCESS
                currentPage++; 

                // 4. Hide if we hit the end of the 10,000+ records
                if (data.length < 5) {
                    $("#loadMoreBtn").hide();
                } else {
                    $("#loadMoreBtn").prop("disabled", false).text("Load More");
                }
            } else {
                $("#loadMoreBtn").hide();
            }
        },
        error: function(xhr) {
            console.error("Error Status: " + xhr.status);
            $("#loadMoreBtn").prop("disabled", false).text("Load More");
        }
    });
}

$(document).ready(function() {
    // Initial load for Page 1
    loadData();

    // Click event
    $("#loadMoreBtn").click(function(e) {
        e.preventDefault();
        loadData(); // Call function, which now uses the updated currentPage
    });
});
    </script>
</body>
</html>