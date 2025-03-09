<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}
$username = $_SESSION['username'];

// Database connection details - Include db.php
require_once 'db.php'; // Include your db.php file with database connection

$today = date("Y-m-d");

// 1. Total Workers Present Today
$sqlWorkersToday = "SELECT COUNT(DISTINCT worker_id) AS totalWorkers FROM attendance WHERE date = '$today' AND present = 1";
$resultWorkersToday = $conn->query($sqlWorkersToday);
$totalWorkersToday = 0;
if ($resultWorkersToday->num_rows > 0) {
    $row = $resultWorkersToday->fetch_assoc();
    $totalWorkersToday = $row['totalWorkers'];
}

// 2. Today's Coffee Crop
$jobIdCoffee = 1; // Job ID for "cofffee plucking"
$sqlCoffeeCrop = "SELECT SUM(total_kg) AS coffeeCrop FROM crops_plucked WHERE plucked_date = '$today' AND job_id = $jobIdCoffee";
$resultCoffeeCrop = $conn->query($sqlCoffeeCrop);
$todaysCoffeeCrop = 0;
if ($resultCoffeeCrop->num_rows > 0) {
    $row = $resultCoffeeCrop->fetch_assoc();
    $todaysCoffeeCrop = $row['coffeeCrop'] ? $row['coffeeCrop'] : 0; // Handle NULL case
}

// 3. Today's Pepper Crop
$jobIdPepper = 2; // Job ID for "pepper plucking"
$sqlPepperCrop = "SELECT SUM(total_kg) AS pepperCrop FROM crops_plucked WHERE plucked_date = '$today' AND job_id = $jobIdPepper";
$resultPepperCrop = $conn->query($sqlPepperCrop);
$todaysPepperCrop = 0;
if ($resultPepperCrop->num_rows > 0) {
    $row = $resultPepperCrop->fetch_assoc();
    $todaysPepperCrop = $row['pepperCrop'] ? $row['pepperCrop'] : 0; // Handle NULL case
}

// 4. Today's Areca Crop (Kone Count)
$jobIdAreca = 3; // Job ID for "arreca plucking"
$sqlArecaCrop = "SELECT SUM(kone_count) AS arecaCropKoneCount FROM crops_plucked WHERE plucked_date = '$today' AND job_id = $jobIdAreca";
$resultArecaCrop = $conn->query($sqlArecaCrop);
$todaysArecaKoneCount = 0;
if ($resultArecaCrop->num_rows > 0) {
    $row = $resultArecaCrop->fetch_assoc();
    $todaysArecaKoneCount = $row['arecaCropKoneCount'] ? $row['arecaCropKoneCount'] : 0; // Handle NULL case
}


$conn->close();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EaseEstate</title>
    <link rel="stylesheet" href="css/dash.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="icon" href="css/logos.png" sizes="32x32" type="image/png">
</head>
<body>
    <div class="welcome-container">
        <h1>Welcome, <?php echo htmlspecialchars($username); ?>!</h1>
    </div>

    <div class="dashboard-container">
        <div class="sidebar">
            <div class="sidebar-header">
                <h1>EaseEstate</h1>
                <p>Coffee Management</p>
            </div>
            <div class="sidebar-stats">
                <div class="stat-item">
                    <i class="fas fa-users icon"></i>
                    <div>
                        <p class="stat-title">Total Workers Today</p>
                        <p class="stat-value" id="totalWorkersToday"><?php echo $totalWorkersToday; ?></p>
                    </div>
                </div>
                <div class="stat-item">
                    <i class="fas fa-seedling icon"></i>
                    <div>
                        <p class="stat-title">Today's Coffee Crop</p>
                        <p class="stat-value" id="todaysCoffeeCrop"><?php echo number_format($todaysCoffeeCrop, 2); ?> kg</p>
                    </div>
                </div>
                 <div class="stat-item">
                    <i class="fas fa-seedling icon"></i>
                    <div>
                        <p class="stat-title">Today's Pepper Crop</p>
                        <p class="stat-value" id="todaysPepperCrop"><?php echo number_format($todaysPepperCrop, 2); ?> kg</p>
                    </div>
                </div>
                 <div class="stat-item">
                    <i class="fas fa-seedling icon"></i>
                    <div>
                        <p class="stat-title">Today's Areca Crop</p>
                        <p class="stat-value" id="todaysArecaCrop"><?php echo $todaysArecaKoneCount; ?> Kone Count</p>
                    </div>
                </div>
               
                <div class="stat-item">
                    <i class="fas fa-tools icon"></i>
                    <div>
                        <p class="stat-title">Pending Tools</p>
                        <p class="stat-value" id="pendingTools">0</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="main-content">
            <div class="action-section">
                <h2><i class="fas fa-calendar-alt"></i> Attendance</h2>
                <div class="action-buttons">
                    <a href="update_attendance.php" class="btn">
                        <i class="fas fa-calendar-check"></i>
                        Update Attendance
                    </a>
                    <a href="update_overtime.php" class="btn">
                        <i class="fas fa-clock"></i>
                        Update Overtime
                    </a>
                    <a href="view_attendance.php" class="btn">
                        <i class="fas fa-eye"></i>
                        View Attendance
                    </a>
                     <a href="view_overtime.php" class="btn">
                        <i class="fas fa-eye"></i>
                        View overtime
                    </a>
                     <a href="labour_lead.php" class="btn">
                        <i class="fas fa-user-tie"></i>
                        UpdateLabour Lead
                    </a>
                     <a href="workers.php" class="btn">
                        <i class="fas fa-user-edit"></i>
                        Update Worker
                    </a>
                     <a href="job.php" class="btn">
                        <i class="fas fa-briefcase"></i>
                        Update Jobs
                    </a>
                </div>
            </div>

            <div class="action-section">
                <h2><i class="fas fa-wallet"></i> Payment</h2>
                <div class="action-buttons">
                    <a href="salary_display.php" class="btn">
                        <i class="fas fa-file-invoice-dollar"></i>
                        weekly payment
                    </a>
                    <a href="salary_display_user.php" class="btn">
                        <i class="fas fa-list-alt"></i>
                        View Payment Details
                    </a>

                </div>
            </div>

            <div class="action-section">
                <h2><i class="fas fa-tractor"></i> Crops</h2>
                <div class="action-buttons">
                    <a href="update_crops_plucked.php" class="btn">
                        <i class="fas fa-edit"></i>
                        Update Crop Plucked
                    </a>
                    <a href="view_crops_plucked.php" class="btn">
                        <i class="fas fa-chart-bar"></i>
                        View Crop Plucked
                    </a>
                    <a href="view_crop_summary.php" class="btn">
                        <i class="fas fa-boxes"></i>
                        View Crop summary
                    </a>
                </div>
            </div>

            <div class="action-section">
                <h2><i class="fas fa-warehouse"></i> Stock Management</h2>
                <div class="action-buttons">
                    <a href="coffee_management.php" class="btn">
                        <i class="fas fa-coffee"></i>
                        Coffee
                    </a>
                    <a href="pepper_management.php" class="btn">
                        <i class="fas fa-pepper-hot"></i>
                        Pepper
                    </a>
                    <a href="arreca_management.php" class="btn">
                        <i class="fas fa-leaf"></i>
                        arreca
                    </a>

                </div>
            </div>

            <div class="action-section">
                <h2><i class="fas fa-flask"></i> Fertilizers</h2>
                <div class="action-buttons">
                    <a href="fertilizer_add.php" class="btn">
                        <i class="fas fa-eye"></i>
                        update fertilizer
                    </a>
                    <a href="fertilizer_assign.php" class="btn">
                        <i class="fas fa-tasks"></i>
                        Assign Fertilizer
                    </a>
                    <a href="fertilizer_view.php" class="btn">
                        <i class="fas fa-edit"></i>
                        View fertilizer
                    </a>
                     <a href="fertilizer_purchace_history_view.php" class="btn">
                        <i class="fas fa-edit"></i>
                        View purchace history
                    </a>
                </div>
            </div>

            <div class="action-section">
                <h2><i class="fas fa-tools"></i> Tools</h2>
                <div class="action-buttons">
                    <a href="view_tools.php" class="btn">
                        <i class="fas fa-eye"></i>
                        View Tools
                    </a>
                    <a href="assign_tools.php" class="btn">
                        <i class="fas fa-tasks"></i>
                        Assign Tools
                    </a>
                    <a href="update_tools.php" class="btn">
                        <i class="fas fa-edit"></i>
                        Update Tools
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="logout-container">
    <form method="POST" action="logout.php">
        <button type="submit" class="logout-btn">
            <i class="fas fa-sign-out-alt"></i>
            Logout
        </button>
    </form>
</div>

    <script src="js/dashboard.js"></script>
</body>
</html>