<?php
require_once __DIR__ . '/../functions/security.php';
require_once __DIR__ . '/../config/koneksi.php';

check_admin();

// Ambil semua data dengan role 'user'
$query = "SELECT id_user, nama, email, no_hp, created_at FROM users WHERE role = 'user' ORDER BY created_at DESC";
$result = mysqli_query($conn, $query);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Data Pengguna - Admin Podomoro</title>
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
                <a href="transaksi.php" class="list-group-item list-group-item-action">Pesanan Masuk</a>
                <a href="users.php" class="list-group-item list-group-item-action active">Data Pengguna</a>
            </div>
        </div>
        <div class="col-md-9">
            <h3 class="mb-3">Daftar Pengguna (Pelanggan)</h3>
            <div class="card shadow-sm">
                <div class="card-body">
                    <table class="table table-bordered table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>No</th>
                                <th>Nama Lengkap</th>
                                <th>Email</th>
                                <th>No. WhatsApp</th>
                                <th>Tanggal Daftar</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $no = 1; while ($row = mysqli_fetch_assoc($result)): ?>
                            <tr>
                                <td><?php echo $no++; ?></td>
                                <td><?php echo htmlspecialchars($row['nama']); ?></td>
                                <td><?php echo htmlspecialchars($row['email']); ?></td>
                                <td><?php echo htmlspecialchars($row['no_hp']); ?></td>
                                <td><?php echo date('d-m-Y H:i', strtotime($row['created_at'])); ?></td>
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