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
    $email = strtolower(trim($_POST['email'] ?? ''));
    $no_hp_input = trim($_POST['no_hp'] ?? '');
    $password = $_POST['password'] ?? '';

    // Validasi input kosong
    if (empty($nama) || empty($email) || empty($no_hp_input) || empty($password)) {
        echo json_encode(["status" => false, "message" => "Semua field wajib diisi!"]);
        exit();
    }

    // Validasi dan normalisasi email
    if (strlen($email) > 100 || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(["status" => false, "message" => "Format email tidak valid!"]);
        exit();
    }

    // Normalisasi nomor WhatsApp ke format internasional 628...
    $no_hp = preg_replace('/[\\s\\-()]/', '', $no_hp_input);

    if (strpos($no_hp, '+62') === 0) {
        $no_hp = substr($no_hp, 1);
    } elseif (strpos($no_hp, '08') === 0) {
        $no_hp = '62' . substr($no_hp, 1);
    }

    // Nomor seluler Indonesia: 628 diikuti 8-11 digit
    if (!preg_match('/^628[1-9][0-9]{7,10}$/', $no_hp)) {
        echo json_encode([
            "status" => false,
            "message" => "Nomor WhatsApp tidak valid. Gunakan format 08..., 628..., atau +628..."
        ]);
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