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
    <div class="card card-primary">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center ">
                <h5 class="card-title fw-bold mb-0"><i class="fas fa-shirt"></i> Input Transfer Garment Dari Sewing</h5>
                <a href="{{ route('transfer-garment') }}" class="btn btn-sm btn-light">
                    <i class="fa fa-reply"></i> Kembali
                </a>
            </div>
        </div>
        <form id="form_h" name='form_h' method='post'>
            <div class="card-body">
                <div class="row justify-content-center align-items-end">
                    <div class="col-md-12">
                        <div class="mb-3">
                            <label>Tujuan</label>
                            <select class="form-control select2bs4" id="cbotuj" name="cbotuj" style="width: 100%;">
                                <option selected="selected" value="" disabled="true">Pilih Tujuan</option>
                                @foreach ($data_tujuan as $datatujuan)
                                    <option value="{{ $datatujuan->isi }}">
                                        {{ $datatujuan->tampil }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="mb-3">
                            <input type="hidden" class="form-control " name="user" id="user"
                                value = "{{ $user }}">
                            <label>Line</label>
                            <select class="form-control select2bs4" id="cboline" name="cboline" style="width: 100%;"
                                onchange="getpo();getgarment();">
                                <option selected="selected" value="" disabled="true">Pilih Line</option>
                                @foreach ($data_line as $dataline)
                                    <option value="{{ $dataline->isi }}">
                                        {{ $dataline->tampil }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="mb-3">
                            <div class="form-group">
                                <label>No. PO</label>
                                <select class='form-control select2bs4 form-control-sm' style='width: 100%;' name='cbopo'
                                    id='cbopo' onchange="getgarment()"></select>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="mb-3">
                            <div class="form-group">
                                <label>Garment</label>
                                <select class='form-control select2bs4 form-control-sm' style='width: 100%;'
                                    name='cbogarment' id='cbogarment'></select>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="mb-3">
                            <div class="form-group">
                                <label>Qty</label>
                                <div class="input-group mb-3">
                                    <input type="number" class="form-control " name="txtqty" id="txtqty" min = "0"
                                        autocomplete="off">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text" id="inputGroup-sizing-sm">PCS</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="d-flex flex-row-reverse">
                    <a class="btn btn-outline-primary" onclick="tambah_data()">
                        <i class="fas fa-plus"></i>
                        Tambah
                    </a>
                </div>
            </div>
        </form>
        <div class="row">
            <div class="col-md-12">
                <div class="card card-primary card-outline">
                    <div class="card-header">
                        <h5 class="card-title"><i class="fas fa-list"></i> List Garment</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="datatable_tmp" class="table table-bordered 100 text-nowrap">
                                <thead>
                                    <tr>
                                        <th>Line</th>
                                        <th>PO</th>
                                        <th>WS</th>
                                        <th>Color</th>
                                        <th>Size</th>
                                        <th>Qty</th>
                                        <th>Act</th>
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


        function getpo() {
            let cboline = document.form_h.cboline.value;
                let html = $.ajax({
                type: "GET",
                url: '{{ route('get_po') }}',
                data: {
                    cbo_line: cboline
                },
                async: false
            }).responseText;
            // console.log(html != "");
            if (html != "") {
                $("#cbopo").html(html);
            }
        };

        function getgarment() {
            let cboline = document.form_h.cboline.value;
            let cbopo = document.form_h.cbopo.value;
            let html = $.ajax({
                type: "GET",
                url: '{{ route('get_garment') }}',
                data: {
                    cbo_line: cboline,
                    cbo_po: cbopo
                },
                async: false
            }).responseText;
            // console.log(html != "");
            if (html != "") {
                $("#cbogarment").html(html);
            }
        };

        function tambah_data() {
            let cboline = document.form_h.cboline.value;
            let cbopo = document.form_h.cbopo.value;
            let cbogarment = document.form_h.cbogarment.value;
            let txtqty = document.form_h.txtqty.value;
            $.ajax({
                type: "post",
                url: '{{ route('store_tmp_trf_garment') }}',
                data: {
                    cboline: cboline,
                    cbopo: cbopo,
                    cbogarment: cbogarment,
                    txtqty: txtqty
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
                    document.getElementById('txtqty').value = "";
                    $("#cbogarment").val('').trigger('change');
                    $("#cbopo").val(cbopo).trigger('change');
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
                    url: '{{ route('show_tmp_trf_garment') }}',
                    dataType: 'json',
                    dataSrc: 'data',
                    data: function(d) {
                        d.id = $('#id').val();
                    },
                },
                columns: [{
                        data: 'line',
                    },
                    {
                        data: 'po',
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
                        data: 'qty_tmp_trf_garment',
                    },
                ],
                columnDefs: [{
                    targets: [6],
                    render: (data, type, row, meta) => {
                        return `
                    <div
                    class='d-flex gap-1 justify-content-center'>
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
            document.getElementById('txtqty').value = "";
            // $("#cboline").val('').trigger('change');
            $("#cbopo").val('').trigger('change');
            $("#cbogarment").val('').trigger('change');
        }

        function hapus(id) {
            let cbopo = document.form_h.cbopo.value;
            $.ajax({
                type: "post",
                url: '{{ route('hapus_tmp_trf_garment') }}',
                data: {
                    id: id
                },
                success: async function(res) {
                    iziToast.error({
                        message: 'Data Berhasil Dihapus',
                        position: 'topCenter'
                    });
                    dataTableTmpReload();
                    document.getElementById('txtqty').value = "";
                    $("#cbogarment").val('').trigger('change');
                    $("#cbopo").val(cbopo).trigger('change');
                }
            });

        }

        function simpan() {
            let cbotuj = document.form_h.cbotuj.value;
            if (cbotuj == '') {
                iziToast.error({
                    message: 'Tujuan Kosong Harap Diisi',
                    position: 'topCenter'
                });
            } else {
                $.ajax({
                    type: "post",
                    url: '{{ route('store_trf_garment') }}',
                    data: {
                        cbotuj: cbotuj
                    },
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
            }
        };

        function undo() {
            let user = document.form_h.user.value;
            $.ajax({
                type: "post",
                url: '{{ route('undo-trf-garment') }}',
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
                url: '{{ route('reset-trf-garment') }}',
                data: {
                    user: user
                },
            });
        };
    </script>
@endsection
