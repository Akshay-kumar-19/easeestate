<?php
session_start();
require 'db.php';
require 'salary_calculation.php';

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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    
</head>
<body>
    <h1>Weekly Labour Salary Report</h1>

    <div id="salaryDisplayArea">
        <p>Loading salary data...</p>
    </div>

    <button id="sendSalaryButton" class="send-salary-button">
        <i class="fas fa-envelope"></i> Send Salary Details
    </button>

    <div id="emailStatus" style="margin-top: 10px;"></div>

    <script src="js/salary_calculation.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {

            const today = new Date();


            const dayOfWeek = today.getDay();


            const sunday = new Date(today);
            sunday.setDate(today.getDate() - dayOfWeek);


            const saturday = new Date(today);
            saturday.setDate(today.getDate() + (6 - dayOfWeek));


            const weekStartDate = sunday.toISOString().slice(0, 10);
            const weekEndDate = saturday.toISOString().slice(0, 10);

            triggerSalaryCalculation(weekStartDate, weekEndDate);
        });
    </script>

<script>
        document.addEventListener('DOMContentLoaded', function () {

            const today = new Date();
            const dayOfWeek = today.getDay();
            const sunday = new Date(today);
            sunday.setDate(today.getDate() - dayOfWeek);
            const saturday = new Date(today);
            saturday.setDate(today.getDate() + (6 - dayOfWeek));
            const weekStartDate = sunday.toISOString().slice(0, 10);
            const weekEndDate = saturday.toISOString().slice(0, 10);

            triggerSalaryCalculation(weekStartDate, weekEndDate);

            const sendSalaryButton = document.getElementById('sendSalaryButton');
            const salaryDisplayArea = document.getElementById('salaryDisplayArea');
            const emailStatusDiv = document.getElementById('emailStatus');

            sendSalaryButton.addEventListener('click', function() {
                const salaryDetails = salaryDisplayArea.innerHTML;


                const recipientEmail = 'akshayckm04@gmail.com';

                fetch('send_salary_email.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'recipient_email=' + encodeURIComponent(recipientEmail) + '&salary_details=' + encodeURIComponent(salaryDetails),
                })
                .then(response => response.text())
                .then(data => {
                    emailStatusDiv.innerHTML = data;
                })
                .catch(error => {
                    console.error('Error sending salary details:', error);
                    emailStatusDiv.innerHTML = '<span style="color: red;">Error sending email. Please try again later.</span>';
                });
            });
        });
    </script>
     <a href="dashboard.php" class="dashboard-button">
        <i class="fas fa-home"></i> Dashboard
    </a>
</body>
</html>