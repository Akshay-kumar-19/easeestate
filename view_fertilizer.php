<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width-device-width, initial-scale=1.0">
    <title>View Fertilizer Inventory</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="icon" href="css/logos.png" sizes="32x32" type="image/png">
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>
<div class="container">
    <h1>Fertilizer Inventory View</h1>

    <div class="table-responsive">
        <table class="fertilizer-table">
            <thead>
                <tr>
                    <th>Fertilizer Name</th>
                    <th>Type</th>
                    <th>Total Quantity</th>
                    <th>Unit</th>
                </tr>
            </thead>
            <tbody id="fertilizerTableViewBody">
                </tbody>
        </table>
    </div>

    <div class="button-container">
        <button type="button" class="add-button" onclick="window.location.href='update_fertilizer.php'">Back to Fertilizer Management</button>
        <button type="button" class="assign-button" onclick="window.location.href='assign_fertilizer.php'">Assign Fertilizer</button>
    </div>


</div>

<script src="js/view_fertilizer.js"></script>
<script>
    
    window.onload = function() {
       
        if (performance.navigation.type !== performance.navigation.TYPE_RELOAD) {
            location.reload();
        }
    };
</script>
<a href="dashboard.php" class="dashboard-button">
        <i class="fas fa-home"></i> Dashboard
    </a>
</body>
</html>