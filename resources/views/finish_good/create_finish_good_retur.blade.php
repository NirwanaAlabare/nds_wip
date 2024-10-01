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
    <form id="form_del" name='form_del' method='post' action="{{ route('delete_karton_fg_retur') }}"
        onsubmit="submitForm(this, event)">
        <div class="modal fade" id="exampleModalDel" tabindex="-1" role="dialog" aria-labelledby="exampleModalDelLabel"
            aria-hidden="true">
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <div class="modal-header bg-danger">
                        <h3 class="modal-title fs-5">List Detail karton Retur</h3>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class='row'>
                            <div class="col-md-12 table-responsive">
                                <table id="datatable_delete" class="table table-bordered table-hover table-sm w-100 nowrap">
                                    <thead class="table-danger">
                                        <tr style='text-align:center; vertical-align:middle'>
                                            <th>
                                                <input class="form-check checkbox-xl" type="checkbox"
                                                    onclick="toggle(this);">
                                            </th>
                                            <th>PO #</th>
                                            <th>No. Ctn</th>
                                            <th>WS #</th>
                                            <th>Color</th>
                                            <th>Size</th>
                                            <th>Qty</th>
                                        </tr>
                                    </thead>
                                    <tfoot>
                                        <tr>
                                            <th colspan="6">Total</th>
                                            <th></th>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        {{-- <input id="submit" name="submit" type="submit" value="Delete" onclick="test();"
                            class="btn btn-outline-danger btn-sm" /> --}}
                        <button type="submit"class="btn btn-outline-danger btn-sm"><i class="fas fa-trash"></i> Delete
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>

    <form id="form" name='form' method='post' action="{{ route('store-fg-retur') }}"
        onsubmit="submitForm(this, event)">
        <div class="card card-info">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center ">
                    <h5 class="card-title fw-bold mb-0"><i class="fas fa-user-edit"></i> Input Retur Barang Jadi Ekspedisi /
                        FG
                        RO
                    </h5>
                    <a href="{{ route('finish_good_pengeluaran') }}" class="btn btn-sm btn-secondary">
                        <i class="fa fa-reply"></i> Kembali
                    </a>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-sm-2">
                        <div class="form-group">
                            <label class="form-label"><small><b>Tgl Pengeluaran :</b></small></label>
                            <input type="date" class="form-control form-control-sm " id="tgl_pengeluaran"
                                name="tgl_pengeluaran" value="{{ date('Y-m-d') }}">
                        </div>
                    </div>
                    <div class="col-sm-3">
                        <div class="form-group">
                            <label><small><b>Buyer Filter:</b></small></label>
                            <select class="form-control select2bs4" id="cbobuyer" name="cbobuyer" onchange="getpo();"
                                style="width: 100%;">
                                <option selected="selected" value="" disabled="true">Pilih Buyer</option>
                                @foreach ($data_buyer as $databuyer)
                                    <option value="{{ $databuyer->isi }}">
                                        {{ $databuyer->tampil }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-sm-3">
                        <div class="form-group">
                            <label><small><b>Type Document :</b></small></label>
                            <select class="form-control select2bs4" id="cbotipe_doc" name="cbotipe_doc" style="width: 100%;"
                                required>
                                <option selected="selected" value="" disabled="true">Pilih Dokumen</option>
                                @foreach ($data_dok as $datadok)
                                    <option value="{{ $datadok->isi }}">
                                        {{ $datadok->tampil }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-sm-4">
                        <div class="form-group">
                            <label><small><b>Inv / SJ :</b></small></label>
                            <input type="text" class="form-control form-control-sm" id="txtinv" name="txtinv"
                                style="width: 100%;" required>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-12">
                    <div class="card card-primary card-outline">
                        <div class="card-header">
                            <h5 class="card-title"><i class="fas fa-list"></i> Filter Karton</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-sm-4">
                                    <div class="form-group">
                                        <label><small><b>PO :</b></small></label>
                                        <select class="form-control select2bs4" id="cbopo" name="cbopo"
                                            onchange="getnotes();" style="width: 100%;">
                                        </select>
                                    </div>
                                </div>
                                <div class="col-sm-3">
                                    <div class="form-group">
                                        <label><small><b>Notes :</b></small></label>
                                        <select class="form-control select2bs4" id="cbonotes" name="cbonotes"
                                            onchange="getno_carton();" style="width: 100%;">
                                        </select>
                                    </div>
                                </div>
                                <div class="col-sm-2">
                                    <div class="form-group">
                                        <label><small><b>No. Ctn Awal :</b></small></label>
                                        <input type="text" class="form-control form-control-sm" id="txtctn_awal"
                                            name="txtctn_awal" style="width: 100%;">
                                    </div>
                                </div>
                                <div class="col-sm-2">
                                    <div class="form-group">
                                        <label><small><b>No. Ctn Akhir :</b></small></label>
                                        <input type="text" class="form-control form-control-sm" id="txtctn_akhir"
                                            name="txtctn_akhir" style="width: 100%;">
                                    </div>
                                </div>
                                <div class="col-md-1">
                                    <div class="form-group">
                                        <label>&nbsp; &nbsp; &nbsp;&nbsp;&nbsp;&nbsp;</label>
                                        <div class="input-group">
                                            <a class="btn btn-outline-primary btn-sm"
                                                onclick="inserttmp();dataTableDetKartonReload()">
                                                <i class="fas fa-filter"></i>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-7">
                    <div class="card card-primary card-outline">
                        <div class="card-header">
                            <h5 class="card-title"><i class="fas fa-list"></i> Detail Karton Retur</h5>
                        </div>
                        <div class="card-body">
                            <div class="input-group">
                                <a class="btn btn-outline-danger position-relative btn-sm" data-bs-toggle="modal"
                                    data-bs-target="#exampleModalDel" onclick="dataTableDeleteReload();">
                                    <i class="fas fa-trash fa-sm"></i>
                                    Delete
                                </a>
                            </div>
                            <div class="table-responsive">
                                <table id="datatable_det_karton" class="table table-bordered table-sm w-100 text-nowrap">
                                    <thead class="table-warning">
                                        <tr>
                                            <th>PO #</th>
                                            <th>No. Ctn</th>
                                            <th>WS #</th>
                                            <th>Color</th>
                                            <th>Size</th>
                                            <th>Dest</th>
                                            <th>Qty</th>
                                        </tr>
                                    </thead>
                                    <tfoot>
                                        <tr>
                                            <th colspan="6">Total</th>
                                            <th></th>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-5">
                    <div class="card card-primary card-outline">
                        <div class="card-header">
                            <h5 class="card-title"><i class="fas fa-list"></i> Summary Retur</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table id="datatable_summary" class="table table-bordered table-sm w-100 text-nowrap">
                                    <thead class="table-success">
                                        <tr>
                                            <th>WS #</th>
                                            <th>Color</th>
                                            <th>Size</th>
                                            <th>Qty</th>
                                            <th>Dest</th>
                                            <th style="display:none;">ID SO Det</th>
                                        </tr>
                                    </thead>
                                    <tfoot>
                                        <tr>
                                            <th>Total</th>
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
                </div>
            </div>
            <button type="submit"class="btn btn-outline-success btn-sm"><i class="fas fa-check"></i> Simpan
            </button>

        </div>
    </form>
@endsection

@section('custom-script')
    <!-- DataTables & Plugins -->
    <script src="{{ asset('plugins/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-responsive/js/dataTables.responsive.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-responsive/js/responsive.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('plugins/select2/js/select2.full.min.js') }}"></script>
    <style>
        .checkbox-xl .form-check-input {
            /* top: 1.2rem; */
            scale: 1.5;
            /* margin-right: 0.8rem; */
        }
    </style>
    <script>
        function toggle(source) {
            var checkboxes = document.querySelectorAll('input[type="checkbox"]');
            for (var i = 0; i < checkboxes.length; i++) {
                if (checkboxes[i] != source)
                    checkboxes[i].checked = source.checked;
            }
        }

        function ceklis(checkeds) {
            //get id..and check if checked
            console.log($(checkeds).attr("value"), checkeds.checked)

        }
    </script>
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
            containerCssClass: 'form-control-sm rounded'
        })
    </script>
    <script>
        $(document).ready(function() {
            $("#cbobuyer").val('').trigger('change');
            $("#cbopo").val('').trigger('change');
            $("#cbonotes").val('').trigger('change');
            $("#cbotipe_doc").val('').trigger('change');
            $("#txtinv").val('');
            $("#txtctn_awal").val('');
            $("#txtctn_akhir").val('');
            cleartmp();
        })

        function cleartmp() {
            $.ajax({
                type: "post",
                url: '{{ route('clear_tmp_fg_retur') }}',
                success: function(response) {
                    dataTableDetKartonReload();
                    dataTableSummaryReload();
                    dataTableDeleteReload();
                },
            });
        }

        function getpo() {
            let cbobuyer = document.form.cbobuyer.value;
            let html = $.ajax({
                type: "GET",
                url: '{{ route('getpo_fg_retur') }}',
                data: {
                    cbobuyer: cbobuyer
                },
                async: false
            }).responseText;
            if (html != "") {
                $("#cbopo").html(html);
            }
            $("#cbonotes").val('').trigger('change');
            $("#txtctn_awal").val('');
            $("#txtctn_akhir").val('');
            cleartmp();
        };

        function getnotes() {
            let cbobuyer = document.form.cbobuyer.value;
            let cbopo = document.form.cbopo.value;
            let html = $.ajax({
                type: "GET",
                url: '{{ route('getcarton_notes_fg_retur') }}',
                data: {
                    cbobuyer: cbobuyer,
                    cbopo: cbopo
                },
                async: false
            }).responseText;
            if (html != "") {
                $("#cbonotes").html(html);
            }
        };

        function getno_carton() {
            let cbopo = $('#cbopo').val();
            let cbonotes = $('#cbonotes').val();
            jQuery.ajax({
                url: '{{ route('show_number_carton_fg_retur') }}',
                method: 'GET',
                data: {
                    cbopo: cbopo,
                    cbonotes: cbonotes
                },
                dataType: 'json',
                success: async function(response) {
                    document.getElementById('txtctn_awal').value = response.min;
                    document.getElementById('txtctn_akhir').value = response.max;
                },
                error: function(request, status, error) {
                    alert(request.responseText);
                },
            });
        }

        function inserttmp() {
            let cbobuyer = $('#cbobuyer').val();
            let cbopo = $('#cbopo').val();
            let cbonotes = $('#cbonotes').val();
            let txtctn_awal = $('#txtctn_awal').val();
            let txtctn_akhir = $('#txtctn_akhir').val();
            $.ajax({
                type: "post",
                url: '{{ route('insert_tmp_fg_retur') }}',
                data: {
                    cbobuyer: cbobuyer,
                    cbopo: cbopo,
                    cbonotes: cbonotes,
                    txtctn_awal: txtctn_awal,
                    txtctn_akhir: txtctn_akhir
                },
                success: function(response) {
                    dataTableDetKartonReload();
                    dataTableSummaryReload();
                },
            });
        }

        function dataTableDetKartonReload() {
            datatable_det_karton.ajax.reload();
        }


        let datatable_det_karton = $("#datatable_det_karton").DataTable({
            "footerCallback": function(row, data, start, end, display) {
                var api = this.api(),
                    data;

                // converting to interger to find total
                var intVal = function(i) {
                    return typeof i === 'string' ?
                        i.replace(/[\$,]/g, '') * 1 :
                        typeof i === 'number' ?
                        i : 0;
                };

                // computing column Total of the complete result
                var sumTotal = api
                    .column(6)
                    .data()
                    .reduce(function(a, b) {
                        return intVal(a) + intVal(b);
                    }, 0);

                // Update footer by showing the total with the reference of the column index
                $(api.column(0).footer()).html('Total');
                $(api.column(6).footer()).html(sumTotal);
            },
            ordering: false,
            processing: true,
            serverSide: true,
            paging: false,
            destroy: true,
            autoWidth: true,
            scrollX: '300px',
            ajax: {
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: '{{ route('show_det_karton_fg_retur') }}',
                dataType: 'json',
                dataSrc: 'data',
                data: function(d) {
                    d.cbobuyer = $('#cbobuyer').val();
                    d.cbopo = $('#cbopo').val();
                    d.cbonotes = $('#cbonotes').val();
                    d.txtctn_awal = $('#txtctn_awal').val();
                    d.txtctn_akhir = $('#txtctn_akhir').val();
                },
            },
            columns: [{
                    data: 'po',
                },
                {
                    data: 'no_carton',
                },
                {
                    data: 'ws',
                },
                {
                    data: 'color',
                },
                {
                    data: 'size',
                },
                {
                    data: 'dest',
                },
                {
                    data: 'qty',
                },
            ],

            columnDefs: [{
                "className": "align-left",
                "targets": "_all"
            }, ]

        });


        function dataTableSummaryReload() {
            datatable_summary.ajax.reload();
        }

        let datatable_summary = $("#datatable_summary").DataTable({
            "footerCallback": function(row, data, start, end, display) {
                var api = this.api(),
                    data;

                // converting to interger to find total
                var intVal = function(i) {
                    return typeof i === 'string' ?
                        i.replace(/[\$,]/g, '') * 1 :
                        typeof i === 'number' ?
                        i : 0;
                };

                // computing column Total of the complete result
                var sumTotal = api
                    .column(3)
                    .data()
                    .reduce(function(a, b) {
                        return intVal(a) + intVal(b);
                    }, 0);

                // Update footer by showing the total with the reference of the column index
                $(api.column(0).footer()).html('Total');
                $(api.column(3).footer()).html(sumTotal);
            },
            ordering: false,
            processing: true,
            serverSide: true,
            paging: false,
            destroy: true,
            autoWidth: true,
            scrollX: '300px',
            ajax: {
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: '{{ route('show_summary_karton_fg_retur') }}',
                dataType: 'json',
                dataSrc: 'data',
            },
            columns: [{
                    data: 'ws',
                },
                {
                    data: 'color',
                },
                {
                    data: 'size',
                },
                {
                    data: 'qty',
                },
                {
                    data: 'dest',
                },
                {
                    data: 'id_so_det',
                },
            ],

            columnDefs: [{
                    "className": "align-left",
                    "targets": "_all"
                },
                {
                    targets: [5],
                    render: (data, type, row, meta) => {
                        return `
                        <input type="hidden" readonly size="4" id="id_so_det[` + row.id_so_det + `]"
                        name="id_so_det[` + row.id_so_det + `]" value = "` + row.id_so_det + `"/>
                        <input type="hidden" size="4" id="qty[` + row.id_so_det + `]"
                        name="qty[` + row.id_so_det + `]" value = "` + row.qty + `"/>
                        <input type="hidden" size="4" id="curr[` + row.id_so_det + `]"
                        name="curr[` + row.id_so_det + `]" value = "` + row.curr + `"/>
                        <input type="hidden" size="4" id="price[` + row.id_so_det + `]"
                        name="price[` + row.id_so_det + `]" value = "` + row.price + `"/>
                    `;
                    }
                },

            ]

        });

        function dataTableDeleteReload() {
            datatable_delete.ajax.reload();
        }

        $('#datatable_delete thead tr').clone(true).appendTo('#datatable_delete thead');
        $('#datatable_delete thead tr:eq(1) th').each(function(i) {
            var title = $(this).text();
            $(this).html('<input type="text" class="form-control form-control-sm"/>');
            $('input', this).on('keyup change', function() {
                if (datatable_delete.column(i).search() !== this.value) {
                    datatable_delete
                        .column(i)
                        .search(this.value)
                        .draw();
                }
            });
        });
        let datatable_delete = $("#datatable_delete").DataTable({
            "footerCallback": function(row, data, start, end, display) {
                var api = this.api(),
                    data;

                // converting to interger to find total
                var intVal = function(i) {
                    return typeof i === 'string' ?
                        i.replace(/[\$,]/g, '') * 1 :
                        typeof i === 'number' ?
                        i : 0;
                };

                // computing column Total of the complete result
                var sumTotal = api
                    .column(6)
                    .data()
                    .reduce(function(a, b) {
                        return intVal(a) + intVal(b);
                    }, 0);

                // Update footer by showing the total with the reference of the column index
                $(api.column(0).footer()).html('Total');
                $(api.column(6).footer()).html(sumTotal);
            },
            ordering: false,
            processing: true,
            serverSide: true,
            paging: false,
            destroy: true,
            autoWidth: true,
            ajax: {
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: '{{ route('show_delete_karton_fg_retur') }}',
                dataType: 'json',
                dataSrc: 'data',
            },
            columns: [{
                    data: 'id',
                }, {
                    data: 'po',
                },
                {
                    data: 'no_carton',
                },
                {
                    data: 'ws',
                },
                {
                    data: 'color',
                },
                {
                    data: 'size',
                },
                {
                    data: 'qty',
                },
            ],
            columnDefs: [{
                    "className": "align-center",
                    "targets": "_all"
                },
                {
                    targets: [0],
                    render: (data, type, row, meta) => {
                        return `
                    <div
                        class="form-check checkbox-xl" style="text-align:center">
                        <input class="form-check-input" type="checkbox"
                        value="` + row.id + `" id="cek_data" onchange="ceklis(this)"
                        name="cek_data[` + row.id + `] "/>
                    </div>
                    <div>
                            <input type="hidden" size="10" id="id"
                            name="id[` + row.id + `]" value = "` + row.id + `"/>
                    </div>
                    `;
                    }
                },
            ]

        });
    </script>
@endsection
