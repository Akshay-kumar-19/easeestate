<?php

session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

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
    echo "Error fetching fertilizer purchase history.";
    $purchaseHistoryDetails = [];
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
    <link rel="stylesheet" href="css/fertilizer_purchace_history.css">
    <link rel="icon" href="css/logos.png" sizes="32x32" type="image/png">
    
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

       
    </div>
    <a href="dashboard.php" class="dashboard-button">
        <i class="fas fa-home"></i> Dashboard
    </a>
</body>
</html>