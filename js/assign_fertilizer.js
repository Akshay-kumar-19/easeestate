document.addEventListener('DOMContentLoaded', function () {
    const fertilizerSelect = document.getElementById('fertilizerSelect');
    const quantityInput = document.getElementById('quantity');
    const unitSelect = document.getElementById('unit'); 
    const assignFertilizerForm = document.getElementById('assignFertilizerForm');
    const assignmentMessageDiv = document.getElementById('assignmentMessage');
    const assignmentErrorDiv = document.getElementById('assignmentError');


    

    assignFertilizerForm.addEventListener('submit', function (event) {
        event.preventDefault();

        const formData = new FormData(assignFertilizerForm);
        const fertilizerNameTypeUnit = formData.get('fertilizer_name_type');
        if (fertilizerNameTypeUnit) {
            const parts = fertilizerNameTypeUnit.split('|');
            formData.set('fertilizer_name', parts[0]); // fertilizer_name
            formData.set('fertilizer_type', parts[1]); // fertilizer_type
          
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
                assignFertilizerForm.reset(); 
                
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