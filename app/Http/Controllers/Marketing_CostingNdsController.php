<?php

namespace App\Http\Controllers;

use App\Services\MarketingHistoryService;
use App\Services\MarketingNdsApprovalService;
use App\Services\MarketingNdsCostingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

/**
 * Costing di DB baru (koneksi mysql_mkt), dipakai saat percobaan DB baru aktif. Route-nya sama dengan Marketing_CostingController.
 * View Costing tetap sama, jadi data DB baru dibentuk mengikuti nama field yang dipakai view.
 * Isian yang belum punya kolom di DB baru (Due Date, Qty, Ship Mode, WS Type, Shipment Type, Market) tidak ditampilkan.
 */
class Marketing_CostingNdsController extends Marketing_CostingController
{
    private const MATERIAL_CATEGORIES = ['Fabric' => 'FABRIC', 'Accessories Sewing' => 'SEWING', 'Accessories Packing' => 'PACKING'];
    private const CHARGE_GROUPS = ['Manufacturing' => 'M', 'Other Cost' => 'OC'];

    private function db()
    {
        return DB::connection('mysql_mkt');
    }

    private function nds(): MarketingNdsCostingService
    {
        return app(MarketingNdsCostingService::class);
    }

    private function history(): MarketingHistoryService
    {
        return app(MarketingHistoryService::class)->forNds();
    }

    public function index(Request $request)
    {
        if ($request->ajax()) {
            return $this->nds()->listing($request);
        }

        return view('marketing.costing.index', [
            'buyers' => $this->db()->table('master_partners')->where('is_buyer', 1)->orderBy('name')->get(['id', 'name']),
            'ndsTrial' => true,
            'containerFluid' => true,
            'page' => 'dashboard-marketing',
            'subPageGroup' => 'marketing-master',
            'subPage' => 'marketing-master-costing',
        ]);
    }

    public function costingTimeline(int $id)
    {
        $costingNo = $this->db()->table('costings')->where('id', $id)->value('costing_no');
        if (!$costingNo) {
            return response()->json(['message' => 'Data Costing tidak ditemukan.'], 404);
        }

        return response()->json([
            'data' => $this->history()->timeline($id),
            'costing' => ['cost_no' => $costingNo, 'approved' => false, 'approved_at' => null, 'approved_by' => null],
        ]);
    }

    public function create()
    {
        return view('marketing.costing.create', $this->formOptions() + [
            'product_items' => $this->nds()->productItemOptions(),
            'master_set' => collect(),
            'ndsTrial' => true,
            'page' => 'dashboard-marketing',
            'subPageGroup' => 'marketing-master',
            'subPage' => 'marketing-master-costing',
            'containerFluid' => true,
        ]);
    }

    public function store(Request $request)
    {
        $this->normalizeMoneyInputs($request);
        $request->validate($this->headerRules());
        $items = $this->validatedProductItems($request);

        $id = $this->db()->transaction(function ($db) use ($request, $items) {
            $id = $db->table('costings')->insertGetId($this->headerValues($request) + [
                'costing_no' => $this->nds()->nextNumber('costings', 'costing_no', 'CST', 5),
                'costing_date' => now()->toDateString(),
                'revision_no' => 0,
                'ga_percent' => 3,
                'cancel' => 'N',
                'created_at' => now(),
            ] + MarketingNdsCostingService::creator('costings'));
            $this->syncSets($id, $items);
            $this->savePictures($request, $id);

            // Other Cost wajib ikut otomatis: semua content group OTH.
            $currency = $db->table('master_currencies')->where('id', $request->curr)->value('currency_code');
            foreach ($this->contents('Other Cost') as $content) {
                $this->saveDetail($id, 'Other Cost', [
                    'item' => $content->id,
                    'allowance' => str_contains(strtoupper($content->content_name), 'OVERHEAD') ? 6 : 0,
                    'cons' => 1,
                    'unit' => 'PCS',
                    'curr' => $currency,
                ]);
            }

            $this->history()->costingCreated($id);

            return $id;
        });

        return redirect()->route('edit-costing', $id)->with('success', 'Costing berhasil dibuat');
    }

    public function updateHeader(Request $request, $id)
    {
        $costing = $this->db()->table('costings')->where('id', $id)->first();
        if (!$costing) {
            return $this->headerFailed($request, 'Data Costing tidak ditemukan.', 404);
        }
        if ($locked = $this->costingLockedResponse($request, $id)) {
            return $locked;
        }
        $this->normalizeMoneyInputs($request);
        $request->validate($this->headerRules() + [
            'clear_existing_pictures' => ['sometimes', 'boolean'],
            'remove_picture_files' => ['sometimes', 'array'],
            'remove_picture_files.*' => ['string', 'max:255'],
        ]);
        $items = $this->validatedProductItems($request);

        try {
            $this->db()->transaction(function ($db) use ($request, $id, $items) {
                $db->table('costings')->where('id', $id)->update($this->headerValues($request) + ['ga_percent' => $request->ga_percent ?? 3]);
                $this->syncSets((int) $id, $items);
                if ($request->boolean('clear_existing_pictures')) {
                    $this->removePictures((int) $id);
                } elseif ($request->filled('remove_picture_files')) {
                    $this->removePictures((int) $id, $request->input('remove_picture_files', []));
                }
                $this->savePictures($request, (int) $id);
                $this->history()->costingUpdated((int) $id);
            });
        } catch (\Illuminate\Database\QueryException $exception) {
            // QueryException juga RuntimeException, jadi ditangkap lebih dulu supaya pesan SQL tidak sampai ke user.
            logger()->error('Header Costing gagal disimpan.', ['costing_id' => $id, 'exception' => $exception->getMessage()]);

            return $this->headerFailed($request, ($exception->errorInfo[1] ?? null) === 1451
                ? 'Perubahan Product Item belum bisa disimpan karena masih ada data detail yang memakai item lama. Hapus baris detail item tersebut dulu, lalu Save lagi.'
                : 'Costing gagal disimpan. Coba lagi; kalau masih gagal, hubungi IT.', 500);
        } catch (\RuntimeException $exception) {
            return $this->headerFailed($request, $exception->getMessage(), 422);
        }

        // Tombol Update (bukan autosave): harga CMT tiap set di tab Manufacturing wajib terisi.
        if (!$request->ajax() && ($issues = $this->nds()->cmtIssues((int) $id))) {
            return $this->headerFailed($request, implode(' ', $issues), 422);
        }

        // Save and lock (Costing yang di-unlock): tutup unlock, Costing kembali Awaiting Approval, lalu kembali ke listing
        // supaya form tidak tertinggal terbuka saat manager meng-approve. Costing masih bisa diedit lagi lewat menu Edit;
        // Add SO baru aktif setelah manager Approve.
        if ($request->boolean('lock_after_save')) {
            try {
                $this->approvals()->relockCosting((int) $id, true);
            } catch (\RuntimeException $exception) {
                return $this->headerFailed($request, $exception->getMessage(), 422);
            }
            $message = "Costing {$costing->costing_no} disimpan dan menunggu approval Marketing Manager. SO baru bisa dibuat setelah di-approve.";

            return $request->ajax()
                ? response()->json(['status' => 200, 'message' => $message, 'redirect' => route('master-costing')])
                : redirect()->route('master-costing')->with('success', $message);
        }

        if ($request->ajax()) {
            return response()->json(['status' => 200, 'message' => 'Data berhasil di update']);
        }

        // Tombol Update di form Edit Costing: selesai simpan kembali ke listing.
        return redirect()->route('master-costing')->with('success_title', 'Costing diperbarui')
            ->with('success', "Costing {$costing->costing_no} berhasil diperbarui.");
    }

    private function headerFailed(Request $request, string $message, int $status)
    {
        return $request->ajax()
            ? response()->json(['status' => $status, 'message' => $message])
            : redirect()->back()->with('error', $message);
    }

    private function headerRules(): array
    {
        return [
            'buyer' => ['required', 'exists:mysql_mkt.master_partners,id'],
            'product_items' => ['required', 'array', 'min:1'],
            'product_items.*' => ['integer', 'exists:mysql_mkt.master_product_items,id'],
            'curr' => ['required', 'exists:mysql_mkt.master_currencies,id'],
            'qty_planned' => ['required', 'numeric', 'min:0'],
            'smv' => ['required', 'numeric', 'min:0', 'regex:/^\d+(\.\d{1,3})?$/'],
            'season_id' => ['nullable', 'exists:mysql_mkt.master_seasons,id'],
            'marketing_order' => ['nullable', 'exists:mysql_mkt.master_marketing_office,id'],
            'style' => ['nullable', 'string', self::styleSpacingRule()],
            'upload_foto' => ['sometimes', 'array'],
            'upload_foto.*' => ['image', 'max:10240'],
        ];
    }

    private function validatedProductItems(Request $request): array
    {
        $sorted = $this->nds()->sortProductItems($request->product_items);
        if ($sorted['missing']) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'product_items' => 'Product Set (Top/Bottom) belum diisi di master untuk: ' . implode(', ', $sorted['missing']),
            ]);
        }
        // Unit SET hanya untuk costing dengan lebih dari 1 product item (mis. TOP + BOTTOM).
        if (count($sorted['ids']) < 2 && strtoupper(trim((string) $request->unit)) === 'SET') {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'unit' => 'Unit SET hanya bisa dipakai kalau Product Item lebih dari 1. Pilih PCS.',
            ]);
        }

        return $sorted['ids'];
    }

    private function headerValues(Request $request): array
    {
        $number = fn ($field) => is_numeric($request->input($field)) ? $request->input($field) : null;
        $brand = Str::upper(trim((string) $request->brand));

        return [
            'buyer_id' => $request->buyer,
            'brand_id' => $brand === '' ? null : $this->nds()->master('master_brands', ['buyer_id' => $request->buyer, 'brand_name' => mb_substr($brand, 0, 100)], ['cancel' => 'N']),
            'season_id' => $request->season_id ?: null,
            'style' => Str::upper((string) $request->style),
            'main_destination_id' => is_numeric($request->main_dest) ? $request->main_dest : null,
            'planned_qty' => (int) $request->qty_planned,
            'unit_id' => $request->unit ? $this->db()->table('master_units')->where('unit_code', $request->unit)->value('id') : null,
            'currency_id' => $request->curr,
            'rate_usd_idr' => $request->rate_to_idr,
            'rate_idr_usd' => $request->rate_from_idr,
            'smv' => $number('smv'),
            'vat_percent' => $number('vat'),
            'fob_price' => $number('confirm_price'),
            'notes' => Str::upper((string) $request->notes),
            'status' => $request->status,
            'marketing_office_id' => $request->marketing_order ?: null,
            'updated_at' => now(),
        ];
    }

    /**
     * Satu baris costing_set per product item. Item yang dilepas ditolak kalau masih dipakai detail Costing atau Sales Order.
     * Baris detail tanpa set mengikuti set pertama, jadi saat set pertama dilepas detailnya pindah ke set yang tersisa.
     */
    private function syncSets(int $costingId, array $itemIds): void
    {
        $db = $this->db();
        $existing = self::activeRows($db->table('costing_set as s'), 's.cancel')
            ->leftJoin('master_product_items as p', 'p.id', '=', 's.product_items_id')
            ->where('s.costing_id', $costingId)
            ->orderBy('s.id')
            ->get(['s.id', 's.product_items_id', 'p.product_item_name']);

        // Costing satu set yang item-nya diganti (mis. T-SHIRT -> POLO SHIRT): set yang sama dipakai ulang, jadi isi detail ikut pindah.
        $removed = $existing->reject(fn ($set) => in_array((int) $set->product_items_id, $itemIds, true))->values();
        $added = array_values(array_diff($itemIds, $existing->pluck('product_items_id')->map(fn ($id) => (int) $id)->all()));
        if ($existing->count() === 1 && $removed->count() === 1 && count($added) === 1) {
            $set = $removed->first();
            if ($db->table('so_line')->where('costing_set_id', $set->id)->exists()) {
                throw new \RuntimeException("{$set->product_item_name} sudah dipakai Sales Order, tidak bisa diganti.");
            }
            $db->table('costing_set')->where('id', $set->id)->update(['product_items_id' => $added[0], 'updated_at' => now()]);
            $db->table('costing_materials')->where('costing_set_id', $set->id)->update(['product_items_id' => $added[0]]);
            $db->table('costing_charges')->where('costing_set_id', $set->id)->update(['product_items_id' => $added[0]]);
            $db->table('costing_charges')->where('costings_id', $costingId)->whereNull('costing_set_id')->update(['product_items_id' => $added[0]]);
            $this->ensureCmt($costingId);

            return;
        }

        foreach ($existing as $set) {
            if (in_array((int) $set->product_items_id, $itemIds, true)) {
                continue;
            }
            if ($db->table('so_line')->where('costing_set_id', $set->id)->exists()) {
                throw new \RuntimeException("{$set->product_item_name} sudah dipakai Sales Order, tidak bisa dihapus.");
            }
            // Baris yang belum diisi hanya penanda set, jadi ikut di-cancel bersama itemnya (tanpa hard delete). Baris yang sudah punya part dihitung berisi.
            $cancel = ['cancel' => 'Y', 'updated_at' => now()];
            self::activeRows($db->table('costing_materials')->where('costing_set_id', $set->id))->whereNull('shells_id')
                ->whereNotIn('id', fn ($q) => self::activeRows($q->from('costing_material_parts'))->select('costing_materials_id'))
                ->where(fn ($q) => $q->whereNull('cons')->orWhere('cons', 0))
                ->where(fn ($q) => $q->whereNull('price')->orWhere('price', 0))
                ->whereNull('supplier_id')
                ->update($cancel);
            self::activeRows($db->table('costing_charges')->where('costing_set_id', $set->id))
                // Template CMT sudah punya cons, jadi yang menandai kosong hanya harganya.
                ->where(fn ($q) => $q->whereNull('cons')->orWhere('cons', 0)->orWhere('contents_id', $this->nds()->cmtContentId()))
                ->where(fn ($q) => $q->whereNull('price')->orWhere('price', 0))
                ->whereNull('supplier_id')
                ->update($cancel);
            $used = self::activeRows($db->table('costing_materials')->where('costing_set_id', $set->id))->count()
                + self::activeRows($db->table('costing_charges')->where('costing_set_id', $set->id))->count();
            if ($used > 0) {
                throw new \RuntimeException("{$set->product_item_name} belum bisa dilepas karena masih ada {$used} baris detail berisi di set ini. Hapus baris-baris tersebut dulu di tabel detail, lalu Save lagi.");
            }
            $db->table('costing_set')->where('id', $set->id)->update($cancel);
        }

        $kept = $existing->pluck('product_items_id')->map(fn ($id) => (int) $id)->all();
        foreach ($itemIds as $itemId) {
            if (!in_array($itemId, $kept, true)) {
                $db->table('costing_set')->insert([
                    'costing_id' => $costingId,
                    'product_items_id' => $itemId,
                    'cancel' => 'N',
                    'created_at' => now(),
                    'updated_at' => now(),
                ] + MarketingNdsCostingService::creator('costing_set'));
            }
        }

        // Other Cost tidak punya set; product item-nya mengikuti set pertama.
        $db->table('costing_charges')->where('costings_id', $costingId)->whereNull('costing_set_id')->update(['product_items_id' => $this->firstSet($costingId)->product_items_id]);

        $this->ensureCmt($costingId);
    }

    /** Template Manufacturing: tiap set punya satu baris CMT. Harganya diisi user dan wajib sebelum Costing dipakai SO. */
    private function ensureCmt(int $costingId): void
    {
        $contentId = $this->nds()->cmtContentId();
        if (!$contentId) {
            return;
        }
        foreach ($this->nds()->costingSets($costingId)['sets'] as $set) {
            $exists = self::activeRows($this->db()->table('costing_charges')->where('costing_set_id', $set->id)->where('contents_id', $contentId))->exists();
            if (!$exists) {
                $this->saveDetail($costingId, 'Manufacturing', ['item' => $contentId, 'cons' => 1, 'unit' => 'PCS', 'set' => $set->id]);
            }
        }
        $this->nds()->syncCmPrice($costingId);
    }

    private function firstSet(int $costingId): object
    {
        return self::activeRows($this->db()->table('costing_set')->where('costing_id', $costingId))->orderBy('id')->first();
    }

    public function copyCosting($id)
    {
        $db = $this->db();
        $original = $db->table('costings')->where('id', $id)->first();
        if (!$original) {
            return redirect()->route('master-costing')->with('error', 'Data Costing tidak ditemukan.');
        }

        $copiedFiles = [];
        try {
            [$newId, $number] = $db->transaction(function ($db) use ($original, $id, &$copiedFiles) {
                $number = $this->nds()->nextNumber('costings', 'costing_no', 'CST', 5);
                $header = (array) $original;
                unset($header['id']);
                // Salinan adalah Costing baru: approval mulai dari Awaiting, bukan ikut status Costing asal.
                if (array_key_exists('approval', $header)) {
                    $header['approval'] = 'W';
                }
                if (array_key_exists('approved_at', $header)) {
                    $header['approved_at'] = null;
                }
                $newId = $db->table('costings')->insertGetId(array_merge($header, [
                    'costing_no' => $number,
                    'costing_date' => now()->toDateString(),
                    'revision_no' => 0,
                    'approved_by' => null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ], MarketingNdsCostingService::creator('costings')));

                // Salinan dicatat atas nama user yang menyalin, bukan pembuat Costing asal.
                $setMap = [];
                foreach (self::activeRows($db->table('costing_set')->where('costing_id', $id))->orderBy('id')->get() as $set) {
                    $row = array_merge((array) $set, ['costing_id' => $newId, 'created_at' => now(), 'updated_at' => now()], MarketingNdsCostingService::creator('costing_set'));
                    unset($row['id']);
                    $setMap[$set->id] = $db->table('costing_set')->insertGetId($row);
                }
                foreach (self::activeRows($db->table('costing_materials')->where('costings_id', $id))->orderBy('id')->get() as $material) {
                    $row = array_merge((array) $material, ['costings_id' => $newId, 'costing_set_id' => $setMap[$material->costing_set_id] ?? null, 'created_at' => now(), 'updated_at' => now()], MarketingNdsCostingService::creator('costing_materials'));
                    unset($row['id']);
                    $newMaterialId = $db->table('costing_materials')->insertGetId($row);
                    foreach (self::activeRows($db->table('costing_material_parts')->where('costing_materials_id', $material->id))->get() as $part) {
                        $db->table('costing_material_parts')->insert(['costing_materials_id' => $newMaterialId, 'parts_id' => $part->parts_id, 'cancel' => 'N', 'created_at' => now(), 'updated_at' => now()] + MarketingNdsCostingService::creator('costing_material_parts'));
                    }
                }
                foreach (self::activeRows($db->table('costing_charges')->where('costings_id', $id))->orderBy('id')->get() as $charge) {
                    $row = array_merge((array) $charge, ['costings_id' => $newId, 'costing_set_id' => $setMap[$charge->costing_set_id] ?? null, 'created_at' => now(), 'updated_at' => now()], MarketingNdsCostingService::creator('costing_charges'));
                    unset($row['id']);
                    $db->table('costing_charges')->insert($row);
                }
                foreach (self::activeRows($db->table('costing_images')->where('costings_id', $id))->orderBy('id')->get() as $image) {
                    $source = public_path($image->file_path);
                    if (!is_file($source)) {
                        throw new \RuntimeException('File gambar Costing tidak ditemukan: ' . basename($image->file_path));
                    }
                    $extension = pathinfo($source, PATHINFO_EXTENSION);
                    $newPath = 'uploads/costing/' . Str::uuid() . ($extension ? '.' . $extension : '');
                    if (!copy($source, public_path($newPath))) {
                        throw new \RuntimeException('Gagal menyalin gambar Costing: ' . basename($image->file_path));
                    }
                    $copiedFiles[] = public_path($newPath);
                    $db->table('costing_images')->insert(['costings_id' => $newId, 'file_path' => $newPath, 'image_type' => $image->image_type, 'cancel' => 'N', 'created_at' => now(), 'updated_at' => now()] + MarketingNdsCostingService::creator('costing_images'));
                }

                $this->history()->record($newId, 'costing', 'copied', "Costing {$number} disalin dari {$original->costing_no}.");

                return [$newId, $number];
            });
        } catch (\Throwable $exception) {
            foreach ($copiedFiles as $file) {
                @unlink($file);
            }

            throw $exception;
        }

        return redirect()->route('edit-costing', $newId)->with('success', "Costing berhasil dicopy! No. Costing baru: $number");
    }

    public function edit($id)
    {
        return $this->page($id, false);
    }

    public function show($id)
    {
        return $this->page($id, true);
    }

    /** SO terakhir Costing ini beserta WS-nya, untuk pengingat di halaman Edit. */
    private function soInfo(int $costingId): ?array
    {
        $so = $this->db()->table('sales_orders')->where('costing_id', $costingId)->orderByDesc('id')->first(['id', 'so_no']);

        return $so ? [
            'id' => (int) $so->id,
            'so_no' => $so->so_no,
            'worksheets' => app(\App\Services\MarketingSalesOrderService::class)->worksheetSummary((int) $so->id),
        ] : null;
    }

    private function page($id, bool $viewOnly)
    {
        $costing = $this->costingForView((int) $id);
        if (!$costing) {
            return redirect()->route('master-costing')->with('error', 'Data Costing tidak ditemukan!');
        }
        if (!$viewOnly) {
            // Costing yang dibuat sebelum ada template CMT.
            $this->db()->transaction(fn () => $this->ensureCmt((int) $id));
        }

        return view($viewOnly ? 'marketing.costing.detail' : 'marketing.costing.edit', $this->formOptions() + [
            'costing' => $costing,
            'details' => $this->getCostingDetail($id, $costing->rate),
            'pictures' => $this->pictures((int) $id),
            'master_set' => $costing->master_set,
            'set' => $costing->master_set,
            // Pilihan Shell hanya yang namanya diawali "Shell" (Shell A, Shell 1, ...). Shell lain yang sudah tersimpan tetap tampil di tabel.
            'shellOptions' => $this->db()->table('master_shells')->where(fn ($q) => $q->whereNull('cancel')->orWhere('cancel', 'N'))
                ->where('shell_name', 'like', 'Shell%')->orderBy('shell_name')->get(['id', 'shell_name as name']),
            'partOptions' => $this->db()->table('master_parts')->where(fn ($q) => $q->whereNull('cancel')->orWhere('cancel', 'N'))->orderBy('part_name')->get(['id', 'part_name as name']),
            // Kondisi approval / lock asli (costings.approval + unlock_cost), format sama dengan alur Signalbit.
            'unlock' => $this->approvals()->state('costing', (int) $id),
            'viewOnly' => $viewOnly,
            'ndsTrial' => true,
            'cmtContentId' => $this->nds()->cmtContentId(),
            'cmtIssues' => $viewOnly ? [] : $this->nds()->cmtIssues((int) $id),
            'soInfo' => $this->soInfo((int) $id),
            'product_items' => $this->nds()->productItemOptions($costing->product_items),
            'buyerName' => $costing->nama_buyer,
            'page' => 'dashboard-marketing',
            'subPageGroup' => 'marketing-master',
            'subPage' => 'marketing-master-costing',
            'containerFluid' => true,
        ]);
    }

    /** Dropdown form dalam bentuk field yang dipakai view Costing. */
    private function formOptions(): array
    {
        $db = $this->db();
        $active = fn ($table) => $db->table($table)->where(fn ($q) => $q->whereNull('cancel')->orWhere('cancel', 'N'));
        $partner = fn ($row) => (object) [
            'Id_Supplier' => $row->id, 'id_supplier' => $row->id, 'Supplier' => $row->name, 'supplier' => $row->name,
            'supplier_code' => $row->short_name ?: $row->code,
        ];

        return [
            'buyers' => $active('master_partners')->where('is_buyer', 1)->orderBy('name')->get()->map($partner),
            'suppliers' => $active('master_partners')->where('is_supplier', 1)->orderBy('name')->get()->map($partner),
            'product_groups' => collect(),
            'currencies' => $active('master_currencies')->orderBy('currency_code')->get()->map(fn ($row) => (object) ['id' => $row->id, 'nama_pilihan' => $row->currency_code]),
            'shipmodes' => collect(),
            'marketing_orders' => $active('master_marketing_office')->orderBy('office_name')->get()->map(fn ($row) => (object) ['id' => $row->id, 'mkt_order' => $row->office_name]),
            'status_cst' => collect(),
            // nama_pilihan = kode (nilai yang disimpan & tampil setelah dipilih), label = "kode - nama" untuk daftar pilihan.
            'units' => $units = $active('master_units')->orderBy('unit_code')->get()
                ->map(fn ($row) => (object) ['nama_pilihan' => $row->unit_code, 'label' => trim($row->unit_code . ($row->unit_name ? ' - ' . $row->unit_name : ''))]),
            // Unit header Costing hanya PCS dan SET.
            'header_units' => $units->filter(fn ($u) => in_array(strtoupper($u->nama_pilihan), ['PCS', 'SET'], true))->values(),
            'destinations' => $active('master_destinations')->orderBy('destination_name')->get()->map(fn ($row) => (object) ['id' => $row->id, 'country_name' => $row->destination_name]),
            'seasons' => $active('master_seasons')->orderBy('season_name')->get()->map(fn ($row) => (object) ['id_season' => $row->id, 'season' => $row->season_code, 'season_desc' => $row->season_name]),
        ];
    }

    private function costingForView(int $id): ?object
    {
        $costing = $this->db()->table('costings as c')
            ->leftJoin('master_partners as b', 'b.id', '=', 'c.buyer_id')
            ->leftJoin('master_brands as br', 'br.id', '=', 'c.brand_id')
            ->leftJoin('master_seasons as se', 'se.id', '=', 'c.season_id')
            ->leftJoin('master_currencies as cu', 'cu.id', '=', 'c.currency_id')
            ->leftJoin('master_units as u', 'u.id', '=', 'c.unit_id')
            ->leftJoin('master_marketing_office as o', 'o.id', '=', 'c.marketing_office_id')
            ->where('c.id', $id)
            ->first(['c.*', 'b.name as nama_buyer', 'b.short_name as buyer_short', 'b.code as buyer_full_code', 'br.brand_name', 'se.season_name', 'cu.currency_code', 'u.unit_code', 'o.office_name']);
        if (!$costing) {
            return null;
        }

        $sets = $this->nds()->costingSets($id)['sets'];
        $pictures = $this->pictures($id);

        $costing->cost_no = $costing->costing_no;
        $costing->no_costing = $costing->costing_no;
        $costing->kpno = null;
        $costing->buyer = $costing->buyer_id;
        $costing->id_buyer = $costing->buyer_id;
        $costing->buyer_code = $costing->buyer_short ?: $costing->buyer_full_code;
        $costing->brand = $costing->brand_name;
        $costing->season = $costing->season_name;
        $costing->curr = $costing->currency_id;
        $costing->nama_curr = $costing->currency_code;
        $costing->unit = $costing->unit_code;
        $costing->rate = $costing->rate_usd_idr;
        $costing->rate_to_idr = $costing->rate_usd_idr;
        $costing->rate_from_idr = $costing->rate_idr_usd;
        $costing->confirm_price = $costing->fob_price;
        $costing->cfm_price = $costing->fob_price;
        $costing->smv_min = $costing->smv;
        $costing->vat = $costing->vat_percent;
        $costing->qty_planned = $costing->planned_qty;
        $costing->qty = $costing->planned_qty;
        $costing->marketing_order = $costing->marketing_office_id;
        $costing->mkt_order = $costing->office_name;
        $costing->main_dest = $costing->main_destination_id;
        $costing->foto = $pictures->first()->file_name ?? null;
        $costing->created_by = null;
        $costing->username = null;
        $costing->dateinput = $costing->created_at;
        foreach (['market', 'deldate', 'ship_mode', 'nama_ship_mode', 'tipe_ws', 'approved_at'] as $column) {
            $costing->{$column} = null;
        }
        $costing->shipment_type = '';

        // Set Costing menggantikan Product Type: id-nya dipakai kolom Set pada detail.
        $costing->type = $sets->count() > 1 ? 'multiple' : 'single';
        $costing->product_set = $costing->type === 'multiple' ? $sets->pluck('id')->implode(',') : null;
        $costing->master_set = $sets->values()->map(fn ($set, $index) => (object) ['id' => $set->id, 'nama' => $set->set_type, 'urutan' => $index + 1]);
        $costing->product_items = $sets->pluck('product_items_id')->map(fn ($itemId) => (int) $itemId)->all();
        $costing->product_item = $sets->pluck('product_item_name')->implode(', ');
        $costing->product_item_name = $costing->product_item;
        $costing->nama_product_item = $costing->product_item;
        $costing->product_group = $sets->pluck('product_group_name')->filter()->unique()->implode(', ');

        return $costing;
    }

    protected function getCostingDetail($costingId, $rateToIdr)
    {
        $db = $this->db();
        $details = collect(['Fabric' => collect(), 'Accessories Sewing' => collect(), 'Accessories Packing' => collect(), 'Manufacturing' => collect(), 'Other Cost' => collect()]);
        $rateToIdr = (float) $rateToIdr;
        $sets = $this->nds()->costingSets((int) $costingId)['sets'];
        $setNames = $sets->pluck('set_type', 'id');
        $isMultiple = $sets->count() > 1;
        // nama_set selalu diisi (juga untuk satu set) supaya tabel menulis TOP/BOTTOM, bukan SINGLE.

        $materials = self::activeRows($db->table('costing_materials as d'), 'd.cancel')
            ->leftJoin('master_contents as m', 'm.id', '=', 'd.contents_id')
            ->leftJoin('master_group as g', 'g.id', '=', 'm.group_id')
            ->leftJoin('master_partners as s', 's.id', '=', 'd.supplier_id')
            ->leftJoin('master_units as u', 'u.id', '=', 'd.units_id')
            ->leftJoin('master_currencies as cu', 'cu.id', '=', 'd.currency_id')
            ->leftJoin('master_shells as sh', 'sh.id', '=', 'd.shells_id')
            ->where('d.costings_id', $costingId)
            ->orderBy('d.id')
            ->get(['d.*', 'm.content_code', 'm.content_name', 'g.group_name', 's.name as nama_supplier', 'u.unit_code', 'cu.currency_code', 'sh.shell_name']);
        $parts = self::activeRows($db->table('costing_material_parts as mp'), 'mp.cancel')
            ->join('master_parts as p', 'p.id', '=', 'mp.parts_id')
            ->whereIn('mp.costing_materials_id', $materials->pluck('id')->all())
            ->get(['mp.costing_materials_id', 'mp.parts_id', 'p.part_name'])
            ->keyBy('costing_materials_id');

        foreach ($materials as $row) {
            $category = null;
            foreach (self::MATERIAL_CATEGORIES as $name => $keyword) {
                if (str_contains(strtoupper((string) $row->group_name), $keyword)) {
                    $category = $name;
                }
            }
            if (!$category) {
                continue;
            }
            $this->fillDetailRow($row, $category, trim("{$row->content_code} {$row->group_name} {$row->content_name}"), $rateToIdr);
            $row->id_panel = $row->shells_id;
            $row->shell_name = $row->shell_name;
            $row->id_part = $parts[$row->id]->parts_id ?? null;
            $row->part_name = $parts[$row->id]->part_name ?? null;
            $row->set = $isMultiple ? $row->costing_set_id : '';
            $row->nama_set = $setNames[$row->costing_set_id] ?? '';
            $details[$category]->push($row);
        }

        $charges = self::activeRows($db->table('costing_charges as d'), 'd.cancel')
            ->leftJoin('master_contents as m', 'm.id', '=', 'd.contents_id')
            ->leftJoin('master_group as g', 'g.id', '=', 'm.group_id')
            ->leftJoin('master_partners as s', 's.id', '=', 'd.supplier_id')
            ->leftJoin('master_units as u', 'u.id', '=', 'd.units_id')
            ->leftJoin('master_currencies as cu', 'cu.id', '=', 'd.currency_id')
            ->where('d.costings_id', $costingId)
            ->orderBy('d.id')
            ->get(['d.*', 'm.content_name', 'g.group_code', 's.name as nama_supplier', 'u.unit_code', 'cu.currency_code']);
        foreach ($charges as $row) {
            $category = array_search($row->group_code, self::CHARGE_GROUPS, true);
            if (!$category) {
                continue;
            }
            $this->fillDetailRow($row, $category, trim((string) $row->content_name), $rateToIdr);
            $row->set = $isMultiple && $row->costing_set_id ? $row->costing_set_id : '';
            $row->nama_set = $setNames[$row->costing_set_id] ?? '';
            $details[$category]->push($row);
        }

        // Baris satu content berkumpul, urut per set. CMT (wajib diisi) paling atas. Other Cost tidak punya set.
        $setOrder = $sets->pluck('id')->flip();
        $cmtId = $this->nds()->cmtContentId();

        return $details->map(fn ($rows, $category) => $category === 'Other Cost' ? $rows : $rows->sortBy(
            fn ($row) => sprintf('%d|%012d|%05d|%012d', (int) $row->contents_id === $cmtId ? 0 : 1, $row->contents_id, $setOrder[$row->costing_set_id] ?? 999, $row->id)
        )->values());
    }

    private function fillDetailRow(object $row, string $category, string $name, float $rateToIdr): void
    {
        $row->type = $category;
        $row->id_item = $row->contents_id;
        $row->item_id = $row->contents_id;
        $row->nama_item = Str::upper($name);
        $row->item_desc = $row->description ?? '';
        $row->unit = $row->unit_code;
        $row->curr = $row->currency_code;
        $row->allowance = $row->allowance_percent;
        $row->price_px_idr = $row->price_idr;
        $row->price_px_usd = $row->price_usd;
        $row->jenis_rate = null;
        $row->set = null;
        $this->collectCostingDetail($row, $rateToIdr);
    }

    public function getBrands(Request $request)
    {
        $brands = $this->db()->table('master_brands')
            ->where('buyer_id', $request->buyer)
            ->where(fn ($q) => $q->whereNull('cancel')->orWhere('cancel', 'N'))
            ->orderBy('brand_name')
            ->get(['id as kd_brand', 'brand_name as brand']);

        return response()->json(['status' => 200, 'data' => $brands]);
    }

    public function storeBrand(Request $request)
    {
        $request->merge(['brand' => Str::upper(preg_replace('/\s+/', ' ', trim((string) $request->brand)))]);
        $validator = Validator::make($request->all(), [
            'buyer' => ['required', 'exists:mysql_mkt.master_partners,id'],
            'brand' => ['required', 'max:100'],
        ], ['buyer.required' => 'Pilih buyer dulu.', 'buyer.exists' => 'Buyer tidak valid.'], ['brand' => 'Brand Name']);
        if ($validator->fails()) {
            return response()->json(['status' => 422, 'message' => $validator->errors()->first()]);
        }
        $exists = $this->db()->table('master_brands')->where('buyer_id', $request->buyer)->whereRaw('UPPER(brand_name) = ?', [$request->brand])->exists();
        if ($exists) {
            return response()->json(['status' => 422, 'message' => 'Brand ini sudah ada untuk buyer tersebut.']);
        }
        $id = $this->nds()->master('master_brands', ['buyer_id' => $request->buyer, 'brand_name' => $request->brand], ['cancel' => 'N']);

        return response()->json(['status' => 200, 'message' => 'Brand berhasil ditambahkan.', 'data' => ['kd_brand' => $id, 'brand' => $request->brand]]);
    }

    public function getItemContents(Request $request)
    {
        $html = "<option value=''>Choose Content</option>";
        if ($request->filled('kategori')) {
            // CMT tidak dipilih manual lagi: harganya diisi lewat card CM Price di header (baris CMT dibuat otomatis per set).
            $cmtId = $this->nds()->cmtContentId();
            foreach ($this->contents($request->kategori)->reject(fn ($content) => (int) $content->id === (int) $cmtId) as $content) {
                $label = e(Str::upper(trim("{$content->content_code} {$content->group_name} {$content->content_name}")));
                $html .= "<option value='{$content->id}'>{$label}</option>";
            }
        }

        return response()->json($html);
    }

    /** Content master milik satu kategori detail Costing. */
    private function contents(string $category)
    {
        $query = $this->db()->table('master_contents as m')
            ->join('master_group as g', 'g.id', '=', 'm.group_id')
            ->where(fn ($q) => $q->whereNull('m.cancel')->orWhere('m.cancel', 'N'))
            ->orderBy('m.content_name')
            ->select('m.id', 'm.content_code', 'm.content_name', 'g.group_name');
        if (isset(self::CHARGE_GROUPS[$category])) {
            return $query->where('g.group_code', self::CHARGE_GROUPS[$category])->get();
        }
        if (isset(self::MATERIAL_CATEGORIES[$category])) {
            return $query->whereRaw('UPPER(g.group_name) LIKE ?', ['%' . self::MATERIAL_CATEGORIES[$category] . '%'])->get();
        }

        return collect();
    }

    private function detailTable(string $category): string
    {
        return isset(self::CHARGE_GROUPS[$category]) ? 'costing_charges' : 'costing_materials';
    }

    /** Baris detail yang belum dihapus. Baris yang sudah dipakai BOM hanya ditandai cancel = 'Y' (lihat removeDetailRows). */
    private static function activeRows($query, string $column = 'cancel')
    {
        return $query->where(fn ($q) => $q->whereNull($column)->orWhere($column, '!=', 'Y'));
    }

    /**
     * Menghapus baris detail Costing = cancel 'Y' (tidak ada hard delete di marketing). BOM menampilkan baris yang dipakai BOM
     * sebagai "sudah dihapus dari Costing" dan membatalkannya saat BOM disimpan.
     */
    private function removeDetailRows($db, string $table, $ids): void
    {
        $ids = collect($ids)->map(fn ($id) => (int) $id)->unique()->values();
        if ($ids->isEmpty()) {
            return;
        }
        // Semua di-cancel (tanpa hard delete, aturan marketing) supaya tetap jadi histori; part ikut di-cancel.
        $db->table($table)->whereIn('id', $ids)->update(['cancel' => 'Y', 'updated_at' => now()]);
        if ($table === 'costing_materials') {
            self::activeRows($db->table('costing_material_parts')->whereIn('costing_materials_id', $ids))->update(['cancel' => 'Y', 'updated_at' => now()]);
        }
    }

    private function assertContent(string $category, $contentId): void
    {
        if (!$this->contents($category)->contains('id', (int) $contentId)) {
            throw new \InvalidArgumentException('Item tidak sesuai dengan kategori Costing.');
        }
    }

    /** Nilai baris detail. Currency kosong mengikuti currency header. */
    private function detailValues($costingId, string $category, array $detail): array
    {
        $db = $this->db();
        $header = $db->table('costings')->where('id', $costingId)->first(['currency_id']);
        $unit = trim((string) ($detail['unit'] ?? ''));
        $currency = trim((string) ($detail['curr'] ?? ''));
        // Cons disimpan 3 desimal, semua price 2 desimal.
        $round = fn ($value, int $decimals) => round((float) ($value ?? 0), $decimals);
        $values = [
            'contents_id' => $detail['item'],
            'description' => $detail['desc'] ?? null,
            'supplier_id' => ($detail['supplier'] ?? null) ?: null,
            'cons' => $round($detail['cons'] ?? 0, 3),
            'units_id' => $unit === '' ? null : $db->table('master_units')->where('unit_code', $unit)->value('id'),
            'allowance_percent' => $detail['allowance'] ?? 0,
            'price' => $round($detail['price'] ?? 0, 2),
            'currency_id' => ($currency === '' ? null : $db->table('master_currencies')->where('currency_code', $currency)->value('id')) ?: $header->currency_id,
            'price_idr' => $round($detail['price_px_idr'] ?? 0, 2),
            'price_usd' => $round($detail['price_px_usd'] ?? 0, 2),
            'value_idr' => $detail['value_idr'] ?? 0,
            'value_usd' => $detail['value_usd'] ?? 0,
            'updated_at' => now(),
        ];
        if ($category !== 'Other Cost') {
            // Baris tanpa set masuk ke set pertama.
            $sets = $this->nds()->costingSets((int) $costingId)['sets'];
            $set = $sets->firstWhere('id', (int) ($detail['set'] ?? 0)) ?? $sets->first();
            $values['costing_set_id'] = $set->id;
            $values['product_items_id'] = $set->product_items_id;
        }

        return $values;
    }

    private function saveDetail($costingId, string $category, array $detail): int
    {
        $this->assertContent($category, $detail['item']);
        $values = $this->detailValues($costingId, $category, $detail) + [
            'costings_id' => $costingId,
            'cancel' => 'N',
            'created_at' => now(),
        ] + MarketingNdsCostingService::creator($this->detailTable($category));
        if ($category === 'Other Cost') {
            $values['product_items_id'] = $this->firstSet((int) $costingId)->product_items_id;
        }

        return (int) $this->db()->table($this->detailTable($category))->insertGetId($values);
    }

    private function detailRules(): array
    {
        return [
            'id_costing' => ['required', 'exists:mysql_mkt.costings,id'],
            'category' => ['required', 'in:Fabric,Accessories Sewing,Accessories Packing,Manufacturing,Other Cost'],
            'item' => ['required', 'integer'],
            'cons' => ['nullable', 'numeric'],
            'price' => ['nullable', 'numeric'],
            'allowance' => ['nullable', 'numeric'],
            'set' => ['nullable', 'integer'],
        ];
    }

    public function storeDetail(Request $request)
    {
        $request->validate($this->detailRules());
        if ($locked = $this->costingLockedResponse($request, $request->id_costing)) {
            return $locked;
        }
        if ((int) $request->item === $this->nds()->cmtContentId()) {
            return response()->json(['status' => 422, 'message' => 'CMT sudah ada di tabel Manufacturing, tinggal isi harganya.']);
        }
        try {
            $detailId = $this->db()->transaction(function () use ($request) {
                $detailId = $this->saveDetail($request->id_costing, $request->category, $request->only(['item', 'desc', 'supplier', 'curr', 'price', 'cons', 'unit', 'price_px_idr', 'price_px_usd', 'allowance', 'set', 'value_idr', 'value_usd']));
                $this->history()->costingItemsChanged((int) $request->id_costing, 'items_added', (string) $request->category);

                return $detailId;
            });

            return response()->json(['status' => 200, 'message' => 'Item berhasil ditambahkan!', 'insert_id' => $detailId]);
        } catch (\Exception $e) {
            return response()->json(['status' => 500, 'message' => 'Terjadi kesalahan sistem: ' . $e->getMessage()]);
        }
    }

    /**
     * Fabric: pecah content ke beberapa shell, atau shell ke beberapa part (multi-select dari modal).
     * Baris yang belum punya shell/part diisi duluan; sisanya jadi baris baru (salinan baris sumber dengan cons & value 0).
     */
    public function storeFabricBreakdown(Request $request)
    {
        $isShell = $request->type === 'shell';
        $request->validate([
            'id_costing' => ['required', 'exists:mysql_mkt.costings,id'],
            'id_detail' => ['required', 'integer'],
            'type' => ['required', 'in:shell,part'],
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer', $isShell ? 'exists:mysql_mkt.master_shells,id' : 'exists:mysql_mkt.master_parts,id'],
        ], [], ['ids' => $isShell ? 'Shell' : 'Part']);
        if ($locked = $this->costingLockedResponse($request, $request->id_costing)) {
            return $locked;
        }

        $db = $this->db();
        $source = self::activeRows($db->table('costing_materials')->where('id', $request->id_detail)->where('costings_id', $request->id_costing))->first();
        if (!$source) {
            return response()->json(['status' => 404, 'message' => 'Content tidak ditemukan.']);
        }
        if (!$isShell && !$source->shells_id) {
            return response()->json(['status' => 422, 'message' => 'Pilih shell dulu sebelum menambah part.']);
        }

        // Satu content = content + set; part dikelompokkan lagi per shell.
        $group = self::activeRows($db->table('costing_materials as m'), 'm.cancel')
            ->leftJoin('costing_material_parts as mp', fn ($j) => $j->on('mp.costing_materials_id', '=', 'm.id')
                ->where(fn ($w) => $w->whereNull('mp.cancel')->orWhere('mp.cancel', '!=', 'Y')))
            ->where('m.costings_id', $source->costings_id)
            ->where('m.contents_id', $source->contents_id)
            ->where('m.costing_set_id', $source->costing_set_id)
            ->when(!$isShell, fn ($q) => $q->where('m.shells_id', $source->shells_id))
            ->orderByRaw('m.id = ? DESC', [$source->id])->orderBy('m.id')
            ->get(['m.id', 'm.shells_id', 'mp.parts_id']);
        $column = $isShell ? 'shells_id' : 'parts_id';
        $newIds = array_values(array_diff(array_unique(array_map('intval', $request->ids)), $group->pluck($column)->filter()->map(fn ($id) => (int) $id)->all()));
        if (!$newIds) {
            return response()->json(['status' => 422, 'message' => ($isShell ? 'Shell' : 'Part') . ' yang dipilih sudah ada semua.']);
        }
        $blank = $group->first(fn ($row) => !$row->{$column} && ($isShell ? !$row->parts_id : true));

        $db->transaction(function ($db) use ($source, $newIds, $blank, $isShell, $request) {
            $addPart = fn ($materialId, $partId) => $db->table('costing_material_parts')->insert(['costing_materials_id' => $materialId, 'parts_id' => $partId, 'cancel' => 'N', 'created_at' => now(), 'updated_at' => now()] + MarketingNdsCostingService::creator('costing_material_parts'));
            foreach ($newIds as $id) {
                if ($blank) {
                    $isShell
                        ? $db->table('costing_materials')->where('id', $blank->id)->update(['shells_id' => $id])
                        : $addPart($blank->id, $id);
                    $blank = null;
                    continue;
                }
                $copy = (array) $source;
                unset($copy['id']);
                $newId = $db->table('costing_materials')->insertGetId(array_merge($copy, [
                    'shells_id' => $isShell ? $id : $source->shells_id,
                    'cons' => 0, 'value_idr' => 0, 'value_usd' => 0,
                    'created_at' => now(), 'updated_at' => now(),
                ], MarketingNdsCostingService::creator('costing_materials')));
                if (!$isShell) {
                    $addPart($newId, $id);
                }
            }
            $this->history()->costingItemsChanged((int) $request->id_costing, 'items_added', 'Fabric');
        });

        return response()->json(['status' => 200, 'message' => count($newIds) . ' ' . ($isShell ? 'shell' : 'part') . ' ditambahkan.']);
    }

    public function destroyDetail(Request $request, $id)
    {
        $request->validate([
            'category' => ['required', 'in:Fabric,Accessories Sewing,Accessories Packing,Manufacturing,Other Cost'],
            'id_costing' => ['required', 'exists:mysql_mkt.costings,id'],
        ]);
        if ($locked = $this->costingLockedResponse($request, $request->id_costing)) {
            return $locked;
        }
        try {
            if ($request->category !== 'Other Cost') {
                return $this->destroySetRow($request, (int) $id);
            }

            $deleted = $this->db()->transaction(function ($db) use ($request, $id) {
                $table = $this->detailTable($request->category);
                $deleted = self::activeRows($db->table($table)->where('id', $id)->where('costings_id', $request->id_costing))->exists();
                if ($deleted) {
                    $this->removeDetailRows($db, $table, [$id]);
                    $this->history()->costingItemsChanged((int) $request->id_costing, 'items_deleted', (string) $request->category);
                }

                return $deleted;
            });
            if (!$deleted) {
                return response()->json(['status' => 404, 'message' => 'Data detail tidak ditemukan.']);
            }

            return response()->json(['status' => 200, 'message' => 'Data berhasil dihapus!']);
        } catch (\Exception $e) {
            return response()->json(['status' => 500, 'message' => 'Gagal menghapus data: ' . $e->getMessage()]);
        }
    }

    /**
     * Satu content selalu punya baris untuk tiap set, jadi menghapus baris tidak boleh menghilangkan set.
     * Fabric: baris terakhir pada satu set hanya dikosongkan shell dan part-nya. Baris set yang sudah kosong baru bisa hilang
     * bersama seluruh content (whole_content=1), setelah user mengonfirmasi.
     */
    private function destroySetRow(Request $request, int $id)
    {
        $db = $this->db();
        $table = $this->detailTable($request->category);
        $isMaterial = $table === 'costing_materials';
        $row = self::activeRows($db->table($table)->where('id', $id)->where('costings_id', $request->id_costing))->first();
        if (!$row) {
            return response()->json(['status' => 404, 'message' => 'Data detail tidak ditemukan.']);
        }
        if ($table === 'costing_charges' && (int) $row->contents_id === $this->nds()->cmtContentId()) {
            return response()->json(['status' => 422, 'message' => 'CMT wajib ada, tidak bisa dihapus.']);
        }
        $siblings = self::activeRows($db->table($table)->where('costings_id', $row->costings_id)->where('contents_id', $row->contents_id));
        $inSet = (clone $siblings)->where('costing_set_id', $row->costing_set_id)->count();
        $otherSets = (clone $siblings)->where('costing_set_id', '!=', $row->costing_set_id)->count();
        $partIds = fn () => self::activeRows($db->table('costing_material_parts')->where('costing_materials_id', $row->id));
        $wholeContent = $request->boolean('whole_content');

        if ($request->category === 'Fabric' && $inSet === 1 && !$wholeContent && ($row->shells_id || $partIds()->exists())) {
            $db->transaction(function ($db) use ($row, $partIds, $request) {
                $partIds()->update(['cancel' => 'Y', 'updated_at' => now()]);
                $db->table('costing_materials')->where('id', $row->id)->update(['shells_id' => null, 'updated_at' => now()]);
                $this->history()->costingItemsChanged((int) $request->id_costing, 'items_updated', 'Fabric');
            });

            return response()->json(['status' => 200, 'kept' => true, 'message' => 'Shell dihapus, set tetap ada.']);
        }
        if ($inSet === 1 && !$wholeContent && $otherSets > 0) {
            return response()->json(['status' => 409, 'confirm_whole' => true, 'message' => 'Set tidak bisa dihapus sendiri. Hapus content ini dari semua set?']);
        }

        $db->transaction(function ($db) use ($row, $inSet, $wholeContent, $siblings, $request, $table) {
            $ids = $wholeContent || $inSet === 1 ? (clone $siblings)->pluck('id') : collect([$row->id]);
            $this->removeDetailRows($db, $table, $ids);
            $this->history()->costingItemsChanged((int) $request->id_costing, 'items_deleted', (string) $request->category);
        });

        return response()->json(['status' => 200, 'reload' => $wholeContent, 'message' => 'Data berhasil dihapus!']);
    }

    public function getDetailRow(Request $request, $id)
    {
        $request->validate([
            'category' => ['required', 'in:Fabric,Accessories Sewing,Accessories Packing,Manufacturing,Other Cost'],
            'id_costing' => ['required', 'exists:mysql_mkt.costings,id'],
        ]);
        $costing = $this->db()->table('costings')->where('id', $request->id_costing)->first(['rate_usd_idr']);
        $row = $this->getCostingDetail($request->id_costing, $costing->rate_usd_idr)[$request->category]->firstWhere('id', (int) $id);
        if ($row) {
            return response()->json(['status' => 200, 'data' => $row]);
        }

        return response()->json(['status' => 404, 'message' => 'Data tidak ditemukan']);
    }

    public function updateDetail(Request $request)
    {
        $request->validate($this->detailRules() + ['id_detail' => ['required', 'integer']]);
        if ($locked = $this->costingLockedResponse($request, $request->id_costing)) {
            return $locked;
        }
        $table = $this->detailTable($request->category);
        $detail = self::activeRows($this->db()->table($table)->where('id', $request->id_detail)->where('costings_id', $request->id_costing))->first();
        if (!$detail) {
            return response()->json(['status' => 404, 'message' => 'Data detail tidak ditemukan.']);
        }
        $cmtId = $this->nds()->cmtContentId();
        if ($table === 'costing_charges' && (int) $detail->contents_id !== (int) $request->item && in_array($cmtId, [(int) $detail->contents_id, (int) $request->item], true)) {
            return response()->json(['status' => 422, 'message' => 'Baris CMT tidak bisa diganti ke item lain, dan item lain tidak bisa diganti ke CMT.']);
        }
        $this->assertContent($request->category, $request->item);
        $values = $this->detailValues($request->id_costing, $request->category, $request->only(['item', 'desc', 'supplier', 'curr', 'price', 'cons', 'unit', 'price_px_idr', 'price_px_usd', 'allowance', 'set', 'value_idr', 'value_usd']));

        // Histori hanya kalau ada nilai yang benar-benar berubah (Save tabel bisa mengirim ulang baris yang sama).
        $changed = collect($values)->except('updated_at')->contains(function ($value, $column) use ($detail) {
            $old = $detail->{$column} ?? null;
            if (is_numeric($old) && is_numeric($value)) {
                return round((float) $old, 7) !== round((float) $value, 7);
            }

            return trim((string) $old) !== trim((string) $value);
        });

        $this->db()->transaction(function ($db) use ($table, $request, $values, $changed) {
            $db->table($table)->where('id', $request->id_detail)->where('costings_id', $request->id_costing)->update($values);
            if ($changed) {
                $this->history()->costingItemsChanged((int) $request->id_costing, 'items_updated', (string) $request->category);
            }
            if ($table === 'costing_charges') {
                $this->nds()->syncCmPrice((int) $request->id_costing);
            }
        });

        return response()->json(['status' => 200, 'message' => 'Success']);
    }

    public function costingItems($id)
    {
        $costing = $this->db()->table('costings')->where('id', $id)->first(['id', 'costing_no', 'rate_usd_idr']);
        if (!$costing) {
            return response()->json(['status' => 404, 'message' => 'Data Costing tidak ditemukan.'], 404);
        }

        $items = [];
        foreach ($this->getCostingDetail($costing->id, $costing->rate_usd_idr) as $category => $rows) {
            $items[$category] = $rows->map(fn ($row) => [
                'item' => $row->nama_item,
                'desc' => $row->item_desc,
                'set' => $row->nama_set ?? '',
                'supplier' => $row->nama_supplier ?? '',
                'cons' => $row->cons,
                'curr' => $row->curr ?? '',
                'price' => $row->price,
                'unit' => $row->unit ?? '',
            ])->values();
        }

        return response()->json(['status' => 200, 'cost_no' => $costing->costing_no, 'items' => $items]);
    }

    protected function getCostingPdfData($id)
    {
        $costing = $this->costingForView((int) $id);
        if (!$costing) {
            return null;
        }
        $costing->nama_dest = $this->db()->table('master_destinations')->where('id', $costing->main_destination_id)->value('destination_name') ?: '-';

        return [
            'costing' => $costing,
            'doc' => $this->costingDocument($costing),
        ];
    }

    protected function pdfView(): string
    {
        return 'marketing.costing.pdf_nds';
    }

    /** Footer tiap halaman: "No Costing Rev n" di kiri, "Halaman X dari Y" di kanan (batch: hanya nomor halaman). */
    protected function decoratePdf($pdf, ?array $pdfData): void
    {
        $pdf->render();
        $dompdf = $pdf->getDomPDF();
        $canvas = $dompdf->getCanvas();
        $font = $dompdf->getFontMetrics()->getFont('Helvetica');
        $size = 6.5;
        $y = $canvas->get_height() - 22;
        if ($pdfData) {
            $canvas->page_text(26, $y, $pdfData['doc']['no'] . ' Rev ' . $pdfData['doc']['revision'], $font, $size, [0.2, 0.2, 0.2]);
        }
        $label = 'Halaman {PAGE_NUM} dari {PAGE_COUNT}';
        $width = $dompdf->getFontMetrics()->getTextWidth('Halaman 00 dari 00', $font, $size);
        $canvas->page_text($canvas->get_width() - 26 - $width, $y, $label, $font, $size, [0.2, 0.2, 0.2]);
    }

    /**
     * Isi PDF & Excel Costing (acuan: pdf costing.pdf / excel costing.xlsx).
     * Angka dihitung dengan rumus yang sama dengan rumus di Excel, supaya PDF dan Excel selalu sama:
     *  PRICE IDR = PRICE x Rate USD to IDR bila USD; PRICE USD & VALUE USD material/manufacturing = / Rate IDR to USD,
     *  other cost = / Rate USD to IDR; VALUE IDR = PRICE IDR x CONS x (1 + ALLOW%); % = VALUE IDR / TOTAL COST;
     *  QTY BOM = ROUNDUP(CONS x Planned Qty x (1 + ALLOW%)); VALUE = QTY BOM x PRICE IDR.
     */
    private function costingDocument(object $costing): array
    {
        $db = $this->db();
        $usdIdr = (float) $costing->rate_usd_idr ?: 1;
        $idrUsd = (float) $costing->rate_idr_usd ?: 1;
        $qty = (float) $costing->planned_qty;
        $details = $this->getCostingDetail($costing->id, $costing->rate);
        $dash = fn ($v) => ($v === null || trim((string) $v) === '') ? '-' : mb_strtoupper(trim((string) $v));

        $row = function ($d, bool $charge, string $group) use ($usdIdr, $idrUsd, $qty, $costing, $dash) {
            $curr = mb_strtoupper((string) ($d->currency_code ?: 'IDR'));
            $cons = (float) $d->cons;
            $allow = (float) $d->allowance_percent;
            $price = (float) $d->price;
            // Other cost berbasis persen menyimpan hasilnya di value_idr: harga diturunkan dari situ agar rumus Excel tetap sama.
            if ($charge && $price == 0.0 && (float) $d->value_idr != 0.0 && $cons != 0.0) {
                $priceIdr = (float) $d->value_idr / ($cons * (1 + $allow / 100));
                $price = $curr === 'USD' ? $priceIdr / $usdIdr : $priceIdr;
            }
            $priceIdr = $curr === 'USD' ? $price * $usdIdr : $price;
            $usdRate = $group === 'OTHER' ? $usdIdr : $idrUsd;
            $valueIdr = $priceIdr * $cons * (1 + $allow / 100);
            $qtyBom = $group === 'OTHER' ? null : (int) ceil(round($cons * $qty * (1 + $allow / 100), 6));
            $calc = null;
            if ($charge) {
                $type = mb_strtoupper((string) ($d->calc_type ?? ''));
                if ($type === '') {
                    $type = (float) ($d->percent ?? 0) != 0.0 ? 'PERCENT' : (mb_strtoupper((string) $d->content_name) === 'CMT' ? 'SMV' : 'FIXED');
                }
                $calc = $type === 'PERCENT' ? 'PERCENT ' . rtrim(rtrim(number_format((float) ($d->percent ?? 0), 2, '.', ''), '0'), '.') . '%'
                    : ($type === 'SMV' ? 'SMV ' . number_format((float) (($d->smv ?? 0) ?: $costing->smv), 2, '.', '') : $type);
            }

            return [
                'content' => $dash($d->content_name),
                'shell' => $dash($d->shell_name ?? null),
                'part' => $dash($d->part_name ?? null),
                'calc' => $calc,
                'subcont' => $charge ? (mb_strtoupper((string) ($d->is_subcont ?? '')) === 'Y' || (string) ($d->is_subcont ?? '') === '1' ? 'Y' : 'N') : null,
                'set' => $costing->type === 'multiple' ? $dash($d->nama_set ?? null) : '-',
                'desc' => $dash($d->description ?? null),
                'supplier' => $dash($d->nama_supplier ?? null),
                'curr' => $curr,
                'price' => $price,
                'price_idr' => $priceIdr,
                'price_usd' => $priceIdr / $usdRate,
                'cons' => $cons,
                'unit' => $dash($d->unit_code ?? null),
                'allow' => $allow,
                'value_idr' => $valueIdr,
                'value_usd' => $valueIdr / $usdRate,
                'qty_bom' => $qtyBom,
                'value' => $qtyBom === null ? null : $qtyBom * $priceIdr,
                'usd_rate' => $group === 'OTHER' ? 'usd_idr' : 'idr_usd',
            ];
        };
        $group = function (string $label, $rows, bool $charge) use ($row) {
            $rows = collect($rows)->map(fn ($d) => $row($d, $charge, $label))->values()->all();
            $other = $label === 'OTHER';

            return [
                'label' => $label,
                'rows' => $rows,
                'value_idr' => array_sum(array_column($rows, 'value_idr')),
                'value_usd' => array_sum(array_column($rows, 'value_usd')),
                'qty_bom' => $other ? null : array_sum(array_column($rows, 'qty_bom')),
                'value' => $other ? null : array_sum(array_column($rows, 'value')),
            ];
        };

        $material = [
            $group('FABRIC', $details['Fabric'], false),
            $group('ACC SEWING', $details['Accessories Sewing'], false),
            $group('ACC PACKING', $details['Accessories Packing'], false),
        ];
        $manufacturing = $group('MANUFACTURING', $details['Manufacturing'], true);
        $other = $group('OTHER', $details['Other Cost'], true);

        $matIdr = array_sum(array_column($material, 'value_idr'));
        $sumIdr = $matIdr + $manufacturing['value_idr'] + $other['value_idr'];
        $gaPct = (float) $costing->ga_percent;
        $gaExtraPct = (float) $costing->ga_extra_percent;
        $ga = $sumIdr * $gaPct / 100;
        $gaExtra = $sumIdr * $gaExtraPct / 100;
        $totalCost = $sumIdr + $ga + $gaExtra;
        $vatPct = (float) $costing->vat_percent;
        $profitPct = $costing->profit_percent === null ? 6.0 : (float) $costing->profit_percent;
        $costVat = $totalCost * (1 + $vatPct / 100);
        $costVatProfit = $costVat * (1 + $profitPct / 100);
        $pct = fn ($v) => $totalCost == 0.0 ? 0 : $v / $totalCost;

        foreach ([&$material[0], &$material[1], &$material[2], &$manufacturing, &$other] as &$g) {
            foreach ($g['rows'] as &$r) {
                $r['pct'] = $pct($r['value_idr']);
            }
            unset($r);
            $g['pct'] = $pct($g['value_idr']);
        }
        unset($g);

        $creator = $db->table('process_logs as l')
            ->leftJoin('users as u', 'u.id', '=', 'l.action_by')
            ->where('l.costings_id', $costing->id)->where('l.doc_type', 'costing')->where('l.action', 'created')
            ->orderBy('l.id')
            ->first(['u.username', 'l.action_at']);
        $images = $db->table('costing_images')->where('costings_id', $costing->id)
            ->where(fn ($q) => $q->whereNull('cancel')->orWhere('cancel', 'N'))
            ->orderBy('id')->get(['file_path', 'image_type'])
            ->map(fn ($image) => [
                'path' => public_path('uploads/costing/' . basename($image->file_path)),
                'name' => basename($image->file_path),
                'type' => mb_strtoupper((string) ($image->image_type ?: 'PHOTO')),
            ])
            ->filter(fn ($image) => is_file($image['path']))
            ->values()->all();
        $status = (string) $costing->status;

        return [
            'no' => $costing->costing_no,
            'revision' => (int) $costing->revision_no,
            'date' => $costing->costing_date ? date('Y-m-d', strtotime($costing->costing_date)) : '-',
            'status' => $status === '' || strtolower($status) === 'awaiting' ? 'AWAITING APPROVAL' : mb_strtoupper($status),
            'buyer' => $dash($costing->nama_buyer),
            'brand' => $dash($costing->brand_name),
            'season' => $dash($costing->season_name),
            'style' => $dash($costing->style),
            'product_group' => $dash($costing->product_group),
            'product_item' => $dash($costing->product_item),
            'type' => mb_strtoupper($costing->type),
            'unit' => $dash($costing->unit_code),
            'destination' => $costing->nama_dest && $costing->nama_dest !== '-' ? mb_strtoupper($costing->nama_dest) : '-',
            'office' => $dash($costing->office_name),
            'qty' => $qty,
            'smv' => (float) $costing->smv,
            'currency' => $dash($costing->currency_code),
            'rate_usd_idr' => $usdIdr,
            'rate_idr_usd' => $idrUsd,
            'vat' => $vatPct,
            'notes' => trim((string) $costing->notes),
            'creator' => $creator->username ?? '-',
            'created_at' => ($creator->action_at ?? $costing->created_at) ? date('d/m/Y H:i', strtotime($creator->action_at ?? $costing->created_at)) : '',
            'material' => $material,
            'material_total' => [
                'value_idr' => $matIdr,
                'value_usd' => array_sum(array_column($material, 'value_usd')),
                'pct' => $pct($matIdr),
                'qty_bom' => array_sum(array_column($material, 'qty_bom')),
                'value' => array_sum(array_column($material, 'value')),
            ],
            'manufacturing' => $manufacturing,
            'other' => $other,
            'grand' => [
                'qty_bom' => array_sum(array_column($material, 'qty_bom')) + $manufacturing['qty_bom'],
                'value' => array_sum(array_column($material, 'value')) + $manufacturing['value'],
            ],
            'summary' => [
                'material' => [$matIdr, array_sum(array_column($material, 'value_usd'))],
                'manufacturing' => [$manufacturing['value_idr'], $manufacturing['value_usd']],
                'other' => [$other['value_idr'], $other['value_usd']],
                'ga' => [$gaPct, $ga, $ga / $usdIdr],
                'ga_extra' => [$gaExtraPct, $gaExtra, $gaExtra / $usdIdr],
                'total' => [$totalCost, $totalCost / $usdIdr],
                'deal' => (float) $costing->deal_allowance_percent,
                'vat' => [$vatPct, $costVat, $costVat / $usdIdr],
                'profit' => [$profitPct, $costVatProfit, $costVatProfit / $usdIdr],
            ],
            'images' => $images,
            'header_image' => $images[0] ?? null,
        ];
    }

    public function printExcel($id)
    {
        $data = $this->getCostingPdfData($id);
        if (!$data) {
            return redirect()->route('master-costing')->with('error', 'Data Costing tidak ditemukan.');
        }

        return app(\App\Services\MarketingNdsCostingExcel::class)->download($data['doc']);
    }

    protected function batchCostingRule(): string
    {
        return 'exists:mysql_mkt.costings,id';
    }

    private function pictures(int $costingId)
    {
        return self::activeRows($this->db()->table('costing_images')->where('costings_id', $costingId))->orderBy('id')->get()
            ->map(fn ($image) => (object) ['file_name' => basename($image->file_path), 'original_name' => basename($image->file_path)]);
    }

    private function savePictures(Request $request, int $costingId): void
    {
        $files = array_filter((array) $request->file('upload_foto', []));
        if (!$files) {
            return;
        }
        $directory = public_path('uploads/costing');
        if (!is_dir($directory) && !mkdir($directory, 0755, true) && !is_dir($directory)) {
            throw new \RuntimeException('Unable to create the costing image directory.');
        }
        foreach ($files as $file) {
            $filename = Str::uuid() . '.' . $file->extension();
            $file->move($directory, $filename);
            $this->db()->table('costing_images')->insert([
                'costings_id' => $costingId,
                'file_path' => 'uploads/costing/' . $filename,
                'cancel' => 'N',
                'created_at' => now(),
                'updated_at' => now(),
            ] + MarketingNdsCostingService::creator('costing_images'));
        }
    }

    /**
     * Menghapus gambar Costing = cancel 'Y' (tanpa hard delete); file tetap disimpan sebagai histori.
     * Tanpa $fileNames berarti semua.
     */
    private function removePictures(int $costingId, ?array $fileNames = null): void
    {
        $images = self::activeRows($this->db()->table('costing_images')->where('costings_id', $costingId))->get();
        if ($fileNames !== null) {
            $images = $images->filter(fn ($image) => in_array(basename($image->file_path), $fileNames, true));
        }
        if ($images->isNotEmpty()) {
            $this->db()->table('costing_images')->whereIn('id', $images->pluck('id'))->update(['cancel' => 'Y', 'updated_at' => now()]);
        }
    }

    // Belum tersedia di DB baru.
    private function notAvailable(string $feature)
    {
        return redirect()->route('master-costing')->with('warning', "{$feature} belum tersedia di DB baru.");
    }

    // Approval Costing DB baru ada di Approval Center (Marketing_ApprovalCenterController), bukan halaman approval lama.
    public function approval(Request $request)
    {
        return redirect()->to(route('marketing-approval-center') . '#ac-doc-costing');
    }

    public function submitApproval(Request $request, $id)
    {
        return redirect()->to(route('marketing-approval-center') . '#ac-doc-costing');
    }

    public function cancelUnlockRequest($id)
    {
        return $this->lockAction(fn () => $this->approvals()->cancelUnlockRequest('costing', (int) $id), 'Request unlock dibatalkan.');
    }

    public function cancelEditing($id)
    {
        return $this->lockAction(fn () => $this->approvals()->relockCosting((int) $id, false), 'Edit dibatalkan, Costing dikunci lagi.');
    }

    private function lockAction(callable $action, string $message)
    {
        try {
            $action();
        } catch (\RuntimeException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }

        return redirect()->back()->with('success', $message);
    }

    private function approvals(): MarketingNdsApprovalService
    {
        return app(MarketingNdsApprovalService::class);
    }

    /**
     * Costing Approved terkunci (unlocked: hanya peminta). Boleh diubah -> Return / Cancel kembali Awaiting (markEdited).
     * Balasan JSON HTTP 200 + status 423 karena front-end Costing membaca res.status di callback success.
     */
    private function costingLockedResponse(Request $request, $costingId)
    {
        $message = $this->approvals()->lockedMessage('costing', (int) $costingId);
        if ($message === null) {
            $this->approvals()->markEdited('costing', (int) $costingId);

            return null;
        }
        if ($request->ajax() || $request->expectsJson()) {
            return response()->json(['status' => 423, 'message' => $message]);
        }

        return redirect()->route('master-costing')->with('warning', $message);
    }
}
