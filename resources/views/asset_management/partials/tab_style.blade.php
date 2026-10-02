{{-- Gaya bersama menu Tab IT: Transaksi Tab, Monitoring Tab, Opname Tab --}}
<style type="text/css">
    /* Warna mengikuti tema aplikasi (--sb-color navy, --sb-secondary-color teal di style.css) */
    .tt-wrap {
        --tt-navy: var(--sb-color, #082149);
        --tt-teal: var(--sb-secondary-color, #238380);
        --tt-ambil: #198754;
        --tt-kembali: #d97706;
        max-width: 560px;
        margin: 0 auto 24px;
    }

    .tt-card {
        background: #fff;
        border-radius: 18px;
        box-shadow: 0 10px 30px rgba(8, 33, 73, 0.12);
    }

    /* ---------- Header ---------- */
    .tt-hero {
        background: linear-gradient(135deg, var(--tt-navy) 0%, var(--tt-teal) 100%);
        color: #fff;
        padding: 18px 20px 16px;
        border-radius: 18px 18px 0 0;
    }

    .tt-hero-top {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .tt-hero-icon {
        width: 44px;
        height: 44px;
        flex-shrink: 0;
        border-radius: 12px;
        background: rgba(255, 255, 255, 0.16);
        display: grid;
        place-items: center;
        font-size: 20px;
    }

    .tt-hero-title {
        font-size: 18px;
        font-weight: 700;
        margin: 0;
        line-height: 1.2;
    }

    .tt-hero-sub {
        font-size: 12px;
        opacity: 0.8;
        margin: 2px 0 0;
    }

    .tt-stats {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 10px;
        margin-top: 14px;
    }

    .tt-stat {
        display: flex;
        align-items: center;
        gap: 10px;
        background: rgba(255, 255, 255, 0.12);
        border: 1px solid rgba(255, 255, 255, 0.18);
        border-radius: 12px;
        padding: 10px 12px;
    }

    .tt-stat-icon {
        width: 34px;
        height: 34px;
        flex-shrink: 0;
        border-radius: 10px;
        background: rgba(255, 255, 255, 0.18);
        display: grid;
        place-items: center;
        font-size: 15px;
    }

    .tt-stat-value {
        font-size: 22px;
        font-weight: 800;
        line-height: 1;
    }

    .tt-stat-label {
        font-size: 11px;
        opacity: 0.85;
        margin-top: 3px;
    }

    /* ---------- Isi ---------- */
    .tt-body {
        padding: 18px 20px 0;
    }

    .tt-section {
        margin-bottom: 16px;
    }

    .tt-section-label {
        display: flex;
        align-items: center;
        gap: 6px;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        color: #6c757d;
        margin-bottom: 6px;
    }

    .tt-section-label i {
        color: var(--tt-teal);
    }

    /* Pilihan Mode/Aksi: segmented control, besar supaya enak dipencet di HP */
    .tt-seg {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 4px;
        background: #f1f3f5;
        padding: 4px;
        border-radius: 12px;
    }

    .tt-seg-btn {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        margin: 0;
        padding: 10px 8px;
        border-radius: 9px;
        font-weight: 600;
        font-size: 14px;
        color: #6c757d;
        cursor: pointer;
        user-select: none;
        transition: background 0.2s ease, color 0.2s ease, box-shadow 0.2s ease;
    }

    .tt-seg-btn:hover {
        color: var(--tt-navy);
    }

    .btn-check:checked + .tt-seg-btn {
        background: #fff;
        color: var(--tt-navy);
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.08);
    }

    .btn-check:checked + .tt-seg-btn.is-ambil {
        color: var(--tt-ambil);
    }

    .btn-check:checked + .tt-seg-btn.is-kembali {
        color: var(--tt-kembali);
    }

    .btn-check:focus-visible + .tt-seg-btn {
        outline: 2px solid var(--tt-teal);
        outline-offset: 1px;
    }

    /* Keterangan mode aktif */
    .tt-banner {
        display: flex;
        align-items: flex-start;
        gap: 10px;
        padding: 10px 12px;
        margin-bottom: 16px;
        border-radius: 12px;
        font-size: 13px;
        background: #e8f5f4;
        color: #135e5c;
        border: 1px solid #cfe9e7;
    }

    .tt-banner i {
        margin-top: 2px;
    }

    .tt-banner.is-kembali {
        background: #fff6e6;
        color: #8a4b00;
        border-color: #fde2b5;
    }

    /* Input dengan ikon */
    .tt-input {
        position: relative;
    }

    /* Dipusatkan dengan flex, bukan transform, karena transform ikut diatur oleh CSS Font Awesome */
    .tt-input .tt-input-icon {
        position: absolute;
        left: 13px;
        top: 0;
        height: 42px;
        display: flex;
        align-items: center;
        color: #adb5bd;
        pointer-events: none;
        z-index: 5;
    }

    .tt-input .form-control {
        height: 42px;
        padding-left: 38px;
        border: 1.5px solid #dee2e6;
        border-radius: 10px;
        transition: border-color 0.2s ease, box-shadow 0.2s ease;
    }

    .tt-input .form-control:focus {
        border-color: var(--tt-teal);
        box-shadow: 0 0 0 0.2rem rgba(35, 131, 128, 0.15);
    }

    /* Nama karyawan terpilih: ikon lewat CSS supaya teks tetap diisi JS dengan .text() */
    #nikEmployeeName {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        margin-top: 8px;
        padding: 3px 10px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
        color: var(--tt-ambil);
        background: #e9f7ef;
    }

    #nikEmployeeName::before {
        font-family: "Font Awesome 6 Free";
        font-weight: 900;
        content: "\f4fc";
    }

    #nikEmployeeName:empty {
        display: none;
    }

    #nikSuggestList {
        display: none;
        position: absolute;
        z-index: 1050;
        top: calc(100% + 4px);
        left: 0;
        right: 0;
        background: #fff;
        border: 1px solid #e9ecef;
        border-radius: 10px;
        max-height: 220px;
        overflow-y: auto;
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
    }

    #nikSuggestList .nik-suggest-item {
        padding: 8px 12px;
        font-size: 13px;
        cursor: pointer;
        border-bottom: 1px solid #f1f3f5;
    }

    #nikSuggestList .nik-suggest-item:last-child {
        border-bottom: 0;
    }

    #nikSuggestList .nik-suggest-item:hover {
        background: #e8f5f4;
    }

    #nikSuggestList .nik-suggest-item b {
        margin-right: 6px;
        color: var(--tt-navy);
    }

    /* ---------- Scan ---------- */
    .tt-box-title {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 8px;
    }

    .tt-count {
        display: inline-block;
        padding: 3px 10px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 700;
        color: #fff;
        background: var(--tt-navy);
    }

    .tt-clear {
        border: 1px solid #f1aeb5;
        background: #fff;
        color: #dc3545;
        border-radius: 20px;
        padding: 2px 10px;
        font-size: 12px;
        font-weight: 600;
        transition: background 0.2s ease, color 0.2s ease;
    }

    .tt-clear:hover {
        background: #dc3545;
        color: #fff;
    }

    .tt-scan .form-control {
        height: 50px;
        font-size: 15px;
        font-weight: 600;
        border: 2px dashed #ced4da;
        background: #fbfcfd;
    }

    .tt-scan .form-control:focus {
        border-style: solid;
        background: #fff;
    }

    .tt-input.tt-scan .tt-input-icon {
        height: 50px;
        font-size: 16px;
    }

    .tt-scan:focus-within .tt-input-icon {
        color: var(--tt-teal);
    }

    /* Tanda "Siap scan" muncul saat kotak scan aktif */
    .tt-scan-ready {
        display: none;
        align-items: center;
        gap: 6px;
        position: absolute;
        right: 12px;
        top: 50%;
        transform: translateY(-50%);
        font-size: 11px;
        font-weight: 700;
        color: var(--tt-teal);
        pointer-events: none;
    }

    .tt-scan:focus-within .tt-scan-ready {
        display: inline-flex;
    }

    .tt-pulse {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background: var(--tt-teal);
        animation: tt-pulse 1.2s infinite;
    }

    @keyframes tt-pulse {
        0% { box-shadow: 0 0 0 0 rgba(35, 131, 128, 0.55); }
        70% { box-shadow: 0 0 0 8px rgba(35, 131, 128, 0); }
        100% { box-shadow: 0 0 0 0 rgba(35, 131, 128, 0); }
    }

    #scannedTagList {
        max-height: 280px;
        overflow-y: auto;
        margin-top: 10px;
        padding-right: 2px;
    }

    #scannedTagList:empty {
        display: none;
    }

    .scanned-tag-row {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 8px 10px;
        margin-bottom: 6px;
        background: #fff;
        border: 1px solid #edf0f2;
        border-left: 3px solid #ced4da;
        border-radius: 10px;
        font-size: 13px;
    }

    .scanned-tag-row.tag-ok {
        border-left-color: var(--tt-ambil);
    }

    .scanned-tag-row.tag-error {
        background: #fff5f5;
        border-color: #f8d7da;
        border-left-color: #dc3545;
    }

    .scanned-tag-row .tag-no {
        min-width: 26px;
        height: 22px;
        padding: 0 4px;
        border-radius: 6px;
        background: #f1f3f5;
        color: #6c757d;
        font-size: 11px;
        font-weight: 700;
        display: grid;
        place-items: center;
    }

    .scanned-tag-row .tag-status {
        width: 16px;
        font-size: 15px;
        text-align: center;
    }

    .scanned-tag-row .tag-code {
        flex: 1;
        min-width: 0;
        font-family: SFMono-Regular, Menlo, Consolas, "Liberation Mono", monospace;
        font-size: 12.5px;
        font-weight: 600;
        word-break: break-all;
    }

    .scanned-tag-row .tag-info {
        display: block;
        margin-top: 1px;
        font-family: var(--bs-body-font-family);
        font-size: 11px;
        font-weight: 400;
    }

    .tt-remove {
        border: 0;
        background: transparent;
        color: #adb5bd;
        width: 28px;
        height: 28px;
        border-radius: 8px;
        transition: background 0.2s ease, color 0.2s ease;
    }

    .tt-remove:hover {
        background: #f8d7da;
        color: #dc3545;
    }

    #tagSummary {
        font-size: 12px;
        margin-top: 6px;
    }

    #tagSummary:empty {
        display: none;
    }

    /* ---------- Tujuan (select2) ---------- */
    #tujuanBox .select2-container--bootstrap4 .select2-selection {
        border: 1.5px solid #dee2e6;
        border-radius: 10px;
    }

    /* ---------- Tombol proses: menempel di bawah layar saat daftar tag panjang ---------- */
    .tt-footer {
        position: sticky;
        bottom: 0;
        z-index: 5;
        padding: 12px 0 18px;
        background: linear-gradient(to top, #fff 75%, rgba(255, 255, 255, 0));
        border-radius: 0 0 18px 18px;
    }

    #btnSubmit {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        height: 52px;
        border-radius: 12px;
        font-size: 15px;
        font-weight: 700;
    }

    #btnSubmit.btn-success {
        box-shadow: 0 6px 16px rgba(25, 135, 84, 0.3);
    }

    #btnSubmit.btn-warning {
        color: #fff;
        background: var(--tt-kembali);
        border-color: var(--tt-kembali);
        box-shadow: 0 6px 16px rgba(217, 119, 6, 0.3);
    }

    #btnSubmit:disabled {
        box-shadow: none;
    }

    @media (max-width: 575.98px) {
        .tt-hero,
        .tt-body {
            padding-left: 14px;
            padding-right: 14px;
        }

        .tt-seg-btn {
            font-size: 13px;
        }
    }

    /* ---------- Dipakai bersama Monitoring Tab / Opname Tab ---------- */
    .tt-wrap.is-medium {
        max-width: 760px;
    }

    .tt-wrap.is-wide {
        max-width: 1200px;
    }

    .tt-hero-actions {
        margin-left: auto;
        display: flex;
        gap: 6px;
    }

    .tt-icon-btn {
        width: 36px;
        height: 36px;
        border-radius: 10px;
        border: 1px solid rgba(255, 255, 255, 0.25);
        background: rgba(255, 255, 255, 0.14);
        color: #fff;
        display: grid;
        place-items: center;
        font-size: 15px;
        text-decoration: none;
        transition: background 0.2s ease, opacity 0.2s ease;
    }

    .tt-icon-btn:hover {
        background: rgba(255, 255, 255, 0.28);
        color: #fff;
    }

    .tt-icon-btn.is-off {
        opacity: 0.55;
    }

    .tt-stats.is-4 {
        grid-template-columns: repeat(4, 1fr);
    }

    .tt-stat.is-danger .tt-stat-icon {
        background: rgba(220, 53, 69, 0.9);
    }

    .tt-stat.is-warning .tt-stat-icon {
        background: rgba(217, 119, 6, 0.9);
    }

    /* Peringatan tab yang belum kembali */
    .tt-alert {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-top: 12px;
        padding: 9px 12px;
        border-radius: 12px;
        background: #fff;
        color: #b02a37;
        font-size: 13px;
        text-decoration: none;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.12);
    }

    .tt-alert:hover {
        color: #842029;
    }

    .tt-alert-icon {
        width: 28px;
        height: 28px;
        flex-shrink: 0;
        border-radius: 8px;
        background: #f8d7da;
        display: grid;
        place-items: center;
    }

    /* Penanda field opsional di judul section (judulnya huruf besar) */
    .tt-optional {
        margin-left: 2px;
        padding: 1px 7px;
        border-radius: 20px;
        background: #f1f3f5;
        color: #6c757d;
        font-size: 10px;
        font-weight: 600;
        text-transform: none;
        letter-spacing: 0;
    }

    /* Keterangan di bawah tombol Proses: tercatat atas nama siapa / tanpa NIK */
    .tt-submit-hint {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        margin-top: 8px;
        font-size: 12px;
        color: #6c757d;
    }

    .tt-submit-hint:empty {
        display: none;
    }

    .scanned-tag-row .tag-info.is-warn {
        color: #b45309;
        font-weight: 600;
    }

    @media (max-width: 767.98px) {
        .tt-stats.is-4 {
            grid-template-columns: 1fr 1fr;
        }
    }
</style>
