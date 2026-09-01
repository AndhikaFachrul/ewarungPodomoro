<?php
require_once __DIR__ . '/functions/security.php';
if (isset($_SESSION['id_user'])) {
    header("Location: index.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Akun - Toko Podomoro</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/css/responsive.css" rel="stylesheet">
    <style>
        .bg-navy { background-color: #122b4f; }
    </style>
</head>
<body class="bg-light">

<div class="container mt-5 auth-page">
    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="card shadow-sm">
                <div class="card-header bg-navy text-white text-center py-3">
                    <h4 class="mb-0">Daftar Akun Baru</h4>
                </div>
                <div class="card-body p-4">
                    <div id="alert-msg" class="alert d-none" role="alert"></div>

                    <form id="formRegister">
                        <div class="mb-3">
                            <label class="form-label">Nama Lengkap</label>
                            <input type="text" name="nama" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control" maxlength="100" autocomplete="email" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Nomor WhatsApp</label>
                            <input type="tel" name="no_hp" class="form-control" maxlength="20" inputmode="tel" autocomplete="tel" placeholder="Contoh: 081234567890" required>
                            <div class="form-text">Gunakan nomor Indonesia dengan awalan 08, 628, atau +628.</div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Password</label>
                            <input type="password" name="password" class="form-control" required minlength="6">
                        </div>
                        <button type="submit" class="btn btn-danger w-100 fw-bold" id="btnRegister">DAFTAR SEKARANG</button>
                    </form>
                    <div class="text-center mt-3">
                        <small>Sudah punya akun? <a href="login.php">Masuk di sini</a></small><br>
                        <small class="d-inline-block mt-2">
                            <a href="index.php" class="text-decoration-none text-muted">← Kembali ke Beranda</a>
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('formRegister').addEventListener('submit', function(e) {
    e.preventDefault();
    
    let btn = document.getElementById('btnRegister');
    let alertMsg = document.getElementById('alert-msg');
    let formData = new FormData(this);

    btn.disabled = true;
    btn.innerHTML = 'Memproses...';

    fetch('api/register.php', {
        method: 'POST',
        body: formData
    })
    .then(async response => {
        let res = await response.json();
        
        // Tangkap status 429 dari algoritma Fixed Window Counter
        if (response.status === 429) {
            showAlert('danger', res.message);
            btn.disabled = false;
            btn.innerHTML = 'DAFTAR SEKARANG';
            return;
        }
        
        if (res.status) {
            showAlert('success', res.message);
            setTimeout(() => {
                window.location.href = 'login.php';
            }, 1500);
        } else {
            showAlert('danger', res.message);
            btn.disabled = false;
            btn.innerHTML = 'DAFTAR SEKARANG';
        }
    })
    .catch(error => {
        showAlert('danger', 'Terjadi kesalahan sistem. Pastikan server berjalan.');
        btn.disabled = false;
        btn.innerHTML = 'DAFTAR SEKARANG';
    });
});

function showAlert(type, message) {
    let alertDiv = document.getElementById('alert-msg');
    alertDiv.className = `alert alert-${type}`;
    alertDiv.innerHTML = message;
    alertDiv.classList.remove('d-none');
}
</script>
</body>
</html>
