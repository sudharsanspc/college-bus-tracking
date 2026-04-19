// Home page JavaScript
let map;
let markers = {};
let updateInterval;

// 🔥 Bus 14 Route
const bus14Route = [
 [9.515217345595737, 77.62934564723365],
 [9.515052652722918, 77.62707790150843],
 [9.512490698320148, 77.62445287100478],
 [9.510386703360348, 77.62342020234185],
 [9.503865154007773, 77.59801274795473],
 [9.498515524666095, 77.58909285323396],
 [9.487245143848147, 77.58547580641054],
 [9.482024896003418, 77.58215793739969],
 [9.468091122115876, 77.56617110180228],
 [9.460840792917788, 77.55873609308189],
 [9.48284701675354, 77.51437435330176]
];

let routeIndex = 0;

// Initialize map
function initMap() {
    const collegeLat = 9.6427;
    const collegeLng = 77.5674;

    map = L.map('map').setView([collegeLat, collegeLng], 13);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors'
    }).addTo(map);

    // College marker
    L.marker([collegeLat, collegeLng])
        .addTo(map)
        .bindPopup('<b>Ramco Institute of Technology</b>')
        .openPopup();

    // 🔥 Draw route line
    L.polyline(bus14Route, {
        color: 'green',
        weight: 4
    }).addTo(map);

    // 🔥 Start Bus 14 movement
    moveBus14();

    // 🔥 Start API updates (optional)
    fetchBusLocations();
    updateInterval = setInterval(fetchBusLocations, 10000);
}

// 🔥 Move Bus 14 (animation)
function moveBus14() {
    setInterval(() => {

        routeIndex++;
        if (routeIndex >= bus14Route.length) routeIndex = 0;

        let pos = bus14Route[routeIndex];

        if (markers[14]) {
            markers[14].setLatLng(pos);
        } else {
            markers[14] = L.marker(pos)
                .addTo(map)
                .bindPopup("🚌 Bus 14 Live Route");
        }

    }, 2000);
}

// Fetch bus locations from server (other buses)
async function fetchBusLocations() {
    try {
        const response = await fetch('api/get_locations.php');
        const data = await response.json();
        updateMapMarkers(data);
        updateBusList(data);
        updateLastUpdateTime();
    } catch (error) {
        console.error('Error fetching bus locations:', error);
    }
}

// Update markers (other buses)
function updateMapMarkers(buses) {
    buses.forEach(bus => {

        // 🔥 Skip bus 14 (because we manually animate it)
        if (bus.bus_number == 14) return;

        if (bus.latitude && bus.longitude) {
            const position = [
                parseFloat(bus.latitude),
                parseFloat(bus.longitude)
            ];

            if (markers[bus.bus_number]) {
                markers[bus.bus_number].setLatLng(position);
            } else {
                markers[bus.bus_number] = L.marker(position)
                    .addTo(map)
                    .bindPopup(`🚌 Bus ${bus.bus_number}`);
            }
        }
    });
}

// Dummy UI update (safe fallback)
function updateBusList(data) {
    // optional - leave empty if already handled
}

// Update time
function updateLastUpdateTime() {
    const el = document.getElementById("updateTime");
    if (el) {
        el.innerText = new Date().toLocaleTimeString();
    }
}

// Start map
document.addEventListener('DOMContentLoaded', () => {
    initMap();
});