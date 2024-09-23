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
    <form action="{{ route('export_excel') }}" method="get">
        <div class="card card-sb">
            <div class="card-header">
                <h5 class="card-title fw-bold mb-0"><i class="fa-solid fa-toilet-paper fa-sm"></i> Pemakaian Roll</h5>
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-end gap-3">
                    <div class="d-flex align-items-end gap-3 mb-3">
                        <div class="mb-3">
                            <label class="form-label"><small>Tanggal Awal</small></label>
                            <input type="date" class="form-control form-control-sm" id="from" name="from" value="{{ date('Y-m-d') }}" onchange="datatableReload()">
                        </div>
                        <div class="mb-3">
                            <label class="form-label"><small>Tanggal Akhir</small></label>
                            <input type="date" class="form-control form-control-sm" id="to" name="to" value="{{ date('Y-m-d') }}" onchange="datatableReload()">
                        </div>
                        <button type="button" class="btn btn-primary btn-sm mb-3"><i class="fa fa-search fa-sm"></i></button>
                    </div>
                    <div class="d-flex align-items-end gap-3 mb-3">
                        <div class="mb-3">
                            <button type='submit' name='submit' class='btn btn-success btn-sm'>
                                <i class="fas fa-file-excel"></i> Export
                            </button>
                        </div>
                    </div>
                </div>
                <div class="table-responsive">
                    <table id="datatable" class="table table-bordered table-hover table-sm w-100">
                        <thead>
                            <tr>
                                <th>Tanggal Pemakaian</th>
                                <th>No. WS</th>
                                <th>ID Roll</th>
                                <th>ID Item</th>
                                <th>Nama Barang</th>
                                <th>No. Form</th>
                                <th>No. Meja</th>
                                <th>Color</th>
                                <th>Group</th>
                                <th>Lot</th>
                                <th>Roll</th>
                                <th>Qty</th>
                                <th>Unit</th>
                                <th>Cons. Piping</th>
                                <th>Berat Amparan (KGM)</th>
                                <th>Lembar Gelaran</th>
                                <th>Total Pemakaian</th>
                                <th>Short Roll</th>
                                <th>Short Roll (%)</th>
                                <th>Remark</th>
                                <th>Sisa Gelaran</th>
                                <th>Sambungan</th>
                                <th>Kepala Kain</th>
                                <th>Sisa Tidak Bisa</th>
                                <th>Reject</th>
                                <th>Sisa Kain</th>
                                <th>Piping</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </form>
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
            let oneWeeksBefore = new Date(new Date().setDate(new Date().getDate() - 7));
            let oneWeeksBeforeDate = ("0" + oneWeeksBefore.getDate()).slice(-2);
            let oneWeeksBeforeMonth = ("0" + (oneWeeksBefore.getMonth() + 1)).slice(-2);
            let oneWeeksBeforeYear = oneWeeksBefore.getFullYear();
            let oneWeeksBeforeFull = oneWeeksBeforeYear + '-' + oneWeeksBeforeMonth + '-' + oneWeeksBeforeDate;

            // $("#from").val(oneWeeksBeforeFull).trigger("change");

            // window.addEventListener("focus", () => {
            //     datatableReload();
            // });
        });

        $('#datatable thead tr').clone(true).appendTo('#datatable thead');
        $('#datatable thead tr:eq(1) th').each(function(i) {
            if (i <= 9) {
                var title = $(this).text();
                $(this).html('<input type="text" class="form-control form-control-sm" />');

                $('input', this).on('keyup change', function() {
                    if (datatable.column(i).search() !== this.value) {
                        datatable
                            .column(i)
                            .search(this.value)
                            .draw();
                    }
                });
            } else {
                $(this).empty();
            }
        });

        let datatable = $("#datatable").DataTable({
            processing: true,
            serverSide: true,
            ordering: false,
            scrollX: "500px",
            scrollY: "500px",
            pageLength: 50,
            ajax: {
                url: '{{ route('lap_pemakaian') }}',
                data: function(d) {
                    d.dateFrom = $('#from').val();
                    d.dateTo = $('#to').val();
                },
            },
            columns: [
                {
                    data: 'tgl_input'
                },
                {
                    data: 'act_costing_ws'
                },
                {
                    data: 'id_roll'
                },
                {
                    data: 'id_item'
                },
                {
                    data: 'detail_item'
                },
                {
                    data: 'no_form_cut_input'
                },
                {
                    data: 'nama_meja'
                },
                {
                    data: 'color_act'
                },
                {
                    data: 'group_roll'
                },
                {
                    data: 'lot'
                },
                {
                    data: 'roll'
                },
                {
                    data: 'qty_item'
                },
                {
                    data: 'unit_item'
                },
                {
                    data: 'cons_pipping'
                },
                {
                    data: 'berat_amparan'
                },
                {
                    data: 'lembar_gelaran'
                },
                {
                    data: 'total_pemakaian_roll'
                },
                {
                    data: 'short_roll'
                },
                {
                    data: 'short_roll_percentage'
                },
                {
                    data: 'remark'
                },
                {
                    data: 'sisa_gelaran'
                },
                {
                    data: 'sambungan'
                },
                {
                    data: 'kepala_kain'
                },
                {
                    data: 'sisa_tidak_bisa'
                },
                {
                    data: 'reject'
                },
                {
                    data: 'sisa_kain'
                },
                {
                    data: 'piping'
                },
            ],
            columnDefs: [
                {
                    targets: "_all",
                    className: "text-nowrap"
                }
            ],
        });

        function datatableReload() {
            datatable.ajax.reload();
        }
    </script>
@endsection
