@extends('layouts.index')

@section('custom-link')
    <!-- Select2 -->
    <link rel="stylesheet" href="{{ asset('plugins/select2/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}">
    <style>
        .line-map-calendar-wrapper {
            overflow: auto;
            max-height: 65vh;
            max-width: 100%;
            border-top: 1px solid #dee2e6;
            border-left: 1px solid #dee2e6;
        }

        .line-map-calendar-inner {
            display: flex;
            align-items: flex-start;
            width: max-content;
        }

        .line-map-fixed-table,
        .line-map-dates-table {
            border-collapse: separate;
            border-spacing: 0;
            margin-bottom: 0 !important;
        }

        .line-map-fixed-table th,
        .line-map-fixed-table td,
        .line-map-dates-table th,
        .line-map-dates-table td {
            white-space: nowrap;
            vertical-align: middle;
            height: 44px;
            border-right: 1px solid #dee2e6;
            border-bottom: 1px solid #dee2e6;
        }

        .line-map-fixed-table {
            width: auto !important;
            min-width: 160px;
            flex: 0 0 auto;
            position: sticky;
            left: 0;
            z-index: 3;
            background-color: #fff;
            box-shadow: 2px 0 4px -2px rgba(0, 0, 0, .15);
        }

        .line-map-fixed-table thead th,
        .line-map-dates-table thead th {
            position: sticky;
            top: 0;
            z-index: 2;
            background-color: #fff;
        }

        .line-map-fixed-table thead th {
            z-index: 4;
        }

        .line-map-dates-table th {
            text-align: center;
        }

        .line-map-dates-table td {
            text-align: center;
            padding: 4px 6px;
        }

        .badge-plan {
            display: inline-block;
            background-color: #6f42c1;
            color: #fff;
            font-size: 11px;
            font-weight: 600;
            padding: 3px 8px;
            border-radius: 10px;
            white-space: nowrap;
        }

        .badge-plan.badge-plan-rampup {
            background-color: #fd7e14;
        }
    </style>
@endsection

@section('content')
    <div class="modal fade" id="newLineMapModal" tabindex="-1" role="dialog" aria-labelledby="newLineMapModalLabel"
        data-bs-backdrop="static" aria-hidden="true">
        <form action="{{ route('store_ppic_line_map') }}" method="post" onsubmit="submitLineMapForm(this, event)"
            name="formLineMap" id="formLineMap">
            @csrf
            <input type="hidden" id="editid" name="editid" value="">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header bg-sb text-light">
                        <h3 class="modal-title fs-5" id="lineMapModalTitle">Tambah Line Map</h3>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">Line :</label>
                                    <select class="form-control select2bs4 form-control-sm" id="cboline" name="cboline">
                                        <option value="">- Pilih Line -</option>
                                        @foreach ($line as $row)
                                            <option value="{{ $row->username }}">{{ $row->FullName }}
                                                ({{ $row->username }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Style :</label>
                                    <input type="text" class="form-control form-control-sm" id="txtstyle"
                                        name="txtstyle" placeholder="Cnth: POLO ZIP SIDE SLIT" value=""
                                        autocomplete="off" style="text-transform: uppercase;"
                                        oninput="this.value = this.value.toUpperCase();">
                                </div>
                                <div class="form-group">
                                    <label class="form-label">SMV :</label>
                                    <input type="number" class="form-control form-control-sm" id="txtsmv" name="txtsmv"
                                        placeholder="Cnth: 12.5" value="" autocomplete="off">
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Efficiency :</label>
                                    <div class="input-group input-group-sm">
                                        <input type="number" class="form-control form-control-sm" id="txtefficiency"
                                            name="txtefficiency" placeholder="Cnth: 85" value="" autocomplete="off">
                                        <span class="input-group-text">%</span>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Order Qty :</label>
                                    <input type="text" class="form-control form-control-sm" id="txtorderqty"
                                        name="txtorderqty" placeholder="Cnth: 1.000" value="" autocomplete="off"
                                        inputmode="numeric"
                                        oninput="this.value = this.value.replace(/\D/g, '').replace(/\B(?=(\d{3})+(?!\d))/g, '.');">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">Buyer :</label>
                                    <input type="text" class="form-control form-control-sm" id="txtbuyer"
                                        name="txtbuyer" value="" autocomplete="off" style="text-transform: uppercase;"
                                        oninput="this.value = this.value.toUpperCase();">
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Man Power :</label>
                                    <input type="number" class="form-control form-control-sm" id="txtmanpower"
                                        name="txtmanpower" placeholder="Cnth: 10" value="" autocomplete="off">
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Working Minutes :</label>
                                    <input type="number" class="form-control form-control-sm" id="txtworkingminutes"
                                        name="txtworkingminutes" placeholder="Cnth: 480" value=""
                                        autocomplete="off">
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Start Day Calendar :</label>
                                    <input type="date" class="form-control form-control-sm" id="cbodate"
                                        name="cbodate" value="<?= date('Y-m-d') ?>">
                                </div>
                            </div>
                        </div>

                        <hr>

                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label class="form-label">Mins Available :</label>
                                    <input type="text" class="form-control form-control-sm bg-light"
                                        id="txtminsavailable" readonly tabindex="-1">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label class="form-label">Output / Day 100% :</label>
                                    <input type="text" class="form-control form-control-sm bg-light"
                                        id="txtoutputperday100" readonly tabindex="-1">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label class="form-label">Output / Day based Eff :</label>
                                    <input type="text" class="form-control form-control-sm bg-light"
                                        id="txtoutputperdayefficiency" readonly tabindex="-1">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label class="form-label">Total Days :</label>
                                    <input type="text" class="form-control form-control-sm bg-light" id="txttotaldays"
                                        readonly tabindex="-1">
                                </div>
                            </div>
                        </div>

                        <hr>

                        <div class="form-group">
                            <label class="form-label">Ramp Up Efficiency (opsional) :</label>
                            <div id="rampUpContainer"></div>
                            <button type="button" class="btn btn-outline-secondary btn-sm mt-1"
                                onclick="addRampUpRow()">
                                <i class="fas fa-plus"></i> Tambah Hari
                            </button>
                            <small class="form-text text-muted d-block mt-1">
                                Efisiensi bertahap untuk hari-hari awal (mis. operator masih adaptasi style
                                baru). Kosongkan jika tidak perlu, hari setelahnya otomatis pakai Efficiency
                                normal di atas.
                            </small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-danger" data-bs-dismiss="modal"><i
                                class="fas fa-times-circle"></i> Tutup</button>
                        <button type="submit" class="btn btn-outline-success"><i class="fas fa-check"></i>
                            Simpan</button>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <div class="card card-sb">
        <div class="card-header">
            <h5 class="card-title fw-bold mb-0"><i class="fas fa-map-marker-alt"></i> PPIC Line Map</h5>
        </div>
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-end flex-wrap gap-2 mb-3">
                <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal"
                    data-bs-target="#newLineMapModal" onclick="openNewLineMap()">
                    <i class="fas fa-plus"></i> New
                </button>

                <form action="{{ route('ppic_line_map') }}" method="get" class="d-flex align-items-end gap-2">
                    <div class="form-group mb-0">
                        <label class="form-label mb-0">Dari Tanggal :</label>
                        <input type="date" class="form-control form-control-sm" name="tgl_dari"
                            value="{{ $filterStart }}">
                    </div>
                    <div class="form-group mb-0">
                        <label class="form-label mb-0">Sampai Tanggal :</label>
                        <input type="date" class="form-control form-control-sm" name="tgl_sampai"
                            value="{{ $filterEnd }}">
                    </div>
                    <button type="submit" class="btn btn-outline-secondary btn-sm">
                        <i class="fas fa-filter"></i> Filter
                    </button>
                    <a href="{{ route('ppic_line_map') }}" class="btn btn-outline-danger btn-sm">
                        <i class="fas fa-times"></i> Reset
                    </a>
                </form>
            </div>

            <div class="line-map-calendar-wrapper">
                <div class="line-map-calendar-inner">
                    <table class="table table-sm line-map-fixed-table">
                        <thead>
                            <tr>
                                <th>Line</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($line as $ln)
                                <tr>
                                    <td>
                                        <div class="fw-bold">{{ $ln->FullName ?? $ln->username }}</div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td class="text-muted">Belum ada data</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>

                    <table class="table table-sm line-map-dates-table">
                        <thead>
                            <tr>
                                @foreach ($calendarDates as $date)
                                    <th>{{ date('d M', strtotime($date->tanggal)) }}</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($line as $ln)
                                <tr>
                                    @foreach ($calendarDates as $date)
                                        @php
                                            $activeEntry = ($lineMapByLine[$ln->username] ?? collect())
                                                ->first(fn($e) => $date->tanggal >= $e->tgl_start && $date->tanggal <= $e->tgl_end);
                                            $planQty = $activeEntry->daily_plan[$date->tanggal] ?? null;
                                            $effPct = $activeEntry->daily_efficiency[$date->tanggal] ?? null;
                                            $isRampUp = $activeEntry && in_array($date->tanggal, $activeEntry->ramp_up_dates ?? []);
                                        @endphp
                                        <td>
                                            @if ($activeEntry && $planQty !== null)
                                                <span class="badge-plan @if ($isRampUp) badge-plan-rampup @endif"
                                                    @if (!$isRampUp) style="background-color: {{ $activeEntry->style_color }};" @endif
                                                    @if ($effPct !== null) title="Efisiensi: {{ rtrim(rtrim(number_format($effPct, 1), '0'), '.') }}%" @endif>
                                                    {{ $activeEntry->style }} - Plan {{ number_format($planQty, 0, ',', '.') }} pcs
                                                </span>
                                            @endif
                                        </td>
                                    @endforeach
                                </tr>
                            @empty
                                <tr>
                                    @foreach ($calendarDates as $date)
                                        <td></td>
                                    @endforeach
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="card card-sb">
        <div class="card-header">
            <h5 class="card-title fw-bold mb-0"><i class="fas fa-list"></i> Daftar Line Map</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-sm align-middle">
                    <thead>
                        <tr>
                            <th>Line</th>
                            <th>Style</th>
                            <th>Buyer</th>
                            <th>SMV</th>
                            <th>Efficiency</th>
                            <th>Order Qty</th>
                            <th>Start Date</th>
                            <th>Total Days</th>
                            <th>Ramp Up</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($lineMap as $row)
                            <tr>
                                <td>{{ $lineNameByUsername[$row->line] ?? $row->line }}</td>
                                <td>{{ $row->style }}</td>
                                <td>{{ $row->buyer }}</td>
                                <td>{{ $row->smv }}</td>
                                <td>{{ $row->efficiency !== null ? number_format($row->efficiency * 100, 0) . '%' : '-' }}</td>
                                <td>{{ $row->qty_order !== null ? number_format($row->qty_order, 0, ',', '.') : '-' }}</td>
                                <td>{{ $row->tgl_start }}</td>
                                <td>{{ $row->tot_days_rounded }} hari</td>
                                <td>
                                    @if (count($row->ramp_up_efficiency))
                                        {{ count($row->ramp_up_efficiency) }} hari
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="text-nowrap">
                                    <button type="button" class="btn btn-outline-warning btn-sm"
                                        data-bs-toggle="modal" data-bs-target="#newLineMapModal"
                                        onclick='openEditLineMap(@json($row->edit_payload))'>
                                        <i class="fas fa-pen"></i> Edit
                                    </button>
                                    <button type="button" class="btn btn-outline-danger btn-sm"
                                        onclick="cancelLineMap({{ $row->id }})">
                                        <i class="fas fa-trash"></i> Hapus
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="text-center text-muted">Belum ada data</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

@section('custom-script')
    <script src="{{ asset('plugins/select2/js/select2.full.min.js') }}"></script>
    <script>
        $(document).on('select2:open', () => {
            document.querySelector('.select2-search__field').focus();
        });

        $('.select2bs4').select2({
            theme: 'bootstrap4',
            containerCssClass: 'form-control-sm rounded',
            dropdownParent: $('#newLineMapModal')
        });

        function addRampUpRow() {
            const dayNumber = $('#rampUpContainer .ramp-up-row').length + 1;
            const row = $(`
                <div class="input-group input-group-sm mb-1 ramp-up-row">
                    <span class="input-group-text ramp-up-day-label">Hari ${dayNumber}</span>
                    <input type="number" class="form-control" name="ramp_efficiency[]"
                        placeholder="Cnth: 50" min="0" max="100">
                    <span class="input-group-text">%</span>
                    <button type="button" class="btn btn-outline-danger" tabindex="-1">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            `);
            row.find('button').on('click', function() {
                row.remove();
                renumberRampUpRows();
                calculateLineMap();
            });
            row.find('input').on('input', calculateLineMap);
            $('#rampUpContainer').append(row);
        }

        function renumberRampUpRows() {
            $('#rampUpContainer .ramp-up-row').each(function(index) {
                $(this).find('.ramp-up-day-label').text('Hari ' + (index + 1));
            });
        }

        function getRampUpEfficiencies() {
            return $('#rampUpContainer input[name="ramp_efficiency[]"]').map(function() {
                return parseFloat($(this).val());
            }).get().filter(val => !isNaN(val));
        }

        function openNewLineMap() {
            $('#formLineMap').trigger('reset');
            $('#editid').val('');
            $('#lineMapModalTitle').text('Tambah Line Map');
            $('.select2bs4').val('').trigger('change');
            $('#rampUpContainer').empty();
            calculateLineMap();
        }

        function openEditLineMap(data) {
            $('#formLineMap').trigger('reset');
            $('#editid').val(data.id);
            $('#lineMapModalTitle').text('Edit Line Map');

            $('#cboline').val(data.line).trigger('change');
            $('#txtstyle').val(data.style);
            $('#txtsmv').val(data.smv);
            $('#txtefficiency').val(data.efficiency !== null ? Math.round(data.efficiency * 100) : '');
            $('#txtorderqty').val(data.qty_order !== null ?
                Number(data.qty_order).toLocaleString('id-ID').replace(/,/g, '.') : '');
            $('#txtbuyer').val(data.buyer);
            $('#txtmanpower').val(data.man_power);
            $('#txtworkingminutes').val(data.working_min);
            $('#cbodate').val(data.tgl_start);

            $('#rampUpContainer').empty();
            (data.ramp_up_efficiency || []).forEach(function(eff) {
                addRampUpRow();
                $('#rampUpContainer .ramp-up-row').last().find('input[name="ramp_efficiency[]"]')
                    .val(Math.round(eff * 100));
            });

            calculateLineMap();
        }

        function calculateLineMap() {
            const manPower = parseFloat($('#txtmanpower').val()) || 0;
            const workingMinutes = parseFloat($('#txtworkingminutes').val()) || 0;
            const smv = parseFloat($('#txtsmv').val()) || 0;
            const efficiency = parseFloat($('#txtefficiency').val()) || 0;
            const orderQty = parseFloat(($('#txtorderqty').val() || '').replace(/\./g, '')) || 0;
            const rampUp = getRampUpEfficiencies();

            const minsAvailable = manPower * workingMinutes;
            const outputPerDay100 = smv > 0 ? minsAvailable / smv : 0;
            const outputPerDayEfficiency = outputPerDay100 * (efficiency / 100);

            let totalDays = 0;
            if (outputPerDay100 > 0 && orderQty > 0) {
                let produced = 0;
                const maxDays = 3650;
                while (produced < orderQty && totalDays < maxDays) {
                    const eff = totalDays < rampUp.length ? (rampUp[totalDays] / 100) : (efficiency / 100);
                    const dailyOutput = outputPerDay100 * eff;
                    if (dailyOutput <= 0) break;
                    produced += dailyOutput;
                    totalDays++;
                }
            }

            $('#txtminsavailable').val(minsAvailable.toLocaleString('id-ID', {
                maximumFractionDigits: 0
            }));
            $('#txtoutputperday100').val(outputPerDay100.toLocaleString('id-ID', {
                maximumFractionDigits: 0
            }));
            $('#txtoutputperdayefficiency').val(outputPerDayEfficiency.toLocaleString('id-ID', {
                maximumFractionDigits: 0
            }));
            $('#txttotaldays').val(totalDays.toLocaleString('id-ID', {
                maximumFractionDigits: 0
            }));
        }

        $('#txtmanpower, #txtworkingminutes, #txtsmv, #txtefficiency, #txtorderqty').on('input', calculateLineMap);

        function cancelLineMap(id) {
            Swal.fire({
                icon: 'warning',
                title: 'Hapus Data?',
                text: 'Data ini tidak akan tampil lagi di tabel maupun kalender.',
                showCancelButton: true,
                confirmButtonText: 'Ya, Hapus',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (!result.isConfirmed) return;

                const url = @json(route('cancel_ppic_line_map', ':id')).replace(':id', id);

                fetch(url, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil',
                                text: data.message,
                                timer: 1500,
                                showConfirmButton: false
                            }).then(() => location.reload());
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Gagal',
                                text: data.message ?? 'Data gagal dihapus',
                                confirmButtonText: 'Tutup'
                            });
                        }
                    })
                    .catch(error => {
                        console.error('Hapus Line Map error:', error);
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal',
                            text: 'Terjadi kesalahan saat menghapus data',
                            confirmButtonText: 'Tutup'
                        });
                    });
            });
        }

        function submitLineMapForm(form, event) {
            event.preventDefault();

            const formData = new FormData(form);
            if (formData.has('txtorderqty')) {
                formData.set('txtorderqty', formData.get('txtorderqty').replace(/\./g, ''));
            }

            fetch(form.getAttribute('action'), {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        $('#newLineMapModal').modal('hide');
                        form.reset();
                        $('.select2bs4').val('').trigger('change');
                        $('#rampUpContainer').empty();
                        calculateLineMap();

                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil',
                            text: data.message,
                            timer: 2000,
                            showConfirmButton: false
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal',
                            text: data.message ?? 'Data gagal disimpan',
                            confirmButtonText: 'Tutup'
                        });
                    }
                })
                .catch(error => {
                    console.error('Simpan Line Map error:', error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal',
                        text: 'Terjadi kesalahan saat menyimpan data',
                        confirmButtonText: 'Tutup'
                    });
                });
        }
    </script>
@endsection
