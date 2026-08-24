<?php
session_start();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Barang - Toko Podomoro</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .text-navy { color: #122b4f; }
        .btn-red { background-color: #e53e3e; color: white; }
        .btn-red:hover { background-color: #c53030; color: white; }
        .card-produk { transition: transform 0.2s; border: 1px solid #dee2e6; }
        .card-produk:hover { transform: scale(1.02); box-shadow: 0 4px 8px rgba(0,0,0,0.1); }
        .img-container { height: 120px; display: flex; align-items: center; justify-content: center; background-color: #f8f9fa; }
        .img-container img { max-height: 100%; max-width: 100%; object-fit: contain; }
    </style>
</head>
<body class="bg-light">

<!-- NAVBAR -->
<nav class="navbar navbar-expand-lg navbar-light bg-white py-3 shadow-sm sticky-top">
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
                <li class="nav-item"><a class="nav-link" href="index.php">Beranda</a></li>
                <li class="nav-item"><a class="nav-link fw-bold text-danger border-bottom border-danger border-2 pb-1" href="barang.php">Barang</a></li>
                <li class="nav-item"><a class="nav-link" href="tentang.php">Tentang Kami</a></li>
                
                <?php if(isset($_SESSION['id_user'])): ?>
                    <li class="nav-item dropdown ms-3">
                        <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                            <div class="text-white rounded-circle d-flex justify-content-center align-items-center shadow-sm" style="width: 35px; height: 35px; font-size: 1.1rem;">👤</div>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end shadow border-0 mt-2">
                            <li><a class="dropdown-item py-2" href="user/profile.php">👤 Profil Saya</a></li>
                            <li><a class="dropdown-item py-2" href="user/riwayat.php">📋 Riwayat Pesanan</a></li>
                            <li><a class="dropdown-item py-2" href="user/cart.php">🛒 Keranjang Belanja</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item py-2 text-danger fw-bold" href="logout.php">🚪 Logout</a></li>
                        </ul>
                    </li>
                <?php else: ?>
                    <li class="nav-item ms-3"><a class="btn btn-outline-primary btn-sm px-4 rounded-pill fw-bold" href="login.php">Login</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>

<div class="container my-4">
    
    <!-- Wrapper Flexbox untuk mensejajarkan Header dan Pencarian -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        
        <!-- Judul diposisikan di sebelah kiri -->
        <h4 class="fw-bold text-navy mb-0">Semua Produk Kami</h4>

        <!-- Struktur UI Pencarian diposisikan di sebelah kanan -->
        <div class="search-container" style="position: relative; width: 300px;">
            <!-- Menggunakan class form-control bawaan bootstrap agar lebih rapi -->
            <input type="text" id="searchInput" class="form-control" placeholder="Cari nama barang..." autocomplete="off">
            
            <!-- Wadah dropdown hasil pencarian -->
            <div id="searchResults" class="rounded-bottom" style="position: absolute; top: 100%; left: 0; width: 100%; background: white; border: 1px solid #ccc; border-top: none; display: none; z-index: 1000; box-shadow: 0px 4px 6px rgba(0,0,0,0.1);">
            </div>
        </div>

    </div>

    <!-- Wadah List Barang (Tersusun ke bawah secara responsif) -->
    <div class="row g-3" id="produk-container">
        <!-- Data akan di-render di sini oleh JavaScript -->
    </div>


    <!-- Elemen Loading / Trigger Scroll -->
    <div id="loading-indicator" class="text-center py-4">
        <div class="spinner-border text-danger" role="status">
            <span class="visually-hidden">Loading...</span>
        </div>
        <p class="text-muted mt-2 small">Memuat barang lainnya...</p>
    </div>
    
    <div id="end-of-data" class="text-center py-4 d-none">
        <p class="text-muted fw-bold">✓ Semua barang telah ditampilkan.</p>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    let offset = 0;
    const limit = 8;
    let isFetching = false;
    let hasMoreData = true;

    const container = document.getElementById('produk-container');
    const loadingIndicator = document.getElementById('loading-indicator');
    const endOfData = document.getElementById('end-of-data');

    // Fungsi Fetch API Data Barang
    async function fetchBarang() {
        if (isFetching || !hasMoreData) return;
        isFetching = true;
        loadingIndicator.classList.remove('d-none');

        try {
            const response = await fetch(`api/get_barang_scroll.php?limit=${limit}&offset=${offset}`);
            if (response.status === 429) {
                console.warn("Terlalu banyak request, sistem rate limiting menahan koneksi.");
                isFetching = false;
                return;
            }
            
            const res = await response.json();

            if (res.status) {
                renderBarang(res.data);
                offset += limit;
                hasMoreData = res.has_more;

                if (!hasMoreData) {
                    loadingIndicator.classList.add('d-none');
                    endOfData.classList.remove('d-none');
                }
            }
        } catch (error) {
            console.error("Gagal mengambil data:", error);
        } finally {
            isFetching = false;
        }
    }

    // Fungsi Render HTML (Format Horizontal Card untuk tampilan kebawah)
    function renderBarang(data) {
        let html = '';
        data.forEach(item => {
            html += `
            <div class="col-md-6 col-lg-6">
                <div class="card card-produk h-100 p-2">
                    <div class="row g-0 align-items-center">
                        <div class="col-4 img-container rounded">
                            <img src="assets/img/${item.gambar || 'default.jpg'}" alt="${item.nama_barang}">
                        </div>
                        <div class="col-8">
                            <div class="card-body py-1 pe-1">
                                <span class="badge bg-secondary mb-1" style="font-size:0.7rem">${item.kategori}</span>
                                <h6 class="fw-bold mb-1 text-navy">${item.nama_barang}</h6>
                                <p class="text-danger fw-bold mb-2">Mulai Rp ${parseInt(item.harga).toLocaleString('id-ID')}</p>
                                <a href="detail.php?id=${item.id_barang}" class="btn btn-red btn-sm fw-bold w-100">Beli Sekarang</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>`;
        });
        // append/masukkan ke container tanpa menimpa data sebelumnya
        container.insertAdjacentHTML('beforeend', html);
    }

    // INTERSECTION OBSERVER: Deteksi saat user scroll mendekati kotak loading
    const observer = new IntersectionObserver((entries) => {
        if (entries[0].isIntersecting && !isFetching && hasMoreData) {
            fetchBarang();
        }
    }, {
        rootMargin: '100px' // Fetch data 100px sebelum mentok bawah biar mulus
    });

    // Mulai amati elemen loading
    observer.observe(loadingIndicator);

    // Panggil pertama kali saat web dibuka
    fetchBarang();

    const searchInput = document.getElementById('searchInput');
    const searchResults = document.getElementById('searchResults');

    /**
     * Fungsi Debounce: Menunda eksekusi AJAX sampai pengguna berhenti mengetik
     * Sangat penting agar waktu respons sistem tetap stabil dan tidak memicu rate limiter secara normal
     */
    function debounce(func, delay) {
        let timeoutId;
        return function(...args) {
            clearTimeout(timeoutId);
            timeoutId = setTimeout(() => {
                func.apply(this, args);
            }, delay);
        };
    }

    // Fungsi utama pemanggilan AJAX
    async function performSearch() {
        const keyword = searchInput.value.trim();

        // Sembunyikan hasil jika input kosong
        if (keyword.length === 0) {
            searchResults.innerHTML = '';
            searchResults.style.display = 'none';
            return;
        }

        try {
            const response = await fetch(`api/search.php?q=${encodeURIComponent(keyword)}`);

            // Menangkap blokir dari modul Rate Limiting (Kode 429)
            if (response.status === 429) {
                searchResults.innerHTML = '<div style="padding: 10px; color: red;">Permintaan terlalu cepat. Tunggu sebentar.</div>';
                searchResults.style.display = 'block';
                return;
            }

            const result = await response.json();

            if (result.status === 'success') {
                displayResults(result.data);
            }
        } catch (error) {
            console.error('Error fetching search data:', error);
        }
    }

    // Menampilkan data ke dalam DOM secara aman (mencegah XSS)
    function displayResults(data) {
        searchResults.innerHTML = '';

        if (data.length === 0) {
            searchResults.innerHTML = '<div style="padding: 10px; color: #666;">Barang tidak ditemukan.</div>';
        } else {
            data.forEach(item => {
                // Pembuatan elemen dinamis untuk mencegah XSS (jangan gunakan innerHTML pada input eksternal)
                const div = document.createElement('div');
                div.style.padding = '10px';
                div.style.cursor = 'pointer';
                div.style.borderBottom = '1px solid #eee';
                
                // Efek hover sederhana
                div.onmouseover = () => div.style.backgroundColor = '#f9f9f9';
                div.onmouseout = () => div.style.backgroundColor = 'white';
                
                div.textContent = `${item.nama_barang} - Rp${item.harga}`;
                
                // Navigasi ke P10 (Detail Barang) saat diklik
                div.onclick = () => {
                    window.location.href = `detail.php?id=${item.id_barang}`;
                };
                
                searchResults.appendChild(div);
            });
        }
        searchResults.style.display = 'block';
    }

    // Pasang event listener 'input' dengan debounce 350 milidetik
    searchInput.addEventListener('input', debounce(performSearch, 350));

    // Fitur tambahan: Sembunyikan hasil pencarian jika pengguna mengklik area luar
    document.addEventListener('click', function(event) {
        if (!searchInput.contains(event.target) && !searchResults.contains(event.target)) {
            searchResults.style.display = 'none';
        }
    });

</script>
</body>
</html>