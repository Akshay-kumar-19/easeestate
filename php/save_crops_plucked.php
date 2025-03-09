<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/php-error.log');

require 'db.php';
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

$request_body = file_get_contents("php://input");
$data = json_decode($request_body, true);

if ($data === null && json_last_error() !== JSON_ERROR_NONE) {
    $json_error_message = "JSON Decode Error: " . json_last_error_msg();
    error_log($json_error_message . " - Request Body: " . $request_body);
    echo json_encode(['status' => 'error', 'message' => 'Failed to parse JSON data. ' . $json_error_message]);
    exit;
}

$crop_type = $data['crop_type'] ?? null;
$plucked_date = $data['plucked_date'] ?? null;
$salary_calculation_type = $data['salary_calculation_type'] ?? null;
$per_kg_rate = $data['per_kg_rate'] ?? null;
$daily_wage_rate = $data['daily_wage_rate'] ?? null;
$plucked_data = $data['plucked_data'] ?? null;

if (!is_array($plucked_data)) {
    echo json_encode(['status' => 'error', 'message' => 'Plucked data is not in the correct format.']);
    exit;
}

try {
    $conn->begin_transaction();

    foreach ($plucked_data as $record) {
        $worker_id = $record['worker_id'] ?? null;
        $ripe_kg = $record['ripe_kg'] ?? null;
        $unripe_kg = $record['unripe_kg'] ?? null;
        $total_kg = $record['total_kg'] ?? null;
        $kone_count = $record['kone_count'] ?? null;

        $lead_id_stmt = $conn->prepare("SELECT lead_id FROM workers WHERE worker_id = ? AND user_id = ?");
        $lead_id_stmt->bind_param("ii", $worker_id, $user_id);
        $lead_id_stmt->execute();
        $lead_id_result = $lead_id_stmt->get_result();
        $worker_lead = $lead_id_result->fetch_assoc();
        $lead_id = $worker_lead ? $worker_lead['lead_id'] : null;
        $lead_id_stmt->close();

        if ($lead_id === null) {
            throw new Exception("Lead ID not found for worker ID: " . $worker_id);
        }

        $sql = "";
        $stmt = null;

        if ($salary_calculation_type === 'per_kg') {
            $sql = "INSERT INTO crops_plucked (user_id, worker_id, lead_id, job_id, plucked_date, ripe_kg, unripe_kg, total_kg, kone_count, per_kg_rate, salary_calculation_type)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("iiiisddddds", $user_id, $worker_id, $lead_id, $crop_type, $plucked_date, $ripe_kg, $unripe_kg, $total_kg, $kone_count, $per_kg_rate, $salary_calculation_type);
        } elseif ($salary_calculation_type === 'daily_wage') {
            $sql = "INSERT INTO crops_plucked (user_id, worker_id, lead_id, job_id, plucked_date, ripe_kg, unripe_kg, total_kg, kone_count, daily_wage, salary_calculation_type)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("iiiisddddds", $user_id, $worker_id, $lead_id, $crop_type, $plucked_date, $ripe_kg, $unripe_kg, $total_kg, $kone_count, $daily_wage_rate, $salary_calculation_type);
        } else {
            throw new Exception("Invalid salary calculation type: " . $salary_calculation_type);
        }


        if (!$stmt->execute()) {
            throw new Exception($stmt->error);
        }
        $stmt->close();
    }

    $conn->commit();
    echo json_encode(['status' => 'success', 'message' => 'Data saved successfully']);

} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}

$conn->close();
?>