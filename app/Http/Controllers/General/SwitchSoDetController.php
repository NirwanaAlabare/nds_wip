<?php

namespace App\Http\Controllers\General;

use App\Http\Controllers\Controller;
use App\Services\SwitchSoDetService;
use App\Services\SwitchSoDetUndoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class SwitchSoDetController extends Controller
{
    public function index()
    {
        return view('general.tools.switch-so-det');
    }

    /**
     * Select2 ajax: cari so_det berdasarkan WS / style / color / size / id so_det
     */
    public function getSoDet(Request $request)
    {
        $search = trim($request->q ?? '');

        $query = DB::table('master_sb_ws')
            ->select('id_so_det', 'ws', 'styleno', 'color', 'size', 'dest', 'buyer')
            ->orderBy('id_act_cost', 'desc')
            ->limit(50);

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('ws', 'like', '%' . $search . '%')
                    ->orWhere('styleno', 'like', '%' . $search . '%')
                    ->orWhere('id_so_det', $search);
            });
        } else {
            $query->whereRaw('1 = 0');
        }

        if ($request->color) {
            $query->where('color', 'like', '%' . $request->color . '%');
        }

        return response()->json([
            'results' => $query->get()->map(function ($row) {
                return [
                    'id' => $row->id_so_det,
                    'text' => $row->id_so_det . ' | ' . $row->ws . ' | ' . $row->styleno . ' | ' . $row->color . ' | ' . $row->size . ' | ' . $row->dest,
                ];
            }),
        ]);
    }

    public function preview(Request $request, SwitchSoDetService $service)
    {
        $request->validate([
            'so_det_asal' => 'required|numeric',
            'so_det_tujuan' => 'required|numeric',
        ]);

        return response()->json($service->preview($request->so_det_asal, $request->so_det_tujuan));
    }

    public function store(Request $request, SwitchSoDetService $service)
    {
        $request->validate([
            'so_det_asal' => 'required|numeric',
            'so_det_tujuan' => 'required|numeric',
            'notes' => 'nullable|string|max:1000',
        ]);

        ini_set('max_execution_time', 3600);

        $result = $service->execute($request->so_det_asal, $request->so_det_tujuan, $request->notes);

        return response()->json($result, $result['status'] == 200 ? 200 : ($result['status'] == 400 ? 422 : 500));
    }

    public function undoPreview(Request $request, SwitchSoDetUndoService $service)
    {
        $request->validate(['id' => 'required|numeric']);

        return response()->json($service->preview($request->id));
    }

    public function undo(Request $request, SwitchSoDetUndoService $service)
    {
        $request->validate([
            'id' => 'required|numeric',
            'notes' => 'nullable|string|max:1000',
        ]);

        ini_set('max_execution_time', 3600);

        $result = $service->execute($request->id, $request->notes);

        return response()->json($result, $result['status'] == 200 ? 200 : ($result['status'] == 400 ? 422 : 500));
    }

    public function getData(Request $request)
    {
        $query = DB::table('switch_so_det_logs')->select(
            'id',
            'so_det_id_asal',
            'ws_asal',
            'color_asal',
            'size_asal',
            'so_det_id_tujuan',
            'ws_tujuan',
            'color_tujuan',
            'size_tujuan',
            'status',
            'summary',
            'notes',
            'error',
            'created_by_username',
            'created_at',
            'undone_at',
            'undone_by_username',
            'undo_notes'
        )->orderBy('id', 'desc');

        return DataTables::query($query)->toJson();
    }
}
