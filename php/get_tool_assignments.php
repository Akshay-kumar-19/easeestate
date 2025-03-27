<?php
header('Content-Type: application/json');
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Session expired, please login again.']);
    exit();
}

$user_id = $_SESSION['user_id'];

try {
    $assignments_sql = "SELECT 
        ta.assignment_id,
        t.tool_name,
        ta.quantity_assigned,
        w.worker_name,
        ta.assignment_date,
        ta.return_date,
        ta.status,
        ta.notes
    FROM tool_assignments ta
    JOIN tools t ON ta.tool_id = t.tool_id
    JOIN workers w ON ta.assigned_worker_id = w.worker_id
    WHERE t.user_id = ?";

    $assignments_stmt = $conn->prepare($assignments_sql);
    if (!$assignments_stmt) {
        throw new Exception("Prepare statement failed: " . $conn->error);
    }
    $assignments_stmt->bind_param("i", $user_id);
    $assignments_stmt->execute();
    $assignments_result = $assignments_stmt->get_result();

    $assignments = [];
    while ($row = $assignments_result->fetch_assoc()) {
        $assignments[] = $row;
    }

    echo json_encode($assignments);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
} finally {
    $conn->close();
}
?>