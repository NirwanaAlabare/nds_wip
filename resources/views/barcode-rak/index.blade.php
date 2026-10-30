{{-- ===========================================================================
     Print Barcode per Rak (Warehouse Fabric > Laporan).

     Daftar rak yang masih punya stok roll (view data_stock_fabric), untuk mencetak
     ulang label barcode roll satu rak sekaligus - sebelumnya hanya bisa per BPB.
     Tampilan & warnanya mengikuti skin Invoice EXIM (export-import/invoice/_skin);
     semua aturan diawali .brk-skin / .brk-modal supaya tidak bocor.

     Alur print: PDF dibuka sebagai link GET (barcode-rak/pdf?rak=...) di tab
     baru, jadi tab PDF bisa di-refresh untuk label dari stok terbaru. Tab
     dibuka saat tombol diklik (tidak diblokir popup blocker) dan menampilkan
     halaman tunggu sampai PDF selesai dibuat. Label PDF meniru label BPB ERP
     (lihat barcode-rak/pdf/label.blade.php).
     =========================================================================== --}}
@extends('layouts.index', ['containerFluid' => true])

@section('custom-link')
<link rel="stylesheet" href="{{ asset('plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
<style>
/* Palet navy = skin Invoice EXIM (export-import/invoice/_skin): #2c5282 -> #1e3a5f -> #0f2942 */
.brk-skin, .brk-modal {
  --n-950: #0f2942; --n-900: #1e3a5f; --n-700: #2c5282; --n-500: #3b6db0; --n-300: #9fb6d6;
  --n-200: #cbd8e6; --n-100: #eef2f7; --n-50: #f5f7fb;
  --ink: #1e293b; --ink-2: #475569; --muted: #64748b; --line: #e2e8f0; --line-2: #d7dee9;
  --zebra: #f8fafc; --hover: #eaf2ff; --pilih: #e0f2fe;
  --ok: #166534; --ok-bg: #dcfce7; --warn: #92400e; --warn-bg: #fef3c7; --bahaya: #b91c1c; --bahaya-bg: #fee2e2;
  --bayang: rgba(30, 58, 95, .45); --fokus: rgba(44, 82, 130, .15); --fokus-kuat: rgba(44, 82, 130, .35);
}
.brk-skin { padding: 18px 18px 96px; color: var(--ink); }
.brk-skin input[type=checkbox], .brk-modal input[type=checkbox] { accent-color: var(--n-700); width: 15px; height: 15px; cursor: pointer; vertical-align: middle; }

/* ===== Kepala halaman ===== */
.brk-kepala { display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px 16px; margin-bottom: 16px; }
.brk-kepala-kiri { display: flex; align-items: center; gap: 12px; min-width: 0; }
.brk-kepala-ikon {
  flex: 0 0 auto; width: 40px; height: 40px; border-radius: 11px;
  background: linear-gradient(135deg, var(--n-900), var(--n-700)); color: #f8fafc; font-size: 16px;
  display: inline-flex; align-items: center; justify-content: center;
  box-shadow: 0 6px 14px -6px rgba(30, 58, 95, .65);
}
.brk-kepala-judul { margin: 0; font-size: 19px; font-weight: 700; letter-spacing: -.2px; color: var(--n-950); line-height: 1.2; }
.brk-kepala-sub { margin: 2px 0 0; font-size: 12px; color: var(--muted); }
.brk-kepala-tombol { display: flex; gap: 8px; flex-wrap: wrap; align-items: center; }
.brk-waktu { font-size: 11.5px; color: var(--muted); display: inline-flex; align-items: center; gap: 6px; padding: 0 4px; }
.brk-waktu .brk-titik { width: 7px; height: 7px; border-radius: 50%; background: #22c55e; box-shadow: 0 0 0 3px rgba(34, 197, 94, .18); }

/* ===== Tombol ===== */
.brk-skin .btn, .brk-modal .btn { height: 36px; border-radius: 8px; font-size: 12.5px; font-weight: 600; display: inline-flex; align-items: center; gap: 7px; padding: 0 14px; }
.brk-btn-utama { background: linear-gradient(135deg, var(--n-900), var(--n-700)); border: 1px solid transparent; color: #fff; box-shadow: 0 1px 2px rgba(15, 23, 42, .12); }
.brk-btn-utama:hover, .brk-btn-utama:focus { filter: brightness(1.08); color: #fff; }
.brk-btn-utama:disabled { background: #b6c4d6; box-shadow: none; color: #fff; opacity: 1; }
.brk-btn-netral { background: #fff; border: 1px solid var(--line-2); color: #334155; }
.brk-btn-netral:hover, .brk-btn-netral:focus { background: #f1f5f9; border-color: #c3cedd; color: var(--n-900); }
.brk-skin .btn:focus-visible, .brk-modal .btn:focus-visible { outline: 0; box-shadow: 0 0 0 3px var(--fokus-kuat); }
.brk-skin .brk-aksi .btn { height: 30px; padding: 0 10px; font-size: 11.5px; border-radius: 7px; gap: 5px; }

/* ===== Kartu statistik ===== */
.brk-stat-grid { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 14px; margin-bottom: 16px; }
@media (max-width: 991px) { .brk-stat-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
.brk-stat {
  position: relative; overflow: hidden; background: #fff; border: 1px solid #dde5f0; border-radius: 12px; padding: 14px 16px;
  box-shadow: 0 1px 2px rgba(15, 23, 42, .04), 0 12px 26px -16px var(--bayang);
  display: flex; gap: 12px; align-items: center; min-width: 0;
}
.brk-stat-ikon { flex: 0 0 auto; width: 40px; height: 40px; border-radius: 11px; display: inline-flex; align-items: center; justify-content: center; font-size: 16px; background: var(--n-100); color: var(--n-700); }
.brk-stat-isi { min-width: 0; }
.brk-stat-label { font-size: 11px; font-weight: 600; letter-spacing: .3px; color: var(--muted); text-transform: uppercase; }
.brk-stat-angka { font-size: 22px; font-weight: 700; color: var(--n-950); line-height: 1.15; font-variant-numeric: tabular-nums; }
.brk-stat-ket { font-size: 11.5px; color: var(--muted); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.brk-stat.is-pilih { background: linear-gradient(135deg, var(--n-900), var(--n-700)); border-color: transparent; }
.brk-stat.is-pilih .brk-stat-ikon { background: rgba(255, 255, 255, .14); color: #fff; }
.brk-stat.is-pilih .brk-stat-label, .brk-stat.is-pilih .brk-stat-ket { color: rgba(248, 250, 252, .75); }
.brk-stat.is-pilih .brk-stat-angka { color: #fff; }
@media (max-width: 480px) {
  .brk-stat-grid { gap: 10px; }
  .brk-stat { padding: 11px 12px; }
  .brk-stat-ikon { display: none; }
  .brk-stat-angka { font-size: 19px; }
}

/* ===== Kartu ===== */
.brk-skin .card { margin-bottom: 16px; border: 1px solid #dde5f0; border-radius: 12px; box-shadow: 0 1px 2px rgba(15, 23, 42, .04), 0 12px 26px -16px var(--bayang); }
.brk-skin .card > .card-header {
  background: linear-gradient(180deg, var(--n-700) 0%, var(--n-900) 100%); border-bottom: 0; border-radius: 11px 11px 0 0;
  display: flex; align-items: center; gap: 10px; padding: 13px 18px; flex-wrap: wrap;
}
.brk-skin .card > .card-header .card-title { color: #f8fafc; font-weight: 600; font-size: 14px; letter-spacing: .5px; margin: 0; display: flex; align-items: center; gap: 9px; }
.brk-skin .card > .card-header .card-title i { opacity: .75; font-size: 13px; }
.brk-skin .card-header .brk-hitung { margin-left: auto; color: rgba(248, 250, 252, .8); font-size: 12px; }
.brk-skin .card > .card-body { padding: 18px; }

/* ===== Toolbar filter ===== */
.brk-toolbar { display: flex; flex-wrap: wrap; gap: 10px 18px; align-items: center; margin-bottom: 14px; }
.brk-cari { position: relative; flex: 1 1 320px; max-width: 460px; }
.brk-cari > i { position: absolute; left: 13px; top: 50%; transform: translateY(-50%); color: #94a3b8; font-size: 13px; pointer-events: none; }
.brk-cari input { width: 100%; height: 38px; border-radius: 8px; border: 1px solid var(--line); padding: 0 44px 0 36px; font-size: 13px; color: var(--ink); background: #fff; }
.brk-cari input:focus { outline: 0; border-color: var(--n-700); box-shadow: 0 0 0 3px var(--fokus); }
.brk-cari kbd { position: absolute; right: 10px; top: 50%; transform: translateY(-50%); background: #f1f5f9; color: var(--n-900); border: 1px solid var(--line-2); border-radius: 6px; font-size: 11px; padding: 1px 7px; box-shadow: none; }
.brk-grup { display: flex; align-items: center; gap: 6px; flex-wrap: wrap; }
.brk-grup-label { font-size: 11px; font-weight: 600; letter-spacing: .3px; text-transform: uppercase; color: var(--muted); margin-right: 2px; }
.brk-chip {
  height: 28px; padding: 0 11px; border-radius: 999px; border: 1px solid var(--line-2); background: #fff; color: #334155;
  font-size: 12px; font-weight: 600; display: inline-flex; align-items: center; gap: 6px; cursor: pointer; transition: all .12s ease; white-space: nowrap;
}
.brk-chip:hover { border-color: #c3cedd; color: var(--n-900); background: #f1f5f9; }
.brk-chip.is-aktif { background: var(--n-900); border-color: var(--n-900); color: #fff; box-shadow: 0 4px 10px -6px rgba(30, 58, 95, .8); }
.brk-chip .brk-chip-n { font-size: 10.5px; opacity: .75; font-variant-numeric: tabular-nums; }

/* ===== Tabel =====
   Kartunya sudah berjudul navy, jadi kepala tabel dibuat terang (sama dengan
   tabel di dalam kartu pada layar Create Invoice EXIM). */
.brk-scroll { overflow-x: auto; border-radius: 10px; border: 1px solid var(--line); }
.brk-table { width: 100% !important; margin: 0 !important; border-collapse: separate; border-spacing: 0; font-size: 12.5px; color: var(--ink); }
.brk-table thead th {
  background: #f1f5f9; color: var(--n-900); font-weight: 600; font-size: 12px; letter-spacing: .2px;
  white-space: nowrap; padding: 11px 12px; border: 0 !important; border-bottom: 2px solid var(--n-200) !important; vertical-align: middle;
}
.brk-table tbody td { padding: 7px 12px; vertical-align: middle; border: 0; border-bottom: 1px solid #eef2f7; font-variant-numeric: tabular-nums; }
.brk-table tbody tr:nth-child(odd) td { background: #fff; }
.brk-table tbody tr:nth-child(even) td { background: var(--zebra); }
.brk-table tbody tr:hover td { background: var(--hover); }
.brk-table tbody tr.is-pilih td { background: var(--pilih) !important; }
.brk-table tbody tr.is-pilih td:first-child { box-shadow: inset 3px 0 0 var(--n-700); }
.brk-table tbody tr:last-child td { border-bottom: 0; }
.brk-table td.dataTables_empty { text-align: center; color: var(--muted); padding: 34px 12px; }
.brk-table .brk-kanan { text-align: right; }
.brk-table .brk-tengah { text-align: center; width: 38px; }
table.dataTable.brk-table thead .sorting:before, table.dataTable.brk-table thead .sorting:after,
table.dataTable.brk-table thead .sorting_asc:before, table.dataTable.brk-table thead .sorting_asc:after,
table.dataTable.brk-table thead .sorting_desc:before, table.dataTable.brk-table thead .sorting_desc:after { color: var(--n-700); }

.brk-rak { font-weight: 700; color: var(--n-950); font-size: 13px; letter-spacing: .2px; }
.brk-gedung { display: inline-block; font-size: 10.5px; font-weight: 600; color: var(--n-700); background: var(--n-100); border-radius: 6px; padding: 1px 7px; margin-left: 6px; vertical-align: 1px; }
.brk-cetak { display: inline-flex; align-items: center; gap: 4px; font-size: 10.5px; font-weight: 600; border-radius: 6px; padding: 1px 7px; margin-top: 4px; }
.brk-cetak.is-penuh { background: var(--ok-bg); color: var(--ok); }
.brk-cetak.is-sebagian { background: var(--warn-bg); color: var(--warn); }
.brk-roll { display: flex; align-items: center; justify-content: flex-end; gap: 10px; }
.brk-roll b { min-width: 32px; text-align: right; }
.brk-bar { width: 86px; height: 6px; border-radius: 99px; background: var(--n-100); overflow: hidden; flex: 0 0 auto; }
.brk-bar > span { display: block; height: 100%; border-radius: 99px; background: linear-gradient(90deg, var(--n-300), var(--n-900)); }
.brk-qty { display: flex; flex-wrap: wrap; gap: 4px; justify-content: flex-end; }
.brk-pill { display: inline-flex; align-items: baseline; gap: 4px; border-radius: 7px; padding: 2px 8px; font-size: 11.5px; font-weight: 600; white-space: nowrap; background: #f1f5f9; color: #334155; }
.brk-pill small { font-size: 9.5px; font-weight: 700; letter-spacing: .3px; opacity: .8; }
.brk-pill.s0 { background: var(--n-100); color: var(--n-900); }
.brk-pill.s1 { background: #e0f2fe; color: #075985; }
.brk-pill.s2 { background: #fef3c7; color: #92400e; }
.brk-pill.s3 { background: #dcfce7; color: #166534; }
.brk-pill.s4 { background: #fce7f3; color: #9d174d; }
.brk-ws { line-height: 1.35; }
.brk-ws .brk-lain { color: var(--n-700); font-weight: 600; font-size: 11px; cursor: help; }
.brk-ws .brk-buyer { color: var(--muted); font-size: 11px; }
.brk-aksi { display: flex; gap: 6px; justify-content: flex-end; }
.brk-mono { font-family: ui-monospace, SFMono-Regular, Consolas, monospace; font-size: 12px; font-weight: 600; color: var(--n-900); }

/* DataTables: info & paging di bawah tabel */
.brk-dt-bawah { display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 10px; margin-top: 12px; }
.brk-dt-bawah .dataTables_info { color: var(--muted); font-size: 12px; padding: 0 !important; }
.brk-dt-bawah .dataTables_length label { display: flex; align-items: center; gap: 6px; margin: 0; font-size: 12px; color: var(--muted); }
.brk-dt-bawah .dataTables_length select { width: auto; height: 30px; border-radius: 8px; border: 1px solid var(--line-2); font-size: 12px; padding: 0 6px; }
.brk-dt-bawah .pagination { margin: 0; }
.brk-skin .pagination .page-link { border: 1px solid var(--line); color: var(--n-900); font-size: 12.5px; padding: 5px 11px; margin-left: 4px; border-radius: 8px; }
.brk-skin .pagination .page-item.active .page-link { background: var(--n-900); border-color: var(--n-900); color: #fff; }
.brk-skin .pagination .page-item.disabled .page-link { color: #cbd5e1; background: #f8fafc; }

/* ===== Overlay loading daftar ===== */
.brk-area { position: relative; min-height: 220px; }
.brk-loader { position: absolute; inset: 0; z-index: 5; display: none; align-items: center; justify-content: center; background: rgba(248, 250, 252, .78); border-radius: 10px; }
.brk-area.is-memuat .brk-loader { display: flex; }
.brk-loader-kartu { display: flex; align-items: center; gap: 10px; background: #fff; border: 1px solid var(--line); border-radius: 10px; padding: 12px 18px; font-size: 12.5px; color: var(--ink-2); box-shadow: 0 6px 20px -8px rgba(15, 23, 42, .18); }
.brk-putar { width: 16px; height: 16px; border: 2px solid var(--line); border-top-color: var(--n-700); border-radius: 50%; animation: brk-putar .7s linear infinite; flex: 0 0 auto; }
@keyframes brk-putar { to { transform: rotate(360deg); } }

/* ===== Bar aksi melayang (muncul saat ada rak dipilih) ===== */
.brk-bar-aksi {
  position: fixed; left: 50%; bottom: 18px; z-index: 1030; transform: translate(-50%, 130%); opacity: 0; transition: transform .22s ease, opacity .22s ease;
  display: flex; align-items: center; gap: 14px; flex-wrap: wrap; justify-content: center;
  background: rgba(15, 41, 66, .97); color: #fff; border-radius: 14px; padding: 10px 12px 10px 18px; max-width: calc(100vw - 32px);
  box-shadow: 0 18px 40px -12px rgba(15, 41, 66, .7); backdrop-filter: blur(6px);
}
.brk-bar-aksi.is-tampil { transform: translate(-50%, 0); opacity: 1; }
.brk-bar-aksi .brk-bar-teks { font-size: 13px; }
.brk-bar-aksi .brk-bar-teks b { font-size: 15px; }
.brk-bar-aksi .brk-bar-ket { font-size: 11.5px; color: rgba(248, 250, 252, .7); }
.brk-bar-aksi.is-lebih .brk-bar-ket { color: #fcd34d; font-weight: 600; }
.brk-bar-aksi .btn { height: 34px; }
.brk-bar-aksi .brk-btn-netral { background: transparent; color: #fff; border-color: rgba(255, 255, 255, .3); }
.brk-bar-aksi .brk-btn-netral:hover { background: rgba(255, 255, 255, .1); color: #fff; }
.brk-bar-aksi .brk-btn-utama { background: linear-gradient(135deg, var(--n-500), var(--n-700)); border-color: rgba(255, 255, 255, .15); }
.brk-bar-aksi .brk-btn-utama:disabled { background: rgba(255, 255, 255, .16); color: rgba(255, 255, 255, .55); }

/* ===== Modal ===== */
.brk-modal .modal-content { border: 0; border-radius: 14px; overflow: hidden; box-shadow: 0 30px 60px -20px rgba(15, 41, 66, .5); }
.brk-modal .modal-header { background: linear-gradient(180deg, var(--n-700) 0%, var(--n-900) 100%); color: #f8fafc; border: 0; padding: 13px 20px; align-items: center; }
.brk-modal .modal-title { font-size: 14px; font-weight: 600; letter-spacing: .3px; display: flex; align-items: center; gap: 10px; flex-wrap: wrap; }
.brk-modal .modal-title > i { opacity: .75; }
.brk-modal .modal-title b { font-size: 16px; letter-spacing: .3px; }
.brk-modal .modal-header .brk-gedung { background: rgba(255, 255, 255, .14); color: #fff; margin: 0; }
.brk-modal .brk-tutup { background: rgba(255, 255, 255, .12); border: 0; color: #fff; width: 32px; height: 32px; border-radius: 8px; display: inline-flex; align-items: center; justify-content: center; margin-left: auto; }
.brk-modal .brk-tutup:hover { background: rgba(255, 255, 255, .24); }
.brk-modal .modal-body { background: var(--n-50); padding: 18px 20px; }
.brk-modal .modal-footer { background: #fff; border-top: 1px solid var(--line); padding: 12px 20px; gap: 8px; }
.brk-modal .brk-foot-info { margin-right: auto; font-size: 12.5px; color: var(--ink-2); }
.brk-modal .brk-foot-info b { color: var(--n-950); }
.brk-modal .brk-foot-info.is-lebih { color: var(--bahaya); }
.brk-ringkas { display: flex; flex-wrap: wrap; gap: 10px; margin-bottom: 14px; }
.brk-ringkas-item { background: #fff; border: 1px solid var(--line); border-radius: 10px; padding: 9px 14px; min-width: 120px; }
.brk-ringkas-item .brk-stat-label { font-size: 10.5px; }
.brk-ringkas-item .brk-ringkas-isi { font-size: 16px; font-weight: 700; color: var(--n-950); font-variant-numeric: tabular-nums; display: flex; flex-wrap: wrap; gap: 4px; align-items: center; }
#modal-detail .brk-scroll { max-height: max(260px, calc(100vh - 380px)); overflow: auto; background: #fff; }
#modal-detail .brk-table thead th { position: sticky; top: 0; z-index: 2; }
#modal-detail .brk-table tbody td, #modal-detail .brk-table thead th { padding-left: 9px; padding-right: 9px; white-space: nowrap; }
#modal-detail .brk-item { max-width: 260px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
#modal-detail .brk-sub { font-size: 11px; color: var(--muted); max-width: 210px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }

@media (prefers-reduced-motion: reduce) {
  .brk-putar { animation-duration: 3s; }
  .brk-bar-aksi { transition: none; }
}
</style>
@endsection

@section('content')
<div class="brk-skin">

    {{-- ===== Kepala halaman ===== --}}
    <div class="brk-kepala">
        <div class="brk-kepala-kiri">
            <span class="brk-kepala-ikon"><i class="fas fa-barcode"></i></span>
            <div>
                <h1 class="brk-kepala-judul">Print Barcode per Rak</h1>
                <p class="brk-kepala-sub">Cetak ulang label roll fabric satu rak sekaligus, dengan qty dan nama rak sesuai stok terakhir</p>
            </div>
        </div>
        <div class="brk-kepala-tombol">
            <span class="brk-waktu" id="brk-waktu"><span class="brk-titik"></span> Memuat stok...</span>
            <button type="button" class="btn brk-btn-netral" onclick="muatUlang()" title="Ambil ulang stok terbaru">
                <i class="fas fa-sync-alt"></i> Refresh
            </button>
        </div>
    </div>

    {{-- ===== Statistik ===== --}}
    <div class="brk-stat-grid">
        <div class="brk-stat">
            <span class="brk-stat-ikon"><i class="fas fa-warehouse"></i></span>
            <div class="brk-stat-isi">
                <div class="brk-stat-label">Rak berisi stok</div>
                <div class="brk-stat-angka" id="stat-rak">-</div>
                <div class="brk-stat-ket" id="stat-rak-ket">&nbsp;</div>
            </div>
        </div>
        <div class="brk-stat">
            <span class="brk-stat-ikon"><i class="fas fa-scroll"></i></span>
            <div class="brk-stat-isi">
                <div class="brk-stat-label">Total roll</div>
                <div class="brk-stat-angka" id="stat-roll">-</div>
                <div class="brk-stat-ket" id="stat-roll-ket">&nbsp;</div>
            </div>
        </div>
        <div class="brk-stat">
            <span class="brk-stat-ikon"><i class="fas fa-check-double"></i></span>
            <div class="brk-stat-isi">
                <div class="brk-stat-label">Sudah dicetak</div>
                <div class="brk-stat-angka" id="stat-cetak">-</div>
                <div class="brk-stat-ket" id="stat-cetak-ket" title="Riwayat print disimpan di browser perangkat ini">di perangkat ini</div>
            </div>
        </div>
        <div class="brk-stat is-pilih">
            <span class="brk-stat-ikon"><i class="fas fa-print"></i></span>
            <div class="brk-stat-isi">
                <div class="brk-stat-label">Dipilih</div>
                <div class="brk-stat-angka" id="stat-pilih">0 rak</div>
                <div class="brk-stat-ket" id="stat-pilih-ket">Maks. {{ number_format($maxLabel) }} label per print</div>
            </div>
        </div>
    </div>

    {{-- ===== Daftar rak ===== --}}
    <div class="card">
        <div class="card-header">
            <h5 class="card-title"><i class="fas fa-layer-group"></i> Daftar Rak</h5>
            <span class="brk-hitung" id="brk-hitung"></span>
        </div>
        <div class="card-body">
            <div class="brk-toolbar">
                <div class="brk-cari">
                    <i class="fas fa-search"></i>
                    <input type="text" id="brk-cari" placeholder="Cari rak, WS, buyer, style..." title="Ketik / scan kode rak lalu Enter untuk langsung membuka raknya" autocomplete="off">
                    <kbd title="Tekan / untuk langsung mencari">/</kbd>
                </div>
                <div class="brk-grup" id="filter-gedung"><span class="brk-grup-label">Gedung</span></div>
                <div class="brk-grup" id="filter-satuan"><span class="brk-grup-label">Satuan</span></div>
                <div class="brk-grup" id="filter-status">
                    <span class="brk-grup-label">Status</span>
                    <button type="button" class="brk-chip is-aktif" data-nilai="semua">Semua</button>
                    <button type="button" class="brk-chip" data-nilai="belum">Belum dicetak</button>
                    <button type="button" class="brk-chip" data-nilai="sudah">Sudah dicetak</button>
                </div>
            </div>

            <div class="brk-area is-memuat" id="brk-area">
                <div class="brk-loader"><div class="brk-loader-kartu"><span class="brk-putar"></span> Menghitung stok per rak...</div></div>
                <div class="brk-scroll">
                    <table id="tbl-rak" class="brk-table">
                        <thead>
                            <tr>
                                <th class="brk-tengah"><input type="checkbox" id="cek-semua-rak" title="Pilih semua rak yang tampil"></th>
                                <th>Rak</th>
                                <th class="brk-kanan">Roll</th>
                                <th class="brk-kanan">Qty</th>
                                <th>WS / Buyer</th>
                                <th class="brk-kanan">Aksi</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- ===== Bar aksi melayang ===== --}}
    <div class="brk-bar-aksi" id="brk-bar-aksi" aria-live="polite">
        <div>
            <div class="brk-bar-teks" id="bar-teks"></div>
            <div class="brk-bar-ket" id="bar-ket"></div>
        </div>
        <button type="button" class="btn brk-btn-netral" onclick="kosongkanRak()"><i class="fas fa-times"></i> Kosongkan</button>
        <button type="button" class="btn brk-btn-utama" id="btn-print-pilih" onclick="printRakTerpilih()"><i class="fas fa-print"></i> Print Terpilih</button>
    </div>

    {{-- ===== Modal detail roll per rak ===== --}}
    <div class="modal fade brk-modal" id="modal-detail" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-box-open"></i> Rak <b id="detail-rak"></b> <span class="brk-gedung" id="detail-gedung"></span></h5>
                    <button type="button" class="brk-tutup" data-bs-dismiss="modal" aria-label="Tutup"><i class="fas fa-times"></i></button>
                </div>
                <div class="modal-body">
                    <div class="brk-ringkas" id="detail-ringkas"></div>
                    <div class="brk-toolbar">
                        <div class="brk-cari">
                            <i class="fas fa-search"></i>
                            <input type="text" id="detail-cari" placeholder="Cari barcode, WS, item, lot..." autocomplete="off">
                        </div>
                        <div class="brk-grup" id="detail-ws"></div>
                        <div class="brk-grup ms-auto">
                            <button type="button" class="brk-chip" onclick="pilihRollTampil(true)"><i class="fas fa-check-square"></i> Pilih yang tampil</button>
                            <button type="button" class="brk-chip" onclick="pilihRollTampil(false)"><i class="far fa-square"></i> Kosongkan</button>
                        </div>
                    </div>
                    <div class="brk-area" id="detail-area">
                        <div class="brk-loader"><div class="brk-loader-kartu"><span class="brk-putar"></span> Memuat roll...</div></div>
                        <div class="brk-scroll">
                            <table id="tbl-detail" class="brk-table">
                                <thead>
                                    <tr>
                                        <th class="brk-tengah"><input type="checkbox" id="cek-semua-roll" title="Pilih semua roll yang tampil"></th>
                                        <th>Barcode</th>
                                        <th>No WS / Style</th>
                                        <th>Item</th>
                                        <th>Lot</th>
                                        <th>Roll</th>
                                        <th class="brk-kanan">Qty</th>
                                        <th>BPB Asal</th>
                                    </tr>
                                </thead>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <span class="brk-foot-info" id="detail-info"></span>
                    <button type="button" class="btn brk-btn-netral" data-bs-dismiss="modal">Tutup</button>
                    <button type="button" class="btn brk-btn-utama" id="btn-print-roll" onclick="printRollTerpilih()" disabled>
                        <i class="fas fa-print"></i> Print Roll Terpilih
                    </button>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection

@section('custom-script')
<script src="{{ asset('plugins/datatables/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('plugins/datatables-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
<script>
    const MAX_LABEL = Number("{{ $maxLabel }}");
    const URL_DAFTAR = "{{ route('barcode-rak') }}";
    const URL_DETAIL = "{{ route('barcode-rak-detail') }}";
    const URL_PDF = "{{ route('barcode-rak-pdf') }}";

    let semuaRak = [];
    let rakByKode = new Map();
    let maksRoll = 1;
    let urutanSatuan = [];
    const rakDipilih = new Map(); // kode_lok => jumlah roll
    const filter = { gedung: 'semua', satuan: 'semua', status: 'semua' };

    // ---------- Util ----------
    function esc(v) {
        return String(v ?? '').replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
    }
    const fmtAngka = n => Number(n).toLocaleString('en-US');
    const fmtQty = n => Number(n).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    const fmtDetik = d => d < 60 ? `${Math.max(1, Math.round(d))} dtk` : `${Math.floor(d / 60)} mnt ${Math.round(d % 60)} dtk`;
    const jumlahRollDipilih = () => [...rakDipilih.values()].reduce((s, n) => s + n, 0);

    function pillQty(list) {
        return list.map(q => `<span class="brk-pill s${Math.max(0, urutanSatuan.indexOf(q.satuan)) % 5}">${fmtQty(q.qty)} <small>${esc(q.satuan)}</small></span>`).join('');
    }

    // Scanner USB ke kolom cari: QR label rak bisa berisi kode polos atau URL "...?rak=KODE"
    function kodeDariScan(teks) {
        teks = String(teks || '').trim();
        const m = teks.match(/[?&]rak=([^&#]+)/i);
        return (m ? decodeURIComponent(m[1]) : teks).trim();
    }

    function cariRak(teks) {
        const kode = kodeDariScan(teks).toUpperCase();
        return semuaRak.find(r => r.kode_lok.toUpperCase() === kode) || null;
    }

    // ---------- Riwayat print (per browser) & estimasi waktu ----------
    // Riwayat hanya di localStorage perangkat ini, sebagai penanda saat menyisir rak satu per satu.
    const Riwayat = {
        kunci: 'brk-dicetak-v1',
        data: {},
        muat() {
            try { this.data = JSON.parse(localStorage.getItem(this.kunci)) || {}; } catch (e) { this.data = {}; }
            const batas = Date.now() - 30 * 86400000;
            Object.keys(this.data).forEach(k => { if (!(this.data[k]?.t > batas)) delete this.data[k]; });
        },
        tandai(kodeLok, sebagian) {
            kodeLok.forEach(k => { this.data[k] = { t: Date.now(), sebagian: !!sebagian }; });
            try { localStorage.setItem(this.kunci, JSON.stringify(this.data)); } catch (e) {}
        },
        get(kode) { return this.data[kode] || null; },
    };
    Riwayat.muat();

    // Detik per label dikalibrasi dari print sebelumnya di perangkat ini
    const Estimasi = {
        kunci: 'brk-detik-per-label',
        dasar: 3,
        perLabel() {
            try { const v = parseFloat(localStorage.getItem(this.kunci)); return v > 0.01 && v < 3 ? v : 0.08; } catch (e) { return 0.08; }
        },
        detik(label) { return this.dasar + label * this.perLabel(); },
        catat(label, detik) {
            if (label < 20) return;
            const baru = Math.max(0.02, (detik - this.dasar) / label);
            try { localStorage.setItem(this.kunci, String(this.perLabel() * 0.4 + baru * 0.6)); } catch (e) {}
        },
    };

    function teksCetak(info) {
        if (!info) return '';
        const d = new Date(info.t);
        const hariIni = d.toDateString() === new Date().toDateString();
        const jam = d.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
        const waktu = hariIni ? jam : d.toLocaleDateString('id-ID', { day: 'numeric', month: 'short' }) + ' ' + jam;
        return info.sebagian
            ? `<span class="brk-cetak is-sebagian"><i class="fas fa-adjust"></i> Sebagian ${waktu}</span>`
            : `<span class="brk-cetak is-penuh"><i class="fas fa-check"></i> Dicetak ${waktu}</span>`;
    }

    // ---------- Daftar rak ----------
    $.fn.dataTable.ext.search.push(function (settings, data, idx, row) {
        if (settings.nTable.id !== 'tbl-rak') return true;
        if (filter.gedung !== 'semua' && (row.area || '') !== filter.gedung) return false;
        if (filter.satuan !== 'semua' && !row.qty.some(q => q.satuan === filter.satuan)) return false;
        if (filter.status === 'belum' && Riwayat.get(row.kode_lok)) return false;
        if (filter.status === 'sudah' && !Riwayat.get(row.kode_lok)) return false;
        return true;
    });

    const tblRak = $('#tbl-rak').DataTable({
        data: [],
        dom: 't<"brk-dt-bawah"lip>',
        order: [[1, 'asc']],
        pageLength: 10,
        lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, 'Semua']],
        language: {
            emptyTable: 'Tidak ada rak dengan stok roll',
            zeroRecords: 'Tidak ada rak yang cocok dengan filter',
            info: '_START_-_END_ dari _TOTAL_ rak',
            infoEmpty: '0 rak',
            infoFiltered: '(disaring dari _MAX_)',
            lengthMenu: 'Tampil _MENU_',
            paginate: { previous: '&lsaquo;', next: '&rsaquo;' },
        },
        createdRow: (tr, row) => $(tr).toggleClass('is-pilih', rakDipilih.has(row.kode_lok)),
        columns: [
            {
                data: 'kode_lok', orderable: false, searchable: false, className: 'brk-tengah',
                render: (d, t) => t === 'display' ? `<input type="checkbox" class="cek-rak" value="${esc(d)}" aria-label="Pilih rak ${esc(d)}">` : '',
            },
            {
                data: 'kode_lok',
                render: (d, t, r) => {
                    if (t === 'sort' || t === 'type') return r.urutan;
                    if (t !== 'display') return d + ' ' + (r.area || '');
                    return `<span class="brk-rak">${esc(d)}</span>${r.area ? `<span class="brk-gedung">${esc(r.area)}</span>` : ''}`
                        + (Riwayat.get(d) ? `<div>${teksCetak(Riwayat.get(d))}</div>` : '');
                },
            },
            {
                data: 'jml_roll', searchable: false, className: 'brk-kanan',
                render: (d, t) => t !== 'display' ? d
                    : `<div class="brk-roll"><span class="brk-bar"><span style="width:${Math.max(4, Math.sqrt(d / maksRoll) * 100).toFixed(1)}%"></span></span><b>${fmtAngka(d)}</b></div>`,
            },
            {
                data: 'qty', orderable: false, className: 'brk-kanan',
                render: (d, t) => t === 'display' ? `<div class="brk-qty">${pillQty(d)}</div>` : d.map(q => q.satuan).join(' '),
            },
            {
                data: 'ws', orderable: false,
                render: (d, t, r) => {
                    if (t !== 'display') return [...d, ...r.buyer, ...r.style].join(' ');
                    const lain = d.length > 2 ? ` <span class="brk-lain" title="${esc(d.join(', '))}">+${d.length - 2} WS</span>` : '';
                    const buyer = r.buyer.length ? `<div class="brk-buyer">${esc(r.buyer.slice(0, 2).join(', '))}${r.buyer.length > 2 ? ` +${r.buyer.length - 2}` : ''}</div>` : '';
                    return `<div class="brk-ws">${esc(d.slice(0, 2).join(', ')) || '-'}${lain}${buyer}</div>`;
                },
            },
            {
                data: 'kode_lok', orderable: false, searchable: false, className: 'brk-kanan',
                render: (d, t) => t !== 'display' ? '' : `<div class="brk-aksi">
                        <button type="button" class="btn brk-btn-netral btn-detail" title="Lihat & pilih roll"><i class="fas fa-list-ul"></i> Detail</button>
                        <button type="button" class="btn brk-btn-utama btn-print-rak" title="Print semua roll di rak ini"><i class="fas fa-print"></i> Print</button>
                    </div>`,
            },
        ],
    });

    tblRak.on('draw', function () {
        syncCekRak();
        const tampil = tblRak.rows({ search: 'applied' }).data().toArray();
        const roll = tampil.reduce((s, r) => s + r.jml_roll, 0);
        $('#brk-hitung').text(semuaRak.length ? `Menampilkan ${fmtAngka(tampil.length)} rak · ${fmtAngka(roll)} roll` : '');
    });

    function muatUlang() {
        $('#brk-area').addClass('is-memuat');
        $('#brk-waktu').html('<span class="brk-putar"></span> Memuat stok...');
        $.getJSON(URL_DAFTAR)
            .done(res => {
                semuaRak = res.data.map((r, i) => ({ ...r, urutan: i }));
                rakByKode = new Map(semuaRak.map(r => [r.kode_lok, r]));
                maksRoll = Math.max(1, ...semuaRak.map(r => r.jml_roll));
                urutanSatuan = [...new Set(semuaRak.flatMap(r => r.qty.map(q => q.satuan)))].sort();

                // Pilihan yang raknya sudah kosong dibuang, jumlah rollnya diperbarui
                for (const kode of [...rakDipilih.keys()]) {
                    rakByKode.has(kode) ? rakDipilih.set(kode, rakByKode.get(kode).jml_roll) : rakDipilih.delete(kode);
                }

                isiChipFilter();
                tblRak.clear().rows.add(semuaRak).draw(false);
                updateStat();
                updatePilihan();
                $('#brk-waktu').html(`<span class="brk-titik"></span> Stok per ${esc(res.waktu)}`);
            })
            .fail(() => {
                $('#brk-waktu').html('<i class="fas fa-exclamation-triangle text-danger"></i> Gagal memuat stok');
                Swal.fire({ icon: 'error', title: 'Gagal memuat daftar rak', text: 'Coba klik Refresh beberapa saat lagi.' });
            })
            .always(() => $('#brk-area').removeClass('is-memuat'));
    }

    function isiChipFilter() {
        const hitung = list => list.reduce((m, v) => m.set(v, (m.get(v) || 0) + 1), new Map());
        const gedung = hitung(semuaRak.map(r => r.area || ''));
        const satuan = hitung(semuaRak.flatMap(r => r.qty.map(q => q.satuan)));
        const chip = (nilai, label, n, aktif) =>
            `<button type="button" class="brk-chip ${aktif ? 'is-aktif' : ''}" data-nilai="${esc(nilai)}">${esc(label)}${n != null ? ` <span class="brk-chip-n">${fmtAngka(n)}</span>` : ''}</button>`;

        if (filter.gedung !== 'semua' && !gedung.has(filter.gedung)) filter.gedung = 'semua';
        if (filter.satuan !== 'semua' && !satuan.has(filter.satuan)) filter.satuan = 'semua';

        $('#filter-gedung').html('<span class="brk-grup-label">Gedung</span>' + chip('semua', 'Semua', null, filter.gedung === 'semua')
            + [...gedung.keys()].sort((a, b) => (a === '') - (b === '') || a.localeCompare(b, undefined, { numeric: true }))
                .map(g => chip(g, g || 'Tanpa gedung', gedung.get(g), filter.gedung === g)).join(''));
        $('#filter-satuan').html('<span class="brk-grup-label">Satuan</span>' + chip('semua', 'Semua', null, filter.satuan === 'semua')
            + urutanSatuan.map(s => chip(s, s, satuan.get(s), filter.satuan === s)).join(''));
    }

    $('#filter-gedung, #filter-satuan, #filter-status').on('click', '.brk-chip', function () {
        const grup = this.parentElement.id.replace('filter-', '');
        filter[grup] = this.dataset.nilai;
        $(this).addClass('is-aktif').siblings('.brk-chip').removeClass('is-aktif');
        tblRak.draw();
    });

    let tundaCari = null;
    $('#brk-cari').on('input', function () {
        clearTimeout(tundaCari);
        tundaCari = setTimeout(() => tblRak.search(kodeDariScan(this.value)).draw(), 150);
    }).on('keydown', function (e) {
        if (e.key !== 'Enter') return;
        e.preventDefault();
        const rak = cariRak(this.value);
        if (rak) {
            bukaDetail(rak.kode_lok);
        } else if (this.value.trim()) {
            iziToast.warning({ title: 'Rak tidak ditemukan', message: `"${kodeDariScan(this.value)}" tidak punya stok roll.`, position: 'topCenter' });
        }
    });

    // Tekan "/" untuk langsung ke kolom cari
    $(document).on('keydown', function (e) {
        if (e.key === '/' && !$(e.target).is('input, textarea, select') && !$('.modal.show').length) {
            e.preventDefault();
            $('#brk-cari').trigger('focus').trigger('select');
        }
    });

    function updateStat() {
        const roll = semuaRak.reduce((s, r) => s + r.jml_roll, 0);
        const gedung = new Set(semuaRak.map(r => r.area).filter(Boolean)).size;
        const terbesar = semuaRak.reduce((a, r) => (!a || r.jml_roll > a.jml_roll ? r : a), null);
        $('#stat-rak').text(fmtAngka(semuaRak.length));
        $('#stat-rak-ket').text(`tersebar di ${gedung} gedung`);
        $('#stat-roll').text(fmtAngka(roll));
        $('#stat-roll-ket').text(terbesar ? `terbanyak ${terbesar.kode_lok} (${fmtAngka(terbesar.jml_roll)} roll)` : '');

        const dicetak = semuaRak.filter(r => Riwayat.get(r.kode_lok));
        const hariIni = dicetak.filter(r => new Date(Riwayat.get(r.kode_lok).t).toDateString() === new Date().toDateString()).length;
        $('#stat-cetak').text(`${fmtAngka(dicetak.length)} rak`);
        $('#stat-cetak-ket').text(`${fmtAngka(hariIni)} hari ini · di perangkat ini`);
    }

    // ---------- Pilihan rak ----------
    function syncCekRak() {
        $(tblRak.rows({ page: 'current' }).nodes()).each(function () {
            const kode = tblRak.row(this).data().kode_lok;
            $(this).toggleClass('is-pilih', rakDipilih.has(kode)).find('.cek-rak').prop('checked', rakDipilih.has(kode));
        });
        const tampil = tblRak.rows({ search: 'applied' }).data().toArray();
        $('#cek-semua-rak').prop('checked', tampil.length > 0 && tampil.every(r => rakDipilih.has(r.kode_lok)));
    }

    function updatePilihan() {
        const roll = jumlahRollDipilih();
        const lebih = roll > MAX_LABEL;
        $('#stat-pilih').text(`${fmtAngka(rakDipilih.size)} rak`);
        $('#stat-pilih-ket').text(rakDipilih.size ? `${fmtAngka(roll)} roll · ±${fmtDetik(Estimasi.detik(roll))}` : `Maks. ${fmtAngka(MAX_LABEL)} label per print`);
        $('#bar-teks').html(`<b>${fmtAngka(rakDipilih.size)}</b> rak · <b>${fmtAngka(roll)}</b> roll dipilih`);
        $('#bar-ket').text(lebih ? `Melebihi batas ${fmtAngka(MAX_LABEL)} label per print, kurangi pilihan rak` : `Perkiraan ±${fmtDetik(Estimasi.detik(roll))} · urut rak, WS, item, lot, roll`);
        $('#brk-bar-aksi').toggleClass('is-tampil', rakDipilih.size > 0).toggleClass('is-lebih', lebih);
        $('#btn-print-pilih').prop('disabled', lebih || rakDipilih.size === 0);
        syncCekRak();
    }

    $('#tbl-rak').on('change', '.cek-rak', function () {
        const row = tblRak.row($(this).closest('tr')).data();
        this.checked ? rakDipilih.set(row.kode_lok, row.jml_roll) : rakDipilih.delete(row.kode_lok);
        updatePilihan();
    });

    $('#cek-semua-rak').on('change', function () {
        const cek = this.checked;
        tblRak.rows({ search: 'applied' }).data().each(r => cek ? rakDipilih.set(r.kode_lok, r.jml_roll) : rakDipilih.delete(r.kode_lok));
        updatePilihan();
    });

    function kosongkanRak() {
        rakDipilih.clear();
        updatePilihan();
    }

    $('#tbl-rak').on('click', '.btn-detail', function () {
        bukaDetail(tblRak.row($(this).closest('tr')).data().kode_lok);
    });

    $('#tbl-rak').on('click', '.btn-print-rak', function () {
        const row = tblRak.row($(this).closest('tr')).data();
        bukaPdf({ kodeLok: [row.kode_lok], jumlah: row.jml_roll, judul: `Rak ${row.kode_lok}` });
    });

    function rakTerpilihUrut() {
        return [...rakDipilih.keys()].sort((a, b) => rakByKode.get(a).urutan - rakByKode.get(b).urutan);
    }

    function printRakTerpilih() {
        const kode = rakTerpilihUrut();
        bukaPdf({
            kodeLok: kode, jumlah: jumlahRollDipilih(),
            judul: kode.length === 1 ? `Rak ${kode[0]}` : `${kode.length} rak (${kode.slice(0, 3).join(', ')}${kode.length > 3 ? ', ...' : ''})`,
            sesudah: kosongkanRak,
        });
    }

    // ---------- Detail roll per rak ----------
    let detailRak = null;
    let detailData = [];
    let detailWs = 'semua';
    const rollDipilih = new Set();

    $.fn.dataTable.ext.search.push(function (settings, data, idx, row) {
        if (settings.nTable.id !== 'tbl-detail') return true;
        return detailWs === 'semua' || (row.no_ws || '') === detailWs;
    });

    const tblDetail = $('#tbl-detail').DataTable({
        data: [],
        dom: 't',
        paging: false,
        order: [],
        language: { emptyTable: 'Tidak ada roll', zeroRecords: 'Tidak ada roll yang cocok' },
        createdRow: (tr, row) => $(tr).toggleClass('is-pilih', rollDipilih.has(row.id)),
        columns: [
            {
                data: 'id', orderable: false, searchable: false, className: 'brk-tengah',
                render: (d, t) => t === 'display' ? `<input type="checkbox" class="cek-roll" value="${esc(d)}" aria-label="Pilih roll ${esc(d)}">` : '',
            },
            { data: 'id', render: (d, t) => t === 'display' ? `<span class="brk-mono">${esc(d)}</span>` : d },
            { data: 'no_ws', render: (d, t, r) => t === 'display' ? `${esc(d)}<div class="brk-sub" title="${esc(r.styleno)}">${esc(r.styleno)}</div>` : `${d ?? ''} ${r.styleno ?? ''}` },
            { data: 'item_desc', render: (d, t, r) => t === 'display' ? `<div class="brk-item" title="${esc(d)}">${esc(d)}</div><div class="brk-sub">${esc(r.kode_item)}</div>` : `${d ?? ''} ${r.kode_item ?? ''}` },
            { data: 'no_lot', render: d => esc(d) },
            { data: 'roll', render: (d, t, r) => (t === 'sort' || t === 'type') ? (parseFloat(r.no_roll) || 0) : esc(d) },
            { data: 'qty', className: 'brk-kanan', render: (d, t, r) => t === 'display' ? `<b>${fmtQty(d)}</b> <span class="text-muted small">${esc(r.satuan)}</span>` : Number(d) },
            {
                data: 'no_bpb',
                render: (d, t, r) => t === 'display'
                    ? `${esc(d)}<div class="brk-sub" title="${esc(r.supplier)}">${esc((r.no_dok.split(' | ')[1] || '') + (r.supplier && r.supplier !== '-' ? ' · ' + r.supplier : ''))}</div>`
                    : `${d ?? ''} ${r.supplier ?? ''}`,
            },
        ],
    });

    tblDetail.on('draw', syncCekRoll);

    function syncCekRoll() {
        $(tblDetail.rows().nodes()).each(function () {
            const id = tblDetail.row(this).data().id;
            $(this).toggleClass('is-pilih', rollDipilih.has(id)).find('.cek-roll').prop('checked', rollDipilih.has(id));
        });
        const tampil = tblDetail.rows({ search: 'applied' }).data().toArray();
        $('#cek-semua-roll').prop('checked', tampil.length > 0 && tampil.every(r => rollDipilih.has(r.id)));
    }

    function ringkasQty(rows) {
        const per = new Map();
        rows.forEach(r => per.set(r.satuan, (per.get(r.satuan) || 0) + Number(r.qty)));
        return [...per.entries()].sort().map(([satuan, qty]) => ({ satuan, qty }));
    }

    function updateInfoDetail() {
        const dipilih = detailData.filter(r => rollDipilih.has(r.id));
        const lebih = dipilih.length > MAX_LABEL;
        $('#detail-info').toggleClass('is-lebih', lebih).html(detailData.length === 0 ? '' :
            `<b>${fmtAngka(dipilih.length)}</b> dari ${fmtAngka(detailData.length)} roll dipilih`
            + (dipilih.length ? ` · ${ringkasQty(dipilih).map(q => `${fmtQty(q.qty)} ${esc(q.satuan)}`).join(', ')} · ±${fmtDetik(Estimasi.detik(dipilih.length))}` : '')
            + (lebih ? ` · maks. ${fmtAngka(MAX_LABEL)} label` : ''));
        $('#btn-print-roll').prop('disabled', dipilih.length === 0 || lebih);
        syncCekRoll();
    }

    function isiRingkasDetail() {
        const rak = rakByKode.get(detailRak);
        const ws = new Map();
        detailData.forEach(r => ws.set(r.no_ws || '', (ws.get(r.no_ws || '') || 0) + 1));
        const bpb = new Set(detailData.map(r => r.no_bpb)).size;
        const item = (label, isi) => `<div class="brk-ringkas-item"><div class="brk-stat-label">${label}</div><div class="brk-ringkas-isi">${isi}</div></div>`;
        $('#detail-ringkas').html(
            item('Roll', fmtAngka(detailData.length))
            + item('Qty', `<span class="brk-qty" style="justify-content:flex-start">${pillQty(ringkasQty(detailData))}</span>`)
            + item('No WS', fmtAngka(ws.size))
            + item('BPB asal', fmtAngka(bpb))
            + (rak && Riwayat.get(detailRak) ? item('Terakhir dicetak', teksCetak(Riwayat.get(detailRak))) : '')
        );

        const chip = (nilai, label, n) => `<button type="button" class="brk-chip ${detailWs === nilai ? 'is-aktif' : ''}" data-nilai="${esc(nilai)}">${esc(label)}${n != null ? ` <span class="brk-chip-n">${fmtAngka(n)}</span>` : ''}</button>`;
        const daftarWs = [...ws.entries()].sort((a, b) => b[1] - a[1]);
        $('#detail-ws').html(daftarWs.length > 1
            ? '<span class="brk-grup-label">WS</span>' + chip('semua', 'Semua', null) + daftarWs.slice(0, 8).map(([w, n]) => chip(w, w || '-', n)).join('')
                + (daftarWs.length > 8 ? `<span class="brk-grup-label" title="Gunakan kolom cari untuk WS lainnya">+${daftarWs.length - 8}</span>` : '')
            : '');
    }

    $('#detail-ws').on('click', '.brk-chip', function () {
        detailWs = this.dataset.nilai;
        $(this).addClass('is-aktif').siblings('.brk-chip').removeClass('is-aktif');
        tblDetail.draw();
    });

    $('#detail-cari').on('input', function () { tblDetail.search(this.value).draw(); });

    $('#tbl-detail').on('change', '.cek-roll', function () {
        this.checked ? rollDipilih.add(this.value) : rollDipilih.delete(this.value);
        updateInfoDetail();
    });

    $('#cek-semua-roll').on('change', function () { pilihRollTampil(this.checked); });

    function pilihRollTampil(cek) {
        tblDetail.rows({ search: 'applied' }).data().each(r => cek ? rollDipilih.add(r.id) : rollDipilih.delete(r.id));
        updateInfoDetail();
    }

    $('#modal-detail').on('shown.bs.modal', () => tblDetail.columns.adjust());

    function bukaDetail(kodeLok) {
        const rak = rakByKode.get(kodeLok);
        detailRak = kodeLok;
        detailData = [];
        detailWs = 'semua';
        rollDipilih.clear();
        $('#detail-rak').text(kodeLok);
        $('#detail-gedung').text(rak?.area || '').toggle(!!rak?.area);
        $('#detail-cari').val('');
        $('#detail-ringkas, #detail-ws').empty();
        tblDetail.search('').clear().draw();
        $('#detail-info').text('');
        $('#btn-print-roll').prop('disabled', true);
        $('#detail-area').addClass('is-memuat');
        bootstrap.Modal.getOrCreateInstance(document.getElementById('modal-detail')).show();

        $.getJSON(URL_DETAIL, { kode_lok: kodeLok })
            .done(res => {
                if (detailRak !== kodeLok) return;
                detailData = res.data;
                detailData.forEach(r => rollDipilih.add(r.id));
                isiRingkasDetail();
                tblDetail.rows.add(detailData).draw();
                updateInfoDetail();
            })
            .fail(() => $('#detail-info').addClass('is-lebih').text('Gagal memuat roll, tutup lalu coba lagi.'))
            .always(() => { if (detailRak === kodeLok) $('#detail-area').removeClass('is-memuat'); });
    }

    // Pilihan roll di modal sebagai parameter link: semua roll = cukup raknya; selain itu pakai daftar
    // yang lebih pendek antara roll terpilih (roll=) atau roll yang tidak dipilih (kecuali=).
    function pilihanRollLink() {
        const dipilih = detailData.filter(r => rollDipilih.has(r.id)).map(r => r.id);
        const tidak = detailData.filter(r => !rollDipilih.has(r.id)).map(r => r.id);
        const semua = tidak.length === 0;
        return {
            kodeLok: [detailRak],
            roll: !semua && dipilih.length <= tidak.length ? dipilih : null,
            kecuali: !semua && tidak.length < dipilih.length ? tidak : null,
            jumlah: dipilih.length,
            sebagian: !semua,
        };
    }

    function printRollTerpilih() {
        const p = pilihanRollLink();
        bootstrap.Modal.getOrCreateInstance(document.getElementById('modal-detail')).hide();
        bukaPdf({ ...p, judul: `Rak ${detailRak}` + (p.sebagian ? ` (${fmtAngka(p.jumlah)} roll terpilih)` : '') });
    }

    // ---------- Print (link PDF) ----------
    // PDF dibuka sebagai link GET di tab baru, jadi tab PDF bisa di-refresh (F5) untuk membuat ulang label
    // dari stok terbaru. Selama server membuat PDF, tab itu menampilkan halaman tunggu (dengan perkiraan
    // waktu) yang lalu berganti sendiri menjadi PDF.
    function urlPdf({ kodeLok, roll = null, kecuali = null }) {
        const gabung = list => list.map(encodeURIComponent).join(',');
        return URL_PDF + '?rak=' + gabung(kodeLok)
            + (roll && roll.length ? '&roll=' + gabung(roll) : '')
            + (kecuali && kecuali.length ? '&kecuali=' + gabung(kecuali) : '');
    }

    // Tanpa tag head/html: Debugbar menyisipkan script-nya sebelum teks penutup head yang terakhir di
    // halaman, termasuk yang ada di dalam string JS, sehingga script ini rusak.
    function halamanTunggu(win, { url, judul, jumlah }) {
        const total = Math.max(1, Math.round(Estimasi.detik(jumlah)));
        win.document.write(`<!doctype html><meta charset="utf-8"><title>Menyiapkan ${jumlah} label...</title>
            <style>
                body{margin:0;min-height:100vh;display:grid;place-items:center;font-family:system-ui,"Segoe UI",sans-serif;background:#f5f7fb;color:#0f2942}
                .k{width:min(440px,calc(100vw - 32px));background:#fff;border:1px solid #dde5f0;border-radius:14px;padding:28px 24px;text-align:center;box-shadow:0 12px 26px -16px rgba(30,58,95,.45)}
                .p{width:44px;height:44px;margin:0 auto 16px;border:4px solid #eef2f7;border-top-color:#2c5282;border-radius:50%;animation:r .8s linear infinite}
                @keyframes r{to{transform:rotate(360deg)}}
                h2{margin:0 0 4px;font-size:19px} p{margin:0;color:#64748b;font-size:13px;line-height:1.5}
                .j{height:8px;border-radius:99px;background:#eef2f7;overflow:hidden;margin:18px 0 8px}
                .i{height:100%;width:0;border-radius:99px;background:linear-gradient(90deg,#2c5282,#1e3a5f);transition:width .3s linear}
                .m{font-size:12px;font-variant-numeric:tabular-nums} .c{margin-top:14px;font-size:12px;color:#475569}
            </style>
            <div class="k" id="brk-tunggu">
                <div class="p"></div>
                <h2>Menyiapkan ${fmtAngka(jumlah)} label</h2>
                <p>${esc(judul)}</p>
                <div class="j"><div class="i" id="i"></div></div>
                <p class="m" id="m">perkiraan &plusmn;${fmtDetik(total)}</p>
                <p class="c">PDF tampil otomatis di tab ini.<br>Tekan <b>F5</b> di PDF kapan saja untuk membuat ulang label dari stok terbaru.</p>
            </div>
            <script>
                var mulai = Date.now(), total = ${total};
                setInterval(function () {
                    var d = (Date.now() - mulai) / 1000;
                    document.getElementById('i').style.width = Math.min(95, d / total * 95) + '%';
                    document.getElementById('m').textContent = Math.round(d) + ' dtk berjalan' + (d < total ? ' · sisa ±' + Math.max(1, Math.round(total - d)) + ' dtk' : ' · hampir selesai...');
                }, 300);
                setTimeout(function () { location.replace(${JSON.stringify(url)}); }, 80);
            <\/script>`);
        win.document.close();
    }

    function bukaPdf({ kodeLok, roll = null, kecuali = null, jumlah, judul, sebagian = false, sesudah = null }) {
        if (jumlah > MAX_LABEL) {
            Swal.fire({
                icon: 'warning', title: 'Terlalu banyak label',
                html: `Yang dipilih <b>${fmtAngka(jumlah)}</b> roll, maksimal <b>${fmtAngka(MAX_LABEL)}</b> label per sekali print.<br>Kurangi pilihan rak, atau buka <b>Detail</b> rak untuk memilih sebagian roll.`,
            });
            return;
        }
        const url = urlPdf({ kodeLok, roll, kecuali });
        if (url.length > 7000) {
            Swal.fire({ icon: 'warning', title: 'Link terlalu panjang', html: `Pilihan ${fmtAngka(kodeLok.length)} rak terlalu banyak untuk satu link PDF.<br>Bagi menjadi beberapa kali print.` });
            return;
        }

        // Tab dibuka sekarang, selagi masih dalam klik user, supaya tidak diblokir popup blocker
        const win = window.open('', '_blank');
        if (!win) {
            Swal.fire({ icon: 'info', title: 'Popup diblokir browser', html: `Izinkan popup untuk situs ini, atau <a href="${esc(url)}" target="_blank" rel="noopener">klik di sini untuk membuka PDF</a>.` });
            return;
        }
        halamanTunggu(win, { url, judul, jumlah });
        pantauTab(win, { url, kodeLok, jumlah, judul, sebagian });
        if (sesudah) sesudah();
    }

    // Pantau tab PDF: begitu halaman tunggu berganti, cek yang tampil PDF atau halaman error,
    // lalu tandai rak sudah dicetak dan kalibrasi perkiraan waktu.
    function pantauTab(win, { url, kodeLok, jumlah, judul, sebagian }) {
        const mulai = Date.now();
        const kelas = 'brk-toast-' + mulai;
        iziToast.info({ class: kelas, title: esc(judul), message: `Menyiapkan ${fmtAngka(jumlah)} label di tab baru...`, position: 'bottomRight', timeout: false, close: false, progressBar: false });

        const timer = setInterval(() => {
            let status, pesan = '';
            try {
                if (win.closed) {
                    status = 'tutup';
                } else {
                    const doc = win.document;
                    if (doc.getElementById('brk-tunggu')) {
                        if (Date.now() - mulai < 900000) return; // masih halaman tunggu
                        status = 'tutup';
                    } else if (doc.querySelector('meta[name="brk-error"]')) {
                        status = 'gagal';
                        pesan = doc.getElementById('brk-pesan')?.textContent || '';
                    } else {
                        status = doc.contentType === 'application/pdf' ? 'selesai' : 'gagal';
                        if (status === 'gagal') pesan = 'Tab PDF menampilkan halaman lain (sesi login habis atau terjadi error). Cek tab tersebut.';
                    }
                }
            } catch (e) {
                status = 'selesai'; // sebagian browser tidak mengizinkan membaca dokumen PDF dari tab lain
            }

            clearInterval(timer);
            const toast = document.querySelector('.' + kelas);
            if (toast) iziToast.hide({}, toast);
            if (status === 'tutup') return;

            const detik = (Date.now() - mulai) / 1000;
            if (status === 'selesai') {
                Estimasi.catat(jumlah, detik);
                Riwayat.tandai(kodeLok, sebagian);
                tblRak.rows().invalidate('data').draw(false);
                updateStat();
                iziToast.success({
                    title: `${fmtAngka(jumlah)} label siap`, message: `${esc(judul)} · ${fmtDetik(detik)}`, position: 'bottomRight', timeout: 9000,
                    buttons: [
                        ['<button type="button">Buka lagi</button>', () => window.open(url, '_blank')],
                    ],
                });
            } else {
                iziToast.error({ title: 'PDF gagal dibuat', message: esc(pesan || 'Terjadi kesalahan.'), position: 'bottomRight', timeout: 12000 });
            }
        }, 700);
    }

    muatUlang();
</script>
@endsection
