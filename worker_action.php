<?php
session_start();
require 'db.php';
header('Content-Type: application/json');

$response = ["success" => false, "message" => "Invalid request."];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $user_id = $_SESSION['user_id'] ?? null;

    if (!$user_id) {
        $response = ["success" => false, "message" => "Unauthorized access."];
        echo json_encode($response);
        exit();
    }

    if ($action === 'add') {
        $lead_id = $_POST['lead_id'] ?? null;
        $worker_name = $_POST['worker_name'] ?? '';

        if ($lead_id && !empty($worker_name)) {
            $validate_lead_query = "SELECT lead_id FROM labour_lead WHERE lead_id = ? AND user_id = ?";
            $stmt = $conn->prepare($validate_lead_query);
            $stmt->bind_param("ii", $lead_id, $user_id);
            $stmt->execute();
            $stmt->store_result();

            if ($stmt->num_rows > 0) {
                $query = "INSERT INTO workers (lead_id, worker_name, user_id) VALUES (?, ?, ?)";
                $stmt = $conn->prepare($query);
                $stmt->bind_param("isi", $lead_id, $worker_name, $user_id);

                if ($stmt->execute()) {
                    $response = ["success" => true, "message" => "Worker added successfully."];
                } else {
                    $response = ["success" => false, "message" => "Error adding worker."];
                }
            } else {
                $response = ["success" => false, "message" => "Invalid labour lead."];
            }
        } else {
            $response = ["success" => false, "message" => "Missing lead ID or worker name."];
        }
    }

    if ($action === 'delete') {
        $worker_id = $_POST['worker_id'] ?? null;

        if ($worker_id) {
            $validate_worker_query = "SELECT worker_id FROM workers WHERE worker_id = ? AND user_id = ?";
            $stmt = $conn->prepare($validate_worker_query);
            $stmt->bind_param("ii", $worker_id, $user_id);
            $stmt->execute();
            $stmt->store_result();

            if ($stmt->num_rows > 0) {
                $query = "DELETE FROM workers WHERE worker_id = ?";
                $stmt = $conn->prepare($query);
                $stmt->bind_param("i", $worker_id);

                if ($stmt->execute()) {
                    $response = ["success" => true, "message" => "Worker deleted successfully."];
                } else {
                    $response = ["success" => false, "message" => "Error deleting worker."];
                }
            } else {
                $response = ["success" => false, "message" => "Invalid worker ID."];
            }
        } else {
            $response = ["success" => false, "message" => "Missing worker ID."];
        }
    }
}

echo json_encode($response);