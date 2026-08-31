<?php
$host = "mysql-ce96da-faithconnection03-fe6b.f.aivencloud.com";
$port = 14165;
$dbname = "defaultdb";
$username = "avnadmin";
$password = "AVNS_gUyJ-wb3_WArX1cBgxU";

$conn = mysqli_init();
mysqli_ssl_set($conn, NULL, NULL, NULL, NULL, NULL);
mysqli_options($conn, MYSQLI_OPT_SSL_VERIFY_SERVER_CERT, false);

if (!mysqli_real_connect($conn, $host, $username, $password, $dbname, $port, NULL, MYSQLI_CLIENT_SSL)) {
    die("Connection Failed: " . mysqli_connect_error());
}
echo "Database Connection Successful!<br>";

// Ek test table banane ki query
$sql = "CREATE TABLE IF NOT EXISTS test_table (
    id INT AUTO_INCREMENT PRIMARY KEY,
    message VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

if ($conn->query($sql) === TRUE) {
    echo "Table 'test_table' successfully ban gayi hai!";
} else {
    echo "Error creating table: " . $conn->error;
}

$conn->close();
?>