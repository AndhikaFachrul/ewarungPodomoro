<?php
require_once __DIR__ . '/../functions/security.php';
header('Content-Type: application/json');
require_once __DIR__ . '/../config/koneksi.php';

// Pastikan method adalah POST dan user sudah login
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['id_user'])) {
    $id_cart = intval($_POST['id_cart']);
    $qty = intval($_POST['qty']);
    $id_user = $_SESSION['id_user'];

    if ($qty > 0) {
        $stmt = mysqli_prepare($conn, "UPDATE cart SET qty = ? WHERE id_cart = ? AND id_user = ?");
        mysqli_stmt_bind_param($stmt, "iii", $qty, $id_cart, $id_user);
        
        if (mysqli_stmt_execute($stmt)) {
            echo json_encode(["status" => true, "message" => "Kuantitas diperbarui"]);
        } else {
            echo json_encode(["status" => false, "message" => "Gagal memperbarui kuantitas"]);
        }
    } else {
        echo json_encode(["status" => false, "message" => "Kuantitas tidak valid"]);
    }
} else {
    echo json_encode(["status" => false, "message" => "Akses ditolak"]);
}
?>
