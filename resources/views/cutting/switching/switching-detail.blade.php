@extends('layouts.index', ["containerFluid" => false])

@section('custom-link')
    <!-- DataTables -->
    <link rel="stylesheet" href="{{ asset('plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/datatables-responsive/css/responsive.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/datatables-buttons/css/buttons.bootstrap4.min.css') }}">

    <!-- Select2 -->
    <link rel="stylesheet" href="{{ asset('plugins/select2/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}">

    <style>
        /* Page Header */
        h5.fw-bold {
            border-bottom: 3px solid #0c5460;
            padding-bottom: 10px;
            margin-bottom: 20px;
            display: inline-block;
        }

        /* Card Styling */
        .card-sb {
            border: none;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            border-radius: 6px;
            transition: box-shadow 0.3s ease;
        }

        .card-sb:hover {
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.12);
        }

        .card-sb .card-header {
            background-color: #0c5460;
            color: white;
            border: none;
            padding: 15px 18px;
            border-radius: 6px 6px 0 0;
        }

        .card-sb .card-header .card-title {
            margin: 0;
            font-size: 1rem;
            font-weight: 600;
            letter-spacing: 0.5px;
        }

        .card-sb .card-body {
            padding: 20px;
        }

        /* Tabel styling */
        #datatable {
            border-collapse: separate;
            border-spacing: 0;
        }

        #datatable thead tr th {
            position: sticky;
            top: 0;
            z-index: 10;
            background-color: #0c5460;
            color: white;
            font-size: 0.85rem;
            letter-spacing: 0.5px;
            padding: 12px 8px !important;
            font-weight: 600;
        }

        #datatable tbody tr {
            transition: background-color 0.2s ease;
            border-bottom: 1px solid #e3e6f0;
        }

        #datatable tbody tr:hover {
            background-color: #f8f9fa !important;
            transform: scale(1.001);
        }

        #datatable tbody td {
            padding: 10px 8px !important;
            vertical-align: middle;
        }

        /* Filter row styling */
        #datatable thead tr:eq(1) input {
            font-size: 0.8rem;
            padding: 6px 4px !important;
            border-radius: 3px;
            border: 1px solid #ced4da;
        }

        #datatable thead tr:eq(1) input:focus {
            border-color: #0c5460;
            box-shadow: 0 0 0 0.2rem rgba(12, 84, 96, 0.25);
        }

        /* Pagination styling */
        .dataTables_paginate {
            padding-top: 15px !important;
        }

        .paginate_button {
            padding: 5px 10px !important;
            margin: 0 2px !important;
            border-radius: 4px !important;
            border: 1px solid #dee2e6 !important;
        }

        .paginate_button.current {
            background-color: #0c5460 !important;
            border-color: #0c5460 !important;
        }

        .paginate_button:hover {
            background-color: #0c5460 !important;
            color: white !important;
            border-color: #0c5460 !important;
        }

        /* Info text styling */
        .dataTables_info {
            padding-top: 15px !important;
            color: #6c757d;
            font-size: 0.9rem;
        }

        /* Ratio table styling */
        #datatable-ratio {
            border-collapse: separate;
            border-spacing: 0;
        }

        #datatable-ratio thead tr th {
            background-color: #0c5460;
            color: white;
            font-weight: 600;
            padding: 10px 8px !important;
            font-size: 0.85rem;
        }

        #datatable-ratio tbody tr:hover {
            background-color: #f8f9fa;
        }

        #datatable-ratio tbody td {
            padding: 8px;
            border-color: #e3e6f0;
        }

        /* Marker Details Table */
        .marker-details-table {
            border-collapse: collapse;
            width: 100%;
        }

        .marker-details-table thead th {
            background-color: #0c5460;
            color: white;
            padding: 10px;
            text-align: left;
            font-weight: 600;
            font-size: 0.85rem;
        }

        .marker-details-table tbody td {
            padding: 10px;
            border-bottom: 1px solid #dee2e6;
        }

        .marker-details-table tbody tr:hover {
            background-color: #f8f9fa;
        }

        .marker-details-table .form-control-sm {
            font-size: 0.8rem;
            padding: 4px 6px;
            border: 1px solid #ced4da;
        }

        .marker-details-table .form-control-sm:focus {
            border-color: #0c5460;
            box-shadow: 0 0 0 0.2rem rgba(12, 84, 96, 0.15);
        }

        .marker-details-table .select-ws,
        .marker-details-table .select-form,
        .marker-details-table .select-size {
            font-size: 0.8rem;
            padding: 4px 6px;
            border: 1px solid #ced4da;
        }

        .marker-details-table .select-ws:focus,
        .marker-details-table .select-form:focus,
        .marker-details-table .select-size:focus {
            border-color: #0c5460;
            box-shadow: 0 0 0 0.2rem rgba(12, 84, 96, 0.15);
        }

        .marker-select2-container .select2-container--bootstrap4 .select2-selection--single {
            border: 1px solid #ced4da;
            height: calc(1.5em + 0.5rem + 2px) !important;
        }

        .marker-select2-container .select2-container--bootstrap4.select2-container--open .select2-selection--single {
            border-color: #0c5460;
        }

        .marker-select2-container .select2-container--bootstrap4 .select2-selection--single .select2-selection__rendered {
            padding: 0.25rem 0.5rem;
            line-height: calc(1.5em + 0.5rem);
            font-size: 0.8rem;
        }

        .marker-select2-container .select2-container--bootstrap4 .select2-selection--single .select2-selection__placeholder {
            line-height: calc(1.5em + 0.5rem);
        }

        .highlight-ws {
            color: var(--sb-secondary-color);
            font-weight: 600;
        }

        .highlight-form {
            color: var(--sb-secondary-color);
            font-weight: 600;
        }

        .highlight-meja {
            color: var(--sb-secondary-color);
            font-weight: 600;
        }

        .highlight-color {
            color: var(--sb-secondary-color);
            font-weight: 600;
        }

        .highlight-panel {
            color: var(--sb-secondary-color);
            font-weight: 600;
        }
    </style>
@endsection

@section('content')
    @php
        $formDetails = $form->formCutInputDetails;
        $formMarker = $form ? $form->marker : null;
        $formMarkerDetails = $formMarker ? $formMarker->markerDetails : null;
        $formMeja = $form ? $form->alokasiMeja : null;
    @endphp
    <h5 class="fw-bold text-sb mb-4">
        <i class="fas fa-list" style="color: #0c5460;"></i> DETAIL CUTTING
    </h5>
    <div class="row mb-3">
        <div class="col-md-3">
            <div class="card card-sb shadow-sm h-100">
                <div class="card-header">
                    <h5 class="card-title">
                        <i class="fas fa-shopping-bag text-warning"></i> Order
                    </h5>
                </div>
                <div class="card-body">
                    <div class="mb-2">
                        <small class="text-muted d-block">Buyer</small>
                        <strong>{{ $formMarker ? $formMarker->buyer : '-' }}</strong>
                    </div>
                    <div class="mb-2">
                        <small class="text-muted d-block">No. WS</small>
                        <strong><span class="highlight-ws">{{ $formMarker ? $formMarker->act_costing_ws : '-' }}</span></strong>
                    </div>
                    <div class="mb-0">
                        <small class="text-muted d-block">Style</small>
                        <strong>{{ $formMarker ? $formMarker->style : '-' }}</strong>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card card-sb shadow-sm h-100">
                <div class="card-header">
                    <h5 class="card-title">
                        <i class="fas fa-clipboard-list text-warning"></i> Form Info
                    </h5>
                </div>
                <div class="card-body">
                    <div class="mb-2">
                        <small class="text-muted d-block">No. Form</small>
                        <strong><a href="{{ route('show-stocker')."/".$form->id }}" class="highlight-form" target="_blank">{{ $form ? ($form->no_form ?? '-') : '-' }}</a></strong>
                    </div>
                    <div class="mb-2">
                        <small class="text-muted d-block">Tanggal</small>
                        <strong>{{ $form ? ($form->tgl_form_cut ? \Carbon\Carbon::parse($form->tgl_form_cut)->format('d M Y') : '-') : '-' }}</strong>
                    </div>
                    <div class="mb-0">
                        <small class="text-muted d-block">Meja</small>
                        <strong><span class="highlight-meja">{{ $formMeja ? (strtoupper($formMeja->name) ?? '-') : '-' }}</span></strong>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card card-sb shadow-sm h-100">
                <div class="card-header">
                    <h5 class="card-title">
                        <i class="fas fa-marker text-warning"></i> Marker Info
                    </h5>
                </div>
                <div class="card-body">
                    <div class="mb-2">
                        <small class="text-muted d-block">No. Marker</small>
                        <strong>{{ $formMarker ? ($formMarker->kode ?? '-') : '-' }}</strong>
                    </div>
                    <div class="mb-2">
                        <small class="text-muted d-block">Color</small>
                        <strong><span class="highlight-color">{{ $formMarker ? ($formMarker->color ?? '-') : '-' }}</span></strong>
                    </div>
                    <div class="mb-0">
                        <small class="text-muted d-block">Panel</small>
                        <strong><span class="highlight-panel">{{ $formMarker ? ($formMarker->panel ?? '-') : '-' }}</span></strong>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card card-sb shadow-sm h-100">
                <div class="card-header">
                    <h5 class="card-title">
                        <i class="fas fa-cogs text-warning"></i> Cutting Details
                    </h5>
                </div>
                <div class="card-body">
                    <div class="mb-2">
                        <small class="text-muted d-block">Qty Ply</small>
                        <strong>{{ $formDetails ? ($formDetails->sum('lembar_gelaran') ?? '-') : '-' }} Ply</strong>
                    </div>
                    <div class="mb-2">
                        <small class="text-muted d-block">Tipe Form</small>
                        <strong>{{ $form ? ($form->tipe_form_cut ?? '-') : '-' }}</strong>
                    </div>
                    <div class="mb-0">
                        <small class="text-muted d-block">Notes</small>
                        <strong>{{ $form ? ($form->notes ?? '-') : '-' }}</strong>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <input type="hidden" id="currentActCostingId" value="{{ $formMarker ? $formMarker->act_costing_ws : '-' }}">
            <input type="hidden" id="currentForm" value="{{ $form ? ($form->no_form ?? '-') : '-' }}">
            @php
                $formDetailsGroup = $formDetails ? $formDetails->groupBy("group_roll") : collect();

                // Group roll yang dimiliki form ini sendiri
                $groupRollList = $formDetailsGroup->keys()->all();

                // Output kiriman dari form lain : group roll-nya tidak ada di spreading form ini
                $additionalOutputGroups = $form ? $form->formCutInputDetailOutputs()->where("qty_output_aktual", ">", 0)->get()->whereNotIn("group_roll", $groupRollList)->groupBy("group_roll") : collect();
            @endphp
            @if (count($formDetailsGroup) > 0)
                @foreach ($formDetailsGroup as $key => $group)
                    @php
                        $groupItem = $group->first();
                        $groupRoll = $groupItem->group_roll;
                        $groupTotal = $group->sum("lembar_gelaran");

                        $formDetailOutputs = $form->formCutInputDetailOutputs()->where('group_roll', $groupRoll)->where("qty_output_aktual", ">", 0)->orderBy("group_roll")->get();
                    @endphp
                    @include('cutting.switching.switching-detail-group', ["groupRoll" => $groupRoll, "formDetailOutputs" => $formDetailOutputs, "isAdditional" => false])
                @endforeach
            @endif

            {{-- Group Tambahan --}}
            @if (count($additionalOutputGroups) > 0)
                @foreach ($additionalOutputGroups as $groupRoll => $formDetailOutputs)
                    @include('cutting.switching.switching-detail-group', ["groupRoll" => $groupRoll, "formDetailOutputs" => $formDetailOutputs, "isAdditional" => true])
                @endforeach
            @endif

            @if (count($formDetailsGroup) < 1 && count($additionalOutputGroups) < 1)
                <p class="text-center text-muted mb-0">Data output aktual tidak ditemukan</p>
            @else
                <div class="d-flex justify-content-end mt-4">
                    <button type="button" class="btn btn-primary" onclick="sendAllOutput()">
                        <i class="fa-solid fa-paper-plane"></i> Kirim Semua
                    </button>
                </div>
            @endif
        </div>
    </div>

    <div class="card">
        <div class="card-header bg-sb text-white">
            <h5 class="card-title mb-0"><i class="fas fa-history"></i> Riwayat Transfer Output</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover table-sm w-100">
                    <thead class="thead-light">
                        <tr>
                            <th class="text-nowrap text-center">Waktu</th>
                            <th class="text-nowrap text-center">Jenis</th>
                            <th class="text-nowrap text-center">Form</th>
                            <th class="text-nowrap text-center">Size
                            <th class="text-nowrap text-center">Qty</th>
                            <th class="text-nowrap text-center">User</th>
                            <th class="text-nowrap text-center">Status</th>
                            <th class="text-nowrap text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if (isset($logs) && count($logs) > 0)
                            @foreach ($logs as $log)
                                <tr>
                                    <td class="text-nowrap text-center">{{ $log->created_at }}</td>
                                    <td class="text-nowrap text-center"><span class="badge rounded-pill {{ ($log->no_form_tujuan == $form->no_form ? "bg-success" : "bg-warning" )}}">{{ $log->no_form_tujuan == $form->no_form ? 'MASUK' : 'KELUAR'  }}</span></td>
                                    <td class="text-nowrap text-center text-sb fw-bold"><span class="text-info">{{ ($log->no_form_asal ?? '-') }}</span> <i class="fas fa-arrow-right mx-1"></i> <span class="highlight-form">{{ ($log->no_form_tujuan ?? '-') }}</span></td>
                                    <td class="text-center text-center text-sb fw-bold"><span class="text-info">{{ $log->size_asal }}</span> <i class="fas fa-arrow-right mx-1"></i> <span class="highlight-form">{{ $log->size_tujuan }}</span></td>
                                    <td class="text-center fw-bold">{{ number_format($log->qty_transfer, 0) }}</td>
                                    <td class="text-center">{{ $log->created_by }}</td>
                                    <td class="text-center">
                                        <span class="badge {{ ($log->is_active === null || $log->is_active > 0) ? 'bg-success' : 'bg-danger' }}">{{ ($log->is_active === null || $log->is_active > 0) ? 'AKTIF' : 'DIBATALKAN' }}</span>
                                    </td>
                                    <td class="text-center">
                                        <button type="button" class="btn btn-danger btn-sm" onclick="deleteLog('{{ $log->id }}')" {{ ($log->is_active === null || $log->is_active > 0) ? '' : 'disabled' }}><i class="fa fa-trash"></i></button>
                                    </td>
                                </tr>
                            @endforeach
                        @else
                            <tr>
                                <td colspan="8" class="text-center text-muted">Belum ada riwayat transfer untuk form ini.</td>
                            </tr>
                        @endif
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
        $('.select2').select2()
        $('.select2bs4').select2({
            theme: 'bootstrap4',
            dropdownParent: $("#editMejaModal")
        })

        // Initialize Select2 for marker details table
        $('.select-ws, .select-form, .select-size').select2({
            theme: 'bootstrap4',
            width: '100%',
            containerCssClass: 'form-control-sm rounded'
        })
    </script>

    <script>
        document.addEventListener("DOMContentLoaded", () => {
            // let oneWeeksBefore = new Date(new Date().setDate(new Date().getDate() - 7));
            // let oneWeeksBeforeDate = ("0" + oneWeeksBefore.getDate()).slice(-2);
            // let oneWeeksBeforeMonth = ("0" + (oneWeeksBefore.getMonth() + 1)).slice(-2);
            // let oneWeeksBeforeYear = oneWeeksBefore.getFullYear();
            // let oneWeeksBeforeFull = oneWeeksBeforeYear + '-' + oneWeeksBeforeMonth + '-' + oneWeeksBeforeDate;

            // $("#tgl-awal").val(oneWeeksBeforeFull).trigger("change");

            window.addEventListener("focus", () => {
                $('#datatable').DataTable().ajax.reload(null, false);
            });

            clearInput();
        });

        function clearInput() {
            $(".switch-internal").prop('checked', false);
            $(".select-ws").val('').trigger('change');
            $(".select-form").val('').trigger('change');
            $(".select-size").val('').trigger('change');
            $(".qty").val(null).trigger('change');
        }

        $('#datatable thead tr').clone(true).appendTo('#datatable thead');
        $('#datatable thead tr:eq(1) th').each(function(i) {
            if (i != 0 && i != 8 && i != 9 && i != 12) {
                var title = $(this).text();
                $(this).html('<input type="text" class="form-control form-control-sm"/>');

                $('input', this).on('keyup change', function() {
                    if (datatable.column(i).search() !== this.value) {
                        datatable
                            .column(i)
                            .search(this.value)
                            .draw();
                    }
                });
            } else {
                $(this).empty();
            }
        });

       async function setInternal(element) {
            let id = $(element).data('id');
            let isInternal = $(element).is(':checked');

            let $wsSelect = $('#ws-' + id);
            let $formSelect = $('#form-' + id);

            if (!isInternal) {
                clearRow(id);
                return;
            }

            let actCostingId = $(element).data('act-costing-id');
            let ownFormId = $(element).data('form-id');
            let ownMarkerDetailId = $(element).data('marker-detail-id');

            if (!actCostingId || !ownFormId || !ownMarkerDetailId) {
                return;
            }

            try {
                // Tandai bahwa dropdown sedang di-set secara programmatic (internal)
                // agar event listener 'change' biasa tidak menjalankan AJAX dua kali
                $wsSelect.data('is-internal-loading', true);
                $wsSelect.val(actCostingId).trigger("change");

                // Ambil data form secara async
                await getFormList($wsSelect);

                // Set form
                $formSelect.data('is-internal-loading', true);
                $formSelect.val(ownFormId).trigger("change");

                // Ambil data size secara async
                await getFormSizeList($formSelect);

            } catch (error) {
                console.error("Terjadi kesalahan:", error);
            }
        }

        function clearRow(id) {
            $('#ws-' + id).val('').trigger('change');
            $('#form-' + id).empty().append($('<option>', { value: '', text: '-- Pilih No. Form --' })).trigger('change');
            $('#size-' + id).empty().append($('<option>', { value: '', text: '-- Pilih Size --' })).trigger('change');
            $('#qty-' + id).val(null).trigger('change');
        }

        function getFormList(element) {
            return new Promise((resolve, reject) => {
                let $element = $(element);

                // Cek apakah sedang dalam proses internal loading
                if ($element.data('is-internal-loading')) {
                    $element.data('is-internal-loading', false); // Reset kembali
                    resolve(); // Langsung selesaikan promise agar await tidak macet
                    return;
                }

                let id = $(element).data('id');
                let actCostingId = $(element).val();

                let $formSelect = $('#form-' + id);
                let $sizeSelect = $('#size-' + id);

                $formSelect.empty().append($('<option>', { value: '', text: '-- Pilih No. Form --' })).trigger('change');
                $sizeSelect.empty().append($('<option>', { value: '', text: '-- Pilih Size --' })).trigger('change');

                if (!actCostingId) {
                    resolve();
                    return;
                }

                showLoading();
                $.ajax({
                    type: "get",
                    url: "{{ route('get-form-list-cutting-switching') }}",
                    data: { act_costing_id: actCostingId },
                    success: function (response) {
                        $.each(response, function (i, form) {
                            $formSelect.append($('<option>', { value: form.id, text: form.no_form }));
                        });
                        $formSelect.trigger('change');
                        resolve(response); // Berhasil
                    },
                    error: function (xhr) {
                        reject(xhr); // Gagal
                    },
                    complete: function () {
                        hideLoading();
                    }
                });
            });
        }

        function getFormSizeList(element) {
            return new Promise((resolve, reject) => {
                let $element = $(element);

                // Cek apakah sedang dalam proses internal loading
                if ($element.data('is-internal-loading')) {
                    $element.data('is-internal-loading', false); // Reset kembali
                    resolve(); // Langsung selesaikan promise agar await tidak macet
                    return;
                }

                let id = $(element).data('id');
                let formCutId = $(element).val();

                let $sizeSelect = $('#size-' + id);

                $sizeSelect.empty().append($('<option>', { value: '', text: '-- Pilih Size --' })).trigger('change');

                if (!formCutId) {
                    resolve();
                    return;
                }

                showLoading();
                $.ajax({
                    type: "get",
                    url: "{{ route('get-form-size-list-cutting-switching') }}",
                    data: { form_cut_id: formCutId },
                    success: function (response) {
                        console.log(response);
                        $.each(response, function (i, size) {
                            $sizeSelect.append($('<option>', { value: size.id, text: size.size }));
                        });
                        $sizeSelect.trigger('change');
                        resolve(response); // Berhasil
                    },
                    error: function (xhr) {
                        reject(xhr); // Gagal
                    },
                    complete: function () {
                        hideLoading();
                    }
                });
            });
        }

        const currentFormId = {{ $form->id ?? 'null' }};

        function sendOutput(button) {
            let id = $(button).data('id');
            let fromMarkerDetailId = $(button).data('marker-detail-id');
            let groupRoll = $(button).data('group-roll');

            let actCostingId = $('#ws-' + id).val();
            let formCutId = $('#form-' + id).val();
            let markerDetailId = $('#size-' + id).val();
            let qty = $('#qty-' + id).val();

            if (!actCostingId || !formCutId || !markerDetailId || !qty || qty <= 0) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Perhatian',
                    text: 'Mohon lengkapi pilihan WS, No. Form, Size, dan Qty terlebih dahulu.',
                    showCancelButton: false,
                    showConfirmButton: true,
                    confirmButtonText: 'Oke',
                });
                return;
            }

            showLoading();
            $.ajax({
                type: "post",
                url: "{{ route('store-cutting-switching') }}",
                data: {
                    id: id,
                    from_form_cut_id: currentFormId,
                    from_marker_detail_id: fromMarkerDetailId,
                    form_cut_id: formCutId,
                    marker_detail_id: markerDetailId,
                    group_roll: groupRoll,
                    qty: qty
                },
                success: function (response) {
                    if (response.status == 400) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal',
                            text: response.message,
                            showCancelButton: false,
                            showConfirmButton: true,
                            confirmButtonText: 'Oke',
                        });
                    } else {
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil',
                            text: response.message,
                            showCancelButton: false,
                            showConfirmButton: true,
                            confirmButtonText: 'Oke',
                        }).then(() => {
                            location.reload();
                        });
                    }
                },
                error: function (jqXHR) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal',
                        text: jqXHR.responseJSON && jqXHR.responseJSON.message ? jqXHR.responseJSON.message : 'Terjadi kesalahan.',
                        showCancelButton: false,
                        showConfirmButton: true,
                        confirmButtonText: 'Oke',
                    });
                },
                complete: function () {
                    hideLoading();
                }
            });
        }

        function sendAllOutput() {
            let transfers = [];

            // Kumpulkan semua baris dari seluruh group yang ada di halaman
            $('button[onclick="sendOutput(this)"]').each(function() {
                let sendButton = $(this);

                let id = sendButton.data('id');
                let fromMarkerDetailId = sendButton.data('marker-detail-id');
                let groupRoll = sendButton.data('group-roll');

                let actCostingId = $('#ws-' + id).val();
                let formCutId = $('#form-' + id).val();
                let markerDetailId = $('#size-' + id).val();
                let qty = $('#qty-' + id).val();

                if (actCostingId && formCutId && markerDetailId && qty && qty > 0) {
                    transfers.push({
                        id: id,
                        from_form_cut_id: currentFormId,
                        from_marker_detail_id: fromMarkerDetailId,
                        form_cut_id: formCutId,
                        marker_detail_id: markerDetailId,
                        group_roll: groupRoll,
                        qty: qty
                    });
                }
            });

            if (transfers.length === 0) {
                Swal.fire('Perhatian', 'Tidak ada data yang valid untuk dikirim. Mohon lengkapi pilihan WS, No. Form, Size, dan Qty.', 'warning');
                return;
            }

            Swal.fire({
                title: 'Kirim Semua Output?',
                text: `${transfers.length} baris akan dikirim dari seluruh group.`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Ya, kirim!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (!result.isConfirmed) {
                    return;
                }

                showLoading();
                $.ajax({
                    type: "post",
                    url: "{{ route('mass-store-cutting-switching') }}",
                    data: {
                        transfers: transfers
                    },
                    success: function (response) {
                        if (response.status == 400) {
                            Swal.fire('Gagal', response.message, 'error').then(() => {
                                // callback
                            });
                        } else {
                            Swal.fire('Berhasil', response.message, 'success').then(() => {
                                location.reload();
                            });
                        }

                    },
                    error: function (jqXHR) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal',
                            text: jqXHR.responseJSON && jqXHR.responseJSON.message ? jqXHR.responseJSON.message : 'Terjadi kesalahan saat transfer massal.',
                        });
                    },
                    complete: function () {
                        hideLoading();
                    }
                });
            });
        }

        function deleteLog(id) {
            Swal.fire({
                title: 'Apakah Anda yakin?',
                text: "Tindakan ini akan mengembalikan kuantitas yang ditransfer dan menghapus catatan riwayat ini. Anda tidak akan dapat mengembalikannya!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Ya, hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    showLoading();
                    $.ajax({
                        type: "DELETE",
                        url: `{{ url('cutting/switching/log') }}/${id}`,
                        success: function (response) {
                            Swal.fire(
                                'Dihapus!',
                                response.message,
                                'success'
                            ).then(() => {
                                location.reload();
                            });
                        },
                        error: function (jqXHR) {
                            Swal.fire({
                                icon: 'error',
                                title: 'Gagal',
                                text: jqXHR.responseJSON && jqXHR.responseJSON.message ? jqXHR.responseJSON.message : 'Terjadi kesalahan saat menghapus log.',
                            });
                        },
                        complete: function() {
                            hideLoading();
                        }
                    });
                }
            });
        }

        function deleteLog(id) {
            Swal.fire({
                title: 'Apakah Anda yakin?',
                text: "Tindakan ini akan mengembalikan kuantitas yang ditransfer dan menghapus catatan riwayat ini. Anda tidak akan dapat mengembalikannya!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Ya, hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    showLoading();
                    $.ajax({
                        type: "DELETE",
                    url: `{{ route('destroy-cutting-switching') }}/${id}`,
                        success: function (response) {
                            if (response.status == 200) {
                                Swal.fire(
                                    'Dihapus!',
                                    response.message,
                                    'success'
                                ).then(() => {
                                    location.reload();
                                });
                            } else {
                                Swal.fire(
                                    'Gagal!',
                                    response.message,
                                    'error'
                                ).then(() => {
                                    // location.reload();
                                });
                            }
                        },
                        error: function (jqXHR) {
                            Swal.fire({
                                icon: 'error',
                                title: 'Gagal',
                                text: jqXHR.responseJSON && jqXHR.responseJSON.message ? jqXHR.responseJSON.message : 'Terjadi kesalahan saat menghapus log.',
                            });
                        },
                        complete: function() {
                            hideLoading();
                        }
                    });
                }
            });
        }
    </script>
@endsection
