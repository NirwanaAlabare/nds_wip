@role('warehouse')
    <li class="nav-item dropdown">
        <a href="#" data-bs-toggle="dropdown" aria-haspopup="true"aria-expanded="false"
            class="nav-link dropdown-toggle {{ $subPageGroup == 'master-whs-soljer' ? 'active' : '' }}">Master</a>
        <ul aria-labelledby="dropdownSubMenu1" class="dropdown-menu border-0 shadow">
            <li>
                <a href="{{ route('master-barang-per-kategori') }}"
                    class="dropdown-item {{ $routeName == 'master-barang-per-kategori' ? 'active' : '' }}">
                    Master Barang
                </a>
            </li>
        </ul>
    </li>
    <li class="nav-item dropdown">
        <a href="#" data-bs-toggle="dropdown" aria-haspopup="true"aria-expanded="false"
            class="nav-link dropdown-toggle {{ $subPageGroup == 'penerimaan-whs-soljer' ? 'active' : '' }}">Penerimaan</a>
        <ul aria-labelledby="dropdownSubMenu1" class="dropdown-menu border-0 shadow">
            <li>
                <a href="{{ route('penerimaan-gudang-inputan') }}"
                    class="dropdown-item {{ $routeName == 'penerimaan-gudang-inputan' ? 'active' : '' }}">
                    Penerimaan Gudang Inputan (FABRIC)
                </a>
            </li>
            <li>
                <a href="{{ route('penerimaan-gudang-inputan-accesories') }}"
                    class="dropdown-item {{ $routeName == 'penerimaan-gudang-inputan-accesories' ? 'active' : '' }}">
                    Penerimaan Gudang Inputan (ACCESORIES)
                </a>
            </li>
            <li>
                <a href="{{ route('penerimaan-gudang-inputan-fg') }}"
                    class="dropdown-item {{ $routeName == 'penerimaan-gudang-inputan-fg' ? 'active' : '' }}">
                    Penerimaan Gudang Inputan (FG)
                </a>
            </li>
        </ul>
    </li>
    <li class="nav-item dropdown">
        <a href="#" data-bs-toggle="dropdown" aria-haspopup="true"aria-expanded="false"
            class="nav-link dropdown-toggle {{ $subPageGroup == 'pengeluaran-whs-soljer' ? 'active' : '' }}">Pengeluaran</a>
        <ul aria-labelledby="dropdownSubMenu1" class="dropdown-menu border-0 shadow">
            <li>
                <a href="{{ route('pengeluaran-gudang-inputan') }}"
                    class="dropdown-item {{ $routeName == 'pengeluaran-gudang-inputan' ? 'active' : '' }}">
                    Pengeluaran Gudang Inputan (FABRIC)
                </a>
            </li>
            <li>
                <a href="{{ route('pengeluaran-gudang-inputan-accesories') }}"
                    class="dropdown-item {{ $routeName == 'pengeluaran-gudang-inputan-accesories' ? 'active' : '' }}">
                    Pengeluaran Gudang Inputan (ACCESORIES)
                </a>
            </li>
            <li>
                <a href="{{ route('pengeluaran-gudang-inputan-fg') }}"
                    class="dropdown-item {{ $routeName == 'pengeluaran-gudang-inputan-fg' ? 'active' : '' }}">
                    Pengeluaran Gudang Inputan (FG)
                </a>
            </li>
        </ul>
    </li>
    <li class="nav-item dropdown">
        <a href="#" data-bs-toggle="dropdown" aria-haspopup="true"aria-expanded="false"
            class="nav-link dropdown-toggle {{ $subPageGroup == 'laporan-whs-soljer' ? 'active' : '' }}">Laporan</a>
        <ul aria-labelledby="dropdownSubMenu1" class="dropdown-menu border-0 shadow">
            <li>
                <a href="{{ route('laporan-penerimaan-per-kategori') }}"
                    class="dropdown-item {{ $routeName == 'laporan-penerimaan-per-kategori' ? 'active' : '' }}">
                    Penerimaan Per Kategori
                </a>
            </li>
            <li>
                <a href="{{ route('laporan-pengeluaran-per-kategori') }}"
                    class="dropdown-item {{ $routeName == 'laporan-pengeluaran-per-kategori' ? 'active' : '' }}">
                    Pengeluaran Per Kategori
                </a>
            </li>
            <li>
                <a href="{{ route('laporan-mutasi-per-kategori') }}"
                    class="dropdown-item {{ $routeName == 'laporan-mutasi-per-kategori' ? 'active' : '' }}">
                    Mutasi Per Kategori
                </a>
            </li>
        </ul>
    </li>
@endrole