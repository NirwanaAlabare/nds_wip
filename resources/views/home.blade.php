@extends('layouts.index', ['navbar' => false, 'footer' => false])

@section('content')

    <div class="container mt-5">
        <div class="card h-100">
            <div class="card-body">
                <h3 class="card-title">Halo, {{ strtoupper(auth()->user()->name) }}</h3>
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
                                            <p class="text-center">Marker</p>
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
                                            <p class="text-center">Cutting</p>
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
                                            <p class="text-center">Stocker</p>
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
                                            <p class="text-center">DC</p>
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
                                            <p class="text-center">Mutasi Karyawan</p>
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
                                            <p class="text-center">Mutasi Mesin</p>
                                        </div>
                                    </div>
                                </div>
                            </a>
                        </div>
                    @endhr


                <!-- warehouse -->
                <div class="col-md-2 col-3">
                    <a href="{{ route('dashboard-warehouse') }}" class="home-item">
                        <div class="card h-100">
                            <div class="card-body">
                                <div class="d-flex h-100 flex-column justify-content-between">
                                    <img src="{{ asset('dist/img/warehouse.png') }}" class="img-fluid p-3" alt="cutting image">
                                    <p class="text-center">Warehouse</p>
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
                                        <p class="text-center">Logout</p>
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
@endsection
