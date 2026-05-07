@extends('layouts.index')

@section('custom-link')
    <style>
        body { background-color: #f4f6f9; }
        .card { transition: transform 0.2s; }
        .card:hover { transform: translateY(-5px); }
        .bg-warning-light { background-color: #fff3cd; }
    </style>
    <link rel="stylesheet" href="{{ asset('plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/datatables-responsive/css/responsive.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/datatables-buttons/css/buttons.bootstrap4.min.css') }}">


    <link rel="stylesheet" href="{{ asset('plugins/select2/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}">
@endsection

@section('content')

    <div class="container-fluid mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="text-danger font-weight-bold">
                <i class="fas fa-exclamation-triangle"></i> Alert Shipment
            </h4>
            <span class="badge badge-danger" style="font-size: 14px;">Hari ini: {{ date('d M Y') }}</span>
        </div>

        <div class="row">
            @forelse($data ?? [] as $item)
                <div class="col-md-4 mb-4">
                    <div class="card border-warning shadow-sm h-100">
                        <div class="card-header font-weight-bold d-flex align-items-center" style="background-color: #081e3f; color: white;">
                            <span style="font-size: 20px;">PO: {{ $item->po }}</span>
                            <i class="fas fa-ship ml-auto"></i>
                        </div>
                        <div class="card-body" style="background-color:white;">
                            <h5 class="card-title font-weight-bold text-dark w-100">{{ $item->desc }}</h5>
                            <h6 class="card-subtitle mb-3 "><i class="fas fa-map-marker-alt"></i> Dest: {{ $item->dest }}</h6>

                            <div class="mb-2">
                                <small class="d-block">Tanggal Shipment:</small>
                                <span style="font-size: 1.25rem;" class="font-weight-bold text-danger">{{ date('d M Y', strtotime($item->tgl_shipment)) }}</span>
                            </div>

                            <div class="mb-3">
                                <small class="d-block">Total QTY PO:</small>
                                <span style="font-size: 1.25rem;" class="font-weight-bold">{{ number_format($item->total_qty, 0, ',', '.') }} PCS</span>
                            </div>
                        </div>
                        <div class="card-footer bg-white border-top-0 pb-3 text-center">
                            <button class="btn btn-outline-danger w-100 font-weight-bold" onclick="cekDetail('{{ $item->po }}', '{{ $item->desc }}', '{{ $item->total_qty }}')">
                                <i class="fas fa-search"></i> Cek Detail
                            </button>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12">
                    <div class="alert alert-success shadow-sm">
                        <i class="fas fa-check-circle"></i> Belum ada PO yang mendekati Shipment.
                    </div>
                </div>
            @endforelse
        </div>
    </div>
    <div class="modal fade" id="detailModal" tabindex="-1" role="dialog" aria-labelledby="detailModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
          <div class="modal-header bg-sb text-white">
            <h5 class="modal-title" id="detailModalLabel">Rincian PO</h5>
            <button type="button" class="close text-white" data-bs-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="modal-body">
            <div class="row mb-3">
                <div class="col-md-6">
                    <strong>Item Desc:</strong> <span id="modalDesc">-</span>
                </div>
                <div class="col-md-6 text-md-right">
                    <strong>Total QTY:</strong> <span id="modalTotal">-</span>
                </div>
            </div>
            <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                <table class="table table-bordered table-striped table-sm">
                    <thead class="thead-light">
                        <tr>
                            <th class="text-center">No</th>
                            <th>WS</th>
                            <th>Color</th>
                            <th>Size</th>
                            <th class="text-center">QTY</th>
                        </tr>
                    </thead>
                    <tbody id="detailTableBody">
                    </tbody>
                </table>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
          </div>
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
        function cekDetail(po, desc, total) {
            $('#detailModalLabel').text('Rincian PO: ' + po);
            $('#modalDesc').text(desc);
            $('#modalTotal').text(total + ' PCS');
            $('#detailTableBody').html('<tr><td colspan="4" class="text-center"><div class="spinner-border text-primary spinner-border-sm" role="status"></div> Memuat data...</td></tr>');
            $('#detailModal').modal('show');
            $.ajax({
                url: '{{ url("/export-import/alert-detail") }}/' + po,
                type: 'GET',
                success: function(response) {
                    let html = '';
                    if(response.data && response.data.length > 0) {
                        $.each(response.data, function(index, item) {
                            html += `<tr>
                                <td class="text-center">${index + 1}</td>
                                <td>${item.kpno}</td>
                                <td>${item.color}</td>
                                <td>${item.size}</td>
                                <td class="text-center">${item.qty_po}</td>
                            </tr>`;
                        });
                    } else {
                        html = '<tr><td colspan="4" class="text-center">Data rincian tidak ditemukan.</td></tr>';
                    }
                    $('#detailTableBody').html(html);
                },
                error: function(xhr) {
                    $('#detailTableBody').html('<tr><td colspan="4" class="text-center text-danger">Terjadi kesalahan saat memuat data.</td></tr>');
                    console.error(xhr.responseText);
                }
            });
        }
    </script>
@endsection
