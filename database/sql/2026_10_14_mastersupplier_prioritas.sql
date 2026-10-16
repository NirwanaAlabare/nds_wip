-- =====================================================================
-- Master Data: prioritas supplier (High / Medium / Low)
-- Jalankan di database Signalbit (koneksi mysql_sb).
-- Kolom baru boleh kosong, jadi insert / update dari Signalbit tetap jalan seperti biasa.
-- Selama kolom ini belum ada, isian Priority tidak ditampilkan di form NDS.
-- =====================================================================

ALTER TABLE `mastersupplier`
  ADD COLUMN IF NOT EXISTS `prioritas` VARCHAR(10) NULL DEFAULT NULL
  COMMENT 'Prioritas supplier: HIGH (merah) / MEDIUM (kuning) / LOW (hijau); NULL = belum diisi';
