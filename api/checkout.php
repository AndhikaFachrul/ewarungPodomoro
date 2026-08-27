header('Content-Type: application/json');
require_once __DIR__ . '/../functions/security.php';
require_once __DIR__ . '/../config/koneksi.php';
require_once __DIR__ . '/../functions/ratelimit.php';

class CheckoutException extends Exception
{
}

if (!isset($_SESSION['id_user'])) {
    echo json_encode(["status" => false, "message" => "Harap login terlebih dahulu"]);
    exit();
}

if (!check_rate_limit('/api/checkout.php', 10, 60)) {
    http_response_code(429);
    echo json_encode(["status" => false, "message" => "Terlalu banyak permintaan. Silakan tunggu sebentar."]);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_user = $_SESSION['id_user'];

    mysqli_begin_transaction($conn);

    try {
        // Lock baris keranjang dan barang sampai transaksi selesai agar stok aman
        // ketika beberapa pengguna checkout pada waktu yang bersamaan.
        $query_cart = "SELECT c.id_cart, c.id_varian, c.qty, v.nama_satuan, v.harga, v.isi_per_satuan,
                              b.id_barang, b.nama_barang, b.stok
                       FROM cart c
                       JOIN varian_barang v ON c.id_varian = v.id_varian
                       JOIN barang b ON v.id_barang = b.id_barang
                       WHERE c.id_user = ?
                       FOR UPDATE";

        $stmt_cart = mysqli_prepare($conn, $query_cart);
        mysqli_stmt_bind_param($stmt_cart, "i", $id_user);
        mysqli_stmt_execute($stmt_cart);
        $result_cart = mysqli_stmt_get_result($stmt_cart);

        $cart_items = [];
        $total_harga = 0;

        while ($row = mysqli_fetch_assoc($result_cart)) {
            $cart_items[] = $row;
            $total_harga += ($row['harga'] * $row['qty']);
        }

        if (empty($cart_items)) {
            throw new CheckoutException("Keranjang belanja kosong!");
        }

        foreach ($cart_items as $item) {
            $pengurangan_stok = $item['qty'] * $item['isi_per_satuan'];

            if ($item['stok'] < $pengurangan_stok) {
                throw new CheckoutException(
                    "Stok " . $item['nama_barang'] . " (" . $item['nama_satuan'] . ") tidak mencukupi!"
                );
            }
        }

        //Insert ke tabel transaksi induk
        $stmt_trx = mysqli_prepare($conn, "INSERT INTO transaksi (id_user, total_harga, status) VALUES (?, ?, 'pending')");
        mysqli_stmt_bind_param($stmt_trx, "id", $id_user, $total_harga);
        mysqli_stmt_execute($stmt_trx);
        $id_transaksi = mysqli_insert_id($conn);

        // Siapkan Teks untuk WhatsApp (Gunakan \n untuk baris baru standar)
        $pesanan_teks = "Halo Bu, saya ingin belanja:\n\n";
        $no = 1;

        // Siapkan Query untuk Insert Detail dan Update Stok
        $stmt_dtl = mysqli_prepare($conn, "INSERT INTO detail_transaksi (id_transaksi, id_varian, jumlah, subtotal) VALUES (?, ?, ?, ?)");
        $stmt_stok = mysqli_prepare(
            $conn,
            "UPDATE barang SET stok = stok - ? WHERE id_barang = ? AND stok >= ?"
        );

        // Looping setiap barang untuk masuk ke detail_transaksi dan potong stok
        foreach ($cart_items as $item) {
            $subtotal = $item['harga'] * $item['qty'];
            $pengurangan_stok = $item['qty'] * $item['isi_per_satuan'];

            // Insert detail
            mysqli_stmt_bind_param($stmt_dtl, "iiid", $id_transaksi, $item['id_varian'], $item['qty'], $subtotal);
            mysqli_stmt_execute($stmt_dtl);

            // Potong stok
            mysqli_stmt_bind_param(
                $stmt_stok,
                "iii",
                $pengurangan_stok,
                $item['id_barang'],
                $pengurangan_stok
            );
            mysqli_stmt_execute($stmt_stok);

            if (mysqli_stmt_affected_rows($stmt_stok) !== 1) {
                throw new CheckoutException(
                    "Stok " . $item['nama_barang'] . " (" . $item['nama_satuan'] . ") tidak mencukupi!"
                );
            }

            // Format Teks WA (Menggunakan \n)
            $pesanan_teks .= $no . ". " . $item['nama_barang'] . " (" . $item['nama_satuan'] . ") - " . $item['qty'] . "x = Rp " . number_format($subtotal, 0, ',', '.') . "\n";
            $no++;
        }

        $pesanan_teks .= "\n*Total Keseluruhan: Rp " . number_format($total_harga, 0, ',', '.') . "*\n\n";
        $pesanan_teks .= "Mohon segera diproses ya, terima kasih.";

        // Hapus isi keranjang user setelah berhasil
        $stmt_del = mysqli_prepare($conn, "DELETE FROM cart WHERE id_user = ?");
        mysqli_stmt_bind_param($stmt_del, "i", $id_user);
        mysqli_stmt_execute($stmt_del);

        // Jika semua query di atas berhasil, simpan permanen ke database
        mysqli_commit($conn);

        //Buat URL WhatsApp (Gunakan wa.me dan urlencode)
        $no_wa = "6283833933667"; // Ganti dengan nomor asli Admin (Harus diawali 62)
        
        // urlencode() akan secara otomatis mengubah spasi dan enter menjadi format link yang sah
        $wa_url = "https://wa.me/" . $no_wa . "?text=" . urlencode($pesanan_teks);

        echo json_encode([
            "status" => true,
            "message" => "Checkout berhasil!",
            "wa_url" => $wa_url
        ]);

    } catch (CheckoutException $e) {
        mysqli_rollback($conn);
        echo json_encode(["status" => false, "message" => $e->getMessage()]);
    } catch (Throwable $e) {
        // Pesan error database tidak ditampilkan kepada pengguna.
        mysqli_rollback($conn);
        http_response_code(500);
        echo json_encode(["status" => false, "message" => "Terjadi kesalahan sistem saat memproses checkout."]);
    }
} else {
    http_response_code(405);
    echo json_encode(["status" => false, "message" => "Metode tidak diizinkan"]);
}
?>
