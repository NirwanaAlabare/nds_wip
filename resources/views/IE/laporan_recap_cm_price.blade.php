@extends('layouts.index')

@section('custom-link')
    {{-- <!-- DataTables -->
    <link rel="stylesheet" href="{{ asset('plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/datatables-responsive/css/responsive.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/datatables-buttons/css/buttons.bootstrap4.min.css') }}"> --}}

    <!-- DataTables CSS -->
    <link rel="stylesheet" href="{{ asset('plugins/datatables 2.0/jquery.dataTables.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/datatables 2.0/dataTables.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/datatables 2.0/fixedColumns.bootstrap4.min.css') }}">
    <!-- jQuery -->
    <script src="{{ asset('plugins/datatables 2.0/jquery-3.3.1.js') }}"></script>


    <!-- Select2 -->
    <link rel="stylesheet" href="{{ asset('plugins/select2/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}">
@endsection

@section('content')
    <form>
        <div class="card card-sb">
            <div class="card-header">
                <h5 class="card-title fw-bold mb-0"><i class="fas fa-list"></i> Recap CM Price</h5>
            </div>

            <div class="card-body">
                <div class="table-responsive">
                    <table id="myTable" class="table table-bordered text-nowrap" style="width:100%">
                        <thead class="bg-sb">
                            <tr>
                                <th class="text-center align-middle">Buyer</th>
                                <th class="text-center align-middle">WS</th>
                                <th class="text-center align-middle">Styleno</th>
                                <th class="text-center align-middle">Price</th>
                                <th class="text-center align-middle">Number of Changes</th>

                                @php
                                    $maxDetails = 0;
                                    foreach ($groupedData as $ws) {
                                        $maxDetails = max($maxDetails, count($ws['details']));
                                    }
                                @endphp

                                @for ($i = 0; $i < $maxDetails; $i++)
                                    <th class="text-center">Date of Update</th>
                                    <th class="text-center">Price</th>
                                @endfor
                            </tr>
                        </thead>

                        <tbody>
                            @php
                                $colors = ['#fff3cd', '#d1ecf1', '#f8d7da', '#d4edda']; // kuning, biru, merah muda, hijau muda
                                $colorCount = count($colors);
                            @endphp

                            @foreach ($groupedData as $ws)
                                <tr>
                                    <td>{{ $ws['buyer'] }}</td>
                                    <td>{{ $ws['kpno'] }}</td>
                                    <td>{{ $ws['styleno'] }}</td>
                                    <td>{{ $ws['price_act'] }}</td>
                                    <td class="text-center">{{ $ws['total_changes'] }}</td>

                                    @foreach ($ws['details'] as $index => $detail)
                                        @php
                                            $hasValue =
                                                !empty($detail['tgl_upd_fix']) || !empty($detail['price_act_upd']);
                                            $bgColor = $hasValue ? $colors[$index % $colorCount] : '';
                                        @endphp

                                        <td class="text-center"
                                            @if ($bgColor) style="background-color: {{ $bgColor }}" @endif>
                                            {{ $detail['tgl_upd_fix'] ?? '-' }}
                                        </td>
                                        <td class="text-center"
                                            @if ($bgColor) style="background-color: {{ $bgColor }}" @endif>
                                            {{ $detail['price_act_upd'] ?? '-' }}
                                        </td>
                                    @endforeach

                                    {{-- tambahkan sel kosong agar kolom tetap balance --}}
                                    @php
                                        $missing = $maxDetails - count($ws['details']);
                                    @endphp
                                    @for ($i = 0; $i < $missing; $i++)
                                        <td class="text-center">-</td>
                                        <td class="text-center">-</td>
                                    @endfor
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>


    </form>
    </div>
@endsection

@section('custom-script')
    <!-- DataTables & Plugins -->
    {{-- <script src="{{ asset('plugins/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-responsive/js/dataTables.responsive.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-responsive/js/responsive.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('plugins/select2/js/select2.full.min.js') }}"></script> --}}
    <script src="{{ asset('plugins/select2/js/select2.full.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables 2.0/dataTables.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables 2.0/dataTables.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables 2.0/dataTables.fixedColumns.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-rowsgroup/dataTables.rowsGroup.js') }}"></script>
    <script>
        // Select2 Autofocus
        $(document).on('select2:open', () => {
            document.querySelector('.select2-search__field').focus();
        });

        // Initialize Select2 Elements
        $('.select2').select2();

        // Initialize Select2BS4 Elements
        $('.select2bs4').select2({
            theme: 'bootstrap4',
            width: 'resolve' // Ensures it respects the 100% width from inline style or Bootstrap
        });
        // Now set height and font-size on the Select2 container after init
        $('.select2-container--bootstrap4 .select2-selection--single').css({
            'height': '30px', // your desired height
            'font-size': '12px', // your desired font size
            'line-height': '30px' // vertically center text
        });

        function notif() {
            alert("Maaf, Fitur belum tersedia!");
        }
    </script>

    <script>
        $(document).ready(function() {
            var table = $('#myTable').DataTable({
                ordering: true,
                responsive: true,
                processing: true,
                serverSide: false,
                paging: true,
                searching: true,
                scrollY: "800px",
                scrollX: true,
                scrollCollapse: true,

                // Ubah default page length menjadi 25
                pageLength: 15,

                // Menentukan pilihan dropdown untuk jumlah row yang ditampilkan
                lengthMenu: [
                    [15, 50, 100, -1],
                    [15, 50, 100, "All"]
                ],

                initComplete: function() {
                    let api = this.api();

                    setTimeout(function() {
                        api.columns.adjust().draw(false);
                    }, 100);
                }
            });
        });
    </script>
@endsection
