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
            <h5 class="card-title"><i class="fa-solid fa-sliders"></i> Modify Loading Line</h5>
        </div>
        <div class="card-body">
            <div class="row justify-content-between align-items-start g-3 mb-3">
                <div class="col-md-12">
                    <label class="form-label">Stocker</label>
                    <textarea class="form-control" name="text" id="stocker_ids" rows="5"></textarea>
                </div>
                <div class="col-md-6">
                    <div class="form-text">Contoh : <br>&nbsp;&nbsp;&nbsp;<b> STK-12332</b><br>&nbsp;&nbsp;&nbsp;<b> STK-12433</b><br>&nbsp;&nbsp;&nbsp;<b> STK-12651</b></div>
                </div>
                <div class="col-md-6">
                    <button class="btn btn-block btn-sb-secondary mt-1" onclick="currentStockerTableReload()"><i class="fa fa-search"></i> Check</button>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table table table-bordered w-100'" id="current-stocker-table">
                    <thead>
                        <tr>
                            <th>IDs</th>
                            <th>Stocker IDs</th>
                            <th>Tanggal Loading</th>
                            <th>Line</th>
                            <th>No. WS</th>
                            <th>Color</th>
                            <th>Size</th>
                            <th>Lokasi</th>
                            <th>Trolley</th>
                            <th>Qty</th>
                            <th>DC Qty</th>
                            <th>Secondary In Qty</th>
                            <th>Secondary Inhouse Qty</th>
                            <th>Loading Qty</th>
                            <th>Range Awal Akhir</th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>
            <div class="mt-3">
                <div class="border rounded border-danger p-3 mb-3 {{ Auth::user()->roles->whereIn("nama_role", ["superadmin"])->count() > 0 ? "" : "d-none" }}">
                    <h5 class="text-danger">Critical Update</h5>
                    <hr class="border-danger">
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label class="form-label text-danger">Tanggal</label>
                            <input type="date" class="form-control" name="tanggal_loading" id="tanggal_loading" {{ Auth::user()->roles->whereIn("nama_role", ["superadmin"])->count() > 0 ? "" : "readonly" }}>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label text-danger">Reject (-)</label>
                            <input type="number" class="form-control" name="qty_reject" id="qty_reject" {{ Auth::user()->roles->whereIn("nama_role", ["superadmin"])->count() > 0 ? "" : "readonly" }}>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label text-danger">Replace (+)</label>
                            <input type="number" class="form-control" name="qty_replace" id="qty_replace" {{ Auth::user()->roles->whereIn("nama_role", ["superadmin"])->count() > 0 ? "" : "readonly" }}>
                        </div>
                    </div>
                </div>
                <div class="row align-items-end">
                    <div class="col-md-4">
                        <label class="form-label">Line</label>
                        <select class="form-select select2bs4" name="line_id" id="line_id">
                            <option value="">Pilih Line</option>
                            @foreach ($lines as $line)
                                <option value="{{ $line->line_id }}">{{ strtoupper(str_replace("_", " ", $line->username)) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Total</label>
                        <input type="number" class="form-control" id="total_update" readonly>
                    </div>
                    <div class="col-md-4">
                        <div class="d-flex gap-3">
                            <div class="w-50">
                                <button class="btn btn-sb btn-block" onclick="updateStocker()">UPDATE</button>
                            </div>
                            @role('superadmin')
                                <div class="w-50">
                                    <button class="btn btn-danger btn-block" onclick="deleteStocker()">DELETE</button>
                                </div>
                            @endrole
                        </div>
                    </div>
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
    <!-- Select2 -->
    <script src="{{ asset('plugins/select2/js/select2.full.min.js') }}"></script>
    <script>
        // Select2 Autofocus
        $(document).on('select2:open', () => {
            document.querySelector('.select2-search__field').focus();
        });

        // Initialize Select2 Elements
        $('.select2').select2()

        // Initialize Select2BS4 Elements
        $('.select2bs4').select2({
            theme: 'bootstrap4',
            containerCssClass: 'rounded'
        })

        let currentStockerTable = $("#current-stocker-table").DataTable({
            ordering: false,
            processing: true,
            serverSide: true,
            ajax: {
                url: '{{ route("modify-loading-line") }}',
                data: function (d) {
                    d.stocker_ids = $('#stocker_ids').val();
                },
            },
            columns: [
                {
                    data:'ids'
                },
                {
                    data:'stocker_ids'
                },
                {
                    data:'tanggal_loading'
                },
                {
                    data:'nama_line'
                },
                {
                    data:'act_costing_ws'
                },
                {
                    data:'color'
                },
                {
                    data:'size'
                },
                {
                    data:'lokasi'
                },
                {
                    data:'trolley'
                },
                {
                    data:'qty'
                },
                {
                    data:'dc_qty'
                },
                {
                    data:'secondary_in_qty'
                },
                {
                    data:'secondary_inhouse_qty'
                },
                {
                    data:'loading_qty'
                },
                {
                    data:'range_awal_akhir'
                }
            ],
            columnDefs: [
                {
                    targets: [0],
                    className: "hidden",
                    render: (data, type, row, meta) => {
                        // Hidden Size Input
                        return '<input type="hidden" id="id-' + meta.row + '" name="ids['+meta.row+']" value="' + data + '" readonly />'
                    }
                },
                {
                    targets: [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14],
                    className: "text-nowrap"
                }
            ],
        });

        function currentStockerTableReload() {
            $("#current-stocker-table").DataTable().ajax.reload(() => {
                $("#total_update").val(currentStockerTable.page.info().recordsTotal);
            });
        }

        function updateStocker() {
            Swal.fire({
                title: 'Please Wait...',
                html: 'Updating Data...  <br><br> <b>0</b>s elapsed...',
                didOpen: () => {
                    Swal.showLoading();

                    let estimatedTime = 0;
                    const estimatedTimeElement = Swal.getPopup().querySelector("b");
                    estimatedTimeInterval = setInterval(() => {
                        estimatedTime++;
                        estimatedTimeElement.textContent = estimatedTime;
                    }, 1000);
                },
                allowOutsideClick: false,
            });

            $.ajax({
                type: "post",
                url: "{{ route('modify-loading-line-update') }}",
                data: {
                    stockerIds: $("#stocker_ids").val(),
                    lineId : $("#line_id").val(),
                    tanggal_loading : $("#tanggal_loading").val(),
                    qty_reject : $("#qty_reject").val(),
                    qty_replace : $("#qty_replace").val(),
                },
                dataType: "json",
                success: function (response) {
                    console.log(response);
                    if (response.status == 200) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil',
                            html: response.message,
                            showCancelButton: false,
                            showConfirmButton: true,
                            confirmButtonText: 'Oke',
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal',
                            html: 'Terjadi Kesalahan',
                            showCancelButton: false,
                            showConfirmButton: true,
                            confirmButtonText: 'Oke',
                        });
                    }

                    currentStockerTableReload();
                },
                error: function(jqXHR) {
                    console.error(jqXHR);
                }
            });
        }

        function deleteStocker() {
            Swal.fire({
                icon: "info",
                title: "Konfirmasi",
                html: "Hapus Loading Line?",
                showDenyButton: true,
                showCancelButton: false,
                confirmButtonText: "Lanjut",
                denyButtonText: "Batal"
            }).then((result) => {
                $.ajax({
                    type: "delete",
                    url: "{{ route('modify-loading-line-delete') }}",
                    data: {
                        stockerIds: $("#stocker_ids").val()
                    },
                    dataType: "json",
                    success: function (response) {
                        console.log(response);
                        if (response.status == 200) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil',
                                html: response.message,
                                showCancelButton: false,
                                showConfirmButton: true,
                                confirmButtonText: 'Oke',
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Gagal',
                                html: 'Terjadi Kesalahan',
                                showCancelButton: false,
                                showConfirmButton: true,
                                confirmButtonText: 'Oke',
                            });
                        }

                        currentStockerTableReload();
                    },
                    error: function(jqXHR) {
                        console.error(jqXHR);
                    }
                });
            });
        }
    </script>
@endsection
