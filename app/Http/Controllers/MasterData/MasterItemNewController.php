<?php

namespace App\Http\Controllers\MasterData;

use App\Http\Controllers\Controller;
use avadim\FastExcelLaravel\Excel as FastExcel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\Style\Border;

/**
 * Master Item General - tab NEW (database nds, koneksi mysql_nds, tabel master_item_generals).
 * Tab OLD tetap di MasterItemController (masteritem di SB).
 *
 * Query ditulis SQL biasa (select ... / insert into ...) lewat DB::select / DB::insert,
 * nilai isian selalu lewat tanda ? (binding), tidak disambung langsung ke teks query.
 *
 * Relasi master_item_generals:
 *   general_subgroup_id -> master_general_subgroups.id (Group General diambil lewat sub group)
 *   group_id            -> master_group.id, selalu group_code 'N' (ITEM GENERAL)
 *   unit_id             -> master_units.id
 *   facility            -> 'Y' pakai fasilitas, 'N' tidak pakai fasilitas
 *   cancel              -> 'N' aktif, 'Y' tidak aktif
 *
 * Edit: Item Code, Group & Sub Group tidak bisa diubah (Item Code berawalan kode sub group).
 * Item Inactive tetap boleh di-Edit.
 */
class MasterItemNewController extends Controller
{
    private const KONEKSI = 'mysql_nds';

    // group_code di master_group untuk semua item general
    private const KODE_GROUP_ITEM_GENERAL = 'N';

    // Isian Facility: Y = pakai fasilitas, N = tidak pakai fasilitas (disimpan apa adanya)
    private const FASILITAS = ['Y', 'N'];

    // Item Code otomatis dari kode sub group (Master Group General): ATK -> ATK-1, ATK-2, ...
    // Kode sub group yang bisa dipakai: huruf besar & angka saja (sama dengan aturan di MasterGroupGeneralController)
    private const POLA_KODE_SUB = '/^[A-Z0-9]{2,10}$/';

    // Panjang kolom master_item_generals - ditolak dengan pesan jelas, bukan dipotong diam-diam
    // (general_code tidak diisi user)
    private const PANJANG = [
        'general_name' => ['Description', 50],
        'color'        => ['Color', 100],
        'size'         => ['Size', 100],
    ];

    private const FOLDER_GAMBAR = 'master-item-general';
    private const EKSTENSI_GAMBAR = ['jpg', 'jpeg', 'png', 'webp'];

    private function db()
    {
        return DB::connection(self::KONEKSI);
    }

    // ===================================================================
    // Daftar
    // ===================================================================

    public function index()
    {
        $db = $this->db();

        $subGrup = $db->select("
            select s.id, s.general_group_id as grup, s.general_subgroup_code as kode, s.general_subgroup_name as nama,
                   s.accounting_type as accounting, s.customs_type as customs,
                   if(coalesce(s.cancel, '') = 'Y', 0, 1) as aktif
            from master_general_subgroups s
            order by s.id
        ");
        // Perkiraan Item Code berikutnya per sub group (hanya tampilan; nomor pastinya ditentukan saat simpan)
        foreach ($subGrup as $s) {
            $s->berikut = preg_match(self::POLA_KODE_SUB, (string) $s->kode) ? $this->kodeBerikut($s->kode) : null;
        }

        return view('master-data.item.list-item-general-new', [
            'grup'           => $db->select("
                select id, general_group_code as kode, general_group_name as nama
                from master_general_groups
                order by id
            "),
            // Semua sub group untuk filter; form Create hanya memakai yang aktif (kolom aktif)
            'subGrup'        => $subGrup,
            'satuan'         => $db->select("
                select id, unit_code as kode, unit_name as nama
                from master_units
                where coalesce(cancel, '') <> 'Y'
                order by unit_code
            "),
            'panjang'        => collect(self::PANJANG)->map(fn ($p) => $p[1]),
            'ekstensi'       => self::EKSTENSI_GAMBAR,
            'batasUpload'    => $this->batasUpload(),
            // Dari dashboard: status awal filter & teks Search awal
            'awal'           => [
                'status' => in_array(request()->query('status'), ['aktif', 'nonaktif'], true) ? request()->query('status') : '',
                'cari'   => mb_substr(trim(is_string(request()->query('cari')) ? request()->query('cari') : ''), 0, 100),
            ],
            // 'baru' = modal Create, angka = modal Edit item itu (dari alamat create / edit langsung)
            'bukaForm'       => ctype_digit((string) request()->query('form')) ? (int) request()->query('form')
                : request()->query('form') === 'baru',            'page'           => 'dashboard-master-data',
            'subPageGroup'   => 'master-data',
            'subPage'        => 'master-item',
            'containerFluid' => true,
        ]);
    }

    // Angka kartu Item General (New) di dashboard Master Data. null = database nds tidak terbaca
    // (kartunya dilewati supaya dashboard tetap tampil).
    public static function ringkasan()
    {
        try {
            $row = DB::connection(self::KONEKSI)->select("
                select count(*) as total,
                       coalesce(sum(case when coalesce(cancel, '') <> 'Y' then 1 else 0 end), 0) as aktif
                from master_item_generals
            ")[0];
        } catch (\Throwable $e) {
            report($e);
            return null;
        }

        return [
            'total'     => (int) $row->total,
            'aktif'     => (int) $row->aktif,
            'nonaktif'  => (int) $row->total - (int) $row->aktif,
            'blokir'    => 0,
            'adaBlokir' => false,
        ];
    }

    // Tabel & join daftar - dipakai bersama oleh data() dan excel()
    private const DARI = "
        from master_item_generals i
        join master_general_subgroups s on s.id = i.general_subgroup_id
        join master_general_groups g on g.id = s.general_group_id
        left join master_units u on u.id = i.unit_id
    ";

    // Server-side DataTables
    public function data(Request $request)
    {
        $db = $this->db();
        $dari = self::DARI;

        // Total = sesudah filter atas, sebelum kotak Search (sama dengan tab Old)
        [$syarat, $nilai] = $this->saring($request, null);
        $total = $db->select("select count(*) as jumlah " . $dari . $syarat, $nilai)[0]->jumlah;

        [$syarat, $nilai] = $this->saring($request, $request->input('search.value'));
        $tersaring = $db->select("select count(*) as jumlah " . $dari . $syarat, $nilai)[0]->jumlah;

        // Urutan: hanya kolom dari daftar ini (nama kolom tidak pernah diambil dari isian user)
        $kolomUrut = [0 => 'i.general_code', 1 => 'i.general_name', 2 => 'i.color', 3 => 'i.size',
                      4 => 'g.general_group_name', 5 => 's.general_subgroup_name', 6 => 'u.unit_code',
                      7 => 'i.facility', 8 => 'i.cancel'];
        $urut = $request->input('order.0.column');
        $orderBy = 'i.id desc';   // bawaan: item terbaru di atas
        if ($urut !== null && isset($kolomUrut[(int) $urut])) {
            $orderBy = $kolomUrut[(int) $urut] . ($request->input('order.0.dir') === 'desc' ? ' desc' : ' asc') . ', i.id desc';
        }

        $panjang = (int) $request->input('length', 10);
        $panjang = $panjang > 0 ? min($panjang, 500) : 10;
        $mulai = max(0, (int) $request->input('start'));

        $rows = $db->select("
            select i.id, i.general_code as kode, i.general_name as nama, i.color, i.size,
                   g.general_group_name as grup, s.general_subgroup_name as sub_grup,
                   u.unit_code as satuan, i.facility, i.cancel,
                   coalesce(i.picture, '') <> '' as gambar
            " . $dari . $syarat . "
            order by " . $orderBy . "
            limit " . $panjang . " offset " . $mulai, $nilai);

        return response()->json([
            'draw'            => (int) $request->input('draw'),
            'recordsTotal'    => (int) $total,
            'recordsFiltered' => (int) $tersaring,
            'data'            => $rows,
        ]);
    }

    // Filter atas (grup, sub, fas, status) + teks cari -> [" where ...", [nilai binding]]
    private function saring(Request $request, $cari)
    {
        $where = ['1 = 1'];
        $nilai = [];
        if (ctype_digit((string) $request->input('grup'))) {
            $where[] = 'g.id = ?';
            $nilai[] = (int) $request->input('grup');
        }
        if (ctype_digit((string) $request->input('sub'))) {
            $where[] = 's.id = ?';
            $nilai[] = (int) $request->input('sub');
        }
        if (in_array($request->input('fas'), self::FASILITAS, true)) {
            $where[] = 'i.facility = ?';
            $nilai[] = $request->input('fas');
        }
        if ($request->input('status') === 'aktif') {
            $where[] = "coalesce(i.cancel, '') <> 'Y'";
        } elseif ($request->input('status') === 'nonaktif') {
            $where[] = "i.cancel = 'Y'";
        }

        $cari = trim((string) $cari);
        if ($cari !== '') {
            $like = '%' . addcslashes($cari, '\\%_') . '%';
            $where[] = '(i.general_code like ? or i.general_name like ? or i.color like ? or i.size like ?
                         or s.general_subgroup_name like ? or g.general_group_name like ? or u.unit_code like ?)';
            array_push($nilai, $like, $like, $like, $like, $like, $like, $like);
        }

        return [" where " . implode(' and ', $where), $nilai];
    }

    // ===================================================================
    // Export Excel (FastExcel) - filter & teks cari sama dengan yang tampil di daftar
    // ===================================================================

    public function excel(Request $request)
    {
        @set_time_limit(300);

        [$syarat, $nilai] = $this->saring($request, $request->input('cari'));
        $rows = $this->db()->select("
            select i.id, i.general_code, i.general_name, i.color, i.size,
                   g.general_group_code, g.general_group_name,
                   s.general_subgroup_code, s.general_subgroup_name, s.accounting_type, s.customs_type,
                   u.unit_code, u.unit_name, i.facility, i.cancel, i.created_by, i.created_at
            " . self::DARI . $syarat . "
            order by i.id desc
        ", $nilai);

        $kolom = ['No', 'ID', 'Item Code', 'Description', 'Color', 'Size', 'Group', 'Sub Group',
                  'Accounting', 'Customs', 'Unit', 'Facility', 'Status', 'Created By', 'Created At'];

        $excel = FastExcel::create('Item General New');
        $sheet = $excel->getSheet();

        $sheet->writeRow(['Master Item General (New)'])->applyFontStyleBold()->applyFontSize(13);
        $sheet->writeRow(['Exported ' . now()->format('d M Y H:i') . ' - ' . count($rows) . ' rows'])
            ->applyFontSize(9)->applyFontColor('#64748B');
        // Baris 3 = kepala kolom (writeRow([]) tidak menghasilkan baris kosong di writer ini)
        $sheet->writeRow($kolom)->applyFontStyleBold()->applyBgColor('#F1F5F9')->applyBorder(Border::BORDER_THIN);

        foreach ($rows as $i => $r) {
            $sheet->writeRow([
                $i + 1,
                (int) $r->id,
                (string) $r->general_code,
                (string) $r->general_name,
                (string) $r->color,
                (string) $r->size,
                (string) $r->general_group_name,
                (string) $r->general_subgroup_name,
                (string) $r->accounting_type,
                (string) $r->customs_type,
                trim($r->unit_code . ($r->unit_name ? ' - ' . $r->unit_name : '')),
                strtoupper((string) $r->facility) === 'Y' ? 'Facility' : 'Non Facility',
                $r->cancel === 'Y' ? 'Inactive' : 'Active',
                (string) $r->created_by,
                (string) $r->created_at,
            ])->applyBorder(Border::BORDER_THIN);
        }

        // Kode & teks tetap teks (kode berawalan nol tidak berubah jadi angka)
        $sheet->setColFormats(['C' => '@', 'D' => '@', 'E' => '@', 'F' => '@']);
        $sheet->setColWidths(['A' => 6, 'B' => 9, 'C' => 18, 'D' => 45, 'E' => 14, 'F' => 14, 'G' => 22, 'H' => 26,
                              'I' => 14, 'J' => 14, 'K' => 18, 'L' => 24, 'M' => 10, 'N' => 16, 'O' => 20]);
        $sheet->setFreezeRows(3);

        // FastExcel::download() langsung echo tanpa Response (file bisa rusak) - simpan ke file sementara
        // lalu kirim lewat response()->download(), sama dengan AssetMesinMasterController
        $tmp = sys_get_temp_dir() . DIRECTORY_SEPARATOR . uniqid('item_general_new_') . '.xlsx';
        $excel->save($tmp);

        return response()->download($tmp, 'Master Item General New ' . now()->format('Ymd') . '.xlsx', [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ])->deleteFileAfterSend(true);
    }

    // Alamat create langsung (Ctrl+klik / tab baru): dibuka di daftar dengan modal Create terbuka
    public function create()
    {
        return redirect()->route('master-data-item-new', ['form' => 'baru']);
    }

    // Alamat edit langsung (Ctrl+klik / tab baru): dibuka di daftar dengan modal Edit terbuka
    public function edit(Request $request)
    {
        $row = $this->cari((int) $request->query('id'));
        if (! $row) {
            abort(404);
        }

        return redirect()->route('master-data-item-new', ['form' => $row->id]);
    }

    // Satu item lengkap dengan group, sub group & unit
    private function cari($id)
    {
        if ($id <= 0) {
            return null;
        }

        return $this->db()->select("
            select i.id, i.general_code, i.general_name, i.color, i.size, i.facility, i.cancel, i.picture,
                   i.general_subgroup_id, i.unit_id, i.created_by, i.created_at, i.updated_at,
                   s.general_subgroup_code, s.general_subgroup_name, s.accounting_type, s.customs_type,
                   s.cancel as sub_cancel,
                   g.id as general_group_id, g.general_group_code, g.general_group_name,
                   u.unit_code, u.unit_name
            " . self::DARI . "
            where i.id = ?
        ", [$id])[0] ?? null;
    }

    // ===================================================================
    // Show / Status / Gambar
    // ===================================================================

    public function detail(Request $request)
    {
        $row = $this->cari((int) $request->query('id'));
        if (! $row) {
            return response()->json(['message' => 'Item not found'], 404);
        }

        return response()->json([
            'data'       => $row,
            'gambar_url' => $this->urlGambar($row),
        ]);
    }

    // Active / Inactive (kolom cancel: N aktif, Y tidak aktif)
    public function status(Request $request)
    {
        $row = $this->cari((int) $request->input('id'));
        if (! $row) {
            return response()->json(['message' => 'Item not found'], 404);
        }

        $aktif = $request->boolean('aktif');
        $this->db()->update("
            update master_item_generals set cancel = ?, updated_at = ? where id = ?
        ", [$aktif ? 'N' : 'Y', now()->format('Y-m-d H:i:s'), $row->id]);

        return response()->json(['status' => 200, 'aktif' => $aktif, 'kode' => $row->general_code, 'nama' => $row->general_name]);
    }

    // Gambar item dari storage/app/master-item-general (tidak publik, lewat login)
    public function gambar(Request $request)
    {
        $row = $this->cari((int) $request->query('id'));
        $path = self::FOLDER_GAMBAR . '/' . ($row ? (string) $row->picture : '');
        if (! $row || (string) $row->picture === '' || ! Storage::disk('local')->exists($path)) {
            abort(404);
        }

        return response()->file(Storage::disk('local')->path($path), ['Cache-Control' => 'private, max-age=86400']);
    }

    // v = nama file, jadi gambar yang diganti tidak terbaca dari cache lama
    private function urlGambar($row)
    {
        if ((string) $row->picture === '' || ! Storage::disk('local')->exists(self::FOLDER_GAMBAR . '/' . $row->picture)) {
            return null;
        }

        return route('master-data-item-new-gambar', ['id' => $row->id, 'v' => $row->picture]);
    }

    // ===================================================================
    // Simpan (Create) / Perbarui (Edit)
    // ===================================================================

    public function simpan(Request $request)
    {
        return $this->tulis($request, null);
    }

    // Edit: Group & Sub Group dikunci (Item Code ikut kode sub group), sisanya boleh diubah - juga saat Inactive
    public function perbarui(Request $request)
    {
        $lama = $this->cari((int) $request->input('id'));
        if (! $lama) {
            return response()->json(['status' => 404, 'message' => 'Item not found, it may have been deleted.'], 404);
        }

        return $this->tulis($request, $lama);
    }

    private function tulis(Request $request, $lama)
    {
        $db = $this->db();

        $v = [];
        foreach (array_keys(self::PANJANG) as $kolom) {
            $v[$kolom] = $this->besar($request->input($kolom));
        }
        $subId = trim((string) $request->input('general_subgroup_id'));
        $unitId = trim((string) $request->input('unit_id'));
        $fasilitas = strtoupper(trim((string) $request->input('facility')));

        $kurang = [];
        if ($v['general_name'] === '') { $kurang[] = 'Description'; }
        if ($v['color'] === '') { $kurang[] = 'Color (use - if none)'; }
        if ($v['size'] === '') { $kurang[] = 'Size (use - if none)'; }
        foreach (self::PANJANG as $kolom => [$label, $maks]) {
            if (mb_strlen($v[$kolom]) > $maks) {
                $kurang[] = $label . ' max ' . $maks . ' characters';
            }
        }

        // Create: sub group harus ada, aktif, dan kodenya bisa dipakai jadi awalan Item Code.
        // Edit: sub group tidak dibaca dari isian (dikunci), item tetap di sub group lamanya.
        $sub = null;
        if (! $lama) {
            $sub = ctype_digit($subId) ? ($db->select("
                select id, general_subgroup_code, general_subgroup_name, cancel
                from master_general_subgroups
                where id = ?
            ", [(int) $subId])[0] ?? null) : null;
            if (! $sub) {
                $kurang[] = 'Sub Group';
            } elseif ($sub->cancel === 'Y') {
                $kurang[] = 'Sub Group ' . $sub->general_subgroup_name . ' is inactive';
            } elseif (! preg_match(self::POLA_KODE_SUB, (string) $sub->general_subgroup_code)) {
                $kurang[] = 'Sub Group ' . $sub->general_subgroup_name . ' has no valid code yet - set it in Master Group General';
            }
        }

        // Satuan harus ada & aktif (Edit: satuan lama tetap boleh walau sudah Inactive)
        $unit = ctype_digit($unitId) ? ($db->select("
            select id from master_units where id = ? and (coalesce(cancel, '') <> 'Y' or id = ?)
        ", [(int) $unitId, $lama ? (int) $lama->unit_id : 0])[0] ?? null) : null;
        if (! $unit) { $kurang[] = 'Unit'; }

        if (! in_array($fasilitas, self::FASILITAS, true)) { $kurang[] = 'Facility / Non Facility'; }

        // Gambar: file() supaya upload yang gagal karena batas server tetap ketahuan
        $file = $request->file('gambar');
        if ($file) {
            if (! $file->isValid()) {
                $kurang[] = in_array($file->getError(), [UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE], true)
                    ? 'Image is larger than the server upload limit (' . $this->batasUpload() . ')'
                    : 'Image failed to upload, please choose it again';
            } elseif (! in_array(strtolower($file->getClientOriginalExtension()), self::EKSTENSI_GAMBAR, true)
                || strpos((string) $file->getMimeType(), 'image/') !== 0) {
                $kurang[] = 'Image must be JPG, PNG or WEBP';
            }
        }

        if ($kurang) {
            return response()->json(['status' => 400, 'message' => 'Please complete the form', 'kurang' => $kurang], 422);
        }

        if ($lama) {
            return $this->ubah($lama, $v, (int) $unit->id, $fasilitas, $file, $request->boolean('hapus_gambar'));
        }

        // Group ITEM GENERAL di master_group
        $grup = $db->select("select id from master_group where group_code = ? limit 1", [self::KODE_GROUP_ITEM_GENERAL])[0] ?? null;
        if (! $grup) {
            return response()->json([
                'status'  => 500,
                'message' => "Group '" . self::KODE_GROUP_ITEM_GENERAL . "' (ITEM GENERAL) not found in master_group.",
            ], 500);
        }

        $user = Auth::user()->username ?? Auth::user()->name;
        $sekarang = now()->format('Y-m-d H:i:s');
        $fileBaru = null;

        $db->beginTransaction();
        try {
            // Item Code otomatis: KODE-SUB + (nomor terbesar + 1), mis. ATK-1, ATK-2 ... (dibaca dengan kunci)
            $v['general_code'] = $this->kodeBerikut($sub->general_subgroup_code, true);

            $db->insert("
                insert into master_item_generals
                    (general_subgroup_id, general_code, general_name, facility, unit_id, cancel, picture,
                     group_id, color, size, created_by, created_at, updated_at)
                values
                    (?, ?, ?, ?, ?, 'N', null,
                     ?, ?, ?, ?, ?, ?)
            ", [
                (int) $sub->id, $v['general_code'], $v['general_name'], $fasilitas, (int) $unit->id,
                (int) $grup->id, $v['color'], $v['size'], $user, $sekarang, $sekarang,
            ]);
            $id = (int) $db->getPdo()->lastInsertId();

            if ($file) {
                $fileBaru = $this->simpanGambar($file, $id);
                $db->update("update master_item_generals set picture = ? where id = ?", [$fileBaru, $id]);
            }

            $db->commit();
        } catch (\Illuminate\Database\QueryException $e) {
            $db->rollBack();
            $this->buangGambar($fileBaru);
            // Nomor yang sama baru saja dipakai simpan lain (kolom UNIQUE) - cukup simpan ulang
            if (($e->errorInfo[1] ?? null) === 1062) {
                return response()->json(['status' => 409, 'message' => 'Item code ' . ($v['general_code'] ?? '') . ' was just taken by another user. Please save again.'], 409);
            }
            throw $e;
        } catch (\Throwable $e) {
            $db->rollBack();
            $this->buangGambar($fileBaru);
            throw $e;
        }

        return response()->json([
            'status' => 200,
            'id'     => $id,
            'kode'   => $v['general_code'],
            'nama'   => $v['general_name'],
        ]);
    }

    // Edit: Item Code, Group & Sub Group tidak berubah.
    // Gambar: file baru = ganti, hapus_gambar = buang, selain itu tetap. File lama dibuang setelah commit.
    private function ubah($lama, array $v, $unitId, $fasilitas, $file, $hapusGambar)
    {
        $db = $this->db();
        $gambarLama = (string) $lama->picture !== '' ? (string) $lama->picture : null;
        $gambar = $gambarLama;
        $fileBaru = null;

        $db->beginTransaction();
        try {
            if ($file) {
                $fileBaru = $this->simpanGambar($file, $lama->id);
                $gambar = $fileBaru;
            } elseif ($hapusGambar) {
                $gambar = null;
            }

            $db->update("
                update master_item_generals
                set general_name = ?, color = ?, size = ?, unit_id = ?, facility = ?, picture = ?, updated_at = ?
                where id = ?
            ", [
                $v['general_name'], $v['color'], $v['size'], $unitId, $fasilitas, $gambar,
                now()->format('Y-m-d H:i:s'), $lama->id,
            ]);

            $db->commit();
        } catch (\Throwable $e) {
            $db->rollBack();
            $this->buangGambar($fileBaru);
            throw $e;
        }

        if ($gambarLama && $gambarLama !== $gambar) {
            $this->buangGambar($gambarLama);
        }

        return response()->json([
            'status' => 200,
            'id'     => (int) $lama->id,
            'kode'   => $lama->general_code,
            'nama'   => $v['general_name'],
        ]);
    }

    // ===================================================================

    // Item Code berikutnya untuk kode sub group: KODE-(nomor terbesar + 1). Nomor tanpa nol di depan.
    // $kunci = true (di dalam transaksi simpan): baris yang dibaca dikunci supaya dua simpan bersamaan tidak dapat nomor sama.
    private function kodeBerikut($kodeSub, $kunci = false)
    {
        $kodeSub = (string) $kodeSub;
        $maks = $this->db()->select("
            select max(cast(substring(general_code, ?) as unsigned)) as n
            from master_item_generals
            where general_code like ? and general_code regexp ?
        " . ($kunci ? ' for update' : ''), [
            strlen($kodeSub) + 2,
            $kodeSub . '-%',
            '^' . $kodeSub . '-[0-9]+$',   // kode sub hanya huruf & angka (POLA_KODE_SUB), aman di regexp
        ])[0]->n ?? 0;

        return $kodeSub . '-' . ((int) $maks + 1);
    }

    // Huruf besar + trim, sama dengan isian item Old
    private function besar($nilai)
    {
        return trim(mb_strtoupper((string) $nilai));
    }

    // Nama file unik per item: ITEMG-<id>-<waktu>.<ext>, di storage/app/master-item-general
    private function simpanGambar($file, $id)
    {
        $nama = 'ITEMG-' . $id . '-' . now()->format('YmdHis') . '.' . strtolower($file->getClientOriginalExtension());
        Storage::disk('local')->putFileAs(self::FOLDER_GAMBAR, $file, $nama);

        return $nama;
    }

    private function buangGambar($nama)
    {
        if ($nama) {
            Storage::disk('local')->delete(self::FOLDER_GAMBAR . '/' . $nama);
        }
    }

    // Batas upload server (terkecil dari upload_max_filesize & post_max_size) - hanya untuk pesan
    private function batasUpload()
    {
        $byte = function ($nilai) {
            $nilai = trim((string) $nilai);
            $angka = (float) $nilai;
            switch (strtoupper(substr($nilai, -1))) {
                case 'G': return $angka * 1073741824;
                case 'M': return $angka * 1048576;
                case 'K': return $angka * 1024;
                default:  return $angka;
            }
        };
        $batas = array_filter([ini_get('upload_max_filesize'), ini_get('post_max_size')], fn ($v) => $byte($v) > 0);
        if (! $batas) {
            return 'no limit';
        }
        usort($batas, fn ($a, $b) => $byte($a) <=> $byte($b));

        return round($byte($batas[0]) / 1048576, 1) . ' MB';
    }
}
