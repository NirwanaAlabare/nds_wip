-- =====================================================================
-- Master Data: riwayat customer / supplier (Create, Edit, Active/Inactive)
-- Jalankan di database Signalbit (koneksi mysql_sb) SEBELUM deploy kode tab History.
-- Sesudah tabel dibuat, salin riwayat lama dari act_hist sekali saja:
--     php artisan masterdata:sync-history
-- =====================================================================

CREATE TABLE IF NOT EXISTS `mastersupplier_log` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `id_supplier` bigint(20) NOT NULL COMMENT 'mastersupplier.Id_Supplier (0 = baris penanda sinkron act_hist)',
  `aksi` varchar(10) NOT NULL COMMENT 'CREATE / UPDATE / ACTIVE / INACTIVE / SYNC',
  `isi` longtext DEFAULT NULL COMMENT 'JSON kolom => nilai: data saat dibuat, atau data yang disimpan oleh edit dari luar NDS',
  `perubahan` longtext DEFAULT NULL COMMENT 'JSON kolom => [lama, baru]: edit dari NDS',
  `act_hist_id` bigint(20) DEFAULT NULL COMMENT 'act_hist.id asal catatan - mencegah catatan dobel waktu sinkron',
  `created_by` varchar(100) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_mslog_act_hist` (`act_hist_id`),
  KEY `idx_mslog_supplier` (`id_supplier`, `created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Riwayat master customer / supplier';
