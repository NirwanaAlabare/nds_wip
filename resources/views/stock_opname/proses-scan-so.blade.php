{{-- ===========================================================================
     Proses Scan SO - scan barcode roll di satu rak (Stock Opname > pilih rak).

     Dipakai di tablet & HP (sering dengan scanner gun):
     - kotak scan besar; scanner mengirim Enter/Tab -> barcode diproses. Barcode yang
       masuk selagi barcode sebelumnya masih diproses ikut antre, tidak hilang.
     - hasil scan terakhir tampil di bawah kotak scan; error tetap pakai swal + getar.
     - Multi Barcode bisa dilipat; daftar dibagi tab (Daftar Scan / Outstanding /
       Tersimpan / Dibatalkan), di layar sempit tiap baris jadi kartu.
     - bar Simpan menempel di bawah layar.

     Semua hasil simpan dibaca dari jawaban server: tidak ada lagi "Berhasil" untuk
     barcode yang ternyata tidak tersimpan (lihat simpanbarcodeso di controller).
     =========================================================================== --}}
@extends('layouts.index', ['containerFluid' => true])

@section('custom-link')
<link rel="stylesheet" href="{{ asset('plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
@include('stock_opname._skin')
<style>
/* ===== Kotak scan ===== */
.so-scan { display: flex; gap: 8px; align-items: stretch; }
.so-scan-isian { position: relative; flex: 1 1 auto; min-width: 0; }
.so-scan-isian > i { position: absolute; left: 16px; top: 50%; transform: translateY(-50%); font-size: 20px; color: var(--n-700); pointer-events: none; }
.so-skin .so-scan-isian .form-control {
  min-height: 58px; padding-left: 52px; border: 2px solid var(--n-300); border-radius: 12px;
  font-family: ui-monospace, SFMono-Regular, Consolas, monospace; font-size: 20px; font-weight: 700; letter-spacing: .5px; color: var(--n-950);
}
.so-skin .so-scan-isian .form-control::placeholder { font-family: inherit; font-size: 15px; font-weight: 500; letter-spacing: 0; color: #94a3b8; }
.so-skin .so-scan-isian .form-control:focus { border-color: var(--n-700); box-shadow: 0 0 0 4px var(--fokus); }
.so-skin .so-scan .btn { min-height: 58px; padding: 0 20px; font-size: 15px; border-radius: 12px; }
@media (max-width: 420px) { .so-skin .so-scan .btn { padding: 0 16px; } .so-scan .btn span { display: none; } }

/* Hasil scan terakhir */
.so-umpan {
  display: flex; align-items: center; gap: 10px; margin-top: 12px; padding: 10px 14px; border-radius: 12px; border: 1px solid var(--line);
  background: var(--n-50); color: var(--ink-2); font-size: 14px; line-height: 1.35; animation: so-muncul .18s ease;
}
.so-umpan > i { font-size: 18px; flex: 0 0 auto; }
.so-umpan .so-umpan-bc { font-family: ui-monospace, SFMono-Regular, Consolas, monospace; font-weight: 700; }
.so-umpan .so-umpan-teks { min-width: 0; flex: 1 1 auto; word-break: break-word; }
.so-umpan small { flex: 0 0 auto; opacity: .7; font-size: 11.5px; }
.so-umpan.is-ok { background: var(--ok-bg); border-color: var(--ok-garis); color: var(--ok); }
.so-umpan.is-warn { background: var(--warn-bg); border-color: var(--warn-garis); color: var(--warn); }
.so-umpan.is-err { background: var(--bahaya-bg); border-color: var(--bahaya-garis); color: var(--bahaya); }
@keyframes so-muncul { from { transform: translateY(-4px); opacity: 0; } }

/* Multi barcode (bisa dilipat) */
.so-multi { margin-top: 14px; border: 1px solid var(--line); border-radius: 12px; background: var(--n-50); }
.so-multi > summary {
  list-style: none; cursor: pointer; min-height: 48px; display: flex; align-items: center; gap: 10px; padding: 0 14px;
  font-size: 14px; font-weight: 600; color: var(--n-900); user-select: none;
}
.so-multi > summary::-webkit-details-marker { display: none; }
.so-multi > summary small { font-weight: 500; color: var(--muted); }
.so-multi > summary .fa-chevron-down { margin-left: auto; font-size: 12px; transition: transform .15s ease; }
.so-multi[open] > summary .fa-chevron-down { transform: rotate(180deg); }
.so-multi-isi { padding: 0 14px 14px; }
.so-skin .so-multi textarea.form-control { min-height: 130px; font-family: ui-monospace, SFMono-Regular, Consolas, monospace; font-size: 14px; }
.so-multi-alat { display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 8px; margin-top: 10px; }
.so-multi-alat small { color: var(--muted); font-size: 12px; }

/* Nilai di tabel/kartu */
.so-ubah-dari { display: block; font-size: 11px; font-weight: 600; color: #c2410c; }
.so-rak-beda { color: var(--warn); font-weight: 700; }
.so-rak-beda i { font-size: 11px; margin-right: 3px; }
.so-mono { font-family: ui-monospace, SFMono-Regular, Consolas, monospace; }

/* ===== Bar Simpan menempel di bawah layar ===== */
.so-bar-simpan {
  position: sticky; bottom: 0; z-index: 20; margin: 8px -4px 0; display: flex; align-items: center; gap: 12px;
  padding: 10px 12px calc(10px + env(safe-area-inset-bottom)); border-radius: 14px 14px 0 0;
  background: rgba(255, 255, 255, .97); border: 1px solid var(--line); border-bottom: 0; box-shadow: 0 -12px 26px -18px rgba(15, 41, 66, .5);
}
@supports (backdrop-filter: blur(6px)) { .so-bar-simpan { background: rgba(255, 255, 255, .88); backdrop-filter: blur(6px); } }
.so-bar-info { flex: 1 1 auto; min-width: 0; font-size: 12.5px; color: var(--ink-2); line-height: 1.3; }
.so-bar-info b { font-size: 18px; color: var(--n-950); font-variant-numeric: tabular-nums; }
.so-bar-info .so-bar-qty { display: block; font-size: 12px; color: var(--muted); }
.so-skin .so-bar-simpan .btn { min-height: 50px; padding: 0 24px; font-size: 15px; }
@media (min-width: 768px) { .so-bar-simpan { margin: 8px -8px 0; } }
</style>
@endsection

@section('content')
@php($dok = $notrans[0] ?? null)
@php($lok = $lokasi[0] ?? null)
@php($qtyRak = (float) ($qty_rak[0]->qty ?? 0))
{{-- Form hanya pembungkus isian (Save lewat AJAX) - jangan sampai Enter mengirim form --}}
<form method="post" id="store-opname" onsubmit="event.preventDefault(); return false;">
    @csrf
    <input type="hidden" id="txt_no_dokumen" name="txt_no_dokumen" value="{{ $dok->no_transaksi ?? '' }}">
    <input type="hidden" id="txt_no_dokumen_hide" name="txt_no_dokumen_hide" value="{{ $dok->no_transaksi_awal ?? '' }}">
    <input type="hidden" id="txt_tgl_so" name="txt_tgl_so" value="{{ date('Y-m-d') }}">
    <input type="hidden" id="txt_lokasi" name="txt_lokasi" value="{{ $lok->rak ?? '' }}">
    <input type="hidden" id="txt_lokasi_h" name="txt_lokasi_h" value="{{ $lok->kode_lok ?? '' }}">

    <div class="so-skin">

        <div class="so-kepala">
            <div class="so-kepala-kiri">
                <a href="{{ route('list-stok-opname') }}" class="so-kepala-ikon" title="Kembali ke daftar rak" aria-label="Kembali ke daftar rak">
                    <i class="fas fa-arrow-left"></i>
                </a>
                <div class="so-kepala-teks">
                    <h1 class="so-kepala-judul">Scan Rak {{ $lok->kode_lok ?? '-' }}</h1>
                    <p class="so-kepala-sub">
                        <span class="so-chip"><i class="fas fa-clipboard-check"></i>{{ $dok->no_transaksi_awal ?? '-' }}</span>
                        <span class="so-chip"><i class="fas fa-calendar-day"></i>{{ date('d M Y') }}</span>
                    </p>
                </div>
            </div>
        </div>

        {{-- ===== Angka rak ===== --}}
        <div class="so-stat-grid">
            <div class="so-stat">
                <span class="so-stat-ikon"><i class="fas fa-warehouse"></i></span>
                <span class="so-stat-isi">
                    <span class="so-stat-label">Qty Rak</span>
                    <input type="text" class="so-stat-angka" id="txt_qty_rak" name="txt_qty_rak" value="{{ number_format($qtyRak, 2, ',', '.') }}" readonly tabindex="-1">
                    <span class="so-stat-ket">sisa belum tersimpan</span>
                </span>
            </div>
            <div class="so-stat">
                <span class="so-stat-ikon"><i class="fas fa-barcode"></i></span>
                <span class="so-stat-isi">
                    <span class="so-stat-label">Qty Scan</span>
                    <input type="text" class="so-stat-angka" id="txt_qty_scan" name="txt_qty_scan" value="" readonly tabindex="-1">
                    <span class="so-stat-ket">di daftar scan</span>
                </span>
            </div>
            <div class="so-stat">
                <span class="so-stat-ikon"><i class="fas fa-layer-group"></i></span>
                <span class="so-stat-isi">
                    <span class="so-stat-label">Roll Di-scan</span>
                    <span class="so-stat-angka" id="so-n-scan">-</span>
                    <span class="so-stat-ket">belum disimpan</span>
                </span>
            </div>
            <div class="so-stat is-partial">
                <span class="so-stat-ikon"><i class="fas fa-magnifying-glass-location"></i></span>
                <span class="so-stat-isi">
                    <span class="so-stat-label">Outstanding</span>
                    <span class="so-stat-angka" id="so-n-outstanding">-</span>
                    <span class="so-stat-ket">roll belum tersimpan</span>
                </span>
            </div>
        </div>

        {{-- ===== Scan ===== --}}
        <div class="so-kartu">
            <div class="so-kartu-kepala">
                <h5 class="so-kartu-judul"><i class="fas fa-barcode"></i> Scan Barcode</h5>
            </div>
            <div class="so-kartu-isi">
                <label class="so-label" for="txt_barcode">Scan atau ketik barcode, lalu Enter</label>
                <div class="so-scan">
                    <div class="so-scan-isian">
                        <i class="fas fa-barcode"></i>
                        <input type="text" class="form-control" id="txt_barcode" name="txt_barcode" value="" placeholder="Arahkan scanner ke label roll"
                            autofocus autocomplete="off" autocorrect="off" autocapitalize="off" spellcheck="false" enterkeyhint="go">
                    </div>
                    <button type="button" class="btn so-btn-utama" id="so-btn-proses" title="Proses barcode">
                        <i class="fas fa-arrow-right"></i><span>Proses</span>
                    </button>
                </div>
                <input type="hidden" id="txt_qty_barcode" name="txt_qty_barcode">
                <input type="hidden" id="txt_item_barcode" name="txt_item_barcode">
                <input type="hidden" id="txt_iditem_barcode" name="txt_iditem_barcode">
                <input type="hidden" id="txt_jo_barcode" name="txt_jo_barcode">
                <input type="hidden" id="txt_lok_barcode" name="txt_lok_barcode">
                <input type="hidden" id="txt_lot_barcode" name="txt_lot_barcode">
                <input type="hidden" id="txt_roll_barcode" name="txt_roll_barcode">
                <input type="hidden" id="txt_unit_barcode" name="txt_unit_barcode">

                <div class="so-umpan" id="so-umpan" hidden></div>

                <details class="so-multi" id="so-multi">
                    <summary>
                        <i class="fas fa-list-ol"></i> Multi Barcode <small>1 baris 1 barcode</small>
                        <i class="fas fa-chevron-down"></i>
                    </summary>
                    <div class="so-multi-isi">
                        <textarea id="txt_barcodes" class="form-control" rows="5" placeholder="Scan/masukkan banyak barcode (1 baris 1 barcode)"
                            autocomplete="off" autocorrect="off" autocapitalize="off" spellcheck="false"></textarea>
                        <div class="so-multi-alat">
                            <small id="so-multi-n">0 barcode</small>
                            <button type="button" id="btnSendBarcode" class="btn so-btn-utama">
                                <i class="fas fa-paper-plane"></i> Kirim
                            </button>
                        </div>
                    </div>
                </details>
            </div>
        </div>

        {{-- ===== Daftar ===== --}}
        <div class="so-tab" role="tablist">
            <button type="button" class="so-tab-btn is-aktif" data-tab="scan" role="tab" aria-selected="true">
                <i class="fas fa-list-check"></i> Daftar Scan <span class="so-tab-n" id="so-tab-n-scan">0</span>
            </button>
            <button type="button" class="so-tab-btn is-bahaya" data-tab="outstanding" role="tab" aria-selected="false">
                <i class="fas fa-hourglass-half"></i> Outstanding <span class="so-tab-n" id="so-tab-n-outstanding">0</span>
            </button>
            <button type="button" class="so-tab-btn" data-tab="tersimpan" role="tab" aria-selected="false">
                <i class="fas fa-circle-check"></i> Tersimpan <span class="so-tab-n" id="so-tab-n-tersimpan">0</span>
            </button>
            <button type="button" class="so-tab-btn" data-tab="batal" role="tab" aria-selected="false">
                <i class="fas fa-rotate-left"></i> Dibatalkan <span class="so-tab-n" id="so-tab-n-batal">0</span>
            </button>
        </div>

        <div class="so-kartu">
            <div class="so-kartu-isi">

                <div class="so-tab-panel" id="so-panel-scan" role="tabpanel">
                    <div class="so-tab-alat">
                        <span class="so-tab-ket">Barcode yang sudah di-scan di rak ini, belum disimpan.</span>
                        <button type="button" class="btn so-btn-bahaya" onclick="delete_scan_all()">
                            <i class="fas fa-trash"></i> Hapus Semua
                        </button>
                    </div>
                    <div class="so-scroll">
                        <table id="datatable" class="so-tabel so-kartu-lg text-nowrap">
                            <thead>
                                <tr>
                                    <th>No Barcode</th>
                                    <th>ID Item</th>
                                    <th>ID JO</th>
                                    <th>Item Desc</th>
                                    <th class="so-kanan">Qty SO</th>
                                    <th class="so-kanan">Qty Scan</th>
                                    <th>Unit</th>
                                    <th>Rak Scan</th>
                                    <th>Rak Saldo</th>
                                    <th class="so-tengah">Aksi</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>

                <div class="so-tab-panel" id="so-panel-outstanding" role="tabpanel" hidden>
                    <div class="so-tab-alat">
                        <span class="so-tab-ket">Roll di saldo rak ini yang belum tersimpan. <span class="so-pil is-temp"><span class="so-pil-titik"></span>Di-scan</span> = sudah di-scan, tinggal disimpan.</span>
                    </div>
                    <div class="so-scroll">
                        <table id="datatable3" class="so-tabel so-kartu-md text-nowrap">
                            <thead>
                                <tr>
                                    <th>No Barcode</th>
                                    <th>ID Item</th>
                                    <th>Item Desc</th>
                                    <th class="so-kanan">Qty</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>

                <div class="so-tab-panel" id="so-panel-tersimpan" role="tabpanel" hidden>
                    <div class="so-tab-alat">
                        <span class="so-tab-ket">Hasil scan yang sudah disimpan untuk rak ini.</span>
                    </div>
                    <div class="so-scroll">
                        <table id="datatable2" class="so-tabel so-kartu-md text-nowrap">
                            <thead>
                                <tr>
                                    <th>Rak Saldo</th>
                                    <th>No Barcode</th>
                                    <th>ID Item</th>
                                    <th>Item Desc</th>
                                    <th class="so-kanan">Qty</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>

                <div class="so-tab-panel" id="so-panel-batal" role="tabpanel" hidden>
                    <div class="so-tab-alat">
                        <span class="so-tab-ket">Scan yang dihapus dari daftar. Bisa dikembalikan.</span>
                        <button type="button" class="btn so-btn-netral" onclick="undo_scan_all()">
                            <i class="fas fa-rotate-left"></i> Kembalikan Semua
                        </button>
                    </div>
                    <div class="so-scroll">
                        <table id="datatable4" class="so-tabel so-kartu-lg text-nowrap">
                            <thead>
                                <tr>
                                    <th>No Barcode</th>
                                    <th>ID Item</th>
                                    <th>ID JO</th>
                                    <th>Item Desc</th>
                                    <th class="so-kanan">Qty SO</th>
                                    <th class="so-kanan">Qty Scan</th>
                                    <th>Unit</th>
                                    <th>Rak Scan</th>
                                    <th>Rak Saldo</th>
                                    <th class="so-tengah">Aksi</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>

            </div>
        </div>

        {{-- ===== Simpan ===== --}}
        <div class="so-bar-simpan">
            <div class="so-bar-info">
                <b id="so-bar-n" data-n="0">0</b> barcode siap disimpan
                <span class="so-bar-qty">Qty <span id="so-bar-qty">0,00</span></span>
            </div>
            <button type="button" class="btn so-btn-ok" id="so-btn-simpan" onclick="savedataopname()">
                <i class="fa-solid fa-floppy-disk"></i> Simpan
            </button>
        </div>

    </div>
</form>

{{-- ===== Ubah qty scan ===== --}}
<div class="modal fade so-modal" id="modal-edit-so" tabindex="-1" aria-hidden="true">
    <form method="post" onsubmit="event.preventDefault(); saveeditso();">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title"><i class="fas fa-pen-to-square"></i> Ubah Qty Scan</h4>
                    <button type="button" class="so-tutup" data-bs-dismiss="modal" aria-label="Tutup"><i class="fas fa-xmark"></i></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="so-label" for="mdl_barcode">No Barcode</label>
                        <input type="text" class="form-control so-mono" id="mdl_barcode" name="mdl_barcode" value="" readonly>
                    </div>
                    <div>
                        <label class="so-label" for="mdl_qty">Qty</label>
                        <input type="text" class="form-control" id="mdl_qty" name="mdl_qty" value="" inputmode="decimal" autocomplete="off">
                        <input type="hidden" id="mdl_qty_old" name="mdl_qty_old" value="">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn so-btn-netral" data-bs-dismiss="modal"><i class="fas fa-xmark"></i> Tutup</button>
                    <button type="submit" class="btn so-btn-utama" id="mdl_btn_simpan"><i class="fa-solid fa-floppy-disk"></i> Simpan</button>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection


@section('custom-script')
<script src="{{ asset('plugins/datatables/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('plugins/datatables-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
<script type="text/javascript">
// ===========================================================================
// Bantuan
// ===========================================================================
function teks(v) { return $('<div>').text(v == null ? '' : String(v)).html(); }
function angka(v, des) {
    if (v === null || v === undefined || v === '') { return '-'; }
    return (parseFloat(v) || 0).toLocaleString('id-ID', { minimumFractionDigits: des, maximumFractionDigits: des });
}
function noTransaksi() { return $('#txt_no_dokumen_hide').val(); }
function lokasiScan() { return $('#txt_lokasi_h').val(); }
function getar() { try { if (navigator.vibrate) { navigator.vibrate([120, 60, 120]); } } catch (e) {} }

// Pesan dari jawaban server yang gagal (validasi, ditolak, sesi habis, koneksi)
function pesanXhr(xhr, cadangan) {
    if (xhr && (xhr.status === 401 || xhr.status === 419)) { return 'Sesi login habis. Muat ulang halaman lalu login lagi.'; }
    var j = (xhr && xhr.responseJSON) || {};
    if (j.errors) {
        var pertama = Object.values(j.errors)[0];
        if (pertama && pertama[0]) { return pertama[0]; }
    }
    if (j.message) { return j.message; }
    return xhr && xhr.status === 0 ? 'Tidak ada koneksi ke server.' : cadangan;
}

// Hasil scan terakhir di bawah kotak scan: jenis = ok | warn | err | proses
function umpan(jenis, barcode, pesan) {
    var ikon = { ok: 'fa-circle-check', warn: 'fa-circle-exclamation', err: 'fa-circle-xmark', proses: 'fa-spinner fa-spin' }[jenis];
    var jam = new Date().toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
    $('#so-umpan').attr('class', 'so-umpan' + (jenis === 'proses' ? '' : ' is-' + jenis)).prop('hidden', false)
        .html('<i class="fas ' + ikon + '"></i><span class="so-umpan-teks"><span class="so-umpan-bc">' + teks(barcode) + '</span> '
            + teks(pesan) + '</span><small>' + jam + '</small>');
}

function muatUlangDaftar() {
    dataTableReload();
    datatable3.ajax.reload(null, false);
    datatable4.ajax.reload(null, false);
}

function dataSimpan(d) {
    return {
        lokasi_scan: lokasiScan(),
        no_barcode: d.no_barcode,
        qty: d.qty,
        id_item: d.id_item,
        id_jo: d.id_jo,
        lokasi_so: d.kode_lok,
        no_lot: d.no_lot,
        no_roll: d.no_roll,
        unit: d.unit
    };
}

// ===========================================================================
// Scan: cari barcode di saldo SO -> simpan ke daftar scan
// ===========================================================================

// Barcode tetap diproses walau tidak ada di saldo SO (ditambahkan sebagai barcode tambahan).
// diam = true (multi barcode): tanpa swal & tanpa muat ulang daftar per barcode.
function sendForceBarcode(barcode, diam) {
    return $.ajax({
        url: '{{ route("simpan-barcode-force") }}',
        type: 'POST',
        dataType: 'json',
        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
        data: {
            no_barcode: barcode,
            lokasi_scan: lokasiScan(),
            no_transaksi: noTransaksi()
        }
    }).then(function (res) {
        return res && res.status == 200
            ? { ok: true, hasil: res.hasil || 'baru', pesan: res.message || '' }
            : { ok: false, pesan: (res && res.message) || 'Barcode gagal diproses.' };
    }, function (xhr) {
        return { ok: false, pesan: pesanXhr(xhr, 'Barcode gagal diproses, coba lagi.') };
    }).then(function (h) {
        if (diam) { return h; }
        if (h.ok) {
            muatUlangDaftar();
            if (h.hasil === 'sudah') {
                umpan('warn', barcode, 'sudah ada di daftar scan rak ini');
                return h;
            }
            umpan('ok', barcode, 'ditambahkan sebagai barcode tambahan');
            return Swal.fire({ icon: 'success', title: 'Berhasil', text: 'Barcode berhasil ditambahkan: ' + barcode }).then(function () { return h; });
        }
        umpan('err', barcode, h.pesan);
        getar();
        return Swal.fire({ icon: 'error', title: 'Barcode tidak diproses', text: h.pesan }).then(function () { return h; });
    });
}

// Cari barcode di saldo SO. Hasil: ok | duplicate (sudah tersimpan di SO ini) | notfound |
// force / sudah / gagal (dari Tetap Proses) | error | skip
function getdatabarcode(no_barcode = null, opsi = {}) {
    let barcode = $.trim(no_barcode ?? $('#txt_barcode').val());

    if (!barcode) return Promise.resolve({ status: 'skip' });

    return $.ajax({
        url: '{{ route("get-data-barcodeso") }}',
        type: 'get',
        data: { no_barcode: barcode, no_transaksi: noTransaksi() },
        dataType: 'json'
    }).then(function (res) {
        if (!res || res.length === 0) {
            return Swal.fire({
                icon: 'error',
                title: 'Gagal',
                html: 'Data Barcode <b>' + teks(barcode) + '</b> Tidak Tersedia di saldo SO ini!',
                showCancelButton: true,
                confirmButtonText: 'Tutup',
                cancelButtonText: 'Tetap Proses'
            }).then(function (result) {
                // Hanya tombol Tetap Proses yang memproses; Tutup / Esc / klik di luar = tidak diproses
                if (result.dismiss === Swal.DismissReason.cancel) {
                    return sendForceBarcode(barcode, !!opsi.batch).then(function (f) {
                        return { status: f.ok ? (f.hasil === 'sudah' ? 'sudah' : 'force') : 'gagal', barcode: barcode, pesan: f.pesan };
                    });
                }
                return { status: 'notfound', barcode: barcode };
            });
        }

        res = res[0];
        if (res.barcode_so != null) {
            return { status: 'duplicate', barcode: barcode };
        }
        return { status: 'ok', barcode: barcode, rawData: res, rak_lain: lokasiScan() != res.kode_lok, lokasi_rak: res.kode_lok };
    }, function (xhr) {
        return { status: 'error', barcode: barcode, pesan: pesanXhr(xhr, 'Gagal koneksi ke server!') };
    });
}

// Simpan ke daftar scan. Hasil: { ok, hasil: 'baru' | 'sudah', pesan }.
// diam = true (multi barcode): daftar dimuat ulang sekali di akhir, bukan per barcode.
function savedatabarcode(data, diam) {
    return $.ajax({
        url: '{{ route("simpan-scan-barcode-so") }}',
        type: 'GET',
        dataType: 'json',
        data: $.extend({ no_transaksi: noTransaksi() }, data)
    }).then(function (res) {
        if (res && res.status == 200) {
            if (!diam) { muatUlangDaftar(); }
            return { ok: true, hasil: res.hasil || 'baru', pesan: res.message || '' };
        }
        return { ok: false, pesan: (res && res.message) || 'Barcode tidak tersimpan.' };
    }, function (xhr) {
        return { ok: false, hasil: (xhr.responseJSON || {}).hasil, pesan: pesanXhr(xhr, 'Barcode gagal disimpan, coba lagi.') };
    });
}

// === reset input
function resetBarcodeInputs() {
    $('#txt_qty_barcode, #txt_item_barcode, #txt_iditem_barcode, #txt_jo_barcode, #txt_lok_barcode, #txt_lot_barcode, #txt_roll_barcode, #txt_unit_barcode').val('');
    $('#txt_barcode').val('').focus();
}

// Satu barcode dari kotak scan. Diproses berurutan lewat antrean: scanner yang mengirim barcode
// berikutnya selagi barcode sebelumnya masih diproses tidak membuat barcode itu hilang.
async function prosesSatu(code) {
    umpan('proses', code, 'sedang diperiksa...');
    let res = await getdatabarcode(code);

    if (res.status === 'ok') {
        let d = res.rawData;
        let h = await savedatabarcode(dataSimpan(d));
        if (!h.ok) {
            umpan('err', code, h.pesan);
            getar();
            await Swal.fire({ icon: 'error', title: 'Barcode tidak tersimpan', text: h.pesan });
            return;
        }
        if (h.hasil === 'sudah') {
            umpan('warn', code, 'sudah ada di daftar scan rak ini');
            return;
        }
        umpan(res.rak_lain ? 'warn' : 'ok', code, 'masuk daftar scan' + (res.rak_lain ? ' - saldo di rak ' + d.kode_lok : ''));
        if (res.rak_lain) {
            await Swal.fire({ icon: 'warning', title: 'Info', text: 'Barcode ' + code + ' berada di rak ' + d.kode_lok });
        }
    } else if (res.status === 'duplicate') {
        umpan('err', code, 'sudah tersimpan di SO ini');
        getar();
        await Swal.fire({ icon: 'error', title: 'Gagal', text: 'Barcode ' + code + ' Sudah diinput!' });
    } else if (res.status === 'notfound') {
        umpan('err', code, 'tidak ada di saldo SO, tidak diproses');
    } else if (res.status === 'error') {
        umpan('err', code, res.pesan);
        getar();
        await Swal.fire({ icon: 'error', title: 'Error', text: res.pesan });
    }
    // force / sudah / gagal dari Tetap Proses sudah ditampilkan oleh sendForceBarcode
}

let antrian = Promise.resolve();
function scanBarcode(code) {
    code = $.trim(code || '');
    if (!code) { return; }
    antrian = antrian.then(function () { return prosesSatu(code); }).catch(function (e) {
        console.error('Scan gagal:', code, e);
        umpan('err', code, 'terjadi kesalahan, coba scan lagi');
    }).then(function () {
        $('#txt_barcode').focus();
    });
}

// === scan satu-satu (scanner mengirim Enter / Tab)
$('#txt_barcode').on('keydown', function(e) {
    if (["Tab","Enter"].includes(e.key) || [9,13].includes(e.keyCode)) {
        e.preventDefault();
        let code = $(this).val().trim();
        if (!code) return;
        $(this).val('');
        scanBarcode(code);
    }
});

$('#so-btn-proses').on('click', function () {
    let code = $('#txt_barcode').val().trim();
    $('#txt_barcode').val('');
    if (!code) {
        $('#txt_barcode').focus();
        return;
    }
    scanBarcode(code);
});

// ===========================================================================
// Multi barcode
// ===========================================================================
function daftarMulti() {
    return [...new Set($('#txt_barcodes').val().split("\n").map(b => b.trim()).filter(b => b.length > 0))];
}

$('#txt_barcodes').on('input', function () {
    $('#so-multi-n').text(daftarMulti().length + ' barcode');
});

function tampilMuat(i, n, bc) {
    var html = 'Barcode <b>' + i + '</b> dari <b>' + n + '</b><br><span class="so-mono">' + teks(bc) + '</span>';
    if (Swal.isVisible() && Swal.isLoading()) {
        Swal.getHtmlContainer().innerHTML = html;
        return;
    }
    Swal.fire({
        title: 'Sedang memproses...',
        html: html,
        allowOutsideClick: false,
        allowEscapeKey: false,
        showConfirmButton: false,
        didOpen: () => Swal.showLoading()
    });
}

$('#btnSendBarcode').on('click', async function () {
    let uniqueBarcodes = daftarMulti();
    if (!uniqueBarcodes.length) {
        Swal.fire({ icon: 'warning', title: 'Kosong', text: 'Tidak ada barcode untuk diproses!' });
        return;
    }

    let $tb = $(this).prop('disabled', true);
    let results = [];

    try {
        for (let i = 0; i < uniqueBarcodes.length; i++) {
            let bc = uniqueBarcodes[i];
            tampilMuat(i + 1, uniqueBarcodes.length, bc);

            let res = await getdatabarcode(bc, { batch: true });
            if (res.status === 'ok' && res.rawData) {
                let h = await savedatabarcode(dataSimpan(res.rawData), true);
                if (h.ok) {
                    results.push({ status: h.hasil === 'sudah' ? 'sudah' : 'success', barcode: bc, rak_lain: res.rak_lain, lokasi_rak: res.lokasi_rak });
                } else {
                    results.push({ status: 'gagal', barcode: bc, pesan: h.pesan });
                }
            } else {
                results.push(res);
            }
        }
    } finally {
        $tb.prop('disabled', false);
        muatUlangDaftar();
    }

    let ambil = (s, f) => results.filter(r => r.status === s && (!f || f(r)));
    let success = ambil('success', r => !r.rak_lain).map(r => teks(r.barcode));
    let rakLain = ambil('success', r => r.rak_lain).map(r => teks(r.barcode) + " (Rak: " + teks(r.lokasi_rak) + ")");
    let sudah = ambil('sudah').map(r => teks(r.barcode));
    let duplicate = ambil('duplicate').map(r => teks(r.barcode));
    let notfound = ambil('notfound').map(r => teks(r.barcode));
    let force = ambil('force').map(r => teks(r.barcode));
    let gagal = results.filter(r => r.status === 'gagal' || r.status === 'error');

    let msg = "";
    if (success.length) msg += "✅ Berhasil: " + success.join(", ") + "<br>";
    if (rakLain.length) msg += "⚠️ Berhasil tapi di rak lain: " + rakLain.join(", ") + "<br>";
    if (force.length) msg += "⚠️ Tetap proses: " + force.join(", ") + "<br>";
    if (sudah.length) msg += "ℹ️ Sudah ada di daftar scan: " + sudah.join(", ") + "<br>";
    if (duplicate.length) msg += "❌ Duplikat (sudah tersimpan): " + duplicate.join(", ") + "<br>";
    if (notfound.length) msg += "❌ Tidak ditemukan: " + notfound.join(", ") + "<br>";
    if (gagal.length) msg += "❌ Gagal disimpan:<br>" + gagal.map(r => '&nbsp;&nbsp;' + teks(r.barcode) + ' - ' + teks(r.pesan)).join("<br>") + "<br>";

    let semuaOk = results.every(r => r.status === 'success' || r.status === 'sudah' || r.status === 'force');
    if (!semuaOk) { getar(); }
    // Yang gagal disimpan dibiarkan di kotak supaya bisa langsung dikirim ulang
    $('#txt_barcodes').val(gagal.map(r => r.barcode).join("\n")).trigger('input');

    Swal.fire({ icon: semuaOk ? 'success' : 'warning', title: 'Hasil Proses', html: '<div style="text-align:left">' + msg + '</div>' });
});

// ===========================================================================
// Simpan hasil scan rak
// ===========================================================================
let sedangSimpan = false;

function savedataopname() {
    if (sedangSimpan) { return; }
    let n = parseInt($('#so-bar-n').attr('data-n'), 10) || 0;
    if (n <= 0) {
        Swal.fire({ icon: 'info', title: 'Belum ada scan', text: 'Scan barcode dulu sebelum menyimpan.' });
        return;
    }

    Swal.fire({
        icon: 'question',
        title: 'Simpan hasil scan?',
        html: '<b>' + n + '</b> barcode di rak <b>' + teks(lokasiScan()) + '</b> akan disimpan.',
        showCancelButton: true,
        confirmButtonText: '<i class="fa-solid fa-floppy-disk"></i> Ya, simpan',
        cancelButtonText: 'Batal',
        reverseButtons: true
    }).then(function (r) {
        if (r.isConfirmed) { kirimSimpan(); }
    });
}

function kirimSimpan() {
    sedangSimpan = true;
    $('#so-btn-simpan').prop('disabled', true);
    Swal.fire({ title: 'Menyimpan...', allowOutsideClick: false, allowEscapeKey: false, showConfirmButton: false, didOpen: () => Swal.showLoading() });

    return $.ajax({
        url: '{{ route('save-stockopname-fabric') }}',
        type: 'POST',
        dataType: 'json',
        data: {
            txt_qty_scan: $('#txt_qty_scan').val(),
            txt_no_dokumen: $('#txt_no_dokumen').val(),
            txt_tgl_so: $('#txt_tgl_so').val(),
            txt_lokasi_h: lokasiScan(),
        },
        success: function(res) {
            if (res && res.status == 200) {
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil',
                    text: 'Data Berhasil Disimpan ' + res.message,
                    showConfirmButton: true,
                    confirmButtonText: 'Oke',
                    timer: 1500,
                    timerProgressBar: true
                }).then(function () {
                    window.location.href = res.redirect;
                });
                return;
            }
            sedangSimpan = false;
            $('#so-btn-simpan').prop('disabled', false);
            dataTableReload();
            Swal.fire({ icon: 'warning', title: 'Tidak disimpan', text: (res && res.message) || 'Tidak ada data yang disimpan.' });
        },
        error: function(xhr) {
            sedangSimpan = false;
            $('#so-btn-simpan').prop('disabled', false);
            Swal.fire({
                icon: 'warning',
                title: 'Gagal!',
                text: pesanXhr(xhr, 'Silahkan Coba Lagi'),
                showCancelButton: true,
                confirmButtonText: 'Coba Lagi',
                cancelButtonText: 'Tutup'
            }).then(function (result) {
                if (result.isConfirmed) {
                    kirimSimpan();
                }
            });
        }
    });
}
</script>
<script>
// ===========================================================================
// Daftar (DataTables) - di layar sempit tiap baris tampil sebagai kartu
// ===========================================================================
var hp = window.matchMedia('(max-width: 575px)').matches;

function opsiTabel(id, kosong, opsi) {
    // Label tiap nilai di mode kartu = judul kolomnya
    var judul = $('#' + id + ' thead th').map(function () { return $.trim($(this).text()); }).get();
    return $.extend(true, {
        serverSide: true,
        processing: true,
        ordering: false,
        autoWidth: false,
        pageLength: 10,
        pagingType: hp ? 'simple' : 'simple_numbers',
        dom: "<'so-dt-atas'f>t<'so-dt-bawah'<'so-dt-info'i><'so-dt-hal'p>>",
        language: {
            processing: '<span class="spinner-border spinner-border-sm me-2"></span> Memuat...',
            search: '',
            searchPlaceholder: 'Cari barcode / item...',
            emptyTable: '<div class="so-kosong"><i class="fas fa-inbox"></i><span>' + kosong + '</span></div>',
            zeroRecords: '<div class="so-kosong"><i class="fas fa-magnifying-glass"></i><span>Tidak ada yang cocok.</span></div>',
            info: '_START_-_END_ dari _TOTAL_',
            infoEmpty: '0 data',
            infoFiltered: '(dari _MAX_)',
            paginate: { previous: '<i class="fas fa-chevron-left"></i>', next: '<i class="fas fa-chevron-right"></i>' }
        },
        createdRow: function (row) {
            $(row).children('td').each(function (i) {
                if (!$(this).hasClass('so-k-judul') && !$(this).hasClass('so-k-aksi') && judul[i]) {
                    this.setAttribute('data-label', judul[i]);
                }
            });
        }
    }, opsi);
}

// Nilai dari server sudah di-escape Yajra DataTables (config datatables.columns.escape = '*'),
// jadi di sini ditampilkan apa adanya - di-escape lagi justru tampil &quot; dsb.
function kolomBarcode(v, type) {
    return type === 'display' ? '<span class="so-barcode">' + (v || '') + '</span>' : v;
}
function kolomDesc(v, type) {
    return type === 'display' ? '<div class="so-desc">' + (v || '-') + '</div>' : v;
}
function kolomQty(v, type) { return type === 'display' ? angka(v, 2) : v; }
function kolomQtyScan(v, type, row) {
    if (type !== 'display') { return v; }
    return '<b>' + angka(v, 2) + '</b>' + (parseFloat(row.qty_old) > 0 ? '<span class="so-ubah-dari">diubah dari ' + angka(row.qty_old, 2) + '</span>' : '');
}
function kolomRakSaldo(v, type, row) {
    if (type !== 'display') { return v; }
    // Saldo barcode ada di rak lain dari rak yang sedang di-scan
    return v && row.lokasi_scan && v !== row.lokasi_scan
        ? '<span class="so-rak-beda" title="Saldo barcode ini di rak lain"><i class="fas fa-triangle-exclamation"></i>' + v + '</span>'
        : (v || '-');
}

function kolomDaftar(aksi) {
    return [
        { data: 'no_barcode', className: 'so-k-judul', render: kolomBarcode },
        { data: 'id_item' },
        { data: 'id_jo' },
        { data: 'itemdesc', className: 'so-k-penuh', render: kolomDesc },
        { data: 'qty', className: 'so-kanan', render: kolomQty, searchable: false },
        { data: 'qty_scan', className: 'so-kanan', render: kolomQtyScan, searchable: false },
        { data: 'unit', searchable: false },
        { data: 'lokasi_scan', searchable: false },
        { data: 'lokasi_aktual', render: kolomRakSaldo },
        { data: 'no_barcode', className: 'so-k-aksi so-tengah', searchable: false, render: aksi }
    ];
}

let datatable = $("#datatable").DataTable(opsiTabel('datatable', 'Belum ada barcode di-scan. Scan barcode di kotak atas.', {
    ajax: {
        url: '{{ route('list-scan-barcode-so') }}',
        dataType: 'json',
        dataSrc: 'data',
        data: function(d) {
            d.kode_lok = lokasiScan();
            d.no_transaksi = noTransaksi();
        },
    },
    columns: kolomDaftar(function (data, type) {
        if (type !== 'display') { return data; }
        return '<div class="so-aksi">'
            + '<button type="button" class="btn so-ikon so-ikon-ubah" data-aksi="ubah" title="Ubah qty"><i class="fa-solid fa-pen-to-square"></i></button>'
            + '<button type="button" class="btn so-ikon so-ikon-hapus" data-aksi="hapus" title="Hapus dari daftar"><i class="fa-solid fa-trash"></i></button>'
            + '</div>';
    })
}));

let datatable4 = $("#datatable4").DataTable(opsiTabel('datatable4', 'Tidak ada scan yang dibatalkan.', {
    ajax: {
        url: '{{ route('list-scan-barcode-so-cancel') }}',
        dataType: 'json',
        dataSrc: 'data',
        data: function(d) {
            d.kode_lok = lokasiScan();
            d.no_transaksi = noTransaksi();
        },
    },
    columns: kolomDaftar(function (data, type) {
        if (type !== 'display') { return data; }
        return '<div class="so-aksi">'
            + '<button type="button" class="btn so-ikon so-ikon-undo" data-aksi="undo" title="Kembalikan ke daftar scan"><i class="fa-solid fa-rotate-left"></i></button>'
            + '</div>';
    })
}));

let datatable2 = $("#datatable2").DataTable(opsiTabel('datatable2', 'Belum ada scan tersimpan untuk rak ini.', {
    ajax: {
        url: '{{ route('list-scan-barcode-so2') }}',
        dataType: 'json',
        dataSrc: 'data',
        data: function(d) {
            d.kode_lok = lokasiScan();
            d.txt_no_dokumen = $('#txt_no_dokumen').val();
        },
    },
    columns: [
        { data: 'lokasi_aktual' },
        { data: 'no_barcode', className: 'so-k-judul', render: kolomBarcode },
        { data: 'id_item' },
        { data: 'itemdesc', className: 'so-k-penuh', render: kolomDesc },
        { data: 'qty', className: 'so-kanan', render: kolomQty, searchable: false }
    ]
}));

let datatable3 = $("#datatable3").DataTable(opsiTabel('datatable3', 'Semua roll di rak ini sudah tersimpan.', {
    ajax: {
        url: '{{ route('list-scan-barcode-so3') }}',
        dataType: 'json',
        dataSrc: 'data',
        data: function(d) {
            d.kode_lok = lokasiScan();
            d.txt_no_dokumen = $('#txt_no_dokumen').val();
        },
    },
    columns: [
        {
            data: 'no_barcode', className: 'so-k-judul',
            render: function (v, type, row) {
                if (type !== 'display') { return v; }
                return kolomBarcode(v, type) + (row.barcode_temp != null ? ' <span class="so-pil is-temp"><span class="so-pil-titik"></span>Di-scan</span>' : '');
            }
        },
        { data: 'id_item' },
        { data: 'itemdesc', className: 'so-k-penuh', render: kolomDesc },
        { data: 'qty', className: 'so-kanan', render: kolomQty, searchable: false }
    ],
    rowCallback: function (row, data) {
        $(row).toggleClass('is-temp', data['barcode_temp'] != null);
    }
}));

// Jumlah di tab & kotak angka
datatable.on('xhr.dt', function (e, s, json) { $('#so-tab-n-scan').text(json ? json.recordsTotal : 0); });
datatable4.on('xhr.dt', function (e, s, json) { $('#so-tab-n-batal').text(json ? json.recordsTotal : 0); });
datatable2.on('xhr.dt', function (e, s, json) { $('#so-tab-n-tersimpan').text(json ? json.recordsTotal : 0); });
datatable3.on('xhr.dt', function (e, s, json) {
    var n = json ? json.recordsTotal : 0;
    $('#so-tab-n-outstanding').text(n);
    $('#so-n-outstanding').text(angka(n, 0));
});

// Tombol di baris: data diambil dari baris DataTables (tidak lewat string onclick)
$('#datatable').on('click', 'button[data-aksi]', function () {
    var row = datatable.row($(this).closest('tr')).data() || {};
    if ($(this).data('aksi') === 'ubah') { editdata(row.no_barcode, row.qty_scan, row.qty_old); }
    if ($(this).data('aksi') === 'hapus') { delete_scan(row.no_barcode); }
});
$('#datatable4').on('click', 'button[data-aksi="undo"]', function () {
    var row = datatable4.row($(this).closest('tr')).data() || {};
    undo_scan(row.no_barcode);
});

// Tab
$('.so-tab').on('click', '.so-tab-btn', function () {
    var tab = $(this).data('tab');
    $('.so-tab-btn').removeClass('is-aktif').attr('aria-selected', 'false');
    $(this).addClass('is-aktif').attr('aria-selected', 'true');
    $('.so-tab-panel').prop('hidden', true);
    $('#so-panel-' + tab).prop('hidden', false);
});

function dataTableReload() {
    datatable.ajax.reload();
    return $.ajax({
        url: '{{ route('get-sum-barcodeso') }}',
        type: 'get',
        data: {
            kode_lok: lokasiScan(),
            no_transaksi: noTransaksi(),
        },
        dataType: 'json',
        success: function(res) {
            if (res) {
                var n = parseInt(res.roll, 10) || 0;
                $('#txt_qty_scan').val(angka(res.qty, 2));
                $('#so-n-scan').text(angka(n, 0));
                $('#so-bar-n').text(angka(n, 0)).attr('data-n', n);
                $('#so-bar-qty').text(angka(res.qty, 2));
            }
        },
    });
}

function delete_scan($no_barcode){
    let no_barcode = $no_barcode;
    return $.ajax({
        url: '{{ route("delete-so-temp") }}',
        type: 'get',
        data: {
            no_barcode: no_barcode,
        },
        success: function (res) {
            umpan('warn', no_barcode, 'dihapus dari daftar scan (bisa dikembalikan di tab Dibatalkan)');
            muatUlangDaftar();
        },
        error: function (xhr) {
            Swal.fire({ icon: 'error', title: 'Gagal menghapus', text: pesanXhr(xhr, 'Coba lagi.') });
        }
    });
}

function delete_scan_all(){
    let n = parseInt($('#so-bar-n').attr('data-n'), 10) || 0;
    if (n <= 0) {
        Swal.fire({ icon: 'info', title: 'Daftar scan kosong', text: 'Tidak ada barcode untuk dihapus.' });
        return;
    }
    Swal.fire({
        icon: 'warning',
        title: 'Hapus semua scan?',
        html: '<b>' + n + '</b> barcode di daftar scan rak ini dipindah ke tab Dibatalkan (masih bisa dikembalikan).',
        showCancelButton: true,
        confirmButtonText: '<i class="fas fa-trash"></i> Ya, hapus semua',
        confirmButtonColor: '#b91c1c',
        cancelButtonText: 'Batal',
        reverseButtons: true,
        focusCancel: true
    }).then(function (r) {
        if (!r.isConfirmed) { return; }
        $.ajax({
            url: '{{ route("delete-so-temp-all") }}',
            type: 'get',
            data: {
                lokasi_h: lokasiScan(),
            },
            success: function (res) {
                muatUlangDaftar();
            },
            error: function (xhr) {
                Swal.fire({ icon: 'error', title: 'Gagal menghapus', text: pesanXhr(xhr, 'Coba lagi.') });
            }
        });
    });
}

function undo_scan($no_barcode){
    let no_barcode = $no_barcode;
    return $.ajax({
        url: '{{ route("undo-so-temp") }}',
        type: 'get',
        data: {
            no_barcode: no_barcode,
        },
        success: function (res) {
            umpan('ok', no_barcode, 'dikembalikan ke daftar scan');
            muatUlangDaftar();
        },
        error: function (xhr) {
            Swal.fire({ icon: 'error', title: 'Gagal mengembalikan', text: pesanXhr(xhr, 'Coba lagi.') });
        }
    });
}

function undo_scan_all(){
    return $.ajax({
        url: '{{ route("undo-so-temp-all") }}',
        type: 'get',
        data: {
            lokasi_h: lokasiScan(),
        },
        success: function (res) {
            muatUlangDaftar();
        },
        error: function (xhr) {
            Swal.fire({ icon: 'error', title: 'Gagal mengembalikan', text: pesanXhr(xhr, 'Coba lagi.') });
        }
    });
}

function editdata($no_barcode,$qty_scan,$qty_old){
    let no_barcode  = $no_barcode;
    let qty_scan  = $qty_scan;
    let qty_old  = $qty_old;
    if (qty_old > 0) {
        Swal.fire({
            icon: 'info',
            title: 'Peringatan!!',
            text: 'Data sudah pernah diedit',
            showCancelButton: false,
            showConfirmButton: true,
            confirmButtonText: 'Oke',
        });
    } else {
        $('#mdl_barcode').val(no_barcode);
        $('#mdl_qty').val(qty_scan);
        $('#mdl_qty_old').val(qty_scan);
        $('#modal-edit-so').modal('show');
    }
}

$('#modal-edit-so').on('shown.bs.modal', function () { $('#mdl_qty').trigger('focus').trigger('select'); });
$('#modal-edit-so').on('hidden.bs.modal', function () { $('#txt_barcode').focus(); });

$( document ).ready(function() {
    dataTableReload();
});
</script>
<script type="text/javascript">
function saveeditso() {
    var barcode = document.getElementById('mdl_barcode').value;
    var qty = document.getElementById('mdl_qty').value;
    var qty_old = document.getElementById('mdl_qty_old').value;
    var $tb = $('#mdl_btn_simpan').prop('disabled', true);

    return $.ajax({
        url: '{{ route('simpan-edit-barcode-so') }}',
        type: 'GET',
        dataType: 'json',
        data: {
            barcode: barcode,
            qty: qty,
            qty_old: qty_old,
        },
        success: function(res) {
            if (res && res.status == 200) {
                $('#modal-edit-so').modal('hide');
                umpan('ok', barcode, 'qty diubah jadi ' + qty);
                dataTableReload();
            }
        },
        error: function (xhr) {
            Swal.fire({ icon: 'error', title: 'Qty tidak diubah', text: pesanXhr(xhr, 'Coba lagi.') });
        },
        complete: function () {
            $tb.prop('disabled', false);
        }
    });
}
</script>
@endsection
