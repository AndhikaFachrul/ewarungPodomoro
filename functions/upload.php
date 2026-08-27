<?php

function save_product_image($file, $upload_directory)
{
    if (!isset($file) || ($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        return null;
    }

    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
        throw new RuntimeException('Upload gambar gagal. Silakan coba kembali.');
    }

    $max_size = 2 * 1024 * 1024;
    if (($file['size'] ?? 0) <= 0 || $file['size'] > $max_size) {
        throw new RuntimeException('Ukuran gambar maksimal 2 MB.');
    }

    $temporary_path = $file['tmp_name'] ?? '';
    if (!is_uploaded_file($temporary_path) || getimagesize($temporary_path) === false) {
        throw new RuntimeException('File yang dipilih bukan gambar yang valid.');
    }

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime_type = $finfo->file($temporary_path);
    $allowed_types = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
    ];

    if (!isset($allowed_types[$mime_type])) {
        throw new RuntimeException('Format gambar harus JPG, PNG, atau WEBP.');
    }

    if (!is_dir($upload_directory) || !is_writable($upload_directory)) {
        throw new RuntimeException('Folder penyimpanan gambar tidak tersedia.');
    }

    $filename = bin2hex(random_bytes(16)) . '.' . $allowed_types[$mime_type];
    $destination = rtrim($upload_directory, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $filename;

    if (!move_uploaded_file($temporary_path, $destination)) {
        throw new RuntimeException('Gambar gagal disimpan.');
    }

    return $filename;
}

function delete_product_image($filename, $upload_directory)
{
    if (empty($filename) || $filename === 'default.jpg' || basename($filename) !== $filename) {
        return;
    }

    $path = rtrim($upload_directory, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $filename;
    if (is_file($path)) {
        unlink($path);
    }
}

