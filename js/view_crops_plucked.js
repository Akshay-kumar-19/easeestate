document.addEventListener("DOMContentLoaded", function () {
    const viewCropTypeDropdown = document.getElementById("view_crop_type");
    const viewPluckedDateInput = document.getElementById("view_plucked_date");
    const viewCropsBtn = document.getElementById("viewCropsBtn");
    const cropsPluckedDataSection = document.getElementById("crops_plucked_data_section");
    const cropsPluckedTableContainer = document.getElementById("cropsPluckedTableContainer");

    const cropsPluckedTotalsCoffeeSection = document.getElementById("cropsPluckedTotalsCoffee"); // Coffee totals section
    const cropsPluckedTotalsPepperSection = document.getElementById("cropsPluckedTotalsPepper"); // Pepper totals section
    const cropsPluckedTotalsArecaSection = document.getElementById("cropsPluckedTotalsAreca"); // Areca totals section

    const totalRipeKgSpan = document.getElementById("totalRipeKg"); // Span for coffee total ripe kg
    const totalUnripeKgSpan = document.getElementById("totalUnripeKg"); // Span for coffee total unripe kg
    const grandTotalKgSpan = document.getElementById("grandTotalKg"); // Span for coffee grand total kg
    const totalPepperKgSpan = document.getElementById("totalPepperKg"); // Span for pepper total kg
    const totalArecaKoneSpan = document.getElementById("totalArecaKone"); // Span for areca total kone


    viewCropsBtn.addEventListener("click", function () {
        const selectedCropType = viewCropTypeDropdown.value;
        const selectedPluckedDate = viewPluckedDateInput.value;

        if (!selectedCropType) {
            alert("Please select a crop type to view.");
            return;
        }
        if (!selectedPluckedDate) {
            alert("Please select a date to view.");
            return;
        }

        fetchCropsPluckedData(selectedCropType, selectedPluckedDate);
    });

    function fetchCropsPluckedData(cropType, pluckedDate) {
        fetch('fetch_crops_plucked_data.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                crop_type: cropType,
                plucked_date: pluckedDate
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
                displayCropsPluckedData(data.crops_data, cropType, data.totals);
                cropsPluckedDataSection.style.display = 'block';
            } else if (data.status === 'no_data') {
                cropsPluckedTableContainer.innerHTML = "<p>No data found for the selected crop and date.</p>";
                cropsPluckedDataSection.style.display = 'block';
                hideAllTotalsSections(); // Hide all totals sections if no data
            }
             else {
                alert('Failed to fetch crops plucked data.');
                console.error('Error fetching crops plucked data:', data);
                cropsPluckedTableContainer.innerHTML = "<p>Error fetching data. Please check console.</p>";
                cropsPluckedDataSection.style.display = 'block';
                hideAllTotalsSections(); // Hide all totals sections on error
            }
        })
        .catch(error => {
            console.error('Error fetching crops plucked data:', error);
            alert('Error fetching crops plucked data. Check console for details.');
            cropsPluckedDataSection.style.display = 'block';
            hideAllTotalsSections(); // Hide all totals sections on error
            cropsPluckedTableContainer.innerHTML = "<p>Error fetching data. Please check console.</p>";
        });
    }

    function displayCropsPluckedData(cropsData, cropType, totals) {
        cropsPluckedTableContainer.innerHTML = '';
        hideAllTotalsSections(); // Initially hide all totals sections for each new data load


        if (cropsData.length === 0) {
            cropsPluckedTableContainer.innerHTML = "<p>No data found for the selected crop and date.</p>";
            return;
        }

        const table = document.createElement('table');
        table.id = 'cropsPluckedTable';
        table.innerHTML = `
            <thead>
                <tr>
                    <th>Worker Name</th>
                    <th>Labour Lead</th>
                    ${cropType == coffee_job_id ? '<th>Ripe (KG)</th><th>Unripe (KG)</th><th>Total (KG)</th>' : ''}
                    ${cropType == pepper_job_id ? '<th>Total (KG)</th>' : ''}
                    ${cropType == areca_job_id ? '<th>Total Kone</th>' : ''}
                    <th>Salary Calculation</th>
                    <th>Rate/Wage</th>
                </tr>
            </thead>
            <tbody></tbody>
        `;
        const tableBody = table.querySelector('tbody');

        cropsData.forEach(record => {
            const row = tableBody.insertRow();
            row.insertCell().textContent = record.worker_name;
            row.insertCell().textContent = record.lead_name;
            if (cropType == coffee_job_id) {
                row.insertCell().textContent = record.ripe_kg;
                row.insertCell().textContent = record.unripe_kg;
                row.insertCell().textContent = record.total_kg;
            } else if (cropType == pepper_job_id) {
                row.insertCell().textContent = record.total_kg;
            } else if (cropType == areca_job_id) {
                row.insertCell().textContent = record.kone_count;
            }
            row.insertCell().textContent = record.salary_calculation_type.replace('_', ' ').toUpperCase();
            row.insertCell().textContent = record.rate_or_wage;
        });

        cropsPluckedTableContainer.appendChild(table);

        if (totals) {
            if (cropType == coffee_job_id) {
                cropsPluckedTotalsCoffeeSection.style.display = 'block';
                totalRipeKgSpan.textContent = totals.coffee.ripe_kg;
                totalUnripeKgSpan.textContent = totals.coffee.unripe_kg;
                grandTotalKgSpan.textContent = totals.coffee.total_kg;
            } else if (cropType == pepper_job_id) {
                cropsPluckedTotalsPepperSection.style.display = 'block';
                cropsPluckedTotalsPepperSection.querySelector('h3').textContent = 'Total Plucked (Pepper)'; // Update heading for pepper
                totalPepperKgSpan.textContent = totals.pepper.total_kg; // Display pepper total kg
            } else if (cropType == areca_job_id) {
                cropsPluckedTotalsArecaSection.style.display = 'block';
                cropsPluckedTotalsArecaSection.querySelector('h3').textContent = 'Total Plucked (Areca)'; // Update heading for areca
                totalArecaKoneSpan.textContent = totals.areca.total_kone; // Display areca total kone
            }
        }
    }

    function hideAllTotalsSections() {
        cropsPluckedTotalsCoffeeSection.style.display = 'none';
        cropsPluckedTotalsPepperSection.style.display = 'none';
        cropsPluckedTotalsArecaSection.style.display = 'none';
    }
});