<?php
$db_host = getenv('DB_HOST') !== false ? getenv('DB_HOST') : 'localhost';
$db_port = getenv('DB_PORT') !== false ? (int) getenv('DB_PORT') : 3306;
$db_name = getenv('DB_NAME') !== false ? getenv('DB_NAME') : 'ewarung_podomoro';
$db_user = getenv('DB_USER') !== false ? getenv('DB_USER') : 'root';
$db_password = getenv('DB_PASSWORD') !== false ? getenv('DB_PASSWORD') : '';

try {
    $conn = mysqli_connect($db_host, $db_user, $db_password, $db_name, $db_port);

    if ($conn === false) {
        throw new RuntimeException(mysqli_connect_error());
    }

    mysqli_set_charset($conn, 'utf8mb4');
} catch (Throwable $e) {
    error_log('Database connection failed: ' . $e->getMessage());
    http_response_code(500);
    die(json_encode([
        'status' => false,
        'message' => 'Koneksi database gagal. Silakan coba kembali nanti.',
    ]));
}
