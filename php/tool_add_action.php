<?php
# tool_add_action.php - Backend to add and edit tool types

session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized access.']);
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $toolName = $_POST['tool_name'] ?? '';
    $toolQuantity = $_POST['tool_quantity'] ?? 1; // Default quantity to 1 if not provided
    $toolId = $_POST['tool_id'] ?? null; // Tool ID for edit, null for add

    // Server-side validation for Tool Name (Alphanumeric and spaces only)
    if (empty($toolName)) {
        echo json_encode(['status' => 'error', 'message' => 'Tool name is required.']);
        exit();
    }
    if (!preg_match('/^[a-zA-Z\s\'\-]+$/', $toolName)) { // Regex for letters, spaces, hyphens, apostrophes
        echo json_encode(['status' => 'error', 'message' => 'Tool name can only contain letters, spaces, hyphens, and apostrophes.']);
        exit();
    }

    $user_id = $_SESSION['user_id'];

    try {
        $conn->begin_transaction(); // Start transaction for atomicity

        if ($toolId) { // Edit existing tool
            // 1. Update tools table (only tool_name is editable in this version)
            $sqlTool = "UPDATE tools SET tool_name = ? WHERE tool_id = ? AND user_id = ?";
            $stmtTool = $conn->prepare($sqlTool);
            $stmtTool->bind_param("sii", $toolName, $toolId, $user_id);
            if (!$stmtTool->execute()) {
                throw new Exception("Error updating tool type: " . $stmtTool->error);
            }
            $stmtTool->close();
            $message = 'Tool updated successfully.';

        } else { // Add new tool
            // 1. Insert into tools table (tool type) - Include tool_quantity in INSERT
            $sqlTool = "INSERT INTO tools (tool_name, user_id, tool_quantity) VALUES (?, ?, ?)";
            $stmtTool = $conn->prepare($sqlTool);
            $stmtTool->bind_param("sii", $toolName, $user_id, $toolQuantity); // Include quantity
            if (!$stmtTool->execute()) {
                throw new Exception("Error adding tool type: " . $stmtTool->error);
            }
            $toolId = $conn->insert_id; // Get the last inserted tool_id
            $stmtTool->close();

            // 2. Insert into tool_inventory table (initial inventory)
            if ($toolQuantity > 0) {
                $sqlInventory = "INSERT INTO tool_inventory (tool_id, status, user_id) VALUES (?, 'available', ?)";
                $stmtInventory = $conn->prepare($sqlInventory);
                for ($i = 0; $i < $toolQuantity; $i++) { // Insert each tool individually
                    $stmtInventory->bind_param("ii", $toolId, $user_id);
                    if (!$stmtInventory->execute()) {
                        throw new Exception("Error adding to tool inventory: " . $stmtInventory->error);
                    }
                }
                $stmtInventory->close();
            }
            $message = 'Tool added successfully.';
        }

        $conn->commit(); // Commit transaction if all operations are successful

        // Fetch updated tool list for response (now fetches quantity as well)
        $updatedToolList = [];
        $sqlSelectTools = "SELECT
                                    t.tool_id,
                                    t.tool_name,
                                    t.tool_quantity,
                                    COUNT(ti.inventory_id) AS quantity
                                FROM tools t
                                LEFT JOIN tool_inventory ti ON t.tool_id = ti.tool_id
                                WHERE t.user_id = ?
                                GROUP BY t.tool_id, t.tool_name, t.tool_quantity
                                ORDER BY t.tool_name"; // Include tool_quantity in GROUP BY and SELECT
        $stmtSelect = $conn->prepare($sqlSelectTools);
        $stmtSelect->bind_param("i", $user_id);
        $stmtSelect->execute();
        $resultTools = $stmtSelect->get_result();
        if ($resultTools->num_rows > 0) {
            while ($row = $resultTools->fetch_assoc()) {
                $updatedToolList[] = $row;
            }
        }
        $stmtSelect->close();

        echo json_encode(['status' => 'success', 'message' => $message, 'tools' => $updatedToolList]);

    } catch (Exception $e) {
        $conn->rollback(); // Rollback transaction on error
        error_log("Transaction error saving tool: " . $e->getMessage());
        echo json_encode(['status' => 'error', 'message' => 'Error saving tool: ' . $e->getMessage()]);
    } finally {
        $conn->close(); // Close connection
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
}
?>