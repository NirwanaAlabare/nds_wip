@extends('layouts.index')

@section('custom-link')
    <!-- DataTables -->
    <link rel="stylesheet" href="{{ asset('plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/datatables-responsive/css/responsive.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/datatables-buttons/css/buttons.bootstrap4.min.css') }}">
@endsection

@section('content')
    <div class="card">
        <div class="card-header bg-sb text-light">
            <h5 class="card-title fw-bold mb-0"><i class="fa-solid fa-list-check fa-sm"></i> Summary Loading</h5>
        </div>
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-end">
                <div class="d-flex align-items-end gap-3 mb-3">
                    <div>
                        <label class="form-label"><small>Tanggal</small></label>
                        <input type="date" class="form-control form-control-sm" id="tgl" name="tgl" value="{{ date('Y-m-d') }}" onchange="datatableLoadingLineReload()">
                    </div>
                    <div>
                        <button class="btn btn-primary btn-sm" onclick="datatableLoadingLineReload()"><i class="fa fa-search"></i></button>
                    </div>
                </div>
                <div class="d-flex align-items-end gap-3 mb-3">
                    <button class="btn btn-success btn-sm" onclick="exportExcel(this);"><i class="fa fa-file-excel fa-sm"></i> Excel</button>
                </div>
            </div>
            <div class="table-responsive">
                <table id="datatable-loading-line" class="table table-bordered table-sm w-100">
                    <thead>
                        <tr>
                            <th>Tanggal Loading</th>
                            <th>Line</th>
                            <th>No. WS</th>
                            <th>Style</th>
                            <th>Color</th>
                            <th>Loading Qty</th>
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
    <script src="{{ asset('plugins/datatables-rowsgroup/dataTables.rowsGroup.js') }}"></script>

    <script>
        let datatableLoadingLine = $("#datatable-loading-line").DataTable({
            ordering: false,
            processing: true,
            serverSide: true,
            pageLength: 25,
            ajax: {
                url: '{{ route('summary-loading') }}',
                data: function (d) {
                    d.tanggal = $("#tgl").val();
                }
            },
            columns: [
                {
                    data: 'tanggal_loading'
                },
                {
                    data: 'nama_line'
                },
                {
                    data: 'act_costing_ws',
                },
                {
                    data: 'style',
                },
                {
                    data: 'color'
                },
                {
                    data: 'loading_qty'
                },
            ],
            columnDefs: [
                {
                    targets: "_all",
                    className: 'align-middle text-nowrap'
                },
                {
                    targets: [5],
                    render: (data, type, row, meta) => {
                        return Number(data).toLocaleString('id-ID')
                    }
                }
            ],
            rowsGroup: [
                // Always the array (!) of the column-selectors in specified order to which rows groupping is applied
                // (column-selector could be any of specified in https://datatables.net/reference/type/column-selector)
                0,
                1,
            ],
        });

        function datatableLoadingLineReload() {
            datatableLoadingLine.ajax.reload()
        }

        $('#datatable-loading-line thead tr').clone(true).appendTo('#datatable-loading-line thead');
        $('#datatable-loading-line thead tr:eq(1) th').each(function(i) {
            var title = $(this).text();
            $(this).html('<input type="text" class="form-control form-control-sm" />');

            $('input', this).on('keyup change', function() {
                if (datatableLoadingLine.column(i).search() !== this.value) {
                    datatableLoadingLine
                        .column(i)
                        .search(this.value)
                        .draw();
                }
            });
        });

        function exportExcel(elm) {
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
                url: "{{ url("/loading-line/export-excel") }}",
                type: 'post',
                data: { tanggal:$('#tgl').val() },
                xhrFields: { responseType : 'blob' },
                success: function(res) {
                    elm.removeAttribute('disabled');
                    elm.innerText = "Export ";
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
                    link.download = "Summary Loading "+$('#tgl').val()+".xlsx";
                    link.click();
                }, error: function (jqXHR) {
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
