<?php

header('Content-Type: application/json');
require 'db.php';
session_start();
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Session expired, please login again.']);
    exit();
}
$user_id = $_SESSION['user_id'];


if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] == 'add') {
    $lot_number = $_POST['lot_number'];
    $date_received = $_POST['date_received'];
    $total_bags = $_POST['total_bags'];
    $total_weight_kg = $_POST['total_weight_kg'];

    if (empty($lot_number) || empty($date_received) || empty($total_bags) || empty($total_weight_kg)) {
        echo json_encode(['status' => 'error', 'message' => 'All fields are required.']);
        exit;
    }

    try {
        $conn->begin_transaction();

        $sql_current = "INSERT INTO areca_inventory (lot_number, date_received, total_bags, total_weight_kg, user_id) VALUES (?, ?, ?, ?, ?)";
        $stmt_current = $conn->prepare($sql_current);
        if (!$stmt_current) throw new Exception("Prepare statement (current inventory) failed: " . $conn->error);
        $stmt_current->bind_param("isddi", $lot_number, $date_received, $total_bags, $total_weight_kg, $user_id);
        if (!$stmt_current->execute()) {
            throw new Exception("Execute failed (current inventory): " . $stmt_current->error);
        }
        $stmt_current->close();

        $sql_history = "INSERT INTO areca_inventory_history (lot_number, date_received, total_bags, total_weight_kg, user_id) VALUES (?, ?, ?, ?, ?)";
        $stmt_history = $conn->prepare($sql_history);
        if (!$stmt_history) throw new Exception("Prepare statement (history) failed: " . $conn->error);
        $stmt_history->bind_param("isddi", $lot_number, $date_received, $total_bags, $total_weight_kg, $user_id);
        if (!$stmt_history->execute()) {
            throw new Exception("Execute failed (history): " . $stmt_history->error);
        }
        $stmt_history->close();

        $conn->commit();

        echo json_encode(['status' => 'success', 'message' => 'Areca lot added successfully.']);

    } catch (Exception $e) {
        $conn->rollback();
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    } finally {
        $conn->close();
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request.']);
}
?>