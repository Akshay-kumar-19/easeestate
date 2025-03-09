document.addEventListener('DOMContentLoaded', function () {
    const fertilizerSelect = document.getElementById('fertilizerSelect');
    const quantityInput = document.getElementById('quantity');
    const unitSelect = document.getElementById('unit'); // Direct access to unit dropdown
    const assignFertilizerForm = document.getElementById('assignFertilizerForm');
    const assignmentMessageDiv = document.getElementById('assignmentMessage');
    const assignmentErrorDiv = document.getElementById('assignmentError');


    // Removed unitDisplayInput and unitInput as they are no longer needed

    // Removed fertilizerSelect event listener for unit display/hidden input - not needed anymore

    assignFertilizerForm.addEventListener('submit', function (event) {
        event.preventDefault();

        const formData = new FormData(assignFertilizerForm);
        const fertilizerNameTypeUnit = formData.get('fertilizer_name_type');
        if (fertilizerNameTypeUnit) {
            const parts = fertilizerNameTypeUnit.split('|');
            formData.set('fertilizer_name', parts[0]); // Set fertilizer_name
            formData.set('fertilizer_type', parts[1]); // Set fertilizer_type
            // Unit is now directly from the unit dropdown, no need to set it here
        }


        fetch('assign_fertilizer_action.php', {
            method: 'POST',
            body: formData,
        })
        .then(response => response.json())
        .then(data => {
            assignmentMessageDiv.style.display = 'none';
            assignmentErrorDiv.style.display = 'none';

            if (data.status === 'success') {
                assignmentMessageDiv.textContent = data.message;
                assignmentMessageDiv.style.display = 'block';
                assignFertilizerForm.reset(); // Clear form on success
                // No need to clear unitDisplayInput anymore
                // Optionally, redirect or update UI further
            } else {
                assignmentErrorDiv.textContent = data.message;
                assignmentErrorDiv.style.display = 'block';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            assignmentErrorDiv.textContent = 'Error assigning fertilizer.';
            assignmentErrorDiv.style.display = 'block';
        });
    });
});