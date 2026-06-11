@php
    $currentRoute = Route::currentRouteName();
@endphp
<ul class="nav nav-tabs mb-3" id="tab-create-ri" role="tablist">
    <li class="nav-item" role="presentation">
        <a class="nav-link {{ $currentRoute === 'create-retur-inmaterial' ? 'active' : '' }}"
           href="{{ route('create-retur-inmaterial') }}">
            <i class="fa-solid fa-file-lines me-1"></i> Retur from Supplier
        </a>
    </li>
    <li class="nav-item" role="presentation">
        <a class="nav-link {{ $currentRoute === 'create-retur-inmaterial-cutting' ? 'active' : '' }}"
           href="{{ route('create-retur-inmaterial-cutting') }}">
            <i class="fa-solid fa-scissors me-1"></i> Retur from Cutting
        </a>
    </li>
    <li class="nav-item" role="presentation">
        <a class="nav-link {{ $currentRoute === 'create-retur-inmaterial-barcode' ? 'active' : '' }}"
           href="{{ route('create-retur-inmaterial-barcode') }}">
            <i class="fa-solid fa-barcode me-1"></i> Retur New
        </a>
    </li>
</ul>
