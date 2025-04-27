<?php
header('Content-Type: application/json');
require 'db.php';
session_start();
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Session expired, please login again.']);
    exit();
}
$user_id = $_SESSION['user_id'];

$tool_id = isset($_GET['tool_id']) ? intval($_GET['tool_id']) : 0;

if ($tool_id <= 0) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid tool ID.']);
    exit;
}

try {
    $sql = "SELECT tool_id, tool_name, tool_quantity FROM tools WHERE tool_id = ? AND user_id = ?";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        throw new Exception("Prepare statement failed: " . $conn->error);
    }
    $stmt->bind_param("ii", $tool_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $tool = $result->fetch_assoc();

    if ($tool) {
        echo json_encode($tool);
    } else {
        echo json_encode(null);
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
} finally {
    $conn->close();
}
?>