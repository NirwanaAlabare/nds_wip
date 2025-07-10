@extends('layouts.index', ['navbar' => false, 'footer' => false])

@section('content')

    <div class="container my-3">
        <div class="card card-outline card-sb h-100">
            <div class="card-body">
                <h3 class="card-title fw-bold text-sb">Halo, {{ strtoupper(auth()->user()->name) }}</h3>
                <br>
                <div class="row g-3 mt-3">
                    @role('marker')
                        <div class="col-lg-2 col-md-3 col-sm-6">
                            <a href="{{ route('dashboard-marker') }}" class="home-item">
                                <div class="card h-100">
                                    <div class="card-body">
                                        <div class="d-flex h-100 flex-column justify-content-between">
                                            <img src="{{ asset('dist/img/marker.png') }}" class="img-fluid p-3"
                                                alt="marker image">
                                            <p class="text-center fw-bold text-uppercase text-dark">Marker</p>
                                        </div>
                                    </div>
                                </div>
                            </a>
                        </div>
                    @endrole

                    @role('cutting')
                        <div class="col-lg-2 col-md-3 col-sm-6">
                            <a href="{{ route('dashboard-cutting') }}" class="home-item">
                                <div class="card h-100">
                                    <div class="card-body">
                                        <div class="d-flex h-100 flex-column justify-content-between">
                                            <img src="{{ asset('dist/img/cutting.png') }}" class="img-fluid p-3"
                                                alt="cutting image">
                                            <p class="text-center fw-bold text-uppercase text-dark">Cutting</p>
                                        </div>
                                    </div>
                                </div>
                            </a>
                        </div>
                    @endrole

                    @role('stocker')
                        <div class="col-lg-2 col-md-3 col-sm-6">
                            <a href="{{ route('dashboard-stocker') }}" class="home-item">
                                <div class="card h-100">
                                    <div class="card-body">
                                        <div class="d-flex h-100 flex-column justify-content-between">
                                            <img src="{{ asset('dist/img/stocker.png') }}" class="img-fluid p-3"
                                                alt="stocker image">
                                            <p class="text-center fw-bold text-uppercase text-dark">Stocker</p>
                                        </div>
                                    </div>
                                </div>
                            </a>
                        </div>
                    @endrole

                    @role('dc')
                        <div class="col-lg-2 col-md-3 col-sm-6">
                            <a href="{{ route('dashboard-dc') }}" class="home-item">
                                <div class="card h-100">
                                    <div class="card-body">
                                        <div class="d-flex h-100 flex-column justify-content-between">
                                            <img src="{{ asset('dist/img/distribution.jpeg') }}" class="img-fluid p-3"
                                                alt="dc image">
                                            <p class="text-center fw-bold text-uppercase text-dark">DC</p>
                                        </div>
                                    </div>
                                </div>
                            </a>
                        </div>
                    @endrole

                    @role('sewing')
                        <div class="col-lg-2 col-md-3 col-sm-6">
                            <a href="{{ route('dashboard-sewing-eff') }}" class="home-item">
                                <div class="card h-100">
                                    <div class="card-body">
                                        <div class="d-flex h-100 flex-column justify-content-between">
                                            <img src="{{ asset('dist/img/sewingline.png') }}" class="img-fluid p-3"
                                                alt="sewing image">
                                            <p class="text-center fw-bold text-uppercase text-dark">Sewing Line</p>
                                        </div>
                                    </div>
                                </div>
                            </a>
                        </div>
                    @endrole

                    @role('machine')
                        <div class="col-lg-2 col-md-3 col-sm-6">
                            <a href="{{ route('dashboard-mut-mesin') }}" class="home-item">
                                <div class="card h-100">
                                    <div class="card-body">
                                        <div class="d-flex h-100 flex-column justify-content-between">
                                            <img src="{{ asset('dist/img/mut_mesin.png') }}" class="img-fluid p-3"
                                                alt="machine image">
                                            <p class="text-center fw-bold text-uppercase text-dark">Mutasi Mesin</p>
                                        </div>
                                    </div>
                                </div>
                            </a>
                        </div>
                    @endrole

                    @role('warehouse')
                        <div class="col-lg-2 col-md-3 col-sm-6">
                            <a href="#" class="home-item" onclick="getmodalwarehouse()">
                                <div class="card h-100">
                                    <div class="card-body">
                                        <div class="d-flex h-100 flex-column justify-content-between">
                                            <img src="{{ asset('dist/img/warehouse.png') }}" class="img-fluid p-3"
                                                alt="whs image">
                                            <p class="text-center fw-bold text-uppercase text-dark">Warehouse</p>
                                        </div>
                                    </div>
                                </div>
                            </a>
                        </div>
                        <div class="col-lg-2 col-md-3 col-sm-6">
                            <a href="{{ route('stock_opname') }}" class="home-item">
                                <div class="card h-100">
                                    <div class="card-body">
                                        <div class="d-flex h-100 flex-column justify-content-between">
                                            <img src="{{ asset('dist/img/stock_opname.png') }}" class="img-fluid p-3"
                                                alt="so image">
                                            <p class="text-center fw-bold text-uppercase text-dark">Stock Opname</p>
                                        </div>
                                    </div>
                                </div>
                            </a>
                        </div>
                        {{-- <div class="col-lg-2 col-md-3 col-sm-6">
                            <a href="{{ route('dashboard-qc-inspect') }}" class="home-item">
                                <div class="card h-100">
                                    <div class="card-body">
                                        <div class="d-flex h-100 flex-column justify-content-between">
                                            <img src="{{ asset('dist/img/inspect.png') }}" class="img-fluid p-3"
                                                alt="inspect image">
                                            <p class="text-center fw-bold text-uppercase text-dark">Inspect</p>
                                        </div>
                                    </div>
                                </div>
                            </a>
                        </div> --}}
                        <div class="col-lg-2 col-md-3 col-sm-6 d-none">
                            <a href="{{ route('procurement') }}" class="home-item">
                                <div class="card h-100">
                                    <div class="card-body">
                                        <div class="d-flex h-100 flex-column justify-content-between">
                                            <img src="{{ asset('dist/img/procurement.png') }}" class="img-fluid p-3"
                                                alt="qr code image">
                                            <p class="text-center fw-bold text-uppercase text-dark">Procurement</p>
                                        </div>
                                    </div>
                                </div>
                            </a>
                        </div>
                        @endwarehouse

                        @ppic
                            <div class="col-lg-2 col-md-3 col-sm-6">
                                <a href="{{ route('dashboard-ppic') }}" class="home-item">
                                    <div class="card h-100">
                                        <div class="card-body">
                                            <div class="d-flex h-100 flex-column justify-content-between">
                                                <img src="{{ asset('dist/img/ppic.png') }}" class="img-fluid p-3"
                                                    alt="ppic image">
                                                <p class="text-center fw-bold text-uppercase text-dark">PPIC</p>
                                            </div>
                                        </div>
                                    </div>
                                </a>
                            </div>
                        @endppic

                        @packing
                            <div class="col-lg-2 col-md-3 col-sm-6">
                                <a href="{{ route('dashboard-packing') }}" class="home-item">
                                    <div class="card h-100">
                                        <div class="card-body">
                                            <div class="d-flex h-100 flex-column justify-content-between">
                                                <img src="{{ asset('dist/img/packing.png') }}" class="img-fluid p-3"
                                                    alt="packing image">
                                                <p class="text-center fw-bold text-uppercase text-dark">Packing</p>
                                            </div>
                                        </div>
                                    </div>
                                </a>
                            </div>

                            <div class="col-lg-2 col-md-3 col-sm-6">
                                <a href="{{ route('dashboard_finish_good') }}" class="home-item">
                                    <div class="card h-100">
                                        <div class="card-body">
                                            <div class="d-flex h-100 flex-column justify-content-between">
                                                <img src="{{ asset('dist/img/finish_good.png') }}" class="img-fluid p-3"
                                                    alt="finish good image">
                                                <p class="text-center fw-bold text-uppercase text-dark">Finish Good Ekspedisi</p>
                                            </div>
                                        </div>
                                    </div>
                                </a>
                            </div>
                        @endpacking

                        @admin
                            <div class="col-lg-2 col-md-3 col-sm-6">
                                <a href="{{ route('dashboard-report-doc') }}" class="home-item">
                                    <div class="card h-100">
                                        <div class="card-body">
                                            <div class="d-flex h-100 flex-column justify-content-between">
                                                <img src="{{ asset('dist/img/doc_report.png') }}" class="img-fluid p-3"
                                                    alt="qr code image">
                                                <p class="text-center fw-bold text-uppercase text-dark">Document Report</p>
                                            </div>
                                        </div>
                                    </div>
                                </a>
                            </div>
                        @endadmin

                        {{-- Deprecated --}}
                        {{--
                        @ga
                            <div class="col-lg-2 col-md-3 col-sm-6">
                                <a href="{{ route('dashboard-ga') }}" class="home-item">
                                    <div class="card h-100">
                                        <div class="card-body">
                                            <div class="d-flex h-100 flex-column justify-content-between">
                                                <img src="{{ asset('dist/img/general_affair.png') }}" class="img-fluid p-3" alt="qr code image">
                                                <p class="text-center fw-bold text-uppercase text-dark">G.A.I.S</p>
                                            </div>
                                        </div>
                                    </div>
                                </a>
                            </div>
                        @endga
                    --}}

                        {{-- Marketing --}}
                        @role('admin')
                            <div class="col-lg-2 col-md-3 col-sm-6">
                                <a href="{{ route('dashboard-marketing') }}" class="home-item">
                                    <div class="card h-100">
                                        <div class="card-body">
                                            <div class="d-flex h-100 flex-column justify-content-between">
                                                <img src="{{ asset('dist/img/marketing.png') }}" class="img-fluid p-3"
                                                    alt="marketing image">
                                                <p class="text-center fw-bold text-uppercase text-dark">Marketing</p>
                                            </div>
                                        </div>
                                    </div>
                                </a>
                            </div>
                        @endrole

                        {{-- Accounting --}}
                        @role('accounting')
                            <div class="col-lg-2 col-md-3 col-sm-6">
                                <a href="{{ route('accounting') }}" class="home-item">
                                    <div class="card h-100">
                                        <div class="card-body">
                                            <div class="d-flex h-100 flex-column justify-content-between">
                                                <img src="{{ asset('dist/img/accounting_img.png') }}" class="img-fluid p-3"
                                                    alt="qr code image">
                                                <p class="text-center fw-bold text-uppercase text-dark">Accounting</p>
                                            </div>
                                        </div>
                                    </div>
                                </a>
                            </div>
                        @endrole

                        {{-- General Menu --}}
                        @role('superadmin')
                            <div class="col-lg-2 col-md-3 col-sm-6">
                                <a href="{{ route('manage-user') }}" class="home-item">
                                    <div class="card h-100">
                                        <div class="card-body">
                                            <div class="d-flex h-100 flex-column justify-content-between">
                                                <img src="{{ asset('dist/img/manage-users.png') }}" class="img-fluid p-3"
                                                    alt="manage users image">
                                                <p class="text-center fw-bold text-uppercase text-dark">MANAGE USER</p>
                                            </div>
                                        </div>
                                    </div>
                                </a>
                            </div>
                            <div class="col-lg-2 col-md-3 col-sm-6">
                                <a href="{{ route('general-tools') }}" class="home-item">
                                    <div class="card h-100">
                                        <div class="card-body">
                                            <div class="d-flex h-100 flex-column justify-content-between">
                                                <img src="{{ asset('dist/img/tools.png') }}" class="img-fluid p-3"
                                                    alt="general tools image">
                                                <p class="text-center fw-bold text-uppercase text-dark">GENERAL TOOLS</p>
                                            </div>
                                        </div>
                                    </div>
                                </a>
                            </div>
                        @endrole

                        {{-- Log Out --}}
                        <div class="col-lg-2 col-md-3 col-sm-6">
                            <a href="{{ route('logout') }}"
                                onclick="event.preventDefault(); document.getElementById('logout-form').submit();"
                                class="home-item">
                                <div class="card h-100">
                                    <div class="card-body">
                                        <div class="d-flex h-100 flex-column justify-content-between">
                                            <img src="{{ asset('dist/img/signout.png') }}" class="img-fluid p-3"
                                                alt="other">
                                            <p class="text-center fw-bold text-uppercase text-dark">Logout</p>
                                        </div>
                                    </div>
                                </div>
                            </a>

                            <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none"
                                onsubmit="logout(this, event)">
                                @csrf
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Warehouse Modal --}}
        <div class="modal fade" id="modal-pilih-gudang">
            <form>
                <div class="modal-dialog modal-dialog-centered modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h4 class="modal-title text-sb fw-bold">List Warehouse</h4>
                            <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <div class="form-group row">
                                <div class="col-md-4 col-4">
                                    <a href="{{ route('dashboard-warehouse') }}" class="home-item">
                                        <div class="card h-100">
                                            <div class="card-body">
                                                <div class="d-flex h-100 flex-column justify-content-between">
                                                    <img src="{{ asset('dist/img/whs_fabric.png') }}" class="img-fluid p-3"
                                                        alt="whs_fabric image">
                                                    <p class="text-center fw-bold text-uppercase text-dark">Fabric</p>
                                                </div>
                                            </div>
                                        </div>
                                    </a>
                                </div>

                                <div class="col-md-4 col-4">
                                    <a href="#" class="home-item">
                                        <div class="card h-100">
                                            <div class="card-body">
                                                <div class="d-flex h-100 flex-column justify-content-between">
                                                    <img src="{{ asset('dist/img/whs_accs.png') }}" class="img-fluid p-3"
                                                        alt="whs_acc image">
                                                    <p class="text-center fw-bold text-uppercase text-dark">Accesories</p>
                                                </div>
                                            </div>
                                        </div>
                                    </a>
                                </div>

                                <div class="col-md-4 col-4">
                                    <a href="{{ route('dashboard-fg-stock') }}" class="home-item">
                                        <div class="card h-100">
                                            <div class="card-body">
                                                <div class="d-flex h-100 flex-column justify-content-between">
                                                    <img src="{{ asset('dist/img/whs_fg_stock.png') }}" class="img-fluid p-3"
                                                        alt="fg_stok image">
                                                    <p class="text-center fw-bold text-uppercase text-dark">FG Stock</p>
                                                </div>
                                            </div>
                                        </div>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>

    @endsection
