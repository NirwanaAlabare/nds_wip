@extends('layouts.index', ['containerFluid' => true])

@section('custom-link')
    <!-- Select2 -->
    <link rel="stylesheet" href="{{ asset('plugins/select2/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}">

    <style>
        :root {
            --sc-navy: #082149;
            --sc-border: #e5e9f0;
            --sc-line: #f1f4f8;
            --sc-muted: #64748b;
            --sc-faint: #94a3b8;
            --sc-success: #059669;
            --sc-amber: #d97706;
        }

        .sc-label {
            display: block;
            margin-bottom: .25rem;
            font-size: .68rem;
            font-weight: 600;
            letter-spacing: .06em;
            text-transform: uppercase;
            color: var(--sc-faint);
        }

        .sc-table thead th {
            position: sticky;
            top: 0;
            z-index: 1;
            background: #fafbfd;
            color: var(--sc-faint);
            font-size: .68rem;
            font-weight: 600;
            letter-spacing: .05em;
            text-transform: uppercase;
            white-space: nowrap;
        }

        .sc-table tbody td,
        .sc-table tfoot td {
            font-size: .85rem;
            vertical-align: middle;
            border-color: var(--sc-line);
        }

        .sc-table tfoot td {
            background: #fafbfd;
            font-weight: 700;
        }

        .sc-table-wrap {
            max-height: 50vh;
            overflow: auto;
        }

        .sc-saldo {
            font-weight: 700;
            color: var(--sc-navy);
        }

        .sc-saldo.is-minus {
            color: #dc2626;
        }

        .sc-sisa {
            font-weight: 700;
            color: var(--sc-amber);
        }

        .sc-banner {
            display: flex;
            align-items: center;
            gap: .6rem;
            padding: .6rem .9rem;
            border-radius: 8px;
            font-size: .85rem;
        }

        .sc-banner.is-preview {
            background: #eef4ff;
            color: #1e40af;
        }

        .sc-banner.is-closed {
            background: #ecfdf5;
            color: var(--sc-success);
        }

        .sc-banner.is-blocked {
            background: #fef2f2;
            color: #dc2626;
        }

        .sc-section-title {
            font-size: .8rem;
            font-weight: 700;
            color: var(--sc-navy);
        }

        .sc-row-click {
            cursor: pointer;
        }
    </style>
@endsection

@section('content')
    <div class="container-fluid">
        <h3 class="my-3 text-sb text-start fw-bold">Closing Stok Sewing In</h3>

        <div class="card">
            <div class="card-body">
                <div class="row g-2 align-items-end">
                    <div class="col-md-4">
                        <label class="sc-label" for="line_id">Line</label>
                        <select class="form-select select2bs4" id="line_id">
                            <option value="">Pilih Line</option>
                            @foreach ($lines as $line)
                                <option value="{{ $line->line_id }}">{{ $line->FullName }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="sc-label" for="tanggal">Tanggal</label>
                        <input type="date" class="form-control" id="tanggal" value="{{ date('Y-m-d') }}" max="{{ date('Y-m-d') }}">
                    </div>
                    <div class="col-md-auto">
                        <button class="btn btn-outline-secondary" type="button" onclick="loadData()" title="Hitung ulang"><i class="fa-solid fa-rotate"></i></button>
                    </div>
                    <div class="col-md">
                        <div class="sc-banner is-preview d-none" id="banner"></div>
                    </div>
                </div>
                <small class="text-muted d-block mt-2">
                    <b>Saldo awal</b> = stok sampai kemarin (closing terakhir + transaksi yang belum di-closing).
                    <b>Saldo akhir</b> = saldo awal + Sewing In + mutasi masuk − mutasi keluar − output.
                    Setelah closing, transaksi line ini sampai tanggal tsb <b>terkunci</b>.
                </small>
            </div>

            {{-- Saldo WIP set --}}
            <div class="px-3 pb-1 sc-section-title">Saldo WIP (set) per WS / Color / Size</div>
            <div class="sc-table-wrap">
                <table class="table table-hover sc-table mb-0">
                    <thead>
                        <tr>
                            <th>WS</th>
                            <th>Color</th>
                            <th class="text-center">Size</th>
                            <th class="text-end">Saldo Awal</th>
                            <th class="text-end">Sewing In</th>
                            <th class="text-end">Mutasi In</th>
                            <th class="text-end">Mutasi Out</th>
                            <th class="text-end">Output</th>
                            <th class="text-end">Saldo Akhir</th>
                        </tr>
                    </thead>
                    <tbody id="row_list">
                        <tr><td colspan="9" class="text-center text-muted py-4">Pilih line</td></tr>
                    </tbody>
                    <tfoot id="row_total"></tfoot>
                </table>
            </div>

            {{-- Stocker outstanding --}}
            <div class="px-3 pt-3 pb-1 sc-section-title">Sisa Part Stocker Outstanding</div>
            <div class="sc-table-wrap">
                <table class="table table-hover sc-table mb-0">
                    <thead>
                        <tr>
                            <th>Stocker</th>
                            <th>WS</th>
                            <th>Color</th>
                            <th class="text-center">Size</th>
                            <th>Part</th>
                            <th class="text-end">Qty</th>
                            <th class="text-end">Terpakai</th>
                            <th class="text-end">Sisa</th>
                        </tr>
                    </thead>
                    <tbody id="stocker_list">
                        <tr><td colspan="8" class="text-center text-muted py-3">-</td></tr>
                    </tbody>
                </table>
            </div>

            <div class="card-body border-top d-flex justify-content-end">
                <button class="btn btn-sb" type="button" id="btn_closing" onclick="confirmClosing()" disabled>
                    <i class="fa-solid fa-lock"></i> Closing
                </button>
            </div>
        </div>

        {{-- Riwayat closing --}}
        <div class="card">
            <div class="card-body pb-2">
                <div class="row g-2 align-items-end">
                    <div class="col-6 col-md-2">
                        <label class="sc-label" for="history_dari">Dari</label>
                        <input type="date" class="form-control" id="history_dari" value="{{ date('Y-m-01') }}">
                    </div>
                    <div class="col-6 col-md-2">
                        <label class="sc-label" for="history_sampai">Sampai</label>
                        <input type="date" class="form-control" id="history_sampai" value="{{ date('Y-m-d') }}">
                    </div>
                    <div class="col-md">
                        <h6 class="fw-bold text-sb mb-2">Riwayat Closing <small class="text-muted fw-normal">(klik baris untuk melihat)</small></h6>
                    </div>
                </div>
            </div>
            <div class="sc-table-wrap">
                <table class="table table-hover sc-table mb-0">
                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th>Line</th>
                            <th class="text-end">WS/Size</th>
                            <th class="text-end">Sewing In</th>
                            <th class="text-end">Output</th>
                            <th class="text-end">Saldo Akhir</th>
                            <th class="text-end">Stocker Sisa</th>
                            <th>Oleh</th>
                            <th>Waktu</th>
                        </tr>
                    </thead>
                    <tbody id="history_list"></tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

@section('custom-script')
    <!-- Select2 -->
    <script src="{{ asset('plugins/select2/js/select2.full.min.js') }}"></script>
    <script>
        $('.select2bs4').select2({
            theme: 'bootstrap4'
        })
    </script>

    <script>
        var closingData = null;

        function escapeHtml(text) {
            return $("<div>").text(text ?? "-").html();
        }

        function formatDate(date) {
            return date ? date.substring(8, 10) + "-" + date.substring(5, 7) + "-" + date.substring(0, 4) : "-";
        }

        $("#line_id").on("change", function () {
            loadData();
            loadHistory();
        });

        $("#tanggal").on("change", loadData);
        $("#history_dari, #history_sampai").on("change", loadHistory);

        // ===== Data closing / pratinjau =====
        function loadData() {
            let lineId = $("#line_id").val();

            closingData = null;
            $("#btn_closing").prop("disabled", true);

            if (!lineId) {
                $("#banner").addClass("d-none");
                $("#row_list").html(`<tr><td colspan="9" class="text-center text-muted py-4">Pilih line</td></tr>`);
                $("#row_total").html("");
                $("#stocker_list").html(`<tr><td colspan="8" class="text-center text-muted py-3">-</td></tr>`);
                return;
            }

            $("#row_list").html(`<tr><td colspan="9" class="text-center text-muted py-4"><i class="fa-solid fa-spinner fa-spin"></i> Menghitung...</td></tr>`);
            $("#row_total").html("");

            $.ajax({
                url: "{{ route('sewing-in-closing-data') }}",
                type: "get",
                data: {
                    line_id: lineId,
                    tanggal: $("#tanggal").val(),
                },
                success: function (res) {
                    closingData = res;
                    renderData();
                },
                error: function (jqXHR) {
                    let errors = Object.values(jqXHR.responseJSON?.errors ?? {}).flat();

                    $("#row_list").html(`<tr><td colspan="9" class="text-center text-danger py-4">${escapeHtml(errors[0] ?? 'Gagal menghitung closing')}</td></tr>`);
                }
            });
        }

        function renderData() {
            let res = closingData;

            // Status
            if (res.closed) {
                $("#banner").attr("class", "sc-banner is-closed").html(`<i class="fa-solid fa-lock"></i> Sudah closing oleh <b>${escapeHtml(res.closed_by)}</b>, ${escapeHtml(res.closed_at)}`);
            } else if (res.blocked) {
                $("#banner").attr("class", "sc-banner is-blocked").html(`<i class="fa-solid fa-ban"></i> ${escapeHtml(res.blocked)}`);
            } else {
                $("#banner").attr("class", "sc-banner is-preview").html(`<i class="fa-solid fa-eye"></i> Pratinjau, belum closing`);
            }

            // Saldo WIP
            let total = { saldo_awal: 0, qty_sewing_in: 0, qty_mutasi_in: 0, qty_mutasi_out: 0, qty_output: 0, saldo_akhir: 0 };

            let rows = res.rows.map(row => {
                Object.keys(total).forEach(field => total[field] += Number(row[field]));

                return `
                    <tr>
                        <td class="text-nowrap">${escapeHtml(row.act_costing_ws)}</td>
                        <td>${escapeHtml(row.color)}</td>
                        <td class="text-center fw-semibold">${escapeHtml(row.size)}</td>
                        <td class="text-end">${escapeHtml(row.saldo_awal)}</td>
                        <td class="text-end">${escapeHtml(row.qty_sewing_in)}</td>
                        <td class="text-end">${escapeHtml(row.qty_mutasi_in)}</td>
                        <td class="text-end">${escapeHtml(row.qty_mutasi_out)}</td>
                        <td class="text-end">${escapeHtml(row.qty_output)}</td>
                        <td class="text-end"><span class="sc-saldo ${row.saldo_akhir < 0 ? 'is-minus' : ''}">${escapeHtml(row.saldo_akhir)}</span></td>
                    </tr>
                `;
            }).join("");

            $("#row_list").html(rows || `<tr><td colspan="9" class="text-center text-muted py-4">Tidak ada stok / transaksi di line ini${res.closed ? '' : ' (closing akan disimpan sebagai line kosong)'}</td></tr>`);
            $("#row_total").html(res.rows.length > 0 ? `
                <tr>
                    <td colspan="3">Total</td>
                    <td class="text-end">${total.saldo_awal}</td>
                    <td class="text-end">${total.qty_sewing_in}</td>
                    <td class="text-end">${total.qty_mutasi_in}</td>
                    <td class="text-end">${total.qty_mutasi_out}</td>
                    <td class="text-end">${total.qty_output}</td>
                    <td class="text-end"><span class="sc-saldo">${total.saldo_akhir}</span></td>
                </tr>
            ` : "");

            // Stocker outstanding
            let stockers = res.stockers.map(stocker => `
                <tr>
                    <td class="fw-bold">${escapeHtml(stocker.id_qr_stocker)}</td>
                    <td class="text-nowrap">${escapeHtml(stocker.act_costing_ws)}</td>
                    <td>${escapeHtml(stocker.color)}</td>
                    <td class="text-center">${escapeHtml(stocker.size)}</td>
                    <td>${escapeHtml(stocker.nama_part)}</td>
                    <td class="text-end">${escapeHtml(stocker.qty_stocker)}</td>
                    <td class="text-end">${escapeHtml(stocker.qty_used)}</td>
                    <td class="text-end"><span class="sc-sisa">${escapeHtml(stocker.qty_sisa)}</span></td>
                </tr>
            `).join("");

            $("#stocker_list").html(stockers || `<tr><td colspan="8" class="text-center text-muted py-3">Tidak ada sisa stocker</td></tr>`);

            $("#btn_closing").prop("disabled", res.closed || !!res.blocked);
        }

        // ===== Simpan closing =====
        function confirmClosing() {
            let saldo = closingData.rows.reduce((total, row) => total + Number(row.saldo_akhir), 0);

            Swal.fire({
                icon: 'warning',
                title: 'Closing stok?',
                html: `
                    <div class="text-start">
                        Line: <b>${escapeHtml($("#line_id option:selected").text())}</b><br>
                        Tanggal: <b>${formatDate($("#tanggal").val())}</b><br>
                        Saldo akhir: <b>${saldo}</b> set · <b>${closingData.stockers.length}</b> stocker sisa<br>
                        <small class="text-danger">Setelah closing, Sewing In & mutasi line ini sampai tanggal tsb terkunci.</small>
                    </div>
                `,
                showCancelButton: true,
                confirmButtonText: 'Closing',
                cancelButtonText: 'Batal',
                confirmButtonColor: '#082149',
            }).then((result) => {
                if (result.isConfirmed) {
                    saveClosing();
                }
            });
        }

        function saveClosing() {
            document.getElementById("loading").classList.remove("d-none");
            $("#btn_closing").prop("disabled", true);

            $.ajax({
                url: "{{ route('sewing-in-closing-store') }}",
                type: "post",
                headers: {
                    "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content")
                },
                data: {
                    line_id: $("#line_id").val(),
                    tanggal: $("#tanggal").val(),
                },
                success: function (res) {
                    // Antrian simpan penuh: tunggu lalu kirim ulang otomatis
                    if (res.busy) {
                        setTimeout(saveClosing, 500);
                        return;
                    }

                    document.getElementById("loading").classList.add("d-none");

                    if (res.status != 200) {
                        Swal.fire({ icon: 'error', title: 'Gagal', text: res.message });
                        loadData();
                        return;
                    }

                    Swal.fire({ icon: 'success', title: 'Berhasil', text: res.message });

                    loadData();
                    loadHistory();
                },
                error: function (jqXHR) {
                    document.getElementById("loading").classList.add("d-none");
                    renderData();

                    let errors = Object.values(jqXHR.responseJSON?.errors ?? {}).flat();

                    Swal.fire({ icon: 'error', title: 'Error', text: errors[0] ?? jqXHR.responseJSON?.message ?? 'Terjadi kesalahan saat closing' });
                }
            });
        }

        // ===== Riwayat closing =====
        function loadHistory() {
            $("#history_list").html(`<tr><td colspan="9" class="text-center text-muted py-3"><i class="fa-solid fa-spinner fa-spin"></i> Memuat...</td></tr>`);

            $.ajax({
                url: "{{ route('sewing-in-closing-history') }}",
                type: "get",
                data: {
                    tanggal_awal: $("#history_dari").val(),
                    tanggal_akhir: $("#history_sampai").val(),
                    line_id: $("#line_id").val(),
                },
                success: function (res) {
                    let html = res.data.map(item => `
                        <tr class="sc-row-click" onclick="openClosing(${Number(item.line_id)}, '${escapeHtml(item.tanggal)}')">
                            <td class="text-nowrap">${formatDate(item.tanggal)}</td>
                            <td>${escapeHtml(item.nama_line)}</td>
                            <td class="text-end">${escapeHtml(item.total_ws)}</td>
                            <td class="text-end">${escapeHtml(item.qty_sewing_in)}</td>
                            <td class="text-end">${escapeHtml(item.qty_output)}</td>
                            <td class="text-end"><span class="sc-saldo">${escapeHtml(item.saldo_akhir)}</span></td>
                            <td class="text-end">${escapeHtml(item.total_stocker)} <span class="text-muted">(${escapeHtml(item.qty_sisa)} pcs)</span></td>
                            <td>${escapeHtml(item.created_by_username)}</td>
                            <td class="text-nowrap">${escapeHtml(item.created_at)}</td>
                        </tr>
                    `).join("");

                    $("#history_list").html(html || `<tr><td colspan="9" class="text-center text-muted py-3">Belum ada closing</td></tr>`);
                },
                error: function (jqXHR) {
                    let errors = Object.values(jqXHR.responseJSON?.errors ?? {}).flat();

                    $("#history_list").html(`<tr><td colspan="9" class="text-center text-danger py-3">${escapeHtml(errors[0] ?? 'Gagal memuat riwayat')}</td></tr>`);
                }
            });
        }

        // Klik riwayat: tampilkan closing tsb di atas
        function openClosing(lineId, tanggal) {
            $("#tanggal").val(tanggal);
            $("#line_id").val(String(lineId)).trigger("change.select2");

            loadData();
            window.scrollTo({ top: 0, behavior: "smooth" });
        }

        loadHistory();
    </script>
@endsection
