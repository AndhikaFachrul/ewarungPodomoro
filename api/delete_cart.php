<?php
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/../config/koneksi.php';

// Pastikan method adalah POST dan user sudah login
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['id_user'])) {
    $id_cart = intval($_POST['id_cart']);
    $id_user = $_SESSION['id_user'];

    // Menghapus data keranjang berdasarkan id_cart milik user yang sedang login
    $stmt = mysqli_prepare($conn, "DELETE FROM cart WHERE id_cart = ? AND id_user = ?");
    mysqli_stmt_bind_param($stmt, "ii", $id_cart, $id_user);
    
    if (mysqli_stmt_execute($stmt)) {
        echo json_encode(["status" => true, "message" => "Barang dihapus"]);
    } else {
        echo json_encode(["status" => false, "message" => "Gagal menghapus barang"]);
    }
} else {
    echo json_encode(["status" => false, "message" => "Akses ditolak"]);
}
?>