-- =====================================================================
-- Backfill penerimaan_cutting untuk BPPB ke Cutting
-- Rentang : 2026-01-01 <= tgl_bppb < 2026-10-01 (dipecah per bulan)
-- Logika  : sama dengan command penerimaan-cutting:backfill (tanpa scanned_item)
--   - tujuan 'Production - Cutting', no_bppb NOT LIKE 'MT/%'
--   - status != 'N' (NULL dianggap 'Y')
--   - hanya whs_bppb_det yang belum punya penerimaan_cutting
--   - tanggal_terima = tgl_bppb, created_at = tgl_bppb 00:00:00
--   - YRD/YARD -> METER (x 0.9144, round 2), satuan lain apa adanya (UPPER TRIM)
--   - penanda: created_by_username = 'backfill'
--
-- Cara pakai: jalankan BAGIAN 0 dulu (cek), lalu BAGIAN 1 satu per satu.
-- Setiap INSERT berdiri sendiri (autocommit) -> aman bila terhenti; jalankan
-- ulang bulan yang sama tidak membuat data ganda karena ada NOT EXISTS.
-- =====================================================================


-- ---------------------------------------------------------------------
-- BAGIAN 0. Cek jumlah per bulan (read-only)
-- ---------------------------------------------------------------------
SELECT
    DATE_FORMAT(h.tgl_bppb, '%Y-%m') AS bulan,
    COUNT(*)                         AS baris,
    COUNT(DISTINCT d.id_roll)        AS roll
FROM signalbit_erp.whs_bppb_det d
JOIN signalbit_erp.whs_bppb_h h ON h.no_bppb = d.no_bppb
WHERE h.tujuan = 'Production - Cutting'
  AND d.no_bppb NOT LIKE 'MT/%'
  AND h.tgl_bppb >= '2026-01-01' AND h.tgl_bppb < '2026-10-01'
  AND COALESCE(d.status, 'Y') <> 'N'
  AND NOT EXISTS (SELECT 1 FROM laravel_nds.penerimaan_cutting pc WHERE pc.whs_bppb_det_id = d.id)
GROUP BY DATE_FORMAT(h.tgl_bppb, '%Y-%m')
ORDER BY bulan;


-- ---------------------------------------------------------------------
-- (Opsional) kurangi penguncian baris sumber selama INSERT ... SELECT.
-- Hanya berlaku bila binlog_format = ROW/MIXED; bila STATEMENT, INSERT akan
-- error -> lewati baris ini saja.
-- ---------------------------------------------------------------------
-- SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;


-- ---------------------------------------------------------------------
-- BAGIAN 1. Insert per bulan. Ganti dua tanggal di WHERE untuk tiap bulan:
--   2026-01-01 .. 2026-02-01
--   2026-02-01 .. 2026-03-01
--   2026-03-01 .. 2026-04-01
--   2026-04-01 .. 2026-05-01
--   2026-05-01 .. 2026-06-01
--   2026-06-01 .. 2026-07-01
--   2026-07-01 .. 2026-08-01
--   2026-08-01 .. 2026-09-01
--   2026-09-01 .. 2026-10-01
-- ---------------------------------------------------------------------

-- Januari 2026
INSERT INTO laravel_nds.penerimaan_cutting
    (tanggal_terima, whs_bppb_det_id, id_roll, qty_konv, unit_konv,
     created_by, created_by_username, created_at, updated_at)
SELECT
    DATE(h.tgl_bppb),
    d.id,
    d.id_roll,
    CASE WHEN UPPER(TRIM(d.satuan)) IN ('YRD', 'YARD')
         THEN ROUND(d.qty_out * 0.9144, 2) ELSE d.qty_out END,
    CASE WHEN UPPER(TRIM(d.satuan)) IN ('YRD', 'YARD')
         THEN 'METER' ELSE UPPER(TRIM(d.satuan)) END,
    NULL,
    'backfill',
    CONCAT(DATE(h.tgl_bppb), ' 00:00:00'),
    NOW()
FROM signalbit_erp.whs_bppb_det d
JOIN signalbit_erp.whs_bppb_h h ON h.no_bppb = d.no_bppb
WHERE h.tujuan = 'Production - Cutting'
  AND d.no_bppb NOT LIKE 'MT/%'
  AND h.tgl_bppb >= '2026-01-01' AND h.tgl_bppb < '2026-02-01'
  AND COALESCE(d.status, 'Y') <> 'N'
  AND NOT EXISTS (SELECT 1 FROM laravel_nds.penerimaan_cutting pc WHERE pc.whs_bppb_det_id = d.id)
ORDER BY d.id;

-- Februari 2026
INSERT INTO laravel_nds.penerimaan_cutting
    (tanggal_terima, whs_bppb_det_id, id_roll, qty_konv, unit_konv,
     created_by, created_by_username, created_at, updated_at)
SELECT DATE(h.tgl_bppb), d.id, d.id_roll,
    CASE WHEN UPPER(TRIM(d.satuan)) IN ('YRD', 'YARD') THEN ROUND(d.qty_out * 0.9144, 2) ELSE d.qty_out END,
    CASE WHEN UPPER(TRIM(d.satuan)) IN ('YRD', 'YARD') THEN 'METER' ELSE UPPER(TRIM(d.satuan)) END,
    NULL, 'backfill', CONCAT(DATE(h.tgl_bppb), ' 00:00:00'), NOW()
FROM signalbit_erp.whs_bppb_det d
JOIN signalbit_erp.whs_bppb_h h ON h.no_bppb = d.no_bppb
WHERE h.tujuan = 'Production - Cutting' AND d.no_bppb NOT LIKE 'MT/%'
  AND h.tgl_bppb >= '2026-02-01' AND h.tgl_bppb < '2026-03-01'
  AND COALESCE(d.status, 'Y') <> 'N'
  AND NOT EXISTS (SELECT 1 FROM laravel_nds.penerimaan_cutting pc WHERE pc.whs_bppb_det_id = d.id)
ORDER BY d.id;

-- Maret 2026
INSERT INTO laravel_nds.penerimaan_cutting
    (tanggal_terima, whs_bppb_det_id, id_roll, qty_konv, unit_konv,
     created_by, created_by_username, created_at, updated_at)
SELECT DATE(h.tgl_bppb), d.id, d.id_roll,
    CASE WHEN UPPER(TRIM(d.satuan)) IN ('YRD', 'YARD') THEN ROUND(d.qty_out * 0.9144, 2) ELSE d.qty_out END,
    CASE WHEN UPPER(TRIM(d.satuan)) IN ('YRD', 'YARD') THEN 'METER' ELSE UPPER(TRIM(d.satuan)) END,
    NULL, 'backfill', CONCAT(DATE(h.tgl_bppb), ' 00:00:00'), NOW()
FROM signalbit_erp.whs_bppb_det d
JOIN signalbit_erp.whs_bppb_h h ON h.no_bppb = d.no_bppb
WHERE h.tujuan = 'Production - Cutting' AND d.no_bppb NOT LIKE 'MT/%'
  AND h.tgl_bppb >= '2026-03-01' AND h.tgl_bppb < '2026-04-01'
  AND COALESCE(d.status, 'Y') <> 'N'
  AND NOT EXISTS (SELECT 1 FROM laravel_nds.penerimaan_cutting pc WHERE pc.whs_bppb_det_id = d.id)
ORDER BY d.id;

-- April 2026
INSERT INTO laravel_nds.penerimaan_cutting
    (tanggal_terima, whs_bppb_det_id, id_roll, qty_konv, unit_konv,
     created_by, created_by_username, created_at, updated_at)
SELECT DATE(h.tgl_bppb), d.id, d.id_roll,
    CASE WHEN UPPER(TRIM(d.satuan)) IN ('YRD', 'YARD') THEN ROUND(d.qty_out * 0.9144, 2) ELSE d.qty_out END,
    CASE WHEN UPPER(TRIM(d.satuan)) IN ('YRD', 'YARD') THEN 'METER' ELSE UPPER(TRIM(d.satuan)) END,
    NULL, 'backfill', CONCAT(DATE(h.tgl_bppb), ' 00:00:00'), NOW()
FROM signalbit_erp.whs_bppb_det d
JOIN signalbit_erp.whs_bppb_h h ON h.no_bppb = d.no_bppb
WHERE h.tujuan = 'Production - Cutting' AND d.no_bppb NOT LIKE 'MT/%'
  AND h.tgl_bppb >= '2026-04-01' AND h.tgl_bppb < '2026-05-01'
  AND COALESCE(d.status, 'Y') <> 'N'
  AND NOT EXISTS (SELECT 1 FROM laravel_nds.penerimaan_cutting pc WHERE pc.whs_bppb_det_id = d.id)
ORDER BY d.id;

-- Mei 2026
INSERT INTO laravel_nds.penerimaan_cutting
    (tanggal_terima, whs_bppb_det_id, id_roll, qty_konv, unit_konv,
     created_by, created_by_username, created_at, updated_at)
SELECT DATE(h.tgl_bppb), d.id, d.id_roll,
    CASE WHEN UPPER(TRIM(d.satuan)) IN ('YRD', 'YARD') THEN ROUND(d.qty_out * 0.9144, 2) ELSE d.qty_out END,
    CASE WHEN UPPER(TRIM(d.satuan)) IN ('YRD', 'YARD') THEN 'METER' ELSE UPPER(TRIM(d.satuan)) END,
    NULL, 'backfill', CONCAT(DATE(h.tgl_bppb), ' 00:00:00'), NOW()
FROM signalbit_erp.whs_bppb_det d
JOIN signalbit_erp.whs_bppb_h h ON h.no_bppb = d.no_bppb
WHERE h.tujuan = 'Production - Cutting' AND d.no_bppb NOT LIKE 'MT/%'
  AND h.tgl_bppb >= '2026-05-01' AND h.tgl_bppb < '2026-06-01'
  AND COALESCE(d.status, 'Y') <> 'N'
  AND NOT EXISTS (SELECT 1 FROM laravel_nds.penerimaan_cutting pc WHERE pc.whs_bppb_det_id = d.id)
ORDER BY d.id;

-- Juni 2026
INSERT INTO laravel_nds.penerimaan_cutting
    (tanggal_terima, whs_bppb_det_id, id_roll, qty_konv, unit_konv,
     created_by, created_by_username, created_at, updated_at)
SELECT DATE(h.tgl_bppb), d.id, d.id_roll,
    CASE WHEN UPPER(TRIM(d.satuan)) IN ('YRD', 'YARD') THEN ROUND(d.qty_out * 0.9144, 2) ELSE d.qty_out END,
    CASE WHEN UPPER(TRIM(d.satuan)) IN ('YRD', 'YARD') THEN 'METER' ELSE UPPER(TRIM(d.satuan)) END,
    NULL, 'backfill', CONCAT(DATE(h.tgl_bppb), ' 00:00:00'), NOW()
FROM signalbit_erp.whs_bppb_det d
JOIN signalbit_erp.whs_bppb_h h ON h.no_bppb = d.no_bppb
WHERE h.tujuan = 'Production - Cutting' AND d.no_bppb NOT LIKE 'MT/%'
  AND h.tgl_bppb >= '2026-06-01' AND h.tgl_bppb < '2026-07-01'
  AND COALESCE(d.status, 'Y') <> 'N'
  AND NOT EXISTS (SELECT 1 FROM laravel_nds.penerimaan_cutting pc WHERE pc.whs_bppb_det_id = d.id)
ORDER BY d.id;

-- Juli 2026
INSERT INTO laravel_nds.penerimaan_cutting
    (tanggal_terima, whs_bppb_det_id, id_roll, qty_konv, unit_konv,
     created_by, created_by_username, created_at, updated_at)
SELECT DATE(h.tgl_bppb), d.id, d.id_roll,
    CASE WHEN UPPER(TRIM(d.satuan)) IN ('YRD', 'YARD') THEN ROUND(d.qty_out * 0.9144, 2) ELSE d.qty_out END,
    CASE WHEN UPPER(TRIM(d.satuan)) IN ('YRD', 'YARD') THEN 'METER' ELSE UPPER(TRIM(d.satuan)) END,
    NULL, 'backfill', CONCAT(DATE(h.tgl_bppb), ' 00:00:00'), NOW()
FROM signalbit_erp.whs_bppb_det d
JOIN signalbit_erp.whs_bppb_h h ON h.no_bppb = d.no_bppb
WHERE h.tujuan = 'Production - Cutting' AND d.no_bppb NOT LIKE 'MT/%'
  AND h.tgl_bppb >= '2026-07-01' AND h.tgl_bppb < '2026-08-01'
  AND COALESCE(d.status, 'Y') <> 'N'
  AND NOT EXISTS (SELECT 1 FROM laravel_nds.penerimaan_cutting pc WHERE pc.whs_bppb_det_id = d.id)
ORDER BY d.id;

-- Agustus 2026
INSERT INTO laravel_nds.penerimaan_cutting
    (tanggal_terima, whs_bppb_det_id, id_roll, qty_konv, unit_konv,
     created_by, created_by_username, created_at, updated_at)
SELECT DATE(h.tgl_bppb), d.id, d.id_roll,
    CASE WHEN UPPER(TRIM(d.satuan)) IN ('YRD', 'YARD') THEN ROUND(d.qty_out * 0.9144, 2) ELSE d.qty_out END,
    CASE WHEN UPPER(TRIM(d.satuan)) IN ('YRD', 'YARD') THEN 'METER' ELSE UPPER(TRIM(d.satuan)) END,
    NULL, 'backfill', CONCAT(DATE(h.tgl_bppb), ' 00:00:00'), NOW()
FROM signalbit_erp.whs_bppb_det d
JOIN signalbit_erp.whs_bppb_h h ON h.no_bppb = d.no_bppb
WHERE h.tujuan = 'Production - Cutting' AND d.no_bppb NOT LIKE 'MT/%'
  AND h.tgl_bppb >= '2026-08-01' AND h.tgl_bppb < '2026-09-01'
  AND COALESCE(d.status, 'Y') <> 'N'
  AND NOT EXISTS (SELECT 1 FROM laravel_nds.penerimaan_cutting pc WHERE pc.whs_bppb_det_id = d.id)
ORDER BY d.id;

-- September 2026
INSERT INTO laravel_nds.penerimaan_cutting
    (tanggal_terima, whs_bppb_det_id, id_roll, qty_konv, unit_konv,
     created_by, created_by_username, created_at, updated_at)
SELECT DATE(h.tgl_bppb), d.id, d.id_roll,
    CASE WHEN UPPER(TRIM(d.satuan)) IN ('YRD', 'YARD') THEN ROUND(d.qty_out * 0.9144, 2) ELSE d.qty_out END,
    CASE WHEN UPPER(TRIM(d.satuan)) IN ('YRD', 'YARD') THEN 'METER' ELSE UPPER(TRIM(d.satuan)) END,
    NULL, 'backfill', CONCAT(DATE(h.tgl_bppb), ' 00:00:00'), NOW()
FROM signalbit_erp.whs_bppb_det d
JOIN signalbit_erp.whs_bppb_h h ON h.no_bppb = d.no_bppb
WHERE h.tujuan = 'Production - Cutting' AND d.no_bppb NOT LIKE 'MT/%'
  AND h.tgl_bppb >= '2026-09-01' AND h.tgl_bppb < '2026-10-01'
  AND COALESCE(d.status, 'Y') <> 'N'
  AND NOT EXISTS (SELECT 1 FROM laravel_nds.penerimaan_cutting pc WHERE pc.whs_bppb_det_id = d.id)
ORDER BY d.id;


-- ---------------------------------------------------------------------
-- BAGIAN 2. Verifikasi
-- ---------------------------------------------------------------------
-- Hasil backfill per bulan (bandingkan dengan BAGIAN 0)
SELECT DATE_FORMAT(tanggal_terima, '%Y-%m') AS bulan, COUNT(*) AS baris, MIN(created_at), MAX(created_at)
FROM laravel_nds.penerimaan_cutting
WHERE created_by_username = 'backfill'
GROUP BY DATE_FORMAT(tanggal_terima, '%Y-%m')
ORDER BY bulan;

-- Sisa yang belum diterima di rentang ini (harusnya 0) -> jalankan ulang BAGIAN 0

-- Cek duplikat whs_bppb_det_id (harusnya kosong)
SELECT whs_bppb_det_id, COUNT(*) c
FROM laravel_nds.penerimaan_cutting
WHERE created_by_username = 'backfill'
GROUP BY whs_bppb_det_id
HAVING c > 1;


-- ---------------------------------------------------------------------
-- ROLLBACK (bila perlu)
-- ---------------------------------------------------------------------
-- DELETE FROM laravel_nds.penerimaan_cutting WHERE created_by_username = 'backfill';
-- (bila besar, hapus bertahap: tambahkan LIMIT 5000 dan ulangi sampai 0 rows affected)
