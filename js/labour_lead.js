document.addEventListener("DOMContentLoaded", function () {
    fetchLabourLeads();

    const leadForm = document.getElementById('leadForm');
    leadForm.addEventListener('submit', function (event) {
        event.preventDefault();
        const leadName = document.getElementById('leadName').value.trim();

        if (leadName) {
            if (isValidLeadName(leadName)) {
                addLabourLead(leadName);
            } else {
                alert('Labour lead name should only contain text.');
            }
        } else {
            alert('Please enter a labour lead name.');
        }
    });
});

function fetchLabourLeads() {
    fetch('labour_lead_action.php', {
        method: 'POST',
        body: new URLSearchParams({
            action: 'fetch'
        })
    })
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                alert('Error loading labour leads');
            } else {
                displayLabourLeads(data);
            }
        })
        .catch(error => {
            console.error('Error fetching labour leads:', error);
            alert('Error loading labour leads');
        });
}

function displayLabourLeads(data) {
    const tableBody = document.getElementById('leadsTableBody');
    tableBody.innerHTML = '';

    data.forEach(lead => {
        const row = document.createElement('tr');
        row.innerHTML = `
            <td>${lead.lead_name}</td>
            <td class="action-buttons">
                <button class="delete-btn" onclick="deleteLabourLead('${lead.lead_name}')">Delete</button>
            </td>
        `;
        tableBody.appendChild(row);
    });
}

function openModal() {
    document.getElementById('leadModal').style.display = 'block';
}

function closeModal() {
    document.getElementById('leadModal').style.display = 'none';
}

function isValidLeadName(leadName) {
    return /^[a-zA-Z\s]+$/.test(leadName);
}

function addLabourLead(leadName) {
    const formData = new FormData();
    formData.append('action', 'add');
    formData.append('lead_name', leadName);

    fetch('labour_lead_action.php', {
        method: 'POST',
        body: formData,
    })
        .then(response => response.json())
        .then(data => {
            if (data.message) {
                alert(data.message);
                if (data.message === 'Labour lead added successfully.') {
                    fetchLabourLeads();
                    closeModal();
                }
            } else {
                alert('Error adding labour lead');
            }
        })
        .catch(error => {
            console.error('Error adding labour lead:', error);
            alert('Error adding labour lead');
        });
}

function deleteLabourLead(leadName) {
    const confirmDelete = confirm(`Are you sure you want to delete the labour lead: ${leadName}?`);
    if (confirmDelete) {
        const formData = new FormData();
        formData.append('action', 'delete');
        formData.append('lead_name', leadName);

        fetch('labour_lead_action.php', {
            method: 'POST',
            body: formData,
        })
            .then(response => response.json())
            .then(data => {
                if (data.message) {
                    alert(data.message);
                    if (data.message === 'Labour lead deleted successfully.') {
                        fetchLabourLeads();
                    }
                } else {
                    alert('Error deleting labour lead');
                }
            })
            .catch(error => {
                console.error('Error deleting labour lead:', error);
                alert('Error deleting labour lead');
            });
    }
}