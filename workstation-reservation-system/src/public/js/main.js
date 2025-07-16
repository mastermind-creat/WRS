// This file contains JavaScript code for client-side functionality, including AJAX calls for smoother user experience.

// Function to check workstation availability
function checkAvailability() {
    const date = document.getElementById('reservation-date')?.value;
    const timeSlot = document.getElementById('time-slot')?.value;

    if (!date || !timeSlot) {
        alert('Please select a date and time slot.');
        return;
    }

    fetch(`api/check-availability.php?date=${encodeURIComponent(date)}&timeSlot=${encodeURIComponent(timeSlot)}`)
        .then(response => {
            if (!response.ok) throw new Error('Network response was not ok');
            return response.json();
        })
        .then(data => {
            if (data.available) {
                alert('Workstation is available for reservation.');
            } else {
                alert('Selected workstation is already booked for this time slot.');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while checking availability. Please try again.');
        });
}

// Function to reserve a workstation
function reserveWorkstation() {
    const workstationId = document.getElementById('workstation-id')?.value;
    const date = document.getElementById('reservation-date')?.value;
    const timeSlot = document.getElementById('time-slot')?.value;

    if (!workstationId || !date || !timeSlot) {
        alert('Please fill in all reservation details.');
        return;
    }

    const reservationData = {
        workstationId: workstationId,
        date: date,
        timeSlot: timeSlot
    };

    fetch('api/reserve-workstation.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest' // Optional: Helps server identify AJAX
        },
        body: JSON.stringify(reservationData)
    })
    .then(response => {
        if (!response.ok) throw new Error('Server error');
        return response.json();
    })
    .then(data => {
        if (data.success) {
            alert('Reservation successful!');
            // Optionally clear form or redirect
            document.getElementById('reservation-form')?.reset();
        } else {
            alert('Reservation failed: ' + (data.message || 'Unknown error'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while reserving. Please try again.');
    });
}

// Event listeners for reservation form
document.addEventListener('DOMContentLoaded', function() {
    const checkBtn = document.getElementById('check-availability-btn');
    const reserveBtn = document.getElementById('reserve-btn');

    if (checkBtn) checkBtn.addEventListener('click', checkAvailability);
    if (reserveBtn) reserveBtn.addEventListener('click', reserveWorkstation);

    // Sidebar toggle for mobile responsiveness
    const sidebarFab = document.querySelector('.sidebar-fab');
    const sidebar = document.querySelector('.sidebar');
    const sidebarOverlay = document.querySelector('.sidebar-overlay');
    const dashboardMain = document.querySelector('.dashboard-main');

    if (sidebarFab && sidebar && dashboardMain) {
        sidebarFab.addEventListener('click', function() {
            const isOpen = sidebar.classList.toggle('open');
            if (window.innerWidth < 992) {
                dashboardMain.style.transform = isOpen ? 'translateX(220px)' : 'translateX(0)';
            }
            if (sidebarOverlay) {
                sidebarOverlay.style.display = isOpen ? 'block' : 'none';
            }
        });
    }

    if (sidebarOverlay && sidebar) {
        sidebarOverlay.addEventListener('click', function() {
            sidebar.classList.remove('open');
            if (window.innerWidth < 992 && dashboardMain) {
                dashboardMain.style.transform = 'translateX(0)';
            }
            sidebarOverlay.style.display = 'none';
        });
    }

    // Optional: Close sidebar if window is resized above 992px
    window.addEventListener('resize', function() {
        if (window.innerWidth >= 992 && sidebar) {
            sidebar.classList.remove('open');
            if (dashboardMain) dashboardMain.style.transform = 'none';
            if (sidebarOverlay) sidebarOverlay.style.display = 'none';
        }
    });
});