-- Struktur database E-Warung Toko Podomoro
-- Berisi struktur tabel tanpa data user, nomor WhatsApp, password, atau transaksi.
-- Gunakan pada database kosong untuk instalasi lokal/pengujian.

CREATE DATABASE IF NOT EXISTS `ewarung_podomoro`
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE `ewarung_podomoro`;

CREATE TABLE IF NOT EXISTS `users` (
  `id_user` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `nama` VARCHAR(100) NOT NULL,
  `email` VARCHAR(100) NOT NULL,
  `password` VARCHAR(255) NOT NULL,
  `no_hp` VARCHAR(15) NOT NULL,
  `role` ENUM('admin', 'user') NOT NULL DEFAULT 'user',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_user`),
  UNIQUE KEY `uq_users_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `barang` (
  `id_barang` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `nama_barang` VARCHAR(150) NOT NULL,
  `kategori` VARCHAR(50) NOT NULL,
  `stok` INT UNSIGNED NOT NULL DEFAULT 0,
  `gambar` VARCHAR(255) DEFAULT NULL,
  PRIMARY KEY (`id_barang`),
  KEY `idx_barang_nama` (`nama_barang`),
  KEY `idx_barang_kategori` (`kategori`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `varian_barang` (
  `id_varian` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `id_barang` INT UNSIGNED NOT NULL,
  `nama_satuan` VARCHAR(50) NOT NULL,
  `harga` DECIMAL(10,2) UNSIGNED NOT NULL,
  `isi_per_satuan` INT UNSIGNED NOT NULL DEFAULT 1,
  PRIMARY KEY (`id_varian`),
  UNIQUE KEY `uq_varian_barang_satuan` (`id_barang`, `nama_satuan`),
  KEY `idx_varian_id_barang` (`id_barang`),
  CONSTRAINT `fk_varian_barang`
    FOREIGN KEY (`id_barang`) REFERENCES `barang` (`id_barang`)
    ON UPDATE CASCADE
    ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `cart` (
  `id_cart` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `id_user` INT UNSIGNED NOT NULL,
  `id_varian` INT UNSIGNED NOT NULL,
  `qty` INT UNSIGNED NOT NULL DEFAULT 1,
  PRIMARY KEY (`id_cart`),
  UNIQUE KEY `uq_cart_user_varian` (`id_user`, `id_varian`),
  KEY `idx_cart_id_user` (`id_user`),
  KEY `idx_cart_id_varian` (`id_varian`),
  CONSTRAINT `fk_cart_user`
    FOREIGN KEY (`id_user`) REFERENCES `users` (`id_user`)
    ON UPDATE CASCADE
    ON DELETE CASCADE,
  CONSTRAINT `fk_cart_varian`
    FOREIGN KEY (`id_varian`) REFERENCES `varian_barang` (`id_varian`)
    ON UPDATE CASCADE
    ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `transaksi` (
  `id_transaksi` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `id_user` INT UNSIGNED NOT NULL,
  `total_harga` DECIMAL(10,2) UNSIGNED NOT NULL DEFAULT 0.00,
  `tanggal` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `status` ENUM('pending', 'selesai', 'batal') NOT NULL DEFAULT 'pending',
  PRIMARY KEY (`id_transaksi`),
  KEY `idx_transaksi_id_user` (`id_user`),
  KEY `idx_transaksi_tanggal` (`tanggal`),
  KEY `idx_transaksi_status` (`status`),
  CONSTRAINT `fk_transaksi_user`
    FOREIGN KEY (`id_user`) REFERENCES `users` (`id_user`)
    ON UPDATE CASCADE
    ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `detail_transaksi` (
  `id_detail` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `id_transaksi` INT UNSIGNED NOT NULL,
  `id_varian` INT UNSIGNED NOT NULL,
  `jumlah` INT UNSIGNED NOT NULL,
  `subtotal` DECIMAL(10,2) UNSIGNED NOT NULL,
  PRIMARY KEY (`id_detail`),
  KEY `idx_detail_id_transaksi` (`id_transaksi`),
  KEY `idx_detail_id_varian` (`id_varian`),
  CONSTRAINT `fk_detail_transaksi`
    FOREIGN KEY (`id_transaksi`) REFERENCES `transaksi` (`id_transaksi`)
    ON UPDATE CASCADE
    ON DELETE CASCADE,
  CONSTRAINT `fk_detail_varian`
    FOREIGN KEY (`id_varian`) REFERENCES `varian_barang` (`id_varian`)
    ON UPDATE CASCADE
    ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `rate_limit` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `ip_address` VARCHAR(45) NOT NULL,
  `endpoint` VARCHAR(255) NOT NULL,
  `algorithm` ENUM('fixed_window', 'token_bucket') NOT NULL,
  `request_count` INT UNSIGNED NOT NULL DEFAULT 0,
  `expired_at` DATETIME DEFAULT NULL,
  `tokens` DECIMAL(10,4) UNSIGNED NOT NULL DEFAULT 0.0000,
  `last_refill` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_rate_limit_identity` (`ip_address`, `endpoint`, `algorithm`),
  KEY `idx_rate_limit_expired_at` (`expired_at`),
  KEY `idx_rate_limit_last_refill` (`last_refill`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

