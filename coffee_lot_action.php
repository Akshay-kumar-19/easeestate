<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
header('Content-Type: application/json');

require 'db.php';


if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] == 'add') {
    $lot_number = isset($_POST['lot_number']) ? trim($_POST['lot_number']) : '';
    $date_received = isset($_POST['date_received']) ? trim($_POST['date_received']) : '';
    $coffee_type = isset($_POST['coffee_type']) ? trim($_POST['coffee_type']) : '';
    $total_bags = isset($_POST['total_bags']) ? trim($_POST['total_bags']) : '';
    $total_weight_kg = isset($_POST['total_weight_kg']) ? trim($_POST['total_weight_kg']) : '';
    $moisture_level = isset($_POST['moisture_level']) ? trim($_POST['moisture_level']) : '';

    if (empty($lot_number) || empty($coffee_type) || empty($total_bags) || empty($total_weight_kg) || empty($moisture_level)) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => "All fields are required."]);
        exit;
    }

    $conn->begin_transaction();

    try {
        $sql_current = "INSERT INTO coffee_lots (lot_number, date_received, coffee_type, total_bags, total_weight_kg, moisture_level) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt_current = $conn->prepare($sql_current);
        if ($stmt_current === false) throw new Exception("Prepare statement (current) failed: " . $conn->error);
        $stmt_current->bind_param("ssssdd", $lot_number, $date_received, $coffee_type, $total_bags, $total_weight_kg, $moisture_level);
        if (!$stmt_current->execute()) throw new Exception("Execute statement (current) failed: " . $stmt_current->error);
        $stmt_current->close();


        $sql_history = "INSERT INTO coffee_inventory_history (lot_number, date_received, coffee_type, total_bags, total_weight_kg, moisture_level) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt_history = $conn->prepare($sql_history);
        if ($stmt_history === false) throw new Exception("Prepare statement (history) failed: " . $conn->error);
        $stmt_history->bind_param("ssssdd", $lot_number, $date_received, $coffee_type, $total_bags, $total_weight_kg, $moisture_level);
        if (!$stmt_history->execute()) throw new Exception("Execute statement (history) failed: " . $stmt_history->error);
        $stmt_history->close();


        $conn->commit();
        echo json_encode(['status' => 'success', 'message' => 'Coffee lot added successfully and history recorded.']);


    } catch (Exception $e) {
        $conn->rollback();
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => "Transaction failed: " . $e->getMessage()]);
    }


} else {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => "Invalid request method or action."]);
}

$conn->close();

?>