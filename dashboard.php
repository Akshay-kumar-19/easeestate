<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}
$username = $_SESSION['username'];


require_once 'db.php'; 

$today = date("Y-m-d");


$sqlWorkersToday = "SELECT COUNT(DISTINCT worker_id) AS totalWorkers FROM attendance WHERE date = '$today' AND present = 1";
$resultWorkersToday = $conn->query($sqlWorkersToday);
$totalWorkersToday = 0;
if ($resultWorkersToday->num_rows > 0) {
    $row = $resultWorkersToday->fetch_assoc();
    $totalWorkersToday = $row['totalWorkers'];
}


$jobIdCoffee = 1; 
$sqlCoffeeCrop = "SELECT SUM(total_kg) AS coffeeCrop FROM crops_plucked WHERE plucked_date = '$today' AND job_id = $jobIdCoffee";
$resultCoffeeCrop = $conn->query($sqlCoffeeCrop);
$todaysCoffeeCrop = 0;
if ($resultCoffeeCrop->num_rows > 0) {
    $row = $resultCoffeeCrop->fetch_assoc();
    $todaysCoffeeCrop = $row['coffeeCrop'] ? $row['coffeeCrop'] : 0; 
}


$jobIdPepper = 2; 
$sqlPepperCrop = "SELECT SUM(total_kg) AS pepperCrop FROM crops_plucked WHERE plucked_date = '$today' AND job_id = $jobIdPepper";
$resultPepperCrop = $conn->query($sqlPepperCrop);
$todaysPepperCrop = 0;
if ($resultPepperCrop->num_rows > 0) {
    $row = $resultPepperCrop->fetch_assoc();
    $todaysPepperCrop = $row['pepperCrop'] ? $row['pepperCrop'] : 0; 
}


$jobIdAreca = 3;
$sqlArecaCrop = "SELECT SUM(kone_count) AS arecaCropKoneCount FROM crops_plucked WHERE plucked_date = '$today' AND job_id = $jobIdAreca";
$resultArecaCrop = $conn->query($sqlArecaCrop);
$todaysArecaKoneCount = 0;
if ($resultArecaCrop->num_rows > 0) {
    $row = $resultArecaCrop->fetch_assoc();
    $todaysArecaKoneCount = $row['arecaCropKoneCount'] ? $row['arecaCropKoneCount'] : 0; 
}


$apiKey = "ff5352907696f34a8bcbef019263e4de";
$city = "Mangaluru";
$countryCode = "IN";
$units = "metric";
$cacheFile = 'weather_cache.json'; 
$cacheTime = 900; 
$weatherData = null; 


if (file_exists($cacheFile) && (time() - filemtime($cacheFile)) < $cacheTime) {
  
    $weatherData = json_decode(file_get_contents($cacheFile), true);
} else {
    
    $weatherUrl = "http://api.openweathermap.org/data/2.5/weather?q={$city},{$countryCode}&appid={$apiKey}&units={$units}";
    $weatherData = @json_decode(file_get_contents($weatherUrl), true);

    if ($weatherData && $weatherData['cod'] == 200) {
        
        file_put_contents($cacheFile, json_encode($weatherData));
    } else {
       
        if (file_exists($cacheFile)) {
            $weatherData = json_decode(file_get_contents($cacheFile), true);
        }
    }
}

$weatherDescription = '';
$temperature = '';
$weatherIcon = '';
$rainPrediction = 'No rain expected'; 

if ($weatherData && $weatherData['cod'] == 200) { 
    $weatherDescription = $weatherData['weather'][0]['description'];
    $temperature = round($weatherData['main']['temp']);
    $weatherIcon = "http://openweathermap.org/img/wn/{$weatherData['weather'][0]['icon']}.png";


    if (isset($weatherData['rain']) && isset($weatherData['rain']['1h'])) {
        $rainPrediction = 'Rain expected';
    } elseif (isset($weatherData['rain']) && isset($weatherData['rain']['3h'])) {
        $rainPrediction = 'Rain expected';
    }
     elseif (isset($weatherData['snow']) && isset($weatherData['snow']['1h'])) {
        $rainPrediction = 'Snow expected';
    }
    elseif (isset($weatherData['snow']) && isset($weatherData['snow']['3h'])) {
        $rainPrediction = 'Snow expected';
    }
    else {
        $rainPrediction = 'No rain expected';
    }
} else {
    $weatherDescription = "Weather data not available.";
    $temperature = "N/A";
    $weatherIcon = "";
    $rainPrediction = "N/A";
}

$sqlLowFertilizer = "SELECT fertilizer_name, total_quantity, unit FROM fertilizer_inventory WHERE total_quantity < 100";
$resultLowFertilizer = $conn->query($sqlLowFertilizer);
$lowFertilizer = [];
if ($resultLowFertilizer->num_rows > 0) {
    while ($row = $resultLowFertilizer->fetch_assoc()) {
        $lowFertilizer[] = $row;
    }
}

// Fetch count of unreturned tools
$sqlUnreturnedToolsCount = "SELECT COUNT(*) AS unreturned_count FROM tool_assignments WHERE return_date IS NULL";
$resultUnreturnedToolsCount = $conn->query($sqlUnreturnedToolsCount);
$unreturnedToolsCount = 0;
if ($resultUnreturnedToolsCount->num_rows > 0) {
    $row = $resultUnreturnedToolsCount->fetch_assoc();
    $unreturnedToolsCount = $row['unreturned_count'];
}

$totalNotifications = count($lowFertilizer) + ($unreturnedToolsCount > 0 ? 1 : 0);



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
        <h1>Welcome!</h1>
    </div>

    <div class="dashboard-container">
        <div class="sidebar">
            <div class="sidebar-header">
                <h1>EaseEstate</h1>
                <p>Estate Management</p>
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
                    <i class="fas fa-pepper-hot icon"></i>
                    <div>
                        <p class="stat-title">Today's Pepper Crop</p>
                        <p class="stat-value" id="todaysPepperCrop"><?php echo number_format($todaysPepperCrop, 2); ?> kg</p>
                    </div>
                </div>
                <div class="stat-item">
                    <i class="fas fa-tree icon"></i>
                    <div>
                        <p class="stat-title">Today's Areca Crop</p>
                        <p class="stat-value" id="todaysArecaCrop"><?php echo $todaysArecaKoneCount; ?> Kone Count</p>
                    </div>
                </div>


            </div>

            <div class="sidebar-weather">
                <a href="weather_details.php">
                    <h3>Weather in Mangaluru</h3>
                    <div class="sidebar-weather-icon">
                        <?php if ($weatherIcon): ?>
                            <img src="<?php echo $weatherIcon; ?>" alt="Weather Icon">
                        <?php endif; ?>
                    </div>
                    <div class="sidebar-weather-temp"><?php echo $temperature; ?>°C</div>
                    <div class="sidebar-weather-desc"><?php echo $weatherDescription; ?></div>
                     <div class="sidebar-weather-rain"><?php echo $rainPrediction; ?></div>
                    View Details
                </a>
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
                        <i class="fas fa-edit"></i>
                        update fertilizer
                    </a>
                    <a href="fertilizer_assign.php" class="btn">
                        <i class="fas fa-tasks"></i>
                        Assign Fertilizer
                    </a>
                    <a href="fertilizer_view.php" class="btn">
                        <i class="fas fa-eye"></i>
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
                    <a href="tool_dashboard.php" class="btn">
                        <i class="fas fa-eye"></i>
                        View Tools
                    </a>
                    <a href="tool_assignment.php" class="btn">
                        <i class="fas fa-tasks"></i>
                        Assign Tools
                    </a>
                    <a href="tool_management.php" class="btn">
                        <i class="fas fa-edit"></i>
                        Update Tools
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <button id="notificationButton" class="notification-button">
        <i class="fas fa-bell"></i>
        <?php if ($totalNotifications > 0): ?>
            <span id="notificationCount" class="notification-badge"><?php echo $totalNotifications; ?></span>
        <?php endif; ?>
    </button>

    <div id="notificationDropdown" class="notification-dropdown">
        <div class="notification-header">
            Notifications
            <button class="close-button" onclick="document.getElementById('notificationDropdown').style.display='none';">&times;</button>
        </div>
        <ul id="notificationList">
            <?php if (empty($lowFertilizer) && $unreturnedToolsCount == 0): ?>
                <li class="notification-empty">No new notifications.</li>
            <?php else: ?>
                <?php if (!empty($lowFertilizer)): ?>
                    <li><strong>Low Fertilizer Stock:</strong>
                        <ul>
                            <?php foreach ($lowFertilizer as $fertilizer): ?>
                                <li><?php echo htmlspecialchars($fertilizer['fertilizer_name']); ?> - <?php echo htmlspecialchars(number_format($fertilizer['total_quantity'], 2)); ?> <?php echo htmlspecialchars($fertilizer['unit']); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </li>
                <?php endif; ?>
                <?php if ($unreturnedToolsCount > 0): ?>
                    <li><strong>Unreturned Tools:</strong> <?php echo $unreturnedToolsCount; ?> tools are currently unreturned.</li>
                <?php endif; ?>
            <?php endif; ?>
        </ul>
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