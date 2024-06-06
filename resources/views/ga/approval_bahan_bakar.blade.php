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
    <div class="d-flex justify-content-between mb-3">
        <h5 class="fw-bold text-dark">Approval Bahan Bakar <i class="fas fa-gas-pump"></i></h5>
    </div>
    <form id="form" name='form' method='post' action="{{ route('store-approval-bahan-bakar') }}"
        onsubmit="submitForm(this, event)">
        <div class="card card-success">
            <div class="card-header">
                <h5 class="card-title fw-bold mb-0"><i class="fas fa-list"></i> List Transaksi</h5>
            </div>
            <div class="card-body">
                <div class="p-2 bd-highlight">
                    <button type="submit" class="btn btn-outline-success">Simpan </button>
                </div>
                <div class="table-responsive">
                    <table id="datatable-trans" class="table table-bordered table-sm w-100 table-hover display nowrap">
                        <thead class="table-primary">
                            <tr style='text-align:center; vertical-align:middle'>
                                <th>Act</th>
                                <th><input type="checkbox" onclick="toggle(this);"></th>
                                <th>No. Form</th>
                                <th>Tgl. Trans</th>
                                <th>Plat No</th>
                                <th>Driver</th>
                                <th>Kendaraan</th>
                                <th>Oddometer</th>
                                <th>Bahan Bakar</th>
                                <th>Jumlah</th>
                                <th>Total Pembayaran</th>
                                <th>Jumlah Realisasi</th>
                                <th>Realisasi Bayar</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
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
        });
    </script>
    <script>
        $(document).ready(function() {
            dataTableTransReload();
        })

        function notif() {
            alert("Maaf, Fitur belum tersedia!");
        }
    </script>

    <script>
        function dataTableTransReload() {
            let datatable_m_kendaraan = $("#datatable-trans").DataTable({
                processing: true,
                serverSide: true,
                paging: true,
                searching: true,
                info: false,
                ordering: false,
                destroy: true,
                ajax: {
                    url: '{{ route('approval-bahan-bakar') }}',
                },
                columns: [{
                        data: 'id'
                    },
                    {
                        data: 'id'
                    },
                    {
                        data: 'no_trans'

                    },
                    {
                        data: 'tgl_trans_fix'

                    },
                    {
                        data: 'plat_no'
                    },
                    {
                        data: 'nm_driver'
                    },
                    {
                        data: 'jns_kendaraan'
                    },
                    {
                        data: 'oddometer'
                    },
                    {
                        data: 'nm_bhn_bakar'
                    },
                    {
                        data: 'jml_fix'
                    },
                    {
                        data: 'tot_biaya_fix'
                    },
                    {
                        data: 'realisasi_jml_fix'
                    },
                    {
                        data: 'tot_biaya_realisasi_fix'
                    },
                    {
                        data: 'status'
                    },
                ],
                columnDefs: [{
                        targets: [0],
                        render: (data, type, row, meta) => {
                            return `
                <div
                class='d-flex gap-1 justify-content-center'>
                <a class='btn btn-secondary btn-sm' data-bs-toggle='tooltip'
                onclick="export_pdf(` + row.id + `)"><i class='fas fa-print'></i></a>
                </div>
                    `;
                        }
                    },
                    {
                        targets: [1],
                        render: (data, type, row, meta) => {
                            return `
                            <div
                        class="form-check checkbox-xl" style="text-align:center">
                        <input class="form-check-input" type="checkbox"
                        value="` + row.id + `" id="cek_data" onchange="ceklis(this)"
                        name="cek_data[` + row.id + `] "/>
                    </div>
                    `;
                        }
                    },
                    {
                        "className": "align-middle",
                        "targets": "_all"
                    },
                    {
                        targets: '_all',
                        render: (data, type, row, meta) => {
                            var color = '#000000';
                            if (row.status == 'WAITING') {
                                color = '#7b7d7d';
                            } else if (row.status == 'PENDING APPROVE') {
                                color = '#0000FF';
                            } else if (row.status == 'APPROVE') {
                                color = '#008000';
                            } else if (row.status == 'CANCEL') {
                                color = '#FF0000';
                            }
                            return '<span style="font-weight: 600; color:' + color + '">' + data +
                                '</span>';
                        }
                    }



                ]
            });
        }

        function export_pdf(id_n) {
            let id = id_n;
            Swal.fire({
                title: 'Please Wait...',
                html: 'Exporting Data...',
                didOpen: () => {
                    Swal.showLoading()
                },
                allowOutsideClick: false,
            });

            $.ajax({
                type: "get",
                url: '{{ route('export_pdf_pengajuan_bhn_bakar') }}',
                data: {
                    id: id
                },
                xhrFields: {
                    responseType: 'blob'
                },
                success: function(response) {
                    {
                        swal.close();
                        Swal.fire({
                            title: 'Data Sudah Di Export!',
                            icon: "success",
                            showConfirmButton: true,
                            allowOutsideClick: false
                        });
                        var blob = new Blob([response]);
                        var link = document.createElement('a');
                        link.href = window.URL.createObjectURL(blob);
                        link.download = "a.pdf";
                        link.click();

                    }
                },
            });
        }

        function ceklis(checkeds) {
            //get id..and check if checked
            console.log($(checkeds).attr("value"), checkeds.checked)

        }

        function toggle(source) {
            var checkboxes = document.querySelectorAll('input[type="checkbox"]');
            for (var i = 0; i < checkboxes.length; i++) {
                if (checkboxes[i] != source)
                    checkboxes[i].checked = source.checked;
            }
        }
    </script>
@endsection
