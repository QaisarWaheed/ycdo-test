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

if( isset($_POST['date']) && $_POST['date'] != '')
{
    $date = $_POST['date'];
    $br_id = $_POST['br_id'];
    echo '<script>window.open("print_progess_report_test.php?date='.$date.'&br_id='.$br_id.'", "PROGRESS REPORT", "width=3000,height=3000");</script>';
}
?>
	<title>Dashboard - <?php echo $company_trademark; ?></title>
<script src="js/jquery.min.js"></script>
<script src="js/selectize.min.js" integrity="sha256-+C0A5Ilqmu4QcSPxrlGpaZxJ04VjsRjKu+G82kl5UJk=" crossorigin="anonymous"></script>
<!--<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>-->
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.25/jspdf.plugin.autotable.min.js"></script>
<link rel="stylesheet" href="css/selectize.bootstrap3.min.css" integrity="sha256-ze/OEYGcFbPRmvCnrSeKbRTtjG4vGLHXgOqsyLFTRjg=" crossorigin="anonymous" />
<style>
#reportTable tbody tr {
    animation: fadeIn 0.5s;
}
#reportTable tfoot {
    display: none; /* Hidden by default */
}
@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}
table { width: 100%; border-collapse: collapse; margin-top: 20px; }
th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
th { background-color: #f4f4f4; }
@media print {
     Hide the button and filters when printing 
    #pdfBtn, #whatsappBtn, #loadMoreBtn, #mainActionBtn, #closeBtn, #filterBtn, .branch-filter-div, label  {
    display: none !important;
    }   
    table { width: 100%; border: 1px solid black; }    
     Ensure the table fits the page 
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
    display: table-footer-group; /* Ensures totals show on the last page */
    font-weight: bold;
    background-color: #f0f0f0 !important;
    -webkit-print-color-adjust: exact;
    }
}
</style>
</head>

<body style = "background-color: #fff">
<div class="row" style="margin: 0px;" id = "submitBody">
	<div class="col-md-12" style="text-align: center;background: lightgreen;"><label><h1><?php echo $company_name; ?> </h1></label></div>
    <div class = "col-md-12">
    <div style="margin: 20px 0; padding: 15px; background: #f9f9f9; border: 1px solid #ddd;">

            <h2 align = "center">Daily Progress Report</h2>
            <label><strong>Select Date:</strong></label>
            <input type="date" id="dateFilter" value="<?php echo date('Y-m-d'); ?>" style="padding: 7px;">
        <button id="filterBtn" style="padding: 8px 15px; background: #28a745; color: white; border: none; cursor: pointer; border-radius: 4px;">Filter Report</button>
    </div>
    <table id="reportTable" class = "table">
        <caption id="reportCaption" style="caption-side: top; text-align: center; font-weight: bold; padding: 10px; color: #333;">
        </caption>
        <thead>
            <tr>
                <!--<th>S.No</th>-->
                <th>Id</th>
                <th>Tag</th>
                <th>Name</th>
                <th>OPD</th>
                <th>CONS</th>
                <th>Dia.Pt</th>
                <th>USG</th>
                <th>SVD</th>
                <th>D&C</th>
                <th>OPERATION</th>
                <th>DENTAL</th>
                <th>SKIN</th>
                <th>EYE</th>
                <th>ADMISSION</th>
                <th>EMERGENCY</th>
                <th>ECG</th>
                <th>GYNAE</th>
            </tr>
        </thead>
        <tbody>
        </tbody>
        <tfoot id="reportFooter" style="background-color: #eee; font-weight: bold;">
            <tr id="totalRow">
                <td colspan="3" style="text-align: right;">Grand Total:</td>
                <td id="totalOpd">0</td>
                <td id="totalCons">0</td>
                <td id="totalunique_test_tokens">0</td>
                <td id="totalUSG">0</td>
                <td id="totalSVD">0</td>
                <td id="totalDNC">0</td>
                <td id="totalOP">0</td>
                <td id="totalDental">0</td>
                <td id="totalSkin">0</td>
                <td id="totalEYE">0</td>
                <td id="totalAdm">0</td>
                <td id="totalEmg">0</td>
                <td id="totalEcg">0</td>
                <td id="totalGyn">0</td>
            </tr>
        </tfoot>
    </table>
    <div id="actionContainer" style="text-align: center; margin: 20px;">
        <button disabled id="mainActionBtn" style="padding: 10px 20px; background-color: #28a745; color: white; border: none; border-radius: 4px;">
            Select Date For Progress ...
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
</body>
</html>
<?php mysqli_close($con); ?>
<script>
var currentPage = 1;
var limitPerPage = 50; 
// Variables for totals
var totalOpd = 0, totalCons = 0, totalunique_test_tokens = 0, totalUSG = 0, totalSVD = 0, 
    totalDNC = 0, totalOP = 0, totalDental = 0, totalSkin = 0, totalEye = 0, 
    totalAdm = 0, totalEmg = 0, totalEcg = 0, totalGyn = 0, totalCash = 0;

function updateFooter() {
    $('#totalOpd').text(totalOpd.toLocaleString());
    $('#totalCons').text(totalCons.toLocaleString());
    $('#totalunique_test_tokens').text(totalunique_test_tokens.toLocaleString());
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
}
var currentPage = 1;
var limitPerPage = 10; 
var isFinished = false; // Track if we hit the end of the database
const loggedInUser = "<?php echo $user_name; ?>";
function loadData(isNewFilter = false) {
    if (!isNewFilter && currentPage === 1) {
        $('#reportTable tbody').html('<tr><td colspan="17" class="text-center">Select a date and click "Filter Report" to begin.</td></tr>');
        $("#mainActionBtn").hide(); // Hide the load more button until a filter is used
        return; 
    }
    if (isNewFilter) {
        currentPage = 1;
        isFinished = false;
        totalOpd = 0; 
        totalCons = 0;
        totalunique_test_tokens = 0;
        totalUSG = 0;
        totalSVD = 0;
        totalDNC = 0;
        totalOP = 0;
        totalDental = 0;
        totalSkin = 0;
        totalEye = 0;
        totalAdm = 0;
        totalEmg = 0;
        totalEcg = 0;
        totalGyn = 0;
        totalCash = 0;
        $('#reportCaption').empty();
        $('#reportTable tfoot').hide();
        $('#reportTable tbody').empty();
        $('#totalOpd, #totalCons, #totalunique_test_tokens, #totalUSG, #totalSVD, #totalDNC, #totalOP, #totalDental, #totalSkin, #totalEye, #totalAdm, #totalEmg, #totalEcg, #totalGyn').text('0');
        $('#mainActionBtn').text("Load More Records").css("background-color", "#28a745");
    }

    $("#mainActionBtn").prop("disabled", true).text("Loading...");

    $.ajax({
        url: 'fetch_progress_report.php',
        type: 'GET',
        data: { 
            page: currentPage,
            date: $('#dateFilter').val() 
        }, 
        dataType: 'json',
        success: function(data) {
            $("#mainActionBtn").prop("disabled", false);

            if (data && data.length > 0) {
                // 1. Get Current Date and Time
                const now = new Date();
                const timestamp = now.toLocaleDateString() + ' ' + now.toLocaleTimeString();
                const selectedDate = $('#dateFilter').val();

                // 2. Update Caption
                $('#reportCaption').html(
                    `PROGRESS REPORT FOR : ${selectedDate} <br>` +
                    `<small>Generated By: ${loggedInUser} | Date: <?php echo date("h:i:sA d-m-Y"); ?></small>`
                );
                $('#reportTable tfoot').show();
                var rows = '';
                $.each(data, function(key, value) {
                    // Update running totals
                    totalOpd += parseInt(value.opd || 0);
                    totalCons += parseInt(value.cons_opds || 0);
                    totalunique_test_tokens += parseInt(value.labs || 0);
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
                    totalCash += parseFloat(value.collection || 0);

                    rows += '<tr>';
                    rows += '<td>'+value.doctor_id+'</td>';
                    rows += '<td>'+value.branch_name+'</td>';
                    rows += '<td>'+value.doctor_name+'</td>';
                    rows += '<td>'+value.opd+'</td>';
                    rows += '<td>'+value.cons_opds+'</td>';
                    rows += '<td>'+value.labs+'</td>';
                    rows += '<td>'+value.usgs+'</td>';
                    rows += '<td>'+value.svds+'</td>';
                    rows += '<td>'+value.dncs+'</td>';
                    rows += '<td>'+value.procedures+'</td>';
                    rows += '<td>'+value.dentals+'</td>';
                    rows += '<td>'+value.skins+'</td>';
                    rows += '<td>'+value.eyes+'</td>';
                    rows += '<td>'+value.admissions+'</td>';
                    rows += '<td>'+value.emergency+'</td>';
                    rows += '<td>'+value.ecgs+'</td>';
                    rows += '<td>'+value.gynaes+'</td>';
                    rows += '</tr>';
                });

                $('#reportTable tbody').append(rows);
                
                // Update Footer Totals
                $('#totalOpd').text(totalOpd);
                $('#totalCons').text(totalCons.toLocaleString());
                $('#totalunique_test_tokens').text(totalunique_test_tokens.toLocaleString());
                $('#totalUSG').text(totalUSG.toLocaleString());
                $('#totalSVD').text(totalSVD.toLocaleString());
                $('#totalDNC').text(totalDNC.toLocaleString());
                $('#totalOP').text(totalOP.toLocaleString());
                $('#totalDental').text(totalDental.toLocaleString());
                $('#totalSkin').text(totalSkin.toLocaleString());
                $('#totalEye').text(totalEye.toLocaleString());
                $('#totalAdm').text(totalAdm.toLocaleString());
                $('#totalEmg').text(totalEmg.toLocaleString());
                $('#totalEcg').text(totalEcg.toLocaleString());
                $('#totalGyn').text(totalGyn.toLocaleString());

                    currentPage++; 
                    isFinished = true;
                    finishReport();
                    $("#mainActionBtn").text("Print Report").css("background-color", "#007bff");
            } else {
                $('#reportTable tfoot').hide();
                isFinished = true;
                finishReport();
                if(currentPage === 1) {
                    $('#reportCaption').html("<span class='text-danger'>No Records Found</span>");
                    $('#reportTable tbody').html('<tr><td colspan="18" style="text-align:center;">No data found.</td></tr>');
                }
                $("#mainActionBtn").text("Print Report").css("background-color", "#007bff");
            }
        },
        error: function() {
            $("#mainActionBtn").prop("disabled", false).text("Error - Try Again");
        }
    });
}

function finishReport() {
    isFinished = true;
    $("#mainActionBtn").text("Print Report").css("background-color", "#007bff");
    
    // Show all export/close buttons
    $("#whatsappBtn, #pdfBtn, #closeBtn").show();
}

$(document).ready(function() {
    // 1. Initial Load
    // loadData();

    // 2. The Master Button Click
    $("#mainActionBtn").click(function(e) {
        e.preventDefault();
        if (isFinished) {
            window.print(); // If data is done, print
        } else {
            loadData(false); // If data is NOT done, load more
        }
    });

// Handle Close Button
    $("#closeBtn").click(function() {
        if(confirm("Are you sure you want to close this report?")) {
            window.location.href = "dashboard.php"; // Change this to your home/dashboard page
        }
    });


    // --- PDF EXPORT ---
    $("#pdfBtn").click(function() {
        const { jsPDF } = window.jspdf;
        var doc = new jsPDF('l', 'pt', 'a4'); // 'l' for landscape to fit all columns
        
        doc.text("Monthly Sales Report", 40, 30);
        
        doc.autoTable({ 
            html: '#reportTable',
            startY: 50,
            theme: 'grid',
            styles: { fontSize: 8 }, // Smaller font to fit many columns
            headStyles: { fillGray: [244, 244, 244], textColor: 0 }
        });
        
        doc.save("Daily_Progress_Report.pdf");
    });   

    
$(document).on('click', '#whatsappBtn', function(e) {
    e.preventDefault();
    const reportDate = $('#dateFilter').val();
    const branchName = "<?php echo $company_name; ?>";
    const opd = $('#totalOpd').text();
    const cons = $('#totalCons').text();
    const cash = $('#totalCash').text() || totalCash.toLocaleString();
    
    const labs = $('#totalunique_test_tokens').text();
    const usgs = $('#totalUSG').text();
    const svds = $('#totalSVD').text();
    const dncs = $('#totalDNC').text();
    const procedures = $('#totalOP').text();
    const dentals = $('#totalDental').text();
    const skins = $('#totalSkin').text();
    const eyes = $('#totalEye').text();
    const admissions = $('#totalAdm').text();
    const emergency = $('#totalEmg').text();
    const ecgs = $('#totalEcg').text();
    const gynaes = $('#totalGyn').text();
    

    // 2. Format Message
    let message = `*🏥 PROGRESS REPORT - ${branchName}*\n`;
    message += `*Date:* ${reportDate}\n`;
    message += `----------------------------\n`;
    message += `✅ *OPD:* ${opd}\n`;
    message += `✅ *Consultants:* ${cons}\n`;
    message += `✅ *Dia:* ${labs}\n`;
    message += `✅ *USG.:* ${usgs}\n`;
    message += `✅ *SVD:* ${svds}\n`;;
    message += `✅ *D&C:* ${dncs}\n`;
    message += `✅ *OPERATIONS:* ${procedures}\n`;
    message += `✅ *DENTAL:* ${dentals}\n`;
    message += `✅ *SKIN:* ${skins}\n`;
    message += `✅ *EYE:* ${eyes}\n`;
    message += `✅ *ADMISSION:* ${admissions}\n`;
    message += `✅ *EMERGENCY:* ${emergency}\n`;
    message += `✅ *ECG:* ${ecgs}\n`;
    message += `✅ *GYNAE:* ${gynaes}\n`;
    message += `----------------------------\n`;
    message += `_Sent via YCDO Software`;

    // 3. Construct URL (Ensure phone has no + or 00)
    const targetPhone = "923002355594"; 
    const whatsappUrl = "https://api.whatsapp.com/send?phone=" + targetPhone + "&text=" + encodeURIComponent(message);

    // 4. Open Window
    console.log("Opening WhatsApp..."); // Debugging line
    window.open(whatsappUrl, '_blank');
});


    // 3. Filter Reset
    $("#filterBtn").click(function() {
        loadData(true);
    });
    
});
</script>
</body>
</html>