<?php
require_once __DIR__ . '/../functions/security.php';
require_once __DIR__ . '/../config/koneksi.php';

// Proteksi mutlak admin
check_admin();

if (isset($_GET['id'])) {
    $id_hapus = intval($_GET['id']);
    
    // 1. Cari gambar lama untuk dihapus dari folder assets/img
    $stmt_img = mysqli_prepare($conn, "SELECT gambar FROM barang WHERE id_barang = ?");
    mysqli_stmt_bind_param($stmt_img, "i", $id_hapus);
    mysqli_stmt_execute($stmt_img);
    $res_img = mysqli_stmt_get_result($stmt_img);
    
    if ($row = mysqli_fetch_assoc($res_img)) {
        // Hapus file fisik gambar jika eksis dan bukan default
        if (!empty($row['gambar']) && file_exists("../assets/img/" . $row['gambar'])) {
            unlink("../assets/img/" . $row['gambar']);
        }
    }
    mysqli_stmt_close($stmt_img);
    
    // 2. Hapus record dari database
    $stmt_del = mysqli_prepare($conn, "DELETE FROM barang WHERE id_barang = ?");
    mysqli_stmt_bind_param($stmt_del, "i", $id_hapus);
    
    if (mysqli_stmt_execute($stmt_del)) {
        header("Location: barang.php?msg=hapus_sukses");
    } else {
        header("Location: barang.php?msg=hapus_gagal");
    }
    mysqli_stmt_close($stmt_del);
    exit();
} else {
    // Jika tidak ada parameter ID, kembalikan ke halaman barang
    header("Location: barang.php");
    exit();
}
?>