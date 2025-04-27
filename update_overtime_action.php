<?php
require 'db.php'; 
session_start();
header('Content-Type: application/json');


ini_set('log_errors', 1);
ini_set('error_log', 'php_errors.log');
ini_set('display_errors', 0);
error_reporting(E_ALL);

$user_id = $_SESSION['user_id'] ?? null;

if (!$user_id) {
    echo json_encode(["status" => "error", "message" => "User not authenticated"]);
    exit;
}


$inputData = file_get_contents("php://input");
$data = json_decode($inputData, true);

if (!$data || empty($data['lead_id']) || empty($data['date']) || empty($data['overtime'])) {
    error_log("Invalid JSON received: " . print_r($inputData, true));
    echo json_encode(["status" => "error", "message" => "Invalid data format: Missing lead_id, date, or overtime data"]);
    exit;
}

$lead_id = $data['lead_id'];
$date = $data['date'];
$overtime_entries = $data['overtime'];
$errors = [];


if (date('Y-m-d', strtotime($date)) !== $date) {
    echo json_encode(["status" => "error", "message" => "Invalid date format. Please use YYYY-MM-DD."]);
    exit;
}


$checkSql = "SELECT COUNT(*) FROM overtime WHERE lead_id = ? AND date = ?";
$checkStmt = $conn->prepare($checkSql);

if (!$checkStmt) {
    error_log("Prepare check statement failed: " . $conn->error);
    echo json_encode(["status" => "error", "message" => "Database error (check prepare failed)"]);
    exit;
}

$checkStmt->bind_param("ii", $lead_id, $date);
if (!$checkStmt->execute()) {
    error_log("Execute check failed: " . $checkStmt->error);
    echo json_encode(["status" => "error", "message" => "Database error (check execute failed)"]);
    exit;
}

$checkResult = $checkStmt->get_result();
$overtimeCount = $checkResult->fetch_array()[0];
$checkStmt->close();

if ($overtimeCount > 0) {
    echo json_encode(["status" => "error", "message" => "Overtime for Labour Lead ID " . $lead_id . " has already been submitted for this date."]);
    exit; 
}


foreach ($overtime_entries as $entry) {
    if (!isset($entry['worker_id']) || !isset($entry['job_id']) || !isset($entry['overtime_hours'])) {
        error_log("Missing worker_id, job_id, or overtime_hours in overtime entry: " . print_r($entry, true));
        $errors[] = "Invalid overtime data for a worker.";
        continue; 
    }

    $worker_id = $entry['worker_id'];
    $job_id = $entry['job_id'];
    $overtime_hours = floatval($entry['overtime_hours']); 

    if (is_nan($overtime_hours) || $overtime_hours < 0) {
        error_log("Invalid overtime hours value: " . print_r($entry, true));
        $errors[] = "Invalid overtime hours for worker ID " . $worker_id;
        continue;
    }


    $sql = "INSERT INTO overtime (worker_id, lead_id, job_id, user_id, date, overtime_hours)
            VALUES (?, ?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE overtime_hours = VALUES(overtime_hours), job_id = VALUES(job_id)"; 

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        error_log("Prepare failed: " . $conn->error);
        echo json_encode(["status" => "error", "message" => "Database error (prepare failed)"]);
        exit;
    }

    $stmt->bind_param("iiiisd", $worker_id, $lead_id, $job_id, $user_id, $date, $overtime_hours); 

    if (!$stmt->execute()) {
        error_log("Execution failed: " . $stmt->error . " SQL: " . $sql . " Params: " . json_encode([$worker_id, $lead_id, $job_id, $user_id, $date, $overtime_hours]));
        $errors[] = "Failed to update overtime for worker ID " . $worker_id;
    }
    $stmt->close();
}

if (!empty($errors)) {
    echo json_encode(["status" => "error", "message" => "Overtime update failed with errors: " . implode(", ", $errors)]);
} else {
    echo json_encode(["status" => "success", "message" => "Overtime updated successfully."]);
}
$conn->close();
?>