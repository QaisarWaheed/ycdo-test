<?php
// Database connection details
$host = 'localhost';
$db   = 'ycdomlt';
$user = 'ycdoeh1';
$pass = 'ycdoeh1';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if (isset($_GET['q'])) {
        $search = "%" . $_GET['q'] . "%";
        
        // Prepared statement to search by name or ID
        $stmt = $pdo->prepare("SELECT * FROM patients WHERE name LIKE ? OR phone LIKE ?");
        $stmt->execute([$search, $search]);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if ($results) {
            echo "<table border='1'><tr><th>ID</th><th>Name</th><th>Diagnosis</th></tr>";
            foreach ($results as $row) {
            // Inside your while/foreach loop:
            echo "<tr>
                    <td>{$row['id']}</td>
                    <td>{$row['name']}</td>
                    <td>
                        <a href='#' class='view-details' data-id='{$row['id']}'>
                            View History
                        </a>
                    </td>
                  </tr>";
            }
            echo "</table>";
        } else {
            echo "No records found.";
        }
    }
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}
?>