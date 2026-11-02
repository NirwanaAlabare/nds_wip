-- =====================================================================
-- Master Data: kolom baru master supplier
-- Jalankan di database Signalbit (koneksi mysql_sb) sekaligus satu file (Execute all). Aman dijalankan ulang.
-- Kolom baru boleh kosong dan insert Signalbit menyebut kolomnya satu per satu,
-- jadi insert / update dari Signalbit tetap jalan seperti biasa.
-- Selama kolom-kolom ini belum ada, isian barunya tidak ditampilkan di form NDS.
-- Data lama tidak diubah.
-- Butuh kolom prioritas dari 2026_10_14_mastersupplier_prioritas.sql (sudah ada di server); kolom itu
-- diganti nama jadi payment_rating, isinya tetap.
-- =====================================================================

-- 1. Semua perubahan tabel mastersupplier dalam satu ALTER
ALTER TABLE `mastersupplier`
  -- Priority diganti Payment Flexibility Rating
  CHANGE COLUMN IF EXISTS `prioritas` `payment_rating` VARCHAR(10) NULL DEFAULT NULL
    COMMENT 'Payment Flexibility Rating: RED / YELLOW / GREEN; NULL = belum diisi',
  ADD COLUMN IF NOT EXISTS `legal_name` VARCHAR(150) NULL DEFAULT NULL
    COMMENT 'Legal Name: nama resmi perusahaan',
  ADD COLUMN IF NOT EXISTS `nib` VARCHAR(20) NULL DEFAULT NULL
    COMMENT 'NIB: Nomor Induk Berusaha (13 digit)',
  ADD COLUMN IF NOT EXISTS `status_aktif` VARCHAR(10) NULL DEFAULT NULL
    COMMENT 'Supplier Status: ACTIVE / INACTIVE / BLOCKED. non_aktif tetap diisi untuk Signalbit (0 = Active, 1 = Inactive / Blocked); NULL = data lama, statusnya dari non_aktif',
  ADD COLUMN IF NOT EXISTS `blocked_reason` VARCHAR(255) NULL DEFAULT NULL
    COMMENT 'Blocked Reason: alasan pemblokiran, wajib diisi saat status BLOCKED',
  ADD COLUMN IF NOT EXISTS `payment_review_date` DATE NULL DEFAULT NULL
    COMMENT 'Payment Flexibility Last Review Date',
  ADD COLUMN IF NOT EXISTS `verified_by` VARCHAR(100) NULL DEFAULT NULL
    COMMENT 'Verified By: diisi dari menu verifikasi (belum dibuat)',
  ADD COLUMN IF NOT EXISTS `verified_date` DATETIME NULL DEFAULT NULL
    COMMENT 'Verified Date: diisi dari menu verifikasi (belum dibuat)' AFTER `verified_by`,
  ADD COLUMN IF NOT EXISTS `approved_by` VARCHAR(100) NULL DEFAULT NULL
    COMMENT 'Approved By: diisi dari menu approval (belum dibuat)',
  ADD COLUMN IF NOT EXISTS `approved_date` DATETIME NULL DEFAULT NULL
    COMMENT 'Approved Date: diisi dari menu approval (belum dibuat)' AFTER `approved_by`,
  ADD COLUMN IF NOT EXISTS `modified_by` VARCHAR(100) NULL DEFAULT NULL
    COMMENT 'Last Modified By: user terakhir yang create / edit / ubah status dari NDS',
  ADD COLUMN IF NOT EXISTS `modified_date` DATETIME NULL DEFAULT NULL
    COMMENT 'Last Modified Date: waktu terakhir create / edit / ubah status dari NDS',
  -- Customs Classification: KITE PENGEMBALIAN 17 huruf, kolom lama hanya 5
  MODIFY COLUMN `jenis_fasilitas` VARCHAR(30) DEFAULT '';

-- 2. Pilihan baru di combo Signalbit (form master_supcust.php) - tabel lain, jadi statement sendiri.
-- Tanpa ini, edit dari Signalbit mengosongkan Fasilitas / Status PKP yang nilainya tidak ada di pilihan. KITE lama tetap ada.
INSERT IGNORE INTO `masterpilihan` (`kode_pilihan`, `nama_pilihan`) VALUES
  ('J_FAS', 'PLB'),
  ('J_FAS', 'KITE PEMBEBASAN'),
  ('J_FAS', 'KITE PENGEMBALIAN'),
  ('J_FAS', 'KEK'),
  ('J_FAS', 'KPBPB'),
  ('J_FAS', 'OVERSEAS'),
  ('PKP', 'NA');
