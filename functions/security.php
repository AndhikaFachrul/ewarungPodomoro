<?php
$is_https = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';

// Security headers yang aman untuk localhost maupun production.
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: SAMEORIGIN');
header('Referrer-Policy: strict-origin-when-cross-origin');
header('Permissions-Policy: geolocation=(), camera=(), microphone=()');

if (session_status() !== PHP_SESSION_ACTIVE) {
    ini_set('session.use_strict_mode', '1');
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'domain' => '',
        'secure' => $is_https,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

// Timeout dihitung dari aktivitas terakhir, bukan sejak pengguna membuka situs.
if (isset($_SESSION['id_user'])) {
    if (isset($_SESSION['LAST_ACTIVITY']) && time() - $_SESSION['LAST_ACTIVITY'] > 1800) {
        $_SESSION = [];
        session_destroy();
        header('Location: /ewarung/login.php?msg=timeout');
        exit();
    }

    $_SESSION['LAST_ACTIVITY'] = time();

    // Regenerate ID setiap 5 menit untuk mengurangi risiko session hijacking.
    if (!isset($_SESSION['LAST_REGENERATED']) || time() - $_SESSION['LAST_REGENERATED'] > 300) {
        session_regenerate_id(true);
        $_SESSION['LAST_REGENERATED'] = time();
    }
}

function check_login()
{
    if (!isset($_SESSION['id_user'])) {
        header("Location: /ewarung/login.php");
        exit();
    }
}

function check_admin()
{
    check_login();
    if ($_SESSION['role'] !== 'admin') {
        http_response_code(403);
        die("Akses ditolak.");
    }
}
?>
