<?php

namespace App\Exports;

use App\Models\Marker;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use DB;

// class ExportLaporanPemakaian implements FromCollection
// {
//     /**
//      * @return \Illuminate\Support\Collection
//      */
//     public function collection()
//     {
//         return Marker::all();
//     }
// }

class ExportLaporanPemakaian implements FromView, ShouldAutoSize
{
    use Exportable;


    protected $from, $to;

    public function __construct($from, $to)
    {

        $this->from = $from;
        $this->to = $to;
    }


    public function view(): View

    {
        $data = DB::select("select a.tgl_form_cut,
        act_costing_ws,
        mrk.color,
        month(a.tgl_form_cut),
        b.roll,
        b.lot,
        mrk.cons_marker,
        a.cons_pipping,
        a.cons_ampar,
        a.cons_act,
        mrk.panel,
        b.unit,
        b.lembar_gelaran,
        mr.tot_ratio,
        concat (mrk.panjang_marker,'.',mrk.comma_marker) panjang_marker,
        mrk.unit_panjang_marker,
        concat (a.p_act,'.',a.comma_p_act) panjang_act,
        a.unit_p_act,
        mrk.lebar_marker,
        mrk.unit_lebar_marker,
        a.l_act,
        a.unit_l_act,
        b.lembar_gelaran * mr.tot_ratio qty_potong,
        b.lembar_gelaran * concat (a.p_act,'.',a.comma_p_act) actual_gelar_kain,
        b.lembar_gelaran * concat (a.p_act,'.',a.comma_p_act) + b.sambungan + b.piping + b.sisa_tidak_bisa kain_terpakai,
        b.sisa_kain,
        b.sisa_tidak_bisa,
        b.sambungan,
        b.piping,
        b.kepala_kain,
        b.reject,
        b.lembar_gelaran * concat (a.p_act,'.',a.comma_p_act) + b.sambungan + b.piping + b.kepala_kain + b.sisa_kain + b.reject + b.sisa_tidak_bisa total_aktual_kain,
        round((b.lembar_gelaran * concat (a.p_act,'.',a.comma_p_act) / b.lembar_gelaran * mr.tot_ratio /100),2) cons,
        b.created_at
        from form_cut_input a
        inner join form_cut_input_detail b on a.no_form = b.no_form_cut_input
        inner join marker_input mrk on a.id_marker = mrk.kode
        inner join (select marker_id, sum(ratio) tot_ratio from marker_input_detail group by marker_id) mr on mrk.id = mr.marker_id
        where b.created_at >='$this->from'
        and b.created_at <= '$this->to'");

        // $data = Marker::orderBy('tgl_cutting', 'asc')->get();
        return view('lap_pemakaian.export', [
            'data' => $data,
            'from' => $this->from,
            'to' => $this->to
        ]);
    }
}
