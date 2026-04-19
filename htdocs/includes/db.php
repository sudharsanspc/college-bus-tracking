<?php
$host = "sql313.infinityfree.com";   // un screenshot la irundhu
$username = "if0_41696127";          // DB username
$password = "ZP14esrSg9Ypo";      // panel la irukum (IMPORTANT)
$database = "ifo_41696127_bus_tracking";

$conn = new mysqli($host, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>