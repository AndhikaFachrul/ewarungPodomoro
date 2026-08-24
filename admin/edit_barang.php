<?php
require_once __DIR__ . '/../functions/security.php';
require_once __DIR__ . '/../config/koneksi.php';
check_admin();

$id_barang = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Ambil data barang induk
$stmt_get = mysqli_prepare($conn, "SELECT * FROM barang WHERE id_barang = ?");
mysqli_stmt_bind_param($stmt_get, "i", $id_barang);
mysqli_stmt_execute($stmt_get);
$result = mysqli_stmt_get_result($stmt_get);
$barang = mysqli_fetch_assoc($result);

if (!$barang) { die("Data barang tidak ditemukan."); }

// Ambil data varian harga
$stmt_var = mysqli_prepare($conn, "SELECT * FROM varian_barang WHERE id_barang = ?");
mysqli_stmt_bind_param($stmt_var, "i", $id_barang);
mysqli_stmt_execute($stmt_var);
$result_var = mysqli_stmt_get_result($stmt_var);
$varians = [];
while($v = mysqli_fetch_assoc($result_var)) { $varians[] = $v; }

// PROSES UPDATE DATA
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = htmlspecialchars(trim($_POST['nama_barang']));
    $kategori = htmlspecialchars(trim($_POST['kategori']));
    $stok = intval($_POST['stok']);
    $gambar_lama = $_POST['gambar_lama'];
    
    $gambar_baru = $gambar_lama; 
    if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] === 0) {
        $file_ext = strtolower(pathinfo($_FILES['gambar']['name'], PATHINFO_EXTENSION));
        $gambar_baru = time() . "_" . uniqid() . "." . $file_ext;
        move_uploaded_file($_FILES['gambar']['tmp_name'], "../assets/img/" . $gambar_baru);
        if ($gambar_lama && file_exists("../assets/img/" . $gambar_lama)) {
            unlink("../assets/img/" . $gambar_lama);
        }
    }

    // 1. Update Tabel Induk
    $stmt_upd = mysqli_prepare($conn, "UPDATE barang SET nama_barang=?, kategori=?, stok=?, gambar=? WHERE id_barang=?");
    mysqli_stmt_bind_param($stmt_upd, "ssisi", $nama, $kategori, $stok, $gambar_baru, $id_barang);
    mysqli_stmt_execute($stmt_upd);

    // 2. Update Tabel Varian
    $id_varians = $_POST['id_varian'] ?? [];
    $satuans = $_POST['satuan'] ?? [];
    $hargas = $_POST['harga'] ?? [];
    $isis = $_POST['isi'] ?? [];

    // Hapus varian yang dihapus oleh admin di UI
    $submitted_ids = array_filter($id_varians, function($val) { return intval($val) > 0; });
    if (!empty($submitted_ids)) {
        $ids_str = implode(',', $submitted_ids);
        mysqli_query($conn, "DELETE FROM varian_barang WHERE id_barang = $id_barang AND id_varian NOT IN ($ids_str)");
    }

    // Loop untuk Update atau Insert varian baru
    for ($i = 0; $i < count($satuans); $i++) {
        $id_v = intval($id_varians[$i]);
        $satuan = htmlspecialchars($satuans[$i]);
        $harga = floatval($hargas[$i]);
        $isi = intval($isis[$i]);

        if ($id_v > 0) {
            // Update varian lama
            $stmt_v = mysqli_prepare($conn, "UPDATE varian_barang SET nama_satuan=?, harga=?, isi_per_satuan=? WHERE id_varian=?");
            mysqli_stmt_bind_param($stmt_v, "sdii", $satuan, $harga, $isi, $id_v);
            mysqli_stmt_execute($stmt_v);
        } else {
            // Insert varian baru jika admin menekan tombol "+ Tambah Varian"
            $stmt_v = mysqli_prepare($conn, "INSERT INTO varian_barang (id_barang, nama_satuan, harga, isi_per_satuan) VALUES (?, ?, ?, ?)");
            mysqli_stmt_bind_param($stmt_v, "isdi", $id_barang, $satuan, $harga, $isi);
            mysqli_stmt_execute($stmt_v);
        }
    }
    
    header("Location: barang.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Edit Barang</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-5">
    <div class="card shadow-sm col-md-8 mx-auto mb-5">
        <div class="card-header bg-warning text-dark fw-bold">Edit Data Barang</div>
        <div class="card-body">
            <form action="" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="gambar_lama" value="<?php echo htmlspecialchars($barang['gambar']); ?>">
                
                <div class="mb-3">
                    <label>Nama Barang Induk</label>
                    <input type="text" name="nama_barang" class="form-control" value="<?php echo htmlspecialchars($barang['nama_barang']); ?>" required>
                </div>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label>Kategori</label>
                        <select name="kategori" class="form-select" required>
                            <option value="">-- Pilih Kategori --</option>
                            <option value="Sembako" <?php echo ($barang['kategori'] == 'Sembako') ? 'selected' : ''; ?>>Sembako</option>
                            <option value="Mie Instan" <?php echo ($barang['kategori'] == 'Mie Instan') ? 'selected' : ''; ?>>Mie Instan</option>
                            <option value="Minuman" <?php echo ($barang['kategori'] == 'Minuman') ? 'selected' : ''; ?>>Minuman</option>
                            <option value="Makanan Ringan" <?php echo ($barang['kategori'] == 'Makanan Ringan') ? 'selected' : ''; ?>>Makanan Ringan</option>
                            <option value="Bumbu Dapur" <?php echo ($barang['kategori'] == 'Bumbu Dapur') ? 'selected' : ''; ?>>Bumbu Dapur</option>
                            <option value="Perawatan Diri" <?php echo ($barang['kategori'] == 'Perawatan Diri') ? 'selected' : ''; ?>>Perawatan Diri</option>
                            <option value="Perlengkapan Kebersihan" <?php echo ($barang['kategori'] == 'Perlengkapan Kebersihan') ? 'selected' : ''; ?>>Perlengkapan Kebersihan</option>
                            <option value="ATK" <?php echo ($barang['kategori'] == 'ATK') ? 'selected' : ''; ?>>ATK</option>
                            <option value="Rokok" <?php echo ($barang['kategori'] == 'Rokok') ? 'selected' : ''; ?>>Rokok</option>
                            <option value="Lainnya" <?php echo ($barang['kategori'] == 'Lainnya') ? 'selected' : ''; ?>>Lainnya</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label>Total Stok (Satuan Terkecil)</label>
                        <input type="number" name="stok" class="form-control" value="<?php echo $barang['stok']; ?>" required>
                    </div>
                </div>

                <h5 class="mt-4 border-bottom pb-2">Harga & Satuan (Varian)</h5>
                <div id="varian-container">
                    <?php foreach($varians as $v): ?>
                    <div class="row mb-2 varian-row align-items-center">
                        <input type="hidden" name="id_varian[]" value="<?php echo $v['id_varian']; ?>">
                        <div class="col-md-3"><input type="text" name="satuan[]" class="form-control" value="<?php echo htmlspecialchars($v['nama_satuan']); ?>" required></div>
                        <div class="col-md-4"><input type="number" name="harga[]" class="form-control" value="<?php echo $v['harga']; ?>" required></div>
                        <div class="col-md-3"><input type="number" name="isi[]" class="form-control" value="<?php echo $v['isi_per_satuan']; ?>" required></div>
                        <div class="col-md-2"><button type="button" class="btn btn-danger btn-sm" onclick="hapusBaris(this)">X</button></div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <button type="button" class="btn btn-sm btn-success mb-3 mt-2" onclick="tambahVarian()">+ Tambah Varian Baru</button>

                <div class="mb-4">
                    <label>Upload Gambar Baru (Opsional)</label>
                    <input type="file" name="gambar" class="form-control" accept="image/*">
                    <small class="text-muted">Biarkan kosong jika tidak ingin mengubah gambar.</small>
                </div>
                <a href="barang.php" class="btn btn-secondary">Batal</a>
                <button type="submit" class="btn btn-warning fw-bold">Update Barang</button>
            </form>
        </div>
    </div>
</div>
<script>
function tambahVarian() {
    let html = `<div class="row mb-2 varian-row align-items-center">
        <input type="hidden" name="id_varian[]" value="0">
        <div class="col-md-3"><input type="text" name="satuan[]" class="form-control" placeholder="Satuan (Dus/Bks)" required></div>
        <div class="col-md-4"><input type="number" name="harga[]" class="form-control" placeholder="Harga Rp" required></div>
        <div class="col-md-3"><input type="number" name="isi[]" class="form-control" placeholder="Isi per satuan" required></div>
        <div class="col-md-2"><button type="button" class="btn btn-danger btn-sm" onclick="hapusBaris(this)">X</button></div>
    </div>`;
    document.getElementById('varian-container').insertAdjacentHTML('beforeend', html);
}
function hapusBaris(btn) {
    if(document.querySelectorAll('.varian-row').length > 1) {
        btn.closest('.varian-row').remove();
    } else {
        alert("Barang minimal harus memiliki 1 satuan harga!");
    }
}
</script>
</body>
</html>