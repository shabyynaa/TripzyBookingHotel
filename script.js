const API = {
    hotels:  'api/hotel.php',
    auth:    'api/auth.php',
    booking: 'api/booking.php',
};

const DEV_MODE = false;

// ── STATE ──
let currentUserObj = null;
let pendingBooking = null;
let allHotels      = [];

// INIT
function populateDropdowns() {
    const ids = ['count-room', 'count-adult', 'count-child'];
    ids.forEach(id => {
        const el = document.getElementById(id);
        for (let i = 0; i <= 10; i++) {
            if (i === 0 && id !== 'count-child') continue;
            let opt = document.createElement('option');
            opt.value = i;
            opt.innerHTML = i + (id === 'count-room' ? ' Room' : id === 'count-adult' ? ' Adult' : ' Child');
            el.appendChild(opt);
        }
        el.value = (id === 'count-child' ? 0 : 1);
    });
}


window.onload = async function () {
    populateDropdowns();

    if (DEV_MODE) {
        currentUserObj = { id: 1, full_name: 'Dev User', ktp: '0000000000', phone: '08123456789', role: 'user' };
        loginSuccessAction(currentUserObj);
        return;
    }

// Cek apakah sudah login (session aktif)
try {
    const res  = await fetch(`${API.auth}?action=me`);
    const data = await res.json();
    if (data.success) {
        currentUserObj = data.user;
        loginSuccessAction(currentUserObj);
        return;
    }
} catch (e) {}

// Belum login — tampil auth page
document.getElementById('auth-page').style.display = 'flex';
};

// AUTH — LOGIN
async function loginAction() {
    const username = document.getElementById('login-id').value.trim();
    const password = document.getElementById('login-pass').value.trim();

    if (!username || !password) {
        alert('Username dan password wajib diisi!');
        return;
    }

    try {
        const res  = await fetch(`${API.auth}?action=login`, {
            method:  'POST',
            headers: { 'Content-Type': 'application/json' },
            body:    JSON.stringify({ username, password }),
        });
        const data = await res.json();

        if (data.success) {
            currentUserObj = data.user;
            loginSuccessAction(data.user);
        } else {
            alert(data.message || 'Login gagal');
        }
    } catch (e) {
        alert('Terjadi kesalahan, coba lagi.');
    }
}

// AUTH — REGISTER
async function signupAction() {
    const username  = document.getElementById('signup-user').value.trim();
    const password  = document.getElementById('signup-pass').value.trim();
    const full_name = document.getElementById('signup-fullname').value.trim();
    const ktp       = document.getElementById('signup-ktp').value.trim();
    const phone     = document.getElementById('signup-phone').value.trim();

    if (!username || !password || !full_name || !ktp || !phone) {
        alert('Semua field wajib diisi!');
        return;
    }

    try {
        const res  = await fetch(`${API.auth}?action=register`, {
            method:  'POST',
            headers: { 'Content-Type': 'application/json' },
            body:    JSON.stringify({ username, password, full_name, ktp, phone }),
        });
        const data = await res.json();

        if (data.success) {
            alert('Registrasi berhasil! Silakan login.');
            toggleAuth(true);
        } else {
            alert(data.message || 'Registrasi gagal');
        }
    } catch (e) {
        alert('Terjadi kesalahan, coba lagi.');
    }
}

// AFTER LOGIN SUCCESS
async function loginSuccessAction(user) {
    currentUserObj = user;

    // Kalau admin, redirect ke halaman admin
    if (user.role === 'admin') {
        window.location.href = 'admin.php';
        return;
    }

    document.getElementById('auth-page').style.display  = 'none';
    document.getElementById('main-app').classList.remove('hidden');
    document.getElementById('navbar').classList.remove('hidden');
    document.getElementById('user-info').innerText = `Hi, ${user.full_name}`;

    if (document.getElementById('acc-name'))  document.getElementById('acc-name').innerText  = user.full_name;
    if (document.getElementById('acc-ktp'))   document.getElementById('acc-ktp').innerText   = user.ktp;
    if (document.getElementById('acc-phone')) document.getElementById('acc-phone').innerText = user.phone;

    showPage('home-page');
    await loadHotels();
    renderRecommendations();
    loadBookingHistory();
}

// LOAD HOTELS DARI DATABASE
async function loadHotels(loc = 'all') {
    try {
        const url = loc === 'all'
            ? API.hotels
            : `${API.hotels}?loc=${encodeURIComponent(loc)}`;
        const res = await fetch(url);
        allHotels = await res.json();
        return allHotels;
    } catch (e) {
        console.error('Gagal load hotel:', e);
        return [];
    }
}

// SEARCH
async function performSearch() {
    const loc = document.getElementById('search-loc').value;
    showPage('results-page');
    const hotels = await loadHotels(loc);
    renderResults(hotels);
}

// RENDER HOTEL LIST
function renderResults(data) {
    const list = document.getElementById('hotel-list');
    if (!data || data.length === 0) {
        list.innerHTML = '<p style="text-align:center; padding:40px; color:#999;">Tidak ada hotel ditemukan.</p>';
        return;
    }
    list.innerHTML = data.map(h => `
        <div class="hotel-card">
            <img src="${h.thumb_url}" class="hotel-img-large" onerror="this.src='img/placeholder.jpg'">
            <div class="hotel-details">
                <div>
                    <div style="display:flex; justify-content:space-between; align-items:flex-start;">
                        <div>
                            <h2 style="font-size:1.4rem; font-weight:800;">${h.name}</h2>
                            <p style="color:#888; font-size:0.85rem; margin-top:4px;">📍 ${h.location}</p>
                        </div>
                        <div style="text-align:right;">
                            <div style="font-weight:700; color:var(--yellow);">⭐ ${h.rating}</div>
                            <small style="color:#aaa;">${Number(h.review_count).toLocaleString()} Reviews</small>
                        </div>
                    </div>
                </div>
                <div style="display:flex; justify-content:space-between; align-items:center; background:white; padding:18px; border-radius:12px; margin-top:15px; border:1px solid #f0f0f0;">
                    <div>
                        <small style="display:block; margin-bottom:4px; color:#999;">Best price from</small>
                        <h3 style="color:var(--charcoal);">Rp ${Number(h.min_price).toLocaleString()}</h3>
                    </div>
                    <button class="btn-gradient" onclick="openRoomModal(${h.id})">Select Room</button>
                </div>
            </div>
        </div>`).join('');
}

// RENDER RECOMMENDATIONS
function renderRecommendations() {
    const container = document.getElementById('recommendations');
    if (!container) return;
    const top = allHotels.slice(0, 8);
    container.innerHTML = top.map(h => `
        <div class="recom-card" onclick="openRoomModal(${h.id})">
            <div style="overflow:hidden; height:160px;">
                <img src="${h.thumb_url}" class="recom-img" onerror="this.src='img/placeholder.jpg'">
            </div>
            <div class="recom-info">
                <small style="color:var(--pink); font-weight:700;">${h.location}</small>
                <h4 style="margin:5px 0; font-size:0.95rem;">${h.name}</h4>
                <p style="color:var(--charcoal); font-weight:700; font-size:0.9rem;">
                    Rp ${Number(h.min_price).toLocaleString()}
                    <span style="color:#aaa; font-weight:400; font-size:0.75rem;"> / malam</span>
                </p>
            </div>
        </div>`).join('');
}

// OPEN ROOM MODAL
async function openRoomModal(hotelId) {
    try {
        const res   = await fetch(`${API.hotels}?id=${hotelId}`);
        const hotel = await res.json();

        document.getElementById('modal-hotel-name').innerText = hotel.name;
        document.getElementById('modal-hotel-loc').innerText  = `Exclusive Stays in ${hotel.location}`;

        if (document.getElementById('modal-hotel-address')) {
            document.getElementById('modal-hotel-address').innerText = hotel.address || hotel.location;
        }
        if (document.getElementById('modal-hotel-description')) {
            document.getElementById('modal-hotel-description').innerText = hotel.description || '-';
        }

        const facEl = document.getElementById('modal-hotel-facilities');
        if (facEl) {
            const facs = Array.isArray(hotel.facilities) ? hotel.facilities : JSON.parse(hotel.facilities || '[]');
            facEl.innerHTML = facs.map(f => `<span class="facility-tag">✦ ${f}</span>`).join('');
        }

        const reviewStatsEl = document.getElementById('review-stats');
        if (reviewStatsEl) reviewStatsEl.innerText = `Rated ${hotel.rating} based on ${Number(hotel.review_count).toLocaleString()} guests`;

        const reviewEl = document.getElementById('modal-hotel-reviews');
        if (reviewEl) {
            reviewEl.innerHTML = hotel.reviews && hotel.reviews.length > 0
                ? hotel.reviews.map(r => `
                    <div style="border-bottom:1px solid #eee; padding-bottom:10px;">
                        <strong>${r.reviewer}</strong>
                        <span style="color:var(--yellow); margin-left:8px;">${'★'.repeat(r.rating)}</span>
                        <p style="font-size:0.85rem; margin-top:5px; font-style:italic;">"${r.comment}"</p>
                    </div>`).join('')
                : '<p style="color:#aaa; font-style:italic;">Belum ada review.</p>';
        }

        const roomGrid = document.getElementById('room-grid');
        roomGrid.innerHTML = hotel.rooms && hotel.rooms.length > 0
            ? hotel.rooms.map(room => {
                const facs = Array.isArray(room.facilities)
                    ? room.facilities
                    : JSON.parse(room.facilities || '[]');
                return `
                <div class="room-card">
                    <img src="${room.img_url || room.img}" style="width:100%; height:180px; object-fit:cover; border-radius:12px; margin-bottom:15px;" onerror="this.src='img/placeholder.jpg'">
                    <h3 style="font-size:1.1rem; font-weight:700;">${room.name}</h3>
                    <div style="margin:10px 0;">
                        ${facs.map(f => `<small style="background:#f0f0f0; padding:2px 8px; border-radius:4px; margin-right:5px;">${f}</small>`).join('')}
                    </div>
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-top:15px;">
                        <h4 style="color:var(--charcoal);">Rp ${Number(room.price).toLocaleString()}</h4>
                        <button class="btn-gradient" onclick="confirmBooking('${hotel.name}', '${room.name}', ${room.price}, ${room.id}, ${hotel.id})">Reserve</button>
                    </div>
                </div>`;
            }).join('')
            : '<p style="color:#aaa;">Tidak ada kamar tersedia.</p>';

        document.getElementById('room-modal').style.display = 'flex';
    } catch (e) {
        console.error('Gagal load detail hotel:', e);
        alert('Gagal memuat detail hotel.');
    }
}

// BOOKING
function confirmBooking(hName, rType, price, roomId, hotelId) {
    const checkin  = document.getElementById('checkin').value;
    const checkout = document.getElementById('checkout').value;
    
    if (!checkin || !checkout) {
        alert('Mohon isi tanggal check-in dan check-out terlebih dahulu!');
        return;
    }

    // Hitung jumlah malam
    const msPerDay = 1000 * 60 * 60 * 24;
    const nights   = Math.max(1, Math.round((new Date(checkout) - new Date(checkin)) / msPerDay));
    const totalPrice = price * nights;

    const r = document.getElementById('count-room').value;
    const a = document.getElementById('count-adult').value;
    const c = document.getElementById('count-child').value;

    pendingBooking = { 
        hName, rType, 
        price: totalPrice,  // ← total sudah dikali malam
        roomId, hotelId, 
        config: `${r} Room, ${a} Adult, ${c} Child`, 
        roomCount: r, adultCount: a, childCount: c,
        nights  // ← simpan juga jumlah malamnya
    };
    document.getElementById('book-for').value = 'self';
    toggleGuestFields();
    closeModal('room-modal');
    document.getElementById('confirm-form-modal').style.display = 'flex';
}

function toggleGuestFields() {
    const type       = document.getElementById('book-for').value;
    const extra      = document.getElementById('extra-guest-fields');
    const nameInput  = document.getElementById('guest-name');
    const phoneInput = document.getElementById('guest-phone');

    if (type === 'self' && currentUserObj) {
        nameInput.value  = currentUserObj.full_name || currentUserObj.name;
        phoneInput.value = currentUserObj.phone;
        extra.classList.add('hidden');
        nameInput.disabled  = true;
        phoneInput.disabled = true;
    } else {
        nameInput.value  = '';
        phoneInput.value = '';
        extra.classList.remove('hidden');
        nameInput.disabled  = false;
        phoneInput.disabled = false;
    }
}

function proceedToPayment() {
    const name  = document.getElementById('guest-name').value;
    const bookFor = document.getElementById('book-for').value;
    const phone = bookFor == 'self'
        ? currentUserObj.phone
        : document.getElementById('guest-phone').value;

    if (!name || !phone) { alert('Nama dan nomor telepon wajib diisi!'); return; }

    pendingBooking.guestName  = name;
    pendingBooking.guestPhone = phone;
    pendingBooking.guestKTP   = (document.getElementById('book-for').value === 'self')
        ? (currentUserObj.ktp || '')
        : (document.getElementById('guest-id-num')?.value || '');

    document.getElementById('payment-summary').innerHTML = `
        <p><strong>${pendingBooking.hName}</strong></p>
        <p>${pendingBooking.rType} (${pendingBooking.config})</p>
        <hr style="margin:10px 0; opacity:0.2">
        <p>Total: <strong>Rp ${Number(pendingBooking.price).toLocaleString()}</strong></p>`;
    closeModal('confirm-form-modal');
    document.getElementById('payment-modal').style.display = 'flex';
}

// PROSES BAYAR
async function processFinalPayment(method) {
    try {
        const payload = {
            hotel_id:       pendingBooking.hotelId,
            room_id:        pendingBooking.roomId,
            guest_name:     pendingBooking.guestName,
            guest_ktp:      pendingBooking.guestKTP,
            guest_phone:    pendingBooking.guestPhone,
            room_count:     pendingBooking.roomCount,
            adult_count:    pendingBooking.adultCount,
            child_count:    pendingBooking.childCount,
            checkin:        document.getElementById('checkin')?.value  || null,
            checkout:       document.getElementById('checkout')?.value || null,
            total_price:    pendingBooking.price,
            payment_method: method,
            special_req:    document.getElementById('guest-requests')?.value || '',
        };
        console.log('payload:', payload);

        const res  = await fetch(API.booking, {
            method:  'POST',
            headers: { 'Content-Type': 'application/json' },
            body:    JSON.stringify(payload),
        });
        const data = await res.json();

        if (data.success) {
            closeModal('payment-modal');
            showReceipt(data.booking);
            pendingBooking = null;
            loadBookingHistory();
        } else {
            alert(data.message || 'Pembayaran gagal');
        }
    } catch (e) {
        console.error(e);
        alert('Terjadi kesalahan saat memproses pembayaran.');
    }
}

// BOOKING HISTORY
async function loadBookingHistory() {
    try {
        const res  = await fetch(`${API.booking}?user_id=${currentUserObj.id}`);
        const data = await res.json();
        renderBookingHistory(data);
    } catch (e) {
        console.error('Gagal load history:', e);
    }
}

function renderBookingHistory(bookings) {
    const container = document.getElementById('booking-history');
    if (!container) return;
    if (!bookings || bookings.length === 0) {
        container.innerHTML = '<p style="color:rgba(255,255,255,0.7); font-style:italic;">Belum ada booking.</p>';
        return;
    }
    container.innerHTML = bookings.map(b => `
        <div class="history-card" onclick="showReceiptById('${b.booking_code}')" style="cursor:pointer;">
            <div style="display:flex; justify-content:space-between; align-items:center;">
                <h4 style="color:var(--charcoal);">${b.hotel_name}</h4>
                <strong>Rp ${Number(b.total_price).toLocaleString()}</strong>
            </div>
            <p style="font-size:0.8rem; color:#666; margin-top:5px;">
                ${b.room_name} • ${b.created_at?.split(' ')[0]}
            </p>
        </div>`).join('');
}

// RECEIPT
function showReceipt(b) {
    document.getElementById('receipt-details').innerHTML = `
        <div style="background:#f9f9f9; padding:20px; border-radius:12px; line-height:1.8;">
            <p><strong>Booking ID:</strong> ${b.booking_code}</p>
            <p><strong>Hotel:</strong> ${b.hotel_name}</p>
            <p><strong>Kamar:</strong> ${b.room_name}</p>
            <p><strong>Tamu:</strong> ${b.guest_name}</p>
            <p><strong>KTP:</strong> ${b.guest_ktp}</p>
            <p><strong>Pembayaran:</strong> ${b.payment_method}</p>
            <hr style="margin:10px 0; opacity:0.2">
            <p style="font-size:1.1rem;"><strong>Total: Rp ${Number(b.total_price).toLocaleString()}</strong></p>
        </div>
        <a href="api/receipt.php?code=${b.booking_code}" target="_blank" 
           class="btn-gradient" 
           style="display:block; text-align:center; margin-top:16px; padding:12px; text-decoration:none;">
            ⬇ Download Struk PDF
        </a>`;
    document.getElementById('receipt-modal').style.display = 'flex';
}

async function showReceiptById(bookingCode) {
    try {
        const res  = await fetch(`${API.booking}?code=${bookingCode}`);
        const data = await res.json();
        if (data.success) showReceipt(data.booking);
    } catch (e) {}
}

// NAVIGATION & UTILITIES
function showPage(pageId, el) {
    document.querySelectorAll('main section').forEach(s => s.classList.add('hidden'));
    document.getElementById(pageId)?.classList.remove('hidden');
    if (el) {
        document.querySelectorAll('.nav-item').forEach(n => n.classList.remove('active'));
        el.classList.add('active');
    }
    if (pageId === 'bookings-page') loadBookingHistory();
    if (pageId === 'account-page')  fillAccountPage();
}

function fillAccountPage() {
    if (!currentUserObj) return;
    if (document.getElementById('acc-name'))  document.getElementById('acc-name').innerText  = currentUserObj.full_name;
    if (document.getElementById('acc-ktp'))   document.getElementById('acc-ktp').innerText   = currentUserObj.ktp;
    if (document.getElementById('acc-phone')) document.getElementById('acc-phone').innerText = currentUserObj.phone;
}

function unlockPassword() {
    const input = document.getElementById('acc-pass');
    if (input) { input.disabled = false; input.type = 'text'; input.value = ''; input.focus(); }
}

function toggleAuth(showLogin) {
    document.getElementById('login-section').classList.toggle('hidden', !showLogin);
    document.getElementById('signup-section').classList.toggle('hidden', showLogin);
}

function closeModal(id) {
    document.getElementById(id).style.display = 'none';
}

async function signOutAction() {
    await fetch(`${API.auth}?action=logout`, { method: 'POST' });
    location.reload();
}