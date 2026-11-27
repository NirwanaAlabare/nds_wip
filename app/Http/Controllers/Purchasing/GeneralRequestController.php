<?php

namespace App\Http\Controllers\Purchasing;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/**
 * General Request (database nds, koneksi mysql_nds).
 *
 * Tabel:
 *   user_access_general_requests
 *                          Role Access: 1 baris = 1 user + 1 departemen.
 *                          username -> laravel_nds.users.username,
 *                          department_id -> hris.department_all.department_id (NAG / NAK, AKTIF),
 *                          cancel ('Y' = akses dicabut, baris tidak dihapus)
 *   master_departments     Master departemen dari HRIS. department_id -> hris.department_all.department_id,
 *                          department_code = alias wajib 4 karakter, hanya diisi lewat Role Department.
 *                          Departemen tanpa alias belum bisa dipakai membuat request.
 *   Nomor request: GR/{department_code}/yymm/0001, urut gabungan semua departemen, reset tiap tahun.
 *   general_requests       request_no, request_date, department_id -> master_departments.id,
 *                          created_by = username pemohon (laravel_nds.users.username),
 *                          status, approver_1 / approver_2 = username approver (laravel_nds.users.username),
 *                          approve_1_at / approve_2_at = waktu approve, cancel ('Y' = dibatalkan)
 *   general_request_items  general_request_id, item_general_id -> master_item_generals.id, qty,
 *                          unit_id -> master_units.id, notes, currency_id -> master_currencies.id,
 *                          price, cancel ('Y' = dibatalkan)
 *
 * Halaman ini hanya sisi pemohon: buat / ubah Draft, Ajukan, Batalkan, Request Ulang.
 * Approve / Tolak dibuat terpisah.
 *
 * Belum ada tabel PO / penerimaan yang terhubung, jadi qty PO & qty diterima per item masih 0
 * (tampil "Belum ada PO") sampai modul PO terhubung.
 */
class GeneralRequestController extends Controller
{
    private const KONEKSI = 'mysql_nds';

    public const DRAFT = 'Draft';
    public const MENUNGGU = 'Menunggu Approval';
    public const BATAL = 'Dibatalkan';
    public const PROSES_PO = 'Proses PO';
    public const DITOLAK = 'Ditolak';

    // Format nomor: GR/yymm/0001 (urut per bulan)
    private const PREFIX_NO = 'GR';

    private const MATA_UANG_DEFAULT = 'IDR';

    // Username yang boleh membuka & mengubah Role Access
    private const ADMIN_ROLE_ACCESS = ['nirwana_it', 'admin_01'];

    private function db()
    {
        return DB::connection(self::KONEKSI);
    }

    private function usernameLogin(): string
    {
        return (string) (Auth::user()->username ?? '');
    }

    private function bolehRoleAccess(): bool
    {
        return in_array($this->usernameLogin(), self::ADMIN_ROLE_ACCESS, true);
    }

    // Nama site HRIS -> kode singkat, untuk membedakan departemen bernama sama di NAG & NAK
    private const SITE_SINGKAT = [
        'PT. NIRWANA ALABARE GARMENT' => 'NAG',
        'PT. NIRWANA ALABARE KNITTING' => 'NAK',
    ];

    /**
     * Departemen aktif dari HRIS (NAG / NAK), key = department_id.
     */
    private function departemenHris(): array
    {
        $rows = DB::connection('mysql_hris')->select("
            select site_nirwana_name, department_id, department_name
            from department_all
            where site_nirwana_id in ('NAG', 'NAK') and status = 'AKTIF'
            group by department_id
        ");
        usort($rows, fn($a, $b) => [$a->site_nirwana_name, $a->department_name] <=> [$b->site_nirwana_name, $b->department_name]);

        $hasil = [];
        foreach ($rows as $r) {
            $hasil[$r->department_id] = (object) [
                'department_id' => $r->department_id,
                'department_name' => trim($r->department_name),
                'site_id' => self::SITE_SINGKAT[strtoupper(trim($r->site_nirwana_name))] ?? $r->site_nirwana_name,
                'site' => $r->site_nirwana_name,
            ];
        }

        // Nama yang sama di NAG & NAK (mis. WAREHOUSE) diberi akhiran site supaya bisa dibedakan
        $jumlahNama = array_count_values(array_map(fn($h) => strtoupper($h->department_name), $hasil));
        foreach ($hasil as $h) {
            $h->label = $jumlahNama[strtoupper($h->department_name)] > 1 ? "{$h->department_name} ({$h->site_id})" : $h->department_name;
        }

        return $hasil;
    }

    /**
     * Sub dept aktif dari HRIS untuk departemen tertentu: [department_id => [sub_dept_id => nama]].
     * $deptIds kosong = tidak ada yang diambil.
     */
    private function subDeptHris(array $deptIds): array
    {
        $deptIds = array_values(array_unique(array_filter(array_map('strval', $deptIds))));
        if (!$deptIds) {
            return [];
        }
        $tanda = implode(',', array_fill(0, count($deptIds), '?'));
        $hasil = [];
        foreach (DB::connection('mysql_hris')->select("
            select department_id, sub_dept_id, sub_dept_name
            from department_all
            where site_nirwana_id in ('NAG', 'NAK') and status = 'AKTIF' and department_id in ($tanda)
              and sub_dept_id is not null and sub_dept_id <> ''
            order by department_id, sub_dept_name
        ", $deptIds) as $r) {
            $hasil[$r->department_id][$r->sub_dept_id] = trim($r->sub_dept_name);
        }

        return $hasil;
    }

    /**
     * Nama sub dept (termasuk yang sudah tidak aktif) untuk ditampilkan: [sub_dept_id => nama].
     */
    private function namaSubDept(array $subIds): array
    {
        $subIds = array_values(array_unique(array_filter(array_map('strval', $subIds))));
        if (!$subIds) {
            return [];
        }
        $tanda = implode(',', array_fill(0, count($subIds), '?'));
        $hasil = [];
        foreach (DB::connection('mysql_hris')->select("
            select sub_dept_id, sub_dept_name from department_all where sub_dept_id in ($tanda)
        ", $subIds) as $r) {
            $hasil[$r->sub_dept_id] ??= trim($r->sub_dept_name);
        }

        return $hasil;
    }

    /**
     * Label departemen dari department_id HRIS.
     */
    private function labelDepartemen($deptId, array $hris): string
    {
        return isset($hris[$deptId]) ? $hris[$deptId]->label : ((string) $deptId ?: '-');
    }

    /**
     * master_departments.id untuk 1 department_id HRIS; dibuat dari data HRIS kalau belum ada.
     * Dipanggil di dalam transaksi.
     */
    private function idMasterDepartemen(string $deptHris, array $hris): int
    {
        $db = $this->db();
        $ada = $db->selectOne("
            select id, cancel from master_departments where department_id = ? order by (coalesce(cancel, '') = 'Y'), id limit 1 for update
        ", [$deptHris]);
        if ($ada) {
            if (($ada->cancel ?? '') === 'Y') {
                $db->update("update master_departments set cancel = 'N', updated_at = ? where id = ?", [now(), $ada->id]);
            }
            return (int) $ada->id;
        }

        // department_code (alias) dikosongkan, diisi lewat Role Department
        $sekarang = now();
        $db->insert("
            insert into master_departments (department_code, department_name, department_id, cancel, created_by, created_at, updated_at)
            values (null, ?, ?, 'N', ?, ?, ?)
        ", [$hris[$deptHris]->department_name ?? $deptHris, $deptHris, $this->usernameLogin(), $sekarang, $sekarang]);

        return (int) $db->getPdo()->lastInsertId();
    }

    // ===================================================================
    // Halaman
    // ===================================================================

    public function index(Request $request)
    {
        $tglAwal = $this->tanggalValid($request->tgl_awal) ?? date('Y-m-01');
        $tglAkhir = $this->tanggalValid($request->tgl_akhir) ?? date('Y-m-t');
        if ($tglAwal > $tglAkhir) {
            [$tglAwal, $tglAkhir] = [$tglAkhir, $tglAwal];
        }

        $db = $this->db();
        $hris = $this->departemenHris();

        // Dropdown departemen di form = Role Access milik user login saja
        // id = department_id HRIS. kode = alias di master_departments; tanpa alias belum bisa dipakai membuat request
        $kodeDept = $this->kodeDepartemen();
        $departemen = array_map(function ($d) use ($hris, $kodeDept) {
            $d->nama = $this->labelDepartemen($d->department_id, $hris);
            $d->kode = $kodeDept[$d->department_id] ?? null;
            $d->no_berikut = $d->kode ? $this->nomorBerikut($d->kode, false) : null;
            return $d;
        }, $db->select("
            select department_id as id, department_id
            from user_access_general_requests
            where username = ? and coalesce(cancel, '') <> 'Y'
            group by department_id
        ", [$this->usernameLogin()]));
        usort($departemen, fn($a, $b) => strcmp($a->nama, $b->nama));

        // Sub dept per departemen milik user (dipilih per item, wajib)
        $subDept = [];
        foreach ($this->subDeptHris(array_column($departemen, 'department_id')) as $dept => $subs) {
            foreach ($subs as $id => $nama) {
                $subDept[$dept][] = ['id' => $id, 'nama' => $nama];
            }
        }

        // Barang tidak dimuat semua (bisa sangat banyak), dicari lewat cariBarang()
        $adaBarang = (bool) $db->selectOne("
            select 1 as ada from master_item_generals where coalesce(cancel, '') <> 'Y' limit 1
        ");

        // Sub group untuk chip filter katalog (sedikit, cukup dimuat sekali) + jumlah barang aktif
        $subGroup = $db->select("
            select s.id, s.general_subgroup_code as kode, s.general_subgroup_name as nama,
                   g.general_group_name as grup, count(i.id) as jumlah
            from master_general_subgroups s
            left join master_general_groups g on g.id = s.general_group_id
            join master_item_generals i on i.general_subgroup_id = s.id and coalesce(i.cancel, '') <> 'Y'
            where coalesce(s.cancel, '') <> 'Y'
            group by s.id, s.general_subgroup_code, s.general_subgroup_name, g.general_group_name
            order by g.general_group_name, s.general_subgroup_name
        ");

        $mataUang = $db->select("
            select id, currency_code as kode, currency_name as nama
            from master_currencies
            where coalesce(cancel, '') <> 'Y'
            order by currency_code
        ");
        $idr = collect($mataUang)->firstWhere('kode', self::MATA_UANG_DEFAULT);

        return view('purchasing.general_request.list-general-request', [
            'page' => 'dashboard-purchasing',
            'subPageGroup' => 'general-request',
            'subPage' => 'general-request',
            'containerFluid' => true,
            // Daftar request dimuat per halaman lewat requestData() (paging server)
            'requests' => [],
            'boleh_role_access' => $this->bolehRoleAccess(),
            'tgl_awal' => $tglAwal,
            'tgl_akhir' => $tglAkhir,
            // Tampil sebelum departemen dipilih
            'no_request' => self::PREFIX_NO . '/····/' . date('ym') . '/····',
            'departemen' => $departemen,
            'ada_barang' => $adaBarang,
            'sub_dept' => $subDept,
            'sub_group' => $subGroup,
            'mata_uang' => $mataUang,
            'mata_uang_default' => $idr->id ?? null,
            // Approver dipilih pemohon per request: semua user aktif, termasuk dirinya sendiri
            'approver_list' => $this->daftarApprover(),
        ]);
    }

    private function daftarApprover(): array
    {
        return DB::select("
            select username, name from users
            where deleted_at is null and coalesce(username, '') <> ''
            order by name, username
        ");
    }

    /**
     * Request beserta item-nya. Format array disamakan dengan yang dipakai view.
     */
    /**
     * Request yang boleh dilihat: admin Role Access semua departemen,
     * user lain hanya departemen (HRIS) yang ada di Role Access-nya. Dipakai di daftar & cetak PDF.
     */
    private function syaratAksesRequest(): array
    {
        if ($this->bolehRoleAccess()) {
            return ['', []];
        }

        // Departemen di Role Access, atau request yang approver (1 / 2)-nya user ini
        $saya = $this->usernameLogin();
        return ["
              and (d.department_id in (
                  select a.department_id from user_access_general_requests a
                  where a.username = ? and coalesce(a.cancel, '') <> 'Y'
              ) or g.approver_1 = ? or g.approver_2 = ?)", [$saya, $saya, $saya]];
    }

    // Approver 1 / 2: sudah approve kalau approve_x_at terisi (penolakan tidak dicatat per approver)
    private function infoApprover(int $ke, ?string $username, $waktu, array $nama): ?array
    {
        if (!$username) {
            return null;
        }

        return [
            'ke' => $ke,
            'username' => $username,
            'nama' => $nama[$username] ?? $username,
            'hasil' => $waktu ? 'Disetujui' : null,
            'waktu' => $waktu ? date('d-m-Y H:i', strtotime($waktu)) : null,
            'saya' => $username === $this->usernameLogin(),
        ];
    }

    /**
     * $untukApproval = true: hanya request yang approver-nya user login
     * (yang masih Menunggu Approval selalu tampil, yang lain sesuai rentang tanggal).
     */
    private function ambilRequest(string $tglAwal, string $tglAkhir, array $hris, bool $untukApproval = false, ?array $hanyaId = null): array
    {
        $db = $this->db();
        $saya = $this->usernameLogin();
        if ($hanyaId !== null) {
            // Satu halaman tabel (paging server): id sudah disaring & dicek hak aksesnya oleh requestData()
            if (!$hanyaId) {
                return [];
            }
            $syarat = 'and g.id in (' . implode(',', array_fill(0, count($hanyaId), '?')) . ')';
            $bind = array_values($hanyaId);
        } elseif ($untukApproval) {
            $syarat = "and (g.approver_1 = ? or g.approver_2 = ?) and coalesce(g.status, '') <> ? and (g.status = ? or g.request_date between ? and ?)";
            $bind = [$saya, $saya, self::DRAFT, self::MENUNGGU, $tglAwal, $tglAkhir];
        } else {
            [$syaratAkses, $bindAkses] = $this->syaratAksesRequest();
            $syarat = "and g.request_date between ? and ? $syaratAkses";
            $bind = array_merge([$tglAwal, $tglAkhir], $bindAkses);
        }

        $header = $db->select("
            select g.id, g.request_no, g.request_date, g.department_id, g.status, g.notes,
                   g.approver_1, g.approver_2, g.approve_1_at, g.approve_2_at,
                   g.created_at, g.updated_at,
                   g.created_by as username, d.department_id as dept_hris, d.department_name
            from general_requests g
            left join master_departments d on d.id = g.department_id
            where coalesce(g.cancel, '') <> 'Y'
              $syarat
            order by (g.status = ?) desc, g.request_date desc, g.id desc
        ", array_merge($bind, [self::MENUNGGU]));

        if (!$header) {
            return [];
        }

        $ids = array_column($header, 'id');
        $tanda = implode(',', array_fill(0, count($ids), '?'));

        $items = collect($db->select("
            select r.id, r.general_request_id, r.sub_dept_id, r.item_general_id, r.qty, r.unit_id, r.notes, r.currency_id, r.price, r.required_date,
                   i.general_code, i.general_name, i.picture, u.unit_code, c.currency_code
            from general_request_items r
            left join master_item_generals i on i.id = r.item_general_id
            left join master_units u on u.id = r.unit_id
            left join master_currencies c on c.id = r.currency_id
            where coalesce(r.cancel, '') <> 'Y'
              and r.general_request_id in ($tanda)
            order by r.id
        ", $ids))->groupBy('general_request_id');

        // Pemohon & approver (username) -> nama di users database utama (laravel_nds)
        $usernames = array_values(array_unique(array_filter(array_merge(array_column($header, 'username'), array_column($header, 'approver_1'), array_column($header, 'approver_2')))));
        $namaPemohon = [];
        if ($usernames) {
            $tandaUser = implode(',', array_fill(0, count($usernames), '?'));
            foreach (DB::select("select username, name from users where username in ($tandaUser)", $usernames) as $u) {
                $namaPemohon[$u->username] = $u->name;
            }
        }

        // Nama sub dept per item (HRIS)
        $namaSub = $this->namaSubDept($items->flatten(1)->pluck('sub_dept_id')->all());

        return array_map(function ($g) use ($items, $namaPemohon, $hris, $saya, $namaSub) {
            $itemReq = $items[$g->id] ?? collect();

            return [
                'id' => $g->id,
                'no_request' => $g->request_no,
                'tgl_request' => $g->request_date,
                // department_id HRIS, sama dengan value dropdown departemen
                'departemen_id' => $g->dept_hris,
                'departemen' => $g->dept_hris && isset($hris[$g->dept_hris]) ? $hris[$g->dept_hris]->label : ($g->department_name ?: ($g->dept_hris ?: '-')),
                'pemohon' => $g->username ? ($namaPemohon[$g->username] ?? $g->username) : '-',
                'pemohon_username' => $g->username,
                // Ubah / Ajukan / Batalkan hanya oleh pemohonnya sendiri
                'milik' => $g->username !== null && $g->username === $saya,
                'status' => $g->status ?: self::DRAFT,
                'dibuat' => $g->created_at ? date('d-m-Y H:i', strtotime($g->created_at)) : null,
                // Waktu dibatalkan (perubahan terakhir pada request yang berstatus Dibatalkan)
                // Catatan keseluruhan request (header)
                'catatan' => $g->notes,
                'diubah' => $g->updated_at ? date('d-m-Y H:i', strtotime($g->updated_at)) : null,
                // Approver yang dipilih pemohon (username) + hasil approval
                'approver' => $g->approver_1,
                'approver2' => $g->approver_2,
                'approver_nama' => $g->approver_1 ? ($namaPemohon[$g->approver_1] ?? $g->approver_1) : null,
                // Approver 1 & 2 (bersamaan): hasil masing-masing. hasil null = belum memutuskan
                'approvers' => array_values(array_filter([
                    $this->infoApprover(1, $g->approver_1, $g->approve_1_at, $namaPemohon),
                    $this->infoApprover(2, $g->approver_2, $g->approve_2_at, $namaPemohon),
                ])),
                // Tgl dibutuhkan per item; di level request ditampilkan yang paling awal
                'tgl_butuh' => $itemReq->pluck('required_date')->filter()->min(),
                'items' => $itemReq->map(fn($r) => [
                    'id' => $r->id,
                    'item_id' => $r->item_general_id,
                    'kode' => $r->general_code,
                    'nama' => $r->general_name ?? '(item tidak ditemukan)',
                    // Foto barang dari Master Item General (null = tampil ikon)
                    'gambar' => (string) $r->picture !== ''
                        ? route('general-request-gambar', ['id' => $r->item_general_id, 'v' => $r->picture]) : null,
                    'qty' => (int) $r->qty,
                    'unit_id' => $r->unit_id,
                    'satuan' => $r->unit_code ?? '-',
                    'ket' => $r->notes,
                    'currency_id' => $r->currency_id,
                    'mata_uang' => $r->currency_code,
                    'harga' => $r->price,
                    'tgl_butuh' => $r->required_date,
                    'sub_dept_id' => $r->sub_dept_id,
                    'sub_dept' => $r->sub_dept_id ? ($namaSub[$r->sub_dept_id] ?? $r->sub_dept_id) : null,
                    // Belum terhubung ke PO / penerimaan
                    'no_po' => null,
                    'tgl_po' => null,
                    'qty_po' => 0,
                    'qty_terima' => 0,
                    'terima' => [],
                ])->values()->all(),
            ];
        }, $header);
    }

    // ===================================================================
    // Simpan (baru / ubah Draft)
    // ===================================================================

    public function simpan(Request $request)
    {
        $mode = $request->mode === 'ajukan' ? 'ajukan' : 'draft';
        $id = $request->id ? (int) $request->id : null;
        $db = $this->db();

        // Departemen harus Role Access milik user login yang masih aktif
        $deptHris = trim((string) $request->department_id);
        if ($deptHris === '') {
            return $this->gagal('Pilih departemen terlebih dahulu.');
        }
        if (!$db->selectOne("
            select id from user_access_general_requests where department_id = ? and username = ? and coalesce(cancel, '') <> 'Y' limit 1
        ", [$deptHris, $this->usernameLogin()])) {
            return $this->gagal('Departemen tidak terdaftar untuk user Anda. Minta admin mengatur Role Access.');
        }
        // Departemen harus sudah punya alias (department_code) di master_departments, dipakai di nomor request
        $master = $db->selectOne("
            select id, department_code from master_departments
            where department_id = ? and coalesce(cancel, '') <> 'Y' and coalesce(department_code, '') <> ''
            order by id limit 1
        ", [$deptHris]);
        if (!$master) {
            return $this->gagal('Departemen ' . $this->labelDepartemen($deptHris, $this->departemenHris()) . ' belum punya alias. Minta admin mengisinya lewat Role Department.');
        }
        $departemenId = (int) $master->id;

        // Catatan keseluruhan request (header), opsional
        $catatan = trim((string) $request->catatan);
        if (mb_strlen($catatan) > 1000) {
            return $this->gagal('Catatan request maksimal 1000 karakter.');
        }
        $catatan = $catatan !== '' ? $catatan : null;

        // Approver: wajib saat diajukan, boleh kosong untuk Draft; boleh diri sendiri
        $approver = trim((string) $request->approver);
        if ($approver === '' && $mode === 'ajukan') {
            return $this->gagal('Pilih approver dulu sebelum mengajukan request.');
        }
        if ($approver !== '') {
            if (!DB::selectOne("select id from users where username = ? and deleted_at is null", [$approver])) {
                return $this->gagal('Approver tidak ditemukan.');
            }
        }
        $approver = $approver !== '' ? $approver : null;

        // Approver 2 opsional, tidak boleh sama dengan approver 1
        $approver2 = trim((string) $request->approver2);
        if ($approver2 !== '') {
            if ($approver2 === $approver) {
                return $this->gagal('Approver 2 tidak boleh sama dengan approver 1.');
            }
            if (!DB::selectOne("select id from users where username = ? and deleted_at is null", [$approver2])) {
                return $this->gagal('Approver 2 tidak ditemukan.');
            }
        }
        $approver2 = $approver2 !== '' && $approver ? $approver2 : null;

        $baris = is_array($request->items) ? $request->items : [];
        if (!$baris) {
            return $this->gagal('Tambahkan minimal 1 item.');
        }

        // Tgl dibutuhkan tidak boleh sebelum hari ini (berlaku juga saat ubah Draft lama)
        $hariIni = date('Y-m-d');

        // Sub dept (wajib per item) harus milik departemen yang dipilih
        $subValid = $this->subDeptHris([$deptHris])[$deptHris] ?? [];

        // Validasi item + gabung barang/satuan yang sama
        $items = [];
        foreach ($baris as $n => $b) {
            $ke = 'Item ke-' . ($n + 1);
            $subDeptId = trim((string) ($b['sub_dept_id'] ?? ''));
            if ($subDeptId === '') {
                return $this->gagal("$ke: sub dept belum dipilih.");
            }
            if (!isset($subValid[$subDeptId])) {
                return $this->gagal("$ke: sub dept tidak termasuk departemen yang dipilih / sudah tidak aktif di HRIS.");
            }
            $itemId = (int) ($b['item_id'] ?? 0);
            $currencyId = (int) ($b['currency_id'] ?? 0);
            $qty = filter_var($b['qty'] ?? null, FILTER_VALIDATE_INT);
            $harga = $b['harga'] ?? null;
            $ket = trim((string) ($b['ket'] ?? ''));
            $tglButuh = $this->tanggalValid($b['tgl_butuh'] ?? null);

            $barang = $itemId ? $db->selectOne("select id, unit_id from master_item_generals where id = ? and coalesce(cancel, '') <> 'Y'", [$itemId]) : null;
            if (!$barang) {
                return $this->gagal("$ke: barang tidak ditemukan atau sudah tidak aktif.");
            }
            if ($qty === false || $qty <= 0) {
                return $this->gagal("$ke: qty harus lebih dari 0.");
            }
            // Satuan selalu mengikuti master item, bukan dari input
            $unitId = (int) $barang->unit_id;
            if (!$unitId || !$db->selectOne("select id from master_units where id = ? and coalesce(cancel, '') <> 'Y'", [$unitId])) {
                return $this->gagal("$ke: satuan barang belum diatur / tidak aktif di master item.");
            }
            if (!$currencyId || !$db->selectOne("select id from master_currencies where id = ? and coalesce(cancel, '') <> 'Y'", [$currencyId])) {
                return $this->gagal("$ke: mata uang tidak valid.");
            }
            if (!$tglButuh) {
                return $this->gagal("$ke: tanggal dibutuhkan belum diisi.");
            }
            if ($tglButuh < $hariIni) {
                return $this->gagal("$ke: tanggal dibutuhkan tidak boleh sebelum hari ini (" . date('d-m-Y') . ").");
            }
            if ($harga === '' || $harga === null) {
                $harga = null;
            } elseif (!is_numeric($harga) || $harga < 0) {
                return $this->gagal("$ke: harga tidak valid.");
            } else {
                $harga = (float) $harga;
            }

            // Barang + satuan + tgl dibutuhkan sama -> digabung
            // Barang + satuan + tgl dibutuhkan + sub dept sama -> digabung
            $kunci = $itemId . '|' . $unitId . '|' . $tglButuh . '|' . $subDeptId;
            if (isset($items[$kunci])) {
                $items[$kunci]['qty'] += $qty;
                if ($ket !== '' && $items[$kunci]['ket'] !== $ket) {
                    $items[$kunci]['ket'] = trim($items[$kunci]['ket'] . '; ' . $ket, '; ');
                }
                continue;
            }
            $items[$kunci] = ['sub_dept_id' => $subDeptId, 'item_id' => $itemId, 'qty' => $qty, 'unit_id' => $unitId, 'currency_id' => $currencyId, 'harga' => $harga, 'ket' => $ket, 'tgl_butuh' => $tglButuh];
        }

        $status = $mode === 'ajukan' ? self::MENUNGGU : self::DRAFT;
        $sekarang = now();

        $db->beginTransaction();
        try {
            if ($id) {
                // Ubah: hanya Draft yang boleh diubah
                $lama = $db->selectOne("
                    select g.id, g.request_no, g.status, g.cancel, g.created_by as username, g.department_id
                    from general_requests g
                    where g.id = ? for update
                ", [$id]);
                if (!$lama || $lama->cancel === 'Y') {
                    $db->rollBack();
                    return $this->gagal('Request tidak ditemukan, mungkin sudah dibatalkan.', 404);
                }
                if ($lama->username !== $this->usernameLogin()) {
                    $db->rollBack();
                    return $this->gagal("Request {$lama->request_no} bukan milik Anda.", 403);
                }
                if (($lama->status ?: self::DRAFT) !== self::DRAFT) {
                    $db->rollBack();
                    return $this->gagal("Request {$lama->request_no} sudah berstatus {$lama->status}, tidak bisa diubah lagi.", 409);
                }
                // Nomor request sudah memakai alias departemen, jadi departemen tidak bisa diganti
                if ((int) $lama->department_id !== $departemenId) {
                    $db->rollBack();
                    return $this->gagal("Departemen request {$lama->request_no} tidak bisa diganti. Batalkan lalu buat request baru.", 409);
                }

                $db->update("
                    update general_requests set department_id = ?, status = ?, notes = ?, approver_1 = ?, approver_2 = ?, updated_at = ? where id = ?
                ", [$departemenId, $status, $catatan, $approver, $approver2, $sekarang, $id]);

                // Draft belum terhubung ke mana pun: item lama dihapus lalu diisi ulang
                $db->delete("delete from general_request_items where general_request_id = ?", [$id]);
                $noRequest = $lama->request_no;
            } else {
                $noRequest = $this->nomorBerikut($master->department_code, true);
                $db->insert("
                    insert into general_requests (request_no, request_date, department_id, status, notes, approver_1, approver_2, cancel, created_by, created_at, updated_at)
                    values (?, ?, ?, ?, ?, ?, ?, 'N', ?, ?, ?)
                ", [$noRequest, $sekarang->toDateString(), $departemenId, $status, $catatan, $approver, $approver2, $this->usernameLogin(), $sekarang, $sekarang]);
                $id = (int) $db->getPdo()->lastInsertId();
            }

            // created_by item = username yang login (sama seperti header request)
            $pembuat = $this->usernameLogin();
            foreach ($items as $it) {
                $db->insert("
                    insert into general_request_items
                        (general_request_id, sub_dept_id, item_general_id, qty, unit_id, notes, currency_id, price, required_date, cancel, created_by, created_at, updated_at)
                    values (?, ?, ?, ?, ?, ?, ?, ?, ?, 'N', ?, ?, ?)
                ", [$id, $it['sub_dept_id'], $it['item_id'], $it['qty'], $it['unit_id'], $it['ket'] !== '' ? $it['ket'] : null, $it['currency_id'], $it['harga'], $it['tgl_butuh'], $pembuat, $sekarang, $sekarang]);
            }

            $db->commit();
        } catch (\Illuminate\Database\QueryException $e) {
            $db->rollBack();
            // Nomor request bentrok dengan user lain yang menyimpan bersamaan
            if (($e->errorInfo[1] ?? null) == 1062) {
                return $this->gagal('Nomor request baru saja dipakai user lain. Silakan simpan ulang.', 409);
            }
            throw $e;
        } catch (\Throwable $e) {
            $db->rollBack();
            throw $e;
        }

        return response()->json([
            'status' => 200,
            'message' => $mode === 'ajukan' ? 'Request diajukan' : 'Tersimpan sebagai Draft',
            'no_request' => $noRequest,
            'status_request' => $status,
        ]);
    }

    // ===================================================================
    // Katalog barang (panel Tambah Item): dicari di server, dimuat per halaman
    // ===================================================================

    private const BARANG_PER_HALAMAN = 30;

    public function cariBarang(Request $request)
    {
        $hal = max(1, (int) $request->hal);
        $q = trim((string) $request->q);

        // Tiap kata harus ada di kode / nama / warna / ukuran (urutan kata bebas)
        $syarat = '';
        $bind = [];
        foreach (array_slice(preg_split('/\s+/', $q, -1, PREG_SPLIT_NO_EMPTY), 0, 6) as $kata) {
            $syarat .= " and concat_ws(' ', i.general_code, i.general_name, i.color, i.size) like ?";
            $bind[] = '%' . addcslashes($kata, '%_\\') . '%';
        }

        // Filter sub group (chip di panel)
        $sub = (int) $request->sub;
        if ($sub > 0) {
            $syarat .= ' and i.general_subgroup_id = ?';
            $bind[] = $sub;
        }

        $bind[] = self::BARANG_PER_HALAMAN + 1; // +1 untuk tahu masih ada halaman berikutnya
        $bind[] = ($hal - 1) * self::BARANG_PER_HALAMAN;

        $rows = $this->db()->select("
            select i.id, i.general_code as kode, i.general_name as nama, i.unit_id, i.color, i.size, u.unit_code as satuan,
                   s.general_subgroup_code as sub_kode, i.picture
            from master_item_generals i
            left join master_units u on u.id = i.unit_id and coalesce(u.cancel, '') <> 'Y'
            left join master_general_subgroups s on s.id = i.general_subgroup_id
            where coalesce(i.cancel, '') <> 'Y' $syarat
            order by i.general_name, i.id
            limit ? offset ?
        ", $bind);

        $adaLagi = count($rows) > self::BARANG_PER_HALAMAN;

        return response()->json([
            'items' => array_map(fn($r) => [
                'id' => (int) $r->id,
                'kode' => $r->kode,
                'nama' => $r->nama,
                'varian' => implode(' / ', array_filter([$r->color, $r->size], fn($v) => $v && $v !== '-')),
                'unit_id' => $r->unit_id ? (int) $r->unit_id : null,
                'satuan' => $r->satuan,
                'sub_kode' => $r->sub_kode,
                // v = nama file, jadi gambar yang diganti tidak terbaca dari cache lama
                'gambar' => (string) $r->picture !== ''
                    ? route('general-request-gambar', ['id' => $r->id, 'v' => $r->picture]) : null,
            ], array_slice($rows, 0, self::BARANG_PER_HALAMAN)),
            'ada_lagi' => $adaLagi,
        ]);
    }

    // Gambar barang untuk katalog (file yang di-upload di Master Item General, storage/app/master-item-general)
    public function gambarBarang(Request $request)
    {
        $row = $this->db()->select("
            select picture from master_item_generals where id = ? and coalesce(cancel, '') <> 'Y' limit 1
        ", [(int) $request->query('id')])[0] ?? null;
        $file = $row ? basename((string) $row->picture) : '';
        $path = 'master-item-general/' . $file;
        if ($file === '' || !Storage::disk('local')->exists($path)) {
            abort(404);
        }

        return response()->file(Storage::disk('local')->path($path), ['Cache-Control' => 'private, max-age=86400']);
    }

    // ===================================================================
    // Ubah status
    // ===================================================================

    // Draft -> Menunggu Approval
    public function ajukan(Request $request)
    {
        $id = (int) $request->id;
        $ada = $this->db()->selectOne("
            select count(*) as n from general_request_items where general_request_id = ? and coalesce(cancel, '') <> 'Y'
        ", [$id]);
        if (!$ada || !$ada->n) {
            return $this->gagal('Request belum punya item.');
        }
        $row = $this->db()->selectOne("select approver_1 from general_requests where id = ?", [$id]);
        if (!$row || !$row->approver_1) {
            return $this->gagal('Request belum punya approver. Ubah Draft lalu pilih approver dulu.');
        }

        return $this->gantiStatus($id, [self::DRAFT], function ($db) use ($id) {
            $db->update("update general_requests set status = ?, updated_at = ? where id = ?", [self::MENUNGGU, now(), $id]);
        }, 'Request diajukan');
    }

    // Draft / Menunggu Approval -> Dibatalkan (tetap tampil di daftar, bisa disaring & di-request ulang)
    public function batal(Request $request)
    {
        $id = (int) $request->id;

        return $this->gantiStatus($id, [self::DRAFT, self::MENUNGGU], function ($db) use ($id) {
            $db->update("update general_requests set status = ?, updated_at = ? where id = ?", [self::BATAL, now(), $id]);
        }, 'Request dibatalkan');
    }

    private function gantiStatus(int $id, array $boleh, callable $ubah, string $pesan)
    {
        $db = $this->db();
        $db->beginTransaction();
        try {
            $row = $db->selectOne("
                select g.id, g.request_no, g.status, g.cancel, g.created_by as username
                from general_requests g
                where g.id = ? for update
            ", [$id]);
            if (!$row || $row->cancel === 'Y') {
                $db->rollBack();
                return $this->gagal('Request tidak ditemukan, mungkin sudah dibatalkan.', 404);
            }
            if ($row->username !== $this->usernameLogin()) {
                $db->rollBack();
                return $this->gagal("Request {$row->request_no} bukan milik Anda.", 403);
            }
            $status = $row->status ?: self::DRAFT;
            if (!in_array($status, $boleh, true)) {
                $db->rollBack();
                return $this->gagal("Request {$row->request_no} sudah berstatus {$status}. Muat ulang halaman.", 409);
            }
            $ubah($db);
            $db->commit();
        } catch (\Throwable $e) {
            $db->rollBack();
            throw $e;
        }

        return response()->json(['status' => 200, 'message' => $pesan, 'no_request' => $row->request_no]);
    }

    // ===================================================================
    // Data tabel daftar request: paging, cari & filter status di server (DataTables server-side)
    // ===================================================================

    public function requestData(Request $request)
    {
        $db = $this->db();
        $tglAwal = $this->tanggalValid($request->tgl_awal) ?? date('Y-m-01');
        $tglAkhir = $this->tanggalValid($request->tgl_akhir) ?? date('Y-m-t');
        if ($tglAwal > $tglAkhir) {
            [$tglAwal, $tglAkhir] = [$tglAkhir, $tglAwal];
        }
        $mulai = max(0, (int) $request->start);
        $panjang = min(100, max(1, (int) ($request->length ?: 10)));
        $status = trim((string) $request->status);
        $cari = trim((string) ($request->input('search.value') ?? ''));

        [$syaratAkses, $bindAkses] = $this->syaratAksesRequest();
        $syarat = "coalesce(g.cancel, '') <> 'Y' and g.request_date between ? and ? $syaratAkses";
        $bind = array_merge([$tglAwal, $tglAkhir], $bindAkses);

        // Cari: no request, notes, departemen, pemohon (username / nama), status, kode / nama barang
        if ($cari !== '') {
            $suka = '%' . addcslashes($cari, '%_\\') . '%';
            $namaCocok = array_column(DB::select("
                select username from users where (name like ? or username like ?) and coalesce(username, '') <> '' limit 200
            ", [$suka, $suka]), 'username');
            $syaratNama = $namaCocok ? ' or g.created_by in (' . implode(',', array_fill(0, count($namaCocok), '?')) . ')' : '';
            $syarat .= " and (g.request_no like ? or g.notes like ? or d.department_name like ? or g.status like ? or g.created_by like ?
                or exists (
                    select 1 from general_request_items r
                    join master_item_generals i on i.id = r.item_general_id
                    where r.general_request_id = g.id and coalesce(r.cancel, '') <> 'Y'
                      and (i.general_name like ? or i.general_code like ?)
                ) $syaratNama)";
            $bind = array_merge($bind, [$suka, $suka, $suka, $suka, $suka, $suka, $suka], $namaCocok);
        }

        // Jumlah per status (untuk kartu ringkasan): rentang tanggal + cari, tanpa filter status
        $perStatus = [];
        foreach ($db->select("
            select coalesce(nullif(g.status, ''), ?) as status, count(*) as n
            from general_requests g
            left join master_departments d on d.id = g.department_id
            where $syarat
            group by coalesce(nullif(g.status, ''), ?)
        ", array_merge([self::DRAFT], $bind, [self::DRAFT])) as $r) {
            $perStatus[$r->status] = (int) $r->n;
        }
        $semua = array_sum($perStatus);

        if ($status !== '') {
            $syarat .= " and coalesce(nullif(g.status, ''), ?) = ?";
            $bind = array_merge($bind, [self::DRAFT, $status]);
        }
        $tersaring = $status !== '' ? ($perStatus[$status] ?? 0) : $semua;

        // Hanya id 1 halaman yang diambil lengkap beserta item-nya
        $ids = array_column($db->select("
            select g.id
            from general_requests g
            left join master_departments d on d.id = g.department_id
            where $syarat
            order by (g.status = ?) desc, g.request_date desc, g.id desc
            limit $panjang offset $mulai
        ", array_merge($bind, [self::MENUNGGU])), 'id');

        $requests = $this->ambilRequest($tglAwal, $tglAkhir, $this->departemenHris(), false, $ids);

        $kolom = ['caret', 'request', 'dept', 'notes', 'item', 'butuh', 'status', 'aksi'];
        $baris = [];
        foreach ($requests as $i => $req) {
            $sel = [];
            foreach ($kolom as $k) {
                $sel[] = view('purchasing.general_request._sel-request', ['req' => $req, 'i' => $i, 'kolom' => $k])->render();
            }
            $baris[] = $sel;
        }

        return response()->json([
            'draw' => (int) $request->draw,
            'recordsTotal' => $semua,
            'recordsFiltered' => $tersaring,
            'data' => $baris,
            // Data lengkap baris di halaman ini (detail, tracking, ubah draft), index = urutan baris
            'requests' => $requests,
            'jumlah' => ['semua' => $semua] + $perStatus,
        ]);
    }

    // ===================================================================
    // Export Excel (FastExcel): 1 baris = 1 item per sub dept, mengikuti filter halaman
    // ===================================================================

    public function exportExcel(Request $request)
    {
        $tglAwal = $this->tanggalValid($request->tgl_awal) ?? date('Y-m-01');
        $tglAkhir = $this->tanggalValid($request->tgl_akhir) ?? date('Y-m-t');
        if ($tglAwal > $tglAkhir) {
            [$tglAwal, $tglAkhir] = [$tglAkhir, $tglAwal];
        }
        $status = trim((string) $request->status);

        // Hak lihat sama dengan daftar (Role Access / approver)
        $requests = array_filter(
            $this->ambilRequest($tglAwal, $tglAkhir, $this->departemenHris()),
            fn($r) => $status === '' || $r['status'] === $status
        );

        $judul = 'General Request';
        $excel = \avadim\FastExcelLaravel\Excel::create([$judul]);
        $sheet = $excel->sheet($judul);

        $periode = date('d-m-Y', strtotime($tglAwal)) . ' s/d ' . date('d-m-Y', strtotime($tglAkhir));
        $sheet->writeRow(['Export General Request'])->applyFontStyleBold()->applyFontSize(14);
        $sheet->writeRow(["Periode: $periode" . ($status !== '' ? "  |  Status: $status" : '') . '  |  Dicetak: ' . date('d-m-Y H:i') . ' oleh ' . $this->usernameLogin()]);
        $sheet->writeRow([]);

        $kepala = [
            'No', 'No Request', 'Tgl Request', 'Departemen', 'Pemohon', 'Status',
            'Approver 1', 'Approve 1', 'Approver 2', 'Approve 2', 'Notes Request',
            'Kode Barang', 'Nama Barang', 'Sub Dept', 'Tgl Butuh', 'Qty', 'Unit', 'Curr', 'Harga', 'Subtotal', 'Keterangan Item',
        ];
        $sheet->writeRow($kepala)
            ->applyFontStyleBold()->applyFontColor('#FFFFFF')->applyBgColor('#26262B')
            ->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->setColWidths([5, 22, 12, 26, 18, 18, 18, 17, 18, 17, 30, 14, 32, 24, 12, 8, 8, 7, 13, 15, 30]);

        $tglId = fn($d) => $d ? date('d-m-Y', strtotime($d)) : '';
        $no = 1;
        foreach ($requests as $r) {
            $ap = collect($r['approvers'])->keyBy('ke');
            foreach ($r['items'] as $it) {
                $harga = $it['harga'] !== null ? (float) $it['harga'] : null;
                $sheet->writeRow([
                    $no++,
                    $r['no_request'],
                    $tglId($r['tgl_request']),
                    $r['departemen'],
                    $r['pemohon'],
                    $r['status'],
                    $ap[1]['nama'] ?? '',
                    $ap[1]['waktu'] ?? '',
                    $ap[2]['nama'] ?? '',
                    $ap[2]['waktu'] ?? '',
                    $r['catatan'] ?? '',
                    $it['kode'] ?? '',
                    $it['nama'],
                    $it['sub_dept'] ?? '',
                    $tglId($it['tgl_butuh']),
                    (int) $it['qty'],
                    $it['satuan'],
                    $it['mata_uang'] ?? '',
                    $harga ?? '',
                    $harga !== null ? $harga * $it['qty'] : '',
                    $it['ket'] ?? '',
                ])->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
            }
        }
        if ($no === 1) {
            $sheet->writeRow(['Tidak ada data pada filter ini']);
        }
        $sheet->setFreezeRows(4);

        $nama = 'General Request ' . date('Ymd', strtotime($tglAwal)) . '-' . date('Ymd', strtotime($tglAkhir)) . '.xlsx';
        $tmp = sys_get_temp_dir() . DIRECTORY_SEPARATOR . uniqid('gr_export_') . '.xlsx';
        $excel->save($tmp);

        return response()->download($tmp, $nama)->deleteFileAfterSend(true);
    }

    // ===================================================================
    // Approval: menu terpisah, untuk semua user login (bukan hanya purchasing)
    // ===================================================================

    public function approvalIndex(Request $request)
    {
        $tglAwal = $request->tgl_awal ?: date('Y-m-d', strtotime('-30 days'));
        $tglAkhir = $request->tgl_akhir ?: date('Y-m-d');

        return view('purchasing.general_request.approval-general-request', [
            'page' => 'dashboard-purchasing',
            'subPageGroup' => 'general-request',
            'subPage' => 'general-request-approval',
            'containerFluid' => true,
            'requests' => $this->ambilRequest($tglAwal, $tglAkhir, $this->departemenHris(), true),
            'tgl_awal' => $tglAwal,
            'tgl_akhir' => $tglAkhir,
        ]);
    }

    // Approve: approve_x_at diisi. Semua approver (1, atau 1 & 2) sudah approve -> Proses PO
    public function approvalSetuju(Request $request)
    {
        return $this->putusApproval($request, true);
    }

    // Reject: salah satu approver menolak -> Ditolak (tanpa alasan)
    public function approvalTolak(Request $request)
    {
        return $this->putusApproval($request, false);
    }

    /**
     * id = 1 request, atau ids = [..] untuk aksi massal.
     * Massal: tiap request diproses sendiri-sendiri (transaksi masing-masing), yang gagal dilaporkan.
     */
    private function putusApproval(Request $request, bool $setuju)
    {
        if (!is_array($request->ids)) {
            $hasil = $this->putusSatu((int) $request->id, $setuju);
            return $hasil['ok']
                ? response()->json(['status' => 200, 'message' => $hasil['pesan'], 'no_request' => $hasil['no']])
                : $this->gagal($hasil['pesan'], $hasil['kode']);
        }

        // Aksi massal hanya untuk Approve; Reject satu per satu
        if (!$setuju) {
            return $this->gagal('Reject hanya bisa satu per satu.');
        }

        $ids = array_values(array_unique(array_filter(array_map('intval', $request->ids))));
        if (!$ids) {
            return $this->gagal('Pilih request terlebih dahulu.');
        }
        if (count($ids) > 200) {
            return $this->gagal('Maksimal 200 request sekali proses.');
        }

        $berhasil = [];
        $gagal = [];
        foreach ($ids as $id) {
            $hasil = $this->putusSatu($id, $setuju);
            if ($hasil['ok']) {
                $berhasil[] = ['no' => $hasil['no'], 'pesan' => $hasil['pesan']];
            } else {
                $gagal[] = ['no' => $hasil['no'] ?? "#$id", 'pesan' => $hasil['pesan']];
            }
        }

        return response()->json([
            'status' => 200,
            'message' => count($berhasil) . ' request ' . ($setuju ? 'disetujui' : 'ditolak')
                . ($gagal ? ', ' . count($gagal) . ' gagal' : ''),
            'berhasil' => $berhasil,
            'gagal' => $gagal,
        ]);
    }

    /**
     * Putuskan 1 request oleh user login. Hasil: ok, kode (HTTP kalau gagal), pesan, no (request_no).
     */
    private function putusSatu(int $id, bool $setuju): array
    {
        $tolak = fn($pesan, $kode, $no = null) => ['ok' => false, 'kode' => $kode, 'pesan' => $pesan, 'no' => $no];

        $db = $this->db();
        $db->beginTransaction();
        try {
            $row = $db->selectOne("
                select id, request_no, status, approver_1, approver_2, approve_1_at, approve_2_at, cancel
                from general_requests where id = ? for update
            ", [$id]);
            if (!$row || $row->cancel === 'Y') {
                $db->rollBack();
                return $tolak('Request tidak ditemukan.', 404);
            }
            $saya = $this->usernameLogin();
            // Slot approver milik user ini: 1 atau 2
            $ke = $row->approver_1 === $saya ? 1 : ($row->approver_2 === $saya ? 2 : null);
            if (!$ke) {
                $db->rollBack();
                return $tolak("Anda bukan approver request {$row->request_no}.", 403, $row->request_no);
            }
            if ($row->status !== self::MENUNGGU) {
                $db->rollBack();
                return $tolak("Request {$row->request_no} sudah berstatus {$row->status}. Muat ulang halaman.", 409, $row->request_no);
            }
            if (($ke === 1 ? $row->approve_1_at : $row->approve_2_at) !== null) {
                $db->rollBack();
                return $tolak("Anda sudah approve request {$row->request_no}. Muat ulang halaman.", 409, $row->request_no);
            }

            $sekarang = now();
            if (!$setuju) {
                $db->update("update general_requests set status = ?, updated_at = ? where id = ?", [self::DITOLAK, $sekarang, $id]);
                $pesan = 'Request ditolak';
            } else {
                $sudah1 = $ke === 1 || $row->approve_1_at !== null;
                $sudah2 = !$row->approver_2 || $ke === 2 || $row->approve_2_at !== null;
                $status = $sudah1 && $sudah2 ? self::PROSES_PO : self::MENUNGGU;
                $kolom = $ke === 1 ? 'approve_1_at' : 'approve_2_at';
                $db->update("update general_requests set status = ?, $kolom = ?, updated_at = ? where id = ?", [$status, $sekarang, $sekarang, $id]);
                $pesan = $status === self::PROSES_PO ? 'Request disetujui' : 'Disetujui, menunggu approver lain';
            }
            $db->commit();
        } catch (\Throwable $e) {
            $db->rollBack();
            throw $e;
        }

        return ['ok' => true, 'kode' => 200, 'pesan' => $pesan, 'no' => $row->request_no];
    }

    // ===================================================================
    // Cetak PDF (semua status kecuali Dibatalkan)
    // ===================================================================

    // Kop surat per site departemen (HRIS). Alamat NAK belum diisi: tampil nama PT saja
    private const KOP = [
        'NAG' => [
            'nama' => 'PT. Nirwana Alabare Garment',
            'alamat' => ['Jl. Raya Rancaekek - Majalaya No. 289', 'Desa Solokan Jeruk, Kec. Solokan Jeruk, Kabupaten Bandung 40382'],
            'telp' => 'Telp. 022-5959049  ·  Fax. 022-5959261',
            'logo' => 'nag-logo.png',
        ],
        'NAK' => [
            'nama' => 'PT. Nirwana Alabare Knitting',
            'alamat' => [],
            'telp' => '',
            'logo' => null,
        ],
    ];

    public function cetakPdf(int $id)
    {
        $db = $this->db();
        [$syaratAkses, $bindAkses] = $this->syaratAksesRequest();
        $g = $db->selectOne("
            select g.id, g.request_no, g.request_date, g.status, g.notes, g.approver_1, g.approver_2, g.approve_1_at, g.approve_2_at,
                   g.created_at, g.created_by as username,
                   d.department_id as dept_hris, d.department_name
            from general_requests g
            left join master_departments d on d.id = g.department_id
            where g.id = ? and coalesce(g.cancel, '') <> 'Y'
              $syaratAkses
        ", array_merge([$id], $bindAkses));
        abort_if(!$g, 404, 'Request tidak ditemukan atau bukan departemen Anda.');
        abort_if(($g->status ?: self::DRAFT) === self::BATAL, 403, "Request {$g->request_no} sudah dibatalkan, tidak bisa dicetak.");

        $rows = $db->select("
            select r.item_general_id, r.qty, r.unit_id, r.notes, r.currency_id, r.price, r.required_date,
                   i.general_code, i.general_name, i.color, i.size, u.unit_code, c.currency_code
            from general_request_items r
            left join master_item_generals i on i.id = r.item_general_id
            left join master_units u on u.id = r.unit_id
            left join master_currencies c on c.id = r.currency_id
            where r.general_request_id = ? and coalesce(r.cancel, '') <> 'Y'
            order by r.id
        ", [$id]);

        // Global: barang yang sama (satuan, mata uang & harga sama) dari beberapa sub dept / tgl digabung jadi 1 baris
        $items = [];
        foreach ($rows as $r) {
            $kunci = implode('|', [$r->item_general_id, $r->unit_id, $r->currency_id, $r->price]);
            if (!isset($items[$kunci])) {
                $items[$kunci] = (object) [
                    'kode' => $r->general_code ?? '-',
                    'nama' => $r->general_name ?? '(item tidak ditemukan)',
                    'warna' => $r->color ?: '-',
                    'ukuran' => $r->size ?: '-',
                    'qty' => 0,
                    'satuan' => $r->unit_code ?? '-',
                    'mata_uang' => $r->currency_code ?? '',
                    'harga' => $r->price !== null ? (float) $r->price : null,
                    'tgl_butuh' => $r->required_date,
                    'ket' => [],
                ];
            }
            $it = $items[$kunci];
            $it->qty += (int) $r->qty;
            if ($r->required_date && (!$it->tgl_butuh || $r->required_date < $it->tgl_butuh)) {
                $it->tgl_butuh = $r->required_date;
            }
            if ($r->notes && !in_array($r->notes, $it->ket, true)) {
                $it->ket[] = $r->notes;
            }
        }
        $items = array_values($items);

        // Total per satuan & per mata uang (bisa campur)
        $totalQty = [];
        $totalUang = [];
        foreach ($items as $it) {
            $totalQty[$it->satuan] = ($totalQty[$it->satuan] ?? 0) + $it->qty;
            if ($it->harga !== null) {
                $totalUang[$it->mata_uang] = ($totalUang[$it->mata_uang] ?? 0) + $it->qty * $it->harga;
            }
        }

        $hris = $g->dept_hris ? $this->departemenHris() : [];
        $site = isset($hris[$g->dept_hris]) ? $hris[$g->dept_hris]->site_id : 'NAG';
        $kop = self::KOP[$site] ?? self::KOP['NAG'];
        $kop['logo'] = $kop['logo'] && is_file(public_path($kop['logo'])) ? public_path($kop['logo']) : null;

        $pemohon = $g->username ? DB::selectOne("select name from users where username = ?", [$g->username]) : null;
        // Approver yang dipilih pemohon: Waiting = approver 1, Waiting 2 = approver 2
        $namaApprover = fn($u) => $u ? (DB::selectOne("select name from users where username = ?", [$u])->name ?? $u) : null;

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('purchasing.general_request.pdf-general-request', [
            'g' => $g,
            'kop' => $kop,
            'departemen' => isset($hris[$g->dept_hris]) ? $hris[$g->dept_hris]->label : ($g->department_name ?: '-'),
            'status' => $g->status ?: self::DRAFT,
            'items' => $items,
            'totalQty' => $totalQty,
            'totalUang' => $totalUang,
            'pemohon' => $g->username,
            'namaPemohon' => $pemohon->name ?? $g->username,
            'ttdApprover' => [
                ['nama' => $namaApprover($g->approver_1), 'hasil' => $g->approve_1_at ? 'Disetujui' : null, 'waktu' => $g->approve_1_at],
                ['nama' => $namaApprover($g->approver_2), 'hasil' => $g->approve_2_at ? 'Disetujui' : null, 'waktu' => $g->approve_2_at],
            ],
        ])->setPaper('a4', 'portrait');

        // Nomor halaman "Hal. x / y" di kanan bawah (counter(pages) tidak didukung dompdf)
        $pdf->render();
        $dompdf = $pdf->getDomPDF();
        $canvas = $dompdf->getCanvas();
        $font = $dompdf->getFontMetrics()->getFont('DejaVu Sans');
        $teks = 'Hal. {PAGE_NUM} / {PAGE_COUNT}';
        $lebar = $dompdf->getFontMetrics()->getTextWidth('Hal. 9 / 9', $font, 5.3);
        $canvas->page_text($canvas->get_width() - 22.5 - $lebar, $canvas->get_height() - 19, $teks, $font, 5.3, [0.545, 0.565, 0.6]);

        return $pdf->stream(str_replace('/', '-', $g->request_no) . '.pdf');
    }

    // ===================================================================
    // Role Access (user -> departemen / sub dept), hanya ADMIN_ROLE_ACCESS
    // ===================================================================

    public function roleAccessData()
    {
        if (!$this->bolehRoleAccess()) {
            return $this->gagal('Anda tidak punya akses ke Role Access.', 403);
        }

        $hris = $this->departemenHris();

        $users = DB::select("
            select username, name
            from users
            where deleted_at is null and username is not null and username <> ''
            order by username
        ");

        $akses = $this->db()->select("
            select id, username, department_id
            from user_access_general_requests
            where coalesce(cancel, '') <> 'Y'
            order by username, id
        ");

        $namaUser = [];
        foreach ($users as $u) {
            $namaUser[$u->username] = $u->name;
        }

        // Dikelompokkan per user
        $daftar = [];
        foreach ($akses as $a) {
            $daftar[$a->username] ??= [
                'username' => $a->username,
                'nama' => $namaUser[$a->username] ?? null,
                'departemen' => [],
            ];
            $daftar[$a->username]['departemen'][] = [
                'department_id' => $a->department_id,
                'label' => $this->labelDepartemen($a->department_id, $hris),
                'site_id' => $hris[$a->department_id]->site_id ?? null, // NAG / NAK, untuk warna chip
                'aktif_hris' => isset($hris[$a->department_id]), // sudah tidak aktif / tidak ada di HRIS
            ];
        }

        return response()->json([
            'users' => $users,
            'departemen' => array_values(array_map(fn($h) => [
                'department_id' => $h->department_id,
                'department_name' => $h->department_name,
                'site_id' => $h->site_id,
                'site' => $h->site,
            ], $hris)),
            'akses' => array_values($daftar),
        ]);
    }

    /**
     * Simpan akses 1 user: department_ids = daftar lengkap departemen user itu.
     * Yang tidak ada di daftar dicabut (cancel = 'Y'), yang baru ditambah,
     * yang pernah dicabut diaktifkan lagi (baris lama dipakai ulang, id tetap).
     * Daftar kosong = cabut semua akses user itu.
     */
    public function roleAccessSimpan(Request $request)
    {
        if (!$this->bolehRoleAccess()) {
            return $this->gagal('Anda tidak punya akses ke Role Access.', 403);
        }

        $username = trim((string) $request->username);
        if ($username === '' || !DB::selectOne("select id from users where username = ? and deleted_at is null", [$username])) {
            return $this->gagal('User tidak ditemukan.');
        }

        $deptIds = array_values(array_unique(array_filter(array_map('strval', (array) $request->department_ids))));
        $hris = $this->departemenHris();
        foreach ($deptIds as $d) {
            if (!isset($hris[$d])) {
                return $this->gagal("Departemen $d tidak ditemukan / sudah tidak aktif di HRIS.");
            }
        }

        $db = $this->db();
        $sekarang = now();
        $tambah = $cabut = 0;

        $db->beginTransaction();
        try {
            $lama = $db->select("
                select id, department_id, cancel from user_access_general_requests where username = ? for update
            ", [$username]);

            $perDept = [];
            foreach ($lama as $l) {
                // Kalau ada dobel, yang aktif diutamakan
                if (!isset($perDept[$l->department_id]) || ($l->cancel ?? '') !== 'Y') {
                    $perDept[$l->department_id] = $l;
                }
            }

            // Cabut yang tidak dipilih lagi
            foreach ($lama as $l) {
                if (($l->cancel ?? '') !== 'Y' && !in_array((string) $l->department_id, $deptIds, true)) {
                    $db->update("update user_access_general_requests set cancel = 'Y', updated_at = ? where id = ?", [$sekarang, $l->id]);
                    $cabut++;
                }
            }

            // Tambah / aktifkan lagi (hanya user_access_general_requests, master_departments tidak disentuh)
            foreach ($deptIds as $d) {
                $ada = $perDept[$d] ?? null;
                if ($ada && ($ada->cancel ?? '') !== 'Y') {
                    continue;
                }
                if ($ada) {
                    $db->update("update user_access_general_requests set cancel = 'N', updated_at = ? where id = ?", [$sekarang, $ada->id]);
                } else {
                    $db->insert("
                        insert into user_access_general_requests (username, department_id, cancel, created_by, created_at, updated_at)
                        values (?, ?, 'N', ?, ?, ?)
                    ", [$username, $d, $this->usernameLogin(), $sekarang, $sekarang]);
                }
                $tambah++;
            }

            $db->commit();
        } catch (\Throwable $e) {
            $db->rollBack();
            throw $e;
        }

        return response()->json([
            'status' => 200,
            'message' => $deptIds ? 'Role Access tersimpan' : 'Semua akses user dicabut',
            'tambah' => $tambah,
            'cabut' => $cabut,
        ]);
    }

    // ===================================================================
    // Role Department (alias / department_code per departemen HRIS), hanya ADMIN_ROLE_ACCESS
    // ===================================================================

    private const PANJANG_KODE = 4;

    public function roleDepartmentData()
    {
        if (!$this->bolehRoleAccess()) {
            return $this->gagal('Anda tidak punya akses ke Role Department.', 403);
        }

        $kode = [];
        foreach ($this->db()->select("
            select department_id, department_code
            from master_departments
            where coalesce(cancel, '') <> 'Y'
            order by id
        ") as $m) {
            $kode[$m->department_id] ??= $m->department_code;
        }

        return response()->json([
            'panjang_kode' => self::PANJANG_KODE,
            'departemen' => array_values(array_map(fn($h) => [
                'department_id' => $h->department_id,
                'department_name' => $h->department_name,
                'label' => $h->label,
                'site_id' => $h->site_id,
                'site' => $h->site,
                'department_code' => $kode[$h->department_id] ?? null,
            ], $this->departemenHris())),
        ]);
    }

    /**
     * items = [{department_id, department_code}] yang berubah saja.
     * Kode kosong = alias dihapus (department_code = NULL).
     */
    public function roleDepartmentSimpan(Request $request)
    {
        if (!$this->bolehRoleAccess()) {
            return $this->gagal('Anda tidak punya akses ke Role Department.', 403);
        }

        $hris = $this->departemenHris();
        $baris = is_array($request->items) ? $request->items : [];
        if (!$baris) {
            return $this->gagal('Tidak ada perubahan.');
        }

        $ubah = [];
        foreach ($baris as $b) {
            $deptId = trim((string) ($b['department_id'] ?? ''));
            $kode = strtoupper(trim((string) ($b['department_code'] ?? '')));
            if (!isset($hris[$deptId])) {
                return $this->gagal("Departemen $deptId tidak ditemukan / sudah tidak aktif di HRIS.");
            }
            $nama = $hris[$deptId]->label;
            if ($kode !== '' && !preg_match('/^[A-Z0-9.]{' . self::PANJANG_KODE . '}$/', $kode)) {
                return $this->gagal("$nama: alias wajib " . self::PANJANG_KODE . " karakter, hanya huruf, angka, dan titik.");
            }
            $ubah[$deptId] = $kode === '' ? null : $kode;
        }

        // Alias tidak boleh sama di antara isian
        $dipakai = array_filter(array_count_values(array_filter($ubah)), fn($n) => $n > 1);
        if ($dipakai) {
            return $this->gagal('Alias ' . implode(', ', array_keys($dipakai)) . ' dipakai lebih dari satu departemen.');
        }

        $db = $this->db();
        $db->beginTransaction();
        try {
            foreach ($ubah as $deptId => $kode) {
                if ($kode !== null) {
                    $bentrok = $db->selectOne("
                        select department_id from master_departments where department_code = ? and department_id <> ? limit 1
                    ", [$kode, $deptId]);
                    // Bentrok dengan departemen lain yang aliasnya tidak ikut diubah di simpanan ini
                    if ($bentrok && (!array_key_exists($bentrok->department_id, $ubah) || $ubah[$bentrok->department_id] === $kode)) {
                        $db->rollBack();
                        return $this->gagal("Alias $kode sudah dipakai " . $this->labelDepartemen($bentrok->department_id, $hris) . '.');
                    }
                }
            }

            // Kosongkan dulu supaya tukar alias antar departemen tidak kena unique key
            foreach ($ubah as $deptId => $kode) {
                $db->update("update master_departments set department_code = null where department_id = ?", [$deptId]);
            }

            $sekarang = now();
            foreach ($ubah as $deptId => $kode) {
                $id = $this->idMasterDepartemen($deptId, $hris);
                $db->update("
                    update master_departments set department_code = ?, department_name = ?, updated_at = ? where id = ?
                ", [$kode, $hris[$deptId]->department_name, $sekarang, $id]);
            }

            $db->commit();
        } catch (\Throwable $e) {
            $db->rollBack();
            throw $e;
        }

        return response()->json([
            'status' => 200,
            'message' => count($ubah) . ' alias departemen tersimpan',
        ]);
    }

    // ===================================================================
    // Helper
    // ===================================================================

    /**
     * department_id HRIS -> alias (department_code) yang sudah diisi di master_departments.
     */
    private function kodeDepartemen(): array
    {
        $hasil = [];
        foreach ($this->db()->select("
            select department_id, department_code
            from master_departments
            where coalesce(cancel, '') <> 'Y' and coalesce(department_code, '') <> ''
            order by id
        ") as $m) {
            $hasil[$m->department_id] ??= $m->department_code;
        }

        return $hasil;
    }

    /**
     * Nomor berikutnya GR/{alias}/yymm/0001. Urutan 1 untuk semua departemen (tidak per alias),
     * berlanjut sepanjang tahun dan reset ke 0001 di tahun baru.
     * $kunci = true dipakai di dalam transaksi saat simpan (baris tahun ini dikunci); false hanya untuk perkiraan di form.
     * Request yang dibatalkan tetap dihitung supaya nomor tidak dipakai ulang.
     */
    private function nomorBerikut(string $kode, bool $kunci): string
    {
        // GR/{alias}/yy??/nnnn: nomor urut tahun ini dari semua alias & bulan
        $row = $this->db()->selectOne("
            select max(cast(substring_index(request_no, '/', -1) as unsigned)) as urut
            from general_requests
            where request_no like ?
        " . ($kunci ? ' for update' : ''), [self::PREFIX_NO . '/%/' . date('y') . '__/%']);

        $awalan = self::PREFIX_NO . '/' . $kode . '/' . date('ym') . '/';
        $urut = ((int) ($row->urut ?? 0)) + 1;

        return $awalan . str_pad($urut, 4, '0', STR_PAD_LEFT);
    }

    private function tanggalValid($tgl): ?string
    {
        if (!is_string($tgl) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $tgl)) {
            return null;
        }
        [$y, $m, $d] = array_map('intval', explode('-', $tgl));

        return checkdate($m, $d, $y) ? $tgl : null;
    }

    private function gagal(string $pesan, int $kode = 422)
    {
        return response()->json(['status' => $kode, 'message' => $pesan], $kode);
    }
}
