document.addEventListener('DOMContentLoaded', function() {
    loadAssignedTools();
});

function loadAssignedTools() {
    setActiveButton('btnAssignedTools');
    loadDashboardData('get_assigned_tools.php', 'Assigned Tools', createAssignedToolsTable);
}

function loadReturnedTools() {
    setActiveButton('btnReturnedTools');
    loadDashboardData('get_returned_tools.php', 'Returned Tools', createReturnedToolsTable);
}

function loadToolInventory() {
    setActiveButton('btnToolInventory');
    loadDashboardData('get_tool_inventory.php', 'Tool Inventory', createToolInventoryTable);
}

function setActiveButton(buttonId) {
    document.querySelectorAll('.dashboard-buttons button').forEach(button => {
        button.classList.remove('active');
    });
    document.getElementById(buttonId).classList.add('active');
}


function loadDashboardData(phpFile, tableTitle, tableCreationFunction) {
    const dashboardTableContainer = document.getElementById('dashboardTableContainer');
    dashboardTableContainer.innerHTML = '<h2>Loading ' + tableTitle + '...</h2>';

    fetch(phpFile)
        .then(response => response.json())
        .then(data => {
            dashboardTableContainer.innerHTML = '';
            if (data.status === 'error') {
                dashboardTableContainer.innerHTML = '<p class="error-message">Error loading data: ' + data.message + '</p>';
            } else {
                if (data && data.length > 0) {
                    const table = tableCreationFunction(tableTitle, data);
                    dashboardTableContainer.appendChild(table);
                } else {
                    dashboardTableContainer.innerHTML = '<p>No data to display for ' + tableTitle + '.</p>';
                }
            }
        })
        .catch(error => {
            console.error('Error fetching data:', error);
            dashboardTableContainer.innerHTML = '<p class="error-message">Error loading data. Check console.</p>';
        });
}


function createAssignedToolsTable(tableTitle, data) {
    const table = document.createElement('table');
    table.className = 'dashboard-table assigned-tools-table';
    const caption = table.createCaption();
    caption.textContent = tableTitle;
    const thead = table.createTHead();
    const headerRow = thead.insertRow();
    const headers = ['Tool Name', 'Quantity Assigned', 'Worker Name', 'Assignment Date', 'Return Date', 'Status', 'Notes'];
    headers.forEach(headerText => {
        const th = document.createElement('th');
        th.textContent = headerText;
        headerRow.appendChild(th);
    });
    const tbody = table.createTBody();
    data.forEach(item => {
        const row = tbody.insertRow();
        row.insertCell(0).textContent = item.tool_name;
        row.insertCell(1).textContent = item.quantity_assigned;
        row.insertCell(2).textContent = item.worker_name;
        const assignmentDate = new Date(item.assignment_date);
        row.insertCell(3).textContent = assignmentDate.toLocaleDateString();
        const returnDateCell = row.insertCell(4);
        if (item.return_date) {
            const returnDate = new Date(item.return_date);
            returnDateCell.textContent = returnDate.toLocaleDateString();
        } else {
            returnDateCell.textContent = 'Not Returned';
        }
        row.insertCell(5).textContent = item.status;
        row.insertCell(6).textContent = item.notes || '';
    });
    return table;
}


function createReturnedToolsTable(tableTitle, data) {
    const table = document.createElement('table');
    table.className = 'dashboard-table returned-tools-table';
    const caption = table.createCaption();
    caption.textContent = tableTitle;
    const thead = table.createTHead();
    const headerRow = thead.insertRow();
    const headers = ['Tool Name', 'Quantity Assigned', 'Worker Name', 'Assignment Date', 'Return Date', 'Status', 'Notes'];
    headers.forEach(headerText => {
        const th = document.createElement('th');
        th.textContent = headerText;
        headerRow.appendChild(th);
    });
    const tbody = table.createTBody();
    data.forEach(item => {
        const row = tbody.insertRow();
        row.insertCell(0).textContent = item.tool_name;
        row.insertCell(1).textContent = item.quantity_assigned;
        row.insertCell(2).textContent = item.worker_name;
        const assignmentDate = new Date(item.assignment_date);
        row.insertCell(3).textContent = assignmentDate.toLocaleDateString();
        const returnDateCell = row.insertCell(4);
        if (item.return_date) {
            const returnDate = new Date(item.return_date);
                returnDateCell.textContent = formattedReturnDate = returnDate.toLocaleDateString();
        } else {
            returnDateCell.textContent = 'Not Returned';
        }
        row.insertCell(5).textContent = item.status;
        row.insertCell(6).textContent = item.notes || '';
    });
    return table;
}

function createToolInventoryTable(tableTitle, data) {
    const table = document.createElement('table');
    table.className = 'dashboard-table tool-inventory-table';
    const caption = table.createCaption();
    caption.textContent = tableTitle;
    const thead = table.createTHead();
    const headerRow = thead.insertRow();
    const headers = ['Tool Name', 'Current Quantity'];
    headers.forEach(headerText => {
        const th = document.createElement('th');
        th.textContent = headerText;
        headerRow.appendChild(th);
    });
    const tbody = table.createTBody();
    data.forEach(item => {
        const row = tbody.insertRow();
        row.insertCell(0).textContent = item.tool_name;
        row.insertCell(1).textContent = item.tool_quantity;
    });
    return table;
}