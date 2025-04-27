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

document.addEventListener('DOMContentLoaded', loadFertilizerInventory);

function openModal() {
    modal.style.display = "block";
    modalTitle.textContent = "Add Fertilizer";
    fertilizerForm.reset();
    fertilizerIdInput.value = "";
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
        fetch('update_fertilizer_action.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: new URLSearchParams({
                fertilizer_id: id,
                action: 'delete'
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
    event.preventDefault();

    const formData = new FormData(fertilizerForm);
    const action = fertilizerIdInput.value ? 'edit' : 'add';

    formData.append('action', action);

    fetch('update_fertilizer_action.php', {
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

window.onclick = function (event) {
    if (event.target == modal) {
        closeModal();
    }
}

function loadFertilizerInventory() {
    fetch('update_fertilizer_action.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: new URLSearchParams({
            action: 'fetch_inventory'
        }),
    })
    .then(response => response.text())
    .then(data => {
        document.getElementById('fertilizerTableBody').innerHTML = data;
    })
    .catch((error) => {
        console.error('Error:', error);
        alert('Error loading fertilizer inventory.');
    });
}