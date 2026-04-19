<?php
// Security functions
function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

function generateToken($length = 32) {
    return bin2hex(random_bytes($length));
}

function encryptData($data, $key) {
    $iv = random_bytes(16);
    $encrypted = openssl_encrypt($data, 'AES-256-CBC', $key, 0, $iv);
    return base64_encode($iv . $encrypted);
}

function decryptData($data, $key) {
    $data = base64_decode($data);
    $iv = substr($data, 0, 16);
    $encrypted = substr($data, 16);
    return openssl_decrypt($encrypted, 'AES-256-CBC', $key, 0, $iv);
}

// Bus related functions
function getAllBuses($conn) {
    $sql = "SELECT * FROM buses WHERE status = 'active' ORDER BY bus_number";
    $result = $conn->query($sql);
    return $result->fetch_all(MYSQLI_ASSOC);
}

function getBusLocation($conn, $bus_id) {
    $sql = "SELECT latitude, longitude, last_update FROM bus_locations WHERE bus_id = ? ORDER BY last_update DESC LIMIT 1";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $bus_id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc();
}

function getBusesByRoute($conn, $origin, $destination) {
    $sql = "SELECT DISTINCT b.bus_number, b.id, r.route_name 
            FROM routes r
            JOIN route_buses rb ON r.id = rb.route_id
            JOIN buses b ON rb.bus_id = b.id
            WHERE (r.origin = ? AND r.destination = ?)
            AND b.status = 'active'
            ORDER BY b.bus_number";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $origin, $destination);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_all(MYSQLI_ASSOC);
}

function updateBusLocation($conn, $bus_id, $latitude, $longitude) {
    $sql = "INSERT INTO bus_locations (bus_id, latitude, longitude) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("idd", $bus_id, $latitude, $longitude);
    return $stmt->execute();
}

function getNearbyBuses($conn, $latitude, $longitude, $radius_km = 5) {
    // Haversine formula to calculate distance
    $sql = "SELECT b.bus_number, bl.latitude, bl.longitude, bl.last_update,
            (6371 * acos(cos(radians(?)) * cos(radians(bl.latitude)) 
            * cos(radians(bl.longitude) - radians(?)) + sin(radians(?)) 
            * sin(radians(bl.latitude)))) AS distance
            FROM bus_locations bl
            JOIN buses b ON bl.bus_id = b.id
            HAVING distance < ?
            ORDER BY distance";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("dddd", $latitude, $longitude, $latitude, $radius_km);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_all(MYSQLI_ASSOC);
}

// User related functions
function getUserById($conn, $id) {
    $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

function getUserPreferences($conn, $user_id) {
    $sql = "SELECT preferences FROM user_profiles WHERE user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    
    if ($row && $row['preferences']) {
        return json_decode($row['preferences'], true);
    }
    return [];
}

function saveUserPreferences($conn, $user_id, $preferences) {
    $preferences_json = json_encode($preferences);
    $sql = "UPDATE user_profiles SET preferences = ? WHERE user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $preferences_json, $user_id);
    return $stmt->execute();
}

// Notification functions
function sendNotification($user_id, $title, $message, $type = 'info') {
    // Store notification in database
    global $conn;
    $sql = "INSERT INTO notifications (user_id, title, message, type, created_at) VALUES (?, ?, ?, ?, NOW())";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("isss", $user_id, $title, $message, $type);
    return $stmt->execute();
}

function getUserNotifications($conn, $user_id, $limit = 10) {
    $sql = "SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $user_id, $limit);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_all(MYSQLI_ASSOC);
}

function markNotificationAsRead($conn, $notification_id) {
    $sql = "UPDATE notifications SET is_read = 1 WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $notification_id);
    return $stmt->execute();
}

// Route functions
function getAllRoutes($conn) {
    $sql = "SELECT DISTINCT origin, destination FROM routes ORDER BY origin";
    $result = $conn->query($sql);
    return $result->fetch_all(MYSQLI_ASSOC);
}

function getRouteStops($conn, $route_id) {
    $sql = "SELECT stop_name, stop_order FROM route_stops WHERE route_id = ? ORDER BY stop_order";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $route_id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_all(MYSQLI_ASSOC);
}

// Time and date functions
function getCurrentTime() {
    return date('H:i:s');
}

function getCurrentDate() {
    return date('Y-m-d');
}

function formatTime($time, $format = 'h:i A') {
    return date($format, strtotime($time));
}

function isBusOnRoute($conn, $bus_id, $route_id) {
    $sql = "SELECT id FROM route_buses WHERE bus_id = ? AND route_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $bus_id, $route_id);
    $stmt->execute();
    return $stmt->get_result()->num_rows > 0;
}

// Validation functions
function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

function validatePhone($phone) {
    return preg_match('/^[0-9]{10}$/', $phone);
}

function validateBusNumber($bus_number) {
    return preg_match('/^[0-9]{1,3}$/', $bus_number);
}

// Response functions
function jsonResponse($data, $status_code = 200) {
    http_response_code($status_code);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit();
}

function redirect($url, $message = null, $type = 'success') {
    if ($message) {
        $_SESSION['flash_message'] = $message;
        $_SESSION['flash_type'] = $type;
    }
    header("Location: $url");
    exit();
}

function displayFlashMessage() {
    if (isset($_SESSION['flash_message'])) {
        $message = $_SESSION['flash_message'];
        $type = $_SESSION['flash_type'] ?? 'info';
        echo "<div class='alert alert-$type'>$message</div>";
        unset($_SESSION['flash_message']);
        unset($_SESSION['flash_type']);
    }
}

// Logging function
function logActivity($conn, $user_id, $action, $details = null) {
    $sql = "INSERT INTO activity_logs (user_id, action, details, ip_address, user_agent, created_at) 
            VALUES (?, ?, ?, ?, ?, NOW())";
    $stmt = $conn->prepare($sql);
    $ip = $_SERVER['REMOTE_ADDR'] ?? null;
    $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? null;
    $stmt->bind_param("issss", $user_id, $action, $details, $ip, $user_agent);
    return $stmt->execute();
}

// Cache functions
function getCache($key, $ttl = 3600) {
    $cache_file = __DIR__ . "/../cache/{$key}.cache";
    if (file_exists($cache_file) && (time() - filemtime($cache_file)) < $ttl) {
        return unserialize(file_get_contents($cache_file));
    }
    return null;
}

function setCache($key, $data) {
    $cache_dir = __DIR__ . "/../cache";
    if (!file_exists($cache_dir)) {
        mkdir($cache_dir, 0777, true);
    }
    $cache_file = $cache_dir . "/{$key}.cache";
    file_put_contents($cache_file, serialize($data));
}

function clearCache($key = null) {
    $cache_dir = __DIR__ . "/../cache";
    if ($key) {
        $cache_file = $cache_dir . "/{$key}.cache";
        if (file_exists($cache_file)) {
            unlink($cache_file);
        }
    } else {
        array_map('unlink', glob("$cache_dir/*.cache"));
    }
}
?>