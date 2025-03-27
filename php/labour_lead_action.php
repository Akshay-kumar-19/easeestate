<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'User not logged in']);
    exit();
}

$user_id = $_SESSION['user_id'];

$action = isset($_POST['action']) ? $_POST['action'] : '';

switch ($action) {
    case 'add':
        $lead_name = isset($_POST['lead_name']) ? $_POST['lead_name'] : '';
        if ($lead_name) {
            $sql = "INSERT INTO labour_lead (user_id, lead_name) VALUES (?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("is", $user_id, $lead_name);
            if ($stmt->execute()) {
                echo json_encode(['message' => 'Labour lead added successfully.']);
            } else {
                echo json_encode(['error' => 'Error adding labour lead.']);
            }
        } else {
            echo json_encode(['error' => 'Labour lead name is required.']);
        }
        break;

    case 'delete':
        $lead_name = isset($_POST['lead_name']) ? $_POST['lead_name'] : '';
        if ($lead_name) {
            $sql = "DELETE FROM labour_lead WHERE user_id = ? AND lead_name = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("is", $user_id, $lead_name);
            if ($stmt->execute()) {
                echo json_encode(['message' => 'Labour lead deleted successfully.']);
            } else {
                echo json_encode(['error' => 'Error deleting labour lead.']);
            }
        } else {
            echo json_encode(['error' => 'Labour lead name is required.']);
        }
        break;

    case 'fetch':
        $sql = "SELECT lead_name FROM labour_lead WHERE user_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $leads = [];
        while ($row = $result->fetch_assoc()) {
            $leads[] = $row;
        }

        echo json_encode($leads);
        break;

    default:
        echo json_encode(['error' => 'Invalid action.']);
        break;
}
