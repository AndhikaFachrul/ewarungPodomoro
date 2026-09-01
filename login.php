<?php
require_once __DIR__ . '/functions/security.php';
// Jika sudah login, lempar ke index atau dashboard
if (isset($_SESSION['id_user'])) {
    if ($_SESSION['role'] === 'admin') {
        header("Location: admin/dashboard.php");
    } else {
        header("Location: index.php");
    }
    exit();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Toko Podomoro</title>
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
                    <h4 class="mb-0">Masuk Akun</h4>
                </div>
                <div class="card-body p-4">
                    <div id="alert-msg" class="alert d-none" role="alert"></div>

                    <form id="formLogin">
                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Password</label>
                            <input type="password" name="password" class="form-control" required>
                        </div>
                        <button type="submit" class="btn btn-danger w-100" id="btnLogin">Masuk</button>
                    </form>
                    <div class="text-center mt-3">
                        <small>Belum punya akun? <a href="register.php">Daftar di sini</a></small><br>
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
document.getElementById('formLogin').addEventListener('submit', function(e) {
    e.preventDefault();
    
    let btn = document.getElementById('btnLogin');
    let alertMsg = document.getElementById('alert-msg');
    let formData = new FormData(this);

    btn.disabled = true;
    btn.innerHTML = 'Memproses...';

    fetch('api/login.php', {
        method: 'POST',
        body: formData
    })
    .then(async response => {
        let res = await response.json();
        // Cek jika HTTP Status 429 (Rate Limit tercapai)
        if (response.status === 429) {
            showAlert('danger', res.message);
            btn.disabled = false;
            btn.innerHTML = 'Masuk';
            return;
        }
        
        if (res.status) {
            showAlert('success', 'Login berhasil! Mengalihkan...');
            setTimeout(() => {
                window.location.href = res.data.role === 'admin' ? 'admin/dashboard.php' : 'index.php';
            }, 1000);
        } else {
            showAlert('danger', res.message);
            btn.disabled = false;
            btn.innerHTML = 'Masuk';
        }
    })
    .catch(error => {
        showAlert('danger', 'Terjadi kesalahan sistem.');
        btn.disabled = false;
        btn.innerHTML = 'Masuk';
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
