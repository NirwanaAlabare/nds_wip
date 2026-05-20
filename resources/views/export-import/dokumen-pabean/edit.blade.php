@extends('layouts.index')

@section('custom-link')
<link rel="stylesheet" href="{{ asset('plugins/select2/css/select2.min.css') }}">
<link rel="stylesheet" href="{{ asset('plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}">
<style>
    .nav-tabs .nav-link.active {
        font-weight: bold;
        color: var(--sb-color) !important;
        border-bottom: 3px solid var(--sb-color);
    }
    .form-group label {
        font-size: 13px;
        font-weight: 600;
        margin-bottom: 0.2rem;
    }
    .form-control-sm {
        font-size: 13px;
    }
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
            </div>

            <ul class="nav nav-tabs" id="ceisaTab" role="tablist">
                <li class="nav-item">
                    <a class="nav-link active" id="header-tab" data-toggle="tab" href="#tab-header" role="tab">Data Header & Nilai</a>
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
                            <input type="text" name="nomorAju" class="form-control form-control-sm" value="{{ $ceisaInfo->nomor_aju ?? '' }}" readonly placeholder="Generate Otomatis">
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
                            <select name="kodeTujuanPengiriman" class="form-control form-control-sm select2bs4">
                                <option value="1" {{ ($dataDetail['kodeTujuanPengiriman'] ?? '1') == '1' ? 'selected' : '' }}>1 - Penyerahan BKP</option>
                            </select>
                        </div>
                    </div>

                    <div class="section-title">Data Nilai & Fisik</div>
                    <div class="row">
                        <div class="col-md-3 form-group">
                            <label>Bruto (Kg)</label>
                            <input type="number" step="0.0001" name="bruto" class="form-control form-control-sm" value="{{ $dataDetail['bruto'] ?? $header->berat_kotor ?? 0 }}">
                        </div>
                        <div class="col-md-3 form-group">
                            <label>Netto (Kg)</label>
                            <input type="number" step="0.0001" name="netto" class="form-control form-control-sm" value="{{ $dataDetail['netto'] ?? $header->berat_bersih ?? 0 }}">
                        </div>
                        <div class="col-md-3 form-group">
                            <label>Volume (m3)</label>
                            <input type="number" step="0.0001" name="volume" class="form-control form-control-sm" value="{{ $dataDetail['volume'] ?? 0 }}">
                        </div>
                        <div class="col-md-3 form-group">
                            <label>Harga Penyerahan (Rp)</label>
                            <input type="number" step="0.01" name="hargaPenyerahan" class="form-control form-control-sm" value="{{ $dataDetail['hargaPenyerahan'] ?? 0 }}">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-2 form-group"><label>Asuransi</label><input type="number" name="asuransi" class="form-control form-control-sm" value="{{ $dataDetail['asuransi'] ?? 0 }}"></div>
                        <div class="col-md-2 form-group"><label>Freight</label><input type="number" name="freight" class="form-control form-control-sm" value="{{ $dataDetail['freight'] ?? 0 }}"></div>
                        <div class="col-md-2 form-group"><label>Biaya Tambahan</label><input type="number" name="biayaTambahan" class="form-control form-control-sm" value="{{ $dataDetail['biayaTambahan'] ?? 0 }}"></div>
                        <div class="col-md-2 form-group"><label>Biaya Pengurang</label><input type="number" name="biayaPengurang" class="form-control form-control-sm" value="{{ $dataDetail['biayaPengurang'] ?? 0 }}"></div>
                        <div class="col-md-2 form-group"><label>Uang Muka</label><input type="number" name="uangMuka" class="form-control form-control-sm" value="{{ $dataDetail['uangMuka'] ?? 0 }}"></div>
                        <div class="col-md-2 form-group"><label>Nilai Jasa</label><input type="number" name="nilaiJasa" class="form-control form-control-sm" value="{{ $dataDetail['nilaiJasa'] ?? 0 }}"></div>
                    </div>

                    <div class="section-title">Penandatangan</div>
                    <div class="row">
                        <div class="col-md-3 form-group">
                            <label>Nama TTD</label>
                            <input type="text" name="namaTtd" class="form-control form-control-sm" value="{{ $dataDetail['namaTtd'] ?? 'USER EXIM' }}">
                        </div>
                        <div class="col-md-3 form-group">
                            <label>Jabatan</label>
                            <input type="text" name="jabatanTtd" class="form-control form-control-sm" value="{{ $dataDetail['jabatanTtd'] ?? 'EXIM STAFF' }}">
                        </div>
                        <div class="col-md-3 form-group">
                            <label>Kota TTD</label>
                            <input type="text" name="kotaTtd" class="form-control form-control-sm" value="{{ $dataDetail['kotaTtd'] ?? 'BANDUNG' }}">
                        </div>
                        <div class="col-md-3 form-group">
                            <label>Tanggal TTD</label>
                            <input type="date" name="tanggalTtd" class="form-control form-control-sm" value="{{ $dataDetail['tanggalTtd'] ?? date('Y-m-d') }}">
                        </div>
                    </div>
                </div>

                <div class="tab-pane fade" id="tab-entitas" role="tabpanel">

                    <div class="section-title"><i class="fas fa-building"></i> Entitas Pengusaha TPB</div>
                    <div class="row">
                        <div class="col-md-4 form-group">
                            <label>Nama Entitas</label>
                            <input type="text" name="entitas[3][namaEntitas]" class="form-control form-control-sm" value="{{ $dataDetail['entitas'][3]['namaEntitas'] ?? 'NIRWANA ALABARE GARMENT' }}">
                        </div>
                        <div class="col-md-4 form-group">
                            <label>NPWP (15/16 Digit)</label>
                            <input type="text" name="entitas[3][nomorIdentitas]" class="form-control form-control-sm" value="{{ $dataDetail['entitas'][3]['nomorIdentitas'] ?? '0745406926444000000000' }}">
                        </div>
                        <div class="col-md-4 form-group">
                            <label>NIB</label>
                            <input type="text" name="entitas[3][nibEntitas]" class="form-control form-control-sm" value="{{ $dataDetail['entitas'][3]['nibEntitas'] ?? '0220103231143' }}">
                        </div>
                        <div class="col-md-8 form-group">
                            <label>Alamat</label>
                            <input type="text" name="entitas[3][alamatEntitas]" class="form-control form-control-sm" value="{{ $dataDetail['entitas'][3]['alamatEntitas'] ?? 'JL. RAYA RANCAEKEK MAJALAYA NO. 289 RT. 001 RW. 007' }}">
                        </div>
                        <div class="col-md-4 form-group">
                            <label>No. Izin TPB</label>
                            <input type="text" name="entitas[3][nomorIjinEntitas]" class="form-control form-control-sm" value="{{ $dataDetail['entitas'][3]['nomorIjinEntitas'] ?? '16/MK/WBC.09/2026' }}">
                        </div>
                    </div>

                    <div class="section-title"><i class="fas fa-truck-loading"></i> Entitas Pengirim</div>
                    <div class="row">
                        <div class="col-md-4 form-group">
                            <label>Nama Entitas</label>
                            <input type="text" name="entitas[7][namaEntitas]" class="form-control form-control-sm" value="{{ $dataDetail['entitas'][7]['namaEntitas'] ?? $header->supplier ?? '' }}">
                        </div>
                        <div class="col-md-4 form-group">
                            <label>NPWP</label>
                            <input type="text" name="entitas[7][nomorIdentitas]" class="form-control form-control-sm" value="{{ $dataDetail['entitas'][7]['nomorIdentitas'] ?? $header->npwp_supplier ?? '' }}">
                        </div>
                        <div class="col-md-4 form-group">
                            <label>Status</label>
                            <input type="text" name="entitas[7][kodeStatus]" class="form-control form-control-sm" value="{{ $dataDetail['entitas'][7]['kodeStatus'] ?? '5' }}">
                        </div>
                        <div class="col-md-12 form-group">
                            <label>Alamat</label>
                            <input type="text" name="entitas[7][alamatEntitas]" class="form-control form-control-sm" value="{{ $dataDetail['entitas'][7]['alamatEntitas'] ?? $header->alamat_supplier ?? '' }}">
                        </div>
                    </div>

                    <div class="section-title"><i class="fas fa-user-tag"></i> Entitas Pemilik (Kode: 9)</div>
                    <div class="row">
                        <div class="col-md-4 form-group">
                            <label>Nama Entitas</label>
                            <input type="text" name="entitas[9][namaEntitas]" class="form-control form-control-sm" value="{{ $dataDetail['entitas'][9]['namaEntitas'] ?? '' }}">
                        </div>
                        <div class="col-md-4 form-group">
                            <label>NPWP</label>
                            <input type="text" name="entitas[9][nomorIdentitas]" class="form-control form-control-sm" value="{{ $dataDetail['entitas'][9]['nomorIdentitas'] ?? '' }}">
                        </div>
                        <div class="col-md-4 form-group">
                            <label>Alamat</label>
                            <input type="text" name="entitas[9][alamatEntitas]" class="form-control form-control-sm" value="{{ $dataDetail['entitas'][9]['alamatEntitas'] ?? '' }}">
                        </div>
                    </div>

                </div>

                <div class="tab-pane fade" id="tab-pendukung" role="tabpanel">

                    <div class="row">
                        <div class="col-md-6">
                            <div class="section-title mt-0">Pengangkut</div>
                            <div class="row">
                                <div class="col-md-6 form-group">
                                    <label>Nama Pengangkut</label>
                                    <input type="text" name="pengangkut[nama]" class="form-control form-control-sm" value="{{ $dataDetail['pengangkut']['nama'] ?? 'TRUK' }}">
                                </div>
                                <div class="col-md-6 form-group">
                                    <label>Nomor Polisi / Pengangkut</label>
                                    <input type="text" name="pengangkut[nomor]" class="form-control form-control-sm" value="{{ $dataDetail['pengangkut']['nomor'] ?? $header->nomor_mobil ?? '' }}">
                                </div>
                            </div>

                            <div class="section-title">Data Kemasan</div>
                            <div class="row">
                                <div class="col-md-4 form-group">
                                    <label>Jml Kemasan</label>
                                    <input type="number" name="kemasan[jumlah]" class="form-control form-control-sm" value="{{ $dataDetail['kemasan']['jumlah'] ?? $header->qty_karton ?? 0 }}">
                                </div>
                                <div class="col-md-4 form-group">
                                    <label>Kode Kemasan</label>
                                    <input type="text" name="kemasan[kode]" class="form-control form-control-sm" value="{{ $dataDetail['kemasan']['kode'] ?? 'CT' }}">
                                </div>
                                <div class="col-md-4 form-group">
                                    <label>Merk</label>
                                    <input type="text" name="kemasan[merk]" class="form-control form-control-sm" value="{{ $dataDetail['kemasan']['merk'] ?? '-' }}">
                                </div>
                            </div>

                            <div class="section-title">Pungutan</div>
                            <div class="row">
                                <div class="col-md-4 form-group">
                                    <label>Jenis Pungutan</label>
                                    <input type="text" name="pungutan[jenis]" class="form-control form-control-sm" value="{{ $dataDetail['pungutan']['jenis'] ?? 'PPN' }}">
                                </div>
                                <div class="col-md-8 form-group">
                                    <label>Nilai Pungutan (Rp)</label>
                                    <input type="number" name="pungutan[nilai]" class="form-control form-control-sm" value="{{ $dataDetail['pungutan']['nilai'] ?? 0 }}">
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="section-title mt-0">Dokumen Pendukung</div>
                            <table class="table table-sm table-bordered">
                                <thead class="bg-light text-center">
                                    <tr>
                                        <th>Kode Dok</th>
                                        <th>Nomor Dokumen</th>
                                        <th>Tgl Dokumen</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td><input type="text" name="dok[0][kode]" class="form-control form-control-sm" value="{{ $dataDetail['dok'][0]['kode'] ?? '380' }}"></td>
                                        <td><input type="text" name="dok[0][nomor]" class="form-control form-control-sm" value="{{ $dataDetail['dok'][0]['nomor'] ?? $header->invno ?? '' }}"></td>
                                        <td><input type="date" name="dok[0][tgl]" class="form-control form-control-sm" value="{{ $dataDetail['dok'][0]['tgl'] ?? $header->bpbdate ?? '' }}"></td>
                                    </tr>
                                    <tr>
                                        <td><input type="text" name="dok[1][kode]" class="form-control form-control-sm" value="{{ $dataDetail['dok'][1]['kode'] ?? '217' }}"></td>
                                        <td><input type="text" name="dok[1][nomor]" class="form-control form-control-sm" value="{{ $dataDetail['dok'][1]['nomor'] ?? $header->pono ?? '' }}"></td>
                                        <td><input type="date" name="dok[1][tgl]" class="form-control form-control-sm" value="{{ $dataDetail['dok'][1]['tgl'] ?? $header->bpbdate ?? '' }}"></td>
                                    </tr>
                                    <tr>
                                        <td><input type="text" name="dok[2][kode]" class="form-control form-control-sm" value="{{ $dataDetail['dok'][2]['kode'] ?? '' }}"></td>
                                        <td><input type="text" name="dok[2][nomor]" class="form-control form-control-sm" value="{{ $dataDetail['dok'][2]['nomor'] ?? '' }}"></td>
                                        <td><input type="date" name="dok[2][tgl]" class="form-control form-control-sm" value="{{ $dataDetail['dok'][2]['tgl'] ?? '' }}"></td>
                                    </tr>
                                </tbody>
                            </table>
                            {{-- <small class="text-muted">* 380 = Invoice | 217 = Packing List | 640 = Dok. Internal</small> --}}
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
<script>
    $(document).ready(function() {
        $('.select2bs4').select2({ theme: 'bootstrap4', width: '100%' });


        $('#ceisaTab a').on('click', function (e) {
            e.preventDefault();
            $(this).tab('show');
        });


        $('#form-edit-ceisa').on('submit', function(e) {
            e.preventDefault();

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
                    Swal.fire({
                        title: 'Menyimpan...',
                        allowOutsideClick: false,
                        didOpen: () => { Swal.showLoading(); }
                    });
                    this.submit();
                }
            });
        });
    });
</script>
@endsection
