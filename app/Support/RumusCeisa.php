<?php

namespace App\Support;

// Rumus nilai barang CEISA (exim_barang, sheet barang) per jenis dokumen, dan sumber Supplier
// (exim_entitas, lihat supplier()). Dipakai bersama oleh Laporan Rekonsiliasi Ceisa & Report Ceisa
// Detail, layar maupun Excel, supaya keempatnya selalu sama.
//
//   Dokumen               Total                                  Total IDR
//   BC 2.3, 2.6.1, 2.6.2  cif                                    cif_rupiah
//   BC 2.5, 4.0, 4.1      harga_penyerahan                       harga_penyerahan
//   BC 2.7                cif (kosong -> harga_penyerahan)       cif_rupiah (kosong -> harga_penyerahan)
//   BC 3.0, 3.3           fob                                    fob x ndpbm
//   Qty = jumlah_satuan, Unit = kode_satuan, Rate = ndpbm, Price (Ceisa Detail) = Total / Qty.
//   BC 2.5/4.0/4.1 sengaja tidak memakai Price = cif / qty: cif di dokumen itu umumnya 0 (BC 4.0/4.1 selalu).
//
// Dokumen dengan tanggal daftar sebelum CUTOFF tetap memakai rumus lama (BC 2.7 tanpa cadangan
// harga_penyerahan, BC 3.0 = harga_satuan x jumlah_satuan), supaya angka periode sebelumnya tidak berubah.
//
// Sumber query wajib punya kolom fil_aju (kode dokumen angka: 23, 25, 27, 30, 33, 40, 41, 261, 262)
// dan tgl_rumus (lihat kolomTanggal()), di samping kolom-kolom exim_barang.
class RumusCeisa
{
    const CUTOFF = '2026-11-15';

    // Tambahan kolom untuk subquery "select eb.* ... from exim_barang eb": tanggal daftar dokumennya
    // (exim_header.tanggal_daftar, varchar yyyy-mm-dd).
    public static function kolomTanggal()
    {
        return "(select MAX(eh.tanggal_daftar) from exim_header eh where eh.nomor_aju = eb.nomor_aju) tgl_rumus";
    }

    // Total dalam mata uang dokumen.
    public static function total()
    {
        $baru = "tgl_rumus >= '".self::CUTOFF."'";

        return "CASE
                WHEN fil_aju IN (25, 40, 41) THEN harga_penyerahan
                WHEN fil_aju IN (23, 261, 262) THEN cif
                WHEN fil_aju IN (27) THEN IF(".$baru." AND IFNULL(cif, 0) = 0, harga_penyerahan, cif)
                WHEN fil_aju IN (30) THEN IF(".$baru.", fob, harga_satuan * jumlah_satuan)
                WHEN fil_aju IN (33) THEN fob
                ELSE '0'
                END";
    }

    // Total dalam rupiah.
    public static function totalIdr()
    {
        $baru = "tgl_rumus >= '".self::CUTOFF."'";

        return "CASE
                WHEN fil_aju IN (25, 40, 41) THEN harga_penyerahan
                WHEN fil_aju IN (23, 261, 262) THEN cif_rupiah
                WHEN fil_aju IN (27) THEN IF(".$baru." AND IFNULL(cif_rupiah, 0) = 0, harga_penyerahan, cif_rupiah)
                WHEN fil_aju IN (30) THEN IF(".$baru.", fob, harga_satuan * jumlah_satuan) * ndpbm
                WHEN fil_aju IN (33) THEN fob * ndpbm
                ELSE '0'
                END";
    }

    // Sumber kolom Supplier (nama_entitas) per dokumen CEISA, dari exim_entitas: tepat 1 baris per
    // nomor_aju. Dipakai sebagai tabel turunan: LEFT JOIN ".RumusCeisa::supplier()." b ON b.nomor_aju = ...
    //
    // Mulai CUTOFF cukup dilihat dari kode_entitas-nya (seri & jenis identitas tidak dipakai lagi):
    //   BC 2.3 = 5    BC 2.5, 2.6.1, 3.0, 3.3, 4.1 = 8    BC 2.6.2, 4.0 = 9
    //   BC 2.7 = 3 (IN) / 8 (OUT): dokumen BC 2.7 selalu memuat Nirwana di salah satu dari kedua kode
    //            itu, jadi yang diambil yang bukan Nirwana.
    //   BC 2.5 sengaja 8, bukan 7: kode 7 (pemilik barang) sering berisi Nirwana sendiri, sedangkan
    //   kode 8 sama dengan supplier BPPB-nya di Signalbit.
    //   BC 3.0 IN (kode 6) tidak dibedakan: di Signalbit BC 3.0 selalu keluar (BPPB).
    // Kalau kode yang sama muncul di lebih dari 1 baris (isinya nama yang sama), di-group per nomor_aju.
    // Sebelum CUTOFF tetap aturan lama (prioritas seri / entitas / identitas), supaya laporan periode
    // sebelumnya tidak berubah.
    public static function supplier()
    {
        $sebelum = "IFNULL(b.tanggal_daftar, '') < '".self::CUTOFF."'";

        return "(SELECT *
FROM
(
".str_replace('{SEBELUM_CUTOFF}', $sebelum, self::SUPPLIER_LAMA)."

    UNION

    /* Mulai CUTOFF: kode entitas saja */
    SELECT
        b.nomor_aju,
        MIN(a.nomor_identitas) nomor_identitas,
        MIN(a.nama_entitas) nama_entitas,
        MIN(a.alamat_entitas) alamat_entitas
    FROM exim_entitas a
    INNER JOIN exim_header b
        ON b.nomor_aju = a.nomor_aju
    WHERE b.tanggal_daftar >= '".self::CUTOFF."'
    AND (
        ((b.kode_dokumen+0) = 23 AND a.kode_entitas = 5)
        OR ((b.kode_dokumen+0) IN (25, 261, 30, 33, 41) AND a.kode_entitas = 8)
        OR ((b.kode_dokumen+0) IN (262, 40) AND a.kode_entitas = 9)
        OR ((b.kode_dokumen+0) = 27 AND a.kode_entitas IN (3, 8) AND a.nama_entitas NOT LIKE '%NIRWANA ALABARE%')
    )
    GROUP BY b.nomor_aju

) a)";
    }

    // Aturan supplier lama (dokumen sebelum CUTOFF), apa adanya. {SEBELUM_CUTOFF} diisi supplier().
    const SUPPLIER_LAMA = <<<'SQL'
   SELECT
    b.nomor_aju,
    a.nomor_identitas,
    a.nama_entitas,
    a.alamat_entitas
FROM exim_entitas a
INNER JOIN exim_header b
    ON b.nomor_aju = a.nomor_aju
WHERE
(
    (
        (b.kode_dokumen+0) = 27
        AND
        (
            -- Prioritas 1 : 3,3,6
            (
                a.seri = '3'
                AND a.kode_entitas = '3'
                AND a.kode_jenis_identitas = '6'
            )

            OR

            -- Tambahan : 3,8,6
            (
                a.seri = '3'
                AND a.kode_entitas = '8'
                AND a.kode_jenis_identitas = '6'
                AND NOT EXISTS
                (
                    SELECT 1
                    FROM exim_entitas x
                    WHERE x.nomor_aju = a.nomor_aju
                      AND x.seri = '3'
                      AND x.kode_entitas = '3'
                      AND x.kode_jenis_identitas = '6'
                      AND x.nama_entitas NOT LIKE '%NIRWANA ALABARE%'
                )
            )

            OR

            -- Berikutnya : 8,8,6
            (
                a.seri = '8'
                AND a.kode_entitas = '8'
                AND a.kode_jenis_identitas = '6'
                AND NOT EXISTS
                (
                    SELECT 1
                    FROM exim_entitas x
                    WHERE x.nomor_aju = a.nomor_aju
                      AND x.seri = '3'
                      AND x.kode_entitas IN ('3','8')
                      AND x.kode_jenis_identitas = '6'
                      AND x.nama_entitas NOT LIKE '%NIRWANA ALABARE%'
                )
            )

            OR

            -- Terakhir : 1,3,6
            (
                a.seri = '1'
                AND a.kode_entitas = '3'
                AND a.kode_jenis_identitas = '6'
                AND NOT EXISTS
                (
                    SELECT 1
                    FROM exim_entitas x
                    WHERE x.nomor_aju = a.nomor_aju
                      AND ((x.seri = '3' AND x.kode_entitas IN ('3','8')) OR (x.seri = '8' AND x.kode_entitas = '8'))
                      AND x.kode_jenis_identitas = '6'
                      AND x.nama_entitas NOT LIKE '%NIRWANA ALABARE%'
                )
            )
        )
    )

    OR

    (
        (b.kode_dokumen+0) IN (25,41,261)
        AND a.seri='8'
        AND a.kode_entitas='8'
        AND a.kode_jenis_identitas='6'
    )
)
-- Supplier Ceisa tidak pernah Nirwana sendiri (semua variasi tulisan)
AND a.nama_entitas NOT LIKE '%NIRWANA ALABARE%'
AND {SEBELUM_CUTOFF}
GROUP BY b.nomor_aju

    UNION

    SELECT
        b.nomor_aju,
        a.nomor_identitas,
        a.nama_entitas,
        a.alamat_entitas
    FROM exim_entitas a
    INNER JOIN exim_header b
        ON b.nomor_aju = a.nomor_aju
    WHERE
    (
        (
            (b.kode_dokumen+0) IN (40,262)
            AND
            (
                (
                    a.seri='3'
                    AND a.kode_entitas='9'
                    AND a.kode_jenis_identitas='6'
                )
                OR
                (
                    a.seri='9'
                    AND a.kode_entitas='9'
                    AND a.kode_jenis_identitas='6'
                    AND NOT EXISTS
                    (
                        SELECT 1
                        FROM exim_entitas x
                        WHERE x.nomor_aju = a.nomor_aju
                          AND x.seri='3'
                          AND x.kode_entitas='9'
                          AND x.kode_jenis_identitas='6'
                          AND x.nama_entitas NOT LIKE '%NIRWANA ALABARE%'
                    )
                )
            )
        )

    )
    AND a.nama_entitas NOT LIKE '%NIRWANA ALABARE%'
    AND {SEBELUM_CUTOFF}
    GROUP BY b.nomor_aju

    UNION

    SELECT
        b.nomor_aju,
        a.nomor_identitas,
        a.nama_entitas,
        a.alamat_entitas
    FROM exim_entitas a
    INNER JOIN exim_header b
        ON b.nomor_aju = a.nomor_aju
    WHERE
        a.seri <> '4'
        AND a.kode_entitas <> '4'
        AND a.kode_jenis_identitas <> '6'
        AND (b.kode_dokumen+0)=23
        AND a.nama_entitas NOT LIKE '%NIRWANA ALABARE%'
    AND {SEBELUM_CUTOFF}
    GROUP BY b.nomor_aju

    UNION

    /* BC 30,33 */
    SELECT
    b.nomor_aju,
    a.nomor_identitas,
    a.nama_entitas,
    a.alamat_entitas
FROM exim_entitas a
INNER JOIN exim_header b
    ON b.nomor_aju = a.nomor_aju
WHERE
(
    (
        a.seri='8'
        AND a.kode_entitas='8'
        AND a.kode_jenis_identitas=''
    )
    OR
    (
        a.seri='2'
        AND a.kode_entitas='7'
        AND a.kode_jenis_identitas='4'
        AND NOT EXISTS (
            SELECT 1
            FROM exim_entitas x
            WHERE x.nomor_aju = a.nomor_aju
              AND x.seri='8'
              AND x.kode_entitas='8'
              AND x.kode_jenis_identitas=''
              AND x.nama_entitas NOT LIKE '%NIRWANA ALABARE%'
        )
    )
    OR
    (
        a.seri='3'
        AND a.kode_entitas='8'
        AND a.kode_jenis_identitas='0'
        AND NOT EXISTS (
            SELECT 1
            FROM exim_entitas x
            WHERE x.nomor_aju = a.nomor_aju
              AND x.seri='8'
              AND x.kode_entitas='8'
              AND x.kode_jenis_identitas=''
              AND x.nama_entitas NOT LIKE '%NIRWANA ALABARE%'
        )
    )
)
AND (b.kode_dokumen+0) IN (30,33)
AND a.nama_entitas NOT LIKE '%NIRWANA ALABARE%'
AND {SEBELUM_CUTOFF}
GROUP BY b.nomor_aju
SQL;
}
