
<?php
header('Content-Type: application/json');
require 'db.php'; // Database connection

session_start();
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Session expired, please login again.']);
    exit();
}
$user_id = $_SESSION['user_id'];


try {
    $lots = [];
    $sql = "SELECT lot_number, date_received, total_bags, total_weight_kg FROM areca_inventory ORDER BY date_received DESC"; // Fetch from areca_inventory - No moisture_level
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            // Include moisture_level: null if not needed in frontend
            $row['moisture_level'] = null; // Add moisture_level as null for consistent frontend data structure
            $lots[] = $row;
        }
    }

    echo json_encode($lots);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
} finally {
    $conn->close();
}
?>