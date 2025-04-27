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

    if (empty($tool_id) || !is_numeric($tool_id) || intval($tool_id) <= 0) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid tool ID.']);
        exit;
    }

    try {
        
        $sql_tool_delete = "DELETE FROM tools WHERE tool_id = ? AND user_id = ?";
        $stmt_tool_delete = $conn->prepare($sql_tool_delete);
        if (!$stmt_tool_delete) {
            throw new Exception("Prepare statement failed: " . $conn->error);
        }
        $stmt_tool_delete->bind_param("ii", $tool_id, $user_id);

        if ($stmt_tool_delete->execute()) {
            if ($stmt_tool_delete->affected_rows > 0) {
                echo json_encode(['status' => 'success', 'message' => 'Tool deleted successfully.']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Tool not found or you do not have permission to delete it.']);
            }
        } else {
            throw new Exception("Delete execution failed: " . $stmt_tool_delete->error);
        }
        $stmt_tool_delete->close();


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