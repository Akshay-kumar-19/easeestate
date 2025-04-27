document.addEventListener("DOMContentLoaded", function () {
            const cropTypeDropdown = document.getElementById("crop_type");
            const workersSection = document.getElementById("workers_section");
            const workersTableBody = document.querySelector("#workersTable tbody");
            const saveCropsPluckedBtn = document.getElementById("saveCropsPluckedBtn");
            const pluckedDateInput = document.getElementById("plucked_date");
            const submitSection = document.getElementById("submit_section");
            const updateWorkersBtn = document.getElementById("updateWorkersBtn");
    
            const perKgBtn = document.getElementById("per_kg_btn");
            const dailyWageBtn = document.getElementById("daily_wage_btn");
    
            let selectedCropType = "";
            let salaryCalculationType = 'per_kg';
            let currentPerKgRate = 0;
            let currentDailyWage = 0;
    
            function clearWorkerTable() {
                workersTableBody.innerHTML = "";
            }
    
            function fetchWorkersForCrop(jobId) {
                clearWorkerTable();
                if (!jobId) {
                    workersSection.style.display = 'none';
                    submitSection.style.display = 'none';
                    return;
                }
    
                fetch(`fetch_workers_by_job.php?job_id=${jobId}`)
                    .then(response => {
                        if (!response.ok) {
                            throw new Error("Failed to fetch workers.");
                        }
                        return response.json();
                    })
                    .then(data => {
                        if (data.status === "success") {
                            if (data.workers.length === 0) {
                                workersTableBody.innerHTML = "<tr><td colspan='7'>No workers assigned to this crop today.</td></tr>";
                                workersSection.style.display = 'block';
                                submitSection.style.display = 'none';
                                return;
                            }
                            populateWorkerTable(data.workers, jobId, data.per_kg_rate, data.daily_wage);
                            workersSection.style.display = 'block';
                            submitSection.style.display = 'block';
                            currentPerKgRate = parseFloat(data.per_kg_rate);
                            currentDailyWage = parseFloat(data.daily_wage);
                        } else {
                            console.error("Error fetching workers:", data.message);
                            workersTableBody.innerHTML = `<tr><td colspan='7'>Error fetching workers: ${data.message}</td></tr>`;
                            workersSection.style.display = 'block';
                            submitSection.style.display = 'none';
                        }
                    })
                    .catch(error => {
                        console.error("Error fetching workers:", error);
                        workersTableBody.innerHTML = `<tr><td colspan='7'>Error fetching workers: ${error.message}</td></tr>`;
                        workersSection.style.display = 'block';
                        submitSection.style.display = 'none';
                    });
            }
    
            function populateWorkerTable(workers, jobId, perKgRate, dailyWage) {
                workers.forEach(worker => {
                    const row = document.createElement("tr");
                    row.dataset.workerId = worker.worker_id;
                    let cellsHTML = `
                                <td>${worker.worker_name}</td>
                                <td>${worker.lead_name}</td>
                            `;
    
                    if (jobId == coffee_job_id) {
                        cellsHTML += `
                                    <td><input type="number" class="ripe-kg-input" value="0" step="0.01"></td>
                                    <td><input type="number" class="unripe-kg-input" value="0" step="0.01"></td>
                                    <td class="total-plucked-kg">0.00</td>
                                    `;
                    } else if (jobId == pepper_job_id) {
                        cellsHTML += `
                                    <td><input type="number" class="dummy-input" value="0" step="0.01" readonly style="display:none;"></td>
                                    <td><input type="number" class="dummy-input" value="0" step="0.01" readonly style="display:none;"></td>
                                    <td></td>
                                    <td><input type="number" class="total-kg-input" value="0" step="0.01"></td>
                                    `;
                    } else if (jobId == areca_job_id) {
                        cellsHTML += `
                                    <td><input type="number" class="dummy-input" value="0" step="0.01" readonly style="display:none;"></td>
                                    <td><input type="number" class="dummy-input" value="0" step="0.01" readonly style="display:none;"></td>
                                    <td></td>
                                    <td></td>
                                    <td><input type="number" class="kone-count-input" value="0" step="1"></td>
                                    `;
                    }
    
                    row.innerHTML = cellsHTML;
                    workersTableBody.appendChild(row);
    
                   
                    if (jobId == coffee_job_id) {
                        const ripeKgInput = row.querySelector('.ripe-kg-input');
                        const unripeKgInput = row.querySelector('.unripe-kg-input');
                        const totalKgCell = row.querySelector('.total-plucked-kg');
    
                        ripeKgInput.addEventListener('input', updateTotalKg);
                        unripeKgInput.addEventListener('input', updateTotalKg);
    
                        function updateTotalKg() {
                            const ripeKg = parseFloat(ripeKgInput.value) || 0;
                             const unripeKg = parseFloat(unripeKgInput.value) || 0;
                            const totalKg = ripeKg + unripeKg;
                            totalKgCell.textContent = totalKg.toFixed(2);
                        }
                    }
                });
    
                if (salaryCalculationType === 'per_kg') {
                    perKgBtn.classList.add('active');
                    dailyWageBtn.classList.remove('active');
                } else {
                    dailyWageBtn.classList.add('active');
                    perKgBtn.classList.remove('active');
                }
            }
    
    
            updateWorkersBtn.addEventListener('click', function() {
                selectedCropType = cropTypeDropdown.value;
                if (selectedCropType) {
                    fetchWorkersForCrop(selectedCropType);
                    adjustTableHeader(selectedCropType);
                } else {
                    alert("Please select a crop type first.");
                }
            });
    
    
            function adjustTableHeader(cropType) {
                const coffeeColumns = document.querySelectorAll('#workersTable th[data-crop-type="coffee"]');
                const pepperColumn = document.querySelectorAll('#workersTable th[data-crop-type="pepper"]');
                const arecaColumn = document.querySelectorAll('#workersTable th[data-crop-type="areca"]');
    
                coffeeColumns.forEach(col => col.style.display = (cropType == coffee_job_id) ? '' : 'none');
                pepperColumn.forEach(col => col.style.display = (cropType == pepper_job_id) ? '' : 'none');
                arecaColumn.forEach(col => col.style.display = (cropType == areca_job_id) ? '' : 'none');
    
                const totalPluckedHeader = document.querySelector('#workersTable th[data-crop-type="coffee"]:nth-child(5)');
                if (cropType == coffee_job_id) {
                    totalPluckedHeader.textContent = "Total Plucked (KG)";
                } else if (cropType == pepper_job_id) {
                    totalPluckedHeader.textContent = "Total Plucked (KG)";
                } else if (cropType == areca_job_id) {
                    totalPluckedHeader.textContent = "Total Count (Kone)";
                } else {
                    totalPluckedHeader.textContent = "Total Plucked";
                }
            }
    
    
            perKgBtn.addEventListener('click', function () {
                salaryCalculationType = 'per_kg';
                perKgBtn.classList.add('active');
                dailyWageBtn.classList.remove('active');
                if (workersSection.style.display === 'block') {
                    fetchWorkersForCrop(selectedCropType);
                }
            });
    
            dailyWageBtn.addEventListener('click', function () {
                salaryCalculationType = 'daily_wage';
                dailyWageBtn.classList.add('active');
                perKgBtn.classList.remove('active');
                 if (workersSection.style.display === 'block') {
                    fetchWorkersForCrop(selectedCropType);
                }
            });
    
    
            saveCropsPluckedBtn.addEventListener("click", function () {
                if (!selectedCropType) {
                    alert("Please select a crop type.");
                    return;
                }
    
                const pluckedData = [];
                const pluckedDate = pluckedDateInput.value;
                let rateValue = 0;
                let wageValue = 0;
    
                if (salaryCalculationType === 'per_kg') {
                    rateValue = currentPerKgRate;
                    wageValue = 0;
                } else if (salaryCalculationType === 'daily_wage') {
                    wageValue = currentDailyWage;
                    rateValue = 0;
                }
    
                if (!pluckedDate) {
                    alert("Please select plucked date.");
                    return;
                }
    
                let isValid = true;
                document.querySelectorAll("#workersTable tbody tr").forEach(row => {
                    let ripeKgInput, unripeKgInput, totalKgInput, koneCountInput;
                    let ripeKg = 0, unripeKg = 0, totalKg = 0, koneCount = 0;
    
                    if (selectedCropType == coffee_job_id) {
                        ripeKgInput = row.querySelector('.ripe-kg-input');
                        unripeKgInput = row.querySelector('.unripe-kg-input');
                        ripeKg = parseFloat(ripeKgInput.value);
                        unripeKg = parseFloat(unripeKgInput.value);
    
                        if (isNaN(ripeKg) || ripeKg < 0 || isNaN(unripeKg) || unripeKg < 0) {
                            alert("Ripe KG and Unripe KG must be non-negative numbers.");
                            isValid = false;
                            return;
                        }
                        totalKg = parseFloat(row.querySelector('.total-plucked-kg').textContent) || 0;
    
                    } else if (selectedCropType == pepper_job_id) {
                        totalKgInput = row.querySelector('.total-kg-input');
                        totalKg = parseFloat(totalKgInput.value);
                        if (isNaN(totalKg) || totalKg < 0) {
                            alert("Total KG must be a non-negative number.");
                            isValid = false;
                            return;
                        }
                    } else if (selectedCropType == areca_job_id) {
                        koneCountInput = row.querySelector('.kone-count-input');
                        koneCount = parseInt(koneCountInput.value);
                        if (isNaN(koneCount) || koneCount < 0 || !Number.isInteger(koneCount)) {
                            alert("Kone Count must be a non-negative integer.");
                            isValid = false;
                            return;
                        }
                    }
    
                    pluckedData.push({
                        worker_id: row.dataset.workerId,
                        ripe_kg: ripeKg,
                        unripe_kg: unripeKg,
                        total_kg: totalKg,
                        kone_count: koneCount,
                    });
                });
    
                if (!isValid) {
                    return;
                }
    
    
                const dataToSend = {
                    plucked_date: pluckedDate,
                    crop_type: selectedCropType,
                    plucked_data: pluckedData,
                    salary_calculation_type: salaryCalculationType,
                    per_kg_rate: rateValue,
                    daily_wage_rate: wageValue
                };
    
                fetch('save_crops_plucked.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(dataToSend)
                })
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Network response was not ok');
                        }
                        return response.json();
                    })
                    .then(data => {
                        if (data.status === 'success') {
                            alert('Crops plucked data saved successfully!');
                             clearWorkerTable();
                            workersSection.style.display = 'none';
                            submitSection.style.display = 'none';
                            cropTypeDropdown.value = "";
                        } else {
                            alert('Failed to save crops plucked data.');
                            console.error('Error saving crops plucked data:', data);
                        }
                    })
                    .catch(error => {
                        console.error('Error saving crops plucked data:', error);
                        alert('Error saving crops plucked data. Check console for details.');
                    });
            });
    
            if (salaryCalculationType === 'per_kg') {
                perKgBtn.classList.add('active');
                dailyWageBtn.classList.remove('active');
            }
        });