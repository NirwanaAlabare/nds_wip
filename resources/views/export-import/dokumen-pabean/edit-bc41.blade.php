@extends('layouts.index')

@section('custom-link')
<link rel="stylesheet" href="{{ asset('plugins/select2/css/select2.min.css') }}">
<link rel="stylesheet" href="{{ asset('plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}">
<style>
    .nav-tabs { border-bottom: none; }
    .nav-tabs .nav-item { margin-bottom: 0; margin-right: 5px; }
    .nav-tabs .nav-link { border: 1px solid #ddd; border-radius: 4px; padding: 8px 15px; font-size: 13px; transition: all 0.3s ease; }
    .nav-tabs .nav-link.active { font-weight: bold; background-color: #003366 !important; color: #ffffff !important; border-color: #003366 !important; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
    .nav-tabs .nav-link.active::after { display: none; }
    .nav-tabs .nav-link:not(.active) { color: #000000 !important; background-color: #ffffff; }
    .nav-tabs .nav-link:not(.active):hover { background-color: #f8f9fa; border-color: #ddd; color: #000000 !important; }
    .form-group label { font-size: 13px; font-weight: 600; margin-bottom: 0.2rem; }
    .form-control-sm { font-size: 13px; }
    .section-title { font-size: 14px; font-weight: bold; color: #333; border-bottom: 2px solid #ddd; padding-bottom: 5px; margin-bottom: 15px; margin-top: 20px; }
</style>
@endsection

@section('content')
<div class="card card-sb">
    <div class="card-header">
        <h5 class="card-title fw-bold mb-0">
            <i class="fas fa-edit"></i> BC 4.1 - PEMBERITAHUAN PENGELUARAN KEMBALI BARANG ASAL TEMPAT LAIN DALAM DAERAH PABEAN DARI TEMPAT PENIMBUNAN BERIKAT
        </h5>
    </div>

    <form action="{{ route('dokumen-pabean-update_draft_bc41', $header->bppbno ?? $header->trx_no_par) }}" method="POST" id="form-edit-ceisa" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        <div class="card-body">
            <div class="alert alert-info py-2 mb-4">
                <strong>No. Transaksi:</strong> {{ $header->trx_no_par }} |
                <strong>Supplier / Vendor:</strong> {{ $header->supplier ?? '-' }}
                <input type="hidden" name="bppbno_int" value="{{ $header->bppbno_int ?? '' }}">
                <input type="hidden" name="kodeDokumen" value="41">
                <input type="hidden" name="asalData" value="S">
            </div>

            <ul class="nav nav-tabs" id="ceisaTab" role="tablist">
                <li class="nav-item"><a class="nav-link active" id="header-tab" data-toggle="tab" href="#tab-header" role="tab">Header</a></li>
                <li class="nav-item"><a class="nav-link" id="entitas-tab" data-toggle="tab" href="#tab-entitas" role="tab">Entitas</a></li>
                <li class="nav-item"><a class="nav-link" id="dokumen-tab" data-toggle="tab" href="#tab-dokumen" role="tab">Dokumen</a></li>
                <li class="nav-item"><a class="nav-link" id="pengangkut-tab" data-toggle="tab" href="#tab-pengangkut" role="tab">Pengangkut</a></li>
                <li class="nav-item"><a class="nav-link" id="kemasan-tab" data-toggle="tab" href="#tab-kemasan" role="tab">Kemasan & Peti Kemas</a></li>
                <li class="nav-item"><a class="nav-link" id="transaksi-tab" data-toggle="tab" href="#tab-transaksi" role="tab">Transaksi</a></li>
                <li class="nav-item"><a class="nav-link" id="barang-tab" data-toggle="tab" href="#tab-barang" role="tab">Barang</a></li>
                <li class="nav-item"><a class="nav-link" id="pungutan-tab" data-toggle="tab" href="#tab-pungutan" role="tab">Pungutan</a></li>
                <li class="nav-item"><a class="nav-link" id="pernyataan-tab" data-toggle="tab" href="#tab-pernyataan" role="tab">Pernyataan</a></li>
            </ul>

            <div class="tab-content mt-3" id="ceisaTabContent">


                <div class="tab-pane fade show active" id="tab-header" role="tabpanel">
                    <div class="row">

                        <div class="col-md-4">
                            <div class="card shadow-sm mb-3 border">
                                <div class="card-header fw-bold px-3 py-2 bg-light text-dark" style="font-size:13px;">Pengajuan</div>
                                <div class="card-body">
                                    <div class="form-group mb-2">
                                        <label class="text-sm">Nomor Aju</label>
                                        <input type="text" name="nomorAju" class="form-control form-control-sm fw-bold" value="{{ $nomorAju }}"\>
                                    </div>
                                </div>
                            </div>
                        </div>


                        <div class="col-md-4">
                            <div class="card shadow-sm mb-3 border">
                                <div class="card-header fw-bold px-3 py-2 bg-light text-dark" style="font-size:13px;">Kantor Pabean</div>
                                <div class="card-body">
                                    <div class="form-group mb-2">
                                        <label class="text-sm">Kantor Pabean Pengawasan</label>
                                        <select name="kodeKantor" class="form-control form-control-sm select2bs4">
                                            <option value="">Pilih Kantor Pabean Pengawasan</option>
                                            @foreach($kantorList as $ktr)
                                                <option value="{{ $ktr['kode'] }}" {{ ($dataDetail['kodeKantor'] ?? '') == $ktr['kode'] ? 'selected' : '' }}>{{ $ktr['nama'] }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>


                        <div class="col-md-4">
                            <div class="card shadow-sm mb-3 border">
                                <div class="card-header fw-bold px-3 py-2 bg-light text-dark" style="font-size:13px;">Informasi TPB</div>
                                <div class="card-body">
                                    <div class="form-group mb-2">
                                        <label class="text-sm">Jenis TPB</label>
                                        <select name="kodeJenisTpb" class="form-control form-control-sm select2bs4">
                                            <option value="">Pilih Jenis TPB</option>
                                            @foreach($listJenisTpb as $k => $v)
                                                <option value="{{ $k }}" {{ ($dataDetail['kodeJenisTpb'] ?? '') == $k ? 'selected' : '' }}>{{ $k }} - {{ strtoupper($v) }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="form-group mb-2">
                                        <label class="text-sm">Tujuan Pengiriman</label>
                                        <select name="kodeTujuanPengiriman" class="form-control form-control-sm select2bs4">
                                            <option value="">Tujuan Pengiriman</option>
                                            @foreach($listTujuanPengiriman as $k => $v)
                                                <option value="{{ $k }}" {{ ($dataDetail['kodeTujuanPengiriman'] ?? '') == $k ? 'selected' : '' }}>{{ $k }} - {{ strtoupper($v) }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>


                <div class="tab-pane fade" id="tab-entitas" role="tabpanel">
                    <div class="row">

                        <div class="col-md-4">
                            <div class="card shadow-sm mb-3 border">
                                <div class="card-header fw-bold px-3 py-2 bg-light text-dark" style="font-size:13px;">Pengusaha TPB (Asal)</div>
                                <div class="card-body">
                                    @php $entTpb = $dataDetail['entitas']['tpb'] ?? []; @endphp
                                    <div class="form-group mb-2">
                                        <label class="small mb-0">Nomor Identitas (NPWP)</label>
                                        <input type="text" name="entitas[tpb][nomorIdentitas]" class="form-control form-control-sm" value="{{ $entTpb['nomorIdentitas'] ?? '0745406926444000000000' }}" placeholder="NPWP 15/16 Digit">
                                    </div>
                                    <div class="form-group mb-2">
                                        <label class="small mb-0">NITKU</label>
                                        <input type="text" name="entitas[tpb][nitku]" class="form-control form-control-sm" value="{{ $entTpb['nitku'] ?? '' }}" placeholder="NITKU">
                                    </div>
                                    <div class="form-group mb-2">
                                        <label class="small mb-0">Nama Pengusaha TPB</label>
                                        <input type="text" name="entitas[tpb][namaEntitas]" class="form-control form-control-sm" value="{{ $entTpb['namaEntitas'] ?? 'NIRWANA ALABARE GARMENT' }}" placeholder="Nama Perusahaan">
                                    </div>
                                    <div class="form-group mb-2">
                                        <label class="small mb-0">Alamat</label>
                                        <textarea name="entitas[tpb][alamatEntitas]" class="form-control form-control-sm" rows="2" placeholder="Alamat Perusahaan">{{ $entTpb['alamatEntitas'] ?? 'JL. RAYA RANCAEKEK MAJALAYA NO. 289 RT. 001 RW. 007' }}</textarea>
                                    </div>
                                    <div class="form-group mb-2">
                                        <label class="small mb-0">Nomor Ijin TPB</label>
                                        <input type="text" name="entitas[tpb][nomorIjinEntitas]" class="form-control form-control-sm" value="{{ $entTpb['nomorIjinEntitas'] ?? '16/MK/WBC.09/2026' }}" placeholder="Nomor Ijin">
                                    </div>
                                    <div class="form-group mb-2">
                                        <label class="small mb-0">Tanggal Ijin TPB</label>
                                        <input type="date" name="entitas[tpb][tanggalIjinEntitas]" class="form-control form-control-sm" value="{{ $entTpb['tanggalIjinEntitas'] ?? '2026-01-20' }}">
                                    </div>
                                    <div class="form-group mb-0">
                                        <label class="small mb-0">NIB</label>
                                        <input type="text" name="entitas[tpb][nibEntitas]" class="form-control form-control-sm" value="{{ $entTpb['nibEntitas'] ?? '0220103231143' }}" placeholder="NIB">
                                    </div>
                                </div>
                            </div>
                        </div>


                        <div class="col-md-4">
                            <div class="card shadow-sm mb-3 border">
                                <div class="card-header fw-bold px-3 py-2 bg-light text-dark" style="font-size:13px;">Penerima Barang / Pembeli</div>
                                <div class="card-body">
                                    @php $entPenerima = $dataDetail['entitas']['penerima'] ?? []; @endphp
                                    <div class="form-group mb-2">
                                        <label class="small mb-0">Nomor Identitas (NPWP/KTP)</label>
                                        <input type="text" name="entitas[penerima][nomorIdentitas]" class="form-control form-control-sm" value="{{ $entPenerima['nomorIdentitas'] ?? ($header->npwp_supplier ?? '') }}" placeholder="NPWP / KTP">
                                    </div>
                                    <div class="form-group mb-2">
                                        <label class="small mb-0">NITKU</label>
                                        <input type="text" name="entitas[penerima][nitku]" class="form-control form-control-sm" value="{{ $entPenerima['nitku'] ?? '' }}" placeholder="NITKU">
                                    </div>
                                    <div class="form-group mb-2">
                                        <label class="small mb-0">Nama Penerima</label>
                                        <input type="text" name="entitas[penerima][namaEntitas]" class="form-control form-control-sm" value="{{ $entPenerima['namaEntitas'] ?? ($header->supplier ?? '') }}" placeholder="Nama Penerima">
                                    </div>
                                    <div class="form-group mb-2">
                                        <label class="small mb-0">Alamat Penerima</label>
                                        <textarea name="entitas[penerima][alamatEntitas]" class="form-control form-control-sm" rows="3" placeholder="Alamat Penerima">{{ $entPenerima['alamatEntitas'] ?? ($header->alamat_supplier ?? '') }}</textarea>
                                    </div>
                                </div>
                            </div>
                        </div>


                        <div class="col-md-4">
                            <div class="card shadow-sm mb-3 border">
                                <div class="card-header fw-bold px-3 py-2 bg-light text-dark" style="font-size:13px;">Pemilik Barang</div>
                                <div class="card-body">
                                    @php $entPemilik = $dataDetail['entitas']['pemilik'] ?? []; @endphp
                                    <div class="form-group mb-2">
                                        <label class="small mb-0">Nomor Identitas (NPWP/KTP)</label>
                                        <input type="text" name="entitas[pemilik][nomorIdentitas]" class="form-control form-control-sm" value="{{ $entPemilik['nomorIdentitas'] ?? ($header->npwp_supplier ?? '') }}" placeholder="NPWP / KTP">
                                    </div>
                                    <div class="form-group mb-2">
                                        <label class="small mb-0">NITKU</label>
                                        <input type="text" name="entitas[pemilik][nitku]" class="form-control form-control-sm" value="{{ $entPemilik['nitku'] ?? '' }}" placeholder="NITKU">
                                    </div>
                                    <div class="form-group mb-2">
                                        <label class="small mb-0">Nama Pemilik</label>
                                        <input type="text" name="entitas[pemilik][namaEntitas]" class="form-control form-control-sm" value="{{ $entPemilik['namaEntitas'] ?? ($header->supplier ?? '') }}" placeholder="Nama Pemilik">
                                    </div>
                                    <div class="form-group mb-2">
                                        <label class="small mb-0">Alamat Pemilik</label>
                                        <textarea name="entitas[pemilik][alamatEntitas]" class="form-control form-control-sm" rows="3" placeholder="Alamat Pemilik">{{ $entPemilik['alamatEntitas'] ?? ($header->alamat_supplier ?? '') }}</textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>


                <div class="tab-pane fade" id="tab-dokumen" role="tabpanel">
                    <div class="card shadow-sm mb-3 border">
                        <div class="card-header text-white fw-bold d-flex align-items-center px-3 py-2" style="font-size:13px; background-color: #001f3f;">
                            <span>Dokumen Lampiran</span>
                            <button type="button" class="btn btn-sm btn-light py-0 px-2 ml-auto" id="btn-add-dok" title="Tambah Dokumen"><i class="fas fa-plus text-primary"></i> Tambah Dokumen</button>
                        </div>
                        <div class="card-body p-0" style="overflow-x: auto;">
                            <table class="table table-sm table-bordered mb-0" id="table-dokumen" style="min-width: 800px;">
                                <thead class="bg-light text-center">
                                    <tr>
                                        <th width="5%">Seri</th>
                                        <th width="25%">Jenis Dokumen</th>
                                        <th width="22%">Nomor</th>
                                        <th width="15%">Tanggal</th>
                                        <th width="12%">Fasilitas</th>
                                        <th width="13%">Kode Izin</th>
                                        <th width="8%">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody id="tbody-dokumen">
                                    @foreach($dokumens as $index => $dok)
                                        <tr>
                                            <td class="text-center align-middle">
                                                {{ $index + 1 }}
                                                <input type="hidden" name="dok[{{ $index }}][seriDokumen]" value="{{ $index + 1 }}">
                                            </td>
                                            <td>
                                                <select name="dok[{{ $index }}][kode]" class="form-control form-control-sm select2bs4">
                                                    <option value="">-- Pilih Kode --</option>
                                                    @foreach($referensiDokumen as $val => $text)
                                                        <option value="{{ $val }}" {{ ($dok['kodeDokumen'] ?? $dok['kode'] ?? '') == $val ? 'selected' : '' }}>
                                                            {{ $val }} - {{ $text }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </td>
                                            <td>
                                                <input type="text" name="dok[{{ $index }}][nomor]" class="form-control form-control-sm" value="{{ $dok['nomorDokumen'] ?? $dok['nomor'] ?? '' }}">
                                            </td>
                                            <td>
                                                <input type="date" name="dok[{{ $index }}][tgl]" class="form-control form-control-sm" value="{{ $dok['tanggalDokumen'] ?? $dok['tgl'] ?? '' }}">
                                            </td>
                                            <td><input type="text" name="dok[{{ $index }}][fasilitas]" class="form-control form-control-sm" value="{{ $dok['fasilitas'] ?? '' }}" placeholder="Kode Fasilitas"></td>
                                            <td><input type="text" name="dok[{{ $index }}][izin]" class="form-control form-control-sm" value="{{ $dok['izin'] ?? '' }}" placeholder="Kode Izin"></td>
                                            <td class="text-center align-middle">
                                                <button type="button" class="btn btn-sm btn-danger py-0 px-2 btn-hapus-dok"><i class="fas fa-trash-alt"></i></button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>


                <div class="tab-pane fade" id="tab-pengangkut" role="tabpanel">
                    <div class="card shadow-sm mb-3 border">
                        <div class="card-header fw-bold d-flex align-items-center px-3 py-2 bg-light text-dark" style="font-size:13px;">
                            <span>Sarana Pengangkut</span>
                            <button type="button" class="btn btn-sm btn-outline-primary py-0 px-2 ml-auto" id="btn-add-sarkut"><i class="fas fa-plus"></i> Tambah Pengangkut</button>
                        </div>
                        <div class="card-body p-0">
                            <table class="table table-sm table-bordered mb-0" id="table-sarkut">
                                <thead class="bg-light text-center">
                                    <tr>
                                        <th width="5%">Seri</th>
                                        <th width="35%">Nama Pengangkut</th>
                                        <th width="25%">No. Pengangkut / Plat / Voy</th>
                                        <th width="20%">Cara Angkut</th>
                                        <th width="15%">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody id="tbody-sarkut">
                                    @foreach($pengangkuts as $sIndex => $sarkut)
                                    <tr>
                                        <td class="text-center align-middle">
                                            <input type="text" class="form-control form-control-sm text-center bg-light" value="{{ $sIndex + 1 }}" readonly>
                                            <input type="hidden" name="pengangkut[{{ $sIndex }}][seriPengangkut]" value="{{ $sIndex + 1 }}">
                                            <input type="hidden" name="pengangkut[{{ $sIndex }}][kodeBendera]" value="ID">
                                        </td>
                                        <td><input type="text" name="pengangkut[{{ $sIndex }}][namaPengangkut]" class="form-control form-control-sm" value="{{ $sarkut['namaPengangkut'] ?? '' }}"></td>
                                        <td><input type="text" name="pengangkut[{{ $sIndex }}][nomorPengangkut]" class="form-control form-control-sm" value="{{ $sarkut['nomorPengangkut'] ?? '' }}"></td>
                                        <td>
                                            <select name="pengangkut[{{ $sIndex }}][kodeCaraAngkut]" class="form-control form-control-sm select2bs4">
    <option value="">-- Pilih --</option>
    @foreach($listCaraAngkut as $k => $v)
        <option value="{{ $k }}" {{ ($pengangkut['kodeCaraAngkut'] ?? '') == $k ? 'selected' : '' }}>{{ $k }} - {{ strtoupper($v) }}</option>
    @endforeach
</select>
                                        </td>
                                        <td class="text-center align-middle"><button type="button" class="btn btn-sm btn-danger py-0 px-2 btn-hapus-sarkut"><i class="fas fa-trash-alt"></i></button></td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>


                <div class="tab-pane fade" id="tab-kemasan" role="tabpanel">

                    <div class="card shadow-sm mb-3 border">
                        <div class="card-header fw-bold d-flex align-items-center px-3 py-2 bg-light text-dark" style="font-size:13px;">
                            <span>Data Kemasan</span>
                            <button type="button" class="btn btn-sm btn-outline-primary py-0 px-2 ml-auto" id="btn-add-kemasan" title="Tambah Kemasan"><i class="fas fa-plus"></i> Tambah Kemasan</button>
                        </div>
                        <div class="card-body p-0">
                            <table class="table table-sm table-bordered mb-0" id="table-kemasan">
                                <thead class="bg-light text-center">
                                    <tr>
                                        <th width="5%">Seri</th>
                                        <th width="18%">Jumlah</th>
                                        <th width="38%">Jenis Kemasan</th>
                                        <th width="29%">Merek</th>
                                        <th width="10%">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody id="tbody-kemasan">
                                    @foreach($kemasans as $kIndex => $kemasan)
                                    <tr>
                                        <td class="text-center align-middle">
                                            {{ $kIndex + 1 }}
                                            <input type="hidden" name="kemasan[{{ $kIndex }}][seriKemasan]" value="{{ $kIndex + 1 }}">
                                        </td>
                                        <td><input type="number" step="any" name="kemasan[{{ $kIndex }}][jumlahKemasan]" class="form-control form-control-sm" value="{{ $kemasan['jumlahKemasan'] ?? 0 }}"></td>
                                        <td>
                                            <select name="kemasan[{{ $kIndex }}][kodeJenisKemasan]" class="form-control form-control-sm select2bs4">
                                                <option value="">-- Pilih Jenis Kemasan --</option>
                                                @foreach($listJenisKemasan as $k => $v)
                                                    <option value="{{ $k }}" {{ ($kemasan['kodeJenisKemasan'] ?? '') == $k ? 'selected' : '' }}>{{ $k }} - {{ $v }}</option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td><input type="text" name="kemasan[{{ $kIndex }}][merkKemasan]" class="form-control form-control-sm" value="{{ $kemasan['merkKemasan'] ?? '-' }}"></td>
                                        <td class="text-center align-middle"><button type="button" class="btn btn-sm btn-danger py-0 px-2 btn-hapus-kemasan"><i class="fas fa-trash-alt"></i></button></td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>


                    <div class="card shadow-sm mb-3 border">
                        <div class="card-header fw-bold d-flex align-items-center px-3 py-2 bg-light text-dark" style="font-size:13px;">
                            <span>Data Petikemas (Kontainer)</span>
                            <button type="button" class="btn btn-sm btn-outline-primary py-0 px-2 ml-auto" id="btn-add-kontainer" title="Tambah Kontainer"><i class="fas fa-plus"></i> Tambah Petikemas</button>
                        </div>
                        <div class="card-body p-0">
                            @php
                                $listJenisKontainer = ['4' => 'Empty', '7' => 'LCL', '8' => 'FCL'];
                                $listTipeKontainer = [
                                    '1' => 'General/Dry Cargo', '2' => 'Tunnel Type', '3' => 'Open Top Steel',
                                    '4' => 'Flat Rack', '5' => 'Reefer/Refrigerated', '6' => 'Barge Container',
                                    '7' => 'Bulk Container', '8' => 'Isotank', '99' => 'Lain-lain'
                                ];
                                $listUkuranKontainer = ['20' => '20 Feet', '40' => '40 Feet', '45' => '45 Feet', '60' => '60 Feet'];
                            @endphp
                            <table class="table table-sm table-bordered mb-0" id="table-kontainer">
                                <thead class="bg-light text-center">
                                    <tr>
                                        <th width="25%">Nomor Kontainer</th>
                                        <th width="15%">Ukuran</th>
                                        <th width="20%">Jenis</th>
                                        <th width="20%">Tipe</th>
                                        <th width="12%">Jenis Muatan</th>
                                        <th width="8%">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody id="tbody-kontainer">
                                    @foreach($kontainers as $cIndex => $kont)
                                    <tr>
                                        <input type="hidden" name="kontainer[{{ $cIndex }}][seriKontainer]" value="{{ $cIndex + 1 }}">
                                        <td><input type="text" name="kontainer[{{ $cIndex }}][nomorKontainer]" class="form-control form-control-sm text-uppercase" value="{{ $kont['nomorKontainer'] ?? '' }}"></td>
                                        <td>
                                            <select name="kontainer[{{ $cIndex }}][kodeUkuranKontainer]" class="form-control form-control-sm select2bs4">
                                                <option value="">-- Pilih Ukuran --</option>
                                                @foreach($listUkuranKontainer as $k => $v)
                                                    <option value="{{ $k }}" {{ ($kont['kodeUkuranKontainer'] ?? '') == $k ? 'selected' : '' }}>{{ $k }} - {{ $v }}</option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td>
                                            <select name="kontainer[{{ $cIndex }}][kodeJenisKontainer]" class="form-control form-control-sm select2bs4">
                                                <option value="">-- Pilih Jenis --</option>
                                                @foreach($listJenisKontainer as $k => $v)
                                                    <option value="{{ $k }}" {{ ($kont['kodeJenisKontainer'] ?? '') == $k ? 'selected' : '' }}>{{ $k }} - {{ $v }}</option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td>
                                            <select name="kontainer[{{ $cIndex }}][kodeTipeKontainer]" class="form-control form-control-sm select2bs4">
                                                <option value="">-- Pilih Tipe --</option>
                                                @foreach($listTipeKontainer as $k => $v)
                                                    <option value="{{ $k }}" {{ ($kont['kodeTipeKontainer'] ?? '') == $k ? 'selected' : '' }}>{{ $k }} - {{ $v }}</option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td><input type="text" name="kontainer[{{ $cIndex }}][jenisMuatan]" class="form-control form-control-sm" value="{{ $kont['jenisMuatan'] ?? '' }}"></td>
                                        <td class="text-center align-middle"><button type="button" class="btn btn-sm btn-danger py-0 px-2 btn-hapus-kontainer"><i class="fas fa-trash-alt"></i></button></td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>


                <div class="tab-pane fade" id="tab-transaksi" role="tabpanel">
                    <div class="row">

                        <div class="col-md-4">
                            <div class="card shadow-sm mb-3 border">
                                <div class="card-header fw-bold bg-light text-dark px-3 py-2" style="font-size:13px;">Nilai Transaksi</div>
                                <div class="card-body">
                                    <div class="form-group mb-2">
                                        <label>Harga Penyerahan</label>
                                        <input type="number" step="any" name="hargaPenyerahan" class="form-control form-control-sm" value="{{ $dataDetail['hargaPenyerahan'] ?? '0.00' }}">
                                    </div>
                                    <div class="form-group mb-2">
                                        <label>Nilai Jasa</label>
                                        <input type="number" step="any" name="nilaiJasa" class="form-control form-control-sm" value="{{ $dataDetail['nilaiJasa'] ?? '0.00' }}">
                                    </div>
                                    <div class="form-group mb-2">
                                        <label>Uang Muka</label>
                                        <input type="number" step="any" name="uangMuka" class="form-control form-control-sm" value="{{ $dataDetail['uangMuka'] ?? '0.00' }}">
                                    </div>
                                    <div class="form-group mb-2">
                                        <label>Diskon</label>
                                        <input type="number" step="any" name="diskon" class="form-control form-control-sm" value="{{ $dataDetail['diskon'] ?? '0.00' }}">
                                    </div>
                                    <div class="form-group mb-0">
                                        <label>Harga Perolehan</label>
                                        <input type="number" step="any" name="hargaPerolehan" class="form-control form-control-sm" value="{{ $dataDetail['hargaPerolehan'] ?? '0.00' }}">
                                    </div>
                                </div>
                            </div>
                        </div>


                        <div class="col-md-4">
                            <div class="card shadow-sm mb-3 border">
                                <div class="card-header fw-bold bg-light text-dark px-3 py-2" style="font-size:13px;">Perhitungan Pajak</div>
                                <div class="card-body">
                                    <div class="form-group mb-2">
                                        <label>Dasar Pengenaan Pajak (DPP)</label>
                                        <input type="number" step="any" name="dasarPengenaanPajak" class="form-control form-control-sm" value="{{ $dataDetail['dasarPengenaanPajak'] ?? '0.00' }}">
                                    </div>
                                    <div class="form-group mb-2">
                                        <label>Tarif PPN (%)</label>
                                        <input type="number" step="any" name="ppnTarif" class="form-control form-control-sm" value="{{ $dataDetail['ppnTarif'] ?? '11' }}">
                                    </div>
                                    <div class="form-group mb-2">
                                        <label>Nilai PPN</label>
                                        <input type="number" step="any" name="ppnNilai" class="form-control form-control-sm" value="{{ $dataDetail['ppnNilai'] ?? '0.00' }}">
                                    </div>
                                    <div class="form-group mb-2">
                                        <label>Tarif PPnBM (%)</label>
                                        <input type="number" step="any" name="ppnbmTarif" class="form-control form-control-sm" value="{{ $dataDetail['ppnbmTarif'] ?? '0' }}">
                                    </div>
                                    <div class="form-group mb-0">
                                        <label>Nilai PPnBM</label>
                                        <input type="number" step="any" name="ppnbmNilai" class="form-control form-control-sm" value="{{ $dataDetail['ppnbmNilai'] ?? '0.00' }}">
                                    </div>
                                </div>
                            </div>
                        </div>


                        <div class="col-md-4">
                            <div class="card shadow-sm mb-3 border">
                                <div class="card-header fw-bold bg-light text-dark px-3 py-2" style="font-size:13px;">Volume & Berat</div>
                                <div class="card-body">
                                    <div class="form-group mb-2">
                                        <label>Volume (M3)</label>
                                        <input type="number" step="any" name="volume" class="form-control form-control-sm" value="{{ $dataDetail['volume'] ?? '0.00' }}">
                                    </div>
                                    <div class="form-group mb-2">
                                        <label>Berat Kotor / Bruto (Kg)</label>
                                        <input type="number" step="any" name="bruto" class="form-control form-control-sm" value="{{ $dataDetail['bruto'] ?? '0.00' }}">
                                        <small class="text-muted" style="font-size: 10px;">Berat Kotor harus lebih besar dari 0</small>
                                    </div>
                                    <div class="form-group mb-0">
                                        <label>Berat Bersih / Netto (Kg)</label>
                                        <input type="number" step="any" name="netto" class="form-control form-control-sm" value="{{ $dataDetail['netto'] ?? '0.00' }}">
                                        <small class="text-muted" style="font-size: 10px;">Berat Bersih harus lebih besar dari 0</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>


                <div class="tab-pane fade" id="tab-barang" role="tabpanel">
                    <div class="card shadow-sm mb-3 border">
                        <div class="card-header text-white fw-bold px-3 py-2" style="font-size:13px; background-color: #001f3f;">
                            <i class="fas fa-boxes"></i> Rincian Barang ({{ count($items) }} Item)
                        </div>
                        <div class="card-body">
                            <div class="accordion" id="accordionBarang">
                                @foreach($items as $index => $item)
                                @php
                                    $draftItem = $dataDetail['barang'][$index] ?? [];
                                @endphp

                                <div class="card mb-2 border">
                                    <div class="card-header py-2 btn-collapse-barang" data-target="#collapseBarang{{ $index }}" style="cursor: pointer; background-color: #001f3f;">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div class="fw-bold text-white" style="font-size: 13px;">
                                                {{ $item->goods_code ?? $item->id_item }} - {{ $item->itemdesc }}
                                            </div>
                                            <i class="fas fa-chevron-down text-white icon-collapse"></i>
                                        </div>
                                    </div>

                                    <div id="collapseBarang{{ $index }}" class="collapse {{ $index == 0 ? 'show' : '' }}" data-parent="#accordionBarang">
                                        <div class="card-body py-3 px-3 bg-white">


                                            <input type="hidden" name="barang[{{ $index }}][seriBarang]" value="{{ $index + 1 }}">

                                            <div class="row">

                                                <div class="col-md-4">
                                                    <div class="card shadow-sm mb-3 border">
                                                        <div class="card-header fw-bold bg-light text-dark px-3 py-2" style="font-size:13px;">Identifikasi Barang</div>
                                                        <div class="card-body">
                                                            <div class="form-group mb-2">
                                                                <label>Pos Tarif / HS</label>
                                                                <input type="text" name="barang[{{ $index }}][posTarif]" class="form-control form-control-sm" value="{{ $draftItem['posTarif'] ?? '' }}" placeholder="Pos Tarif / HS">
                                                            </div>
                                                            <div class="form-group mb-2">
                                                                <label>Kode Barang</label>
                                                                <input type="text" name="barang[{{ $index }}][kodeBarang]" class="form-control form-control-sm" value="{{ $draftItem['kodeBarang'] ?? $item->goods_code ?? $item->id_item ?? '-' }}">
                                                            </div>
                                                            <div class="form-group mb-2">
                                                                <label>Uraian Barang</label>
                                                                <textarea name="barang[{{ $index }}][uraian]" class="form-control form-control-sm" rows="3">{{ $draftItem['uraian'] ?? $item->itemdesc ?? '' }}</textarea>
                                                            </div>
                                                            <div class="form-group mb-2">
                                                                <label>Merek</label>
                                                                <input type="text" name="barang[{{ $index }}][merk]" class="form-control form-control-sm" value="{{ $draftItem['merk'] ?? '-' }}">
                                                            </div>
                                                            <div class="form-group mb-2">
                                                                <label>Tipe</label>
                                                                <input type="text" name="barang[{{ $index }}][tipe]" class="form-control form-control-sm" value="{{ $draftItem['tipe'] ?? '-' }}">
                                                            </div>
                                                            <div class="form-group mb-2">
                                                                <label>Ukuran</label>
                                                                <input type="text" name="barang[{{ $index }}][ukuran]" class="form-control form-control-sm" value="{{ $draftItem['ukuran'] ?? '-' }}">
                                                            </div>
                                                            <div class="form-group mb-0">
                                                                <label>Spesifikasi Lain</label>
                                                                <input type="text" name="barang[{{ $index }}][spesifikasiLain]" class="form-control form-control-sm" value="{{ $draftItem['spesifikasiLain'] ?? '-' }}">
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>


                                                <div class="col-md-4">
                                                    <div class="card shadow-sm mb-3 border">
                                                        <div class="card-header fw-bold bg-light text-dark px-3 py-2" style="font-size:13px;">Kuantitas & Nilai</div>
                                                        <div class="card-body">
                                                            <div class="form-group mb-2">
                                                                <label>Jumlah Satuan</label>
                                                                <div class="row">
                                                                    <div class="col-sm-6 pr-1">
                                                                        <input type="number" step="any" name="barang[{{ $index }}][jumlahSatuan]" class="form-control form-control-sm" value="{{ $draftItem['jumlahSatuan'] ?? (float) $item->qty }}">
                                                                    </div>
                                                                    <div class="col-sm-6 pl-1">
                                                                        <select name="barang[{{ $index }}][kodeSatuanBarang]" class="form-control form-control-sm select2bs4">
                                                                            <option value="">-- Satuan --</option>
                                                                            @foreach($listSatuanBarang as $k => $v)
                                                                                <option value="{{ $k }}" {{ ($draftItem['kodeSatuanBarang'] ?? $item->unit ?? '') == $k ? 'selected' : '' }}>{{ $k }} - {{ $v }}</option>
                                                                            @endforeach
                                                                        </select>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="form-group mb-2">
                                                                <label>Kemasan</label>
                                                                <div class="row">
                                                                    <div class="col-sm-6 pr-1">
                                                                        <input type="number" step="any" name="barang[{{ $index }}][jumlahKemasan]" class="form-control form-control-sm" value="{{ $draftItem['jumlahKemasan'] ?? 0 }}">
                                                                    </div>
                                                                    <div class="col-sm-6 pl-1">
                                                                        <select name="barang[{{ $index }}][kodeJenisKemasan]" class="form-control form-control-sm select2bs4">
                                                                            <option value="">-- Kemasan --</option>
                                                                            @foreach($listJenisKemasan as $k => $v)
                                                                                <option value="{{ $k }}" {{ ($draftItem['kodeJenisKemasan'] ?? 'CT') == $k ? 'selected' : '' }}>{{ $k }} - {{ $v }}</option>
                                                                            @endforeach
                                                                        </select>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="form-group mb-2">
                                                                <label>Volume (M3)</label>
                                                                <input type="number" step="any" name="barang[{{ $index }}][volume]" class="form-control form-control-sm" value="{{ $draftItem['volume'] ?? '0.00' }}">
                                                            </div>
                                                            <div class="form-group mb-2">
                                                                <label>Berat Bersih (Netto Kg)</label>
                                                                <input type="number" step="any" name="barang[{{ $index }}][netto]" class="form-control form-control-sm" value="{{ $draftItem['netto'] ?? (float) ($item->nw ?? $item->netto ?? 0) }}">
                                                            </div>
                                                            <div class="form-group mb-2">
                                                                <label>Kategori Barang</label>
                                                                <select name="barang[{{ $index }}][kodeKategoriBarang]" class="form-control form-control-sm select2bs4">
                                                                    <option value="">Pilih Kategori</option>
                                                                    <optgroup label="BC 2.3">
                                                                    @foreach($listKategoriBarang['23'] as $kode => $nama)
                                                                        <option value="{{ $kode }}" {{ ($draftItem['kodeKategoriBarang'] ?? '') == $kode ? 'selected' : '' }}>{{ $kode }} - {{ $nama }}</option>
                                                                    @endforeach
                                                                    </optgroup>
                                                                    <optgroup label="BC 2.5">
                                                                    @foreach($listKategoriBarang['25'] as $kode => $nama)
                                                                        <option value="{{ $kode }}" {{ ($draftItem['kodeKategoriBarang'] ?? '') == $kode ? 'selected' : '' }}>{{ $kode }} - {{ $nama }}</option>
                                                                    @endforeach
                                                                    </optgroup>
                                                                </select>
                                                            </div>
                                                            <div class="form-group mb-2">
                                                                <label>Harga Penyerahan</label>
                                                                <input type="number" step="any" name="barang[{{ $index }}][hargaPenyerahan]" class="form-control form-control-sm" value="{{ $draftItem['hargaPenyerahan'] ?? (float)($item->qty * $item->price) }}">
                                                            </div>
                                                            <div class="form-group mb-0">
                                                                <label>Nilai Jasa</label>
                                                                <input type="number" step="any" name="barang[{{ $index }}][nilaiJasa]" class="form-control form-control-sm" value="{{ $draftItem['nilaiJasa'] ?? '0.00' }}">
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>


                                                <div class="col-md-4">
                                                    <div class="card shadow-sm mb-3 border">
                                                        <div class="card-header fw-bold d-flex align-items-center px-3 py-2 bg-light text-dark" style="font-size:13px;">
                                                            <span>Bahan Baku Lokal</span>
                                                            <button type="button" class="btn btn-sm btn-primary py-0 px-2 ml-auto btn-add-bahan-baku" data-itemidx="{{ $index }}"><i class="fas fa-plus"></i> Tambah</button>
                                                        </div>
                                                        <div class="card-body p-0">
                                                            <div class="table-responsive">
                                                                <table class="table table-sm table-bordered mb-0">
                                                                    <thead class="bg-light text-center" style="font-size: 12px;">
                                                                        <tr>
                                                                            <th width="25%">Pos Tarif / HS</th>
                                                                            <th width="35%">Uraian Bahan Baku</th>
                                                                            <th width="25%">Nilai Barang</th>
                                                                            <th width="15%">Aksi</th>
                                                                        </tr>
                                                                    </thead>
                                                                    <tbody id="tbody-bahan-baku-{{ $index }}">
                                                                        @php $bahanBakuLokal = $draftItem['bahanBakuLokal'] ?? []; @endphp
                                                                        @foreach($bahanBakuLokal as $bbIndex => $bb)
                                                                        <tr>
                                                                            <input type="hidden" name="barang[{{ $index }}][bahanBakuLokal][{{ $bbIndex }}][seriBahanBaku]" value="{{ $bbIndex + 1 }}">
                                                                            <td class="p-1"><input type="text" name="barang[{{ $index }}][bahanBakuLokal][{{ $bbIndex }}][hs]" class="form-control form-control-sm" value="{{ $bb['hs'] ?? '' }}" placeholder="HS"></td>
                                                                            <td class="p-1">
                                                                                <input type="text" name="barang[{{ $index }}][bahanBakuLokal][{{ $bbIndex }}][uraian]" class="form-control form-control-sm mb-1" value="{{ $bb['uraian'] ?? '' }}" placeholder="Uraian">
                                                                                <select name="barang[{{ $index }}][bahanBakuLokal][{{ $bbIndex }}][kodeSatuan]" class="form-control form-control-sm select2bs4">
                                                                                    <option value="">Satuan</option>
                                                                                    @foreach($listSatuanBarang as $k => $v)
                                                                                        <option value="{{ $k }}" {{ ($bb['kodeSatuan'] ?? '') == $k ? 'selected' : '' }}>{{ $k }} - {{ $v }}</option>
                                                                                    @endforeach
                                                                                </select>
                                                                            </td>
                                                                            <td class="p-1"><input type="number" step="any" name="barang[{{ $index }}][bahanBakuLokal][{{ $bbIndex }}][nilaiBarang]" class="form-control form-control-sm" value="{{ $bb['nilaiBarang'] ?? 0 }}"></td>
                                                                            <td class="text-center p-1 align-middle"><button type="button" class="btn btn-sm btn-outline-danger btn-hapus-bb"><i class="fas fa-trash-alt"></i></button></td>
                                                                        </tr>
                                                                        @endforeach
                                                                    </tbody>
                                                                </table>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>


                <div class="tab-pane fade" id="tab-pungutan" role="tabpanel">

                    <div class="card shadow-sm mb-3 border">
                        <div class="card-header text-white fw-bold px-3 py-2" style="font-size:13px; background-color: #001f3f;">Pembayaran Pungutan</div>
                        <div class="card-body">
                            <div class="row">

                                <div class="col-md-4 border-right">
                                    <h6 class="font-weight-bold text-muted mb-3" style="font-size: 13px;">Lokasi Bayar</h6>
                                    <div class="form-group mb-2">
                                        <label class="small text-muted mb-0">Cara Bayar</label>
                                        <input type="text" name="caraBayar" class="form-control form-control-sm fw-bold" value="{{ $dataDetail['caraBayar'] ?? 'BANK' }}" readonly>
                                    </div>
                                </div>

                                <div class="col-md-4 border-right">
                                    <h6 class="font-weight-bold text-muted mb-3" style="font-size: 13px;">Wajib Bayar</h6>
                                    <div class="form-group mb-2">
                                        <label class="small text-muted mb-0">Wajib Bayar</label>
                                        <select name="wajibBayar" class="form-control form-control-sm select2bs4">
                                            @php
                                                $wb = $dataDetail['wajibBayar'] ?? '';
                                                $entTpb = $dataDetail['entitas']['tpb']['nomorIdentitas'] ?? '0745406926444000000000';
                                                $entPemilik = $dataDetail['entitas']['pemilik']['nomorIdentitas'] ?? '020812054422000';
                                                $entPenerima = $dataDetail['entitas']['penerima']['nomorIdentitas'] ?? '020812954422000';
                                            @endphp
                                            <option value="">-- Pilih --</option>
                                            <option value="{{ '3 - '.$entTpb }}" {{ $wb == ('3 - '.$entTpb) ? 'selected' : '' }}>3 - {{ $entTpb }}</option>
                                            <option value="{{ '7 - '.$entPemilik }}" {{ $wb == ('7 - '.$entPemilik) ? 'selected' : '' }}>7 - {{ $entPemilik }}</option>
                                            <option value="{{ '8 - '.$entPenerima }}" {{ $wb == ('8 - '.$entPenerima) ? 'selected' : '' }}>8 - {{ $entPenerima }}</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <h6 class="font-weight-bold text-muted mb-3" style="font-size: 13px;">Bukti Bayar</h6>
                                    <div class="form-group mb-2">
                                        <label class="small text-muted mb-0">Nomor Bukti Bayar</label>
                                        <input type="text" name="nomorBuktiBayar" class="form-control form-control-sm" value="{{ $dataDetail['nomorBuktiBayar'] ?? '' }}">
                                    </div>
                                    <div class="form-group mb-0">
                                        <label class="small text-muted mb-0">Tanggal Bukti Bayar</label>
                                        <input type="date" name="tanggalBuktiBayar" class="form-control form-control-sm" value="{{ $dataDetail['tanggalBuktiBayar'] ?? '' }}">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>


                <div class="tab-pane fade" id="tab-pernyataan" role="tabpanel">
                    <div class="card shadow-sm mb-3 border">
                        <div class="card-header text-white fw-bold px-3 py-2" style="font-size:13px; background-color: #001f3f;">Pernyataan & Penandatangan</div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-3 form-group"><label>Nama TTD</label><input type="text" name="namaTtd" class="form-control form-control-sm" value="{{ $dataDetail['namaTtd'] ?? '' }}" placeholder="Nama Lengkap"></div>
                                <div class="col-md-3 form-group"><label>Jabatan</label><input type="text" name="jabatanTtd" class="form-control form-control-sm" value="{{ $dataDetail['jabatanTtd'] ?? '' }}" placeholder="Jabatan"></div>
                                <div class="col-md-3 form-group"><label>Tempat / Kota TTD</label><input type="text" name="tempatTtd" class="form-control form-control-sm" value="{{ $dataDetail['tempatTtd'] ?? '' }}" placeholder="Kota"></div>
                                <div class="col-md-3 form-group"><label>Tanggal TTD</label><input type="date" name="tanggalTtd" class="form-control form-control-sm" value="{{ $dataDetail['tanggalTtd'] ?? date('Y-m-d') }}"></div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <div class="card-footer text-right bg-white border-top">
            <a href="{{ route('dokumen-pabean-index') }}" class="btn btn-default btn-sm"><i class="fas fa-arrow-left"></i> Kembali</a>
            <button type="submit" class="btn btn-success btn-sm"><i class="fas fa-save"></i> Simpan Draft</button>
        </div>
    </form>
</div>
@endsection

@section('custom-script')
<script src="{{ asset('plugins/select2/js/select2.full.min.js') }}"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    $(document).ready(function() {
        $('.select2bs4').select2({ theme: 'bootstrap4', width: '100%' });

        $('#ceisaTab a').on('click', function (e) {
            e.preventDefault();
            $(this).tab('show');
        });

        // ================= DOKUMEN HANDLER =================
        const optDokumenHtml = `
            <option value="">-- Pilih Kode --</option>
            @foreach($referensiDokumen as $val => $text) <option value="{{ $val }}">{{ $val }} - {{ $text }}</option> @endforeach
        `;
        let dokIndex = {{ count($dokumens ?? []) }};
        $('#btn-add-dok').on('click', function() {
            let htmlTr = `
                <tr>
                    <td class="text-center align-middle">${dokIndex + 1}<input type="hidden" name="dok[${dokIndex}][seriDokumen]" value="${dokIndex + 1}"></td>
                    <td><select name="dok[${dokIndex}][kode]" class="form-control form-control-sm select2bs4-dynamic">${optDokumenHtml}</select></td>
                    <td><input type="text" name="dok[${dokIndex}][nomor]" class="form-control form-control-sm"></td>
                    <td><input type="date" name="dok[${dokIndex}][tgl]" class="form-control form-control-sm"></td>
                    <td><input type="text" name="dok[${dokIndex}][fasilitas]" class="form-control form-control-sm" placeholder="Kode Fasilitas"></td>
                    <td><input type="text" name="dok[${dokIndex}][izin]" class="form-control form-control-sm" placeholder="Kode Izin"></td>
                    <td class="text-center align-middle"><button type="button" class="btn btn-sm btn-danger py-0 px-2 btn-hapus-dok"><i class="fas fa-trash-alt"></i></button></td>
                </tr>`;
            $('#tbody-dokumen').append(htmlTr);
            $(`select[name="dok[${dokIndex}][kode]"]`).select2({ theme: 'bootstrap4', width: '100%' });
            dokIndex++;
        });
        $(document).on('click', '.btn-hapus-dok', function() { $(this).closest('tr').remove(); });

        // ================= SARKUT HANDLER =================
        let sarkutIndex = {{ count($pengangkuts ?? []) }};
        $('#btn-add-sarkut').on('click', function() {
            let tr = `<tr>
                <td class="text-center align-middle">
                    <input type="text" class="form-control form-control-sm text-center bg-light" value="${sarkutIndex + 1}" readonly>
                    <input type="hidden" name="pengangkut[${sarkutIndex}][seriPengangkut]" value="${sarkutIndex + 1}">
                    <input type="hidden" name="pengangkut[${sarkutIndex}][kodeBendera]" value="ID">
                </td>
                <td><input type="text" name="pengangkut[${sarkutIndex}][namaPengangkut]" class="form-control form-control-sm"></td>
                <td><input type="text" name="pengangkut[${sarkutIndex}][nomorPengangkut]" class="form-control form-control-sm"></td>
                <td>
                    <select name="pengangkut[${sarkutIndex}][kodeCaraAngkut]" class="form-control form-control-sm select2bs4-dynamic">
    <option value="">-- Pilih --</option>
    @foreach($listCaraAngkut as $k => $v)
        <option value="{{ $k }}">{{ $k }} - {{ strtoupper($v) }}</option>
    @endforeach
</select>
                </td>
                <td class="text-center align-middle"><button type="button" class="btn btn-sm btn-danger py-0 px-2 btn-hapus-sarkut"><i class="fas fa-trash-alt"></i></button></td>
            </tr>`;
            $('#tbody-sarkut').append(tr);
            sarkutIndex++;
        });
        $(document).on('click', '.btn-hapus-sarkut', function() { $(this).closest('tr').remove(); });

        // ================= KEMASAN HANDLER =================
        const optJenisKemasan = `
            <option value="">-- Pilih --</option>
            @foreach($listJenisKemasan as $kKem => $vKem) <option value="{{ $kKem }}">{{ $kKem }} - {{ $vKem }}</option> @endforeach
        `;
        let kemasanIndex = {{ count($kemasans ?? []) }};
        $('#btn-add-kemasan').on('click', function() {
            let htmlTr = `
                <tr>
                    <td class="text-center align-middle">${kemasanIndex + 1}<input type="hidden" name="kemasan[${kemasanIndex}][seriKemasan]" value="${kemasanIndex + 1}"></td>
                    <td><input type="number" step="any" name="kemasan[${kemasanIndex}][jumlahKemasan]" class="form-control form-control-sm" value="0"></td>
                    <td><select name="kemasan[${kemasanIndex}][kodeJenisKemasan]" class="form-control form-control-sm select2bs4-dynamic">${optJenisKemasan}</select></td>
                    <td><input type="text" name="kemasan[${kemasanIndex}][merkKemasan]" class="form-control form-control-sm" value="-"></td>
                    <td class="text-center align-middle"><button type="button" class="btn btn-sm btn-danger py-0 px-2 btn-hapus-kemasan"><i class="fas fa-trash-alt"></i></button></td>
                </tr>`;
            $('#tbody-kemasan').append(htmlTr);
            $(`select[name="kemasan[${kemasanIndex}][kodeJenisKemasan]"]`).select2({ theme: 'bootstrap4', width: '100%' });
            kemasanIndex++;
        });
        $(document).on('click', '.btn-hapus-kemasan', function() { $(this).closest('tr').remove(); });

        // ================= KONTAINER HANDLER =================
        const optJenisKontainer = `<option value="">-- Pilih --</option>` + `@foreach($listJenisKontainer as $k => $v)<option value="{{ $k }}">{{ $k }} - {{ $v }}</option>@endforeach`;
        const optTipeKontainer = `<option value="">-- Pilih --</option>` + `@foreach($listTipeKontainer as $k => $v)<option value="{{ $k }}">{{ $k }} - {{ $v }}</option>@endforeach`;
        const optUkuranKontainer = `<option value="">-- Pilih --</option>` + `@foreach($listUkuranKontainer as $k => $v)<option value="{{ $k }}">{{ $k }} - {{ $v }}</option>@endforeach`;
        let kontainerIndex = {{ count($kontainers ?? []) }};
        $('#btn-add-kontainer').on('click', function() {
            let htmlTr = `
                <tr>
                    <input type="hidden" name="kontainer[${kontainerIndex}][seriKontainer]" value="${kontainerIndex + 1}">
                    <td><input type="text" name="kontainer[${kontainerIndex}][nomorKontainer]" class="form-control form-control-sm text-uppercase"></td>
                    <td><select name="kontainer[${kontainerIndex}][kodeUkuranKontainer]" class="form-control form-control-sm select2bs4">${optUkuranKontainer}</select></td>
                    <td><select name="kontainer[${kontainerIndex}][kodeJenisKontainer]" class="form-control form-control-sm select2bs4">${optJenisKontainer}</select></td>
                    <td><select name="kontainer[${kontainerIndex}][kodeTipeKontainer]" class="form-control form-control-sm select2bs4">${optTipeKontainer}</select></td>
                    <td><input type="text" name="kontainer[${kontainerIndex}][jenisMuatan]" class="form-control form-control-sm"></td>
                    <td class="text-center align-middle"><button type="button" class="btn btn-sm btn-danger py-0 px-2 btn-hapus-kontainer"><i class="fas fa-trash-alt"></i></button></td>
                </tr>`;
            $('#tbody-kontainer').append(htmlTr);
            kontainerIndex++;
        });
        $(document).on('click', '.btn-hapus-kontainer', function() { $(this).closest('tr').remove(); });

        // ================= BAHAN BAKU LOKAL HANDLER =================
        const optSatuanHtml = `
            <option value="">Satuan</option>
            @foreach($listSatuanBarang as $k => $v) <option value="{{ $k }}">{{ $k }} - {{ $v }}</option> @endforeach
        `;
        $(document).on('click', '.btn-add-bahan-baku', function() {
            let itemIdx = $(this).data('itemidx');
            let tbody = $(`#tbody-bahan-baku-${itemIdx}`);
            let rowIdx = tbody.find('tr').length;

            let tr = `<tr>
                <input type="hidden" name="barang[${itemIdx}][bahanBakuLokal][${rowIdx}][seriBahanBaku]" value="${rowIdx + 1}">
                <td class="p-1"><input type="text" name="barang[${itemIdx}][bahanBakuLokal][${rowIdx}][hs]" class="form-control form-control-sm" placeholder="HS"></td>
                <td class="p-1">
                    <input type="text" name="barang[${itemIdx}][bahanBakuLokal][${rowIdx}][uraian]" class="form-control form-control-sm mb-1" placeholder="Uraian">
                    <select name="barang[${itemIdx}][bahanBakuLokal][${rowIdx}][kodeSatuan]" class="form-control form-control-sm select2bs4-dynamic">${optSatuanHtml}</select>
                </td>
                <td class="p-1"><input type="number" step="any" name="barang[${itemIdx}][bahanBakuLokal][${rowIdx}][nilaiBarang]" class="form-control form-control-sm" value="0"></td>
                <td class="text-center p-1 align-middle"><button type="button" class="btn btn-sm btn-outline-danger btn-hapus-bb"><i class="fas fa-trash-alt"></i></button></td>
            </tr>`;
            tbody.append(tr);
            $(`select[name="barang[${itemIdx}][bahanBakuLokal][${rowIdx}][kodeSatuan]"]`).select2({ theme: 'bootstrap4', width: '100%' });
        });
        $(document).on('click', '.btn-hapus-bb', function() { $(this).closest('tr').remove(); });

        // ================= ACCORDION & AJAX SUBMIT =================
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

        $('#form-edit-ceisa').on('submit', function(e) {
            e.preventDefault();
            let form = $(this);
            Swal.fire({
                title: 'Simpan Perubahan?',
                text: "Data draft BC 4.1 akan diperbarui.",
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#28a745',
                confirmButtonText: 'Ya, Simpan!'
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({ title: 'Menyimpan...', allowOutsideClick: false, didOpen: () => { Swal.showLoading(); }});
                    $.ajax({
                        url: form.attr('action'),
                        type: form.attr('method') || 'POST',
                        data: form.serialize(),
                        success: function(response) {
                            Swal.fire({
                                title: 'Berhasil!',
                                text: response.message || 'Draft BC 4.1 berhasil disimpan.',
                                icon: 'success'
                            }).then(() => {
                                window.location.reload();
                            });
                        },
                        error: function(xhr) {
                            Swal.fire({
                                title: 'Gagal!',
                                text: xhr.responseJSON?.message || 'Terjadi kesalahan saat menyimpan data.',
                                icon: 'error'
                            });
                        }
                    });
                }
            });
        });
    });
</script>
@endsection
