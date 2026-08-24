<?php
// Tampilkan error (hanya untuk proses development/debugging)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json');

// Memanggil koneksi database
require_once __DIR__ . '/../config/koneksi.php';

// Mendeteksi dan mengaktifkan modul rate limiting
$rate_limit_path = __DIR__ . '/../functions/ratelimit.php';

if (file_exists($rate_limit_path)) {
    require_once $rate_limit_path;
    $ip_address = $_SERVER['REMOTE_ADDR'];
    // Validasi batasan request
    $is_allowed = check_rate_limit($ip_address, 'search_endpoint');

    if (!$is_allowed) {
        http_response_code(429);
        echo json_encode([
            'status' => 'error', 
            'message' => 'Permintaan terlalu cepat. Modul rate limiting aktif.'
        ]);
        exit;
    }
} else {
    // Memberikan respons terstruktur jika file rate_limit tidak ditemukan
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'File rate limiting tidak ditemukan di path: ' . $rate_limit_path]);
    exit;
}

// Menangkap kata kunci pencarian
$keyword = isset($_GET['q']) ? trim($_GET['q']) : '';

// Jika kolom pencarian kosong, kembalikan array kosong
if (empty($keyword)) {
    echo json_encode(['status' => 'success', 'data' => []]);
    exit;
}

// Mengamankan string untuk mencegah SQL Injection
$keyword_safe = mysqli_real_escape_string($conn, $keyword);

// Query JOIN untuk mengambil harga termurah dari varian barang
$query = "
    SELECT 
        b.id_barang, 
        b.nama_barang, 
        MIN(v.harga) as harga 
    FROM barang b
    JOIN varian_barang v ON b.id_barang = v.id_barang
    WHERE b.nama_barang LIKE '%$keyword_safe%'
    GROUP BY b.id_barang, b.nama_barang
    LIMIT 10
";

$result = mysqli_query($conn, $query);

// Menangkap dan menampilkan pesan jika terjadi error pada database
if (!$result) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error', 
        'message' => 'Terjadi kesalahan pada query database: ' . mysqli_error($conn)
    ]);
    exit;
}

// Memasukkan hasil pencarian ke dalam array
$results = [];
while ($row = mysqli_fetch_assoc($result)) {
    $results[] = $row;
}

// Mengirim hasil akhir dalam format JSON
echo json_encode(['status' => 'success', 'data' => $results]);
?>