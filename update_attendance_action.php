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
error_log("Received JSON Data: " . print_r($data, true)); 

$action_type = $data['action_type'] ?? 'batch_update'; 

if ($action_type === 'individual_update') {
    
    $worker_id = $data['worker_id'] ?? null;
    $lead_id = $data['lead_id'] ?? null;
    $present = isset($data['present']) ? intval($data['present']) : null; 
    $job_id = $data['job_id'] ?? null;

    if (!$worker_id || !$lead_id || $present === null || !$job_id) {
        error_log("Invalid data for individual update: " . print_r($data, true));
        echo json_encode(["status" => "error", "message" => "Invalid data format for individual update"]);
        exit;
    }

    $date = date('Y-m-d');
    $job_role = 'N/A'; 

    $sql = "INSERT INTO attendance (worker_id, lead_id, job_id, user_id, date, job_role, present)
            VALUES (?, ?, ?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE present = VALUES(present), job_id = VALUES(job_id), job_role = VALUES(job_role)";

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        error_log("Prepare failed for individual update: " . $conn->error);
        echo json_encode(["status" => "error", "message" => "Database error (prepare failed)"]);
        exit;
    }

    $stmt->bind_param("iiiissi", $worker_id, $lead_id, $job_id, $user_id, $date, $job_role, $present);

    if (!$stmt->execute()) {
        error_log("Execution failed for individual update: " . $stmt->error);
        echo json_encode(["status" => "error", "message" => "Database error (execution failed)"]);
        exit;
    }
    $stmt->close();

    echo json_encode(["status" => "success", "message" => "Attendance updated for worker."]);


} elseif ($action_type === 'batch_update') {
    
    if (!$data || empty($data['lead_id']) || empty($data['attendance'])) {
        error_log("Invalid JSON received for batch update: " . print_r($inputData, true));
        echo json_encode(["status" => "error", "message" => "Invalid data format: Missing lead_id or attendance data"]);
        exit;
    }

    $lead_id = $data['lead_id'];
    $errors = [];
    $date = date('Y-m-d');


    $checkSql = "SELECT COUNT(*) FROM attendance WHERE lead_id = ? AND date = ?";
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
    $attendanceCount = $checkResult->fetch_array()[0];
    $checkStmt->close();

    if ($attendanceCount > 0) {
        echo json_encode(["status" => "error", "message" => "Attendance for Labour Lead ID " . $lead_id . " has already been submitted for today."]);
        exit;
    }


    foreach ($data['attendance'] as $entry) {
        if (!isset($entry['worker_id']) || !isset($entry['present']) || !isset($entry['job_id'])) {
            error_log("Missing worker_id, present status, or job_id in attendance entry: " . print_r($entry, true));
            continue;
        }

        $worker_id = $entry['worker_id'];
        $present = $entry['present'];
        $job_id = $entry['job_id'];
        $job_role = 'N/A';

        $sql = "INSERT INTO attendance (worker_id, lead_id, job_id, user_id, date, job_role, present)
                VALUES (?, ?, ?, ?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE present = VALUES(present), job_id = VALUES(job_id), job_role = VALUES(job_role)";

        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            error_log("Prepare failed: " . $conn->error);
            echo json_encode(["status" => "error", "message" => "Database error (prepare failed)"]);
            exit;
        }

        $stmt->bind_param("iiiissi", $worker_id, $lead_id, $job_id, $user_id, $date, $job_role, $present);

        if (!$stmt->execute()) {
            error_log("Execution failed: " . $stmt->error);
            $errors[] = "Failed to update worker ID $worker_id";
        }
        $stmt->close();
    }

    if (!empty($errors)) {
        echo json_encode(["status" => "error", "message" => implode(", ", $errors)]);
    } else {
        echo json_encode(["status" => "success", "message" => "Attendance updated successfully."]);
    }

} else {
    echo json_encode(["status" => "error", "message" => "Invalid action type."]);
}


$conn->close();

?>