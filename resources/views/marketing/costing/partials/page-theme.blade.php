{{-- Background lembut & animasi halus untuk halaman costing (create & edit) --}}
<style>
    /* ============ BACKGROUND NYAMAN DI MATA ============ */
    .content-wrapper {
        background:
            radial-gradient(circle at 0% 0%, rgba(48, 133, 214, 0.07), transparent 40%),
            radial-gradient(circle at 100% 100%, rgba(30, 58, 138, 0.06), transparent 45%),
            #f3f6fa;
    }

    .card-sb > .card-body {
        background: linear-gradient(180deg, #f8fafd 0%, #f1f5fa 100%);
        color: #1f2937;
    }

    .form-section {
        border-color: #e6ecf3;
        transition: box-shadow 0.25s ease, transform 0.25s ease, border-color 0.25s ease;
    }
    .form-section:hover,
    .form-section:focus-within {
        border-color: #d3e2f3;
        box-shadow: 0 8px 22px rgba(15, 23, 42, 0.07);
    }
    .form-section:hover { transform: translateY(-1px); }

    /* ============ ANIMASI MASUK HALAMAN ============ */
    @keyframes costing-fade-up {
        from { opacity: 0; transform: translateY(12px); }
        to   { opacity: 1; transform: translateY(0); }
    }

    .card-sb {
        animation: costing-fade-up 0.45s ease-out backwards;
    }
    .card-sb .card-body > .form-section,
    .card-sb .card-body > form > .form-section,
    .card-sb .card-body > .table-responsive,
    .card-sb .card-body > h5,
    .card-sb .card-body > h6 {
        animation: costing-fade-up 0.5s ease-out backwards;
    }
    .card-sb .form-section:nth-of-type(1) { animation-delay: 0.05s; }
    .card-sb .form-section:nth-of-type(2) { animation-delay: 0.12s; }
    .card-sb .form-section:nth-of-type(3) { animation-delay: 0.19s; }
    .card-sb .form-section:nth-of-type(4) { animation-delay: 0.26s; }
    .card-sb .form-section:nth-of-type(5) { animation-delay: 0.33s; }

    /* ============ FIELD & TOMBOL ============ */
    .form-control,
    .select2-container--bootstrap4 .select2-selection {
        transition: border-color 0.2s ease, box-shadow 0.2s ease, background-color 0.2s ease;
    }
    .form-control:hover:not(:focus):not([readonly]):not(:disabled) {
        border-color: #c7d6e8;
    }

    .card-sb .btn {
        transition: transform 0.15s ease, box-shadow 0.2s ease, filter 0.2s ease;
    }
    .card-sb .btn:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(15, 23, 42, 0.15);
    }
    .card-sb .btn:active {
        transform: translateY(0) scale(0.97);
        box-shadow: none;
    }

    /* ============ BARIS DETAIL: baru / di-update / dihapus ============ */
    @keyframes costing-row-in {
        0%   { background-color: #d1fae5; }
        100% { background-color: transparent; }
    }
    tr.row-new > td { animation: costing-row-in 1.4s ease-out; }

    .table tbody tr.row-costing > td { transition: background-color 0.2s ease; }
    .table tbody tr.row-costing:hover > td { background-color: #f5f9ff; }

    /* Hormati pengaturan "kurangi animasi" di sistem */
    @media (prefers-reduced-motion: reduce) {
        .card-sb *,
        .card-sb {
            animation: none !important;
            transition: none !important;
        }
    }
</style>
