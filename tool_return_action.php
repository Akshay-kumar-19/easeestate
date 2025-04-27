<?php
header('Content-Type: application/json');
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Session expired, please login again.']);
    exit();
}

$user_id = $_SESSION['user_id'];

$assignment_id = isset($_POST['assignmentId']) ? intval($_POST['assignmentId']) : 0;
$quantity_returned = isset($_POST['quantityReturned']) ? intval($_POST['quantityReturned']) : 0;
$return_notes = isset($_POST['returnNotes']) ? trim($_POST['returnNotes']) : '';

if ($assignment_id <= 0 || $quantity_returned <= 0) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid input data for tool return.']);
    exit();
}

try {
    $assignment_details_sql = "SELECT tool_id, quantity_assigned FROM tool_assignments WHERE assignment_id = ? AND status = 'assigned'";
    $assignment_details_stmt = $conn->prepare($assignment_details_sql);
    if (!$assignment_details_stmt) {
        throw new Exception("Prepare statement failed: " . $conn->error);
    }
    $assignment_details_stmt->bind_param("i", $assignment_id);
    $assignment_details_stmt->execute();
    $assignment_details_result = $assignment_details_stmt->get_result();
    if ($assignment_details_result->num_rows === 0) {
        throw new Exception("Tool assignment not found or already returned.");
    }
    $assignment_data = $assignment_details_result->fetch_assoc();
    $tool_id = $assignment_data['tool_id'];
    $quantity_assigned_original = $assignment_data['quantity_assigned'];
    $assignment_details_stmt->close();

    if ($quantity_returned > $quantity_assigned_original) {
        echo json_encode(['status' => 'error', 'message' => 'Quantity returned cannot exceed the quantity assigned.']);
        exit();
    }

    $update_assignment_sql = "UPDATE tool_assignments SET return_date = NOW(), status = 'returned', notes = ? WHERE assignment_id = ?";
    $update_assignment_stmt = $conn->prepare($update_assignment_sql);
    if (!$update_assignment_stmt) {
        throw new Exception("Prepare statement failed: " . $conn->error);
    }
    $update_assignment_stmt->bind_param("si", $return_notes, $assignment_id);
    if (!$update_assignment_stmt->execute()) {
        throw new Exception("Failed to update tool assignment status: " . $update_assignment_stmt->error);
    }
    $update_assignment_stmt->close();

    $insert_return_sql = "INSERT INTO tool_returns (assignment_id, quantity_returned, return_notes, user_id) VALUES (?, ?, ?, ?)";
    $insert_return_stmt = $conn->prepare($insert_return_sql);
    if (!$insert_return_stmt) {
        throw new Exception("Prepare statement failed: " . $conn->error);
    }
    $insert_return_stmt->bind_param("iiis", $assignment_id, $quantity_returned, $return_notes, $user_id);
    if (!$insert_return_stmt->execute()) {
        throw new Exception("Failed to record tool return: " . $insert_return_stmt->error);
    }
    $insert_return_stmt->close();


    $update_tool_quantity_sql = "UPDATE tools SET tool_quantity = tool_quantity + ? WHERE tool_id = ? AND user_id = ?";
    $update_tool_quantity_stmt = $conn->prepare($update_tool_quantity_sql);
    if (!$update_tool_quantity_stmt) {
        throw new Exception("Prepare statement failed: " . $conn->error);
    }
    $update_tool_quantity_stmt->bind_param("iii", $quantity_returned, $tool_id, $user_id);
    if (!$update_tool_quantity_stmt->execute()) {
        throw new Exception("Failed to update tool quantity: " . $update_tool_quantity_stmt->error);
    }
    $update_tool_quantity_stmt->close();

    echo json_encode(['status' => 'success', 'message' => 'Tool(s) returned successfully.', 'assignmentId' => $assignment_id]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
} finally {
    $conn->close();
}
?>