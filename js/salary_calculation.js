let weekStartDate;
let weekEndDate;

// Function to determine the week range (Saturday to Friday)
function determineWeekRange() {
    const today = new Date();
    const dayOfWeek = today.getDay();
    
    // Calculate previous Saturday
    const lastSaturday = new Date(today);
    lastSaturday.setDate(today.getDate() - dayOfWeek - 1 + (dayOfWeek === 6 ? 7 : 0));
    
    // Calculate next Friday
    const nextFriday = new Date(lastSaturday);
    nextFriday.setDate(lastSaturday.getDate() + 6);
    
    weekStartDate = lastSaturday.toISOString().slice(0, 10);
    weekEndDate = nextFriday.toISOString().slice(0, 10);
}

determineWeekRange();

// Function to fetch JSON salary data
function fetchJsonSalariesForDisplay(weekStartDate, weekEndDate) {
    return fetch(`/easeestate/salary_calculation.php?week_start=${weekStartDate}&week_end=${weekEndDate}`)
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .catch(error => {
            console.error("Error fetching salary data for display:", error);
            return null;
        });
}

// Function to fetch CSV salary data
function fetchCsvSalariesForDownload(weekStartDate, weekEndDate) {
    return fetch(`/easeestate/salary_calculation.php?week_start=${weekStartDate}&week_end=${weekEndDate}&report_format=csv`)
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.text();
        })
        .catch(error => {
            console.error("Error fetching salary data for CSV download:", error);
            return null;
        });
}

function downloadCsvReport(csvData) {
    if (!csvData) {
        alert("No data to download.");
        return;
    }

    const blob = new Blob([csvData], { type: 'text/csv' });
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = 'weekly_salary_report.csv';
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
    URL.revokeObjectURL(url);
}

function displaySalaries(allLeadsSalaryData) {
    const salaryDisplayArea = document.getElementById('salaryDisplayArea');
    salaryDisplayArea.innerHTML = '';

    if (!allLeadsSalaryData || allLeadsSalaryData.length === 0) {
        salaryDisplayArea.innerHTML = '<p>No salary data found for the selected week.</p>';
        return;
    }

    let grandTotalAllLeads = 0;
    allLeadsSalaryData.forEach(leadData => {
        if (!leadData || !leadData.worker_salaries) return;

        const leadHeader = document.createElement('h2');
        leadHeader.textContent = `Labour Lead: ${leadData.lead_name}`;
        salaryDisplayArea.appendChild(leadHeader);

        const table = document.createElement('table');
        table.className = 'salary-table';

        table.innerHTML = `
            <thead>
                <tr>
                    <th>Worker Name</th>
                    <th>Present Days</th>
                    <th>Daily Wage Salary (₹)</th>
                    <th>Per KG Salary (₹)</th>
                    <th>Overtime Hours</th>
                    <th>Overtime Salary (₹)</th>
                    <th>Total Salary (₹)</th>
                </tr>
            </thead>
        `;

        const tbody = document.createElement('tbody');
        leadData.worker_salaries.forEach(workerSalary => {
            const row = document.createElement('tr');
            row.innerHTML = `
                <td>${workerSalary.worker_name}</td>
                <td>${workerSalary.present_days}</td>
                <td>₹${parseFloat(workerSalary.daily_wage_salary).toFixed(2)}</td>
                <td>₹${parseFloat(workerSalary.per_kg_salary).toFixed(2)}</td>
                <td>${parseFloat(workerSalary.total_overtime_hours).toFixed(2)}</td>
                <td>₹${parseFloat(workerSalary.overtime_salary).toFixed(2)}</td>
                <td>₹${parseFloat(workerSalary.total_salary).toFixed(2)}</td>
            `;
            tbody.appendChild(row);
        });
        table.appendChild(tbody);

        const tfoot = document.createElement('tfoot');
        tfoot.innerHTML = `
            <tr class="total-row">
                <td><strong>Total for ${leadData.lead_name}</strong></td>
                <td><strong>${leadData.totals.total_present_days}</strong></td>
                <td><strong>₹${parseFloat(leadData.totals.total_daily_wage_salary).toFixed(2)}</strong></td>
                <td><strong>₹${parseFloat(leadData.totals.total_per_kg_salary).toFixed(2)}</strong></td>
                <td><strong>${parseFloat(leadData.totals.total_overtime_hours).toFixed(2)}</strong></td>
                <td><strong>₹${parseFloat(leadData.totals.total_overtime_salary).toFixed(2)}</strong></td>
                <td><strong>₹${parseFloat(leadData.totals.grand_total_salary).toFixed(2)}</strong></td>
            </tr>
        `;
        table.appendChild(tfoot);

        salaryDisplayArea.appendChild(table);
        grandTotalAllLeads += leadData.totals.grand_total_salary;
    });

    const grandTotalElement = document.createElement('h2');
    grandTotalElement.textContent = `Grand Total Salary for All Leads: ₹${parseFloat(grandTotalAllLeads).toFixed(2)}`;
    salaryDisplayArea.appendChild(grandTotalElement);

    const downloadButton = document.createElement('button');
    downloadButton.textContent = 'Download Report (CSV)';
    downloadButton.id = 'downloadReportButton';
    salaryDisplayArea.appendChild(downloadButton);
    
    downloadButton.addEventListener('click', () => {
        fetchCsvSalariesForDownload(weekStartDate, weekEndDate)
            .then(csvData => {
                if (csvData) downloadCsvReport(csvData);
                else alert("Failed to fetch salary data for CSV download.");
            });
    });
}

function triggerSalaryCalculation() {
    fetchJsonSalariesForDisplay(weekStartDate, weekEndDate)
        .then(displaySalaries);
}

document.addEventListener('DOMContentLoaded', triggerSalaryCalculation);