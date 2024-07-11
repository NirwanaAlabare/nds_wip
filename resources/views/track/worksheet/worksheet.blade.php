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
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="card-title fw-bold mb-0"><i class="fas fa-ticket fa-sm"></i> Stock List</h5>
                <div class="d-flex justify-content-end gap-1">
                    <button type="button" class="btn btn-success" onclick="exportExcel(this);"><i class="fa fa-file-excel"></i></button>
                    <select class="form-select form-select-sm select2bs4 w-auto" id="ws-month-filter" readonly value="{{ date('m') }}">
                        <option value="" selected disabled>Bulan</option>
                        @foreach ($months as $month)
                            <option value="{{ $month['angka'] }}">{{ $month['nama'] }}</option>
                        @endforeach
                    </select>
                    <select class="form-select form-select-sm select2bs4 w-auto" id="ws-year-filter" readonly value="{{ date('Y') }}">
                        <option value="" selected disabled>Tahun</option>
                        @foreach ($years as $year)
                            <option value="{{ $year }}">{{ $year }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive w-100">
                <table class="table table-sm table-bordered W-100" id="ws-table">
                    <thead>
                        <tr>
                            <th>Tanggal Kirim</th>
                            <th>Worksheet</th>
                            <th>Style</th>
                            <th>Color</th>
                            <th>Size</th>
                            <th>Destination</th>
                            <th>Panel</th>
                            <th>Qty</th>
                            <th>Ratio</th>
                            <th>Ply Marker</th>
                            <th>Cut Marker</th>
                            <th>Ply Form</th>
                            <th>Cut Form</th>
                            <th>Stocker</th>
                            <th>DC</th>
                            <th>Secondary In</th>
                            <th>Secondary Inhouse</th>
                            <th>QC Sewing</th>
                            <th>Packing Sewing</th>
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
            $('#ws-month-filter').val((today.getMonth() + 1)).trigger("change");
            $('#ws-year-filter').val(todayYear).trigger("change");
        });

        $('#ws-table thead tr').clone(true).appendTo('#ws-table thead');
        $('#ws-table thead tr:eq(1) th').each(function(i) {
            if (i != 7 && i != 8 && i != 9 && i != 10 && i != 11 && i != 12 && i != 13 && i != 14 && i != 15 && i != 16 && i != 17 && i != 18) {
                var title = $(this).text();
                $(this).html('<input type="text" class="form-control form-control-sm" style="width:100%"/>');

                $('input', this).on('keyup change', function() {
                    if (wsTable.column(i).search() !== this.value) {
                        wsTable
                            .column(i)
                            .search(this.value)
                            .draw();
                    }
                });
            } else {
                $(this).empty();
            }
        });

        let wsTable = $("#ws-table").DataTable({
            ordering: false,
            processing: true,
            // serverSide: true,
            scrollY: "500px",
            scrollX: "500px",
            pageLength: 100,
            ajax: {
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: '{{ route('track-ws') }}',
                dataType: 'json',
                dataSrc: 'data',
                data: function(d) {
                    d.month = $('#ws-month-filter').val();
                    d.year = $('#ws-year-filter').val();
                },
            },
            columns: [
                {
                    data: 'tgl_kirim',
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
                    data: 'dest',
                },
                {
                    data: 'panel',
                },
                {
                    data: 'qty',
                },
                {
                    data: 'total_ratio_marker',
                    searchable: false
                },
                {
                    data: 'total_gelar_marker',
                    searchable: false
                },
                {
                    data: 'total_cut_marker',
                    searchable: false
                },
                {
                    data: 'total_lembar_form',
                    searchable: false
                },
                {
                    data: 'total_cut_form',
                    searchable: false
                },
                {
                    data: 'total_stocker',
                    searchable: false
                },
                {
                    data: 'total_dc',
                    searchable: false
                },
                {
                    data: 'total_sec',
                    searchable: false
                },
                {
                    data: 'total_sec_in',
                    searchable: false
                },
                {
                    data: 'output_sewing',
                    searchable: false
                },
                {
                    data: 'output_packing',
                    searchable: false
                },
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
                    targets: [1],
                    className: "text-nowrap",
                    render: function (data, type, row, meta) {
                        let column = '<a href="{{ route('track-ws-detail') }}/'+row.id_act_cost+'" target="_blank">'+data+'</a>';
                        return column;
                    }
                },
                {
                    targets: [7, 8, 9, 10, 11, 12, 13, 14, 15, 16],
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

        function dataTableReload() {
            wsTable.ajax.reload();
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
                url: "{{ route("track-ws-export") }}",
                type: 'post',
                data: {
                    month : $('#ws-month-filter').val(),
                    year : $('#ws-year-filter').val()
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
                    link.download = "Track WS - "+$('#ws-month-filter').val()+" - "+$('#ws-year-filter').val()+".xlsx";
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

        $('#ws-month-filter').on('change', () => {
            $('#ws-table').DataTable().ajax.reload();
        });
        $('#ws-year-filter').on('change', () => {
            $('#ws-table').DataTable().ajax.reload();
        });
    </script>
@endsection
