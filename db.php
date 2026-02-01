<?php
/**
 * DATABASE CONNECTION & SECURITY CONFIGURATION
 * ปรับปรุงสำหรับ Render + TiDB Cloud (SSL Enforcement)
 */

// -------------------------------------------------------------------------
// 🕵️ STEALTH FUNCTION: ฟังก์ชันส่งหน้า 404 เพื่อตบตาแฮกเกอร์
// -------------------------------------------------------------------------
function trigger404() {
    header("HTTP/1.1 404 Not Found");
    if (file_exists(__DIR__ . '/404.php')) {
        include(__DIR__ . '/404.php');
    } else {
        echo '<!DOCTYPE HTML PUBLIC "-//IETF//DTD HTML 2.0//EN">
        <html><head><title>404 Not Found</title></head><body>
        <h1>Not Found</h1>
        <p>The requested URL was not found on this server.</p>
        <hr><address>Apache Server at ' . $_SERVER['HTTP_HOST'] . ' Port 80</address>
        </body></html>';
    }
    exit;
}

// ป้องกันการเข้าถึงไฟล์ db.php โดยตรงผ่าน URL
if (basename($_SERVER['PHP_SELF']) == basename(__FILE__)) {
    trigger404();
}

// -------------------------------------------------------------------------
// 🛡️ SECURITY HEADERS & SESSION
// -------------------------------------------------------------------------
header("X-Frame-Options: SAMEORIGIN");
header("X-Content-Type-Options: nosniff");
header("Content-Security-Policy: default-src 'self' https: data: 'unsafe-inline' 'unsafe-eval'; script-src 'self' https://cdn.tailwindcss.com https://cdn.jsdelivr.net 'unsafe-inline' 'unsafe-eval'; style-src 'self' https: 'unsafe-inline'; font-src 'self' https: data:;");

ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
if (getenv('RENDER')) { // ตรวจสอบว่ารันบน Render หรือไม่
    ini_set('session.cookie_secure', 1); 
}

ob_start(); 
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
date_default_timezone_set("Asia/Bangkok");

// -------------------------------------------------------------------------
// 🛡️ CSRF PROTECTION
// -------------------------------------------------------------------------
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// -------------------------------------------------------------------------
// 🔌 DATABASE CONNECTION (PDO) - บังคับ SSL สำหรับ TiDB Cloud
// -------------------------------------------------------------------------
$host     = getenv('DB_HOST')     ?: 'localhost';
$dbname   = getenv('DB_NAME')     ?: 'test1';
$username = getenv('DB_USER')     ?: 'root';
$password = getenv('DB_PASS')     ?: '';
$port     = getenv('DB_PORT')     ?: '3306'; // TiDB มักจะใช้ 4000

try {
    $dsn = "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4";
    
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ,
        // สำคัญมากสำหรับ TiDB: บังคับใช้ SSL ทันทีที่เชื่อมต่อ
        PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8mb4',
    ];

    // ตรวจสอบและบังคับใช้ SSL ถ้าไม่ได้รันบน localhost
    if ($host !== 'localhost' && $host !== '127.0.0.1') {
        $options[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = false;
        // บรรทัดนี้จะบังคับให้ Driver ใช้ SSL Mode ในการเชื่อมต่อ
        if (defined('PDO::MYSQL_ATTR_SSL_CA')) {
            $options[PDO::MYSQL_ATTR_SSL_CA] = true;
        }
    }

    $pdo = new PDO($dsn, $username, $password, $options);
    
} catch(PDOException $e) {
    // บันทึก Error ลง Log เพื่อตรวจสอบบน Dashboard ของ Render
    error_log("Database Connection Error: " . $e->getMessage()); 
    die("<h1>Service Unavailable</h1><p>The server is temporarily unable to service your request.</p>");
}

// -------------------------------------------------------------------------
// ⚙️ FETCH SITE CONFIGURATION
// -------------------------------------------------------------------------
try {
    $stmt = $pdo->prepare("SELECT * FROM settings LIMIT 1");
    $stmt->execute();
    $web_config = $stmt->fetch();
} catch (Exception $e) {
    $web_config = null;
}

if (!$web_config) {
    $web_config = (object)[
        'site_name' => 'My Shop',
        'site_color' => 'purple',
        'site_logo' => '',
        'marquee_text' => '',
        'background_img' => ''
    ];
}

// 👮 AUTHENTICATION FUNCTIONS
function checkLogin() {
    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit;
    }
}

function checkAdmin($pdo) {
    if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
        trigger404();
    }
}
?>
