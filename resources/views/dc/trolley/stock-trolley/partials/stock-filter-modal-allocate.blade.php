{{-- Modal filter untuk tabel Alokasi (layout kolom : partials/stock-table-allocate), opsi diisi otomatis dari data tabel --}}
@include('dc.trolley.stock-trolley.partials.stock-filter-modal', [
    // [label, kolom datatable, mode pencocokan, opsi, lebar kolom]
    'filterSelects' => [
        ['No. WS', 9, 'exact', [], 'col-6'],
        ['No. Cut', 4, 'exact', [], 'col-6'],
        ['Style', 10, 'exact', [], 'col-6'],
        ['Color', 11, 'exact', [], 'col-6'],
        ['Panel', 5, 'contains', [], 'col-6'],
        ['Size', 12, 'exact', [], 'col-6'],
        ['Part', 6, 'contains', [], 'col-12'],
    ],
])
