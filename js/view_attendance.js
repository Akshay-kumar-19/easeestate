document.addEventListener("DOMContentLoaded", function () {
    const leadDropdown = document.getElementById("lead_id");
    const workersTable = document.querySelector("#workersTable tbody");
    const presentAllBtn = document.getElementById("presentAllBtn");
    const absentAllBtn = document.getElementById("absentAllBtn");
    const submitBtn = document.getElementById("submitAttendance");
    const jobDropdownMain = document.getElementById("main_job_id");

    let selectedLead = "";

    function clearWorkerTable() {
        workersTable.innerHTML = "";
    }

    function fetchWorkers(leadId) {
        clearWorkerTable();
        if (!leadId) return;

        fetch(`fetch_workers.php?lead_id=${leadId}`)
            .then(response => {
                if (!response.ok) throw new Error("Failed to fetch workers.");
                return response.json();
            })
            .then(data => {
                if (data.status === "success") {
                    if (data.workers.length === 0) {
                        workersTable.innerHTML = "<tr><td colspan='4'>No workers found for this Labour Lead.</td></tr>";
                    } else {
                        populateWorkerTable(data.workers);
                    }
                } else {
                    console.error("Error:", data.message);
                    workersTable.innerHTML = `<tr><td colspan='4'>Error: ${data.message}</td></tr>`;
                }
            })
            .catch(error => {
                console.error("Fetch error:", error);
                workersTable.innerHTML = `<tr><td colspan='4'>Error: ${error.message}</td></tr>`;
            });
    }

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

    function generateJobOptions() {
        return phpJobsData.map(job =>
            `<option value="${job.id}">${job.job_name}</option>`
        ).join("");
    }

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

    presentAllBtn.addEventListener("click", () => {
        document.querySelectorAll(".status").forEach(status => {
            status.textContent = "Present";
        });
    });

    absentAllBtn.addEventListener("click", () => {
        document.querySelectorAll(".status").forEach(status => {
            status.textContent = "Absent";
        });
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
            alert("Please select a labour lead before submitting attendance.");
            return;
        }

        const attendanceData = [];

        document.querySelectorAll("#workersTable tbody tr").forEach(row => {
            const workerId = row.dataset.workerId;
            const status = row.querySelector(".status").textContent === "Present" ? 1 : 0;
            const jobId = row.querySelector(".job-dropdown").value;

            attendanceData.push({
                worker_id: workerId,
                present: status,
                job_id: jobId
            });
        });

        if (attendanceData.length === 0) {
            alert("No workers found to submit attendance for.");
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
                if (!response.ok) throw new Error("Error submitting attendance.");
                return response.json();
            })
            .then(data => {
                if (data.status === "success") {
                    alert("Attendance updated successfully.");
                } else {
                    alert("Error: " + data.message);
                }
            })
            .catch(error => {
                console.error("Submission error:", error);
                alert("Failed to submit attendance. Please try again.");
            });
    });
});
