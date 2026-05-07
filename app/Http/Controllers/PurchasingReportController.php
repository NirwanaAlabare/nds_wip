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
                ->editColumn('price', function($row) {
                    return number_format($row->price, 2, '.', ',');
                })
                ->editColumn('total_price', function($row) {
                    return number_format($row->total_price, 2, '.', ',');
                })
                ->editColumn('jenis', function($row) {
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

            public function __construct($tgl_awal, $tgl_akhir) {
                $this->tgl_awal = $tgl_awal;
                $this->tgl_akhir = $tgl_akhir;
            }

            public function collection() {
                $query = DB::connection('mysql_sb')->table('po_item as pi')
                    ->join('po_header as h', 'pi.id_po', '=', 'h.id')
                    ->leftJoin('mastersupplier as s', 'h.id_supplier', '=', 's.Id_Supplier')
                    ->leftJoin('masteritem as mi', 'pi.id_gen', '=', 'mi.id_item')
                    ->select(
                        'h.podate', 'h.pono', 'h.jenis', 's.Supplier as nama_supplier',
                        'mi.itemdesc', 'pi.product_set', 'pi.qty_pr_awal', 'pi.unit_pr_awal',
                        'pi.convert_val', 'pi.qty', 'pi.unit', 'pi.price',
                        DB::raw('(pi.qty * pi.price) as total_price')
                    );

                if ($this->tgl_awal && $this->tgl_akhir) {
                    $query->whereBetween('h.podate', [$this->tgl_awal, $this->tgl_akhir]);
                }

                return $query->orderBy('h.podate', 'desc')->get();
            }

            public function headings(): array {

                $periode = ($this->tgl_awal && $this->tgl_akhir)
                    ? date('d-m-Y', strtotime($this->tgl_awal)) . ' s/d ' . date('d-m-Y', strtotime($this->tgl_akhir))
                    : 'Semua Tanggal';

                return [
                    ['NIRWANA ALABARE GARMENT'],
                    ['LAPORAN ITEM PURCHASE ORDER'],
                    ['Periode : ' . $periode],
                    [''],
                    [
                        'Tanggal PO', 'No PO', 'Jenis', 'Supplier', 'Item Description',
                        'Set', 'Qty Awal', 'Unit Awal', 'Convert', 'Qty', 'Unit',
                        'Price', 'Total Price'
                    ]
                ];
            }

            public function map($row): array {
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

            public function styles(Worksheet $sheet) {

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
}
