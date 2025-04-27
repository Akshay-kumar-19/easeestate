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
    $tool_name = $_POST['toolName'];
    $tool_quantity = $_POST['toolQuantity'];

    if (empty($tool_name) || empty($tool_quantity)) {
        echo json_encode(['status' => 'error', 'message' => 'Tool Name and Quantity are required.']);
        exit;
    }
    if (!preg_match('/^[a-zA-Z0-9\s]+$/', $tool_name)) {
        echo json_encode(['status' => 'error', 'message' => 'Tool Name should only contain letters, numbers, and spaces.']);
        exit;
    }
    if (!is_numeric($tool_quantity) || intval($tool_quantity) <= 0) {
        echo json_encode(['status' => 'error', 'message' => 'Quantity must be a positive number.']);
        exit;
    }


    try {
        $sql = "INSERT INTO tools (user_id, tool_name, tool_quantity) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            throw new Exception("Prepare statement failed: " . $conn->error);
        }

        $stmt->bind_param("isi", $user_id, $tool_name, $tool_quantity);

        if ($stmt->execute()) {
            echo json_encode(['status' => 'success', 'message' => 'Tool added successfully.']);
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