@extends('layouts.index', ['page' => 'dashboard-report-bc', 'containerFluid' => true])

@section('title', 'Laporan Mutasi Barang Jadi - ' . strtoupper($kategoriBarang == 'all' ? 'Semua Kategori' : $kategoriBarang))

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
            color: #8b5cf6;
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
            color: #fff;
            background: #8b5cf6;
            padding: 4px 11px;
            border-radius: 999px;
            vertical-align: middle;
            margin-left: 8px;
        }
        .report-detail-card .btn-back {
            border-radius: 8px;
            font-weight: 600;
            font-size: 0.8rem;
            background: rgba(255, 255, 255, 0.1);
            color: #f8fafc;
            border: 1px solid rgba(255, 255, 255, 0.25);
            transition: all 0.2s ease;
            white-space: nowrap;
        }
        .report-detail-card .btn-back:hover {
            background: rgba(255, 255, 255, 0.18);
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

        #tabel-report {
            border-radius: 10px;
            overflow: hidden;
        }
        #tabel-report thead th {
            background: #0f172a !important;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            font-weight: 700;
            vertical-align: middle;
            color: #f8fafc !important;
        }
        #tabel-report tbody td {
            font-size: 12.5px;
            vertical-align: middle;
        }
        #tabel-report tbody tr:hover {
            background-color: #f4f8fd !important;
        }

        .report-detail-card .card-header .card-tools {
            margin-left: auto;
        }
    </style>
@endsection

@section('content')
    <div class="card report-detail-card mutasi-fg">
        <div class="card-header">
            <div>
                <span class="card-eyebrow">Pabean · Report BC</span>
                <h3 class="card-title mb-0">
                    Laporan Mutasi Barang Jadi
                    <span class="periode-pill">
                        {{ \Carbon\Carbon::parse($fromDate)->format('d/m/Y') }} s/d {{ \Carbon\Carbon::parse($toDate)->format('d/m/Y') }}
                    </span>
                </h3>
            </div>
            <div class="card-tools" style="text-align: right;">
                <a href="{{ route('index-report-bc') }}" class="btn btn-sm btn-back">
                    <i class="fas fa-arrow-left mr-1"></i> Kembali ke Dashboard
                </a>
            </div>
        </div>

        <div class="card-body">
            <form method="GET" action="" class="toolbar-panel">
                <input type="hidden" name="from" value="{{ $fromDate }}">
                <input type="hidden" name="to" value="{{ $toDate }}">

                <div class="row align-items-center">
                    <div class="col-md-8 d-flex align-items-center">
                        <label class="mr-3 mb-0">Kategori Laporan:</label>
                        <span class="badge px-3 py-2" style="background: #e2e8f0; color: #334155; border-radius: 6px; font-weight: 700; font-size: 0.75rem;">
                            <i class="fas fa-tshirt mr-1"></i> {{ strtoupper($kategoriBarang == 'all' ? 'Semua Kategori' : $kategoriBarang) }}
                        </span>
                    </div>
                    <div class="col-md-4 text-right">
                        <button type="button" id="btn-export-excel" class="btn btn-success btn-sm text-white">
                            <i class="fas fa-file-excel mr-1"></i> Ekspor Excel
                        </button>
                    </div>
                </div>
            </form>

            <div class="table-responsive">
                <table id="tabel-report" class="table table-bordered table-striped table-hover table-sm w-100 text-nowrap">
                    <thead class="text-center">
                        <tr>
                            <th width="5%">No</th>
                            <th>Id So Det</th>
                            <th>Kode Barang</th>
                            <th>Style</th>
                            <th>No WS</th>
                            <th>Color</th>
                            <th>Size</th>
                            <th>Dest / Country</th>
                            <th>Unit</th>
                            <th>Saldo Awal</th>
                            <th>Penerimaan</th>
                            <th>Pengeluaran</th>
                            <th>Saldo Akhir</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if(count($data) > 0)
                            @foreach ($data as $index => $row)
                                <tr>
                                    <td class="text-center">{{ $index + 1 }}</td>
                                    <td>{{ $row->id_so_det ?? '-' }}</td>
                                    <td>{{ $row->goods_code ?? '-' }}</td>
                                    <td>{{ $row->styleno ?? '-' }}</td>
                                    <td>{{ $row->kpno ?? '-' }}</td>
                                    <td>{{ $row->color ?? '-' }}</td>
                                    <td>{{ $row->size ?? '-' }}</td>
                                    <td>{{ $row->country ?? '-' }}</td>
                                    <td class="text-center">PCS</td>

                                    <td class="text-right text-info font-weight-bold">{{ number_format($row->saldoawal ?? 0, 2) }}</td>
                                    <td class="text-right text-success">{{ number_format($row->qtyterima ?? 0, 2) }}</td>
                                    <td class="text-right text-danger">{{ number_format($row->qtykeluar ?? 0, 2) }}</td>
                                    <td class="text-right text-primary font-weight-bold">{{ number_format($row->saldoakhir ?? 0, 2) }}</td>
                                </tr>
                            @endforeach
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

@section('custom-script')
    <script src="{{ asset('plugins/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-bs4/js/dataTables.bootstrap4.min.js') }}"></script>

    <script>
        $(document).ready(function() {
            $('#tabel-report').DataTable({
                "responsive": false,
                "autoWidth": false,
                "scrollX": true,
                "lengthMenu": [[10, 25, 50, -1], [10, 25, 50, "All"]],
                "language": { "emptyTable": "Tidak ada data mutasi barang jadi untuk periode ini." }
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
                    url: url, type: 'GET', data: formData,
                    xhrFields: { responseType: 'blob' },
                    success: function(response, status, xhr) {
                        let filename = "Laporan_Mutasi_Barang_Jadi.xls";
                        let disposition = xhr.getResponseHeader('Content-Disposition');
                        if (disposition && disposition.indexOf('attachment') !== -1) {
                            let matches = /filename[^;=\n]*=((['"]).*?\2|[^;\n]*)/.exec(disposition);
                            if (matches != null && matches[1]) filename = matches[1].replace(/['"]/g, '');
                        }

                        let blob = new Blob([response], { type: 'application/vnd.ms-excel' });
                        let downloadUrl = window.URL.createObjectURL(blob);
                        let a = document.createElement('a');
                        a.href = downloadUrl; a.download = filename;
                        document.body.appendChild(a); a.click(); a.remove();
                        window.URL.revokeObjectURL(downloadUrl);

                        $('#loading-bg').addClass('d-none');
                        btn.html(originalText).prop('disabled', false);
                    },
                    error: function() {
                        alert('Terjadi kesalahan saat mengunduh file Excel.');
                        $('#loading-bg').addClass('d-none');
                        btn.html(originalText).prop('disabled', false);
                    }
                });
            });
        });
    </script>
@endsection
