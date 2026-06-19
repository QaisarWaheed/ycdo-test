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
            #excelBtn, #pdfBtn, #loadMoreBtn, #mainActionBtn, #closeBtn, #filterBtn, .branch-filter-div, label  {
                display: none !important;
            }   
            table {
                width: 100%;
                border: 1px solid black;
                font-size: 10pt;
            }
            th, td {
                border: 1px solid black !important;
            }
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
            <label><strong>1st Date:</strong></label>
            <input type="date" id="start_date" value="<?php echo date('Y-m-d'); ?>" style="padding: 7px;">
            
            <label><strong>2nd Date:</strong></label>
            <input type="date" id="end_date" value="<?php echo date('Y-m-d'); ?>" style="padding: 7px;">
        </div>
        <button id="filterBtn" style="padding: 8px 15px; background: #28a745; color: white; border: none; cursor: pointer; border-radius: 4px;">Filter Report</button>
    </div>
    <table id="reportTable">
        <thead style="text-align: center;">
            <tr>
                <th rowspan="2">Id</th>
                <th rowspan="2">BRANCH</th>
                <th colspan="9" id="displayDate1">1ST DATE</th> <!-- Changed from colspan="8" to "9" -->
                <th colspan="9" id="displayDate2">2ND DATE</th> <!-- Changed from colspan="8" to "9" -->
            </tr>
            <tr>    
                <th>OPD</th>
                <th>DIAGNOSES</th> <!-- Added -->
                <th>TESTS</th>
                <th>CASH</th>
                <th>OPERATION</th>
                <th>GYNAE</th>
                <th>X-RAY</th>
                <th>ECG</th>
                <th>COLLECTION</th>
                
                <th>OPD</th>
                <th>DIAGNOSES</th> <!-- Added -->
                <th>TESTS</th>
                <th>CASH</th>
                <th>OPERATION</th>
                <th>GYNAE</th>
                <th>X-RAY</th>
                <th>ECG</th>
                <th>COLLECTION</th>
            </tr>
        </thead>
        <tbody>
        </tbody>
        <tfoot id="reportFooter" style="background-color: #eee; font-weight: bold;">
            <tr id="totalRow">
                <td colspan="2" style="text-align: right;">Grand Total:</td>
                
                <td id="totalOpd">0</td>
                <td id="totalDiagnoses">0</td> <!-- Added -->
                <td id="totalLab">0</td>
                <td id="totalLabAmount">0</td>
                <td id="totalOperation">0</td>
                <td id="totalGynae">0</td>
                <td id="totalXray">0</td>
                <td id="totalEcg">0</td>
                <td id="totalCollection">0</td>
        
                <td id="totalOpd2">0</td>
                <td id="totalDiagnoses2">0</td> <!-- Added -->
                <td id="totalLab2">0</td>
                <td id="totalLab2Amount">0</td>
                <td id="totalOperation2">0</td>
                <td id="totalGynae2">0</td>
                <td id="totalXray2">0</td>
                <td id="totalEcg2">0</td>
                <td id="totalCollection2">0</td>
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
var limitPerPage = 100; 
var isFinished = false; 

// Totals variables
var totalOpd = 0, totalDiagnoses = 0, totalLab = 0, totalOperation = 0, totalGynae = 0, totalXray = 0, totalCollection = 0;

function loadData(isNewFilter = false) {
    var date1 = $('#start_date').val();
    var date2 = $('#end_date').val();

    if(date1) $('#displayDate1').text(date1);
    if(date2) $('#displayDate2').text(date2);
    
    if (isNewFilter) {
        currentPage = 1;
        isFinished = false;
        totalOpd = 0; totalDiagnoses = 0; totalLab = 0; totalOperation = 0; totalGynae = 0; totalXray = 0; totalCollection = 0;
        $('#reportTable tbody').empty();
        $("#excelBtn, #pdfBtn, #closeBtn").hide();
        $('#mainActionBtn').text("Load More Records").css("background-color", "#28a745").prop("disabled", false);
    }

    $("#mainActionBtn").prop("disabled", true).text("Loading...");

    $.ajax({
        url: 'fetch_daily_branch_progress_comparison.php',
        type: 'GET',
        data: { 
            start_date: $('#start_date').val(), 
            end_date: $('#end_date').val() 
        },
        success: function(response) {
            var rows = '';
            // Reset totals every time data is loaded
            totalOpd = 0; totalDiagnoses = 0; totalLab = 0; totalLabAmount = 0; totalOperation = 0; totalGynae = 0; totalXray = 0; totalEcg = 0; totalCollection = 0;
            var t2Opd = 0, t2Diag = 0, t2Lab = 0, t2LabAmount = 0, t2Op = 0, t2Gyn = 0, t2Ecg = 0, t2Xray = 0, t2Coll = 0;

            $.each(response.data, function(k, v) {
                // ADD values to totals
                totalOpd += parseInt(v.d1_opd) || 0;
                totalDiagnoses += parseInt(v.d1_diagnoses) || 0; // Added
                totalLab += parseInt(v.d1_lab) || 0;
                totalLabAmount += parseInt(v.d1_lab_amount) || 0;
                totalOperation += parseInt(v.d1_op) || 0;
                totalGynae += parseInt(v.d1_gyn) || 0;
                totalXray += parseInt(v.d1_xray) || 0;
                totalEcg += parseInt(v.d1_ecg) || 0;
                totalCollection += parseFloat(v.d1_coll) || 0;

                // Second Date Totals
                t2Opd += parseInt(v.d2_opd) || 0;
                t2Diag += parseInt(v.d2_diagnoses) || 0; // Added
                t2Lab += parseInt(v.d2_lab) || 0;
                t2LabAmount += parseInt(v.d2_lab_amount) || 0;
                t2Op += parseInt(v.d2_op) || 0;
                t2Gyn += parseInt(v.d2_gyn) || 0;
                t2Xray += parseInt(v.d2_xray) || 0;
                t2Ecg += parseInt(v.d2_ecg) || 0;
                t2Coll += parseFloat(v.d2_coll) || 0;

                rows += `<tr>
                    <td>${v.branch_id}</td>
                    <td>${v.branch_name}</td>
                    <td>${v.d1_opd}</td>
                    <td>${v.d1_diagnoses}</td> <!-- Added -->
                    <td>${v.d1_lab}</td>
                    <td>${v.d1_lab_amount}</td>
                    <td>${v.d1_op}</td>
                    <td>${v.d1_gyn}</td>
                    <td>${v.d1_xray}</td>
                    <td>${v.d1_ecg}</td>
                    <td>${parseFloat(v.d1_coll).toLocaleString()}</td>
                    <td>${v.d2_opd}</td>
                    <td>${v.d2_diagnoses}</td> <!-- Added -->
                    <td>${v.d2_lab}</td>
                    <td>${v.d2_lab_amount}</td>
                    <td>${v.d2_op}</td>
                    <td>${v.d2_gyn}</td>
                    <td>${v.d2_xray}</td>
                    <td>${v.d2_ecg}</td>
                    <td>${parseFloat(v.d2_coll).toLocaleString()}</td>
                </tr>`;
            });

            $('#reportTable tbody').html(rows);

            // UPDATE the footer IDs with the calculated sums
            $('#totalOpd').text(totalOpd);
            $('#totalDiagnoses').text(totalDiagnoses); // Added
            $('#totalLab').text(totalLab);
            $('#totalLabAmount').text(totalLabAmount);
            $('#totalOperation').text(totalOperation);
            $('#totalGynae').text(totalGynae);
            $('#totalXray').text(totalXray);
            $('#totalEcg').text(totalEcg);
            $('#totalCollection').text(totalCollection.toLocaleString(undefined, {minimumFractionDigits: 2}));

            // Update Date 2 Totals
            $('#totalOpd2').text(t2Opd);
            $('#totalDiagnoses2').text(t2Diag); // Added
            $('#totalLab2').text(t2Lab);
            $('#totalLab2Amount').text(t2LabAmount);
            $('#totalOperation2').text(t2Op);
            $('#totalGynae2').text(t2Gyn);
            $('#totalXray2').text(t2Xray);
            $('#totalEcg2').text(t2Ecg);
            $('#totalCollection2').text(t2Coll.toLocaleString(undefined, {minimumFractionDigits: 2}));

            finishReport();
        }
    });
}

function finishReport() {
    isFinished = true;
    $("#mainActionBtn").text("Print Report").css("background-color", "#007bff");
    $("#excelBtn, #pdfBtn, #closeBtn").show();
}

$(document).ready(function() {
    $("#filterBtn").click(function() {
        loadData(true);
    });

    $("#excelBtn").click(function() {
        var wb = XLSX.utils.table_to_book(document.getElementById('reportTable'));
        XLSX.writeFile(wb, "Branch_Comparison_Report.xlsx");
    });

    $("#pdfBtn").click(function() {
        const { jsPDF } = window.jspdf;
        var doc = new jsPDF('p', 'pt', 'a4');
        doc.text("Branch Progress Comparison", 40, 30);
        doc.autoTable({ html: '#reportTable', startY: 50, theme: 'grid' });
        doc.save("Branch_Comparison.pdf");
    });

    $("#closeBtn").click(function() {
        if(confirm("Close report?")) window.close();
    });
});
</script>
</body>
</html>