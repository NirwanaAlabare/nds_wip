-- =====================================================================
-- Nomor invoice di SJ knitting GG/OUT (keluaran greige).
--
-- Jalankan di DATABASE KNITTING (PostgreSQL, koneksi pgsql_nak di NDS),
-- BUKAN di signalbit_erp.
--
-- Invoice EXIM (InvoiceEximController::tandaiInvnoNak) sudah menulis nomor
-- invoice ke official_out_h.no_invoice (OFC/OUT) dan gp_out_greige_h.no_invoice
-- (GG/OUT). Kolom yang kedua belum ada, jadi untuk GG/OUT penandaannya selama
-- ini dilewati (tercatat di laravel.log: "kolom gp_out_greige_h.no_invoice
-- tidak ada"). Begitu kolomnya ada, invoice yang dibuat / di-edit / dibatalkan
-- sesudahnya langsung mengisi / mengosongkannya sendiri.
--
-- Layar Delivery di aplikasi knitting (DeliveryDataTable) membaca kolom ini
-- untuk baris Greige.
-- =====================================================================

-- 1. Kolom nomor invoice (sama dengan official_out_h.no_invoice)
ALTER TABLE public.gp_out_greige_h ADD COLUMN IF NOT EXISTS no_invoice character varying;

-- 2. SJ GG/OUT yang SUDAH masuk invoice EXIM aktif sebelum kolomnya ada.
--    Diambil dari tbl_book_invoice_exim_det (asal NAK, invoice bukan CANCEL)
--    per 27 Nov 2026. id = gp_out_greige_h.id; no_bppb ikut dicocokkan supaya
--    tidak mengenai baris lain, dan baris yang sudah terisi tidak ditimpa.
BEGIN;

UPDATE public.gp_out_greige_h SET no_invoice = '0175/L/NAK/1026' WHERE id = 1266 AND no_bppb = 'GG/OUT/1026/00017' AND no_invoice IS NULL;
UPDATE public.gp_out_greige_h SET no_invoice = '0176/L/NAK/1026' WHERE id = 1267 AND no_bppb = 'GG/OUT/1026/00018' AND no_invoice IS NULL;
UPDATE public.gp_out_greige_h SET no_invoice = '0177/L/NAK/1026' WHERE id = 1268 AND no_bppb = 'GG/OUT/1026/00019' AND no_invoice IS NULL;
UPDATE public.gp_out_greige_h SET no_invoice = '0350/L/NAK/1026' WHERE id = 1291 AND no_bppb = 'GG/OUT/1026/00040' AND no_invoice IS NULL;
UPDATE public.gp_out_greige_h SET no_invoice = '0344/L/NAK/1026' WHERE id = 1292 AND no_bppb = 'GG/OUT/1026/00041' AND no_invoice IS NULL;
UPDATE public.gp_out_greige_h SET no_invoice = '0349/L/NAK/1026' WHERE id = 1293 AND no_bppb = 'GG/OUT/1026/00042' AND no_invoice IS NULL;
UPDATE public.gp_out_greige_h SET no_invoice = '0348/L/NAK/1026' WHERE id = 1294 AND no_bppb = 'GG/OUT/1026/00043' AND no_invoice IS NULL;
UPDATE public.gp_out_greige_h SET no_invoice = '0084/L/NAK/1126' WHERE id = 1346 AND no_bppb = 'GG/OUT/1126/00020' AND no_invoice IS NULL;
UPDATE public.gp_out_greige_h SET no_invoice = '0083/L/NAK/1126' WHERE id = 1347 AND no_bppb = 'GG/OUT/1126/00021' AND no_invoice IS NULL;
UPDATE public.gp_out_greige_h SET no_invoice = '0082/L/NAK/1126' WHERE id = 1348 AND no_bppb = 'GG/OUT/1126/00022' AND no_invoice IS NULL;
UPDATE public.gp_out_greige_h SET no_invoice = '0081/L/NAK/1126' WHERE id = 1349 AND no_bppb = 'GG/OUT/1126/00023' AND no_invoice IS NULL;
UPDATE public.gp_out_greige_h SET no_invoice = '0447/L/NAK/1126' WHERE id = 1380 AND no_bppb = 'GG/OUT/1126/00054' AND no_invoice IS NULL;
UPDATE public.gp_out_greige_h SET no_invoice = '0448/L/NAK/1126' WHERE id = 1381 AND no_bppb = 'GG/OUT/1126/00055' AND no_invoice IS NULL;

COMMIT;

-- 3. Cek: harus 13 baris
-- SELECT id, no_bppb, tgl_bppb, no_invoice FROM public.gp_out_greige_h WHERE no_invoice IS NOT NULL ORDER BY no_bppb;
