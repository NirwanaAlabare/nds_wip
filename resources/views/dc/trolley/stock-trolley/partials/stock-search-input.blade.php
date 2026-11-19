{{--
    Pencarian No. Stocker (di luar modal filter), langsung menyaring saat mengetik.
    Parameter : $stockerColumn = index kolom datatable No. Stocker
--}}
<div class="stock-search">
    <i class="fas fa-search stock-search-icon"></i>
    <input type="search" class="form-control form-control-sm stock-filter-input" id="filter-stocker-input" data-column="{{ $stockerColumn }}" data-label="No. Stocker" placeholder="Cari No. Stocker..." autocomplete="off">
</div>
