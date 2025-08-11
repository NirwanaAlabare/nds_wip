@extends('layouts.index')

@section('custom-link')
    {{-- <!-- DataTables -->
    <link rel="stylesheet" href="{{ asset('plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/datatables-responsive/css/responsive.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/datatables-buttons/css/buttons.bootstrap4.min.css') }}"> --}}

    <!-- DataTables CSS -->
    <link rel="stylesheet" href="{{ asset('plugins/datatables 2.0/jquery.dataTables.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/datatables 2.0/dataTables.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/datatables 2.0/fixedColumns.bootstrap4.min.css') }}">
    <!-- jQuery -->
    <script src="{{ asset('plugins/datatables 2.0/jquery-3.3.1.js') }}"></script>


    <!-- Select2 -->
    <link rel="stylesheet" href="{{ asset('plugins/select2/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}">

    <style>
        .modal-fullscreen-width {
            max-width: 90vw;
            /* Use full viewport width */
            width: 90vw;
            margin: 0 auto;
        }

        #photoPreview {
            margin-bottom: 1rem;
        }
    </style>
@endsection

@section('content')
    <!-- Modal -->
    <div class="modal fade" id="ModalDefect" tabindex="-1" aria-labelledby="modalDefectLabel" aria-hidden="true">
        <div class="modal-dialog modal-fullscreen-width">
            <div class="modal-content">

                <div class="modal-header bg-sb text-white">
                    <h5 class="modal-title" id="modalDefectLabel"><i class="fas fa-list"></i> Defect</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>

                <form id="DefectUploadForm" enctype="multipart/form-data">
                    @csrf

                    <div class="modal-body text-center" id="photoModalBody" style="max-height: 70vh; overflow-y: auto;">
                        <div id="photoPreview"></div>

                        <div class="mb-3">
                            <label class="form-label" for="photoInput">Upload / Capture Defect Photo</label>
                            <input type="file" name="photo" accept="image/*" capture="environment" class="form-control"
                                id="photoInput">
                        </div>

                        <div class="mb-3">
                            <label class="form-label" for="cbo_defect">Defect</label>
                            <select id="cbo_defect" name="cbo_defect" class="form-control form-control-sm select2bs4"
                                style="width: 100%; font-size: 0.875rem;" required>
                                <option value="">Pilih Defect</option>
                                @foreach ($data_defect as $dd)
                                    <option value="{{ $dd->isi }}">
                                        {{ $dd->tampil }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-4 text-end">
                            <button type="submit" class="btn btn-success">Upload</button>
                        </div>

                        <div class="row mb-3">
                            <input type="hidden" name="txtid" id="txtid">
                            <input type="hidden" name="txtid_jo" id="txtid_jo">
                            <div class="col-md-4">
                                <label for="txtno_invoice"><small><b>No. Packing List :</b></small></label>
                                <input type="text" id="txtno_invoice" name="txtno_invoice"
                                    class="form-control form-control-sm border-primary" readonly>
                            </div>
                            <div class="col-md-3">
                                <label for="txtsupplier"><small><b>Supplier :</b></small></label>
                                <input type="text" id="txtsupplier" name="txtsupplier"
                                    class="form-control form-control-sm border-primary" readonly>
                            </div>
                            <div class="col-md-2">
                                <label for="txtid_item"><small><b>ID Item :</b></small></label>
                                <input type="text" id="txtid_item" name="txtid_item"
                                    class="form-control form-control-sm border-primary" readonly>
                            </div>
                            <div class="col-md-3">
                                <label for="txtcolor"><small><b>Color :</b></small></label>
                                <input type="text" id="txtcolor" name="txtcolor"
                                    class="form-control form-control-sm border-primary" readonly>
                            </div>
                        </div>


                        <div class="table-responsive">
                            <table id="datatable_defect" class="table table-bordered w-100 text-nowrap">
                                <thead class="bg-sb">
                                    <tr style="text-align:center; vertical-align:middle">
                                        <th class="text-center align-middle">Photo</th>
                                        <th class="text-center align-middle">Defect Name</th>
                                        <th class="text-center align-middle">Act</th>
                                    </tr>
                                </thead>
                            </table>
                        </div>

                    </div>

                    <div class="modal-footer">
                        <a id="pdf_list_defect" name= "pdf_list_defect" href="javascript:void(0);"
                            onclick="pdf_list_defect();" class="btn btn-danger">
                            <i class="fas fa-file-pdf"></i> PDF
                        </a>
                    </div>
                </form>

            </div>
        </div>
    </div>

    <div class="card card-sb">
        <div class="card-header">
            <h5 class="card-title fw-bold mb-0"><i class="fas fa-list"></i> Packing List Fabric</h5>
        </div>
        <div class="card-body">
            <div class="d-flex align-items-end gap-3 mb-3">
                <div class="mb-3">
                    <label class="form-label"><small><b>Tgl BPB Awal</b></small></label>
                    <input type="date" class="form-control form-control-sm " id="tgl-awal" name="tgl_awal"
                        value="{{ $tgl_skrg_min_sebulan }}">
                </div>
                <div class="mb-3">
                    <label class="form-label"><small><b>Tgl BPB Akhir</b></small></label>
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
                    <a onclick="notif()" class="btn btn-outline-success position-relative btn-sm">
                        <i class="fas fa-file-excel fa-sm"></i>
                        Export Excel
                    </a>
                </div>
            </div>
            <div class="table-responsive">
                <table id="datatable" class="table table-bordered w-100 text-nowrap">
                    <thead class="bg-sb">
                        <tr style="text-align:center; vertical-align:middle">
                            <th scope="col"style="color: black;">Act</th>
                            <th scope="col">Tanggal</th>
                            <th scope="col">No. PL</th>
                            <th scope="col">Supplier</th>
                            <th scope="col">Buyer</th>
                            <th scope="col">Style</th>
                            <th scope="col">Color</th>
                            <th scope="col">ID Item</th>
                            <th scope="col">Jml Lot</th>
                            <th scope="col">Jml Roll</th>
                            <th scope="col">Notes</th>
                        </tr>
                        <tr>
                            <th></th> <!-- Empty cell for Act (no search input) -->
                            <th><input type="text" class="column-search form-control form-control-sm" /></th>
                            <th><input type="text" class="column-search form-control form-control-sm" /></th>
                            <th><input type="text" class="column-search form-control form-control-sm" /></th>
                            <th><input type="text" class="column-search form-control form-control-sm" /></th>
                            <th><input type="text" class="column-search form-control form-control-sm" /></th>
                            <th><input type="text" class="column-search form-control form-control-sm" /></th>
                            <th><input type="text" class="column-search form-control form-control-sm" /></th>
                            <th><input type="text" class="column-search form-control form-control-sm" /></th>
                            <th><input type="text" class="column-search form-control form-control-sm" /></th>
                            <th><input type="text" class="column-search form-control form-control-sm" /></th>
                        </tr>

                    </thead>
                </table>
            </div>
        </div>
    </div>
@endsection

@section('custom-script')
    <!-- DataTables & Plugins -->
    {{-- <script src="{{ asset('plugins/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-responsive/js/dataTables.responsive.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-responsive/js/responsive.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('plugins/select2/js/select2.full.min.js') }}"></script> --}}
    <script src="{{ asset('plugins/select2/js/select2.full.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables 2.0/dataTables.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables 2.0/dataTables.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables 2.0/dataTables.fixedColumns.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-rowsgroup/dataTables.rowsGroup.js') }}"></script>
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
            serverSide: false,
            paging: true,
            searching: true,
            scrollY: true,
            scrollX: true,
            scrollCollapse: false,
            fixedColumns: {
                leftColumns: 1
            },
            ajax: {
                url: '{{ route('qc_inspect_proses_packing_list') }}',
                data: function(d) {
                    d.dateFrom = $('#tgl-awal').val();
                    d.dateTo = $('#tgl-akhir').val();
                },
            },
            columns: [{
                    data: null,
                    orderable: false,
                    searchable: false,
                    render: function(data, type, row) {
                        return `
            <a class="btn btn-outline-primary position-relative btn-sm"
               href="{{ route('qc_inspect_proses_packing_list_det') }}/${data.id_lok_in_material}"
               title="Detail" target="_blank">
                Detail
            </a>
        <button class="btn btn-outline-warning position-relative btn-sm"
                onclick="openDefectModal('${data.id_lok_in_material}')"
                title="Defect">
            Defect
        </button>

            ${data.status_pdf === 'Y' ? `
                                                                                                                            <a class="btn btn-outline-danger position-relative btn-sm"
                                                                                                                            href="{{ route('export_qc_inspect') }}/${data.id_lok_in_material}" title="PDF" target="_blank">PDF
                                                                                                                            </a>` : ''}
        `;
                    }
                },

                {
                    data: 'tgl_dok_fix'
                },
                {
                    data: 'no_invoice'
                },
                {
                    data: 'supplier'
                },
                {
                    data: 'buyer'
                },
                {
                    data: 'styleno'
                },
                {
                    data: 'color'
                },
                {
                    data: 'id_item'
                },
                {
                    data: 'jml_lot'
                },
                {
                    data: 'jml_roll'
                },
                {
                    data: 'type_pch'
                },
            ],
            initComplete: function() {
                this.api().columns().every(function() {
                    var column = this;
                    $('input', this.header()).on('keyup change clear', function() {
                        if (column.search() !== this.value) {
                            column.search(this.value).draw();
                        }
                    });
                });
            },
            createdRow: function(row, data, dataIndex) {
                if (data.status_inspect === 'Y') {
                    $(row).addClass('table-success');
                }
            },
        });


        $('#ModalDefect').off('shown.bs.modal').on('shown.bs.modal', function() {
            const table = $('#datatable_defect').DataTable();
            setTimeout(() => {
                table.columns.adjust();
                if (table.responsive) {
                    table.responsive.recalc();
                }
            }, 100);
        });


        function openDefectModal(id) {
            // ✅ Clear input & preview
            $('#photoInput').val('');
            $('#photoPreview').empty();
            $('#cbo_defect').val('').trigger('change');

            const modalElement = document.getElementById('ModalDefect');
            const modal = new bootstrap.Modal(modalElement);

            console.log(id);

            // Fetch item data via AJAX
            $.ajax({
                url: '{{ route('get_info_modal_defect_packing_list') }}',
                method: 'GET',
                dataType: 'json',
                data: {
                    id: id
                },
                success: function(response) {
                    console.log('AJAX Success:', response);

                    // Populate fields
                    $('#txtid').val(id);
                    $('#txtid_jo').val(response.id_jo);
                    $('#txtno_invoice').val(response.no_invoice);
                    $('#txtid_item').val(response.id_item);
                    $('#txtcolor').val(response.color);
                    $('#txtsupplier').val(response.supplier);

                    // Show the modal
                    modal.show();

                    // Wait for modal to be fully shown
                    $('#ModalDefect').on('shown.bs.modal', function() {
                        const datatable = $('#datatable_defect').DataTable();

                        // Reload the table
                        datatable.ajax.reload(function() {
                            // Adjust columns after reload
                            setTimeout(() => {
                                datatable.columns.adjust();
                                if (datatable.responsive) {
                                    datatable.responsive.recalc();
                                }
                            }, 100); // slight delay helps layout
                        });
                    });
                },
                error: function(xhr, status, error) {
                    console.error('AJAX Error:', error);
                    alert('Failed to load defect data. Please try again.');
                }
            });
        }

        document.getElementById('photoInput').addEventListener('change', function(event) {
            const previewContainer = document.getElementById('photoPreview');
            previewContainer.innerHTML = ''; // Clear any existing preview

            const file = event.target.files[0];
            if (!file) return;

            const reader = new FileReader();

            reader.onload = function(e) {
                const img = document.createElement('img');
                img.src = e.target.result;
                img.alt = 'Preview';
                img.classList.add('img-fluid', 'rounded', 'shadow');
                img.style.maxHeight = '300px';
                previewContainer.appendChild(img);
            };

            reader.readAsDataURL(file);
        });



        $('#DefectUploadForm').on('submit', function(e) {
            e.preventDefault();

            const formData = new FormData(this);
            const modalEl = document.getElementById('ModalDefect');
            const modal = bootstrap.Modal.getInstance(modalEl);

            $.ajax({
                url: '{{ route('upload_modal_defect_photo') }}',
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(res) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Uploaded!',
                        text: 'Photo uploaded successfully',
                        timer: 2000,
                        showConfirmButton: false
                    });
                    // ✅ Clear input & preview
                    $('#photoInput').val('');
                    $('#photoPreview').empty();
                    $('#cbo_defect').val('').trigger('change');

                    // ✅ Optionally reload defect table
                    $('#datatable_defect').DataTable().ajax.reload();
                },
                error: function(xhr) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Upload Failed',
                        text: 'There was an error uploading the photo. Please try again.',
                    });
                    console.error(xhr.responseText);
                }
            });
        });


        // Initialize only once globally
        let datatable_defect = $('#datatable_defect').DataTable({
            ordering: false,
            responsive: true,
            processing: true,
            serverSide: false,
            paging: false,
            searching: true,
            scrollY: false,
            scrollX: true,
            scrollCollapse: false,
            autoWidth: false,
            ajax: {
                url: '{{ route('show_modal_defect_packing_list') }}',
                data: function(d) {
                    d.id_jo = $('#txtid_jo').val();
                    d.no_inv = $('#txtno_invoice').val();
                    d.id_item = $('#txtid_item').val();
                },
            },
            columns: [{
                    data: 'photo',
                    render: function(data) {
                        if (!data) return 'No Photo';

                        const imageUrl = `/nds_wip/public/storage/gambar_defect_inspect/${data}`;

                        return `
            <a href="${imageUrl}" target="_blank">
                <img src="${imageUrl}"
                     alt="Photo"
                     class="img-thumbnail"
                     style="max-height: 200px; max-width: 100%; object-fit: contain;" />
            </a>
        `;
                    }
                },

                {
                    data: 'critical_defect',
                    className: 'align-middle text-center'
                },
                {
                    data: 'id',
                    className: 'align-middle text-center',
                    render: function(data) {
                        return `<input type="button" class="btn btn-danger btn-sm" value="Delete" onclick="deleteDefect('${data}')" />`;
                    }
                },
            ],
        });

        function deleteDefect(id) {
            Swal.fire({
                title: 'Yakin ingin menghapus?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Hapus',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        type: 'POST',
                        url: '{{ route('delete_modal_defect_packing_list') }}', // Your delete route
                        data: {
                            _token: '{{ csrf_token() }}',
                            id: id
                        },
                        success: function(response) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil',
                                text: response.message,
                                timer: 1000,
                                showConfirmButton: false
                            });

                            const table = $('#datatable_defect').DataTable();

                            table.ajax.reload(() => {
                                setTimeout(() => {
                                    table.columns.adjust();
                                    if (table.responsive) {
                                        table.responsive.recalc();
                                    }
                                }, 100); // delay gives time to redraw DOM
                            });
                        },
                        error: function() {
                            Swal.fire('Gagal', 'Data tidak dapat dihapus', 'error');
                        }
                    });
                }
            });
        }

        function pdf_list_defect() {
            let id = $('#txtid').val();
            if (!id) {
                alert('ID not found!');
                return;
            }
            let url = `{{ route('export_pdf_list_defect', 0) }}`.replace('/0', '/' + id);
            window.open(url, '_blank');
        }
    </script>
@endsection
