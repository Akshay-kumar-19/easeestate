<?php
// assign_fertilizer_action.php

// Database connection details
$servername = "localhost";
$username = "root"; // Replace with your actual database username
$password = "akki";     // Replace with your actual database password
$dbname = "easeestate"; // Replace with your actual database name

// Create database connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die(json_encode(["status" => "error", "message" => "Database connection failed: " . $conn->connect_error]));
}

// Function to reduce fertilizer inventory
function reduceFertilizerInventory($conn, $fertilizerName, $fertilizerType, $quantityKg, $quantityMl, $unit) {
    // Determine quantity and unit columns based on unit
    $quantityColumn = ($unit == 'kg') ? 'quantity_kg' : 'quantity_ml';
    $reduceQuantity = ($unit == 'kg') ? $quantityKg : $quantityMl;

    // *** Enhanced Logging - START ***
    error_log("PHP message: Starting inventory reduction for: Fertilizer Name: " . $fertilizerName . ", Fertilizer Type: " . $fertilizerType . ", Quantity: " . $reduceQuantity . " " . $unit);

    // Fetch current quantity - IMPORTANT: Fetch based on BOTH name and type
    $sql_select = "SELECT fertilizer_id, quantity_kg, quantity_ml, unit FROM fertilizer_inventory WHERE fertilizer_name = ? AND fertilizer_type = ?";
    $stmt_select = $conn->prepare($sql_select);
    $stmt_select->bind_param("ss", $fertilizerName, $fertilizerType);
    $stmt_select->execute();
    $result_select = $stmt_select->get_result();

    if ($result_select->num_rows > 0) {
        while ($row = $result_select->fetch_assoc()) {
            $recordId = $row['fertilizer_id'];
            $currentQuantityKg = $row['quantity_kg'];
            $currentQuantityMl = $row['quantity_ml'];
            $currentUnit = $row['unit'];

            error_log("PHP message:   Processing record ID: " . $recordId . ", Current Quantity (kg): " . $currentQuantityKg . ", Current Quantity (ml): " . $currentQuantityMl . ", Unit: " . $currentUnit);


            if ($unit == 'kg' && $currentUnit == 'kg') {
                $newQuantityKg = max(0, $currentQuantityKg - $quantityKg); // Ensure quantity doesn't go below 0
                 error_log("PHP message:   Reducing kg: " . $quantityKg . " from current kg: " . $currentQuantityKg . ". New kg will be: " . $newQuantityKg);
                $sql_update = "UPDATE fertilizer_inventory SET quantity_kg = ? WHERE fertilizer_id = ?";
                $stmt_update = $conn->prepare($sql_update);
                $stmt_update->bind_param("di", $newQuantityKg, $recordId);
            } else if ($unit == 'ml' && $currentUnit == 'ml') {
                $newQuantityMl = max(0, $currentQuantityMl - $quantityMl); // Ensure quantity doesn't go below 0
                error_log("PHP message:   Reducing ml: " . $quantityMl . " from current ml: " . $currentQuantityMl . ". New ml will be: " . $newQuantityMl);
                $sql_update = "UPDATE fertilizer_inventory SET quantity_ml = ? WHERE fertilizer_id = ?";
                $stmt_update = $conn->prepare($sql_update);
                $stmt_update->bind_param("di", $newQuantityMl, $recordId);
            } else {
                error_log("PHP message:   Warning: Unit mismatch or unit not kg/ml for record ID: " . $recordId . ". Expected unit: " . $unit . ", Current unit: " . $currentUnit);
                continue; // Skip to the next record if units don't match or are invalid
            }


            if ($stmt_update) {
                $update_result = $stmt_update->execute(); // Execute the update
                if ($update_result) {
                    error_log("PHP message:   Successfully reduced quantity for record ID: " . $recordId);
                } else {
                    error_log("PHP message:   Error reducing inventory quantity for record ID: " . $recordId . ". Error: " . $stmt_update->error);
                }
                $stmt_update->close();
            } else {
                 error_log("PHP message:   Error: Prepare statement for update failed for record ID: " . $recordId . ". Error: " . $conn->error);
            }
        }
         error_log("PHP message:   Finished reducing required quantity.");
         return true; // Indicate successful reduction (even if some records had issues)

    } else {
        error_log("PHP message:   Error: No matching fertilizer found in inventory for Name: " . $fertilizerName . ", Type: " . $fertilizerType);
        return false; // No matching fertilizer found
    }
    $stmt_select->close();
}

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get data from the form
    $fertilizerName = $_POST["fertilizer_name"];
    $fertilizerType = $_POST["fertilizer_type"];
    $quantityKg = $_POST["quantity_kg"];
    $quantityMl = $_POST["quantity_ml"];
    $unit = $_POST["unit"];
    $labourLeadId = $_POST["labour_lead_id"];
    $assignmentDate = $_POST["assignment_date"];

    // Validate input data (add more validation as needed)
    if (empty($fertilizerName) || empty($fertilizerType) || (empty($quantityKg) && empty($quantityMl)) || empty($unit) || empty($labourLeadId) || empty($assignmentDate)) {
        echo json_encode(["status" => "error", "message" => "All fields are required."]);
        $conn->close();
        exit();
    }

    // Insert data into fertilizer_assignment table
    $sql_assignment = "INSERT INTO fertilizer_assignment (fertilizer_name, fertilizer_type, quantity_assigned_kg, quantity_assigned_ml, unit, labour_lead_id, assignment_date, user_id) VALUES (?, ?, ?, ?, ?, ?, ?, 1)";
    $stmt_assignment = $conn->prepare($sql_assignment);
    $stmt_assignment->bind_param("sssdsis", $fertilizerName, $fertilizerType, $quantityKg, $quantityMl, $unit, $labourLeadId, $assignmentDate);

    if ($stmt_assignment->execute()) {
        // Attempt to reduce inventory
        if (reduceFertilizerInventory($conn, $fertilizerName, $fertilizerType, $quantityKg, $quantityMl, $unit)) {
             echo json_encode(["status" => "success", "message" => "Fertilizer assigned successfully and inventory updated."]);
        } else {
             echo json_encode(["status" => "warning", "message" => "Fertilizer assigned successfully, but inventory reduction may have failed. Check error logs for details."]);
        }


    } else {
        echo json_encode(["status" => "error", "message" => "Error assigning fertilizer: " . $stmt_assignment->error]);
    }

    $stmt_assignment->close();
} else {
    echo json_encode(["status" => "error", "message" => "Invalid request or action"]);
}

$conn->close();
?>