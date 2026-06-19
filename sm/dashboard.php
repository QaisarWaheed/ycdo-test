<?php include 'includes/connect.php'; ?>
<?php include 'includes/head.php'; ?>
	<title>Dashboard - <?php echo $company_trademark; ?></title>
	<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</head>

<body class="background_image">

<div class="row" style="margin: 0px;">
	<div class="col-md-12" style="text-align: center;background: lightgreen;">
		<label><h1>YCDO </h1></label>
	</div>
	<div class="col-md-3 background_whitesmoke">
		<?php include 'left_navigation.php'; ?>
	</div>
	<div class="col-md-9">
	<div style="text-align: right;float: right;margin-right: 10px;">
		<h2 style="color: white;"><?php echo $company_name; ?></h2>
		<h6 style="color: brown;"><?php echo $company_ambition; ?></h6>
		<h3 style="color: red;"><?php echo $branch_name; ?></h3>
		<h4 style="color: white;"><?php echo $branch_address; ?></h4>
		<h4 style="color: white;"><?php echo $branch_phone; ?></h4>
		<h3 style="margin-top: 350px;text-align: center;">USER: <?php echo $_SESSION['sm_name']; ?></h3>
	</div>
			
	</div>
</div>

<?php if(isset($_GET['msg']) || isset($_GET['error'])): 
    $is_error = isset($_GET['error']);
    $message = $is_error ? $_GET['error'] : $_GET['msg'];
    $bg_color = $is_error ? '#ff4d4d' : '#2ecc71';
?>
<style>
    .custom-toast {
        position: fixed;
        top: 25px;
        right: 25px;
        background: #ffffff;
        color: #333;
        padding: 16px 24px;
        border-radius: 12px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.15);
        display: flex;
        align-items: center;
        gap: 15px;
        z-index: 10000;
        border-left: 6px solid <?php echo $bg_color; ?>;
        animation: slideIn 0.5s ease forwards;
        min-width: 320px;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }

    .toast-icon {
        width: 24px;
        height: 24px;
        border-radius: 50%;
        background: <?php echo $bg_color; ?>;
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .toast-content {
        flex-grow: 1;
    }

    .toast-title {
        font-weight: 700;
        font-size: 14px;
        margin: 0;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: <?php echo $bg_color; ?>;
    }

    .toast-msg {
        font-size: 13px;
        margin: 2px 0 0 0;
        color: #666;
    }

    .btn-toast-close {
        background: none;
        border: none;
        color: #ccc;
        cursor: pointer;
        font-size: 20px;
        padding: 0;
        line-height: 1;
        transition: color 0.2s;
    }

    .btn-toast-close:hover {
        color: #888;
    }

    @keyframes slideIn {
        from { transform: translateX(100%); opacity: 0; }
        to { transform: translateX(0); opacity: 1; }
    }

    .fade-out {
        animation: fadeOut 0.5s ease forwards !important;
    }

    @keyframes fadeOut {
        from { transform: translateX(0); opacity: 1; }
        to { transform: translateX(20px); opacity: 0; }
    }
</style>

<div id="toast" class="custom-toast">
    <div class="toast-icon">
        <?php if($is_error): ?>
            <span style="font-weight:bold">!</span>
        <?php else: ?>
            <span style="font-size: 14px;">✓</span>
        <?php endif; ?>
    </div>
    <div class="toast-content">
        <p class="toast-title"><?php echo $is_error ? 'Operation Failed' : 'Action Successful'; ?></p>
        <p class="toast-msg"><?php echo htmlspecialchars($message); ?></p>
    </div>
    <button class="btn-toast-close" onclick="closeToast()">×</button>
</div>

<script>
    function closeToast() {
        const toast = document.getElementById('toast');
        toast.classList.add('fade-out');
        setTimeout(() => toast.remove(), 500);
    }

    // Auto-close after 6 seconds
    setTimeout(closeToast, 6000);
</script>
<?php endif; ?>


</body>
</html>