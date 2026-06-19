<?php
$host = 'localhost';
$db   = 'ycdomlt';
$user = 'ycdoeh1';
$pass = 'ycdoeh1';

$pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $pass);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// (Include your DB connection here)
if (isset($_GET['id'])) {
    // 1. Fetch the main Token/Visit info
    $stmt = $pdo->prepare("SELECT tokans.id, tokans.cash, branchs.tag_name, users.u_name, tokans.created, tokans.tokan_type_id 
                           FROM tokans 
                           INNER JOIN users ON tokans.doctor_id = users.id 
                           INNER JOIN branchs ON tokans.branch_id = branchs.id 
                           WHERE tokans.patient_id = ? 
                           ORDER BY tokans.created DESC LIMIT 1");
    $stmt->execute([$_GET['id']]);
    $patient = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($patient) {
        echo "<h4>Visit Summary</h4>";
        echo "<p><strong>Date:</strong> {$patient['created']}</p>";
        echo "<p><strong>Token No:</strong> {$patient['id']}</p>";
        echo "<p><strong>Doctor:</strong> {$patient['u_name']}</p>";
        echo "<p><strong>Branch:</strong> {$patient['tag_name']}</p>";
        echo "<p><strong>Fee Paid:</strong> {$patient['cash']}</p>";
        
        echo "<hr><h4>Treatment Details</h4>";
        
        if($patient['tokan_type_id'] < 100) {
            echo "<p><em>General Check-up Token (No items prescribed)</em></p>";
        } else {
            // 2. Fetch ALL items for this specific token
            $token_no = $patient['id'];
            $stmt2 = $pdo->prepare("SELECT items.name, item_by_doctor.sale_quantity 
                                   FROM `item_by_doctor` 
                                   INNER JOIN item_register_to_branches ON item_by_doctor.item_id = item_register_to_branches.id 
                                   INNER JOIN items ON item_register_to_branches.item_id = items.id 
                                   WHERE item_by_doctor.tokan_no = ?");
            
            // Fix: execute expects an array
            $stmt2->execute([$token_no]); 
            
            // Fix: Use fetchAll to get all prescribed medicines
            $items = $stmt2->fetchAll(PDO::FETCH_ASSOC);

            if($items) {
                echo "<table border='1' width='100%' style='border-collapse: collapse;'>
                        <tr style='background:#f2f2f2;'>
                            <th>Item Name</th>
                            <th>Qty</th>
                        </tr>";
                foreach($items as $item) {
                    echo "<tr>
                            <td>{$item['name']}</td>
                            <td>{$item['sale_quantity']}</td>
                          </tr>";
                }
                echo "</table>";
            } else {
                echo "<p>No specific items found for this token.</p>";
            }
        }
    } else {
        echo "No visit records found for this patient.";
    }
}
?>