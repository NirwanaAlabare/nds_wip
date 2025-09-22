@extends('layouts.index')

@section('custom-link')
    <!-- Select2 -->
    <link rel="stylesheet" href="{{ asset('plugins/select2/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}">
@endsection

@section('content')
    <div class="container-fluid">
        <div class="card">
            <div class="mx-3 my-3">
                <h5 class="card-title fw-bold text-sb text-center"><i class="fa-solid fa-circle-exclamation"></i> Summary Output</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col">
                        <label class="form-label">Dari</label>
                        <input type="date" class="form-control" name="date_from" id="date_from">
                    </div>
                    <div class="col">
                        <label class="form-label">Sampai</label>
                        <input type="date" class="form-control" name="date_to" id="date_to">
                    </div>
                    <div class="col-12">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-check">
                                    <input class="form-check-input" value="monthly" type="radio" name="period" id="monthly">
                                    <label class="form-check-label" for="monthly">
                                        Monthly
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-check">
                                    <input class="form-check-input" value="weekly" type="radio" name="period" id="weekly">
                                    <label class="form-check-label" for="weekly">
                                        Weekly
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-12">
                        <h5>Group By</h5>
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="line_group">
                                    <label class="form-check-label" for="line_group">
                                        Line
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-bordered" id="summary-output-table">
                        <thead>
                            <th>Period</th>
                            <th>Rft</th>
                            <th>Defect</th>
                            <th>Rework</th>
                            <th>Reject</th>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('custom-script')
    <script src="{{ asset('plugins/select2/js/select2.full.min.js') }}"></script>
    <script>
        $('.select2').select2({
            theme: 'bootstrap4',
        })

        $('.select2bs4').select2({
            theme: 'bootstrap4',
        })

        let summaryOutputTable = $("#summary-output-table").DataTable({
            ordering: false,
            processing: true,
            serverSide: false,
            ajax: {
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: '{{ route('summary-output') }}',
                dataType: 'json',
                dataSrc: 'data',
                scrollY: '400px',
                data: function(d) {
                    d.date_from = $('#date_from').val();
                    d.date_to = $('#date_to').val();
                    d.grouping = $('#line_group').is(':checked') ? true : null;
                    d.period = $("input[name='period']:checked").val();
                },
            },
            columns: [
                {
                    data: "period"
                },
                {
                    data: "rft"
                },
                {
                    data: "defect"
                },
                {
                    data: "reject"
                },
                {
                    data: "rework"
                },
            ]
        });
    </script>
@endsection
