<?php

session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized access.']);
    exit();
}

$action = $_GET['action'] ?? '';

if ($action === 'names') {
    $sql = "SELECT lead_id, lead_name FROM labour_lead ORDER BY lead_name";
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid action.']);
    exit;
}


$result = $conn->query($sql);
if ($result) {
    $labour_leads = [];
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $labour_leads[] = $row;
        }
    }
    echo json_encode($labour_leads);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Error fetching labour leads: ' . $conn->error]);
}

$conn->close();
?>