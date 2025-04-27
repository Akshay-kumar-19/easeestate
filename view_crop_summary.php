<?php
require 'db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

$coffee_job_id = 1;
$pepper_job_id = 2;
$areca_job_id = 3;

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crop Summary</title>
    <link rel="stylesheet" href="css/view_crop_summary.css">
    <link rel="icon" href="css/logos.png" sizes="32x32" type="image/png">
    <script src="js/view_crop_summary.js" defer></script>
    <script>
        const coffee_job_id = <?php echo json_encode($coffee_job_id); ?>;
        const pepper_job_id = <?php echo json_encode($pepper_job_id); ?>;
        const areca_job_id = <?php echo json_encode($areca_job_id); ?>;
    </script>
</head>
<body>
    <div class="container">
        <h1>Crop Summary</h1>
        <div class="summary-controls">
            <button type="button" id="yearlySummaryBtn" class="summary-btn active">Yearly</button>
            <button type="button" id="monthlySummaryBtn" class="summary-btn">Monthly</button>
            <button type="button" id="weeklySummaryBtn" class="summary-btn">Weekly</button>
        </div>

        <div id="cropSummaryDataSection">
            <h2>Crop Summary Data</h2>
            <div id="cropSummaryTableContainer">
                </div>
        </div>
    </div>
    <a href="dashboard.php" class="dashboard-button">
        <i class="fas fa-home"></i> Dashboard
    </a>
</body>
</html>