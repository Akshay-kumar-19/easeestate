<?php

session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized access.']);
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $date_used = $_POST['date_used'];
    $team_lead_id = $_POST['team_lead_id'];
    $fertilizer_name = $_POST['fertilizer_name'];
    $quantity_used = $_POST['quantity_used'];
    $unit = $_POST['unit'];
    $field_location = $_POST['field_location'];

    if (empty($date_used) || empty($team_lead_id) || empty($fertilizer_name) || empty($quantity_used) || !is_numeric($quantity_used) || $quantity_used <= 0 || empty($unit) || empty($field_location)) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid input data. Please check all required fields.']);
        exit();
    }

    $user_id = $_SESSION['user_id'];

    try {
        $conn->begin_transaction();

        $sql_check_stock = "SELECT total_quantity FROM fertilizer_inventory WHERE fertilizer_name = ?";
        $stmt_check_stock = $conn->prepare($sql_check_stock);
        $stmt_check_stock->bind_param("s", $fertilizer_name);
        $stmt_check_stock->execute();
        $result_check_stock = $stmt_check_stock->get_result();

        if ($result_check_stock->num_rows === 0) {
            throw new Exception("Fertilizer name not found in inventory.");
        }
        $inventory_row = $result_check_stock->fetch_assoc();
        $current_stock = $inventory_row['total_quantity'];

        if ($current_stock < $quantity_used) {
            echo json_encode(['status' => 'error', 'message' => 'Cannot assign fertilizer. Available stock for ' . htmlspecialchars($fertilizer_name) . ' is ' . htmlspecialchars($current_stock) . ' ' . htmlspecialchars($unit) . ', which is less than the requested ' . htmlspecialchars($quantity_used) . ' ' . htmlspecialchars($unit) . '.']);
            $conn->close();
            exit();
        }

        $sql_insert_usage = "INSERT INTO fertilizer_usage_history (date_used, lead_id, fertilizer_name, quantity_used, unit, field_location) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt_insert_usage = $conn->prepare($sql_insert_usage);
        $stmt_insert_usage->bind_param("sisdss", $date_used, $team_lead_id, $fertilizer_name, $quantity_used, $unit, $field_location);

        if (!$stmt_insert_usage->execute()) {
            $error_message_usage = $stmt_insert_usage->error;
            $conn->rollback();
            error_log("Error inserting into fertilizer_usage_history: " . $error_message_usage);
            echo json_encode(['status' => 'error', 'message' => 'Error assigning fertilizer (usage history): ' . $error_message_usage]);
            exit();
        } else {
            error_log("Successfully inserted into fertilizer_usage_history for fertilizer: " . $fertilizer_name . ", team lead: " . $team_lead_id);
        }

        $sql_update_inventory = "UPDATE fertilizer_inventory SET total_quantity = total_quantity - ? WHERE fertilizer_name = ?";
        $stmt_update_inventory = $conn->prepare($sql_update_inventory);
        $stmt_update_inventory->bind_param("ds", $quantity_used, $fertilizer_name);

        if (!$stmt_update_inventory->execute()) {
            $error_message_inventory = $stmt_update_inventory->error;
            $conn->rollback();
            error_log("Error updating fertilizer_inventory (decreasing quantity): " . $error_message_inventory);
            echo json_encode(['status' => 'error', 'message' => 'Error updating fertilizer inventory: ' . $error_message_inventory]);
            exit();
        } else {
            error_log("Successfully updated fertilizer_inventory (decreased quantity) for fertilizer: " . $fertilizer_name);
        }

        $conn->commit();
        echo json_encode(['status' => 'success', 'message' => 'Fertilizer assigned successfully.', 'reload' => true]);

    } catch (Exception $e) {
        $conn->rollback();
        error_log("Exception in fertilizer_assign_action.php: " . $e->getMessage());
        echo json_encode(['status' => 'error', 'message' => 'Error assigning fertilizer: ' . $e->getMessage()]);
    } finally {
        $conn->close();
    }

} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
}
?>
