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
    $sql = "SELECT ta.assignment_id, t.tool_name, ta.quantity_assigned, w.worker_name, ta.assignment_date, ta.status, ta.notes
            FROM tool_assignments ta
            JOIN tools t ON ta.tool_id = t.tool_id
            JOIN workers w ON ta.assigned_worker_id = w.worker_id
            WHERE t.user_id = ? AND ta.status = 'assigned' AND ta.return_date IS NULL
            ORDER BY ta.assignment_date DESC";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        throw new Exception("Prepare statement error: " . $conn->error);
    }
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $assignments = [];
    while ($row = $result->fetch_assoc()) {
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