<?php

namespace App\Services\Marketing;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use RuntimeException;

/**
 * BOM di DB baru (koneksi mysql_mkt), mengikuti ERD: 1 BOM = 1 WS (so_line).
 * bom_materials = 1 baris per material Costing milik set WS itu (item, destinasi, cons, unit, allowance, supplier, price).
 * bom_charges = 1 baris per charge Costing group Manufacturing dan Other Cost (item, supplier, cons, unit, price).
 * bom_material_details = cons (dan item khusus) per material: 1 baris semua color/size untuk All Color All Size,
 * atau 1 baris per color dan/atau range size sesuai rule_bom.
 */
class MarketingNdsBomService
{
    private const CONNECTION = 'mysql_mkt';
    // Tab material sama dengan kategori Costing: dicocokkan dari nama group content.
    private const TABS = ['FAB' => 'FABRIC', 'SEW' => 'SEWING', 'PACK' => 'PACKING'];
    /** Tab BOM untuk content charge: group M = Manufacturing, lainnya (OC) = Other Cost. */
    private const CHARGE_TAB_SQL = "CASE g.group_code WHEN 'M' THEN 'MFG' ELSE 'OTH' END as tab";
    private const SET_ORDER_SQL = "CASE UPPER(TRIM(pi.product_set)) WHEN 'TOP' THEN 1 WHEN 'BOTTOM' THEN 2 ELSE 3 END";

    private function db()
    {
        return DB::connection(self::CONNECTION);
    }

    private static function active($query, string $column = 'cancel')
    {
        return $query->where(fn ($q) => $q->whereNull($column)->orWhere($column, '!=', 'Y'));
    }

    /** bom_materials.items_id dari ERD terbaru. Tanpa kolom ini halaman tetap tampil tapi belum bisa Save. */
    public function schemaReady(): bool
    {
        return Schema::connection(self::CONNECTION)->hasColumn('bom_materials', 'items_id');
    }

    /** Pilihan Rule BOM, disimpan sebagai teks (sama dengan BOM lama). */
    /** 4 rule pertama = semua destinasi; "... Per Destination" = rincian juga dipecah per destinasi SO (destinations_id di rincian). */
    public const RULES = ['All Color All Size', 'All Color Range Size', 'Per Color All Size', 'Per Color Range Size',
        'All Color All Size Per Destination', 'All Color Range Size Per Destination', 'Per Color All Size Per Destination', 'Per Color Range Size Per Destination'];

    /** bom_materials.rule_bom: kolom tambahan, halaman tetap jalan tanpa kolom ini (Rule BOM terkunci). */
    public function ruleReady(): bool
    {
        return Schema::connection(self::CONNECTION)->hasColumn('bom_materials', 'rule_bom');
    }

    /** bom_material_details: tempat cons per color/size. Tanpa tabel ini cons masih disimpan di bom_materials.cons. */
    public function detailReady(): bool
    {
        return Schema::connection(self::CONNECTION)->hasTable('bom_material_details');
    }

    /** Kolom isian bom_material_details: item & cons, plus destinasi/unit/loss/supplier/price kalau kolomnya sudah ada. */
    private function detailColumns(): array
    {
        $full = Schema::connection(self::CONNECTION)->hasColumn('bom_material_details', 'units_id');

        return $full
            ? ['master_items_id', 'destinations_id', 'cons', 'units_id', 'allowance_percent', 'supplier_id', 'price']
            : ['master_items_id', 'cons'];
    }

    /** WS milik SO, TOP dulu lalu BOTTOM. */
    public function lines(int $soId)
    {
        return $this->db()->table('so_line as l')
            ->leftJoin('costing_set as cs', 'cs.id', '=', 'l.costing_set_id')
            ->leftJoin('master_product_items as pi', 'pi.id', '=', 'cs.product_items_id')
            ->where('l.sales_order_id', $soId)
            ->where(fn ($q) => $q->whereNull('l.cancel')->orWhere('l.cancel', '!=', 'Y'))
            ->orderByRaw(self::SET_ORDER_SQL)
            ->orderBy('l.id')
            ->get(['l.id', 'l.no_ws', 'l.costing_set_id', 'pi.product_set', 'pi.product_item_name']);
    }

    /**
     * Perbedaan BOM tersimpan dengan Costing & SO saat ini (bukan perbandingan tanggal, supaya tidak alarm palsu).
     * changes = struktur BOM sudah tidak cocok, perlu dibuka & di-Save ulang:
     *   material / charge baru atau sudah dihapus di Costing, color / size / destinasi SO bertambah atau hilang
     *   untuk material yang dipecah per color / size.
     * info = cukup diketahui: cons / supplier material di Costing diubah setelah BOM terakhir disimpan.
     * Qty SO dan price Costing tidak dihitung: BOM tidak menyimpan qty, dan price costing selalu dibaca langsung.
     */
    public function staleness(object $line): array
    {
        $result = ['changes' => [], 'info' => []];
        $bom = $this->activeBom((int) $line->id);
        if (!$bom) {
            return $result;
        }
        $db = $this->db();
        $costing = (object) ['id' => (int) $db->table('sales_orders')->where('id', $line->sales_order_id)->value('costing_id')];
        $tabNames = ['FAB' => 'Fabric', 'SEW' => 'Accessories Sewing', 'PACK' => 'Accessories Packing', 'MFG' => 'Manufacturing', 'OTH' => 'Other Cost'];
        $tabOf = fn ($group) => collect(self::TABS)->search(fn ($keyword) => str_contains(strtoupper((string) $group), $keyword));
        $name = fn ($r) => trim($r->content_name . ($r->shell_name ?? '' ? ' (' . $r->shell_name . ')' : ''));
        $list = function ($items) {
            $items = collect($items)->unique()->values();

            return $items->take(3)->implode(', ') . ($items->count() > 3 ? ' dan ' . ($items->count() - 3) . ' lainnya' : '');
        };
        $byTab = fn ($rows) => collect($rows)->groupBy('tab')->map(fn ($g, $tab) => ($tabNames[$tab] ?? $tab) . ': ' . $list($g->map($name)))->implode('; ');

        // Material & charge Costing saat ini vs yang tersimpan di BOM.
        $materials = $this->costingMaterials($costing, $line)
            ->leftJoin('master_contents as m', 'm.id', '=', 'd.contents_id')
            ->leftJoin('master_group as g', 'g.id', '=', 'm.group_id')
            ->leftJoin('master_shells as sh', 'sh.id', '=', 'd.shells_id')
            ->get(['d.id', 'd.cons', 'd.supplier_id', 'd.updated_at', 'm.content_name', 'g.group_name', 'sh.shell_name'])
            ->map(fn ($r) => tap($r, fn ($r) => $r->tab = $tabOf($r->group_name)))->filter(fn ($r) => $r->tab)->keyBy('id');
        $savedM = self::active($db->table('bom_materials as b')->where('b.bom_id', $bom->id), 'b.cancel')
            ->leftJoin('costing_materials as d', 'd.id', '=', 'b.costing_materials_id')
            ->leftJoin('master_contents as m', 'm.id', '=', 'd.contents_id')
            ->leftJoin('master_group as g', 'g.id', '=', 'm.group_id')
            ->leftJoin('master_shells as sh', 'sh.id', '=', 'd.shells_id')
            ->get(['b.id', 'b.costing_materials_id', 'b.rule_bom', 'b.destinations_id', 'm.content_name', 'g.group_name', 'sh.shell_name'])
            ->map(fn ($r) => tap($r, fn ($r) => $r->tab = $tabOf($r->group_name) ?: 'FAB'))->keyBy('costing_materials_id');
        $charges = $this->costingCharges($costing, $line)->get(['d.id', 'd.updated_at', 'm.content_name', DB::raw(self::CHARGE_TAB_SQL)])->keyBy('id');
        $savedC = self::active($db->table('bom_charges as b')->where('b.bom_id', $bom->id), 'b.cancel')
            ->leftJoin('costing_charges as d', 'd.id', '=', 'b.costing_charges_id')
            ->leftJoin('master_contents as m', 'm.id', '=', 'd.contents_id')
            ->leftJoin('master_group as g', 'g.id', '=', 'm.group_id')
            ->get(['b.costing_charges_id', 'm.content_name', DB::raw(self::CHARGE_TAB_SQL)])->keyBy('costing_charges_id');

        $added = $materials->diffKeys($savedM)->merge($charges->diffKeys($savedC));
        if ($added->isNotEmpty()) {
            $result['changes'][] = $added->count() . ' material/charge baru di Costing belum ada di BOM (' . $byTab($added) . ').';
        }
        $removed = $savedM->diffKeys($materials)->merge($savedC->diffKeys($charges));
        if ($removed->isNotEmpty()) {
            $result['changes'][] = $removed->count() . ' material/charge sudah dihapus dari Costing tapi masih ada di BOM (' . $byTab($removed) . ').';
        }

        // Rincian color/size/destinasi vs Breakdown Order SO saat ini.
        $breakdown = $this->breakdown((int) $line->id);
        $colorIds = array_column($breakdown['colors'], 'id');
        $sizeIds = array_column($breakdown['sizes'], 'id');
        $destIds = array_column($breakdown['dests'], 'id');
        $kept = $savedM->intersectByKeys($materials);
        $details = $kept->isNotEmpty() && $this->detailReady()
            ? self::active($db->table('bom_material_details')->whereIn('bom_materials_id', $kept->pluck('id')))->get()->groupBy('bom_materials_id')
            : collect();
        $missing = [];
        $orphan = [];
        foreach ($kept as $row) {
            $rule = in_array($row->rule_bom, self::RULES, true) ? $row->rule_bom : self::RULES[0];
            $own = $details[$row->id] ?? collect();
            $want = [];
            $perDest = str_ends_with($rule, 'Per Destination');
            // Sama dengan halaman: rincian hanya untuk kombinasi yang ada qty-nya di SO.
            $scopeDests = $row->destinations_id && !$perDest ? [(int) $row->destinations_id] : $destIds;
            $hasQty = function ($dest, $color, $size) use ($breakdown, $scopeDests, $colorIds, $sizeIds) {
                foreach ($dest !== '' ? [$dest] : $scopeDests as $d) {
                    foreach ($color !== null ? [$color] : $colorIds as $c) {
                        foreach ($sizeIds as $i => $z) {
                            if (($size === null || $z === $size) && (($breakdown['qty'][$d][$c][$i] ?? 0) > 0)) {
                                return true;
                            }
                        }
                    }
                }
                return false;
            };
            foreach ($perDest ? $destIds : [''] as $dest) {
                foreach (str_starts_with($rule, 'Per Color') ? $colorIds : [null] as $color) {
                    foreach (str_contains($rule, 'Range Size') ? $sizeIds : [null] as $size) {
                        if ($hasQty($dest, $color, $size)) {
                            $want[] = [(string) $dest, (string) $color, $size];
                        }
                    }
                }
            }
            $sizePos = array_flip(array_map('intval', $sizeIds));
            $covered = function (array $w) use ($own, $perDest, $sizePos) {
                [$dest, $color, $size] = $w;
                foreach ($own as $d) {
                    if (($perDest ? (string) ($d->destinations_id ?? '') : '') !== $dest || (string) ($d->color_id ?? '') !== $color) {
                        continue;
                    }
                    if ($size === null) {
                        return true;
                    }
                    $from = $sizePos[(int) $d->size_from_id] ?? null;
                    $to = $sizePos[(int) ($d->size_to_id ?: $d->size_from_id)] ?? null;
                    $at = $sizePos[(int) $size] ?? null;
                    if ($from !== null && $to !== null && $at !== null && $at >= min($from, $to) && $at <= max($from, $to)) {
                        return true;
                    }
                }

                return false;
            };
            if ($this->detailReady() && collect($want)->contains(fn ($w) => !$covered($w))) {
                $missing[] = $row;
            }
            $badDest = ($row->destinations_id && !in_array((int) $row->destinations_id, $destIds, true))
                || $own->contains(fn ($d) => ($d->destinations_id ?? null) && !in_array((int) $d->destinations_id, $destIds, true));
            $gone = fn ($v, $ids) => $v && !in_array((int) $v, $ids, true);
            $badColorSize = $own->contains(fn ($d) => $gone($d->color_id, $colorIds) || $gone($d->size_from_id, $sizeIds) || $gone($d->size_to_id, $sizeIds));
            if ($badColorSize || $badDest) {
                $orphan[] = $row;
            }
        }
        if ($missing) {
            $result['changes'][] = count($missing) . ' material belum punya rincian untuk color/size di Sales Order saat ini (' . $byTab($missing) . ').';
        }
        if ($orphan) {
            $result['changes'][] = count($orphan) . ' material punya rincian color/size/destinasi yang sudah tidak ada di Sales Order (' . $byTab($orphan) . ').';
        }

        // Costing (cons / supplier / price / CMT) atau qty / PO Sales Order diubah setelah BOM terakhir disimpan: BOM perlu dicek & di-Save ulang.
        // Status approval BOM tidak diubah; cukup peringatan merah (keputusan tim).
        $since = $bom->updated_at ?? $bom->created_at;
        $edited = $since ? $materials->intersectByKeys($savedM)->merge($charges->intersectByKeys($savedC))->filter(fn ($r) => $r->updated_at && $r->updated_at > $since) : collect();
        if ($edited->isNotEmpty()) {
            $result['changes'][] = 'Cons / supplier / harga di Costing diubah setelah BOM disimpan (' . $byTab($edited) . ').';
        }
        // Bandingkan ISI qty SO (color x size x destinasi) saat BOM disimpan dengan sekarang. Save SO menulis ulang baris walau qty sama,
        // jadi waktu ubah saja tidak cukup (alarm palsu). Baris yang aktif saat itu = dibuat sebelum $since dan belum di-cancel saat itu.
        $soEdited = $since && $this->soQtyAt((int) $line->id, $since) !== $this->soQtyAt((int) $line->id, null);
        if ($soEdited) {
            $result['changes'][] = 'Qty / PO / destinasi Sales Order WS ini diubah setelah BOM disimpan. Cek ulang qty material di BOM.';
        }

        return $result;
    }

    /**
     * Qty PO SO per color|size|destinasi untuk WS ini: sekarang ($at = null) atau pada waktu $at
     * (baris yang sudah ada saat itu dan belum di-cancel saat itu; cancel = 'Y' mencatat waktunya di updated_at).
     */
    private function soQtyAt(int $lineId, $at): array
    {
        $alive = function ($q, string $a) use ($at) {
            if ($at === null) {
                return $q->where(fn ($w) => $w->whereNull("{$a}.cancel")->orWhere("{$a}.cancel", '!=', 'Y'));
            }

            return $q->where("{$a}.created_at", '<=', $at)
                ->where(fn ($w) => $w->whereNull("{$a}.cancel")->orWhere("{$a}.cancel", '!=', 'Y')->orWhere("{$a}.updated_at", '>', $at));
        };
        $q = $this->db()->table('so_production as p')->join('so_po as po', 'po.so_production_id', '=', 'p.id')->where('p.so_line_id', $lineId);
        $alive($q, 'p');
        $alive($q, 'po');
        $rows = $q->groupBy('p.color_id', 'p.sizes_id', 'po.destination_id')
            ->selectRaw("CONCAT_WS('|', p.color_id, p.sizes_id, COALESCE(po.destination_id, '')) as k, SUM(po.qty) as qty")
            ->pluck('qty', 'k')->map(fn ($v) => (int) $v)->filter()->all();
        ksort($rows);

        return $rows;
    }

    /** BOM WS ini perlu diperbarui (dipakai juga untuk menahan tahap berikutnya, mis. Worksheet). */
    public function needsUpdate(object $line): bool
    {
        return (bool) $this->staleness($line)['changes'];
    }

    /** Jumlah WS di SO yang BOM-nya perlu diperbarui (tanda di listing Costing). */
    /**
     * Rincian BOM yang belum sesuai per WS di SO ini (titik merah tombol BOM di listing):
     * [['no_ws' => .., 'changes' => [..]]]. Hanya WS yang sudah punya BOM; hilang sendiri setelah BOM disesuaikan.
     */
    public function staleDetails(int $soId): array
    {
        return $this->db()->table('so_line')->where('sales_order_id', $soId)
            ->where(fn ($q) => $q->whereNull('cancel')->orWhere('cancel', '!=', 'Y'))
            ->orderBy('id')->get()
            ->map(fn ($line) => ['no_ws' => $line->no_ws, 'changes' => $this->staleness($line)['changes']])
            ->filter(fn ($row) => $row['changes'])->values()->all();
    }

    public function staleLines(int $soId): int
    {
        return $this->db()->table('so_line')->where('sales_order_id', $soId)
            ->where(fn ($q) => $q->whereNull('cancel')->orWhere('cancel', '!=', 'Y'))
            ->get()->filter(fn ($line) => $this->needsUpdate($line))->count();
    }

    public function activeBom(int $lineId): ?object
    {
        return self::active($this->db()->table('bom')->where('so_line_id', $lineId))->orderByDesc('id')->first();
    }

    /**
     * Semua data halaman Input BOM untuk satu WS.
     * $withMasters false: tanpa master item/unit/supplier (±5 MB) untuk respons Save & pindah WS; browser memakai master yang sudah dimuat.
     */
    public function page(object $line, bool $withMasters = true): array
    {
        $db = $this->db();
        $so = $db->table('sales_orders')->where('id', $line->sales_order_id)->first();
        $costing = app(MarketingSalesOrderService::class)->costing((int) $so->costing_id);
        $set = $costing->sets->firstWhere('id', $line->costing_set_id);
        $breakdown = $this->breakdown((int) $line->id);
        $bom = $this->activeBom((int) $line->id);
        $poRows = self::active($db->table('so_po as p')->join('so_production as sp', 'sp.id', '=', 'p.so_production_id'), 'p.cancel')
            ->leftJoin('master_ship_modes as sm', 'sm.id', '=', 'p.ship_modes_id')
            ->where('sp.so_line_id', $line->id)
            ->get(['p.delivery_date', 'sm.ship_mode_name']);
        $dates = $poRows->pluck('delivery_date')->filter()->unique()->sort()->values();

        return [
            'header' => [
                'costing_no' => $costing->costing_no,
                'no_ws' => $line->no_ws,
                'so_no' => $so->so_no,
                'delivery' => $dates->map(fn ($date) => date('d-m-Y', strtotime($date)))->pipe(fn ($d) => $d->count() > 1 ? $d->first() . ' s/d ' . $d->last() : (string) $d->first()),
                'bom_no' => $bom->bom_no ?? null,
                'buyer' => $costing->buyer,
                'buyer_code' => $costing->supplier_code,
                'brand' => $costing->brand_name,
                'season' => $costing->season_name,
                'style' => $costing->style,
                'planned_qty' => $costing->planned_qty,
                'ws_qty' => collect($breakdown['qty'])->flatten()->sum(),
                'product_group' => $set->product_group_name ?? null,
                'product_item' => $set->product_item_name ?? null,
                'product_set' => $set->set_type ?? null,
                'unit' => $costing->unit_code,
                'ship_mode' => $poRows->pluck('ship_mode_name')->filter()->unique()->implode(', '),
                'main_destination' => $costing->destination_name,
                'destinations' => collect($breakdown['dests'])->pluck('name')->implode(', '),
            ],
            'breakdown' => $breakdown,
            'rows' => $this->materialRows($costing, $line, $bom),
            'charges' => $this->chargeRows($costing, $line, $bom),
            'masters' => $withMasters ? $this->masters() : null,
            'ruleReady' => $this->ruleReady(),
            'detailReady' => $this->detailReady(),
            'detailFull' => $this->detailReady() && count($this->detailColumns()) > 2,
            'stale' => $this->staleness($line),
        ];
    }

    /** Qty WS per destinasi x color x size dari so_po. Color urut kemunculan, size urut master. */
    public function breakdown(int $lineId): array
    {
        $rows = self::active($this->db()->table('so_po as p')->join('so_production as sp', 'sp.id', '=', 'p.so_production_id'), 'p.cancel')
            ->leftJoin('master_colors as c', 'c.id', '=', 'sp.color_id')
            ->leftJoin('master_sizes as s', 's.id', '=', 'sp.sizes_id')
            ->leftJoin('master_destinations as d', 'd.id', '=', 'p.destination_id')
            ->leftJoin('master_countries as ct', 'ct.id', '=', 'd.country_id')
            ->where('sp.so_line_id', $lineId)
            ->where(fn ($q) => $q->whereNull('sp.cancel')->orWhere('sp.cancel', '!=', 'Y'))
            ->groupBy('p.destination_id', 'd.destination_name', 'ct.country_name', 'sp.color_id', 'c.color_name', 'sp.sizes_id', 's.size_name', 's.urutan')
            ->orderBy(DB::raw('MIN(sp.id)'))
            ->get(['p.destination_id', DB::raw("CONCAT_WS(' - ', NULLIF(ct.country_name, ''), d.destination_name) as destination_name"), 'sp.color_id', 'c.color_name', 'sp.sizes_id', 's.size_name', 's.urutan', DB::raw('SUM(p.qty) as qty')]);

        $colors = $rows->unique('color_id')->map(fn ($r) => ['id' => (int) $r->color_id, 'name' => (string) $r->color_name])->values();
        $sizes = $rows->unique('sizes_id')->sortBy(fn ($r) => sprintf('%08d|%s', (int) $r->urutan, $r->size_name))
            ->map(fn ($r) => ['id' => (int) $r->sizes_id, 'name' => (string) $r->size_name])->values();
        $dests = $rows->unique('destination_id')->map(fn ($r) => ['id' => (int) $r->destination_id, 'name' => (string) $r->destination_name])->values();
        $sizeIndex = $sizes->pluck('id')->flip();

        // qty[destId][colorId] = [qty per size, urut $sizes]
        $qty = [];
        foreach ($dests as $dest) {
            foreach ($colors as $color) {
                $qty[$dest['id']][$color['id']] = array_fill(0, $sizes->count(), 0);
            }
        }
        foreach ($rows as $row) {
            $qty[(int) $row->destination_id][(int) $row->color_id][$sizeIndex[(int) $row->sizes_id]] += (int) $row->qty;
        }

        return ['colors' => $colors->all(), 'sizes' => $sizes->all(), 'dests' => $dests->all(), 'qty' => $qty];
    }

    private function masters(): array
    {
        $db = $this->db();
        $pairs = fn ($query, $name) => self::active($query)->orderBy($name)->get(['id', "{$name} as name"])
            ->map(fn ($row) => ['id' => (int) $row->id, 'name' => (string) $row->name])->values()->all();

        return [
            'items' => self::active($db->table('master_items'))->orderBy('items_name')->get(['id', 'items_name', 'content_id', 'unit_id'])
                ->map(fn ($row) => ['id' => (int) $row->id, 'name' => mb_strtoupper((string) $row->items_name), 'content' => (int) $row->content_id, 'unit' => $row->unit_id ? (int) $row->unit_id : null])->values()->all(),
            // name = kode (yang tampil di sel), full = "kode - nama" (yang tampil di daftar pilihan).
            'units' => self::active($db->table('master_units'))->orderBy('unit_code')->get(['id', 'unit_code', 'unit_name'])
                ->map(fn ($row) => ['id' => (int) $row->id, 'name' => (string) $row->unit_code, 'full' => trim($row->unit_code . ($row->unit_name ? ' - ' . $row->unit_name : ''))])->values()->all(),
            'suppliers' => $pairs($db->table('master_partners')->where('is_supplier', 1), 'name'),
            // Label destinasi "NEGARA - DESTINASI" (pilihan kolom Destinasi tetap hanya destinasi SO, lihat validIds).
            'dests' => $db->table('master_destinations as d')->leftJoin('master_countries as c', 'c.id', '=', 'd.country_id')
                ->where(fn ($q) => $q->whereNull('d.cancel')->orWhere('d.cancel', '!=', 'Y'))
                ->orderByRaw('COALESCE(c.country_name, \'\'), d.destination_name')
                ->get(['d.id', 'd.destination_name', 'c.country_name'])
                ->map(fn ($row) => ['id' => (int) $row->id, 'name' => trim(($row->country_name ? $row->country_name . ' - ' : '') . $row->destination_name)])->values()->all(),
        ];
    }

    private function costingMaterials(object $costing, object $line)
    {
        return self::active($this->db()->table('costing_materials as d'), 'd.cancel')
            ->where('d.costings_id', $costing->id)
            ->where(fn ($q) => $q->whereNull('d.costing_set_id')->orWhere('d.costing_set_id', $line->costing_set_id));
    }

    private function costingCharges(object $costing, object $line)
    {
        return self::active($this->db()->table('costing_charges as d'), 'd.cancel')
            ->join('master_contents as m', 'm.id', '=', 'd.contents_id')
            ->join('master_group as g', 'g.id', '=', 'm.group_id')
            ->where('d.costings_id', $costing->id)
            ->whereIn('g.group_code', ['M', 'OC'])
            ->where(fn ($q) => $q->whereNull('d.costing_set_id')->orWhere('d.costing_set_id', $line->costing_set_id));
    }

    /** Material Costing milik set WS ini (atau tanpa set) beserta isi BOM yang sudah tersimpan. */
    private function materialRows(object $costing, object $line, ?object $bom): array
    {
        $db = $this->db();
        $materials = $this->costingMaterials($costing, $line)
            ->leftJoin('master_contents as m', 'm.id', '=', 'd.contents_id')
            ->leftJoin('master_group as g', 'g.id', '=', 'm.group_id')
            ->leftJoin('master_shells as sh', 'sh.id', '=', 'd.shells_id')
            ->leftJoin('master_currencies as cu', 'cu.id', '=', 'd.currency_id')
            ->orderBy('d.contents_id')->orderBy('d.id')
            ->get(['d.*', 'm.content_name', 'g.group_name', 'sh.shell_name', 'cu.currency_code']);
        $parts = self::active($db->table('costing_material_parts as mp'), 'mp.cancel')
            ->join('master_parts as p', 'p.id', '=', 'mp.parts_id')
            ->whereIn('mp.costing_materials_id', $materials->pluck('id'))
            ->orderBy('mp.id')
            ->get(['mp.costing_materials_id', 'p.part_name'])
            ->groupBy('costing_materials_id');
        $saved = $bom ? self::active($db->table('bom_materials')->where('bom_id', $bom->id))->orderBy('id')->get()->keyBy('costing_materials_id') : collect();
        $num = fn ($value) => $value === null || $value === '' ? null : (float) $value;
        $id = fn ($value) => $value ? (int) $value : null;
        $details = $saved->isNotEmpty() && $this->detailReady()
            ? self::active($db->table('bom_material_details')->whereIn('bom_materials_id', $saved->pluck('id')))->orderBy('id')->get()->groupBy('bom_materials_id')
            : collect();

        $rows = [];
        foreach ($materials as $material) {
            $tab = collect(self::TABS)->search(fn ($keyword) => str_contains(strtoupper((string) $material->group_name), $keyword));
            if (!$tab) {
                continue;
            }
            // Belum ada di BOM: isi awal dari Costing, item dipilih user.
            $row = $saved[$material->id] ?? null;
            $rule = in_array($row->rule_bom ?? null, self::RULES, true) ? $row->rule_bom : self::RULES[0];
            [$cons, $subs] = $this->consAndSubs($row, $details[$row->id ?? 0] ?? collect(), $rule, $num($material->cons), $num, $id);
            $rows[] = [
                'id' => $row ? (int) $row->id : null,
                'cm' => (int) $material->id,
                'tab' => $tab,
                'content' => mb_strtoupper((string) $material->content_name),
                'contentId' => (int) $material->contents_id,
                'shell' => (string) $material->shell_name,
                'parts' => ($parts[$material->id] ?? collect())->pluck('part_name')->implode(', '),
                'pc' => $num($material->price),
                // Cons Costing: batas atas cons BOM (baris induk dan rincian per color/size).
                'consC' => $num($material->cons),
                'cur' => (string) $material->currency_code,
                'rule' => $rule,
                'subs' => $subs,
                'item' => $row ? $id($row->items_id) : null,
                'dest' => $row ? $id($row->destinations_id) : null,
                'cons' => $cons,
                'unit' => $id($row ? $row->units_id : $material->units_id),
                'loss' => $num($row ? $row->allowance_percent : $material->allowance_percent),
                'sup' => $id($row ? $row->supplier_id : $material->supplier_id),
                'price' => $num($row ? $row->price : $material->price),
            ];
        }

        return $rows;
    }

    /**
     * Cons baris induk + rincian per color/size dari bom_material_details.
     * Cons induk = nilai yang paling sering dipakai (jadi cons awal sub-baris baru); cons tiap rincian dikirim apa adanya.
     */
    private function consAndSubs(?object $row, $details, string $rule, ?float $costingCons, callable $num, callable $id): array
    {
        if (!$row) {
            return [$costingCons, []];
        }
        if ($details->isEmpty()) {
            return [$num($row->cons ?? null) ?? $costingCons, []];   // tersimpan sebelum ada rincian
        }
        if ($rule === self::RULES[0]) {
            return [$num($details->first()->cons), []];
        }
        $common = $details->pluck('cons')->filter(fn ($v) => $v !== null)->map(fn ($v) => (string) (float) $v)->countBy()->sortDesc()->keys()->first();
        $common = $common === null ? null : (float) $common;

        return [$common, $details->map(fn ($d) => [
            'color' => $id($d->color_id),
            'sfrom' => $id($d->size_from_id),
            'sto' => $id($d->size_to_id),
            'item' => $id($d->master_items_id),
            'dest' => $id($d->destinations_id ?? null),
            'cons' => $num($d->cons),
            'unit' => $id($d->units_id ?? null),
            'loss' => $num($d->allowance_percent ?? null),
            'sup' => $id($d->supplier_id ?? null),
            'price' => $num($d->price ?? null),
        ])->values()->all()];
    }

    private function chargeRows(object $costing, object $line, ?object $bom): array
    {
        $db = $this->db();
        $charges = $this->costingCharges($costing, $line)
            ->leftJoin('master_currencies as cu', 'cu.id', '=', 'd.currency_id')
            ->orderBy('d.id')
            ->orderByRaw("CASE g.group_code WHEN 'M' THEN 1 ELSE 2 END")
            ->get(['d.*', 'm.content_name', 'g.group_code', 'cu.currency_code']);
        $saved = $bom ? self::active($db->table('bom_charges')->where('bom_id', $bom->id))->get()->keyBy('costing_charges_id') : collect();
        $num = fn ($value) => $value === null || $value === '' ? null : (float) $value;
        $id = fn ($value) => $value ? (int) $value : null;

        return $charges->map(function ($charge) use ($saved, $num, $id) {
            $row = $saved[$charge->id] ?? null;
            // Harga Costing termasuk allowance baris itu (CMT: CM Price + allowance dari card CM Price), 2 desimal.
            $costed = $charge->price === null ? null : round((float) $charge->price * (1 + (float) ($charge->allowance_percent ?? 0) / 100), 2);

            return [
                'id' => $row ? (int) $row->id : null,
                'cc' => (int) $charge->id,
                'tab' => $charge->group_code === 'M' ? 'MFG' : 'OTH',
                'content' => mb_strtoupper((string) $charge->content_name),
                'contentId' => (int) $charge->contents_id,
                'pc' => $costed,
                'cur' => (string) $charge->currency_code,
                'item' => $row ? $id($row->items_id) : null,
                'cons' => $num($row ? $row->cons : $charge->cons),
                'unit' => $id($row ? $row->units_id : $charge->units_id),
                'sup' => $id($row ? $row->supplier_id : $charge->supplier_id),
                'price' => $row ? $num($row->price) : $costed,
            ];
        })->values()->all();
    }

    /**
     * Menyimpan isi tabel. Baris yang sudah ada diubah di tempat (dicocokkan lewat material / charge Costing-nya),
     * baris baru ditambah, baris yang materialnya sudah hilang dari Costing di-cancel. Mengembalikan BOM yang tersimpan.
     */
    public function save(object $line, array $rows, array $charges): object
    {
        $db = $this->db();

        return $db->transaction(function () use ($db, $line, $rows, $charges) {
            $db->table('so_line')->where('id', $line->id)->lockForUpdate()->first();
            $so = $db->table('sales_orders')->where('id', $line->sales_order_id)->first();
            $costing = app(MarketingSalesOrderService::class)->costing((int) $so->costing_id);
            $bom = $this->activeBom((int) $line->id);
            // Cek kunci lagi di dalam transaksi (baris BOM dikunci): approve yang masuk bersamaan tidak boleh tertimpa simpanan ini.
            if ($bom) {
                $db->table('bom')->where('id', $bom->id)->lockForUpdate()->first();
                if ($message = app(MarketingNdsApprovalService::class)->lockedMessage('bom', (int) $bom->id)) {
                    throw new RuntimeException($message);
                }
            }
            if (!$bom) {
                $values = [
                    'bom_no' => app(MarketingNdsCostingService::class)->nextNumber('bom', 'bom_no', 'BOM', 5),
                    'revision_no' => 0,
                    'bom_date' => now()->toDateString(),
                    'costings_id' => $costing->id,
                    'so_line_id' => $line->id,
                    'status' => 'DRAFT',
                    'cancel' => 'N',
                    'created_at' => now(),
                ] + MarketingNdsCostingService::creator('bom');
                // sales_orders_id sudah tidak ada di ERD terbaru, tapi masih ada di DB yang belum digenerate ulang.
                if (Schema::connection(self::CONNECTION)->hasColumn('bom', 'sales_orders_id')) {
                    $values['sales_orders_id'] = $so->id;
                }
                $bom = $db->table('bom')->where('id', $db->table('bom')->insertGetId($values))->first();
            } else {
                $db->table('bom')->where('id', $bom->id)->update(['updated_at' => now()]);
            }

            // Cons BOM (induk & rincian) tidak boleh melebihi cons Costing material itu.
            $consCosting = $this->costingMaterials($costing, $line)->get(['d.id', 'd.cons', 'd.units_id'])->keyBy('id');
            foreach (array_values($rows) as $index => $row) {
                $cm = $consCosting[(int) ($row['cm'] ?? 0)] ?? null;
                $limit = $cm->cons ?? null;
                if ($limit === null) {
                    continue;
                }
                $rowUnit = ($row['unit'] ?? '') !== '' ? (int) $row['unit'] : null;
                $entries = array_merge([[$row['cons'] ?? null, $rowUnit]], array_map(fn ($sub) => [$sub['cons'] ?? null, ($sub['unit'] ?? '') !== '' ? (int) $sub['unit'] : $rowUnit], $row['subs'] ?? []));
                foreach ($entries as [$cons, $unit]) {
                    if ($unit !== null && $cm->units_id && $unit !== (int) $cm->units_id) {
                        continue; // unit berbeda dari Costing: angka cons tidak sebanding
                    }
                    if ($cons !== null && $cons !== '' && is_numeric($cons) && (float) $cons > (float) $limit + 1e-9) {
                        throw new RuntimeException('Baris ' . ($index + 1) . ': Cons BOM ' . (float) $cons . ' lebih besar dari Cons costing ' . (float) $limit . '.');
                    }
                }
            }

            // Price BOM (induk & rincian) tidak boleh melebihi Price costing (charges: termasuk allowance, sama dengan kolom Price costing).
            $priceCosting = $this->costingMaterials($costing, $line)->pluck('d.price', 'd.id');
            $chargeCosting = $this->costingCharges($costing, $line)->get(['d.id', 'd.price', 'd.allowance_percent'])
                ->mapWithKeys(fn ($c) => [$c->id => $c->price === null ? null : round((float) $c->price * (1 + (float) ($c->allowance_percent ?? 0) / 100), 4)]);
            foreach ([['cm', $rows, $priceCosting, 'Baris'], ['cc', $charges, $chargeCosting, 'Other Cost baris']] as [$key, $list, $limits, $prefix]) {
                foreach (array_values($list) as $index => $row) {
                    $limit = $limits[(int) ($row[$key] ?? 0)] ?? null;
                    if ($limit === null) {
                        continue;
                    }
                    foreach (array_merge([$row['price'] ?? null], array_column($row['subs'] ?? [], 'price')) as $price) {
                        if ($price !== null && $price !== '' && is_numeric($price) && round((float) $price, 4) > round((float) $limit, 4) + 1e-9) {
                            throw new RuntimeException($prefix . ' ' . ($index + 1) . ': Price BOM ' . (float) $price . ' lebih besar dari Price costing ' . (float) $limit . '.');
                        }
                    }
                }
            }

            $breakdown = $this->breakdown((int) $line->id);
            $valid = $this->validIds($breakdown);
            $detailReady = $this->detailReady();
            // Dengan bom_material_details, cons material hanya disimpan di rincian (bom_materials.cons sudah tidak ada di ERD).
            $drop = array_merge($this->ruleReady() ? [] : ['rule_bom'], $detailReady ? ['cons'] : []);
            $this->saveRows('bom_materials', 'costing_materials_id', $bom, $this->costingMaterials($costing, $line)->get(['d.id', 'd.currency_id'])->keyBy('id'), $rows, 'cm', $valid, 'Baris',
                fn ($values) => array_diff_key($values, array_flip($drop)),
                $detailReady ? fn (int $materialId, array $row, array $values, string $label) => $this->saveDetails($materialId, $row, $values, $breakdown, $valid, $label) : null);
            $this->saveRows('bom_charges', 'costing_charges_id', $bom, $this->costingCharges($costing, $line)->get(['d.id', 'd.currency_id'])->keyBy('id'), $charges, 'cc', $valid, 'Other Cost baris',
                fn ($values) => array_diff_key($values, array_flip(['destinations_id', 'allowance_percent', 'line_no', 'rule_bom'])));

            return $bom;
        });
    }

    /** $source = baris Costing (material atau charge) yang boleh dipakai, kunci id. $shape membuang kolom yang tidak ada di tabel tujuan. */
    private function saveRows(string $table, string $sourceColumn, object $bom, $source, array $rows, string $key, array $valid, string $labelPrefix, callable $shape, ?callable $after = null): void
    {
        $db = $this->db();
        $existing = self::active($db->table($table)->where('bom_id', $bom->id))->get()->keyBy($sourceColumn);
        $kept = [];

        foreach (array_values($rows) as $index => $row) {
            $sourceId = (int) ($row[$key] ?? 0);
            if (!isset($source[$sourceId])) {
                throw new RuntimeException(($key === 'cm' ? 'Ada material' : 'Ada Other Cost') . ' yang sudah tidak ada di Costing. Muat ulang halaman lalu isi lagi.');
            }
            $label = $labelPrefix . ' ' . ($index + 1);
            $all = $this->rowValues($row, $valid, $label);
            $values = $shape($all + [
                'bom_id' => $bom->id,
                $sourceColumn => $sourceId,
                'currency_id' => $source[$sourceId]->currency_id,
                'line_no' => (string) ($index + 1),
                'cancel' => 'N',
            ]);
            $old = $existing[$sourceId] ?? null;
            if ($old) {
                $db->table($table)->where('id', $old->id)->update($values + ['updated_at' => now()]);
                $kept[] = (int) $old->id;
            } else {
                $kept[] = (int) $db->table($table)->insertGetId($values + ['created_at' => now()] + MarketingNdsCostingService::creator($table));
            }
            if ($after) {
                $after(end($kept), $row, $all, $label);
            }
        }

        // Baris lama yang materialnya sudah tidak ada di tabel (dihapus dari Costing).
        $removed = $existing->pluck('id')->diff($kept)->values();
        if ($removed->isNotEmpty()) {
            $db->table($table)->whereIn('id', $removed)->update(['cancel' => 'Y', 'updated_at' => now()]);
        }
    }

    /**
     * Rincian 1 material sesuai rule: All Color All Size = 1 baris tanpa color/size; rule lain = baris dari front-end
     * (per color dari SO dan/atau range size berurutan). Dicocokkan lewat color + size_from + size_to, sisanya di-cancel.
     */
    private function saveDetails(int $materialId, array $row, array $values, array $breakdown, array $valid, string $label): void
    {
        $db = $this->db();
        $rule = $values['rule_bom'];
        $perColor = str_starts_with($rule, 'Per Color');
        $range = str_contains($rule, 'Range Size');
        $perDest = str_ends_with($rule, 'Per Destination');
        $destIds = array_column($breakdown['dests'], 'id');
        $colorIds = array_column($breakdown['colors'], 'id');
        $sizeIds = array_column($breakdown['sizes'], 'id');
        $int = fn ($v) => $v === null || $v === '' ? null : (int) $v;
        // Nilai hasil rowValues -> kolom bom_material_details yang tersedia.
        $columns = array_flip($this->detailColumns());
        $fields = fn (array $v) => array_intersect_key([
            'master_items_id' => $v['items_id'], 'destinations_id' => $v['destinations_id'], 'cons' => $v['cons'], 'units_id' => $v['units_id'],
            'allowance_percent' => $v['allowance_percent'], 'supplier_id' => $v['supplier_id'], 'price' => $v['price'],
        ], $columns);

        $want = [];
        if ($rule === self::RULES[0]) {
            $want[] = ['color_id' => null, 'size_from_id' => null, 'size_to_id' => null] + $fields($values);
        } else {
            foreach (array_values($row['subs'] ?? []) as $i => $sub) {
                $at = "{$label} rincian " . ($i + 1);
                $color = $int($sub['color'] ?? null);
                $from = $int($sub['sfrom'] ?? null);
                $to = $int($sub['sto'] ?? null);
                if ($perColor !== ($color !== null) || ($color !== null && !in_array($color, $colorIds, true))) {
                    throw new RuntimeException("{$at}: color tidak sesuai Rule BOM / Sales Order WS ini. Muat ulang halaman.");
                }
                $fromAt = array_search($from, $sizeIds, true);
                $toAt = array_search($to, $sizeIds, true);
                if ($range ? ($fromAt === false || $toAt === false || $fromAt > $toAt) : ($from !== null || $to !== null)) {
                    throw new RuntimeException("{$at}: size tidak sesuai Rule BOM / Sales Order WS ini. Muat ulang halaman.");
                }
                if ($perDest && !in_array($int($sub['dest'] ?? null), $destIds, true)) {
                    throw new RuntimeException("{$at}: destinasi tidak sesuai Rule BOM / Sales Order WS ini. Muat ulang halaman.");
                }
                // Isian sub divalidasi sama dengan baris induk (item, destinasi, unit, supplier dari daftar; angka >= 0; loss 0-100).
                $subValues = $this->rowValues($sub, $valid, $at);
                if (!$perDest) {
                    // Rule bukan per destinasi: destinasi rincian = destinasi baris induk.
                    $subValues['destinations_id'] = $values['destinations_id'];
                }
                $want[] = ['color_id' => $color, 'size_from_id' => $from, 'size_to_id' => $to] + $fields($subValues);
            }
            if (!$want) {
                throw new RuntimeException("{$label}: rincian {$rule} kosong (belum ada color/size di Sales Order WS ini).");
            }
        }

        // Rule per destinasi: rincian juga dibedakan menurut destinasinya.
        $key = fn ($d) => ($perDest ? ($d['destinations_id'] ?? '') . '#' : '') . ($d['color_id'] ?? '') . '|' . ($d['size_from_id'] ?? '') . '|' . ($d['size_to_id'] ?? '');
        $existing = self::active($db->table('bom_material_details')->where('bom_materials_id', $materialId))->orderBy('id')->get()
            ->keyBy(fn ($d) => $key((array) $d));
        $kept = [];
        foreach ($want as $detail) {
            $k = $key($detail);
            if (isset($kept[$k])) {
                throw new RuntimeException("{$label}: ada rincian color/size/destinasi yang dobel. Muat ulang halaman.");
            }
            $old = $existing[$k] ?? null;
            if ($old) {
                $db->table('bom_material_details')->where('id', $old->id)->update($detail + ['cancel' => 'N', 'updated_at' => now()]);
                $kept[$k] = (int) $old->id;
            } else {
                $kept[$k] = (int) $db->table('bom_material_details')->insertGetId($detail + ['bom_materials_id' => $materialId, 'cancel' => 'N', 'created_at' => now()] + MarketingNdsCostingService::creator('bom_material_details'));
            }
        }
        $removed = $existing->pluck('id')->diff(array_values($kept))->values();
        if ($removed->isNotEmpty()) {
            $db->table('bom_material_details')->whereIn('id', $removed)->update(['cancel' => 'Y', 'updated_at' => now()]);
        }
    }

    /** Id yang boleh dipakai: destinasi dari SO WS ini, serta master item, unit, dan supplier aktif. */
    private function validIds(array $breakdown): array
    {
        $masters = $this->masters();

        return [
            'dests' => array_column($breakdown['dests'], 'id'),
            'items' => array_column($masters['items'], 'id'),
            'units' => array_column($masters['units'], 'id'),
            'suppliers' => array_column($masters['suppliers'], 'id'),
        ];
    }

    private function rowValues(array $row, array $valid, string $label): array
    {
        $pick = function ($key, $list, $name) use ($row, $valid, $label) {
            $value = $row[$key] ?? null;
            if ($value === null || $value === '') {
                return null;
            }
            if (!in_array((int) $value, $valid[$list], true)) {
                throw new RuntimeException("{$label}: {$name} tidak ada di daftar.");
            }

            return (int) $value;
        };
        // Cons disimpan 3 desimal, price 4 desimal (sama dengan Costing), loss % 2 desimal.
        $number = function ($key, $name, $max = null) use ($row, $label) {
            $decimals = ['cons' => 3, 'price' => 4, 'loss' => 2][$key] ?? 4;
            $value = $row[$key] ?? null;
            if ($value === null || $value === '') {
                return null;
            }
            if (!is_numeric($value) || $value < 0 || ($max !== null && $value > $max)) {
                throw new RuntimeException("{$label}: {$name} harus angka" . ($max !== null ? " 0 sampai {$max}." : ' 0 atau lebih.'));
            }

            return round((float) $value, $decimals);
        };

        $rule = $row['rule'] ?? null;
        if ($rule !== null && $rule !== '' && !in_array($rule, self::RULES, true)) {
            throw new RuntimeException("{$label}: Rule BOM tidak ada di daftar.");
        }

        return [
            'rule_bom' => $rule ?: self::RULES[0],
            'items_id' => $pick('item', 'items', 'Item'),
            'destinations_id' => $pick('dest', 'dests', 'Destinasi'),
            'cons' => $number('cons', 'Cons/pc'),
            'units_id' => $pick('unit', 'units', 'Unit'),
            'allowance_percent' => $number('loss', 'Loss %', 100),
            'supplier_id' => $pick('sup', 'suppliers', 'Supplier'),
            'price' => $number('price', 'Price'),
        ];
    }
}
