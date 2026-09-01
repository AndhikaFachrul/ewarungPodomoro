<?php
require_once __DIR__ . '/../functions/security.php';
// Validasi hanya user login yang bisa masuk sini
check_login();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Keranjang Belanja - Podomoro</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../assets/css/responsive.css" rel="stylesheet">
    <style>
        .bg-navy { background-color: #122b4f; }
        .text-red { color: #dc3545; }
    </style>
</head>
<body class="bg-light">

<nav class="navbar navbar-expand-lg navbar-light bg-white py-3 shadow-sm cart-navbar">
    <div class="container">
        <a class="navbar-brand d-flex align-items-center" href="../index.php" style="text-decoration: none;">
            <img src="../assets/img/pdm_logotas.png" alt="Icon Tas" style="height: 45px; margin-right: 12px; object-fit: contain;">
            
            <div class="d-flex flex-column justify-content-center" style="line-height: 1; font-family: 'Arial Black', Impact, sans-serif;">
                <span style="color: #b01c20; font-weight: 900; font-size: 0.9rem; letter-spacing: 1px;">TOKO</span>
                <span style="color: #122b4f; font-weight: 900; font-size: 0.9rem; letter-spacing: 0.5px; margin-top: 2px;">PODOMORO</span>
            </div>
        </a>
        <div class="d-flex">
            <a href="../index.php" class="btn btn-outline-secondary cart-back-button">Kembali Belanja</a>
        </div>
    </div>
</nav>

<div class="container mt-5 cart-page">
    <h3 class="mb-4 text-navy">Keranjang Belanja</h3>
    <div class="row">
        <div class="col-md-8">
            <div class="card shadow-sm mb-3 cart-table-card">
                <div class="card-body">
                    <table class="table table-borderless align-middle" id="cart-table">
                        <thead class="border-bottom">
                            <tr>
                                <th>Produk</th>
                                <th>Harga</th>
                                <th>Kuantitas</th>
                                <th>Subtotal</th>
                                <th>Aksi</th> <!-- Tambahan Kolom Baru -->
                            </tr>
                        </thead>
                        <tbody id="cart-items">
                            <tr><td colspan="5" class="text-center cart-empty-cell">Memuat keranjang...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm cart-summary-card">
                <div class="card-body">
                    <h5 class="card-title fw-bold">Ringkasan Pesanan</h5>
                    <hr>
                    <div class="d-flex justify-content-between mb-3">
                        <span>Total Keseluruhan</span>
                        <strong class="text-red" id="total-harga">Rp 0</strong>
                    </div>
                    <button class="btn btn-danger w-100 fw-bold" id="btn-checkout">CHECKOUT VIA WHATSAPP</button>
                    
                    <div id="alert-msg" class="alert mt-3 d-none"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
const csrfToken = <?php echo json_encode(csrf_token()); ?>;

document.addEventListener("DOMContentLoaded", loadCart);

function loadCart() {
    fetch('../api/cart.php')
    .then(response => response.json())
    .then(res => {
        if(res.status) {
            let html = '';
            if(res.data.length === 0) {
                // Ubah colspan menjadi 5 karena ada kolom Aksi
                html = '<tr><td colspan="5" class="text-center py-4 cart-empty-cell">Keranjang masih kosong.</td></tr>';
            } else {
                res.data.forEach(item => {
                    html += `
                    <tr class="border-bottom">
                        <td class="cart-product-cell" data-label="Produk">
                            <div class="d-flex align-items-center">
                                <img src="../assets/img/${item.gambar || 'default.jpg'}" width="60" class="me-3 rounded" alt="${item.nama_barang}">
                                <span class="fw-bold">${item.nama_barang} <span class="text-primary">(${item.nama_satuan})</span></span>
                            </div>
                        </td>
                        <td data-label="Harga">Rp ${parseInt(item.harga).toLocaleString('id-ID')}</td>
                        <td data-label="Kuantitas">
                            <!-- Tombol Plus Minus Kuantitas -->
                            <div class="input-group input-group-sm" style="width: 110px;">
                                <button class="btn btn-outline-secondary fw-bold" type="button" onclick="updateQty(${item.id_cart}, ${item.qty - 1})">-</button>
                                <input type="text" class="form-control text-center fw-bold" value="${item.qty}" readonly>
                                <button class="btn btn-outline-secondary fw-bold" type="button" onclick="updateQty(${item.id_cart}, ${item.qty + 1})">+</button>
                            </div>
                        </td>
                        <td class="fw-bold" data-label="Subtotal">Rp ${parseInt(item.subtotal).toLocaleString('id-ID')}</td>
                        <td data-label="Aksi">
                            <!-- Tombol Hapus -->
                            <button class="btn btn-danger btn-sm" onclick="deleteItem(${item.id_cart})">Hapus</button>
                        </td>
                    </tr>`;
                });
            }
            document.getElementById('cart-items').innerHTML = html;
            document.getElementById('total-harga').innerText = `Rp ${parseInt(res.total_belanja).toLocaleString('id-ID')}`;
            
            // Nonaktifkan tombol checkout jika keranjang kosong
            document.getElementById('btn-checkout').disabled = (res.data.length === 0);
        }
    });
}

// FUNGSI BARU: Update Kuantitas
function updateQty(id_cart, new_qty) {
    if (new_qty < 1) return; // Jangan biarkan kurang dari 1, arahkan user ke tombol hapus
    
    let formData = new FormData();
    formData.append('id_cart', id_cart);
    formData.append('qty', new_qty);
    formData.append('csrf_token', csrfToken);

    fetch('../api/update_cart.php', { method: 'POST', body: formData })
    .then(response => response.json())
    .then(res => {
        if(res.status) {
            loadCart(); // Refresh tabel setelah update
        } else {
            showAlert('danger', res.message);
        }
    });
}

// FUNGSI BARU: Hapus Barang
function deleteItem(id_cart) {
    if(confirm("Apakah Anda yakin ingin menghapus barang ini dari keranjang?")) {
        let formData = new FormData();
        formData.append('id_cart', id_cart);
        formData.append('csrf_token', csrfToken);

        fetch('../api/delete_cart.php', { method: 'POST', body: formData })
        .then(response => response.json())
        .then(res => {
            if(res.status) {
                loadCart(); // Refresh tabel setelah hapus
            } else {
                showAlert('danger', res.message);
            }
        });
    }
}

// Fungsi Checkout (Sama seperti sebelumnya)
document.getElementById('btn-checkout').addEventListener('click', function() {
    let btn = this;
    btn.disabled = true;
    btn.innerText = "Memproses...";

    let formData = new FormData();
    formData.append('csrf_token', csrfToken);

    fetch('../api/checkout.php', { method: 'POST', body: formData })
    .then(response => {
        if (response.status === 429) {
            throw new Error("Terlalu banyak permintaan (Rate Limit). Tunggu beberapa saat.");
        }
        return response.json();
    })
    .then(res => {
        if(res.status && res.wa_url) {
            alert("Checkout berhasil! Mengalihkan ke WhatsApp...");
            window.open(res.wa_url, '_blank');
            window.location.href = 'riwayat.php';
        } else {
            showAlert('danger', res.message);
            btn.disabled = false;
            btn.innerText = "CHECKOUT VIA WHATSAPP";
        }
    })
    .catch(error => {
        showAlert('danger', error.message);
        btn.disabled = false;
        btn.innerText = "CHECKOUT VIA WHATSAPP";
    });
});

function showAlert(type, message) {
    let alertDiv = document.getElementById('alert-msg');
    alertDiv.className = `alert alert-${type} mt-3`;
    alertDiv.innerHTML = message;
    alertDiv.classList.remove('d-none');
}
</script>

</body>
</html>
