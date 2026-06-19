<?php 
include '../lab/includes/config.php';
include 'connect.php'; 
include '../lab/includes/head.php'; 
?>
	<link rel="stylesheet" type="text/css" href="../lab/css/nav_style.css"> 
	<title>Lab Dashboard - <?php echo $company_trademark; ?></title>
</head>
<body class="background_image">
    <div class="row" style="margin: 0px;">
    	<div class="col-md-12" style="text-align: center;background: lightgreen;">
    		<label><h1><?php echo $company_name; ?> </h1></label>
    	</div>
    	<div class="col-md-2 background_whitesmoke nodisplay_print">
    		<?php include 'left_navigation.php'; ?>
    	</div>
    	<div class="col-md-10">
        <form id="filterForm">
            <div class="row">
                <div class="col-md-3">
                    <select name="branch_id" id="branch_id" class="form-control">
                        <option value="">All Branches</option>
                        <?php
                        $branch_query = mysqli_query($con, "SELECT id, tag_name FROM branchs ORDER BY tag_name ASC");
                        while($b = mysqli_fetch_array($branch_query)) {
                            echo "<option value='".$b['id']."'>".$b['tag_name']."</option>";
                        }
                        ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <select name="doctor_id" id="doctor_id" class="form-control">
                        <option value="">Select Branch First</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <input type="date" name="from_date" id="from_date" class="form-control">
                </div>
                <div class="col-md-2">
                    <input type="date" name="to_date" id="to_date" class="form-control">
                </div>
                <div class="col-md-2">
                    <button type="button" id="searchBtn" class="btn btn-primary w-100">Search</button>
                </div>
            </div>
        </form>
        
        <hr>
        
        <div id="reportResult">
            <p class="text-center">Select filters and click search to view data.</p>
        </div>
    	</div>
    </div>
    
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function(){
    $('#branch_id').on('change', function() {
        var branchID = $(this).val();
        if(branchID) {
            $.ajax({
                type: 'POST',
                url: 'get_doctors.php',
                data: {branch_id: branchID},
                success: function(html) {
                    $('#doctor_id').html(html);
                }
            });
        } else {
            $('#doctor_id').html('<option value="">All Doctors</option>');
        }
    });
    $('#searchBtn').click(function(){
        // Get values from inputs
        var branch = $('#branch_id').val();
        var from = $('#from_date').val();
        var to = $('#to_date').val();
        var doctor = $('#doctor_id').val();

        // Show a loading message
        $('#reportResult').html('<div class="text-center"><p>Loading Report...</p></div>');

        // The AJAX call
        $.ajax({
            url: 'fetch_progress_report.php', // Separate file to handle logic
            type: 'POST',
            data: {
                branch_id: branch,
                from_date: from,
                to_date: to,
                doctor_id: doctor
            },
            success: function(response){
                // Inject the returned HTML table into the div
                $('#reportResult').html(response);
            }
        });
    });
});
</script>    
</body>
</html>