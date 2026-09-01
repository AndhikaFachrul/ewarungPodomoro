<?php
require_once __DIR__ . '/../functions/security.php';
require_once __DIR__ . '/../config/koneksi.php';

// Pastikan user sudah login
check_login();

$id_user = $_SESSION['id_user'];

// Ambil data transaksi user yang sedang login
$stmt = mysqli_prepare($conn, "SELECT * FROM transaksi WHERE id_user = ? ORDER BY tanggal DESC");
mysqli_stmt_bind_param($stmt, "i", $id_user);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Pesanan - Toko Podomoro</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../assets/css/responsive.css" rel="stylesheet">
    <style>
        .text-navy { color: #122b4f; }
        .bg-navy { background-color: #122b4f; color: white; }
    </style>
</head>
<body class="bg-light">

<nav class="navbar navbar-expand-lg navbar-light bg-white py-3 shadow-sm mb-4">
    <div class="container">
        <a class="navbar-brand d-flex align-items-center" href="../index.php" style="text-decoration: none;">
            <img src="../assets/img/pdm_logotas.png" alt="Icon Tas" style="height: 45px; margin-right: 12px; object-fit: contain;">
            
            <div class="d-flex flex-column justify-content-center" style="line-height: 1; font-family: 'Arial Black', Impact, sans-serif;">
                <span style="color: #b01c20; font-weight: 900; font-size: 0.9rem; letter-spacing: 1px;">TOKO</span>
                <span style="color: #122b4f; font-weight: 900; font-size: 0.9rem; letter-spacing: 0.5px; margin-top: 2px;">PODOMORO</span>
            </div>
        </a>
        
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>

<div class="collapse navbar-collapse justify-content-end" id="navbarNav">
    <ul class="navbar-nav align-items-center">
        <!-- Menu Utama (Kembali ke folder luar menggunakan ../) -->
        <li class="nav-item"><a class="nav-link" href="../index.php">Beranda</a></li>
        <li class="nav-item"><a class="nav-link" href="../barang.php">Barang</a></li>
        <li class="nav-item"><a class="nav-link" href="../tentang.php">Tentang Kami</a></li>
        
        <!-- Menu Dropdown Profil -->
        <li class="nav-item dropdown ms-3">
            <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                <!-- Icon Foto Profil -->
                <div class="text-white rounded-circle d-flex justify-content-center align-items-center shadow-sm" style="width: 35px; height: 35px; font-size: 1.1rem;">
                    👤
                </div>
            </a>
            <ul class="dropdown-menu dropdown-menu-end shadow border-0 mt-2" aria-labelledby="navbarDropdown">
                <!-- Menu di dalam folder user tidak perlu ../ -->
                <li><a class="dropdown-item py-2" href="profile.php">👤 Profil Saya</a></li>
                
                <!-- Riwayat Pesanan diset Aktif (Merah & Tebal) -->
                <li><a class="dropdown-item py-2 fw-bold text-danger bg-light" href="riwayat.php">📋 Riwayat Pesanan</a></li>
                
                <li><a class="dropdown-item py-2" href="cart.php">🛒 Keranjang Belanja</a></li>
                <li><hr class="dropdown-divider"></li>
                
                <!-- Logout kembali ke luar folder -->
                <li><a class="dropdown-item py-2 text-danger fw-bold" href="../logout.php">🚪 Logout</a></li>
            </ul>
        </li>
    </ul>
</div>
    </div>
</nav>

<div class="container mb-5">
    <div class="card shadow-sm border-0">
        <div class="card-header bg-navy text-white fw-bold py-3">
            📋 Riwayat Pesanan Saya
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="px-4">ID Pesanan</th>
                            <th>Tanggal</th>
                            <th>Total Belanja</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (mysqli_num_rows($result) > 0): ?>
                            <?php while ($row = mysqli_fetch_assoc($result)): ?>
                            <tr>
                                <td class="px-4 fw-bold">TRX-<?php echo str_pad($row['id_transaksi'], 4, '0', STR_PAD_LEFT); ?></td>
                                <td><?php echo date('d M Y, H:i', strtotime($row['tanggal'])); ?></td>
                                <td class="text-danger fw-bold">Rp <?php echo number_format($row['total_harga'], 0, ',', '.'); ?></td>
                                <td>
                                    <?php if($row['status'] == 'pending'): ?>
                                        <span class="badge bg-warning text-dark">Menunggu Konfirmasi</span>
                                    <?php else: ?>
                                        <span class="badge bg-success">Selesai</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4" class="text-center text-muted py-4">
                                    Belum ada riwayat pesanan.<br>
                                    <a href="../index.php" class="btn btn-outline-danger btn-sm mt-3">Mulai Belanja</a>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<?php include '../footer.php'; ?>

</body>
</html>