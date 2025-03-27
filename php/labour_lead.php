<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

$sql = "SELECT lead_name FROM labour_lead WHERE user_id = ?";
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
    <title>Manage Labour Leads</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="icon" href="css/logos.png" sizes="32x32" type="image/png">
    <link rel="stylesheet" href="css/labour.css">
</head>
<body>
    <div class="container">
        <h1>Manage Labour Leads</h1>

        
        <button class="add-lead-btn" onclick="openModal()">
            <i class="fas fa-plus"></i> Add Labour Lead
        </button>

        
        <table class="leads-table">
            <thead>
                <tr>
                    <th>Labour Lead Name</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="leadsTableBody">
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['lead_name']); ?></td>
                        <td class="action-buttons">
                            <button class="delete-btn" onclick="deleteLead('<?php echo htmlspecialchars($row['lead_name']); ?>')">Delete</button>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    
    <div class="modal" id="leadModal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 id="modalTitle">Add Labour Lead</h2>
                <button class="close-modal" onclick="closeModal()">&times;</button>
            </div>
            <form id="leadForm">
                <div class="form-group">
                    <label for="leadName">Labour Lead Name:</label>
                    <input type="text" id="leadName" required>
                </div>
                
                <div class="modal-actions">
                    <button type="button" onclick="closeModal()">Cancel</button>
                    <button type="submit" id="submitBtn">Save</button>
                </div>
            </form>
        </div>
    </div>
    <a href="dashboard.php" class="dashboard-button">
        <i class="fas fa-home"></i> Dashboard
    </a>
    <script src="js/labour_lead.js"></script>
</body>
</html>
