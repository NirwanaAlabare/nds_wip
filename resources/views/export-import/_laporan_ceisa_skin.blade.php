{{-- ===========================================================================
     Tampilan laporan CEISA: Rekonsiliasi Ceisa - Signalbit, Ceisa Detail dan
     Data BC Signalbit.
     Dasarnya skin Invoice EXIM (invoice/_skin) - satu modul, satu gaya: kepala
     halaman berikon, kartu filter, tombol navy, tabel berkepala navy, pil status.
     Di sini cuma tambahan untuk tabel laporan yang lebar (18-20 kolom).
     Dipakai lewat @include di section custom-link, sesudah CSS DataTables.
     =========================================================================== --}}
@include('export-import.invoice._skin')
<style>
/* Tabel digeser ke samping di dalam wadahnya sendiri - kotak filter, Search dan
   nomor halaman tetap di tempat. Tingginya dibatasi layar dan kepala tabelnya
   menempel waktu digulir ke bawah, jadi judul kolom selalu terlihat. */
.nag-skin .dn-lap-gulir {
  max-height: max(320px, calc(100vh - 380px));
  overflow: auto;
  border: 1px solid #e4ecf7;
  background: #fff;
}
.nag-skin .dn-lap-gulir > .dn-table { margin: 0 !important; }
/* Yang menempel thead-nya, bukan tiap th: lebar kolom yang pecahan piksel
   menyisakan celah 1px di antara sel, dan latar navy thead menutup celah itu -
   kalau tiap th yang menempel, celahnya tembus putih / tembus baris di bawahnya. */
.nag-skin .dn-lap-gulir .dn-table thead {
  position: sticky;
  top: 0;
  z-index: 2;
  background: linear-gradient(180deg, #2c5282 0%, #1e3a5f 100%);
}
/* Laporan ini tidak bisa diurutkan per kolom, jadi ruang tanda urut di kanan
   judul kolom tidak perlu. */
.nag-skin .dn-lap-gulir .dn-table thead th { padding-right: 12px; }

/* Kolom beku: kolom identitas dokumen di kiri tetap terlihat waktu tabel
   digeser ke samping. Posisi kiri tiap kolom diisi pasangBeku() (JS) dari lebar
   kolom sebenarnya. Latarnya ikut aturan baris (belang / hover) yang sudah pejal,
   jadi isi kolom lain yang lewat di bawahnya tidak tembus. */
.nag-skin .dn-lap-tabel .dn-lap-beku { position: sticky; z-index: 1; }
/* Garis bayangan di tepi kanan kolom beku, hanya waktu tabel sedang digeser. */
.nag-skin .dn-lap-gulir.is-geser tbody .dn-lap-beku-akhir { box-shadow: 6px 0 8px -6px rgba(15, 23, 42, .28); }
.nag-skin .dn-lap-gulir.is-geser thead .dn-lap-beku-akhir {
  box-shadow: inset 0 -1px 0 rgba(148, 197, 255, .28), 6px 0 8px -6px rgba(15, 23, 42, .45);
}
/* Layar sempit: kolom beku malah menutup separuh tabel - dilepas. */
@media (max-width: 767px) {
  .nag-skin .dn-lap-tabel .dn-lap-beku { position: static; }
}

/* Baris belang tipis: kolomnya banyak, jadi mata perlu pegangan waktu membaca
   satu baris dari kiri ke kanan. */
.nag-skin .dn-lap-tabel tbody tr:nth-child(even) td { background: #f8fafc; }
.nag-skin .dn-lap-tabel tbody tr:hover td { background: #eaf2ff; }

/* Nama supplier & nama barang boleh turun baris, selebihnya tetap satu baris
   supaya angka & tanggal tidak terpecah. */
.nag-skin .dn-lap-tabel .dn-lap-lipat {
  min-width: 180px;
  max-width: 280px;
  white-space: normal;
  overflow-wrap: anywhere;
  line-height: 1.35;
}
/* Nama barang: teksnya bisa sangat panjang (spesifikasi lengkap, campur huruf
   Cina). Kolomnya dibuat lebar dan isinya dipotong 3 baris supaya tinggi baris
   rata - teks lengkapnya muncul waktu kursor diarahkan ke sel, dan tetap utuh
   di Excel. */
.nag-skin .dn-lap-tabel .dn-lap-lipat.dn-lap-lebar {
  min-width: 340px;
  max-width: 520px;
}
.nag-skin .dn-lap-tabel .dn-lap-potong {
  display: -webkit-box;
  -webkit-box-orient: vertical;
  -webkit-line-clamp: 3;
  line-clamp: 3;
  overflow: hidden;
  cursor: help;
}
/* Nomor daftar = identitas dokumen, dibuat menonjol seperti nomor invoice. */
.nag-skin .dn-lap-tabel .dn-lap-kunci { font-weight: 700; color: #1e3a5f; letter-spacing: .2px; }
.nag-skin .dn-lap-tabel td.dn-nilai { font-weight: 600; color: #0f2942; }
/* Selisih: merah kalau ada, redup kalau nol - yang dicari orang yang merah. */
.nag-skin .dn-lap-tabel .dn-lap-beda { color: #b91c1c; font-weight: 700; }
.nag-skin .dn-lap-tabel .dn-lap-nol { color: #94a3b8; }
.nag-skin .dn-lap-tabel .dn-lap-kosong { color: #cbd5e1; }
.nag-skin .dn-lap-tabel .dn-lap-redup { color: #64748b; }
</style>
