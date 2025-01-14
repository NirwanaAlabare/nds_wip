<?php

namespace App\Exports\Stocker;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Sheet;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithEvents;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use App\Models\YearSequence;
use DB;

Sheet::macro('styleCells', function (Sheet $sheet, string $cellRange, array $style) {
    $sheet->getDelegate()->getStyle($cellRange)->applyFromArray($style);
});

class StockerListDetailExport implements FromView, WithEvents, ShouldAutoSize
{
    use Exportable;

    protected $form_cut_id;
    protected $group_stocker;
    protected $ratio;
    protected $so_det_id;

    public function __construct($form_cut_id, $group_stocker, $ratio, $so_det_id)
    {
        $this->form_cut_id = $form_cut_id;
        $this->group_stocker = $group_stocker;
        $this->ratio = $ratio;
        $this->so_det_id = $so_det_id;
    }

    public function view(): View
    {
        $stockerList = DB::select("
            SELECT
                GROUP_CONCAT(DISTINCT stocker_input.id_qr_stocker) id_qr_stocker,
                GROUP_CONCAT(DISTINCT master_part.nama_part) part,
                stocker_input.form_cut_id,
                stocker_input.act_costing_ws,
                stocker_input.so_det_id,
                master_sb_ws.buyer buyer,
                master_sb_ws.styleno style,
                master_sb_ws.color,
                master_sb_ws.size,
                master_sb_ws.dest,
                form_cut_input.no_form,
                form_cut_input.no_cut,
                stocker_input.group_stocker,
                stocker_input.shade,
                stocker_input.ratio,
                MIN(stocker_input.range_awal) range_awal,
                MAX(stocker_input.range_akhir) range_akhir,
                CONCAT(MIN(stocker_input.range_awal), '-', MAX(stocker_input.range_akhir)) stocker_range
            FROM
                stocker_input
            LEFT JOIN
                part_detail on part_detail.id = stocker_input.part_detail_id
            LEFT JOIN
                master_part on master_part.id = part_detail.master_part_id
            LEFT JOIN
                master_sb_ws on master_sb_ws.id_so_det = stocker_input.so_det_id
            LEFT JOIN
                form_cut_input on form_cut_input.id = stocker_input.form_cut_id
            WHERE
                (form_cut_input.cancel is null or form_cut_input.cancel != 'Y') AND
                stocker_input.form_cut_id = '".$this->form_cut_id."' AND
                stocker_input.group_stocker = '".$this->group_stocker."' AND
                stocker_input.ratio = '".$this->ratio."' AND
                stocker_input.so_det_id = '".$this->so_det_id."'
            GROUP BY
                stocker_input.form_cut_id,
                stocker_input.so_det_id,
                stocker_input.group_stocker,
                stocker_input.ratio
            ORDER BY
                stocker_input.updated_at desc,
                stocker_input.created_at desc,
                form_cut_input.waktu_selesai desc,
                form_cut_input.waktu_mulai desc
            LIMIT 1
        ");

        $this->rowCount = 0;

        if ($stockerList[0]) {
            $stockerListNumber = YearSequence::selectRaw("
                year_sequence.id_year_sequence,
                year_sequence.number,
                year_sequence.year,
                year_sequence.year_sequence,
                year_sequence.year_sequence_number,
                master_sb_ws.size,
                master_sb_ws.dest
            ")->
            leftJoin("master_sb_ws", "master_sb_ws.id_so_det", "=", "year_sequence.so_det_id")->
            whereRaw("
                year_sequence.form_cut_id = '".$this->form_cut_id."' and
                year_sequence.so_det_id = '".$this->so_det_id."' and
                year_sequence.number >= '".$stockerList[0]->range_awal."' and
                year_sequence.number <= '".$stockerList[0]->range_akhir."'
            ")->
            get();

            $output = DB::connection("mysql_sb")->
                table("output_rfts")->
                selectRaw("
                    output_rfts.kode_numbering,
                    so_det.id,
                    userpassword.username sewing_line,
                    coalesce(output_rfts.updated_at) sewing_update,
                    output_rfts_packing.created_by packing_line,
                    coalesce(output_rfts_packing.updated_at) packing_update
                ")->
                leftJoin("output_rfts_packing", "output_rfts_packing.kode_numbering", "=", "output_rfts.kode_numbering")->
                leftJoin("so_det", "so_det.id", "=", "output_rfts.so_det_id")->
                leftJoin("user_sb_wip", "user_sb_wip.id", "=", "output_rfts.created_by")->
                leftJoin("userpassword", "userpassword.line_id", "=", "user_sb_wip.line_id")->
                whereIn("output_rfts.kode_numbering", $stockerListNumber->pluck("id_year_sequence"))->
                get();

            $this->rowCount = count($stockerListNumber);
        }

        return view("stocker.stocker.export.stocker-list-detail-export", [
            "stockerList" => $stockerList,
            "stockerListNumber" => $stockerListNumber,
            "output" => $output
        ]);
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => [self::class, 'afterSheet']
        ];
    }

    public static function afterSheet(AfterSheet $event)
    {
        $event->sheet->styleCells(
            'A4:I' . ($event->getConcernable()->rowCount+4),
            [
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                        'color' => ['argb' => '000000'],
                    ],
                ],
            ]
        );
    }

    // public function columnFormats(): array
    // {
    //     return [
    //         'E' => NumberFormat::FORMAT_NUMBER,
    //     ];
    // }
}
