
document.addEventListener('DOMContentLoaded', function () {
    loadCoffeeLots(); //loading coffee lot

    const addCoffeeForm = document.getElementById('addCoffeeForm');
    const addCoffeeButton = document.getElementById('addCoffeeButton');

    if (addCoffeeButton) { 
        addCoffeeButton.addEventListener('click', openCoffeeModal);
    }
    const cancelButton = document.getElementById('cancelAddButton');
    if (cancelButton) { 
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
        addCoffeeButton.style.display = 'none'; 
    }
}

function closeCoffeeModal() {
    const addCoffeeForm = document.getElementById('addCoffeeForm');
    const addCoffeeButton = document.getElementById('addCoffeeButton');
    if (addCoffeeForm) {
        addCoffeeForm.style.display = 'none';
    }
    if (addCoffeeButton) {
        addCoffeeButton.style.display = 'block'; 
    }
     
     const coffeeForm = document.getElementById('coffeeForm');
     if (coffeeForm) {
        coffeeForm.reset();
     }
}


function loadCoffeeLots() {
    fetch('coffeelot.php') 
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
        .catch(error => {
            console.error('Error fetching coffee lots:', error);
            alert('Error fetching coffee lots. See console for details.'); 
        });
}

function addCoffeeLot() {
    const lotBagsInput = document.getElementById('total_bags');
    const lotWeightInput = document.getElementById('total_weight_kg');
    const lotMoistureInput = document.getElementById('moisture_level');
    const lotNumberInput = document.getElementById('lot_number');
    const coffeeTypeInput = document.getElementById('coffee_type'); 


    const totalBags = lotBagsInput.value.trim();
    const totalWeightKg = lotWeightInput.value.trim();
    const moistureLevel = lotMoistureInput.value.trim();
    const lotNumber = lotNumberInput.value.trim();
    const coffeeType = coffeeTypeInput.value; 


    if (lotNumber === '' || coffeeType === '' || totalBags === '' || totalWeightKg === '' || moistureLevel === '') {
        alert('All fields are required.');
        return; 
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
    .catch(error => {
        console.error('Error adding coffee lot:', error);
        alert('Error adding coffee lot. Check console for details.');
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

function openSellCoffeeLotsForm() { 
    document.getElementById('sellCoffeeLotsForm').style.display = 'block';
    document.getElementById('sellCoffeeButton').style.display = 'none'; 
    document.getElementById('addCoffeeButton').style.display = 'none';
    document.getElementById('addCoffeeForm').style.display = 'none'; 
    loadCoffeeLotsForSelling(); 
}

function closeSellCoffeeLotsForm() { 
    document.getElementById('sellCoffeeLotsForm').style.display = 'none';
    document.getElementById('sellCoffeeButton').style.display = 'block'; 
    document.getElementById('addCoffeeButton').style.display = 'block'; 

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