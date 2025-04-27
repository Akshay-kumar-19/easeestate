<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
 header("Location: login.php");
 exit();
}

$user_id = $_SESSION['user_id'];

$sql = "SELECT * FROM jobs WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Manage Jobs</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
 <link rel="icon" href="css/logos.png" sizes="32x32" type="image/png">
 <link rel="stylesheet" href="css/job.css">
</head>
<body>
<div class="container">
<h1>Manage Jobs</h1>

 <button class="add-job-btn" onclick="openModal()">
<i class="fas fa-plus"></i> Add Job
 </button>

<table class="jobs-table">
<thead>
<tr>
 <th>Job Name</th>
 <th>Daily Wage (₹)</th>
<th>Rate per kg (₹)</th>
                        <th>Overtime Rate (₹/Hour)</th>
<th>Actions</th>
 </tr>
</thead>
 <tbody id="jobsTableBody">
 <?php while ($row = $result->fetch_assoc()): ?>
 <tr>
<td><?php echo htmlspecialchars($row['job_name']); ?></td>
<td>₹<?php echo number_format($row['daily_wage'], 2); ?></td>
<td>₹<?php echo number_format($row['per_kg_rate'], 2); ?></td>
                            <td>₹<?php echo number_format($row['overtime_hourly_rate'], 2); ?></td>
 <td class="action-buttons">
 <button class="edit-btn" onclick="editJob(<?php echo $row['id']; ?>, '<?php echo htmlspecialchars($row['job_name']); ?>', '<?php echo $row['daily_wage']; ?>', '<?php echo $row['per_kg_rate']; ?>', '<?php echo $row['overtime_hourly_rate']; ?>')">Edit</button>
 <button class="delete-btn" onclick="deleteJob(<?php echo $row['id']; ?>)">Delete</button>
</td>
 </tr>
 <?php endwhile; ?>
</tbody>
 </table>
 </div>

 <div class="modal" id="jobModal">
<div class="modal-content">
 <div class="modal-header">
<h2 id="modalTitle">Add Job</h2>
 <button class="close-modal" onclick="closeModal()">&times;</button>
 </div>
 <form id="jobForm">
 <input type="hidden" id="jobId">  <div class="form-group">
 <label for="jobName">Job Name:</label>
<input type="text" id="jobName" required>
 </div>

<div class="form-group">
 <label for="dailyWage">Daily Wage (₹):</label>
 <input type="number" id="dailyWage" step="0.01" required>
 </div>
 
 <div class="form-group">
<label for="perKgRate">Rate per kg (₹):</label>
<input type="number" id="perKgRate" step="0.01" required>
 </div>

                        <div class="form-group">
 <label for="overtimeRate">Overtime Rate (₹/Hour):</label>
 <input type="number" id="overtimeRate" step="0.01" value="0.00" required>
 </div>

<div class="modal-actions">
 <button type="button" onclick="closeModal()">Cancel</button>
 <button type="submit" id="submitBtn">Save</button>
</div>
</form>
 </div>
 </div>

 <script src="js/job.js"></script>
 <a href="dashboard.php" class="dashboard-button">
        <i class="fas fa-home"></i> Dashboard
    </a>
</body>
</html>