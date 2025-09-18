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
        .checkbox-cell-wrapper {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 50px;
            height: 100%;
            padding: 0;
        }
    </style>
@endsection

@section('content')
    <div class="card card-sb">
        <div class="card-header">
            <h5 class="card-title fw-bold mb-0"><i class="fas fa-list"></i> Detail Report Shade Band</h5>
        </div>
        <form id="FormSB" enctype="multipart/form-data">
            @csrf
            <div class="card-body">

                <div class="row mb-3">
                    <div class="col-md-3">
                        <input type="hidden" id="txtid_jo" name="txtid_jo" value="{{ $id_jo }}"
                            class="form-control form-control-sm border-primary"readonly>
                        <label for="txtsupplier"><small><b>Supplier :</b></small></label>
                        <input type="text" id="txtsupplier" name="txtsupplier" value="{{ $supplier }}"
                            class="form-control form-control-sm border-primary"readonly>
                    </div>
                    <div class="col-md-3">
                        <label for="txtbuyer"><small><b>Buyer :</b></small></label>
                        <input type="text" id="txtbuyer" name="txtbuyer" value="{{ $buyer }}"
                            class="form-control form-control-sm border-primary" readonly>
                    </div>
                    <div class="col-md-3">
                        <label for="txtlast_update"><small><b>Last Update :</b></small></label>
                        <input type="text" id="txtlast_update" name="txtlast_update" value="{{ $tgl_update_fix }}"
                            class="form-control form-control-sm border-primary" readonly>
                    </div>
                    <div class="col-md-3">
                        <label for="txtgroup"><small><b>Group :</b></small></label>
                        <input type="text" id="txtgroup" name="txtgroup" value="{{ $group }}"
                            class="form-control form-control-sm border-primary" readonly>
                    </div>

                </div>

                <div class="row mb-3">
                    <div class="col-md-3">
                        <label for="txtws"><small><b>Worksheet :</b></small></label>
                        <input type="text" id="txtws" name="txtws" value="{{ $ws }}"
                            class="form-control form-control-sm border-primary" readonly>
                    </div>
                    <div class="col-md-3">
                        <label for="txtstyle"><small><b>Style :</b></small></label>
                        <input type="text" id="txtstyle" name="txtstyle" value="{{ $style }}"
                            class="form-control form-control-sm border-primary" readonly>
                    </div>
                    <div class="col-md-3">
                        <label for="txtcolor"><small><b>Color :</b></small></label>
                        <input type="text" id="txtcvolor" name="txtcvolor" value="{{ $color }}"
                            class="form-control form-control-sm border-primary" readonly>
                    </div>
                    <div class="col-md-3">
                        <label for="txtid_item"><small><b>ID Item :</b></small></label>
                        <input type="text" id="txtid_item" name="txtid_item" value="{{ $id_item }}"
                            class="form-control form-control-sm border-primary"readonly>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-12">
                        <label for="txtitem_desc"><small><b>Item :</b></small></label>
                        <input type="text" id="txtitem_desc" name="txtitem_desc" value="{{ $itemdesc }}"
                            class="form-control form-control-sm border-primary" readonly>
                    </div>
                </div>
            </div>
            <div class="card-header">
                <h5 class="card-title fw-bold mb-0"><i class="fas fa-list"></i> List Roll</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="datatable" class="table table-bordered table-hover align-middle text-nowrap w-100">
                        <thead class="bg-sb">
                            <tr>
                                <th scope="col" class="text-center align-middle">No. PL</th>
                                <th scope="col" class="text-center align-middle">Barcode</th>
                                <th scope="col" class="text-center align-middle">No. Roll</th>
                                <th scope="col" class="text-center align-middle">Lot</th>
                                <th scope="col" class="text-center align-middle">Qty</th>
                                <th scope="col" class="text-center align-middle">Unit</th>
                            </tr>
                            <tr>
                                <th><input type="text" class="column-search form-control form-control-sm w-100" /></th>
                                <th><input type="text" class="column-search form-control form-control-sm w-100" /></th>
                                <th><input type="text" class="column-search form-control form-control-sm w-100" /></th>
                                <th><input type="text" class="column-search form-control form-control-sm w-100" /></th>
                                <th><input type="text" class="column-search form-control form-control-sm w-100" /></th>
                                <th><input type="text" class="column-search form-control form-control-sm w-100" /></th>
                            </tr>
                        </thead>
                    </table>
                </div>

                <div class="row g-3 mt-2">
                    {{-- Left Column: Photo --}}
                    <div class="col-md-4">
                        <label class="form-label mb-1"><small><b>Foto</b></small></label>
                        <input type="file" name="photo" accept="image/*" capture="environment"
                            class="form-control form-control-sm" id="photoInput">
                        {{-- Preview Area --}}
                        <div class="mt-2">
                            <div id="photoPreview"></div>
                        </div>
                    </div>

                    {{-- Right Column: Status + Keterangan --}}
                    <div class="col-md-8">
                        <div class="row g-2">
                            {{-- Status --}}
                            <div class="col-md-6">
                                <label class="form-label mb-1"><small><b>Result</b></small></label>
                                <select name="result" id="result" class="form-select form-select-sm">
                                    <option value="">-- Pilih Result --</option>
                                    <option value="Pass">Pass</option>
                                    <option value="Reject">Reject</option>
                                    <option value="Pass With Condition">Pass With Condition</option>
                                </select>
                            </div>

                            {{-- Keterangan --}}
                            <div class="col-md-6">
                                <label class="form-label mb-1"><small><b>Keterangan</b></small></label>
                                <input type="text" name="txtket" id="txtket" class="form-control form-control-sm"
                                    placeholder="Isi keterangan...">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="mt-3 text-end">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i> Save
                    </button>
                </div>
            </div>
        </form>

    </div>
@endsection

@section('custom-script')
    <!-- DataTables & Plugins -->
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
            show_list_detail();
        })

        let datatable = $("#datatable").DataTable({
            ordering: false,
            responsive: true,
            processing: true,
            serverSide: false,
            paging: false,
            searching: true,
            scrollY: true,
            scrollX: true,
            scrollCollapse: false,
            autoWidth: false,
            ajax: {
                url: '{{ route('qc_inspect_report_shade_band_detail') }}',
                data: function(d) {
                    d.id_item = $('#txtid_item').val();
                    d.id_jo = $('#txtid_jo').val();
                    d.group = $('#txtgroup').val();
                },
            },
            columns: [{
                    data: 'no_invoice',
                    className: 'text-center'
                },
                {
                    data: 'barcode',
                    className: 'text-center'
                },
                {
                    data: 'no_roll_buyer',
                    className: 'text-center'
                },
                {
                    data: 'no_lot',
                    className: 'text-center'
                },
                {
                    data: 'qty_aktual',
                    className: 'text-center'
                },
                {
                    data: 'satuan',
                    className: 'text-center'
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
            }
        });
    </script>
    <script>
        function show_list_detail() {
            const previewContainer = document.getElementById('photoPreview');
            const input = document.getElementById('photoInput');

            // Reset preview and input
            if (previewContainer) previewContainer.innerHTML = '<p class="text-muted">Loading photo...</p>';
            if (input) input.value = '';

            // Fetch actual photo filename from backend
            $.ajax({
                url: '{{ route('get_photo_shade_band') }}',
                method: 'GET',
                data: {
                    id_item: $('#txtid_item').val(),
                    id_jo: $('#txtid_jo').val(),
                    group: $('#txtgroup').val(),
                    _token: '{{ csrf_token() }}'
                },
                success: function(res) {
                    previewContainer.innerHTML = ''; // Clear loading text


                    // ✅ Update result and ket
                    document.getElementById('result').value = res.result;
                    document.getElementById('txtket').value = res.ket;
                    // Show photo
                    if (res.photo) {
                        const timestamp = new Date().getTime();
                        const imgPath = `/nds_wip/public/storage/gambar_shade_band/${res.photo}?t=${timestamp}`;
                        previewContainer.innerHTML = `
    <div style="position: relative; display: inline-block;">
        <img src="${imgPath}" alt="Blanket Photo"
             class="img-fluid rounded border mb-2" style="max-height: 400px; display: block;">

<input type="button"
       value="Delete Photo"
       onclick="handlePhotoDelete('${res.photo}')"
       class="btn btn-danger btn-sm"
       style="
           position: absolute;
           bottom: -30px;
           left: 50%;
           transform: translateX(-50%);
           z-index: 10;
           opacity: 0.9;
       ">

    </div>
`;
                    } else {
                        previewContainer.innerHTML = `<p class="text-muted">No photo available yet.</p>`;
                    }
                },

                error: function(xhr) {
                    previewContainer.innerHTML = `<p class="text-danger">Error loading photo.</p>`;
                    console.error(xhr.responseText);
                }
            });
        }

        // Instant preview when selecting a photo
        document.getElementById('photoInput').addEventListener('change', function(event) {
            const file = event.target.files[0];

            if (!file || !file.type.startsWith('image/')) return;

            const reader = new FileReader();
            reader.onload = function(e) {
                const img = new Image();
                img.src = e.target.result;

                img.onload = function() {
                    const canvas = document.createElement('canvas');
                    const MAX_WIDTH = 1024;
                    const MAX_HEIGHT = 1024;
                    let width = img.width;
                    let height = img.height;

                    if (width > height && width > MAX_WIDTH) {
                        height *= MAX_WIDTH / width;
                        width = MAX_WIDTH;
                    } else if (height > MAX_HEIGHT) {
                        width *= MAX_HEIGHT / height;
                        height = MAX_HEIGHT;
                    }

                    canvas.width = width;
                    canvas.height = height;
                    const ctx = canvas.getContext('2d');
                    ctx.drawImage(img, 0, 0, width, height);

                    // Convert to blob with quality compression (e.g. 0.7)
                    canvas.toBlob(function(blob) {
                        // Preview compressed image
                        document.getElementById('photoPreview').innerHTML = `
                    <img src="${URL.createObjectURL(blob)}" class="img-fluid rounded border mb-2" style="max-height: 400px;">
                `;

                        // Replace original file in form data
                        const compressedFile = new File([blob], file.name, {
                            type: 'image/jpeg'
                        });

                        // Store it for uploading later
                        const dataTransfer = new DataTransfer();
                        dataTransfer.items.add(compressedFile);
                        document.getElementById('photoInput').files = dataTransfer.files;
                    }, 'image/jpeg', 0.7);
                };
            };

            reader.readAsDataURL(file);
        });


        $('#FormSB').on('submit', function(e) {
            e.preventDefault();

            const formData = new FormData(this);

            $.ajax({
                url: '{{ route('save_report_shade_band_detail') }}',
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


        function handlePhotoDelete(photoName) {
            Swal.fire({
                title: 'Are you sure?',
                text: 'This photo will be permanently deleted!',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, delete it!',
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: '{{ route('delete_photo_shade_band') }}',
                        method: 'POST',
                        data: {
                            photo: photoName,
                            _token: '{{ csrf_token() }}'
                        },
                        success: function(res) {
                            Swal.fire('Deleted!', res.message, 'success');
                            document.getElementById('photoPreview').innerHTML =
                                `<p class="text-muted">Photo deleted.</p>`;
                        },
                        error: function(xhr) {
                            Swal.fire('Error', 'Failed to delete photo.', 'error');
                            console.error(xhr.responseText);
                        }
                    });
                }
            });
        }
    </script>
@endsection
