// Get DOM elements
const modal = document.getElementById("fertilizerModal");
const modalTitle = document.getElementById("modalTitle");
const fertilizerForm = document.getElementById("fertilizerForm");
const fertilizerIdInput = document.getElementById("fertilizerId");
const fertilizerNameInput = document.getElementById("fertilizerName");
const fertilizerTypeInput = document.getElementById("fertilizerType");
const quantityInput = document.getElementById("quantity");
const unitSelect = document.getElementById("unit");
const purchaseDateInput = document.getElementById("purchaseDate");
const submitBtn = document.getElementById("submitBtn");
const fertilizerTableBody = document.getElementById("fertilizerTableBody");

document.addEventListener('DOMContentLoaded', loadFertilizerInventory); // Load inventory on page load

function openModal() {
    modal.style.display = "block";
    modalTitle.textContent = "Add Fertilizer";
    fertilizerForm.reset(); // Clear form fields
    fertilizerIdInput.value = ""; // Important: Clear hidden ID for Add mode
    submitBtn.textContent = "Save";
}

function closeModal() {
    modal.style.display = "none";
}

function editFertilizer(id, name, type, quantity_kg, quantity_ml, unit, purchaseDate) {
    modal.style.display = "block";
    modalTitle.textContent = "Edit Fertilizer";
    fertilizerIdInput.value = id;
    fertilizerNameInput.value = name;
    fertilizerTypeInput.value = type;
    quantityInput.value = (unit === 'kg') ? quantity_kg : quantity_ml;
    unitSelect.value = unit;
    purchaseDateInput.value = purchaseDate;
    submitBtn.textContent = "Update";
}

function deleteFertilizer(id) {
    if (confirm("Are you sure you want to delete this fertilizer?")) {
        fetch('update_fertilizer_action.php', { // Use combined action file
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: new URLSearchParams({
                fertilizer_id: id,
                action: 'delete' // Specify action as delete
            }),
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                alert(data.message);
                loadFertilizerInventory();
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error deleting fertilizer.');
        });
    }
}


fertilizerForm.addEventListener("submit", function (event) {
    event.preventDefault(); // Prevent form from actually submitting

    const formData = new FormData(fertilizerForm);
    const action = fertilizerIdInput.value ? 'edit' : 'add'; // Determine action

    formData.append('action', action); // Append action to form data

    fetch('update_fertilizer_action.php', { // Use combined action file
        method: "POST",
        body: formData,
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            alert(data.message);
            closeModal();
            loadFertilizerInventory();
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error("Error:", error);
        alert("Error submitting form.");
    });
});

// Close modal if clicked outside of it
window.onclick = function (event) {
    if (event.target == modal) {
        closeModal();
    }
}

function loadFertilizerInventory() {
    fetch('update_fertilizer_action.php', { // Use combined action file for fetching
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: new URLSearchParams({
            action: 'fetch_inventory' // Specify action as fetch_inventory
        }),
    })
    .then(response => response.text()) // IMPORTANT: Changed to .text()
    .then(data => {
        document.getElementById('fertilizerTableBody').innerHTML = data;
    })
    .catch((error) => {
        console.error('Error:', error);
        alert('Error loading fertilizer inventory.');
    });
}