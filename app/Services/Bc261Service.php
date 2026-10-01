<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class Bc261Service
{
    protected $ceisaService;

    public function __construct(CeisaService $ceisaService)
    {
        $this->ceisaService = $ceisaService;
    }

    public function edit($id, Request $request)
    {
        $db = DB::connection('mysql_sb');

        $header = $db->table('bppb as a')
            ->select(
                'a.*',
                'ms.supplier',
                'ms.alamat as alamat_supplier',
                'ms.npwp as npwp_supplier',
                DB::raw("IF(a.bppbno_int != '', a.bppbno_int, a.bppbno) as trx_no_par")
            )
            ->leftJoin('mastersupplier as ms', 'a.id_supplier', '=', 'ms.id_supplier')
            ->where(function ($query) use ($id) {
                $query->where('a.bppbno', $id)->orWhere('a.bppbno_int', $id);
            })
            ->first();

        if (!$header) abort(404, 'Data Transaksi Tidak Ditemukan');

        $ceisaInfo = $db->table('bpb_ceisa')->where('bpbno', $id)->first();
        $dataDetail = json_decode($ceisaInfo->payload_json ?? '{}', true);

        $items = $db->table('bppb as a')
            ->leftJoin('masteritem as mi', 'a.id_item', '=', 'mi.id_item')
            ->leftJoin('masterstyle as ms', 'a.id_item', '=', 'ms.id_item')
            ->select(
                'a.id_item',

                DB::raw("IF(a.bppbno LIKE '%FG%' OR a.bppbno_int LIKE '%FG%', ms.goods_code, mi.goods_code) as goods_code"),
                DB::raw("IF(a.bppbno LIKE '%FG%' OR a.bppbno_int LIKE '%FG%', CONCAT(ms.itemname, ' ', IFNULL(ms.color,''), ' ', IFNULL(ms.size,'')), mi.itemdesc) as itemdesc"),

                DB::raw("MAX(a.unit) as unit"),
                DB::raw('SUM(a.qty) as qty'),
                DB::raw('AVG(a.price) as price'),
                DB::raw('SUM(a.qty * a.price) as total_harga')
            )
            ->where(function ($query) use ($id) {
                $query->where('a.bppbno', $id)->orWhere('a.bppbno_int', $id);
            })
            ->groupBy('a.id_item')
            ->get();

        $nomorAju = $ceisaInfo->nomor_aju ?? $this->generateNomorAju($db);

        $dokumens = !empty($dataDetail['dok']) ? $dataDetail['dok'] : [
            ['kode' => '', 'nomor' => '', 'tgl' => '', 'fasilitas' => '', 'izin' => '', 'kantor' => '']
        ];

        $jaminans = !empty($dataDetail['jaminan']) ? $dataDetail['jaminan'] : [
            ['kodeJenisJaminan' => '', 'nomorJaminan' => '', 'tanggalJaminan' => '', 'nilaiJaminan' => '', 'tanggalJatuhTempo' => '', 'penjamin' => '', 'nomorBpj' => '', 'tanggalBpj' => '']
        ];

        $pengangkuts = !empty($dataDetail['pengangkut']) ? $dataDetail['pengangkut'] : [
            ['kodeCaraAngkut' => '']
        ];

        $kontainers = !empty($dataDetail['kontainer']) ? $dataDetail['kontainer'] : [
            ['nomor' => '', 'ukuran' => '', 'jenis' => '', 'tipe' => '']
        ];

        $kemasans = !empty($dataDetail['kemasan']) ? $dataDetail['kemasan'] : [
            ['jumlah' => '', 'jenis' => '', 'merk' => '']
        ];

        $listJenisKemasan = \App\Services\BcReferenceService::getJenisKemasan();
        $listSatuanBarang = \App\Services\BcReferenceService::getSatuanBarang();

        $referensiDokumen = \App\Services\BcReferenceService::getReferensiDokumen();

        $listValuta = \App\Services\BcReferenceService::getValuta();
        $listTujuanPengiriman = \App\Services\BcReferenceService::getTujuanPengiriman('261');

        $listCaraAngkut = \App\Services\BcReferenceService::getCaraAngkut();

        $listJenisPungutan = \App\Services\BcReferenceService::getJenisPungutan();

        $listJenisJaminan = \App\Services\BcReferenceService::getJenisJaminan();

        $listKategoriBarang = \App\Services\BcReferenceService::getKategoriBarang('261');

        $listUkuranKontainer = \App\Services\BcReferenceService::getUkuranKontainer();

        $listTipeKontainer = \App\Services\BcReferenceService::getTipeKontainer();

        $listJenisKontainer = \App\Services\BcReferenceService::getJenisKontainer();

        // Format CEISA status
        if (!$ceisaInfo) {
            $statusCeisa = '<span class="badge badge-secondary">Draft Kosong</span>';
        } else {
            if ($ceisaInfo->status == 0) {
                $statusCeisa = '<span class="badge badge-warning">Draft</span>';
            } elseif ($ceisaInfo->status == 1) {
                $statusCeisa = '<span class="badge badge-success">Sudah Kirim</span>';
            } else {
                $statusCeisa = '<span class="badge badge-danger">Gagal</span>';
            }
        }

        $kantorList = $this->getKantorList();

        return view('export-import.dokumen-pabean.edit-bc261', [
            'header'              => $header,
            'items'               => $items,
            'ceisaInfo'           => $ceisaInfo,
            'dataDetail'          => $dataDetail,
            'nomorAju'            => $nomorAju,
            'dokumens'            => $dokumens,
            'jaminans'            => $jaminans,
            'statusCeisa'         => $statusCeisa,
            'kantorList'          => $kantorList,
            'pengangkuts'         => $pengangkuts,
            'kontainers'          => $kontainers,
            'kemasans'            => $kemasans,
            'listSatuanBarang'    => $listSatuanBarang,
            'listJenisKemasan'    => $listJenisKemasan,
            'listValuta'          => $listValuta,
            'listTujuanPengiriman'=> $listTujuanPengiriman,
            'listKategoriBarang'  => $listKategoriBarang,
            'listUkuranKontainer' => $listUkuranKontainer,
            'listTipeKontainer'   => $listTipeKontainer,
            'listJenisKontainer'  => $listJenisKontainer,
            'referensiDokumen'    => $referensiDokumen,
            'listCaraAngkut'      => $listCaraAngkut,
            'listJenisPungutan'   => $listJenisPungutan,
            'listJenisJaminan'    => $listJenisJaminan,
            'containerFluid'      => true,
            "page"           => "dashboard-export-import",
            "subPageGroup"   => "export-import",
            "subPage"        => "dokumen-pabean-list",
        ]);
    }


    public function updateDraft($id, Request $request)
    {
        DB::connection('mysql_sb')->beginTransaction();

        try {
            // --- 1. Dokumen Pendukung & Upload File ---
            $dokumenInput = $request->input('dok', []);
            $dokumenFiles = $request->file('dok', []);
            $dokumenList  = [];
            $seriDok = 1;

            foreach ($dokumenInput as $index => $d) {
                if (!empty($d['kode']) || !empty($d['nomor'])) {
                    $dokData = [
                        'seriDokumen'    => $seriDok++,
                        'kodeDokumen'    => $d['kode'] ?? '',
                        'nomorDokumen'   => $d['nomor'] ?? '',
                        'tanggalDokumen' => $d['tgl'] ?? date('Y-m-d'),
                        'fasilitas'      => $d['fasilitas'] ?? '',
                        'izin'           => $d['izin'] ?? '',
                        'kantor'         => $d['kantor'] ?? '',
                        'fileName'       => $d['fileName'] ?? null,
                    ];

                    if (isset($dokumenFiles[$index]['file_lampiran'])) {
                        $file = $dokumenFiles[$index]['file_lampiran'];
                        $fileName = 'CEISA_' . str_replace('/', '-', $id) . '_' . ($d['kode'] ?? 'DOC') . '_' . time() . '.' . $file->getClientOriginalExtension();
                        $destinationPath = public_path('uploads/ceisa');
                        if (!file_exists($destinationPath)) {
                            mkdir($destinationPath, 0755, true);
                        }
                        $file->move($destinationPath, $fileName);
                        $dokData['fileName'] = $fileName;
                        $dokData['urlDokumen'] = url('/uploads/ceisa/' . $fileName);
                    } else {
                        if (!empty($d['old_file'])) {
                            $dokData['fileName'] = $d['old_file'];
                            $dokData['urlDokumen'] = url('/uploads/ceisa/' . $d['old_file']);
                        }
                    }

                    $dokumenList[] = $dokData;
                }
            }

            // --- 2. Kontainer ---
            $kontainerInput = $request->input('kontainer', []);
            $kontainerList  = [];
            $seriKontainer = 1;
            foreach ($kontainerInput as $k) {
                $nomor = $k['nomorKontainer'] ?? '';
                if (!empty($nomor)) {
                    $kontainerList[] = [
                        'seriKontainer'       => $seriKontainer++,
                        'kodeJenisKontainer'  => $k['kodeJenisKontainer'] ?? '',
                        'kodeTipeKontainer'   => $k['kodeTipeKontainer'] ?? '',
                        'kodeUkuranKontainer' => $k['kodeUkuranKontainer'] ?? '',
                        'nomorKontainer'      => $nomor,
                    ];
                }
            }

            // --- 3. Kemasan ---
            $kemasanInput = $request->input('kemasan', []);
            $kemasanList  = [];
            $seriKemasan = 1;
            foreach ($kemasanInput as $k) {
                $jumlah = $k['jumlahKemasan'] ?? 0;
                $jenis  = $k['kodeJenisKemasan'] ?? '';
                if (!empty($jumlah) || !empty($jenis)) {
                    $kemasanList[] = [
                        'seriKemasan'      => $seriKemasan++,
                        'jumlahKemasan'    => (float) $jumlah,
                        'kodeJenisKemasan' => $jenis,
                        'merkKemasan'      => $k['merkKemasan'] ?? '-',
                    ];
                }
            }

            // --- 4. Pengangkut ---
            $pengangkutInput = $request->input('pengangkut', []);
            $pengangkutList  = [];
            $seriPengangkut = 1;
            foreach ($pengangkutInput as $p) {
                if (!empty($p['kodeCaraAngkut'])) {
                    $pengangkutList[] = [
                        'seriPengangkut' => $seriPengangkut++,
                        'kodeCaraAngkut' => $p['kodeCaraAngkut'] ?? '',
                        'kodeBendera'    => $p['kodeBendera'] ?? 'ID',
                    ];
                }
            }

            // --- 5. Jaminan ---
            $jaminanInput = $request->input('jaminan', []);
            $jaminanList  = [];
            foreach ($jaminanInput as $j) {
                if (!empty($j['kodeJenisJaminan'])) {
                    $jaminanList[] = [
                        'kodeJenisJaminan'  => $j['kodeJenisJaminan'] ?? '',
                        'nomorJaminan'      => $j['nomorJaminan'] ?? '',
                        'tanggalJaminan'    => $j['tanggalJaminan'] ?? date('Y-m-d'),
                        'nilaiJaminan'      => (float) ($j['nilaiJaminan'] ?? 0),
                        'tanggalJatuhTempo' => $j['tanggalJatuhTempo'] ?? date('Y-m-d'),
                        'penjamin'          => $j['penjamin'] ?? '',
                        'nomorBpj'          => $j['nomorBpj'] ?? '',
                        'tanggalBpj'        => $j['tanggalBpj'] ?? date('Y-m-d'),
                    ];
                }
            }

            // --- 6. Barang & Bahan Baku ---
            $barangInput = $request->input('barang', []);
            $barangList  = [];
            $kantorPabean = $request->input('kantorPabean');

            foreach ($barangInput as $index => $b) {

                // Bahan Baku — form HANYA mengirim: hs, uraian, nilaiBarang, kodeSatuan, seriBahanBaku
                $bahanBakuInput = $b['bahanBaku'] ?? [];
                $bahanBakuList  = [];
                $seriBahanBaku  = 1;

                foreach ($bahanBakuInput as $bb) {
                    // Gate yang benar: form tidak pernah kirim 'kodeBarang', jadi pakai 'hs' atau 'uraian' sebagai penanda baris terisi
                    if (!empty($bb['hs']) || !empty($bb['uraian'])) {
                        $bahanBakuList[] = [
                            'cif'                   => (float) ($bb['nilaiBarang'] ?? 0), // asumsi: Nilai Barang di form = CIF bahan baku
                            'cifRupiah'             => 0,
                            'hargaPenyerahan'       => 0,
                            'hargaPerolehan'        => 0,
                            'jumlahSatuan'          => (float) ($b['jumlahSatuan'] ?? 0),
                            'kodeSatuanBarang'      => $bb['kodeSatuan'] ?? '',
                            'kodeAsalBahanBaku'     => $bb['kodeAsalBahanBaku'] ?? '0',
                            'kodeBarang'            => $b['kodeBarang'] ?? '',
                            'kodeDokAsal'           => $bb['kodeDokAsal'] ?? '',
                            'kodeDokumen'           => $bb['kodeDokumen'] ?? '',
                            'kodeKantor'            => $kantorPabean ?? '',
                            'merkBarang'            => $b['merk'] ?? '-',
                            'ndpbm'                 => 0,
                            'netto'                 => (float) ($b['netto'] ?? 0),
                            'nomorDaftarDokAsal'    => $bb['nomorDaftarDokAsal'] ?? '',
                            'posTarif'              => $bb['hs'] ?? '',
                            'seriBahanBaku'         => $seriBahanBaku,
                            'seriBarang'            => (int) ($b['seriBarang'] ?? ($index + 1)),
                            'seriBarangDokAsal'     => 1,
                            'seriIjin'              => 1,
                            'spesifikasiLainBarang' => '-',
                            'tanggalDaftarDokAsal'  => date('Y-m-d'),
                            'tipeBarang'            => $b['tipe'] ?? '-',
                            'ukuranBarang'          => $b['ukuran'] ?? '-',
                            'uraianBarang'          => $bb['uraian'] ?? '',
                            'nilaiJasa'             => 0,
                            'flagTis'               => '0',
                            'bahanBakuTarif'        => [],
                        ];

                        $seriBahanBaku++;
                    }
                }

                $cifBarang = (float) ($b['cif'] ?? 0);

                $barangList[] = [
                    'seriBarang'        => (int) ($b['seriBarang'] ?? ($index + 1)),
                    'kodeBarang'        => $b['kodeBarang'] ?? '',
                    'uraian'            => $b['uraian'] ?? '',
                    'merk'              => $b['merk'] ?? '',
                    'tipe'              => $b['tipe'] ?? '',
                    'ukuran'            => $b['ukuran'] ?? '',
                    'spesifikasiLain'   => $b['spesifikasiLain'] ?? '-',
                    'posTarif'          => $b['posTarif'] ?? '',
                    'kodeNegaraAsal'    => $b['kodeNegaraAsal'] ?? '',
                    'kodeAsalBarang'    => $b['kodeAsalBarang'] ?? '0',
                    'jumlahSatuan'      => (float) ($b['jumlahSatuan'] ?? 0),
                    'kodeSatuanBarang'  => $b['kodeSatuanBarang'] ?? '',
                    'jumlahKemasan'     => (float) ($b['jumlahKemasan'] ?? 0),
                    'kodeJenisKemasan'  => $b['kodeJenisKemasan'] ?? '',
                    'netto'             => (float) ($b['netto'] ?? 0),
                    'cif'               => $cifBarang,
                    // Form tidak punya field nilaiBarang per-item; default ke cif barang jika kosong
                    'nilaiBarang'       => (float) ($b['nilaiBarang'] ?? $cifBarang),

                    'cifRupiah'         => (float) ($b['cifRupiah'] ?? 0),
                    'hargaEkspor'       => (float) ($b['hargaEkspor'] ?? 0),
                    'hargaPenyerahan'   => (float) ($b['hargaPenyerahan'] ?? 0),
                    'hargaPerolehan'    => (float) ($b['hargaPerolehan'] ?? 0),
                    'isiPerKemasan'     => (float) ($b['isiPerKemasan'] ?? 0),
                    'kodeAsalBahanBaku' => $b['kodeAsalBahanBaku'] ?? '0',
                    'kodeDokumen'       => $b['kodeDokumen'] ?? '23',
                    'ndpbm'             => (float) ($b['ndpbm'] ?? 0),
                    'uangMuka'          => (float) ($b['uangMuka'] ?? 0),
                    'nilaiJasa'         => (float) ($b['nilaiJasa'] ?? 0),

                    'bahanBaku'         => $bahanBakuList,
                ];
            }

            $draft = [
                'kantorPabean'     => $kantorPabean,
                'tujuanPengiriman' => $request->input('tujuanPengiriman'),

                'entitas'          => $request->input('entitas', []),

                'valuta'           => $request->input('valuta'),
                'ndpbm'            => (float) $request->input('ndpbm'),
                'nilaiCif'         => (float) $request->input('nilaiCif'),
                'nilaiPabean'      => (float) $request->input('nilaiPabean'),

                'bruto'            => (float) $request->input('bruto', 0),
                'netto'            => (float) $request->input('netto', 0),

                'pungutan'         => $request->input('pungutan', []),

                'dok'              => $dokumenList,
                'kontainer'        => $kontainerList,
                'kemasan'          => $kemasanList,
                'pengangkut'       => $pengangkutList,
                'barang'           => $barangList,
                'jaminan'          => $jaminanList,

                'tempatTtd'        => $request->input('tempatTtd'),
                'tanggalTtd'       => $request->input('tanggalTtd'),
                'namaTtd'          => $request->input('namaTtd'),
                'jabatanTtd'       => $request->input('jabatanTtd'),
            ];

            $headerBpb = DB::connection('mysql_sb')->table('bpb')->where(function ($query) use ($id) {
                $query->where('bpbno', $id)->orWhere('bpbno_int', $id);
            })->first();

            $realBpbno    = $headerBpb ? $headerBpb->bpbno : $id;
            $realBpbnoInt = $headerBpb ? $headerBpb->bpbno_int : '';

            $ceisaRec = DB::connection('mysql_sb')->table('bpb_ceisa')
                ->where('bpbno', $id)->orWhere('bpbno_int', $id)->first();

            $inputNomorAju = $request->input('nomorAju', '');
            $payloadJson   = json_encode($draft);

            if ($ceisaRec) {
                DB::connection('mysql_sb')->table('bpb_ceisa')
                    ->where('id', $ceisaRec->id)
                    ->update([
                        'bpbno'        => $realBpbno,
                        'bpbno_int'    => $realBpbnoInt,
                        'nomor_aju'    => $inputNomorAju ?: $ceisaRec->nomor_aju,
                        'payload_json' => $payloadJson,
                        'jenis_bc'     => '2.6.1',
                        'updated_at'   => Carbon::now()
                    ]);
            } else {
                DB::connection('mysql_sb')->table('bpb_ceisa')->insert([
                    'bpbno'        => $realBpbno,
                    'bpbno_int'    => $realBpbnoInt,
                    'nomor_aju'    => $inputNomorAju,
                    'jenis_bc'     => '2.6.1',
                    'payload_json' => $payloadJson,
                    'status'       => 0,
                    'created_at'   => Carbon::now(),
                    'updated_at'   => Carbon::now()
                ]);
            }

            DB::connection('mysql_sb')->commit();

            return response()->json([
                'status'  => 200,
                'message' => 'Draft BC 2.6.1 berhasil disimpan.'
            ]);

        } catch (\Exception $e) {
            DB::connection('mysql_sb')->rollBack();
            Log::error('Error Update Draft BC 2.6.1: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json([
                'status'  => 500,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function sendCeisa($id, Request $request)
    {
        $db = DB::connection('mysql_sb');

        try {
            $draftRow = $db->table('bpb_ceisa')->where('bpbno', $id)->orWhere('bpbno_int', $id)->first();
            if (!$draftRow || empty($draftRow->payload_json)) {
                throw new \Exception('Draft kosong. Simpan draft terlebih dahulu.');
            }

            $draft = json_decode($draftRow->payload_json, true);
            $nomorAju = $draftRow->nomor_aju ?? $this->generateNomorAju($db);

            // --- Dokumen ---
            $payloadDokumen = [];
            foreach (($draft['dok'] ?? []) as $index => $d) {
                $docItem = [
                    'idDokumen'      => "DOK" . str_pad($index + 1, 4, "0", STR_PAD_LEFT),
                    'kodeDokumen'    => $d['kodeDokumen'] ?? $d['kode'] ?? '',
                    'nomorDokumen'   => $d['nomorDokumen'] ?? $d['nomor'] ?? '',
                    'seriDokumen'    => (int) ($d['seriDokumen'] ?? ($index + 1)),
                    'tanggalDokumen' => $d['tanggalDokumen'] ?? $d['tgl'] ?? date('Y-m-d')
                ];
                if (!empty($d['fasilitas'])) {
                    $docItem['kodeFasilitas'] = substr($d['fasilitas'], 0, 2);
                }
                $payloadDokumen[] = $docItem;
            }

            // --- Entitas ---
            $entitasDraft = $draft['entitas'] ?? [];
            $payloadEntitas = [];

            if (!empty($entitasDraft['tpb'])) {
                $e = $entitasDraft['tpb'];
                $payloadEntitas[] = [
                    'seriEntitas'        => 1,
                    'kodeEntitas'        => '3',
                    'kodeJenisApi'       => $e['jenisApi'] ?? '02',
                    'kodeJenisIdentitas' => !empty($e['nomorIdentitas']) && strlen(str_replace(['.', '-'], '', $e['nomorIdentitas'])) == 16 ? '6' : '5',
                    'kodeStatus'         => $e['status'] ?? '5',
                    'namaEntitas'        => $e['namaEntitas'] ?? $e['nama'] ?? '',
                    'nibEntitas'         => $e['nibEntitas'] ?? $e['nib'] ?? '',
                    'nomorIdentitas'     => str_replace(['.', '-'], '', $e['nomorIdentitas'] ?? ''),
                    'nomorIjinEntitas'   => $e['nomorIjinEntitas'] ?? $e['nomorIjin'] ?? '',
                    'alamatEntitas'      => $e['alamatEntitas'] ?? $e['alamat'] ?? '',
                    'tanggalIjinEntitas' => $e['tanggalIjinEntitas'] ?? $e['tanggalIjin'] ?? date('Y-m-d')
                ];
            }

            if (!empty($entitasDraft['pemilik'])) {
                $e = $entitasDraft['pemilik'];
                $payloadEntitas[] = [
                    'seriEntitas'        => 2,
                    'kodeEntitas'        => '7',
                    'kodeJenisApi'       => $e['jenisApi'] ?? '02',
                    'kodeJenisIdentitas' => !empty($e['nomorIdentitas']) && strlen(str_replace(['.', '-'], '', $e['nomorIdentitas'])) == 16 ? '6' : '5',
                    'kodeStatus'         => $e['status'] ?? '5',
                    'namaEntitas'        => $e['namaEntitas'] ?? $e['nama'] ?? '',
                    'nibEntitas'         => $e['nibEntitas'] ?? $e['nib'] ?? '',
                    'nomorIdentitas'     => str_replace(['.', '-'], '', $e['nomorIdentitas'] ?? ''),
                    'nomorIjinEntitas'   => $e['nomorIjinEntitas'] ?? $e['nomorIjin'] ?? '',
                    'alamatEntitas'      => $e['alamatEntitas'] ?? $e['alamat'] ?? '',
                    'tanggalIjinEntitas' => $e['tanggalIjinEntitas'] ?? $e['tanggalIjin'] ?? date('Y-m-d')
                ];
            }

            if (!empty($entitasDraft['penerima'])) {
                $e = $entitasDraft['penerima'];
                $payloadEntitas[] = [
                    'seriEntitas'        => 3,
                    'kodeEntitas'        => '8',
                    'kodeJenisApi'       => $e['jenisApi'] ?? '02',
                    'kodeJenisIdentitas' => !empty($e['nomorIdentitas']) && strlen(str_replace(['.', '-'], '', $e['nomorIdentitas'])) == 16 ? '6' : '5',
                    'kodeStatus'         => $e['status'] ?? '5',
                    'namaEntitas'        => $e['namaEntitas'] ?? $e['nama'] ?? '',
                    'nibEntitas'         => $e['nibEntitas'] ?? $e['nib'] ?? '',
                    'nomorIdentitas'     => str_replace(['.', '-'], '', $e['nomorIdentitas'] ?? ''),
                    'nomorIjinEntitas'   => $e['nomorIjinEntitas'] ?? $e['nomorIjin'] ?? '',
                    'alamatEntitas'      => $e['alamatEntitas'] ?? $e['alamat'] ?? '',
                    'tanggalIjinEntitas' => $e['tanggalIjinEntitas'] ?? $e['tanggalIjin'] ?? date('Y-m-d')
                ];
            }

            // --- Kemasan ---
            $payloadKemasan = [];
            foreach (($draft['kemasan'] ?? []) as $index => $k) {
                $payloadKemasan[] = [
                    'jumlahKemasan'    => (float) ($k['jumlahKemasan'] ?? 0),
                    'kodeJenisKemasan' => $k['kodeJenisKemasan'] ?? '',
                    'merkKemasan'      => $k['merkKemasan'] ?? '-',
                    'seriKemasan'      => (int) ($k['seriKemasan'] ?? ($index + 1)),
                ];
            }

            // --- Kontainer ---
            $payloadKontainer = [];
            foreach (($draft['kontainer'] ?? []) as $index => $k) {
                $payloadKontainer[] = [
                    'kodeJenisKontainer'  => $k['kodeJenisKontainer'] ?? '',
                    'kodeTipeKontainer'   => $k['kodeTipeKontainer'] ?? '',
                    'kodeUkuranKontainer' => $k['kodeUkuranKontainer'] ?? '',
                    'nomorKontainer'      => $k['nomorKontainer'] ?? '',
                    'seriKontainer'       => (int) ($k['seriKontainer'] ?? ($index + 1)),
                ];
            }

            // --- Pengangkut ---
            $payloadPengangkut = [];
            foreach (($draft['pengangkut'] ?? []) as $index => $p) {
                $payloadPengangkut[] = [
                    'idPengangkut'   => "ANG" . str_pad($index + 1, 4, "0", STR_PAD_LEFT),
                    'kodeCaraAngkut' => $p['kodeCaraAngkut'] ?? '',
                    'seriPengangkut' => (int) ($p['seriPengangkut'] ?? ($index + 1)),
                ];
            }

            // --- Jaminan ---
            $payloadJaminan = [];
            foreach (($draft['jaminan'] ?? []) as $index => $j) {
                $payloadJaminan[] = [
                    'idJaminan'         => "JAM" . str_pad($index + 1, 4, "0", STR_PAD_LEFT),
                    'nomorBpj'          => $j['nomorBpj'] ?? '',
                    'tanggalBpj'        => $j['tanggalBpj'] ?? date('Y-m-d'),
                    'kodeJenisJaminan'  => $j['kodeJenisJaminan'] ?? '',
                    'nomorJaminan'      => $j['nomorJaminan'] ?? '',
                    'tanggalJaminan'    => $j['tanggalJaminan'] ?? date('Y-m-d'),
                    'tanggalJatuhTempo' => $j['tanggalJatuhTempo'] ?? date('Y-m-d'),
                    'penjamin'          => $j['penjamin'] ?? '',
                    'nilaiJaminan'      => (float) ($j['nilaiJaminan'] ?? 0),
                ];
            }

            // --- Barang ---
            $payloadBarang = [];
            foreach (($draft['barang'] ?? []) as $index => $b) {

                $payloadBahanBaku = [];
                foreach (($b['bahanBaku'] ?? []) as $bb) {

                    $payloadBahanBakuTarif = [];
                    foreach (($bb['bahanBakuTarif'] ?? []) as $bbt) {
                        $payloadBahanBakuTarif[] = [
                            'seriBahanBaku'      => (int) ($bbt['seriBahanBaku'] ?? 1),
                            'kodeJenisPungutan'  => $bbt['kodeJenisPungutan'] ?? '',
                            'kodeAsalBahanBaku'  => $bbt['kodeAsalBahanBaku'] ?? '0',
                            'kodeFasilitasTarif' => $bbt['kodeFasilitasTarif'] ?? '',
                            'kodeSatuanBarang'   => $bbt['kodeSatuanBarang'] ?? '',
                            'kodeJenisTarif'     => $bbt['kodeJenisTarif'] ?? '1',
                            'nilaiBayar'         => round((float) ($bbt['nilaiBayar'] ?? 0), 2),
                            'nilaiSudahDilunasi' => round((float) ($bbt['nilaiSudahDilunasi'] ?? 0), 2),
                            'nilaiFasilitas'     => round((float) ($bbt['nilaiFasilitas'] ?? 0), 2),
                            'jumlahSatuan'       => (float) ($bbt['jumlahSatuan'] ?? 0),
                            'jumlahKemasan'      => (float) ($bbt['jumlahKemasan'] ?? 0),
                            'tarif'              => round((float) ($bbt['tarif'] ?? 0), 2),
                            'tarifFasilitas'     => round((float) ($bbt['tarifFasilitas'] ?? 0), 2)
                        ];
                    }

                    $payloadBahanBaku[] = [
                        'cif'                   => round((float) ($bb['cif'] ?? 0), 2),
                        'cifRupiah'             => round((float) ($bb['cifRupiah'] ?? 0), 2),
                        'hargaPenyerahan'       => round((float) ($bb['hargaPenyerahan'] ?? 0), 2),
                        'hargaPerolehan'        => round((float) ($bb['hargaPerolehan'] ?? 0), 2),
                        'jumlahSatuan'          => (float) ($bb['jumlahSatuan'] ?? 0),
                        'kodeSatuanBarang'      => $bb['kodeSatuanBarang'] ?? '',
                        'kodeAsalBahanBaku'     => $bb['kodeAsalBahanBaku'] ?? '0',
                        'kodeBarang'            => $bb['kodeBarang'] ?? '',
                        'kodeDokAsal'           => $bb['kodeDokAsal'] ?? '',
                        'kodeDokumen'           => $bb['kodeDokumen'] ?? '',
                        'kodeKantor'            => $bb['kodeKantor'] ?? '',
                        'merkBarang'            => $bb['merkBarang'] ?? '',
                        'ndpbm'                 => round((float) ($bb['ndpbm'] ?? 0), 2),
                        'netto'                 => (float) ($bb['netto'] ?? 0),
                        'nomorDaftarDokAsal'    => $bb['nomorDaftarDokAsal'] ?? '',
                        'posTarif'              => $bb['posTarif'] ?? '',
                        'seriBahanBaku'         => (int) ($bb['seriBahanBaku'] ?? 1),
                        'seriBarang'            => (int) ($bb['seriBarang'] ?? 1),
                        'seriBarangDokAsal'     => (int) ($bb['seriBarangDokAsal'] ?? 1),
                        'seriIjin'              => (int) ($bb['seriIjin'] ?? 1),
                        'spesifikasiLainBarang' => $bb['spesifikasiLainBarang'] ?? '-',
                        'tanggalDaftarDokAsal'  => $bb['tanggalDaftarDokAsal'] ?? date('Y-m-d'),
                        'tipeBarang'            => $bb['tipeBarang'] ?? '-',
                        'ukuranBarang'          => $bb['ukuranBarang'] ?? '-',
                        'uraianBarang'          => $bb['uraianBarang'] ?? '',
                        'nilaiJasa'             => round((float) ($bb['nilaiJasa'] ?? 0), 2),
                        'flagTis'               => $bb['flagTis'] ?? '0',
                        'bahanBakuTarif'        => $payloadBahanBakuTarif
                    ];
                }

                $cifBarangItem = round((float) ($b['cif'] ?? 0), 2);

                $payloadBarang[] = [
                    'cif'               => $cifBarangItem,
                    'cifRupiah'         => round((float) ($b['cifRupiah'] ?? 0), 2),
                    'hargaEkspor'       => round((float) ($b['hargaEkspor'] ?? 0), 2),
                    'hargaPenyerahan'   => round((float) ($b['hargaPenyerahan'] ?? 0), 2),
                    'hargaPerolehan'    => round((float) ($b['hargaPerolehan'] ?? 0), 2),
                    'isiPerKemasan'     => (float) ($b['isiPerKemasan'] ?? 0),
                    'jumlahKemasan'     => (float) ($b['jumlahKemasan'] ?? 0),
                    'jumlahSatuan'      => (float) ($b['jumlahSatuan'] ?? 0),
                    'kodeAsalBahanBaku' => $b['kodeAsalBahanBaku'] ?? '0',
                    'kodeAsalBarang'    => $b['kodeAsalBarang'] ?? '0',
                    'kodeBarang'        => $b['kodeBarang'] ?? '',
                    'kodeDokumen'       => $b['kodeDokumen'] ?? '20',
                    'kodeJenisKemasan'  => $b['kodeJenisKemasan'] ?? '',
                    'kodeNegaraAsal'    => $b['kodeNegaraAsal'] ?? '',
                    'kodeSatuanBarang'  => $b['kodeSatuanBarang'] ?? '',
                    'merk'              => $b['merk'] ?? '-',
                    'ndpbm'             => round((float) ($b['ndpbm'] ?? 0), 2),
                    'netto'             => (float) ($b['netto'] ?? 0),
                    'nilaiBarang'       => round((float) ($b['nilaiBarang'] ?? $cifBarangItem), 2),
                    'nilaiJasa'         => round((float) ($b['nilaiJasa'] ?? 0), 2),
                    'posTarif'          => $b['posTarif'] ?? '',
                    'seriBarang'        => (int) ($b['seriBarang'] ?? ($index + 1)),
                    'spesifikasiLain'   => $b['spesifikasiLain'] ?? '-',
                    'tipe'              => $b['tipe'] ?? '-',
                    'uangMuka'          => round((float) ($b['uangMuka'] ?? 0), 2),
                    'ukuran'            => $b['ukuran'] ?? '-',
                    'uraian'            => $b['uraian'] ?? '',

                    'bahanBaku'         => $payloadBahanBaku
                ];
            }

            // --- Pungutan ---
            $payloadPungutan = [];
            foreach (($draft['pungutan'] ?? []) as $index => $p) {
                if (!empty($p['kodeJenisPungutan'])) {
                    $payloadPungutan[] = [
                        'idPungutan'         => "PUN" . str_pad($index + 1, 4, "0", STR_PAD_LEFT),
                        'kodeFasilitasTarif' => $p['kodeFasilitasTarif'] ?? '1',
                        'kodeJenisPungutan'  => $p['kodeJenisPungutan'] ?? '',
                        'nilaiPungutan'      => round((float) ($p['nilaiPungutan'] ?? 0), 2),
                    ];
                }
            }

            // --- Hitung ulang CIF & Nilai Barang header dari barang (jangan percaya nilaiCif tersimpan) ---
            $totalCifBarang = 0;
            foreach ($payloadBarang as $b) {
                $totalCifBarang += $b['cif'];
            }
            $totalCifBarang = round($totalCifBarang, 2);

            $cifHeader = $totalCifBarang > 0 ? $totalCifBarang : round((float) ($draft['nilaiCif'] ?? 0), 2);

            $nilaiBarangHeader = round((float) ($draft['nilaiPabean'] ?? 0), 2);
            if ($nilaiBarangHeader <= 0 && $totalCifBarang > 0) {
                $nilaiBarangHeader = $totalCifBarang;
            }

            $finalPayload = [
                'asalData'             => 'S',
                'asuransi'             => 0,
                'biayaTambahan'        => 0,
                'biayaPengurang'       => 0,
                'bruto'                => round((float) ($draft['bruto'] ?? 0), 2),
                'cif'                  => $cifHeader,
                'disclaimer'           => "1",
                'freight'              => 0,
                'hargaPenyerahan'      => 0,
                'jabatanTtd'           => $draft['jabatanTtd'] ?? '-',
                'jumlahKontainer'      => count($payloadKontainer),
                'kodeDokumen'          => '261',
                'kodeKantor'           => $draft['kantorPabean'] ?? '',
                'kodeTujuanPengiriman' => $draft['tujuanPengiriman'] ?? '',
                'kodeValuta'           => $draft['valuta'] ?? 'IDR',
                'kotaTtd'              => $draft['tempatTtd'] ?? '-',
                'namaTtd'              => $draft['namaTtd'] ?? '-',
                'ndpbm'                => round((float) ($draft['ndpbm'] ?? 0), 2),
                'netto'                => (float) ($draft['netto'] ?? 0),
                'nik'                  => '',
                'nilaiBarang'          => $nilaiBarangHeader,
                'nomorAju'             => $nomorAju,
                'seri'                 => 0,
                'tanggalAju'           => date('Y-m-d'),
                'tanggalTtd'           => $draft['tanggalTtd'] ?? date('Y-m-d'),
                'tempatStuffing'       => '',
                'tglAkhirBerlaku'      => date('Y-m-d'),
                'tglAwalBerlaku'       => date('Y-m-d'),
                'totalDanaSawit'       => 0,
                'uangMuka'             => 0,
                'vd'                   => 0,

                'barang'               => $payloadBarang,
                'dokumen'              => $payloadDokumen,
                'entitas'              => $payloadEntitas,
                'jaminan'              => $payloadJaminan,
                'kemasan'              => $payloadKemasan,
                'kontainer'            => $payloadKontainer,
                'pengangkut'           => $payloadPengangkut,
                'pungutan'             => $payloadPungutan,
            ];

            foreach (['tanggalTtd', 'tglAkhirBerlaku', 'tglAwalBerlaku'] as $dateField) {
                if (isset($finalPayload[$dateField]) && empty($finalPayload[$dateField])) {
                    unset($finalPayload[$dateField]);
                }
            }

            Log::info('Kirim BC 2.6.1 CEISA Payload: ', $finalPayload);

            $responseCeisa = $this->ceisaService->kirimDokumenBc261($finalPayload);

            if ($responseCeisa['successful']) {

                $data_kantor = $db->table('master_kantor')
                                ->where('kode', $draft['kantorPabean'] ?? '')
                                ->get()->first();

                $kantor = 60;
                if ($data_kantor) {
                    $kantor = $data_kantor->id;
                }

                $db->table('bppb')
                    ->where(function ($query) use ($id) {
                        $query->where('bppbno', $id)->orWhere('bppbno_int', $id);
                    })
                    ->update([
                        'nomor_aju'   => $nomorAju,
                        'tanggal_aju' => date('Y-m-d'),
                        'bcdate'      => date('Y-m-d'),
                        'kode_kantor' => $kantor,
                    ]);

                $db->table('bpb_ceisa')->where('bpbno', $id)->orWhere('bpbno_int', $id)->update([
                    'nomor_aju'   => $nomorAju,
                    'tanggal_aju' => Carbon::now()->format('Y-m-d'),
                    'status'      => 1,
                    'updated_at'  => Carbon::now()
                ]);

                return response()->json([
                    'status'         => 200,
                    'message'        => 'Dokumen BC 2.6.1 berhasil dikirim ke CEISA!',
                    'data_payload'   => $finalPayload,
                    'ceisa_response' => $responseCeisa['body']
                ]);
            } else {
                return response()->json([
                    'status'      => $responseCeisa['status_code'],
                    'message'     => 'Gagal mengirim BC 2.6.1 ke CEISA.',
                    'ceisa_error' => $responseCeisa['body']
                ], $responseCeisa['status_code']);
            }

        } catch (\Exception $e) {
            Log::error('Error Send CEISA BC 2.6.1: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json([
                'status'  => 500,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    private function generateNomorAju($db)
    {
        $currentYear = date('Y');
        $today       = date('Ymd');
        $prefix      = '000261NIW3452';

        $lastCeisa = $db->table('bpb_ceisa')
            ->where('nomor_aju', 'like', $prefix . $currentYear . '%')
            ->where('jenis_bc', '2.6.1')
            ->orderBy('nomor_aju', 'desc')
            ->first();

        $localSeq = 0;
        if ($lastCeisa && $lastCeisa->nomor_aju && strlen($lastCeisa->nomor_aju) === 26) {
            $localSeq = (int) substr($lastCeisa->nomor_aju, -5);
        }

        $ceisaSeq = $this->ceisaService->getLastSequenceFromCeisa($prefix . $currentYear, '261');

        $maxSeq  = max($localSeq, $ceisaSeq);
        $nextSeq = str_pad($maxSeq + 1, 5, '0', STR_PAD_LEFT);

        return $prefix . $today . $nextSeq;
    }

    private function getKantorList()
    {
        return [
            ['kode' => '050500', 'nama' => '050500 - KPPBC TMP A BANDUNG'],
            ['kode' => '010700', 'nama' => '010700 - KPPBC TMP B MEDAN'],
            ['kode' => '040300', 'nama' => '040300 - KPPBC TMP A JAKARTA'],
            ['kode' => '050100', 'nama' => '050100 - KPPBC TMP A BOGOR'],
            ['kode' => '050200', 'nama' => '050200 - KPPBC TMP A PURWAKARTA'],
            ['kode' => '050300', 'nama' => '050300 - KPPBC TMP A BEKASI'],
            ['kode' => '060100', 'nama' => '060100 - KPPBC TMP TANJUNG EMAS'],
            ['kode' => '070100', 'nama' => '070100 - KPPBC TMP TANJUNG PERAK'],
            ['kode' => '070300', 'nama' => '070300 - KPPBC TMP PASURUAN'],
            ['kode' => '070600', 'nama' => '070600 - KPPBC TMP C GRESIK'],
        ];
    }

    public function editBatch($ids, Request $request)
    {
        $db = DB::connection('mysql_sb');

        $bppbs = explode(',', $ids);
        $firstBpb = $bppbs[0];

        $header = $db->table('bppb as a')
            ->select(
                'a.*',
                'ms.supplier',
                'ms.alamat as alamat_supplier',
                'ms.npwp as npwp_supplier',
                DB::raw("IF(a.bppbno_int != '', a.bppbno_int, a.bppbno) as trx_no_par")
            )
            ->leftJoin('mastersupplier as ms', 'a.id_supplier', '=', 'ms.id_supplier')
            ->where(function ($query) use ($firstBpb) {
                $query->where('a.bppbno', $firstBpb)->orWhere('a.bppbno_int', $firstBpb);
            })
            ->first();


        if (!$header) abort(404, 'Data Transaksi Tidak Ditemukan');

        $ceisaInfo = $db->table('bpb_ceisa')->where('bpbno', $firstBpb)->first();
        $dataDetail = json_decode($ceisaInfo->payload_json ?? '{}', true);


        if(isset($dataDetail['barang']) && count($dataDetail['barang']) > 0){
            $items = collect($dataDetail['barang'])->map(function($b) {
                return (object)[
                    'id_item'         => $b['idItem'] ?? '',
                    'goods_code'      => $b['kodeBarang'] ?? '',
                    'itemdesc'        => $b['uraian'] ?? '',
                    'unit'            => $b['kodeSatuanBarang'] ?? '',
                    'qty'             => $b['jumlahSatuan'] ?? 0,
                    // 'price'           => $b['hargaPenyerahan'] / ($b['jumlahSatuan'] > 0 ? $b['jumlahSatuan'] : 1),
                    'total_harga'     => $b['hargaPenyerahan'] ?? 0,
                ];
            });
        }else{

            $items = $db->table('bppb as a')
                    ->leftJoin('masteritem as mi', 'a.id_item', '=', 'mi.id_item')
                    ->leftJoin('masterstyle as ms', 'a.id_item', '=', 'ms.id_item')
                    ->select(
                        'a.id_item',

                        DB::raw("IF(a.bppbno LIKE '%FG%' OR a.bppbno_int LIKE '%FG%', ms.goods_code, mi.goods_code) as goods_code"),
                        DB::raw("IF(a.bppbno LIKE '%FG%' OR a.bppbno_int LIKE '%FG%', CONCAT(ms.itemname, ' ', IFNULL(ms.color,''), ' ', IFNULL(ms.size,'')), mi.itemdesc) as itemdesc"),

                        DB::raw("MAX(a.unit) as unit"),
                        DB::raw('SUM(a.qty) as qty'),
                        DB::raw('AVG(a.price) as price'),
                        DB::raw('SUM(a.qty * a.price) as total_harga')
                    )
                    ->where(function($query) use ($bppbs) {
                        $query->whereIn('a.bppbno', $bppbs)->orWhereIn('a.bppbno_int', $bppbs);
                    })
                    ->groupBy('a.id_item')
                    ->get();

        }


        $nomorAju = $ceisaInfo->nomor_aju ?? $this->generateNomorAju($db);
        $dokumens = !empty($dataDetail['dok']) ? $dataDetail['dok'] : [
            ['kode' => '', 'nomor' => '', 'tgl' => '']
        ];

        $listTujuanPengiriman = \App\Services\BcReferenceService::getTujuanPengiriman('261');

        $dokumens = !empty($dataDetail['dok']) ? $dataDetail['dok'] : [
            ['kode' => '', 'nomor' => '', 'tgl' => '', 'fasilitas' => '', 'izin' => '', 'kantor' => '']
        ];

        $jaminans = !empty($dataDetail['jaminan']) ? $dataDetail['jaminan'] : [
            ['kodeJenisJaminan' => '', 'nomorJaminan' => '', 'tanggalJaminan' => '', 'nilaiJaminan' => '', 'tanggalJatuhTempo' => '', 'penjamin' => '', 'nomorBpj' => '', 'tanggalBpj' => '']
        ];

        $pengangkuts = !empty($dataDetail['pengangkut']) ? $dataDetail['pengangkut'] : [
            ['kodeCaraAngkut' => '']
        ];

        $kontainers = !empty($dataDetail['kontainer']) ? $dataDetail['kontainer'] : [
            ['nomor' => '', 'ukuran' => '', 'jenis' => '', 'tipe' => '']
        ];

        $kemasans = !empty($dataDetail['kemasan']) ? $dataDetail['kemasan'] : [
            ['jumlah' => '', 'jenis' => '', 'merk' => '']
        ];

        $listJenisJaminan = \App\Services\BcReferenceService::getJenisJaminan();


        return view('export-import.dokumen-pabean.edit-batch-bc261', [
            'page'          => 'dashboard-export-import',
            'subPageGroup'  => 'export-import',
            'subPage'       => 'dokumen-pabean-list',
            'containerFluid'=> true,
            'header'        => $header,
            'ceisaInfo'     => $ceisaInfo,
            'dataDetail'    => $dataDetail,
            'items'         => $items,
            'nomorAju'      => $nomorAju,
            'dokumens'      => $dokumens,
            "batch_id"       => $ids,
            'kantorList'    => $this->getKantorList(),
            'listIncoterm'       => BcReferenceService::getIncoterm(),
            'listSatuanBarang'   => BcReferenceService::getSatuanBarang(),
            'listJenisKemasan'   => BcReferenceService::getJenisKemasan(),
            'referensiDokumen'   => BcReferenceService::getReferensiDokumen(),
            'listValuta'         => BcReferenceService::getValuta(),
            'listKategoriBarang' => BcReferenceService::getKategoriBarang(),
            'listJenisKontainer' => BcReferenceService::getJenisKontainer(),
            'listTipeKontainer'  => BcReferenceService::getTipeKontainer(),
            'listUkuranKontainer'=> BcReferenceService::getUkuranKontainer(),
            'listCaraAngkut' => BcReferenceService::getCaraAngkut(),
            'listJenisKemasan'     => BcReferenceService::getJenisKemasan(),
            'listSatuanBarang'     => BcReferenceService::getSatuanBarang(),
            'referensiDokumen'     => BcReferenceService::getReferensiDokumen(),
            'listValuta'           => BcReferenceService::getValuta(),
            'listIncoterm'         => BcReferenceService::getIncoterm(),
            'listCaraAngkut'       => BcReferenceService::getCaraAngkut(),
            'listCaraDagang'       => BcReferenceService::getCaraDagang(),
            'listCaraBayar'        => BcReferenceService::getCaraPembayaran(),
            'listJenisKontainer'   => BcReferenceService::getJenisKontainer(),
            'listTipeKontainer'    => BcReferenceService::getTipeKontainer(),
            'listUkuranKontainer'  => BcReferenceService::getUkuranKontainer(),
            'listNegara'           => BcReferenceService::getNegara(),
            'listBank'             => BcReferenceService::getBank(),
            'listDaerahAsal'       => BcReferenceService::getDaerahAsal(),
            'listJenisIdentitas'   => BcReferenceService::getJenisIdentitas(),
            'listJenisEkspor'      => BcReferenceService::getJenisEkspor(),
            'listStatusPengusaha'  => BcReferenceService::getStatusPengusaha(),
            'listJenisPengangkutan'=> BcReferenceService::getJenisPengangkutan(),
            'listKategoriKonsolidator' => BcReferenceService::getKategoriKonsolidator(),
            'kantorList'           => BcReferenceService::getKantorPelayanan(),
            'listTujuanPengiriman' => $listTujuanPengiriman,
            'kemasans'             => $kemasans,
            'kontainers'           => $kontainers,
            'pengangkuts'          => $pengangkuts,
            'jaminans'             => $jaminans,
            'dokumens'             => $dokumens,
            'listJenisJaminan'   => $listJenisJaminan,


        ]);
    }

    public function updateDraftBatchBc261($ids, Request $request)
    {
        DB::connection('mysql_sb')->beginTransaction();

        try {
            $bpbs = explode(',', $ids);

            // --- 1. Dokumen ---
            $dokumenInput = $request->input('dok', []);
            $dokumenFiles = $request->file('dok', []);
            $dokumenList  = [];
            $seriDok = 1;

            foreach ($dokumenInput as $index => $d) {
                if (!empty($d['kode']) || !empty($d['nomor'])) {
                    $dokData = [
                        'seriDokumen'    => $seriDok++,
                        'kodeDokumen'    => $d['kode'] ?? '',
                        'nomorDokumen'   => $d['nomor'] ?? '',
                        'tanggalDokumen' => $d['tgl'] ?? date('Y-m-d'),
                        'fasilitas'      => $d['fasilitas'] ?? '',
                        'izin'           => $d['izin'] ?? '',
                        'kantor'         => $d['kantor'] ?? '',
                        'fileName'       => $d['fileName'] ?? null,
                    ];

                    if (isset($dokumenFiles[$index]['file_lampiran'])) {
                        $file = $dokumenFiles[$index]['file_lampiran'];
                        $fileName = 'CEISA_' . str_replace(',', '-', $ids) . '_' . ($d['kode'] ?? 'DOC') . '_' . time() . '.' . $file->getClientOriginalExtension();
                        $destinationPath = public_path('uploads/ceisa');
                        if (!file_exists($destinationPath)) {
                            mkdir($destinationPath, 0755, true);
                        }
                        $file->move($destinationPath, $fileName);
                        $dokData['fileName'] = $fileName;
                        $dokData['urlDokumen'] = url('/uploads/ceisa/' . $fileName);
                    } else {
                        if (!empty($d['old_file'])) {
                            $dokData['fileName'] = $d['old_file'];
                            $dokData['urlDokumen'] = url('/uploads/ceisa/' . $d['old_file']);
                        }
                    }

                    $dokumenList[] = $dokData;
                }
            }

            // --- 2. Kontainer ---
            $kontainerInput = $request->input('kontainer', []);
            $kontainerList  = [];
            $seriKontainer = 1;
            foreach ($kontainerInput as $k) {
                $nomor = $k['nomorKontainer'] ?? '';
                if (!empty($nomor)) {
                    $kontainerList[] = [
                        'seriKontainer'       => $seriKontainer++,
                        'kodeJenisKontainer'  => $k['kodeJenisKontainer'] ?? '',
                        'kodeTipeKontainer'   => $k['kodeTipeKontainer'] ?? '',
                        'kodeUkuranKontainer' => $k['kodeUkuranKontainer'] ?? '',
                        'nomorKontainer'      => $nomor,
                    ];
                }
            }

            // --- 3. Kemasan ---
            $kemasanInput = $request->input('kemasan', []);
            $kemasanList  = [];
            $seriKemasan = 1;
            foreach ($kemasanInput as $k) {
                $jumlah = $k['jumlahKemasan'] ?? 0;
                $jenis  = $k['kodeJenisKemasan'] ?? '';
                if (!empty($jumlah) || !empty($jenis)) {
                    $kemasanList[] = [
                        'seriKemasan'      => $seriKemasan++,
                        'jumlahKemasan'    => (float) $jumlah,
                        'kodeJenisKemasan' => $jenis,
                        'merkKemasan'      => $k['merkKemasan'] ?? '-',
                    ];
                }
            }

            // --- 4. Pengangkut ---
            $pengangkutInput = $request->input('pengangkut', []);
            $pengangkutList  = [];
            $seriPengangkut = 1;
            foreach ($pengangkutInput as $p) {
                if (!empty($p['kodeCaraAngkut'])) {
                    $pengangkutList[] = [
                        'seriPengangkut' => $seriPengangkut++,
                        'kodeCaraAngkut' => $p['kodeCaraAngkut'] ?? '',
                        'kodeBendera'    => $p['kodeBendera'] ?? 'ID',
                    ];
                }
            }

            // --- 5. Jaminan ---
            $jaminanInput = $request->input('jaminan', []);
            $jaminanList  = [];
            foreach ($jaminanInput as $j) {
                if (!empty($j['kodeJenisJaminan'])) {
                    $jaminanList[] = [
                        'kodeJenisJaminan'  => $j['kodeJenisJaminan'] ?? '',
                        'nomorJaminan'      => $j['nomorJaminan'] ?? '',
                        'tanggalJaminan'    => $j['tanggalJaminan'] ?? date('Y-m-d'),
                        'nilaiJaminan'      => (float) ($j['nilaiJaminan'] ?? 0),
                        'tanggalJatuhTempo' => $j['tanggalJatuhTempo'] ?? date('Y-m-d'),
                        'penjamin'          => $j['penjamin'] ?? '',
                        'nomorBpj'          => $j['nomorBpj'] ?? '',
                        'tanggalBpj'        => $j['tanggalBpj'] ?? date('Y-m-d'),
                    ];
                }
            }

            // --- 6. Barang & Bahan Baku ---
            $barangInput  = $request->input('barang', []);
            $barangList   = [];
            $kantorPabean = $request->input('kantorPabean');

            foreach ($barangInput as $index => $b) {

                $bahanBakuInput = $b['bahanBaku'] ?? [];
                $bahanBakuList  = [];
                $seriBahanBaku  = 1;

                foreach ($bahanBakuInput as $bb) {
                    if (!empty($bb['hs']) || !empty($bb['uraian'])) {
                        $bahanBakuList[] = [
                            'cif'                   => (float) ($bb['nilaiBarang'] ?? 0),
                            'cifRupiah'             => 0,
                            'hargaPenyerahan'       => 0,
                            'hargaPerolehan'        => 0,
                            'jumlahSatuan'          => (float) ($b['jumlahSatuan'] ?? 0),
                            'kodeSatuanBarang'      => $bb['kodeSatuan'] ?? '',
                            'kodeAsalBahanBaku'     => $bb['kodeAsalBahanBaku'] ?? '0',
                            'kodeBarang'            => $b['kodeBarang'] ?? '',
                            'kodeDokAsal'           => $bb['kodeDokAsal'] ?? '',
                            'kodeDokumen'           => $bb['kodeDokumen'] ?? '',
                            'kodeKantor'            => $kantorPabean ?? '',
                            'merkBarang'            => $b['merk'] ?? '-',
                            'ndpbm'                 => 0,
                            'netto'                 => (float) ($b['netto'] ?? 0),
                            'nomorDaftarDokAsal'    => $bb['nomorDaftarDokAsal'] ?? '',
                            'posTarif'              => $bb['hs'] ?? '',
                            'seriBahanBaku'         => $seriBahanBaku,
                            'seriBarang'            => (int) ($b['seriBarang'] ?? ($index + 1)),
                            'seriBarangDokAsal'     => 1,
                            'seriIjin'              => 1,
                            'spesifikasiLainBarang' => '-',
                            'tanggalDaftarDokAsal'  => date('Y-m-d'),
                            'tipeBarang'            => $b['tipe'] ?? '-',
                            'ukuranBarang'          => $b['ukuran'] ?? '-',
                            'uraianBarang'          => $bb['uraian'] ?? '',
                            'nilaiJasa'             => 0,
                            'flagTis'               => '0',
                            'bahanBakuTarif'        => [],
                        ];

                        $seriBahanBaku++;
                    }
                }

                $cifBarang = (float) ($b['cif'] ?? 0);

                $barangList[] = [
                    'seriBarang'        => (int) ($b['seriBarang'] ?? ($index + 1)),
                    'kodeBarang'        => $b['kodeBarang'] ?? '',
                    'uraian'            => $b['uraian'] ?? '',
                    'merk'              => $b['merk'] ?? '',
                    'tipe'              => $b['tipe'] ?? '',
                    'ukuran'            => $b['ukuran'] ?? '',
                    'spesifikasiLain'   => $b['spesifikasiLain'] ?? '-',
                    'posTarif'          => $b['posTarif'] ?? '',
                    'kodeNegaraAsal'    => $b['kodeNegaraAsal'] ?? '',
                    'kodeAsalBarang'    => $b['kodeAsalBarang'] ?? '0',
                    'jumlahSatuan'      => (float) ($b['jumlahSatuan'] ?? 0),
                    'kodeSatuanBarang'  => $b['kodeSatuanBarang'] ?? '',
                    'jumlahKemasan'     => (float) ($b['jumlahKemasan'] ?? 0),
                    'kodeJenisKemasan'  => $b['kodeJenisKemasan'] ?? '',
                    'netto'             => (float) ($b['netto'] ?? 0),
                    'cif'               => $cifBarang,
                    'nilaiBarang'       => (float) ($b['nilaiBarang'] ?? $cifBarang),

                    'cifRupiah'         => (float) ($b['cifRupiah'] ?? 0),
                    'hargaEkspor'       => (float) ($b['hargaEkspor'] ?? 0),
                    'hargaPenyerahan'   => (float) ($b['hargaPenyerahan'] ?? 0),
                    'hargaPerolehan'    => (float) ($b['hargaPerolehan'] ?? 0),
                    'isiPerKemasan'     => (float) ($b['isiPerKemasan'] ?? 0),
                    'kodeAsalBahanBaku' => $b['kodeAsalBahanBaku'] ?? '0',
                    'kodeDokumen'       => $b['kodeDokumen'] ?? '23',
                    'ndpbm'             => (float) ($b['ndpbm'] ?? 0),
                    'uangMuka'          => (float) ($b['uangMuka'] ?? 0),
                    'nilaiJasa'         => (float) ($b['nilaiJasa'] ?? 0),

                    'bahanBaku'         => $bahanBakuList,
                ];
            }

            $draft = [
                'kantorPabean'     => $kantorPabean,
                'tujuanPengiriman' => $request->input('tujuanPengiriman'),
                'entitas'          => $request->input('entitas', []),
                'valuta'           => $request->input('valuta'),
                'ndpbm'            => (float) $request->input('ndpbm'),
                'nilaiCif'         => (float) $request->input('nilaiCif'),
                'nilaiPabean'      => (float) $request->input('nilaiPabean'),
                'bruto'            => (float) $request->input('bruto', 0),
                'netto'            => (float) $request->input('netto', 0),
                'pungutan'         => $request->input('pungutan', []),
                'dok'              => $dokumenList,
                'kontainer'        => $kontainerList,
                'kemasan'          => $kemasanList,
                'pengangkut'       => $pengangkutList,
                'barang'           => $barangList,
                'jaminan'          => $jaminanList,
                'tempatTtd'        => $request->input('tempatTtd'),
                'tanggalTtd'       => $request->input('tanggalTtd'),
                'namaTtd'          => $request->input('namaTtd'),
                'jabatanTtd'       => $request->input('jabatanTtd'),
            ];

            $inputNomorAju = $request->input('nomorAju', '');
            $payloadJson   = json_encode($draft);

            foreach ($bpbs as $id) {
                $headerBpb = DB::connection('mysql_sb')->table('bppb')->where(function ($query) use ($id) {
                    $query->where('bppbno', $id)->orWhere('bppbno_int', $id);
                })->first();

                $realBpbno    = $headerBpb ? $headerBpb->bppbno : $id;
                $realBpbnoInt = $headerBpb ? $headerBpb->bppbno_int : '';

                $ceisaRec = DB::connection('mysql_sb')->table('bpb_ceisa')
                    ->where('bpbno', $id)->orWhere('bpbno_int', $id)->first();

                if ($ceisaRec) {
                    DB::connection('mysql_sb')->table('bpb_ceisa')
                        ->where('id', $ceisaRec->id)
                        ->update([
                            'bpbno'            => $realBpbno,
                            'bpbno_int'        => $realBpbnoInt,
                            'nomor_aju'        => $inputNomorAju ?: $ceisaRec->nomor_aju,
                            'payload_json'     => $payloadJson,
                            'jenis_bc'         => '2.6.1',
                            'is_batch'         => 1,
                            'no_dokumen_merge' => $request->input('no_dokumen_merge', ''),
                            'updated_at'       => Carbon::now()
                        ]);
                } else {
                    DB::connection('mysql_sb')->table('bpb_ceisa')->insert([
                        'bpbno'            => $realBpbno,
                        'bpbno_int'        => $realBpbnoInt,
                        'nomor_aju'        => $inputNomorAju,
                        'jenis_bc'         => '2.6.1',
                        'payload_json'     => $payloadJson,
                        'status'           => 0,
                        'is_batch'         => 1,
                        'no_dokumen_merge' => $request->input('no_dokumen_merge', ''),
                        'created_at'       => Carbon::now(),
                        'updated_at'       => Carbon::now()
                    ]);
                }
            }

            DB::connection('mysql_sb')->commit();

            return response()->json([
                'status'  => 200,
                'message' => 'Draft BC 2.6.1 berhasil disimpan.'
            ]);

        } catch (\Exception $e) {
            DB::connection('mysql_sb')->rollBack();
            Log::error('Error Update Draft Batch BC 2.6.1: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json([
                'status'  => 500,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function sendCeisaBatch261(array $bppbs, Request $request)
    {
        $db = DB::connection('mysql_sb');
        $firstBppb = $bppbs[0];
        try {
            $draftRow = $db->table('bpb_ceisa')->where('bpbno', $firstBppb)->orWhere('bpbno_int', $firstBppb)->first();
            if (!$draftRow || empty($draftRow->payload_json)) {
                throw new \Exception('Draft kosong. Simpan draft terlebih dahulu.');
            }

            $draft = json_decode($draftRow->payload_json, true);
            $nomorAju = $draftRow->nomor_aju ?? $this->generateNomorAju($db);

            // --- Dokumen ---
            $payloadDokumen = [];
            foreach (($draft['dok'] ?? []) as $index => $d) {
                $docItem = [
                    'idDokumen'      => "DOK" . str_pad($index + 1, 4, "0", STR_PAD_LEFT),
                    'kodeDokumen'    => $d['kodeDokumen'] ?? $d['kode'] ?? '',
                    'nomorDokumen'   => $d['nomorDokumen'] ?? $d['nomor'] ?? '',
                    'seriDokumen'    => (int) ($d['seriDokumen'] ?? ($index + 1)),
                    'tanggalDokumen' => $d['tanggalDokumen'] ?? $d['tgl'] ?? date('Y-m-d')
                ];
                if (!empty($d['fasilitas'])) {
                    $docItem['kodeFasilitas'] = substr($d['fasilitas'], 0, 2);
                }
                $payloadDokumen[] = $docItem;
            }

            // --- Entitas ---
            $entitasDraft = $draft['entitas'] ?? [];
            $payloadEntitas = [];

            if (!empty($entitasDraft['tpb'])) {
                $e = $entitasDraft['tpb'];
                $payloadEntitas[] = [
                    'seriEntitas'        => 1,
                    'kodeEntitas'        => '3',
                    'kodeJenisApi'       => $e['jenisApi'] ?? '02',
                    'kodeJenisIdentitas' => !empty($e['nomorIdentitas']) && strlen(str_replace(['.', '-'], '', $e['nomorIdentitas'])) == 16 ? '6' : '5',
                    'kodeStatus'         => $e['status'] ?? '5',
                    'namaEntitas'        => $e['namaEntitas'] ?? $e['nama'] ?? '',
                    'nibEntitas'         => $e['nibEntitas'] ?? $e['nib'] ?? '',
                    'nomorIdentitas'     => str_replace(['.', '-'], '', $e['nomorIdentitas'] ?? ''),
                    'nomorIjinEntitas'   => $e['nomorIjinEntitas'] ?? $e['nomorIjin'] ?? '',
                    'alamatEntitas'      => $e['alamatEntitas'] ?? $e['alamat'] ?? '',
                    'tanggalIjinEntitas' => $e['tanggalIjinEntitas'] ?? $e['tanggalIjin'] ?? date('Y-m-d')
                ];
            }

            if (!empty($entitasDraft['pemilik'])) {
                $e = $entitasDraft['pemilik'];
                $payloadEntitas[] = [
                    'seriEntitas'        => 2,
                    'kodeEntitas'        => '7',
                    'kodeJenisApi'       => $e['jenisApi'] ?? '02',
                    'kodeJenisIdentitas' => !empty($e['nomorIdentitas']) && strlen(str_replace(['.', '-'], '', $e['nomorIdentitas'])) == 16 ? '6' : '5',
                    'kodeStatus'         => $e['status'] ?? '5',
                    'namaEntitas'        => $e['namaEntitas'] ?? $e['nama'] ?? '',
                    'nibEntitas'         => $e['nibEntitas'] ?? $e['nib'] ?? '',
                    'nomorIdentitas'     => str_replace(['.', '-'], '', $e['nomorIdentitas'] ?? ''),
                    'nomorIjinEntitas'   => $e['nomorIjinEntitas'] ?? $e['nomorIjin'] ?? '',
                    'alamatEntitas'      => $e['alamatEntitas'] ?? $e['alamat'] ?? '',
                    'tanggalIjinEntitas' => $e['tanggalIjinEntitas'] ?? $e['tanggalIjin'] ?? date('Y-m-d')
                ];
            }

            if (!empty($entitasDraft['penerima'])) {
                $e = $entitasDraft['penerima'];
                $payloadEntitas[] = [
                    'seriEntitas'        => 3,
                    'kodeEntitas'        => '8',
                    'kodeJenisApi'       => $e['jenisApi'] ?? '02',
                    'kodeJenisIdentitas' => !empty($e['nomorIdentitas']) && strlen(str_replace(['.', '-'], '', $e['nomorIdentitas'])) == 16 ? '6' : '5',
                    'kodeStatus'         => $e['status'] ?? '5',
                    'namaEntitas'        => $e['namaEntitas'] ?? $e['nama'] ?? '',
                    'nibEntitas'         => $e['nibEntitas'] ?? $e['nib'] ?? '',
                    'nomorIdentitas'     => str_replace(['.', '-'], '', $e['nomorIdentitas'] ?? ''),
                    'nomorIjinEntitas'   => $e['nomorIjinEntitas'] ?? $e['nomorIjin'] ?? '',
                    'alamatEntitas'      => $e['alamatEntitas'] ?? $e['alamat'] ?? '',
                    'tanggalIjinEntitas' => $e['tanggalIjinEntitas'] ?? $e['tanggalIjin'] ?? date('Y-m-d')
                ];
            }

            // --- Kemasan ---
            $payloadKemasan = [];
            foreach (($draft['kemasan'] ?? []) as $index => $k) {
                $payloadKemasan[] = [
                    'jumlahKemasan'    => (float) ($k['jumlahKemasan'] ?? 0),
                    'kodeJenisKemasan' => $k['kodeJenisKemasan'] ?? '',
                    'merkKemasan'      => $k['merkKemasan'] ?? '-',
                    'seriKemasan'      => (int) ($k['seriKemasan'] ?? ($index + 1)),
                ];
            }

            // --- Kontainer ---
            $payloadKontainer = [];
            foreach (($draft['kontainer'] ?? []) as $index => $k) {
                $payloadKontainer[] = [
                    'kodeJenisKontainer'  => $k['kodeJenisKontainer'] ?? '',
                    'kodeTipeKontainer'   => $k['kodeTipeKontainer'] ?? '',
                    'kodeUkuranKontainer' => $k['kodeUkuranKontainer'] ?? '',
                    'nomorKontainer'      => $k['nomorKontainer'] ?? '',
                    'seriKontainer'       => (int) ($k['seriKontainer'] ?? ($index + 1)),
                ];
            }

            // --- Pengangkut ---
            $payloadPengangkut = [];
            foreach (($draft['pengangkut'] ?? []) as $index => $p) {
                $payloadPengangkut[] = [
                    'idPengangkut'   => "ANG" . str_pad($index + 1, 4, "0", STR_PAD_LEFT),
                    'kodeCaraAngkut' => $p['kodeCaraAngkut'] ?? '',
                    'seriPengangkut' => (int) ($p['seriPengangkut'] ?? ($index + 1)),
                ];
            }

            // --- Jaminan ---
            $payloadJaminan = [];
            foreach (($draft['jaminan'] ?? []) as $index => $j) {
                $payloadJaminan[] = [
                    'idJaminan'         => "JAM" . str_pad($index + 1, 4, "0", STR_PAD_LEFT),
                    'nomorBpj'          => $j['nomorBpj'] ?? '',
                    'tanggalBpj'        => $j['tanggalBpj'] ?? date('Y-m-d'),
                    'kodeJenisJaminan'  => $j['kodeJenisJaminan'] ?? '',
                    'nomorJaminan'      => $j['nomorJaminan'] ?? '',
                    'tanggalJaminan'    => $j['tanggalJaminan'] ?? date('Y-m-d'),
                    'tanggalJatuhTempo' => $j['tanggalJatuhTempo'] ?? date('Y-m-d'),
                    'penjamin'          => $j['penjamin'] ?? '',
                    'nilaiJaminan'      => (float) ($j['nilaiJaminan'] ?? 0),
                ];
            }

            // --- Barang ---
            $payloadBarang = [];
            foreach (($draft['barang'] ?? []) as $index => $b) {

                $payloadBahanBaku = [];
                foreach (($b['bahanBaku'] ?? []) as $bb) {

                    $payloadBahanBakuTarif = [];
                    foreach (($bb['bahanBakuTarif'] ?? []) as $bbt) {
                        $payloadBahanBakuTarif[] = [
                            'seriBahanBaku'      => (int) ($bbt['seriBahanBaku'] ?? 1),
                            'kodeJenisPungutan'  => $bbt['kodeJenisPungutan'] ?? '',
                            'kodeAsalBahanBaku'  => $bbt['kodeAsalBahanBaku'] ?? '0',
                            'kodeFasilitasTarif' => $bbt['kodeFasilitasTarif'] ?? '',
                            'kodeSatuanBarang'   => $bbt['kodeSatuanBarang'] ?? '',
                            'kodeJenisTarif'     => $bbt['kodeJenisTarif'] ?? '1',
                            'nilaiBayar'         => round((float) ($bbt['nilaiBayar'] ?? 0), 2),
                            'nilaiSudahDilunasi' => round((float) ($bbt['nilaiSudahDilunasi'] ?? 0), 2),
                            'nilaiFasilitas'     => round((float) ($bbt['nilaiFasilitas'] ?? 0), 2),
                            'jumlahSatuan'       => (float) ($bbt['jumlahSatuan'] ?? 0),
                            'jumlahKemasan'      => (float) ($bbt['jumlahKemasan'] ?? 0),
                            'tarif'              => round((float) ($bbt['tarif'] ?? 0), 2),
                            'tarifFasilitas'     => round((float) ($bbt['tarifFasilitas'] ?? 0), 2)
                        ];
                    }

                    $payloadBahanBaku[] = [
                        'cif'                   => round((float) ($bb['cif'] ?? 0), 2),
                        'cifRupiah'             => round((float) ($bb['cifRupiah'] ?? 0), 2),
                        'hargaPenyerahan'       => round((float) ($bb['hargaPenyerahan'] ?? 0), 2),
                        'hargaPerolehan'        => round((float) ($bb['hargaPerolehan'] ?? 0), 2),
                        'jumlahSatuan'          => (float) ($bb['jumlahSatuan'] ?? 0),
                        'kodeSatuanBarang'      => $bb['kodeSatuanBarang'] ?? '',
                        'kodeAsalBahanBaku'     => $bb['kodeAsalBahanBaku'] ?? '0',
                        'kodeBarang'            => $bb['kodeBarang'] ?? '',
                        'kodeDokAsal'           => $bb['kodeDokAsal'] ?? '',
                        'kodeDokumen'           => $bb['kodeDokumen'] ?? '',
                        'kodeKantor'            => $bb['kodeKantor'] ?? '',
                        'merkBarang'            => $bb['merkBarang'] ?? '',
                        'ndpbm'                 => round((float) ($bb['ndpbm'] ?? 0), 2),
                        'netto'                 => (float) ($bb['netto'] ?? 0),
                        'nomorDaftarDokAsal'    => $bb['nomorDaftarDokAsal'] ?? '',
                        'posTarif'              => $bb['posTarif'] ?? '',
                        'seriBahanBaku'         => (int) ($bb['seriBahanBaku'] ?? 1),
                        'seriBarang'            => (int) ($bb['seriBarang'] ?? 1),
                        'seriBarangDokAsal'     => (int) ($bb['seriBarangDokAsal'] ?? 1),
                        'seriIjin'              => (int) ($bb['seriIjin'] ?? 1),
                        'spesifikasiLainBarang' => $bb['spesifikasiLainBarang'] ?? '-',
                        'tanggalDaftarDokAsal'  => $bb['tanggalDaftarDokAsal'] ?? date('Y-m-d'),
                        'tipeBarang'            => $bb['tipeBarang'] ?? '-',
                        'ukuranBarang'          => $bb['ukuranBarang'] ?? '-',
                        'uraianBarang'          => $bb['uraianBarang'] ?? '',
                        'nilaiJasa'             => round((float) ($bb['nilaiJasa'] ?? 0), 2),
                        'flagTis'               => $bb['flagTis'] ?? '0',
                        'bahanBakuTarif'        => $payloadBahanBakuTarif
                    ];
                }

                $cifBarangItem = round((float) ($b['cif'] ?? 0), 2);

                $payloadBarang[] = [
                    'cif'               => $cifBarangItem,
                    'cifRupiah'         => round((float) ($b['cifRupiah'] ?? 0), 2),
                    'hargaEkspor'       => round((float) ($b['hargaEkspor'] ?? 0), 2),
                    'hargaPenyerahan'   => round((float) ($b['hargaPenyerahan'] ?? 0), 2),
                    'hargaPerolehan'    => round((float) ($b['hargaPerolehan'] ?? 0), 2),
                    'isiPerKemasan'     => (float) ($b['isiPerKemasan'] ?? 0),
                    'jumlahKemasan'     => (float) ($b['jumlahKemasan'] ?? 0),
                    'jumlahSatuan'      => (float) ($b['jumlahSatuan'] ?? 0),
                    'kodeAsalBahanBaku' => $b['kodeAsalBahanBaku'] ?? '0',
                    'kodeAsalBarang'    => $b['kodeAsalBarang'] ?? '0',
                    'kodeBarang'        => $b['kodeBarang'] ?? '',
                    'kodeDokumen'       => $b['kodeDokumen'] ?? '20',
                    'kodeJenisKemasan'  => $b['kodeJenisKemasan'] ?? '',
                    'kodeNegaraAsal'    => $b['kodeNegaraAsal'] ?? '',
                    'kodeSatuanBarang'  => $b['kodeSatuanBarang'] ?? '',
                    'merk'              => $b['merk'] ?? '-',
                    'ndpbm'             => round((float) ($b['ndpbm'] ?? 0), 2),
                    'netto'             => (float) ($b['netto'] ?? 0),
                    'nilaiBarang'       => round((float) ($b['nilaiBarang'] ?? $cifBarangItem), 2),
                    'nilaiJasa'         => round((float) ($b['nilaiJasa'] ?? 0), 2),
                    'posTarif'          => $b['posTarif'] ?? '',
                    'seriBarang'        => (int) ($b['seriBarang'] ?? ($index + 1)),
                    'spesifikasiLain'   => $b['spesifikasiLain'] ?? '-',
                    'tipe'              => $b['tipe'] ?? '-',
                    'uangMuka'          => round((float) ($b['uangMuka'] ?? 0), 2),
                    'ukuran'            => $b['ukuran'] ?? '-',
                    'uraian'            => $b['uraian'] ?? '',

                    'bahanBaku'         => $payloadBahanBaku
                ];
            }

            // --- Pungutan ---
            $payloadPungutan = [];
            foreach (($draft['pungutan'] ?? []) as $index => $p) {
                if (!empty($p['kodeJenisPungutan'])) {
                    $payloadPungutan[] = [
                        'idPungutan'         => "PUN" . str_pad($index + 1, 4, "0", STR_PAD_LEFT),
                        'kodeFasilitasTarif' => $p['kodeFasilitasTarif'] ?? '1',
                        'kodeJenisPungutan'  => $p['kodeJenisPungutan'] ?? '',
                        'nilaiPungutan'      => round((float) ($p['nilaiPungutan'] ?? 0), 2),
                    ];
                }
            }

            // --- Hitung ulang CIF & Nilai Barang header dari gabungan semua barang (batch) ---
            $totalCifBarang = 0;
            foreach ($payloadBarang as $b) {
                $totalCifBarang += $b['cif'];
            }
            $totalCifBarang = round($totalCifBarang, 2);

            $cifHeader = $totalCifBarang > 0 ? $totalCifBarang : round((float) ($draft['nilaiCif'] ?? 0), 2);

            $nilaiBarangHeader = round((float) ($draft['nilaiPabean'] ?? 0), 2);
            if ($nilaiBarangHeader <= 0 && $totalCifBarang > 0) {
                $nilaiBarangHeader = $totalCifBarang;
            }

            $finalPayload = [
                'asalData'             => 'S',
                'asuransi'             => 0,
                'biayaTambahan'        => 0,
                'biayaPengurang'       => 0,
                'bruto'                => round((float) ($draft['bruto'] ?? 0), 2),
                'cif'                  => $cifHeader,
                'disclaimer'           => "1",
                'freight'              => 0,
                'hargaPenyerahan'      => 0,
                'jabatanTtd'           => $draft['jabatanTtd'] ?? '-',
                'jumlahKontainer'      => count($payloadKontainer),
                'kodeDokumen'          => '261',
                'kodeKantor'           => $draft['kantorPabean'] ?? '',
                'kodeTujuanPengiriman' => $draft['tujuanPengiriman'] ?? '',
                'kodeValuta'           => $draft['valuta'] ?? 'IDR',
                'kotaTtd'              => $draft['tempatTtd'] ?? '-',
                'namaTtd'              => $draft['namaTtd'] ?? '-',
                'ndpbm'                => round((float) ($draft['ndpbm'] ?? 0), 2),
                'netto'                => (float) ($draft['netto'] ?? 0),
                'nik'                  => '',
                'nilaiBarang'          => $nilaiBarangHeader,
                'nomorAju'             => $nomorAju,
                'seri'                 => 0,
                'tanggalAju'           => date('Y-m-d'),
                'tanggalTtd'           => $draft['tanggalTtd'] ?? date('Y-m-d'),
                'tempatStuffing'       => '',
                'tglAkhirBerlaku'      => date('Y-m-d'),
                'tglAwalBerlaku'       => date('Y-m-d'),
                'totalDanaSawit'       => 0,
                'uangMuka'             => 0,
                'vd'                   => 0,

                'barang'               => $payloadBarang,
                'dokumen'              => $payloadDokumen,
                'entitas'              => $payloadEntitas,
                'jaminan'              => $payloadJaminan,
                'kemasan'              => $payloadKemasan,
                'kontainer'            => $payloadKontainer,
                'pengangkut'           => $payloadPengangkut,
                'pungutan'             => $payloadPungutan,
            ];

            foreach (['tanggalTtd', 'tglAkhirBerlaku', 'tglAwalBerlaku'] as $dateField) {
                if (isset($finalPayload[$dateField]) && empty($finalPayload[$dateField])) {
                    unset($finalPayload[$dateField]);
                }
            }

            Log::info('Kirim BC 2.6.1 CEISA Payload: ', $finalPayload);

            $responseCeisa = $this->ceisaService->kirimDokumenBatch262($finalPayload);

            if ($responseCeisa['successful']) {
                foreach ($bppbs as $no_bppb) {

                    $data_kantor = $db->table('master_kantor')
                                    ->where('kode', $draft['kantorPabean'] ?? '')
                                    ->get()->first();

                    $kantor = 60;
                    if ($data_kantor) {
                        $kantor = $data_kantor->id;
                    }

                    $db->table('bppb')->where('bppbno', $no_bppb)->orWhere('bppbno_int', $no_bppb)->update([
                        'nomor_aju'   => $nomorAju,
                        'tanggal_aju' => date('Y-m-d'),
                        'bcdate'      => date('Y-m-d'),
                        'kode_kantor' => $kantor,
                    ]);

                    $db->table('bpb_ceisa')->where('bpbno', $no_bppb)->update([
                        'nomor_aju'   => $nomorAju,
                        'tanggal_aju' => date('Y-m-d'),
                        'status'      => 1,
                        'jenis_bc'    => '2.6.1',
                        'updated_at'  => \Carbon\Carbon::now()
                    ]);
                }
                return response()->json([
                    'status'         => 200,
                    'message'        => 'Dokumen BC 2.6.1 berhasil dikirim ke CEISA!',
                    'data_payload'   => $finalPayload,
                    'ceisa_response' => $responseCeisa['body']
                ]);
            } else {
                return response()->json([
                    'status'      => $responseCeisa['status_code'],
                    'message'     => 'Gagal mengirim ke CEISA.',
                    'ceisa_error' => $responseCeisa['body'],
                ], $responseCeisa['status_code']);
            }

        } catch (\Exception $e) {
            Log::error('Error Send CEISA Batch BC 2.6.1: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json([
                'status'  => 500,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
