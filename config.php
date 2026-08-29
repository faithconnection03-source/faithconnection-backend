<?php
// Aiven MySQL Database Configuration
define('DB_HOST', 'mysql-ce96da-faithconnection03-fe6b.f.aivencloud.com');
define('DB_USER', 'avnadmin');
define('DB_PASS', 'AVNS_gUyJ-wb3_WArX1cBgxU'); // Yahan apna Aiven wala password daalein
define('DB_NAME', 'defaultdb');
define('DB_PORT', 14165);

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json; charset=UTF-8");

// Initializing MySQL connection with SSL and Port for Aiven
$conn = mysqli_init();

// SSL Certificate requirement for Aiven cloud database
mysqli_ssl_set($conn, NULL, NULL, NULL, NULL, NULL);

// Connecting to the database using custom port
if (!@mysqli_real_connect($conn, DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT, NULL, MYSQLI_CLIENT_SSL)) {
    echo json_encode(["success" => false, "message" => "Database Connection Failed: " . mysqli_connect_error()]);
    exit();
}
?>
