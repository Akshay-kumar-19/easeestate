<?php

session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

$time_filter = isset($_GET['time_filter']) ? $_GET['time_filter'] : 'weekly';
$current_date = date('Y-m-d');
$start_date = '';
$end_date = $current_date;

if ($time_filter === 'daily') {
    $start_date = $current_date;
} elseif ($time_filter === 'weekly') {
    $start_date = date('Y-m-d', strtotime('-7 days', strtotime($current_date)));
} elseif ($time_filter === 'monthly') {
    $start_date = date('Y-m-01', strtotime($current_date));
} elseif ($time_filter === 'yearly') {
    $start_date = date('Y-01-01', strtotime($current_date));
}

$sql_filter_condition = "";
if ($time_filter !== 'all') {
    $sql_filter_condition = "WHERE fuh.date_used BETWEEN '$start_date' AND '$end_date'";
}


$usageHistory = [];
try {
    $sql_usage_history = "SELECT fuh.date_used, fuh.fertilizer_name, fuh.quantity_used, fuh.unit, fuh.field_location, ll.lead_name
                           FROM fertilizer_usage_history fuh
                           LEFT JOIN labour_lead ll ON fuh.lead_id = ll.lead_id
                           $sql_filter_condition
                           ORDER BY fuh.date_used DESC";

    $result_usage_history = $conn->query($sql_usage_history);

    if ($result_usage_history->num_rows > 0) {
        while ($row = $result_usage_history->fetch_assoc()) {
            $usageHistory[] = $row;
        }
    }

} catch (Exception $e) {
    error_log("Error fetching fertilizer usage history: " . $e->getMessage());
    echo "Error fetching usage history.";
    $usageHistory = [];
} finally {
    $conn->close();
}


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fertilizer Assignment Details</title>
    <link rel="stylesheet" href="css/fertilizer_usage_history.css">
    <link rel="icon" href="css/logos.png" sizes="32x32" type="image/png">
    
</head>
<body>
    <div class="container history-container">
        <h1>Fertilizer Assignment Details</h1>

        <div class="filter-options">
            <a href="?time_filter=daily"   class="<?php if($time_filter === 'daily') echo 'active'; ?>">Daily</a> <a href="?time_filter=weekly"  class="<?php if($time_filter === 'weekly') echo 'active'; ?>">Weekly</a>
            <a href="?time_filter=monthly" class="<?php if($time_filter === 'monthly') echo 'active'; ?>">Monthly</a>
            <a href="?time_filter=yearly"  class="<?php if($time_filter === 'yearly') echo 'active'; ?>">Yearly</a>
            <a href="?time_filter=all"     class="<?php if($time_filter === 'all') echo 'active'; ?>">All History</a>
        </div>


        <table class="history-table">
            <thead>
                <tr>
                    <th>Date Used</th>
                    <th>Fertilizer Name</th>
                    <th>Quantity Used</th>
                    <th>Unit</th>
                    <th>Field Location</th>
                    <th>Team Lead</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($usageHistory)): ?>
                    <tr><td colspan="6">No fertilizer usage history found for this period.</td></tr>
                <?php else: ?>
                    <?php foreach ($usageHistory as $usage): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($usage['date_used']); ?></td>
                            <td><?php echo htmlspecialchars($usage['fertilizer_name']); ?></td>
                            <td><?php echo htmlspecialchars($usage['quantity_used']); ?></td>
                            <td><?php echo htmlspecialchars($usage['unit']); ?></td>
                            <td><?php echo htmlspecialchars($usage['field_location']); ?></td>
                            <td><?php echo htmlspecialchars($usage['lead_name'] ? $usage['lead_name'] : 'N/A'); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>

        <div class="modal-actions">
            <button type="button" onclick="window.location.href='fertilizer_assign.php'" class="cancel-button">Back to assignment</button>
            
            
        </div>
    </div>
    <a href="dashboard.php" class="dashboard-button">
        <i class="fas fa-home"></i> Dashboard
    </a>
</body>
</html>