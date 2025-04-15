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
            <h5 class="card-title">
                <i class="fa-solid fa-file-circle-exclamation"></i> Report Defect
            </h5>
        </div>
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-end gap-3">
                <div class="d-flex align-items-end gap-3">
                    <div>
                        <label class="form-label">Dari </label>
                        <input type="date" class="form-control" id="dateFrom" name="dateFrom" value="{{ date("Y-m-d") }}" onchange="reportDefectDatatableReload()">
                    </div>
                    <span class="mb-2"> - </span>
                    <div>
                        <label class="form-label">Sampai </label>
                        <input type="date" class="form-control" id="dateTo" name="dateTo" value="{{ date("Y-m-d") }}" onchange="reportDefectDatatableReload()">
                    </div>
                    <button class="btn btn-primary"><i class="fa fa-search"></i></button>
                    <button class="btn btn-sb-secondary" data-bs-toggle="modal" data-bs-target="#filterModal"><i class="fa fa-filter"></i></button>
                </div>
                <button class="btn btn-success">Export <i class="fa fa-file-excel"></i></button>
            </div>
            <div class="table-responsive mt-3">
                <table class="table table-bordered table-sm" id="report-defect-table">
                    <thead>
                        <tr>
                            <th>Kode Numbering</th>
                            <th>Buyer</th>
                            <th>No. WS</th>
                            <th>Style</th>
                            <th>Color</th>
                            <th>Size</th>
                            <th>Dest</th>
                            <th>Sewing Line</th>
                            <th>Defect Type</th>
                            <th>Defect Area</th>
                            <th>Status</th>
                            <th>Rework ID</th>
                            <th>External Status</th>
                            <th>External ID</th>
                            <th>External IN</th>
                            <th>External OUT</th>
                            <th>Created At</th>
                            <th>Updated At</th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal -->
    <div class="modal fade" id="filterModal" tabindex="-1" aria-labelledby="reportDefectModal" aria-hidden="true">
        <div class="modal-dialog modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header bg-sb">
                    <h1 class="modal-title fs-5" id="filterModalLabel"><i class="fa fa-filter"></i> Filter</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Defect Types</label>
                        <select class="select2bs4filter" name="defect_types[]" multiple="multiple" id="defect_types">
                            @foreach ($defectTypes as $defectType)
                                <option value="{{ $defectType->id }}">{{ $defectType->defect_type }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Defect Areas</label>
                        <select class="select2bs4filter" name="defect_areas[]" multiple="multiple" id="defect_areas">
                            @foreach ($defectAreas as $defectArea)
                                <option value="{{ $defectArea->id }}">{{ $defectArea->defect_area }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Defect Status</label>
                        <select class="select2bs4filter" name="defect_status[]" multiple="multiple" id="defect_status">
                            <option value="">SEMUA</option>
                            <option value="defect">DEFECT</option>
                            <option value="reworked">REWORKED</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Sewing Line</label>
                        <select class="select2bs4filter" name="sewing_line[]" multiple="multiple" id="sewing_line">
                            <option value="">SEMUA</option>
                            @foreach ($lines as $line)
                                <option value="{{ $line }}">{{ strtoupper(str_replace("_", " ", $line)) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">No. WS</label>
                        <select class="select2bs4filter" name="ws[]" multiple="multiple" id="ws">
                            <option value="">SEMUA</option>
                            @foreach ($orders as $order)
                                <option value="{{ $order }}">{{ $order }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Style</label>
                        <select class="select2bs4filter" name="style[]" multiple="multiple" id="style">
                            <option value="">SEMUA</option>
                            @foreach ($styles as $style)
                                <option value="{{ $style }}">{{ $style }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Color</label>
                        <select class="select2bs4filter" name="color[]" multiple="multiple" id="color">
                            <option value="">SEMUA</option>
                            @foreach ($colors as $color)
                                <option value="{{ $color }}">{{ $color }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Size</label>
                        <select class="select2bs4filter" name="size[]" multiple="multiple" id="size">
                            <option value="">SEMUA</option>
                            @foreach ($sizes as $size)
                                <option value="{{ $size }}">{{ $size }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">External Type</label>
                        <select class="select2bs4filter" name="external_type[]" multiple="multiple" id="external_type">
                            <option value="">SEMUA</option>
                            @foreach ($externalTypes as $externalType)
                                <option value="{{ $externalType }}">{{ ($externalType ? strtoupper($externalType) : "SEWING") }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">External IN</label>
                        <input type="date" class="form-control" name="external_in">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">External OUT</label>
                        <input type="date" class="form-control" name="external_out">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                    <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Bersihkan <i class="fa-solid fa-broom"></i></button>
                    <button type="button" class="btn btn-success" onclick="reportDefectDatatableReload()">Simpan <i class="fa-solid fa-check"></i></button>
                </div>
            </div>
        </div>
    </div>

@endsection

@section('custom-script')
<script src="{{ asset('plugins/select2/js/select2.full.min.js') }}"></script>

    <!-- DataTables & Plugins -->
    <script src="{{ asset('plugins/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-responsive/js/dataTables.responsive.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-responsive/js/responsive.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-rowsgroup/dataTables.rowsGroup.js') }}"></script>

    <!-- Select2 -->
    <script src="{{ asset('plugins/select2/js/select2.full.min.js') }}"></script>
    <script>
        // Select2 Autofocus
        // $(document).on('select2:open', () => {
        //     document.querySelector('.select2-search__field').focus();
        // });

        // Initialize Select2 Elements
        $('.select2').select2();

        // Initialize Select2BS4 Elements
        $('.select2bs4').select2({
            theme: 'bootstrap4',
            containerCssClass: 'form-control-sm rounded'
        });

        // Initialize Select2BS4 Elements Filter Modal
        $('.select2bs4filter').select2({
            theme: 'bootstrap4',
            dropdownParent: $("#filterModal")
        });

        $('#report-defect-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: '{!! route('report-defect') !!}',
                data: function (d) {
                    d.dateFrom = $('#dateFrom').val();
                    d.dateTo = $('#dateTo').val();
                    d.defect_types = $('#defect_types').val();
                    d.defect_areas = $('#defect_areas').val();
                    d.defect_status = $('#defect_status').val();
                    d.sewing_line = $('#sewing_line').val();
                    d.ws = $('#ws').val();
                    d.style = $('#style').val();
                    d.color = $('#color').val();
                    d.size = $('#size').val();
                    d.external_type = $('#external_type').val();
                    d.external_in = $('#external_in').val();
                    d.external_out = $('#external_out').val();
                }
            },
            columns: [
                {data: 'kode_numbering',name: 'kode_numbering'},
                {data: 'buyer',name: 'buyer'},
                {data: 'ws',name: 'ws'},
                {data: 'style',name: 'style'},
                {data: 'color',name: 'color'},
                {data: 'size',name: 'size'},
                {data: 'dest',name: 'dest'},
                {data: 'sewing_line',name: 'sewing_line'},
                {data: 'defect_type',name: 'defect_type'},
                {data: 'defect_area',name: 'defect_area'},
                {data: 'defect_status',name: 'defect_status'},
                {data: 'rework_id',name: 'rework_id'},
                {data: 'external_status',name: 'external_status'},
                {data: 'external_id',name: 'external_id'},
                {data: 'external_in',name: 'external_in'},
                {data: 'external_out',name: 'external_out'},
                {data: 'created_at',name: 'created_at'},
                {data: 'updated_at',name: 'updated_at'}
            ],
            columnDefs: [
                {
                    className: "text-nowrap",
                    targets: "_all"
                },
                {
                    targets: [16, 17],
                    render: function (data, type, row) {
                        return formatDateTime(data);
                    },
                }
            ]
        });

        function reportDefectDatatableReload() {
            console.log($('#defect_types').val());
            $('#report-defect-table').DataTable().ajax.reload();
        }
    </script>
@endsection






