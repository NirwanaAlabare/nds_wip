<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

/**
 * Marketing > Master > Master Content & Item.
 * Content = tabel native mastercontents (id-nya dipakai Costing/BOM) dengan kolom tambahan id_group
 * (hubungan langsung ke mastergroup). Item baru disimpan di masteritem dengan id_contents;
 * item lama dari native (tanpa id_contents) tidak tampil di sini.
 */
class Marketing_MasterContentController extends Controller
{
    public function index()
    {
        return view('marketing.master.content', [
            'page' => 'dashboard-marketing',
            'subPageGroup' => 'marketing-master-data',
            'subPage' => 'marketing-master-content',
            'containerFluid' => true,
            'groups' => $this->db()->table('mastergroup')->where('cancel', 'N')->orderBy('id')->get(['id', 'kode_group', 'nama_group']),
            'units' => $this->db()->table('masterpilihan')->where('kode_pilihan', 'Satuan')->orderBy('nama_pilihan')->pluck('nama_pilihan'),
        ]);
    }

    /** Panel kiri: group aktif beserta content dan jumlah item aktifnya. */
    public function contents()
    {
        $itemCounts = $this->db()->table('masteritem')
            ->where('cancel', 'N')
            ->whereNotNull('id_contents')
            ->groupBy('id_contents')
            ->select('id_contents', DB::raw('COUNT(*) as total'));

        $contents = $this->activeContents()
            ->leftJoinSub($itemCounts, 'ic', 'ic.id_contents', '=', 'c.id')
            ->orderBy('g.id')
            ->orderBy('c.nama_contents')
            ->get([
                'c.id as id_contents', 'c.id_group', 'c.id_type', 'c.nama_contents as nm_contents', 'g.nama_group',
                DB::raw('COALESCE(ic.total, 0) as item_count'),
            ]);

        return response()->json([
            'groups' => $contents->groupBy('id_group')->map(fn ($rows) => [
                'id' => $rows->first()->id_group,
                'name' => $rows->first()->nama_group,
                'contents' => $rows->values(),
            ])->values(),
        ]);
    }

    public function storeContent(Request $request)
    {
        $validated = $this->validateContent($request);
        $this->db()->table('mastercontents')->insert([
            'id_group' => $validated['id_group'],
            'nama_contents' => $validated['nm_contents'],
            'aktif' => 'Y',
            'is_active' => 'Y',
        ] + $this->createdColumns());

        return response()->json(['status' => 200, 'message' => 'Content berhasil disimpan.']);
    }

    public function updateContent(Request $request, int $id)
    {
        $content = $this->findActiveContent($id);
        $validated = $this->validateContent($request, $id);
        // Content lama masih terhubung ke group lewat mastertype2 (dipakai Costing/BOM), jadi group-nya tidak boleh dipindah.
        if ($content->id_type && (int) $validated['id_group'] !== (int) $content->id_group) {
            return response()->json(['status' => 422, 'message' => 'Group untuk content lama tidak bisa diganti, hanya namanya.'], 422);
        }
        $this->db()->table('mastercontents')->where('id', $id)->update([
            'id_group' => $validated['id_group'],
            'nama_contents' => $validated['nm_contents'],
            'updated_at' => now(),
        ]);

        return response()->json(['status' => 200, 'message' => 'Content berhasil diubah.']);
    }

    public function destroyContent(int $id)
    {
        $content = $this->findActiveContent($id);
        $usage = [
            'item' => $this->db()->table('masteritem')->where('id_contents', $id)->where('cancel', 'N')->exists(),
            'Costing' => $this->db()->table('act_costing_mat')->where('id_item', $id)->exists(),
            // id_contents di BOM bisa berformat "kategori|id".
            'BOM' => $this->db()->table('bom_marketing_detail')->where('cancel', 'N')
                ->where(fn ($q) => $q->where('id_contents', (string) $id)->orWhere('id_contents', 'like', "%|{$id}"))
                ->exists(),
        ];
        if ($usage['item']) {
            return response()->json(['status' => 422, 'message' => "Content {$content->nama_contents} masih punya item. Hapus item-nya dulu."], 422);
        }
        if ($usedBy = array_keys(array_filter(array_slice($usage, 1)))) {
            return response()->json(['status' => 422, 'message' => "Content {$content->nama_contents} sudah dipakai di " . implode(' dan ', $usedBy) . ', tidak bisa dihapus.'], 422);
        }
        $this->db()->table('mastercontents')->where('id', $id)->update(['cancel' => 'Y', 'updated_at' => now()]);

        return response()->json(['status' => 200, 'message' => 'Content berhasil dihapus.']);
    }

    private const SEARCH_COLUMNS = [
        'group' => ['g.nama_group', 'g.kode_group'],
        'content' => ['c.nama_contents'],
        'item' => ['i.itemdesc', 'i.size', 'i.color'],
    ];

    /**
     * Tabel Content & Items: 1 baris per item yang punya nama (content tanpa item tidak tampil). Paging di server.
     */
    public function rows(Request $request)
    {
        $perPage = in_array((int) $request->per_page, [10, 25, 50, 100], true) ? (int) $request->per_page : 10;
        $query = $this->activeContents()
            ->join('masteritem as i', fn ($join) => $join->on('i.id_contents', '=', 'c.id')->where('i.cancel', 'N'))
            ->whereRaw("TRIM(COALESCE(i.itemdesc, '')) <> ''");

        $keyword = trim((string) $request->keyword);
        if ($keyword !== '') {
            // search_by[] = group/content/item yang dicentang; kosong = semua kolom.
            $fields = array_intersect((array) $request->input('search_by', []), array_keys(self::SEARCH_COLUMNS)) ?: array_keys(self::SEARCH_COLUMNS);
            $columns = array_merge(...array_values(array_intersect_key(self::SEARCH_COLUMNS, array_flip($fields))));
            $query->where(function ($q) use ($columns, $keyword) {
                foreach ($columns as $column) {
                    $q->orWhere($column, 'like', "%{$keyword}%");
                }
            });
        }

        $total = (clone $query)->count();
        $page = min(max(1, (int) $request->page), max(1, (int) ceil($total / $perPage)));
        $rows = $query->orderBy('g.id')->orderBy('c.nama_contents')->orderBy('i.id_item')
            ->forPage($page, $perPage)
            ->get([
                'c.id as id_contents', 'c.id_group', 'c.id_type', 'c.nama_contents as nm_contents', 'g.nama_group',
                'i.id_item', 'i.itemdesc', 'i.size', 'i.color', 'i.kd_satuan', 'i.width', 'i.length', 'i.gramasi',
            ]);

        return response()->json(['total' => $total, 'page' => $page, 'per_page' => $perPage, 'rows' => $rows]);
    }

    public function storeItem(Request $request)
    {
        $validated = $this->validateItem($request);
        $group = $this->db()->table('mastercontents as c')
            ->join('mastergroup as g', 'g.id', '=', 'c.id_group')
            ->where('c.id', $validated['id_contents'])
            ->first(['g.nama_group', 'g.mattype']);
        if (trim((string) $group->mattype) === '') {
            return response()->json(['status' => 422, 'message' => "Matt Type group {$group->nama_group} belum diisi. Isi dulu di Master Group."], 422);
        }
        $this->db()->table('masteritem')->insert($this->itemColumns($validated) + [
            'id_contents' => $validated['id_contents'],
            // mattype & matclass mengikuti group, sama seperti item native.
            'mattype' => trim((string) $group->mattype),
            'matclass' => $group->nama_group,
            'non_aktif' => 'N',
        ] + $this->createdColumns());

        return response()->json(['status' => 200, 'message' => 'Item berhasil disimpan.']);
    }

    public function updateItem(Request $request, int $id)
    {
        $this->findActiveItem($id);
        $validated = $this->validateItem($request, false);
        $this->db()->table('masteritem')->where('id_item', $id)->update($this->itemColumns($validated) + ['updated_at' => now()]);

        return response()->json(['status' => 200, 'message' => 'Item berhasil diubah.']);
    }

    public function destroyItem(int $id)
    {
        $item = $this->findActiveItem($id);
        if ($this->db()->table('bom_marketing_detail')->where('id_item', $id)->where('cancel', 'N')->exists()) {
            return response()->json(['status' => 422, 'message' => "Item {$item->itemdesc} sudah dipakai di BOM, tidak bisa dihapus."], 422);
        }
        $this->db()->table('masteritem')->where('id_item', $id)->update(['cancel' => 'Y', 'updated_at' => now()]);

        return response()->json(['status' => 200, 'message' => 'Item berhasil dihapus.']);
    }

    /** Content aktif yang sudah punya group langsung (id_group). */
    private function activeContents()
    {
        return $this->db()->table('mastercontents as c')
            ->join('mastergroup as g', 'g.id', '=', 'c.id_group')
            ->where('c.cancel', 'N')
            ->where('c.aktif', 'Y')
            ->where('g.cancel', 'N');
    }

    private function validateContent(Request $request, ?int $id = null): array
    {
        $request->merge(['nm_contents' => trim((string) $request->nm_contents)]);

        return $request->validate([
            'id_group' => ['required', 'integer', Rule::exists('mysql_sb.mastergroup', 'id')->where('cancel', 'N')],
            // Nama content tidak boleh dobel di group yang sama.
            'nm_contents' => ['required', 'string', 'max:250', Rule::unique('mysql_sb.mastercontents', 'nama_contents')
                ->where('id_group', $request->id_group)
                ->where('cancel', 'N')
                ->where('aktif', 'Y')
                ->ignore($id)],
        ], ['nm_contents.unique' => 'Nama Content sudah ada di group ini.'], [
            'id_group' => 'Nama Group',
            'nm_contents' => 'Nama Content',
        ]);
    }

    private function validateItem(Request $request, bool $withContent = true): array
    {
        $rules = [
            'itemdesc' => ['required', 'string', 'max:500'],
            'size' => ['nullable', 'string', 'max:50'],
            'color' => ['nullable', 'string', 'max:50'],
            'kd_satuan' => ['required', 'string', Rule::exists('mysql_sb.masterpilihan', 'nama_pilihan')->where('kode_pilihan', 'Satuan')],
            'width' => ['nullable', 'string', 'max:50'],
            'length' => ['nullable', 'string', 'max:50'],
            'gramasi' => ['nullable', 'numeric', 'min:0', 'max:99999999'],
        ];
        if ($withContent) {
            $rules['id_contents'] = ['required', 'integer', Rule::exists('mysql_sb.mastercontents', 'id')
                ->where('cancel', 'N')
                ->where('aktif', 'Y')
                ->whereNotNull('id_group')];
        }

        return $request->validate($rules, [], [
            'id_contents' => 'Content',
            'itemdesc' => 'Nama Item',
            'kd_satuan' => 'Unit',
            'gramasi' => 'Weight',
        ]);
    }

    private function itemColumns(array $validated): array
    {
        $text = fn ($key) => isset($validated[$key]) && trim((string) $validated[$key]) !== '' ? trim($validated[$key]) : null;

        return [
            'itemdesc' => trim($validated['itemdesc']),
            'size' => $text('size'),
            'color' => $text('color'),
            'kd_satuan' => $validated['kd_satuan'],
            'width' => $text('width'),
            'length' => $text('length'),
            'gramasi' => $text('gramasi'),
        ];
    }

    private function findActiveContent(int $id)
    {
        $content = $this->db()->table('mastercontents')->where('id', $id)->where('cancel', 'N')->first();
        abort_if(!$content, 404, 'Content tidak ditemukan.');

        return $content;
    }

    private function findActiveItem(int $id)
    {
        $item = $this->db()->table('masteritem')->where('id_item', $id)->where('cancel', 'N')->whereNotNull('id_contents')->first();
        abort_if(!$item, 404, 'Item tidak ditemukan.');

        return $item;
    }

    private function createdColumns(): array
    {
        $user = Auth::user();

        return [
            'cancel' => 'N',
            'created_by' => $user ? mb_substr((string) ($user->name ?: $user->username), 0, 100) : null,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    private function db()
    {
        return DB::connection('mysql_sb');
    }
}
