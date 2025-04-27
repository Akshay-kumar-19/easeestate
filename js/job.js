document.addEventListener("DOMContentLoaded", function () {
    loadJobs();

    document.getElementById("jobForm").addEventListener("submit", function (e) {
        e.preventDefault();
        saveJob();
    });
});


function openModal(jobId = "", jobName = "", dailyWage = "", perKgRate = "", overtimeRate = "0.00") {
    document.getElementById("jobId").value = jobId; 
    document.getElementById("jobName").value = jobName;
    document.getElementById("dailyWage").value = dailyWage;
    document.getElementById("perKgRate").value = perKgRate;
    document.getElementById("overtimeRate").value = overtimeRate; 
    document.getElementById("modalTitle").innerText = jobId ? "Edit Job" : "Add Job";
    document.getElementById("submitBtn").innerText = jobId ? "Update" : "Save";
    document.getElementById("jobModal").style.display = "block";
}


function closeModal() {
    document.getElementById("jobModal").style.display = "none";
}


function editJob(jobId, jobName, dailyWage, perKgRate, overtimeRate) {
    openModal(jobId, jobName, dailyWage, perKgRate, overtimeRate); 
}

function loadJobs() {
    fetch('job_actions.php')
        .then(response => response.json())
        .then(data => {
            const jobsTableBody = document.getElementById("jobsTableBody");
            jobsTableBody.innerHTML = '';

            if (data.length === 0) {
                jobsTableBody.innerHTML = '<tr><td colspan="5">No jobs found.</td></tr>';
                return;
            }

            data.forEach(job => {
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td>${job.job_name}</td>
                    <td>₹${parseFloat(job.daily_wage).toFixed(2)}</td>
                    <td>₹${parseFloat(job.per_kg_rate).toFixed(2)}</td>
                    <td>₹${parseFloat(job.overtime_hourly_rate).toFixed(2)}</td>
                    <td class="action-buttons">
                        <button class="edit-btn" onclick="editJob('${job.job_id}', '${job.job_name}', ${job.daily_wage}, ${job.per_kg_rate}, ${job.overtime_hourly_rate})">Edit</button>
                        <button class="delete-btn" onclick="deleteJob(${job.job_id})">Delete</button>
                    </td>
                `;
                jobsTableBody.appendChild(row);
            });
        })
        .catch(error => console.error("Error loading jobs:", error));
}

function saveJob() {
    let jobId = document.getElementById("jobId").value;
    let jobName = document.getElementById("jobName").value.trim();
    let dailyWage = document.getElementById("dailyWage").value;
    let perKgRate = document.getElementById("perKgRate").value;
    let overtimeRate = document.getElementById("overtimeRate").value;

    const textOnlyRegex = /^[a-zA-Z\s]+$/;

    if (!jobName) {
        alert("Enter job name");
        return;
    }

    if (!textOnlyRegex.test(jobName)) {
        alert("Job name should only contain text.");
        return;
    }

    let formData = new FormData();
    formData.append("action", jobId ? "update" : "add");
    formData.append("job_id", jobId);
    formData.append("job_name", jobName);
    formData.append("daily_wage", dailyWage);
    formData.append("per_kg_rate", perKgRate);
    formData.append("overtime_rate", overtimeRate);

    fetch("job_actions.php", {
        method: "POST",
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        alert(data.message);
        closeModal();
        loadJobs();
    })
    .catch(error => console.error("Error saving job:", error));
}

function deleteJob(jobId) {
    if (!jobId) {
        alert("Invalid job!");
        return;
    }

    if (confirm("Are you sure you want to delete this job?")) {
        fetch(`job_actions.php?action=delete&job_id=${jobId}`)
            .then(response => response.json())
            .then(data => {
                alert(data.message);
                loadJobs();
            })
            .catch(error => console.error("Error deleting job:", error));
    }
}