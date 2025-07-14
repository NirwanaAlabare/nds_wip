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

        <div class="card-header text-start">
            <h5 class="card-title fw-bold mb-1">
                <i class="fas fa-list"></i> Detail Packing List
            </h5>
        </div>

        <div class="card-body pb-0">
            <div class="row mb-3">
                <div class="col-md-3">
                    <div class="form-group">
                        <label><small><b>Tgl Dok :</b></small></label>
                        <input type="text" class="form-control form-control-sm border-primary" id="txttgl_dok"
                            name="txttgl_dok" value="{{ $tgl_dok_fix }}" readonly>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label><small><b>No. Packing List :</b></small></label>
                        <input type="text" class="form-control form-control-sm border-primary" id="txtno_inv"
                            name="txtno_inv" value="{{ $no_invoice }}" readonly>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label><small><b>Buyer :</b></small></label>
                        <input type="text" class="form-control form-control-sm border-primary" id="txtbuyer"
                            name="txtbuyer" value="{{ $buyer }}" readonly>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label><small><b>Style :</b></small></label>
                        <input type="text" class="form-control form-control-sm border-primary" id="txtstyle"
                            name="txtstyle" value="{{ $styleno }}" readonly>
                    </div>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-3">
                    <div class="form-group">
                        <label><small><b>Color :</b></small></label>
                        <input type="text" class="form-control form-control-sm border-primary" id="txtcolor"
                            name="txtcolor" value="{{ $color }}" readonly>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label><small><b>ID Item :</b></small></label>
                        <input type="text" class="form-control form-control-sm border-primary" id="txtid_item"
                            name="txtid_item" value="{{ $id_item }}" readonly>
                        <input type="hidden" class="form-control form-control-sm border-primary" id="txtid_jo"
                            name="txtid_jo" value="{{ $id_jo }}" readonly>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label><small><b>Jml Lot :</b></small></label>
                        <input type="text" class="form-control form-control-sm border-primary" id="txtjml_lot"
                            name="txtjml_lot" value="{{ $jml_lot }}" readonly>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label><small><b>Jml Roll :</b></small></label>
                        <input type="text" class="form-control form-control-sm border-primary" id="txtjml_roll"
                            name="txtjml_roll" value="{{ $jml_roll }}" readonly>
                    </div>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-6">
                    <div class="form-group">
                        <label><small><b>Group Inspect :</b></small></label>
                        <select class="form-control form-control-sm select2bs4" id="cbo_group_def" name="cbo_group_def"
                            style="width: 100%; font-size: 0.875rem;" required>
                            <option selected="selected" value="" disabled="true">Pilih Group Inspect
                            </option>
                            @foreach ($data_group as $dg)
                                <option value="{{ $dg->isi }}">
                                    {{ $dg->tampil }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label><small><b>Cek Inspect :</b></small></label>
                        <div class="input-group input-group-sm">
                            <input type="number" class="form-control border-primary" id="txtcek_inspect"
                                name="txtcek_inspect" min="0" max="100" value = "10">
                            <span class="input-group-text">%</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label><small><b>Notes :</b></small></label>
                        <input type="text" class="form-control form-control-sm border-primary" id="txtstyle"
                            name="txtstyle" value="{{ $type_pch }}" readonly>
                    </div>
                </div>
            </div>
        </div>

        <div class="card-footer">
            <button type="submit" class="btn btn-primary w-100">
                <i class="fas fa-cogs fa-spin"></i> Calculate
            </button>
        </div>
    </div>



    <div class="card card-sb">
        <div class="card-header text-start">
            <h5 class="card-title fw-bold mb-1">
                <i class="fas fa-scroll"></i> Detail Roll
            </h5>
        </div>


        <div class="card-body">
            <div class="table-responsive">
                <table id="datatable" class="table table-bordered w-100 text-nowrap">
                    <thead class="bg-sb">
                        <tr style="text-align:center; vertical-align:middle">
                            <th scope="col">Lot</th>
                            <th scope="col">Jml Roll Cek</th>
                            <th scope="col">Cek Inspect</th>
                            <th scope="col">Proses</th>
                            <th scope="col">Shipment Point</th>
                            <th scope="col">Max Shipment Point</th>
                            <th scope="col">Result</th>
                            <th scope="col">Act</th>
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
        const collapseBody = document.getElementById('collapseBody');
        const collapseIcon = document.getElementById('collapseIcon');

        collapseBody.addEventListener('show.bs.collapse', function() {
            collapseIcon.classList.add('rotated');
        });

        collapseBody.addEventListener('hide.bs.collapse', function() {
            collapseIcon.classList.remove('rotated');
        });
    </script>

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
            width: 'resolve' // Ensures it respects the 100% width from inline style or Bootstrap
        });
        // Now set height and font-size on the Select2 container after init
        $('.select2-container--bootstrap4 .select2-selection--single').css({
            'height': '30px', // your desired height
            'font-size': '12px', // your desired font size
            'line-height': '30px' // vertically center text
        });

        function notif() {
            alert("Maaf, Fitur belum tersedia!");
        }


        let datatable = $("#datatable").DataTable({
            ordering: false,
            processing: true,
            serverSide: false,
            paging: true,
            searching: true,
            scrollY: true,
            scrollX: true,
            scrollCollapse: false,
            fixedColumns: {
                leftColumns: 1
            },
            ajax: {
                url: '{{ route('qc_inspect_proses_packing_list') }}',
                data: function(d) {
                    d.dateFrom = $('#tgl-awal').val();
                    d.dateTo = $('#tgl-akhir').val();
                },
            },
            columns: [{
                    data: null,
                    orderable: false,
                    searchable: false,
                    render: function(data, type, row) {
                        return `
                    <a class="btn btn-outline-primary position-relative btn-sm" href="{{ route('qc_inspect_proses_packing_list_det') }}/` +
                            data.id_lok_in_material + `" title="Detail" target="_blank">
                        Detail
                    </a>`;
                    }
                },
                {
                    data: 'tgl_dok_fix'
                },
                {
                    data: 'no_invoice'
                },
                {
                    data: 'supplier'
                },
                {
                    data: 'buyer'
                },
                {
                    data: 'styleno'
                },
                {
                    data: 'color'
                },
                {
                    data: 'id_item'
                },
                {
                    data: 'jml_lot'
                },
                {
                    data: 'jml_roll'
                },
                {
                    data: 'type_pch'
                },
            ],
            initComplete: function() {
                this.api().columns().every(function() {
                    var column = this;
                    $('input', this.header()).on('keyup change clear', function() {
                        if (column.search() !== this.value) {
                            column.search(this.value).draw();
                        }
                    });
                });
            }
        });
    </script>
@endsection
