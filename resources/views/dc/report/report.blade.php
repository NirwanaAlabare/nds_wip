@extends('layouts.index')

@section('custom-link')
    <!-- DataTables -->
    <link rel="stylesheet" href="{{ asset('plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/datatables-responsive/css/responsive.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/datatables-buttons/css/buttons.bootstrap4.min.css') }}">

    <!-- Select2 -->
    <link rel="stylesheet" href="{{ asset('plugins/select2/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}">

    <style>
        div.dataTables_wrapper div.dataTables_processing {
            top: 15%;
        }
    </style>
@endsection

@section('content')
    <div class="card">
        <div class="card-header bg-sb text-light">
            <h5 class="card-title fw-bold mb-0"><i class="fa-solid fa-users-line"></i> DC Report</h5>
        </div>
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-end gap-3 mb-3">
                <div class="d-flex align-items-end gap-3 mb-3">
                    <div>
                        <label class="form-label"><small>Tanggal Awal</small></label>
                        <input type="date" class="form-control form-control-sm" id="tgl-awal" name="tgl_awal" value="{{ date('Y-m-d') }}" onchange="datatableLoadingLineReload()">
                    </div>
                    <div>
                        <label class="form-label"><small>Tanggal Akhir</small></label>
                        <input type="date" class="form-control form-control-sm" id="tgl-akhir" name="tgl_akhir" value="{{ date('Y-m-d') }}" onchange="datatableLoadingLineReload()">
                    </div>
                    <div>
                        <button class="btn btn-primary btn-sm" onclick="datatableLoadingLineReload()"><i class="fa fa-search"></i></button>
                    </div>
                </div>
                <button class="btn btn-sm btn-success mb-3" onclick="exportExcel()"><i class="fa fa-file-excel"></i> Export</button>
            </div>
            <div class="table-responsive">
                <table id="datatable-loading-line" class="table table-bordered table w-100">
                    <thead>
                        <tr>
                            <th>No WsColorSize</th>
                            <th>No WsColorPart</th>
                            <th>No. WS</th>
                            <th>Buyer</th>
                            <th>Style</th>
                            <th>Color</th>
                            <th>Size</th>
                            <th>Part</th>
                            <th>Saldo Awal</th>
                            <th>Masuk</th>
                            <th>Kirim Sec Dalam</th>
                            <th>Terima Repaired Sec Dalam</th>
                            <th>Terima Good Sec Dalam</th>
                            <th>Kirim Sec Luar</th>
                            <th>Terima Repaired Sec Luar</th>
                            <th>Terima Good Sec Luar</th>
                            <th>Loading</th>
                            <th>Saldo Akhir</th>
                        </tr>
                    </thead>
                    <tfoot>
                    </tfoot>
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
    <script src="{{ asset('plugins/datatables-rowsgroup/dataTables.rowsGroup.js') }}"></script>

    <!-- Select2 -->
    <script src="{{ asset('plugins/select2/js/select2.full.min.js') }}"></script>

    <script>
        document.addEventListener("DOMContentLoaded", () => {
            let today = new Date(new Date().setDate(new Date().getDate()));
            let todayDate = ("0" + today.getDate()).slice(-2);
            let todayMonth = ("0" + (today.getMonth() + 1)).slice(-2);
            let todayYear = today.getFullYear();
            let todayFull = todayYear + '-' + todayMonth + '-' + todayDate;

            $("#tgl-awal").val(todayFull).trigger("change");

            window.addEventListener("focus", () => {
                // $('#datatable-loading-line').DataTable().ajax.reload(null, false);
            });
        });


        let datatableLoadingLine = $("#datatable-loading-line").DataTable({
            ordering: false,
            processing: true,
            serverSide: true,
            pageLength: 25,
            ajax: {
                url: '{{ route('dc-report') }}',
                data: function (d) {
                    d.dateFrom = $("#tgl-awal").val();
                    d.dateTo = $("#tgl-akhir").val();
                }
            },
            columns: [
                {
                    data: 'color',
                },
                {
                    data: 'color',
                },
                {
                    data: 'color',
                },
                {
                    data: 'buyer',
                },
                {
                    data: 'style',
                },
                {
                    data: 'color',
                },
                {
                    data: 'size',
                },
                {
                    data: 'nama_part',
                },
                {
                    data: 'color',
                },
                {
                    data: 'qty_in',
                },
                {
                    data: 'kirim_secondary_dalam',
                },
                {
                    data: 'terima_repaired_secondary_dalam',
                },
                {
                    data: 'terima_good_secondary_dalam',
                },
                {
                    data: 'color',
                },
                {
                    data: 'color',
                },
                {
                    data: 'color',
                },
                {
                    data: 'color',
                },
                 {
                    data: 'color',
                },
            ],
            columnDefs: [
                {
                    targets: "_all",
                    className: 'align-middle text-nowrap'
                },
                // {
                //     targets: [1],
                //     render: (data, type, row, meta) => {
                //         console.log(row['id']);
                //         return `
                //             <div class='d-flex flex-column gap-1 justify-content-center align-items-center'>
                //                 <a href='{{ route('detail-loading-plan') }}/` + row['id'] + `/` + $("#tgl-awal").val() + `/` + $("#tgl-akhir").val() + `' class='btn btn-sb btn-sm' target='_blank'>
                //                     <i class='fa fa-search'></i>
                //                 </a>
                //                 <a href='{{ route('edit-loading-plan') }}/` + row['id'] + `' class='btn btn-sb-secondary btn-sm'>
                //                     <i class='fa fa-edit'></i>
                //                 </a>
                //             </div>
                //         `;
                //     }
                // },
                // {
                //     targets: [5,6,7,10],
                //     render: (data, type, row, meta) => {
                //         return Number(data).toLocaleString('id-ID')
                //     }
                // },
                // {
                //     targets: [8],
                //     render: (data, type, row, meta) => {
                //         return "<span class='"+(Number(data) >= 0 ? "text-success" : "text-danger")+" fw-bold'>"+Number(data).toLocaleString('id-ID')+"</span";
                //     }
                // }
            ],
            // rowsGroup: [
            //     // Always the array (!) of the column-selectors in specified order to which rows groupping is applied
            //     // (column-selector could be any of specified in https://datatables.net/reference/type/column-selector)
            //     0,
            //     1,
            //     2,
            //     3,
            //     4,
            //     5,
            //     6
            // ],
            // footerCallback: async function(row, data, start, end, display) {
            //     var api = this.api(),data;

            //     $(api.column(0).footer()).html('Total');
            //     $(api.column(5).footer()).html("...");
            //     $(api.column(6).footer()).html("...");
            //     $(api.column(7).footer()).html("...");
            //     $(api.column(8).footer()).html("...");

            //     $.ajax({
            //         url: '{{ route('total-loading-line') }}',
            //         dataType: 'json',
            //         dataSrc: 'data',
            //         data: {
            //             'dateFrom': $('#tgl-awal').val(),
            //             'dateTo': $('#tgl-akhir').val(),
            //             'lineFilter': $('#lineFilter').val(),
            //             'wsFilter': $('#wsFilter').val(),
            //             'styleFilter': $('#styleFilter').val(),
            //             'colorFilter': $('#colorFilter').val(),
            //             'trolleyFilter': $('#trolleyFilter').val(),
            //             'trolleyColorFilter': $('#trolleyColorFilter').val(),
            //         },
            //         success: function(response) {
            //             console.log(response);
            //             if (response) {
            //                 // Update footer by showing the total with the reference of the column index
            //                 $(api.column(0).footer()).html('Total');
            //                 $(api.column(5).footer()).html((Number(response['total_target_sewing'])).toLocaleString("ID-id"));
            //                 $(api.column(6).footer()).html((Number(response['total_target_loading'])).toLocaleString("ID-id"));
            //                 $(api.column(7).footer()).html((Number(response['total_loading'])).toLocaleString("ID-id"));
            //                 $(api.column(8).footer()).html("<span class='"+(Number(response['total_balance_loading']) >= 0 ? "text-success" : "text-danger")+" fw-bold'>"+(Number(response['total_balance_loading'])).toLocaleString("ID-id")+"</span>");
            //             }
            //         },
            //         error: function(jqXHR) {
            //             console.error(jqXHR);
            //         },
            //     })
            // },
        });

        function datatableLoadingLineReload() {
            datatableLoadingLine.ajax.reload()
        }

        var filters = ['noWsColorSizeFilter', 'noWsColorPartFilter', 'noWsFilter', 'buyerFilter', 'styleFilter', 'colorFilter', 'sizeFilter', 'partFilter', 'saldoAwalFilter', 'masukFilter', 'kirimSecDalamFilter', 'terimaRepairedSecDalamFilter', 'terimaGoodSecDalamFilter', 'kirimSecLuarFilter', 'terimaRepairedSecLuarFilter', 'terimaGoodSecLuarFilter', 'loadingFilter', 'saldoAkhirFilter'];
        $('#datatable-loading-line thead tr').clone(true).appendTo('#datatable-loading-line thead');
        $('#datatable-loading-line thead tr:eq(1) th').each(function(i) {
            // if (i == 0 || i == 2 || i == 3 || i == 4 || i == 9 || i == 11) {
                var title = $(this).text();
                $(this).html('<input type="text" class="form-control form-control-sm" id="'+filters[i]+'"/>');

                $('input', this).on('keyup change', function() {
                    if (datatableLoadingLine.column(i).search() !== this.value) {
                        datatableLoadingLine
                            .column(i)
                            .search(this.value)
                            .draw();
                    }
                });
            // } else {
            //     $(this).empty();
            // }
        });

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
                url: "{{ route("export-report-dc") }}",
                type: "post",
                data: {
                    from : $("#tgl-awal").val(),
                    to : $("#tgl-akhir").val(),
                    lineFilter : $("#lineFilter").val(),
                    noWsColorSizeFilter     : $("#noWsColorSizeFilter").val(),
                    noWsColorPartFilter     : $("#noWsColorPartFilter").val(),
                    noWsFilter :  $("#noWsFilter").val(),
                    buyerFilter : $("#buyerFilter").val(),
                    styleFilter : $("#styleFilter").val(),
                    colorFilter : $("#colorFilter").val(),
                    sizeFilter : $("#sizeFilter").val(),
                    partFilter : $("#partFilter").val(),
                    saldoAwalFilter : $("#saldoAwalFilter").val(),
                    masukFilter : $("#masukFilter").val(),
                    kirimSecDalamFilter : $("#kirimSecDalamFilter").val(),
                    terimaRepairedSecDalamFilter : $("#terimaRepairedSecDalamFilter").val(),
                    terimaGoodSecDalamFilter : $("#terimaGoodSecDalamFilter").val(),
                    kirimSecLuarFilter : $("#kirimSecLuarFilter").val(),
                    terimaRepairedSecLuarFilter : $("#terimaRepairedSecLuarFilter").val(),
                    terimaGoodSecLuarFilter : $("#terimaGoodSecLuarFilter").val(),
                    loadingFilter : $("#loadingFilter").val(),
                    saldoAkhirFilter : $("#saldoAkhirFilter").val()
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
                    link.download = "DC REPORT "+$("#tgl-awal").val()+" - "+$("#tgl-akhir").val()+".xlsx";
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
