<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    exit("Unauthorized");
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $action = $_POST['action'] ?? '';

    switch ($action) {
        case 'add':
        case 'edit':
            $fertilizerId = $_POST['fertilizer_id'] ?? null;
            $fertilizerName = $_POST['fertilizer_name'];
            $fertilizerType = $_POST['fertilizer_type'];
            $quantity = $_POST['quantity'];
            $unit = $_POST['unit'];
            $purchaseDate = $_POST['purchase_date'];
            $user_id = $_SESSION['user_id'];

            $quantity_kg = null;
            $quantity_ml = null;

            if ($unit == 'kg') {
                $quantity_kg = $quantity;
            } elseif ($unit == 'ml') {
                $quantity_ml = $quantity;
            }

            if ($action === 'edit') {
                $sql = "UPDATE fertilizer_inventory SET
                        fertilizer_name = ?,
                        fertilizer_type = ?,
                        quantity_kg = ?,
                        quantity_ml = ?,
                        unit = ?,
                        purchase_date = ?
                        WHERE fertilizer_id = ?";

                $stmt = $conn->prepare($sql);
                $stmt->bind_param("ssssssi", $fertilizerName, $fertilizerType, $quantity_kg, $quantity_ml, $unit, $purchaseDate, $fertilizerId);

                if ($stmt->execute()) {
                    echo json_encode(['status' => 'success', 'message' => 'Fertilizer updated successfully']);
                } else {
                    echo json_encode(['status' => 'error', 'message' => 'Error updating fertilizer: ' . $stmt->error]);
                }
            } else {
                $sql = "INSERT INTO fertilizer_inventory (fertilizer_name, fertilizer_type, quantity_kg, quantity_ml, unit, purchase_date, user_id)
                        VALUES (?, ?, ?, ?, ?, ?, ?)";

                $stmt = $conn->prepare($sql);
                $stmt->bind_param("ssssssi", $fertilizerName, $fertilizerType, $quantity_kg, $quantity_ml, $unit, $purchaseDate, $user_id);

                if ($stmt->execute()) {
                    echo json_encode(['status' => 'success', 'message' => 'Fertilizer added successfully']);
                } else {
                    echo json_encode(['status' => 'error', 'message' => 'Error adding fertilizer: ' . $stmt->error]);
                }
            }
            $stmt->close();
            break;

        case 'delete':
            $fertilizerId = $_POST['fertilizer_id'];

            $sql = "DELETE FROM fertilizer_inventory WHERE fertilizer_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $fertilizerId);

            if ($stmt->execute()) {
                echo json_encode(['status' => 'success', 'message' => 'Fertilizer deleted successfully']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Error deleting fertilizer: ' . $stmt->error]);
            }
            $stmt->close();
            break;

        case 'fetch_inventory':
            $user_id = $_SESSION['user_id'];

            $sql = "SELECT * FROM fertilizer_inventory WHERE user_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $result = $stmt->get_result();

            $output = '';
            while ($row = $result->fetch_assoc()) {
                $output .= '<tr>';
                $output .= '<td>' . htmlspecialchars($row['fertilizer_name']) . '</td>';
                $output .= '<td>' . htmlspecialchars($row['fertilizer_type']) . '</td>';
                $output .= '<td>' . ($row['quantity_kg'] !== null ? number_format($row['quantity_kg'], 2) : ($row['quantity_ml'] !== null ? number_format($row['quantity_ml'], 2) : '0')) . '</td>';
                $output .= '<td>' . htmlspecialchars($row['unit']) . '</td>';
                $output .= '<td>' . htmlspecialchars($row['purchase_date']) . '</td>';
                $output .= '<td class="action-buttons">';
                $output .= '<button class="edit-btn" onclick="editFertilizer(' . htmlspecialchars($row['fertilizer_id']) . ', \'' . htmlspecialchars($row['fertilizer_name']) . '\', \'' . htmlspecialchars($row['fertilizer_type']) . '\', \'' . htmlspecialchars($row['quantity_kg']) . '\', \'' . htmlspecialchars($row['quantity_ml']) . '\', \'' . htmlspecialchars($row['unit']) . '\', \'' . htmlspecialchars($row['purchase_date']) . '\')">Edit</button>';
                $output .= '<button class="delete-btn" onclick="deleteFertilizer(' . htmlspecialchars($row['fertilizer_id']) . ')">Delete</button>';
                $output .= '</td>';
                $output .= '</tr>';
            }

            echo $output;
            break;

        default:
            echo json_encode(['status' => 'error', 'message' => 'Invalid action']);
            break;
    }


} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request']);
}

$conn->close();
?>