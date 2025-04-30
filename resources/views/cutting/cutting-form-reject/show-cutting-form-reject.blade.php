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
    @php
        $currentRejectDetail = $formCutReject->formCutRejectDetails->where("qty", ">", "0")->sortBy("id");
    @endphp
    <form action="#" method="post" id="stocker-form">
        <div class="card card-sb">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="card-title fw-bold">
                        <i class="fa fa-search"></i> Detail Form Ganti Reject
                    </h5>
                    <a href="{{ route('cutting-reject') }}" class="btn btn-sb-secondary btn-sm fw-bold"><i class="fa fa-reply"></i> Kembali Ke Form Ganti Reject</a>
                </div>
            </div>
            <div class="card-body">
                <div class="row row-gap-3">
                    <div class="col-md-3 d-none">
                        <label class="form-label">ID</label>
                        <input type="hidden" class="form-control" id="id" name="id" value="{{ $formCutReject->id }}" readonly>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Worksheet</label>
                        <input type="hidden" class="form-control" id="act_costing_id" name="act_costing_id" value="{{ $formCutReject->act_costing_id }}" readonly>
                        <input type="text" class="form-control" id="act_costing_ws" name="act_costing_ws" value="{{ $formCutReject->act_costing_ws }}" readonly>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Buyer</label>
                        <input type="hidden" class="form-control" id="buyer_id" name="buyer_id" value="{{ $formCutReject->buyer_id }}" readonly>
                        <input type="text" class="form-control" id="buyer" name="buyer" value="{{ $formCutReject->buyer }}" readonly>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Style</label>
                        <input type="text" class="form-control" id="style" name="style" value="{{ $formCutReject->style }}" readonly>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Color</label>
                        <input type="text" class="form-control" id="color" name="color" value="{{ $formCutReject->color }}" readonly>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Panel</label>
                        <input type="text" class="form-control" id="panel" name="panel" value="{{ $formCutReject->panel }}" readonly>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">No. Form</label>
                        <input type="text" class="form-control" id="no_form" name="no_form" value="{{ $formCutReject->no_form }}" readonly>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Size</label>
                        <input type="text" class="form-control" id="size" name="size" value="{{ $currentRejectDetail->implode("size", " / ") }}" readonly>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Group</label>
                        <input type="text" class="form-control" id="group" name="group" value="{{ $formCutReject->group }}" readonly>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Tanggal</label>
                        <input type="date" class="form-control" id="tanggal" name="tanggal" value="{{ $formCutReject->tanggal }}" readonly>
                    </div>
                    <div class="col-md-3"></div>
                    <div class="col-md-3"></div>
                    <div class="col-md-3">
                        <label class="form-label">Note</label>
                        <textarea class="form-control" name="note" id="note" cols="30" rows="3"></textarea>
                    </div>
                    @if ($partDetails->count() > 0)
                        @php
                            $i = 0;
                            $j = 0;
                        @endphp
                        <div class="accordion mt-3" id="accordionPanelsStayOpenExample">
                            @foreach ($partDetails as $partDetail)
                                <div class="accordion-item">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h2 class="accordion-header w-75">
                                            <button class="accordion-button accordion-sb collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#panelsStayOpen-{{ $i }}" aria-expanded="false" aria-controls="panelsStayOpen-collapseTwo">
                                                <div class="d-flex w-75 justify-content-between align-items-center">
                                                    <p class="mb-0">{{ $partDetail->nama_part." // ".$partDetail->bag }}</p>
                                                    <p class="mb-0">{{ $partDetail->tujuan." // ".$partDetail->proses }}</p>
                                                </div>
                                            </button>
                                        </h2>
                                        <div class="accordion-header-side col-3">
                                            <div class="form-check ms-3">
                                                <input class="form-check-input generate-stocker-check generate-{{ $partDetail->id }}" type="checkbox" id="generate_{{ $i }}" name="generate_stocker[{{ $i }}]" data-group="generate-{{ $partDetail->id }}" value="{{ $partDetail->id }}">
                                                <label class="form-check-label fw-bold text-sb">
                                                    Generate Stocker
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                    <div id="panelsStayOpen-{{ $i }}" class="accordion-collapse collapse">
                                        <div class="accordion-body">
                                            <div class="table-responsive">
                                                <table class="table table-bordered table" id="table-ratio-{{ $i }}">
                                                    <thead>
                                                        <th class="d-none">ID</th>
                                                        <th class="d-none">So Det Id</th>
                                                        <th>Size</th>
                                                        <th>Qty</th>
                                                    </thead>
                                                    <tbody>
                                                        @foreach ($currentRejectDetail as $rejectDetail)
                                                            <tr>
                                                                <td class="d-none">{{ $rejectDetail->id }}</td>
                                                                <td class="d-none">{{ $rejectDetail->so_det_id }}</td>
                                                                <td>{{ $rejectDetail->size }}</td>
                                                                <td>{{ $rejectDetail->qty }}</td>
                                                            </tr>

                                                            <input type="hidden" name="part_detail_id[{{ $j }}]" id="part_detail_id_{{ $j }}" value="{{ $partDetail->id }}">
                                                            <input type="hidden" name="so_det_id[{{ $j }}]" id="so_det_id_{{ $j }}" value="{{ $rejectDetail->so_det_id }}">
                                                            <input type="hidden" name="size[{{ $j }}]" id="size_{{ $j }}" value="{{ $rejectDetail->size }}">
                                                            <input type="hidden" name="qty[{{ $j }}]" id="qty_{{ $j }}" value="{{ $rejectDetail->qty }}">

                                                            @php
                                                                $j++;
                                                            @endphp
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                                <button type="button" class="btn btn-sm btn-danger fw-bold float-end mb-3" onclick="printStockerAllSize('{{ $partDetail->id }}');"> Generate All Size <i class="fas fa-print"></i></button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @php
                                    $i++;
                                @endphp
                            @endforeach
                        </div>
                    @else
                        <div>
                            <h5 class="text-center">Part tidak ditemukan.</h5>
                        </div>
                    @endif
                </div>
                <div class="d-flex justify-content-end mt-3 p-1">
                    <button type="button" class="btn btn-danger btn-sm w-auto fw-bold" onclick="generateCheckedStocker()">Generate Selected Stocker <i class="fa fa-print"></i></button>
                </div>
            </div>
        </div>
    </form>
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
        document.getElementById("loading").classList.remove("d-none");

        // Initial Window On Load Event
        $(document).ready(async function () {
            document.getElementById("loading").classList.add("d-none");
        });

        // Select2 Autofocus
        $(document).on('select2:open', () => {
            document.querySelector('.select2-search__field').focus();
        });

        // Initialize Select2 Elements
        $('.select2').select2();

        // Initialize Select2BS4 Elements
        $('.select2bs4').select2({
            theme: 'bootstrap4',
        });

        function printStockerAllSize(part) {
            console.log(part);
            generating = true;

            let stockerForm = new FormData(document.getElementById("stocker-form"));

            let act_costing_ws = document.getElementById("act_costing_ws").value;
            let style = document.getElementById("style").value;
            let color = document.getElementById("color").value;
            let panel = document.getElementById("panel").value;
            let no_form = document.getElementById("no_form").value;

            let fileName = [
                act_costing_ws,
                style,
                color,
                panel,
                part,
                no_form
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
                url: '{{ route('print-stocker-reject-all-size') }}/'+part,
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
                }
            });
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

                let act_costing_ws = document.getElementById("act_costing_ws").value;
                let style = document.getElementById("style").value;
                let color = document.getElementById("color").value;
                let panel = document.getElementById("panel").value;
                let no_form = document.getElementById("no_form").value;

                let fileName = [
                    act_costing_ws,
                    style,
                    color,
                    panel,
                    no_form
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
                    url: '{{ route('print-stocker-reject-checked') }}',
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
    </script>
@endsection
