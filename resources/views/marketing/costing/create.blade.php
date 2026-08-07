@extends('layouts.index')

@section('custom-link')
    <link rel="stylesheet" href="{{ asset('plugins/select2/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}">

    <style>
        :root {
            --sb-navy: #0f172a;
            --sb-blue: #3085d6;
            --sb-blue-dark: #1e3a8a;
            --sb-border: #e2e8f0;
        }

        /* ============ CARD UTAMA ============ */
        .card-sb {
            border: none;
            border-radius: 18px;
            overflow: hidden;
            box-shadow: 0 10px 40px rgba(30, 41, 59, 0.08);
        }

        .card-sb > .card-header {
            background: var(--sb-navy) !important;
            border: none;
            padding: 1.6rem 2rem;
            position: relative;
        }

        .card-sb > .card-header::before {
            content: "";
            position: absolute;
            left: 0; top: 0; bottom: 0;
            width: 5px;
            background: repeating-linear-gradient(180deg, var(--sb-blue) 0px, var(--sb-blue) 10px, transparent 10px, transparent 20px);
        }

        .card-sb > .card-header .card-eyebrow {
            font-size: 0.7rem;
            font-weight: 700;
            letter-spacing: 1.4px;
            text-transform: uppercase;
            color: #5aa9f0;
            display: block;
            margin-bottom: 4px;
        }

        .card-sb > .card-header .card-title {
            font-size: 1.15rem;
            font-weight: 700;
            color: #f8fafc !important;
            margin: 0;
        }

        .card-sb > .card-body {
            background: #fbfcff;
            padding: 1.75rem 2rem 2rem;
        }

        /* ============ SECTION CARD (pengelompokan form) ============ */
        .form-section {
            background: #fff;
            border: 1px solid #eef1f5;
            border-radius: 14px;
            padding: 1.3rem 1.5rem 1rem;
            margin-bottom: 1.4rem;
            box-shadow: 0 2px 8px rgba(15, 23, 42, 0.03);
        }

        .form-section-title {
            font-size: 13px;
            font-weight: 700;
            color: var(--sb-navy);
            text-transform: uppercase;
            letter-spacing: 0.4px;
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 1.1rem;
            padding-bottom: 0.7rem;
            border-bottom: 1px solid #eef1f5;
        }

        .form-section-title i {
            color: var(--sb-blue);
            font-size: 13px;
        }

        /* ============ FORM CONTROLS ============ */
        .form-group label {
            font-size: 12.5px;
            font-weight: 700;
            color: #475569;
            margin-bottom: 0.3rem;
        }

        .form-control,
        .select2-container--bootstrap4 .select2-selection {
            border-radius: 8px;
            border: 1.5px solid var(--sb-border);
        }
        .form-control:focus,
        .select2-container--bootstrap4.select2-container--focus .select2-selection {
            border-color: var(--sb-blue) !important;
            box-shadow: 0 0 0 3px rgba(48, 133, 214, 0.12) !important;
        }

        /* Section highlight khusus untuk angka (Qty/SMV/VAT/Rate) */
        .form-section.form-section-figures {
            background: #f4f8fd;
            border-color: #dbe7f7;
        }

        /* Section upload gambar */
        .upload-preview-box {
            border: 1.5px dashed #cbd5e1;
            border-radius: 12px;
            padding: 1rem;
            background: #fbfcff;
            text-align: center;
            min-height: 160px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }

        /* Submit button */
        .btn-success {
            border-radius: 8px;
            font-weight: 700;
        }
    </style>
@endsection

@section('content')
<div class="card card-sb">
    <div class="card-header">
        <span class="card-eyebrow">Marketing &middot; Costing</span>
        <h3 class="card-title" style="font-size: 1.1rem;">Form Create Costing</h3>
    </div>

    <form action="{{ route('store-costing') }}" method="POST" id="form-costing" enctype="multipart/form-data">
        @csrf
        <div class="card-body">

            {{-- ============ SECTION: INFORMASI DASAR ============ --}}
            <div class="form-section">
                <div class="form-section-title"><i class="fas fa-info-circle"></i> Buyer</div>
                <div class="row">
                    <div class="col-md-3 form-group">
                        <label>Buyer Name</label>
                        <select name="buyer" id="buyer" class="form-control select2bs4" required>
                            <option value="">Pilih Buyer Name</option>
                            @foreach ($buyers as $b)
                                <option value="{{ $b->id_supplier ?? $b->Id_Supplier }}">{{ $b->supplier ?? $b->Supplier }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3 form-group">
                        <label>Brand</label>
                        <input type="text" name="brand" class="form-control" placeholder="Brand" required>
                    </div>
                    <div class="col-md-3 form-group">
                        <label>Season</label>
                        <select name="season_id" id="season_id" class="form-control select2bs4" required>
                            <option value="">Pilih Season</option>
                            @foreach ($seasons as $s)
                                <option value="{{ $s->id_season }}">{{ $s->season }} - {{ $s->season_desc }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3 form-group">
                        <label>Style</label>
                        <input type="text" name="style" class="form-control" placeholder="Style" required>
                    </div>
                </div>
            </div>

            {{-- ============ SECTION: PRODUK & TIPE ============ --}}
            <div class="form-section">
                <div class="form-section-title"><i class="fas fa-tshirt"></i> Produk & Tipe</div>
                <div class="row">
                    <div class="col-md-3 form-group">
                        <label>Product Group</label>
                        <select name="product_group" id="product_group" class="form-control select2bs4" required>
                            <option value="">Pilih Product Group</option>
                            @foreach ($product_groups as $pg)
                                <option value="{{ $pg->product_group }}">{{ $pg->product_group }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3 form-group">
                        <label>Product Item</label>
                        <select name="product_item" id="product_item" class="form-control select2bs4" required>
                            <option value="">Pilih Product Item</option>
                        </select>
                    </div>
                    <div class="col-md-3 form-group">
                        <label>Costing Type</label>
                        <select name="type" id="type" class="form-control select2bs4" required>
                            <option value="single">SINGLE</option>
                            <option value="multiple">MULTIPLE</option>
                        </select>
                    </div>
                    <div class="col-md-3 form-group">
                        <label>Product Type (Set)</label>
                        <select id="product_set" name="product_set[]" class="form-control select2bs4" multiple disabled>
                            @foreach ($master_set as $m_set)
                                <option value="{{ $m_set->id }}">{{ $m_set->nama ?? $m_set->id }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-3 form-group">
                        <label>Tipe WS</label>
                        <select name="tipe_ws" id="tipe_ws" class="form-control select2bs4" required>
                            <option value="">Pilih Tipe WS</option>
                            <option value="GLOBAL">GLOBAL</option>
                            <option value="STD">STANDAR</option>
                            {{-- <option value="DTH">DTH</option> --}}
                        </select>
                    </div>
                    <div class="col-md-3 form-group">
                        <label>Unit</label>
                        <select name="unit" id="unit" class="form-control select2bs4" required>
                            <option value="">Pilih Satuan</option>
                            @foreach ($units as $u)
                                <option value="{{ $u->nama_pilihan }}" {{ $u->nama_pilihan == 'PCS' ? 'selected' : '' }}>{{ $u->nama_pilihan }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            {{-- ============ SECTION: PENGIRIMAN & TUJUAN ============ --}}
            <div class="form-section">
                <div class="form-section-title"><i class="fas fa-ship"></i> Pengiriman & Tujuan</div>
                <div class="row">
                    <div class="col-md-3 form-group">
                        <label>Ship Mode</label>
                        <select name="ship_mode" id="ship_mode" class="form-control select2bs4" required>
                            <option value="">Pilih Ship Mode</option>
                            @foreach ($shipmodes as $sm)
                                <option value="{{ $sm->id }}">{{ $sm->shipmode }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3 form-group">
                        <label>Shipment Type</label>
                        <select name="shipment_type" id="shipment_type" class="form-control select2bs4" required>
                            <option value="local">LOCAL</option>
                            <option value="export">EXPORT</option>
                        </select>
                    </div>
                    <div class="col-md-3 form-group">
                        <label>Delivery Date *</label>
                        <input type="date" name="deldate" id="deldate" class="form-control" required>
                    </div>
                    <div class="col-md-3 form-group">
                        <label>Status *</label>
                        <select name="status" id="status" class="form-control select2bs4" required>
                            <option value="">Pilih Status</option>
                            <option value="CONFIRM">CONFIRM</option>
                            <option value="CANCEL">CANCEL</option>
                            <option value="SAMPLE">SAMPLE</option>
                        </select>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-3 form-group">
                        <label>Marketing Order</label>
                        <select name="marketing_order" id="marketing_order" class="form-control select2bs4" required>
                            <option value="">Pilih Marketing Order</option>
                            @foreach ($marketing_orders as $mo)
                                <option value="{{ $mo->id }}">{{ $mo->mkt_order }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3 form-group">
                        <label>Main Destination #</label>
                        <select name="main_dest[]" id="main_dest" class="form-control select2bs4" multiple>
                            @foreach ($destinations as $dest)
                                <option value="{{ $dest->country_name }}">{{ $dest->country_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3 form-group">
                        <label>Market</label>
                        <input type="text" name="market" id="market" class="form-control" placeholder="Market">
                    </div>
                    <div class="col-md-3 form-group">
                        <label for="notes">Notes</label>
                        <textarea id="notes" name="notes" rows="1" class="form-control" placeholder="Tambahkan catatan jika ada..."></textarea>
                    </div>
                </div>
            </div>

            {{-- ============ SECTION: HARGA & KURS ============ --}}
            <div class="form-section">
                <div class="form-section-title"><i class="fas fa-money-bill-wave"></i> Harga & Kurs</div>
                <div class="row">
                    <div class="col-md-3 form-group">
                        <label>Curr</label>
                        <select name="curr" id="curr" class="form-control select2bs4" required>
                            <option value="">Pilih Currency</option>
                            @foreach ($currencies as $cur)
                                <option value="{{ $cur->id }}">{{ $cur->nama_pilihan }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3 form-group">
                        <label>Confirm Price</label>
                        <input type="text" name="confirm_price" id="confirm_price" class="form-control input-decimal text-right" placeholder="0.00">
                    </div>
                </div>
            </div>

            {{-- ============ SECTION: QTY / SMV / VAT / RATE (angka penting, di-highlight) ============ --}}
            <div class="form-section form-section-figures">
                <div class="form-section-title"><i class="fas fa-calculator"></i> Qty, SMV & Rate</div>
                <div class="row">
                    <div class="col-md-2 form-group">
                        <label>QTY (PCS)</label>
                        <input type="text" name="qty" class="form-control input-decimal text-right" required>
                    </div>
                    <div class="col-md-2 form-group">
                        <label>SMV (Min)</label>
                        <input type="text" name="smv" id="smv" class="form-control input-decimal text-right" required>
                    </div>
                    <div class="col-md-2 form-group">
                        <label>VAT (%)</label>
                        <input type="text" name="vat" id="vat" class="form-control input-decimal text-right" required>
                    </div>
                    <div class="col-md-3 form-group">
                        <label>Rate to IDR</label>
                        <input type="text" name="rate_to_idr" class="form-control input-decimal text-right" required>
                    </div>
                    <div class="col-md-3 form-group">
                        <label>Rate from IDR</label>
                        <input type="text" name="rate_from_idr" class="form-control input-decimal text-right" required>
                    </div>
                </div>
            </div>

            {{-- ============ SECTION: UPLOAD GAMBAR ============ --}}
            <div class="form-section">
                <div class="form-section-title"><i class="fas fa-image"></i> Upload Gambar Costing</div>
                <div class="row align-items-center">
                    <div class="col-md-5 form-group mb-md-0">
                        <label>Pilih File Gambar</label>
                        <input type="file" name="upload_foto" id="upload_foto" class="form-control p-1" accept="image/*">
                    </div>

                    <div class="col-md-7 text-center">
                        <div class="upload-preview-box">
                            @if(isset($costing->upload_foto) && $costing->upload_foto != '')
                                <img id="img_preview" src="{{ asset('uploads/costing/' . $costing->upload_foto) }}" alt="Preview Gambar" class="img-thumbnail shadow-sm" style="max-height: 150px; object-fit: contain;">
                            @else
                                <img id="img_preview" src="" alt="Preview Gambar" class="img-thumbnail shadow-sm" style="max-height: 150px; object-fit: contain; display: none;">
                                <span id="no_img_text" class="text-muted font-italic">Belum ada gambar terpilih</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <div class="text-right mb-2">
                <a href="{{ route('master-costing') }}" class="btn btn-default btn-sm shadow-sm px-4 mr-2">
                    <i class="fas fa-arrow-left"></i> Kembali
                </a>
                <button type="submit" class="btn btn-success btn-sm shadow-sm px-4">
                    <i class="fas fa-save"></i> Simpan
                </button>
            </div>

        </div>
    </form>
</div>
@endsection

@section('custom-script')
<script src="{{ asset('plugins/select2/js/select2.full.min.js') }}"></script>
<script>
    $(document).ready(function() {
        $('.select2bs4').select2({ theme: 'bootstrap4' });

        $(document).on('input', '.input-decimal', function() {
            let val = $(this).val().replace(/[^0-9.]/g, '');
            let parts = val.split('.');
            if (parts.length > 2) val = parts[0] + '.' + parts.slice(1).join('');
            $(this).val(val);
        });

        $(document).on('select2:open', function(e) {
            setTimeout(function() {
                document.querySelector('.select2-container--open .select2-search__field').focus();
            }, 50);
        });

        function checkShipmentType() {
            let shipmentType = $('#shipment_type').val();

            $('#vat').val('0.00');

            if (shipmentType === 'local') {
                $('#vat').val('11.00');
            }
        }

        $('#shipment_type').on('change', function() {
            checkShipmentType();
        });

        $('#main_dest').on('change', function() {
            let curr_val = $(this).val();
            let curr_count = curr_val ? curr_val.length : 0;

            if (curr_count > 1) {
                Swal.fire({
                    title: 'Perhatian!',
                    text: 'Anda memilih lebih dari 1 Destination. Ingat untuk menggabungkan WS jika diperlukan!',
                    icon: 'info',
                    confirmButtonColor: '#3085d6',
                    confirmButtonText: 'Ya, Mengerti'
                });
            }
        });

        $('#product_group').on('change', function() {
            let selected_grup = $(this).val();
            let select_item = $('#product_item');
            if(selected_grup) {
                $.post("{{ route('get-product-items') }}", { _token: "{{ csrf_token() }}", product_group: selected_grup }, function(res) {
                    select_item.empty().append('<option value="">Pilih Product Item</option>');
                    $.each(res, function(key, value) { select_item.append(`<option value="${value.id}">${value.product_item}</option>`); });
                    select_item.trigger('change');
                });
            } else {
                select_item.empty().append('<option value="">Pilih Product Item</option>').trigger('change');
            }
        });

        $('#form-costing').on('submit', function(e) {
            e.preventDefault();
            let form = this;

            Swal.fire({
                title: 'Simpan Data Costing?',
                text: "Pastikan semua data Header sudah terisi dengan benar!",
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#28a745',
                cancelButtonColor: '#d33',
                confirmButtonText: '<i class="fas fa-save"></i> Ya, Simpan!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {

                    Swal.fire({
                        title: 'Menyimpan...',
                        text: 'Mohon tunggu sebentar',
                        allowOutsideClick: false,
                        showConfirmButton: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });


                    form.submit();
                }
            });
        });

        $('#upload_foto').on('change', function(event) {
            let file = event.target.files[0];

            if (file) {
                let reader = new FileReader();
                reader.onload = function(e) {
                    $('#img_preview').attr('src', e.target.result).show();
                    $('#no_img_text').hide();
                }
                reader.readAsDataURL(file);
            } else {
                let isDbImageExist = "{{ isset($costing->upload_foto) && $costing->upload_foto != '' ? 'yes' : 'no' }}";

                if(isDbImageExist === 'no') {
                    $('#img_preview').hide();
                    $('#img_preview').attr('src', '');
                    $('#no_img_text').show();
                } else {
                    $('#img_preview').attr('src', "{{ isset($costing->upload_foto) ? asset('uploads/costing/' . $costing->upload_foto) : '' }}").show();
                }
            }
        });
    });

    function toggleSetHeader() {
        let type = $('#type').val();
        if (type === 'single') {
            $('#product_set').val('').trigger('change').prop('disabled', true);
        } else {
            $('#product_set').prop('disabled', false);
        }
    }

    toggleSetHeader();

    $('#type').on('change', function() {
        toggleSetHeader();
    });
</script>
@endsection
