-- Roll retur (dokumen /RI/) yang tersimpan dengan no_lot kosong: isi no_lot / no_roll / no_roll_buyer
-- dari riwayat barcode yang sama di whs_lokasi_inmaterial. no_lot yang tidak ditemukan di riwayat diisi '-'.
-- Jalankan di database signalbit_erp. Perkiraan per 22 Okt 2026: 29 roll di 8 dokumen.

-- 1. Cek dulu (tidak mengubah data)
SELECT b.id, b.no_dok, b.no_barcode, b.no_roll, b.no_lot,
       h.roll AS no_roll_baru, COALESCE(h.lot, '-') AS no_lot_baru
FROM whs_lokasi_inmaterial b
JOIN (
    SELECT no_barcode,
           MAX(NULLIF(NULLIF(no_roll, ''), '-'))       AS roll,
           MAX(NULLIF(NULLIF(no_roll_buyer, ''), '-')) AS roll_buyer,
           MAX(NULLIF(NULLIF(no_lot, ''), '-'))        AS lot
    FROM whs_lokasi_inmaterial
    WHERE no_barcode IN (SELECT no_barcode FROM whs_lokasi_inmaterial WHERE no_dok LIKE '%/RI/%' AND (no_lot IS NULL OR TRIM(no_lot) = ''))
    GROUP BY no_barcode
) h ON h.no_barcode = b.no_barcode
WHERE b.no_dok LIKE '%/RI/%' AND (b.no_lot IS NULL OR TRIM(b.no_lot) = '');

-- 2. Perbaiki
UPDATE whs_lokasi_inmaterial b
JOIN (
    SELECT * FROM (
        SELECT no_barcode,
               MAX(NULLIF(NULLIF(no_roll, ''), '-'))       AS roll,
               MAX(NULLIF(NULLIF(no_roll_buyer, ''), '-')) AS roll_buyer,
               MAX(NULLIF(NULLIF(no_lot, ''), '-'))        AS lot
        FROM whs_lokasi_inmaterial
        WHERE no_barcode IN (SELECT no_barcode FROM whs_lokasi_inmaterial WHERE no_dok LIKE '%/RI/%' AND (no_lot IS NULL OR TRIM(no_lot) = ''))
        GROUP BY no_barcode
    ) z
) h ON h.no_barcode = b.no_barcode
SET b.no_lot        = COALESCE(h.lot, '-'),
    b.no_roll       = COALESCE(NULLIF(b.no_roll, ''), h.roll),
    b.no_roll_buyer = COALESCE(NULLIF(b.no_roll_buyer, ''), h.roll_buyer)
WHERE b.no_dok LIKE '%/RI/%' AND (b.no_lot IS NULL OR TRIM(b.no_lot) = '');
