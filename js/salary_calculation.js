// salary_calculation.js

let weekStartDate;
let weekEndDate;

// Function to fetch JSON data for displaying salaries on the page
function fetchJsonSalariesForDisplay(weekStartDate, weekEndDate) {
    return fetch(`/easeestate/salary_calculation.php?week_start=${weekStartDate}&week_end=${weekEndDate}`) // Fetch JSON - no report_format parameter
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json(); // Parse response as JSON
        })
        .then(jsonData => {
            return jsonData; // JSON data
        })
        .catch(error => {
            console.error("Error fetching salary data for display:", error);
            return null;
        });
}


// Function to fetch CSV data for downloading the report (remains as before, but renamed slightly for clarity)
function fetchCsvSalariesForDownload(weekStartDate, weekEndDate) {
    return fetch(`/easeestate/salary_calculation.php?week_start=${weekStartDate}&week_end=${weekEndDate}&report_format=csv`) // Request CSV format
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.text(); // Get response as text (CSV is text-based)
        })
        .then(csvData => {
            return csvData; // CSV data is now plain text
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

    allLeadsSalaryData.forEach(leadData => { // This line caused the error - now allLeadsSalaryData should be an array
        if (!leadData || !leadData.worker_salaries) {
            return;
        }

        const leadHeader = document.createElement('h2');
        leadHeader.textContent = `Labour Lead: ${leadData.lead_name}`;
        salaryDisplayArea.appendChild(leadHeader);

        const table = document.createElement('table');
        table.className = 'salary-table';

        const thead = document.createElement('thead');
        thead.innerHTML = `
                <tr>
                    <th>Worker Name</th>
                    <th>Present Days</th>
                    <th>Daily Wage Salary (₹)</th>
                    <th>Per KG Salary (₹)</th>
                    <th>Overtime Hours</th>
                    <th>Overtime Salary (₹)</th>
                    <th>Total Salary (₹)</th>
                </tr>
            `;
        table.appendChild(thead);

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

    downloadButton.addEventListener('click', function() {
        if (weekStartDate && weekEndDate) {
            fetchCsvSalariesForDownload(weekStartDate, weekEndDate) // Use fetchCsvSalariesForDownload for CSV
                .then(csvData => {
                    if (csvData) {
                        downloadCsvReport(csvData);
                    } else {
                        alert("Failed to fetch salary data for CSV download.");
                    }
                });
        } else {
            alert("Week start and end dates are not set.");
        }
    });
}

function triggerSalaryCalculation(weekStart, weekEnd) {
    weekStartDate = weekStart;
    weekEndDate = weekEnd;

    fetchJsonSalariesForDisplay(weekStart, weekEnd) // Use fetchJsonSalariesForDisplay for initial display
        .then(allSalariesData => {
            if (allSalariesData) {
                displaySalaries(allSalariesData);
            } else {
                alert("Failed to fetch salary data.");
            }
        });
}

document.addEventListener('DOMContentLoaded', function () {
    const today = new Date();
    const currentDayOfWeek = today.getDay();

    const lastSaturday = new Date(today);
    lastSaturday.setDate(today.getDate() - currentDayOfWeek - 1);

    const thisFriday = new Date(today);
    thisFriday.setDate(today.getDate() + (5 - currentDayOfWeek));

    weekStartDate = lastSaturday.toISOString().slice(0, 10);
    weekEndDate = thisFriday.toISOString().slice(0, 10);

    triggerSalaryCalculation(weekStartDate, weekEndDate);
});