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
                        <label class="form-label fw-bold">No. WS</label>
                        <input type="text" class="form-control" value="{{ $formCutScrap->act_costing_ws }}" readonly>
                    </div>
                    <div class="col-6 col-md-4">
                        <label class="form-label fw-bold">Style</label>
                        <input type="text" class="form-control" value="{{ $formCutScrap->style }}" readonly>
                    </div>
                    <div class="col-6 col-md-4">
                        <label class="form-label fw-bold">Color</label>
                        <input type="text" class="form-control" value="{{ $formCutScrap->color }}" readonly>
                    </div>
                    <div class="col-6 col-md-4">
                        <label class="form-label fw-bold">Panel</label>
                        <input type="text" class="form-control" value="{{ $formCutScrap->panel }}" readonly>
                    </div>
                    <div class="col-6 col-md-4">
                        <label class="form-label fw-bold">Part</label>
                        <input type="text" class="form-control" readonly value="{{ $formCutScrap->formCutScrapFormParts->pluck('nama_part')->implode(', ') ?: '-' }}">
                    </div>
                    <div class="col-6 col-md-4">
                        <label class="form-label fw-bold">Tanggal</label>
                        <input type="date" class="form-control" name="tanggal" value="{{ $formCutScrap->tanggal }}" required readonly>
                    </div>
                    <div class="col-6 col-md-3">
                        <label class="form-label">Waktu Mulai</label>
                        <input type="datetime" class="form-control" name="waktu_mulai" value="{{ $formCutScrap->waktu_mulai ? $formCutScrap->waktu_mulai : '' }}">
                    </div>
                    <div class="col-6 col-md-3">
                        <label class="form-label">Waktu Selesai</label>
                        <input type="datetime" class="form-control" name="waktu_selesai" value="{{ $formCutScrap->waktu_selesai ? $formCutScrap->waktu_selesai : '' }}">
                    </div>
                    <div class="col-6 col-md-6">
                        <label class="form-label">Status</label>
                        <select class="form-control" name="status" required>
                            <option value="complete" {{ $formCutScrap->status == 'complete' ? 'selected' : '' }}>Selesai</option>
                            <option value="incomplete" {{ $formCutScrap->status == 'incomplete' ? 'selected' : '' }}>Belum Selesai</option>
                        </select>
                    </div>
                    <div class="col-12 col-md-12">
                        <label class="form-label">Keterangan</label>
                        <textarea class="form-control" name="ket" rows="1">{{ $formCutScrap->ket }}</textarea>
                    </div>
                </div>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header bg-sb-secondary">
                <h5 class="card-title fw-bold">Detail Roll</h5>
            </div>
            <div class="card-body">
                @forelse ($formCutScrap->formCutScrapDetails as $detail)
                    <div class="border rounded p-3 mb-3">
                        <div class="d-flex flex-column flex-md-row justify-content-between gap-1 mb-3">
                            <div>
                                <div class="text-sb-secondary fw-bold fs-6">ITEM  {{ $detail->id_item ?: '-' }}</div>
                                <small class="text-muted">{{ $detail->itemdesc ?: '-' }}</small>
                            </div>
                            <small class="text-muted align-self-md-center">{{ $detail->formCutScrapParts->count() }} part</small>
                        </div>

                        <div class="row g-2 mb-3">
                            <div class="col-6 col-md-3">
                                <label class="form-label mb-1">Qty Roll</label>
                                <input type="number" class="form-control form-control-sm" name="details[{{ $detail->id }}][qty_roll]" value="{{ $detail->qty_roll }}" min="0.01" step="0.01" required>
                            </div>
                            <div class="col-6 col-md-2">
                                <label class="form-label mb-1">Unit Roll</label>
                                <input type="text" class="form-control form-control-sm" value="{{ $detail->unit }}" readonly>
                            </div>
                            <div class="col-6 col-md-3">
                                <label class="form-label mb-1">Lot</label>
                                <input type="text" class="form-control form-control-sm" name="details[{{ $detail->id }}][lot]" value="{{ $detail->lot }}">
                            </div>
                            <div class="col-6 col-md-4">
                                <label class="form-label mb-1">Group</label>
                                <input type="text" class="form-control form-control-sm" name="details[{{ $detail->id }}][group_roll]" value="{{ $detail->group_roll }}">
                            </div>
                        </div>

                        @forelse ($detail->formCutScrapParts as $part)
                            <div class="border-top pt-3 mt-2">
                                <div class="row g-2 align-items-end">
                                    <div class="col-12 col-md-4">
                                        <div class="text-sb-secondary fw-bold mb-1 fs-6">
                                            {{ $part->partDetail && $part->partDetail->masterPart ? $part->partDetail->masterPart->nama_part : 'Part #'.$part->part_detail_id }}
                                        </div>
                                        <label class="form-label mb-1">Keterangan Part</label>
                                        <input type="text" class="form-control form-control-sm" name="details[{{ $detail->id }}][parts][{{ $part->id }}][ket]" value="{{ $part->ket }}">
                                    </div>
                                    @forelse ($part->formCutScrapSizes as $size)
                                        <div class="col-6 col-md-2">
                                            <label class="form-label mb-1">Size {{ $size->size }}</label>
                                            <input type="number" class="form-control form-control-sm" name="details[{{ $detail->id }}][parts][{{ $part->id }}][sizes][{{ $size->id }}][qty]" value="{{ $size->qty }}" min="0" step="any" required>
                                        </div>
                                    @empty
                                        <div class="col-12 col-md-8">
                                            <small class="text-muted">Belum ada data size.</small>
                                        </div>
                                    @endforelse
                                </div>
                            </div>
                        @empty
                            <small class="text-muted">Belum ada part pada roll ini.</small>
                        @endforelse
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
