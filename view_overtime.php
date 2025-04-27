<?php
require 'db.php';
session_start();
$user_id = $_SESSION['user_id'];


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Overtime</title>
    <link rel="stylesheet" href="css/view_overtime.css">
    <link rel="icon" href="css/logos.png" sizes="32x32" type="image/png">
    <script src="js/view_overtime.js" defer></script>
</head>
<body>
    <div class="container">
        <h1>View Overtime</h1>

        <div class="input-section">
            <label for="date_overtime">Select Date:</label>
            <input type="date" id="date_overtime" name="date_overtime" value="<?php echo date('Y-m-d'); ?>">

            <button id="viewOvertimeBtn" class="view-btn">View Overtime</button>
        </div>

        <div id="overtime_data" class="overtime-table" style="display: none;">
            <h2>Daily Overtime Report</h2>
            <table id="overtimeTable">
                <thead>
                    <tr>
                        <th>Worker Name</th>
                        <th>Labour Lead</th>
                        <th>Job Name</th>
                        <th>Total Overtime Hours</th>
                    </tr>
                </thead>
                <tbody>
                    </tbody>
            </table>
            <div class="summary-section">
                <p id="total_workers_overtime">Total Workers Overtime: 0</p>
                <p id="total_overtime_hours">Total Overtime Hours: 0</p>
            </div>
        </div>

    </div>
    <a href="dashboard.php" class="dashboard-button">
        <i class="fas fa-home"></i> Dashboard
    </a>
</body>
</html>