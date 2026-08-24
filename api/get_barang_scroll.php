<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../functions/ratelimit.php';
// Koneksi database sudah di-include di dalam ratelimit.php

// Batasi akses API agar server tidak kelebihan beban saat di-scroll cepat
if (!check_rate_limit('/api/get_barang_scroll.php', 30, 60)) {
    http_response_code(429);
    echo json_encode(["status" => false, "message" => "Too many requests"]);
    exit();
}

// Ambil parameter limit dan offset dari URL (dikirim oleh JavaScript nanti)
$limit = isset($_GET['limit']) ? intval($_GET['limit']) : 8;
$offset = isset($_GET['offset']) ? intval($_GET['offset']) : 0;

// Query mengambil data dengan LIMIT dan OFFSET
$query = "SELECT b.id_barang, b.nama_barang, b.kategori, b.stok, b.gambar, 
          (SELECT MIN(harga) FROM varian_barang WHERE id_barang = b.id_barang) as harga 
          FROM barang b ORDER BY b.id_barang DESC LIMIT ? OFFSET ?";
          
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "ii", $limit, $offset);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$data = [];
while ($row = mysqli_fetch_assoc($result)) {
    $row['harga'] = $row['harga'] ?? 0;
    $data[] = $row;
}

echo json_encode([
    "status" => true,
    "data" => $data,
    // Jika data yang dikembalikan lebih kecil dari limit, berarti data sudah habis
    "has_more" => count($data) === $limit 
]);
?>