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

$sql_jobs = "SELECT id, job_name FROM jobs WHERE user_id = ?";
$stmt_jobs = $conn->prepare($sql_jobs);
$stmt_jobs->bind_param("i", $user_id);
$stmt_jobs->execute();
$result_jobs = $stmt_jobs->get_result();
$jobs = $result_jobs->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Attendance</title>
    <link rel="stylesheet" href="css/update_overtime.css">
    <link rel="icon" href="css/logos.png" sizes="32x32" type="image/png">
    <script src="js/update_attendance.js" defer></script>
    <script>
        var phpJobsData = <?php echo json_encode($jobs); ?>;
    </script>
</head>
<body>
    <div class="container">
        <h1>Update Attendance</h1>

        <div class="header-controls"> <label for="lead_id">Select Labour Lead:</label>
            <select name="lead_id" id="lead_id">
                <option value="">-- Select Labour Lead --</option>
                <?php foreach ($leads as $lead) { ?>
                    <option value="<?php echo $lead['lead_id']; ?>"><?php echo htmlspecialchars($lead['lead_name']); ?></option>
                <?php } ?>
            </select>

            <label for="main_job_id">Select Job (For All):</label>  <select name="main_job_id" id="main_job_id">
                <option value="">-- Select Job (For All) --</option>
                <?php foreach ($jobs as $job) { ?>
                    <option value="<?php echo $job['id']; ?>"><?php echo htmlspecialchars($job['job_name']); ?></option>
                <?php } ?>
            </select>

            <label for="date">Date:</label>
            <input type="date" id="date" name="date" value="<?php echo date('Y-m-d'); ?>" readonly>
        </div> <button id="presentAllBtn" class="present-btn">Present All</button>
        <button id="absentAllBtn" class="absent-btn">Absent All</button>

        <table id="workersTable">
            <thead>
                <tr>
                    <th>Worker Name</th>
                    <th>Job</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            </tbody>
        </table>

        <button id="submitAttendance" class="submit-btn">Submit Attendance</button>
    </div>
    <a href="dashboard.php" class="dashboard-button">
        <i class="fas fa-home"></i> Dashboard
    </a>
    
</body>
</html>