<?php
require_once __DIR__ . '/../functions/security.php';
require_once __DIR__ . '/../functions/upload.php';
require_once __DIR__ . '/../config/koneksi.php';
check_admin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_csrf_page();

    $nama = htmlspecialchars(trim($_POST['nama_barang']));
    $kategori = htmlspecialchars(trim($_POST['kategori']));
    $stok = intval($_POST['stok']); // Stok adalah total unit terkecil (misal: 100 bungkus)
    
    // Validasi upload berdasarkan isi file, bukan ekstensi dari pengguna.
    try {
        $gambar = save_product_image($_FILES['gambar'] ?? null, __DIR__ . '/../assets/img');
    } catch (RuntimeException $e) {
        http_response_code(422);
        die(htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8'));
    }

    $gambar = $gambar ?? 'default.jpg';

    // Insert ke tabel barang (Induk)
    $stmt_barang = mysqli_prepare($conn, "INSERT INTO barang (nama_barang, kategori, stok, gambar) VALUES (?, ?, ?, ?)");
    mysqli_stmt_bind_param($stmt_barang, "ssis", $nama, $kategori, $stok, $gambar);
    
    if (mysqli_stmt_execute($stmt_barang)) {
        $id_barang_baru = mysqli_insert_id($conn);

        // Insert ke tabel varian_barang (Anak)
        $satuans = $_POST['satuan'];
        $hargas = $_POST['harga'];
        $isis = $_POST['isi'];

        $stmt_varian = mysqli_prepare($conn, "INSERT INTO varian_barang (id_barang, nama_satuan, harga, isi_per_satuan) VALUES (?, ?, ?, ?)");
        
        for ($i = 0; $i < count($satuans); $i++) {
            $satuan = htmlspecialchars($satuans[$i]);
            $harga = floatval($hargas[$i]);
            $isi = intval($isis[$i]);
            mysqli_stmt_bind_param($stmt_varian, "isdi", $id_barang_baru, $satuan, $harga, $isi);
            mysqli_stmt_execute($stmt_varian);
        }
        header("Location: barang.php");
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Tambah Barang (Varian)</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-5">
    <div class="card shadow-sm col-md-8 mx-auto">
        <div class="card-header bg-primary text-white">Tambah Barang</div>
        <div class="card-body">
            <form action="" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8'); ?>">
                <div class="mb-3">
                    <label>Nama Barang Induk</label>
                    <input type="text" name="nama_barang" class="form-control" required placeholder="Contoh: Indomie Goreng">
                </div>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Kategori</label>
                        <select name="kategori" class="form-select" required>
                            <option value="">-- Pilih Kategori --</option>
                            <option value="Sembako">Sembako</option>
                            <option value="Mie Instan">Mie Instan</option>
                            <option value="Minuman">Minuman</option>
                            <option value="Makanan Ringan">Makanan Ringan</option>
                            <option value="Bumbu Dapur">Bumbu Dapur</option>
                            <option value="Perawatan Diri">Perawatan Diri</option>
                            <option value="Perlengkapan Kebersihan">Perlengkapan Kebersihan</option>
                            <option value="ATK">ATK</option>
                            <option value="Rokok">Rokok</option>
                            <option value="Lainnya">Lainnya</option>
                        </select>
                    </div>
                    <div class="col-md-6"><label>Total Stok (Satuan Terkecil)</label><input type="number" name="stok" class="form-control" required></div>
                </div>
                
                <h5 class="mt-4 border-bottom pb-2">Harga & Satuan (Varian)</h5>
                <div id="varian-container">
                    <div class="row mb-2 varian-row">
                        <div class="col-md-4"><input type="text" name="satuan[]" class="form-control" placeholder="Satuan (Bungkus/Dus)" required></div>
                        <div class="col-md-4"><input type="number" name="harga[]" class="form-control" placeholder="Harga Rp" required></div>
                        <div class="col-md-4"><input type="number" name="isi[]" class="form-control" placeholder="Isi per satuan (Bungkus=1, Dus=40)" required></div>
                    </div>
                </div>
                <button type="button" class="btn btn-sm btn-success mb-3" onclick="tambahVarian()">+ Tambah Varian Lain</button>

                <div class="mb-3">
                    <label>Gambar</label>
                    <input type="file" name="gambar" class="form-control" accept="image/jpeg,image/png,image/webp">
                    <small class="text-muted">Format JPG, PNG, atau WEBP. Maksimal 2 MB.</small>
                </div>
                <button type="submit" class="btn btn-primary w-100">Simpan Barang</button>
            </form>
        </div>
    </div>
</div>
<script>
function tambahVarian() {
    let html = `<div class="row mb-2 varian-row">
        <div class="col-md-4"><input type="text" name="satuan[]" class="form-control" placeholder="Satuan" required></div>
        <div class="col-md-4"><input type="number" name="harga[]" class="form-control" placeholder="Harga Rp" required></div>
        <div class="col-md-4"><input type="number" name="isi[]" class="form-control" placeholder="Isi per satuan" required></div>
    </div>`;
    document.getElementById('varian-container').insertAdjacentHTML('beforeend', html);
}
</script>
</body>
</html>
