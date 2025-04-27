<?php
require 'db.php';
session_start();
header('Content-Type: application/json');

if (!isset($_GET['job_id']) || !isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Missing job ID or user ID.']);
    exit;
}

$job_id = $_GET['job_id'];
$user_id = $_SESSION['user_id'];

try {
    $stmt = $conn->prepare("SELECT w.worker_id, w.worker_name, ll.lead_name, ll.lead_id, j.per_kg_rate, j.daily_wage
                                FROM workers w
                                INNER JOIN labour_lead ll ON w.lead_id = ll.lead_id
                                INNER JOIN attendance a ON w.worker_id = a.worker_id AND ll.lead_id = a.lead_id
                                INNER JOIN jobs j ON a.job_id = j.id AND a.user_id = j.user_id
                                WHERE w.user_id = ? AND a.job_id = ? AND a.date = CURDATE() AND a.present = 1");
    $stmt->bind_param("ii", $user_id, $job_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $workers = $result->fetch_all(MYSQLI_ASSOC);

    $job_rates_stmt = $conn->prepare("SELECT per_kg_rate, daily_wage FROM jobs WHERE id = ? AND user_id = ?");
    $job_rates_stmt->bind_param("ii", $job_id, $user_id);
    $job_rates_stmt->execute();
    $job_rates_result = $job_rates_stmt->get_result();
    $job_rates = $job_rates_result->fetch_assoc();
    $per_kg_rate = $job_rates ? $job_rates['per_kg_rate'] : 0;
    $daily_wage = $job_rates ? $job_rates['daily_wage'] : 0;
    $job_rates_stmt->close();


    if ($workers) {
        echo json_encode(['status' => 'success', 'workers' => $workers, 'per_kg_rate' => $per_kg_rate, 'daily_wage' => $daily_wage]);
    } else {
        echo json_encode(['status' => 'success', 'workers' => [], 'per_kg_rate' => $per_kg_rate, 'daily_wage' => $daily_wage]);
    }

    $stmt->close();
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}

$conn->close();
?>