<h5 class="fw-bold mb-3">Dashboard</h5>

<div class="card oh position-relative">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center no-block">
            <h5 class="card-title text-sb fw-bold">Sewing Efficiency</h5>
            <div class="ml-auto">
                <div class="d-flex justify-content-end align-items-center gap-1">
                    <div class="mb-1">
                        <select class="form-select form-select-sm select2bs4" id="sewing-eff-month-filter" readonly>
                            <option value="" selected disabled>Bulan</option>
                            @foreach ($months as $month)
                                <option value="{{ $month['angka'] }}">{{ $month['nama'] }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-1">
                        <select class="form-select form-select-sm select2bs4" id="sewing-eff-year-filter" readonly>
                            <option value="" selected disabled>Tahun</option>
                            @foreach ($years as $year)
                                <option value="{{ $year }}">{{ $year }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="card-body bg-light">
        <div id="sewing-eff-chart" style="height: 350px;"></div>
    </div>
    <div class="card-body">
        <div class="row text-center m-b-20">
            <div class="col-lg-4 col-md-4 m-t-20">
                <h2 class="m-b-0 font-light" id="sewing-total-order">0</h2>
                <span class="text-muted">Total Worksheet</span>
            </div>
            <div class="col-lg-4 col-md-4 m-t-20">
                <h2 class="m-b-0 font-light" id="sewing-total-output">0</h2>
                <span class="text-muted">Total Output</span>
            </div>
            <div class="col-lg-4 col-md-4 m-t-20">
                <h2 class="m-b-0 font-light" id="sewing-total-efficiency">0</h2>
                <span class="text-muted">Total Efficiency</span>
            </div>
        </div>
    </div>
</div>
