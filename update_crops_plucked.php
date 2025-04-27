<?php
require 'db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

$sql_leads = "SELECT lead_id, lead_name FROM labour_lead WHERE user_id = ?";
$stmt_leads = $conn->prepare($sql_leads);
$stmt_leads->bind_param("i", $user_id);
$stmt_leads->execute();
$result_leads = $stmt_leads->get_result();
$leads = $result_leads->fetch_all(MYSQLI_ASSOC);

$sql_jobs = "SELECT id, job_name, daily_wage, per_kg_rate FROM jobs
             WHERE user_id = ? AND job_name IN ('cofffee plucking', 'pepper plucking', 'arreca plucking')";
$stmt_jobs = $conn->prepare($sql_jobs);
$stmt_jobs->bind_param("i", $user_id);
$stmt_jobs->execute();
$result_jobs = $stmt_jobs->get_result();
$jobs = $result_jobs->fetch_all(MYSQLI_ASSOC);

$coffee_job_id = 1;
$pepper_job_id = 2;
$areca_job_id = 3;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Crops</title>
    <link rel="stylesheet" href="css/update_crops.css">
    <link rel="icon" href="css/logos.png" sizes="32x32" type="image/png">
    <script src="js/update_crops_plucked.js" defer></script>
    <script>
        const jobs = <?php echo json_encode($jobs); ?>;
        const leads = <?php echo json_encode($leads); ?>;
        const coffee_job_id = <?php echo json_encode($coffee_job_id); ?>;
        const pepper_job_id = <?php echo json_encode($pepper_job_id); ?>;
        const areca_job_id = <?php echo json_encode($areca_job_id); ?>;
    </script>
</head>
<body>
    <div class="container">
        <h1>Update Crops</h1>
        <div class="input-section">
            <label for="plucked_date">Date:</label>
            <input type="date" id="plucked_date" name="plucked_date" value="<?php echo date('Y-m-d'); ?>" readonly>

            <label for="crop_type">Select Crop:</label>
            <div class="crop-selection">
                <select name="crop_type" id="crop_type">
                    <option value="">-- Select Crop --</option>
                    <?php foreach ($jobs as $job): ?>
                        <option value="<?php echo $job['id']; ?>"><?php echo $job['job_name']; ?></option>
                    <?php endforeach; ?>
                </select>
                <button type="button" id="updateWorkersBtn">Update Workers</button>
            </div>

            <div class="rate-wage-section">
                <button type="button" id="per_kg_btn" class="rate-wage-btn active">Per KG Rate</button>
                <button type="button" id="daily_wage_btn" class="rate-wage-btn">Daily Wage</button>
            </div>
        </div>

        <div id="workers_section" style="display: none;">
            <h2>Worker Plucking Details</h2>
            <table id="workersTable">
                <thead>
                    <tr>
                        <th>Worker Name</th>
                        <th>Labour Lead</th>
                        <th data-crop-type="coffee">Ripe (KG)</th>
                        <th data-crop-type="coffee">Unripe (KG)</th>
                        <th data-crop-type="coffee">Total Plucked (KG)</th>
                        <th data-crop-type="pepper">                  </th>
                        <th data-crop-type="pepper">Total Plucked (KG)</th>
                        <th data-crop-type="areca">                       </th>
                        <th data-crop-type="areca">Total Kone</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>

        <div class="submit-section" id="submit_section" style="display: none;">
            <button id="saveCropsPluckedBtn" class="submit-btn">Save Plucked Data</button>
        </div>
    </div>
    <a href="dashboard.php" class="dashboard-button">
        <i class="fas fa-home"></i> Dashboard
    </a>
</body>
</html>
