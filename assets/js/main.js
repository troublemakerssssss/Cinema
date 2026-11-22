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
                : 'Continue to Ticket';
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

/* ---- UTILS ---- */
function escapeHtml(str) {
    if (str === null || str === undefined) return '';
    return String(str)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}

/* ---- AJAX: Showtimes by Date ---- */
let showtimesRequestId = 0;

function loadShowtimes(movieId, date) {
    const container = document.getElementById('showtime-container');
    if (!container) return;

    const requestId = ++showtimesRequestId;
    container.innerHTML = '<div class="text-center py-3"><div class="spinner-border spinner-border-sm text-dark"></div></div>';

    fetch(`${BASE_URL}/ajax/get_showtimes.php?movie_id=${encodeURIComponent(movieId)}&date=${encodeURIComponent(date)}`)
        .then(r => {
            if (!r.ok) {
                throw new Error('Network response was not ok');
            }
            return r.json();
        })
        .then(data => {
            if (requestId !== showtimesRequestId) return;
            if (!data.success || !data.showtimes || data.showtimes.length === 0) {
                container.innerHTML = '<p class="text-muted-cb small">No showtimes available for this date.</p>';
                return;
            }
            let html = '<div class="d-flex flex-wrap gap-3">';
            data.showtimes.forEach(st => {
                const url = data.logged_in
                    ? `${BASE_URL}/booking/seats.php?showtime_id=${encodeURIComponent(st.id)}`
                    : `${BASE_URL}/login.php`;
                const titleAttr = !data.logged_in ? ' title="Login to book"' : '';
                const time = escapeHtml(st.show_time);
                const hall = escapeHtml(st.hall);
                const price = Number(st.price).toLocaleString('id-ID');

                html += `<a href="${url}" class="cb-showtime-btn text-decoration-none"${titleAttr}>
                            <div class="st-time">${time}</div>
                            <div class="st-hall">${hall}</div>
                            <div class="st-price">Rp ${price}</div>
                         </a>`;
            });
            html += '</div>';
            container.innerHTML = html;
        })
        .catch(() => {
            if (requestId !== showtimesRequestId) return;
            container.innerHTML = '<p class="text-danger small">Failed to load showtimes.</p>';
        });
}

function initShowtimeTabs() {
    const tabsList = document.getElementById('dateTabs');
    if (!tabsList) return;

    const movieId = tabsList.dataset.movieId;
    const tabs = document.querySelectorAll('#dateTabs .nav-link[data-date]');
    if (!tabs.length || !movieId) return;

    tabs.forEach(tab => {
        tab.addEventListener('click', (e) => {
            e.preventDefault();
            tabs.forEach(t => t.classList.remove('active'));
            tab.classList.add('active');
            const date = tab.dataset.date;
            if (date) {
                loadShowtimes(movieId, date);
            }
        });
    });

    const activeTab = document.querySelector('#dateTabs .nav-link[data-date].active') || tabs[0];
    if (activeTab && activeTab.dataset.date) {
        if (!activeTab.classList.contains('active')) {
            activeTab.classList.add('active');
        }
        loadShowtimes(movieId, activeTab.dataset.date);
    }
}

/* ---- INIT ---- */
document.addEventListener('DOMContentLoaded', () => {
    SeatMap.init();
    initAlerts();
    initCarousels();
    initShowtimeTabs();
});

/* expose BASE_URL to JS — set in PHP before this file loads */
var BASE_URL = BASE_URL || '';

/* ============================================================
   MOVIE CAROUSELS
   Independent horizontal scroll carousels with prev/next nav.
   Each button uses data-target="<track-id>" to control its
   own carousel without affecting others.
   ============================================================ */
function initCarousels() {
    /* ---- Button navigation ---- */
    document.querySelectorAll('.cb-carousel-btn').forEach(btn => {
        const trackId = btn.dataset.target;
        const track   = document.getElementById(trackId);
        if (!track) return;

        btn.addEventListener('click', () => {
            const item      = track.querySelector('.cb-carousel-item');
            const gap       = parseFloat(getComputedStyle(track).gap) || 16;
            const itemWidth = item ? item.offsetWidth + gap : 300;
            // Scroll by one "page" = number of visible items × card width
            const visibleItems = Math.round(track.offsetWidth / itemWidth) || 1;
            const delta        = itemWidth * visibleItems;

            if (btn.classList.contains('cb-carousel-prev')) {
                track.scrollBy({ left: -delta, behavior: 'smooth' });
            } else {
                track.scrollBy({ left:  delta, behavior: 'smooth' });
            }
        });

        // Update button states when this track scrolls
        track.addEventListener('scroll', () => updateCarouselBtns(track), { passive: true });
        // Set initial button states
        updateCarouselBtns(track);
    });

    /* ---- Touch / pointer drag on desktop ---- */
    document.querySelectorAll('.cb-carousel-track').forEach(track => {
        let startX     = 0;
        let scrollLeft = 0;
        let isDown     = false;   // mouse is pressed
        let isDragging = false;   // drag threshold exceeded
        let wasDragged = false;   // used to block the click after a real drag

        track.addEventListener('pointerdown', e => {
            if (e.pointerType === 'touch') return; // let native touch handle it
            isDown     = true;
            isDragging = false;
            wasDragged = false;
            startX     = e.pageX - track.getBoundingClientRect().left;
            scrollLeft = track.scrollLeft;
            track.style.scrollBehavior = 'auto'; // instant during drag
        });

        track.addEventListener('pointermove', e => {
            if (!isDown || e.pointerType === 'touch') return;
            const x    = e.pageX - track.getBoundingClientRect().left;
            const walk = x - startX;
            // Only commit to drag once the pointer has moved > 5 px
            if (!isDragging) {
                if (Math.abs(walk) <= 5) return;
                isDragging = true;
                wasDragged = true;
                track.setPointerCapture(e.pointerId); // capture only after threshold
                track.style.cursor = 'grabbing';
            }
            track.scrollLeft = scrollLeft - walk * 1.2;
        });

        const stopDrag = () => {
            isDown     = false;
            isDragging = false;
            track.style.cursor = '';
            track.style.scrollBehavior = '';
        };
        track.addEventListener('pointerup',     stopDrag);
        track.addEventListener('pointercancel', stopDrag);

        // Block click-through only when a real drag occurred
        track.addEventListener('click', e => {
            if (wasDragged) {
                e.preventDefault();
                e.stopPropagation();
                wasDragged = false; // reset so the next plain click works
            }
        }, { capture: true });
    });
}

/**
 * Enable/disable prev and next buttons based on current scroll position.
 * Buttons are matched by their data-target attribute against the track's id.
 */
function updateCarouselBtns(track) {
    const id       = track.id;
    const prevBtn  = document.querySelector(`.cb-carousel-prev[data-target="${id}"]`);
    const nextBtn  = document.querySelector(`.cb-carousel-next[data-target="${id}"]`);
    const atStart  = track.scrollLeft < 4;
    const atEnd    = track.scrollLeft + track.offsetWidth >= track.scrollWidth - 4;

    if (prevBtn) {
        prevBtn.disabled          = atStart;
        prevBtn.setAttribute('aria-disabled', atStart);
    }
    if (nextBtn) {
        nextBtn.disabled          = atEnd;
        nextBtn.setAttribute('aria-disabled', atEnd);
    }
}
