@extends('layouts.index')

@section('custom-link')
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap">
    <link rel="stylesheet" href="{{ asset('plugins/select2/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}">

    <style>
        :root { --costing-navy: #183f5a; --costing-border: #dce4ec; }
        .costing-create-page { padding: 8px 12px 24px; background: #f5f3f3; min-height: calc(100vh - 56px); font-family: 'Poppins', sans-serif; }
        .costing-crumb { background: var(--costing-navy); color: #b4b4b4; padding: 8px 10px; margin-bottom: 14px; font-size: 16px; font-weight: 500; }
        .costing-crumb a { color: #b4b4b4; text-decoration: none; }
        .costing-crumb a:hover { color: #fff; text-decoration: underline; }
        .costing-crumb b { color: #fff; font-weight: 500; }
        .costing-back { display: inline-flex; align-items: center; gap: 8px; margin: 10px 0; color: var(--costing-navy); font-size: 14px; font-weight: 700; }

        .costing-create-card { border: 0; border-radius: 4px; box-shadow: 0 5px 12px -3px rgba(30, 41, 59, .18), 0 2px 4px rgba(30, 41, 59, .07); background: #fff;}
        .costing-create-card > .card-body { padding: 20px 24px 32px; }

        .costing-page-title { color: #284a63; font-size: 32px; font-weight: 700; margin: 0 0 4px; }
        .costing-page-subtitle { color: #6c757d; font-size: 11px; margin: 0 0 20px; font-weight: 500; }

        .costing-section { margin-bottom: 16px; overflow: hidden; border: 1px solid #284a63; border-radius: 8px; background: #fff; box-shadow: 0 4px 8px -2px rgba(24, 63, 90, .16), 0 1px 2px rgba(24, 63, 90, .06); }
        .costing-section-header { margin: 0; }
        .costing-section-toggle {
            display: flex; justify-content: space-between; align-items: center;
            width: 100%; box-sizing: border-box; padding: 10px 15px;
            border: 0; border-radius: 0;
            background: #f8fafc; color: #284a63;
            text-align: left; font-family: 'Poppins', sans-serif; font-size: 25px; font-weight: 500; appearance: none;
        }
        .costing-section-toggle:focus { outline: 0; box-shadow: 0 0 0 2px rgba(48, 133, 214, .18); }
        .costing-section-toggle .required-mark { color: #dc3545; margin-left: 2px;}

        .section-chevron-box {
            background-color: #a3b2c7; color: white; border-radius: 4px;
            width: 22px; height: 22px; display: flex; align-items: center; justify-content: center; font-size: 11px;
        }
        .section-chevron-box i { transition: transform .2s ease; }
        .costing-section-toggle[aria-expanded="false"] .section-chevron-box i { transform: rotate(180deg); }

        .costing-section-body { padding: 14px 15px 14px; border-top: 1px solid #edf1f5; }

        .costing-fields { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); column-gap: 15px; row-gap: 10px; }
        .costing-fields.five-columns { grid-template-columns: repeat(5, minmax(0, 1fr)); }

        .costing-field { min-width: 0; margin-bottom: 10px; }
        .costing-field label { display: flex; align-items: flex-start; min-height: 27px; margin-bottom: 6px; color: #ababab; font-size: 18px; }
        .costing-create-page .costing-field label:not(.form-check-label):not(.custom-file-label) { font-weight: 400; }
        .costing-field .form-control,
        .costing-field .select2-container--bootstrap4 .select2-selection { height: 44px; min-height: 44px; border: 1px solid #e1e5e9; border-radius: 5px; font-size: 12px; color: #495057; font-weight: 600; }
        .costing-field .form-control,
        .costing-field .select2-container--bootstrap4 .select2-selection { box-shadow: 0 2px 4px rgba(30, 41, 59, .10); }
        .costing-field input.form-control::placeholder { color: #adb5bd; font-weight: 500; }
        .costing-field input.form-control { padding: 6px 12px; }

        .costing-field .select2-container { width: 100% !important; }
        .costing-field .select2-container--bootstrap4 .select2-selection--single { padding: 6px 12px; }
        .costing-field .select2-container--bootstrap4 .select2-selection--multiple .select2-selection__rendered { padding: 0 12px; }
        .costing-field .select2-container--bootstrap4 .select2-selection__rendered { padding-left: 0; line-height: 30px; color: #495057; font-weight: 600; }
        .costing-field .select2-container--bootstrap4 .select2-selection--single .select2-selection__arrow { top: 4px; right: 5px; }

        .costing-field .form-control:focus { border-color: #8aa8bc; box-shadow: 0 0 0 2px rgba(39, 75, 99, .08); }
        .costing-field .form-control[readonly],
        .costing-field .form-control:disabled { background: #e9ecef; color: #6c757d; border-color: #dee2e6; }

        /* Gambar Upload Section */
        .upload-file-box { border: 1px solid var(--costing-border); padding: 15px; border-radius: 5px; background: #fff; }
        .costing-picture-dropzone {
            display: flex; flex-direction: column; align-items: center; justify-content: center;
            border: 1.5px dashed var(--costing-navy); border-radius: 5px; background: #fff;
            cursor: pointer; text-align: center; padding: 25px 10px; min-height: 120px;
        }
        .costing-picture-dropzone.dragging { background: #eff7ff; }
        .costing-picture-thumbnails {
            border: 1px dashed #6f42c1; border-radius: 5px; padding: 10px;
            min-height: 70px; display: flex; flex-wrap: wrap; gap: 6px;
        }
        .costing-picture-clear { margin-top: 8px; }

        .costing-picture-carousel { position: relative; display: flex; flex-direction: column; align-items: center; justify-content: center; width: 100%; height: 100%; min-height: 250px;}
        .costing-picture-main { max-height: 250px; max-width: 100%; object-fit: contain; }
        .costing-picture-nav {
            width: 30px; height: 30px; padding: 0; border: 0; border-radius: 50%;
            background: #f1f3f5; color: #53687a; box-shadow: 0 2px 4px rgba(0,0,0,0.1); transition: background 0.2s;
        }
        .costing-picture-nav:hover { background: #e2e6ea; }

        .costing-form-actions { padding-top: 20px; text-align: right; }
        .btn-success-custom { background-color: #198754; border-color: #198754; border-radius: 20px; padding: 6px 30px; font-weight: 600; color: #fff;}
        .btn-success-custom:hover { background-color: #157347; }

        @media (max-width: 991px) {
            .costing-fields, .costing-fields.five-columns { grid-template-columns: repeat(3, minmax(0, 1fr)); }
        }
        @media (max-width: 767px) {
            .costing-fields, .costing-fields.five-columns { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        }
        @media (max-width: 480px) {
            .costing-fields, .costing-fields.five-columns { grid-template-columns: 1fr; }
        }

        .select2-container .select2-selection--single .select2-selection__arrow {
            top: 50% !important;
            transform: translateY(-50%) !important;
            height: auto !important;
        }
    </style>
@endsection

@section('content')
<div class="costing-create-page">
    {{-- <a href="{{ route('master-costing') }}" class="costing-back"><i class="fas fa-chevron-left" style="background:#a3b2c7; color:white; border-radius:4px; padding:3px 5px; font-size:10px;"></i> Back</a> --}}

    <div class="card costing-create-card">
        <form action="{{ route('store-costing') }}" method="POST" id="form-costing" enctype="multipart/form-data">
            @csrf
            <div class="card-body">
                <div class="costing-crumb"><a href="{{ route('master-costing') }}">Marketing &nbsp;›&nbsp; Proses &nbsp;›&nbsp; Costing</a> &nbsp;›&nbsp; <b>Create Costing</b></div>
                <h1 class="costing-page-title">Create Costing <i class="fas fa-info-circle text-muted" style="font-size: 14px;"></i></h1>
                <p class="costing-page-subtitle"></p>

                @if ($errors->any())
                    <div class="alert alert-danger py-2" role="alert">
                        <ul class="mb-0 pl-3">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="costing-section">
                    <div class="costing-section-header">
                        <button type="button" class="costing-section-toggle" data-section-target="#section-buyer" aria-expanded="true">
                            <span>Information Buyer<span class="required-mark">*</span></span>
                            <span class="section-chevron-box"><i class="fas fa-chevron-down"></i></span>
                        </button>
                    </div>
                    <div id="section-buyer" class="collapse show">
                        <div class="costing-section-body">
                            <div class="costing-fields five-columns">
                                <div class="costing-field">
                                    <label>Buyer Name</label>
                                    <select name="buyer" id="buyer" class="form-control select2bs4" required>
                                        <option value="">Choose Buyer Name</option>
                                        @foreach ($buyers as $b)
                                            <option value="{{ $b->id_supplier ?? $b->Id_Supplier }}">{{ $b->supplier ?? $b->Supplier }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="costing-field">
                                    <label>Kode Buyer</label>
                                    <input type="text" id="buyer_code" class="form-control" placeholder="Buyer" readonly>
                                </div>
                                <div class="costing-field">
                                    <label>Brand</label>
                                    <select name="brand" class="form-control select2bs4" required>
                                        <option value="">Choose Brand</option>
                                        <option value="Sample Brand">Sample Brand</option>
                                    </select>
                                </div>
                                <div class="costing-field">
                                    <label>Season</label>
                                    <select name="season_id" id="season_id" class="form-control select2bs4" required>
                                        <option value="">Choose Seasons</option>
                                        @foreach ($seasons as $s)
                                            <option value="{{ $s->id_season }}">{{ $s->season }} - {{ $s->season_desc }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="costing-field">
                                    <label>Style</label>
                                    <input type="text" name="style" class="form-control" placeholder="Style" required>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="costing-section">
                    <div class="costing-section-header">
                        <button type="button" class="costing-section-toggle" data-section-target="#section-product" aria-expanded="true">
                            <span>Product & Type<span class="required-mark">*</span></span>
                            <span class="section-chevron-box"><i class="fas fa-chevron-down"></i></span>
                        </button>
                    </div>
                    <div id="section-product" class="collapse show">
                        <div class="costing-section-body">
                            <div class="costing-fields">
                                <div class="costing-field">
                                    <label>Product Group</label>
                                    <select name="product_group" id="product_group" class="form-control select2bs4" required>
                                        <option value="">Choose Product Group</option>
                                        @foreach ($product_groups as $pg)
                                            <option value="{{ $pg->product_group }}">{{ $pg->product_group }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="costing-field">
                                    <label>Product Item</label>
                                    <select name="product_item" id="product_item" class="form-control select2bs4" required>
                                        <option value="">Choose Product Item</option>
                                    </select>
                                </div>
                                <div class="costing-field">
                                    <label>Costing Type</label>
                                    <select name="type" id="type" class="form-control select2bs4" required>
                                        <option value="">Choose Costing Type</option>
                                        <option value="single">SINGLE</option>
                                        <option value="multiple">MULTIPLE</option>
                                    </select>
                                </div>
                                <div class="costing-field">
                                    <label>Product Type</label>
                                    <select id="product_set" name="product_set[]" class="form-control select2bs4" multiple disabled data-placeholder="Product Type">
                                        @foreach ($master_set as $m_set)
                                            <option value="{{ $m_set->id }}">{{ $m_set->nama ?? $m_set->id }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="costing-field">
                                    <label>WS Type</label>
                                    <select name="tipe_ws" id="tipe_ws" class="form-control select2bs4" required>
                                        <option value="">Choose WS Type</option>
                                        <option value="GLOBAL">GLOBAL</option>
                                        <option value="STD">STANDAR</option>
                                    </select>
                                </div>
                                <div class="costing-field">
                                    <label>Unit</label>
                                    <select name="unit" id="unit" class="form-control select2bs4" required>
                                        <option value="">Choose Unit</option>
                                        @foreach ($units as $u)
                                            <option value="{{ $u->nama_pilihan }}" {{ $u->nama_pilihan == 'PCS' ? 'selected' : '' }}>{{ $u->nama_pilihan }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="costing-section">
                    <div class="costing-section-header">
                        <button type="button" class="costing-section-toggle" data-section-target="#section-shipping" aria-expanded="true">
                            <span>Shipping & Destinations<span class="required-mark">*</span></span>
                            <span class="section-chevron-box"><i class="fas fa-chevron-down"></i></span>
                        </button>
                    </div>
                    <div id="section-shipping" class="collapse show">
                        <div class="costing-section-body">
                            <div class="costing-fields">
                                <div class="costing-field">
                                    <label>Ship Mode</label>
                                    <select name="ship_mode" id="ship_mode" class="form-control select2bs4" required>
                                        <option value="">Choose Ship Mode</option>
                                        @foreach ($shipmodes as $sm)
                                            <option value="{{ $sm->id }}">{{ $sm->shipmode }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="costing-field">
                                    <label>Shipment Type</label>
                                    <select name="shipment_type" id="shipment_type" class="form-control select2bs4" required>
                                        <option value="">Choose Shipment Type</option>
                                        <option value="local">LOCAL</option>
                                        <option value="export">EXPORT</option>
                                    </select>
                                </div>
                                <div class="costing-field">
                                    <label>Delivery Date</label>
                                    <input type="date" name="deldate" id="deldate" class="form-control" required>
                                </div>
                                <div class="costing-field">
                                    <label>Status</label>
                                    <select name="status" id="status" class="form-control select2bs4" required>
                                        <option value="">Choose Status</option>
                                        <option value="CONFIRM">CONFIRM</option>
                                        <option value="CANCEL">CANCEL</option>
                                        <option value="SAMPLE">SAMPLE</option>
                                    </select>
                                </div>
                                <div class="costing-field">
                                    <label>Marketing Order</label>
                                    <select name="marketing_order" id="marketing_order" class="form-control select2bs4" required>
                                        <option value="">Choose Marketing Order</option>
                                        @foreach ($marketing_orders as $mo)
                                            <option value="{{ $mo->id }}">{{ $mo->mkt_order }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="costing-field">
                                    <label>Main Destination</label>
                                    <select name="main_dest[]" id="main_dest" class="form-control select2bs4" data-placeholder="Choose Destination" multiple>
                                        @foreach ($destinations as $dest)
                                            <option value="{{ $dest->country_name }}">{{ $dest->country_name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="costing-field">
                                    <label>Market</label>
                                    <input type="text" name="market" id="market" class="form-control" placeholder="Market">
                                </div>
                                <div class="costing-field">
                                    <label>Notes</label>
                                    <textarea id="notes" name="notes" rows="1" class="form-control" placeholder="Notes"></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="costing-section">
                    <div class="costing-section-header">
                        <button type="button" class="costing-section-toggle" data-section-target="#section-price" aria-expanded="true">
                            <span>Price & Currency<span class="required-mark">*</span></span>
                            <span class="section-chevron-box"><i class="fas fa-chevron-down"></i></span>
                        </button>
                    </div>
                    <div id="section-price" class="collapse show">
                        <div class="costing-section-body">
                            <div class="costing-fields">
                                <div class="costing-field">
                                    <label>Currency</label>
                                    <select name="curr" id="curr" class="form-control select2bs4" required>
                                        <option value="">Choose Currency</option>
                                        @foreach ($currencies as $cur)
                                            <option value="{{ $cur->id }}">{{ $cur->nama_pilihan }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="costing-field">
                                    <label>Confirm Price</label>
                                    <input type="text" name="confirm_price" id="confirm_price" class="form-control input-decimal" placeholder="Confirm Price">
                                </div>
                                <div class="costing-field">
                                    <label>Rate to IDR</label>
                                    <input type="text" name="rate_to_idr" class="form-control input-decimal" placeholder="Rate to IDR" required>
                                </div>
                                <div class="costing-field">
                                    <label>Rate from IDR</label>
                                    <input type="text" name="rate_from_idr" class="form-control input-decimal" placeholder="Rate from IDR" required>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="costing-section">
                    <div class="costing-section-header">
                        <button type="button" class="costing-section-toggle" data-section-target="#section-parameters" aria-expanded="true">
                            <span>Parameter Costing<span class="required-mark">*</span></span>
                            <span class="section-chevron-box"><i class="fas fa-chevron-down"></i></span>
                        </button>
                    </div>
                    <div id="section-parameters" class="collapse show">
                        <div class="costing-section-body">
                            <div class="costing-fields">
                                <div class="costing-field">
                                    <label>QTY Planned (PCS)</label>
                                    <input type="text" name="qty_planned" id="qty_planned" class="form-control input-decimal" placeholder="QTY Planned (PCS)" required>
                                </div>
                                <div class="costing-field">
                                    <label>QTY (PCS)</label>
                                    <input type="text" name="qty" class="form-control input-decimal" placeholder="QTY (PCS)" required>
                                </div>
                                <div class="costing-field">
                                    <label>SMV (Min)</label>
                                    <input type="text" name="smv" id="smv" class="form-control input-decimal" placeholder="SMV (Min)" required>
                                </div>
                                <div class="costing-field">
                                    <label>VAT (%)</label>
                                    <input type="text" name="vat" id="vat" class="form-control input-decimal" placeholder="VAT (%)" required>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="costing-section">
                    <div class="costing-section-header">
                        <button type="button" class="costing-section-toggle" data-section-target="#section-image" aria-expanded="true">
                            <span>Upload Image Costing<span class="required-mark">*</span></span>
                            <span class="section-chevron-box"><i class="fas fa-chevron-down"></i></span>
                        </button>
                    </div>
                    <div id="section-image" class="collapse show">
                        <div class="costing-section-body pt-3">
                            <div class="row">
                                <div class="col-md-4">
                                    <input type="file" name="upload_foto[]" id="upload_foto" accept="image/*" multiple hidden>

                                    <div class="upload-file-box mb-3">
                                        <h6 style="font-size: 11px; font-weight: 700; color: var(--costing-navy); margin-bottom: 15px;">Upload File</h6>
                                        <div id="picture-dropzone" class="costing-picture-dropzone" role="button" tabindex="0">
                                            <i class="fas fa-file-alt" style="font-size: 30px; color: var(--costing-navy); margin-bottom: 12px;"></i>
                                            <div style="font-size: 11px; font-weight: 600; color: var(--costing-navy);">
                                                Drag n Drop here<br>Or<br><span style="color: #0056b3;">Browse</span>
                                            </div>
                                        </div>
                                        <button type="button" id="btn-upload-now" class="btn btn-block mt-3" style="background-color: #c4c4c4; font-weight: 700; font-size: 11px; color: #333;">Upload Now</button>
                                    </div>

                                    <div id="picture-thumbnails" class="costing-picture-thumbnails" aria-label="Selected image previews"></div>
                                    <button type="button" id="clear-pictures" class="btn btn-outline-danger btn-sm costing-picture-clear mb-4" disabled>
                                        <i class="fas fa-trash-alt"></i> Clear All
                                    </button>
                                </div>
                                <div class="col-md-8">
                                    <div class="costing-picture-carousel">
                                        <div class="d-flex align-items-center justify-content-center gap-4 w-100">
                                            <button type="button" id="picture-prev" class="costing-picture-nav" aria-label="Previous image"><i class="fas fa-chevron-left"></i></button>

                                            <div class="main-img-container" style="min-height: 250px; display:flex; align-items:center;">
                                                <img id="img_preview" class="costing-picture-main" alt="Costing image preview" hidden>
                                                <span id="no_img_text" class="text-muted" style="font-size: 11px;">Belum ada gambar yang dipilih</span>
                                            </div>

                                            <button type="button" id="picture-next" class="costing-picture-nav" aria-label="Next image"><i class="fas fa-chevron-right"></i></button>
                                        </div>
                                        <div id="carousel-dots" class="mt-4 d-flex justify-content-center" style="gap: 5px;"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="costing-form-actions">
                    <button type="submit" class="btn btn-success-custom shadow-sm">
                        Save
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@section('custom-script')
<script src="{{ asset('plugins/select2/js/select2.full.min.js') }}"></script>
<script>
    $(document).ready(function() {
        $('.select2bs4').select2({ theme: 'bootstrap4' });

        $('.costing-section-toggle').on('click', function() {
            const $toggle = $(this);
            const $section = $($toggle.attr('data-section-target'));
            const isExpanded = $toggle.attr('aria-expanded') === 'true';

            $section.collapse('toggle');
            $toggle.attr('aria-expanded', String(!isExpanded));
        });

        let costingImageUrls = [];
        let activeCostingImage = 0;

        function renderCostingImages() {
            const $mainImage = $('#img_preview');
            const $thumbnails = $('#picture-thumbnails').empty();
            const $dots = $('#carousel-dots').empty();
            const hasImages = costingImageUrls.length > 0;

            $('#no_img_text').prop('hidden', hasImages);
            $mainImage.prop('hidden', !hasImages);
            $('#picture-prev, #picture-next').prop('disabled', costingImageUrls.length < 2);
            $('#clear-pictures').prop('disabled', !hasImages);

            if (!hasImages) {
                $mainImage.attr('src', '');
                return;
            }

            activeCostingImage = (activeCostingImage + costingImageUrls.length) % costingImageUrls.length;
            $mainImage.attr('src', costingImageUrls[activeCostingImage].url);

            costingImageUrls.forEach((image, index) => {
                // Buat Thumbnail HTML
                let thumb = $('<div>', {
                    class: `costing-picture-thumb`,
                    style: `position: relative; width: 60px; height: 60px; cursor: pointer; border-radius: 4px; overflow: hidden; border: ${index === activeCostingImage ? '2px solid #3085d6' : '1px solid #dce4ec'};`
                });

                thumb.append($('<img>', { src: image.url, style: 'width: 100%; height: 100%; object-fit: cover; opacity: 0.8;' }));

                if (index === activeCostingImage) {
                    thumb.append($('<div>', {
                        style: 'position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); background: transparent; display: flex; align-items: center; justify-content: center;'
                    }).append('<i class="fas fa-check-circle text-white" style="font-size: 20px; drop-shadow: 0 1px 2px rgba(0,0,0,0.5);"></i>'));
                } else {
                    let closeBtn = $('<div>', {
                        style: 'position: absolute; top: 3px; right: 3px; background: rgba(0,0,0,0.7); color: white; border-radius: 50%; width: 16px; height: 16px; display: flex; align-items: center; justify-content: center; font-size: 9px;'
                    }).append('<i class="fas fa-times"></i>');

                    closeBtn.on('click', function(e) {
                        e.stopPropagation();
                        costingImageUrls.splice(index, 1);
                        if (activeCostingImage >= index && activeCostingImage > 0) activeCostingImage--;
                        if (costingImageUrls.length === 0) activeCostingImage = 0;
                        renderCostingImages();
                    });
                    thumb.append(closeBtn);
                }

                thumb.on('click', function() {
                    activeCostingImage = index;
                    renderCostingImages();
                });

                $thumbnails.append(thumb);

                // Buat dots untuk karosel
                $dots.append($('<span>', {
                    style: `display: block; width: 20px; height: 4px; border-radius: 2px; cursor: pointer; transition: background 0.3s; background: ${index === activeCostingImage ? '#0d6efd' : '#e0e0e0'};`
                }).on('click', function() {
                    activeCostingImage = index;
                    renderCostingImages();
                }));
            });
        }

        renderCostingImages();

        function setCostingFiles(files) {
            const selectedFiles = Array.from(files || []);
            const invalidFile = selectedFiles.find(file => !file.type.startsWith('image/'));
            if (invalidFile) {
                $('#upload_foto').val('');
                Swal.fire('File tidak valid', `${invalidFile.name} bukan file gambar.`, 'error');
                return;
            }

            const allFiles = costingImageUrls.map(image => image.file).concat(selectedFiles);
            const uniqueFiles = allFiles.filter((file, index) => allFiles.findIndex(candidate =>
                candidate.name === file.name && candidate.size === file.size && candidate.lastModified === file.lastModified
            ) === index);
            const transfer = new DataTransfer();
            uniqueFiles.forEach(file => transfer.items.add(file));
            $('#upload_foto')[0].files = transfer.files;
            costingImageUrls.forEach(image => URL.revokeObjectURL(image.url));
            costingImageUrls = uniqueFiles.map(file => ({ url: URL.createObjectURL(file), name: file.name, file }));
            activeCostingImage = selectedFiles.length ? Math.max(0, uniqueFiles.length - selectedFiles.length) : 0;
            renderCostingImages();
        }

        $('#btn-upload-now, #picture-dropzone').on('click', function() {
            $('#upload_foto').trigger('click');
        });

        $('#picture-dropzone').on('keydown', function(event) {
            if (event.key === 'Enter' || event.key === ' ') {
                event.preventDefault();
                $('#upload_foto').trigger('click');
            }
        }).on('dragover', function(event) {
            event.preventDefault();
            $(this).addClass('dragging');
        }).on('dragleave drop', function(event) {
            event.preventDefault();
            $(this).removeClass('dragging');
        }).on('drop', function(event) {
            setCostingFiles(event.originalEvent.dataTransfer.files);
        });

        $('#upload_foto').on('change', function() {
            setCostingFiles(this.files);
        });

        $('#clear-pictures').on('click', function() {
            costingImageUrls.forEach(image => URL.revokeObjectURL(image.url));
            costingImageUrls = [];
            activeCostingImage = 0;
            $('#upload_foto').val('');
            renderCostingImages();
        });

        $('#picture-prev').on('click', function() {
            activeCostingImage--;
            renderCostingImages();
        });
        $('#picture-next').on('click', function() {
            activeCostingImage++;
            renderCostingImages();
        });

        $('#buyer').on('change', function() {
            $('#buyer_code').val($(this).val() || '');
        }).trigger('change');

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
        checkShipmentType();

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
                confirmButtonColor: '#198754',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Ya, Simpan!',
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
