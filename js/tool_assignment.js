function loadWorkersByLead(leadId) {
    const workerDropdown = document.getElementById('workerId');
    workerDropdown.innerHTML = '<option value="">Loading Workers...</option>';

    if (leadId) {
        fetch('get_workers_by_lead.php?leadId=' + leadId)
            .then(response => response.json())
            .then(workers => {
                workerDropdown.innerHTML = '<option value="">Select Worker</option>';
                if (workers && workers.length > 0) {
                    workers.forEach(worker => {
                        let option = document.createElement('option');
                        option.value = worker.worker_id;
                        option.textContent = worker.worker_name;
                        workerDropdown.appendChild(option);
                    });
                } else {
                    workerDropdown.innerHTML += '<option value="">No workers under this lead</option>';
                }
            })
            .catch(error => {
                console.error('Error fetching workers:', error);
                workerDropdown.innerHTML = '<option value="">Error loading workers</option>';
            });
    } else {
        workerDropdown.innerHTML = '<option value="">Select Labour Lead First</option>';
    }
}

function assignTool() {
    const toolId = document.getElementById('toolId').value;
    const workerId = document.getElementById('workerId').value;
    const quantity = document.getElementById('quantity').value;
    const notes = document.getElementById('notes').value;

    if (!toolId || !workerId || !quantity) {
        alert("Please select a tool, worker, and quantity.");
        return;
    }

    const formData = new FormData();
    formData.append('toolId', toolId);
    formData.append('workerId', workerId);
    formData.append('quantity', quantity);
    formData.append('notes', notes);

    fetch('tool_assign_action.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            alert(data.message);
            resetAssignmentForm();
            loadToolAssignments();
        } else {
            alert("Error: " + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error assigning tool. Check console.');
    });
}


function resetAssignmentForm() {
    document.getElementById('assignmentForm').reset();
    const workerDropdown = document.getElementById('workerId');
    workerDropdown.innerHTML = '<option value="">Select Labour Lead First</option>';
}

function loadToolAssignments() {
    const assignmentTableBody = document.getElementById('assignmentTableBody');
    assignmentTableBody.innerHTML = '<tr><td colspan="8">Loading assignments...</td></tr>';

    fetch('get_tool_assignments.php')
        .then(response => response.json())
        .then(assignments => {
            assignmentTableBody.innerHTML = '';
            if (assignments && assignments.length > 0) {
                assignments.forEach(assignment => {
                    let row = assignmentTableBody.insertRow();
                    row.insertCell(0).textContent = assignment.tool_name;
                    row.insertCell(1).textContent = assignment.quantity_assigned;
                    row.insertCell(2).textContent = assignment.worker_name;

                    const assignmentDate = new Date(assignment.assignment_date);
                    const formattedDate = assignmentDate.toLocaleDateString();
                    row.insertCell(3).textContent = formattedDate;

                    let returnDateCell = row.insertCell(4);
                    if (assignment.return_date) {
                        const returnDate = new Date(assignment.return_date);
                        const formattedReturnDate = returnDate.toLocaleDateString();
                        returnDateCell.textContent = formattedReturnDate;
                    } else {
                        returnDateCell.textContent = 'Not Returned';
                    }

                    row.insertCell(5).textContent = assignment.status;
                    row.insertCell(6).textContent = assignment.notes || '';
                    let actionsCell = row.insertCell(7);
                    actionsCell.innerHTML = `
                        <button onclick="openReturnModal(${assignment.assignment_id}, ${assignment.quantity_assigned})" class="return-btn">Return</button>
                    `;
                });
            } else {
                assignmentTableBody.innerHTML = '<tr><td colspan="8">No tool assignments found.</td></tr>';
            }
        })
        .catch(error => {
            console.error('Error fetching tool assignments:', error);
            assignmentTableBody.innerHTML = '<tr><td colspan="8">Error loading assignments.</td></tr>';
        });
}

function openReturnModal(assignmentId, assignedQuantity) {
    document.getElementById('returnAssignmentId').value = assignmentId;
    document.getElementById('quantityReturn').value = assignedQuantity; 
    document.getElementById('returnToolModal').style.display = "block";
}

function closeReturnModal() {
    document.getElementById('returnToolModal').style.display = "none";
}

function submitReturn() {
    const assignmentId = document.getElementById('returnAssignmentId').value;
    const quantityReturned = document.getElementById('quantityReturn').value;
    const returnNotes = document.getElementById('returnNotes').value;

    if (!assignmentId || !quantityReturned) {
        alert("Assignment ID and Quantity to Return are required.");
        return;
    }

    fetch('tool_return_action.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `assignmentId=${encodeURIComponent(assignmentId)}&quantityReturned=${encodeURIComponent(quantityReturned)}&returnNotes=${encodeURIComponent(returnNotes)}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            alert(data.message);
            closeReturnModal();

            
            const assignmentRow = document.querySelector(`.return-btn[onclick*='openReturnModal(${assignmentId}']`).closest('tr');
            if (assignmentRow) {
                assignmentRow.remove();
            }

        } else {
            alert("Error: " + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error processing tool return. Check console.');
    });
}


document.addEventListener('DOMContentLoaded', loadToolAssignments);