// This file contains JavaScript code for client-side functionality, including AJAX calls for smoother user experience.

// Function to check workstation availability
function checkAvailability() {
    const date = document.getElementById('reservation-date').value;
    const timeSlot = document.getElementById('time-slot').value;

    fetch(`api/check-availability.php?date=${date}&timeSlot=${timeSlot}`)
        .then(response => response.json())
        .then(data => {
            if (data.available) {
                alert('Workstation is available for reservation.');
            } else {
                alert('Selected workstation is already booked for this time slot.');
            }
        })
        .catch(error => console.error('Error:', error));
}

// Function to reserve a workstation
function reserveWorkstation() {
    const workstationId = document.getElementById('workstation-id').value;
    const date = document.getElementById('reservation-date').value;
    const timeSlot = document.getElementById('time-slot').value;

    const reservationData = {
        workstationId: workstationId,
        date: date,
        timeSlot: timeSlot
    };

    fetch('api/reserve-workstation.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(reservationData)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Reservation successful!');
        } else {
            alert('Reservation failed: ' + data.message);
        }
    })
    .catch(error => console.error('Error:', error));
}

// Event listeners for reservation form
document.getElementById('check-availability-btn').addEventListener('click', checkAvailability);
document.getElementById('reserve-btn').addEventListener('click', reserveWorkstation);