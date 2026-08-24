<?php
require_once __DIR__ . '/../functions/security.php';
require_once __DIR__ . '/../config/koneksi.php';

check_admin();

// Proses Update Status Transaksi
if (isset($_GET['selesai'])) {
    $id_trx = intval($_GET['selesai']);
    $stmt_upd = mysqli_prepare($conn, "UPDATE transaksi SET status = 'selesai' WHERE id_transaksi = ?");
    mysqli_stmt_bind_param($stmt_upd, "i", $id_trx);
    mysqli_stmt_execute($stmt_upd);
    header("Location: transaksi.php");
    exit();
}

// Ambil data transaksi beserta nama user
$query = "SELECT t.id_transaksi, t.tanggal, t.total_harga, t.status, u.nama, u.no_hp 
          FROM transaksi t 
          JOIN users u ON t.id_user = u.id_user 
          ORDER BY t.tanggal DESC";
$result = mysqli_query($conn, $query);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Transaksi - Admin Podomoro</title>
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
                <a href="barang.php" class="list-group-item list-group-item-action">Kelola Barang</a>
                <a href="transaksi.php" class="list-group-item list-group-item-action active">Pesanan Masuk</a>
            </div>
        </div>
        <div class="col-md-9">
            <h3 class="mb-3">Daftar Transaksi</h3>
            <div class="card shadow-sm">
                <div class="card-body">
                    <table class="table table-bordered table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>ID</th>
                                <th>Tanggal</th>
                                <th>Nama Pembeli</th>
                                <th>Total Harga</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = mysqli_fetch_assoc($result)): ?>
                            <tr>
                                <td>TRX-<?php echo str_pad($row['id_transaksi'], 4, '0', STR_PAD_LEFT); ?></td>
                                <td><?php echo date('d-m-Y H:i', strtotime($row['tanggal'])); ?></td>
                                <td>
                                    <?php echo htmlspecialchars($row['nama']); ?><br>
                                    <small class="text-muted">WA: <?php echo htmlspecialchars($row['no_hp']); ?></small>
                                </td>
                                <td>Rp <?php echo number_format($row['total_harga'], 0, ',', '.'); ?></td>
                                <td>
                                    <?php if($row['status'] == 'pending'): ?>
                                        <span class="badge bg-warning text-dark">Pending</span>
                                    <?php else: ?>
                                        <span class="badge bg-success">Selesai</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if($row['status'] == 'pending'): ?>
                                        <a href="transaksi.php?selesai=<?php echo $row['id_transaksi']; ?>" class="btn btn-success btn-sm" onclick="return confirm('Tandai pesanan ini selesai?');">✓ Selesai</a>
                                    <?php else: ?>
                                        <button class="btn btn-secondary btn-sm" disabled>Selesai</button>
                                    <?php endif; ?>
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