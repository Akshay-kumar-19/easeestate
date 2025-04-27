<?php
require 'db.php';
session_start();
header('Content-Type: application/json');

$user_id = $_SESSION['user_id'] ?? null;
if (!$user_id) {
    echo json_encode(["status" => "error", "message" => "User not authenticated"]);
    exit;
}

$date = $_GET['date'] ?? null;

if (!$date) {
    echo json_encode(["status" => "error", "message" => "Date is required"]);
    exit;
}

$sql = "SELECT
            w.worker_name,
            ll.lead_name,
            j.job_name,
            o.overtime_hours
        FROM overtime o
        JOIN workers w ON o.worker_id = w.worker_id
        JOIN labour_lead ll ON w.lead_id = ll.lead_id
        JOIN jobs j ON o.job_id = j.id
        WHERE o.date = ? AND o.user_id = ?;";

$stmt = $conn->prepare($sql);
if (!$stmt) {
    error_log("Prepare statement failed: " . $conn->error);
    echo json_encode(["status" => "error", "message" => "Database error preparing statement"]);
    exit;
}

$stmt->bind_param("si", $date, $user_id);
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

$overtimeRecords = [];
$totalWorkersOvertime = 0;
$totalOvertimeHours = 0;
while ($row = $result->fetch_assoc()) {
    $overtimeRecords[] = [
        "worker_name" => $row['worker_name'],
        "lead_name" => $row['lead_name'],
        "job_name" => $row['job_name'],
        "total_overtime_hours" => $row['overtime_hours']
    ];
    $totalWorkersOvertime++;
    $totalOvertimeHours += $row['overtime_hours'];
}

echo json_encode([
    "status" => "success",
    "overtimeRecords" => $overtimeRecords,
    "totalWorkersOvertime" => $totalWorkersOvertime,
    "totalOvertimeHours" => $totalOvertimeHours
]);

$stmt->close();
$conn->close();

?>