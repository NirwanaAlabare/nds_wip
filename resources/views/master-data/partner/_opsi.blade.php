{{-- Buyer / Agent hanya untuk customer (sama dengan form Signalbit); To Account Manual untuk keduanya.
     Dipakai form.blade.php - letaknya beda antara customer dan supplier yang punya Priority. --}}
<div class="col-md-4">
    <div class="form-group">
        <label>{{ $isCustomer ? 'Customer Type' : 'Options' }}</label>
        <div class="md-cek">
            @if ($isCustomer)
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="md-buyer" name="tipe_buyer" value="1" {{ ($ubah->tipe_buyer ?? '') === 'Y' ? 'checked' : '' }}>
                    <label class="form-check-label" for="md-buyer">Buyer</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="md-agent" name="tipe_agent" value="1" {{ ($ubah->tipe_agent ?? '') === 'Y' ? 'checked' : '' }}>
                    <label class="form-check-label" for="md-agent">Agent</label>
                </div>
            @endif
            <div class="form-check">
                <input class="form-check-input" type="checkbox" id="md-to-account-manual" name="to_account_manual" value="1" {{ (int) ($ubah->to_account_manual ?? 0) === 1 ? 'checked' : '' }}>
                <label class="form-check-label" for="md-to-account-manual">To Account Manual</label>
            </div>
        </div>
    </div>
</div>
