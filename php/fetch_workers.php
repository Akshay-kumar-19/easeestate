<?php
require 'db.php';
session_start();
header('Content-Type: application/json');

if (!isset($_GET['lead_id']) || empty($_GET['lead_id'])) {
    echo json_encode(["status" => "error", "message" => "Labour lead ID is required"]);
    exit;
}

$lead_id = $_GET['lead_id'];
$user_id = $_SESSION['user_id'];
$job_id = $_GET['job_id'] ?? null;

$sql = "SELECT w.worker_id, w.worker_name
         FROM workers w
         WHERE w.lead_id = ? AND w.user_id = ?";

if ($job_id !== null && !empty($job_id)) {

    $sql = "SELECT w.worker_id, w.worker_name
            FROM workers w
            WHERE w.lead_id = ? AND w.user_id = ?";


    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        error_log("Prepare statement failed: " . $conn->error);
        echo json_encode(["status" => "error", "message" => "Database error preparing statement"]);
        exit;
    }


     $stmt->bind_param("ii", $lead_id, $user_id);


} else {
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        error_log("Prepare statement failed: " . $conn->error);
        echo json_encode(["status" => "error", "message" => "Database error preparing statement"]);
        exit;
    }
    $stmt->bind_param("ii", $lead_id, $user_id);
}


if (!$stmt->execute()) {
    error_log("Execute failed: " . $stmt->error);
    echo json_encode(["status" => "error", "message" => "Database error executing query"]);
    exit;
}

$result = $stmt->get_result();
if (!$result) {
    error_log("Get result failed: " . $stmt->error);
    echo json_encode(["status" => "error", "message" => "Database error fetching results"]);
    exit;
}


$workers = [];
while ($row = $result->fetch_assoc()) {
    $workers[] = [
        "worker_id" => $row['worker_id'],
        "worker_name" => $row['worker_name'],
        "present" => false
    ];
}

echo json_encode(["status" => "success", "workers" => $workers], JSON_UNESCAPED_UNICODE);

$stmt->close();
$conn->close();
?>