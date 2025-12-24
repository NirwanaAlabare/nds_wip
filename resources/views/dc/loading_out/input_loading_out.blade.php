@extends('layouts.index')

@section('custom-link')
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="{{ asset('plugins/datatables 2.0/jquery.dataTables.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/datatables 2.0/dataTables.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/datatables 2.0/fixedColumns.bootstrap4.min.css') }}">
    <!-- jQuery -->
    <script src="{{ asset('plugins/datatables 2.0/jquery-3.3.1.js') }}"></script>


    <!-- Select2 -->
    <link rel="stylesheet" href="{{ asset('plugins/select2/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}">

    <style>
        .select2-container--bootstrap4 .select2-selection--single {
            height: 31px !important;
            padding: 0.25rem 0.5rem !important;
            font-size: 0.875rem !important;
        }

        .select2-container--bootstrap4 .select2-selection__rendered {
            line-height: 1.5 !important;
        }
    </style>
@endsection

@section('content')
    <div class="card card-sb">
        <div class="card-header">
            <h5 class="card-title fw-bold mb-0"><i class="fas fa-list"></i> Header Loading Out / WIP Out</h5>
        </div>
        <div class="card-body pb-0">
            <div class="row mb-3">
                <div class="col-md-3">
                    <label for="cbo_sup"><small><b>Supplier :</b></small></label>
                    <select class="form-control form-control-sm select2bs4 select-border-primary visual-input"
                        id="cbo_sup" name="cbo_sup" style="width: 100%;" onchange="getno_po();">
                        <option selected="selected" value="" disabled="true">Pilih Supplier
                        </option>
                        @foreach ($data_supplier as $ds)
                            <option value="{{ $ds->isi }}">
                                {{ $ds->tampil }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="cbo_po"><small><b>PO :</b></small></label>
                    <select class='form-control select2bs4 select-border-primary visual-input' style='width: 100%;'
                        name='cbo_po' id='cbo_po' onchange="dataTablePOReload();"></select>
                </div>
                <div class="col-md-3">
                    <label for="cbo_dok"><small><b>Jenis Dokumen :</b></small></label>
                    <select class="form-control form-control-sm select2bs4 select-border-primary visual-input"
                        id="cbo_dok" name="cbo_dok" style="width: 100%;">
                        <option selected="selected" value="" disabled="true">Pilih Dokumen
                        </option>
                        @foreach ($data_dok as $dd)
                            <option value="{{ $dd->isi }}">
                                {{ $dd->tampil }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="cbo_jns"><small><b>Jenis Pengeluaran :</b></small></label>
                    <select class="form-control form-control-sm select2bs4 select-border-primary visual-input"
                        id="cbo_jns" name="cbo_jns" style="width: 100%;">
                        <option selected="selected" value="" disabled="true">Pilih Jenis
                        </option>
                        @foreach ($data_jns as $dj)
                            <option value="{{ $dj->isi }}">
                                {{ $dj->tampil }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-6">
                    <label for="txt_ket"><small><b>Keterangan :</b></small></label>
                    <input type="text" id="txt_ket" name="txt_ket" class="form-control form-control-sm border-primary">
                </div>
                <div class="col-md-3">
                    <label for="txt_berat_panel"><small><b>Berat Set Panel:</b></small></label>
                    <div class="input-group input-group-sm">
                        <input type="text" id="txt_berat_panel" name="txt_berat_panel"
                            class="form-control form-control-sm border-primary">
                        <span class="input-group-text border-primary text-primary">KG</span>
                    </div>
                </div>
                <div class="col-md-3">
                    <label for="txt_berat_karung"><small><b>Berat Karung:</b></small></label>
                    <div class="input-group input-group-sm">
                        <input type="text" id="txt_berat_karung" name="txt_berat_karung"
                            class="form-control form-control-sm border-primary">
                        <span class="input-group-text border-primary text-primary">KG</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card card-sb">
        <div class="card-header">
            <h5 class="card-title fw-bold mb-0"><i class="fas fa-list"></i> Detail PO </h5>
        </div>
        <div class="card-body pb-0">
            <div class="row mb-3">
                <div class="table-responsive">
                    <table id="datatable_po" class="table table-bordered table-hover align-middle text-nowrap w-100">
                        <thead class="bg-sb">
                            <tr>
                                <th class="text-center">Job Order</th>
                                <th class="text-center">WS</th>
                                <th class="text-center">ID Item</th>
                                <th class="text-center">Item</th>
                                <th class="text-center">Qty PO</th>
                                <th class="text-center">Unit</th>
                                <th class="text-center">Qty Outstanding</th>
                                <th class="text-center">Qty Out</th>
                                <th class="text-center">Balance</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="card card-sb">
        <div class="card-header">
            <h5 class="card-title fw-bold mb-0"><i class="fas fa-list"></i> Summary </h5>
        </div>
        <div class="card-body pb-0">
            <div class="row mb-3">
                <div class="table-responsive">
                    <table id="datatable" class="table table-bordered table-hover align-middle text-nowrap w-100">
                        <thead class="bg-sb">
                            <tr>
                                <th class="text-center">No. Karung</th>
                                <th class="text-center">WS</th>
                                <th class="text-center">Style</th>
                                <th class="text-center">Color</th>
                                <th class="text-center">Size</th>
                                <th class="text-center">Qty PCS</th>
                                <th class="text-center">NW (KG)</th>
                                <th class="text-center">GW (KG)</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="card card-sb">
        <div class="card-header">
            <h5 class="card-title fw-bold mb-0"><i class="fas fa-list"></i> Scan Stocker </h5>
        </div>
        <div class="card-body pb-0">
            <div class="row mb-3">
                <div class="table-responsive">
                    <table id="datatable" class="table table-bordered table-hover align-middle text-nowrap w-100">
                        <thead class="bg-sb">
                            <tr>
                                <th class="text-center">No. Karung</th>
                                <th class="text-center">WS</th>
                                <th class="text-center">Style</th>
                                <th class="text-center">Color</th>
                                <th class="text-center">Size</th>
                                <th class="text-center">Qty PCS</th>
                                <th class="text-center">NW (KG)</th>
                                <th class="text-center">GW (KG)</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('custom-script')
    <!-- DataTables & Plugins -->
    <script src="{{ asset('plugins/select2/js/select2.full.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables 2.0/dataTables.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables 2.0/dataTables.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables 2.0/dataTables.fixedColumns.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-rowsgroup/dataTables.rowsGroup.js') }}"></script>
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

        function notif() {
            alert("Maaf, Fitur belum tersedia!");
        }
    </script>
    <script>
        $(document).ready(function() {
            $('#cbo_sup').val('').trigger('change');
            $('#cbo_dok').val('').trigger('change');
            $('#cbo_jns').val('').trigger('change');
            $('#txt_ket').val('');
            $('#txt_berat_panel').val('0');
            $('#txt_berat_karung').val('0');
        });

        function getno_po() {
            let cbo_sup = $('#cbo_sup').val();

            $.ajax({
                type: "GET",
                url: '{{ route('getpo_loading_out') }}',
                data: {
                    cbo_sup: cbo_sup
                },
                success: function(html) {
                    if (html !== "") {
                        $("#cbo_po").html(html);
                    }
                },
                error: function(xhr) {
                    console.error(xhr.responseText);
                }
            });
        }


        let datatable_po = $('#datatable_po').DataTable({
            ordering: false,
            processing: true,
            serverSide: false,
            paging: false,
            searching: true,
            scrollY: '600px',
            scrollX: true,
            scrollCollapse: true,
            autoWidth: true,

            ajax: {
                url: "{{ route('get_list_po_loading_out') }}",
                type: "GET",
                data: function(d) {
                    d.id_po = $('#cbo_po').val();
                }
            },

            columns: [{
                    data: 'jo_no'
                },
                {
                    data: 'ws'
                },
                {
                    data: 'id_item'
                },
                {
                    data: 'itemdesc'
                },
                {
                    data: 'qty_po'
                },
                {
                    data: 'unit'
                },
                {
                    data: 'qty_po'
                },
                {
                    data: 'qty_po'
                },
                {
                    data: 'qty_po'
                }
            ],

            initComplete: function() {
                let table = this.api();
                setTimeout(() => table.columns.adjust(), 300);
            }
        });

        function dataTablePOReload() {
            datatable_po.ajax.reload(function() {
                setTimeout(() => {
                    datatable_po.columns.adjust();
                }, 300);
            }, false);
        }
    </script>
@endsection
