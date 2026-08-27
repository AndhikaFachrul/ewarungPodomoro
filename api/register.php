<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../functions/ratelimit.php';

// Rate Limiting: Max 10 request per menit untuk endpoint register
if (!check_rate_limit('/api/register.php', 10, 60, 'token_bucket')) {
    http_response_code(429);
    echo json_encode(["status" => false, "message" => "Too many requests. Silakan coba lagi nanti."]);
    exit();
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = htmlspecialchars(trim($_POST['nama'] ?? ''));
    $email = filter_var(trim($_POST['email'] ?? ''), FILTER_SANITIZE_EMAIL);
    $no_hp = htmlspecialchars(trim($_POST['no_hp'] ?? ''));
    $password = $_POST['password'] ?? '';

    // Validasi input kosong
    if (empty($nama) || empty($email) || empty($no_hp) || empty($password)) {
        echo json_encode(["status" => false, "message" => "Semua field wajib diisi!"]);
        exit();
    }

    // Validasi format email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(["status" => false, "message" => "Format email tidak valid!"]);
        exit();
    }

    // Cek apakah email sudah terdaftar
    $cek_email = mysqli_prepare($conn, "SELECT id_user FROM users WHERE email = ?");
    mysqli_stmt_bind_param($cek_email, "s", $email);
    mysqli_stmt_execute($cek_email);
    mysqli_stmt_store_result($cek_email);

    if (mysqli_stmt_num_rows($cek_email) > 0) {
        echo json_encode(["status" => false, "message" => "Email sudah digunakan!"]);
        exit();
    }

    // Hash password (Penting untuk keamanan)
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    $role = 'user'; // Default role

    // Insert user baru menggunakan Prepared Statement
    $insert = mysqli_prepare($conn, "INSERT INTO users (nama, email, password, no_hp, role) VALUES (?, ?, ?, ?, ?)");
    mysqli_stmt_bind_param($insert, "sssss", $nama, $email, $hashed_password, $no_hp, $role);
    
    if (mysqli_stmt_execute($insert)) {
        echo json_encode(["status" => true, "message" => "Registrasi berhasil! Silakan login."]);
    } else {
        echo json_encode(["status" => false, "message" => "Terjadi kesalahan server."]);
    }
} else {
    echo json_encode(["status" => false, "message" => "Method not allowed"]);
}
?>