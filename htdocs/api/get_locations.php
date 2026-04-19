<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
require_once '../includes/db.php';

$sql = "SELECT b.bus_number, bl.latitude, bl.longitude, bl.last_update 
        FROM buses b
        LEFT JOIN bus_locations bl ON b.id = bl.bus_id
        WHERE b.status = 'active'
        ORDER BY b.bus_number";
        
$result = $conn->query($sql);
$buses = [];

while ($row = $result->fetch_assoc()) {
    $buses[] = [
        'bus_number' => $row['bus_number'],
        'latitude' => $row['latitude'] ? floatval($row['latitude']) : null,
        'longitude' => $row['longitude'] ? floatval($row['longitude']) : null,
        'last_update' => $row['last_update']
    ];
}

echo json_encode($buses);
?>