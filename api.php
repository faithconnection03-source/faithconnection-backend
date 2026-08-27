<?php
$host = "mysql-ce96da-faithconnection03-fe6b.f.aivencloud.com";
$port = 14165;
$dbname = "defaultdb";
$username = "avnadmin";
$password = "AVNS_UfdMBLZ_aN_SapdQQk_"; // Replace with your actual Aiven password

// Enable SSL parameters required by Aiven
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    $conn = mysqli_init();
    
    // Disable certificate verification to match rejectUnauthorized: false
    // Note: mysqli_ssl_set requires keys/certs if strict verification is on, 
    // but passing nulls with flags or setting options handles unverified SSL contexts.
    mysqli_ssl_set($conn, NULL, NULL, NULL, NULL, NULL);
    mysqli_options($conn, MYSQLI_OPT_SSL_VERIFY_SERVER_CERT, false);

    // Establish the connection
    mysqli_real_connect($conn, $host, $username, $password, $dbname, $port, NULL, MYSQLI_CLIENT_SSL);
    
    // Set charset to utf8mb4
    mysqli_set_charset($conn, "utf8mb4");

} catch (Exception $e) {
    header('Content-Type: application/json');
    echo json_encode([
        "success" => false, 
        "message" => "Database connection failed: " . $e->getMessage()
    ]);
    exit();
}
?>
