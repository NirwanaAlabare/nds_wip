<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use Yajra\DataTables\Facades\DataTables;
use DB;
use Illuminate\Support\Facades\Auth;

class MaintenanceACFormController extends Controller
{
    private const PRIVILEGED_USERNAMES = ['admin_01', 'nirwana_it'];

    private const TEKNISI_USERNAMES = ['admin_01', 'nirwana_it', 'roy'];

    private function isPrivileged()
    {
        return in_array(Auth::user()->username, self::PRIVILEGED_USERNAMES);
    }

    private function isTeknisi()
    {
        return in_array(Auth::user()->username, self::TEKNISI_USERNAMES);
    }

    private function isOwner($row)
    {
        return $row->created_by === Auth::user()->name;
    }

    private function formatDurasi($menit)
    {
        if ($menit < 60) {
            return $menit . ' menit';
        }

        if ($menit < 1440) {
            $jam = intdiv($menit, 60);
            $sisa = $menit % 60;

            return $sisa ? $jam . ' jam ' . $sisa . ' menit' : $jam . ' jam';
        }

        $hari = intdiv($menit, 1440);
        $jam = intdiv($menit % 1440, 60);

        return $jam ? $hari . ' hari ' . $jam . ' jam' : $hari . ' hari';
    }

    private function getDepartmentNames($subDeptIds)
    {
        $subDeptIds = collect($subDeptIds)->filter()->unique()->values();

        if ($subDeptIds->isEmpty()) {
            return collect();
        }

        $placeholders = implode(',', array_fill(0, $subDeptIds->count(), '?'));

        $rows = DB::connection('mysql_hris')->select(
            "SELECT sub_dept_id, sub_dept_name FROM department_all WHERE sub_dept_id IN ($placeholders)",
            $subDeptIds->all()
        );

        return collect($rows)->pluck('sub_dept_name', 'sub_dept_id');
    }

    public function getDepartments()
    {
        $departments = DB::connection('mysql_hris')->select("SELECT sub_dept_id, sub_dept_name FROM department_all
        WHERE site_nirwana_id = 'NAG'
        AND status = 'AKTIF'
        GROUP BY sub_dept_id, sub_dept_name
        ORDER BY sub_dept_name ASC");

        return response()->json($departments);
    }

    public function summary(Request $request)
    {
        $tgl_awal = $request->tgl_awal;
        $tgl_akhir = $request->tgl_akhir;

        $query = DB::table('maintenance_ac_form');

        if (!empty($tgl_awal) && !empty($tgl_akhir)) {
            $query->whereDate('tgl_form', '>=', $tgl_awal)
                ->whereDate('tgl_form', '<=', $tgl_akhir);
        }

        return response()->json([
            'total' => (clone $query)->count(),
            'draft' => (clone $query)->where('status', 'DRAFT')->count(),
            'on_progress' => (clone $query)->where('status', 'ON PROGRESS')->count(),
            'done' => (clone $query)->where('status', 'DONE')->count(),
            'cancel' => (clone $query)->where('status', 'CANCEL')->count(),
        ]);
    }

    public function form_maintenance_ac(Request $request)
    {
        if ($request->ajax()) {
            $tgl_awal = $request->tgl_awal;
            $tgl_akhir = $request->tgl_akhir;
            $status = $request->status;

            $data = DB::table('maintenance_ac_form')
                ->select('id', 'no_form', 'tgl_form', 'sub_dept_id', 'keterangan', 'usulan', 'penyelesaian', 'tgl_pengerjaan', 'tgl_selesai', 'status', 'created_by')
                ->orderByRaw("FIELD(status, 'ON PROGRESS', 'DRAFT', 'DONE', 'CANCEL')")
                ->orderBy('tgl_form', 'desc')
                ->orderBy('no_form', 'desc');

            if (!empty($tgl_awal) && !empty($tgl_akhir)) {
                $data->whereDate('tgl_form', '>=', $tgl_awal)
                    ->whereDate('tgl_form', '<=', $tgl_akhir);
            }

            if (!empty($status)) {
                $data->where('status', $status);
            }

            $rows = $data->get();
            $departmentNames = $this->getDepartmentNames($rows->pluck('sub_dept_id'));

            return DataTables::of($rows)
                ->addColumn('department', function ($row) use ($departmentNames) {
                    return $departmentNames->get($row->sub_dept_id, $row->sub_dept_id ?: '-');
                })
                ->addColumn('tgl_form', function ($row) {
                    return $row->tgl_form ? date('d-m-Y', strtotime($row->tgl_form)) : '-';
                })
                ->addColumn('durasi', function ($row) {
                    if (!$row->tgl_pengerjaan) {
                        return '-';
                    }

                    $mulai = strtotime($row->tgl_pengerjaan);
                    $selesai = $row->tgl_selesai ? strtotime($row->tgl_selesai) : time();
                    $menit = (int) round(max(0, $selesai - $mulai) / 60);

                    $durasi = $this->formatDurasi($menit);

                    return $row->tgl_selesai ? $durasi : $durasi . ' (berjalan)';
                })
                ->addColumn('status_badge', function ($row) {
                    $badge = [
                        'DRAFT' => 'badge-secondary',
                        'ON PROGRESS' => 'badge-warning',
                        'DONE' => 'badge-success',
                        'CANCEL' => 'badge-danger',
                    ];

                    $status = $row->status ?: 'DRAFT';

                    $html = '<span class="badge ' . ($badge[$status] ?? 'badge-secondary') . '">' . $status . '</span>';

                    if ($row->tgl_pengerjaan) {
                        $html .= '<br><small class="text-muted">Mulai: ' . date('d-m-Y H:i', strtotime($row->tgl_pengerjaan)) . '</small>';
                    }

                    if ($row->tgl_selesai) {
                        $html .= '<br><small class="text-muted">Selesai: ' . date('d-m-Y H:i', strtotime($row->tgl_selesai)) . '</small>';
                    }

                    return $html;
                })
                ->rawColumns(['status_badge'])
                ->make(true);
        }

        return view('helpdesk.form_maintenance_ac', [
            'page' => 'dashboard-mt-ac',
            'brandRoute' => 'dashboard-helpdesk',
            'subPage' => 'form-maintenance-ac',
            'subPageGroup' => 'mt-ac-form',
            'containerFluid' => true,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'sub_dept_id' => 'required|string|max:30',
            'tgl_form' => 'required|date|after_or_equal:today',
            'keterangan' => 'nullable|string',
        ], [
            'tgl_form.after_or_equal' => 'Tgl. Form tidak boleh mundur dari hari ini',
        ]);

        $now = Carbon::now();
        $tglForm = Carbon::parse($request->tgl_form)->startOfDay();
        $prefix = 'MAC-' . $tglForm->format('dmy') . '-';

        $noForm = DB::transaction(function () use ($prefix) {
            $lastNoForm = DB::table('maintenance_ac_form')
                ->where('no_form', 'like', $prefix . '%')
                ->lockForUpdate()
                ->max('no_form');

            $lastSequence = $lastNoForm ? (int) substr($lastNoForm, strlen($prefix)) : 0;

            return $prefix . str_pad($lastSequence + 1, 2, '0', STR_PAD_LEFT);
        });

        DB::table('maintenance_ac_form')->insert([
            'no_form' => $noForm,
            'tgl_form' => $tglForm->toDateString(),
            'sub_dept_id' => $request->sub_dept_id,
            'keterangan' => $request->keterangan,
            'status' => 'DRAFT',
            'created_by' => Auth::user()->name,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        return response()->json([
            'message' => 'Form Maintenance AC berhasil disimpan',
            'no_form' => $noForm,
        ]);
    }

    public function edit(Request $request)
    {
        $request->validate([
            'id' => 'required|integer',
        ]);

        $row = DB::table('maintenance_ac_form')->where('id', $request->id)->first();

        if (!$row) {
            return response()->json(['message' => 'Data tidak ditemukan'], 404);
        }

        return response()->json($row);
    }

    public function update(Request $request)
    {
        $request->validate([
            'id' => 'required|integer',
            'sub_dept_id' => 'nullable|string|max:30',
            'tgl_form' => 'nullable|date',
            'keterangan' => 'nullable|string',
            'usulan' => 'nullable|string',
            'penyelesaian' => 'nullable|string',
        ]);

        $row = DB::table('maintenance_ac_form')->where('id', $request->id)->first();

        if (!$row) {
            return response()->json(['message' => 'Data tidak ditemukan'], 404);
        }

        if ($row->status === 'ON PROGRESS') {
            if (!$this->isTeknisi()) {
                return response()->json(['message' => 'Anda tidak memiliki akses untuk mengubah form yang sedang dikerjakan'], 403);
            }

            $update = [
                'usulan' => $request->usulan,
                'updated_at' => Carbon::now(),
            ];
        } elseif ($row->status === 'DONE') {
            if (!$this->isTeknisi()) {
                return response()->json(['message' => 'Anda tidak memiliki akses untuk mengubah form yang sudah selesai'], 403);
            }

            $update = [
                'penyelesaian' => $request->penyelesaian,
                'updated_at' => Carbon::now(),
            ];
        } elseif ($row->status === 'DRAFT') {
            if (!$this->isPrivileged() && !$this->isOwner($row)) {
                return response()->json(['message' => 'Anda tidak memiliki akses untuk mengubah form ini'], 403);
            }

            $request->validate([
                'tgl_form' => 'required|date|after_or_equal:today',
            ], [
                'tgl_form.after_or_equal' => 'Tgl. Form tidak boleh mundur dari hari ini',
            ]);

            $update = [
                'tgl_form' => Carbon::parse($request->tgl_form)->toDateString(),
                'keterangan' => $request->keterangan,
                'updated_at' => Carbon::now(),
            ];

            if ($this->isPrivileged() && $request->sub_dept_id) {
                $update['sub_dept_id'] = $request->sub_dept_id;
            }
        } else {
            return response()->json(['message' => 'Form dengan status ' . $row->status . ' tidak bisa diubah'], 403);
        }

        DB::table('maintenance_ac_form')
            ->where('id', $request->id)
            ->update($update);

        return response()->json([
            'message' => 'Form Maintenance AC berhasil diupdate',
        ]);
    }

    public function startProgress(Request $request)
    {
        $request->validate([
            'id' => 'required|integer',
        ]);

        if (!$this->isTeknisi()) {
            return response()->json(['message' => 'Anda tidak memiliki akses untuk memulai pengerjaan'], 403);
        }

        $row = DB::table('maintenance_ac_form')->where('id', $request->id)->first();

        if (!$row) {
            return response()->json(['message' => 'Data tidak ditemukan'], 404);
        }

        if ($row->status !== 'DRAFT') {
            return response()->json(['message' => 'Pengerjaan hanya bisa dimulai dari status DRAFT'], 422);
        }

        DB::table('maintenance_ac_form')
            ->where('id', $request->id)
            ->update([
                'tgl_pengerjaan' => Carbon::now(),
                'status' => 'ON PROGRESS',
                'updated_at' => Carbon::now(),
            ]);

        return response()->json([
            'message' => 'Pengerjaan dimulai',
        ]);
    }

    public function finishProgress(Request $request)
    {
        $request->validate([
            'id' => 'required|integer',
        ]);

        if (!$this->isTeknisi()) {
            return response()->json(['message' => 'Anda tidak memiliki akses untuk menyelesaikan pengerjaan'], 403);
        }

        $row = DB::table('maintenance_ac_form')->where('id', $request->id)->first();

        if (!$row) {
            return response()->json(['message' => 'Data tidak ditemukan'], 404);
        }

        if ($row->status !== 'ON PROGRESS') {
            return response()->json(['message' => 'Pengerjaan hanya bisa diselesaikan dari status ON PROGRESS'], 422);
        }

        DB::table('maintenance_ac_form')
            ->where('id', $request->id)
            ->update([
                'tgl_selesai' => Carbon::now(),
                'status' => 'DONE',
                'updated_at' => Carbon::now(),
            ]);

        return response()->json([
            'message' => 'Pengerjaan selesai',
        ]);
    }

    public function cancel(Request $request)
    {
        $request->validate([
            'id' => 'required|integer',
        ]);

        $row = DB::table('maintenance_ac_form')->where('id', $request->id)->first();

        if (!$row) {
            return response()->json(['message' => 'Data tidak ditemukan'], 404);
        }

        if (!$this->isPrivileged() && !$this->isOwner($row)) {
            return response()->json(['message' => 'Anda tidak memiliki akses untuk membatalkan form ini'], 403);
        }

        DB::table('maintenance_ac_form')
            ->where('id', $request->id)
            ->update([
                'status' => 'CANCEL',
                'updated_at' => Carbon::now(),
            ]);

        return response()->json([
            'message' => 'Form Maintenance AC dibatalkan',
        ]);
    }

    public function restoreCancel(Request $request)
    {
        $request->validate([
            'id' => 'required|integer',
        ]);

        $row = DB::table('maintenance_ac_form')->where('id', $request->id)->first();

        if (!$row) {
            return response()->json(['message' => 'Data tidak ditemukan'], 404);
        }

        if (!$this->isPrivileged() && !$this->isOwner($row)) {
            return response()->json(['message' => 'Anda tidak memiliki akses untuk mengembalikan status ini'], 403);
        }

        DB::table('maintenance_ac_form')
            ->where('id', $request->id)
            ->update([
                'status' => 'DRAFT',
                'updated_at' => Carbon::now(),
            ]);

        return response()->json([
            'message' => 'Status cancel berhasil dikembalikan',
        ]);
    }
}
