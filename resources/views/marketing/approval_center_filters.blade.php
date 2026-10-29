{{-- Filter tanggal + Search By + kata kunci untuk tabel di Approval Center. --}}
<div class="ac-date-wrap">
    <button type="button" class="ac-date-toggle" title="Filter tanggal"><i class="far fa-calendar-alt"></i></button>
    <div class="ac-date-panel">
        <label class="mb-1">Dari</label>
        <input type="date" class="form-control ac-date-from">
        <label class="mb-1 mt-2">Sampai</label>
        <input type="date" class="form-control ac-date-to">
        <button type="button" class="mkt-btn mkt-btn--green mkt-btn--sm w-100 mt-2 ac-date-apply">Terapkan</button>
    </div>
</div>
<div class="ac-search-by-wrap">
    <i class="fas fa-filter ac-input-icon"></i>
    <select class="form-control ac-search-by" multiple>
        @foreach ($searchColumns as $value => $label)
            <option value="{{ $value }}">{{ $label ?? $numberLabel }}</option>
        @endforeach
    </select>
</div>
<div class="ac-keyword-wrap">
    <i class="fas fa-search ac-input-icon"></i>
    <input type="search" class="form-control ac-keyword" placeholder="Search">
</div>
