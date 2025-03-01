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
    {{-- Create Part Group --}}
    <form action="{{ route('store-part') }}" method="post" id="store-part" onsubmit="submitPartForm(this, event)">
        @csrf
        <div class="card card-sb collapsed-card" id="create-part-card">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="card-title fw-bold">
                        <i class="fa fa-plus fa-sm"></i> Tambah Part Group
                    </h5>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i>
                        </button>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-6 col-md-3">
                        <div class="mb-1">
                            <div class="form-group">
                                <label class="form-label">No. WS</small></label>
                                <select class="form-control select2bs4" id="ws_id" name="ws_id" style="width: 100%;">
                                    <option selected="selected" value="">Pilih WS</option>
                                    @foreach ($orders as $order)
                                        <option value="{{ $order->id }}">
                                            {{ $order->kpno }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="mb-1">
                            <label class="form-label"><small>Buyer</small></label>
                            <input type="text" class="form-control" id="buyer" name="buyer" readonly>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="mb-1">
                            <label class="form-label">Style</label>
                            <input type="text" class="form-control" id="style" name="style" readonly>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="mb-1">
                            <div class="form-group">
                                <label class="form-label">Panel</small></label>
                                <select class="form-control select2bs4" id="panel" name="panel" style="width: 100%;" >
                                    <option selected="selected" value="">Pilih Panel</option>
                                    {{-- select 2 option --}}
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <input type="hidden" class="form-control" id="ws" name="ws" readonly>
                    <div class="col-12 col-md-12">
                        <div class="mb-1">
                            <div class="form-group">
                                <label class="form-label">Color</small></label>
                                <input type="text" class="form-control" name="color" id="color" readonly>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-md-12" id="parts-section">
                        <div class="row">
                            <div class="col-3">
                                <label class="form-label">Part</label>
                                <select class="form-control select2bs4" name="part_details[0]" id="part_details_0">
                                    <option value="">Pilih Part</option>
                                    @foreach ($masterParts as $masterPart)
                                        <option value="{{ $masterPart->id }}" data-index="0">{{ $masterPart->nama_part }} - {{ $masterPart->bag }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-3">
                                <label class="form-label">Cons</label>
                                <div class="d-flex mb-3">
                                    <div style="width: 50%;">
                                        <input type="number" class="form-control" style="border-radius: 3px 0 0 3px;" name="cons[0]" id="cons_0" step="0.001">
                                    </div>
                                    <div style="width: 50%;">
                                        <select class="form-select" style="border-radius: 0 3px 3px 0;" name="cons_unit[0]" id="cons_unit_0">
                                            <option value="meter">METER</option>
                                            <option value="yard">YARD</option>
                                            <option value="kgm">KGM</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="col-3">
                                <label class="form-label">Tujuan</label>
                                <select class="form-control select2bs4" style="border-radius: 0 3px 3px 0;" name="tujuan[0]" id="tujuan_0">
                                    <option value="">Pilih Tujuan</option>
                                    @foreach ($masterTujuan as $tujuan)
                                        <option value="{{ $tujuan->id }}">{{ $tujuan->tujuan }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-3">
                                <label class="form-label">Proses</label>
                                <select class="form-control select2bs4" style="border-radius: 0 3px 3px 0;" name="proses[0]" id="proses_0" data-index="0" onchange="changeTujuan(this)">
                                    <option value="">Pilih Proses</option>
                                    @foreach ($masterSecondary as $secondary)
                                        <option value="{{ $secondary->id }}" data-tujuan="{{ $secondary->id_tujuan }}">{{ $secondary->proses }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="mt-3">
                    <button type="button" class="btn btn-sb-secondary fw-bold" onclick="addNewPart()">
                        <i class="far fa-plus-square"></i> Tambah Part
                    </button>
                    <input type="hidden" class="form-control" id="jumlah_part_detail" name="jumlah_part_detail" value="1" readonly>
                </div>
            </div>
            <div class="card-footer border-top">
                <div class="row justify-content-end">
                    <div class="col-auto">
                        <a href="{{ route('part') }}" class="btn btn-danger btn btn-block fw-bold" id="cancel-button"><i class="fa fa-close"></i> BATAL</a>
                    </div>
                    <div class="col-auto">
                        <button type="submit" class="btn btn-success btn btn-block fw-bold" id="submit-button"><i class="fa fa-save"></i> SIMPAN</button>
                    </div>
                </div>
            </div>
        </div>
    </form>

    {{-- Part Group Data --}}
    <div class="card">
        <div class="card-header bg-sb text-light">
            <h5 class="card-title fw-bold mb-0"><i class="fas fa-th fa-sm"></i> Part Group</h5>
        </div>
        <div class="card-body">
            <div class="d-flex flex-wrap justify-content-between align-items-end">
                <div class="d-flex flex-wrap align-items-end gap-3 mb-3">
                    <div>
                        <label class="form-label"><small>Dari</small></label>
                        <input type="date" class="form-control form-control-sm" id="tgl-awal" name="tgl_awal" onchange="datatablePartReload()">
                    </div>
                    <div>
                        <label class="form-label"><small>Sampai</small></label>
                        <input type="date" class="form-control form-control-sm" id="tgl-akhir" name="tgl_akhir" value="{{ date('Y-m-d') }}" onchange="datatablePartReload()">
                    </div>
                    <div>
                        <button class="btn btn-primary btn-sm" onclick="datatablePartReload()"><i class="fa fa-search"></i></button>
                    </div>
                </div>
                <a  class="btn btn-sb btn-sm mb-3" onclick="goToCreatePart()">
                    <i class="fas fa-plus"></i>
                    Baru
                </a>
            </div>
            <div class="table-responsive">
                <table id="datatable-part" class="table table-bordered table-sm w-100">
                    <thead>
                        <tr>
                            <th class="align-bottom">Action</th>
                            <th>Kode Part</th>
                            <th>No. WS</th>
                            <th>Buyer</th>
                            <th>Style</th>
                            <th>Color</th>
                            <th>Panel</th>
                            <th>Part</th>
                            <th>Total Form</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>

    {{-- Part Group Detail Data --}}
    <div class="modal fade" id="detailPartModal" tabindex="-1" aria-labelledby="detailPartLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header bg-sb text-light">
                    <h1 class="modal-title fs-5" id="detailPartLabel"><i class="fa fa-search fa-sm"></i> Detail Part Group</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row align-items-end">
                        <input type="hidden" name="detail_id" id="detail_id" onchange="dataTablePartFormReload()">
                        <div class="col-md-6 col-lg-4">
                            <div class="mb-3">
                                <label class="form-label">No. WS</label>
                                <input type="text" class="form-control" name="detail_act_costing_ws" id="detail_act_costing_ws" value="" readonly>
                            </div>
                        </div>
                        <div class="col-md-6 col-lg-4">
                            <div class="mb-3">
                                <label class="form-label">Style</label>
                                <input type="text" class="form-control" name="detail_style" id="detail_style" value="" readonly>
                            </div>
                        </div>
                        <div class="col-md-6 col-lg-4">
                            <div class="mb-3">
                                <label class="form-label">Color</label>
                                <input type="text" class="form-control" name="detail_color" id="detail_color" value="" readonly>
                            </div>
                        </div>
                        <div class="col-md-6 col-lg-4">
                            <div class="mb-3">
                                <label class="form-label">Panel</label>
                                <input type="text" class="form-control" name="detail_panel" id="detail_panel" value="" readonly>
                            </div>
                        </div>
                        <div class="col-md-6 col-lg-4">
                            <div class="mb-3">
                                <label class="form-label">Part</label>
                                <input type="text" class="form-control" name="detail_part" id="detail_part_details" value="" readonly>
                            </div>
                        </div>
                        <div class="col-md-6 col-lg-4">
                            <div class="row">
                                {{-- <div class="col-md-6"> --}}
                                <div class="col-md-12">
                                    <div class="mb-3">
                                        <button class="btn btn-primary btn-block fw-bold" onclick="reorderStockerNumbering()"><i class="fa-solid fa-arrow-up-wide-short"></i> URUTKAN ULANG</button>
                                    </div>
                                </div>
                                <div class="col-md-6 d-none">
                                    <div class="mb-3">
                                        <button class="btn btn-info btn-block fw-bold" onclick="generateFullStockerNumbering()"><i class="fa-solid fa-list-check"></i> GENERATE SEMUA</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="table-responsive mb-3">
                                <table class="table table-bordered table-sm w-100" id="datatable-part-form">
                                    <thead>
                                        <tr>
                                            <th>Action</th>
                                            <th>Tanggal</th>
                                            <th>No. Form</th>
                                            <th>Meja</th>
                                            <th>No. Cut</th>
                                            <th>Style</th>
                                            <th>Color</th>
                                            <th>Part</th>
                                            <th>Lembar</th>
                                            <th>Size Ratio</th>
                                            <th>No. Marker</th>
                                            <th>No. WS</th>
                                            <th>Buyer</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" data-bs-dismiss="modal"><i class="fa fa-times"></i> Tutup</button>
                </div>
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
    <script>
        // Initial Function
        document.addEventListener("DOMContentLoaded", () => {
            // Set Filter to 1 Week Ago
            let oneWeeksBefore = new Date(new Date().setDate(new Date().getDate() - 7));
            let oneWeeksBeforeDate = ("0" + oneWeeksBefore.getDate()).slice(-2);
            let oneWeeksBeforeMonth = ("0" + (oneWeeksBefore.getMonth() + 1)).slice(-2);
            let oneWeeksBeforeYear = oneWeeksBefore.getFullYear();
            let oneWeeksBeforeFull = oneWeeksBeforeYear + '-' + oneWeeksBeforeMonth + '-' + oneWeeksBeforeDate;

            $("#tgl-awal").val(oneWeeksBeforeFull).trigger("change");

            window.addEventListener("focus", () => {
                $('#datatable').DataTable().ajax.reload(null, false);
            });
        });

        // Part Datatable
        let datatablePart = $("#datatable-part").DataTable({
            ordering: false,
            processing: true,
            serverSide: true,
            ajax: {
                url: '{{ route('part') }}',
                data: function(d) {
                    d.id = $("detail_id").val();
                }
            },
            columns: [
                {
                    data: 'id'
                },
                {
                    data: 'kode',
                },
                {
                    data: 'act_costing_ws',
                },
                {
                    data: 'buyer'
                },
                {
                    data: 'style',
                },
                {
                    data: 'color'
                },
                {
                    data: 'panel'
                },
                {
                    data: 'part_details',
                    searchable: false
                },
                {
                    data: 'total_form',
                    searchable: false
                },
            ],
            columnDefs: [
                {
                    // Act Column
                    targets: [0],
                    render: (data, type, row, meta) => {
                        return `
                            <div class='d-flex gap-1 justify-content-center'>
                                <buton type="button" onclick='showPartForm(` + JSON.stringify(row) + `)' class='btn btn-primary btn-sm'>
                                    <i class='fa fa-search'></i>
                                </buton>
                                <a href='{{ route('manage-part-secondary') }}/` + row['id'] + `' class='btn btn-info btn-sm'>
                                    <i class='fa fa-plus-circle'></i>
                                </a>
                                <a href='{{ route('manage-part-form') }}/` + row['id'] + `' class='btn btn-success btn-sm'>
                                    <i class="fa-solid fa-file-circle-plus"></i>
                                </a>
                                <a class='btn btn-danger btn-sm' data='` + JSON.stringify(row) + `' data-url='{{ route('destroy-part') }}/` + row['id'] + `' onclick='deleteData(this)'>
                                    <i class='fa fa-trash'></i>
                                </a>
                            </div>
                        `;
                    }
                },
                {
                    // No. Meja Column
                    targets: [5],
                    render: (data, type, row, meta) => {
                        var color = '#2b2f3a';
                        if (row.sisa == '0') {
                            color = '#087521';
                        } else {
                            color = '#2b2f3a';
                        }
                        return '<span style="font-weight: 600; color:' + color + '">'+data.replace(/,/g, ' ||')+'</span>';
                    }
                },
                {
                    // All Column Colorization
                    targets: '_all',
                    className: 'text-nowrap',
                    render: (data, type, row, meta) => {
                        var color = '#2b2f3a';
                        if (row.sisa == '0') {
                            color = '#087521';
                        } else {
                            color = '#2b2f3a';
                        }
                        return '<span style="font-weight: 600; color:' + color + '">' + data + '</span>';
                    }
                },
            ],
        });

        // Part Datatable Reload
        function datatablePartReload() {
            datatablePart.ajax.reload()
        }

        // Part Datatable Header Column Filter
        $('#datatable-part thead tr').clone(true).appendTo('#datatable-part thead');
        $('#datatable-part thead tr:eq(1) th').each(function(i) {
            if (i == 1 || i == 2 || i == 3 || i == 4 || i == 5 || i == 6 || i == 7 || i == 8) {
                var title = $(this).text();
                $(this).html('<input type="text" class="form-control form-control-sm" />');

                $('input', this).on('keyup change', function() {
                    if (datatablePart.column(i).search() !== this.value) {
                        datatablePart
                            .column(i)
                            .search(this.value)
                            .draw();
                    }
                });
            } else {
                $(this).empty();
            }
        });

        // Open Detail Part Modal
        function showPartForm(data) {
            for (let key in data) {
                console.log(document.getElementById('detail_' + key));
                if (document.getElementById('detail_' + key)) {
                    $('#detail_' + key).val(data[key]).trigger("change");
                    document.getElementById('detail_' + key).setAttribute('value', data[key]);

                    if (document.getElementById('detail_' + key).classList.contains('select2bs4') || document
                        .getElementById('detail_' + key).classList.contains('select2')) {
                        $('#detail_' + key).val(data[key]).trigger('change.select2');
                    }
                }
            }

            $("#detailPartModal").modal('show');
        };

        // Part Detail Form Datatable
        let datatablePartForm = $("#datatable-part-form").DataTable({
            ordering: false,
            processing: true,
            serverSide: true,
            ajax: {
                url: '{{ route('show-part-form') }}',
                dataType: 'json',
                dataSrc: 'data',
                data: function(d) {
                    d.id = $('#detail_id').val();
                },
            },
            columns: [
                {
                    data: null,
                    searchable: false
                },
                {
                    data: 'tanggal_selesai',
                    searchable: false
                },
                {
                    data: 'no_form'
                },
                {
                    data: 'nama_meja'
                },
                {
                    data: 'no_cut',
                },
                {
                    data: 'style'
                },
                {
                    data: 'color'
                },
                {
                    data: 'part_details'
                },
                {
                    data: 'total_lembar',
                    searchable: false
                },
                {
                    data: 'marker_details',
                    searchable: false
                },
                {
                    data: 'id_marker'
                },
                {
                    data: 'act_costing_ws'
                },
                {
                    data: 'buyer'
                },
            ],
            columnDefs: [
                // Act Column
                {
                    targets: [0],
                    render: (data, type, row, meta) => {
                        return `
                            <div class='d-flex gap-1 justify-content-center'>
                                <a class='btn btn-primary btn-sm' href='{{ route('show-stocker') }}/` + row.form_cut_id + `' data-bs-toggle='tooltip' target='_blank'>
                                    <i class='fa fa-search-plus'></i>
                                </a>
                            </div>
                        `;
                    }
                },
                // No. Meja Column
                {
                    targets: [3],
                    render: (data, type, row, meta) => data ? data.toUpperCase() : "-"
                },
                // Marker Hyperlink
                {
                    targets: [10],
                    render: (data, type, row, meta) => {
                        return `
                            <a href='{{ route('edit-marker') }}/ `+row.marker_id+`' target='_blank'>`+data+`</a>
                        `;
                    }
                },
                // No Wrap
                {
                    targets: '_all',
                    className: 'text-nowrap',
                }
            ]
        });

        // Datatable Part Detail Form Header Column Filter
        $('#datatable-part-form thead tr').clone(true).appendTo('#datatable-part-form thead');
        $('#datatable-part-form thead tr:eq(1) th').each(function(i) {
            if (i == 1 || i == 2 || i == 3 || i == 4 || i == 5 || i == 6 || i == 7 || i == 8 || i == 10 || i == 11 || i == 12) {
                var title = $(this).text();
                $(this).html('<input type="text" class="form-control form-control-sm" style="width:100%"/>');

                $('input', this).on('keyup change', function() {
                    if (datatablePartForm.column(i).search() !== this.value) {
                        datatablePartForm
                            .column(i)
                            .search(this.value)
                            .draw();
                    }
                });
            } else {
                $(this).empty();
            }
        });

        // Datatable Part Detail Form Reload
        function dataTablePartFormReload() {
            datatablePartForm.ajax.reload();
        }

        // Reorder Stocker & Numbering
        function reorderStockerNumbering() {
            Swal.fire({
                title: 'Please Wait...',
                html: 'Reordering Data...',
                didOpen: () => {
                    Swal.showLoading()
                },
                allowOutsideClick: false,
            });

            $.ajax({
                url: '{{ route('reorder-stocker-numbering') }}',
                type: 'post',
                data: {
                    id : $("#detail_id").val()
                },
                success: function (res) {
                    console.log(res);

                    dataTablePartFormReload();

                    swal.close();
                },
                error: function (jqXHR) {
                    console.log(jqXHR);
                }
            });
        }

        // Generate Full Stocker & Numbering
        function generateFullStockerNumbering() {
            Swal.fire({
                title: 'Please Wait...',
                html: 'Generating Data...',
                didOpen: () => {
                    Swal.showLoading()
                },
                allowOutsideClick: false,
            });

            $.ajax({
                url: '{{ route('full-generate-numbering') }}',
                type: 'post',
                data: {
                    id : $("#detail_id").val()
                },
                success: function (res) {
                    console.log(res);

                    dataTablePartFormReload();

                    swal.close();
                },
                error: function (jqXHR) {
                    console.log(jqXHR);
                }
            });
        }

        // Create Part JS
        function goToCreatePart() {
            $("#create-part-card").CardWidget('expand');

            // $('html, body').animate({
            //     scrollTop: $("#create-part-card").offset().top
            // }, 500);
        }

        // Global Variable
        var sumCutQty = null;
        var totalRatio = null;

        var partSection = null;
        var partOptions = null;
        var tujuanOptions = null;
        var prosesOptions = null;
        var selectedPartArray = [];

        var jumlahPartDetail = null;

        document.getElementById('loading').classList.remove("d-none");

        // Initial Window On Load Event
        $(document).ready(async function () {
            //Reset Form
            if (document.getElementById('store-part')) {
                document.getElementById('store-part').reset();

                $(".select2").val('').trigger('change');
                $(".select2bs4").val('').trigger('change');
                $(".select2bs4custom").val('').trigger('change');

                $("#ws_id").val(null).trigger("change");
                $('#part_details').val(null).trigger('change');

                await getMasterParts();
                await getTujuan();
                await getProses();

                partSection = document.getElementById('parts-section');

                jumlahPartDetail = document.getElementById('jumlah_part_detail');
                jumlahPartDetail.value = 1;
            }

            // Select2 Prevent Step-Jump Input ( Step = WS -> Panel )
            $("#panel").prop("disabled", true);

            document.getElementById('loading').classList.add("d-none");
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

        // Step One (WS) on change event
        $('#ws_id').on('change', async function(e) {
            document.getElementById("loading").classList.remove("d-none");

            if (this.value) {
                await updateOrderInfo();
                await updatePanelList();
            }

            document.getElementById("loading").classList.add("d-none");
        });

        // Update Order Information Based on Order WS and Order Color
        function updateOrderInfo() {
            return $.ajax({
                url: '{{ route("get-part-order") }}',
                type: 'get',
                data: {
                    act_costing_id: $('#ws_id').val(),
                },
                dataType: 'json',
                success: function (res) {
                    if (res) {
                        document.getElementById('ws').value = res.kpno;
                        document.getElementById('buyer').value = res.buyer;
                        document.getElementById('style').value = res.styleno;
                        document.getElementById('color').value = res.colors;
                    }
                },
            });
        }

        // Update Panel Select Option Based on Order WS and Color WS
        function updatePanelList() {
            document.getElementById('panel').value = null;
            return $.ajax({
                url: '{{ route("get-part-panels") }}',
                type: 'get',
                data: {
                    act_costing_id: $('#ws_id').val(),
                    color: $('#color').val(),
                },
                success: function (res) {
                    if (res) {
                        // Update this step
                        document.getElementById('panel').innerHTML = res;

                        // Open this step
                        $("#panel").prop("disabled", false);
                    }
                },
            });
        }

        function getMasterParts() {
            return $.ajax({
                url: '{{ route("get-master-parts") }}',
                type: 'get',
                success: function (res) {
                    if (res) {
                        partOptions = res;
                    }
                },
                error: function (jqXHR) {
                    console.log(jqXHR);
                }
            });
        }

        function getTujuan() {
            return $.ajax({
                url: '{{ route("get-master-tujuan") }}',
                type: 'get',
                success: function (res) {
                    if (res) {
                        tujuanOptions = res;
                    }
                },
                error: function (jqXHR) {
                    console.log(jqXHR);
                }
            });
        }

        function getProses() {
            return $.ajax({
                url: '{{ route("get-master-secondary") }}',
                type: 'get',
                success: function (res) {
                    if (res) {
                        prosesOptions = res;
                    }
                },
                error: function (jqXHR) {
                    console.log(jqXHR);
                }
            });
        }

        function changeTujuan(element) {
            let thisIndex = element.getAttribute('data-index');
            let thisSelected = element.options[element.selectedIndex];
            let thisTujuan = document.getElementById('tujuan_'+thisIndex);

            console.log(thisTujuan);

            if (thisTujuan.value != thisSelected.getAttribute('data-tujuan')) {
                $('#tujuan_'+thisIndex).val(thisSelected.getAttribute('data-tujuan')).trigger("change");
            }
        }

        function addNewPart() {
            if (jumlahPartDetail) {
                // row
                let divRow = document.createElement('div');
                divRow.setAttribute('class', 'row');

                // 1
                let divCol1 = document.createElement('div');
                divCol1.setAttribute('class', 'col-3');

                let label1 = document.createElement('label');
                label1.setAttribute('class', 'form-label');
                label1.innerHTML = 'Part';

                let partDetail = document.createElement("select");
                partDetail.setAttribute('class', 'form-select select2bs4custom');
                partDetail.setAttribute('name', 'part_details['+jumlahPartDetail.value+']');
                partDetail.setAttribute('id', 'part_details_'+jumlahPartDetail.value);
                partDetail.innerHTML = partOptions;

                divCol1.appendChild(label1);
                divCol1.appendChild(partDetail);

                // 2
                let divCol2 = document.createElement('div');
                divCol2.setAttribute('class', 'col-3');

                divCol2.innerHTML= `
                    <label class="form-label">Cons</small></label>
                    <div class="d-flex mb-3">
                        <div style="width: 50%;">
                            <input type="number" class="form-control" style="border-radius: 3px 0 0 3px;" name="cons[`+jumlahPartDetail.value+`]" id="cons_`+jumlahPartDetail.value+`" step="0.001">
                        </div>
                        <div style="width: 50%;">
                            <select class="form-select" style="border-radius: 0 3px 3px 0;" name="cons_unit[`+jumlahPartDetail.value+`]" id="cons_unit_`+jumlahPartDetail.value+`">
                                <option value="meter">METER</option>
                                <option value="yard">YARD</option>
                                <option value="kgm">KGM</option>
                            </select>
                        </div>
                    </div>
                `;

                // 3
                let divCol3 = document.createElement('div');
                divCol3.setAttribute('class', 'col-3');

                let label3 = document.createElement('label');
                label3.setAttribute('class', 'form-label');
                label3.innerHTML = 'Tujuan</small>';

                let tujuan = document.createElement("select");
                tujuan.setAttribute('class', 'form-select select2bs4custom');
                tujuan.setAttribute('name', 'tujuan['+jumlahPartDetail.value+']');
                tujuan.setAttribute('id', 'tujuan_'+jumlahPartDetail.value);
                tujuan.innerHTML = tujuanOptions;

                divCol3.appendChild(label3);
                divCol3.appendChild(tujuan);

                // 4
                let divCol4 = document.createElement('div');
                divCol4.setAttribute('class', 'col-3');

                let label4 = document.createElement('label');
                label4.setAttribute('class', 'form-label');
                label4.innerHTML = 'Proses</small>';

                let proses = document.createElement("select");
                proses.setAttribute('class', 'form-select select2bs4custom');
                proses.setAttribute('name', 'proses['+jumlahPartDetail.value+']');
                proses.setAttribute('id', 'proses_'+jumlahPartDetail.value);
                proses.setAttribute('data-index', jumlahPartDetail.value);
                proses.setAttribute('onchange', 'changeTujuan(this)');
                proses.innerHTML = prosesOptions;

                divCol4.appendChild(label4);
                divCol4.appendChild(proses);

                // row
                divRow.appendChild(divCol1);
                divRow.appendChild(divCol2);
                divRow.appendChild(divCol3);
                divRow.appendChild(divCol4);

                partSection.appendChild(divRow);

                $('#part_details_'+jumlahPartDetail.value).select2({
                    theme: 'bootstrap4',
                });
                $('#tujuan_'+jumlahPartDetail.value).select2({
                    theme: 'bootstrap4',
                });
                $('#proses_'+jumlahPartDetail.value).select2({
                    theme: 'bootstrap4',
                });

                jumlahPartDetail.value++;
            } else {
                Swal.fire({
                    title: "Warning",
                    text: "Harap pilih No. WS",
                    icon: "warning"
                });
            }
        }

        // Prevent Form Submit When Pressing Enter
        document.getElementById("store-part").onkeypress = function(e) {
            var key = e.charCode || e.keyCode || 0;
            if (key == 13) {
                e.preventDefault();
            }
        }

        // Submit Part Form
        function submitPartForm(e, evt) {
            document.getElementById('submit-button').setAttribute('disabled', true);

            evt.preventDefault();

            clearModified();

            $.ajax({
                url: e.getAttribute('action'),
                type: e.getAttribute('method'),
                data: new FormData(e),
                processData: false,
                contentType: false,
                success: async function(res) {
                    document.getElementById('submit-button').removeAttribute('disabled');

                    // Success Response

                    if (res.status == 200) {
                        // When Actually Success :

                        // Reset This Form
                        e.reset();

                        // Success Alert
                        Swal.fire({
                            icon: 'success',
                            title: 'Data Part Berhasil disimpan',
                            text: res.message,
                            showCancelButton: false,
                            showConfirmButton: true,
                            confirmButtonText: 'Oke',
                            timer: 5000,
                            timerProgressBar: true
                        }).then(() => {
                            if (res.redirect != '') {
                                if (res.redirect != 'reload') {
                                    location.reload();

                                    window.open(res.redirect);
                                } else {
                                    location.reload();
                                }
                            }
                        })
                    } else {
                        // When Actually Error :

                        // Error Alert
                        iziToast.error({
                            title: 'Error',
                            message: res.message,
                            position: 'topCenter'
                        });
                    }

                    // If There Are Some Additional Error
                    if (Object.keys(res.additional).length > 0 ) {
                        for (let key in res.additional) {
                            if (document.getElementById(key)) {
                                document.getElementById(key).classList.add('is-invalid');

                                if (res.additional[key].hasOwnProperty('message')) {
                                    document.getElementById(key+'_error').classList.remove('d-none');
                                    document.getElementById(key+'_error').innerHTML = res.additional[key]['message'];
                                }

                                if (res.additional[key].hasOwnProperty('value')) {
                                    document.getElementById(key).value = res.additional[key]['value'];
                                }

                                modified.push(
                                    [key, '.classList', '.remove(', "'is-invalid')"],
                                    [key+'_error', '.classList', '.add(', "'d-none')"],
                                    [key+'_error', '.innerHTML = ', "''"],
                                )
                            }
                        }
                    }
                }, error: function (jqXHR) {
                    document.getElementById('submit-button').removeAttribute('disabled');

                    // Error Response

                    let res = jqXHR.responseJSON;
                    let message = '';
                    let i = 0;

                    for (let key in res.errors) {
                        message = res.errors[key];
                        document.getElementById(key).classList.add('is-invalid');
                        modified.push(
                            [key, '.classList', '.remove(', "'is-invalid')"],
                        )

                        if (i == 0) {
                            document.getElementById(key).focus();
                            i++;
                        }
                    };
                }
            });
        }

        // Reset Step
        async function resetStep() {
            $('#part_details').val(null).trigger('change');
            await $("#ws_id").val(null).trigger("change");
            await $("#panel").val(null).trigger("change");
            await $("#panel").prop("disabled", true);
        }
    </script>
@endsection
