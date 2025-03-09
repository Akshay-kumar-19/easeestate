document.addEventListener("DOMContentLoaded", function () {
    const viewOvertimeBtn = document.getElementById("viewOvertimeBtn");
    const dateOvertimeInput = document.getElementById("date_overtime");
    const overtimeDataDiv = document.getElementById("overtime_data");
    const overtimeTableBody = document.querySelector("#overtimeTable tbody");
    const totalWorkersOvertimeSpan = document.getElementById("total_workers_overtime");
    const totalOvertimeHoursSpan = document.getElementById("total_overtime_hours");

    // Function to fetch and display overtime data
    function fetchOvertimeData(date) {
        fetch(`fetch_overtime.php?date=${date}`)
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                overtimeTableBody.innerHTML = ''; // Clear existing table data
                totalWorkersOvertimeSpan.textContent = `Total Workers Overtime: ${data.totalWorkersOvertime}`;
                totalOvertimeHoursSpan.textContent = `Total Overtime Hours: ${data.totalOvertimeHours}`;

                if (data.status === "success" && data.overtimeRecords.length > 0) {
                    data.overtimeRecords.forEach(record => {
                        let row = overtimeTableBody.insertRow();
                        row.insertCell().textContent = record.worker_name;
                        row.insertCell().textContent = record.lead_name;
                        row.insertCell().textContent = record.job_name;
                        row.insertCell().textContent = record.total_overtime_hours;
                    });
                    overtimeDataDiv.style.display = 'block'; // Show overtime table
                } else {
                    overtimeTableBody.innerHTML = "<tr><td colspan='4'>No overtime records found for this date.</td></tr>";
                    overtimeDataDiv.style.display = 'block'; // Show even with no data, to display "No records" message
                }
            })
            .catch(error => {
                console.error("Error fetching overtime data:", error);
                overtimeTableBody.innerHTML = `<tr><td colspan='4'>Error fetching overtime data. Please check console.</td></tr>`;
                overtimeDataDiv.style.display = 'block'; // Show error message in overtime table area
            });
    }

    // Event listener for View Overtime button click
    viewOvertimeBtn.addEventListener("click", function() {
        const selectedDate = dateOvertimeInput.value;
        if (!selectedDate) {
            alert("Please select a date to view overtime.");
            return;
        }
        fetchOvertimeData(selectedDate);
    });
});