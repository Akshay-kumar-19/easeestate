<!DOCTYPE html>
<html>
<head>
    <title>Tool Dashboard</title>
    <link rel="stylesheet" href="css/tool_dashboard.css">
    <script src="js/tool_view.js"></script>
    <link rel="icon" href="css/logos.png" sizes="32x32" type="image/png">
</head>
<body>
    <div class="container">
        <h1>Tool Dashboard</h1>

        <div class="dashboard-buttons">
            <button id="btnAssignedTools" onclick="loadAssignedTools()">Assigned Tools</button>
            <button id="btnReturnedTools" onclick="loadReturnedTools()">Returned Tools</button>
            <button id="btnToolInventory" onclick="loadToolInventory()">Tool Inventory</button>
        </div>

        <div id="dashboardTableContainer">
            </div>
    </div>
    <a href="dashboard.php" class="dashboard-button">
        <i class="fas fa-home"></i> Dashboard
    </a>
</body>
</html>