<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fertilizer Inventory Management</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="icon" href="css/logos.png" sizes="32x32" type="image/png">
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>
    <div class="container">
        <h1>Fertilizer Inventory Management</h1>

        <div class="inventory-actions">
            <button onclick="showAddForm()" class="add-button">Add Fertilizer</button>
            <button onclick="loadFertilizerTable()" class="view-button">View Inventory</button>
            <button type="button" class="assign-button" onclick="window.location.href='assign_fertilizer.php'">Assign Fertilizer</button>
            <button type="button" class="view-button" onclick="window.location.href='view_fertilizer.php'">View Fertilizer Summary</button>

        </div>


        <div id="addFertilizerForm" style="display:none;">
            <h2>Add New Fertilizer</h2>
            <form id="fertilizerForm" action="update_fertilizer_action.php" method="POST">
                <input type="hidden" name="action" value="add">
                <div class="form-group">
                    <label for="fertilizer_name">Fertilizer Name:</label>
                    <input type="text" id="fertilizer_name" name="fertilizer_name" required>
                </div>
                <div class="form-group">
                    <label for="fertilizer_type">Fertilizer Type:</label>
                    <input type="text" id="fertilizer_type" name="fertilizer_type" required>
                </div>
                <div class="form-group">
                    <label for="quantity">Quantity:</label>
                    <input type="number" id="quantity" name="quantity" step="0.01" required>
                </div>
                <div class="form-group">
                    <label for="unit">Unit:</label>
                    <select id="unit" name="unit" required>
                        <option value="kg">kg</option>
                        <option value="ml">ml</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="purchase_date">Purchase Date:</label>
                    <input type="date" id="purchase_date" name="purchase_date" value="<?php echo date('Y-m-d'); ?>" required>
                </div>
                <div class="form-actions">
                    <button type="submit" class="save-btn">Add Fertilizer</button>
                    <button type="button" class="cancel-btn" onclick="hideAddForm()">Cancel</button>
                </div>
            </form>
            <div id="formMessage" class="message-area" style="display:none;"></div>
            <div id="formError" class="error-area" style="display:none;"></div>
        </div>

        <div id="editFertilizerForm" style="display:none;">
            <h2>Edit Fertilizer</h2>
            <form id="editForm">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" id="edit_fertilizer_id" name="fertilizer_id">
                <div class="form-group">
                    <label for="edit_fertilizer_name">Fertilizer Name:</label>
                    <input type="text" id="edit_fertilizer_name" name="fertilizer_name" required>
                </div>
                <div class="form-group">
                    <label for="edit_fertilizer_type">Fertilizer Type:</label>
                    <input type="text" id="edit_fertilizer_type" name="fertilizer_type" required>
                </div>
                <div class="form-group">
                    <label for="edit_quantity">Quantity:</label>
                    <input type="number" id="edit_quantity" name="quantity" step="0.01" required>
                </div>
                 <div class="form-group">
                    <label for="edit_unit">Unit:</label>
                    <select id="edit_unit" name="unit" required>
                        <option value="kg">kg</option>
                        <option value="ml">ml</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="edit_purchase_date">Purchase Date:</label>
                    <input type="date" id="edit_purchase_date" name="purchase_date" value="<?php echo date('Y-m-d'); ?>" required>
                </div>
                <div class="form-actions">
                    <button type="submit" class="save-btn">Save Changes</button>
                    <button type="button" class="cancel-btn" onclick="hideEditForm()">Cancel</button>
                </div>
            </form>
            <div id="editMessage" class="message-area" style="display:none;"></div>
            <div id="editError" class="error-area" style="display:none;"></div>
        </div>


        <div id="fertilizerInventoryTable" class="table-responsive">
            <h2>Fertilizer Inventory</h2>
            <table class="fertilizer-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Type</th>
                        <th>Quantity</th>
                        <th>Unit</th>
                        <th>Purchase Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="fertilizerTableBody">
                    </tbody>
            </table>
             <div id="tableMessage" class="message-area" style="display:none;"></div>
            <div id="tableError" class="error-area" style="display:none;"></div>
        </div>
    </div>

    <script src="js/update_fertilizer.js"></script>
    <a href="dashboard.php" class="dashboard-button">
        <i class="fas fa-home"></i> Dashboard
    </a>

</body>
</html>