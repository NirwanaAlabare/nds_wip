

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
            color: #5aa9f0;
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
            background: #5aa9f0;
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

        /* ============ TOOLBAR FILTER & EXPORT ============ */
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
           overflow TIDAK hidden di #tabel-report langsung, karena
           scrollX/scrollY DataTables butuh area sendiri buat scroll.
        ============================================================ */
        .report-table-wrapper {
            border: 1px solid #e7ecf3;
            border-radius: 14px;
            box-shadow: 0 2px 10px rgba(15, 23, 42, 0.04);
            overflow: hidden; /* aman, cuma buat radius luar */
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
            white-space: nowrap;
        }

        #tabel-report tbody td {
            font-size: 12.5px;
            vertical-align: middle;
            white-space: nowrap;
        }

        #tabel-report tbody tr:hover {
            background-color: #f4f8fd !important;
        }

        /* Paksa tabel head & body punya lebar identik biar kolom gak geser */
        .dataTables_scrollHeadTable,
        .dataTables_scrollBody table {
            width: 100% !important;
        }

        .dataTables_scrollBody {
            border-top: none !important;
        }

        /* Scrollbar custom biar area scroll kanan/bawah kelihatan jelas */
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

        .report-detail-card.pemasukan .card-eyebrow { color: #34d399; }
        .report-detail-card.pemasukan .card-title .periode-pill {
            background: #34d399;
            color: #0f172a;
        }

    </style>

    <div class="card report-detail-card pemasukan">

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
        </div>

        <div class="card-body">

            <form method="GET" action="" class="toolbar-panel">
                <input type="hidden" name="from" id="from" value="{{ $fromDate }}">
                <input type="hidden" name="to" id="to" value="{{ $toDate }}">
                <input type="hidden" name="jenis" id="jenis" value="{{ $jenis }}">
                <input type="hidden" name="kategori" id="kategori" value="{{ $kategori }}">
                <input type="hidden" name="kategoriBarang" id="kategoriBarang" value="{{ $kategoriBarang }}">

                <div class="row align-items-center">
                    {{-- <div class="col-md-8 d-flex align-items-center">
                        <label class="mr-3 mb-0" for="filter_by">Filter Berdasarkan:</label>
                        <select class="form-control form-control-sm w-auto select2bs4" id="filter_by" name="filter_by">
                            <option value="dokumen" {{ $filterBy == 'dokumen' ? 'selected' : '' }}>Tanggal Dokumen</option>
                            <option value="transaksi" {{ $filterBy == 'transaksi' ? 'selected' : '' }}>Tanggal Transaksi (BPB)</option>
                        </select>
                    </div> --}}

                    <div class="col-md-12 text-right">
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
                            <th>Nama Pengirim</th>
                            <th>Nomor BPB</th>
                            <th>Tanggal BPB</th>
                            <th>WS</th>
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
                        
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script src="{{ asset('plugins/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-responsive/js/dataTables.responsive.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-responsive/js/responsive.bootstrap4.min.js') }}"></script>

    <script>
       (function() {
            let table = $('#tabel-report').DataTable({
                "processing": true,
                "scrollX": true,
                "scrollY": "800px",     
                "scrollCollapse": true, 
                "paging": true,
                "ajax": {
                    url: `{{ route('get_pemasukan_data') }}`,
                    data: function(d) {
                        d.from = $('#from').val();
                        d.to = $('#to').val();
                        // d.filter_by = $('#filter_by').val();
                        d.kategoriBarang = $('#kategoriBarang').val();
                        d.kategori = $('#kategori').val();
                    }
                },
                "columns": [
                    { data: 'no' },
                    { data: 'kode_kantor' },
                    { data: 'jenis_dokumen' },
                    { data: 'kategori_barang' },
                    { data: 'nomor_daftar' },
                    { data: 'tanggal_daftar' },
                    { data: 'nama_pengirim' },
                    { data: 'nomor_bpb' },
                    { data: 'tanggal_bpb' }, // TERLEWAT SEBELUMNYA
                    { data: 'id_item' },
                    { data: 'uraian_barang' },
                    { data: 'jenis_satuan' },
                    { data: 'jumlah_satuan', className: 'text-right font-weight-bold' },
                    { data: 'kode_valuta' },
                    { data: 'nilai_barang', className: 'text-right font-weight-bold' },
                    { data: 'kurs', className: 'text-right font-weight-bold' },
                    { data: 'nilai_barang_idr', className: 'text-right font-weight-bold' },
                ]
            });

            $('#filter_by').on('change', function() {
                table.ajax.reload();
            });

            $(window).off('resize.reportTable').on('resize.reportTable', function() {
                table.columns.adjust();
            });

            $('.select2bs4').select2({ theme: 'bootstrap4', width: '100%' });

            $('#btn-export-excel').off('click').on('click', function(e) {
                Swal.fire({
                    title: 'Please Wait...',
                    html: 'Exporting Data...',
                    didOpen: () => { Swal.showLoading() },
                    allowOutsideClick: false,
                });

                $.ajax({
                    type: "get",
                    // Arahkan ke endpoint khusus export excel pemasukan
                    url: `{{ route('export_excel_pemasukan_bc') }}`, 
                    data: {
                        from: $('#from').val(),
                        to: $('#to').val(),
                        filter_by: $('#filter_by').val(),
                        jenis: $('#jenis').val(),
                        kategori: $('#kategori').val(),
                        kategoriBarang: $('#kategoriBarang').val(),
                    },
                    xhrFields: { responseType: 'blob' },
                    success: function(response) {
                        Swal.close();
                        Swal.fire({
                            title: 'Data Sudah Di Export!',
                            icon: "success",
                            showConfirmButton: true,
                            allowOutsideClick: false
                        });
                        var blob = new Blob([response]);
                        var link = document.createElement('a');
                        link.href = window.URL.createObjectURL(blob);
                        link.download = "Laporan Pemasukan " + $('#from').val() + " sampai " + $('#to').val() + ".xlsx";
                        link.click();
                    },
                    error: function() {
                        Swal.close();
                        Swal.fire('Error', 'Gagal mengeksport data Excel', 'error');
                    }
                });
            });
        })();
    </script>
