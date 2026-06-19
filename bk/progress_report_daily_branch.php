<?php 
include 'includes/connect.php'; 
include 'includes/head.php'; 

$roles = "SELECT * FROM roles WHERE id IN (SELECT role_id FROM users WHERE id = '$user_id') ";
$run_roles = mysqli_query($con, $roles);
if(mysqli_num_rows($run_roles) == 1)
{
    while($row_role = mysqli_fetch_array($run_roles))
    {
        $role_title = $row_role['title'];
    }
}
else
{
    $role_title = '';
}

if( isset($_POST['fromDate']) && $_POST['fromDate'] != '')
{
    $fromDate = $_POST['fromDate'];
    $toDate = $_POST['toDate'];
    $br_id = $_POST['br_id'];
    echo '<script>window.open("print_progess_report_test.php?fromDate='.$fromDate.'&toDate='.$toDate.'&br_id='.$br_id.'", "PROGRESS REPORT", "width=3000,height=3000");</script>';
}
?>
<title>Dashboard - <?php echo $company_trademark; ?></title>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.25/jspdf.plugin.autotable.min.js"></script>
<style>
#reportTable tbody tr {
    animation: fadeIn 0.5s;
}
#reportTable tfoot {
    display: none; 
}
@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}
table { width: 100%; border-collapse: collapse; margin-top: 20px; }
th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
th { background-color: #f4f4f4; }
@media print {
    #pdfBtn, #whatsappBtn, #loadMoreBtn, #mainActionBtn, #closeBtn, #filterBtn, .branch-filter-div, label  
    {
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
    tfoot 
    {
    display: table-footer-group; 
    font-weight: bold;
    background-color: #f0f0f0 !important;
    -webkit-print-color-adjust: exact;
    }
}
</style>
</head>

<body style="background-color: #fff">
<div class="row" style="margin: 0px;" id="submitBody">
    <div class="col-md-12" style="text-align: center;background: lightgreen;"><label><h1><?php echo $company_name; ?> </h1></label></div>
    <div class="col-md-12">
    <div style="margin: 20px 0; padding: 15px; background: #f9f9f9; border: 1px solid #ddd;">
        <h2 align="center">Progress Report</h2>
        <div class="row">
            <div class="col-md-3">
                <label><strong>Select Branch:</strong></label>
                <select id="branchFilter" style="padding: 7px; width: 100%;">
                    <?php 
                    $br_q = mysqli_query($con, "SELECT id, tag_name, address FROM branchs WHERE status = 1 ORDER BY tag_name ASC");
                    while($br_row = mysqli_fetch_array($br_q)) 
                    {
                        $selected = ($_SESSION['branch_id'] === $br_row['id']) ? "SELECTED" : "";
                        echo '<option '.$selected.' value="'.$br_row['id'].'">'.$br_row['tag_name'].' - '.$br_row['address'].'</option>';
                    }
                    ?>
                </select>
            </div>
            <div class="col-md-3">
                <label><strong>From Date:</strong></label>
                <input type="date" id="fromDate" value="<?php echo date('Y-m-d'); ?>" style="padding: 7px; width: 100%;">
            </div>
            <div class="col-md-3">
                <label><strong>To Date:</strong></label>
                <input type="date" id="toDate" value="<?php echo date('Y-m-d'); ?>" style="padding: 7px; width: 100%;">
            </div>
            <div class="col-md-3" style="padding-top: 25px;">
                <button id="filterBtn" style="padding: 8px 15px; background: #28a745; color: white; border: none; cursor: pointer; border-radius: 4px; width: 100%;">Generate Report</button>
            </div>
        </div>
    </div>
    <table id="reportTable" class="table">
        <caption id="reportCaption" style="caption-side: top; text-align: center; font-weight: bold; padding: 10px; color: #333;"></caption>
        <thead>
            <tr>
                <th>Id</th>
                <th>Tag</th>
                <th>Name</th>
                <th>OPD</th>
                <th>CONS</th>
                <th>Dia.Pt</th>
                <th>Dia.Amut</th>
                <th>USG</th>
                <th>SVD</th>
                <th>D&C</th>
                <th>OPERATION</th>
                <th>OP. CASH</th>
                <th>DENTAL</th>
                <th>SKIN</th>
                <th>EYE</th>
                <th>ADMISSION</th>
                <th>EMERGENCY</th>
                <th>ECG</th>
                <th>GYNAE TOKENS</th>
                <th>GYNAE ONLINE</th>
                <th>Ref From</th>
                <th>Ref To</th>
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
                <td id="totalLabsAmonut">0</td>
                <td id="totalUSG">0</td>
                <td id="totalSVD">0</td>
                <td id="totalDNC">0</td>
                <td id="totalOP">0</td>
                <td id="totalProceduresAmonut">0</td>
                <td id="totalDental">0</td>
                <td id="totalSkin">0</td>
                <td id="totalEYE">0</td>
                <td id="totalAdm">0</td>
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
        <button disabled id="mainActionBtn" style="padding: 10px 20px; background-color: #28a745; color: white; border: none; border-radius: 4px;">
            Select Dates For Progress ...
        </button>
        <button id="whatsappBtn" style="padding: 8px 15px; background: #25D366; color: white; border: none; cursor: pointer; border-radius: 4px; display: none;">
            <i class="fa fa-whatsapp"></i> Send to WhatsApp
        </button>        
        <button id="pdfBtn" style="padding: 10px 20px; cursor: pointer; background-color: #1d6f42; color: white; border: none; border-radius: 4px; display: none; margin-left: 10px;">
            Save PDF
        </button>
        <button id="closeBtn" style="padding: 10px 20px; cursor: pointer; background-color: #6c757d; color: white; border: none; border-radius: 4px; display: none; margin-left: 10px;">
            Close
        </button>
    </div>
    </div>
</div>

<script>
var currentPage = 1;
var isFinished = false; 
const loggedInUser = "<?php echo $bk_name; ?>";

var totalOpd = 0, totalCons = 0, totalunique_test_tokens = 0, totalUSG = 0, totalSVD = 0, totalLabsAmonut = 0, 
    totalDNC = 0, totalOP = 0, totalDental = 0, totalSkin = 0, totalEye = 0, totalProceduresAmonut = 0,
    totalAdm = 0, totalEmg = 0, totalEcg = 0, totalGyn = 0, totalCollection = 0, total_gynae_registrations = 0,
    totalRefFrom = 0, totalRefTo = 0;

function loadData(isNewFilter = false) {
    if (isNewFilter) {
        currentPage = 1;
        isFinished = false;
        totalOpd = 0; totalCons = 0; totalunique_test_tokens = 0; totalLabsAmonut = 0;
        totalProceduresAmonut = 0; totalUSG = 0; totalSVD = 0; totalDNC = 0;
        totalOP = 0; totalDental = 0; totalSkin = 0; totalEye = 0;
        totalAdm = 0; totalEmg = 0; totalEcg = 0; totalGyn = 0;
        total_gynae_registrations = 0; totalCollection = 0;
        totalRefFrom = 0; totalRefTo = 0;
        
        $('#reportCaption').empty();
        $('#reportTable tfoot').hide();
        $('#reportTable tbody').empty();
    }

    $("#mainActionBtn").prop("disabled", true).text("Loading...");

    $.ajax({
        url: 'process_progress_report_daily_branch.php',
        type: 'GET',
        data: { 
            page: currentPage,
            fromDate: $('#fromDate').val(),
            toDate: $('#toDate').val(),
            br_id: $('#branchFilter').val()
        }, 
        dataType: 'json',
        success: function(data) 
        {
            $("#mainActionBtn").prop("disabled", false);
            if (data && data.length > 0) 
            {
                const fDate = $('#fromDate').val();
                const tDate = $('#toDate').val();
                const selectedBranchName = $('#branchFilter option:selected').text();

                $('#reportCaption').html(
                    `PROGRESS REPORT FROM: ${fDate} TO: ${tDate} <br>` +
                    ` ${selectedBranchName} <br>` +
                    `<small>Generated By: ${loggedInUser} | Date: <?php echo date("h:i:sA d-m-Y"); ?></small>`
                );
                $('#reportTable tfoot').show();
                var rows = '';
                $.each(data, function(key, value) 
                {
                    totalRefFrom += parseInt(value.ref_from || 0);
                    totalRefTo += parseInt(value.ref_to || 0);
                    totalOpd += parseInt(value.opd || 0);
                    totalCons += parseInt(value.cons_opds || 0);
                    totalunique_test_tokens += parseInt(value.labs || 0);
                    totalLabsAmonut += parseInt(value.amount_labs || 0);
                    totalProceduresAmonut += parseInt(value.amount_procedures || 0);
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
                    totalGyn += parseInt(value.gynaes || 0);
                    totalCollection += parseFloat(value.collection || 0);
                    total_gynae_registrations += parseInt(value.gynae_registrations || 0);

                    rows += '<tr>';
                    rows += '<td>'+value.doctor_id+'</td>';
                    rows += '<td>'+value.branch_name+'</td>';
                    rows += '<td>'+value.doctor_name+'</td>';
                    rows += '<td>'+value.opd+'</td>';
                    rows += '<td>'+value.cons_opds+'</td>';
                    rows += '<td>'+value.labs+'</td>';
                    rows += '<td>'+value.amount_labs+'</td>';
                    rows += '<td>'+value.usgs+'</td>';
                    rows += '<td>'+value.svds+'</td>';
                    rows += '<td>'+value.dncs+'</td>';
                    rows += '<td>'+value.procedures+'</td>';
                    rows += '<td>'+value.amount_procedures+'</td>';
                    rows += '<td>'+value.dentals+'</td>';
                    rows += '<td>'+value.skins+'</td>';
                    rows += '<td>'+value.eyes+'</td>';
                    rows += '<td>'+value.admissions+'</td>';
                    rows += '<td>'+value.emergency+'</td>';
                    rows += '<td>'+value.ecgs+'</td>';
                    rows += '<td>'+value.gynaes+'</td>';
                    rows += '<td>'+value.gynae_registrations+'</td>';
                    rows += '<td>'+(value.ref_from || 0)+'</td>';
                    rows += '<td>'+(value.ref_to || 0)+'</td>';
                    rows += '<td>'+value.collection+'</td>';
                    rows += '</tr>';
                });

                $('#reportTable tbody').append(rows);
                
                $('#totalRefFrom').text(totalRefFrom.toLocaleString());
                $('#totalRefTo').text(totalRefTo.toLocaleString());
                $('#totalOpd').text(totalOpd.toLocaleString());
                $('#totalCons').text(totalCons.toLocaleString());
                $('#totalunique_test_tokens').text(totalunique_test_tokens.toLocaleString());
                $('#totalLabsAmonut').text(totalLabsAmonut.toLocaleString());
                $('#totalProceduresAmonut').text(totalProceduresAmonut.toLocaleString());
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
                $('#totalCollection').text(totalCollection.toLocaleString());

                currentPage++; 
                isFinished = true;
                finishReport();
            } else {
                if(currentPage === 1) {
                    $('#reportCaption').html("<span class='text-danger'>No Records Found</span>");
                    $('#reportTable tbody').html('<tr><td colspan="23" style="text-align:center;">No data found.</td></tr>');
                }
                finishReport();
            }
        },
        error: function() {
            $("#mainActionBtn").prop("disabled", false).text("Error - Try Again");
        }
    });
}

function finishReport() {
    isFinished = true;
    $("#mainActionBtn").show().text("Print Report").css("background-color", "#007bff").prop("disabled", false);
    $("#whatsappBtn, #pdfBtn, #closeBtn").show();
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

    $("#closeBtn").click(function() {
        if(confirm("Are you sure you want to close this report?")) {
            window.location.href = "dashboard.php";
        }
    });

    $("#pdfBtn").click(function() {
        const { jsPDF } = window.jspdf;
        var doc = new jsPDF('l', 'pt', 'a4'); 
        var captionText = $('#reportCaption').text().replace(/\s\s+/g, ' ').trim();
        doc.setFontSize(16);
        doc.text("Progress Report", 40, 30);
        doc.setFontSize(10);
        doc.text(captionText, 40, 50);
        doc.autoTable({ 
            html: '#reportTable',
            startY: 70,
            theme: 'grid',
            styles: { fontSize: 7 },
            headStyles: { fillColor: [244, 244, 244], textColor: 0 },
            margin: { top: 70 } 
        });
        doc.save("Progress_Report.pdf");
    });  

    $(document).on('click', '#whatsappBtn', function(e) {
        e.preventDefault();
        let message = `*🏥 PROGRESS REPORT - <?php echo $company_name; ?>*\n`;
        message += `*Duration:* ${$('#fromDate').val()} to ${$('#toDate').val()}\n`;
        message += `----------------------------\n`;
        message += `✅ *OPD:* ${$('#totalOpd').text()}\n`;
        message += `✅ *Consultants:* ${$('#totalCons').text()}\n`;
        message += `✅ *COLLECTION:* ${$('#totalCollection').text()}\n`;
        message += `----------------------------\n`;
        message += `_Sent via Software_`;
        const targetPhone = "923002355594"; 
        window.open("https://api.whatsapp.com/send?phone=" + targetPhone + "&text=" + encodeURIComponent(message), '_blank');
    });

    $("#filterBtn").click(function() {
        loadData(true);
    });
});
</script>
</body>
</html>
<?php mysqli_close($con); ?>