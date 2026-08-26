<?php

namespace App\Exports\MgtReportDashboard;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class SheetCosting implements FromArray, WithEvents, WithHeadings, WithTitle
{
    use Exportable;

    /** kolom angka: mulai dari seluruh total biaya (kolom 15) */
    private const FIRST_NUMERIC_COL = 15;

    /** kolom angka yang berada sebelum kolom biaya: qty so, price so, qty cost */
    private const NUMERIC_COLS = [10, 11, 14];

    /** lebar merge untuk baris judul */
    private const TITLE_MERGE_COLS = 6;

    /** kolom identitas awal (cost no s/d status cost) dibuat lebih lebar */
    private const IDENTITY_WIDTH_COLS = 6;

    /** batas awal data costing yang diambil */
    private const COST_DATE_FROM = '2025-01-01';

    /** kolom string yang tetap ditulis apa adanya; sisanya dijadikan float */
    private const TEXT_COLS = [
        'cost_no', 'kpno', 'supplier', 'styleno', 'product_item',
        'season_desc', 'curr', 'so_date', 'status', 'cost_date', 'status_cost',
    ];

    private const HEADINGS = [
        'Cost No', 'WS Number', 'Buyer', 'Style', 'Product Item', 'Season', 'Curr',
        'SO Date', 'Status', 'Qty SO', 'Price SO', 'Cost Date', 'Status Cost', 'Qty Cost',
        'Fabric', 'Acc Sewing', 'Acc Packing', 'Total Material',
        'CMT', 'Embroidery', 'Washing', 'Printing', 'Wrapped Button',
        'Complexity Makloon Button', 'Label Print', 'Laser Cutting', 'Total Manufacturing',
        'Development', 'Overhead', 'Marketing', 'Shipping', 'Import Cost', 'Handling',
        'Testing', 'Fabric Handling', 'Service Charge', 'Clearance Cost',
        'Development (Others)', 'Unexpected Cost', 'Management Fee', 'Profit', 'Total Others',
    ];

    /**
     * Ditulis langsung sebagai array (bukan lewat blade/HTML) supaya jauh lebih cepat:
     * sheet ini isinya ribuan baris tanpa header bertingkat.
     */
    public function array(): array
    {
        $rows = [];

        foreach ($this->fetchData() as $row) {
            $values = [];

            foreach ((array) $row as $column => $value) {
                $values[] = in_array($column, self::TEXT_COLS, true) ? $value : (float) $value;
            }

            $rows[] = $values;
        }

        return $rows;
    }

    /** baris 1 judul, baris 2 header kolom */
    public function headings(): array
    {
        return [
            ['Laporan Costing'],
            self::HEADINGS,
        ];
    }

    public function title(): string
    {
        return 'Costing';
    }

    private function fetchData(): array
    {
        $from = self::COST_DATE_FROM;

        return DB::connection('mysql_sb')->select("SELECT
    a.cost_no, kpno, supplier, styleno, product_item, season_desc, curr,
    so_date, status, qty_so, price_so, cost_date, status_cost, qty_cost,

    COALESCE(b.ttl_fabric,0)  ttl_fabric,
    COALESCE(b.ttl_accsew,0)  ttl_accsew,
    COALESCE(b.ttl_accpack,0) ttl_accpack,
    (COALESCE(b.ttl_fabric,0) + COALESCE(b.ttl_accsew,0) + COALESCE(b.ttl_accpack,0)) ttl_material,

    COALESCE(c.ttl_cmt,0)     ttl_cmt,
    COALESCE(c.ttl_embro,0)   ttl_embro,
    COALESCE(c.ttl_wash,0)    ttl_wash,
    COALESCE(c.ttl_print,0)   ttl_print,
    COALESCE(c.ttl_wrapbut,0) ttl_wrapbut,
    COALESCE(c.ttl_compbut,0) ttl_compbut,
    COALESCE(c.ttl_label,0)   ttl_label,
    COALESCE(c.ttl_laser,0)   ttl_laser,
    (COALESCE(c.ttl_cmt,0) + COALESCE(c.ttl_embro,0) + COALESCE(c.ttl_wash,0) + COALESCE(c.ttl_print,0)
     + COALESCE(c.ttl_wrapbut,0) + COALESCE(c.ttl_compbut,0) + COALESCE(c.ttl_label,0) + COALESCE(c.ttl_laser,0)) ttl_manufacturing,

    COALESCE(d.ttl_develop,0)       ttl_develop,
    COALESCE(d.ttl_overhead,0)      ttl_overhead,
    COALESCE(d.ttl_market,0)        ttl_market,
    COALESCE(d.ttl_shipp,0)         ttl_shipp,
    COALESCE(d.ttl_import,0)        ttl_import,
    COALESCE(d.ttl_handl,0)         ttl_handl,
    COALESCE(d.ttl_test,0)          ttl_test,
    COALESCE(d.ttl_fabhandl,0)      ttl_fabhandl,
    COALESCE(d.ttl_service,0)       ttl_service,
    COALESCE(d.ttl_clearcost,0)     ttl_clearcost,
    COALESCE(d.ttl_development,0)   ttl_development,
    COALESCE(d.ttl_unexcost,0)      ttl_unexcost,
    COALESCE(d.ttl_managementfee,0) ttl_managementfee,
    COALESCE(d.ttl_profit,0)        ttl_profit,
    (COALESCE(d.ttl_develop,0) + COALESCE(d.ttl_overhead,0) + COALESCE(d.ttl_market,0) + COALESCE(d.ttl_shipp,0)
     + COALESCE(d.ttl_import,0) + COALESCE(d.ttl_handl,0) + COALESCE(d.ttl_test,0) + COALESCE(d.ttl_fabhandl,0)
     + COALESCE(d.ttl_service,0) + COALESCE(d.ttl_clearcost,0) + COALESCE(d.ttl_development,0)
     + COALESCE(d.ttl_unexcost,0) + COALESCE(d.ttl_managementfee,0) + COALESCE(d.ttl_profit,0)) ttl_others

FROM (
    SELECT a.cost_no, a.kpno, b.supplier, styleno, product_item, season_desc,
           IF(so.curr IS NULL, a.curr, so.curr) curr,
           so_date, IF(so.cancel_h = 'Y','CANCEL','-') status,
           so.qty qty_so, so.fob price_so, cost_date, a.status status_cost, a.qty qty_cost
    FROM act_costing a
    INNER JOIN mastersupplier b ON a.id_buyer = b.Id_Supplier
    INNER JOIN masterproduct mp ON a.id_product = mp.id
    LEFT JOIN so ON so.id_cost = a.id
    LEFT JOIN masterseason ms ON ms.id_season = so.id_season
    WHERE cost_date >= ? AND a.aktif = 'Y'
    GROUP BY cost_no
) a

LEFT JOIN (
    SELECT cost_no,
        SUM(CASE WHEN mattype = 'FABRIC'              THEN total END) ttl_fabric,
        SUM(CASE WHEN mattype = 'ACCESORIES SEWING'    THEN total END) ttl_accsew,
        SUM(CASE WHEN mattype = 'ACCESORIES PACKING'   THEN total END) ttl_accpack
    FROM (
        SELECT cost_no, mattype, IF(curr='IDR', val_idr, val_usd) total
        FROM act_material
        WHERE cost_date >= ?
    ) x
    GROUP BY cost_no
) b ON b.cost_no = a.cost_no

LEFT JOIN (
    SELECT cost_no,
        SUM(CASE WHEN mattype = 'CMT'                        THEN total END) ttl_cmt,
        SUM(CASE WHEN mattype = 'EMBRODEIRY'                 THEN total END) ttl_embro,
        SUM(CASE WHEN mattype = 'WASHING'                    THEN total END) ttl_wash,
        SUM(CASE WHEN mattype = 'PRINTING'                   THEN total END) ttl_print,
        SUM(CASE WHEN mattype = 'WRAPPED BUTTON'              THEN total END) ttl_wrapbut,
        SUM(CASE WHEN mattype = 'COMPLEXITY MAKLOON BUTTON'   THEN total END) ttl_compbut,
        SUM(CASE WHEN mattype = 'LABEL PRINT'                 THEN total END) ttl_label,
        SUM(CASE WHEN mattype = 'LASER CUTTING'               THEN total END) ttl_laser
    FROM (
        SELECT cost_no, mattype, IF(curr='IDR', val_idr, val_usd) total
        FROM act_manufacturing
        WHERE cost_date >= ?
    ) x
    GROUP BY cost_no
) c ON c.cost_no = a.cost_no

LEFT JOIN (
    SELECT cost_no,
        SUM(CASE WHEN mattype = 'DEVELOPMENT'       THEN total END) ttl_develop,
        SUM(CASE WHEN mattype = 'OVERHEAD'          THEN total END) ttl_overhead,
        SUM(CASE WHEN mattype = 'MARKETING'         THEN total END) ttl_market,
        SUM(CASE WHEN mattype = 'SHIPPING'          THEN total END) ttl_shipp,
        SUM(CASE WHEN mattype = 'IMPORT COST'       THEN total END) ttl_import,
        SUM(CASE WHEN mattype = 'HANDLING'          THEN total END) ttl_handl,
        SUM(CASE WHEN mattype = 'TESTING'           THEN total END) ttl_test,
        SUM(CASE WHEN mattype = 'FABRIC HANDLING'   THEN total END) ttl_fabhandl,
        SUM(CASE WHEN mattype = 'SERVICE CHARGE'    THEN total END) ttl_service,
        SUM(CASE WHEN mattype = 'CLEARANCE  COST'   THEN total END) ttl_clearcost,
        0                                                              ttl_development,
        SUM(CASE WHEN mattype = 'UNEXPECTED COST'   THEN total END) ttl_unexcost,
        SUM(CASE WHEN mattype = 'MANAGEMENT FEE'    THEN total END) ttl_managementfee,
        SUM(CASE WHEN mattype = 'PROFIT'            THEN total END) ttl_profit
    FROM (
        SELECT cost_no, mattype, IF(curr='IDR', val_idr, val_usd) total
        FROM act_others
        WHERE cost_date >= ?
    ) x
    GROUP BY cost_no
) d ON d.cost_no = a.cost_no", [$from, $from, $from, $from]);
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet         = $event->sheet->getDelegate();
                $highestRow    = $sheet->getHighestRow();
                $highestColumn = $sheet->getHighestColumn();
                $lastColIndex  = Coordinate::columnIndexFromString($highestColumn);

                // judul cukup selebar beberapa kolom saja, tidak perlu sampai kolom terakhir
                $titleColumn = Coordinate::stringFromColumnIndex(min(self::TITLE_MERGE_COLS, $lastColIndex));

                $sheet->mergeCells('A1:' . $titleColumn . '1');
                $sheet->getStyle('A1:' . $titleColumn . '1')->applyFromArray([
                    'font'      => ['bold' => true, 'size' => 14],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT],
                ]);

                $sheet->getStyle('A2:' . $highestColumn . '2')->applyFromArray([
                    'font'      => ['bold' => true, 'color' => ['argb' => 'FF000000']],
                    'fill'      => [
                        'fillType'   => Fill::FILL_SOLID,
                        'startColor' => ['argb' => 'FFD9EDF7'],
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical'   => Alignment::VERTICAL_CENTER,
                        'wrapText'   => true,
                    ],
                ]);

                if ($highestRow > 2) {
                    for ($i = 1; $i <= $lastColIndex; $i++) {
                        if ($i < self::FIRST_NUMERIC_COL && !in_array($i, self::NUMERIC_COLS, true)) {
                            continue;
                        }

                        $colLetter = Coordinate::stringFromColumnIndex($i);
                        $sheet->getStyle($colLetter . '3:' . $colLetter . $highestRow)->applyFromArray([
                            'numberFormat' => ['formatCode' => '#,##0.00;[Red]-#,##0.00'],
                            'alignment'    => [
                                'horizontal' => Alignment::HORIZONTAL_RIGHT,
                                'vertical'   => Alignment::VERTICAL_CENTER,
                            ],
                        ]);
                    }
                }

                // border hanya di baris judul + header: memberi border ke ~99rb sel data
                // menambah ~7 detik, sementara Excel sudah menampilkan gridline sendiri
                $sheet->getStyle('A2:' . $highestColumn . '2')->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color'       => ['argb' => 'FF000000'],
                        ],
                    ],
                ]);

                // lebar kolom di-set manual: auto-size sangat lambat untuk ribuan baris
                for ($i = 1; $i <= $lastColIndex; $i++) {
                    $sheet->getColumnDimension(Coordinate::stringFromColumnIndex($i))
                        ->setWidth($i <= self::IDENTITY_WIDTH_COLS ? 22 : 16);
                }

                $sheet->freezePane('A3');
                $sheet->setTitle($this->title());
            },
        ];
    }
}
