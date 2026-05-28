<?php
namespace App\Http\Controllers\Purchasing;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class BookingStockController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $tgl_awal  = $request->tgl_awal;
            $tgl_akhir = $request->tgl_akhir;
            $status    = $request->status;
            $jenis     = $request->jenis;
            $search    = $request->search_text;
            $query = DB::connection('mysql_sb')->table('booking_stock_detail as bsd')
                ->join('booking_stock as bs', 'bsd.id_booking', '=', 'bs.id')
                ->select(
                    'bsd.id as id_detail',
                    'bs.id as id_booking',
                    'bs.no_booking',
                    'bs.tanggal_booking',
                    'bs.jenis',
                    'bs.status',
                    'bs.created_by',
                    'bsd.nama_barang',
                    'bsd.satuan',
                    'bsd.qty',
                    'bsd.ws_asal',
                    'bsd.ws_tujuan',
                    'bs.keterangan',
                );

            if ($tgl_awal && $tgl_akhir) {
                $query->whereBetween('bs.tanggal_booking', [$tgl_awal, $tgl_akhir]);
            }

            if ($status) {
                $query->where('bs.status', ucfirst($status));
            }
            if ($jenis) {
                $query->where('bs.jenis', $jenis);
            }

            if ($search) {
                $query->where(function($q) use ($search) {
                    $q->where('bs.no_booking', 'like', '%' . $search . '%')
                      ->orWhere('bsd.nama_barang', 'like', '%' . $search . '%')
                      ->orWhere('bs.created_by', 'like', '%' . $search . '%');
                });
            }

            $query->orderBy('bs.id', 'desc');


            return DataTables::of($query)
                ->addIndexColumn()
                ->addColumn('tgl_booking', function ($row) {
                    return date('d-m-Y', strtotime($row->tanggal_booking));
                })
                ->addColumn('qty_tampil', function ($row) {
                    $qty = floatval($row->qty);
                    $satuan = $row->satuan ?? '';
                    return number_format($qty, 2) . ' ' . $satuan;
                })
                ->addColumn('ws_asal', function ($row) {
                    return $row->ws_asal;
                })
                ->addColumn('ws_tujuan', function ($row) {
                    return $row->ws_tujuan;
                })
                ->addColumn('action', function ($row) {
                    $btnDelete = '<button type="button" class="btn btn-sm btn-danger" onclick="deleteBooking('.$row->id_detail.')" title="Hapus Item"><i class="fas fa-trash"></i></button>';
                    if ($row->status == 'Approved') {
                         return '<div class="d-flex justify-content-center">-</div>';
                    }

                    return '<div class="d-flex justify-content-center">' . $btnDelete . '</div>';
                })
                ->rawColumns(['status', 'action'])
                ->make(true);
        }

        return view('purchasing.booking_stock.index', [
            'page' => 'dashboard-purchasing',
            'subPageGroup' => 'purchasing',
            'subPage' => 'booking-stock',
            'containerFluid' => true,
        ]);
    }

    public function create(Request $request)
    {
        $bulan = date('m');
        $tahun = date('y');
        $prefix = "BKM/$bulan$tahun/";

        $lastBooking = DB::connection('mysql_sb')->table('booking_stock')
            ->where('no_booking', 'like', $prefix . '%')
            ->orderBy('no_booking', 'desc')
            ->first();

        if ($lastBooking) {
            $lastNum = (int) substr($lastBooking->no_booking, -5);
            $nextNum = str_pad($lastNum + 1, 5, '0', STR_PAD_LEFT);
        } else {
            $nextNum = "00001";
        }
        $no_booking = $prefix . $nextNum;

        $units = DB::connection('mysql_sb')->table('masterpilihan')
           ->where('kode_pilihan', 'Satuan')
           ->get();

        $ws_list = DB::connection('mysql_sb')->table('act_costing')->select('id', 'kpno')->groupBy('kpno')->get();

        return view('purchasing.booking_stock.create', [
            'page' => 'dashboard-purchasing',
            'subPageGroup' => 'purchasing',
            'subPage' => 'booking-stock',
            'no_booking' => $no_booking,
            'units' => $units,
            'containerFluid' => true,
            'ws_list' => $ws_list,
            // 'ws_asal' => $ws_asal
        ]);
    }

    public function getWsAsal() {
        $ws_asal = DB::connection('mysql_sb')->table('data_stock_fabric')->groupBy('id_jo')->get();
        return response()->json($ws_asal);
    }

    public function store(Request $request)
    {
        DB::connection('mysql_sb')->beginTransaction();
        try {
            $id_booking = DB::connection('mysql_sb')->table('booking_stock')->insertGetId([
                'no_booking' => $request->no_booking,
                'tanggal_booking' => $request->tgl_booking,
                'jenis' => $request->jenis,
                'keterangan' => $request->keterangan,
                'status' => 'Draft',
                'created_by' => auth()->user()->name,
                'created_at' => now(),
            ]);

            foreach ($request->id_item as $key => $val) {
                DB::connection('mysql_sb')->table('booking_stock_detail')->insert([
                    'id_booking' => $id_booking,
                    'id_item' => $request->id_item[$key],
                    'nama_barang' => $request->nama_barang[$key],
                    'qty' => $request->qty_det[$key],
                    'satuan' => $request->satuan_det[$key],
                    'ws_asal' => $request->ws_asal_det[$key],
                    'ws_tujuan' => $request->ws_tujuan_det[$key],
                    'created_at' => now(),
                ]);
            }

            DB::connection('mysql_sb')->commit();
            return response()->json(['status' => 200, 'message' => 'Booking Berhasil Disimpan!']);
        } catch (\Exception $e) {
            DB::connection('mysql_sb')->rollBack();
            return response()->json(['status' => 500, 'message' => $e->getMessage()]);
        }
    }

    public function getItems($jenis)
    {
        $keyword = ($jenis == 'Accessories') ? 'ACCESORIES' : $jenis;

        $items = DB::connection('mysql_sb')
            ->table('data_stock_fabric')
            ->select(
                'id_item as id',
                'itemdesc',
                'satuan',
                'kpno',
                DB::raw('SUM(sal_awal) as total_qty')
            )
            ->where('itemdesc', 'LIKE', '%' . $keyword . '%')
            ->groupBy('id_item', 'itemdesc', 'satuan')
            ->get();

        return response()->json($items);
    }

    public function delete($id)
    {
        try {
            DB::connection('mysql_sb')->table('booking_stock_detail')->where('id', $id)->delete();

            return response()->json([
                'status' => 200,
                'message' => 'Item booking berhasil dihapus!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 500,
                'message' => 'Gagal menghapus data: ' . $e->getMessage()
            ]);
        }
    }

    public function exportExcel(Request $request)
    {
        $tgl_awal  = $request->tgl_awal;
        $tgl_akhir = $request->tgl_akhir;
        $status    = $request->status;
        $jenis     = $request->jenis;
        $search    = $request->search_text;

        $query = DB::connection('mysql_sb')->table('booking_stock_detail as bsd')
            ->join('booking_stock as bs', 'bsd.id_booking', '=', 'bs.id')
            ->select(
                'bs.no_booking',
                'bs.tanggal_booking',
                'bs.jenis',
                'bs.status',
                'bs.created_by',
                'bs.keterangan',
                'bsd.nama_barang',
                'bsd.satuan',
                'bsd.qty',
                'bsd.ws_asal',
                'bsd.ws_tujuan'
            );

        if ($tgl_awal && $tgl_akhir) {
            $query->whereBetween('bs.tanggal_booking', [$tgl_awal, $tgl_akhir]);
        }
        if ($status) {
            $query->where('bs.status', ucfirst($status));
        }
        if ($jenis) {
            $query->where('bs.jenis', $jenis);
        }
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('bs.no_booking', 'like', '%' . $search . '%')
                  ->orWhere('bsd.nama_barang', 'like', '%' . $search . '%')
                  ->orWhere('bs.created_by', 'like', '%' . $search . '%');
            });
        }

        $data = $query->orderBy('bs.id', 'desc')->get();

        $export = new class($data, $tgl_awal, $tgl_akhir) implements FromView, ShouldAutoSize {
            protected $data, $tgl_awal, $tgl_akhir;

            public function __construct($data, $tgl_awal, $tgl_akhir) {
                $this->data = $data;
                $this->tgl_awal = $tgl_awal;
                $this->tgl_akhir = $tgl_akhir;
            }

            public function view(): View {
                return view('purchasing.booking_stock.export', [
                    'data' => $this->data,
                    'tgl_awal' => $this->tgl_awal,
                    'tgl_akhir' => $this->tgl_akhir
                ]);
            }
        };

        $nama_file = 'Laporan_Booking_Stock_' . date('Ymd_His') . '.xlsx';
        return Excel::download($export, $nama_file);
    }
}
