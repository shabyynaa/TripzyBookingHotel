<?php
session_start();
require 'config/db.php';

// Cek apakah sudah login dan role admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: index.html');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tripzy Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        :root {
            --pink:    #E05297;
            --pink-lt: #FFB1D4;
            --yellow:  #FFD372;
            --charcoal:#2B2B2B;
            --bg:      #F7F7F7;
            --white:   #FFFFFF;
            --border:  #EBEBEB;
            --text:    #2B2B2B;
            --muted:   #888;
            --radius:  12px;
            --shadow:  0 2px 12px rgba(0,0,0,0.07);
        }

        body { font-family: 'Poppins', sans-serif; background: var(--bg); color: var(--text); }

        /* ── SIDEBAR ── */
        .sidebar {
            position: fixed; top: 0; left: 0;
            width: 220px; height: 100vh;
            background: var(--charcoal);
            display: flex; flex-direction: column;
            padding: 30px 0;
            z-index: 100;
        }
        .sidebar-logo {
            font-size: 1.6rem; font-weight: 800;
            color: var(--white); text-align: center;
            letter-spacing: -1px; margin-bottom: 8px;
        }
        .sidebar-sub {
            font-size: 0.65rem; color: var(--pink-lt);
            text-align: center; letter-spacing: 3px;
            text-transform: uppercase; margin-bottom: 36px;
        }
        .nav-link {
            display: flex; align-items: center; gap: 12px;
            padding: 12px 24px; color: rgba(255,255,255,0.6);
            cursor: pointer; font-size: 0.88rem; font-weight: 500;
            transition: all 0.2s; border-left: 3px solid transparent;
            text-decoration: none;
        }
        .nav-link:hover, .nav-link.active {
            color: var(--white);
            background: rgba(255,255,255,0.06);
            border-left-color: var(--pink);
        }
        .nav-link svg { flex-shrink: 0; }
        .sidebar-bottom {
            margin-top: auto; padding: 0 16px;
        }
        .btn-logout {
            width: 100%; padding: 10px;
            background: rgba(224,82,151,0.15);
            border: 1px solid rgba(224,82,151,0.3);
            color: var(--pink-lt); border-radius: var(--radius);
            cursor: pointer; font-family: 'Poppins', sans-serif;
            font-size: 0.85rem; font-weight: 600; transition: all 0.2s;
        }
        .btn-logout:hover { background: var(--pink); color: white; }

        /* ── MAIN ── */
        .main { margin-left: 220px; padding: 32px; min-height: 100vh; }

        .page-header { margin-bottom: 28px; }
        .page-header h1 { font-size: 1.6rem; font-weight: 700; }
        .page-header p  { color: var(--muted); font-size: 0.85rem; margin-top: 4px; }

        /* ── STATS CARDS ── */
        .stats-grid {
            display: grid; grid-template-columns: repeat(4, 1fr);
            gap: 16px; margin-bottom: 32px;
        }
        .stat-card {
            background: var(--white); border-radius: var(--radius);
            padding: 20px 22px; box-shadow: var(--shadow);
        }
        .stat-label { font-size: 0.75rem; color: var(--muted); font-weight: 500; text-transform: uppercase; letter-spacing: 1px; }
        .stat-value { font-size: 1.8rem; font-weight: 800; margin-top: 6px; }
        .stat-card.pink  .stat-value { color: var(--pink); }
        .stat-card.yellow .stat-value { color: #c89000; }
        .stat-card.dark  .stat-value { color: var(--charcoal); }
        .stat-card.green .stat-value { color: #2e9e6b; }

        /* ── SECTION ── */
        .section { display: none; }
        .section.active { display: block; }

        /* ── CARD ── */
        .card {
            background: var(--white); border-radius: var(--radius);
            box-shadow: var(--shadow); overflow: hidden;
        }
        .card-header {
            padding: 18px 22px; border-bottom: 1px solid var(--border);
            display: flex; justify-content: space-between; align-items: center;
        }
        .card-header h3 { font-size: 1rem; font-weight: 700; }

        /* ── TABLE ── */
        .table-wrap { overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; font-size: 0.85rem; }
        th {
            background: #FAFAFA; padding: 12px 16px;
            text-align: left; font-weight: 600; font-size: 0.78rem;
            text-transform: uppercase; letter-spacing: 0.5px;
            color: var(--muted); border-bottom: 1px solid var(--border);
        }
        td { padding: 12px 16px; border-bottom: 1px solid var(--border); vertical-align: middle; }
        tr:last-child td { border-bottom: none; }
        tr:hover td { background: #FAFAFA; }

        .badge {
            display: inline-block; padding: 3px 10px;
            border-radius: 20px; font-size: 0.72rem; font-weight: 600;
        }
        .badge-confirmed { background: #d4f4e6; color: #1a7a4a; }
        .badge-pending   { background: #fff3cd; color: #856404; }
        .badge-cancelled { background: #fde8e8; color: #b91c1c; }

        /* ── BUTTONS ── */
        .btn {
            padding: 8px 16px; border-radius: 8px; border: none;
            cursor: pointer; font-family: 'Poppins', sans-serif;
            font-size: 0.8rem; font-weight: 600; transition: all 0.2s;
        }
        .btn-pink    { background: var(--pink); color: white; }
        .btn-pink:hover { background: #c03d80; }
        .btn-yellow  { background: var(--yellow); color: var(--charcoal); }
        .btn-yellow:hover { background: #e6b800; }
        .btn-outline { background: transparent; border: 1.5px solid var(--border); color: var(--text); }
        .btn-outline:hover { border-color: var(--pink); color: var(--pink); }
        .btn-danger  { background: #fee2e2; color: #b91c1c; }
        .btn-danger:hover  { background: #b91c1c; color: white; }
        .btn-sm { padding: 5px 12px; font-size: 0.75rem; }

        /* ── MODAL ── */
        .modal-overlay {
            display: none; position: fixed; inset: 0;
            background: rgba(0,0,0,0.45); z-index: 999;
            align-items: center; justify-content: center;
        }
        .modal-overlay.open { display: flex; }
        .modal-box {
            background: var(--white); border-radius: 16px;
            padding: 28px; width: 520px; max-width: 95vw;
            max-height: 90vh; overflow-y: auto;
            box-shadow: 0 20px 60px rgba(0,0,0,0.2);
        }
        .modal-title {
            font-size: 1.2rem; font-weight: 700; margin-bottom: 20px;
            padding-bottom: 14px; border-bottom: 1px solid var(--border);
        }

        /* ── FORM ── */
        .form-group { margin-bottom: 14px; }
        .form-group label { display: block; font-size: 0.8rem; font-weight: 600; color: var(--muted); margin-bottom: 5px; text-transform: uppercase; letter-spacing: 0.5px; }
        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%; padding: 10px 14px;
            border: 1.5px solid var(--border); border-radius: 8px;
            font-family: 'Poppins', sans-serif; font-size: 0.88rem;
            color: var(--text); transition: border 0.2s;
            background: white;
        }
        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none; border-color: var(--pink);
        }
        .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
        .modal-actions { display: flex; gap: 10px; justify-content: flex-end; margin-top: 20px; padding-top: 16px; border-top: 1px solid var(--border); }

        .loading { color: var(--muted); font-style: italic; padding: 20px 16px; }
        .img-thumb { width: 52px; height: 40px; object-fit: cover; border-radius: 6px; }
    </style>
</head>
<body>

<!-- SIDEBAR -->
<aside class="sidebar">
    <div class="sidebar-logo">Tripzy</div>
    <div class="sidebar-sub">Admin Panel</div>

    <a class="nav-link active" onclick="showSection('dashboard', this)">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/></svg>
        Dashboard
    </a>
    <a class="nav-link" onclick="showSection('hotels', this)">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/></svg>
        Hotels
    </a>
    <a class="nav-link" onclick="showSection('rooms', this)">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M2 4v16"/><path d="M22 4v16"/><rect x="2" y="8" width="20" height="8" rx="1"/></svg>
        Rooms
    </a>
    <a class="nav-link" onclick="showSection('bookings', this)">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg>
        Bookings
    </a>
    <a class="nav-link" onclick="showSection('users', this)">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
        Users
    </a>

    <div class="sidebar-bottom">
        <button class="btn-logout" onclick="logout()">Sign Out</button>
    </div>
</aside>

<!-- MAIN -->
<main class="main">

    <!-- DASHBOARD -->
    <div id="sec-dashboard" class="section active">
        <div class="page-header">
            <h1>Dashboard</h1>
            <p>Welcome back, <?= htmlspecialchars($_SESSION['full_name']) ?>!</p>
        </div>
        <div class="stats-grid">
            <div class="stat-card pink">
                <div class="stat-label">Total Hotels</div>
                <div class="stat-value" id="stat-hotels">—</div>
            </div>
            <div class="stat-card dark">
                <div class="stat-label">Total Rooms</div>
                <div class="stat-value" id="stat-rooms">—</div>
            </div>
            <div class="stat-card yellow">
                <div class="stat-label">Total Bookings</div>
                <div class="stat-value" id="stat-bookings">—</div>
            </div>
            <div class="stat-card green">
                <div class="stat-label">Total Users</div>
                <div class="stat-value" id="stat-users">—</div>
            </div>
        </div>

        <div class="card">
            <div class="card-header"><h3>Recent Bookings</h3></div>
            <div class="table-wrap">
                <table>
                    <thead><tr><th>Code</th><th>Guest</th><th>Hotel</th><th>Room</th><th>Total</th><th>Status</th></tr></thead>
                    <tbody id="recent-bookings"><tr><td colspan="6" class="loading">Loading...</td></tr></tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- HOTELS -->
    <div id="sec-hotels" class="section">
        <div class="page-header">
            <h1>Hotels</h1>
            <p>Manage all hotel listings</p>
        </div>
        <div class="card">
            <div class="card-header">
                <h3>All Hotels</h3>
                <button class="btn btn-pink" onclick="openHotelModal()">+ Add Hotel</button>
            </div>
            <div class="table-wrap">
                <table>
                    <thead><tr><th>Photo</th><th>Name</th><th>Location</th><th>Rating</th><th>Actions</th></tr></thead>
                    <tbody id="hotels-table"><tr><td colspan="6" class="loading">Loading...</td></tr></tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- ROOMS -->
    <div id="sec-rooms" class="section">
        <div class="page-header">
            <h1>Rooms</h1>
            <p>Manage all room listings</p>
        </div>
        <div class="card">
            <div class="card-header">
                <h3>All Rooms</h3>
                <button class="btn btn-pink" onclick="openRoomModal()">+ Add Room</button>
            </div>
            <div class="table-wrap">
                <table>
                    <thead><tr><th>Photo</th><th>Hotel</th><th>Room Name</th><th>Price/Night</th><th>Actions</th></tr></thead>
                    <tbody id="rooms-table"><tr><td colspan="5" class="loading">Loading...</td></tr></tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- BOOKINGS -->
    <div id="sec-bookings" class="section">
        <div class="page-header">
            <h1>Bookings</h1>
            <p>All booking transactions</p>
        </div>
        <div class="card">
            <div class="card-header"><h3>All Bookings</h3></div>
            <div class="table-wrap">
                <table>
                    <thead><tr><th>Code</th><th>Guest</th><th>Hotel</th><th>Room</th><th>Check-in</th><th>Check-out</th><th>Total</th><th>Payment</th><th>Status</th></tr></thead>
                    <tbody id="bookings-table"><tr><td colspan="9" class="loading">Loading...</td></tr></tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- USERS -->
    <div id="sec-users" class="section">
        <div class="page-header">
            <h1>Users</h1>
            <p>Registered user accounts</p>
        </div>
        <div class="card">
            <div class="card-header"><h3>All Users</h3></div>
            <div class="table-wrap">
                <table>
                    <thead><tr><th>ID</th><th>Username</th><th>Full Name</th><th>Phone</th><th>Role</th><th>Joined</th></tr></thead>
                    <tbody id="users-table"><tr><td colspan="6" class="loading">Loading...</td></tr></tbody>
                </table>
            </div>
        </div>
    </div>

</main>

<!-- MODAL: Hotel -->
<div id="hotel-modal" class="modal-overlay">
    <div class="modal-box">
        <div class="modal-title" id="hotel-modal-title">Add Hotel</div>
        <input type="hidden" id="hotel-id">
        <div class="form-row">
            <div class="form-group">
                <label>Hotel Name</label>
                <input type="text" id="hotel-name" placeholder="e.g. Shangri-La">
            </div>
            <div class="form-group">
                <label>Location</label>
                <select id="hotel-location">
                    <option value="Surabaya">Surabaya</option>
                    <option value="Jakarta">Jakarta</option>
                    <option value="Bali">Bali</option>
                    <option value="Japan">Japan</option>
                </select>
            </div>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label>Rating</label>
                <input type="number" id="hotel-rating" min="1" max="5" step="0.1" placeholder="4.8">
            </div>
        </div>
        <div class="form-group">
            <label>Thumbnail Image</label>
            <input type="file" id="hotel-img-file" accept="image/*">
            <input type="hidden" id="hotel-thumb">
            <img id="hotel-img-preview" src="" style="margin-top:8px; width:100%; height:120px; object-fit:cover; border-radius:8px; display:none;">
        </div>
        <div class="form-group">
            <label>Facilities (comma separated)</label>
            <input type="text" id="hotel-facilities" placeholder="WiFi, Pool, Spa, Gym">
        </div>
        <div class="modal-actions">
            <button class="btn btn-outline" onclick="closeModal('hotel-modal')">Cancel</button>
            <button class="btn btn-pink" onclick="saveHotel()">Save Hotel</button>
        </div>
    </div>
</div>

<!-- MODAL: Room -->
<div id="room-modal" class="modal-overlay">
    <div class="modal-box">
        <div class="modal-title" id="room-modal-title">Add Room</div>
        <input type="hidden" id="room-id">
        <div class="form-row">
            <div class="form-group">
                <label>Hotel</label>
                <select id="room-hotel-id"></select>
            </div>
            <div class="form-group">
                <label>Room Name</label>
                <input type="text" id="room-name" placeholder="e.g. Deluxe Suite">
            </div>
        </div>
        <div class="form-group">
            <label>Price per Night (Rp)</label>
            <input type="number" id="room-price" placeholder="2500000">
        </div>
        <div class="form-group">
            <label>Room Image</label>
            <input type="file" id="room-img-file" accept="image/*">
            <input type="hidden" id="room-img">
            <img id="room-img-preview" src="" style="margin-top:8px; width:100%; height:120px; object-fit:cover; border-radius:8px; display:none;">
        </div>
        <div class="form-group">
            <label>Facilities (comma separated)</label>
            <input type="text" id="room-facilities" placeholder="WiFi, TV, King Bed">
        </div>
        <div class="modal-actions">
            <button class="btn btn-outline" onclick="closeModal('room-modal')">Cancel</button>
            <button class="btn btn-pink" onclick="saveRoom()">Save Room</button>
        </div>
    </div>
</div>

<!-- MODAL: Confirm Delete -->
<div id="delete-modal" class="modal-overlay">
    <div class="modal-box" style="max-width:380px; text-align:center;">
        <div style="font-size:2.5rem; margin-bottom:12px;">🗑️</div>
        <div class="modal-title" style="justify-content:center;">Delete Confirmation</div>
        <p style="color:var(--muted); font-size:0.88rem; margin-bottom:20px;" id="delete-msg">Are you sure?</p>
        <div style="display:flex; gap:10px; justify-content:center;">
            <button class="btn btn-outline" onclick="closeModal('delete-modal')">Cancel</button>
            <button class="btn btn-danger" onclick="confirmDelete()">Delete</button>
        </div>
    </div>
</div>

<script>
const API = {
    hotel:   'api/hotel.php',
    room:    'api/room.php',
    booking: 'api/booking.php',
    user:    'api/user.php',
    upload:  'api/upload.php',
};

let deleteTarget = null;

// ── NAVIGATION ──
function showSection(name, el) {
    document.querySelectorAll('.section').forEach(s => s.classList.remove('active'));
    document.querySelectorAll('.nav-link').forEach(a => a.classList.remove('active'));
    document.getElementById('sec-' + name).classList.add('active');
    if (el) el.classList.add('active');

    if (name === 'dashboard') loadDashboard();
    if (name === 'hotels')    loadHotels();
    if (name === 'rooms')     loadRooms();
    if (name === 'bookings')  loadAllBookings();
    if (name === 'users')     loadUsers();
}

function closeModal(id) {
    document.getElementById(id).classList.remove('open');
}

// ── LOGOUT ──
async function logout() {
    await fetch('api/auth.php?action=logout', { method: 'POST' });
    window.location.href = 'index.html';
}

// ── DASHBOARD ──
async function loadDashboard() {
    const [hotels, rooms, bookings, users] = await Promise.all([
        fetch(API.hotel + '?loc=all').then(r => r.json()),
        fetch(API.room).then(r => r.json()),
        fetch(API.booking + '?all=1').then(r => r.json()),
        fetch(API.user).then(r => r.json()),
    ]);
    document.getElementById('stat-hotels').textContent   = hotels.length   || 0;
    document.getElementById('stat-rooms').textContent    = rooms.length    || 0;
    document.getElementById('stat-bookings').textContent = bookings.length || 0;
    document.getElementById('stat-users').textContent    = users.length    || 0;

    const tbody = document.getElementById('recent-bookings');
    const recent = bookings.slice(0, 8);
    tbody.innerHTML = recent.length ? recent.map(b => `
        <tr>
            <td><strong>${b.booking_code}</strong></td>
            <td>${b.guest_name}</td>
            <td>${b.hotel_name}</td>
            <td>${b.room_name}</td>
            <td>Rp ${Number(b.total_price).toLocaleString()}</td>
            <td><span class="badge badge-${b.status}">${b.status}</span></td>
        </tr>`).join('') : '<tr><td colspan="6" class="loading">No bookings yet.</td></tr>';
}

// ── HOTELS ──
async function loadHotels() {
    const hotels = await fetch(API.hotel + '?loc=all').then(r => r.json());
    const tbody  = document.getElementById('hotels-table');
    tbody.innerHTML = hotels.map(h => `
        <tr>
            <td><img src="${h.thumb_url}" class="img-thumb" onerror="this.style.display='none'"></td>
            <td><strong>${h.name}</strong></td>
            <td>${h.location}</td>
            <td>${h.rating}</td>
            <td style="display:flex; gap:6px;">
                <button class="btn btn-outline btn-sm" onclick="editHotel(${h.id})">Edit</button>
                <button class="btn btn-danger btn-sm" onclick="askDelete('hotel', ${h.id}, '${h.name}')">Delete</button>
            </td>
        </tr>`).join('');
}

function openHotelModal(data = null) {
    document.getElementById('hotel-id').value        = data ? data.id : '';
    document.getElementById('hotel-name').value      = data ? data.name : '';
    document.getElementById('hotel-location').value  = data ? data.location : 'Surabaya';
    document.getElementById('hotel-rating').value    = data ? data.rating : '';
    document.getElementById('hotel-thumb').value     = data ? data.thumb_url : '';
    document.getElementById('hotel-facilities').value= data ? (Array.isArray(data.facilities) ? data.facilities.join(', ') : data.facilities) : '';
    document.getElementById('hotel-modal-title').textContent = data ? 'Edit Hotel' : 'Add Hotel';

    const preview = document.getElementById('hotel-img-preview');
    if (data && data.thumb_url) { preview.src = data.thumb_url; preview.style.display = 'block'; }
    else { preview.style.display = 'none'; }

    document.getElementById('hotel-modal').classList.add('open');
}

async function editHotel(id) {
    const res   = await fetch(API.hotel + '?id=' + id);
    const hotel = await res.json();
    openHotelModal(hotel);
}

async function saveHotel() {
    const id = document.getElementById('hotel-id').value;

    // Upload gambar dulu kalau ada file baru
    let thumbUrl = document.getElementById('hotel-thumb').value;
    const file   = document.getElementById('hotel-img-file').files[0];
    if (file) {
        const fd = new FormData();
        fd.append('image', file);
        fd.append('folder', 'hotels');
        const upRes  = await fetch(API.upload, { method: 'POST', body: fd });
        const upData = await upRes.json();
        if (upData.success) thumbUrl = upData.path;
    }

    const facStr = document.getElementById('hotel-facilities').value;
    const fac    = facStr.split(',').map(f => f.trim()).filter(Boolean);

    const payload = {
        name:         document.getElementById('hotel-name').value,
        location:     document.getElementById('hotel-location').value,
        rating:       parseFloat(document.getElementById('hotel-rating').value),
        thumb_url:    thumbUrl,
        facilities:   JSON.stringify(fac),
    };

    const url    = id ? API.hotel + '?id=' + id : API.hotel;
    const method = id ? 'PUT' : 'POST';
    const res  = await fetch(url, { method, headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(payload) });
    const text = await res.text();
    console.log('response:', text);
    const data = JSON.parse(text);

    if (data.success || data.id) {
        closeModal('hotel-modal');
        loadHotels();
    } else {
        alert(data.message || 'Gagal menyimpan hotel');
    }
}

// ── ROOMS ──
async function loadRooms() {
    const [rooms, hotels] = await Promise.all([
        fetch(API.room).then(r => r.json()),
        fetch(API.hotel + '?loc=all').then(r => r.json()),
    ]);
    const hotelMap = {};
    hotels.forEach(h => hotelMap[h.id] = h.name);

    const tbody = document.getElementById('rooms-table');
    tbody.innerHTML = rooms.map(r => `
        <tr>
            <td><img src="${r.img}" class="img-thumb" onerror="this.style.display='none'"></td>
            <td>${hotelMap[r.hotel_id] || '-'}</td>
            <td><strong>${r.name}</strong></td>
            <td>Rp ${Number(r.price).toLocaleString()}</td>
            <td style="display:flex; gap:6px;">
                <button class="btn btn-outline btn-sm" onclick="editRoom(${r.id})">Edit</button>
                <button class="btn btn-danger btn-sm" onclick="askDelete('room', ${r.id}, '${r.name}')">Delete</button>
            </td>
        </tr>`).join('');
}

async function openRoomModal(data = null) {
    const hotels = await fetch(API.hotel + '?loc=all').then(r => r.json());
    const sel    = document.getElementById('room-hotel-id');
    sel.innerHTML = hotels.map(h => `<option value="${h.id}" ${data && data.hotel_id == h.id ? 'selected' : ''}>${h.name}</option>`).join('');

    document.getElementById('room-id').value        = data ? data.id : '';
    document.getElementById('room-name').value      = data ? data.name : '';
    document.getElementById('room-price').value     = data ? data.price : '';
    document.getElementById('room-img').value       = data ? data.img : '';
    document.getElementById('room-facilities').value= data ? (Array.isArray(data.facilities) ? data.facilities.join(', ') : data.facilities) : '';
    document.getElementById('room-modal-title').textContent = data ? 'Edit Room' : 'Add Room';

    const preview = document.getElementById('room-img-preview');
    if (data && data.img) { preview.src = data.img; preview.style.display = 'block'; }
    else { preview.style.display = 'none'; }

    document.getElementById('room-modal').classList.add('open');
}

async function editRoom(id) {
    const res  = await fetch(API.room + '?id=' + id);
    const room = await res.json();
    openRoomModal(room);
}

async function saveRoom() {
    const id = document.getElementById('room-id').value;

    let imgPath = document.getElementById('room-img').value;
    const file  = document.getElementById('room-img-file').files[0];
    if (file) {
        const fd = new FormData();
        fd.append('image', file);
        fd.append('folder', 'rooms');
        const upRes  = await fetch(API.upload, { method: 'POST', body: fd });
        const upData = await upRes.json();
        if (upData.success) imgPath = upData.path;
    }

    const facStr = document.getElementById('room-facilities').value;
    const fac    = facStr.split(',').map(f => f.trim()).filter(Boolean);

    const payload = {
        hotel_id:   parseInt(document.getElementById('room-hotel-id').value),
        name:       document.getElementById('room-name').value,
        price:      parseInt(document.getElementById('room-price').value),
        img:        imgPath,
        facilities: JSON.stringify(fac),
    };

    const url    = id ? API.room + '?id=' + id : API.room;
    const method = id ? 'PUT' : 'POST';
    const res    = await fetch(url, { method, headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(payload) });
    const data   = await res.json();

    if (data.success || data.id) {
        closeModal('room-modal');
        loadRooms();
    } else {
        alert(data.message || 'Gagal menyimpan room');
    }
}

// ── BOOKINGS ──
async function loadAllBookings() {
    const bookings = await fetch(API.booking + '?all=1').then(r => r.json());
    const tbody    = document.getElementById('bookings-table');
    tbody.innerHTML = bookings.length ? bookings.map(b => `
        <tr>
            <td><strong>${b.booking_code}</strong></td>
            <td>${b.guest_name}</td>
            <td>${b.hotel_name}</td>
            <td>${b.room_name}</td>
            <td>${b.checkin || '-'}</td>
            <td>${b.checkout || '-'}</td>
            <td>Rp ${Number(b.total_price).toLocaleString()}</td>
            <td>${b.payment_method}</td>
            <td><span class="badge badge-${b.status}">${b.status}</span></td>
        </tr>`).join('') : '<tr><td colspan="9" class="loading">No bookings yet.</td></tr>';
}

// ── USERS ──
async function loadUsers() {
    const users = await fetch(API.user).then(r => r.json());
    const tbody = document.getElementById('users-table');
    tbody.innerHTML = users.length ? users.map(u => `
        <tr>
            <td>${u.id}</td>
            <td>${u.username}</td>
            <td>${u.full_name}</td>
            <td>${u.phone}</td>
            <td><span class="badge ${u.role === 'admin' ? 'badge-confirmed' : 'badge-pending'}">${u.role}</span></td>
            <td>${u.created_at || '-'}</td>
        </tr>`).join('') : '<tr><td colspan="6" class="loading">No users.</td></tr>';
}

// ── DELETE ──
function askDelete(type, id, name) {
    deleteTarget = { type, id };
    document.getElementById('delete-msg').textContent = `Delete "${name}"? This cannot be undone.`;
    document.getElementById('delete-modal').classList.add('open');
}

async function confirmDelete() {
    if (!deleteTarget) return;
    const url = deleteTarget.type === 'hotel' ? API.hotel + '?id=' + deleteTarget.id : API.room + '?id=' + deleteTarget.id;
    await fetch(url, { method: 'DELETE' });
    closeModal('delete-modal');
    deleteTarget.type === 'hotel' ? loadHotels() : loadRooms();
    deleteTarget = null;
}

// ── IMAGE PREVIEW ──
document.getElementById('hotel-img-file').addEventListener('change', function() {
    if (this.files[0]) {
        const preview = document.getElementById('hotel-img-preview');
        preview.src = URL.createObjectURL(this.files[0]);
        preview.style.display = 'block';
    }
});
document.getElementById('room-img-file').addEventListener('change', function() {
    if (this.files[0]) {
        const preview = document.getElementById('room-img-preview');
        preview.src = URL.createObjectURL(this.files[0]);
        preview.style.display = 'block';
    }
});

// Init
loadDashboard();
</script>
</body>
</html>