<?php

header('Content-Type: application/json');
require 'db.php';
session_start();
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Session expired, please login again.']);
    exit();
}
$user_id = $_SESSION['user_id'];


if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['lot_number'])) {
    $lot_number = $_POST['lot_number'];

    if (empty($lot_number)) {
        echo json_encode(['status' => 'error', 'message' => 'Lot number is required.']);
        exit;
    }

    try {
        $sql = "DELETE FROM areca_inventory WHERE lot_number = ? AND user_id = ?";
        $stmt = $conn->prepare($sql);
        if (!$stmt) throw new Exception("Prepare statement failed: " . $conn->error);

        $stmt->bind_param("ii", $lot_number, $user_id);

        if ($stmt->execute()) {
            if ($stmt->affected_rows > 0) {
                echo json_encode(['status' => 'success', 'message' => 'Areca lot sold successfully.']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Areca lot not found or could not be sold.']);
            }
        } else {
            throw new Exception("Execute failed: " . $stmt->error);
        }
        $stmt->close();

    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    } finally {
        $conn->close();
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request.']);
}
?>