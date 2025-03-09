document.addEventListener('DOMContentLoaded', loadFertilizerInventoryView);

function loadFertilizerInventoryView() {
    fetch('view_fertilizer_action.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: new URLSearchParams({
            action: 'fetch_inventory'
        }),
    })
    .then(response => response.text())
    .then(data => {
        document.getElementById('fertilizerTableViewBody').innerHTML = data;
    })
    .catch((error) => {
        console.error('Error:', error);
        alert('Error loading fertilizer inventory view.');
    });
}