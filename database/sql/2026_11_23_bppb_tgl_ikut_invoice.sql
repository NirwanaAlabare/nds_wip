-- =====================================================================
-- Invoice EXIM: tanggal SJ (bppb.bppbdate) ikut Invoice Date
-- Jalankan di database Signalbit (koneksi mysql_sb) sekaligus satu file (Execute all). Aman dijalankan ulang.
--
-- Invoice Date di Create/Edit Invoice Local & Export sekarang bisa diubah. Waktu invoice disimpan,
-- bppbdate SJ-nya ikut diganti ke Invoice Date. Tanggal SJ yang asli disimpan SEKALI di bppbdate_ori
-- dan tidak pernah ditimpa lagi:
--   SJ 1 Nov -> invoice dibuat 2 Nov -> invoice di-edit 3 Nov  =>  bppbdate 3 Nov, bppbdate_ori tetap 1 Nov.
-- create_invoice_by / create_invoice_date = user & waktu SJ itu dimasukkan ke invoice.
--
-- Selama kolom-kolom ini belum ada, NDS menolak Invoice Date yang berbeda dari tanggal SJ
-- (tanggal SJ tidak diubah tanpa cadangan tanggal aslinya).
-- Kolom baru boleh kosong, dan semua insert ke bppb (Signalbit & NDS) menyebut kolomnya satu per satu,
-- jadi tetap jalan seperti biasa. Data lama tidak diubah.
--
-- ALGORITHM=INSTANT: kolom ditambah tanpa menyalin ulang tabel bppb (±665 ribu baris), jadi tabel tidak
-- terkunci lama. Kalau server menolak INSTANT, berhenti dulu - jangan dijalankan tanpa INSTANT di jam kerja.
-- =====================================================================

ALTER TABLE `bppb`
  ADD COLUMN IF NOT EXISTS `bppbdate_ori` DATE NULL DEFAULT NULL
    COMMENT 'Tanggal SJ asli sebelum diubah lewat Invoice EXIM; diisi sekali, tidak ditimpa' AFTER `bppbdate`,
  ADD COLUMN IF NOT EXISTS `create_invoice_by` VARCHAR(100) NULL DEFAULT NULL
    COMMENT 'User yang memasukkan SJ ini ke invoice EXIM' AFTER `bppbdate_ori`,
  ADD COLUMN IF NOT EXISTS `create_invoice_date` DATETIME NULL DEFAULT NULL
    COMMENT 'Waktu SJ ini dimasukkan ke invoice EXIM' AFTER `create_invoice_by`,
  ALGORITHM=INSTANT;

-- Cek: harus keluar 3 baris
SELECT COLUMN_NAME, COLUMN_TYPE, IS_NULLABLE
  FROM information_schema.COLUMNS
 WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'bppb'
   AND COLUMN_NAME IN ('bppbdate_ori', 'create_invoice_by', 'create_invoice_date');

-- Kalau suatu saat tanggal SJ perlu dikembalikan ke aslinya (contoh satu SJ):
--   UPDATE bppb SET bppbdate = bppbdate_ori WHERE bppbno_int = 'FG/OUT/1126/00001' AND bppbdate_ori IS NOT NULL;
