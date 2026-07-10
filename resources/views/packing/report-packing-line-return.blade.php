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
            <h5 class="card-title fw-bold mb-0"> <i class="fas fa-list"></i> Report Packing Line Return</h5>
        </div>
        <div class="card-body" id="report-packing-line-return">
            <div class="row g-3 align-items-end mb-3">
                <div class="col-12 col-md-2">
                    <label class="form-label">
                        <small>Tipe</small>
                    </label>
                    <select class="form-select form-select-sm select2bs4base" id="tipe" name="tipe">
                        <option selected value="" disabled>Pilih Tipe</option>
                        <option value="Detail">Detail</option>
                        <option value="Summary">Summary</option>
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
            <div class="table-responsive">
                <table id="datatable" class="table table-bordered table-striped table-hover w-100 text-nowrap">
                    <thead class="bg-sb">
                        <tr>
                            <th>Tgl Return</th>
                            <th>Nomor QR</th>
                            <th>PO</th>
                            <th>Buyer</th>
                            <th>Worksheet</th>
                            <th>Style</th>
                            <th>Color</th>
                            <th>Size</th>
                            <th>Packing Line</th>
                            <th>Kirim QC Finishing</th>
                            <th>Created At</th>
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
            dropdownParent: $("#report-packing-line-return")
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

        // ================= CONFIG DETAIL =================
        const detailConfig = {
            header: `
                <thead class="bg-sb">
                    <tr>
                        <th class="text-center">Tgl Return</th>
                        <th class="text-center">Nomor QR</th>
                        <th class="text-center">PO</th>
                        <th class="text-center">Buyer</th>
                        <th class="text-center">Worksheet</th>
                        <th class="text-center">Style</th>
                        <th class="text-center">Color</th>
                        <th class="text-center">Size</th>
                        <th class="text-center">Packing Line</th>
                        <th class="text-center">Kirim QC Finishing</th>
                        <th class="text-center">Created At</th>
                    </tr>
                </thead>
            `,
            columns: [
                { data: "tgl_return" },
                { data: "kode_numbering" },
                { data: "po" },
                { data: "buyer" },
                { data: "ws" },
                { data: "style" },
                { data: "color" },
                { data: "size" },
                { data: "packing_line" },
                { data: "line_qc_finishing" },
                { data: "created_at" }
            ]
        };

        // ================= CONFIG SUMMARY =================
        const summaryConfig = {
            header: `
                <thead class="bg-sb">
                    <tr>
                        <th class="text-center">Buyer</th>
                        <th class="text-center">Worksheet</th>
                        <th class="text-center">Style</th>
                        <th class="text-center">Color</th>
                        <th class="text-center">Size</th>
                        <th class="text-center">Qty Return</th>
                    </tr>
                </thead>
            `,
            columns: [
                { data: "buyer" },
                { data: "ws" },
                { data: "style" },
                { data: "color" },
                { data: "size" },
                {
                    data: "qty_return",
                    className: "text-end"
                }
            ]
        };
        
        document.addEventListener("DOMContentLoaded", function(){

            let today = new Date().toISOString().slice(0,10);

            $("#tgl-awal").val(today);
            $("#tgl-akhir").val(today);

            initDataTable();
        });

        function initDataTable(){

            if($.fn.DataTable.isDataTable("#datatable")){
                table.destroy();
            }

            const config = $("#tipe").val() === "Summary"
                ? summaryConfig
                : detailConfig;

            $("#datatable").html(config.header);

            table = $("#datatable").DataTable({
                processing: true,
                serverSide: true,
                ordering: false,
                searching: true,
                destroy: true,
                ajax:{
                    url:'{{ route("report-packing-line-return") }}',
                    data:function(d){
                        d.dateFrom=$("#tgl-awal").val();
                        d.dateTo=$("#tgl-akhir").val();
                        d.tipe=$("#tipe").val();
                        d.buyer=$("#buyer").val();
                    }
                },
                columns:config.columns
            });
        }

        function dataTableReload(){

            Swal.fire({
                title:'Loading...',
                text:'Please wait while data is loading.',
                allowOutsideClick:false,
                didOpen:()=>{
                    Swal.showLoading();
                }
            });

            $("#table").removeClass("d-none");

            initDataTable();

            table.one("draw",function(){
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
                url: "{{ route("export-report-packing-line-return") }}",
                type: "post",
                data: {
                    dateFrom : $("#tgl-awal").val(),
                    dateTo : $("#tgl-akhir").val(),
                    tipe : $("#tipe").val(),
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
                    link.download = "Report Packing Line Return "+$("#tgl-awal").val()+" - "+$("#tgl-akhir").val()+".xlsx";
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
