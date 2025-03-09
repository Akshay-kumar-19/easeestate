document.addEventListener("DOMContentLoaded", function () {
    const leadDropdown = document.getElementById("lead_id");
    const jobDropdownMain = document.getElementById("main_job_id");
    const overtimeHoursMainInput = document.getElementById("main_overtime_hours");
    const workersTableBody = document.getElementById("workersTable").querySelector("tbody");
    const submitBtn = document.getElementById("submitOvertime");
    const dateInput = document.getElementById("date");

    let selectedLeadId = "";
    let jobs = {}; // Store fetched jobs for worker row dropdowns

    // Function to clear worker table
    function clearWorkerTable() {
        workersTableBody.innerHTML = "";
    }

    // Function to fetch workers for a selected labor lead
    function fetchWorkers(leadId) {
        clearWorkerTable();
        if (!leadId) {
            return;
        }

        fetch(`fetch_workers.php?lead_id=${leadId}`) // You might need to adjust fetch_workers.php to work for overtime if needed.
            .then(response => response.json())
            .then(data => {
                if (data.status === "success") {
                    if (data.workers.length === 0) {
                        workersTableBody.innerHTML = "<tr><td colspan='3'>No workers found for this Labour Lead.</td></tr>";
                        return;
                    }
                    populateWorkerTable(data.workers);
                } else {
                    console.error("Error fetching workers:", data.message);
                    workersTableBody.innerHTML = `<tr><td colspan='3'>Error fetching workers: ${data.message}</td></tr>`;
                }
            })
            .catch(error => {
                console.error("Error fetching workers:", error);
                workersTableBody.innerHTML = `<tr><td colspan='3'>Error fetching workers: ${error.message}</td></tr>`;
            });
    }

    // Function to populate worker table rows
    function populateWorkerTable(workers) {
        workers.forEach(worker => {
            const row = document.createElement("tr");
            row.dataset.workerId = worker.worker_id; // Store worker ID in row dataset
            row.innerHTML = `
                <td>${worker.worker_name}</td>
                <td>
                    <select class="job-dropdown" name="job_id_${worker.worker_id}">
                        <option value="">-- Select Job --</option>
                        ${generateJobOptions()}
                    </select>
                </td>
                <td><input type="number" class="overtime-hours-input" name="overtime_hours_${worker.worker_id}" value="" step="0.25" placeholder="Hours"></td>
            `;
            workersTableBody.appendChild(row);
        });
    }

    // Function to generate job options for dropdowns (using jobs data fetched in PHP - see update_overtime.php)
    function generateJobOptions() {
        let optionsHTML = "";
        for (const job of phpJobsData) { // Assuming you'll pass $jobs array from PHP to JS as phpJobsData
            optionsHTML += `<option value="${job.id}">${job.job_name}</option>`;
        }
        return optionsHTML;
    }

    // Event listener for Labour Lead dropdown change
    leadDropdown.addEventListener("change", function () {
        selectedLeadId = this.value;
        if (selectedLeadId) {
            fetchWorkers(selectedLeadId);
        } else {
            clearWorkerTable();
        }
    });

    // Event listener for Main Job dropdown change
    jobDropdownMain.addEventListener("change", function () {
        const selectedJobId = this.value;
        document.querySelectorAll(".job-dropdown").forEach(dropdown => {
            dropdown.value = selectedJobId;
        });
    });

    // Event listener for Main Overtime Hours input change
    overtimeHoursMainInput.addEventListener("input", function () {
        const mainOvertimeHours = this.value;
        document.querySelectorAll(".overtime-hours-input").forEach(input => {
            input.value = mainOvertimeHours;
        });
    });

    // Event listener for Submit Overtime button click
    submitBtn.addEventListener("click", function () {
        if (!selectedLeadId) {
            alert("Please select a Labour Lead.");
            return;
        }

        const overtimeData = [];
        let hasError = false;
        workersTableBody.querySelectorAll("tr").forEach(row => {
            const workerId = row.dataset.workerId;
            const jobId = row.querySelector(".job-dropdown").value;
            const overtimeHours = row.querySelector(".overtime-hours-input").value;

            if (overtimeHours && !jobId) { // Check for jobId *only if* overtimeHours is entered
                alert(`Please select a Job for worker ID: ${workerId}`);
                hasError = true;
                return; // Stop processing this row and further rows
            }
            if (overtimeHours && (isNaN(overtimeHours) || parseFloat(overtimeHours) < 0)) { // Only validate if overtimeHours has a value
                alert(`Please enter valid Overtime Hours for worker ID: ${workerId}`);
                hasError = true;
                return; // Stop processing this row and further rows
            }

            if (overtimeHours && parseFloat(overtimeHours) > 0) { // Only add to data if overtimeHours is provided and > 0
                overtimeData.push({
                    worker_id: workerId,
                    job_id: jobId,
                    overtime_hours: parseFloat(overtimeHours)
                });
            }
        });

        if (hasError) {
            return; // Stop submission if there are errors in worker data.
        }

        if (overtimeData.length === 0) {
            alert("No worker overtime data to submit."); // Adjusted alert message
            return;
        }


        fetch("update_overtime_action.php", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({
                lead_id: selectedLeadId,
                date: dateInput.value,
                overtime: overtimeData
            })
        })
            .then(response => response.json())
            .then(data => {
                if (data.status === "success") {
                    alert("Overtime updated successfully.");
                    leadDropdown.value = ""; // Reset lead dropdown
                    jobDropdownMain.value = ""; // Reset main job dropdown
                    overtimeHoursMainInput.value = ""; // Reset main overtime hours input

                } else {
                    alert("Error: " + data.message);
                }
            })
            .catch(error => {
                console.error("Error submitting overtime:", error);
                alert("Error submitting overtime. Please check console for details.");
            });
    });


});