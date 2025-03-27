function openToolModal() {
    document.getElementById('toolModal').style.display = 'block';
    document.getElementById('addToolButton').style.display = 'none';
    document.getElementById('modalTitle').textContent = 'Add Tool';
    document.getElementById('submitBtn').textContent = 'Save';
    document.getElementById('toolForm').reset();
    document.getElementById('toolId').value = '';
}

function closeToolModal() {
    document.getElementById('toolModal').style.display = 'none';
    document.getElementById('addToolButton').style.display = 'block';
    document.getElementById('toolForm').reset();
}

function validateToolName(toolName) {
    const regex = /^[a-zA-Z0-9\s]+$/;
    return regex.test(toolName);
}

function addTool() {
    const toolName = document.getElementById('toolName').value;
    const toolQuantity = document.getElementById('toolQuantity').value;
    const toolId = document.getElementById('toolId').value;

    if (!validateToolName(toolName)) {
        alert("Tool Name should only contain letters, numbers, and spaces.");
        return;
    }

    const formData = new FormData();
    formData.append('toolId', toolId);
    formData.append('toolName', toolName);
    formData.append('toolQuantity', toolQuantity);


    console.log("Form Data being sent:", formData);

    const action = toolId ? 'tool_update_action.php' : 'tool_action.php';

    fetch(action, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            alert(data.message);
            closeToolModal();
            loadTools();
        } else {
            alert("Error: " + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error. Check console.');
    });
}

function loadTools() {
    fetch('get_tools.php')
        .then(response => response.json())
        .then(data => {
            const tableBody = document.getElementById('toolTableBody');
            tableBody.innerHTML = '';
            if (data.length > 0) {
                data.forEach(tool => {
                    let row = tableBody.insertRow();
                    row.insertCell(0).textContent = tool.tool_name;
                    row.insertCell(1).textContent = tool.tool_quantity;
                    let actionsCell = row.insertCell(2);
                    actionsCell.classList.add('action-buttons');
                    actionsCell.innerHTML = `
                        <button class="edit-btn" onclick="editTool(${tool.tool_id})">Edit</button>
                        <button class="delete-btn" onclick="deleteTool(${tool.tool_id})">Delete</button>
                    `;
                });
            } else {
                tableBody.innerHTML = '<tr><td colspan="3">No tools added yet.</td></tr>';
            }
        })
        .catch(error => {
            console.error('Error fetching tools:', error);
            alert('Error loading tools. See console.');
        });
}

function editTool(toolId) {
    fetch('get_tool_details.php?tool_id=' + toolId)
        .then(response => response.json())
        .then(tool => {
            if (tool) {
                document.getElementById('toolId').value = tool.tool_id;
                document.getElementById('toolName').value = tool.tool_name;
                document.getElementById('toolQuantity').value = tool.tool_quantity;
                document.getElementById('modalTitle').textContent = 'Edit Tool';
                document.getElementById('submitBtn').textContent = 'Update';
                document.getElementById('toolModal').style.display = 'block';
                document.getElementById('addToolButton').style.display = 'none';
            } else {
                alert('Tool details not found.');
            }
        })
        .catch(error => {
            console.error('Error fetching tool details:', error);
            alert('Error fetching tool details.');
        });
}


function deleteTool(toolId) {
    if (confirm('Are you sure you want to delete this tool?')) {
        fetch('tool_delete_action.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'toolId=' + toolId
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                alert(data.message);
                loadTools();
            } else {
                alert('Error deleting tool: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error deleting tool:', error);
            alert('Error deleting tool. Check console.');
        });
    }
}


document.addEventListener('DOMContentLoaded', loadTools);