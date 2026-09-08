@extends('layouts.index')

@section('custom-link')
    <!-- DataTables -->
    <link rel="stylesheet" href="{{ asset('plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/datatables-responsive/css/responsive.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/datatables-buttons/css/buttons.bootstrap4.min.css') }}">

    <style type="text/css">
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
    </style>
@endsection

@section('content')
    <div class="card card-sb">
        <div class="card-header">
            <h5 class="card-title fw-bold mb-0"><i class="fas fa-clipboard-check"></i> Opname Mesin</h5>
        </div>
        <div class="card-body">
            <div class="mb-3 d-flex justify-content-between align-items-center">
                <a href="{{ route('create_asset_mesin_opname') }}" class="btn btn-primary btn-sm">
                    <i class="fas fa-plus"></i> New
                </a>
            </div>
            <div class="mb-3 d-flex align-items-end gap-2 flex-wrap">
                <div>
                    <label for="txttgl_awal" class="col-form-label"><small><b>Tgl Awal :</b></small></label>
                    <input type="date" id="txttgl_awal" class="form-control form-control-sm">
                </div>
                <div>
                    <label for="txttgl_akhir" class="col-form-label"><small><b>Tgl Akhir :</b></small></label>
                    <input type="date" id="txttgl_akhir" class="form-control form-control-sm">
                </div>
                <button type="button" class="btn btn-primary btn-sm" onclick="dataTableReload();">
                    <i class="fas fa-search"></i> Search
                </button>
                <button type="button" class="btn btn-success btn-sm" onclick="export_excel_opname_mesin();">
                    <i class="fas fa-file-excel"></i> Export
                </button>
            </div>

            <div class="table-responsive">
                <table id="datatable" class="table table-bordered table-hover align-middle text-nowrap w-100">
                    <thead class="bg-sb">
                        <tr>
                            <th scope="col" class="text-center align-middle">Tgl. Opname</th>
                            <th scope="col" class="text-center align-middle">Lokasi</th>
                            <th scope="col" class="text-center align-middle">Total Mesin</th>
                            <th scope="col" class="text-center align-middle">Act</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal List Mesin per tanggal & lokasi -->
    <div class="modal fade" id="DetailOpnameModal" tabindex="-1" aria-labelledby="DetailOpnameModalLabel"
        aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header bg-sb text-white">
                    <h5 class="modal-title mb-0" id="DetailOpnameModalLabel">List Mesin</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="table-responsive">
                        <table id="detailTable" class="table table-bordered table-sm align-middle mb-0 w-100">
                            <thead class="bg-sb">
                                <tr>
                                    <th scope="col" class="text-center">No</th>
                                    <th scope="col" class="text-center">Sumber</th>
                                    <th scope="col">Kode QR</th>
                                    <th scope="col">Jenis</th>
                                    <th scope="col">Merk</th>
                                    <th scope="col">Tipe</th>
                                    <th scope="col">Serial Number</th>
                                    <th scope="col">User</th>
                                    <th scope="col">Waktu Scan</th>
                                </tr>
                            </thead>
                            <tbody id="detailTableBody"></tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <small class="me-auto text-muted" id="detailTotal"></small>
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
        // Modul Asset: senyapkan alert bawaan DataTables saat ajax gagal, cukup dicatat di console
        $.fn.dataTable.ext.errMode = function(settings, techNote, message) {
            console.error('DataTable ajax error:', message);
        };
    </script>
    <script>
        // Default filter: awal bulan berjalan s.d. hari ini
        let todayStr = new Date().toISOString().slice(0, 10);
        $('#txttgl_awal').val(todayStr.slice(0, 8) + '01');
        $('#txttgl_akhir').val(todayStr);

        function dataTableReload() {
            datatable.ajax.reload();
        }

        let datatable = $('#datatable').DataTable({
            ordering: true,
            responsive: false,
            processing: true,
            serverSide: false,
            paging: true,
            searching: true,
            scrollX: true,
            ajax: {
                url: '{{ route('asset_mesin_opname') }}',
                data: function(d) {
                    d.tgl_awal = $('#txttgl_awal').val();
                    d.tgl_akhir = $('#txttgl_akhir').val();
                }
            },
            columns: [
                { data: 'tgl_opname', className: 'text-center' }, // Tgl. Opname
                { data: 'lokasi', defaultContent: '-' }, // Lokasi
                { data: 'total_mesin', className: 'text-center' }, // Total Mesin
                {
                    data: null,
                    className: 'text-center',
                    orderable: false,
                    render: function(row) {
                        // Tombol "+" membuka halaman input dengan lokasi & tanggal opname ini terkunci
                        let urlTambah = '{{ route('create_asset_mesin_opname') }}' +
                            `?lokasi=${encodeURIComponent(row.lokasi ?? '')}&tgl=${encodeURIComponent(row.tgl_trans ?? '')}`;

                        return `
                            <button type="button" class="btn btn-sm btn-primary btn-view" title="Lihat list mesin">
                                <i class="fas fa-eye"></i>
                            </button>
                            <a href="${urlTambah}" class="btn btn-sm btn-success" title="Tambah / kurangi mesin">
                                <i class="fas fa-plus"></i>
                            </a>`;
                    }
                }, // Act
            ],
        });

        // Export Excel mengikuti filter tanggal yang sedang aktif di tabel
        function export_excel_opname_mesin() {
            let tgl_awal = $('#txttgl_awal').val();
            let tgl_akhir = $('#txttgl_akhir').val();

            Swal.fire({
                title: 'Please Wait,',
                html: 'Exporting Data...',
                didOpen: () => {
                    Swal.showLoading();
                },
                allowOutsideClick: false,
            });

            $.ajax({
                type: 'get',
                url: '{{ route('export_excel_asset_mesin_opname') }}',
                data: {
                    tgl_awal: tgl_awal,
                    tgl_akhir: tgl_akhir
                },
                xhrFields: {
                    responseType: 'blob'
                },
                success: function(response) {
                    Swal.close();
                    Swal.fire({
                        title: 'Data Berhasil Di Export!',
                        icon: 'success',
                        showConfirmButton: true,
                        allowOutsideClick: false
                    });

                    let blob = new Blob([response]);
                    let link = document.createElement('a');
                    link.href = window.URL.createObjectURL(blob);
                    link.download = 'Stok Opname Mesin ' + tgl_awal + ' sd ' + tgl_akhir + '.xlsx';
                    link.click();
                },
                error: function(xhr) {
                    console.error(xhr.responseText);
                    Swal.close();
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Gagal export data ke Excel.',
                    });
                }
            });
        }

        // Lihat isi opname satu tanggal & lokasi (read-only)
        $('#datatable').on('click', '.btn-view', function() {
            let row = datatable.row($(this).closest('tr')).data();

            $('#DetailOpnameModalLabel').text(`List Mesin - ${row.tgl_opname} - ${row.lokasi ?? '-'}`);
            $('#detailTotal').text('');

            let $body = $('#detailTableBody').empty();

            $.ajax({
                type: 'GET',
                url: '{{ route('getdata_asset_mesin_opname') }}',
                data: {
                    cbolok: row.lokasi,
                    tgl: row.tgl_trans
                },
                success: function(res) {
                    let rows = res.data ?? [];
                    let sewa = rows.filter(r => r.sumber === 'SEWA').length;

                    rows.forEach(function(r, i) {
                        let warna = r.sumber === 'SEWA' ? 'bg-warning text-dark' : 'bg-primary';
                        let badge = r.sumber ?
                            `<span class="badge ${warna}">${r.sumber}</span>` :
                            '<span class="badge bg-secondary">-</span>';

                        $body.append(`
                            <tr>
                                <td class="text-center">${i + 1}</td>
                                <td class="text-center">${badge}</td>
                                <td>${r.kode_qr ?? '-'}</td>
                                <td>${r.nm_jenis ?? '-'}</td>
                                <td>${r.nm_merk ?? '-'}</td>
                                <td>${r.tipe ?? '-'}</td>
                                <td>${r.serial_number ?? '-'}</td>
                                <td>${r.created_by ?? '-'}</td>
                                <td>${r.created_at ?? '-'}</td>
                            </tr>`);
                    });

                    $('#detailTotal').text(
                        `Total : ${rows.length} mesin (Pembelian : ${rows.length - sewa}, Sewa : ${sewa})`);
                    $('#DetailOpnameModal').modal('show');
                },
                error: function(xhr) {
                    console.error(xhr.responseText);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Gagal memuat list mesin.',
                    });
                }
            });
        });
    </script>
@endsection
