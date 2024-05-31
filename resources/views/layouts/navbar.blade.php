@if (!isset($page))
    @php
        $page = '';
    @endphp
@endif

@if (!isset($subPage))
    @php
        $subPage = '';
    @endphp
@endif

@if (!isset($subPageGroup))
    @php
        $subPageGroup = '';
    @endphp
@endif

<!-- Navbar -->
<nav class="main-header navbar navbar-expand-md navbar-light navbar-white">
    <div class="container">
        <a href="{{ $page != '' ? route($page) : '#' }}" class="navbar-brand">
            <img src="{{ asset('dist/img/logo-icon.png') }}" alt="nds Logo" class="brand-image">
        </a>

        <button class="navbar-toggler order-1" type="button" data-bs-toggle="collapse" data-bs-target="#navbarCollapse" aria-controls="navbarCollapse" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse order-3" id="navbarCollapse">
            <!-- Left navbar links -->
            <ul class="navbar-nav">
                @if ($page == 'dashboard-warehouse')
                    <li class="nav-item dropdown">
                        <a id="dropdownSubMenu1" href="#" data-bs-toggle="dropdown" aria-haspopup="true"
                            aria-expanded="false" class="nav-link dropdown-toggle">Master</a>
                        <ul aria-labelledby="dropdownSubMenu1" class="dropdown-menu border-0 shadow">
                            <li>
                                <a href="{{ route('master-lokasi') }}" class="dropdown-item">
                                    Master Lokasi
                                </a>
                            </li>
                        </ul>
                    </li>
                @endif

                @if ($page == 'dashboard-warehouse')
                    <li class="nav-item dropdown">
                        <a id="dropdownSubMenu1" href="#" data-bs-toggle="dropdown" aria-haspopup="true"
                            aria-expanded="false" class="nav-link dropdown-toggle">Penerimaan</a>
                        <ul aria-labelledby="dropdownSubMenu1" class="dropdown-menu border-0 shadow">

                            <li>
                                <a href="{{ route('in-material') }}" class="dropdown-item">
                                    Penerimaan Bahan Baku
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('retur-inmaterial') }}" class="dropdown-item">
                                    Penerimaan Retur Bahan Baku
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('mutasi-lokasi') }}" class="dropdown-item">
                                    Mutasi Lokasi
                                </a>
                            </li>
                        </ul>
                    </li>
                @endif

                @if ($page == 'dashboard-warehouse')
                    <li class="nav-item dropdown">
                        <a id="dropdownSubMenu1" href="#" data-bs-toggle="dropdown" aria-haspopup="true"
                            aria-expanded="false" class="nav-link dropdown-toggle">Pengeluaran</a>
                        <ul aria-labelledby="dropdownSubMenu1" class="dropdown-menu border-0 shadow">

                            <li>
                                <a href="{{ route('req-material') }}" class="dropdown-item">
                                    Permintaan Bahan Baku
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('out-material') }}" class="dropdown-item">
                                    Pengeluaran Bahan Baku
                                </a>
                            </li>

                            <li>
                                <a href="{{ route('retur-material') }}" class="dropdown-item">
                                    Retur Bahan Baku
                                </a>
                            </li>
                        </ul>
                    </li>
                @endif

                @if ($page == 'dashboard-warehouse')
                    <li class="nav-item dropdown">
                        <a id="dropdownSubMenu1" href="#" data-bs-toggle="dropdown" aria-haspopup="true"
                            aria-expanded="false" class="nav-link dropdown-toggle">QC</a>
                        <ul aria-labelledby="dropdownSubMenu1" class="dropdown-menu border-0 shadow">

                            <li>
                                <a href="{{ route('qc-pass') }}" class="dropdown-item">
                                    QC Inspect
                                </a>
                            </li>
                        </ul>
                    </li>
                @endif

                @if ($page == 'dashboard-warehouse')
                    <li class="nav-item dropdown">
                        <a id="dropdownSubMenu1" href="#" data-bs-toggle="dropdown" aria-haspopup="true"
                            aria-expanded="false" class="nav-link dropdown-toggle">Laporan</a>
                        <ul aria-labelledby="dropdownSubMenu1" class="dropdown-menu border-0 shadow">

                            <li>
                                <a href="{{ route('lap-det-pemasukan') }}" class="dropdown-item">
                                    Pemasukan Detail Item
                                </a>
                                <a href="{{ route('lap-det-pemasukanroll') }}" class="dropdown-item">
                                    Pemasukan Detail Roll
                                </a>
                                <a href="{{ route('lap-det-pengeluaran') }}" class="dropdown-item">
                                    Pengeluaran Detail Item
                                </a>
                                <a href="{{ route('lap-det-pengeluaranroll') }}" class="dropdown-item">
                                    Pengeluaran Detail Roll
                                </a>
                                <a href="{{ route('lap-mutasi-global') }}" class="dropdown-item">
                                    Mutasi Global
                                </a>
                                <a href="{{ route('lap-mutasi-detail') }}" class="dropdown-item">
                                    Mutasi Detail
                                </a>
                            </li>
                        </ul>
                    </li>
                @endif

                @if ($page == 'dashboard-warehouse')
                    <li class="nav-item dropdown">
                        <a id="dropdownSubMenu1" href="#" data-bs-toggle="dropdown" aria-haspopup="true"
                            aria-expanded="false" class="nav-link dropdown-toggle">Konfirmasi</a>
                        <ul aria-labelledby="dropdownSubMenu1" class="dropdown-menu border-0 shadow">

                            <li>
                                <a href="{{ route('konfirmasi-pemasukan') }}" class="dropdown-item">
                                    Konfirmasi Pemasukan
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('konfirmasi-pengeluaran') }}" class="dropdown-item">
                                    Konfirmasi Pengeluaran
                                </a>
                            </li>

                        </ul>
                    </li>
                @endif


                @if ($page == 'dashboard-marker')
                    <li class="nav-item dropdown">
                        <a id="dropdownSubMenu1" href="#" data-bs-toggle="dropdown"
                            aria-haspopup="true"aria-expanded="false"
                            class="nav-link dropdown-toggle {{ $subPageGroup == 'master-marker' ? 'active' : '' }}">Master</a>
                        <ul aria-labelledby="dropdownSubMenu1" class="dropdown-menu border-0 shadow">
                            @marker
                                <li>
                                    <a href="{{ route('master-part') }}"
                                        class="dropdown-item {{ $subPage == 'master-part' ? 'active' : '' }}">
                                        Master Part <i class="fas fa-plus-square fa-sm"></i>
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('master-secondary') }}"
                                        class="dropdown-item {{ $subPage == 'master-secondary' ? 'active' : '' }}">
                                        Master Secondary <i class="fas fa-location-arrow fa-sm"></i>
                                    </a>
                                </li>
                            @endmarker
                        </ul>
                    </li>
                    <li class="nav-item dropdown">
                        <a id="dropdownSubMenu1" href="#" data-bs-toggle="dropdown"
                            aria-haspopup="true"aria-expanded="false"
                            class="nav-link dropdown-toggle {{ $subPageGroup == 'proses-marker' ? 'active' : '' }}">Process</a>
                        <ul aria-labelledby="dropdownSubMenu1" class="dropdown-menu border-0 shadow">
                            @marker
                                <li>
                                    <a href="{{ route('part') }}"
                                        class="dropdown-item {{ $subPage == 'part' ? 'active' : '' }}">
                                        Part <i class="fas fa-th fa-sm"></i>
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('marker') }}"
                                        class="dropdown-item {{ $subPage == 'marker' ? 'active' : '' }}">
                                        Marker <i class="fas fa-marker fa-sm"></i>
                                    </a>
                                </li>
                            @endmarker
                        </ul>
                    </li>
                @endif
                @if ($page == 'dashboard-cutting')
                    <li class="nav-item dropdown">
                        <a id="dropdownSubMenu1" href="#" data-bs-toggle="dropdown"
                            aria-haspopup="true"aria-expanded="false"
                            class="nav-link dropdown-toggle {{ $subPageGroup == 'proses-cutting' ? 'active' : '' }}">Process</a>
                        <ul aria-labelledby="dropdownSubMenu1" class="dropdown-menu border-0 shadow">
                            {{-- @marker
                                <li>
                                    <a href="{{ route('marker') }}"
                                        class="dropdown-item {{ $subPage == 'marker' ? 'active' : '' }}">
                                        Marker <i class="fas fa-marker fa-sm"></i>
                                    </a>
                                </li>
                            @endmarker --}}

                            @admin
                                <li>
                                    <a href="{{ route('spreading') }}"
                                        class="dropdown-item {{ $subPage == 'spreading' ? 'active' : '' }}">
                                        Spreading <i class="fas fa-scroll fa-sm"></i>
                                    </a>
                                </li>
                            @endadmin

                            @meja
                                <li>
                                    <a href="{{ route('form-cut-input') }}"
                                        class="appeared dropdown-item {{ $subPage == 'form-cut-input' ? 'active' : '' }}">
                                        Form Cutting <i class="fas fa-cut fa-sm"></i>
                                    </a>
                                </li>
                            @endmeja
                        </ul>
                    </li>

                    @admin
                        <li class="nav-item dropdown">
                            <a id="dropdownSubMenu2" href="#" data-bs-toggle="dropdown" aria-haspopup="true"
                                aria-expanded="false"
                                class="nav-link dropdown-toggle {{ $subPageGroup == 'cuttingplan-cutting' ? 'active' : '' }}">Cutting
                                Plan</a>
                            <ul aria-labelledby="dropdownSubMenu2" class="dropdown-menu border-0 shadow">
                                <li>
                                    <a href="{{ route('cut-plan') }}"
                                        class="dropdown-item {{ $subPage == 'cut-plan' ? 'active' : '' }}">
                                        Cutting Plan <i class="fas fa-map fa-sm"></i>
                                    </a>
                                </li>
                            </ul>
                        </li>
                        <li class="nav-item dropdown">
                            <a id="dropdownSubMenu2" href="#" data-bs-toggle="dropdown" aria-haspopup="true"
                                aria-expanded="false"
                                class="nav-link dropdown-toggle {{ $subPage == 'manage-cutting' ? 'active' : '' }}">Completed
                                Form</a>
                            <ul aria-labelledby="dropdownSubMenu2" class="dropdown-menu border-0 shadow">
                                <li>
                                    <a href="{{ route('manage-cutting') }}"
                                        class="dropdown-item {{ $subPage == 'manage-cutting' ? 'active' : '' }}">
                                        Completed Form <i class="fa-solid fa-check-to-slot"></i>
                                    </a>
                                </li>
                            </ul>
                        </li>
                        <li class="nav-item dropdown">
                            <a id="dropdownSubMenu2" href="#" data-bs-toggle="dropdown" aria-haspopup="true"
                                aria-expanded="false"
                                class="nav-link dropdown-toggle {{ $subPageGroup == 'laporan-cutting' ? 'active' : '' }}">Roll</a>
                            <ul aria-labelledby="dropdownSubMenu2" class="dropdown-menu border-0 shadow">
                                <li>
                                    <a href="{{ route('lap_pemakaian') }}"
                                        class="dropdown-item {{ $subPage == 'lap-pemakaian' ? 'active' : '' }}">
                                        Roll Consumption <i class="fa-solid fa-toilet-paper fa-sm"></i>
                                    </a>
                                </li>
                            </ul>
                        </li>
                    @endadmin
                @endif

                @if ($page == 'dashboard-stocker')
                    {{-- <li class="nav-item dropdown">
                        <a id="dropdownSubMenu1" href="#" data-bs-toggle="dropdown"
                            aria-haspopup="true"aria-expanded="false"
                            class="nav-link dropdown-toggle {{ $subPageGroup == 'master-stocker' ? 'active' : '' }}">Master</a>
                        <ul aria-labelledby="dropdownSubMenu1" class="dropdown-menu border-0 shadow">
                            @stocker
                                <li>
                                    <a href="{{ route('master-part') }}"
                                        class="dropdown-item {{ $subPage == 'master-part' ? 'active' : '' }}">
                                        Master Part <i class="fas fa-plus-square fa-sm"></i>
                                    </a>
                                </li>
                            @endstocker
                        </ul>
                    </li> --}}

                    <li class="nav-item dropdown">
                        <a id="dropdownSubMenu1" href="#" data-bs-toggle="dropdown"
                            aria-haspopup="true"aria-expanded="false"
                            class="nav-link dropdown-toggle {{ $subPageGroup == 'proses-stocker' ? 'active' : '' }}">Process</a>
                        <ul aria-labelledby="dropdownSubMenu1" class="dropdown-menu border-0 shadow">
                            @stocker
                                <li>
                                    <a href="{{ route('stocker-part') }}"
                                        class="dropdown-item {{ $subPage == 'part' ? 'active' : '' }}">
                                        Part <i class="fas fa-th fa-sm"></i>
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('stocker') }}"
                                        class="dropdown-item {{ $subPage == 'stocker' ? 'active' : '' }}">
                                        Stocker <i class="fas fa-ticket-alt"></i>
                                    </a>
                                </li>
                            @endstocker
                        </ul>
                    </li>
                @endif

                @if ($page == 'dashboard-dc')
                    <li class="nav-item dropdown">
                        <a id="dropdownSubMenu1" href="#" data-bs-toggle="dropdown"
                            aria-haspopup="true"aria-expanded="false"
                            class="nav-link dropdown-toggle {{ $subPageGroup == 'dcin-dc' ? 'active' : '' }}">DC
                            In</a>
                        <ul aria-labelledby="dropdownSubMenu1" class="dropdown-menu border-0 shadow">
                            @dc
                                <li>
                                    <a href="{{ route('dc-in') }}"
                                        class="dropdown-item {{ $subPage == 'dc-in' ? 'active' : '' }}">
                                        DC In <i class="fas fa-qrcode fa-sm"></i>
                                    </a>
                                </li>
                            @enddc
                        </ul>
                    </li>
                    <li class="nav-item dropdown">
                        <a id="dropdownSubMenu1" href="#" data-bs-toggle="dropdown"
                            aria-haspopup="true"aria-expanded="false"
                            class="nav-link dropdown-toggle {{ $subPageGroup == 'secondary-dc' ? 'active' : '' }}">Secondary</a>
                        <ul aria-labelledby="dropdownSubMenu1" class="dropdown-menu border-0 shadow">
                            @dc
                                <li>
                                    <a href="{{ route('secondary-inhouse') }}"
                                        class="dropdown-item {{ $subPage == 'secondary-inhouse' ? 'active' : '' }}">
                                        Secondary Dalam <i class="fas fa-house-user"></i>
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('secondary-in') }}"
                                        class="dropdown-item {{ $subPage == 'secondary-in' ? 'active' : '' }}">
                                        Secondary In <i class="fas fa-receipt fa-sm"></i>
                                    </a>
                                </li>
                                <li>
                                    {{-- <a href="{{ route('summary-secondary') }}"
                                        class="dropdown-item {{ $subPage == 'summary-secondary' ? 'active' : '' }}">
                                        Summary Secondary <i class="fas fa-receipt fa-sm"></i>
                                    </a> --}}
                                </li>
                            @enddc
                        </ul>
                    </li>
                    <li class="nav-item dropdown">
                        <a id="dropdownSubMenu1" href="#" data-bs-toggle="dropdown"
                            aria-haspopup="true"aria-expanded="false"
                            class="nav-link dropdown-toggle {{ $subPageGroup == 'rak-dc' ? 'active' : '' }}">Rak</a>
                        <ul aria-labelledby="dropdownSubMenu1" class="dropdown-menu border-0 shadow">
                            @dc
                                <li>
                                    <a href="{{ route('rack') }}"
                                        class="dropdown-item {{ $subPage == 'rack' ? 'active' : '' }}">
                                        Master Rak <i class="fas fa-plus-square fa-sm"></i>
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('stock-rack') }}"
                                        class="dropdown-item {{ $subPage == 'stock-rack' ? 'active' : '' }}">
                                        Rak <i class="fas fa-table fa-sm"></i>
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('stock-rack-visual') }}"
                                        class="dropdown-item {{ $subPage == 'stock-rack-visual' ? 'active' : '' }}">
                                        Stok Rak <i class="fas fa-th-list"></i>
                                    </a>
                                </li>
                            @enddc
                        </ul>
                    </li>
                    <li class="nav-item dropdown">
                        <a id="dropdownSubMenu1" href="#" data-bs-toggle="dropdown"
                            aria-haspopup="true"aria-expanded="false"
                            class="nav-link dropdown-toggle {{ $subPageGroup == 'stok-dc' ? 'active' : '' }}">Stok
                            DC</a>
                        <ul aria-labelledby="dropdownSubMenu1" class="dropdown-menu border-0 shadow">
                            @dc
                                <li>
                                    <a href="{{ route('stock-dc-complete') }}"
                                        class="dropdown-item {{ $subPage == 'stok-dc-complete' ? 'active' : '' }}">
                                        Stocker Complete <i class="fa-solid fa-circle-check"></i>
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('stock-dc-incomplete') }}"
                                        class="dropdown-item {{ $subPage == 'stok-dc-incomplete' ? 'active' : '' }}">
                                        Stocker Incomplete <i class="fa-solid fa-spinner"></i>
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('stock-dc-wip') }}"
                                        class="dropdown-item {{ $subPage == 'stok-dc-wip' ? 'active' : '' }}">
                                        WIP <i class="fa-solid fa-shirt"></i>
                                    </a>
                                </li>
                            @enddc
                        </ul>
                    </li>
                    <li class="nav-item dropdown">
                        <a id="dropdownSubMenu1" href="#" data-bs-toggle="dropdown" aria-haspopup="true"
                            aria-expanded="false"
                            class="nav-link dropdown-toggle {{ $subPageGroup == 'trolley-dc' ? 'active' : '' }}">Trolley</a>
                        <ul aria-labelledby="dropdownSubMenu1" class="dropdown-menu border-0 shadow">
                            @dc
                                <li>
                                    <a href="{{ route('trolley') }}"
                                        class="dropdown-item {{ $subPage == 'trolley' ? 'active' : '' }}">
                                        Master Trolley <i class="fas fa-plus-square fa-sm"></i>
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('stock-trolley') }}"
                                        class="dropdown-item {{ $subPage == 'stock-trolley' ? 'active' : '' }}">
                                        Trolley <i class="fas fa-dolly-flatbed"></i>
                                    </a>
                                </li>
                            @enddc
                        </ul>
                    </li>
                    <li class="nav-item dropdown">
                        <a id="dropdownSubMenu1" href="#" data-bs-toggle="dropdown" aria-haspopup="true"
                            aria-expanded="false"
                            class="nav-link dropdown-toggle {{ $subPageGroup == 'loading-dc' ? 'active' : '' }}">Loading</a>
                        <ul aria-labelledby="dropdownSubMenu1" class="dropdown-menu border-0 shadow">
                            @dc
                                <li>
                                    <a href="{{ route('loading-line') }}" class="dropdown-item {{ $subPage == 'loading-line' ? 'active' : '' }}">
                                        Line <i class="fa-solid fa-users-line"></i>
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('summary-loading') }}" class="dropdown-item {{ $subPage == 'summary-loading' ? 'active' : '' }}">
                                        Summary <i class="fa-solid fa-list-check"></i>
                                    </a>
                                </li>
                            @enddc
                        </ul>
                    </li>
                @endif

                @if ($page == 'dashboard-fg-stock')
                    <li class="nav-item dropdown">
                        <a id="dropdownSubMenu1" href="#" data-bs-toggle="dropdown"
                            aria-haspopup="true"aria-expanded="false"
                            class="nav-link dropdown-toggle {{ $subPageGroup == 'fgstock-masterlokasi' ? 'active' : '' }}">Master</a>
                        <ul aria-labelledby="dropdownSubMenu1" class="dropdown-menu border-0 shadow">
                            <li>
                                <a href="{{ route('master-lokasi-fg-stock') }}"
                                    class="dropdown-item {{ $subPage == 'fg-stock' ? 'active' : '' }}">
                                    Master Lokasi <i class="fas fa-search-location fa-sm"></i>
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('master-sumber-penerimaan') }}"
                                    class="dropdown-item {{ $subPage == 'master-sumber-penerimaan' ? 'active' : '' }}">
                                    Master Sumber Penerimaan <i class="fas fa-indent fa-sm"></i>
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('master-tujuan-pengeluaran') }}"
                                    class="dropdown-item {{ $subPage == 'master-tujuan-pengeluaran' ? 'active' : '' }}">
                                    Master Tujuan Pengeluaran <i class="fas fa-outdent fa-sm"></i>
                                </a>
                            </li>
                        </ul>
                    </li>
                    <li class="nav-item dropdown">
                        <a id="dropdownSubMenu1" href="#" data-bs-toggle="dropdown"
                            aria-haspopup="true"aria-expanded="false"
                            class="nav-link dropdown-toggle {{ $subPageGroup == 'fgstock-bpb' ? 'active' : '' }}">Penerimaan</a>
                        <ul aria-labelledby="dropdownSubMenu1" class="dropdown-menu border-0 shadow">
                            <li>
                                <a href="{{ route('bpb-fg-stock') }}"
                                    class="dropdown-item {{ $subPage == 'bpb-fg-stock' ? 'active' : '' }}">
                                    Penerimaan Barang Jadi Stok <i class="fas fa-box fa-sm"></i>
                                </a>
                            </li>
                        </ul>
                    </li>
                    <li class="nav-item dropdown">
                        <a id="dropdownSubMenu1" href="#" data-bs-toggle="dropdown"
                            aria-haspopup="true"aria-expanded="false"
                            class="nav-link dropdown-toggle {{ $subPageGroup == 'fgstock-bppb' ? 'active' : '' }}">Pengeluaran</a>
                        <ul aria-labelledby="dropdownSubMenu1" class="dropdown-menu border-0 shadow">
                            <li>
                                <a href="{{ route('bppb-fg-stock') }}"
                                    class="dropdown-item {{ $subPage == 'bppb-fg-stock' ? 'active' : '' }}">
                                    Pengeluaran Barang Jadi Stok <i class="fas fa-box-open fa-sm"></i>
                                </a>
                            </li>
                        </ul>
                    </li>
                    <li class="nav-item dropdown">
                        <a id="dropdownSubMenu1" href="#" data-bs-toggle="dropdown"
                            aria-haspopup="true"aria-expanded="false"
                            class="nav-link dropdown-toggle {{ $subPageGroup == 'fgstock-mutasi' ? 'active' : '' }}">Mutasi</a>
                        <ul aria-labelledby="dropdownSubMenu1" class="dropdown-menu border-0 shadow">
                            <li>
                                <a href="{{ route('mutasi-fg-stock') }}"
                                    class="dropdown-item {{ $subPage == 'mutasi-fg-stock' ? 'active' : '' }}">
                                    Mutasi Internal <i class="fas fa-exchange-alt"></i>
                                </a>
                            </li>
                        </ul>
                    </li>
                    <li class="nav-item dropdown">
                        <a id="dropdownSubMenu1" href="#" data-bs-toggle="dropdown"
                            aria-haspopup="true"aria-expanded="false"
                            class="nav-link dropdown-toggle {{ $subPageGroup == 'fgstock-laporan' ? 'active' : '' }}">Laporan</a>
                        <ul aria-labelledby="dropdownSubMenu1" class="dropdown-menu border-0 shadow">
                            <li>
                                <a href="{{ route('laporan-fg-stock') }}"
                                    class="dropdown-item {{ $subPage == 'laporan-fg-stock' ? 'active' : '' }}">
                                    List Laporan <i class="fas fa-list fa-sm"></i>
                                </a>
                            </li>
                        </ul>
                    </li>
                @endif

                @if ($page == 'dashboard-packing')
                    <li class="nav-item dropdown">
                        <a id="dropdownSubMenu1" href="#" data-bs-toggle="dropdown"
                            aria-haspopup="true"aria-expanded="false"
                            class="nav-link dropdown-toggle {{ $subPageGroup == 'packing-transfer-garment' ? 'active' : '' }}">Transfer
                            Garment</a>
                        <ul aria-labelledby="dropdownSubMenu1" class="dropdown-menu border-0 shadow">
                            <li>
                                <a href="{{ route('transfer-garment') }}"
                                    class="dropdown-item {{ $subPage == 'transfer-garment' ? 'active' : '' }}">
                                    Transfer Garment <i class="fa-solid fa-right-left fa-sm"></i>
                                </a>
                            </li>
                        </ul>
                    </li>
                    <li class="nav-item dropdown">
                        <a id="dropdownSubMenu1" href="#" data-bs-toggle="dropdown"
                            aria-haspopup="true"aria-expanded="false"
                            class="nav-link dropdown-toggle {{ $subPageGroup == 'packing-packing-in' ? 'active' : '' }}">Packing
                            In</a>
                        <ul aria-labelledby="dropdownSubMenu1" class="dropdown-menu border-0 shadow">
                            <li>
                                <a href="{{ route('packing-in') }}"
                                    class="dropdown-item {{ $subPage == 'packing-in' ? 'active' : '' }}">
                                    Packing In <i class="fa-solid fa-people-carry fa-sm"></i>
                                </a>
                            </li>
                        </ul>
                    </li>
                    <li class="nav-item dropdown">
                        <a id="dropdownSubMenu1" href="#" data-bs-toggle="dropdown"
                            aria-haspopup="true"aria-expanded="false"
                            class="nav-link dropdown-toggle {{ $subPageGroup == 'packing-packing-out' ? 'active' : '' }}">Packing
                            Out</a>
                        <ul aria-labelledby="dropdownSubMenu1" class="dropdown-menu border-0 shadow">
                            <li>
                                <a href="{{ route('packing-out') }}"
                                    class="dropdown-item {{ $subPage == 'packing-out' ? 'active' : '' }}">
                                    Packing Out <i class="fa-solid fa-compress fa-sm"></i>
                                </a>
                            </li>
                        </ul>
                    </li>
                    <li class="nav-item dropdown">
                        <a id="dropdownSubMenu1" href="#" data-bs-toggle="dropdown"
                            aria-haspopup="true"aria-expanded="false"
                            class="nav-link dropdown-toggle {{ $subPageGroup == 'packing-master-karton' ? 'active' : '' }}">Master</a>
                        <ul aria-labelledby="dropdownSubMenu1" class="dropdown-menu border-0 shadow">
                            <li>
                                <a href="{{ route('master-karton') }}"
                                    class="dropdown-item {{ $subPage == 'master-karton' ? 'active' : '' }}">
                                    Master Karton <i class="fa-solid fa-boxes fa-sm"></i>
                                </a>
                            </li>
                        </ul>
                    </li>
                @endif

                @if ($page == 'dashboard-ppic')
                    <li class="nav-item dropdown">
                        <a id="dropdownSubMenu1" href="#" data-bs-toggle="dropdown"
                            aria-haspopup="true"aria-expanded="false"
                            class="nav-link dropdown-toggle {{ $subPageGroup == 'ppic-master' ? 'active' : '' }}">Master</a>
                        <ul aria-labelledby="dropdownSubMenu1" class="dropdown-menu border-0 shadow">
                            <li>
                                <a href="{{ route('master-so') }}"
                                    class="dropdown-item {{ $subPage == 'ppic-master-master-so' ? 'active' : '' }}">
                                    Master Sales Order <i class="fa-solid fa-list-ul fa-sm"></i>
                                </a>
                            </li>
                        </ul>
                    </li>
                @endif


                @if ($page == 'dashboard-ga')
                    <li class="nav-item dropdown">
                        <a id="dropdownSubMenu1" href="#" data-bs-toggle="dropdown"
                            aria-haspopup="true"aria-expanded="false"
                            class="nav-link dropdown-toggle {{ $subPageGroup == 'ga-pengajuan' ? 'active' : '' }}">Pengajuan</a>
                        <ul aria-labelledby="dropdownSubMenu1" class="dropdown-menu border-0 shadow">
                            <li>
                                <a href="{{ route('pengajuan-bahan-bakar') }}"
                                    class="dropdown-item {{ $subPage == 'ga-pengajuan-bahan-bakar' ? 'active' : '' }}">
                                    Bahan Bakar <i class="fa-solid fa-gas-pump fa-sm"></i>
                                </a>
                            </li>
                        </ul>
                    </li>
                    <li class="nav-item dropdown">
                        <a id="dropdownSubMenu1" href="#" data-bs-toggle="dropdown"
                            aria-haspopup="true"aria-expanded="false"
                            class="nav-link dropdown-toggle {{ $subPageGroup == 'ga-approval' ? 'active' : '' }}">Approval</a>
                        <ul aria-labelledby="dropdownSubMenu1" class="dropdown-menu border-0 shadow">
                            <li>
                                <a href="{{ route('approval-bahan-bakar') }}"
                                    class="dropdown-item {{ $subPage == 'ga-approval-bahan-bakar' ? 'active' : '' }}">
                                    Bahan Bakar <i class="fa-solid fa-gas-pump fa-sm"></i>
                                </a>
                            </li>
                        </ul>
                    </li>
                @endif



                @if ($page == 'dashboard-mut-karyawan')
                    <li class="nav-item dropdown">
                        <a id="dropdownSubMenu1" href="#" data-bs-toggle="dropdown" aria-haspopup="true"
                            aria-expanded="false"
                            class="nav-link dropdown-toggle {{ $subPageGroup == 'proses-karyawan' ? 'active' : '' }}">Process</a>
                        <ul aria-labelledby="dropdownSubMenu1" class="dropdown-menu border-0 shadow">
                            @hr
                                <li>
                                    <a href="{{ route('mut-karyawan') }}"
                                        class="dropdown-item {{ $subPage == 'mut-karyawan' ? 'active' : '' }}">
                                        Mutasi Karyawan<i class="fas fa-users-cog fa-sm"></i>
                                    </a>
                                </li>
                            @endhr
                        </ul>
                    </li>
                @endif


                @if ($page == 'dashboard-mut-mesin')
                    <li class="nav-item dropdown">
                        <a id="dropdownSubMenu1" href="#" data-bs-toggle="dropdown"
                            aria-haspopup="true"aria-expanded="false"
                            class="nav-link dropdown-toggle {{ $subPageGroup == 'proses-mut-mesin' ? 'active' : '' }}">Process</a>
                        <ul aria-labelledby="dropdownSubMenu1" class="dropdown-menu border-0 shadow">
                            @hr
                                <li>
                                    <a href="{{ route('mut-mesin') }}"
                                        class="dropdown-item {{ $subPage == 'mut-mesin' ? 'active' : '' }}">
                                        Mutasi Mesin <i class="fas fa-tools fa-sm"></i>
                                    </a>
                                </li>
                            @endhr
                        </ul>
                    </li>
                    <li class="nav-item dropdown">
                        <a id="dropdownSubMenu1" href="#" data-bs-toggle="dropdown"
                            aria-haspopup="true"aria-expanded="false"
                            class="nav-link dropdown-toggle {{ $subPageGroup == 'master-mut-mesin' ? 'active' : '' }}">Master</a>
                        <ul aria-labelledby="dropdownSubMenu1" class="dropdown-menu border-0 shadow">
                            <li>
                                <a href="{{ route('master-mut-mesin') }}"
                                    class="dropdown-item {{ $subPage == 'master-mut-mesin' ? 'active' : '' }}">
                                    Master Mesin <i class="fas fa-cogs fa-sm"></i>
                                </a>
                            </li>
                        </ul>
                    </li>
                @endif

                {{-- <li class="dropdown-divider"></li> --}}
                <!-- Level two dropdown-->
                {{-- <li class="dropdown-submenu dropdown-hover">
                            <a id="dropdownSubMenu2" href="#" role="button" data-bs-toggle="dropdown"
                                aria-haspopup="true" aria-expanded="false" class="dropdown-item dropdown-toggle">Hover
                                for action</a>
                            <ul aria-labelledby="dropdownSubMenu2" class="dropdown-menu border-0 shadow">
                                <li>
                                    <a tabindex="-1" href="#" class="dropdown-item">level 2</a>
                                </li>

                                <!-- Level three dropdown-->
                                <li class="dropdown-submenu">
                                    <a id="dropdownSubMenu3" href="#" role="button" data-bs-toggle="dropdown"
                                        aria-haspopup="true" aria-expanded="false"
                                        class="dropdown-item dropdown-toggle">level 2</a>
                                    <ul aria-labelledby="dropdownSubMenu3" class="dropdown-menu border-0 shadow">
                                        <li><a href="#" class="dropdown-item">3rd level</a></li>
                                        <li><a href="#" class="dropdown-item">3rd level</a></li>
                                    </ul>
                                </li>
                                <!-- End Level three -->

                                <li><a href="#" class="dropdown-item">level 2</a></li>
                                <li><a href="#" class="dropdown-item">level 2</a></li>
                            </ul>
                        </li> --}}
                <!-- End Level two -->

                <li class="nav-item">
                    <a href="{{ route('home') }}/" class="nav-link">
                        <i class="fas fa-home"></i>
                    </a>
                </li>
            </ul>

            <!-- SEARCH FORM -->
            {{-- <form class="form-inline ml-0 ml-md-3">
            <div class="input-group input-group-sm">
                <input class="form-control form-control-navbar" type="search" placeholder="Search" aria-label="Search">
                <div class="input-group-append">
                <button class="btn btn-navbar" type="submit">
                    <i class="fas fa-search"></i>
                </button>
                </div>
            </div>
            </form> --}}
        </div>

        <!-- Right navbar links -->
        <ul class="order-1 order-md-3 navbar-nav navbar-no-expand ml-auto">
            {{-- <!-- Messages Dropdown Menu -->
            <li class="nav-item dropdown">
                <a class="nav-link" data-bs-toggle="dropdown" href="#">
                    <i class="fas fa-comments"></i>
                    <span class="badge badge-danger navbar-badge">3</span>
                </a>
                <div class="dropdown-menu dropdown-menu-lg dropdown-menu-end">
                    <a href="#" class="dropdown-item">
                        <!-- Message Start -->
                        <div class="media">
                            <img src="{{ asset('dist/img/user1-128x128.jpg') }}" alt="User Avatar" class="img-size-50 mr-3 img-circle">
            <div class="media-body">
                <h3 class="dropdown-item-title">
                    Brad Diesel
                    <span class="float-end text-sm text-danger"><i class="fas fa-star"></i></span>
                </h3>
                <p class="text-sm">Call me whenever you can...</p>
                <p class="text-sm text-muted"><i class="far fa-clock mr-1"></i> 4 Hours Ago</p>
            </div>
    </div>
    <!-- Message End -->
    </a>
    <div class="dropdown-divider"></div>
    <a href="#" class="dropdown-item">
        <!-- Message Start -->
        <div class="media">
            <img src="{{ asset('dist/img/user8-128x128.jpg') }}" alt="User Avatar" class="img-size-50 img-circle mr-3">
            <div class="media-body">
                <h3 class="dropdown-item-title">
                    John Pierce
                    <span class="float-end text-sm text-muted"><i class="fas fa-star"></i></span>
                </h3>
                <p class="text-sm">I got your message bro</p>
                <p class="text-sm text-muted"><i class="far fa-clock mr-1"></i> 4 Hours Ago</p>
            </div>
        </div>
        <!-- Message End -->
    </a>
    <div class="dropdown-divider"></div>
    <a href="#" class="dropdown-item">
        <!-- Message Start -->
        <div class="media">
            <img src="{{ asset('dist/img/user3-128x128.jpg') }}" alt="User Avatar" class="img-size-50 img-circle mr-3">
            <div class="media-body">
                <h3 class="dropdown-item-title">
                    Nora Silvester
                    <span class="float-end text-sm text-warning"><i class="fas fa-star"></i></span>
                </h3>
                <p class="text-sm">The subject goes here</p>
                <p class="text-sm text-muted"><i class="far fa-clock mr-1"></i> 4 Hours Ago</p>
            </div>
        </div>
        <!-- Message End -->
    </a>
    <div class="dropdown-divider"></div>
    <a href="#" class="dropdown-item dropdown-footer">See All Messages</a>
    </div>
    </li>
    <!-- Notifications Dropdown Menu -->
    <li class="nav-item dropdown">
        <a class="nav-link" data-bs-toggle="dropdown" href="#">
            <i class="far fa-bell"></i>
            <span class="badge badge-warning navbar-badge">15</span>
        </a>
        <div class="dropdown-menu dropdown-menu-lg dropdown-menu-end">
            <span class="dropdown-header">15 Notifications</span>
            <div class="dropdown-divider"></div>
            <a href="#" class="dropdown-item">
                <i class="fas fa-envelope mr-2"></i> 4 new messages
                <span class="float-end text-muted text-sm">3 mins</span>
            </a>
            <div class="dropdown-divider"></div>
            <a href="#" class="dropdown-item">
                <i class="fas fa-users mr-2"></i> 8 friend requests
                <span class="float-end text-muted text-sm">12 hours</span>
            </a>
            <div class="dropdown-divider"></div>
            <a href="#" class="dropdown-item">
                <i class="fas fa-file mr-2"></i> 3 new reports
                <span class="float-end text-muted text-sm">2 days</span>
            </a>
            <div class="dropdown-divider"></div>
            <a href="#" class="dropdown-item dropdown-footer">See All Notifications</a>
        </div>
    </li> --}}
            <li class="nav-item">
                <a class="nav-link" data-bs-toggle="offcanvas" href="#user-offcanvas" role="button"
                    aria-controls="user-offcanvas">
                    {{ strtoupper(auth()->user()->name) }}
                    <i class="fas fa-user ps-1"></i>
                </a>
            </li>
        </ul>
    </div>
</nav>
<!-- /.navbar -->
