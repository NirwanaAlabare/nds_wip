{{-- ===========================================================================
     Skin Stock Opname (daftar rak & proses scan) - palet navy Invoice EXIM.
     Dipakai di tablet & HP: tombol/isian setinggi 44px, tabel lebar berubah jadi
     kartu per baris di layar sempit, bar Simpan menempel di bawah layar.
     Semua aturan diawali .so-skin / .so-modal / .so-dropdown supaya tidak bocor.
     =========================================================================== --}}
<style>
.so-skin, .so-modal, .so-dropdown {
  --n-950: #0f2942; --n-900: #1e3a5f; --n-700: #2c5282; --n-500: #3b6db0; --n-300: #9fb6d6;
  --n-200: #cbd8e6; --n-100: #eef2f7; --n-50: #f5f7fb;
  --ink: #1e293b; --ink-2: #475569; --muted: #64748b; --line: #e2e8f0; --line-2: #d7dee9;
  --zebra: #f8fafc; --hover: #eaf2ff;
  --ok: #166534; --ok-bg: #dcfce7; --ok-garis: #86efac;
  --warn: #92400e; --warn-bg: #fef3c7; --warn-garis: #fcd34d;
  --bahaya: #b91c1c; --bahaya-bg: #fee2e2; --bahaya-garis: #fca5a5;
  --bayang: rgba(30, 58, 95, .45); --fokus: rgba(44, 82, 130, .18);
}
.so-skin { padding: 16px 4px 24px; color: var(--ink); }
@media (min-width: 768px) { .so-skin { padding: 18px 8px 28px; } }

/* ===== Kepala halaman ===== */
.so-kepala { display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px; margin-bottom: 16px; }
.so-kepala-kiri { display: flex; align-items: center; gap: 12px; min-width: 0; flex: 1 1 auto; }
.so-kepala-ikon {
  flex: 0 0 auto; width: 44px; height: 44px; border-radius: 12px;
  background: linear-gradient(135deg, var(--n-900), var(--n-700)); color: #f8fafc; font-size: 17px;
  display: inline-flex; align-items: center; justify-content: center; box-shadow: 0 6px 14px -6px rgba(30, 58, 95, .65);
}
a.so-kepala-ikon:hover { color: #fff; filter: brightness(1.1); }
.so-kepala-teks { min-width: 0; }
.so-kepala-judul { margin: 0; font-size: 19px; font-weight: 700; letter-spacing: -.2px; color: var(--n-950); line-height: 1.2; }
.so-kepala-sub { margin: 3px 0 0; font-size: 12.5px; color: var(--muted); display: flex; flex-wrap: wrap; gap: 6px; align-items: center; }
.so-chip {
  display: inline-flex; align-items: center; gap: 5px; padding: 2px 9px; border-radius: 999px;
  background: var(--n-100); color: var(--n-900); font-size: 11.5px; font-weight: 600; white-space: nowrap;
}
.so-chip i { font-size: 10px; opacity: .7; }

/* ===== Tombol (target sentuh 44px) ===== */
.so-skin .btn, .so-modal .btn {
  min-height: 44px; border-radius: 10px; font-size: 13.5px; font-weight: 600;
  display: inline-flex; align-items: center; justify-content: center; gap: 8px; padding: 0 16px; touch-action: manipulation;
}
.so-btn-utama { background: linear-gradient(135deg, var(--n-900), var(--n-700)); border: 1px solid transparent; color: #fff; box-shadow: 0 1px 2px rgba(15, 23, 42, .12); }
.so-btn-utama:hover, .so-btn-utama:focus { filter: brightness(1.08); color: #fff; }
.so-btn-utama:disabled { background: #b6c4d6; box-shadow: none; color: #fff; opacity: 1; }
.so-btn-netral { background: #fff; border: 1px solid var(--line-2); color: #334155; }
.so-btn-netral:hover, .so-btn-netral:focus { background: #f1f5f9; border-color: #c3cedd; color: var(--n-900); }
.so-btn-bahaya { background: #fff; border: 1px solid var(--bahaya-garis); color: var(--bahaya); }
.so-btn-bahaya:hover, .so-btn-bahaya:focus { background: var(--bahaya-bg); color: var(--bahaya); }
.so-btn-ok { background: linear-gradient(135deg, #15803d, #16a34a); border: 1px solid transparent; color: #fff; }
.so-btn-ok:hover, .so-btn-ok:focus { filter: brightness(1.06); color: #fff; }
.so-btn-ok:disabled { background: #a7c4b2; color: #fff; opacity: 1; }
.so-skin .btn:focus-visible, .so-modal .btn:focus-visible { outline: 0; box-shadow: 0 0 0 3px var(--fokus); }
/* Tombol ikon di baris tabel/kartu: tetap 40px supaya mudah ditekan */
.so-skin .so-ikon { min-height: 40px; width: 40px; padding: 0; border-radius: 9px; font-size: 14px; }
.so-ikon-ubah { background: #fff7ed; border: 1px solid #fed7aa; color: #c2410c; }
.so-ikon-ubah:hover { background: #ffedd5; color: #9a3412; }
.so-ikon-hapus { background: #fff; border: 1px solid var(--bahaya-garis); color: var(--bahaya); }
.so-ikon-hapus:hover { background: var(--bahaya-bg); color: var(--bahaya); }
.so-ikon-undo { background: #fff; border: 1px solid var(--n-200); color: var(--n-700); }
.so-ikon-undo:hover { background: var(--n-100); color: var(--n-900); }

/* ===== Kartu ===== */
.so-kartu {
  background: #fff; border: 1px solid #dde5f0; border-radius: 14px; margin-bottom: 16px;
  box-shadow: 0 1px 2px rgba(15, 23, 42, .04), 0 12px 26px -16px var(--bayang);
}
.so-kartu-kepala {
  background: linear-gradient(180deg, var(--n-700) 0%, var(--n-900) 100%); border-radius: 13px 13px 0 0;
  display: flex; align-items: center; gap: 10px; flex-wrap: wrap; padding: 12px 16px; color: #f8fafc;
}
.so-kartu-judul { margin: 0; font-size: 14px; font-weight: 600; letter-spacing: .4px; display: flex; align-items: center; gap: 9px; }
.so-kartu-judul i { opacity: .75; font-size: 13px; }
.so-kartu-isi { padding: 16px; }
@media (min-width: 768px) { .so-kartu-isi { padding: 18px; } }

/* ===== Isian ===== */
.so-label { display: block; font-size: 11.5px; font-weight: 600; letter-spacing: .3px; color: var(--muted); text-transform: uppercase; margin-bottom: 6px; }
.so-skin .form-control, .so-modal .form-control { min-height: 44px; border-radius: 10px; border-color: var(--line-2); font-size: 15px; color: var(--ink); }
.so-skin .form-control:focus, .so-modal .form-control:focus { border-color: var(--n-700); box-shadow: 0 0 0 3px var(--fokus); }
.so-skin .select2-container--bootstrap4 .select2-selection--single { height: 44px !important; border-radius: 10px; border-color: var(--line-2); }
.so-skin .select2-container--bootstrap4 .select2-selection--single .select2-selection__rendered { line-height: 42px; padding-left: 12px; font-size: 15px; font-weight: 600; color: var(--n-950); }
.so-skin .select2-container--bootstrap4 .select2-selection--single .select2-selection__arrow { height: 42px; }
.so-skin .select2-container { width: 100% !important; }
/* Dropdown select2 menempel ke <body> - diberi kelas so-dropdown lewat dropdownCssClass */
.so-dropdown .select2-results__option { padding: 11px 14px; font-size: 14.5px; }
.so-dropdown .select2-search__field { min-height: 40px; font-size: 15px; border-radius: 8px; }
.so-dropdown .select2-results > .select2-results__options { max-height: min(340px, 55vh); }

.so-cari { position: relative; }
.so-cari > i { position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: #94a3b8; font-size: 14px; pointer-events: none; }
.so-cari .form-control { padding-left: 40px; text-transform: uppercase; }
.so-cari .form-control::placeholder { text-transform: none; }

/* ===== Kotak statistik (yang bisa ditekan jadi filter status) ===== */
.so-stat-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 10px; margin-bottom: 16px; }
@media (min-width: 768px) { .so-stat-grid { grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 14px; } }
.so-stat {
  position: relative; background: #fff; border: 1px solid #dde5f0; border-radius: 14px; padding: 12px 14px; min-width: 0;
  box-shadow: 0 1px 2px rgba(15, 23, 42, .04), 0 12px 26px -16px var(--bayang); display: flex; gap: 12px; align-items: center; text-align: left;
}
button.so-stat { width: 100%; cursor: pointer; transition: transform .1s ease, box-shadow .12s ease; touch-action: manipulation; }
button.so-stat:active { transform: scale(.98); }
button.so-stat:focus-visible { outline: 0; box-shadow: 0 0 0 3px var(--fokus); }
.so-stat-ikon { flex: 0 0 auto; width: 40px; height: 40px; border-radius: 11px; display: inline-flex; align-items: center; justify-content: center; font-size: 15px; background: var(--n-100); color: var(--n-700); }
.so-stat-isi { min-width: 0; flex: 1 1 auto; }
.so-stat-label { display: block; font-size: 11px; font-weight: 600; letter-spacing: .3px; color: var(--muted); text-transform: uppercase; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.so-stat-angka { display: block; width: 100%; font-size: 21px; font-weight: 700; color: var(--n-950); line-height: 1.2; font-variant-numeric: tabular-nums; }
input.so-stat-angka { border: 0; padding: 0; background: transparent; outline: 0; min-height: 0; }
.so-stat-ket { display: block; font-size: 11.5px; color: var(--muted); line-height: 1.3; }
.so-stat.is-pending .so-stat-ikon { background: #f1f5f9; color: #475569; }
.so-stat.is-partial .so-stat-ikon { background: var(--warn-bg); color: var(--warn); }
.so-stat.is-completed .so-stat-ikon { background: var(--ok-bg); color: var(--ok); }
.so-stat.is-aktif { background: linear-gradient(135deg, var(--n-900), var(--n-700)); border-color: transparent; }
.so-stat.is-aktif .so-stat-ikon { background: rgba(255, 255, 255, .15); color: #fff; }
.so-stat.is-aktif .so-stat-label, .so-stat.is-aktif .so-stat-ket { color: rgba(248, 250, 252, .78); }
.so-stat.is-aktif .so-stat-angka { color: #fff; }
@media (max-width: 420px) {
  .so-stat { padding: 10px 12px; gap: 10px; }
  .so-stat-ikon { width: 34px; height: 34px; font-size: 13px; }
  .so-stat-angka { font-size: 18px; }
}

/* ===== Progres ===== */
.so-progres { height: 8px; border-radius: 99px; background: var(--n-100); overflow: hidden; }
.so-progres > span { display: block; height: 100%; border-radius: 99px; background: linear-gradient(90deg, var(--n-500), var(--n-900)); transition: width .3s ease; }
.so-progres.is-partial > span { background: linear-gradient(90deg, #fbbf24, #d97706); }
.so-progres.is-completed > span { background: linear-gradient(90deg, #4ade80, #16a34a); }
.so-progres-baris { display: flex; align-items: center; justify-content: space-between; gap: 10px; font-size: 12px; color: var(--ink-2); margin-bottom: 6px; }
.so-progres-baris b { color: var(--n-950); font-variant-numeric: tabular-nums; }

/* ===== Pil status ===== */
.so-pil { display: inline-flex; align-items: center; gap: 5px; padding: 3px 10px; border-radius: 999px; font-size: 11.5px; font-weight: 700; white-space: nowrap; background: #f1f5f9; color: #475569; }
.so-pil-titik { width: 7px; height: 7px; border-radius: 50%; background: currentColor; opacity: .8; }
.so-pil.is-pending { background: #f1f5f9; color: #475569; }
.so-pil.is-partial { background: var(--warn-bg); color: var(--warn); }
.so-pil.is-completed { background: var(--ok-bg); color: var(--ok); }
.so-pil.is-temp { background: #d1fae5; color: #065f46; }

/* ===== DataTables ===== */
.so-skin .dataTables_wrapper .dataTables_processing {
  top: 40px; left: 50%; transform: translateX(-50%); margin: 0; width: auto; height: auto; padding: 10px 18px;
  border: 1px solid var(--line); border-radius: 10px; background: #fff; box-shadow: 0 6px 20px -8px rgba(15, 23, 42, .25);
  font-size: 13px; color: var(--ink-2); z-index: 5;
}
.so-dt-atas { margin-bottom: 12px; }
.so-dt-atas .dataTables_filter { text-align: left; }
.so-dt-atas .dataTables_filter label { display: block; width: 100%; margin: 0; position: relative; }
.so-dt-atas .dataTables_filter label::before {
  content: '\f002'; font-family: 'Font Awesome 6 Free', 'Font Awesome 5 Free'; font-weight: 900; position: absolute; left: 14px; top: 50%; transform: translateY(-50%);
  color: #94a3b8; font-size: 13px; pointer-events: none;
}
.so-dt-atas .dataTables_filter input { width: 100% !important; margin: 0 !important; min-height: 44px; border-radius: 10px; border: 1px solid var(--line-2); padding: 0 12px 0 40px; font-size: 15px; }
.so-dt-atas .dataTables_filter input:focus { outline: 0; border-color: var(--n-700); box-shadow: 0 0 0 3px var(--fokus); }
.so-dt-bawah { display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 10px; margin-top: 12px; }
.so-dt-bawah .dataTables_info { color: var(--muted); font-size: 12.5px; padding: 0 !important; }
.so-dt-bawah .dataTables_paginate { margin-left: auto; }
.so-dt-bawah .pagination { margin: 0 !important; flex-wrap: wrap; gap: 4px; }
.so-skin .pagination .page-link { min-width: 40px; min-height: 40px; display: inline-flex; align-items: center; justify-content: center; border: 1px solid var(--line); color: var(--n-900); font-size: 13.5px; padding: 0 12px; margin: 0 !important; border-radius: 9px !important; }
.so-skin .pagination .page-item.active .page-link { background: var(--n-900); border-color: var(--n-900); color: #fff; }
.so-skin .pagination .page-item.disabled .page-link { color: #cbd5e1; background: #f8fafc; }
@media (max-width: 575px) { .so-dt-bawah { justify-content: center; } .so-dt-bawah .dataTables_paginate { margin: 0 auto; } .so-dt-bawah .dataTables_info { width: 100%; text-align: center; } }

/* ===== Tabel ===== */
.so-scroll { overflow-x: auto; border-radius: 12px; border: 1px solid var(--line); -webkit-overflow-scrolling: touch; }
.so-tabel { width: 100% !important; margin: 0 !important; border-collapse: separate; border-spacing: 0; font-size: 13px; color: var(--ink); }
.so-tabel thead th {
  background: #f1f5f9; color: var(--n-900); font-weight: 600; font-size: 12px; letter-spacing: .2px; white-space: nowrap;
  padding: 11px 12px; border: 0 !important; border-bottom: 2px solid var(--n-200) !important; vertical-align: middle;
}
.so-tabel tbody td { padding: 8px 12px; vertical-align: middle; border: 0; border-bottom: 1px solid #eef2f7; font-variant-numeric: tabular-nums; }
.so-tabel tbody tr:nth-child(odd) td { background: #fff; }
.so-tabel tbody tr:nth-child(even) td { background: var(--zebra); }
.so-tabel tbody tr:last-child td { border-bottom: 0; }
.so-tabel tbody tr.is-temp td { background: #ecfdf5; }
.so-tabel td.dataTables_empty { text-align: center; color: var(--muted); padding: 30px 12px; }
.so-tabel .so-kanan { text-align: right; }
.so-tabel .so-tengah { text-align: center; }
.so-tabel .so-barcode { font-family: ui-monospace, SFMono-Regular, Consolas, monospace; font-weight: 700; color: var(--n-950); letter-spacing: .2px; }
.so-tabel .so-desc { max-width: 360px; white-space: normal; line-height: 1.35; color: var(--ink-2); font-size: 12.5px; }
.so-aksi { display: flex; gap: 6px; justify-content: center; }
.so-kosong { display: flex; flex-direction: column; align-items: center; gap: 6px; color: var(--muted); }
.so-kosong i { font-size: 22px; color: #c3cedd; }

/* Tabel jadi kartu per baris di layar sempit. so-kartu-lg: di bawah 992px (tabel lebar),
   so-kartu-md: di bawah 768px. Label tiap nilai dari judul kolomnya (data-label). */
@media (max-width: 991.98px) {
  .so-kartu-lg.so-tabel, .so-kartu-lg.so-tabel tbody { display: block; }
  .so-kartu-lg.so-tabel thead { display: none; }
  .so-scroll:has(.so-kartu-lg) { border: 0; border-radius: 0; overflow: visible; }
  .so-kartu-lg.so-tabel tbody tr {
    display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 8px 14px; padding: 12px 14px; margin-bottom: 10px;
    border: 1px solid var(--line); border-radius: 12px; background: #fff; box-shadow: 0 1px 2px rgba(15, 23, 42, .04);
  }
  .so-kartu-lg.so-tabel tbody tr td { display: block; padding: 0; border: 0; background: transparent !important; min-width: 0; text-align: left; }
  .so-kartu-lg.so-tabel tbody tr.is-temp { background: #ecfdf5; border-color: #a7f3d0; }
  .so-kartu-lg.so-tabel tbody td[data-label]::before { content: attr(data-label); display: block; font-size: 10.5px; font-weight: 600; letter-spacing: .3px; text-transform: uppercase; color: var(--muted); margin-bottom: 1px; }
  .so-kartu-lg.so-tabel tbody td.so-k-judul { grid-column: 1 / -1; font-size: 15px; }
  .so-kartu-lg.so-tabel tbody td.so-k-judul::before { display: none; }
  .so-kartu-lg.so-tabel tbody td.so-k-penuh { grid-column: 1 / -1; max-width: none; }
  .so-kartu-lg.so-tabel tbody td.so-k-aksi { grid-column: 1 / -1; border-top: 1px dashed var(--line); padding-top: 10px; }
  .so-kartu-lg.so-tabel tbody td.so-k-aksi::before { display: none; }
  .so-kartu-lg.so-tabel tbody td.so-k-aksi .so-aksi { justify-content: flex-end; }
  .so-kartu-lg.so-tabel tbody td.dataTables_empty { grid-column: 1 / -1; padding: 24px 0; text-align: center; }
  .so-kartu-lg.so-tabel tbody tr:has(td.dataTables_empty) { display: block; }
}
/* Tablet portrait: kartu tiga kolom supaya tidak terlalu panjang */
@media (min-width: 576px) and (max-width: 991.98px) {
  .so-kartu-lg.so-tabel tbody tr { grid-template-columns: repeat(3, minmax(0, 1fr)); }
}
@media (max-width: 767.98px) {
  .so-kartu-md.so-tabel, .so-kartu-md.so-tabel tbody { display: block; }
  .so-kartu-md.so-tabel thead { display: none; }
  .so-scroll:has(.so-kartu-md) { border: 0; border-radius: 0; overflow: visible; }
  .so-kartu-md.so-tabel tbody tr {
    display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 8px 14px; padding: 12px 14px; margin-bottom: 10px;
    border: 1px solid var(--line); border-radius: 12px; background: #fff; box-shadow: 0 1px 2px rgba(15, 23, 42, .04);
  }
  .so-kartu-md.so-tabel tbody tr td { display: block; padding: 0; border: 0; background: transparent !important; min-width: 0; text-align: left; }
  .so-kartu-md.so-tabel tbody tr.is-temp { background: #ecfdf5; border-color: #a7f3d0; }
  .so-kartu-md.so-tabel tbody td[data-label]::before { content: attr(data-label); display: block; font-size: 10.5px; font-weight: 600; letter-spacing: .3px; text-transform: uppercase; color: var(--muted); margin-bottom: 1px; }
  .so-kartu-md.so-tabel tbody td.so-k-judul { grid-column: 1 / -1; font-size: 15px; }
  .so-kartu-md.so-tabel tbody td.so-k-judul::before { display: none; }
  .so-kartu-md.so-tabel tbody td.so-k-penuh { grid-column: 1 / -1; max-width: none; }
  .so-kartu-md.so-tabel tbody td.dataTables_empty { grid-column: 1 / -1; padding: 24px 0; text-align: center; }
  .so-kartu-md.so-tabel tbody tr:has(td.dataTables_empty) { display: block; }
}

/* ===== Tab ===== */
.so-tab { display: flex; gap: 6px; overflow-x: auto; padding: 4px; margin-bottom: 14px; background: var(--n-100); border-radius: 12px; scrollbar-width: none; }
.so-tab::-webkit-scrollbar { display: none; }
.so-tab-btn {
  flex: 1 0 auto; min-height: 44px; border: 0; border-radius: 9px; background: transparent; color: var(--ink-2);
  font-size: 13.5px; font-weight: 600; display: inline-flex; align-items: center; justify-content: center; gap: 8px; padding: 0 14px; white-space: nowrap; touch-action: manipulation;
}
.so-tab-btn:hover { color: var(--n-900); }
.so-tab-btn.is-aktif { background: #fff; color: var(--n-900); box-shadow: 0 1px 3px rgba(15, 23, 42, .12); }
.so-tab-n { min-width: 22px; padding: 1px 7px; border-radius: 999px; background: rgba(30, 58, 95, .1); color: var(--n-900); font-size: 11.5px; font-weight: 700; text-align: center; font-variant-numeric: tabular-nums; }
.so-tab-btn.is-aktif .so-tab-n { background: var(--n-900); color: #fff; }
.so-tab-btn.is-bahaya .so-tab-n { background: var(--warn-bg); color: var(--warn); }
.so-tab-panel[hidden] { display: none !important; }
.so-tab-alat { display: flex; flex-wrap: wrap; gap: 8px; align-items: center; justify-content: space-between; margin-bottom: 12px; }
.so-tab-ket { font-size: 12.5px; color: var(--muted); }
/* HP: empat tab jadi 2 x 2, semuanya kelihatan tanpa geser */
@media (max-width: 575.98px) {
  .so-tab { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); overflow: visible; }
  .so-tab-btn { padding: 0 8px; font-size: 13px; gap: 6px; }
}

/* ===== Modal ===== */
.so-modal .modal-content { border: 0; border-radius: 16px; overflow: hidden; box-shadow: 0 30px 60px -20px rgba(15, 41, 66, .5); }
.so-modal .modal-header { background: linear-gradient(180deg, var(--n-700) 0%, var(--n-900) 100%); color: #f8fafc; border: 0; padding: 14px 18px; align-items: center; }
.so-modal .modal-title { font-size: 15px; font-weight: 600; display: flex; align-items: center; gap: 10px; margin: 0; }
.so-modal .modal-title i { opacity: .75; }
.so-modal .so-tutup { background: rgba(255, 255, 255, .12); border: 0; color: #fff; width: 40px; height: 40px; border-radius: 10px; display: inline-flex; align-items: center; justify-content: center; margin-left: auto; font-size: 16px; }
.so-modal .so-tutup:hover { background: rgba(255, 255, 255, .24); }
.so-modal .modal-body { background: var(--n-50); padding: 18px; }
.so-modal .modal-footer { background: #fff; border-top: 1px solid var(--line); padding: 12px 18px; gap: 8px; }
.so-modal .modal-footer .btn { margin: 0; flex: 1 1 0; }
@media (min-width: 576px) { .so-modal .modal-footer .btn { flex: 0 0 auto; } }

@media (prefers-reduced-motion: reduce) { .so-progres > span, button.so-stat { transition: none; } }
</style>
