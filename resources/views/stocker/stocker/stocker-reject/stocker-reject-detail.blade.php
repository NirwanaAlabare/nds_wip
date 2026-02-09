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
    <div class="d-flex justify-content-between mb-3">
        <h5 class="text-sb"><i class="fa fa-search-plus fa-sm"></i> Stocker Reject Detail</h5>
        <a class="btn btn-primary btn-sm" href="{{ route('stocker-reject') }}"><i class="fa fa-reply"></i> Kembali ke stocker reject</a>
    </div>
    <form id="stocker-reject-form">
        <div class="card">
            <div class="card-header bg-sb">
                <h5 class="card-title">
                    {{ $data->id_qr_stocker }}
                </h5>
            </div>
            <div class="card-body">
                <div class="row row-gap-3">
                    <div class="col-4">
                        <div>
                            <label class="form-label">Stocker</label>
                            <input type="text" class="form-control" name="id_qr_stocker_source" id="id_qr_stocker_source" value="{{ $data->id_qr_stocker }}" readonly>
                        </div>
                    </div>
                    <div class="col-8">
                        <div>
                            <label class="form-label">Stocker Bundle</label>
                            <input type="text" class="form-control" name="id_qr_stocker_bundle" id="id_qr_stocker_bundle" value="{{ $data->id_qr_similar_stocker }}" readonly>
                        </div>
                    </div>
                    <div class="col-3">
                        <div>
                            <label class="form-label">No. WS</label>
                            <input type="text" class="form-control" name="act_costing_ws" id="act_costing_ws" value="{{ $data->act_costing_ws }}" readonly>
                        </div>
                    </div>
                    <div class="col-3">
                        <div>
                            <label class="form-label">Style</label>
                            <input type="text" class="form-control" name="style" id="style" value="{{ $data->style }}" readonly>
                        </div>
                    </div>
                    <div class="col-3">
                        <div>
                            <label class="form-label">Color</label>
                            <input type="text" class="form-control" name="color" id="color" value="{{ $data->color }}" readonly>
                        </div>
                    </div>
                    <div class="col-3">
                        <div>
                            <label class="form-label">Panel</label>
                            <input type="text" class="form-control" name="panel" id="panel" value="{{ $data->panel }}" readonly>
                        </div>
                    </div>
                    <div class="col-3">
                        <div>
                            <label class="form-label">Proses</label>
                            <input type="text" class="form-control" name="proses" id="proses" value="{{ $data->proses }}" readonly>
                        </div>
                    </div>
                    <div class="col-3">
                        <div>
                            <label class="form-label">Size</label>
                            <input type="text" class="form-control" name="size" id="size" value="{{ $data->size }}" readonly>
                        </div>
                    </div>
                    <div class="col-3">
                        <div>
                            <label class="form-label">No. Form</label>
                            <input type="text" class="form-control" name="no_form" id="no_form" value="{{ $data->no_form }}" readonly>
                        </div>
                    </div>
                    <div class="col-3">
                        <div>
                            <label class="form-label">Qty Reject</label>
                            <input type="text" class="form-control" name="qty" id="qty" value="{{ $data->qty_reject }}" readonly>
                        </div>
                    </div>
                    <div class="col-4 d-none">
                        <div>
                            <label class="form-label">Form Cut ID</label>
                            <input type="text" class="form-control d-none" name="form_cut_id" id="form_cut_id" value="{{ $data->form_cut_id }}" readonly>
                        </div>
                    </div>
                    <div class="col-4 d-none">
                        <div>
                            <label class="form-label">Urutan</label>
                            <input type="text" class="form-control d-none" name="current_urutan" id="current_urutan" value="{{ $data->urutan }}" readonly>
                        </div>
                    </div>
                    <div class="col-4 d-none">
                        <div>
                            <label class="form-label">DC IN ID</label>
                            <input type="text" class="form-control d-none" name="dc_in_id" id="dc_in_id" value="{{ $data->dc_in_id }}" readonly>
                        </div>
                    </div>
                    <div class="col-4 d-none">
                        <div>
                            <label class="form-label">Secondary Inhouse ID</label>
                            <input type="text" class="form-control d-none" name="secondary_inhouse_id" id="secondary_inhouse_id" value="{{ $data->secondary_inhouse_id }}" readonly>
                        </div>
                    </div>
                    <div class="col-4 d-none">
                        <div>
                            <label class="form-label">Secondary IN ID</label>
                            <input type="text" class="form-control d-none" name="secondary_in_id" id="secondary_in_id" value="{{ $data->secondary_in_id }}" readonly>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="card">
            <div class="card-header bg-sb-secondary">
                <h5 class="card-title">
                    Stocker
                </h5>
            </div>
            <div class="card-body">
                @php
                    $index = 0;
                @endphp
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Stocker Source</th>
                                <th>Part Detail</th>
                                <th>Size</th>
                                <th>Shade</th>
                                <th>Ratio</th>
                                <th>Qty</th>
                                <th>Print</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $index = 1;
                            @endphp
                            @foreach ($dataStocker as $stocker)
                                <tr>
                                    <input type="text" class="form-control d-none" id="stocker_id_{{ $index }}" name="stocker_id[{{ $index }}]" value="{{ $stocker->id }}" readonly >
                                    <input type="text" class="form-control d-none" id="id_qr_stocker_{{ $index }}" name="id_qr_stocker[{{ $index }}]" value="{{ $stocker->id_qr_stocker }}" readonly >
                                    <input type="text" class="form-control d-none" id="part_detail_id_{{ $index }}" name="part_detail_id[{{ $index }}]" value="{{ $stocker->part_detail_id }}" readonly >
                                    <input type="text" class="form-control d-none" id="shade_{{ $index }}" name="shade[{{ $index }}]" value="{{ $stocker->shade }}" readonly >
                                    <input type="text" class="form-control d-none" id="group_stocker_{{ $index }}" name="group_stocker[{{ $index }}]" value="{{ $stocker->group_stocker }}" readonly >
                                    <input type="text" class="form-control d-none" id="so_det_id_{{ $index }}" name="so_det_id[{{ $index }}]" value="{{ $stocker->so_det_id }}" readonly >
                                    <input type="text" class="form-control d-none" id="size_{{ $index }}" name="size[{{ $index }}]" value="{{ $stocker->size }}" readonly >
                                    <input type="text" class="form-control d-none" id="ratio_{{ $index }}" name="ratio[{{ $index }}]" value="{{ $stocker->ratio }}" readonly >
                                    <input type="text" class="form-control d-none" id="urutan_{{ $index }}" name="urutan[{{ $index }}]" value="{{ $stocker->urutan }}" readonly >
                                    <td>{{ $stocker->id_qr_stocker }}</td>
                                    <td>{{ $stocker->nama_part }}</td>
                                    <td>{{ $stocker->size }}</td>
                                    <td>{{ $stocker->shade }}</td>
                                    <td>{{ $stocker->ratio }}</td>
                                    <td>{{ $data->qty_reject }}</td>
                                    <td><button type="button" class="btn btn-danger" onclick="printStocker({{ $index }})"><i class="fa fa-print"></i></button></td>
                                </tr>

                                @php
                                    $index++;
                                @endphp
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="d-flex justify-content-end">
                    <button type="button" class="btn btn-danger btn-sm" onclick="printStocker()">Generate All <i class="fa fa-print"></i></button>
                </div>
            </div>
        </div>
    </form>
@endsection

@section('custom-script')
    <!-- DataTables & Plugins -->
    <script src="{{ asset('plugins/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-responsive/js/dataTables.responsive.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-responsive/js/responsive.bootstrap4.min.js') }}"></script>

    <!-- Select2 -->
    <script src="{{ asset('plugins/select2/js/select2.full.min.js') }}"></script>

    <script>

        // Generate Stocker
        function printStocker(index) {
            generating = true;

            let stockerRejectForm = new FormData(document.getElementById("stocker-reject-form"));

            let fileName = [
                'REJECT',
                $('#ws').val(),
                $('#style').val(),
                $('#color').val(),
                $('#panel').val(),
                $('#no_form').val(),
            ].join('-');

            Swal.fire({
                title: 'Please Wait...',
                html: 'Exporting Data...',
                didOpen: () => {
                    Swal.showLoading()
                },
                allowOutsideClick: false,
            });

            console.log(index)

            $.ajax({
                url: '{{ route('print-stocker-process-reject') }}'+(index ? '/'+index : ''),
                type: 'post',
                processData: false,
                contentType: false,
                data: stockerRejectForm,
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
    </script>
@endsection
