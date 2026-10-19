{{-- ===========================================================================
     Create / Edit Master Item General.
     Isian & aturannya mengikuti form Signalbit pages/others/master.php (mod=2).
     CoA wajib untuk mapping ATK & UMUM, selain itu dikunci "Not required" ('-').
     Gambar baru disimpan di storage NDS.
     =========================================================================== --}}
@extends('layouts.index')

@section('custom-link')
<link rel="stylesheet" href="{{ asset('plugins/select2/css/select2.min.css') }}">
<link rel="stylesheet" href="{{ asset('plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}">
@include('export-import.invoice._skin')
<style>
.nag-skin .md-ket { display: block; margin-top: 4px; font-size: 11px; color: #64748b; }
.nag-skin .md-ket i { margin-right: 4px; color: #94a3b8; }
.nag-skin .md-besar { text-transform: uppercase; }
.nag-skin .md-besar::placeholder { text-transform: none; }
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
/* Di sebelah Inventory Mapping: CoA wajib atau tidak untuk mapping yang dipilih.
   Diberi jarak setinggi label supaya sejajar dengan kotak pilihannya. */
.nag-skin .md-coa-status {
  display: flex;
  align-items: center;
  gap: 8px;
  min-height: 31px;
  margin-top: 23px;
  padding: 5px 11px;
  border: 1px solid #e2e8f0;
  border-radius: 8px;
  background: #f1f5f9;
  color: #475569;
  font-size: 12px;
  line-height: 1.3;
}
.nag-skin .md-coa-status.is-wajib { background: #fef3c7; border-color: #fde68a; color: #92400e; }
.nag-skin .md-coa-status:empty { display: none; }

/* Gambar: kotak pratinjau selebar kolom + tombol pilih / hapus di bawahnya */
.nag-skin .md-pratinjau {
  width: 100%;
  height: 158px;
  display: flex;
  align-items: center;
  justify-content: center;
  overflow: hidden;
  border: 1px dashed #cbd5e1;
  border-radius: 12px;
  background: #f8fafc;
  color: #94a3b8;
  font-size: 12px;
  text-align: center;
}
.nag-skin .md-pratinjau i { display: block; margin-bottom: 6px; font-size: 28px; opacity: .6; }
.nag-skin .md-pratinjau img { width: 100%; height: 100%; object-fit: contain; }
.nag-skin .md-unggah-aksi { display: flex; flex-wrap: wrap; gap: 6px; margin-top: 8px; }
.nag-skin .md-nama-file { display: block; margin-top: 5px; font-size: 11.5px; color: #334155; word-break: break-all; }
.nag-skin .md-nama-file:empty { display: none; }
.nag-skin .md-nama-file.is-hapus { color: #b91c1c; }
.nag-skin .btn-md-pilih {
  border: 1px solid #cbd5e1;
  background: #fff;
  color: #1e3a5f;
  font-size: 12.5px;
  font-weight: 600;
}
.nag-skin .btn-md-pilih:hover { background: #f1f5f9; }
.nag-skin .btn-md-hapus {
  border: 1px solid #fecaca;
  background: #fff;
  color: #b91c1c;
  font-size: 12.5px;
  font-weight: 600;
}
.nag-skin .btn-md-hapus:hover { background: #fef2f2; }
</style>
@endsection

@section('content')
{{-- $ubah terisi = mode edit (data lama), kosong = create --}}
<div class="nag-skin dn-kontrol-sm">

    <div class="dn-kepala-halaman">
        <div class="dn-kepala-kiri">
            <span class="dn-kepala-ikon"><i class="fas {{ $ubah ? 'fa-pen-to-square' : 'fa-boxes-stacked' }}"></i></span>
            <div>
                <h1 class="dn-kepala-judul">{{ $ubah ? 'Edit' : 'Create' }} Master Item General</h1>
                @if ($ubah)
                    <p class="dn-kepala-sub">
                        Update general item data
                        <span class="md-sub-kode">{{ $ubah->goods_code ?: '-' }} &middot; ID {{ $ubah->id_item }}</span>
                        @if ($ubah->non_aktif === 'Y')
                            <span class="dn-pil is-batal">Inactive</span>
                        @endif
                    </p>
                @else
                    <p class="dn-kepala-sub">Input form to add new general (non-production) item</p>
                @endif
            </div>
        </div>
    </div>

    {{-- ===== Data item: gambar di kiri, isian di kanan ===== --}}
    <div class="card">
        <div class="card-header">
            <h5 class="card-title"><i class="fas fa-box"></i> Item Details</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-lg-3 col-md-4">
                    <div class="form-group mb-0">
                        <label>Image</label>
                        <div class="md-pratinjau" id="md-pratinjau">
                            @if ($gambarUrl)
                                <img src="{{ $gambarUrl }}" alt="">
                            @else
                                <span><i class="fas fa-image"></i>{{ ($ubah->file_gambar ?? '') !== '' ? 'Image file not found' : 'No image' }}</span>
                            @endif
                        </div>
                        <input type="file" id="md-file" accept=".jpg,.jpeg,.png,.webp" hidden>
                        <div class="md-unggah-aksi">
                            <button type="button" class="btn btn-sm btn-md-pilih" id="md-btn-pilih">
                                <i class="fas fa-upload"></i> {{ ($ubah->file_gambar ?? '') !== '' ? 'Replace' : 'Choose image' }}
                            </button>
                            <button type="button" class="btn btn-sm btn-md-hapus" id="md-btn-hapus-gambar" {{ ($ubah->file_gambar ?? '') !== '' ? '' : 'hidden' }}>
                                <i class="fas fa-trash"></i> Remove
                            </button>
                        </div>
                        <span class="md-nama-file" id="md-nama-file">{{ $ubah->file_gambar ?? '' }}</span>
                        <small class="md-ket"><i class="fas fa-circle-info"></i>JPG, PNG or WEBP.</small>
                    </div>
                </div>

                <div class="col-lg-9 col-md-8">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="md-kode">Item Code <span class="dn-wajib" title="Required">*</span></label>
                                <input type="text" class="form-control md-besar" id="md-kode" name="goods_code" value="{{ $ubah->goods_code ?? '' }}"
                                    maxlength="{{ $panjang['goods_code'] }}" placeholder="Item code" autocomplete="off">
                            </div>
                        </div>
                        <div class="col-md-8">
                            <div class="form-group">
                                <label for="md-nama">Description <span class="dn-wajib" title="Required">*</span></label>
                                <input type="text" class="form-control md-besar" id="md-nama" name="itemdesc" value="{{ $ubah->itemdesc ?? '' }}"
                                    maxlength="{{ $panjang['itemdesc'] }}" placeholder="Item description" autocomplete="off">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label for="md-warna">Color <span class="dn-wajib" title="Required">*</span></label>
                                <input type="text" class="form-control md-besar" id="md-warna" name="color" value="{{ $ubah->color ?? '' }}"
                                    maxlength="{{ $panjang['color'] }}" placeholder="Use - if none" autocomplete="off">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label for="md-ukuran">Size <span class="dn-wajib" title="Required">*</span></label>
                                <input type="text" class="form-control md-besar" id="md-ukuran" name="size" value="{{ $ubah->size ?? '' }}"
                                    maxlength="{{ $panjang['size'] }}" placeholder="Use - if none" autocomplete="off">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="md-tipe">Item Type</label>
                                <select class="form-control select2bs4" id="md-tipe" name="tipe_item">
                                    <option value="">-- Select item type --</option>
                                    @foreach ($tipe as $t)
                                        <option value="{{ $t }}" {{ ($ubah->tipe_item ?? null) === $t ? 'selected' : '' }}>{{ $t }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="md-mutasi">Mutation Type</label>
                                <select class="form-control select2bs4" id="md-mutasi" name="tipe_mut">
                                    <option value="">-- None --</option>
                                    @foreach ($mutasi as $nilai => $label)
                                        <option value="{{ $nilai }}" {{ ($ubah->tipe_mut ?? null) === $nilai ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-8">
                            <div class="form-group mb-md-0">
                                <label for="md-kategori">Inventory Mapping <span class="dn-wajib" title="Required">*</span></label>
                                <select class="form-control select2bs4" id="md-kategori" name="n_code_category">
                                    <option value="">-- Select inventory mapping --</option>
                                    @foreach ($kategori as $id => $nama)
                                        <option value="{{ $id }}" {{ $ubah && (string) $ubah->n_code_category === (string) $id ? 'selected' : '' }}>{{ $nama }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            {{-- Tanda wajib CoA - mengikuti mapping yang dipilih --}}
                            <div class="md-coa-status" id="md-coa-status"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ===== CoA (wajib untuk ATK & UMUM) + tombol ===== --}}
    <div class="card mt-3">
        <div class="card-header">
            <h5 class="card-title"><i class="fas fa-book"></i> Chart of Accounts</h5>
        </div>
        <div class="card-body">
            <div class="row">
                @foreach ($coa as $kolom => $pilihan)
                    @php($nilaiLama = trim((string) ($ubah->$kolom ?? '')))
                    <div class="col-lg-3 col-md-6">
                        <div class="form-group">
                            <label for="md-{{ $kolom }}">{{ $coaLabel[$kolom] }} <span class="dn-wajib md-coa-wajib" title="Required" hidden>*</span></label>
                            <select class="form-control select2bs4 md-coa" id="md-{{ $kolom }}" name="{{ $kolom }}">
                                <option value="">-- Select account --</option>
                                <option value="-" {{ $nilaiLama === '-' ? 'selected' : '' }}>Not required</option>
                                {{-- Akun lama yang tidak ada di daftar tetap ditampilkan supaya tidak hilang --}}
                                @if ($nilaiLama !== '' && $nilaiLama !== '-' && ! $pilihan->has($nilaiLama))
                                    <option value="{{ $nilaiLama }}" selected>{{ $nilaiLama }}</option>
                                @endif
                                @foreach ($pilihan as $no => $label)
                                    <option value="{{ $no }}" {{ $nilaiLama === (string) $no ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                @endforeach
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

@push('scripts')
<script src="{{ asset('plugins/select2/js/select2.full.min.js') }}"></script>
<script>
$(function () {
    var UBAH        = @json((bool) $ubah);
    var ID_UBAH     = @json($ubah->id_item ?? null);
    var RUT_SIMPAN  = @json($ubah ? route('master-data-item-perbarui') : route('master-data-item-simpan'));
    var RUT_DAFTAR  = @json($urlKembali);   // daftar dengan filter yang sama seperti sebelum form dibuka
    var WAJIB_COA   = @json($wajibCoa);
    var BATAS_SERVER = @json($batasUpload);   // batas upload PHP server - hanya untuk pesan galat
    var EKSTENSI    = @json($ekstensi);
    var ADA_GAMBAR  = @json(($ubah->file_gambar ?? '') !== '');
    var KATA = UBAH
        ? { tanya: 'Update', ya: 'Yes, update it', proses: 'Updating...', selesai: 'updated', gagal: 'Not updated' }
        : { tanya: 'Save', ya: 'Yes, save it', proses: 'Saving...', selesai: 'saved', gagal: 'Not saved' };

    $('.select2bs4').select2({ theme: 'bootstrap4', width: '100%' });

    function teks(nilai) {
        if (nilai == null || String(nilai).trim() === '') { return '-'; }
        return $('<div>').text(String(nilai).trim()).html();
    }
    function nilai(id) { return $.trim($(id).val() || ''); }

    // ---------- CoA: wajib untuk ATK & UMUM, selain itu dikunci "Not required" ----------
    function wajibCoa() { return WAJIB_COA.indexOf(String($('#md-kategori').val())) !== -1; }

    function aturCoa() {
        var wajib = wajibCoa();
        $('.md-coa').each(function () {
            var $s = $(this);
            // Nilai dibaca sebelum opsi '-' dimatikan (opsi terpilih yang dimatikan terbaca null)
            if (wajib) {
                if ($s.val() === '-') { $s.val(''); }
            } else {
                $s.val('-');
            }
            $s.find('option[value="-"]').prop('disabled', wajib);
            $s.prop('disabled', !wajib).trigger('change.select2');
        });
        $('.md-coa-wajib').prop('hidden', !wajib);
        // Keterangan di sebelah Inventory Mapping
        var $status = $('#md-coa-status').toggleClass('is-wajib', wajib);
        if (!$('#md-kategori').val()) {
            $status.empty();
        } else if (wajib) {
            $status.html('<i class="fas fa-circle-exclamation"></i><span>Chart of accounts is required for this mapping.</span>');
        } else {
            $status.html('<i class="fas fa-circle-info"></i><span>Chart of accounts is not required for this mapping.</span>');
        }
    }
    $('#md-kategori').on('change', aturCoa);
    aturCoa();

    // ---------- Gambar ----------
    var fileBaru = null;
    var hapusGambar = false;

    function tampilPratinjau(src, pesan) {
        var $p = $('#md-pratinjau').empty();
        if (src) {
            $p.append($('<img alt="">').attr('src', src));
        } else {
            $p.html('<span><i class="fas fa-image"></i>' + teks(pesan) + '</span>');
        }
    }

    $('#md-btn-pilih').on('click', function () { $('#md-file').val('').trigger('click'); });

    $('#md-file').on('change', function () {
        var f = this.files && this.files[0];
        if (!f) { return; }
        var ext = (f.name.split('.').pop() || '').toLowerCase();
        // Ukuran tidak dibatasi; yang dicek hanya jenis filenya
        if (EKSTENSI.indexOf(ext) === -1) {
            Swal.fire({ icon: 'warning', title: 'Image not accepted', text: 'Image must be JPG, PNG or WEBP.', customClass: { popup: 'dn-swal' } });
            return;
        }
        fileBaru = f;
        hapusGambar = false;
        tampilPratinjau(URL.createObjectURL(f));
        $('#md-nama-file').text(f.name + ' (new)').removeClass('is-hapus');
        $('#md-btn-hapus-gambar').prop('hidden', false);
    });

    $('#md-btn-hapus-gambar').on('click', function () {
        fileBaru = null;
        hapusGambar = ADA_GAMBAR;
        tampilPratinjau(null, 'No image');
        $('#md-nama-file').text(ADA_GAMBAR ? 'Image will be removed when saved' : '').toggleClass('is-hapus', ADA_GAMBAR);
        $(this).prop('hidden', true);
    });

    function keteranganGambar() {
        if (fileBaru) { return 'New image'; }
        if (hapusGambar) { return 'Removed'; }
        return ADA_GAMBAR ? (UBAH ? 'Unchanged' : '-') : 'None';
    }

    // ---------- Simpan ----------
    function dataForm() {
        var fd = new FormData();
        if (UBAH) { fd.append('id', ID_UBAH); }
        // Isian yang dikunci (CoA tidak wajib) tidak ikut terkirim - server mengisinya '-'
        $('.nag-skin').find('input[name], select[name]').each(function () {
            if (!this.disabled) { fd.append(this.name, $.trim($(this).val() || '')); }
        });
        if (fileBaru) { fd.append('gambar', fileBaru); }
        if (hapusGambar) { fd.append('hapus_gambar', 1); }
        return fd;
    }

    $('#md-btn-simpan').on('click', function () {
        var kurang = [];
        if (!nilai('#md-kode'))     { kurang.push('Item Code'); }
        if (!nilai('#md-nama'))     { kurang.push('Description'); }
        if (!nilai('#md-warna'))    { kurang.push('Color (use - if none)'); }
        if (!nilai('#md-ukuran'))   { kurang.push('Size (use - if none)'); }
        if (!nilai('#md-kategori')) { kurang.push('Inventory Mapping'); }
        if (wajibCoa()) {
            $('.md-coa').each(function () {
                if (!$(this).val() || $(this).val() === '-') {
                    kurang.push('COA ' + $.trim($('label[for="' + this.id + '"]').clone().children().remove().end().text()));
                }
            });
        }

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
            + '<div><span>Item Code</span><b>' + teks(nilai('#md-kode').toUpperCase()) + '</b></div>'
            + '<div><span>Description</span><b>' + teks(nilai('#md-nama').toUpperCase()) + '</b></div>'
            + '<div><span>Inventory Mapping</span><b>' + teks($('#md-kategori').find(':selected').text()) + '</b></div>'
            + '<div><span>Item Type</span><b>' + teks(nilai('#md-tipe')) + '</b></div>'
            + '<div><span>Image</span><b>' + teks(keteranganGambar()) + '</b></div>'
            + '</div>';

        Swal.fire({
            icon: 'question',
            title: KATA.tanya + ' this item?',
            html: ringkas,
            showCancelButton: true,
            confirmButtonText: '<i class="fa fa-save"></i> ' + KATA.ya,
            cancelButtonText: 'Cancel',
            reverseButtons: true,
            focusCancel: true,
            customClass: { popup: 'dn-swal' }
        }).then(function (pilih) {
            if (pilih.isConfirmed) { kirimSimpan($tb); }
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
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'), 'Accept': 'application/json' },
            data: dataForm(),
            processData: false,
            contentType: false
        }).done(function (res) {
            if (UBAH && res.berubah === 0) {
                $tb.prop('disabled', false);
                Swal.fire({
                    icon: 'info',
                    title: 'No changes',
                    html: 'Nothing was changed on <b>' + teks(res.kode) + '</b>.',
                    confirmButtonText: 'OK',
                    customClass: { popup: 'dn-swal' }
                });
                return;
            }
            Swal.fire({
                icon: 'success',
                title: 'Item ' + KATA.selesai,
                html: '<b>' + teks(res.kode) + ' - ' + teks(res.nama) + '</b> has been ' + KATA.selesai + '.',
                confirmButtonText: 'OK',
                allowOutsideClick: false,
                customClass: { popup: 'dn-swal' }
            }).then(function () { window.location.href = RUT_DAFTAR; });
        }).fail(function (x) {
            $tb.prop('disabled', false);
            var res = x.responseJSON || {};
            var pesan = res.message || (x.status === 413 ? 'The image is larger than the server upload limit (' + BATAS_SERVER + ').' :'Failed to ' + KATA.tanya.toLowerCase() + ', please try again.');
            var html = teks(pesan);
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
