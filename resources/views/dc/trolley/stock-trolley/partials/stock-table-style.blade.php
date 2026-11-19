{{--
    Style bersama tabel stok trolley (Kirim & Alokasi).
    Pakai class "stock-table" pada <table>. Kolom yang disembunyikan per ukuran layar diatur di masing-masing halaman.
--}}
<style>
    /* No. Stocker : satu stocker per baris */
    .stock-table td.col-stocker {
        white-space: normal !important;
        min-width: 120px;
    }

    .stock-table .stocker-list {
        display: flex;
        flex-direction: column;
        gap: 2px;
        font-weight: 700;
    }

    .stock-table .stocker-chip {
        white-space: nowrap;
    }

    /* Kolom gabungan WS / Style / Color */
    .stock-table td.col-wsc {
        white-space: normal !important;
        min-width: 130px;
        line-height: 1.35;
    }

    .stock-table .col-wsc .wsc-ws {
        font-weight: 600;
    }

    .stock-table .col-wsc .wsc-style {
        font-size: .85em;
        color: #6c757d;
    }

    /* Color lebih menonjol dibanding style */
    .stock-table .col-wsc .wsc-color {
        display: inline-flex;
        align-items: center;
        gap: .35rem;
        margin-top: .2rem;
        padding: .05rem .45rem;
        border-radius: .25rem;
        background-color: #fff3cd;
        color: #212529;
        font-size: .85em;
        font-weight: 600;
    }

    /* Titik warna sesuai nama color */
    .stock-table .color-dot {
        flex: 0 0 auto;
        width: .7rem;
        height: .7rem;
        border-radius: 50%;
        border: 1px solid rgba(0, 0, 0, .25);
        background-color: transparent;
    }

    /* Color tidak dikenali : titik abu transparan */
    .stock-table .color-dot.unknown {
        background-color: rgba(108, 117, 125, .25);
        border: 1px dashed rgba(108, 117, 125, .6);
    }

    /* Kolom gabungan Size / Qty */
    .stock-table td.col-sq {
        text-align: center;
        line-height: 1.2;
        min-width: 64px;
    }

    .stock-table .col-sq .sq-size {
        display: inline-block;
        padding: .1rem .5rem;
        border-radius: .25rem;
        background-color: #e9ecef;
        font-size: .8em;
        font-weight: 600;
    }

    .stock-table .col-sq .sq-qty {
        margin-top: .3rem;
        font-size: 1.15em;
        font-weight: 700;
    }

    /* Baris dengan selisih qty minus (diff_qty < 0) : garis merah lembut di sisi kiri No. Stocker */
    .stock-table tbody tr.has-qty-diff > td.col-stocker {
        box-shadow: inset 3px 0 0 #f1aeb5;
    }

    .stock-table .qty-diff {
        color: #dc3545;
        font-weight: 600;
    }

    /* Pencarian No. Stocker */
    .stock-search {
        position: relative;
        width: 220px;
    }

    .stock-search .stock-search-icon {
        position: absolute;
        top: 50%;
        left: .6rem;
        color: #adb5bd;
        font-size: .8rem;
        transform: translateY(-50%);
        pointer-events: none;
    }

    .stock-search .stock-filter-input {
        padding-left: 1.8rem;
    }

    @media (max-width: 575.98px) {
        .stock-search {
            flex: 1 1 100%;
            width: 100%;
        }
    }

    /* Tombol filter */
    .filter-count {
        font-size: .7rem;
    }

    /* Chip filter aktif */
    .filter-active-chips {
        display: inline-flex;
        flex-wrap: wrap;
        align-items: center;
        gap: .35rem;
    }

    .filter-active-chips:empty {
        display: none;
    }

    .filter-chip {
        display: inline-flex;
        align-items: center;
        gap: .25rem;
        max-width: 240px;
        padding: .15rem .25rem .15rem .6rem;
        border: 1px solid #b6d4fe;
        border-radius: 999px;
        background-color: #e7f1ff;
        color: #084298;
        font-size: .8rem;
        line-height: 1.3;
    }

    .filter-chip-label {
        flex: 0 0 auto;
        opacity: .75;
    }

    .filter-chip-value {
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        font-weight: 600;
    }

    .filter-chip-remove {
        flex: 0 0 auto;
        width: 1.2rem;
        height: 1.2rem;
        padding: 0;
        border: 0;
        border-radius: 50%;
        background: transparent;
        color: inherit;
        font-size: 1rem;
        line-height: 1;
        cursor: pointer;
    }

    .filter-chip-remove:hover {
        background-color: rgba(8, 66, 152, .15);
    }

    /* Action bar bawah (sticky : tetap selebar area konten, tidak menutupi sidebar) */
    .stock-action-bar {
        position: sticky;
        bottom: 1.5rem; /* sedikit mengambang di atas tepi bawah layar */
        z-index: 1020;
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: .75rem 1.25rem;
        min-height: 80px;
        margin: 1rem 0 .5rem;
        padding: 1rem 1.25rem 1.5rem;
        border-radius: .5rem;
        border-top: 3px solid #0d6efd;
        background-color: #fff;
        box-shadow: 0 -.5rem 1.25rem rgba(0, 0, 0, .12);
        font-size: 1rem;
    }

    .stock-action-group {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: .6rem;
        min-width: 0;
    }

    .stock-action-bar .filter-chip {
        font-size: .875rem;
        padding: .25rem .3rem .25rem .7rem;
    }

    .stock-action-bar .stock-summary {
        padding: .45rem 1rem;
        font-size: 1rem;
    }

    .stock-action-bar .stock-summary #stock-summary-count,
    .stock-action-bar .stock-summary #stock-summary-qty {
        font-size: 1.15rem;
        font-weight: 700;
    }

    @media (max-width: 575.98px) {
        /* Setiap grup satu baris penuh */
        .stock-action-bar {
            bottom: .75rem;
            padding: .75rem 1rem 1.25rem;
            gap: .6rem;
        }

        .stock-action-group {
            width: 100%;
        }
    }

    /* Ringkasan (jumlah & total qty) */
    .stock-summary {
        display: inline-flex;
        align-items: center;
        gap: .35rem;
        padding: .25rem .75rem;
        border-radius: 999px;
        background-color: #f1f3f5;
        color: #495057;
        font-size: .875rem;
    }

    .stock-summary .stock-summary-divider {
        opacity: .5;
    }

    /*
        Layout tabel halaman Alokasi (class "stock-table layout-allocate"), urutan kolom di DOM (nth-child) :
        1 Act | 2 No. Stocker | 3 WS / Style / Color | 4 Size / Qty | 5 No. Cut | 6 Panel | 7 Part | 8 Range | 9 By
        (kolom No. WS, Style, Color, Size mentah disembunyikan DataTables, tidak ada di DOM)
    */
    .stock-table.layout-allocate td:nth-child(1) {
        width: 1%;
        vertical-align: top;
    }

    @media (max-width: 991.98px) {
        /* Sembunyikan Panel, Range, By */
        .stock-table.layout-allocate tr > :nth-child(6),
        .stock-table.layout-allocate tr > :nth-child(8),
        .stock-table.layout-allocate tr > :nth-child(9) {
            display: none;
        }
    }

    @media (max-width: 575.98px) {
        /* Sembunyikan juga Part */
        .stock-table.layout-allocate tr > :nth-child(7) {
            display: none;
        }
    }

    /* Tablet & HP : tabel lebih rapat */
    @media (max-width: 991.98px) {
        .stock-table {
            font-size: .85rem;
        }

        .stock-table th,
        .stock-table td {
            padding: .45rem .5rem;
        }
    }

    @media (max-width: 575.98px) {
        .stock-table td.col-stocker,
        .stock-table td.col-wsc {
            min-width: 0;
        }
    }
</style>
