<?php
$is_https = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';

function app_base_path()
{
    static $base_path = null;

    if ($base_path !== null) {
        return $base_path;
    }

    $document_root = realpath($_SERVER['DOCUMENT_ROOT'] ?? '');
    $application_root = realpath(__DIR__ . '/..');

    if ($document_root === false || $application_root === false) {
        return $base_path = '';
    }

    $document_root = rtrim(str_replace('\\', '/', $document_root), '/');
    $application_root = rtrim(str_replace('\\', '/', $application_root), '/');

    if (stripos($application_root, $document_root) !== 0) {
        return $base_path = '';
    }

    $relative_path = trim(substr($application_root, strlen($document_root)), '/');
    return $base_path = $relative_path === '' ? '' : '/' . $relative_path;
}

function app_url($path = '')
{
    return app_base_path() . '/' . ltrim($path, '/');
}

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
        header('Location: ' . app_url('login.php?msg=timeout'));
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
        header('Location: ' . app_url('login.php'));
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

function csrf_token()
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

function verify_csrf_token($token = null)
{
    if ($token === null) {
        $token = $_POST['csrf_token'] ?? ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? '');
    }

    return is_string($token)
        && isset($_SESSION['csrf_token'])
        && hash_equals($_SESSION['csrf_token'], $token);
}

function require_csrf_json()
{
    if (!verify_csrf_token()) {
        http_response_code(403);
        echo json_encode([
            'status' => false,
            'message' => 'Permintaan tidak valid. Silakan muat ulang halaman dan coba lagi.',
        ]);
        exit();
    }
}

function require_csrf_page()
{
    if (!verify_csrf_token()) {
        http_response_code(403);
        die('Permintaan tidak valid. Silakan kembali dan muat ulang halaman.');
    }
}
?>
