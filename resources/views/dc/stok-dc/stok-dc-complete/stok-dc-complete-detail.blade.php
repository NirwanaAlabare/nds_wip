@extends('layouts.index')

@section('custom-link')
    <!-- DataTables -->
    <link rel="stylesheet" href="{{ asset('plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/datatables-responsive/css/responsive.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/datatables-buttons/css/buttons.bootstrap4.min.css') }}">
@endsection

@section('content')
    {{-- Complete Stocker Data --}}
    <div class="card">
        <div class="card-header bg-sb text-light">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="card-title fw-bold mb-0"><i class="fa-solid fa-circle-info"></i> Detail Stock Complete</h5>
                <a href="{{ route('stock-dc-complete') }}" class="btn btn-sm btn-primary">
                    <i class="fa fa-reply"></i> Kembali ke Stok DC Complete
                </a>
            </div>
        </div>
        <div class="card-body">
            <div class="row align-items-end g-1 mb-3">
                <input type="hidden" class="form-control form-control-sm" id="part_id" name="part_id" value="{{ $stockDcComplete[0]->part_id }}">
                <div class="col-6 col-md-3">
                    <label class="form-label"><small>No. WS</small></label>
                    <input type="text" class="form-control form-control-sm" id="no_ws" name="no_ws" value="{{ $stockDcComplete[0]->act_costing_ws }}" readonly>
                </div>
                <div class="col-6 col-md-3">
                    <label class="form-label"><small>Buyer</small></label>
                    <input type="text" class="form-control form-control-sm" id="buyer" name="buyer" value="{{ $stockDcComplete[0]->buyer }}" readonly>
                </div>
                <div class="col-6 col-md-3">
                    <label class="form-label"><small>Style</small></label>
                    <input type="text" class="form-control form-control-sm" id="style" name="style" value="{{ $stockDcComplete[0]->style }}" readonly>
                </div>
                <div class="col-6 col-md-3">
                    <label class="form-label"><small>Color</small></label>
                    <input type="text" class="form-control form-control-sm" id="color" name="color" value="{{ $stockDcComplete[0]->color }}" readonly>
                </div>
                <div class="col-6 col-md-3">
                    <label class="form-label"><small>Size</small></label>
                    <input type="text" class="form-control form-control-sm" id="size" name="size" value="{{ $stockDcComplete[0]->size }}" readonly>
                </div>
                <div class="col-6 col-md-3">
                    <label class="form-label"><small>Jumlah Bundle</small></label>
                    <input type="text" class="form-control form-control-sm" id="jumlah_bundle" name="jumlah_bundle" value="{{ count($stockDcComplete) }}" readonly>
                </div>
                @php
                    $stockDcCompleteSumQty = array_reduce(
                        $stockDcComplete,
                        function($carry, $item)
                        {
                            return $carry + $item->qty;
                        }
                    );
                @endphp
                <div class="col-6 col-md-3">
                    <label class="form-label"><small>Jumlah Qty</small></label>
                    <input type="text" class="form-control form-control-sm" id="jumlah_qty" name="jumlah_qty" value="{{ $stockDcCompleteSumQty }}" readonly>
                </div>
                <div class="col-6 col-md-3">
                    <label class="form-label"><small></small></label>
                    <button class="btn btn-sb-secondary btn-block btn-sm" onclick="reorderStockerNumbering()"><i class="fa-solid fa-arrow-up-wide-short"></i> Reorder Stocker Numbering</button>
                </div>
            </div>
            <div class="table-responsive">
                <table id="datatable-complete-stocker" class="table table-bordered table w-100">
                    <thead>
                        <tr>
                            <th>No. Cut</th>
                            <th>Group</th>
                            <th>Range Awal</th>
                            <th>Range Akhir</th>
                            <th>Qty</th>
                            <th>Lokasi</th>
                            <th>No. Stocker</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($stockDcComplete as $stock)
                            <tr>
                                <td>{{ $stock->no_cut }}</td>
                                <td>{{ $stock->shade }}</td>
                                <td>{{ $stock->range_awal }}</td>
                                <td>{{ $stock->range_akhir }}</td>
                                <td>{{ $stock->qty }}</td>
                                <td>{{ $stock->lokasi }}</td>
                                <td>{{ $stock->stockers }}</td>
                            </tr>
                        @endforeach
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

    <script>
        // Initial Function
        document.addEventListener("DOMContentLoaded", () => {
            // Set Filter to 1 Week Ago
            let oneWeeksBefore = new Date(new Date().setDate(new Date().getDate() - 7));
            let oneWeeksBeforeDate = ("0" + oneWeeksBefore.getDate()).slice(-2);
            let oneWeeksBeforeMonth = ("0" + (oneWeeksBefore.getMonth() + 1)).slice(-2);
            let oneWeeksBeforeYear = oneWeeksBefore.getFullYear();
            let oneWeeksBeforeFull = oneWeeksBeforeYear + '-' + oneWeeksBeforeMonth + '-' + oneWeeksBeforeDate;

            $("#tgl-awal").val(oneWeeksBeforeFull).trigger("change");
        });

        // Complete Stocker Datatable
        let datatableCompleteStocker = $("#datatable-complete-stocker").DataTable({
            ordering: false,
            columnDefs: [
                {
                    // All Column Colorization
                    targets: [0],
                    className: '',
                    render: function (data, type, row, meta) {
                        return `
                            <div class='overflow-auto'>
                                `+data+`
                            </div>
                        `;
                    }
                },
            ],
        });

        // Complete Stocker Datatable Reload
        function datatableCompleteStockerReload() {
            datatableCompleteStocker.ajax.reload()
        }

        // Complete Stocker Datatable Header Column Filter
        $('#datatable-complete-stocker thead tr').clone(true).appendTo('#datatable-complete-stocker thead');
        $('#datatable-complete-stocker thead tr:eq(1) th').each(function(i) {
            if (i == 1 || i == 2 || i == 3 || i == 4 || i == 5 || i == 6 || i == 7 || i == 8) {
                var title = $(this).text();
                $(this).html('<input type="text" class="form-control form-control-sm" />');

                $('input', this).on('keyup change', function() {
                    if (datatableCompleteStocker.column(i).search() !== this.value) {
                        datatableCompleteStocker
                            .column(i)
                            .search(this.value)
                            .draw();
                    }
                });
            } else {
                $(this).empty();
            }
        });

        // Reorder Stocker & Numbering
        function reorderStockerNumbering() {
            Swal.fire({
                title: 'Please Wait...',
                html: 'Reordering Data...',
                didOpen: () => {
                    Swal.showLoading()
                },
                allowOutsideClick: false,
            });

            $.ajax({
                url: '{{ route('reorder-stocker-numbering') }}',
                type: 'post',
                data: {
                    id : $("#part_id").val()
                },
                success: function (res) {
                    console.log(res);

                    swal.close();
                },
                error: function (jqXHR) {
                    console.log(jqXHR);
                }
            });
        }
    </script>
@endsection
