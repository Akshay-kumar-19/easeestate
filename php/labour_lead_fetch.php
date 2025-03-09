<?php
# labour_lead_fetch.php - Backend to Fetch Labour Leads for Dropdown

session_start();
require 'db.php'; // Database connection

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized access.']);
    exit();
}

$action = $_GET['action'] ?? ''; // Get action parameter

if ($action === 'names') {
    // Fetch labour lead names and IDs for dropdown
    $sql = "SELECT lead_id, lead_name FROM labour_lead ORDER BY lead_name"; // Adjust table and column names if needed
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