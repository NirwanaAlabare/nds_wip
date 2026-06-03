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
            <h5 class="card-title fw-bold mb-0">
                <i class="fas fa-list"></i> List Stocker Reject
            </h5>
        </div>
        <div class="card-body">
            <div class="row g-3 mb-3">
                <div class="col-md-3">
                    <label class="form-label"><small><b>WS</b></small></label>
                    <select class="form-select select2bs4" name="ws" id="ws">
                        <option value=""></option>
                        @foreach ($orders as $order)
                            <option value="{{ $order->id }}">{{ $order->ws }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label"><small><b>Style</b></small></label>
                    <select class="form-select select2bs4" name="style" id="style">
                        <option value=""></option>
                        @foreach ($orders as $order)
                            <option value="{{ $order->id }}">{{ $order->style }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label"><small><b>Color</b></small></label>
                    <select class="form-select select2bs4" name="color" id="color">
                        <option value=""></option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label"><small><b>Size</b></small></label>
                    <select class="form-select select2bs4" multiple="multiple" name="size[]" id="size">
                    </select>
                </div>
                <div class="col-12 d-flex justify-content-end">
                    <button class="btn btn-primary btn-sm" onclick="tableReload()">
                        <i class="fa fa-search fa-xs"></i> Cari
                    </button>
                </div>
            </div>

            <div class="table-responsive">
                <table id="datatable" class="table table-bordered w-100">
                    <thead>
                        <tr>
                            <th class="text-center"><input type="checkbox" id="check-all"></th>
                            <th class="text-center">Tanggal</th>
                            <th class="text-center">Stocker</th>
                            <th class="text-center">No. WS</th>
                            <th class="text-center">Style</th>
                            <th class="text-center">Color</th>
                            <th class="text-center">Size</th>
                            <th class="text-center">Proses</th>
                            <th class="text-center">Qty Reject</th>
                            <th class="text-center">Generated Qty</th>
                            <th class="text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                    <tfoot>
                        <tr>
                            <th colspan="8" class="text-center">Total</th>
                            <th class="text-center"></th>
                            <th class="text-center"></th>
                            <th></th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

    {{-- Card: Selected Stocker Reject --}}
    <div class="card card-sb mt-3" id="card-selected" style="display:none;">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="card-title fw-bold mb-0 w-50">
                <i class="fas fa-check-square"></i>
                Stocker Reject Terpilih (<span id="selected-count">0</span>)
            </h5>
            <div class="d-flex justify-content-end w-50">
                <button class="btn btn-danger btn-sm float-end" onclick="clearAllSelected()">
                    <i class="fas fa-times"></i> Batalkan Semua
                </button>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered table-sm mb-0" id="selected-table">
                    <thead style="background-color: #f0f4ff;">
                        <tr>
                            <th class="text-center" style="width:80px;">Action</th>
                            <th class="text-center">Stocker</th>
                            <th class="text-center">No. WS</th>
                            <th class="text-center">Style</th>
                            <th class="text-center">Color</th>
                            <th class="text-center">Size</th>
                            <th class="text-center">Proses</th>
                            <th class="text-center">Qty Reject</th>
                            <th class="text-center">Generated Qty</th>
                            <th class="text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody id="selected-tbody"></tbody>
                    <tfoot>
                        <tr>
                            <th colspan="7" class="text-center">Total</th>
                            <th class="text-center fw-bold" id="selected-total-qty"></th>
                            <th class="text-center fw-bold" id="selected-total-generated"></th>
                            <th></th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
    <button class="btn btn-sm btn-success btn-block mb-3" id="btn-simpan" onclick="simpanBatch()" style="display:none;">
        <i class="fa fa-save"></i> SIMPAN
    </button>
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
        $('.select2').select2();
        $('.select2bs4').select2({ theme: 'bootstrap4' });

        // Sync WS ↔ Style (same value = act_costing.id)
        $("#ws").on("change", function () {
            if ($("#style").val() !== $(this).val()) {
                $("#style").val($(this).val()).trigger("change");
            }

            tableReload();
        });

        $("#style").on("change", function () {
            if ($("#ws").val() !== $(this).val()) {
                $("#ws").val($(this).val()).trigger("change");
            }
            updateColorList();

            tableReload();
        });

        $("#color").on("change", function () {
            updateSizeList();

            tableReload();
        });

        $("#size").on("change", function () {
            tableReload();
        });

        function updateColorList() {
            $('#color').html('<option value=""></option>').trigger('change');
            $('#size').html('').trigger('change');

            let actCostingId = $('#ws').val();
            if (!actCostingId) return;

            $.ajax({
                url: '{{ route("get-colors") }}',
                type: 'get',
                data: { act_costing_id: actCostingId },
                success: function (res) {
                    let select = document.getElementById('color');
                    select.innerHTML = '<option value="">ALL</option>';
                    (res || []).forEach(function (r) {
                        let o = document.createElement('option');
                        o.value = r.color;
                        o.text  = r.color;
                        select.appendChild(o);
                    });
                    $('#color').trigger('change.select2');
                    if (res && res.length > 0) {
                        $('#color').val(res[0].color).trigger('change');
                    }
                },
            });
        }

        function updateSizeList() {
            $('#size').html('').trigger('change');

            let actCostingId = $('#ws').val();
            let color        = $('#color').val();
            if (!actCostingId || !color) return;

            $.ajax({
                url: '{{ route("get-sizes") }}',
                type: 'get',
                data: { act_costing_id: actCostingId, color: color },
                success: function (res) {
                    let select = document.getElementById('size');
                    select.innerHTML = '';
                    (res || []).forEach(function (r) {
                        let o = document.createElement('option');
                        o.value = r.so_det_id;
                        o.text  = r.size;
                        select.appendChild(o);
                    });
                    // Select all sizes by default
                    let allIds = (res || []).map(r => r.so_det_id);
                    $('#size').val(allIds).trigger('change.select2');
                },
            });
        }

        let table;
        let selectedRows = {}; // { id: rowData }

        $(document).ready(function () {
            tableReload();

            $('#ws').val('').trigger('change');
            $('#color').val('').trigger('change');
            $('#size').val('').trigger('change');
        });

        // Select-all header checkbox
        $(document).on('change', '#check-all', function () {
            let checked = $(this).is(':checked');
            $('#datatable tbody .row-check').each(function () {
                $(this).prop('checked', checked);
                $(this).closest('tr').toggleClass('selected', checked);
                let rowData = table.row($(this).closest('tr')).data();
                if (rowData) {
                    if (checked) {
                        selectedRows[rowData.id] = rowData;
                    } else {
                        delete selectedRows[rowData.id];
                    }
                }
            });
            renderSelectedCard();
        });

        // Individual row checkbox
        $(document).on('change', '#datatable .row-check', function () {
            let checked   = $(this).is(':checked');
            let rowData   = table.row($(this).closest('tr')).data();
            let allChecked = $('#datatable tbody .row-check').length ===
                             $('#datatable tbody .row-check:checked').length;
            $('#check-all').prop('checked', allChecked);
            $(this).closest('tr').toggleClass('selected', checked);

            if (rowData) {
                if (checked) {
                    selectedRows[rowData.id] = rowData;
                } else {
                    delete selectedRows[rowData.id];
                }
            }
            renderSelectedCard();
        });

        function renderSelectedCard() {
            let rows   = Object.values(selectedRows);
            let count  = rows.length;
            let tbody  = $('#selected-tbody');

            $('#selected-count').text(count);

            if (count === 0) {
                $('#card-selected').hide();
                $('#btn-simpan').hide();
                return;
            }

            $('#card-selected').show();

            const proseBadge = (p) => {
                const map = { 'DC In': 'primary', 'Secondary Inhouse': 'warning', 'Secondary In': 'info' };
                return `<span class="badge badge-${map[p] ?? 'secondary'}">${p ?? '-'}</span>`;
            };
            const statusBadge = (b) => {
                if (b == null) return '-';
                return b > 0
                    ? `<span class="text-success fw-bold">AVAILABLE (${b})</span>`
                    : `<span class="text-danger fw-bold">EXHAUSTED</span>`;
            };

            let totalQty  = 0;
            let totalGen  = 0;
            let html      = '';

            rows.forEach(function (r) {
                totalQty += parseInt(r.qty_reject)           || 0;
                totalGen += parseInt(r.generated_qty_reject) || 0;
                let stocker = `<b>${r.id_qr_stocker ?? '-'}</b>`;
                if (r.id_qr_similar_stocker) stocker += `, <span class="text-muted">${r.id_qr_similar_stocker}</span>`;

                let detailUrl = '{{ url("stocker-reject/show") }}/' + r.id + '/' + encodeURIComponent(r.proses);

                html += `<tr>
                    <td class="text-center">
                        <div class="d-flex gap-1 justify-content-center">
                            <button class="btn btn-danger btn-xs py-0 px-1 btn-unselect" data-id="${r.id}" title="Hapus pilihan">
                                <i class="fas fa-times fa-xs"></i>
                            </button>
                            <a href="${detailUrl}" target="_blank" class="btn btn-sb btn-xs py-0 px-1" title="Go to Detail">
                                <i class="fas fa-external-link-alt fa-xs"></i>
                            </a>
                        </div>
                    </td>
                    <td class="text-nowrap">${stocker}</td>
                    <td class="text-nowrap">${r.act_costing_ws ?? '-'}</td>
                    <td class="text-nowrap">${r.style ?? '-'}</td>
                    <td class="text-nowrap">${r.color ?? '-'}</td>
                    <td class="text-center">${r.size ?? '-'}</td>
                    <td class="text-center">${proseBadge(r.proses)}</td>
                    <td class="text-center fw-bold">${r.qty_reject ?? 0}</td>
                    <td class="text-center fw-bold">${r.generated_qty_reject ?? 0}</td>
                    <td class="text-center">${statusBadge(r.qty_reject_balance)}</td>
                </tr>`;
            });

            tbody.html(html);
            $('#selected-total-qty').text(totalQty);
            $('#selected-total-generated').text(totalGen);

            // Tampilkan tombol SIMPAN hanya jika ada item
            $('#btn-simpan').toggle(count > 0);
        }

        function simpanBatch() {
            let items = Object.values(selectedRows);

            if (items.length === 0) {
                Swal.fire('Peringatan', 'Tidak ada stocker reject yang dipilih.', 'warning');
                return;
            }

            Swal.fire({
                title: 'Konfirmasi',
                html: `Simpan <b>${items.length}</b> stocker reject terpilih?`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Ya, Simpan',
                cancelButtonText: 'Batal',
            }).then(function (result) {
                if (!result.isConfirmed) return;

                Swal.fire({
                    title: 'Menyimpan...',
                    text: 'Harap tunggu.',
                    allowOutsideClick: false,
                    didOpen: () => Swal.showLoading(),
                });

                $.ajax({
                    url: '{{ route("store-stocker-reject-batch") }}',
                    type: 'POST',
                    contentType: 'application/json',
                    data: JSON.stringify({ items: items }),
                    headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                    success: function (res) {
                        Swal.close();

                        let successItems = (res.results || []).filter(r => r.status === 200);
                        let failItems    = (res.results || []).filter(r => r.status !== 200);

                        // Bangun HTML link untuk item sukses
                        function buildDetailLinks(items) {
                            return items.map(r => {
                                let url = '{{ url("stocker-reject/show") }}/' + r.show_id + '/' + encodeURIComponent(r.proses);
                                return `<a href="${url}" target="_blank" class="btn btn-sm btn-sb-secondary mt-1 d-block text-start">
                                    <i class="fas fa-external-link-alt fa-xs"></i> ${r.id_qr_stocker} — ${r.proses}
                                </a>`;
                            }).join('');
                        }

                        if (res.status === 200 || res.status === 206) {
                            let icon     = res.status === 200 ? 'success' : 'warning';
                            let failHtml = failItems.length > 0
                                ? `<hr><b>Gagal:</b><br>` + failItems.map(r => `${r.id_qr_stocker}: ${r.message}`).join('<br>')
                                : '';
                            let successHtml = successItems.length > 0
                                ? `<b>Berhasil disimpan:</b><br><br>` + buildDetailLinks(successItems)
                                : '';

                            Swal.fire({
                                title: res.message,
                                html: successHtml + failHtml,
                                icon: icon,
                                confirmButtonText: 'OK',
                            }).then(() => {
                                // Reload tabel utama, tapi card terpilih tetap tampil
                                // agar user bisa navigasi ke detail lewat button di card
                                tableReload();
                            });
                        } else {
                            let detail = failItems.map(r =>
                                `${r.id_qr_stocker}: ${r.message}`
                            ).join('<br>');
                            Swal.fire({ title: 'Gagal', html: detail || res.message, icon: 'error' });
                        }
                    },
                    error: function (xhr) {
                        Swal.fire('Error', 'Terjadi kesalahan server.', 'error');
                        console.error(xhr.responseText);
                    },
                });
            });
        }

        // Unselect dari card terpilih
        $(document).on('click', '.btn-unselect', function () {
            let id = $(this).data('id');
            delete selectedRows[id];
            // uncheck di tabel utama jika row masih visible
            $('#datatable tbody .row-check[value="' + id + '"]').prop('checked', false)
                .closest('tr').removeClass('selected');
            $('#check-all').prop('checked', false);
            renderSelectedCard();
        });

        function clearAllSelected() {
            selectedRows = {};
            $('#datatable tbody .row-check').prop('checked', false)
                .closest('tr').removeClass('selected');
            $('#check-all').prop('checked', false);
            renderSelectedCard();
        }

        function getSelectedIds() {
            return Object.keys(selectedRows);
        }

        function tableReload() {
            $('#check-all').prop('checked', false);

            if (table) {
                table.destroy();
            }

            table = $('#datatable').DataTable({
                ordering: false,
                processing: true,
                serverSide: true,
                ajax: {
                    url: '{{ route('list-stocker-reject') }}',
                    dataType: 'json',
                    data: function (d) {
                        d.ws    = $('#ws').val();
                        d.color = $('#color').val();
                        d.size  = $('#size').val();
                    },
                },
                columns: [
                    { data: 'id' },
                    { data: 'tanggal' },
                    { data: 'id_qr_stocker' },
                    { data: 'act_costing_ws' },
                    { data: 'style' },
                    { data: 'color' },
                    { data: 'size' },
                    { data: 'proses' },
                    { data: 'qty_reject' },
                    { data: 'generated_qty_reject' },
                    { data: 'qty_reject_balance' },
                ],
                columnDefs: [
                    {
                        targets: [0],
                        orderable: false,
                        searchable: false,
                        className: 'text-center',
                        render: (data, type, row) =>
                            `<input type="checkbox" class="row-check" value="${row.id}">`,
                    },
                    {
                        targets: [2],
                        render: (data, type, row) => {
                            let html = `<b>${data}</b>`;
                            if (row.id_qr_similar_stocker) {
                                html += `, <span class="text-muted">${row.id_qr_similar_stocker}</span>`;
                            }
                            return html;
                        },
                    },
                    {
                        targets: [7],
                        className: 'text-center',
                        render: (data) => {
                            const map = {
                                'DC In'             : 'primary',
                                'Secondary Inhouse' : 'warning',
                                'Secondary In'      : 'info',
                            };
                            const c = map[data] ?? 'secondary';
                            return `<span class="badge badge-${c}">${data ?? '-'}</span>`;
                        },
                    },
                    {
                        targets: [8, 9],
                        className: 'text-center fw-bold',
                    },
                    {
                        targets: [10],
                        className: 'text-center',
                        render: (data) => {
                            if (data == null) return '-';
                            if (data > 0) {
                                return `<span class="text-success fw-bold">AVAILABLE (${data})</span>`;
                            }
                            return `<span class="text-danger fw-bold">EXHAUSTED</span>`;
                        },
                    },
                    {
                        targets: '_all',
                        defaultContent: '-',
                        className: 'text-nowrap',
                    },
                ],
                footerCallback: function (row, data, start, end, display) {
                    let api = this.api();
                    [8, 9].forEach(function (col) {
                        let total = api.column(col, { search: 'applied' }).data()
                            .reduce((a, b) => (parseInt(a) || 0) + (parseInt(b) || 0), 0);
                        $(api.column(col).footer()).html(`<b>${total}</b>`);
                    });
                },
                drawCallback: function () {
                    // Restore checkbox state untuk rows yang sudah tersimpan di selectedRows
                    $('#datatable tbody .row-check').each(function () {
                        let id = $(this).val();
                        if (selectedRows[id]) {
                            $(this).prop('checked', true);
                            $(this).closest('tr').addClass('selected');
                        }
                    });
                    // Update state check-all
                    let total   = $('#datatable tbody .row-check').length;
                    let checked = $('#datatable tbody .row-check:checked').length;
                    $('#check-all').prop('checked', total > 0 && total === checked);
                },
            });

            // Header filter row
            $('#datatable thead tr:eq(1)').remove();
            $('#datatable thead tr:eq(0)').clone(true).appendTo('#datatable thead');
            $('#datatable thead tr:eq(1) th').each(function (i) {
                if (i === 0) {
                    $(this).html('');
                    return;
                }
                $(this).html('<input type="text" class="form-control form-control-sm" style="width:100%">');
                $('input', this).on('keyup change', function () {
                    if (table.column(i).search() !== this.value) {
                        table.column(i).search(this.value).draw();
                    }
                });
            });
        }
    </script>
@endsection
