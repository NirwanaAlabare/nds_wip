<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class Bc27Service
{
    protected $ceisaService;

    public function __construct(CeisaService $ceisaService)
    {
        $this->ceisaService = $ceisaService;
    }

        public function edit($id, Request $request)
    {
        $db = DB::connection('mysql_sb');

        $header = $db->table('bpb as a')
            ->select('a.*', 'ms.supplier', 'ms.alamat as alamat_supplier', 'ms.npwp as npwp_supplier',
                     DB::raw("IF(a.bpbno_int != '', a.bpbno_int, a.bpbno) as trx_no_par"))
            ->leftJoin('mastersupplier as ms', 'a.id_supplier', '=', 'ms.id_supplier')
            ->where(function($query) use ($id) {
                $query->where('a.bpbno', $id)->orWhere('a.bpbno_int', $id);
            })
            ->first();

        if (!$header) {
            $header = $db->table('bppb as a')
                ->select('a.*', 'a.bppbno as bpbno', 'a.bppbno_int as bpbno_int', 'a.bppbdate as bpbdate', 'ms.supplier', 'ms.alamat as alamat_supplier', 'ms.npwp as npwp_supplier',
                         DB::raw("IF(a.bppbno_int != '', a.bppbno_int, a.bppbno) as trx_no_par"))
                ->leftJoin('mastersupplier as ms', 'a.id_supplier', '=', 'ms.id_supplier')
                ->where(function($query) use ($id) {
                    $query->where('a.bppbno', $id)->orWhere('a.bppbno_int', $id);
                })
                ->first();
        }

        if (!$header) abort(404, 'Data Transaksi Tidak Ditemukan');

        $ceisaInfo = $db->table('bpb_ceisa')->where('bpbno', $id)->first();

        $dataDetail = json_decode($ceisaInfo->payload_json ?? '{}', true);

        $items = $db->table('bpb as a')
                ->leftJoin('masteritem as mi', 'a.id_item', '=', 'mi.id_item')
                ->leftJoin('masterstyle as ms', 'a.id_item', '=', 'ms.id_item')
                ->select(
                    'a.id_item',
                    DB::raw("IF(mi.goods_code IS NOT NULL AND mi.goods_code != '', mi.goods_code, ms.goods_code) as goods_code"),
                    DB::raw("IF(mi.itemdesc IS NOT NULL AND mi.itemdesc != '', mi.itemdesc, CONCAT(ms.itemname, ' ', IFNULL(ms.color,''), ' ', IFNULL(ms.size,''))) as itemdesc"),
                    DB::raw("MAX(a.unit) as unit"),
                    DB::raw('SUM(a.qty) as qty'),
                    DB::raw('AVG(a.price) as price'),
                    DB::raw('SUM(a.qty * a.price) as total_harga'),
                )
                ->where(function($query) use ($id) {
                    $query->where('a.bpbno', $id)->orWhere('a.bpbno_int', $id);
                })
                ->groupBy('a.id_item')
                ->get();

        if ($items->isEmpty()) {
            $items = $db->table('bppb as a')
                ->leftJoin('masteritem as mi', 'a.id_item', '=', 'mi.id_item')
                ->leftJoin('masterstyle as ms', 'a.id_item', '=', 'ms.id_item')
                ->select(
                    'a.id_item',
                    DB::raw("MAX(a.bppbno) as bpbno"), DB::raw("MAX(a.bppbno_int) as bpbno_int"), DB::raw("MAX(a.bppbdate) as bpbdate"),
                    DB::raw("IF(mi.goods_code IS NOT NULL AND mi.goods_code != '', mi.goods_code, ms.goods_code) as goods_code"),
                    DB::raw("IF(mi.itemdesc IS NOT NULL AND mi.itemdesc != '', mi.itemdesc, CONCAT(ms.itemname, ' ', IFNULL(ms.color,''), ' ', IFNULL(ms.size,''))) as itemdesc"),
                    DB::raw("MAX(a.unit) as unit"),
                    DB::raw('SUM(a.qty) as qty'),
                    DB::raw('AVG(a.price) as price'),
                    DB::raw('SUM(a.qty * a.price) as total_harga'),
                )
                ->where(function($query) use ($id) {
                    $query->where('a.bppbno', $id)->orWhere('a.bppbno_int', $id);
                })
                ->groupBy('a.id_item')
                ->get();
        }

        $nomorAju = $ceisaInfo->nomor_aju ?? $this->generateNomorAju($db);

        return view('export-import.dokumen-pabean.edit-bc27', [
            "page"           => "dashboard-export-import",
            "subPageGroup"   => "export-import",
            "subPage"        => "dokumen-pabean-list",
            "containerFluid" => true,
            "header"         => $header,
            "ceisaInfo"      => $ceisaInfo,
            "dataDetail"     => $dataDetail,
            "items"          => $items,
            "nomorAju"       => $nomorAju,
            "kantorList"     => $this->getKantorList()
        ]);
    }

    private function generateNomorAju($db)
    {
        $currentYear = date('Y');
        $today       = date('Ymd');
        $prefix      = '000027NIW345';

        $lastCeisa = $db->table('bpb_ceisa')
                        ->where('nomor_aju', 'like', $prefix . $currentYear . '%')
                        ->where(function($q) {
                            $q->where('jenis_bc', '2.7')->orWhere('jenis_bc', '27');
                        })
                        ->orderBy('nomor_aju', 'desc')
                        ->first();

        $localSeq = 0;
        if ($lastCeisa && $lastCeisa->nomor_aju && strlen($lastCeisa->nomor_aju) === 26) {
            $localSeq = (int) substr($lastCeisa->nomor_aju, -6);
        }

        $ceisaSeq = $this->ceisaService->getLastSequenceFromCeisa($prefix . $currentYear, '27');

        $maxSeq  = max($localSeq, $ceisaSeq);
        $nextSeq = str_pad($maxSeq + 1, 6, '0', STR_PAD_LEFT);

        return $prefix . $today . $nextSeq;
    }

    public function updateDraft($id, Request $request)
    {
        DB::connection('mysql_sb')->beginTransaction();

        try {
            $dokumenInput = $request->input('dok', []);
            $dokumenList = array_values(array_filter($dokumenInput, function($dok) {
                return !empty($dok['kode']) || !empty($dok['nomor']);
            }));

            $kontainerInput = $request->input('kontainer', []);
            $kontainerList = array_values(array_filter($kontainerInput, function($kont) {
                return !empty($kont['nomorKontainer']);
            }));

            $kemasanInput = $request->input('kemasan', []);
            $kemasanList = array_values(array_filter($kemasanInput, function($kem) {
                return isset($kem['jumlahKemasan']) && $kem['jumlahKemasan'] !== '';
            }));
            foreach ($kemasanList as &$k) {
                $k['jumlahKemasan'] = (float) $k['jumlahKemasan'];
            }

            $pungutan = $request->input('pungutan', []);
            if (is_array($pungutan)) {
                foreach ($pungutan as &$p) {
                    if (isset($p['nilaiPungutan'])) {
                        $p['nilaiPungutan'] = (float) $p['nilaiPungutan'];
                    }
                }
                unset($p);
            }

            $payloadJson = [
                'kodeKantor'         => $request->input('kodeKantor', '050500'),
                'kodeKantorTujuan'   => $request->input('kodeKantorTujuan', ''),
                'jenisTpb'           => $request->input('jenisTPB', '1'),
                'jenisTpbTujuan'     => $request->input('jenisTpbTujuan', ''),
                'kodeKantorBongkar'  => $request->input('kodeKantorBongkar', ''),
                'kodeTujuanTpb'      => $request->input('kodeTujuanTpb', ''),
                'kodeTujuanPengiriman' => $request->input('kodeTujuanPengiriman', '1'),
                'kodeTutupPu'        => $request->input('kodeTutupPu', ''),
                'bruto'              => (float) $request->input('bruto', 0),
                'netto'              => (float) $request->input('netto', 0),
                'hargaPenyerahan'    => (float) $request->input('hargaPenyerahan', 0),
                'cif'                => (float) $request->input('cif', 0),
                'fob'                => (float) $request->input('fob', 0),
                'asuransi'           => (float) $request->input('asuransi', 0),
                'kodeAsuransi'       => $request->input('kodeAsuransi', 'LN'),
                'freight'            => (float) $request->input('freight', 0),
                'biayaTambahan'      => (float) $request->input('biayaTambahan', 0),
                'biayaPengurang'     => (float) $request->input('biayaPengurang', 0),
                'kodeKenaPajak'      => $request->input('kodeKenaPajak', '1'),
                'ndpbm'              => (float) $request->input('ndpbm', 0) <= 0 && $request->input('kodeValuta', 'IDR') === 'IDR' ? 1 : (float) $request->input('ndpbm', 0),
                'nilaiBarang'        => (float) $request->input('nilaiBarang', 0),
                'nilaiJasa'          => (float) $request->input('nilaiJasa', 0),
                'nilaiUangMuka'      => (float) $request->input('nilaiUangMuka', $request->input('uangMuka', 0)),
                'uangMuka'           => (float) $request->input('nilaiUangMuka', $request->input('uangMuka', 0)),
                'diskon'             => (float) $request->input('diskon', 0),
                'dasarPengenaanPajak'=> (float) $request->input('dasarPengenaanPajak', 0),
                'nilaiPabean'        => (float) $request->input('nilaiPabean', 0),
                'tarifPPN'           => (float) $request->input('tarifPPN', 0),
                'nilaiPPN'           => (float) $request->input('nilaiPPN', 0),
                'tarifPPnBM'         => (float) $request->input('tarifPPnBM', 0),
                'nilaiPPnBM'         => (float) $request->input('nilaiPPnBM', 0),
                'kodeIncoterm'       => $request->input('kodeIncoterm', ''),
                'kodeValuta'         => $request->input('kodeValuta', 'IDR'),
                'kodePelMuat'        => $request->input('kodePelMuat', ''),
                'kodePelBongkar'     => $request->input('kodePelBongkar', ''),
                'kodePelTransit'     => $request->input('kodePelTransit', ''),
                'kodeTps'            => $request->input('kodeTps', ''),
                'jumlahKontainer'    => (int) $request->input('jumlahKontainer', 0),
                'nomorBc11'          => $request->input('nomorBc11', ''),
                'posBc11'            => $request->input('posBc11', ''),
                'subposBc11'         => $request->input('subposBc11', ''),
                'subsubposBc11'      => $request->input('subsubposBc11', ''),
                'tanggalBc11'        => $request->input('tanggalBc11', ''),
                'kodeBc11'           => $request->input('kodeBc11', ''),
                'nik'                => $request->input('nik', ''),
                'seri'               => (int) $request->input('seri', 0),
                'namaTtd'            => $request->input('namaTtd', ''),
                'jabatanTtd'         => $request->input('jabatanTtd', ''),
                'kotaTtd'            => $request->input('kotaTtd', ''),
                'tanggalTtd'         => $request->input('tanggalTtd', date('Y-m-d')),
                'tanggalTiba'        => $request->input('tanggalTiba', ''),
                'entitas'            => $request->input('entitas', []),
                'pengangkut'         => $request->input('pengangkut', []),
                'pungutan'           => $pungutan,
                'dok'                => $dokumenList,
                'kontainer'          => $kontainerList,
                'kemasan'            => $kemasanList,
                'barang'             => array_map(function($brg) use ($request) {
                    $ndpbm = (float) $request->input('ndpbm', 0) <= 0 && $request->input('kodeValuta', 'IDR') === 'IDR' ? 1 : (float) $request->input('ndpbm', 0);
                    $brg['cif'] = (float) ($brg['cif'] ?? 0);
                    $brg['cifRupiah'] = (float) ($brg['cifRupiah'] ?? 0);
                    if ($brg['cifRupiah'] <= 0 && $ndpbm > 0) {
                        $brg['cifRupiah'] = $brg['cif'] * $ndpbm;
                    }
                    $brg['fob'] = (float) ($brg['fob'] ?? 0);
                    $brg['asuransi'] = (float) ($brg['asuransi'] ?? 0);
                    $brg['freight'] = (float) ($brg['freight'] ?? 0);
                    $brg['hargaSatuan'] = (float) ($brg['hargaSatuan'] ?? 0);
                    $brg['netto'] = (float) ($brg['netto'] ?? 0);
                    $brg['jumlahSatuan'] = (float) ($brg['jumlahSatuan'] ?? 0);
                    $brg['jumlahKemasan'] = (float) ($brg['jumlahKemasan'] ?? 0);
                    $brg['biayaTambahan'] = (float) ($brg['biayaTambahan'] ?? 0);
                    $brg['hargaPenyerahan'] = (float) ($brg['hargaPenyerahan'] ?? 0);
                    $brg['hargaPabrikasi'] = (float) ($brg['hargaPabrikasi'] ?? 0);
                    $brg['nilaiPenggantian'] = (float) ($brg['nilaiPenggantian'] ?? 0);
                    $brg['kategoriBarang'] = $brg['kategoriBarang'] ?? $brg['kodeKategoriBarang'] ?? '';
                    $brg['kodeKategoriBarang'] = $brg['kategoriBarang'];
                    return $brg;
                }, $request->input('barang', [])),
                'bc11Nomor'         => $request->input('nomorBc11', ''),
                'bc11Tanggal'       => $request->input('tanggalBc11', ''),
                'bc11Pos'          => $request->input('posBc11', ''),
                'bc11Subpos'       => $request->input('subposBc11', ''),
                'bc11Subsubpos'    => $request->input('subsubposBc11', ''),
                'bc11KodeBc'       => $request->input('kodeBc11', ''),
            ];

            DB::connection('mysql_sb')->table('bpb_ceisa')->updateOrInsert(
                ['bpbno' => $id],
                [
                    'tanggal_aju'  => $request->input('tanggalAju', date('Y-m-d')),
                    'nomor_aju'    => $request->input('nomorAju'),
                    'payload_json' => json_encode($payloadJson),
                    'jenis_bc'     => '2.7',
                    'updated_at'   => date('Y-m-d H:i:s'),
                    'bpbno_int'    => $request->input('bpbno_int') ?? null
                ]
            );

            DB::connection('mysql_sb')->commit();

            return redirect()->route('dokumen-pabean-index')
                             ->with('success', 'Data draft BC 2.7 berhasil disimpan!');

        } catch (\Exception $e) {
            DB::connection('mysql_sb')->rollBack();
            \Illuminate\Support\Facades\Log::error('Error Update Draft BC 2.7: ' . $e->getMessage());

            return redirect()->back()
                             ->withInput()
                             ->with('error', 'Terjadi kesalahan saat menyimpan: ' . $e->getMessage());
        }
    }

    public function sendCeisa($id, Request $request)
    {
        $db = DB::connection('mysql_sb');

        try {
            $header = $db->table('bpb as a')
                ->join('mastersupplier as ms', 'a.id_supplier', '=', 'ms.id_supplier')
                ->where(function($query) use ($id) {
                    $query->where('a.bpbno', $id)->orWhere('a.bpbno_int', $id);
                })
                ->first();

            if (!$header) {
                $header = $db->table('bppb as a')
                    ->join('mastersupplier as ms', 'a.id_supplier', '=', 'ms.id_supplier')
                    ->where(function($query) use ($id) {
                        $query->where('a.bppbno', $id)->orWhere('a.bppbno_int', $id);
                    })
                    ->first();
            }

            if (!$header) throw new \Exception("Data transaksi tidak ditemukan!");

            $ceisaInfo = $db->table('bpb_ceisa')->where('bpbno', $id)->first();
            if (!$ceisaInfo) throw new \Exception("Data CEISA belum disiapkan. Simpan draft terlebih dahulu.");

            $draft     = json_decode($ceisaInfo->payload_json ?? '{}', true);
            $nomorAju  = $ceisaInfo->nomor_aju ?? '';
            $tanggalAju = date('Y-m-d');

            $payloadDokumen = [];
            $seriDok = 1;
            foreach (($draft['dok'] ?? []) as $d) {
                if (!empty($d['kode']) && !empty($d['nomor'])) {
                    $payloadDokumen[] = [
                        "idDokumen"      => !empty($d['idDokumen']) ? strval($d['idDokumen']) : "",
                        "kodeDokumen"    => trim(explode(' - ', $d['kode'])[0]),
                        "nomorDokumen"   => $d['nomor'],
                        "seriDokumen"    => $seriDok++,
                        "tanggalDokumen" => !empty($d['tgl']) ? $d['tgl'] : date('Y-m-d')
                    ];
                }
            }

            $hasInvoice = false;
            $hasTransport = false;
            foreach ($payloadDokumen as $dok) {
                $kodeStr = explode(' - ', $dok['kodeDokumen'])[0];
                $kodeStr = trim($kodeStr);

                if ($kodeStr === '380') $hasInvoice = true;
                if (in_array($kodeStr, ['705', '740', '704', '741', '640'])) $hasTransport = true;
            }

            if (!$hasInvoice || !$hasTransport) {
                throw new \Exception("Validasi Gagal: Dokumen BC 2.7 wajib melampirkan INVOICE (380) dan B/L / AWB / Delivery Order (705/740/640). Silakan tambahkan di tab Dokumen Pendukung.");
            }

            $payloadKontainer = [];
            $seriKont = 1;
            foreach (($draft['kontainer'] ?? []) as $k) {
                if (!empty($k['nomorKontainer'])) {
                    $jenisKont = strval($k['kodeJenisKontainer'] ?? '');
                    if (!in_array($jenisKont, ["4", "7", "8"])) $jenisKont = "4";

                    $tipeKont = strval($k['kodeTipeKontainer'] ?? '');
                    if (!in_array($tipeKont, ["1", "2", "3", "4", "5", "6", "7", "8", "99"])) $tipeKont = "1";

                    $ukuranKont = strval($k['kodeUkuranKontainer'] ?? '');
                    if (!in_array($ukuranKont, ["20", "40", "45", "60"])) $ukuranKont = "20";

                    $payloadKontainer[] = [
                        "seriKontainer"       => $seriKont++,
                        "nomorKontainer"      => strtoupper(trim($k['nomorKontainer'])),
                        "kodeUkuranKontainer" => $ukuranKont,
                        "kodeJenisKontainer"  => $jenisKont,
                        "kodeTipeKontainer"   => $tipeKont
                    ];
                }
            }

            $payloadKemasan = [];
            $seriKem = 1;
            foreach (($draft['kemasan'] ?? []) as $k) {
                $payloadKemasan[] = [
                    "jumlahKemasan"    => (int) ($k['jumlahKemasan'] ?? 0),
                    "kodeJenisKemasan" => !empty($k['kodeJenisKemasan']) ? strval($k['kodeJenisKemasan']) : "CT",
                    "merkKemasan"      => !empty($k['merkKemasan']) ? strval($k['merkKemasan']) : "-",
                    "seriKemasan"      => $seriKem++
                ];
            }
            if (empty($payloadKemasan)) {
                $payloadKemasan[] = ["jumlahKemasan" => 0, "kodeJenisKemasan" => "CT", "merkKemasan" => "-", "seriKemasan" => 1];
            }

            $totalHargaPenyerahan = 0;
            $totalCif = 0;
            $totalFob = 0;
            $totalFreight = 0;
            $totalAsuransi = 0;
            $totalDiskon = 0;
            $arrayBarang = [];

            if (count($draft['barang'] ?? []) === 1) {
                if (empty($draft['barang'][0]['cif']) && !empty($draft['cif'])) $draft['barang'][0]['cif'] = $draft['cif'];
                if (empty($draft['barang'][0]['fob']) && !empty($draft['fob'])) $draft['barang'][0]['fob'] = $draft['fob'];
                if (empty($draft['barang'][0]['freight']) && !empty($draft['freight'])) $draft['barang'][0]['freight'] = $draft['freight'];
                if (empty($draft['barang'][0]['asuransi']) && !empty($draft['asuransi'])) $draft['barang'][0]['asuransi'] = $draft['asuransi'];
                if (empty($draft['barang'][0]['diskon']) && !empty($draft['diskon'])) $draft['barang'][0]['diskon'] = $draft['diskon'];
            }

            foreach (($draft['barang'] ?? []) as $index => $brg) {
                $cifItem = (float) ($brg['cif'] ?? 0);
                $nettoItem = (float) ($brg['netto'] ?? 0);

                if ($cifItem <= 0 || $nettoItem <= 0) {
                    $itemNum = $index + 1;
                    throw new \Exception("Validasi Gagal: Harga CIF dan Berat Bersih (Netto) pada Barang ke-{$itemNum} harus lebih besar dari 0.");
                }

                $hargaPenyerahanItem = (float) ($brg['hargaPenyerahan'] ?? 0);
                $totalHargaPenyerahan += $hargaPenyerahanItem;
                $totalCif += (float) ($brg['cif'] ?? 0);
                $totalFob += (float) ($brg['fob'] ?? 0);
                $totalFreight += (float) ($brg['freight'] ?? 0);
                $totalAsuransi += (float) ($brg['asuransi'] ?? 0);
                $totalDiskon += (float) ($brg['diskon'] ?? 0);

                $bahanBakuList = [];
                if (!empty($brg['bahanBaku']) && is_array($brg['bahanBaku'])) {
                    foreach ($brg['bahanBaku'] as $bbIndex => $bb) {
                        $bbTarif = [];
                        if (!empty($bb['bahanBakuTarif']) && is_array($bb['bahanBakuTarif'])) {
                            foreach ($bb['bahanBakuTarif'] as $bbt) {
                                $asalBb = strval($bbt['kodeAsalBahanBaku'] ?? "0");
                                if (!in_array($asalBb, ["0", "1"])) $asalBb = "0";

                                $jenisTarif = strval($bbt['kodeJenisTarif'] ?? "1");
                                if (!in_array($jenisTarif, ["1", "2"])) $jenisTarif = "1";

                                $bbTarif[] = [
                                    "seriBahanBaku"      => (int) ($bbt['seriBahanBaku'] ?? ($bbIndex + 1)),
                                    "kodeJenisPungutan"  => strval($bbt['kodeJenisPungutan'] ?? "BM"),
                                    "kodeAsalBahanBaku"  => $asalBb,
                                    "kodeFasilitasTarif" => strval($bbt['kodeFasilitasTarif'] ?? "3"),
                                    "nilaiBayar"         => (float) ($bbt['nilaiBayar'] ?? 0),
                                    "nilaiFasilitas"     => (float) ($bbt['nilaiFasilitas'] ?? 0),
                                    "nilaiSudahDilunasi" => (float) ($bbt['nilaiSudahDilunasi'] ?? 0),
                                    "tarif"              => (float) ($bbt['tarif'] ?? 0),
                                    "tarifFasilitas"     => (float) ($bbt['tarifFasilitas'] ?? 100),
                                    "jumlahSatuan"       => (float) ($bbt['jumlahSatuan'] ?? $bb['jumlahSatuan'] ?? 0),
                                    "kodeJenisTarif"     => $jenisTarif,
                                    "jumlahKemasan"      => (int) ($bbt['jumlahKemasan'] ?? 0),
                                ];
                            }
                        }
                        if (empty($bbTarif)) {
                            $bbTarif[] = [
                                "seriBahanBaku"      => (int) ($bbIndex + 1),
                                "kodeJenisPungutan"  => "BM",
                                "kodeAsalBahanBaku"  => "0",
                                "kodeFasilitasTarif" => "3",
                                "nilaiBayar"         => 0,
                                "nilaiFasilitas"     => 0,
                                "nilaiSudahDilunasi" => 0,
                                "tarif"              => 0,
                                "tarifFasilitas"     => 100,
                                "jumlahSatuan"       => (float) ($bb['jumlahSatuan'] ?? $brg['jumlahSatuan'] ?? 0),
                                "kodeJenisTarif"     => "1",
                                "jumlahKemasan"      => (int) ($bb['jumlahKemasan'] ?? $brg['jumlahKemasan'] ?? 0),
                            ];
                        }

                        $asalBbMain = strval($bb['kodeAsalBahanBaku'] ?? "0");
                        if (!in_array($asalBbMain, ["0", "1"])) $asalBbMain = "0";

                        $nomorAjuAsal = strval($bb['nomorAjuDokAsal'] ?? "");
                        if (!preg_match('/^[A-Za-z0-9]{26}$/', $nomorAjuAsal)) {
                            $nomorAjuAsal = "00000000000000000000000000";
                        }

                        $bahanBakuList[] = [
                            "cif"                   => (float) ($bb['cif'] ?? 0),
                            "cifRupiah"             => (float) ($bb['cifRupiah'] ?? 0),
                            "hargaPenyerahan"       => (float) ($bb['hargaPenyerahan'] ?? 0),
                            "hargaPerolehan"        => (float) ($bb['hargaPerolehan'] ?? 0),
                            "jumlahSatuan"          => (float) ($bb['jumlahSatuan'] ?? $brg['jumlahSatuan'] ?? 0),
                            "kodeSatuanBarang"      => strval($bb['kodeSatuanBarang'] ?? $brg['kodeSatuanBarang'] ?? ""),
                            "kodeAsalBahanBaku"     => $asalBbMain,
                            "kodeBarang"            => strval($bb['kodeBarang'] ?? $brg['kodeBarang'] ?? ""),
                            "kodeDokAsal"           => strval($bb['kodeDokAsal'] ?? "23"),
                            "kodeKantor"            => strval($bb['kodeKantor'] ?? $draft['kodeKantor'] ?? "050500"),
                            "merkBarang"            => strval($bb['merkBarang'] ?? $brg['merk'] ?? "-"),
                            "ndpbm"                 => (float) ($bb['ndpbm'] ?? $bb['ndbpm'] ?? $draft['ndpbm'] ?? 0),
                            "netto"                 => (float) ($bb['netto'] ?? $brg['netto'] ?? 0),
                            "nomorAjuDokAsal"       => $nomorAjuAsal,
                            "nomorDaftarDokAsal"    => strval($bb['nomorDaftarDokAsal'] ?? ""),
                            "posTarif"              => strval($bb['posTarif'] ?? $brg['posTarif'] ?? ""),
                            "seriBahanBaku"         => (int) ($bb['seriBahanBaku'] ?? ($bbIndex + 1)),
                            "seriBarang"            => (int) ($bb['seriBarang'] ?? ($index + 1)),
                            "seriBarangDokAsal"     => (int) ($bb['seriBarangDokAsal'] ?? 1),
                            "seriIjin"              => (int) ($bb['seriIjin'] ?? 1),
                            "spesifikasiLainBarang" => strval($bb['spesifikasiLainBarang'] ?? $brg['spesifikasiLain'] ?? "-"),
                            "tanggalDaftarDokAsal"  => strval($bb['tanggalDaftarDokAsal'] ?? date('Y-m-d')),
                            "tipeBarang"            => strval($bb['tipeBarang'] ?? $brg['tipe'] ?? "-"),
                            "ukuranBarang"          => strval($bb['ukuranBarang'] ?? $brg['ukuran'] ?? "-"),
                            "uraianBarang"          => strval($bb['uraianBarang'] ?? $brg['uraian'] ?? "-"),
                            "nilaiJasa"             => (float) ($bb['nilaiJasa'] ?? 0),
                            "bahanBakuTarif"        => $bbTarif,
                        ];
                    }
                }

                if (empty($bahanBakuList)) {
                    $asalBbFallback = strval($brg['kodeAsalBahanBaku'] ?? "0");
                    if (!in_array($asalBbFallback, ["0", "1"])) $asalBbFallback = "0";

                    $bahanBakuList[] = [
                        "cif"                   => (float) ($brg['cif'] ?? 0),
                        "cifRupiah"             => (float) ($brg['cifRupiah'] ?? 0),
                        "hargaPenyerahan"       => (float) ($hargaPenyerahanItem),
                        "hargaPerolehan"        => (float) ($brg['hargaPerolehan'] ?? 0),
                        "jumlahSatuan"          => (float) ($brg['jumlahSatuan'] ?? 0),
                        "kodeSatuanBarang"      => strval($brg['kodeSatuanBarang'] ?? ""),
                        "kodeAsalBahanBaku"     => $asalBbFallback,
                        "kodeBarang"            => strval($brg['kodeBarang'] ?? ""),
                        "kodeDokAsal"           => "23",
                        "kodeKantor"            => strval($draft['kodeKantor'] ?? "050500"),
                        "merkBarang"            => strval($brg['merk'] ?? "-"),
                        "ndpbm"                 => (float) ($brg['ndpbm'] ?? $draft['ndpbm'] ?? 0),
                        "netto"                 => (float) ($brg['netto'] ?? 0),
                        "nomorAjuDokAsal"       => "00000000000000000000000000",
                        "nomorDaftarDokAsal"    => "",
                        "posTarif"              => strval($brg['posTarif'] ?? ""),
                        "seriBahanBaku"         => 1,
                        "seriBarang"            => (int) ($index + 1),
                        "seriBarangDokAsal"     => 1,
                        "seriIjin"              => 1,
                        "spesifikasiLainBarang" => strval($brg['spesifikasiLain'] ?? "-"),
                        "tanggalDaftarDokAsal"  => date('Y-m-d'),
                        "tipeBarang"            => strval($brg['tipe'] ?? "-"),
                        "ukuranBarang"          => strval($brg['ukuran'] ?? "-"),
                        "uraianBarang"          => strval($brg['uraian'] ?? "-"),
                        "nilaiJasa"             => (float) ($brg['nilaiJasa'] ?? 0),
                        "bahanBakuTarif"        => [[
                            "seriBahanBaku"      => 1,
                            "kodeJenisPungutan"  => "BM",
                            "kodeAsalBahanBaku"  => "0",
                            "kodeFasilitasTarif" => "3",
                            "nilaiBayar"         => 0,
                            "nilaiFasilitas"     => 0,
                            "nilaiSudahDilunasi" => 0,
                            "tarif"              => 0,
                            "tarifFasilitas"     => 100,
                            "jumlahSatuan"       => (float) ($brg['jumlahSatuan'] ?? 0),
                            "kodeJenisTarif"     => "1",
                            "jumlahKemasan"      => (int) ($brg['jumlahKemasan'] ?? 0),
                        ]],
                    ];
                }

                $arrayBarang[] = [
                    "cif"               => (float) ($brg['cif'] ?? 0),
                    "cifRupiah"         => (float) ($brg['cifRupiah'] ?? 0),
                    "hargaEkspor"       => (float) ($brg['hargaEkspor'] ?? 0),
                    "hargaPenyerahan"   => $hargaPenyerahanItem,
                    "hargaPerolehan"    => (float) ($brg['hargaPerolehan'] ?? 0),
                    "isiPerKemasan"     => (float) ($brg['isiPerKemasan'] ?? 0),
                    "jumlahSatuan"      => (float) ($brg['jumlahSatuan'] ?? 0),
                    "kodeBarang"        => strval($brg['kodeBarang'] ?? ''),
                    "kodeDokumen"       => "27",
                    "kodeSatuanBarang"  => strval($brg['kodeSatuanBarang'] ?? ""),
                    "merk"              => !empty($brg['merk']) ? strval($brg['merk']) : "-",
                    "ndpbm"             => (float) ($brg['ndpbm'] ?? 0),
                    "netto"             => (float) ($brg['netto'] ?? 0),
                    "nilaiBarang"       => (float) ($brg['nilaiBarang'] ?? 0),
                    "nilaiJasa"         => (float) ($brg['nilaiJasa'] ?? 0),
                    "posTarif"          => strval($brg['posTarif'] ?? ""),
                    "seriBarang"        => strval($brg['seriBarang'] ?? ($index + 1)),
                    "spesifikasiLain"   => !empty($brg['spesifikasiLain']) ? strval($brg['spesifikasiLain']) : "-",
                    "tipe"              => !empty($brg['tipe']) ? strval($brg['tipe']) : "-",
                    "uangMuka"          => (float) ($brg['uangMuka'] ?? $brg['nilaiUangMuka'] ?? 0),
                    "ukuran"            => !empty($brg['ukuran']) ? strval($brg['ukuran']) : "-",
                    "uraian"            => !empty($brg['uraian']) ? strval($brg['uraian']) : "-",
                    "bahanBaku"         => $bahanBakuList,
                ];
            }

            $entitasDraft = $draft['entitas'] ?? [];
            $jenisId3 = strval($entitasDraft[3]['kodeJenisIdentitas'] ?? "5");
            if (!in_array($jenisId3, ["2", "3", "4", "5", "6"])) $jenisId3 = "5";

            $jenisId7 = strval($entitasDraft[7]['kodeJenisIdentitas'] ?? "5");
            if (!in_array($jenisId7, ["2", "3", "4", "5", "6"])) $jenisId7 = "5";

            $jenisId8 = strval($entitasDraft[8]['kodeJenisIdentitas'] ?? "5");
            if (!in_array($jenisId8, ["2", "3", "4", "5", "6"])) $jenisId8 = "5";

            $payloadEntitas = [
                [
                    "alamatEntitas"      => !empty($entitasDraft[3]['alamatEntitas']) ? strval($entitasDraft[3]['alamatEntitas']) : "-",
                    "kodeEntitas"        => "3",
                    "kodeJenisIdentitas" => $jenisId3,
                    "namaEntitas"        => !empty($entitasDraft[3]['namaEntitas']) ? strval($entitasDraft[3]['namaEntitas']) : "-",
                    "nibEntitas"         => !empty($entitasDraft[3]['nibEntitas']) ? strval($entitasDraft[3]['nibEntitas']) : "",
                    "nomorIdentitas"     => !empty($entitasDraft[3]['nomorIdentitas']) ? strval($entitasDraft[3]['nomorIdentitas']) : "-",
                    "nomorIjinEntitas"   => !empty($entitasDraft[3]['nomorIjinEntitas']) ? strval($entitasDraft[3]['nomorIjinEntitas']) : "-",
                    "seriEntitas"        => 1,
                    "tanggalIjinEntitas" => !empty($entitasDraft[3]['tanggalIjinEntitas']) ? strval($entitasDraft[3]['tanggalIjinEntitas']) : date('Y-m-d'),
                ],
                [
                    "alamatEntitas"      => !empty($entitasDraft[7]['alamatEntitas']) ? strval($entitasDraft[7]['alamatEntitas']) : "-",
                    "kodeEntitas"        => "7",
                    "kodeJenisApi"       => !empty($entitasDraft[7]['kodeJenisApi']) ? strval($entitasDraft[7]['kodeJenisApi']) : "02",
                    "kodeJenisIdentitas" => $jenisId7,
                    "kodeStatus"         => !empty($entitasDraft[7]['kodeStatus']) ? strval($entitasDraft[7]['kodeStatus']) : "5",
                    "namaEntitas"        => !empty($entitasDraft[7]['namaEntitas']) ? strval($entitasDraft[7]['namaEntitas']) : "-",
                    "nibEntitas"         => !empty($entitasDraft[7]['nibEntitas']) ? strval($entitasDraft[7]['nibEntitas']) : "",
                    "nomorIdentitas"     => !empty($entitasDraft[7]['nomorIdentitas']) ? strval($entitasDraft[7]['nomorIdentitas']) : "-",
                    "nomorIjinEntitas"   => !empty($entitasDraft[7]['nomorIjinEntitas']) ? strval($entitasDraft[7]['nomorIjinEntitas']) : "-",
                    "seriEntitas"        => 2,
                    "tanggalIjinEntitas" => !empty($entitasDraft[7]['tanggalIjinEntitas']) ? strval($entitasDraft[7]['tanggalIjinEntitas']) : date('Y-m-d'),
                ],
                [
                    "alamatEntitas"      => !empty($entitasDraft[8]['alamatEntitas']) ? strval($entitasDraft[8]['alamatEntitas']) : (!empty($header->alamat_supplier) ? strval($header->alamat_supplier) : "-"),
                    "kodeEntitas"        => "8",
                    "kodeJenisApi"       => !empty($entitasDraft[8]['kodeJenisApi']) ? strval($entitasDraft[8]['kodeJenisApi']) : "02",
                    "kodeJenisIdentitas" => $jenisId8,
                    "kodeStatus"         => !empty($entitasDraft[8]['kodeStatus']) ? strval($entitasDraft[8]['kodeStatus']) : "5",
                    "namaEntitas"        => !empty($entitasDraft[8]['namaEntitas']) ? strval($entitasDraft[8]['namaEntitas']) : (!empty($header->supplier) ? strval($header->supplier) : "-"),
                    "nibEntitas"         => !empty($entitasDraft[8]['nibEntitas']) ? strval($entitasDraft[8]['nibEntitas']) : "",
                    "nomorIdentitas"     => !empty($entitasDraft[8]['nomorIdentitas']) ? strval($entitasDraft[8]['nomorIdentitas']) : "-",
                    "nomorIjinEntitas"   => !empty($entitasDraft[8]['nomorIjinEntitas']) ? strval($entitasDraft[8]['nomorIjinEntitas']) : "-",
                    "seriEntitas"        => 3,
                    "tanggalIjinEntitas" => !empty($entitasDraft[8]['tanggalIjinEntitas']) ? strval($entitasDraft[8]['tanggalIjinEntitas']) : date('Y-m-d'),
                ],
            ];

            $payloadPungutan = [];
            if (!empty($draft['pungutan']) && is_array($draft['pungutan'])) {
                foreach ($draft['pungutan'] as $p) {
                    if (isset($p['kodeJenisPungutan'])) {
                        $payloadPungutan[] = [
                            "idPungutan"         => !empty($p['idPungutan']) ? strval($p['idPungutan']) : "11",
                            "kodeFasilitasTarif" => $p['kodeFasilitasTarif'] ?? "3",
                            "kodeJenisPungutan"  => $p['kodeJenisPungutan'],
                            "nilaiPungutan"      => round((float) ($p['nilaiPungutan'] ?? 0), 2)
                        ];
                    }
                }
            }

            $nilaiBarangHeader = (float) ($draft['nilaiBarang'] ?? 0);
            if ($nilaiBarangHeader <= 0 && $totalHargaPenyerahan > 0) {
                $nilaiBarangHeader = $totalHargaPenyerahan;
            } elseif ($nilaiBarangHeader <= 0 && $totalCif > 0) {
                $nilaiBarangHeader = $totalCif;
            }

            $jenisTpb = !empty($draft['jenisTpb']) ? strval($draft['jenisTpb']) : "1";
            if (!in_array($jenisTpb, ["1", "2", "3", "4", "5", "6", "7", "8"])) $jenisTpb = "1";

            $tujuanPengiriman = !empty($draft['kodeTujuanPengiriman']) ? strval($draft['kodeTujuanPengiriman']) : "1";
            if (!in_array($tujuanPengiriman, ["1", "2", "3", "4", "5"])) $tujuanPengiriman = "1";

            $payload = [
                "idPlatform"       => config('ceisa.id_platform_dev', ''),
                "asalData"         => "S",
                "asuransi"         => $totalAsuransi > 0 ? $totalAsuransi : (float) ($draft['asuransi'] ?? 0),
                "biayaPengurang"   => (float) ($draft['biayaPengurang'] ?? 0),
                "biayaTambahan"    => (float) ($draft['biayaTambahan'] ?? 0),
                "bruto"            => (float) ($draft['bruto'] ?? 0),
                "cif"              => $totalCif > 0 ? $totalCif : (float) ($draft['cif'] ?? 0),
                "dasarPengenaanPajak" => (float) ($draft['dasarPengenaanPajak'] ?? 0),
                "disclaimer"       => "0",
                "freight"          => $totalFreight > 0 ? $totalFreight : (float) ($draft['freight'] ?? 0),
                "hargaPenyerahan"  => (float) ($draft['hargaPenyerahan'] ?? $totalHargaPenyerahan),
                "jabatanTtd"       => !empty($draft['jabatanTtd']) ? strval($draft['jabatanTtd']) : "-",
                "jumlahKontainer"  => (int) ($draft['jumlahKontainer'] ?? count($payloadKontainer)),
                "kodeDokumen"      => "27",
                "kodeJenisTpb"     => $jenisTpb,
                "kodeKantor"       => !empty($draft['kodeKantor']) ? strval($draft['kodeKantor']) : "050500",
                "kodeKantorTujuan" => !empty($draft['kodeKantorTujuan']) ? strval($draft['kodeKantorTujuan']) : (!empty($draft['kodeKantorBongkar']) ? strval($draft['kodeKantorBongkar']) : "050500"),
                "kodeTps"          => !empty($draft['kodeTps']) ? strval($draft['kodeTps']) : "UTPK",
                "kodeTujuanPengiriman" => $tujuanPengiriman,
                "kodeTujuanTpb"    => !empty($draft['kodeTujuanTpb']) ? strval($draft['kodeTujuanTpb']) : "1",
                "kodeValuta"       => !empty($draft['kodeValuta']) ? strval($draft['kodeValuta']) : "USD",
                "kotaTtd"          => !empty($draft['kotaTtd']) ? strval($draft['kotaTtd']) : "kota_ttd",
                "namaTtd"          => !empty($draft['namaTtd']) ? strval($draft['namaTtd']) : "nama_ttd",
                "ndpbm"            => (float) ($draft['ndpbm'] ?? 0),
                "netto"            => (float) ($draft['netto'] ?? 0),
                "nik"              => !empty($draft['nik']) ? strval($draft['nik']) : (!empty($entitasDraft[3]['nomorIdentitas']) ? strval($entitasDraft[3]['nomorIdentitas']) : "0000000000000000"),
                "nilaiBarang"      => $nilaiBarangHeader,
                "nilaiJasa"        => (float) ($draft['nilaiJasa'] ?? 0),
                "nomorAju"         => $nomorAju,
                "seri"             => (int) ($draft['seri'] ?? 0),
                "tanggalAju"       => $tanggalAju,
                "tanggalTtd"       => !empty($draft['tanggalTtd']) ? strval($draft['tanggalTtd']) : date('Y-m-d'),
                "uangMuka"         => (float) ($draft['uangMuka'] ?? $draft['nilaiUangMuka'] ?? 0),
                "vd"               => (float) ($draft['vd'] ?? 0),
                "ppnPajak"         => (float) ($draft['nilaiPPN'] ?? 0),
                "ppnbmPajak"       => (float) ($draft['nilaiPPnBM'] ?? 0),
                "tarifPpnPajak"    => (float) ($draft['tarifPPN'] ?? 0),
                "tarifPpnbmPajak"  => (float) ($draft['tarifPPnBM'] ?? 0),
                "entitas"          => $payloadEntitas,
                "dokumen"          => $payloadDokumen,
                "pengangkut"       => [[
                    "namaPengangkut"  => !empty($draft['pengangkut']['nama']) ? strval($draft['pengangkut']['nama']) : "-",
                    "nomorPengangkut" => !empty($draft['pengangkut']['nomor']) ? strval($draft['pengangkut']['nomor']) : "-",
                    "seriPengangkut"  => "1"
                ]],
                "kontainer"        => $payloadKontainer,
                "kemasan"          => $payloadKemasan,
                "pungutan"         => $payloadPungutan,
                "barang"           => $arrayBarang,
            ];

            $responseCeisa = $this->ceisaService->kirimDokumenBc27($payload);

            if ($responseCeisa['successful']) {
                $db->table('bpb_ceisa')->where('bpbno', $id)->update([
                    'nomor_aju'   => $nomorAju,
                    'tanggal_aju' => $tanggalAju,
                    'jenis_bc'    => '2.7',
                    'status'      => 1,
                    'updated_at'  => Carbon::now()
                ]);

                return response()->json([
                    'status'         => 200,
                    'message'        => 'Dokumen BC 2.7 berhasil dikirim ke CEISA!',
                    'data_payload'   => $payload,
                    'ceisa_response' => $responseCeisa['body']
                ]);
            } else {
                return response()->json([
                    'status'      => $responseCeisa['status_code'],
                    'message'     => 'Gagal mengirim ke CEISA.',
                    'ceisa_error' => $responseCeisa['body']
                ], $responseCeisa['status_code']);
            }

        } catch (\Exception $e) {
            return response()->json([
                'status'  => 500,
                'message' => $e->getMessage()
            ], 500);
        }
    }



     private function getKantorList()
    {
        return [
            '000000' => 'DJBC',
            '001000' => 'SEKRETARIAT',
            '001100' => 'TP BIDANG I',
            '001200' => 'TP BIDANG II',
            '001300' => 'TP BIDANG III',
            '001400' => 'DIREKTORAT KI',
            '001500' => 'DIREKTORAT KBP',
            '002000' => 'DIREKTORAT TEKNIS',
            '003000' => 'DIREKTORAT FASILITAS',
            '004000' => 'DIREKTORAT CUKAI',
            '005000' => 'DIREKTORAT P2',
            '006000' => 'DIREKTORAT AUDIT',
            '007000' => 'DIREKTORAT KIAL',
            '008000' => 'DIREKTORAT PPS',
            '009000' => 'DIREKTORAT IKC',
            '010000' => 'KANWIL SUMUT',
            '010100' => 'KPPBC KUALANAMU',
            '010700' => 'KPPBC ELAWAN',
            '010800' => 'KPPBC MEDAN',
            '010900' => 'KPPBC PANGKALAN SUSU',
            '011000' => 'KPPBC PEMATANGSIANTAR',
            '011100' => 'KPPBC TELUK NIBUNG',
            '011200' => 'KPPBC KUALA TANJUNG',
            '011300' => 'KPPBC SIBOLGA',
            '011500' => 'KPPBC TELUK BAYUR',
            '011600' => 'BLBC KELAS II MEDAN',
            '020000' => 'KANWIL KHUSUS KEPRI',
            '020100' => 'KPPBC TBK',
            '020200' => 'KPPBC SAMBU BELAKANG PADANG',
            '020300' => 'KPPBC SELAT PANJANG',
            '020400' => 'KPU BATAM',
            '020500' => 'KPPBC TANJUNG PINANG',
            '020800' => 'KPPBC DABO SINGKEP',
            '020900' => 'KPPBC DUMAI',
            '021000' => 'KPPBC BAGAN SIAPIAPI',
            '021100' => 'KPPBC BENGKALIS',
            '021200' => 'KPPBC PEKANBARU',
            '021300' => 'KPPBC SIAK SRI INDRAPURA',
            '021500' => 'KPPBC TEMBILAHAN',
            '021700' => 'KPPBC TAREMPA',
            '021800' => 'PANGSAROP BATAM',
            '021900' => 'PANGSAROP TANJUNG BALAI KARIMUN',
            '030000' => 'KANWIL SUMBAGTIM',
            '030100' => 'KPPBC PALEMBANG',
            '030200' => 'KPPBC BENGKULU',
            '030300' => 'KPPBC PANGKALPINANG',
            '030500' => 'KPPBC TANJUNGPANDAN',
            '030600' => 'KPPBC JAMBI',
            '030700' => 'KPPBC BANDAR LAMPUNG',
            '040000' => 'KANWIL JAKARTA',
            '040300' => 'KPU TANJUNG PRIOK',
            '040400' => 'KPPBC JAKARTA',
            '040500' => 'BLBC KELAS I JAKARTA',
            '040600' => 'KPPBC KANTOR POS PASAR BARU',
            '040700' => 'PANGSAROP TANJUNG PRIOK',
            '050000' => 'KANWIL JABAR',
            '050100' => 'KPU SOEKARNO-HATTA',
            '050300' => 'KPPBC BOGOR',
            '050400' => 'KPPBC TMP MERAK',
            '050500' => 'KPPBC BANDUNG',
            '050600' => 'KPPBC TASIKMALAYA',
            '050700' => 'KPPBC CIREBON',
            '050800' => 'KPPBC PURWAKARTA',
            '050900' => 'KPPBC BEKASI',
            '051000' => 'KPPBC CIKARANG',
            '060000' => 'KANWIL JATENG DIY',
            '060100' => 'KPPBC TMP TANJUNG EMAS',
            '060200' => 'KPPBC PEKALONGAN',
            '060300' => 'KPPBC TMC KUDUS',
            '060400' => 'KPPBC CILACAP',
            '060600' => 'KPPBC SURAKARTA',
            '060700' => 'KPPBC YOGYAKARTA',
            '060800' => 'KPPBC SEMARANG',
            '061000' => 'KPPBC TEGAL',
            '061100' => 'KPPBC MAGELANG',
            '062000' => 'KPPBC PURWOKERTO',
            '070000' => 'KANWIL JATIM I',
            '070100' => 'KPPBC TMP TANJUNG PERAK',
            '070200' => 'KPPBC MADURA',
            '070300' => 'KPPBC GRESIK',
            '070400' => 'KPPBC BOJONEGORO',
            '070500' => 'KPPBC TMP JUANDA',
            '070600' => 'KPPBC TMC MALANG',
            '070700' => 'KPPBC BLITAR',
            '070800' => 'KPPBC TMC KEDIRI',
            '070900' => 'KPPBC TULUNG AGUNG',
            '071000' => 'KPPBC MADIUN',
            '071100' => 'KPPBC JEMBER',
            '071200' => 'KPPBC PROBOLINGGO',
            '071300' => 'KPPBC PASURUAN',
            '071400' => 'BLBC KELAS II SURABAYA',
            '071500' => 'KPPBC SIDOARJO',
            '080000' => 'KANWIL BALI,NTB DAN NTT',
            '080100' => 'KPPBC TMP NGURAH RAI',
            '080200' => 'KPPBC DENPASAR',
            '080300' => 'KPPBC MATARAM',
            '080400' => 'KPPBC SUMBAWA',
            '080500' => 'KPPBC KUPANG',
            '080700' => 'KPPBC MAUMERE',
            '081200' => 'KPPBC BENOA',
            '081300' => 'KPPBC ATAPUPU',
            '081400' => 'KPPBC ATAMBUA',
            '090000' => 'KANWIL KALBAGBAR',
            '090100' => 'KPPBC PONTIANAK',
            '090200' => 'KPPBC ENTIKONG',
            '090400' => 'KPPBC KETAPANG',
            '090500' => 'KPPBC SINTETE',
            '090700' => 'KPPBC SAMPIT',
            '090800' => 'KPPBC PANGKALAN BUN',
            '090900' => 'KPPBC PULANG PISAU',
            '091000' => 'KPPBC NANGA BADAU',
            '092000' => 'KPPBC JAGOI BABANG',
            '100000' => 'KANWIL KALBAGTIM',
            '100100' => 'KPPBC BANJARMASIN',
            '100200' => 'KPPBC KOTABARU',
            '100300' => 'KPPBC BALIKPAPAN',
            '100500' => 'KPPBC SAMARINDA',
            '100600' => 'KPPBC BONTANG',
            '100800' => 'KPPBC TARAKAN',
            '100900' => 'KPPBC NUNUKAN',
            '101000' => 'KPPBC SANGATTA',
            '110000' => 'KANWIL SULBAGSEL',
            '110100' => 'KPPBC MAKASSAR',
            '110300' => 'KPPBC PAREPARE',
            '110400' => 'KPPBC MALILI',
            '110500' => 'KPPBC BAJOE',
            '110600' => 'KPPBC KENDARI',
            '110700' => 'KPPBC POMALAA',
            '110800' => 'KPPBC PANTOLOAN',
            '110900' => 'KPPBC MOROWALI',
            '111000' => 'KPPBC LUWUK',
            '111100' => 'KPPBC BITUNG',
            '111200' => 'KPPBC MANADO',
            '111300' => 'KPPBC GORONTALO',
            '111400' => 'PANGSAROP PANTOLOAN',
            '120000' => 'KANWIL MALUKU',
            '120100' => 'KPPBC AMBON',
            '120200' => 'KPPBC TERNATE',
            '120300' => 'KPPBC SORONG',
            '120400' => 'KPPBC MANOKWARI',
            '120500' => 'KPPBC FAK-FAK',
            '120600' => 'KPPBC JAYAPURA',
            '120700' => 'KPPBC MERAUKE',
            '120800' => 'KPPBC AMAMAPARE',
            '120900' => 'KPPBC BIAK',
            '121000' => 'KPPBC TUAL',
            '121100' => 'PANGSAROP SORONG',
            '122000' => 'KPPBC BINTUNI',
            '122100' => 'KPPBC KAIMANA',
            '122200' => 'KPPBC NABIRE',
            '122300' => 'KPPBC BABO',
            '130000' => 'KANWIL ACEH',
            '130100' => 'KPPBC BANDA ACEH',
            '130300' => 'KPPBC SABANG',
            '130400' => 'KPPBC MEULABOH',
            '130500' => 'KPPBC LHOKSEUMAWE',
            '130600' => 'KPPBC KUALA LANGSA',
            '140000' => 'KANWIL RIAU',
            '150000' => 'KANWIL BANTEN',
            '150300' => 'KPPBC TANGERANG',
            '160000' => 'KANWIL JATIM II',
            '160200' => 'KPPBC MARUNDA',
            '160700' => 'KPPBC BANYUWANGI',
            '170000' => 'KANWIL SUMBAGBAR',
            '180000' => 'KANWIL KALBAGSEL',
            '180100' => 'KPPBC Nashta',
            '180200' => 'KPPBC Nashta',
            '190000' => 'KANWIL SULBAGTARA',
            '200000' => 'KANWIL KHUSUS PAPUA',
            '760000' => 'PUSDIKLAT BEA DAN CUKAI',
            '999999' => 'UNIT LAIN DI LUAR DJBC'
        ];
    }
}
