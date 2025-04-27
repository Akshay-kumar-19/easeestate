<?php
header('Content-Type: application/json');
require 'db.php';
session_start();
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Session expired, please login again.']);
    exit();
}
$user_id = $_SESSION['user_id'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $tool_id = $_POST['toolId'];
    $tool_name = $_POST['toolName'];
    $tool_quantity = $_POST['toolQuantity'];

    if (empty($tool_id) || !is_numeric($tool_id) || intval($tool_id) <= 0) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid tool ID.']);
        exit;
    }
    if (empty($tool_name) || empty($tool_quantity)) {
        echo json_encode(['status' => 'error', 'message' => 'Tool Name and Quantity are required.']);
        exit;
    }
    if (!preg_match('/^[a-zA-Z0-9\s]+$/', $tool_name)) {
        echo json_encode(['status' => 'error', 'message' => 'Tool Name should only contain letters, numbers, and spaces.']);
        exit;
    }
    if (!is_numeric($tool_quantity) || intval($tool_quantity) < 0) {
        echo json_encode(['status' => 'error', 'message' => 'Quantity must be a non-negative number.']);
        exit;
    }

    try {
        $sql = "UPDATE tools SET tool_name = ?, tool_quantity = ? WHERE tool_id = ? AND user_id = ?";
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            throw new Exception("Prepare statement failed: " . $conn->error);
        }
        $stmt->bind_param("siii", $tool_name, $tool_quantity, $tool_id, $user_id);

        if ($stmt->execute()) {
            echo json_encode(['status' => 'success', 'message' => 'Tool updated successfully.']);
        } else {
            throw new Exception("Update execution failed: " . $stmt->error);
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