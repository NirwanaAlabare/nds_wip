<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use \avadim\FastExcelLaravel\Excel as FastExcel;

class PurchasingReportController extends Controller
{

    public function itemReport(Request $request)
    {
        if ($request->ajax()) {
            $tgl_awal = $request->tgl_awal;
            $tgl_akhir = $request->tgl_akhir;

            $query = DB::connection('mysql_sb')->table('po_item as pi')
                ->join('po_header as h', 'pi.id_po', '=', 'h.id')
                ->leftJoin('mastersupplier as s', 'h.id_supplier', '=', 's.Id_Supplier')
                ->leftJoin('masteritem as mi', 'pi.id_gen', '=', 'mi.id_item')
                ->select(
                    'h.podate',
                    'h.pono',
                    'h.jenis',
                    's.Supplier as nama_supplier',
                    'mi.itemdesc',
                    'pi.product_set',
                    'pi.qty_pr_awal',
                    'pi.unit_pr_awal',
                    'pi.convert_val',
                    'pi.qty',
                    'pi.unit',
                    'pi.price',
                    DB::raw('(pi.qty * pi.price) as total_price')
                );


            if ($tgl_awal && $tgl_akhir) {
                $query->whereBetween('h.podate', [$tgl_awal, $tgl_akhir]);
            }

            $query->orderBy('h.podate', 'desc');

            return datatables()->of($query)
                ->editColumn('price', function ($row) {
                    return number_format($row->price, 2, '.', ',');
                })
                ->editColumn('total_price', function ($row) {
                    return number_format($row->total_price, 2, '.', ',');
                })
                ->editColumn('jenis', function ($row) {
                    return $row->jenis === 'M' ? 'Manufacturing' : 'Material';
                })
                ->make(true);
        }


        return view('purchasing.report.item_report', [
            'page' => 'dashboard-purchasing',
            'subPageGroup' => 'purchasing-report',
            'subPage' => 'item-report',
            'containerFluid' => true
        ]);
    }

    public function exportItemReport(Request $request)
    {
        $tgl_awal = $request->tgl_awal;
        $tgl_akhir = $request->tgl_akhir;

        $fileName = 'Laporan_Item_PO_' . ($tgl_awal ?: 'All') . '_sd_' . ($tgl_akhir ?: 'All') . '.xlsx';

        return Excel::download(new class($tgl_awal, $tgl_akhir) implements FromCollection, WithHeadings, WithMapping, WithStyles {
            protected $tgl_awal;
            protected $tgl_akhir;

            public function __construct($tgl_awal, $tgl_akhir)
            {
                $this->tgl_awal = $tgl_awal;
                $this->tgl_akhir = $tgl_akhir;
            }

            public function collection()
            {
                $query = DB::connection('mysql_sb')->table('po_item as pi')
                    ->join('po_header as h', 'pi.id_po', '=', 'h.id')
                    ->leftJoin('mastersupplier as s', 'h.id_supplier', '=', 's.Id_Supplier')
                    ->leftJoin('masteritem as mi', 'pi.id_gen', '=', 'mi.id_item')
                    ->select(
                        'h.podate',
                        'h.pono',
                        'h.jenis',
                        's.Supplier as nama_supplier',
                        'mi.itemdesc',
                        'pi.product_set',
                        'pi.qty_pr_awal',
                        'pi.unit_pr_awal',
                        'pi.convert_val',
                        'pi.qty',
                        'pi.unit',
                        'pi.price',
                        DB::raw('(pi.qty * pi.price) as total_price')
                    );

                if ($this->tgl_awal && $this->tgl_akhir) {
                    $query->whereBetween('h.podate', [$this->tgl_awal, $this->tgl_akhir]);
                }

                return $query->orderBy('h.podate', 'desc')->get();
            }

            public function headings(): array
            {

                $periode = ($this->tgl_awal && $this->tgl_akhir)
                    ? date('d-m-Y', strtotime($this->tgl_awal)) . ' s/d ' . date('d-m-Y', strtotime($this->tgl_akhir))
                    : 'Semua Tanggal';

                return [
                    ['NIRWANA ALABARE GARMENT'],
                    ['LAPORAN ITEM PURCHASE ORDER'],
                    ['Periode : ' . $periode],
                    [''],
                    [
                        'Tanggal PO',
                        'No PO',
                        'Jenis',
                        'Supplier',
                        'Item Description',
                        'Set',
                        'Qty Awal',
                        'Unit Awal',
                        'Convert',
                        'Qty',
                        'Unit',
                        'Price',
                        'Total Price'
                    ]
                ];
            }

            public function map($row): array
            {
                return [
                    $row->podate,
                    $row->pono,
                    $row->jenis === 'M' ? 'Manufacturing' : 'Material',
                    $row->nama_supplier,
                    $row->itemdesc,
                    $row->product_set ?: '-',
                    $row->qty_pr_awal,
                    $row->unit_pr_awal,
                    $row->convert_val,
                    $row->qty,
                    $row->unit,
                    $row->price,
                    $row->total_price
                ];
            }

            public function styles(Worksheet $sheet)
            {

                $sheet->mergeCells('A1:M1');
                $sheet->mergeCells('A2:M2');
                $sheet->mergeCells('A3:M3');

                return [
                    1 => ['font' => ['bold' => true, 'size' => 14], 'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]],
                    2 => ['font' => ['bold' => true, 'size' => 12], 'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]],
                    3 => ['font' => ['bold' => true, 'size' => 11], 'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]],
                    5 => ['font' => ['bold' => true]],
                ];
            }
        }, $fileName);
    }

    protected function historyItemPurchasingSql($id_item)
    {
        $id_item = (int) $id_item;

        $select = "
            ph.pono,
            ph.podate,
            ms.Supplier as nama_supplier,
            SUM(pi.qty) as qty,
            pi.unit,
            pi.price,
            mi.id_item,
            CASE WHEN mi.n_code_category IS NULL THEN mi.matclass ELSE mc.description END as jenis,
            mi.itemdesc
        ";

        return "
            SELECT {$select}
            FROM po_header ph
            INNER JOIN po_item pi ON ph.id = pi.id_po
            INNER JOIN mastersupplier ms ON ph.id_supplier = ms.Id_Supplier
            INNER JOIN masteritem mi ON pi.id_gen = mi.id_item
            LEFT JOIN mapping_category mc ON mi.n_code_category = mc.n_id
            WHERE ph.app = 'A' AND pi.cancel = 'N' AND ph.pono LIKE 'PO%' AND mi.id_item = {$id_item}
            GROUP BY ph.pono, mi.id_item, pi.unit, pi.price

            UNION ALL

            SELECT {$select}
            FROM po_header ph
            INNER JOIN po_item pi ON ph.id = pi.id_po
            INNER JOIN mastersupplier ms ON ph.id_supplier = ms.Id_Supplier
            INNER JOIN masteritem mi ON pi.id_gen = mi.id_gen
            LEFT JOIN mapping_category mc ON mi.n_code_category = mc.n_id
            WHERE ph.app = 'A' AND pi.cancel = 'N' AND ph.pono NOT LIKE 'PO%' AND mi.id_item = {$id_item}
            GROUP BY ph.pono, mi.id_item, pi.unit, pi.price
        ";
    }

    public function historyItemPurchasing(Request $request)
    {
        if ($request->ajax()) {
            $sql = $this->historyItemPurchasingSql($request->id_item);

            $query = DB::connection('mysql_sb')->table(DB::raw("({$sql}) as results"))
                ->orderBy('results.podate', 'desc');

            return datatables()->of($query)
                ->editColumn('podate', function ($row) {
                    return $row->podate ? date('d-m-Y', strtotime($row->podate)) : '-';
                })
                ->editColumn('price', function ($row) {
                    return number_format($row->price, 2, '.', ',');
                })
                ->addColumn('total_price', function ($row) {
                    return number_format($row->qty * $row->price, 2, '.', ',');
                })
                ->make(true);
        }

        return view('purchasing.report.history_item_purchasing', [
            'page' => 'dashboard-purchasing',
            'subPageGroup' => 'purchasing-report',
            'subPage' => 'history-item-purchasing',
            'containerFluid' => true
        ]);
    }

    public function exportHistoryItemPurchasing(Request $request)
    {
        $id_item = $request->id_item;

        if (!$id_item) {
            return response()->json(['message' => 'Silakan pilih item terlebih dahulu.'], 422);
        }

        $item = DB::connection('mysql_sb')->table('masteritem')
            ->where('id_item', $id_item)
            ->first(['id_item', 'goods_code', 'itemdesc', 'add_info', 'color', 'size']);

        $itemLabel = $item
            ? trim(($item->goods_code ?: '-') . ' - ' . ($item->itemdesc ?: '-'))
            : $id_item;

        $sql = $this->historyItemPurchasingSql($id_item);

        $rows = DB::connection('mysql_sb')->select("{$sql} ORDER BY podate DESC");

        $fileName = 'History_Item_Purchasing_' . $id_item . '.xlsx';

        $excel = FastExcel::create('History Item Purchasing');
        $sheet = $excel->sheet();

        $sheet->writeRow(['History Item Purchasing'], ['font-style' => 'bold', 'font-size' => 14]);
        $sheet->writeRow(['ID Item : ' . $id_item], ['font-size' => 12]);
        $sheet->writeRow(['Item : ' . $itemLabel], ['font-size' => 12]);
        $sheet->writeRow(['Color : ' . ($item && $item->color ? $item->color : '-')], ['font-size' => 12]);
        $sheet->writeRow(['Size : ' . ($item && $item->size ? $item->size : '-')], ['font-size' => 12]);
        $sheet->writeRow(['Keterangan : ' . ($item && $item->add_info ? $item->add_info : '-')], ['font-size' => 12]);
        $sheet->writeRow(['']);

        $header = ['Tanggal PO', 'No PO', 'Jenis', 'Supplier', 'Qty', 'Unit', 'Price', 'Total Price'];

        $sheet->writeRow($header, [
            'font-style' => 'bold',
            'border'     => 'thin',
            'halign'     => 'center',
        ]);

        foreach ($rows as $row) {
            $sheet->writeRow(
                [
                    $row->podate ? date('d-m-Y', strtotime($row->podate)) : '-',
                    $row->pono,
                    $row->jenis ?: '-',
                    $row->nama_supplier,
                    (float) $row->qty,
                    $row->unit,
                    (float) $row->price,
                    (float) ($row->qty * $row->price),
                ],
                ['border' => 'thin']
            );
        }

        foreach (range('A', 'H') as $col) {
            $sheet->setColWidth($col, 20);
        }

        $tmpPath = storage_path('app/tmp/fast-excel/' . uniqid('export_') . '.xlsx');
        $excel->save($tmpPath);

        return response()->download($tmpPath, $fileName)->deleteFileAfterSend(true);
    }

    public function searchItemPurchasing(Request $request)
    {
        $search = trim((string) $request->q);

        $items = collect();

        if (strlen($search) >= 2) {
            $items = DB::connection('mysql_sb')->table('masteritem')
                ->where('non_aktif', 'N')
                ->where(function ($q) use ($search) {
                    $q->where('id_item', 'like', "%{$search}%")
                        ->orWhere('goods_code', 'like', "%{$search}%")
                        ->orWhere('itemdesc', 'like', "%{$search}%")
                        ->orWhere('add_info', 'like', "%{$search}%")
                        ->orWhere('color', 'like', "%{$search}%")
                        ->orWhere('size', 'like', "%{$search}%");
                })
                ->orderBy('itemdesc')
                ->get(['id_item', 'goods_code', 'itemdesc', 'add_info', 'color', 'size']);
        }

        return response()->json([
            'results' => $items->map(function ($item) {
                $text = '[' . $item->id_item . '] ' . trim(($item->goods_code ?: '-') . ' - ' . ($item->itemdesc ?: '-'));

                if ($item->color) {
                    $text .= ' - ' . $item->color;
                }

                if ($item->size) {
                    $text .= ' - ' . $item->size;
                }

                if ($item->add_info) {
                    $text .= ' (' . $item->add_info . ')';
                }

                return [
                    'id' => $item->id_item,
                    'text' => $text,
                ];
            })->values(),
        ]);
    }
}
