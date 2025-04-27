document.addEventListener("DOMContentLoaded", function () {
    const leadDropdown = document.getElementById("lead_id");
    const workersTable = document.getElementById("workersTable").querySelector("tbody");
    const presentAllBtn = document.getElementById("presentAllBtn");
    const absentAllBtn = document.getElementById("absentAllBtn");
    const submitBtn = document.getElementById("submitAttendance");
    const dateInput = document.getElementById("date");
    const jobDropdownMain = document.getElementById("main_job_id"); 

    let selectedLead = "";

   
    function clearWorkerTable() {
        workersTable.innerHTML = "";
    }

    // to fetch worker from labour lead
    function fetchWorkers(leadId) {
        clearWorkerTable(); //
        if (!leadId) {
            return; // dont fetch worker if lead is not there 
        }

        fetch(`fetch_workers.php?lead_id=${leadId}`) // to fetch worker table from lead 
            .then(response => {
                if (!response.ok) {
                    throw new Error("Failed to fetch workers.");
                }
                return response.json();
            })
            .then(data => {
                if (data.status === "success") {
                    if (data.workers.length === 0) {
                        workersTable.innerHTML = "<tr><td colspan='4'>No workers found for this Labour Lead.</td></tr>";
                        return;
                    }
                    populateWorkerTable(data.workers);
                } else {
                    console.error("Error fetching workers:", data.message);
                    workersTable.innerHTML = `<tr><td colspan='4'>Error fetching workers: ${data.message}</td></tr>`;
                }
            })
            .catch(error => {
                console.error("Error fetching workers:", error);
                workersTable.innerHTML = `<tr><td colspan='4'>Error fetching workers: ${error.message}</td></tr>`;
            });
    }

   //worker table 
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
                <td><span class="status">Absent</span></td>
                <td>
                    <button class="present-btn" data-id="${worker.worker_id}">Present</button>
                    <button class="absent-btn" data-id="${worker.worker_id}">Absent</button>
                </td>
            `;
            workersTable.appendChild(row);
        });
    }

    // generating job option
    function generateJobOptions() {
        let optionsHTML = "";
        for (const job of phpJobsData) {
            optionsHTML += `<option value="${job.id}">${job.job_name}</option>`;
        }
        return optionsHTML;
    }

    // labour lead drop down box 
    leadDropdown.addEventListener("change", function () {
        selectedLead = this.value;
        if (selectedLead) {
            fetchWorkers(selectedLead);
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


   
    presentAllBtn.addEventListener("click", function () {
        document.querySelectorAll(".status").forEach(status => status.textContent = "Present");
    });

 
    absentAllBtn.addEventListener("click", function () {
        document.querySelectorAll(".status").forEach(status => status.textContent = "Absent");
    });


    workersTable.addEventListener("click", function (event) {
        if (event.target.classList.contains("present-btn")) {
            event.target.closest("tr").querySelector(".status").textContent = "Present";
        } else if (event.target.classList.contains("absent-btn")) {
            event.target.closest("tr").querySelector(".status").textContent = "Absent";
        }
    });

    submitBtn.addEventListener("click", function () {
        if (!selectedLead) {
            alert("Please select a labor lead before submitting attendance.");
            return;
        }

        let attendanceData = [];
        document.querySelectorAll("#workersTable tbody tr").forEach(row => {
            let workerId = row.querySelector(".present-btn").dataset.id;
            let status = row.querySelector(".status").textContent === "Present" ? 1 : 0;
            let jobId = row.querySelector(".job-dropdown").value; 
            attendanceData.push({ worker_id: workerId, present: status, job_id: jobId }); 
        });

        if (attendanceData.length === 0) {
            alert("No workers to submit attendance for. Please select a valid Labour Lead.");
            return; 
        }


        fetch("update_attendance_action.php", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({
                lead_id: selectedLead,
                attendance: attendanceData 
            })
        })
            .then(response => {
                if (!response.ok) {
                    throw new Error("Error submitting attendance.");
                }
                return response.json();
            })
            .then(data => {
                if (data.status === "success") {
                    alert("Attendance updated successfully.");
                } else {
                    alert("Error: " + data.message);
                }
            })
            .catch(error => console.error("Error submitting attendance:", error));
    });
});