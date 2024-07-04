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
                <h5 class="card-title fw-bold">
                    <i class="fa fa-cog fa-sm"></i> Atur Part Secondary
                </h5>
                <a href="{{ route('part') }}" class="btn btn-sm btn-primary">
                    <i class="fa fa-reply"></i> Kembali ke Part
                </a>
            </div>
        </div>
        <div class="card-body">
            <form action="#" method="post">
                <div class="row">
                    <input type="hidden" class="form-control form-control-sm" name="id" id="id" value="{{ $part->id }}" readonly>
                    <div class="col-md-3">
                        <div class="mb-3">
                            <label><small><b>Kode Part</b></small></label>
                            <input type="text" class="form-control form-control-sm" name="kode" id="kode" value="{{ $part->kode }}" readonly>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="mb-3">
                            <label><small><b>No. WS</b></small></label>
                            <input type="text" class="form-control form-control-sm" name="ws" id="ws" value="{{ $part->act_costing_ws }}" readonly>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="mb-3">
                            <label><small><b>Buyer</b></small></label>
                            <input type="text" class="form-control form-control-sm" name="buyer" id="buyer" value="{{ $part->buyer }}" readonly>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="mb-3">
                            <label><small><b>Style</b></small></label>
                            <input type="text" class="form-control form-control-sm" name="style" id="style" value="{{ $part->style }}" readonly>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="mb-3">
                            <label><small><b>Color</b></small></label>
                            <input type="text" class="form-control form-control-sm" name="color" id="color" value="{{ $part->color }}" readonly>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="mb-3">
                            <label><small><b>Panel</b></small></label>
                            <input type="text" class="form-control form-control-sm" name="panel" id="panel" value="{{ $part->panel }}" readonly>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label><small><b>Parts</b></small></label>
                            <input type="text" class="form-control form-control-sm" name="part_details" id="part_details" value="{{ $part->part_details }}" readonly>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <div class="row mb-3">
        <div class="col-12 mb-3">
            <div class="card card-primary h-100">
                <div class="card-header">
                    <div class="row align-items-center">
                        <div class="col-6">
                            <h5 class="card-title fw-bold">
                                <i class="fa fa-list fa-sm"></i> Tambah Part Secondary :
                            </h5>
                        </div>
                    </div>
                </div>
                <form method="post" id="store-spreading" name='form' onsubmit="submitSpreadingForm(this, event)">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="mb-4">
                                    <label><small><b>Part</b></small></label>
                                    <select class="form-control form-control-sm" id="txtpart" name="txtpart">
                                        <option selected="selected" value="">Pilih Part</option>
                                        @foreach ($masterPart as $mp)
                                            <option value="{{ $mp->isi }}">
                                                {{ $mp->tampil }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="mb-4">
                                    <label><small><b>Cons</b></small></label>
                                    <div class="input-group input-group-sm mb-3">
                                        <input type="text" class="form-control form-control-sm" name="txtcons" id="txtcons">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text" id="inputGroup-sizing-sm">METER</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="mb-4">
                                    <label><small><b>Tujuan</b></small></label>
                                    <select class="form-control form-control-sm" id="cbotuj" name="cbotuj" onchange="getProses();">
                                        <option selected="selected" value="">Pilih Tujuan</option>
                                        @foreach ($masterTujuan as $mt)
                                            <option value="{{ $mt->isi }}">
                                                {{ $mt->tampil }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="mb-4">
                                    <label><small><b>Proses</b></small></label>
                                    <select class="form-control form-control-sm" id="cboproses" name="cboproses" style="width: 100%;">
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-1">
                                <div class="mb-4">
                                    <label><small><b>&nbsp;</b></small></label>
                                    <input type="button" class="form-control form-control-sm bg-primary" name="simpan" id="simpan" value="Simpan"onclick="save();">
                                </div>
                            </div>
                            <div class="table-responsive">
                                <table id="datatable_list_part" class="table table-bordered table-sm w-100">
                                    <thead>
                                        <tr>
                                            <th>Part</th>
                                            <th>Cons</th>
                                            <th>Satuan</th>
                                            <th>Tujuan</th>
                                            <th>Proses</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
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
    <!-- Page specific script -->
    <script>
        //Initialize Select2 Elements
        $('.select2').select2()

        //Initialize Select2 Elements
        $('.select2bs4').select2({
            theme: 'bootstrap4'
        })

        clear();
        dataTableReload();

        function clear() {
            $("#cboproses").val('');
            $("#cbotuj").val('');
            $("#txtpart").val('');
            $("#txtcons").val('');
        }

        function dataTableReload() {
            let datatable = $("#datatable_list_part").DataTable({
                ordering: false,
                processing: true,
                serverSide: true,
                paging: false,
                destroy: true,
                ajax: {
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    url: '{{ route('datatable_list_part') }}',
                    dataType: 'json',
                    dataSrc: 'data',
                    data: function(d) {
                        d.id = $('#id').val();
                    },
                },
                columns: [{
                        data: 'nama_part',
                    },
                    {
                        data: 'cons',
                    },
                    {
                        data: 'unit',
                    },
                    {
                        data: 'tujuan',
                    },
                    {
                        data: 'proses',
                    },
                ],
                columnDefs: [
                    {
                        targets: [0],
                        className: "text-center",
                        render: (data, type, row, meta) => {
                            if (row.total_stocker > 0) {
                                return `
                                    <button class='btn btn-secondary btn-sm' data='`+JSON.stringify(row)+`' data-url='{{ route('destroy-part-detail') }}/`+row['id']+`' onclick='deleteData(this)' disabled>
                                        <i class='fa fa-trash'></i>
                                    </button>
                                `;
                            }

                            return `
                                <a class='btn btn-danger btn-sm' data='`+JSON.stringify(row)+`' data-url='{{ route('destroy-part-detail') }}/`+row['id']+`' onclick='deleteData(this)' `+(row.total_stocker > 0 ? 'disabled' : '')+`>
                                    <i class='fa fa-trash'></i>
                                </a>
                            `;
                        }
                    },
                ]
            });
        }

        function getProses() {
            let cbotuj = document.form.cbotuj.value;
            let html = $.ajax({
                type: "GET",
                url: '{{ route('get_proses') }}',
                data: {
                    cbotuj: cbotuj
                },
                async: false
            }).responseText;

            if (html != "") {
                $("#cboproses").html(html);
            }
        };

        function save() {
            let cbotuj = document.form.cbotuj.value;
            let txtpart = document.form.txtpart.value;
            let txtcons = document.form.txtcons.value;
            let cboproses = document.form.cboproses.value;
            $.ajax({
                type: "post",
                url: '{{ route('store_part_secondary') }}',
                data: {
                    cbotuj: cbotuj,
                    txtpart: txtpart,
                    txtcons: txtcons,
                    cboproses: cboproses
                },
                success: function(response) {
                    if (response.icon == 'salah') {
                        iziToast.warning({
                            message: response.msg,
                            position: 'topCenter'
                        });
                    } else {
                        iziToast.success({
                            message: response.msg,
                            position: 'topCenter'
                        });
                    }
                    dataTableReload();
                    clear();
                },
            });
        };
    </script>
@endsection
