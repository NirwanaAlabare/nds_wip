{{-- Modal "Add Brand" + gaya dropdown Brand. Dipakai Create & Edit Costing bersama public/assets/dist/js/marketing-brand-select.js. --}}
<style>
    .brand-dropdown .select2-results__option { padding: 6px 12px; color: #1e293b; font-size: 13px; font-weight: 500; }
    .brand-dropdown .select2-results__option[aria-selected="true"] { background: transparent; color: #1e293b; }
    .brand-dropdown .select2-results__option--highlighted[aria-selected] { background: #f1f5f9; color: #1e293b; }
    .brand-opt { display: flex; align-items: center; gap: 8px; }
    .brand-radio { flex: 0 0 auto; width: 14px; height: 14px; border: 1.5px solid #94a3b8; border-radius: 50%; background: #fff; }
    .brand-dropdown .select2-results__option[aria-selected="true"] .brand-radio { border: 4px solid #284a63; }
    .brand-opt--add { padding: 2px 4px; border-radius: 3px; background: #e8eef5; color: #1e293b; }
    .brand-opt--add i { color: #284a63; }
    .brand-modal { max-width: 420px; }
    .brand-modal .modal-content { border: 0; border-radius: 12px; }
    .brand-modal .modal-header { justify-content: center; border-bottom: 1px solid #e2e8f0; }
    .brand-modal .modal-title { color: #284a63; font-family: 'Poppins', sans-serif; font-size: 16px; font-weight: 600; }
    .brand-modal .brand-modal-card { display: grid; gap: 14px; padding: 14px; border-radius: 10px; box-shadow: 0 3px 10px rgba(15, 23, 42, .10); }
    .brand-modal label { display: block; margin-bottom: 6px; color: #1e293b; font-family: 'Poppins', sans-serif; font-size: 14px; font-weight: 500; }
    .brand-modal .brand-modal-buyer-label { color: #94a3b8; }
    .brand-modal .form-control { height: 42px; border: 1px solid #d6d6d6; border-radius: 10px; font-size: 14px; }
    .brand-modal .form-control[readonly] { background: #f8fafc; color: #64748b; }
    .brand-modal .modal-footer { justify-content: flex-end; gap: 10px; border-top: 0; }
</style>

<div class="modal fade" id="modal-add-brand" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered brand-modal" role="document">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title">Add Brand</h5></div>
            <div class="modal-body">
                <div class="brand-modal-card">
                    <div>
                        <label class="brand-modal-buyer-label">Buyer</label>
                        <input type="text" class="form-control brand-modal-buyer" readonly>
                    </div>
                    <div>
                        <label for="brand-modal-code">Brand Code</label>
                        <input type="text" id="brand-modal-code" class="form-control brand-modal-code" maxlength="20" placeholder="Brand Code" autocomplete="off">
                    </div>
                    <div>
                        <label for="brand-modal-name">Brand Name</label>
                        <input type="text" id="brand-modal-name" class="form-control brand-modal-name" maxlength="120" placeholder="Brand Name" autocomplete="off">
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="mkt-btn mkt-btn--red mkt-btn--cancel" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="mkt-btn mkt-btn--green brand-modal-save">Save</button>
            </div>
        </div>
    </div>
</div>
