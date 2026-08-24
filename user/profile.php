<?php
require_once __DIR__ . '/../functions/security.php';
require_once __DIR__ . '/../config/koneksi.php';

check_login();

$id_user = $_SESSION['id_user'];

$stmt = mysqli_prepare($conn, "SELECT nama, email, no_hp, created_at FROM users WHERE id_user = ?");
mysqli_stmt_bind_param($stmt, "i", $id_user);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$user = mysqli_fetch_assoc($result);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil Saya - Toko Podomoro</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .text-navy { color: #122b4f; }
        .bg-navy { background-color: #122b4f; color: white; }
    </style>
</head>
<body class="bg-light">

<nav class="navbar navbar-expand-lg navbar-light bg-white py-3 shadow-sm mb-5">
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
                        <!-- Profil Saya diset Aktif (Merah & Tebal) -->
                        <li><a class="dropdown-item py-2 fw-bold text-danger bg-light" href="profile.php">👤 Profil Saya</a></li>
                        
                        <!-- Menu di dalam folder user tidak perlu ../ -->
                        <li><a class="dropdown-item py-2" href="riwayat.php">📋 Riwayat Pesanan</a></li>
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
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-navy text-white py-3">
                    <h5 class="mb-0 text-center fw-bold">👤 Informasi Akun</h5>
                </div>
                <div class="card-body p-4">
                    <ul class="list-group list-group-flush mb-4">
                        <li class="list-group-item px-0 py-3">
                            <small class="text-muted d-block mb-1">Nama Lengkap</small>
                            <span class="fw-bold fs-5"><?php echo htmlspecialchars($user['nama']); ?></span>
                        </li>
                        <li class="list-group-item px-0 py-3">
                            <small class="text-muted d-block mb-1">Email</small>
                            <span class="fw-bold fs-5"><?php echo htmlspecialchars($user['email']); ?></span>
                        </li>
                        <li class="list-group-item px-0 py-3">
                            <small class="text-muted d-block mb-1">Nomor WhatsApp</small>
                            <span class="fw-bold fs-5"><?php echo htmlspecialchars($user['no_hp']); ?></span>
                        </li>
                        <li class="list-group-item px-0 py-3">
                            <small class="text-muted d-block mb-1">Terdaftar Pada</small>
                            <span class="fw-bold fs-5"><?php echo date('d F Y', strtotime($user['created_at'])); ?></span>
                        </li>
                    </ul>
                    
                    <div class="text-center mt-2">
                        <a href="../logout.php" class="btn btn-outline-danger w-100 fw-bold py-2">LOGOUT / KELUAR</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<?php include '../footer.php'; ?>

</body>
</html>