<?php
# fertilizer_purchase_history_view.php - View Fertilizer Purchase History

session_start();
require 'db.php';

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch fertilizer purchase history data
$purchaseHistoryDetails = [];
try {
    $sql = "SELECT purchase_date, fertilizer_name, quantity_purchased, unit FROM fertilizer_purchase_history ORDER BY purchase_date DESC";
    $result = $conn->query($sql);
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $purchaseHistoryDetails[] = $row;
        }
    }
} catch (Exception $e) {
    error_log("Database error fetching fertilizer purchase history: " . $e->getMessage());
    echo "Error fetching fertilizer purchase history."; // User-friendly error message
    $purchaseHistoryDetails = []; // Ensure empty array in case of error
} finally {
    $conn->close();
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fertilizer Purchase History</title>
    <link rel="stylesheet" href="css/styles.css">
    <link rel="icon" href="css/logos.png" sizes="32x32" type="image/png">
    <style>
        .history-purchase-container { padding: 20px; }
        .purchase-history-table { width: 70%; border-collapse: collapse; margin-top: 20px; } /* Reduced width and centered */
        .purchase-history-table th, .purchase-history-table td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        .purchase-history-table th { background-color: #f4f4f4; }
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
    <div class="container history-purchase-container">
        <h1>Fertilizer Purchase History</h1>

        <div class="table-wrapper">
            <table class="purchase-history-table">
                <thead>
                    <tr>
                        <th>Purchase Date</th>
                        <th>Fertilizer Name</th>
                        <th>Quantity Purchased</th>
                        <th>Unit</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($purchaseHistoryDetails)): ?>
                        <tr><td colspan="4">No fertilizer purchase history found.</td></tr>
                    <?php else: ?>
                        <?php foreach ($purchaseHistoryDetails as $purchase): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($purchase['purchase_date']); ?></td>
                                <td><?php echo htmlspecialchars($purchase['fertilizer_name']); ?></td>
                                <td><?php echo htmlspecialchars($purchase['quantity_purchased']); ?></td>
                                <td><?php echo htmlspecialchars($purchase['unit']); ?></td>
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