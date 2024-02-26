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
            <div class="d-flex justify-content-between align-items-center ">
                <h5 class="card-title fw-bold mb-0"><i class="fas fa-exchange"></i> Mutasi Internal
                    Barang Jadi Stok
                    <i class="fas fa-exchange fa-flip-horizontal"></i>
                </h5>
                <a href="{{ route('mutasi-fg-stock') }}" class="btn btn-light">
                    <i class="fa fa-reply"></i> Kembali
                </a>
            </div>
        </div>
    </div>
    <form id="form_h" name='form_h' method='post' action="{{ route('store-mutasi-fg-stock') }}"
        onsubmit="submitForm(this, event)">
        <div class="row">
            <div class="col-md-6">
                <div class="card card-primary card-outline">
                    <div class="card-header">
                        <h6 class="card-title"><i class="fas fa-exchange" style="color:blue;"></i> Posisi Asal</h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label><small><b>Lokasi</b></small></label>
                                    <select class="form-control select2bs4" id="cbolok_asal" name="cbolok_asal"
                                        style="width: 100%;" onchange="getno_karton_asal()">
                                        <option selected="selected" value="" disabled="true">Pilih Lokasi Asal
                                        </option>
                                        @foreach ($data_lok_asal as $datalok_asal)
                                            <option value="{{ $datalok_asal->isi }}">
                                                {{ $datalok_asal->tampil }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label><small><b>No. Karton</b></small></label>
                                    <select class='form-control select2bs4 form-control-sm' style='width: 100%;'
                                        name='cbono_carton_asal' id='cbono_carton_asal'
                                        onchange="dataTableReload()"></select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card card-primary card-outline">
                    <div class="card-header">
                        <h6 class="card-title"><i class="fas fa-exchange fa-flip-horizontal" style="color:red;"></i> Posisi
                            Tujuan</h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label><small><b>Lokasi</b></small></label>
                                    <select class="form-control select2bs4" id="cbolok_tuj" name="cbolok_tuj"
                                        style="width: 100%;">
                                        <option selected="selected" value="" disabled="true">Pilih Lokasi Tujuan
                                        </option>
                                        @foreach ($data_lok_tuj as $datalok_tuj)
                                            <option value="{{ $datalok_tuj->isi }}">
                                                {{ $datalok_tuj->tampil }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label><small><b>No. Karton Tujuan</b></small></label>
                                    <input type="text" class="form-control" id="txtno_carton_tuj" name="txtno_carton_tuj"
                                        value="" autocomplete="off" placeholder="Pilih No Kartun Tujuan">
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>

        <div class="card card-primary card-outline">
            <div class="card-header">
                <div class="align-items-center">
                    <h6 class="card-title mb-0"><i class="fas fa-cart-plus"></i> List Detail Barang Jadi Stok
                    </h6>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="datatable_det" class="table table-bordered table-sm w-100">
                        <thead class="table-primary">
                            <tr>
                                <th>Lokasi</th>
                                <th>No. Karton</th>
                                <th>Buyer</th>
                                <th>Brand</th>
                                <th>Style</th>
                                <th>Grade</th>
                                <th>WS</th>
                                <th>Color</th>
                                <th>Size</th>
                                <th>Qty</th>
                                <th>Qty Transfer</th>
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
                        <button type="submit" class="btn btn-outline-success">Simpan </button>
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
            $("#cbolok_asal").val('').trigger('change');
            $("#cbolok_tuj").val('').trigger('change');
            $("#txtno_carton_tuj").val('');
        })

        function getno_karton_asal() {
            let cbolok_asal = document.form_h.cbolok_asal.value;
            let html = $.ajax({
                type: "GET",
                url: '{{ route('getno-karton-asal-fg-stock') }}',
                data: {
                    cbolok_asal: cbolok_asal
                },
                async: false
            }).responseText;
            if (html != "") {
                $("#cbono_carton_asal").html(html);
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
                    url: '{{ route('show_det-fg-stock') }}',
                    dataType: 'json',
                    dataSrc: 'data',
                    data: function(d) {
                        d.cbolok_asal = $('#cbolok_asal').val();
                        d.cbono_carton_asal = $('#cbono_carton_asal').val();
                    },
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
                        data: 'size',
                    },
                    {
                        data: 'saldo',
                    },
                ],
                columnDefs: [{
                        targets: [10],
                        render: (data, type, row, meta) => {
                            // return '<input type="text" id="txtqty' + meta.row + '" name="txtqty[' + meta
                            //     .row + ']" value = "0"  />'
                            // return '<input type="number" size="10" id="txtqty[' + row.kode +
                            //     ']" name="txtqty[' + row
                            //     .kode + ']" autocomplete="off" />'
                            return `
                        <div>
                            <input type="number" size="10" id="txtqty[` + row.kode + `]"
                            name="txtqty[` + row.kode + `]" value = "0" autocomplete="off" max = "` + row.saldo + `" min = "0"/>
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
                    },

                ]
            });
        }
    </script>
@endsection
