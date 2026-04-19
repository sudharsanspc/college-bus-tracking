<?php
/**
 * API Endpoint: Get Buses by Route
 * Returns available buses for a given from and to location
 * Supports both directions (normal and swapped)
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../includes/db.php';
require_once '../includes/functions.php';

// Get parameters
$from = isset($_GET['from']) ? trim($_GET['from']) : '';
$to = isset($_GET['to']) ? trim($_GET['to']) : '';

// Validate input
if (empty($from) || empty($to)) {
    echo json_encode([
        'success' => false,
        'message' => 'Both "from" and "to" parameters are required',
        'buses' => []
    ]);
    exit();
}

// Sanitize inputs
$from = mysqli_real_escape_string($conn, $from);
$to = mysqli_real_escape_string($conn, $to);

// Method 1: Direct route match (from -> to)
$sql = "SELECT DISTINCT 
            b.id as bus_id,
            b.bus_number,
            b.capacity,
            b.status as bus_status,
            r.id as route_id,
            r.route_name,
            r.origin,
            r.destination,
            r.distance_km,
            r.duration_minutes,
            rb.departure_time,
            rb.arrival_time,
            rb.days_of_week,
            GROUP_CONCAT(DISTINCT rs.stop_name ORDER BY rs.stop_order) as stops
        FROM routes r
        JOIN route_buses rb ON r.id = rb.route_id
        JOIN buses b ON rb.bus_id = b.id
        LEFT JOIN route_stops rs ON r.id = rs.route_id
        WHERE (r.origin = '$from' AND r.destination = '$to')
            AND b.status = 'active'
        GROUP BY b.id, r.id, rb.departure_time
        ORDER BY b.bus_number";

$result = $conn->query($sql);
$buses = [];

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        // Process days of week
        $days = explode(',', $row['days_of_week']);
        $dayNames = [];
        $dayMap = [
            1 => 'Mon', 2 => 'Tue', 3 => 'Wed', 4 => 'Thu', 
            5 => 'Fri', 6 => 'Sat', 7 => 'Sun'
        ];
        foreach ($days as $day) {
            if (isset($dayMap[$day])) {
                $dayNames[] = $dayMap[$day];
            }
        }
        
        $buses[] = [
            'bus_id' => $row['bus_id'],
            'bus_number' => $row['bus_number'],
            'capacity' => $row['capacity'],
            'route_id' => $row['route_id'],
            'route_name' => $row['route_name'] ?? "{$row['origin']} → {$row['destination']}",
            'origin' => $row['origin'],
            'destination' => $row['destination'],
            'distance_km' => floatval($row['distance_km']),
            'duration_minutes' => intval($row['duration_minutes']),
            'departure_time' => $row['departure_time'] ? date('h:i A', strtotime($row['departure_time'])) : 'Scheduled',
            'departure_time_raw' => $row['departure_time'],
            'arrival_time' => $row['arrival_time'] ? date('h:i A', strtotime($row['arrival_time'])) : null,
            'days_of_week' => implode(', ', $dayNames),
            'stops' => $row['stops'] ? explode(',', $row['stops']) : [],
            'status' => 'available',
            'type' => 'direct'
        ];
    }
}

// If no direct route found, try reverse route (to -> from)
if (empty($buses)) {
    $sql_reverse = "SELECT DISTINCT 
                        b.id as bus_id,
                        b.bus_number,
                        b.capacity,
                        b.status as bus_status,
                        r.id as route_id,
                        r.route_name,
                        r.origin,
                        r.destination,
                        r.distance_km,
                        r.duration_minutes,
                        rb.departure_time,
                        rb.arrival_time,
                        rb.days_of_week,
                        GROUP_CONCAT(DISTINCT rs.stop_name ORDER BY rs.stop_order) as stops
                    FROM routes r
                    JOIN route_buses rb ON r.id = rb.route_id
                    JOIN buses b ON rb.bus_id = b.id
                    LEFT JOIN route_stops rs ON r.id = rs.route_id
                    WHERE (r.origin = '$to' AND r.destination = '$from')
                        AND b.status = 'active'
                    GROUP BY b.id, r.id, rb.departure_time
                    ORDER BY b.bus_number";
    
    $result_reverse = $conn->query($sql_reverse);
    
    if ($result_reverse && $result_reverse->num_rows > 0) {
        while ($row = $result_reverse->fetch_assoc()) {
            $days = explode(',', $row['days_of_week']);
            $dayNames = [];
            $dayMap = [1 => 'Mon', 2 => 'Tue', 3 => 'Wed', 4 => 'Thu', 5 => 'Fri', 6 => 'Sat', 7 => 'Sun'];
            foreach ($days as $day) {
                if (isset($dayMap[$day])) $dayNames[] = $dayMap[$day];
            }
            
            $buses[] = [
                'bus_id' => $row['bus_id'],
                'bus_number' => $row['bus_number'],
                'capacity' => $row['capacity'],
                'route_id' => $row['route_id'],
                'route_name' => $row['route_name'] ?? "{$row['origin']} → {$row['destination']}",
                'origin' => $row['origin'],
                'destination' => $row['destination'],
                'distance_km' => floatval($row['distance_km']),
                'duration_minutes' => intval($row['duration_minutes']),
                'departure_time' => $row['departure_time'] ? date('h:i A', strtotime($row['departure_time'])) : 'Scheduled',
                'departure_time_raw' => $row['departure_time'],
                'arrival_time' => $row['arrival_time'] ? date('h:i A', strtotime($row['arrival_time'])) : null,
                'days_of_week' => implode(', ', $dayNames),
                'stops' => $row['stops'] ? explode(',', $row['stops']) : [],
                'status' => 'available',
                'type' => 'reverse'
            ];
        }
    }
}

// Get current location for each bus (if available)
foreach ($buses as &$bus) {
    $location_sql = "SELECT latitude, longitude, last_update, speed 
                     FROM bus_locations 
                     WHERE bus_id = {$bus['bus_id']} 
                     ORDER BY last_update DESC LIMIT 1";
    $location_result = $conn->query($location_sql);
    
    if ($location_result && $location_result->num_rows > 0) {
        $location = $location_result->fetch_assoc();
        $bus['current_location'] = [
            'latitude' => floatval($location['latitude']),
            'longitude' => floatval($location['longitude']),
            'last_update' => $location['last_update'],
            'speed' => floatval($location['speed'])
        ];
        
        // Calculate if bus is on time (based on schedule)
        $current_time = date('H:i:s');
        if ($bus['departure_time_raw']) {
            $departure_timestamp = strtotime($bus['departure_time_raw']);
            $current_timestamp = strtotime($current_time);
            $time_diff = ($current_timestamp - $departure_timestamp) / 60; // minutes difference
            
            if ($time_diff < -15) {
                $bus['schedule_status'] = 'early';
                $bus['schedule_message'] = 'Bus will depart in ' . abs(round($time_diff)) . ' minutes';
            } elseif ($time_diff <= 15) {
                $bus['schedule_status'] = 'on_time';
                $bus['schedule_message'] = 'On schedule';
            } else {
                $bus['schedule_status'] = 'delayed';
                $bus['schedule_message'] = round($time_diff) . ' minutes delayed';
            }
        } else {
            $bus['schedule_status'] = 'unknown';
            $bus['schedule_message'] = 'Schedule information not available';
        }
    } else {
        $bus['current_location'] = null;
        $bus['schedule_status'] = 'unknown';
        $bus['schedule_message'] = 'Live location not available';
    }
}

// Prepare response
$response = [
    'success' => true,
    'message' => count($buses) . ' bus(es) found for route: ' . $from . ' → ' . $to,
    'route' => [
        'from' => $from,
        'to' => $to
    ],
    'total_buses' => count($buses),
    'buses' => $buses,
    'timestamp' => date('Y-m-d H:i:s')
];

// Add alternative routes suggestion if no buses found
if (empty($buses)) {
    // Find nearby locations as suggestions
    $suggestion_sql = "SELECT DISTINCT origin, destination 
                       FROM routes 
                       WHERE origin LIKE '%$from%' 
                          OR destination LIKE '%$to%'
                          OR origin LIKE '%" . explode(' ', $from)[0] . "%'
                       LIMIT 5";
    $suggestion_result = $conn->query($suggestion_sql);
    $suggestions = [];
    
    if ($suggestion_result && $suggestion_result->num_rows > 0) {
        while ($row = $suggestion_result->fetch_assoc()) {
            $suggestions[] = $row;
        }
    }
    
    $response['message'] = 'No buses found for the selected route';
    $response['suggestions'] = $suggestions;
}

// Log the search query (for analytics)
if (isset($_SESSION['user_id'])) {
    $log_sql = "INSERT INTO search_logs (user_id, search_from, search_to, result_count, created_at) 
                VALUES ({$_SESSION['user_id']}, '$from', '$to', " . count($buses) . ", NOW())";
    $conn->query($log_sql);
}

// Output response
echo json_encode($response, JSON_PRETTY_PRINT);
?>