<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class Marketing_WorksheetController extends Controller
{
    public function create($costingId)
    {
        $db = $this->db();
        $costing = $db->table('act_costing as c')
            ->leftJoin('mastersupplier as ms', 'c.id_buyer', '=', 'ms.Id_Supplier')
            ->leftJoin('masterseason as seas', 'c.season_id', '=', 'seas.id_season')
            ->where('c.id', (int) $costingId)
            ->select('c.id', 'c.cost_no', 'c.kpno', 'c.deldate', 'c.styleno as style', 'c.brand', 'c.qty_planned',
                'ms.Supplier as buyer_name', 'ms.supplier_code as buyer_code', 'seas.season', 'seas.season_desc')
            ->first();
        abort_if(!$costing, 404, 'Costing tidak ditemukan.');

        $salesOrders = $this->salesOrders((int) $costingId);
        $bom = $this->activeBom((int) $costingId);

        return view('marketing.worksheet.create', [
            'page' => 'dashboard-marketing',
            'subPageGroup' => 'marketing-master',
            'subPage' => 'marketing-master-costing',
            'containerFluid' => true,
            'costing' => $costing,
            'soNumbers' => $salesOrders->pluck('so_no')->implode(', '),
            'bom' => $bom,
            'shellOptions' => $this->shellOptions($bom->id ?? null),
            'partOptions' => $this->partOptions(),
            'soDetails' => $this->soDetails($salesOrders->pluck('id'))->values(),
        ]);
    }

    public function rows($costingId)
    {
        return response()->json(['status' => 200, 'rows' => $this->worksheetRows((int) $costingId)]);
    }

    public function store(Request $request, $costingId)
    {
        $costingId = (int) $costingId;
        $validated = $request->validate([
            'id_panel' => ['required', 'integer'],
            'id_part' => ['required', 'integer'],
            'id_so_det' => ['required', 'array', 'min:1'],
            'id_so_det.*' => ['integer'],
            'qty' => ['nullable', 'numeric', 'min:0'],
        ]);

        if ($error = $this->validateRefs($costingId, (int) $validated['id_panel'], (int) $validated['id_part'])) {
            return response()->json(['status' => 422, 'message' => $error]);
        }

        $soDets = $this->soDetails($this->salesOrders($costingId)->pluck('id'))
            ->whereIn('id', array_map('intval', $validated['id_so_det']))
            ->keyBy('id');
        if ($soDets->count() !== count(array_unique($validated['id_so_det']))) {
            return response()->json(['status' => 422, 'message' => 'Color / size tidak ditemukan di Sales Order Costing ini.']);
        }

        $existing = $this->db()->table('worksheet_mkt')
            ->where('cancel', 'N')
            ->where('id_panel', $validated['id_panel'])->where('id_part', $validated['id_part'])
            ->whereIn('id_so_det', $soDets->keys())
            ->pluck('id_so_det')->map(fn ($id) => (int) $id)->all();
        $new = $soDets->except($existing);
        if ($new->isEmpty()) {
            return response()->json(['status' => 422, 'message' => 'Shell, part, color dan size itu sudah ada di worksheet.']);
        }

        $now = now();
        $this->db()->table('worksheet_mkt')->insert($new->map(fn ($soDet) => [
            'id_so_det' => $soDet->id,
            'id_panel' => (int) $validated['id_panel'],
            'id_part' => (int) $validated['id_part'],
            'qty' => $validated['qty'] ?? $soDet->qty,
            'cancel' => 'N',
            'created_by' => $this->userName(),
            'created_at' => $now,
        ])->values()->all());

        $skipped = count($existing) ? ' ' . count($existing) . ' baris yang sudah ada dilewati.' : '';

        return response()->json([
            'status' => 200,
            'message' => $new->count() . ' baris ditambahkan.' . $skipped,
            'rows' => $this->worksheetRows($costingId),
        ]);
    }

    public function update(Request $request, $costingId, $id)
    {
        $costingId = (int) $costingId;
        $validated = $request->validate([
            'id_panel' => ['required', 'integer'],
            'id_part' => ['required', 'integer'],
            'qty' => ['required', 'numeric', 'min:0'],
        ]);

        $row = $this->costingRows($costingId)->where('w.id', (int) $id)->first(['w.id', 'w.id_so_det']);
        if (!$row) {
            return response()->json(['status' => 404, 'message' => 'Baris worksheet tidak ditemukan.']);
        }
        if ($error = $this->validateRefs($costingId, (int) $validated['id_panel'], (int) $validated['id_part'])) {
            return response()->json(['status' => 422, 'message' => $error]);
        }
        $duplicate = $this->db()->table('worksheet_mkt')
            ->where('cancel', 'N')->where('id', '<>', $row->id)
            ->where('id_so_det', $row->id_so_det)->where('id_panel', $validated['id_panel'])->where('id_part', $validated['id_part'])
            ->exists();
        if ($duplicate) {
            return response()->json(['status' => 422, 'message' => 'Shell dan part itu sudah ada untuk color / size ini.']);
        }

        $this->db()->table('worksheet_mkt')->where('id', $row->id)->update([
            'id_panel' => (int) $validated['id_panel'],
            'id_part' => (int) $validated['id_part'],
            'qty' => (float) $validated['qty'],
        ]);

        return response()->json(['status' => 200, 'message' => 'Baris worksheet disimpan.', 'rows' => $this->worksheetRows($costingId)]);
    }

    /** Simpan edit grid (ala Excel): beberapa baris sekaligus, shell / part / qty. */
    public function updateBatch(Request $request, $costingId)
    {
        $costingId = (int) $costingId;
        $validated = $request->validate([
            'rows' => ['required', 'array', 'min:1'],
            'rows.*.id' => ['required', 'integer'],
            'rows.*.id_panel' => ['required', 'integer'],
            'rows.*.id_part' => ['required', 'integer'],
            'rows.*.qty' => ['required', 'numeric', 'min:0'],
        ]);

        $current = $this->costingRows($costingId)->get(['w.id', 'w.id_so_det', 'w.id_panel', 'w.id_part'])->keyBy('id');
        $changes = collect($validated['rows'])->keyBy(fn ($row) => (int) $row['id']);
        if ($changes->keys()->diff($current->keys())->isNotEmpty()) {
            return response()->json(['status' => 404, 'message' => 'Sebagian baris worksheet tidak ditemukan. Muat ulang halaman.']);
        }

        $shellIds = $this->shellOptions($this->activeBom($costingId)->id ?? null)->pluck('id')->all();
        $partIds = $this->partOptions()->pluck('id')->all();
        foreach ($changes as $row) {
            if (!in_array((int) $row['id_panel'], $shellIds, true)) {
                return response()->json(['status' => 422, 'message' => 'Shell harus salah satu shell yang dipilih di BOM.']);
            }
            if (!in_array((int) $row['id_part'], $partIds, true)) {
                return response()->json(['status' => 422, 'message' => 'Part tidak ditemukan di master part.']);
            }
        }

        // Cek dobel pada kondisi akhir: so_det + shell + part tidak boleh sama.
        $keys = $current->map(function ($row, $id) use ($changes) {
            $change = $changes[$id] ?? null;
            return $row->id_so_det . '|' . ($change['id_panel'] ?? $row->id_panel) . '|' . ($change['id_part'] ?? $row->id_part);
        });
        if ($keys->duplicates()->isNotEmpty()) {
            return response()->json(['status' => 422, 'message' => 'Ada baris dengan color / size, shell dan part yang sama.']);
        }

        $this->db()->transaction(function () use ($changes) {
            foreach ($changes as $id => $row) {
                $this->db()->table('worksheet_mkt')->where('id', $id)->update([
                    'id_panel' => (int) $row['id_panel'],
                    'id_part' => (int) $row['id_part'],
                    'qty' => (float) $row['qty'],
                ]);
            }
        });

        return response()->json(['status' => 200, 'message' => $changes->count() . ' baris disimpan.', 'rows' => $this->worksheetRows($costingId)]);
    }

    /** Hapus = soft delete (cancel = 'Y'). */
    public function destroy(Request $request, $costingId)
    {
        $costingId = (int) $costingId;
        $validated = $request->validate(['ids' => ['required', 'array', 'min:1'], 'ids.*' => ['integer']]);

        $ids = $this->costingRows($costingId)->whereIn('w.id', $validated['ids'])->pluck('w.id');
        $deleted = $ids->isEmpty() ? 0 : $this->db()->table('worksheet_mkt')->whereIn('id', $ids)->update(['cancel' => 'Y']);

        return response()->json(['status' => 200, 'message' => $deleted . ' baris dihapus.', 'rows' => $this->worksheetRows($costingId)]);
    }

    /** Baris worksheet aktif milik Costing (lewat so_det -> so). */
    private function costingRows(int $costingId)
    {
        return $this->db()->table('worksheet_mkt as w')
            ->join('so_det as sd', 'sd.id', '=', 'w.id_so_det')
            ->join('so', 'so.id', '=', 'sd.id_so')
            ->where('so.id_cost', $costingId)
            ->where('so.cancel_h', 'N')
            ->where('w.cancel', 'N');
    }

    private function worksheetRows(int $costingId): array
    {
        $rows = $this->costingRows($costingId)
            ->leftJoin('masterpanel as p', 'p.id', '=', 'w.id_panel')
            ->leftJoin('master_size_new as sz', 'sz.id', '=', 'sd.id_size')
            ->orderBy('p.nama_panel')->orderBy('w.id_part')->orderBy('sd.color')->orderByRaw('COALESCE(sz.urutan, 9999)')->orderBy('w.id')
            ->get(['w.id', 'w.id_so_det', 'w.id_panel', 'w.id_part', 'w.qty', 'p.nama_panel as shell',
                'sd.color', 'sd.size', 'sd.qty as qty_so', 'sd.cancel as so_det_cancel']);

        $parts = $this->partOptions()->keyBy('id');

        return $rows->map(fn ($r) => [
            'id' => (int) $r->id,
            'id_so_det' => (int) $r->id_so_det,
            'id_panel' => (int) $r->id_panel,
            'id_part' => (int) $r->id_part,
            'shell' => $r->shell ?: '-',
            'part' => $parts[(int) $r->id_part]['name'] ?? '-',
            'color' => $r->color,
            'size' => $r->size,
            'qty' => (float) $r->qty,
            'qty_so' => (float) $r->qty_so,
        ])->values()->all();
    }

    private function validateRefs(int $costingId, int $panelId, int $partId): ?string
    {
        $bom = $this->activeBom($costingId);
        if (!$this->shellOptions($bom->id ?? null)->contains('id', $panelId)) {
            return 'Shell harus salah satu shell yang dipilih di BOM.';
        }
        if (!$this->partOptions()->contains('id', $partId)) {
            return 'Part tidak ditemukan di master part.';
        }

        return null;
    }

    private function salesOrders(int $costingId)
    {
        return $this->db()->table('so')->where('id_cost', $costingId)->where('cancel_h', 'N')->orderBy('id')->get(['id', 'so_no', 'id_bom']);
    }

    /** BOM aktif Costing: BOM dari SO terbaru, kalau belum ada BOM terbaru milik Costing. */
    private function activeBom(int $costingId)
    {
        $active = fn ($q) => $q->whereNull('b.cancel')->orWhere('b.cancel', 'N');

        return $this->db()->table('so')
            ->join('bom_marketing as b', 'b.id', '=', 'so.id_bom')
            ->where('so.id_cost', $costingId)->where('so.cancel_h', 'N')->where($active)
            ->orderByDesc('so.id')
            ->first(['b.id', 'b.no_katalog_bom'])
            ?? $this->db()->table('bom_marketing as b')->where('b.id_costing', $costingId)->where($active)
                ->orderByDesc('b.id')->first(['b.id', 'b.no_katalog_bom']);
    }

    /** Hanya shell yang dipakai item BOM (bukan seluruh master shell). */
    private function shellOptions(?int $bomId)
    {
        if (!$bomId) {
            return collect();
        }

        return $this->db()->table('bom_marketing_detail as d')
            ->join('masterpanel as p', 'p.id', '=', 'd.shell')
            ->where('d.id_bom_marketing', $bomId)
            ->where('d.cancel', 'N')
            ->distinct()
            ->orderBy('p.nama_panel')
            ->get(['p.id', 'p.nama_panel'])
            ->map(fn ($p) => ['id' => (int) $p->id, 'name' => $p->nama_panel])
            ->values();
    }

    private function partOptions()
    {
        return DB::connection('mysql')->table('master_part')
            ->where(fn ($q) => $q->whereNull('cancel')->orWhere('cancel', 'N'))
            ->orderBy('nama_part')
            ->get(['id', 'nama_part', 'bag'])
            ->map(fn ($p) => [
                'id' => (int) $p->id,
                'name' => $p->nama_part,
                'label' => trim($p->nama_part . ($p->bag ? " ({$p->bag})" : '')),
            ])
            ->values();
    }

    /** so_det aktif dari SO Costing (sumber pilihan color / size). */
    private function soDetails($soIds)
    {
        if (collect($soIds)->isEmpty()) {
            return collect();
        }

        return $this->db()->table('so_det as sd')
            ->leftJoin('master_size_new as sz', 'sz.id', '=', 'sd.id_size')
            ->whereIn('sd.id_so', $soIds)
            ->where('sd.cancel', 'N')
            ->orderBy('sd.color')->orderByRaw('COALESCE(sz.urutan, 9999)')->orderBy('sd.id')
            ->get(['sd.id', 'sd.color', 'sd.size', 'sd.market', 'sd.product_set', 'sd.qty'])
            ->map(function ($d) {
                $d->id = (int) $d->id;
                $d->qty = (float) $d->qty;
                return $d;
            });
    }

    private function userName(): ?string
    {
        $user = Auth::user();

        return $user ? ($user->name ?: $user->username) : null;
    }

    private function db()
    {
        return DB::connection('mysql_sb');
    }
}
