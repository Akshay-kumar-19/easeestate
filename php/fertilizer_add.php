<?php

session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$current_date = date('Y-m-d');

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Purchase/Update Fertilizer (Simplified)</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="icon" href="css/logos.png" sizes="32x32" type="image/png">
    <link rel="stylesheet" href="css/fertilizer_add.css">
</head>
<body>
<div class="container form-container">
    <!--<div class="date-display">Today's Date: <?php echo $current_date; ?></div>-->
    <h1>Update Fertilizer Inventory</h1>

    <form id="fertilizerForm">
        <div class="form-group">
            <label for="fertilizer_name">Fertilizer Name:</label>
            <input type="text" id="fertilizer_name" name="fertilizer_name" required>
        </div>

        <div class="form-group">
            <label for="unit">Unit:</label>
            <select id="unit" name="unit" required>
                <option value="kg">kg</option>
                <option value="ml">ml</option>
            </select>
        </div>

        <div class="form-group">
            <label for="quantity">Quantity to Add:</label>
            <input type="number" step="0.01" id="quantity" name="quantity" required>
        </div>


        <div class="modal-actions">
            <button type="button" onclick="window.location.href='dashboard.php'" class="cancel-button" id="cancelAddFertilizerButton">Cancel</button>
            <button type="button" id="saveFertilizerButton" class="save-button" onclick="addFertilizer()">Update Fertilizer</button>
        </div>
    </form>
</div>

<script>
    function addFertilizer() {
        const formData = new FormData(document.getElementById('fertilizerForm'));

        fetch('fertilizer_add_action.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                alert(data.message);
                window.location.href = 'fertilizer_add.php';
            } else {
                alert(data.message);
            }
        })
        .catch(error => {
            console.error('Error adding fertilizer:', error);
            alert('Error updating fertilizer inventory. See console for details.');
        });
    }

    document.getElementById('fertilizerForm').addEventListener('submit', function(event) {
        const quantityInput = document.getElementById('quantity');
        if (isNaN(parseFloat(quantityInput.value)) || parseFloat(quantityInput.value) <= 0) {
            alert('Quantity must be a positive number.');
            event.preventDefault();
        }
    });
</script>
<a href="dashboard.php" class="dashboard-button">
        <i class="fas fa-home"></i> Dashboard
    </a>
</body>
</html>