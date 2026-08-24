<?php
require_once __DIR__ . '/../functions/security.php';
require_once __DIR__ . '/../config/koneksi.php';

// Validasi mutlak: Pastikan yang akses adalah admin
check_admin();

// Query untuk mendapatkan metrik dashboard
$query_barang = mysqli_query($conn, "SELECT COUNT(id_barang) as total FROM barang");
$total_barang = mysqli_fetch_assoc($query_barang)['total'];

$query_user = mysqli_query($conn, "SELECT COUNT(id_user) as total FROM users WHERE role = 'user'");
$total_user = mysqli_fetch_assoc($query_user)['total'];

$query_trx = mysqli_query($conn, "SELECT COUNT(id_transaksi) as total FROM transaksi");
$total_trx = mysqli_fetch_assoc($query_trx)['total'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard - Podomoro</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<nav class="navbar navbar-expand-lg navbar-dark" style="background-color: #122b4f;">
    <div class="container-fluid">
        <a class="navbar-brand" href="#">Panel Admin</a>
        <div class="d-flex">
            <span class="navbar-text me-3 text-white">
                Halo, <?php echo htmlspecialchars($_SESSION['nama']); ?>
            </span>
            <a href="../logout.php" class="btn btn-outline-light btn-sm">Logout</a>
        </div>
    </div>
</nav>

<div class="container mt-4">
    <div class="row">
        <div class="col-md-3">
            <div class="list-group">
                <a href="dashboard.php" class="list-group-item list-group-item-action active">Dashboard</a>
                <a href="barang.php" class="list-group-item list-group-item-action">Kelola Barang</a>
                <a href="transaksi.php" class="list-group-item list-group-item-action">Pesanan Masuk</a>
            </div>
        </div>

        <div class="col-md-9">
            <h3>Dashboard</h3>
            <div class="row mt-3">
                <div class="col-md-4">
                    <div class="card text-white bg-primary mb-3 shadow-sm">
                        <div class="card-body">
                            <h5 class="card-title">Total Barang</h5>
                            <h2><?php echo $total_barang; ?></h2>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card text-white bg-success mb-3 shadow-sm">
                        <div class="card-body">
                            <h5 class="card-title">Total Pengguna</h5>
                            <h2><?php echo $total_user; ?></h2>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card text-white bg-warning mb-3 shadow-sm">
                        <div class="card-body">
                            <h5 class="card-title">Total Transaksi</h5>
                            <h2><?php echo $total_trx; ?></h2>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

</body>
</html>