document.addEventListener("DOMContentLoaded", function () {
    const leadDropdown = document.getElementById("lead_id");
    const viewAttendanceBtn = document.getElementById("viewAttendanceBtn");
    const dailyViewRadio = document.getElementById("daily");
    const weeklyViewRadio = document.getElementById("weekly");
    const dateDailyInput = document.getElementById("date_daily");
    const monthWeeklyDropdown = document.getElementById("month_weekly");
    const weekWeeklyDropdown = document.getElementById("week_weekly");
    const weeklyOptionsDiv = document.getElementById("weekly_options");
    const attendanceDataDailyDiv = document.getElementById("attendance_data_daily");
    const attendanceDataWeeklyDiv = document.getElementById("attendance_data_weekly");
    const dailyAttendanceTableBody = document.querySelector("#dailyAttendanceTable tbody");
    const weeklyAttendanceTableBody = document.querySelector("#weeklyAttendanceTable tbody");
    const totalPresentDailySpan = document.getElementById("total_present_daily");
    const totalPresentWeeklySpan = document.getElementById("total_present_weekly");


    let selectedLeadId = "";
    let currentViewType = "daily"; // Default view is daily

    // Event listener for Labour Lead dropdown change
    leadDropdown.addEventListener("change", function() {
        selectedLeadId = this.value;
    });

    // Event listener for View Type change (Daily/Weekly)
    const viewTypeDropdown = document.getElementById("view_type");
    viewTypeDropdown.addEventListener("change", function() {
        currentViewType = this.value;
        if (currentViewType === 'weekly') {
            weeklyOptionsDiv.style.display = 'block';
            dateDailyInput.style.display = 'none';
        } else {
            weeklyOptionsDiv.style.display = 'none';
            dateDailyInput.style.display = 'block';
        }
        attendanceDataDailyDiv.style.display = 'none'; // Hide tables when view type changes
        attendanceDataWeeklyDiv.style.display = 'none';
    });


    // Function to fetch and display daily attendance
    function fetchDailyAttendance(leadId, date) {
        console.log("Fetching daily attendance for Lead ID:", leadId, "Date:", date); // Debug log
        fetch(`fetch_daily_attendance.php?lead_id=${leadId}&date=${date}`)
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                console.log("Daily attendance data received:", data); // Debug log
                dailyAttendanceTableBody.innerHTML = ''; // Clear existing table data
                totalPresentDailySpan.textContent = `Total Present: ${data.totalPresent}`; // Update total present count

                if (data.status === "success" && data.attendance.length > 0) {
                    data.attendance.forEach(record => {
                        let row = dailyAttendanceTableBody.insertRow();
                        row.insertCell().textContent = record.worker_name;
                        row.insertCell().textContent = record.job_name;
                        row.insertCell().textContent = record.present_status;
                    });
                    attendanceDataDailyDiv.style.display = 'block'; // Show daily attendance table
                    attendanceDataWeeklyDiv.style.display = 'none'; // Hide weekly table
                } else {
                    dailyAttendanceTableBody.innerHTML = "<tr><td colspan='3'>No attendance records found for this date.</td></tr>";
                    attendanceDataDailyDiv.style.display = 'block'; // Show even with no data, to display the "No records" message
                    attendanceDataWeeklyDiv.style.display = 'none';
                }
            })
            .catch(error => {
                console.error("Error fetching daily attendance:", error);
                dailyAttendanceTableBody.innerHTML = `<tr><td colspan='3'>Error fetching attendance. Please check console.</td></tr>`;
                attendanceDataDailyDiv.style.display = 'block'; // Show error message in daily table area
                attendanceDataWeeklyDiv.style.display = 'none';
            });
    }

    // Function to fetch and display weekly attendance
    function fetchWeeklyAttendance(leadId, month, weekNumber) {
        console.log("Fetching weekly attendance for Lead ID:", leadId, "Month:", month, "Week:", weekNumber); // Debug log
        fetch(`fetch_weekly_attendance.php?lead_id=${leadId}&month=${month}&week=${weekNumber}`)
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                console.log("Weekly attendance data received:", data); // Debug log
                weeklyAttendanceTableBody.innerHTML = ''; // Clear existing table data
                totalPresentWeeklySpan.textContent = `Total Present This Week: ${data.weeklyAttendance.reduce((sum, worker) => sum + worker.total_present, 0)}`; // Calculate and update total present for the week

                if (data.status === "success" && data.weeklyAttendance.length > 0) {
                    data.weeklyAttendance.forEach(workerData => {
                        let row = weeklyAttendanceTableBody.insertRow();
                        row.insertCell().textContent = workerData.worker_name;
                        row.insertCell().textContent = workerData.Mon || 'Absent'; // Use 'Absent' as default if no data for the day
                        row.insertCell().textContent = workerData.Tue || 'Absent';
                        row.insertCell().textContent = workerData.Wed || 'Absent';
                        row.insertCell().textContent = workerData.Thu || 'Absent';
                        row.insertCell().textContent = workerData.Fri || 'Absent';
                        row.insertCell().textContent = workerData.Sat || 'Absent';
                        row.insertCell().textContent = workerData.Sun || 'Absent';
                        row.insertCell().textContent = workerData.total_present;
                    });
                    attendanceDataWeeklyDiv.style.display = 'block'; // Show weekly attendance table
                    attendanceDataDailyDiv.style.display = 'none'; // Hide daily table
                } else {
                    weeklyAttendanceTableBody.innerHTML = "<tr><td colspan='9'>No attendance records found for this week.</td></tr>";
                    attendanceDataWeeklyDiv.style.display = 'block'; // Show even with no data, to display "No records" message
                    attendanceDataDailyDiv.style.display = 'none';
                }
            })
            .catch(error => {
                console.error("Error fetching weekly attendance:", error);
                weeklyAttendanceTableBody.innerHTML = `<tr><td colspan='9'>Error fetching weekly attendance. Please check console.</td></tr>`;
                attendanceDataWeeklyDiv.style.display = 'block'; // Show error message in weekly table area
                attendanceDataDailyDiv.style.display = 'none';
            });
    }


    // Event listener for View Attendance button click
    viewAttendanceBtn.addEventListener("click", function() {
        if (!selectedLeadId) {
            alert("Please select a Labour Lead to view attendance.");
            return;
        }

        if (currentViewType === 'daily') {
            const selectedDate = dateDailyInput.value;
            if (!selectedDate) {
                alert("Please select a date to view daily attendance.");
                return;
            }
            fetchDailyAttendance(selectedLeadId, selectedDate);
        } else if (currentViewType === 'weekly') {
            const selectedMonth = monthWeeklyDropdown.value;
            const selectedWeek = weekWeeklyDropdown.value;
            if (!selectedMonth || !selectedWeek) {
                alert("Please select a month and week number to view weekly attendance.");
                return;
            }
            fetchWeeklyAttendance(selectedLeadId, selectedMonth, selectedWeek);
        }
    });
});