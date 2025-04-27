
<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
header('Content-Type: application/json');

require 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $lot_number = isset($_POST['lot_number']) ? trim($_POST['lot_number']) : '';

    if (empty($lot_number)) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => "Lot Number is required."]);
        exit;
    }

    $sql = "DELETE FROM coffee_lots WHERE lot_number = ?";
    $stmt = $conn->prepare($sql);

    if ($stmt === false) {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => "Prepare statement failed: " . $conn->error]);
        exit;
    }

    $stmt->bind_param("s", $lot_number);

    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            echo json_encode(['status' => 'success', 'message' => 'Coffee lot ' . $lot_number . ' sold successfully']);
        } else {
            http_response_code(404);
            echo json_encode(['status' => 'error', 'message' => 'Coffee lot ' . $lot_number . ' not found.']);
        }
    } else {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => "Execute statement failed: " . $stmt->error]);
        exit;
    }

    $stmt->close();
} else {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => "Invalid request method."]);
}

$conn->close();

?>