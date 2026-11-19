<?php

namespace App\Http\Controllers\Marketing;

use App\Http\Controllers\Controller;

use App\Services\Marketing\MarketingHistoryService;
use App\Services\Marketing\MarketingNdsApprovalService;
use App\Services\Marketing\MarketingNdsBomService;
use App\Services\Marketing\MarketingNdsCostingService;
use App\Services\Marketing\MarketingNdsDocumentService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use RuntimeException;

/** Input BOM di DB baru (koneksi mysql_mkt): 1 halaman = 1 WS, dibuka dari tombol BOM di listing Costing. */
class Marketing_BomNdsController extends Controller
{
    private MarketingNdsBomService $boms;

    public function __construct(MarketingNdsBomService $boms)
    {
        $this->boms = $boms;
        $this->middleware(function ($request, $next) {
            abort_unless(MarketingNdsCostingService::enabled(), 404);
            return $next($request);
        });
    }

    /** {soId} = SO; ?line= memilih WS (default WS pertama, TOP dulu). */
    public function edit(Request $request, int $soId)
    {
        $lines = $this->boms->lines($soId);
        abort_if($lines->isEmpty(), 404);
        $line = $lines->firstWhere('id', (int) $request->query('line')) ?? $lines->first();
        $line = DB::connection('mysql_mkt')->table('so_line')->where('id', $line->id)->first();
        $schemaReady = $this->boms->schemaReady();
        // Pindah WS dari halaman BOM (AJAX): cukup data tabel WS itu.
        if ($request->wantsJson() && $schemaReady) {
            // Master item (±5 MB) tidak dikirim ulang; browser memakai yang sudah dimuat saat halaman dibuka.
            $page = $this->pageWithLock($line, false);

            return response()->json([
                'line_id' => $line->id,
                'save_url' => route('bom-nds-save', $line->id),
                'is_edit' => (bool) ($page['header']['bom_no'] ?? null),
                'page' => $page,
            ]);
        }
        return view('marketing.bom_nds.form', [
            'soId' => $soId,
            'line' => $line,
            'lines' => $lines,
            // WS yang BOM-nya perlu diperbarui (titik oranye di tombol WS).
            'staleWs' => $schemaReady ? $lines->mapWithKeys(fn ($ws) => [$ws->id => $this->boms->needsUpdate(DB::connection('mysql_mkt')->table('so_line')->where('id', $ws->id)->first())])->all() : [],
            'schemaReady' => $schemaReady,
            'bomPage' => $schemaReady ? $this->pageWithLock($line) : null,
            'costingImages' => app(\App\Services\Marketing\MarketingSalesOrderService::class)->costingImages(
                (int) DB::connection('mysql_mkt')->table('sales_orders')->where('id', $soId)->value('costing_id')
            ),
            'page' => 'dashboard-marketing',
            'subPageGroup' => 'marketing-master',
            'subPage' => 'marketing-master-costing',
            'containerFluid' => true,
        ]);
    }

    // ===== Dokumen baca-saja. ?line= satu WS; tanpa line = semua WS di SO (PDF / Excel).

    public function detail(Request $request, int $soId)
    {
        $lines = $this->boms->lines($soId);
        abort_if($lines->isEmpty() || !$this->boms->schemaReady(), 404);
        $line = $lines->firstWhere('id', (int) $request->query('line')) ?? $lines->first();
        // Tombol Edit hanya kalau masih ada WS yang bisa diedit (BOM belum dibuat, belum Approved, atau sudah di-unlock).
        // Semua WS Approved / menunggu unlock -> Edit disembunyikan. Edit membuka WS ini kalau bisa, kalau tidak WS lain yang bisa.
        $editable = $lines->filter(function ($ws) {
            $bom = $this->boms->activeBom((int) $ws->id);

            return !$bom || !MarketingNdsApprovalService::ready('bom') || $this->approvals()->state('bom', (int) $bom->id)['can_edit'];
        });
        $editLine = $editable->firstWhere('id', $line->id) ?? $editable->first();

        return view('marketing.bom_nds.detail', [
            'soId' => $soId,
            'lines' => $lines,
            'line' => $line,
            'editLineId' => $editLine->id ?? null,
            'doc' => app(MarketingNdsDocumentService::class)->boms($soId, (int) $line->id)[0],
            'page' => 'dashboard-marketing',
            'subPageGroup' => 'marketing-master',
            'subPage' => 'marketing-master-costing',
            'containerFluid' => true,
        ]);
    }

    private function documents(Request $request, int $soId): array
    {
        abort_if(!$this->boms->schemaReady(), 404);
        $docs = app(MarketingNdsDocumentService::class)->boms($soId, $request->query('line') ? (int) $request->query('line') : null);
        abort_if(!$docs, 404);

        return $docs;
    }

    private function fileName(array $docs): string
    {
        return 'BOM_' . (count($docs) === 1 ? $docs[0]['header']['no_ws'] : $docs[0]['header']['so_no']);
    }

    public function pdf(Request $request, int $soId)
    {
        $docs = $this->documents($request, $soId);

        return $this->streamPdf($docs, str_replace('/', '_', $this->fileName($docs)) . '.pdf');
    }

    /** Batch Print dari Marketing List: BOM beberapa SO digabung jadi satu PDF (?ids[]= id SO). SO tanpa BOM dilewati. */
    public function pdfBatch(Request $request)
    {
        $validated = $request->validate(['ids' => ['required', 'array', 'min:1', 'max:100'], 'ids.*' => ['required', 'integer', 'distinct']]);
        abort_if(!$this->boms->schemaReady(), 404);
        $docs = [];
        foreach ($validated['ids'] as $soId) {
            array_push($docs, ...app(MarketingNdsDocumentService::class)->boms((int) $soId, null));
        }
        abort_if(!$docs, 404, 'Belum ada BOM di SO yang dipilih.');

        return $this->streamPdf($docs, 'BOM_Batch.pdf');
    }

    private function streamPdf(array $docs, string $fileName)
    {
        $make = fn (array $list) => Pdf::loadView('marketing.bom_nds.pdf', ['docs' => $list])->setOption('isFontSubsettingEnabled', true)->setPaper('a4', 'landscape');

        // Footer per dokumen: "BOM no Rev n (WS)" + "Halaman X dari Y" dihitung per WS. Kalau isinya beberapa WS,
        // jumlah halaman tiap WS dihitung dulu dengan merender WS itu sendiri.
        $ranges = [];
        $start = 1;
        foreach ($docs as $doc) {
            $pages = 1;
            if (count($docs) > 1) {
                $single = $make([$doc]);
                $single->render();
                $pages = $single->getDomPDF()->getCanvas()->get_page_count();
            }
            $ranges[] = [$start, $pages, 'BOM ' . $doc['print']['no'] . ' Rev ' . $doc['print']['revision'] . ' (' . $doc['print']['no_ws'] . ')'];
            $start += $pages;
        }
        $pdf = $make($docs);
        $pdf->render();
        $dompdf = $pdf->getDomPDF();
        $font = $dompdf->getFontMetrics()->getFont('Helvetica');
        $dompdf->getCanvas()->page_script(function (int $page, int $count, $canvas, $metrics) use ($ranges, $font) {
            $range = collect($ranges)->last(fn ($r) => $r[0] <= $page) ?? [1, $count, ''];
            [$first, $pages, $label] = count($ranges) > 1 ? $range : [1, $count, $range[2]];
            $y = $canvas->get_height() - 24;
            $color = [0.2, 0.2, 0.2];
            $canvas->text(28, $y, $label, $font, 6.5, $color);
            $text = 'Halaman ' . min($page - $first + 1, $pages) . ' dari ' . $pages;
            $canvas->text($canvas->get_width() - 28 - $metrics->getTextWidth($text, $font, 6.5), $y, $text, $font, 6.5, $color);
        });

        return $pdf->stream($fileName);
    }

    public function excel(Request $request, int $soId)
    {
        $docs = $this->documents($request, $soId);

        return MarketingNdsDocumentService::download(app(MarketingNdsDocumentService::class)->bomExcel($docs), $this->fileName($docs));
    }

    /**
     * Export Excel tab Preview di halaman Input BOM (fast excel). Isi dikirim dari tabel preview yang sedang tampil:
     * rows[] = {t: h|sec|g|tot|'' , c: [[teks/angka, colspan, angka?]]}.
     */
    public function previewExcel(Request $request)
    {
        $request->validate([
            'name' => ['nullable', 'string', 'max:120'],
            'rows' => ['required', 'array', 'max:50000'],
            'rows.*.c' => ['required', 'array'],
        ]);
        $x = (new \App\Services\Marketing\MarketingXlsx())->sheet('Preview BOM');
        $width = [];
        foreach (array_values($request->input('rows')) as $i => $row) {
            $r = $i + 1;
            $col = 1;
            $type = (string) ($row['t'] ?? '');
            foreach ($row['c'] as [$value, $span, $num]) {
                $span = max(1, (int) $span);
                $from = \App\Services\Marketing\MarketingXlsx::col($col);
                $to = \App\Services\Marketing\MarketingXlsx::col($col + $span - 1);
                if ($span > 1) {
                    $x->merge("{$from}{$r}:{$to}{$r}");
                }
                $isNum = $num && is_numeric($value);
                $x->set($from . $r, $isNum ? $value + 0 : (string) $value, $isNum ? ['h' => 'right', 'format' => '#,##0.###'] : []);
                if ($span === 1) {
                    $width[$col] = max($width[$col] ?? 6, mb_strlen((string) $value) + 2);
                }
                $col += $span;
            }
            $last = \App\Services\Marketing\MarketingXlsx::col($col - 1);
            $x->border("A{$r}:{$last}{$r}", 'all', 'thin', 'B8C2D1');
            if ($type === 'h') {
                $x->style("A{$r}:{$last}{$r}", ['bold' => true, 'fill' => 'CFE2FF', 'h' => 'center', 'v' => 'center', 'wrap' => true]);
            } elseif ($type === 'sec') {
                $x->style("A{$r}:{$last}{$r}", ['bold' => true, 'fill' => 'C7DCFF']);
            } elseif ($type === 'tot') {
                $x->style("A{$r}:{$last}{$r}", ['bold' => true, 'fill' => 'FFD6DC']);
            }
        }
        foreach ($width as $col => $w) {
            $x->width(\App\Services\Marketing\MarketingXlsx::col($col), min(45, $w));
        }
        $x->freeze(1);
        $name = preg_replace('/[^A-Za-z0-9_ -]+/', '-', trim((string) $request->input('name'))) ?: 'BOM';

        return $x->download("Preview {$name}.xlsx");
    }

    public function save(Request $request, int $lineId)
    {
        // WS yang sudah dibatalkan tidak bisa disimpan BOM-nya.
        $line = DB::connection('mysql_mkt')->table('so_line')->where('id', $lineId)
            ->where(fn ($q) => $q->whereNull('cancel')->orWhere('cancel', '!=', 'Y'))->first();
        abort_if(!$line, 404);
        // BOM dibuat dari Costing yang sudah Approved (bukan yang sedang di-unlock / menunggu approval ulang).
        $costingId = (int) DB::connection('mysql_mkt')->table('sales_orders')->where('id', $line->sales_order_id)->value('costing_id');
        if ($costingId && ($message = $this->approvals()->soBlockMessage($costingId))) {
            return response()->json(['message' => str_replace('SO baru bisa dibuat', 'BOM baru bisa disimpan', $message)], 423);
        }
        // BOM Approved terkunci (Request Unlock dulu); BOM yang di-unlock / Return / Cancel kembali Awaiting setelah disimpan.
        $existing = $this->boms->activeBom($lineId);
        if ($existing && ($message = $this->approvals()->lockedMessage('bom', (int) $existing->id))) {
            return response()->json(['message' => $message], 423);
        }
        if (!$this->boms->schemaReady()) {
            return response()->json(['message' => 'Tabel BOM belum siap. Hubungi IT untuk menjalankan query Input BOM.'], 422);
        }
        $request->validate([
            'rows' => ['present', 'array'],
            'rows.*.cm' => ['required', 'integer'],
            'charges' => ['present', 'array'],
            'charges.*.cc' => ['required', 'integer'],
        ]);

        $isNew = !$this->boms->activeBom($lineId);
        try {
            $bom = $this->boms->save($line, $request->input('rows', []), $request->input('charges', []));
        } catch (QueryException $e) {
            Log::error('Save BOM nds gagal', ['so_line_id' => $lineId, 'error' => $e->getMessage()]);

            return response()->json(['message' => 'BOM gagal disimpan. Coba lagi; kalau masih gagal, hubungi IT.'], 500);
        } catch (RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        // Histori ditulis setelah simpan berhasil; gagal menulis log tidak membatalkan simpan.
        app(MarketingHistoryService::class)->bom((int) $bom->costings_id, (int) $bom->id, $isNew ? 'created' : 'updated', (string) $bom->bom_no, (string) $line->no_ws);
        // Approval: BOM Return / Cancel / Unlocked yang disimpan kembali Awaiting (unlock ditutup, revisi naik).
        if (!$isNew) {
            $this->approvals()->markEdited('bom', (int) $bom->id);
        }

        // Save dari tombol Save: kembali ke listing Costing, notifikasinya ditampilkan di sana.
        if ($request->boolean('to_listing')) {
            session()->flash('success_title', 'BOM menunggu persetujuan');
            session()->flash('success', "BOM {$bom->bom_no} ({$line->no_ws}) disimpan. Worksheet bisa dibuat setelah Marketing Manager menyetujui BOM ini.");

            return response()->json(['message' => "BOM {$bom->bom_no} disimpan.", 'bom_no' => $bom->bom_no, 'redirect' => route('master-costing')]);
        }

        return response()->json(['message' => "BOM {$bom->bom_no} disimpan.", 'bom_no' => $bom->bom_no, 'data' => $this->pageWithLock($line, false)]);
    }

    private function approvals(): MarketingNdsApprovalService
    {
        return app(MarketingNdsApprovalService::class);
    }

    /** Data halaman BOM + kondisi approval/lock BOM WS ini (lihat MarketingNdsApprovalService::state). */
    private function pageWithLock(object $line, bool $withMasters = true): array
    {
        $page = $this->boms->page($line, $withMasters);
        $bom = $this->boms->activeBom((int) $line->id);
        $lock = ['state' => 'editable', 'status' => 'awaiting', 'can_edit' => true, 'bom_id' => null, 'request' => null, 'unlock' => null, 'rev' => 1];
        if ($bom && MarketingNdsApprovalService::ready('bom')) {
            $lock = ['bom_id' => (int) $bom->id] + $this->approvals()->state('bom', (int) $bom->id);
        }
        $page['lock'] = $lock;

        return $page;
    }
}
