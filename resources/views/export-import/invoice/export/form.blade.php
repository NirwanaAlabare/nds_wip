{{-- ===========================================================================
     Create & Edit Invoice Export (EXIM).

     Bentuknya beda jauh dari Invoice Local. Local itu dua lapis: header lalu
     daftar baris SJ yang langsung tercetak satu-satu. Export tiga lapis:

       1. Pihak-pihak      Shipper, Seller, Purchaser, Receiver (4, bukan 2)
       2. Shipment Details baris 8-24 - boleh lebih dari satu baris
       3. Invoice Summary  baris 25-31 - sudah diringkas per warna

     Baris FG/OUT tetap dipilih seperti Local, tapi TIDAK ikut tercetak. Dia
     dipakai dua hal: jadi asal angka Qty Invoiced, dan nanti dibaca Create
     Invoice di AR untuk menandai bppb. Makanya tiap baris SJ ditandai masuk
     ke baris Invoice Summary yang mana.

     Satu layar dipakai dua mode. Saat $ubah terisi (dikirim editExport()),
     layar ini membuka invoice yang sudah tersimpan: isiannya terisi, nomor
     invoice & profit center dikunci, dan tombolnya jadi Update.

     Skin-nya sama dengan Invoice Local (../_skin.blade.php).
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
    $ubah        = $ubah        ?? null;
    $ubahHeader  = $ubahHeader  ?? [];
    $ubahKirim   = $ubahKirim   ?? [];
    $ubahRingkas = $ubahRingkas ?? [];
    $ubahBaris   = $ubahBaris   ?? [];
    $ubahBarisSo = $ubahBarisSo ?? [];
    $ubahPot     = $ubahPot     ?? ['dp' => 0, 'dp_cbd' => 0, 'retur' => 0, 'vat_persen' => 0];
    // Nilai header export yang tersimpan (mode edit), atau bawaan (create).
    $isiH = function ($kunci, $bawaan = '') use ($ubahHeader) {
        return isset($ubahHeader[$kunci]) ? $ubahHeader[$kunci] : $bawaan;
    };
@endphp
{{-- dn-kontrol-sm: tombol, isian & select2 ukuran kecil - sama di semua halaman invoice (lihat _skin). --}}
<div class="nag-skin dn-kontrol-sm dn-form-invoice">

    {{-- ===== Kepala halaman =====
         Judul layar saja - Save & Back tetap di kaki kartu Summary, dekat
         angka yang baru saja diperiksa sebelum disimpan. --}}
    <div class="dn-kepala-halaman">
        <div class="dn-kepala-kiri">
            <span class="dn-kepala-ikon"><i class="fas fa-file-export"></i></span>
            <div>
                <h1 class="dn-kepala-judul">{{ $ubah ? 'Edit Invoice Export' : 'Create Invoice Export' }}</h1>
                <p class="dn-kepala-sub">
                    {{ $ubah ? 'Ubah booking invoice export yang masih DRAFT' : 'Booking invoice export EXIM' }}
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
                        <div class="col-md-7">
                            <div class="form-group">
                                <label for="inv-no">Invoice Number</label>
                                <input type="text" class="form-control" id="inv-no" name="no_invoice"
                                    value="{{ $noInvoice }}" readonly>
                            </div>
                        </div>
                        <div class="col-md-5">
                            <div class="form-group">
                                <label for="inv-pc">Profit Center</label>
                                {{-- Saat Edit dikunci: profit center sudah menyatu dengan nomor invoice. --}}
                                <select class="form-control select2bs4" id="inv-pc" name="profit_center"
                                    {{ $ubah ? 'disabled' : '' }}>
                                    @foreach ($profitCenter as $pc)
                                        <option value="{{ $pc->kode_pc }}"
                                            {{ $ubah && $ubah['profit_center'] === $pc->kode_pc ? 'selected' : '' }}>{{ $pc->nama_pc }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-7">
                            <div class="form-group">
                                {{-- Baris 2: dipakai kalau buyer minta penomoran sendiri. --}}
                                <label for="inv-no2">Invoice Number #2</label>
                                <input type="text" class="form-control" id="inv-no2" name="no_invoice_2"
                                    value="{{ $isiH('no_invoice_2') }}" placeholder="Optional" maxlength="255" autocomplete="off">
                            </div>
                        </div>
                        <div class="col-md-5">
                            <div class="form-group">
                                <label for="inv-tgl">Invoice Date <span class="dn-wajib" title="Required">*</span></label>
                                {{-- Tidak diketik: ikut tanggal SJ yang dipilih, jadi readonly.
                                     Nilainya diambil lewat tglIso() di JS. --}}
                                <input type="text" class="form-control dn-tgl dn-tgl-ikut" id="inv-tgl" name="tgl_inv"
                                    value="{{ $isiH('tgl_invoice') ? date('j M Y', strtotime($isiH('tgl_invoice'))) : now()->format('j M Y') }}" autocomplete="off" readonly
                                    title="Follows the SJ date">
                                <small class="dn-tgl-ket"><i class="fas fa-truck"></i> Follows the SJ date</small>
                                {{-- Muncul kalau tanggalnya baru saja bergeser karena SJ
                                     yang dilengkapi di layar Edit. --}}
                                <small class="dn-tgl-pindah" id="inv-tgl-pindah" hidden></small>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-7">
                            <div class="form-group">
                                <label for="inv-type">Invoice Type</label>
                                <select class="form-control select2bs4" id="inv-type" name="id_type">
                                    @foreach ($tipe as $t)
                                        <option value="{{ $t->id }}"
                                            {{ $ubah && (int) $ubah['id_type'] === (int) $t->id ? 'selected' : '' }}>{{ $t->type }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-5">
                            <div class="form-group">
                                <label for="inv-shipp">Shipp</label>
                                {{-- Selalu Export: nomor invoice memakai kode E. --}}
                                <input type="text" class="form-control" id="inv-shipp" name="shipp"
                                    value="{{ $shipp }}" readonly>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-7">
                            <div class="form-group mb-0">
                                <label for="inv-doc-type">Document Type</label>
                                <select class="form-control select2bs4" id="inv-doc-type" name="doc_type">
                                    @foreach ($docType as $dt)
                                        <option value="{{ $dt }}" {{ $ubah && $ubah['doc_type'] === $dt ? 'selected' : '' }}>{{ $dt }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-5">
                            <div class="form-group">
                                <label for="inv-doc-number">Document Number</label>
                                <input type="text" class="form-control" id="inv-doc-number"
                                    name="doc_number" value="{{ $ubah['doc_number'] ?? '' }}" autocomplete="off">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-7">
                            <div class="form-group mb-0">
                                <label for="inv-notes">Invoice Notes</label>
                                <input type="text" class="form-control" id="inv-notes"
                                    value="{{ $isiH('invoice_notes') }}" placeholder="e.g. (NO FS)" autocomplete="off">
                            </div>
                        </div>
                        <div class="col-md-5">
                            <div class="form-group mb-0">
                                <label for="inv-reff">Refference</label>
                                <input type="text" class="form-control" id="inv-reff"
                                    value="{{ $isiH('reference') }}" placeholder="e.g. 265K0104" maxlength="255" autocomplete="off">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ===== Kanan: empat pihak ===== --}}
        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="card-title"><i class="fas fa-people-arrows"></i> Parties</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                {{-- Shipper selalu perusahaan sendiri, jadi tidak dipilih.
                                     Alamatnya tetap boleh dirapikan sebelum dicetak. --}}
                                <label for="inv-shipper-nama">Shipper</label>
                                <input type="hidden" id="inv-shipper" name="id_shipper"
                                    value="{{ $shipper['id_supplier'] ?? '' }}">
                                <input type="text" class="form-control" id="inv-shipper-nama"
                                    value="{{ $shipper['supplier'] ?? '' }}" readonly>
                            </div>
                            <div class="form-group">
                                <textarea class="form-control dn-alamat" id="inv-shipper-alamat"
                                    rows="3" placeholder="Shipper address">{{ $ubah ? $isiH('shipper_alamat') : ($shipper['alamat'] ?? '') }}</textarea>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                {{-- Seller inilah yang nanti mengisi Billed To & Shipped To
                                     di tbl_book_invoice - bukan Purchaser atau Receiver. --}}
                                <label for="inv-seller">Seller <span class="dn-wajib" title="Required">*</span> <span class="dn-label-bantu">(Billed To &amp; Shipped To)</span></label>
                                <select class="form-control select2bs4" id="inv-seller" name="id_seller">
                                    <option value="">-- Select seller --</option>
                                    @foreach ($customer as $c)
                                        <option value="{{ $c->Id_Supplier }}"
                                            data-alamat="{{ $c->alamat ?? '' }}"
                                            {{ $ubah && (string) $isiH('id_seller') === (string) $c->Id_Supplier ? 'selected' : '' }}>{{ $c->Supplier }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group">
                                <textarea class="form-control dn-alamat" id="inv-seller-alamat"
                                    rows="3" placeholder="Seller address">{{ $isiH('seller_alamat') }}</textarea>
                            </div>
                        </div>
                    </div>

                    {{-- Purchaser & Receiver diketik bebas - tidak diambil dari master. --}}
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="inv-purchaser">Purchaser <span class="dn-wajib" title="Required">*</span></label>
                                <input type="text" class="form-control" id="inv-purchaser"
                                    value="{{ $isiH('purchaser_nama') }}" placeholder="Purchaser name" maxlength="255" autocomplete="off">
                            </div>
                            <div class="form-group mb-0">
                                <textarea class="form-control dn-alamat dn-alamat-tinggi" id="inv-purchaser-alamat"
                                    rows="5" placeholder="Purchaser address">{{ $isiH('purchaser_alamat') }}</textarea>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="inv-receiver">Receiver / Ship To <span class="dn-wajib" title="Required">*</span></label>
                                <input type="text" class="form-control" id="inv-receiver"
                                    value="{{ $isiH('receiver_nama') }}" placeholder="Receiver name" maxlength="255" autocomplete="off">
                            </div>
                            <div class="form-group mb-0">
                                <textarea class="form-control dn-alamat dn-alamat-tinggi" id="inv-receiver-alamat"
                                    rows="5" placeholder="Receiver address">{{ $isiH('receiver_alamat') }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ===== Shipment Details: baris 8-24, boleh lebih dari satu ===== --}}
    <div class="card">
        <div class="card-header dn-kepala-aksi">
            <h5 class="card-title"><i class="fas fa-ship"></i> Shipment Details</h5>
            <button type="button" class="btn btn-dn-kosongkan btn-sm" id="btn-kosongkan-kirim" disabled>
                <i class="fas fa-eraser"></i> Clear All
            </button>
            <button type="button" class="btn btn-dn-create btn-sm" id="btn-tambah-kirim">
                <i class="fa fa-plus"></i> Add Data
            </button>
        </div>
        <div class="card-body">
            <div class="dn-table-muat-wrap dn-table-tinggi">
                <table id="inv-table-kirim" class="dn-table dn-table-muat" style="width:100%">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Dest Purchase</th>
                            <th>Style NO</th>
                            <th>Brand</th>
                            <th>Currency</th>
                            <th>Final Destination</th>
                            <th>Ship Mode</th>
                            <th>Gross Weight</th>
                            <th>Net Weight</th>
                            <th>Net Net Weight</th>
                            <th>Carton</th>
                            <th>Product Description</th>
                            <th class="dn-tengah">Action</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- ===== Invoice Summary + Detail SJ dalam satu kartu =====
         Detail SJ sudah tinggal satu baris ringkasan, jadi tidak perlu kartu
         sendiri. Keduanya memang satu kesatuan: baris Invoice Summary diturunkan
         dari warna SJ, jadi tombol Add SJ & Clear All pantas ada di kepala kartu
         ini.

         Urutannya: ringkasan SJ (sumber) di atas, tabel Invoice Summary (yang
         diisi & dicetak) di bawah. --}}
    <div class="card">
        <div class="card-header dn-kepala-aksi">
            <h5 class="card-title"><i class="fas fa-layer-group"></i> Invoice Summary</h5>
            {{-- Clear All mengosongkan SJ sekaligus baris Invoice Summary-nya:
                 baris summary diturunkan dari warna SJ, jadi tidak punya tombol sendiri. --}}
            <button type="button" class="btn btn-dn-kosongkan btn-sm" id="btn-kosongkan-sj" disabled>
                <i class="fas fa-eraser"></i> Clear All
            </button>
            {{-- Satu tombol, dua sumber: SJ (barangnya sudah keluar) atau WS
                 (SJ-nya belum terbit). Pilihannya ditanyakan waktu diklik. --}}
            <button type="button" class="btn btn-dn-create btn-sm" id="inv-btn-so">
                <i class="fa fa-plus"></i> Add SJ / WS
            </button>
        </div>
        <div class="card-body">
            {{-- ----- Detail SO (WS): dilipat -----
                 Dipakai kalau SJ-nya belum terbit. Bentuk barisnya sama dengan
                 Detail SJ, jadi Invoice Summary di bawah memperlakukan keduanya
                 dengan rumus yang sama. Letaknya di atas Detail SJ: ini yang
                 lebih dulu ada, SJ-nya menyusul. --}}
            <div class="dn-sj-pita">
                <div class="dn-lipat-strip">
                    <span class="dn-sj-label"><i class="fas fa-clipboard-list"></i> Detail SO</span>
                    <div class="dn-lipat-ringkas" id="so-ringkas">
                        <span class="dn-lipat-kosong">No WS selected yet.</span>
                    </div>
                    <button type="button" class="btn btn-dn-lipat" id="btn-lipat-so"
                        aria-expanded="false" aria-controls="so-isi" hidden>
                        <span id="btn-lipat-so-teks">Show details</span>
                        <i class="fas fa-chevron-down dn-lipat-panah"></i>
                    </button>
                </div>
                <div class="dn-table-scroll dn-table-tinggi" id="so-isi" hidden>
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
                                <th style="width:52px" class="dn-tengah">Action</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                        <tfoot class="dn-sj-jumlah" id="inv-so-jumlah" hidden>
                            <tr>
                                <td colspan="4" id="inv-so-jumlah-ws"></td>
                                <td colspan="5" class="dn-angka">Total Qty</td>
                                <td class="dn-angka dn-sj-jumlah-qty" id="inv-so-jumlah-qty">0.00</td>
                                <td colspan="2"></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
            {{-- ----- Detail SJ: dilipat -----
                 Tidak ikut tercetak, dan angkanya sudah terlihat per warna di tabel
                 bawah. Cukup satu baris ringkasan; tabelnya dibuka kalau perlu. --}}
            <div class="dn-sj-pita">
                <div class="dn-lipat-strip">
                    <span class="dn-sj-label"><i class="fas fa-truck"></i> Detail SJ</span>
                    <div class="dn-lipat-ringkas" id="sj-ringkas">
                        <span class="dn-lipat-kosong">No SJ selected yet.</span>
                    </div>
                    <button type="button" class="btn btn-dn-lipat" id="btn-lipat-sj"
                        aria-expanded="false" aria-controls="sj-isi" hidden>
                        <span id="btn-lipat-sj-teks">Show details</span>
                        <i class="fas fa-chevron-down dn-lipat-panah"></i>
                    </button>
                </div>
                <div class="dn-table-scroll dn-table-tinggi" id="sj-isi" hidden>
                    <table id="inv-table-sj" class="dn-table text-nowrap" style="width:100%">
                        <thead>
                            <tr>
                                <th>SO Number</th>
                                <th>Bppb Number</th>
                                <th>SJ Date</th>
                                <th>Shipping Number</th>
                                <th>WS#</th>
                                <th>Style No</th>
                                <th>Product Item</th>
                                <th>Color</th>
                                <th>Size</th>
                                <th>Curr</th>
                                <th>UOM</th>
                                <th>Qty</th>
                                <th>Unit Price</th>
                                {{-- Nilai TAGIH knitting - disembunyikan kalau barisnya
                                     memang tidak punya (garment). --}}
                                <th class="dn-sel-tagih">UOM Billing</th>
                                <th class="dn-sel-tagih">Qty Billing</th>
                                <th class="dn-sel-tagih">Unit Price Billing</th>
                                <th style="width:52px" class="dn-tengah">Action</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                        {{-- Jumlah SJ & Total Qty - Qty-nya persis di bawah kolom Qty. --}}
                        <tfoot class="dn-sj-jumlah" id="inv-sj-jumlah" hidden>
                            <tr>
                                <td colspan="4" id="inv-sj-jumlah-sj"></td>
                                <td colspan="7" class="dn-angka">Total Qty</td>
                                <td class="dn-angka dn-sj-jumlah-qty" id="inv-sj-jumlah-qty">0.00</td>
                                <td colspan="5"></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            {{-- ----- Invoice Summary per warna ----- --}}
            <div class="dn-table-scroll dn-table-tinggi">
                {{-- Lebar kolomnya diatur di _skin (persen, table-layout fixed) -
                     bukan di sini, supaya kolom teks tidak menyedot sisa ruang. --}}
                <table id="inv-table-ringkas" class="dn-table" style="width:100%">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Color Code <span class="dn-wajib" title="Required">*</span></th>
                            <th>Color Name</th>
                            <th>Total Pieces (Custom Units)</th>
                            <th>Qty Invoiced (Each)</th>
                            <th>Unit Cost (CM)</th>
                            <th>Unit Cost (FOB)</th>
                            <th>Disc (%)</th>
                            <th>Total (CM)</th>
                            <th>Total (FOB)</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- ===== Ringkasan nilai =====
         Daftar barisnya sama persis dengan Invoice Local (Total sampai Grand
         Total) supaya tidak perlu belajar dua layar. Bedanya angkanya dua
         kolom: CM yang ditagih tim AR, dan FOB untuk perizinan barang keluar.
         Semua isian (DP, DP/CBD, Return, VAT) diketik di modal Add SJ, sama
         seperti Local - di sini tinggal terbaca. --}}
    <div class="row justify-content-end">
        <div class="col-xxl-4 col-xl-5 col-lg-6 col-md-10">
            <div class="card">
                <div class="card-header dn-kepala-aksi">
                    <h5 class="card-title"><i class="fas fa-calculator"></i> Summary</h5>
                    <button type="button" class="btn btn-dn-lipat" id="btn-lipat-rekap"
                        aria-expanded="false" aria-controls="inv-rekap-rinci">
                        <span id="btn-lipat-rekap-teks">Show details</span>
                        <i class="fas fa-chevron-down dn-lipat-panah"></i>
                    </button>
                </div>
                <div class="card-body dn-ringkas">
                    {{-- Rekap dibuat seperti bagian bawah dokumen invoice, bukan deretan
                         kotak isian: semua angka di sini hasil hitungan, jadi tidak
                         boleh terlihat seolah bisa diketik. Potongan ditandai minus,
                         Grand Total ditonjolkan karena itu angka yang diputuskan. --}}
                    <table class="dn-rekap" id="inv-rekap-tabel">
                        <thead>
                            <tr>
                                <th></th>
                                <th>CM <span class="dn-rekap-curr" id="inv-curr-cm"></span></th>
                                <th>FOB <span class="dn-rekap-curr" id="inv-curr-fob"></span></th>
                                {{-- Nilai TAGIH knitting - lihat rekapTagih(). --}}
                                <th class="dn-sel-tagih">Billing <span class="dn-rekap-curr" id="inv-curr-tagih"></span></th>
                            </tr>
                        </thead>
                        {{-- Dilipat secara bawaan: yang diputuskan sebelum simpan cukup
                             Grand Total. Rinciannya tetap dihitung, dibuka kalau perlu. --}}
                        <tbody id="inv-rekap-rinci" hidden>
                            <tr>
                                <th>Total</th>
                                <td id="inv-total-cm">0.00</td>
                                <td id="inv-total-fob">0.00</td>
                                <td class="dn-sel-tagih" id="inv-total-tagih">0.00</td>
                            </tr>
                            <tr class="dn-rekap-kurang">
                                <th>Discount</th>
                                <td id="inv-disc-cm">0.00</td>
                                <td id="inv-disc-fob">0.00</td>
                                <td class="dn-sel-tagih" id="inv-disc-tagih">0.00</td>
                            </tr>
                            <tr class="dn-rekap-kurang">
                                <th>Down Payment</th>
                                <td id="inv-dp-cm">0.00</td>
                                <td id="inv-dp-fob">0.00</td>
                                <td class="dn-sel-tagih" id="inv-dp-tagih">0.00</td>
                            </tr>
                            <tr class="dn-rekap-kurang">
                                <th>DP/CBD from Invoice</th>
                                <td id="inv-dpcbd-cm">0.00</td>
                                <td id="inv-dpcbd-fob">0.00</td>
                                <td class="dn-sel-tagih" id="inv-dpcbd-tagih">0.00</td>
                            </tr>
                            <tr class="dn-rekap-kurang">
                                <th>Return</th>
                                <td id="inv-retur-cm">0.00</td>
                                <td id="inv-retur-fob">0.00</td>
                                <td class="dn-sel-tagih" id="inv-retur-tagih">0.00</td>
                            </tr>
                            <tr class="dn-rekap-sub">
                                <th>Total Without Tax</th>
                                <td id="inv-twot-cm">0.00</td>
                                <td id="inv-twot-fob">0.00</td>
                                <td class="dn-sel-tagih" id="inv-twot-tagih">0.00</td>
                            </tr>
                            <tr class="dn-rekap-tambah">
                                <th id="inv-label-vat">VAT</th>
                                <td id="inv-vat-cm">0.00</td>
                                <td id="inv-vat-fob">0.00</td>
                                <td class="dn-sel-tagih" id="inv-vat-tagih">0.00</td>
                            </tr>
                        </tbody>
                        <tfoot>
                            <tr class="dn-rekap-grand">
                                <th>Grand Total</th>
                                <td id="inv-grand-cm">0.00</td>
                                <td id="inv-grand-fob">0.00</td>
                                <td class="dn-sel-tagih" id="inv-grand-tagih">0.00</td>
                            </tr>
                        </tfoot>
                    </table>

                    {{-- Aksi ditaruh tepat di bawah Grand Total: itu angka terakhir
                         yang dilihat sebelum memutuskan simpan. --}}
                    <div class="dn-kaki">
                        <button type="button" class="btn btn-dn-simpan" id="inv-btn-simpan">
                            <i class="fa fa-save"></i> {{ $ubah ? 'Update Invoice' : 'Save Invoice' }}
                        </button>
                        <a href="{{ route('invoice-exim-export') }}" class="btn btn-dn-kembali">
                            <i class="fa fa-arrow-left"></i> Back
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ===================================================================
         Modal isian Shipment Details.
         Halaman depan cukup menampilkan tabelnya - isian sebanyak ini kalau
         dibentang di halaman utama malah mendorong bagian lain jauh ke bawah.
         =================================================================== --}}
    <div class="modal fade" id="modal-kirim" tabindex="-1" aria-hidden="true">
        {{-- Tanpa modal-dialog-scrollable: kelas itu memaksa dialog setinggi layar.
             Lebar & batas tingginya diatur di _skin (#modal-kirim) supaya tetap
             lega di laptop 14 inci. --}}
        <div class="modal-dialog modal-xl modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-ship"></i>
                        <span id="kirim-judul">Shipment Detail</span></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    {{-- Empat kolom seragam supaya label & kotaknya sejajar ke bawah. --}}
                    <div class="dn-petak-isian">
                        <div class="form-group">
                            <label for="k-dest">Dest Purchase</label>
                            <input type="text" class="form-control" id="k-dest" maxlength="255" autocomplete="off">
                        </div>
                        <div class="form-group">
                            <label for="k-style">Style NO</label>
                            <input type="text" class="form-control" id="k-style" maxlength="255" autocomplete="off">
                        </div>
                        <div class="form-group">
                            <label for="k-brand">Brand</label>
                            {{-- Pilihannya brand milik Seller yang dipilih (act_costing),
                                 diisi lewat JS. Boleh diketik sendiri kalau brandnya
                                 belum terdaftar di sana. --}}
                            <select class="form-control" id="k-brand">
                                <option value=""></option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="k-chanel">Chanel Description</label>
                            <input type="text" class="form-control" id="k-chanel" maxlength="255" autocomplete="off">
                        </div>

                        <div class="form-group">
                            <label for="k-curr">Currency</label>
                            <select class="form-control" id="k-curr">
                                @foreach ($mataUang as $mu)
                                    <option value="{{ $mu }}" {{ $mu === 'USD' ? 'selected' : '' }}>{{ $mu }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="k-payterm">Payment Term</label>
                            <input type="text" class="form-control" id="k-payterm" maxlength="255" autocomplete="off">
                        </div>
                        <div class="form-group">
                            <label for="k-findest">Final Destination</label>
                            <input type="text" class="form-control" id="k-findest" maxlength="10" autocomplete="off">
                        </div>
                        <div class="form-group">
                            <label for="k-origin">Country of Origin</label>
                            <input type="text" class="form-control" id="k-origin" maxlength="10" autocomplete="off">
                        </div>

                        <div class="form-group">
                            <label for="k-mode">Ship Mode</label>
                            <select class="form-control" id="k-mode">
                                <option value="OCEAN">OCEAN</option>
                                <option value="AIR">AIR</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="k-sale">Term of Sale</label>
                            <input type="text" class="form-control" id="k-sale" maxlength="255" autocomplete="off">
                        </div>
                        <div class="form-group">
                            <label for="k-transfer">Transfer Point</label>
                            <input type="text" class="form-control" id="k-transfer" maxlength="100" autocomplete="off">
                        </div>
                        <div class="form-group">
                            <label for="k-port">Port of Loading</label>
                            <input type="text" class="form-control" id="k-port" maxlength="100" autocomplete="off">
                        </div>

                        <div class="form-group">
                            <label for="k-gross">Total Gross Weight (KGS)</label>
                            <input type="number" step="0.001" class="form-control dn-angka-input" id="k-gross">
                        </div>
                        <div class="form-group">
                            <label for="k-net">Total Net Weight (KGS)</label>
                            <input type="number" step="0.001" class="form-control dn-angka-input" id="k-net">
                        </div>
                        <div class="form-group">
                            <label for="k-netnet">Total Net Net Weight (KGS)</label>
                            <input type="number" step="0.001" class="form-control dn-angka-input" id="k-netnet">
                        </div>
                        <div class="form-group">
                            <label for="k-carton">Total Carton</label>
                            <input type="number" step="1" class="form-control dn-angka-input" id="k-carton">
                        </div>

                        <div class="form-group dn-penuh mb-0">
                            <label for="k-desc">Product Description</label>
                            <textarea class="form-control" id="k-desc" rows="3"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-dn-kembali" data-bs-dismiss="modal">
                        <i class="fa fa-times"></i> Cancel
                    </button>
                    <button type="button" class="btn btn-dn-simpan" id="k-btn-simpan">
                        <i class="fa fa-check"></i> Apply
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- ===================================================================
         Modal Add SJ - sama cara kerjanya dengan Invoice Local, tapi tanpa
         blok DP/Return/VAT: invoice export tidak memakainya.
         =================================================================== --}}
    {{-- ===================================================================
         Modal Add WS - dipakai kalau SJ-nya belum terbit.

         Satu baris = satu WS; dicentang berarti seluruh baris SO-nya ikut,
         sama seperti modal Add SJ yang mengelompokkan per FG/OUT.
         =================================================================== --}}
    <div class="modal fade dn-modal-seragam" id="modal-add-ws" tabindex="-1" aria-hidden="true">
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
                                    value="{{ now()->startOfMonth()->format('j M Y') }}" autocomplete="off">
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
                                        {{-- Satu baris = satu WS + satu warna. Size digabung:
                                             yang ditagih per warna, dan qty-nya pun diketik ulang. --}}
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

    {{-- dn-modal-seragam: lebarnya disamakan dengan modal Add Shipment (lihat _skin). --}}
    <div class="modal fade dn-modal-seragam" id="modal-add-so" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fa fa-plus"></i> Add SJ</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body">
                    <div class="row">
                        <div class="col-lg-4 col-md-6">
                            <div class="form-group">
                                <label for="so-buyer">Buyer</label>
                                <select class="form-control" id="so-buyer">
                                    <option value="">ALL</option>
                                    @foreach ($buyer as $b)
                                        <option value="{{ $b->Id_Supplier }}">{{ $b->Supplier }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        {{-- Tanggal cukup selebar isinya - lihat .dn-filter-tgl di _skin. --}}
                        <div class="col-auto dn-filter-tgl">
                            <div class="form-group">
                                <label for="so-tgl-awal">SJ Date From</label>
                                <input type="text" class="form-control dn-tgl" id="so-tgl-awal"
                                    value="{{ now()->format('j M Y') }}" autocomplete="off">
                            </div>
                        </div>
                        <div class="col-auto dn-filter-tgl">
                            <div class="form-group">
                                <label for="so-tgl-akhir">SJ Date To</label>
                                <input type="text" class="form-control dn-tgl" id="so-tgl-akhir"
                                    value="{{ now()->format('j M Y') }}" autocomplete="off">
                            </div>
                        </div>
                        <div class="col-auto">
                            <div class="form-group dn-aksi-filter">
                                <button type="button" class="btn btn-dn-search" id="so-btn-cari">
                                    <i class="fa fa-search"></i> Search
                                </button>
                            </div>
                        </div>
                    </div>

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
                                            <th class="dn-angka">Total Price</th>
                                            {{-- Nilai TAGIH - cuma muncul kalau ada baris knitting. --}}
                                            <th class="dn-kol-tagih">UOM Billing</th>
                                            <th class="dn-angka dn-kol-tagih">Total Qty Billing</th>
                                            <th class="dn-angka dn-kol-tagih">Total Price Billing</th>
                                            <th style="width:52px" class="dn-tengah">
                                                <input type="checkbox" id="so-cek-semua" title="Select all">
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td class="dn-kosong" colspan="17">No SJ yet. Set the filter above, then press Search.</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            {{-- Satu invoice cuma boleh memuat SJ dari SATU tanggal - begitu
                                 ada yang dicentang, SJ bertanggal lain dikunci. --}}
                            <div class="dn-catatan-tgl" id="so-tgl-info" hidden></div>
                            <div class="dn-pilih-info" id="so-info-pilih">No SJ ticked yet.</div>
                        </div>
                    </div>

                    {{-- ----- Ringkasan nilai di dalam modal -----
                         Sama persis dengan modal Add SJ di Invoice Local: di sinilah
                         DP, DP/CBD, Return dan VAT diketik, lalu dibawa ke form utama
                         waktu Apply. Angkanya memakai harga CM. --}}
                    {{-- Ketentuan SJ ditaruh di ruang kosong sebelah Invoice
                         Summary - terbaca tepat waktu user memilih SJ, bukan
                         sesudah daftarnya kosong dan dia bingung sendiri. --}}
                    <div class="row justify-content-between">
                        <div class="col-xl-7 col-lg-7 col-md-12">
                            @include('export-import.invoice._ketentuan_sj')
                        </div>
                        <div class="col-xl-4 col-lg-5 col-md-8">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title"><i class="fas fa-calculator"></i> Invoice Summary</h5>
                                </div>
                                <div class="card-body dn-ringkas">
                                    <div class="form-group row">
                                        <label for="so-total" class="col-6 col-form-label">Total</label>
                                        <div class="col-6">
                                            <input type="text" class="form-control" id="so-total" placeholder="0.00" readonly>
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <label for="so-discount" class="col-6 col-form-label">Discount</label>
                                        <div class="col-6">
                                            <input type="text" class="form-control" id="so-discount" placeholder="0.00" readonly>
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <label for="so-dp" class="col-6 col-form-label">Down Payment</label>
                                        <div class="col-6">
                                            <input type="text" class="form-control dn-angka-input" id="so-dp" placeholder="0.00" autocomplete="off">
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <label for="so-dpcbd" class="col-6 col-form-label">DP/CBD from Invoice</label>
                                        <div class="col-6">
                                            <input type="text" class="form-control dn-angka-input" id="so-dpcbd" placeholder="0.00" autocomplete="off">
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <label for="so-return" class="col-6 col-form-label">Return</label>
                                        <div class="col-6">
                                            <input type="text" class="form-control dn-angka-input" id="so-return" placeholder="0.00" autocomplete="off">
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <label for="so-twot" class="col-6 col-form-label">Total Without Tax</label>
                                        <div class="col-6">
                                            <input type="text" class="form-control" id="so-twot" placeholder="0.00" readonly>
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <label class="col-6 col-form-label">VAT</label>
                                        <div class="col-6">
                                            {{-- Tarif yang dipakai sekarang cuma 11%; pilihan 12%
                                                 dihapus supaya tidak ada yang salah centang. --}}
                                            <div class="dn-vat">
                                                <div class="form-check form-check-inline">
                                                    <input class="form-check-input" type="checkbox" id="so-vat-11">
                                                    <label class="form-check-label" for="so-vat-11">Vat 11%</label>
                                                </div>
                                            </div>
                                            <input type="text" class="form-control mt-2" id="so-vat" placeholder="0.00" readonly>
                                        </div>
                                    </div>
                                    <div class="form-group row dn-grand mb-0">
                                        <label for="so-grandtotal" class="col-6 col-form-label">Grand Total</label>
                                        <div class="col-6">
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
                    {{-- "Apply": baris yang dicentang dipakai ke Invoice Summary,
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

    var RUT_NOMOR  = @json(route('invoice-exim-nomor'));
    var RUT_SIMPAN = @json(route('invoice-exim-export-simpan'));
    var RUT_DAFTAR = @json(route('invoice-exim-export'));
    var RUT_UBAH   = @json(route('invoice-exim-export-perbarui'));
    var RUT_SJ     = @json(route('invoice-exim-sj'));
    var RUT_WS     = @json(route('invoice-exim-ws'));
    var RUT_NEGARA = @json(route('invoice-exim-kode-negara'));
    var RUT_MEREK  = @json(route('invoice-exim-brand'));
    var NEGARA     = @json($negara);

    // Penanda mode. MODE_UBAH true berarti layar ini sedang mengubah invoice yang
    // sudah tersimpan. ID_UBAH juga dikirim waktu mencari SJ, supaya baris milik
    // invoice ini sendiri tidak dianggap sudah terpakai.
    var MODE_UBAH = @json((bool) $ubah);
    var ID_UBAH   = @json($ubah['id'] ?? null);

    $('.select2bs4').select2({ theme: 'bootstrap4', width: '100%' });

    // ---------- tanggal ----------
    @include('export-import.invoice._kalender')

    // ---------- pembantu ----------
    function teksAman(v) {
        return $('<div>').text(v === null || v === undefined ? '' : v).html();
    }
    function angka(v) {
        var n = parseFloat(v);
        return isNaN(n) ? '0.00' : n.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }
    function rupiah(v) {
        var n = parseFloat(v) || 0;
        return n.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }
    function nilai(sel) {
        return parseFloat(String($(sel).val() || '').replace(/,/g, '')) || 0;
    }

    /**
     * Brand dari nama Purchaser - salinan rumus Excel yang dipakai tim exim:
     *   =IF(LEFT(B14,2)="OL","OLD NAVY",IF(LEFT(B14,2)="TH","THE GAP",
     *     IF(LEFT(B14,2)="GA","GAP",IF(LEFT(B14,2)="GP","OLD NAVY"))))
     * Yang tidak cocok dikosongkan (di Excel hasilnya FALSE), lalu diketik sendiri.
     */
    /**
     * Brand dari SO - diambil dari baris SJ/WS yang dipilih (act_costing.brand).
     *
     * Brand-nya per kontrak/style, jadi kalau baris Shipment Details sudah
     * menyebut Style NO, yang dipakai brand milik style itu. Kalau belum,
     * barulah dipakai brand invoice ini - itu pun cuma kalau seluruh barisnya
     * memang satu brand; kalau bercampur, dibiarkan kosong supaya user yang
     * menentukan daripada diisi brand yang salah.
     */
    function merekDariSo(styleNo) {
        var gaya = $.trim(String(styleNo || '')).toUpperCase();
        var merek = {};
        invBaris.concat(invSo).forEach(function (r) {
            var m = $.trim(String(r.brand == null ? '' : r.brand));
            if (m === '') { return; }
            var g = $.trim(String(r.styleno == null ? '' : r.styleno)).toUpperCase();
            if (gaya !== '' && g !== gaya) { return; }
            merek[m] = true;
        });
        var daftar = Object.keys(merek);
        return daftar.length === 1 ? daftar[0] : '';
    }

    /** Brand untuk baris Shipment Details: dari SO dulu, baru tebakan lama. */
    function merekBaris(styleNo) {
        return merekDariSo(styleNo) || merekDariPurchaser($('#inv-purchaser').val());
    }

    function merekDariPurchaser(nama) {
        var dua = String(nama || '').trim().toUpperCase().slice(0, 2);
        if (dua === 'OL' || dua === 'GP') { return 'OLD NAVY'; }
        if (dua === 'TH') { return 'THE GAP'; }
        if (dua === 'GA') { return 'GAP'; }
        return '';
    }

    // ---- Pilihan Brand: milik Seller yang dipilih (act_costing) ----
    // Brandnya memang per customer, jadi daftarnya ikut Seller - bukan daftar
    // seluruh brand, supaya pilihannya pendek dan brand customer lain tidak
    // mungkin terpakai. Masih boleh diketik sendiri (select2 tags) kalau
    // brandnya belum terdaftar di act_costing - invoice tidak boleh tertahan
    // cuma karena master datanya belum lengkap.
    var merekSeller = [];

    /** Susun ulang pilihannya, nilainya dipertahankan. */
    function isiPilihanMerek(nilai) {
        var $s = $('#k-brand');
        var isi = $.trim(String(nilai == null ? '' : nilai));
        var daftar = merekSeller.slice();
        // Brand yang sudah tersimpan tapi tidak ada di daftar tetap dipakai,
        // supaya membuka invoice lama tidak diam-diam mengosongkannya.
        if (isi !== '' && daftar.indexOf(isi) < 0) { daftar.unshift(isi); }
        $s.empty().append($('<option>').val('').text(''));
        daftar.forEach(function (m) { $s.append($('<option>').val(m).text(m)); });
        // change.select2 - cuma menyegarkan tampilannya, tidak menyalakan
        // penanda "diketik sendiri" seperti change biasa.
        $s.val(isi).trigger('change.select2');
    }

    /** Ambil brand milik Seller yang sedang dipilih. */
    function muatMerekSeller() {
        var id = $('#inv-seller').val() || '';
        if (id === '') {
            merekSeller = [];
            isiPilihanMerek($('#k-brand').val());
            return;
        }
        $.getJSON(RUT_MEREK, { id_seller: id }).done(function (d) {
            merekSeller = (d && d.brand) ? d.brand : [];
            isiPilihanMerek($('#k-brand').val());
        }).fail(function () {
            // Daftarnya gagal diambil - isiannya tetap bisa diketik sendiri.
            merekSeller = [];
        });
    }

    $('#k-brand').select2({
        theme: 'bootstrap4', width: '100%', tags: true,
        placeholder: 'Select or type a brand',
        dropdownParent: $('#modal-kirim')
    });

    // ================= Shipment Details (baris 8-24) =================
    // Di halaman depan cuma tabelnya. Isiannya lewat modal - kalau dibentang
    // di sini, bagian Detail SJ dan Summary terdorong jauh ke bawah layar.
    var kirimBaris = [];
    var nomorKirim = 0;
    var kirimSedang = -1;           // baris yang sedang dibuka di modal, -1 = baru
    var modalKirim = new bootstrap.Modal(document.getElementById('modal-kirim'));

    // Isi yang memang selalu sama, sesuai spesifikasi.
    function kirimKosong() {
        return {
            _id: ++nomorKirim,
            dest_purchase: '', style_no: '',
            brand: merekBaris(''), _brandTangan: false,
            chanel_description: '',
            currency: 'USD', _currTangan: false, payment_term: '', final_destination: '',
            country_origin: 'ID', ship_mode: 'OCEAN', term_of_sale: '',
            transfer_point: 'JAKARTA,ID', port_of_loading: 'JAKARTA,ID',
            total_gross_weight: '', total_net_weight: '', total_net_net_weight: '',
            total_carton: '', product_description: ''
        };
    }

    // Kotak isian di modal <-> kunci data. Satu daftar saja, dipakai dua arah,
    // jadi tidak mungkin ada kolom yang terisi saat dibuka tapi hilang saat
    // disimpan (atau sebaliknya).
    var PETA_KIRIM = {
        '#k-dest': 'dest_purchase', '#k-style': 'style_no', '#k-brand': 'brand',
        '#k-chanel': 'chanel_description', '#k-curr': 'currency',
        '#k-payterm': 'payment_term', '#k-findest': 'final_destination',
        '#k-origin': 'country_origin', '#k-mode': 'ship_mode', '#k-sale': 'term_of_sale',
        '#k-transfer': 'transfer_point', '#k-port': 'port_of_loading',
        '#k-gross': 'total_gross_weight', '#k-net': 'total_net_weight',
        '#k-netnet': 'total_net_net_weight', '#k-carton': 'total_carton',
        '#k-desc': 'product_description'
    };

    function bukaKirim(i) {
        kirimSedang = i;
        var r = (i >= 0 && kirimBaris[i]) ? kirimBaris[i] : kirimKosong();
        // Pilihan brandnya disusun lebih dulu - kalau optionnya belum ada,
        // .val() di bawah ini tidak akan kena.
        isiPilihanMerek(r.brand);
        // Penanda "diketik sendiri" berlaku per baris, jadi direset tiap
        // modal dibuka - kalau tidak, sekali diketik, baris berikutnya ikut
        // terkunci dan Style NO tidak pernah mengisi brand lagi.
        $('#k-brand').data('tangan', false);
        Object.keys(PETA_KIRIM).forEach(function (sel) {
            $(sel).val(r[PETA_KIRIM[sel]]);
        });
        $('#k-brand').trigger('change.select2');
        $('#kirim-judul').text(i >= 0 ? 'Edit Shipment ' + (i + 1) : 'Add Shipment');
        modalKirim.show();
    }

    $('#k-btn-simpan').on('click', function () {
        var r = (kirimSedang >= 0 && kirimBaris[kirimSedang]) ? kirimBaris[kirimSedang] : kirimKosong();
        var currLama = r.currency;
        var brandLama = r.brand;
        Object.keys(PETA_KIRIM).forEach(function (sel) {
            r[PETA_KIRIM[sel]] = $(sel).val();
        });
        // Begitu mata uangnya dipilih sendiri, SJ tidak boleh menimpanya lagi.
        if (r.currency !== currLama) { r._currTangan = true; }
        // Begitu brand-nya diketik sendiri, nama Purchaser tidak menimpanya lagi.
        if (r.brand !== brandLama) { r._brandTangan = true; }
        if (kirimSedang < 0) { kirimBaris.push(r); }
        gambarKirim();
        modalKirim.hide();
    });

    function gambarKirim() {
        // Label mata uang di rekap mengikuti baris shipment.
        if (typeof hitungSemua === 'function') { setTimeout(function () { hitungSemua(true); }, 0); }
        // Clear All mati selama tabelnya kosong - tidak ada yang bisa dibersihkan.
        $('#btn-kosongkan-kirim').prop('disabled', !kirimBaris.length);
        var $b = $('#inv-table-kirim tbody').empty();
        if (!kirimBaris.length) {
            $b.html('<tr><td class="dn-kosong" colspan="13">'
                + '<div class="dn-kosong-isi">'
                + '<i class="fas fa-ship"></i>'
                + '<span>No shipment row yet.</span>'
                + '</div></td></tr>');
            return;
        }
        // Kolomnya HARUS sama persis dengan <thead>. Yang isinya selalu tetap
        // (Country of Origin, Transfer Point, Port of Loading) dan yang jarang
        // dilihat sekilas (Chanel Description, Payment Term, Term of Sale)
        // sengaja tidak ikut - lengkapnya tetap ada di modal.
        $b.html(kirimBaris.map(function (r, i) {
            function sel(kunci) {
                return '<td>' + teksAman(r[kunci]) + '</td>';
            }
            return '<tr>'
                + '<td class="dn-tengah"><b>' + (i + 1) + '</b></td>'
                + sel('dest_purchase') + sel('style_no') + sel('brand')
                + sel('currency') + sel('final_destination') + sel('ship_mode')
                + '<td class="dn-angka">' + angka(r.total_gross_weight) + '</td>'
                + '<td class="dn-angka">' + angka(r.total_net_weight) + '</td>'
                + '<td class="dn-angka">' + angka(r.total_net_net_weight) + '</td>'
                + '<td class="dn-angka">' + angka(r.total_carton) + '</td>'
                + '<td>' + teksAman(r.product_description) + '</td>'
                + '<td class="dn-tengah dn-aksi-sel">'
                + '  <button type="button" class="btn btn-primary btn-ubah-kirim" data-i="' + i + '"'
                + '    title="Edit this row"><i class="fas fa-pen"></i></button>'
                + '  <button type="button" class="btn btn-dn-buang btn-hapus-kirim" data-i="' + i + '"'
                + '    title="Remove this row"><i class="fas fa-times"></i></button>'
                + '</td>'
                + '</tr>';
        }).join(''));
    }

    $('#btn-tambah-kirim').on('click', function () { bukaKirim(-1); });
    $('#inv-table-kirim').on('click', '.btn-ubah-kirim', function () {
        bukaKirim(parseInt($(this).data('i'), 10));
    });

    $('#inv-table-kirim').on('click', '.btn-hapus-kirim', function () {
        // Boleh dihapus sampai habis - tabelnya memang mulai kosong.
        // Kelengkapannya diperiksa nanti waktu Save.
        kirimBaris.splice(parseInt($(this).data('i'), 10), 1);
        gambarKirim();
    });

    // Buang semua baris shipment sekaligus. Ditanya dulu: isian di modalnya
    // (berat, carton, deskripsi) ikut hilang dan tidak bisa dikembalikan.
    $('#btn-kosongkan-kirim').on('click', function () {
        var n = kirimBaris.length;
        if (!n) { return; }
        Swal.fire({
            icon: 'warning',
            title: 'Clear all shipment rows?',
            html: 'All <b>' + n + '</b> row' + (n > 1 ? 's' : '') + ' in Shipment Details will be removed,'
                + ' including the weights, cartons and descriptions typed in them.',
            showCancelButton: true,
            confirmButtonText: '<i class="fas fa-eraser"></i> Yes, clear all',
            cancelButtonText: 'No, keep them',
            reverseButtons: true,
            focusCancel: true,
            customClass: { popup: 'dn-swal' }
        }).then(function (pilih) {
            if (!pilih.isConfirmed) { return; }
            kirimBaris = [];
            kirimSedang = -1;
            gambarKirim();
        });
    });

    // ---- Final Destination ikut negara Receiver ----
    // Diisikan otomatis, tapi tetap boleh ditimpa: isi kolom country di
    // mastersupplier memang tidak seragam, jadi daftar ini tidak selalu tepat.
    function kodeDariNama(nama) {
        nama = String(nama || '').toUpperCase().trim();
        for (var i = 0; i < NEGARA.length; i++) {
            if (String(NEGARA[i].nama_negara).toUpperCase() === nama) { return NEGARA[i].kode; }
        }
        return '';
    }

    $('#inv-receiver-alamat, #inv-receiver').on('blur', function () {
        var teks = String($('#inv-receiver-alamat').val() || '');
        var baris = teks.split(/\r?\n/).filter(function (x) { return x.trim() !== ''; });
        if (!baris.length) { return; }
        var kode = kodeDariNama(baris[baris.length - 1]);   // negara biasanya di baris terakhir
        if (!kode) { return; }
        kirimBaris.forEach(function (r) {
            if (!r.final_destination) { r.final_destination = kode; }
        });
        gambarKirim();
    });

    // ---- Brand ikut SO (act_costing), baru nama Purchaser ----
    // Baris yang brand-nya sudah diketik sendiri tidak pernah ditimpa.
    function selaraskanMerek() {
        var berubah = false;
        kirimBaris.forEach(function (r) {
            if (r._brandTangan) { return; }
            var merek = merekBaris(r.style_no);
            if (merek !== '' && r.brand !== merek) { r.brand = merek; berubah = true; }
        });
        if (berubah) { gambarKirim(); }
    }

    $('#inv-purchaser').on('input', selaraskanMerek);

    // Style NO-nya diketik di modal: brandnya ikut style itu begitu diketik.
    $('#k-style').on('input', function () {
        if ($('#k-brand').data('tangan')) { return; }
        var merek = merekBaris($(this).val());
        if (merek !== '') { isiPilihanMerek(merek); }
    });
    // Isian pilihan - penandanya dari change, bukan input.
    $('#k-brand').on('change', function () { $(this).data('tangan', true); });

    // ---- Alamat ikut pilihan Shipper / Seller ----
    function ikutAlamat(selSelect, selAlamat) {
        $(selSelect).on('change', function () {
            var alamat = $(this).find(':selected').data('alamat') || '';
            if (!$(selAlamat).val()) { $(selAlamat).val(alamat); }
        });
    }
    // Shipper sudah terisi dari server, jadi cuma Seller yang perlu diikutkan.
    ikutAlamat('#inv-seller', '#inv-seller-alamat');

    // ---- Buyer di modal Add SJ & Add WS ikut Seller ----
    // Keduanya dari mastersupplier tipe C, jadi id-nya sama. Buyer hanya disetel
    // waktu Seller diganti: kalau Buyer diubah sendiri di modal, pilihan itu
    // dibiarkan sampai Seller diganti lagi.
    function buyerIkutSeller(sel) {
        var id = $('#inv-seller').val() || '';
        if (id === '') { return; }
        var $buyer = $(sel);
        var ada = $buyer.find('option').filter(function () { return this.value === id; }).length;
        if (!ada) { return; }
        $buyer.val(id).trigger('change');
    }

    $('#inv-seller').on('change', function () {
        buyerIkutSeller('#so-buyer');
        buyerIkutSeller('#ws-buyer');
        // Brandnya per customer - pilihannya ikut Seller yang dipilih.
        muatMerekSeller();
    });
    muatMerekSeller();

    // ================= Baris SJ (FG/OUT) =================
    // Ditahan di halaman ini saja, tidak lewat tabel temporary - alasannya sama
    // dengan Invoice Local.
    var invBaris = [];

    /**
     * Satu baris ringkasan Detail SJ, pengganti tabelnya selama dilipat:
     * jumlah baris FG/OUT, jumlah warna, dan total pcs.
     */
    function ringkasDetailSj() {
        var n = invBaris.length;
        if (!n) {
            $('#sj-ringkas').html('<span class="dn-lipat-kosong">No SJ selected yet.</span>');
            $('#btn-lipat-sj').prop('hidden', true);
            return;
        }
        var pcs = invBaris.reduce(function (a, r) { return a + (parseFloat(r.qty) || 0); }, 0);
        var w = warnaUrut().length;
        var sj = hitungSj(invBaris);
        var bagian = [
            '<b>' + sj.toLocaleString('en-US') + '</b> SJ',
            '<b>' + w + '</b> colour' + (w > 1 ? 's' : ''),
            '<b>' + pcs.toLocaleString('en-US') + '</b> pcs'
        ];
        $('#sj-ringkas').html(bagian.join(' <span class="dn-lipat-titik">&middot;</span> '));
        $('#btn-lipat-sj').prop('hidden', false);
    }

    // Baris jumlah di kaki Detail SJ: berapa SJ dan total qty-nya.
    function segarkanJumlahSj() {
        var qty = invBaris.reduce(function (a, r) { return a + (parseFloat(r.qty) || 0); }, 0);
        $('#inv-sj-jumlah-sj').html('<b>' + hitungSj(invBaris) + '</b> SJ');
        $('#inv-sj-jumlah-qty').text(angka(qty));
        $('#inv-sj-jumlah').prop('hidden', !invBaris.length);
    }

    $('#btn-lipat-sj').on('click', function () {
        var buka = $('#sj-isi').prop('hidden');
        $('#sj-isi').prop('hidden', !buka);
        $(this).attr('aria-expanded', buka ? 'true' : 'false');
        $('#btn-lipat-sj-teks').text(buka ? 'Hide details' : 'Show details');
    });

    $('#btn-lipat-rekap').on('click', function () {
        var buka = $('#inv-rekap-rinci').prop('hidden');
        $('#inv-rekap-rinci').prop('hidden', !buka);
        $(this).attr('aria-expanded', buka ? 'true' : 'false');
        $('#btn-lipat-rekap-teks').text(buka ? 'Hide details' : 'Show details');
    });

    // ---- Harga baris SJ yang tidak punya SO ----
    // FG/OUT (garment) dan OFC/OUT (knitting) harganya ikut SO, jadi tetap
    // teks. Tipe lain - GK/OUT, GEN/OUT, WIP/OUT, GACC/OUT, SCR/OUT, SPCK/OUT -
    // keluar tanpa SO dan di database harganya 0, jadi diketik di sini. Unit
    // Cost (CM) di Invoice Summary ikut hasil ketikan ini, bukan sebaliknya:
    // satu warna bisa berisi beberapa baris SJ dengan harga berbeda.
    function perluHarga(r) {
        return Number(r.harga_manual) === 1;
    }

    function hargaKosong(r) {
        return perluHarga(r) && !((parseFloat(r.unit_price) || 0) > 0);
    }

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
        gambarDetailSj();
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

    // Detail SJ sengaja TIDAK digambar ulang tiap ketikan - kursornya akan
    // lompat keluar. Yang disegarkan cuma Invoice Summary & rekapnya.
    $('#inv-table-sj').on('input', '.dn-harga', function () {
        var r = invBaris[parseInt($(this).data('i'), 10)];
        if (!r) { return; }
        pakaiHargaBaris(r, $(this).val());
        $(this).toggleClass('dn-harga-kurang', hargaKosong(r));
        gambarRingkas();
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
        gambarDetailSj();
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

    function gambarDetailSj() {
        $('#btn-kosongkan-sj').prop('disabled', !invBaris.length && !invSo.length);
        ringkasDetailSj();
        segarkanJumlahSj();
        var $b = $('#inv-table-sj tbody').empty();
        if (!invBaris.length) {
            $b.html('<tr><td class="dn-kosong" colspan="17">'
                + '<div class="dn-kosong-isi">'
                + '<i class="fas fa-truck"></i>'
                + '<span>No SJ selected yet.</span>'
                + '</div></td></tr>');
        } else {
            $b.html(invBaris.map(function (r, i) {
                return '<tr>'
                    + '<td>' + teksAman(r.no_so) + '</td>'
                    + '<td>' + teksAman(r.sj) + '</td>'
                    + '<td>' + teksAman(r.bppbdate) + '</td>'
                    + '<td>' + teksAman(r.shipping_number) + '</td>'
                    + '<td>' + teksAman(r.ws) + '</td>'
                    + '<td>' + teksAman(r.styleno) + '</td>'
                    + '<td>' + teksAman(r.product_item) + '</td>'
                    + '<td>' + teksAman(r.color) + '</td>'
                    + '<td>' + teksAman(r.size) + '</td>'
                    + '<td>' + selCurr(r, i) + '</td>'
                    + '<td>' + teksAman(r.uom) + '</td>'
                    + '<td class="dn-angka">' + angka(r.qty) + '</td>'
                    + '<td class="dn-angka">' + selHarga(r, i) + '</td>'
                    + '<td class="dn-sel-tagih">' + teksAman(r.uom_tagih) + '</td>'
                    + '<td class="dn-angka dn-sel-tagih">' + angka(r.qty_tagih) + '</td>'
                    + '<td class="dn-angka dn-sel-tagih">' + angka(r.unit_price_tagih) + '</td>'
                    + '<td class="dn-tengah dn-aksi-sel"><button type="button" class="btn btn-dn-buang btn-buang-sj"'
                    + ' data-i="' + i + '" title="Remove this row"><i class="fas fa-times"></i></button></td>'
                    + '</tr>';
            }).join(''));
        }
        segarkanTanggalInvoice();
        gambarRingkas();
        // Kolom nilai tagih cuma dipakai knitting - ditentukan dari barisnya,
        // bukan dari pilihan profit center, jadi tidak ada yang perlu disetel.
        $('#inv-table-sj').toggleClass('is-knit', invBaris.some(function (r) {
            return $.trim(String(r.uom_tagih == null ? '' : r.uom_tagih)) !== ''
                || (parseFloat(r.total_price_tagih) || 0) > 0;
        }));
    }

    /** Invoice Date ikut tanggal SJ - kotaknya readonly, jadi tidak ada yang
     *  perlu diketik. Sekarang satu invoice cuma boleh satu tanggal SJ;
     *  tanggal paling akhir dipakai hanya sebagai jaring pengaman untuk
     *  invoice lama yang terlanjur tercampur. Belum ada SJ = tanggal
     *  bawaannya dibiarkan. */
    function segarkanTanggalInvoice() {
        var tgl = '';
        invBaris.forEach(function (r) {
            var t = String(r.bppbdate || '').slice(0, 10);
            if (/^\d{4}-\d{2}-\d{2}$/.test(t) && t > tgl) { tgl = t; }
        });
        if (!tgl || tgl === tglIso('#inv-tgl')) { return; }

        var lama = tglIso('#inv-tgl');
        tglSet('#inv-tgl', tgl);

        // Invoice Date ikut tanggal SJ. Di layar Edit itu bisa mengagetkan:
        // invoice yang tadinya bertanggal hari ini mendadak mundur ke tanggal
        // SJ, lalu hilang dari daftar karena daftarnya disaring tanggal itu.
        // Jadi perubahannya diberitahukan, bukan terjadi diam-diam.
        if (MODE_UBAH && lama) {
            $('#inv-tgl-pindah')
                .html('<i class="fas fa-circle-info"></i> Invoice Date follows the SJ: <b>'
                    + teksAman(tglTampil(lama)) + '</b> &rarr; <b>' + teksAman(tglTampil(tgl)) + '</b>')
                .prop('hidden', false);
            // Dicatat juga supaya ikut disebut di dialog perubahan - keterangan
            // di bawah kotaknya gampang terlewat kalau layarnya sedang digulir.
            TGL_PINDAH = { dari: lama, ke: tgl };
        }
    }

    /** "2026-09-10" -> "10 Sep 2026", sama dengan yang tertulis di kotaknya. */
    function tglTampil(iso) {
        var p = String(iso || '').slice(0, 10).split('-');
        if (p.length !== 3) { return String(iso || ''); }
        var bulan = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun',
                     'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        var b = bulan[parseInt(p[1], 10) - 1];
        return b ? (parseInt(p[2], 10) + ' ' + b + ' ' + p[0]) : String(iso || '');
    }

    $('#inv-table-sj').on('click', '.btn-buang-sj', function () {
        invBaris.splice(parseInt($(this).data('i'), 10), 1);
        // Qty Detail SO ikut berkurang - yang ditagih yang benar-benar dikirim.
        if (selaraskanQtySo()) { gambarDetailSo(); }
        gambarDetailSj();
    });


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
    // barangnya belum keluar, tapi nomor invoice sudah diminta buyer. Bentuk
    // barisnya sama persis dengan baris SJ (lihat wsGarment di
    // InvoiceEximController), jadi Invoice Summary di bawah tidak perlu tahu
    // baris itu datang dari mana.
    var invSo = [];

    // Pergeseran Invoice Date yang belum sempat diberitahukan lewat dialog.
    var TGL_PINDAH = null;

    /**
     * Baris yang menurunkan Invoice Summary.
     *
     * Aturannya PER WARNA, bukan digabung begitu saja: memilih SJ ikut mengisi
     * Detail SO, jadi kalau keduanya dijumlahkan warna itu terhitung dua kali.
     *
     *   warna punya baris SO  -> pakai baris SO (itu yang ditagih; qty-nya
     *                            boleh dikurangi untuk pengiriman sebagian)
     *   warna tanpa baris SO  -> pakai baris SJ (SJ yang tidak punya SO, mis.
     *                            GK/GEN/WIP - harganya diketik di Detail SJ)
     */
    function semuaBaris() {
        var adaSo = {};
        invSo.forEach(function (r) { adaSo[kunciWarna(r)] = true; });
        return invSo.concat(invBaris.filter(function (r) { return !adaSo[kunciWarna(r)]; }));
    }
    /** Harga satuan ditulis 4 angka di belakang koma - angka() cuma 2. */
    function angka4(v) {
        var n = parseFloat(v);
        return isNaN(n) ? '0.0000' : n.toLocaleString('en-US', { minimumFractionDigits: 4, maximumFractionDigits: 4 });
    }

    /** Berapa WS yang berbeda di dalam sekumpulan baris SO. */
    function hitungWs(baris) {
        var ws = {};
        baris.forEach(function (r) {
            var k = $.trim(String(r.ws == null ? '' : r.ws));
            if (k !== '') { ws[k] = true; }
        });
        return Object.keys(ws).length;
    }

    function ringkasDetailSo() {
        if (!invSo.length) {
            $('#so-ringkas').html('<span class="dn-lipat-kosong">No WS selected yet.</span>');
            $('#btn-lipat-so').prop('hidden', true);
            return;
        }
        var pcs = invSo.reduce(function (a, r) { return a + (parseFloat(r.qty) || 0); }, 0);
        var w = hitungWs(invSo);
        var bagian = [
            '<b>' + w.toLocaleString('en-US') + '</b> WS',
            '<b>' + invSo.length.toLocaleString('en-US') + '</b> row' + (invSo.length > 1 ? 's' : ''),
            '<b>' + pcs.toLocaleString('en-US') + '</b> pcs'
        ];
        $('#so-ringkas').html(bagian.join(' <span class="dn-lipat-titik">&middot;</span> '));
        $('#btn-lipat-so').prop('hidden', false);
    }

    function segarkanJumlahSo() {
        var qty = invSo.reduce(function (a, r) { return a + (parseFloat(r.qty) || 0); }, 0);
        $('#inv-so-jumlah-ws').html('<b>' + hitungWs(invSo) + '</b> WS');
        $('#inv-so-jumlah-qty').text(angka(qty));
        $('#inv-so-jumlah').prop('hidden', !invSo.length);
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
        segarkanJumlahSo();
        var $b = $('#inv-table-so tbody').empty();
        if (!invSo.length) {
            $b.html('<tr><td class="dn-kosong" colspan="12">'
                + '<div class="dn-kosong-isi">'
                + '<i class="fas fa-clipboard-list"></i>'
                + '<span>No WS selected yet.</span>'
                + '</div></td></tr>');
        } else {
            $b.html(invSo.map(function (r, i) {
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
                    + '<td class="dn-tengah dn-aksi-sel"><button type="button" class="btn btn-dn-buang btn-buang-so"'
                    + ' data-i="' + i + '" title="Remove this row"><i class="fas fa-times"></i></button></td>'
                    + '</tr>';
            }).join(''));
        }
        $('#btn-kosongkan-sj').prop('disabled', !invBaris.length && !invSo.length);
        gambarRingkas();
    }

    // Qty ditagih boleh dibetulkan di sini juga - pengiriman sebagian sering
    // baru ketahuan angkanya sesudah barisnya masuk.
    $('#inv-table-so').on('input', '.so-qty', function () {
        var r = invSo[parseInt($(this).data('i'), 10)];
        if (!r) { return; }
        r.qty = nilaiAngka($(this).val());
        r.total_price = r.qty * (parseFloat(r.unit_price) || 0);
        segarkanJumlahSo();
        ringkasDetailSo();
        gambarRingkas();
    });

    $('#inv-table-so').on('click', '.btn-buang-so', function () {
        invSo.splice(parseInt($(this).data('i'), 10), 1);
        gambarDetailSo();
    });

    // ---- Modal Add WS ----
    var modalWS = new bootstrap.Modal(document.getElementById('modal-add-ws'));
    var wsTerakhir = [];
    var wsGrup = [];

    /** Baris SO yang sudah masuk tidak ditawarkan dua kali. */
    function wsSudahMasuk(r) {
        for (var i = 0; i < invSo.length; i++) {
            if (String(invSo[i].id_baris) === String(r.id_baris)) { return true; }
        }
        return false;
    }

    /** Angka yang diketik user - koma ribuan diabaikan. */
    function nilaiAngka(v) {
        var n = parseFloat(String(v === null || v === undefined ? '' : v).replace(/,/g, ''));
        return isNaN(n) || n < 0 ? 0 : n;
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
                // Qty yang ditagih diketik di sini: pengiriman bisa sebagian,
                // jadi angkanya boleh kurang dari qty SO.
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

    // Qty diketik: total baris itu ikut berubah.
    $('#ws-table').on('input', '.ws-qty', function () {
        var r = wsTerakhir[parseInt($(this).data('i'), 10)];
        if (!r) { return; }
        r._qty = nilaiAngka($(this).val());
        $('#ws-table .ws-total[data-i="' + r._i + '"]')
            .text(angka(r._qty * (parseFloat(r.unit_price) || 0)));
        wsInfoPilih();
    });

    /** Kotak qty satu baris ikut centangnya - dibuka & disorot supaya
     *  angkanya bisa langsung diketik ulang. */
    function bukaQtyWs($cek, pilih) {
        var $isi = $cek.closest('tr').find('.ws-qty');
        $isi.prop('readonly', !pilih);
        return $isi;
    }
    function wsInfoPilih() {
        var ws = {}, baris = 0, qty = 0;
        wsTerakhir.forEach(function (r) {
            if (!r._pilih) { return; }
            ws[$.trim(String(r.ws || ''))] = true;
            baris++;
            qty += parseFloat(r._qty) || 0;
        });
        var n = Object.keys(ws).length;
        $('#ws-info-pilih').html(baris
            ? '<b>' + n + '</b> WS &middot; <b>' + baris + '</b> colour' + (baris > 1 ? 's' : '')
              + ' &middot; <b>' + angka(qty) + '</b> pcs ticked'
            : 'No WS ticked yet.');
    }

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
        $('#ws-table tbody').html('<tr><td class="dn-kosong" colspan="12">Loading...</td></tr>');
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
            $('#ws-table tbody').html('<tr><td class="dn-kosong" colspan="12">Could not load WS.</td></tr>');
            var p = (x.responseJSON && x.responseJSON.pesan) ? x.responseJSON.pesan : 'Please try again.';
            Swal.fire({ icon: 'error', title: 'Search failed', text: p, customClass: { popup: 'dn-swal' } });
        }).always(function () { $tb.prop('disabled', false); });
    });

    $('#modal-add-ws').on('shown.bs.modal', function () {
        var $s = $('#ws-buyer');
        if (!$s.hasClass('select2-hidden-accessible')) {
            $s.select2({ theme: 'bootstrap4', width: '100%', dropdownParent: $('#modal-add-ws') });
        }
        // Seller bisa saja dipilih sebelum modal ini pernah dibuka.
        buyerIkutSeller('#ws-buyer');
    });

    $('#ws-btn-apply').on('click', function () {
        var baru = [];
        wsTerakhir.forEach(function (r) {
            if (r._pilih && !wsSudahMasuk(r)) { baru.push(r); }
        });
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
     * Detail SO diisikan sendiri dari WS milik SJ yang baru dipilih.
     *
     * Yang wajib di invoice ini adalah baris SO (WS) - SJ boleh menyusul.
     * Jadi begitu SJ dipilih, WS-nya langsung dibacakan ke Detail SO supaya
     * user tidak perlu memilih hal yang sama dua kali.
     *
     * Qty & harga yang dibaca adalah qty & harga SO-nya, bukan qty SJ: Detail
     * SO memang menggambarkan pesanannya, bukan pengirimannya.
     */
    /** Qty SJ yang warnanya sama dengan baris SO ini, dalam WS yang sama. */
    function qtySjWarna(so) {
        var ws = $.trim(String(so.ws == null ? '' : so.ws));
        var w = kunciWarna(so);
        var qty = 0;
        invBaris.forEach(function (r) {
            if ($.trim(String(r.ws == null ? '' : r.ws)) !== ws) { return; }
            if (kunciWarna(r) !== w) { return; }
            qty += parseFloat(r.qty) || 0;
        });
        return qty;
    }

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

    /** Dipakai di tempat yang memang tidak perlu ditanya lagi (mis. baris SJ dihapus). */
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
        return teksAman(r.ws) + ' <span class="dn-lipat-titik">&middot;</span> ' + teksAman(kunciWarna(r));
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
     * Detail SO yang menentukan angka Invoice Summary, jadi perubahannya tidak
     * boleh terjadi diam-diam - apalagi kalau qty-nya disesuaikan sendiri atau
     * ada baris yang akan hilang. Pergeseran Invoice Date ikut disebut di sini
     * karena keterangan di bawah kotaknya gampang terlewat kalau layarnya
     * sedang digulir.
     */
    function konfirmasiUbahSo(rencana, warnaBaru, lanjut) {
        var tanpaSj = soTanpaSj();
        var tglPindah = TGL_PINDAH;
        TGL_PINDAH = null;   // sesudah disebut sekali, tidak diulang-ulang
        var adaUbah = rencana.length + warnaBaru.length + tanpaSj.length;
        if (!adaUbah && !tglPindah) { return; }

        var isi = [];
        if (adaUbah) {
            isi.push('<div class="dn-swal-ringkas-ubah">'
                + ringkasUbahSo(rencana, warnaBaru, tanpaSj) + '</div>');
        }
        if (tglPindah) {
            isi.push(bagianUbahSo('Invoice Date follows the SJ',
                'The invoice list is filtered by this date, so look for it on the new date.',
                [tglPindah], function (t) {
                    return '<li><b>' + teksAman(tglTampil(t.dari)) + '</b> &rarr; <b>'
                        + teksAman(tglTampil(t.ke)) + '</b></li>';
                }));
        }
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

        // Detail SO-nya sendiri tidak berubah, cuma tanggalnya yang bergeser -
        // tidak ada yang perlu disetujui, jadi cukup diberitahu.
        if (!adaUbah) {
            Swal.fire({
                icon: 'info',
                title: 'Invoice Date changed',
                html: isi.join(''),
                confirmButtonText: 'OK',
                width: 620,
                customClass: { popup: 'dn-swal' }
            }).then(function () { lanjut(); });
            return;
        }

        Swal.fire({
            icon: 'question',
            title: 'Update Detail SO to match the SJ?',
            html: isi.join('')
                + '<p class="dn-swal-catatan" style="margin-top:10px">Invoice Summary follows Detail SO, so these numbers go into the invoice.</p>',
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

    function lengkapiSoDariSj() {
        // Yang dicari WS + WARNA, bukan WS saja. Satu WS biasanya berisi
        // banyak warna; menarik semuanya bikin Invoice Summary punya baris
        // warna yang tidak ditagih - dan tiap baris itu menuntut Color Code
        // diisi. Per warna juga berarti menambah SJ warna kedua di WS yang
        // sama tetap terbaca, walau WS-nya sudah ada di Detail SO.
        var sudah = {};
        invSo.forEach(function (r) {
            sudah[$.trim(String(r.ws == null ? '' : r.ws)) + '|' + kunciWarna(r)] = true;
        });

        var warnaSj = {};
        var ws = [];
        invBaris.forEach(function (r) {
            var k = $.trim(String(r.ws == null ? '' : r.ws));
            if (k === '' || k === '-') { return; }
            var kunci = k + '|' + kunciWarna(r);
            if (sudah[kunci]) { return; }   // warna ini sudah ada di Detail SO
            warnaSj[kunci] = true;
            if (ws.indexOf(k) === -1) { ws.push(k); }
        });

        var rencana = rencanaQtySo();

        // Tidak ada warna baru yang perlu dibaca: cukup tanyakan qty-nya.
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
                if (!warnaSj[$.trim(String(r.ws == null ? '' : r.ws)) + '|' + kunciWarna(r)]) { return; }
                if (wsSudahMasuk(r)) { return; }
                var salin = $.extend({}, r);
                salin.qty_so = parseFloat(r.qty_so) || 0;
                // Yang ditagih = yang benar-benar dikirim. Warna ini SJ-nya
                // sudah ada, jadi qty-nya diambil dari SJ - bukan qty SO
                // penuh, yang biasanya lebih besar karena kirimnya bertahap.
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
            // Gagal membaca SO bukan alasan menggagalkan pilihan SJ-nya -
            // barisnya tetap bisa ditambahkan sendiri lewat Add WS.
            Swal.fire({
                icon: 'warning',
                title: 'SO rows not loaded',
                text: 'The SJ was added, but its SO rows could not be read. Use Add WS to add them.',
                customClass: { popup: 'dn-swal' }
            });
        });
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
                modalWS.show();
            } else if (h.isDenied) {
                rentangIkutBaris();
                modalSJ.show();
            }
        });
    }

    // ================= Invoice Summary (baris 25-31) =================
    // Satu baris = satu warna. Barisnya tidak ditambah sendiri, tapi diturunkan
    // dari warna yang ada di Detail SJ - jadi tidak mungkin ada warna yang
    // kelewat atau baris summary yang tidak punya FG/OUT.
    //
    // Yang diketik user disimpan per warna, bukan per nomor baris. Kalau daftar
    // SJ berubah dan urutan warnanya bergeser, ketikannya tetap menempel di
    // warna yang benar.
    var ringkasIsi = {};

    /**
     * Penentu baris summary: warnanya. SJ tanpa SO (GK/OUT dan kawan-kawan)
     * sering tidak punya warna di master barang - kalau dibiarkan, semuanya
     * menumpuk jadi satu baris "kosong", jadi dipakai nama barangnya. Server
     * memakai rumus yang sama (kunciWarna di InvoiceEximController).
     */
    function kunciWarna(r) {
        var w = String(r.color == null ? '' : r.color).trim();
        if (w !== '' && w !== '-') { return w; }
        var nama = String(r.product_item == null ? '' : r.product_item).trim();
        return nama !== '' ? nama : '-';
    }

    function warnaUrut() {
        var out = [];
        semuaBaris().forEach(function (r) {
            var w = kunciWarna(r);
            if (out.indexOf(w) === -1) { out.push(w); }
        });
        return out;
    }

    /** Baris (SJ maupun SO) dengan warna tertentu. */
    function sjWarna(w) {
        return semuaBaris().filter(function (r) { return kunciWarna(r) === w; });
    }

    /**
     * Satuan asli baris SJ sebuah baris summary - YRD, KG, PCS, dan seterusnya.
     *
     * Ini yang jadi pilihan satuan Total Pieces di samping SET: kalau barangnya
     * keluar dalam YRD, "PCS" tidak ada artinya. Kalau satu baris summary berisi
     * beberapa satuan, yang pertama yang dipakai - satuan lain tetap terlihat di
     * Detail SJ.
     */
    function uomWarna(w) {
        var uom = '';
        sjWarna(w).forEach(function (x) {
            var u = $.trim(String(x.uom || '')).toUpperCase();
            if (uom === '' && u !== '' && u !== '-') { uom = u; }
        });
        return uom !== '' ? uom : 'PCS';
    }

    function isiWarna(w) {
        if (!ringkasIsi[w]) {
            ringkasIsi[w] = {
                color_code: '', color_name: w, total_pieces: '', total_pieces_unit: uomWarna(w),
                unit_cost_fob: '', disc: ''
            };
        }
        return ringkasIsi[w];
    }

    /**
     * Angka CM satu warna, langsung dari SJ-nya: qty, nilai kotor (jumlah
     * total_price), dan Unit Cost = nilai / qty. Kalau satu warna punya harga
     * berbeda (mis. ukuran besar lebih mahal), totalnya tetap persis jumlah
     * nilai SJ. Server menghitung dengan cara yang sama waktu Save.
     */
    function cmWarna(w) {
        var qty = 0, kotor = 0;
        sjWarna(w).forEach(function (x) {
            qty   += parseFloat(x.qty) || 0;
            kotor += parseFloat(x.total_price) || 0;
        });
        return { qty: qty, kotor: kotor, unit: qty ? Math.round(kotor / qty * 10000) / 10000 : 0 };
    }

    /**
     * Qty yang dipakai Total (FOB): Total Pieces (satuan custom). Satuan asli
     * (YRD, KG, PCS, ...) selalu sama dengan Qty Invoiced; SET diketik sendiri.
     */
    function qtyFobWarna(w) {
        var r = isiWarna(w);
        return r.total_pieces_unit === 'SET' ? (parseFloat(r.total_pieces) || 0) : cmWarna(w).qty;
    }

    /** Qty, Total CM & Total FOB satu warna - satu rumus untuk semua tempat. */
    function hitungWarna(w) {
        var r = isiWarna(w);
        var c = cmWarna(w);
        var sisa = 1 - (parseFloat(r.disc) || 0) / 100;
        return {
            qty: c.qty,
            cm:  c.kotor * sisa,
            fob: qtyFobWarna(w) * (parseFloat(r.unit_cost_fob) || 0) * sisa
        };
    }

    function gambarRingkas() {
        var warna = warnaUrut();
        var $b = $('#inv-table-ringkas tbody').empty();
        if (!warna.length) {
            $b.html('<tr><td class="dn-kosong" colspan="10">'
                + '<div class="dn-kosong-isi">'
                + '<i class="fas fa-palette"></i>'
                + '<span>No colour yet - colours follow the SJ or WS rows you pick.</span>'
                + '</div></td></tr>');
            hitungSemua(true);
            return;
        }

        $b.html(warna.map(function (w, k) {
            var r = isiWarna(w);
            var h = hitungWarna(w);
            var qty = h.qty, cm = h.cm, fob = h.fob;

            function isi(kunci, kelas, tipe) {
                return '<input type="' + (tipe || 'text') + '" class="form-control form-control-sm dn-ringkas-isi '
                    + (kelas || '') + '" data-w="' + teksAman(w) + '" data-kunci="' + kunci + '"'
                    + ' value="' + teksAman(r[kunci]) + '" autocomplete="off"'
                    + (kunci === 'color_code' ? ' maxlength="255"' : '') + '>';
            }
            // Kotak terkunci: tampil seragam dengan kotak isian, tapi tidak bisa
            // diubah dan dilewati tombol Tab.
            function kunci(nilai, kelas, hasil) {
                return '<input type="text" class="form-control form-control-sm ' + (kelas || '') + '"'
                    + (hasil ? ' data-hasil="' + hasil + '"' : '')
                    + ' value="' + teksAman(nilai) + '" readonly tabindex="-1">';
            }

            // Total Pieces (Custom Units): satuannya dipilih di kiri.
            //   satuan asli SJ (YRD, KG, PCS, ...) -> ikut Qty Invoiced, terkunci.
            //   SET                                -> diketik sendiri, wajib diisi.
            var set = r.total_pieces_unit === 'SET';
            var uomAsli = uomWarna(w);
            if (!set) {
                r.total_pieces_unit = uomAsli;
                r.total_pieces = String(Math.round(qty * 10000) / 10000);
            }
            var satuan = '<div class="dn-satuan-grup">'
                + '<select class="form-control form-control-sm dn-satuan" data-w="' + teksAman(w) + '"'
                + ' aria-label="Unit of Total Pieces">'
                + (uomAsli === 'SET' ? ''
                    : '<option value="' + teksAman(uomAsli) + '"' + (set ? '' : ' selected') + '>'
                      + teksAman(uomAsli) + '</option>')
                + '<option value="SET"' + (set ? ' selected' : '') + '>SET</option>'
                + '</select>'
                + (set
                    ? '<input type="number" class="form-control form-control-sm dn-ringkas-isi dn-angka-input"'
                      + ' data-w="' + teksAman(w) + '" data-kunci="total_pieces" min="0" placeholder="Required"'
                      + ' value="' + teksAman(r.total_pieces) + '" autocomplete="off">'
                    : '<input type="text" class="form-control form-control-sm dn-angka-input"'
                      + ' data-kunci="total_pieces" value="' + teksAman(angka(qty)) + '" readonly tabindex="-1"'
                      + ' title="Follows Qty Invoiced (Each)">')
                + '</div>';

            // Dikunci : Color Name (dari warna SJ), Qty Invoiced (jumlah FG/OUT),
            //           Unit Cost CM (harga SO - dasar penagihan AR), kedua Total,
            //           Total Pieces kalau satuannya PCS.
            // Diketik : Color Code, Total Pieces (SET), Unit Cost FOB, Disc.
            return '<tr>'
                + '<td class="dn-tengah"><b>' + (k + 1) + '</b></td>'
                + '<td>' + isi('color_code') + '</td>'
                + '<td>' + kunci(r.color_name) + '</td>'
                + '<td>' + satuan + '</td>'
                + '<td>' + kunci(angka(qty), 'dn-angka-input', 'qty') + '</td>'
                + '<td>' + kunci(cmWarna(w).unit, 'dn-angka-input') + '</td>'
                + '<td>' + isi('unit_cost_fob', 'dn-angka-input', 'number') + '</td>'
                + '<td>' + isi('disc', 'dn-angka-input', 'number') + '</td>'
                + '<td>' + kunci(angka(cm), 'dn-angka-input dn-hasil', 'cm') + '</td>'
                + '<td>' + kunci(angka(fob), 'dn-angka-input dn-hasil', 'fob') + '</td>'
                + '</tr>';
        }).join(''));

        hitungSemua(true);
    }

    $('#inv-table-ringkas').on('input', '.dn-ringkas-isi', function () {
        var w = String($(this).data('w'));
        isiWarna(w)[$(this).data('kunci')] = $(this).val();

        // Tabel tidak digambar ulang supaya kursor tidak lompat - cukup kotak
        // hasil hitungan di baris ini yang disegarkan. Tanpa ini Total (CM) &
        // Total (FOB) di baris ini diam di angka lama sampai ada yang menggambar
        // ulang tabelnya.
        var h = hitungWarna(w);
        var $tr = $(this).closest('tr');
        $tr.find('[data-hasil="cm"]').val(angka(h.cm));
        $tr.find('[data-hasil="fob"]').val(angka(h.fob));

        hitungSemua(true);
    });

    // Ganti satuan Total Pieces. Satuan asli: angkanya ikut Qty Invoiced. SET:
    // isian dikosongkan supaya jumlah set-nya benar-benar diketik, lalu difokuskan.
    $('#inv-table-ringkas').on('change', '.dn-satuan', function () {
        var w = String($(this).data('w'));
        var r = isiWarna(w);
        var baris = $(this).closest('tr').index();
        r.total_pieces_unit = $(this).val() === 'SET' ? 'SET' : uomWarna(w);
        r.total_pieces = '';
        gambarRingkas();
        if (r.total_pieces_unit === 'SET') {
            $('#inv-table-ringkas tbody tr').eq(baris).find('[data-kunci="total_pieces"]').trigger('focus');
        }
    });

    // ================= Perhitungan =================
    // Urutan & nama barisnya sama persis dengan Invoice Local:
    //   Total            = jumlah (qty x unit cost) semua baris summary
    //   Discount         = jumlah (disc% x total baris)
    //   Total Without Tax= Total - Discount - DP - DP/CBD - Return
    //   VAT              = Total Without Tax x 11% atau 12%
    //   Grand Total      = Total Without Tax + VAT
    // Bedanya dihitung dua kali: sekali pakai harga CM, sekali pakai FOB.
    // DP, DP/CBD, Return dan tarif VAT-nya satu, dipakai untuk keduanya.
    function hitungSemua(tanpaGambar) {
        var totalCm = 0, totalFob = 0, discCm = 0, discFob = 0;

        warnaUrut().forEach(function (w) {
            var r = isiWarna(w);
            var c = cmWarna(w);
            var d = parseFloat(r.disc) || 0;
            var kotorCm  = c.kotor;
            var kotorFob = qtyFobWarna(w) * (parseFloat(r.unit_cost_fob) || 0);
            totalCm  += kotorCm;
            totalFob += kotorFob;
            discCm   += kotorCm * d / 100;
            discFob  += kotorFob * d / 100;
        });

        var dp    = parseFloat($('#inv-table-ringkas').data('dp')) || 0;
        var dpcbd = parseFloat($('#inv-table-ringkas').data('dpcbd')) || 0;
        var retur = parseFloat($('#inv-table-ringkas').data('retur')) || 0;
        var tarif = parseFloat($('#inv-table-ringkas').data('vat')) || 0;

        var twotCm  = totalCm  - discCm  - dp - dpcbd - retur;
        var twotFob = totalFob - discFob - dp - dpcbd - retur;
        var vatCm   = twotCm  * tarif;
        var vatFob  = twotFob * tarif;

        // Angka ditulis sebagai teks, dan yang nol diberi tanda supaya diredupkan -
        // angka yang memang berisi jadi langsung menonjol.
        function tulis(sel, angka) {
            $(sel).text(rupiah(angka)).toggleClass('dn-nol', Math.abs(angka) < 0.005);
        }
        tulis('#inv-total-cm', totalCm);
        tulis('#inv-total-fob', totalFob);
        tulis('#inv-disc-cm', discCm);
        tulis('#inv-disc-fob', discFob);
        tulis('#inv-dp-cm', dp);
        tulis('#inv-dp-fob', dp);
        tulis('#inv-dpcbd-cm', dpcbd);
        tulis('#inv-dpcbd-fob', dpcbd);
        tulis('#inv-retur-cm', retur);
        tulis('#inv-retur-fob', retur);
        tulis('#inv-twot-cm', twotCm);
        tulis('#inv-twot-fob', twotFob);
        tulis('#inv-vat-cm', vatCm);
        tulis('#inv-vat-fob', vatFob);
        tulis('#inv-grand-cm', twotCm + vatCm);
        tulis('#inv-grand-fob', twotFob + vatFob);

        // Nilai TAGIH knitting. Potongannya sama persis dengan kolom di
        // sebelahnya - yang berbeda cuma dasar nilainya, jadi tidak ada angka
        // yang harus diketik dua kali.
        var totalTagih = 0, discTagih = 0, adaTagih = false;
        invBaris.forEach(function (r) {
            var harga = parseFloat(r.total_price_tagih) || 0;
            if (harga > 0 || $.trim(String(r.uom_tagih == null ? '' : r.uom_tagih)) !== '') {
                adaTagih = true;
            }
            totalTagih += harga;
            discTagih += (parseFloat(r.disc) || 0) / 100 * harga;
        });
        $('#inv-rekap-tabel').toggleClass('is-knit', adaTagih);
        if (adaTagih) {
            var twotTagih = totalTagih - discTagih - dp - dpcbd - retur;
            var vatTagih  = twotTagih * tarif;
            tulis('#inv-total-tagih', totalTagih);
            tulis('#inv-disc-tagih', discTagih);
            tulis('#inv-dp-tagih', dp);
            tulis('#inv-dpcbd-tagih', dpcbd);
            tulis('#inv-retur-tagih', retur);
            tulis('#inv-twot-tagih', twotTagih);
            tulis('#inv-vat-tagih', vatTagih);
            tulis('#inv-grand-tagih', twotTagih + vatTagih);
            $('#inv-curr-tagih').text($('#inv-curr-cm').text());
        }

        // Mata uangnya ikut ditulis di judul kolom - angka tanpa mata uang itu
        // ambigu. Diambil dari baris shipment pertama, kalau kosong dari SJ.
        var curr = (kirimBaris[0] && kirimBaris[0].currency)
            || (invBaris[0] && invBaris[0].curr) || '';
        $('#inv-curr-cm, #inv-curr-fob').text(String(curr).toUpperCase());

        // Tarifnya ikut ditulis di label supaya tidak perlu buka modal untuk tahu.
        $('#inv-label-vat').text(tarif ? 'VAT (' + Math.round(tarif * 100) + '%)' : 'VAT');

        if (!tanpaGambar) { gambarRingkas(); }
    }

    // ================= Modal Add SJ =================
    var modalSJ = new bootstrap.Modal(document.getElementById('modal-add-so'));
    var sjTerakhir = [];

    function cariDiInvoice(r) {
        for (var i = 0; i < invBaris.length; i++) {
            if (String(invBaris[i].asal) === String(r.asal)
                && String(invBaris[i].id_baris) === String(r.id_baris)) { return invBaris[i]; }
        }
        return null;
    }
    function sudahMasuk(r) { return cariDiInvoice(r) !== null; }

    /* ---- Satu invoice = satu tanggal SJ ----
     * Sama dengan Invoice Local: boleh beberapa SJ, tapi tanggalnya harus
     * sama. Begitu ada satu yang dicentang, kotak centang SJ bertanggal lain
     * dihilangkan; kalau centangnya dikosongkan lagi, kotaknya muncul lagi. */
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

    /** Rentang tanggal dibuat melingkupi SJ yang sudah dipilih. */
    function rentangIkutBaris() {
        if (!invBaris.length) { return; }
        var paling = null, terbaru = null;
        invBaris.forEach(function (r) {
            var t = String(r.bppbdate || '').slice(0, 10);
            if (!/^\d{4}-\d{2}-\d{2}$/.test(t)) { return; }
            if (paling === null || t < paling)   { paling = t; }
            if (terbaru === null || t > terbaru) { terbaru = t; }
        });
        if (paling === null) { return; }
        tglSet('#so-tgl-awal', paling);
        if (!tglIso('#so-tgl-akhir') || tglIso('#so-tgl-akhir') < terbaru) {
            tglSet('#so-tgl-akhir', terbaru);
        }
    }

    $('#inv-btn-so').on('click', function () {
        pilihSumberBaris();
    });

    $('#modal-add-so').on('shown.bs.modal', function () {
        var $s = $('#so-buyer');
        if (!$s.hasClass('select2-hidden-accessible')) {
            $s.select2({ theme: 'bootstrap4', width: '100%', dropdownParent: $('#modal-add-so') });
        }
        hitungRingkasan();
        if (invBaris.length) { $('#so-btn-cari').trigger('click'); }
    });

    // ---- Pemilihan SJ dikelompokkan per FG/OUT ----
    // Satu FG/OUT bisa berisi puluhan baris warna/size. Dicentang satu per satu
    // gampang ada yang terlewat, jadi di modal ini satu baris mewakili satu
    // FG/OUT - dicentang berarti seluruh barisnya ikut. Yang masuk ke tabel
    // Detail SJ dan yang disimpan tetap per baris.
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

    function kelompokSj(daftar) {
        var peta = {}, hasil = [];
        daftar.forEach(function (r) {
            var fg = $.trim(String(r.shipping_number || ''));
            var kunci = kunciKelompok(r);
            if (!peta[kunci]) {
                peta[kunci] = { fg: fg, baris: [], qty: 0, nilai: 0,
                    // Nilai tagih knitting - kosong untuk garment.
                    qtyTagih: 0, nilaiTagih: 0, uomTagih: '',
                    warna: [], dipilih: 0, manual: 0,
                    tgl: tglSj(r) };
                hasil.push(peta[kunci]);
            }
            peta[kunci].baris.push(r);
        });
        hasil.forEach(function (g, i) {
            g._g = i;
            g.baris.forEach(function (r) {
                g.qty += parseFloat(r.qty) || 0;
                g.qtyTagih += parseFloat(r.qty_tagih) || 0;
                g.nilaiTagih += parseFloat(r.total_price_tagih) || 0;
                var ut = $.trim(String(r.uom_tagih == null ? '' : r.uom_tagih));
                if (ut !== '' && g.uomTagih.indexOf(ut) === -1) {
                    g.uomTagih = g.uomTagih === '' ? ut : g.uomTagih + ', ' + ut;
                }
                if (Number(r.harga_manual) === 1) { g.manual++; }
                var tp = parseFloat(r.total_price);
                if (isNaN(tp)) { tp = (parseFloat(r.qty) || 0) * (parseFloat(r.unit_price) || 0); }
                g.nilai += tp;
                // Barang tanpa warna (SJ tanpa SO) dihitung lewat nama barangnya,
                // jadi kolom ini tidak jadi "-" untuk seluruh SJ GK/OUT.
                var w = kunciWarna(r);
                if (w !== '-' && g.warna.indexOf(w) === -1) { g.warna.push(w); }
                if (r._pilih) { g.dipilih++; }
            });
        });
        return hasil;
    }

    function gambarSj(daftar) {
        var $b = $('#so-table-sj tbody').empty();
        sjGrup = kelompokSj(daftar);
        if (!sjGrup.length) {
            $b.html('<tr><td class="dn-kosong" colspan="17">No SJ found for this filter.</td></tr>');
            aturKunciTanggal();
            segarkanCekSemua();
            infoPilih();
            return;
        }
        // Ditentukan dari datanya, bukan dari pilihan di layar: cuma baris
        // knitting yang punya nilai tagih, jadi tabel garment tidak ikut melebar.
        $('#modal-add-so').toggleClass('is-knit', sjGrup.some(function (g) {
            return g.uomTagih !== '' || g.nilaiTagih > 0;
        }));
        $b.html(sjGrup.map(function (g) {
            var penuh = g.dipilih > 0 && g.dipilih === g.baris.length;
            return '<tr' + (g.dipilih ? ' class="is-terpilih"' : '') + '>'
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
                + '<td class="dn-angka">' + (g.manual === g.baris.length && !g.nilai
                    ? '<span class="dn-lain">priced later</span>' : angka(g.nilai)) + '</td>'
                + '<td class="dn-kol-tagih">' + teksAman(g.uomTagih) + '</td>'
                + '<td class="dn-angka dn-kol-tagih">' + angka(g.qtyTagih) + '</td>'
                + '<td class="dn-angka dn-kol-tagih">' + angka(g.nilaiTagih) + '</td>'
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
        infoPilih();
    }

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

    function infoPilih() {
        // Yang dihitung SJ-nya (kelompok FG/OUT), bukan baris warna/size.
        var baru = hitungSj(sjTerakhir.filter(function (r) { return r._pilih && !sudahMasuk(r); }));
        var lama = hitungSj(invBaris);

        // Total qty invoice setelah Apply: baris lama yang tidak ikut tersaring
        // ditambah baris hasil pencarian yang tercentang.
        var qty = 0, adaDiHasil = {};
        sjTerakhir.forEach(function (r) {
            adaDiHasil[String(r.asal) + '|' + String(r.id_baris)] = true;
            if (r._pilih) { qty += parseFloat(r.qty) || 0; }
        });
        invBaris.forEach(function (x) {
            if (!adaDiHasil[String(x.asal) + '|' + String(x.id_baris)]) { qty += parseFloat(x.qty) || 0; }
        });
        $('#so-info-pilih').html(teksPilih(baru, lama, qty));
        hitungRingkasan();
    }

    // ---- Ringkasan nilai di dalam modal ----
    // Rumusnya sama dengan Invoice Local. Yang dihitung SELURUH isi invoice -
    // baris yang sudah masuk ditambah yang baru ditandai - supaya angkanya
    // tidak menyesatkan saat menambah baris ke invoice yang sudah berisi.
    // Dasarnya harga CM, sama seperti yang ditagih tim AR.
    function hitungRingkasan() {
        var total = 0, discount = 0;

        // Yang sudah masuk: dihitung per warna, ikut diskon yang sudah diketik.
        warnaUrut().forEach(function (w) {
            var r = isiWarna(w);
            var kotor = cmWarna(w).kotor;
            total += kotor;
            discount += kotor * (parseFloat(r.disc) || 0) / 100;
        });

        // Yang baru ditandai tapi belum masuk: dipakai harga SJ-nya.
        sjTerakhir.forEach(function (r) {
            if (!r._pilih || sudahMasuk(r)) { return; }
            total += (parseFloat(r.qty) || 0) * (parseFloat(r.unit_price) || 0);
        });

        var dp    = nilai('#so-dp');
        var dpcbd = nilai('#so-dpcbd');
        var retur = nilai('#so-return');
        var twot  = total - discount - dp - dpcbd - retur;
        var tarif = $('#so-vat-11').is(':checked') ? 0.11 : 0;
        var vat   = twot * tarif;

        $('#so-total').val(rupiah(total));
        $('#so-discount').val(rupiah(discount));
        $('#so-twot').val(rupiah(twot));
        $('#so-vat').val(rupiah(vat));
        $('#so-grandtotal').val(rupiah(twot + vat));
    }

    $('#so-vat-11').on('change', hitungRingkasan);
    $('#so-dp, #so-dpcbd, #so-return').on('input', hitungRingkasan);

    $('#so-btn-cari').on('click', function () {
        var $tb = $(this);
        $tb.prop('disabled', true);
        $('#so-table-sj tbody').html('<tr><td class="dn-kosong" colspan="17">Loading...</td></tr>');
        $.getJSON(RUT_SJ, {
            tgl_awal: tglIso('#so-tgl-awal'),
            tgl_akhir: tglIso('#so-tgl-akhir'),
            buyer: $('#so-buyer').val() || '',
            profit_center: $('#inv-pc').val() || '',
            abaikan: ID_UBAH || ''
        }).done(function (res) {
            sjTerakhir = (res && res.data) ? res.data : [];
            sjTerakhir.forEach(function (r, i) { r._i = i; r._pilih = sudahMasuk(r); });
            gambarSj(sjTerakhir);
            if (res && res.pesan) {
                Swal.fire({ icon: 'warning', title: 'Partial result', text: res.pesan, customClass: { popup: 'dn-swal' } });
            }
        }).fail(function (x) {
            $('#so-table-sj tbody').html('<tr><td class="dn-kosong" colspan="17">Could not load SJ.</td></tr>');
            var p = (x.responseJSON && x.responseJSON.pesan) ? x.responseJSON.pesan : 'Please try again.';
            Swal.fire({ icon: 'error', title: 'Search failed', text: p, customClass: { popup: 'dn-swal' } });
        }).always(function () { $tb.prop('disabled', false); });
    });

    // Centang disimpan ke DATA, bukan ke baris yang tampil - supaya baris yang
    // tersembunyi oleh kotak filter tetap ikut terbawa. Satu centang mengenai
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
        $(this).closest('tr').toggleClass('is-terpilih', pilih);
        aturKunciTanggal();
        segarkanCekSemua();
        infoPilih();
    });

    // Centang-semua hanya mengambil SJ yang tanggalnya sama - satu invoice
    // cuma boleh memuat satu tanggal SJ.
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
            $(this).prop('checked', pilih).closest('tr').toggleClass('is-terpilih', pilih);
        });
        aturKunciTanggal();
        segarkanCekSemua();
        infoPilih();
    });

    $('#so-cari-sj').on('input', function () {
        var q = String($(this).val() || '').toLowerCase();
        gambarSj(sjTerakhir.filter(function (r) { return cocokCari(r, q); }));
    });

    $('#so-btn-tambah').on('click', function () {
        // Centang di modal ini yang menentukan isi invoice: dicentang = masuk,
        // dilepas = keluar. Yang diatur hanya baris yang ada di hasil pencarian
        // sekarang, jadi mempersempit tanggal tidak membuang isi invoice.
        var tambah = 0, buang = 0, sisa = [];
        var dalamHasil = {};
        sjTerakhir.forEach(function (r) { dalamHasil[String(r.asal) + '|' + String(r.id_baris)] = r; });

        invBaris.forEach(function (x) {
            var r = dalamHasil[String(x.asal) + '|' + String(x.id_baris)];
            if (!r) { sisa.push(x); return; }
            if (!r._pilih) { buang++; return; }
            sisa.push(x);
        });

        sjTerakhir.forEach(function (r) {
            if (!r._pilih || sudahMasuk(r)) { return; }
            sisa.push($.extend({}, r));
            tambah++;
        });

        if (!sisa.length) {
            Swal.fire({
                icon: 'warning', title: 'No row selected',
                text: 'Tick at least one SJ row before continuing - or close this window and use Add WS instead.',
                customClass: { popup: 'dn-swal' }
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
        // DP, DP/CBD, Return & VAT dibawa dari modal, sama seperti Invoice Local.
        $('#inv-table-ringkas')
            .data('dp', nilai('#so-dp'))
            .data('dpcbd', nilai('#so-dpcbd'))
            .data('retur', nilai('#so-return'))
            .data('vat', $('#so-vat-11').is(':checked') ? 0.11 : 0);
        // Baris Invoice Summary tidak dibuat di sini - dia mengikuti warna yang
        // ada di invBaris, jadi terbentuk sendiri saat digambar.
        lengkapiRingkasDariSj();
        lengkapiCurrencyDariSj();
        gambarDetailSj();
        // WS milik SJ yang baru dipilih dibacakan ke Detail SO - itu yang wajib.
        lengkapiSoDariSj();
        modalSJ.hide();

        if (buang) {
            var kabar = [];
            if (tambah) { kabar.push('<b>' + tambah + '</b> row' + (tambah > 1 ? 's' : '') + ' added'); }
            kabar.push('<b>' + buang + '</b> row' + (buang > 1 ? 's' : '') + ' removed');
            Swal.fire({
                icon: 'info', title: 'Detail updated', html: kabar.join(', ') + '.',
                customClass: { popup: 'dn-swal' }
            });
        }
    });

    /**
     * Isi otomatis kolom Invoice Summary dari SJ warna itu.
     *
     * HANYA kolom yang masih kosong yang diisi. Begitu user mengetik sendiri,
     * ketikannya tidak akan ditimpa - spesifikasinya memang bilang kolom ini
     * manual, ini cuma supaya tidak mengetik ulang yang sudah ada di SJ.
     */
    function lengkapiRingkasDariSj() {
        warnaUrut().forEach(function (w) {
            var milik = sjWarna(w);
            if (!milik.length) { return; }
            var r = isiWarna(w);

            if (!r.color_name) { r.color_name = w; }
            // Unit Cost (CM) tidak disimpan di sini - selalu dihitung dari SJ
            // lewat cmWarna(), jadi ikut benar saat baris SJ ditambah/dibuang.
            // Harga FOB di SO belum bisa dijadikan patokan - ditampilkan apa
            // adanya (sering kosong) dan memang untuk diubah sendiri.
            if (!r.unit_cost_fob && milik[0].fob) { r.unit_cost_fob = milik[0].fob; }
        });
    }

    /**
     * Currency baris shipment ikut mata uang SJ.
     *
     * Syaratnya bukan lagi "kalau masih kosong" - bawaannya sekarang USD, jadi
     * tidak pernah kosong. Yang dijaga: baris yang mata uangnya sudah dipilih
     * sendiri di modal tidak ikut diubah.
     */
    function lengkapiCurrencyDariSj() {
        if (!invBaris.length || !invBaris[0].curr) { return; }
        var dariSj = String(invBaris[0].curr).toUpperCase();
        var berubah = false;
        kirimBaris.forEach(function (r) {
            if (!r._currTangan && r.currency !== dariSj) { r.currency = dariSj; berubah = true; }
        });
        if (berubah) { gambarKirim(); }
    }

    // ================= Clear All: Detail SJ + Invoice Summary =================
    // Invoice Summary tidak punya tombol sendiri. Barisnya diturunkan dari warna
    // di Detail SJ, jadi begitu SJ kosong summary-nya ikut kosong. Yang harus
    // dibuang terang-terangan cuma ketikannya (ringkasIsi) - kalau dibiarkan,
    // warna yang sama muncul lagi membawa angka lama saat SJ-nya dipilih ulang.
    $('#btn-kosongkan-sj').on('click', function () {
        var n = invBaris.length;
        var m = invSo.length;
        if (!n && !m) { return; }
        var w = warnaUrut().length;
        var apa = [];
        if (m) { apa.push('<b>' + m + '</b> SO row' + (m > 1 ? 's' : '')); }
        if (n) { apa.push('<b>' + n + '</b> SJ row' + (n > 1 ? 's' : '')); }
        Swal.fire({
            icon: 'warning',
            title: 'Clear all rows?',
            html: 'All ' + apa.join(' and ') + ' will be removed.'
                + '<p class="dn-swal-catatan" style="margin-top:10px">Invoice Summary is cleared with them: '
                + '<b>' + w + '</b> colour row' + (w > 1 ? 's' : '') + ', including the Color Code,'
                + ' Total Pieces, Unit Cost (FOB) and Disc typed there.</p>',
            showCancelButton: true,
            confirmButtonText: '<i class="fas fa-eraser"></i> Yes, clear all',
            cancelButtonText: 'No, keep them',
            reverseButtons: true,
            focusCancel: true,
            customClass: { popup: 'dn-swal' }
        }).then(function (pilih) {
            if (!pilih.isConfirmed) { return; }

            invBaris = [];
            invSo = [];
            ringkasIsi = {};

            // Centang di modal Add SJ ikut dilepas. Tanpa ini, membuka modal lalu
            // langsung menekan Apply memasukkan lagi semua baris tadi - tanda
            // centangnya masih tersimpan di sjTerakhir.
            sjTerakhir.forEach(function (r) { r._pilih = false; });
            if (sjTerakhir.length) { $('#so-cari-sj').trigger('input'); }
            // Begitu juga centang di modal Add WS.
            wsTerakhir.forEach(function (r) { r._pilih = false; });
            if (wsTerakhir.length) { $('#ws-cari').trigger('input'); }

            // DP, DP/CBD, Return & VAT yang sudah terbawa ke Summary dinolkan supaya
            // invoice kosong tidak menampilkan Total Without Tax minus. Isiannya di
            // modal dibiarkan - akan terpakai lagi begitu Apply ditekan.
            $('#inv-table-ringkas').data('dp', 0).data('dpcbd', 0).data('retur', 0).data('vat', 0);

            gambarDetailSo();   // Detail SO + Invoice Summary
            gambarDetailSj();   // Detail SJ, lalu Invoice Summary & Summary lagi
        });
    });

    // ================= Save =================
    // Bingkai merah di isian wajib yang masih kosong - hilang begitu diisi.
    $(document).on('input change', '.dn-kurang', function () {
        if (String($(this).val() || '').trim() !== '') { $(this).removeClass('dn-kurang'); }
    });

    $('#inv-btn-simpan').on('click', function () {
        // Semua yang kurang dikumpulkan dulu lalu ditampilkan sekaligus - user
        // tidak perlu menekan Save berkali-kali untuk tahu apa saja yang kurang.
        $('.dn-kurang').removeClass('dn-kurang');
        var kurang = [];
        var pertama = null;
        function tandai(sel, label) {
            kurang.push(label);
            var $el = $(sel).addClass('dn-kurang');
            if (!pertama) { pertama = $el.next('.select2-container').length ? $el.next('.select2-container') : $el; }
        }
        function kosong(sel) { return String($(sel).val() || '').trim() === ''; }

        if (kosong('#inv-tgl'))       { tandai('#inv-tgl', 'Invoice Date'); }
        if (kosong('#inv-seller'))    { tandai('#inv-seller', 'Seller'); }
        if (kosong('#inv-purchaser')) { tandai('#inv-purchaser', 'Purchaser'); }
        if (kosong('#inv-receiver'))  { tandai('#inv-receiver', 'Receiver / Ship To'); }

        function kartuKurang(sel, label) {
            kurang.push(label);
            if (!pertama) { pertama = $(sel).closest('.card'); }
        }
        if (!kirimBaris.length) { kartuKurang('#inv-table-kirim', 'Shipment Details: add at least one row'); }
        // Harus ada isinya - dari SJ atau dari SO, tidak harus dua-duanya.
        // Invoice yang terbit sebelum barangnya keluar baru punya baris SO;
        // yang barangnya sudah keluar bisa langsung punya SJ saja.
        if (!invSo.length && !invBaris.length) {
            kartuKurang('#inv-table-ringkas',
                'Detail SJ or Detail SO: add at least one row (Add SJ / WS)');
        }

        var warna = warnaUrut();
        if ((invSo.length || invBaris.length) && !warna.length) {
            kartuKurang('#inv-table-ringkas', 'Invoice Summary: at least one colour row');
        }
        var tanpaKode = [];
        warna.forEach(function (w, i) {
            if (String(isiWarna(w).color_code || '').trim() === '') {
                tanpaKode.push((i + 1) + ' (' + w + ')');
                var $kotak = $('#inv-table-ringkas tbody tr').eq(i).find('[data-kunci="color_code"]').addClass('dn-kurang');
                if (!pertama) { pertama = $kotak; }
            }
        });
        if (tanpaKode.length) {
            kurang.push('Color Code on Invoice Summary row ' + tanpaKode.join(', ')
                + ' &ndash; type <b>-</b> if there is none');
        }
        // Satuan SET: Total Pieces wajib diketik (dipakai Total FOB).
        var tanpaSet = [];
        warna.forEach(function (w, i) {
            var r = isiWarna(w);
            if (r.total_pieces_unit === 'SET' && !((parseFloat(r.total_pieces) || 0) > 0)) {
                tanpaSet.push((i + 1) + ' (' + w + ')');
                var $kotak = $('#inv-table-ringkas tbody tr').eq(i).find('[data-kunci="total_pieces"]').addClass('dn-kurang');
                if (!pertama) { pertama = $kotak; }
            }
        });
        if (tanpaSet.length) {
            kurang.push('Total Pieces on Invoice Summary row ' + tanpaSet.join(', ')
                + ' &ndash; required when the unit is <b>SET</b>');
        }

        // SJ tanpa SO harus punya harga - server menolaknya juga, tapi lebih
        // enak ketahuan di sini sambil kotaknya masih di depan mata.
        var belumHarga = barisTanpaHarga();
        if (belumHarga.length) {
            var sjKurang = [];
            belumHarga.forEach(function (r) {
                var n = String(r.shipping_number || r.sj || '');
                if (sjKurang.indexOf(n) === -1) { sjKurang.push(n); }
            });
            kurang.push('Unit Price on ' + belumHarga.length + ' row' + (belumHarga.length > 1 ? 's' : '')
                + ' in Detail SJ (' + teksAman(sjKurang.slice(0, 3).join(', '))
                + (sjKurang.length > 3 ? ', ...' : '') + ') &ndash; these SJ have no SO');
            var $isian = $('#inv-table-sj').find('.dn-harga-kurang').first();
            if (!pertama && $isian.length) { pertama = $isian; }
        }

        // Mata uang baris tanpa SO juga tidak ada di database - lihat selCurr().
        var belumCurr = barisTanpaCurr();
        if (belumCurr.length) {
            kurang.push('Currency on ' + belumCurr.length + ' row' + (belumCurr.length > 1 ? 's' : '')
                + ' in Detail SJ &ndash; these SJ have no SO, so their currency is picked here');
            var $curr = $('#inv-table-sj').find('select.dn-harga-kurang').first();
            if (!pertama && $curr.length) { pertama = $curr; }
        }
        // Satu invoice satu mata uang: rekap di bawah menjumlahkan semua baris,
        // jadi campuran mata uang hasilnya tidak berarti apa-apa.
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
            }).then(function () {
                if (!pertama || !pertama.length) { return; }
                pertama[0].scrollIntoView({ block: 'center', behavior: 'smooth' });
                if (pertama.is('input, textarea')) { pertama.trigger('focus'); }
            });
            return;
        }

        var $tb = $(this);
        var curr = String((invBaris[0] && invBaris[0].curr) || '').toUpperCase();

        // Ditunjukkan dulu apa yang akan disimpan - sekali disimpan, nomornya
        // terpakai dan tidak bisa ditarik lagi.
        var ringkas = ''
            + '<div class="dn-swal-ringkas">'
            + '<div><span>Seller</span><b>' + teksAman($('#inv-seller').find(':selected').text()) + '</b></div>'
            + '<div><span>Purchaser</span><b>' + teksAman($('#inv-purchaser').val()) + '</b></div>'
            + '<div><span>Receiver</span><b>' + teksAman($('#inv-receiver').val()) + '</b></div>'
            + '<div><span>Profit Center</span><b>' + teksAman($('#inv-pc').find(':selected').text()) + '</b></div>'
            + '<div><span>Document</span><b>' + teksAman($('#inv-doc-type').val())
            + ($('#inv-doc-number').val() ? ' / ' + teksAman($('#inv-doc-number').val()) : '') + '</b></div>'
            + '<div><span>Rows</span><b>' + kirimBaris.length + ' shipment &middot; '
            + invBaris.length + ' SJ &middot; ' + warna.length + ' colour' + (warna.length > 1 ? 's' : '') + '</b></div>'
            + '<div class="dn-swal-grand"><span>Grand Total (CM)</span><b>' + teksAman($('#inv-grand-cm').text())
            + (curr ? ' ' + teksAman(curr) : '') + '</b></div>'
            + '<div><span>Grand Total (FOB)</span><b>' + teksAman($('#inv-grand-fob').text())
            + (curr ? ' ' + teksAman(curr) : '') + '</b></div>'
            + '</div>'
            + (MODE_UBAH
                ? '<p class="dn-swal-catatan">The invoice number stays the same. Shipment Details,'
                    + ' Invoice Summary and SJ rows will be replaced by what is on this form.</p>'
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

        var $rekap = $('#inv-table-ringkas');
        $.ajax({
            url: MODE_UBAH ? RUT_UBAH : RUT_SIMPAN,
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            data: {
                id: ID_UBAH,
                profit_center: $('#inv-pc').val(),
                no_invoice_2: $('#inv-no2').val(),
                tgl_invoice: tglIso('#inv-tgl'),
                id_type: $('#inv-type').val(),
                doc_type: $('#inv-doc-type').val(),
                doc_number: $('#inv-doc-number').val(),
                invoice_notes: $('#inv-notes').val(),
                reference: $('#inv-reff').val(),
                shipper_alamat: $('#inv-shipper-alamat').val(),
                id_seller: $('#inv-seller').val(),
                seller_alamat: $('#inv-seller-alamat').val(),
                purchaser_nama: $('#inv-purchaser').val(),
                purchaser_alamat: $('#inv-purchaser-alamat').val(),
                receiver_nama: $('#inv-receiver').val(),
                receiver_alamat: $('#inv-receiver-alamat').val(),
                dp: parseFloat($rekap.data('dp')) || 0,
                dp_cbd: parseFloat($rekap.data('dpcbd')) || 0,
                retur: parseFloat($rekap.data('retur')) || 0,
                vat_persen: Math.round((parseFloat($rekap.data('vat')) || 0) * 100),
                // Isi Shipment Details apa adanya - kolomnya diambil dari PETA_KIRIM
                // yang sama dengan modal, jadi tidak ada kolom yang tertinggal.
                kirim: kirimBaris.map(function (r) {
                    var out = {};
                    Object.keys(PETA_KIRIM).forEach(function (sel) {
                        out[PETA_KIRIM[sel]] = r[PETA_KIRIM[sel]];
                    });
                    return out;
                }),
                // Baris SJ cuma penunjuk - qty & harganya dibaca ulang di
                // server. Kecuali harga SJ tanpa SO: itu memang cuma ada di
                // layar ini, jadi ikut dikirim (server tetap mengabaikannya
                // untuk baris yang punya SO).
                baris: invBaris.map(function (r) {
                    var b = { id_baris: r.id_baris };
                    if (perluHarga(r)) { b.unit_price = r.unit_price; b.curr = r.curr; }
                    return b;
                }),
                // Baris SO juga cuma penunjuk: nama, harga & qty pesanan dibaca
                // ulang di server. Yang dipercaya dari sini cuma baris SO mana
                // yang dipilih dan berapa qty yang ditagih.
                baris_ws: invSo.map(function (r) {
                    return { id_so_det: r.id_so_det, qty: r.qty };
                }),
                // Isian per warna. Qty & Unit Cost (CM) tidak dikirim: dihitung
                // ulang di server dari SJ.
                ringkas: warnaUrut().map(function (w) {
                    var r = isiWarna(w);
                    return {
                        warna: w,
                        color_code: r.color_code,
                        total_pieces: r.total_pieces,
                        total_pieces_unit: r.total_pieces_unit === 'SET' ? 'SET' : uomWarna(w),
                        unit_cost_fob: r.unit_cost_fob,
                        disc: r.disc
                    };
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
            Swal.fire({ icon: 'error', title: MODE_UBAH ? 'Update failed' : 'Save failed', text: p, customClass: { popup: 'dn-swal' } });
        });
    }

    // ================= Isi awal =================
    // Create: sengaja kosong. Baris shipment ditambah sendiri lewat Add Row, dan
    // baris summary dibuatkan otomatis begitu SJ pertama masuk.
    // Edit: isi yang tersimpan dimasukkan ke keadaan layar yang sama dengan saat
    // diketik, jadi semua fungsi di atas bekerja tanpa dibedakan.
    if (MODE_UBAH) {
        // Currency & brand yang tersimpan dianggap sudah dipilih sendiri, supaya
        // tidak ditimpa otomatis dari SJ atau nama Purchaser.
        kirimBaris = @json($ubahKirim).map(function (r) {
            // Brand yang MEMANG terisi dikunci. Yang masih kosong jangan -
            // invoice lama yang brandnya belum sempat diisi harus tetap bisa
            // terisi otomatis dari SO-nya waktu dibuka lagi di sini.
            var adaMerek = $.trim(String(r.brand == null ? '' : r.brand)) !== '';
            return $.extend(kirimKosong(), r, { _currTangan: true, _brandTangan: adaMerek });
        });
        invBaris = @json($ubahBaris);
        // Baris SO yang tersimpan - itu yang menurunkan Invoice Summary.
        invSo = @json($ubahBarisSo);
        @json($ubahRingkas).forEach(function (r) {
            var isi = isiWarna(r.warna);
            isi.color_code    = r.color_code;
            isi.total_pieces  = r.total_pieces;
            // Satuan selain SET dipastikan ulang dari SJ-nya waktu digambar.
            isi.total_pieces_unit = r.total_pieces_unit === 'SET' ? 'SET' : uomWarna(r.warna);
            isi.unit_cost_fob = r.unit_cost_fob;
            isi.disc          = r.disc;
        });

        var POT_UBAH = @json($ubahPot);
        var vatUbah = parseFloat(POT_UBAH.vat_persen) || 0;
        $('#inv-table-ringkas')
            .data('dp', parseFloat(POT_UBAH.dp) || 0)
            .data('dpcbd', parseFloat(POT_UBAH.dp_cbd) || 0)
            .data('retur', parseFloat(POT_UBAH.retur) || 0)
            .data('vat', vatUbah / 100);
        // Isian di modal Add SJ ikut terisi - dari sanalah nilainya dibawa lagi
        // waktu Apply ditekan.
        $('#so-dp').val(parseFloat(POT_UBAH.dp) || '');
        $('#so-dpcbd').val(parseFloat(POT_UBAH.dp_cbd) || '');
        $('#so-return').val(parseFloat(POT_UBAH.retur) || '');
        // Invoice lama yang memakai 12% tetap dianggap kena VAT - tarifnya
        // sekarang 11%, jadi kotaknya ikut tercentang.
        $('#so-vat-11').prop('checked', vatUbah === 11 || vatUbah === 12);
        $('#inv-seller').trigger('change');   // Buyer di modal ikut Seller tersimpan
        // Baris SJ & SO-nya baru terpasang di atas, jadi brand dari SO memang
        // baru bisa dibaca sekarang - baris yang brandnya masih kosong ikut
        // terisi. Yang sudah ada isinya terkunci, jadi tidak mungkin tertimpa.
        selaraskanMerek();
    }
    gambarKirim();
    gambarRingkas();
    gambarDetailSo();   // Detail SO dulu - itu yang menurunkan Invoice Summary
    gambarDetailSj();
});
</script>
@endpush
