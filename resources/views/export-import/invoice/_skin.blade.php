{{-- ===========================================================================
     Skin daftar Invoice EXIM.
     Disalin dari skin Debit Note aplikasi AR (application/views/arnag/dn_skin.php
     dan list_debitnote.php) supaya tampilannya sama persis.
     Semua aturan diawali .nag-skin supaya tidak bocor ke halaman nds_wip lain.
     Catatan: AR pakai Bootstrap 4 + AdminLTE, di sini Bootstrap 5 - warnanya
     sama, markup-nya yang menyesuaikan (ms-auto, dll).
     =========================================================================== --}}
<style>
/* Jarak dari navbar & tepi layar */
.nag-skin { padding: 18px 18px 0; }

/* ===== Kartu ===== */
.nag-skin .card {
  margin-bottom: 16px;
  border: 1px solid #dde5f0;
  border-radius: 12px;
  /* Bayangannya kebiruan, senada wash halaman - sama dengan kartu di daftar. */
  box-shadow: 0 1px 2px rgba(15, 23, 42, .04), 0 12px 26px -16px rgba(30, 58, 95, .45);
}
.nag-skin .card > .card-header {
  background: linear-gradient(180deg, #2c5282 0%, #1e3a5f 100%);
  background-image: linear-gradient(180deg, #2c5282 0%, #1e3a5f 100%);
  border-bottom: 0;
  border-radius: 11px 11px 0 0;
  display: flex;
  align-items: center;
  padding: 13px 18px;
}
.nag-skin .card > .card-header .card-title {
  color: #f8fafc;
  font-weight: 600;
  font-size: 14px;
  letter-spacing: .5px;
  text-transform: none;
  margin: 0;
  display: flex;
  align-items: center;
  gap: 9px;
}
.nag-skin .card > .card-header .card-title i { opacity: .75; font-size: 13px; }
.nag-skin .card > .card-body { padding: 18px; }
/* Kartu bersebelahan dibuat sama tinggi lewat .h-100. Tingginya dikurangi
   margin sendiri, supaya jarak ke kartu di bawahnya tidak ikut termakan. */
.nag-skin .card.h-100 { height: calc(100% - 16px) !important; }

/* ===== Label & kontrol filter ===== */
.nag-skin label {
  font-size: 11.5px;
  font-weight: 600;
  letter-spacing: .3px;
  text-transform: none;
  color: #64748b;
  margin-bottom: 6px;
}
.nag-skin .form-group { margin-bottom: 14px; }
.nag-skin .form-control,
.nag-skin .select2-container .select2-selection--single,
.nag-skin .input-group-text,
.nag-skin .btn {
  height: 38px;
  border-radius: 8px;
  border-color: #e2e8f0;
  font-size: 13px;
}
.nag-skin .form-control { color: #1e293b; }
.nag-skin .form-control:focus {
  border-color: #2c5282;
  box-shadow: 0 0 0 3px rgba(44, 82, 130, .15);
}
.nag-skin .input-group-text { background: #f8fafc; color: #64748b; }
.nag-skin .input-group > .form-control { border-radius: 8px 0 0 8px; }

/* select2 dibuat setinggi & sebulat kontrol lain */
.nag-skin .select2-container { display: block; width: 100% !important; }
.nag-skin .select2-container .select2-selection--single { height: 38px !important; padding: 0; }
.nag-skin .select2-container .select2-selection--single .select2-selection__rendered {
  line-height: 36px;
  padding-left: 12px;
  padding-right: 28px;
  color: #1e293b;
}
.nag-skin .select2-container .select2-selection--single .select2-selection__arrow {
  height: 36px;
  top: 1px;
  right: 6px;
}

/* Dropdown select2 menempel ke <body>, jadi tidak bisa di-scope ke .nag-skin -
   aman karena style ini cuma dimuat di halaman daftar invoice. */
.select2-container--bootstrap4 .select2-dropdown {
  border-color: #e2e8f0;
  border-radius: 10px;
  box-shadow: 0 10px 25px rgba(15, 23, 42, .12);
  overflow: hidden;
}
.select2-container--bootstrap4 .select2-search--dropdown .select2-search__field {
  border: 1px solid #e2e8f0;
  border-radius: 8px;
  padding: 6px 10px;
  font-size: 13px;
}
.select2-container--bootstrap4 .select2-search--dropdown .select2-search__field:focus {
  outline: none;
  border-color: #2c5282;
  box-shadow: 0 0 0 3px rgba(44, 82, 130, .15);
}
.select2-container--bootstrap4 .select2-results > .select2-results__options { max-height: 260px; }
.select2-container--bootstrap4 .select2-results__option {
  font-size: 13px;
  padding: 7px 12px;
  color: #1e293b;
}
.select2-container--bootstrap4 .select2-results__option[aria-selected=true] {
  background: #eef2f7;
  color: #0f172a;
  font-weight: 600;
}
.select2-container--bootstrap4 .select2-results__option--highlighted,
.select2-container--bootstrap4 .select2-results__option--highlighted[aria-selected],
.select2-container--bootstrap4 .select2-results__option[aria-selected=true].select2-results__option--highlighted {
  background: #1e3a5f !important;
  color: #f8fafc !important;
}
.select2-container--bootstrap4.select2-container--focus .select2-selection,
.select2-container--bootstrap4.select2-container--open .select2-selection {
  border-color: #2c5282 !important;
  box-shadow: 0 0 0 3px rgba(44, 82, 130, .15);
}

/* ===== Tiga tombol aksi - warna persis Debit Note ===== */
/* Search: indigo (di AR ini .btn-primary global) */
.nag-skin .btn-dn-search {
  background: linear-gradient(135deg, #283593, #3949ab);
  border: none;
  color: #fff;
}
/* Create: biru muda, sengaja beda jelas dengan Search di sebelahnya */
.nag-skin .btn-dn-create {
  background: linear-gradient(135deg, #38bdf8, #0ea5e9);
  border: none;
  color: #fff;
}
/* Export: hijau */
.nag-skin .btn-dn-excel {
  background: linear-gradient(135deg, #15803d, #16a34a);
  border: none;
  color: #fff;
}
.nag-skin .btn-dn-search:hover,
.nag-skin .btn-dn-create:hover,
.nag-skin .btn-dn-excel:hover { filter: brightness(.94); color: #fff; }
.nag-skin .btn-dn-search:focus-visible,
.nag-skin .btn-dn-create:focus-visible,
.nag-skin .btn-dn-excel:focus-visible {
  outline: 0;
  box-shadow: 0 0 0 3px rgba(44, 82, 130, .35);
}

/* ===== Tabel ===== */
.nag-skin .dn-list-area { position: relative; min-height: 220px; }
.nag-skin .dn-table {
  width: 100%;
  margin: 0;
  border-collapse: separate;
  border-spacing: 0;
  font-size: 12.5px;
  color: #1e293b;
}
.nag-skin .dn-table thead th {
  background: #1e3a5f;
  color: #f8fafc;
  font-weight: 600;
  font-size: 12px;
  letter-spacing: .2px;
  text-align: left;
  vertical-align: middle;
  white-space: nowrap;
  padding: 11px 12px;
  border: 0;
  border-bottom: 2px solid #0f2942;
}
.nag-skin .dn-table tbody td {
  padding: 6px 12px;
  vertical-align: middle;
  white-space: nowrap;
  border: 0;
  border-bottom: 1px solid #eef2f7;
  font-variant-numeric: tabular-nums;
}
/* height sel tidak termasuk padding 6px - 38px isi = baris 50px, sama dengan DN */
.nag-skin .dn-table tbody tr:not(.child) > td:not(.dataTables_empty) { height: 38px; }
.nag-skin .dn-table tbody tr:last-child td { border-bottom: 0; }
.nag-skin .dn-table tbody tr:nth-child(odd) td { background: #fff; }
.nag-skin .dn-table tbody tr:nth-child(even) td { background: #f8fafc; }
.nag-skin .dn-table tbody tr:hover td { background: #eaf2ff; }
.nag-skin .dn-table td.dn-angka { text-align: right; }
.nag-skin .dn-table td.dn-tengah { text-align: center; }
.nag-skin .dn-table td.dataTables_empty {
  text-align: center;
  color: #94a3b8;
  font-style: italic;
  padding: 28px 12px;
}

/* ===== Tabel daftar: muat penuh, tanpa geser ke samping =====
   Kolom teks panjang (nama customer) boleh turun baris; kolom pendek tetap
   satu baris supaya angka & tanggal tidak terpecah. */
.nag-skin .dn-table-muat-wrap { overflow: visible; }
.nag-skin .dn-table-muat { table-layout: fixed; }
.nag-skin .dn-table-muat thead th,
.nag-skin .dn-table-muat tbody td {
  white-space: normal;
  word-break: break-word;
  padding-left: 9px;
  padding-right: 9px;
}
/* Lebar kolom diatur supaya 11 kolom muat di layar biasa.
   Urutan: Inv Number, Billed To, Shipped To, Shipp, Date, Type, Status,
           Doc Type, Doc Number, Value, Action */
.nag-skin .dn-table-muat th:nth-child(1) { width: 11%; }
.nag-skin .dn-table-muat th:nth-child(2) { width: 14%; }
.nag-skin .dn-table-muat th:nth-child(3) { width: 14%; }
.nag-skin .dn-table-muat th:nth-child(4) { width: 5%; }
.nag-skin .dn-table-muat th:nth-child(5) { width: 7%; }
.nag-skin .dn-table-muat th:nth-child(6) { width: 7%; }
.nag-skin .dn-table-muat th:nth-child(7) { width: 8.5%; }
.nag-skin .dn-table-muat th:nth-child(8) { width: 6%; }
.nag-skin .dn-table-muat th:nth-child(9) { width: 7%; }
.nag-skin .dn-table-muat th:nth-child(10) { width: 7.5%; }
.nag-skin .dn-table-muat th:nth-child(11) { width: 13%; }
/* Pil status di daftar tidak perlu selebar 12.5em kalau kolomnya dibatasi. */
.nag-skin .dn-table-muat .dn-badge { min-width: 0; width: 100%; font-size: 10px; }
/* Tombol aksi disusun dua kolom supaya rapi di lebar layar mana pun:
   draft dapat 2x2 (PDF, Excel, Update, Cancel), sisanya satu baris berisi dua.
   Kolomnya auto, bukan 1fr: tombol selebar tulisannya saja, tidak melar
   mengikuti lebar kolom tabel. */
.nag-skin .dn-table-muat .dn-aksi {
  display: grid;
  grid-template-columns: repeat(2, auto);
  justify-content: center;
  justify-items: center;
  gap: 3px;
}
.nag-skin .dn-table-muat .dn-aksi .btn {
  height: 26px;
  padding: 0 5px;
  font-size: 10.5px;
  gap: 4px;
  white-space: nowrap;
  justify-content: center;
}
/* Tombol cetak PDF - merah bata, beda dari Update/Cancel. */
.nag-skin .btn-dn-cetak {
  background: #b91c1c;
  border-color: #b91c1c;
  color: #fff;
}
.nag-skin .btn-dn-cetak:hover,
.nag-skin .btn-dn-cetak:focus { background: #991b1b; border-color: #991b1b; color: #fff; }
/* Nomor invoice bisa diklik untuk melihat rinciannya. */
.nag-skin .dn-no-link {
  color: #1d4ed8;
  font-weight: 600;
  cursor: pointer;
  border-bottom: 1px dashed rgba(29, 78, 216, .45);
  background: none;
  border-left: 0; border-right: 0; border-top: 0;
  padding: 0;
  text-align: left;
}
.nag-skin .dn-no-link:hover { color: #1e3a5f; border-bottom-style: solid; }

@media (max-width: 991px) {
  /* Di layar sempit biarkan menggeser daripada terlalu sesak. */
  .nag-skin .dn-table-muat-wrap { overflow-x: auto; }
  .nag-skin .dn-table-muat { table-layout: auto; min-width: 900px; }
}

/* ===== Pil status ===== */
.nag-skin .dn-badge {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  height: 22px;
  min-width: 12.5em;
  padding: 0 10px;
  border-radius: 999px;
  font-size: 10.5px;
  font-weight: 700;
  letter-spacing: .5px;
  line-height: 1;
  text-transform: uppercase;
  vertical-align: middle;
}
/* Urutan alur: DRAFT abu (belum jalan) - POST biru - first approval kuning -
   second approval hijau (beres) - CANCEL merah. Sama dengan Debit Note. */
.nag-skin .dn-badge.is-draft  { background: #f1f5f9; color: #475569; }
.nag-skin .dn-badge.is-post   { background: #e0f2fe; color: #075985; }
.nag-skin .dn-badge.is-first  { background: #fef3c7; color: #92400e; }
.nag-skin .dn-badge.is-second { background: #dcfce7; color: #166534; }
.nag-skin .dn-badge.is-batal  { background: #fee2e2; color: #991b1b; }
.nag-skin .dn-badge.is-lain   { background: #f1f5f9; color: #475569; }

/* ===== Tombol dalam baris ===== */
.nag-skin .dn-aksi { display: flex; gap: 6px; }
.nag-skin .dn-aksi .btn {
  height: 30px;
  padding: 0 10px;
  font-size: 11.5px;
  border-radius: 7px;
  display: inline-flex;
  align-items: center;
  gap: 5px;
}
.nag-skin .dn-diproses { color: #94a3b8; font-style: italic; font-size: 11.5px; }
/* Kolom Action-nya dua kolom grid; label ini mengisi keduanya. */
.nag-skin .dn-table-muat .dn-aksi .dn-diproses { grid-column: 1 / -1; text-align: center; }

/* ===== DataTables ===== */
/* Baris bawaan DataTables memakai row Bootstrap yang bermargin negatif -
   isinya jadi melebihi lebar kartu dan memunculkan geseran ke samping. */
.nag-skin .dataTables_wrapper .row { margin-left: 0; margin-right: 0; }
.nag-skin .dataTables_wrapper .row > [class^="col-"] { padding-left: 0; padding-right: 0; }
.nag-skin .dataTables_wrapper .row:first-child { margin-bottom: 12px; align-items: center; }
.nag-skin .dataTables_wrapper .row:last-child { margin-top: 12px; align-items: center; }
.nag-skin .dataTables_length label,
.nag-skin .dataTables_filter label {
  display: flex;
  align-items: center;
  gap: 8px;
  margin: 0;
  font-size: 11.5px;
  color: #64748b;
}
.nag-skin .dataTables_length select { width: auto; min-width: 74px; }
.nag-skin .dataTables_filter { text-align: right; }
.nag-skin .dataTables_filter label { justify-content: flex-end; }
.nag-skin .dataTables_filter input { min-width: 240px; }
.nag-skin .dataTables_info { color: #64748b; font-size: 12px; padding-top: 0 !important; }
.nag-skin .dataTables_paginate .pagination { margin: 0; justify-content: flex-end; }
.nag-skin .pagination .page-link {
  border: 1px solid #e2e8f0;
  color: #1e3a5f;
  font-size: 12.5px;
  padding: 5px 11px;
  margin-left: 4px;
  border-radius: 8px;
}
.nag-skin .pagination .page-item.active .page-link {
  background: #1e3a5f;
  border-color: #1e3a5f;
  color: #fff;
}
.nag-skin .pagination .page-item.disabled .page-link { color: #cbd5e1; background: #f8fafc; }

/* Tabel lebar - scrollbar tebal & kontras supaya user sadar masih ada kolom di
   kanan (sama seperti di Debit Note). */
.nag-skin .dn-table-scroll { overflow-x: auto; border-radius: 10px; }
.nag-skin .dn-table-scroll::-webkit-scrollbar { width: 14px; height: 14px; }
.nag-skin .dn-table-scroll::-webkit-scrollbar-track { background: #e2e8f0; border-radius: 8px; }
.nag-skin .dn-table-scroll::-webkit-scrollbar-thumb {
  background: #64748b;
  border-radius: 8px;
  border: 3px solid #e2e8f0;
}
.nag-skin .dn-table-scroll::-webkit-scrollbar-thumb:hover { background: #475569; }

/* ===== Overlay loading ===== */
.nag-skin .dn-loader-overlay {
  position: absolute;
  inset: 0;
  z-index: 5;
  display: none;
  align-items: center;
  justify-content: center;
  background: rgba(248, 250, 252, .78);
  border-radius: 10px;
}
.nag-skin .dn-list-area.is-memuat .dn-loader-overlay { display: flex; }
.nag-skin .dn-loader-kartu {
  display: flex;
  align-items: center;
  gap: 10px;
  background: #fff;
  border: 1px solid #e2e8f0;
  border-radius: 10px;
  padding: 12px 18px;
  font-size: 12.5px;
  color: #475569;
  box-shadow: 0 6px 20px -8px rgba(15, 23, 42, .18);
}
.nag-skin .dn-loader-putar {
  width: 16px;
  height: 16px;
  border: 2px solid #e2e8f0;
  border-top-color: #2c5282;
  border-radius: 50%;
  animation: dn-putar .7s linear infinite;
}
@keyframes dn-putar { to { transform: rotate(360deg); } }
@media (prefers-reduced-motion: reduce) {
  .nag-skin .dn-loader-putar { animation-duration: 2.4s; }
}

/* ===== Khusus halaman Create ===== */
/* Kolom ringkasan (Total, VAT, Grand Total, dst) - label kiri, angka kanan */
.nag-skin .dn-ringkas .form-group.row { margin-bottom: 10px; align-items: center; }
.nag-skin .dn-ringkas .col-form-label {
  font-size: 11.5px;
  font-weight: 600;
  letter-spacing: .3px;
  text-transform: none;
  color: #64748b;
  padding-top: 0;
  padding-bottom: 0;
  margin-bottom: 0;
}
.nag-skin .dn-ringkas .form-control { text-align: right; font-variant-numeric: tabular-nums; }
/* Grand Total dibedakan - ini angka yang dicari orang */
.nag-skin .dn-ringkas .dn-grand .col-form-label { color: #1e3a5f; }
.nag-skin .dn-ringkas .dn-grand .form-control {
  background: #eef2f7;
  border-color: #cbd8e6;
  font-weight: 700;
  color: #0f172a;
}
/* Field yang terisi otomatis (bukan diketik user) dibedakan tipis saja */
.nag-skin .form-control[readonly] { background: #f8fafc; color: #475569; }
/* Nomor invoice: ini identitas dokumen, dibuat menonjol */
.nag-skin #inv-no {
  font-weight: 700;
  color: #1e3a5f;
  background: #eef2f7;
  border-color: #cbd8e6;
  letter-spacing: .3px;
}
/* Tombol simpan & kembali, tepat di bawah Grand Total */
.nag-skin .dn-kaki {
  display: flex;
  gap: 8px;
  flex-wrap: wrap;
  justify-content: flex-end;
  margin-top: 16px;
  padding-top: 14px;
  border-top: 1px solid #e2e8f0;
}
.nag-skin .dn-kaki .btn {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 0 16px;
}
.nag-skin .btn-dn-simpan {
  background: linear-gradient(135deg, #283593, #3949ab);
  border: none;
  color: #fff;
}
.nag-skin .btn-dn-kembali {
  background: linear-gradient(135deg, #b91c1c, #dc2626);
  border: none;
  color: #fff;
}
.nag-skin .btn-dn-simpan:hover,
.nag-skin .btn-dn-kembali:hover { filter: brightness(.94); color: #fff; }
.nag-skin .dn-kaki .btn:focus-visible {
  outline: 0;
  box-shadow: 0 0 0 3px rgba(44, 82, 130, .35);
}

/* Kartu-kartu ini sudah punya judul ber-latar navy. Kepala tabelnya dibuat
   terang supaya tidak ada dua batang navy bertumpuk - cuma satu yang diwarnai.
   Berlaku juga untuk tabel di dalam modal. */
.nag-skin #inv-table-sj thead th,
.nag-skin #inv-table-kirim thead th,
.nag-skin #inv-table-ringkas thead th,
:is(#modal-add-so,#modal-add-ws) .dn-table thead th {
  background: #f1f5f9;
  color: #1e3a5f;
  border-bottom: 2px solid #cbd8e6;
}

/* ===== Modal rincian invoice =====
   Menempel ke <body>, di luar .nag-skin. */
#modal-inv-detail .modal-content { border: 0; border-radius: 14px; overflow: hidden; }
#modal-inv-detail .modal-header {
  background: #1e3a5f;
  border-bottom: 0;
  padding: 13px 18px;
}
#modal-inv-detail .modal-title {
  color: #f8fafc;
  font-weight: 600;
  font-size: 14px;
  letter-spacing: .5px;
  display: flex;
  align-items: center;
  gap: 9px;
}
#modal-inv-detail .modal-title i { opacity: .75; font-size: 13px; }
#modal-inv-detail .btn-close { filter: invert(1) grayscale(100%) brightness(200%); opacity: .8; }
#modal-inv-detail .modal-body { background: #f5f7fb; padding: 18px; }
#modal-inv-detail .modal-footer { border-top: 1px solid #e2e8f0; padding: 12px 18px; gap: 8px; }
#modal-inv-detail .modal-footer .btn { margin: 0; }

/* Keterangan invoice: tiga kolom, label kecil di atas nilainya. */
.dn-det-info {
  display: grid;
  grid-template-columns: repeat(3, minmax(0, 1fr));
  gap: 12px 20px;
  margin: 0 0 16px;
  padding: 16px 18px;
  background: #fff;
  border: 1px solid #e2e8f0;
  border-radius: 12px;
}
.dn-det-info dt {
  font-size: 10.5px;
  font-weight: 600;
  letter-spacing: .4px;
  text-transform: none;
  color: #64748b;
  margin-bottom: 2px;
}
.dn-det-info dd {
  margin: 0;
  font-size: 13px;
  color: #1e293b;
  word-break: break-word;
}
.dn-det-judul {
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 12px;
  font-weight: 600;
  letter-spacing: .4px;
  text-transform: none;
  color: #1e3a5f;
  margin: 0 0 8px;
}
#modal-inv-detail .dn-table-scroll {
  background: #fff;
  border: 1px solid #e2e8f0;
  border-radius: 12px;
  margin-bottom: 16px;
}
#modal-inv-detail .dn-table thead th {
  position: sticky;
  top: 0;
  z-index: 2;
  background: #1e3a5f;
  color: #f8fafc;
}
/* Ringkasan nilai: label kiri, angka kanan. */
.dn-det-ringkas {
  background: #fff;
  border: 1px solid #e2e8f0;
  border-radius: 12px;
  overflow: hidden;
}
.dn-det-ringkas > div {
  display: flex;
  justify-content: space-between;
  gap: 16px;
  padding: 8px 16px;
  font-size: 13px;
  border-bottom: 1px solid #eef2f7;
}
.dn-det-ringkas > div:last-child { border-bottom: 0; }
.dn-det-ringkas span { color: #64748b; }
.dn-det-ringkas b { color: #1e293b; font-variant-numeric: tabular-nums; }
.dn-det-ringkas .dn-det-grand { background: #eef2f7; }
.dn-det-ringkas .dn-det-grand b { color: #1e3a5f; font-size: 15px; }
.dn-det-muat {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 10px;
  padding: 40px;
  color: #64748b;
  font-size: 13px;
}

@media (max-width: 767px) {
  .dn-det-info { grid-template-columns: 1fr; }
}

/* ===== Riwayat invoice =====
   Satu garis waktu: tiap penyimpanan jadi satu kartu, urut dari saat dibuat.
   Nomornya di kiri sebagai titik, jadi urutannya terbaca tanpa membaca
   judulnya. */
.dn-riw { padding: 2px 0 4px; }
.dn-riw-muat,
.dn-riw-kosong {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 10px;
  padding: 34px 16px;
  color: #64748b;
  font-size: 13px;
  text-align: center;
}
.dn-riw-kosong i { color: #94a3b8; }

.dn-riw-alur { position: relative; }
.dn-riw-butir {
  position: relative;
  display: grid;
  grid-template-columns: 26px 1fr;
  gap: 10px;
  padding-bottom: 12px;
}
/* Garis penghubung antar kartu - berhenti di kartu terakhir. */
.dn-riw-butir::before {
  content: '';
  position: absolute;
  left: 12px;
  top: 26px;
  bottom: 0;
  width: 2px;
  background: #e2e8f0;
}
.dn-riw-butir:last-child { padding-bottom: 0; }
.dn-riw-butir:last-child::before { display: none; }

.dn-riw-titik {
  width: 26px;
  height: 26px;
  border-radius: 50%;
  background: #1e3a5f;
  color: #fff;
  font-size: 12px;
  font-weight: 700;
  display: flex;
  align-items: center;
  justify-content: center;
  font-variant-numeric: tabular-nums;
}
.dn-riw-batal > .dn-riw-titik { background: #b91c1c; }

.dn-riw-kartu {
  border: 1px solid #e2e8f0;
  border-radius: 8px;
  background: #fff;
  padding: 9px 11px 10px;
}
.dn-riw-batal > .dn-riw-kartu { border-color: #fca5a5; background: #fef2f2; }

.dn-riw-kepala {
  display: flex;
  flex-wrap: wrap;
  align-items: baseline;
  gap: 4px 12px;
}
.dn-riw-judul { color: #1e3a5f; font-size: 13.5px; }
.dn-riw-batal .dn-riw-judul { color: #991b1b; }
.dn-riw-oleh,
.dn-riw-waktu { color: #64748b; font-size: 11.5px; }
.dn-riw-oleh i,
.dn-riw-waktu i { color: #94a3b8; margin-right: 3px; }
.dn-riw-waktu { margin-left: auto; font-variant-numeric: tabular-nums; }

.dn-riw-ringkas {
  margin-top: 5px;
  font-size: 11.5px;
  color: #475569;
  font-variant-numeric: tabular-nums;
}
.dn-riw-catatan {
  margin-top: 5px;
  font-size: 11.5px;
  color: #94a3b8;
  font-style: italic;
}

/* Yang berubah dari revisi sebelumnya - ini yang paling sering dibaca, jadi
   diberi latar sendiri supaya langsung ketemu. */
.dn-riw-beda {
  margin-top: 7px;
  padding: 7px 9px;
  background: #f8fafc;
  border: 1px solid #eef2f7;
  border-radius: 6px;
}
.dn-riw-beda-judul {
  font-size: 11px;
  font-weight: 700;
  color: #334155;
  text-transform: uppercase;
  letter-spacing: .3px;
  margin: 6px 0 3px;
}
.dn-riw-beda-judul:first-child { margin-top: 0; }
.dn-riw-cacah {
  display: inline-block;
  min-width: 17px;
  padding: 0 4px;
  border-radius: 9px;
  background: #e2e8f0;
  color: #475569;
  font-size: 10px;
  font-weight: 700;
  text-align: center;
  letter-spacing: 0;
}
.dn-riw-beda-daftar {
  margin: 0;
  padding-left: 16px;
  font-size: 12px;
  line-height: 1.65;
  color: #475569;
}
.dn-riw-beda-daftar b { color: #1e293b; font-variant-numeric: tabular-nums; }
.dn-riw-panah { color: #94a3b8; font-size: 9px; margin: 0 1px; }
.dn-riw-hampa { color: #94a3b8; font-style: italic; }
.dn-riw-ekor { color: #64748b; font-variant-numeric: tabular-nums; }
.dn-riw-tanda {
  display: inline-block;
  padding: 0 5px;
  border-radius: 4px;
  font-size: 10px;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: .3px;
}
.dn-riw-tambah { background: #dcfce7; color: #15803d; }
.dn-riw-buang  { background: #fee2e2; color: #b91c1c; }

.dn-riw-lipat { margin-top: 7px; }

/* Bagian History di modal Local - dilipat, jadi diberi jarak dari rekap di atasnya. */
.dn-riw-wadah-lipat { margin-top: 14px; }

/* Potret lengkap - dibuka kalau perlu, jadi boleh padat. */
.dn-riw-potret {
  margin-top: 7px;
  padding-top: 8px;
  border-top: 1px dashed #e2e8f0;
}
.dn-riw-info {
  display: grid;
  grid-template-columns: repeat(3, minmax(0, 1fr));
  gap: 2px 14px;
  margin: 0 0 4px;
}
.dn-riw-info > div { min-width: 0; }
.dn-riw-info dt {
  font-size: 10.5px;
  font-weight: 600;
  color: #94a3b8;
  text-transform: uppercase;
  letter-spacing: .3px;
  margin: 0;
}
.dn-riw-info dd {
  margin: 0 0 4px;
  font-size: 12px;
  color: #1e293b;
  overflow-wrap: anywhere;
}
.dn-riw-uang dd { font-variant-numeric: tabular-nums; }

.dn-riw-grup-judul {
  margin: 8px 0 4px;
  font-size: 11px;
  font-weight: 700;
  color: #1e3a5f;
  text-transform: uppercase;
  letter-spacing: .3px;
}
.dn-riw-grup {
  margin: 0;
  padding: 0;
  list-style: none;
  font-size: 12px;
}
.dn-riw-grup > li {
  padding: 4px 0;
  border-bottom: 1px dashed #eef2f7;
  color: #1e293b;
}
.dn-riw-grup > li:last-child { border-bottom: 0; }
.dn-riw-nilai {
  display: block;
  color: #64748b;
  font-size: 11.5px;
  font-variant-numeric: tabular-nums;
  overflow-wrap: anywhere;
}
.dn-riw-nilai b { color: #334155; }

@media (max-width: 767px) {
  .dn-riw-info { grid-template-columns: 1fr; }
  .dn-riw-waktu { margin-left: 0; }
}

/* ===== SweetAlert =====
   Menempel ke <body>, di luar .nag-skin - jadi diberi kelas sendiri. */
.dn-swal { border-radius: 14px; }
.dn-swal .swal2-title { color: #1e3a5f; font-size: 19px; }
.dn-swal .swal2-icon.swal2-question { border-color: #1e3a5f !important; color: #1e3a5f !important; }
.dn-swal .swal2-confirm { background: #1e3a5f !important; border-color: #1e3a5f !important; }
.dn-swal .swal2-confirm:hover { filter: brightness(.92); }
.dn-swal .swal2-cancel { background: #f1f5f9 !important; color: #334155 !important; }
.dn-swal .swal2-cancel:hover { background: #e2e8f0 !important; }
/* Ringkasan sebelum simpan: label kiri, nilai kanan. */
.dn-swal .dn-swal-ringkas {
  text-align: left;
  border: 1px solid #e2e8f0;
  border-radius: 10px;
  overflow: hidden;
  margin: 4px 0 12px;
}
.dn-swal .dn-swal-ringkas > div {
  display: flex;
  justify-content: space-between;
  gap: 16px;
  padding: 8px 14px;
  font-size: 13px;
  border-bottom: 1px solid #eef2f7;
}
.dn-swal .dn-swal-ringkas > div:last-child { border-bottom: 0; }
.dn-swal .dn-swal-ringkas span { color: #64748b; }
.dn-swal .dn-swal-ringkas b { color: #1e293b; text-align: right; }
.dn-swal .dn-swal-ringkas .dn-swal-grand { background: #eef2f7; }
.dn-swal .dn-swal-ringkas .dn-swal-grand b { color: #1e3a5f; font-size: 15px; }
.dn-swal .dn-swal-kurang {
  text-align: left;
  margin: 8px 0 0;
  padding-left: 22px;
  font-size: 13.5px;
  color: #991b1b;
}
.dn-swal .dn-swal-kurang li { margin-bottom: 3px; }
/* Ringkasan perubahan Detail SO di dialog konfirmasi: judul kecil lalu
   daftar barisnya, supaya yang berubah kelihatan satu per satu. */
/* Keterangan waktu Invoice Date bergeser sendiri karena SJ - dibuat mencolok
   sedikit, karena daftar Invoice Export disaring pakai tanggal itu. */
/* Penanda baris yang sudah masuk invoice - dipakai modal Add WS. */
.dn-sudah { color: #16a34a; }

.dn-tgl-pindah {
  display: block;
  margin-top: 4px;
  padding: 4px 8px;
  border-radius: 6px;
  background: #fffbeb;
  color: #92400e;
  font-size: 11.5px;
  line-height: 1.4;
}
.dn-tgl-pindah b { color: #78350f; }

.dn-swal .dn-swal-judul-kecil {
  margin: 10px 0 4px;
  text-align: left;
  font-size: 12px;
  font-weight: 700;
  color: #334155;
}
.dn-swal .dn-swal-daftar {
  margin: 0;
  padding-left: 18px;
  text-align: left;
  font-size: 12.5px;
  line-height: 1.6;
  color: #475569;
}
.dn-swal .dn-swal-daftar b { color: #1e293b; font-variant-numeric: tabular-nums; }

/* Ringkasan satu baris di atas dialog "Update Detail SO to match the SJ?" -
   angkanya yang dibaca dulu, rinciannya menyusul di bawahnya. */
.dn-swal .dn-swal-ringkas-ubah {
  margin: 2px 0 8px;
  padding: 7px 10px;
  text-align: left;
  font-size: 12.5px;
  color: #334155;
  background: #f1f5f9;
  border: 1px solid #e2e8f0;
  border-radius: 6px;
}
.dn-swal .dn-swal-ringkas-ubah b { color: #0f172a; font-variant-numeric: tabular-nums; }

/* Alasan kenapa barisnya berubah - dibuat lebih redup dari judul bagiannya. */
.dn-swal .dn-swal-alasan {
  margin: 0 0 5px;
  text-align: left;
  font-size: 11.5px;
  line-height: 1.45;
  color: #64748b;
}

/* Titik pemisah dipakai juga di dalam dialog, yang menempel ke <body>. */
.dn-swal .dn-lipat-titik { color: #cbd5e1; }

.dn-swal .dn-swal-catatan {
  font-size: 12px;
  color: #64748b;
  text-align: left;
  margin: 0;
  line-height: 1.5;
}

/* ===== Modal Add SO =====
   Modal menempel ke <body>, di luar .nag-skin - jadi aturannya diberi awalan
   :is(#modal-add-so,#modal-add-ws), bukan .nag-skin. */
/* Tabel SJ-nya 18 kolom - modal dibuat lebih lebar dari modal-xl bawaan
   supaya kolomnya tidak terlalu sering digeser. */
:is(#modal-add-so,#modal-add-ws) .modal-dialog { max-width: min(1680px, 96vw); }
/* Modal Shipment (Invoice Export) ikut warna yang sama - supaya tidak ada dua
   gaya modal berbeda di satu modul. */
:is(#modal-add-so,#modal-add-ws) .modal-content,
#modal-kirim .modal-content { border: 0; border-radius: 14px; overflow: hidden; }
:is(#modal-add-so,#modal-add-ws) .modal-header,
#modal-kirim .modal-header {
  background: #1e3a5f;
  border-bottom: 0;
  padding: 13px 18px;
}
:is(#modal-add-so,#modal-add-ws) .modal-title,
#modal-kirim .modal-title {
  color: #f8fafc;
  font-weight: 600;
  font-size: 14px;
  letter-spacing: .5px;
  text-transform: none;
  display: flex;
  align-items: center;
  gap: 9px;
}
:is(#modal-add-so,#modal-add-ws) .modal-title i,
#modal-kirim .modal-title i { opacity: .75; font-size: 13px; }
:is(#modal-add-so,#modal-add-ws) .btn-close,
#modal-kirim .btn-close { filter: invert(1) grayscale(100%) brightness(200%); opacity: .8; }
:is(#modal-add-so,#modal-add-ws) .modal-body,
#modal-kirim .modal-body { background: #f5f7fb; padding: 18px; }
:is(#modal-add-so,#modal-add-ws) .modal-footer,
#modal-kirim .modal-footer {
  border-top: 1px solid #e2e8f0;
  padding: 12px 18px;
  gap: 8px;
}
:is(#modal-add-so,#modal-add-ws) .modal-footer .btn,
#modal-kirim .modal-footer .btn { margin: 0; }
/* ===== Modal Shipment =====
   Label & kotak isian di dalam modal ikut aturan .nag-skin, padahal modal
   menempel ke <body> di luar kartu. Diulang di sini seperlunya.

   Dibuat lebar dan besar: 17 isian dalam empat kolom harus tetap nyaman dibaca
   di laptop 14 inci - layar efektifnya sekitar 1280-1366 x 610-660 setelah
   dipotong toolbar browser & taskbar. Lebarnya ikut layar sampai 1400px.
   Tingginya dibatasi layar: kalau tidak muat, badan modal yang bergulir,
   judul dan tombol Apply tetap terlihat. */
/* Lebarnya disamakan dengan modal Add SJ - lihat "Modal Add SJ & Add Shipment". */
#modal-kirim .modal-dialog { margin: 16px auto; }
#modal-kirim .modal-dialog-centered { min-height: calc(100% - 32px); }
#modal-kirim .modal-content { max-height: calc(100vh - 32px); }
#modal-kirim .modal-header { padding: 15px 24px; }
#modal-kirim .modal-title { font-size: 16px; }
#modal-kirim .modal-title i { font-size: 15px; }
#modal-kirim .modal-body { padding: 20px 24px; overflow-y: auto; }
#modal-kirim .modal-footer { padding: 12px 24px; }
#modal-kirim .modal-footer .btn {
  height: 42px;
  padding: 0 22px;
  font-size: 15px;
  display: inline-flex;
  align-items: center;
  gap: 7px;
}

#modal-kirim label {
  font-size: 12px;
  font-weight: 700;
  letter-spacing: .4px;
  text-transform: none;
  color: #64748b;
  margin-bottom: 6px;
}
#modal-kirim .form-control {
  border: 1px solid #cbd5e1;
  border-radius: 9px;
  font-size: 15px;
  padding: 0 14px;
}
#modal-kirim input.form-control,
#modal-kirim select.form-control { height: 44px; }
#modal-kirim .form-control:focus {
  border-color: #2c5282;
  box-shadow: 0 0 0 3px rgba(44, 82, 130, .15);
}

#modal-kirim .dn-petak-isian {
  display: grid;
  grid-template-columns: repeat(4, minmax(0, 1fr));
  gap: 0 22px;
  /* Kalau satu label terlipat dua baris, kotaknya tetap sejajar dengan kotak
     di sebelahnya - yang naik cuma labelnya. */
  align-items: end;
}
#modal-kirim .dn-petak-isian .form-group { margin-bottom: 16px; }
/* Baris terakhir tidak perlu jarak bawah - itu yang bikin badan modal
   terasa menggantung. */
#modal-kirim .dn-petak-isian .form-group:last-child { margin-bottom: 0; }
#modal-kirim .dn-petak-isian .dn-penuh { grid-column: 1 / -1; }
#modal-kirim .dn-angka-input { text-align: right; font-variant-numeric: tabular-nums; }
/* Keterangan barang biasanya satu kalimat panjang (bahan + ciri), jadi diberi
   tiga baris. Kalau kurang, kotaknya bisa ditarik. */
#modal-kirim textarea.form-control {
  min-height: 84px;
  padding: 10px 14px;
  font-size: 15px;
  line-height: 1.5;
  resize: vertical;
}

/* Layar pendek (misalnya laptop 1080p dengan skala 150%): jarak dirapatkan
   supaya tombol Apply tetap terlihat tanpa menggulir. */
@media (max-height: 640px) {
  #modal-kirim .modal-body { padding: 14px 24px; }
  #modal-kirim .dn-petak-isian .form-group { margin-bottom: 10px; }
  #modal-kirim textarea.form-control { min-height: 68px; }
}
@media (max-width: 991px) { #modal-kirim .dn-petak-isian { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
@media (max-width: 575px) {
  #modal-kirim .dn-petak-isian { grid-template-columns: 1fr; }
  #modal-kirim .modal-dialog { max-width: calc(100vw - 16px); margin: 8px auto; }
}
/* Kartu di dalam modal dibuat lebih ringan supaya tidak bertumpuk bayangan. */
:is(#modal-add-so,#modal-add-ws) .card { box-shadow: none; }
:is(#modal-add-so,#modal-add-ws) .card:last-child { margin-bottom: 0; }
/* Tombol filter diratakan ke dasar baris, bukan diberi label kosong -
   tingginya jadi sejajar dengan kotak isian di sebelahnya. */
/* padding-top = tinggi label + jaraknya, jadi tombol berdiri
   tepat sejajar dengan kotak isian di sebelahnya tanpa label kosong. */
:is(#modal-add-so,#modal-add-ws) .dn-aksi-filter {
  display: flex;
  align-items: flex-end;
  padding-top: 28px;
}
:is(#modal-add-so,#modal-add-ws) .dn-aksi-filter .btn { width: auto; padding: 0 18px; }
/* Kotak Qty di modal Add WS - selebar angkanya (ratusan ribu + desimal).
   Selama barisnya belum dicentang kotaknya terkunci: qty baru ada artinya
   kalau warnanya memang mau ditagih. */
#modal-add-ws .ws-qty {
  width: 120px;
  min-width: 120px;
  height: 32px;
  margin-left: auto;
  text-align: right;
  font-variant-numeric: tabular-nums;
}
#modal-add-ws .ws-qty[readonly] {
  background: #f1f5f9;
  color: #94a3b8;
  border-color: #e2e8f0;
  cursor: not-allowed;
}
#modal-add-ws td.dn-angka { vertical-align: middle; }

/* Kotak tanggal cukup selebar isinya (dd/mm/yyyy + ikon kalender). Lewat kolom
   grid biasa kotaknya ikut melebar sampai ±400px di modal selebar ini. */
:is(#modal-add-so,#modal-add-ws) .dn-filter-tgl { width: 230px; }

/* Kotak pencarian di atas tabel, rata kanan - seperti Search bawaan DataTables. */
/* Kotak cari sekarang di kepala kartu SJ List - didorong ke kanan, selebar
   secukupnya. (.dn-alat-tabel sudah tidak dipakai lagi.) */
:is(#modal-add-so,#modal-add-ws) .card > .card-header .dn-cari-kotak { margin-left: auto; }
:is(#modal-add-so,#modal-add-ws) .card > .card-header .dn-cari-kotak input { min-width: 240px; }
:is(#modal-add-so,#modal-add-ws) .dn-cari-kotak {
  position: relative;
  display: inline-flex;
  align-items: center;
}
:is(#modal-add-so,#modal-add-ws) .dn-cari-kotak i {
  position: absolute;
  left: 11px;
  font-size: 11px;
  color: #94a3b8;
  pointer-events: none;
}
:is(#modal-add-so,#modal-add-ws) .dn-cari-kotak input {
  height: 34px;
  width: 260px;
  max-width: 60vw;
  padding: 0 12px 0 30px;
  border: 1px solid #e2e8f0;
  border-radius: 8px;
  background: #fff;
  color: #1e293b;
  font-size: 13px;
}
:is(#modal-add-so,#modal-add-ws) .dn-cari-kotak input::placeholder { color: #94a3b8; }
:is(#modal-add-so,#modal-add-ws) .dn-cari-kotak input:focus {
  outline: 0;
  border-color: #2c5282;
  box-shadow: 0 0 0 3px rgba(44, 82, 130, .15);
}

/* Tabel panjang di modal dibatasi tingginya, bukan memanjangkan modal. */
.dn-table-tinggi { max-height: 320px; overflow-y: auto; }
/* Kepala tabel ikut menempel saat digulir - kolomnya banyak & barisnya panjang. */
:is(#modal-add-so,#modal-add-ws) .dn-table thead th {
  position: sticky;
  top: 0;
  z-index: 3;
}
/* Kolom Cek menempel di kanan supaya tetap terlihat waktu tabel digeser. */
:is(#modal-add-so,#modal-add-ws) .dn-table thead th:last-child,
:is(#modal-add-so,#modal-add-ws) .dn-table tbody td:last-child {
  position: sticky;
  right: 0;
  z-index: 2;
  box-shadow: -6px 0 8px -6px rgba(15, 23, 42, .18);
}
:is(#modal-add-so,#modal-add-ws) .dn-table thead th:last-child { z-index: 4; background: #f1f5f9; }
:is(#modal-add-so,#modal-add-ws) .dn-table tbody tr:nth-child(odd) td:last-child { background: #fff; }

/* Detail SJ di form utama: sama perlakuannya dengan daftar di modal -
   kepala tabel menempel saat digulir, kolom Action menempel di kanan. */
#inv-table-sj thead th {
  position: sticky;
  top: 0;
  z-index: 3;
}
#inv-table-sj thead th:last-child,
#inv-table-sj tbody td:last-child {
  position: sticky;
  right: 0;
  z-index: 2;
  box-shadow: -6px 0 8px -6px rgba(15, 23, 42, .18);
}
#inv-table-sj thead th:last-child { z-index: 4; }
/* Baris jumlah di kaki Detail SJ: menempel di bawah saat tabel digulir,
   jadi jumlah SJ & Total Qty selalu terlihat. */
#inv-table-sj tfoot.dn-sj-jumlah td {
  position: sticky;
  bottom: 0;
  z-index: 3;
  /* Jarak kiri-kanan & ukuran hurufnya sama dengan baris isi (lihat aturan
     "Tabel SJ (17 kolom)"), supaya angkanya sejajar kolomnya. */
  padding: 9px 7px;
  white-space: nowrap;
  vertical-align: middle;
  font-size: 11.5px;
  background: #eaf1fa;
  color: #1e3a5f;
  font-weight: 600;
  box-shadow: inset 0 1px 0 #bcd0e8;
}
#inv-table-sj tfoot.dn-sj-jumlah td:last-child {
  right: 0;
  z-index: 4;
}
#inv-table-sj tfoot .dn-sj-jumlah-qty {
  font-size: 12.5px;
  font-weight: 700;
  font-variant-numeric: tabular-nums;
}
#inv-table-sj tbody td:last-child { background: #f8fafc; }
#inv-table-sj tbody tr:nth-child(odd) td:last-child { background: #fff; }
:is(#modal-add-so,#modal-add-ws) .dn-table tbody tr:nth-child(even) td:last-child { background: #f8fafc; }
:is(#modal-add-so,#modal-add-ws) .dn-table tbody tr:hover td:last-child { background: #eaf2ff; }
/* Baris yang dicentang ditandai jelas, termasuk kolom Cek yang menempel. */
:is(#modal-add-so,#modal-add-ws) .dn-table tbody tr.is-pilih td,
:is(#modal-add-so,#modal-add-ws) .dn-table tbody tr.is-pilih td:last-child { background: #e0f2fe !important; }
:is(#modal-add-so,#modal-add-ws) .dn-table tbody tr.is-pilih td:first-child { box-shadow: inset 3px 0 0 #2c5282; }
:is(#modal-add-so,#modal-add-ws) .dn-table input[type="checkbox"] { width: 15px; height: 15px; cursor: pointer; }
/* Kolom Discount diisi tangan - kotaknya dibuat kecil & rata kanan. */
:is(#modal-add-so,#modal-add-ws) .dn-table .so-disc {
  height: 26px;
  width: 64px;
  margin-left: auto;
  padding: 0 8px;
  text-align: right;
  font-size: 12px;
  border-radius: 6px;
}
/* Angka disamakan lebarnya supaya digitnya sejajar antar baris. */
:is(#modal-add-so,#modal-add-ws) .dn-table td.dn-angka { font-variant-numeric: tabular-nums; }
/* Jumlah baris terpilih, tampil di kaki tabel. */
:is(#modal-add-so,#modal-add-ws) .dn-pilih-info {
  margin-top: 10px;
  font-size: 12px;
  color: #64748b;
}
:is(#modal-add-so,#modal-add-ws) .dn-pilih-info b { color: #1e3a5f; }
/* Invoice Date tidak diketik - isinya ikut tanggal SJ yang dipilih. */
.dn-tgl-ikut { cursor: default; }
.dn-tgl-ket {
  display: block;
  margin-top: 4px;
  color: #64748b;
  font-size: 11px;
  line-height: 1.4;
}
.dn-tgl-ket i { margin-right: 4px; opacity: .8; }
/* Aturan "satu invoice = satu tanggal SJ": keterangannya, dan SJ bertanggal
   lain yang jadi terkunci begitu ada satu yang dicentang. */
:is(#modal-add-so,#modal-add-ws) .dn-catatan-tgl {
  display: block;
  margin-top: 10px;
  padding: 6px 10px;
  border: 1px solid #bfdbfe;
  border-radius: 8px;
  background: #eff6ff;
  color: #1e40af;
  font-size: 11.5px;
  line-height: 1.5;
}
:is(#modal-add-so,#modal-add-ws) .dn-catatan-tgl[hidden] { display: none; }
:is(#modal-add-so,#modal-add-ws) .dn-catatan-tgl i { margin-right: 5px; opacity: .8; }
:is(#modal-add-so,#modal-add-ws) .dn-table tbody tr.is-kunci-tgl td,
:is(#modal-add-so,#modal-add-ws) .dn-table tbody tr.is-kunci-tgl td:last-child { background: #f8fafc !important; color: #94a3b8; }
:is(#modal-add-so,#modal-add-ws) .dn-table tbody tr.is-kunci-tgl td:first-child { box-shadow: none; }
/* Kotak centangnya memang dihilangkan, bukan cuma dimatikan - begitu
   pilihannya dikosongkan, kotaknya muncul lagi. */
:is(#modal-add-so,#modal-add-ws) .dn-table tbody tr.is-kunci-tgl input[type="checkbox"] { display: none; }
/* Total Qty dibuat menonjol - angka yang dicek sebelum menekan Apply. */
:is(#modal-add-so,#modal-add-ws) .dn-pilih-qty {
  display: inline-flex;
  align-items: baseline;
  gap: 6px;
  margin-left: 10px;
  padding: 3px 11px;
  border-radius: 999px;
  background: #e0f2fe;
  border: 1px solid #bae6fd;
  color: #1e3a5f;
  font-weight: 600;
}
:is(#modal-add-so,#modal-add-ws) .dn-pilih-qty b {
  font-size: 13.5px;
  font-variant-numeric: tabular-nums;
}
/* Baris SO/SJ bisa diklik untuk dipilih. */
:is(#modal-add-so,#modal-add-ws) .dn-table tbody tr { cursor: pointer; }
:is(#modal-add-so,#modal-add-ws) .dn-table tbody tr.is-terpilih td { background: #e0f2fe !important; }
/* Dua pilihan tarif VAT, sejajar di atas kotak nilainya. */
.nag-skin .dn-vat, :is(#modal-add-so,#modal-add-ws) .dn-vat {
  display: flex;
  gap: 14px;
  margin-bottom: 6px;
}
.nag-skin .dn-vat .form-check-label, :is(#modal-add-so,#modal-add-ws) .dn-vat .form-check-label {
  font-size: 11.5px;
  font-weight: 600;
  color: #475569;
  text-transform: none;
  letter-spacing: 0;
}
/* Kolom nilai yang boleh diisi tangan tetap rata kanan. */
.nag-skin .dn-angka-input, :is(#modal-add-so,#modal-add-ws) .dn-angka-input { text-align: right; }
/* Tombol buang baris di tabel Detail SJ */
.nag-skin .btn-dn-buang {
  height: 26px;
  width: 26px;
  padding: 0;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  border: 1px solid #fecaca;
  border-radius: 7px;
  background: #fee2e2;
  color: #991b1b;
  font-size: 11px;
}
.nag-skin .btn-dn-buang:hover { background: #fecaca; color: #7f1d1d; }
.nag-skin .btn-dn-buang:focus-visible {
  outline: 0;
  box-shadow: 0 0 0 3px rgba(220, 38, 38, .3);
}

/* Kosongkan satu tabel (Clear All): sekeluarga warna dengan tombol buang baris,
   tapi berlabel - aksinya jauh lebih besar, jadi tidak cukup berupa ikon saja.
   Sengaja tidak merah pekat supaya tidak bersaing dengan tombol Add di sebelahnya. */
.nag-skin .btn-dn-kosongkan {
  border: 1px solid #fecaca;
  background: #fee2e2;
  color: #991b1b;
}
.nag-skin .btn-dn-kosongkan:hover:not(:disabled) { background: #fecaca; color: #7f1d1d; }

/* Tabel detail saat masih kosong */
.nag-skin .dn-table tbody td.dn-kosong {
  text-align: center;
  color: #94a3b8;
  font-style: italic;
  padding: 28px 12px;
  white-space: normal;
}

@media (max-width: 767px) {
  .nag-skin .card > .card-body { padding: 14px; }
  .nag-skin .dataTables_filter { text-align: left; }
  .nag-skin .dataTables_filter label { justify-content: flex-start; }
  .nag-skin .dataTables_filter input { min-width: 0; width: 100%; }
  .nag-skin .dataTables_paginate .pagination { justify-content: center; margin-top: 8px; }
}


/* ==========================================================================
   Khusus Create Invoice Export
   Warna, sudut, dan jarak mengikuti yang sudah dipakai halaman lain - ini
   cuma menambah bentuk yang memang belum ada di Local.
   ========================================================================== */

/* Kepala kartu dengan tombol di kanannya (Add Row / Add SJ).
   .card-header bawaan AdminLTE punya ::before/::after untuk clearfix. Di dalam
   flex keduanya ikut jadi item, sehingga space-between membagi ruang untuk TIGA
   benda - tombolnya berakhir di tengah, bukan di kanan. Keduanya dimatikan di
   sini, lalu tombol didorong ke kanan pakai margin. */
.nag-skin .dn-kepala-aksi {
  display: flex;
  align-items: center;
  gap: 10px;
  flex-wrap: wrap;
}
.nag-skin .dn-kepala-aksi::before,
.nag-skin .dn-kepala-aksi::after { content: none; display: none; }
.nag-skin .dn-kepala-aksi .card-title { margin: 0; }
.nag-skin .dn-kepala-aksi > .btn { margin-left: auto; }
/* Dua tombol di kepala kartu (Clear All + Add) harus menempel. Tanpa ini
   keduanya ikut margin-left:auto, ruang kosong terbagi dua, dan tombolnya
   jadi berjauhan. Cukup tombol pertama yang mendorong ke kanan. */
.nag-skin .dn-kepala-aksi > .btn + .btn { margin-left: 0; }

/* Isian di dalam modal dibuat satu petak seragam - empat kolom yang sejajar
   ke bawah, bukan tiap baris beda jumlah kolom. */
.nag-skin .dn-petak-isian {
  display: grid;
  grid-template-columns: repeat(4, minmax(0, 1fr));
  gap: 0 16px;
}
.nag-skin .dn-petak-isian .form-group { margin-bottom: 14px; }
.nag-skin .dn-petak-isian .dn-penuh { grid-column: 1 / -1; }
@media (max-width: 991px) { .nag-skin .dn-petak-isian { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
@media (max-width: 575px) { .nag-skin .dn-petak-isian { grid-template-columns: 1fr; } }

/* Dua tombol ikon berdampingan di dalam sel tabel. */
.nag-skin .dn-aksi-sel { white-space: nowrap; }
.nag-skin .dn-aksi-sel .btn {
  width: 26px;
  height: 26px;
  padding: 0;
  font-size: 10.5px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
}
.nag-skin .dn-aksi-sel .btn + .btn { margin-left: 4px; }


/* ---- Alamat Purchaser & Receiver: alamat luar negeri biasa 4-5 baris ---- */
/* Dua kelas sekaligus supaya tidak kalah oleh .nag-skin textarea.dn-alamat
   (min-height 62px) yang letaknya lebih bawah di file ini. */
.nag-skin textarea.dn-alamat.dn-alamat-tinggi { min-height: 104px; }

/* ---- Tabel Shipment Details ----
   Kolomnya dibatasi persen supaya muat tanpa geser ke samping; teks yang
   kepanjangan dipotong, lengkapnya lewat tooltip. Kolom yang isinya selalu
   sama (Country of Origin, Transfer Point, Port of Loading) tidak ikut
   ditampilkan - lengkapnya tetap ada di modal. */
.nag-skin #inv-table-kirim { table-layout: fixed; }
/* Isinya dilipat, BUKAN dipotong jadi "...". Barisnya boleh jadi lebih tinggi -
   yang penting tidak ada isi yang tersembunyi dari mata. */
/* Judul kolom ditahan satu baris - kalau melipat, tinggi baris kepalanya jadi
   tidak rata dan tabel terlihat berantakan. Isinya tetap boleh melipat supaya
   tidak ada yang perlu digeser ke samping. */
.nag-skin #inv-table-kirim thead th {
  white-space: nowrap;
  vertical-align: bottom;
  line-height: 1.45;
}
.nag-skin #inv-table-kirim tbody td {
  white-space: normal;
  overflow-wrap: anywhere;
  vertical-align: top;
  line-height: 1.45;
}
.nag-skin #inv-table-kirim th:nth-child(1)  { width: 3.5%; }
.nag-skin #inv-table-kirim th:nth-child(2)  { width: 9%; }
.nag-skin #inv-table-kirim th:nth-child(3)  { width: 8%; }
.nag-skin #inv-table-kirim th:nth-child(4)  { width: 8%; }
.nag-skin #inv-table-kirim th:nth-child(5)  { width: 5.5%; }
.nag-skin #inv-table-kirim th:nth-child(6)  { width: 8.5%; }
.nag-skin #inv-table-kirim th:nth-child(7)  { width: 6%; }
.nag-skin #inv-table-kirim th:nth-child(8)  { width: 7%; }
.nag-skin #inv-table-kirim th:nth-child(9)  { width: 7%; }
.nag-skin #inv-table-kirim th:nth-child(10) { width: 7.5%; }
.nag-skin #inv-table-kirim th:nth-child(11) { width: 5.5%; }
.nag-skin #inv-table-kirim th:nth-child(12) { width: 18.5%; }
.nag-skin #inv-table-kirim th:nth-child(13) { width: 6%; }


/* ---- Kotak angka: tanpa tombol panah ----
   Panah bawaan <input type="number"> memakan ruang di kotak yang sudah sempit,
   dan yang lebih mengganggu: menggulir halaman di atas kotak yang sedang aktif
   ikut mengubah angkanya. Panahnya dimatikan di sini, penggulirannya dijaga
   lewat JavaScript - nilainya hanya berubah kalau memang diketik. */
.nag-skin input[type="number"]::-webkit-outer-spin-button,
.nag-skin input[type="number"]::-webkit-inner-spin-button,
#modal-kirim input[type="number"]::-webkit-outer-spin-button,
#modal-kirim input[type="number"]::-webkit-inner-spin-button,
:is(#modal-add-so,#modal-add-ws) input[type="number"]::-webkit-outer-spin-button,
:is(#modal-add-so,#modal-add-ws) input[type="number"]::-webkit-inner-spin-button {
  -webkit-appearance: none;
  margin: 0;
}
.nag-skin input[type="number"],
#modal-kirim input[type="number"],
:is(#modal-add-so,#modal-add-ws) input[type="number"] {
  -moz-appearance: textfield;
  appearance: textfield;
}

/* ---- Kepala tabel ikut menempel saat digulir ----
   Tabelnya dibatasi tingginya, jadi tanpa ini judul kolomnya ikut hilang ke
   atas dan angka di baris bawah jadi tidak jelas kolom apa. */
.nag-skin #inv-table-kirim thead th,
.nag-skin #inv-table-ringkas thead th {
  position: sticky;
  top: 0;
  z-index: 3;
}


/* ==========================================================================
   Rekap nilai (Summary) - dibuat seperti bagian bawah dokumen invoice.
   Tidak ada kotak isian: semua angkanya hasil hitungan, jadi tampil sebagai
   tulisan. Deretan kotak yang tidak bisa diketik justru mengundang orang
   mencoba mengetik.
   ========================================================================== */
.nag-skin .dn-rekap {
  width: 100%;
  border-collapse: collapse;
  font-variant-numeric: tabular-nums;
}

/* Judul kolom CM / FOB */
.nag-skin .dn-rekap thead th {
  padding: 0 14px 10px;
  text-align: right;
  font-size: 11px;
  font-weight: 700;
  letter-spacing: .6px;
  text-transform: none;
  color: #1e3a5f;
  border-bottom: 2px solid #1e3a5f;
}
.nag-skin .dn-rekap thead th:first-child { border-bottom-color: #1e3a5f; }
.nag-skin .dn-rekap-curr {
  display: inline-block;
  margin-left: 4px;
  padding: 1px 6px;
  border-radius: 4px;
  background: #e2e8f0;
  color: #475569;
  font-size: 10px;
  letter-spacing: .3px;
}
.nag-skin .dn-rekap-curr:empty { display: none; }

/* Baris biasa */
.nag-skin .dn-rekap tbody th,
.nag-skin .dn-rekap tbody td {
  padding: 9px 14px;
  border-bottom: 1px solid #edf1f6;
  font-size: 13px;
}
.nag-skin .dn-rekap tbody th {
  text-align: left;
  font-weight: 500;
  color: #475569;
  white-space: nowrap;
}
.nag-skin .dn-rekap tbody td {
  text-align: right;
  color: #1e293b;
  font-weight: 500;
  width: 30%;
}

/* Potongan: labelnya menjorok dan angkanya diberi tanda minus, supaya jelas
   ini mengurangi, bukan menambah. */
.nag-skin .dn-rekap-kurang th { padding-left: 28px !important; color: #64748b !important; }
.nag-skin .dn-rekap-kurang td { color: #b91c1c !important; }
.nag-skin .dn-rekap-kurang td::before { content: "\2212\00a0"; }
.nag-skin .dn-rekap-tambah td::before { content: "+\00a0"; }

/* Angka nol diredupkan - yang penting jadi langsung kelihatan. */
.nag-skin .dn-rekap td.dn-nol { color: #cbd5e1 !important; }
.nag-skin .dn-rekap td.dn-nol::before { content: none; }

/* Subtotal: dipisah garis di atasnya. */
.nag-skin .dn-rekap-sub th,
.nag-skin .dn-rekap-sub td {
  border-top: 1px solid #cbd5e1;
  font-weight: 600 !important;
  color: #1e293b !important;
}

/* Grand Total: pita biru muda berbingkai tipis, angka paling besar di kartu ini.
   Sengaja tidak navy penuh - warna itu sudah dipakai kepala kartu, dan dua pita
   navy berdekatan membuat Grand Total terbaca seperti judul kedua.
   Bingkainya pakai box-shadow inset, bukan border: tabel ini border-collapse,
   dan border di mode itu mengabaikan sudut bulat. */
.nag-skin .dn-rekap tfoot th,
.nag-skin .dn-rekap tfoot td {
  padding: 14px 14px;
  background: #eaf1fa;
  color: #1e3a5f;
  box-shadow: inset 0 1px 0 #bcd0e8, inset 0 -1px 0 #bcd0e8;
}
.nag-skin .dn-rekap tfoot th {
  text-align: left;
  font-size: 12px;
  font-weight: 700;
  letter-spacing: .6px;
  text-transform: none;
  border-radius: 10px 0 0 10px;
  box-shadow: inset 0 1px 0 #bcd0e8, inset 0 -1px 0 #bcd0e8, inset 1px 0 0 #bcd0e8;
}
.nag-skin .dn-rekap tfoot td {
  text-align: right;
  font-size: 17px;
  font-weight: 700;
}
.nag-skin .dn-rekap tfoot td:last-child {
  border-radius: 0 10px 10px 0;
  box-shadow: inset 0 1px 0 #bcd0e8, inset 0 -1px 0 #bcd0e8, inset -1px 0 0 #bcd0e8;
}
.nag-skin .dn-rekap tfoot td.dn-nol { color: #94a3b8 !important; }

@media (max-width: 575px) {
  .nag-skin .dn-rekap tbody th,
  .nag-skin .dn-rekap tbody td { padding: 8px 8px; font-size: 12px; }
  .nag-skin .dn-rekap-kurang th { padding-left: 16px !important; }
  .nag-skin .dn-rekap tfoot td { font-size: 14px; }
}

/* ---- Tabel Invoice Summary ----
   Lebar dibagi tetap dalam persen supaya kotak angka tidak terjepit kolom teks.
   Judul kolom dibuat SATU BARIS. Karena beberapa judulnya panjang
   ("Total Pieces (Custom Units)", "Qty Invoiced (Each)"), dua kolom itu diberi
   ruang lebih - diambil dari Color Name dan kedua Total yang ruangnya longgar.
   Kalau suatu saat judulnya diubah lagi jadi lebih panjang, lebar kolom ini
   yang perlu ditambah, bukan dipaksa melipat.
   Angkanya diukur sampai layar 1280px: "Total Pieces (Custom Units)" butuh
   148px, jadi kolomnya 15%. Ruangnya diambil dari kedua Total, yang judulnya
   cuma butuh sekitar 60px. */
.nag-skin #inv-table-ringkas { table-layout: fixed; }
.nag-skin #inv-table-ringkas thead th {
  white-space: nowrap;
  line-height: 1.3;
  vertical-align: middle;
}
.nag-skin #inv-table-ringkas tbody td { vertical-align: middle; }
.nag-skin #inv-table-ringkas .form-control { width: 100%; }
.nag-skin #inv-table-ringkas th:nth-child(1)  { width: 3%; }
.nag-skin #inv-table-ringkas th:nth-child(2)  { width: 9%; }
.nag-skin #inv-table-ringkas th:nth-child(3)  { width: 13.5%; }
.nag-skin #inv-table-ringkas th:nth-child(4)  { width: 15%; }
.nag-skin #inv-table-ringkas th:nth-child(5)  { width: 11.5%; }
.nag-skin #inv-table-ringkas th:nth-child(6)  { width: 9.5%; }
.nag-skin #inv-table-ringkas th:nth-child(7)  { width: 9.5%; }
.nag-skin #inv-table-ringkas th:nth-child(8)  { width: 6%; }
.nag-skin #inv-table-ringkas th:nth-child(9)  { width: 11.5%; }
.nag-skin #inv-table-ringkas th:nth-child(10) { width: 11.5%; }


/* ---- Invoice Summary: semua sel kotak isian ----
   Semua sel sengaja dibuat kotak supaya barisnya seragam. Yang tidak boleh
   diubah dikunci: warnanya abu, tidak ada cincin fokus, dan dilewati tombol
   Tab - jadi Tab langsung lompat antar kotak yang memang perlu diketik. */
.nag-skin #inv-table-ringkas thead th:nth-child(1) { text-align: center; }
.nag-skin #inv-table-ringkas thead th:nth-child(n+4) { text-align: right; }
.nag-skin #inv-table-ringkas tbody td { padding: 5px 6px; }
.nag-skin #inv-table-ringkas .form-control {
  height: 32px !important;
  padding: 2px 9px !important;
  font-size: 12.5px !important;
  border: 1px solid #cbd5e1;
  border-radius: 6px;
  background: #fff;
  color: #1e293b;
}
.nag-skin #inv-table-ringkas .form-control:focus {
  border-color: #2c5282;
  box-shadow: 0 0 0 3px rgba(44, 82, 130, .15);
}
.nag-skin #inv-table-ringkas .form-control[readonly] {
  background: #eef2f7;
  border-color: #e2e8f0;
  color: #475569;
  cursor: default;
}
.nag-skin #inv-table-ringkas .form-control[readonly]:focus { box-shadow: none; border-color: #e2e8f0; }
/* Hasil hitungan akhir baris ditebalkan - itu yang paling sering dicek. */
.nag-skin #inv-table-ringkas .form-control.dn-hasil { font-weight: 600; color: #1e293b; }
/* Total Pieces: pilihan satuan (PCS/SET) menempel di kiri isiannya. */
.nag-skin #inv-table-ringkas .dn-satuan-grup { display: flex; gap: 5px; }
/* Panahnya digambar sendiri: gaya form-control menyembunyikan panah bawaan,
   padahal tanpa panah kotak ini tidak terlihat sebagai pilihan. */
.nag-skin #inv-table-ringkas .form-control.dn-satuan {
  flex: 0 0 66px;
  width: 66px;
  padding: 2px 20px 2px 8px !important;
  font-weight: 600;
  color: #1e3a5f;
  cursor: pointer;
  -webkit-appearance: none;
  appearance: none;
  background: #fff url("data:image/svg+xml,%3Csvg xmlns=%27http://www.w3.org/2000/svg%27 viewBox=%270 0 16 16%27%3E%3Cpath fill=%27none%27 stroke=%27%231e3a5f%27 stroke-width=%272%27 stroke-linecap=%27round%27 stroke-linejoin=%27round%27 d=%27M4 6l4 4 4-4%27/%3E%3C/svg%3E") no-repeat right 6px center / 10px 10px;
}
.nag-skin #inv-table-ringkas .dn-satuan-grup .dn-angka-input { flex: 1 1 auto; min-width: 0; }

/* ---- Tombol aksi dikunci di kanan, sama seperti Detail SJ ---- */
#inv-table-kirim thead th:last-child,
#inv-table-kirim tbody td:last-child {
  position: sticky;
  right: 0;
  z-index: 2;
  box-shadow: -6px 0 8px -6px rgba(15, 23, 42, .18);
}
#inv-table-kirim thead th:last-child { z-index: 4; background: #f1f5f9; }
#inv-table-kirim tbody td:last-child { background: #f8fafc; }
#inv-table-kirim tbody tr:nth-child(odd) td:last-child { background: #fff; }



/* Keterangan kecil di samping label - menjelaskan kolom ini mengisi apa. */
.nag-skin .dn-label-bantu {
  font-weight: 500;
  text-transform: none;
  letter-spacing: 0;
  color: #64748b;
  font-size: 11px;
}

/* Satu blok yang bisa ditambah/dikurangi (Shipment Details). */
.nag-skin .dn-baris-ulang {
  border: 1px solid #e2e8f0;
  border-radius: 12px;
  padding: 14px 14px 10px;
  margin-bottom: 12px;
  background: #f8fafc;
}
.nag-skin .dn-baris-ulang:last-child { margin-bottom: 0; }
.nag-skin .dn-baris-kepala {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 10px;
}
.nag-skin .dn-baris-nomor {
  font-size: 11px;
  font-weight: 700;
  letter-spacing: .4px;
  text-transform: none;
  color: #1e3a5f;
  background: #e2e8f0;
  padding: 3px 10px;
  border-radius: 20px;
}

/* Alamat & catatan: beberapa baris, tidak perlu diseret melebar. */
.nag-skin textarea.dn-alamat {
  resize: vertical;
  /* Alamat Shipper & Seller biasanya 3-4 baris - kotaknya dibuat muat segitu
     tanpa harus digulir di dalam kotaknya. */
  min-height: 80px;
  font-size: 12.5px;
  line-height: 1.5;
}

/* Sel yang dihitung sendiri - dibedakan supaya tidak dikira bisa diketik. */
.nag-skin .dn-terkunci {
  background: #f1f5f9;
  color: #334155;
  font-weight: 600;
}

/* Isian angka di dalam tabel: rapat dan rata kanan. */
.nag-skin .dn-table .dn-angka-input,
.nag-skin .dn-table input.form-control-sm,
.nag-skin .dn-table select.form-control-sm {
  height: 30px;
  padding: 2px 8px;
  font-size: 12px;
}
.nag-skin .dn-table .dn-angka-input { text-align: right; font-variant-numeric: tabular-nums; }


/* ==========================================================================
   Form Create Invoice Export: Detail SJ dilipat.
   Semua kelas di sini khusus halaman itu - halaman Local tidak terpengaruh.
   ========================================================================== */

/* ---- Detail SJ di dalam kartu Invoice Summary ----
   Dibuat seperti pita tipis berlatar abu muda, dipisah garis dari tabel di
   bawahnya - supaya jelas ini bagian sumber, bukan baris Invoice Summary. */
.nag-skin .dn-sj-pita {
  background: #f8fafc;
  border: 1px solid #e2e8f0;
  border-radius: 10px;
  padding: 10px 14px;
  margin-bottom: 16px;
}
.nag-skin .dn-sj-label {
  font-size: 11px;
  font-weight: 700;
  letter-spacing: .5px;
  text-transform: none;
  color: #1e3a5f;
  display: inline-flex;
  align-items: center;
  gap: 7px;
  padding-right: 12px;
  border-right: 1px solid #cbd5e1;
}
.nag-skin .dn-sj-label i { opacity: .65; }
.nag-skin .dn-sj-pita #sj-isi { margin-top: 12px; background: #fff; }

/* ---- Detail SJ dilipat ---- */
.nag-skin .dn-lipat-strip {
  display: flex;
  align-items: center;
  gap: 12px;
  flex-wrap: wrap;
}
.nag-skin .dn-lipat-ringkas {
  display: flex;
  align-items: center;
  gap: 8px;
  flex-wrap: wrap;
  font-size: 13px;
  color: #475569;
}
.nag-skin .dn-lipat-ringkas b { color: #1e3a5f; font-variant-numeric: tabular-nums; }
.nag-skin .dn-lipat-titik { color: #cbd5e1; }
.nag-skin .dn-lipat-kosong { color: #94a3b8; font-style: italic; }

.nag-skin .btn-dn-lipat {
  margin-left: auto;
  height: 32px;
  padding: 0 12px;
  font-size: 12px;
  font-weight: 600;
  background: #f1f5f9;
  border: 1px solid #cbd5e1;
  color: #1e3a5f;
  display: inline-flex;
  align-items: center;
  gap: 6px;
}
.nag-skin .btn-dn-lipat:hover { background: #e2e8f0; color: #1e3a5f; }
.nag-skin .btn-dn-lipat .dn-lipat-panah { font-size: 10px; transition: transform .2s ease; }
.nag-skin .btn-dn-lipat[aria-expanded="true"] .dn-lipat-panah { transform: rotate(180deg); }

/* Jarak strip ke tabel waktu dibuka. */
.nag-skin #sj-isi { margin-top: 14px; }

/* ---- Summary dilipat ----
   Tombol Show details duduk di kepala kartu yang navy, jadi tepinya dibuat
   tanpa garis supaya tidak terlihat seperti kotak di atas kotak. */
.nag-skin .card-header .btn-dn-lipat { border-color: transparent; }
/* Waktu terlipat, garis bawah judul CM / FOB menempel langsung ke pita Grand
   Total dan memotong sudut bulatnya - garisnya dilepas selama terlipat. */
.nag-skin .dn-rekap:has(#inv-rekap-rinci[hidden]) thead th { border-bottom-color: transparent; }
@media (prefers-reduced-motion: no-preference) {
  .nag-skin #inv-rekap-rinci:not([hidden]) tr { animation: dn-rekap-buka .2s ease-out; }
}
@keyframes dn-rekap-buka {
  from { opacity: 0; }
  to   { opacity: 1; }
}

/* ==========================================================================
   Tombol, isian & select2 ukuran kecil - dipakai semua halaman invoice
   --------------------------------------------------------------------------
   Aktif lewat kelas dn-kontrol-sm di pembungkus .nag-skin: daftar, Create/Edit
   dan semua modal Invoice Local & Export. Setara form-control-sm Bootstrap:
   tinggi 31px, huruf 12.5px. Tombol dan pita Grand Total ikut mengecil;
   tombol ikon di dalam tabel tetap 26px seperti semula.

   Modal Shipment ikut mengecil, tapi lebarnya tetap (sampai 1400px) supaya
   empat kolomnya tetap lega di laptop 14 inci. Aturannya diawali
   .nag-skin.dn-kontrol-sm supaya mengalahkan aturan #modal-kirim.
   ========================================================================== */
.nag-skin.dn-kontrol-sm .form-control,
.nag-skin.dn-kontrol-sm .input-group-text {
  height: 31px;
  padding: 4px 10px;
  font-size: 12.5px;
  border-radius: 6px;
}
.nag-skin.dn-kontrol-sm textarea.form-control { padding: 6px 10px; }
.nag-skin.dn-kontrol-sm .input-group > .form-control { border-radius: 6px 0 0 6px; }

.nag-skin.dn-kontrol-sm .select2-container .select2-selection--single {
  height: 31px !important;
  border-radius: 6px;
}
.nag-skin.dn-kontrol-sm .select2-container .select2-selection--single .select2-selection__rendered {
  line-height: 29px;
  padding-left: 10px;
  font-size: 12.5px;
}
.nag-skin.dn-kontrol-sm .select2-container .select2-selection--single .select2-selection__arrow { height: 29px; }

/* ---- Tombol ---- */
.nag-skin.dn-kontrol-sm .btn {
  height: 31px;
  padding: 0 12px;
  font-size: 12.5px;
  border-radius: 6px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 6px;
}
.nag-skin.dn-kontrol-sm .dn-kaki .btn,
.nag-skin.dn-kontrol-sm :is(#modal-add-so,#modal-add-ws) .dn-aksi-filter .btn { padding: 0 14px; }
/* Tombol ikon di dalam sel tabel sudah kecil - dikembalikan ke ukurannya. */
.nag-skin.dn-kontrol-sm .dn-aksi-sel .btn {
  width: 26px;
  height: 26px;
  padding: 0;
  font-size: 10.5px;
  border-radius: 7px;
}

/* ---- Pita Grand Total ---- */
.nag-skin.dn-kontrol-sm .dn-rekap tfoot th,
.nag-skin.dn-kontrol-sm .dn-rekap tfoot td { padding: 9px 12px; }
.nag-skin.dn-kontrol-sm .dn-rekap tfoot th { font-size: 11.5px; border-radius: 8px 0 0 8px; }
.nag-skin.dn-kontrol-sm .dn-rekap tfoot td { font-size: 14px; }
.nag-skin.dn-kontrol-sm .dn-rekap tfoot td:last-child { border-radius: 0 8px 8px 0; }

.nag-skin.dn-kontrol-sm :is(#modal-add-so,#modal-add-ws) .dn-cari-kotak input { height: 31px; font-size: 12.5px; border-radius: 6px; }

/* Kepala, badan & kaki modal Shipment disamakan dengan modal Add SJ. */
.nag-skin.dn-kontrol-sm #modal-kirim .modal-header { padding: 13px 18px; }
.nag-skin.dn-kontrol-sm #modal-kirim .modal-title { font-size: 14px; }
.nag-skin.dn-kontrol-sm #modal-kirim .modal-title i { font-size: 13px; }
.nag-skin.dn-kontrol-sm #modal-kirim .modal-body { padding: 18px; }
.nag-skin.dn-kontrol-sm #modal-kirim .modal-footer { padding: 12px 18px; }
.nag-skin.dn-kontrol-sm #modal-kirim .modal-footer .btn { height: 31px; padding: 0 14px; font-size: 12.5px; gap: 6px; }
.nag-skin.dn-kontrol-sm #modal-kirim label { font-size: 11.5px; margin-bottom: 5px; }
.nag-skin.dn-kontrol-sm #modal-kirim .form-control {
  padding: 4px 10px;
  font-size: 12.5px;
  border-radius: 6px;
}
.nag-skin.dn-kontrol-sm #modal-kirim input.form-control,
.nag-skin.dn-kontrol-sm #modal-kirim select.form-control { height: 31px; }
/* Tiga baris keterangan barang tetap muat. */
.nag-skin.dn-kontrol-sm #modal-kirim textarea.form-control { min-height: 72px; padding: 6px 10px; }
.nag-skin.dn-kontrol-sm #modal-kirim .dn-petak-isian { gap: 0 16px; }
.nag-skin.dn-kontrol-sm #modal-kirim .dn-petak-isian .form-group { margin-bottom: 12px; }
.nag-skin.dn-kontrol-sm #modal-kirim .dn-petak-isian .form-group:last-child { margin-bottom: 0; }

/* ==========================================================================
   Isian wajib yang masih kosong waktu Save ditekan
   --------------------------------------------------------------------------
   Bingkai merah tipis tanpa ikon - ikon peringatan bawaan Bootstrap
   (.is-invalid) duduk di dalam kotak dan menutupi isian. Hilang begitu diisi.
   ========================================================================== */
.nag-skin .form-control.dn-kurang,
.nag-skin .dn-kurang + .select2-container .select2-selection--single {
  border-color: #dc2626 !important;
  box-shadow: 0 0 0 3px rgba(220, 38, 38, .12) !important;
}

/* ==========================================================================
   Modal Add SJ & Add Shipment
   --------------------------------------------------------------------------
   Keduanya dibuka dari halaman yang sama, jadi lebar & jarak tepinya dibuat
   sama supaya tidak terasa dua gaya modal. 1400px: tabel SJ masih lega, dan
   empat kolom isian Shipment tidak kepanjangan. Tinggi tetap beda - Add SJ
   setinggi layar karena tabelnya bergulir, Shipment ikut isinya.
   dn-modal-seragam hanya dipasang di Invoice Export; modal Add SJ di Invoice
   Local tidak ikut berubah.
   ========================================================================== */
:is(#modal-add-so,#modal-add-ws).dn-modal-seragam .modal-dialog,
#modal-kirim .modal-dialog {
  max-width: min(1400px, calc(100vw - 32px));
  margin: 16px auto;
}
:is(#modal-add-so,#modal-add-ws).dn-modal-seragam .modal-dialog-scrollable {
  height: calc(100% - 32px);
  max-height: calc(100% - 32px);
}

/* ==========================================================================
   Kotak tanggal & kalender (datepicker jQuery UI)
   --------------------------------------------------------------------------
   Tampilannya mengikuti kalender Debit Note: kecil, « » untuk ganti bulan,
   hari ini abu, tanggal terpilih navy, tanggal bulan lain redup.
   Popup-nya menempel ke <body> atau ke modal, jadi tidak di-scope ke .nag-skin;
   kelas dn-kalender baru dipasang waktu kalender dibuka dari halaman ini.
   ========================================================================== */
/* Kotak teks biasa diberi ikon kalender kecil supaya tetap terlihat bisa diklik. */
.nag-skin input.form-control.dn-tgl {
  padding-right: 30px;
  background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16' fill='none' stroke='%2364748b' stroke-width='1.4' stroke-linecap='round'%3E%3Crect x='2' y='3' width='12' height='11' rx='2'/%3E%3Cpath d='M2 6.5h12M5.5 1.5v3M10.5 1.5v3'/%3E%3C/svg%3E");
  background-repeat: no-repeat;
  background-position: right 10px center;
  background-size: 13px 13px;
  cursor: pointer;
}

#ui-datepicker-div.dn-kalender {
  width: 236px;
  margin-top: 4px;
  padding: 8px;
  background: #fff;
  border: 1px solid #e2e8f0;
  border-radius: 10px;
  box-shadow: 0 10px 25px rgba(15, 23, 42, .14);
  font-family: inherit;
  font-size: 12.5px;
  color: #1e293b;
  z-index: 1070 !important;
}
#ui-datepicker-div.dn-kalender .ui-datepicker-header {
  position: relative;
  padding: 0 0 4px;
  background: none;
  border: 0;
  border-radius: 0;
}
#ui-datepicker-div.dn-kalender .ui-datepicker-title {
  margin: 0 32px;
  line-height: 28px;
  font-size: 12.5px;
  font-weight: 600;
  color: #1e3a5f;
}

/* Tombol « » - ikon bawaan jQuery UI diganti teks. */
#ui-datepicker-div.dn-kalender .ui-datepicker-prev,
#ui-datepicker-div.dn-kalender .ui-datepicker-next {
  top: 0;
  width: 28px;
  height: 28px;
  display: flex;
  align-items: center;
  justify-content: center;
  border: 0;
  border-radius: 8px;
  background: none;
  cursor: pointer;
}
#ui-datepicker-div.dn-kalender .ui-datepicker-prev,
#ui-datepicker-div.dn-kalender .ui-datepicker-prev-hover { left: 0; top: 0; }
#ui-datepicker-div.dn-kalender .ui-datepicker-next,
#ui-datepicker-div.dn-kalender .ui-datepicker-next-hover { right: 0; top: 0; }
#ui-datepicker-div.dn-kalender .ui-datepicker-prev:hover,
#ui-datepicker-div.dn-kalender .ui-datepicker-next:hover { background: #eef2f7; border: 0; }
#ui-datepicker-div.dn-kalender .ui-datepicker-prev span,
#ui-datepicker-div.dn-kalender .ui-datepicker-next span {
  position: static;
  display: block;
  width: auto;
  height: auto;
  margin: 0;
  background: none;
  text-indent: 0;
  overflow: visible;
  font-size: 16px;
  font-weight: 700;
  line-height: 1;
  color: #1d4ed8;
}

#ui-datepicker-div.dn-kalender table.ui-datepicker-calendar {
  width: 100%;
  margin: 0;
  font-size: 12.5px;
  border-collapse: separate;
  border-spacing: 2px;
}
#ui-datepicker-div.dn-kalender th {
  padding: 4px 0;
  font-size: 10.5px;
  font-weight: 600;
  letter-spacing: .3px;
  text-transform: none;
  text-align: center;
  color: #64748b;
}
#ui-datepicker-div.dn-kalender td { padding: 0; border: 0; }
#ui-datepicker-div.dn-kalender td a,
#ui-datepicker-div.dn-kalender td span {
  display: block;
  padding: 5px 0;
  text-align: center;
  line-height: 1.3;
  border: 0;
  border-radius: 7px;
  background: transparent;
  color: #1e293b;
  font-weight: 400;
}
#ui-datepicker-div.dn-kalender td a:hover { background: #eef2f7; color: #0f172a; }
#ui-datepicker-div.dn-kalender td.ui-datepicker-other-month a,
#ui-datepicker-div.dn-kalender td.ui-datepicker-other-month span { color: #cbd5e1; opacity: 1; }
#ui-datepicker-div.dn-kalender td a.ui-state-highlight { background: #e2e8f0; color: #0f172a; font-weight: 600; }
#ui-datepicker-div.dn-kalender td a.ui-state-active,
#ui-datepicker-div.dn-kalender td a.ui-state-active:hover { background: #1e3a5f; color: #fff; font-weight: 600; }

/* ==========================================================================
   Daftar Invoice Export (export/index.blade.php)
   ========================================================================== */
/* Tombol PDF / Excel dengan pilihan versi CM / FOB. */
.nag-skin .dn-pilih-versi .dropdown-toggle::after { margin-left: 3px; vertical-align: 1px; }
.dn-menu-versi {
  min-width: 170px;
  padding: 4px;
  border: 1px solid #e2e8f0;
  border-radius: 10px;
  box-shadow: 0 10px 25px rgba(15, 23, 42, .14);
  font-size: 12px;
}
.dn-menu-versi .dropdown-item {
  display: flex;
  align-items: baseline;
  gap: 10px;
  padding: 7px 10px;
  border-radius: 7px;
  color: #1e293b;
}
.dn-menu-versi .dropdown-item b { min-width: 30px; color: #1e3a5f; }
.dn-menu-versi .dropdown-item span { color: #64748b; font-size: 11px; }
.dn-menu-versi .dropdown-item:hover,
.dn-menu-versi .dropdown-item:focus { background: #eef2f7; color: #0f172a; }

/* Modal rincian Export: selebar modal di form, dokumennya memang lebar. */
/* Modal rincian Export: selebar modal di form, tapi tingginya ikut isinya.
   Bawaan .modal-dialog-scrollable selalu setinggi layar - dengan tab, isi yang
   pendek jadi menyisakan ruang kosong besar di bawahnya. */
#modal-inv-detail .dn-modal-rincian { max-width: min(1400px, calc(100vw - 32px)); }
#modal-inv-detail .dn-modal-rincian.modal-dialog-scrollable { height: auto; max-height: calc(100% - 3.5rem); }
#modal-inv-detail .dn-modal-rincian.modal-dialog-scrollable .modal-content { max-height: 100%; }
.dn-det-info.dn-det-info-4 { grid-template-columns: repeat(4, minmax(0, 1fr)); }

/* Empat pihak berdampingan: label kecil, nama tebal, alamat apa adanya. */
.dn-det-pihak {
  display: grid;
  grid-template-columns: repeat(4, minmax(0, 1fr));
  gap: 12px;
  margin-bottom: 16px;
}
.dn-det-pihak-kotak {
  background: #fff;
  border: 1px solid #e2e8f0;
  border-radius: 12px;
  padding: 12px 14px;
  font-size: 12.5px;
  color: #1e293b;
}
.dn-det-pihak-label {
  display: block;
  font-size: 10.5px;
  font-weight: 600;
  letter-spacing: .4px;
  text-transform: none;
  color: #64748b;
  margin-bottom: 4px;
}
.dn-det-pihak-kotak b { display: block; color: #1e3a5f; }
.dn-det-pihak-kotak p { margin: 4px 0 0; white-space: pre-line; color: #475569; line-height: 1.45; }

.dn-det-rekap-bungkus { display: flex; justify-content: flex-end; }
.dn-det-rekap-bungkus > div { width: min(520px, 100%); }
.dn-det-rekap {
  background: #fff;
  border: 1px solid #e2e8f0;
  border-radius: 12px;
  padding: 14px 16px;
  margin-bottom: 16px;
}
/* Invoice Summary di modal: 10 kolom, muat tanpa digeser. */
#modal-inv-detail #det-summary { table-layout: fixed; }
#modal-inv-detail #det-summary th:nth-child(1),
#modal-inv-detail #det-summary td:nth-child(1) { width: 3%; }
#modal-inv-detail #det-summary th:nth-child(2),
#modal-inv-detail #det-summary td:nth-child(2) { width: 9%; }
#modal-inv-detail #det-summary th:nth-child(3),
#modal-inv-detail #det-summary td:nth-child(3) { width: 17%; white-space: normal; }
#modal-inv-detail #det-summary th:nth-child(n+4),
#modal-inv-detail #det-summary td:nth-child(n+4) { width: 8.875%; }

/* Detail SJ: 15 kolom nilai pendek - dibiarkan satu baris, digeser kalau perlu. */
#modal-inv-detail #det-tabel th,
#modal-inv-detail #det-tabel td { padding: 5px 8px; font-size: 11px; }

/* ----- Blok pengiriman: label & isi, bukan tabel -----
   Sebagai tabel isinya 18 kolom; dipaksa muat malah terpenggal, dibiarkan lebar
   malah harus digeser. Bentuk label-isi ini sama dengan cetakannya. */
#modal-inv-detail .dn-det-kirim-kotak {
  background: #fff;
  border: 1px solid #e2e8f0;
  border-radius: 12px;
  padding: 12px 14px 14px;
  margin-bottom: 12px;
}
#modal-inv-detail .dn-det-kirim-kepala {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 10px;
  margin-bottom: 10px;
}
#modal-inv-detail .dn-det-kirim-judul {
  font-size: 10.5px;
  font-weight: 700;
  letter-spacing: .4px;
  text-transform: none;
  color: #1e3a5f;
}
#modal-inv-detail .dn-det-kirim-dari { color: #94a3b8; font-weight: 600; }
#modal-inv-detail .dn-det-kirim-navigasi { display: flex; gap: 6px; }
#modal-inv-detail .dn-det-kirim-navigasi .btn {
  width: 28px;
  height: 28px;
  padding: 0;
  justify-content: center;
  margin-left: 0;
}
/* Beberapa shipment tetap dalam SATU kotak: yang tampil satu lembar, panahnya
   mengganti isi. Tidak ada jalur yang digeser - jadi tidak ada scrollbar. */
#modal-inv-detail .dn-det-kirim-navigasi .btn:disabled { opacity: .45; }
#modal-inv-detail .dn-det-kirim-grid {
  display: grid;
  grid-template-columns: repeat(4, minmax(0, 1fr));
  gap: 10px 18px;
  margin: 0;
}
#modal-inv-detail .dn-det-kirim-grid dt {
  font-size: 10.5px;
  font-weight: 600;
  letter-spacing: .4px;
  text-transform: none;
  color: #64748b;
  margin-bottom: 2px;
}
#modal-inv-detail .dn-det-kirim-grid dd {
  margin: 0;
  font-size: 12.5px;
  color: #1e293b;
  word-break: break-word;
}
#modal-inv-detail .dn-det-kirim-grid .dn-det-penuh { grid-column: 1 / -1; }
#modal-inv-detail .dn-det-kosong {
  background: #fff;
  border: 1px solid #e2e8f0;
  border-radius: 12px;
  padding: 18px;
  text-align: center;
  color: #94a3b8;
  font-style: italic;
  font-size: 12.5px;
  margin-bottom: 12px;
}
@media (max-width: 991px) {
  #modal-inv-detail .dn-det-kirim-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
}
/* Kartu putih pembungkus Detail SJ + Invoice Summary di modal. */
#modal-inv-detail .dn-det-kartu {
  background: #fff;
  border: 1px solid #e2e8f0;
  border-radius: 12px;
  padding: 14px;
  margin-bottom: 16px;
}
#modal-inv-detail .dn-det-kartu .dn-table-scroll { border: 0; margin-bottom: 0; }
#modal-inv-detail .dn-det-kartu .dn-sj-pita { margin-bottom: 14px; }

/* Keterangan barang panjang ikut dilipat. */
#modal-inv-detail .dn-det-panjang { white-space: normal; }

@media (max-width: 991px) {
  .dn-det-pihak,
  .dn-det-info.dn-det-info-4 { grid-template-columns: repeat(2, minmax(0, 1fr)); }
}
@media (max-width: 575px) {
  .dn-det-pihak,
  .dn-det-info.dn-det-info-4 { grid-template-columns: 1fr; }
}

/* Tabel SJ (17 kolom): sel dirapatkan supaya muat tanpa digeser ke samping. */
.nag-skin #inv-table-sj thead th,
.nag-skin #so-table-sj thead th,
.nag-skin #inv-table-sj tbody td,
.nag-skin #so-table-sj tbody td {
  padding-left: 7px;
  padding-right: 7px;
  font-size: 11.5px;
}

/* ==========================================================================
   Animasi tombol
   --------------------------------------------------------------------------
   Sengaja ditaruh di akhir supaya berlaku untuk semua tombol di modul ini
   sekaligus: daftar, create, edit, modal Add SJ, dan modal rincian.

   Gerakannya dibuat pendek (<= .25s) dan kecil (1-2px). Tombol di dalam tabel
   ditekan puluhan kali sehari - animasi yang panjang atau melompat justru
   melelahkan. Yang dikejar cuma tiga hal: terasa bisa diklik saat disentuh,
   terasa tertekan saat diklik, dan jelas terlihat saat difokus lewat keyboard.
   ========================================================================== */

.nag-skin .btn,
.nag-skin .dn-no-link {
  transition:
    transform .16s ease,
    box-shadow .16s ease,
    filter .16s ease,
    background-color .16s ease,
    border-color .16s ease,
    color .16s ease;
  will-change: transform;
}

/* Ikon di dalam tombol digerakkan terpisah dari tombolnya. */
.nag-skin .btn > i,
.nag-skin .btn > .fa,
.nag-skin .btn > .fas {
  transition: transform .18s ease;
}

/* ---- Tersentuh: sedikit terangkat ---- */
.nag-skin .btn:hover:not(:disabled):not(.disabled) {
  transform: translateY(-1px);
  box-shadow: 0 4px 10px -2px rgba(15, 23, 42, .28);
}

/* ---- Ditekan: benar-benar turun, bukan cuma berubah warna ---- */
.nag-skin .btn:active:not(:disabled):not(.disabled) {
  transform: translateY(0) scale(.97);
  box-shadow: 0 1px 2px rgba(15, 23, 42, .2);
  transition-duration: .06s;
}

/* ---- Fokus keyboard: cincin yang jelas, tidak bergantung warna tombol ---- */
.nag-skin .btn:focus-visible {
  outline: 0;
  box-shadow: 0 0 0 3px rgba(44, 82, 130, .35);
}

/* ---- Tombol mati tidak ikut bergerak ---- */
.nag-skin .btn:disabled,
.nag-skin .btn.disabled {
  transform: none;
  box-shadow: none;
  cursor: not-allowed;
  opacity: .65;
}

/* ---- Gerak ikon sesuai arti tombolnya ---- */

/* Kembali: panah mundur sedikit. */
.nag-skin .btn-dn-kembali:hover > i { transform: translateX(-3px); }

/* Tambah (Create, Add SJ, Add Data): tanda plus berputar seperempat. */
.nag-skin .btn-dn-create:hover > i,
.nag-skin #inv-btn-so:hover > i,
.nag-skin #so-btn-tambah:hover > i { transform: rotate(90deg); }

/* Cari: kaca pembesar membesar sedikit. */
.nag-skin .btn-dn-search:hover > i,
.nag-skin #so-btn-cari:hover > i { transform: scale(1.15); }

/* Unduh & cetak: ikonnya turun, seperti berkas yang tersimpan. */
.nag-skin .btn-dn-excel:hover > i,
.nag-skin .btn-dn-cetak:hover > i { transform: translateY(2px); }

/* Simpan: ikon disket sedikit membesar. */
.nag-skin .btn-dn-simpan:hover > i { transform: scale(1.12); }

/* Buang baris: silang berputar, memberi kesan membatalkan. */
.nag-skin .btn-dn-buang:hover > i { transform: rotate(90deg); }

/* Kosongkan: penghapus sedikit miring, seperti sedang menggosok. */
.nag-skin .btn-dn-kosongkan:hover:not(:disabled) > i { transform: rotate(-15deg); }

/* ---- Tombol di kolom Action: gerakannya dikecilkan lagi ---- */
/* Barisnya rapat, jadi angkatan 1px pun sudah cukup terasa. */
.nag-skin .dn-table-muat .dn-aksi .btn:hover:not(:disabled) {
  transform: translateY(-1px);
  box-shadow: 0 3px 7px -2px rgba(15, 23, 42, .3);
}
.nag-skin .dn-table-muat .dn-aksi .btn:active:not(:disabled) { transform: scale(.94); }

/* ---- Nomor invoice yang bisa diklik ---- */
.nag-skin .dn-no-link:hover { transform: translateY(-1px); }
.nag-skin .dn-no-link:active { transform: translateY(0); }

/* ---- Tombol yang sedang menunggu proses ---- */
/* Saat Save / Cancel ditekan, tombolnya dimatikan. Kedipan halus ini menandai
   bahwa yang berjalan bukan macet. */
.nag-skin .btn:disabled { animation: dn-btn-denyut 1.4s ease-in-out infinite; }
@keyframes dn-btn-denyut {
  0%, 100% { opacity: .65; }
  50%      { opacity: .45; }
}

/* ---- Hormati setelan "kurangi gerak" di sistem pengguna ---- */
@media (prefers-reduced-motion: reduce) {
  .nag-skin .btn,
  .nag-skin .dn-no-link,
  .nag-skin .btn > i,
  .nag-skin .btn > .fa,
  .nag-skin .btn > .fas {
    transition: none;
  }
  .nag-skin .btn:hover,
  .nag-skin .btn:active,
  .nag-skin .btn:hover > i,
  .nag-skin .dn-no-link:hover,
  .nag-skin .dn-table-muat .dn-aksi .btn:hover,
  .nag-skin .dn-table-muat .dn-aksi .btn:active { transform: none; }
  .nag-skin .btn:disabled { animation: none; }
}

/* ---- Tombol halaman tabel ---- */
.nag-skin .pagination .page-link {
  transition: transform .16s ease, box-shadow .16s ease, background-color .16s ease, color .16s ease;
}
.nag-skin .pagination .page-item:not(.disabled):not(.active) .page-link:hover {
  transform: translateY(-1px);
  box-shadow: 0 3px 7px -2px rgba(15, 23, 42, .25);
}
.nag-skin .pagination .page-item:not(.disabled) .page-link:active { transform: translateY(0) scale(.95); }

/* ---- Tombol di kotak dialog (SweetAlert) ----
   Swal menempelkan kotaknya langsung di <body>, jadi di luar .nag-skin.
   Yang disasar kelas .dn-swal - kelas yang cuma dipakai dialog modul ini. */
.dn-swal .swal2-confirm,
.dn-swal .swal2-cancel,
.dn-swal .swal2-deny {
  transition: transform .16s ease, box-shadow .16s ease, filter .16s ease;
}
.dn-swal .swal2-confirm:hover,
.dn-swal .swal2-cancel:hover,
.dn-swal .swal2-deny:hover {
  transform: translateY(-1px);
  box-shadow: 0 4px 10px -2px rgba(15, 23, 42, .3);
}
.dn-swal .swal2-confirm:active,
.dn-swal .swal2-cancel:active,
.dn-swal .swal2-deny:active {
  transform: translateY(0) scale(.97);
  box-shadow: 0 1px 2px rgba(15, 23, 42, .2);
  transition-duration: .06s;
}

@media (prefers-reduced-motion: reduce) {
  .nag-skin .pagination .page-link,
  .dn-swal .swal2-confirm,
  .dn-swal .swal2-cancel,
  .dn-swal .swal2-deny { transition: none; }
  .nag-skin .pagination .page-link:hover,
  .nag-skin .pagination .page-link:active,
  .dn-swal .swal2-confirm:hover,
  .dn-swal .swal2-cancel:hover,
  .dn-swal .swal2-deny:hover,
  .dn-swal .swal2-confirm:active,
  .dn-swal .swal2-cancel:active,
  .dn-swal .swal2-deny:active { transform: none; }
}

/* ==========================================================================
   Daftar Invoice Export - tampilan layar daftar.
   Dipisah dari aturan di atas karena hanya dipakai layar daftar export:
   kepala halaman, toolbar filter, dan tabelnya (.dn-tabel-daftar).
   ========================================================================== */

/* ---- Kepala halaman ----
   Judul + keterangan singkat di kiri, dua tombol utama di kanan. Di layar
   sempit tombolnya turun ke bawah judul, bukan memaksa halaman melebar. */
.nag-skin .dn-kepala-halaman {
  display: flex;
  align-items: center;
  justify-content: space-between;
  flex-wrap: wrap;
  gap: 12px 16px;
  margin-bottom: 16px;
}
.nag-skin .dn-kepala-kiri { display: flex; align-items: center; gap: 12px; min-width: 0; }
.nag-skin .dn-kepala-ikon {
  flex: 0 0 auto;
  width: 38px;
  height: 38px;
  border-radius: 11px;
  background: linear-gradient(135deg, #1e3a5f, #2c5282);
  color: #f8fafc;
  font-size: 15px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  box-shadow: 0 6px 14px -6px rgba(30, 58, 95, .65);
}
.nag-skin .dn-kepala-judul {
  margin: 0;
  font-size: 19px;
  font-weight: 700;
  letter-spacing: -.2px;
  color: #0f2942;
  line-height: 1.2;
}
.nag-skin .dn-kepala-sub {
  margin: 2px 0 0;
  font-size: 12px;
  color: #64748b;
  line-height: 1.35;
}
.nag-skin .dn-kepala-tombol { display: flex; gap: 8px; flex-wrap: wrap; }
/* Pemisah tipis antara Search dan tombol dokumen (Export, Create). */
.nag-skin .dn-filter-pisah {
  align-self: center;
  width: 1px;
  height: 20px;
  margin: 0 3px;
  background: #d7dee9;
}

/* ---- Tombol utama & netral ----
   Satu aksi berwarna (Create) dan sisanya netral: yang berwarna cuma yang
   paling sering dipakai, jadi arah matanya jelas. */
.nag-skin .btn-dn-utama {
  background: linear-gradient(135deg, #1e3a5f, #2c5282);
  border: 1px solid transparent;
  color: #fff;
  box-shadow: 0 1px 2px rgba(15, 23, 42, .12);
}
.nag-skin .btn-dn-utama:hover,
.nag-skin .btn-dn-utama:focus { filter: brightness(1.08); color: #fff; }
.nag-skin .btn-dn-netral {
  background: #fff;
  border: 1px solid #d7dee9;
  color: #334155;
}
.nag-skin .btn-dn-netral:hover,
.nag-skin .btn-dn-netral:focus { background: #f1f5f9; border-color: #c3cedd; color: #1e3a5f; }
.nag-skin .btn-dn-utama:focus-visible,
.nag-skin .btn-dn-netral:focus-visible {
  outline: 0;
  box-shadow: 0 0 0 3px rgba(44, 82, 130, .3);
}

/* ---- Toolbar filter ----
   Kartu tanpa kepala: isiannya sendiri sudah berlabel, kepala biru lagi di
   atasnya cuma menambah tinggi tanpa menambah keterangan. */
.nag-skin .dn-kartu-filter > .card-body { padding: 14px 18px; }
.nag-skin .dn-filter-petak {
  display: flex;
  flex-wrap: wrap;
  align-items: flex-end;
  gap: 12px 14px;
}
.nag-skin .dn-filter-petak .form-group { flex: 1 1 165px; min-width: 0; margin-bottom: 0; }
/* Lebar tiap isian mengikuti isinya: nama seller paling panjang, status
   sedang, tanggal cukup selebar dd/mm/yyyy - tidak ada kotak yang melar
   jauh melebihi isinya. */
.nag-skin .dn-filter-petak .form-group.dn-f-lebar  { flex: 1 1 300px; max-width: 420px; }
.nag-skin .dn-filter-petak .form-group.dn-f-sedang { flex: 1 1 180px; max-width: 240px; }
.nag-skin .dn-filter-petak .form-group.dn-f-tgl    { flex: 0 1 160px; }
/* Search menempel di ujung isian, bukan didorong ke tepi kartu - satu
   rangkaian kerja: pilih saringan lalu tekan Search, Export atau Create. */
.nag-skin .dn-filter-tombol { display: flex; flex-wrap: wrap; gap: 8px; }
/* Tiga tombol ini sederet, jadi gelapnya disamakan dengan Search (navy tua)
   dan yang dibedakan warnanya saja - hijau tua untuk Export, teal tua untuk
   Create. Hijau & biru terang bawaannya terasa dari set yang beda. */
.nag-skin .dn-filter-tombol .btn-dn-excel  { background: linear-gradient(135deg, #14532d, #166534); }
.nag-skin .dn-filter-tombol .btn-dn-create { background: linear-gradient(135deg, #0e7490, #0891b2); }

/* ---- Tabel daftar export ---- */
/* Kepala tabel: huruf kecil berspasi - kolomnya kebaca sebagai label, bukan
   sebagai kalimat. */
.nag-skin .dn-tabel-daftar thead th {
  font-size: 10.5px;
  font-weight: 600;
  letter-spacing: .2px;
  text-transform: none;
  color: #cbd8e8;
  padding-top: 10px;
  padding-bottom: 10px;
}
.nag-skin .dn-tabel-daftar th.dn-angka { text-align: right; }
.nag-skin .dn-tabel-daftar th.dn-tengah { text-align: center; }
/* Baris putih semua + garis tipis; belang abu bikin tabel terasa ramai
   sementara pil status & tombol ikon sudah punya warnanya sendiri. */
.nag-skin .dn-tabel-daftar tbody tr:nth-child(odd) td,
.nag-skin .dn-tabel-daftar tbody tr:nth-child(even) td { background: #fff; }
.nag-skin .dn-tabel-daftar tbody tr:hover td { background: #f6f9ff; }
.nag-skin .dn-tabel-daftar tbody td { border-bottom: 1px solid #edf1f7; }
.nag-skin .dn-tabel-daftar tbody td.dn-nilai { font-weight: 600; color: #0f2942; }
/* Nomor invoice: tautan tegas tanpa garis putus-putus. */
.nag-skin .dn-tabel-daftar .dn-no-link {
  border-bottom: 0;
  color: #1e3a5f;
  font-weight: 700;
  letter-spacing: .2px;
}
.nag-skin .dn-tabel-daftar .dn-no-link:hover { color: #1d4ed8; text-decoration: underline; }

/* Lebar kolom per halaman - jumlah kolomnya beda.
   Export (9): Inv Number, Invoice Date, Seller, Type, Doc Type, Doc Number,
   Value, Status, Action. */
.nag-skin.dn-daftar-export .dn-tabel-daftar th:nth-child(1) { width: 13%; }
.nag-skin.dn-daftar-export .dn-tabel-daftar th:nth-child(2) { width: 9%; }
.nag-skin.dn-daftar-export .dn-tabel-daftar th:nth-child(3) { width: 18%; }
.nag-skin.dn-daftar-export .dn-tabel-daftar th:nth-child(4) { width: 8%; }
.nag-skin.dn-daftar-export .dn-tabel-daftar th:nth-child(5) { width: 8%; }
.nag-skin.dn-daftar-export .dn-tabel-daftar th:nth-child(6) { width: 9%; }
.nag-skin.dn-daftar-export .dn-tabel-daftar th:nth-child(7) { width: 10%; }
.nag-skin.dn-daftar-export .dn-tabel-daftar th:nth-child(8) { width: 10%; }
.nag-skin.dn-daftar-export .dn-tabel-daftar th:nth-child(9) { width: 15%; }
/* Local (10): dua kolom pihak (Billed To & Shipped To bisa beda). */
.nag-skin.dn-daftar-local .dn-tabel-daftar th:nth-child(1)  { width: 12%; }
.nag-skin.dn-daftar-local .dn-tabel-daftar th:nth-child(2)  { width: 8.5%; }
.nag-skin.dn-daftar-local .dn-tabel-daftar th:nth-child(3)  { width: 13.5%; }
.nag-skin.dn-daftar-local .dn-tabel-daftar th:nth-child(4)  { width: 13%; }
.nag-skin.dn-daftar-local .dn-tabel-daftar th:nth-child(5)  { width: 8%; }
.nag-skin.dn-daftar-local .dn-tabel-daftar th:nth-child(6)  { width: 7%; }
.nag-skin.dn-daftar-local .dn-tabel-daftar th:nth-child(7)  { width: 8%; }
.nag-skin.dn-daftar-local .dn-tabel-daftar th:nth-child(8)  { width: 9%; }
.nag-skin.dn-daftar-local .dn-tabel-daftar th:nth-child(9)  { width: 8.5%; }
/* Action harus cukup untuk empat tombol ikon berjajar satu baris. */
.nag-skin.dn-daftar-local .dn-tabel-daftar th:nth-child(10) { width: 12.5%; }

/* ---- Pil status ----
   Selebar tulisannya saja, dengan titik warna di depan supaya statusnya
   kebaca sekilas tanpa jadi blok warna selebar kolom. */
.nag-skin .dn-pil {
  display: inline-flex;
  align-items: center;
  gap: 5px;
  height: 21px;
  padding: 0 9px;
  border: 1px solid transparent;
  border-radius: 999px;
  font-size: 10px;
  font-weight: 700;
  letter-spacing: .5px;
  line-height: 1;
  text-transform: uppercase;
  white-space: nowrap;
}
.nag-skin .dn-pil-titik {
  width: 5px;
  height: 5px;
  border-radius: 50%;
  background: currentColor;
  opacity: .7;
}
.nag-skin .dn-pil.is-draft  { background: #f1f5f9; border-color: #e2e8f0; color: #475569; }
.nag-skin .dn-pil.is-post   { background: #e0f2fe; border-color: #bae6fd; color: #075985; }
.nag-skin .dn-pil.is-first  { background: #fef3c7; border-color: #fde68a; color: #92400e; }
.nag-skin .dn-pil.is-second { background: #dcfce7; border-color: #bbf7d0; color: #166534; }
.nag-skin .dn-pil.is-batal  { background: #fee2e2; border-color: #fecaca; color: #991b1b; }
.nag-skin .dn-pil.is-lain   { background: #f1f5f9; border-color: #e2e8f0; color: #475569; }

/* ---- Tombol aksi per baris ----
   Ikon saja dalam satu baris, keterangannya lewat tooltip. Empat tombol penuh
   warna di tiap baris membuat kolom Action lebih ramai daripada datanya. */
/* Kolom Action dirapatkan sedikit: tombolnya sudah kecil, yang perlu lebar
   justru ruang untuk berjajar satu baris. */
.nag-skin .dn-tabel-daftar tbody td:last-child { padding-left: 6px; padding-right: 6px; }
.nag-skin .dn-tabel-daftar .dn-aksi {
  display: flex;
  flex-wrap: wrap;
  justify-content: center;
  gap: 4px;
}
.nag-skin .dn-tabel-daftar .dn-aksi .btn.dn-ikon {
  width: 26px;
  height: 26px;
  padding: 0;
  gap: 1px;
  border: 1px solid #e2e8f0;
  border-radius: 7px;
  background: #fff;
  font-size: 11px;
  color: #475569;
}
/* Tombol cetak punya panah kecil: dua versi (CM & FOB) dipilih dari dropdown. */
.nag-skin .dn-tabel-daftar .dn-aksi .btn.dn-ikon-pdf,
.nag-skin .dn-tabel-daftar .dn-aksi .btn.dn-ikon-excel { width: 32px; }
.nag-skin .dn-tabel-daftar .dn-aksi .dn-ikon-panah { font-size: 8px; opacity: .5; }
.nag-skin .dn-tabel-daftar .dn-aksi .btn.dn-ikon-pdf   { color: #b91c1c; border-color: #f0cccc; background: #fff7f7; }
.nag-skin .dn-tabel-daftar .dn-aksi .btn.dn-ikon-excel { color: #15803d; border-color: #c8e6d2; background: #f5fcf7; }
.nag-skin .dn-tabel-daftar .dn-aksi .btn.dn-ikon-ubah  { color: #1d4ed8; border-color: #cddcf8; background: #f7faff; }
.nag-skin .dn-tabel-daftar .dn-aksi .btn.dn-ikon:hover,
.nag-skin .dn-tabel-daftar .dn-aksi .btn.dn-ikon.show { color: #fff; }
.nag-skin .dn-tabel-daftar .dn-aksi .btn.dn-ikon-pdf:hover,
.nag-skin .dn-tabel-daftar .dn-aksi .btn.dn-ikon-pdf.show   { background: #b91c1c; border-color: #b91c1c; }
.nag-skin .dn-tabel-daftar .dn-aksi .btn.dn-ikon-excel:hover,
.nag-skin .dn-tabel-daftar .dn-aksi .btn.dn-ikon-excel.show { background: #15803d; border-color: #15803d; }
/* PDF Classic (tanpa CARING): abu, supaya jelas beda dari PDF CARING yang merah. */
.nag-skin .dn-tabel-daftar .dn-aksi .btn.dn-ikon-pdf.dn-ikon-pdf-lama { color: #475569; border-color: #cbd5e1; background: #f8fafc; }
.nag-skin .dn-tabel-daftar .dn-aksi .btn.dn-ikon-pdf.dn-ikon-pdf-lama:hover,
.nag-skin .dn-tabel-daftar .dn-aksi .btn.dn-ikon-pdf.dn-ikon-pdf-lama.show { color: #fff; background: #475569; border-color: #475569; }
.nag-skin .dn-tabel-daftar .dn-aksi .btn.dn-ikon-ubah:hover  { background: #1d4ed8; border-color: #1d4ed8; }
/* Cancel ikut berwarna merah seperti tombol cetak: semua tombol aksi punya
   warnanya sendiri, jadi satu baris tidak ada yang terlihat mati. */
.nag-skin .dn-tabel-daftar .dn-aksi .btn.dn-ikon-batal { color: #b91c1c; border-color: #f0cccc; background: #fff7f7; }
.nag-skin .dn-tabel-daftar .dn-aksi .btn.dn-ikon-batal:hover { background: #b91c1c; border-color: #b91c1c; }
.nag-skin .dn-tabel-daftar .dn-aksi .dn-diproses { width: 100%; text-align: center; }

/* ---- Kepala tabel ----
   Warnanya digradasi senada tombol Create & ikon judul, dengan garis tipis
   terang di bawahnya supaya kepala tabel terbaca sebagai satu pita, bukan
   balok biru datar. Sudut atas ikut membulat mengikuti kartunya. */
.nag-skin .dn-tabel-daftar thead th {
  background: linear-gradient(180deg, #2c5282 0%, #1e3a5f 100%);
  color: #e8eef7;
  border-bottom: 0;
  box-shadow: inset 0 -1px 0 rgba(148, 197, 255, .28);
}
.nag-skin .dn-tabel-daftar thead th:first-child { border-top-left-radius: 9px; }
.nag-skin .dn-tabel-daftar thead th:last-child  { border-top-right-radius: 9px; }
.nag-skin .dn-tabel-daftar thead th.sorting:hover { background: linear-gradient(180deg, #35618f 0%, #24466f 100%); }

/* Panah urut bawaan DataTables dua buah (naik & turun) berjejer di tiap kolom -
   ramai dan memakan lebar. Dijadikan satu tanda: redup kalau kolomnya belum
   dipakai mengurutkan, terang dan searah kalau sedang dipakai. */
.nag-skin .dn-tabel-daftar thead th { padding-right: 22px; }
.nag-skin .dn-tabel-daftar thead .sorting::before,
.nag-skin .dn-tabel-daftar thead .sorting_asc::before,
.nag-skin .dn-tabel-daftar thead .sorting_desc::before { display: none; }
.nag-skin .dn-tabel-daftar thead .sorting::after,
.nag-skin .dn-tabel-daftar thead .sorting_asc::after,
.nag-skin .dn-tabel-daftar thead .sorting_desc::after {
  right: 7px;
  bottom: auto;
  top: 50%;
  transform: translateY(-50%);
  font-size: 8.5px;
  line-height: 1;
}
.nag-skin .dn-tabel-daftar thead .sorting::after      { content: "\2195"; opacity: .4; }
.nag-skin .dn-tabel-daftar thead .sorting_asc::after  { content: "\2191"; opacity: 1; }
.nag-skin .dn-tabel-daftar thead .sorting_desc::after { content: "\2193"; opacity: 1; }
/* Kolom yang sedang dipakai mengurutkan dibuat lebih terang. */
.nag-skin .dn-tabel-daftar thead .sorting_asc,
.nag-skin .dn-tabel-daftar thead .sorting_desc { color: #fff; }

/* ---- Aksen warna halaman daftar ----
   Judulnya sekarang teks biasa, jadi warnanya dipindah ke permukaan: bagian
   atas halaman diberi wash biru tipis yang luruh jadi bening. Warnanya tetap
   satu keluarga dengan thead & tombol - bukan warna baru. */
.nag-skin {
  background: linear-gradient(180deg, rgba(44, 82, 130, .11) 0, rgba(44, 82, 130, .05) 170px, rgba(44, 82, 130, .035) 320px);
  /* Nada birunya menutup layar walau isinya sedikit - sisa ruang di bawah
     kartu tidak balik jadi putih polos. Angkanya disisakan supaya tidak
     memunculkan gulungan tegak tambahan. */
  min-height: calc(100vh - 170px);
}
/* Bayangan kartu ikut kebiruan supaya menyatu dengan wash-nya. */
.nag-skin .dn-kartu-filter,
.nag-skin .dn-kartu-tabel {
  border-color: #dde5f0;
  box-shadow: 0 1px 2px rgba(15, 23, 42, .04), 0 12px 26px -16px rgba(30, 58, 95, .45);
}
/* Kartu filter: pita navy di tepi kiri + latar bergradasi tipis - kotaknya
   punya warna tanpa harus memasang kepala biru lagi di atas isian. */
.nag-skin .dn-kartu-filter {
  background: linear-gradient(180deg, #f8fbff 0, #fff 70%);
  box-shadow: inset 3px 0 0 0 #2c5282, 0 1px 2px rgba(15, 23, 42, .04), 0 12px 26px -16px rgba(30, 58, 95, .45);
}
.nag-skin .dn-kartu-filter label { color: #46618a; }

/* Baris Show entries & Search dijadikan satu strip bernada biru: batas atas
   tabel jadi jelas, dan ruang putih besar di atas kepala tabel berkurang. */
.nag-skin .dn-kartu-tabel .dataTables_wrapper .row:first-child {
  background: #f4f8fd;
  border: 1px solid #e4ecf7;
  border-radius: 9px;
  padding: 9px 12px;
  margin-bottom: 14px;
}
.nag-skin .dn-kartu-tabel .dataTables_length label,
.nag-skin .dn-kartu-tabel .dataTables_filter label { color: #46618a; }
/* Jumlah baris & halaman di kaki tabel ikut bernada sama. */
.nag-skin .dn-kartu-tabel .dataTables_info { color: #46618a; }
.nag-skin .dn-kartu-tabel .dataTables_info b { color: #1e3a5f; }

/* ==========================================================================
   Modal rincian Export - dipadatkan & diberi nada.
   Isinya panjang (keterangan, 4 pihak, shipment, summary, rekap), jadi yang
   dikurangi jaraknya, bukan isinya: modal tetap digulir, tapi lebih pendek.
   Latarnya dibuat lebih biru supaya kartu putihnya punya batas yang jelas -
   sebelumnya putih di atas putih.
   ========================================================================== */
#modal-inv-detail .modal-body { background: #eef2f8; padding: 14px 16px; }

/* Judul bagian: pita navy kecil + tulisan - jadi penanda bagian, bukan sekadar
   baris teks di antara kartu. */
#modal-inv-detail .dn-det-judul {
  gap: 7px;
  font-size: 11px;
  letter-spacing: .6px;
  margin: 0 0 7px;
  padding-left: 9px;
  position: relative;
}
#modal-inv-detail .dn-det-judul::before {
  content: "";
  position: absolute;
  left: 0;
  top: 2px;
  bottom: 2px;
  width: 3px;
  border-radius: 2px;
  background: linear-gradient(180deg, #2c5282, #1e3a5f);
}
#modal-inv-detail .dn-det-judul i { font-size: 11px; opacity: .65; }

/* Kartu keterangan: jarak baris dirapatkan, label lebih kecil, latarnya sedikit
   bergradasi supaya tidak rata putih. */
#modal-inv-detail .dn-det-info {
  gap: 9px 18px;
  margin-bottom: 12px;
  padding: 12px 14px;
  border-color: #dde5f0;
  background: linear-gradient(180deg, #fff 0, #f8fbff 100%);
}
#modal-inv-detail .dn-det-info dt { font-size: 9.5px; letter-spacing: .6px; color: #74889f; margin-bottom: 1px; }
#modal-inv-detail .dn-det-info dd { font-size: 12.5px; color: #16293f; font-weight: 500; }
#modal-inv-detail .dn-det-info dd .dn-pil { margin-top: 1px; }

/* Empat pihak: lebih rapat, nama tetap menonjol, alamatnya yang dikecilkan. */
#modal-inv-detail .dn-det-pihak { gap: 10px; margin-bottom: 12px; }
#modal-inv-detail .dn-det-pihak-kotak {
  padding: 10px 12px;
  border-color: #dde5f0;
  font-size: 12px;
}
#modal-inv-detail .dn-det-pihak-label { font-size: 9.5px; letter-spacing: .6px; color: #74889f; margin-bottom: 3px; }
#modal-inv-detail .dn-det-pihak-kotak b { font-size: 12.5px; line-height: 1.3; }
#modal-inv-detail .dn-det-pihak-kotak p { margin-top: 3px; font-size: 11.5px; line-height: 1.38; }

/* Blok shipment & kartu ringkasan: padding dan jarak antar baris dikurangi. */
#modal-inv-detail .dn-det-kirim-kotak {
  padding: 10px 12px 12px;
  margin-bottom: 12px;
  border-color: #dde5f0;
}
#modal-inv-detail .dn-det-kirim-kepala { margin-bottom: 8px; }
#modal-inv-detail .dn-det-kirim-grid { gap: 8px 16px; }
#modal-inv-detail .dn-det-kirim-grid dt { font-size: 9.5px; letter-spacing: .6px; color: #74889f; margin-bottom: 1px; }
#modal-inv-detail .dn-det-kirim-grid dd { font-size: 12px; color: #16293f; }
#modal-inv-detail .dn-det-kartu {
  padding: 12px;
  margin-bottom: 12px;
  border-color: #dde5f0;
}
#modal-inv-detail .dn-det-kartu .dn-sj-pita { margin-bottom: 10px; }
#modal-inv-detail .dn-det-rekap { padding: 12px 14px; margin-bottom: 12px; border-color: #dde5f0; }

/* Rekap CM/FOB: barisnya dirapatkan supaya blok totalnya tidak setinggi
   setengah layar - isinya cuma delapan baris angka. */
#modal-inv-detail .dn-rekap thead th { padding: 0 12px 7px; font-size: 10.5px; }
#modal-inv-detail .dn-rekap tbody th,
#modal-inv-detail .dn-rekap tbody td { padding: 6px 12px; font-size: 12px; }
#modal-inv-detail .dn-rekap tfoot th,
#modal-inv-detail .dn-rekap tfoot td { padding: 8px 12px; }
#modal-inv-detail .dn-rekap tfoot td { font-size: 13px; }

/* Tabel di dalam modal ikut dirapatkan. */
/* Di dalam modal kepala tabelnya dibuat abu: kepala navy dipakai di daftar,
   kalau dipakai lagi di sini jadi dua blok gelap bertumpuk dengan kepala modal. */
#modal-inv-detail .dn-table thead th {
  padding: 7px 8px;
  font-size: 10.5px;
  letter-spacing: .2px;
  text-transform: none;
  background: #eef2f7;
  color: #4a5f7a;
  border-bottom: 1px solid #dbe4f0;
}
#modal-inv-detail #det-summary th,
#modal-inv-detail #det-summary td { padding: 5px 8px; font-size: 11px; }
#modal-inv-detail #det-summary thead th,
#modal-inv-detail #det-tabel thead th { font-size: 10.5px; }
#modal-inv-detail .dn-table tbody tr:not(.child) > td:not(.dataTables_empty) { height: 30px; }

/* ---- Pita ringkasan modal ----
   Duduk di antara kepala biru dan badan yang digulir, jadi selalu terlihat.
   Latarnya senada kepala modal supaya terbaca sebagai satu kesatuan. */
#modal-inv-detail .dn-det-pita-ringkas {
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  gap: 8px 26px;
  padding: 10px 18px;
  background: linear-gradient(180deg, #f4f8fd 0, #eaf0f8 100%);
  border-bottom: 1px solid #dbe4f0;
}
#modal-inv-detail .dn-pita-butir { display: flex; flex-direction: column; gap: 2px; min-width: 0; }
#modal-inv-detail .dn-pita-label {
  font-size: 9px;
  font-weight: 600;
  letter-spacing: .7px;
  text-transform: none;
  color: #74889f;
}
#modal-inv-detail .dn-pita-butir b {
  font-size: 13px;
  color: #1e3a5f;
  line-height: 1.2;
  font-variant-numeric: tabular-nums;
}
/* Dua grand total didorong ke kanan & dibuat lebih besar - itu angka yang dicari. */
#modal-inv-detail .dn-pita-total:nth-of-type(4) { margin-left: auto; }
#modal-inv-detail .dn-pita-total b { font-size: 15px; font-weight: 700; }
#modal-inv-detail .dn-pita-total .dn-pita-label { color: #5b7a9e; }

/* Tanpa ini tinggi tab yang diukur beda 12px dengan yang tergambar - margin
   bawah kartu terakhir tidak ikut terhitung, jadi modal tetap bergeser
   sedikit waktu tabnya diganti. */
#modal-inv-detail .tab-pane > :last-child,
#modal-inv-detail .tab-pane > :last-child > :last-child,
#modal-inv-detail .tab-pane > :last-child > :last-child > :last-child { margin-bottom: 0; }

/* ---- Tab isi modal ---- */
#modal-inv-detail .dn-det-tab {
  display: flex;
  gap: 4px;
  border-bottom: 1px solid #dbe4f0;
  margin: -2px 0 12px;
  padding: 0;
  list-style: none;
}
#modal-inv-detail .dn-det-tab .nav-link {
  display: flex;
  align-items: center;
  gap: 6px;
  border: 0;
  border-bottom: 2px solid transparent;
  border-radius: 8px 8px 0 0;
  padding: 7px 14px;
  background: none;
  font-size: 11.5px;
  font-weight: 600;
  letter-spacing: .3px;
  text-transform: none;
  color: #74889f;
}
#modal-inv-detail .dn-det-tab .nav-link i { font-size: 11px; opacity: .8; }
#modal-inv-detail .dn-det-tab .nav-link:hover { color: #1e3a5f; background: #e9f0f9; }
#modal-inv-detail .dn-det-tab .nav-link.active {
  color: #1e3a5f;
  background: #fff;
  border-bottom-color: #2c5282;
}
/* Jumlah shipment menempel di label tabnya. */
#modal-inv-detail .dn-tab-angka {
  min-width: 17px;
  padding: 1px 5px;
  border-radius: 999px;
  background: #dbe4f0;
  color: #46618a;
  font-size: 9.5px;
  text-align: center;
}
#modal-inv-detail .dn-det-tab .nav-link.active .dn-tab-angka { background: #1e3a5f; color: #fff; }

/* ---- Parties sebagai alur ----
   Empat kotak berderet dengan panah di antaranya: Shipper mengirim, Seller
   menagih ke Purchaser, barangnya sampai ke Receiver. */
#modal-inv-detail .dn-det-pihak {
  display: flex;
  flex-wrap: wrap;
  align-items: stretch;
  gap: 6px;
}
#modal-inv-detail .dn-det-pihak .dn-det-pihak-kotak { flex: 1 1 0; min-width: 0; }
#modal-inv-detail .dn-pihak-panah {
  flex: 0 0 auto;
  display: flex;
  align-items: center;
  color: #b3c2d6;
  font-size: 11px;
}
@media (max-width: 991px) {
  #modal-inv-detail .dn-det-pihak .dn-det-pihak-kotak { flex: 1 1 45%; }
  #modal-inv-detail .dn-pihak-panah { display: none; }
}
/* Isian kosong dibuat redup supaya mata langsung ke yang terisi. */
#modal-inv-detail .dn-det-kosong-nilai { color: #b3c2d6; }

/* ==========================================================================
   Layar Create/Edit Invoice Export (.dn-form-invoice).
   Di sini judul seksinya ada lima bertumpuk ke bawah; kalau semuanya pita navy
   penuh, yang paling menarik mata justru bingkainya, bukan isian yang harus
   diisi. Jadi: navy tetap dipakai penuh di daftar & modal (menahan tabel dan
   dialog), sedangkan judul seksi di form dibuat ringan - rail navy + garis
   tipis.
   ========================================================================== */
.nag-skin.dn-form-invoice .card > .card-header {
  position: relative;
  background: linear-gradient(180deg, #fff 0, #f7fafd 100%);
  background-image: linear-gradient(180deg, #fff 0, #f7fafd 100%);
  border-bottom: 1px solid #e4ebf4;
  border-radius: 11px 11px 0 0;
  padding: 10px 16px 10px 17px;
}
.nag-skin.dn-form-invoice .card > .card-header::before {
  content: "";
  position: absolute;
  left: 0;
  top: 9px;
  bottom: 9px;
  width: 3px;
  border-radius: 0 2px 2px 0;
  background: linear-gradient(180deg, #2c5282, #1e3a5f);
}
.nag-skin.dn-form-invoice .card > .card-header .card-title {
  color: #1e3a5f;
  font-size: 11.5px;
  letter-spacing: .6px;
}
.nag-skin.dn-form-invoice .card > .card-header .card-title i { color: #2c5282; opacity: .85; }
/* Tombol di kepala kartu tidak lagi berdiri di atas navy - Add dibuat solid
   navy, Clear All jadi netral bergaris. */
.nag-skin.dn-form-invoice .input-group .btn-dn-create,
.nag-skin.dn-form-invoice .card > .card-header .btn-dn-create {
  background: linear-gradient(135deg, #1e3a5f, #2c5282);
  color: #fff;
}
/* Clear All membuang isi tabel - warnanya merah lembut, jadi jelas ini aksi
   yang menghapus, bukan tombol netral. */
.nag-skin.dn-form-invoice .card > .card-header .btn-dn-kosongkan {
  background: #fef2f2;
  border: 1px solid #f7c9c9;
  color: #b91c1c;
}
.nag-skin.dn-form-invoice .card > .card-header .btn-dn-kosongkan:hover:not(:disabled) {
  background: #b91c1c;
  border-color: #b91c1c;
  color: #fff;
}
.nag-skin.dn-form-invoice .card > .card-header .btn-dn-kosongkan:disabled { opacity: .55; }
/* Modal di layar ini (Add SJ, Shipment) tetap memakai kepala navy-nya sendiri. */
.nag-skin.dn-form-invoice .modal .card > .card-header { padding-left: 17px; }

/* ---- Tanda wajib isi ----
   Dipasang di label yang ditolak waktu Save: user tahu sebelum menekan Save,
   bukan setelah dapat pesan merah. */
/* Nama pilihan yang kepanjangan (mis. nama profit center) dipotong dengan
   elipsis - kalau meluber, kotaknya jadi bisa digeser ke samping. */
.nag-skin .select2-container .select2-selection--single .select2-selection__rendered {
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.nag-skin .dn-wajib {
  color: #dc2626;
  font-weight: 700;
  margin-left: 2px;
}

/* ---- Baris kosong yang bisa ditindak ----
   Tombolnya ditaruh di tengah tabel kosong, jadi tidak perlu mencari tombol
   di pojok kanan atas kartu. */
.nag-skin .dn-kosong-isi {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 7px;
  padding: 6px 0 2px;
  font-style: normal;
}
.nag-skin .dn-kosong-isi > i { font-size: 17px; color: #c3cedd; }
.nag-skin .dn-kosong-isi > span { color: #94a3b8; }

/* ---- Tombol di kaki modal ----
   Warna & pembagiannya tetap (merah untuk keluar/PDF, hijau untuk Excel &
   simpan), cuma nadanya diturunkan supaya sekeluarga dengan tombol di kaki
   kartu Summary - tidak ada yang menyala lebih terang dari yang lain. */
/* Close/Cancel bukan aksi cetak - warnanya dibedakan dari tombol PDF supaya
   tidak terlihat sepasang: abu slate, tetap gelap seperti tetangganya. */
.nag-skin .modal-footer .btn-dn-kembali,
#modal-inv-detail .modal-footer .btn-dn-kembali {
  background: linear-gradient(135deg, #475569, #334155);
  border: none;
  color: #fff;
}
.nag-skin .modal-footer .btn-dn-simpan {
  background: linear-gradient(135deg, #1e3a5f, #2c5282);
  border: none;
  color: #fff;
}
#modal-inv-detail .modal-footer .btn-dn-excel {
  background: linear-gradient(135deg, #14532d, #166534);
  border: none;
  color: #fff;
}
#modal-inv-detail .modal-footer .btn-dn-cetak {
  background: linear-gradient(135deg, #7f1d1d, #991b1b);
  border-color: transparent;
  color: #fff;
}
/* PDF Classic: bergaris saja, supaya tidak tertukar dengan PDF CARING (merah)
   maupun Close (abu penuh). */
#modal-inv-detail .modal-footer .btn-dn-cetak-lama {
  background: #fff;
  border: 1px solid #94a3b8;
  color: #334155;
}
#modal-inv-detail .modal-footer .btn-dn-cetak-lama:hover,
#modal-inv-detail .modal-footer .btn-dn-cetak-lama.show { background: #f1f5f9; border-color: #64748b; color: #1e293b; }
#modal-inv-detail .modal-footer .btn-dn-cetak:hover,
#modal-inv-detail .modal-footer .btn-dn-cetak.show,
.nag-skin .modal-footer .btn-dn-kembali:hover,
.nag-skin .modal-footer .btn-dn-simpan:hover,
#modal-inv-detail .modal-footer .btn-dn-excel:hover,
#modal-inv-detail .modal-footer .btn-dn-excel.show { filter: brightness(1.12); color: #fff; }

/* ---- Tombol Save & Back di kaki kartu Summary ----
   Save navy - warna yang sama dengan tombol Search di daftar, jadi 'aksi utama'
   di modul ini selalu satu warna. Back merah tua. Keduanya sengaja tidak
   terang: di halaman yang nadanya lembut, warna terang terasa menusuk. */
.nag-skin.dn-form-invoice .dn-kaki .btn-dn-simpan {
  background: linear-gradient(135deg, #1e3a5f, #2c5282);
  border: none;
  color: #fff;
}
.nag-skin.dn-form-invoice .dn-kaki .btn-dn-kembali {
  background: linear-gradient(135deg, #7f1d1d, #991b1b);
  border: none;
  color: #fff;
}
.nag-skin.dn-form-invoice .dn-kaki .btn-dn-simpan:hover,
.nag-skin.dn-form-invoice .dn-kaki .btn-dn-kembali:hover { filter: brightness(1.12); color: #fff; }

/* ---- Kepala tabel di form ----
   Ukurannya disamakan dengan daftar & modal (9.5px). Dengan huruf sekecil ini
   judul sepanjang 'Final Destination' muat satu baris, jadi tinggi baris
   kepalanya rata - sebelumnya ada yang turun ke baris kedua. */
.nag-skin.dn-form-invoice .dn-table thead th {
  font-size: 10.5px;
  letter-spacing: .2px;
  text-transform: none;
  white-space: nowrap;
  padding: 8px 8px;
}
/* ---- Detail SJ di modal Local ----
   15 kolom dipas jadi 100% lebar modal: isi yang panjang melipat, jadi tidak
   ada yang perlu digeser ke samping. */
.dn-daftar-local #det-tabel { table-layout: fixed; }
.dn-daftar-local #det-tabel thead th { white-space: nowrap; vertical-align: bottom; }
.dn-daftar-local #det-tabel tbody td { white-space: normal; overflow-wrap: anywhere; vertical-align: top; }
.dn-daftar-local #det-tabel th:nth-child(1) { width: 9.5%; }
.dn-daftar-local #det-tabel th:nth-child(2) { width: 12%; }
.dn-daftar-local #det-tabel th:nth-child(3) { width: 7.5%; }
.dn-daftar-local #det-tabel th:nth-child(4) { width: 8%; }
.dn-daftar-local #det-tabel th:nth-child(5) { width: 9.5%; }
.dn-daftar-local #det-tabel th:nth-child(6) { width: 8.5%; }
.dn-daftar-local #det-tabel th:nth-child(7) { width: 8.5%; }
.dn-daftar-local #det-tabel th:nth-child(8) { width: 4%; }
.dn-daftar-local #det-tabel th:nth-child(9) { width: 4%; }
.dn-daftar-local #det-tabel th:nth-child(10) { width: 5.5%; }
.dn-daftar-local #det-tabel th:nth-child(11) { width: 7.5%; }
.dn-daftar-local #det-tabel th:nth-child(12) { width: 7%; }
.dn-daftar-local #det-tabel th:nth-child(13) { width: 8.5%; }
.dn-daftar-local #det-tabel th:nth-child(10),
.dn-daftar-local #det-tabel th:nth-child(11),
.dn-daftar-local #det-tabel th:nth-child(12),
.dn-daftar-local #det-tabel th:nth-child(13) { text-align: right; }
/* Baris Bootstrap di dalam modal bermargin negatif - isinya jadi 8px lebih lebar
   dari badan modal dan memunculkan geseran ke samping. Dinolkan di sini. */
#modal-inv-detail #det-isi .row { margin-left: 0; margin-right: 0; }
#modal-inv-detail #det-isi .row > [class^="col-"] { padding-left: 0; padding-right: 0; }
/* Wadahnya cuma untuk gulir tegak - ke samping tidak perlu lagi. */
.dn-daftar-local .dn-table-scroll { overflow-x: hidden; }

/* Rekap Local di modal dilipat: kepala strip + Grand Total selalu terlihat,
   baris rinciannya menyusul di bawahnya. */
#modal-inv-detail .dn-det-rekap-lipat {
  background: #fff;
  border: 1px solid #dde5f0;
  border-radius: 12px;
  overflow: hidden;
}
#modal-inv-detail .dn-det-rekap-lipat .dn-lipat-strip { padding: 8px 12px; border-bottom: 1px solid #edf1f7; }
#modal-inv-detail .dn-det-rekap-lipat .dn-det-ringkas { border: 0; border-radius: 0; }
#modal-inv-detail .dn-det-ringkas-grand > div { border-bottom: 0; }

/* Rekap Local di modal: barisnya dirapatkan supaya sepadan dengan rekap CM/FOB
   di modal Export. */
#modal-inv-detail .dn-det-ringkas > div { padding: 6px 12px; font-size: 12px; }
#modal-inv-detail .dn-det-ringkas > div.dn-det-grand { padding: 8px 12px; }
#modal-inv-detail .dn-det-ringkas > div.dn-det-grand b { font-size: 13px; }

/* ---- Kartu Summary: bentuknya disamakan dengan rekap di modal rincian ---- */
.nag-skin.dn-form-invoice .dn-rekap thead th { padding: 0 12px 7px; font-size: 10.5px; }
.nag-skin.dn-form-invoice .dn-rekap tbody th,
.nag-skin.dn-form-invoice .dn-rekap tbody td { padding: 6px 12px; font-size: 12px; }
.nag-skin.dn-form-invoice .dn-rekap tfoot th,
.nag-skin.dn-form-invoice .dn-rekap tfoot td { padding: 8px 12px; }
.nag-skin.dn-form-invoice .dn-rekap tfoot td { font-size: 13px; }

/* ---- Unit Price yang diketik sendiri (SJ tanpa SO) ----
   Kotaknya seukuran kolom Unit Price, dengan tombol kecil di kanannya untuk
   menyalin harga ke seluruh baris SJ yang sama. Yang masih kosong diberi
   bingkai kuning - itu satu-satunya isian yang menahan Save. */
.nag-skin .dn-harga-sel {
  display: flex;
  gap: 2px;
  align-items: center;
  justify-content: flex-end;
}
.nag-skin .dn-table .form-control.dn-harga {
  height: 26px;
  width: 72px;
  padding: 0 5px;
  text-align: right;
  font-size: 12px;
  font-variant-numeric: tabular-nums;
  border-radius: 6px;
}
.nag-skin .dn-table .form-control.dn-harga-kurang {
  border-color: #f59e0b;
  background: #fffbeb;
}
.nag-skin .dn-table .form-control.dn-harga-kurang::placeholder { color: #b45309; }
.nag-skin .dn-harga-sebar,
.nag-skin .dn-curr-sebar {
  height: 26px;
  width: 22px;
  padding: 0;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  border: 1px solid #cbd5e1;
  border-radius: 7px;
  background: #f1f5f9;
  color: #334155;
  font-size: 11px;
}
.nag-skin .dn-harga-sebar:hover,
.nag-skin .dn-curr-sebar:hover { background: #e2e8f0; color: #0f172a; }
/* Mata uang baris SJ tanpa SO. Panahnya digambar sendiri - gaya .form-control
   menyembunyikan panah bawaan, padahal tanpa itu kotaknya tidak terlihat
   sebagai pilihan. */
.nag-skin .dn-table select.form-control.dn-curr {
  height: 26px;
  width: 50px;
  padding: 0 14px 0 4px !important;
  font-size: 12px;
  font-weight: 600;
  color: #1e3a5f;
  border-radius: 6px;
  cursor: pointer;
  -webkit-appearance: none;
  appearance: none;
  background: #fff url("data:image/svg+xml,%3Csvg xmlns=%27http://www.w3.org/2000/svg%27 viewBox=%270 0 16 16%27%3E%3Cpath fill=%27none%27 stroke=%27%231e3a5f%27 stroke-width=%272%27 stroke-linecap=%27round%27 stroke-linejoin=%27round%27 d=%27M4 6l4 4 4-4%27/%3E%3C/svg%3E") no-repeat right 4px center / 9px 9px;
}
.nag-skin .dn-table select.form-control.dn-curr.dn-harga-kurang {
  border-color: #f59e0b;
  background-color: #fffbeb;
}
/* Layar 1440px ke bawah: kotak harga menambah lebar tabel Detail SJ yang
   kolomnya sudah 17. Jarak dalam selnya dirapatkan supaya tabelnya tetap muat
   utuh - bukan tabelnya yang digeser ke samping. */
@media (max-width: 1440px) {
  .nag-skin #inv-table-sj thead th,
  .nag-skin #inv-table-sj tbody td { padding-left: 4px; padding-right: 4px; }
}

@media (max-width: 767px) {
  .nag-skin .dn-kepala-tombol { width: 100%; }
  .nag-skin .dn-kepala-tombol .btn { flex: 1 1 0; }
  .nag-skin .dn-filter-tombol { width: 100%; }
  .nag-skin .dn-filter-tombol .btn { flex: 1 1 0; }
}

/* Kolom nilai TAGIH di daftar Add SJ. Knitting punya dua angka - kirim &
   tagih - dan keduanya perlu terbaca berdampingan, seperti layar knitting AR
   lama. Garment tidak punya nilai tagih, jadi kolomnya disembunyikan supaya
   tabelnya tidak melebar tanpa guna.

   Selektornya disamakan dengan modal Add WS - kelasnya memang belum dipakai
   di sana, tapi kalau nanti WS ikut punya dua nilai, gayanya sudah sama. */
:is(#modal-add-so,#modal-add-ws) .dn-kol-tagih { display: none; }
:is(#modal-add-so,#modal-add-ws).is-knit .dn-kol-tagih { display: table-cell; }
/* Dibedakan tipis dari kolom kirim di sebelahnya - sekilas kelihatan mana
   yang mana, tanpa harus membaca judul kolomnya lagi. */
:is(#modal-add-so,#modal-add-ws).is-knit thead .dn-kol-tagih { color: #0f766e; }
:is(#modal-add-so,#modal-add-ws).is-knit tbody .dn-kol-tagih { background: #f0fdfa; }

/* Kolom nilai TAGIH di tabel Detail SJ halaman utama. Aturannya sama dengan
   di modal: disembunyikan sampai ada baris yang memang punya nilai tagih,
   penandanya kelas is-knit di tabelnya sendiri. */
:is(#inv-table-sj,#inv-rekap-tabel) .dn-sel-tagih { display: none; }
:is(#inv-table-sj,#inv-rekap-tabel).is-knit .dn-sel-tagih { display: table-cell; }
:is(#inv-table-sj,#inv-rekap-tabel).is-knit thead .dn-sel-tagih { color: #0f766e; }
:is(#inv-table-sj,#inv-rekap-tabel).is-knit tbody .dn-sel-tagih { background: #f0fdfa; }
/* Rekap Export: Grand Total ada di tfoot, jadi ikut diurus terpisah. */
#inv-rekap-tabel.is-knit tfoot .dn-sel-tagih { display: table-cell; }

/* Rekap Invoice Local waktu knitting: SATU kartu dengan dua kolom angka -
   Shipment & Billing - sejajar baris demi baris. Dibuat begini, bukan dua
   kartu bersebelahan: kartu terpisah tingginya ikut isinya masing-masing,
   jadi barisnya tidak akan pernah lurus. */
.dn-ringkas .dn-nilai-tagih,
.dn-ringkas .dn-judul-nilai { display: none; }
.dn-ringkas.is-dua-nilai .dn-nilai-tagih { display: block; }
.dn-ringkas.is-dua-nilai .dn-judul-nilai { display: flex; }
/* Label & kedua angkanya dibagi tiga sama rata. Label tidak perlu selebar
   waktu cuma satu angka - ruangnya lebih berguna untuk angkanya. */
.dn-ringkas.is-dua-nilai .col-sm-5,
.dn-ringkas.is-dua-nilai .dn-nilai-kirim,
.dn-ringkas.is-dua-nilai .dn-nilai-tagih {
  flex: 0 0 33.3333%;
  max-width: 33.3333%;
}
.dn-ringkas .dn-judul-nilai > div {
  font-size: 10.5px;
  font-weight: 700;
  letter-spacing: .04em;
  text-transform: uppercase;
  color: #64748b;
  text-align: right;
  padding-bottom: 2px;
}
.dn-ringkas .dn-judul-nilai .dn-nilai-tagih { color: #0f766e; }
/* Angka Billing diberi latar tipis - sama penandanya dengan kolom tagih di
   tabel, jadi satu arti satu warna di seluruh layar. */
.dn-ringkas.is-dua-nilai .dn-nilai-tagih .form-control { background: #f0fdfa; }
/* Kartunya ikut melebar sedikit - dua kolom angka tidak muat di lebar untuk
   satu, tapi jangan sampai separuh layar jadi ruang kosong. */
@media (min-width: 992px) {
  #inv-rekap-kolom.is-dua-nilai { flex: 0 0 50%; max-width: 50%; }
}

/* Ketentuan SJ yang bisa ditarik - mengisi ruang kosong di sebelah Invoice
   Summary di modal Add SJ, jadi aturannya terbaca tepat waktu user memilih. */
:is(#modal-add-so,#modal-add-ws) .dn-ketentuan .card-body { padding: 10px 14px 6px; }
:is(#modal-add-so,#modal-add-ws) .dn-ket-judul {
  font-size: 11px;
  font-weight: 700;
  letter-spacing: .04em;
  text-transform: uppercase;
  color: #1e3a5f;
  margin-bottom: 4px;
}
:is(#modal-add-so,#modal-add-ws) .dn-ket-judul-2 { margin-top: 10px; }
:is(#modal-add-so,#modal-add-ws) ul.dn-ket {
  margin: 0;
  padding-left: 16px;
  font-size: 11.5px;
  line-height: 1.55;
  color: #475569;
}
:is(#modal-add-so,#modal-add-ws) ul.dn-ket li { margin-bottom: 2px; }
:is(#modal-add-so,#modal-add-ws) ul.dn-ket b { color: #1e3a5f; font-weight: 600; }
/* Layar sempit: kedua kolomnya menumpuk, jangan sampai berdempetan. */
@media (max-width: 767.98px) {
  :is(#modal-add-so,#modal-add-ws) .dn-ket-kolom + .dn-ket-kolom { margin-top: 10px; }
}
</style>
