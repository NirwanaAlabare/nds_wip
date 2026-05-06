<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Carbon\Carbon;

class PurchasingController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $jenis = $request->jenis;
            $tahun = $request->tahun;
            $supplier = $request->supplier;

            $query = DB::connection('mysql_sb')->table('po_header as h')
                ->leftJoin('mastersupplier as s', 'h.id_supplier', '=', 's.Id_Supplier')
                ->leftJoin('masterpterms as t', 'h.id_terms', '=', 't.id')
                ->select('h.*', 's.Supplier as nama_supplier', 't.kode_pterms as nama_terms');

            if ($tahun) {
                $query->whereYear('h.podate', $tahun);
            }

            if ($supplier) {
                $query->where('h.id_supplier', $supplier);
            }

            if ($jenis === 'draft') {
                $query->whereIn('h.app', ['W', 'C']);
            } else {
                $query->where('h.app', 'A');
            }

            $query->orderBy('h.podate', 'desc')->orderBy('h.id', 'desc');

            return datatables()->of($query)
                ->addColumn('action', function ($row) use ($jenis) {
                    $host = request()->getHost();
                    $base_url = ($host == 'localhost' || $host == '127.0.0.1') ? 'http://localhost:8080' : 'http://' . $host . ':8080';

                    $url_pdf = $base_url . '/erp/pages/pur/pdfPO.php?id=' . $row->id;
                    $urlEdit = route('edit-purchase-order', $row->id);
                    $urlExcel = route('export-purchase-order', $row->id);

                    if ($jenis === 'draft') {
                        if ($row->app === 'C') {
                            return '<div class="d-flex justify-content-center">
                                        <button type="button" class="btn btn-sm btn-success mr-1 btn-restore" data-id="'.$row->id.'" title="Restore ke Draft"><i class="fas fa-undo"></i></button>
                                        <button type="button" class="btn btn-sm btn-primary mr-1 btn-view" data-id="'.$row->id.'" title="View"><i class="fas fa-eye"></i></button>
                                        <a href="' . $url_pdf . '" class="btn btn-sm btn-secondary mr-1" title="Print" target="_blank"><i class="fas fa-print"></i></a>
                                        <a href="' . $urlExcel . '" class="btn btn-sm btn-success" title="Export Excel"><i class="fas fa-file-excel"></i></a>
                                    </div>';
                        } else {
                            return '<div class="d-flex justify-content-center">
                                        <a href="' . $urlEdit . '" class="btn btn-sm btn-info mr-1" title="Edit"><i class="fas fa-edit"></i></a>
                                        <button type="button" class="btn btn-sm btn-danger mr-1 btn-cancel" data-id="'.$row->id.'" title="Cancel PO"><i class="fas fa-times"></i></button>
                                        <button type="button" class="btn btn-sm btn-primary mr-1 btn-view" data-id="'.$row->id.'" title="View"><i class="fas fa-eye"></i></button>
                                        <a href="' . $url_pdf . '" class="btn btn-sm btn-secondary mr-1" title="Print" target="_blank"><i class="fas fa-print"></i></a>
                                        <a href="' . $urlExcel . '" class="btn btn-sm btn-success" title="Export Excel"><i class="fas fa-file-excel"></i></a>
                                    </div>';
                        }
                    } else {
                        return '<div class="d-flex justify-content-center">
                                    <button type="button" class="btn btn-sm btn-primary mr-1 btn-view" data-id="'.$row->id.'" title="View"><i class="fas fa-eye"></i></button>
                                    <a href="' . $url_pdf . '" class="btn btn-sm btn-secondary mr-1" title="Print" target="_blank"><i class="fas fa-print"></i></a>
                                    <button type="button" class="btn btn-sm btn-warning mr-1 btn-edit-date" data-id="'.$row->id.'" data-etd="'.$row->etd.'" data-eta="'.$row->eta.'" title="Update ETD & ETA"><i class="fas fa-calendar-alt"></i></button>
                                    <a href="' . $urlExcel . '" class="btn btn-sm btn-success" title="Export Excel"><i class="fas fa-file-excel"></i></a>
                                </div>';
                    }
                })
                ->rawColumns(['action'])
                ->make(true);
        }

        $suppliers = DB::connection('mysql_sb')->table('mastersupplier')->get();

        return view('purchasing.po.index', [
            'page' => 'dashboard-purchasing',
            'subPageGroup' => 'purchasing',
            'subPage' => 'purchase-order',
            'suppliers' => $suppliers,
            'containerFluid' => true
        ]);
    }

    public function countData(Request $request)
    {
        $tahun = $request->tahun;
        $supplier = $request->supplier;

        $queryDraft = DB::connection('mysql_sb')->table('po_header')->where('app', 'W');
        $queryPo = DB::connection('mysql_sb')->table('po_header')->where('app', 'A');

        if ($tahun) {
            $queryDraft->whereYear('podate', $tahun);
            $queryPo->whereYear('podate', $tahun);
        }

        if ($supplier) {
            $queryDraft->where('id_supplier', $supplier);
            $queryPo->where('id_supplier', $supplier);
        }

        return response()->json([
            'draft' => $queryDraft->count(),
            'po'    => $queryPo->count()
        ]);
    }

    public function create(Request $request)
    {
        $mysql_sb = DB::connection('mysql_sb');
        $suppliers = $mysql_sb->table('mastersupplier')->where('tipe_sup', 'S')->orderBy('Supplier', 'ASC')->get();
        $currency = $mysql_sb->table('masterpilihan')->where('kode_pilihan', 'Curr')->select('id', 'nama_pilihan')->get();
        $payment_term = $mysql_sb->table('masterpterms')->where('aktif', 'Y')->select('id', DB::raw("CONCAT(kode_pterms, '-', nama_pterms) AS tampil"))->get();
        $tax = $mysql_sb->table('mtax')->where('category_tax', 'PPN')->where('idtax', 1)->select('percentage AS id', DB::raw("concat(kriteria,' ',percentage,'%') AS tampil"))->get();
        $day_terms = $mysql_sb->table('masterdayterms')->where('aktif', 'Y')->where('is_deleted', 'N')->select('id', DB::raw("concat(kode_pterms) AS tampil"))->get();
        $kategori_biaya = $mysql_sb->table('po_master_pilihan')->where('status', 'Y')->select('id', DB::raw("UPPER(nama_kategori) AS tampil"))->get();
        $units = $mysql_sb->table('masterpilihan')->where('kode_pilihan', 'Satuan')->select('nama_pilihan')->get();

        $style = $mysql_sb->table('so')
            ->join('bom_marketing', 'so.id_bom', '=', 'bom_marketing.id')
            ->join('act_costing_new', 'bom_marketing.id_costing', '=', 'act_costing_new.id')
            ->leftJoin('mastersupplier', 'act_costing_new.buyer', '=', 'mastersupplier.Id_supplier')
            ->whereNotNull('so.id_bom')
            ->where('so.qty', '>', 0)
            ->select('so.id_bom', 'so.so_no', DB::raw("CONCAT(act_costing_new.style, ' - ', mastersupplier.Supplier) AS style"))
            ->orderBy('so.d_insert', 'desc')
            ->groupBy('so.id_bom', 'act_costing_new.style')
            ->limit(500)
            ->get();

        return view('purchasing.po.create', [
            'page'           => 'dashboard-purchasing',
            'subPageGroup'   => 'purchasing',
            'subPage'        => 'purchase-requisition',
            'suppliers'      => $suppliers,
            'currency'       => $currency,
            'payment_term'   => $payment_term,
            'tax'            => $tax,
            'day_terms'      => $day_terms,
            'kategori_biaya' => $kategori_biaya,
            'style'          => $style,
            'units'          => $units,
            'containerFluid' => true
        ]);
    }

    public function edit($id)
    {
        $mysql_sb = DB::connection('mysql_sb');

        $po_header = $mysql_sb->table('po_header')->where('id', $id)->first();
        if (!$po_header) {
            return redirect()->route('purchasing')->with('error', 'Data PO tidak ditemukan');
        }

        $notes_po = $po_header->notes;
        $tipe_commercial = '';
        if (strpos($notes_po, 'Tipe: ') !== false) {
            $parts = explode(' | ', $notes_po);
            $tipe_commercial = str_replace('Tipe: ', '', $parts[0]);
            $notes_po = isset($parts[1]) ? $parts[1] : '';
        }

        $po_items = $mysql_sb->table('po_item as pi')
            ->leftJoin('masteritem as mi', 'pi.id_gen', '=', 'mi.id_item')
            ->leftJoin('bom_marketing as bm', 'pi.id_bom', '=', 'bm.id')
            ->leftJoin('act_costing_new as acn', 'bm.id_costing', '=', 'acn.id')
            ->where('pi.id_po', $id)
            ->select('pi.*', 'mi.itemdesc', 'acn.style as nama_style')
            ->get();

        $po_biaya = $mysql_sb->table('po_add_biaya as pb')
            ->leftJoin('po_master_pilihan as kb', 'pb.id_kategori', '=', 'kb.id')
            ->where('pb.id_po_draft', $id)
            ->select('pb.*', 'kb.nama_kategori')
            ->get();

        $suppliers = $mysql_sb->table('mastersupplier')->where('tipe_sup', 'S')->orderBy('Supplier', 'ASC')->get();
        $currency = $mysql_sb->table('masterpilihan')->where('kode_pilihan', 'Curr')->select('id', 'nama_pilihan')->get();
        $payment_term = $mysql_sb->table('masterpterms')->where('aktif', 'Y')->select('id', DB::raw("CONCAT(kode_pterms, '-', nama_pterms) AS tampil"))->get();
        $tax = $mysql_sb->table('mtax')->where('category_tax', 'PPN')->where('idtax', 1)->select('percentage AS id', DB::raw("concat(kriteria,' ',percentage,'%') AS tampil"))->get();
        $day_terms = $mysql_sb->table('masterdayterms')->where('aktif', 'Y')->where('is_deleted', 'N')->select('id', DB::raw("concat(kode_pterms) AS tampil"))->get();
        $kategori_biaya = $mysql_sb->table('po_master_pilihan')->where('status', 'Y')->select('id', DB::raw("UPPER(nama_kategori) AS tampil"))->get();
        $units = $mysql_sb->table('masterpilihan')->where('kode_pilihan', 'Satuan')->select('nama_pilihan')->get();

        $style = $mysql_sb->table('so')
            ->join('bom_marketing', 'so.id_bom', '=', 'bom_marketing.id')
            ->join('act_costing_new', 'bom_marketing.id_costing', '=', 'act_costing_new.id')
            ->leftJoin('mastersupplier', 'act_costing_new.buyer', '=', 'mastersupplier.Id_supplier')
            ->whereNotNull('so.id_bom')
            ->where('so.qty', '>', 0)
            ->select('so.id_bom', 'so.so_no', DB::raw("CONCAT(act_costing_new.style, ' - ', mastersupplier.Supplier) AS style"))
            ->orderBy('so.d_insert', 'desc')
            ->groupBy('so.id_bom', 'act_costing_new.style')
            ->limit(500)
            ->get();

        return view('purchasing.po.edit', [
            'page'           => 'dashboard-purchasing',
            'subPageGroup'   => 'purchasing',
            'subPage'        => 'purchase-requisition',
            'po_header'      => $po_header,
            'notes_po'       => $notes_po,
            'tipe_commercial'=> $tipe_commercial,
            'po_items'       => $po_items,
            'po_biaya'       => $po_biaya,
            'tax'            => $tax,
            'payment_term'   => $payment_term,
            'day_terms'      => $day_terms,
            'currency'       => $currency,
            'suppliers'      => $suppliers,
            'kategori_biaya' => $kategori_biaya,
            'style'          => $style,
            'units'          => $units,
            'containerFluid' => true
        ]);
    }

    public function getItemsByBom(Request $request)
    {
        $id_bom = $request->id_bom;
        $jenis_item = $request->jenis_item;

        $mysql_sb = DB::connection('mysql_sb');

        $so = $mysql_sb->table('so')->where('id_bom', $id_bom)->orderBy('id', 'desc')->first();

        if (!$so) return response()->json([]);

        $so_details = $mysql_sb->table('so_det as sd')
            ->leftJoin('master_colors_gmt as c', 'sd.id_color', '=', 'c.id')
            ->leftJoin('master_size_new as s', 'sd.id_size', '=', 's.id')
            ->where('sd.id_so', $so->id)
            ->select('sd.id_color', 'sd.id_size', 'sd.product_set', 'c.name as color_name', 's.size as size_name', $mysql_sb->raw('SUM(sd.qty) as qty'))
            ->groupBy('sd.id_color', 'sd.id_size', 'sd.product_set', 'c.name', 's.size')
            ->get();

        if ($so_details->isEmpty()) return response()->json([]);

        $bom_query = $mysql_sb->table('bom_marketing_detail as d')
            ->join('bom_marketing as h', 'd.id_bom_marketing', '=', 'h.id')
            ->leftJoin('masteritem as i', 'd.id_item', '=', 'i.id_item')
            ->leftJoin('mastercontents as e', 'd.id_contents', '=', 'e.id')
            ->leftJoin('mastertype2 as d2', 'e.id_type', '=', 'd2.id')
            ->leftJoin('mastersubgroup as s_grp', 'd2.id_sub_group', '=', 's_grp.id')
            ->leftJoin('mastergroup as a', 's_grp.id_group', '=', 'a.id')
            ->leftJoin('masterpilihan as u', 'd.unit', '=', 'u.id')
            ->leftJoin('master_set as mset', 'd.id_set', '=', 'mset.id')
            ->where('d.id_bom_marketing', $id_bom)
            ->whereNotNull('d.id_item');

        if ($jenis_item) {
            $bom_query->where('d.category', $jenis_item);
        }

        $bom_details_raw = $bom_query->select(
                'd.id as detail_id', 'd.id_item', 'd.id_contents', 'd.id_color as bom_id_color',
                'd.id_size as bom_id_size', 'd.id_set', 'mset.nama as bom_product_set', 'd.rule_bom',
                'd.category', 'a.nama_group', 'i.itemdesc', 'i.color as i_color', 'i.size as i_size',
                'i.add_info', 'd.qty as cons', 'd.unit as unit'
            )->get();

        $bom_details_raw = $bom_details_raw->map(function($item) {
            if ($item->category == 'Manufacturing') {
                $item->item_desc_formatted = trim(preg_replace('/\s+/', ' ', $item->itemdesc . ' ' . $item->i_color . ' ' . $item->i_size . ' ' . $item->add_info));
            } else {
                $item->item_desc_formatted = $item->itemdesc;
            }
            return $item;
        });

        $bom_details = $bom_details_raw->unique(function ($item) {
            if ($item->rule_bom == 'All Color All Size') {
                return $item->id_item . '-' . $item->id_contents . '-' . $item->id_set;
            }
            return $item->detail_id;
        });

        $grouped_data = [];

        foreach ($bom_details as $bom) {
            foreach ($so_details as $sdet) {
                $is_match = false;

                if ($bom->rule_bom == 'All Color All Size') {
                    $is_match = true;
                } elseif ($bom->rule_bom == 'Per Color All Size' && $sdet->id_color == $bom->bom_id_color) {
                    $is_match = true;
                } elseif ($bom->rule_bom == 'All Color Range Size' && $sdet->id_size == $bom->bom_id_size) {
                    $is_match = true;
                } elseif ($sdet->id_color == $bom->bom_id_color && $sdet->id_size == $bom->bom_id_size) {
                    $is_match = true;
                }

                $bom_set = strtoupper(trim($bom->bom_product_set ?? ''));
                $so_set  = strtoupper(trim($sdet->product_set ?? ''));
                if ($bom_set !== '' && $bom_set !== $so_set) {
                    $is_match = false;
                }

                if ($is_match) {
                    if (in_array($bom->rule_bom, ['All Color All Size', 'All Color Range Size'])) {
                        $color_label = 'ALL COLOR';
                    } else {
                        $color_label = strtoupper(trim($sdet->color_name ?? 'ALL COLOR'));
                    }

                    $set_label = $so_set ?: 'SINGLE';
                    $key = $bom->id_item . '_' . $color_label . '_' . $set_label . '_' . $bom->unit;

                    if (!isset($grouped_data[$key])) {
                        $display_set = $set_label;
                        $nama_group = strtoupper(trim($bom->nama_group ?? ''));
                        $sort_val = 5;
                        if (strpos($nama_group, 'FABRIC') !== false) $sort_val = 1;
                        elseif (strpos($nama_group, 'SEWING') !== false) $sort_val = 2;
                        elseif (strpos($nama_group, 'PACKING') !== false) $sort_val = 3;
                        elseif ($bom->category === 'Manufacturing') $sort_val = 4;

                        $grouped_data[$key] = [
                            'id_item'     => $bom->id_item,
                            'itemdesc'    => $bom->item_desc_formatted,
                            'cons_bom'    => 0,
                            'cons_asli'   => (float) $bom->cons,
                            'unit'        => $bom->unit,
                            'product_set' => $display_set,
                            'sort_group'  => $sort_val
                        ];
                    }

                    $qty_baju = (float) $sdet->qty;
                    $cons = (float) $bom->cons;
                    $grouped_data[$key]['cons_bom'] += ($qty_baju * $cons);
                }
            }
        }

        $result = collect(array_values($grouped_data))->sortBy([
            ['sort_group', 'asc'],
            ['product_set', 'desc'],
            ['itemdesc', 'asc']
        ])->map(function ($item) {
            $item['cons_bom'] = round($item['cons_bom'], 3);
            return $item;
        })->values()->all();

        return response()->json($result);
    }

    public function store(Request $request)
    {
        $request->validate([
            'po_date'     => 'required',
            'id_supplier' => 'required',
            'id_item'     => 'required|array'
        ]);

        $mysql_sb = DB::connection('mysql_sb');

        try {
            $mysql_sb->beginTransaction();

            $id_boms = [];
            if ($request->has('style_item')) {
                $id_boms = array_filter(array_unique($request->style_item));
            }

            $kode_buyer = [];
            if (count($id_boms) > 0) {
                $buyers = $mysql_sb->table('bom_marketing')
                    ->join('act_costing_new', 'bom_marketing.id_costing', '=', 'act_costing_new.id')
                    ->leftJoin('mastersupplier', 'act_costing_new.buyer', '=', 'mastersupplier.Id_supplier')
                    ->whereIn('bom_marketing.id', $id_boms)
                    ->select('mastersupplier.supplier_code as kode_buyer')
                    ->groupBy('mastersupplier.Supplier')
                    ->pluck('kode_buyer')
                    ->toArray();

                $kode_buyer = array_filter($buyers);
            }

            $date = \Carbon\Carbon::parse($request->po_date);
            $bulan_tahun = $date->format('my');

            if (count($kode_buyer) == 1) {
                $buyer_code = array_values($kode_buyer)[0];
                $prefix = $buyer_code . "/" . $bulan_tahun . "/";
            } elseif (count($kode_buyer) > 1) {
                $buyer_code_first = array_values($kode_buyer)[0];
                $prefix = "C/" . $buyer_code_first . "/" . $bulan_tahun . "/";
            } else {
                $prefix = "PO/" . $bulan_tahun . "/";
            }

            $last_po = $mysql_sb->table('po_header')
                ->where('pono', 'like', $prefix . '%')
                ->orderBy('pono', 'desc')
                ->first();

            if ($last_po) {
                $last_number = (int) substr($last_po->pono, -5);
                $no_urut = str_pad($last_number + 1, 5, '0', STR_PAD_LEFT);
            } else {
                $no_urut = '00001';
            }

            $pono = $prefix . $no_urut;
            $kurs = (float) str_replace(',', '', $request->kurs ?? 0);

            $style_string = null;
            if (count($id_boms) > 0) {
                $styles = $mysql_sb->table('bom_marketing')
                    ->join('act_costing_new', 'bom_marketing.id_costing', '=', 'act_costing_new.id')
                    ->whereIn('bom_marketing.id', $id_boms)
                    ->pluck('act_costing_new.style')
                    ->unique()
                    ->toArray();

                if (count($styles) > 0) {
                    $style_string = implode(', ', $styles);
                }
            }

            $id_po = $mysql_sb->table('po_header')->insertGetId([
                'pono'            => $pono,
                'podate'          => $request->po_date,
                'id_supplier'     => $request->id_supplier,
                'id_terms'        => $request->payment_term,
                'n_kurs'          => $kurs,
                'notes'           => $request->notes_po,
                'tax'             => $request->tax,
                'jenis'           => $request->jenis_item == 'Manufacturing' ? 'M' : 'P',
                'jml_pterms'      => $request->days ?: 0,
                'id_dayterms'     => $request->day_terms,
                'username'        => auth()->user()->name ?? '',
                'tipe_commercial' => $request->tipe_commercial ?? '',
                'app'             => 'W',
                'style'           => $style_string,
            ]);

            $po_items = [];
            $po_items_draft = [];
            $jo_data = [];

            foreach ($request->id_item as $index => $id_item) {
                $id_bom = $request->style_item[$index] ?? null;
                $id_jo = null;

                if ($id_bom) {
                    if (!array_key_exists($id_bom, $jo_data)) {
                        $so = $mysql_sb->table('so')->where('id_bom', $id_bom)->orderBy('id', 'desc')->first();
                        if ($so) {
                            $jo_det = $mysql_sb->table('jo_det')->where('id_so', $so->id)->first();
                            $jo_data[$id_bom] = $jo_det ? $jo_det->id_jo : null;
                        } else {
                            $jo_data[$id_bom] = null;
                        }
                    }
                    $id_jo = $jo_data[$id_bom];
                }

                $stok_item          = (float) str_replace(',', '', $request->stok_item[$index] ?? 0);
                $qty_bom            = (float) str_replace(',', '', $request->qty_bom[$index] ?? 0);
                $qty_need           = (float) str_replace(',', '', $request->qty_need[$index] ?? 0);
                $blc_pr             = (float) str_replace(',', '', $request->blc_pr[$index] ?? 0);
                $qty_pr_awal        = (float) str_replace(',', '', $request->qty_pr[$index] ?? 0);
                $unit_pr_awal       = $request->unit_pr[$index] ?? '';
                $convert            = (float) str_replace(',', '', $request->convert[$index] ?? 0);
                $unit_convert       = $request->unit_convert[$index] ?? '';
                $price_costing      = (float) str_replace(',', '', $request->price_costing[$index] ?? 0);
                $price_costing_conv = (float) str_replace(',', '', $request->price_costing_conv[$index] ?? 0);
                $price_pr           = (float) str_replace(',', '', $request->price_pr[$index] ?? 0);

                $qty_final  = ($convert > 0) ? (float) str_replace(',', '', $request->qty_pr_conv[$index]) : $qty_pr_awal;
                $unit_final = ($convert > 0) ? $request->unit_pr_conv[$index] : $unit_pr_awal;

                if ($qty_final > 0) {
                    $item_data = [
                        'id_po'              => $id_po,
                        'id_jo'              => $id_jo,
                        'id_gen'             => $id_item,
                        'qty'                => $qty_final,
                        'unit'               => $unit_final,
                        'curr'               => $request->currency,
                        'price'              => $price_pr,
                        'cancel'             => 'N',
                        'id_bom'             => $id_bom,
                        'product_set'        => $request->product_set[$index] ?? '',
                        'stok_item'          => $stok_item,
                        'qty_bom'            => $qty_bom,
                        'qty_need'           => $qty_need,
                        'blc_pr'             => $blc_pr,
                        'qty_pr_awal'        => $qty_pr_awal,
                        'unit_pr_awal'       => $unit_pr_awal,
                        'convert_val'        => $convert,
                        'unit_convert'       => $unit_convert,
                        'price_costing'      => $price_costing,
                        'price_costing_conv' => $price_costing_conv,
                    ];

                    $item_data_draft = [
                        'id_po_draft'        => $id_po,
                        'id_jo'              => $id_jo,
                        'id_gen'             => $id_item,
                        'qty'                => $qty_final,
                        'unit'               => $unit_final,
                        'curr'               => $request->currency,
                        'price'              => $price_pr,
                        'cancel'             => 'N',
                        'id_bom'             => $id_bom,
                        'product_set'        => $request->product_set[$index] ?? '',
                        'stok_item'          => $stok_item,
                        'qty_bom'            => $qty_bom,
                        'qty_need'           => $qty_need,
                        'blc_pr'             => $blc_pr,
                        'qty_pr_awal'        => $qty_pr_awal,
                        'unit_pr_awal'       => $unit_pr_awal,
                        'convert_val'        => $convert,
                        'unit_convert'       => $unit_convert,
                        'price_costing'      => $price_costing,
                        'price_costing_conv' => $price_costing_conv,
                    ];

                    $po_items[] = $item_data;
                    $po_items_draft[] = $item_data_draft;
                }
            }

            if (count($po_items) > 0) {
                $mysql_sb->table('po_item')->insert($po_items);
                $mysql_sb->table('po_item_draft')->insert($po_items_draft);
            } else {
                $mysql_sb->rollBack();
                return response()->json([
                    'status'  => 400,
                    'message' => 'Gagal! Semua nilai Qty PR pada item masih 0.'
                ]);
            }

            $po_biaya = [];
            $po_biaya_temp = [];
            if ($request->has('kategori_biaya') && is_array($request->kategori_biaya)) {
                foreach ($request->kategori_biaya as $index => $kategori) {
                    $total_biaya = (float) str_replace(',', '', $request->total_biaya[$index] ?? 0);
                    $ppn_biaya   = (float) str_replace(',', '', $request->ppn_biaya[$index] ?? 0);

                    if ($total_biaya > 0) {
                        $biaya_data = [
                            'id_po_draft' => $id_po,
                            'id_kategori' => $kategori,
                            'total'       => $total_biaya,
                            'ppn'         => $ppn_biaya,
                            'keterangan'  => $request->desc_biaya[$index] ?? '',
                            'status'      => 'Y',
                            'created_by'  => auth()->user()->name ?? '',
                            'created_at'  => now(),
                        ];

                        $po_biaya[] = $biaya_data;
                        $po_biaya_temp[] = $biaya_data;
                    }
                }
            }

            if (count($po_biaya) > 0) {
                $mysql_sb->table('po_add_biaya')->insert($po_biaya);
                $mysql_sb->table('po_add_biaya_temp')->insert($po_biaya_temp);
            }

            $mysql_sb->commit();

            return response()->json([
                'status'  => 200,
                'message' => 'PO Berhasil Dibuat (' . $pono . ')'
            ]);

        } catch (\Exception $e) {
            $mysql_sb->rollBack();
            return response()->json([
                'status'  => 500,
                'message' => 'Terjadi Kesalahan Server: ' . $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'po_date'     => 'required',
            'id_supplier' => 'required',
            'id_item'     => 'required|array'
        ]);

        $mysql_sb = DB::connection('mysql_sb');

        try {
            $mysql_sb->beginTransaction();

            $notes = $request->notes_po;
            $kurs = (float) str_replace(',', '', $request->kurs ?? 0);

            $id_boms = [];
            if ($request->has('style_item')) {
                $id_boms = array_filter(array_unique($request->style_item));
            }

            $style_string = null;
            if (count($id_boms) > 0) {
                $styles = $mysql_sb->table('bom_marketing')
                    ->join('act_costing_new', 'bom_marketing.id_costing', '=', 'act_costing_new.id')
                    ->whereIn('bom_marketing.id', $id_boms)
                    ->pluck('act_costing_new.style')
                    ->unique()
                    ->toArray();

                if (count($styles) > 0) {
                    $style_string = implode(', ', $styles);
                }
            }

            $mysql_sb->table('po_header')->where('id', $id)->update([
                'podate'          => $request->po_date,
                'id_supplier'     => $request->id_supplier,
                'id_terms'        => $request->payment_term,
                'n_kurs'          => $kurs,
                'notes'           => $notes,
                'tax'             => $request->tax,
                'jenis'           => $request->jenis_item == 'Manufacturing' ? 'M' : 'P',
                'style'           => $style_string,
                'jml_pterms'      => $request->days ?: 0,
                'id_dayterms'     => $request->day_terms,
                'username'        => auth()->user()->name ?? '',
                'tipe_commercial' => $request->tipe_commercial ?? '',
            ]);

            $mysql_sb->table('po_item')->where('id_po', $id)->delete();
            $mysql_sb->table('po_item_draft')->where('id_po_draft', $id)->delete();
            $mysql_sb->table('po_add_biaya')->where('id_po_draft', $id)->delete();

            try {
                $mysql_sb->table('po_add_biaya_temp')->where('id_po_draft', $id)->delete();
            } catch (\Exception $e) {}

            $po_items = [];
            $po_items_draft = [];
            $jo_data = [];

            foreach ($request->id_item as $index => $id_item) {
                $id_bom = $request->style_item[$index] ?? null;
                $id_jo = null;

                if ($id_bom) {
                    if (!array_key_exists($id_bom, $jo_data)) {
                        $so = $mysql_sb->table('so')->where('id_bom', $id_bom)->orderBy('id', 'desc')->first();
                        if ($so) {
                            $jo_det = $mysql_sb->table('jo_det')->where('id_so', $so->id)->first();
                            $jo_data[$id_bom] = $jo_det ? $jo_det->id_jo : null;
                        } else {
                            $jo_data[$id_bom] = null;
                        }
                    }
                    $id_jo = $jo_data[$id_bom];
                }

                $stok_item          = (float) str_replace(',', '', $request->stok_item[$index] ?? 0);
                $qty_bom            = (float) str_replace(',', '', $request->qty_bom[$index] ?? 0);
                $qty_need           = (float) str_replace(',', '', $request->qty_need[$index] ?? 0);
                $blc_pr             = (float) str_replace(',', '', $request->blc_pr[$index] ?? 0);
                $qty_pr_awal        = (float) str_replace(',', '', $request->qty_pr[$index] ?? 0);
                $unit_pr_awal       = $request->unit_pr[$index] ?? '';
                $convert            = (float) str_replace(',', '', $request->convert[$index] ?? 0);
                $unit_convert       = $request->unit_convert[$index] ?? '';
                $price_costing      = (float) str_replace(',', '', $request->price_costing[$index] ?? 0);
                $price_costing_conv = (float) str_replace(',', '', $request->price_costing_conv[$index] ?? 0);
                $price_pr           = (float) str_replace(',', '', $request->price_pr[$index] ?? 0);

                $qty_final  = ($convert > 0) ? (float) str_replace(',', '', $request->qty_pr_conv[$index]) : $qty_pr_awal;
                $unit_final = ($convert > 0) ? $request->unit_pr_conv[$index] : $unit_pr_awal;

                if ($qty_final > 0) {
                    $item_data = [
                        'id_po'              => $id,
                        'id_jo'              => $id_jo,
                        'id_gen'             => $id_item,
                        'qty'                => $qty_final,
                        'unit'               => $unit_final,
                        'curr'               => $request->currency,
                        'price'              => $price_pr,
                        'cancel'             => 'N',
                        'id_bom'             => $id_bom,
                        'product_set'        => $request->product_set[$index] ?? '',
                        'stok_item'          => $stok_item,
                        'qty_bom'            => $qty_bom,
                        'qty_need'           => $qty_need,
                        'blc_pr'             => $blc_pr,
                        'qty_pr_awal'        => $qty_pr_awal,
                        'unit_pr_awal'       => $unit_pr_awal,
                        'convert_val'        => $convert,
                        'unit_convert'       => $unit_convert,
                        'price_costing'      => $price_costing,
                        'price_costing_conv' => $price_costing_conv,
                    ];

                    $item_data_draft = [
                        'id_po_draft'        => $id,
                        'id_jo'              => $id_jo,
                        'id_gen'             => $id_item,
                        'qty'                => $qty_final,
                        'unit'               => $unit_final,
                        'curr'               => $request->currency,
                        'price'              => $price_pr,
                        'cancel'             => 'N',
                        'id_bom'             => $id_bom,
                        'product_set'        => $request->product_set[$index] ?? '',
                        'stok_item'          => $stok_item,
                        'qty_bom'            => $qty_bom,
                        'qty_need'           => $qty_need,
                        'blc_pr'             => $blc_pr,
                        'qty_pr_awal'        => $qty_pr_awal,
                        'unit_pr_awal'       => $unit_pr_awal,
                        'convert_val'        => $convert,
                        'unit_convert'       => $unit_convert,
                        'price_costing'      => $price_costing,
                        'price_costing_conv' => $price_costing_conv,
                    ];

                    $po_items[] = $item_data;
                    $po_items_draft[] = $item_data_draft;
                }
            }

            if (count($po_items) > 0) {
                $mysql_sb->table('po_item')->insert($po_items);
                $mysql_sb->table('po_item_draft')->insert($po_items_draft);
            } else {
                $mysql_sb->rollBack();
                return response()->json([
                    'status'  => 400,
                    'message' => 'Gagal! Semua nilai Qty PR pada item masih 0.'
                ]);
            }

            $po_biaya = [];
            $po_biaya_temp = [];
            if ($request->has('kategori_biaya') && is_array($request->kategori_biaya)) {
                foreach ($request->kategori_biaya as $index => $kategori) {
                    $total_biaya = (float) str_replace(',', '', $request->total_biaya[$index] ?? 0);
                    $ppn_biaya   = (float) str_replace(',', '', $request->ppn_biaya[$index] ?? 0);

                    if ($total_biaya > 0) {
                        $biaya_data = [
                            'id_po_draft' => $id,
                            'id_kategori' => $kategori,
                            'total'       => $total_biaya,
                            'ppn'         => $ppn_biaya,
                            'keterangan'  => $request->desc_biaya[$index] ?? '',
                            'status'      => 'Y',
                            'created_by'  => auth()->user()->name ?? '',
                            'created_at'  => now(),
                        ];

                        $po_biaya[] = $biaya_data;
                        $po_biaya_temp[] = $biaya_data;
                    }
                }
            }

            if (count($po_biaya) > 0) {
                $mysql_sb->table('po_add_biaya')->insert($po_biaya);
                $mysql_sb->table('po_add_biaya_temp')->insert($po_biaya_temp);
            }

            $mysql_sb->commit();

            return response()->json([
                'status'  => 200,
                'message' => 'Data PO Berhasil Diupdate!'
            ]);

        } catch (\Exception $e) {
            $mysql_sb->rollBack();
            return response()->json([
                'status'  => 500,
                'message' => 'Terjadi Kesalahan Server: ' . $e->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        $mysql_sb = DB::connection('mysql_sb');

        $header = $mysql_sb->table('po_header as h')
            ->leftJoin('mastersupplier as s', 'h.id_supplier', '=', 's.Id_Supplier')
            ->leftJoin('masterpterms as pt', 'h.id_terms', '=', 'pt.id')
            ->where('h.id', $id)
            ->select('h.*', 's.Supplier as nama_supplier', 'pt.kode_pterms as nama_terms')
            ->first();

        $items = $mysql_sb->table('po_item as pi')
            ->leftJoin('masteritem as mi', 'pi.id_gen', '=', 'mi.id_item')
            ->leftJoin('bom_marketing as bm', 'pi.id_bom', '=', 'bm.id')
            ->leftJoin('act_costing_new as acn', 'bm.id_costing', '=', 'acn.id')
            ->where('pi.id_po', $id)
            ->select('pi.*', 'mi.itemdesc', 'acn.style as nama_style')
            ->get();

        $biaya = $mysql_sb->table('po_add_biaya as pb')
            ->leftJoin('po_master_pilihan as kb', 'pb.id_kategori', '=', 'kb.id')
            ->where('pb.id_po_draft', $id)
            ->select('pb.*', 'kb.nama_kategori')
            ->get();

        return response()->json([
            'header' => $header,
            'items'  => $items,
            'biaya'  => $biaya
        ]);
    }

    public function updateDate(Request $request, $id)
    {
        try {
            DB::connection('mysql_sb')->table('po_header')->where('id', $id)->update([
                'etd' => $request->etd ?: null,
                'eta' => $request->eta ?: null,
            ]);

            return response()->json([
                'status'  => 200,
                'message' => 'Tanggal ETD & ETA Berhasil Diupdate!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => 500,
                'message' => 'Gagal Update Tanggal: ' . $e->getMessage()
            ], 500);
        }
    }

    public function approval(Request $request)
    {
        if ($request->ajax()) {
            $tahun = $request->tahun;
            $supplier = $request->supplier;

            $query = DB::connection('mysql_sb')->table('po_header as h')
                ->leftJoin('mastersupplier as s', 'h.id_supplier', '=', 's.Id_Supplier')
                ->leftJoin('masterpterms as t', 'h.id_terms', '=', 't.id')
                ->select('h.*', 's.Supplier as nama_supplier', 't.kode_pterms as nama_terms');

            if ($tahun) {
                $query->whereYear('h.podate', $tahun);
            }

            if ($supplier) {
                $query->where('h.id_supplier', $supplier);
            }

            $query->where('h.app', 'W');
            $query->orderBy('h.podate', 'desc')->orderBy('h.id', 'desc');

            return datatables()->of($query)
                ->addColumn('action', function ($row) {
                    $host = request()->getHost();
                    $base_url = ($host == 'localhost' || $host == '127.0.0.1') ? 'http://localhost:8080' : 'http://' . $host . ':8080';
                    $url_pdf = $base_url . '/erp/pages/pur/pdfPO.php?id=' . $row->id;
                    $urlExcel = route('export-purchase-order', $row->id);

                    return '<div class="d-flex justify-content-center">
                                <button type="button" class="btn btn-sm btn-success mr-1 btn-approve" data-id="'.$row->id.'" title="Approve"><i class="fas fa-check"></i></button>
                                <button type="button" class="btn btn-sm btn-primary mr-1 btn-view" data-id="'.$row->id.'" title="View"><i class="fas fa-eye"></i></button>
                                <a href="' . $url_pdf . '" class="btn btn-sm btn-secondary mr-1" title="Print" target="_blank"><i class="fas fa-print"></i></a>
                                <a href="' . $urlExcel . '" class="btn btn-sm btn-warning" title="Export Excel"><i class="fas fa-file-excel"></i></a>
                            </div>';
                })
                ->rawColumns(['action'])
                ->make(true);
        }

        $suppliers = DB::connection('mysql_sb')->table('mastersupplier')->get();

        return view('purchasing.po.approval', [
            'page' => 'dashboard-purchasing',
            'subPageGroup' => 'purchasing',
            'subPage' => 'purchase-order',
            'suppliers' => $suppliers,
            'containerFluid' => true
        ]);
    }

    public function approve(Request $request, $id)
    {
        try {
            DB::connection('mysql_sb')->table('po_header')->where('id', $id)->update([
                'app' => 'A',
                'app_date' => now(),
                'app_by' => auth()->user()->name ?? '',
            ]);

            return response()->json([
                'status'  => 200,
                'message' => 'PO Berhasil Diapprove!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => 500,
                'message' => 'Gagal Approve PO: ' . $e->getMessage()
            ], 500);
        }
    }

   public function exportExcel($id)
    {
        $header = DB::connection('mysql_sb')->table('po_header as h')
            ->leftJoin('mastersupplier as s', 'h.id_supplier', '=', 's.Id_Supplier')
            ->leftJoin('masterpterms as pt', 'h.id_terms', '=', 'pt.id')
            ->select('h.*', 's.Supplier as nama_supplier', 'pt.kode_pterms as nama_terms')
            ->where('h.id', $id)
            ->first();

        if (!$header) return back()->with('error', 'Data PO tidak ditemukan');

        $po_no = str_replace('/', '_', $header->pono);
        $file_name = 'PO_' . $po_no . '.xlsx';

        return Excel::download(new class($id, $header) implements FromCollection, WithHeadings, WithMapping, WithStyles {
            protected $id_po;
            protected $header;

            public function __construct($id_po, $header) {
                $this->id_po = $id_po;
                $this->header = $header;
            }

            public function collection() {
                return DB::connection('mysql_sb')->table('po_item as pi')
                    ->join('po_header as h', 'pi.id_po', '=', 'h.id')
                    ->leftJoin('mastersupplier as s', 'h.id_supplier', '=', 's.Id_Supplier')
                    ->leftJoin('masteritem as mi', 'pi.id_gen', '=', 'mi.id_item')
                    ->leftJoin('bom_marketing as bm', 'pi.id_bom', '=', 'bm.id')
                    ->leftJoin('act_costing_new as acn', 'bm.id_costing', '=', 'acn.id')
                    ->select(
                        'h.podate', 'h.pono', 'h.jenis', 's.Supplier as nama_supplier',
                        'acn.style', 'mi.itemdesc', 'pi.product_set', 'pi.qty_pr_awal', 'pi.unit_pr_awal',
                        'pi.convert_val', 'pi.qty', 'pi.unit', 'pi.price', 'h.n_kurs', 'h.notes'
                    )
                    ->where('pi.id_po', $this->id_po)
                    ->get();
            }

            public function headings(): array {
                $jenis_item = $this->header->jenis == 'M' ? 'Manufacturing' : 'Material';
                $p_terms = ($this->header->jml_pterms ?? 0) . ' Days - ' . ($this->header->nama_terms ?? '-');
                $kurs = number_format($this->header->n_kurs, 2, ',', '.');
                $po_no_tampil = $this->header->pono ?: $this->header->pono;

                return [
                    ['NIRWANA ALABARE GARMENT'],
                    ['PURCHASE ORDER (' . $po_no_tampil . ')'],
                    [''],

                    ['INFO PO'],
                    ['No PO:', $po_no_tampil, '', 'Tanggal PO:', (date('d-m-Y', strtotime($this->header->podate))), '', 'Supplier:', $this->header->nama_supplier, '', 'Jenis Item:', $jenis_item],
                    ['P Terms (Days):', $p_terms, '', 'Tax:', $this->header->tax ?? '-', '', 'Kurs:', $kurs, '', 'Tipe Comm:', $this->header->tipe_commercial ?? '-'],
                    ['Notes:', $this->header->notes ?? '-'],
                    [''],
                    [''],
                    [
                        'Tanggal PO',
                        'No PO',
                        'Jenis',
                        'Supplier',
                        'Style',
                        'Item Description',
                        'Set',
                        'Qty Awal',
                        'Unit Awal',
                        'Convert',
                        'Qty',
                        'Unit',
                        'Price',
                        'Kurs',
                        'Total (Price * Qty)',
                        'Notes'
                    ]
                ];
            }

            public function map($row): array {
                return [
                    $row->podate,
                    $row->pono ?: '-',
                    $row->jenis,
                    $row->nama_supplier,
                    $row->style ?: '-',
                    $row->itemdesc,
                    $row->product_set ?: '-',
                    $row->qty_pr_awal,
                    $row->unit_pr_awal,
                    $row->convert_val,
                    $row->qty,
                    $row->unit,
                    $row->price,
                    $row->n_kurs,
                    $row->qty * $row->price,
                    $row->notes
                ];
            }

            public function styles(Worksheet $sheet)
            {
                $sheet->mergeCells('A1:P1');
                $sheet->mergeCells('A2:P2');
                $sheet->mergeCells('A4:P4');
                $sheet->mergeCells('A9:P9');

                $sheet->mergeCells('B7:P7');

                $cellsToBold = ['A5', 'D5', 'G5', 'J5', 'A6', 'D6', 'G6', 'J6', 'A7'];
                foreach ($cellsToBold as $cell) {
                    $sheet->getStyle($cell)->getFont()->setBold(true);
                }

                return [
                    1 => [
                        'font' => ['bold' => true, 'size' => 14],
                        'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
                    ],
                    2 => [
                        'font' => ['bold' => true, 'size' => 12],
                        'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
                    ],
                    4 => ['font' => ['bold' => true, 'size' => 12]],
                    9 => ['font' => ['bold' => true, 'size' => 12]],
                    10 => ['font' => ['bold' => true]],
                ];
            }
        }, $file_name);
    }

    public function cancel(Request $request, $id)
    {
        try {
            DB::connection('mysql_sb')->table('po_header')->where('id', $id)->update([
                'app' => 'C'
            ]);
            return response()->json(['status' => 200, 'message' => 'Data PO Berhasil Di-Cancel!']);
        } catch (\Exception $e) {
            return response()->json(['status' => 500, 'message' => 'Gagal Cancel PO: ' . $e->getMessage()], 500);
        }
    }

    public function restore(Request $request, $id)
    {
        try {
            DB::connection('mysql_sb')->table('po_header')->where('id', $id)->update([
                'app' => 'W',
            ]);
            return response()->json(['status' => 200, 'message' => 'Data PO Berhasil Di-Restore menjadi Draft!']);
        } catch (\Exception $e) {
            return response()->json(['status' => 500, 'message' => 'Gagal Restore PO: ' . $e->getMessage()], 500);
        }
    }
}
