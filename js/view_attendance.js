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
    let currentViewType = "daily"; 

  
    leadDropdown.addEventListener("change", function() {
        selectedLeadId = this.value;
    });

   
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
        attendanceDataDailyDiv.style.display = 'none'; 
        attendanceDataWeeklyDiv.style.display = 'none';
    });


    //  fetch and display daily attendance
    function fetchDailyAttendance(leadId, date) {
        console.log("Fetching daily attendance for Lead ID:", leadId, "Date:", date); 
        fetch(`fetch_daily_attendance.php?lead_id=${leadId}&date=${date}`)
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                console.log("Daily attendance data received:", data); 
                dailyAttendanceTableBody.innerHTML = ''; 
                totalPresentDailySpan.textContent = `Total Present: ${data.totalPresent}`; 

                if (data.status === "success" && data.attendance.length > 0) {
                    data.attendance.forEach(record => {
                        let row = dailyAttendanceTableBody.insertRow();
                        row.insertCell().textContent = record.worker_name;
                        row.insertCell().textContent = record.job_name;
                        row.insertCell().textContent = record.present_status;
                    });
                    attendanceDataDailyDiv.style.display = 'block'; 
                    attendanceDataWeeklyDiv.style.display = 'none'; 
                } else {
                    dailyAttendanceTableBody.innerHTML = "<tr><td colspan='3'>No attendance records found for this date.</td></tr>";
                    attendanceDataDailyDiv.style.display = 'block'; 
                    attendanceDataWeeklyDiv.style.display = 'none';
                }
            })
            .catch(error => {
                console.error("Error fetching daily attendance:", error);
                dailyAttendanceTableBody.innerHTML = `<tr><td colspan='3'>Error fetching attendance. Please check console.</td></tr>`;
                attendanceDataDailyDiv.style.display = 'block'; 
                attendanceDataWeeklyDiv.style.display = 'none';
            });
    }

    //  fetch and display weekly attendance
    function fetchWeeklyAttendance(leadId, month, weekNumber) {
        console.log("Fetching weekly attendance for Lead ID:", leadId, "Month:", month, "Week:", weekNumber); 
        fetch(`fetch_weekly_attendance.php?lead_id=${leadId}&month=${month}&week=${weekNumber}`)
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                console.log("Weekly attendance data received:", data); 
                weeklyAttendanceTableBody.innerHTML = ''; 
                totalPresentWeeklySpan.textContent = `Total Present This Week: ${data.weeklyAttendance.reduce((sum, worker) => sum + worker.total_present, 0)}`; // Calculate and update total present for the week

                if (data.status === "success" && data.weeklyAttendance.length > 0) {
                    data.weeklyAttendance.forEach(workerData => {
                        let row = weeklyAttendanceTableBody.insertRow();
                        row.insertCell().textContent = workerData.worker_name;
                        row.insertCell().textContent = workerData.Mon || 'Absent'; 
                        row.insertCell().textContent = workerData.Tue || 'Absent';
                        row.insertCell().textContent = workerData.Wed || 'Absent';
                        row.insertCell().textContent = workerData.Thu || 'Absent';
                        row.insertCell().textContent = workerData.Fri || 'Absent';
                        row.insertCell().textContent = workerData.Sat || 'Absent';
                        row.insertCell().textContent = workerData.Sun || 'Absent';
                        row.insertCell().textContent = workerData.total_present;
                    });
                    attendanceDataWeeklyDiv.style.display = 'block'; e
                    attendanceDataDailyDiv.style.display = 'none'; 
                } else {
                    weeklyAttendanceTableBody.innerHTML = "<tr><td colspan='9'>No attendance records found for this week.</td></tr>";
                    attendanceDataWeeklyDiv.style.display = 'block';
                    attendanceDataDailyDiv.style.display = 'none';
                }
            })
            .catch(error => {
                console.error("Error fetching weekly attendance:", error);
                weeklyAttendanceTableBody.innerHTML = `<tr><td colspan='9'>Error fetching weekly attendance. Please check console.</td></tr>`;
                attendanceDataWeeklyDiv.style.display = 'block'; 
                attendanceDataDailyDiv.style.display = 'none';
            });
    }


  
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