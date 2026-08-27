<?php
// Hostinger MySQL Database Configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'YOUR_HOSTINGER_DB_USER');      // Hostinger MySQL Username
define('DB_PASS', 'YOUR_HOSTINGER_DB_PASSWORD');  // Hostinger MySQL Password
define('DB_NAME', 'YOUR_HOSTINGER_DB_NAME');      // Hostinger MySQL Database Name

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json; charset=UTF-8");

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if ($conn->connect_error) {
    echo json_encode(["success" => false, "message" => "Database Connection Failed: " . $conn->connect_error]);
    exit();
}
?>
