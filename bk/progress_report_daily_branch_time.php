<?php include 'includes/connect.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Monthly Progress Report - Specific Hours</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.25/jspdf.plugin.autotable.min.js"></script>
    <style>
        body { font-family: sans-serif; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; font-size: 13px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f4f4f4; position: sticky; top: 0; }
        @media print {
            #excelBtn, #pdfBtn, #loadMoreBtn, #mainActionBtn, #closeBtn, #filterBtn, .branch-filter-div, label, input, select {
                display: none !important;
            }   
            table { width: 100%; border: 1px solid black; font-size: 10pt; }    
            th, td { border: 1px solid black !important; }
            tfoot { display: table-footer-group; font-weight: bold; background-color: #f0f0f0 !important; }
        }
    </style>
</head>
<body>

    <h2>Monthly Progress Report - Specific Hours</h2>
    <div style="margin: 20px 0; padding: 15px; background: #f9f9f9; border: 1px solid #ddd;">
        <label><strong>From Date:</strong></label>
        <input type="date" id="fromDate" value="2026-05-01" style="padding: 7px;">
        
        <label><strong>To Date:</strong></label>
        <input type="date" id="toDate" value="2026-05-05" style="padding: 7px;">
        
        <label><strong>Start:</strong></label>
        <input type="time" id="startTime" value="00:00" style="padding: 7px;">

        <label><strong>End:</strong></label>
        <input type="time" id="endTime" value="06:00" style="padding: 7px;">

        <label for="branchFilter"><strong>Branch:</strong></label>
        <select id="branchFilter" style="padding: 8px;">
            <?php
            $select = "SELECT * FROM branchs WHERE status = 1 ";
            $run = mysqli_query($con, $select);
            while($row_branch = mysqli_fetch_array($run)) {
                echo '<option value="'.$row_branch['id'].'">'.$row_branch['tag_name'].' - '.$row_branch['address'].'</option>';
            }
            ?>
        </select>
    
        <button id="filterBtn" style="padding: 8px 15px; background: #28a745; color: white; border: none; cursor: pointer; border-radius: 4px;">Filter Report</button>
    </div>

    <table id="reportTable">
        <caption id="tableCaption" style="font-size: 1.2em; font-weight: bold; margin-bottom: 10px; color: #333;">
            Select filters and click Filter Report
        </caption>        
        <thead>
            <tr>
                <th>Id</th>
                <th>Tag</th>
                <th>Name</th>
                <th>OPD</th>
                <th>CONS</th>
                <th>Dia.Pt</th>
                <th>LAB</th>
                <th>%</th>
                <th>USG</th>
                <th>SVD</th>
                <th>D&C</th>
                <th>OP</th>
                <th>DENTAL</th>
                <th>SKIN</th>
                <th>EYE</th>
                <th>ADM</th>
                <th>%</th>
                <th>EMG</th>
                <th>ECG</th>
                <th>GYN</th>
                <th>COLLECTION</th>
            </tr>
        </thead>
        <tbody></tbody>
        <tfoot id="reportFooter" style="background-color: #eee; font-weight: bold;">
            <tr id="totalRow">
                <td colspan="3" style="text-align: right;">Grand Total:</td>
                <td id="totalOpd">0</td>
                <td id="totalCons">0</td>
                <td id="totalunique_test_tokens">0</td>
                <td id="totalLab">0</td>
                <td></td>
                <td id="totalUSG">0</td>
                <td id="totalSVD">0</td>
                <td id="totalDNC">0</td>
                <td id="totalOP">0</td>
                <td id="totalDental">0</td>
                <td id="totalSkin">0</td>
                <td id="totalEYE">0</td>
                <td id="totalAdm">0</td>
                <td></td>
                <td id="totalEmg">0</td>
                <td id="totalEcg">0</td>
                <td id="totalGyn">0</td>
                <td id="totalCollection">0</td>
            </tr>
        </tfoot>
    </table>

    <div id="actionContainer" style="text-align: center; margin: 20px;">
        <button id="mainActionBtn" style="padding: 10px 20px; cursor: pointer; background-color: #28a745; color: white; border: none; border-radius: 4px;">
            Load More Records
        </button>
        <button id="excelBtn" style="padding: 10px 20px; cursor: pointer; background-color: #1d6f42; color: white; border: none; border-radius: 4px; display: none;">Export Excel</button>
        <button id="pdfBtn" style="padding: 10px 20px; cursor: pointer; background-color: #e74c3c; color: white; border: none; border-radius: 4px; display: none;">Save PDF</button>
        <button id="closeBtn" onclick="window.close()" style="padding: 10px 20px; cursor: pointer; background-color: #6c757d; color: white; border: none; border-radius: 4px; display: none;">Close</button>
    </div>

<script>
var currentPage = 1;
var limitPerPage = 25;
var isFinished = false;
var totalOpd = 0, totalCons = 0, totalLab = 0, totalunique_test_tokens = 0, totalUSG = 0, totalSVD = 0, 
    totalDNC = 0, totalOP = 0, totalDental = 0, totalSkin = 0, totalEye = 0, 
    totalAdm = 0, totalEmg = 0, totalEcg = 0, totalGyn = 0, totalCash = 0;

$(document).ready(function() {
    // 1. CLICK EVENT FOR FILTER
    $('#filterBtn').on('click', function() {
        loadData(true);
    });

    // 2. CLICK EVENT FOR LOAD MORE
    $('#mainActionBtn').on('click', function() {
        if (!isFinished) {
            loadData(false);
        }
    });
});

function updateFooter() {
    $('#totalOpd').text(totalOpd);
    $('#totalCons').text(totalCons);
    $('#totalunique_test_tokens').text(totalunique_test_tokens);
    $('#totalLab').text(totalLab);
    $('#totalUSG').text(totalUSG);
    $('#totalSVD').text(totalSVD);
    $('#totalDNC').text(totalDNC);
    $('#totalOP').text(totalOP);
    $('#totalDental').text(totalDental);
    $('#totalSkin').text(totalSkin);
    $('#totalEYE').text(totalEye);
    $('#totalAdm').text(totalAdm);
    $('#totalEmg').text(totalEmg);
    $('#totalEcg').text(totalEcg);
    $('#totalGyn').text(totalGyn);
    $('#totalCollection').text(totalCash.toLocaleString());
}

function finishReport() {
    isFinished = true;
    $("#mainActionBtn").hide();
    $("#excelBtn, #pdfBtn, #closeBtn").show();
}

function loadData(isNewFilter = false) {
    if (isNewFilter) {
        currentPage = 1;
        isFinished = false;
        totalOpd = 0; totalCons = 0; totalunique_test_tokens = 0; totalLab = 0;
        totalUSG = 0; totalSVD = 0; totalDNC = 0; totalOP = 0; totalDental = 0;
        totalSkin = 0; totalEye = 0; totalAdm = 0; totalEmg = 0; totalEcg = 0;
        totalGyn = 0; totalCash = 0;
        
        var branchName = $('#branchFilter option:selected').text();
        $('#tableCaption').text("Progress Report: " + branchName + " (" + $('#fromDate').val() + " to " + $('#toDate').val() + ")");
        $('#reportTable tbody').empty();
        $('#mainActionBtn').show().text("Load More Records").prop("disabled", false);
        $("#excelBtn, #pdfBtn, #closeBtn").hide();
    }

    $("#mainActionBtn").prop("disabled", true).text("Loading...");

    $.ajax({
        url: 'process_progress_report_daily_branch_time.php',
        type: 'GET',
        data: { 
            page: currentPage,
            branch_id: $('#branchFilter').val(),
            from_date: $('#fromDate').val(),
            to_date: $('#toDate').val(),
            start_time: $('#startTime').val(),
            end_time: $('#endTime').val()
        }, 
        dataType: 'json',
        success: function(data) {
            $("#mainActionBtn").prop("disabled", false);

            if (data && data.length > 0) {
                var rows = '';
                $.each(data, function(key, value) {
                    totalOpd += parseInt(value.opd || 0);
                    totalCons += parseInt(value.consultants || 0);
                    totalunique_test_tokens += parseInt(value.test_tokens || 0);
                    totalLab += parseInt(value.tests || 0);
                    totalUSG += parseInt(value.usgs || 0);
                    totalSVD += parseInt(value.svds || 0);
                    totalDNC += parseInt(value.dncs || 0);
                    totalOP += parseInt(value.procedures || 0);
                    totalDental += parseInt(value.dentals || 0);
                    totalSkin += parseInt(value.skins || 0);
                    totalEye += parseInt(value.eyes || 0);
                    totalAdm += parseInt(value.admissions || 0);
                    totalEmg += parseInt(value.emergency || 0);
                    totalEcg += parseInt(value.ecgs || 0);
                    totalGyn += parseInt(value.gyneas || 0);
                    totalCash += parseFloat(value.collection || 0);

                    rows += '<tr><td>'+value.doctor_id+'</td><td>'+value.branch_name+'</td><td>'+value.doctor_name+'</td><td>'+value.opd+'</td><td>'+value.consultants+'</td><td>'+value.test_tokens+'</td><td>'+value.tests+'</td><td>'+value.diagnostic_percentage+'%</td><td>'+value.usgs+'</td><td>'+value.svds+'</td><td>'+value.dncs+'</td><td>'+value.procedures+'</td><td>'+value.dentals+'</td><td>'+value.skins+'</td><td>'+value.eyes+'</td><td>'+value.admissions+'</td><td>'+value.admission_percentage+'%</td><td>'+value.emergency+'</td><td>'+value.ecgs+'</td><td>'+value.gyneas+'</td><td>'+parseFloat(value.collection).toLocaleString()+'</td></tr>';
                });

                $('#reportTable tbody').append(rows);
                updateFooter();
                currentPage++; 

                if (data.length < limitPerPage) {
                    finishReport();
                } else {
                    $("#mainActionBtn").text("Load More Records");
                }
            } else {
                finishReport();
                if(currentPage === 1) {
                    $('#reportTable tbody').html('<tr><td colspan="21" style="text-align:center;">No data found.</td></tr>');
                }
            }
        },
        error: function(xhr) {
            console.log(xhr.responseText);
            $("#mainActionBtn").prop("disabled", false).text("Error - Check Console");
        }
    });
}
</script>
</body>
</html>