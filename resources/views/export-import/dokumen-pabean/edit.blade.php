@extends('layouts.index')

@section('custom-link')
<link rel="stylesheet" href="{{ asset('plugins/select2/css/select2.min.css') }}">
<link rel="stylesheet" href="{{ asset('plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}">
<style>
    .nav-tabs { border-bottom: none; }
    .nav-tabs .nav-item { margin-bottom: 0; margin-right: 5px; }
    .nav-tabs .nav-link {
        border: 1px solid #ddd;
        border-radius: 4px;
        padding: 8px 15px;
        font-size: 13px;
        transition: all 0.3s ease;
    }
    .nav-tabs .nav-link.active {
        font-weight: bold;
        background-color: #003366 !important;
        color: #ffffff !important;
        border-color: #003366 !important;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    .nav-tabs .nav-link.active::after { display: none; }
    .nav-tabs .nav-link:not(.active) {
        color: #000000 !important;
        background-color: #ffffff;
    }
    .nav-tabs .nav-link:not(.active):hover {
        background-color: #f8f9fa;
        border-color: #ddd;
        color: #000000 !important;
    }
    .form-group label {
        font-size: 13px;
        font-weight: 600;
        margin-bottom: 0.2rem;
    }
    .form-control-sm { font-size: 13px; }
    .section-title {
        font-size: 14px;
        font-weight: bold;
        color: #333;
        border-bottom: 2px solid #ddd;
        padding-bottom: 5px;
        margin-bottom: 15px;
        margin-top: 20px;
    }
</style>
@endsection

@section('content')
<div class="card card-sb">
    <div class="card-header">
        <h5 class="card-title fw-bold mb-0">
            <i class="fas fa-edit"></i> Edit Dokumen Pabean
        </h5>
    </div>

    <form action="{{ route('dokumen-pabean-update_draft', $header->bpbno) }}" method="POST" id="form-edit-ceisa">
        @csrf
        @method('PUT')
        <div class="card-body">
            <div class="alert alert-info py-2 mb-4">
                <strong>No. Transaksi:</strong> {{ $header->trx_no_par }} |
                <strong>Supplier:</strong> {{ $header->supplier ?? '-' }}
                <input type="hidden" name="bpbno_int" value="{{ $header->bpbno_int }}">
            </div>

            <ul class="nav nav-tabs" id="ceisaTab" role="tablist">
                <li class="nav-item">
                    <a class="nav-link active" id="header-tab" data-toggle="tab" href="#tab-header" role="tab">Data Header & Nilai</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="barang-tab" data-toggle="tab" href="#tab-barang" role="tab">Daftar Barang</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="entitas-tab" data-toggle="tab" href="#tab-entitas" role="tab">Entitas</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="pendukung-tab" data-toggle="tab" href="#tab-pendukung" role="tab">Pendukung (Dokumen, Kemasan)</a>
                </li>
            </ul>

            <div class="tab-content mt-3" id="ceisaTabContent">

                <div class="tab-pane fade show active" id="tab-header" role="tabpanel">
                    <div class="section-title">Data Pengajuan</div>
                    <div class="row">
                        <div class="col-md-3 form-group">
                            <label>Nomor Aju</label>
                            <input type="text" name="nomorAju" class="form-control form-control-sm fw-bold" value="{{ $nomorAju }}">
                        </div>
                        <div class="col-md-3 form-group">
                            <label>Tanggal Aju</label>
                            <input type="date" name="tanggalAju" class="form-control form-control-sm" value="{{ $ceisaInfo->tanggal_aju ?? $header->tanggal_aju ?? date('Y-m-d') }}">
                        </div>
                        <div class="col-md-3 form-group">
                            <label>Kode Kantor</label>
                            <input type="text" name="kodeKantor" class="form-control form-control-sm" value="{{ $dataDetail['kodeKantor'] ?? '050100' }}">
                        </div>
                        <div class="col-md-3 form-group">
                            <label>Tujuan Pengiriman</label>
                            @php
                                $listTujuan = [
                                    '1' => 'PENYERAHAN BKP', '2' => 'PENYERAHAN JKP', '3' => 'RETUR',
                                    '4' => 'NON PENYERAHAN', '5' => 'LAINNYA'
                                ];
                                $tujuanTerpilih = $dataDetail['kodeTujuanPengiriman'] ?? '1';
                            @endphp
                            <select name="kodeTujuanPengiriman" class="form-control form-control-sm select2bs4">
                                <option value="">Pilih Tujuan Pengiriman</option>
                                @foreach($listTujuan as $key => $text)
                                    <option value="{{ $key }}" {{ $tujuanTerpilih == $key ? 'selected' : '' }}>{{ $key }} - {{ $text }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="section-title">Data Nilai & Fisik</div>
                    <div class="row">
                        <div class="col-md-2 form-group"><label>Bruto</label><input type="number" step="0.0001" name="bruto" class="form-control form-control-sm" value="{{ $dataDetail['bruto'] ?? $header->berat_kotor ?? 0 }}"></div>
                        <div class="col-md-2 form-group"><label>Netto</label><input type="number" step="0.0001" name="netto" class="form-control form-control-sm" value="{{ $dataDetail['netto'] ?? $header->berat_bersih ?? 0 }}"></div>
                        <div class="col-md-2 form-group"><label>Volume</label><input type="number" step="0.0001" name="volume" class="form-control form-control-sm" value="{{ $dataDetail['volume'] ?? 0 }}"></div>
                        <div class="col-md-3 form-group"><label>Harga Penyerahan</label><input type="number" step="0.01" name="hargaPenyerahan" class="form-control form-control-sm" value="{{ $dataDetail['hargaPenyerahan'] ?? 0 }}"></div>
                        <div class="col-md-3 form-group"><label>CIF</label><input type="number" step="0.01" name="cif" class="form-control form-control-sm" value="{{ $dataDetail['cif'] ?? 0 }}"></div>
                    </div>

                    <div class="row">
                        <div class="col-md-2 form-group"><label>Asuransi</label><input type="number" step="0.01" name="asuransi" class="form-control form-control-sm" value="{{ $dataDetail['asuransi'] ?? 0 }}"></div>
                        <div class="col-md-2 form-group"><label>Freight</label><input type="number" step="0.01" name="freight" class="form-control form-control-sm" value="{{ $dataDetail['freight'] ?? 0 }}"></div>
                        <div class="col-md-2 form-group"><label>Biaya Tambahan</label><input type="number" step="0.01" name="biayaTambahan" class="form-control form-control-sm" value="{{ $dataDetail['biayaTambahan'] ?? 0 }}"></div>
                        <div class="col-md-2 form-group"><label>Biaya Pengurang</label><input type="number" step="0.01" name="biayaPengurang" class="form-control form-control-sm" value="{{ $dataDetail['biayaPengurang'] ?? 0 }}"></div>
                        <div class="col-md-2 form-group"><label>Uang Muka</label><input type="number" step="0.01" name="uangMuka" class="form-control form-control-sm" value="{{ $dataDetail['uangMuka'] ?? 0 }}"></div>
                        <div class="col-md-2 form-group"><label>Nilai Jasa</label><input type="number" step="0.01" name="nilaiJasa" class="form-control form-control-sm" value="{{ $dataDetail['nilaiJasa'] ?? 0 }}"></div>
                    </div>

                    <div class="section-title">Penandatangan</div>
                    <div class="row">
                        <div class="col-md-3 form-group"><label>Nama TTD</label><input type="text" name="namaTtd" class="form-control form-control-sm" value="{{ $dataDetail['namaTtd'] ?? '' }}"></div>
                        <div class="col-md-3 form-group"><label>Jabatan</label><input type="text" name="jabatanTtd" class="form-control form-control-sm" value="{{ $dataDetail['jabatanTtd'] ?? '' }}"></div>
                        <div class="col-md-3 form-group"><label>Kota TTD</label><input type="text" name="kotaTtd" class="form-control form-control-sm" value="{{ $dataDetail['kotaTtd'] ?? '' }}"></div>
                        <div class="col-md-3 form-group"><label>Tanggal TTD</label><input type="date" name="tanggalTtd" class="form-control form-control-sm" value="{{ $dataDetail['tanggalTtd'] ?? date('Y-m-d') }}"></div>
                    </div>
                </div>

                <div class="tab-pane fade" id="tab-barang" role="tabpanel">
                    <div class="section-title"><i class="fas fa-boxes"></i> Rincian Barang ({{ count($items) }} Item)</div>

                    <div class="accordion" id="accordionBarang">
                        @foreach($items as $index => $item)
                        @php
                            $draftItem = $dataDetail['barang'][$index] ?? [];
                        @endphp

                        <div class="card mb-2 border">
                            <div class="card-header bg-light py-2 btn-collapse-barang" data-target="#collapseBarang{{ $index }}" style="cursor: pointer;">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div class="fw-bold" style="font-size: 13px;">
                                        {{ $item->goods_code ?? $item->id_item }} - {{ $item->itemdesc }}
                                    </div>
                                    <i class="fas fa-chevron-down icon-collapse"></i>
                                </div>
                            </div>

                            <div id="collapseBarang{{ $index }}" class="collapse" data-parent="#accordionBarang">
                                <div class="card-body py-3 px-3 bg-white">

                                    <input type="hidden" name="barang[{{ $index }}][kodeBarang]" value="{{ $draftItem['kodeBarang'] ?? $item->goods_code ?? $item->id_item }}">
                                    <input type="hidden" name="barang[{{ $index }}][seriBarang]" value="{{ $index + 1 }}">

                                    <div class="row">
                                        <div class="col-md-3">
                                            <div class="border-bottom fw-bold mb-2 pb-1" style="font-size: 12px; color: #003366;">Jenis</div>
                                            <div class="form-group mb-2">
                                                <label class="small mb-0">Pos Tarif/HS</label>
                                                <select name="barang[{{ $index }}][posTarif]" class="form-control form-control-sm select2bs4">
                                                    <option value="">Pilih Pos Tarif/HS</option>
                                                    <option value="01012100" {{ ($draftItem['posTarif'] ?? '') == '01012100' ? 'selected' : '' }}>01012100 - BIBIT</option>
                                                    <option value="01012900" {{ ($draftItem['posTarif'] ?? '') == '01012900' ? 'selected' : '' }}>01012900 - LAIN-LAIN</option>
                                                    <option value="01013010" {{ ($draftItem['posTarif'] ?? '') == '01013010' ? 'selected' : '' }}>01013010 - BIBIT</option>
                                                    <option value="01013090" {{ ($draftItem['posTarif'] ?? '') == '01013090' ? 'selected' : '' }}>01013090 - LAIN-LAIN</option>
                                                    <option value="01019000" {{ ($draftItem['posTarif'] ?? '') == '01019000' ? 'selected' : '' }}>01019000 - LAIN-LAIN</option>
                                                    <option value="01022100" {{ ($draftItem['posTarif'] ?? '') == '01022100' ? 'selected' : '' }}>01022100 - BIBIT</option>
                                                    <option value="01022910" {{ ($draftItem['posTarif'] ?? '') == '01022910' ? 'selected' : '' }}>01022910 - SAPI JANTAN</option>
                                                    <option value="01022911" {{ ($draftItem['posTarif'] ?? '') == '01022911' ? 'selected' : '' }}>01022911 - OXEN</option>
                                                    <option value="01022919" {{ ($draftItem['posTarif'] ?? '') == '01022919' ? 'selected' : '' }}>01022919 - LAIN-LAIN</option>
                                                </select>
                                            </div>
                                            <div class="form-group mb-2">
                                                <label class="small mb-0">Kode Barang</label>
                                                <input type="text" name="barang[{{ $index }}][kodeBarang]" class="form-control form-control-sm" value="{{ $draftItem['kodeBarang'] ?? $item->id_item ?? '' }}">
                                            </div>
                                            <div class="form-group mb-2">
                                                <label class="small mb-0">Uraian Jenis Barang</label>
                                                <textarea name="barang[{{ $index }}][uraian]" class="form-control form-control-sm" rows="2">{{ $draftItem['uraian'] ?? $item->itemdesc }}</textarea>
                                            </div>
                                            <div class="form-group mb-2">
                                                <label class="small mb-0">Merek</label>
                                                <input type="text" name="barang[{{ $index }}][merk]" class="form-control form-control-sm" value="{{ $draftItem['merk'] ?? '-' }}">
                                            </div>
                                            <div class="form-group mb-2">
                                                <label class="small mb-0">Tipe</label>
                                                <input type="text" name="barang[{{ $index }}][tipe]" class="form-control form-control-sm" value="{{ $draftItem['tipe'] ?? 'TIPE BARANG' }}">
                                            </div>
                                            <div class="form-group mb-2">
                                                <label class="small mb-0">Ukuran</label>
                                                <input type="text" name="barang[{{ $index }}][ukuran]" class="form-control form-control-sm" value="{{ $draftItem['ukuran'] ?? '' }}">
                                            </div>
                                            <div class="form-group mb-0">
                                                <label class="small mb-0">Spesifikasi Lain</label>
                                                <input type="text" name="barang[{{ $index }}][spesifikasiLain]" class="form-control form-control-sm" value="{{ $draftItem['spesifikasiLain'] ?? $item->remark ?? '-' }}">
                                            </div>
                                        </div>

                                        <div class="col-md-3 border-left">
                                            <div class="border-bottom fw-bold mb-2 pb-1" style="font-size: 12px; color: #003366;">Jumlah & Berat</div>
                                            <div class="row">
                                                <div class="col-7 form-group mb-2 pr-1">
                                                    <label class="small mb-0">Jml Satuan</label>
                                                    <input type="number" step="any" name="barang[{{ $index }}][jumlahSatuan]" class="form-control form-control-sm" value="{{ $draftItem['jumlahSatuan'] ?? (float) $item->qty }}">
                                                </div>
                                                <div class="col-5 form-group mb-2 pl-1">
                                                    <label class="small mb-0">Satuan</label>
                                                    <select name="barang[{{ $index }}][kodeSatuanBarang]" class="form-control form-control-sm select2bs4">
                                                        <option value="">Pilih Satuan</option>
                                                        <option value="05" {{ ($draftItem['kodeSatuanBarang'] ?? $item->unit) == '05' ? 'selected' : '' }}>05 - LIFT</option>
                                                        <option value="06" {{ ($draftItem['kodeSatuanBarang'] ?? $item->unit) == '06' ? 'selected' : '' }}>06 - SMALL SPRAY</option>
                                                        <option value="08" {{ ($draftItem['kodeSatuanBarang'] ?? $item->unit) == '08' ? 'selected' : '' }}>08 - HEAT LOT</option>
                                                        <option value="10" {{ ($draftItem['kodeSatuanBarang'] ?? $item->unit) == '10' ? 'selected' : '' }}>10 - GROUP</option>
                                                        <option value="11" {{ ($draftItem['kodeSatuanBarang'] ?? $item->unit) == '11' ? 'selected' : '' }}>11 - OUTFIT</option>
                                                        <option value="123" {{ ($draftItem['kodeSatuanBarang'] ?? $item->unit) == '123' ? 'selected' : '' }}>123 - 124</option>
                                                        <option value="13" {{ ($draftItem['kodeSatuanBarang'] ?? $item->unit) == '13' ? 'selected' : '' }}>13 - RATION</option>
                                                        <option value="14" {{ ($draftItem['kodeSatuanBarang'] ?? $item->unit) == '14' ? 'selected' : '' }}>14 - SHOT</option>
                                                    </select>
                                                </div>
                                            </div>

                                            <div class="row">
                                                <div class="col-7 form-group mb-2 pr-1">
                                                    <label class="small mb-0">Jml Kemasan</label>
                                                    <input type="number" step="any" name="barang[{{ $index }}][jumlahKemasan]" class="form-control form-control-sm" value="{{ $draftItem['jumlahKemasan'] ?? 0 }}">
                                                </div>
                                                <div class="col-5 form-group mb-2 pl-1">
                                                    <label class="small mb-0">Kemasan</label>
                                                    <select name="barang[{{ $index }}][kodeJenisKemasan]" class="form-control form-control-sm select2bs4">
                                                        <option value="">Pilih Kemasan</option>
                                                        <option value="1A" {{ ($draftItem['kodeJenisKemasan'] ?? '1A') == '1A' ? 'selected' : '' }}>1A - DRUM , </option>
                                                        <option value="1B" {{ ($draftItem['kodeJenisKemasan'] ?? '1B') == '1B' ? 'selected' : '' }}>1B - DRUM, ALUMINIUM</option>
                                                        <option value="1D" {{ ($draftItem['kodeJenisKemasan'] ?? '1D') == '1D' ? 'selected' : '' }}>1D - DRUM, PLYWOOD</option>
                                                        <option value="1F" {{ ($draftItem['kodeJenisKemasan'] ?? '1F') == '1F' ? 'selected' : '' }}>1F - CONTAINER, FLEXIBLE</option>
                                                        <option value="1G" {{ ($draftItem['kodeJenisKemasan'] ?? '1G') == '1G' ? 'selected' : '' }}>1G - DRUM, FIBRE</option>
                                                        <option value="1W" {{ ($draftItem['kodeJenisKemasan'] ?? '1W') == '1W' ? 'selected' : '' }}>1W - DRUM, WOODEN</option>
                                                        <option value="2C" {{ ($draftItem['kodeJenisKemasan'] ?? '2C') == '2C' ? 'selected' : '' }}>2C - BARREL, WOODEN</option>
                                                        <option value="3A" {{ ($draftItem['kodeJenisKemasan'] ?? '3A') == '3A' ? 'selected' : '' }}>3A - JERRICAN, STEEL</option>
                                                        <option value="3H" {{ ($draftItem['kodeJenisKemasan'] ?? '3H') == '3H' ? 'selected' : '' }}>3H - JERRICAN, PLASTIC</option>
                                                    </select>
                                                </div>
                                            </div>

                                            <div class="form-group mb-2">
                                                <label class="small mb-0">Volume (M3)</label>
                                                <input type="number" step="0.0001" name="barang[{{ $index }}][volume]" class="form-control form-control-sm" value="{{ $draftItem['volume'] ?? 0 }}">
                                            </div>
                                            <div class="form-group mb-0">
                                                <label class="small mb-0">Berat Bersih / Netto (Kg)</label>
                                                <input type="number" step="0.0001" name="barang[{{ $index }}][netto]" class="form-control form-control-sm" value="{{ $draftItem['netto'] ?? 0 }}">
                                            </div>
                                        </div>

                                        <div class="col-md-3 border-left">
                                            <div class="border-bottom fw-bold mb-2 pb-1" style="font-size: 12px; color: #003366;">Harga</div>
                                            <div class="form-group mb-2">
                                                <label class="small mb-0">Harga Satuan (Rp)</label>
                                                <input type="number" step="0.01" name="barang[{{ $index }}][hargaSatuan]" class="form-control form-control-sm" value="{{ $draftItem['hargaSatuan'] ?? (float) $item->price }}">
                                            </div>
                                            <div class="form-group mb-2">
                                                <label class="small mb-0 fw-bold">Harga Penyerahan/Jual</label>
                                                <input type="number" step="0.01" name="barang[{{ $index }}][hargaPenyerahan]" class="form-control form-control-sm fw-bold" value="{{ $draftItem['hargaPenyerahan'] ?? (float) ($item->qty * $item->price) }}">
                                            </div>
                                            <div class="form-group mb-2">
                                                <label class="small mb-0">Nilai Jasa/Penggantian</label>
                                                <input type="number" step="0.01" name="barang[{{ $index }}][nilaiJasa]" class="form-control form-control-sm" value="{{ $draftItem['nilaiJasa'] ?? 0 }}">
                                            </div>
                                            <div class="form-group mb-0">
                                                <label class="small mb-0">Diskon</label>
                                                <input type="number" step="0.01" name="barang[{{ $index }}][diskon]" class="form-control form-control-sm" value="{{ $draftItem['diskon'] ?? 0 }}">
                                            </div>
                                        </div>

                                        <div class="col-md-3 border-left">
                                            <div class="border-bottom fw-bold mb-2 pb-1" style="font-size: 12px; color: #003366;">Pungutan (Tarif)</div>
                                            @php
                                                $tarif = $draftItem['barangTarif'][0] ?? [];
                                            @endphp
                                            <div class="row">
                                                <div class="col-4 form-group mb-2 pr-1">
                                                    <label class="small mb-0">Jenis</label>
                                                    <input type="text" name="barang[{{ $index }}][barangTarif][kodeJenisPungutan]" class="form-control form-control-sm" value="{{ $tarif['kodeJenisPungutan'] ?? 'PPN' }}">
                                                </div>
                                                <div class="col-4 form-group mb-2 px-1">
                                                    <label class="small mb-0">Tarif (%)</label>
                                                    <input type="number" step="any" name="barang[{{ $index }}][barangTarif][tarif]" class="form-control form-control-sm" value="{{ $tarif['tarif'] ?? 11 }}">
                                                </div>
                                                <div class="col-4 form-group mb-2 pl-1">
                                                    <label class="small mb-0">Fas. (%)</label>
                                                    <input type="number" step="any" name="barang[{{ $index }}][barangTarif][tarifFasilitas]" class="form-control form-control-sm" value="{{ $tarif['tarifFasilitas'] ?? 100 }}">
                                                </div>
                                            </div>

                                            <div class="form-group mb-2">
                                                <label class="small mb-0">Kode Fasilitas</label>
                                                <select name="barang[{{ $index }}][barangTarif][kodeFasilitasTarif]" class="form-control form-control-sm">
                                                    <option value="3" {{ ($tarif['kodeFasilitasTarif'] ?? '3') == '3' ? 'selected' : '' }}>3 - DITANGGUHKAN</option>
                                                    <option value="5" {{ ($tarif['kodeFasilitasTarif'] ?? '') == '5' ? 'selected' : '' }}>5 - DIBEBASKAN</option>
                                                    <option value="6" {{ ($tarif['kodeFasilitasTarif'] ?? '') == '6' ? 'selected' : '' }}>6 - TIDAK DIPUNGUT</option>
                                                    <option value="7" {{ ($tarif['kodeFasilitasTarif'] ?? '') == '7' ? 'selected' : '' }}>7 - SUDAH DI LUNASI</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>

                <div class="tab-pane fade" id="tab-entitas" role="tabpanel">
                    <div class="section-title"><i class="fas fa-building"></i> Entitas Pengusaha TPB (Kode: 3)</div>
                    <div class="row">
                        <div class="col-md-4 form-group"><label>Nama Entitas</label><input type="text" name="entitas[3][namaEntitas]" class="form-control form-control-sm" value="{{ $dataDetail['entitas'][3]['namaEntitas'] ?? 'NIRWANA ALABARE GARMENT' }}"></div>
                        <div class="col-md-4 form-group"><label>NPWP (15/16 Digit)</label><input type="text" name="entitas[3][nomorIdentitas]" class="form-control form-control-sm" value="{{ $dataDetail['entitas'][3]['nomorIdentitas'] ?? '0745406926444000000000' }}"></div>
                        <div class="col-md-4 form-group"><label>NIB</label><input type="text" name="entitas[3][nibEntitas]" class="form-control form-control-sm" value="{{ $dataDetail['entitas'][3]['nibEntitas'] ?? '0220103231143' }}"></div>
                        <div class="col-md-8 form-group"><label>Alamat</label><input type="text" name="entitas[3][alamatEntitas]" class="form-control form-control-sm" value="{{ $dataDetail['entitas'][3]['alamatEntitas'] ?? 'JL. RAYA RANCAEKEK MAJALAYA NO. 289 RT. 001 RW. 007' }}"></div>
                        <div class="col-md-4 form-group"><label>No. Izin TPB</label><input type="text" name="entitas[3][nomorIjinEntitas]" class="form-control form-control-sm" value="{{ $dataDetail['entitas'][3]['nomorIjinEntitas'] ?? '16/MK/WBC.09/2026' }}"></div>
                    </div>

                    <div class="section-title"><i class="fas fa-truck-loading"></i> Entitas Pengirim (Kode: 9)</div>
                    <div class="row">
                        <div class="col-md-4 form-group"><label>Nama Entitas</label><input type="text" name="entitas[9][namaEntitas]" class="form-control form-control-sm" value="{{ $dataDetail['entitas'][9]['namaEntitas'] ?? $header->supplier ?? '' }}"></div>
                        <div class="col-md-4 form-group"><label>NPWP</label><input type="text" name="entitas[9][nomorIdentitas]" class="form-control form-control-sm" value="{{ $dataDetail['entitas'][9]['nomorIdentitas'] ?? $header->npwp_supplier ?? '' }}"></div>
                        <div class="col-md-4 form-group"><label>Status</label><input type="text" name="entitas[9][kodeStatus]" class="form-control form-control-sm" value="{{ $dataDetail['entitas'][9]['kodeStatus'] ?? '5' }}"></div>
                        <div class="col-md-12 form-group"><label>Alamat</label><input type="text" name="entitas[9][alamatEntitas]" class="form-control form-control-sm" value="{{ $dataDetail['entitas'][9]['alamatEntitas'] ?? $header->alamat_supplier ?? '' }}"></div>
                    </div>

                    <div class="section-title"><i class="fas fa-user-tag"></i> Entitas Pemilik Barang (Kode: 7)</div>
                    <div class="row">
                        <div class="col-md-4 form-group"><label>Nama Entitas</label><input type="text" name="entitas[7][namaEntitas]" class="form-control form-control-sm" value="{{ $dataDetail['entitas'][7]['namaEntitas'] ?? '' }}"></div>
                        <div class="col-md-4 form-group"><label>NPWP</label><input type="text" name="entitas[7][nomorIdentitas]" class="form-control form-control-sm" value="{{ $dataDetail['entitas'][7]['nomorIdentitas'] ?? '' }}"></div>
                        <div class="col-md-4 form-group"><label>Alamat</label><input type="text" name="entitas[7][alamatEntitas]" class="form-control form-control-sm" value="{{ $dataDetail['entitas'][7]['alamatEntitas'] ?? '' }}"></div>
                    </div>
                </div>

                <div class="tab-pane fade" id="tab-pendukung" role="tabpanel">

                    <div class="section-title mt-0">Pengangkut & Pungutan</div>
                    <div class="row mb-3">
                        <div class="col-md-3 form-group"><label>Nama Pengangkut</label><input type="text" name="pengangkut[nama]" class="form-control form-control-sm" value="{{ $dataDetail['pengangkut']['nama'] ?? 'TRUK' }}"></div>
                        <div class="col-md-3 form-group"><label>Nomor Polisi</label><input type="text" name="pengangkut[nomor]" class="form-control form-control-sm" value="{{ $dataDetail['pengangkut']['nomor'] ?? $header->nomor_mobil ?? '' }}"></div>
                        <div class="col-md-3 form-group">
                            <label>Jenis Pungutan</label>
                            @php
                                $listPungutan = [
                                    'BK' => 'Bea Keluar', 'BM' => 'Bea Masuk', 'BMAD' => 'Bea Masuk Anti Dumping', 'BMI' => 'Bea Masuk Imbalan',
                                    'BMKITE' => 'Bea Masuk KITE', 'BMP' => 'Bea Masuk Pembalasan', 'BMTP' => 'Bea Masuk Tindakan Pengamanan',
                                    'CEA' => 'Cukai Etil Alkohol', 'CMEA' => 'Cukai Minuman Mengandung Etil Alkohol', 'CTEM' => 'Cukai Tembakau',
                                    'DENDA' => 'Denda Administrasi Pabean', 'DS' => 'Dana Sawit', 'PNBP' => 'Penerimaan Negara Bukan Pajak',
                                    'PPH' => 'PPh Impor', 'PPHEKSPOR' => 'PPh Ekspor', 'PPN' => 'PPN Impor', 'PPNBM' => 'PPnBM Impor', 'PPNLOKAL' => 'PPN Dalam Negeri / Hasil Tembakau'
                                ];
                                $selectedPungutan = $dataDetail['pungutan']['jenis'] ?? 'PPN';
                            @endphp
                            <select name="pungutan[jenis]" class="form-control form-control-sm select2bs4">
                                @foreach($listPungutan as $kode => $nama)
                                    <option value="{{ $kode }}" {{ $selectedPungutan == $kode ? 'selected' : '' }}>{{ $kode }} - {{ $nama }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3 form-group"><label>Nilai Pungutan</label><input type="number" step="0.01" name="pungutan[nilai]" class="form-control form-control-sm" value="{{ $dataDetail['pungutan']['nilai'] ?? 0 }}"></div>
                    </div>

                    <div class="section-title">Data Kemasan</div>
                    <div class="row mb-3">
                        <div class="col-md-12">
                            @php
                                $kemasans = $dataDetail['kemasan'] ?? [];
                                $listJenisKemasan = [
                                    '1A' => 'DRUM, STEEL', '1B' => 'DRUM, ALUMINIUM', '1D' => 'DRUM, PLYWOOD', '1F' => 'CONTAINER, FLEXIBLE',
                                    '1G' => 'DRUM, FIBRE', '1W' => 'DRUM, WOODEN', '2C' => 'BARREL, WOODEN', '3A' => 'JERRICAN, STEEL',
                                    '3H' => 'JERRICAN, PLASTIC'
                                ];

                                if (empty($kemasans)) {
                                    $kemasans[] = ['jumlahKemasan' => $header->qty_karton ?? 0, 'kodeJenisKemasan' => 'CT', 'merkKemasan' => '-'];
                                }
                            @endphp
                            <table class="table table-sm table-bordered" id="table-kemasan">
                                <thead class="bg-light text-center">
                                    <tr>
                                        <th width="20%">Jumlah Kemasan</th>
                                        <th width="40%">Jenis Kemasan</th>
                                        <th width="30%">Merek</th>
                                        <th width="10%">
                                            <button type="button" class="btn btn-sm btn-primary py-0 px-2" id="btn-add-kemasan" title="Tambah Kemasan"><i class="fas fa-plus"></i></button>
                                        </th>
                                    </tr>
                                </thead>
                                <tbody id="tbody-kemasan">
                                    @foreach($kemasans as $index => $kemasan)
                                    <tr>
                                        <td><input type="number" step="any" name="kemasan[{{ $index }}][jumlahKemasan]" class="form-control form-control-sm" value="{{ $kemasan['jumlahKemasan'] ?? $kemasan['jumlah'] ?? 0 }}"></td>
                                        <td>
                                            <select name="kemasan[{{ $index }}][kodeJenisKemasan]" class="form-control form-control-sm select2bs4">
                                                <option value="">-- Pilih --</option>
                                                @foreach($listJenisKemasan as $k => $v)
                                                    <option value="{{ $k }}" {{ ($kemasan['kodeJenisKemasan'] ?? $kemasan['kode'] ?? '') == $k ? 'selected' : '' }}>{{ $k }} - {{ $v }}</option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td><input type="text" name="kemasan[{{ $index }}][merkKemasan]" class="form-control form-control-sm" value="{{ $kemasan['merkKemasan'] ?? $kemasan['merk'] ?? '-' }}"></td>
                                        <td class="text-center align-middle">
                                            <button type="button" class="btn btn-sm btn-danger py-0 px-2 btn-hapus-kemasan" title="Hapus"><i class="fas fa-trash-alt"></i></button>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="section-title">Data Kontainer / Peti Kemas</div>
                    <div class="row mb-3">
                        <div class="col-md-12">
                            @php
                                $kontainers = $dataDetail['kontainer'] ?? [];
                                $listJenisKontainer = ['4' => 'Empty', '7' => 'LCL', '8' => 'FCL'];
                                $listTipeKontainer = [
                                    '1' => 'General/Dry Cargo', '2' => 'Tunnel Type', '3' => 'Open Top Steel',
                                    '4' => 'Flat Rack', '5' => 'Reefer/Refrigerated', '6' => 'Barge Container',
                                    '7' => 'Bulk Container', '8' => 'Isotank', '99' => 'Lain-lain'
                                ];
                                $listUkuranKontainer = ['20' => '20 Feet', '40' => '40 Feet', '45' => '45 Feet', '60' => '60 Feet'];
                            @endphp
                            <table class="table table-sm table-bordered" id="table-kontainer">
                                <thead class="bg-light text-center">
                                    <tr>
                                        <th width="30%">Nomor Kontainer</th>
                                        <th width="20%">Jenis</th>
                                        <th width="25%">Tipe</th>
                                        <th width="15%">Ukuran</th>
                                        <th width="10%">
                                            <button type="button" class="btn btn-sm btn-primary py-0 px-2" id="btn-add-kontainer" title="Tambah Kontainer"><i class="fas fa-plus"></i></button>
                                        </th>
                                    </tr>
                                </thead>
                                <tbody id="tbody-kontainer">
                                    @foreach($kontainers as $kIndex => $kont)
                                    <tr>
                                        <td><input type="text" name="kontainer[{{ $kIndex }}][nomorKontainer]" class="form-control form-control-sm text-uppercase" value="{{ $kont['nomorKontainer'] ?? '' }}" placeholder="Contoh: TGHU1234567"></td>
                                        <td>
                                            <select name="kontainer[{{ $kIndex }}][kodeJenisKontainer]" class="form-control form-control-sm select2bs4">
                                                <option value="">-- Pilih --</option>
                                                @foreach($listJenisKontainer as $k => $v)
                                                    <option value="{{ $k }}" {{ ($kont['kodeJenisKontainer'] ?? '') == $k ? 'selected' : '' }}>{{ $k }} - {{ $v }}</option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td>
                                            <select name="kontainer[{{ $kIndex }}][kodeTipeKontainer]" class="form-control form-control-sm select2bs4">
                                                <option value="">-- Pilih --</option>
                                                @foreach($listTipeKontainer as $k => $v)
                                                    <option value="{{ $k }}" {{ ($kont['kodeTipeKontainer'] ?? '') == $k ? 'selected' : '' }}>{{ $k }} - {{ $v }}</option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td>
                                            <select name="kontainer[{{ $kIndex }}][kodeUkuranKontainer]" class="form-control form-control-sm select2bs4">
                                                <option value="">-- Pilih --</option>
                                                @foreach($listUkuranKontainer as $k => $v)
                                                    <option value="{{ $k }}" {{ ($kont['kodeUkuranKontainer'] ?? '') == $k ? 'selected' : '' }}>{{ $k }} - {{ $v }}</option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td class="text-center align-middle">
                                            <button type="button" class="btn btn-sm btn-danger py-0 px-2 btn-hapus-kontainer" title="Hapus"><i class="fas fa-trash-alt"></i></button>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="section-title">Dokumen Pendukung</div>
                    <div class="row">
                        <div class="col-md-12">
                            @php
                                $referensiDokumen = [
                                    '10' => 'RKSP', '11' => 'MANIFES', '16' => 'BC 1.6', '20' => 'BC 2.0 - PIB',
                                    '23' => 'BC 2.3', '25' => 'BC 2.5', '27' => 'BC 2.7', '30' => 'BC 3.0 - PEB',
                                    '40' => 'BC 4.0', '41' => 'BC 4.1', '217' => 'PACKING LIST', '380' => 'INVOICE', '388' => 'FAKTUR PAJAK'
                                ];

                                $dokumens = [];
                                if (!empty($dataDetail['dok']) && count($dataDetail['dok']) > 0) {
                                    $dokumens = $dataDetail['dok'];
                                }
                            @endphp
                            <table class="table table-sm table-bordered" id="table-dokumen">
                                <thead class="bg-light text-center">
                                    <tr>
                                        <th width="40%">Kode Dokumen</th>
                                        <th width="35%">Nomor Dokumen</th>
                                        <th width="15%">Tgl Dokumen</th>
                                        <th width="10%"><button type="button" class="btn btn-sm btn-primary py-0 px-2" id="btn-add-dok" title="Tambah Dokumen"><i class="fas fa-plus"></i></button></th>
                                    </tr>
                                </thead>
                                <tbody id="tbody-dokumen">
                                    @foreach($dokumens as $index => $dok)
                                    <tr>
                                        <td>
                                            <select name="dok[{{ $index }}][kode]" class="form-control form-control-sm select2bs4">
                                                <option value="">-- Pilih Kode --</option>
                                                @foreach($referensiDokumen as $val => $text)
                                                    <option value="{{ $val }}" {{ ($dok['kode'] ?? '') == $val ? 'selected' : '' }}>{{ $val }} - {{ $text }}</option>
                                                @endforeach
                                                @if(!empty($dok['kode']) && !array_key_exists($dok['kode'], $referensiDokumen))
                                                    <option value="{{ $dok['kode'] }}" selected>{{ $dok['kode'] }} - Custom</option>
                                                @endif
                                            </select>
                                        </td>
                                        <td><input type="text" name="dok[{{ $index }}][nomor]" class="form-control form-control-sm" value="{{ $dok['nomor'] ?? '' }}"></td>
                                        <td><input type="date" name="dok[{{ $index }}][tgl]" class="form-control form-control-sm" value="{{ $dok['tgl'] ?? '' }}"></td>
                                        <td class="text-center align-middle"><button type="button" class="btn btn-sm btn-danger py-0 px-2 btn-hapus-dok" title="Hapus Baris"><i class="fas fa-trash-alt"></i></button></td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                </div>
            </div>
        </div>

        <div class="card-footer text-right bg-white border-top">
            <a href="{{ route('dokumen-pabean-index') }}" class="btn btn-default btn-sm"><i class="fas fa-arrow-left"></i> Kembali</a>
            <button type="submit" class="btn btn-success btn-sm"><i class="fas fa-save"></i> Simpan</button>
        </div>
    </form>
</div>
@endsection

@section('custom-script')
<script src="{{ asset('plugins/select2/js/select2.full.min.js') }}"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    $(document).ready(function() {

        $('.select2bs4').select2({ theme: 'bootstrap4', width: '100%', tags: true });


        $('#ceisaTab a').on('click', function (e) {
            e.preventDefault();
            $(this).tab('show');
        });

        const optDokumenHtml = `
            <option value="">-- Pilih Kode --</option>
            @foreach($referensiDokumen as $val => $text)
                <option value="{{ $val }}">{{ $val }} - {{ $text }}</option>
            @endforeach
        `;
        let dokIndex = {{ count($dokumens) }};
        $('#btn-add-dok').on('click', function() {
            let htmlTr = `
                <tr>
                    <td><select name="dok[${dokIndex}][kode]" class="form-control form-control-sm select2bs4-dynamic">${optDokumenHtml}</select></td>
                    <td><input type="text" name="dok[${dokIndex}][nomor]" class="form-control form-control-sm" value=""></td>
                    <td><input type="date" name="dok[${dokIndex}][tgl]" class="form-control form-control-sm" value=""></td>
                    <td class="text-center align-middle"><button type="button" class="btn btn-sm btn-danger py-0 px-2 btn-hapus-dok"><i class="fas fa-trash-alt"></i></button></td>
                </tr>
            `;
            $('#tbody-dokumen').append(htmlTr);
            $(`select[name="dok[${dokIndex}][kode]"]`).select2({ theme: 'bootstrap4', width: '100%', tags: true });
            dokIndex++;
        });
        $(document).on('click', '.btn-hapus-dok', function() {
            if ($('#tbody-dokumen tr').length > 1) { $(this).closest('tr').remove(); }
            else { Swal.fire('Info', 'Minimal sisakan 1 baris.', 'info'); }
        });

        const optJenisKemasan = `
            <option value="">-- Pilih --</option>
            <option value="1A">DRUM, STEEL</option>
            <option value="1B">DRUM, ALUMINIUM</option>
            <option value="1D">DRUM, PLYWOOD</option>
            <option value="1F">CONTAINER, FLEXIBLE</option>
            <option value="1G">DRUM, FIBRE</option>
            <option value="1W">DRUM, WOODEN</option>
            <option value="2C">BARREL, WOODEN</option>
            <option value="3A">JERRICAN, STEEL</option>
            <option value="3H">JERRICAN, PLASTIC</option>
        `;
        let kemasanIndex = {{ count($kemasans) }};
        $('#btn-add-kemasan').on('click', function() {
            let htmlTr = `
                <tr>
                    <td><input type="number" step="any" name="kemasan[${kemasanIndex}][jumlahKemasan]" class="form-control form-control-sm" value="0"></td>
                    <td><select name="kemasan[${kemasanIndex}][kodeJenisKemasan]" class="form-control form-control-sm select2bs4-dynamic">${optJenisKemasan}</select></td>
                    <td><input type="text" name="kemasan[${kemasanIndex}][merkKemasan]" class="form-control form-control-sm" value="-"></td>
                    <td class="text-center align-middle"><button type="button" class="btn btn-sm btn-danger py-0 px-2 btn-hapus-kemasan"><i class="fas fa-trash-alt"></i></button></td>
                </tr>
            `;
            $('#tbody-kemasan').append(htmlTr);
            $(`select[name="kemasan[${kemasanIndex}][kodeJenisKemasan]"]`).select2({ theme: 'bootstrap4', width: '100%', tags: true });
            kemasanIndex++;
        });
        $(document).on('click', '.btn-hapus-kemasan', function() {
            $(this).closest('tr').remove();
        });


        const optJenisKontainer = `<option value="">-- Pilih --</option><option value="4">4 - Empty</option><option value="7">7 - LCL</option><option value="8">8 - FCL</option>`;
        const optTipeKontainer = `<option value="">-- Pilih --</option><option value="1">1 - General/Dry Cargo</option><option value="2">2 - Tunnel Type</option><option value="3">3 - Open Top Steel</option><option value="4">4 - Flat Rack</option><option value="5">5 - Reefer/Refrigerated</option><option value="6">6 - Barge Container</option><option value="7">7 - Bulk Container</option><option value="8">8 - Isotank</option><option value="99">99 - Lain-lain</option>`;
        const optUkuranKontainer = `<option value="">-- Pilih --</option><option value="20">20 Feet</option><option value="40">40 Feet</option><option value="45">45 Feet</option> <option value="60">60 Feet</option>`;

        let kontainerIndex = {{ count($kontainers) }};
        $('#btn-add-kontainer').on('click', function() {
            let htmlTr = `
                <tr>
                    <td><input type="text" name="kontainer[${kontainerIndex}][nomorKontainer]" class="form-control form-control-sm text-uppercase"></td>
                    <td><select name="kontainer[${kontainerIndex}][kodeJenisKontainer]" class="form-control form-control-sm select2bs4-dynamic">${optJenisKontainer}</select></td>
                    <td><select name="kontainer[${kontainerIndex}][kodeTipeKontainer]" class="form-control form-control-sm select2bs4-dynamic">${optTipeKontainer}</select></td>
                    <td><select name="kontainer[${kontainerIndex}][kodeUkuranKontainer]" class="form-control form-control-sm select2bs4-dynamic">${optUkuranKontainer}</select></td>
                    <td class="text-center align-middle"><button type="button" class="btn btn-sm btn-danger py-0 px-2 btn-hapus-kontainer"><i class="fas fa-trash-alt"></i></button></td>
                </tr>
            `;
            $('#tbody-kontainer').append(htmlTr);
            $(`select[name^="kontainer[${kontainerIndex}]"]`).select2({ theme: 'bootstrap4', width: '100%' });
            kontainerIndex++;
        });
        $(document).on('click', '.btn-hapus-kontainer', function() {
            $(this).closest('tr').remove();
        });


        $('#form-edit-ceisa').on('submit', function(e) {
            e.preventDefault();
            if($('input[name="pengangkut[nomor]"]').val() === ""){
                Swal.fire({
                    title: 'Error!',
                    text: 'No Polisi Pengangkut belum diisi. Mohon lengkapi data pengangkut terlebih dahulu.',
                    icon: 'error'
                });
                return;
            }

            Swal.fire({
                title: 'Simpan Perubahan?',
                text: "Data draft akan diperbarui di database.",
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#28a745',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Ya, Simpan!'
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({ title: 'Menyimpan...', allowOutsideClick: false, didOpen: () => { Swal.showLoading(); }});
                    this.submit();
                }
            });
        });

        $('.btn-collapse-barang').on('click', function() {
            let targetId = $(this).data('target');
            let icon = $(this).find('.icon-collapse');
            let isExpanded = $(targetId).hasClass('show');

            $('.collapse').collapse('hide');
            $('.icon-collapse').removeClass('fa-chevron-up').addClass('fa-chevron-down');

            if (!isExpanded) {
                $(targetId).collapse('show');
                icon.removeClass('fa-chevron-down').addClass('fa-chevron-up');
            }
        });

    });
</script>
@endsection
