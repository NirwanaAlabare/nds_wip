<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use \avadim\FastExcelLaravel\Excel as FastExcel;

class PemasukanBarangJadiSoController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            return response()->json(['data' => $this->getList($request->dateFrom, $request->dateTo)]);
        }

        return view(
            'finish_good.pemasukan_barang_jadi_so',
            [
                'page' => 'dashboard_finish_good',
                "subPageGroup" => "pemasukan_barang_jadi_so",
                "subPage" => "pemasukan_barang_jadi_so",
                "containerFluid" => true,
            ]
        );
    }

    public function export_excel(Request $request)
    {
        $from = $request->from ?: date('Y-m-d');
        $to = $request->to ?: date('Y-m-d');
        $rows = $this->getList($from, $to);
        $border = \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN;

        $excel = FastExcel::create('Pemasukan Barang Jadi SO');
        $sheet = $excel->getSheet();
        $sheet->setColWidth('A:R', 20); // 18 kolom, lebar sama semua

        $sheet->writeRow(['Laporan Pemasukan Barang Jadi SO'])->applyFontStyleBold()->applyFontSize(14);
        $sheet->writeRow(['Periode ' . date('d-M-Y', strtotime($from)) . ' s/d ' . date('d-M-Y', strtotime($to))])->applyFontStyleBold();
        $sheet->writeRow(['']);

        $sheet->writeRow([
            'No. BPB', 'No. Trans', 'Tgl. BPB', 'Supplier', 'Jenis Pemasukan', 'Jenis Dok', 'Invoice', 'No. Reff', 'PO',
            'WS', 'Style', 'Color', 'Size', 'Qty', 'Unit', 'Grade', 'User', 'Tgl. Input',
        ])->applyFontStyleBold()->applyBorder($border);

        $total = 0;
        foreach ($rows as $r) {
            $total += (int) $r->qty;
            $sheet->writeRow(array_map(fn ($v) => $v ?? '', [
                $r->bpbno_int, $r->bpbno, $r->bpbdate, $r->supplier, $r->jenis_trans, $r->jenis_dok, $r->invno, $r->reffno, $r->po,
                $r->ws, $r->styleno, $r->color, $r->size, (int) $r->qty, $r->unit, $r->grade, $r->username, $r->dateinput,
            ]))->applyBorder($border);
        }

        $sheet->writeRow([
            'Total', '', '', '', '', '', '', '', '', '', '', '', '', $total, 'PCS', '', '', '',
        ])->applyFontStyleBold()->applyBorder($border);

        $tmpFile = sys_get_temp_dir() . DIRECTORY_SEPARATOR . uniqid('xlsx_export_') . '.xlsx';
        $excel->save($tmpFile);

        return response()->download($tmpFile, "Laporan Pemasukan Barang Jadi SO {$from} sampai {$to}.xlsx")->deleteFileAfterSend(true);
    }

    private function getList($from, $to)
    {
        $from = $from ?: date('Y-m-d');
        $to = $to ?: date('Y-m-d');

        return DB::connection('mysql_sb')->select("
            select a.bpbno_int,
                a.bpbno,
                a.bpbdate,
                s.supplier,
                a.jenis_trans,
                a.jenis_dok,
                a.invno,
                a.reffno,
                coalesce(nullif(a.po_fg, ''), '-') as po,
                ws.ws,
                ws.styleno,
                ws.color,
                ws.size,
                a.qty,
                a.unit,
                a.grade,
                a.username,
                a.dateinput
            from bpb a
            left join mastersupplier s on s.id_supplier = a.id_supplier
            left join laravel_nds.master_sb_ws ws on ws.id_so_det = a.id_so_det 
            where left(a.bpbno, 2) = 'FG'
                and a.bpbdate >= ? and a.bpbdate <= ?
            order by a.dateinput desc, a.bpbdate desc, a.bpbno desc, a.id
        ", [$from, $to]);
    }

    public function create(Request $request)
    {
        // Sama dengan ERP bpb_so.php (mod=31, mode=FG)
        $suppliers = DB::connection('mysql_sb')->select("
            select id_supplier, supplier
            from mastersupplier
            where tipe_sup in ('S','D') and non_aktif = '0'
            order by supplier
        ");

        // Jenis pemasukan gudang FG (pola sama dengan ERP bpb_po.php / bpb_global.php, beda jns_gudang)
        $jenisPemasukan = DB::connection('mysql_sb')->select("
            select nama_trans
            from mastertransaksi
            where jenis_trans = 'IN' and jns_gudang = 'FG'
            order by id
        ");

        // Jenis dokumen, sama dengan ERP bpb_so.php: kode_pilihan tergantung status perusahaan
        $company = DB::connection('mysql_sb')->selectOne("select status_company from mastercompany where company != '' limit 1");
        $stCompany = $company->status_company ?? '';
        if ($stCompany == 'KITE') {
            $statusKb = 'Status KITE In';
        } elseif ($stCompany == 'PLB') {
            $statusKb = 'Status PLB In';
        } else {
            $statusKb = 'Status KB In';
        }
        $jenisDokumen = DB::connection('mysql_sb')->select("
            select nama_pilihan
            from masterpilihan
            where kode_pilihan = ?
            order by nama_pilihan
        ", [$statusKb]);

        // WS, sama dengan ERP bpb_so.php (id act_costing, tampil kpno|styleno)
        $wsList = DB::connection('mysql_sb')->select("
            select id, concat(kpno, '|', styleno) as text
            from act_costing
            where cost_date >= '2022-01-01'
            order by kpno
        ");

        return view(
            'finish_good.pemasukan_barang_jadi_so_create',
            [
                'suppliers' => $suppliers,
                'jenisPemasukan' => $jenisPemasukan,
                'jenisDokumen' => $jenisDokumen,
                'wsList' => $wsList,
                'page' => 'dashboard_finish_good',
                "subPageGroup" => "pemasukan_barang_jadi_so",
                "subPage" => "pemasukan_barang_jadi_so",
                "containerFluid" => true,
            ]
        );
    }

    // Supplier Production - Sewing pakai data packing out NDS; supplier lain pakai alur ERP.
    // Dicek dari id_supplier 435 (sama dengan id yang dipakai Penerimaan FG).
    private function isNag($idSupplier)
    {
        return (string) $idSupplier === '435';
    }

    // Daftar PO:
    // - NAG: dari hasil scan packing out
    // - supplier lain: PO milik supplier yang punya item ke JO (pola ERP bpb_so_po.php, mod=31_bj_po)
    public function get_po(Request $request)
    {
        if (! $this->isNag($request->id_supplier)) {
            $po = DB::connection('mysql_sb')->select("
                select ph.id, ph.pono as text
                from po_header ph
                inner join po_item pi on pi.id_po = ph.id
                inner join jo_det jod on jod.id_jo = pi.id_jo
                where ph.id_supplier = ?
                group by ph.id, ph.pono
                order by ph.id desc
            ", [$request->id_supplier]);

            return response()->json($po);
        }

        $po = DB::select("
            select distinct po as id, po as text
            from packing_packing_out_scan
            where po is not null and po != ''
            order by po
        ");

        return response()->json($po);
    }

    // Detail barang per WS:
    // - NAG: data karton dari packing_master_packing_list + jumlah scan packing_packing_out_scan
    // - supplier lain: sama dengan ERP (lihat getDetailErp)
    public function get_detail_by_ws(Request $request)
    {
        $ids = array_values(array_filter(array_map('intval', (array) $request->input('ws_ids', []))));
        if (! $ids) {
            return response()->json([]);
        }
        $idCost = implode(',', $ids); // sudah di-intval, aman dipakai di IN (...)

        if (! $this->isNag($request->id_supplier)) {
            return response()->json($this->getDetailErp($idCost));
        }

        return response()->json($this->getDetailNag($idCost, trim((string) $request->input('po', ''))));
    }

    // Detail NAG per PO + so_det (dipakai get_detail_by_ws dan validasi store)
    private function getDetailNag($idCost, $po = '')
    {
        // Kalau pilih berdasarkan PO, detail dibatasi ke PO tersebut
        $wherePo = $po !== '' ? 'and po = ?' : '';
        $bind = $po !== '' ? [$po] : [];

        // Satu baris per PO + so_det (tanpa karton), qty global:
        // qty_pl = total packing_master_packing_list.qty, qty_scan = jumlah scan packing out,
        // qty_masuk = total BPB FG status_input nds per so_det + PO (po_fg), selain nds = 0
        $rows = DB::select("
            select pl.po,
                pl.id_so_det,
                m.id_act_cost as id_cost,
                m.ws,
                m.styleno,
                m.color,
                m.size,
                m.price,
                m.curr,
                sum(pl.qty_pl) as qty_pl,
                coalesce(sum(pos.qty_scan), 0) as qty_scan,
                coalesce(max(fgin.qty_in), 0) as qty_masuk
            from (
                select id_ppic_master_so, po, id_so_det, sum(qty) as qty_pl
                from packing_master_packing_list
                where id_so_det in (select id_so_det from master_sb_ws where id_act_cost in ($idCost)) $wherePo
                group by id_ppic_master_so, po, id_so_det
            ) pl
            inner join master_sb_ws m on m.id_so_det = pl.id_so_det
            left join (
                select id_ppic, id_so_det, count(id) as qty_scan
                from packing_packing_out_scan
                where id_so_det in (select id_so_det from master_sb_ws where id_act_cost in ($idCost))
                group by id_ppic, id_so_det
            ) pos on pos.id_ppic = pl.id_ppic_master_so
                and pos.id_so_det = pl.id_so_det
            left join (
                -- Hanya BPB dari NDS (status_input = 'nds') yang dihitung, dicocokkan per PO lewat po_fg.
                -- BPB selain nds dianggap 0 (tidak ikut).
                select id_so_det, po_fg, sum(qty) as qty_in
                from signalbit_erp.bpb
                where status_input = 'nds'
                    and id_so_det is not null
                    and bpbno_int like '%FG%'
                    and id_supplier in (357, 435)
                    and id_item != 0
                    and id_so_det in (select id_so_det from master_sb_ws where id_act_cost in ($idCost))
                group by id_so_det, po_fg
            ) fgin on fgin.id_so_det = pl.id_so_det
                and fgin.po_fg = pl.po
            where m.id_act_cost in ($idCost)
            group by pl.po, pl.id_so_det, m.id_act_cost, m.ws, m.styleno, m.color, m.size, m.price, m.curr
            order by pl.po, m.ws, m.color, pl.id_so_det
        ", $bind);

        return $rows;
    }

    // Simpan BPB FG (alur NAG): insert ke bpb SB, pola sama dengan FinishGoodPenerimaanController::saveFgIn.
    // Satu kali simpan = satu nomor BPB (FG + tempbpb fg, FG/IN/mmyy/xxxxx), po_fg per baris.
    // Qty divalidasi ulang di server: tidak boleh melebihi sisa (sudah discan - sudah masuk) per PO + WS + color + size.
    public function store(Request $request)
    {
        // TRIAL: sementara selalu gagal, tidak ada yang disimpan. Hapus baris ini kalau sudah selesai trial.
        // return response()->json(['message' => 'Trial: simpan dinonaktifkan sementara.'], 422);

        if (! $this->isNag($request->id_supplier)) {
            return $this->storeErp($request);
        }

        $request->validate([
            'id_supplier'     => 'required',
            'jenis_pemasukan' => 'required',
            'jenis_dokumen'   => 'required',
            'invoice'         => 'required',
            'reff'            => 'required',
            'grade'           => 'required|in:A,B,C',
            'lines'           => 'required|array|min:1',
            'lines.*.po'      => 'required',
            'lines.*.id_cost' => 'required|integer',
            'lines.*.color'   => 'required',
            'lines.*.size'    => 'required',
            'lines.*.qty'     => 'required|integer|min:1',
        ]);

        $lines = collect($request->lines);
        $idCost = $lines->pluck('id_cost')->map(fn ($v) => (int) $v)->unique()->implode(',');

        // Sisa per so_det dihitung ulang dari data terbaru, dikelompokkan sama seperti tampilan (PO + WS + color + size)
        $detail = collect($this->getDetailNag($idCost))
            ->groupBy(fn ($r) => $r->po . '|' . $r->id_cost . '|' . $r->color . '|' . $r->size);

        // Qty per baris tampilan dibagi ke so_det-nya, urut sesuai sisa masing-masing so_det
        $alloc = [];
        $errors = [];
        foreach ($lines as $l) {
            $key = $l['po'] . '|' . $l['id_cost'] . '|' . $l['color'] . '|' . $l['size'];
            $rows = $detail->get($key, collect());
            $sisa = (int) $rows->sum(fn ($r) => max(0, $r->qty_scan - $r->qty_masuk));
            $qty = (int) $l['qty'];
            if ($qty > $sisa) {
                $errors[] = "PO {$l['po']} {$l['color']} {$l['size']}: qty {$qty} melebihi sisa {$sisa}";
                continue;
            }
            foreach ($rows as $r) {
                $bal = max(0, $r->qty_scan - $r->qty_masuk);
                $take = min($bal, $qty);
                if ($take <= 0) {
                    continue;
                }
                $alloc[] = ['po' => $r->po, 'id_so_det' => $r->id_so_det, 'qty' => $take, 'price' => $r->price, 'curr' => $r->curr];
                $qty -= $take;
                if ($qty <= 0) {
                    break;
                }
            }
        }
        if ($errors) {
            return response()->json(['message' => implode('<br>', $errors)], 422);
        }

        $sb = DB::connection('mysql_sb');
        $user = Auth::user()->name;
        $tgl = date('Y-m-d');
        $grade = 'GRADE ' . $request->grade;

        $bpbno_int = $sb->transaction(function () use ($sb, $alloc, $request, $user, $tgl, $grade) {
            $sb->update("update tempbpb set bpbno = bpbno + 1 where mattype = 'fg'");
            $bpbno = $sb->selectOne("select * from tempbpb where mattype = 'fg'")->BPBNo;

            $kode = 'FG-IN-' . date('Y');
            $sb->update("update tempbpb set bpbno = bpbno + 1 where mattype = ?", [$kode]);
            $noTr = $sb->selectOne("select * from tempbpb where mattype = ?", [$kode])->BPBNo;
            $bpbno_int = 'FG/IN/' . date('my') . '/' . sprintf('%05s', $noTr);

            // bpb 1 baris per PO + so_det
            foreach (collect($alloc)->groupBy(fn ($a) => $a['po'] . '|' . $a['id_so_det']) as $items) {
                $a = $items->first();
                $id_item = $this->cekMasterstyle($sb, $a['id_so_det']);

                $sb->insert("insert into bpb(bpbno,bpbno_int,bpbdate,id_supplier,grade,invno,jenis_dok,id_item,id_so_det,qty,unit,price,curr,username,reffno,status_input,po_fg,jenis_trans)
                    values(?,?,?,?,?,?,?,?,?,?,'PCS',?,?,?,?,'nds',?,?)", [
                    'FG' . $bpbno, $bpbno_int, $tgl, $request->id_supplier, $grade, $request->invoice, $request->jenis_dokumen,
                    $id_item, $a['id_so_det'], $items->sum('qty'), $a['price'], $a['curr'], $user, $request->reff,
                    $a['po'], $request->jenis_pemasukan,
                ]);
            }

            return $bpbno_int;
        });

        return response()->json(['bpbno_int' => $bpbno_int, 'qty' => collect($alloc)->sum('qty')]);
    }

    // Simpan BPB FG supplier selain NAG, sama dengan ERP s_bpb_so.php (mod=31, mode=FG):
    // - bpbno     : urutkan("FG","FG")  -> tempbpb mattype 'FG' + 1, format FG00001
    // - bpbno_int : kalau mastercompany.gen_nomor_int = 'Y' -> urutkan_inq("{MAP_MAT_FG}-IN-yyyy", "{MAP_MAT_FG}/IN/mmyy"), selain itu kosong
    // - bpb       : 1 baris per so_det (qty > 0), id_item dari cek_masterstyle
    // - act_hist  : log setiap query (insert_log)
    // Field BC (tujuan, nomor_aju, bcno, no_fp, dst) belum ada di form, diisi kosong.
    private function storeErp(Request $request)
    {
        $request->validate([
            'id_supplier'       => 'required',
            'jenis_pemasukan'   => 'required',
            'jenis_dokumen'     => 'required',
            'invoice'           => 'required',
            'reff'              => 'required',
            'grade'             => 'required|in:A,B,C',
            'lines'             => 'required|array|min:1',
            'lines.*.id_so_det' => 'required|integer',
            'lines.*.id_cost'   => 'required|integer',
            'lines.*.qty'       => 'required|integer|min:1',
        ]);

        $lines = collect($request->lines);
        $idCost = $lines->pluck('id_cost')->map(fn ($v) => (int) $v)->unique()->implode(',');

        // unit/curr/price diambil dari server. Qty tidak dibatasi Bal (sama dengan ERP bpb_so.php, cek "Qty Sudah Tidak Mencukupi" dimatikan)
        $detail = collect($this->getDetailErp($idCost))->keyBy(fn ($r) => (string) $r->id);
        $errors = [];
        foreach ($lines as $l) {
            if (! $detail->has((string) $l['id_so_det'])) {
                $errors[] = "so_det {$l['id_so_det']} tidak ditemukan di WS yang dipilih";
            }
        }
        if ($errors) {
            return response()->json(['message' => implode('<br>', $errors)], 422);
        }

        $sb = DB::connection('mysql_sb');
        $user = Auth::user()->name;
        $ip = $request->ip();
        $ipHost = $ip . '/' . gethostbyaddr($ip); // Trans_Host act_hist = "{user}/{ip}/{hostname}"
        $tgl = date('Y-m-d');
        $grade = 'GRADE ' . $request->grade;

        $no = $sb->transaction(function () use ($sb, $lines, $detail, $request, $user, $ipHost, $tgl, $grade) {
            // bpbno = urutkan("FG","FG") (di ERP tanpa act_hist), counter tempbpb mattype 'fg' (sama dengan alur NAG)
            $bpbno = 'FG' . sprintf('%05d', $this->nextTempbpb($sb, 'fg', null));

            // bpbno_int = urutkan_inq(...) kalau gen_nomor_int = Y
            $bpbno_int = '';
            $comp = $sb->selectOne("select gen_nomor_int from mastercompany limit 1");
            if (($comp->gen_nomor_int ?? '') == 'Y') {
                $mat = $sb->selectOne("select nama_pilihan from masterpilihan where kode_pilihan = 'MAP_MAT_FG'")->nama_pilihan ?? 'FG';
                $n = $this->nextTempbpb($sb, $mat . '-IN-' . date('Y'), '/' . $ipHost); // insert_log($sql,'')
                $bpbno_int = $mat . '/IN/' . date('my') . '/' . sprintf('%05d', $n);
            }

            foreach ($lines as $l) {
                $r = $detail->get((string) $l['id_so_det']);
                $id_item = $this->cekMasterstyle($sb, $r->id, 'Function/' . $ipHost); // insert_log($sql,'Function')

                $this->execLog($sb, "insert into bpb (bpbno,bpbno_int,bpbdate,id_supplier,grade,invno,jenis_dok,tujuan,
                    subtujuan,nomor_aju,tanggal_aju,bcno,bcdate,no_fp,tgl_fp,nomor_kk_bc,id_item,id_so_det,
                    qty,unit,curr,price,username,reffno,jenis_trans)
                    values (?,?,?,?,?,?,?,'','','','','','','','','',?,?,?,?,?,?,?,?,?)", [
                    $bpbno, $bpbno_int, $tgl, $request->id_supplier, $grade, $request->invoice, $request->jenis_dokumen,
                    $id_item, $r->id, (int) $l['qty'], $r->unit, $r->curr, $r->price, $user, $request->reff, $request->jenis_pemasukan,
                ], $user . '/' . $ipHost);
            }

            return $bpbno_int ?: $bpbno;
        });

        return response()->json(['bpbno_int' => $no, 'qty' => $lines->sum('qty')]);
    }

    // Nomor urut tempbpb (urutkan / urutkan_inq ERP): +1, kalau mattype belum ada dibuat dengan nilai 1
    // $host null = tanpa act_hist (urutkan), diisi = dicatat (urutkan_inq)
    private function nextTempbpb($sb, $mattype, $host)
    {
        $row = $sb->selectOne("select bpbno from tempbpb where mattype = ? for update", [$mattype]);
        $n = ($row ? (int) $row->bpbno : 0) + 1;
        if ($n == 1) {
            $this->execLog($sb, "insert into tempbpb (mattype,bpbno) values (?, '1')", [$mattype], $host);
        } else {
            $this->execLog($sb, "update tempbpb set bpbno = ? where mattype = ?", [$n, $mattype], $host);
        }

        return $n;
    }

    // Jalankan query + catat ke act_hist kalau $host diisi (sama dengan insert_log ERP: kutip diganti '!')
    private function execLog($sb, $sql, $bindings, $host)
    {
        $sb->statement($sql, $bindings);
        if ($host === null) {
            return;
        }
        $text = preg_replace_callback('/\?/', function () use (&$bindings) {
            return "'" . array_shift($bindings) . "'";
        }, $sql);
        $sb->insert("insert into act_hist (Trans_Date,Trans_Desc,Trans_Host) values (?,?,?)", [
            date('Y-m-d H:i:s'), str_replace("'", '!', $text), $host,
        ]);
    }

    // id_item dari masterstyle; kalau belum ada, dibuat dari so_det (sama dengan cek_masterstyle ERP)
    // $logHost diisi -> insert masterstyle ikut dicatat ke act_hist (alur ERP)
    private function cekMasterstyle($sb, $id_so_det, $logHost = null)
    {
        $ms = $sb->selectOne("select id_item from masterstyle where id_so_det = ?", [$id_so_det]);
        if (! $ms) {
            $sql = "insert into masterstyle
                (Styleno,Buyerno,DelDate,unit,itemname,Color,Size,id_so_det,KPNo,country,goods_code)
                select Styleno,so.Buyerno,DelDate_det,sod.unit,product_item,Color,Size,sod.id,KPNo,sod.dest,product_group from
                so_det sod inner join so on sod.id_so=so.id
                inner join act_costing ac on ac.id=so.id_cost
                inner join masterproduct mp on ac.id_product=mp.id
                where sod.cancel='N' and sod.id = ?";
            if ($logHost) {
                $this->execLog($sb, $sql, [$id_so_det], $logHost);
            } else {
                $sb->insert($sql, [$id_so_det]);
            }
            $ms = $sb->selectOne("select id_item from masterstyle where id_so_det = ?", [$id_so_det]);
        }

        return $ms->id_item;
    }

    // Detail barang per WS (so_det) untuk supplier selain NAG, query sama dengan ERP ajax_bpb_so.php modeajax=view_list_jo.
    // bal = saldo awal (periode 2022-10-01) + penerimaan FG - pengeluaran SJ-FG s/d hari ini
    private function getDetailErp($idCost)
    {
        $tglSkrg = date('Y-m-d');

        return DB::connection('mysql_sb')->select("
            select sd.id,
                ac.id as id_cost,
                ac.kpno,
                ac.styleno,
                coalesce(sum(mutasi.saldo_awal), 0) + coalesce(sum(mutasi.penerimaan), 0) as qtyprev,
                coalesce(sum(mutasi.pengeluaran), 0) as qtyout,
                so.so_no,
                so.buyerno,
                sd.dest,
                sd.color,
                sd.size,
                sd.reff_no,
                sd.sku,
                sd.qty,
                so.unit,
                so.curr,
                sd.price,
                mp.product_group,
                mp.product_item
            from so_det sd
            inner join so on sd.id_so = so.id
            inner join act_costing ac on so.id_cost = ac.id
            inner join masterproduct mp on ac.id_product = mp.id
            left join (
                select id_item, id_so_det, saldo as saldo_awal, '0' as penerimaan, '0' as pengeluaran
                from saldoawal_fg
                inner join so_det sd on saldoawal_fg.id_so_det = sd.id
                inner join so on sd.id_so = so.id
                where periode = '2022-10-01' and so.id_cost in ($idCost)
                union
                select id_item, id_so_det, '0' as saldo_awal, sum(bpb.qty) as penerimaan, '0' as pengeluaran
                from bpb
                inner join so_det sd on bpb.id_so_det = sd.id
                inner join so on sd.id_so = so.id
                where bpbdate >= '2022-10-01' and bpbdate <= ?
                    and bpbno like 'FG%' and so.id_cost in ($idCost)
                group by id_item, id_so_det
                union
                select id_item, id_so_det, '0' as saldo_awal, '0' as penerimaan, sum(bppb.qty) as pengeluaran
                from bppb
                inner join so_det sd on bppb.id_so_det = sd.id
                inner join so on sd.id_so = so.id
                where bppbdate >= '2022-10-01' and bppbdate <= ?
                    and bppbno like 'SJ-FG%' and so.id_cost in ($idCost)
                group by id_item, id_so_det
            ) mutasi on mutasi.id_so_det = sd.id
            where so.id_cost in ($idCost)
            group by sd.id
            order by ac.kpno, sd.color, sd.id
        ", [$tglSkrg, $tglSkrg]);
    }

    // Daftar WS kalau pilih berdasarkan WS (semua PO), per supplier:
    // - NAG: WS yang punya data di packing_master_packing_list (sumber sama dengan getDetailNag)
    // - supplier lain: WS dari semua PO milik supplier (relasi sama dengan get_po / get_ws_by_po ERP)
    public function get_ws_by_supplier(Request $request)
    {
        // Supplier 458 (Selling - Sample Room): tampil semua WS yang depannya SM/
        if ((string) $request->id_supplier === '458') {
            $ws = DB::connection('mysql_sb')->select("
                select id, concat(kpno, '|', styleno) as text
                from act_costing
                where kpno like 'SM/%'
                order by kpno
            ");

            return response()->json($ws);
        }

        if (! $this->isNag($request->id_supplier)) {
            $ws = DB::connection('mysql_sb')->select("
                select ac.id, concat(ac.kpno, '|', ac.styleno) as text
                from po_header ph
                inner join po_item pi on pi.id_po = ph.id
                inner join jo_det jod on jod.id_jo = pi.id_jo
                inner join so on so.id = jod.id_so
                inner join act_costing ac on ac.id = so.id_cost
                where ph.id_supplier = ?
                group by ac.id, ac.kpno, ac.styleno
                order by ac.kpno
            ", [$request->id_supplier]);

            return response()->json($ws);
        }

        $ws = DB::select("
            select m.id_act_cost as id, concat(m.ws, '|', m.styleno) as text
            from (select distinct id_so_det from packing_master_packing_list) pl
            inner join master_sb_ws m on m.id_so_det = pl.id_so_det
            group by m.id_act_cost, m.ws, m.styleno
            order by m.ws
        ");

        return response()->json($ws);
    }

    // WS dari PO:
    // - NAG: packing_packing_out_scan.id_so_det -> master_sb_ws (id_po = teks PO)
    // - supplier lain: po_item.id_jo -> jo_det -> so -> act_costing (sama dengan ERP ajax_bpb_so_po.php, id_po = po_header.id)
    public function get_ws_by_po(Request $request)
    {
        if (! $this->isNag($request->id_supplier)) {
            $ws = DB::connection('mysql_sb')->select("
                select ac.id, concat(ac.kpno, '|', ac.styleno) as text
                from po_item pi
                inner join jo_det jod on jod.id_jo = pi.id_jo
                inner join so on so.id = jod.id_so
                inner join act_costing ac on ac.id = so.id_cost
                where pi.id_po = ?
                group by ac.id, ac.kpno, ac.styleno
                order by ac.kpno
            ", [$request->id_po]);

            return response()->json($ws);
        }

        $ws = DB::select("
            select m.id_act_cost as id, concat(m.ws, '|', m.styleno) as text
            from packing_packing_out_scan pos
            inner join master_sb_ws m on m.id_so_det = pos.id_so_det
            where pos.po = ?
            group by m.id_act_cost, m.ws, m.styleno
            order by m.ws
        ", [$request->id_po]);

        return response()->json($ws);
    }
}
