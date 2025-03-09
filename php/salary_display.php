<?php
session_start();
require 'db.php'; // Database connection
require 'salary_calculation.php'; // Include salary calculation logic

// Ensure user is logged in (you might need to adapt this)
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Weekly Salary Report</title>
    <link rel="icon" href="css/logos.png" sizes="32x32" type="image/png">
 <link rel="stylesheet" href="css/salary.css">
</head>
<body>
    <h1>Weekly Labour Salary Report</h1>

    <div id="salaryDisplayArea">
        <p>Loading salary data...</p>
    </div>

    <script src="js/salary_calculation.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Get current date
            const today = new Date();

            // Calculate day of the week (0 for Sunday, 1 for Monday, ..., 6 for Saturday)
            const dayOfWeek = today.getDay();

            // Calculate Sunday's date (start of the week)
            const sunday = new Date(today);
            sunday.setDate(today.getDate() - dayOfWeek); // Subtract days to get to Sunday

            // Calculate Saturday's date (end of the week)
            const saturday = new Date(today);
            saturday.setDate(today.getDate() + (6 - dayOfWeek)); // Add days to get to Saturday

            // Format dates as<ctrl3348>-MM-DD for PHP compatibility
            const weekStartDate = sunday.toISOString().slice(0, 10); //-MM-DD

            triggerSalaryCalculation(weekStartDate, weekEndDate);
        });
    </script>
</body>
</html>