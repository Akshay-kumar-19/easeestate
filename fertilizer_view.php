<?php

session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

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
    echo "Error fetching fertilizer inventory details.";
    $inventoryDetails = [];
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
    <link rel="stylesheet" href="css/fertilizer_view.css">
    <link rel="icon" href="css/logos.png" sizes="32x32" type="image/png">
    
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


        
    </div>
    <a href="dashboard.php" class="dashboard-button">
        <i class="fas fa-home"></i> Dashboard
    </a>
</body>
</html>