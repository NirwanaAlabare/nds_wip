<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use Yajra\DataTables\Facades\DataTables;
use DB;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ExportPPIC_Master_so_sb;
use App\Exports\ExportPPIC_Master_so_ppic;
use App\Imports\ImportPPIC_SO;

class PPIC_MasterSOController extends Controller
{
    public function index(Request $request)
    {
        $tgl_awal = $request->dateFrom;
        $tgl_akhir = $request->dateTo;
        $user = Auth::user()->name;

        if ($request->ajax()) {
            $data_input = DB::select("
            SELECT
            m.buyer,
            date_format(a.tgl_shipment,'%d-%m-%y') tgl_shipment_fix,
            a.barcode,
            m.reff_no,
            a.po,
            a.dest,
            m.color,
            m.size,
            a.qty_po,
            m.ws,
            a.created_by,
            a.created_at
            FROM ppic_master_so a
            inner join master_sb_ws m on a.id_so_det = m.id_so_det
            left join master_size_new msn on m.size = msn.size
            where tgl_shipment >= '$tgl_awal' and tgl_shipment <= '$tgl_akhir'
            order by tgl_shipment desc, buyer asc, ws asc , msn.urutan asc
            ");

            return DataTables::of($data_input)->toJson();
        }
        return view(
            'ppic.master_so',
            [
                'page' => 'dashboard-ppic', "subPageGroup" => "ppic-master",
                "subPage" => "ppic-master-master-so",
                "user" => $user
            ]
        );
    }


    public function show_tmp_ppic_so(Request $request)
    {
        $user = Auth::user()->name;
        if ($request->ajax()) {

            $data_tmp = DB::select("
            SELECT
            a.id_tmp,
            a.id_so_det,
            a.qty_po,
            m.product_group,
            m.product_item,
            m.ws,
            m.color,
            m.size,
            m.buyer,
            m.styleno,
            m.reff_no,
            m.brand,
            m.main_dest,
            a.dest,
            a.tgl_shipment,
            a.po,
            a.barcode,
            a.created_by,
            a.created_at
            from ppic_master_so_tmp a
            inner join master_sb_ws m on a.id_so_det = m.id_so_det
            where created_by = '$user'
            ");

            return DataTables::of($data_tmp)->toJson();
        }
    }

    public function import_excel_so(Request $request)
    {
        // validasi
        $this->validate($request, [
            'file' => 'required|mimes:csv,xls,xlsx'
        ]);

        $file = $request->file('file');

        $nama_file = rand() . $file->getClientOriginalName();

        $file->move('file_upload', $nama_file);

        Excel::import(new ImportPPIC_SO, public_path('/file_upload/' . $nama_file));

        return array(
            "status" => 200,
            "message" => 'Data Berhasil Di Upload',
            "additional" => [],
            // "redirect" => url('in-material/upload-lokasi')
        );
    }

    public function contoh_upload_ppic_so()
    {
        $path = public_path('storage/contoh-upload.xlsx');
        return response()->download($path);
    }

    public function undo_tmp_ppic_so(Request $request)
    {
        $user = Auth::user()->name;

        $undo =  DB::delete(
            "DELETE FROM ppic_master_so_tmp where created_by = '$user'"
        );

        if ($undo) {
            return array(
                'icon' => 'benar',
                'msg' => 'Data berhasil diundo',
            );
        } else {
            return array(
                'icon' => 'salah',
                'msg' => 'Tidak ada yang diundo',
            );
        }
    }

    public function store_tmp_ppic_so(Request $request)
    {
        $user = Auth::user()->name;
        $timestamp = Carbon::now();

        $cek = DB::select("select * from ppic_master_so_tmp where created_by = '$user'");

        $cekinput = $cek[0]->id_so_det;

        if ($cekinput == '') {
            return array(
                'icon' => 'salah',
                'msg' => 'Tidak ada yang disimpan',
            );
        } else {
            $insert = DB::insert(
                "insert into ppic_master_so
                (id_so_det,barcode,po,dest,tgl_shipment,qty_po,created_at,updated_at,created_by)
                SELECT id_so_det,barcode,po,dest,tgl_shipment,qty_po,'$timestamp','$timestamp','$user'
                from ppic_master_so_tmp
                where created_by = '$user'
                "
            );

            if ($insert) {
                $delete =  DB::delete(
                    "DELETE FROM ppic_master_so_tmp where created_by = '$user'"
                );
                return array(
                    'icon' => 'benar',
                    'msg' => 'Transaksi Sudah Terbuat',
                );
            }
        }
    }


    public function export_excel_master_sb_so(Request $request)
    {
        return Excel::download(new exportPPIC_Master_so_sb, 'Laporan_Master_SB_SO.xlsx');
    }

    public function export_excel_master_so_ppic(Request $request)
    {
        return Excel::download(new exportPPIC_Master_so_ppic, 'Laporan_Master_SB_SO.xlsx');
    }
}
