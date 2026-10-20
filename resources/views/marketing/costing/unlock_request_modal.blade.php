{{-- Modal Request Unlock (Costing / BOM). Buka dengan openUnlockRequest({ type, id, number, buyer, onSuccess }). --}}
<div class="modal fade" id="modal-request-unlock" tabindex="-1" aria-labelledby="unlock-request-title" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered unlock-request-dialog">
        <div class="modal-content unlock-request-modal">
            <div class="unlock-request-head">
                <span class="unlock-request-no" id="unlock-request-no"></span>
                <span class="unlock-request-buyer" id="unlock-request-buyer"></span>
                <span class="unlock-request-badges">
                    <span class="unlock-request-approved"><i class="fas fa-circle"></i> Approved</span>
                    <span class="unlock-request-locked"><i class="fas fa-lock"></i>Locked</span>
                </span>
            </div>
            <p class="unlock-request-note">This document is approved and can't be edited. Request an unlock from the <b>marketing manager</b> if you need to change it.</p>

            <h5 class="unlock-request-title" id="unlock-request-title">Request Unlock</h5>
            <label for="unlock-request-reason" class="unlock-request-label">Reason for Change</label>
            {{-- unlock_cost / unlock_jo.reason hanya varchar(100). --}}
            <textarea id="unlock-request-reason" class="unlock-request-reason" rows="2" maxlength="100"></textarea>
            <div class="unlock-request-error" id="unlock-request-error" hidden></div>

            <div class="unlock-request-actions">
                <button type="button" class="mkt-btn mkt-btn--red mkt-btn--cancel" data-bs-dismiss="modal" data-dismiss="modal">Cancel</button>
                <button type="button" class="mkt-btn mkt-btn--green" id="unlock-request-save">Save</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<style>
    .unlock-request-dialog { max-width: 600px; }
    .unlock-request-modal { padding: 20px 24px 18px; border: 0; border-radius: 30px; background: #fff; box-shadow: 0 12px 32px rgba(15, 23, 42, .28); }
    .unlock-request-head { display: flex; flex-wrap: wrap; align-items: center; gap: 10px; }
    .unlock-request-no { color: #334155; font-size: 13px; font-weight: 600; }
    .unlock-request-buyer { color: #64748b; font-size: 13px; }
    .unlock-request-badges { display: inline-flex; align-items: center; gap: 10px; margin-left: auto; }
    .unlock-request-approved { display: inline-flex; align-items: center; gap: 5px; padding: 1px 7px; border-radius: 3px; background: #dcfce7; color: #15803d; font-size: 10px; font-weight: 500; }
    .unlock-request-approved .fa-circle { font-size: 4px; }
    .unlock-request-locked { display: inline-flex; align-items: center; gap: 3px; color: #64748b; font-size: 9px; font-weight: 500; }
    .unlock-request-locked i { color: #0f172a; font-size: 10px; }
    .unlock-request-note { margin: 16px 0 18px; color: #94a3b8; font-size: 10px; line-height: 1.6; }
    .unlock-request-note b { color: #64748b; }
    .unlock-request-title { margin: 0; color: #334155; font-size: 13px; font-weight: 600; }
    .unlock-request-label { display: block; margin: 2px 0 14px; color: #94a3b8; font-size: 9px; font-weight: 400 !important; }
    .unlock-request-reason { display: block; width: 100%; min-height: 60px; padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 8px; color: #334155; font-size: 12px; resize: vertical; outline: 0; }
    .unlock-request-reason:focus { border-color: #94a3b8; box-shadow: 0 0 0 2px rgba(148, 163, 184, .2); }
    .unlock-request-error { margin-top: 4px; color: #dc2626; font-size: 10px; }
    .unlock-request-actions { display: flex; justify-content: flex-end; gap: 14px; margin-top: 6px; }
</style>
<script>
    (function() {
        let current = null;
        const $modal = $('#modal-request-unlock');

        window.openUnlockRequest = function(options) {
            current = options;
            $('#unlock-request-no').text(options.number || '');
            $('#unlock-request-buyer').text(options.buyer || '');
            $('#unlock-request-reason').val('');
            $('#unlock-request-error').prop('hidden', true).text('');
            $('#unlock-request-save').prop('disabled', false);
            $modal.modal('show');
        };

        $modal.on('shown.bs.modal', () => $('#unlock-request-reason').trigger('focus'));

        $('#unlock-request-save').on('click', function() {
            const reason = $('#unlock-request-reason').val().trim();
            if (!reason) {
                $('#unlock-request-error').prop('hidden', false).text('Reason for change is required.');
                return;
            }
            const $btn = $(this).prop('disabled', true);
            $.post("{{ route('marketing-request-unlock') }}", {
                _token: "{{ csrf_token() }}",
                type: current.type,
                id: current.id,
                reason: reason
            }).done(function(res) {
                $modal.modal('hide');
                if (current.onSuccess) current.onSuccess(res);
            }).fail(function(xhr) {
                const errors = xhr.responseJSON?.errors;
                $('#unlock-request-error').prop('hidden', false)
                    .text(errors ? Object.values(errors).flat()[0] : (xhr.responseJSON?.message || 'Terjadi kesalahan.'));
                $btn.prop('disabled', false);
            });
        });
    })();
</script>
@endpush
