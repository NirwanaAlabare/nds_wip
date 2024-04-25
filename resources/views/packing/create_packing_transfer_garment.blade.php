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
    <div class="card card-info">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center ">
                <h5 class="card-title fw-bold mb-0"><i class="fas fa-shirt"></i> Input Transfer Garment</h5>
                <a href="{{ route('transfer-garment') }}" class="btn btn-sm btn-primary">
                    <i class="fa fa-reply"></i> Kembali
                </a>
            </div>
        </div>
        <form id="form_h" name='form_h' method='post'>
            <div class="card-body">
                <div class="row justify-content-center align-items-end">
                    <div class="col-md-12">
                        <div class="mb-3">
                            <label>Line</label>
                            <select class="form-control select2bs4" id="cboline" name="cboline" style="width: 100%;"
                                onchange="showgarment()">
                                <option selected="selected" value="" disabled="true">Pilih Line</option>
                                @foreach ($data_line as $dataline)
                                    <option value="{{ $dataline->isi }}">
                                        {{ $dataline->tampil }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="mb-3">
                            <div class="form-group">
                                <label>Tipe Garment</label>
                                <select class='form-control select2bs4 form-control-sm' style='width: 100%;'
                                    name='cbo_garment' id='cbo_garment' onchange='getcolor();getproduct();'></select>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="mb-3">
                            <label>Qty</label>
                            <select class="form-control select2bs4" id="cbolok" name="cbolok" style="width: 100%;"
                                onchange="showlok()">
                                <option selected="selected" value="" disabled="true">Pilih Line</option>
                                @foreach ($data_line as $dataline)
                                    <option value="{{ $dataline->isi }}">
                                        {{ $dataline->tampil }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="mb-3">
                            <label>PO</label>
                            <select class="form-control select2bs4" id="cbolok" name="cbolok" style="width: 100%;"
                                onchange="showlok()">
                                <option selected="selected" value="" disabled="true">Pilih Line</option>
                                @foreach ($data_line as $dataline)
                                    <option value="{{ $dataline->isi }}">
                                        {{ $dataline->tampil }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
                <div class="d-flex flex-row-reverse">
                    <a class="btn btn-outline-success" onclick="simpan()">
                        <i class="fas fa-plus"></i>
                        Tambah
                    </a>
                </div>
            </div>
        </form>
        <div class="row">
            <div class="col-md-12">
                <div class="card card-primary card-outline">
                    <div class="card-header">
                        <h5 class="card-title"><i class="fas fa-list"></i> List Garment</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="datatable_tmp" class="table table-bordered table-sm w-100">
                                <thead>
                                    <tr>
                                        <th>No. Karton</th>
                                        <th>Brand</th>
                                        <th>Style</th>
                                        <th>Grade</th>
                                        <th>WS</th>
                                        <th>Color</th>
                                        <th>Size</th>
                                        <th>Qty</th>
                                        <th>Act</th>
                                    </tr>
                                </thead>
                            </table>
                        </div>
                        <div class="d-flex justify-content-between">
                            <div class="p-2 bd-highlight">
                                <a class="btn btn-outline-warning" onclick="undo()">
                                    <i class="fas fa-sync-alt
                                    fa-spin"></i>
                                    Undo
                                </a>
                            </div>
                            <div class="p-2 bd-highlight">
                                <a class="btn btn-outline-success" onclick="simpan()">
                                    <i class="fas fa-check"></i>
                                    Simpan
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    </div>
@endsection

@section('custom-script')
    <!-- DataTables & Plugins -->
    <script src="{{ asset('plugins/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-responsive/js/dataTables.responsive.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-responsive/js/responsive.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('plugins/select2/js/select2.full.min.js') }}"></script>
    <script>
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
    </script>
    <script>
        function showgarment() {
            let cboline = document.form_h.cboline.value;
            let html = $.ajax({
                type: "GET",
                url: '{{ route('gettipe_garment') }}',
                data: {
                    cbo_line: cboline
                },
                async: false
            }).responseText;
            // console.log(html != "");
            if (html != "") {
                $("#cbo_garment").html(html);
            }
        };
    </script>
@endsection
