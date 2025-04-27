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

$sql_jobs = "SELECT id, job_name FROM jobs WHERE user_id = ? AND job_name IN ('cofffee plucking', 'pepper plucking', 'arreca plucking')";
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
    <title>View Crops Plucked</title>
    <link rel="stylesheet" href="css/view_crops.css">
    <link rel="icon" href="css/logos.png" sizes="32x32" type="image/png">
    <script src="js/view_crops_plucked.js" defer></script>
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
        <h1>View Crops Plucked Data</h1>
        <div class="input-section">
            <label for="view_crop_type">Select Crop:</label>
            <select name="view_crop_type" id="view_crop_type">
                <option value="">-- Select Crop --</option>
                <?php foreach ($jobs as $job): ?>
                    <option value="<?php echo $job['id']; ?>"><?php echo $job['job_name']; ?></option>
                <?php endforeach; ?>
            </select>

            <label for="view_plucked_date">Select Date:</label>
            <input type="date" id="view_plucked_date" name="view_plucked_date" value="<?php echo date('Y-m-d'); ?>">

            <button type="button" id="viewCropsBtn">View</button>
        </div>

        <div id="crops_plucked_data_section" style="display: none;">
            <h2>Crops Plucked Data</h2>
            <div id="cropsPluckedTableContainer">
                </div>
                <div id="cropsPluckedTableContainer">
                </div>

            <div id="cropsPluckedTotalsCoffee" style="display:none; margin-top: 20px; padding: 15px; background-color: #f8f9fa; border-radius: 8px; border: 1px solid #e0e0e0;">
                <h3>Total Plucked (Coffee)</h3>
                <p><strong>Total Ripe (KG):</strong> <span id="totalRipeKg"></span></p>
                <p><strong>Total Unripe (KG):</strong> <span id="totalUnripeKg"></span></p>
                <p><strong>Grand Total (KG):</strong> <span id="grandTotalKg"></span></p>
            </div>

            <div id="cropsPluckedTotalsPepper" style="display:none; margin-top: 20px; padding: 15px; background-color: #f8f9fa; border-radius: 8px; border: 1px solid #e0e0e0;">
                <h3>Total Plucked (Pepper)</h3>
                <p><strong>Total Plucked (KG):</strong> <span id="totalPepperKg"></span></p>
            </div>

            <div id="cropsPluckedTotalsAreca" style="display:none; margin-top: 20px; padding: 15px; background-color: #f8f9fa; border-radius: 8px; border: 1px solid #e0e0e0;">
                <h3>Total Plucked (Areca)</h3>
                <p><strong>Total Kone:</strong> <span id="totalArecaKone"></span></p>
            </div>
        </div>
    </div>
    <a href="dashboard.php" class="dashboard-button">
        <i class="fas fa-home"></i> Dashboard
    </a>
</body>
</html>