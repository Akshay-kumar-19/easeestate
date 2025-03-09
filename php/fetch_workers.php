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
$job_id = $_GET['job_id'] ?? null; // Get job_id if provided, otherwise null

$sql = "SELECT w.worker_id, w.worker_name
         FROM workers w
         WHERE w.lead_id = ? AND w.user_id = ?";

if ($job_id !== null && !empty($job_id)) {
    // Add job_id filtering if job_id is provided and not empty.
    // Adapt this condition based on your actual database schema and how workers are related to jobs.
    // The example assumes there is a direct or indirect relation you can filter on, e.g., through a job_assignments table.
    // For this example, I'm adding a placeholder comment, you might need to join tables or adjust the WHERE clause.
    // Example placeholder:  AND EXISTS (SELECT 1 FROM job_assignments ja WHERE ja.worker_id = w.worker_id AND ja.job_id = ?)

    // Modified SQL to include optional job_id filtering (adapt based on your DB schema)
    $sql = "SELECT w.worker_id, w.worker_name
            FROM workers w
            /*
            -- Example if workers are directly linked to jobs through a 'worker_jobs' table:
            INNER JOIN worker_jobs wj ON w.worker_id = wj.worker_id AND wj.job_id = ?
            -- Example if jobs are related through labour leads and you want to filter workers under the lead AND job:
            -- (No direct worker-job relation assumed, filtering workers under the lead and assuming job context is within the lead)
            */
            WHERE w.lead_id = ? AND w.user_id = ?";


    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        error_log("Prepare statement failed: " . $conn->error);
        echo json_encode(["status" => "error", "message" => "Database error preparing statement"]);
        exit;
    }

    // Bind parameters - adjust binding based on the actual SQL used.
    // Example if using INNER JOIN worker_jobs (adjust types if needed, assuming job_id is integer 'i')
    // $stmt->bind_param("iii", $job_id, $lead_id, $user_id);

    // Example if NO explicit job filtering in SQL, but still want to use job_id in fetch_workers for other purposes later:
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
        "present" => false // Default present status
    ];
}

// Ensure proper JSON encoding
echo json_encode(["status" => "success", "workers" => $workers], JSON_UNESCAPED_UNICODE);

$stmt->close();
$conn->close(); //Consider keeping connection open if this script is called frequently and connection overhead is significant.
?>