@extends('layouts.index', ['navbar' => false, 'footer' => false])

@section('content')
    <div class="container mt-5">
        <div class="card h-100">
            <div class="card-body">
                <h3 class="card-title">Halo, {{ strtoupper(auth()->user()->name) }}</h3>
                <br>
                <div class="row g-3 mt-3">
                    <div class="col-md-2 col-3">
                        <a href="{{ route('dashboard-cutting') }}" class="home-item">
                            <div class="card h-100">
                                <div class="card-body">
                                    <div class="d-flex h-100 flex-column justify-content-between">
                                        <img src="{{ asset('dist/img/cutting.png') }}" class="img-fluid p-3" alt="cutting image">
                                        <p class="text-center">Cutting</p>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>
                    @if (auth()->user()->type == "stocker")
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
                    @endif
                    <div class="col-md-2 col-3">
                        <a href="items" class="home-item">
                            <div class="card h-100">
                                <div class="card-body">
                                    <div class="d-flex h-100 flex-column justify-content-between">
                                        <img src="{{ asset('dist/img/qr-generate.png') }}" class="img-fluid p-3"
                                            alt="qr code image">
                                        <p class="text-center">Generate QR</p>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>
                    <div class="col-md-2 col-3">
                        <a href="item-details" class="home-item">
                            <div class="card h-100">
                                <div class="card-body">
                                    <div class="d-flex h-100 flex-column justify-content-between">
                                        <img src="{{ asset('dist/img/scan-qr.png') }}" class="img-fluid p-3" alt="qr code image">
                                        <p class="text-center">Scan QR</p>
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
                                        <img src="{{ asset('dist/img/signout.png') }}" class="img-fluid p-3" alt="other">
                                        <p class="text-center">Logout</p>
                                    </div>
                                </div>
                            </div>
                        </a>

                        <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none" onsubmit="logout(this, event)">
                            @csrf
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
