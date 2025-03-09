<?php
# fertilizer_inventory_fetch.php - Backend to Fetch Fertilizer Inventory Data

require 'db.php';

$action = $_GET['action'] ?? ''; // Get action type from GET request

if ($action == 'names') {
    // Fetch only fertilizer names for dropdown
    $fertilizerNames = [];
    try {
        $sql = "SELECT fertilizer_name FROM fertilizer_inventory";
        $result = $conn->query($sql);
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $fertilizerNames[] = ['fertilizer_name' => $row['fertilizer_name']];
            }
        }
        echo json_encode($fertilizerNames);
    } catch (Exception $e) {
        error_log("Error fetching fertilizer names: " . $e->getMessage());
        echo json_encode([]); // Return empty array in case of error
    } finally {
        $conn->close();
    }


} elseif ($action == 'all') {
    // Fetch all inventory data (if needed later)
    $inventoryData = [];
    // ... (Implementation for fetching all inventory data - you can add this later if needed) ...
    echo json_encode($inventoryData);

} else {
    echo json_encode(['error' => 'Invalid action']);
}
?>