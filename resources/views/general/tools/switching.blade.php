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
        input[type=file]::file-selector-button {
            margin-right: 20px;
            border: none;
            background: #084cdf;
            padding: 10px 20px;
            border-radius: 10px;
            color: #fff;
            cursor: pointer;
            transition: background .2s ease-in-out;
        }

        input[type=file]::file-selector-button:hover {
            background: #0d45a5;
        }

        .drop-container {
            position: relative;
            display: flex;
            gap: 10px;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            height: 200px;
            padding: 20px;
            border-radius: 10px;
            border: 2px dashed #555;
            color: #444;
            cursor: pointer;
            transition: background .2s ease-in-out, border .2s ease-in-out;
        }

        .drop-container:hover {
            background: #eee;
            border-color: #111;
        }

        .drop-container:hover .drop-title {
            color: #222;
        }

        .drop-title {
            color: #444;
            font-size: 20px;
            font-weight: bold;
            text-align: center;
            transition: color .2s ease-in-out;
        }
    </style>
@endsection

@section('content')

    <div class="card">
        <div class="mx-3 my-3">
            <h5 class="card-title fw-bold text-sb text-center"><i class="fa-solid fa-arrow-right-arrow-left"></i> Switching</h5>
        </div>
        <div class="row g-3 mx-2">
            <div class="col-12 col-md-5">
                <div class="card">
                    <div class="card-header bg-sb">
                        <h5 class="card-title text-light text-center">OUT</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Tanggal</label>
                            <input type="date" class="form-control" name="from_tgl_saldo" id="from_tgl_saldo">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">No WS</label>
                            <select class="form-select select2bs4" name="from_no_ws" id="from_no_ws">
                                <option value="">Select No WS</option>
                                @foreach ($wsList as $item)
                                    <option value="{{ $item->ws }}"
                                        data-buyer="{{ $item->buyer }}"
                                        data-styleno="{{ $item->styleno }}"> {{ $item->ws }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Buyer</label>
                            <input type="text" class="form-control" name="from_buyer" id="from_buyer" readonly>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Style</label>
                            <input type="text" class="form-control" name="from_style" id="from_style" readonly>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Color</label>
                            <select class="form-select select2bs4" name="from_color" id="from_color">
                                <option value="">Select Color</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Size</label>
                            <select class="form-select select2bs4" name="from_size" id="from_size">
                                <option value="">Select Size</option>
                            </select>
                        </div>
                        <div id="from_panel_section">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Panel</label>
                                <select class="form-select select2bs4" name="from_panel" id="from_panel">
                                    <option value="">Select Panel</option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold">Part</label>
                                <select class="form-select select2bs4" name="from_part" id="from_part">
                                    <option value="">Select Part</option>
                                </select>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Qty</label>
                            <input type="number" class="form-control" name="from_qty" id="from_qty">
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-2">
                <div class="d-flex flex-column justify-content-center align-items-center h-100">
                    <select class="form-select w-auto mb-3" name="source" id="source">
                        <option value="NDS">NDS</option>
                        <option value="SignalBit">SignalBit</option>
                    </select>
                    <select class="form-select w-auto mb-3" name="type_report" id="type_report">
                        <option value="CUTTING">CUTTING</option>
                        <option value="DC">DC</option>
                        <option value="SEWING">SEWING</option>
                        <option value="PACKING">PACKING</option>
                    </select>
                    <i class="fa-solid fa-arrow-right fa-5x text-sb"></i>
                </div>
            </div>
            <div class="col-12 col-md-5">
                <div class="card">
                    <div class="card-header bg-sb">
                        <h5 class="card-title text-light text-center">IN</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Tanggal</label>
                            <input type="date" class="form-control" name="tgl_saldo" id="tgl_saldo">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">No WS</label>
                            <select class="form-select select2bs4" name="no_ws" id="no_ws">
                                <option value="">Select No WS</option>
                                @foreach ($wsList as $item)
                                    <option value="{{ $item->ws }}"
                                        data-buyer="{{ $item->buyer }}"
                                        data-styleno="{{ $item->styleno }}"> {{ $item->ws }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Buyer</label>
                            <input type="text" class="form-control" name="buyer" id="buyer" readonly>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Style</label>
                            <input type="text" class="form-control" name="style" id="style" readonly>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Color</label>
                            <select class="form-select select2bs4" name="color" id="color">
                                <option value="">Select Color</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Size</label>
                            <select class="form-select select2bs4" name="size" id="size">
                                <option value="">Select Size</option>
                            </select>
                        </div>
                        <div id="panel_section">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Panel</label>
                                <select class="form-select select2bs4" name="panel" id="panel">
                                    <option value="">Select Panel</option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold">Part</label>
                                <select class="form-select select2bs4" name="part" id="part">
                                    <option value="">Select Part</option>
                                </select>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Qty</label>
                            <input type="number" class="form-control" name="qty" id="qty" readonly>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-6 offset-md-3 my-3 text-center">
                <button type="submit" class="btn btn-success w-100 mb-1 fw-bold" id="btnSimpan">
                    <i class="fa fa-save"></i> SIMPAN
                </button>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header bg-sb-secondary">
            <h5 class="card-title">
                <i class="fas fa-list fa-sm"></i> List Data
            </h5>
        </div>
        <div class="card-body">
            <div class="d-flex align-items-end gap-3 mb-3">
                <div>
                    <label class="form-label"><small>Tanggal Awal</small></label>
                    <input type="date" class="form-control form-control-sm" id="tgl-awal" name="tgl_awal" value="{{ date('Y-m-d') }}">
                </div>
                <div>
                    <label class="form-label"><small>Tanggal Akhir</small></label>
                    <input type="date" class="form-control form-control-sm" id="tgl-akhir" name="tgl_akhir" value="{{ date('Y-m-d') }}">
                </div>
                <div>
                    <label class="form-label"><small>Source</small></label>
                    <select name="source_list" id="source_list" class="form-control form-control-sm" style="width: 150px;">
                        <option value="NDS">NDS</option>
                        <option value="SignalBit">SignalBit</option>
                    </select>
                </div>
                <div>
                    <button class="btn btn-primary btn-sm" onclick="listTableReload()"> <i class="fa fa-search"></i> </button>
                </div>
                <div class="ms-auto">
                    <button type="button" class="btn btn-success btn-sm" id="btnExport" onclick="exportDataSwitching()"> <i class="fa fa-download"></i> Export </button>
                    <button type="button" class="btn btn-danger btn-sm" id="btnDeleteSelected" style="display:none;"> <i class="fa fa-trash"></i> Delete </button>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table table-bordered table-sm" id="list-table">
                    <thead>
                        <tr>
                            <th class="text-center">
                                <input type="checkbox" id="check_all">
                            </th>
                            <th>Type Report</th>
                            <th>From Tgl Saldo</th>
                            <th>From No WS</th>
                            <th>From Buyer</th>
                            <th>From Style</th>
                            <th>From Color</th>
                            <th>From Size</th>
                            <th>From Panel</th>
                            <th>From Part</th>
                            <th>From Qty</th>
                            <th>Tgl Saldo</th>
                            <th>No WS</th>
                            <th>Buyer</th>
                            <th>Style</th>
                            <th>Color</th>
                            <th>Size</th>
                            <th>Panel</th>
                            <th>Part</th>
                            <th>Qty</th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
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

    <!-- Select2 -->
    <script src="{{ asset('plugins/select2/js/select2.full.min.js') }}"></script>
    <script>
        $('.select2').select2()
        $('.select2bs4').select2({
            theme: 'bootstrap4'
        })

        loadTypeReport($('#source').val());
        togglePanelPart($('#type_report').val());

        function loadTypeReport(source)
        {
            $('#type_report').html('');

            if (source == 'NDS') {
                $('#type_report').append(`
                    <option value="CUTTING">CUTTING</option>
                    <option value="DC">DC</option>
                `);
            } else if (source == 'SignalBit') {
                $('#type_report').append(`
                    <option value="SEWING">SEWING</option>
                    <option value="PACKING">PACKING</option>
                `);
            }

            $('#type_report').trigger('change.select2');
        }

        $('#source').on('change', function () {
            loadTypeReport($(this).val());
            togglePanelPart($('#type_report').val());
        });

        function togglePanelPart(typeReport)
        {
            if (typeReport == 'CUTTING' || typeReport == 'DC') {
                $('#from_panel_section').show();
                $('#panel_section').show();
            } else {
                $('#from_panel_section').hide();
                $('#panel_section').hide();

                $('#from_panel').val('').trigger('change');
                $('#from_part').val('').trigger('change');

                $('#panel').val('').trigger('change');
                $('#part').val('').trigger('change');
            }
        }

        $('#type_report').on('change', function () {
            togglePanelPart($(this).val());
        });

        // OUT
        $('#from_no_ws').on('change', function () {
            let selected = $(this).find(':selected');

            let buyer = selected.data('buyer');
            let style = selected.data('styleno');
            let ws = $(this).val();

            $('#from_buyer').val(buyer ?? '');
            $('#from_style').val(style ?? '');

            $('#from_color').html('<option value="">Select Color</option>');

            if (ws == '') {
                return;
            }

            $.ajax({
                url: "{{ route('get-data-color-switching') }}",
                type: "POST",
                data: {
                    _token: "{{ csrf_token() }}",
                    ws: ws
                },
                success: function(response) {
                    $.each(response, function(index, item) {
                        $('#from_color').append(`
                            <option value="${item.color}">
                                ${item.color}
                            </option>
                        `);
                    });
                }
            });
        });

        $('#from_color').on('change', function () {

            let ws = $('#from_no_ws').val();
            let color = $(this).val();

            $('#from_size').html('<option value="">Select Size</option>');
            $('#from_panel').html('<option value="">Select Panel</option>');

            if (ws == '' || color == '') {
                return;
            }

            // SIZE
            $.ajax({
                url: "{{ route('get-data-size-switching') }}",
                type: "POST",
                data: {
                    _token: "{{ csrf_token() }}",
                    ws: ws,
                    color: color
                },

                success: function(response) {
                    $.each(response, function(index, item) {
                        $('#from_size').append(`
                            <option value="${item.size}">
                                ${item.size}
                            </option>
                        `);
                    });

                    $('#from_size').trigger('change.select2');
                }
            });

            // PANEL
            $.ajax({
                url: "{{ route('get-data-panel-switching') }}",
                type: "POST",
                data: {
                    _token: "{{ csrf_token() }}",
                    ws: ws,
                    color: color
                },
                success: function(response) {
                    $.each(response, function(index, item) {
                        $('#from_panel').append(`
                            <option value="${item.panel}">
                                ${item.panel}
                            </option>
                        `);
                    });

                    $('#from_panel').trigger('change.select2');
                }
            });

        });

        $('#from_panel').on('change', function () {

            let ws = $('#from_no_ws').val();
            let color = $('#from_color').val();
            let panel = $(this).val();

            $('#from_part').html('<option value="">Select Part</option>');

            if (ws == '' || color == '' || panel == '') {
                return;
            }

            $.ajax({
                url: "{{ route('get-data-part-switching') }}",
                type: "POST",
                data: {
                    _token: "{{ csrf_token() }}",
                    ws: ws,
                    color: color,
                    panel: panel
                },
                success: function(response) {
                    $.each(response, function(index, item) {
                        $('#from_part').append(`
                            <option value="${item.nama_part}">
                                ${item.nama_part}
                            </option>
                        `);
                    });

                    $('#from_part').trigger('change.select2');
                }
            });

        });

        // IN
        $('#no_ws').on('change', function () {
            let selected = $(this).find(':selected');

            let buyer = selected.data('buyer');
            let style = selected.data('styleno');
            let ws = $(this).val();

            $('#buyer').val(buyer ?? '');
            $('#style').val(style ?? '');

            $('#color').html('<option value="">Select Color</option>');

            if (ws == '') {
                return;
            }

            $.ajax({
                url: "{{ route('get-data-color-switching') }}",
                type: "POST",
                data: {
                    _token: "{{ csrf_token() }}",
                    ws: ws
                },
                success: function(response) {
                    $.each(response, function(index, item) {
                        $('#color').append(`
                            <option value="${item.color}">
                                ${item.color}
                            </option>
                        `);
                    });
                }
            });
        });

        $('#color').on('change', function () {

            let ws = $('#no_ws').val();
            let color = $(this).val();

            $('#size').html('<option value="">Select Size</option>');
            $('#panel').html('<option value="">Select Panel</option>');

            if (ws == '' || color == '') {
                return;
            }

            // SIZE
            $.ajax({
                url: "{{ route('get-data-size-switching') }}",
                type: "POST",
                data: {
                    _token: "{{ csrf_token() }}",
                    ws: ws,
                    color: color
                },
                success: function(response) {
                    $.each(response, function(index, item) {
                        $('#size').append(`
                            <option value="${item.size}">
                                ${item.size}
                            </option>
                        `);
                    });

                    $('#size').trigger('change.select2');
                }
            });

            // PANEL
            $.ajax({
                url: "{{ route('get-data-panel-switching') }}",
                type: "POST",
                data: {
                    _token: "{{ csrf_token() }}",
                    ws: ws,
                    color: color
                },
                success: function(response) {
                    $.each(response, function(index, item) {
                        $('#panel').append(`
                            <option value="${item.panel}">
                                ${item.panel}
                            </option>
                        `);
                    });

                    $('#panel').trigger('change.select2');
                }
            });

        });

        $('#panel').on('change', function () {

            let ws = $('#no_ws').val();
            let color = $('#color').val();
            let panel = $(this).val();

            $('#part').html('<option value="">Select Part</option>');

            if (ws == '' || color == '' || panel == '') {
                return;
            }

            $.ajax({
                url: "{{ route('get-data-part-switching') }}",
                type: "POST",
                data: {
                    _token: "{{ csrf_token() }}",
                    ws: ws,
                    color: color,
                    panel: panel
                },
                success: function(response) {
                    $.each(response, function(index, item) {
                        $('#part').append(`
                            <option value="${item.nama_part}">
                                ${item.nama_part}
                            </option>
                        `);
                    });

                    $('#part').trigger('change.select2');
                }
            });

        });

        $("#from_qty").on('keyup', function(){
            $("#qty").val($(this).val());
        })

        let listTable = $("#list-table").DataTable({
            ordering: false,
            processing: true,
            serverSide: true,
            ajax: {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: '{{ route('get-data-switching') }}',
                dataType: 'json',
                dataSrc: 'data',
                scrollY: '400px',
                data: function(d) {
                    d.dateFrom = $('#tgl-awal').val();
                    d.dateTo = $('#tgl-akhir').val();
                    d.source = $('#source_list').val();
                },
            },
            columns: [
                {
                    data: null,
                    className: 'text-center',
                    orderable: false,
                    render: function (data, type, row) {
                        return `
                            <input
                                type="checkbox"
                                class="row-check"
                                value="${row.id}"
                            >
                        `;
                    }
                },
                { data: "type_report" },
                { data: "from_tgl_saldo" },
                { data: "from_no_ws" },
                { data: "from_buyer" },
                { data: "from_style" },
                { data: "from_color" },
                { data: "from_size" },
                { data: 'from_panel' },
                { data: 'from_part' },
                { data: 'from_qty' },
                { data: "tgl_saldo" },
                { data: "no_ws" },
                { data: "buyer" },
                { data: "style" },
                { data: "color" },
                { data: "size" },
                { data: 'panel' },
                { data: 'part' },
                { data: 'qty' },
            ],
            columnDefs: [
                {
                    targets: "_all",
                    className: "text-nowrap",
                    render: (data, type, row, meta) => data
                },
            ]
        });

        togglePanelPartColumns($('#source_list').val());

        function togglePanelPartColumns(typeReport)
        {
            let showPanelPart = typeReport == 'CUTTING' || typeReport == 'DC';

            listTable.column(8).visible(showPanelPart);
            listTable.column(9).visible(showPanelPart);

            listTable.column(17).visible(showPanelPart);
            listTable.column(18).visible(showPanelPart);
        }

        function listTableReload() {
            showLoading();

            listTable.ajax.reload(function () {
                hideLoading();
            });
        }

        $(document).on('click', '#btnSimpan', function () {
            let formData = {
                from_tgl_saldo: $('#from_tgl_saldo').val(),
                from_no_ws: $('#from_no_ws').val(),
                from_buyer: $('#from_buyer').val(),
                from_style: $('#from_style').val(),
                from_color: $('#from_color').val(),
                from_size: $('#from_size').val(),
                from_panel: $('#from_panel').val(),
                from_part: $('#from_part').val(),
                from_qty: $('#from_qty').val(),
                source: $('#source').val(),
                type_report: $('#type_report').val(),
                tgl_saldo: $('#tgl_saldo').val(),
                no_ws: $('#no_ws').val(),
                buyer: $('#buyer').val(),
                style: $('#style').val(),
                color: $('#color').val(),
                size: $('#size').val(),
                panel: $('#panel').val(),
                part: $('#part').val(),
                qty: $('#qty').val(),
            };

            let requiredFields = [
                'from_tgl_saldo',
                'from_no_ws',
                'from_buyer',
                'from_style',
                'from_color',
                'from_size',
                'from_qty',
                'source',
                'type_report',
                'tgl_saldo',
                'no_ws',
                'buyer',
                'style',
                'color',
                'size',
                'qty'
            ];

            if (
                formData.type_report == 'CUTTING' ||
                formData.type_report == 'DC'
            ) {
                requiredFields.push(
                    'from_panel',
                    'from_part',
                    'panel',
                    'part'
                );
            }

            // validasi
            for (const key of requiredFields) {
                if (formData[key] == '' || formData[key] == null) {
                    iziToast.warning({
                        title: 'Warning',
                        message: key.replaceAll('_', ' ') + ' wajib diisi!',
                        position: 'topCenter',
                        transitionIn: 'slideInRight',
                        timeout: 2000
                    });
                    return;
                }
            }

            $.ajax({
                url: "{{ route('store-switching') }}",
                type: "POST",
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },

                data: formData,

                beforeSend: function () {
                    showLoading();
                    $('#btnSimpan').prop('disabled', true);
                },

                success: function (res) {

                    Swal.fire('Success', 'Data berhasil disimpan', 'success');

                    $('#from_tgl_saldo').val('');
                    $('#from_buyer').val('');
                    $('#from_style').val('');
                    $('#from_qty').val('');

                    $('#tgl_saldo').val('');
                    $('#buyer').val('');
                    $('#style').val('');
                    $('#qty').val('');

                    $('#from_no_ws').val('').trigger('change');
                    $('#from_color').html('<option value="">Select Color</option>').trigger('change');
                    $('#from_size').html('<option value="">Select Size</option>').trigger('change');
                    $('#from_panel').html('<option value="">Select Panel</option>').trigger('change');
                    $('#from_part').html('<option value="">Select Part</option>').trigger('change');

                    $('#no_ws').val('').trigger('change');
                    $('#color').html('<option value="">Select Color</option>').trigger('change');
                    $('#size').html('<option value="">Select Size</option>').trigger('change');
                    $('#panel').html('<option value="">Select Panel</option>').trigger('change');
                    $('#part').html('<option value="">Select Part</option>').trigger('change');

                    listTableReload();
                },

                error: function (xhr) {
                    Swal.fire('Error', 'Gagal simpan data', 'error');
                },

                complete: function () {
                    hideLoading();
                    $('#btnSimpan').prop('disabled', false);
                }
            });

        });

        $('#check_all').on('change', function () {
            $('.row-check').prop('checked', $(this).prop('checked'));
            toggleDeleteButton();
        });

        $('#btnDeleteSelected').on('click', function () {

            let ids = [];

            $('.row-check:checked').each(function () {
                ids.push($(this).val());
            });

            if (ids.length === 0) {
                Swal.fire('Warning', 'Tidak ada data yang dipilih!', 'warning');
                return;
            }

            Swal.fire({
                title: 'Yakin?',
                text: 'Data yang dicentang akan dihapus!',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Ya, hapus!'
            }).then((result) => {

                if (result.isConfirmed) {

                    $.ajax({
                        url: "{{ route('delete-switching') }}",
                        type: "POST",
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        data: {
                            ids: ids,
                            source: $("#source_list").val(),
                        },
                        success: function (res) {
                            $('#list-table').DataTable().ajax.reload(function () {
                                toggleDeleteButton();
                            });

                            Swal.fire('Success', 'Data berhasil dihapus!', 'success');
                        },
                        error: function () {
                            Swal.fire('Error', 'Gagal menghapus data!', 'error');
                        }
                    });
                }
            });
        });

        $('#list-table').on('change', '.row-check', function () {
            toggleDeleteButton();
        });

        function toggleDeleteButton() {
            let checked = $('.row-check:checked').length;

            if (checked > 0) {
                $('#btnDeleteSelected').show();
            } else {
                $('#btnDeleteSelected').hide();
            }
        }

        function exportDataSwitching() {
            let tglAwal = $('#tgl-awal').val();
            let tglAkhir = $('#tgl-akhir').val();
            let source = $('#source_list').val();

            if (!tglAwal || !tglAkhir) {
                Swal.fire('Warning', 'Pilih tanggal awal dan akhir!', 'warning');
                return;
            }

            Swal.fire({ title: 'Please Wait...', html: 'Exporting Data...', didOpen: () => { Swal.showLoading(); }, allowOutsideClick: false });
            $.ajax({
                type: 'POST',
                url: "{{ route('export-switching') }}",
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                data: {
                    dateFrom: tglAwal,
                    dateTo: tglAkhir,
                    source: source
                },
                xhrFields: { responseType: 'blob' },
                success: function(response) {
                    Swal.close();
                    let link = document.createElement('a');
                    link.href = window.URL.createObjectURL(new Blob([response]));
                    link.download = 'Switching_' + tglAwal + '_' + tglAkhir + '.xlsx';
                    link.click();
                },
                error: function() { Swal.fire({ title: 'Gagal Export', icon: 'error' }); }
            });
        }
    </script>
@endsection
