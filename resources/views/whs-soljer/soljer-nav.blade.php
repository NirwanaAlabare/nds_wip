@role('warehouse')
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
        </ul>
    </li>
@endrole