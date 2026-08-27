<?php
require_once __DIR__ . '/functions/security.php';
require_once __DIR__ . '/config/koneksi.php';

$id_barang = isset($_GET['id']) ? intval($_GET['id']) : 0;

// 1. Ambil data barang induk (tanpa harga, karena harga ada di varian)
$stmt = mysqli_prepare($conn, "SELECT * FROM barang WHERE id_barang = ?");
mysqli_stmt_bind_param($stmt, "i", $id_barang);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$barang = mysqli_fetch_assoc($result);

if (!$barang) {
    die("Barang tidak ditemukan.");
}

// 2. TAMBAHAN: Ambil data varian (satuan dan harga) untuk barang ini
$stmt_var = mysqli_prepare($conn, "SELECT * FROM varian_barang WHERE id_barang = ?");
mysqli_stmt_bind_param($stmt_var, "i", $id_barang);
mysqli_stmt_execute($stmt_var);
$res_var = mysqli_stmt_get_result($stmt_var);

// Simpan data varian ke dalam array
$varians = [];
while($row = mysqli_fetch_assoc($res_var)) {
    $varians[] = $row;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail <?php echo htmlspecialchars($barang['nama_barang']); ?> - Podomoro</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<nav class="navbar navbar-expand-lg navbar-light bg-white py-3 shadow-sm mb-5">
    <div class="container">
        <a class="navbar-brand fw-bold" style="color: #122b4f;" href="index.php">🛍️ TOKO PODOMORO</a>
        <a href="index.php" class="btn btn-outline-secondary">Kembali</a>
    </div>
</nav>

<div class="container">
    <div class="row bg-white p-4 shadow-sm rounded">
        <div class="col-md-5 text-center">
            <img src="assets/img/<?php echo htmlspecialchars($barang['gambar']); ?>" class="img-fluid rounded" alt="<?php echo htmlspecialchars($barang['nama_barang']); ?>" style="max-height: 400px;">
        </div>
        <div class="col-md-7">
            <span class="badge bg-secondary mb-2"><?php echo htmlspecialchars($barang['kategori']); ?></span>
            <h2 class="fw-bold"><?php echo htmlspecialchars($barang['nama_barang']); ?></h2>
            
            <p class="mt-3">Total Stok Fisik: <strong><?php echo $barang['stok']; ?></strong> unit terkecil</p>
            
            <hr>
            
            <?php if(isset($_SESSION['id_user'])): ?>
                <form id="formCart" class="mt-4">
                    <div class="mb-3">
                        <label class="fw-bold mb-2">Pilih Satuan & Harga:</label>
                        <select name="id_varian" class="form-select" required>
                            <option value="">-- Pilih Satuan --</option>
                            <?php foreach($varians as $v): ?>
                                <option value="<?php echo $v['id_varian']; ?>">
                                    <?php echo htmlspecialchars($v['nama_satuan']); ?> - Rp <?php echo number_format($v['harga'],0,',','.'); ?> 
                                    (Tarik Stok: <?php echo $v['isi_per_satuan']; ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="d-flex align-items-center">
                        <input type="number" name="qty" class="form-control me-3" value="1" min="1" style="width: 100px;" required>
                        <button type="submit" class="btn btn-danger px-4 fw-bold" id="btnCart">+ KERANJANG</button>
                    </div>
                </form>
                <div id="alert-msg" class="alert mt-3 d-none"></div>
            <?php else: ?>
                <div class="alert alert-warning mt-4">
                    Silakan <a href="login.php" class="alert-link">Login</a> untuk menambahkan barang ke keranjang.
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
<?php if(isset($_SESSION['id_user'])): ?>
// Script JS sama sekali tidak perlu diubah, karena FormData akan otomatis 
// mengambil nilai dari tag <select name="id_varian"> yang baru kita buat.
document.getElementById('formCart').addEventListener('submit', function(e) {
    e.preventDefault();
    let btn = document.getElementById('btnCart');
    let alertMsg = document.getElementById('alert-msg');
    let formData = new FormData(this);

    btn.disabled = true;
    btn.innerHTML = 'Memproses...';

    fetch('api/cart.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(res => {
        alertMsg.className = `alert alert-${res.status ? 'success' : 'danger'} mt-3`;
        alertMsg.innerHTML = res.message;
        alertMsg.classList.remove('d-none');
        btn.disabled = false;
        btn.innerHTML = '+ KERANJANG';
    })
    .catch(err => {
        alertMsg.className = 'alert alert-danger mt-3';
        alertMsg.innerHTML = 'Terjadi kesalahan sistem.';
        alertMsg.classList.remove('d-none');
        btn.disabled = false;
        btn.innerHTML = '+ KERANJANG';
    });
});
<?php endif; ?>
</script>
</body>
</html>
