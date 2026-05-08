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
            <h5 class="card-title fw-bold mb-0"> <i class="fas fa-list"></i> Report Finishing</h5>
        </div>
        <div class="card-body" id="report-finishing">
            <div class="row g-3 align-items-end mb-3">
                <div class="col-12 col-md-2">
                    <label class="form-label">
                        <small>Kategori</small>
                    </label>
                    <select class="form-select form-select-sm select2bs4base" id="kategori" name="kategori">
                        <option selected value="" disabled>Pilih Kategori</option>
                        <option value="TERIMA">TERIMA</option>
                        <option value="DEFECT">DEFECT</option>
                        <option value="REWORK">REWORK</option>
                        <option value="REJECT">REJECT</option>
                        <option value="OUTPUT">OUTPUT</option>
                    </select>
                </div>

                <div class="col-12 col-md-3">
                    <label class="form-label">
                        <small>Buyer</small>
                    </label>
                    <select class="form-select form-select-sm select2bs4base select2-buyer" name="buyer" id="buyer">
                        <option value="">Semua Buyer</option>
                        @foreach ($buyer as $row)
                            <option value="{{ $row->supplier }}">
                                {{ $row->supplier }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-12 col-md-2">
                    <label class="form-label">
                        <small>Tanggal Awal</small>
                    </label>
                    <input type="date" class="form-control form-control-sm" id="tgl-awal" name="tgl_awal">
                </div>

                <div class="col-12 col-md-2">
                    <label class="form-label">
                        <small>Tanggal Akhir</small>
                    </label>
                    <input type="date" class="form-control form-control-sm" id="tgl-akhir" name="tgl_akhir" value="{{ date('Y-m-d') }}">
                </div>

                <div class="col-12 col-md-3">
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-primary btn-sm" onclick="dataTableReload()">
                            <i class="fas fa-search"></i>
                            Cari
                        </button>

                        <button type="button" onclick="exportExcel()" id="exportExcel" class="btn btn-success btn-sm">
                            <i class="fas fa-file-excel fa-sm"></i>
                            Export Excel
                        </button>
                    </div>
                </div>
            </div>
            <div id="table" class="table-responsive">
                <table id="datatable" class="table table-bordered table-striped table-hover table w-100">
                    <thead class="bg-sb">
                        <tr>
                            <th class="text-center align-middle">Buyer</th>
                            <th class="text-center align-middle">WS</th>
                            <th class="text-center align-middle">Style</th>
                            <th class="text-center align-middle">Color</th>
                            <th class="text-center align-middle">Size</th>
                            <th class="text-center align-middle">Jumlah</th>
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

    <!-- Select2 -->
    <script src="{{ asset('plugins/select2/js/select2.full.min.js') }}"></script>
    <script>
        $(document).on('select2:open', () => {
            document.querySelector('.select2-search__field').focus();
        });

        // Initialize Select2 Elements
        $('.select2').select2();

        // Initialize Select2BS4 Elements
        $('.select2bs4').select2({
            theme: 'bootstrap4',
            width: 'resolve' // Ensures it respects the 100% width from inline style or Bootstrap
        });

         $('.select2bs4base').select2({
            theme: 'bootstrap4',
            dropdownParent: $("#report-finishing")
        });

        // Now set height and font-size on the Select2 container after init
        $('.select2-container--bootstrap4 .select2-selection--single').css({
            'height': '30px', // your desired height
            'font-size': '12px', // your desired font size
            'line-height': '30px' // vertically center text
        });
    </script>

    <script>

        let table;

        document.addEventListener("DOMContentLoaded", () => {

            // set tanggal default TANPA trigger
            let oneWeeksBefore = new Date(new Date().setDate(new Date().getDate() - 0));
            let oneWeeksBeforeFull = oneWeeksBefore.toISOString().slice(0,10);

            $("#tgl-awal").val(oneWeeksBeforeFull);
            $("#tgl-akhir").val(new Date().toISOString().slice(0,10));

            // ================= INIT DATATABLE =================
            table = $("#datatable").DataTable({
                processing: true,
                serverSide: true,
                ordering: false,
                deferLoading: 0,
                ajax: {
                    url: '{{ route('report-finishing') }}',
                    data: d => {
                        d.dateFrom = $('#tgl-awal').val();
                        d.dateTo = $('#tgl-akhir').val();
                        d.kategori = $("#kategori").val();
                        d.buyer = $("#buyer").val();
                    }
                },
                columns: [
                    {
                        data: 'buyer'
                    },
                    {
                        data: 'ws'
                    },
                    {
                        data: 'styleno'
                    },
                    {
                        data: 'color'
                    },
                    {
                        data: 'size'
                    },
                    {
                        data: 'jumlah',
                        className: 'text-end',
                        render: function(data, type, row) {
                            return parseFloat(data);
                        }
                    },
                ],
            });
        });

        function dataTableReload() {
            Swal.fire({
                title: 'Loading...',
                text: 'Please wait while data is loading.',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            $('#table').removeClass('d-none');
            $('#exportExcel').prop('disabled', false);

            table.ajax.reload(function () {
                Swal.close();
            });
        }

        async function exportExcel() {
            Swal.fire({
                title: "Exporting",
                html: "Please Wait...",
                timerProgressBar: true,
                didOpen: () => {
                    Swal.showLoading();
                },
            });

            await $.ajax({
                url: "{{ route("export-report-finishing") }}",
                type: "post",
                data: {
                    from : $("#tgl-awal").val(),
                    to : $("#tgl-akhir").val(),
                    kategori : $("#kategori").val(),
                    buyer : $("#buyer").val()
                },
                xhrFields: { responseType : 'blob' },
                success: function (res) {
                    Swal.close();

                    iziToast.success({
                        title: 'Success',
                        message: 'Success',
                        position: 'topCenter'
                    });

                    var blob = new Blob([res]);
                    var link = document.createElement('a');
                    link.href = window.URL.createObjectURL(blob);
                    link.download = "Report Finishing "+$("#tgl-awal").val()+" - "+$("#tgl-akhir").val()+".xlsx";
                    link.click();
                },
                error: function (jqXHR) {
                    console.error(jqXHR);
                }
            });

            Swal.close();
        }
    </script>
@endsection
