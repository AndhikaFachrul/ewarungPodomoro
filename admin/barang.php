<?php
require_once __DIR__ . '/../functions/security.php';
require_once __DIR__ . '/../config/koneksi.php';

// Validasi akses Admin
check_admin();

// Proses Hapus Barang
if (isset($_GET['hapus'])) {
    $id_hapus = intval($_GET['hapus']);
    
    // Ambil nama gambar untuk dihapus dari folder
    $stmt_img = mysqli_prepare($conn, "SELECT gambar FROM barang WHERE id_barang = ?");
    mysqli_stmt_bind_param($stmt_img, "i", $id_hapus);
    mysqli_stmt_execute($stmt_img);
    $res_img = mysqli_stmt_get_result($stmt_img);
    
    if ($row = mysqli_fetch_assoc($res_img)) {
        if ($row['gambar'] && file_exists("../assets/img/" . $row['gambar'])) {
            unlink("../assets/img/" . $row['gambar']);
        }
    }
    
    // Hapus data dari database
    $stmt_del = mysqli_prepare($conn, "DELETE FROM barang WHERE id_barang = ?");
    mysqli_stmt_bind_param($stmt_del, "i", $id_hapus);
    if (mysqli_stmt_execute($stmt_del)) {
        echo "<script>alert('Barang berhasil dihapus!'); window.location.href='barang.php';</script>";
    }
}

// Mengambil data barang sekaligus harga termurah dari tabel varian
$query = "SELECT b.*, 
          (SELECT MIN(harga) FROM varian_barang WHERE id_barang = b.id_barang) as harga_mulai 
          FROM barang b ORDER BY b.id_barang DESC";
$result = mysqli_query($conn, $query);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Kelola Barang - Admin Podomoro</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<nav class="navbar navbar-expand-lg navbar-dark" style="background-color: #122b4f;">
    <div class="container-fluid">
        <a class="navbar-brand" href="dashboard.php">Panel Admin</a>
        <div class="d-flex">
            <span class="navbar-text me-3 text-white">Halo, <?php echo htmlspecialchars($_SESSION['nama']); ?></span>
            <a href="../logout.php" class="btn btn-outline-light btn-sm">Logout</a>
        </div>
    </div>
</nav>

<div class="container mt-4">
    <div class="row">
        <div class="col-md-3">
            <div class="list-group">
                <a href="dashboard.php" class="list-group-item list-group-item-action">Dashboard</a>
                <a href="barang.php" class="list-group-item list-group-item-action active">Kelola Barang</a>
                <a href="transaksi.php" class="list-group-item list-group-item-action">Pesanan Masuk</a>
            </div>
        </div>
        <div class="col-md-9">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h3>Daftar Barang</h3>
                <a href="tambah_barang.php" class="btn btn-primary">+ Tambah Barang</a>
            </div>
            <div class="card shadow-sm">
                <div class="card-body">
                    <table class="table table-bordered table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>No</th>
                                <th>Gambar</th>
                                <th>Nama Barang</th>
                                <th>Stok</th>
                                <th>Harga</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $no = 1; while ($row = mysqli_fetch_assoc($result)): ?>
                            <tr>
                                <td><?php echo $no++; ?></td>
                                <td>
                                    <?php if ($row['gambar']): ?>
                                        <img src="../assets/img/<?php echo htmlspecialchars($row['gambar']); ?>" width="50" alt="Gambar">
                                    <?php endif; ?>
                                </td>
                                <td><?php echo htmlspecialchars($row['nama_barang']); ?></td>
                                <td><?php echo htmlspecialchars($row['stok']); ?></td>
                                <td>
                                    <small class="text-muted">Mulai dari</small><br>
                                    Rp <?php echo number_format($row['harga_mulai'] ?? 0, 0, ',', '.'); ?>
                                </td>
                                <td>
                                    <a href="edit_barang.php?id=<?php echo $row['id_barang']; ?>" class="btn btn-warning btn-sm">Edit</a>
                                    <a href="barang.php?hapus=<?php echo $row['id_barang']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Yakin ingin menghapus?');">Hapus</a>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

</body>
</html>