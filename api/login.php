<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../functions/security.php';
require_once __DIR__ . '/../functions/ratelimit.php';

// 1. Terapkan Rate Limiting: Max 20 request per menit
if (!check_rate_limit('/api/login.php', 20, 60, 'token_bucket')) {
    http_response_code(429);
    echo json_encode(["status" => false, "message" => "Too many requests. Silakan coba lagi nanti."]);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = htmlspecialchars(trim($_POST['email'] ?? ''));
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        echo json_encode(["status" => false, "message" => "Email dan password wajib diisi!"]);
        exit();
    }

    $stmt = mysqli_prepare($conn, "SELECT id_user, nama, password, role FROM users WHERE email = ?");
    mysqli_stmt_bind_param($stmt, "s", $email);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $user = mysqli_fetch_assoc($result);

    if ($user && password_verify($password, $user['password'])) {
        session_regenerate_id(true);

        // Set Session
        $_SESSION['id_user'] = $user['id_user'];
        $_SESSION['nama'] = $user['nama'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['LAST_ACTIVITY'] = time();
        $_SESSION['LAST_REGENERATED'] = time();
        
        echo json_encode([
            "status" => true, 
            "message" => "success", 
            "data" => ["role" => $user['role']]
        ]);
    } else {
        echo json_encode(["status" => false, "message" => "Email atau password salah!"]);
    }
} else {
    echo json_encode(["status" => false, "message" => "Method not allowed"]);
}
?>
