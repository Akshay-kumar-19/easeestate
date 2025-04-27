<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

$tools_sql = "SELECT tool_id, tool_name, tool_quantity FROM tools WHERE user_id = ?";
$tools_stmt = $conn->prepare($tools_sql);
$tools_stmt->bind_param("i", $user_id);
$tools_stmt->execute();
$tools_result = $tools_stmt->get_result();
$tools = $tools_result->fetch_all(MYSQLI_ASSOC);
$tools_stmt->close();

$leads_sql = "SELECT lead_id, lead_name FROM labour_lead WHERE user_id = ?";
$leads_stmt = $conn->prepare($leads_sql);
$leads_stmt->bind_param("i", $user_id);
$leads_stmt->execute();
$leads_result = $leads_stmt->get_result();
$labour_leads = $leads_result->fetch_all(MYSQLI_ASSOC);
$leads_stmt->close();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tool Assignment</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="icon" href="css/logos.png" sizes="32x32" type="image/png">
    <link rel="stylesheet" href="css/tool_assignment.css">
    
</head>
<body>
    <div class="container">
        <h1>Tool Assignment</h1>

        <div class="tool-assignment-form">
            <h2>Assign Tool to Worker</h2>
            <form id="assignmentForm">
                <div class="form-group">
                    <label for="toolId">Tool:</label>
                    <select id="toolId" name="toolId" required>
                        <option value="">Select Tool</option>
                        <?php foreach ($tools as $tool): ?>
                            <option value="<?php echo htmlspecialchars($tool['tool_id']); ?>"><?php echo htmlspecialchars($tool['tool_name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="quantity">Quantity to Assign:</label>
                    <input type="number" id="quantity" name="quantity" value="1" min="1" required>
                </div>
                <div class="form-group">
                    <label for="leadId">Labour Lead:</label>
                    <select id="leadId" name="leadId" required onchange="loadWorkersByLead(this.value)">
                        <option value="">Select Labour Lead</option>
                        <?php foreach ($labour_leads as $lead): ?>
                            <option value="<?php echo htmlspecialchars($lead['lead_id']); ?>"><?php echo htmlspecialchars($lead['lead_name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="workerId">Worker:</label>
                    <select id="workerId" name="workerId" required>
                        <option value="">Select Labour Lead First</option>
                        </select>
                </div>
                <div class="form-group">
                    <label for="notes">Notes:</label>
                    <textarea id="notes" name="notes"></textarea>
                </div>
                <div class="modal-actions">
                    
                    <button type="button" onclick="assignTool(); return false;" id="assignBtn">Assign Tool</button>
                </div>
            </form>
        </div>

        <h3>Current Tool Assignments</h3>
        <table class="jobs-table tool-assignment-table">
            <thead>
                <tr>
                    <th>Tool Name</th>
                    <th>Quantity Assigned</th>
                    <th>Assigned Worker</th>
                    <th>Assignment Date</th>
                    <th>Return Date</th>
                    <th>Status</th>
                    <th>Notes</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="assignmentTableBody">
                </tbody>
        </table>
    </div>

    <div id="returnToolModal" class="modal">
        <div class="modal-content">
            <span class="close-button" onclick="closeReturnModal()">&times;</span>
            <h2>Return Tool</h2>
            <form id="returnForm">
                <input type="hidden" id="returnAssignmentId" name="assignmentId" value=""> <div class="form-group">
                    <label for="quantityReturn">Quantity to Return:</label>
                    <input type="number" id="quantityReturn" name="quantityReturn" value="" min="1" required>
                </div>
                <div class="form-group">
                    <label for="returnNotes">Return Notes (Optional):</label>
                    <textarea id="returnNotes" name="returnNotes"></textarea>
                </div>
                <div class="modal-actions">
                    <button type="button" onclick="closeReturnModal()">Cancel</button>
                    <button type="button" onclick="submitReturn(); return false;" id="returnBtn">Return Tool</button>
                </div>
            </form>
        </div>
    </div>
    <a href="dashboard.php" class="dashboard-button">
        <i class="fas fa-home"></i> Dashboard
    </a>

    <script src="js/tool_assignment.js"></script>
</body>
</html>