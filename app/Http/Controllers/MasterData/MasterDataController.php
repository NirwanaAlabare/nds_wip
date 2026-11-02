<?php

namespace App\Http\Controllers\MasterData;

use App\Http\Controllers\Controller;
use App\Services\MasterDataHistoryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Master Data: daftar & create customer / supplier di mastersupplier (SB).
 * Customer = tipe_sup 'C', supplier = tipe_sup 'S'.
 * Create mengikuti Signalbit (pages/forms/master_supcust.php + save_data.php mode Supplier/Customer)
 * supaya data dari NDS sama persis dengan yang dibuat dari Signalbit.
 */
class MasterDataController extends Controller
{
    use BantuanMasterData;

    // Pengaturan per jenis daftar - dipakai halaman, data tabel & Excel
    private const JENIS = [
        'customer' => [
            'tipe_sup' => 'C',
            'judul'    => 'Master Customer',
            'sub'      => 'List of master customer (buyer) data',
            'ikon'     => 'fa-user-tie',
            'kolom'    => 'Customer',
        ],
        'supplier' => [
            'tipe_sup' => 'S',
            'judul'    => 'Master Supplier',
            'sub'      => 'List of master supplier data',
            'ikon'     => 'fa-truck-field',
            'kolom'    => 'Supplier',
        ],
    ];

    // Label kode area mastersupplier (pilihan Area di Signalbit: Lokal, Import/Export, Factory) - ditampilkan bahasa Inggris
    private const AREA = [
        'L' => 'Local',
        'I' => 'Import/Export',
        'F' => 'Factory',
    ];

    // Type supplier hanya Local / Overseas. Overseas disimpan 'I' (Import/Export di Signalbit) supaya
    // combo Area Signalbit tetap mengenali nilainya. Factory (F) hanya ada di data lama.
    private const TIPE_SUPPLIER = [
        'L' => 'Local',
        'I' => 'Overseas',
    ];

    // Payment Flexibility Rating supplier (kolom payment_rating, dulu Priority): merah / kuning / hijau.
    // Kosong = belum diisi.
    private const RATING = [
        'RED'    => 'Red',
        'YELLOW' => 'Yellow',
        'GREEN'  => 'Green',
    ];

    // Supplier Status. non_aktif tetap diisi untuk Signalbit (0 = Active, 1 = Inactive / Blocked),
    // status_aktif yang membedakan Inactive dengan Blocked.
    private const STATUS = [
        'ACTIVE'   => 'Active',
        'INACTIVE' => 'Inactive',
        'BLOCKED'  => 'Blocked',
    ];

    // PKP Status - nilai tersimpan sama dengan pilihan PKP Signalbit (masterpilihan), labelnya di sini
    private const PKP = [
        'PKP'     => 'PKP',
        'NON PKP' => 'Non-PKP',
        'NA'      => 'NA',
    ];

    // Customs Classification (jenis_fasilitas), urutan sesuai ketentuan. Disimpan huruf besar seperti
    // pilihan J_FAS Signalbit (combo Signalbit mencocokkan nilainya persis).
    private const FASILITAS = [
        'TLDDP'             => 'TLDDP',
        'KB'                => 'KB',
        'GB'                => 'GB',
        'PLB'               => 'PLB',
        'KITE PEMBEBASAN'   => 'KITE Pembebasan',
        'KITE PENGEMBALIAN' => 'KITE Pengembalian',
        'KEK'               => 'KEK',
        'KPBPB'             => 'KPBPB',
        'OVERSEAS'          => 'Overseas',
    ];

    // Kolom dari database/sql/2026_10_30_mastersupplier_kolom_baru.sql
    private const KOLOM_BARU = [
        'legal_name', 'nib', 'status_aktif', 'blocked_reason', 'payment_rating', 'payment_review_date',
        'verified_by', 'verified_date', 'approved_by', 'approved_date', 'modified_by', 'modified_date',
    ];

    // Panjang kolom mastersupplier - Signalbit memotong diam-diam, di sini ditolak dengan pesan jelas
    private const PANJANG = [
        'supplier_code'    => ['Code', 10],
        'Supplier'         => ['Name', 80],
        'legal_name'       => ['Legal Name', 150],
        'short_name'       => ['Short Name', 10],
        'group_name'       => ['Group Name', 20],
        'alamat'           => ['Address', 200],
        'alamat2'          => ['Address 2', 100],
        'zip_code'         => ['Zip Code', 10],
        'country'          => ['Country', 25],
        'npwp'             => ['NPWP', 100],
        'Attn'             => ['Contact 1', 50],
        'Attn2'            => ['Contact 2', 50],
        'Attn3'            => ['Contact 3', 50],
        'Attn4'            => ['Contact 4', 50],
        'Phone'            => ['Phone', 20],
        'Fax'              => ['Fax', 20],
        'Email'            => ['E-Mail', 50],
    ];

    // Pintu masuk modul (kartu di home & logo navbar) - langsung ke daftar customer
    public function dashboard()
    {
        return redirect()->route('master-data-customer');
    }

    public function customer()
    {
        return $this->halaman('customer');
    }

    public function supplier()
    {
        return $this->halaman('supplier');
    }

    private function halaman($jenis)
    {
        $tipeSup = self::JENIS[$jenis]['tipe_sup'];

        $negara = DB::connection('mysql_sb')->table('mastersupplier')
            ->where('tipe_sup', $tipeSup)
            ->whereRaw("trim(coalesce(country, '')) <> ''")
            ->distinct()
            ->orderBy('country')
            ->pluck('country');

        $area = DB::connection('mysql_sb')->table('mastersupplier')
            ->where('tipe_sup', $tipeSup)
            ->whereRaw("trim(coalesce(area, '')) <> ''")
            ->distinct()
            ->orderBy('area')
            ->pluck('area')
            ->mapWithKeys(fn ($kode) => [$kode => $this->labelArea($jenis, $kode)]);

        return view('master-data.partner.index', [
            'jenis'        => $jenis,
            'conf'         => self::JENIS[$jenis],
            'negara'       => $negara,
            'area'         => $area,
            // Kolom baru supplier (Blocked, Payment Flexibility Rating, dll); false = SQL-nya belum dijalankan
            'baru'         => $this->supplierBaru($jenis),
            'labelNilai'   => ['rating' => self::RATING, 'status' => self::STATUS, 'pkp' => self::PKP, 'fasilitas' => self::FASILITAS],
            // Filter, cari, halaman & urutan saat kembali dari Create / Edit
            'awal'         => $this->keadaanDaftar(),
            'page'           => 'dashboard-master-data',
            'subPageGroup'   => 'master-data',
            'subPage'        => 'master-' . $jenis,
            // Selebar layar, sama dengan daftar Invoice EXIM
            'containerFluid' => true,
        ]);
    }

    /**
     * Keadaan daftar yang dibawa ke Create / Edit lewat query string, lalu dipakai lagi waktu kembali
     * (sesudah simpan atau tombol Back) supaya filter & pencarian tidak hilang. Hanya kunci & nilai
     * yang dikenal yang dipakai.
     */
    private function keadaanDaftar()
    {
        $q = request()->query();
        $teks = fn ($kunci, $maks) => mb_substr(trim((string) (is_string($q[$kunci] ?? null) ? $q[$kunci] : '')), 0, $maks);

        return [
            'country' => $teks('country', 50),
            'area'    => $teks('area', 20),
            'status'  => in_array($q['status'] ?? '', ['aktif', 'nonaktif', 'blokir'], true) ? $q['status'] : '',
            'cari'    => $teks('cari', 100),
            'hal'     => min(100000, max(0, (int) $teks('hal', 6))),
            'baris'   => in_array((int) $teks('baris', 3), [10, 25, 50, 100], true) ? (int) $teks('baris', 3) : 10,
            'urut'    => ctype_digit($teks('urut', 1)) ? (int) $teks('urut', 1) : 1,
            'arah'    => $teks('arah', 4) === 'desc' ? 'desc' : 'asc',
        ];
    }

    // Alamat daftar dengan keadaan yang dibawa (yang bawaan tidak ikut ditulis)
    private function urlDaftar($jenis)
    {
        $bawaan = ['country' => '', 'area' => '', 'status' => '', 'cari' => '', 'hal' => 0, 'baris' => 10, 'urut' => 1, 'arah' => 'asc'];
        $keadaan = array_diff_assoc($this->keadaanDaftar(), $bawaan);

        return route('master-data-' . $jenis, $keadaan);
    }

    public function data(Request $request, $jenis)
    {
        $rows = $this->ambil($request, $jenis)->map(function ($row) use ($jenis) {
            $row->area_label = $this->labelArea($jenis, $row->area);
            $row->status = $this->statusDari($row);
            unset($row->status_aktif);
            return $row;
        });

        return response()->json(['data' => $rows]);
    }

    private function ambil(Request $request, $jenis)
    {
        $query = DB::connection('mysql_sb')->table('mastersupplier')
            ->select(
                'Id_Supplier as id',
                'supplier_code as kode',
                'Supplier as nama',
                'short_name',
                'country',
                'area',
                'jenis_fasilitas as fasilitas',
                'alamat',
                'npwp',
                'pkp',
                'Attn as kontak',
                'Phone as telepon',
                'Email as email',
                'product_name as produk',
                'non_aktif'
            )
            ->where('tipe_sup', self::JENIS[$jenis]['tipe_sup']);

        $baru = $this->supplierBaru($jenis);
        if ($baru) {
            $query->addSelect('legal_name', 'nib', 'payment_rating', 'payment_review_date', 'status_aktif', 'blocked_reason',
                'verified_by', 'verified_date', 'approved_by', 'approved_date', 'modified_by', 'modified_date');
        }

        if ($request->filled('country')) {
            $query->where('country', $request->country);
        }

        if ($request->filled('area')) {
            $query->where('area', $request->area);
        }

        // non_aktif '1' = tidak aktif, selain itu (0 / kosong) dianggap aktif.
        // Blocked = non_aktif '1' dengan status_aktif BLOCKED (lihat statusDari()).
        if ($request->status === 'aktif') {
            $query->where(fn ($q) => $q->whereNull('non_aktif')->orWhere('non_aktif', '<>', '1'));
        } elseif ($request->status === 'nonaktif') {
            $query->where('non_aktif', '1');
            if ($baru) {
                $query->where(fn ($q) => $q->whereNull('status_aktif')->orWhere('status_aktif', '<>', 'BLOCKED'));
            }
        } elseif ($request->status === 'blokir' && $baru) {
            $query->where('non_aktif', '1')->where('status_aktif', 'BLOCKED');
        }

        return $query->orderBy('Supplier')->get();
    }

    public function excel(Request $request, $jenis)
    {
        $conf = self::JENIS[$jenis];
        $rows = $this->ambil($request, $jenis);
        $supplier = $jenis === 'supplier';
        $baru = $this->supplierBaru($jenis);

        if ($baru) {
            $kolom = ['No', 'ID', 'Code', $conf['kolom'], 'Legal Name', 'Short Name', 'Country', 'Type', 'Customs Classification', 'Address',
                      'NPWP', 'PKP Status', 'NIB', 'Contact', 'Phone', 'Email', 'Product', 'Payment Flexibility Rating',
                      'Payment Flexibility Last Review Date', 'Supplier Status', 'Blocked Reason', 'Verified By', 'Verified Date',
                      'Approved By', 'Approved Date', 'Last Modified By', 'Last Modified Date'];
        } else {
            $kolom = ['No', 'ID', 'Code', $conf['kolom'], 'Short Name', 'Country', $supplier ? 'Type' : 'Area', 'Facility', 'Address', 'NPWP', 'PKP', 'Contact', 'Phone', 'Email'];
            if ($supplier) {
                $kolom[] = 'Product';
            }
            $kolom[] = 'Status';
        }

        $ss = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $s  = $ss->getActiveSheet();
        $s->setTitle($conf['kolom']);

        $s->setCellValue('A1', $conf['judul']);
        $s->getStyle('A1')->getFont()->setBold(true)->setSize(13);
        $s->setCellValue('A2', 'Exported ' . now()->format('d M Y H:i') . ' - ' . count($rows) . ' rows');
        $s->getStyle('A2')->getFont()->setSize(9)->getColor()->setARGB('FF64748B');

        $bKepala = 4;
        $s->fromArray($kolom, null, 'A' . $bKepala);
        $akhirKol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(count($kolom));
        $kepala = 'A' . $bKepala . ':' . $akhirKol . $bKepala;
        $s->getStyle($kepala)->getFont()->setBold(true);
        // Latar abu tipis untuk kepala tabel - sama dengan Excel Invoice EXIM
        $s->getStyle($kepala)->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FFF1F5F9');

        $b = $bKepala + 1;
        foreach ($rows as $i => $r) {
            $area = $this->labelArea($jenis, $r->area);
            $status = self::STATUS[$this->statusDari($r)];
            if ($baru) {
                $isi = [
                    $i + 1,
                    (int) $r->id,
                    $r->kode,
                    $r->nama,
                    $r->legal_name,
                    $r->short_name,
                    $r->country,
                    $area,
                    self::FASILITAS[$r->fasilitas] ?? $r->fasilitas,
                    $r->alamat,
                    $r->npwp,
                    self::PKP[$r->pkp] ?? $r->pkp,
                    $r->nib,
                    $r->kontak,
                    $r->telepon,
                    $r->email,
                    $r->produk,
                    self::RATING[$r->payment_rating] ?? '',
                    $r->payment_review_date ? date('d M Y', strtotime($r->payment_review_date)) : '',
                    $status,
                    $status === 'Blocked' ? $r->blocked_reason : '',
                    $r->verified_by,
                    $r->verified_date ? date('d M Y H:i', strtotime($r->verified_date)) : '',
                    $r->approved_by,
                    $r->approved_date ? date('d M Y H:i', strtotime($r->approved_date)) : '',
                    $r->modified_by,
                    $r->modified_date ? date('d M Y H:i', strtotime($r->modified_date)) : '',
                ];
            } else {
                $isi = [
                    $i + 1,
                    (int) $r->id,
                    $r->kode,
                    $r->nama,
                    $r->short_name,
                    $r->country,
                    $area,
                    $r->fasilitas,
                    $r->alamat,
                    $r->npwp,
                    $r->pkp,
                    $r->kontak,
                    $r->telepon,
                    $r->email,
                ];
                if ($supplier) {
                    $isi[] = $r->produk;
                }
                $isi[] = $status;
            }

            foreach ($isi as $k => $nilai) {
                $sel = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($k + 1) . $b;
                // No & ID tetap angka
                if ($k <= 1) {
                    $s->setCellValue($sel, $nilai);
                } else {
                    // Kode, NPWP & telepon disimpan sebagai teks supaya nol di depan tidak hilang
                    $s->setCellValueExplicit($sel, trim((string) $nilai), \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
                }
            }
            $b++;
        }

        $s->getStyle('A' . $bKepala . ':' . $akhirKol . max($bKepala, $b - 1))->getBorders()->getAllBorders()
            ->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $s->getStyle('A' . $bKepala . ':' . $akhirKol . max($bKepala, $b - 1))->getFont()->setSize(10);

        // No lebarnya tetap: autosize ikut menghitung judul & keterangan export di A1 / A2 sehingga kolomnya melebar
        $s->getColumnDimension('A')->setWidth(6);
        foreach (range(2, count($kolom)) as $k) {
            $s->getColumnDimensionByColumn($k)->setAutoSize(true);
        }
        // Alamat & produk bisa sangat panjang - dibatasi lebar lalu dibungkus
        foreach (['Address', 'Product'] as $judul) {
            $idx = array_search($judul, $kolom, true);
            if ($idx !== false) {
                $huruf = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($idx + 1);
                $s->getColumnDimension($huruf)->setAutoSize(false)->setWidth(50);
                $s->getStyle($huruf . ($bKepala + 1) . ':' . $huruf . max($bKepala, $b - 1))->getAlignment()->setWrapText(true);
            }
        }
        $s->freezePane('A' . ($bKepala + 1));

        $nama = $conf['judul'] . ' ' . now()->format('Ymd') . '.xlsx';
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($ss);

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $nama, ['Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']);
    }

    // ===================================================================
    // Create - patokan Signalbit pages/forms/master_supcust.php (form) &
    // pages/forms/save_data.php blok mode Supplier/Customer (simpan).
    // ===================================================================

    public function create($jenis)
    {
        return $this->formulir($jenis);
    }

    // Edit - form yang sama dengan create, terisi data lama. Patokan form edit Signalbit master_supcust.php.
    public function edit(Request $request, $jenis)
    {
        $id = (int) $request->query('id');
        $row = DB::connection('mysql_sb')->table('mastersupplier')
            ->where('Id_Supplier', $id)->where('tipe_sup', self::JENIS[$jenis]['tipe_sup'])->first();

        if (! $row) {
            abort(404);
        }

        return $this->formulir($jenis, $row);
    }

    private function formulir($jenis, $ubah = null)
    {
        $sb = DB::connection('mysql_sb');
        $pilihan = fn ($kode) => $sb->table('masterpilihan')->where('kode_pilihan', $kode)->orderBy('nama_pilihan')->pluck('nama_pilihan');
        $autoCoa = $this->autoCoa();

        // Data lama hanya punya jumlah hari di terms_of_pay (id_terms kosong) - ditampilkan sebagai keterangan
        $termLama = $ubah && ! (int) $ubah->id_terms && (float) $ubah->terms_of_pay > 0 ? (float) $ubah->terms_of_pay . ' days' : null;

        if ($jenis === 'supplier') {
            // Type supplier: Local / Overseas (nilai = kode area tersimpan)
            $area = $this->denganNilaiLama(collect(self::TIPE_SUPPLIER), $ubah->area ?? null, self::AREA[$ubah->area ?? ''] ?? null);
        } else {
            // nilai tetap pilihan aslinya (Lokal / Import/Export / Factory), labelnya bahasa Inggris
            $area = $pilihan('Area')->mapWithKeys(fn ($a) => [$a => self::AREA[strtoupper(substr($a, 0, 1))] ?? $a]);
        }

        // Sesudah SQL kolom baru dijalankan: pilihan Facility & PKP sesuai ketentuan (nilai => label).
        // Nilai data lama yang tidak ada di pilihan tetap ikut supaya tidak hilang waktu disimpan.
        if ($this->kolomBaru()) {
            $fasilitas = $this->denganNilaiLama(collect(self::FASILITAS), $ubah->jenis_fasilitas ?? null);
            $pkp = $this->denganNilaiLama(collect(self::PKP), $ubah->pkp ?? null);
        } else {
            $fasilitas = $pilihan('J_FAS')->mapWithKeys(fn ($f) => [$f => $f]);
            $pkp = $pilihan('PKP')->mapWithKeys(fn ($p) => [$p => $p]);
        }

        return view('master-data.partner.form', [
            'jenis'          => $jenis,
            'conf'           => self::JENIS[$jenis],
            'ubah'           => $ubah,
            'kodeTerkunci'   => $ubah ? $this->dipakaiTransaksi($ubah->Id_Supplier) : false,
            // Isian baru supplier (Legal Name, NIB, Payment Flexibility); false = kolomnya belum dibuat, tidak ditampilkan
            'baru'           => $this->supplierBaru($jenis),
            'rating'         => self::RATING,
            'status'         => $ubah ? $this->statusDari($ubah) : 'ACTIVE',
            // Kembali ke daftar dengan filter & pencarian yang sama seperti sebelum Create / Edit dibuka
            'urlKembali'     => $this->urlDaftar($jenis),
            'termLama'       => $termLama,
            'area'           => $area,
            'kategori'       => $pilihan('Vendor_Cat'),
            'fasilitas'      => $fasilitas,
            'pkp'            => $pkp,
            'terms'          => $sb->table('masterpterms')->select('id', 'kode_pterms', 'nama_pterms')->orderBy('id')->get(),
            'negara'         => $sb->table('mastersupplier')->whereRaw("trim(coalesce(country, '')) <> ''")->distinct()->orderBy('country')->pluck('country'),
            'autoCoa'        => $autoCoa,
            // CoA dipilih manual hanya kalau auto_coa_supp bukan 'Y' (sama dengan Signalbit)
            'coa'            => $autoCoa ? collect() : $sb->table('mastercoa')->select('id_coa', 'nm_coa')->orderBy('id_coa')->get(),
            'panjang'        => collect(self::PANJANG)->map(fn ($p) => $p[1]),
            'page'           => 'dashboard-master-data',
            'subPageGroup'   => 'master-data',
            'subPage'        => 'master-' . $jenis,
            'containerFluid' => true,
        ]);
    }

    public function simpan(Request $request, $jenis)
    {
        $conf = self::JENIS[$jenis];
        $sb = DB::connection('mysql_sb');

        [$v, $kategori, $kurang] = $this->periksa($request, $jenis);
        if ($kurang) {
            return response()->json(['status' => 400, 'message' => 'Please complete the form', 'kurang' => $kurang], 422);
        }

        // Nama + tipe tidak boleh dobel (cek yang sama dengan Signalbit)
        $dobel = $sb->table('mastersupplier')->where('Supplier', $v['Supplier'])->where('tipe_sup', $conf['tipe_sup'])->first(['Id_Supplier', 'supplier_code']);
        if ($dobel) {
            return response()->json([
                'status'  => 409,
                'message' => $conf['kolom'] . ' ' . $v['Supplier'] . ' already exists (code ' . ($dobel->supplier_code ?: '-') . ').',
            ], 409);
        }

        $sb->beginTransaction();
        try {
            $idCoa = $this->autoCoa()
                ? $this->buatCoaPartner($v['supplier_code'] . ' - ' . $v['Supplier'])
                : $this->nb($request->input('id_coa'));

            // Term of payment: insert Signalbit menaruh ID term ke terms_of_pay, padahal kolom itu berisi
            // jumlah hari (data lama 30/45/90, dan dicetak di invoice). Di sini id-nya ke id_terms
            // (yang dibaca form edit Signalbit) dan jumlah harinya ke terms_of_pay.
            $idTerms = (int) $request->input('id_terms');
            $term = $idTerms ? $sb->table('masterpterms')->where('id', $idTerms)->first(['id', 'days_pterms']) : null;

            // Kolom & urutan mengikuti insert Signalbit (ditambah id_terms). Area disimpan huruf pertamanya (L/I/F).
            // Data bank tidak lagi di mastersupplier - rekening dikelola di master_supplier_bank.
            $data = [
                'Supplier'         => $v['Supplier'],
                'Attn'             => $v['Attn'],
                'Phone'            => $v['Phone'],
                'Fax'              => $v['Fax'],
                'Email'            => $v['Email'],
                'area'             => substr($v['area'], 0, 1),
                'vendor_cat'       => $kategori !== '' ? $kategori : 'NG',
                'jenis_fasilitas'  => $v['jenis_fasilitas'],
                'alamat'           => $v['alamat'],
                'alamat2'          => $v['alamat2'],
                'npwp'             => $v['npwp'],
                'status_kb'        => '',
                'country'          => $v['country'],
                'tipe_sup'         => $conf['tipe_sup'],
                'supplier_code'    => $v['supplier_code'],
                'short_name'       => $v['short_name'],
                'zip_code'         => $v['zip_code'],
                'group_name'       => $v['group_name'],
                'product_name'     => $v['product_name'],
                'moq'              => $this->angka($request->input('moq')),
                'lead_time'        => $this->angka($request->input('lead_time')),
                'moq_lead_time'    => $this->angka($request->input('moq_lead_time')),
                'terms_of_pay'     => $term ? (float) $term->days_pterms : 0,
                'id_terms'         => $term ? $term->id : null,
                'pkp'              => $v['pkp'],
                'id_coa'           => (string) $idCoa,
                'Attn2'            => $v['Attn2'],
                'Attn3'            => $v['Attn3'],
                'Attn4'            => $v['Attn4'],
                'tipe_buyer'       => $jenis === 'customer' && $request->boolean('tipe_buyer') ? 'Y' : '',
                'tipe_agent'       => $jenis === 'customer' && $request->boolean('tipe_agent') ? 'Y' : '',
                // Kolom tinyint (default 0) - dicentang = 1
                'to_account_manual' => $request->boolean('to_account_manual') ? 1 : 0,
            ];
            if ($this->supplierBaru($jenis)) {
                $data += $this->isianBaru($v) + ['status_aktif' => 'ACTIVE'] + $this->jejakUbah();
            }

            $id = $sb->table('mastersupplier')->insertGetId($data, 'Id_Supplier');
            $this->riwayat()->catatBuat($id, $data, $this->catatActHist($data));

            $sb->commit();
        } catch (\Throwable $e) {
            $sb->rollBack();
            throw $e;
        }

        return response()->json([
            'status'  => 200,
            'id'      => $id,
            'kode'    => $data['supplier_code'],
            'nama'    => $data['Supplier'],
        ]);
    }

    // Update - patokan blok update mode Supplier/Customer di save_data.php Signalbit:
    // - Code tidak boleh berubah kalau partner sudah dipakai transaksi (bpb, bppb, act_costing).
    // - Nama yang sudah dipakai partner lain ditolak dengan pesan (Signalbit diam-diam tidak menyimpan).
    // - id_coa tidak disentuh (Signalbit menghitung ulang dan bisa mengosongkannya).
    // - Semua isian form disimpan (Signalbit melewatkan short_name, zip_code, group_name, moq, lead time).
    public function perbarui(Request $request, $jenis)
    {
        $conf = self::JENIS[$jenis];
        $sb = DB::connection('mysql_sb');
        $id = (int) $request->input('id');

        $lama = $sb->table('mastersupplier')->where('Id_Supplier', $id)->where('tipe_sup', $conf['tipe_sup'])->first();
        if (! $lama) {
            return response()->json(['status' => 404, 'message' => $conf['kolom'] . ' not found'], 404);
        }

        // Code terkunci: isian dari form diabaikan, tetap kode lama (data lama boleh kosong)
        $terkunci = $this->dipakaiTransaksi($id);
        if ($terkunci) {
            $request->merge(['supplier_code' => $lama->supplier_code]);
        }

        [$v, $kategori, $kurang] = $this->periksa($request, $jenis, $lama, ! $terkunci);
        if ($kurang) {
            return response()->json(['status' => 400, 'message' => 'Please complete the form', 'kurang' => $kurang], 422);
        }

        $dobel = $sb->table('mastersupplier')->where('Supplier', $v['Supplier'])->where('tipe_sup', $conf['tipe_sup'])
            ->where('Id_Supplier', '<>', $id)->first(['Id_Supplier', 'supplier_code']);
        if ($dobel) {
            return response()->json([
                'status'  => 409,
                'message' => $conf['kolom'] . ' ' . $v['Supplier'] . ' already exists (code ' . ($dobel->supplier_code ?: '-') . ').',
            ], 409);
        }

        $data = [
            'supplier_code'   => $terkunci ? $lama->supplier_code : $v['supplier_code'],
            'Supplier'        => $v['Supplier'],
            'short_name'      => $v['short_name'],
            'group_name'      => $v['group_name'],
            'pkp'             => $v['pkp'],
            'npwp'            => $v['npwp'],
            'area'            => substr($v['area'], 0, 1),
            'vendor_cat'      => $kategori,
            'jenis_fasilitas' => $v['jenis_fasilitas'],
            'product_name'    => $v['product_name'],
            'moq'             => $this->angka($request->input('moq')),
            'lead_time'       => $this->angka($request->input('lead_time')),
            'moq_lead_time'   => $this->angka($request->input('moq_lead_time')),
            'alamat'          => $v['alamat'],
            'alamat2'         => $v['alamat2'],
            'zip_code'        => $v['zip_code'],
            'country'         => $v['country'],
            'Attn'            => $v['Attn'],
            'Attn2'           => $v['Attn2'],
            'Attn3'           => $v['Attn3'],
            'Attn4'           => $v['Attn4'],
            'Phone'           => $v['Phone'],
            'Fax'             => $v['Fax'],
            'Email'           => $v['Email'],
        ];

        // Term of payment hanya diubah kalau pilihannya berubah. Data lama yang cuma punya jumlah hari
        // (id_terms kosong) dan tidak dipilih term baru, terms_of_pay-nya dibiarkan.
        $idTerms = (int) $request->input('id_terms');
        if ($idTerms !== (int) $lama->id_terms) {
            $term = $idTerms ? $sb->table('masterpterms')->where('id', $idTerms)->first(['id', 'days_pterms']) : null;
            $data['id_terms'] = $term ? $term->id : null;
            $data['terms_of_pay'] = $term ? (float) $term->days_pterms : 0;
        }

        // Buyer / Agent khusus customer - supplier tidak punya isian ini, nilainya tidak ditimpa
        if ($jenis === 'customer') {
            $data['tipe_buyer'] = $request->boolean('tipe_buyer') ? 'Y' : '';
            $data['tipe_agent'] = $request->boolean('tipe_agent') ? 'Y' : '';
        }
        $data['to_account_manual'] = $request->boolean('to_account_manual') ? 1 : 0;
        $baru = $this->supplierBaru($jenis);
        if ($baru) {
            $data += $this->isianBaru($v);
        }

        // Yang disimpan & dicatat hanya kolom yang nilainya berubah. Kosong vs NULL di data lama
        // dianggap sama supaya tidak tercatat berubah padahal tidak diisi apa-apa.
        $berubah = array_filter($data, fn ($nilai, $kolom) => (string) $nilai !== (string) $lama->$kolom, ARRAY_FILTER_USE_BOTH);

        if ($berubah) {
            // Last Modified By / Date ikut disimpan, tapi tidak dihitung sebagai perubahan isian
            $simpan = $baru ? $berubah + $this->jejakUbah() : $berubah;
            $sb->transaction(function () use ($sb, $id, $berubah, $simpan, $lama) {
                $sb->table('mastersupplier')->where('Id_Supplier', $id)->update($simpan);
                $actHistId = $this->catatLog($this->sqlUpdate('mastersupplier', $simpan, 'id_supplier', $id));
                $this->riwayat()->catatUbah($id, $lama, $berubah, $actHistId);
            });
        }

        return response()->json([
            'status'  => 200,
            'id'      => $id,
            'kode'    => $data['supplier_code'],
            'nama'    => $data['Supplier'],
            'berubah' => count($berubah),
        ]);
    }

    // Isian form create / edit: semua teks lewat nb() seperti Signalbit (trim, huruf besar, tanpa tanda kutip),
    // lalu validasi sama dengan validasi() di master_supcust.php - semua kekurangan dilaporkan sekaligus.
    // $lama = data sebelum diedit (null waktu create): nilai lama yang tidak ada di pilihan sekarang
    // (mis. Type Factory, Facility KITE) tetap boleh disimpan apa adanya.
    private function periksa(Request $request, $jenis, $lama = null, $kodeWajib = true)
    {
        $conf = self::JENIS[$jenis];
        $supplier = $jenis === 'supplier';
        $baru = $this->supplierBaru($jenis);

        $v = [];
        foreach (['supplier_code', 'short_name', 'Supplier', 'legal_name', 'group_name', 'product_name', 'alamat', 'alamat2', 'zip_code',
                  'country', 'npwp', 'pkp', 'Attn', 'Attn2', 'Attn3', 'Attn4', 'Phone', 'Fax', 'Email',
                  'jenis_fasilitas', 'area'] as $kolom) {
            $v[$kolom] = $this->nb($request->input($kolom));
        }
        $v['nib'] = preg_replace('/\s+/', '', (string) $request->input('nib'));
        $v['payment_rating'] = strtoupper(trim((string) $request->input('payment_rating')));
        $v['payment_review_date'] = trim((string) $request->input('payment_review_date'));
        $kategori = (string) $request->input('vendor_cat');
        $kodeArea = substr($v['area'], 0, 1);
        $sah = fn ($nilai, array $pilihan, $kolom) => $nilai === '' || array_key_exists($nilai, $pilihan)
            || ($lama && $nilai === (string) $lama->$kolom);

        $kurang = [];
        if ($kodeWajib && $v['supplier_code'] === '') { $kurang[] = 'Code'; }
        if ($v['Supplier'] === '') { $kurang[] = $conf['kolom'] . ' Name'; }
        if ($v['npwp'] === '') { $kurang[] = 'NPWP'; }
        // Nilai kategori = dua huruf depan pilihan Vendor_Cat (GR / NG), sama dengan combo Signalbit
        $kategoriSah = DB::connection('mysql_sb')->table('masterpilihan')->where('kode_pilihan', 'Vendor_Cat')->pluck('nama_pilihan')
            ->map(fn ($p) => substr($p, 0, 2))->all();
        if ($kategori === '' || !in_array($kategori, $kategoriSah, true)) { $kurang[] = 'Category'; }
        if ($v['area'] === '') {
            $kurang[] = $supplier ? 'Type' : 'Area';
        } elseif ($supplier && ! $sah($kodeArea, self::TIPE_SUPPLIER, 'area')) {
            $kurang[] = 'Type (Local / Overseas)';
        }
        $labelFasilitas = $baru ? 'Customs Classification' : 'Facility';
        if ($kodeArea === 'L' && $v['jenis_fasilitas'] === '') { $kurang[] = $labelFasilitas . ' (required for Local)'; }
        if ($this->kolomBaru()) {
            if (! $sah($v['jenis_fasilitas'], self::FASILITAS, 'jenis_fasilitas')) { $kurang[] = $labelFasilitas; }
            if (! $sah($v['pkp'], self::PKP, 'pkp')) { $kurang[] = 'PKP Status'; }
        }
        if ($baru) {
            if ($v['nib'] !== '' && ! preg_match('/^\d{13}$/', $v['nib'])) { $kurang[] = 'NIB must be 13 digits'; }
            if (! $sah($v['payment_rating'], self::RATING, 'payment_rating')) { $kurang[] = 'Payment Flexibility Rating'; }
            $tgl = $v['payment_review_date'];
            if ($tgl !== '') {
                $d = \DateTime::createFromFormat('!Y-m-d', $tgl);
                if (! $d || $d->format('Y-m-d') !== $tgl) {
                    $kurang[] = 'Last Review Date is not a valid date';
                } elseif ($tgl > now()->format('Y-m-d')) {
                    $kurang[] = 'Last Review Date cannot be in the future';
                }
            }
        }
        foreach (self::PANJANG as $kolom => [$label, $maks]) {
            if (mb_strlen($v[$kolom]) > $maks) {
                $kurang[] = $label . ' max ' . $maks . ' characters';
            }
        }

        return [$v, $kategori, $kurang];
    }

    // Isian baru supplier yang disimpan dari form create / edit (kosong = NULL)
    private function isianBaru(array $v)
    {
        return [
            'legal_name'          => $v['legal_name'] !== '' ? $v['legal_name'] : null,
            'nib'                 => $v['nib'] !== '' ? $v['nib'] : null,
            'payment_rating'      => $v['payment_rating'] !== '' ? $v['payment_rating'] : null,
            'payment_review_date' => $v['payment_review_date'] !== '' ? $v['payment_review_date'] : null,
        ];
    }

    // Last Modified By / Date - diisi setiap create, edit & ubah status dari NDS
    private function jejakUbah()
    {
        return [
            'modified_by'   => substr((string) (Auth::user()->username ?? Auth::user()->name), 0, 100),
            'modified_date' => now()->format('Y-m-d H:i:s'),
        ];
    }

    // Kolom dari database/sql/2026_10_30_mastersupplier_kolom_baru.sql sudah dibuat (termasuk jenis_fasilitas
    // yang dilebarkan). Sebelum itu isian barunya disembunyikan dan form tetap seperti sebelumnya.
    private function kolomBaru()
    {
        static $ada;
        if ($ada === null) {
            $kolom = DB::connection('mysql_sb')->table('information_schema.columns')
                ->whereRaw('table_schema = database()')
                ->where('table_name', 'mastersupplier')
                ->whereIn('column_name', array_merge(self::KOLOM_BARU, ['jenis_fasilitas']))
                ->pluck('character_maximum_length', 'column_name');
            $ada = $kolom->count() === count(self::KOLOM_BARU) + 1 && (int) $kolom['jenis_fasilitas'] >= 17;
        }
        return $ada;
    }

    // Isian baru (Legal Name, NIB, Blocked, Payment Flexibility, dll) hanya untuk supplier
    private function supplierBaru($jenis)
    {
        return $jenis === 'supplier' && $this->kolomBaru();
    }

    // Active / Inactive / Blocked. non_aktif yang menentukan aktif atau tidak (Signalbit hanya mengubah
    // kolom itu), status_aktif hanya membedakan Inactive dengan Blocked.
    private function statusDari($row)
    {
        if ((string) $row->non_aktif !== '1') {
            return 'ACTIVE';
        }
        return ($row->status_aktif ?? null) === 'BLOCKED' ? 'BLOCKED' : 'INACTIVE';
    }

    private function labelArea($jenis, $kode)
    {
        $label = $jenis === 'supplier' ? self::TIPE_SUPPLIER + self::AREA : self::AREA;
        return $label[$kode] ?? $kode;
    }

    // Pilihan (nilai => label) ditambah nilai data lama yang tidak ada di pilihan, ditandai (old)
    private function denganNilaiLama($pilihan, $nilai, $label = null)
    {
        $nilai = (string) $nilai;
        if (trim($nilai) !== '' && ! $pilihan->has($nilai)) {
            $pilihan->put($nilai, ($label ?? $nilai) . ' (old)');
        }
        return $pilihan;
    }

    // Partner sudah dipakai transaksi (penerimaan, pengeluaran, costing) -> Code tidak boleh diganti.
    // Ketiga kolom ber-index, cek exists ~1 ms.
    private function dipakaiTransaksi($id)
    {
        $sb = DB::connection('mysql_sb');

        return $sb->table('bpb')->where('id_supplier', $id)->exists()
            || $sb->table('bppb')->where('id_supplier', $id)->exists()
            || $sb->table('act_costing')->where('id_buyer', $id)->exists();
    }

    // Detail satu customer / supplier untuk modal Show
    public function detail(Request $request, $jenis)
    {
        $id = (int) $request->query('id');
        $sb = DB::connection('mysql_sb');
        $row = $sb->table('mastersupplier')->where('Id_Supplier', $id)->where('tipe_sup', self::JENIS[$jenis]['tipe_sup'])->first();

        if (! $row) {
            return response()->json(['message' => self::JENIS[$jenis]['kolom'] . ' not found'], 404);
        }

        $term = $row->id_terms ? $sb->table('masterpterms')->where('id', $row->id_terms)->first(['kode_pterms', 'nama_pterms']) : null;
        if ($term) {
            $termLabel = $term->kode_pterms . ($term->nama_pterms && $term->nama_pterms !== $term->kode_pterms ? ' | ' . $term->nama_pterms : '');
        } else {
            // Data lama: terms_of_pay berisi jumlah hari
            $termLabel = (float) $row->terms_of_pay > 0 ? (float) $row->terms_of_pay . ' days' : null;
        }

        $kategori = $sb->table('masterpilihan')->where('kode_pilihan', 'Vendor_Cat')->pluck('nama_pilihan')
            ->first(fn ($p) => substr($p, 0, 2) === $row->vendor_cat);

        // CoA urusan akunting di belakang - tidak dikirim ke layar
        unset($row->id_coa);

        // Rekening bank dari master_supplier_bank (bisa lebih dari satu), yang aktif di atas
        $bank = $sb->table('master_supplier_bank')
            ->select('bank_currency', 'bank_name', 'bank_account', 'beneficiary_name', 'swift_code', 'status')
            ->where('id_supplier', (string) $row->Id_Supplier)
            ->orderByRaw("status = 'Active' desc")
            ->orderByDesc('id')
            ->get();

        $baru = $this->supplierBaru($jenis);
        $status = $this->statusDari($row);
        $waktu = fn ($nilai, $format) => $nilai ? date($format, strtotime($nilai)) : null;

        return response()->json([
            'data'            => $row,
            'area_label'      => $this->labelArea($jenis, $row->area),
            'kategori_label'  => $kategori ?? $row->vendor_cat,
            'fasilitas_label' => self::FASILITAS[$row->jenis_fasilitas] ?? $row->jenis_fasilitas,
            'pkp_label'       => self::PKP[$row->pkp] ?? $row->pkp,
            'status'          => ['nilai' => $status, 'label' => self::STATUS[$status]],
            // null = tidak ada isian baru (customer, atau kolomnya belum dibuat); label rating null = belum diisi
            'baru'            => $baru ? [
                'rating'        => ['nilai' => $row->payment_rating, 'label' => self::RATING[$row->payment_rating ?? ''] ?? null],
                'review_date'   => $waktu($row->payment_review_date, 'd M Y'),
                'verified_date' => $waktu($row->verified_date, 'd M Y H:i'),
                'approved_date' => $waktu($row->approved_date, 'd M Y H:i'),
                'modified_date' => $waktu($row->modified_date, 'd M Y H:i'),
            ] : null,
            'term_label'      => $termLabel,
            'bank'            => $bank,
        ]);
    }

    // Ubah status. Signalbit cuma punya Non Aktif untuk supplier (pages/forms/non_akt.php, non_aktif = '1');
    // di sini dua arah, dicatat ke act_hist. Supplier (sesudah SQL kolom baru) memilih Active / Inactive /
    // Blocked - Blocked wajib dengan alasannya, dan non_aktif tetap diisi supaya Signalbit ikut menyaring.
    public function status(Request $request, $jenis)
    {
        $conf = self::JENIS[$jenis];
        $id = (int) $request->input('id');
        $sb = DB::connection('mysql_sb');
        $baru = $this->supplierBaru($jenis);

        $row = $sb->table('mastersupplier')->where('Id_Supplier', $id)->where('tipe_sup', $conf['tipe_sup'])
            ->first(array_merge(['Id_Supplier', 'Supplier', 'non_aktif'], $baru ? ['status_aktif'] : []));
        if (! $row) {
            return response()->json(['message' => $conf['kolom'] . ' not found'], 404);
        }

        $alasan = null;
        if ($baru) {
            $status = strtoupper(trim((string) $request->input('status')));
            if (! array_key_exists($status, self::STATUS)) {
                return response()->json(['message' => 'Please choose the new status.'], 422);
            }
            if ($status === $this->statusDari($row)) {
                return response()->json(['message' => $row->Supplier . ' is already ' . self::STATUS[$status] . '.'], 422);
            }
            if ($status === 'BLOCKED') {
                $alasan = trim(preg_replace('/\s+/', ' ', (string) $request->input('alasan')));
                if ($alasan === '') {
                    return response()->json(['message' => 'Blocked reason is required.'], 422);
                }
                if (mb_strlen($alasan) > 255) {
                    return response()->json(['message' => 'Blocked reason max 255 characters.'], 422);
                }
            }
            $data = [
                'non_aktif'      => $status === 'ACTIVE' ? '0' : '1',
                'status_aktif'   => $status,
                'blocked_reason' => $alasan,
            ] + $this->jejakUbah();
        } else {
            $status = $request->boolean('aktif') ? 'ACTIVE' : 'INACTIVE';
            $data = ['non_aktif' => $status === 'ACTIVE' ? '0' : '1'];
        }

        $sb->transaction(function () use ($sb, $id, $data, $status, $alasan) {
            $sb->table('mastersupplier')->where('Id_Supplier', $id)->update($data);
            $actHistId = $this->catatLog($this->sqlUpdate('mastersupplier', $data, 'id_supplier', $id));
            $this->riwayat()->catatStatus($id, $status, $actHistId, $alasan);
        });

        return response()->json([
            'status' => 200,
            'aktif'  => $status === 'ACTIVE',
            'nilai'  => $status,
            'label'  => self::STATUS[$status],
            'nama'   => $row->Supplier,
        ]);
    }

    // Tab History di modal Show: dibuat, diubah, aktif / tidak aktif. Dimuat waktu tab-nya dibuka.
    public function histori(Request $request, $jenis)
    {
        $conf = self::JENIS[$jenis];
        $id = (int) $request->query('id');
        $ada = DB::connection('mysql_sb')->table('mastersupplier')->where('Id_Supplier', $id)->where('tipe_sup', $conf['tipe_sup'])->exists();
        if (! $ada) {
            return response()->json(['message' => $conf['kolom'] . ' not found'], 404);
        }

        $riwayat = $this->riwayat();
        if (! $riwayat->siap()) {
            return response()->json(['status' => 200, 'siap' => false, 'baris' => [], 'awal' => false]);
        }

        // Ambil dulu perubahan dari luar NDS yang belum tersalin
        try {
            $riwayat->sinkron();
        } catch (\Throwable $e) {
            Log::warning('Sinkron riwayat master data gagal - ' . $e->getMessage());
        }

        return response()->json(['status' => 200, 'siap' => true] + $riwayat->riwayat($id, $conf['kolom']));
    }

    private function riwayat()
    {
        return app(MasterDataHistoryService::class);
    }

    // mastercompany.auto_coa_supp = 'Y' -> CoA dibuat otomatis saat simpan (Signalbit save_data.php)
    private function autoCoa()
    {
        return DB::connection('mysql_sb')->table('mastercompany')->where('company', '<>', '')->value('auto_coa_supp') === 'Y';
    }

    // Salinan create_supplier_coa() Signalbit (pages/forms/journal_interface.php):
    // akun baru di segmen 2101 = nomor terakhir + 1 (2101999 tidak dihitung). Kalau nama sudah ada
    // hasilnya kosong. Kalau insert gagal karena nomornya sudah ada (segmen penuh), Signalbit tetap
    // memakai nomor itu - karena itu partner baru sekarang mendapat 2101999.
    private function buatCoaPartner($nama)
    {
        $sb = DB::connection('mysql_sb');
        $segmen = '2101';
        $postTo = '2101000';

        $ada = $sb->table('mastercoa')->where('id_coa', 'like', $segmen . '%')->where('nm_coa', $nama)->exists();
        if ($ada) {
            return '';
        }

        $maks = $sb->table('mastercoa')->where('id_coa', 'like', $segmen . '%')->where('id_coa', '<>', '2101999')->max('id_coa');
        $urut = (int) substr($maks ?? $postTo, strlen($segmen), 3) + 1;
        $idCoa = $segmen . str_pad($urut, 3, '0', STR_PAD_LEFT);

        $sb->table('mastercoa')->insertOrIgnore([
            'id_coa'     => $idCoa,
            'nm_coa'     => $nama,
            'fg_posting' => '1',
            'fg_mapping' => '1',
            'fg_active'  => '1',
            'post_to'    => $postTo,
        ]);

        return $idCoa;
    }

    private function catatActHist(array $data)
    {
        return $this->catatLog($this->sqlInsert('mastersupplier', $data));
    }
}
