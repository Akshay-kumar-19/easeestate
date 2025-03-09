<?php
require 'db.php';
session_start();
header('Content-Type: application/json');

$user_id = $_SESSION['user_id'] ?? null;
if (!$user_id) {
    echo json_encode(["status" => "error", "message" => "User not authenticated"]);
    exit;
}

$lead_id = $_GET['lead_id'] ?? null;
$month = $_GET['month'] ?? null; // Month number (1-12)
$week_number = $_GET['week'] ?? null; // Week number in the year (1-53)

if (!$lead_id || !$month || !$week_number) {
    echo json_encode(["status" => "error", "message" => "Labour Lead ID, Month, and Week number are required"]);
    exit;
}

// Function to get the start and end date of a week number in a given month and year
function getStartAndEndDateFromWeekMonth($week, $month, $year) {
    error_log("getStartAndEndDateFromWeekMonth() - Input: Week={$week}, Month={$month}, Year={$year}"); // Debug log input
    $dateTime = new DateTime();
    $dateTime->setISODate($year, $week, 1); // Set to Monday of the week
    error_log("DateTime after setISODate: " . $dateTime->format('Y-m-d')); // Debug log DateTime after setISODate
    if ($dateTime->format('n') != $month) {
        error_log("Month mismatch: DateTime Month=" . $dateTime->format('n') . ", Input Month=" . $month); // Debug log month mismatch
        $dateTime->modify('+7 days'); // Adjust if week is in the wrong month
        error_log("DateTime after modify('+7 days'): " . $dateTime->format('Y-m-d')); // Debug log DateTime after modify
    }
    if ($dateTime->format('n') != $month) {
        error_log("Still Month mismatch after adjust: DateTime Month=" . $dateTime->format('n') . ", Input Month=" . $month); // Debug log still month mismatch
        error_log("getStartAndEndDateFromWeekMonth() - Returning false"); // Debug log return false
        return false; // Week doesn't belong to the given month
    }
    $startDate = $dateTime->format('Y-m-d');
    $dateTime->modify('+6 days'); // Move to Sunday of the week
    $endDate = $dateTime->format('Y-m-d');
    error_log("getStartAndEndDateFromWeekMonth() - Returning: StartDate={$startDate}, EndDate={$endDate}"); // Debug log return values
    return ["start_date" => $startDate, "end_date" => $endDate];
}


$year = date('Y'); // Assume current year for simplicity - you might want to allow year selection as well
$weekDates = getStartAndEndDateFromWeekMonth($week_number, $month, $year);

if (!$weekDates) {
    error_log("getStartAndEndDateFromWeekMonth() returned false for Week={$week_number}, Month={$month}, Year={$year}"); // Debug log before error
    echo json_encode(["status" => "error", "message" => "Invalid week or month selection"]);
    exit;
}

$startDate = $weekDates['start_date'];
$endDate = $weekDates['end_date'];


$sql = "SELECT
    w.worker_name,
    j.job_name,
    a.date,
    a.present
FROM attendance a
JOIN workers w ON a.worker_id = w.worker_id
JOIN jobs j ON a.job_id = j.id
WHERE a.lead_id = ?
    AND a.date >= ? AND a.date <= ?
    AND a.user_id = ?
ORDER BY w.worker_name, a.date;";


$stmt = $conn->prepare($sql);
if (!$stmt) {
    error_log("Prepare statement failed: " . $conn->error);
    echo json_encode(["status" => "error", "message" => "Database error preparing statement"]);
    exit;
}

$stmt->bind_param("isss", $lead_id, $startDate, $endDate, $user_id);
if (!$stmt->execute()) {
    error_log("Execute failed: " . $stmt->error);
    echo json_encode(["status" => "error", "message" => "Database error executing query"]);
    exit;
}

$result = $stmt->get_result();
if (!$result) {
    error_log("Get result failed: " . $stmt->error);
    echo json_encode(["status" => "error", "message" => "Database error fetching results"]);
    exit;
}

$weeklyAttendanceData = [];
$workers = []; // To keep track of unique workers
$workerAttendance = []; // To store attendance for each worker by day


while ($row = $result->fetch_assoc()) {
    $workerName = $row['worker_name'];
    if (!in_array($workerName, $workers)) {
        $workers[] = $workerName;
        $workerAttendance[$workerName] = []; // Initialize attendance for the worker
    }
    $dayOfWeek = date('D', strtotime($row['date'])); // Get day of the week (Mon, Tue, Wed...)
    $workerAttendance[$workerName][$dayOfWeek] = $row['present'] == 1 ? "Present" : "Absent";
}


$weekDaysOrder = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
$weeklyDataForTable = [];

foreach ($workers as $workerName) {
    $workerRow = ["worker_name" => $workerName];
    $totalPresentDays = 0;
    foreach ($weekDaysOrder as $day) {
        $status = $workerAttendance[$workerName][$day] ?? 'Absent'; // Default to Absent if no record
        $workerRow[$day] = $status;
        if ($status === 'Present') {
            $totalPresentDays++;
        }
    }
    $workerRow["total_present"] = $totalPresentDays;
    $weeklyDataForTable[] = $workerRow;
}


echo json_encode(["status" => "success", "weeklyAttendance" => $weeklyDataForTable]);

$stmt->close();
$conn->close();

?>