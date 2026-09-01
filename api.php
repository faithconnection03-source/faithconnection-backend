<?php
declare(strict_types=1);

/*
 * FaithConnection API
 * IMPORTANT: Database connection values below are intentionally unchanged.
 * This file expands the existing API without changing the site's configuration.
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

/* --- Existing connection configuration: DO NOT CHANGE --- */
$host = "sql210.infinityfree.com";
$username = "if0_42767881";
$password = "RdzqKOLVL2SM";
$dbname = "if0_42767881_faithconnectiondb";
$port = 3306;

mysqli_report(MYSQLI_REPORT_OFF);
$conn = new mysqli($host, $username, $password, $dbname, $port);
if ($conn->connect_errno) {
    echo json_encode(["success" => false, "message" => "Database connection failed: " . $conn->connect_error]);
    exit;
}
$conn->set_charset("utf8mb4");

function out(array $data, int $status = 200): void {
    http_response_code($status);
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}
function body(): array {
    $raw = file_get_contents("php://input");
    $data = json_decode($raw ?: "{}", true);
    return is_array($data) ? $data : [];
}
function q(string $name, $default = null) {
    return isset($_GET[$name]) ? $_GET[$name] : $default;
}
function cleanText($value, int $max = 5000): string {
    $value = trim((string)$value);
    return mb_substr($value, 0, $max);
}
function genId(string $prefix): string {
    return $prefix . '_' . bin2hex(random_bytes(8));
}
function stmt(mysqli $conn, string $sql, string $types = '', array $params = []): mysqli_stmt {
    $s = $conn->prepare($sql);
    if (!$s) out(["success"=>false, "message"=>"Database error: ".$conn->error], 500);
    if ($types !== '') $s->bind_param($types, ...$params);
    if (!$s->execute()) out(["success"=>false, "message"=>"Database error: ".$s->error], 500);
    return $s;
}
function tableExists(mysqli $conn, string $table): bool {
    $safe = $conn->real_escape_string($table);
    $r = $conn->query("SHOW TABLES LIKE '$safe'");
    return $r && $r->num_rows > 0;
}
function columnExists(mysqli $conn, string $table, string $column): bool {
    $t = $conn->real_escape_string($table);
    $c = $conn->real_escape_string($column);
    $r = $conn->query("SHOW COLUMNS FROM `$t` LIKE '$c'");
    return $r && $r->num_rows > 0;
}
function ensureColumn(mysqli $conn, string $table, string $column, string $definition): void {
    if (!columnExists($conn, $table, $column)) {
        $conn->query("ALTER TABLE `$table` ADD COLUMN `$column` $definition");
    }
}

/*
 * The original database.sql only contains users + posts.
 * We keep those tables compatible and add the missing social features.
 */
$conn->query("CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

ensureColumn($conn, 'users', 'username', "VARCHAR(100) DEFAULT NULL");
ensureColumn($conn, 'users', 'phone', "VARCHAR(30) DEFAULT NULL");
ensureColumn($conn, 'users', 'role', "VARCHAR(120) DEFAULT 'Church Member'");
ensureColumn($conn, 'users', 'church', "VARCHAR(255) DEFAULT 'Fellowship Community Church'");
ensureColumn($conn, 'users', 'avatar', "LONGTEXT DEFAULT NULL");
ensureColumn($conn, 'users', 'bio', "TEXT DEFAULT NULL");
ensureColumn($conn, 'users', 'updated_at', "TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP");

$conn->query("CREATE TABLE IF NOT EXISTS posts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    content TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
ensureColumn($conn, 'posts', 'type', "VARCHAR(30) DEFAULT 'prayer'");
ensureColumn($conn, 'posts', 'category', "VARCHAR(120) DEFAULT 'General'");
ensureColumn($conn, 'posts', 'image', "LONGTEXT DEFAULT NULL");
ensureColumn($conn, 'posts', 'updated_at', "TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP");

$conn->query("CREATE TABLE IF NOT EXISTS post_likes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    post_id INT NOT NULL,
    user_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_like (post_id,user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
$conn->query("CREATE TABLE IF NOT EXISTS post_prayers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    post_id INT NOT NULL,
    user_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_prayer (post_id,user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
$conn->query("CREATE TABLE IF NOT EXISTS comments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    post_id INT NOT NULL,
    user_id INT NOT NULL,
    text TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
$conn->query("CREATE TABLE IF NOT EXISTS saved_posts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    post_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_saved (user_id,post_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
$conn->query("CREATE TABLE IF NOT EXISTS user_connections (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    friend_id INT NOT NULL,
    status VARCHAR(20) DEFAULT 'connected',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_connection (user_id,friend_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
$conn->query("CREATE TABLE IF NOT EXISTS ministry_groups (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    category VARCHAR(100) NOT NULL,
    leader_id INT NOT NULL,
    bio TEXT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
$conn->query("CREATE TABLE IF NOT EXISTS group_members (
    id INT AUTO_INCREMENT PRIMARY KEY,
    group_id INT NOT NULL,
    user_id INT NOT NULL,
    is_admin TINYINT(1) DEFAULT 0,
    joined_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_group_member (group_id,user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
$conn->query("CREATE TABLE IF NOT EXISTS group_messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    group_id INT NOT NULL,
    user_id INT NOT NULL,
    text TEXT DEFAULT NULL,
    image LONGTEXT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
$conn->query("CREATE TABLE IF NOT EXISTS direct_messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sender_id INT NOT NULL,
    receiver_id INT NOT NULL,
    text TEXT DEFAULT NULL,
    image LONGTEXT DEFAULT NULL,
    is_read TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
$conn->query("CREATE TABLE IF NOT EXISTS stories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    text TEXT NOT NULL,
    media LONGTEXT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

$action = (string)q('action', '');
$data = body();

switch ($action) {
    case 'register': {
        $name = cleanText($data['name'] ?? '', 255);
        $username = preg_replace('/[^a-zA-Z0-9_.]/', '', cleanText($data['username'] ?? '', 100));
        $email = strtolower(trim((string)($data['email'] ?? '')));
        $phone = cleanText($data['phone'] ?? '', 30);
        $role = cleanText($data['role'] ?? 'Church Member', 120);
        $church = cleanText($data['church'] ?? 'Fellowship Community Church', 255);
        $plain = (string)($data['password'] ?? '');

        if ($name === '' || $email === '' || $plain === '') out(["success"=>false,"message"=>"Please complete all required fields."],422);
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) out(["success"=>false,"message"=>"Please enter a valid email."],422);
        if (strlen($plain) < 4) out(["success"=>false,"message"=>"Password must be at least 4 characters."],422);

        $s = stmt($conn, "SELECT id FROM users WHERE email=? LIMIT 1", "s", [$email]);
        $r = $s->get_result();
        if ($r && $r->num_rows) out(["success"=>false,"message"=>"Email already registered."]);
        $s->close();

        if ($username !== '') {
            $s = stmt($conn, "SELECT id FROM users WHERE username=? LIMIT 1", "s", [$username]);
            $r = $s->get_result();
            if ($r && $r->num_rows) $username .= '_' . random_int(100,999);
            $s->close();
        } else {
            $username = preg_replace('/[^a-zA-Z0-9_.]/', '', explode('@',$email)[0]) ?: 'believer';
        }

        $hash = password_hash($plain, PASSWORD_BCRYPT);
        $s = stmt($conn, "INSERT INTO users (name,username,email,phone,password,role,church,avatar,bio) VALUES (?,?,?,?,?,?,?,?,?)",
            "sssssssss", [$name,$username,$email,$phone,$hash,$role,$church,'','']);
        $id = $conn->insert_id;
        $s->close();
        out(["success"=>true,"message"=>"Registration successful! Please login.","user"=>["id"=>$id,"name"=>$name,"username"=>$username,"email"=>$email,"phone"=>$phone,"role"=>$role,"church"=>$church,"avatar"=>"","bio"=>""]]);
    }

    case 'login': {
        $identifier = cleanText($data['identifier'] ?? ($data['email'] ?? ''), 255);
        $plain = (string)($data['password'] ?? '');
        if ($identifier === '' || $plain === '') out(["success"=>false,"message"=>"Please enter username/email/mobile and password."],422);

        $s = stmt($conn, "SELECT * FROM users WHERE email=? OR username=? OR phone=? LIMIT 1", "sss", [$identifier,$identifier,$identifier]);
        $r = $s->get_result();
        if (!$r || !$r->num_rows) { $s->close(); out(["success"=>false,"message"=>"User not found."]); }
        $u = $r->fetch_assoc(); $s->close();

        $valid = password_verify($plain, (string)($u['password'] ?? ''));
        if (!$valid && hash_equals((string)($u['password'] ?? ''), $plain)) {
            $valid = true;
            $newHash = password_hash($plain, PASSWORD_BCRYPT);
            stmt($conn,"UPDATE users SET password=? WHERE id=?","si",[$newHash,$u['id']])->close();
        }
        if (!$valid) out(["success"=>false,"message"=>"Invalid credentials."]);

        out(["success"=>true,"message"=>"Login successful","user"=>$u]);
    }

    case 'google_login': {
        $email = strtolower(trim((string)($data['email'] ?? '')));
        $name = cleanText($data['name'] ?? 'Google User',255);
        $avatar = cleanText($data['avatar'] ?? '',1000000);
        if (!filter_var($email,FILTER_VALIDATE_EMAIL)) out(["success"=>false,"message"=>"Invalid Google account email."],422);

        $s = stmt($conn,"SELECT * FROM users WHERE email=? LIMIT 1","s",[$email]);
        $r = $s->get_result();
        if ($r && $r->num_rows) {
            $u=$r->fetch_assoc(); $s->close();
            if ($avatar !== '' && empty($u['avatar'])) stmt($conn,"UPDATE users SET avatar=? WHERE id=?","si",[$avatar,$u['id']])->close();
            out(["success"=>true,"message"=>"Google Login successful","user"=>$u]);
        }
        $s->close();
        $username = preg_replace('/[^a-zA-Z0-9_.]/','',explode('@',$email)[0]) ?: 'googleuser';
        $base=$username; $n=1;
        while (true) {
            $x=stmt($conn,"SELECT id FROM users WHERE username=? LIMIT 1","s",[$username]);
            $xr=$x->get_result(); $exists=$xr && $xr->num_rows; $x->close();
            if(!$exists) break; $username=$base.$n++;
        }
        $dummy=password_hash(bin2hex(random_bytes(20)),PASSWORD_BCRYPT);
        stmt($conn,"INSERT INTO users (name,username,email,password,role,church,avatar,bio) VALUES (?,?,?,?,?,?,?,?)",
            "ssssssss",[$name,$username,$email,$dummy,'Church Member','Fellowship Community Church',$avatar,'']);
        $id=$conn->insert_id;
        $s=stmt($conn,"SELECT * FROM users WHERE id=?","i",[$id]); $u=$s->get_result()->fetch_assoc(); $s->close();
        out(["success"=>true,"message"=>"Google account connected successfully","user"=>$u]);
    }

    case 'get_user': {
        $id=(int)q('id',0);
        if(!$id) out(["success"=>false,"message"=>"User id is required."],422);
        $s=stmt($conn,"SELECT id,username,name,email,phone,role,church,avatar,bio,created_at FROM users WHERE id=?","i",[$id]);
        $r=$s->get_result(); $u=$r?$r->fetch_assoc():null; $s->close();
        if(!$u) out(["success"=>false,"message"=>"User not found."],404);
        $viewer=(int)q('viewer_id',0);
        $u['posts_count']=0; $u['friends_count']=0; $u['ministries_count']=0; $u['is_following']=false;
        $x=stmt($conn,"SELECT COUNT(*) c FROM posts WHERE user_id=?","i",[$id]); $u['posts_count']=(int)$x->get_result()->fetch_assoc()['c']; $x->close();
        $x=stmt($conn,"SELECT COUNT(*) c FROM user_connections WHERE user_id=? AND status='connected'","i",[$id]); $u['friends_count']=(int)$x->get_result()->fetch_assoc()['c']; $x->close();
        $x=stmt($conn,"SELECT COUNT(*) c FROM group_members WHERE user_id=?","i",[$id]); $u['ministries_count']=(int)$x->get_result()->fetch_assoc()['c']; $x->close();
        if($viewer && $viewer!==$id){ $x=stmt($conn,"SELECT id FROM user_connections WHERE user_id=? AND friend_id=? LIMIT 1","ii",[$viewer,$id]); $u['is_following']=($x->get_result()->num_rows>0); $x->close(); }
        out(["success"=>true,"user"=>$u]);
    }

    case 'update_profile': {
        $id=(int)($data['user_id']??0);
        if(!$id) out(["success"=>false,"message"=>"User id is required."],422);
        $name=cleanText($data['name']??'',255);
        $role=cleanText($data['role']??'Church Member',120);
        $church=cleanText($data['church']??'Fellowship Community Church',255);
        $bio=cleanText($data['bio']??'',3000);
        $avatar=cleanText($data['avatar']??'',1000000);
        $username=preg_replace('/[^a-zA-Z0-9_.]/','',cleanText($data['username']??'',100));
        stmt($conn,"UPDATE users SET name=?,username=?,role=?,church=?,bio=?,avatar=? WHERE id=?","ssssssi",[$name,$username,$role,$church,$bio,$avatar,$id])->close();
        $s=stmt($conn,"SELECT * FROM users WHERE id=?","i",[$id]); $u=$s->get_result()->fetch_assoc(); $s->close();
        out(["success"=>true,"message"=>"Profile updated successfully.","user"=>$u]);
    }

    case 'get_users': {
        $viewer=(int)q('viewer_id',0);
        $sql="SELECT id,username,name,email,phone,role,church,avatar,bio,created_at FROM users ORDER BY name ASC";
        $r=$conn->query($sql); $users=[];
        while($u=$r->fetch_assoc()){
            $u['is_following']=false;
            if($viewer && (int)$u['id']!==$viewer){
                $x=stmt($conn,"SELECT id FROM user_connections WHERE user_id=? AND friend_id=? LIMIT 1","ii",[$viewer,$u['id']]);
                $u['is_following']=$x->get_result()->num_rows>0; $x->close();
            }
            $users[]=$u;
        }
        out(["success"=>true,"users"=>$users]);
    }

    case 'get_posts': {
        $viewer=(int)q('viewer_id',0);
        $sql="SELECT p.id,p.user_id,p.type,p.category,p.content,p.image,p.created_at,p.updated_at,
                     u.username,u.name AS user_name,u.avatar AS user_avatar,u.role AS user_role,u.church AS user_church,
                     (SELECT COUNT(*) FROM post_likes l WHERE l.post_id=p.id) likes_count,
                     (SELECT COUNT(*) FROM post_prayers pr WHERE pr.post_id=p.id) prayers_count,
                     (SELECT COUNT(*) FROM comments c WHERE c.post_id=p.id) comments_count
              FROM posts p JOIN users u ON u.id=p.user_id ORDER BY p.created_at DESC";
        $r=$conn->query($sql); $posts=[];
        while($p=$r->fetch_assoc()){
            $p['liked']=false;$p['prayed']=false;$p['saved']=false;
            if($viewer){
                $x=stmt($conn,"SELECT id FROM post_likes WHERE post_id=? AND user_id=? LIMIT 1","ii",[$p['id'],$viewer]);$p['liked']=$x->get_result()->num_rows>0;$x->close();
                $x=stmt($conn,"SELECT id FROM post_prayers WHERE post_id=? AND user_id=? LIMIT 1","ii",[$p['id'],$viewer]);$p['prayed']=$x->get_result()->num_rows>0;$x->close();
                $x=stmt($conn,"SELECT id FROM saved_posts WHERE post_id=? AND user_id=? LIMIT 1","ii",[$p['id'],$viewer]);$p['saved']=$x->get_result()->num_rows>0;$x->close();
            }
            $posts[]=$p;
        }
        out(["success"=>true,"posts"=>$posts]);
    }

    case 'create_post': {
        $uid=(int)($data['user_id']??0);
        $text=cleanText($data['content']??'',10000);
        $type=($data['type']??'prayer')==='testimony'?'testimony':'prayer';
        $category=cleanText($data['category']??'General',120);
        $image=cleanText($data['image']??'',2000000);
        if(!$uid || ($text==='' && $image==='')) out(["success"=>false,"message"=>"Post cannot be empty."],422);
        stmt($conn,"INSERT INTO posts (user_id,content,type,category,image) VALUES (?,?,?,?,?)","issss",[$uid,$text,$type,$category,$image])->close();
        out(["success"=>true,"message"=>"Post published successfully!","post_id"=>$conn->insert_id]);
    }

    case 'update_post': {
        $uid=(int)($data['user_id']??0); $pid=(int)($data['post_id']??0);
        $text=cleanText($data['content']??'',10000); $type=($data['type']??'prayer')==='testimony'?'testimony':'prayer'; $cat=cleanText($data['category']??'General',120);
        if(!$uid||!$pid) out(["success"=>false,"message"=>"Invalid post data."],422);
        $x=stmt($conn,"SELECT id FROM posts WHERE id=? AND user_id=?","ii",[$pid,$uid]);$ok=$x->get_result()->num_rows>0;$x->close();
        if(!$ok) out(["success"=>false,"message"=>"You can only edit your own posts."],403);
        stmt($conn,"UPDATE posts SET content=?,type=?,category=? WHERE id=?","sssi",[$text,$type,$cat,$pid])->close();
        out(["success"=>true,"message"=>"Post updated successfully!"]);
    }

    case 'delete_post': {
        $uid=(int)($data['user_id']??0);$pid=(int)($data['post_id']??0);
        $x=stmt($conn,"DELETE FROM posts WHERE id=? AND user_id=?","ii",[$pid,$uid]);$affected=$x->affected_rows;$x->close();
        if(!$affected) out(["success"=>false,"message"=>"Post not found or you do not have permission."],403);
        out(["success"=>true,"message"=>"Post deleted successfully!"]);
    }

    case 'toggle_like':
    case 'toggle_prayer':
    case 'toggle_save': {
        $uid=(int)($data['user_id']??0);$pid=(int)($data['post_id']??0);
        if(!$uid||!$pid) out(["success"=>false,"message"=>"Invalid request."],422);
        $table=$action==='toggle_like'?'post_likes':($action==='toggle_prayer'?'post_prayers':'saved_posts');
        $where=$action==='toggle_save'?'user_id=? AND post_id=?':'post_id=? AND user_id=?';
        $x=stmt($conn,"SELECT id FROM `$table` WHERE $where LIMIT 1","ii",$action==='toggle_save'?[$uid,$pid]:[$pid,$uid]);
        $exists=$x->get_result()->num_rows>0;$x->close();
        if($exists) stmt($conn,"DELETE FROM `$table` WHERE $where","ii",$action==='toggle_save'?[$uid,$pid]:[$pid,$uid])->close();
        else {
            if($action==='toggle_save') stmt($conn,"INSERT INTO saved_posts (user_id,post_id) VALUES (?,?)","ii",[$uid,$pid])->close();
            elseif($action==='toggle_like') stmt($conn,"INSERT INTO post_likes (post_id,user_id) VALUES (?,?)","ii",[$pid,$uid])->close();
            else stmt($conn,"INSERT INTO post_prayers (post_id,user_id) VALUES (?,?)","ii",[$pid,$uid])->close();
        }
        $field=$action==='toggle_like'?'post_likes':($action==='toggle_prayer'?'post_prayers':'saved_posts');
        $cond=$action==='toggle_save'?'user_id=?':'post_id=?';
        $param=$action==='toggle_save'?$uid:$pid;
        $x=stmt($conn,"SELECT COUNT(*) c FROM `$field` WHERE $cond","i",[$param]);$count=(int)$x->get_result()->fetch_assoc()['c'];$x->close();
        out(["success"=>true,"active"=>!$exists,"count"=>$count]);
    }

    case 'get_comments': {
        $pid=(int)q('post_id',0);
        $r=stmt($conn,"SELECT c.id,c.post_id,c.user_id,c.text,c.created_at,u.name AS user_name,u.username,u.avatar FROM comments c JOIN users u ON u.id=c.user_id WHERE c.post_id=? ORDER BY c.created_at ASC","i",[$pid])->get_result();
        $comments=[];while($c=$r->fetch_assoc())$comments[]=$c;
        out(["success"=>true,"comments"=>$comments]);
    }

    case 'add_comment': {
        $uid=(int)($data['user_id']??0);$pid=(int)($data['post_id']??0);$text=cleanText($data['text']??'',3000);
        if(!$uid||!$pid||$text==='') out(["success"=>false,"message"=>"Comment cannot be empty."],422);
        stmt($conn,"INSERT INTO comments (post_id,user_id,text) VALUES (?,?,?)","iis",[$pid,$uid,$text])->close();
        out(["success"=>true,"message"=>"Comment added."]);
    }

    case 'edit_comment': {
        $uid=(int)($data['user_id']??0);$cid=(int)($data['comment_id']??0);$text=cleanText($data['text']??'',3000);
        $x=stmt($conn,"UPDATE comments SET text=? WHERE id=? AND user_id=?","sii",[$text,$cid,$uid]);$a=$x->affected_rows;$x->close();
        if(!$a) out(["success"=>false,"message"=>"Comment not found or permission denied."],403);
        out(["success"=>true,"message"=>"Comment updated."]);
    }

    case 'delete_comment': {
        $uid=(int)($data['user_id']??0);$cid=(int)($data['comment_id']??0);
        $x=stmt($conn,"DELETE FROM comments WHERE id=? AND user_id=?","ii",[$cid,$uid]);$a=$x->affected_rows;$x->close();
        if(!$a) out(["success"=>false,"message"=>"Comment not found or permission denied."],403);
        out(["success"=>true,"message"=>"Comment deleted."]);
    }

    case 'follow':
    case 'unfollow': {
        $uid=(int)($data['user_id']??0);$fid=(int)($data['friend_id']??0);
        if(!$uid||!$fid||$uid===$fid) out(["success"=>false,"message"=>"Invalid connection."],422);
        if($action==='follow') {
            stmt($conn,"INSERT IGNORE INTO user_connections (user_id,friend_id,status) VALUES (?,?, 'connected')","ii",[$uid,$fid])->close();
        } else {
            stmt($conn,"DELETE FROM user_connections WHERE user_id=? AND friend_id=?","ii",[$uid,$fid])->close();
        }
        out(["success"=>true,"following"=>$action==='follow']);
    }

    case 'get_followers':
    case 'get_following': {
        $uid=(int)q('user_id',0);
        if($action==='get_following'){
            $sql="SELECT u.id,u.username,u.name,u.email,u.phone,u.role,u.church,u.avatar,u.bio FROM user_connections c JOIN users u ON u.id=c.friend_id WHERE c.user_id=? ORDER BY u.name";
        } else {
            $sql="SELECT u.id,u.username,u.name,u.email,u.phone,u.role,u.church,u.avatar,u.bio FROM user_connections c JOIN users u ON u.id=c.user_id WHERE c.friend_id=? ORDER BY u.name";
        }
        $r=stmt($conn,$sql,"i",[$uid])->get_result();$list=[];while($u=$r->fetch_assoc())$list[]=$u;
        out(["success"=>true,"users"=>$list]);
    }

    case 'get_ministries': {
        $viewer=(int)q('viewer_id',0);
        $sql="SELECT m.*,u.name leader_name,u.avatar leader_avatar,
             (SELECT COUNT(*) FROM group_members gm WHERE gm.group_id=m.id) members_count
             FROM ministry_groups m JOIN users u ON u.id=m.leader_id ORDER BY m.created_at DESC";
        $r=$conn->query($sql);$groups=[];
        while($g=$r->fetch_assoc()){
            $g['is_member']=false;$g['is_admin']=false;
            if($viewer){
                $x=stmt($conn,"SELECT is_admin FROM group_members WHERE group_id=? AND user_id=? LIMIT 1","ii",[$g['id'],$viewer]);
                $rr=$x->get_result(); if($rr&&$rr->num_rows){$row=$rr->fetch_assoc();$g['is_member']=true;$g['is_admin']=(bool)$row['is_admin'];}$x->close();
            }
            $groups[]=$g;
        }
        out(["success"=>true,"ministries"=>$groups]);
    }

    case 'create_ministry': {
        $uid=(int)($data['user_id']??0);$name=cleanText($data['name']??'',150);$cat=cleanText($data['category']??'General',100);$bio=cleanText($data['bio']??'',4000);
        if(!$uid||$name==='') out(["success"=>false,"message"=>"Group name is required."],422);
        stmt($conn,"INSERT INTO ministry_groups (name,category,leader_id,bio) VALUES (?,?,?,?)","ssis",[$name,$cat,$uid,$bio])->close();
        $gid=$conn->insert_id;
        stmt($conn,"INSERT INTO group_members (group_id,user_id,is_admin) VALUES (?,?,1)","iii",[$gid,$uid])->close();
        out(["success"=>true,"message"=>"Ministry group created!","group_id"=>$gid]);
    }

    case 'join_group':
    case 'leave_group': {
        $uid=(int)($data['user_id']??0);$gid=(int)($data['group_id']??0);
        if($action==='join_group'){
            stmt($conn,"INSERT IGNORE INTO group_members (group_id,user_id,is_admin) VALUES (?,?,0)","iii",[$gid,$uid])->close();
            out(["success"=>true,"message"=>"You joined the group."]);
        }
        stmt($conn,"DELETE FROM group_members WHERE group_id=? AND user_id=?","ii",[$gid,$uid])->close();
        out(["success"=>true,"message"=>"You left the group."]);
    }

    case 'add_group_member': {
        $uid=(int)($data['user_id']??0);$gid=(int)($data['group_id']??0);$member=(int)($data['member_id']??0);
        $x=stmt($conn,"SELECT is_admin FROM group_members WHERE group_id=? AND user_id=? LIMIT 1","ii",[$gid,$uid]);$r=$x->get_result();$admin=$r&&$r->num_rows?(bool)$r->fetch_assoc()['is_admin']:false;$x->close();
        if(!$admin) out(["success"=>false,"message"=>"Only group admins can add members."],403);
        stmt($conn,"INSERT IGNORE INTO group_members (group_id,user_id,is_admin) VALUES (?,?,0)","iii",[$gid,$member])->close();
        out(["success"=>true,"message"=>"Member added to group."]);
    }

    case 'get_group_members': {
        $gid=(int)q('group_id',0);
        $sql="SELECT u.id,u.username,u.name,u.email,u.role,u.church,u.avatar,u.bio,gm.is_admin,gm.joined_at
              FROM group_members gm JOIN users u ON u.id=gm.user_id WHERE gm.group_id=? ORDER BY gm.is_admin DESC,u.name";
        $r=stmt($conn,$sql,"i",[$gid])->get_result();$members=[];while($m=$r->fetch_assoc())$members[]=$m;
        out(["success"=>true,"members"=>$members]);
    }

    case 'get_group_messages': {
        $gid=(int)q('group_id',0);
        $sql="SELECT gm.id,gm.group_id,gm.user_id,gm.text,gm.image,gm.created_at,u.name sender_name,u.username,u.avatar sender_avatar,u.role sender_role
              FROM group_messages gm JOIN users u ON u.id=gm.user_id WHERE gm.group_id=? ORDER BY gm.created_at ASC";
        $r=stmt($conn,$sql,"i",[$gid])->get_result();$msgs=[];while($m=$r->fetch_assoc())$msgs[]=$m;
        out(["success"=>true,"messages"=>$msgs]);
    }

    case 'send_group_message': {
        $uid=(int)($data['user_id']??0);$gid=(int)($data['group_id']??0);$text=cleanText($data['text']??'',10000);$image=cleanText($data['image']??'',2000000);
        if(!$uid||!$gid||($text===''&&$image==='')) out(["success"=>false,"message"=>"Message cannot be empty."],422);
        $x=stmt($conn,"SELECT id FROM group_members WHERE group_id=? AND user_id=?","ii",[$gid,$uid]);$ok=$x->get_result()->num_rows>0;$x->close();
        if(!$ok) out(["success"=>false,"message"=>"Join the group before sending messages."],403);
        stmt($conn,"INSERT INTO group_messages (group_id,user_id,text,image) VALUES (?,?,?,?)","iiss",[$gid,$uid,$text,$image])->close();
        out(["success"=>true,"message"=>"Message sent."]);
    }

    case 'get_direct_messages': {
        $uid=(int)q('user_id',0);$other=(int)q('other_id',0);
        $sql="SELECT dm.*,u.name sender_name,u.username,u.avatar sender_avatar,u.role sender_role
              FROM direct_messages dm JOIN users u ON u.id=dm.sender_id
              WHERE (dm.sender_id=? AND dm.receiver_id=?) OR (dm.sender_id=? AND dm.receiver_id=?)
              ORDER BY dm.created_at ASC";
        $r=stmt($conn,$sql,"iiii",[$uid,$other,$other,$uid])->get_result();$msgs=[];while($m=$r->fetch_assoc())$msgs[]=$m;
        stmt($conn,"UPDATE direct_messages SET is_read=1 WHERE sender_id=? AND receiver_id=?","ii",[$other,$uid])->close();
        out(["success"=>true,"messages"=>$msgs]);
    }

    case 'send_direct_message': {
        $uid=(int)($data['user_id']??0);$other=(int)($data['other_id']??0);$text=cleanText($data['text']??'',10000);$image=cleanText($data['image']??'',2000000);
        if(!$uid||!$other||($text===''&&$image==='')) out(["success"=>false,"message"=>"Message cannot be empty."],422);
        stmt($conn,"INSERT INTO direct_messages (sender_id,receiver_id,text,image) VALUES (?,?,?,?)","iiss",[$uid,$other,$text,$image])->close();
        out(["success"=>true,"message"=>"Message sent."]);
    }

    case 'get_chat_contacts': {
        $uid=(int)q('user_id',0);
        $sql="SELECT u.id,u.username,u.name,u.role,u.church,u.avatar,u.bio,
              (SELECT dm.text FROM direct_messages dm WHERE (dm.sender_id=? AND dm.receiver_id=u.id) OR (dm.sender_id=u.id AND dm.receiver_id=?) ORDER BY dm.created_at DESC LIMIT 1) last_message,
              (SELECT dm.created_at FROM direct_messages dm WHERE (dm.sender_id=? AND dm.receiver_id=u.id) OR (dm.sender_id=u.id AND dm.receiver_id=?) ORDER BY dm.created_at DESC LIMIT 1) last_message_at,
              (SELECT COUNT(*) FROM direct_messages dm WHERE dm.sender_id=u.id AND dm.receiver_id=? AND dm.is_read=0) unread_count
              FROM users u WHERE u.id<>? ORDER BY COALESCE(last_message_at,'1970-01-01') DESC,u.name";
        $r=stmt($conn,$sql,"iiiiii",[$uid,$uid,$uid,$uid,$uid,$uid])->get_result();$users=[];while($u=$r->fetch_assoc())$users[]=$u;
        out(["success"=>true,"contacts"=>$users]);
    }

    case 'create_story': {
        $uid=(int)($data['user_id']??0);$text=cleanText($data['text']??'',3000);$media=cleanText($data['media']??'',2000000);
        if(!$uid||$text==='') out(["success"=>false,"message"=>"Story text is required."],422);
        stmt($conn,"INSERT INTO stories (user_id,text,media) VALUES (?,?,?)","iss",[$uid,$text,$media])->close();
        out(["success"=>true,"message"=>"Story shared."]);
    }

    case 'get_stories': {
        $sql="SELECT s.*,u.name user_name,u.avatar user_avatar FROM stories s JOIN users u ON u.id=s.user_id
              WHERE s.created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR) ORDER BY s.created_at DESC";
        $r=$conn->query($sql);$stories=[];while($s=$r->fetch_assoc())$stories[]=$s;
        out(["success"=>true,"stories"=>$stories]);
    }

    case 'reset_app_data': {
        // Safe reset: only clears browser/session state from the frontend.
        out(["success"=>true,"message"=>"Local app state can be reset safely from the browser."]);
    }

    default:
        out(["success"=>false,"message"=>"Invalid action."],404);
}
?>
