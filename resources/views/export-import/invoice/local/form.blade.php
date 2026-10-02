{{-- ===========================================================================
     Create & Edit Invoice Local (EXIM).

     Satu layar dipakai dua mode. Saat $ubah terisi (dikirim editLocal()),
     isinya sudah terisi dan yang ditekan adalah Update, bukan Save. Nomor
     invoice & profit center dikunci saat Edit karena keduanya sudah menyatu
     di nomor (0211/L/NAG/0926).
     Susunan formnya mengikuti menu Create Invoice di aplikasi AR
     (application/views/arnag/createinvoice.php), dengan dua perbedaan:

       1. Nomor invoice DIBANGKITKAN, bukan diambil dari Booking Invoice -
          jadi tidak ada tombol "Add Book Inv". Akibatnya Customer, Shipp,
          dan Document Type/Number yang di AR ikut terisi dari booking,
          di sini harus diisi sendiri.
       2. Tampilannya memakai skin Debit Note (_skin.blade.php).
     =========================================================================== --}}
@extends('layouts.index')

@section('custom-link')
<link rel="stylesheet" href="{{ asset('plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
<link rel="stylesheet" href="{{ asset('plugins/select2/css/select2.min.css') }}">
<link rel="stylesheet" href="{{ asset('plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}">
@include('export-import.invoice._skin')
@endsection

@section('content')
@php
    /* Hanya terisi saat mode Edit; saat Create semuanya kosong. */
    $ubah      = $ubah      ?? null;
    $ubahBaris = $ubahBaris ?? [];
    $ubahBarisSo = $ubahBarisSo ?? [];
    $ubahPot   = $ubahPot   ?? ['dp' => 0, 'dp_cbd' => 0, 'retur' => 0, 'vat_persen' => 0];
@endphp
{{-- dn-kontrol-sm: tombol, isian & select2 ukuran kecil - sama di semua halaman invoice (lihat _skin). --}}
<div class="nag-skin dn-kontrol-sm dn-form-invoice">

    {{-- ===== Kepala halaman =====
         Judul layar saja - Save & Back tetap di kaki kartu Summary. Bentuknya
         sama dengan Create/Edit Invoice Export. --}}
    <div class="dn-kepala-halaman">
        <div class="dn-kepala-kiri">
            <span class="dn-kepala-ikon"><i class="fas fa-file-invoice"></i></span>
            <div>
                <h1 class="dn-kepala-judul">{{ $ubah ? 'Edit Invoice Local' : 'Create Invoice Local' }}</h1>
                <p class="dn-kepala-sub">
                    {{ $ubah ? 'Ubah booking invoice local yang masih DRAFT' : 'Booking invoice local EXIM' }}
                </p>
            </div>
        </div>
    </div>

    <div class="row">
        {{-- ===== Kiri: identitas invoice ===== --}}
        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="card-title"><i class="fas fa-file-invoice"></i> Invoice Information</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="inv-no">Invoice Number</label>
                                <input type="text" class="form-control" id="inv-no" name="no_invoice"
                                    value="{{ $noInvoice }}" readonly>
                            </div>
                        </div>
                        {{-- Invoice Date duduk bareng nomor & profit center: ketiganya
                             identitas dokumennya, bukan keterangan dokumen sumber. --}}
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="inv-tgl">Invoice Date <span class="dn-wajib" title="Required">*</span></label>
                                {{-- Tanggal dokumen - ini yang tampil di daftar, modal & cetakan.
                                     Tidak diketik: ikut tanggal SJ yang dipilih (satu invoice =
                                     satu tanggal SJ), jadi kotaknya readonly. --}}
                                <input type="text" class="form-control dn-tgl dn-tgl-ikut" id="inv-tgl" name="tgl_invoice"
                                    value="{{ $ubah && !empty($ubahPot['tgl_invoice'])
                                        ? date('j M Y', strtotime($ubahPot['tgl_invoice']))
                                        : now()->format('j M Y') }}" autocomplete="off" readonly
                                    title="Follows the SJ date">
                                <small class="dn-tgl-ket"><i class="fas fa-truck"></i> Follows the SJ date</small>
                            </div>
                        </div>
                        <div class="col-md-5">
                            <div class="form-group">
                                <label for="inv-pc">Profit Center</label>
                                {{-- Yang disimpan tetap kodenya (NAG/NAK, ikut ke nomor invoice),
                                     yang ditampilkan nama lengkapnya. --}}
                                {{-- Saat Edit dikunci: profit center sudah ikut tercetak di
                                     nomor invoice, jadi tidak bisa diganti tanpa ganti nomor. --}}
                                <select class="form-control select2bs4" id="inv-pc" name="profit_center"
                                    {{ $ubah ? 'disabled' : '' }}>
                                    @foreach ($profitCenter as $pc)
                                        <option value="{{ $pc->kode_pc }}"
                                            {{ $ubah && $ubah['profit_center'] === $pc->kode_pc ? 'selected' : '' }}>
                                            {{ $pc->nama_pc }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="inv-customer">Billed To <span class="dn-wajib" title="Required">*</span></label>
                        <select class="form-control select2bs4" id="inv-customer" name="id_customer">
                            <option value="">-- Select customer --</option>
                            @foreach ($customer as $c)
                                <option value="{{ $c->Id_Supplier }}"
                                    {{ $ubah && (string) $ubah['id_customer'] === (string) $c->Id_Supplier ? 'selected' : '' }}>
                                    {{ $c->Supplier }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="inv-customer-ship">Shipped To <span class="dn-wajib" title="Required">*</span></label>
                        <select class="form-control select2bs4" id="inv-customer-ship" name="id_customer_ship">
                            <option value="">-- Select shipped to --</option>
                            @foreach ($customer as $c)
                                <option value="{{ $c->Id_Supplier }}"
                                    {{ $ubah && (string) $ubah['id_customer_ship'] === (string) $c->Id_Supplier ? 'selected' : '' }}>
                                    {{ $c->Supplier }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- TOP Type, Time Period, Amount, Bank, PPh & Type SO sengaja tidak
                         ada di form ini - semuanya diisi nanti saat Create Invoice di
                         aplikasi AR. --}}
                </div>
            </div>
        </div>

        {{-- ===== Kanan: dokumen & referensi ===== --}}
        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="card-title"><i class="fas fa-file-signature"></i> Document &amp; Reference</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="inv-type">Type</label>
                                <select class="form-control select2bs4" id="inv-type" name="id_type">
                                    @foreach ($tipe as $t)
                                        <option value="{{ $t->id }}"
                                            {{ $ubah && (int) $ubah['id_type'] === (int) $t->id ? 'selected' : '' }}>
                                            {{ $t->type }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="inv-shipp">Shipp</label>
                                {{-- Selalu Local: nomor invoice memakai kode L. --}}
                                <input type="text" class="form-control" id="inv-shipp" name="shipp"
                                    value="{{ $shipp }}" readonly>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="inv-doc-type">Document Type <span class="dn-wajib" title="Required">*</span></label>
                                <select class="form-control select2bs4" id="inv-doc-type" name="doc_type">
                                    @foreach ($docType as $dt)
                                        <option value="{{ $dt }}"
                                            {{ $ubah && $ubah['doc_type'] === $dt ? 'selected' : '' }}>{{ $dt }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="inv-doc-number">Document Number</label>
                                <input type="text" class="form-control" id="inv-doc-number" name="doc_number"
                                    value="{{ $ubah['doc_number'] ?? '' }}">
                            </div>
                        </div>
                    </div>

                    <div class="form-group mb-0">
                        <label for="inv-so">SO Number</label>
                        {{-- Terisi otomatis dari SJ yang dipilih - di sini SJ dipilih
                             langsung, tidak lewat tahap pilih SO seperti di AR. --}}
                        <div class="input-group">
                            <input type="text" class="form-control" id="inv-so" name="so_number" readonly>
                            {{-- Satu tombol, dua sumber: SJ (barangnya sudah keluar) atau
                                 WS (SJ-nya belum terbit). Ditanyakan waktu diklik. --}}
                            <button type="button" class="btn btn-dn-create" id="inv-btn-so">
                                <i class="fas fa-plus"></i> Add SJ / WS
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ===== Detail SO (WS) =====
         Dipakai kalau SJ-nya belum terbit: barangnya belum keluar, tapi nomor
         invoice sudah diminta. Satu baris = satu WS + satu warna; qty-nya
         boleh dikurangi karena pengiriman sering bertahap.

         Letaknya di atas Detail SJ: ini yang lebih dulu ada, SJ-nya menyusul. --}}
    <div class="card">
        <div class="card-header dn-kepala-aksi">
            <h5 class="card-title"><i class="fas fa-clipboard-list"></i> Detail SO</h5>
            <span class="dn-lipat-ringkas" id="so-ringkas">
                <span class="dn-lipat-kosong">No WS selected yet.</span>
            </span>
            {{-- Tertutup dulu: yang perlu dilihat sehari-hari cuma ringkasannya.
                 Tabelnya panjang dan mendorong Summary jauh ke bawah layar. --}}
            <button type="button" class="btn btn-dn-lipat" id="btn-lipat-so"
                aria-expanded="false" aria-controls="so-isi" hidden>
                <span id="btn-lipat-so-teks">Show details</span>
                <i class="fas fa-chevron-down dn-lipat-panah"></i>
            </button>
        </div>
        <div class="card-body" id="so-isi" hidden>
            <div class="dn-table-scroll dn-table-tinggi">
                <table id="inv-table-so" class="dn-table text-nowrap" style="width:100%">
                    <thead>
                        <tr>
                            <th>WS#</th>
                            <th>SO Number</th>
                            <th>SO Date</th>
                            <th>Style No</th>
                            <th>Product Item</th>
                            <th>Color</th>
                            <th>Curr</th>
                            <th>UOM</th>
                            <th class="dn-angka">Qty SO</th>
                            <th class="dn-angka">Qty</th>
                            <th class="dn-angka">Unit Price</th>
                            <th class="dn-angka">Discount (%)</th>
                            <th class="dn-angka">Total Price</th>
                            <th style="width:52px" class="dn-tengah">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="dn-kosong" colspan="14">No WS selected yet. Use &ldquo;Add SJ / WS&rdquo; to pick one.</td>
                        </tr>
                    </tbody>
                    <tfoot class="dn-sj-jumlah" id="inv-so-jumlah" hidden>
                        <tr>
                            <td colspan="4" id="inv-so-jumlah-ws"></td>
                            <td colspan="5" class="dn-angka">Total Qty</td>
                            <td class="dn-angka dn-sj-jumlah-qty" id="inv-so-jumlah-qty">0.00</td>
                            <td colspan="4"></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
    {{-- ===== Detail SJ ===== --}}
    <div class="card">
        <div class="card-header">
            <h5 class="card-title"><i class="fas fa-table"></i> Detail SJ</h5>
        </div>
        <div class="card-body">
            {{-- Tingginya dibatasi: sekali invoice berisi puluhan baris, Summary
                 dan tombol Save terdorong jauh ke bawah layar. --}}
            <div class="dn-table-scroll dn-table-tinggi">
                <table id="inv-table-sj" class="dn-table text-nowrap" style="width:100%">
                    <thead>
                        {{-- ID Bppb tidak ditampilkan: id internal database, bukan
                             data yang dipakai user. --}}
                        <tr>
                            <th>SO Number</th>
                            <th>Bppb Number</th>
                            <th>SJ Date</th>
                            <th>Shipping Number</th>
                            <th>WS#</th>
                            <th>Style No</th>
                            <th>Product Group</th>
                            <th>Product Item</th>
                            <th>Color</th>
                            <th>Size</th>
                            <th>Curr</th>
                            <th>UOM</th>
                            <th>Qty</th>
                            <th>Unit Price</th>
                            <th>Discount (%)</th>
                            <th>Total Price</th>
                            {{-- Kolom buang: tidak ada di AR (di sana barisnya lewat tabel
                                 temporary), tapi di sini perlu supaya salah pilih bisa
                                 dibatalkan tanpa memuat ulang halaman. --}}
                            <th style="width:52px" class="dn-tengah">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="dn-kosong" colspan="17">No SJ selected yet. Use &ldquo;Add SJ&rdquo; to pick one.</td>
                        </tr>
                    </tbody>
                    {{-- Jumlah SJ & Total Qty - Qty-nya persis di bawah kolom Qty. --}}
                    <tfoot class="dn-sj-jumlah" id="inv-sj-jumlah" hidden>
                        <tr>
                            <td colspan="4" id="inv-sj-jumlah-sj"></td>
                            <td colspan="8" class="dn-angka">Total Qty</td>
                            <td class="dn-angka dn-sj-jumlah-qty" id="inv-sj-jumlah-qty">0.00</td>
                            <td colspan="4"></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

    {{-- ===== Ringkasan nilai ===== --}}
    <div class="row justify-content-end">
        <div class="col-lg-5 col-md-8">
            <div class="card">
                {{-- Yang selalu terlihat cuma Grand Total; rinciannya dibuka kalau
                     perlu - sama dengan kartu Summary di Invoice Export & modal. --}}
                <div class="card-header dn-kepala-aksi">
                    <h5 class="card-title"><i class="fas fa-calculator"></i> Summary</h5>
                    <button type="button" class="btn btn-dn-lipat" id="btn-lipat-rekap"
                        aria-expanded="false" aria-controls="inv-rekap-rinci">
                        <span id="btn-lipat-rekap-teks">Show details</span>
                        <i class="fas fa-chevron-down dn-lipat-panah"></i>
                    </button>
                </div>
                <div class="card-body dn-ringkas">
                    <div id="inv-rekap-rinci" hidden>
                    <div class="form-group row">
                        <label for="inv-total" class="col-sm-5 col-form-label">Total</label>
                        <div class="col-sm-7">
                            <input type="text" class="form-control" id="inv-total" name="total" placeholder="0.00" readonly>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label for="inv-discount" class="col-sm-5 col-form-label">Discount</label>
                        <div class="col-sm-7">
                            <input type="text" class="form-control" id="inv-discount" name="discount" placeholder="0.00" readonly>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label for="inv-dp" class="col-sm-5 col-form-label">Down Payment</label>
                        <div class="col-sm-7">
                            <input type="text" class="form-control" id="inv-dp" name="dp" placeholder="0.00" readonly>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label for="inv-return" class="col-sm-5 col-form-label">Return</label>
                        <div class="col-sm-7">
                            <input type="text" class="form-control" id="inv-return" name="return" placeholder="0.00" readonly>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label for="inv-twot" class="col-sm-5 col-form-label">Total Without Tax</label>
                        <div class="col-sm-7">
                            <input type="text" class="form-control" id="inv-twot" name="twot" placeholder="0.00" readonly>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label for="inv-vat" class="col-sm-5 col-form-label">VAT</label>
                        <div class="col-sm-7">
                            <input type="text" class="form-control" id="inv-vat" name="vat" placeholder="0.00" readonly>
                        </div>
                    </div>
                    </div>{{-- /rincian --}}

                    <div class="form-group row dn-grand">
                        <label for="inv-grandtotal" class="col-sm-5 col-form-label">Grand Total</label>
                        <div class="col-sm-7">
                            <input type="text" class="form-control" id="inv-grandtotal" name="grandtotal" placeholder="0.00" readonly>
                        </div>
                    </div>

                    {{-- Aksi ditaruh tepat di bawah Grand Total: itu angka terakhir
                         yang dilihat sebelum memutuskan simpan. --}}
                    <div class="dn-kaki">
                        <button type="button" class="btn btn-dn-simpan" id="inv-btn-simpan">
                            <i class="fa fa-save"></i> {{ $ubah ? 'Update Invoice' : 'Save Invoice' }}
                        </button>
                        <a href="{{ route('invoice-exim-local') }}" class="btn btn-dn-kembali">
                            <i class="fa fa-arrow-left"></i> Back
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ===================================================================
         Modal Add SO - disalin dari modal "Add Number SO" di aplikasi AR
         (createinvoice.php), disesuaikan ke Bootstrap 5 & skin Debit Note.
         =================================================================== --}}
    {{-- ===================================================================
         Modal Add WS - dipakai kalau SJ-nya belum terbit. Satu baris = satu
         WS + satu warna; qty yang ditagih diketik di sini.
         =================================================================== --}}
    <div class="modal fade" id="modal-add-ws" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-clipboard-list"></i> Add WS</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body">
                    <div class="row">
                        <div class="col-lg-4 col-md-6">
                            <div class="form-group">
                                <label for="ws-buyer">Buyer</label>
                                <select class="form-control" id="ws-buyer">
                                    <option value="">ALL</option>
                                    @foreach ($buyer as $b)
                                        <option value="{{ $b->Id_Supplier }}">{{ $b->Supplier }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-auto dn-filter-tgl">
                            <div class="form-group">
                                <label for="ws-tgl-awal">SO Date From</label>
                                <input type="text" class="form-control dn-tgl" id="ws-tgl-awal"
                                    value="{{ now()->format('j M Y') }}" autocomplete="off">
                            </div>
                        </div>
                        <div class="col-auto dn-filter-tgl">
                            <div class="form-group">
                                <label for="ws-tgl-akhir">SO Date To</label>
                                <input type="text" class="form-control dn-tgl" id="ws-tgl-akhir"
                                    value="{{ now()->format('j M Y') }}" autocomplete="off">
                            </div>
                        </div>
                        <div class="col-auto">
                            <div class="form-group dn-aksi-filter">
                                <button type="button" class="btn btn-dn-search" id="ws-btn-cari">
                                    <i class="fa fa-search"></i> Search
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header dn-kepala-aksi">
                            <h5 class="card-title"><i class="fas fa-table"></i> WS List</h5>
                            <span class="dn-cari-kotak">
                                <i class="fas fa-search"></i>
                                <input type="text" id="ws-cari"
                                    placeholder="Search WS / SO / style / colour..." autocomplete="off"
                                    aria-label="Search any column in the WS list">
                            </span>
                        </div>
                        <div class="card-body">
                            <div class="dn-table-scroll dn-table-tinggi">
                                <table id="ws-table" class="dn-table text-nowrap" style="width:100%">
                                    <thead>
                                        <tr>
                                            <th>WS#</th>
                                            <th>SO Number</th>
                                            <th>SO Date</th>
                                            <th>Style No</th>
                                            <th>Product Item</th>
                                            <th>Color</th>
                                            <th>Curr</th>
                                            <th>UOM</th>
                                            <th class="dn-angka">Qty SO</th>
                                            <th class="dn-angka">Qty</th>
                                            <th class="dn-angka">Unit Price</th>
                                            <th class="dn-angka">Total Price</th>
                                            <th style="width:52px" class="dn-tengah">
                                                <input type="checkbox" id="ws-cek-semua" title="Select all">
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td class="dn-kosong" colspan="13">No WS yet. Set the filter above, then press Search.</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <div class="dn-pilih-info" id="ws-info-pilih">No WS ticked yet.</div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-dn-kembali" data-bs-dismiss="modal">
                        <i class="fas fa-times"></i> Close
                    </button>
                    <button type="button" class="btn btn-dn-simpan" id="ws-btn-apply">
                        <i class="fas fa-check"></i> Apply
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modal-add-so" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-plus"></i> Add SJ</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body">
                    {{-- ----- Filter ----- --}}
                    <div class="row">
                        <div class="col-lg-3 col-md-6">
                            <div class="form-group">
                                <label for="so-custm">Billed To</label>
                                <input type="text" class="form-control" id="so-custm" readonly>
                                <input type="hidden" id="so-id-custm">
                                <input type="hidden" id="so-profit-ctr">
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-6">
                            <div class="form-group">
                                <label for="so-buyer">Buyer</label>
                                <select class="form-control select2bs4-modal" id="so-buyer" name="buyer">
                                    <option value="">ALL</option>
                                    @foreach ($buyer as $byr)
                                        <option value="{{ $byr->Id_Supplier }}">{{ $byr->Supplier }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-lg-2 col-md-4">
                            <div class="form-group">
                                <label for="so-tgl-awal">SJ Date From</label>
                                {{-- Tampil dd/mm/yyyy; nilainya diambil lewat tglIso() - lihat _kalender. --}}
                                <input type="text" class="form-control dn-tgl" id="so-tgl-awal"
                                    value="{{ now()->format('j M Y') }}" autocomplete="off">
                            </div>
                        </div>
                        <div class="col-lg-2 col-md-4">
                            <div class="form-group">
                                <label for="so-tgl-akhir">SJ Date To</label>
                                {{-- Tampil dd/mm/yyyy; nilainya diambil lewat tglIso() - lihat _kalender. --}}
                                <input type="text" class="form-control dn-tgl" id="so-tgl-akhir"
                                    value="{{ now()->format('j M Y') }}" autocomplete="off">
                            </div>
                        </div>
                        <div class="col-lg-2 col-md-4">
                            <div class="form-group dn-aksi-filter">
                                <button type="button" class="btn btn-dn-search" id="so-btn-cari">
                                    <i class="fa fa-search"></i> Search
                                </button>
                            </div>
                        </div>
                    </div>

                    {{-- ----- Daftar SJ -----
                         Beda dengan AR: di sana pilih SO dulu baru SJ-nya muncul.
                         Di sini SJ langsung difilter & dipilih, tanpa tahap SO. --}}
                    <div class="card">
                        {{-- Kotak cari duduk di kepala kartu: kalau ditaruh di atas
                             tabel, barisnya sendirian dan terlihat seperti ruang kosong. --}}
                        <div class="card-header dn-kepala-aksi">
                            <h5 class="card-title"><i class="fas fa-table"></i> SJ List</h5>
                            <span class="dn-cari-kotak">
                                <i class="fas fa-search"></i>
                                <input type="text" id="so-cari-sj"
                                    placeholder="Search SJ / WS / SO / style..." autocomplete="off"
                                    aria-label="Search any column in the SJ list">
                            </span>
                        </div>
                        <div class="card-body">
                            <div class="dn-table-scroll dn-table-tinggi">
                                <table id="so-table-sj" class="dn-table text-nowrap" style="width:100%">
                                    <thead>
                                        {{-- Satu baris = satu FG/OUT, bukan satu warna/size.
                                             Rinciannya tetap utuh di belakang layar. --}}
                                        <tr>
                                            <th>Shipping Number</th>
                                            <th>SJ Date</th>
                                            <th>Bppb Number</th>
                                            <th>SO Number</th>
                                            <th>WS#</th>
                                            <th>Style No</th>
                                            <th>Product Item</th>
                                            <th>Colours</th>
                                            <th>Curr</th>
                                            <th>UOM</th>
                                            <th class="dn-angka">Rows</th>
                                            <th class="dn-angka">Total Qty</th>
                                            <th class="dn-angka">Discount (%)</th>
                                            <th class="dn-angka">Total Price</th>
                                            <th style="width:52px" class="dn-tengah">
                                                <input type="checkbox" id="so-cek-semua" title="Select all">
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td class="dn-kosong" colspan="15">No SJ yet. Set the filter above, then press Search.</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <!-- Satu invoice cuma boleh memuat SJ dari SATU tanggal - begitu
                                 ada yang dicentang, SJ bertanggal lain dikunci. -->
                            <div class="dn-catatan-tgl" id="so-tgl-info" hidden></div>
                            <div class="dn-pilih-info" id="so-info-pilih">No SJ ticked yet.</div>
                        </div>
                    </div>

                    {{-- ----- Ringkasan nilai di dalam modal ----- --}}
                    <div class="row justify-content-end">
                        {{-- Lebarnya disamakan dengan Invoice Summary di modal Add SJ
                             Invoice Export - isinya sama-sama satu kolom angka. --}}
                        <div class="col-xl-4 col-lg-5 col-md-8">
                            <div class="card">
                                <div class="card-header">
                                    {{-- Angkanya untuk SELURUH invoice, bukan cuma baris yang
                                         baru ditandai - supaya tidak rancu saat menambah
                                         baris ke invoice yang sudah berisi. --}}
                                    <h5 class="card-title"><i class="fas fa-calculator"></i> Invoice Summary</h5>
                                </div>
                                <div class="card-body dn-ringkas">
                                    <div class="form-group row">
                                        <label for="so-total" class="col-sm-5 col-form-label">Total</label>
                                        <div class="col-sm-7">
                                            <input type="text" class="form-control" id="so-total" placeholder="0.00" readonly>
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <label for="so-discount" class="col-sm-5 col-form-label">Discount</label>
                                        <div class="col-sm-7">
                                            <input type="text" class="form-control" id="so-discount" placeholder="0.00" readonly>
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <label for="so-dp" class="col-sm-5 col-form-label">Down Payment</label>
                                        <div class="col-sm-7">
                                            <input type="text" class="form-control dn-angka-input" id="so-dp" placeholder="0.00" autocomplete="off">
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <label for="so-dpcbd" class="col-sm-5 col-form-label">DP/CBD from Invoice</label>
                                        <div class="col-sm-7">
                                            <input type="text" class="form-control dn-angka-input" id="so-dpcbd" placeholder="0.00" autocomplete="off">
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <label for="so-return" class="col-sm-5 col-form-label">Return</label>
                                        <div class="col-sm-7">
                                            <input type="text" class="form-control dn-angka-input" id="so-return" placeholder="0.00" autocomplete="off">
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <label for="so-twot" class="col-sm-5 col-form-label">Total Without Tax</label>
                                        <div class="col-sm-7">
                                            <input type="text" class="form-control" id="so-twot" placeholder="0.00" readonly>
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <label class="col-sm-5 col-form-label">VAT</label>
                                        <div class="col-sm-7">
                                            {{-- Invoice lokal praktis selalu kena PPN, jadi 11% langsung
                                                 tercentang - masih bisa dilepas kalau memang tidak kena.
                                                 Pilihan 12% dihapus, tarif yang berlaku cuma 11%. --}}
                                            <div class="dn-vat">
                                                <div class="form-check form-check-inline">
                                                    <input class="form-check-input" type="checkbox" id="so-vat-11" checked>
                                                    <label class="form-check-label" for="so-vat-11">Vat 11%</label>
                                                </div>
                                            </div>
                                            <input type="text" class="form-control" id="so-vat" placeholder="0.00" readonly>
                                        </div>
                                    </div>
                                    <div class="form-group row dn-grand mb-0">
                                        <label for="so-grandtotal" class="col-sm-5 col-form-label">Grand Total</label>
                                        <div class="col-sm-7">
                                            <input type="text" class="form-control" id="so-grandtotal" placeholder="0.00" readonly>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-dn-kembali" data-bs-dismiss="modal">
                        <i class="fa fa-times"></i> Cancel
                    </button>
                    {{-- "Apply": baris yang dicentang dipakai ke Detail SJ,
                         bukan menambah data baru. --}}
                    <button type="button" class="btn btn-dn-simpan" id="so-btn-tambah">
                        <i class="fa fa-check"></i> Apply
                    </button>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script src="{{ asset('plugins/select2/js/select2.full.min.js') }}"></script>
<script>
$(function () {
    // Menggulir halaman di atas kotak angka yang sedang aktif ikut
    // mengubah angkanya - perilaku bawaan browser. Kotaknya dilepas dari
    // fokus begitu digulir, jadi nilainya hanya berubah kalau diketik.
    document.addEventListener('wheel', function (e) {
        var el = document.activeElement;
        if (el && el.type === 'number' && el === e.target) { el.blur(); }
    }, { passive: true });

    var RUT_NOMOR = @json(route('invoice-exim-nomor'));

    // Penanda mode. MODE_UBAH true berarti layar ini sedang mengubah invoice
    // yang sudah tersimpan, bukan membuat yang baru.
    var MODE_UBAH = @json((bool) $ubah);
    var ID_UBAH   = @json($ubah['id'] ?? null);

    // ================= Baris SJ yang sudah dipilih =================
    // Ditahan di halaman ini saja (tidak lewat tabel temporary seperti AR).
    // Alasannya: tbl_invoice_detail_temp di AR dihapus dengan DELETE tanpa WHERE,
    // jadi dua user yang membuat invoice bersamaan bisa saling menghapus baris.
    // Semua baris baru dikirim ke server sekali saja waktu Save.
    // Dideklarasikan di awal karena ringkasan di modal Add SJ ikut membacanya.
    var invBaris = [];

    $('.select2bs4').select2({ theme: 'bootstrap4', width: '100%' });

    // ---------- tanggal ----------
    @include('export-import.invoice._kalender')

    // Nomor invoice ikut profit center yang dipilih (0210/L/NAG/0926).
    // Saat Edit tidak berlaku: nomornya sudah terpakai dan tidak boleh berubah.
    $('#inv-pc').on('change', function () {
        if (MODE_UBAH) { return; }
        $.getJSON(RUT_NOMOR, { profit_center: $(this).val() }, function (res) {
            if (res && res.no_invoice) { $('#inv-no').val(res.no_invoice); }
        });
    });

    // Shipped To kosong = sama dengan Billed To.
    $('#inv-customer').on('change', function () {
        if (!$('#inv-customer-ship').val()) {
            $('#inv-customer-ship').val($(this).val()).trigger('change.select2');
        }
    });

    // ---- Modal Add SJ ----
    var modalSJ = new bootstrap.Modal(document.getElementById('modal-add-so'));

    /**
     * Samakan rentang tanggal pencarian dengan SJ yang sudah dipilih.
     * Awalnya mundur ke tanggal SJ paling lama; akhirnya hanya dimajukan kalau
     * ternyata ada SJ yang lebih baru dari isian sekarang - jadi rentang yang
     * sudah diperlebar user sendiri tidak dipersempit diam-diam.
     */
    function rentangIkutBaris() {
        if (!invBaris.length) { return; }

        var paling = null, terbaru = null;
        invBaris.forEach(function (r) {
            var t = String(r.bppbdate || '').slice(0, 10);
            if (!/^\d{4}-\d{2}-\d{2}$/.test(t)) { return; }
            if (paling === null || t < paling)  { paling = t; }
            if (terbaru === null || t > terbaru) { terbaru = t; }
        });
        if (paling === null) { return; }

        tglSet('#so-tgl-awal', paling);
        if (!tglIso('#so-tgl-akhir') || tglIso('#so-tgl-akhir') < terbaru) {
            tglSet('#so-tgl-akhir', terbaru);
        }
    }

    /**
     * Siapkan & buka modal Add SJ.
     *
     * Dipisah dari tombolnya karena tombol itu sekarang menawarkan dua sumber -
     * kalau persiapannya ikut di dalam handler, memilih "By SJ" membuka modal
     * yang Billed To & Buyer-nya masih kosong.
     */
    function siapkanModalSj() {
        // Billed To & profit center dibawa ke modal, sama seperti add_id_for_so() di AR.
        var terpilih = $('#inv-customer').find(':selected');
        var idCust = $('#inv-customer').val() || '';
        $('#so-custm').val(terpilih.val() ? $.trim(terpilih.text()) : '');
        $('#so-id-custm').val(idCust);
        $('#so-profit-ctr').val($('#inv-pc').val());

        // Buyer disamakan dulu dengan Billed To - itu yang paling sering dicari.
        // Kalau customer-nya tidak ada di daftar buyer, biarkan ALL.
        var adaDiBuyer = idCust !== '' && $('#so-buyer option[value="' + idCust + '"]').length > 0;
        $('#so-buyer').val(adaDiBuyer ? idCust : '').trigger('change.select2');

        // Rentang tanggal dibuat melingkupi SJ yang sudah dipilih, kalau tidak
        // baris-baris itu tidak akan ikut muncul dan mustahil terlihat tercentang.
        rentangIkutBaris();

        hitungRingkasan();
        modalSJ.show();
    }

    $('#inv-btn-so').on('click', function () {
        pilihSumberBaris();
    });

    // select2 di dalam modal harus dilampirkan ke modal-nya, kalau tidak
    // dropdown-nya muncul di belakang lapisan modal.
    $('#modal-add-so').on('shown.bs.modal', function () {
        var $s = $('#so-buyer');
        if (!$s.hasClass('select2-hidden-accessible')) {
            $s.select2({ theme: 'bootstrap4', width: '100%', dropdownParent: $('#modal-add-so') });
        }

        // Kalau invoice sudah berisi, pencarian langsung dijalankan supaya
        // baris yang sudah dipilih terlihat tercentang begitu modal terbuka -
        // tidak perlu menekan Search dulu untuk tahu apa yang sudah diambil.
        if (invBaris.length) { $('#so-btn-cari').trigger('click'); }
    });

    // ---- Cari SJ ----
    var RUT_SJ = @json(route('invoice-exim-sj'));
    var RUT_WS = @json(route('invoice-exim-ws'));
    var sjTerakhir = [];

    function teksAman(v) {
        if (v === null || v === undefined || v === '') { return '-'; }
        return $('<div>').text(v).html();
    }
    function angka(v) {
        var n = parseFloat(v);
        if (isNaN(n)) { return teksAman(v); }
        return n.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }


    /**
     * Cocokkan satu baris dengan kata yang diketik di kotak cari.
     *
     * Semua kolom yang kelihatan di tabel ikut dicari - dulu cuma nomor SJ,
     * shipping number & SO, jadi mengetik nomor WS tidak menghasilkan apa-apa
     * padahal kolomnya ada di depan mata.
     */
    var KOLOM_CARI = ['sj', 'shipping_number', 'no_so', 'ws', 'styleno', 'product_group',
        'product_item', 'color', 'size', 'curr', 'uom', 'bppbdate', 'so_date', 'grade'];

    function cocokCari(r, q) {
        if (q === '') { return true; }
        for (var i = 0; i < KOLOM_CARI.length; i++) {
            var v = r[KOLOM_CARI[i]];
            if (v === null || v === undefined) { continue; }
            if (String(v).toLowerCase().indexOf(q) > -1) { return true; }
        }
        return false;
    }

    // ================= Baris SO (WS) =================
    // Sumber baris kedua, di samping SJ. Dipakai kalau SJ-nya belum terbit:
    // barangnya belum keluar, tapi nomor invoice sudah diminta. Bentuk
    // barisnya sama dengan baris SJ (lihat wsGarment di InvoiceEximController),
    // jadi rumus uangnya tidak perlu dibedakan.
    var invSo = [];

    /** Nama warna sebuah baris - dipakai memasangkan baris SO dengan SJ-nya. */
    function kunciWarnaSo(r) {
        var w = $.trim(String(r.color == null ? '' : r.color));
        if (w !== '' && w !== '-') { return w; }
        var nama = $.trim(String(r.product_item == null ? '' : r.product_item));
        return nama !== '' ? nama : '-';
    }

    /**
     * Baris yang benar-benar ditagih.
     *
     * Aturannya PER WARNA: memilih SJ ikut mengisi Detail SO, jadi kalau
     * keduanya dijumlahkan warna itu terhitung dua kali. Warna yang punya
     * baris SO memakai baris SO; warna tanpa baris SO tetap memakai baris SJ
     * (SJ yang tidak punya SO, mis. GK/GEN/WIP).
     */
    function barisDitagih() {
        var adaSo = {};
        invSo.forEach(function (r) { adaSo[kunciWarnaSo(r)] = true; });
        return invSo.concat(invBaris.filter(function (r) { return !adaSo[kunciWarnaSo(r)]; }));
    }

    /** Qty SJ yang warnanya sama dengan baris SO ini, dalam WS yang sama. */
    function qtySjWarna(so) {
        var ws = $.trim(String(so.ws == null ? '' : so.ws));
        var w = kunciWarnaSo(so);
        var qty = 0;
        invBaris.forEach(function (r) {
            if ($.trim(String(r.ws == null ? '' : r.ws)) !== ws) { return; }
            if (kunciWarnaSo(r) !== w) { return; }
            qty += parseFloat(r.qty) || 0;
        });
        return qty;
    }

    function hitungWs(baris) {
        var ws = {};
        baris.forEach(function (r) {
            var k = $.trim(String(r.ws == null ? '' : r.ws));
            if (k !== '') { ws[k] = true; }
        });
        return Object.keys(ws).length;
    }

    /** Angka yang diketik user - koma ribuan diabaikan. */
    function nilaiAngka(v) {
        var n = parseFloat(String(v === null || v === undefined ? '' : v).replace(/,/g, ''));
        return isNaN(n) || n < 0 ? 0 : n;
    }

    function angka4(v) {
        var n = parseFloat(v);
        return isNaN(n) ? '0.0000' : n.toLocaleString('en-US', { minimumFractionDigits: 4, maximumFractionDigits: 4 });
    }

    function ringkasDetailSo() {
        $('#btn-lipat-so').prop('hidden', !invSo.length);
        if (!invSo.length) {
            $('#so-ringkas').html('<span class="dn-lipat-kosong">No WS selected yet.</span>');
            // Tidak ada isinya - tutup lagi supaya tombolnya konsisten.
            $('#so-isi').prop('hidden', true);
            $('#btn-lipat-so-teks').text('Show details');
            $('#btn-lipat-so').attr('aria-expanded', 'false')
                .find('.dn-lipat-panah').removeClass('is-buka');
            return;
        }
        var pcs = invSo.reduce(function (a, r) { return a + (parseFloat(r.qty) || 0); }, 0);
        var w = hitungWs(invSo);
        $('#so-ringkas').html('<b>' + w + '</b> WS <span class="dn-lipat-titik">&middot;</span> '
            + '<b>' + invSo.length + '</b> row' + (invSo.length > 1 ? 's' : '')
            + ' <span class="dn-lipat-titik">&middot;</span> <b>' + angka(pcs) + '</b> pcs');
    }

    $('#btn-lipat-so').on('click', function () {
        var buka = $('#so-isi').prop('hidden');
        $('#so-isi').prop('hidden', !buka);
        $(this).attr('aria-expanded', buka ? 'true' : 'false');
        $('#btn-lipat-so-teks').text(buka ? 'Hide details' : 'Show details');
        $(this).find('.dn-lipat-panah').toggleClass('is-buka', buka);
    });

    function gambarDetailSo() {
        ringkasDetailSo();
        var qty = invSo.reduce(function (a, r) { return a + (parseFloat(r.qty) || 0); }, 0);
        $('#inv-so-jumlah-ws').html('<b>' + hitungWs(invSo) + '</b> WS');
        $('#inv-so-jumlah-qty').text(angka(qty));
        $('#inv-so-jumlah').prop('hidden', !invSo.length);

        var $b = $('#inv-table-so tbody').empty();
        if (!invSo.length) {
            $b.html('<tr><td class="dn-kosong" colspan="14">No WS selected yet. '
                + 'Use &ldquo;Add SJ / WS&rdquo; to pick one.</td></tr>');
        } else {
            $b.html(invSo.map(function (r, i) {
                var disc = parseFloat(r.disc) || 0;
                var total = (parseFloat(r.qty) || 0) * (parseFloat(r.unit_price) || 0);
                return '<tr>'
                    + '<td>' + teksAman(r.ws) + '</td>'
                    + '<td>' + teksAman(r.no_so) + '</td>'
                    + '<td>' + teksAman(r.so_date) + '</td>'
                    + '<td>' + teksAman(r.styleno) + '</td>'
                    + '<td>' + teksAman(r.product_item) + '</td>'
                    + '<td>' + teksAman(r.color) + '</td>'
                    + '<td>' + teksAman(r.curr) + '</td>'
                    + '<td>' + teksAman(r.uom) + '</td>'
                    + '<td class="dn-angka">' + angka(r.qty_so) + '</td>'
                    + '<td class="dn-angka"><input type="text" class="form-control dn-angka-input so-qty"'
                    + ' data-i="' + i + '" value="' + angka(r.qty) + '" autocomplete="off"></td>'
                    + '<td class="dn-angka">' + angka4(r.unit_price) + '</td>'
                    + '<td class="dn-angka"><input type="text" class="form-control dn-angka-input so-disc"'
                    + ' data-i="' + i + '" value="' + (disc || '') + '" placeholder="0" autocomplete="off"></td>'
                    + '<td class="dn-angka">' + angka(total) + '</td>'
                    + '<td class="dn-tengah dn-aksi-sel"><button type="button" class="btn btn-dn-buang btn-buang-so"'
                    + ' data-i="' + i + '" title="Remove this row"><i class="fas fa-times"></i></button></td>'
                    + '</tr>';
            }).join(''));
        }
        hitungRingkasan();
    }

    $('#inv-table-so').on('input', '.so-qty', function () {
        var r = invSo[parseInt($(this).data('i'), 10)];
        if (!r) { return; }
        r.qty = nilaiAngka($(this).val());
        r.total_price = r.qty * (parseFloat(r.unit_price) || 0);
        gambarDetailSo();
    });

    $('#inv-table-so').on('input', '.so-disc', function () {
        var r = invSo[parseInt($(this).data('i'), 10)];
        if (!r) { return; }
        var d = nilaiAngka($(this).val());
        r.disc = d > 100 ? 100 : d;
        hitungRingkasan();
    });

    $('#inv-table-so').on('click', '.btn-buang-so', function () {
        invSo.splice(parseInt($(this).data('i'), 10), 1);
        gambarDetailSo();
    });

    // ---- Modal Add WS ----
    var modalWS = new bootstrap.Modal(document.getElementById('modal-add-ws'));
    var wsTerakhir = [];

    function wsSudahMasuk(r) {
        for (var i = 0; i < invSo.length; i++) {
            if (String(invSo[i].id_so_det) === String(r.id_so_det)) { return true; }
        }
        return false;
    }

    function gambarWs(daftar) {
        var $b = $('#ws-table tbody').empty();
        if (!daftar.length) {
            $b.html('<tr><td class="dn-kosong" colspan="13">No WS found for this filter.</td></tr>');
            wsInfoPilih();
            return;
        }
        $b.html(daftar.map(function (r) {
            var sudah = wsSudahMasuk(r);
            return '<tr class="' + (r._pilih ? 'is-terpilih' : '') + '">'
                + '<td><b>' + teksAman(r.ws) + '</b></td>'
                + '<td>' + teksAman(r.no_so) + '</td>'
                + '<td>' + teksAman(r.so_date) + '</td>'
                + '<td>' + teksAman(r.styleno) + '</td>'
                + '<td>' + teksAman(r.product_item) + '</td>'
                + '<td>' + teksAman(r.color) + '</td>'
                + '<td>' + teksAman(r.curr) + '</td>'
                + '<td>' + teksAman(r.uom) + '</td>'
                + '<td class="dn-angka">' + angka(r.qty_so) + '</td>'
                // Terkunci sampai barisnya dicentang - qty baru ada artinya
                // kalau warna itu memang mau ditagih.
                + '<td class="dn-angka"><input type="text" class="form-control dn-angka-input ws-qty"'
                + ' data-i="' + r._i + '" value="' + angka(r._qty) + '" autocomplete="off"'
                + (sudah ? ' disabled' : (r._pilih ? '' : ' readonly')) + '></td>'
                + '<td class="dn-angka">' + angka4(r.unit_price) + '</td>'
                + '<td class="dn-angka ws-total" data-i="' + r._i + '">'
                + angka(r._qty * (parseFloat(r.unit_price) || 0)) + '</td>'
                + '<td class="dn-tengah">'
                + (sudah
                    ? '<i class="fas fa-check dn-sudah" title="Already added"></i>'
                    : '<input type="checkbox" class="ws-cek" data-i="' + r._i + '"'
                      + (r._pilih ? ' checked' : '') + '>')
                + '</td>'
                + '</tr>';
        }).join(''));
        wsInfoPilih();
    }

    function wsInfoPilih() {
        var ws = {}, baris = 0, qty = 0;
        wsTerakhir.forEach(function (r) {
            if (!r._pilih) { return; }
            ws[$.trim(String(r.ws || ''))] = true;
            baris++;
            qty += parseFloat(r._qty) || 0;
        });
        $('#ws-info-pilih').html(baris
            ? '<b>' + Object.keys(ws).length + '</b> WS &middot; <b>' + baris + '</b> colour'
              + (baris > 1 ? 's' : '') + ' &middot; <b>' + angka(qty) + '</b> pcs ticked'
            : 'No WS ticked yet.');
    }

    function bukaQtyWs($cek, pilih) {
        var $isi = $cek.closest('tr').find('.ws-qty');
        $isi.prop('readonly', !pilih);
        return $isi;
    }

    $('#ws-table').on('input', '.ws-qty', function () {
        var r = wsTerakhir[parseInt($(this).data('i'), 10)];
        if (!r) { return; }
        r._qty = nilaiAngka($(this).val());
        $('#ws-table .ws-total[data-i="' + r._i + '"]')
            .text(angka(r._qty * (parseFloat(r.unit_price) || 0)));
        wsInfoPilih();
    });

    $('#ws-table').on('change', '.ws-cek', function () {
        var pilih = $(this).is(':checked');
        var r = wsTerakhir[parseInt($(this).data('i'), 10)];
        if (r) { r._pilih = pilih; }
        $(this).closest('tr').toggleClass('is-terpilih', pilih);
        var $isi = bukaQtyWs($(this), pilih);
        if (pilih) { $isi.trigger('focus').trigger('select'); }
        wsInfoPilih();
    });

    $('#ws-cek-semua').on('change', function () {
        var on = $(this).is(':checked');
        $('#ws-table tbody .ws-cek').each(function () {
            var r = wsTerakhir[parseInt($(this).data('i'), 10)];
            if (r) { r._pilih = on; }
            $(this).prop('checked', on).closest('tr').toggleClass('is-terpilih', on);
            bukaQtyWs($(this), on);
        });
        wsInfoPilih();
    });

    $('#ws-cari').on('input', function () {
        var q = String($(this).val() || '').toLowerCase();
        gambarWs(wsTerakhir.filter(function (r) { return cocokCari(r, q); }));
    });

    $('#ws-btn-cari').on('click', function () {
        var $tb = $(this);
        $tb.prop('disabled', true);
        $('#ws-table tbody').html('<tr><td class="dn-kosong" colspan="13">Loading...</td></tr>');
        $.getJSON(RUT_WS, {
            tgl_awal: tglIso('#ws-tgl-awal'),
            tgl_akhir: tglIso('#ws-tgl-akhir'),
            buyer: $('#ws-buyer').val() || '',
            profit_center: $('#inv-pc').val() || ''
        }).done(function (res) {
            wsTerakhir = (res && res.data) ? res.data : [];
            wsTerakhir.forEach(function (r, i) {
                r._i = i;
                r._pilih = false;
                r.qty_so = parseFloat(r.qty_so) || 0;
                r._qty = r.qty_so;   // bawaannya qty SO penuh, tinggal dikurangi
            });
            gambarWs(wsTerakhir);
            if (res && res.pesan) {
                Swal.fire({ icon: 'warning', title: 'Partial result', text: res.pesan, customClass: { popup: 'dn-swal' } });
            }
        }).fail(function (x) {
            $('#ws-table tbody').html('<tr><td class="dn-kosong" colspan="13">Could not load WS.</td></tr>');
            var p = (x.responseJSON && x.responseJSON.pesan) ? x.responseJSON.pesan : 'Please try again.';
            Swal.fire({ icon: 'error', title: 'Search failed', text: p, customClass: { popup: 'dn-swal' } });
        }).always(function () { $tb.prop('disabled', false); });
    });

    $('#modal-add-ws').on('shown.bs.modal', function () {
        var $s = $('#ws-buyer');
        if (!$s.hasClass('select2-hidden-accessible')) {
            $s.select2({ theme: 'bootstrap4', width: '100%', dropdownParent: $('#modal-add-ws') });
        }
        var id = $('#inv-customer').val() || '';
        if (id !== '' && $s.find('option').filter(function () { return this.value === id; }).length) {
            $s.val(id).trigger('change');
        }
    });

    $('#ws-btn-apply').on('click', function () {
        var baru = wsTerakhir.filter(function (r) { return r._pilih && !wsSudahMasuk(r); });
        if (!baru.length) {
            Swal.fire({
                icon: 'warning', title: 'Nothing ticked',
                text: 'Tick at least one WS colour first.',
                customClass: { popup: 'dn-swal' }
            });
            return;
        }
        var tanpaQty = baru.filter(function (r) { return nilaiAngka(r._qty) <= 0; });
        if (tanpaQty.length) {
            Swal.fire({
                icon: 'warning', title: 'Qty is empty',
                html: '<b>' + tanpaQty.length + '</b> ticked colour'
                    + (tanpaQty.length > 1 ? 's have' : ' has') + ' no qty.'
                    + ' Fill in the qty to be invoiced, or untick it.',
                customClass: { popup: 'dn-swal' }
            });
            return;
        }
        baru.forEach(function (r) {
            var salin = $.extend({}, r);
            salin.qty = nilaiAngka(r._qty);
            salin.disc = 0;
            salin.total_price = salin.qty * (parseFloat(r.unit_price) || 0);
            delete salin._pilih;
            delete salin._i;
            delete salin._qty;
            invSo.push(salin);
        });
        gambarDetailSo();
        modalWS.hide();
    });

    /**
     * Rencana penyesuaian qty Detail SO terhadap SJ-nya - belum diterapkan.
     *
     * Yang ditagih adalah yang benar-benar dikirim, jadi begitu SJ sebuah
     * warna ada, qty baris SO-nya mengikuti. Warna yang belum punya SJ
     * dibiarkan: qty SO penuh, atau angka yang sudah diketik sendiri.
     */
    function rencanaQtySo() {
        var rencana = [];
        invSo.forEach(function (r) {
            var q = qtySjWarna(r);
            if (q <= 0) { return; }
            var lama = parseFloat(r.qty) || 0;
            if (lama === q) { return; }
            rencana.push({ baris: r, dari: lama, ke: q });
        });
        return rencana;
    }

    function terapkanQtySo(rencana) {
        rencana.forEach(function (x) {
            x.baris.qty = x.ke;
            x.baris.total_price = x.ke * (parseFloat(x.baris.unit_price) || 0);
        });
        return rencana.length > 0;
    }

    function selaraskanQtySo() {
        return terapkanQtySo(rencanaQtySo());
    }

    /**
     * Baris SO yang warnanya tidak punya SJ.
     *
     * Acuannya baris SJ: itu yang benar-benar dikirim. Warna yang tidak ada di
     * SJ berarti tidak jadi ditagih di invoice ini, jadi barisnya dibuang -
     * tapi tidak diam-diam, user dimintai persetujuan dulu.
     */
    function soTanpaSj() {
        if (!invBaris.length) { return []; }   // belum ada SJ apa pun - wajar
        return invSo.filter(function (r) { return qtySjWarna(r) <= 0; });
    }

    function labelSo(r) {
        return teksAman(r.ws) + ' <span class="dn-lipat-titik">&middot;</span> ' + teksAman(kunciWarnaSo(r));
    }

    /** "2 qty updated · 1 row added · 1 row removed" */
    function ringkasUbahSo(rencana, warnaBaru, tanpaSj) {
        var bagian = [];
        if (rencana.length)   { bagian.push('<b>' + rencana.length + '</b> qty updated'); }
        if (warnaBaru.length) { bagian.push('<b>' + warnaBaru.length + '</b> row'
            + (warnaBaru.length > 1 ? 's' : '') + ' added'); }
        if (tanpaSj.length)   { bagian.push('<b>' + tanpaSj.length + '</b> row'
            + (tanpaSj.length > 1 ? 's' : '') + ' removed'); }
        return bagian.join(' <span class="dn-lipat-titik">&middot;</span> ');
    }

    function bagianUbahSo(judul, alasan, daftar, isiBaris) {
        return '<div class="dn-swal-judul-kecil">' + judul + '</div>'
            + '<div class="dn-swal-alasan">' + alasan + '</div>'
            + '<ul class="dn-swal-daftar">'
            + daftar.map(isiBaris).join('')
            + '</ul>';
    }

    /**
     * Tanyakan dulu sebelum Detail SO ikut berubah.
     *
     * Detail SO yang menentukan angka invoice, jadi perubahannya tidak boleh
     * terjadi diam-diam - apalagi kalau qty-nya sudah disesuaikan sendiri atau
     * ada baris yang akan hilang.
     */
    function konfirmasiUbahSo(rencana, warnaBaru, lanjut) {
        var tanpaSj = soTanpaSj();
        if (!rencana.length && !warnaBaru.length && !tanpaSj.length) { return; }

        var isi = ['<div class="dn-swal-ringkas-ubah">'
            + ringkasUbahSo(rencana, warnaBaru, tanpaSj) + '</div>'];

        if (rencana.length) {
            var naik = rencana.filter(function (x) { return x.ke > x.dari; }).length;
            var turun = rencana.length - naik;
            var judul = 'Qty follows the SJ';
            if (naik && turun) { judul += ' (' + naik + ' up, ' + turun + ' down)'; }
            else if (naik)     { judul += ' (' + naik + ' up)'; }
            else if (turun)    { judul += ' (' + turun + ' down)'; }
            isi.push(bagianUbahSo(judul,
                'What is invoiced is what was actually shipped, so the SJ qty replaces the ordered qty.',
                rencana, function (x) {
                    return '<li>' + labelSo(x.baris) + ': <b>' + angka(x.dari) + '</b> &rarr; <b>'
                        + angka(x.ke) + '</b></li>';
                }));
        }
        if (warnaBaru.length) {
            isi.push(bagianUbahSo(
                'Added from the SJ &ndash; ' + warnaBaru.length + ' colour' + (warnaBaru.length > 1 ? 's' : ''),
                'These colours are on the SJ but not in Detail SO yet.',
                warnaBaru, function (r) {
                    return '<li>' + labelSo(r) + ': <b>' + angka(r.qty) + '</b></li>';
                }));
        }
        if (tanpaSj.length) {
            isi.push(bagianUbahSo(
                'Removed &ndash; ' + tanpaSj.length + ' colour' + (tanpaSj.length > 1 ? 's' : '') + ' without SJ',
                'No SJ covers these colours, so they are not shipped on this invoice.',
                tanpaSj, function (r) {
                    return '<li>' + labelSo(r) + ': <b>' + angka(r.qty) + '</b></li>';
                }));
        }

        Swal.fire({
            icon: 'question',
            title: 'Update Detail SO to match the SJ?',
            html: isi.join('')
                + '<p class="dn-swal-catatan" style="margin-top:10px">The invoice total follows Detail SO, so these numbers go into the invoice.</p>',
            showCancelButton: true,
            confirmButtonText: '<i class="fas fa-check"></i> Yes, update',
            cancelButtonText: 'No, leave it',
            reverseButtons: true,
            width: 620,
            customClass: { popup: 'dn-swal' }
        }).then(function (pilih) {
            if (!pilih.isConfirmed) { return; }
            lanjut();
            if (tanpaSj.length) {
                invSo = invSo.filter(function (r) { return tanpaSj.indexOf(r) === -1; });
                gambarDetailSo();
            }
        });
    }
    /**
     * Detail SO diisikan sendiri dari WS milik SJ yang baru dipilih.
     *
     * Yang wajib di invoice ini baris SO - SJ boleh menyusul. Jadi begitu SJ
     * dipilih, WS-nya langsung dibacakan ke Detail SO supaya user tidak perlu
     * memilih hal yang sama dua kali. Yang diambil HANYA warna yang SJ-nya
     * dipilih; satu WS biasanya berisi banyak warna.
     */
    function lengkapiSoDariSj() {
        var sudah = {};
        invSo.forEach(function (r) {
            sudah[$.trim(String(r.ws == null ? '' : r.ws)) + '|' + kunciWarnaSo(r)] = true;
        });

        var warnaSj = {};
        var ws = [];
        invBaris.forEach(function (r) {
            var k = $.trim(String(r.ws == null ? '' : r.ws));
            if (k === '' || k === '-') { return; }
            var kunci = k + '|' + kunciWarnaSo(r);
            if (sudah[kunci]) { return; }   // warna ini sudah ada di Detail SO
            warnaSj[kunci] = true;
            if (ws.indexOf(k) === -1) { ws.push(k); }
        });

        var rencana = rencanaQtySo();

        if (!ws.length) {
            konfirmasiUbahSo(rencana, [], function () {
                if (terapkanQtySo(rencana)) { gambarDetailSo(); }
            });
            return;
        }

        $.getJSON(RUT_WS, {
            ws: ws.join(','),
            profit_center: $('#inv-pc').val() || ''
        }).done(function (res) {
            var data = (res && res.data) ? res.data : [];
            var baru = [];
            data.forEach(function (r) {
                if (!warnaSj[$.trim(String(r.ws == null ? '' : r.ws)) + '|' + kunciWarnaSo(r)]) { return; }
                if (wsSudahMasuk(r)) { return; }
                var salin = $.extend({}, r);
                salin.qty_so = parseFloat(r.qty_so) || 0;
                salin.disc = 0;
                // Yang ditagih = yang benar-benar dikirim.
                var dariSj = qtySjWarna(salin);
                salin.qty = dariSj > 0 ? dariSj : salin.qty_so;
                salin.total_price = salin.qty * (parseFloat(salin.unit_price) || 0);
                baru.push(salin);
            });

            konfirmasiUbahSo(rencana, baru, function () {
                baru.forEach(function (r) { invSo.push(r); });
                terapkanQtySo(rencana);
                gambarDetailSo();
            });
        }).fail(function () {
            Swal.fire({
                icon: 'warning',
                title: 'SO rows not loaded',
                text: 'The SJ was added, but its SO rows could not be read. Use Add WS to add them.',
                customClass: { popup: 'dn-swal' }
            });
        });
    }

    /** Siapkan & buka modal Add WS - Buyer-nya ikut Billed To di form. */
    function siapkanModalWs() {
        var idCust = $('#inv-customer').val() || '';
        var $buyer = $('#ws-buyer');
        if (idCust !== '' && $buyer.find('option').filter(function () { return this.value === idCust; }).length) {
            $buyer.val(idCust).trigger('change');
        }
        modalWS.show();
    }
    /**
     * Satu tombol, dua sumber. SJ-nya sering belum terbit waktu invoice harus
     * dibuat, jadi user memilih dulu: mulai dari WS, atau langsung dari SJ.
     */
    function pilihSumberBaris() {
        Swal.fire({
            icon: 'question',
            title: 'Add rows from',
            html: 'Pick <b>WS</b> when the SJ has not been issued yet -'
                + ' the SJ can be filled in later.',
            showCancelButton: true,
            showDenyButton: true,
            confirmButtonText: '<i class="fas fa-clipboard-list"></i> By WS',
            denyButtonText: '<i class="fas fa-truck"></i> By SJ',
            cancelButtonText: 'Cancel',
            reverseButtons: true,
            customClass: { popup: 'dn-swal' }
        }).then(function (h) {
            if (h.isConfirmed) {
                siapkanModalWs();
            } else if (h.isDenied) {
                siapkanModalSj();
            }
        });
    }

    // ---- Pemilihan SJ dikelompokkan per nomor internal SJ ----
    // Satu SJ (FG/OUT, GK/OUT, dan seterusnya) bisa berisi puluhan baris
    // warna/size. Dicentang satu per satu gampang ada yang terlewat, jadi di
    // modal ini satu baris mewakili satu SJ - dicentang berarti seluruh
    // barisnya ikut, begitu juga diskonnya.
    // Yang masuk ke tabel Detail SJ dan yang disimpan tetap per baris.
    var sjGrup = [];

    // Nilai yang berbeda-beda di dalam satu kelompok diwakili yang pertama;
    // sisanya cukup dihitung supaya ketahuan kalau isinya campur.
    function wakilNilai(baris, kunci) {
        var beda = [];
        baris.forEach(function (r) {
            var v = $.trim(String(r[kunci] == null ? '' : r[kunci]));
            if (v !== '' && beda.indexOf(v) === -1) { beda.push(v); }
        });
        if (!beda.length) { return '-'; }
        return teksAman(beda[0])
            + (beda.length > 1 ? ' <span class="dn-lain">+' + (beda.length - 1) + '</span>' : '');
    }

    // Kode pendek (Curr, UOM) ditulis lengkap dipisah koma - "YRD, PCS" lebih
    // jelas daripada "YRD +1", dan isinya cuma beberapa huruf jadi kolomnya
    // tidak melebar. Lebih dari 4 baru dipendekkan.
    function daftarNilai(baris, kunci) {
        var beda = [];
        baris.forEach(function (r) {
            var v = $.trim(String(r[kunci] == null ? '' : r[kunci]));
            if (v !== '' && beda.indexOf(v) === -1) { beda.push(v); }
        });
        if (!beda.length) { return '-'; }
        var sisa = beda.length - 4;
        return teksAman(beda.slice(0, 4).join(', '))
            + (sisa > 0 ? ' <span class="dn-lain">+' + sisa + '</span>' : '');
    }

    // Satu kelompok = satu SJ di mata user: nomor internalnya (FG/OUT, GK/OUT,
    // WIP/OUT, ...), atau nomor SJ kalau nomor internalnya belum ada.
    function kunciKelompok(r) {
        var fg = $.trim(String(r.shipping_number || ''));
        return fg !== '' ? 'FG|' + fg : 'SJ|' + String(r.asal) + '|' + String(r.sj);
    }

    // Jumlah SJ (kelompok) yang berbeda di sebuah daftar baris.
    function hitungSj(baris) {
        var ada = {}, n = 0;
        baris.forEach(function (r) {
            var k = kunciKelompok(r);
            if (!ada[k]) { ada[k] = true; n++; }
        });
        return n;
    }

    // Keterangan di bawah SJ List. Total Qty dibuat menonjol - itu angka yang
    // paling sering dicek sebelum menekan Apply.
    function teksPilih(baru, lama, qty) {
        var teks;
        if (baru) {
            teks = '<b>' + baru + '</b> new SJ ticked';
            if (lama) { teks += ' &middot; <b>' + lama + '</b> SJ already in the invoice'; }
        } else if (lama) {
            teks = '<b>' + lama + '</b> SJ already in the invoice. Ticked SJ are the ones it uses'
                + ' - untick one to drop it.';
        } else {
            teks = 'No SJ ticked yet.';
        }
        if (qty) {
            teks += '<span class="dn-pilih-qty">Total Qty <b>' + angka(qty) + '</b></span>';
        }
        return teks;
    }

    /* ---- Satu invoice = satu tanggal SJ ----
     * Boleh beberapa SJ, tapi tanggalnya harus sama. Begitu ada satu yang
     * dicentang, SJ bertanggal lain dikunci; kalau semuanya dilepas, kuncinya
     * hilang lagi jadi tanggal lain tetap bisa dipilih. */
    function tglSj(r) {
        return String((r && r.bppbdate) || '').slice(0, 10);
    }

    /** Baris yang akan jadi isi invoice kalau Apply ditekan sekarang - sama
     *  dengan hitungan di #so-btn-tambah. */
    function barisProspek() {
        var dalamHasil = {};
        sjTerakhir.forEach(function (r) {
            dalamHasil[String(r.asal) + '|' + String(r.id_baris)] = r;
        });
        var hasil = [];
        invBaris.forEach(function (x) {
            var r = dalamHasil[String(x.asal) + '|' + String(x.id_baris)];
            if (!r) { hasil.push(x); return; }   // di luar hasil pencarian: tetap terpakai
            if (r._pilih) { hasil.push(x); }     // centangnya dilepas = dibuang
        });
        sjTerakhir.forEach(function (r) {
            if (r._pilih && !sudahMasuk(r)) { hasil.push(r); }
        });
        return hasil;
    }

    /** Tanggal yang sedang dipakai invoice ini - '' kalau belum ada. */
    function tanggalTerpakai() {
        var tgl = '';
        barisProspek().forEach(function (r) { if (!tgl) { tgl = tglSj(r); } });
        return tgl;
    }

    /** Tanggal SJ yang berbeda-beda di sekumpulan baris. */
    function tanggalBeda(daftar) {
        var ada = [];
        (daftar || []).forEach(function (r) {
            var t = tglSj(r);
            if (t && ada.indexOf(t) === -1) { ada.push(t); }
        });
        return ada;
    }

    function aturKunciTanggal() {
        var tgl = tanggalTerpakai();
        var terkunci = 0;
        $('#so-table-sj tbody .so-cek').each(function () {
            var g = sjGrup[parseInt($(this).data('g'), 10)];
            if (!g) { return; }
            var beda = !!tgl && !!g.tgl && g.tgl !== tgl;
            $(this).prop('disabled', beda);
            $(this).closest('tr').toggleClass('is-kunci-tgl', beda);
            if (beda) { terkunci++; }
        });

        var $ket = $('#so-tgl-info');
        if (tgl && terkunci) {
            $ket.html('<i class="fas fa-calendar-day"></i> One invoice = one SJ date. Locked to <b>'
                + teksAman(tgl) + '</b> - <b>' + terkunci + '</b> SJ of another date have no tick box. '
                + 'Untick everything to pick another date.').prop('hidden', false);
        } else if (tgl) {
            $ket.html('<i class="fas fa-calendar-day"></i> One invoice = one SJ date. This invoice uses <b>'
                + teksAman(tgl) + '</b>.').prop('hidden', false);
        } else {
            $ket.prop('hidden', true).html('');
        }
    }

    function kelompokSj(daftar) {
        var peta = {}, hasil = [];
        daftar.forEach(function (r) {
            var fg = $.trim(String(r.shipping_number || ''));
            var kunci = kunciKelompok(r);
            if (!peta[kunci]) {
                peta[kunci] = { fg: fg, baris: [], qty: 0, nilai: 0, warna: [], dipilih: 0, disc: 0,
                    manual: 0, tgl: tglSj(r) };
                hasil.push(peta[kunci]);
            }
            peta[kunci].baris.push(r);
        });
        hasil.forEach(function (g, i) {
            g._g = i;
            g.disc = parseFloat(g.baris[0]._disc) || 0;
            g.baris.forEach(function (r) {
                g.qty += parseFloat(r.qty) || 0;
                if (Number(r.harga_manual) === 1) { g.manual++; }
                var tp = parseFloat(r.total_price);
                if (isNaN(tp)) { tp = (parseFloat(r.qty) || 0) * (parseFloat(r.unit_price) || 0); }
                g.nilai += tp;
                // Barang tanpa warna (SJ tanpa SO) dihitung lewat nama barangnya,
                // jadi kolom ini tidak jadi "-" untuk seluruh SJ GK/OUT.
                var w = $.trim(String(r.color || '')) || $.trim(String(r.product_item || ''));
                if (w !== '' && w !== '-' && g.warna.indexOf(w) === -1) { g.warna.push(w); }
                if (r._pilih) { g.dipilih++; }
            });
        });
        return hasil;
    }

    function gambarSj(baris) {
        var $b = $('#so-table-sj tbody').empty();
        sjGrup = kelompokSj(baris);
        if (!sjGrup.length) {
            $b.append('<tr><td class="dn-kosong" colspan="15">No SJ found for this filter.</td></tr>');
            aturKunciTanggal();
            segarkanCekSemua();
            hitungRingkasan();
            return;
        }
        $b.html(sjGrup.map(function (g) {
            var penuh = g.dipilih > 0 && g.dipilih === g.baris.length;
            return '<tr' + (g.dipilih ? ' class="is-pilih"' : '') + '>'
                + '<td>' + (g.fg ? teksAman(g.fg) : '-') + '</td>'
                + '<td>' + wakilNilai(g.baris, 'bppbdate') + '</td>'
                + '<td>' + wakilNilai(g.baris, 'sj') + '</td>'
                + '<td>' + wakilNilai(g.baris, 'no_so') + '</td>'
                + '<td>' + wakilNilai(g.baris, 'ws') + '</td>'
                + '<td>' + wakilNilai(g.baris, 'styleno') + '</td>'
                + '<td>' + daftarNilai(g.baris, 'product_item') + '</td>'
                + '<td>' + (g.warna.length ? g.warna.length + (g.warna.length > 1 ? ' colours' : ' colour') : '-') + '</td>'
                + '<td>' + daftarNilai(g.baris, 'curr') + '</td>'
                + '<td>' + daftarNilai(g.baris, 'uom') + '</td>'
                + '<td class="dn-angka">' + g.baris.length + '</td>'
                + '<td class="dn-angka">' + angka(g.qty) + '</td>'
                // Satu kotak diskon untuk satu SJ - isinya diteruskan ke
                // seluruh baris di dalamnya.
                + '<td class="dn-angka"><input type="text" class="form-control so-disc"'
                + ' data-g="' + g._g + '" value="' + g.disc + '" inputmode="decimal" autocomplete="off"></td>'
                + '<td class="dn-angka">' + (g.manual === g.baris.length && !g.nilai
                    ? '<span class="dn-lain">priced later</span>' : angka(g.nilai)) + '</td>'
                + '<td class="dn-tengah"><input type="checkbox" class="so-cek" data-g="' + g._g + '"'
                + (penuh ? ' checked' : '') + '></td>'
                + '</tr>';
        }).join(''));
        // Kelompok yang baru sebagian ikut ditandai setengah - tandanya masih ada
        // baris yang belum terpilih (bisa terjadi dari data invoice lama).
        $('#so-table-sj tbody .so-cek').each(function () {
            var g = sjGrup[parseInt($(this).data('g'), 10)];
            this.indeterminate = !!g && g.dipilih > 0 && g.dipilih < g.baris.length;
        });
        aturKunciTanggal();
        segarkanCekSemua();
        hitungRingkasan();
    }

    /** Centang-semua mengikuti kelompok yang sedang terlihat. */
    function segarkanCekSemua() {
        // SJ yang terkunci tanggal tidak ikut dihitung - kalau ikut, kotak
        // "centang semua" selamanya tampak setengah.
        var $cb = $('#so-table-sj tbody .so-cek').filter(':not(:disabled)');
        var penuh = $cb.filter(':checked').length;
        var separuh = $cb.filter(function () { return this.indeterminate; }).length;
        $('#so-cek-semua')
            .prop('checked', $cb.length > 0 && penuh === $cb.length)
            .prop('indeterminate', $cb.length > 0 && (separuh > 0 || (penuh > 0 && penuh < $cb.length)));
    }

    $('#so-btn-cari').on('click', function () {
        var $tb = $(this);
        $tb.prop('disabled', true);
        $('#so-table-sj tbody').html('<tr><td class="dn-kosong" colspan="15">Loading...</td></tr>');
        $.getJSON(RUT_SJ, {
            tgl_awal: tglIso('#so-tgl-awal'),
            tgl_akhir: tglIso('#so-tgl-akhir'),
            buyer: $('#so-buyer').val() || '',
            // Sumber SJ ikut profit center: NAK tidak boleh menampilkan SJ garment.
            profit_center: $('#so-profit-ctr').val() || '',
            // Saat Edit: baris milik invoice ini sendiri tetap boleh muncul.
            abaikan: ID_UBAH || ''
        }).done(function (res) {
            sjTerakhir = (res && res.data) ? res.data : [];
            // Nomor urut tetap + status awal, dipakai sebagai acuan saat difilter.
            // Baris yang sudah masuk invoice langsung tercentang beserta diskonnya,
            // jadi kelihatan mana yang sudah diambil dan mana yang belum.
            sjTerakhir.forEach(function (r, i) {
                var sudah = cariDiInvoice(r);
                r._i = i;
                r._pilih = !!sudah;
                r._disc = sudah ? (parseFloat(sudah.disc) || 0) : 0;
            });
            gambarSj(sjTerakhir);
            if (res && res.pesan) {
                Swal.fire({ icon: 'warning', title: 'Partial result', text: res.pesan });
            }
        }).fail(function (x) {
            $('#so-table-sj tbody').html('<tr><td class="dn-kosong" colspan="15">Could not load SJ.</td></tr>');
            var p = (x.responseJSON && x.responseJSON.pesan) ? x.responseJSON.pesan : 'Please try again.';
            Swal.fire({ icon: 'error', title: 'Search failed', text: p });
        }).always(function () { $tb.prop('disabled', false); });
    });

    // ---- Perhitungan ringkasan modal ----
    // Rumusnya sama dengan AR (modal_input_dp / retur / dpcbd / vat):
    //   Total            = jumlah Total Price baris yang dicentang
    //   Discount         = jumlah (disc% x Total Price) baris yang dicentang
    //   Total Without Tax= Total - Discount - DP - DP/CBD - Return
    //   VAT              = Total Without Tax x 11% atau 12% (kalau dicentang)
    //   Grand Total      = Total Without Tax + VAT
    function nilai(sel) {
        var n = parseFloat(String($(sel).val() || '').replace(/,/g, ''));
        return isNaN(n) ? 0 : n;
    }
    function rupiah(n) {
        return n.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    /** Baris invoice yang sepadan dengan satu baris hasil pencarian, kalau ada. */
    function cariDiInvoice(r) {
        for (var i = 0; i < invBaris.length; i++) {
            if (String(invBaris[i].asal) === String(r.asal)
                && String(invBaris[i].id_baris) === String(r.id_baris)) {
                return invBaris[i];
            }
        }
        return null;
    }

    function sudahMasuk(r) {
        return cariDiInvoice(r) !== null;
    }

    function hitungRingkasan() {
        // Yang dihitung SELURUH isi invoice: baris yang sudah masuk ditambah
        // baris yang baru ditandai di modal ini. Kalau cuma batch terbaru,
        // angkanya menyesatkan - DP, Return & VAT di bawah ini berlaku untuk
        // seluruh invoice, bukan untuk satu batch.
        var total = 0, discount = 0, qty = 0;

        // Baris SO dipakai untuk warna yang punya SO; baris SJ cuma untuk
        // warna yang tidak punya - lihat barisDitagih(). Tanpa itu warna
        // yang punya keduanya terhitung dua kali.
        barisDitagih().forEach(function (r) {
            var harga = (parseFloat(r.qty) || 0) * (parseFloat(r.unit_price) || 0);
            total += harga;
            discount += (parseFloat(r.disc) || 0) / 100 * harga;
            qty += parseFloat(r.qty) || 0;
        });

        // Dihitung dari DATA, bukan dari baris yang sedang tampil - baris yang
        // sudah dicentang lalu tersembunyi oleh filter tetap ikut terhitung.
        sjTerakhir.forEach(function (r) {
            if (!r._pilih || sudahMasuk(r)) { return; }
            var harga = parseFloat(r.total_price) || 0;
            total += harga;
            discount += (parseFloat(r._disc) || 0) / 100 * harga;
            qty += parseFloat(r.qty) || 0;
        });

        var dp    = nilai('#so-dp');
        var dpcbd = nilai('#so-dpcbd');
        var retur = nilai('#so-return');
        var twot  = total - discount - dp - dpcbd - retur;

        var tarif = $('#so-vat-11').is(':checked') ? 0.11 : 0;
        var vat = twot * tarif;

        $('#so-total').val(rupiah(total));
        $('#so-discount').val(rupiah(discount));
        $('#so-twot').val(rupiah(twot));
        $('#so-vat').val(rupiah(vat));
        $('#so-grandtotal').val(rupiah(twot + vat));

        // Dipisah supaya jelas mana yang baru dan mana yang sudah ada. Baris
        // yang sudah masuk invoice memang tampil tercentang, jadi jumlahnya
        // disebut terpisah - kalau tidak, '0 selected' terbaca janggal.
        // Yang dihitung SJ-nya (kelompok FG/OUT), bukan baris warna/size.
        var sjBaru = hitungSj(sjTerakhir.filter(function (r) { return r._pilih && !sudahMasuk(r); }));
        $('#so-info-pilih').html(teksPilih(sjBaru, hitungSj(invBaris), qty));
    }

    $('#so-vat-11').on('change', hitungRingkasan);
    $('#so-dp, #so-dpcbd, #so-return').on('input', hitungRingkasan);

    // Status centang & diskon disimpan di data, jadi tidak hilang waktu tabel
    // digambar ulang (mis. setelah filter dikosongkan). Satu centang mengenai
    // seluruh baris di FG/OUT itu.
    function pilihKelompok(g, pilih) {
        if (!g) { return; }
        g.baris.forEach(function (r) { r._pilih = pilih; });
        g.dipilih = pilih ? g.baris.length : 0;
    }

    $('#so-table-sj').on('change', '.so-cek', function () {
        var pilih = $(this).is(':checked');
        pilihKelompok(sjGrup[parseInt($(this).data('g'), 10)], pilih);
        this.indeterminate = false;
        $(this).closest('tr').toggleClass('is-pilih', pilih);
        aturKunciTanggal();
        segarkanCekSemua();
        hitungRingkasan();
    });
    $('#so-table-sj').on('input', '.so-disc', function () {
        var g = sjGrup[parseInt($(this).data('g'), 10)];
        if (!g) { return; }
        var d = parseFloat($(this).val()) || 0;
        g.disc = d;
        g.baris.forEach(function (r) { r._disc = d; });
        hitungRingkasan();
    });
    // Centang-semua hanya untuk kelompok yang sedang terlihat, dan hanya yang
    // tanggalnya sama - satu invoice cuma boleh memuat satu tanggal SJ.
    $('#so-cek-semua').on('change', function () {
        var on = $(this).is(':checked');
        var tgl = on ? tanggalTerpakai() : '';
        if (on && !tgl) {
            var g0 = sjGrup[parseInt($('#so-table-sj tbody .so-cek').first().data('g'), 10)];
            tgl = g0 ? g0.tgl : '';
        }
        $('#so-table-sj tbody .so-cek').each(function () {
            var g = sjGrup[parseInt($(this).data('g'), 10)];
            var pilih = on && (!tgl || !g || !g.tgl || g.tgl === tgl);
            pilihKelompok(g, pilih);
            this.indeterminate = false;
            $(this).prop('checked', pilih).closest('tr').toggleClass('is-pilih', pilih);
        });
        aturKunciTanggal();
        segarkanCekSemua();
        hitungRingkasan();
    });

    // Filter cepat di sisi tampilan, tidak menembak server lagi.
    $('#so-cari-sj').on('input', function () {
        var q = $.trim($(this).val()).toLowerCase();
        gambarSj(sjTerakhir.filter(function (r) { return cocokCari(r, q); }));
    });


    // ---- Harga baris SJ yang tidak punya SO ----
    // FG/OUT (garment) dan OFC/OUT (knitting) harganya ikut SO, jadi tetap
    // tampil sebagai teks dan tidak bisa diubah dari sini. Tipe lain - GK/OUT,
    // GEN/OUT, WIP/OUT, GACC/OUT, SCR/OUT, SPCK/OUT - keluar tanpa SO dan di
    // database harganya memang 0, jadi diketik di sini. Server memakai angka
    // ini apa adanya, tapi HANYA untuk baris yang memang tanpa SO.
    function perluHarga(r) {
        return Number(r.harga_manual) === 1;
    }

    function hargaKosong(r) {
        return perluHarga(r) && !((parseFloat(r.unit_price) || 0) > 0);
    }

    /** Baris tanpa SO yang harganya masih kosong. */
    function barisTanpaHarga() {
        return invBaris.filter(hargaKosong);
    }

    function selHarga(r, i) {
        if (!perluHarga(r)) { return angka(r.unit_price); }
        var isi = (parseFloat(r.unit_price) || 0) > 0 ? r.unit_price : '';
        return '<div class="dn-harga-sel">'
            + '<input type="text" class="form-control dn-harga' + (isi === '' ? ' dn-harga-kurang' : '') + '"'
            + ' data-i="' + i + '" value="' + (isi === '' ? '' : teksAman(isi)) + '" inputmode="decimal"'
            + ' autocomplete="off" placeholder="0.00" aria-label="Unit Price">'
            + '<button type="button" class="btn dn-harga-sebar" data-i="' + i + '"'
            + ' title="Use this price &amp; currency for every row with the same item: '
            + teksAman(namaItem(r)) + ' (' + teksAman(r.uom) + ')">'
            + '<i class="fas fa-angle-double-down"></i></button>'
            + '</div>';
    }

    // ---- Mata uang baris SJ tanpa SO ----
    // Di bppb kolom curr memang kosong untuk tipe selain FG/OUT, jadi dipilih
    // di sini. Nilai invoice memakai mata uang ini, karena itu wajib diisi dan
    // semua baris harus memakai mata uang yang sama.
    var MATA_UANG = @json($mataUangSj ?? array('IDR', 'USD'));

    function currKosong(r) {
        return perluHarga(r) && $.trim(String(r.curr || '')) === '';
    }

    /** Baris tanpa SO yang mata uangnya belum dipilih. */
    function barisTanpaCurr() {
        return invBaris.filter(currKosong);
    }

    /** Mata uang berbeda yang terpakai di invoice ini. */
    function currDipakai() {
        var ada = [];
        invBaris.forEach(function (r) {
            var c = $.trim(String(r.curr || '')).toUpperCase();
            if (c !== '' && ada.indexOf(c) === -1) { ada.push(c); }
        });
        return ada;
    }

    function selCurr(r, i) {
        if (!perluHarga(r)) { return teksAman(r.curr); }
        var kini = $.trim(String(r.curr || ''));
        var pilihan = ['<option value="">--</option>'];
        MATA_UANG.forEach(function (m) {
            pilihan.push('<option value="' + teksAman(m) + '"' + (m === kini ? ' selected' : '') + '>'
                + teksAman(m) + '</option>');
        });
        return '<div class="dn-harga-sel">'
            + '<select class="form-control dn-curr' + (kini === '' ? ' dn-harga-kurang' : '') + '"'
            + ' data-i="' + i + '" aria-label="Currency">' + pilihan.join('') + '</select>'
            + '<button type="button" class="btn dn-curr-sebar" data-i="' + i + '"'
            + ' title="Use this currency for every row that has no SO">'
            + '<i class="fas fa-angle-double-down"></i></button>'
            + '</div>';
    }

    $('#inv-table-sj').on('change', '.dn-curr', function () {
        var r = invBaris[parseInt($(this).data('i'), 10)];
        if (!r) { return; }
        r.curr = $(this).val();
        $(this).toggleClass('dn-harga-kurang', currKosong(r));
    });

    // Mata uang tidak dipatok item atau SJ: sekali klik, semua baris yang memang
    // boleh diisi ikut memakai mata uang ini - satu invoice toh cuma boleh satu
    // mata uang.
    $('#inv-table-sj').on('click', '.dn-curr-sebar', function () {
        var r = invBaris[parseInt($(this).data('i'), 10)];
        if (!r) { return; }
        if (currKosong(r)) {
            Swal.fire({
                icon: 'warning',
                title: 'Pick a currency first',
                text: 'Choose this row\u2019s currency, then copy it to the other rows.',
                customClass: { popup: 'dn-swal' }
            });
            return;
        }
        var n = 0;
        invBaris.forEach(function (x) {
            if (x === r || !perluHarga(x)) { return; }
            if ($.trim(String(x.curr || '')) === $.trim(String(r.curr))) { return; }
            x.curr = r.curr;
            n++;
        });
        gambarDetail();
        Swal.fire({
            icon: n ? 'success' : 'info',
            title: n ? 'Currency copied' : 'Nothing to change',
            html: n
                ? '<b>' + n + '</b> more row' + (n > 1 ? 's' : '') + ' now use <b>' + teksAman(r.curr) + '</b>.'
                : 'Every row that can be edited already uses <b>' + teksAman(r.curr) + '</b>.',
            timer: n ? 1600 : undefined,
            showConfirmButton: !n,
            customClass: { popup: 'dn-swal' }
        });
    });

    /** Nama barang satu baris - penentu baris mana yang ikut disalin harganya. */
    function namaItem(r) {
        return $.trim(String(r.product_item || '')) || '-';
    }

    /** "Barang yang sama" = nama barang DAN satuannya sama. */
    function kunciItem(r) {
        return namaItem(r).toUpperCase() + ' | ' + $.trim(String(r.uom || '')).toUpperCase();
    }

    function pakaiHargaBaris(r, nilai) {
        r.unit_price  = nilai;
        r.total_price = (parseFloat(r.qty) || 0) * (parseFloat(nilai) || 0);
    }

    // Tabelnya sengaja TIDAK digambar ulang tiap ketikan - kursor user akan
    // lompat keluar dari kotaknya. Cukup sel Total Price dan ringkasan bawah.
    $('#inv-table-sj').on('input', '.dn-harga', function () {
        var r = invBaris[parseInt($(this).data('i'), 10)];
        if (!r) { return; }
        pakaiHargaBaris(r, $(this).val());
        $(this).closest('tr').find('.dn-total-baris').text(angka(r.total_price));
        $(this).toggleClass('dn-harga-kurang', hargaKosong(r));
        hitungDetail();
    });

    // Barang yang sama biasanya berharga sama walau keluar lewat SJ berbeda -
    // tombol ini menyalin harga & mata uang baris ini ke semua baris dengan
    // nama barang dan satuan yang sama, di SJ mana pun.
    $('#inv-table-sj').on('click', '.dn-harga-sebar', function () {
        var r = invBaris[parseInt($(this).data('i'), 10)];
        if (!r) { return; }
        if (hargaKosong(r)) {
            Swal.fire({
                icon: 'warning',
                title: 'Type a price first',
                text: 'Fill this row’s Unit Price, then copy it to the other rows with the same item.',
                customClass: { popup: 'dn-swal' }
            });
            return;
        }
        var n = 0;
        invBaris.forEach(function (x) {
            if (x === r || !perluHarga(x) || kunciItem(x) !== kunciItem(r)) { return; }
            pakaiHargaBaris(x, r.unit_price);
            if ($.trim(String(r.curr || '')) !== '') { x.curr = r.curr; }
            n++;
        });
        gambarDetail();
        Swal.fire({
            icon: n ? 'success' : 'info',
            title: n ? 'Price copied' : 'Nothing to copy',
            html: n
                ? '<b>' + n + '</b> more row' + (n > 1 ? 's' : '') + ' of '
                    + teksAman(namaItem(r)) + ' now use this price.'
                : 'No other row has the same item &amp; unit as <b>' + teksAman(namaItem(r)) + '</b>.',
            timer: n ? 1600 : undefined,
            showConfirmButton: !n,
            customClass: { popup: 'dn-swal' }
        });
    });

    function gambarDetail() {
        var $b = $('#inv-table-sj tbody').empty();
        if (!invBaris.length) {
            $b.html('<tr><td class="dn-kosong" colspan="17">No SJ selected yet. Use &ldquo;Add SJ&rdquo; to pick one.</td></tr>');
        } else {
            $b.html(invBaris.map(function (r, i) {
                return '<tr>'
                    + '<td>' + teksAman(r.no_so) + '</td>'
                    + '<td>' + teksAman(r.sj) + '</td>'
                    + '<td>' + teksAman(r.bppbdate) + '</td>'
                    + '<td>' + teksAman(r.shipping_number) + '</td>'
                    + '<td>' + teksAman(r.ws) + '</td>'
                    + '<td>' + teksAman(r.styleno) + '</td>'
                    + '<td>' + teksAman(r.product_group) + '</td>'
                    + '<td>' + teksAman(r.product_item) + '</td>'
                    + '<td>' + teksAman(r.color) + '</td>'
                    + '<td>' + teksAman(r.size) + '</td>'
                    + '<td>' + selCurr(r, i) + '</td>'
                    + '<td>' + teksAman(r.uom) + '</td>'
                    + '<td class="dn-angka">' + angka(r.qty) + '</td>'
                    + '<td class="dn-angka">' + selHarga(r, i) + '</td>'
                    + '<td class="dn-angka">' + angka(r.disc) + '</td>'
                    + '<td class="dn-angka dn-total-baris">' + angka(r.total_price) + '</td>'
                    + '<td class="dn-tengah"><button type="button" class="btn btn-dn-buang"'
                    + ' data-i="' + i + '" title="Remove this row"><i class="fas fa-times"></i></button></td>'
                    + '</tr>';
            }).join(''));
        }

        segarkanJumlahSj();

        // SO Number diisi dari SJ yang dipilih (bisa lebih dari satu SO).
        var so = [];
        invBaris.forEach(function (r) {
            if (r.no_so && so.indexOf(r.no_so) === -1) { so.push(r.no_so); }
        });
        $('#inv-so').val(so.join(', '));
        segarkanTanggalInvoice();
        hitungDetail();
    }

    /** Invoice Date ikut tanggal SJ - kotaknya readonly, jadi tidak ada yang
     *  perlu diketik. Kalau SJ-nya belum ada, tanggal bawaannya dibiarkan. */
    function segarkanTanggalInvoice() {
        var tgl = '';
        invBaris.forEach(function (r) { if (!tgl) { tgl = tglSj(r); } });
        if (tgl && tgl !== tglIso('#inv-tgl')) { tglSet('#inv-tgl', tgl); }
    }

    // Baris jumlah di kaki Detail SJ: berapa SJ dan total qty-nya.
    function segarkanJumlahSj() {
        var qty = invBaris.reduce(function (a, r) { return a + (parseFloat(r.qty) || 0); }, 0);
        var sj = hitungSj(invBaris);
        $('#inv-sj-jumlah-sj').html('<b>' + sj + '</b> SJ');
        $('#inv-sj-jumlah-qty').text(angka(qty));
        $('#inv-sj-jumlah').prop('hidden', !invBaris.length);
    }

    // Ringkasan di form utama memakai rumus yang sama dengan di modal.
    function hitungDetail() {
        var total = 0, discount = 0;
        invBaris.forEach(function (r) {
            var harga = parseFloat(r.total_price) || 0;
            total += harga;
            discount += (parseFloat(r.disc) || 0) / 100 * harga;
        });
        var dp    = nilai('#inv-dp');
        var dpcbd = parseFloat($('#inv-table-sj').data('dpcbd')) || 0;
        var retur = nilai('#inv-return');
        // DP/CBD ikut dikurangkan, sama seperti hitungan di modal dan di server.
        var twot  = total - discount - dp - dpcbd - retur;
        var vat   = twot * (parseFloat($('#inv-table-sj').data('vat')) || 0);

        $('#inv-total').val(rupiah(total));
        $('#inv-discount').val(rupiah(discount));
        $('#inv-twot').val(rupiah(twot));
        $('#inv-vat').val(rupiah(vat));
        $('#inv-grandtotal').val(rupiah(twot + vat));
    }

    $('#inv-table-sj').on('click', '.btn-dn-buang', function () {
        invBaris.splice(parseInt($(this).data('i'), 10), 1);
        // Qty Detail SO ikut berkurang - yang ditagih yang benar-benar dikirim.
        if (selaraskanQtySo()) { gambarDetailSo(); }
        gambarDetail();
    });
    $('#inv-dp, #inv-return').on('input', hitungDetail);

    $('#so-btn-tambah').on('click', function () {
        // Centang di modal ini yang menentukan isi invoice, BUKAN cuma menambah.
        // Karena baris yang sudah masuk tampil tercentang, melepas centangnya
        // wajar diartikan "buang baris itu" - kalau tidak, centangnya cuma hiasan.
        //
        // Yang diatur hanya baris yang ada di hasil pencarian sekarang. Baris di
        // luar rentang pencarian tidak tersentuh, jadi mempersempit tanggal tidak
        // diam-diam membuang isi invoice.
        var tambah = 0, buang = 0, ubahDisc = 0;
        var sisa = [];

        // Dinilai dari DATA, jadi baris yang dicentang lalu tersembunyi oleh
        // kotak filter tetap ikut terhitung.
        var dalamHasil = {};
        sjTerakhir.forEach(function (r) {
            dalamHasil[String(r.asal) + '|' + String(r.id_baris)] = r;
        });

        invBaris.forEach(function (x) {
            var r = dalamHasil[String(x.asal) + '|' + String(x.id_baris)];
            if (!r) { sisa.push(x); return; }        // di luar hasil pencarian
            if (!r._pilih) { buang++; return; }      // centangnya dilepas
            var disc = parseFloat(r._disc) || 0;
            if (disc !== (parseFloat(x.disc) || 0)) { x.disc = disc; ubahDisc++; }
            sisa.push(x);
        });

        sjTerakhir.forEach(function (r) {
            if (!r._pilih || sudahMasuk(r)) { return; }
            var salin = $.extend({}, r);
            salin.disc = parseFloat(r._disc) || 0;
            sisa.push(salin);
            tambah++;
        });

        if (!sisa.length) {
            Swal.fire({
                icon: 'warning',
                title: 'No row selected',
                text: 'An invoice needs at least one SJ row. Tick at least one before continuing.'
            });
            return;
        }

        // Pengaman terakhir: satu invoice = satu tanggal SJ. Kotak centangnya
        // sudah dikunci, tapi invoice lama bisa saja sudah tercampur.
        var bedaTgl = tanggalBeda(sisa);
        if (bedaTgl.length > 1) {
            Swal.fire({
                icon: 'warning',
                title: 'One invoice = one SJ date',
                html: 'The selected SJ carry <b>' + bedaTgl.length + '</b> different dates ('
                    + teksAman(bedaTgl.join(', ')) + '). Keep only the SJ of one date.',
                customClass: { popup: 'dn-swal' }
            });
            return;
        }

        invBaris = sisa;
        // DP & Return dibawa dari modal, sama seperti duplicate_data_so() di AR.
        $('#inv-dp').val($('#so-dp').val() || '');
        $('#inv-return').val($('#so-return').val() || '');
        $('#inv-table-sj').data('vat', $('#so-vat-11').is(':checked') ? 0.11 : 0);
        $('#inv-table-sj').data('dpcbd', nilai('#so-dpcbd'));
        gambarDetail();
        // WS milik SJ yang baru dipilih dibacakan ke Detail SO - itu yang wajib.
        lengkapiSoDariSj();
        modalSJ.hide();

        // Kalau ada yang dibuang, dikabarkan - itu perubahan yang tidak
        // langsung terlihat kalau barisnya banyak.
        if (buang) {
            var kabar = [];
            if (tambah)   { kabar.push('<b>' + tambah + '</b> row' + (tambah > 1 ? 's' : '') + ' added'); }
            kabar.push('<b>' + buang + '</b> row' + (buang > 1 ? 's' : '') + ' removed');
            if (ubahDisc) { kabar.push('<b>' + ubahDisc + '</b> discount' + (ubahDisc > 1 ? 's' : '') + ' changed'); }
            Swal.fire({
                icon: 'info',
                title: 'Detail updated',
                html: kabar.join(', ') + '.',
                customClass: { popup: 'dn-swal' }
            });
        }
    });

    // ---- Isi awal saat Edit ----
    // Baris SJ-nya dibaca dari yang tersimpan, bentuknya sudah disamakan dengan
    // keluaran pencarian SJ, jadi bisa langsung dipakai apa adanya.
    if (MODE_UBAH) {
        invBaris = @json($ubahBaris);
        // Baris SO yang tersimpan - itu yang ditagih.
        invSo = @json($ubahBarisSo);
        $('#inv-dp').val(@json($ubahPot['dp'] ? (float) $ubahPot['dp'] : ''));
        $('#inv-return').val(@json($ubahPot['retur'] ? (float) $ubahPot['retur'] : ''));
        $('#inv-table-sj').data('dpcbd', @json((float) $ubahPot['dp_cbd']));
        $('#inv-table-sj').data('vat', @json(((float) $ubahPot['vat_persen']) / 100));

        // Modal Add SJ ikut diselaraskan: kalau nanti user menambah baris lagi,
        // nilai-nilai ini yang dibawa balik ke form utama.
        $('#so-dp').val($('#inv-dp').val());
        $('#so-return').val($('#inv-return').val());
        $('#so-dpcbd').val(@json($ubahPot['dp_cbd'] ? (float) $ubahPot['dp_cbd'] : ''));
        // Invoice yang sudah ada mengikuti simpanannya - yang dulu 12% tetap
        // dianggap kena VAT, tarifnya sekarang 11%.
        $('#so-vat-11').prop('checked', @json(((float) $ubahPot['vat_persen']) > 0));

        gambarDetailSo();   // Detail SO dulu - itu yang menentukan totalnya
        gambarDetail();
    }

    // ---- Simpan booking ----
    var RUT_SIMPAN = @json(route('invoice-exim-local-simpan'));
    var RUT_UBAH   = @json(route('invoice-exim-local-perbarui'));
    var RUT_DAFTAR = @json(route('invoice-exim-local'));

    // Buka/tutup rincian rekap.
    $('#btn-lipat-rekap').on('click', function () {
        var buka = $('#inv-rekap-rinci').prop('hidden');
        $('#inv-rekap-rinci').prop('hidden', !buka);
        $(this).attr('aria-expanded', buka ? 'true' : 'false');
        $('#btn-lipat-rekap-teks').text(buka ? 'Hide details' : 'Show details');
    });

    $('#inv-btn-simpan').on('click', function () {
        // Semua yang wajib diperiksa dulu, lalu ditampilkan sekaligus - user
        // tidak perlu menekan Save berkali-kali untuk tahu apa saja yang kurang.
        var kurang = [];
        if (!$('#inv-customer').val())      { kurang.push('Billed To'); }
        if (!$('#inv-customer-ship').val()) { kurang.push('Shipped To'); }
        if (!$('#inv-doc-type').val())      { kurang.push('Document Type'); }
        if (!$('#inv-tgl').val())           { kurang.push('Invoice Date'); }
        // Yang wajib baris SO (WS), bukan SJ: invoice sering harus terbit
        // sebelum barangnya keluar. Memilih SJ pun ikut mengisi Detail SO.
        // Harus ada isinya - dari SJ atau dari SO, tidak harus dua-duanya.
        // Invoice yang terbit sebelum barangnya keluar baru punya baris SO;
        // yang barangnya sudah keluar bisa langsung punya SJ saja.
        if (!invSo.length && !invBaris.length) {
            kurang.push('SJ or SO (use "Add SJ / WS" to pick at least one row)');
        }

        // SJ tanpa SO harus punya harga - server menolaknya juga, tapi lebih
        // enak ketahuan di sini sambil kotaknya masih di depan mata.
        var belumHarga = barisTanpaHarga();
        if (belumHarga.length) {
            var sj = [];
            belumHarga.forEach(function (r) {
                var n = String(r.shipping_number || r.sj || '');
                if (sj.indexOf(n) === -1) { sj.push(n); }
            });
            kurang.push('Unit Price on ' + belumHarga.length + ' row' + (belumHarga.length > 1 ? 's' : '')
                + ' in Detail SJ (' + teksAman(sj.slice(0, 3).join(', '))
                + (sj.length > 3 ? ', ...' : '') + ') - these SJ have no SO');
        }

        // Mata uang baris tanpa SO juga tidak ada di database - lihat selCurr().
        var belumCurr = barisTanpaCurr();
        if (belumCurr.length) {
            kurang.push('Currency on ' + belumCurr.length + ' row' + (belumCurr.length > 1 ? 's' : '')
                + ' in Detail SJ - these SJ have no SO, so their currency is picked here');
        }
        // Satu invoice satu mata uang: angka rekap di bawah menjumlahkan semua
        // baris, jadi campuran mata uang hasilnya tidak berarti apa-apa.
        var mata = currDipakai();
        if (mata.length > 1) {
            kurang.push('One currency per invoice &ndash; Detail SJ now mixes <b>'
                + teksAman(mata.join(', ')) + '</b>');
        }

        if (kurang.length) {
            Swal.fire({
                icon: 'warning',
                title: 'Please complete the form',
                html: 'The following must be filled in before saving:<ul class="dn-swal-kurang"><li>'
                    + kurang.join('</li><li>') + '</li></ul>',
                confirmButtonText: 'OK',
                customClass: { popup: 'dn-swal' }
            });
            return;
        }

        var $tb = $(this);

        // Ditunjukkan dulu apa yang akan disimpan - sekali disimpan, nomornya
        // terpakai dan tidak bisa ditarik lagi.
        var ringkas = ''
            + '<div class="dn-swal-ringkas">'
            + '<div><span>Billed To</span><b>' + teksAman($('#inv-customer').find(':selected').text()) + '</b></div>'
            + '<div><span>Profit Center</span><b>' + teksAman($('#inv-pc').find(':selected').text()) + '</b></div>'
            + '<div><span>Document</span><b>' + teksAman($('#inv-doc-type').val())
            + ($('#inv-doc-number').val() ? ' / ' + teksAman($('#inv-doc-number').val()) : '') + '</b></div>'
            + '<div><span>SJ rows</span><b>' + invBaris.length + '</b></div>'
            + '<div class="dn-swal-grand"><span>Grand Total</span><b>' + teksAman($('#inv-grandtotal').val()) + '</b></div>'
            + '</div>'
            + (MODE_UBAH
                ? '<p class="dn-swal-catatan">The invoice number stays the same.'
                    + ' The previous SJ rows will be replaced by the list above.</p>'
                : '<p class="dn-swal-catatan">The invoice number is assigned when it is saved,'
                    + ' so it may differ from the one shown on the form. It will be saved with status DRAFT.</p>');

        Swal.fire({
            icon: 'question',
            title: MODE_UBAH ? 'Update this invoice?' : 'Save this invoice?',
            html: ringkas,
            showCancelButton: true,
            confirmButtonText: MODE_UBAH
                ? '<i class="fa fa-save"></i> Yes, update it'
                : '<i class="fa fa-save"></i> Yes, save it',
            cancelButtonText: 'Cancel',
            reverseButtons: true,
            focusCancel: true,
            customClass: { popup: 'dn-swal' }
        }).then(function (pilih) {
            if (!pilih.isConfirmed) { return; }
            kirimSimpan($tb);
        });
    });

    function kirimSimpan($tb) {
        $tb.prop('disabled', true);
        Swal.fire({
            title: MODE_UBAH ? 'Updating...' : 'Saving...',
            allowOutsideClick: false, allowEscapeKey: false,
            showConfirmButton: false, customClass: { popup: 'dn-swal' },
            didOpen: function () { Swal.showLoading(); }
        });

        $.ajax({
            url: MODE_UBAH ? RUT_UBAH : RUT_SIMPAN,
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            data: {
                id: ID_UBAH,
                profit_center: $('#inv-pc').val(),
                id_customer: $('#inv-customer').val(),
                id_customer_ship: $('#inv-customer-ship').val() || '',
                id_type: $('#inv-type').val(),
                doc_type: $('#inv-doc-type').val(),
                tgl_invoice: tglIso('#inv-tgl'),
                doc_number: $('#inv-doc-number').val(),
                dp: nilai('#inv-dp'),
                dp_cbd: $('#inv-table-sj').data('dpcbd') || 0,
                retur: nilai('#inv-return'),
                vat_persen: (parseFloat($('#inv-table-sj').data('vat')) || 0) * 100,
                // Cuma penunjuk baris + diskonnya - nilainya dihitung ulang di
                // server. Kecuali harga SJ tanpa SO: itu memang cuma ada di
                // layar ini, jadi ikut dikirim (server tetap mengabaikannya
                // untuk baris yang punya SO).
                // Baris SO juga cuma penunjuk: nama, harga & qty pesanan dibaca
                // ulang di server. Yang dipercaya dari sini cuma baris SO mana
                // yang dipilih, qty yang ditagih, dan diskonnya.
                baris_ws: invSo.map(function (r) {
                    return { id_so_det: r.id_so_det, qty: r.qty, disc: r.disc };
                }),
                baris: invBaris.map(function (r) {
                    var b = { id_baris: r.id_baris, disc: r.disc || 0 };
                    if (perluHarga(r)) { b.unit_price = r.unit_price; b.curr = r.curr; }
                    return b;
                })
            }
        }).done(function (res) {
            Swal.fire({
                icon: 'success',
                title: MODE_UBAH ? 'Invoice updated' : 'Invoice saved',
                html: 'Invoice <b>' + teksAman(res.no_invoice) + '</b> '
                    + (MODE_UBAH ? 'has been updated. Status stays <b>DRAFT</b>.'
                                 : 'has been saved with status <b>DRAFT</b>.'),
                confirmButtonText: 'OK',
                allowOutsideClick: false,
                customClass: { popup: 'dn-swal' }
            }).then(function () { window.location.href = RUT_DAFTAR; });
        }).fail(function (x) {
            $tb.prop('disabled', false);
            var bawaan = MODE_UBAH ? 'Update failed, please try again.' : 'Save failed, please try again.';
            var p = (x.responseJSON && x.responseJSON.pesan) ? x.responseJSON.pesan : bawaan;
            Swal.fire({
                icon: 'error',
                title: MODE_UBAH ? 'Update failed' : 'Save failed',
                text: p,
                customClass: { popup: 'dn-swal' }
            });
        });
    }
});
</script>
@endpush
