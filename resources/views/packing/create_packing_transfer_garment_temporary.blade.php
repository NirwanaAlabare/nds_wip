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
        .step-number {
            width: 22px;
            height: 22px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0;
            font-size: 12px;
        }
    </style>
@endsection

@section('content')
    <div class="card card-warning">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center ">
                <h5 class="card-title fw-bold mb-0"><i class="fas fa-warehouse"></i> Input Transfer Garment Dari Temporary
                </h5>
                <a href="{{ route('transfer-garment') }}" class="btn btn-sm btn-light">
                    <i class="fa fa-reply"></i> Kembali
                </a>
            </div>
        </div>
        <form id="form_h" name="form_h" method="post">
            <input type="hidden" name="user" id="user" value="{{ $user }}">
            <div class="card-body">

                <!-- Step 1 -->
                <div class="border rounded p-3 mb-3">
                    <div class="mb-2">
                        <span class="badge badge-primary mr-2 step-number">1</span>
                        <b>Tujuan Transfer</b>
                    </div>

                    <select class="form-control" id="cbotuj" name="tujuan" disabled>
                        <option value="Packing Central" selected>
                            Packing Central
                        </option>
                    </select>
                </div>

                <!-- Step 2 -->
                <div class="border rounded p-3 mb-3">
                    <div class="mb-3">
                        <span class="badge badge-primary mr-2 step-number">2</span>
                        <b>Filter Barang (Khusus Tipe Temporary Packing)</b>
                    </div>

                    <div class="row">
                        <div class="col-md-4">
                            <label>STYLE</label>
                            <select class="form-control select2bs4" id="cbstyle" name="style">
                                <option value="">-- Pilih Style --</option>

                                @foreach ($data_style as $data)
                                    <option value="{{ $data->styleno }}">
                                        {{ $data->styleno }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-8">
                            <label>WORKSHEET</label>
                            <select class="form-control select2bs4" id="cbworksheet" name="worksheet">
                                <option value="">-- Pilih Worksheet --</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Step 3 -->
                <div class="border rounded p-3">
                    <div class="mb-3">
                        <span class="badge badge-primary mr-2 step-number">3</span>
                        <b>Pilih Variant & Quantity</b>
                    </div>

                    <div class="row align-items-end">
                        <div class="col-md-3">
                            <label>COLOR</label>
                            <select class="form-control select2bs4" id="cbcolor" name="color">
                                <option value="">-- Pilih Color --</option>
                            </select>
                        </div>

                        <div class="col-md-3">
                            <label>SIZE</label>
                            <select class="form-control select2bs4" id="cbsize" name="size">
                                <option value="">-- Pilih Size --</option>
                            </select>
                        </div>

                        <div class="col-md-3">
                            <label>QTY TRANSFER</label>
                            <input type="number" class="form-control" id="qty_transfer" name="qty_transfer" value="" min="1">
                        </div>

                        <div class="col-md-3">
                            <button type="button"
                                    class="btn btn-success btn-block"
                                    onclick="tambah_data()">
                                <i class="fas fa-plus"></i>
                                Tambah
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
        <div class="row">
            <div class="col-md-12">
                <div class="card card-warning card-outline">
                    <div class="card-header">
                        <h5 class="card-title"><i class="fas fa-list"></i> List Garment</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="datatable_tmp" class="table table-bordered 100 text-nowrap">
                                <thead>
                                    <tr>
                                        <th>TIPE</th>
                                        <th>WORKSHEET</th>
                                        <th>STYLE</th>
                                        <th>COLOR</th>
                                        <th>SIZE</th>
                                        <th>QTY</th>
                                        <th>ACT</th>
                                    </tr>
                                </thead>
                                <tfoot>
                                    <tr>
                                        <th colspan="5"></th>
                                        <th></th>
                                        <th></th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                        <div class="d-flex justify-content-between">
                            <div class="p-2 bd-highlight">
                                <a class="btn btn-outline-warning" onclick="undo()">
                                    <i class="fas fa-sync-alt
                                    fa-spin"></i>
                                    Undo
                                </a>
                            </div>
                            <div class="p-2 bd-highlight">
                                <a class="btn btn-outline-success" onclick="simpan()">
                                    <i class="fas fa-check"></i>
                                    Simpan
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
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
    </script>
    <script>
        $(document).ready(function() {
            clear_h();
            reset();
            dataTableTmpReload();
        })

        $('#cbstyle').on('change', function() {
            getWs();
        });

        $('#cbworksheet').on('change', function() {
            getColor();
        });

        $('#cbcolor').on('change', function() {
            getSize();
        });

        $('#cbsize').on('change', function() {
            getQty();
        });

        $('#qty_transfer').on('input', function() {

            let max = parseInt($(this).attr('max')) || 0;
            let value = parseInt($(this).val()) || 0;

            if (value < 1) {
                $(this).val(1);
            }

            if (value > max) {
                $(this).val(max);
            }
        });

        function getWs() {
            let style = $('#cbstyle').val();

            $('#cbworksheet').empty();
            $('#cbcolor').empty();
            $('#cbsize').empty();
            $('#qty_transfer').val('').removeAttr('max');

            $('#cbworksheet').append(
                '<option value="">-- Pilih Worksheet --</option>'
            );

            $('#cbcolor').append(
                '<option value="">-- Pilih Color --</option>'
            );

            $('#cbsize').append(
                '<option value="">-- Pilih Size --</option>'
            );

            if (!style) {
                return;
            }

            $.ajax({
                url: "{{ route('get_ws_trf_garment_temporary') }}",
                type: "POST",
                data: {
                    _token: "{{ csrf_token() }}",
                    style: style
                },
                success: function(response) {

                    $('#cbworksheet').empty();

                    $('#cbworksheet').append(
                        '<option value="">-- Pilih Worksheet --</option>'
                    );

                    $.each(response, function(index, item) {
                        $('#cbworksheet').append(
                            `<option value="${item.ws}">${item.ws}</option>`
                        );
                    });
                }
            });
        }

        function getColor() {
            let style = $('#cbstyle').val();
            let ws = $('#cbworksheet').val();

            $('#cbcolor').empty();
            $('#cbsize').empty();
            $('#qty_transfer').val('').removeAttr('max');

            $('#cbcolor').append(
                '<option value="">-- Pilih Color --</option>'
            );

            $('#cbsize').append(
                '<option value="">-- Pilih Size --</option>'
            );

            if (!style || !ws) {
                return;
            }

            $.ajax({
                url: "{{ route('get_color_trf_garment_temporary') }}",
                type: "POST",
                data: {
                    _token: "{{ csrf_token() }}",
                    style: style,
                    ws: ws
                },
                success: function(response) {

                    $.each(response, function(index, item) {
                        $('#cbcolor').append(
                            `<option value="${item.color}">${item.color}</option>`
                        );
                    });
                }
            });
        }

        function getSize() {

            let style = $('#cbstyle').val();
            let ws = $('#cbworksheet').val();
            let color = $('#cbcolor').val();

            $('#cbsize').empty();
            $('#qty_transfer').val('').removeAttr('max');

            $('#cbsize').append(
                '<option value="">-- Pilih Size --</option>'
            );

            if (!style || !ws || !color) {
                return;
            }

            $.ajax({
                url: "{{ route('get_size_trf_garment_temporary') }}",
                type: "POST",
                data: {
                    _token: "{{ csrf_token() }}",
                    style: style,
                    ws: ws,
                    color: color
                },
                success: function(response) {

                    $.each(response, function(index, item) {

                        $('#cbsize').append(
                            `<option value="${item.size}" data-id-so-det="${item.id_so_det}">
                                ${item.size} - ${item.qty} PCS
                            </option>`
                        );

                    });

                    // Refresh Select2
                    $('#cbsize').trigger('change');
                }
            });
        }

        $('#cbsize').select2({
            theme: 'bootstrap4',

            templateResult: function(item) {

                if (!item.id) {
                    return item.text;
                }

                let parts = item.text.split(' - ');

                return $(`
                    <div style="
                        display: flex;
                        justify-content: space-between;
                        align-items: center;
                        width: 100%;
                    ">
                        <span>${parts[0]}</span>

                        <span style="
                            background-color: #28a745;
                            color: white;
                            padding: 2px 6px;
                            border-radius: 3px;
                            font-weight: bold;
                            font-size: 12px;
                            line-height: 16px;
                        ">
                            ${parts[1]}
                        </span>
                    </div>
                `);
            },

            templateSelection: function(item) {
                if (!item.id) {
                    return item.text;
                }

                let parts = item.text.split(' - ');

                return $(`
                    <span style="
                        display: flex;
                        justify-content: space-between;
                        align-items: center;
                        width: 100%;
                        line-height: normal;
                    ">
                        <span style="
                            line-height: 35px;
                        ">
                            ${parts[0]}
                        </span>

                        <span style="
                            background-color: #28a745;
                            color: white;
                            padding: 1px 5px;
                            border-radius: 3px;
                            font-weight: bold;
                            font-size: 11px;
                            line-height: 14px;
                        ">
                            ${parts[1]}
                        </span>
                    </span>
                `);
            }
        });

        function getQty() {
            let style = $('#cbstyle').val();
            let ws = $('#cbworksheet').val();
            let color = $('#cbcolor').val();
            let size = $('#cbsize').val();

            $('#qty_transfer').val('').removeAttr('max');

            if (!style || !ws || !color || !size) {
                return;
            }

            $.ajax({
                url: "{{ route('get_qty_trf_garment_temporary') }}",
                type: "POST",
                data: {
                    _token: "{{ csrf_token() }}",
                    style: style,
                    ws: ws,
                    color: color,
                    size: size
                },
                success: function(response) {

                    $('#qty_transfer')
                        .val(response.qty)
                        .attr('max', response.qty);
                }
            });
        }

        function getgarment() {
            let cbopo = document.form_h.cbopo.value;
            let html = $.ajax({
                type: "GET",
                url: '{{ route('get_garment_temporary') }}',
                data: {
                    cbo_po: cbopo
                },
                async: false
            }).responseText;
            // console.log(cbopo);
            if (html != "") {
                $("#cbogarment").html(html);
            }
        };

        function tambah_data() {
            let id_so_det = $('#cbsize option:selected').data('id-so-det');
            let qty_transfer = document.form_h.qty_transfer.value;

            $.ajax({
                type: "post",
                url: '{{ route('store_tmp_trf_garment_temporary') }}',
                data: {
                    id_so_det: id_so_det,
                    qty_transfer: qty_transfer,
                },
                success: function(response) {
                    if (response.icon == 'salah') {
                        iziToast.warning({
                            message: response.msg,
                            position: 'topCenter'
                        });
                    } else {
                        iziToast.success({
                            message: response.msg,
                            position: 'topCenter'
                        });
                    }
                    
                    dataTableTmpReload();
                    // $("#cbstyle").val('').trigger('change');
                    // $("#cbworksheet").val('').trigger('change');
                    $("#cbcolor").val('').trigger('change');
                    $("#cbsize").val('').trigger('change');
                    $("#qty_transfer").val('');
                },
                // error: function(request, status, error) {
                //     alert(request.responseText);
                // },
            });
        };

        function dataTableTmpReload() {
            let datatable = $("#datatable_tmp").DataTable({

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
                        .column(5)
                        .data()
                        .reduce(function(a, b) {
                            return intVal(a) + intVal(b);
                        }, 0);

                    // Update footer by showing the total with the reference of the column index
                    $(api.column(0).footer()).html('Total');
                    $(api.column(5).footer()).html(sumTotal);
                },

                ordering: false,
                processing: true,
                serverSide: true,
                paging: false,
                destroy: true,
                ajax: {
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    url: '{{ route('show_tmp_trf_garment_temporary') }}',
                    dataType: 'json',
                    dataSrc: 'data',
                    data: function(d) {
                        d.id = $('#id').val();
                    },
                },
                columns: [
                    {
                        data: 'tipe',
                    },
                    {
                        data: 'ws',
                    },
                    {
                        data: 'styleno',
                    },
                    {
                        data: 'color',
                    },
                    {
                        data: 'size',
                    },
                    {
                        data: 'qty_tmp_trf_garment',
                    },
                ],
                columnDefs: [{
                    targets: [6],
                    render: (data, type, row, meta) => {
                        return `
                            <div class='d-flex gap-1 justify-content-center'>
                                <a  class='btn btn-sm' data-bs-toggle='tooltip' onclick="hapus('` + row.id_tmp_trf_garment + `');">
                                    <i class='fas fa-minus-square fa-lg' style='color: #ff0000;'></i>
                                </a>
                            </div>
                        `;
                    }
                }, ]
            });
        }

        function clear_h() {
            $("#qty_transfer").val('');
            // $("#cbopo").val('').trigger('change');
            // $("#cbogarment").val('').trigger('change');
        }

        function hapus(id) {
            $.ajax({
                type: "post",
                url: '{{ route('hapus_tmp_trf_garment_temporary') }}',
                data: {
                    id: id
                },
                success: async function(res) {
                    iziToast.error({
                        message: 'Data Berhasil Dihapus',
                        position: 'topCenter'
                    });
                    dataTableTmpReload();
                    $("#cbogarment").val('').trigger('change');
                    $("#cbcolor").val('').trigger('change');
                    $("#cbsize").val('').trigger('change');
                    $("#qty_transfer").val('');
                    getColor();
                }
            });

        }

        function simpan() {
            $.ajax({
                type: "post",
                url: '{{ route('store_trf_garment_temporary') }}',
                success: function(response) {
                    if (response.icon == 'salah') {
                        iziToast.warning({
                            message: response.msg,
                            position: 'topCenter'
                        });
                    } else {
                        Swal.fire({
                            text: response.msg,
                            icon: "success",
                            title: response.title
                        });
                    }
                    dataTableTmpReload();
                    clear_h();
                },
                error: function(request, status, error) {
                    iziToast.warning({
                        message: 'Data Temporary Kosong cek lagi',
                        position: 'topCenter'
                    });
                },
            });

        };

        function undo() {
            let user = document.form_h.user.value;
            $.ajax({
                type: "post",
                url: '{{ route('undo_trf_garment_temporary') }}',
                data: {
                    user: user
                },
                success: function(response) {
                    if (response.icon == 'salah') {
                        iziToast.warning({
                            message: response.msg,
                            position: 'topCenter'
                        });
                    } else {
                        iziToast.success({
                            message: response.msg,
                            position: 'topCenter'
                        });
                    }
                    dataTableTmpReload();
                },
                // error: function(request, status, error) {
                //     alert(request.responseText);
                // },
            });
        };

        function reset() {
            let user = document.form_h.user.value;
            $.ajax({
                type: "post",
                url: '{{ route('reset_trf_garment_temporary') }}',
                data: {
                    user: user
                },
            });
        };
    </script>
@endsection
