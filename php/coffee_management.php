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
    <title>Coffee Lot Management</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="icon" href="css/logos.png" sizes="32x32" type="image/png">
    <link rel="stylesheet" href="css/coffee_management.css">
    
</head>
<body>
<div class="container">
    <h1>Coffee Lot Management</h1>

    <button class="add-coffee-btn" onclick="openCoffeeModal()" id="addCoffeeButton">
        <i class="fas fa-plus"></i> Add Coffee Lot
    </button>
    <button class="sell-coffee-btn" onclick="openSellCoffeeLotsForm()" id="sellCoffeeButton">
        <i class="fas fa-minus"></i> Sell Coffee Lot
    </button>
    <button class="summary-btn" onclick="openCoffeeSummarySection()" id="summaryButton">
        <i class="fas fa-chart-bar"></i> View Summary
    </button>


    <div id="addCoffeeForm" >
        <h3>Add New Coffee Lot</h3>
        <form id="coffeeForm">
            <div class="form-group">
                <label for="lot_number">Lot Number:</label>
                <input type="number" id="lot_number" name="lot_number" required>
            </div>

            <div class="form-group">
                <label for="date_received">Date Received:</label>
                <input type="date" id="date_received" name="date_received" required value="<?php echo date('Y-m-d'); ?>" readonly>
            </div>

            <div class="form-group">
                <label for="coffee_type">Coffee Type:</label>
                <select id="coffee_type" name="coffee_type" required>
                    <option value="Parchment">Parchment</option>
                    <option value="Cherry">Cherry</option>
                </select>
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
                <button type="button" onclick="closeCoffeeModal()">Cancel</button>
                <button type="button" id="saveCoffeeButton" onclick="addCoffeeLot()">Add Coffee Lot</button>
            </div>
        </form>
    </div>

    <h3>Current Coffee Lots in Godown</h3>
    <table class="coffee-table" id="coffeeTable">
        <thead>
            <tr>
                <th>Lot Number</th>
                <th>Coffee Type</th>
                <th>Total Bags</th>
                <th>Total Weight (kg)</th>
                <th>Moisture Level</th>
            </tr>
        </thead>
        <tbody id="coffeeTableBody">
            </tbody>
    </table>


    <div id="sellCoffeeLotsForm" >
        <h3>Sell Coffee Lots</h3>
        <div id="sellLotsList">
            </div>
        <button type="button" onclick="closeSellCoffeeLotsForm()">Cancel Sell</button>
    </div>

    <div id="coffeeSummarySection" style="display: none;">
        <h3>Total Coffee Intake Summary</h3>

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
                for ($i = $currentYear; $i >= 2020; $i--) {
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
             <button type="button" onclick="loadCoffeeSummary()">Load Summary</button> </form>


        <div id="coffeeSummary">
            <p>Loading summary...</p>
        </div>
        <button onclick="closeCoffeeSummary()">Close Summary</button>
    </div>


</div>
<a href="dashboard.php" class="dashboard-button">
        <i class="fas fa-home"></i> Dashboard
    </a>

<script src="js/coffee_lot.js"></script>
</body>
</html>

<script>
    

    document.addEventListener('DOMContentLoaded', function () {
        loadCoffeeLots(); 

        const addCoffeeForm = document.getElementById('addCoffeeForm');
        const addCoffeeButton = document.getElementById('addCoffeeButton');
        const sellCoffeeButton = document.getElementById('sellCoffeeButton');
        const summaryButton = document.getElementById('summaryButton');


        addCoffeeButton.addEventListener('click', openCoffeeModal);
        document.getElementById('cancelAddButton').addEventListener('click', closeCoffeeModal);
        sellCoffeeButton.addEventListener('click', openSellCoffeeLotsForm); 
        summaryButton.addEventListener('click', openCoffeeSummarySection); 


    });

    function openCoffeeModal() {
        document.getElementById('addCoffeeForm').style.display = 'block';
        document.getElementById('addCoffeeButton').style.display = 'none'; 
        document.getElementById('sellCoffeeButton').style.display = 'none'; 
        document.getElementById('sellCoffeeLotsForm').style.display = 'none';
        document.getElementById('summaryButton').style.display = 'none'; 
        document.getElementById('coffeeSummarySection').style.display = 'none'; 


    }

    function closeCoffeeModal() {
        document.getElementById('addCoffeeForm').style.display = 'none';
        document.getElementById('addCoffeeButton').style.display = 'block'; 
        document.getElementById('sellCoffeeButton').style.display = 'block'; 
        document.getElementById('summaryButton').style.display = 'block'; 

         
         document.getElementById('coffeeForm').reset();
    }


    function loadCoffeeLots() {
        fetch('coffeelot.php') 
            .then(response => response.json())
            .then(data => {
                const tableBody = document.getElementById('coffeeTableBody');
                tableBody.innerHTML = ''; 
                if (data.length > 0) {
                    data.forEach(lot => {
                        let row = tableBody.insertRow();
                        row.insertCell(0).textContent = lot.lot_number;
                        row.insertCell(1).textContent = lot.coffee_type;
                        row.insertCell(2).textContent = lot.total_bags;
                        row.insertCell(3).textContent = lot.total_weight_kg;
                        row.insertCell(4).textContent = lot.moisture_level;
                    });
                } else {
                    tableBody.innerHTML = '<tr><td colspan="5">No coffee lots in godown.</td></tr>'; 
                }
            })
            .catch(error => console.error('Error fetching coffee lots:', error));
    }

    function addCoffeeLot() {
        const formData = new FormData(document.getElementById('coffeeForm'));
        formData.append('action', 'add'); 

        fetch('coffee_lot_action.php', { 
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                alert(data.message);
                closeCoffeeModal();
                loadCoffeeLots();
            } else {
                alert(data.message);
            }
        })
        .catch(error => console.error('Error adding coffee lot:', error));
    }


    function openSellCoffeeLotsForm() {
        document.getElementById('sellCoffeeLotsForm').style.display = 'block';
        document.getElementById('sellCoffeeButton').style.display = 'none'; 
        document.getElementById('addCoffeeButton').style.display = 'none'; 
        document.getElementById('addCoffeeForm').style.display = 'none'; 
        document.getElementById('summaryButton').style.display = 'none'; 
        document.getElementById('coffeeSummarySection').style.display = 'none'; 
        loadCoffeeLotsForSelling(); 
    }

    function closeSellCoffeeLotsForm() {
        document.getElementById('sellCoffeeLotsForm').style.display = 'none';
        document.getElementById('sellCoffeeButton').style.display = 'block';
        document.getElementById('addCoffeeButton').style.display = 'block'; 
        document.getElementById('summaryButton').style.display = 'block'; 

    }

    function loadCoffeeLotsForSelling() {
        fetch('coffeelot.php') 
            .then(response => response.json())
            .then(data => {
                const sellLotsListDiv = document.getElementById('sellLotsList');
                sellLotsListDiv.innerHTML = ''; 

                if (data.length > 0) {
                    data.forEach(lot => {
                        const lotDiv = document.createElement('div');
                        lotDiv.className = 'lot-item'; 
                        lotDiv.innerHTML = `
                            <p>Lot Number: ${lot.lot_number}, Type: ${lot.coffee_type}, Bags: ${lot.total_bags}, Weight: ${lot.total_weight_kg}kg, Moisture: ${lot.moisture_level}</p>
                            <button class="sell-lot-button" onclick="sellCoffeeLot('${lot.lot_number}')">Sell Lot ${lot.lot_number}</button>
                            <hr/>
                        `;
                        sellLotsListDiv.appendChild(lotDiv);
                    });
                } else {
                    sellLotsListDiv.innerHTML = '<p>No coffee lots available to sell.</p>';
                }
            })
            .catch(error => {
                console.error('Error fetching coffee lots for selling:', error);
                alert('Error loading coffee lots for selling. See console for details.');
            });
    }


    function sellCoffeeLot(lotNumber) {
        if (confirm('Are you sure you want to sell Lot Number: ' + lotNumber + '? This action cannot be undone.')) {
            fetch('sell_coffee_lot.php', { 
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded', 
                },
                body: 'lot_number=' + encodeURIComponent(lotNumber) 
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    alert('Coffee lot ' + lotNumber + ' sold successfully!');
                    loadCoffeeLots(); 
                    loadCoffeeLotsForSelling(); 
                } else {
                    alert('Error selling coffee lot ' + lotNumber + ': ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error selling coffee lot:', error);
                alert('Error selling coffee lot. Check console for details.');
            });
        }
    }

    function openCoffeeSummarySection() {
        document.getElementById('coffeeSummarySection').style.display = 'block';
        document.getElementById('summaryButton').style.display = 'none'; 
        document.getElementById('addCoffeeButton').style.display = 'none'; 
        document.getElementById('sellCoffeeButton').style.display = 'none'; 
        document.getElementById('addCoffeeForm').style.display = 'none';
        document.getElementById('sellCoffeeLotsForm').style.display = 'none'; 
        loadCoffeeSummary(); 
    }


    function loadCoffeeSummary() {
        const summaryType = document.getElementById('summaryType').value;
        const summaryYear = document.getElementById('summaryYear').value;
        const summaryMonth = document.getElementById('summaryMonth').value;

        let params = `summaryType=${summaryType}`; 

        if (summaryType === 'yearly') {
            params += `&year=${summaryYear}`;
        } else if (summaryType === 'monthly') {
            params += `&year=${summaryYear}&month=${summaryMonth}`;
        }


        document.getElementById('coffeeSummary').innerHTML = '<p>Loading summary...</p>'; 

        fetch(`coffee_summary.php?${params}`) 
            .then(response => response.json())
            .then(data => {
                const summaryDiv = document.getElementById('coffeeSummary');
                if (data.status === 'success') {
                    let summaryHTML = `<p><strong>Summary Period:</strong> ${getSummaryPeriodText(summaryType, summaryMonth, summaryYear)}</p>`;
                    summaryHTML += `<p><strong>Total Coffee Lots Added:</strong> ${data.totalLots}</p>`;
                    summaryHTML += `<p><strong>Total Bags of Coffee:</strong> ${data.totalBags}</p>`;
                    summaryHTML += `<p><strong>Total Weight of Coffee:</strong> ${data.totalWeightKg} kg</p>`;

                    summaryHTML += `<p><strong>Parchment Coffee:</strong></p>`; 
                    summaryHTML += `<ul>
                        <li><strong>Total Bags:</strong> ${data.parchmentSummary.total_bags}</li>
                        <li><strong>Total Weight:</strong> ${data.parchmentSummary.total_weight_kg} kg</li>
                    </ul>`;

                    summaryHTML += `<p><strong>Cherry Coffee:</strong></p>`; 
                    summaryHTML += `<ul>
                        <li><strong>Total Bags:</strong> ${data.cherrySummary.total_bags}</li>
                        <li><strong>Total Weight:</strong> ${data.cherrySummary.total_weight_kg} kg</li>
                    </ul>`;


                    summaryHTML +=  `<table class="summary-table">
                            <thead>
                                <tr>
                                    <th>Coffee Type</th>
                                    <th>Total Bags</th>
                                    <th>Total Weight (kg)</th>
                                </tr>
                            </thead>
                            <tbody>
                                ${data.summaryByType.map(item => `
                                    <tr>
                                        <td>${item.coffee_type}</td>
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
                console.error('Error fetching coffee summary:', error);
                document.getElementById('coffeeSummary').innerHTML = `<p>No summary.</p>`;
            });
    }

    function closeCoffeeSummary() {
        document.getElementById('coffeeSummarySection').style.display = 'none';
        document.getElementById('summaryButton').style.display = 'block'; 
        document.getElementById('addCoffeeButton').style.display = 'block'; 
        document.getElementById('sellCoffeeButton').style.display = 'block'; 
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
        else { 
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