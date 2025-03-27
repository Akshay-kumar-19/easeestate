document.addEventListener("DOMContentLoaded", function () {
    const yearlySummaryBtn = document.getElementById("yearlySummaryBtn");
    const monthlySummaryBtn = document.getElementById("monthlySummaryBtn");
    const weeklySummaryBtn = document.getElementById("weeklySummaryBtn");
    const cropSummaryDataSection = document.getElementById("cropSummaryDataSection");
    const cropSummaryTableContainer = document.getElementById("cropSummaryTableContainer");
    const summaryButtons = document.querySelectorAll('.summary-btn');

    function setActiveButton(button) {
        summaryButtons.forEach(btn => btn.classList.remove('active'));
        button.classList.add('active');
    }

    yearlySummaryBtn.addEventListener("click", function () {
        setActiveButton(yearlySummaryBtn);
        fetchCropSummaryData('yearly');
    });

    monthlySummaryBtn.addEventListener("click", function () {
        setActiveButton(monthlySummaryBtn);
        fetchCropSummaryData('monthly');
    });

    weeklySummaryBtn.addEventListener("click", function () {
        setActiveButton(weeklySummaryBtn);
        fetchCropSummaryData('weekly');
    });

    fetchCropSummaryData('yearly');

    function fetchCropSummaryData(summaryType) {
        fetch('fetch_crop_summary_data.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                summary_type: summaryType
            })
        })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                if (data.status === 'success') {
                    displayCropSummaryData(data.summary_data, summaryType);
                    cropSummaryDataSection.style.display = 'block';
                } else if (data.status === 'no_data') {
                    cropSummaryTableContainer.innerHTML = "<p>No summary data found for the selected period.</p>";
                    cropSummaryDataSection.style.display = 'block';
                } else {
                    alert('Failed to fetch crop summary data.');
                    console.error('Error fetching crop summary data:', data);
                    cropSummaryTableContainer.innerHTML = "<p>Error fetching summary data. Please check console.</p>";
                    cropSummaryDataSection.style.display = 'block';
                }
            })
            .catch(error => {
                console.error('Error fetching crop summary data:', error);
                alert('Error fetching crop summary data. Check console for details.');
                cropSummaryDataSection.style.display = 'block';
                cropSummaryTableContainer.innerHTML = "<p>Error fetching summary data. Please check console.</p>";
            });
    }

    function displayCropSummaryData(summaryData, summaryType) {
        cropSummaryTableContainer.innerHTML = '';

        if (summaryData.length === 0) {
            cropSummaryTableContainer.innerHTML = "<p>No summary data found for the selected period.</p>";
            return;
        }

        const table = document.createElement('table');
        table.id = 'cropSummaryTable';
        table.innerHTML = `
                <thead>
                    <tr>
                        <th>${summaryType.charAt(0).toUpperCase() + summaryType.slice(1)}</th>
                        <th>Coffee - Ripe (KG)</th>
                        <th>Coffee - Unripe (KG)</th>
                        <th>Coffee - Total (KG)</th>
                        <th>Pepper - Total (KG)</th>
                        <th>Areca - Total Kone</th>
                    </tr>
                </thead>
                <tbody></tbody>
            `;
        const tableBody = table.querySelector('tbody');

        summaryData.forEach(record => {
            const row = tableBody.insertRow();
            let periodLabel = '';
            if (summaryType === 'yearly') {
                periodLabel = record.year;
            } else if (summaryType === 'monthly') {
                periodLabel = record.month;
            } else if (summaryType === 'weekly') {
                const startDate = new Date(record.week_start_date);
                const monthName = startDate.toLocaleString('default', { month: 'long' });
                const firstDayOfMonth = new Date(startDate.getFullYear(), startDate.getMonth(), 1);
                let weekOfMonth = Math.ceil((startDate.getDate() - firstDayOfMonth.getDate() + 1) / 7);
                 if (weekOfMonth <= 0) {
                    weekOfMonth = 1;
                }
                periodLabel = `${monthName} Week ${weekOfMonth} (${record.week_start_date} to ${record.week_end_date})`;
            }
            row.insertCell().textContent = periodLabel;
            row.insertCell().textContent = record.coffee_ripe_kg || '0.00';
            row.insertCell().textContent = record.coffee_unripe_kg || '0.00';
            row.insertCell().textContent = record.coffee_total_kg || '0.00';
            row.insertCell().textContent = record.pepper_total_kg || '0.00';
            row.insertCell().textContent = record.areca_total_kone || '0';
        });

        cropSummaryTableContainer.appendChild(table);
    }
});