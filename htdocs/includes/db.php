<?php
$host = "sql313.infinityfree.com";   
$username = "db_username";         
$password = "db_password";      
$database = "ifo_41696127_bus_tracking";

$conn = new mysqli($host, $username, $password, $database);


if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
