<?php

session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized access.']);
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $fertilizer_name_raw = $_POST['fertilizer_name'];
    $unit = $_POST['unit'];
    $quantity_added = $_POST['quantity'];

    $fertilizer_name = preg_replace('/[^a-zA-Z0-9\s]/', '', trim($fertilizer_name_raw));

    if (empty($fertilizer_name) || empty($unit) || !is_numeric($quantity_added) || $quantity_added <= 0) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid input data. Please check required fields. Fertilizer name should not contain special characters.']);
        exit();
    }

    $user_id = $_SESSION['user_id'];
    $purchase_date = date('Y-m-d');

    try {
        $conn->begin_transaction();

        $sql_check_inventory = "SELECT fertilizer_name FROM fertilizer_inventory WHERE fertilizer_name = ?";
        $stmt_check_inventory = $conn->prepare($sql_check_inventory);
        $stmt_check_inventory->bind_param("s", $fertilizer_name);
        $stmt_check_inventory->execute();
        $stmt_check_inventory->store_result();

        if ($stmt_check_inventory->num_rows > 0) {
            $sql_update_inventory = "UPDATE fertilizer_inventory SET total_quantity = total_quantity + ? WHERE fertilizer_name = ?";
            $stmt_update_inventory = $conn->prepare($sql_update_inventory);
            $stmt_update_inventory->bind_param("ds", $quantity_added, $fertilizer_name);

            if (!$stmt_update_inventory->execute()) {
                $error_message_inventory = $stmt_update_inventory->error;
                $conn->rollback();
                error_log("Error updating fertilizer_inventory: " . $error_message_inventory);
                echo json_encode(['status' => 'error', 'message' => 'Error updating fertilizer inventory: ' . $error_message_inventory]);
                exit();
            } else {
                error_log("Successfully updated fertilizer_inventory for: " . $fertilizer_name);
            }
            $stmt_check_inventory->close();

        } else {
            $stmt_check_inventory->close();
            $sql_insert_inventory = "INSERT INTO fertilizer_inventory (fertilizer_name, unit, total_quantity) VALUES (?, ?, ?)";
            $stmt_insert_inventory = $conn->prepare($sql_insert_inventory);
            $stmt_insert_inventory->bind_param("ssd", $fertilizer_name, $unit, $quantity_added);


            if (!$stmt_insert_inventory->execute()) {
                $error_message_inventory = $stmt_insert_inventory->error;
                $conn->rollback();
                error_log("Error inserting into fertilizer_inventory: " . $error_message_inventory);
                echo json_encode(['status' => 'error', 'message' => 'Error adding to fertilizer inventory: ' . $error_message_inventory]);
                exit();
            } else {
                error_log("Successfully inserted into fertilizer_inventory: " . $fertilizer_name);
            }
        }


        $sql_purchase_history = "INSERT INTO fertilizer_purchase_history (purchase_date, fertilizer_name, quantity_purchased, unit, user_id) VALUES (?, ?, ?, ?, ?)";
        $stmt_purchase_history = $conn->prepare($sql_purchase_history);
        $stmt_purchase_history->bind_param("ssdsi", $purchase_date, $fertilizer_name, $quantity_added, $unit, $user_id);


        if (!$stmt_purchase_history->execute()) {
            $error_message_purchase = $stmt_purchase_history->error;
            $conn->rollback();
            error_log("Error inserting into fertilizer_purchase_history: " . $error_message_purchase);
            echo json_encode(['status' => 'error', 'message' => 'Error recording purchase history: ' . $error_message_purchase]);
            exit();
        } else {
            error_log("Successfully inserted into fertilizer_purchase_history for fertilizer: " . $fertilizer_name);
        }


        $conn->commit();
        echo json_encode(['status' => 'success', 'message' => 'Fertilizer inventory updated successfully.']);

    } catch (Exception $e) {
        $conn->rollback();
        error_log("Exception in fertilizer_add_action.php: " . $e->getMessage());
        echo json_encode(['status' => 'error', 'message' => 'Error updating fertilizer inventory: ' . $e->getMessage()]);
    } finally {
        $conn->close();
    }

} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
}
?>