@extends('layouts.index')

@section('custom-link')
    <!-- DataTables -->
    <link rel="stylesheet" href="{{ asset('plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/datatables-responsive/css/responsive.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/datatables-buttons/css/buttons.bootstrap4.min.css') }}">
@endsection

@section('content')
    {{-- Part Data --}}
    <div class="card">
        <div class="card-header bg-sb text-light">
            <h5 class="card-title fw-bold mb-0"><i class="fas fa-th fa-sm"></i> Part</h5>
        </div>
        <div class="card-body">
            <a href="{{ route('create-part') }}" class="btn btn-success btn-sm mb-3">
                <i class="fas fa-plus"></i>
                Baru
            </a>
            <div class="d-flex align-items-end gap-3 mb-3">
                <div>
                    <label class="form-label"><small>Tanggal Awal</small></label>
                    <input type="date" class="form-control form-control-sm" id="tgl-awal" name="tgl_awal" onchange="datatablePartReload()">
                </div>
                <div>
                    <label class="form-label"><small>Tanggal Akhir</small></label>
                    <input type="date" class="form-control form-control-sm" id="tgl-akhir" name="tgl_akhir" value="{{ date('Y-m-d') }}" onchange="datatablePartReload()">
                </div>
                <div>
                    <button class="btn btn-primary btn-sm" onclick="datatablePartReload()"><i class="fa fa-search"></i></button>
                </div>
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

    {{-- Detail Part Modal --}}
    <div class="modal fade" id="detailPartModal" tabindex="-1" aria-labelledby="detailPartLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header bg-sb text-light">
                    <h1 class="modal-title fs-5" id="detailPartLabel"><i class="fa fa-search fa-sm"></i> Detail Part</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row align-items-end">
                        <input type="hidden" name="detail_id" id="detail_id" onchange="dataTablePartFormReload()">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">No. WS</label>
                                <input type="text" class="form-control" name="detail_act_costing_ws" id="detail_act_costing_ws" value="" readonly>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Style</label>
                                <input type="text" class="form-control" name="detail_style" id="detail_style" value="" readonly>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Color</label>
                                <input type="text" class="form-control" name="detail_color" id="detail_color" value="" readonly>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Panel</label>
                                <input type="text" class="form-control" name="detail_panel" id="detail_panel" value="" readonly>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Part</label>
                                <input type="text" class="form-control" name="detail_part" id="detail_part_details" value="" readonly>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <button class="btn btn-primary btn-block fw-bold" onclick="reorderStockerNumbering()"><i class="fa-solid fa-arrow-up-wide-short"></i> REORDER</button>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <button class="btn btn-info btn-block fw-bold" onclick="generateFullStockerNumbering()"><i class="fa-solid fa-list-check"></i> GENERATE</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="table-responsive mb-3">
                                <table class="table table-bordered table-sm w-100" id="datatable-part-form">
                                    <thead>
                                        <tr>
                                            <th>Act</th>
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
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
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
                                    <i class='fa fa-cog'></i>
                                </a>
                                <a class='btn btn-danger btn-sm' data='` + JSON.stringify(row) +
                            `' data-url='{{ route('destroy-part') }}/` + row['id'] + `' onclick='deleteData(this)'>
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

                    swal.close();
                },
                error: function (jqXHR) {
                    console.log(jqXHR);
                }
            });
        }
    </script>
@endsection
