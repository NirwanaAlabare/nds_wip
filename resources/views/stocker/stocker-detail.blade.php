@extends('layouts.index')

@section('custom-link')
    <!-- DataTables -->
    <link rel="stylesheet" href="{{ asset('plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/datatables-responsive/css/responsive.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/datatables-buttons/css/buttons.bootstrap4.min.css') }}">

    <!-- Select2 -->
    <link rel="stylesheet" href="{{ asset('plugins/select2/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}">
@endsection

@section('content')
    <div class="card card-sb card-outline">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="card-title fw-bold mb-0"><i class="fas fa-receipt fa-sm"></i> Detail Stocker</h5>
                <div>
                    <button type="button" class="btn btn-dark btn-sm d-none" onclick="countStockerUpdate()">
                        <i class="fa fa-sync"></i> Update No. Stocker
                    </button>
                    <a href="{{ route('stocker') }}" class="btn btn-primary btn-sm">
                        <i class="fa fa-reply"></i> Kembali ke Stocker
                    </a>
                </div>
            </div>
        </div>
        <div class="card-body">
            <button class="btn btn-sm btn-dark mb-3" onclick="rearrangeGroup('{{ $dataSpreading->no_form }}')">Rearrange Group</button>
            <form action="#" method="post" id="stocker-form">
                <div class="row mb-3">
                    <div class="col-6 col-md-3">
                        <div class="mb-1">
                            <label class="form-label"><small>WS Number</small></label>
                            <input type="text" class="form-control form-control-sm" id="no_ws" name="no_ws" value="{{ $dataSpreading->ws }}" readonly>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="mb-1">
                            <label class="form-label"><small>Buyer</small></label>
                            <input type="text" class="form-control form-control-sm" id="buyer" name="buyer" value="{{ $dataSpreading->buyer }}" readonly>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="mb-1">
                            <label class="form-label"><small>Style</small></label>
                            <input type="text" class="form-control form-control-sm" id="style" name="style" value="{{ $dataSpreading->style }}" readonly>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="mb-1">
                            <label class="form-label"><small>Color</small></label>
                            <input type="text" class="form-control form-control-sm" id="color" name="color" value="{{ $dataSpreading->color }}" readonly>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="mb-1">
                            <label class="form-label"><small>Size</small></label>
                            <input type="text" class="form-control form-control-sm" id="size" name="size" value="{{ $dataSpreading->sizes }}" readonly>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="mb-1">
                            <label class="form-label"><small>Panel</small></label>
                            <input type="text" class="form-control form-control-sm" id="panel" name="panel" value="{{ $dataSpreading->panel }}" readonly>
                        </div>
                    </div>
                    <div class="col-6 col-md-6">
                        <div class="row">
                            <div class="col-6 col-md-6">
                                <div class="mb-1">
                                    <label class="form-label"><small>Part</small></label>
                                    <input type="text" class="form-control form-control-sm" id="part" name="part" value="{{ $dataSpreading->part }}" readonly>
                                </div>
                            </div>
                            <div class="col-6 col-md-6">
                                <div class="mb-1">
                                    <label class="form-label"><small>Shade</small></label>
                                    <input type="text" class="form-control form-control-sm" id="shade" name="shade" value="{{ $dataSpreading->shell }}">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-md-6">
                        <div class="row">
                            <div class="col-4 col-md-4">
                                <div class="mb-1">
                                    <label class="form-label"><small>Form Cut</small></label>
                                    <input type="hidden" id="form_cut_id" name="form_cut_id" value="{{ $dataSpreading->form_cut_id }}">
                                    <input type="text" class="form-control form-control-sm" id="no_form_cut" name="no_form_cut" value="{{ $dataSpreading->no_form }}" readonly>
                                </div>
                            </div>
                            <div class="col-4 col-md-4">
                                <div class="mb-1">
                                    <label class="form-label"><small>Total Lembar</small></label>
                                    <input type="text" class="form-control form-control-sm" id="qty_ply_total" name="qty_ply_total" value="{{ $dataSpreading->total_lembar }}" readonly>
                                </div>
                            </div>
                            <div class="col-4 col-md-4">
                                <div class="mb-1">
                                    <label class="form-label"><small>No. Cut</small></label>
                                    <input type="text" class="form-control form-control-sm" id="no_cut" name="no_cut" value="{{ $dataSpreading->no_cut }}" readonly>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="mb-1">
                            <label class="form-label"><small>Tanggal Cutting</small></label>
                            <input type="date" class="form-control form-control-sm" id="tgl_form_cut" name="tgl_form_cut" value="{{ $dataSpreading->tgl_form_cut }}" readonly>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="mb-1">
                            <label class="form-label"><small>Note</small></label>
                            <textarea class="form-control form-control-sm" id="note" name="note" rows="2"></textarea>
                        </div>
                    </div>
                </div>
                <div class="mb-5">
                    <h5 class="fw-bold mb-3 ps-1">Print Stocker</h5>
                    <div class="card">
                        <div class="card-body">
                            @php
                                $index = 0;

                                $currentGroup = "";
                                $currentGroupStocker = 0;
                                $currentTotal = 0;

                            @endphp
                            @foreach ($dataSpreading->formCutInputDetails as $detail)
                                @if (!$detail->group_stocker)
                                    @if ($loop->first)
                                        @php
                                            $currentGroup = $detail->group_roll;
                                            $currentGroupStocker = $detail->group_stocker;
                                        @endphp
                                    @endif

                                    @if ($detail->group_roll != $currentGroup)
                                        <div class="d-flex gap-3">
                                            <div class="mb-3">
                                                <label><small>Group</small></label>
                                                <input type="text" class="form-control form-control-sm" value="{{ $currentGroup }}" readonly>
                                            </div>
                                            <div class="mb-3">
                                                <label><small>Qty</small></label>
                                                <input type="text" class="form-control form-control-sm" value="{{ $currentTotal }}" readonly>
                                            </div>
                                        </div>

                                        @include('stocker.stocker-detail-part',['dataPartDetail' => $dataPartDetail, 'group' => $currentGroup, 'groupStocker' => $currentGroupStocker, 'qtyPly' => $currentTotal, 'index' => $index])
                                        @php
                                            $index += $dataRatio->count() * $dataPartDetail->count();
                                        @endphp

                                        @php
                                            $currentGroup = $detail->group_roll;
                                            $currentGroupStocker = $detail->group_stocker;
                                            $currentTotal = $detail->lembar_gelaran;
                                        @endphp

                                        @if ($loop->last)
                                            <div class="d-flex gap-3">
                                                <div class="mb-3">
                                                    <label><small>Group</small></label>
                                                    <input type="text" class="form-control form-control-sm" value="{{ $currentGroup }}" readonly>
                                                </div>
                                                <div class="mb-3">
                                                    <label><small>Qty</small></label>
                                                    <input type="text" class="form-control form-control-sm" value="{{ $currentTotal }}" readonly>
                                                </div>
                                            </div>

                                            @include('stocker.stocker-detail-part',['dataPartDetail' => $dataPartDetail, 'group' => $currentGroup, 'groupStocker' => $currentGroupStocker, 'qtyPly' => $currentTotal, 'index' => $index])
                                            @php
                                                $index += $dataRatio->count() * $dataPartDetail->count();
                                            @endphp
                                        @endif
                                    @else
                                        @php
                                            $currentTotal += $detail->lembar_gelaran;
                                        @endphp

                                        @if ($loop->last)
                                            <div class="d-flex gap-3">
                                                <div class="mb-3">
                                                    <label><small>Group</small></label>
                                                    <input type="text" class="form-control form-control-sm" value="{{ $currentGroup }}" readonly>
                                                </div>
                                                <div class="mb-3">
                                                    <label><small>Qty</small></label>
                                                    <input type="text" class="form-control form-control-sm" value="{{ $currentTotal }}" readonly>
                                                </div>
                                            </div>

                                            @include('stocker.stocker-detail-part',['dataPartDetail' => $dataPartDetail, 'group' => $currentGroup, 'groupStocker' => $currentGroupStocker, 'qtyPly' => $currentTotal, 'index' => $index])
                                            @php
                                                $index += $dataRatio->count() * $dataPartDetail->count();
                                            @endphp
                                        @endif
                                    @endif
                                @else
                                    @if ($loop->first)
                                        @php
                                            $currentGroup = $detail->group_roll;
                                            $currentGroupStocker = $detail->group_stocker;
                                        @endphp
                                    @endif

                                    @if ($detail->group_stocker != $currentGroupStocker)
                                        <div class="d-flex gap-3">
                                            <div class="mb-3">
                                                <label><small>Group</small></label>
                                                <input type="text" class="form-control form-control-sm" value="{{ $currentGroup }}" readonly>
                                            </div>
                                            <div class="mb-3">
                                                <label><small>Qty</small></label>
                                                <input type="text" class="form-control form-control-sm" value="{{ $currentTotal }}" readonly>
                                            </div>
                                        </div>

                                        @include('stocker.stocker-detail-part',['dataPartDetail' => $dataPartDetail, 'group' => $currentGroup, 'groupStocker' => $currentGroupStocker, 'qtyPly' => $currentTotal, 'index' => $index])
                                        @php
                                            $index += $dataRatio->count() * $dataPartDetail->count();
                                        @endphp

                                        @php
                                            $currentGroup = $detail->group_roll;
                                            $currentGroupStocker = $detail->group_stocker;
                                            $currentTotal = $detail->lembar_gelaran;
                                        @endphp

                                        @if ($loop->last)
                                            <div class="d-flex gap-3">
                                                <div class="mb-3">
                                                    <label><small>Group</small></label>
                                                    <input type="text" class="form-control form-control-sm" value="{{ $currentGroup }}" readonly>
                                                </div>
                                                <div class="mb-3">
                                                    <label><small>Qty</small></label>
                                                    <input type="text" class="form-control form-control-sm" value="{{ $currentTotal }}" readonly>
                                                </div>
                                            </div>

                                            @include('stocker.stocker-detail-part',['dataPartDetail' => $dataPartDetail, 'group' => $currentGroup, 'groupStocker' => $currentGroupStocker, 'qtyPly' => $currentTotal, 'index' => $index])
                                            @php
                                                $index += $dataRatio->count() * $dataPartDetail->count();
                                            @endphp
                                        @endif
                                    @else
                                        @php
                                            $currentTotal += $detail->lembar_gelaran;
                                        @endphp

                                        @if ($loop->last)
                                            <div class="d-flex gap-3">
                                                <div class="mb-3">
                                                    <label><small>Group</small></label>
                                                    <input type="text" class="form-control form-control-sm" value="{{ $currentGroup }}" readonly>
                                                </div>
                                                <div class="mb-3">
                                                    <label><small>Qty</small></label>
                                                    <input type="text" class="form-control form-control-sm" value="{{ $currentTotal }}" readonly>
                                                </div>
                                            </div>

                                            @include('stocker.stocker-detail-part',['dataPartDetail' => $dataPartDetail, 'group' => $currentGroup, 'groupStocker' => $currentGroupStocker, 'qtyPly' => $currentTotal, 'index' => $index])
                                            @php
                                                $index += $dataRatio->count() * $dataPartDetail->count();
                                            @endphp
                                        @endif
                                    @endif
                                @endif
                            @endforeach
                        </div>
                    </div>
                </div>
                <div class="mb-5">
                    <h5 class="fw-bold mb-3 ps-1">Print Numbering</h5>
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm" id="table-ratio-numbering">
                            <thead>
                                <th>Size</th>
                                <th>Ratio</th>
                                <th>Qty Cut</th>
                                <th>Range Awal</th>
                                <th>Range Akhir</th>
                                <th>Generated</th>
                                <th>Print Numbering</th>
                            </thead>
                            <tbody>
                                @foreach ($dataRatio as $ratio)
                                    @php
                                        $qty = intval($ratio->ratio) * intval($dataSpreading->total_lembar);

                                        $numberingThis = $dataNumbering ? $dataNumbering->where("so_det_id", $ratio->so_det_id)->where("no_cut", $dataSpreading->no_cut)->where("ratio", ">", "0")->first() : null;
                                        $numberingBefore = $dataNumbering ? $dataNumbering->where("so_det_id", $ratio->so_det_id)->where("no_cut", "<", $dataSpreading->no_cut)->where("ratio", ">", "0")->sortByDesc('no_cut')->first() : null;
                                        $rangeAwal = ($dataSpreading->no_cut > 1 ? ($numberingBefore ? ($numberingBefore->numbering_id != null ? $numberingBefore->range_akhir + 1 : "-") : "-") : 1);
                                        $rangeAkhir = ($dataSpreading->no_cut > 1 ? ($numberingBefore ? ($numberingBefore->numbering_id != null ? $numberingBefore->range_akhir + $qty : "-") : "-") : $qty);
                                    @endphp
                                    <tr>
                                        <input type="hidden" name="ratio[{{ $index }}]" id="ratio_{{ $index }}" value="{{ $ratio->ratio }}">
                                        <input type="hidden" name="so_det_id[{{ $index }}]" id="so_det_id_{{ $index }}" value="{{ $ratio->so_det_id }}">
                                        <input type="hidden" name="size[{{ $index }}]" id="size_{{ $index }}" value="{{ $ratio->size }}">
                                        <input type="hidden" name="qty_cut[{{ $index }}]" id="qty_cut_{{ $index }}" value="{{ $qty }}">
                                        <input type="hidden" name="range_awal[{{ $index }}]" id="range_awal_{{ $index }}" value="{{ $rangeAwal }}">
                                        <input type="hidden" name="range_akhir[{{ $index }}]" id="range_akhir_{{ $index }}" value="{{ $rangeAkhir }}">

                                        <td>{{ $ratio->size}}</td>
                                        <td>{{ $ratio->ratio }}</td>
                                        <td>{{ $qty }}</td>
                                        <td>{{ $rangeAwal }}</td>
                                        <td>{{ $rangeAkhir }}</td>
                                        <td>
                                            @if ($dataSpreading->no_cut > 1)
                                                @if ($numberingBefore)
                                                    @if ($numberingBefore->numbering_id != null)
                                                        @if ($numberingThis->numbering_id != null)
                                                            <i class="fa fa-check"></i>
                                                        @else
                                                            <i class="fa fa-times"></i>
                                                        @endif
                                                    @else
                                                        <i class="fa fa-minus"></i>
                                                    @endif
                                                @else
                                                    <i class="fa fa-minus"></i>
                                                @endif
                                            @else
                                                @if ($numberingThis->numbering_id != null)
                                                    <i class="fa fa-check"></i>
                                                @else
                                                    <i class="fa fa-times"></i>
                                                @endif
                                            @endif
                                        <td>
                                            <button type="button" class="btn btn-sm btn-danger" onclick="printNumbering({{ $index }});" {{ ($dataSpreading->no_cut > 1 ? ($numberingBefore ? ($numberingBefore->numbering_id != null ? "" : "disabled") : "") : "") }}>
                                                <i class="fa fa-print fa-s"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    @php
                                        $index++;
                                    @endphp
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('custom-script')
    <!-- DataTables  & Plugins -->
    <script src="{{ asset('plugins/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-responsive/js/dataTables.responsive.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-responsive/js/responsive.bootstrap4.min.js') }}"></script>

    <!-- Select2 -->
    <script src="{{ asset('plugins/select2/js/select2.full.min.js') }}"></script>
    <script>
        $('.select2').select2()
        $('.select2bs4').select2({
            theme: 'bootstrap4',
            dropdownParent: $("#editMejaModal")
        })

        $("#datatable").DataTable({
            ordering: false,
            paging: false,
        });

        function rearrangeGroup(noForm) {
            $.ajax({
                url: '{{ route('rearrange-group') }}',
                type: 'post',
                data: {
                    no_form : noForm
                },
                success: function(res) {
                    console.log("successs", res);

                    location.reload();
                },
                error: function(jqXHR) {
                    console.log("error", jqXHR);
                }
            });
        }

        function printStocker(index) {
            let stockerForm = new FormData(document.getElementById("stocker-form"));

            let no_ws = document.getElementById("no_ws").value;
            let style = document.getElementById("style").value;
            let color = document.getElementById("color").value;
            let panel = document.getElementById("panel").value;
            let no_form_cut = document.getElementById("no_form_cut").value;
            let current_size = document.getElementById("size_"+index).value;

            let fileName = [
                no_ws,
                style,
                color,
                panel,
                no_form_cut,
                current_size,
            ].join('-');

            Swal.fire({
                title: 'Please Wait...',
                html: 'Exporting Data...',
                didOpen: () => {
                    Swal.showLoading()
                },
                allowOutsideClick: false,
            });

            $.ajax({
                url: '{{ route('print-stocker') }}/'+index,
                type: 'post',
                processData: false,
                contentType: false,
                data: stockerForm,
                xhrFields:
                {
                    responseType: 'blob'
                },
                success: function(res) {
                    if (res) {
                        console.log(res);

                        var blob = new Blob([res], {type: 'application/pdf'});
                        var link = document.createElement('a');
                        link.href = window.URL.createObjectURL(blob);
                        link.download = fileName+".pdf";
                        link.click();

                        swal.close();

                        window.location.reload();
                    }
                }
            });
        }

        function printStockerAllSize(part) {
            let stockerForm = new FormData(document.getElementById("stocker-form"));

            let no_ws = document.getElementById("no_ws").value;
            let style = document.getElementById("style").value;
            let color = document.getElementById("color").value;
            let panel = document.getElementById("panel").value;
            let no_form_cut = document.getElementById("no_form_cut").value;

            let fileName = [
                no_ws,
                style,
                color,
                panel,
                part,
                no_form_cut
            ].join('-');

            console.log(fileName);

            Swal.fire({
                title: 'Please Wait...',
                html: 'Exporting Data...',
                didOpen: () => {
                    Swal.showLoading()
                },
                allowOutsideClick: false,
            });

            $.ajax({
                url: '{{ route('print-stocker-all-size') }}/'+part,
                type: 'post',
                processData: false,
                contentType: false,
                data: stockerForm,
                xhrFields:
                {
                    responseType: 'blob'
                },
                success: function(res) {
                    if (res) {
                        console.log(res);

                        var blob = new Blob([res], {type: 'application/pdf'});
                        var link = document.createElement('a');
                        link.href = window.URL.createObjectURL(blob);
                        link.download = fileName+".pdf";
                        link.click();

                        swal.close();

                        window.location.reload();
                    }
                }
            });
        }

        function printNumbering(index) {
            let stockerForm = new FormData(document.getElementById("stocker-form"));

            let no_ws = document.getElementById("no_ws").value;
            let style = document.getElementById("style").value;
            let color = document.getElementById("color").value;
            let panel = document.getElementById("panel").value;
            let no_form_cut = document.getElementById("no_form_cut").value;
            let current_size = document.getElementById("size_"+index).value;

            let fileName = [
                no_ws,
                style,
                color,
                panel,
                no_form_cut,
                current_size,
            ].join('-');

            Swal.fire({
                title: 'Please Wait...',
                html: 'Exporting Data...',
                didOpen: () => {
                    Swal.showLoading()
                },
                allowOutsideClick: false,
            });

            $.ajax({
                url: '{{ route('print-numbering') }}/'+index,
                type: 'post',
                processData: false,
                contentType: false,
                data: stockerForm,
                xhrFields:
                {
                    responseType: 'blob'
                },
                success: function(res) {
                    if (res) {
                        console.log(res);

                        var blob = new Blob([res], {type: 'application/pdf'});
                        var link = document.createElement('a');
                        link.href = window.URL.createObjectURL(blob);
                        link.download = fileName+".pdf";
                        link.click();
                    }

                    window.location.reload();
                }
            });
        }

        function countStockerUpdate() {
            let stockerForm = new FormData(document.getElementById("stocker-form"));

            $.ajax({
                url: '{{ route('count-stocker-update') }}',
                type: 'put',
                data: stockerForm,
                processData: false,
                contentType: false,
                success: function(res) {
                    console.log("successs", res);
                },
                error: function(jqXHR) {
                    console.log("error", jqXHR);
                }
            });
        }
    </script>
@endsection
