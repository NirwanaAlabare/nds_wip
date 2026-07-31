<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use Yajra\DataTables\Facades\DataTables;
use DB;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;

class BAPFormController extends Controller
{
    public function getDepartments()
    {
        $departments = DB::connection('mysql_hris')->select("SELECT sub_dept_id, sub_dept_name FROM department_all
        WHERE site_nirwana_id = 'NAG'
        AND status = 'AKTIF'
        GROUP BY sub_dept_id, sub_dept_name
        ORDER BY sub_dept_name ASC");

        return response()->json($departments);
    }

    public function getMyDepartments()
    {
        $subDeptIds = collect(DB::select("SELECT sub_dept_id FROM user_department WHERE user_id = ?", [Auth::id()]))
            ->pluck('sub_dept_id')
            ->filter()
            ->unique()
            ->values();

        if ($subDeptIds->isEmpty()) {
            return response()->json([]);
        }

        $placeholders = implode(',', array_fill(0, $subDeptIds->count(), '?'));

        $departments = DB::connection('mysql_hris')->select(
            "SELECT sub_dept_id, sub_dept_name FROM department_all
            WHERE site_nirwana_id = 'NAG'
            AND status = 'AKTIF'
            AND sub_dept_id IN ($placeholders)
            GROUP BY sub_dept_id, sub_dept_name
            ORDER BY sub_dept_name ASC",
            $subDeptIds->all()
        );

        return response()->json($departments);
    }

    private function getAllowedDepartmentNames()
    {
        $subDeptIds = collect(DB::select("SELECT sub_dept_id FROM user_department WHERE user_id = ?", [Auth::id()]))
            ->pluck('sub_dept_id')
            ->filter()
            ->unique()
            ->values();

        if ($subDeptIds->isEmpty()) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, $subDeptIds->count(), '?'));

        $names = DB::connection('mysql_hris')->select(
            "SELECT sub_dept_name FROM department_all WHERE sub_dept_id IN ($placeholders)",
            $subDeptIds->all()
        );

        return collect($names)->pluck('sub_dept_name')->unique()->values()->all();
    }

    public function summary(Request $request)
    {
        $tgl_awal = $request->tgl_awal;
        $tgl_akhir = $request->tgl_akhir;

        $query = DB::table('bap_form')
            ->whereIn('department', $this->getAllowedDepartmentNames());

        if (!empty($tgl_awal) && !empty($tgl_akhir)) {
            $query->whereDate('tgl_form', '>=', $tgl_awal)
                ->whereDate('tgl_form', '<=', $tgl_akhir);
        }

        $total = (clone $query)->count();
        $selesai = (clone $query)->where('is_cancel', false)->where('is_selesai', true)->count();
        $cancel = (clone $query)->where('is_cancel', true)->count();
        $proses = (clone $query)->where('is_cancel', false)->where('is_selesai', false)->count();

        return response()->json([
            'total' => $total,
            'proses' => $proses,
            'selesai' => $selesai,
            'cancel' => $cancel,
        ]);
    }

    public function form_bap(Request $request)
    {
        if ($request->ajax()) {
            $tgl_awal = $request->tgl_awal;
            $tgl_akhir = $request->tgl_akhir;
            $status = $request->status;

            $data = DB::table('bap_form')
                ->select('id', 'no_form', 'tgl_form', 'department', 'modul', 'no_dokumen', 'tgl_masalah', 'masalah', 'penyebab', 'usulan', 'keterangan', 'is_selesai', 'is_cancel')
                ->whereIn('department', $this->getAllowedDepartmentNames())
                ->orderBy('tgl_form', 'desc');

            if (!empty($tgl_awal) && !empty($tgl_akhir)) {
                $data->whereDate('tgl_form', '>=', $tgl_awal)
                    ->whereDate('tgl_form', '<=', $tgl_akhir);
            }

            if ($status === 'proses') {
                $data->where('is_cancel', false)->where('is_selesai', false);
            } elseif ($status === 'selesai') {
                $data->where('is_cancel', false)->where('is_selesai', true);
            } elseif ($status === 'cancel') {
                $data->where('is_cancel', true);
            }

            return DataTables::of($data)
                ->addColumn('tgl_form', function ($row) {
                    return $row->tgl_form ? date('d-m-Y', strtotime($row->tgl_form)) : '-';
                })
                ->addColumn('tgl_masalah', function ($row) {
                    return $row->tgl_masalah ? date('d-m-Y', strtotime($row->tgl_masalah)) : '-';
                })
                ->addColumn('status', function ($row) {
                    if ($row->is_cancel) {
                        return '<span class="badge badge-danger">Cancel</span>';
                    }

                    return $row->is_selesai
                        ? '<span class="badge badge-success">Selesai</span>'
                        : '<span class="badge badge-secondary">Proses</span>';
                })
                ->rawColumns(['status'])
                ->make(true);
        }

        return view('helpdesk.form_bap', [
            'page' => 'dashboard-helpdesk',
            'subPage' => 'form-bap',
            'subPageGroup' => 'bap-form',
            'containerFluid' => true,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'department' => 'required|string|max:100',
            'sub_dept_id' => 'nullable|string|max:30',
            'modul' => 'nullable|string|max:100',
            'no_dokumen' => 'nullable|string|max:100',
            'tgl_masalah' => 'nullable|date',
            'masalah' => 'nullable|string',
            'penyebab' => 'nullable|string',
            'usulan' => 'nullable|string',
            'keterangan' => 'nullable|string',
        ]);

        $tglForm = Carbon::now();
        $prefix = 'BAP-' . $tglForm->format('dmy') . '-';

        $noForm = DB::transaction(function () use ($prefix) {
            $lastNoForm = DB::table('bap_form')
                ->where('no_form', 'like', $prefix . '%')
                ->lockForUpdate()
                ->max('no_form');

            $lastSequence = $lastNoForm ? (int) substr($lastNoForm, strlen($prefix)) : 0;

            return $prefix . str_pad($lastSequence + 1, 2, '0', STR_PAD_LEFT);
        });

        DB::table('bap_form')->insert([
            'no_form' => $noForm,
            'tgl_form' => $tglForm,
            'department' => $request->department,
            'sub_dept_id' => $request->sub_dept_id,
            'modul' => $request->modul,
            'no_dokumen' => $request->no_dokumen,
            'tgl_masalah' => $request->tgl_masalah,
            'masalah' => $request->masalah,
            'penyebab' => $request->penyebab,
            'usulan' => $request->usulan,
            'keterangan' => $request->keterangan,
            'is_selesai' => false,
            'created_by' => Auth::user()->name,
            'created_at' => $tglForm,
            'updated_at' => $tglForm,
        ]);

        return response()->json([
            'message' => 'Form BAP berhasil disimpan',
            'no_form' => $noForm,
        ]);
    }

    public function toggleStatus(Request $request)
    {
        $request->validate([
            'id' => 'required|integer',
            'current_status' => 'required|boolean',
        ]);

        DB::table('bap_form')
            ->where('id', $request->id)
            ->update([
                'is_selesai' => !$request->current_status,
                'updated_at' => Carbon::now(),
            ]);

        return response()->json([
            'message' => $request->current_status ? 'Kasus ditandai belum selesai' : 'Kasus ditandai selesai',
        ]);
    }

    public function edit(Request $request)
    {
        $request->validate([
            'id' => 'required|integer',
        ]);

        $row = DB::table('bap_form')->where('id', $request->id)->first();

        if (!$row) {
            return response()->json(['message' => 'Data tidak ditemukan'], 404);
        }

        return response()->json($row);
    }

    public function update(Request $request)
    {
        $request->validate([
            'id' => 'required|integer',
            'department' => 'required|string|max:100',
            'sub_dept_id' => 'nullable|string|max:30',
            'modul' => 'nullable|string|max:100',
            'no_dokumen' => 'nullable|string|max:100',
            'tgl_masalah' => 'nullable|date',
            'masalah' => 'nullable|string',
            'penyebab' => 'nullable|string',
            'usulan' => 'nullable|string',
            'keterangan' => 'nullable|string',
        ]);

        DB::table('bap_form')
            ->where('id', $request->id)
            ->update([
                'department' => $request->department,
                'sub_dept_id' => $request->sub_dept_id,
                'modul' => $request->modul,
                'no_dokumen' => $request->no_dokumen,
                'tgl_masalah' => $request->tgl_masalah,
                'masalah' => $request->masalah,
                'penyebab' => $request->penyebab,
                'usulan' => $request->usulan,
                'keterangan' => $request->keterangan,
                'updated_at' => Carbon::now(),
            ]);

        return response()->json([
            'message' => 'Form BAP berhasil diupdate',
        ]);
    }

    public function printPdf($id)
    {
        $row = DB::table('bap_form')->where('id', $id)->first();

        if (!$row) {
            abort(404, 'Data tidak ditemukan');
        }

        $pdf = Pdf::loadView('helpdesk.form_bap_pdf', [
            'bap' => $row,
        ])->setPaper('a4', 'portrait');

        return $pdf->stream($row->no_form . '.pdf');
    }

    public function cancel(Request $request)
    {
        $request->validate([
            'id' => 'required|integer',
        ]);

        DB::table('bap_form')
            ->where('id', $request->id)
            ->update([
                'is_cancel' => true,
                'updated_at' => Carbon::now(),
            ]);

        return response()->json([
            'message' => 'Form BAP dibatalkan',
        ]);
    }

    public function listUsersDepartment()
    {
        $data = DB::table('users as u')
            ->leftJoin('user_department as ud', 'ud.user_id', '=', 'u.id')
            ->select('u.id', 'u.name', 'u.username', DB::raw('COUNT(ud.id) as dept_count'))
            ->groupBy('u.id', 'u.name', 'u.username')
            ->orderBy('u.name');

        return DataTables::of($data)->make(true);
    }

    public function getUserDepartments(Request $request)
    {
        $request->validate([
            'user_id' => 'required|integer',
        ]);

        $rows = DB::select("SELECT id, sub_dept_id FROM user_department WHERE user_id = ? ORDER BY id DESC", [$request->user_id]);

        $subDeptIds = collect($rows)->pluck('sub_dept_id')->filter()->unique()->values();

        $names = collect();
        if ($subDeptIds->isNotEmpty()) {
            $placeholders = implode(',', array_fill(0, $subDeptIds->count(), '?'));

            $departmentRows = DB::connection('mysql_hris')->select(
                "SELECT sub_dept_id, sub_dept_name FROM department_all WHERE sub_dept_id IN ($placeholders) and status = 'aktif' and site_nirwana_id = 'NAG'",
                $subDeptIds->all()
            );

            $names = collect($departmentRows)->pluck('sub_dept_name', 'sub_dept_id');
        }

        $result = collect($rows)->map(function ($row) use ($names) {
            return [
                'id' => $row->id,
                'sub_dept_id' => $row->sub_dept_id,
                'sub_dept_name' => $names->get($row->sub_dept_id, $row->sub_dept_id),
            ];
        });

        return response()->json($result);
    }

    public function syncUserDepartment(Request $request)
    {
        $request->validate([
            'user_id' => 'required|integer|exists:users,id',
            'sub_dept_id' => 'array',
            'sub_dept_id.*' => 'string|max:30',
        ]);

        $selected = collect($request->sub_dept_id ?? [])->filter()->unique()->values();

        $existingRows = DB::select("SELECT sub_dept_id FROM user_department WHERE user_id = ?", [$request->user_id]);
        $existing = collect($existingRows)->pluck('sub_dept_id');

        $toInsert = $selected->diff($existing);
        $toDelete = $existing->diff($selected);

        if ($toInsert->isNotEmpty()) {
            $now = Carbon::now();
            $insertRows = $toInsert->map(function ($subDeptId) use ($request, $now) {
                return [
                    'user_id' => $request->user_id,
                    'sub_dept_id' => $subDeptId,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            })->all();

            DB::table('user_department')->insert($insertRows);
        }

        if ($toDelete->isNotEmpty()) {
            $placeholders = implode(',', array_fill(0, $toDelete->count(), '?'));

            DB::delete(
                "DELETE FROM user_department WHERE user_id = ? AND sub_dept_id IN ($placeholders)",
                array_merge([$request->user_id], $toDelete->all())
            );
        }

        return response()->json([
            'message' => 'Department berhasil disimpan',
        ]);
    }
}
