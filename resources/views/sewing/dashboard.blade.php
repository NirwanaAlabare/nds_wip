<h5 class="fw-bold mb-3">Dashboard</h5>

<div class="card oh position-relative">
    <div class="loading-container position-absolute w-100 h-100 d-none" style="background: #7777771c;z-index: 1;" id="loading-sewing-chart">
        <div class="loading"></div>
    </div>
    <div class="card-header">
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
                <span class="text-sb-secondary fw-bold">Total Worksheet</span>
            </div>
            <div class="col-lg-4 col-md-4 m-t-20">
                <h2 class="m-b-0 font-light" id="sewing-total-output">0</h2>
                <span class="text-sb-secondary fw-bold">Total Output</span>
            </div>
            <div class="col-lg-4 col-md-4 m-t-20">
                <h2 class="m-b-0 font-light" id="sewing-total-efficiency">0</h2>
                <span class="text-sb-secondary fw-bold">Total Efficiency</span>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <div class="d-flex justify-content-between align-items-center no-block">
            <h5 class="card-title text-sb fw-bold">Sewing Output</h5>
            <div class="ml-auto">
                <div class="d-flex justify-content-end align-items-center gap-1">
                    <div class="mb-1">
                        <select class="form-select form-select-sm select2bs4" id="sewing-output-month-filter" readonly>
                            <option value="" selected disabled>Bulan</option>
                            @foreach ($months as $month)
                                <option value="{{ $month['angka'] }}">{{ $month['nama'] }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-1">
                        <select class="form-select form-select-sm select2bs4" id="sewing-output-year-filter" readonly>
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
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-sm" id="datatable-sewing-output">
                <thead>
                    <tr>
                        <th>Tanggal Order</th>
                        <th>Buyer</th>
                        <th>No. WS</th>
                        <th>No. Style</th>
                        <th>Color</th>
                        <th>Size</th>
                        <th>Qty</th>
                        <th>Output</th>
                        <th>Balance</th>
                        <th>Output Packing</th>
                        <th>Balance Packing</th>
                        <th>Rft Rate</th>
                        <th>Defect Rate</th>
                        <th>Tanggal Delivery</th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
        </div>
    </div>
</div>
