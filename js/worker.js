document.addEventListener("DOMContentLoaded", function () {
    const addWorkerBtn = document.getElementById("addWorkerBtn");
    const addWorkerModal = document.getElementById("addWorkerModal");
    const closeBtn = document.getElementById("closeModal");
    const workerForm = document.getElementById("addWorkerForm");
    const deleteButtons = document.querySelectorAll(".delete-btn");
    const leadSelect = document.getElementById("lead_id");

    // Handle labour lead selection
    leadSelect.addEventListener("change", function () {
        const selectedLeadId = this.value;
        if (selectedLeadId) {
            // Replace the current history entry to avoid back button issues
            window.location.replace(`workers.php?lead_id=${selectedLeadId}`);
        }
    });

    // Open modal to add worker
    addWorkerBtn.addEventListener("click", function () {
        const selectedLead = leadSelect.value;
        if (!selectedLead) {
            alert("Please select a labour lead first.");
            return;
        }
        document.getElementById("lead_id_hidden").value = selectedLead;
        addWorkerModal.style.display = "block";
    });

    // Close modal
    closeBtn.addEventListener("click", function () {
        addWorkerModal.style.display = "none";
    });

    // Submit worker form
    workerForm.addEventListener("submit", function (event) {
        event.preventDefault();
        const formData = new FormData(workerForm);
        formData.append("action", "add");

        // --- START OF VALIDATION ---
        const workerName = formData.get("worker_name"); // Get worker name field
        const textOnlyRegex = /^[a-zA-Z\s]+$/; // Regex to allow only letters and spaces

        if (workerName && !textOnlyRegex.test(workerName)) {
            alert("Worker name should only contain text.");
            return; // Stop form submission
        }
        // --- END OF VALIDATION ---

        fetch("worker_action.php", {
            method: "POST",
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert("Worker added successfully!");
                window.location.replace(`workers.php?lead_id=${leadSelect.value}`); // Refresh without adding to history
            } else {
                alert("Error adding worker: " + data.message);
            }
        })
        .catch(error => {
            console.error("Error:", error);
            alert("Request failed. Check console for details.");
        });
    });

    // Delete worker
    deleteButtons.forEach(button => {
        button.addEventListener("click", function () {
            const workerId = this.dataset.workerId;
            if (confirm("Are you sure you want to delete this worker?")) {
                const formData = new FormData();
                formData.append("action", "delete");
                formData.append("worker_id", workerId);

                fetch("worker_action.php", {
                    method: "POST",
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert("Worker deleted successfully!");
                        window.location.replace(`workers.php?lead_id=${leadSelect.value}`); // Refresh without adding to history
                    } else {
                        alert("Error deleting worker: " + data.message);
                    }
                })
                .catch(error => {
                    console.error("Error:", error);
                    alert("Delete request failed. Check console for details.");
                });
            }
        });
    });
});