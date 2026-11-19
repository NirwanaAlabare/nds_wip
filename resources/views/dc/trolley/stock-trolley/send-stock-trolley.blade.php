@extends('layouts.index', ["containerFluid" => false])

@section('custom-link')
    <!-- DataTables -->
    <link rel="stylesheet" href="{{ asset('plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/datatables-responsive/css/responsive.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/datatables-buttons/css/buttons.bootstrap4.min.css') }}">
    <!-- Select2 -->
    <link rel="stylesheet" href="{{ asset('plugins/select2/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}">

    @include('dc.trolley.stock-trolley.partials.stock-table-style')
    @include('dc.trolley.stock-trolley.partials.page-guide-style')

    <style>
        /* Baris bisa diklik untuk memilih */
        #datatable-trolley-stock tbody tr {
            cursor: pointer;
            user-select: none;
            transition: background-color .15s ease;
        }

        /* Warna baris terpilih mengikuti data color (diset lewat JS : --row-bg & --row-accent) */
        #datatable-trolley-stock tbody tr.row-checked > td {
            background-color: var(--row-bg, #d1e7ff) !important;
        }

        #datatable-trolley-stock tbody tr.row-checked .check-col input[type="checkbox"] {
            accent-color: var(--row-accent, #0d6efd);
        }

        /* Kolom checkbox */
        #datatable-trolley-stock .check-col {
            width: 1%;
            text-align: center;
            border-left: 4px solid transparent;
        }

        #datatable-trolley-stock thead .check-col {
            vertical-align: middle;
        }

        /* Sejajar dengan baris teks pertama, sama seperti kolom lain */
        #datatable-trolley-stock tbody .check-col {
            vertical-align: top;
            padding-top: .95rem;
        }

        /* AdminLTE / Bootstrap 4 membuat .form-check-input position: absolute */
        #datatable-trolley-stock .check-col input[type="checkbox"] {
            position: static;
            float: none;
            margin: 0;
            width: 1.1rem;
            height: 1.1rem;
            vertical-align: middle;
            cursor: pointer;
            accent-color: #0d6efd;
        }

        /* Penanda di sisi kiri baris terpilih */
        #datatable-trolley-stock tbody tr.row-checked > td.check-col {
            border-left-color: var(--row-accent, #0d6efd);
        }

        /* Ringkasan pilihan */
        .selected-summary {
            display: inline-flex;
            align-items: center;
            gap: .35rem;
            padding: .25rem .75rem;
            border-radius: 999px;
            background-color: #f1f3f5;
            color: #6c757d;
            font-size: .875rem;
        }

        .selected-summary.has-selected {
            background-color: #d1e7ff;
            color: #084298;
            font-weight: 600;
        }

        .selected-summary .selected-divider {
            opacity: .5;
        }

        /* Action bar bawah : style dasar ada di partial 'stock-table-style' */

        /* No. BON & Kirim selalu di kanan */
        .stock-action-send {
            margin-left: auto;
        }

        .stock-action-send:empty {
            display: none;
        }

        .stock-action-bar .selected-summary {
            padding: .45rem 1rem;
            font-size: 1rem;
        }

        .stock-action-bar .selected-summary #selected-row-count-1,
        .stock-action-bar .selected-summary #selected-qty-sum {
            font-size: 1.15rem;
            font-weight: 700;
        }

        /* No. BON & Kirim */
        .send-controls {
            display: flex;
            align-items: center;
            gap: .5rem;
        }

        .send-controls #no_bon {
            width: 180px;
        }

        /* Versi lebih besar saat berada di action bar */
        .stock-action-bar .send-controls #no_bon {
            width: 220px;
            height: calc(1.5em + .75rem + 2px);
            padding: .375rem .75rem;
            font-size: 1rem;
        }

        .stock-action-bar .send-controls .btn {
            padding: .45rem 1.25rem;
            font-size: 1rem;
            font-weight: 600;
        }


        /*
            Urutan kolom di DOM (nth-child) :
            1 ID (hidden) | 2 Check | 3 No. Stocker | 4 WS / Style / Color | 5 Size / Qty
            6 No. Cut | 7 Panel | 8 Part | 9 Range | 10 By
            (kolom No. WS, Style, Color, Size, Qty mentah disembunyikan DataTables, tidak ada di DOM)
        */

        /* Tablet */
        @media (max-width: 991.98px) {
            #datatable-trolley-stock tbody .check-col {
                padding-top: .6rem;
            }

            /* Sembunyikan Panel, Range, By */
            #datatable-trolley-stock tr > :nth-child(7),
            #datatable-trolley-stock tr > :nth-child(9),
            #datatable-trolley-stock tr > :nth-child(10) {
                display: none;
            }
        }

        /* HP */
        @media (max-width: 575.98px) {
            /* Sembunyikan juga Part */
            #datatable-trolley-stock tr > :nth-child(8) {
                display: none;
            }

            /* Toolbar memenuhi lebar layar */
            .send-toolbar > div {
                width: 100%;
            }

            .send-toolbar #no_bon {
                flex: 1;
                width: auto !important;
            }

            /* Action bar : No. BON melebar */
            .stock-action-send,
            .send-controls {
                width: 100%;
            }

            .stock-action-send {
                margin-left: 0;
            }

            .send-controls #no_bon,
            .stock-action-bar .send-controls #no_bon {
                flex: 1;
                width: auto;
            }

            .send-toolbar .hint-click {
                display: none;
            }
        }
    </style>
@endsection

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="text-sb fw-bold">{{ $trolley->nama_trolley }}</h5>
        <div class="d-flex justify-content-end gap-1 st-head-r">
            <button type="button" class="btn btn-outline-primary btn-sm" id="pg-guide-btn"><i class="fa fa-book-open"></i> Panduan</button>
            <a href="{{ route('send-trolley-stock-old')."/".$trolley->id }}" class="btn btn-outline-secondary btn-sm" id="pg-old-view-btn" title="Buka tampilan lama"><i class="fas fa-history"></i> Tampilan lama</a>
            <a href="{{ route('stock-trolley') }}" class="btn btn-primary btn-sm">
                <i class="fas fa-reply"></i> Kembali
            </a>
            <a href="{{ route('allocate-this-trolley')."/".$trolley->id }}" class="btn btn-sb-secondary btn-sm">
                Alokasi ke Stok Trolley <i class="fas fa-share"></i>
            </a>
        </div>
    </div>
    <form action="{{ route('store-allocate-this-trolley') }}" method="post" onsubmit="submitForm(this, event)">
        <div class="d-flex justify-content-center">
            <div class="form-check form-switch st-dest">
                <input class="form-check-input" type="checkbox" role="switch" id="switch-destination" checked onchange="switchDestination(this)">
                <label class="form-check-label" id="to-line">Line</label>
                <label class="form-check-label d-none" id="to-trolley">Trolley</label>
            </div>
        </div>
        <input type="hidden" name="trolley_id" id="trolley_id" value="{{ $trolley->id }}">
        <div class="card card-sb" id="to-line-card">
            <div class="card-header">
                <h5 class="card-title fw-bold">Kirim ke Line</h5>
                <div class="card-tools">
                    <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i>
                    </button>
                </div>
            </div>
            <div class="card-body" style="display: block" id="scan-line">
                <div id="line-reader" onclick="clearLineScan()"></div>
                <div class="mb-3">
                    <label class="form-label">Line</label>
                    <div class="input-group w-100">
                        <select class="form-control form-control-sm select2bs4" name="line_id" id="line_id">
                            @foreach ($lines as $line)
                                <option value="{{ $line->line_id }}" {{ $trolley->line_id == $line->line_id ? "selected" : "" }}>{{ $line->FullName }}</option>
                            @endforeach
                        </select>
                        <button class="btn btn-sm btn-outline-success" type="button">Get</button>
                        <button class="btn btn-sm btn-outline-primary" type="button" onclick="initLineScan()">Scan</button>
                    </div>
                </div>
            </div>
        </div>
        <div class="card card-sb d-none" id="to-trolley-card">
            <div class="card-header">
                <h5 class="card-title fw-bold">Kirim ke Trolley</h5>
                <div class="card-tools">
                    <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i>
                    </button>
                </div>
            </div>
            <div class="card-body" style="display: block" id="scan-stocker">
                <div id="trolley-reader" onclick="clearTrolleyScan()"></div>
                <div class="mb-3">
                    <label class="form-label">Trolley</label>
                    <div class="input-group w-100">
                        <select class="form-control form-control-sm select2bs4" name="destination_trolley_id" id="destination_trolley_id">
                            @foreach ($trolleys as $trolley)
                                <option value="{{ $trolley->id }}">{{ $trolley->nama_trolley }}</option>
                            @endforeach
                        </select>
                        <button class="btn btn-sm btn-outline-success" type="button">Get</button>
                        <button class="btn btn-sm btn-outline-primary" type="button" onclick="initTrolleyScan()">Scan</button>
                    </div>
                </div>
            </div>
        </div>
        <div class="card card-primary h-100">
            <div class="card-header">
                <div class="row align-items-center">
                    <div class="col-6">
                        <h5 class="card-title fw-bold">
                            Kirim Stocker :
                        </h5>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="send-toolbar d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                    <div class="d-flex flex-wrap align-items-center gap-2" id="st-filter-group">
                        <button type="button" class="btn btn-outline-primary btn-sm text-nowrap" id="st-filter-btn" data-bs-toggle="modal" data-bs-target="#filterStockModal">
                            <i class="fas fa-filter fa-sm"></i> Filter
                            <span class="badge rounded-pill bg-primary filter-count d-none" id="filter-count">0</span>
                        </button>
                        @include('dc.trolley.stock-trolley.partials.stock-search-input', ['stockerColumn' => 2])
                        <small class="text-muted hint-click"><i class="fas fa-hand-pointer"></i> Centang atau klik baris untuk memilih</small>
                    </div>
                    {{-- No. BON & Kirim : dipindah ke action bar bawah saat ada stocker terpilih (lihat updateActionBar) --}}
                    <div class="d-flex align-items-center gap-2" id="send-toolbar-slot">
                        <div class="send-controls" id="send-controls">
                            <input type="text" class="form-control form-control-sm" id="no_bon" name="no_bon" value="" placeholder="No. BON">
                            <button type="button" class="btn btn-success btn-sm text-nowrap" onclick="sendToLine(this)"><i class="fa fa-paper-plane fa-sm"></i> Kirim</button>
                        </div>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-bordered table-hover w-100 stock-table" id="datatable-trolley-stock">
                        <thead>
                            <tr>
                                <th class="d-none">ID</th>
                                <th class="check-col">
                                    <input class="form-check-input" type="checkbox" value="" id="checkAllStock" title="Pilih semua">
                                </th>
                                <th>No. Stocker</th>
                                <th>WS / Style / Color</th>
                                <th class="text-center">Size / Qty</th>
                                <th>No. Cut</th>
                                <th>Panel</th>
                                <th>Part</th>
                                <th>Range</th>
                                <th>By</th>
                                {{-- Kolom data mentah untuk filter & perhitungan (disembunyikan) --}}
                                <th>No. WS</th>
                                <th>Style</th>
                                <th>Color</th>
                                <th>Size</th>
                                <th>Qty</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if ($trolleyStocks && $trolleyStocks->count() < 1)
                                {{-- No Data --}}
                            @else
                                @php
                                    $i = 0;
                                @endphp
                                @foreach ($trolleyStocks as $trolleyStock)
                                    <tr class="{{ $trolleyStock->diff_qty < 0 ? 'has-qty-diff' : '' }}">
                                        <td class="d-none">
                                            {{ $trolleyStock->stocker_id }}
                                        </td>
                                        <td class="check-col">
                                            <input class="form-check-input check-stock" type="checkbox" value="" onchange="checkStock(this)" id="stock_{{ $i }}">
                                        </td>
                                        <td class="col-stocker">
                                            <div class="stocker-list">@foreach (explode(', ', $trolleyStock->id_qr_stocker) as $qrStocker)<button type="button" class="stocker-chip stocker-detail-link" data-stocker="{{ $qrStocker }}" title="Lihat detail transaksi {{ $qrStocker }}">{{ $qrStocker }}</button>@endforeach</div>
                                        </td>
                                        <td class="col-wsc">
                                            <div class="wsc-ws">{{ $trolleyStock->act_costing_ws }}</div>
                                            <div class="wsc-style">{{ $trolleyStock->style }}</div>
                                            <div class="wsc-color"><span class="color-dot unknown"></span>{{ $trolleyStock->color }}</div>
                                        </td>
                                        <td class="col-sq">
                                            <div class="sq-size">{{ $trolleyStock->size }}</div>
                                            <div class="sq-qty">{{ $trolleyStock->qty }}</div>
                                        </td>
                                        <td>{{ $trolleyStock->no_cut }}</td>
                                        <td><span>{!! str_replace(",", "<br>", e($trolleyStock->panel)) !!}</span></td>
                                        <td>{{ $trolleyStock->nama_part }}</td>
                                        <td>{{ $trolleyStock->rangeAwalAkhir }}@if ($trolleyStock->diff_qty != 0) <span class="qty-diff">({{ $trolleyStock->diff_qty }})</span>@endif</td>
                                        <td>{{ $trolleyStock->user }}</td>
                                        <td>{{ $trolleyStock->act_costing_ws }}</td>
                                        <td>{{ $trolleyStock->style }}</td>
                                        <td>{{ $trolleyStock->color }}</td>
                                        <td>{{ $trolleyStock->size }}</td>
                                        <td>{{ $trolleyStock->qty }}</td>
                                    </tr>
                                    @php
                                        $i++;
                                    @endphp
                                @endforeach
                            @endif
                        </tbody>
                    </table>
                </div>

                {{-- Action bar : menempel di bawah layar, tampil saat ada filter aktif / stocker terpilih --}}
                <div class="stock-action-bar d-none" id="stock-action-bar">
                    <div class="stock-action-group" id="stock-action-selected">
                        <span class="selected-summary" id="selected-summary">
                            <i class="fas fa-check-square"></i>
                            <span id="selected-row-count-1">0</span> stocker dipilih
                            <span class="selected-divider">&middot;</span>
                            Total Qty <span id="selected-qty-sum">0</span>
                        </span>
                        <button type="button" class="btn btn-outline-danger btn-sm text-nowrap d-none" id="clear-selected" onclick="clearSelected()"><i class="fas fa-times fa-sm"></i> Batal pilih</button>
                    </div>
                    <div class="stock-action-group" id="stock-action-filter">
                        <span class="text-muted text-nowrap"><i class="fas fa-filter fa-sm"></i> Filter :</span>
                        <span class="filter-active-chips" id="filter-active-chips"></span>
                        <button type="button" class="btn btn-link text-danger p-0 text-nowrap" id="filter-reset-inline" onclick="resetStockFilter()">Reset filter</button>
                    </div>
                    <div class="stock-action-group stock-action-send" id="stock-action-send"></div>
                </div>
            </div>
        </div>
    </form>

    @php
        // Pilihan filter dari data yang tersedia di trolley ini
        $filterUnique = function ($values) {
            return collect($values)
                ->map(function ($value) { return trim((string) $value); })
                ->filter(function ($value) { return $value !== ''; })
                ->unique()
                ->sort(function ($a, $b) { return strnatcasecmp($a, $b); })
                ->values();
        };

        // Panel & Part berisi beberapa nilai dipisah koma
        $filterSplit = function ($field) use ($trolleyStocks) {
            return $trolleyStocks->flatMap(function ($trolleyStock) use ($field) {
                return explode(',', (string) $trolleyStock->{$field});
            });
        };
    @endphp

    {{-- Modal Detail Transaksi Stocker (klik No. Stocker) --}}
    @include('dc.trolley.stock-trolley.partials.stocker-detail-modal')

    {{-- Panduan halaman --}}
    @include('dc.trolley.stock-trolley.partials.guide-send')

    {{-- Modal Filter (di luar form supaya Enter tidak men-submit form) --}}
    @include('dc.trolley.stock-trolley.partials.stock-filter-modal', [
        // [label, kolom datatable, mode pencocokan, opsi, lebar kolom]
        'filterSelects' => [
            ['No. WS', 10, 'exact', $filterUnique($trolleyStocks->pluck('act_costing_ws')), 'col-6'],
            ['No. Cut', 5, 'exact', $filterUnique($trolleyStocks->pluck('no_cut')), 'col-6'],
            ['Style', 11, 'exact', $filterUnique($trolleyStocks->pluck('style')), 'col-6'],
            ['Color', 12, 'exact', $filterUnique($trolleyStocks->pluck('color')), 'col-6'],
            ['Panel', 6, 'contains', $filterUnique($filterSplit('panel')), 'col-6'],
            ['Size', 13, 'exact', $filterUnique($trolleyStocks->pluck('size')), 'col-6'],
            ['Part', 7, 'contains', $filterUnique($filterSplit('nama_part')), 'col-12'],
        ],
    ])
@endsection

@section('custom-script')
    <!-- DataTables  & Plugins -->
    <script src="{{ asset('plugins/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-responsive/js/dataTables.responsive.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-responsive/js/responsive.bootstrap4.min.js') }}"></script>
    <!-- Select2 -->
    <script src="{{ asset('plugins/select2/js/select2.full.min.js') }}"></script>

    @include('dc.trolley.stock-trolley.partials.stock-table-script')
    @include('dc.trolley.stock-trolley.partials.page-guide-script')

    <script>
        // Select2 Autofocus
        $(document).on('select2:open', () => {
            document.querySelector('.select2-search__field').focus();
        });

        // Initialize Select2 Elements
        $('.select2').select2()

        // Initialize Select2BS4 Elements
        $('.select2bs4').select2({
            theme: 'bootstrap4',
        });

        $(document).ready(async () => {
            document.getElementById("loading").classList.remove("d-none");

            $('#trolley').val("").trigger("change");
            $('#kode_stocker').val("").trigger("change");

            initLineScan();

            await $('#switch-destination').prop('checked', true);

            initialSelected();

            document.getElementById("loading").classList.add("d-none");
        });

        // Trolley ID
        var trolleyId = document.getElementById('trolley_id').value;

        // Datatable
        let datatableTrolleyStock = $("#datatable-trolley-stock").DataTable({
            ordering: false,
            columnDefs: [
                {
                    // Part
                    targets: [7],
                    render: (data, type, row, meta) => {
                        return `<span class="text-nowrap">`+ data.replace(/,/g, ", <br>") +`</span>`;
                    }
                },
                {
                    // No. WS, Style, Color, Size, Qty mentah : untuk filter & perhitungan
                    targets: [10, 11, 12, 13, 14],
                    visible: false
                },
                {
                    targets: "_all",
                    className: "text-nowrap"
                }
            ]
        });

        // Filter (modal) : fungsi ada di partial 'stock-table-script'
        initStockFilter(datatableTrolleyStock, {
            onApply: () => updateSelectedSum(),
        });

        // Isi titik warna di kolom WS / Style / Color (semua halaman datatable)
        datatableTrolleyStock.rows().every(function () {
            paintColorDot(this.node().querySelector('.wsc-color .color-dot'), this.data()[12]); // index 12 = Color (kolom tersembunyi)
        });

        function setRowChecked(tr, checked) {
            if (!tr) {
                return;
            }

            tr.classList.toggle('row-checked', checked);

            if (!checked) {
                tr.style.removeProperty('--row-bg');
                tr.style.removeProperty('--row-accent');
                return;
            }

            let data = datatableTrolleyStock.row(tr).data();
            let rgb = data ? colorFromName(data[12]) : null; // index 12 = Color (kolom tersembunyi)

            if (!rgb) {
                // Tidak dikenali : pakai warna default (biru)
                tr.style.removeProperty('--row-bg');
                tr.style.removeProperty('--row-accent');
                return;
            }

            let luminance = (0.2126 * rgb[0] + 0.7152 * rgb[1] + 0.0722 * rgb[2]) / 255;

            if (luminance > 0.85) {
                // Warna sangat terang (putih, cream) : digelapkan sedikit & garis abu agar tetap terlihat
                tr.style.setProperty('--row-bg', `rgb(${shadeRgb(rgb, -0.08).join(',')})`);
                tr.style.setProperty('--row-accent', '#adb5bd');
            } else {
                // Latar dibuat transparan supaya teks tetap terbaca
                tr.style.setProperty('--row-bg', `rgba(${rgb.join(',')}, 0.22)`);
                tr.style.setProperty('--row-accent', `rgb(${rgb.join(',')})`);
            }
        }

        // Check Stocker
        var stockArr = [];

        // Check each
        function checkStock(element) {
            let data = $('#datatable-trolley-stock').DataTable().row(element.closest('tr')).data();

            setRowChecked(element.closest('tr'), element.checked);

            if (data) {
                if (element.checked) {
                    stockArr.push(data);
                } else {
                    stockArr = stockArr.filter(item => item[0] != data[0]);
                }

                console.log(data, stockArr);

                updateSelectedSum();
            }
        }

        // Check all
        // $("#checkAllStock").on("change", function () {
        //     document.getElementById("loading").classList.remove("d-none");

        //     // get elements
        //     let checkStockElements = document.getElementsByClassName("check-stock");

        //     // reset stock arr
        //     stockArr = [];

        //     // check each element
        //     for (let i = 0; i < checkStockElements.length; i++) {
        //         if (this.checked) {
        //             checkStockElements[i].checked = true;

        //             // push data
        //             let data = $('#datatable-trolley-stock').DataTable().row(checkStockElements[i].closest('tr')).data();
        //             stockArr.push(data);
        //         } else {
        //             checkStockElements[i].checked = false;
        //         }
        //     }

        //     updateSelectedSum();
        // });
        $("#checkAllStock").on("change", function () {
            document.getElementById("loading").classList.remove("d-none");

            let table = $('#datatable-trolley-stock').DataTable();

            // reset stock arr
            stockArr = [];

            if (this.checked) {
                // check ALL checkboxes from filtered rows (across all pages)
                table.rows({ search: 'applied' }).nodes().to$()
                    .find('.check-stock')
                    .prop('checked', true)
                    .each(function () {
                        setRowChecked(this.closest('tr'), true);

                        let data = table.row($(this).closest('tr')).data();
                        stockArr.push(data);
                    });
            } else {
                // uncheck ALL checkboxes
                table.rows({ search: 'applied' }).nodes().to$()
                    .find('.check-stock')
                    .prop('checked', false)
                    .each(function () {
                        setRowChecked(this.closest('tr'), false);
                    });
            }

            updateSelectedSum();
        });

        // Update checked summary
        function updateSelectedSum() {
            let stockArrSum = stockArr.reduce((acc, row) => acc + Number(row[14]), 0); // index 14 = Qty (kolom tersembunyi)

            document.getElementById('selected-row-count-1').innerText = stockArr.length;
            document.getElementById('selected-qty-sum').innerText = stockArrSum.toLocaleString('id-ID');
            document.getElementById('selected-summary').classList.toggle('has-selected', stockArr.length > 0);
            document.getElementById('clear-selected').classList.toggle('d-none', stockArr.length < 1);

            updateActionBar();

            // Sinkronkan "Pilih semua" dengan baris yang tampil (hasil filter)
            let filteredCount = $('#datatable-trolley-stock').DataTable().rows({ search: 'applied' }).count();
            document.getElementById('checkAllStock').checked = filteredCount > 0 && stockArr.length >= filteredCount;

            document.getElementById("loading").classList.add("d-none");
        }

        // Reset checked
        function initialSelected() {
            stockArr = [];

            let checkStockElements = document.getElementsByClassName("check-stock");
            for (let i = 0; i < checkStockElements.length; i++) {
                checkStockElements[i].checked = false;
                setRowChecked(checkStockElements[i].closest('tr'), false);
            }
        }

        // Action bar bawah : tampil kalau ada stocker terpilih / filter aktif
        function updateActionBar() {
            let hasSelected = stockArr.length > 0;
            let hasFilter = document.getElementById('filter-active-chips').children.length > 0;

            document.getElementById('stock-action-selected').classList.toggle('d-none', !hasSelected);
            document.getElementById('stock-action-filter').classList.toggle('d-none', !hasFilter);
            document.getElementById('stock-action-bar').classList.toggle('d-none', !hasSelected && !hasFilter);

            // No. BON & Kirim pindah ke action bar hanya saat ada stocker terpilih
            // (elemen yang sama dipindah, jadi isi No. BON tidak hilang)
            let sendControls = document.getElementById('send-controls');
            let target = document.getElementById(hasSelected ? 'stock-action-send' : 'send-toolbar-slot');

            if (sendControls.parentElement !== target) {
                target.appendChild(sendControls);
            }
        }

        // Batal pilih : kosongkan semua pilihan (semua halaman & termasuk baris yang tersaring filter)
        function clearSelected() {
            stockArr = [];

            datatableTrolleyStock.rows().nodes().to$()
                .find('.check-stock')
                .prop('checked', false)
                .each(function () {
                    setRowChecked(this.closest('tr'), false);
                });

            updateSelectedSum();
        }

        // Klik baris untuk check / uncheck stocker
        $('#datatable-trolley-stock tbody').on('click', 'tr', function (e) {
            // Klik langsung pada checkbox / elemen interaktif sudah ditangani sendiri
            if ($(e.target).closest('input, a, button, label, select, textarea').length) {
                return;
            }

            let checkbox = this.querySelector('.check-stock');

            if (checkbox) {
                checkbox.checked = !checkbox.checked;
                checkStock(checkbox);
            }
        });

        // Datatable selected row selection
        // var totalQty = 0;
        // datatableTrolleyStock.on('click', 'tbody tr', async function(e) {
        //     await e.currentTarget.classList.toggle('selected');

        //     if (e.currentTarget.classList.contains('selected')) {
        //         totalQty += parseInt(e.currentTarget.cells[8].innerText);
        //     } else {
        //         totalQty -= parseInt(e.currentTarget.cells[8].innerText);
        //     }

        //     document.getElementById('selected-row-count-1').innerText = $('#datatable-trolley-stock').DataTable().rows('.selected').data().length+" (Total Qty : "+totalQty+")";
        // });

        // Submit Send to Line
        async function sendToLine(element) {
            // Validation
            if (!document.getElementById("no_bon").value) {
                return Swal.fire({
                        icon: 'error',
                        title: 'Gagal',
                        html: 'Harap isi no. bon',
                        showCancelButton: false,
                        showConfirmButton: true,
                        confirmButtonText: 'Oke',
                    }).then(() => {
                        if (isNotNull(res.redirect)) {
                            if (res.redirect != 'reload') {
                                location.href = res.redirect;
                            } else {
                                location.reload();
                            }
                        } else {
                            location.reload();
                        }
                    });
            } else {
                let date = new Date();

                let day = date.getDate();
                let month = date.getMonth() + 1;
                let year = date.getFullYear();

                let tanggalLoading = `${year}-${month}-${day}`

                let selectedStockerTable = stockArr;
                let selectedStocker = [];

                for (let key in selectedStockerTable) {
                    if (!isNaN(key)) {
                        console.log(selectedStockerTable[key]);
                        selectedStocker.push({
                            stocker_ids: selectedStockerTable[key][0]
                        });
                    }
                }

                // Destination Line/Trolley with Bon
                let lineId = document.getElementById('line_id').value;
                let destinationTrolleyId = document.getElementById('destination_trolley_id').value;
                let noBon = document.getElementById('no_bon').value;

                if (tanggalLoading && selectedStocker.length > 0) {
                    if (document.getElementById("loading")) {
                        document.getElementById("loading").classList.remove("d-none");
                    }

                    element.setAttribute('disabled', true);

                    $.ajax({
                        type: "POST",
                        url: '{!! route('submit-send-trolley-stock') !!}',
                        data: {
                            tanggal_loading: tanggalLoading,
                            selectedStocker: selectedStocker,
                            destination: destination,
                            line_id: lineId,
                            destination_trolley_id: destinationTrolleyId,
                            no_bon: noBon,
                        },
                        success: function(res) {
                            if (document.getElementById("loading")) {
                                document.getElementById("loading").classList.add("d-none");
                            }

                            element.removeAttribute('disabled');

                            if (res.status == 200) {
                                document.getElementById("no_bon").value = "";

                                iziToast.success({
                                    title: 'Success',
                                    message: res.message,
                                    position: 'topCenter'
                                });
                            } else {
                                iziToast.error({
                                    title: 'Error',
                                    message: res.message,
                                    position: 'topCenter'
                                });
                            }

                            if (res.additional) {
                                let message = "";

                                if (res.additional['success'].length > 0) {
                                    res.additional['success'].forEach(element => {
                                        message += element['stocker'] + " - Berhasil <br>";
                                    });
                                }

                                if (res.additional['fail'].length > 0) {
                                    res.additional['fail'].forEach(element => {
                                        message += element['stocker'] + " - Gagal <br>";
                                    });
                                }

                                if (res.additional['exist'].length > 0) {
                                    res.additional['exist'].forEach(element => {
                                        message += element['stocker'] + " - Sudah Ada di '" + element["line"] + "' ( " + element["additional"] + " ) <br>";
                                    });
                                }

                                Swal.fire({
                                    icon: 'info',
                                    title: 'Hasil Transfer',
                                    html: message,
                                    showCancelButton: false,
                                    showConfirmButton: true,
                                    confirmButtonText: 'Oke',
                                }).then(() => {
                                    if (isNotNull(res.redirect)) {
                                        if (res.redirect != 'reload') {
                                            location.href = res.redirect;
                                        } else {
                                            location.reload();
                                        }
                                    } else {
                                        location.reload();
                                    }
                                });
                            }
                        },
                        error: function(jqXHR) {
                            if (document.getElementById("loading")) {
                                document.getElementById("loading").classList.add("d-none");
                            }

                            element.removeAttribute('disabled');

                            // let res = jqXHR.responseJSON;
                            // let message = '';

                            // for (let key in res.errors) {
                            //     message = res.errors[key];
                            // }

                            iziToast.error({
                                title: 'Error',
                                message: 'Terjadi kesalahan.',
                                position: 'topCenter'
                            });

                            console.log(jqXHR);
                        }
                    })
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal',
                        html: "Harap pilih line/trolley dan tentukan stocker nya",
                        showCancelButton: false,
                        showConfirmButton: true,
                        confirmButtonText: 'Oke',
                    });
                }
            }
        }

        // Scan QR Module :
            // Variable List :
                var lineScanner = new Html5Qrcode("line-reader");
                var lineScannerInitialized = false;

                var trolleyScanner = new Html5Qrcode("trolley-reader");
                var trolleyScannerInitialized = false;

            // Function List :
                // -Initialize Line Scanner-
                    async function initLineScan() {
                        if (document.getElementById("line-reader")) {
                            if (lineScannerInitialized == false) {
                                if (lineScanner == null || (lineScanner && (lineScanner.getState() && lineScanner.getState() != 2))) {
                                    const lineScanSuccessCallback = (decodedText, decodedResult) => {
                                            // handle the scanned code as you like, for example:
                                        console.log(`Code matched = ${decodedText}`, decodedResult);

                                        // store to input text
                                        let breakDecodedText = decodedText.split('-');

                                        setLineInput(breakDecodedText[0])

                                        clearLineScan();
                                    };
                                    const lineScanConfig = { fps: 10, qrbox: { width: 250, height: 250 } };

                                    // If you want to prefer front camera
                                    await lineScanner.start({ facingMode: "environment" }, lineScanConfig, lineScanSuccessCallback);

                                    lineScannerInitialized = true;
                                }
                            }
                        }
                    }

                    async function clearLineScan() {
                        if (lineScannerInitialized) {
                            if (lineScanner && (lineScanner.getState() && lineScanner.getState() != 1)) {
                                await lineScanner.stop();
                                await lineScanner.clear();
                            }

                            lineScannerInitialized = false;
                        }
                    }

                    async function initTrolleyScan() {
                        if (document.getElementById("trolley-reader")) {
                            if (trolleyScannerInitialized == false) {
                                if (trolleyScanner == null || (trolleyScanner && (trolleyScanner.getState() && trolleyScanner.getState() != 2))) {
                                    const trolleyScanSuccessCallback = (decodedText, decodedResult) => {
                                            // handle the scanned code as you like, for example:
                                        console.log(`Code matched = ${decodedText}`, decodedResult);

                                        // store to input text
                                        let breakDecodedText = decodedText.split('-');

                                        setTrolleyInput(breakDecodedText[0])

                                        clearTrolleyScan();
                                    };
                                    const trolleyScanConfig = { fps: 10, qrbox: { width: 250, height: 250 } };

                                    // If you want to prefer front camera
                                    await trolleyScanner.start({ facingMode: "environment" }, trolleyScanConfig, trolleyScanSuccessCallback);

                                    trolleyScannerInitialized = true;
                                }
                            }
                        }
                    }

                    async function clearTrolleyScan() {
                        if (trolleyScannerInitialized) {
                            if (trolleyScanner && (trolleyScanner.getState() && trolleyScanner.getState() != 1)) {
                                await trolleyScanner.stop();
                                await trolleyScanner.clear();
                            }

                            trolleyScannerInitialized = false;
                        }
                    }

                    async function refreshStockerScan() {
                        await clearLineScan();
                        await initLineScan();

                        await clearTrolleyScan();
                        await initTrolleyScan();
                    }

                    function setLineInput(val) {
                        $('#line_id').val(val).trigger("change");
                    }

                    function setTrolleyInput(val) {
                        $('#destination_trolley_id').val(val).trigger("change");
                    }

        // -Switch Destination-
        var destination = "line";

        function switchDestination(element) {
            if (element.checked) {
                toLine();
            } else {
                toTrolley();
            }
        }

        // When Destination = line
        async function toLine() {
            document.getElementById("loading").classList.remove("d-none");

            destination = "line";

            document.getElementById("to-trolley-card").classList.add('d-none');
            document.getElementById("to-trolley").classList.add('d-none');

            document.getElementById("to-line-card").classList.remove('d-none');
            document.getElementById("to-line").classList.remove('d-none');

            $("#line_id").val("").trigger("change");

            await clearTrolleyScan();

            document.getElementById("loading").classList.add("d-none");

            await initLineScan();
        }

        // When Destination = trolley
        async function toTrolley() {
            document.getElementById("loading").classList.remove("d-none");

            destination = "trolley";

            document.getElementById("to-line-card").classList.add('d-none');
            document.getElementById("to-line").classList.add('d-none');

            document.getElementById("to-trolley-card").classList.remove('d-none');
            document.getElementById("to-trolley").classList.remove('d-none');

            $("#destination_trolley_id").val("").trigger("change");

            await clearLineScan();

            document.getElementById("loading").classList.add("d-none");

            await initTrolleyScan();
        }
    </script>

    {{-- Panduan halaman : langkah tur --}}
    <script>
        (function () {
            const noStock = 'Trolley ini belum berisi stok.';

            const guide = initPageGuide({
                modal: '#pgGuide',
                button: '#pg-guide-btn',
                tourKey: 'pg_tour_send_trolley_{{ Auth::id() }}',
                steps: [
                    { sec: 'pgs-atas', sel: '.st-head-r', lbl: 'Tombol di kanan atas',
                      d: '<b>Panduan</b> membuka panduan ini · <b>Tampilan lama</b> membuka tampilan sebelumnya · <b>Kembali</b> ke daftar stok trolley · <b>Alokasi Stok Trolley</b> untuk scan stocker masuk ke trolley ini.' },
                    { sec: 'pgs-tujuan', sel: '.st-dest', lbl: 'Pilih tujuan kirim',
                      d: 'Menyala = kirim ke <b>Line</b>. Mati = pindah ke <b>Trolley</b> lain. Kartu di bawahnya ikut berganti.' },
                    { sec: 'pgs-tujuan', sel: '#to-line-card, #to-trolley-card', lbl: 'Line / trolley tujuan',
                      d: 'Pilih dari daftar, atau tekan <b>Scan</b> untuk scan QR pakai kamera.' },
                    { sec: 'pgs-cari', sel: '#st-filter-group', lbl: 'Cari & filter',
                      d: '<b>Cari No. Stocker</b> langsung menyaring saat mengetik. <b>Filter</b> untuk WS, No. Cut, Style, Color, Panel, Size, Part.' },
                    { sec: 'pgs-tabel', sel: '#datatable-trolley-stock thead', lbl: 'Kolom tabel stok',
                      d: 'Satu baris = satu grup stocker. <b>Qty</b> = qty terkecil di grup, yang akan dikirim. Garis merah tipis di No. Stocker = ada selisih qty minus.' },
                    { sec: 'pgs-pilih', sel: '#datatable-trolley-stock tbody .check-col', lbl: 'Memilih stocker',
                      d: 'Centang kotaknya atau <b>klik di mana saja pada baris</b>. Centang di judul kolom = pilih semua yang tampil.', missing: noStock },
                    { sec: 'pgs-detail', sel: '#datatable-trolley-stock .stocker-detail-link', lbl: 'Detail transaksi stocker',
                      d: 'Klik No. Stocker untuk melihat riwayat DC In sampai Secondary dan <b>qty terakhir</b> tiap stocker di grupnya.', missing: noStock },
                    { sec: 'pgs-kirim', sel: '#send-controls', lbl: 'No. BON & Kirim',
                      d: 'Isi <b>No. BON</b> (wajib) lalu tekan <b>Kirim</b>. Pindah ke bar bawah begitu ada stocker dipilih.' },
                    { sec: 'pgs-kirim', sel: '#stock-action-bar', lbl: 'Bar bawah',
                      d: 'Jumlah stocker dipilih, <b>Total Qty</b>, Batal pilih, chip filter aktif, serta No. BON &amp; Kirim.',
                      missing: 'Bar ini muncul di bawah tabel setelah ada stocker yang dipilih atau filter aktif.' },
                ],
            });

            // Tur otomatis saat halaman pertama kali dibuka (tabel sudah dirender di server)
            if (document.readyState === 'complete') {
                guide.autoTour();
            } else {
                $(window).on('load', () => guide.autoTour());
            }
        })();
    </script>
@endsection
