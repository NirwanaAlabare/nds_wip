<?php

namespace App\Http\Controllers\General;

use App\Http\Controllers\Controller;
use App\Services\ErdService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

// ERD Designer: satu diagram per koneksi (dipilih dari config/database.php). Logika ada di ErdService.
class ErdController extends Controller
{
    protected $erd;

    public function __construct(ErdService $erd)
    {
        $this->erd = $erd;
    }

    public function index()
    {
        return view('general.tools.erd');
    }

    public function list()
    {
        return response()->json([
            'diagrams'    => DB::table('erd_diagrams')->orderBy('name')->get(['id', 'name', 'connection', 'updated_by', 'updated_at']),
            'connections' => $this->erd->connections(),
        ]);
    }

    public function create(Request $request)
    {
        $request->validate([
            'connection' => 'required|string',
            'name'       => 'required|string|max:150',
        ]);

        $id = $this->erd->createDiagram($request->connection, $request->name, auth()->user());

        return response()->json([
            'status'  => 200,
            'message' => 'Diagram berhasil dibuat',
            'id'      => $id,
        ]);
    }

    public function delete(Request $request)
    {
        $this->erd->deleteDiagram($request->id, auth()->user());

        return response()->json([
            'status'  => 200,
            'message' => 'Diagram berhasil dihapus',
        ]);
    }

    // rev & after dikirim browser supaya polling hanya menerima data yang berubah
    public function state(Request $request, $id)
    {
        return response()->json($this->erd->state($id, auth()->user(), null, $request->query('rev'), $request->query('after', 0)));
    }

    public function op(Request $request, $id)
    {
        return response()->json($this->erd->op($id, auth()->user(), $request->json()->all(), $request->query('after', 0)));
    }

    public function undo(Request $request, $id)
    {
        return response()->json($this->erd->undo($id, auth()->user(), $request->activity_id, $request->query('after', 0)));
    }

    public function preview(Request $request, $id)
    {
        return response()->json($this->erd->preview($id, $request->tables));
    }

    public function apply(Request $request, $id)
    {
        return response()->json($this->erd->apply($id, auth()->user(), $request->tables, $request->query('after', 0)));
    }
}
