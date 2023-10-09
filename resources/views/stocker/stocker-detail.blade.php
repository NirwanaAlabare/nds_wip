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
            <h5 class="card-title fw-bold mb-0">Detail Stocker</h5>
        </div>
        <div class="card-body">
            <form action="#" method="post" id="stocker-form">
                <div class="row mb-3">
                    <div class="col-4">
                        <div class="mb-1">
                            <label class="form-label"><small>WS Number</small></label>
                            <input type="text" class="form-control form-control-sm" id="no_ws" name="no_ws" value="{{ $dataSpreading->ws }}" readonly>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="mb-1">
                            <label class="form-label"><small>Buyer</small></label>
                            <input type="text" class="form-control form-control-sm" id="buyer" name="buyer" value="{{ $dataSpreading->buyer }}" readonly>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="mb-1">
                            <label class="form-label"><small>Style</small></label>
                            <input type="text" class="form-control form-control-sm" id="style" name="style" value="{{ $dataSpreading->style }}" readonly>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="mb-1">
                            <label class="form-label"><small>Color</small></label>
                            <input type="text" class="form-control form-control-sm" id="color" name="color" value="{{ $dataSpreading->color }}" readonly>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="mb-1">
                            <label class="form-label"><small>Size</small></label>
                            <input type="text" class="form-control form-control-sm" id="size" name="size" value="{{ $dataSpreading->sizes }}" readonly>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="row">
                            <div class="col-7">
                                <div class="mb-1">
                                    <label class="form-label"><small>Panel</small></label>
                                    <input type="text" class="form-control form-control-sm" id="panel" name="panel" value="{{ $dataSpreading->panel }}" readonly>
                                </div>
                            </div>
                            <div class="col-5">
                                <div class="mb-1">
                                    <label class="form-label"><small>Shade</small></label>
                                    <input type="text" class="form-control form-control-sm" id="shade" name="shade" value="{{ $dataSpreading->shell }}">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="row">
                            <div class="col-6">
                                <div class="mb-1">
                                    <label class="form-label"><small>Form Cut</small></label>
                                    <input type="text" class="form-control form-control-sm" id="no_form_cut" name="no_form_cut" value="{{ $dataSpreading->no_form }}" readonly>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="mb-1">
                                    <label class="form-label"><small>Total Lembar</small></label>
                                    <input type="text" class="form-control form-control-sm" id="shell" name="shell" value="{{ $dataSpreading->total_lembar }}" readonly>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="mb-1">
                            <label class="form-label"><small>Tanggal Cutting</small></label>
                            <input type="date" class="form-control form-control-sm" id="tgl_form_cut" name="tgl_form_cut" value="{{ $dataSpreading->tgl_form_cut }}" readonly>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="mb-1">
                            <label class="form-label"><small>Note</small></label>
                            <textarea class="form-control form-control-sm" id="note" name="note" rows="2"></textarea>
                        </div>
                    </div>
                </div>
                <div class="table-responsive">
                    <table id="datatable" class="table table-bordered table-striped table-sm text-center w-100">
                        <thead>
                            <tr>
                                <th>Size</th>
                                <th>Ratio</th>
                                <th>Qty Cut</th>
                                <th>Range Awal</th>
                                <th>Range Akhir</th>
                                <th>Print Stocker</th>
                                <th>Print Numbering</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($dataRatio as $ratio)
                                @php
                                    $qty = intval($ratio->ratio) * intval($dataSpreading->total_lembar);
                                @endphp
                                <tr>
                                    <input type="hidden" name="ratio[{{ $loop->index }}]" id="ratio_{{ $loop->index }}" value="{{ $ratio->ratio }}">
                                    <input type="hidden" name="so_det_id[{{ $loop->index }}]" id="so_det_id_{{ $loop->index }}" value="{{ $ratio->so_det_id }}">
                                    <input type="hidden" name="size[{{ $loop->index }}]" id="size_{{ $loop->index }}" value="{{ $ratio->size }}">
                                    <input type="hidden" name="qty_cut[{{ $loop->index }}]" id="qty_cut_{{ $loop->index }}" value="{{ $qty }}">

                                    <td>{{ $ratio->size }}</td>
                                    <td>{{ $ratio->ratio }}</td>
                                    <td>{{ $qty }}</td>
                                    <td>1</td>
                                    <td>{{ $qty }}</td>
                                    <td>
                                        <button type="button" class="btn btn-sm btn-danger" onclick="printStocker({{ $loop->index }});">
                                            <i class="fa fa-print fa-s"></i>
                                        </button>
                                    </td>
                                    <td>
                                        <button type="button" class="btn btn-sm btn-danger">
                                            <i class="fa fa-print fa-s"></i>
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
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

        function printStocker(index) {
            let stockerForm = new FormData(document.getElementById("stocker-form"));

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
                        link.download = index+".pdf";
                        link.click();
                    }
                }
            });
        }

        function printNumbering(index) {
            let stockerForm = new FormData(document.getElementById("stocker-form"));

            // $.ajax({
            //     url: '{{ route('print-numbering') }}/'+index,
            //     type: 'post',
            //     processData: false,
            //     contentType: false,
            //     data: stockerForm,
            //     xhrFields:
            //     {
            //         responseType: 'blob'
            //     },
            //     success: function(res) {
            //         if (res) {
            //             console.log(res);

            //             var blob = new Blob([res], {type: 'application/pdf'});
            //             var link = document.createElement('a');
            //             link.href = window.URL.createObjectURL(blob);
            //             link.download = index+".pdf";
            //             link.click();
            //         }
            //     }
            // });
        }
    </script>
@endsection
