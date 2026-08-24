<?php
session_start();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tentang Kami - Toko Podomoro</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .text-navy { color: #122b4f; }
        .text-red { color: #b01c20; }
        .bg-light-blue { background-color: #f4f6f9; }
        .visi-misi-list li {
            margin-bottom: 10px;
            color: #333;
        }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-light bg-white py-3 shadow-sm border-bottom">
    <div class="container">
        <a class="navbar-brand d-flex align-items-center" href="index.php" style="text-decoration: none;">
            <img src="assets/img/pdm_logotas.png" alt="Icon Tas" style="height: 45px; margin-right: 12px; object-fit: contain;">
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
                <!-- Menu Utama -->
                <li class="nav-item"><a class="nav-link" href="index.php">Beranda</a></li>
                <li class="nav-item"><a class="nav-link" href="barang.php">Barang</a></li>
                
                <!-- Menu Tentang Kami diset Aktif (Merah & Tebal dengan garis bawah) -->
                <li class="nav-item"><a class="nav-link fw-bold text-danger border-bottom border-danger border-2 pb-1" href="tentang.php">Tentang Kami</a></li>
                
                <?php if(isset($_SESSION['id_user'])): ?>
                    <!-- Menu Dropdown Profil -->
                    <li class="nav-item dropdown ms-3">
                        <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <!-- Icon Foto Profil -->
                            <div class="text-white rounded-circle d-flex justify-content-center align-items-center shadow-sm" style="width: 35px; height: 35px; font-size: 1.1rem;">
                                👤
                            </div>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end shadow border-0 mt-2" aria-labelledby="navbarDropdown">
                            <li><a class="dropdown-item py-2" href="user/profile.php">👤 Profil Saya</a></li>
                            <li><a class="dropdown-item py-2" href="user/riwayat.php">📋 Riwayat Pesanan</a></li>
                            <li><a class="dropdown-item py-2" href="user/cart.php">🛒 Keranjang Belanja</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item py-2 text-danger fw-bold" href="logout.php">🚪 Logout</a></li>
                        </ul>
                    </li>
                <?php else: ?>
                    <!-- Tombol Login -->
                    <li class="nav-item ms-3">
                        <a class="btn btn-outline-primary btn-sm px-4 rounded-pill fw-bold" href="login.php">Login</a>
                    </li>
                <?php endif; ?>
            </ul>
</div>
    </div>
</nav>

<div class="container mt-5 pt-4 text-center">
    <div class="d-flex justify-content-center align-items-center mb-5">
        <img src="assets/img/pdm_logotas.png" alt="Logo Toko Podomoro" style="height: 120px; margin-right: 20px; object-fit: contain;">
        <div class="d-flex flex-column justify-content-center text-start" style="line-height: 1; font-family: 'Arial Black', Impact, sans-serif;">
            <span style="color: #b01c20; font-weight: 900; font-size: 2.5rem; letter-spacing: 2px;">TOKO</span>
            <span style="color: #122b4f; font-weight: 900; font-size: 2.5rem; letter-spacing: 1px; margin-top: 5px;">PODOMORO</span>
        </div>
    </div>
</div>

<div class="container mb-5">
    <h3 class="text-navy fw-bold mb-3">Tentang Toko Podomoro</h3>
    <p class="text-secondary lh-lg" style="text-align: justify;">
        Toko Grosir Podomoro merupakan pusat belanja kebutuhan rumah tangga dan sembako terpercaya yang berdedikasi untuk memberikan harga terbaik dengan kualitas barang yang terjamin. Kami hadir untuk memenuhi segala kebutuhan harian masyarakat, mulai dari beras, minyak goreng, bumbu dapur, hingga perlengkapan kebersihan. Dengan pelayanan yang ramah dan sistem yang modern, Toko Podomoro berkomitmen untuk menjadi mitra belanja terbaik bagi keluarga Anda.
    </p>
</div>

<div class="container mb-5 pb-5">
    <hr class="mb-5">
    <div class="row">
        <div class="col-md-6 mb-4">
            <h4 class="text-navy fw-bold mb-4">Visi</h4>
            <ul class="visi-misi-list ps-3">
                <li>Menjadi toko grosir dan eceran terbaik yang menjadi pilihan utama masyarakat.</li>
                <li>Menghadirkan kenyamanan berbelanja dengan harga yang kompetitif dan terjangkau.</li>
                <li>Menjadi pusat distribusi sembako yang selalu mengedepankan kualitas pelayanan dan kepuasan pelanggan secara berkesinambungan.</li>
            </ul>
        </div>
        <div class="col-md-6 mb-4">
            <h4 class="text-navy fw-bold mb-4">Misi</h4>
            <ul class="visi-misi-list ps-3">
                <li>Menyediakan barang berkualitas tinggi dengan stok yang selalu terjaga kelengkapannya.</li>
                <li>Memberikan kemudahan akses berbelanja melalui platform digital E-Warung.</li>
                <li>Menjaga integritas dan kepercayaan pelanggan dengan proses transaksi yang aman, transparan, dan cepat.</li>
                <li>Menjalin hubungan baik dengan mitra pemasok untuk memastikan ketersediaan barang dengan harga terbaik.</li>
            </ul>
        </div>
    </div>
</div>

<div class="bg-light-blue py-5 border-top">
    <div class="container">
        <div class="row">
            <div class="col-md-5 mb-4">
                <h4 class="text-navy fw-bold mb-4">Hubungi Kami</h4>
                <div class="d-flex mb-3 align-items-start">
                    <span class="fs-4 me-3 text-navy">📍</span>
                    <p class="mb-0 mt-1 text-secondary">
                        Jl. Raya Citatah No. 135/12,<br>
                        Kec. Cipatat, Kabupaten Bandung Barat,<br>
                        Toko Podomoro
                    </p>
                </div>
                <div class="d-flex mb-3 align-items-center">
                    <span class="fs-4 me-3 text-navy">📞</span>
                    <p class="mb-0 text-secondary">(081) 234 567 890</p>
                </div>
                <div class="d-flex align-items-center">
                    <span class="fs-4 me-3 text-navy">✉️</span>
                    <p class="mb-0 text-secondary">email@tokopodomoro.com</p>
                </div>
            </div>
            
            <div class="col-md-7">
                <div class="card shadow-sm border-0 p-1">
                    <iframe 
                        src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d126748.56347863116!2d107.573117!3d-6.9034443!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2e68e6398252477f%3A0x146a1f93d3e815b2!2sBandung%2C%20Kota%20Bandung%2C%20Jawa%20Barat!5e0!3m2!1sid!2sid!4v1700000000000!5m2!1sid!2sid" 
                        width="100%" 
                        height="250" 
                        style="border:0; border-radius: 5px;" 
                        allowfullscreen="" 
                        loading="lazy" 
                        referrerpolicy="no-referrer-when-downgrade">
                    </iframe>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<?php include 'footer.php'; ?>

</body>
</html>