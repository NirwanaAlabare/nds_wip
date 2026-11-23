<?php

namespace App\Http\Controllers\MasterData;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Master Group General (database nds, koneksi mysql_nds):
 * - master_general_groups    : group (kode GG1, GG2, ...)
 * - master_general_subgroups : sub group + accounting & customs. Kodenya diketik user (mis. ATK) dan
 *                              jadi awalan Item Code item general New (ATK-1, ATK-2, ...)
 * Satu form: Group (pilih yang ada / ketik baru) + Sub Group Code + Sub Group + Accounting + Customs.
 * Tidak ada hapus; Active / Inactive per sub group lewat kolom cancel ('Y' = Inactive).
 */
class MasterGroupGeneralController extends Controller
{
    private const KONEKSI = 'mysql_nds';
    private const T_GRUP = 'master_general_groups';
    private const T_SUB = 'master_general_subgroups';

    private const AWALAN_GRUP = 'GG';

    // Kode sub group diketik user (bukan GSG otomatis) dan jadi awalan Item Code item general
    // New: kode ATK -> item ATK-1, ATK-2, ... Hanya huruf besar & angka, 2 - 10 karakter.
    private const POLA_KODE_SUB = '/^[A-Z0-9]{2,10}$/';
    private const T_ITEM = 'master_item_generals';

    // Pilihan Accounting tetap (hardcode)
    private const ACCOUNTING = ['EXPENSE', 'ASSET'];

    // cancel = 'Y' -> Inactive; kosong / NULL -> Active
    private const BATAL = 'Y';

    private const PANJANG = [
        'group'      => ['Group', 100],
        'sub_code'   => ['Sub Group Code', 10],
        'sub_group'  => ['Sub Group', 100],
        'accounting' => ['Accounting', 100],
        'customs'    => ['Customs', 100],
    ];

    // ===================================================================
    // Daftar
    // ===================================================================

    // Dari dashboard: ?status=aktif|nonaktif (baris legenda), ?cari= (quick search), ?form=baru (tombol New)
    public function index()
    {
        $q = request()->query();

        return view('master-data.group-general.index', [
            'awal'           => [
                'status'   => in_array($q['status'] ?? '', ['aktif', 'nonaktif'], true) ? $q['status'] : '',
                'cari'     => mb_substr(trim(is_string($q['cari'] ?? null) ? $q['cari'] : ''), 0, 100),
                'bukaForm' => ($q['form'] ?? '') === 'baru',
            ],
            'pilihan'        => $this->pilihan(),
            'page'           => 'dashboard-master-data',
            'subPageGroup'   => 'master-data',
            'subPage'        => 'master-group-general',
            'containerFluid' => true,
        ]);
    }

    // Data sedikit: semua dikirim, pencarian & halaman di browser
    public function data(Request $request)
    {
        return response()->json([
            'data'    => $this->saring($request, null)->get(),
            'pilihan' => $this->pilihan(),
        ]);
    }

    // Excel sama dengan lampiran: judul, Group | Sub Group | Accounting | Customs, Group di-merge per group
    public function excel(Request $request)
    {
        $rows = $this->saring($request, $request->input('cari'))->get();

        $ss = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $s = $ss->getActiveSheet();
        $s->setTitle('Group General');
        $s->setCellValue('A1', 'MASTER GROUP ITEM GENERAL');
        $s->getStyle('A1')->getFont()->setBold(true)->setSize(12);

        $bKepala = 2;
        $s->fromArray(['Group', 'Sub Group', 'Accounting', 'Customs'], null, 'A' . $bKepala);
        $s->getStyle('A' . $bKepala . ':D' . $bKepala)->getFont()->setBold(true);

        $teks = \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING;
        $b = $bKepala;
        $awal = null;
        $grupLalu = null;
        foreach ($rows as $r) {
            $b++;
            $s->setCellValueExplicit('A' . $b, (string) $r->general_group_name, $teks);
            $s->setCellValueExplicit('B' . $b, (string) $r->general_subgroup_name, $teks);
            $s->setCellValueExplicit('C' . $b, (string) $r->accounting_type, $teks);
            $s->setCellValueExplicit('D' . $b, (string) $r->customs_type, $teks);

            if ($r->group_id !== $grupLalu) {
                $this->gabung($s, $awal, $b - 1);
                $awal = $b;
                $grupLalu = $r->group_id;
            }
        }
        $this->gabung($s, $awal, $b);

        $area = 'A' . $bKepala . ':D' . $b;
        $s->getStyle($area)->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $s->getStyle($area)->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_TOP);
        foreach (['A' => 24, 'B' => 38, 'C' => 13, 'D' => 18] as $kol => $lebar) {
            $s->getColumnDimension($kol)->setWidth($lebar);
        }
        $s->freezePane('A' . ($bKepala + 1));

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($ss);

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, 'Master Group General ' . now()->format('Ymd') . '.xlsx', ['Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']);
    }

    private function gabung($s, $awal, $akhir)
    {
        if ($awal !== null && $akhir > $awal) {
            $s->mergeCells('A' . $awal . ':A' . $akhir);
        }
    }

    // Urutan: group menurut id (kapan dibuat), lalu sub group menurut id
    private function saring(Request $request, $cari)
    {
        $q = $this->db()->table(self::T_SUB . ' as s')
            ->join(self::T_GRUP . ' as g', 'g.id', '=', 's.general_group_id')
            ->orderBy('g.id')
            ->orderBy('s.id')
            ->select('s.id', 's.general_subgroup_code', 's.general_subgroup_name', 's.accounting_type', 's.customs_type', 's.cancel',
                'g.id as group_id', 'g.general_group_code', 'g.general_group_name')
            // Banyak item yang Item Code-nya memakai kode sub group ini - lebih dari 0 = kode dikunci di Edit
            ->selectSub(fn ($q) => $q->from(self::T_ITEM . ' as i')
                ->whereColumn('i.general_subgroup_id', 's.id')
                ->whereRaw("i.general_code like concat(s.general_subgroup_code, '-%')")
                ->selectRaw('count(*)'), 'item_terpakai');

        if (ctype_digit((string) $request->input('grup'))) {
            $q->where('g.id', (int) $request->input('grup'));
        }
        if ($request->input('status') === 'aktif') {
            $q->where(fn ($w) => $w->whereNull('s.cancel')->orWhere('s.cancel', '<>', self::BATAL));
        } elseif ($request->input('status') === 'nonaktif') {
            $q->where('s.cancel', self::BATAL);
        }

        $cari = trim((string) $cari);
        if ($cari !== '') {
            $like = '%' . addcslashes($cari, '\\%_') . '%';
            $q->where(fn ($w) => $w->where('g.general_group_name', 'like', $like)->orWhere('g.general_group_code', 'like', $like)
                ->orWhere('s.general_subgroup_name', 'like', $like)->orWhere('s.general_subgroup_code', 'like', $like)
                ->orWhere('s.accounting_type', 'like', $like)->orWhere('s.customs_type', 'like', $like));
        }

        return $q;
    }

    // Pilihan form & filter: daftar group, nilai Accounting / Customs yang sudah pernah dipakai
    private function pilihan()
    {
        $nilai = fn ($kolom) => $this->db()->table(self::T_SUB)->whereNotNull($kolom)->where($kolom, '<>', '')
            ->distinct()->orderBy($kolom)->pluck($kolom)->values();

        return [
            // jumlah = banyak sub group-nya (untuk pesan Rename group di Edit)
            'grup'       => $this->db()->table(self::T_GRUP . ' as g')->orderBy('g.id')
                ->select('g.id', 'g.general_group_code as kode', 'g.general_group_name as nama')
                ->selectSub(fn ($q) => $q->from(self::T_SUB . ' as s')->whereColumn('s.general_group_id', 'g.id')->selectRaw('count(*)'), 'jumlah')
                ->get(),
            'accounting' => self::ACCOUNTING,
            'customs'    => $nilai('customs_type'),
            // Semua kode sub group (tanpa filter) - cek kode dobel langsung saat mengetik di form
            'kodeSub'    => $this->db()->table(self::T_SUB)->whereNotNull('general_subgroup_code')->where('general_subgroup_code', '<>', '')
                ->orderBy('id')->get(['id', 'general_subgroup_code as kode', 'general_subgroup_name as nama']),
        ];
    }

    // ===================================================================
    // Create / Edit / Status
    // ===================================================================

    public function simpan(Request $request)
    {
        return $this->tulis($request, null);
    }

    public function perbarui(Request $request)
    {
        $lama = $this->db()->table(self::T_SUB)->where('id', (int) $request->input('id'))->first();
        if (! $lama) {
            return response()->json(['message' => 'Sub group not found.'], 404);
        }

        return $this->tulis($request, $lama);
    }

    // Satu form: nama group diketik (dengan saran group yang ada). Nama yang belum ada dibuat
    // jadi group baru (kode GG otomatis) bersama sub group-nya.
    private function tulis(Request $request, $lama)
    {
        $v = [
            'group'      => mb_strtoupper($this->rapi($request->input('group'))),
            // Spasi di tengah juga dibuang - kode tanpa spasi
            'sub_code'   => mb_strtoupper(preg_replace('/\s+/u', '', (string) $request->input('sub_code'))),
            'sub_group'  => mb_strtoupper($this->rapi($request->input('sub_group'))),
            'accounting' => mb_strtoupper($this->rapi($request->input('accounting'))),
            'customs'    => mb_strtoupper($this->rapi($request->input('customs'))),
        ];

        $kurang = [];
        foreach (self::PANJANG as $kolom => [$label, $maks]) {
            if ($v[$kolom] === '') {
                $kurang[] = $label;
            }
        }
        if ($kurang) {
            return response()->json(['message' => 'Please complete the form.', 'kurang' => $kurang], 422);
        }

        // Group diketik namanya: kalau sudah ada dipakai, kalau belum dibuat baru bersama sub group-nya.
        // Edit dengan ganti_nama_grup = 1 dan nama yang belum ada: group lama yang diganti namanya
        // (kode GG tetap, semua sub group di dalamnya ikut) - bukan membuat group baru.
        $grup = $this->db()->table(self::T_GRUP)->where('general_group_name', $v['group'])->first();
        $gantiNama = false;
        if (! $grup && $lama && $request->boolean('ganti_nama_grup')) {
            $grup = $this->db()->table(self::T_GRUP)->where('id', $lama->general_group_id)->first();
            $gantiNama = (bool) $grup;
        }
        $grupBaru = $grup ? '' : $v['group'];

        foreach (self::PANJANG as $kolom => [$label, $maks]) {
            if (mb_strlen($v[$kolom]) > $maks) {
                return response()->json(['message' => $label . ' is too long (max ' . $maks . ' characters).'], 422);
            }
        }

        if (! in_array($v['accounting'], self::ACCOUNTING, true)) {
            return response()->json(['message' => 'Accounting must be ' . implode(' or ', self::ACCOUNTING) . '.'], 422);
        }

        if (! preg_match(self::POLA_KODE_SUB, $v['sub_code'])) {
            return response()->json(['message' => 'Sub Group Code must be 2 - 10 characters, letters and numbers only (e.g. ATK, MSN01).'], 422);
        }

        // Kode sub group unik di semua group
        $kodeDobel = $this->db()->table(self::T_SUB)
            ->where('general_subgroup_code', $v['sub_code'])
            ->when($lama, fn ($q) => $q->where('id', '<>', $lama->id))
            ->first(['general_subgroup_name']);
        if ($kodeDobel) {
            return response()->json([
                'message' => 'Sub Group Code ' . $v['sub_code'] . ' is already used by sub group ' . $kodeDobel->general_subgroup_name . '.',
            ], 409);
        }

        // Kode yang sudah jadi awalan Item Code tidak boleh diganti (awalan item harus sama dengan kode sub group-nya)
        if ($lama && $v['sub_code'] !== (string) $lama->general_subgroup_code) {
            $terpakai = $this->itemTerpakai($lama);
            if ($terpakai > 0) {
                return response()->json([
                    'message' => 'Sub Group Code ' . $lama->general_subgroup_code . ' is locked: already used by ' . $terpakai
                        . ' item(s) (' . $lama->general_subgroup_code . '-1, ...).',
                ], 409);
            }
        }

        // Sub group dobel dalam group yang sama (group baru belum punya sub group)
        if ($grup) {
            $dobel = $this->db()->table(self::T_SUB)
                ->where('general_group_id', $grup->id)
                ->where('general_subgroup_name', $v['sub_group'])
                ->when($lama, fn ($q) => $q->where('id', '<>', $lama->id))
                ->exists();
            if ($dobel) {
                return response()->json([
                    'message' => 'Sub group ' . $v['sub_group'] . ' already exists in group ' . $grup->general_group_name . '.',
                ], 409);
            }
        }

        $sekarang = now()->format('Y-m-d H:i:s');

        try {
            $hasil = $this->db()->transaction(function () use ($v, &$grup, $grupBaru, $gantiNama, $lama, $sekarang) {
                if ($gantiNama) {
                    $this->db()->table(self::T_GRUP)->where('id', $grup->id)
                        ->update(['general_group_name' => $v['group'], 'updated_at' => $sekarang]);
                    $grup->general_group_name = $v['group'];
                }

                if ($grupBaru !== '') {
                    $id = $this->db()->table(self::T_GRUP)->insertGetId([
                        'general_group_code' => $this->kodeBerikut(self::T_GRUP, 'general_group_code', self::AWALAN_GRUP),
                        'general_group_name' => $grupBaru,
                        'created_at'         => $sekarang,
                        'updated_at'         => $sekarang,
                    ]);
                    $grup = $this->db()->table(self::T_GRUP)->where('id', $id)->first();
                }

                $data = [
                    'general_group_id'      => $grup->id,
                    'general_subgroup_code' => $v['sub_code'],
                    'general_subgroup_name' => $v['sub_group'],
                    'accounting_type'       => $v['accounting'],
                    'customs_type'          => $v['customs'],
                ];

                if ($lama) {
                    $berubah = collect($data)->filter(fn ($nilai, $kolom) => (string) $lama->$kolom !== (string) $nilai)->all();
                    if ($berubah) {
                        $this->db()->table(self::T_SUB)->where('id', $lama->id)->update($berubah + ['updated_at' => $sekarang]);
                    }
                    return ['berubah' => count($berubah) + ($gantiNama ? 1 : 0), 'kode' => $v['sub_code']];
                }

                $this->db()->table(self::T_SUB)->insert($data + [
                    'created_at' => $sekarang,
                    'updated_at' => $sekarang,
                ]);
                return ['kode' => $v['sub_code']];
            });
        } catch (\Illuminate\Database\QueryException $e) {
            // Kode sub group yang baru saja dipakai orang lain di antara pengecekan & simpan (kolom UNIQUE)
            if (($e->errorInfo[1] ?? null) === 1062) {
                return response()->json(['message' => 'Sub Group Code ' . $v['sub_code'] . ' is already used.'], 409);
            }
            report($e);
            // Pesan database ditampilkan apa adanya supaya mudah dicek
            return response()->json(['message' => 'Database error: ' . ($e->errorInfo[2] ?? $e->getMessage())], 500);
        }

        return response()->json($hasil + [
            'group'      => $grup->general_group_name,
            'kode_grup'  => $grup->general_group_code,
            'grup_baru'  => $grupBaru !== '',
            'grup_ganti' => $gantiNama,
            'sub'        => $v['sub_group'],
            'accounting' => $v['accounting'],
            'customs'    => $v['customs'],
        ]);
    }

    // Angka kartu dashboard Master Data (per sub group: Active / Inactive) + jumlah group.
    // null kalau database nds / tabelnya tidak terbaca - dashboard tetap tampil tanpa kartu ini.
    public static function ringkasan()
    {
        try {
            $db = DB::connection(self::KONEKSI);
            $row = $db->table(self::T_SUB)
                ->selectRaw('count(*) as total')
                ->selectRaw("sum(case when coalesce(cancel, '') <> ? then 1 else 0 end) as aktif", [self::BATAL])
                ->first();
            $grup = $db->table(self::T_GRUP)
                ->selectRaw('count(*) as total')
                ->selectRaw("sum(case when coalesce(cancel, '') <> ? then 1 else 0 end) as aktif", [self::BATAL])
                ->first();
        } catch (\Throwable $e) {
            report($e);
            return null;
        }

        return [
            'total'     => (int) $row->total,
            'aktif'     => (int) $row->aktif,
            'nonaktif'  => (int) $row->total - (int) $row->aktif,
            'grup'      => (int) $grup->total,
            'blokir'    => 0,
            'adaBlokir' => false,
            // Angka group (induk) untuk kartu Group di dashboard
            'angkaGrup' => [
                'total'     => (int) $grup->total,
                'aktif'     => (int) $grup->aktif,
                'nonaktif'  => (int) $grup->total - (int) $grup->aktif,
                'blokir'    => 0,
                'adaBlokir' => false,
            ],
        ];
    }

    // Hapus group - HANYA group kosong (tanpa sub group), mis. group salah ketik yang sub group-nya
    // sudah dipindah. Group yang masih punya sub group ditolak.
    public function hapusGrup(Request $request)
    {
        $id = (int) $request->input('id');

        try {
            $hasil = $this->db()->transaction(function () use ($id) {
                $grup = $this->db()->table(self::T_GRUP)->where('id', $id)->lockForUpdate()->first();
                if (! $grup) {
                    return ['kode' => 404, 'message' => 'Group not found, please reload the page.'];
                }
                $jumlah = $this->db()->table(self::T_SUB)->where('general_group_id', $id)->count();
                if ($jumlah > 0) {
                    return ['kode' => 409, 'message' => 'Group ' . $grup->general_group_name . ' still has ' . $jumlah . ' sub group(s) and cannot be deleted.'];
                }
                $this->db()->table(self::T_GRUP)->where('id', $id)->delete();
                return ['kode' => 200, 'grup' => $grup];
            });
        } catch (\Illuminate\Database\QueryException $e) {
            report($e);
            return response()->json(['message' => 'Database error: ' . ($e->errorInfo[2] ?? $e->getMessage())], 500);
        }

        if ($hasil['kode'] !== 200) {
            return response()->json(['message' => $hasil['message']], $hasil['kode']);
        }

        return response()->json([
            'kode_grup' => $hasil['grup']->general_group_code,
            'group'     => $hasil['grup']->general_group_name,
            'pilihan'   => $this->pilihan(),
        ]);
    }

    public function status(Request $request)
    {
        $row = $this->db()->table(self::T_SUB)->where('id', (int) $request->input('id'))->first();
        if (! $row) {
            return response()->json(['message' => 'Sub group not found.'], 404);
        }

        $aktif = $request->boolean('aktif');
        $this->db()->table(self::T_SUB)->where('id', $row->id)->update([
            'cancel'     => $aktif ? null : self::BATAL,
            'updated_at' => now()->format('Y-m-d H:i:s'),
        ]);

        return response()->json(['aktif' => $aktif, 'sub' => $row->general_subgroup_name, 'kode' => $row->general_subgroup_code]);
    }

    // ===================================================================

    // Banyak item general (New) yang Item Code-nya berawalan kode sub group ini (KODE-...)
    private function itemTerpakai($sub)
    {
        $kode = (string) $sub->general_subgroup_code;
        if ($kode === '') {
            return 0;
        }

        return $this->db()->table(self::T_ITEM)
            ->where('general_subgroup_id', $sub->id)
            ->where('general_code', 'like', addcslashes($kode, '\\%_') . '-%')
            ->count();
    }

    // Kode berikutnya: awalan + (angka terbesar + 1), mis. GG1, GG2 ...
    // Dibaca dengan kunci (dalam transaksi) supaya dua simpan bersamaan tidak dapat kode sama.
    private function kodeBerikut($tabel, $kolom, $awalan)
    {
        $maks = $this->db()->table($tabel)
            ->where($kolom, 'like', $awalan . '%')
            ->whereRaw($kolom . " regexp ?", ['^' . $awalan . '[0-9]+$'])
            ->lockForUpdate()
            ->selectRaw('max(cast(substring(' . $kolom . ', ' . (strlen($awalan) + 1) . ') as unsigned)) as n')
            ->value('n');

        return $awalan . ((int) $maks + 1);
    }

    // Spasi di awal/akhir dibuang, spasi ganda jadi satu
    private function rapi($nilai)
    {
        return trim(preg_replace('/\s+/u', ' ', (string) $nilai));
    }

    private function db()
    {
        return DB::connection(self::KONEKSI);
    }
}
