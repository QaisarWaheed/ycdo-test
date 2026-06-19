<?php
header('Content-Type: application/json');
$host = 'localhost';
$db   = 'ycdomlt';
$user = 'ycdoeh1';
$pass = 'ycdoeh1';

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    echo json_encode(['error' => 'Connection failed']);
    exit;
}

// Get parameters from AJAX request
$branch_id = isset($_GET['branch_id']) ? $_GET['branch_id'] : '';
$from_date = isset($_GET['from_date']) ? $_GET['from_date'] : '';
$to_date   = isset($_GET['to_date']) ? $_GET['to_date'] : '';

// Check if we are fetching branches or the report
$action = isset($_GET['action']) ? $_GET['action'] : '';

if ($action == 'get_branches') {
    $res = $conn->query("SELECT id, name FROM branchs WHERE status = 1 ORDER BY name ASC");
    echo json_encode($res->fetch_all(MYSQLI_ASSOC));
    exit;
}

// Report Logic
$branch_id = $_GET['branch_id'] ?? '';
$from_date = $_GET['from_date'] ?? '';
$to_date   = $_GET['to_date'] ?? '';

$sql = "SELECT 
            c.id as category_id, 
            c.name as category_name, 
            COUNT(ibd.id) as total_usage,
            SUM(ibd.purchase_price*ibd.sale_quantity) as total_purchase,
            SUM(ibd.sale_price) as total_sale,
            (SUM(ibd.sale_price) - SUM(ibd.purchase_price)) as profit_loss
        FROM item_by_doctor ibd
        JOIN categories c ON ibd.category_id = c.id
        WHERE 1=1";
        // AND c.id IN (SELECT id FROM `categories` WHERE `status` = 1 AND `is_medicine` = 1)

if (!empty($branch_id)) {
    $sql .= " AND ibd.branch_id = " . intval($branch_id);
}

if (!empty($from_date) && !empty($to_date)) {
    $sql .= " AND DATE(ibd.created) BETWEEN '$from_date' AND '$to_date'";
}

$sql .= " GROUP BY c.id ORDER BY total_usage DESC";

$result = $conn->query($sql);
$data = $result->fetch_all(MYSQLI_ASSOC);

echo json_encode($data);
$conn->close();
?>