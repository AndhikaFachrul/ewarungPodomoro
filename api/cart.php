<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../functions/security.php';
require_once __DIR__ . '/../config/koneksi.php';

// Pastikan user sudah login
if (!isset($_SESSION['id_user'])) {
    http_response_code(401);
    echo json_encode(["status" => false, "message" => "Unauthorized"]);
    exit();
}

$id_user = $_SESSION['id_user'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_csrf_json();

    // 1. PERUBAHAN: Menangkap id_varian, bukan id_barang
    $id_varian = intval($_POST['id_varian'] ?? 0);
    $qty = intval($_POST['qty'] ?? 1);

    if ($id_varian === 0 || $qty <= 0) {
        echo json_encode(["status" => false, "message" => "Data tidak valid"]);
        exit();
    }

    // 2. PERUBAHAN: Cek keranjang berdasarkan id_varian
    $cek = mysqli_prepare($conn, "SELECT id_cart, qty FROM cart WHERE id_user = ? AND id_varian = ?");
    mysqli_stmt_bind_param($cek, "ii", $id_user, $id_varian);
    mysqli_stmt_execute($cek);
    $res = mysqli_stmt_get_result($cek);
    
    if ($row = mysqli_fetch_assoc($res)) {
        // Jika sudah ada, update kuantitasnya
        $new_qty = $row['qty'] + $qty;
        $upd = mysqli_prepare($conn, "UPDATE cart SET qty = ? WHERE id_cart = ?");
        mysqli_stmt_bind_param($upd, "ii", $new_qty, $row['id_cart']);
        mysqli_stmt_execute($upd);
    } else {
        // 3. PERUBAHAN: Insert menggunakan id_varian
        $ins = mysqli_prepare($conn, "INSERT INTO cart (id_user, id_varian, qty) VALUES (?, ?, ?)");
        mysqli_stmt_bind_param($ins, "iii", $id_user, $id_varian, $qty);
        mysqli_stmt_execute($ins);
    }
    
    echo json_encode(["status" => true, "message" => "Berhasil ditambahkan ke keranjang"]);

} elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // 4. PERUBAHAN: Query JOIN ke tabel varian_barang untuk mengambil harga dan satuan
    $query = "SELECT c.id_cart, c.id_varian, c.qty, v.nama_satuan, v.harga, b.nama_barang, b.gambar 
              FROM cart c 
              JOIN varian_barang v ON c.id_varian = v.id_varian 
              JOIN barang b ON v.id_barang = b.id_barang 
              WHERE c.id_user = ?";
    
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "i", $id_user);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    $data = [];
    $total_belanja = 0;
    while ($row = mysqli_fetch_assoc($result)) {
        $row['subtotal'] = $row['qty'] * $row['harga'];
        $total_belanja += $row['subtotal'];
        $data[] = $row;
    }

    echo json_encode([
        "status" => true, 
        "data" => $data,
        "total_belanja" => $total_belanja
    ]);
}
?>
