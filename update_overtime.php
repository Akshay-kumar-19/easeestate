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
    <title>Update Overtime</title>
    <link rel="stylesheet" href="css/update_overtime.css">
    <script src="js/update_overtime.js" defer></script>
    <script>
        var phpJobsData = <?php echo json_encode($jobs); ?>;
    </script>
    </head>
<body>
    <div class="container">
        <h1>Update Overtime</h1>

        <div class="header-controls">
            <label for="date">Date:</label>
            <input type="date" id="date" name="date" value="<?php echo date('Y-m-d'); ?>" readonly>

            <label for="lead_id">Select Labour Lead:</label>
            <select name="lead_id" id="lead_id">
                <option value="">-- Select Labour Lead --</option>
                <?php foreach ($leads as $lead) { ?>
                    <option value="<?php echo $lead['lead_id']; ?>"><?php echo htmlspecialchars($lead['lead_name']); ?></option>
                <?php } ?>
            </select>

            <label for="main_job_id">Select Job (For All):</label>
            <select name="main_job_id" id="main_job_id">
                <option value="">-- Select Job (Optional) --</option>
                <?php foreach ($jobs as $job) { ?>
                    <option value="<?php echo $job['id']; ?>"><?php echo htmlspecialchars($job['job_name']); ?></option>
                <?php } ?>
            </select>

            <label for="main_overtime_hours">Overtime Hours (For All):</label>
            <input type="number" id="main_overtime_hours" name="main_overtime_hours" placeholder="Hours" step="0.25">
        </div>

        <table id="workersTable">
            <thead>
                <tr>
                    <th>Worker Name</th>
                    <th>Job</th>
                    <th>Overtime Hours</th>
                </tr>
            </thead>
            <tbody>
                </tbody>
            <tfoot>
                <tr>
                    <td colspan="3">
                        <button id="submitOvertime" class="submit-btn">Submit Overtime</button>
                    </td>
                </tr>
            </tfoot>
        </table>
    </div>
    <a href="dashboard.php" class="dashboard-button">
        <i class="fas fa-home"></i> Dashboard
    </a>
</body>
</html>