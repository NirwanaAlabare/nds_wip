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
    <div class="card card-sb">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="card-title fw-bold mb-0"><i class="fas fa-receipt fa-sm"></i> Detail Stocker</h5>
                <div>
                    <a href="{{ route('stocker') }}" class="btn btn-primary btn-sm">
                        <i class="fa fa-reply"></i> Kembali ke Stocker
                    </a>
                </div>
            </div>
        </div>
        <div class="card-body">
            @php
                $dataPartFormPrevious = $dataPartForm->where("no_cut", "<", $dataSpreading->no_cut)->sortByDesc("no_cut")->first();
                $dataPartFormNext = $dataPartForm->where("no_cut", ">", $dataSpreading->no_cut)->sortBy("no_cut")->first();
            @endphp
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    @if ($dataPartFormPrevious)
                        <a href="{{ route('show-stocker-pcs')."/".$dataPartFormPrevious->form_pcs_id }}" class="text-sb"><u><< Prev</u></a>
                    @endif
                </div>
                <div>
                    <select class="form-select form-select-sm" name="gotocut" id="gotocut" onchange="redirectToCut(this)">
                        @foreach ($dataPartForm->sortBy("no_cut") as $dpf)
                            <option value="{{ route("show-stocker-pcs")."/".$dpf->form_pcs_id }}" {{ $dpf->no_cut == $dataSpreading->no_cut ? "selected='true'" : "" }}>{{ $dpf->no_cut }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    @if ($dataPartFormNext)
                        <a href="{{ route('show-stocker-pcs')."/".$dataPartFormNext->form_pcs_id }}" class="text-sb"><u>Next >></u></a>
                    @endif
                </div>
            </div>
            <form action="#" method="post" id="stocker-form">
                <input type="hidden" class="form-control" id="type" name="type" value="PIECE">
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
                            <div class="col-12 col-md-12">
                                <div class="mb-1">
                                    <label class="form-label"><small>Part</small></label>
                                    <input type="text" class="form-control form-control-sm" id="part" name="part" value="{{ $dataSpreading->part }}" readonly>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-md-6">
                        <div class="row">
                            <div class="col-4 col-md-4">
                                <div class="mb-1">
                                    <label class="form-label"><small>Form Cut</small></label>
                                    <input type="hidden" id="form_cut_id" name="form_cut_id" value="{{ $dataSpreading->id }}" readonly>
                                    <input type="text" class="form-control form-control-sm" id="no_form_cut" name="no_form_cut" value="{{ $dataSpreading->no_form }}" readonly>
                                </div>
                            </div>
                            <div class="col-4 col-md-4">
                                <div class="mb-1">
                                    <label class="form-label"><small>Total Lembar</small></label>
                                    <input type="text" class="form-control form-control-sm" id="qty_ply_total" name="qty_ply_total" value="{{ $dataSpreading && $dataSpreading->formCutPieceDetailSizes ? $dataSpreading->formCutPieceDetailSizes->sum('qty') : "-" }}" readonly>
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
                            <label class="form-label"><small>Catatan</small></label>
                            <textarea class="form-control form-control-sm" id="note" name="note" rows="2"></textarea>
                        </div>
                    </div>
                </div>
                <div class="mb-5">
                    <h5 class="fw-bold text-sb mb-3 ps-1">Print Stocker</h5>
                    <div class="card">
                        <div class="card-body">
                            @php
                                $index = 0;
                                $partIndex = 0;

                                $currentGroup = "";
                                $currentGroupStocker = 0;
                                $currentTotal = 0;
                                $currentBefore = collect();
                            @endphp
                            @foreach ($dataSpreading->formCutPieceDetails->where('status', 'complete')->sortByDesc('group_roll')->sortByDesc('group_stocker') as $detail)
                                @if (!$detail->group_stocker)
                                {{-- Without group stocker condition --}}

                                    @if ($loop->first)
                                    {{-- Initial group --}}
                                        @php
                                            $currentGroup = $detail->group_roll;
                                            $currentGroupStocker = $detail->group_stocker;
                                            $currentDetail = $dataDetail->where("group_roll", $currentGroup)->where("group_stocker", $currentGroupStocker);
                                        @endphp
                                    @endif

                                    @if ($detail->group_roll != $currentGroup)
                                        {{-- Create element when switching group --}}
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

                                        @include('stocker.stocker.stocker-piece-detail-part', ["currentDetail" => $currentDetail])
                                        @php
                                            $index += $currentDetail->count() * $dataPartDetail->count();
                                            $partIndex += $dataPartDetail->count();
                                        @endphp

                                        {{-- Change initial group --}}
                                        @php
                                            $currentGroup = $detail->group_roll;
                                            $currentGroupStocker = $detail->group_stocker;
                                            $currentTotal = $detail->formCutPieceDetailSizes->sum("qty");

                                            $currentDetail = $dataDetail->where("group_roll", $currentGroup)->where("group_stocker", $currentGroupStocker);
                                        @endphp

                                        @if ($loop->last)
                                            {{-- Create last element when it comes to an end of this loop --}}
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

                                            @include('stocker.stocker.stocker-piece-detail-part', ["currentDetail" => $currentDetail])
                                            @php
                                                $index += $currentDetail->count() * $dataPartDetail->count();
                                                $partIndex += $dataPartDetail->count();
                                            @endphp
                                        @endif
                                    @else
                                        {{-- Accumulate when it still in the same group --}}
                                        @php
                                            $currentTotal += $detail->formCutPieceDetailSizes->sum("qty");
                                        @endphp

                                        {{-- Create last element when it comes to an end of this loop --}}
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

                                            @include('stocker.stocker.stocker-piece-detail-part', ["currentDetail" => $currentDetail])
                                            @php
                                                $index += $currentDetail->count() * $dataPartDetail->count();
                                                $partIndex += $dataPartDetail->count();
                                            @endphp
                                        @endif
                                    @endif
                                @else
                                {{-- With group stocker condition --}}

                                    @if ($loop->first)
                                    {{-- Initial Group --}}
                                        @php
                                            $currentGroup = $detail->group_roll;
                                            $currentGroupStocker = $detail->group_stocker;
                                        @endphp
                                    @endif

                                    @if ($detail->group_stocker != $currentGroupStocker)
                                        {{-- Create element when switching group --}}
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

                                        @include('stocker.stocker.stocker-piece-detail-part')
                                        @php
                                            $index += $dataDetail->count() * $dataPartDetail->count();
                                            $partIndex += $dataPartDetail->count();
                                        @endphp

                                        {{-- Change initial group --}}
                                        @php
                                            $currentBefore += $currentTotal;

                                            $currentGroup = $detail->group_roll;
                                            $currentGroupStocker = $detail->group_stocker;
                                            $currentTotal = $detail->formCutPieceDetailSizes->sum("qty");
                                        @endphp

                                        {{-- Create last element when it comes to an end of this loop --}}
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

                                            @include('stocker.stocker.stocker-piece-detail-part')
                                            @php
                                                $index += $dataDetail->count() * $dataPartDetail->count();
                                                $partIndex += $dataPartDetail->count();
                                            @endphp
                                        @endif
                                    @else
                                        {{-- Accumulate when it still in the group --}}
                                        @php
                                            $currentTotal += $detail->formCutPieceDetailSizes->sum("qty");
                                        @endphp

                                        @if ($loop->last)
                                        {{-- Create last element when it comes to an end of this loop --}}
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

                                            @include('stocker.stocker.stocker-piece-detail-part')
                                            @php
                                                $index += $dataDetail->count() * $dataPartDetail->count();
                                                $partIndex += $dataPartDetail->count();
                                            @endphp
                                        @endif
                                    @endif
                                @endif
                            @endforeach
                        </div>
                        <div class="d-flex justify-content-end p-3">
                            <button type="button" class="btn btn-danger btn-sm mb-3 w-auto" onclick="generateCheckedStocker()"><i class="fa fa-print"></i> Generate Checked Stocker</button>
                        </div>
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
            dropdownParent: $("#additional-modal")
        })

        $("#datatable").DataTable({
            ordering: false,
            paging: false,
        });

        var generating = false;

        $(document).ready(() => {
            let generatableElements = document.getElementsByClassName('generatable');

            for (let i = 0; i < generatableElements.length; i++) {
                let generatableValue = generatableElements[i].value;
                let generatableIndex = generatableElements[i].getAttribute('data-group');

                let generateStocker = document.getElementsByClassName('generate-'+generatableIndex);

                for (let j = 0; j < generateStocker.length; j++) {
                    if (generatableValue) {
                        generateStocker[j].removeAttribute('disabled');
                    } else {
                        generateStocker[j].setAttribute('disabled', true);
                    }
                }
            }

            // window.onfocus = function() {
            //     console.log(generating);

            //     if (!generating) {
            //         window.location.reload();
            //     }
            // }
        });

        function printStocker(index) {
            generating = true;

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
                current_size
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
                url: '{{ route('print-stocker-pcs') }}/'+index,
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

                    generating = false;
                },
                error: function(jqXHR) {
                    console.log(jqXHR);

                    generating = false;

                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Terjadi kesalahan',
                        confirmButtonText: 'Coba Lagi',
                        showCancelButton: true,
                        cancelButtonText: 'Batalkan',
                    }).then(result => {
                        if (result.isConfirmed) {
                            printStocker(index); // Retry the request
                        }
                    });
                }
            });
        }

        function printStockerAllSize(part) {
            generating = true;

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

            Swal.fire({
                title: 'Please Wait...',
                html: 'Exporting Data...',
                didOpen: () => {
                    Swal.showLoading()
                },
                allowOutsideClick: false,
            });

            $.ajax({
                url: '{{ route('print-stocker-all-size-pcs') }}/'+part,
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
                    generating = false;
                },
                error: function(jqXHR) {
                    console.log(jqXHR);

                    generating = false;

                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Terjadi kesalahan',
                        confirmButtonText: 'Coba Lagi',
                        showCancelButton: true,
                        cancelButtonText: 'Batalkan',
                    }).then(result => {
                        if (result.isConfirmed) {
                            printStockerAllSize(part); // Retry the request
                        }
                    });
                }
            });
        }

        function massChange(element) {
            let dataGroup = element.getAttribute('data-group');
            let dataChecked = element.checked;

            let similarElements = document.getElementsByClassName(dataGroup);

            for (let i = 0; i < similarElements.length; i++) {
                similarElements[i].checked = dataChecked;
            };
        }

        function generateCheckedStocker() {
            let generateStockerCheck = document.getElementsByClassName('generate-stocker-check');

            let checkedCount = 0;
            for (let i = 0; i < generateStockerCheck.length; i++) {
                if (generateStockerCheck[i].checked) {
                    checkedCount++;
                }
            }

            if (checkedCount > 0) {
                generating = true;

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
                    no_form_cut
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
                    url: '{{ route('print-stocker-checked-pcs') }}',
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

                        generating = false;
                    },
                    error: function(jqXHR) {
                        console.log(jqXHR);

                        generating = false;

                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Terjadi kesalahan',
                            confirmButtonText: 'Coba Lagi',
                            showCancelButton: true,
                            cancelButtonText: 'Batalkan',
                        }).then(result => {
                            if (result.isConfirmed) {
                                generateCheckedStocker(); // Retry the request
                            }
                        });
                    }
                });
            } else {
                Swal.fire({
                    icon:'warning',
                    title: 'Warning',
                    html: 'Harap ceklis stocker yang akan di print',
                });
            }
        }

        function rearrangeGroup(noForm) {
            Swal.fire({
                title: 'Please Wait...',
                html: 'Rearranging Data...',
                didOpen: () => {
                    Swal.showLoading()
                },
                allowOutsideClick: false,
            });

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

        function countStockerUpdate() {
            generating = true;

            Swal.fire({
                title: 'Please Wait...',
                html: 'Fixing Data...',
                didOpen: () => {
                    Swal.showLoading()
                },
                allowOutsideClick: false,
            });

            let stockerForm = new FormData(document.getElementById("stocker-form"));

            return $.ajax({
                url: '{{ route('count-stocker-update') }}',
                type: 'put',
                data: stockerForm,
                processData: false,
                contentType: false,
                success: function(res) {
                    console.log("successs", res);

                    swal.close();

                    generating = false;
                },
                error: function(jqXHR) {
                    console.log("error", jqXHR);

                    swal.close();

                    generating = false;
                }
            });
        }

        function redirectToCut(select) {
            const url = select.value;
            if (url) {
                window.location.href = url;
            }
        }
    </script>
@endsection
