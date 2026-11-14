/* ============================================
   CineBook — JavaScript
   ============================================ */

/* ---- SEAT SELECTION ---- */
const SeatMap = (() => {
    let selectedSeats = [];

    function init() {
        const seats = document.querySelectorAll('.seat.available');
        seats.forEach(seat => {
            seat.addEventListener('click', () => toggleSeat(seat));
        });
    }

    function toggleSeat(seat) {
        const seatId   = seat.dataset.seatId;
        const seatCode = seat.dataset.seatCode;

        if (seat.classList.contains('selected')) {
            seat.classList.replace('selected', 'available');
            selectedSeats = selectedSeats.filter(s => s.id !== seatId);
        } else {
            seat.classList.replace('available', 'selected');
            selectedSeats.push({ id: seatId, code: seatCode });
        }
        updateSummary();
    }

    function updateSummary() {
        const count     = selectedSeats.length;
        const price     = parseFloat(document.getElementById('seat-price')?.value || 0);
        const total     = count * price;
        const countEl   = document.getElementById('selected-count');
        const totalEl   = document.getElementById('selected-total');
        const seatsEl   = document.getElementById('selected-seats-label');
        const inputEl   = document.getElementById('selected-seat-ids');
        const proceedBtn = document.getElementById('proceed-btn');

        if (countEl)  countEl.textContent = count;
        if (seatsEl)  seatsEl.textContent = selectedSeats.map(s => s.code).join(', ') || '—';
        if (totalEl)  totalEl.textContent = 'Rp ' + total.toLocaleString('id-ID');
        if (inputEl)  inputEl.value = selectedSeats.map(s => s.id).join(',');

        if (proceedBtn) {
            proceedBtn.disabled = count === 0;
            proceedBtn.textContent = count > 0
                ? `Book ${count} Seat${count > 1 ? 's' : ''} — Rp ${total.toLocaleString('id-ID')}`
                : 'Select Seats to Continue';
        }
    }

    return { init };
})();

/* ---- AUTO-DISMISS ALERTS ---- */
function initAlerts() {
    document.querySelectorAll('[data-auto-dismiss]').forEach(el => {
        setTimeout(() => {
            el.style.transition = 'opacity .4s';
            el.style.opacity = '0';
            setTimeout(() => el.remove(), 400);
        }, parseInt(el.dataset.autoDismiss) || 4000);
    });
}

/* ---- CONFIRM DELETE ---- */
function confirmDelete(formId, message) {
    if (confirm(message || 'Are you sure you want to delete this item?')) {
        document.getElementById(formId).submit();
    }
}

/* ---- AJAX: Showtimes by Date ---- */
function loadShowtimes(movieId, date) {
    const container = document.getElementById('showtime-container');
    if (!container) return;
    container.innerHTML = '<div class="text-center py-3"><div class="spinner-border spinner-border-sm text-primary"></div></div>';

    fetch(`${BASE_URL}/ajax/get_showtimes.php?movie_id=${movieId}&date=${date}`)
        .then(r => r.json())
        .then(data => {
            if (!data.success || data.showtimes.length === 0) {
                container.innerHTML = '<p class="text-muted-cb small">No showtimes available for this date.</p>';
                return;
            }
            let html = '<div class="d-flex flex-wrap gap-2">';
            data.showtimes.forEach(st => {
                html += `<a href="${BASE_URL}/booking/seats.php?showtime_id=${st.id}"
                            class="cb-showtime-btn text-decoration-none">
                            <div class="fw-bold">${st.show_time}</div>
                            <div class="small text-muted-cb">${st.hall}</div>
                            <div class="small text-warning">Rp ${parseInt(st.price).toLocaleString('id-ID')}</div>
                         </a>`;
            });
            html += '</div>';
            container.innerHTML = html;
        })
        .catch(() => {
            container.innerHTML = '<p class="text-danger small">Failed to load showtimes.</p>';
        });
}

/* ---- INIT ---- */
document.addEventListener('DOMContentLoaded', () => {
    SeatMap.init();
    initAlerts();
});

/* expose BASE_URL to JS — set in PHP before this file loads */
var BASE_URL = BASE_URL || '';
