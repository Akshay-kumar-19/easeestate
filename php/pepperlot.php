<?php

header('Content-Type: application/json');
require 'db.php';

session_start();
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Session expired, please login again.']);
    exit();
}
$user_id = $_SESSION['user_id'];


try {
    $lots = [];
    $sql = "SELECT lot_number, date_received, total_bags, total_weight_kg, moisture_level FROM pepper_inventory ORDER BY date_received DESC";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
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