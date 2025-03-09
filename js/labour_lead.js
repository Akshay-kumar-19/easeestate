document.addEventListener("DOMContentLoaded", function () {
    // Fetch all labor leads when the page loads
    fetchLabourLeads();

    // Handle form submission for adding a labor lead
    const leadForm = document.getElementById('leadForm');
    leadForm.addEventListener('submit', function (event) {
        event.preventDefault();
        const leadName = document.getElementById('leadName').value.trim();

        if (leadName) {
            if (isValidLeadName(leadName)) { // Validate lead name here
                addLabourLead(leadName);
            } else {
                alert('Labour lead name should only contain text.'); // Alert for invalid input
            }
        } else {
            alert('Please enter a labour lead name.');
        }
    });
});

// Function to fetch all labour leads
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

// Function to display the labour leads in the table
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

// Function to open the modal
function openModal() {
    document.getElementById('leadModal').style.display = 'block';
}

// Function to close the modal
function closeModal() {
    document.getElementById('leadModal').style.display = 'none';
}

// Function to validate labour lead name (accepts only text)
function isValidLeadName(leadName) {
    return /^[a-zA-Z\s]+$/.test(leadName);
}

// Function to add a labour lead
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
                    fetchLabourLeads(); // Reload the labour leads after adding
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

// Function to delete a labour lead
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
                        fetchLabourLeads(); // Reload the labour leads after deletion
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