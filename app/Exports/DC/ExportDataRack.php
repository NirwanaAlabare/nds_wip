<?php

namespace App\Exports\DC;

use DB;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class ExportDataRack implements FromView, ShouldAutoSize, WithColumnFormatting
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
        $data = DB::table(DB::raw("
            (
                SELECT
                    rack_detail_stocker.id AS id,
                    rack_detail.nama_detail_rak AS no_rak,
                    stocker_input.id_qr_stocker AS no_stocker,
                    marker_input.act_costing_ws AS no_ws,
                    form_cut_input.no_cut,
                    marker_input.style,
                    marker_input.color,
                    master_part.nama_part AS part,
                    COALESCE(master_sb_ws.size, stocker_input.size) AS size,
                    rack_detail_stocker.qty_in,
                    rack_detail_stocker.updated_at
                FROM rack_detail
                LEFT JOIN rack_detail_stocker
                    ON rack_detail_stocker.detail_rack_id = rack_detail.id
                LEFT JOIN stocker_input
                    ON stocker_input.id_qr_stocker = rack_detail_stocker.stocker_id
                LEFT JOIN form_cut_input
                    ON form_cut_input.id = stocker_input.form_cut_id
                LEFT JOIN marker_input
                    ON marker_input.kode = form_cut_input.id_marker
                LEFT JOIN part_detail
                    ON part_detail.id = stocker_input.part_detail_id
                LEFT JOIN master_part
                    ON master_part.id = part_detail.master_part_id
                LEFT JOIN master_sb_ws
                    ON master_sb_ws.id_so_det = stocker_input.so_det_id
                WHERE
                    rack_detail_stocker.status = 'active'
                    AND DATE(rack_detail_stocker.updated_at) BETWEEN '{$this->from}' AND '{$this->to}'
                GROUP BY
                    rack_detail_stocker.id,
                    rack_detail.nama_detail_rak,
                    stocker_input.id_qr_stocker,
                    marker_input.act_costing_ws,
                    form_cut_input.no_cut,
                    marker_input.style,
                    marker_input.color,
                    master_part.nama_part,
                    COALESCE(master_sb_ws.size, stocker_input.size),
                    rack_detail_stocker.updated_at
                ORDER BY
                    rack_detail.nama_detail_rak ASC
            ) as results
        "))->get();

        return view("dc.rack.export-rack", [
            "from" => $this->from,
            "to" => $this->to,
            "data" => $data,
        ]);
    }

    public function columnFormats(): array
    {
        return [
            'I' => NumberFormat::FORMAT_NUMBER_00,
        ];
    }
}
