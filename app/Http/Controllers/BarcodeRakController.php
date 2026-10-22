<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Support\BarcodeLabelErp;
use Mpdf\HTMLParserMode;
use Mpdf\Mpdf;
use Mpdf\Output\Destination;

// Cetak ulang label barcode roll fabric per rak. Stok diambil dari view data_stock_fabric (saldo per
// barcode + rak, sama dengan yang dipakai pengeluaran & mutasi lokasi), sehingga qty dan nama rak di
// label selalu posisi terakhir, termasuk roll yang sudah pindah rak lewat mutasi lokasi.
class BarcodeRakController extends Controller
{
    // Batas label per sekali print
    public const MAX_LABEL = 1000;

    public function index(Request $request)
    {
        if ($request->ajax()) {
            return response()->json(['data' => $this->rakSummary(), 'waktu' => now()->format('d M Y H:i')]);
        }

        return view('barcode-rak.index', ['page' => 'dashboard-warehouse', 'maxLabel' => self::MAX_LABEL]);
    }

    public function detail(Request $request)
    {
        $request->validate(['kode_lok' => 'required|string']);

        return response()->json(['data' => $this->labelRows([$request->kode_lok])]);
    }

    // PDF lewat link GET supaya tab PDF bisa di-refresh / di-bookmark: tiap dibuka, label dibuat ulang dari
    // stok terbaru. Parameter dipisah koma:
    //   rak     = kode rak (wajib)
    //   roll    = hanya barcode ini, atau
    //   kecuali = semua roll di rak kecuali barcode ini (dipakai bila lebih pendek dari daftar roll)
    public function pdf(Request $request)
    {
        $pecah = fn($v) => array_values(array_unique(array_filter(array_map('trim', explode(',', (string) $v)), 'strlen')));
        $kodeLok = $pecah($request->query('rak'));
        $roll = $pecah($request->query('roll'));
        $kecuali = $pecah($request->query('kecuali'));

        if (count($kodeLok) == 0) {
            return $this->halamanPesan('Rak belum dipilih.', 422);
        }

        $dataItem = $this->labelRows($kodeLok, $roll ?: null, $kecuali);

        if (count($dataItem) == 0) {
            return $this->halamanPesan('Tidak ada roll dengan stok di rak ' . implode(', ', $kodeLok) . '. Stoknya mungkin sudah keluar atau dipindah ke rak lain.', 404);
        }
        if (count($dataItem) > self::MAX_LABEL) {
            return $this->halamanPesan('Maksimal ' . number_format(self::MAX_LABEL) . ' label per sekali print, link ini berisi ' . number_format(count($dataItem)) . ' label. Kurangi pilihan rak.', 422);
        }

        set_time_limit(900);

        $fileName = str_replace(['/', '\\', '"'], '_', 'barcode-rak-' . (count($kodeLok) == 1 ? $kodeLok[0] : count($kodeLok) . '-rak') . '.pdf');

        return response($this->renderPdf($dataItem), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . $fileName . '"',
            'Cache-Control' => 'no-store',
        ]);
    }

    // Pesan error di tab PDF (link dibuka langsung, jadi bukan JSON). Meta brk-error dibaca halaman daftar rak.
    private function halamanPesan($pesan, $status)
    {
        return response()->view('barcode-rak.pesan', ['pesan' => $pesan], $status);
    }

    // Label dibuat seperti label BPB di ERP (erp/pages/forms/pdfBarcode_whs.php): mPDF, kertas 100 x 80 mm,
    // DejaVu Sans Condensed Bold 6 pt, dan gambar barcode yang sama persis (BarcodeLabelErp).
    // Tiap label ditulis terpisah (bukan satu HTML besar) supaya tidak kena batas pcre.backtrack_limit mPDF.
    private function renderPdf(array $dataItem)
    {
        $tempDir = storage_path('app/mpdf');
        if (!is_dir($tempDir)) {
            mkdir($tempDir, 0775, true);
        }

        $mpdf = new Mpdf([
            'tempDir' => $tempDir,
            'format' => [100, 80],
            'margin_left' => 3.13, 'margin_right' => 1.93, 'margin_top' => 2.3, 'margin_bottom' => 0,
            'margin_header' => 0, 'margin_footer' => 0,
            'default_font' => 'dejavusanscondensed', 'default_font_size' => 6,
            'shrink_tables_to_fit' => 1, // lebar tabel sudah pas 94,74 mm; jangan dikecilkan otomatis
            'autoPageBreak' => false,    // satu label selalu satu halaman, isi panjang tidak tumpah ke halaman berikutnya
        ]);
        $mpdf->SetTitle('Barcode per Rak');
        $mpdf->WriteHTML(view('barcode-rak.pdf.label-style')->render(), HTMLParserMode::HEADER_CSS);

        foreach (array_values($dataItem) as $i => $item) {
            if ($i > 0) {
                $mpdf->AddPage();
            }
            // Gambar barcode tampil di ukuran aslinya pada 96 dpi seperti ERP (165 px = 43,66 mm untuk kode
            // 10 karakter). Kode yang lebih pendek (mis. F214178) gambarnya lebih sempit, jangan dipaksa melebar.
            $png = BarcodeLabelErp::png($item->id);
            $item->barcode_png = base64_encode($png);
            $item->barcode_lebar = round(getimagesizefromstring($png)[0] * 25.4 / 96, 2);
            $item->rapat = $this->barisTambahan($mpdf, $item) > 2;
            $mpdf->WriteHTML(view('barcode-rak.pdf.label', ['d' => $item])->render(), HTMLParserMode::HTML_BODY);
        }

        return $mpdf->Output('', Destination::STRING_RETURN);
    }

    // Jumlah baris tambahan karena isian terlipat (6 pt). Ruang kosong di bawah kotak barcode muat +/-2 baris;
    // label yang lebih panjang (nama produk sampai 200-an karakter, +/-5% stok) dirapatkan jaraknya di template
    // supaya kotak barcode tidak terpotong di bawah halaman.
    private function barisTambahan(Mpdf $mpdf, $item)
    {
        $mpdf->SetFont('dejavusanscondensed', 'B', 6);
        $baris = fn($teks, $lebar) => max(1, (int) ceil($mpdf->GetStringWidth((string) $teks) / $lebar)) - 1;

        // Lebar isi (mm): baris Product-Supplier selebar label, baris No BPB-Style di samping kotak Grouping
        $tambahan = 0;
        foreach (['item_desc', 'kode_item', 'id_item', 'supplier'] as $field) {
            $tambahan += $baris($item->$field, 67.8);
        }
        foreach (['no_dok', 'no_sj', 'no_po', 'no_ws', 'styleno'] as $field) {
            $tambahan += $baris($item->$field, 37.7);
        }

        return $tambahan;
    }

    // Ringkasan stok per rak: gedung, jumlah roll, qty per satuan, serta daftar WS/buyer/style untuk pencarian
    private function rakSummary()
    {
        $rows = DB::connection('mysql_sb')->select("select kode_lok, satuan, kpno, buyer, styleno, count(*) jml_roll, sum(sal_akhir) qty from data_stock_fabric group by kode_lok, satuan, kpno, buyer, styleno");
        $area = DB::connection('mysql_sb')->table('whs_master_lokasi')->pluck('area_lok', 'kode_lok');

        $rak = [];
        foreach ($rows as $row) {
            $key = $row->kode_lok;
            $rak[$key] ??= ['kode_lok' => $key, 'area' => $area[$key] ?? null, 'jml_roll' => 0, 'qty' => [], 'ws' => [], 'buyer' => [], 'style' => []];
            $rak[$key]['jml_roll'] += $row->jml_roll;
            $rak[$key]['qty'][$row->satuan] = ($rak[$key]['qty'][$row->satuan] ?? 0) + $row->qty;
            foreach (['ws' => $row->kpno, 'buyer' => $row->buyer, 'style' => $row->styleno] as $field => $value) {
                if ($value) {
                    $rak[$key][$field][$value] = true;
                }
            }
        }

        ksort($rak, SORT_NATURAL);

        return array_values(array_map(function ($r) {
            ksort($r['qty']);
            $r['qty'] = array_map(fn($qty, $satuan) => ['satuan' => $satuan, 'qty' => round($qty, 2)], $r['qty'], array_keys($r['qty']));
            foreach (['ws', 'buyer', 'style'] as $field) {
                $r[$field] = array_keys($r[$field]);
                sort($r[$field], SORT_NATURAL);
            }

            return $r;
        }, $rak));
    }

    // Data label per roll dengan field yang sama dengan label print per BPB (inmaterial.pdf.print-barcode).
    // No BPB, supplier, PO dan "Of" roll diambil dari BPB asal barcode (penerimaan pertama), karena setelah
    // mutasi lokasi no_dok di data_stock_fabric berisi nomor mutasi.
    private function labelRows(array $kodeLok, ?array $noBarcode = null, array $kecuali = [])
    {
        $bindings = $kodeLok;
        $where = "s.kode_lok in (" . implode(',', array_fill(0, count($kodeLok), '?')) . ")";
        if ($noBarcode) {
            $where .= " and s.no_barcode in (" . implode(',', array_fill(0, count($noBarcode), '?')) . ")";
            $bindings = array_merge($bindings, $noBarcode);
        }
        if ($kecuali) {
            $where .= " and s.no_barcode not in (" . implode(',', array_fill(0, count($kecuali), '?')) . ")";
            $bindings = array_merge($bindings, $kecuali);
        }

        $stok = DB::connection('mysql_sb')->select("select s.no_barcode, s.kode_lok, s.no_dok, s.tgl_dok, s.supplier, s.kpno, s.styleno, s.id_item, s.itemdesc, mi.goods_code, s.no_roll, s.no_roll_buyer, s.no_lot, s.satuan, round(s.sal_akhir, 2) qty from data_stock_fabric s left join masteritem mi on mi.id_item = s.id_item where $where", $bindings);

        $asal = [];
        foreach (array_chunk(array_unique(array_column($stok, 'no_barcode')), 1000) as $chunk) {
            $rows = DB::connection('mysql_sb')->select("select b.no_barcode, b.no_dok, a.tgl_dok, a.supplier, a.no_po, a.no_invoice, b.no_lot, b.no_roll_buyer from whs_lokasi_inmaterial b inner join whs_inmaterial_fabric a on a.no_dok = b.no_dok where b.status = 'Y' and a.status != 'Cancel' and b.no_barcode in (" . implode(',', array_fill(0, count($chunk), '?')) . ") order by b.id", $chunk);
            foreach ($rows as $row) {
                $asal[$row->no_barcode] ??= $row;
            }
        }

        // Jumlah roll per lot di BPB asal, untuk teks "No Roll Of Jumlah" seperti label saat penerimaan
        $jmlRoll = [];
        foreach (array_chunk(array_unique(array_column($asal, 'no_dok')), 500) as $chunk) {
            $rows = DB::connection('mysql_sb')->select("select no_dok, no_lot, count(distinct no_barcode) jml from whs_lokasi_inmaterial where status = 'Y' and no_dok in (" . implode(',', array_fill(0, count($chunk), '?')) . ") group by no_dok, no_lot", $chunk);
            foreach ($rows as $row) {
                $jmlRoll[$row->no_dok . '|' . $row->no_lot] = $row->jml;
            }
        }

        $labels = array_map(function ($s) use ($asal, $jmlRoll) {
            $a = $asal[$s->no_barcode] ?? null;
            $ofRoll = $a ? ($jmlRoll[$a->no_dok . '|' . $a->no_lot] ?? null) : null;

            return (object) [
                'id' => $s->no_barcode,
                'kode_lok' => $s->kode_lok,
                'item_desc' => $s->itemdesc,
                'kode_item' => $s->goods_code,
                'id_item' => $s->id_item,
                'supplier' => $a ? $a->supplier : ($s->supplier == 'Mutasi Lokasi' ? '-' : $s->supplier),
                'no_bpb' => $a ? $a->no_dok : $s->no_dok,
                'no_dok' => $a ? $a->no_dok . ' | ' . $a->tgl_dok : $s->no_dok . ' | ' . $s->tgl_dok,
                'no_sj' => $a ? $a->no_invoice : '-', // "No SJ" di label ERP = no_invoice BPB
                'no_po' => $a ? $a->no_po : '-',
                'no_ws' => $s->kpno,
                'styleno' => $s->styleno,
                'no_roll' => $s->no_roll,
                'roll' => $ofRoll ? $s->no_roll . ' Of ' . $ofRoll : $s->no_roll,
                'no_roll_buyer' => ($a && $a->no_roll_buyer) ? $a->no_roll_buyer : $s->no_roll_buyer,
                'no_lot' => $s->no_lot,
                'qty' => number_format((float) $s->qty, 2, '.', ''),
                'satuan' => $s->satuan,
            ];
        }, $stok);

        usort($labels, fn($x, $y) => strnatcmp($x->kode_lok, $y->kode_lok)
            ?: strnatcmp((string) $x->no_ws, (string) $y->no_ws)
            ?: strcmp((string) $x->item_desc, (string) $y->item_desc)
            ?: strnatcmp((string) $x->no_lot, (string) $y->no_lot)
            ?: strnatcmp((string) $x->no_roll, (string) $y->no_roll));

        return $labels;
    }
}
