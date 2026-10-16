@extends('layouts.index')

@section('custom-link')
<link rel="stylesheet" href="{{ asset('plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
<link rel="stylesheet" href="{{ asset('plugins/datatables-responsive/css/responsive.bootstrap4.min.css') }}">
<link rel="stylesheet" href="{{ asset('plugins/select2/css/select2.min.css') }}">
<link rel="stylesheet" href="{{ asset('plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}">
{{-- Skin yang sama dengan daftar Invoice EXIM, supaya tampilan Master Data identik. --}}
@include('export-import.invoice._skin')
<style>
/* Lebar kolom per daftar - customer 9 kolom, supplier 10 (ada Product). Facility tidak di tabel,
   ada di modal Show. Kolom terakhir Action. */
@media (min-width: 992px) {
  .nag-skin.dn-daftar-customer .dn-tabel-daftar th:nth-child(1) { width: 8%; }
  .nag-skin.dn-daftar-customer .dn-tabel-daftar th:nth-child(2) { width: 18%; }
  .nag-skin.dn-daftar-customer .dn-tabel-daftar th:nth-child(3) { width: 9%; }
  .nag-skin.dn-daftar-customer .dn-tabel-daftar th:nth-child(4) { width: 7%; }
  .nag-skin.dn-daftar-customer .dn-tabel-daftar th:nth-child(5) { width: 20%; }
  .nag-skin.dn-daftar-customer .dn-tabel-daftar th:nth-child(6) { width: 11%; }
  .nag-skin.dn-daftar-customer .dn-tabel-daftar th:nth-child(7) { width: 13%; }
  .nag-skin.dn-daftar-customer .dn-tabel-daftar th:nth-child(8) { width: 6%; }
  .nag-skin.dn-daftar-customer .dn-tabel-daftar th:nth-child(9) { width: 8%; }
  
  .nag-skin.dn-daftar-supplier .dn-tabel-daftar th:nth-child(1)  { width: 7%; }
  .nag-skin.dn-daftar-supplier .dn-tabel-daftar th:nth-child(2)  { width: 15%; }
  .nag-skin.dn-daftar-supplier .dn-tabel-daftar th:nth-child(3)  { width: 8%; }
  .nag-skin.dn-daftar-supplier .dn-tabel-daftar th:nth-child(4)  { width: 7%; }
  .nag-skin.dn-daftar-supplier .dn-tabel-daftar th:nth-child(5)  { width: 16%; }
  .nag-skin.dn-daftar-supplier .dn-tabel-daftar th:nth-child(6)  { width: 10%; }
  .nag-skin.dn-daftar-supplier .dn-tabel-daftar th:nth-child(7)  { width: 10%; }
  .nag-skin.dn-daftar-supplier .dn-tabel-daftar th:nth-child(8)  { width: 13%; }
  .nag-skin.dn-daftar-supplier .dn-tabel-daftar th:nth-child(9)  { width: 6%; }
  .nag-skin.dn-daftar-supplier .dn-tabel-daftar th:nth-child(10) { width: 8%; }
}

@include("master-data._gaya")


/* Kontak: kartu kecil berikon */
#md-modal-detail .md-kontak-grid {
  display: grid;
  grid-template-columns: repeat(4, minmax(0, 1fr));
  gap: 10px;
  margin-bottom: 16px;
}
#md-modal-detail .md-kontak {
  display: flex;
  align-items: center;
  gap: 10px;
  min-width: 0;
  padding: 11px 12px;
  background: #fff;
  border: 1px solid #e2e8f0;
  border-radius: 12px;
}
#md-modal-detail .md-kontak-ikon {
  flex: 0 0 auto;
  width: 32px;
  height: 32px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  background: #eef2f7;
  color: #1e3a5f;
  font-size: 12px;
}
#md-modal-detail .md-kontak > div { min-width: 0; }
#md-modal-detail .md-kontak > div > span { display: block; font-size: 10.5px; font-weight: 600; color: #64748b; }
#md-modal-detail .md-kontak b {
  display: block;
  font-size: 13px;
  font-weight: 500;
  color: #1e293b;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}
#md-modal-detail .md-kontak.is-kosong { opacity: .55; }
#md-modal-detail .md-kontak.is-kosong b { color: #94a3b8; font-style: italic; }

/* History: garis waktu Invoice, titiknya ikon per jenis kejadian */
#md-modal-detail .dn-riw-titik { font-size: 10.5px; }
#md-modal-detail .md-riw-create > .dn-riw-titik   { background: #0f766e; }
#md-modal-detail .md-riw-update > .dn-riw-titik   { background: #1d4ed8; }
#md-modal-detail .md-riw-active > .dn-riw-titik   { background: #15803d; }
#md-modal-detail .md-riw-inactive > .dn-riw-titik { background: #b91c1c; }
#md-modal-detail .md-riw-inactive > .dn-riw-kartu { border-color: #fecaca; background: #fff8f8; }
#md-modal-detail .md-riw-active > .dn-riw-kartu   { border-color: #bbf7d0; background: #f7fdf9; }
#md-modal-detail .md-riw-ringkas { display: flex; flex-wrap: wrap; gap: 6px; margin-top: 7px; }
#md-modal-detail .md-riw-ringkas span {
  padding: 2px 8px;
  border-radius: 6px;
  background: #f1f5f9;
  color: #475569;
  font-size: 11.5px;
}
#md-modal-detail .md-riw-ringkas b { color: #1e293b; font-weight: 600; }
#md-modal-detail .md-riw-lama { color: #94a3b8; text-decoration: line-through; }
#md-modal-detail .md-riw-ujung {
  margin: 10px 0 0 36px;
  color: #94a3b8;
  font-size: 11.5px;
  font-style: italic;
}

@media (max-width: 991px) {
  #md-modal-detail .md-kontak-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
}
@media (max-width: 575px) {
  #md-modal-detail .md-kontak-grid { grid-template-columns: 1fr; }
}

#md-modal-detail .md-bank-wrap {
  background: #fff;
  border: 1px solid #e2e8f0;
  border-radius: 12px;
  overflow-x: auto;
}
#md-modal-detail .md-bank-wrap .dn-table thead th { background: #f1f5f9; color: #1e3a5f; border-bottom: 2px solid #cbd8e6; }
#md-modal-detail .md-bank-wrap tr.md-bank-batal td { color: #94a3b8; }
#md-modal-detail .md-bank-kosong { padding: 16px 18px; color: #94a3b8; font-style: italic; font-size: 12.5px; }


/* Priority supplier - warna sama dengan pilihan di form: merah High, kuning Medium, hijau Low */
.nag-skin .md-prio-pil {
  display: inline-flex;
  align-items: center;
  gap: 4px;
  margin-top: 3px;
  padding: 1px 7px;
  border: 1px solid transparent;
  border-radius: 999px;
  font-size: 10px;
  font-weight: 700;
  letter-spacing: .3px;
  line-height: 1.5;
  text-transform: uppercase;
  white-space: nowrap;
}
.nag-skin .md-prio-pil-titik { width: 6px; height: 6px; border-radius: 50%; background: currentColor; }
.nag-skin .md-prio-baris { display: block; line-height: 1; }
.nag-skin .md-prio-pil.is-high   { background: #fee2e2; border-color: #fecaca; color: #b91c1c; }
.nag-skin .md-prio-pil.is-medium { background: #fef9c3; border-color: #fde68a; color: #a16207; }
.nag-skin .md-prio-pil.is-low    { background: #dcfce7; border-color: #bbf7d0; color: #15803d; }
.nag-skin .md-prio-pil.is-high .md-prio-pil-titik   { background: #dc2626; }
.nag-skin .md-prio-pil.is-medium .md-prio-pil-titik { background: #eab308; }
.nag-skin .md-prio-pil.is-low .md-prio-pil-titik    { background: #16a34a; }
/* Di kepala modal sejajar dengan pil status */
#md-modal-detail .md-prio-pil.is-kepala { margin-top: 0; height: 21px; padding: 0 9px; }
/* Baris tidak aktif: pil tetap berwarna tapi redup */
.nag-skin .dn-tabel-daftar tbody tr.md-nonaktif .md-prio-pil { opacity: .6; }
</style>
@endsection

@section('content')
{{-- ===========================================================================
     Daftar Master Customer / Supplier (mastersupplier SB).
     Satu view untuk dua daftar - bedanya cuma kolom Product khusus supplier.
     Aksi per baris: Show (modal detail bertab, termasuk History), Active/Inactive, Edit.
     Tampilan mengikuti daftar Invoice EXIM (lihat export-import/invoice/_skin).
     =========================================================================== --}}
@php($isSupplier = $jenis === 'supplier')
<div class="nag-skin dn-kontrol-sm dn-daftar-{{ $jenis }}">

    {{-- ===== Kepala halaman ===== --}}
    <div class="dn-kepala-halaman">
        <div class="dn-kepala-kiri">
            <span class="dn-kepala-ikon"><i class="fas {{ $conf['ikon'] }}"></i></span>
            <div>
                <h1 class="dn-kepala-judul">{{ $conf['judul'] }}</h1>
                <p class="dn-kepala-sub">{{ $conf['sub'] }}</p>
            </div>
        </div>
    </div>

    {{-- ===== Filter ===== --}}
    <div class="card dn-kartu-filter">
        <div class="card-body">
            <div class="dn-filter-petak">
                <div class="form-group dn-f-lebar">
                    <label for="md-country">Country</label>
                    <select class="form-control select2bs4" id="md-country">
                        <option value="">ALL</option>
                        @foreach ($negara as $n)
                            <option value="{{ $n }}" {{ $awal['country'] === trim($n) ? 'selected' : '' }}>{{ $n }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group dn-f-sedang">
                    <label for="md-area">Area</label>
                    <select class="form-control select2bs4" id="md-area">
                        <option value="">ALL</option>
                        @foreach ($area as $kode => $label)
                            <option value="{{ $kode }}" {{ $awal['area'] === (string) $kode ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group dn-f-sedang">
                    <label for="md-status">Status</label>
                    <select class="form-control select2bs4" id="md-status">
                        <option value="">ALL</option>
                        <option value="aktif" {{ $awal['status'] === 'aktif' ? 'selected' : '' }}>ACTIVE</option>
                        <option value="nonaktif" {{ $awal['status'] === 'nonaktif' ? 'selected' : '' }}>INACTIVE</option>
                    </select>
                </div>
                <div class="dn-filter-tombol">
                    <button type="button" class="btn btn-dn-utama" id="md-btn-cari">
                        <i class="fa fa-search"></i> Search
                    </button>
                    <span class="dn-filter-pisah"></span>
                    <button type="button" class="btn btn-dn-excel" id="md-btn-export">
                        <i class="fas fa-file-excel"></i> Export
                    </button>
                    <a href="{{ route('master-data-create', ['jenis' => $jenis]) }}" class="btn btn-dn-create md-ke-form" data-dasar="{{ route('master-data-create', ['jenis' => $jenis]) }}">
                        <i class="fas fa-plus"></i> Create
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- ===== Tabel ===== --}}
    <div class="card">
        <div class="card-body">
            <div class="dn-list-area" id="md-list-area">
                <div class="dn-loader-overlay">
                    <div class="dn-loader-kartu">
                        <span class="dn-loader-putar"></span> Loading...
                    </div>
                </div>
                <div class="dn-table-muat-wrap md-responsif">
                    <table id="md-table" class="dn-table dn-table-muat dn-tabel-daftar" style="width:100%">
                        <thead>
                            <tr>
                                <th>Code</th>
                                <th>{{ $conf['kolom'] }}</th>
                                <th>Country</th>
                                <th>Area</th>
                                <th>Address</th>
                                <th>NPWP</th>
                                @if ($isSupplier)
                                    <th>Product</th>
                                @endif
                                <th>Contact</th>
                                <th class="dn-tengah">Status</th>
                                <th class="dn-tengah md-th-aksi"><span>Action</span></th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- ===================================================================
         Modal Show - terbuka dari tombol mata di kolom Action.
         =================================================================== --}}
    <div class="modal fade" id="md-modal-detail" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content">
                {{-- Kepala: inisial, nama, kode, area, negara & status --}}
                <div class="modal-header md-det-kepala">
                    <div class="md-det-avatar" id="md-det-avatar"><i class="fas {{ $conf['ikon'] }}"></i></div>
                    <div class="md-det-identitas">
                        <h5 class="md-det-nama" id="md-det-judul">{{ $conf['kolom'] }}</h5>
                        <div class="md-det-meta" id="md-det-meta"></div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                {{-- Tab di luar modal-body supaya tetap kelihatan waktu isinya digulung --}}
                <div class="md-det-tab">
                    <ul class="nav" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button type="button" class="nav-link active" id="md-tab-btn-basic" data-bs-toggle="tab" data-bs-target="#md-tab-basic" role="tab">
                                <i class="fas fa-id-card"></i> Basic
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button type="button" class="nav-link" data-bs-toggle="tab" data-bs-target="#md-tab-alamat" role="tab">
                                <i class="fas fa-location-dot"></i> Address &amp; Product
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button type="button" class="nav-link" data-bs-toggle="tab" data-bs-target="#md-tab-kontak" role="tab">
                                <i class="fas fa-address-book"></i> Contact
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button type="button" class="nav-link" data-bs-toggle="tab" data-bs-target="#md-tab-bank" role="tab">
                                <i class="fas fa-building-columns"></i> Bank Account
                                <span class="md-tab-cacah" id="md-cacah-bank">0</span>
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button type="button" class="nav-link" id="md-tab-btn-riw" data-bs-toggle="tab" data-bs-target="#md-tab-riw" role="tab">
                                <i class="fas fa-clock-rotate-left"></i> History
                                <span class="md-tab-cacah" id="md-cacah-riw" hidden></span>
                            </button>
                        </li>
                    </ul>
                </div>

                <div class="modal-body">
                    <div class="dn-det-muat" id="md-det-muat">
                        <span class="dn-loader-putar"></span> Loading...
                    </div>
                    <div class="tab-content" id="md-det-isi" hidden>
                        <div class="tab-pane fade show active" id="md-tab-basic" role="tabpanel">
                            <div class="dn-det-judul"><i class="fas fa-fingerprint"></i> Identity</div>
                            <dl class="dn-det-info dn-det-info-4" id="md-det-identitas"></dl>
                            <div class="dn-det-judul"><i class="fas fa-scale-balanced"></i> Classification &amp; Tax</div>
                            <dl class="dn-det-info dn-det-info-4 mb-0" id="md-det-klasifikasi"></dl>
                        </div>

                        <div class="tab-pane fade" id="md-tab-alamat" role="tabpanel">
                            <div class="dn-det-judul"><i class="fas fa-map-location-dot"></i> Address</div>
                            <dl class="dn-det-info dn-det-info-4" id="md-det-alamat"></dl>
                            <div class="dn-det-judul"><i class="fas fa-box"></i> Product</div>
                            <dl class="dn-det-info dn-det-info-4 mb-0" id="md-det-produk"></dl>
                        </div>

                        <div class="tab-pane fade" id="md-tab-kontak" role="tabpanel">
                            <div class="dn-det-judul"><i class="fas fa-user-group"></i> Contact Persons</div>
                            <div class="md-kontak-grid" id="md-det-orang"></div>
                            <div class="dn-det-judul"><i class="fas fa-phone"></i> Phone, Fax &amp; E-Mail</div>
                            <div class="md-kontak-grid mb-0" id="md-det-telepon"></div>
                        </div>

                        {{-- Rekening dari tabel rekening partner - bisa lebih dari satu --}}
                        <div class="tab-pane fade" id="md-tab-bank" role="tabpanel">
                            <div class="md-bank-wrap" id="md-det-bank"></div>
                        </div>

                        {{-- Dimuat waktu tab-nya pertama kali dibuka --}}
                        <div class="tab-pane fade" id="md-tab-riw" role="tabpanel">
                            <div class="dn-riw">
                                <div class="dn-riw-muat" id="md-riw-muat">
                                    <span class="dn-loader-putar"></span> Loading history...
                                </div>
                                <div class="dn-riw-kosong" id="md-riw-kosong" hidden>
                                    <i class="fas fa-clock-rotate-left"></i>
                                    <span id="md-riw-kosong-teks"></span>
                                </div>
                                <div class="dn-riw-alur" id="md-riw-alur"></div>
                                <div class="md-riw-ujung" id="md-riw-ujung" hidden>
                                    <i class="fas fa-ellipsis"></i> Earlier history is not available.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <a href="#" class="btn btn-dn-utama md-ke-form" id="md-det-ubah" hidden>
                        <i class="fas fa-pen"></i> Edit
                    </a>
                    <button type="button" class="btn btn-dn-kembali" data-bs-dismiss="modal">
                        <i class="fas fa-xmark"></i> Close
                    </button>
                </div>
            </div>
        </div>
    </div>

</div>

@push('scripts')
<script src="{{ asset('plugins/select2/js/select2.full.min.js') }}"></script>
<script src="{{ asset('plugins/datatables/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('plugins/datatables-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
<script src="{{ asset('plugins/datatables-responsive/js/dataTables.responsive.min.js') }}"></script>
<script src="{{ asset('plugins/datatables-responsive/js/responsive.bootstrap4.min.js') }}"></script>
<script>
$(function () {
    var RUT_DATA   = @json(route('master-data-list', ['jenis' => $jenis]));
    var RUT_EXCEL  = @json(route('master-data-excel', ['jenis' => $jenis]));
    var RUT_DETAIL = @json(route('master-data-detail', ['jenis' => $jenis]));
    var RUT_STATUS = @json(route('master-data-status', ['jenis' => $jenis]));
    var RUT_EDIT   = @json(route('master-data-edit', ['jenis' => $jenis]));
    var RUT_HISTORI = @json(route('master-data-histori', ['jenis' => $jenis]));
    var IKON       = @json($conf['ikon']);
    var SUPPLIER   = @json($isSupplier);
    var JUDUL      = @json($conf['kolom']);
    // Keadaan daftar saat kembali dari Create / Edit (filter, cari, halaman, urutan)
    var AWAL       = @json($awal);

    $('.select2bs4').select2({ theme: 'bootstrap4', width: '100%' });

    function teks(nilai) {
        if (nilai == null || String(nilai).trim() === '') { return '-'; }
        return $('<div>').text(String(nilai).trim()).html();
    }

    // Isian yang kosong / '-' / '0' dianggap tidak ada (banyak NPWP diisi '-').
    function ada(nilai) {
        var s = String(nilai == null ? '' : nilai).trim();
        return s !== '' && s !== '-' && s !== '0';
    }

    // Pil Priority supplier: merah High, kuning Medium, hijau Low. Kosong = tidak ditampilkan.
    var LABEL_PRIORITAS = { HIGH: 'High', MEDIUM: 'Medium', LOW: 'Low' };
    function pilPrioritas(nilai, kelas, akhiran) {
        if (!LABEL_PRIORITAS[nilai]) { return ''; }
        return '<span class="md-prio-pil is-' + nilai.toLowerCase() + (kelas ? ' ' + kelas : '') + '">'
            + '<span class="md-prio-pil-titik"></span>' + LABEL_PRIORITAS[nilai] + (akhiran || '') + '</span>';
    }

    function filterSaatIni() {
        return {
            country: $('#md-country').val() || '',
            area:    $('#md-area').val() || '',
            status:  $('#md-status').val() || ''
        };
    }

    // Teks panjang: dua baris di tabel, selengkapnya lewat tooltip.
    function klem(v, t) {
        if (t !== 'display') { return v || ''; }
        if (!ada(v)) { return '-'; }
        return '<span class="md-klem" title="' + teks(v) + '">' + teks(v) + '</span>';
    }

    var kolom = [
        {
            data: 'kode',
            render: function (v, t, row) {
                if (t !== 'display') { return (v || '') + ' ' + row.id; }
                // ID customer / supplier (Id_Supplier) abu-abu di bawah kodenya
                return (ada(v) ? '<span class="md-kode">' + teks(v) + '</span>' : '-')
                    + '<span class="md-sub">ID ' + teks(row.id) + '</span>';
            }
        },
        {
            data: 'nama',
            render: function (v, t, row) {
                // Priority ikut dicari (ketik HIGH / MEDIUM / LOW di Search)
                if (t !== 'display') { return (v || '') + ' ' + (row.short_name || '') + ' ' + (row.prioritas || ''); }
                return '<span class="md-nama">' + teks(v) + '</span>'
                    + (ada(row.short_name) ? '<span class="md-sub">' + teks(row.short_name) + '</span>' : '')
                    + (row.prioritas ? '<span class="md-prio-baris">' + pilPrioritas(row.prioritas) + '</span>' : '');
            }
        },
        { data: 'country', render: function (v, t) { return t === 'display' ? teks(v) : (v || ''); } },
        { data: 'area_label', render: function (v, t) { return t === 'display' ? teks(v) : (v || ''); } },
        { data: 'alamat', render: klem },
        {
            data: 'npwp',
            render: function (v, t, row) {
                if (t !== 'display') { return (v || '') + ' ' + (row.pkp || ''); }
                // Ada yang berisi dua nomor NPWP - dibatasi dua baris seperti alamat.
                return klem(v, t)
                    + (ada(row.pkp) ? '<span class="md-sub">PKP: ' + teks(row.pkp) + '</span>' : '');
            }
        }
    ];

    if (SUPPLIER) {
        kolom.push({ data: 'produk', render: klem });
    }

    kolom.push(
        {
            data: null,
            orderable: false,
            render: function (row, t) {
                var isi = [row.kontak, row.telepon, row.email].filter(ada);
                if (t !== 'display') { return isi.join(' '); }
                if (!isi.length) { return '-'; }
                return (ada(row.kontak) ? '<span class="md-sub" title="' + teks(row.kontak) + '"><i class="fas fa-user"></i> ' + teks(row.kontak) + '</span>' : '')
                    + (ada(row.telepon) ? '<span class="md-sub" title="' + teks(row.telepon) + '"><i class="fas fa-phone"></i> ' + teks(row.telepon) + '</span>' : '')
                    + (ada(row.email) ? '<span class="md-sub" title="' + teks(row.email) + '"><i class="fas fa-envelope"></i> ' + teks(row.email) + '</span>' : '');
            }
        },
        {
            data: 'non_aktif',
            className: 'dn-tengah',
            render: function (v, t) {
                var aktif = String(v) !== '1';
                if (t !== 'display') { return aktif ? 'ACTIVE' : 'INACTIVE'; }
                // Pil kecil bertitik - sama dengan status di daftar Invoice EXIM.
                return '<span class="dn-pil ' + (aktif ? 'is-second' : 'is-batal') + '">'
                    + '<span class="dn-pil-titik"></span>' + (aktif ? 'Active' : 'Inactive') + '</span>';
            }
        },
        {
            data: null,
            orderable: false,
            searchable: false,
            className: 'dn-tengah',
            render: function (row) {
                // Layar lebar: tiga tombol; HP: satu tombol menu (lihat mdTombolAksi)
                return mdTombolAksi({ id: row.id, aktif: String(row.non_aktif) !== '1', urlEdit: RUT_EDIT });
            }
        }
    );

    // Kolom yang tetap tampil di layar sempit (angka kecil = dipertahankan lebih lama):
    // nama, kode, aksi, status. Sisanya masuk ke detail yang dibuka dengan tombol +.
    var PRIORITAS = { nama: 1, kode: 2, non_aktif: 4, country: 5, area_label: 6, produk: 7, npwp: 8, alamat: 9 };
    kolom.forEach(function (k, i) {
        k.responsivePriority = PRIORITAS[k.data] || (i === kolom.length - 1 ? 3 : 10);
    });

@include('master-data._responsif_js')

    // Filter atas yang terakhir dipakai memuat tabel (bukan pilihan yang belum di-Search)
    var filterTerpakai = filterSaatIni();

    var tabel = $('#md-table').DataTable({
        pageLength: AWAL.baris,
        lengthMenu: [10, 25, 50, 100],
        order: [[AWAL.urut < kolom.length ? AWAL.urut : 1, AWAL.arah]],
        search: { search: AWAL.cari },
        displayStart: AWAL.hal * AWAL.baris,
        autoWidth: false,
        responsive: mdResponsif(),
        language: {
            emptyTable: '<div class="dn-kosong-isi">'
                + '<i class="fas {{ $conf['ikon'] }}"></i>'
                + '<span>No {{ strtolower($conf['kolom']) }} for this filter.</span></div>',
            zeroRecords: '<div class="dn-kosong-isi">'
                + '<i class="fas fa-magnifying-glass"></i>'
                + '<span>No {{ strtolower($conf['kolom']) }} matches your search.</span></div>'
        },
        dom: "<'row'<'col-sm-6'l><'col-sm-6'f>>" +
             "<'row'<'col-sm-12'tr>>" +
             "<'row'<'col-sm-5'i><'col-sm-7'p>>",
        ajax: {
            url: RUT_DATA,
            data: function (d) { filterTerpakai = filterSaatIni(); $.extend(d, filterTerpakai); },
            dataSrc: function (res) {
                return (res && res.data) ? res.data : [];
            },
            error: function () {
                $('#md-list-area').removeClass('is-memuat');
            }
        },
        columns: kolom,
        createdRow: function (tr, row) {
            $(tr).toggleClass('md-nonaktif', String(row.non_aktif) === '1');
        }
    });

    tabel.on('preXhr.dt', function () { $('#md-list-area').addClass('is-memuat'); });
    tabel.on('xhr.dt draw.dt', function () { $('#md-list-area').removeClass('is-memuat'); });

    $('#md-btn-cari').on('click', function () {
        tabel.ajax.reload();
    });

    $('#md-btn-export').on('click', function () {
        window.location.href = RUT_EXCEL + '?' + $.param(filterSaatIni());
    });

    // ---------- Create / Edit membawa keadaan daftar ----------
    // Sesudah simpan atau Back, daftar dibuka lagi dengan filter, pencarian, halaman & urutan yang sama.
    function keadaanDaftar() {
        var urut = tabel.order()[0] || [1, 'asc'];
        var k = {
            country: filterTerpakai.country,
            area:    filterTerpakai.area,
            status:  filterTerpakai.status,
            cari:    tabel.search(),
            hal:     tabel.page(),
            baris:   tabel.page.len(),
            urut:    urut[0],
            arah:    urut[1]
        };
        // Yang masih bawaan tidak perlu ditulis di alamat
        var bawaan = { country: '', area: '', status: '', cari: '', hal: 0, baris: 10, urut: 1, arah: 'asc' };
        Object.keys(bawaan).forEach(function (n) { if (String(k[n]) === String(bawaan[n])) { delete k[n]; } });
        return k;
    }

    // Alamat halaman ikut keadaan daftar, jadi tombol Back browser & refresh juga tidak menghapus filter
    tabel.on('draw.dt', function () {
        try {
            var q = $.param(keadaanDaftar());
            history.replaceState(history.state, '', location.pathname + (q ? '?' + q : ''));
        } catch (e) { /* browser lama tanpa history API: cukup tidak diperbarui */ }
    });

    // href diisi saat diklik (termasuk klik tengah / buka di tab baru), jadi selalu keadaan terbaru
    $(document).on('mousedown click', 'a.md-ke-form', function () {
        var $a = $(this);
        var q = $.extend($a.data('id') ? { id: $a.data('id') } : {}, keadaanDaftar());
        this.href = $a.data('dasar') + ($.isEmptyObject(q) ? '' : '?' + $.param(q));
    });

    // ---------- Show: modal detail ----------
    function barisInfo(label, isi, kelas) {
        return '<div' + (kelas ? ' class="' + kelas + '"' : '') + '><dt>' + label + '</dt><dd>' + teks(isi) + '</dd></div>';
    }

    // Kartu kontak berikon; yang kosong tetap tampil tapi redup supaya tata letaknya tetap
    function kartuKontak(ikon, label, isi) {
        var kosong = !ada(isi);
        return '<div class="md-kontak' + (kosong ? ' is-kosong' : '') + '">'
            + '<span class="md-kontak-ikon"><i class="fas ' + ikon + '"></i></span>'
            + '<div><span>' + label + '</span><b title="' + (kosong ? '' : teks(isi)) + '">' + (kosong ? 'Not set' : teks(isi)) + '</b></div>'
            + '</div>';
    }

    // Dua huruf depan nama, tanpa bentuk badan usaha (PT, CV, LTD, ...)
    function inisial(nama) {
        var buang = ['PT', 'CV', 'TBK', 'LTD', 'CO', 'INC', 'LIMITED', 'CORP', 'UD', 'THE'];
        var kata = String(nama || '').toUpperCase().replace(/[^A-Z0-9 ]+/g, ' ').split(/\s+/)
            .filter(function (k) { return k && buang.indexOf(k) === -1; });
        return (kata.length ? kata.slice(0, 2).map(function (k) { return k.charAt(0); }).join('') : '?');
    }

    function angkaAda(v) { return parseFloat(v) ? String(parseFloat(v)) : ''; }

    var modalDetail = new bootstrap.Modal(document.getElementById('md-modal-detail'));
    var idDetail = null;
    var riwayatTermuat = false;

    $('#md-table').on('click', '.md-btn-lihat', function () {
        idDetail = $(this).data('id');
        riwayatTermuat = false;

        // Selalu mulai dari tab Basic
        bootstrap.Tab.getOrCreateInstance(document.getElementById('md-tab-btn-basic')).show();
        $('#md-det-judul').text(JUDUL);
        $('#md-det-avatar').html('<i class="fas ' + IKON + '"></i>');
        $('#md-det-meta').empty();
        $('#md-cacah-riw').prop('hidden', true);
        $('#md-det-ubah').prop('hidden', true);
        $('#md-det-muat').prop('hidden', false);
        $('#md-det-isi').prop('hidden', true);
        modalDetail.show();

        $.getJSON(RUT_DETAIL, { id: idDetail }).done(function (res) {
            var d = res.data || {};
            var aktif = String(d.non_aktif) !== '1';
            var tipe = [d.tipe_buyer === 'Y' ? 'Buyer' : '', d.tipe_agent === 'Y' ? 'Agent' : ''].filter(Boolean).join(', ');

            // ----- Kepala -----
            $('#md-det-judul').text(d.Supplier || JUDUL).attr('title', d.Supplier || '');
            $('#md-det-avatar').text(inisial(d.Supplier));
            $('#md-det-meta').html(''
                + (ada(d.supplier_code) ? '<span class="md-det-chip is-kode">' + teks(d.supplier_code) + '</span>' : '')
                + '<span class="md-det-chip"><i class="fas fa-hashtag"></i>ID ' + teks(d.Id_Supplier) + '</span>'
                + (ada(res.area_label) ? '<span class="md-det-chip"><i class="fas fa-location-dot"></i>' + teks(res.area_label) + '</span>' : '')
                + (ada(d.country) ? '<span class="md-det-chip"><i class="fas fa-globe"></i>' + teks(d.country) + '</span>' : '')
                + (tipe ? '<span class="md-det-chip"><i class="fas fa-tag"></i>' + teks(tipe) + '</span>' : '')
                + '<span class="dn-pil ' + (aktif ? 'is-second' : 'is-batal') + '"><span class="dn-pil-titik"></span>'
                + (aktif ? 'Active' : 'Inactive') + '</span>'
                + (res.prioritas ? pilPrioritas(res.prioritas.nilai, 'is-kepala', ' priority') : ''));

            // Priority (supplier): di depan Classification, kosong ditulis "Not set"
            var barisPrioritas = res.prioritas
                ? '<div><dt>Priority</dt><dd>' + (pilPrioritas(res.prioritas.nilai) || '<span class="md-det-kosong">Not set</span>') + '</dd></div>'
                : '';

            // ----- Basic -----
            $('#md-det-identitas').html(''
                + barisInfo('Code', d.supplier_code)
                + barisInfo(JUDUL + ' Name', d.Supplier)
                + barisInfo('Short Name (Alias)', d.short_name)
                + barisInfo('Group Name', d.group_name));
            $('#md-det-klasifikasi').html(''
                + barisPrioritas
                + barisInfo('Area', res.area_label)
                + barisInfo('Category', res.kategori_label)
                + barisInfo('Facility', d.jenis_fasilitas)
                + (SUPPLIER ? (res.prioritas ? '' : barisInfo('Term of Payment', res.term_label)) : barisInfo('Customer Type', tipe))
                + (SUPPLIER && res.prioritas ? barisInfo('Term of Payment', res.term_label) : '')
                + barisInfo('NPWP', d.npwp)
                + barisInfo('PKP Status', d.pkp)
                + (SUPPLIER ? '' : barisInfo('Term of Payment', res.term_label))
                + barisInfo('To Account Manual', String(d.to_account_manual) === '1' ? 'Yes' : 'No'));

            // ----- Address & Product -----
            $('#md-det-alamat').html(''
                + barisInfo('Address', d.alamat, 'md-penuh')
                + barisInfo('Address 2', d.alamat2, 'md-penuh')
                + barisInfo('Zip Code', d.zip_code)
                + barisInfo('Country', d.country));
            $('#md-det-produk').html(''
                + barisInfo('Product Name', d.product_name)
                + barisInfo('MOQ', angkaAda(d.moq))
                + barisInfo('Lead Time', angkaAda(d.lead_time))
                + barisInfo('MOQ Lead Time', angkaAda(d.moq_lead_time)));

            // ----- Contact -----
            $('#md-det-orang').html(['Attn', 'Attn2', 'Attn3', 'Attn4'].map(function (k, i) {
                return kartuKontak('fa-user', 'Contact Name ' + (i + 1), d[k]);
            }).join(''));
            $('#md-det-telepon').html(''
                + kartuKontak('fa-phone', 'Phone', d.Phone)
                + kartuKontak('fa-fax', 'Fax', d.Fax)
                + kartuKontak('fa-envelope', 'E-Mail', d.Email));

            // ----- Bank Account (yang Active di atas) -----
            var bank = res.bank || [];
            $('#md-cacah-bank').text(bank.length);
            $('#md-det-bank').html(bank.length
                ? '<table class="dn-table"><thead><tr>'
                    + '<th>Currency</th><th>Bank Name</th><th>Account No.</th><th>Beneficiary Name</th><th>SWIFT Code</th>'
                    + '<th class="dn-tengah">Status</th></tr></thead><tbody>'
                    + bank.map(function (b) {
                        var aktifBank = String(b.status) === 'Active';
                        return '<tr class="' + (aktifBank ? '' : 'md-bank-batal') + '">'
                            + '<td>' + teks(b.bank_currency) + '</td>'
                            + '<td>' + teks(b.bank_name) + '</td>'
                            + '<td>' + teks(b.bank_account) + '</td>'
                            + '<td>' + teks(b.beneficiary_name) + '</td>'
                            + '<td>' + teks(b.swift_code) + '</td>'
                            + '<td class="dn-tengah"><span class="dn-pil ' + (aktifBank ? 'is-second' : 'is-batal') + '">'
                            + '<span class="dn-pil-titik"></span>' + teks(b.status) + '</span></td>'
                            + '</tr>';
                    }).join('')
                    + '</tbody></table>'
                : '<div class="md-bank-kosong">No bank account registered for this ' + JUDUL.toLowerCase() + '.</div>');

            $('#md-det-ubah').attr('href', RUT_EDIT + '?id=' + encodeURIComponent(d.Id_Supplier))
                .data({ dasar: RUT_EDIT, id: d.Id_Supplier }).prop('hidden', false);
            $('#md-det-muat').prop('hidden', true);
            $('#md-det-isi').prop('hidden', false);
        }).fail(function (x) {
            modalDetail.hide();
            Swal.fire({
                icon: 'error',
                title: 'Failed to load detail',
                text: (x.responseJSON && x.responseJSON.message) || 'Please try again.',
                customClass: { popup: 'dn-swal' }
            });
        });
    });

    // ---------- History: dimuat waktu tab-nya pertama kali dibuka ----------
    var IKON_RIW = { create: 'fa-plus', update: 'fa-pen', active: 'fa-toggle-on', inactive: 'fa-toggle-off' };

    function kartuRiwayat(b) {
        var isi = '';
        if (b.ringkas && b.ringkas.length) {
            isi += '<div class="md-riw-ringkas">' + b.ringkas.map(function (r) {
                return '<span>' + teks(r.label) + ': <b>' + teks(r.nilai) + '</b></span>';
            }).join('') + '</div>';
        }
        if (b.perubahan && b.perubahan.length) {
            isi += '<div class="dn-riw-beda"><div class="dn-riw-beda-judul">Changes <span class="dn-riw-cacah">' + b.perubahan.length + '</span></div>'
                + '<ul class="dn-riw-beda-daftar">' + b.perubahan.map(function (p) {
                    var lama = p.lama == null ? '<span class="dn-riw-hampa">empty</span>' : '<span class="md-riw-lama">' + teks(p.lama) + '</span>';
                    var baru = p.baru == null ? '<span class="dn-riw-hampa">empty</span>' : '<b>' + teks(p.baru) + '</b>';
                    return '<li>' + teks(p.label) + ': ' + lama + ' <i class="fas fa-arrow-right dn-riw-panah"></i> ' + baru + '</li>';
                }).join('') + '</ul></div>';
        }
        if (b.catatan) {
            isi += '<div class="dn-riw-catatan">' + teks(b.catatan) + '</div>';
        }
        return '<div class="dn-riw-butir md-riw-' + b.aksi + '">'
            + '<span class="dn-riw-titik"><i class="fas ' + (IKON_RIW[b.aksi] || 'fa-circle') + '"></i></span>'
            + '<div class="dn-riw-kartu">'
            + '<div class="dn-riw-kepala"><b class="dn-riw-judul">' + teks(b.judul) + '</b>'
            + '<span class="dn-riw-oleh"><i class="fas fa-user"></i>' + (b.oleh ? teks(b.oleh) : 'Unknown user') + '</span>'
            + '<span class="dn-riw-waktu"><i class="far fa-clock"></i>' + teks(b.waktu) + '</span></div>'
            + isi + '</div></div>';
    }

    function riwayatKosong(pesan) {
        $('#md-riw-kosong-teks').text(pesan);
        $('#md-riw-kosong').prop('hidden', false);
    }

    $('#md-tab-btn-riw').on('shown.bs.tab', function () {
        if (riwayatTermuat || idDetail === null) { return; }
        riwayatTermuat = true;
        var id = idDetail;

        $('#md-riw-muat').prop('hidden', false);
        $('#md-riw-kosong, #md-riw-ujung').prop('hidden', true);
        $('#md-riw-alur').empty();

        $.getJSON(RUT_HISTORI, { id: id }).done(function (res) {
            if (id !== idDetail) { return; }   // modal sudah dibuka untuk partner lain
            var baris = res.baris || [];
            $('#md-riw-muat').prop('hidden', true);
            $('#md-cacah-riw').text(baris.length).prop('hidden', !baris.length);

            if (!res.siap) {
                riwayatKosong('History is not available yet.');
                return;
            }
            if (!baris.length) {
                riwayatKosong('No history recorded for this ' + JUDUL.toLowerCase() + ' yet.');
                return;
            }
            $('#md-riw-alur').html(baris.map(kartuRiwayat).join(''));
            $('#md-riw-ujung').prop('hidden', !!res.awal);
        }).fail(function () {
            if (id !== idDetail) { return; }
            riwayatTermuat = false;   // boleh dicoba lagi dengan membuka tab-nya ulang
            $('#md-riw-muat').prop('hidden', true);
            riwayatKosong('Failed to load history, please open this tab again.');
        });
    });

    // ---------- Active / Inactive ----------
    $('#md-table').on('click', '.md-btn-status', function () {
        var row = mdBarisDari(tabel, this);
        var keAktif = String($(this).data('aktif')) !== '1';   // sekarang tidak aktif -> diaktifkan

        Swal.fire({
            icon: 'question',
            title: keAktif ? 'Set this ' + JUDUL.toLowerCase() + ' Active?' : 'Set this ' + JUDUL.toLowerCase() + ' Inactive?',
            html: '<div class="dn-swal-ringkas">'
                + '<div><span>Code</span><b>' + teks(row.kode) + '</b></div>'
                + '<div><span>' + JUDUL + '</span><b>' + teks(row.nama) + '</b></div>'
                + '<div><span>New status</span><b>' + (keAktif ? 'Active' : 'Inactive') + '</b></div>'
                + '</div>',
            showCancelButton: true,
            confirmButtonText: keAktif ? '<i class="fas fa-toggle-on"></i> Yes, set Active' : '<i class="fas fa-toggle-off"></i> Yes, set Inactive',
            cancelButtonText: 'Cancel',
            reverseButtons: true,
            focusCancel: true,
            customClass: { popup: 'dn-swal' }
        }).then(function (pilih) {
            if (!pilih.isConfirmed) { return; }

            $.ajax({
                url: RUT_STATUS,
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                data: { id: row.id, aktif: keAktif ? 1 : 0 }
            }).done(function (res) {
                Swal.fire({
                    icon: 'success',
                    title: res.aktif ? 'Set to Active' : 'Set to Inactive',
                    html: '<b>' + teks(res.nama) + '</b> is now ' + (res.aktif ? 'Active' : 'Inactive') + '.',
                    timer: 1800,
                    showConfirmButton: false,
                    customClass: { popup: 'dn-swal' }
                });
                tabel.ajax.reload(null, false);   // tetap di halaman yang sama
            }).fail(function (x) {
                Swal.fire({
                    icon: 'error',
                    title: 'Status not changed',
                    text: (x.responseJSON && x.responseJSON.message) || 'Please try again.',
                    customClass: { popup: 'dn-swal' }
                });
            });
        });
    });
});
</script>
@endpush
@endsection
