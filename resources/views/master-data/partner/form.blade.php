{{-- ===========================================================================
     Create Master Customer / Supplier.
     Isian & aturannya mengikuti form Signalbit pages/forms/master_supcust.php
     (tab Basic & Kontak) - di sini tab-nya dibuat kartu supaya semua terlihat
     sekaligus. Tab Bank tidak ada: rekening dikelola di master_supplier_bank.
     Tampilan memakai skin Invoice EXIM.
     =========================================================================== --}}
@extends('layouts.index')

@section('custom-link')
<link rel="stylesheet" href="{{ asset('plugins/select2/css/select2.min.css') }}">
<link rel="stylesheet" href="{{ asset('plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}">
@include('export-import.invoice._skin')
<style>
/* Centang Buyer / Agent - posisi kotaknya bawaan form-check, sama dengan centang VAT di form Invoice. */
.nag-skin .md-cek { display: flex; flex-wrap: wrap; gap: 4px 18px; align-items: center; min-height: 31px; }
.nag-skin .md-cek .form-check-label { font-size: 12.5px; color: #1e293b; font-weight: 500; text-transform: none; letter-spacing: 0; }
/* Keterangan aturan di bawah isian (CoA otomatis, huruf besar, dll). */
.nag-skin .md-ket { display: block; margin-top: 4px; font-size: 11px; color: #64748b; }
.nag-skin .md-ket i { margin-right: 4px; color: #94a3b8; }
.nag-skin .md-info {
  display: flex;
  gap: 8px;
  align-items: flex-start;
  margin-bottom: 14px;
  padding: 9px 12px;
  border-radius: 8px;
  background: #f1f5f9;
  border: 1px solid #e2e8f0;
  color: #475569;
  font-size: 12px;
}
.nag-skin .md-info i { color: #2c5282; margin-top: 2px; }
/* Isian disimpan huruf besar oleh server - ditampilkan begitu juga. */
.nag-skin .md-besar { text-transform: uppercase; }
.nag-skin .md-besar::placeholder { text-transform: none; }
/* Sub-bagian di dalam kartu (Product di kartu kiri, Contact di kartu kanan) */
.nag-skin .md-subjudul {
  display: flex;
  align-items: center;
  gap: 8px;
  margin: 2px 0 12px;
  padding-top: 12px;
  border-top: 1px dashed #dbe3ee;
  font-size: 12px;
  font-weight: 700;
  letter-spacing: .3px;
  color: #1e3a5f;
}
.nag-skin .md-subjudul i { font-size: 11px; opacity: .75; }
/* Tombol Save/Back menempel di dasar kartu kanan walau isinya lebih pendek dari kartu kiri */
.nag-skin .md-kartu-isi { display: flex; flex-direction: column; }
.nag-skin .md-kartu-isi .dn-kaki { margin-top: auto; }
/* Edit: kode & nama yang sedang diubah di bawah judul */
.nag-skin .md-sub-kode {
  display: inline-block;
  margin-left: 6px;
  padding: 1px 8px;
  border-radius: 6px;
  background: #eef2f7;
  color: #1e3a5f;
  font-weight: 600;
}
.nag-skin .dn-kepala-sub .dn-pil { margin-left: 4px; vertical-align: 1px; }
/* Priority supplier: tiga tombol berwarna - merah High, kuning Medium, hijau Low */
.nag-skin .md-prioritas { display: flex; flex-wrap: wrap; gap: 5px; }
.nag-skin .md-prio {
  display: inline-flex;
  align-items: center;
  gap: 5px;
  height: 31px;
  margin: 0;
  padding: 0 9px;
  border: 1px solid #dbe3ee;
  border-radius: 8px;
  background: #fff;
  color: #475569;
  font-size: 12.5px;
  font-weight: 600;
  cursor: pointer;
  user-select: none;
  transition: background .15s, border-color .15s, color .15s;
}
.nag-skin .md-prio:hover { border-color: #94a3b8; }
.nag-skin .md-prio-titik { width: 9px; height: 9px; border-radius: 50%; }
.nag-skin .md-prio-high .md-prio-titik   { background: #dc2626; }
.nag-skin .md-prio-medium .md-prio-titik { background: #eab308; }
.nag-skin .md-prio-low .md-prio-titik    { background: #16a34a; }
.nag-skin .btn-check:checked + .md-prio-high   { background: #fee2e2; border-color: #ef4444; color: #b91c1c; }
.nag-skin .btn-check:checked + .md-prio-medium { background: #fef9c3; border-color: #eab308; color: #a16207; }
.nag-skin .btn-check:checked + .md-prio-low    { background: #dcfce7; border-color: #22c55e; color: #15803d; }
.nag-skin .btn-check:focus-visible + .md-prio  { box-shadow: 0 0 0 3px rgba(30, 58, 95, .18); }
/* Code yang terkunci (sudah dipakai transaksi) */
.nag-skin input[readonly].md-terkunci { background: #f1f5f9; color: #475569; cursor: not-allowed; }
</style>
@endsection

@section('content')
@php($isCustomer = $jenis === 'customer')
{{-- $ubah terisi = mode edit (data lama), kosong = create --}}
<div class="nag-skin dn-kontrol-sm">

    {{-- ===== Kepala halaman ===== --}}
    <div class="dn-kepala-halaman">
        <div class="dn-kepala-kiri">
            <span class="dn-kepala-ikon"><i class="fas {{ $ubah ? 'fa-pen-to-square' : $conf['ikon'] }}"></i></span>
            <div>
                <h1 class="dn-kepala-judul">{{ $ubah ? 'Edit' : 'Create' }} {{ $conf['judul'] }}</h1>
                @if ($ubah)
                    <p class="dn-kepala-sub">
                        Update master {{ strtolower($conf['kolom']) }} data
                        <span class="md-sub-kode">{{ $ubah->supplier_code ?: '-' }} &middot; {{ $ubah->Supplier }}</span>
                        @if ($ubah->non_aktif === '1')
                            <span class="dn-pil is-batal">Inactive</span>
                        @endif
                    </p>
                @else
                    <p class="dn-kepala-sub">Input form to add new master {{ strtolower($conf['kolom']) }} data</p>
                @endif
            </div>
        </div>
    </div>

    {{-- Banner info sengaja tidak ditampilkan. Komentar Blade (bukan <!-- -->) supaya tidak ikut terkirim ke browser.
    <div class="md-info">
        <i class="fas fa-circle-info"></i>
        <span>All text is saved in <b>UPPERCASE</b> and the name must be unique per {{ strtolower($conf['kolom']) }}.</span>
    </div>
    --}}

    <div class="row">
        {{-- ===== Kiri: Basic & Product ===== --}}
        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="card-title"><i class="fas fa-id-card"></i> Basic &amp; Product</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="md-kode">Code <span class="dn-wajib" title="Required">*</span></label>
                                <input type="text" class="form-control md-besar {{ $kodeTerkunci ? 'md-terkunci' : '' }}" id="md-kode" name="supplier_code"
                                    value="{{ $ubah->supplier_code ?? '' }}" {{ $kodeTerkunci ? 'readonly' : '' }}
                                    maxlength="{{ $panjang['supplier_code'] }}" placeholder="{{ $conf['kolom'] }} code" autocomplete="off">
                                @if ($kodeTerkunci)
                                    <small class="md-ket"><i class="fas fa-lock"></i>Locked, already used in transactions</small>
                                @endif
                            </div>
                        </div>
                        <div class="col-md-8">
                            <div class="form-group">
                                <label for="md-nama">{{ $conf['kolom'] }} Name <span class="dn-wajib" title="Required">*</span></label>
                                <input type="text" class="form-control md-besar" id="md-nama" name="Supplier" value="{{ $ubah->Supplier ?? '' }}"
                                    maxlength="{{ $panjang['Supplier'] }}" placeholder="{{ $conf['kolom'] }} name" autocomplete="off">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="md-alias">Short Name (Alias)</label>
                                <input type="text" class="form-control md-besar" id="md-alias" name="short_name" value="{{ $ubah->short_name ?? '' }}"
                                    maxlength="{{ $panjang['short_name'] }}" autocomplete="off">
                            </div>
                        </div>
                        <div class="col-md-8">
                            <div class="form-group">
                                <label for="md-grup">Group Name</label>
                                <input type="text" class="form-control md-besar" id="md-grup" name="group_name" value="{{ $ubah->group_name ?? '' }}"
                                    maxlength="{{ $panjang['group_name'] }}" autocomplete="off">
                            </div>
                        </div>


                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="md-pkp">PKP Status</label>
                                <select class="form-control select2bs4" id="md-pkp" name="pkp">
                                    <option value="">-- Select PKP status --</option>
                                    @foreach ($pkp as $p)
                                        <option value="{{ $p }}" {{ ($ubah->pkp ?? null) === $p ? 'selected' : '' }}>{{ $p }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-8">
                            <div class="form-group">
                                <label for="md-npwp">NPWP <span class="dn-wajib" title="Required">*</span></label>
                                <input type="text" class="form-control md-besar" id="md-npwp" name="npwp" value="{{ $ubah->npwp ?? '' }}"
                                    maxlength="{{ $panjang['npwp'] }}" placeholder="Use - if none" autocomplete="off">
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="md-area">Area <span class="dn-wajib" title="Required">*</span></label>
                                <select class="form-control select2bs4" id="md-area" name="area">
                                    <option value="">-- Select area --</option>
                                    {{-- Area tersimpan huruf pertamanya saja (L / I / F) --}}
                                    @foreach ($area as $nilai => $label)
                                        <option value="{{ $nilai }}" {{ $ubah && strtoupper(substr($nilai, 0, 1)) === strtoupper((string) $ubah->area) ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="md-kategori">Category <span class="dn-wajib" title="Required">*</span></label>
                                <select class="form-control select2bs4" id="md-kategori" name="vendor_cat">
                                    <option value="">-- Select category --</option>
                                    @foreach ($kategori as $k)
                                        <option value="{{ substr($k, 0, 2) }}" {{ ($ubah->vendor_cat ?? null) === substr($k, 0, 2) ? 'selected' : '' }}>{{ $k }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        {{-- Kolom ketiga baris ini: Customer Type (customer) atau Priority (supplier).
                             Options supplier (To Account Manual) pindah ke sebelah Term of Payment. --}}
                        @php($opsiDiAtas = $isCustomer || !$prioritas)
                        @if ($opsiDiAtas)
                            @include('master-data.partner._opsi')
                        @else
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Priority</label>
                                    <div class="md-prioritas" role="radiogroup" aria-label="Priority">
                                        @foreach ($prioritas as $nilai => $label)
                                            <input type="radio" class="btn-check" name="prioritas" id="md-prio-{{ strtolower($nilai) }}"
                                                value="{{ $nilai }}" autocomplete="off" {{ ($ubah->prioritas ?? '') === $nilai ? 'checked' : '' }}>
                                            <label class="md-prio md-prio-{{ strtolower($nilai) }}" for="md-prio-{{ strtolower($nilai) }}">
                                                <span class="md-prio-titik"></span>{{ $label }}
                                            </label>
                                        @endforeach
                                    </div>
                                    <small class="md-ket"><i class="fas fa-circle-info"></i>Optional, click again to clear.</small>
                                </div>
                            </div>
                        @endif

                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="md-fasilitas">Facility <span class="dn-wajib" id="md-fasilitas-wajib" title="Required for Local" hidden>*</span></label>
                                <select class="form-control select2bs4" id="md-fasilitas" name="jenis_fasilitas">
                                    <option value="">-- Select facility --</option>
                                    @foreach ($fasilitas as $f)
                                        <option value="{{ $f }}" {{ ($ubah->jenis_fasilitas ?? null) === $f ? 'selected' : '' }}>{{ $f }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-{{ $opsiDiAtas ? 6 : 4 }}">
                            <div class="form-group">
                                <label for="md-terms">Term of Payment</label>
                                <select class="form-control select2bs4" id="md-terms" name="id_terms">
                                    <option value="">-- Select terms --</option>
                                    @foreach ($terms as $t)
                                        <option value="{{ $t->id }}" {{ $ubah && (int) $ubah->id_terms === (int) $t->id ? 'selected' : '' }}>{{ $t->kode_pterms }}{{ $t->nama_pterms ? ' | ' . $t->nama_pterms : '' }}</option>
                                    @endforeach
                                </select>
                                @if ($termLama)
                                    <small class="md-ket"><i class="fas fa-circle-info"></i>Current: {{ $termLama }}. Kept as is unless a term is selected.</small>
                                @endif
                            </div>
                        </div>
                        @unless ($opsiDiAtas)
                            @include('master-data.partner._opsi')
                        @endunless
                        {{-- CoA hanya ditentukan saat create; edit tidak mengubahnya --}}
                        @if (!$autoCoa && !$ubah)
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="md-coa">ID CoA</label>
                                    <select class="form-control select2bs4" id="md-coa" name="id_coa">
                                        <option value="">-- Select CoA --</option>
                                        @foreach ($coa as $c)
                                            <option value="{{ $c->id_coa }}">{{ $c->id_coa }} | {{ $c->nm_coa }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        @endif

                    </div>

                    <div class="md-subjudul"><i class="fas fa-box"></i> Product</div>
                    <div class="form-group">
                        <label for="md-produk">Product Name</label>
                        <input type="text" class="form-control md-besar" id="md-produk" name="product_name" value="{{ $ubah->product_name ?? '' }}" autocomplete="off">
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="md-moq">MOQ</label>
                                <input type="number" step="any" min="0" class="form-control" id="md-moq" name="moq" value="{{ $ubah ? (float) $ubah->moq : '' }}">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="md-lead">Lead Time</label>
                                <input type="number" step="any" min="0" class="form-control" id="md-lead" name="lead_time" value="{{ $ubah ? (float) $ubah->lead_time : '' }}">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="md-moq-lead">MOQ Lead Time</label>
                                <input type="number" step="any" min="0" class="form-control" id="md-moq-lead" name="moq_lead_time" value="{{ $ubah ? (float) $ubah->moq_lead_time : '' }}">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ===== Kanan: Address & Contact + tombol
             (rekening bank tidak di sini - dikelola di master_supplier_bank) ===== --}}
        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="card-title"><i class="fas fa-location-dot"></i> Address &amp; Contact</h5>
                </div>
                <div class="card-body md-kartu-isi">
                    <div class="form-group">
                        <label for="md-alamat">Address</label>
                        <input type="text" class="form-control md-besar" id="md-alamat" name="alamat" value="{{ trim($ubah->alamat ?? '') }}"
                            maxlength="{{ $panjang['alamat'] }}" autocomplete="off">
                    </div>
                    <div class="form-group">
                        <label for="md-alamat2">Address 2</label>
                        <input type="text" class="form-control md-besar" id="md-alamat2" name="alamat2" value="{{ trim($ubah->alamat2 ?? '') }}"
                            maxlength="{{ $panjang['alamat2'] }}" autocomplete="off">
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="md-zip">Zip Code</label>
                                <input type="text" class="form-control md-besar" id="md-zip" name="zip_code" value="{{ $ubah->zip_code ?? '' }}"
                                    maxlength="{{ $panjang['zip_code'] }}" autocomplete="off">
                            </div>
                        </div>
                        <div class="col-md-8">
                            <div class="form-group">
                                <label for="md-negara">Country</label>
                                {{-- Teks bebas seperti Signalbit, dengan saran dari negara yang sudah dipakai
                                     supaya tidak muncul ejaan baru untuk negara yang sama. --}}
                                <input type="text" class="form-control md-besar" id="md-negara" name="country" value="{{ $ubah->country ?? '' }}" list="md-negara-daftar"
                                    maxlength="{{ $panjang['country'] }}" autocomplete="off">
                                <datalist id="md-negara-daftar">
                                    @foreach ($negara as $n)
                                        <option value="{{ $n }}">
                                    @endforeach
                                </datalist>
                            </div>
                        </div>
                    </div>

                    <div class="md-subjudul"><i class="fas fa-address-book"></i> Contact</div>
                    <div class="row">
                        @foreach (['Attn' => 'Contact Name 1', 'Attn2' => 'Contact Name 2', 'Attn3' => 'Contact Name 3', 'Attn4' => 'Contact Name 4'] as $kolom => $label)
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="md-{{ strtolower($kolom) }}">{{ $label }}</label>
                                    <input type="text" class="form-control md-besar" id="md-{{ strtolower($kolom) }}" name="{{ $kolom }}" value="{{ $ubah->$kolom ?? '' }}"
                                        maxlength="{{ $panjang[$kolom] }}" autocomplete="off">
                                </div>
                            </div>
                        @endforeach
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="md-telp">Phone</label>
                                <input type="text" class="form-control" id="md-telp" name="Phone" value="{{ $ubah->Phone ?? '' }}"
                                    maxlength="{{ $panjang['Phone'] }}" autocomplete="off">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="md-fax">Fax</label>
                                <input type="text" class="form-control" id="md-fax" name="Fax" value="{{ $ubah->Fax ?? '' }}"
                                    maxlength="{{ $panjang['Fax'] }}" autocomplete="off">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="md-email">E-Mail</label>
                                <input type="text" class="form-control md-besar" id="md-email" name="Email" value="{{ $ubah->Email ?? '' }}"
                                    maxlength="{{ $panjang['Email'] }}" autocomplete="off">
                            </div>
                        </div>
                    </div>

                    <div class="dn-kaki">
                        <button type="button" class="btn btn-dn-simpan" id="md-btn-simpan">
                            <i class="fa fa-save"></i> {{ $ubah ? 'Update' : 'Save' }}
                        </button>
                        <a href="{{ $urlKembali }}" class="btn btn-dn-kembali">
                            <i class="fa fa-arrow-left"></i> Back
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>

@push('scripts')
<script src="{{ asset('plugins/select2/js/select2.full.min.js') }}"></script>
<script>
$(function () {
    var UBAH       = @json((bool) $ubah);
    var ID_UBAH    = @json($ubah->Id_Supplier ?? null);
    var RUT_SIMPAN = @json($ubah ? route('master-data-perbarui', ['jenis' => $jenis]) : route('master-data-simpan', ['jenis' => $jenis]));
    var RUT_DAFTAR = @json($urlKembali);   // daftar dengan filter yang sama seperti sebelum form dibuka
    var JUDUL      = @json($conf['kolom']);
    // Teks tombol & swal per mode
    var KATA = UBAH
        ? { tanya: 'Update', ya: 'Yes, update it', proses: 'Updating...', selesai: 'updated', gagal: 'Not updated' }
        : { tanya: 'Save', ya: 'Yes, save it', proses: 'Saving...', selesai: 'saved', gagal: 'Not saved' };

    $('.select2bs4').select2({ theme: 'bootstrap4', width: '100%' });

    function teks(nilai) {
        if (nilai == null || String(nilai).trim() === '') { return '-'; }
        return $('<div>').text(String(nilai).trim()).html();
    }

    function nilai(id) { return $.trim($(id).val() || ''); }

    // Facility wajib kalau area Local (nilai pilihannya 'Lokal')
    function aturFasilitas() {
        $('#md-fasilitas-wajib').prop('hidden', $('#md-area').val() !== 'Lokal');
    }
    $('#md-area').on('change', aturFasilitas);
    aturFasilitas();

    // Priority boleh dikosongkan lagi: klik pilihan yang sedang aktif untuk melepasnya
    $('.md-prioritas')
        .on('mousedown', 'label', function () {
            var $r = $('#' + $(this).attr('for'));
            $r.data('tadi', $r.prop('checked'));
        })
        .on('click', 'input[type="radio"]', function () {
            if ($(this).data('tadi')) { this.checked = false; }
            $(this).data('tadi', false);
        });

    function dataForm() {
        var d = UBAH ? { id: ID_UBAH } : {};
        $('.nag-skin').find('input[name], select[name]').each(function () {
            if (this.type === 'checkbox') {
                d[this.name] = this.checked ? 1 : 0;
            } else if (this.type === 'radio') {
                // Kosong kalau tidak ada yang dipilih
                if (!(this.name in d)) { d[this.name] = ''; }
                if (this.checked) { d[this.name] = this.value; }
            } else {
                d[this.name] = $.trim($(this).val() || '');
            }
        });
        return d;
    }

    $('#md-btn-simpan').on('click', function () {
        // Semua yang wajib diperiksa dulu lalu ditampilkan sekaligus (pola form Invoice EXIM)
        var kurang = [];
        if (!nilai('#md-kode'))      { kurang.push('Code'); }
        if (!nilai('#md-nama'))      { kurang.push(JUDUL + ' Name'); }
        if (!nilai('#md-npwp'))      { kurang.push('NPWP (use - if none)'); }
        if (!nilai('#md-kategori'))  { kurang.push('Category'); }
        if (!nilai('#md-area'))      { kurang.push('Area'); }
        if ($('#md-area').val() === 'Lokal' && !nilai('#md-fasilitas')) { kurang.push('Facility (required for Local)'); }

        if (kurang.length) {
            Swal.fire({
                icon: 'warning',
                title: 'Please complete the form',
                html: 'The following must be filled in before ' + (UBAH ? 'updating' : 'saving') + ':<ul class="dn-swal-kurang"><li>'
                    + kurang.join('</li><li>') + '</li></ul>',
                confirmButtonText: 'OK',
                customClass: { popup: 'dn-swal' }
            });
            return;
        }

        var $tb = $(this);
        var ringkas = '<div class="dn-swal-ringkas">'
            + '<div><span>Code</span><b>' + teks(nilai('#md-kode').toUpperCase()) + '</b></div>'
            + '<div><span>' + JUDUL + '</span><b>' + teks(nilai('#md-nama').toUpperCase()) + '</b></div>'
            + '<div><span>Area</span><b>' + teks($('#md-area').find(':selected').text()) + '</b></div>'
            + '<div><span>Category</span><b>' + teks($('#md-kategori').find(':selected').text()) + '</b></div>'
            + '<div><span>Facility</span><b>' + teks($('#md-fasilitas').val()) + '</b></div>'
            + '<div><span>NPWP</span><b>' + teks(nilai('#md-npwp').toUpperCase()) + '</b></div>'
            + ($('.md-prioritas').length
                ? '<div><span>Priority</span><b>' + teks($('input[name="prioritas"]:checked').next('label').text()) + '</b></div>'
                : '')
            + '</div>';

        Swal.fire({
            icon: 'question',
            title: KATA.tanya + ' this ' + JUDUL.toLowerCase() + '?',
            html: ringkas,
            showCancelButton: true,
            confirmButtonText: '<i class="fa fa-save"></i> ' + KATA.ya,
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
            title: KATA.proses,
            allowOutsideClick: false, allowEscapeKey: false,
            showConfirmButton: false, customClass: { popup: 'dn-swal' },
            didOpen: function () { Swal.showLoading(); }
        });

        $.ajax({
            url: RUT_SIMPAN,
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            data: dataForm()
        }).done(function (res) {
            // Edit tanpa perubahan apa pun: tetap di halaman ini
            if (UBAH && res.berubah === 0) {
                $tb.prop('disabled', false);
                Swal.fire({
                    icon: 'info',
                    title: 'No changes',
                    html: 'Nothing was changed on <b>' + teks(res.kode) + ' - ' + teks(res.nama) + '</b>.',
                    confirmButtonText: 'OK',
                    customClass: { popup: 'dn-swal' }
                });
                return;
            }
            Swal.fire({
                icon: 'success',
                title: JUDUL + ' ' + KATA.selesai,
                html: '<b>' + teks(res.kode) + ' - ' + teks(res.nama) + '</b> has been ' + KATA.selesai + '.',
                confirmButtonText: 'OK',
                allowOutsideClick: false,
                customClass: { popup: 'dn-swal' }
            }).then(function () { window.location.href = RUT_DAFTAR; });
        }).fail(function (x) {
            $tb.prop('disabled', false);
            var res = x.responseJSON || {};
            var html = teks(res.message || 'Failed to ' + KATA.tanya.toLowerCase() + ', please try again.');
            if (res.kurang && res.kurang.length) {
                html += '<ul class="dn-swal-kurang"><li>' + res.kurang.map(teks).join('</li><li>') + '</li></ul>';
            }
            Swal.fire({
                icon: x.status === 409 ? 'warning' : 'error',
                title: x.status === 409 ? 'Already exists' : KATA.gagal,
                html: html,
                confirmButtonText: 'OK',
                customClass: { popup: 'dn-swal' }
            });
        });
    }
});
</script>
@endpush
@endsection
