function fetchDashboardStats() {
    fetch('fetch_dashboard_stats.php')
        .then(response => response.json())
        .then(data => {
            document.getElementById('totalWorkersToday').innerText = data.totalWorkersToday;
            document.getElementById('todaysCrop').innerText = data.todaysCrop + " kg";
            document.getElementById('lastMonthPayment').innerText = "$" + data.lastMonthPayment;
            document.getElementById('lastWeekPayment').innerText = "$" + data.lastWeekPayment;
            document.getElementById('pendingTools').innerText = data.pendingTools;
        })
        .catch(error => console.error('Error fetching dashboard stats:', error));
}

// Refresh every 5 seconds
setInterval(fetchDashboardStats, 5000);

// Initial fetch on page load
fetchDashboardStats();
