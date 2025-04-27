<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tool Management System</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="icon" href="css/logos.png" sizes="32x32" type="image/png">
    <link rel="stylesheet" href="css/tool_management.css">
    <link rel="icon" href="css/logos.png" sizes="32x32" type="image/png">
</head>
<body>
    <div class="container">
        <h1>Tool Management</h1>

        <button class="add-job-btn add-tool-btn" onclick="openToolModal()" id="addToolButton">
            <i class="fas fa-plus"></i> Add Tool
        </button>

        <div class="modal" id="toolModal" style="display:none;">
            <div class="modal-content">
                <div class="modal-header">
                    <h2 id="modalTitle">Add Tool</h2>
                    <button class="close-modal" onclick="closeToolModal()">&times;</button>
                </div>
                <form id="toolForm">
                    <input type="hidden" id="toolId" **name="toolId"**>  
                    <div class="form-group">
                        <label for="toolName">Tool Name:</label>
                        <input type="text" id="toolName" name="toolName" required>
                    </div>
                    <div class="form-group">
                        <label for="toolQuantity">Quantity:</label>
                        <input type="number" id="toolQuantity" name="toolQuantity" step="1" required>
                    </div>
                    <div class="modal-actions">
                        <button type="button" onclick="closeToolModal()">Cancel</button>
                        <button type="button" onclick="addTool(); return false;" id="submitBtn">Save</button>
                    </div>
                </form>
            </div>
        </div>

        <h3>Current Tools</h3>
        <table class="jobs-table tool-table">
            <thead>
                <tr>
                    <th>Tool Name</th>
                    <th>Total Quantity</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="toolTableBody">
            </tbody>
        </table>
    </div>

    <script src="js/tool.js?v=<?php echo time(); ?>"></script>
    <a href="dashboard.php" class="dashboard-button">
        <i class="fas fa-home"></i> Dashboard
    </a>
</body>
</html>