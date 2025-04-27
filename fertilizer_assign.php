<?php

session_start();
require 'db.php'; 


if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$current_date = date('F j, Y'); 

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Assign Fertilizer</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="icon" href="css/logos.png" sizes="32x32" type="image/png">
    <link rel="stylesheet" href="css/fertilizer_assign.css">
    
</head>
<body>
<div class="container form-container">
    <div style="margin-bottom: 15px;">
        <button type="button" onclick="window.location.href='fertilizer_usage_history.php'" class="view-history-button">View Assignment Details</button>
    </div>

    <!--<div class="date-display">Today's Date: <?php echo $current_date; ?></div>-->
    <h1>Assign Fertilizer to Field</h1>

    <form id="assignFertilizerFormElement">
        <div class="form-group">
            <label for="assign_date_used">Date Used:</label>
            <input type="date" id="assign_date_used" name="date_used" value="<?php echo date('Y-m-d'); ?>" required>
        </div>

        <div class="form-group">
            <label for="assign_team_lead">Team Lead:</label>
            <select id="assign_team_lead" name="team_lead_id" required>
                <option value="">Select Team Lead</option>
            </select>
        </div>

        <div class="form-group">
            <label for="assign_fertilizer_name">Fertilizer Name:</label>
            <select id="assign_fertilizer_name" name="fertilizer_name" required>
                <option value="">Select Fertilizer</option>
            </select>
        </div>

        <div class="form-group">
            <label for="assign_quantity_used">Quantity to Use:</label>
            <input type="number" step="0.01" id="assign_quantity_used" name="quantity_used" required>
        </div>

        <div class="form-group">
            <label for="assign_unit">Unit:</label>
            <select id="assign_unit" name="unit" required>
                <option value="kg">kg</option>
                <option value="ml">ml</option>
            </select>
        </div>

        <div class="form-group">
            <label for="assign_field_location">Field Location:</label>
            <input type="text" id="assign_field_location" name="field_location" required>
        </div>

        <div class="modal-actions">
           
            <button type="button" id="saveAssignFertilizerButton" class="save-button" onclick="assignFertilizer()">Assign Fertilizer</button>
        </div>
    </form>
    <div class="back-button-container">
    <button type="button" onclick="window.location.href='dashboard.php'" class="cancel-button">Back to Dashboard</button>
</div>

</div>


<script>
    document.addEventListener('DOMContentLoaded', function () {
        loadFertilizerNamesForDropdown();
        loadTeamLeadsForDropdown();
    });

    function assignFertilizer() {
        const formData = new FormData(document.getElementById('assignFertilizerFormElement'));

        fetch('fertilizer_assign_action.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                alert(data.message);
                window.location.href = 'fertilizer_usage_history.php';
            } else {
                alert(data.message);
            }
        })
        .catch(error => {
            console.error('Error assigning fertilizer:', error);
            alert('Error assigning fertilizer. See console for details.');
        });
    }

    function loadFertilizerNamesForDropdown() {
        fetch('fertilizer_inventory_fetch.php?action=names')
            .then(response => response.json())
            .then(data => {
                const selectElement = document.getElementById('assign_fertilizer_name');
                selectElement.innerHTML = '<option value="">Select Fertilizer</option>';
                data.forEach(fertilizer => {
                    let option = document.createElement('option');
                    option.value = fertilizer.fertilizer_name;
                    option.textContent = fertilizer.fertilizer_name;
                    selectElement.appendChild(option);
                });
            })
            .catch(error => {
                console.error('Error fetching fertilizer names:', error);
            });
    }

    function loadTeamLeadsForDropdown() {
        fetch('get_team_leads.php')
            .then(response => response.json())
            .then(data => {
                const selectElement = document.getElementById('assign_team_lead');
                selectElement.innerHTML = '<option value="">Select Team Lead</option>';
                data.forEach(lead => {
                    let option = document.createElement('option');
                    option.value = lead.labour_lead_id;
                    option.textContent = lead.labour_lead_name;
                    selectElement.appendChild(option);
                });
            })
            .catch(error => {
                console.error('Error fetching team leads:', error);
            });
    }
</script>
<a href="dashboard.php" class="dashboard-button">
        <i class="fas fa-home"></i> Dashboard
    </a>
</body>
</html>