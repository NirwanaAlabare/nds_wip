<?php

namespace App\Services;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Riwayat master customer / supplier (mastersupplier di SB): dibuat, diubah, aktif / tidak aktif.
 *
 * Sumbernya dua:
 * - Simpan dari NDS dicatat langsung (catat()), lengkap dengan nilai lama & barunya.
 * - Simpan dari luar NDS hanya ada di act_hist, berupa teks query. act_hist terlalu besar untuk
 *   dicari per partner (jutaan baris, tanpa index di teksnya), jadi lognya disalin bertahap ke
 *   tabel ini (sinkron()) mulai dari id act_hist terakhir yang sudah dibaca. Penyalinan pertama
 *   (semua log lama) lewat: php artisan masterdata:sync-history
 *
 * Tabelnya dibuat dari database/sql/2026_10_14_mastersupplier_log.sql. Selama tabelnya belum ada,
 * semua fungsi di sini diam saja - simpan customer / supplier tetap jalan.
 */
class MasterDataHistoryService
{
    const KONEKSI = 'mysql_sb';
    const TABEL = 'mastersupplier_log';
    const KUNCI_SINKRON = 'mastersupplier_log_sinkron';

    // Kolom yang tampil di riwayat beserta labelnya. CoA, kolom bank lama, status_kb & tipe_sup sengaja tidak.
    const LABEL = [
        'supplier_code'     => 'Code',
        'Supplier'          => 'Name',
        'legal_name'        => 'Legal Name',
        'short_name'        => 'Short Name',
        'group_name'        => 'Group Name',
        'area'              => 'Area',
        'vendor_cat'        => 'Category',
        'jenis_fasilitas'   => 'Facility',
        'npwp'              => 'NPWP',
        'pkp'               => 'PKP Status',
        'nib'               => 'NIB',
        'payment_rating'    => 'Payment Flexibility Rating',
        'payment_review_date' => 'Payment Flexibility Last Review Date',
        'id_terms'          => 'Term of Payment',
        'tipe_buyer'        => 'Buyer',
        'tipe_agent'        => 'Agent',
        'to_account_manual' => 'To Account Manual',
        'alamat'            => 'Address',
        'alamat2'           => 'Address 2',
        'zip_code'          => 'Zip Code',
        'country'           => 'Country',
        'product_name'      => 'Product Name',
        'moq'               => 'MOQ',
        'lead_time'         => 'Lead Time',
        'moq_lead_time'     => 'MOQ Lead Time',
        'Attn'              => 'Contact Name 1',
        'Attn2'             => 'Contact Name 2',
        'Attn3'             => 'Contact Name 3',
        'Attn4'             => 'Contact Name 4',
        'Phone'             => 'Phone',
        'Fax'               => 'Fax',
        'Email'             => 'E-Mail',
    ];

    // Ringkasan yang ditampilkan di kartu "Created"
    const RINGKAS_BUAT = ['supplier_code', 'Supplier', 'area', 'country'];

    const AREA = ['L' => 'Local', 'I' => 'Import/Export', 'F' => 'Factory'];

    // Supplier: Area disebut Type (Import/Export = Overseas), Facility disebut Customs Classification
    const LABEL_SUPPLIER = ['area' => 'Type', 'jenis_fasilitas' => 'Customs Classification'];
    const AREA_SUPPLIER = ['L' => 'Local', 'I' => 'Overseas', 'F' => 'Factory'];

    // Label tampilan yang berbeda dari nilai tersimpannya (sama dengan MasterDataController)
    const LABEL_NILAI = [
        'pkp'             => ['NON PKP' => 'Non-PKP'],
        'jenis_fasilitas' => ['KITE PEMBEBASAN' => 'KITE Pembebasan', 'KITE PENGEMBALIAN' => 'KITE Pengembalian', 'OVERSEAS' => 'Overseas'],
        'payment_rating'  => ['RED' => 'Red', 'YELLOW' => 'Yellow', 'GREEN' => 'Green'],
    ];

    private static $siap;

    // Riwayat yang sedang disusun milik supplier (label Type / Customs Classification)
    private $supplier = false;

    public function siap()
    {
        if (self::$siap === null) {
            try {
                self::$siap = count($this->db()->select(
                    'select 1 from information_schema.tables where table_schema = database() and table_name = ? limit 1',
                    [self::TABEL]
                )) > 0;
            } catch (\Throwable $e) {
                self::$siap = false;
            }
        }

        return self::$siap;
    }

    // ===================================================================
    // Catat dari NDS - dipanggil di dalam transaksi simpan.
    // Gagal mencatat riwayat tidak boleh menggagalkan simpannya: cukup ditulis ke log.
    // ===================================================================

    public function catatBuat($idSupplier, array $data, $actHistId)
    {
        $this->tulis($idSupplier, 'CREATE', $actHistId, array_intersect_key($data, self::LABEL), null);
    }

    public function catatUbah($idSupplier, $lama, array $berubah, $actHistId)
    {
        $perubahan = [];
        foreach (array_intersect_key($berubah, self::LABEL) as $kolom => $baru) {
            $perubahan[$kolom] = [$lama->$kolom, $baru];
        }
        $this->tulis($idSupplier, 'UPDATE', $actHistId, null, $perubahan);
    }

    // $status ACTIVE / INACTIVE / BLOCKED; alasan Blocked disimpan di isi
    public function catatStatus($idSupplier, $status, $actHistId, $alasan = null)
    {
        $this->tulis($idSupplier, $status, $actHistId, $status === 'BLOCKED' ? ['blocked_reason' => $alasan] : null, null);
    }

    private function tulis($idSupplier, $aksi, $actHistId, $isi, $perubahan)
    {
        if (! $this->siap()) {
            return;
        }
        try {
            $user = Auth::user();
            $this->db()->table(self::TABEL)->insert([
                'id_supplier' => (int) $idSupplier,
                'aksi'        => $aksi,
                'isi'         => $isi === null ? null : json_encode($isi),
                'perubahan'   => $perubahan === null ? null : json_encode($perubahan),
                'act_hist_id' => $actHistId ?: null,
                'created_by'  => $user ? ($user->username ?? $user->name) : null,
                'created_at'  => now()->format('Y-m-d H:i:s'),
            ]);
        } catch (\Throwable $e) {
            Log::warning('Riwayat master data tidak tercatat (id ' . $idSupplier . ') - ' . $e->getMessage());
        }
    }

    // ===================================================================
    // Sinkron dari act_hist
    // ===================================================================

    /**
     * Salin log mastersupplier di act_hist yang belum dibaca. Hanya satu proses sinkron sekaligus;
     * yang datang belakangan langsung lewat (riwayat yang sudah ada tetap ditampilkan).
     *
     * $dariAwal = true (artisan): baca semua act_hist dari id 0 - aman diulang, catatan yang sudah
     * ada dilewati lewat unique act_hist_id. Dari web (false): kalau belum pernah disalin sama
     * sekali, penanda dipasang di id act_hist terakhir tanpa menyalin apa pun, supaya halaman tidak
     * menunggu pembacaan seluruh act_hist.
     *
     * @return int jumlah catatan baru
     */
    public function sinkron($dariAwal = false)
    {
        if (! $this->siap()) {
            return 0;
        }

        $sb = $this->db();
        $kunci = $sb->selectOne('select get_lock(?, ?) as k', [self::KUNCI_SINKRON, $dariAwal ? 60 : 0]);
        if (! $kunci || ! $kunci->k) {
            return 0;
        }

        try {
            $penanda = $sb->table(self::TABEL)->where('id_supplier', 0)->where('aksi', 'SYNC')->first();
            $sampai = (int) $sb->table('act_hist')->max('id');

            if (! $penanda && ! $dariAwal) {
                $this->simpanPenanda(null, $sampai);
                return 0;
            }

            $dari = $dariAwal ? 0 : (int) (json_decode((string) $penanda->isi, true)['act_hist_id'] ?? 0);
            if ($sampai <= $dari) {
                return 0;
            }

            // Log Signalbit & NDS selalu diawali persis "insert into mastersupplier (" / "update mastersupplier set"
            $log = $sb->table('act_hist')
                ->select('id', 'Trans_Date', 'Trans_Host', 'Trans_Desc')
                ->where('id', '>', $dari)
                ->where('id', '<=', $sampai)
                ->where(function ($q) {
                    $q->where('Trans_Desc', 'like', 'insert into mastersupplier %')
                      ->orWhere('Trans_Desc', 'like', 'update mastersupplier %');
                })
                ->orderBy('id')
                ->get();

            // Yang sudah tercatat (simpan dari NDS, atau sinkron sebelumnya) dilewati oleh unique act_hist_id
            $jumlah = 0;
            foreach (array_chunk($this->dariLog($log), 500) as $potong) {
                $jumlah += $sb->table(self::TABEL)->insertOrIgnore($potong);
            }

            $this->simpanPenanda($penanda, $sampai);

            return $jumlah;
        } finally {
            $sb->select('select release_lock(?)', [self::KUNCI_SINKRON]);
        }
    }

    private function simpanPenanda($penanda, $sampai)
    {
        $isi = json_encode(['act_hist_id' => $sampai]);
        if ($penanda) {
            $this->db()->table(self::TABEL)->where('id', $penanda->id)->update(['isi' => $isi, 'created_at' => now()->format('Y-m-d H:i:s')]);
        } else {
            $this->db()->table(self::TABEL)->insert([
                'id_supplier' => 0,
                'aksi'        => 'SYNC',
                'isi'         => $isi,
                'created_by'  => 'system',
                'created_at'  => now()->format('Y-m-d H:i:s'),
            ]);
        }
    }

    // Baris act_hist -> baris riwayat
    private function dariLog($log)
    {
        $sb = $this->db();

        $urai = [];
        foreach ($log as $l) {
            $u = $this->uraiSql($l->Trans_Desc);
            if ($u) {
                $u['log'] = $l;
                $urai[] = $u;
            }
        }

        // Insert tidak membawa id-nya, jadi dicari lewat nama + tipe (nama unik per tipe).
        // Nama lama dari log update ikut dipetakan supaya partner yang sudah diganti namanya tetap ketemu.
        $petaNama = [];
        if (in_array('insert', array_column($urai, 'jenis'), true)) {
            $tipeId = [];
            foreach ($sb->table('mastersupplier')->select('Id_Supplier', 'Supplier', 'tipe_sup')->get() as $m) {
                $petaNama[$m->tipe_sup . '|' . $this->kunciNama($m->Supplier)] = (int) $m->Id_Supplier;
                $tipeId[(int) $m->Id_Supplier] = $m->tipe_sup;
            }
            foreach ($urai as $u) {
                if ($u['jenis'] === 'update' && isset($u['isi']['Supplier'], $tipeId[$u['id']])) {
                    $petaNama += [$tipeId[$u['id']] . '|' . $this->kunciNama($u['isi']['Supplier']) => $u['id']];
                }
            }
        }

        $baris = [];
        $buatTerakhir = [];   // id_supplier => index di $baris (yang dipakai create paling akhir)
        foreach ($urai as $u) {
            $l = $u['log'];
            $isi = $u['isi'];

            if ($u['jenis'] === 'insert') {
                $id = $petaNama[($isi['tipe_sup'] ?? '') . '|' . $this->kunciNama($isi['Supplier'] ?? '')] ?? null;
                if (! $id) {
                    continue;
                }
                // Insert lama menaruh id term di terms_of_pay
                if (! array_key_exists('id_terms', $isi) && array_key_exists('terms_of_pay', $isi)) {
                    $isi['id_terms'] = $isi['terms_of_pay'];
                }
                $aksi = 'CREATE';
            } else {
                $id = $u['id'];
                if (array_keys($isi) === ['non_aktif']) {
                    $aksi = trim($isi['non_aktif']) === '1' ? 'INACTIVE' : 'ACTIVE';
                    $isi = null;
                } else {
                    $aksi = 'UPDATE';
                }
            }

            if ($isi !== null) {
                $isi = array_intersect_key($isi, self::LABEL);
                if (! $isi) {
                    continue;   // mis. cuma mengubah CoA
                }
            }

            $baris[] = [
                'id_supplier' => $id,
                'aksi'        => $aksi,
                'isi'         => $isi === null ? null : json_encode($isi),
                'perubahan'   => null,
                'act_hist_id' => (int) $l->id,
                'created_by'  => substr(trim(explode('/', (string) $l->Trans_Host)[0]), 0, 100) ?: null,
                'created_at'  => $l->Trans_Date,
            ];
            if ($aksi === 'CREATE') {
                if (isset($buatTerakhir[$id])) {
                    $baris[$buatTerakhir[$id]] = null;   // nama yang sama pernah dibuat-hapus: pakai yang terakhir
                }
                $buatTerakhir[$id] = count($baris) - 1;
            }
        }
        $baris = array_values(array_filter($baris));

        // Partner yang create-nya sudah tercatat (dari NDS atau sinkron sebelumnya) tidak diberi create kedua
        $sudahBuat = $buatTerakhir
            ? $sb->table(self::TABEL)->where('aksi', 'CREATE')->whereIn('id_supplier', array_keys($buatTerakhir))->pluck('act_hist_id', 'id_supplier')->all()
            : [];

        return array_values(array_filter($baris, function ($b) use ($sudahBuat) {
            return $b['aksi'] !== 'CREATE' || ! array_key_exists($b['id_supplier'], $sudahBuat)
                || (int) $sudahBuat[$b['id_supplier']] === $b['act_hist_id'];
        }));
    }

    /**
     * Teks query di act_hist (tanda kutip sudah jadi '!') -> jenis, kolom => nilai, id.
     * Bentuknya dari save_data.php / non_akt.php Signalbit dan catatLog() NDS.
     */
    private function uraiSql($sql)
    {
        if (preg_match('/^\s*insert\s+into\s+mastersupplier\s*\((.*?)\)\s*values\s*\((.*)\)\s*;?\s*$/is', $sql, $m)) {
            $kolom = array_map('trim', explode(',', $m[1]));
            preg_match_all('/!([^!]*)!/', $m[2], $n);
            if (count($kolom) !== count($n[1])) {
                return null;
            }
            return ['jenis' => 'insert', 'isi' => $this->namaKolom(array_combine($kolom, $n[1])), 'id' => null];
        }

        if (preg_match('/^\s*update\s+mastersupplier\s+set\s+(.*)\s+where\s+id_supplier\s*=\s*!?(\d+)!?\s*;?\s*$/is', $sql, $m)) {
            preg_match_all('/(\w+)\s*=\s*!([^!]*)!/', $m[1], $p, PREG_SET_ORDER);
            $isi = [];
            foreach ($p as $x) {
                $isi[$x[1]] = $x[2];
            }
            return $isi ? ['jenis' => 'update', 'isi' => $this->namaKolom($isi), 'id' => (int) $m[2]] : null;
        }

        return null;
    }

    // Nama kolom di log tidak selalu sama besar-kecil hurufnya - disamakan dengan nama kolom aslinya
    private function namaKolom(array $isi)
    {
        static $kanon;
        if ($kanon === null) {
            foreach (array_merge(array_keys(self::LABEL), ['tipe_sup', 'non_aktif', 'terms_of_pay']) as $k) {
                $kanon[strtolower($k)] = $k;
            }
        }

        $hasil = [];
        foreach ($isi as $k => $v) {
            $k = $kanon[strtolower(trim($k))] ?? null;
            if ($k !== null) {
                $hasil[$k] = trim($v);
            }
        }
        return $hasil;
    }

    private function kunciNama($nama)
    {
        return strtoupper(trim(preg_replace('/\s+/', ' ', (string) $nama)));
    }

    // ===================================================================
    // Tampilan riwayat satu partner
    // ===================================================================

    /**
     * Daftar kejadian, terbaru di atas. Edit dari luar NDS hanya menyimpan isi lengkapnya, jadi
     * perubahannya dihitung dari keadaan sebelumnya (create / edit sebelumnya) di sini.
     *
     * @return array ['baris' => [...], 'awal' => bool ada catatan create]
     */
    public function riwayat($idSupplier, $labelNama)
    {
        $this->supplier = $labelNama === 'Supplier';
        $rows = $this->db()->table(self::TABEL)
            ->where('id_supplier', (int) $idSupplier)
            ->whereIn('aksi', ['CREATE', 'UPDATE', 'ACTIVE', 'INACTIVE', 'BLOCKED'])
            ->orderBy('created_at')
            ->orderBy('id')
            ->get();

        $keadaan = null;
        $out = [];
        $adaBuat = false;

        foreach ($rows as $r) {
            $isi = json_decode((string) $r->isi, true) ?: [];
            $item = [
                'aksi'      => strtolower($r->aksi),
                'oleh'      => $r->created_by ?: null,
                'waktu'     => $r->created_at ? date('d M Y H:i', strtotime($r->created_at)) : '',
                'perubahan' => [],
                'ringkas'   => [],
                'catatan'   => null,
            ];

            if ($r->aksi === 'CREATE') {
                $adaBuat = true;
                $item['judul'] = 'Created';
                $keadaan = $this->normalSemua($isi);
                foreach (self::RINGKAS_BUAT as $kolom) {
                    $nilai = $this->tampil($kolom, $keadaan[$kolom] ?? '');
                    if ($nilai !== null) {
                        $item['ringkas'][] = ['label' => $this->label($kolom, $labelNama), 'nilai' => $nilai];
                    }
                }
            } elseif ($r->aksi === 'UPDATE') {
                $item['judul'] = 'Edited';
                if ($r->perubahan !== null) {
                    // Dari NDS: nilai lama & baru tercatat apa adanya
                    foreach (json_decode((string) $r->perubahan, true) ?: [] as $kolom => $lb) {
                        $this->tambahBeda($item, $kolom, $lb[0], $lb[1], $labelNama);
                        $keadaan[$kolom] = $this->normal($kolom, $lb[1]);
                    }
                } elseif ($keadaan === null) {
                    $item['catatan'] = 'Earlier values were not recorded, so the changes of this save cannot be shown.';
                    $keadaan = $this->normalSemua($isi);
                } else {
                    foreach ($this->normalSemua($isi) as $kolom => $nilai) {
                        if (array_key_exists($kolom, $keadaan) && $keadaan[$kolom] !== $nilai) {
                            $this->tambahBeda($item, $kolom, $keadaan[$kolom], $nilai, $labelNama);
                        }
                        $keadaan[$kolom] = $nilai;
                    }
                }
                if (! $item['perubahan'] && ! $item['catatan']) {
                    $item['catatan'] = 'Saved without changes.';
                }
            } elseif ($r->aksi === 'BLOCKED') {
                $item['judul'] = 'Set Blocked';
                $item['catatan'] = 'Reason: ' . ($isi['blocked_reason'] ?? '-');
            } else {
                $item['judul'] = $r->aksi === 'ACTIVE' ? 'Set Active' : 'Set Inactive';
            }

            $out[] = $item;
        }

        return ['baris' => array_reverse($out), 'awal' => $adaBuat];
    }

    private function tambahBeda(array &$item, $kolom, $lama, $baru, $labelNama)
    {
        $lama = $this->tampil($kolom, $this->normal($kolom, $lama));
        $baru = $this->tampil($kolom, $this->normal($kolom, $baru));
        if ($lama !== $baru) {
            $item['perubahan'][] = ['label' => $this->label($kolom, $labelNama), 'lama' => $lama, 'baru' => $baru];
        }
    }

    private function label($kolom, $labelNama)
    {
        if ($kolom === 'Supplier') {
            return $labelNama . ' Name';
        }
        return ($this->supplier ? self::LABEL_SUPPLIER[$kolom] ?? null : null) ?? self::LABEL[$kolom];
    }

    private function normalSemua(array $isi)
    {
        $hasil = [];
        foreach (array_intersect_key($isi, self::LABEL) as $kolom => $nilai) {
            $hasil[$kolom] = $this->normal($kolom, $nilai);
        }
        return $hasil;
    }

    // Bentuk pembanding: kosong / NULL / '0' yang artinya sama dianggap sama
    private function normal($kolom, $nilai)
    {
        $nilai = trim((string) $nilai);
        switch ($kolom) {
            case 'moq':
            case 'lead_time':
            case 'moq_lead_time':
                return is_numeric($nilai) ? (string) (float) $nilai : '0';
            case 'id_terms':
                return (string) (int) $nilai;
            case 'tipe_buyer':
            case 'tipe_agent':
                return strtoupper($nilai) === 'Y' ? 'Y' : '';
            case 'to_account_manual':
                return $nilai === '1' ? '1' : '0';
            case 'area':
                return strtoupper(substr($nilai, 0, 1));
            case 'payment_rating':
                return strtoupper($nilai);
            default:
                return $nilai;
        }
    }

    // Nilai siap baca; null = kosong
    private function tampil($kolom, $nilai)
    {
        switch ($kolom) {
            case 'tipe_buyer':
            case 'tipe_agent':
                return $nilai === 'Y' ? 'Yes' : 'No';
            case 'to_account_manual':
                return $nilai === '1' ? 'Yes' : 'No';
            case 'id_terms':
                return $nilai === '0' ? null : ($this->terms()[$nilai] ?? $nilai);
            case 'area':
                return $nilai === '' ? null : (($this->supplier ? self::AREA_SUPPLIER : self::AREA)[$nilai] ?? $nilai);
            case 'vendor_cat':
                return $nilai === '' ? null : ($this->kategori()[$nilai] ?? $nilai);
            case 'payment_review_date':
                return $nilai === '' ? null : date('d M Y', strtotime($nilai));
            default:
                return $nilai === '' ? null : (self::LABEL_NILAI[$kolom][$nilai] ?? $nilai);
        }
    }

    private function terms()
    {
        static $terms;
        if ($terms === null) {
            $terms = [];
            foreach ($this->db()->table('masterpterms')->select('id', 'kode_pterms', 'nama_pterms')->get() as $t) {
                $terms[(string) $t->id] = $t->kode_pterms . ($t->nama_pterms && $t->nama_pterms !== $t->kode_pterms ? ' | ' . $t->nama_pterms : '');
            }
        }
        return $terms;
    }

    private function kategori()
    {
        static $kategori;
        if ($kategori === null) {
            $kategori = $this->db()->table('masterpilihan')->where('kode_pilihan', 'Vendor_Cat')->pluck('nama_pilihan')
                ->mapWithKeys(fn ($p) => [substr($p, 0, 2) => $p])->all();
        }
        return $kategori;
    }

    private function db()
    {
        return DB::connection(self::KONEKSI);
    }
}
