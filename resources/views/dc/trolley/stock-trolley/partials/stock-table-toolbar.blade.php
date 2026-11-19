{{-- Toolbar tabel Alokasi : tombol filter & ringkasan (ringkasan pindah ke action bar bawah saat ada filter aktif) --}}
<div class="d-flex flex-wrap align-items-center gap-2 mb-3" id="stock-toolbar">
    <button type="button" class="btn btn-outline-primary btn-sm text-nowrap" id="st-filter-btn" data-bs-toggle="modal" data-bs-target="#filterStockModal">
        <i class="fas fa-filter fa-sm"></i> Filter
        <span class="badge rounded-pill bg-primary filter-count d-none" id="filter-count">0</span>
    </button>
    @include('dc.trolley.stock-trolley.partials.stock-search-input', ['stockerColumn' => 1])
    <span id="stock-summary-slot">
        <span class="stock-summary" id="stock-summary">
            <i class="fas fa-layer-group"></i>
            <span id="stock-summary-count">0</span> stok
            <span class="stock-summary-divider">&middot;</span>
            Total Qty <span class="fw-bold" id="stock-summary-qty">0</span>
        </span>
    </span>
</div>
