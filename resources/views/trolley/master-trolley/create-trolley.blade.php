@extends('layouts.index')

@section('custom-link')
    <!-- DataTables -->
    <link rel="stylesheet" href="{{ asset('plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/datatables-responsive/css/responsive.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/datatables-buttons/css/buttons.bootstrap4.min.css') }}">
    <!-- Select2 -->
    <link rel="stylesheet" href="{{ asset('plugins/select2/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}">
@endsection

@section('content')
    <div class="card card-sb">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="card-title fw-bold mb-0">Tambah Trolley Baru</h5>
                <a href="{{ route('trolley') }}" class="btn btn-sm btn-primary">
                    <i class="fa fa-reply"></i> Kembali ke Master Trolley
                </a>
            </div>
        </div>
        <div class="card-body">
            <form action="{{ route('store-trolley') }}" method="post" id="trolley-form" onsubmit="submitForm(this, event)">
                <div class="row">
                    <div class="col mb-3">
                        <label class="form-label">Line</label>
                        <select class="form-select select2bs4" name="line_id" id="line_id">
                            <option value="" data-username="" selected disabled>Pilih Line</option>
                            @foreach ($lines as $line)
                                <option value="{{ $line->line_id }}">{{ strtoupper(str_replace('_', ' ', $line->username)) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col mb-3">
                        <label class="form-label">Trolley</label>
                        <input type="text" class="form-control" name="nama_trolley" id="nama_trolley" value="" onchange="buildTrolleyTable()" onkeyup="buildTrolleyTable()">
                    </div>
                    <div class="col mb-3">
                        <label class="form-label">Jumlah</label>
                        <input type="number" class="form-control" name="latest_trolley" id="latest_trolley" value="">
                        <input type="number" class="form-control" name="jumlah" id="jumlah" value="" onchange="buildTrolleyTable()" onkeyup="buildTrolleyTable()">
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-bordered" id="trolley-table">
                        <tbody>
                        </tbody>
                    </table>
                </div>
                <button class="btn btn-block btn-success" type="submit">SIMPAN</button>
            </form>
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

        $('document').ready(() => {
            $('#line_id').val('').trigger('change');

            clearTrolleyTable();
        });

        $('#line_id').on('change', () => {
            if ($("#line_id option:selected").text() != "Pilih Line") {
                $('#nama_trolley').val($("#line_id option:selected").text());
                $('#latest_trolley').val(0);
                $('#jumlah').val(0);
            } else {
                $('#nama_trolley').val("");
            }

            getLatestTrolley($('#line_id').val());

            buildTrolleyTable();
        });

        function getLatestTrolley(id) {
            $.ajax({
                url: '{{ route('create-trolley') }}',
                type: 'get',
                data: {
                    id:id
                },
                success: function(res) {
                    if (res) {
                        console.log(res);

                        document.getElementById('latest_trolley').value = res.nama_trolley.substring(8);

                        console.log(document.getElementById('latest_trolley').value);
                    }
                }
            });
        }

        function buildTrolleyTable() {
            let trolleyName = document.getElementById('nama_trolley').value;
            let trolleyAmount = Number(document.getElementById('latest_trolley').value) + Number(document.getElementById('jumlah').value);

            let trolleyTable = document.getElementById('trolley-table');
            let trolleyTableTbody = trolleyTable.getElementsByTagName("tbody")[0];

            trolleyTableTbody.innerHTML = "";

            if (document.getElementById('jumlah').value > 0 && trolleyName != '') {
                for (let i = Number(document.getElementById('latest_trolley').value); i < trolleyAmount; i++) {
                    let tr1 = document.createElement('tr');

                    let td1 = document.createElement('td');

                    td1.innerHTML = trolleyName+'.'+(i+1);

                    tr1.appendChild(td1);

                    trolleyTableTbody.appendChild(tr1);
                }
            }
        }

        function clearTrolleyTable() {
            let trolleyTable = document.getElementById('trolley-table');
            let trolleyTableTbody = trolleyTable.getElementsByTagName("tbody")[0];

            $('#line_id').val('').trigger('change');
            $('#nama_trolley').val('');
            $('#jumlah').val('');
            trolleyTableTbody.innerHTML = "";
        }
    </script>
@endsection
