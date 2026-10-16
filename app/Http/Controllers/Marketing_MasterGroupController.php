<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Yajra\DataTables\Facades\DataTables;

class Marketing_MasterGroupController extends Controller
{
    public const MATTYPES = ['A' => 'Accessories', 'F' => 'Fabric'];

    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = $this->db()->table('mastergroup')
                ->where('cancel', 'N')
                ->select('id', 'kode_group', 'nama_group', 'mattype')
                ->orderBy('id');

            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('mattype_label', fn ($row) => self::MATTYPES[trim((string) $row->mattype)] ?? null)
                ->make(true);
        }

        return view('marketing.master.group', [
            'page' => 'dashboard-marketing',
            'subPageGroup' => 'marketing-master-data',
            'subPage' => 'marketing-master-group',
            'containerFluid' => true,
            'mattypes' => self::MATTYPES,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $this->validateGroup($request);
        $this->db()->table('mastergroup')->insert($validated + [
            'aktif' => 'Y',
            'cancel' => 'N',
            'created_by' => $this->userName(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json(['status' => 200, 'message' => 'Group berhasil disimpan.']);
    }

    public function update(Request $request, int $id)
    {
        $this->findActive($id);
        $validated = $this->validateGroup($request, $id);
        $this->db()->table('mastergroup')->where('id', $id)->update($validated + ['updated_at' => now()]);

        return response()->json(['status' => 200, 'message' => 'Group berhasil diubah.']);
    }

    public function destroy(int $id)
    {
        $group = $this->findActive($id);
        $used = $this->db()->table('mastersubgroup')->where('id_group', $id)->where('aktif', 'Y')->exists()
            || $this->db()->table('mastercontents')->where('id_group', $id)->where('cancel', 'N')->where('aktif', 'Y')->exists();
        if ($used) {
            return response()->json(['status' => 422, 'message' => "Group {$group->nama_group} masih dipakai sub group / content, tidak bisa dihapus."], 422);
        }
        $this->db()->table('mastergroup')->where('id', $id)->update(['cancel' => 'Y', 'updated_at' => now()]);

        return response()->json(['status' => 200, 'message' => 'Group berhasil dihapus.']);
    }

    private function validateGroup(Request $request, ?int $id = null): array
    {
        $request->merge([
            'kode_group' => strtoupper(trim((string) $request->kode_group)),
            'nama_group' => strtoupper(trim((string) $request->nama_group)),
        ]);

        return $request->validate([
            'kode_group' => ['required', 'string', 'max:10', Rule::unique('mysql_sb.mastergroup', 'kode_group')->ignore($id)],
            'nama_group' => ['required', 'string', 'max:255'],
            'mattype' => ['required', Rule::in(array_keys(self::MATTYPES))],
        ], [
            'kode_group.unique' => 'Group Code sudah dipakai.',
        ], [
            'kode_group' => 'Group Code',
            'nama_group' => 'Group Name',
            'mattype' => 'Matt Type',
        ]);
    }

    private function findActive(int $id)
    {
        $group = $this->db()->table('mastergroup')->where('id', $id)->where('cancel', 'N')->first();
        abort_if(!$group, 404, 'Group tidak ditemukan.');

        return $group;
    }

    private function userName(): ?string
    {
        $user = Auth::user();

        return $user ? mb_substr((string) ($user->name ?: $user->username), 0, 100) : null;
    }

    private function db()
    {
        return DB::connection('mysql_sb');
    }
}
