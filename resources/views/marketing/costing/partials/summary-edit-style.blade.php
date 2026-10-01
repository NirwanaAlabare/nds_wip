{{-- Style untuk edit langsung / blok / copy-paste di tabel summary costing --}}
<style>
    .xl-hint {
        font-size: 12px;
        color: #64748b;
    }

    .xl-th-edit::after {
        content: "\270E";
        margin-left: 4px;
        font-weight: 700;
    }

    .xl-table tbody tr.row-costing > td {
        position: relative;
        cursor: cell;
        user-select: none;
    }
    .xl-table tbody tr.row-costing > td:last-child { cursor: default; }

    .xl-table tbody td.xl-sel { background: #dbeafe !important; }
    .xl-table tbody td.xl-active {
        background: #fff !important;
        outline: 2px solid var(--sb-blue);
        outline-offset: -2px;
    }

    .xl-table tbody td.xl-saving { background: #fff4ce !important; }
    .xl-table tbody td.xl-saved { animation: xl-saved 1.2s ease-out; }
    @keyframes xl-saved {
        from { background: #c6efce; }
        to   { background: transparent; }
    }

    /* ============ TABEL SUMMARY MENYESUAIKAN LEBAR LAYAR ============ */
    .xl-table th {
        white-space: normal;
        vertical-align: middle;
        line-height: 1.2;
    }
    .xl-table td { vertical-align: middle; }

    /* Teks panjang turun baris, angka tetap satu baris */
    .xl-table td.item-name   { white-space: normal; min-width: 180px; }
    .xl-table td.desc-td,
    .xl-table td.supplier-td { white-space: normal; min-width: 100px; }
    .xl-table td.text-right,
    .xl-table td.text-center { white-space: nowrap; }

    /* Tombol aksi boleh bertumpuk supaya kolom ACT tidak lebar */
    .xl-table td:last-child { white-space: normal; }
    .xl-table td:last-child .btn { margin: 1px 0 !important; }

    /* Dropdown (Item/Set/Supplier/Curr/Unit) yang muncul di dalam sel saat diedit */
    .xl-table td > .select2-container {
        position: absolute !important;
        inset: 0;
        width: 100% !important;
        min-width: 140px;
        z-index: 2;
    }
    .xl-table td > .select2-container .select2-selection {
        height: 100% !important;
        min-height: 100%;
        border: 0;
        border-radius: 0;
        outline: 2px solid var(--sb-blue);
        outline-offset: -2px;
        font-size: 12px;
    }

    .xl-editor {
        position: absolute;
        inset: 0;
        width: 100%;
        height: 100%;
        border: 0;
        padding: 2px 6px;
        font: inherit;
        background: #fff;
        outline: 2px solid var(--sb-blue);
        outline-offset: -2px;
        z-index: 2;
    }
</style>
