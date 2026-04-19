<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$busNumber = $_GET['bus'] ?? '';
?>

<!DOCTYPE html>
<html>
<head>
    <title>Track Bus</title>

    <link rel="stylesheet" href="assets/css/style.css">

    <!-- Leaflet -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

    <style>
        #map {
            height: 500px;
            margin-top: 15px;
            border-radius: 12px;
        }
    </style>
</head>

<body>

<?php include 'includes/navbar.php'; ?>

<div class="container">
    <h3>🗺️ Bus <?php echo $busNumber; ?> Live Tracking 🔴</h3>
    <div id="map"></div>
</div>

<script>
let map;
let marker;

// 🔥 ROUTE (Bus 14)
let route = [
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

let i = 0;

function initMap() {

    // map init
    map = L.map('map').setView(route[0], 13);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png')
        .addTo(map);

    // 🟢 route line
    L.polyline(route, {
        color: 'green',
        weight: 4
    }).addTo(map);

    // 🚍 start movement
    moveBus();
}

// 🔥 BUS MOVEMENT
function moveBus() {
    setInterval(() => {

        i++;
        if(i >= route.length) i = 0;

        let lat = route[i][0];
        let lng = route[i][1];

        if(marker){
            marker.setLatLng([lat, lng]);
        } else {
            marker = L.marker([lat, lng])
                .addTo(map)
                .bindPopup("🚌 Bus <?php echo $busNumber; ?>")
                .openPopup();
        }

        map.panTo([lat, lng]);

    }, 2000);
}

document.addEventListener("DOMContentLoaded", initMap);
</script>

</body>
</html>