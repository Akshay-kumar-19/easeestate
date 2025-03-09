// js/coffee_lot.js - Complete Code (No Update Needed - but ensure you have the latest version) - CORRECTED FULL CODE
document.addEventListener('DOMContentLoaded', function () {
    loadCoffeeLots(); //  Load coffee lots when page loads

    const addCoffeeForm = document.getElementById('addCoffeeForm');
    const addCoffeeButton = document.getElementById('addCoffeeButton');

    if (addCoffeeButton) { // Check if the button exists before adding listener
        addCoffeeButton.addEventListener('click', openCoffeeModal);
    }
    const cancelButton = document.getElementById('cancelAddButton');
    if (cancelButton) { // Check if the cancel button exists
        cancelButton.addEventListener('click', closeCoffeeModal);
    }

});

function openCoffeeModal() {
    const addCoffeeForm = document.getElementById('addCoffeeForm');
    const addCoffeeButton = document.getElementById('addCoffeeButton');
    if (addCoffeeForm) {
        addCoffeeForm.style.display = 'block';
    }
    if (addCoffeeButton) {
        addCoffeeButton.style.display = 'none'; // Hide Add button when form is open
    }
}

function closeCoffeeModal() {
    const addCoffeeForm = document.getElementById('addCoffeeForm');
    const addCoffeeButton = document.getElementById('addCoffeeButton');
    if (addCoffeeForm) {
        addCoffeeForm.style.display = 'none';
    }
    if (addCoffeeButton) {
        addCoffeeButton.style.display = 'block'; // Show Add button when form is closed
    }
     // Reset form fields on cancel if needed
     const coffeeForm = document.getElementById('coffeeForm');
     if (coffeeForm) {
        coffeeForm.reset();
     }
}


function loadCoffeeLots() {
    fetch('coffeelot.php') //  Path to your coffeelot.php file
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            const tableBody = document.getElementById('coffeeTableBody');
            if (!tableBody) {
                console.error('Table body element not found!');
                return;
            }
            tableBody.innerHTML = ''; //  Clear existing table data
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
                tableBody.innerHTML = '<tr><td colspan="5">No coffee lots in godown.</td></tr>'; // colspan adjusted to 5
            }
        })
        .catch(error => {
            console.error('Error fetching coffee lots:', error);
            alert('Error fetching coffee lots. See console for details.'); // Alert user about fetch error
        });
}

function addCoffeeLot() {
    const lotBagsInput = document.getElementById('total_bags');
    const lotWeightInput = document.getElementById('total_weight_kg');
    const lotMoistureInput = document.getElementById('moisture_level');
    const lotNumberInput = document.getElementById('lot_number');
    const coffeeTypeInput = document.getElementById('coffee_type'); // Get Coffee Type input


    const totalBags = lotBagsInput.value.trim();
    const totalWeightKg = lotWeightInput.value.trim();
    const moistureLevel = lotMoistureInput.value.trim();
    const lotNumber = lotNumberInput.value.trim();
    const coffeeType = coffeeTypeInput.value; // No trim needed for select


    if (lotNumber === '' || coffeeType === '' || totalBags === '' || totalWeightKg === '' || moistureLevel === '') {
        alert('All fields are required.');
        return; // Stop form submission if any field is blank
    }


    if (isNaN(totalBags) || isNaN(totalWeightKg) || isNaN(moistureLevel) || isNaN(lotNumber)) {
        alert('Total Bags, Total Weight (kg), Moisture Level, and Lot Number must be numbers.');
        lotBagsInput.value = '';
        lotWeightInput.value = '';
        lotMoistureInput.value = '';
        lotNumberInput.value = '';
        return;
    }


    const formData = new FormData(document.getElementById('coffeeForm'));
    formData.append('action', 'add'); // Add action parameter

    fetch('coffee_lot_action.php', { // Path to your coffee_lot_action.php
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
    .catch(error => {
        console.error('Error adding coffee lot:', error);
        alert('Error adding coffee lot. Check console for details.');
    });
}


function sellCoffeeLot(lotNumber) {  //  <-- No changes needed here, ensure you have this function from previous response - CORRECTED FULL CODE
    if (confirm('Are you sure you want to sell Lot Number: ' + lotNumber + '? This action cannot be undone.')) {
        fetch('sell_coffee_lot.php', { // Path to your sell_coffee_lot.php file
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded', // Or 'application/json' if sending JSON
            },
            body: 'lot_number=' + encodeURIComponent(lotNumber) // Send lot_number in the request body
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                alert('Coffee lot ' + lotNumber + ' sold successfully!');
                loadCoffeeLots(); // Reload the coffee lot table to update the view
                loadCoffeeLotsForSelling(); // Refresh the sell lots form as well
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

function openSellCoffeeLotsForm() { // <-- No changes needed here, ensure you have this function from previous response - CORRECTED FULL CODE
    document.getElementById('sellCoffeeLotsForm').style.display = 'block';
    document.getElementById('sellCoffeeButton').style.display = 'none'; // Hide Sell button
    document.getElementById('addCoffeeButton').style.display = 'none'; // Hide Add button
    document.getElementById('addCoffeeForm').style.display = 'none'; // Ensure Add Form is hidden
    loadCoffeeLotsForSelling(); // Function to load coffee lots into the sell form
}

function closeSellCoffeeLotsForm() { // <-- No changes needed here, ensure you have this function from previous response - CORRECTED FULL CODE
    document.getElementById('sellCoffeeLotsForm').style.display = 'none';
    document.getElementById('sellCoffeeButton').style.display = 'block'; // Show Sell button again
    document.getElementById('addCoffeeButton').style.display = 'block'; // Show Add button again

}

function loadCoffeeLotsForSelling() { // <-- No changes needed here, ensure you have this function from previous response - CORRECTED FULL CODE
    fetch('coffeelot.php') // Fetch coffee lots as before
        .then(response => response.json())
        .then(data => {
            const sellLotsListDiv = document.getElementById('sellLotsList');
            sellLotsListDiv.innerHTML = ''; // Clear any existing list

            if (data.length > 0) {
                data.forEach(lot => {
                    const lotDiv = document.createElement('div');
                    lotDiv.className = 'lot-item'; // You can style these items with CSS if needed
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