<?php include 'config.php' ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Monthly Progress Report</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
    <style>
        table { width: 100%; border-collapse: collapse; margin-top: 20px; font-size: 11px; }
        th, td { border: 1px solid #ddd; padding: 4px; text-align: center; }
        th { background-color: #f4f4f4; }
        .category-header { background-color: #e9ecef; font-weight: bold; }
        .today-col { background-color: #fff9db; } /* Subtle highlight for today */
        @media print {
            #excelBtn, #pdfBtn, #mainActionBtn, #closeBtn, #filterBtn, .filter-section { display: none !important; }
            table { width: 100%; font-size: 9pt; }
            th, td { border: 1px solid black !important; }
        }
    </style>
</head>
<body>

    <h2>Monthly Progress Report (Today vs Previous)</h2>
    
    <div class="filter-section" style="margin: 20px 0; padding: 15px; background: #f9f9f9; border: 1px solid #ddd;">
        <label><strong>Select Date:</strong></label>
        <input type="date" id="dateFilter" value="<?php echo date('Y-m-d'); ?>" style="padding: 5px;">
        
        <label style="margin-left:15px;"><strong>Branch:</strong></label>
        <select id="branchFilter" style="padding: 5px;">
            <option value = "-1">ALL - ALL BRANCHS RECORD</option>
            <?php
            $run = mysqli_query($con, "SELECT id, tag_name, address FROM branchs WHERE status = 1");
            while($row = mysqli_fetch_assoc($run)) {
                echo '<option value="'.$row['id'].'">'.$row['tag_name'].' - '.$row['address'].'</option>';
            }
            ?>
        </select>
        <button id="filterBtn" style="padding: 6px 15px; background: #28a745; color: white; border: none; cursor: pointer;">Filter Report</button>
    </div>

    <table id="reportTable">
        <thead>
            <tr>
                <th rowspan="2">Ser</th>
                <th rowspan="2">Branch</th>
                <th rowspan="2">Doctor Name</th>
                <th colspan="3" class="category-header">OPD</th>
                <th colspan="3" class="category-header">LAB</th>
                <th colspan="3" class="category-header">USG</th>
                <th colspan="3" class="category-header">Procedures</th>
                <th colspan="3" class="category-header">Admissions</th>
                <th colspan="3" class="category-header">ECG</th>
                <th colspan="3" class="category-header">Gynae</th>
            </tr>
            <tr>
                <th>Tdy</th><th>Prv</th><th>Tot</th> <th>Tdy</th><th>Prv</th><th>Tot</th> <th>Tdy</th><th>Prv</th><th>Tot</th> <th>Tdy</th><th>Prv</th><th>Tot</th> <th>Tdy</th><th>Prv</th><th>Tot</th> <th>Tdy</th><th>Prv</th><th>Tot</th> <th>Tdy</th><th>Prv</th><th>Tot</th> </tr>
        </thead>
        <tbody>
            </tbody>
        <tfoot style="background: #eee; font-weight: bold;">
            <tr id="footerTotals">
                <td colspan="3">Grand Total</td>
                <td id="t_opd_t">0</td><td id="t_opd_p">0</td><td id="t_opd_all">0</td>
                <td id="t_lab_t">0</td><td id="t_lab_p">0</td><td id="t_lab_all">0</td>
                <td id="t_usg_t">0</td><td id="t_usg_p">0</td><td id="t_usg_all">0</td>
                <td id="t_prc_t">0</td><td id="t_prc_p">0</td><td id="t_prc_all">0</td>
                <td id="t_adm_t">0</td><td id="t_adm_p">0</td><td id="t_adm_all">0</td>
                <td id="t_ecg_t">0</td><td id="t_ecg_p">0</td><td id="t_ecg_all">0</td>
                <td id="t_gyn_t">0</td><td id="t_gyn_p">0</td><td id="t_gyn_all">0</td>
            </tr>
        </tfoot>
    </table>

    <div style="text-align: center; margin: 20px;">
        <button id="mainActionBtn" style="padding: 10px 20px; background: #28a745; color: white; border: none; border-radius: 4px; cursor: pointer;">Load More</button>
        <button id="excelBtn" style="padding: 10px 20px; background: #1d6f42; color: white; border: none; border-radius: 4px; display:none;">Export Excel</button>
        <button id="closeBtn" style="padding: 10px 20px; background: #6c757d; color: white; border: none; border-radius: 4px; display:none;">Close</button>
    </div>

<script>
var currentPage = 1;
var isFinished = false;

// Global Totals object
var totals = {
    opd_t:0, opd_p:0, opd_all:0,
    lab_t:0, lab_p:0, lab_all:0,
    usg_t:0, usg_p:0, usg_all:0,
    prc_t:0, prc_p:0, prc_all:0,
    adm_t:0, adm_p:0, adm_all:0,
    ecg_t:0, ecg_p:0, ecg_all:0,
    gyn_t:0, gyn_p:0, gyn_all:0
};

function loadData(isNewFilter = false) {
    if (isNewFilter) {
        currentPage = 1; isFinished = false;
        $('#reportTable tbody').empty();
        Object.keys(totals).forEach(key => totals[key] = 0);
    }

    $.ajax({
        url: 'fetch_monthly_services_report_doctors.php',
        type: 'GET',
        data: { 
            page: currentPage, 
            branch_id: $('#branchFilter').val(), 
            selected_date: $('#dateFilter').val() // Sending the user-selected date
        },
        dataType: 'json',
        success: function(data) {
            $("#mainActionBtn").prop("disabled", false);
            
            if (data && data.length > 0) {
                var rows = '';
                $.each(data, function(i, v) {
                    // Update Totals
                    totals.opd_t += parseInt(v.opd_today); totals.opd_p += parseInt(v.opd_prev); totals.opd_all += parseInt(v.opd_total);
                    totals.lab_t += parseInt(v.lab_today); totals.lab_p += parseInt(v.lab_prev); totals.lab_all += parseInt(v.lab_total);
                    totals.usg_t += parseInt(v.usg_today); totals.usg_p += parseInt(v.usg_prev); totals.usg_all += parseInt(v.usg_total);
                    totals.prc_t += parseInt(v.proc_today); totals.prc_p += parseInt(v.proc_prev); totals.prc_all += parseInt(v.proc_total);
                    totals.adm_t += parseInt(v.adm_today); totals.adm_p += parseInt(v.adm_prev); totals.adm_all += parseInt(v.adm_total);
                    totals.ecg_t += parseInt(v.ecg_today); totals.ecg_p += parseInt(v.ecg_prev); totals.ecg_all += parseInt(v.ecg_total);
                    totals.gyn_t += parseInt(v.gyn_today); totals.gyn_p += parseInt(v.gyn_prev); totals.gyn_all += parseInt(v.gyn_total);

                    rows += `<tr>
                        <td>${v.ser}</td>
                        <td>${v.branch_name}</td>
                        <td style="text-align:left">${v.doctor_name}</td>
                        <td class="today-col">${v.opd_today}</td><td>${v.opd_prev}</td><td style="font-weight:bold">${v.opd_total}</td>
                        <td class="today-col">${v.lab_today}</td><td>${v.lab_prev}</td><td style="font-weight:bold">${v.lab_total}</td>
                        <td class="today-col">${v.usg_today}</td><td>${v.usg_prev}</td><td style="font-weight:bold">${v.usg_total}</td>
                        <td class="today-col">${v.proc_today}</td><td>${v.proc_prev}</td><td style="font-weight:bold">${v.proc_total}</td>
                        <td class="today-col">${v.adm_today}</td><td>${v.adm_prev}</td><td style="font-weight:bold">${v.adm_total}</td>
                        <td class="today-col">${v.ecg_today}</td><td>${v.ecg_prev}</td><td style="font-weight:bold">${v.ecg_total}</td>
                        <td class="today-col">${v.gyn_today}</td><td>${v.gyn_prev}</td><td style="font-weight:bold">${v.gyn_total}</td>
                    </tr>`;
                });

                $('#reportTable tbody').append(rows);
                updateFooterUI();
                currentPage++;

                if (data.length < 50) { finishReport(); } 
                else { $("#mainActionBtn").text("Load More"); }
            } else {
                finishReport();
                if(currentPage === 1) $('#reportTable tbody').html('<tr><td colspan="24">No records found</td></tr>');
            }
        }
    });
}

function updateFooterUI() {
    $('#t_opd_t').text(totals.opd_t); $('#t_opd_p').text(totals.opd_p); $('#t_opd_all').text(totals.opd_all);
    $('#t_lab_t').text(totals.lab_t); $('#t_lab_p').text(totals.lab_p); $('#t_lab_all').text(totals.lab_all);
    $('#t_usg_t').text(totals.usg_t); $('#t_usg_p').text(totals.usg_p); $('#t_usg_all').text(totals.usg_all);
    $('#t_prc_t').text(totals.prc_t); $('#t_prc_p').text(totals.prc_p); $('#t_prc_all').text(totals.prc_all);
    $('#t_adm_t').text(totals.adm_t); $('#t_adm_p').text(totals.adm_p); $('#t_adm_all').text(totals.adm_all);
    $('#t_ecg_t').text(totals.ecg_t); $('#t_ecg_p').text(totals.ecg_p); $('#t_ecg_all').text(totals.ecg_all);
    $('#t_gyn_t').text(totals.gyn_t); $('#t_gyn_p').text(totals.gyn_p); $('#t_gyn_all').text(totals.gyn_all);
}

function finishReport() {
    isFinished = true;
    $("#mainActionBtn").text("Print Report").css("background-color", "#007bff");
    $("#excelBtn, #closeBtn").show();
}

$(document).ready(function() {
    // loadData();
    $("#mainActionBtn").click(function() { isFinished ? window.print() : loadData(); });
    $("#filterBtn").click(function() { loadData(true); });
    $("#excelBtn").click(function() {
        var wb = XLSX.utils.table_to_book(document.getElementById('reportTable'));
        XLSX.writeFile(wb, "Report.xlsx");
    });
    $("#closeBtn").click(function() { window.close(); });
});
</script>
</body>
</html>