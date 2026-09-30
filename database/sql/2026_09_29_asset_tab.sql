-- =====================================================================
-- Asset Tab: perbaikan RFID terpotong, riwayat Repair, dan Opname Tab
-- Jalankan SEBELUM deploy kode Monitoring Tab / Opname Tab / Repair.
-- =====================================================================

-- 0. Cek dulu: 20 karakter awal RFID di master harus unik supaya kode yang terpotong bisa dipulihkan.
--    Hasil "dobel" harus 0. Kalau tidak 0, baris dengan prefix dobel tidak akan diubah oleh langkah 2.
SELECT COUNT(*) AS total, COUNT(DISTINCT LEFT(rfid_code, 20)) AS prefix_unik,
       COUNT(*) - COUNT(DISTINCT LEFT(rfid_code, 20)) AS dobel
FROM asset_master_tab;

-- 1. Kolom RFID transaksi cuma 20 karakter, padahal RFID di master 24 karakter,
--    sehingga kode tersimpan terpotong dan riwayat tidak pernah cocok dengan master.
ALTER TABLE asset_trans_tab MODIFY rfid_code VARCHAR(30) NULL;

-- 2. Pulihkan kode RFID yang sudah terlanjur terpotong (hanya yang cocok dengan tepat 1 tab di master)
UPDATE asset_trans_tab tt
JOIN (
    SELECT LEFT(rfid_code, 20) AS prefix, MIN(rfid_code) AS rfid_code
    FROM asset_master_tab
    GROUP BY LEFT(rfid_code, 20)
    HAVING COUNT(*) = 1
) mt ON mt.prefix = tt.rfid_code
SET tt.rfid_code = mt.rfid_code
WHERE LENGTH(tt.rfid_code) = 20 AND LENGTH(mt.rfid_code) > 20;

-- 3. Riwayat Repair & koreksi Opname ikut tercatat di asset_trans_tab
ALTER TABLE asset_trans_tab
    MODIFY status ENUM('AMBIL', 'KEMBALI', 'REPAIR', 'SELESAI_REPAIR') NULL,
    MODIFY tipe_input ENUM('SINGLE', 'BULK', 'OPNAME', 'MASTER') NULL,
    ADD COLUMN keterangan VARCHAR(255) NULL AFTER tipe_input,
    ADD INDEX idx_asset_trans_tab_tgl (tgl_trans),
    -- dipakai untuk mencari transaksi AMBIL terakhir per tab (siapa pemegang tab sekarang)
    ADD INDEX idx_asset_trans_tab_rfid_status (rfid_code, status);

-- 4. Hasil Opname Tab
CREATE TABLE IF NOT EXISTS asset_opname_tab (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    no_opname VARCHAR(30) NOT NULL,
    tgl_opname DATETIME NOT NULL,
    total_tab INT NOT NULL DEFAULT 0,
    total_idle INT NOT NULL DEFAULT 0,
    total_ada INT NOT NULL DEFAULT 0,
    total_tidak_ada INT NOT NULL DEFAULT 0,
    total_belum_kembali INT NOT NULL DEFAULT 0,
    total_repair_ada INT NOT NULL DEFAULT 0,
    total_tidak_terdaftar INT NOT NULL DEFAULT 0,
    keterangan VARCHAR(255) NULL,
    created_by VARCHAR(255) NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_asset_opname_tab_no (no_opname)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS asset_opname_tab_det (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    id_opname BIGINT UNSIGNED NOT NULL,
    rfid_code VARCHAR(30) NULL,
    line_code VARCHAR(20) NULL,
    tab_code VARCHAR(255) NULL,
    status_sistem VARCHAR(10) NULL,
    lokasi_sistem VARCHAR(255) NULL,
    terbaca TINYINT(1) NOT NULL DEFAULT 0,
    -- ADA | TIDAK_ADA | BELUM_KEMBALI | DI_LAPANGAN | REPAIR_ADA | REPAIR
    hasil VARCHAR(20) NOT NULL,
    created_at TIMESTAMP NULL,
    PRIMARY KEY (id),
    KEY idx_asset_opname_tab_det_opname (id_opname)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_general_ci;
