<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class PackingListController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            // per baris -> per karton -> per po & dest
            // karton full = semua baris di karton qty_scan >= qty (sama dengan modal preview)
            // subquery berkorelasi (ws, styleno, qty_sisa) diganti derived table yang di-join sekali per po & dest
            $data_pl = DB::select("SELECT
                c.po,
                c.dest,
                max(w.ws) ws,
                max(c.buyer) buyer,
                max(w.styleno) styleno,
                max(c.tgl_shipment) tgl_shipment,
                concat((DATE_FORMAT(max(c.tgl_shipment),  '%d')), '-', left(DATE_FORMAT(max(c.tgl_shipment),  '%M'),3),'-',DATE_FORMAT(max(c.tgl_shipment),  '%Y')) tgl_shipment_fix,
                count(c.no_carton) tot_carton,
                sum(c.is_full) carton_full,
                sum(c.qty) tot_qty,
                sum(least(c.qty_scan, c.qty)) qty_isi,
                -- sisa qty PO yang belum masuk packing list, dihitung per SKU seperti form create_repeat
                coalesce(max(s.qty_sisa), 0) qty_sisa,
                DATE_FORMAT(max(c.created_at), '%d-%m-%Y %H:%i:%s') tgl_input,
                max(c.created_by) created_by,
                CASE
                        WHEN sum(c.qty_scan) = 0 THEN 'PENDING'
                        WHEN sum(c.is_full) = count(c.no_carton) THEN 'FULL'
                        ELSE 'PROSES'
                END AS status
                from (
                select
                a.po,
                a.dest,
                a.no_carton,
                max(m.ws) ws,
                max(m.buyer) buyer,
                max(m.styleno) styleno,
                max(f0.tgl_shipment) tgl_shipment,
                sum(a.qty) qty,
                sum(coalesce(b.qty_scan,0)) qty_scan,
                min(case when coalesce(b.qty_scan,0) >= a.qty then 1 else 0 end) is_full,
                max(a.created_at) created_at,
                max(a.created_by) created_by
                from packing_master_packing_list a
                -- tanggal hanya untuk memilih po & dest; karton & qty dihitung dari semua baris po & dest (sama dengan modal preview)
                inner join (
                select a.po, a.dest, max(p.tgl_shipment) tgl_shipment
                from packing_master_packing_list a
                inner join ppic_master_so p on a.id_ppic_master_so = p.id
                where p.tgl_shipment >= ? and p.tgl_shipment <= ?
                group by a.po, a.dest
                ) f0 on a.po = f0.po and a.dest = f0.dest
                left join master_sb_ws m on a.id_so_det = m.id_so_det
                left join (
                select o.po, o.dest, o.no_carton, coalesce(k1.id_so_det, k2.id_so_det) id_so_det, sum(o.qty_scan) qty_scan
                from (
                -- scan diringkas dulu per karton + sku + barcode supaya join ke k1/k2 tidak per baris scan
                select o.po, o.dest, o.no_carton, o.id_so_det, o.barcode, count(*) qty_scan
                from packing_packing_out_scan o
                inner join (
                select distinct a.po, a.dest
                from packing_master_packing_list a
                inner join ppic_master_so p on a.id_ppic_master_so = p.id
                where p.tgl_shipment >= ? and p.tgl_shipment <= ?
                ) f on o.po = f.po and o.dest = f.dest
                group by o.po, o.dest, o.no_carton, o.id_so_det, o.barcode
                ) o
                left join (
                select distinct a.po, a.dest, a.no_carton, a.id_so_det
                from packing_master_packing_list a
                inner join (
                select distinct a.po, a.dest
                from packing_master_packing_list a
                inner join ppic_master_so p on a.id_ppic_master_so = p.id
                where p.tgl_shipment >= ? and p.tgl_shipment <= ?
                ) fk1 on a.po = fk1.po and a.dest = fk1.dest
                ) k1 on o.po = k1.po and o.dest = k1.dest and o.no_carton = k1.no_carton and o.id_so_det = k1.id_so_det
                left join (
                select a.po, a.dest, a.no_carton, a.barcode, min(a.id_so_det) id_so_det
                from packing_master_packing_list a
                inner join (
                select distinct a.po, a.dest
                from packing_master_packing_list a
                inner join ppic_master_so p on a.id_ppic_master_so = p.id
                where p.tgl_shipment >= ? and p.tgl_shipment <= ?
                ) fk2 on a.po = fk2.po and a.dest = fk2.dest
                group by a.po, a.dest, a.no_carton, a.barcode
                ) k2 on o.po = k2.po and o.dest = k2.dest and o.no_carton = k2.no_carton and o.barcode = k2.barcode
                group by o.po, o.dest, o.no_carton, coalesce(k1.id_so_det, k2.id_so_det)
                ) b on a.po = b.po and a.dest = b.dest and a.no_carton = b.no_carton and a.id_so_det = b.id_so_det
                group by a.po, a.dest, a.no_carton
                ) c
                -- ws & styleno: semua baris packing list po & dest tsb (tanpa filter tanggal, sama seperti sebelumnya)
                left join (
                select a2.po, a2.dest,
                group_concat(distinct m2.ws order by m2.ws separator ', ') ws,
                group_concat(distinct m2.styleno order by m2.styleno separator ', ') styleno
                from packing_master_packing_list a2
                inner join (
                select distinct a.po, a.dest
                from packing_master_packing_list a
                inner join ppic_master_so p on a.id_ppic_master_so = p.id
                where p.tgl_shipment >= ? and p.tgl_shipment <= ?
                ) f2 on a2.po = f2.po and a2.dest = f2.dest
                left join master_sb_ws m2 on a2.id_so_det = m2.id_so_det
                group by a2.po, a2.dest
                ) w on c.po = w.po and c.dest = w.dest
                -- sisa qty PO yang belum masuk packing list, dihitung per SKU seperti form create_repeat
                left join (
                select p2.po, p2.dest, sum(greatest(p2.qty_po - coalesce(pl.qty, 0), 0)) qty_sisa
                from ppic_master_so p2
                inner join master_sb_ws m2 on p2.id_so_det = m2.id_so_det
                inner join (
                select distinct a.po, a.dest
                from packing_master_packing_list a
                inner join ppic_master_so p on a.id_ppic_master_so = p.id
                where p.tgl_shipment >= ? and p.tgl_shipment <= ?
                ) f3 on p2.po = f3.po and p2.dest = f3.dest
                left join (
                select a3.po, a3.id_ppic_master_so, sum(a3.qty) qty
                from packing_master_packing_list a3
                inner join (
                select distinct a.po
                from packing_master_packing_list a
                inner join ppic_master_so p on a.id_ppic_master_so = p.id
                where p.tgl_shipment >= ? and p.tgl_shipment <= ?
                ) f4 on a3.po = f4.po
                group by a3.po, a3.id_ppic_master_so
                ) pl on pl.po = p2.po and pl.id_ppic_master_so = p2.id
                group by p2.po, p2.dest
                ) s on c.po = s.po and c.dest = s.dest
                group by c.po, c.dest
                order by max(c.tgl_shipment) asc, c.po asc
            ", array_merge(...array_fill(0, 7, [$request->dateFrom, $request->dateTo])));

            return DataTables::of($data_pl)->toJson();
        }

        return view("packing-list.index", [
            'containerFluid' => true,
            'page' => 'dashboard-packing',
            "subPageGroup" => "packing-master-karton",
            "subPage" => "packing-list-karton",
            "tgl_awal_fix" => date('Y-m-d', strtotime("-90 days")),
            "tgl_akhir_fix" => date('Y-m-d', strtotime("+90 days")),
        ]);
    }

    public function create()
    {

        $data_po = DB::select("SELECT
            DISTINCT a.po isi,
            a.po tampil
            from
            (
            select po,dest, id_so_det from ppic_master_so
            where tgl_shipment >= '2024-09-01'
            group by po, dest ) a
            inner join master_sb_ws m on a.id_so_det = m.id_so_det
            left join packing_master_packing_list mp on a.po = mp.po and a.dest = mp.dest
            where mp.po is null and mp.dest is null
            order by a.po asc
        ");

        return view("packing-list.create", [
            'containerFluid' => true,
            'page' => 'dashboard-packing',
            "subPageGroup" => "packing-master-karton",
            "subPage" => "packing-list-karton",
            "data_po" => $data_po,
        ]);
    }

    // tambah carton ke packing list yang sudah ada, form sama dengan create tapi po & dest sudah terkunci
    public function create_repeat(Request $request)
    {
        $po = $request->po;
        $dest = $request->dest;

        $ada = DB::table('packing_master_packing_list')
            ->where('po', $po)
            ->where('dest', $dest)
            ->exists();

        if (!$ada) {
            return redirect()->route('packing-list-karton');
        }

        return view("packing-list.create_repeat", [
            'containerFluid' => true,
            'page' => 'dashboard-packing',
            "subPageGroup" => "packing-master-karton",
            "subPage" => "packing-list-karton",
            "po" => $po,
            "dest" => $dest,
        ]);
    }

    // no carton yang sudah terpakai pada po & dest
    public function get_used_carton(Request $request)
    {
        $data = DB::table('packing_master_packing_list')
            ->where('po', $request->po)
            ->where('dest', $request->dest)
            ->distinct()
            ->pluck('no_carton');

        return response()->json($data);
    }

    // detail SKU per PO dari ppic_master_so (header + matrix size)
    public function get_po_detail(Request $request)
    {
        $po = $request->po;
        $filterDest = $request->filled('dest') ? 'and p.dest = ?' : '';
        $bindings = $request->filled('dest') ? [$po, $po, $request->dest] : [$po, $po];

        $data = DB::select("SELECT
            p.id id_ppic_master_so,
            p.id_so_det,
            p.barcode,
            p.qty_po,
            p.dest,
            DATE_FORMAT(p.tgl_shipment, '%d %b %Y') tgl_shipment,
            m.ws,
            m.buyer,
            m.styleno,
            m.color,
            m.size,
            msn.urutan,
            coalesce(pl.qty_packing, 0) qty_packing
            from ppic_master_so p
            inner join master_sb_ws m on p.id_so_det = m.id_so_det
            left join master_size_new msn on m.size = msn.size
            left join (
            select id_ppic_master_so, sum(qty) qty_packing from packing_master_packing_list
            where po = ?
            group by id_ppic_master_so
            ) pl on p.id = pl.id_ppic_master_so
            where p.po = ? $filterDest
            order by m.ws asc, m.styleno asc, m.color asc, msn.urutan asc
        ", $bindings);

        return response()->json($data);
    }

    public function show_preview_packing_list(Request $request)
    {
        $po = $request->po;
        $dest = $request->dest;

        $data_preview = DB::select("SELECT
            a.no_carton,
            a.tipe_pack,
            a.barcode,
            m.ws,
            m.buyer,
            m.styleno,
            m.color,
            m.size,
            a.qty,
            coalesce(b.qty_scan,0) qty_scan
            from packing_master_packing_list a
            left join master_sb_ws m on a.id_so_det = m.id_so_det
            left join master_size_new msn on m.size = msn.size
            -- scan dicocokkan per carton + id_so_det (barcode bisa '-' untuk banyak item);
            -- scan lama yang id_so_det-nya kosong/tidak cocok lewat barcode (sama dengan PackingOutScanController)
            left join (
            select s.no_carton, coalesce(k1.id_so_det, k2.id_so_det) id_so_det, count(*) qty_scan
            from packing_packing_out_scan s
            left join (
            select distinct no_carton, id_so_det from packing_master_packing_list
            where po = ? and dest = ?
            ) k1 on s.no_carton = k1.no_carton and s.id_so_det = k1.id_so_det
            left join (
            select no_carton, barcode, min(id_so_det) id_so_det from packing_master_packing_list
            where po = ? and dest = ?
            group by no_carton, barcode
            ) k2 on s.no_carton = k2.no_carton and s.barcode = k2.barcode
            where s.po = ? and s.dest = ?
            group by s.no_carton, coalesce(k1.id_so_det, k2.id_so_det)
            ) b on a.no_carton = b.no_carton and a.id_so_det = b.id_so_det
            where a.po = ? and a.dest = ?
            order by a.no_carton asc, m.ws asc, m.color asc, msn.urutan asc
        ", [$po, $dest, $po, $dest, $po, $dest, $po, $dest]);

        return response()->json($data_preview);
    }

    // simpan hasil input carton manual ke packing_master_packing_list
    public function store(Request $request)
    {
        $request->validate([
            'po' => 'required|string',
            'rows' => 'required|array|min:1',
            'rows.*.carton' => 'required|integer|min:1',
            'rows.*.carton_awal' => 'required|integer|min:1',
            'rows.*.carton_akhir' => 'required|integer|min:1',
            'rows.*.id' => 'required|integer',
            'rows.*.qty' => 'required|integer|min:1',
            'rows.*.tipe_pack' => 'required|string|max:50',
            'rows.*.keterangan' => 'nullable|string|max:255',
        ]);

        $po = $request->po;
        $rows = collect($request->rows);
        $user = Auth::user()->name;
        $timestamp = Carbon::now();

        try {
            DB::transaction(function () use ($po, $rows, $user, $timestamp) {
                $ids = $rows->pluck('id')->unique()->values()->all();
                $placeholder = implode(',', array_fill(0, count($ids), '?'));

                // detail SKU diambil ulang dari DB, jangan percaya data dari form
                $detail = collect(DB::select("SELECT
                    p.id,
                    p.po,
                    p.dest,
                    p.barcode,
                    p.id_so_det,
                    p.qty_po,
                    m.color,
                    m.size
                    from ppic_master_so p
                    inner join master_sb_ws m on p.id_so_det = m.id_so_det
                    where p.po = ? and p.id in ($placeholder)
                    for update
                ", array_merge([$po], $ids)))->keyBy('id');

                if ($detail->count() != count($ids)) {
                    throw new \Exception('Ada SKU yang tidak termasuk PO ' . $po);
                }

                $existing = collect(DB::select("SELECT id_ppic_master_so, sum(qty) qty
                    from packing_master_packing_list
                    where po = ? and id_ppic_master_so in ($placeholder)
                    group by id_ppic_master_so
                ", array_merge([$po], $ids)))->pluck('qty', 'id_ppic_master_so');

                // qty tidak boleh melebihi balance PO
                foreach ($rows->groupBy('id') as $id => $items) {
                    $d = $detail[$id];
                    $total = ($existing[$id] ?? 0) + $items->sum('qty');
                    if ($total > $d->qty_po) {
                        throw new \Exception("Qty barcode {$d->barcode} melebihi Qty PO ({$total} > {$d->qty_po})");
                    }
                }

                // no carton tidak boleh dobel untuk po & dest yang sama
                $dests = $detail->pluck('dest')->unique()->values()->all();
                $cartons = $rows->pluck('carton')->unique()->values()->all();
                $used = DB::table('packing_master_packing_list')
                    ->where('po', $po)
                    ->whereIn('dest', $dests)
                    ->whereIn('no_carton', $cartons)
                    ->distinct()
                    ->orderBy('no_carton')
                    ->pluck('no_carton');
                if ($used->count()) {
                    throw new \Exception('Carton ' . $used->implode(', ') . ' sudah ada di packing list PO ' . $po);
                }

                $insert = $rows->map(function ($r) use ($detail, $user, $timestamp) {
                    $d = $detail[$r['id']];
                    return [
                        'po' => $d->po,
                        'dest' => $d->dest,
                        'no_carton' => $r['carton'],
                        'no_carton_awal' => $r['carton_awal'],
                        'no_carton_akhir' => $r['carton_akhir'],
                        'tot_ctn' => $r['carton_akhir'] - $r['carton_awal'] + 1,
                        'tipe_pack' => strtoupper(trim($r['tipe_pack'])),
                        'color' => $d->color,
                        'id_ppic_master_so' => $d->id,
                        'barcode' => $d->barcode,
                        'id_so_det' => $d->id_so_det,
                        'size' => $d->size,
                        'qty' => $r['qty'],
                        'keterangan' => $r['keterangan'] ?? null,
                        'upload_tipe' => 'MANUAL',
                        'created_at' => $timestamp,
                        'updated_at' => $timestamp,
                        'created_by' => $user,
                    ];
                })->all();

                DB::table('packing_master_packing_list')->insert($insert);
            });
        } catch (\Exception $e) {
            return response()->json([
                'status' => 400,
                'message' => $e->getMessage(),
            ], 400);
        }

        return response()->json([
            'status' => 200,
            'message' => 'Packing list PO ' . $po . ' berhasil disimpan (' . $rows->pluck('carton')->unique()->count() . ' carton)',
        ]);
    }
}
