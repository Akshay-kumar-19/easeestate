<?php
# fertilizer_view.php - View Fertilizer Inventory Details

session_start();
require 'db.php';

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch fertilizer inventory data for display (name, unit, quantity)
$inventoryDetails = [];
try {
    $sql = "SELECT fertilizer_name, unit, total_quantity FROM fertilizer_inventory ORDER BY fertilizer_name";
    $result = $conn->query($sql);
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $inventoryDetails[] = $row;
        }
    }
} catch (Exception $e) {
    error_log("Database error fetching fertilizer inventory details: " . $e->getMessage());
    echo "Error fetching fertilizer inventory details."; // User-friendly error message
    $inventoryDetails = []; // Ensure empty array in case of error
} finally {
    $conn->close();
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Fertilizer Inventory</title>
    <link rel="stylesheet" href="css/styles.css">
    <link rel="icon" href="css/logos.png" sizes="32x32" type="image/png">
    <style>
        .view-container { padding: 20px; }
        .inventory-details-table { width: 70%; border-collapse: collapse; margin-top: 20px; } /* Reduced width and centered */
        .inventory-details-table th, .inventory-details-table td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        .inventory-details-table th { background-color: #f4f4f4; }
        .back-to-dashboard-button {
            padding: 10px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            background-color: #007BFF;
            color: white;
            margin-top: 20px;
            display: inline-block;
        }
        .back-to-dashboard-button:hover {
            background-color: #0056b3;
        }
        .table-wrapper {
            display: flex;
            justify-content: center; /* Center horizontally */
            width: 100%;
        }


    </style>
</head>
<body>
    <div class="container view-container">
        <h1>Fertilizer Inventory Details</h1>

        <div class="table-wrapper">
            <table class="inventory-details-table">
                <thead>
                    <tr>
                        <th>Fertilizer Name</th>
                        <th>Unit</th>
                        <th>Total Quantity</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($inventoryDetails)): ?>
                        <tr><td colspan="3">No fertilizer inventory data found.</td></tr>
                    <?php else: ?>
                        <?php foreach ($inventoryDetails as $detail): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($detail['fertilizer_name']); ?></td>
                                <td><?php echo htmlspecialchars($detail['unit']); ?></td>
                                <td><?php echo htmlspecialchars($detail['total_quantity']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>


        <div class="modal-actions">
            <button type="button" onclick="window.location.href='dashboard.php'" class="back-to-dashboard-button">Back to Dashboard</button>
        </div>
    </div>
</body>
</html>