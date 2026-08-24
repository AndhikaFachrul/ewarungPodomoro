<?php
require_once __DIR__ . '/../functions/security.php';
require_once __DIR__ . '/../config/koneksi.php';

check_login();
$id_user = $_SESSION['id_user'];

// Ambil total keranjang
$query = "SELECT SUM(c.qty * b.harga) as total_belanja FROM cart c JOIN barang b ON c.id_barang = b.id_barang WHERE c.id_user = ?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "i", $id_user);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$row = mysqli_fetch_assoc($result);
$total_belanja = $row['total_belanja'] ?? 0;

if ($total_belanja == 0) {
    header("Location: cart.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Checkout - Podomoro</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<nav class="navbar navbar-expand-lg navbar-light bg-white py-3 shadow-sm mb-4">
    <div class="container">
        <a class="navbar-brand fw-bold" style="color: #122b4f;" href="../index.php">🛍️ TOKO PODOMORO</a>
        <a href="cart.php" class="btn btn-outline-secondary">Kembali ke Keranjang</a>
    </div>
</nav>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow-sm">
                <div class="card-header text-white" style="background-color: #122b4f;">
                    <h5 class="mb-0">Detail Pengiriman & Pembayaran</h5>
                </div>
                <div class="card-body">
                    <h4 class="mb-3">Total Tagihan: <span class="text-danger fw-bold">Rp <?php echo number_format($total_belanja, 0, ',', '.'); ?></span></h4>
                    
                    <form id="formFinalCheckout">
                        <div class="mb-3">
                            <label class="form-label">Nama Lengkap</label>
                            <input type="text" class="form-control" value="<?php echo htmlspecialchars($_SESSION['nama']); ?>" readonly>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Alamat Lengkap Pengiriman</label>
                            <textarea name="alamat" id="alamat" class="form-control" rows="3" placeholder="Contoh: Jl. Citatah No. 10, RT 01/02, Bandung Barat" required></textarea>
                        </div>
                        <div class="alert alert-info">
                            <small>Setelah klik tombol di bawah, pesanan akan dicatat di sistem dan Anda akan diarahkan ke WhatsApp Admin Toko Podomoro untuk proses pembayaran.</small>
                        </div>
                        <button type="submit" class="btn btn-success w-100 fw-bold fs-5" id="btnProses">PESAN VIA WHATSAPP</button>
                    </form>
                    <div id="alert-msg" class="alert mt-3 d-none"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('formFinalCheckout').addEventListener('submit', function(e) {
    e.preventDefault();
    let btn = document.getElementById('btnProses');
    let alamat = document.getElementById('alamat').value;
    
    btn.disabled = true;
    btn.innerText = "Memproses...";

    // Kirim alamat ke API Checkout menggunakan FormData
    let formData = new FormData();
    formData.append('alamat', alamat);

    fetch('../api/checkout.php', { 
        method: 'POST',
        body: formData
    })
    .then(response => {
        if (response.status === 429) {
            throw new Error("Terlalu banyak permintaan (Rate Limit). Tunggu 1 menit.");
        }
        return response.json();
    })
    .then(res => {
        if(res.status && res.redirect_url) {
            window.location.href = res.redirect_url;
        } else {
            showAlert('danger', res.message);
            btn.disabled = false;
            btn.innerText = "PESAN VIA WHATSAPP";
        }
    })
    .catch(error => {
        showAlert('danger', error.message);
        btn.disabled = false;
        btn.innerText = "PESAN VIA WHATSAPP";
    });
});

function showAlert(type, message) {
    let alertDiv = document.getElementById('alert-msg');
    alertDiv.className = `alert alert-${type} mt-3`;
    alertDiv.innerHTML = message;
    alertDiv.classList.remove('d-none');
}
</script>
</body>
</html>