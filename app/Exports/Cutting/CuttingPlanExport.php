<?php

namespace App\Exports\Cutting;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use App\Models\Cutting\CutPlan;
use DB;

class CuttingPlanExport implements FromView, ShouldAutoSize
{
    use Exportable;

    protected $from;
    protected $to;

    public function __construct($from, $to)
    {
        $this->from = $from ? $from : date('Y-m-d');
        $this->to = $to ? $to : date('Y-m-d');
    }

    public function view(): View
    {
        $additionalQuery = "";

        $thisStoredCutPlan = CutPlan::select("form_cut_id")->whereBetween("tgl_plan", [$this->from, $this->to])->get();

        if ($thisStoredCutPlan->count() > 0) {
            $additionalQuery .= " and (";

            $i = 0;
            $length = $thisStoredCutPlan->count();
            foreach ($thisStoredCutPlan as $cutPlan) {
                if ($i == 0) {
                    $additionalQuery .= " a.id = '" . $cutPlan->form_cut_id . "' ";
                } else {
                    $additionalQuery .= " or a.id = '" . $cutPlan->form_cut_id . "' ";
                }

                $i++;
            }

            $additionalQuery .= " ) ";
        } else {
            $additionalQuery .= " and a.no_form = '0' ";
        }

        $data = DB::select("
                SELECT
                    a.id,
                    a.no_meja,
                    a.id_marker,
                    a.no_form,
                    a.tgl_form_cut,
                    b.id marker_id,
                    b.act_costing_ws ws,
                    b.style,
                    panel,
                    b.color,
                    (CASE WHEN a.status = 'SPREADING' THEN 'ANTRIAN SPREADING' ELSE a.status END) as status,
                    UPPER(users.name) nama_meja,
                    b.panjang_marker,
                    UPPER(b.unit_panjang_marker) unit_panjang_marker,
                    b.comma_marker,
                    UPPER(b.unit_comma_marker) unit_comma_marker,
                    b.lebar_marker,
                    UPPER(b.unit_lebar_marker) unit_lebar_marker,
                    a.qty_ply,
                    b.gelar_qty,
                    b.po_marker,
                    b.urutan_marker,
                    b.cons_marker,
                    a.tipe_form_cut,
                    CONCAT(b.panel, ' - ', b.urutan_marker) panel,
                    GROUP_CONCAT(DISTINCT CONCAT(marker_input_detail.size, '(', marker_input_detail.ratio, ')') ORDER BY master_size_new.urutan ASC SEPARATOR ' / ') marker_details,
                    sum(marker_input_detail.ratio) * a.qty_ply	qty_output,
                    coalesce(sum(marker_input_detail.ratio) * c.tot_lembar_akt,0) qty_act,
                    COALESCE(a2.total_lembar, a.total_lembar, '0') total_lembar
                FROM `form_cut_input` a
                left join (select form_cut_input_detail.form_cut_id, SUM(form_cut_input_detail.lembar_gelaran) total_lembar from form_cut_input_detail group by form_cut_input_detail.form_cut_id) a2 on a2.form_cut_id = a.id
                left join marker_input b on a.id_marker = b.kode
                left join marker_input_detail on b.id = marker_input_detail.marker_id
                left join master_size_new on marker_input_detail.size = master_size_new.size
                left join users on users.id = a.no_meja
                left join (select form_cut_id,sum(lembar_gelaran) tot_lembar_akt from form_cut_input_detail group by form_cut_id) c on a.id = c.form_cut_id
                where
                    a.id is not null and
                    marker_input_detail.ratio > 0 and
                    a.tgl_form_cut >= DATE(NOW()-INTERVAL 6 MONTH)
                    " . $additionalQuery . "
                GROUP BY a.id
                ORDER BY b.cancel desc, FIELD(a.status, 'PENGERJAAN FORM CUTTING', 'PENGERJAAN MARKER', 'PENGERJAAN FORM CUTTING DETAIL', 'PENGERJAAN FORM CUTTING SPREAD', 'SPREADING', 'SELESAI PENGERJAAN'), a.tgl_form_cut desc, panel asc
            ");

        return view("cutting.cutting-plan.export.cutting-plan-export", [
            "from" => $this->from,
            "to" => $this->to,
            "data" => $data,
        ]);
    }
}
