<?php
session_start();
include 'db.php'; // Ensure this connects to your MySQL database

// Fetch total workers today
$workersQuery = "SELECT COUNT(*) as total FROM workers WHERE DATE(work_date) = CURDATE()";
$workersResult = mysqli_query($conn, $workersQuery);
$workersRow = mysqli_fetch_assoc($workersResult);
$totalWorkersToday = $workersRow['total'];

// Fetch today's total crop
$cropsQuery = "SELECT SUM(quantity) as total FROM crops WHERE DATE(collected_date) = CURDATE()";
$cropsResult = mysqli_query($conn, $cropsQuery);
$cropsRow = mysqli_fetch_assoc($cropsResult);
$todaysCrop = $cropsRow['total'];

// Fetch last month payment
$lastMonthQuery = "SELECT SUM(amount) as total FROM payments WHERE MONTH(payment_date) = MONTH(CURRENT_DATE() - INTERVAL 1 MONTH)";
$lastMonthResult = mysqli_query($conn, $lastMonthQuery);
$lastMonthRow = mysqli_fetch_assoc($lastMonthResult);
$lastMonthPayment = $lastMonthRow['total'];

// Fetch last week payment
$lastWeekQuery = "SELECT SUM(amount) as total FROM payments WHERE payment_date >= CURDATE() - INTERVAL 7 DAY";
$lastWeekResult = mysqli_query($conn, $lastWeekQuery);
$lastWeekRow = mysqli_fetch_assoc($lastWeekResult);
$lastWeekPayment = $lastWeekRow['total'];

// Fetch pending tools
$pendingToolsQuery = "SELECT COUNT(*) as total FROM tools WHERE status = 'pending'";
$pendingToolsResult = mysqli_query($conn, $pendingToolsQuery);
$pendingToolsRow = mysqli_fetch_assoc($pendingToolsResult);
$pendingTools = $pendingToolsRow['total'];

$response = [
    'totalWorkersToday' => $totalWorkersToday,
    'todaysCrop' => $todaysCrop,
    'lastMonthPayment' => $lastMonthPayment,
    'lastWeekPayment' => $lastWeekPayment,
    'pendingTools' => $pendingTools
];

echo json_encode($response);
?>
