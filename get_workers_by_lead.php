<?php
header('Content-Type: application/json');
require 'db.php';
session_start();
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Session expired, please login again.']);
    exit();
}
$user_id = $_SESSION['user_id'];

$lead_id = isset($_GET['leadId']) ? intval($_GET['leadId']) : 0;

if ($lead_id <= 0) {
    echo json_encode([]);
    exit;
}

try {
    $workers_sql = "SELECT worker_id, worker_name FROM workers WHERE user_id = ? AND lead_id = ?";
    $workers_stmt = $conn->prepare($workers_sql);
    if (!$workers_stmt) {
        throw new Exception("Prepare statement failed: " . $conn->error);
    }
    $workers_stmt->bind_param("ii", $user_id, $lead_id);
    $workers_stmt->execute();
    $workers_result = $workers_stmt->get_result();
    $workers = [];
    while ($row = $workers_result->fetch_assoc()) {
        $workers[] = $row;
    }
    echo json_encode($workers);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
} finally {
    $conn->close();
}
?>