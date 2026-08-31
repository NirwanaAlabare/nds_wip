{{--
    Kartu satu group roll pada halaman switching.

    Variabel : $groupRoll, $formDetailOutputs, $isAdditional (opsional)
    $isAdditional = true untuk group yang berasal dari output kiriman form lain
    (group roll-nya tidak ada di spreading form ini).
--}}
<!-- Marker Details Table Card -->
<div class="row mt-4">
    <div class="col-md-12">
        <div class="card card-sb shadow-sm">
            <div class="card-header">
                <h5 class="card-title">
                    Group{{ ($isAdditional ?? false) ? ' (Tambahan)' : '' }} : <span class="text-warning">{{ $groupRoll }}</span>
                </h5>
            </div>
            <div class="card-body">
                @if($formDetailOutputs && count($formDetailOutputs) > 0)
                    <div class="table-responsive">
                        <table class="table-bordered marker-details-table">
                            <thead>
                                <tr>
                                    <th class="text-center">Size</th>
                                    <th class="text-center">Cut Qty</th>
                                    <th class="text-center">Selection</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($formDetailOutputs as $index => $detail)
                                    @php
                                        $detailMarkerDetail = $detail->markerDetail;
                                    @endphp
                                    <tr>
                                        <td class="text-center text-sb text-md align-middle fw-bold">{{ $detailMarkerDetail->size ?? '-' }}</td>
                                        <td class="text-center text-lg align-middle fw-bold">{{ $detail->qty_output_aktual ?? '-' }} <span class="text-muted text-xs">PCS</span></td>
                                        <td>
                                            <div class="d-flex gap-1">
                                                <div class="mt-1 mx-3">
                                                    <div class="form-check form-switch">
                                                        <input class="form-check-input switch-internal" type="checkbox" role="switch" autocomplete="off" data-act-costing-id='{{ $formMarker->act_costing_id ?? '' }}' data-form-id='{{ $form->id ?? '' }}' data-marker-detail-id='{{ $detailMarkerDetail->id ?? '' }}' data-group-roll='{{ $groupRoll }}' id="switch-{{ $detail->id }}" data-id='{{ $detail->id }}' onchange="setInternal(this)">
                                                        <label class="form-check-label" for="switch-{{ $detail->id }}" data-id='{{ $detail->id }}'> INTERNAL</label>
                                                    </div>
                                                </div>
                                                <div class="mb-2" style="flex: 1;">
                                                    <select class="form-control form-control-sm select2 select-ws" placeholder="Select WS" id="ws-{{ $detail->id }}" data-id='{{ $detail->id }}' onchange="getFormList(this)">
                                                        <option value="">-- Pilih WS --</option>
                                                        @if($orders)
                                                            @foreach ($orders as $order)
                                                                <option value="{{ $order->id_act_cost }}">{{ $order->ws }}</option>
                                                            @endforeach
                                                        @endif
                                                    </select>
                                                </div>
                                                <div class="mb-2" style="flex: 1;">
                                                    <select class="form-control form-control-sm select2 select-form" placeholder="Select No. Form" id="form-{{ $detail->id }}" data-id='{{ $detail->id }}' onchange="getFormSizeList(this)">
                                                        <option value="">-- Pilih No. Form --</option>
                                                    </select>
                                                </div>
                                                <div class="mb-0" style="flex: 1;">
                                                    <select class="form-control form-control-sm select2 select-size" placeholder="Select Size" id="size-{{ $detail->id }}" data-id='{{ $detail->id }}'>
                                                        <option value="">-- Pilih Size --</option>
                                                    </select>
                                                </div>
                                                <div class="mb-0" style="flex: 1;">
                                                    <input type="number" class="form-control form-control-sm qty" id="qty-{{ $detail->id }}" data-id='{{ $detail->id }}' placeholder='Qty Kirim'>
                                                </div>
                                                <div class="mb-0" style="flex: 1;">
                                                    <button type="button" class="btn btn-success btn-sm" data-id="{{ $detail->id }}" data-marker-detail-id="{{ $detailMarkerDetail->id ?? '' }}" data-group-roll="{{ $groupRoll }}" onclick="sendOutput(this)">
                                                        <i class="fa-solid fa-arrow-right-arrow-left"></i>
                                                        Kirim Output
                                                    </button>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="alert alert-info mb-0">
                        <i class="fas fa-info-circle"></i> Tidak ada data marker details
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
