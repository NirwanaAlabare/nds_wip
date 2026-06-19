@extends('layouts.index')

@section('custom-link')
    <!-- DataTables -->
    <link rel="stylesheet" href="{{ asset('plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/datatables-responsive/css/responsive.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/datatables-buttons/css/buttons.bootstrap4.min.css') }}">

    <!-- DataTables CSS -->
    {{-- <link rel="stylesheet" href="{{ asset('plugins/datatables 2.0/jquery.dataTables.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/datatables 2.0/dataTables.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/datatables 2.0/fixedColumns.bootstrap4.min.css') }}"> --}}
    <!-- jQuery -->
    <script src="{{ asset('plugins/datatables 2.0/jquery-3.3.1.js') }}"></script>


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

        .td-truncate {
            max-width: 220px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
    </style>
@endsection

@section('content')
    <div class="card card-sb">
        <div class="card-header">
            <h5 class="card-title fw-bold mb-0"><i class="fas fa-plus"></i> Tambah Mesin</h5>
        </div>
        <div class="card-body">
            <div class="mb-3">
                <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal"
                    data-bs-target="#NewMesinModal">
                    <i class="fas fa-plus"></i> New
                </button>
            </div>
            <div class="table-responsive">
                <table id="datatable" class="table table-bordered table-hover align-middle text-nowrap w-100">
                    <thead class="bg-sb">
                        <tr>
                            <th scope="col" class="text-center align-middle">Jenis</th>
                            <th scope="col" class="text-center align-middle">Merk</th>
                            <th scope="col" class="text-center align-middle">Tipe</th>
                            <th scope="col" class="text-center align-middle">Supplier</th>
                            <th scope="col" class="text-center align-middle">Act</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal New Mesin -->
    <div class="modal fade" id="NewMesinModal" tabindex="-1" aria-labelledby="NewMesinModalLabel" aria-hidden="true"
        data-bs-backdrop="static">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header bg-sb text-white">
                    <h5 class="modal-title" id="NewMesinModalLabel">Tambah Mesin</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row mb-3 align-items-center">
                        <label for="txttanggal_transaksi" class="col-md-3 col-form-label"><small><b>Tanggal
                                    Transaksi :</b></small></label>
                        <div class="col-md-9">
                            <input type="date" id="txttanggal_transaksi" name="txttanggal_transaksi"
                                class="form-control form-control-sm" value="{{ now()->format('Y-m-d') }}">
                        </div>
                    </div>
                    <div class="row mb-3 align-items-center">
                        <label for="cbonomor_bpb" class="col-md-3 col-form-label"><small><b>Nomor BPB :</b></small></label>
                        <div class="col-md-9">
                            <select id="cbonomor_bpb" name="cbonomor_bpb"
                                class="form-control form-control-sm select2bs4 border-primary" style="width: 100%;">
                                <option value="">-- Pilih Nomor BPB --</option>
                                @foreach ($bpbList as $row)
                                    <option value="{{ $row->bpbno }}">{{ $row->bpbno_int }} - {{ $row->supplier }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <div class="table-responsive">
                                <table id="bpbDetailTable"
                                    class="table table-bordered table-hover table-sm align-middle text-nowrap w-100">
                                    <thead class="bg-sb">
                                        <tr>
                                            <th scope="col" class="text-center align-middle">ID Item</th>
                                            <th scope="col" class="text-center align-middle">Nama Barang</th>
                                            <th scope="col" class="text-center align-middle">Qty</th>
                                            <th scope="col" class="text-center align-middle">Unit</th>
                                            <th scope="col" class="text-center align-middle">Act</th>
                                        </tr>
                                    </thead>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-success btn-sm" id="saveNewMesinButton"
                        onclick="notif();">Save</button>
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Detail Mesin (per unit sesuai qty) -->
    <div class="modal fade" id="MesinDetailModal" tabindex="-1" aria-labelledby="MesinDetailModalLabel" aria-hidden="true"
        data-bs-backdrop="static">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-sb text-white">
                    <h5 class="modal-title" id="MesinDetailModalLabel">Detail Mesin</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row mb-3 align-items-center">
                        <label for="cbojenis_detail" class="col-md-3 col-form-label"><small><b>Jenis :</b></small></label>
                        <div class="col-md-9">
                            <select id="cbojenis_detail" class="form-control form-control-sm select2bs4 border-primary"
                                style="width: 100%;">
                                <option value="">-- Pilih Jenis --</option>
                            </select>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm align-middle text-nowrap w-100">
                            <thead class="bg-sb">
                                <tr>
                                    <th scope="col" class="text-center align-middle" style="width: 60px;">No</th>
                                    <th scope="col" class="text-center align-middle">Jenis</th>
                                    <th scope="col" class="text-center align-middle">Merk</th>
                                    <th scope="col" class="text-center align-middle">Tipe</th>
                                    <th scope="col" class="text-center align-middle">Serial Number</th>
                                </tr>
                            </thead>
                            <tbody id="mesinDetailBody">
                                <tr>
                                    <td colspan="5" class="text-center">Pilih jenis mesin terlebih dahulu</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-success btn-sm" onclick="notif();">Save</button>
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
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
    {{-- <script src="{{ asset('plugins/select2/js/select2.full.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables 2.0/dataTables.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables 2.0/dataTables.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables 2.0/dataTables.fixedColumns.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-rowsgroup/dataTables.rowsGroup.js') }}"></script> --}}
    <script>
        // Select2 Autofocus
        $(document).on('select2:open', () => {
            document.querySelector('.select2-search__field').focus();

            // Pastikan dropdown select2 tampil di atas modal/backdrop yang sedang terbuka (modal bertumpuk)
            let maxZ = 1051;
            $('.modal.show').each(function() {
                let z = parseInt($(this).css('z-index')) || 0;
                if (z > maxZ) maxZ = z;
            });
            $('.select2-dropdown').last().css('z-index', maxZ + 10);
        });

        // Initialize Select2 Elements
        $('.select2').select2();

        // Initialize Select2BS4 Elements
        // dropdownParent diarahkan ke modal terdekat agar pencarian tetap bisa diketik
        // (modal Bootstrap 5 menahan focus, sehingga dropdown yang nempel di <body> jadi tidak bisa diketik)
        $('.select2bs4').each(function() {
            let $modal = $(this).closest('.modal');

            $(this).select2({
                theme: 'bootstrap4',
                width: 'resolve', // Ensures it respects the 100% width from inline style or Bootstrap
                dropdownParent: $modal.length ? $modal : $(document.body)
            });
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

        // Master jenis mesin untuk dropdown Jenis di modal Detail Mesin (difilter per id_supplier)
        const jenisList = @json($jenisList);

        // Klik di mana saja pada input tanggal akan membuka date picker
        document.getElementById('txttanggal_transaksi').addEventListener('click', function() {
            this.showPicker();
        });
    </script>
    <script>
        function dataTableReload() {
            datatable.ajax.reload();
        }

        let datatable = $("#datatable").DataTable({
            ordering: false,
            responsive: true,
            processing: true,
            serverSide: false,
            paging: true,
            searching: true,
            scrollY: true,
            scrollX: true,
            scrollCollapse: false,
            ajax: {
                url: '{{ route('asset_master_jenis_mesin') }}',
            },
            columns: [{
                    data: 'jenis'
                }, // Jenis
                {
                    data: 'merk'
                }, // Merk
                {
                    data: 'tipe'
                }, // Tipe
                {
                    data: 'supplier'
                }, // Supplier
                {
                    data: 'id_jenis',
                    render: function(data) {
                        return `
                    <div class="text-center">
                        <button class="btn btn-sm btn-warning" onclick="editData(${data})"
                                data-bs-toggle="modal" data-bs-target="#EditJenisMesinModal">
                            <i class="fa fa-edit"></i>
                        </button>
                        <button class="btn btn-sm btn-danger" onclick="deleteData(${data})">
                            <i class="fa fa-trash"></i>
                        </button>
                    </div>`;
                    },
                    orderable: false,
                    searchable: false
                }
            ],
        });

        let bpbDetailTable = $("#bpbDetailTable").DataTable({
            ordering: false,
            responsive: true,
            processing: true,
            serverSide: false,
            paging: true,
            searching: true,
            scrollY: true,
            scrollX: true,
            scrollCollapse: false,
            ajax: {
                url: '{{ route('asset_mesin_tambah_bpb_detail') }}',
                data: function(d) {
                    d.bpbno = $('#cbonomor_bpb').val();
                }
            },
            columns: [{
                    data: 'id_item'
                }, // Bpb No
                {
                    data: 'itemdesc',
                    className: 'td-truncate',
                    render: function(data) {
                        return `<span title="${data ?? ''}">${data ?? '-'}</span>`;
                    }
                }, // Nama Barang
                {
                    data: 'qty'
                }, // Qty
                {
                    data: 'unit'
                }, // Unit
                {
                    data: null,
                    render: function(data, type, row) {
                        return `
                    <div class="text-center">
                        <button type="button" class="btn btn-sm btn-primary"
                            onclick='openMesinDetailModal(${row.qty}, "${row.bpbno}", ${row.id_supplier}, "${row.itemdesc}")'>
                            <i class="fa fa-check"></i> Simpan
                        </button>
                    </div>`;
                    },
                    orderable: false,
                    searchable: false
                }
            ],
        });

        // Buka modal Detail Mesin: dropdown Jenis difilter berdasarkan id_supplier BPB yang sama
        let mesinDetailQty = 0;

        function openMesinDetailModal(qty, bpbno, idSupplier, itemdesc) {
            mesinDetailQty = parseInt(qty) || 0;

            let options = jenisList
                .filter(j => String(j.id_supplier) === String(idSupplier))
                .map(j => `<option value="${j.id_jenis}">${j.jenis} - ${j.merk} ${j.tipe}</option>`)
                .join('');

            $('#cbojenis_detail').html('<option value="">-- Pilih Jenis --</option>' + options).val('')
                .trigger('change');
            $('#mesinDetailBody').html(
                '<tr><td colspan="5" class="text-center">Pilih jenis mesin terlebih dahulu</td></tr>');
            $('#MesinDetailModalLabel').text('Detail Mesin ' + itemdesc);
            $('#MesinDetailModal').modal('show');
        }

        // Tampilkan baris Serial Number sebanyak qty setelah Jenis dipilih
        $('#cbojenis_detail').on('change', function() {
            let jenisId = $(this).val();
            let $body = $('#mesinDetailBody');

            if (!jenisId) {
                $body.html(
                    '<tr><td colspan="5" class="text-center">Pilih jenis mesin terlebih dahulu</td></tr>');
                return;
            }

            let jenisData = jenisList.find(j => String(j.id_jenis) === String(jenisId));

            let rows = '';
            for (let i = 1; i <= mesinDetailQty; i++) {
                rows += `<tr>
                    <td class="text-center">${i}</td>
                    <td class="text-center">${jenisData?.jenis ?? '-'}</td>
                    <td class="text-center">${jenisData?.merk ?? '-'}</td>
                    <td class="text-center">${jenisData?.tipe ?? '-'}</td>
                    <td>
                        <input type="text" class="form-control form-control-sm"
                            style="text-transform: uppercase;"
                            oninput="this.value = this.value.toUpperCase();">
                    </td>
                </tr>`;
            }

            $body.html(rows || '<tr><td colspan="5" class="text-center">Qty tidak valid</td></tr>');
        });

        // Stacking z-index agar modal kedua tampil rapi di atas modal pertama
        $(document).on('show.bs.modal', '.modal', function() {
            let zIndex = 1050 + (10 * $('.modal.show').length);
            $(this).css('z-index', zIndex);
        });

        $(document).on('shown.bs.modal', '.modal', function() {
            $('.modal-backdrop').not('.modal-stack').css('z-index', $(this).css('z-index') - 1).addClass(
                'modal-stack');
        });

        // Reload tabel detail BPB saat nomor BPB dipilih
        $('#cbonomor_bpb').on('change', function() {
            bpbDetailTable.ajax.reload();
        });

        // Perbaiki lebar kolom saat modal ditampilkan (DataTables tidak bisa hitung lebar saat modal masih hidden)
        $('#NewMesinModal').on('shown.bs.modal', function() {
            bpbDetailTable.columns.adjust();
        });

        // Reset form setiap kali modal dibuka
        $('#NewMesinModal').on('show.bs.modal', function() {
            $('#txttanggal_transaksi').val('{{ now()->format('Y-m-d') }}');
            $('#cbonomor_bpb').val(null).trigger('change');
        });
    </script>
@endsection
