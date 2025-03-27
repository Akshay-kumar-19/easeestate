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
    <title>Areca Nut Lot Management</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="icon" href="css/logos.png" sizes="32x32" type="image/png">
    <link rel="stylesheet" href="css/arreca_management.css">
    
</head>
<body>
<div class="container">
    <h1>Areca Nut Lot Management</h1>

    <button class="add-areca-btn" onclick="openArecaModal()" id="addArecaButton">
        <i class="fas fa-plus"></i> Add Areca Lot
    </button>
    <button class="sell-areca-btn" onclick="openSellArecaLotsForm()" id="sellArecaButton">
        <i class="fas fa-minus"></i> Sell Areca Lot
    </button>
    <button class="summary-btn" onclick="openArecaSummarySection()" id="summaryButton">
        <i class="fas fa-chart-bar"></i> View Summary
    </button>


    <div id="addArecaForm" >
        <h3>Add New Areca Nut Lot</h3>
        <form id="arecaForm">
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

            <div class="modal-actions">
                <button type="button" onclick="closeArecaModal()">Cancel</button>
                <button type="button" id="saveArecaButton" onclick="addArecaLot()">Add Areca Lot</button>
            </div>
        </form>
    </div>

    <h3>Current Areca Nut Lots in Godown</h3>
    <table class="areca-table" id="arecaTable">
        <thead>
            <tr>
                <th>Lot Number</th>
                <th>Total Bags</th>
                <th>Total Weight (kg)</th>
                </tr>
        </thead>
        <tbody id="arecaTableBody">
            </tbody>
    </table>


    <div id="sellArecaLotsForm" >
        <h3>Sell Areca Nut Lots</h3>
        <div id="sellLotsList">
            </div>
        <button type="button" onclick="closeSellArecaLotsForm()">Cancel Sell</button>
    </div>

    <div id="arecaSummarySection" style="display: none;">
        <h3>Total Areca Nut Intake Summary</h3>

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
             <button type="button" onclick="loadArecaSummary()">Load Summary</button> </form>


        <div id="arecaSummary">
            <p>Loading summary...</p>
        </div>
        <button onclick="closeArecaSummary()">Close Summary</button>
    </div>


</div>

<script src="js/coffee_lot.js"></script>
<a href="dashboard.php" class="dashboard-button">
        <i class="fas fa-home"></i> Dashboard
    </a>
</body>
</html>

<script>
   

    document.addEventListener('DOMContentLoaded', function () {
        loadArecaLots(); 

        const addArecaForm = document.getElementById('addArecaForm');
        const addArecaButton = document.getElementById('addArecaButton');
        const sellArecaButton = document.getElementById('sellArecaButton');
        const summaryButton = document.getElementById('summaryButton');


        addArecaButton.addEventListener('click', openArecaModal);
        document.getElementById('cancelAddButton').addEventListener('click', closeArecaModal);
        sellArecaButton.addEventListener('click', openSellArecaLotsForm);
        summaryButton.addEventListener('click', openArecaSummarySection); 


    });

    function openArecaModal() {
        document.getElementById('addArecaForm').style.display = 'block';
        document.getElementById('addArecaButton').style.display = 'none'; 
        document.getElementById('sellArecaButton').style.display = 'none'; 
        document.getElementById('sellArecaLotsForm').style.display = 'none'; 
        document.getElementById('summaryButton').style.display = 'none'; 
        document.getElementById('arecaSummarySection').style.display = 'none'; 


    }

    function closeArecaModal() {
        document.getElementById('addArecaForm').style.display = 'none';
        document.getElementById('addArecaButton').style.display = 'block'; 
        document.getElementById('sellArecaButton').style.display = 'block'; 
        document.getElementById('summaryButton').style.display = 'block'; 

      
         document.getElementById('arecaForm').reset();
    }


    function loadArecaLots() {
        fetch('arecalot.php') 
            .then(response => response.json())
            .then(data => {
                const tableBody = document.getElementById('arecaTableBody');
                tableBody.innerHTML = ''; 
                if (data.length > 0) {
                    data.forEach(lot => {
                        let row = tableBody.insertRow();
                        row.insertCell(0).textContent = lot.lot_number;
                        row.insertCell(1).textContent = lot.total_bags;
                        row.insertCell(2).textContent = lot.total_weight_kg;
                        
                    });
                } else {
                    tableBody.innerHTML = '<tr><td colspan="3">No areca lots in godown.</td></tr>'; 
                }
            })
            .catch(error => console.error('Error fetching areca lots:', error));
    }

    function addArecaLot() {
        const formData = new FormData(document.getElementById('arecaForm'));
        formData.append('action', 'add'); 

        fetch('arreca_lot_action.php', { 
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                alert(data.message);
                closeArecaModal();
                loadArecaLots();
            } else {
                alert(data.message);
            }
        })
        .catch(error => console.error('Error adding areca lot:', error));
    }


    function openSellArecaLotsForm() {
        document.getElementById('sellArecaLotsForm').style.display = 'block';
        document.getElementById('sellArecaButton').style.display = 'none'; 
        document.getElementById('addArecaButton').style.display = 'none';
        document.getElementById('addArecaForm').style.display = 'none'; 
        document.getElementById('summaryButton').style.display = 'none'; 
        document.getElementById('arecaSummarySection').style.display = 'none'; 
        loadArecaLotsForSelling(); 
    }

    function closeSellArecaLotsForm() {
        document.getElementById('sellArecaLotsForm').style.display = 'none';
        document.getElementById('sellArecaButton').style.display = 'block'; 
        document.getElementById('addArecaButton').style.display = 'block'; 
        document.getElementById('summaryButton').style.display = 'block'; 


    }

    function loadArecaLotsForSelling() {
        fetch('arecalot.php') 
            .then(response => response.json())
            .then(data => {
                const sellLotsListDiv = document.getElementById('sellLotsList');
                sellLotsListDiv.innerHTML = ''; 

                if (data.length > 0) {
                    data.forEach(lot => {
                        const lotDiv = document.createElement('div');
                        lotDiv.className = 'lot-item'; 
                        lotDiv.innerHTML = `
                            <p>Lot Number: ${lot.lot_number}, Bags: ${lot.total_bags}, Weight: ${lot.total_weight_kg}kg</p>
                            <button class="sell-lot-button" onclick="sellArecaLot('${lot.lot_number}')">Sell Lot ${lot.lot_number}</button>
                            <hr/>
                        `;
                        sellLotsListDiv.appendChild(lotDiv);
                    });
                } else {
                    sellLotsListDiv.innerHTML = '<p>No areca lots available to sell.</p>';
                }
            })
            .catch(error => {
                console.error('Error fetching areca lots for selling:', error);
                alert('Error loading areca lots for selling. See console for details.');
            });
    }


    function sellArecaLot(lotNumber) {
        if (confirm('Are you sure you want to sell Lot Number: ' + lotNumber + '? This action cannot be undone.')) {
            fetch('sell_arreca_lot.php', { 
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded', 
                },
                body: 'lot_number=' + encodeURIComponent(lotNumber) 
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    alert('Areca lot ' + lotNumber + ' sold successfully!');
                    loadArecaLots(); 
                    loadArecaLotsForSelling(); 
                } else {
                    alert('Error selling areca lot ' + lotNumber + ': ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error selling areca lot:', error);
                alert('Error selling areca lot. Check console for details.');
            });
        }
    }

    function openArecaSummarySection() {
        document.getElementById('arecaSummarySection').style.display = 'block';
        document.getElementById('summaryButton').style.display = 'none'; 
        document.getElementById('addArecaButton').style.display = 'none'; 
        document.getElementById('sellArecaButton').style.display = 'none'; 
        document.getElementById('addArecaForm').style.display = 'none'; 
        document.getElementById('sellArecaLotsForm').style.display = 'none'; 
        loadArecaSummary(); 
    }


    function loadArecaSummary() {
        const summaryType = document.getElementById('summaryType').value;
        const summaryYear = document.getElementById('summaryYear').value;
        const summaryMonth = document.getElementById('summaryMonth').value;

        let params = `summaryType=${summaryType}`; 

        if (summaryType === 'yearly') {
            params += `&year=${summaryYear}`;
        } else if (summaryType === 'monthly') {
            params += `&year=${summaryYear}&month=${summaryMonth}`;
        }


        document.getElementById('arecaSummary').innerHTML = '<p>Loading summary...</p>'; 

        fetch(`areca_summary.php?${params}`) 
            .then(response => response.json())
            .then(data => {
                const summaryDiv = document.getElementById('arecaSummary');
                if (data.status === 'success') {
                    let summaryHTML = `<p><strong>Summary Period:</strong> ${getSummaryPeriodText(summaryType, summaryMonth, summaryYear)}</p>`;
                    summaryHTML += `<p><strong>Total Areca Lots Added:</strong> ${data.totalLots}</p>`;
                    summaryHTML += `<p><strong>Total Bags of Areca Nut:</strong> ${data.totalBags}</p>`;
                    summaryHTML += `<p><strong>Total Weight of Areca Nut:</strong> ${data.totalWeightKg} kg</p>`;


                    summaryHTML += `<p><strong>Areca Nut Lot Details:</strong></p>`; 
                    summaryHTML +=  `<table class="summary-table">
                            <thead>
                                <tr>
                                    <th>Lot Number</th>
                                    <th>Date Received</th>
                                    <th>Total Bags</th>
                                    <th>Total Weight (kg)</th>
                                    </tr>
                            </thead>
                            <tbody>
                                ${data.summaryByType.map(lot => `  <tr>
                                        <td>${lot.lot_number}</td>
                                        <td>${lot.date_received}</td>
                                        <td>${lot.total_bags}</td>
                                        <td>${lot.total_weight_kg}</td>
                                        </tr>
                                `).join('')}
                            </tbody>
                        </table>`;

                    if (data.summaryByType.length === 0) { 
                        summaryHTML += "<p>No areca nut lot records found for this summary period.</p>";
                    }


                    summaryDiv.innerHTML = summaryHTML;


                } else {
                    summaryDiv.innerHTML = `<p>Error loading summary: ${data.message}</p>`;
                }
            })
            .catch(error => {
                console.error('Error fetching areca summary:', error);
                document.getElementById('arecaSummary').innerHTML = `<p>Error loading summary. See console for details.</p>`;
            });
    }

    function closeArecaSummary() {
        document.getElementById('arecaSummarySection').style.display = 'none';
        document.getElementById('summaryButton').style.display = 'block'; 
        document.getElementById('addArecaButton').style.display = 'block'; 
        document.getElementById('sellArecaButton').style.display = 'block'; 
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