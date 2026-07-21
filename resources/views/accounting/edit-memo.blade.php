@extends('layouts.index')

@section('content')
<form action="{{ route('accounting-memo-update', $memo->id) }}" method="post" id="form-memo" onsubmit="submitForm(this, event)">
    @csrf
    @method('PUT')

    <div class="card card-sb">
        <div class="card-header">
            <h5 class="card-title fw-bold">Edit Memo - {{ $memo->nomor }}</h5>
            <div class="card-tools">
                <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i></button>
            </div>
        </div>
        <div class="card-body">
            <div class="form-group row">

                <div class="col-md-4">
                    <div class="mb-2">
                        <label><small>Nomor Memo</small></label>
                        <input type="text" class="form-control" value="{{ $memo->nomor }}" readonly>
                    </div>
                    <div class="mb-2">
                        <label><small>Kepada <span class="text-danger">*</span></small></label>
                        <input type="text" class="form-control" name="kepada" value="{{ $memo->kepada }}" required>
                    </div>
                    <div class="mb-2">
                        <label><small>Perihal <span class="text-danger">*</span></small></label>
                        <input type="text" class="form-control" name="perihal" value="{{ $memo->perihal }}" required>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="mb-2">
                        <label><small>Kepada (Penerima Pembayaran) <span class="text-danger">*</span></small></label>
                        <input type="text" class="form-control" name="kepada_detail" value="{{ $memo->kepada_detail }}" required>
                    </div>
                    <div class="mb-2">
                        <label><small>Jumlah <span class="text-danger">*</span></small></label>
                        <div class="input-group">
                            <select class="form-control" name="mata_uang" style="max-width:90px;">
                                @foreach(['IDR','USD','EUR','SGD','CNY'] as $cur)
                                <option value="{{ $cur }}" {{ $memo->mata_uang == $cur ? 'selected' : '' }}>{{ $cur }}</option>
                                @endforeach
                            </select>
                            <input type="text" class="form-control text-right" name="jumlah" value="{{ $memo->jumlah }}" inputmode="decimal" autocomplete="off" required>
                        </div>
                    </div>
                    <div class="mb-2">
                        <label><small>Peruntukan</small></label>
                        <textarea class="form-control" name="peruntukan" rows="2">{{ $memo->peruntukan }}</textarea>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="mb-2">
                        <label><small>Tanggal Pembayaran</small></label>
                        <input type="date" class="form-control" name="tgl_pembayaran" value="{{ $memo->tgl_pembayaran }}">
                    </div>
                    <div class="mb-2">
                        <label><small>Bank & No Rekening</small></label>
                        <input type="text" class="form-control" name="bank_rekening" value="{{ $memo->bank_rekening }}">
                    </div>
                    <div class="mb-2">
                        <label><small>Nama Penerima</small></label>
                        <input type="text" class="form-control" name="nama_penerima" value="{{ $memo->nama_penerima }}">
                    </div>
                    <div class="mb-2">
                        <label><small>Lampiran</small></label>
                        <input type="text" class="form-control" name="lampiran" value="{{ $memo->lampiran }}">
                    </div>
                </div>

            </div>
        </div>
    </div>

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
                        <input type="text" class="form-control" name="diajukan_nama" value="{{ $memo->diajukan_nama }}">
                    </div>
                    <div class="mb-2">
                        <label><small>Jabatan</small></label>
                        <input type="text" class="form-control" name="diajukan_jabatan" value="{{ $memo->diajukan_jabatan }}">
                    </div>
                </div>
                <div class="col-md-4">
                    <h6 class="fw-bold text-muted mb-2">Disetujui Oleh</h6>
                    <div class="mb-2">
                        <label><small>Nama</small></label>
                        <input type="text" class="form-control" name="disetujui_nama" value="{{ $memo->disetujui_nama }}">
                    </div>
                    <div class="mb-2">
                        <label><small>Jabatan</small></label>
                        <input type="text" class="form-control" name="disetujui_jabatan" value="{{ $memo->disetujui_jabatan }}">
                    </div>
                </div>
                <div class="col-md-4">
                    <h6 class="fw-bold text-muted mb-2">Mengetahui</h6>
                    <div class="mb-2">
                        <label><small>Nama</small></label>
                        <input type="text" class="form-control" name="mengetahui_nama" value="{{ $memo->mengetahui_nama }}">
                    </div>
                    <div class="mb-2">
                        <label><small>Jabatan</small></label>
                        <input type="text" class="form-control" name="mengetahui_jabatan" value="{{ $memo->mengetahui_jabatan }}">
                    </div>
                </div>
            </div>

            <div class="mt-2">
                <a href="{{ route('accounting-memo') }}" class="btn btn-danger float-end"><i class="fas fa-arrow-circle-left"></i> Kembali</a>
                <button class="btn btn-sb float-end me-2"><i class="fa-solid fa-floppy-disk"></i> Update</button>
            </div>
        </div>
    </div>
</form>
@endsection
