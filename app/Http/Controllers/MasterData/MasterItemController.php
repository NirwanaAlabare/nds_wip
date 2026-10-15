<?php

namespace App\Http\Controllers\MasterData;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/**
 * Master Item General: barang non-produksi di masteritem (SB).
 * Patokan Signalbit pages/others/master.php (form & daftar), s_master.php (simpan),
 * server_masteritem.php (daftar) dan forms/non_akt.php (tidak aktif).
 * Bedanya: tidak ada hapus (cukup Active / Inactive), dan gambar baru disimpan di storage NDS
 * (storage/app/master-item) - nama filenya tetap di masteritem.file_gambar.
 */
class MasterItemController extends Controller
{
    use BantuanMasterData;

    // Item general = mattype N. Data lama mattype M ikut tampil & bisa diubah, sama dengan daftar Signalbit.
    private const MATTYPE = ['N', 'M'];
    private const MATTYPE_BARU = 'N';

    // Mapping persediaan ATK (1) & UMUM (2) wajib empat CoA; selain itu CoA diisi '-'
    private const KATEGORI_WAJIB_COA = ['1', '2'];

    // Isian CoA: label & awalan nomor akun yang boleh dipilih (sama dengan combo Signalbit)
    private const COA = [
        'coa_production'     => ['Production', ['5', '1']],
        'coa_sup_production' => ['Supporting Production', ['5', '1']],
        'coa_sup_selling'    => ['Supporting Selling', ['6', '1']],
        'coa_sup_gen_adm'    => ['Supporting General & Administration', ['7', '1']],
    ];

    // Panjang kolom masteritem - ditolak dengan pesan jelas, bukan dipotong diam-diam
    private const PANJANG = [
        'goods_code' => ['Item Code', 200],
        'itemdesc'   => ['Description', 500],
        'color'      => ['Color', 50],
        'size'       => ['Size', 50],
    ];

    // Mutation Type: Signalbit hanya menawarkan 'Mesin' (ditampilkan "Barang Modal")
    private const MUTASI = ['Mesin' => 'Barang Modal'];

    private const FOLDER_GAMBAR = 'master-item';
    private const EKSTENSI_GAMBAR = ['jpg', 'jpeg', 'png', 'webp'];

    // ===================================================================
    // Daftar
    // ===================================================================

    public function index()
    {
        return view('master-data.item.index', [
            'kategori'       => $this->kategori(),
            'tipe'           => $this->tipeItem(),
            'awal'           => $this->keadaanDaftar(),
            'page'           => 'dashboard-master-data',
            'subPageGroup'   => 'master-data',
            'subPage'        => 'master-item',
            'containerFluid' => true,
        ]);
    }

    // Server-side: item general belasan ribu baris
    public function data(Request $request)
    {
        $kolomUrut = [0 => 'i.goods_code', 1 => 'i.itemdesc', 2 => 'i.color', 3 => 'i.size', 4 => 'i.tipe_item', 5 => 'm.description', 6 => 'i.non_aktif'];

        $total = $this->dasar()->count();
        $q = $this->saring($this->dasar(), $request, $request->input('search.value'));
        $tersaring = (clone $q)->count();

        $urut = $request->input('order.0.column');
        if ($urut !== null && isset($kolomUrut[(int) $urut])) {
            $q->orderBy($kolomUrut[(int) $urut], $request->input('order.0.dir') === 'desc' ? 'desc' : 'asc');
        }
        // Tanpa urutan pilihan: item terbaru di atas, sama dengan Signalbit
        $q->orderByDesc('i.id_item');

        $panjang = (int) $request->input('length', 10);
        $q->skip(max(0, (int) $request->input('start')))->take($panjang > 0 ? min($panjang, 500) : 10);

        $rows = $q->get([
            'i.id_item as id',
            'i.goods_code as kode',
            'i.itemdesc as nama',
            'i.color',
            'i.size',
            'i.tipe_item as tipe',
            'm.description as kategori',
            'i.non_aktif',
            DB::raw("coalesce(i.file_gambar, '') <> '' as gambar"),
        ]);

        return response()->json([
            'draw'            => (int) $request->input('draw'),
            'recordsTotal'    => $total,
            'recordsFiltered' => $tersaring,
            'data'            => $rows,
        ]);
    }

    public function excel(Request $request)
    {
        // Semua item (±11 ribu baris) butuh ±200 MB & ±10 detik - jangan sampai kena batas bawaan server
        @ini_set('memory_limit', '512M');
        @set_time_limit(300);

        $rows = $this->saring($this->dasar(), $request, $request->input('cari'))
            ->leftJoin('mastercoa_v2 as c1', 'c1.no_coa', '=', 'i.coa_production')
            ->leftJoin('mastercoa_v2 as c2', 'c2.no_coa', '=', 'i.coa_sup_production')
            ->leftJoin('mastercoa_v2 as c3', 'c3.no_coa', '=', 'i.coa_sup_selling')
            ->leftJoin('mastercoa_v2 as c4', 'c4.no_coa', '=', 'i.coa_sup_gen_adm')
            ->orderByDesc('i.id_item')
            ->get([
                'i.id_item', 'i.goods_code', 'i.itemdesc', 'i.color', 'i.size', 'i.tipe_item', 'i.tipe_mut', 'm.description',
                'i.coa_production', 'c1.nama_coa as n1', 'i.coa_sup_production', 'c2.nama_coa as n2',
                'i.coa_sup_selling', 'c3.nama_coa as n3', 'i.coa_sup_gen_adm', 'c4.nama_coa as n4', 'i.non_aktif',
            ]);

        $coa = fn ($no, $nama) => in_array(trim((string) $no), ['', '-'], true) ? '-' : trim($no . ($nama ? ' - ' . $nama : ''));
        $kolom = ['No', 'ID', 'Item Code', 'Description', 'Color', 'Size', 'Item Type', 'Inventory Mapping', 'Mutation Type',
                  'COA Production', 'COA Supporting Production', 'COA Supporting Selling', 'COA Supporting General & Adm', 'Status'];
        $isi = [];
        foreach ($rows as $i => $r) {
            $isi[] = [
                $i + 1, (int) $r->id_item, (string) $r->goods_code, (string) $r->itemdesc, (string) $r->color, (string) $r->size,
                (string) $r->tipe_item, (string) $r->description, self::MUTASI[$r->tipe_mut] ?? (string) $r->tipe_mut,
                $coa($r->coa_production, $r->n1), $coa($r->coa_sup_production, $r->n2),
                $coa($r->coa_sup_selling, $r->n3), $coa($r->coa_sup_gen_adm, $r->n4),
                $r->non_aktif === 'Y' ? 'Inactive' : 'Active',
            ];
        }

        // Teks tetap teks (kode dengan nol di depan tidak berubah jadi angka); No & ID tetap angka
        $binderAwal = \PhpOffice\PhpSpreadsheet\Cell\Cell::getValueBinder();
        \PhpOffice\PhpSpreadsheet\Cell\Cell::setValueBinder((new \PhpOffice\PhpSpreadsheet\Cell\StringValueBinder())->setNumericConversion(false));

        $ss = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $s = $ss->getActiveSheet();
        $s->setTitle('Item General');
        $s->setCellValue('A1', 'Master Item General');
        $s->getStyle('A1')->getFont()->setBold(true)->setSize(13);
        $s->setCellValue('A2', 'Exported ' . now()->format('d M Y H:i') . ' - ' . count($isi) . ' rows');
        $s->getStyle('A2')->getFont()->setSize(9)->getColor()->setARGB('FF64748B');

        $bKepala = 4;
        $s->fromArray($kolom, null, 'A' . $bKepala);
        if ($isi) {
            $s->fromArray($isi, null, 'A' . ($bKepala + 1), true);
        }
        \PhpOffice\PhpSpreadsheet\Cell\Cell::setValueBinder($binderAwal);

        $akhirKol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(count($kolom));
        $akhirBaris = $bKepala + count($isi);
        $kepala = 'A' . $bKepala . ':' . $akhirKol . $bKepala;
        $s->getStyle($kepala)->getFont()->setBold(true);
        $s->getStyle($kepala)->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FFF1F5F9');
        $s->getStyle('A' . $bKepala . ':' . $akhirKol . $akhirBaris)->getBorders()->getAllBorders()
            ->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $s->getStyle('A' . $bKepala . ':' . $akhirKol . $akhirBaris)->getFont()->setSize(10);

        // Lebar kolom ditetapkan (autosize belasan ribu baris lambat)
        foreach ([6, 9, 16, 50, 14, 14, 16, 30, 14, 34, 34, 34, 34, 10] as $k => $lebar) {
            $s->getColumnDimensionByColumn($k + 1)->setWidth($lebar);
        }
        $s->freezePane('A' . ($bKepala + 1));

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($ss);

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, 'Master Item General ' . now()->format('Ymd') . '.xlsx', ['Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']);
    }

    private function dasar()
    {
        return DB::connection('mysql_sb')->table('masteritem as i')
            ->leftJoin('mapping_category as m', 'm.n_id', '=', 'i.n_code_category')
            ->whereIn('i.mattype', self::MATTYPE);
    }

    private function saring($q, Request $request, $cari)
    {
        if ($request->filled('kategori')) {
            $q->where('i.n_code_category', (int) $request->input('kategori'));
        }
        if ($request->filled('tipe')) {
            $q->where('i.tipe_item', (string) $request->input('tipe'));
        }
        // non_aktif 'Y' = tidak aktif, selain itu ('N' / kosong) dianggap aktif
        if ($request->input('status') === 'aktif') {
            $q->where(fn ($w) => $w->whereNull('i.non_aktif')->orWhere('i.non_aktif', '<>', 'Y'));
        } elseif ($request->input('status') === 'nonaktif') {
            $q->where('i.non_aktif', 'Y');
        }

        // Pencarian sama dengan Signalbit (kode, deskripsi, warna, ukuran, tipe) ditambah ID
        $cari = trim((string) $cari);
        if ($cari !== '') {
            $like = '%' . addcslashes($cari, '\\%_') . '%';
            $q->where(function ($w) use ($like, $cari) {
                $w->where('i.goods_code', 'like', $like)
                  ->orWhere('i.itemdesc', 'like', $like)
                  ->orWhere('i.color', 'like', $like)
                  ->orWhere('i.size', 'like', $like)
                  ->orWhere('i.tipe_item', 'like', $like);
                if (ctype_digit($cari)) {
                    $w->orWhere('i.id_item', (int) $cari);
                }
            });
        }

        return $q;
    }

    // Keadaan daftar yang dibawa ke Create / Edit dan dipakai lagi waktu kembali (lihat MasterDataController)
    private function keadaanDaftar()
    {
        $q = request()->query();
        $teks = fn ($kunci, $maks) => mb_substr(trim((string) (is_string($q[$kunci] ?? null) ? $q[$kunci] : '')), 0, $maks);

        return [
            'kategori' => ctype_digit($teks('kategori', 4)) ? $teks('kategori', 4) : '',
            'tipe'     => $teks('tipe', 20),
            'status'   => in_array($q['status'] ?? '', ['aktif', 'nonaktif'], true) ? $q['status'] : '',
            'cari'     => $teks('cari', 100),
            'hal'      => min(100000, max(0, (int) $teks('hal', 6))),
            'baris'    => in_array((int) $teks('baris', 3), [10, 25, 50, 100], true) ? (int) $teks('baris', 3) : 10,
            // Kosong = urutan bawaan (item terbaru di atas)
            'urut'     => ctype_digit($teks('urut', 1)) && (int) $teks('urut', 1) <= 6 ? (int) $teks('urut', 1) : null,
            'arah'     => $teks('arah', 4) === 'desc' ? 'desc' : 'asc',
        ];
    }

    private function urlDaftar()
    {
        $keadaan = array_filter($this->keadaanDaftar(), fn ($v, $k) => ! (
            $v === '' || $v === null || ($k === 'hal' && $v === 0) || ($k === 'baris' && $v === 10) || ($k === 'arah' && $v === 'asc')
        ), ARRAY_FILTER_USE_BOTH);

        return route('master-data-item', $keadaan);
    }

    // ===================================================================
    // Create / Edit
    // ===================================================================

    public function create()
    {
        return $this->formulir();
    }

    public function edit(Request $request)
    {
        $row = $this->cari((int) $request->query('id'));
        if (! $row) {
            abort(404);
        }

        return $this->formulir($row);
    }

    private function formulir($ubah = null)
    {
        // Semua akun sekali ambil, lalu dibagi per isian sesuai awalan nomornya
        $akun = DB::connection('mysql_sb')->table('mastercoa_v2')->orderBy('no_coa')->get(['no_coa', 'nama_coa']);
        $coa = [];
        foreach (self::COA as $kolom => [$label, $awalan]) {
            $coa[$kolom] = $akun->filter(fn ($a) => in_array(substr((string) $a->no_coa, 0, 1), $awalan, true))
                ->mapWithKeys(fn ($a) => [$a->no_coa => $a->no_coa . ' - ' . $a->nama_coa]);
        }

        return view('master-data.item.form', [
            'ubah'           => $ubah,
            'kategori'       => $this->kategori(),
            'tipe'           => $this->tipeItem(),
            'mutasi'         => self::MUTASI,
            'coa'            => $coa,
            'coaLabel'       => collect(self::COA)->map(fn ($c) => $c[0]),
            'wajibCoa'       => self::KATEGORI_WAJIB_COA,
            'panjang'        => collect(self::PANJANG)->map(fn ($p) => $p[1]),
            'gambarUrl'      => $ubah && $ubah->file_gambar ? $this->urlGambar($ubah) : null,
            'batasUpload'    => $this->batasUpload(),
            'ekstensi'       => self::EKSTENSI_GAMBAR,
            'urlKembali'     => $this->urlDaftar(),
            'page'           => 'dashboard-master-data',
            'subPageGroup'   => 'master-data',
            'subPage'        => 'master-item',
            'containerFluid' => true,
        ]);
    }

    public function simpan(Request $request)
    {
        return $this->tulis($request, null);
    }

    public function perbarui(Request $request)
    {
        $lama = $this->cari((int) $request->input('id'));
        if (! $lama) {
            return response()->json(['status' => 404, 'message' => 'Item not found'], 404);
        }

        return $this->tulis($request, $lama);
    }

    // Simpan item baru ($lama null) atau perubahan item lama - aturan isian dari s_master.php Signalbit
    private function tulis(Request $request, $lama)
    {
        $sb = DB::connection('mysql_sb');

        $v = [];
        foreach (['goods_code', 'itemdesc', 'color', 'size'] as $kolom) {
            $v[$kolom] = $this->nb($request->input($kolom));
        }
        $kategori = trim((string) $request->input('n_code_category'));
        $tipe = trim((string) $request->input('tipe_item'));
        $mutasi = trim((string) $request->input('tipe_mut'));

        $kurang = [];
        if ($v['goods_code'] === '') { $kurang[] = 'Item Code'; }
        if ($v['itemdesc'] === '') { $kurang[] = 'Description'; }
        if ($v['color'] === '') { $kurang[] = 'Color (use - if none)'; }
        if ($v['size'] === '') { $kurang[] = 'Size (use - if none)'; }
        if ($kategori === '' || ! $this->kategori()->has((int) $kategori)) { $kurang[] = 'Inventory Mapping'; }
        if ($tipe !== '' && ! $this->tipeItem()->contains($tipe)) { $kurang[] = 'Item Type'; }
        if ($mutasi !== '' && ! array_key_exists($mutasi, self::MUTASI)) { $kurang[] = 'Mutation Type'; }
        foreach (self::PANJANG as $kolom => [$label, $maks]) {
            if (mb_strlen($v[$kolom]) > $maks) {
                $kurang[] = $label . ' max ' . $maks . ' characters';
            }
        }

        // CoA wajib untuk ATK & UMUM (dan harus akun dengan awalan yang benar); selain itu '-'
        $coa = [];
        $wajibCoa = in_array($kategori, self::KATEGORI_WAJIB_COA, true);
        foreach (self::COA as $kolom => [$label, $awalan]) {
            $nilai = trim((string) $request->input($kolom));
            if (! $wajibCoa) {
                $coa[$kolom] = '-';
            } elseif ($nilai === '' || $nilai === '-') {
                $kurang[] = 'COA ' . $label;
            } elseif (! in_array(substr($nilai, 0, 1), $awalan, true) || ! $sb->table('mastercoa_v2')->where('no_coa', $nilai)->exists()) {
                $kurang[] = 'COA ' . $label . ' (invalid account)';
            } else {
                $coa[$kolom] = $nilai;
            }
        }

        // Ukuran gambar tidak dibatasi aplikasi - batasnya hanya setelan upload PHP di server.
        // file() (bukan hasFile()) supaya upload yang gagal karena batas server tetap ketahuan,
        // tidak diam-diam tersimpan tanpa gambar.
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

        // Kode dobel. Setelan Signalbit goods_code_only = Y: kode unik per mattype; selain itu
        // kombinasi kode + deskripsi + warna + ukuran. Data lama yang sudah dobel tetap bisa diedit
        // selama isian kuncinya tidak diubah.
        $mattype = $lama ? $lama->mattype : self::MATTYPE_BARU;
        $kodeSaja = DB::connection('mysql_sb')->table('mastercompany')->where('company', '<>', '')->value('goods_code_only') === 'Y';
        $kunci = $kodeSaja ? ['goods_code'] : ['goods_code', 'itemdesc', 'color', 'size'];
        $kunciBerubah = ! $lama || collect($kunci)->contains(fn ($k) => $this->nb($lama->$k) !== $v[$k]);
        if ($kunciBerubah) {
            $dobel = $sb->table('masteritem')->where('mattype', $mattype)
                ->where(fn ($w) => collect($kunci)->each(fn ($k) => $w->where($k, $v[$k])))
                ->when($lama, fn ($w) => $w->where('id_item', '<>', $lama->id_item))
                ->first(['id_item', 'goods_code']);
            if ($dobel) {
                return response()->json([
                    'status'  => 409,
                    'message' => 'Item code ' . $v['goods_code'] . ' already exists (ID ' . $dobel->id_item . ').',
                ], 409);
            }
        }

        // Kolom & urutannya mengikuti insert Signalbit
        $data = [
            'n_code_category'    => (int) $kategori,
            'tipe_item'          => $tipe,
            'tipe_mut'           => $mutasi,
            'goods_code'         => $v['goods_code'],
            'itemdesc'           => $v['itemdesc'],
            'color'              => $v['color'],
            'size'               => $v['size'],
            'coa_production'     => $coa['coa_production'],
            'coa_sup_production' => $coa['coa_sup_production'],
            'coa_sup_gen_adm'    => $coa['coa_sup_gen_adm'],
            'coa_sup_selling'    => $coa['coa_sup_selling'],
        ];

        $fileBaru = null;
        $hapusLama = null;
        $sb->beginTransaction();
        try {
            if ($lama) {
                $id = (int) $lama->id_item;
                // Gambar: diganti file baru, dihapus, atau tetap (Signalbit mengosongkannya kalau tidak upload ulang)
                if ($file) {
                    $fileBaru = $this->simpanGambar($file, $id);
                    $data['file_gambar'] = $fileBaru;
                } elseif ($request->boolean('hapus_gambar')) {
                    $data['file_gambar'] = '';
                }
                if (array_key_exists('file_gambar', $data) && $lama->file_gambar) {
                    $hapusLama = $lama->file_gambar;
                }

                $berubah = array_filter($data, fn ($nilai, $kolom) => (string) $nilai !== (string) $lama->$kolom, ARRAY_FILTER_USE_BOTH);
                if ($berubah) {
                    $sb->table('masteritem')->where('id_item', $id)->update($berubah);
                    $this->catatLog($this->sqlUpdate('masteritem', $berubah, 'id_item', $id));
                }
            } else {
                $data = ['n_code_category' => $data['n_code_category'], 'matclass' => '-'] + $data
                    + ['mattype' => self::MATTYPE_BARU, 'file_gambar' => ''];
                $id = $sb->table('masteritem')->insertGetId($data, 'id_item');
                if ($file) {
                    $fileBaru = $this->simpanGambar($file, $id);
                    $data['file_gambar'] = $fileBaru;
                    $sb->table('masteritem')->where('id_item', $id)->update(['file_gambar' => $fileBaru]);
                }
                $this->catatLog($this->sqlInsert('masteritem', $data));
                $berubah = $data;
            }

            $sb->commit();
        } catch (\Throwable $e) {
            $sb->rollBack();
            if ($fileBaru) {
                Storage::disk('local')->delete(self::FOLDER_GAMBAR . '/' . $fileBaru);
            }
            throw $e;
        }

        // File lama dibuang sesudah tersimpan - hanya yang memang ada di storage NDS
        if ($hapusLama && $hapusLama !== ($data['file_gambar'] ?? null)) {
            Storage::disk('local')->delete(self::FOLDER_GAMBAR . '/' . $hapusLama);
        }

        return response()->json([
            'status'  => 200,
            'id'      => $id,
            'kode'    => $v['goods_code'],
            'nama'    => $v['itemdesc'],
            'berubah' => count($berubah),
        ]);
    }

    // Batas upload yang berlaku di server (yang terkecil dari upload_max_filesize & post_max_size),
    // hanya untuk pesan kalau gambarnya ternyata lebih besar
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

    // Nama file unik per item supaya tidak menimpa file lain: ITEM-<id>-<waktu>.<ext>
    private function simpanGambar($file, $id)
    {
        $nama = 'ITEM-' . $id . '-' . now()->format('YmdHis') . '.' . strtolower($file->getClientOriginalExtension());
        Storage::disk('local')->putFileAs(self::FOLDER_GAMBAR, $file, $nama);

        return $nama;
    }

    // ===================================================================
    // Show, status, gambar
    // ===================================================================

    public function detail(Request $request)
    {
        $row = $this->cari((int) $request->query('id'));
        if (! $row) {
            return response()->json(['message' => 'Item not found'], 404);
        }

        $sb = DB::connection('mysql_sb');
        $nomor = collect(self::COA)->keys()->map(fn ($k) => $row->$k)->filter(fn ($n) => ! in_array(trim((string) $n), ['', '-'], true))->all();
        $namaAkun = $nomor ? $sb->table('mastercoa_v2')->whereIn('no_coa', $nomor)->pluck('nama_coa', 'no_coa') : collect();

        $coa = [];
        foreach (self::COA as $kolom => [$label]) {
            $no = trim((string) $row->$kolom);
            $coa[] = ['label' => $label, 'no' => in_array($no, ['', '-'], true) ? null : $no, 'nama' => $namaAkun[$no] ?? null];
        }

        return response()->json([
            'data'           => $row,
            'kategori_label' => $this->kategori()->get($row->n_code_category),
            'mutasi_label'   => self::MUTASI[$row->tipe_mut] ?? ($row->tipe_mut ?: null),
            'coa'            => $coa,
            'wajib_coa'      => in_array((string) $row->n_code_category, self::KATEGORI_WAJIB_COA, true),
            'gambar_url'     => $row->file_gambar ? $this->urlGambar($row) : null,
        ]);
    }

    // Active / Inactive. Signalbit hanya punya Non Aktif (forms/non_akt.php, non_aktif = 'Y'); di sini dua arah.
    public function status(Request $request)
    {
        $row = $this->cari((int) $request->input('id'));
        if (! $row) {
            return response()->json(['message' => 'Item not found'], 404);
        }

        $aktif = $request->boolean('aktif');
        $nilai = $aktif ? 'N' : 'Y';
        DB::connection('mysql_sb')->transaction(function () use ($row, $nilai) {
            DB::connection('mysql_sb')->table('masteritem')->where('id_item', $row->id_item)->update(['non_aktif' => $nilai]);
            $this->catatLog("update masteritem set non_aktif='" . $nilai . "' where id_item='" . $row->id_item . "'");
        });

        return response()->json(['status' => 200, 'aktif' => $aktif, 'kode' => $row->goods_code, 'nama' => $row->itemdesc]);
    }

    // Gambar item: dari storage NDS; gambar lama (upload sebelum pindah) dari alamat yang disetel di config
    public function gambar(Request $request)
    {
        $row = $this->cari((int) $request->query('id'));
        $nama = $row ? (string) $row->file_gambar : '';
        if ($nama === '') {
            abort(404);
        }

        $path = self::FOLDER_GAMBAR . '/' . $nama;
        if (Storage::disk('local')->exists($path)) {
            return response()->file(Storage::disk('local')->path($path), ['Cache-Control' => 'private, max-age=86400']);
        }

        $lama = config('masterdata.item_gambar_lama_url');
        if ($lama) {
            return redirect()->away(rtrim($lama, '/') . '/' . rawurlencode($nama));
        }

        abort(404);
    }

    private function urlGambar($row)
    {
        $ada = Storage::disk('local')->exists(self::FOLDER_GAMBAR . '/' . $row->file_gambar) || config('masterdata.item_gambar_lama_url');

        // v = nama file, supaya cache browser berganti waktu gambarnya diganti
        return $ada ? route('master-data-item-gambar', ['id' => $row->id_item, 'v' => substr(md5($row->file_gambar), 0, 8)]) : null;
    }

    // ===================================================================

    private function cari($id)
    {
        return DB::connection('mysql_sb')->table('masteritem')->where('id_item', $id)->whereIn('mattype', self::MATTYPE)->first();
    }

    private function kategori()
    {
        static $kategori;
        return $kategori ??= DB::connection('mysql_sb')->table('mapping_category')->orderBy('n_id')->pluck('description', 'n_id');
    }

    private function tipeItem()
    {
        static $tipe;
        return $tipe ??= DB::connection('mysql_sb')->table('masterpilihan')->where('kode_pilihan', 'J_Item')->orderBy('nama_pilihan')->pluck('nama_pilihan');
    }
}
