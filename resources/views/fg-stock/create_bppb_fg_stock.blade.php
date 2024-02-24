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
    <form name='form_modal'id='form_modal'>
        <div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
            aria-hidden="true">
            <div class="modal-dialog modal-xl modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header bg-sb text-light">
                        <h1 class="modal-title fs-5" id="exampleModalLabel"> <i class="fas fa-shopping-cart"></i> Cek Stok
                        </h1>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class='row'>
                            <div class="col-md-12 table-responsive">
                                <table id="datatable_modal" class="table table-bordered table-hover table-sm w-100">
                                    <thead>
                                        <tr>
                                            <th>Lokasi</th>
                                            <th>No. Carton</th>
                                            <th>Buyer</th>
                                            <th>Brand</th>
                                            <th>Style</th>
                                            <th>Grade</th>
                                            <th>WS</th>
                                            <th>Color</th>
                                            <th>Style</th>
                                            <th>Size</th>
                                            <th>Saldo</th>

                                        </tr>
                                    </thead>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                    </div>
                </div>
            </div>
        </div>
    </form>


    <form id="form" name='form' method='post' action="{{ route('store-bppb-fg-stock') }}"
        onsubmit="submitForm(this, event)">
        <div class="card card-sb">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center ">
                    <h5 class="card-title fw-bold mb-0"><i class="fas fa-box-open"></i> Input Pengeluaran Barang Jadi Stok
                    </h5>
                    <a href="{{ route('bppb-fg-stock') }}" class="btn btn-sm btn-primary">
                        <i class="fa fa-reply"></i> Kembali
                    </a>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <a class="btn btn-outline-info position-relative" data-bs-toggle="modal"
                                data-bs-target="#exampleModal" onclick="cekstok()">
                                <i class="fas fa-shopping-cart"></i>
                                <small> Cek Stock</small>
                            </a>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-2">
                        <div class="form-group">
                            <label><small><b>Tanggal Pengeluaran</b></small></label>
                            <input type="date" class="form-control" id="tgl_pengeluaran" name="tgl_pengeluaran"
                                value="{{ date('Y-m-d') }}">
                            <input type="hidden" class="form-control" id="user" name="user"
                                value="{{ $user }}">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label><small><b>Lokasi</b></small></label>
                            <select class='form-control select2bs4' style='width: 100%;' name='cbolok' id='cbolok'
                                onchange='get_ws()'>
                                <option selected="selected" value="" disabled="true">Pilih Lokasi</option>
                                @foreach ($data_lok as $datalok)
                                    <option value="{{ $datalok->isi }}">
                                        {{ $datalok->tampil }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    {{-- <div class="col-md-3">
                        <div class="form-group">
                            <label><small><b>WS</b></small></label>
                            <select class='form-control select2bs4 ' style='width: 100%;' name='cbows'
                                id='cbows'></select>
                        </div>
                    </div> --}}
                    {{-- <div class="col-md-4">
                        <div class="form-group">
                            <label><small><b>No. Karton</b></small></label>
                            <select class='form-control select2bs4 ' multiple="multiple" style='width: 100%;'
                                name='cbocarton' id='cbocarton' onchange='getdata()'></select>
                        </div>
                    </div> --}}
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <div class="card card-primary card-outline">
                    <div class="card-header">
                        <h5 class="card-title"><i class="fas fa-list"></i> Detail Pengeluaran</h5>
                    </div>
                    <div class="card-body">
                        <div>
                            <table id="datatable_det" class="table table-bordered table-sm w-100 table-hover">
                                <thead class="table-primary">
                                    <tr>
                                        <th>No. Karton</th>
                                        <th>Brand</th>
                                        <th>Style</th>
                                        <th>Grade</th>
                                        <th>WS</th>
                                        <th>Color</th>
                                        <th>Size</th>
                                        <th>Saldo</th>
                                        <th>Qty Keluar</th>
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
                                {{-- <a class="btn btn-outline-success" onclick="simpan()">
                                <i class="fas fa-check"></i>
                                Simpan
                            </a> --}}
                                <button type="submit" class="btn btn-outline-success">Simpan </button>
                            </div>
                        </div>
                    </div>
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
        function notif() {
            alert("Maaf, Fitur belum tersedia!");
        }

        function undo() {
            location.reload();
        }

        $(document).ready(function() {
            $("#cbolok").val('').trigger('change');
        })

        $('#datatable_modal thead tr').clone(true).appendTo('#datatable_modal thead');
        $('#datatable_modal thead tr:eq(1) th').each(function(i) {
            var title = $(this).text();
            $(this).html('<input type="text" class="form-control form-control-sm" />');

            $('input', this).on('keyup change', function() {
                if (datatable.column(i).search() !== this.value) {
                    datatable
                        .column(i)
                        .search(this.value)
                        .draw();
                }
            });

        });


        function get_ws() {
            let cbolok = document.form.cbolok.value;
            let html = $.ajax({
                type: "GET",
                url: '{{ route('getws') }}',
                data: {
                    cbolok: cbolok
                },
                async: false
            }).responseText;
            // console.log(html != "");
            if (html != "") {
                $("#cbows").html(html);
                dataTableReload();
                // $("#cbomarker").prop("disabled", false);
                // $("#txtqtyply").prop("readonly", false);
            }
        };

        function dataTableReload() {
            let datatable = $("#datatable_det").DataTable({
                ordering: false,
                processing: true,
                serverSide: true,
                paging: false,
                destroy: true,
                autoWidth: true,
                ajax: {
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    url: '{{ route('show_det') }}',
                    dataType: 'json',
                    dataSrc: 'data',
                    data: function(d) {
                        d.cbolok = $('#cbolok').val();
                    },
                },
                columns: [{
                        data: 'no_carton',
                    },
                    {
                        data: 'brand',
                    },
                    {
                        data: 'styleno',
                    },
                    {
                        data: 'grade',
                    },
                    {
                        data: 'ws',
                    },
                    {
                        data: 'color',
                    },
                    {
                        data: 'size',
                    },
                    {
                        data: 'saldo',
                    },
                    {
                        data: 'kode',
                    },
                ],
                columnDefs: [{
                    targets: [8],
                    render: (data, type, row, meta) => {
                        // return '<input type="text" id="txtqty' + meta.row + '" name="txtqty[' + meta
                        //     .row + ']" value = "0"  />'
                        // return '<input type="number" size="10" id="txtqty[' + row.kode +
                        //     ']" name="txtqty[' + row
                        //     .kode + ']" autocomplete="off" />'
                        return `
                        <div>
                            <input type="number" size="10" id="txtqty[` + row.kode + `]"
                            name="txtqty[` + row.kode + `]" value = "0" autocomplete="off" />
                        </div>
                        <div>
                            <input type="hidden" size="10" id="id_so_det[` + row.kode + `]"
                            name="id_so_det[` + row.kode + `]" value = "` + row.id_so_det + `"/>
                        </div>
                        <div>
                            <input type="hidden" size="10" id="no_carton[` + row.kode + `]"
                            name="no_carton[` + row.kode + `]" value = "` + row.no_carton + `"/>
                        </div>
                        <div>
                            <input type="hidden" size="10" id="grade[` + row.kode + `]"
                            name="grade[` + row.kode + `]" value = "` + row.grade + `"/>
                        </div>
                        `;
                    }
                }, ]
            });
        }

        function cekstok() {

            let datatable = $("#datatable_modal").DataTable({
                ordering: false,
                processing: true,
                serverSide: true,
                scrollCollapse: true,
                scroller: true,
                paging: true,
                destroy: true,
                info: false,
                searching: true,
                ajax: {
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    url: '{{ route('getstok-bppb-fg-stock') }}',
                    dataType: 'json',
                    dataSrc: 'data',
                },
                columns: [{
                        data: 'lokasi',
                    },
                    {
                        data: 'no_carton',
                    },
                    {
                        data: 'buyer',
                    },
                    {
                        data: 'brand',
                    },
                    {
                        data: 'styleno',
                    },
                    {
                        data: 'grade',
                    },
                    {
                        data: 'ws',
                    },
                    {
                        data: 'color',
                    },
                    {
                        data: 'styleno',
                    },
                    {
                        data: 'size',
                    },
                    {
                        data: 'saldo',
                    },
                ],
            });
        }
    </script>
@endsection
