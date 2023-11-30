@extends('layouts.index')

@section('custom-link')
    <!-- DataTables -->
    <link rel="stylesheet" href="{{ asset('plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/datatables-responsive/css/responsive.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/datatables-buttons/css/buttons.bootstrap4.min.css') }}">
@endsection

@section('content')
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
                    <label class="form-label"><small>Tgl Awal</small></label>
                    <input type="date" class="form-control form-control-sm" id="tgl-awal" name="tgl_awal" onchange="datatablePartReload()">
                </div>
                <div>
                    <label class="form-label"><small>Tgl Akhir</small></label>
                    <input type="date" class="form-control form-control-sm" id="tgl-akhir" name="tgl_akhir" value="{{ date('Y-m-d') }}" onchange="datatablePartReload()">
                </div>
                <div>
                    <button class="btn btn-primary btn-sm" onclick="datatablePartReload()">Tampilkan</button>
                </div>
            </div>
            <div class="table-responsive">
                <table id="datatable-part" class="table table-bordered table-sm w-100">
                    <thead>
                        <tr>
                            <th>Kode Part</th>
                            <th>No. WS</th>
                            <th>Buyer</th>
                            <th>Style</th>
                            <th>Color</th>
                            <th>Panel</th>
                            <th>Part</th>
                            <th>Total Form</th>
                            <th class="align-bottom">Action</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>

    <div class="modal fade" id="detailPartModal" tabindex="-1" aria-labelledby="detailPartLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header bg-sb text-light">
                    <h1 class="modal-title fs-5" id="detailPartLabel">Detail Part</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <input type="hidden" name="detail_id" id="detail_id" onchange="dataTablePartFormReload()">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">No. WS</label>
                                <input type="text" class="form-control" name="detail_ws" id="detail_ws" value="" readonly>
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
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Panel</label>
                                <input type="text" class="form-control" name="detail_panel" id="detail_panel" value="" readonly>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Part</label>
                                <input type="text" class="form-control" name="detail_part" id="detail_part_details" value="" readonly>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="table-responsive mb-3">
                                <table class="table table-bordered table-sm w-100" id="datatable-part-form">
                                    <thead>
                                        <tr>
                                            <th>No. Marker</th>
                                            <th>No. Form</th>
                                            <th>Tgl Spreading</th>
                                            <th>Meja</th>
                                            <th>WS</th>
                                            <th>Buyer</th>
                                            <th>No. Cut</th>
                                            <th>Style</th>
                                            <th>Color</th>
                                            <th>Part</th>
                                            <th>Size Ratio</th>
                                            <th>Total Lembar</th>
                                            <th>Act</th>
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
                    {{-- <button type="button" class="btn btn-primary">Simpan</button> --}}
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
        document.addEventListener("DOMContentLoaded", () => {
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

        let datatablePart = $("#datatable-part").DataTable({
            ordering: false,
            processing: true,
            serverSide: true,
            ajax: {
                url: '{{ route('part') }}',
                data: function (d) {
                    d.id = $("detail_id").val();
                }
            },
            columns: [
                {
                    data: 'kode',
                },
                {
                    data: 'ws',
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
                {
                    data: 'id'
                },
            ],
            columnDefs: [
                {
                    targets: [8],
                    render: (data, type, row, meta) => {
                        return `
                            <div class='d-flex gap-1 justify-content-center'>
                                <buton type="button" onclick='showPartForm(`+JSON.stringify(row)+`)' class='btn btn-primary btn-sm'>
                                    <i class='fa fa-search'></i>
                                </buton>
                                <a href='{{ route('manage-part-form') }}/`+row['id']+`' class='btn btn-success btn-sm'>
                                    <i class='fa fa-plus'></i>
                                </a>
                                <a class='btn btn-danger btn-sm' data='`+JSON.stringify(row)+`' data-url='{{ route('destroy-part') }}/`+row['id']+`' onclick='deleteData(this)'>
                                    <i class='fa fa-trash'></i>
                                </a>
                            </div>
                        `;
                    }
                }
            ],
        });

        function datatablePartReload() {
            datatablePart.ajax.reload()
        }

        $('#datatable-part thead tr').clone(true).appendTo('#datatable-part thead');
        $('#datatable-part thead tr:eq(1) th').each(function(i) {
            if (i == 0 || i == 1 || i == 2 || i == 3 || i == 5) {
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
                    data: 'id_marker'
                },
                {
                    data: 'no_form'
                },
                {
                    data: 'tgl_form_cut',
                    searchable: false
                },
                {
                    data: 'nama_meja'
                },
                {
                    data: 'act_costing_ws'
                },
                {
                    data: 'buyer'
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
                    data: 'nama_part'
                },
                {
                    data: 'marker_details',
                    searchable: false
                },
                {
                    data: 'total_lembar',
                    searchable: false
                },
                {
                    data: null,
                    searchable: false
                },
            ],
            columnDefs: [
                // Nama Meja
                {
                    targets: [3],
                    render: (data, type, row, meta) => data ? data.toUpperCase() : "-"
                },
                // Last Column
                {
                    targets: [12],
                    render: (data, type, row, meta) => {
                        return `<div class='d-flex gap-1 justify-content-center'> <a class='btn btn-info btn-sm' href='{{ route("show-stocker") }}/`+row.part_detail_id+`/`+row.form_cut_id+`' data-bs-toggle='tooltip' target='_blank'><i class='fa fa-eye'></i></a> </div>`;
                    }
                }
            ]
        });

        $('#datatable-part-form thead tr').clone(true).appendTo('#datatable-part-form thead');
        $('#datatable-part-form thead tr:eq(1) th').each(function(i) {
            if (i == 1 || i == 3 || i == 4 || i == 5 || i == 6 || i == 7 || i == 8 || i == 9 || i == 11) {
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

        function dataTablePartFormReload() {
            datatablePartForm.ajax.reload();
        }
    </script>
@endsection
