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
        currentUserObj = { name: "Dev User", phone: "08123456789", ktp: "0000000000" };
        document.getElementById('auth-page').classList.add('hidden');
        document.getElementById('main-app').classList.remove('hidden');
        document.getElementById('navbar').classList.remove('hidden');
        document.getElementById('user-info').innerText = `Hi, Dev User ✦`;
        renderRecommendations();
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

// --- TAMBAHAN DATA UNTUK DESKRIPSI, FASILITAS, & REVIEWS ---
const descriptions = [
    "Experience the pinnacle of luxury with world-class service and breathtaking views. Each corner is designed for your ultimate comfort.",
    "A perfect blend of heritage charm and modern elegance. This property offers a unique sanctuary in the heart of the city.",
    "Indulge in an oasis of tranquility featuring award-winning dining and unparalleled hospitality that makes you feel at home.",
    "An architectural masterpiece offering a refined stay with state-of-the-art amenities and a sophisticated atmosphere."
];

const generalFacilities = ["Infinity Pool", "Sky Lounge", "Luxury Spa", "24/7 Butler", "Fine Dining", "Fitness Center", "Valet Parking"];

const reviewTemplates = [
    { user: "Syahri Banun", text: "Absolutely stunning! The service was impeccable from the moment I arrived." },
    { user: "Chelsea", text: "The best stay I've ever had. The panoramic view from the suite is worth every penny." },
    { user: "Fazle Mawla Wahyuhanda", text: "Fasilitasnya sangat lengkap dan stafnya sangat membantu. Sangat direkomendasikan!" },
    { user: "Jylan Annisa Mumtaza Syidana", text: "A truly serene experience. The attention to detail in the room design is amazing." },
    { user: "Sitti Aminah", text: "Luxury at its finest. The breakfast spread was world-class." }
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

// Building the Database
let idCounter = 1;
locations.forEach(loc => {
    hotelNames[loc].forEach((name, index) => {
        const rating = (4.5 + Math.random() * 0.5).toFixed(1);
        const reviewsCount = Math.floor(Math.random() * 8000) + 1000; // Generate total reviews random
 
        database.push({
            id: idCounter++,
            name: name,
            loc: loc,
            rate: rating,
            reviews: reviewsCount,
            description: descriptions[Math.floor(Math.random() * descriptions.length)], // Random desc
            facilities: [...generalFacilities].sort(() => 0.5 - Math.random()).slice(0, 5), // Random 5 facilities
            topReviews: reviewTemplates, // Top 5 reviews
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
    if (isLogin) {
        document.getElementById('login-section').classList.remove('hidden');
        document.getElementById('signup-section').classList.add('hidden');
    } else {
        document.getElementById('login-section').classList.add('hidden');
        document.getElementById('signup-section').classList.remove('hidden');
    }
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
    const foundUser = users.find(u => u.user === userIn && u.pass === passIn) || (userIn === "admin" && passIn === "admin");

    if (foundUser) {
        currentUserObj = foundUser === true ? { name: "Demo User", phone: "0812345", ktp: "12345678" } : foundUser;
        document.getElementById('auth-page').classList.add('hidden');
        document.getElementById('main-app').classList.remove('hidden');
        document.getElementById('navbar').classList.remove('hidden');
        document.getElementById('user-info').innerText = `Hi, ${currentUserObj.name} ✦`;
        renderRecommendations();
    } else { alert("Invalid credentials. Try admin/admin for demo."); }
}

function showPage(pageId) {
    document.getElementById('home-page').classList.add('hidden');
    document.getElementById('results-page').classList.add('hidden');
    document.getElementById(pageId).classList.remove('hidden');
    window.scrollTo(0, 0);
}

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
    if (data.length === 0) { list.innerHTML = "<h3>No hotels found in this area.</h3>"; return; }

    list.innerHTML = data.map(h => `
        <div class="hotel-card">
            <img src="${h.thumb}" class="hotel-img-large">
            <div class="hotel-details">
                <div>
                    ${h.isLuxury ? '<span class="badge-luxury"> Luxury Collection</span>' : ''}
                    <div style="display:flex; justify-content:space-between; align-items: flex-start;">
                        <div>
                            <h2 style="font-family:'Cormorant Garamond',serif; font-size: 1.7rem; font-weight:400; letter-spacing:-0.5px; margin-bottom:6px;">${h.name}</h2>
                            <p style="color:var(--text-muted); font-size:0.82rem; letter-spacing:1px; text-transform:uppercase;">📍 ${h.loc}</p>
                        </div>
                        <div style="text-align:right; flex-shrink:0; margin-left:16px;">
                            <div style="color:var(--gold-dark); font-weight:600; font-size:1rem;">⭐ ${h.rate}</div>
                            <small style="color:var(--text-light); font-size:0.75rem;">${h.reviews} Reviews</small>
                        </div>
                    </div>
                </div>
                <div style="display:flex; justify-content:space-between; align-items:center; background:var(--ivory); padding: 18px 20px; border-radius: 4px; border: 1px solid var(--border);">
                    <div>
                        <small style="color:var(--text-muted); text-transform:uppercase; font-size:0.65rem; letter-spacing:2px; font-weight:600; display:block; margin-bottom:4px;">Best price from</small>
                        <h3 style="color:var(--charcoal); font-family:'Cormorant Garamond',serif; font-size:1.6rem; font-weight:400;">Rp ${h.rooms[0].price.toLocaleString()}</h3>
                    </div>
                    <button class="btn-gradient" onclick="openRoomModal(${h.id})" style="padding: 13px 26px;">Select Room</button>
                </div>
            </div>
        </div>`).join('');
}

// --- UPDATED LOGIC FOR MODAL CONTENT (DESC, REVIEWS, FACILITIES) ---
function openRoomModal(hotelId) {
    const hotel = database.find(h => h.id === hotelId);
    document.getElementById('modal-hotel-name').innerText = hotel.name;
    document.getElementById('modal-hotel-loc').innerText = `Exclusive Stays in ${hotel.loc}`;
    
    // 1. Populate Description
    document.getElementById('modal-hotel-description').innerHTML = `<p>${hotel.description}</p>`;

    // 2. Populate Facilities
    const facContainer = document.getElementById('modal-hotel-facilities');
    facContainer.innerHTML = hotel.facilities.map(f => `<span class="facility-tag-detail">✦ ${f}</span>`).join('');

    // 3. Populate Reviews
    document.getElementById('review-stats').innerText = `5 from ${hotel.reviews.toLocaleString()} reviews`;
    const reviewContainer = document.getElementById('modal-hotel-reviews');
    reviewContainer.innerHTML = hotel.topReviews.map(rev => `
        <div class="review-item">
            <span class="review-user">${rev.user}</span>
            <div class="review-stars">⭐⭐⭐⭐⭐</div>
            <p class="review-text">"${rev.text}"</p>
        </div>
    `).join('');

    // 4. Populate Room Selection
    const roomGrid = document.getElementById('room-grid');
    roomGrid.innerHTML = hotel.rooms.map(room => `
        <div class="room-card">
            <img src="${room.img}" style="width: 100%; height: 190px; object-fit: cover; border-radius: 15px; margin-bottom: 20px; display:block;">
            <div style="flex-grow: 1;">
                <h3 style="font-family:'Cormorant Garamond',serif; font-size:1.4rem; font-weight:600; margin-bottom:12px;">${room.name}</h3>
                <div style="display: flex; flex-wrap: wrap; gap: 6px; margin-bottom: 20px;">
                    ${room.fac.map(f => `<span class="facility-tag">✦ ${f}</span>`).join('')}
                </div>
            </div>
            <div style="display: flex; justify-content: space-between; align-items: center; border-top: 1px solid #EDE9E0; padding-top: 18px;">
                <div>
                    <small style="color:var(--text-muted); font-size:0.68rem; letter-spacing:1.5px; text-transform:uppercase; display:block; margin-bottom:3px;">Per Night</small>
                    <h4 style="font-family:'Cormorant Garamond',serif; font-size:1.4rem; font-weight:600; color:var(--charcoal);">Rp ${room.price.toLocaleString()}</h4>
                </div>
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
    if (!name || !phone) { alert("Complete your details!"); return; }

    pendingBooking.guestName = name;
    pendingBooking.guestPhone = phone;
    pendingBooking.guestKTP = (document.getElementById('book-for').value === 'self') ? currentUserObj.ktp : document.getElementById('guest-id-num').value;

    document.getElementById('payment-summary').innerHTML = `
        <div class="receipt-row"><span>Hotel</span><strong>${pendingBooking.hName}</strong></div>
        <div class="receipt-row"><span>Guest</span><strong>${name}</strong></div>
        <div class="receipt-row"><span>Package</span><strong>${pendingBooking.config}</strong></div>
        <div class="receipt-total">
            <span style="font-size:0.75rem; letter-spacing:2px; text-transform:uppercase; color:var(--text-muted); font-weight:600;">Total Amount</span>
            <strong style="font-family:'Cormorant Garamond',serif; font-size:1.8rem; font-weight:400; color:var(--charcoal);">Rp ${pendingBooking.price.toLocaleString()}</strong>
        </div>
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
    showPage('home-page');
}

function updateHistory() {
    const container = document.getElementById('booking-history');
    container.innerHTML = bookings.map((b, idx) => `
        <div class="history-card" onclick="showReceipt(${idx})">
            <div style="width: 100%;">
                <div style="display:flex; justify-content:space-between; align-items:center;">
                    <h4 style="font-size:1rem; font-weight:500;">${b.hName}</h4>
                    <span style="font-family:'Cormorant Garamond',serif; font-size:1.2rem; font-weight:400; color:var(--charcoal);">Rp ${b.price.toLocaleString()}</span>
                </div>
                <div style="display:flex; justify-content:space-between; margin-top:8px; color:var(--text-muted); font-size:0.78rem; letter-spacing:0.3px;">
                    <span>${b.rType} · ${b.date}</span>
                    <span style="color:var(--gold-dark); font-weight:600; letter-spacing:0.5px;">View Receipt →</span>
                </div>
            </div>
        </div>`).join('');
}

function showReceipt(idx) {
    const b = bookings[idx];
    document.getElementById('receipt-details').innerHTML = `
        <div style="background:var(--ivory-warm); padding: 24px; border-radius: 4px; border: 1px solid var(--border);">
            <div class="receipt-row"><span>Booking ID: </span><strong>#TRPZ-${1000 + idx}</strong></div>
            <div class="receipt-row"><span>Guest Name: </span><strong>${b.guestName}</strong></div>
            <div class="receipt-row"><span>Hotel: </span><strong>${b.hName}</strong></div>
            <div class="receipt-row"><span>Room: </span><strong>${b.rType}</strong></div>
            <div class="receipt-row"><span>ID Number: </span><strong>${b.guestKTP}</strong></div>
            <div class="receipt-row"><span>Payment: </span><strong>${b.payment}</strong></div>
            <div class="receipt-total">
                <span style="font-size:0.7rem; letter-spacing:2px; text-transform:uppercase; color:var(--text-muted); font-weight:600;">Total Paid</span>
                <strong style="font-family:'Cormorant Garamond',serif; font-size:1.8rem; font-weight:400; color:var(--gold-dark);">Rp ${b.price.toLocaleString()}</strong>
            </div>
        </div>`;
    document.getElementById('receipt-modal').style.display = 'flex';
}

function closeModal(id) { document.getElementById(id).style.display = 'none'; }