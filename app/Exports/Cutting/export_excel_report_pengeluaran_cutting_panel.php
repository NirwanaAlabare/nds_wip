<?php

namespace App\Exports\Cutting;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;


class export_excel_report_pengeluaran_cutting_panel implements FromView, ShouldAutoSize, WithEvents
{
    use Exportable;
    protected $start_date, $end_date, $rowCount;

    public function __construct($start_date, $end_date)
    {
        $this->start_date = $start_date;
        $this->end_date = $end_date;
    }

    public function view(): View
    {

        $start_date = $this->start_date;
        $end_date = $this->end_date;
        $tgl_saldo = '2026-03-01';

        $rawData = DB::select("
            WITH stocker as (
                select
                        id_so_det,
                        no_form,
                        no_cut,
                        created_at,
                        buyer,
                        ws,
                        styleno,
                        color,
                        size,
                        dest,
                        panel,
                        panel_status,
                        part_detail_id,
                        nama_part,
                        part_status,
                        SUM(qty_out) qty_dc,
                        cancel,
                        cancel_h,
                        status,
                        part_id
                from (
                    select
                        msb.id_so_det,
                        COALESCE(f.no_form, fr.no_form, fp.no_form) no_form,
                        COALESCE(f.no_cut, fp.no_cut) no_cut,
                        DATE_FORMAT(s.created_at, '%d-%m-%Y') AS created_at,
                        msb.buyer,
                        msb.ws,
                        msb.styleno,
                        msb.color,
                        s.so_det_id,
                        k.size,
                        msb.dest,
                        (CASE WHEN pd.part_status = 'complement' THEN p_com.panel ELSE p.panel END) panel,
                        (CASE WHEN pd.part_status = 'complement' THEN p_com.panel_status ELSE p.panel_status END) panel_status,
                        pd.id part_detail_id,
                        mp.nama_part,
                        pd.part_status,
                        (CASE WHEN s.qty_ply_mod > 0 THEN s.qty_ply_mod ELSE s.qty_ply END) qty_out,
                        k.cancel,
                        k.cancel_h,
                        k.status,
                        (CASE WHEN pd.part_status = 'complement' THEN p_com.id ELSE p.id END) part_id
                FROM
                        stocker_input s
                        left join master_sb_ws msb on msb.id_so_det = s.so_det_id
                        left join form_cut_input f on f.id = s.form_cut_id
                        left join form_cut_reject fr on fr.id = s.form_reject_id
                        left join form_cut_piece fp on fp.id = s.form_piece_id
                        left join part_detail pd on s.part_detail_id = pd.id
                        left join part_detail pd_com on pd_com.id = pd.from_part_detail and pd.part_status = 'complement'
                        left join part p on p.id = pd.part_id
                        left join part p_com on p_com.id = pd_com.part_id
                        left join master_part mp on mp.id = pd.master_part_id
                        LEFT JOIN (
                                SELECT sd.id as id_so_det, ac.kpno ws, ac.styleno, sd.color, sd.size, sd.dest, ms.supplier as buyer, sd.cancel, so.cancel_h, ac.status FROM signalbit_erp.so_det sd
                                INNER JOIN signalbit_erp.so ON sd.id_so = so.id
                                INNER JOIN signalbit_erp.act_costing ac ON so.id_cost = ac.id
                                INNER JOIN signalbit_erp.mastersupplier ms ON ac.id_buyer = ms.id_supplier
                        ) k on msb.id_so_det = k.id_so_det
                        where
                        (s.cancel IS NULL OR s.cancel != 'Y') and
                        (s.notes IS NULL OR s.notes NOT LIKE '%STOCKER MANUAL%') and
                        s.created_at between '$start_date 00:00:00' and '$end_date 23:59:59'
                ) cutting
                group by
                        no_form,
                        id_so_det,
                        part_id,
                        part_detail_id
        ),

        form_list as (
            select
                id_so_det,
                no_form,
                no_cut,
                stocker.created_at,
                stocker.buyer,
                ws,
                styleno,
                stocker.color,
                size,
                dest,
                part.panel,
                part.panel_status,
                part_detail.id part_detail_id,
                mp.nama_part,
                part_detail.part_status,
                0 qty_dc,
                '-' cancel,
                '-' cancel_h,
                '-' status,
                part.id part_id
            from
                stocker
                left join part on part.act_costing_ws = stocker.ws and part.id = stocker.part_id
                left join part_detail on part_detail.part_id = part.id
                left join master_part mp on mp.id = part_detail.master_part_id
            where
                part.panel_status != 'COMPLEMENT' and part_detail.part_status != 'COMPLEMENT'
            group by
                no_form,
                id_so_det,
                part.id,
                part_detail.id
        )

        SELECT
            *, MIN(qty) qty_dc
        FROM (
            select
                MAX(id_so_det) id_so_det ,
                MAX(no_form) no_form ,
                MAX(no_cut) no_cut ,
                MAX(created_at) created_at ,
                MAX(buyer) buyer ,
                MAX(ws) ws ,
                MAX(styleno) styleno ,
                MAX(color) color ,
                MAX(size) size ,
                MAX(dest) dest ,
                MAX(panel) panel ,
                MAX(panel_status) panel_status ,
                MAX(part_detail_id ) part_detail_id,
                MAX(nama_part) nama_part ,
                MAX(part_status) part_status ,
                SUM(qty_dc) qty,
                '-' cancel,
                '-' cancel_h,
                '-' status,
                MAX(part_id) part_id
            from (
                select * from stocker
                union all
                select * from form_list
            ) stocker
            group by
                no_form,
                size,
                part_id,
                part_detail_id
            order by
                no_form,
                size,
                part_id,
                part_detail_id
        ) dc
        group by
            no_form, id_so_det, part_id
        ");

        $this->rowCount = count($rawData) + 3; // 1 for header

        return view('cutting.report.export.export_excel_report_pengeluaran_cutting_panel', [
            'rawData' => $rawData,
			'startDate' => $start_date,
            'endDate' => $end_date
        ]);
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                $highestRow = $sheet->getHighestRow();
                $highestColumn = $sheet->getHighestColumn(); // e.g. 'Z'
                $columnIndex = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($highestColumn);

                // ===== 1. Format header rows (row 2 and 3) =====
                for ($i = 1; $i <= $columnIndex; $i++) {
                    $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($i);

                    foreach ([4] as $row) {
                        $cell = $colLetter . $row;

                        $sheet->getStyle($cell)->applyFromArray([
                            'alignment' => [
                                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                            ],
                            'fill' => [
                                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                                'startColor' => ['argb' => 'FFD9EDF7'], // Light blue
                            ],
                            'font' => [
                                'bold' => true,
                                'color' => ['argb' => 'FF000000'], // Black text
                            ],
                        ]);
                    }
                }
                // ===== 3. Apply border to whole table =====
                $range = 'A4:' . $highestColumn . $highestRow;
                $sheet->getStyle($range)->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                            'color' => ['argb' => 'FF000000'],
                        ],
                    ],
                ]);
            }
        ];
    }
}
