@extends('layouts.index')

@section('custom-link')
    <!-- DataTables -->
    <link rel="stylesheet" href="{{ asset('plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/datatables-responsive/css/responsive.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/datatables-buttons/css/buttons.bootstrap4.min.css') }}">
@endsection

@section('content')
    <div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable" style="max-width: 55%;">
            <div class="modal-content">
                <div class="modal-header bg-sb text-light">
                    <h1 class="modal-title fs-5" id="exampleModalLabel"></h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="detail">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="exampleModalEdit" tabindex="-1" role="dialog" aria-labelledby="exampleModalEditLabel"
        aria-hidden="true">
        <form action="{{ route('update_marker') }}" method="post" onsubmit="submitForm(this, event)">
            @method('PUT')
            <div class="modal-dialog modal-lg modal-dialog-scrollable" style="max-width: 55%;">
                <div class="modal-content">
                    <div class="modal-header bg-sb text-light">
                        <h1 class="modal-title fs-5" id="exampleModalEditLabel"></h1>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class='row'>
                            <div class='col-sm-3'>
                                <div class='form-group'>
                                    <label class='form-label'><small>Gramasi</small></label>
                                    <input type='text' class='form-control' id='txt_gramasi' name='txt_gramasi'
                                        value = ''>
                                    <input type='hidden' class='form-control' id='id_c' name='id_c' value = ''>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                        <button type="submit" class="btn btn-sb">Simpan</button>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <div class="card card-sb card-outline">
        <div class="card-header">
            <h5 class="card-title fw-bold mb-0">Data Marker</h5>
        </div>
        <div class="card-body">
            <a href="{{ route('create-marker') }}" class="btn btn-success btn-sm mb-3">
                <i class="fas fa-plus"></i>
                Baru
            </a>
            <div class="d-flex align-items-end gap-3 mb-3">
                <div class="mb-3">
                    <label class="form-label"><small>Tgl Awal</small></label>
                    <input type="date" class="form-control form-control-sm" id="tgl-awal" name="tgl_awal"
                        value="{{ date('Y-m-d') }}">
                </div>
                <div class="mb-3">
                    <label class="form-label"><small>Tgl Akhir</small></label>
                    <input type="date" class="form-control form-control-sm" id="tgl-akhir" name="tgl_akhir"
                        value="{{ date('Y-m-d') }}">
                </div>
                <div class="mb-3">
                    <button class="btn btn-primary btn-sm" onclick="filterTable()">Tampilkan</button>
                </div>
            </div>
            <div class="table-responsive">
                <table id="datatable" class="table table-bordered table-sm w-100">
                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th>No. Marker</th>
                            <th>No. WS</th>
                            <th>Color</th>
                            <th>Panel</th>
                            <th>Panjang Marker</th>
                            <th>Lebar Marker</th>
                            <th>Gramasi</th>
                            <th>Gelar QTYs</th>
                            <th>PO</th>
                            <th>Urutan</th>
                            <th>Total Form</th>
                            <th>Ket.</th>
                            <th class="align-bottom">Act</th>
                        </tr>
                    </thead>
                </table>
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
        $('#datatable thead tr').clone(true).appendTo('#datatable thead');
        $('#datatable thead tr:eq(1) th').each(function(i) {
            if (i == 1 || i == 2 || i == 3 || i == 4) {
                var title = $(this).text();
                $(this).html('<input type="text"  style="width:100%"/>');

                $('input', this).on('keyup change', function() {
                    if (datatable.column(i).search() !== this.value) {
                        datatable
                            .column(i)
                            .search(this.value)
                            .draw();
                    }
                });
            } else {
                $(this).empty();
            }
        });

        let datatable = $("#datatable").DataTable({
            ordering: false,
            processing: true,
            serverSide: true,
            ajax: {
                url: '{{ route('marker') }}',
                data: function(d) {
                    d.tgl_awal = $('#tgl-awal').val();
                    d.tgl_akhir = $('#tgl-akhir').val();
                },
            },
            columns: [{
                    data: 'tgl_cut_fix',
                    searchable: false
                },
                {
                    data: 'kode'
                },
                {
                    data: 'act_costing_ws'
                },
                {
                    data: 'color'
                },
                {
                    data: 'panel'
                },
                {
                    data: 'panjang_marker_fix',
                    searchable: false
                },
                {
                    data: 'lebar_marker',
                },
                {
                    data: 'gramasi'
                },
                {
                    data: 'gelar_qty'
                },
                {
                    data: 'po_marker'
                },
                {
                    data: 'urutan_marker'
                },
                {
                    data: 'tot_form',
                    searchable: false
                },
                {
                    data: 'notes',
                },
                {
                    data: 'id'
                },
            ],
            columnDefs: [{
                    targets: [13],
                    className: "align-middle",
                    render: (data, type, row, meta) => {
                        let exportBtn = `
                            <button type="button" class="btn btn-sm btn-secondary" onclick="printMarker('` + row.kode + `');">
                                <i class="fa fa-print"></i>
                            </button>
                        `;

                        if (row.cancel != 'Y' && row.tot_form != 0) {
                            return `
                                <div class='d-flex gap-1 justify-content-center'>
                                    <a class='btn btn-primary btn-sm' data-bs-toggle="modal" data-bs-target="#exampleModal" onclick='getdetail(` +
                                row.id + `);'>
                                        <i class='fa fa-search'></i>
                                    </a>
                                    ` + exportBtn + `
                                </div>
                            `;
                        } else if (row.cancel != 'Y' && row.tot_form == 0) {
                            return `
                                <div class='d-flex gap-1 justify-content-center mb-1'>
                                    <a class='btn btn-info btn-sm' data-bs-toggle="modal" data-bs-target="#exampleModal" onclick='getdetail(` +
                                row.id + `);'>
                                        <i class='fa fa-search'></i>
                                    </a>
                                    ` + exportBtn +
                                `
                                </div>
                                <div class='d-flex gap-1 justify-content-center'>
                                    <a class='btn btn-primary btn-sm' data-bs-toggle="modal" data-bs-target="#exampleModalEdit" onclick='edit(` +
                                row.id + `);'>
                                        <i class='fa fa-edit'></i>
                                    </a>
                                    <a class='btn btn-danger btn-sm' onclick='cancel(` + row.id + `);'>
                                        <i class='fa fa-ban'></i>
                                    </a>
                                </div>
                            `;
                        } else if (row.cancel == 'Y') {
                            return `
                                <div class='d-flex gap-1 justify-content-center'>
                                    <a class='btn btn-info btn-sm' data-bs-toggle="modal" data-bs-target="#exampleModal" onclick='getdetail(` +
                                row.id + `);'>
                                        <i class='fa fa-search'></i>
                                    </a>
                                    ` + exportBtn + `
                                </div>
                            `;
                        }
                    }
                },
                {
                    targets: '_all',
                    render: (data, type, row, meta) => {
                        var color = '#2b2f3a';
                        if (row.tot_form != '0' && row.cancel == 'N') {
                            color = '#087521';
                        } else if (row.tot_form == '0' && row.cancel == 'N') {
                            color = '#2243d6';
                        } else if (row.cancel == 'Y') {
                            color = '#d33141';
                        }
                        return '<span style="font-weight: 600; color:' + color + '">' + data + '</span>';
                    }
                }
            ],
        });

        function filterTable() {
            datatable.ajax.reload();
        }

        function getdetail(id_c) {
            $("#exampleModalLabel").html('Marker Detail');
            let html = $.ajax({
                type: "POST",
                url: '{{ route('show-marker') }}',
                data: {
                    id_c: id_c
                },
                async: false
            }).responseText;
            $("#detail").html(html);
        };

        function edit(id_c) {
            $("#exampleModalEditLabel").html('Marker Edit');
            jQuery.ajax({
                url: '{{ route('show_gramasi') }}',
                method: 'POST',
                data: {
                    id_c: id_c
                },
                dataType: 'json',
                success: function(response) {
                    document.getElementById('txt_gramasi').value = response.gramasi;
                    document.getElementById('id_c').value = response.id;
                },
                error: function(request, status, error) {
                    alert(request.responseText);
                },
            });
        };

        function cancel(id_c) {
            let html = $.ajax({
                type: "POST",
                url: '{{ route('update_status') }}',
                data: {
                    id_c: id_c
                },
                async: false
            }).responseText;

            swal.fire({
                position: 'mid-end',
                icon: 'info',
                title: 'Data Sudah Di Rubah',
                showConfirmButton: false,
                timer: 1000
            })

            datatable.ajax.reload();
        };

        function printMarker(kodeMarker) {
            let fileName = kodeMarker;

            Swal.fire({
                title: 'Please Wait...',
                html: 'Exporting Data...',
                didOpen: () => {
                    Swal.showLoading()
                },
                allowOutsideClick: false,
            });

            $.ajax({
                url: '{{ route('print-marker') }}/' + kodeMarker.replace(/\//g, '_'),
                type: 'post',
                processData: false,
                contentType: false,
                xhrFields: {
                    responseType: 'blob'
                },
                success: function(res) {
                    if (res) {
                        console.log(res);

                        var blob = new Blob([res], {
                            type: 'application/pdf'
                        });
                        var link = document.createElement('a');
                        link.href = window.URL.createObjectURL(blob);
                        link.download = fileName + ".pdf";
                        link.click();

                        swal.close();
                    }
                }
            });
        }
    </script>
@endsection
