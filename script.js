function populateDropdowns() {
    const ids = ['count-room', 'count-adult', 'count-child'];
    ids.forEach(id => {
        const el = document.getElementById(id);
        for(let i=0; i<=10; i++) {
            if (i === 0 && id !== 'count-child') continue;
            let opt = document.createElement('option');
            opt.value = i;
            opt.innerHTML = i + (id === 'count-room' ? ' Room' : id === 'count-adult' ? ' Adult' : ' Child');
            el.appendChild(opt);
        }
        el.value = (id === 'count-child' ? 0 : 1);
    });
}
const DEV_MODE = false; 

window.onload = function() {
    populateDropdowns();
    if (DEV_MODE) {
        currentUserObj = { name: "Dev User", phone: "08123456789", ktp: "0000000000", pass: "dev123" };
        loginSuccessAction(currentUserObj);
    }
};

// --- EXPANDED DATABASE ---
const locations = ["Surabaya", "Jakarta", "Bali", "Japan"];
const database = [];

const hotelNames = {
    "Surabaya": ["Vasa Hotel", "Hotel Majapahit", "Shangri-La", "Westin Surabaya", "JW Marriott", "Wyndham Tower", "Bumi Surabaya", "Oakwood Suites", "DoubleTree", "Four Points by Sheraton Surabaya, Tunjungan Plaza"],
    "Jakarta": ["The Ritz-Carlton", "Hotel Indonesia Kempinski", "Raffles Jakarta", "The Langham", "Park Hyatt", "Grand Hyatt", "Fairmont", "The Dharmawangsa", "Mandarin Oriental", "Alila SCBD"],
    "Bali": ["The Apurva Kempinski", "W Bali Seminyak", "Ayana Resort", "Alila Villas Uluwatu", "Potato Head Studios", "Four Seasons Jimbaran", "St. Regis Bali", "The Mulia", "Six Senses Uluwatu", "Maya Ubud"],
    "Japan": ["Park Hyatt Tokyo", "Aman Tokyo", "Hoshinoya Kyoto", "The Ritz-Carlton Osaka", "The Peninsula Tokyo", "Mandarin Oriental Tokyo", "Suiran Kyoto", "Conrad Tokyo", "Four Seasons Otemachi", "Ritz-Carlton Kyoto"]
};

const hotelAddresses = {
    "Surabaya": {
        "Vasa Hotel": "Jl. HR Muhammad No. 31, Sukomanunggal",
        "Hotel Majapahit": "Jl. Tunjungan No. 65, Genteng",
        "Shangri-La": "Jl. Mayjen Sungkono No. 120, Sawahan",
        "Westin Surabaya": "Pakuwon Mall, Jl. Puncak Indah Lontar",
        "JW Marriott": "Jl. Embong Malang No. 85, Tegalsari",
        "Wyndham Tower": "Jl. Basuki Rahmat No. 67, Genteng",
        "Bumi Surabaya": "Jl. Jenderal Basuki Rakhmat No. 106",
        "Oakwood Suites": "Jl. Raya Kertajaya Indah No. 79, Manyar",
        "DoubleTree": "Jl. Tunjungan No. 12, Genteng",
        "Four Points by Sheraton Surabaya, Tunjungan Plaza": "Jl. Embong Malang No. 25, Tegalsari"
    },
    "Jakarta": {
        "The Ritz-Carlton": "Mega Kuningan Kav. E1.1, Jakarta Selatan",
        "Hotel Indonesia Kempinski": "Jl. MH Thamrin No. 1, Menteng",
        "Raffles Jakarta": "Ciputra World 1, Jl. Prof. Dr. Satrio",
        "The Langham": "District 8, SCBD Kav. 52-53, Jakarta Selatan",
        "Park Hyatt": "Jl. Kebon Sirih No. 17-19, Menteng",
        "Grand Hyatt": "Jl. M.H. Thamrin Kav. 28-30, Gondangdia",
        "Fairmont": "Jl. Asia Afrika No. 8, Gelora Bung Karno",
        "The Dharmawangsa": "Jl. Brawijaya Raya No. 26, Kebayoran Baru",
        "Mandarin Oriental": "Jl. MH Thamrin, PO Box 3392",
        "Alila SCBD": "SCBD Lot 11, Jl. Jenderal Sudirman"
    },
    "Bali": {
        "The Apurva Kempinski": "Jl. Raya Nusa Dua Selatan, Sawangan",
        "W Bali Seminyak": "Jl. Petitenget, Kerobokan, Seminyak",
        "Ayana Resort": "Jl. Karang Mas Sejahtera, Jimbaran",
        "Alila Villas Uluwatu": "Jl. Belimbing Sari, Tambiyak, Pecatu",
        "Potato Head Studios": "Jl. Petitenget No. 51B, Seminyak",
        "Four Seasons Jimbaran": "Jimbaran, Kuta Selatan",
        "St. Regis Bali": "Kawasan Pariwisata Nusa Dua Lot S6",
        "The Mulia": "Jl. Raya Nusa Dua Selatan, Kuta Selatan",
        "Six Senses Uluwatu": "Jl. Goa Lempeh, Uluwatu, Pecatu",
        "Maya Ubud": "Jl. Raya Gunung Sari Peliatan, Ubud"
    },
    "Japan": {
        "Park Hyatt Tokyo": "3-7-1-2 Nishi Shinjuku, Shinjuku-ku, Tokyo",
        "Aman Tokyo": "The Otemachi Tower, 1-5-6 Otemachi, Chiyoda-ku",
        "Hoshinoya Kyoto": "11-2 Arashiyama Genrokuzan-cho, Nishikyo-ku",
        "The Ritz-Carlton Osaka": "2-5-25 Umeda, Kita-ku, Osaka",
        "The Peninsula Tokyo": "1-8-1 Yurakucho, Chiyoda-ku, Tokyo",
        "Mandarin Oriental Tokyo": "2-1-1 Nihonbashi Muromachi, Chuo-ku",
        "Suiran Kyoto": "12 Susukinobaba-cho, Saga-Tenryuji, Ukyo-ku",
        "Conrad Tokyo": "1-9-1 Higashi-Shinbashi, Minato-ku",
        "Four Seasons Otemachi": "1-2-1 Otemachi, Chiyoda-ku, Tokyo",
        "Ritz-Carlton Kyoto": "Kamogawa Nijo-ohashi Hotori, Nakagyo-ku"
    }
};

const descriptions = [
    "Experience the pinnacle of luxury with world-class service and breathtaking views.",
    "A perfect blend of heritage charm and modern elegance in the heart of the city.",
    "Indulge in an oasis of tranquility featuring award-winning dining.",
    "An architectural masterpiece offering a refined stay with state-of-the-art amenities."
];

const generalFacilities = ["Infinity Pool", "Sky Lounge", "Luxury Spa", "24/7 Butler", "Fine Dining"];

const reviewTemplates = [
    { user: "Syahri Banun", text: "Absolutely stunning! The service was impeccable." },
    { user: "Muhammad Aditya Nugraha", text: "Keren banget sumpah, pelayanannya gacor parah" },
    { user: "Chelsea", text: "Ada gym btw, jadi bicep gue tambah gede" },
    { user: "Muhammad Dayyan Ghazanfar Latief", text: "A truly exceptional experience." },
    { user: "Sitti Aminah", text: "Luxury banget, emang recommended sih jujur, cuma harganya emang aga mahal" }
];

const roomTypes = [
    { name: "Deluxe Modern", price: 3950000, img: "img/DeluxeRoom.jpg", fac: ["WiFi", "TV", "King Bed"] },
    { name: "Executive Suite", price: 2500000, img: "img/ExecutiveSuiteRoom.jpg", fac: ["Lounge Access", "Bathtub", "Mini Bar"] },
    { name: "Presidential Sky", price: 5800000, img: "img/PresidentialSuite.jpg", fac: ["Private Pool", "Butler", "Panoramic View"] }
];

const locationPhotos = {
    "Surabaya": ["img/VasaHotelSurabaya.jpg", "img/HotelMajapahitSurabaya.webp", "img/ShangriLaSurabaya.jpg", "img/WestinSurabaya.webp", "img/JWMarriottSurabaya.webp", "img/WyndhamSurabaya.webp", "img/BumiSurabaya.webp", "img/OakwoodSurabaya.webp", "img/DoubleTreeSurabaya.webp", "img/SheratonSurabaya.webp"],
    "Jakarta": ["img/RitzCaltonJakarta.jpg", "img/KempinskiJakarta.jpg", "img/RafflesJakarta.jpg", "img/TheLanghamJakarta.jpg", "img/ParkHyattJakarta.jpg", "img/GrandHyattJakarta.webp", "img/FairmontJakarta.webp", "img/TheDarmawangsaJakarta.webp", "img/MandarinOrientalJakarta.webp", "img/AlilaSCBD.webp"],
    "Bali": ["img/TheApurvaKempinskiBali.webp", "img/WBali.webp", "img/AyanaBali.webp", "img/AlilasVilaUluwatuBali.webp", "img/HeadPotatoBali.webp", "img/FourSeasonJimbaranBali.jpg", "img/StRegisBali.webp", "img/TheMuliaBali.webp", "img/SixSensesBali.jpg", "img/MayaUbudBali.jpg"],
    "Japan": ["img/ParkHyattJapan.jpg", "img/AmanTokyoJapan.jpg", "img/HoshinoyaKyoto.jpg", "img/TheRitzCarltonOsaka.jpg", "img/ThePeninsulaJapan.jpg", "img/MandarinOrientalTokyo.jpg", "img/SuiranKyotoJapan.webp", "img/ConradTokyoJapan.jpg", "img/FourSeasonOtemachi.webp", "img/RitzCarltonTokyo.jpg"]
};

let idCounter = 1;
locations.forEach(loc => {
    hotelNames[loc].forEach((name, index) => {
        const rating = (4.5 + Math.random() * 0.5).toFixed(1);
        database.push({
            id: idCounter++,
            name: name,
            loc: loc,
            rate: rating,
            reviews: Math.floor(Math.random() * 5000) + 200,
            description: descriptions[Math.floor(Math.random() * descriptions.length)],
            facilities: [...generalFacilities].sort(() => 0.5 - Math.random()).slice(0, 5),
            topReviews: reviewTemplates,
            thumb: locationPhotos[loc][index],
            isLuxury: index < 3,
            rooms: roomTypes.map(r => ({
                ...r,
                price: r.price + (index * 100000) + (loc === "Japan" ? 2000000 : 0)
            }))
        });
    });
});
 
let bookings = [];
let users = [];
let currentUserObj = null;
let pendingBooking = null;

// --- AUTH LOGIC ---
function toggleAuth(isLogin) {
    document.getElementById('login-section').classList.toggle('hidden', !isLogin);
    document.getElementById('signup-section').classList.toggle('hidden', isLogin);
}

function signupAction() {
    const user = document.getElementById('signup-user').value;
    const pass = document.getElementById('signup-pass').value;
    const name = document.getElementById('signup-fullname').value;
    const ktp = document.getElementById('signup-ktp').value;
    const phone = document.getElementById('signup-phone').value;
    if (user && pass && name && ktp && phone) {
        users.push({ user, pass, name, ktp, phone });
        alert("Account created! Please sign in.");
        toggleAuth(true);
    } else { alert("Please fill all fields!"); }
}

function loginAction() {
    const userIn = document.getElementById('login-id').value;
    const passIn = document.getElementById('login-pass').value;
    const foundUser = users.find(u => u.user === userIn && u.pass === passIn) || (userIn === "admin" && passIn === "admin" ? {name: "Admin User", phone: "08123", ktp: "123", pass: "admin"} : null);

    if (foundUser) {
        loginSuccessAction(foundUser);
    } else { alert("Invalid credentials."); }
}

function loginSuccessAction(user) {
    currentUserObj = user;
    document.getElementById('auth-page').classList.add('hidden');
    document.getElementById('main-app').classList.remove('hidden');
    // navbar atas tidak dipakai, pakai floating nav di bawah
    document.getElementById('user-info').innerText = `Hi, ${currentUserObj.name} ✦`;
    
    // Fill Account Data
    document.getElementById('acc-name').innerText = currentUserObj.name;
    document.getElementById('acc-phone').innerText = currentUserObj.phone;
    document.getElementById('acc-ktp').innerText = currentUserObj.ktp;
    document.getElementById('acc-pass').value = "********"; 
    
    showPage('home-page');
    renderRecommendations();
}

// --- NAVIGATION LOGIC ---
function showPage(pageId, navEl = null) {
    // Hide all pages
    ['home-page', 'bookings-page', 'account-page', 'results-page'].forEach(id => {
        document.getElementById(id).classList.add('hidden');
    });
    
    // Show target page
    document.getElementById(pageId).classList.remove('hidden');
    
    // Update Nav UI
    if (navEl) {
        document.querySelectorAll('.nav-item').forEach(el => el.classList.remove('active'));
        navEl.classList.add('active');
    }
    window.scrollTo(0, 0);
}

// --- ACCOUNT LOGIC ---
function unlockPassword() {
    const phoneConfirm = prompt("To view password, please enter your Phone Number:");
    if (phoneConfirm === currentUserObj.phone) {
        document.getElementById('acc-pass').value = currentUserObj.pass;
        document.getElementById('acc-pass').type = "text";
        alert("Password Unlocked.");
    } else {
        alert("Phone number does not match.");
    }
}

// --- CORE APP LOGIC ---
function renderRecommendations() {
    const container = document.getElementById('recommendations');
    const luxuryOnes = database.filter(h => h.isLuxury).sort(() => 0.5 - Math.random()).slice(0, 4);
    container.innerHTML = luxuryOnes.map(h => `
        <div class="recom-card" onclick="openRoomModal(${h.id})">
            <div style="overflow:hidden;">
                <img src="${h.thumb}" class="recom-img">
            </div>
            <div class="recom-info">
                <small style="color:var(--gold-dark); font-weight:600; font-size:0.68rem; letter-spacing:2px; text-transform:uppercase;">${h.loc}</small>
                <h4 style="margin: 6px 0 8px; font-size:1rem; font-weight:500;">${h.name}</h4>
                <p style="color:var(--charcoal); font-weight:600; font-size:0.9rem;">Rp ${h.rooms[0].price.toLocaleString()} <span style="color:var(--text-muted); font-weight:400; font-size:0.75rem;">/ night</span></p>
            </div>
        </div>
    `).join('');
}

function performSearch() {
    const loc = document.getElementById('search-loc').value;
    const filtered = loc === "all" ? database : database.filter(h => h.loc === loc);
    renderResults(filtered);
    showPage('results-page');
}

function renderResults(data) {
    const list = document.getElementById('hotel-list');
    if (data.length === 0) { list.innerHTML = "<h3>No hotels found.</h3>"; return; }

    list.innerHTML = data.map(h => {
        // Logika variabel ditaruh di sini (sebelum return template)
        const address = hotelAddresses[h.loc][h.name] || "Address not available";
        
        return `
        <div class="hotel-card">
            <img src="${h.thumb}" class="hotel-img-large">
            <div class="hotel-details">
                <div>
                    ${h.isLuxury ? '<span class="badge-luxury"> Luxury Collection</span>' : ''}
                    <div style="display:flex; justify-content:space-between; align-items: flex-start;">
                        <div>
                            <h2 style="font-family:'Poppins',sans-serif; font-size: 1.7rem; font-weight:700;">${h.name}</h2>
                            <p class="hotel-address" style="font-size: 0.85rem; color: var(--pink); margin-bottom: 5px;">
                                <svg viewBox="0 0 24 24" width="14" height="14" stroke="currentColor" stroke-width="2" fill="none" style="vertical-align: middle; margin-right: 4px;"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10" r="3"></circle></svg>
                                ${address}
                            </p>
                            <p style="color:var(--text-muted); font-size:0.82rem; letter-spacing:1px; text-transform:uppercase;">📍 ${h.loc}</p>
                        </div>
                        <div style="text-align:right;">
                            <div style="color:var(--gold-dark); font-weight:600;">⭐ ${h.rate}</div>
                            <small>${h.reviews} Reviews</small>
                        </div>
                    </div>
                </div>
                <div style="display:flex; justify-content:space-between; align-items:center; background:white; padding: 18px; border-radius: 12px; margin-top:15px">
                    <div>
                        <small style="display:block; margin-bottom:4px;">Best price from</small>
                        <h3 style="color:var(--charcoal);">Rp ${h.rooms[0].price.toLocaleString()}</h3>
                    </div>
                    <button class="btn-gradient" onclick="openRoomModal(${h.id})">Select Room</button>
                </div>
            </div>
        </div>`;
    }).join('');
}

function openRoomModal(hotelId) {
    const hotel = database.find(h => h.id === hotelId);
    document.getElementById('modal-hotel-name').innerText = hotel.name;
    document.getElementById('modal-hotel-loc').innerText = `Exclusive Stays in ${hotel.loc}`;
    
    // INI TAMBAHANNYA: Mengisi alamat hotel ke dalam modal
    document.getElementById('modal-hotel-address').innerText = hotelAddresses[hotel.loc][hotel.name] || "Address not available";
    
    document.getElementById('modal-hotel-description').innerText = hotel.description;
    
    document.getElementById('modal-hotel-facilities').innerHTML = hotel.facilities.map(f => `<span class="facility-tag">✦ ${f}</span>`).join('');
    document.getElementById('review-stats').innerText = `Rated ${hotel.rate} based on ${hotel.reviews} guests`;
    document.getElementById('modal-hotel-reviews').innerHTML = hotel.topReviews.slice(0, 5).map(r => `
        <div style="border-bottom: 1px solid #eee; padding-bottom: 10px;">
            <strong>${r.user}</strong>
            <p style="font-size: 0.85rem; margin-top: 5px; font-style: italic;">"${r.text}"</p>
        </div>
    `).join('');

    const roomGrid = document.getElementById('room-grid');
    roomGrid.innerHTML = hotel.rooms.map(room => `
        <div class="room-card">
            <img src="${room.img}" style="width: 100%; height: 180px; object-fit: cover; border-radius: 12px; margin-bottom: 15px;">
            <h3 style="font-size:1.2rem; font-weight:700;">${room.name}</h3>
            <div style="margin: 10px 0;">
                ${room.fac.map(f => `<small style="background:#f0f0f0; padding:2px 8px; border-radius:4px; margin-right:5px;">${f}</small>`).join('')}
            </div>
            <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 15px;">
                <h4 style="color:var(--charcoal);">Rp ${room.price.toLocaleString()}</h4>
                <button class="btn-gradient" onclick="confirmBooking('${hotel.name}', '${room.name}', ${room.price})">Reserve</button>
            </div>
        </div>`).join('');
    document.getElementById('room-modal').style.display = 'flex';
}

function confirmBooking(hName, rType, price) {
    const r = document.getElementById('count-room').value;
    const a = document.getElementById('count-adult').value;
    const c = document.getElementById('count-child').value;
    pendingBooking = { hName, rType, price, config: `${r} Room, ${a} Adult, ${c} Child` };
    document.getElementById('book-for').value = "self";
    toggleGuestFields();
    closeModal('room-modal');
    document.getElementById('confirm-form-modal').style.display = 'flex';
}

function toggleGuestFields() {
    const type = document.getElementById('book-for').value;
    const extra = document.getElementById('extra-guest-fields');
    const nameInput = document.getElementById('guest-name');
    const phoneInput = document.getElementById('guest-phone');

    if (type === 'self' && currentUserObj) {
        nameInput.value = currentUserObj.name;
        phoneInput.value = currentUserObj.phone;
        extra.classList.add('hidden');
        nameInput.disabled = true; phoneInput.disabled = true;
    } else {
        nameInput.value = ""; phoneInput.value = "";
        extra.classList.remove('hidden');
        nameInput.disabled = false; phoneInput.disabled = false;
    }
}

function proceedToPayment() {
    const name = document.getElementById('guest-name').value;
    const phone = document.getElementById('guest-phone').value;
    if (!name || !phone) { alert("Complete details!"); return; }

    pendingBooking.guestName = name;
    pendingBooking.guestPhone = phone;
    pendingBooking.guestKTP = (document.getElementById('book-for').value === 'self') ? currentUserObj.ktp : document.getElementById('guest-id-num').value;

    document.getElementById('payment-summary').innerHTML = `
        <p><strong>${pendingBooking.hName}</strong></p>
        <p>${pendingBooking.rType} (${pendingBooking.config})</p>
        <hr style="margin:10px 0; opacity:0.2">
        <p>Total: <strong>Rp ${pendingBooking.price.toLocaleString()}</strong></p>
    `;
    closeModal('confirm-form-modal');
    document.getElementById('payment-modal').style.display = 'flex';
}

function processFinalPayment(method) {
    bookings.unshift({ ...pendingBooking, date: new Date().toLocaleDateString(), payment: method });
    alert("Payment Securely Processed!");
    pendingBooking = null;
    closeModal('payment-modal');
    updateHistory();
    showPage('bookings-page', document.querySelector('.nav-item:nth-child(2)'));
}

function updateHistory() {
    const container = document.getElementById('booking-history');
    if (bookings.length === 0) {
        container.innerHTML = `<p style="color: rgba(255,255,255,0.7); font-style: italic;">No bookings yet.</p>`;
        return;
    }
    container.innerHTML = bookings.map((b, idx) => `
        <div class="history-card" onclick="showReceipt(${idx})" style="background:white; padding:20px; border-radius:15px; margin-bottom:15px; cursor:pointer;">
            <div style="display:flex; justify-content:space-between; align-items:center;">
                <h4 style="color:var(--charcoal);">${b.hName}</h4>
                <strong>Rp ${b.price.toLocaleString()}</strong>
            </div>
            <p style="font-size:0.8rem; color:#666; margin-top:5px;">${b.rType} • ${b.date}</p>
        </div>`).join('');
}

function showReceipt(idx) {
    const b = bookings[idx];
    document.getElementById('receipt-details').innerHTML = `
        <div style="background:#f9f9f9; padding: 20px; border-radius: 12px;">
            <p><strong>Booking ID:</strong> #TRPZ-${1000 + idx}</p>
            <p><strong>Hotel:</strong> ${b.hName}</p>
            <p><strong>Guest:</strong> ${b.guestName}</p>
            <p><strong>Payment:</strong> ${b.payment}</p>
            <p style="margin-top:10px; font-size:1.2rem; color:var(--gold-dark);">Total: Rp ${b.price.toLocaleString()}</p>
        </div>`;
    document.getElementById('receipt-modal').style.display = 'flex';
}

function closeModal(id) { document.getElementById(id).style.display = 'none'; }