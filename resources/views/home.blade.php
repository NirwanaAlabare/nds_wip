@extends('layouts.index', ['navbar' => false, 'footer' => false])

@section('content')

    <div class="container my-3">
        <div class="card card-outline card-sb h-100">
            <div class="card-body">
                <h3 class="card-title fw-bold text-sb">Halo, {{ strtoupper(auth()->user()->name) }}</h3>
                <br>
                <div class="row g-3 mt-3">
                    @if (auth()->user()->type == 'admin' || auth()->user()->type == 'marker' || auth()->user()->type == 'spreading')
                        <div class="col-md-2 col-3">
                            <a href="{{ route('dashboard-marker') }}" class="home-item">
                                <div class="card h-100">
                                    <div class="card-body">
                                        <div class="d-flex h-100 flex-column justify-content-between">
                                            <img src="{{ asset('dist/img/marker.png') }}" class="img-fluid p-3"
                                                alt="cutting image">
                                            <p class="text-center fw-bold text-uppercase text-dark">Marker</p>
                                        </div>
                                    </div>
                                </div>
                            </a>
                        </div>

                        <div class="col-md-2 col-3">
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

                        @stocker
                            <div class="col-md-2 col-3">
                                <a href="{{ route('dashboard-stocker') }}" class="home-item">
                                    <div class="card h-100">
                                        <div class="card-body">
                                            <div class="d-flex h-100 flex-column justify-content-between">
                                                <img src="{{ asset('dist/img/stocker.png') }}" class="img-fluid p-3"
                                                    alt="qr code image">
                                                <p class="text-center fw-bold text-uppercase text-dark">Stocker</p>
                                            </div>
                                        </div>
                                    </div>
                                </a>
                            </div>
                        @endstocker
                    @endif

                    @dc
                        <div class="col-md-2 col-3">
                            <a href="{{ route('dashboard-dc') }}" class="home-item">
                                <div class="card h-100">
                                    <div class="card-body">
                                        <div class="d-flex h-100 flex-column justify-content-between">
                                            <img src="{{ asset('dist/img/distribution.jpeg') }}" class="img-fluid p-3"
                                                alt="qr code image">
                                            <p class="text-center fw-bold text-uppercase text-dark">DC</p>
                                        </div>
                                    </div>
                                </div>
                            </a>
                        </div>
                    @enddc

                    {{-- @hr
                        <div class="col-md-2 col-3">
                            <a href="{{ route('dashboard-mut-karyawan') }}" class="home-item">
                                <div class="card h-100">
                                    <div class="card-body">
                                        <div class="d-flex h-100 flex-column justify-content-between">
                                            <img src="{{ asset('dist/img/mut_karyawan.jpg') }}" class="img-fluid p-3"
                                                alt="qr code image">
                                            <p class="text-center fw-bold text-uppercase text-dark">Mutasi Karyawan</p>
                                        </div>
                                    </div>
                                </div>
                            </a>
                        </div>
                    @endhr --}}

                    @hr
                        <div class="col-md-2 col-3">
                            <a href="{{ route('dashboard-mut-mesin') }}" class="home-item">
                                <div class="card h-100">
                                    <div class="card-body">
                                        <div class="d-flex h-100 flex-column justify-content-between">
                                            <img src="{{ asset('dist/img/mut_mesin.png') }}" class="img-fluid p-3"
                                                alt="qr code image">
                                            <p class="text-center fw-bold text-uppercase text-dark">Mutasi Mesin</p>
                                        </div>
                                    </div>
                                </div>
                            </a>
                        </div>
                    @endhr


                    <!-- warehouse -->
                    <!-- <div class="col-md-2 col-3">
                                        <a href="{{ route('dashboard-warehouse') }}" class="home-item">
                                            <div class="card h-100">
                                                <div class="card-body">
                                                    <div class="d-flex h-100 flex-column justify-content-between">
                                                        <img src="{{ asset('dist/img/warehouse.png') }}" class="img-fluid p-3" alt="cutting image">
                                                        <p class="text-center fw-bold text-uppercase text-dark">Warehouse</p>
                                                    </div>
                                                </div>
                                            </div>
                                        </a>
                                    </div> -->

                    <div class="col-md-2 col-3">
                        <!-- <a href="{{ route('dashboard-warehouse') }}" class="home-item"> -->
                        <a href="#" class="home-item" onclick="getmodalwarehouse()">
                            <div class="card h-100">
                                <div class="card-body">
                                    <div class="d-flex h-100 flex-column justify-content-between">
                                        <img src="{{ asset('dist/img/warehouse.png') }}" class="img-fluid p-3"
                                            alt="cutting image">
                                        <p class="text-center fw-bold text-uppercase text-dark">Warehouse</p>
                                    </div>
                                </div>
                            </div>
                        </a>
                        <!-- </a> -->
                    </div>

                    <div class="col-md-2 col-3">
                        <a href="{{ route('dashboard-packing') }}" class="home-item">
                            <div class="card h-100">
                                <div class="card-body">
                                    <div class="d-flex h-100 flex-column justify-content-between">
                                        <img src="{{ asset('dist/img/packing.png') }}" class="img-fluid p-3"
                                            alt="qr code image">
                                        <p class="text-center fw-bold text-uppercase text-dark">Packing</p>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>

                    <div class="col-md-2 col-3">
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
    </div>

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
                                                    alt="cutting image">
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
                                                    alt="cutting image">
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
                            <div>
                            </div>
                        </div>
                    </div>
        </form>
    </div>

@endsection
