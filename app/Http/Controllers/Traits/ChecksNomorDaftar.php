<?php

namespace App\Http\Controllers\Traits;

use DB;

/**
 * Konfirmasi pemasukan / pengeluaran: selain INHOUSE, dokumen wajib sudah terisi nomor daftar (bcno)
 * lewat update dokumen pabean di signalbit (aturan sama dengan menu konfirmasi di signalbit_erp).
 * Acuannya tabel bpb / bppb yang diupdate exim, dicari lewat nomor internal (bpbno_int / bppbno_int).
 */
trait ChecksNomorDaftar
{
    // 1 kalau masih ada baris non-INHOUSE yang nomor daftarnya kosong (dipakai dengan group by nomor dokumen)
    private $sqlBelumDaftar = "sum(ifnull(jenis_dok,'') != 'INHOUSE' and trim(ifnull(bcno,'')) = '') > 0";

    private function fieldNoInt(string $tbl): string
    {
        return $tbl == 'bppb' ? 'bppbno_int' : 'bpbno_int';
    }

    /**
     * Subquery daftar nomor internal yang belum boleh dikonfirmasi karena nomor daftarnya masih kosong.
     * $listNoInt = subquery nomor dokumen yang dicek (supaya tidak menghitung seluruh tabel bpb / bppb).
     */
    private function sqlNoIntBelumDaftar(string $tbl, string $listNoInt): string
    {
        $fld = $this->fieldNoInt($tbl);
        return "select $fld no_int from $tbl where $fld in ($listNoInt) group by $fld having " . $this->sqlBelumDaftar;
    }

    // true kalau dokumen belum boleh dikonfirmasi karena nomor daftarnya masih kosong
    private function belumNomorDaftar(string $tbl, string $noInt): bool
    {
        $fld = $this->fieldNoInt($tbl);
        $row = DB::connection('mysql_sb')->select("select " . $this->sqlBelumDaftar . " belum from $tbl where $fld = ?", [$noInt]);
        return ($row[0]->belum ?? 0) == 1;
    }
}
