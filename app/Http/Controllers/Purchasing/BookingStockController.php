<?php
namespace App\Http\Controllers\Purchasing;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

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
                    'bsd.ws',
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
            'ws_list' => $ws_list
        ]);
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
                    'ws' => $request->ws_det[$key],
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
            ->table('masteritem')
            ->select('id_item as id', 'itemdesc as itemdesc')
            ->where('matclass', 'LIKE', '%' . $keyword . '%')
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
}
