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
            <h5 class="card-title fw-bold mb-0"><i class="fas fa-list"></i> Stock Opname</h5>
        </div>
        <div class="card-body">
            <div class="mb-3">
                <a class="btn btn-outline-primary btn-sm" href="{{ route('create_so_mesin') }}">
                    <i class="fas fa-plus"></i>
                    New Stock Opname
                </a>
            </div>
            <div class="d-flex align-items-end gap-3 mb-3">
                <div class="mb-3">
                    <label class="form-label"><small><b>Tgl SO Awal</b></small></label>
                    <input type="date" class="form-control form-control-sm " id="tgl-awal" name="tgl_awal"
                        value="{{ date('Y-m-d') }}">
                </div>
                <div class="mb-3">
                    <label class="form-label"><small><b>Tgl SO Akhir</b></small></label>
                    <input type="date" class="form-control form-control-sm" id="tgl-akhir" name="tgl_akhir"
                        value="{{ date('Y-m-d') }}">
                </div>
                <div class="mb-3">
                    <a class="btn btn-outline-primary position-relative btn-sm" onclick="dataTableReload()">
                        <i class="fas fa-search"></i>
                        Cari
                    </a>
                </div>
                <div class="mb-3">
                    <a onclick="export_excel()" class="btn btn-outline-success position-relative btn-sm">
                        <i class="fas fa-file-excel fa-sm"></i>
                        Export Excel
                    </a>
                </div>
            </div>
            <div class="table-responsive">
                <table id="datatable" class="table table-bordered w-100 text-nowrap">
                    <thead class="bg-sb">
                        <tr style="text-align:center; vertical-align:middle">
                            <th scope="col">Tgl. SO</th>
                            <th scope="col">Lokasi</th>
                            <th scope="col">Jumlah Mesin</th>
                            <th scope="col">Act</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal -->
    <div class="modal fade" id="modal-detail-so" tabindex="-1" role="dialog" aria-labelledby="modalTitle"
        aria-hidden="true">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content">
                <div class="modal-header bg-sb">
                    <h5 class="modal-title fw-bold" id="modalTitle">Detail Stock Opname</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"
                        style="filter: invert(1);"></button>

                </div>
                <div class="modal-body">
                    <div class="table-responsive">
                        <table id="datatable-detail-so" class="table table-bordered table-hover w-100">
                            <thead>
                                <tr class="text-center">
                                    <th>No</th>
                                    <th>ID</th>
                                    <th>ID QR</th>
                                    <th>Jenis Mesin</th>
                                    <th>Brand</th>
                                    <th>Tipe Mesin</th>
                                    <th>Serial No</th>
                                    <th>Keterangan</th>
                                    <th>Created By</th>
                                    <th>Created At</th>
                                    <th>Act</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
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
    <script src="{{ asset('plugins/export_excel_js/exceljs.min.js') }}"></script>
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

        function notif() {
            alert("Maaf, Fitur belum tersedia!");
        }
    </script>
    <script>
        function dataTableReload() {
            datatable.ajax.reload();
        }

        $(document).ready(function() {
            dataTableReload();
        })

        let datatable = $("#datatable").DataTable({
            ordering: false,
            processing: true,
            serverSide: true,
            paging: true,
            searching: true,
            scrollY: '500px',
            scrollX: '300px',
            scrollCollapse: true,
            ajax: {
                url: '{{ route('so_mesin') }}',
                data: function(d) {
                    d.dateFrom = $('#tgl-awal').val();
                    d.dateTo = $('#tgl-akhir').val();
                },
            },
            columns: [{
                    data: 'tgl_so_fix'

                },
                {
                    data: 'lokasi'

                }, {
                    data: 'tot_mesin'
                },
                {
                    data: null,
                    orderable: false,
                    searchable: false,
                    render: function(data, type, row) {
                        return `
                    <a class="btn btn-outline-info position-relative btn-sm"
                        onclick="showDetail('${row.tgl_so_fix}', '${row.lokasi}')">
                        Show
                    </a>
                `;
                    }
                },
            ],
        });


        async function export_excel() {
            const dateFrom = $('#tgl-awal').val();
            const dateTo = $('#tgl-akhir').val();

            try {
                const response = await $.ajax({
                    url: '{{ route('export_excel_so_mesin') }}',
                    data: {
                        dateFrom: dateFrom,
                        dateTo: dateTo,
                        length: -1
                    }
                });

                const rows = response.data;

                if (!rows.length) {
                    Swal.fire('No data', 'Tidak ada data untuk diekspor.', 'warning');
                    return;
                }

                const workbook = new ExcelJS.Workbook();
                const worksheet = workbook.addWorksheet("Stock Opname");

                // --- Title ---
                worksheet.mergeCells('A1:J1');
                const titleCell = worksheet.getCell('A1');
                const options = {
                    year: 'numeric',
                    month: 'long',
                    day: 'numeric'
                };
                const dateFromIndo = new Date(dateFrom).toLocaleDateString('id-ID', options);
                const dateToIndo = new Date(dateTo).toLocaleDateString('id-ID', options);

                titleCell.value = `Stock Opname (${dateFromIndo} - ${dateToIndo})`;

                titleCell.font = {
                    bold: true,
                    size: 16
                };
                titleCell.alignment = {
                    horizontal: 'center'
                };

                // --- Header ---
                worksheet.addRow([]); // Empty row after title
                const headerRow = worksheet.addRow([
                    'Tanggal SO', 'ID QR', 'Jenis Mesin', 'Brand', 'Tipe Mesin', 'Serial No',
                    'Keterangan', 'Lokasi', 'Created By', 'Created At'
                ]);
                headerRow.font = {
                    bold: true
                };
                headerRow.alignment = {
                    horizontal: 'center'
                };

                // --- Column Widths ---
                worksheet.columns = [{
                        key: 'tgl_so',
                        width: 15
                    },
                    {
                        key: 'id_qr',
                        width: 25
                    },
                    {
                        key: 'jenis_mesin',
                        width: 20
                    },
                    {
                        key: 'brand',
                        width: 15
                    },
                    {
                        key: 'tipe_mesin',
                        width: 20
                    },
                    {
                        key: 'serial_no',
                        width: 20
                    },
                    {
                        key: 'ket',
                        width: 25
                    },
                    {
                        key: 'lokasi',
                        width: 20
                    },
                    {
                        key: 'created_by',
                        width: 20
                    },
                    {
                        key: 'created_at',
                        width: 20
                    }
                ];

                // --- Data Rows ---
                rows.forEach(row => {
                    worksheet.addRow({
                        tgl_so: row.tgl_so,
                        id_qr: row.id_qr,
                        jenis_mesin: row.jenis_mesin,
                        brand: row.brand,
                        tipe_mesin: row.tipe_mesin,
                        serial_no: row.serial_no,
                        ket: row.ket,
                        lokasi: row.lokasi,
                        created_by: row.created_by,
                        created_at: row.created_at
                    });
                });

                // --- Apply Border to All Data Rows ---
                worksheet.eachRow((row, rowNumber) => {
                    if (rowNumber >= 3) { // skip title (1) + spacer (2)
                        row.eachCell(cell => {
                            cell.border = {
                                top: {
                                    style: 'thin'
                                },
                                left: {
                                    style: 'thin'
                                },
                                bottom: {
                                    style: 'thin'
                                },
                                right: {
                                    style: 'thin'
                                }
                            };
                        });
                    }
                });

                const buffer = await workbook.xlsx.writeBuffer();

                const blob = new Blob([buffer], {
                    type: "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet"
                });
                const link = document.createElement("a");
                link.href = URL.createObjectURL(blob);
                link.download = `Stock_Opname_Detail_${dateFrom}_to_${dateTo}.xlsx`;
                link.click();

            } catch (err) {
                console.error("Export failed:", err);
                Swal.fire('Error', 'Gagal mengekspor data.', 'error');
            }
        }


        let datatableDetail;

        function showDetail(tgl_so_fix, lokasi) {
            $('#modalTitle').text(`Detail Stock Opname - ${tgl_so_fix} | ${lokasi}`);
            $('#modal-detail-so').modal('show');

            $('#modal-detail-so').one('shown.bs.modal', function() {
                if (datatableDetail) {
                    datatableDetail.destroy();
                    $('#datatable-detail-so').empty(); // Reset table header + body
                    $('#datatable-detail-so').html(`
                <thead>
                    <tr class="text-center">
                        <th>No</th>
                        <th>ID SO</th>
                        <th>ID QR</th>
                        <th>Jenis Mesin</th>
                        <th>Brand</th>
                        <th>Tipe Mesin</th>
                        <th>Serial No</th>
                        <th>Keterangan</th>
                        <th>Created By</th>
                        <th>Created At</th>
                        <th>Act</th>
                    </tr>
                </thead>
            `);
                }

                datatableDetail = $("#datatable-detail-so").DataTable({
                    processing: true,
                    serverSide: true,
                    searching: true,
                    paging: false,
                    ordering: false,
                    ajax: {
                        url: '{{ route('so_mesin_detail_modal') }}',
                        data: {
                            tgl_so: tgl_so_fix,
                            lokasi: lokasi
                        }
                    },
                    columns: [{
                            data: null,
                            render: (data, type, row, meta) => meta.row + 1
                        },
                        {
                            data: 'id_so'
                        },
                        {
                            data: 'id_qr'
                        },
                        {
                            data: 'jenis_mesin'
                        },
                        {
                            data: 'brand'
                        },
                        {
                            data: 'tipe_mesin'
                        },
                        {
                            data: 'serial_no'
                        },
                        {
                            data: 'ket'
                        },
                        {
                            data: 'created_by'
                        },
                        {
                            data: 'created_at'
                        },
                        {
                            data: null,
                            orderable: false,
                            className: 'text-center',
                            render: function(data, type, row, meta) {
                                return `<button class="btn btn-danger btn-sm btn-delete" data-id="${row.id_so}">Delete</button>`;
                            }
                        }
                    ]
                });

                $('#datatable-detail-so tbody').off('click', '.btn-delete').on('click', '.btn-delete', function() {
                    const id = $(this).data('id');

                    Swal.fire({
                        title: 'Are you sure?',
                        text: "This will permanently delete the record.",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#d33',
                        cancelButtonColor: '#6c757d',
                        confirmButtonText: 'Yes, delete it!',
                        cancelButtonText: 'Cancel'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            $.ajax({
                                url: '{{ route('so_mesin_delete') }}',
                                method: 'POST',
                                data: {
                                    id_so: id,
                                    _token: '{{ csrf_token() }}'
                                },
                                success: function(response) {
                                    Swal.fire({
                                        icon: 'success',
                                        title: 'Deleted!',
                                        text: 'The record was successfully deleted.',
                                        timer: 1500,
                                        showConfirmButton: false
                                    });
                                    datatableDetail.ajax.reload(null, false);
                                },
                                error: function(xhr) {
                                    Swal.fire({
                                        icon: 'error',
                                        title: 'Oops...',
                                        text: 'Failed to delete the record.',
                                    });
                                }
                            });
                        }
                    });
                });


                // Unbind modal event to prevent multiple bindings
                $('#modal-detail-so').off('shown.bs.modal');
            });
        }
    </script>
@endsection
