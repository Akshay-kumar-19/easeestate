document.addEventListener('DOMContentLoaded', function() {
    const notificationButton = document.getElementById('notificationButton');
    const notificationDropdown = document.getElementById('notificationDropdown');

    if (notificationButton && notificationDropdown) {
        notificationButton.addEventListener('click', function() {
            notificationDropdown.style.display = (notificationDropdown.style.display === 'block') ? 'none' : 'block';
        });

        window.addEventListener('click', function(event) {
            if (event.target !== notificationButton && !notificationDropdown.contains(event.target)) {
                notificationDropdown.style.display = 'none';
            }
        });
    } else {
        console.error('Notification button or dropdown element not found!');
    }
});

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


setInterval(fetchDashboardStats, 5000);


fetchDashboardStats();


