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

    <div class="date-range-selector">
        <label for="fromDate">From:</label>
        <input type="date" id="fromDate" name="fromDate" required>

        <label for="toDate">To:</label>
        <input type="date" id="toDate" name="toDate" required>

        <button id="generateReport">Generate Report</button>
    </div>

    <div id="salaryDisplayArea">
        <p>Please select a date range and click "Generate Report".</p>
    </div>

    <script src="js/salary_calculation_user.js"></script>
    <script>
        
    </script>
     <a href="dashboard.php" class="dashboard-button">
        <i class="fas fa-home"></i> Dashboard
    </a>
</body>
</html>