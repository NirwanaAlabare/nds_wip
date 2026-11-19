@extends('layouts.index')

@php($mktTheme = true)

@section('custom-link')
    <link rel="stylesheet" href="{{ asset('dist/css/marketing-buttons.css') }}?v={{ @filemtime(public_path('assets/dist/css/marketing-buttons.css')) }}">
    <link rel="stylesheet" href="{{ asset('dist/css/marketing-fonts.css') }}?v={{ @filemtime(public_path('assets/dist/css/marketing-fonts.css')) }}">
    <link rel="stylesheet" href="{{ asset('dist/css/marketing-list-theme.css') }}?v={{ @filemtime(public_path('assets/dist/css/marketing-list-theme.css')) }}">
    <style>
        .marketing-guide-page {
            --page: #f3f4f7; --card: #fff; --primary: #284a63; --primary2: #3d6a8a;
            --ink: #16202b; --ink2: #3a4654; --muted: #7a8494; --line: #e8ecf2;
            --soft: #f5f7fa; --thead: #c7dcff; --thead-ink: #16202b; --inter: 'Inter', system-ui, sans-serif; --pop: 'Poppins', system-ui, sans-serif;
            min-height: calc(100vh - 64px); background: var(--page); color: var(--ink); font-family: var(--inter); font-size: 14px;
        }
        .marketing-guide-page .gut { padding-left: max(16px, env(safe-area-inset-left, 0px)); padding-right: max(16px, env(safe-area-inset-right, 0px)); }
        .marketing-guide-page .inner { width: 100%; margin: 0 auto; min-width: 0; }
        .marketing-guide-page * { box-sizing: border-box; }
        .marketing-guide-page [hidden] { display: none !important; }
        /* Tema gelap = palet Marketing List. */
        html[data-theme="dark"] .marketing-guide-page {
            --page: #0a1222; --card: #111c31; --primary: #3d8bff; --primary2: #6aa8ff;
            --ink: #eef3fb; --ink2: #d3ddec; --muted: #a6b6cc; --line: #22324e; --soft: #17253d; --thead: #1a2e52; --thead-ink: #d6e6ff;
        }
        html[data-theme="dark"] .marketing-guide-page .ugwarn { background: rgba(242, 154, 27, .14); }
        html[data-theme="dark"] .marketing-guide-page mark.ugm { background: rgba(242, 154, 27, .4); }
        html[data-theme="dark"] .marketing-guide-page .ugic .o { background: var(--soft); }
.ug{padding-block:20px 48px}
.ugw{display:grid;grid-template-columns:260px minmax(0,1fr);gap:24px;align-items:start}
.ugnav{position:sticky;top:16px;display:flex;flex-direction:column;gap:12px;max-height:calc(100vh - 32px);min-width:0}
.ugs{display:flex;align-items:center;gap:8px;border:1px solid var(--line);background:var(--card);border-radius:10px;padding:0 10px;color:var(--muted)}
.ugs input{flex:1;min-width:0;border:0;background:none;outline:none;color:var(--ink);padding:10px 0;font-size:14px}
.ugtoc{background:var(--card);border:1px solid var(--line);border-radius:14px;padding:10px 8px;overflow:auto;min-height:0}
.ugtoc summary{list-style:none;font-size:11.5px;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:var(--muted);padding:4px 10px 8px;cursor:default}
.ugtoc summary::-webkit-details-marker{display:none}
.ugtoc ol{list-style:none;margin:0;padding:0;counter-reset:t}
.ugtoc li{counter-increment:t}
.ugtoc a{display:flex;gap:10px;padding:7px 10px;border-radius:8px;color:var(--ink2);text-decoration:none;font-size:13.5px;line-height:1.35}
.ugtoc a::before{content:counter(t);min-width:18px;color:var(--muted);font-size:12px;font-weight:600;padding-top:1px}
.ugtoc a:hover{background:var(--soft);color:var(--ink)}
.ugtoc a.on{background:var(--soft);color:var(--primary);font-weight:600}
.ugtoc a.on::before{color:var(--primary)}
.ugtoc li.hide{display:none}
.ugc{min-width:0;background:var(--card);border:1px solid var(--line);border-radius:18px;padding:36px 44px 40px}
.ugc>*{max-width:none}
.ughd{padding-bottom:26px;border-bottom:1px solid var(--line);margin-bottom:8px}
.ughd small{font-size:12px;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:#f29a1b}
.ughd h1{font-family:var(--pop);font-size:30px;line-height:1.2;margin:6px 0 10px;color:var(--ink);text-wrap:balance}
.ughd p{font-size:15.5px;line-height:1.65;color:var(--ink2);margin:0 0 14px;max-width:none}
.ugmeta{display:flex;flex-wrap:wrap;gap:8px}
.ugmeta span{font-size:12.5px;color:var(--muted);background:var(--soft);border-radius:999px;padding:4px 12px}
.ugsec{padding-top:30px;scroll-margin-top:16px}
.ugsec.hide{display:none}
.ugsec h2{font-family:var(--pop);font-size:21px;margin:0 0 12px;color:var(--ink);display:flex;align-items:center;gap:12px;flex-wrap:wrap}
.ugsec h2>span{width:30px;height:30px;border-radius:9px;background:var(--primary);color:#fff;display:grid;place-items:center;font-size:14px;flex:none}
.ugsec h2 em{font-style:normal;font-family:var(--inter);font-size:12px;font-weight:600;color:var(--primary);border:1px solid var(--line);border-radius:999px;padding:3px 10px}
.ugsec h3{font-size:15px;margin:18px 0 10px;color:var(--ink)}
.ugsec p,.ugsec li{font-size:14.5px;line-height:1.7;color:var(--ink2)}
.ugsec p{margin:0 0 12px;max-width:none}
.ugsec b{color:var(--ink)}
.ugsec a{color:var(--primary)}
.ugsec kbd{border:1px solid var(--line);border-bottom-width:2px;border-radius:5px;padding:0 6px;font:600 12px var(--inter);background:var(--soft);color:var(--ink2)}
.ugnote{background:var(--soft);border-left:3px solid var(--primary);border-radius:0 10px 10px 0;padding:10px 14px}
.ugwarn{background:rgba(242,154,27,.12);border-left:3px solid #f29a1b;border-radius:0 10px 10px 0;padding:10px 14px}
.ugdocs,.ugform,.ugmsg{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:10px;margin:0 0 14px}
.ugdocs div,.ugform div,.ugmsg div{border:1px solid var(--line);border-radius:12px;padding:12px 14px;display:flex;flex-direction:column;gap:4px;min-width:0}
.ugdocs div{border-top:4px solid var(--c)}
.ugdocs b,.ugform b,.ugmsg b{font-size:14.5px;color:var(--ink)}
.ugdocs span,.ugform span,.ugmsg span{font-size:13.5px;line-height:1.6;color:var(--ink2)}
.ugtw{overflow-x:auto;border:1px solid var(--line);border-radius:12px;margin:0 0 12px}
.ugt{border-collapse:collapse;width:100%;font-size:14px}
.ugt th{background:var(--thead);color:var(--thead-ink);text-align:left;padding:10px 14px;font-size:12.5px;letter-spacing:.04em;text-transform:uppercase}
.ugt td{padding:12px 14px;border-top:1px solid var(--line);vertical-align:top;color:var(--ink2);line-height:1.6}
.ugt td:first-child{white-space:nowrap}
.ugflow{list-style:none;margin:0;padding:0;display:grid;gap:0}
.ugflow li{--c:var(--primary);display:flex;gap:14px;position:relative;padding-bottom:14px}
.ugflow li:not(:last-child)::before{content:"";position:absolute;left:17px;top:36px;bottom:0;width:2px;background:var(--line)}
.ugflow i{width:36px;height:36px;border-radius:50%;flex:none;display:grid;place-items:center;font-style:normal;font-weight:700;font-size:14px;color:#fff;background:var(--c)}
.ugflow li.ap i{background:var(--card);color:var(--primary);border:2px solid var(--primary)}
.ugflow li.br i{background:#f29a1b;border-radius:8px;transform:rotate(45deg) scale(.82)}
.ugflow li.br i{font-size:0}
.ugflow li.br i::after{content:"?";font-size:15px;transform:rotate(-45deg)}
.ugflow div{flex:1;min-width:0;border:1px solid var(--line);border-radius:12px;padding:10px 14px;display:grid;grid-template-columns:minmax(0,1fr) auto;gap:2px 10px}
.ugflow b{font-size:14.5px;color:var(--ink)}
.ugflow small{font-size:12px;font-weight:600;color:var(--primary);background:var(--soft);border-radius:999px;padding:2px 10px;align-self:start;white-space:nowrap}
.ugflow span{grid-column:1/-1;font-size:13.5px;color:var(--ink2);line-height:1.6}
.ugst{display:grid;gap:8px;margin:0 0 8px}
.ugst div{display:grid;grid-template-columns:12px 170px minmax(0,1fr);gap:12px;align-items:baseline;border:1px solid var(--line);border-radius:10px;padding:10px 14px}
.ugst i{width:12px;height:12px;border-radius:3px;align-self:center}
.ugst b{font-size:14px}
.ugst span{font-size:13.5px;color:var(--ink2);line-height:1.6}
.ugic{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:10px}
.ugic div{border:1px solid var(--line);border-radius:12px;padding:14px;display:flex;flex-direction:column;gap:6px;align-items:flex-start}
.ugic span{width:30px;height:30px;border-radius:50%}
.ugic .f{background:#f29a1b}.ugic .l{border:2.5px solid #f29a1b}.ugic .o{border:1px solid var(--line);background:var(--soft)}
.ugic b{font-size:14px;color:var(--ink)}.ugic small{font-size:13px;color:var(--ink2);line-height:1.55}
.ugsteps{margin:0 0 14px;padding-left:0;list-style:none;counter-reset:s;display:grid;gap:8px}
.ugsteps>li{counter-increment:s;position:relative;padding-left:40px;min-height:28px}
.ugsteps>li::before{content:counter(s);position:absolute;left:0;top:1px;width:26px;height:26px;border-radius:50%;background:var(--soft);color:var(--primary);font-weight:700;font-size:13px;display:grid;place-items:center;border:1px solid var(--line)}
.ugsteps ul{margin:6px 0 0;padding-left:18px;display:grid;gap:4px}
.ugcards{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:12px}
.ugcards a{border:1px solid var(--line);border-radius:14px;padding:16px;text-decoration:none;display:flex;flex-direction:column;gap:6px;color:var(--ink2)}
.ugcards a:hover{border-color:var(--primary)}
.ugcards b{color:var(--ink);font-size:15px}.ugcards span{font-size:13.5px;line-height:1.6}.ugcards em{font-style:normal;font-weight:600;font-size:13px;color:var(--primary);margin-top:4px}
.ugrules{margin:0;padding-left:20px;display:grid;gap:6px}
.ugfaq{display:grid;gap:8px}
.ugfaq details{border:1px solid var(--line);border-radius:12px;padding:0 14px}
.ugfaq summary{cursor:pointer;padding:12px 0;font-weight:600;font-size:14.5px;color:var(--ink);list-style:none;display:flex;gap:10px;align-items:center}
.ugfaq summary::-webkit-details-marker{display:none}
.ugfaq summary::before{content:"+";width:20px;height:20px;flex:none;border-radius:6px;background:var(--soft);color:var(--primary);display:grid;place-items:center;font-weight:700}
.ugfaq details[open] summary::before{content:"−"}
.ugfaq p{margin:0 0 12px 30px}
.ugdl{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:8px;margin:0}
.ugdl div{border:1px solid var(--line);border-radius:10px;padding:10px 14px}
.ugdl dt{font-weight:700;color:var(--ink);font-size:14px}
.ugdl dd{margin:2px 0 0;font-size:13.5px;color:var(--ink2);line-height:1.55}
.ugend{margin:34px 0 0;padding-top:20px;border-top:1px solid var(--line);color:var(--muted);font-size:13.5px}
.ugnone{color:var(--muted);padding:30px 0;text-align:center}
mark.ugm{background:rgba(242,154,27,.3);color:inherit;border-radius:3px;padding:0 1px}
@media (max-width:960px){.ugw{grid-template-columns:minmax(0,1fr);gap:14px}.ugnav{position:static;max-height:none}.ugtoc:not([open]){padding-bottom:4px}.ugtoc summary{cursor:pointer}.ugtoc summary::after{content:" ▾"}.ugc{padding:26px 22px 30px}}
@media (max-width:640px){.ugdocs,.ugform,.ugmsg,.ugcards,.ugdl,.ugic{grid-template-columns:minmax(0,1fr)}.ugst div{grid-template-columns:12px minmax(0,1fr)}.ugst span{grid-column:2}.ughd h1{font-size:25px}.ugc{padding:22px 16px 26px;border-radius:14px}.ugflow small{grid-column:1/-1;justify-self:start}}
@media print{.mh,.crumb,.ugnav{display:none!important}.ugw{display:block}.ugc{border:0;padding:0}}
    </style>
@endsection

@section('content')
<div class="marketing-guide-page"><main class="ug gut"><div class="inner ugw">
<aside class="ugnav" aria-label="Daftar isi">
  <label class="ugs"><svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/></svg><input type="search" id="ugq" placeholder="Cari di panduan…" aria-label="Cari di panduan"></label>
  <details class="ugtoc" id="ugtoc" open><summary>Daftar isi</summary>
  <ol>
    <li><a href="#pengenalan">Pengenalan</a></li>
    <li><a href="#peran">Peran dan hak akses</a></li>
    <li><a href="#alur">Alur kerja Marketing</a></li>
    <li><a href="#status">Status dokumen dan ikon</a></li>
    <li><a href="#navigasi">Masuk dan navigasi</a></li>
    <li><a href="#costing">Membuat Costing</a></li>
    <li><a href="#approval">Approval Costing dan BOM</a></li>
    <li><a href="#so">Membuat Sales Order</a></li>
    <li><a href="#bom">Membuat BOM</a></li>
    <li><a href="#ws">Worksheet</a></li>
    <li><a href="#unlock">Mengubah dokumen yang sudah disetujui</a></li>
    <li><a href="#pantau">Memantau order</a></li>
    <li><a href="#handover">Document Handover dan cetak PDF</a></li>
    <li><a href="#pesan">Arti pesan di layar</a></li>
    <li><a href="#aturan">Aturan penting</a></li>
    <li><a href="#faq">Masalah yang sering terjadi</a></li>
    <li><a href="#istilah">Daftar istilah</a></li>
  </ol></details>
</aside>
<article class="ugc" id="ugc">
<header class="ughd">
  <small>Panduan Pengguna · Modul Marketing</small>
  <h1>Panduan Marketing NDS</h1>
  <p>Panduan ini menjelaskan cara memakai modul Marketing di sistem NDS, mulai dari menerima inquiry buyer sampai nomor Worksheet siap dipakai Purchasing untuk membeli material.</p>
  <div class="ugmeta"><span>Berlaku untuk: Admin Marketing, Manager Marketing, Direksi, Purchasing</span><span>Acuan: BPMN to-be Marketing rev. 17-11-2026</span></div>
</header>

<section id="pengenalan" class="ugsec"><h2><span>1</span>Pengenalan</h2>
<p>Modul Marketing dipakai untuk mencatat semua dokumen order garmen dalam satu alur yang saling terhubung:</p>
<div class="ugdocs">
  <div style="--c:#00a8d6"><b>Costing</b><span>Hitungan biaya material dan produksi untuk menentukan harga jual ke buyer.</span></div>
  <div style="--c:#f29a1b"><b>Sales Order (SO)</b><span>Pesanan resmi buyer: PO buyer, destinasi, ship mode, warna, size, qty, dan harga.</span></div>
  <div style="--c:#7a2fd1"><b>BOM</b><span>Daftar material per order lengkap dengan pemakaian (cons) dan harganya.</span></div>
  <div style="--c:#1d3f80"><b>Worksheet (WS)</b><span>Nomor kerja produksi. No WS menjadi acuan Purchasing untuk membuka PO material.</span></div>
</div>
<p>Setiap dokumen hanya bisa dibuat kalau dokumen sebelumnya sudah memenuhi syarat. Sistem yang menjaga urutan ini, jadi tombol yang belum boleh dipakai akan terlihat abu-abu.</p>
</section>

<section id="peran" class="ugsec"><h2><span>2</span>Peran dan hak akses</h2>
<div class="ugtw"><table class="ugt"><thead><tr><th>Peran</th><th>Yang bisa dilakukan</th></tr></thead><tbody>
<tr><td><b>Marketing</b></td><td>Menerima inquiry dan tech pack dari buyer, memastikan datanya lengkap, lalu menyerahkan dokumen fisik (Costing, BOM, SO) ke Admin Marketing.</td></tr>
<tr><td><b>Admin Marketing</b></td><td>Mengelola master data Marketing, membuat dan mengajukan Costing, membuat SO, membuat dan mengajukan BOM, mengelola Worksheet, mengajukan unlock, Document Handover, dan mencetak PDF.</td></tr>
<tr><td><b>Manager Marketing</b></td><td>Menyetujui (Approve), mengembalikan (Return to Staff), atau membatalkan (Cancel) Costing dan BOM. Melihat Marketing List dan Dashboard.</td></tr>
<tr><td><b>Direksi</b></td><td>Membuka kunci (Unlock) Costing, SO, atau BOM supaya bisa diubah. Melihat Marketing List dan Dashboard.</td></tr>
<tr><td><b>Purchasing</b></td><td>Melihat No WS dan BOM yang sudah disetujui, menerima Document Handover, lalu membuka PO material.</td></tr>
</tbody></table></div>
<p class="ugnote">Tombol yang bukan hak peran Anda tetap terlihat tetapi tidak aktif. Contoh: selain Manager Marketing, tombol Approve tidak bisa diklik.</p>
</section>

<section id="alur" class="ugsec"><h2><span>3</span>Alur kerja Marketing</h2>
<p>Urutan kerja dari awal sampai material siap dibeli:</p>
<ol class="ugflow">
  <li><i>1</i><div><b>Inquiry dan tech pack</b><small>Marketing</small><span>Terima inquiry dan tech pack dari buyer. Kalau belum lengkap, konfirmasi ulang ke buyer.</span></div></li>
  <li><i>2</i><div><b>Dokumen fisik</b><small>Marketing</small><span>Buat dokumen Costing, BOM, dan SO lalu serahkan ke Admin Marketing.</span></div></li>
  <li style="--c:#00a8d6"><i>3</i><div><b>Input dan ajukan Costing</b><small>Admin Marketing</small><span>Isi content, shell, part, cons, dan harga, lalu ajukan.</span></div></li>
  <li class="ap"><i>4</i><div><b>Approval Costing</b><small>Manager Marketing</small><span>Disetujui, atau dikembalikan dengan catatan untuk diperbaiki.</span></div></li>
  <li style="--c:#f29a1b"><i>5</i><div><b>Buat Sales Order</b><small>Admin Marketing</small><span>Dari Costing yang sudah disetujui. Detail diisi lewat form atau upload Excel. Tidak perlu approval; No WS dibuat otomatis.</span></div></li>
  <li class="br"><i>?</i><div><b>Order sampel?</b><small>Sistem</small><span>Sampel barang jadi selesai di SO, tanpa BOM dan tanpa PO material.</span></div></li>
  <li style="--c:#7a2fd1"><i>6</i><div><b>Buat dan ajukan BOM</b><small>Admin Marketing</small><span>Data costing dan qty SO ditarik otomatis. Cons dan harga tidak boleh lebih besar dari costing.</span></div></li>
  <li class="ap"><i>7</i><div><b>Approval BOM</b><small>Manager Marketing</small><span>Disetujui, atau dikembalikan dengan catatan.</span></div></li>
  <li style="--c:#1e8e5a"><i>8</i><div><b>Siap untuk Purchasing</b><small>Purchasing</small><span>No WS dengan BOM yang sudah disetujui dipakai untuk membuka PO material.</span></div></li>
</ol>
</section>

<section id="status" class="ugsec"><h2><span>4</span>Status dokumen dan ikon</h2>
<h3>Status</h3>
<div class="ugst">
  <div><i style="background:#687182"></i><b>Awaiting Approval</b><span>Sudah diajukan, menunggu keputusan Manager Marketing.</span></div>
  <div><i style="background:#c2620c"></i><b>Return to Staff</b><span>Dikembalikan ke Admin Marketing. Baca catatan Manager, perbaiki, lalu ajukan lagi.</span></div>
  <div><i style="background:#38a06c"></i><b>Approved</b><span>Sudah disetujui dan terkunci. Langkah berikutnya bisa dikerjakan.</span></div>
  <div><i style="background:#2873ff"></i><b>Unlocked</b><span>Kuncinya dibuka Direksi supaya bisa diubah. Setelah diubah harus diajukan ulang.</span></div>
  <div><i style="background:#ef5466"></i><b>Cancel</b><span>Dibatalkan dan tidak diproses lagi.</span></div>
</div>
<h3>Ikon aksi di Marketing List</h3>
<p>Setiap baris punya empat ikon: <b>Costing</b>, <b>Sales Order</b>, <b>BOM</b>, dan <b>Worksheet</b>. Tampilannya menunjukkan kondisi dokumen:</p>
<div class="ugic">
  <div><span class="f"></span><b>Berwarna penuh</b><small>Dokumen sudah ada. Klik untuk membuka.</small></div>
  <div><span class="l"></span><b>Garis berwarna</b><small>Dokumen menunggu approval.</small></div>
  <div><span class="o"></span><b>Abu-abu</b><small>Dokumen belum dibuat.</small></div>
</div>
</section>

<section id="navigasi" class="ugsec"><h2><span>5</span>Masuk dan navigasi</h2>
<ol class="ugsteps">
<li>Buka halaman login NDS, isi <b>username</b> dan <b>password</b>, lalu klik <b>Masuk</b>.</li>
<li>Di Beranda, pilih modul <b>Marketing</b>.</li>
<li>Pakai menu atas: <b>Dashboard</b>, <b>Process</b> (berisi Marketing List dan Katalog Style), <b>Approval</b>, <b>Panduan</b>, <b>Document Handover</b>, dan <b>Laporan</b>.</li>
<li>Untuk mencari menu dengan cepat, klik <b>Cari menu</b> atau tekan <kbd>Ctrl</kbd> + <kbd>K</kbd>.</li>
<li>Tombol bulan/matahari mengganti tampilan terang atau gelap (di halaman yang sudah mendukung).</li>
<li>Menu <b>Panduan</b> di navbar membuka panduan ini dari halaman mana pun di modul Marketing.</li>
</ol>
<p class="ugnote">Di HP, menu atas pindah ke tombol tiga garis di kiri atas.</p>
</section>

<section id="costing" class="ugsec"><h2><span>6</span>Membuat Costing <em>Admin Marketing</em></h2>
<p>Buka <b>Process › Marketing List</b>, lalu klik <b>Add New</b>. Form Costing terdiri dari enam bagian:</p>
<div class="ugform">
  <div><b>Information Buyer</b><span>Pilih <b>Buyer Name</b> (Kode Buyer terisi otomatis), <b>Brand</b> sesuai buyer, <b>Season</b>, lalu isi <b>Style</b>.</span></div>
  <div><b>Upload Image</b><span>Unggah gambar produk.</span></div>
  <div><b>Product &amp; Order Information</b><span>Pilih <b>Product Item</b>, <b>Marketing Order</b> (kantor marketing), dan <b>Status</b>. Tambahkan <b>Notes</b> bila perlu.</span></div>
  <div><b>Parameter Costing</b><span>Isi <b>QTY Planned (PCS)</b>, <b>SMV (Min)</b>, dan <b>VAT (%)</b>.</span></div>
  <div><b>Price &amp; Currency</b><span>Pilih <b>Currency</b>, isi <b>Confirm Price</b> (harga FOB), <b>Rate to IDR</b>, dan <b>Rate from IDR</b>.</span></div>
  <div><b>Summary Detail</b><span>Isi baris material: Content, Shell, Part, Set, Description, Supplier, Curr, Price, Cons/Pc, Unit, dan Allow (%). Kolom Price PX dan Value dalam IDR/USD dihitung otomatis. Kolom <b>Set</b> menentukan apakah order ini SET atau PIECE, dan jenis ini terbawa ke SO.</span></div>
</div>
<ol class="ugsteps">
<li>Lengkapi semua bagian di atas.</li>
<li>Klik <b>Simpan</b>. Sistem menampilkan konfirmasi <i>"Simpan dan ajukan costing?"</i>.</li>
<li>Klik <b>Save</b>. Costing berstatus <b>Awaiting Approval</b> dan dikirim ke Manager Marketing.</li>
</ol>
<p class="ugnote">Satu content bisa dipakai beberapa shell, dan satu shell bisa punya beberapa part. Susunannya fleksibel per buyer.</p>
</section>

<section id="approval" class="ugsec"><h2><span>7</span>Approval Costing dan BOM <em>Manager Marketing</em></h2>
<ol class="ugsteps">
<li>Buka menu <b>Approval</b>. Dokumen yang menunggu ada di tab Costing atau BOM.</li>
<li>Centang satu atau beberapa baris yang mau diproses.</li>
<li>Pilih tindakan:
  <ul>
    <li><b>Approve</b>: dokumen disetujui. Costing yang disetujui bisa dibuatkan SO. BOM yang disetujui membuat Purchasing bisa membuka PO material untuk No WS-nya (No WS sendiri sudah dibuat sejak SO disimpan).</li>
    <li><b>Return to Staff</b>: dokumen dikembalikan ke Admin Marketing. <b>Catatan wajib diisi</b> supaya jelas apa yang harus diperbaiki.</li>
    <li><b>Cancel</b>: dokumen dibatalkan dan pindah ke tab Cancel.</li>
  </ul></li>
<li>Konfirmasi di dialog yang muncul. Tombol <b>Back</b> untuk batal.</li>
</ol>
<p class="ugnote">Hanya baris berstatus Awaiting Approval yang diproses. Baris lain yang ikut tercentang akan dilewati, dan sistem memberi tahu jumlahnya.</p>
</section>

<section id="so" class="ugsec"><h2><span>8</span>Membuat Sales Order <em>Admin Marketing</em></h2>
<ol class="ugsteps">
<li>Di Marketing List, cari Costing berstatus <b>Approved</b>, lalu klik ikon <b>Sales Order</b>.</li>
<li>Isi header SO. Jenis order <b>SET</b> atau <b>PIECE</b> sudah ditentukan dari Costing, jadi tidak dipilih ulang di SO.</li>
<li>Isi detail SO lewat form, atau <b>upload file Excel</b>: PO buyer, destinasi, ship mode, warna, size, qty, dan harga.</li>
<li>Sistem mencocokkan setiap baris dengan master. Baris yang warna, size, destinasi, atau ship mode-nya tidak ada di master ditolak beserta alasannya. Perbaiki lalu simpan lagi.</li>
<li>Klik <b>Simpan</b> lalu <b>Save</b>. SO langsung berlaku tanpa approval, dan sistem membuat <b>No WS</b> per piece secara otomatis.</li>
</ol>
<p class="ugnote">Satu Costing bisa dipakai untuk beberapa SO selama style, price, content, dan cons-nya sama. Untuk order <b>sampel barang jadi</b>, prosesnya selesai di SO tanpa BOM dan tanpa PO material.</p>
</section>

<section id="bom" class="ugsec"><h2><span>9</span>Membuat BOM <em>Admin Marketing</em></h2>
<ol class="ugsteps">
<li>Di Marketing List, klik ikon <b>BOM</b> pada baris yang SO-nya sudah tersimpan.</li>
<li>Content, shell, cons, dan harga ditarik otomatis dari Costing, dan qty awal ditarik dari SO.</li>
<li>Lengkapi item material, cons, dan harga. Anda juga bisa <b>menyalin BOM lain</b>, lalu cek ulang warna dan size-nya.</li>
<li>Klik <b>Ajukan</b> lalu <b>Save</b>. BOM berstatus Awaiting Approval sampai disetujui Manager Marketing.</li>
</ol>
<p class="ugnote">Cons dan harga di BOM <b>tidak boleh lebih besar</b> dari Costing; kalau lebih besar, sistem menolak. Satu No WS hanya boleh punya satu BOM. PDF BOM selalu dirinci per warna, size, dan destinasi.</p>
</section>

<section id="ws" class="ugsec"><h2><span>10</span>Worksheet <em>Admin Marketing</em></h2>
<p>No WS dibuat otomatis saat SO disimpan. Setelah BOM untuk No WS itu disetujui, Worksheet bisa dilengkapi dan Purchasing bisa membuka PO material berdasarkan No WS tersebut.</p>
<p class="ugwarn">Langkah rinci pengisian Worksheet masih menunggu konfirmasi alur (Q-23). Bagian ini akan dilengkapi setelah alurnya final.</p>
</section>

<section id="unlock" class="ugsec"><h2><span>11</span>Mengubah dokumen yang sudah disetujui</h2>
<p>Costing dan BOM yang sudah <b>Approved</b>, serta SO yang sudah disimpan, terkunci. Untuk mengubahnya:</p>
<ol class="ugsteps">
<li><b>Admin Marketing</b> mengajukan unlock ke Direksi, disertai alasannya.</li>
<li><b>Direksi</b> membuka kunci dokumen. Siapa, kapan, dan alasannya tercatat di sistem.</li>
<li><b>Admin Marketing</b> mengubah dokumen lalu mengajukannya ulang.</li>
<li><b>Manager Marketing</b> menyetujui ulang Costing atau BOM. Kalau dikembalikan, Admin memperbaiki lalu mengajukan lagi.</li>
<li>Setelah disetujui, perubahan berlaku dan dokumen terkunci lagi.</li>
</ol>
<p class="ugnote"><b>Khusus SO:</b> SO tetap tidak perlu approval setelah diubah, tetapi BOM dari SO itu harus diajukan dan disetujui ulang. Costing yang sedang di-unlock tidak bisa dipakai untuk membuat SO baru. Qty yang sudah diproses modul lain (misalnya sudah dipotong) tidak bisa dikurangi atau dihapus.</p>
</section>

<section id="pantau" class="ugsec"><h2><span>12</span>Memantau order</h2>
<div class="ugcards">
  <a href="{{ route('dashboard-marketing') }}"><b>Dashboard Marketing</b><span>Ringkasan nilai dan qty order, tren, top buyer, posisi order per tahap, dan order yang paling dekat delivery.</span><em>Buka halaman ›</em></a>
  <a href="{{ route('master-costing') }}"><b>Marketing List</b><span>Daftar semua Costing beserta SO, BOM, dan Worksheet-nya. Cari, saring per status, dan buka dokumen dari sini.</span><em>Buka halaman ›</em></a>
</div>
</section>

<section id="handover" class="ugsec"><h2><span>13</span>Document Handover dan cetak PDF</h2>
<p><b>Document Handover</b> dipakai Admin Marketing untuk menyerahkan dokumen ke bagian lain, terutama Purchasing.</p>
<p>Dokumen Costing, Sales Order, BOM, dan Worksheet bisa dicetak sebagai <b>PDF ukuran A4</b>. Untuk Sales Order tersedia beberapa format: SO, SO per WS, dan SO-PO (landscape atau portrait).</p>
</section>

<section id="pesan" class="ugsec"><h2><span>14</span>Arti pesan di layar</h2>
<div class="ugmsg">
  <div><b>Toast</b><span>Kabar singkat di pojok layar, misalnya "Costing tersimpan". Hilang sendiri; pesan gagal tetap tampil sampai ditutup.</span></div>
  <div><b>Banner</b><span>Kondisi yang perlu diperhatikan, tampil di atas form. Contoh: "Costing dikembalikan" beserta catatan Manager.</span></div>
  <div><b>Pesan di field</b><span>Isian yang salah, tampil tepat di bawah kolomnya. Contoh: warna yang dipilih tidak ada di SO.</span></div>
  <div><b>Dialog</b><span>Meminta konfirmasi sebelum menyimpan, menghapus, atau menyetujui. Tombol kiri untuk batal, tombol kanan untuk menjalankan aksi.</span></div>
</div>
</section>

<section id="aturan" class="ugsec"><h2><span>15</span>Aturan penting</h2>
<ul class="ugrules">
<li>SO hanya bisa dibuat dari Costing yang sudah <b>Approved</b>.</li>
<li>Satu Costing bisa dipakai untuk beberapa SO selama <b>style</b>, <b>price</b>, <b>content</b>, dan <b>cons</b>-nya sama. Kalau salah satunya berbeda, buat Costing baru.</li>
<li>SO tidak perlu approval. No WS dibuat otomatis saat SO disimpan.</li>
<li>Warna, size, destinasi, dan ship mode di SO harus sudah ada di master.</li>
<li>BOM baru bisa dibuat setelah SO disimpan. Satu No WS hanya punya satu BOM.</li>
<li>Cons dan harga BOM tidak boleh lebih besar dari Costing.</li>
<li>Return to Staff wajib disertai catatan.</li>
<li>Costing, SO, dan BOM yang sudah tersimpan atau disetujui terkunci. Untuk mengubahnya wajib unlock, dan unlock hanya oleh Direksi.</li>
<li>Order sampel barang jadi selesai di SO.</li>
</ul>
</section>

<section id="faq" class="ugsec"><h2><span>16</span>Masalah yang sering terjadi</h2>
<div class="ugfaq">
<details><summary>Ikon Sales Order abu-abu dan tidak bisa diklik.</summary><p>Costing-nya belum disetujui. Cek statusnya di Marketing List; kalau masih Awaiting Approval, tunggu keputusan Manager Marketing.</p></details>
<details><summary>Sebagian baris detail SO ditolak saat disimpan.</summary><p>Warna, size, destinasi, atau ship mode di baris itu tidak ada di master. Baca alasan di setiap baris, perbaiki isiannya, atau minta datanya ditambahkan ke master.</p></details>
<details><summary>BOM tidak bisa diajukan karena cons atau harga lebih besar.</summary><p>Cons dan harga BOM tidak boleh melebihi Costing. Sesuaikan angkanya. Kalau Costing memang perlu berubah, ajukan unlock Costing terlebih dahulu.</p></details>
<details><summary>Muncul pesan "No WS ini sudah punya BOM".</summary><p>Satu No WS hanya boleh punya satu BOM. Buka BOM yang sudah ada untuk mengubahnya.</p></details>
<details><summary>Tombol Approve tidak aktif.</summary><p>Approve hanya untuk Manager Marketing. Peran lain tetap bisa melihat dokumennya.</p></details>
<details><summary>Saya perlu mengubah Costing, SO, atau BOM yang sudah terkunci.</summary><p>Ajukan unlock ke Direksi. Lihat bagian <a href="#unlock">Mengubah dokumen yang sudah disetujui</a>.</p></details>
<details><summary>Dokumen saya berstatus Return to Staff.</summary><p>Buka dokumennya dan baca catatan dari Manager Marketing di bagian atas form. Perbaiki sesuai catatan, lalu ajukan lagi.</p></details>
</div>
</section>

<section id="istilah" class="ugsec"><h2><span>17</span>Daftar istilah</h2>
<dl class="ugdl">
<div><dt>Tech Pack</dt><dd>Paket spesifikasi produk dari buyer: desain, ukuran, dan material.</dd></div>
<div><dt>FOB</dt><dd>Harga jual yang mencakup material dan biaya produksi.</dd></div>
<div><dt>CMT</dt><dd>Cut, Make, Trim: harga jasa jahit saja, dihitung dari SMV.</dd></div>
<div><dt>SMV</dt><dd>Standard Minute Value, menit standar untuk menjahit satu pcs.</dd></div>
<div><dt>Confirm Price</dt><dd>Harga FOB per pcs yang disepakati dengan buyer.</dd></div>
<div><dt>Content</dt><dd>Kelompok material utama dalam Costing.</dd></div>
<div><dt>Shell / Part</dt><dd>Bagian garmen yang memakai content tertentu. Satu shell bisa terdiri dari beberapa part.</dd></div>
<div><dt>Set / Piece</dt><dd>Jenis order: satu set berisi beberapa piece (misalnya atasan dan bawahan piyama), atau per piece.</dd></div>
<div><dt>Cons/Pc</dt><dd>Consumption, pemakaian material untuk satu pcs.</dd></div>
<div><dt>Allow (%)</dt><dd>Allowance, tambahan persentase material untuk cadangan.</dd></div>
<div><dt>No WS</dt><dd>Nomor Worksheet, nomor kerja produksi per piece yang dibuat otomatis dari SO.</dd></div>
<div><dt>Ship mode</dt><dd>Cara pengiriman barang ke destinasi.</dd></div>
</dl>
</section>
<p class="ugend">Ada yang belum jelas? Hubungi Admin sistem NDS atau Manager Marketing.</p>
</article>
</div></main></div>
@endsection

@section('custom-script')
<script>
(function(){
    const toc=document.getElementById('ugtoc');
    if(!toc)return;
    const links=[].slice.call(toc.querySelectorAll('a'));
    const secs=[].slice.call(document.querySelectorAll('.marketing-guide-page .ugsec'));
    if(innerWidth<=960)toc.open=false;
    const observer=new IntersectionObserver(entries=>entries.forEach(entry=>{if(entry.isIntersecting)links.forEach(a=>a.classList.toggle('on',a.getAttribute('href')=== '#'+entry.target.id));}),{rootMargin:'-20% 0px -70% 0px'});
    secs.forEach(sec=>observer.observe(sec));
    links.forEach(a=>a.addEventListener('click',()=>{if(innerWidth<=960)toc.open=false;}));
    const query=document.getElementById('ugq');
    const article=document.getElementById('ugc');
    if(!query||!article)return;
    secs.forEach(sec=>sec._source=sec.innerHTML);
    const none=document.createElement('p'); none.className='ugnone'; none.hidden=true; none.textContent='Tidak ada bagian yang cocok dengan pencarian ini.'; article.appendChild(none);
    query.addEventListener('input',()=>{const text=query.value.trim().toLowerCase();let count=0;secs.forEach((sec,index)=>{sec.innerHTML=sec._source;const ok=!text||sec.textContent.toLowerCase().includes(text);sec.classList.toggle('hide',!ok);if(links[index])links[index].parentNode.classList.toggle('hide',!ok);if(ok&&text){const walker=document.createTreeWalker(sec,NodeFilter.SHOW_TEXT,null),nodes=[];let node;while(node=walker.nextNode())nodes.push(node);nodes.forEach(node=>{const value=node.nodeValue,index=value.toLowerCase().indexOf(text);if(index<0)return;const fragment=document.createDocumentFragment();fragment.appendChild(document.createTextNode(value.slice(0,index)));const mark=document.createElement('mark');mark.className='ugm';mark.textContent=value.slice(index,index+text.length);fragment.appendChild(mark);fragment.appendChild(document.createTextNode(value.slice(index+text.length)));node.parentNode.replaceChild(fragment,node);});}if(ok)count++;});none.hidden=count>0;document.querySelector('.ughd').hidden=!!text;});
})();
</script>
@endsection