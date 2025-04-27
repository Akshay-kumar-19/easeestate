<?php


session_start();
require 'db.php'; // Database connection

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php"); //  Adjust login page path if needed
    exit();
}

$user_id = $_SESSION['user_id'];

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pepper Lot Management</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="icon" href="css/logos.png" sizes="32x32" type="image/png">
    <link rel="stylesheet" href="css/pepper_management.css">
    
</head>
<body>
<div class="container">
    <h1>Pepper Lot Management</h1>

    <button class="add-pepper-btn" onclick="openPepperModal()" id="addPepperButton">
        <i class="fas fa-plus"></i> Add Pepper Lot
    </button>
    <button class="sell-pepper-btn" onclick="openSellPepperLotsForm()" id="sellPepperButton">
        <i class="fas fa-minus"></i> Sell Pepper Lot
    </button>
    <button class="summary-btn" onclick="openPepperSummarySection()" id="summaryButton">
        <i class="fas fa-chart-bar"></i> View Summary
    </button>


    <div id="addPepperForm" >
        <h3>Add New Pepper Lot</h3>
        <form id="pepperForm">
            <div class="form-group">
                <label for="lot_number">Lot Number:</label>
                <input type="number" id="lot_number" name="lot_number" required>
            </div>

            <div class="form-group">
                <label for="date_received">Date Received:</label>
                <input type="date" id="date_received" name="date_received" required value="<?php echo date('Y-m-d'); ?>" readonly>
            </div>


            <div class="form-group">
                <label for="total_bags">Total Bags:</label>
                <input type="number" id="total_bags" name="total_bags" required>
            </div>

            <div class="form-group">
                <label for="total_weight_kg">Total Weight (kg):</label>
                <input type="number" step="0.01" id="total_weight_kg" name="total_weight_kg" required>
            </div>

            <div class="form-group">
                <label for="moisture_level">Moisture Level:</label>
                <input type="number" id="moisture_level" name="moisture_level" required>
            </div>

            <div class="modal-actions">
                <button type="button" onclick="closePepperModal()">Cancel</button>
                <button type="button" id="savePepperButton" onclick="addPepperLot()">Add Pepper Lot</button>
            </div>
        </form>
    </div>

    <h3>Current Pepper Lots in Godown</h3>
    <table class="pepper-table" id="pepperTable">
        <thead>
            <tr>
                <th>Lot Number</th>
                <th>Total Bags</th>
                <th>Total Weight (kg)</th>
                <th>Moisture Level</th>
            </tr>
        </thead>
        <tbody id="pepperTableBody">
            </tbody>
        </table>


        <div id="sellPepperLotsForm" >
            <h3>Sell Pepper Lots</h3>
            <div id="sellLotsList">
                </div>
            <button type="button" onclick="closeSellPepperLotsForm()">Cancel Sell</button>
        </div>

        <div id="pepperSummarySection" style="display: none;">
            <h3>Total Pepper Intake Summary</h3>

            <form id="summaryFilterForm">
                <label for="summaryType">Summary Period:</label>
                <select id="summaryType" name="summaryType" onchange="updateSummaryFilters()">
                    <option value="total">Total Summary</option>
                    <option value="yearly">Yearly Summary</option>
                    <option value="monthly">Monthly Summary</option>
                </select>

                <label for="summaryYear" id="yearLabel" style="display:none;">Year:</label>
                <select id="summaryYear" name="summaryYear" style="display:none;">
                    <?php
                    $currentYear = date("Y");
                    for ($i = $currentYear; $i >= 2020; $i--) { // Example: Show years from 2020 to current
                        echo "<option value=\"$i\">$i</option>";
                    }
                    ?>
                </select>

                <label for="summaryMonth" id="monthLabel" style="display:none;">Month:</label>
                <select id="summaryMonth" name="summaryMonth" style="display:none;">
                    <option value="01">January</option>
                    <option value="02">February</option>
                    <option value="03">March</option>
                    <option value="04">April</option>
                    <option value="05">May</option>
                    <option value="06">June</option>
                    <option value="07">July</option>
                    <option value="08">August</option>
                    <option value="09">September</option>
                    <option value="10">October</option>
                    <option value="11">November</option>
                    <option value="12">December</option>
                </select>
                 <button type="button" onclick="loadPepperSummary()">Load Summary</button> </form>


            <div id="pepperSummary">
                <p>Loading summary...</p>
            </div>
            <button onclick="closePepperSummary()">Close Summary</button>
        </div>


    </div>

    <script src="js/coffee_lot.js"></script>
    <a href="dashboard.php" class="dashboard-button">
        <i class="fas fa-home"></i> Dashboard
    </a>
    </body>
    </html>

    <script>
        //  JavaScript functions to handle pepper lot management - updated for pepper and 2 tables

        document.addEventListener('DOMContentLoaded', function () {
            loadPepperLots(); //  Load pepper lots when page loads

            const addPepperForm = document.getElementById('addPepperForm');
            const addPepperButton = document.getElementById('addPepperButton');
            const sellPepperButton = document.getElementById('sellPepperButton');
            const summaryButton = document.getElementById('summaryButton');


            addPepperButton.addEventListener('click', openPepperModal);
            document.getElementById('cancelAddButton').addEventListener('click', closePepperModal);
            sellPepperButton.addEventListener('click', openSellPepperLotsForm); // Event listener for Sell Pepper Lot button
            summaryButton.addEventListener('click', openPepperSummarySection); // Event listener for Summary button


        });

        function openPepperModal() {
            document.getElementById('addPepperForm').style.display = 'block';
            document.getElementById('addPepperButton').style.display = 'none'; // Hide Add button when form is open
            document.getElementById('sellPepperButton').style.display = 'none'; // Hide Sell button when form is open
            document.getElementById('sellPepperLotsForm').style.display = 'none'; // Ensure Sell Lots Form is hidden
            document.getElementById('summaryButton').style.display = 'none'; // Hide Summary button
            document.getElementById('pepperSummarySection').style.display = 'none'; // Ensure Summary Section is hidden


        }

        function closePepperModal() {
            document.getElementById('addPepperForm').style.display = 'none';
            document.getElementById('addPepperButton').style.display = 'block'; // Show Add button when form is closed
            document.getElementById('sellPepperButton').style.display = 'block'; // Show Sell button when form is closed
            document.getElementById('summaryButton').style.display = 'block'; // Show Summary button

             // Reset form fields on cancel if needed
             document.getElementById('pepperForm').reset();
        }


        function loadPepperLots() {
            fetch('pepperlot.php') //  Path to your pepperlot.php file
                .then(response => response.json())
                .then(data => {
                    const tableBody = document.getElementById('pepperTableBody');
                    tableBody.innerHTML = ''; //  Clear existing table data
                    if (data.length > 0) {
                        data.forEach(lot => {
                            let row = tableBody.insertRow();
                            row.insertCell(0).textContent = lot.lot_number;
                            row.insertCell(1).textContent = lot.total_bags;
                            row.insertCell(2).textContent = lot.total_weight_kg;
                            row.insertCell(3).textContent = lot.moisture_level;
                        });
                    } else {
                        tableBody.innerHTML = '<tr><td colspan="4">No pepper lots in godown.</td></tr>'; // colspan adjusted to 4
                    }
                })
                .catch(error => console.error('Error fetching pepper lots:', error));
        }

        function addPepperLot() {
            const formData = new FormData(document.getElementById('pepperForm'));
            formData.append('action', 'add'); // Add action parameter

            fetch('pepper_lot_action.php', { // Path to your pepper_lot_action.php
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    alert(data.message);
                    closePepperModal();
                    loadPepperLots();
                } else {
                    alert(data.message);
                }
            })
            .catch(error => console.error('Error adding pepper lot:', error));
        }


        function openSellPepperLotsForm() {
            document.getElementById('sellPepperLotsForm').style.display = 'block';
            document.getElementById('sellPepperButton').style.display = 'none'; // Hide Sell button
            document.getElementById('addPepperButton').style.display = 'none'; // Hide Add button
            document.getElementById('addPepperForm').style.display = 'none'; // Ensure Add Form is hidden
            document.getElementById('summaryButton').style.display = 'none'; // Hide Summary button
            document.getElementById('pepperSummarySection').style.display = 'none'; // Ensure Summary Section is hidden
            loadPepperLotsForSelling(); // Function to load pepper lots into the sell form
        }

        function closeSellPepperLotsForm() {
            document.getElementById('sellPepperLotsForm').style.display = 'none';
            document.getElementById('sellPepperButton').style.display = 'block'; // Show Sell button again
            document.getElementById('addPepperButton').style.display = 'block'; // Show Add button again
            document.getElementById('summaryButton').style.display = 'block'; // Show Summary button


        }

        function loadPepperLotsForSelling() {
            fetch('pepperlot.php') // Fetch pepper lots as before
                .then(response => response.json())
                .then(data => {
                    const sellLotsListDiv = document.getElementById('sellLotsList');
                    sellLotsListDiv.innerHTML = ''; // Clear any existing list

                    if (data.length > 0) {
                        data.forEach(lot => {
                            const lotDiv = document.createElement('div');
                            lotDiv.className = 'lot-item'; // You can style these items with CSS if needed
                            lotDiv.innerHTML = `
                                <p>Lot Number: ${lot.lot_number}, Bags: ${lot.total_bags}, Weight: ${lot.total_weight_kg}kg, Moisture: ${lot.moisture_level}</p>
                                <button class="sell-lot-button" onclick="sellPepperLot('${lot.lot_number}')">Sell Lot ${lot.lot_number}</button>
                                <hr/>
                            `;
                            sellLotsListDiv.appendChild(lotDiv);
                        });
                    } else {
                        sellLotsListDiv.innerHTML = '<p>No pepper lots available to sell.</p>';
                    }
                })
                .catch(error => {
                    console.error('Error fetching pepper lots for selling:', error);
                    alert('Error loading pepper lots for selling. See console for details.');
                });
        }


        function sellPepperLot(lotNumber) {
            if (confirm('Are you sure you want to sell Lot Number: ' + lotNumber + '? This action cannot be undone.')) {
                fetch('sell_pepper_lot.php', { // Path to your sell_pepper_lot.php file
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded', // Or 'application/json' if sending JSON
                    },
                    body: 'lot_number=' + encodeURIComponent(lotNumber) // Send lot_number in the request body
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        alert('Pepper lot ' + lotNumber + ' sold successfully!');
                        loadPepperLots(); // Reload the pepper lot table to update the view
                        loadPepperLotsForSelling(); // Refresh the sell lots form as well
                    } else {
                        alert('Error selling pepper lot ' + lotNumber + ': ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error selling pepper lot:', error);
                    alert('Error selling pepper lot. Check console for details.');
                });
            }
        }

        function openPepperSummarySection() {
            document.getElementById('pepperSummarySection').style.display = 'block';
            document.getElementById('summaryButton').style.display = 'none'; // Hide Summary button
            document.getElementById('addPepperButton').style.display = 'none'; // Hide Add button
            document.getElementById('sellPepperButton').style.display = 'none'; // Hide Sell button
            document.getElementById('addPepperForm').style.display = 'none'; // Ensure Add Form is hidden
            document.getElementById('sellPepperLotsForm').style.display = 'none'; // Ensure Sell Lots Form is hidden
            loadPepperSummary(); // Load summary when section is opened, default to total summary
        }


        function loadPepperSummary() {
            const summaryType = document.getElementById('summaryType').value;
            const summaryYear = document.getElementById('summaryYear').value;
            const summaryMonth = document.getElementById('summaryMonth').value;

            let params = `summaryType=${summaryType}`; // Base parameters

            if (summaryType === 'yearly') {
                params += `&year=${summaryYear}`;
            } else if (summaryType === 'monthly') {
                params += `&year=${summaryYear}&month=${summaryMonth}`;
            }


            document.getElementById('pepperSummary').innerHTML = '<p>Loading summary...</p>'; // Reset loading message

            fetch(`pepper_summary.php?${params}`) // Path to your pepper_summary.php file, now with parameters
                .then(response => response.json())
                .then(data => {
                    const summaryDiv = document.getElementById('pepperSummary');
                    if (data.status === 'success') {
                        let summaryHTML = `<p><strong>Summary Period:</strong> ${getSummaryPeriodText(summaryType, summaryMonth, summaryYear)}</p>`;
                        summaryHTML += `<p><strong>Total Pepper Lots Added:</strong> ${data.totalLots}</p>`;
                        summaryHTML += `<p><strong>Total Bags of Pepper:</strong> ${data.totalBags}</p>`;
                        summaryHTML += `<p><strong>Total Weight of Pepper:</strong> ${data.totalWeightKg} kg</p>`;


                        summaryHTML += `<p><strong>Total Pepper:</strong></p>`; // Generic Pepper Details Heading - No type breakdown
                        summaryHTML += `<ul>
                            <li><strong>Total Bags:</strong> ${data.pepperSummary.total_bags}</li>
                            <li><strong>Total Weight:</strong> ${data.pepperSummary.total_weight_kg} kg</li>
                        </ul>`;


                        summaryHTML +=  `<table class="summary-table">
                                <thead>
                                    <tr>
                                        <th>Pepper Type</th>
                                        <th>Total Bags</th>
                                        <th>Total Weight (kg)</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    ${data.summaryByType.map(item => `
                                        <tr>
                                            <td>Pepper</td>
                                            <td>${item.total_bags}</td>
                                            <td>${item.total_weight_kg}</td>
                                        </tr>
                                    `).join('')}
                                </tbody>
                            </table>`;

                        summaryDiv.innerHTML = summaryHTML;


                    } else {
                        summaryDiv.innerHTML = `<p>Error loading summary: ${data.message}</p>`;
                    }
                })
                .catch(error => {
                    console.error('Error fetching pepper summary:', error);
                    document.getElementById('pepperSummary').innerHTML = `<p>Error loading summary. See console for details.</p>`;
                });
        }

        function closePepperSummary() {
            document.getElementById('pepperSummarySection').style.display = 'none';
            document.getElementById('summaryButton').style.display = 'block'; // Show Summary button again
            document.getElementById('addPepperButton').style.display = 'block'; // Show Add button again
            document.getElementById('sellPepperButton').style.display = 'block'; // Show Sell button again
        }

        function updateSummaryFilters() {
            const summaryType = document.getElementById('summaryType').value;
            const yearLabel = document.getElementById('yearLabel');
            const yearSelect = document.getElementById('summaryYear');
            const monthLabel = document.getElementById('monthLabel');
            const monthSelect = document.getElementById('summaryMonth');

            if (summaryType === 'yearly') {
                yearLabel.style.display = 'inline';
                yearSelect.style.display = 'inline';
                monthLabel.style.display = 'none';
                monthSelect.style.display = 'none';
            } else if (summaryType === 'monthly') {
                yearLabel.style.display = 'inline';
                yearSelect.style.display = 'inline';
                monthLabel.style.display = 'inline';
                monthSelect.style.display = 'inline';
            }
            else { // total summary
                yearLabel.style.display = 'none';
                yearSelect.style.display = 'none';
                monthLabel.style.display = 'none';
                monthSelect.style.display = 'none';
            }
        }

        function getSummaryPeriodText(summaryType, summaryMonth, summaryYear) {
            if (summaryType === 'monthly') {
                const monthNames = ["January", "February", "March", "April", "May", "June",
                    "July", "August", "September", "October", "November", "December"
                ];
                const monthIndex = parseInt(summaryMonth) - 1;
                return `${monthNames[monthIndex]} ${summaryYear}`;
            } else if (summaryType === 'yearly') {
                return `Year ${summaryYear}`;
            } else {
                return 'Total Intake';
            }
        }


    </script>