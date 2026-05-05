@role('cutting')
    <li class="nav-item dropdown">
        <a href="#" data-bs-toggle="dropdown" aria-haspopup="true"aria-expanded="false"
            class="nav-link dropdown-toggle {{ $subPageGroup == 'proses-cutting' ? 'active' : '' }}">Process</a>
        <ul aria-labelledby="dropdownSubMenu1" class="dropdown-menu border-0 shadow">
            <li>
                <a href="{{ route('spreading') }}"
                    class="dropdown-item {{ $routeName == 'spreading' ? 'active' : '' }}">
                    Spreading <i class="fas fa-scroll fa-sm"></i>
                </a>
            </li>
            <li>
                <a href="{{ route('penerimaan-cutting') }}"
                    class="appeared dropdown-item {{ $routeName == 'penerimaan-cutting' ? 'active' : '' }}">
                    Penerimaan Fabric Cutting <i class="fa-solid fa-tape"></i>
                </a>
            </li>
            <li>
                <a href="{{ route('form-cut-input') }}"
                    class="appeared dropdown-item {{ $routeName == 'form-cut-input' ? 'active' : '' }}">
                    Form Cutting <i class="fas fa-cut fa-sm"></i>
                </a>
            </li>
            <li>
                <a href="{{ route('cutting-piece') }}"
                    class="dropdown-item {{ $routeName == 'cutting-piece' ? 'active' : '' }}">
                    Form Pieces <i class="fa-solid fa-shirt"></i>
                </a>
            </li>
            <li>
                <a href="{{ route('form-cut-piping') }}"
                    class="dropdown-item {{ $routeName == 'form-cut-piping' ? 'active' : '' }}">
                    Piping <i class="fa-solid fa-paperclip"></i>
                </a>
            </li>
        </ul>
    </li>

    @strictmeja
        <li class="nav-item dropdown">
            <a href="#" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false"
                class="nav-link dropdown-toggle {{ $subPageGroup == 'cuttingplan-cutting' ? 'active' : '' }}">
                Plan
            </a>
            <ul aria-labelledby="dropdownSubMenu2" class="dropdown-menu border-0 shadow">
                <li>
                    <a href="{{ route('cut-plan') }}"
                        class="dropdown-item {{ $routeName == 'cut-plan' ? 'active' : '' }}">
                        Daily Cutting Plan <i class="fas fa-map fa-sm"></i>
                    </a>
                </li>
                {{-- Deprecated --}}
                {{--
                    <li>
                        <a href="{{ route('cut-plan-output') }}" class="dropdown-item {{ $subPage == 'cut-plan-output' ? 'active' : '' }}">
                            Cutting Plan Output <i class="fa-solid fa-map-location"></i>
                        </a>
                    </li>
                --}}
            </ul>
        </li>
        <li class="nav-item dropdown">
            <a href="#" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false"
                class="nav-link dropdown-toggle {{ $subPage == 'manage-cutting' ? 'active' : '' }}">Completed
            </a>
            <ul aria-labelledby="dropdownSubMenu2" class="dropdown-menu border-0 shadow">
                <li>
                    <a href="{{ route('manage-cutting') }}"
                        class="dropdown-item {{ $routeName == 'manage-cutting' ? 'active' : '' }}">
                        Completed Form <i class="fa-solid fa-check-to-slot"></i>
                    </a>
                </li>
            </ul>
        </li>
        <li class="nav-item dropdown">
            <a href="#" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false"
                class="nav-link dropdown-toggle {{ $subPageGroup == 'laporan-cutting' ? 'active' : '' }}">Roll</a>
            <ul aria-labelledby="dropdownSubMenu2" class="dropdown-menu border-0 shadow">
                <li>
                    <a href="{{ route('sisa_kain_roll') }}"
                        class="dropdown-item {{ $subPage == 'sisa-kain-roll' ? 'active' : '' }}">
                        Bintex Sisa Kain <i class="fa-solid fa-toilet-paper-slash fa-sm"></i>
                    </a>
                </li>
                <li>
                    <a href="{{ route('alokasi_fabric_gr_panel') }}"
                        class="dropdown-item {{ $subPage == 'alokasi-fabric-gr-panel' ? 'active' : '' }}">
                        Alokasi Fabric GR Panel <i class="fa-solid fa-toilet-paper fa-sm"></i>
                    </a>
                </li>
            </ul>
        </li>
        <li class="nav-item dropdown">
            <a href="#" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false"
                class="nav-link dropdown-toggle {{ $subPageGroup == 'cutting-report' ? 'active' : '' }}">Report</a>
            <ul aria-labelledby="dropdownSubMenu2" class="dropdown-menu border-0 shadow">
                <li>
                    <a href="{{ route('report-cutting') }}"
                        class="dropdown-item {{ $subPage == 'cutting' ? 'active' : '' }}">
                        Output Cutting <i class="fa fa-file"></i>
                    </a>
                </li>
                <li>
                    <a href="{{ route('report-cutting-daily') }}"
                        class="dropdown-item {{ $subPage == 'cutting-daily' ? 'active' : '' }}">
                        Output Cutting Daily <i class="fa fa-file"></i>
                    </a>
                </li>
                <li>
                    <a href="{{ route('track-cutting-output') }}"
                        class="dropdown-item {{ $subPage == 'cutting-track' ? 'active' : '' }}">
                        Track Cutting Output <i class="fa fa-file"></i>
                    </a>
                </li>
                <li>
                    <a href="{{ route('lap_pemakaian') }}"
                        class="dropdown-item {{ $subPage == 'lap-pemakaian' ? 'active' : '' }}">
                        Manajemen Roll <i class="fa-solid fa-toilet-paper fa-sm"></i>
                    </a>
                </li>
                <li>
                    <a href="{{ route('pemakaian-roll') }}"
                        class="dropdown-item {{ $routeName == 'pemakaian-roll' ? 'active' : '' }}">
                        Pemakaian Kain <i class="fa fa-file"></i>
                    </a>
                </li>
                <li>
                    <a href="{{ route('cutting-dashboard-list') }}"
                        class="dropdown-item {{ $subPage == 'form-cut-piping' ? 'active' : '' }}"
                        target="_blank">
                        Dashboard <i class="fa-solid fa-pager"></i>
                    </a>
                </li>
                <li>
                    <a href="{{ route('report_cutting_mutasi_fabric') }}"
                        class="dropdown-item {{ $routeName == 'report_cutting_mutasi_fabric' ? 'active' : '' }}">
                        Mutasi Fabric <i class="fa fa-list"></i>
                    </a>
                </li>
                <li>
                    <a href="{{ route('report_cutting_mutasi_fabric_proporsional') }}"
                        class="dropdown-item {{ $routeName == 'report_cutting_mutasi_fabric_proporsional' ? 'active' : '' }}">
                        Mutasi Fabric Proporsional <i class="fa fa-list"></i>
                    </a>
                </li>
                <li>
                    <a href="{{ route('roll_fabric_cutting_in') }}"
                        class="dropdown-item {{ $subPage == 'roll_fabric_cutting_in' ? 'active' : '' }}">
                        Penerimaan Fabric <i class="fa-solid fa-box-open fa-sm"></i>
                    </a>
                </li>
                <li>
                    <a href="{{ route('report_gr_set') }}"
                        class="dropdown-item {{ $routeName == 'report_gr_set' ? 'active' : '' }}">
                        Ganti Reject Set <i class="fa fa-list"></i>
                    </a>
                </li>
                <li>
                    <a href="{{ route('report_gr_panel') }}"
                        class="dropdown-item {{ $routeName == 'report_gr_panel' ? 'active' : '' }}">
                        Ganti Reject Panel <i class="fa fa-list"></i>
                    </a>
                </li>
                <li>
                    <a href="{{ route('report_mutasi_wip_cutting') }}"
                        class="dropdown-item {{ $routeName == 'report_mutasi_wip_cutting' ? 'active' : '' }}">
                        Mutasi WIP Cutting <i class="fa fa-list"></i>
                    </a>
                </li>
                <li>
                    <a href="{{ route('report_mutasi_wip_cutting_detail') }}"
                        class="dropdown-item {{ $routeName == 'report_mutasi_wip_cutting_detail' ? 'active' : '' }}">
                        Mutasi WIP Cutting Detail <i class="fa fa-list"></i>
                    </a>
                </li>
                <li>
                    <a href="{{ route('report_pengeluaran_cutting') }}"
                        class="dropdown-item {{ $routeName == 'report_pengeluaran_cutting' ? 'active' : '' }}">
                        Pengeluaran Cutting <i class="fa fa-list"></i>
                    </a>
                </li>
                <li>
                    <a href="{{ route('report_pengeluaran_cutting_panel') }}"
                        class="dropdown-item {{ $routeName == 'report_pengeluaran_cutting_panel' ? 'active' : '' }}">
                        Pengeluaran Cutting Panel <i class="fa fa-list"></i>
                    </a>
                </li>
                <li>
                    <a href="{{ route('report_return_fabric_cutting') }}"
                        class="dropdown-item {{ $routeName == 'report_return_fabric_cutting' ? 'active' : '' }}">
                        Return Fabric Cutting <i class="fa fa-list"></i>
                    </a>
                </li>
            </ul>
        </li>
    @endstrictmeja
    <li class="nav-item dropdown">
        <a href="#" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false"
            class="nav-link dropdown-toggle {{ $subPageGroup == 'cutting-piping' ? 'active' : '' }}">Piping</a>
        <ul aria-labelledby="dropdownSubMenu2" class="dropdown-menu border-0 shadow">
            <li>
                <a href="{{ route('master-piping') }}"
                    class="dropdown-item {{ $routeName == 'master-piping' ? 'active' : '' }}">
                    Master <i class="fa-solid fa-gear"></i>
                </a>
            </li>
            <li>
                <a href="{{ route('piping-process') }}"
                    class="dropdown-item {{ $routeName == 'piping-process' ? 'active' : '' }}">
                    Process <i class="fa-solid fa-ring"></i>
                </a>
            </li>
            <li>
                <a href="{{ route('piping-loading') }}"
                    class="dropdown-item {{ $routeName == 'piping-loading' ? 'active' : '' }}">
                    Loading <i class="fa-solid fa-right-to-bracket"></i>
                </a>
            </li>
            <li>
                <a href="{{ route('piping-stock') }}"
                    class="dropdown-item {{ $routeName == 'piping-stock' ? 'active' : '' }}">
                    Stock <i class="fa-solid fa-receipt"></i>
                </a>
            </li>
        </ul>
    </li>
    <li class="nav-item dropdown">
        <a href="#" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false"
            class="nav-link dropdown-toggle {{ $subPageGroup == 'cutting-reject' ? 'active' : '' }}">Reject</a>
        <ul aria-labelledby="dropdownSubMenu2" class="dropdown-menu border-0 shadow">
            <li>
                <a href="{{ route('cutting-reject') }}"
                    class="dropdown-item {{ $routeName == 'cutting-reject' ? 'active' : '' }}">
                    Form <i class="fa-solid fa-file-circle-exclamation"></i>
                </a>
            </li>
            <li>
                <a href="{{ route('stock-cutting-reject') }}"
                    class="dropdown-item {{ $routeName == 'stock-cutting-reject' ? 'active' : '' }}">
                    Stocker <i class="fa-solid fa-receipt"></i>
                </a>
            </li>
            <li>
                <a href="{{ route('form_gr_panel') }}"
                    class="dropdown-item {{ $routeName == 'form_gr_panel' ? 'active' : '' }}">
                    Form GR Panel <i class="fa-solid fa-file"></i>
                </a>
            </li>
        </ul>
    </li>
@endrole
@role('superadmin')
    <li class="nav-item">
        <a href="{{ route('cutting-tools') }}"
            class="nav-link {{ $routeName == 'cutting-tools' ? 'active' : '' }}" target="_blank">
            <i class="fa-solid fa-toolbox"></i>
        </a>
    </li>
@endrole
