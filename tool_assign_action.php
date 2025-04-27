<?php
header('Content-Type: application/json');
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Session expired, please login again.']);
    exit();
}
$user_id = $_SESSION['user_id'];

$tool_id = isset($_POST['toolId']) ? intval($_POST['toolId']) : 0;
$worker_id = isset($_POST['workerId']) ? intval($_POST['workerId']) : 0;
$quantity_to_assign = isset($_POST['quantity']) ? intval($_POST['quantity']) : 0;
$notes = isset($_POST['notes']) ? trim($_POST['notes']) : '';

if ($tool_id <= 0 || $worker_id <= 0 || $quantity_to_assign <= 0) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid input data.']);
    exit();
}

try {
    $tool_quantity_sql = "SELECT tool_quantity, tool_name FROM tools WHERE tool_id = ? AND user_id = ?";
    $tool_quantity_stmt = $conn->prepare($tool_quantity_sql);
    if (!$tool_quantity_stmt) {
        throw new Exception("Prepare statement failed: " . $conn->error);
    }
    $tool_quantity_stmt->bind_param("ii", $tool_id, $user_id);
    $tool_quantity_stmt->execute();
    $tool_quantity_result = $tool_quantity_stmt->get_result();
    if ($tool_quantity_result->num_rows === 0) {
        throw new Exception("Tool not found or not authorized.");
    }
    $tool_data = $tool_quantity_result->fetch_assoc();
    $current_tool_quantity = $tool_data['tool_quantity'];
    $tool_name = $tool_data['tool_name'];
    $tool_quantity_stmt->close();

    if ($quantity_to_assign > $current_tool_quantity) {
        echo json_encode(['status' => 'error', 'message' => 'Insufficient stock. Only ' . $current_tool_quantity . ' ' . $tool_name . '(s) available.']);
        exit();
    }

    $assign_sql = "INSERT INTO tool_assignments (tool_id, assigned_worker_id, quantity_assigned, assignment_date, return_date, status, notes) VALUES (?, ?, ?, NOW(), NULL, 'assigned', ?)";
    $assign_stmt = $conn->prepare($assign_sql);
    if (!$assign_stmt) {
        throw new Exception("Prepare statement failed: " . $conn->error);
    }
    $assign_stmt->bind_param("iiis", $tool_id, $worker_id, $quantity_to_assign, $notes);
    if (!$assign_stmt->execute()) {
        throw new Exception("Tool assignment failed: " . $assign_stmt->error);
    }
    $assign_stmt->close();

    $update_quantity_sql = "UPDATE tools SET tool_quantity = tool_quantity - ? WHERE tool_id = ? AND user_id = ?";
    $update_quantity_stmt = $conn->prepare($update_quantity_sql);
    if (!$update_quantity_stmt) {
        throw new Exception("Prepare statement failed: " . $conn->error);
    }
    $update_quantity_stmt->bind_param("iii", $quantity_to_assign, $tool_id, $user_id);
    if (!$update_quantity_stmt->execute()) {
        throw new Exception("Failed to update tool quantity: " . $update_quantity_stmt->error);
    }
    $update_quantity_stmt->close();

    echo json_encode(['status' => 'success', 'message' => 'Tool assigned successfully.']);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
} finally {
    $conn->close();
}
?>