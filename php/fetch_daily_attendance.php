<?php
require 'db.php';
session_start();
header('Content-Type: application/json');

$user_id = $_SESSION['user_id'] ?? null;
if (!$user_id) {
    echo json_encode(["status" => "error", "message" => "User not authenticated"]);
    exit;
}

$lead_id = $_GET['lead_id'] ?? null;
$date = $_GET['date'] ?? null;

if (!$lead_id || !$date) {
    echo json_encode(["status" => "error", "message" => "Labour Lead ID and Date are required"]);
    exit;
}

$sql = "SELECT
            w.worker_name,
            j.job_name,
            a.present
        FROM attendance a
        JOIN workers w ON a.worker_id = w.worker_id
        JOIN jobs j ON a.job_id = j.id
        WHERE a.lead_id = ? AND a.date = ? AND a.user_id = ?;";

$stmt = $conn->prepare($sql);
if (!$stmt) {
    error_log("Prepare statement failed: " . $conn->error);
    echo json_encode(["status" => "error", "message" => "Database error preparing statement"]);
    exit;
}

$stmt->bind_param("isi", $lead_id, $date, $user_id);
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

$attendanceData = [];
$totalPresent = 0;
while ($row = $result->fetch_assoc()) {
    $attendanceData[] = [
        "worker_name" => $row['worker_name'],
        "job_name" => $row['job_name'],
        "present_status" => $row['present'] == 1 ? "Present" : "Absent" 
    ];
    if ($row['present'] == 1) {
        $totalPresent++;
    }
}

echo json_encode(["status" => "success", "attendance" => $attendanceData, "totalPresent" => $totalPresent]);

$stmt->close();
$conn->close();

?>