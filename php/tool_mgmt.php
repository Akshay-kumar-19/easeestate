<?php
# tool_mgmt.php - Farm Tools Management Dashboard (Jobs-like Interface - AJAX)

session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$current_date = date('F j, Y');

// Fetch tool inventory data for display, including quantity
$toolInventory = [];
try {
    $sql = "SELECT
                t.tool_id,
                t.tool_name,
                COUNT(ti.inventory_id) AS quantity
            FROM tools t
            LEFT JOIN tool_inventory ti ON t.tool_id = ti.tool_id
            WHERE t.user_id = ?
            GROUP BY t.tool_id, t.tool_name
            ORDER BY t.tool_name";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $toolInventory[] = $row;
        }
    }
    $stmt->close();
} catch (Exception $e) {
    error_log("Error fetching tool inventory for dashboard: " . $e->getMessage());
    echo "Error fetching tool inventory data.";
    $toolInventory = [];
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Farm Tools</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="icon" href="css/logos.png" sizes="32x32" type="image/png">
    <link rel="stylesheet" href="css/styles.css">

</head>
<body>
    <div class="container">
        <h1>Manage Farm Tools</h1>

        <button class="add-tool-btn" onclick="openModal()">
            <i class="fas fa-plus"></i> Add Tool
        </button>

        <table class="tools-table">
            <thead>
                <tr>
                    <th>Tool Name</th>
                    <th>Quantity</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="toolsTableBody">
                <?php if (empty($toolInventory)): ?>
                    <tr><td colspan="3">No tools available.</td></tr>
                <?php else: ?>
                    <?php foreach ($toolInventory as $tool): ?>
                        <tr data-tool-id="<?php echo $tool['tool_id']; ?>">
                            <td><?php echo htmlspecialchars($tool['tool_name']); ?></td>
                            <td><?php echo htmlspecialchars($tool['quantity']); ?></td>
                            <td class="action-buttons">
                                <button class="edit-btn" onclick="editTool(<?php echo $tool['tool_id']; ?>, '<?php echo htmlspecialchars($tool['tool_name']); ?>')">Edit</button>
                                <button class="delete-btn" onclick="deleteTool(<?php echo $tool['tool_id']; ?>)">Delete</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <div class="modal" id="toolModal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 id="modalTitle">Add Tool</h2>
                <button type="button" class="close-modal" onclick="closeModal()">&times;</button>
            </div>
            <form id="toolForm">
                <input type="hidden" id="toolId">
                <div class="form-group">
                    <label for="toolName">Tool Name:</label>
                    <input type="text" id="toolName" name="tool_name" required>
                </div>

                <div class="form-group">
                    <label for="toolQuantity">Quantity:</label>
                    <input type="number" id="toolQuantity" name="tool_quantity" value="1" required>
                </div>

                <div class="modal-actions">
                    <button type="button" onclick="closeModal()">Cancel</button>
                    <button type="button" id="submitBtn" onclick="saveTool()">Save</button>
                </div>
            </form>
        </div>
    </div>

    <script src="js/tool_dashboard.js"></script>
</body>
</html>