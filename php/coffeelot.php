
<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
header('Content-Type: application/json');

require 'db.php'; // Database connection - now $conn is directly available

// No need for connectDB() function call anymore

$sql = "SELECT lot_number, coffee_type, total_bags, total_weight_kg, moisture_level FROM coffee_lots";
$result = $conn->query($sql);

if ($result) {
    $coffeeLots = [];
    while ($row = $result->fetch_assoc()) {
        $coffeeLots[] = $row;
    }
    echo json_encode($coffeeLots); // Send data as JSON
} else {
    http_response_code(500); // Set HTTP status code to 500 (Internal Server Error)
    echo json_encode(['status' => 'error', 'message' => "Database query failed: " . $conn->error]); // Send error as JSON
}

$conn->close();

?>