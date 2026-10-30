{{-- Term of Payment - dipakai form.blade.php. Supplier dengan kolom baru menaruhnya di bawah Customs Classification
     bersama Options, yang lain di sebelah Facility. $lebar = lebar kolom grid. --}}
<div class="col-md-{{ $lebar }}">
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
