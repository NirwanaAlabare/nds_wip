{{-- Action bar bawah halaman Alokasi : tampil saat ada filter aktif (lihat updateAllocateActionBar) --}}
<div class="stock-action-bar d-none" id="stock-action-bar">
    <div class="stock-action-group" id="stock-action-summary"></div>
    <div class="stock-action-group">
        <span class="text-muted text-nowrap"><i class="fas fa-filter fa-sm"></i> Filter :</span>
        <span class="filter-active-chips" id="filter-active-chips"></span>
        <button type="button" class="btn btn-link text-danger p-0 text-nowrap" id="filter-reset-inline" onclick="resetStockFilter()">Reset filter</button>
    </div>
</div>
