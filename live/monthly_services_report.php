<?php include 'config.php' ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Monthly Progress Report</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.25/jspdf.plugin.autotable.min.js"></script>
    <style>
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f4f4f4; }
        @media print {
            #excelBtn, #pdfBtn, #loadMoreBtn, #mainActionBtn, #closeBtn, #filterBtn, label {
                display: none !important;
            }   
            table { 
                width: 100%; 
                border: 1px solid black; 
                font-size: 10pt;
            }
            th, td { border: 1px solid black !important; }
            tfoot {
                display: table-footer-group;
                font-weight: bold;
                background-color: #f0f0f0 !important;
                -webkit-print-color-adjust: exact;
            }
        }
    </style>
</head>
<body>

    <h2>Monthly Progress Report</h2>
    <div style="margin: 20px 0; padding: 15px; background: #f9f9f9; border: 1px solid #ddd;">
        <div>
            <label><strong>Select Month:</strong></label>
            <input type="month" id="monthFilter" value="2026-03" style="padding: 7px;">
        </div>
        <label for="branchFilter"><strong>Selected Branch:</strong></label>
        <select id="branchFilter" style="padding: 8px; border-radius: 4px;">
            <?php
            $select = "SELECT * FROM branchs WHERE status = 1 ";
            $run = mysqli_query($con, $select);
            if(mysqli_num_rows($run) > 0) {
                while($row_branch = mysqli_fetch_array($run)) {
                    echo '<option value="'.$row_branch['id'].'">'.$row_branch['tag_name'].'</option>';
                }
            } else {
                echo '<option value="">ADD BRANCH RECORD</option>';
            } ?>
        </select>
        <button id="filterBtn" style="padding: 8px 15px; background: #28a745; color: white; border: none; cursor: pointer; border-radius: 4px;">Filter Report</button>
    </div>

    <table id="reportTable">
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
                <th>OPERATION</th>
                <th>DENTAL</th>
                <th>SKIN</th>
                <th>EYE</th>
                <th>ADMISSION</th>
                <th>%</th>
                <th>EMERGENCY</th>
                <th>ECG</th>
                <th>GYNAE TOKEN</th>
                <th>GYNAE ONLINE</th>
                <th>REF. FROM</th>
                <th>REF. TO</th>
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
                <td id="total_gynae_registrations">0</td>
                <td id="totalRefFrom">0</td>
                <td id="totalRefTo">0</td>
                <td id="totalCollection">0</td>
            </tr>
        </tfoot>
    </table>

    <div id="actionContainer" style="text-align: center; margin: 20px;">
        <button id="mainActionBtn" style="padding: 10px 20px; cursor: pointer; background-color: #28a745; color: white; border: none; border-radius: 4px;">
            Load More Records
        </button>
        <button id="excelBtn" style="padding: 10px 20px; cursor: pointer; background-color: #1d6f42; color: white; border: none; border-radius: 4px; display: none; margin-left: 10px;">
            Export Excel
        </button>
        <button id="pdfBtn" style="padding: 10px 20px; cursor: pointer; background-color: #e74c3c; color: white; border: none; border-radius: 4px; display: none; margin-left: 10px;">
            Save PDF
        </button>
        <button id="closeBtn" style="padding: 10px 20px; cursor: pointer; background-color: #6c757d; color: white; border: none; border-radius: 4px; display: none; margin-left: 10px;">
            Close
        </button>
    </div>

<script>
var currentPage = 1;
var limitPerPage = 10; 
var isFinished = false;

// Global Total Variables
var totalOpd = 0, totalCons = 0, totalLab = 0, totalunique_test_tokens = 0, totalUSG = 0, totalSVD = 0, 
    totalDNC = 0, totalOP = 0, totalDental = 0, totalSkin = 0, totalEye = 0, 
    totalAdm = 0, totalEmg = 0, totalEcg = 0, totalGyn = 0, totalCash = 0, 
    total_gynae_registrations = 0, totalRefFrom = 0, totalRefTo = 0;

function resetTotals() {
    totalOpd = 0; totalCons = 0; totalLab = 0; totalunique_test_tokens = 0; totalUSG = 0; 
    totalSVD = 0; totalDNC = 0; totalOP = 0; totalDental = 0; totalSkin = 0; totalEye = 0; 
    totalAdm = 0; totalEmg = 0; totalEcg = 0; totalGyn = 0; totalCash = 0; 
    total_gynae_registrations = 0; totalRefFrom = 0; totalRefTo = 0;
}

function updateFooterUI() {
    $('#totalOpd').text(totalOpd.toLocaleString());
    $('#totalCons').text(totalCons.toLocaleString());
    $('#totalunique_test_tokens').text(totalunique_test_tokens.toLocaleString());
    $('#totalLab').text(totalLab.toLocaleString());
    $('#totalUSG').text(totalUSG.toLocaleString());
    $('#totalSVD').text(totalSVD.toLocaleString());
    $('#totalDNC').text(totalDNC.toLocaleString());
    $('#totalOP').text(totalOP.toLocaleString());
    $('#totalDental').text(totalDental.toLocaleString());
    $('#totalSkin').text(totalSkin.toLocaleString());
    $('#totalEYE').text(totalEye.toLocaleString());
    $('#totalAdm').text(totalAdm.toLocaleString());
    $('#totalEmg').text(totalEmg.toLocaleString());
    $('#totalEcg').text(totalEcg.toLocaleString());
    $('#totalGyn').text(totalGyn.toLocaleString());
    $('#total_gynae_registrations').text(total_gynae_registrations.toLocaleString());
    $('#totalRefFrom').text(totalRefFrom.toLocaleString());
    $('#totalRefTo').text(totalRefTo.toLocaleString());
    $('#totalCollection').text(totalCash.toLocaleString());
}

function loadData(isNewFilter = false) {
    if (isNewFilter) {
        currentPage = 1;
        isFinished = false;
        resetTotals();
        $('#reportTable tbody').empty();
        updateFooterUI();
        $('#mainActionBtn').text("Load More Records").css("background-color", "#28a745");    
        $("#excelBtn, #pdfBtn, #closeBtn").hide();
    }

    $("#mainActionBtn").prop("disabled", true).text("Loading...");

    $.ajax({        
        url: 'fetch_monthly_services_report.php',
        type: 'GET',
        data: { 
            page: currentPage,
            branch_id: $('#branchFilter').val(),
            month: $('#monthFilter').val() 
        }, 
        dataType: 'json',
        success: function(data) {
            $("#mainActionBtn").prop("disabled", false);
            $("#filterBtn").prop("disabled", false).text("Filter Report").css({ "opacity": "1", "cursor": "pointer" });

            if (data && data.length > 0) {
                var rows = '';
                $.each(data, function(key, value) {
                    // Update running totals
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
                    total_gynae_registrations += parseInt(value.gynae_registrations || 0);
                    totalRefFrom += parseInt(value.referral_from || 0);
                    totalRefTo += parseInt(value.referral_to || 0);

                    rows += '<tr>';
                    rows += '<td>'+value.doctor_id+'</td>';
                    rows += '<td>'+value.branch_name+'</td>';
                    rows += '<td>'+value.doctor_name+'</td>';
                    rows += '<td>'+value.opd+'</td>';
                    rows += '<td>'+value.consultants+'</td>';
                    rows += '<td>'+value.test_tokens+'</td>';
                    rows += '<td>'+value.tests+'</td>';
                    rows += '<td>'+value.diagnostic_percentage+'</td>';
                    rows += '<td>'+value.usgs+'</td>';
                    rows += '<td>'+value.svds+'</td>';
                    rows += '<td>'+value.dncs+'</td>';
                    rows += '<td>'+value.procedures+'</td>';
                    rows += '<td>'+value.dentals+'</td>';
                    rows += '<td>'+value.skins+'</td>';
                    rows += '<td>'+value.eyes+'</td>';
                    rows += '<td>'+value.admissions+'</td>';
                    rows += '<td>'+value.admission_percentage+'</td>';
                    rows += '<td>'+value.emergency+'</td>';
                    rows += '<td>'+value.ecgs+'</td>';
                    rows += '<td>'+value.gyneas+'</td>';
                    rows += '<td>'+value.gynae_registrations+'</td>';
                    rows += '<td>'+value.referral_from+'</td>';
                    rows += '<td>'+value.referral_to+'</td>';
                    rows += '<td>'+value.collection+'</td>';
                    rows += '</tr>';
                });

                $('#reportTable tbody').append(rows);
                updateFooterUI();
                currentPage++; 

                if (data.length < limitPerPage) {
                    isFinished = true;
                    finishReport();
                } else {
                    $("#mainActionBtn").text("Load More Records");
                }
            } else {
                isFinished = true;
                finishReport();
                if(currentPage === 1) {
                    $('#reportTable tbody').html('<tr><td colspan="24" style="text-align:center;">No data found.</td></tr>');
                }
            }
        },
        error: function() {
            $("#mainActionBtn").prop("disabled", false).text("Error - Try Again");
            $("#filterBtn").prop("disabled", false).text("Filter Report").css({ "opacity": "1", "cursor": "pointer" });
            alert("Failed to fetch data.");
        }
    });
}

function finishReport() {
    isFinished = true;
    $("#mainActionBtn").text("Print Report").css("background-color", "#007bff");
    $("#excelBtn, #pdfBtn, #closeBtn").show();
}

$(document).ready(function() {
    $("#mainActionBtn").click(function(e) {
        e.preventDefault();
        if (isFinished) {
            window.print();
        } else {
            loadData(false);
        }
    });

    $("#filterBtn").click(function() {
        $(this).prop("disabled", true).text("Processing...").css({ "opacity": "0.7", "cursor": "not-allowed" });
        loadData(true);
    });

    $("#excelBtn").click(function() {
        var wb = XLSX.utils.table_to_book(document.getElementById('reportTable'), {sheet: "Monthly Report"});
        XLSX.writeFile(wb, "Monthly_Progress_Report.xlsx");
    });

    $("#pdfBtn").click(function() {
        const { jsPDF } = window.jspdf;
        var doc = new jsPDF('l', 'pt', 'a4');
        doc.text("Monthly Progress Report", 40, 30);
        doc.autoTable({ 
            html: '#reportTable',
            startY: 50,
            theme: 'grid',
            styles: { fontSize: 7 },
            headStyles: { fillColor: [244, 244, 244], textColor: 0 }
        });
        doc.save("Monthly_Progress_Report.pdf");
    });

    $("#closeBtn").click(function() {
        if(confirm("Are you sure?")) window.close();
    });
});
</script>
</body>
</html>