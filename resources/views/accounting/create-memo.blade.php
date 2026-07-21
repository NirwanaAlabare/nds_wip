@extends('layouts.index')

@section('content')
<form action="{{ route('accounting-memo-store') }}" method="post" id="form-memo" onsubmit="submitForm(this, event)">
    @csrf

    {{-- Header Card --}}
    <div class="card card-sb">
        <div class="card-header">
            <h5 class="card-title fw-bold">Memo Permintaan Pembayaran</h5>
            <div class="card-tools">
                <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i></button>
            </div>
        </div>
        <div class="card-body">
            <div class="form-group row">

                <div class="col-md-4">
                    <div class="mb-2">
                        <label><small>Nomor Memo</small></label>
                        <input type="text" class="form-control" value="{{ $nomor }}" readonly>
                    </div>
                    <div class="mb-2">
                        <label><small>Kepada <span class="text-danger">*</span></small></label>
                        <input type="text" class="form-control" name="kepada" placeholder="Finance Department" required>
                    </div>
                    <div class="mb-2">
                        <label><small>Perihal <span class="text-danger">*</span></small></label>
                        <input type="text" class="form-control" name="perihal" placeholder="Pembayaran Komisi" required>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="mb-2">
                        <label><small>Kepada (Penerima Pembayaran) <span class="text-danger">*</span></small></label>
                        <input type="text" class="form-control" name="kepada_detail" placeholder="Nama perusahaan/individu" required>
                    </div>
                    <div class="mb-2">
                        <label><small>Jumlah <span class="text-danger">*</span></small></label>
                        <div class="input-group">
                            <select class="form-control" name="mata_uang" style="max-width:90px;">
                                <option value="IDR">IDR</option>
                                <option value="USD">USD</option>
                                <option value="EUR">EUR</option>
                                <option value="SGD">SGD</option>
                                <option value="CNY">CNY</option>
                            </select>
                            <input type="text" class="form-control text-right" name="jumlah" placeholder="0.00" inputmode="decimal" autocomplete="off" required>
                        </div>
                    </div>
                    <div class="mb-2">
                        <label><small>Peruntukan</small></label>
                        <textarea class="form-control" name="peruntukan" rows="2" placeholder="Keterangan penggunaan dana"></textarea>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="mb-2">
                        <label><small>Tanggal Pembayaran</small></label>
                        <input type="date" class="form-control" name="tgl_pembayaran">
                    </div>
                    <div class="mb-2">
                        <label><small>Bank & No Rekening</small></label>
                        <input type="text" class="form-control" name="bank_rekening" placeholder="BCA - 1234567890">
                    </div>
                    <div class="mb-2">
                        <label><small>Nama Penerima</small></label>
                        <input type="text" class="form-control" name="nama_penerima" placeholder="Nama pemegang rekening">
                    </div>
                    <div class="mb-2">
                        <label><small>Lampiran</small></label>
                        <input type="text" class="form-control" name="lampiran" placeholder="Invoice, DGT Form, COR">
                    </div>
                </div>

            </div>
        </div>
    </div>

    {{-- Signature Card --}}
    <div class="card card-sb">
        <div class="card-header">
            <h5 class="card-title fw-bold">Tanda Tangan</h5>
        </div>
        <div class="card-body">
            <div class="form-group row">
                <div class="col-md-4">
                    <h6 class="fw-bold text-muted mb-2">Diajukan Oleh</h6>
                    <div class="mb-2">
                        <label><small>Nama</small></label>
                        <input type="text" class="form-control" name="diajukan_nama" placeholder="Nama">
                    </div>
                    <div class="mb-2">
                        <label><small>Jabatan</small></label>
                        <input type="text" class="form-control" name="diajukan_jabatan" placeholder="Jabatan">
                    </div>
                </div>
                <div class="col-md-4">
                    <h6 class="fw-bold text-muted mb-2">Disetujui Oleh</h6>
                    <div class="mb-2">
                        <label><small>Nama</small></label>
                        <input type="text" class="form-control" name="disetujui_nama" placeholder="Nama">
                    </div>
                    <div class="mb-2">
                        <label><small>Jabatan</small></label>
                        <input type="text" class="form-control" name="disetujui_jabatan" placeholder="Jabatan">
                    </div>
                </div>
                <div class="col-md-4">
                    <h6 class="fw-bold text-muted mb-2">Mengetahui</h6>
                    <div class="mb-2">
                        <label><small>Nama</small></label>
                        <input type="text" class="form-control" name="mengetahui_nama" placeholder="Nama">
                    </div>
                    <div class="mb-2">
                        <label><small>Jabatan</small></label>
                        <input type="text" class="form-control" name="mengetahui_jabatan" placeholder="Jabatan">
                    </div>
                </div>
            </div>

            <div class="mt-2">
                <a href="{{ route('accounting-memo') }}" class="btn btn-danger float-end"><i class="fas fa-arrow-circle-left"></i> Kembali</a>
                <button class="btn btn-sb float-end me-2"><i class="fa-solid fa-floppy-disk"></i> Simpan</button>
            </div>
        </div>
    </div>
</form>
@endsection
