
document.addEventListener('DOMContentLoaded', function() {



    window.editTool = function(toolId, toolName) {
        document.getElementById('toolModal').style.display = 'block';
        document.getElementById('modalTitle').textContent = 'Edit Tool';
        document.getElementById('toolId').value = toolId;
        document.getElementById('toolName').value = toolName;
        document.getElementById('toolQuantity').value = 1;
        document.getElementById('submitBtn').textContent = 'Update';
        modal.style.display = "block";
    }


    window.deleteTool = function(toolId) {
        if (confirm('Are you sure you want to delete this tool?')) {
            fetch('tool_delete_action.php?tool_id=' + toolId, {
                method: 'GET',
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    alert(data.message);
                    updateToolTable(data.tools);
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error deleting tool.');
            });
        }
    };


    window.saveTool = function() {
        const toolId = document.getElementById('toolId').value;
        const toolName = document.getElementById('toolName').value;
        const toolQuantity = document.getElementById('toolQuantity').value;
        const modalTitle = document.getElementById('modalTitle').textContent;

        const actionType = modalTitle === 'Add Tool' ? 'add' : 'edit';

        if (!toolName) {
            alert('Tool name is required.');
            return;
        }

        const formData = new FormData();
        formData.append('tool_name', toolName);
        formData.append('tool_quantity', toolQuantity);
        if (toolId) {
            formData.append('tool_id', toolId);
        }

        fetch('tool_add_action.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                closeModal();
                alert(data.message);
                updateToolTable(data.tools);
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error saving tool.');
        });
    };

    function updateToolTable(tools) {
        const tableBody = document.getElementById('toolsTableBody');
        tableBody.innerHTML = '';
        if (tools && tools.length > 0) {
            tools.forEach(tool => {
                const row = tableBody.insertRow();
                row.setAttribute('data-tool-id', tool.tool_id);
                row.insertCell().textContent = tool.tool_name;
                row.insertCell().textContent = tool.quantity;
                const actionsCell = row.insertCell();
                actionsCell.classList.add('action-buttons');
                actionsCell.innerHTML = `
                    <button class="edit-btn" onclick="editTool(${tool.tool_id}, '${tool.tool_name}')">Edit</button>
                    <button class="delete-btn" onclick="deleteTool(${tool.tool_id})">Delete</button>
                `;
            });
        } else {
            tableBody.innerHTML = '<tr><td colspan="3">No tools available.</td></tr>';
        }
    }
});