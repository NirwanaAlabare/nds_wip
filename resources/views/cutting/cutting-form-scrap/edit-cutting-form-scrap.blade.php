@extends('layouts.index')

@section('content')
    <div class="d-flex justify-content-between mb-3">
        <h5 class="fw-bold text-sb"><i class="fa fa-edit fa-sm"></i> Edit Form Cut Scrap - {{ $formCutScrap->no_form }}</h5>
        <a href="{{ route('cutting-scrap') }}" class="btn btn-primary btn-sm px-1 py-1"><i class="fas fa-reply"></i> Kembali ke Form Cut Scrap</a>
    </div>

    <form action="{{ route('update-cutting-scrap') }}" method="post" id="update-cutting-scrap" onsubmit="submitForm(this, event)">
        @method('PUT')
        <input type="hidden" name="id" value="{{ $formCutScrap->id }}">

        <div class="card card-sb mb-3">
            <div class="card-header">
                <h5 class="card-title fw-bold">Data Form</h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-6 col-md-4">
                        <label class="form-label fw-bold"><small>Tanggal</small></label>
                        <input type="date" class="form-control" name="tanggal" value="{{ $formCutScrap->tanggal }}" required readonly>
                    </div>
                    <div class="col-6 col-md-4">
                        <label class="form-label fw-bold"><small>No</small>. WS</label>
                        <input type="text" class="form-control" value="{{ $formCutScrap->act_costing_ws }}" readonly>
                    </div>
                    <div class="col-6 col-md-4">
                        <label class="form-label fw-bold"><small>Style</small></label>
                        <input type="text" class="form-control" value="{{ $formCutScrap->style }}" readonly>
                    </div>
                    <div class="col-6 col-md-4">
                        <label class="form-label fw-bold"><small>Color</small></label>
                        <input type="text" class="form-control" value="{{ $formCutScrap->color }}" readonly>
                    </div>
                    <div class="col-6 col-md-4">
                        <label class="form-label fw-bold"><small>Panel</small></label>
                        <input type="text" class="form-control" value="{{ $formCutScrap->panel }}" readonly>
                    </div>
                    <div class="col-6 col-md-4">
                        <label class="form-label fw-bold"><small>Status</small></label>
                        <select class="form-select" name="status">
                            @foreach (['PROSES', 'SELESAI', 'BATAL'] as $status)
                                <option value="{{ $status }}" {{ $formCutScrap->status == $status ? 'selected' : '' }}>{{ $status }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-6 col-md-3">
                        <label class="form-label">Waktu Selesai</label>
                        <input type="datetime-local" class="form-control" name="waktu_selesai" value="{{ $formCutScrap->waktu_selesai ? date('Y-m-d\TH:i', strtotime($formCutScrap->waktu_selesai)) : '' }}">
                    </div>
                    <div class="col-6 col-md-3">
                        <label class="form-label">Operator</label>
                        <input type="text" class="form-control" name="operator" value="{{ $formCutScrap->operator }}">
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label">Keterangan</label>
                        <textarea class="form-control" name="ket" rows="1">{{ $formCutScrap->ket }}</textarea>
                    </div>
                </div>
            </div>
        </div>

        <div class="card card-sb mb-3">
            <div class="card-header">
                <h5 class="card-title fw-bold">Detail Roll</h5>
            </div>
            <div class="card-body">
                @forelse ($formCutScrap->formCutScrapDetails as $detail)
                    <div class="border rounded p-2 mb-2">
                        <b>{{ $detail->id_roll ?: '-' }}</b>
                        <small class="text-muted">
                            {{ $detail->itemdesc ?: '-' }} | {{ num($detail->qty_roll, 2) }} {{ $detail->unit }} | Lot {{ $detail->lot ?: '-' }} | Group {{ $detail->group_roll ?: '-' }}
                        </small>
                        <ul class="mb-0 mt-1">
                            @foreach ($detail->formCutScrapParts as $part)
                                <li>
                                    {{ $part->partDetail && $part->partDetail->masterPart ? $part->partDetail->masterPart->nama_part : 'Part #'.$part->part_detail_id }}
                                    @if ($part->ket)
                                        <small class="text-muted">({{ $part->ket }})</small>
                                    @endif
                                    <small>
                                        : {{ $part->formCutScrapSizes->map(fn ($size) => $size->size.' = '.$size->qty)->implode(', ') ?: '-' }}
                                    </small>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @empty
                    <small class="text-muted">Belum ada detail roll.</small>
                @endforelse
            </div>
        </div>

        <div class="d-flex justify-content-between mb-4">
            <a href="{{ route('cutting-scrap') }}" class="btn btn-danger fw-bold"><i class="fa fa-times"></i> BATAL</a>
            <button type="submit" class="btn btn-success fw-bold"><i class="fa fa-save"></i> SIMPAN</button>
        </div>
    </form>
@endsection
