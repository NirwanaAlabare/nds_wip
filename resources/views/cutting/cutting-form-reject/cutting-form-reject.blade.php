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
            <h5 class="card-title fw-bold"><i class="fa-solid fa-file-circle-exclamation"></i> Form Ganti Reject</h5>
        </div>
        <div class="card-body">
            <a href="{{ route('create-cutting-reject') }}" class="btn btn-success btn mb-3"><i class="fa fa-plus"></i> Baru</a>
            <div class="d-flex justify-content-between align-items-end mb-3">
                <div class="d-flex justify-content-start align-items-end gap-3">
                    <div>
                        <label class="form-label">Dari</label>
                        <input type="date" class="form-control" value="{{ date("Y-m-d", strtotime(date("Y-m-d")." - 7 days")) }}" id="date-from" name="date-from" onchange="cuttingRejectTableReload()">
                    </div>
                    <div>
                        <label class="form-label">Sampai</label>
                        <input type="date" class="form-control" value="{{ date("Y-m-d") }}" id="date-to" name="date-to" onchange="cuttingRejectTableReload()">
                    </div>
                    <div>
                        <button class="btn btn-sb" onclick="cuttingRejectTableReload()"><i class="fa fa-search"></i></button>
                    </div>
                </div>
                <div class="d-flex gap-1">
                    {{-- <button class="btn btn-success" onclick="exportExcel(this)"><i class="fa fa-file-excel"></i></button> --}}
                </div>
            </div>
            <div class="table-responsive">
                <table class="table table-bordered" id="cutting-reject-table">
                    <thead>
                        <th>Action</th>
                        <th>Tanggal</th>
                        <th>No. Form</th>
                        <th>Panel</th>
                        <th>No. WS</th>
                        <th>Style</th>
                        <th>Color</th>
                        <th>Size</th>
                        <th>Qty</th>
                    </thead>
                    <tbody>
                    </tbody>
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
        document.addEventListener("DOMContentLoaded", () => {
            let oneWeeksBefore = new Date(new Date().setDate(new Date().getDate() - 7));
            let oneWeeksBeforeDate = ("0" + oneWeeksBefore.getDate()).slice(-2);
            let oneWeeksBeforeMonth = ("0" + (oneWeeksBefore.getMonth() + 1)).slice(-2);
            let oneWeeksBeforeYear = oneWeeksBefore.getFullYear();
            let oneWeeksBeforeFull = oneWeeksBeforeYear + '-' + oneWeeksBeforeMonth + '-' + oneWeeksBeforeDate;

            $("#date-from").val(oneWeeksBeforeFull).trigger("change");

            window.addEventListener("focus", () => {
                $('#cutting-reject-table').DataTable().ajax.reload(null, false);
            });
        });

        $('.select2').select2()
        $('.select2bs4').select2({
            theme: 'bootstrap4',
            dropdownParent: $("#editMejaModal")
        })

        let cuttingRejectTable = $("#cutting-reject-table").DataTable({
            processing: true,
            ordering: false,
            serverSide: true,
            ajax: {
                url: '{{ route('cutting-reject') }}',
                data: function(d) {
                    d.dateFrom = $('#date-from').val();
                    d.dateTo = $('#date-to').val();
                },
            },
            columns: [
                {
                    data: 'id'
                },
                {
                    data: 'tanggal'
                },
                {
                    data: 'no_form'
                },
                {
                    data: 'panel'
                },
                {
                    data: 'act_costing_ws'
                },
                {
                    data: 'style'
                },
                {
                    data: 'color'
                },
                {
                    data: 'sizes'
                },
                {
                    data: 'qty'
                },
            ],
            columnDefs: [
                {
                    targets: [0],
                    className: "text-nowrap",
                    render: (data, type, row, meta) => {
                        let buttonEdit = `<a href="{{ route('edit-cutting-reject') }}/`+data+`" class="btn btn-sb-secondary btn-sm mx-1"><i class="fa fa-edit"></i></a>`;
                        let buttonDetail = `<a href="{{ route('show-cutting-reject') }}/`+data+`" class="btn btn-sb btn-sm mx-1"><i class="fa fa-search"></i></a>`;
                        let buttonDelete = `<a href='javascript:void(0);' class='btn btn-danger btn-sm mx-1' data='`+JSON.stringify(row)+`' data-url='`+'{{ route('destroy-cutting-reject') }}'+`/`+data+`' onclick='deleteData(this);'><i class='fa fa-trash'></i></a>`;

                        return buttonEdit+buttonDetail+buttonDelete;
                    }
                },
                {
                    targets: "_all",
                    className: "text-nowrap",
                    render: (data, type, row, meta) => {
                        return data;
                    }
                }
            ]
        });

        function cuttingRejectTableReload() {
            $("#cutting-reject-table").DataTable().ajax.reload();
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
                url: "{{ route("export-form-reject") }}",
                type: 'post',
                data: {
                    dateFrom : $("#date-from").val(),
                    dateTo : $("#date-to").val()
                },
                xhrFields: { responseType : 'blob' },
                success: function(res) {
                    elm.removeChild(loading);
                    elm.removeAttribute('disabled');
                    let icon = document.createElement('i');
                    icon.classList.add('fa-solid');
                    icon.classList.add('fa', 'fa-file-excel');
                    elm.appendChild(icon);
                    elm.innerHTML += " Export";

                    iziToast.success({
                        title: 'Success',
                        message: 'Success',
                        position: 'topCenter'
                    });

                    var blob = new Blob([res]);
                    var link = document.createElement('a');
                    link.href = window.URL.createObjectURL(blob);
                    link.download = "Form Reject "+$("#date-from").val()+" - "+$("#date-to").val()+".xlsx";
                    link.click();
                }, error: function (jqXHR) {
                    elm.removeChild(loading);
                    elm.removeAttribute('disabled');
                    let icon = document.createElement('i');
                    icon.classList.add('fa', 'fa-file-excel');
                    elm.appendChild(icon);
                    elm.innerHTML += " Export";

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
