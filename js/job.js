document.addEventListener("DOMContentLoaded", function () {
    loadJobs();

    document.getElementById("jobForm").addEventListener("submit", function (e) {
        e.preventDefault();
        saveJob();
    });
});

// Open Modal for Add/Edit
function openModal(jobId = "", jobName = "", dailyWage = "", perKgRate = "", overtimeRate = "0.00") {
    document.getElementById("jobId").value = jobId; // Store Job ID in hidden field for updates
    document.getElementById("jobName").value = jobName;
    document.getElementById("dailyWage").value = dailyWage;
    document.getElementById("perKgRate").value = perKgRate;
    document.getElementById("overtimeRate").value = overtimeRate; // Set overtime rate value
    document.getElementById("modalTitle").innerText = jobId ? "Edit Job" : "Add Job";
    document.getElementById("submitBtn").innerText = jobId ? "Update" : "Save";
    document.getElementById("jobModal").style.display = "block";
}

// Close Modal
function closeModal() {
    document.getElementById("jobModal").style.display = "none";
}

// Edit Job (Fix: Send job ID and overtimeRate)
function editJob(jobId, jobName, dailyWage, perKgRate, overtimeRate) {
    openModal(jobId, jobName, dailyWage, perKgRate, overtimeRate); // Pass overtimeRate to openModal
}

// Fetch and Load Jobs
function loadJobs() {
    fetch('job_actions.php')
        .then(response => response.json())
        .then(data => {
            const jobsTableBody = document.getElementById("jobsTableBody");
            jobsTableBody.innerHTML = '';

            if (data.length === 0) {
                jobsTableBody.innerHTML = '<tr><td colspan="5">No jobs found.</td></tr>'; // colspan to 5 because of new column
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

// Save Job (Fix: Send job ID when updating and include overtimeRate)
function saveJob() {
    let jobId = document.getElementById("jobId").value; // Hidden input value
    let jobName = document.getElementById("jobName").value.trim();
    let dailyWage = document.getElementById("dailyWage").value;
    let perKgRate = document.getElementById("perKgRate").value;
    let overtimeRate = document.getElementById("overtimeRate").value; // Get overtimeRate value

    // --- START OF VALIDATION ---
    const textOnlyRegex = /^[a-zA-Z\s]+$/; // Regex to allow only letters and spaces

    if (!jobName) {
        alert("Enter job name");
        return;
    }

    if (!textOnlyRegex.test(jobName)) {
        alert("Job name should only contain text.");
        return; // Stop form submission
    }
    // --- END OF VALIDATION ---

    let formData = new FormData();
    formData.append("action", jobId ? "update" : "add");
    formData.append("job_id", jobId);  // Fix: Send job_id for updates
    formData.append("job_name", jobName);
    formData.append("daily_wage", dailyWage);
    formData.append("per_kg_rate", perKgRate);
    formData.append("overtime_rate", overtimeRate); // Append overtimeRate to formData

    fetch("job_actions.php", {
        method: "POST",
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        alert(data.message);
        closeModal();
        loadJobs(); // Refresh jobs
    })
    .catch(error => console.error("Error saving job:", error));
}

// Delete Job (Fix: Ensure job_id is sent)
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