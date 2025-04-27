
<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
header('Content-Type: application/json');

require 'db.php';


$sql = "SELECT lot_number, coffee_type, total_bags, total_weight_kg, moisture_level FROM coffee_lots";
$result = $conn->query($sql);

if ($result) {
    $coffeeLots = [];
    while ($row = $result->fetch_assoc()) {
        $coffeeLots[] = $row;
    }
    echo json_encode($coffeeLots);
} else {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => "Database query failed: " . $conn->error]);
}

$conn->close();

?>