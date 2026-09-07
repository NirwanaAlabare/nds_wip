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
            <h5 class="card-title fw-bold mb-0"><i class="fa-solid fa-file"></i> REPORT WIP DC</h5>
        </div>
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-end gap-3 mb-3">
                <div class="d-flex align-items-end gap-3 mb-3">
                    <div>
                        <label class="form-label"><small>Tanggal Awal</small></label>
                        <input type="date" class="form-control form-control-sm" id="tgl-awal" name="tgl_awal" value="">
                    </div>
                    <div>
                        <label class="form-label"><small>Tanggal Akhir</small></label>
                        <input type="date" class="form-control form-control-sm" id="tgl-akhir" name="tgl_akhir" value="">
                    </div>
                    <div>
                        <button class="btn btn-primary btn-sm" onclick="datatableReportDC()"><i class="fa fa-search"></i></button>
                    </div>
                </div>
                <button class="btn btn-sm btn-success mb-3" onclick="exportExcel()"><i class="fa fa-file-excel"></i> Export</button>
            </div>
            <div class="table-responsive">
                <table id="datatable-dc-report" class="table table-bordered table w-100">
                    <thead>
                        <tr>
                            <th rowspan="2">No. WS</th>
                            <th rowspan="2">Buyer</th>
                            <th rowspan="2">Style</th>
                            <th rowspan="2">Color</th>
                            <th rowspan="2">Size</th>
                            <th rowspan="2">Panel</th>
                            <th rowspan="2">Part</th>
                            <th class="text-center" colspan="11">Mutasi DC</th>
                            <th class="text-center" colspan="6">Mutasi Secondary Dalam</th>
                            <th class="text-center" colspan="6">Mutasi Secondary Luar</th>
                            <th class="text-center" colspan="5">Mutasi Secondary Luar</th>
                            <th class="text-center" colspan="6">Terima Transit Secondary Luar</th>
                        </tr>
                        <tr>
                            <th>Saldo Awal</th>
                            <th>Masuk</th>
                            <th>Kirim Sec Dalam</th>
                            <th>Terima Repaired Sec Dalam</th>
                            <th>Terima Good Sec Dalam</th>
                            <th>Kirim Sec Luar</th>
                            <th>Terima Repaired Sec Luar</th>
                            <th>Terima Good Sec Luar</th>
                            <th>Loading</th>
                            <th>Adjustment</th>
                            <th>Saldo Akhir</th>
                            <th>Saldo Awal</th>
                            <th>Terima DC</th>
                            <th>Kirim Rep ke DC</th>
                            <th>Kirim Good ke DC</th>
                            <th>Adjustment</th>
                            <th>Saldo Akhir</th>
                            <th>Saldo Awal</th>
                            <th>Terima DC</th>
                            <th>Kirim Rep ke DC</th>
                            <th>Kirim Good ke DC</th>
                            <th>Adjustment</th>
                            <th>Saldo Akhir</th>
                            <th>Saldo Awal</th>
                            <th>Terima DC</th>
                            <th>Kirim Ke DC</th>
                            <th>Adjustment</th>
                            <th>Saldo Akhir</th>
                            <th>Saldo Awal</th>
                            <th>Terima Secondary Luar</th>
                            <th>Kirim Rep Secondary Luar</th>
                            <th>Kirim Good Secondary Luar</th>
                            <th>Adjustment</th>
                            <th>Saldo Akhir</th>
                        </tr>
                    </thead>
                    <tfoot>
                        <tr>
                            <th colspan="7">TOTAL</th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                        </tr>
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
        // document.addEventListener("DOMContentLoaded", () => {
        //     let today = new Date(new Date().setDate(new Date().getDate()));
        //     let todayDate = ("0" + today.getDate()).slice(-2);
        //     let todayMonth = ("0" + (today.getMonth() + 1)).slice(-2);
        //     let todayYear = today.getFullYear();
        //     let todayFull = todayYear + '-' + todayMonth + '-' + todayDate;

        //     $("#tgl-awal").val(todayFull).trigger("change");

        //     window.addEventListener("focus", () => {
        //         // $('#datatable-dc-report').DataTable().ajax.reload(null, false);
        //     });
        // });


        let datatableDcReport = $("#datatable-dc-report").DataTable({
            ordering: false,
            processing: false,
            serverSide: true,
            scrollX: true,
            scrollY: "60vh",
            scrollCollapse: true,
            deferLoading: 0,
            pageLength: 25,
            lengthMenu: [
                [10, 25, 50, 100, -1],
                [10, 25, 50, 100, "All"]
            ],
            ajax: {
                url: '{{ route('dc-report') }}',
                type: 'POST',
                beforeSend: function () {
                    Swal.fire({
                        title: 'Loading...',
                        text: 'Please wait while data is loading.',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });
                },
                data: function (d) {
                    d.dateFrom = $("#tgl-awal").val();
                    d.dateTo = $("#tgl-akhir").val();
                },
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                dataSrc: function (json) {
                    Swal.close();

                    return json.data;
                },
                error: function (xhr, status, error) {
                    Swal.close();

                    Swal.fire(
                        'Error',
                        'Failed to load data.',
                        'error'
                    );
                }
            },
            columns: [
                { data: 'ws' },
                { data: 'buyer' },
                { data: 'style' },
                { data: 'color' },
                { data: 'size' },
                { data: 'panel' },
                { data: 'nama_part' },
                { data: 'current_saldo_awal_adjustment', defaultContent: 0 },
                { data: 'qty_in', defaultContent: 0 },
                { data: 'kirim_secondary_dalam', defaultContent: 0 },
                { data: 'terima_repaired_secondary_dalam', defaultContent: 0 },
                { data: 'terima_good_secondary_dalam', defaultContent: 0 },
                { data: 'kirim_secondary_luar', defaultContent: 0 },
                { data: 'terima_repaired_secondary_luar', defaultContent: 0 },
                { data: 'terima_good_secondary_luar', defaultContent: 0 },
                { data: 'loading', defaultContent: 0 },
                { data: 'adjustment', defaultContent: 0 },
                {
                    data: null,
                    render: function (data, type, row) {
                        let saldoAwal  = parseInt(row.current_saldo_awal_adjustment ?? 0);
                        let masuk      = parseInt(row.qty_in ?? 0);
                        let ksd        = parseInt(row.kirim_secondary_dalam ?? 0);
                        let trsd       = parseInt(row.terima_repaired_secondary_dalam ?? 0);
                        let tgsd       = parseInt(row.terima_good_secondary_dalam ?? 0);
                        let ksl        = parseInt(row.kirim_secondary_luar ?? 0);
                        let trsl       = parseInt(row.terima_repaired_secondary_luar ?? 0);
                        let tgsl       = parseInt(row.terima_good_secondary_luar ?? 0);
                        let loading    = parseInt(row.loading ?? 0);
                        let adjustment    = parseInt(row.adjustment ?? 0);
                        let switchingIn    = parseInt(row.switching_in ?? 0);
                        let switchingOut    = parseInt(row.switching_out ?? 0);

                        return saldoAwal + masuk - ksd + trsd + tgsd - ksl + trsl + tgsl - loading + (adjustment + switchingIn - switchingOut);
                    }
                },
                { data: 'saldo_awal_secondary_dalam', defaultContent: 0 },
                { data: 'kirim_secondary_dalam', defaultContent: 0 },
                { data: 'terima_repaired_secondary_dalam', defaultContent: 0 },
                { data: 'terima_good_secondary_dalam', defaultContent: 0 },
                { data: 'qty_adjustment_secondary_dalam', defaultContent: 0 },
                { data: 'saldo_akhir_secondary_dalam', defaultContent: 0 },
                { data: 'saldo_awal_secondary_luar', defaultContent: 0 },
                { data: 'kirim_secondary_luar', defaultContent: 0 },
                { data: 'terima_repaired_secondary_luar', defaultContent: 0 },
                { data: 'terima_good_secondary_luar', defaultContent: 0 },
                { data: 'qty_adjustment_secondary_luar', defaultContent: 0 },
                { data: 'saldo_akhir_secondary_luar', defaultContent: 0 },
                { data: 'new_saldo_awal_secondary_luar', defaultContent: 0 },
                { data: 'new_terima_dc', defaultContent: 0 },
                { data: 'new_kirim_dc', defaultContent: 0 },
                { data: 'new_qty_adjustment_secondary_luar', defaultContent: 0 },
                { data: 'new_saldo_akhir_secondary_luar', defaultContent: 0 },
                { data: 'transit_saldo_awal_secondary_luar', defaultContent: 0 },
                { data: 'transit_terima_secondary_luar', defaultContent: 0 },
                { data: 'transit_kirim_rep_secondary_luar', defaultContent: 0 },
                { data: 'transit_kirim_good_secondary_luar', defaultContent: 0 },
                { data: 'transit_qty_adjustment_transit_terima_secondary_luar', defaultContent: 0 },
                { data: 'transit_saldo_akhir_secondary_luar', defaultContent: 0 },
            ],
            columnDefs: [
                {
                    targets: "_all",
                    className: 'align-middle text-nowrap'
                },
            ],
            footerCallback: function (row, data, start, end, display) {
                let api = this.api();

                let intVal = function (i) {
                    if (typeof i === 'string') {
                        return i.replace(/[,]/g, '') * 1;
                    }
                    if (typeof i === 'number') {
                        return i;
                    }
                    return 0;
                };

                let sumCol = function (idx) {
                    return api
                        .column(idx, { page: 'current' })
                        .data()
                        .reduce(function (a, b) {
                            return intVal(a) + intVal(b);
                        }, 0);
                };

                $(api.column(0).footer()).html('<b>TOTAL</b>');
                $(api.column(7).footer()).html(sumCol(7));
                $(api.column(8).footer()).html(sumCol(8));
                $(api.column(9).footer()).html(sumCol(9));
                $(api.column(10).footer()).html(sumCol(10));
                $(api.column(11).footer()).html(sumCol(11));
                $(api.column(12).footer()).html(sumCol(12));
                $(api.column(13).footer()).html(sumCol(13));
                $(api.column(14).footer()).html(sumCol(14));
                $(api.column(15).footer()).html(sumCol(15));
                $(api.column(16).footer()).html(sumCol(16));

                let totalSaldoAkhir = 0;

                api.rows({ page: 'current' }).every(function () {
                    let r = this.data();
                    totalSaldoAkhir += intVal(r.current_saldo_awal_adjustment) + intVal(r.qty_in) - intVal(r.kirim_secondary_dalam) + intVal(r.terima_repaired_secondary_dalam) + intVal(r.terima_good_secondary_dalam) - intVal(r.kirim_secondary_luar) + intVal(r.terima_repaired_secondary_luar) + intVal(r.terima_good_secondary_luar) - intVal(r.loading) + (intVal(r.adjustment) + intVal(r.switching_in) - intVal(r.switching_out));
                });

                $(api.column(17).footer()).html(totalSaldoAkhir);

                $(api.column(18).footer()).html(sumCol(18));
                $(api.column(19).footer()).html(sumCol(19));
                $(api.column(20).footer()).html(sumCol(20));
                $(api.column(21).footer()).html(sumCol(21));
                $(api.column(22).footer()).html(sumCol(22));
                $(api.column(23).footer()).html(sumCol(23));
                $(api.column(24).footer()).html(sumCol(24));
                $(api.column(25).footer()).html(sumCol(25));
                $(api.column(26).footer()).html(sumCol(26));
                $(api.column(27).footer()).html(sumCol(27));
                $(api.column(28).footer()).html(sumCol(28));
                $(api.column(29).footer()).html(sumCol(29));
                $(api.column(30).footer()).html(sumCol(30));
                $(api.column(31).footer()).html(sumCol(31));
                $(api.column(32).footer()).html(sumCol(32));
                $(api.column(33).footer()).html(sumCol(33));
                $(api.column(34).footer()).html(sumCol(34));
                $(api.column(35).footer()).html(sumCol(35));
                $(api.column(36).footer()).html(sumCol(36));
                $(api.column(37).footer()).html(sumCol(37));
                $(api.column(38).footer()).html(sumCol(38));
                $(api.column(39).footer()).html(sumCol(39));
                $(api.column(40).footer()).html(sumCol(40));
            }
        });

        updateDcReportView();

        $('#tgl-awal, #tgl-akhir').on('change', function () {
            updateDcReportView();
        });

        function datatableReportDC() {
            datatableDcReport.ajax.reload();
        }

        function updateDcReportView() {

            const endDate = $('#tgl-akhir').val();

            if (!endDate) {
                return;
            }

            const batasTanggal = '2026-07-01';
            const isOld = endDate < batasTanggal;

            const table = $('#datatable-dc-report').DataTable();

            // Lama
            for (let col = 24; col <= 29; col++) {
                table.column(col).visible(isOld, false);
            }

            // Baru
            for (let col = 30; col <= 34; col++) {
                table.column(col).visible(!isOld, false);
            }

            // Transit
            for (let col = 35; col <= 40; col++) {
                table.column(col).visible(!isOld, false);
            }

            table.columns.adjust();
        }

        // var filters = ['noWsColorSizeFilter', 'noWsColorPartFilter', 'noWsFilter', 'buyerFilter', 'styleFilter', 'colorFilter', 'sizeFilter', 'partFilter', 'saldoAwalFilter', 'masukFilter', 'kirimSecDalamFilter', 'terimaRepairedSecDalamFilter', 'terimaGoodSecDalamFilter', 'kirimSecLuarFilter', 'terimaRepairedSecLuarFilter', 'terimaGoodSecLuarFilter', 'loadingFilter', 'saldoAkhirFilter'];
        // $('#datatable-dc-report thead tr').clone(true).appendTo('#datatable-dc-report thead');
        // $('#datatable-dc-report thead tr:eq(1) th').each(function(i) {
        //     if (i == 0 || i == 1 || i == 2 || i == 3 || i == 4 || i == 5) {
        //         var title = $(this).text();
        //         $(this).html('<input type="text" class="form-control form-control-sm" id="'+filters[i]+'"/>');

        //         $('input', this).on('keyup change', function() {
        //             if (datatableDcReport.column(i).search() !== this.value) {
        //                 datatableDcReport
        //                     .column(i)
        //                     .search(this.value)
        //                     .draw();
        //             }
        //         });
        //     } else {
        //         $(this).empty();
        //     }
        // });

        async function exportExcel() {
            Swal.fire({
                title: "Exporting",
                html: "Please Wait...",
                timerProgressBar: true,
                allowOutsideClick: false,
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
                    link.download = "REPORT DC "+$("#tgl-awal").val()+" - "+$("#tgl-akhir").val()+".xlsx";
                    link.click();
                },
                error: function (jqXHR) {
                    Swal.fire({
                        icon: "error",
                        title: "Gagal",
                        html: "Terjadi kesalahan saat mengekspor data.",
                    });

                    console.error(jqXHR);
                }
            });

            Swal.close();
        }
    </script>
@endsection
