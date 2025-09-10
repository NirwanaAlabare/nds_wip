@extends('layouts.index')

@section('custom-link')
    {{-- <!-- DataTables -->
    <link rel="stylesheet" href="{{ asset('plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/datatables-responsive/css/responsive.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/datatables-buttons/css/buttons.bootstrap4.min.css') }}"> --}}

    <!-- DataTables CSS -->
    <link rel="stylesheet" href="{{ asset('plugins/datatables 2.0/jquery.dataTables.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/datatables 2.0/dataTables.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/datatables 2.0/fixedColumns.bootstrap4.min.css') }}">
    <!-- jQuery -->
    <script src="{{ asset('plugins/datatables 2.0/jquery-3.3.1.js') }}"></script>


    <!-- Select2 -->
    <link rel="stylesheet" href="{{ asset('plugins/select2/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}">

    <style>
        .checkbox-cell-wrapper {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 50px;
            height: 100%;
            padding: 0;
        }
    </style>
@endsection

@section('content')
    <div class="card card-sb">
        <div class="card-header">
            <h5 class="card-title fw-bold mb-0"><i class="fas fa-list"></i> Fabric Relaxation</h5>
        </div>
        <div class="card-body">
            <div class="mb-3">
                <a href="{{ route('input_fabric_relaxation') }}" class="btn btn-outline-primary position-relative btn-sm">
                    <i class="fas fa-plus"></i>
                    New
                </a>
            </div>

            <div class="d-flex align-items-end gap-3 mb-3">
                <div class="mb-3">
                    <label class="form-label"><small><b>Tgl Awal</b></small></label>
                    <input type="date" class="form-control form-control-sm " id="tgl-awal" name="tgl_awal"
                        value="{{ $tgl_skrg_min_sebulan }}">
                </div>
                <div class="mb-3">
                    <label class="form-label"><small><b>Tgl Akhir</b></small></label>
                    <input type="date" class="form-control form-control-sm" id="tgl-akhir" name="tgl_akhir"
                        value="{{ date('Y-m-d') }}">
                </div>
                <div class="mb-3">
                    <a class="btn btn-outline-primary position-relative btn-sm" onclick="dataTableReload()">
                        <i class="fas fa-search"></i>
                        Cari
                    </a>
                </div>
                <div class="mb-3">
                    <a onclick="notif_print()" class="btn btn-outline-danger position-relative btn-sm">
                        <i class="fas fa-print fa-sm"></i>
                        Print
                    </a>
                </div>

                <div class="mb-3">
                    <a onclick="notif()" class="btn btn-outline-success position-relative btn-sm">
                        <i class="fas fa-file-excel fa-sm"></i>
                        Export Excel
                    </a>
                </div>
            </div>
            <div class="mb-2">
                <span class="badge bg-info text-dark">
                    <span id="checkedCount">0</span> form(s) selected
                </span>
            </div>
            <div class="table-responsive">
                <table id="datatable" class="table table-bordered table-hover align-middle text-nowrap w-100">
                    <thead class="bg-sb">
                        <tr>
                            <th scope="col" class="text-center align-middle" style="color: black;">Act</th>
                            <th scope="col" class="text-center align-middle">
                                <input type="checkbox" id="selectAllCheckbox" />
                            </th>
                            <th scope="col" class="text-center align-middle">Tgl. Relax</th>
                            <th scope="col" class="text-center align-middle">Operator</th>
                            <th scope="col" class="text-center align-middle">No. Mesin</th>
                            <th scope="col" class="text-center align-middle">No. Form</th>
                            <th scope="col" class="text-center align-middle">Tgl. BPB</th>
                            <th scope="col" class="text-center align-middle">No. PL</th>
                            <th scope="col" class="text-center align-middle">Buyer</th>
                            <th scope="col" class="text-center align-middle">WS</th>
                            <th scope="col" class="text-center align-middle">Style</th>
                            <th scope="col" class="text-center align-middle">Color</th>
                            <th scope="col" class="text-center align-middle">Lot</th>
                            <th scope="col" class="text-center align-middle">ID Item</th>
                            <th scope="col" class="text-center align-middle">Detail Item</th>
                            <th scope="col" class="text-center align-middle">Barcode</th>
                            <th scope="col" class="text-center align-middle">No. Roll</th>
                            <th scope="col" class="text-center align-middle">Durasi Relax (Jam)</th>
                            <th scope="col" class="text-center align-middle">Start Date</th>
                            <th scope="col" class="text-center align-middle">Start Time</th>
                            <th scope="col" class="text-center align-middle">Finish Date</th>
                            <th scope="col" class="text-center align-middle">Finish Time</th>
                            <th scope="col" class="text-center align-middle">Total (Hari)</th>
                        </tr>
                        <tr>
                            <th></th> <!-- Empty cell for Act (no search input) -->
                            <th></th>
                            <th><input type="text" class="column-search form-control form-control-sm" /></th>
                            <th><input type="text" class="column-search form-control form-control-sm" /></th>
                            <th><input type="text" class="column-search form-control form-control-sm" /></th>
                            <th><input type="text" class="column-search form-control form-control-sm" /></th>
                            <th><input type="text" class="column-search form-control form-control-sm" /></th>
                            <th><input type="text" class="column-search form-control form-control-sm" /></th>
                            <th><input type="text" class="column-search form-control form-control-sm" /></th>
                            <th><input type="text" class="column-search form-control form-control-sm" /></th>
                            <th><input type="text" class="column-search form-control form-control-sm" /></th>
                            <th><input type="text" class="column-search form-control form-control-sm" /></th>
                            <th><input type="text" class="column-search form-control form-control-sm" /></th>
                            <th><input type="text" class="column-search form-control form-control-sm" /></th>
                            <th><input type="text" class="column-search form-control form-control-sm" /></th>
                            <th><input type="text" class="column-search form-control form-control-sm" /></th>
                            <th><input type="text" class="column-search form-control form-control-sm" /></th>
                            <th><input type="text" class="column-search form-control form-control-sm" /></th>
                            <th><input type="text" class="column-search form-control form-control-sm" /></th>
                            <th><input type="text" class="column-search form-control form-control-sm" /></th>
                            <th><input type="text" class="column-search form-control form-control-sm" /></th>
                            <th><input type="text" class="column-search form-control form-control-sm" /></th>
                            <th><input type="text" class="column-search form-control form-control-sm" /></th>

                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
@endsection

@section('custom-script')
    <!-- DataTables & Plugins -->
    <script src="{{ asset('plugins/select2/js/select2.full.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables 2.0/dataTables.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables 2.0/dataTables.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables 2.0/dataTables.fixedColumns.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-rowsgroup/dataTables.rowsGroup.js') }}"></script>
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

        function notif() {
            alert("Maaf, Fitur belum tersedia!");
        }
    </script>
    <script>
        function dataTableReload() {
            datatable.ajax.reload();
        }

        $(document).ready(function() {
            dataTableReload();
        })


        // When a single checkbox is changed
        $(document).on('change', '.row-checkbox', function() {
            updateCheckedCount();

            // Optional: sync "Select All"
            const total = $('.row-checkbox').length;
            const checked = $('.row-checkbox:checked').length;
            $('#selectAllCheckbox').prop('checked', total === checked);
        });

        // When "Select All" is toggled
        $('#selectAllCheckbox').on('change', function() {
            $('.row-checkbox').prop('checked', $(this).is(':checked'));
            updateCheckedCount();
        });


        // Toggle all checkboxes when "Select All" is clicked
        $('#selectAllCheckbox').on('change', function() {
            const isChecked = $(this).is(':checked');
            $('.row-checkbox').prop('checked', isChecked);
        });

        function updateCheckedCount() {
            let checked = $('.row-checkbox:checked').length;
            $('#checkedCount').text(checked);
        }

        function notif_print() {
            // Get all checked checkboxes
            const checkedBoxes = document.querySelectorAll('.row-checkbox:checked');

            if (checkedBoxes.length === 0) {
                Swal.fire({
                    icon: 'warning',
                    title: 'No Forms Selected',
                    text: 'Please select at least one form to print.',
                });
                return;
            }

            // Extract no_form and barcode from selected checkboxes
            const selectedItems = Array.from(checkedBoxes).map(box => ({
                id: box.dataset.id,
                no_form: box.dataset.no_form,
                barcode: box.dataset.barcode
            }));

            // Create HTML list with both no_form and barcode
            const listHtml = selectedItems.map(item => `
            <li>
                <strong>Form:</strong> ${item.no_form} (${item.barcode})<br />
            </li>
        `).join('');

            Swal.fire({
                title: 'Confirm Print',
                html: `
                <p>You are about to print the following forms:</p>
                <div style="max-height: 200px; overflow-y: auto; text-align: left;">
                    <ul>
                        ${listHtml}
                    </ul>
                </div>
            `,
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Yes, Print',
                cancelButtonText: 'Cancel'
            }).then(result => {
                if (result.isConfirmed) {
                    const ids = selectedItems.map(item => item.id);

                    // Call your custom print function here
                    print_sticker_fabric_relaxation(ids);

                    // Optional: you can still open a print page if needed
                    // const queryString = selectedItems
                    //     .map(item => `no_form[]=${encodeURIComponent(item.no_form)}&barcode[]=${encodeURIComponent(item.barcode)}`)
                    //     .join('&');
                    // window.open(`/print-multiple?${queryString}`, '_blank');
                }
            });
        }

        const printStickerFabricRelaxationUrl = "{{ route('print_sticker_fabric_relaxation') }}";

        function print_sticker_fabric_relaxation(items) {
            console.log("Printing stickers for:", items); // items is array of IDs

            // Build query string like: id_item[]=1&id_item[]=2&id_item[]=3
            const queryString = items.map(id => `id[]=${encodeURIComponent(id)}`).join('&');

            console.log("Printing stickers for:", queryString);

            const url = `${printStickerFabricRelaxationUrl}?${queryString}`;
            window.open(url, '_blank');
        }




        let datatable = $("#datatable").DataTable({
            ordering: false,
            responsive: true,
            processing: true,
            serverSide: false,
            paging: true,
            searching: true,
            scrollY: true,
            scrollX: true,
            scrollCollapse: false,
            fixedColumns: {
                leftColumns: 2
            },
            ajax: {
                url: '{{ route('qc_inspect_proses_fabric_relaxation') }}',
                data: function(d) {
                    d.dateFrom = $('#tgl-awal').val();
                    d.dateTo = $('#tgl-akhir').val();
                },
            },
            columns: [{
                    data: null,
                    orderable: false,
                    searchable: false,
                    render: function(data, type, row) {
                        return `
                    <a class="btn btn-outline-primary position-relative btn-sm" href="{{ route('input_fabric_relaxation_det') }}/` +
                            data.id + `" title="Detail" target="_blank">
                        Detail
                    </a>`;
                    }
                },
                {
                    data: null,
                    orderable: false,
                    searchable: false,
                    className: 'text-center',
                    render: function(data, type, row) {
                        // if ((data?.status_fix || '').toLowerCase() === 'done') {
                        return `
                <input
                    type="checkbox"
                    class="row-checkbox"
                    data-id="${data?.id || ''}"
                    data-no_form="${data?.no_form || ''}"
                    data-barcode="${data?.barcode || ''}"
                    style="border: 2px solid #000; width: 18px; height: 18px;"
                />
            `;
                        // } else {
                        //     return '';
                        // }
                    }
                },
                {
                    data: 'tgl_form_fix'
                },
                {
                    data: 'operator'
                },
                {
                    data: 'no_mesin'
                },
                {
                    data: 'no_form'
                },
                {
                    data: 'tgl_dok'
                },
                {
                    data: 'no_invoice'
                },
                {
                    data: 'buyer'
                },
                {
                    data: 'kpno'
                },
                {
                    data: 'styleno'
                },
                {
                    data: 'color'
                },
                {
                    data: 'no_lot'
                },
                {
                    data: 'id_item'
                },
                {
                    data: 'itemdesc'
                },
                {
                    data: 'barcode'
                },
                {
                    data: 'no_roll_buyer',
                    className: 'text-center'
                },
                {
                    data: 'durasi_relax',
                    className: 'text-center'
                },
                {
                    data: 'finish_date',
                    className: 'text-center'
                },
                {
                    data: 'finish_time',
                    className: 'text-center'
                },
                {
                    data: 'finish_relax_date',
                    className: 'text-center'
                },
                {
                    data: 'finish_relax_time',
                    className: 'text-center'
                },
                {
                    data: 'days_diff',
                    className: 'text-center'
                },
            ],
            initComplete: function() {
                this.api().columns().every(function() {
                    var column = this;
                    $('input', this.header()).on('keyup change clear', function() {
                        if (column.search() !== this.value) {
                            column.search(this.value).draw();
                        }
                    });
                });
            },

            rowCallback: function(row, data) {
                const statusFix = data.status_fix?.toLowerCase();

                // Remove any previous Bootstrap row color classes
                $(row).removeClass('table-success table-primary table-warning');

                // Apply row color based on status_fix
                if (statusFix === 'done') {
                    $(row).addClass('table-success'); // ✅ green
                } else {
                    $(row).addClass('table-primary'); // ✅ blue (default for not done)
                }
            }
        });
    </script>
@endsection
