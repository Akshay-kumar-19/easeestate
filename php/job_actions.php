<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start();
require 'db.php'; // Database connection

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['message' => 'Please log in.']);
    exit();
}

$user_id = $_SESSION['user_id']; // Get logged-in user ID

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];

    if ($action === 'add' || $action === 'update') {
        $job_name = $_POST['job_name'];
        $daily_wage = $_POST['daily_wage'];
        $per_kg_rate = $_POST['per_kg_rate'];
        $overtime_hourly_rate_input = $_POST['overtime_rate']; // Get overtime rate from POST
        $job_id = $_POST['job_id'] ?? null;

        // Ensure job_name is not empty
        if (empty($job_name)) {
            echo json_encode(['message' => 'Job name is required!']);
            exit();
        }

        // Convert empty values to 0 for daily_wage, per_kg_rate, and overtime_hourly_rate
        $daily_wage = ($daily_wage === "" || !is_numeric($daily_wage)) ? 0 : $daily_wage;
        $per_kg_rate = ($per_kg_rate === "" || !is_numeric($per_kg_rate)) ? 0 : $per_kg_rate;
        $overtime_hourly_rate = (!isset($_POST['overtime_rate']) || $_POST['overtime_rate'] === "" || !is_numeric($overtime_hourly_rate_input)) ? 0 : floatval($overtime_hourly_rate_input); // Convert to float


        if ($action === 'add') {
            $sql = "INSERT INTO jobs (user_id, job_name, daily_wage, per_kg_rate, overtime_hourly_rate) VALUES (?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("isddd", $user_id, $job_name, $daily_wage, $per_kg_rate, $overtime_hourly_rate); // Include overtime_hourly_rate
        } elseif ($action === 'update' && !empty($job_id)) {
            $sql = "UPDATE jobs SET job_name = ?, daily_wage = ?, per_kg_rate = ?, overtime_hourly_rate = ? WHERE id = ? AND user_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sddddi", $job_name, $daily_wage, $per_kg_rate, $overtime_hourly_rate, $job_id, $user_id); // Include overtime_hourly_rate
        }

        echo json_encode(['message' => $stmt->execute() ? 'Job saved successfully.' : 'Failed to save job.', 'error' => $stmt->error]);
        exit();
    }
}

// Delete Job (Fix: Ensure job_id is received)
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'delete') {
    $job_id = $_GET['job_id'];

    if (empty($job_id)) {
        echo json_encode(['message' => 'Job ID is required for deletion!']);
        exit();
    }

    $sql = "DELETE FROM jobs WHERE id = ? AND user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $job_id, $user_id);

    echo json_encode(['message' => $stmt->execute() ? 'Job deleted successfully.' : 'Failed to delete job.', 'error' => $stmt->error]);
    exit();
}

// Fetch Jobs (Include overtime_hourly_rate)
$sql = "SELECT id AS job_id, job_name, daily_wage, per_kg_rate, overtime_hourly_rate FROM jobs WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$jobs = [];
while ($row = $result->fetch_assoc()) {
    $jobs[] = $row;
}

echo json_encode($jobs);
?>