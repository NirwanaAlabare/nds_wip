@role('master_data')
    <li class="nav-item dropdown">
        <a href="#" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false"
            class="nav-link dropdown-toggle {{ (isset($subPageGroup) && $subPageGroup == 'master-data') ? 'active' : '' }}">
            Master Data
        </a>
        <ul aria-labelledby="dropdownSubMenu1" class="dropdown-menu border-0 shadow">
            <li>
                <a href="{{ route('master-data-customer') }}"
                    class="dropdown-item {{ (isset($subPage) && $subPage == 'master-customer') ? 'active' : '' }}">
                    Master Customer <i class="fa-solid fa-user-tie fa-sm"></i>
                </a>
            </li>
            <li>
                <a href="{{ route('master-data-supplier') }}"
                    class="dropdown-item {{ (isset($subPage) && $subPage == 'master-supplier') ? 'active' : '' }}">
                    Master Supplier <i class="fa-solid fa-truck-field fa-sm"></i>
                </a>
            </li>
            <li>
                <a href="{{ route('master-data-item') }}"
                    class="dropdown-item {{ (isset($subPage) && $subPage == 'master-item') ? 'active' : '' }}">
                    Master Item General <i class="fa-solid fa-boxes-stacked fa-sm"></i>
                </a>
            </li>
        </ul>
    </li>
@endrole
