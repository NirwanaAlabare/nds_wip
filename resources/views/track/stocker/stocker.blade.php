@php
    if (!isset($head)) {
        $head = "";
    }
@endphp

@extends('layouts.index' , ["head" => $head])

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
        <div class="card-header bg-sb text-light">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="card-title fw-bold mb-0"><i class="fa-solid fa-receipt"></i> Stock List</h5>
                <div class="d-flex justify-content-end gap-1">
                    <button type="button" class="btn btn-success" onclick="exportExcel(this);"><i class="fa fa-file-excel"></i></button>
                    <select class="form-select form-select-sm select2bs4 w-auto" id="month-filter" readonly value="{{ date('m') }}">
                        <option value="" selected disabled>Bulan</option>
                        @foreach ($months as $month)
                            <option value="{{ $month['angka'] }}">{{ $month['nama'] }}</option>
                        @endforeach
                    </select>
                    <select class="form-select form-select-sm select2bs4 w-auto" id="year-filter" readonly value="{{ date('Y') }}">
                        <option value="" selected disabled>Tahun</option>
                        @foreach ($years as $year)
                            <option value="{{ $year }}">{{ $year }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table w-100" id="stocker-table">
                    <thead>
                        <tr>
                            <th>Action</th>
                            <th>Tanggal Kirim</th>
                            <th>WS</th>
                            <th>Style</th>
                            <th>Color</th>
                            <th>Stocker Qty</th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
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

    <!-- Select2 -->
    <script src="{{ asset('plugins/select2/js/select2.full.min.js') }}"></script>
    <script>
        $('.select2').select2()
        $('.select2bs4').select2({
            theme: 'bootstrap4',
        })
    </script>

    <script>
        $(document).ready(async function() {
            let today = new Date();
            let todayDate = ("0" + today.getDate()).slice(-2);
            let todayMonth = ("0" + (today.getMonth() + 1)).slice(-2);
            let todayYear = today.getFullYear();
            let todayFullDate = todayYear + '-' + todayMonth + '-' + todayDate;

            // Marker Datatable
            $('#month-filter').val((today.getMonth() + 1)).trigger("change");
            $('#year-filter').val(todayYear).trigger("change");
        });

        $('#month-filter').on("change", function () {
            if ($("#month-filter").val() && $("#year-filter").val()) {
                stockerTableReload();
            }
        });

        $('#year-filter').on("change", function () {
            if ($("#month-filter").val() && $("#year-filter").val()) {
                stockerTableReload();
            }
        });

        $('#stocker-table thead tr').clone(true).appendTo('#stocker-table thead');
        $('#stocker-table thead tr:eq(1) th').each(function(i) {
            if (i != 0) {
                var title = $(this).text();
                $(this).html('<input type="text" class="form-control form-control-sm" style="width:100%"/>');

                $('input', this).on('keyup change', function() {
                    if (stockerTable.column(i).search() !== this.value) {
                        stockerTable
                            .column(i)
                            .search(this.value)
                            .draw();
                    }
                });
            } else {
                $(this).empty();
            }
        });

        var stockerTable = $("#stocker-table").DataTable({
            ordering: false,
            processing: true,
            scrollY: "500px",
            pageLength: 100,
            ajax: {
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: '{{ route('track-stocker') }}',
                dataType: 'json',
                data: function(d) {
                    d.month = $('#month-filter').val();
                    d.year = $('#year-filter').val();
                },
            },
            columns: [
                {
                    data: 'id_act_cost',
                },
                {
                    data: 'tgl_kirim',
                },
                {
                    data: 'act_costing_ws',
                },
                {
                    data: 'styleno',
                },
                {
                    data: 'color',
                },
                {
                    data: 'qty',
                }
            ],
            columnDefs: [
                // Act Column
                // {
                //     targets: [0],
                //     render: (data, type, row, meta) => {
                //         return `<div class='d-flex gap-1 justify-content-center'> <a class='btn btn-primary btn-sm' href='{{ route("show-stocker") }}/`+row.form_cut_id+`' data-bs-toggle='tooltip'><i class='fa fa-search-plus'></i></a> </div>`;
                //     }
                // },
                // // No. Meja Column
                // {
                //     targets: [3],
                //     className: "text-nowrap",
                //     render: (data, type, row, meta) => data ? data.toUpperCase() : "-"
                // },
                // Text No Wrap
                {
                    targets: [0],
                    className: "text-nowrap text-center",
                    render: function (data, type, row, meta) {
                        let column = '<a href="{{ $page == 'dashboard-stocker' ? route('dashboard-stocker-show') : route('track-stocker-detail') }}/'+data+'" target="_blank" class="btn btn-success btn-sm"><i class="fa fa-search-plus"></i></a>';
                        return column;
                    }
                },
                {
                    targets: [5],
                    className: "text-nowrap",
                    render: function (data, type, row, meta) {
                        return Number(data).toLocaleString("id-ID");
                    }
                },
                {
                    targets: "_all",
                    className: "text-nowrap"
                },
            ]
        });

        function stockerTableReload() {
            $("#stocker-table").DataTable().ajax.reload();
        }

        function exportExcel (elm) {
            elm.setAttribute('disabled', 'true');
            elm.innerText = "";
            let loading = document.createElement('div');
            loading.classList.add('loading-small');
            elm.appendChild(loading);

            iziToast.info({
                title: 'Exporting...',
                message: 'Data sedang di export. Mohon tunggu...',
                position: 'topCenter'
            });

            let date = new Date();

            let day = date.getDate();
            let month = date.getMonth() + 1;
            let year = date.getFullYear();

            // This arrangement can be altered based on how we want the date's format to appear.
            let currentDate = `${day}-${month}-${year}`;

            $.ajax({
                url: "{{ route("track-stocker-export") }}",
                type: 'post',
                data: {
                    month : $('#month-filter').val(),
                    year : $('#year-filter').val()
                },
                xhrFields: { responseType : 'blob' },
                success: function(res) {
                    elm.removeChild(loading);
                    elm.removeAttribute('disabled');
                    let icon = document.createElement('i');
                    icon.classList.add('fa-solid');
                    icon.classList.add('fa-file-excel');
                    elm.appendChild(icon);

                    iziToast.success({
                        title: 'Success',
                        message: 'Success',
                        position: 'topCenter'
                    });

                    var blob = new Blob([res]);
                    var link = document.createElement('a');
                    link.href = window.URL.createObjectURL(blob);
                    link.download = "Track Stocker - "+$('#month-filter').val()+" - "+$('#year-filter').val()+".xlsx";
                    link.click();
                }, error: function (jqXHR) {
                    elm.removeChild(loading);
                    let res = jqXHR.responseJSON;
                    let message = '';
                    console.log(res.message);
                    for (let key in res.errors) {
                        message += res.errors[key]+' ';
                        document.getElementById(key).classList.add('is-invalid');
                    };
                    iziToast.error({
                        title: 'Error',
                        message: message,
                        position: 'topCenter'
                    });
                }
            });
        }
    </script>
@endsection
