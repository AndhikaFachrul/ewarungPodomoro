<?php
require_once __DIR__ . '/../config/koneksi.php';

function get_client_ip() {
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        return $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        return $_SERVER['HTTP_X_FORWARDED_FOR'];
    } else {
        return $_SERVER['REMOTE_ADDR'];
    }
}

/**
 * Core Engine Rate Limiting (Mendukung Fixed Window & Token Bucket)
 * * @param string $endpoint Nama API (contoh: '/api/login.php')
 * @param int $limit Kapasitas maksimal request/token (contoh: 20)
 * @param int $time_window Waktu dalam detik (contoh: 60)
 * @param string $algorithm Pilihan algoritma: 'fixed_window' atau 'token_bucket'
 * @return bool True jika diizinkan, False jika ditolak
 */
function check_rate_limit($endpoint, $limit = 20, $time_window = 60, $algorithm = 'token_bucket') {
    global $conn;
    $ip_address = get_client_ip();
    
    // =========================================================================
    // L O G I K A   1 :   F I X E D   W I N D O W   C O U N T E R
    // =========================================================================
    if ($algorithm === 'fixed_window') {
        $current_time = date('Y-m-d H:i:s');
        
        $stmt = mysqli_prepare($conn, "SELECT id, request_count, expired_at FROM rate_limit WHERE ip_address = ? AND endpoint = ? AND algorithm = 'fixed_window'");
        mysqli_stmt_bind_param($stmt, "ss", $ip_address, $endpoint);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_assoc($result);
        
        if ($row) {
            if ($current_time > $row['expired_at']) {
                $new_expired = date('Y-m-d H:i:s', time() + $time_window);
                $update = mysqli_prepare($conn, "UPDATE rate_limit SET request_count = 1, expired_at = ? WHERE id = ?");
                mysqli_stmt_bind_param($update, "si", $new_expired, $row['id']);
                mysqli_stmt_execute($update);
                return true;
            } else {
                if ($row['request_count'] >= $limit) {
                    return false; // Limit tercapai
                } else {
                    $update = mysqli_prepare($conn, "UPDATE rate_limit SET request_count = request_count + 1 WHERE id = ?");
                    mysqli_stmt_bind_param($update, "i", $row['id']);
                    mysqli_stmt_execute($update);
                    return true;
                }
            }
        } else {
            $expired_at = date('Y-m-d H:i:s', time() + $time_window);
            $insert = mysqli_prepare($conn, "INSERT INTO rate_limit (ip_address, endpoint, algorithm, request_count, expired_at) VALUES (?, ?, 'fixed_window', 1, ?)");
            mysqli_stmt_bind_param($insert, "sss", $ip_address, $endpoint, $expired_at);
            mysqli_stmt_execute($insert);
            return true;
        }
    } 
    
    // =========================================================================
    // L O G I K A   2 :   T O K E N   B U C K E T
    // =========================================================================
    elseif ($algorithm === 'token_bucket') {
        $current_time = time(); 
        $refill_rate = $limit / $time_window; 
        
        $stmt = mysqli_prepare($conn, "SELECT id, tokens, UNIX_TIMESTAMP(last_refill) as last_refill_ts FROM rate_limit WHERE ip_address = ? AND endpoint = ? AND algorithm = 'token_bucket'");
        mysqli_stmt_bind_param($stmt, "ss", $ip_address, $endpoint);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_assoc($result);

        if ($row) {
            $time_passed = $current_time - $row['last_refill_ts'];
            $tokens_to_add = $time_passed * $refill_rate;
            $current_tokens = min($limit, $row['tokens'] + $tokens_to_add);

            if ($current_tokens >= 1) {
                $current_tokens -= 1;
                $update = mysqli_prepare($conn, "UPDATE rate_limit SET tokens = ?, last_refill = CURRENT_TIMESTAMP WHERE id = ?");
                mysqli_stmt_bind_param($update, "di", $current_tokens, $row['id']);
                mysqli_stmt_execute($update);
                return true; 
            } else {
                $update = mysqli_prepare($conn, "UPDATE rate_limit SET tokens = ?, last_refill = CURRENT_TIMESTAMP WHERE id = ?");
                mysqli_stmt_bind_param($update, "di", $current_tokens, $row['id']);
                mysqli_stmt_execute($update);
                return false; // Limit tercapai
            }
        } else {
            $initial_tokens = $limit - 1; 
            $insert = mysqli_prepare($conn, "INSERT INTO rate_limit (ip_address, endpoint, algorithm, tokens, last_refill) VALUES (?, ?, 'token_bucket', ?, CURRENT_TIMESTAMP)");
            mysqli_stmt_bind_param($insert, "ssd", $ip_address, $endpoint, $initial_tokens);
            mysqli_stmt_execute($insert);
            return true; 
        }
    }
    
    // Jika algoritma tidak dikenali
    return true; 
}
?>