<?php
date_default_timezone_set("Asia/Karachi");
$current_date = date('Y-m-d G:i:s A');
session_start();
if (isset($_SESSION['mm_id'])) {
    $user_id = $_SESSION['mm_id'];
    $is_admin = $_SESSION['is_admin'];
    $is_incharge = $_SESSION['is_incharge'];
    $role_id = $_SESSION['role_id'];
    $user_name = $_SESSION['mm_name'];
    $branch_id = $_SESSION['branch_id'];
    $branch_name = $_SESSION['branch_name'];
    $branch_address = $_SESSION['branch_address'];
    $branch_phone = $_SESSION['branch_phone'];
}
// 1. Database Connection
$servername = "localhost";
$username   = "ycdoeh1";
$password   = "ycdoeh1";
$dbname     = "ycdomlt";
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") 
{
    $invoice_no = $_POST['invoice_no'];
    $purchase_id = $_POST['purchase_id'];
    $item_id     = $_POST['item_id'];
    $qty         = intval($_POST['qty']);
    $amount      = floatval($_POST['amount']);
    $user_id     = intval($_SESSION['mm_id']);
    $status      = "1";
    
    $bill_no = $_POST['bill_no'];
    $item    = $_POST['item'];
    $remarks = $_POST['remarks'];

    // 3. Validation
    if ($qty <= 0) {
        echo "Invalid quantity.";
        exit;
    }

    try {
        // Start transaction
        $conn->begin_transaction();
    
        $stmt1 = $conn->prepare("INSERT INTO `return_purchase_items` (invoice_no,purchase_id, item_id, return_quantity, return_amount, user_id, `return_purchase_item_created_at`, `return_purchase_item_status`) VALUES (?, ?, ?, ?, ?, ?, NOW(), ?)");
        $stmt1->bind_param("iiiidii", $invoice_no,$purchase_id, $item_id, $qty, $amount, $user_id, $status);
        $stmt1->execute();
    
        // Commit changes
        $conn->commit();
    
        // Set header and return JSON
        header('Content-Type: application/json');
        echo json_encode([
            'status' => 'success', 
            'message' => "Query Executed: "
        ]);
    } 
    catch (Exception $e) {
        $conn->rollback();
        header('Content-Type: application/json');
        echo json_encode([
            'status' => 'error', 
            'message' => "Failed! Query was: " . $sql . " Error: " . $e->getMessage()
        ]);
    }
}
    $conn->close();
?>