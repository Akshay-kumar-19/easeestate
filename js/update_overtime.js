document.addEventListener("DOMContentLoaded", function () {
    const leadDropdown = document.getElementById("lead_id");
    const jobDropdownMain = document.getElementById("main_job_id");
    const overtimeHoursMainInput = document.getElementById("main_overtime_hours");
    const workersTableBody = document.getElementById("workersTable").querySelector("tbody");
    const submitBtn = document.getElementById("submitOvertime");
    const dateInput = document.getElementById("date");

    let selectedLeadId = "";
    let jobs = {}; 

    
    function clearWorkerTable() {
        workersTableBody.innerHTML = "";
    }

    // fetch workers for a selected labor lead
    function fetchWorkers(leadId) {
        clearWorkerTable();
        if (!leadId) {
            return;
        }

        fetch(`fetch_workers.php?lead_id=${leadId}`) 
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

    // get worker table 
    function populateWorkerTable(workers) {
        workers.forEach(worker => {
            const row = document.createElement("tr");
            row.dataset.workerId = worker.worker_id; 
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

    // geenrate job options
    function generateJobOptions() {
        let optionsHTML = "";
        for (const job of phpJobsData) { 
            optionsHTML += `<option value="${job.id}">${job.job_name}</option>`;
        }
        return optionsHTML;
    }

    
    leadDropdown.addEventListener("change", function () {
        selectedLeadId = this.value;
        if (selectedLeadId) {
            fetchWorkers(selectedLeadId);
        } else {
            clearWorkerTable();
        }
    });


    jobDropdownMain.addEventListener("change", function () {
        const selectedJobId = this.value;
        document.querySelectorAll(".job-dropdown").forEach(dropdown => {
            dropdown.value = selectedJobId;
        });
    });

 
    overtimeHoursMainInput.addEventListener("input", function () {
        const mainOvertimeHours = this.value;
        document.querySelectorAll(".overtime-hours-input").forEach(input => {
            input.value = mainOvertimeHours;
        });
    });


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

            if (overtimeHours && !jobId) { 
                alert(`Please select a Job for worker ID: ${workerId}`);
                hasError = true;
                return; 
            }
            if (overtimeHours && (isNaN(overtimeHours) || parseFloat(overtimeHours) < 0)) { 
                alert(`Please enter valid Overtime Hours for worker ID: ${workerId}`);
                hasError = true;
                return;
            }

            if (overtimeHours && parseFloat(overtimeHours) > 0) { 
                overtimeData.push({
                    worker_id: workerId,
                    job_id: jobId,
                    overtime_hours: parseFloat(overtimeHours)
                });
            }
        });

        if (hasError) {
            return; 
        }

        if (overtimeData.length === 0) {
            alert("No worker overtime data to submit."); 
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
                    leadDropdown.value = ""; 
                    jobDropdownMain.value = ""; 
                    overtimeHoursMainInput.value = ""; 

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