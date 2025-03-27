<?php

require 'db.php';

$action = $_GET['action'] ?? '';

if ($action == 'names') {
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
        echo json_encode([]);
    } finally {
        $conn->close();
    }


} elseif ($action == 'all') {
    $inventoryData = [];
    echo json_encode($inventoryData);

} else {
    echo json_encode(['error' => 'Invalid action']);
}
?>