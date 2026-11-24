@role('master_data')
    <li class="nav-item dropdown">
        <a href="#" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false"
            class="nav-link dropdown-toggle {{ (isset($subPageGroup) && $subPageGroup == 'master-data') ? 'active' : '' }}">
            Master Data
        </a>
        <ul aria-labelledby="dropdownSubMenu1" class="dropdown-menu border-0 shadow md-nav-menu">
            <li>
                <a href="{{ route('dashboard-master-data') }}"
                    class="dropdown-item {{ (isset($subPage) && $subPage == 'dashboard') ? 'active' : '' }}">
                    Dashboard <i class="fa-solid fa-gauge fa-sm"></i>
                </a>
            </li>
            <li><hr class="dropdown-divider"></li>
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
            {{-- Master General: hover (layar lebar) / klik (layar sentuh) membuka Group General & Item General --}}
            @php $diGeneral = isset($subPage) && in_array($subPage, ['master-item', 'master-group-general'], true); @endphp
            <li class="dropdown-submenu md-nav-general">
                <a href="#" role="button" aria-haspopup="true" aria-expanded="false"
                    class="dropdown-item md-nav-general-buka {{ $diGeneral ? 'active' : '' }}">
                    Master General <i class="fa-solid fa-boxes-packing fa-sm"></i>
                    <i class="fa-solid fa-chevron-right md-nav-panah"></i>
                </a>
                <ul class="dropdown-menu border-0 shadow">
                    <li>
                        <a href="{{ route('master-data-group-general') }}"
                            class="dropdown-item {{ (isset($subPage) && $subPage == 'master-group-general') ? 'active' : '' }}">
                            Group General <i class="fa-solid fa-layer-group fa-sm"></i>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('master-data-item') }}"
                            class="dropdown-item {{ (isset($subPage) && $subPage == 'master-item') ? 'active' : '' }}">
                            Item General <i class="fa-solid fa-boxes-stacked fa-sm"></i>
                        </a>
                    </li>
                </ul>
            </li>
        </ul>
    </li>

    <style>
        /* layouts/link: semua dropdown navbar overflow-y auto (max 80vh) - submenu di samping jadi terpotong.
           Menu Master Data pendek, jadi khusus menu ini dibiarkan overflow visible. */
        .navbar-nav .dropdown-menu.md-nav-menu { max-height: none !important; overflow: visible !important; }
        .md-nav-general > .md-nav-general-buka { display: flex; align-items: center; gap: 6px; }
        .md-nav-general .md-nav-panah { margin-left: auto; padding-left: 14px; font-size: 10px; opacity: .55; }
        .md-nav-general > .dropdown-menu { margin-left: 2px; }
        /* Layar sentuh: dibuka lewat klik (kelas show), hover tetap jalan di layar lebar */
        .md-nav-general > .dropdown-menu.show { display: block; }
        /* HP: submenu turun ke bawah, tidak keluar layar ke kanan */
        @media (max-width: 991.98px) {
            .md-nav-general > .dropdown-menu { position: static; margin: 0 0 4px 14px; box-shadow: none !important; }
        }
    </style>
    <script>
        // Klik "Master General": buka/tutup submenu tanpa menutup dropdown Master Data
        document.addEventListener('click', function (e) {
            var buka = e.target.closest('.md-nav-general-buka');
            if (!buka) { return; }
            e.preventDefault();
            e.stopPropagation();
            var menu = buka.nextElementSibling;
            var tampil = menu.classList.toggle('show');
            buka.setAttribute('aria-expanded', tampil ? 'true' : 'false');
        }, true);
    </script>
@endrole
