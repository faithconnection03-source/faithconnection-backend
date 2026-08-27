<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json; charset=UTF-8");

$host = "mysql-ce96da-faithconnection03-fe6b.f.aivencloud.com";
$port = 14165;
$dbname = "defaultdb";
$username = "avnadmin";
$password = "AVNS_UfdMBLZ_aN_SapdQQk_"; 

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    $conn = mysqli_init();
    mysqli_ssl_set($conn, NULL, NULL, NULL, NULL, NULL);
    mysqli_options($conn, MYSQLI_OPT_SSL_VERIFY_SERVER_CERT, false);
    mysqli_real_connect($conn, $host, $username, $password, $dbname, $port, NULL, MYSQLI_CLIENT_SSL);
    mysqli_set_charset($conn, "utf8mb4");
} catch (Exception $e) {
    echo json_encode([
        "success" => false, 
        "message" => "Database connection failed: " . $e->getMessage()
    ]);
    exit();
}

$action = isset($_GET['action']) ? $_GET['action'] : '';
$data = json_decode(file_get_contents("php://input"), true);

switch ($action) {
    case 'register':
        if (!empty($data['name']) && !empty($data['email']) && !empty($data['password'])) {
            $name = $conn->real_escape_string($data['name']);
            $email = $conn->real_escape_string($data['email']);
            $password = password_hash($data['password'], PASSWORD_BCRYPT);

            $check = $conn->query("SELECT id FROM users WHERE email='$email'");
            if ($check->num_rows > 0) {
                echo json_encode(["success" => false, "message" => "Email already registered"]);
                exit();
            }

            $sql = "INSERT INTO users (name, email, password) VALUES ('$name', '$email', '$password')";
            if ($conn->query($sql)) {
                echo json_encode(["success" => true, "message" => "Registration successful! Please login."]);
            } else {
                echo json_encode(["success" => false, "message" => "Error registering user"]);
            }
        } else {
            echo json_encode(["success" => false, "message" => "Incomplete data"]);
        }
        break;

    case 'login':
        if (!empty($data['email']) && !empty($data['password'])) {
            $email = $conn->real_escape_string($data['email']);
            $password = $data['password'];

            $res = $conn->query("SELECT * FROM users WHERE email='$email'");
            if ($res->num_rows > 0) {
                $user = $res->fetch_assoc();
                if (password_verify($password, $user['password'])) {
                    echo json_encode([
                        "success" => true,
                        "message" => "Login successful",
                        "user" => [
                            "id" => $user['id'],
                            "name" => $user['name'],
                            "email" => $user['email']
                        ]
                    ]);
                } else {
                    echo json_encode(["success" => false, "message" => "Invalid credentials"]);
                }
            } else {
                echo json_encode(["success" => false, "message" => "User not found"]);
            }
        } else {
            echo json_encode(["success" => false, "message" => "Incomplete data"]);
        }
        break;

    case 'create_post':
        if (!empty($data['user_id']) && !empty($data['content'])) {
            $user_id = (int)$data['user_id'];
            $content = $conn->real_escape_string($data['content']);

            $sql = "INSERT INTO posts (user_id, content) VALUES ($user_id, '$content')";
            if ($conn->query($sql)) {
                echo json_encode(["success" => true, "message" => "Post published successfully!"]);
            } else {
                echo json_encode(["success" => false, "message" => "Error creating post"]);
            }
        } else {
            echo json_encode(["success" => false, "message" => "Invalid post data"]);
        }
        break;

    case 'get_posts':
        $sql = "SELECT posts.id, posts.content, posts.created_at, users.name as user_name 
                FROM posts 
                JOIN users ON posts.user_id = users.id 
                ORDER BY posts.created_at DESC";
        $res = $conn->query($sql);
        $posts = [];
        if ($res && $res->num_rows > 0) {
            while ($row = $res->fetch_assoc()) {
                $posts[] = $row;
            }
        }
        echo json_encode(["success" => true, "posts" => $posts]);
        break;

    default:
        echo json_encode(["success" => false, "message" => "Invalid action"]);
        break;
}

$conn->close();
?>
