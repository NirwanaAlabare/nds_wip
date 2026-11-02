{{-- CSS bersama daftar Master Data (customer, supplier, item): tombol aksi, baris tidak aktif,
     teks di sel tabel, dan modal Show bertab. Disisipkan di dalam <style> halaman daftar. --}}
/* ---- Tombol aksi per baris (ukuran & bentuk dari .dn-ikon skin Invoice) ---- */
.nag-skin .dn-tabel-daftar .dn-aksi .btn.md-ikon-lihat    { color: #1e3a5f; border-color: #d7dee9; background: #f8fafc; }
.nag-skin .dn-tabel-daftar .dn-aksi .btn.md-ikon-lihat:hover { background: #1e3a5f; border-color: #1e3a5f; }
/* Toggle mengikuti status sekarang: hijau = aktif, abu = tidak aktif. */
.nag-skin .dn-tabel-daftar .dn-aksi .btn.md-ikon-aktif    { color: #15803d; border-color: #c8e6d2; background: #f5fcf7; }
.nag-skin .dn-tabel-daftar .dn-aksi .btn.md-ikon-aktif:hover { background: #b91c1c; border-color: #b91c1c; }
.nag-skin .dn-tabel-daftar .dn-aksi .btn.md-ikon-nonaktif { color: #94a3b8; border-color: #e2e8f0; background: #f8fafc; }
.nag-skin .dn-tabel-daftar .dn-aksi .btn.md-ikon-nonaktif:hover { background: #15803d; border-color: #15803d; }
/* Supplier Blocked: gelap, warna yang sama dengan pil Blocked */
.nag-skin .dn-tabel-daftar .dn-aksi .btn.md-ikon-blokir   { color: #1e293b; border-color: #cbd5e1; background: #f1f5f9; }
.nag-skin .dn-tabel-daftar .dn-aksi .btn.md-ikon-blokir:hover { background: #1e3a5f; border-color: #1e3a5f; }
.nag-skin .dn-tabel-daftar .dn-aksi .btn.dn-ikon:disabled { opacity: .4; cursor: not-allowed; pointer-events: auto; }
.nag-skin .dn-tabel-daftar .dn-aksi .btn.dn-ikon-ubah:disabled:hover { color: #1d4ed8; background: #f7faff; border-color: #cddcf8; }
/* Baris tidak aktif dibuat redup supaya langsung kelihatan. */
.nag-skin .dn-tabel-daftar tbody tr.md-nonaktif td:not(:last-child) { color: #94a3b8; }
.nag-skin .dn-tabel-daftar tbody tr.md-nonaktif .md-nama,
.nag-skin .dn-tabel-daftar tbody tr.md-nonaktif .md-kode { color: #94a3b8; }

/* ---- Modal Show: kepala bergradasi + tab, isi per tab memakai kartu rincian Invoice ---- */
#md-modal-detail .modal-dialog { max-width: 1000px; }
/* Tinggi modal ikut isinya (bawaan scrollable selalu setinggi layar), sama dengan modal rincian Export */
#md-modal-detail .modal-dialog-scrollable { height: auto; max-height: calc(100% - 3.5rem); }
#md-modal-detail .modal-dialog-scrollable .modal-content { max-height: 100%; }
#md-modal-detail .modal-content { border: 0; border-radius: 14px; overflow: hidden; }
#md-modal-detail .md-det-kepala {
  align-items: center;
  gap: 14px;
  padding: 16px 20px;
  border-bottom: 0;
  background: linear-gradient(120deg, #1e3a5f 0%, #24507f 60%, #2c5f94 100%);
}
#md-modal-detail .md-det-avatar {
  flex: 0 0 auto;
  width: 50px;
  height: 50px;
  border-radius: 14px;
  display: flex;
  align-items: center;
  justify-content: center;
  background: rgba(255, 255, 255, .14);
  border: 1px solid rgba(255, 255, 255, .22);
  color: #fff;
  font-size: 17px;
  font-weight: 700;
  letter-spacing: .5px;
}
#md-modal-detail .md-det-identitas { flex: 1 1 auto; min-width: 0; }
#md-modal-detail .md-det-nama {
  margin: 0 0 6px;
  color: #fff;
  font-size: 17px;
  font-weight: 700;
  letter-spacing: .2px;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}
#md-modal-detail .md-det-meta { display: flex; flex-wrap: wrap; align-items: center; gap: 6px; }
#md-modal-detail .md-det-chip {
  display: inline-flex;
  align-items: center;
  gap: 5px;
  padding: 2px 9px;
  border-radius: 999px;
  background: rgba(255, 255, 255, .13);
  color: #e2e8f0;
  font-size: 11.5px;
  font-weight: 500;
  white-space: nowrap;
}
#md-modal-detail .md-det-chip i { font-size: 10px; opacity: .7; }
#md-modal-detail .md-det-chip.is-kode { background: #fff; color: #1e3a5f; font-weight: 700; letter-spacing: .3px; }
#md-modal-detail .md-det-meta .dn-pil { margin-left: 2px; }
#md-modal-detail .btn-close { align-self: flex-start; filter: invert(1) grayscale(100%) brightness(200%); opacity: .8; }

/* Tab: garis bawah biru tua untuk tab yang terbuka */
#md-modal-detail .md-det-tab { background: #fff; border-bottom: 1px solid #e2e8f0; padding: 0 14px; }
#md-modal-detail .md-det-tab .nav { flex-wrap: nowrap; overflow-x: auto; gap: 2px; }
#md-modal-detail .md-det-tab .nav-link {
  display: flex;
  align-items: center;
  gap: 7px;
  padding: 12px 12px 10px;
  border: 0;
  border-bottom: 2px solid transparent;
  border-radius: 0;
  background: none;
  color: #64748b;
  font-size: 12.5px;
  font-weight: 600;
  white-space: nowrap;
}
#md-modal-detail .md-det-tab .nav-link i { font-size: 11.5px; opacity: .75; }
#md-modal-detail .md-det-tab .nav-link:hover { color: #1e3a5f; }
#md-modal-detail .md-det-tab .nav-link.active { color: #1e3a5f; border-bottom-color: #1e3a5f; }
#md-modal-detail .md-tab-cacah {
  min-width: 18px;
  padding: 1px 6px;
  border-radius: 9px;
  background: #e2e8f0;
  color: #475569;
  font-size: 10px;
  font-weight: 700;
  text-align: center;
}
#md-modal-detail .nav-link.active .md-tab-cacah { background: #1e3a5f; color: #fff; }

/* Tinggi isi dijaga supaya modal tidak melompat waktu pindah tab */
#md-modal-detail .modal-body { background: #f5f7fb; padding: 18px 20px; min-height: 380px; }
#md-modal-detail .modal-footer { border-top: 1px solid #e2e8f0; padding: 12px 18px; gap: 8px; }
#md-modal-detail .modal-footer .btn { margin: 0; }
#md-modal-detail .dn-det-info .md-penuh { grid-column: span 2; }
#md-modal-detail .md-det-kosong { color: #94a3b8; font-style: italic; }

/* Alamat & produk bisa sangat panjang - dua baris saja, selengkapnya lewat tooltip. */
.nag-skin .md-klem {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
/* Nama + nama singkat, dan kontak (nama, telepon, email) ditumpuk per baris. */
.nag-skin .md-nama { font-weight: 600; color: #0f2942; }
/* Satu baris per butir (nama kontak, telepon, email); yang kepanjangan dipotong, lengkapnya di tooltip. */
.nag-skin .md-sub {
  display: block;
  font-size: 11px;
  color: #64748b;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}
.nag-skin .md-sub i { width: 13px; color: #94a3b8; font-size: 10px; }
.nag-skin .md-kode { font-weight: 700; color: #1e3a5f; letter-spacing: .2px; }

@media (max-width: 575px) {
  #md-modal-detail .dn-det-info .md-penuh { grid-column: auto; }
}

/* ---- Layar sempit (HP): tanpa geser ke samping. Kolom yang tidak muat disembunyikan
   DataTables Responsive, dibuka-tutup lewat tombol + / - di kolom pertama. ---- */
.nag-skin .md-responsif { overflow-x: visible !important; }
.nag-skin .md-responsif .dn-table-muat { min-width: 0 !important; }
@media (max-width: 991px) {
  /* Lebar kolom bawaan skin (untuk daftar invoice) tidak dipakai - lebar diatur Responsive.
     Judul kolom tidak dipotong per huruf; kolom yang tidak muat pindah ke detail (+). */
  .nag-skin .md-responsif .dn-table-muat th { width: auto !important; white-space: normal; word-break: normal; overflow-wrap: normal; }
  /* Isi sel turun baris per kata, tidak dipotong di tengah (KNT1 / 0, PRODUCTIO / N).
     Kata yang lebih panjang dari kolomnya tetap boleh dipatahkan. */
  .nag-skin .md-responsif .dn-table-muat tbody td { word-break: normal; overflow-wrap: break-word; }
  /* Tombol aksi berjajar ke samping, tidak bertumpuk */
  .nag-skin .md-responsif .dn-table-muat .dn-aksi { display: flex; flex-wrap: nowrap; gap: 4px; justify-content: center; }
}
/* Tombol menu aksi (titik tiga) - hanya dipakai di HP, lihat mdTombolAksi() */
.nag-skin .md-aksi-hp { display: none; }
.nag-skin .md-ikon-menu {
  width: 28px;
  height: 28px;
  padding: 0;
  border: 1px solid #d7dee9;
  border-radius: 7px;
  background: #f8fafc;
  color: #1e3a5f;
  font-size: 12px;
}
.nag-skin .md-ikon-menu:hover,
.nag-skin .md-ikon-menu[aria-expanded="true"] { background: #1e3a5f; border-color: #1e3a5f; color: #fff; }
.nag-skin .md-menu-aksi {
  min-width: 170px;
  padding: 5px;
  border: 1px solid #e2e8f0;
  border-radius: 10px;
  box-shadow: 0 10px 24px rgba(15, 23, 42, .14);
  font-size: 13px;
}
.nag-skin .md-menu-aksi .dropdown-item {
  display: flex;
  align-items: center;
  gap: 9px;
  padding: 8px 10px;
  border-radius: 7px;
  color: #1e293b;
}
.nag-skin .md-menu-aksi .dropdown-item i { width: 16px; text-align: center; color: #64748b; }
.nag-skin .md-menu-aksi .dropdown-item:hover,
.nag-skin .md-menu-aksi .dropdown-item:focus { background: #eef2f7; color: #1e3a5f; }

@media (max-width: 575px) {
  /* Tiga tombol aksi diganti satu tombol menu, judul kolomnya disembunyikan supaya kolomnya ramping */
  .nag-skin .md-responsif .dn-table-muat .dn-aksi.md-aksi-lebar { display: none; }
  .nag-skin .md-responsif .md-aksi-hp { display: inline-block; }
  .nag-skin .md-responsif .md-th-aksi span { display: none; }
  /* Sel lebih rapat supaya kolom aksi tetap muat di HP */
  .nag-skin .md-responsif .dn-table-muat thead th,
  .nag-skin .md-responsif .dn-table-muat tbody td { padding-left: 6px; padding-right: 6px; }
  /* Ikon panah sortir disembunyikan (sortir tetap dengan menekan judul kolom) supaya ruangnya untuk isi */
  .nag-skin .md-responsif table.dataTable thead > tr > th.sorting,
  .nag-skin .md-responsif table.dataTable thead > tr > th.sorting_asc,
  .nag-skin .md-responsif table.dataTable thead > tr > th.sorting_desc { padding-right: 6px; }
  .nag-skin .md-responsif table.dataTable thead > tr > th.sorting:before,
  .nag-skin .md-responsif table.dataTable thead > tr > th.sorting:after,
  .nag-skin .md-responsif table.dataTable thead > tr > th.sorting_asc:before,
  .nag-skin .md-responsif table.dataTable thead > tr > th.sorting_asc:after,
  .nag-skin .md-responsif table.dataTable thead > tr > th.sorting_desc:before,
  .nag-skin .md-responsif table.dataTable thead > tr > th.sorting_desc:after { display: none; }
  .nag-skin .md-responsif .dn-tabel-daftar .dn-aksi .btn.dn-ikon { width: 25px; height: 25px; }
  .nag-skin table.dataTable.dtr-inline.collapsed > tbody > tr > td.dtr-control { padding-left: 31px; }
  .nag-skin table.dataTable.dtr-inline.collapsed > tbody > tr > td.dtr-control:before { left: 6px; }
  .nag-skin .md-responsif .dn-table-muat .dn-aksi { gap: 3px; }
  /* Halaman tabel: tombol lebih kecil, rata tengah, boleh turun baris kalau nomornya banyak */
  .nag-skin .dataTables_wrapper .dataTables_info { text-align: center; }
  .nag-skin .dataTables_wrapper .dataTables_paginate .pagination { flex-wrap: wrap; justify-content: center; row-gap: 4px; }
  .nag-skin .dataTables_wrapper .dataTables_paginate .page-link { padding: 4px 9px; font-size: 12px; }
}
.nag-skin table.dataTable.dtr-inline.collapsed > tbody > tr > td.dtr-control {
  position: relative;
  padding-left: 36px;
  cursor: pointer;
}
.nag-skin table.dataTable.dtr-inline.collapsed > tbody > tr > td.dtr-control:before {
  top: 50%;
  left: 9px;
  width: 19px;
  height: 19px;
  margin-top: 0;
  transform: translateY(-50%);
  border: 0;
  border-radius: 50%;
  box-shadow: 0 2px 5px rgba(30, 58, 95, .25);
  box-sizing: border-box;
  background: #1e3a5f;
  color: #fff;
  font-family: inherit;
  font-size: 15px;
  font-weight: 700;
  line-height: 19px;
  content: '+';
  transition: transform .15s ease;
}
.nag-skin table.dataTable.dtr-inline.collapsed > tbody > tr.parent > td.dtr-control:before {
  content: '\2212';
  background: #b91c1c;
  box-shadow: 0 2px 5px rgba(185, 28, 28, .25);
}
.nag-skin table.dataTable.dtr-inline.collapsed > tbody > tr > td.dtr-control:hover:before { transform: translateY(-50%) scale(1.1); }
/* Baris yang sedang dibuka diberi latar supaya jelas detail milik baris mana */
.nag-skin table.dataTable.dtr-inline.collapsed > tbody > tr.parent > td { background: #f5f8fc; border-bottom-color: transparent; }
.nag-skin table.dataTable > tbody > tr.child > td.child { padding: 0 10px 10px !important; background: #f5f8fc; }
.nag-skin table.dataTable > tbody > tr.child:hover > td { background: #f5f8fc; }
/* Isi detail: label kecil di atas nilainya, satu kolom di HP, dua kolom di tablet */
.nag-skin .md-dtr {
  display: grid;
  grid-template-columns: 1fr;
  gap: 8px 16px;
  padding: 10px 12px;
  background: #fff;
  border: 1px solid #e2e8f0;
  border-radius: 10px;
  white-space: normal;
}
.nag-skin .md-dtr-butir { min-width: 0; }
.nag-skin .md-dtr-label {
  display: block;
  margin-bottom: 1px;
  font-size: 10.5px;
  font-weight: 600;
  letter-spacing: .3px;
  color: #64748b;
  text-transform: uppercase;
}
.nag-skin .md-dtr-nilai { font-size: 12.5px; color: #1e293b; word-break: break-word; }
.nag-skin .md-dtr-nilai .md-klem { -webkit-line-clamp: unset; line-clamp: unset; display: block; }
.nag-skin .md-dtr-nilai .md-sub { white-space: normal; }
@media (min-width: 576px) {
  .nag-skin .md-dtr { grid-template-columns: repeat(2, minmax(0, 1fr)); }
}
