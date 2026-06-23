@extends('layouts.index')

@section('custom-link')
    <!-- DataTables -->
    <link rel="stylesheet" href="{{ asset('plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/datatables-responsive/css/responsive.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/datatables-buttons/css/buttons.bootstrap4.min.css') }}">

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

        .form-control {
            border: 1.5px solid #ced4da;
            border-radius: 8px;
            padding: 6px 10px;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            border-color: #0d6efd;
            box-shadow: 0 0 0 0.15rem rgba(13, 110, 253, 0.25);
        }

        .dataTables_length select {
            width: auto;
            min-width: 65px;
            padding-right: 24px;
        }

        .unit-qr-img {
            height: 50px;
            width: 50px;
            cursor: pointer;
        }

        .td-truncate {
            max-width: 220px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .filter-row th {
            padding: 4px;
        }
    </style>
@endsection

@section('content')
    <div class="card card-sb">
        <div class="card-header">
            <h5 class="card-title fw-bold mb-0"><i class="fas fa-list-alt"></i> Master Mesin</h5>
        </div>
        <div class="card-body">
            <div class="row mb-3 align-items-end">
                <div class="col-md-3">
                    <label for="cbosupplier"><small><b>Supplier :</b></small></label>
                    <select id="cbosupplier" class="form-control form-control-sm">
                        <option value="">Semua Supplier</option>
                        @foreach ($supplierList as $row)
                            <option value="{{ $row->id_supplier }}">{{ $row->Supplier }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="cbolokasi"><small><b>Lokasi :</b></small></label>
                    <select id="cbolokasi" class="form-control form-control-sm">
                        <option value="">Semua Lokasi</option>
                        @foreach ($lokasiList as $row)
                            <option value="{{ $row->lokasi }}">{{ $row->lokasi }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="table-responsive">
                <table id="datatable" class="table table-bordered table-hover align-middle text-nowrap w-100">
                    <thead class="bg-sb">
                        <tr>
                            <th scope="col" class="text-center align-middle">Sumber</th>
                            <th scope="col" class="text-center align-middle">Kode Jenis</th>
                            <th scope="col" class="text-center align-middle">Jenis</th>
                            <th scope="col" class="text-center align-middle">Kode Merk</th>
                            <th scope="col" class="text-center align-middle">Merk</th>
                            <th scope="col" class="text-center align-middle">Tipe</th>
                            <th scope="col" class="text-center align-middle">Total Unit</th>
                            <th scope="col" class="text-center align-middle">Act</th>
                        </tr>
                        <tr class="filter-row">
                            <th><input type="text" class="form-control form-control-sm col-filter" data-col="0"
                                    placeholder="Cari..."></th>
                            <th>
                                <select class="form-control form-control-sm col-filter-server" id="cbojenis">
                                    <option value="">Semua</option>
                                    @foreach ($jenisList as $row)
                                        <option value="{{ $row->kd_jenis }}">{{ $row->nm_jenis }}</option>
                                    @endforeach
                                </select>
                            </th>
                            <th><input type="text" class="form-control form-control-sm col-filter" data-col="2"
                                    placeholder="Cari..."></th>
                            <th>
                                <select class="form-control form-control-sm col-filter-server" id="cbomerk">
                                    <option value="">Semua</option>
                                    @foreach ($merkList as $row)
                                        <option value="{{ $row->kd_merk }}">{{ $row->nm_merk }}</option>
                                    @endforeach
                                </select>
                            </th>
                            <th><input type="text" class="form-control form-control-sm col-filter" data-col="4"
                                    placeholder="Cari..."></th>
                            <th></th>
                            <th></th>
                            <th></th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal Detail Unit per Jenis -->
    <div class="modal fade" id="MesinUnitModal" tabindex="-1" aria-labelledby="MesinUnitModalLabel" aria-hidden="true"
        data-bs-backdrop="static">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header bg-sb text-white">
                    <h5 class="modal-title mb-0" id="MesinUnitModalLabel">Detail Unit Mesin</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-2">
                        <input type="text" id="unitSearch" class="form-control form-control-sm"
                            placeholder="Cari Serial Number / Lokasi / Supplier...">
                    </div>
                    <div class="table-responsive">
                        <table id="unitTable" class="table table-bordered table-sm align-middle mb-0">
                            <thead class="bg-sb">
                                <tr>
                                    <th scope="col" class="text-center">No</th>
                                    <th scope="col" class="text-center">QR Code</th>
                                    <th scope="col">Serial Number</th>
                                    <th scope="col">Lokasi</th>
                                    <th scope="col">Supplier</th>
                                </tr>
                            </thead>
                            <tbody id="unitTableBody"></tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Tutup</button>
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
    <script>
        function dataTableReload() {
            datatable.ajax.reload();
        }

        let datatable = $("#datatable").DataTable({
            ordering: true,
            responsive: false,
            processing: true,
            serverSide: false,
            paging: true,
            searching: true,
            scrollY: true,
            scrollX: true,
            scrollCollapse: false,
            orderCellsTop: true,
            ajax: {
                url: '{{ route('asset_mesin_master') }}',
                data: function(d) {
                    d.kd_jenis = $('#cbojenis').val();
                    d.kd_merk = $('#cbomerk').val();
                    d.id_supplier = $('#cbosupplier').val();
                    d.lokasi = $('#cbolokasi').val();
                }
            },
            columns: [{
                    data: 'sumber'
                }, // Sumber
                {
                    data: 'kd_jenis'
                }, // Kode Jenis
                {
                    data: 'nm_jenis'
                }, // Jenis
                {
                    data: 'kd_merk'
                }, // Kode Merk
                {
                    data: 'nm_merk'
                }, // Merk
                {
                    data: 'tipe'
                }, // Tipe
                {
                    data: 'total_unit',
                    className: 'text-center'
                }, // Total Unit
                {
                    data: null,
                    className: 'text-center',
                    render: function() {
                        return `
                    <button type="button" class="btn btn-sm btn-primary btn-detail-unit">
                        <i class="fas fa-eye"></i> Detail
                    </button>`;
                    },
                    orderable: false,
                    searchable: false
                }, // Act
            ],
        });

        // Filter teks per kolom (Kode Jenis, Kode Merk, Tipe) langsung di data yang sudah dimuat
        $('.col-filter').on('keyup', function() {
            datatable.column($(this).data('col')).search(this.value).draw();
        });

        // Filter dropdown (Jenis, Merk, Supplier, Lokasi) memuat ulang data dari server sesuai pilihan
        $('#cbojenis, #cbomerk, #cbosupplier, #cbolokasi').on('change', function() {
            dataTableReload();
        });

        // Cegah klik di filter row memicu sorting/seleksi baris datatable
        $('.filter-row').on('click', function(e) {
            e.stopPropagation();
        });

        // Klik tombol Detail: buka modal berisi daftar unit (per-unit) untuk jenis mesin tersebut
        $('#datatable tbody').on('click', '.btn-detail-unit', function() {
            let row = datatable.row($(this).closest('tr')).data();
            openMesinUnitModal(row);
        });

        function openMesinUnitModal(row) {
            $('#MesinUnitModalLabel').text(`${row.nm_jenis ?? '-'} - ${row.nm_merk ?? '-'} - ${row.tipe ?? '-'}`);
            $('#unitSearch').val('');

            if ($.fn.DataTable.isDataTable('#unitTable')) {
                $('#unitTable').DataTable().destroy();
            }

            let $body = $('#unitTableBody').empty();

            $.ajax({
                type: 'GET',
                url: '{{ route('asset_mesin_master_unit') }}',
                data: {
                    id_jenis: row.id_jenis,
                    sumber: row.sumber,
                    nm_jenis: row.nm_jenis,
                    nm_merk: row.nm_merk,
                    tipe: row.tipe
                },
                success: function(units) {
                    units.forEach(function(unit, i) {
                        let qrCell = unit.qr ?
                            `<img class="unit-qr-img" src="data:image/svg+xml;base64,${unit.qr}" data-unit-id="${unit.id}" title="Klik untuk print PDF">` :
                            `<span class="text-muted" title="Lengkapi Serial Number & Foto dahulu"><i class="fas fa-lock"></i></span>`;

                        $body.append(`
                    <tr>
                        <td class="text-center align-middle">${i + 1}</td>
                        <td class="text-center align-middle">${qrCell}</td>
                        <td class="align-middle">${unit.serial_number ?? '-'}</td>
                        <td class="align-middle">${unit.lokasi ?? '-'}</td>
                        <td class="align-middle">${unit.supplier ?? '-'}</td>
                    </tr>`);
                    });

                    $('#unitTable').DataTable({
                        dom: 'rt<"d-flex justify-content-between align-items-center"ip>',
                        paging: true,
                        pageLength: 10,
                        lengthChange: false,
                        searching: true,
                        ordering: false,
                        info: true,
                        autoWidth: false
                    });

                    $('#MesinUnitModal').modal('show');
                },
                error: function(xhr) {
                    console.error(xhr.responseText);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Gagal memuat data unit mesin.',
                    });
                }
            });
        }

        // Cari di tabel unit pada modal
        $(document).on('keyup', '#unitSearch', function() {
            if ($.fn.DataTable.isDataTable('#unitTable')) {
                $('#unitTable').DataTable().search(this.value).draw();
            }
        });

        // Klik QR Code untuk membuka PDF di tab baru, siap di-print / disimpan sebagai PDF
        $(document).on('click', '.unit-qr-img', function() {
            let unitId = $(this).data('unit-id');
            window.open(`{{ url('/asset_mesin_tambah/unit') }}/${unitId}/print_qr`, '_blank');
        });
    </script>
@endsection
