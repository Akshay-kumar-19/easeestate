<?php
require 'db.php';
session_start();
$user_id = $_SESSION['user_id'];

$sql_leads = "SELECT lead_id, lead_name FROM labour_lead WHERE user_id = ?";
$stmt_leads = $conn->prepare($sql_leads);
$stmt_leads->bind_param("i", $user_id);
$stmt_leads->execute();
$result_leads = $stmt_leads->get_result();
$leads = $result_leads->fetch_all(MYSQLI_ASSOC);

function getWeekNumbersForMonth($month, $year) {
    $weekNumbers = [];
    $dateTime = new DateTime();
    $dateTime->setDate($year, $month, 1);

    $monthToCheck = $dateTime->format('n');

    while ($dateTime->format('n') == $monthToCheck) {
        $weekNumber = (int)$dateTime->format('W');
        if (!in_array($weekNumber, $weekNumbers)) {
            $weekNumbers[] = $weekNumber;
        }
        $dateTime->modify('+1 week');
    }
    sort($weekNumbers);
    return $weekNumbers;
}

$currentYear = date('Y');

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Attendance</title>
    <link rel="stylesheet" href="css/view_attendance.css">
    <link rel="icon" href="css/logos.png" sizes="32x32" type="image/png">
    <script src="js/view_attendance.js" defer></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const monthDropdown = document.getElementById('month_weekly');
            const weekDropdown = document.getElementById('week_weekly');

            monthDropdown.addEventListener('change', function() {
                const selectedMonth = this.value;
                const year = new Date().getFullYear(); // Use current year

                fetch('get_week_numbers.php?month=' + selectedMonth + '&year=' + year)
                    .then(response => response.json())
                    .then(data => {
                        weekDropdown.innerHTML = ''; // Clear existing options
                        if (data.status === 'success' && data.weekNumbers.length > 0) {
                            data.weekNumbers.forEach(week => {
                                const option = document.createElement('option');
                                option.value = week;
                                option.textContent = 'Week ' + week;
                                weekDropdown.appendChild(option);
                            });
                        } else {
                            const defaultOption = document.createElement('option');
                            defaultOption.textContent = '-- No weeks found --';
                            defaultOption.value = '';
                            weekDropdown.appendChild(defaultOption);
                        }
                    })
                    .catch(error => {
                        console.error('Error fetching week numbers:', error);
                        weekDropdown.innerHTML = '<option value="">-- Error loading weeks --</option>';
                    });
            });
        });
    </script>
</head>
<body>
    <div class="container">
        <h1>View Attendance</h1>

        <div class="input-section">
            <label for="lead_id">Select Labour Lead:</label>
            <select name="lead_id" id="lead_id">
                <option value="">-- Select Labour Lead --</option>
                <?php foreach ($leads as $lead) { ?>
                    <option value="<?php echo $lead['lead_id']; ?>"><?php echo htmlspecialchars($lead['lead_name']); ?></option>
                <?php } ?>
            </select>

            <label for="date_daily">Select Date:</label>
            <input type="date" id="date_daily" name="date_daily" value="<?php echo date('Y-m-d'); ?>">

            <label for="view_type">View Type:</label>
            <select name="view_type" id="view_type">
                <option value="daily">Daily</option>
                <option value="weekly">Weekly</option>
            </select>

            <div id="weekly_options" style="display: none;">
                <label for="month_weekly">Select Month:</label>
                <select name="month_weekly" id="month_weekly">
                    <?php
                    for ($month = 1; $month <= 12; $month++) {
                        $monthName = date('F', mktime(0, 0, 0, $month, 10));
                        echo "<option value=\"$month\">$monthName</option>";
                    }
                    ?>
                </select>

                <label for="week_weekly">Select Week Number:</label>
                <select name="week_weekly" id="week_weekly">
                    <option value="">-- Select Month First --</option>
                </select>
            </div>

            <button id="viewAttendanceBtn" class="view-btn">View Attendance</button>
        </div>

        <div id="attendance_data_daily" class="attendance-table" style="display: none;">
            <h2>Daily Attendance</h2>
            <table id="dailyAttendanceTable">
                <thead>
                    <tr>
                        <th>Worker Name</th>
                        <th>Job Name</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
            <p id="total_present_daily">Total Present: 0</p>
        </div>

        <div id="attendance_data_weekly" class="attendance-table" style="display: none;">
            <h2>Weekly Attendance</h2>
            <table id="weeklyAttendanceTable">
                <thead>
                    <tr>
                        <th>Worker Name</th>
                        <th>Monday</th>
                        <th>Tuesday</th>
                        <th>Wednesday</th>
                        <th>Thursday</th>
                        <th>Friday</th>
                        <th>Saturday</th>
                        <th>Sunday</th>
                        <th>Total Present</th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
            <p id="total_present_weekly">Total Present This Week: 0</p>
        </div>

    </div>
    <a href="dashboard.php" class="dashboard-button">
        <i class="fas fa-home"></i> Dashboard
    </a>
</body>
</html>