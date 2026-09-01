<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');

function respond(int $statusCode, array $payload): never
{
    http_response_code($statusCode);
    echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function envValue(string $name): string
{
    $value = getenv($name);
    return $value === false ? '' : trim($value);
}

function normalizeText(string $value): string
{
    $value = trim(preg_replace('/\s+/u', ' ', $value) ?? '');
    return function_exists('mb_strtolower')
        ? mb_strtolower($value, 'UTF-8')
        : strtolower($value);
}

/**
 * Mengenali pertanyaan harga atau stok tanpa AI.
 *
 * @return array{0: string, 1: string} [intent, kata kunci barang]
 */
function parseProductQuestion(string $message): array
{
    $cleanMessage = preg_replace('/[^\\p{L}\\p{N}\\s-]+/u', ' ', $message) ?? $message;
    $cleanMessage = normalizeText($cleanMessage);

    if ($cleanMessage === '') {
        return ['', ''];
    }

    $hasPriceIntent = preg_match('/\\b(?:harga|harganya)\\b/u', $cleanMessage) === 1
        || preg_match('/\\bberapa\\s+rupiah\\b/u', $cleanMessage) === 1;

    $hasStockIntent = preg_match('/\\b(?:stok|stoknya|stock|stocknya|tersedia|ketersediaan)\\b/u', $cleanMessage) === 1
        || preg_match('/\\b(?:masih|apakah)\\s+ada\\b/u', $cleanMessage) === 1;

    $intent = $hasPriceIntent ? 'harga' : ($hasStockIntent ? 'stok' : '');
    if ($intent === '') {
        return ['', ''];
    }

    $stopWords = [
        'ada', 'apakah', 'barang', 'berapa', 'bisa', 'boleh', 'buat', 'cek',
        'cekkan', 'coba', 'dong', 'harga', 'harganya', 'ini', 'ingin', 'kak',
        'ketersediaan', 'lagi', 'mas', 'masih', 'mbak', 'mohon', 'mau', 'nya',
        'pak', 'produk', 'saat', 'saya', 'sekarang', 'sih', 'stock', 'stocknya',
        'stok', 'stoknya', 'tersedia', 'tolong', 'untuk', 'ya',
    ];

    $tokens = preg_split('/\\s+/u', $cleanMessage, -1, PREG_SPLIT_NO_EMPTY) ?: [];
    $productTokens = [];

    foreach ($tokens as $token) {
        if (!in_array($token, $stopWords, true)) {
            $productTokens[] = $token;
        }
    }

    return [$intent, trim(implode(' ', $productTokens))];
}

function rupiah(float $value): string
{
    return 'Rp' . number_format($value, 0, ',', '.');
}

function messageAlreadyProcessed(string $key): bool
{
    if ($key === '') {
        return false;
    }

    $directory = sys_get_temp_dir() . '/ewarung_fonnte_webhook';
    if (!is_dir($directory) && !mkdir($directory, 0700, true) && !is_dir($directory)) {
        error_log('Fonnte webhook: failed to create deduplication directory.');
        return false;
    }

    $path = $directory . '/' . hash('sha256', $key) . '.seen';
    $handle = fopen($path, 'c+');
    if ($handle === false) {
        error_log('Fonnte webhook: failed to open deduplication file.');
        return false;
    }

    try {
        if (!flock($handle, LOCK_EX)) {
            return false;
        }

        rewind($handle);
        $existing = trim((string) stream_get_contents($handle));
        if ($existing !== '') {
            return true;
        }

        ftruncate($handle, 0);
        rewind($handle);
        fwrite($handle, (string) time());
        fflush($handle);
        return false;
    } finally {
        flock($handle, LOCK_UN);
        fclose($handle);
    }
}

function sendFonnteMessage(string $target, string $message, ?int $inboxId = null): bool
{
    $token = envValue('FONNTE_TOKEN');
    if ($token === '') {
        error_log('Fonnte webhook: FONNTE_TOKEN is not configured.');
        return false;
    }

    $postFields = [
        'target' => $target,
        'message' => $message,
        'countryCode' => '0',
    ];

    if ($inboxId !== null && $inboxId > 0) {
        $postFields['inboxid'] = (string) $inboxId;
    }

    $curl = curl_init('https://api.fonnte.com/send');
    if ($curl === false) {
        error_log('Fonnte webhook: failed to initialize cURL.');
        return false;
    }

    curl_setopt_array($curl, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => $postFields,
        CURLOPT_HTTPHEADER => ['Authorization: ' . $token],
        CURLOPT_CONNECTTIMEOUT => 5,
        CURLOPT_TIMEOUT => 15,
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_SSL_VERIFYHOST => 2,
    ]);

    $response = curl_exec($curl);
    $curlError = curl_error($curl);
    $httpCode = (int) curl_getinfo($curl, CURLINFO_HTTP_CODE);
    curl_close($curl);

    if ($response === false || $curlError !== '') {
        error_log('Fonnte webhook send failed: ' . $curlError);
        return false;
    }

    $decoded = json_decode((string) $response, true);
    if ($httpCode < 200 || $httpCode >= 300 || !is_array($decoded) || ($decoded['status'] ?? false) !== true) {
        $safeReason = is_array($decoded) ? (string) ($decoded['reason'] ?? $decoded['detail'] ?? 'unknown') : 'invalid response';
        error_log('Fonnte webhook send rejected. HTTP ' . $httpCode . ', reason: ' . $safeReason);
        return false;
    }

    return true;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Allow: POST');
    respond(405, ['status' => false, 'message' => 'Method not allowed.']);
}

$contentLength = isset($_SERVER['CONTENT_LENGTH']) ? (int) $_SERVER['CONTENT_LENGTH'] : 0;
if ($contentLength > 65536) {
    respond(413, ['status' => false, 'message' => 'Payload terlalu besar.']);
}

$rawBody = file_get_contents('php://input');
if ($rawBody === false || $rawBody === '') {
    respond(400, ['status' => false, 'message' => 'Payload kosong.']);
}

$data = json_decode($rawBody, true);
if (!is_array($data)) {
    respond(400, ['status' => false, 'message' => 'JSON tidak valid.']);
}

$configuredSecret = envValue('FONNTE_WEBHOOK_SECRET');
if ($configuredSecret === '') {
    error_log('Fonnte webhook: FONNTE_WEBHOOK_SECRET is not configured.');
    respond(503, ['status' => false, 'message' => 'Webhook belum dikonfigurasi.']);
}

$receivedSecret = trim((string) (
    $data['webhook-secret-key']
    ?? $data['secret']
    ?? $data['secret_key']
    ?? $data['secretkey']
    ?? $data['secretKey']
    ?? ''
));
if ($receivedSecret === '' || !hash_equals($configuredSecret, $receivedSecret)) {
    $payloadKeys = [];
    foreach (array_keys($data) as $payloadKey) {
        $payloadKeys[] = preg_replace('/[^A-Za-z0-9_.-]/', '?', (string) $payloadKey) ?? '?';
    }

    error_log(
        'Fonnte webhook authorization rejected. Payload keys=' . implode(',', $payloadKeys)
        . '; received_secret_length=' . strlen($receivedSecret)
        . '; configured_secret_length=' . strlen($configuredSecret)
    );
    respond(403, ['status' => false, 'message' => 'Webhook tidak terotorisasi.']);
}

$device = trim((string) ($data['device'] ?? ''));
$configuredDevice = preg_replace('/\D+/', '', envValue('FONNTE_DEVICE')) ?? '';
$payloadDevice = preg_replace('/\D+/', '', $device) ?? '';
if ($configuredDevice !== '' && $payloadDevice !== $configuredDevice) {
    respond(403, ['status' => false, 'message' => 'Perangkat tidak sesuai.']);
}

$sender = trim((string) ($data['sender'] ?? ''));
if ($sender === '') {
    $sender = trim((string) ($data['pengirim'] ?? ''));
}

$message = normalizeText((string) ($data['message'] ?? ''));
if ($message === '') {
    $message = normalizeText((string) ($data['pesan'] ?? ''));
}

$member = trim((string) ($data['member'] ?? ''));
$timestamp = trim((string) ($data['timestamp'] ?? ''));
$inboxId = isset($data['inboxid']) && is_numeric($data['inboxid']) ? (int) $data['inboxid'] : null;
if ($inboxId !== null && $inboxId <= 0) {
    $inboxId = null;
}

if ($sender === '' || $message === '') {
    error_log(
        'Fonnte webhook ignored empty payload.'
        . ' sender_length=' . strlen(trim((string) ($data['sender'] ?? '')))
        . '; pengirim_length=' . strlen(trim((string) ($data['pengirim'] ?? '')))
        . '; message_length=' . strlen(trim((string) ($data['message'] ?? '')))
        . '; pesan_length=' . strlen(trim((string) ($data['pesan'] ?? '')))
    );
    respond(200, ['status' => true, 'action' => 'ignored_empty']);
}

if (strlen($sender) > 80 || preg_match('/^[0-9@._:-]+$/', $sender) !== 1) {
    respond(200, ['status' => true, 'action' => 'ignored_sender']);
}

// Pesan grup memiliki nilai member. Chatbot ini hanya melayani pesan personal.
if ($member !== '') {
    respond(200, ['status' => true, 'action' => 'ignored_group']);
}

$senderDigits = preg_replace('/\D+/', '', $sender) ?? '';
if ($payloadDevice !== '' && $senderDigits === $payloadDevice) {
    respond(200, ['status' => true, 'action' => 'ignored_self']);
}

$reply = '';
$command = '';
$keyword = '';

if (in_array($message, ['menu', 'bantuan', 'help'], true)) {
    $reply = "*Chatbot Toko Podomoro*\n\n"
        . "Anda dapat bertanya dengan bahasa sederhana.\n\n"
        . "Contoh:\n"
        . "- harga minyak\n"
        . "- berapa harga telur sekarang?\n"
        . "- stok beras\n"
        . "- apakah susu masih tersedia?";
} else {
    [$command, $keyword] = parseProductQuestion($message);

    if ($command === '') {
        // Pesan checkout dan percakapan biasa tetap ditangani admin secara manual.
        respond(200, ['status' => true, 'action' => 'ignored_non_command']);
    }

    if ($keyword === '') {
        $reply = 'Sebutkan nama barang yang ingin diperiksa. Contoh: berapa harga telur sekarang?';
    }
}

if ($keyword !== '') {
    $keywordLength = function_exists('mb_strlen') ? mb_strlen($keyword, 'UTF-8') : strlen($keyword);
    if ($keywordLength < 2 || $keywordLength > 80) {
        $reply = 'Nama barang harus terdiri dari 2 sampai 80 karakter.';
    } else {
        require_once __DIR__ . '/../../config/koneksi.php';

        $sql = "SELECT b.nama_barang, b.stok, v.nama_satuan, v.harga, v.isi_per_satuan
                FROM barang b
                INNER JOIN varian_barang v ON v.id_barang = b.id_barang
                WHERE b.nama_barang LIKE CONCAT('%', ?, '%')
                ORDER BY b.nama_barang ASC, v.harga ASC
                LIMIT 12";

        $statement = mysqli_prepare($conn, $sql);
        if ($statement === false) {
            error_log('Fonnte webhook query preparation failed: ' . mysqli_error($conn));
            respond(500, ['status' => false, 'message' => 'Kesalahan basis data.']);
        }

        mysqli_stmt_bind_param($statement, 's', $keyword);
        mysqli_stmt_execute($statement);
        $result = mysqli_stmt_get_result($statement);

        $products = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $productName = (string) $row['nama_barang'];
            if (!isset($products[$productName])) {
                $products[$productName] = [
                    'stock' => (int) $row['stok'],
                    'variants' => [],
                ];
            }
            $products[$productName]['variants'][] = [
                'unit' => (string) $row['nama_satuan'],
                'price' => (float) $row['harga'],
                'contents' => max(1, (int) $row['isi_per_satuan']),
            ];
        }
        mysqli_stmt_close($statement);

        if ($products === []) {
            $reply = 'Maaf, barang "' . $keyword . '" tidak ditemukan. Coba gunakan nama yang lebih singkat.';
        } elseif ($command === 'harga') {
            $lines = ["*Hasil harga: " . $keyword . "*"];
            foreach ($products as $productName => $product) {
                $lines[] = '';
                $lines[] = '*' . $productName . '*';
                foreach ($product['variants'] as $variant) {
                    $lines[] = '- ' . $variant['unit'] . ': ' . rupiah($variant['price']);
                }
            }
            $lines[] = '';
            $lines[] = 'Harga dapat berubah mengikuti pembaruan toko.';
            $reply = implode("\n", $lines);
        } else {
            $lines = ["*Hasil stok: " . $keyword . "*"];
            foreach ($products as $productName => $product) {
                $lines[] = '';
                $lines[] = '*' . $productName . '*';
                $lines[] = 'Stok fisik: ' . $product['stock'] . ' unit terkecil';
                foreach ($product['variants'] as $variant) {
                    $available = intdiv($product['stock'], $variant['contents']);
                    $lines[] = '- Maks. ' . $available . ' ' . $variant['unit']
                        . ' (isi ' . $variant['contents'] . ')';
                }
            }
            $lines[] = '';
            $lines[] = 'Jumlah tersebut adalah perkiraan berdasarkan stok fisik saat ini.';
            $reply = implode("\n", $lines);
        }
    }
}

$deduplicationKey = $inboxId !== null
    ? 'inbox:' . $inboxId
    : ($timestamp !== '' ? 'message:' . $sender . '|' . $timestamp . '|' . $message : '');

if (messageAlreadyProcessed($deduplicationKey)) {
    respond(200, ['status' => true, 'action' => 'ignored_duplicate']);
}

$sent = sendFonnteMessage($sender, $reply, $inboxId);
if (!$sent) {
    // Tetap 200 agar Fonnte tidak mengulang request sampai berkali-kali dan mengirim balasan ganda.
    respond(200, ['status' => false, 'action' => 'send_failed']);
}

respond(200, ['status' => true, 'action' => 'replied']);
