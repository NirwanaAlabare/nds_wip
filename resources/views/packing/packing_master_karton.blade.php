@extends('layouts.index')

@section('custom-link')
    <!-- DataTables -->
    <link rel="stylesheet" href="{{ asset('plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/datatables-responsive/css/responsive.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/datatables-buttons/css/buttons.bootstrap4.min.css') }}">

    <!-- Select2 -->
    <link rel="stylesheet" href="{{ asset('plugins/select2/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}">

    <style type="text/css">
        input::-webkit-outer-spin-button,
        input::-webkit-inner-spin-button {
            /* display: none; <- Crashes Chrome on hover */
            -webkit-appearance: none;
            margin: 0;
            /* <-- Apparently some margin are still there even though it's hidden */
        }

        input[type=number] {
            -moz-appearance: textfield;
            /* Firefox */
        }
    </style>
@endsection

@section('content')
    <div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
        aria-hidden="true">
        <form action="{{ route('store-tambah-karton') }}" method="post" onsubmit="submitForm(this, event)" name='form'
            id='form'>
            @method('POST')
            <div class="modal-dialog modal-dialog-scrollable modal-s">
                <div class="modal-content">
                    <div class="modal-header bg-sb text-light">
                        <h3 class="modal-title fs-5"><i class="fas fa-boxes"></i> Tambah Karton</h3>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label><small><b>PO :</b></small></label>
                            <select class="form-control select2bs4" id="cbopo" name="cbopo" style="width: 100%;"
                                onchange="gettot_karton()">
                                <option selected="selected" value="" disabled="true">Pilih PO</option>
                                @foreach ($data_po as $datapo)
                                    <option value="{{ $datapo->isi }}">
                                        {{ $datapo->tampil }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="row">
                            <div class="col-sm">
                                <div class="form-group">
                                    <label><small><b>Total Karton Sekarang # :</b></small></label>
                                    <input type='number' id="tot_skrg" name="tot_skrg"
                                        class='form-control form-control-sm' id="txtinput_carton" name="txtinput_carton"
                                        value="" readonly>
                                </div>
                            </div>
                            <div class="col-sm">
                                <div class="form-group">
                                    <label><small><b>Simulasi Tambah # :</b></small></label>
                                    <input type='number' id="txt_simulasi" name="txt_simulasi"
                                        class='form-control form-control-sm' value="0" readonly>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label><small><b>Penambahan Karton # :</b></small></label>
                            <input type='number' min = '0' value = '0' oninput="get_total_tambah()"
                                autocomplete="off" class='form-control form-control-sm' id="txtinput_carton"
                                name="txtinput_carton" value="">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-danger btn-sm" data-bs-dismiss="modal"><i
                                class="fas fa-times-circle"></i> Tutup</button>
                        <button type="submit" class="btn btn-outline-success btn-sm"><i class="fas fa-check"></i> Simpan
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <div class="card card-info  collapsed-card">
        <div class="card-header">
            <h5 class="card-title fw-bold mb-0"><i class="fas fa-upload"></i> Upload Master Karton</h5>
            <div class="card-tools">
                <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-plus"></i>
                </button>
            </div>
        </div>
        <div class="card-body">
            <div class="d-flex align-items-end gap-3 mb-3">
                <div class="mb-3">
                    <input type="hidden" class="form-control form-control-sm" id="user" name= "user"
                        value="{{ $user }}">
                    <a class="btn btn-outline-info position-relative btn-sm" data-toggle="modal" data-target="#importExcel"
                        onclick="OpenModal()">
                        <i class="fas fa-file-upload fa-sm"></i>
                        Upload
                    </a>
                </div>
                <div class="mb-3">
                    <a class="btn btn-outline-warning position-relative btn-sm"
                        href="{{ route('contoh_upload_ppic_so') }}">
                        <i class="fas fa-file-download fa-sm"></i>
                        Contoh Upload
                    </a>
                </div>
                <div class="mb-3">
                    <a onclick="export_excel_master_so_sb()" class="btn btn-outline-success position-relative btn-sm">
                        <i class="fas fa-file-download fa-sm"></i>
                        Master Data SO SB
                    </a>
                </div>
            </div>
            <label>Preview</label>
            <div class="table-responsive">
                <table id="datatable-preview" class="table table-bordered table-sm w-100 text-nowrap">
                    <thead class="table-info">
                        <tr style='text-align:center; vertical-align:middle'>
                            <th>DC Code</th>
                            <th>PO #</th>
                            <th>Sku / Item</th>
                            <th>Unit</th>
                            <th>Prepack</th>
                            <th>Gross Weight</th>
                            <th>No. Carton</th>
                            <th>Max Carton</th>
                            <th>Made In</th>
                        </tr>
                    </thead>
                </table>
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

    <div class="card card-sb">
        <div class="card-header">
            <h5 class="card-title fw-bold mb-0"><i class="fas fa-list"></i> List Master Karton</h5>
        </div>
        <div class="card-body">
            <div class="mb-3">
                <button class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#exampleModal"
                    onclick="reset()"><i class="fas fa-plus"></i> Baru</button>
            </div>
            <div class="d-flex align-items-end gap-3 mb-3">
                <div class="mb-3">
                    <label class="form-label"><small><b>Tgl Shipment Awal</b></small></label>
                    <input type="date" class="form-control form-control-sm " id="tgl-awal" name="tgl_awal"
                        oninput="dataTableReload()" value="{{ date('Y-m-d') }}">
                </div>
                <div class="mb-3">
                    <label class="form-label"><small><b>Tgl Shipment Akhir</b></small></label>
                    <input type="date" class="form-control form-control-sm" id="tgl-akhir" name="tgl_akhir"
                        oninput="dataTableReload()" value="{{ date('Y-m-d') }}">
                </div>
                <div class="mb-3">
                    <a onclick="notif()" class="btn btn-outline-success position-relative btn-sm">
                        <i class="fas fa-file-excel fa-sm"></i>
                        Export Excel
                    </a>
                </div>
            </div>

            <div class="table-responsive">
                <table id="datatable" class="table table-bordered table-sm w-100 table-hover display nowrap">
                    <thead class="table-primary">
                        <tr style='text-align:center; vertical-align:middle'>
                            <th>Tgl. Shipment</th>
                            <th>PO #</th>
                            <th>Jumlah Carton</th>
                        </tr>
                    </thead>
                </table>
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

        $('#exampleModal').on('show.bs.modal', function(e) {
            $('.select2bs4').select2({
                theme: 'bootstrap4',
                dropdownParent: $("#exampleModal")
            })
        })
    </script>
    <script>
        function notif() {
            alert("Maaf, Fitur belum tersedia!");
        }

        function reset() {
            $("#form").trigger("reset");
            $("#cbopo").val('').trigger('change');
        }
    </script>
    <script>
        $(document).ready(() => {
            reset();

            const todayA = new Date();
            const yyyyA = todayA.getFullYear();
            let mmA = todayA.getMonth() - 3; // Months start at 0!
            let ddA = todayA.getDate();
            if (ddA < 10) ddA = '0' + ddA;
            if (mmA < 10) mmA = '0' + mmA;
            const formattedTodayA = yyyyA + '-' + mmA + '-' + ddA;
            console.log(formattedTodayA);
            $("#tgl-awal").val(formattedTodayA).trigger("change");

            const todayE = new Date();
            const yyyyE = todayE.getFullYear();
            let mmE = todayE.getMonth() + 4; // Months start at 0!
            let ddE = todayE.getDate();
            if (ddE < 10) ddE = '0' + ddE;
            if (mmE < 10) mmE = '0' + mmE;
            const formattedTodayE = yyyyE + '-' + mmE + '-' + ddE;
            console.log(formattedTodayE);

            $("#tgl-akhir").val(formattedTodayE).trigger("change");




            dataTableReload();
            // dataTablePreviewReload();
            // startCalc();
        });

        function get_total_tambah() {
            let a = document.getElementById('tot_skrg').value;
            let b = document.getElementById('txtinput_carton').value;
            let hasil = document.form.txt_simulasi.value;
            hasil = (parseInt(a)) + (parseInt(b));
            document.form.txt_simulasi.value = Math.floor(hasil);
        }

        function gettot_karton() {
            let cbopo = document.form.cbopo.value;
            $.ajax({
                url: '{{ route('show_tot') }}',
                method: 'get',
                data: {
                    cbopo: cbopo
                },
                dataType: 'json',
                success: function(response) {
                    document.getElementById('tot_skrg').value = response.tot_skrg;
                },
                error: function(request, status, error) {
                    alert(request.responseText);
                },
            });
        };


        function dataTableReload() {
            datatable.ajax.reload();
        }

        let datatable = $("#datatable").DataTable({
            ordering: false,
            processing: true,
            serverSide: true,
            paging: true,
            searching: true,
            ajax: {
                url: '{{ route('master-karton') }}',
                data: function(d) {
                    d.dateFrom = $('#tgl-awal').val();
                    d.dateTo = $('#tgl-akhir').val();
                },
            },
            columns: [{
                    data: 'tgl_shipment_fix'

                }, {
                    data: 'po'
                },
                {
                    data: 'tot_carton'
                },
            ],
            columnDefs: [{
                "className": "dt-center",
                "targets": "_all"
            }, ]


        }, );
    </script>
@endsection
