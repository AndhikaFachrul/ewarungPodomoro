<?php
session_start();

// Mencegah Session Hijacking & Fixation
if (!isset($_SESSION['CREATED'])) {
    $_SESSION['CREATED'] = time();
} else if (time() - $_SESSION['CREATED'] > 1800) {
    // Session timeout setelah 30 menit
    session_unset();
    session_destroy();
    header("Location: /ewarung/login.php?msg=timeout");
    exit();
}

// Regenerate ID setiap 5 menit untuk keamanan ekstra
if (!isset($_SESSION['LAST_REGENERATED'])) {
    $_SESSION['LAST_REGENERATED'] = time();
} else if (time() - $_SESSION['LAST_REGENERATED'] > 300) {
    session_regenerate_id(true);
    $_SESSION['LAST_REGENERATED'] = time();
}

function check_login() {
    if (!isset($_SESSION['id_user'])) {
        header("Location: /ewarung/login.php");
        exit();
    }
}

function check_admin() {
    check_login();
    if ($_SESSION['role'] !== 'admin') {
        die("Akses Ditolak: Anda bukan admin.");
    }
}
?>