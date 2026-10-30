// Dropdown Brand di Costing: brand milik buyer terpilih (signalbit_erp.masterbrand), bisa dicari,
// dan bila tidak ada yang cocok muncul "Add Brand <ketikan>" yang membuka modal tambah brand.
// Value yang dikirim = nama brand (act_costing.brand).
(function (window, $) {
    'use strict';

    var NEW_PREFIX = '__new__:';

    window.mktBrandSelect = function (opts) {
        var $select = $(opts.select);
        var $buyer = $(opts.buyer);
        var $modal = $(opts.modal);
        var current = String(opts.current || '');
        var esc = function (v) { return $('<span>').text(v == null ? '' : v).html(); };
        var title = function (v) { return window.mktTitleCase ? window.mktTitleCase(v) : v; };

        function render(item) {
            if (!item || item.loading) return item ? item.text : '';
            if (String(item.id).indexOf(NEW_PREFIX) === 0) {
                return $('<span class="brand-opt brand-opt--add"><i class="fas fa-plus-square"></i><span>Add Brand ' + esc(item.text) + '</span></span>');
            }
            if (item.id === '' || item.id === undefined) return item.text;
            return $('<span class="brand-opt"><span class="brand-radio"></span><span>' + esc(title(item.text)) + '</span></span>');
        }

        $select.select2({
            theme: 'bootstrap4',
            width: '100%',
            allowClear: true,
            placeholder: 'Choose Brand',
            tags: true,
            dropdownCssClass: 'brand-dropdown',
            language: { noResults: function () { return 'Belum ada brand, ketik nama brand untuk menambah'; } },
            templateResult: render,
            templateSelection: function (item) { return item.id === '' ? item.text : title(item.text); },
            // "Add Brand" hanya muncul bila tidak ada brand yang namanya sama persis.
            createTag: function (params) {
                var term = $.trim(params.term).replace(/\s+/g, ' ');
                if (!term || !$buyer.val()) return null;
                var exists = $select.find('option').filter(function () {
                    return this.value !== '' && this.text.toUpperCase() === term.toUpperCase();
                }).length > 0;
                return exists ? null : { id: NEW_PREFIX + term, text: term };
            },
            insertTag: function (data, tag) { data.push(tag); },
        }).on('select2:selecting', function (e) {
            var id = String(e.params.args.data.id);
            if (id.indexOf(NEW_PREFIX) !== 0) return;
            e.preventDefault();
            $select.select2('close');
            openModal(id.slice(NEW_PREFIX.length));
        });

        function setOptions(brands, keep) {
            var found = false;
            var option = function (b) {
                if (b.brand === keep) found = true;
                return '<option value="' + esc(b.brand) + '" data-code="' + esc(b.kd_brand) + '">' + esc(b.brand) + '</option>';
            };
            var html = '<option value=""></option>' + brands.map(option).join('');
            // Brand lama di costing yang belum ada di master tetap ditampilkan supaya tidak hilang saat edit.
            if (keep && !found) html += '<option value="' + esc(keep) + '">' + esc(keep) + '</option>';
            $select.html(html).val(keep || '').trigger('change.select2');
        }

        function loadBrands(keep) {
            var buyer = $buyer.val();
            $select.prop('disabled', !buyer || !!opts.readonly);
            if (!buyer) {
                setOptions([], '');
                return;
            }
            $.get(opts.urlList, { buyer: buyer }).done(function (res) {
                setOptions((res && res.data) || [], keep);
            }).fail(function () {
                setOptions([], keep);
            });
        }

        function openModal(name) {
            $modal.find('.brand-modal-buyer').val($buyer.find(':selected').text().trim());
            $modal.find('.brand-modal-code').val('');
            $modal.find('.brand-modal-name').val(name.toUpperCase());
            $modal.modal('show');
            setTimeout(function () { $modal.find('.brand-modal-code').trigger('focus'); }, 300);
        }

        $modal.on('input', '.brand-modal-code, .brand-modal-name', function () {
            var pos = this.selectionStart;
            this.value = this.value.toUpperCase();
            this.setSelectionRange(pos, pos);
        });

        $modal.on('click', '.brand-modal-save', function () {
            var $btn = $(this);
            var code = $.trim($modal.find('.brand-modal-code').val());
            var name = $.trim($modal.find('.brand-modal-name').val());
            if (!code || !name) {
                Swal.fire('Peringatan', 'Brand Code dan Brand Name wajib diisi.', 'warning');
                return;
            }
            $btn.prop('disabled', true);
            $.post(opts.urlStore, { _token: opts.csrf, buyer: $buyer.val(), kd_brand: code, brand: name }).done(function (res) {
                if (!res || res.status !== 200) {
                    Swal.fire('Gagal', (res && res.message) || 'Brand tidak tersimpan.', 'error');
                    return;
                }
                var b = res.data;
                $select.find('option').filter(function () { return this.value === b.brand; }).remove();
                $select.append('<option value="' + esc(b.brand) + '" data-code="' + esc(b.kd_brand) + '">' + esc(b.brand) + '</option>');
                $select.val(b.brand).trigger('change');
                $modal.modal('hide');
            }).fail(function () {
                Swal.fire('Gagal', 'Brand tidak tersimpan.', 'error');
            }).always(function () {
                $btn.prop('disabled', false);
            });
        });

        $buyer.on('change', function () {
            current = '';
            loadBrands('');
        });
        loadBrands(current);
    };
})(window, jQuery);
