<h5 class="fw-bold mb-3">Dashboard</h5>

<div class="card">
    <div class="card-body">
        <div class="d-flex justify-content-between">
            <h5 class="text-sb fw-bold">Form Count</h5>
            <div class="d-flex justify-content-end gap-1 mb-3">
                {{-- <select class="form-select form-select-sm select2bs4 w-auto" id="cuttingqty-month-filter" readonly value="{{ date('m') }}">
                    <option value="" selected disabled>Bulan</option>
                    @foreach ($months as $month)
                        <option value="{{ $month['angka'] }}">{{ $month['nama'] }}</option>
                    @endforeach
                </select>
                <select class="form-select form-select-sm select2bs4 w-auto" id="cuttingqty-year-filter" readonly value="{{ date('Y') }}">
                    <option value="" selected disabled>Tahun</option>
                    @foreach ($years as $year)
                        <option value="{{ $year }}">{{ $year }}</option>
                    @endforeach
                </select> --}}
                <div class="d-flex justify-content-end gap-1 mb-3">
                    <div class="mb-1">
                        <input type="date" class='form-control' id='cuttingqty-date-filter' value="{{ date('Y-m-d') }}">
                    </div>
                </div>
            </div>
        </div>
        <div class="d-none mb-3" id="loading-cutting-qty">
            <div class="loading-container">
                <div class="loading"></div>
            </div>
        </div>
        <div class="row d-none" id="cutting-qty-data">
            <div class="col-6 col-md-3">
                <div class="info-box">
                    <span class="info-box-icon bg-sb"><i class="fa-regular fa-hourglass-half"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Pending Form</span>
                        <span class="info-box-number" id="pending-qty"></span>
                        <span class="info-box-number"><small>Total Lembar : <span id="pending-total"></span></small></span>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="info-box">
                    <span class="info-box-icon bg-sb-secondary"><i class="fa-solid fa-sliders"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Plan Form</span>
                        <span class="info-box-number" id="plan-qty"></span>
                        <span class="info-box-number"><small>Total Lembar : <span id="plan-total"></span></small></span>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="info-box">
                    <span class="info-box-icon bg-sb"><i class="fa-solid fa-arrows-rotate"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Progress Form</span>
                        <span class="info-box-number" id="progress-qty"></span>
                        <span class="info-box-number"><small>Total Lembar : <span id="progress-total"></span></small></span>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="info-box">
                    <span class="info-box-icon bg-sb-secondary"><i class="fa-solid fa-check-to-slot"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Finished Form</span>
                        <span class="info-box-number" id="finished-qty"></span>
                        <span class="info-box-number"><small>Total Lembar : <span id="finished-total"></span></small></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="card">
    <div class="card-body">
        <div class="d-flex justify-content-between">
            <h5 class="text-sb fw-bold">Cutting Form Chart</h5>
            <div class="d-flex justify-content-start align-items-center gap-1 mb-3">
                <div class="mb-1">
                    <input type="date" class='form-control' id='cutting-form-date-filter' value="{{ date('Y-m-d') }}">
                </div>
            </div>
        </div>
        <div class="d-none mb-3" id="loading-cutting-form">
            <div class="loading-container">
                <div class="loading"></div>
            </div>
        </div>
        <div class="row d-none" id="cutting-form-data">
            <div id="cutting-form-chart" style="height: 350px;"></div>
        </div>
    </div>
</div>
<div class="card">
    <div class="card-body">
        <div class="d-flex justify-content-between">
            <h5 class="text-sb fw-bold">Cutting Summary</h5>
            <div class="d-flex justify-content-start align-items-center gap-1 mb-3">
                {{-- <div class="mb-1">
                    <select class="form-select form-select-sm select2bs4" id="cutting-month-filter" readonly value="{{ date('m') }}">
                        <option value="" selected disabled>Bulan</option>
                        @foreach ($months as $month)
                            <option value="{{ $month['angka'] }}">{{ $month['nama'] }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-1">
                    <select class="form-select form-select-sm select2bs4" id="cutting-year-filter" readonly value="{{ date('Y') }}">
                        <option value="" selected disabled>Tahun</option>
                        @foreach ($years as $year)
                            <option value="{{ $year }}">{{ $year }}</option>
                        @endforeach
                    </select>
                </div> --}}
                <div class="d-flex justify-content-end gap-1 mb-3">
                    <div class="mb-1">
                        <input type="date" class='form-control' id='cutting-date-filter' value="{{ date('Y-m-d') }}">
                    </div>
                </div>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-bordered table-hover table-sm w-100" id="datatable-cutting">
                <thead>
                    <tr>
                        <th>No. WS</th>
                        <th>Style</th>
                        <th>Total Order</th>
                        <th>Total Lembar</th>
                        <th>Tanggal</th>
                        <th>Total Plan</th>
                        <th>Total Complete</th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
        </div>
    </div>
</div>
