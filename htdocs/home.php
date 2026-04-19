<?php  
error_reporting(E_ALL);  
ini_set('display_errors', 1);  
  
session_start();  
require_once 'includes/db.php';  
require_once 'includes/auth.php';  
  
if (!isset($_SESSION['user_id'])) {  
    header("Location: index.php");  
    exit();  
}  
  

if(isset($_GET['location']) && $_GET['location'] != '') {  
    $location = $_GET['location'];  
  
    $sql = "SELECT b.bus_number, bl.latitude, bl.longitude, bl.last_update   
            FROM buses b   
            LEFT JOIN bus_locations bl ON b.id = bl.bus_id   
            JOIN route_buses rb ON b.id = rb.bus_id  
            JOIN routes r ON rb.route_id = r.id  
            WHERE b.status = 'active' AND r.origin = '$location'";  
} else {  
    $sql = "SELECT b.bus_number, bl.latitude, bl.longitude, bl.last_update   
            FROM buses b   
            LEFT JOIN bus_locations bl ON b.id = bl.bus_id   
            WHERE b.status = 'active'";  
}  
  
$result = $conn->query($sql);  
$buses = $result->fetch_all(MYSQLI_ASSOC);  
?>  <!DOCTYPE html>  <html>  
<head>  
    <title>Home - RIT Bus Tracking System</title>  
    <link rel="icon" href="assets/images/rit-logo-wide-1.png" type="image/png">  
    <link rel="stylesheet" href="assets/css/style.css">  
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />  
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">  
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>  
    <style>  
        .college-header {  
            background: linear-gradient(135deg, #1a237e 0%, #283593 100%);  
            border-radius: 15px;  
            padding: 20px 30px;  
            margin-bottom: 30px;  
            color: white;  
            display: flex;  
            align-items: center;  
            justify-content: space-between;  
            flex-wrap: wrap;  
            gap: 20px;  
        }  .accreditation-info {  
        display: flex;  
        gap: 15px;  
        flex-wrap: wrap;  
    }  
      
    .accreditation-badge {  
        background: rgba(255,255,255,0.15);  
        padding: 8px 15px;  
        border-radius: 20px;  
        font-size: 0.75rem;  
        display: flex;  
        align-items: center;  
        gap: 8px;  
    }  
      
    .stats-container {  
        display: grid;  
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));  
        gap: 20px;  
        margin-bottom: 30px;  
    }  
      
    .stat-card {  
        background: white;  
        padding: 20px;  
        border-radius: 12px;  
        text-align: center;  
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);  
    }  
      
    .stat-number {  
        font-size: 32px;  
        font-weight: bold;  
        color: #ff9800;  
    }  
      
    .stat-label {  
        color: #666;  
        margin-top: 5px;  
    }  
</style>

</head>  
<body>  
    <?php include 'includes/navbar.php'; ?>  <div class="container">  
    <!-- College Header with Logo -->  
    <div class="college-header">  
        <div class="college-info">  
            <img src="assets/images/rit-logo-wide-1.png"   
                 alt="RIT Logo"   
                 class="college-logo-large"  
                 onerror="this.src='data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' width=\'80\' height=\'80\' viewBox=\'0 0 100 100\'%3E%3Crect width=\'100\' height=\'100\' fill=\'%23ffffff\'/%3E%3Ctext x=\'50\' y=\'55\' font-size=\'40\' text-anchor=\'middle\' fill=\'%231a237e\'%3E🏛️%3C/text%3E%3C/svg%3E'">  
            <div class="college-text">  
                <h3>  
                    <i class="fas fa-university"></i>   
                    Ramco Institute of Technology  
                </h3>  
                <p>(An Autonomous Institution) | Approved by AICTE, New Delhi | Affiliated to Anna University</p>  
                <p><i class="fas fa-map-marker-alt"></i> Rajapalayam, Tamil Nadu - 626117</p>  
            </div>  
        </div>  
        <div class="accreditation-info">  
            <div class="accreditation-badge">  
                <i class="fas fa-trophy"></i> NBA Accredited  
            </div>  
            <div class="accreditation-badge">  
                <i class="fas fa-microchip"></i> CSE | EEE | ECE  
            </div>  
            <div class="accreditation-badge">  
                <i class="fas fa-cogs"></i> MECH | CIVIL  
            </div>  
        </div>  
    </div>  
      
    <!-- Statistics -->  
    <div class="stats-container">  
        <div class="stat-card">  
            <div class="stat-number"><?php echo count($buses); ?></div>  
            <div class="stat-label"><i class="fas fa-bus"></i> Active Buses</div>  
        </div>  
        <div class="stat-card">  
            <div class="stat-number" id="activeCount">0</div>  
            <div class="stat-label"><i class="fas fa-play-circle"></i> Buses on Route</div>  
        </div>  
        <div class="stat-card">  
            <div class="stat-number">5+</div>  
            <div class="stat-label"><i class="fas fa-route"></i> Routes Available</div>  
        </div>  
        <div class="stat-card">  
            <div class="stat-number">500+</div>  
            <div class="stat-label"><i class="fas fa-users"></i> Daily Commuters</div>  
        </div>  
    </div>  
      
    <!-- 🔥 NEW HEADER + LOGOUT -->

<div style="display:flex; justify-content:space-between; align-items:center;">  
    <h3><i class="fas fa-bus"></i> Available Buses on Campus</h3>  
</div>  <div class="bus-list" id="busList">  
<?php foreach($buses as $bus): ?>  
      
    <a href="map.php?bus=<?php echo $bus['bus_number']; ?>" style="text-decoration:none; color:inherit;">  
          
        <div class="bus-card" data-bus="<?php echo $bus['bus_number']; ?>">  
            <i class="fas fa-bus"></i>  
              
            <div>  
                <strong>Bus <?php echo $bus['bus_number']; ?></strong>  
            </div>  

            <span class="status-badge <?php echo $bus['latitude'] ? 'status-active' : 'status-inactive'; ?>">  
                <i class="fas fa-circle" style="font-size: 8px;"></i>  
                <?php echo $bus['latitude'] ? 'Active' : 'Offline'; ?>  
            </span>  
        </div>  

    </a>  

<?php endforeach; ?>

</div>  <script>  
    const busData = <?php echo json_encode($buses); ?>;  
    let map;  
    let markers = {};  
      
    function initMap() {  
        const collegeLat = 9.4306;  
        const collegeLng = 77.5568;  
          
        map = L.map('map').setView([collegeLat, collegeLng], 13);  
          
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {  
            attribution: '© OpenStreetMap contributors'  
        }).addTo(map);  
          
        // College marker  
        const collegeIcon = L.divIcon({  
            html: `<div style="background: #1a237e; color: white; padding: 8px 15px; border-radius: 25px; display: flex; align-items: center; gap: 8px;">  
                    <i class="fas fa-university"></i>  
                    <span>Ramco Institute of Technology</span>  
                    </div>`,  
            iconSize: [220, 40]  
        });  
          
        L.marker([collegeLat, collegeLng], { icon: collegeIcon })  
            .addTo(map)  
            .bindPopup('<b>🏛️ Ramco Institute of Technology</b><br>Rajapalayam, Tamil Nadu');  
          
        updateMapMarkers(busData);  
    }  
      
    function updateMapMarkers(buses) {  
        let activeCount = 0;  
          
        buses.forEach(bus => {  
            if (bus.latitude && bus.longitude) {  
                activeCount++;  
                const position = [parseFloat(bus.latitude), parseFloat(bus.longitude)];  
                  
                const busIcon = L.divIcon({  
                    html: `<div style="background: #ff9800; color: white; padding: 5px 12px; border-radius: 20px; display: flex; align-items: center; gap: 5px; font-weight: bold;">  
                            <i class="fas fa-bus"></i>  
                            Bus ${bus.bus_number}  
                            </div>`,  
                    iconSize: [90, 30]  
                });  
                  
                if (markers[bus.bus_number]) {  
                    markers[bus.bus_number].setLatLng(position);  
                } else {  
                    markers[bus.bus_number] = L.marker(position, { icon: busIcon })  
                        .addTo(map)  
                        .bindPopup(`<b>🚌 Bus ${bus.bus_number}</b><br>Last update: ${new Date(bus.last_update).toLocaleTimeString()}`);  
                }  
            }  
        });  
          
        document.getElementById('activeCount').innerText = activeCount;  
    }  
      
    function refreshLocations() {  
        fetch('api/get_locations.php')  
            .then(response => response.json())  
            .then(data => {  
                updateMapMarkers(data);  
                document.getElementById('updateTime').innerText = new Date().toLocaleTimeString();  
            })  
            .catch(error => console.error('Error:', error));  
    }  
      
    document.addEventListener('DOMContentLoaded', () => {  
        if (typeof L !== 'undefined') {  
            initMap();  
            setInterval(refreshLocations, 10000);  
            document.getElementById('updateTime').innerText = new Date().toLocaleTimeString();  
        }  
    });  
</script>

</body>  
</html>
