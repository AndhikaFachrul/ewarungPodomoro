<?php

/**
 * Format angka menjadi format mata uang Rupiah
 *
 * @param float|int $angka
 * @return string
 */
function format_rupiah($angka) {
    return "Rp " . number_format($angka, 0, ',', '.');
}

/**
 * Sanitasi string dasar untuk mencegah XSS jika ditampilkan di view
 *
 * @param string $string
 * @return string
 */
function bersihkan_input($string) {
    return htmlspecialchars(trim($string), ENT_QUOTES, 'UTF-8');
}

/**
 * Menghasilkan JSON Response standard dan menghentikan eksekusi script
 * Sangat berguna untuk di-include di dalam API
 *
 * @param bool $status
 * @param string $message
 * @param array $data
 * @param int $http_code
 */
function json_response($status, $message, $data = [], $http_code = 200) {
    http_response_code($http_code);
    header('Content-Type: application/json');
    echo json_encode([
        "status" => $status,
        "message" => $message,
        "data" => $data
    ]);
    exit();
}
?>