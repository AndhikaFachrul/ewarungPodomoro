<?php session_start(); ?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Toko Podomoro</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .bg-navy { background-color: #122b4f; color: white; }
        .bg-red-accent { background-color: #c92a2a; color: white; }
        .hero-section {
            background: linear-gradient(110deg, #122b4f 60%, #c92a2a 60%);
            padding: 80px 0;
            color: white;
        }
        .card-product { border: 2px solid #c92a2a; border-radius: 5px; }
        .btn-red { background-color: #e53e3e; color: white; width: 100%; border-radius: 0; }
        .btn-red:hover { background-color: #c53030; color: white; }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-light bg-white py-3 shadow-sm">
    <div class="container">
        
        <!-- Logo Brand -->
        <a class="navbar-brand d-flex align-items-center" href="index.php" style="text-decoration: none;">
            <img src="assets/img/pdm_logotas.png" alt="Icon Tas" style="height: 45px; margin-right: 12px; object-fit: contain;">
            
            <div class="d-flex flex-column justify-content-center" style="line-height: 1; font-family: 'Arial Black', Impact, sans-serif;">
                <span style="color: #b01c20; font-weight: 900; font-size: 0.9rem; letter-spacing: 1px;">TOKO</span>
                <span style="color: #122b4f; font-weight: 900; font-size: 0.9rem; letter-spacing: 0.5px; margin-top: 2px;">PODOMORO</span>
            </div>
        </a>

        <!-- INI KUNCI RESPONSIVENYA: Tombol Hamburger Toggler -->
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <!-- Menu Navbar -->
        <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
            <ul class="navbar-nav align-items-center">
                <!-- Menu Utama -->
                <li class="nav-item"><a class="nav-link fw-bold text-danger border-bottom border-danger border-2 pb-1" href="index.php">Beranda</a></li>
                <li class="nav-item"><a class="nav-link" href="barang.php">Barang</a></li>
                <li class="nav-item"><a class="nav-link" href="tentang.php">Tentang Kami</a></li>
                
                <?php if(isset($_SESSION['id_user'])): ?>
                    <!-- Menu Dropdown Profil -->
                    <li class="nav-item dropdown ms-3">
                        <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <div class="text-white rounded-circle d-flex justify-content-center align-items-center shadow-sm" style="background-color: #ffffff; width: 35px; height: 35px; font-size: 1.1rem;">
                                👤
                            </div>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end shadow border-0 mt-2" aria-labelledby="navbarDropdown">
                            <li><a class="dropdown-item py-2" href="user/profile.php">👤 Profil Saya</a></li>
                            <li><a class="dropdown-item py-2" href="user/riwayat.php">📋 Riwayat Pesanan</a></li>
                            <li><a class="dropdown-item py-2" href="user/cart.php">🛒 Keranjang Belanja</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item py-2 text-danger fw-bold" href="logout.php">Logout</a></li>
                        </ul>
                    </li>
                <?php else: ?>
                    <!-- Tombol Login -->
                    <li class="nav-item ms-3 mt-2 mt-lg-0">
                        <a class="btn btn-outline-primary btn-sm px-4 rounded-pill fw-bold" href="login.php">Login</a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>

<div class="hero-section">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-6">
                <h1 class="fw-bold display-4">SUPER SPESIAL<br>PROMO</h1>
                <p class="mt-3">Jangan lewatkan kesempatan langka ini untuk belanja cerdas dan hemat, segera borong stok kebutuhan rumah tangga Anda sekarang juga sebelum kehabisan!</p>
            </div>
            <div class="col-md-6 text-center">
                <img src="assets/img/promo.png" alt="Promo Sembako" class="img-fluid rounded-circle  ">
            </div>
        </div>
    </div>
</div>

<div class="container my-5">
    <div class="row g-4" id="produk-container">
        </div>
</div>

<script>
// Fetch Data Produk (AJAX)
document.addEventListener("DOMContentLoaded", function() {
    fetch('api/get_barang.php')
        .then(response => response.json())
        .then(res => {
            if(res.status) {
                let html = '';
                
                // PERBAIKAN 1: Potong data agar HANYA mengambil 6 barang pertama
                let barangLimit = res.data.slice(0, 6);
                
                barangLimit.forEach(item => {
                    html += `
                    <!-- PERBAIKAN 2: Ubah col-md-3 menjadi col-md-4 agar susunannya 3 kolom -->
                    <div class="col-md-4 col-6 mb-3">
                        <div class="card h-100 p-3 text-center border-1 shadow-lg" style="border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.05) !important;">
                            <img src="assets/img/${item.gambar || 'default.jpg'}" class="card-img-top mx-auto mt-2" style="height:160px; object-fit:contain;" alt="${item.nama_barang}">
                            <div class="card-body p-2 d-flex flex-column justify-content-between">
                                <div>
                                    <h6 class="fw-bold mb-1 text-navy" style="font-size: 0.95rem;">${item.nama_barang.toUpperCase()}</h6>
                                    <p class="text-danger fw-bold mb-3 fs-5">Rp ${parseInt(item.harga).toLocaleString('id-ID')}</p>
                                </div>
                                <!-- PERBAIKAN 3: Tombol dibuat oval dan diatur lebarnya sesuai gambar -->
                                <button onclick="lihatDetail(${item.id_barang})" class="btn btn-red btn-sm fw-bold mx-auto px-4 py-2" style="border-radius: 25px; width: 85%;">LIHAT DETAIL</button>
                            </div>
                        </div>
                    </div>`;
                });
                
                document.getElementById('produk-container').innerHTML = html;
                
                // Tambahan: Tombol untuk melihat semua barang yang diarahkan ke barang.php
                if (res.data.length > 6) {
                    let btnSemua = `
                    <div class="col-12 text-center mt-4">
                        <a href="barang.php" class="btn btn-outline-danger fw-bold rounded-pill px-5 py-2">Lihat Semua Produk</a>
                    </div>`;
                    document.getElementById('produk-container').insertAdjacentHTML('beforeend', btnSemua);
                }
            }
        });
});

function lihatDetail(id) {
    window.location.href = `detail.php?id=${id}`;
}
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<?php include 'footer.php'; ?>

</body>
</html>