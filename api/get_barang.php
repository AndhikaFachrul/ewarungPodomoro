<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../functions/ratelimit.php';
// Koneksi sudah di-include di dalam ratelimit.php

// Terapkan Rate Limiting: Max 20 request per menit
if (!check_rate_limit('/api/get_barang.php', 20, 60)) {
    http_response_code(429); // HTTP Status 429: Too Many Requests
    echo json_encode(["status" => false, "message" => "Too many requests. Limit tercapai."]);
    exit();
}

// PERUBAHAN: Mengambil data barang beserta harga terendah dari tabel varian_barang
$query = "SELECT b.id_barang, b.nama_barang, b.kategori, b.stok, b.gambar, 
          (SELECT MIN(harga) FROM varian_barang WHERE id_barang = b.id_barang) as harga 
          FROM barang b ORDER BY b.id_barang DESC";
          
$result = mysqli_query($conn, $query);

$data = [];
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        // Jika karena suatu alasan barang belum memiliki varian harga, set ke 0
        $row['harga'] = $row['harga'] ?? 0;
        $data[] = $row;
    }
    echo json_encode([
        "status" => true,
        "message" => "success",
        "data" => $data
    ]);
} else {
    error_log('Get barang query failed: ' . mysqli_error($conn));
    http_response_code(500);
    echo json_encode([
        "status" => false,
        "message" => "Gagal mengambil data barang."
    ]);
}
?>
