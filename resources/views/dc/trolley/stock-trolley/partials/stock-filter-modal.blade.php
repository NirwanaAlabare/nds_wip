{{--
    Modal filter tabel stok trolley (Kirim & Alokasi).

    Pencarian No. Stocker ada di luar modal (partials/stock-search-input).

    Parameter :
    - $filterSelects : [[label, index kolom, 'exact' | 'contains', opsi (array / collection), class kolom grid], ...]
                       Opsi boleh kosong, nanti diisi otomatis dari data tabel (refreshStockFilterOptions).

    Letakkan di luar <form> supaya Enter tidak men-submit form.
--}}
<div class="modal fade" id="filterStockModal" tabindex="-1" aria-labelledby="filterStockModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable modal-fullscreen-sm-down">
        <div class="modal-content">
            <div class="modal-header bg-sb text-light">
                <h5 class="modal-title fw-bold" id="filterStockModalLabel"><i class="fas fa-filter fa-sm"></i> Filter Stocker</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row g-2" id="filter-stock-fields">
                    @foreach ($filterSelects as [$filterLabel, $filterColumn, $filterMatch, $filterOptions, $filterCol])
                        <div class="{{ $filterCol }}">
                            <label class="form-label small mb-1">{{ $filterLabel }}</label>
                            <select class="form-control form-control-sm filter-select" data-column="{{ $filterColumn }}" data-match="{{ $filterMatch }}">
                                <option value=""></option>
                                @foreach ($filterOptions as $filterOption)
                                    <option value="{{ $filterOption }}">{{ $filterOption }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endforeach
                </div>
            </div>
            <div class="modal-footer justify-content-between">
                <button type="button" class="btn btn-outline-danger btn-sm" onclick="resetStockFilter()"><i class="fas fa-undo fa-sm"></i> Reset</button>
                <button type="button" class="btn btn-primary btn-sm" onclick="applyStockFilter()"><i class="fas fa-check fa-sm"></i> Terapkan</button>
            </div>
        </div>
    </div>
</div>
