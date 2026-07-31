@extends('layouts.index', ['page' => 'dashboard-report-bc', 'containerFluid' => true])

@section('title', 'Laporan ' . ucfirst($jenis) . ' - ' . strtoupper(str_replace('-', ' ', $kategori)))


@section('custom-link')
    <link rel="stylesheet" href="{{ asset('plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/datatables-responsive/css/responsive.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/datatables-buttons/css/buttons.bootstrap4.min.css') }}">

    <style>
        .report-detail-card {
            border: none;
            border-radius: 18px;
            overflow: hidden;
            box-shadow: 0 10px 40px rgba(30, 41, 59, 0.08);
        }

        .report-detail-card .card-header {
            background: #0f172a;
            border: none;
            padding: 1.7rem 2rem 1.6rem;
            position: relative;
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
            align-items: flex-end;
            justify-content: space-between;
        }

        .report-detail-card .card-eyebrow {
            font-size: 0.72rem;
            font-weight: 700;
            letter-spacing: 1.6px;
            text-transform: uppercase;
            color: #fbbf24;
            margin: 0 0 6px 2px;
            display: block;
        }

        .report-detail-card .card-title {
            font-size: 1.4rem;
            font-weight: 800;
            letter-spacing: -0.3px;
            color: #f8fafc;
            margin: 0 0 0 2px;
            line-height: 1.25;
        }

        .report-detail-card .card-title .periode-pill {
            display: inline-block;
            font-size: 0.7rem;
            font-weight: 700;
            letter-spacing: 0.3px;
            text-transform: none;
            color: #0f172a;
            background: #fbbf24;
            padding: 4px 11px;
            border-radius: 999px;
            vertical-align: middle;
            margin-left: 8px;
        }

        .report-detail-card .btn-back {
            border-radius: 8px;
            font-weight: 600;
            font-size: 0.8rem;
            background: rgba(255,255,255,0.1);
            color: #f8fafc;
            border: 1px solid rgba(255,255,255,0.25);
            transition: all 0.2s ease;
            white-space: nowrap;
        }

        .report-detail-card .btn-back:hover {
            background: rgba(255,255,255,0.18);
            color: #fff;
        }

        .report-detail-card .card-body {
            background: #fbfcff;
            padding: 1.75rem 2rem 2rem;
        }

        .toolbar-panel {
            background: #fff;
            border: 1px solid #e7ecf3;
            border-radius: 14px;
            padding: 1rem 1.25rem;
            margin-bottom: 1.4rem;
            box-shadow: 0 2px 10px rgba(15, 23, 42, 0.04);
        }

        .toolbar-panel label {
            font-weight: 700;
            font-size: 0.75rem;
            color: #475569;
            text-transform: uppercase;
            letter-spacing: 0.4px;
        }

        .toolbar-panel .form-control,
        .toolbar-panel .select2-container--bootstrap4 .select2-selection {
            border-radius: 8px;
            border: 1.5px solid #e2e8f0;
        }

        #btn-export-excel {
            border-radius: 8px;
            font-weight: 600;
            background: linear-gradient(135deg, #16a34a, #15803d);
            border: none;
            box-shadow: 0 4px 12px rgba(22, 163, 74, 0.25);
            transition: all 0.2s ease;
        }

        #btn-export-excel:hover {
            transform: translateY(-1px);
            box-shadow: 0 6px 16px rgba(22, 163, 74, 0.35);
        }

        /* ============================================================
           TABEL REPORT — wrapper luar (bingkai rounded + bayangan)
           overflow TIDAK hidden di sini karena scrollX/scrollY DataTables
           butuh area sendiri buat scroll, biar gak ke-clip.
        ============================================================ */
        .report-table-wrapper {
            border: 1px solid #e7ecf3;
            border-radius: 14px;
            box-shadow: 0 2px 10px rgba(15, 23, 42, 0.04);
            overflow: hidden; /* ini aman karena cuma bungkus radius luar */
        }

        #tabel-report {
            margin-bottom: 0 !important;
        }

        /* Header (di-clone DataTables ke tabel terpisah saat scrollX aktif) */
        #tabel-report thead th,
        .dataTables_scrollHeadTable thead th,
        .dataTables_scrollHead thead th {
            background: #0f172a !important;
            color: #f8fafc !important;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            font-weight: 700;
            vertical-align: middle;
            border-color: #1e293b !important;
            white-space: nowrap;
        }

        #tabel-report tbody td {
            font-size: 12.5px;
            vertical-align: middle;
            white-space: nowrap;
        }

        #tabel-report tbody tr:hover {
            background-color: #fffbeb !important;
        }

        /* Paksa tabel head & body punya lebar identik biar kolom gak geser */
        .dataTables_scrollHeadTable,
        .dataTables_scrollBody table {
            width: 100% !important;
        }

        .dataTables_scrollBody {
            border-top: none !important;
        }

        /* Scrollbar custom biar enak dilihat (opsional, tapi bikin scroll kanan kelihatan jelas) */
        .dataTables_scrollBody::-webkit-scrollbar {
            height: 10px;
            width: 10px;
        }
        .dataTables_scrollBody::-webkit-scrollbar-track {
            background: #f1f5f9;
        }
        .dataTables_scrollBody::-webkit-scrollbar-thumb {
            background: #94a3b8;
            border-radius: 10px;
        }
        .dataTables_scrollBody::-webkit-scrollbar-thumb:hover {
            background: #64748b;
        }

        /* Info & length/pagination DataTables biar konsisten sama tema */
        .dataTables_wrapper .dataTables_length,
        .dataTables_wrapper .dataTables_filter,
        .dataTables_wrapper .dataTables_info,
        .dataTables_wrapper .dataTables_paginate {
            padding: 0.85rem 1rem;
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button.current {
            background: #0f172a !important;
            border-color: #0f172a !important;
            color: #fff !important;
        }

         .report-detail-card .card-header .card-tools {
            margin-left: auto;
        }

    </style>
@endsection

@section('content')
    <div class="card report-detail-card">

        {{-- HEADER --}}
        <div class="card-header">
            <div>
                <span class="card-eyebrow">Pabean · Report BC</span>
                <h3 class="card-title mb-0">
                    Laporan {{ ucfirst($jenis) }} - {{ strtoupper(str_replace('-', ' ', $kategori)) }}
                    <span class="periode-pill">
                        {{ \Carbon\Carbon::parse($fromDate)->format('d/m/Y') }} s/d {{ \Carbon\Carbon::parse($toDate)->format('d/m/Y') }}
                    </span>
                </h3>
            </div>
            <div class="card-tools" style="text-align: right;">
                <a href="{{ route('index-report-bc') }}" class="btn btn-sm btn-back">
                    <i class="fas fa-arrow-left mr-1"></i> Kembali
                </a>
            </div>
        </div>

        <div class="card-body">

            <form method="GET" action="" class="toolbar-panel">
                <input type="hidden" name="from" value="{{ $fromDate }}">
                <input type="hidden" name="to" value="{{ $toDate }}">

                <div class="row align-items-center">
                    <div class="col-md-8 d-flex align-items-center">
                        <label class="mr-3 mb-0" for="filter_by">Filter Berdasarkan:</label>
                        <select class="form-control form-control-sm w-auto select2bs4" id="filter_by" name="filter_by" onchange="changeFilter(this)">
                            <option value="dokumen" {{ $filterBy == 'dokumen' ? 'selected' : '' }}>Tanggal Dokumen</option>
                            <option value="transaksi" {{ $filterBy == 'transaksi' ? 'selected' : '' }}>Tanggal Transaksi (BPB)</option>
                        </select>
                    </div>

                    <div class="col-md-4 text-right">
                        <button type="button" id="btn-export-excel" class="btn btn-success btn-sm text-white">
                            <i class="fas fa-file-excel mr-1"></i> Ekspor Excel
                        </button>
                    </div>
                </div>
            </form>

            {{-- Tanpa div.table-responsive: biar scrollX/scrollY DataTables yang handle scroll --}}
            <div class="report-table-wrapper">
                <table id="tabel-report" class="table table-bordered table-striped table-hover table-sm w-100">
                    <thead class="thead-report text-center">
                        <tr>
                            <th>No</th>
                            <th>Kode Kantor</th>
                            <th>Jenis Dokumen</th>
                            <th>Kategori Barang</th>
                            <th>Nomor Daftar</th>
                            <th>Tanggal Daftar</th>
                            <th>Nama Penerima / Pembeli</th>
                            <th>Nomor Bukti Pengeluaran</th>
                            <th>Tanggal Bukti Pengeluaran</th>
                            <th>ID Item</th>
                            <th>Uraian Barang</th>
                            <th>Jenis Satuan</th>
                            <th>Jumlah Satuan</th>
                            <th>Kode Valuta</th>
                            <th>Nilai Barang</th>
                            <th>Kurs</th>
                            <th>Nilai Barang IDR</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($data as $index => $row)
                            <tr>
                                <td class="text-center">{{ $index + 1 }}</td>
                                <td>{{ $row->kode_kantor ?? '-' }}</td>
                                <td>{{ $row->jenis_dokumen ?? '-' }}</td>
                                <td>{{ $row->kategori_barang ?? '-' }}</td>
                                <td>{{ $row->nomor_daftar ?? '-' }}</td>
                                <td>{{ $row->tanggal_daftar ? date('d-m-Y', strtotime($row->tanggal_daftar)) : '-' }}</td>
                                <td>{{ $row->nama_pengirim ?? '-' }}</td>
                                <td>{{ $row->nomor_bpb ?? '-' }}</td>
                                <td>{{ $row->tanggal_bpb ? date('d-m-Y', strtotime($row->tanggal_bpb)) : '-' }}</td>
                                <td>{{ $row->id_item ?? '-' }}</td>
                                <td>{{ $row->uraian_barang ?? '-' }}</td>
                                <td>{{ $row->jenis_satuan ?? '-' }}</td>
                                <td class="text-right">{{ number_format($row->jumlah_satuan ?? 0, 2) }}</td>
                                <td>{{ $row->kode_valuta ?? '-' }}</td>
                                <td class="text-right">{{ number_format($row->nilai_barang ?? 0, 2) }}</td>
                                <td class="text-right">{{ number_format($row->kurs ?? 0, 2) }}</td>
                                <td class="text-right">{{ number_format($row->nilai_barang_idr ?? 0, 2) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="17" class="text-center text-muted">Tidak ada data untuk laporan ini pada rentang tanggal tersebut.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

@section('custom-script')
    <script src="{{ asset('plugins/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-responsive/js/dataTables.responsive.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-responsive/js/responsive.bootstrap4.min.js') }}"></script>

    <script>
        $(document).ready(function() {
            let table = $('#tabel-report').DataTable({
                "responsive": false,
                "autoWidth": false,
                "scrollX": true,
                "scrollY": "60vh",
                "scrollCollapse": true,
                "lengthMenu": [[10, 25, 50, -1], [10, 25, 50, "All"]],
                "initComplete": function() {
                    let api = this.api();
                    setTimeout(function() {
                        api.columns.adjust();
                    }, 50);
                }
            });

            $(window).on('resize', function() {
                table.columns.adjust();
            });

            $('#btn-export-excel').on('click', function(e) {
                e.preventDefault();

                let btn = $(this);
                let originalText = btn.html();
                let form = btn.closest('form');
                let url = window.location.href.split('?')[0];
                let formData = form.serialize() + '&export=excel';

                $('#loading-bg').removeClass('d-none');

                btn.html('<i class="fas fa-spinner fa-spin mr-1"></i> Memproses...').prop('disabled', true);

                $.ajax({
                    url: url,
                    type: 'GET',
                    data: formData,
                    xhrFields: {
                        responseType: 'blob'
                    },
                    success: function(response, status, xhr) {
                        let filename = "Laporan_Excel.xls";
                        let disposition = xhr.getResponseHeader('Content-Disposition');
                        if (disposition && disposition.indexOf('attachment') !== -1) {
                            let matches = /filename[^;=\n]*=((['"]).*?\2|[^;\n]*)/.exec(disposition);
                            if (matches != null && matches[1]) {
                                filename = matches[1].replace(/['"]/g, '');
                            }
                        }

                        let blob = new Blob([response], { type: 'application/vnd.ms-excel' });
                        let downloadUrl = window.URL.createObjectURL(blob);
                        let a = document.createElement('a');
                        a.href = downloadUrl;
                        a.download = filename;
                        document.body.appendChild(a);
                        a.click();

                        a.remove();
                        window.URL.revokeObjectURL(downloadUrl);

                        $('#loading-bg').addClass('d-none');
                        btn.html(originalText).prop('disabled', false);
                    },
                    error: function() {
                        alert('Terjadi kesalahan saat mengunduh file.');

                        $('#loading-bg').addClass('d-none');
                        btn.html(originalText).prop('disabled', false);
                    }
                });
            });
        });

        function changeFilter(element) {
            $('#loading-bg').removeClass('d-none');

            $('<input>').attr({
                type: 'hidden',
                name: element.name,
                value: element.value
            }).appendTo(element.form);

            $(element).prop('disabled', true);

            element.form.submit();
        }
    </script>
    @push('scripts')
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            document.title = "Laporan {{ ucfirst($jenis) }} - {{ strtoupper(str_replace('-', ' ', $kategori)) }}";
        });
    </script>
    @endpush
@endsection
