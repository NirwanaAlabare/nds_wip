<?php

namespace App\Http\Controllers;

use App\Services\MarketingNdsCostingService;
use App\Services\MarketingNdsDocumentService;
use App\Services\MarketingNdsApprovalService;
use App\Services\MarketingSalesOrderService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class Marketing_SalesOrderController extends Controller
{
    private MarketingSalesOrderService $orders;

    public function __construct(MarketingSalesOrderService $orders)
    {
        $this->orders = $orders;
        $this->middleware(function ($request, $next) {
            abort_unless(MarketingNdsCostingService::enabled(), 404);
            return $next($request);
        });
    }

    public function create(Request $request)
    {
        $costingId = (int) $request->id_costing;
        abort_unless(DB::connection('mysql_mkt')->table('costings')->where('id', $costingId)->exists(), 404);
        // Alur approval: SO baru boleh dibuat dari Costing yang sudah Approved.
        if ($message = $this->approvals()->soBlockMessage($costingId)) {
            return redirect()->route('master-costing')->with('warning', $message);
        }
        if ($issues = app(MarketingNdsCostingService::class)->cmtIssues($costingId)) {
            return redirect()->to(route('edit-costing', $costingId) . '#cmt')->with('error', implode(' ', $issues));
        }

        return view('marketing.so_nds.form', $this->pageData() + [
            'so' => null,
            'costingId' => $costingId,
            'shipModeId' => null,
            'costing' => $costing = $this->orders->costing($costingId),
            'setNames' => $this->orders->setNames($costing),
            'setGroups' => $this->orders->setGroups($costing),
            'costingImages' => $this->orders->costingImages($costingId),
        ]);
    }

    /** {id} = id SO, atau "new" untuk SO yang belum tersimpan (Costing-nya dari id_costing). */
    private function order(Request $request, $id): object
    {
        if ($id === 'new') {
            $costingId = (int) $request->input('id_costing');
            abort_unless(DB::connection('mysql_mkt')->table('costings')->where('id', $costingId)->exists(), 404);

            return $this->orders->draft($costingId);
        }
        $so = $this->orders->find((int) $id);
        abort_if(!$so, 404);

        return $so;
    }

    private function approvals(): MarketingNdsApprovalService
    {
        return app(MarketingNdsApprovalService::class);
    }

    /**
     * SO tidak punya approval: bebas diedit, kecuali WS-nya sudah dipakai di Marker (hubungi IT). Endpoint yang mengubah SO
     * membalas 423 dengan pesannya. SO baru (belum tersimpan) dicek dari Costing-nya: harus sudah Approved.
     */
    private function lockedResponse(object $so)
    {
        $message = $so->id
            ? $this->approvals()->soMarkerMessage((int) $so->id)
            : $this->approvals()->soBlockMessage((int) $so->costing_id);

        return $message ? response()->json(['status' => 423, 'message' => $message, 'errors' => [$message]], 423) : null;
    }

    public function edit($id)
    {
        $so = $this->orders->find((int) $id);
        abort_if(!$so, 404);
        if ($message = $this->approvals()->soMarkerMessage((int) $so->id)) {
            return redirect()->route('so-nds-detail', $so->id)->with('warning', $message);
        }
        if ($issues = app(MarketingNdsCostingService::class)->cmtIssues((int) $so->costing_id)) {
            return redirect()->to(route('edit-costing', $so->costing_id) . '#cmt')->with('error', implode(' ', $issues));
        }
        $freshCopy = $this->orders->startEdit($so);
        // Token halaman edit terakhir: discard dari halaman lama (pagehide saat reload) yang datang belakangan diabaikan,
        // supaya so_tmp halaman baru tidak ikut terhapus (tabel jadi mode baca saja tanpa checklist & tombol).
        $editToken = Str::random(20);
        Cache::put($this->editTokenKey((int) $so->id), $editToken, now()->addHours(12));

        return view('marketing.so_nds.form', $this->pageData() + [
            'so' => $so,
            'freshCopy' => $freshCopy,
            'editToken' => $editToken,
            'costingId' => null,
            'shipModeId' => $this->orders->shipModeId($so),
            'costing' => $costing = $this->orders->costing((int) $so->costing_id),
            'setNames' => $this->orders->setNames($costing),
            'setGroups' => $this->orders->setGroups($costing),
            'costingImages' => $this->orders->costingImages((int) $so->costing_id),
        ]);
    }

    public function upload(Request $request, $id)
    {
        $so = $this->order($request, $id);
        if ($locked = $this->lockedResponse($so)) {
            return $locked;
        }
        $request->validate(['file_so' => ['required', 'mimes:xls,xlsx', 'max:51200']]);

        $errors = $this->orders->upload($so, $request->file('file_so')->getPathname());
        if ($errors) {
            // Isi baris Excel yang disebut di error, supaya user langsung tahu baris mana yang harus diperbaiki.
            preg_match_all('/^Baris (\d+)/m', implode("\n", $errors), $mentioned);
            $errorRows = array_intersect_key($this->orders->uploadRows, array_flip(array_map('intval', $mentioned[1])));

            return response()->json(['status' => 422, 'message' => 'Upload gagal. Perbaiki file Excel lalu upload ulang.', 'errors' => $errors, 'error_rows' => (object) $errorRows], 422);
        }

        return response()->json(['status' => 200, 'message' => 'Excel masuk ke Sales Order Detail. Klik Save untuk menyimpan.']);
    }

    public function discard(Request $request, $id)
    {
        $so = $this->order($request, $id);
        $token = (string) $request->input('edit_token', '');
        if ($so->id && ($current = Cache::get($this->editTokenKey((int) $so->id))) && !hash_equals($current, $token)) {
            // Sudah ada halaman edit yang lebih baru (mis. reload): salinan so_tmp-nya dipakai halaman itu.
            return response()->json(['status' => 200, 'message' => 'Diabaikan, halaman edit yang lebih baru sedang dibuka.']);
        }

        $this->orders->discard($so);

        return response()->json(['status' => 200, 'message' => 'Perubahan dibatalkan.']);
    }

    private function editTokenKey(int $soId): string
    {
        return 'so_nds_edit_token:' . Auth::id() . ':' . $soId;
    }

    /** Dicek saat tombol SO di listing diklik, supaya harga CMT yang baru diisi di tab lain langsung terbaca. */
    public function cmtCheck($id)
    {
        return response()->json(['issues' => app(MarketingNdsCostingService::class)->cmtIssues((int) $id)]);
    }

    public function costingPreview($id)
    {
        $data = app(MarketingNdsCostingService::class)->soPreview((int) $id);

        return $data ? response()->json(['status' => 200, 'data' => $data]) : response()->json(['status' => 404, 'message' => 'Costing tidak ditemukan.']);
    }

    public function rows(Request $request, $id)
    {
        $so = $this->order($request, $id);
        // Muat pertama halaman Edit: kalau salinan so_tmp sempat terhapus (discard dari tab/halaman lama), salin ulang
        // supaya tabel tetap bisa diedit (checklist & tombol hapus muncul).
        if ($so->id && $request->boolean('init')) {
            $this->orders->startEdit($so);
        }

        return response()->json($this->orders->rows($so));
    }

    public function updateCells(Request $request, $id)
    {
        $so = $this->order($request, $id);
        if ($locked = $this->lockedResponse($so)) {
            return $locked;
        }
        $validated = $request->validate([
            'changes' => ['required', 'array', 'min:1'],
            'changes.*.id' => ['required', 'integer'],
            'changes.*.field' => ['required', 'string'],
            'changes.*.value' => ['nullable'],
        ]);
        $errors = $this->orders->updateCells($so, $validated['changes']);

        return response()->json([
            'status' => $errors ? 207 : 200,
            'errors' => $errors,
            'message' => $errors ? count($errors) . ' sel tidak disimpan.' : 'Perubahan tersimpan.',
        ]);
    }

    public function addRow(Request $request, $id)
    {
        $so = $this->order($request, $id);
        if ($locked = $this->lockedResponse($so)) {
            return $locked;
        }
        $validated = $request->validate(['source_id' => ['nullable', 'integer']]);
        $rowId = $this->orders->addRow($so, $validated['source_id'] ?? null);
        if (!$rowId) {
            return response()->json(['message' => 'Baris belum bisa ditambahkan. Coba lagi.'], 422);
        }

        return response()->json(['message' => 'Baris baru ditambahkan.', 'id' => $rowId]);
    }

    public function deleteRows(Request $request, $id)
    {
        $so = $this->order($request, $id);
        if ($locked = $this->lockedResponse($so)) {
            return $locked;
        }
        $validated = $request->validate(['ids' => ['required', 'array', 'min:1'], 'ids.*' => ['integer']]);
        if (!$this->orders->deleteRows($so, $validated['ids'])) {
            return response()->json(['message' => 'Baris sudah tidak tersedia.'], 404);
        }

        return response()->json(['message' => 'Baris berhasil dihapus.']);
    }

    public function save(Request $request, $id)
    {
        $so = $this->order($request, $id);
        if ($locked = $this->lockedResponse($so)) {
            return $locked;
        }
        $validated = $request->validate($this->headerRules());
        $shipMode = $request->validate(['ship_mode_id' => ['nullable', 'integer', 'exists:mysql_mkt.master_ship_modes,id']])['ship_mode_id'] ?? null;

        // SO baru dibuat sekaligus dengan detailnya; SO yang sudah ada headernya tetap tersimpan walau detail gagal.
        if ($so->id) {
            DB::connection('mysql_mkt')->table('sales_orders')->where('id', $so->id)->update($validated + ['updated_at' => now()]);
        }

        $oldWorksheets = $so->id ? DB::connection('mysql_mkt')->table('so_line')->where('sales_order_id', $so->id)->pluck('no_ws')->all() : [];
        $errors = $this->orders->commit($so, $shipMode ? (int) $shipMode : null, $validated);
        if ($errors) {
            return response()->json(['status' => 422, 'message' => $id === 'new' ? 'Sales Order belum tersimpan.' : 'Header disimpan, detail belum tersimpan.', 'errors' => $errors], 422);
        }

        // commit() mengisi $so->id untuk SO baru, jadi daftar WS bisa dibaca di sini.
        $worksheets = $so->id ? array_map(fn ($ws) => $ws + ['is_new' => !in_array($ws['no_ws'], $oldWorksheets, true)], $this->orders->worksheetSummary((int) $so->id)) : [];

        return response()->json(['status' => 200, 'message' => 'Sales Order disimpan.', 'so_no' => $so->so_no ?? null, 'worksheets' => $worksheets]);
    }

    private function headerRules(): array
    {
        return [
            'so_date' => ['required', 'date'],
            'so_type' => ['required', 'in:FOB,CMT'],
            'currency_id' => ['required', 'integer', 'exists:mysql_mkt.master_currencies,id'],
            'payment_terms_id' => ['required', 'integer', 'exists:mysql_mkt.master_payment_terms,id'],
            'notes' => ['nullable', 'string'],
        ];
    }

    // ===== Dokumen baca-saja

    private function document($id): array
    {
        $doc = app(MarketingNdsDocumentService::class)->salesOrder((int) $id);
        abort_if(!$doc, 404);

        return $doc;
    }

    public function detail($id)
    {
        return view('marketing.so_nds.detail', [
            'doc' => $this->document($id),
            // SO tanpa approval: tombol Edit memunculkan pop-up kalau WS-nya sudah dipakai di Marker.
            'markerMessage' => $this->approvals()->soMarkerMessage((int) $id),
            'page' => 'dashboard-marketing',
            'subPageGroup' => 'marketing-master',
            'subPage' => 'marketing-master-so',
            'containerFluid' => true,
        ]);
    }

    public function pdf($id)
    {
        $print = app(MarketingNdsDocumentService::class)->salesOrderPrint((int) $id);
        abort_if(!$print, 404);

        return $this->streamPdf([$print], str_replace('/', '-', 'SalesOrder_' . $print['no']) . '.pdf');
    }

    /** Batch Print dari Marketing List: beberapa SO digabung jadi satu PDF (?ids[]= id SO). */
    public function pdfBatch(Request $request)
    {
        $validated = $request->validate(['ids' => ['required', 'array', 'min:1', 'max:100'], 'ids.*' => ['required', 'integer', 'distinct']]);
        $prints = collect($validated['ids'])->map(fn ($id) => app(MarketingNdsDocumentService::class)->salesOrderPrint((int) $id))->filter()->values()->all();
        abort_if(!$prints, 404, 'Sales Order tidak ditemukan.');

        return $this->streamPdf($prints, 'SalesOrder_Batch.pdf');
    }

    /**
     * Render satu / beberapa SO ke satu PDF. Footer ditulis lewat canvas karena total halaman baru diketahui setelah render
     * (counter(pages) tidak didukung dompdf); untuk beberapa SO, nomor halaman dihitung per SO.
     */
    private function streamPdf(array $prints, string $fileName)
    {
        $html = fn (array $print) => view('marketing.so_nds.pdf', ['p' => $print])->render();
        $make = fn (string $source) => Pdf::loadHTML($source)->setOption('isFontSubsettingEnabled', true)->setPaper('a4', 'portrait');
        $label = fn (array $print) => 'Sales Order ' . $print['no'] . ' Rev ' . $print['revision'] . ' (Detail PO Buyer)';

        if (count($prints) === 1) {
            $source = $html($prints[0]);
            $ranges = [[1, null, $label($prints[0])]];
        } else {
            // Gabung isi <body> tiap SO (style sama) dengan pemisah halaman; jumlah halaman tiap SO dihitung dari render tunggal.
            $styles = null;
            $bodies = [];
            $ranges = [];
            $start = 1;
            foreach ($prints as $print) {
                $one = $html($print);
                preg_match('/<head\b[^>]*>(.*?)<\/head>/is', $one, $head);
                preg_match('/<body\b[^>]*>(.*?)<\/body>/is', $one, $body);
                $styles ??= $head[1] ?? '';
                $bodies[] = $body[1] ?? $one;
                $single = $make($one);
                $single->render();
                $pages = $single->getDomPDF()->getCanvas()->get_page_count();
                $ranges[] = [$start, $pages, $label($print)];
                $start += $pages;
            }
            $source = '<!DOCTYPE html><html><head>' . $styles . '</head><body>'
                . implode('<div style="page-break-before: always;"></div>', $bodies) . '</body></html>';
        }

        $pdf = $make($source);
        $pdf->render();
        $dompdf = $pdf->getDomPDF();
        $font = $dompdf->getFontMetrics()->getFont('Helvetica');
        $dompdf->getCanvas()->page_script(function (int $page, int $count, $canvas, $metrics) use ($ranges, $font) {
            [$first, $pages, $text] = collect($ranges)->last(fn ($r) => $r[0] <= $page) ?? $ranges[0];
            $pages = $pages ?? $count;
            $y = $canvas->get_height() - 24;
            $color = [0.2, 0.2, 0.2];
            $canvas->text(28, $y, $text, $font, 6.5, $color);
            $right = 'Halaman ' . min($page - $first + 1, $pages) . ' dari ' . $pages;
            $canvas->text($canvas->get_width() - 28 - $metrics->getTextWidth($right, $font, 6.5), $y, $right, $font, 6.5, $color);
        });

        return $pdf->stream($fileName);
    }

    public function excel($id)
    {
        $doc = $this->document($id);

        return MarketingNdsDocumentService::download(app(MarketingNdsDocumentService::class)->salesOrderExcel($doc), 'SO_' . $doc['header']['so_no']);
    }

    /** Tambah master destinasi dari modal di tabel detail SO. Nama & kode disimpan kapital, kode unik, negara wajib. */
    public function storeDestination(Request $request)
    {
        $clean = fn ($value) => strtoupper(preg_replace('/\s+/', ' ', trim((string) $value)));
        $request->merge(['name' => $clean($request->name), 'code' => $clean($request->code)]);
        $validated = $request->validate([
            'name' => ['required', 'max:100'],
            'code' => ['required', 'max:20'],
            'country_id' => ['required', 'integer', 'exists:mysql_mkt.master_countries,id'],
        ], ['country_id.required' => 'Pilih negara dulu.'], ['name' => 'Nama destinasi', 'code' => 'Kode destinasi', 'country_id' => 'Negara']);
        $db = DB::connection('mysql_mkt');
        // Nama boleh sama di negara lain (mis. INDONESIA - RETAIL dan ARGENTINA - RETAIL); kode harus unik.
        $taken = $db->table('master_destinations')
            ->where(fn ($q) => $q->where(fn ($q) => $q->whereRaw('UPPER(destination_name) = ?', [$validated['name']])->where('country_id', (int) $validated['country_id']))
                ->orWhereRaw('UPPER(destination_code) = ?', [$validated['code']]))
            ->first(['destination_name', 'destination_code', 'cancel']);
        if ($taken) {
            $message = ($taken->cancel ?? null) === 'Y'
                ? "Destinasi {$taken->destination_name} ({$taken->destination_code}) sudah ada tapi berstatus cancel. Hubungi IT untuk mengaktifkannya lagi."
                : (strtoupper((string) $taken->destination_code) === $validated['code']
                    ? "Kode {$validated['code']} sudah dipakai destinasi {$taken->destination_name}. Pakai kode lain."
                    : "Destinasi {$taken->destination_name} sudah ada di negara ini.");

            return response()->json(['message' => $message], 422);
        }
        $id = $db->table('master_destinations')->insertGetId([
            'destination_name' => $validated['name'],
            'destination_code' => $validated['code'],
            'country_id' => (int) $validated['country_id'],
            'cancel' => 'N',
            'created_by' => mb_substr((string) (Auth::user()->username ?? Auth::id()), 0, 50),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $country = $db->table('master_countries')->where('id', (int) $validated['country_id'])->value('country_name');

        return response()->json(['message' => 'Destinasi berhasil ditambahkan.', 'data' => ['id' => (int) $id, 'name' => $validated['name'], 'code' => $validated['code'], 'country' => $country]]);
    }

    private function pageData(): array
    {
        $db = DB::connection('mysql_mkt');
        $active = fn ($table) => $db->table($table)->where(fn ($q) => $q->whereNull('cancel')->orWhere('cancel', '!=', 'Y'));
        $upper = fn ($values) => $values->map(fn ($value) => strtoupper(trim((string) $value)))->filter()->unique()->values();

        return [
            'masterColors' => $upper($active('master_colors')->pluck('color_name')),
            'masterSizes' => $active('master_sizes')->pluck('size_name')->map(fn ($size) => preg_replace('/\s+/u', '', strtoupper((string) $size)))->values(),
            'masterDestinations' => $upper($active('master_destinations')->pluck('destination_name')->merge($active('master_destinations')->pluck('destination_code'))),
            // Dropdown Destination di tabel detail + modal tambah destinasi.
            // Tampil "NEGARA - DESTINASI", urut negara lalu destinasi.
            'destinationOptions' => $db->table('master_destinations as d')->leftJoin('master_countries as c', 'c.id', '=', 'd.country_id')
                ->where(fn ($q) => $q->whereNull('d.cancel')->orWhere('d.cancel', '!=', 'Y'))
                ->orderByRaw('COALESCE(c.country_name, \'\'), d.destination_name')
                ->get(['d.id', 'd.destination_name as name', 'd.destination_code as code', 'c.country_name as country']),
            'countries' => $active('master_countries')->orderBy('country_name')->get(['id', 'country_code as code', 'country_name as name']),
            'page' => 'dashboard-marketing',
            'subPageGroup' => 'marketing-master',
            'subPage' => 'marketing-master-so',
            'containerFluid' => true,
            'currencies' => $db->table('master_currencies')->where('cancel', '!=', 'Y')->orderBy('currency_code')->get(),
            'paymentTerms' => $db->table('master_payment_terms')->where('cancel', '!=', 'Y')->orderBy('term_name')->get(),
            'shipModes' => $active('master_ship_modes')->orderBy('ship_mode_name')->get(),
        ];
    }

}
