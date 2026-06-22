<?php

namespace App\Http\Controllers\Traits;

use DB;

trait ChecksClosingPeriode
{
    /**
     * Tanggal mulai periode paling awal yang statusnya masih 'Open' di
     * tbl_closing_periode_fabric_wh. Dipakai sebagai batas minimal tanggal transaksi.
     */
    private function getMinTglRo()
    {
        $row = DB::connection('mysql_sb')->select("SELECT MIN(tgl_awal) min_tgl FROM tbl_closing_periode_fabric_wh WHERE status_closing = 'Open'");
        return $row[0]->min_tgl ?? null;
    }

    /**
     * Daftar periode (tgl_awal - tgl_akhir) yang statusnya 'Closed' di
     * tbl_closing_periode_fabric_wh. Dipakai untuk menolak tanggal yang jatuh
     * di tengah periode closed meskipun masih >= min_tgl_ro.
     */
    private function getClosedPeriods()
    {
        return DB::connection('mysql_sb')->select("SELECT tgl_awal, tgl_akhir FROM tbl_closing_periode_fabric_wh WHERE status_closing = 'Closed'");
    }

    private function isTglRoClosed($tgl)
    {
        foreach ($this->getClosedPeriods() as $p) {
            if ($tgl >= $p->tgl_awal && $tgl <= $p->tgl_akhir) {
                return true;
            }
        }
        return false;
    }
}
