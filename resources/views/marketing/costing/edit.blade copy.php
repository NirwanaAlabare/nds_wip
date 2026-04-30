@extends('layouts.index')

@section('custom-link')
    <link rel="stylesheet" href="{{ asset('plugins/select2/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}">
@endsection

@section('content')
<div class="card card-sb">
    <div class="card-header bg-sb">
        <div class="d-flex justify-content-between align-items-center">
            <h5 class="card-title fw-bold">Costing {{ $costing->no_costing }}</h5>
            <a href="{{ route('master-costing') }}" class="btn btn-sm btn-primary">
                <i class="fa fa-reply"></i> Kembali ke List
            </a>
        </div>
    </div>

    <div class="card-body">

        <form action="{{ route('update-costing-header', $costing->id) }}" method="POST" id="form-header" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="row">
                <div class="col-md-3 form-group">
                    <label>Buyer Name</label>
                    <select name="buyer" id="buyer" class="form-control select2bs4" required>
                        <option value="">Pilih Buyer Name</option>
                        @foreach ($buyers as $b)
                            @php $sup_id = $b->id_supplier ?? $b->Id_Supplier; @endphp
                            <option value="{{ $sup_id }}" {{ $costing->buyer == $sup_id ? 'selected' : '' }}>
                                {{ $b->supplier ?? $b->Supplier }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 form-group">
                    <label>Brand</label>
                    <input type="text" name="brand" class="form-control" value="{{ $costing->brand }}" required>
                </div>
                <div class="col-md-3 form-group">
                    <label>Product Group</label>
                    <select name="product_group" id="product_group" class="form-control select2bs4" required>
                        <option value="">Pilih Product Group</option>
                        @foreach ($product_groups as $pg)
                            <option value="{{ $pg->product_group }}" {{ $costing->product_group == $pg->product_group ? 'selected' : '' }}>
                                {{ $pg->product_group }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 form-group">
                    <label>Product Item</label>
                    <select name="product_item" id="product_item" class="form-control select2bs4" required>
                        <option value="{{ $costing->product_item }}" selected>{{ $costing->product_item }}</option>
                    </select>
                </div>
            </div>

            <div class="row">
                <div class="col-md-3 form-group">
                    <label>Style</label>
                    <input type="text" name="style" class="form-control" value="{{ $costing->style }}" required>
                </div>
                <div class="col-md-3 form-group">
                    <label>Ship Mode</label>
                    <select name="ship_mode" id="ship_mode" class="form-control select2bs4" required>
                        <option value="">Pilih Ship Mode</option>
                        @foreach ($shipmodes as $sm)
                            <option value="{{ $sm->id }}" {{ $costing->ship_mode == $sm->id ? 'selected' : '' }}>
                                {{ $sm->shipmode }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 form-group">
                    <label>Type</label>
                     <select name="type" id="type" class="form-control select2bs4" required>
                        <option value="single" {{ $costing->type == 'single' ? 'selected' : '' }}>SINGLE</option>
                        <option value="multiple" {{ $costing->type == 'multiple' ? 'selected' : '' }}>MULTIPLE</option>
                    </select>
                </div>
                <div class="col-md-3 form-group">
                    <label>Curr</label>
                    <select name="curr" id="curr" class="form-control select2bs4" required>
                        <option value="">Pilih Currency</option>
                        @foreach ($currencies as $cur)
                            <option value="{{ $cur->id }}" {{ $costing->curr == $cur->id ? 'selected' : '' }}>
                                {{ $cur->nama_pilihan }}
                            </option>
                        @endforeach
                    </select>
                </div>

            </div>

            <div class="row">

                <div class="col-md-3 form-group">
                    <label>Marketing Order</label>
                    <select name="marketing_order" id="marketing_order" class="form-control select2bs4" required>
                        <option value="">Pilih Marketing Order</option>
                        @foreach ($marketing_orders as $mo)
                            <option value="{{ $mo->id }}" {{ $costing->marketing_order == $mo->id ? 'selected' : '' }}>
                                {{ $mo->mkt_order }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 form-group">
                    <label>Shipment Type</label>
                    <select name="shipment_type" id="shipment_type" class="form-control select2bs4" required>
                        <option value="local" {{ $costing->shipment_type == 'local' ? 'selected' : '' }}>LOCAL</option>
                        <option value="export" {{ $costing->shipment_type == 'export' ? 'selected' : '' }}>EXPORT</option>
                    </select>
                </div>
                <div class="col-md-3 form-group">
                    <label for="notes">Notes</label>
                    <textarea id="notes" name="notes" rows="5" class="form-control">{{ $costing->notes }}</textarea>
                </div>


            </div>

            <div class="row bg-light pt-3 pb-1 mt-2 mb-3 rounded border mx-0">
                <div class="col-md-2 form-group">
                    <label>QTY (PCS)</label>
                    <input type="text" name="qty" id="qty" class="form-control input-decimal text-right" value="{{ number_format($costing->qty, 0, '', '') }}" required>
                </div>
                <div class="col-md-2 form-group">
                    <label>SMV (Min)</label>
                    <input type="text" name="smv" id="smv" class="form-control input-decimal text-right" value="{{ number_format($costing->smv, 2, '.', '') }}" required>
                </div>
                <div class="col-md-2 form-group">
                    <label>VAT (%)</label>
                    <input type="text" name="vat" id="vat" class="form-control input-decimal text-right" value="{{ number_format($costing->vat, 2, '.', '') }}" required readonly>
                </div>
                <div class="col-md-3 form-group">
                    <label>Rate to IDR</label>
                    <input type="text" name="rate_to_idr" id="rate_to_idr" class="form-control input-decimal text-right" value="{{ number_format($costing->rate_to_idr, 2, '.', '') }}" required>
                </div>
                <div class="col-md-3 form-group">
                    <label>Rate from IDR</label>
                    <input type="text" name="rate_from_idr" id="rate_from_idr" class="form-control input-decimal text-right" value="{{ number_format($costing->rate_from_idr, 2, '.', '') }}" required>
                </div>
            </div>

            <div class="row mb-3 bg-light pt-3 pb-2 rounded border mx-0">
                <div class="col-md-5 form-group">
                    <label class="fw-bold"><i class="fas fa-image"></i> Upload Gambar Costing</label>
                    <input type="file" name="upload_foto" id="upload_foto" class="form-control p-1" accept="image/*">
                </div>
                <div class="col-md-7 form-group text-center">
                    <label class="fw-bold d-block text-secondary">Preview Gambar</label>
                    @if(isset($costing->foto) && $costing->foto != '')
                        <img id="img_preview" src="/nds_wip/public/uploads/costing/{{ $costing->foto }}" alt="Preview Gambar" class="img-thumbnail shadow-sm" style="max-height: 180px; object-fit: contain;">
                    @else
                        <img id="img_preview" src="" alt="Preview Gambar" class="img-thumbnail shadow-sm" style="max-height: 180px; object-fit: contain; display: none;">
                        <span id="no_img_text" class="text-muted font-italic">Belum ada gambar terpilih</span>
                    @endif
                </div>
            </div>

            <div class="text-right mb-4">
                <button type="submit" class="btn btn-success btn-sm fw-bold shadow-sm px-4">
                    <i class="fas fa-save"></i> Update Header
                </button>
            </div>
        </form>

        <hr style="border-top: 2px dashed #ccc;">

        <div class="row mt-4 mb-2">
            <div class="col-md-3 form-group">
                <label class="fw-bold text-dark">Pilih Kategori :</label>
                <select id="category" class="form-control select2bs4 border-info">
                    <option value="" disabled selected>-- Pilih Kategori --</option>
                    <option value="Fabric">Fabric</option>
                    <option value="Accessories Sewing">Accessories Sewing</option>
                    <option value="Accessories Packing">Accessories Packing</option>
                    <option value="Manufacturing">Manufacturing</option>
                    <option value="Other Cost">Other Cost</option>
                </select>
            </div>
        </div>

        <h6 class="fw-bold mt-4 mb-2 text-sb"><i class="fas fa-keyboard"></i> INPUT COSTING TABLE</h6>
        <div class="table-responsive" style="overflow-x: auto; white-space: nowrap;">
            <table class="table table-bordered table-sm table-striped w-100" style="font-size: 13px; min-width: 1500px;">
                <thead class="bg-sb text-white text-center">
                    <tr>
                        <th style="min-width: 250px;">Item</th>
                        <th style="min-width: 120px;">Set</th>
                        <th style="min-width: 150px;">Desc</th>
                        <th style="min-width: 200px;">Supplier</th>
                        <th style="min-width: 80px;">Curr</th>
                        <th style="min-width: 100px;">Price</th>
                        <th style="min-width: 80px;">Cons/Pc</th>
                        <th style="min-width: 100px;">Unit</th>
                        <th style="min-width: 100px;">Price PX IDR</th>
                        <th style="min-width: 100px;">Price PX USD</th>
                        <th style="min-width: 80px;">Allow (%)</th>
                        <th style="min-width: 120px;">Value IDR</th>
                        <th style="min-width: 120px;">Value USD</th>
                    </tr>
                </thead>
                <tbody class="bg-light">
                    <tr>
                        <td style="padding: 4px;">
                            <select id="txt_item" class="form-control form-control-sm select2bs4" style="width: 100%;">
                                <option value="">-- Pilih Item --</option>
                            </select>
                        </td>
                        <td style="padding: 4px;">
                            <select id="txt_set" class="form-control form-control-sm select2bs4" style="width: 100%;">
                                <option value="">-- Set --</option>
                                @foreach ($set as $s)
                                    <option value="{{ $s->id ?? $s->nama }}">{{ $s->nama ?? $s->id }}</option>
                                @endforeach
                            </select>
                        </td>
                        <td style="padding: 4px;"><input type="text" id="txt_desc" class="form-control form-control-sm" placeholder="Desc..."></td>
                        <td style="padding: 4px;">
                            <select id="txt_supplier" class="form-control form-control-sm select2bs4" style="width: 100%;">
                                <option value="">-- Supplier --</option>
                                @foreach ($suppliers as $sup)
                                    <option value="{{ $sup->Id_Supplier ?? $sup->id_supplier }}">{{ $sup->Supplier ?? $sup->supplier }}</option>
                                @endforeach
                            </select>
                        </td>
                        <td style="padding: 4px;">
                            <select id="txt_curr" class="form-control form-control-sm calc-input select2bs4">
                                @foreach ($currencies as $cur)
                                    <option value="{{ $cur->nama_pilihan }}">{{ $cur->nama_pilihan }}</option>
                                @endforeach
                            </select>
                        </td>
                        <td style="padding: 4px;"><input type="text" id="txt_price" class="form-control form-control-sm text-right input-decimal calc-input" value="0"></td>
                        <td style="padding: 4px;"><input type="text" id="txt_cons" class="form-control form-control-sm text-center input-decimal calc-input" value="0"></td>
                        <td style="padding: 4px;">
                            <select id="txt_unit" class="form-control form-control-sm select2bs4" style="width: 100%;">
                                @foreach ($units as $u)
                                    <option value="{{ $u->nama_pilihan }}">{{ $u->nama_pilihan }}</option>
                                @endforeach
                            </select>
                        </td>
                        <td style="padding: 4px;"><input type="text" id="txt_px_idr" class="form-control form-control-sm text-right bg-white" readonly value="0"></td>
                        <td style="padding: 4px;"><input type="text" id="txt_px_usd" class="form-control form-control-sm text-right bg-white" readonly value="0"></td>
                        <td style="padding: 4px;"><input type="text" id="txt_allowance" class="form-control form-control-sm text-center input-decimal calc-input" value="0"></td>
                        <td style="padding: 4px;"><input type="text" id="txt_val_idr" class="form-control form-control-sm text-right font-weight-bold bg-white" readonly value="0"></td>
                        <td style="padding: 4px;"><input type="text" id="txt_val_usd" class="form-control form-control-sm text-right font-weight-bold bg-white" readonly value="0"></td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="text-right mb-4 mt-2">
            <button type="button" class="btn btn-success btn-sm shadow-sm font-weight-bold px-4 py-2" onclick="saveDetail()">
                <i class="fas fa-plus"></i> ADD
            </button>
        </div>

        <hr style="border-top: 2px solid #000;">

        <h5 class="fw-bold p-2 mb-3 mt-4 text-sb border-bottom">SUMMARY DETAILS</h5>

        @php
            $std_categories = [
                ['key' => 'Fabric', 'title' => 'FABRIC', 'tbody' => 'tbody-fabric', 'prefix' => 'fab'],
                ['key' => 'Accessories Sewing', 'title' => 'ACCESSORIES SEWING', 'tbody' => 'tbody-acc-sewing', 'prefix' => 'sew'],
                ['key' => 'Accessories Packing', 'title' => 'ACCESSORIES PACKING', 'tbody' => 'tbody-acc-packing', 'prefix' => 'pack'],
                ['key' => 'Manufacturing', 'title' => 'MANUFACTURING', 'tbody' => 'tbody-manufacturing', 'prefix' => 'mfg'],
            ];


            $order_qty = $costing->qty ?? 0;
        @endphp

        @foreach($std_categories as $cat)
            <h6 class="fw-bold bg-sb text-white p-2 mb-0" style="font-size: 13px;">{{ $cat['title'] }}</h6>
            <div class="table-responsive mb-3">
                <table class="table table-bordered table-sm text-nowrap w-100" style="font-size: 12px; min-width: 1400px;">
                    <thead class="bg-light text-center">
                        <tr>
                            <th width="3%">NO</th>
                            <th width="15%">ITEM</th>
                            <th width="7%">SET</th>
                            <th width="10%">DESC</th>
                            <th width="10%">SUPPLIER</th>
                            <th width="7%">PRICE PX IDR</th>
                            <th width="7%">PRICE PX USD</th>
                            <th width="5%">CONS/PC</th>
                            <th width="5%">UNIT</th>
                            <th width="5%">ALLOW (%)</th>
                            <th width="8%">VALUE IDR</th>
                            <th width="8%">VALUE USD</th>
                            <th width="5%">%</th>
                            <th width="6%">QTY BOM</th>
                            <th width="8%">VALUE</th>
                            <th width="8%">ACT</th>
                        </tr>
                    </thead>
                    <tbody id="{{ $cat['tbody'] }}">
                        @if(isset($details[$cat['key']]))
                            @foreach($details[$cat['key']] as $det)
                                @php

                                    $allow = $det->allowance > 0 ? $det->allowance : 0;
                                    $qty_bom = ceil((1 + ($allow / 100)) * $order_qty * $det->cons);
                                    $tot_val = $qty_bom * $det->price_px_idr;
                                @endphp
                                <tr class="row-costing" data-category="{{ $cat['key'] }}" data-item="{{ $det->item_id }}" data-set="{{ $det->set }}" id="row-{{ $det->id }}">
                                    <td class="text-center fw-bold row-number">{{ $loop->iteration }}</td>
                                    <td>{{ $det->nama_item }}</td>
                                    <td>{{ $det->nama_set }}</td>
                                    <td>{{ $det->item_desc }}</td>
                                    <td>{{ $det->nama_supplier }}</td>
                                    <td class="text-right px-idr-td" data-val="{{ $det->price_px_idr }}">{{ number_format($det->price_px_idr, 2, '.', ',') }}</td>
                                    <td class="text-right px-usd-td" data-val="{{ $det->price_px_usd }}">{{ number_format($det->price_px_usd, 4, '.', ',') }}</td>
                                    <td class="text-center cons-td" data-val="{{ $det->cons }}">{{ number_format($det->cons, 4, '.', '') }}</td>
                                    <td class="text-center">{{ $det->unit }}</td>
                                    <td class="text-center allow-td" data-val="{{ $det->allowance }}">{{ number_format($det->allowance, 2, '.', '') }}%</td>
                                    <td class="text-right val-idr-td fw-bold" data-val="{{ $det->value_idr }}">{{ number_format($det->value_idr, 2, '.', ',') }}</td>
                                    <td class="text-right val-usd-td fw-bold" data-val="{{ $det->value_usd }}">{{ number_format($det->value_usd, 4, '.', ',') }}</td>
                                    <td class="text-center pct-td fw-bold">0%</td>
                                    <td class="text-right qty-bom-td fw-bold" data-val="{{ $qty_bom }}">{{ number_format($qty_bom, 0, '.', ',') }}</td>
                                    <td class="text-right tot-val-td fw-bold" data-val="{{ $tot_val }}">{{ number_format($tot_val, 2, '.', ',') }}</td>
                                    <td class="text-center align-middle">
                                        <button type="button" class="btn btn-sm btn-primary py-0 px-2 mr-1" onclick="editRowModal(this, {{ $det->id }}, '{{ $cat['key'] }}')"><i class="fas fa-edit"></i></button>
                                        <button type="button" class="btn btn-sm btn-danger py-0 px-2" onclick="removeRow(this, {{ $det->id }})"><i class="fas fa-trash"></i></button>
                                    </td>
                                </tr>
                            @endforeach
                        @endif
                    </tbody>
                    <tfoot class="bg-warning fw-bold">
                        <tr>
                            <td colspan="10" class="text-left">TOTAL {{ $cat['title'] }} :</td>
                            <td class="text-right" id="tot-{{ $cat['prefix'] }}-idr">0.00</td>
                            <td class="text-right" id="tot-{{ $cat['prefix'] }}-usd">0.00</td>
                            <td class="text-center" id="tot-{{ $cat['prefix'] }}-pct">0%</td>
                            <td>Total Value</td>
                            <td class="text-right" id="tot-{{ $cat['prefix'] }}-val">0.00</td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        @endforeach

        <h6 class="fw-bold bg-sb text-white p-2 mb-0" style="font-size: 13px;">OTHER COST</h6>
        <div class="table-responsive mb-4">
            <table class="table table-bordered table-sm text-nowrap w-100" style="font-size: 12px; min-width: 1000px;">
                <thead class="bg-light text-center">
                    <tr>
                        <th width="5%">NO</th>
                        <th width="35%">ITEM</th>
                        <th width="10%">ALLOW (%)</th>
                        <th width="15%">VALUE IDR</th>
                        <th width="15%">VALUE USD</th>
                        <th width="10%">%</th>
                        <th width="10%">ACT</th>
                    </tr>
                </thead>
                <tbody id="tbody-other-cost">
                    @if(isset($details['Other Cost']))
                        @foreach($details['Other Cost'] as $det)
                            <tr class="row-costing" data-category="Other Cost" data-item="{{ $det->item_id }}" id="row-{{ $det->id }}">
                                <td class="text-center fw-bold row-number">{{ $loop->iteration }}</td>
                                <td class="item-name">{{ $det->nama_item }}</td>
                                <td class="text-center allow-td" data-val="{{ $det->allowance }}">{{ number_format($det->allowance, 2, '.', '') }}%</td>
                                <td class="text-right val-idr-td fw-bold" data-val="{{ $det->value_idr }}">{{ number_format($det->value_idr, 2, '.', ',') }}</td>
                                <td class="text-right val-usd-td fw-bold" data-val="{{ $det->value_usd }}">{{ number_format($det->value_usd, 4, '.', ',') }}</td>
                                <td class="text-center pct-td fw-bold">0%</td>
                                <td class="text-center align-middle">
                                    <button type="button" class="btn btn-sm btn-primary py-0 px-2 mr-1" onclick="editRowModal(this, {{ $det->id }}, 'Other Cost')"><i class="fas fa-edit"></i></button>
                                    <button type="button" class="btn btn-sm btn-danger py-0 px-2" onclick="removeRow(this, {{ $det->id }})"><i class="fas fa-trash"></i></button>
                                </td>
                            </tr>
                        @endforeach
                    @endif
                </tbody>
                <tfoot class="bg-warning fw-bold">
                    <tr>
                        <td colspan="3" class="text-left">TOTAL OTHER COST :</td>
                        <td class="text-right" id="tot-other-idr">0.00</td>
                        <td class="text-right" id="tot-other-usd">0.00</td>
                        <td class="text-center" id="tot-other-pct">0%</td>
                        <td></td>
                    </tr>
                    <tr>
                        <td colspan="2" class="text-left">G&A</td>
                        <td class="text-center">3%</td>
                        <td class="text-right" id="val-ga-idr">0.00</td>
                        <td class="text-right" id="val-ga-usd">0.00</td>
                        <td class="text-center pct-ga fw-bold">0%</td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <div class="row mt-4">
            <div class="col-md-6 offset-md-6">
                <table class="table table-bordered table-sm w-100" style="font-size: 14px;">
                    <tr style="border-top: 2px solid #000; font-weight: bold;">
                        <td width="50%">GRAND TOTAL VALUE</td>
                        <td class="text-center" id="grand_total_value">0.00</td>
                    </tr>
                    <tr style="border-top: 2px solid #000; font-weight: bold;">
                        <td width="50%">TOTAL TOP</td>
                        <td class="text-center" id="grand_total_top">0.00</td>
                    </tr>
                    <tr style="border-top: 2px solid #000; font-weight: bold;">
                        <td width="50%">TOTAL BOTTOM</td>
                        <td class="text-center" id="grand_total_bottom">0.00</td>
                    </tr>
                </table>
            </div>
        </div>

        <div class="row mt-4">
            <div class="col-md-6 offset-md-6">
                <table class="table table-bordered table-sm w-100" style="font-size: 14px;">
                    <tr style="border-top: 2px solid #000; font-weight: bold;">
                        <td width="50%"></td>
                        <td class="text-center">VALUE IDR</td>
                        <td class="text-center">VALUE USD</td>
                    </tr>
                    <tr style="border-top: 2px solid #000; font-weight: bold;">
                        <td width="50%">TOTAL COST</td>
                        <td width="25%" class="text-right text-danger" id="grand-tot-idr">0.00</td>
                        <td width="25%" class="text-right text-danger" id="grand-tot-usd">0.00</td>
                    </tr>
                    <tr style="font-weight: bold;">
                        <td>VAT <span class="float-right" id="vat-label-pct">0%</span></td>
                        <td class="text-right" id="val-vat-idr">0.00</td>
                        <td class="text-right" id="val-vat-usd">0.00</td>
                    </tr>
                    <tr style="font-weight: bold;">
                        <td>PROFIT <span class="float-right">6%</span></td>
                        <td class="text-right" id="val-profit-idr">0.00</td>
                        <td class="text-right" id="val-profit-usd">0.00</td>
                    </tr>
                </table>
            </div>
        </div>

    </div>
</div>

<div class="modal fade" id="modal_edit_costing" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h5 class="modal-title fw-bold text-dark"><i class="fas fa-edit"></i> Edit Detail Costing</h5>
                <button type="button" class="btn-close text-light" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body bg-light">
                <input type="hidden" id="m_id_detail">
                <input type="hidden" id="m_category">

                <div class="row">
                    <div class="col-md-12 form-group">
                        <label class="fw-bold">Item <span class="text-danger">*</span></label>
                        <select id="m_item" class="form-control select2modal" style="width: 100%;"></select>
                    </div>
                </div>

                <div class="row">
                   <div class="col-md-4 form-group m-not-other">
                        <label class="fw-bold">Set</label>
                        <select id="m_set" class="form-control select2modal" style="width: 100%;">
                            <option value="">-- Set --</option>
                            @foreach ($set as $s)
                                <option value="{{ $s->id ?? $s->nama }}">{{ $s->nama ?? $s->id }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4 form-group m-not-other">
                        <label class="fw-bold">Desc</label>
                        <input type="text" id="m_desc" class="form-control">
                    </div>
                    <div class="col-md-4 form-group m-not-other">
                        <label class="fw-bold">Supplier</label>
                        <select id="m_supplier" class="form-control select2modal" style="width: 100%;">
                            <option value="">-- Supplier --</option>
                            @foreach ($suppliers as $sup)
                                <option value="{{ $sup->Id_Supplier ?? $sup->id_supplier }}">{{ $sup->Supplier ?? $sup->supplier }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="row m-not-other">
                    <div class="col-md-3 form-group">
                        <label class="fw-bold">Curr</label>
                        <select id="m_curr" class="form-control m-calc">
                            @foreach ($currencies as $cur)
                                <option value="{{ $cur->nama_pilihan }}">{{ $cur->nama_pilihan }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3 form-group">
                        <label class="fw-bold">Price</label>
                        <input type="text" id="m_price" class="form-control text-right input-decimal m-calc" value="0">
                    </div>
                    <div class="col-md-3 form-group">
                        <label class="fw-bold">Cons/Pc</label>
                        <input type="text" id="m_cons" class="form-control text-center input-decimal m-calc" value="0">
                    </div>
                    <div class="col-md-3 form-group">
                        <label class="fw-bold">Unit</label>
                        <select id="m_unit" class="form-control select2modal" style="width: 100%;">
                            @foreach ($units as $u)
                                <option value="{{ $u->nama_pilihan }}">{{ $u->nama_pilihan }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="row mt-2 pt-3 border-top">
                    <div class="col-md-4 form-group">
                        <label class="fw-bold">Allow (%)</label>
                        <input type="text" id="m_allowance" class="form-control text-center input-decimal m-calc " value="0">
                    </div>
                    <div class="col-md-4 form-group">
                        <label class="fw-bold ">Value IDR</label>
                        <input type="text" id="m_val_idr" class="form-control text-right fw-bold bg-white text-success " readonly>
                    </div>
                    <div class="col-md-4 form-group">
                        <label class="fw-bold">Value USD</label>
                        <input type="text" id="m_val_usd" class="form-control text-right fw-bold bg-white text-success " readonly>
                    </div>
                </div>
            </div>
            <div class="modal-footer py-1">
                <button type="button" class="btn btn-danger" data-bs-dismiss="modal"><i class="fa fa-times"></i> Batal</button>
                <button type="button" class="btn btn-success fw-bold" onclick="updateDetailModal()"><i class="fas fa-save"></i> Update</button>
            </div>
        </div>
    </div>
</div>

@endsection

@section('custom-script')
<script src="{{ asset('plugins/select2/js/select2.full.min.js') }}"></script>
<script>
    $(document).ready(function() {
        $('.select2bs4').select2({ theme: 'bootstrap4', width: '100%' });

        $(document).on('select2:open', () => {
            document.querySelector('.select2-container--open .select2-search__field').focus();
        });

        $(document).on('input', '.input-decimal', function() {
            let val = $(this).val().replace(/[^0-9.]/g, '');
            let parts = val.split('.');
            if (parts.length > 2) val = parts[0] + '.' + parts.slice(1).join('');
            $(this).val(val);
        });

        $('.calc-input').on('input change', function() { calculate_template(); });
        $('.m-calc').on('input change', function() { calculate_modal(); });
        $('#rate_to_idr, #rate_from_idr').on('input', function() { calculate_template(); calculate_summary(); });

        calculate_summary();

        $('#form-header').on('submit', function() {
            $('#type').prop('disabled', false);
        });


        $('input[name="qty"], input[name="vat"]').on('input change', function() {
            calculate_summary();
        });

        function check_shipment_type() {
            let shipment_type = $('#shipment_type').val();
            $('#vat').val('0.00');
            if (shipment_type === 'local') {
                $('#vat').val('11.00');
            }
        }

        $('#shipment_type').on('change', function() { check_shipment_type(); });

        let saved_product_item = "{{ $costing->product_item ?? '' }}";

        function loadProductItems(selected_grup) {
            let select_item = $('#product_item');
            if(selected_grup) {
                $.post("{{ route('get-product-items') }}", { _token: "{{ csrf_token() }}", product_group: selected_grup }, function(res) {
                    select_item.empty().append('<option value="">Pilih Product Item</option>');
                    $.each(res, function(key, value) {
                        let is_selected = (value.id == saved_product_item) ? 'selected' : '';
                        select_item.append(`<option value="${value.id}" ${is_selected}>${value.product_item}</option>`);
                    });
                    select_item.trigger('change');
                });
            } else {
                select_item.empty().append('<option value="">Pilih Product Item</option>').trigger('change');
            }
        }

        $('#product_group').on('change', function() {
            loadProductItems($(this).val());
        });

        if ($('#product_group').val() !== '') {
            loadProductItems($('#product_group').val());
        }

        $('#category').on('change', function() {
            let cat = $(this).val();
            let select_item = $('#txt_item');

            if (select_item.data("select2")) {
                select_item.select2('destroy');
            }

            select_item.html('<option value="" disabled selected>Sedang Menarik Data Item...</option>');
            select_item.select2({ theme: 'bootstrap4', width: '100%' });

            if (cat) {
                let cat_query = cat;

                $.ajax({
                    url: "{{ route('get-item-contents') }}",
                    type: "GET",
                    data: { kategori: cat_query },
                    success: function(res) {
                        if (select_item.data("select2")) {
                            select_item.select2('destroy');
                        }
                        select_item.html(res);
                        select_item.select2({ theme: 'bootstrap4', width: '100%' });
                    },
                    error: function() { alert('Gagal mengambil data item dari server.'); }
                });
            }
        });

        let prev_dest_count = $('#main_dest').val() ? $('#main_dest').val().length : 0;
        $('#main_dest').on('change', function() {
            let curr_val = $(this).val();
            let curr_count = curr_val ? curr_val.length : 0;

            if (curr_count > 1 && prev_dest_count <= 1) {
                Swal.fire({
                    title: 'Perhatian!',
                    text: 'Anda memilih lebih dari 1 Destination. Ingat untuk menggabungkan WS jika diperlukan!',
                    icon: 'info',
                    confirmButtonColor: '#3085d6',
                    confirmButtonText: 'Ya, Mengerti'
                });
            }
            prev_dest_count = curr_count;
        });
    });

    function calculate_template() {
        let rate_to_idr = parseFloat($('#rate_to_idr').val()) || 15000;
        let rate_from_idr = parseFloat($('#rate_from_idr').val()) || 15000;

        let curr = $('#txt_curr option:selected').text().trim().toUpperCase();
        let price = parseFloat($('#txt_price').val()) || 0;
        let cons = parseFloat($('#txt_cons').val()) || 0;
        let allow_pct = parseFloat($('#txt_allowance').val()) || 0;
        let item_text = $('#txt_item option:selected').text().toUpperCase();

        if (item_text.includes('OVERHEAD')) {
            $('#txt_allowance').val('6').prop('readonly', true).addClass('bg-light');
            allow_pct = 6;
        } else {
            $('#txt_allowance').prop('readonly', false).removeClass('bg-light');
        }

        let px_idr = 0; let px_usd = 0;
        if (curr === 'IDR') { px_idr = price; px_usd = price / rate_from_idr; }
        else { px_usd = price; px_idr = price * rate_to_idr; }

        let allow = 1 + (allow_pct / 100);
        let val_idr = Math.round(px_idr * cons * allow);
        let val_usd = px_usd * cons * allow;

        $('#txt_px_idr').val(px_idr.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
        $('#txt_px_usd').val(px_usd.toLocaleString('en-US', {minimumFractionDigits: 4, maximumFractionDigits: 4}));
        $('#txt_val_idr').val(val_idr.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
        $('#txt_val_usd').val(val_usd.toLocaleString('en-US', {minimumFractionDigits: 4, maximumFractionDigits: 4}));
        $('#txt_px_idr').data('raw', px_idr);
        $('#txt_px_usd').data('raw', px_usd);
        $('#txt_val_idr').data('raw', val_idr);
        $('#txt_val_usd').data('raw', val_usd);
    }

    // function calculate_modal() {

    //     let rate_to_idr = parseFloat($('#rate_to_idr').val()) || 15000;
    //     let rate_from_idr = parseFloat($('#rate_from_idr').val()) || 15000;

    //     let curr = $('#m_curr').val().toUpperCase();
    //     let price = parseFloat($('#m_price').val()) || 0;
    //     let cons = parseFloat($('#m_cons').val()) || 0;
    //     let allow_pct = parseFloat($('#m_allowance').val()) || 0;
    //     let item_text = $('#m_item option:selected').text().toUpperCase();

    //     if (item_text.includes('OVERHEAD')) {
    //         $('#m_allowance').val('6').prop('readonly', true).addClass('bg-light');
    //         allow_pct = 6;
    //     } else {
    //         $('#m_allowance').prop('readonly', false).removeClass('bg-light');
    //     }

    //     let px_idr = 0; let px_usd = 0;

    //     if (curr === 'IDR') {
    //         px_idr = price; px_usd = price / rate_from_idr;
    //     }else {
    //         px_usd = price; px_idr = price * rate_to_idr;
    //     }

    //     let allow = 1 + (allow_pct / 100);
    //     let val_idr = Math.round(px_idr * cons * allow);
    //     let val_usd = px_usd * cons * allow;

    //     $('#m_val_idr').val(val_idr.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})).data('raw', val_idr);
    //     $('#m_val_usd').val(val_usd.toLocaleString('en-US', {minimumFractionDigits: 4, maximumFractionDigits: 4})).data('raw', val_usd);
    //     $('#m_price').data('raw_idr', px_idr).data('raw_usd', px_usd);
    // }

    function calculate_modal() {
        let rate_to_idr = parseFloat($('#rate_to_idr').val()) || 15000;
        let rate_from_idr = parseFloat($('#rate_from_idr').val()) || 15000;

        let cat = $('#m_category').val();
        let allow_pct = parseFloat($('#m_allowance').val()) || 0;

        // === JIKA KATEGORI OTHER COST ===
        if (cat === 'Other Cost') {
            let item_text = $('#m_item option:selected').text().toUpperCase();

            let sum_fab_idr = 0, sum_sew_idr = 0, sum_pack_idr = 0, sum_oth_norm_idr = 0;
            let sum_fab_usd = 0, sum_sew_usd = 0, sum_pack_usd = 0, sum_oth_norm_usd = 0;

            $('.row-costing').each(function() {
                let row_cat = $(this).data('category');
                let val_idr = parseFloat($(this).find('.val-idr-td').data('val')) || 0;
                let val_usd = parseFloat($(this).find('.val-usd-td').data('val')) || 0;

                if(row_cat === 'Fabric') { sum_fab_idr += val_idr; sum_fab_usd += val_usd; }
                else if(row_cat === 'Accessories Sewing') { sum_sew_idr += val_idr; sum_sew_usd += val_usd; }
                else if(row_cat === 'Accessories Packing') { sum_pack_idr += val_idr; sum_pack_usd += val_usd; }
                // Note: Manufacturing sengaja tidak dihitung untuk dasar Other Cost
                else if(row_cat === 'Other Cost') {
                    let name = $(this).find('.item-name').text().toUpperCase();
                    if (!name.includes('OVERHEAD') && $(this).attr('id') !== 'row-' + $('#m_id_detail').val()) {
                        sum_oth_norm_idr += val_idr;
                        sum_oth_norm_usd += val_usd;
                    }
                }
            });

            // PERBAIKAN: Dasar Perhitungan HANYA MATERIAL (Tanpa MFG)
            let base_material_idr = sum_fab_idr + sum_sew_idr + sum_pack_idr;
            let base_material_usd = sum_fab_usd + sum_sew_usd + sum_pack_usd;

            let val_idr = 0, val_usd = 0;

            if (item_text.includes('OVERHEAD')) {
                $('#m_allowance').val('6').prop('readonly', true).addClass('bg-light');
                allow_pct = 6;

                let base_overhead_idr = base_material_idr + sum_oth_norm_idr;
                let base_overhead_usd = base_material_usd + sum_oth_norm_usd;

                val_idr = base_overhead_idr * (allow_pct / 100);
                val_usd = base_overhead_usd * (allow_pct / 100);
            }
            else {
                $('#m_allowance').prop('readonly', false).removeClass('bg-light');

                val_idr = base_material_idr * (allow_pct / 100);
                val_usd = base_material_usd * (allow_pct / 100);
            }

            $('#m_val_idr').val(val_idr.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})).data('raw', val_idr);
            $('#m_val_usd').val(val_usd.toLocaleString('en-US', {minimumFractionDigits: 4, maximumFractionDigits: 4})).data('raw', val_usd);
            $('#m_price').data('raw_idr', 0).data('raw_usd', 0);

        }
        // === JIKA KATEGORI MATERIAL BIASA ===
        else {
            let curr = $('#m_curr').val().toUpperCase();
            let price = parseFloat($('#m_price').val()) || 0;
            let cons = parseFloat($('#m_cons').val()) || 0;

            $('#m_allowance').prop('readonly', false).removeClass('bg-light');

            let px_idr = 0; let px_usd = 0;

            if (curr === 'IDR') {
                px_idr = price; px_usd = price / rate_from_idr;
            } else {
                px_usd = price; px_idr = price * rate_to_idr;
            }

            let allow = 1 + (allow_pct / 100);
            let val_idr = Math.round(px_idr * cons * allow);
            let val_usd = px_usd * cons * allow;

            $('#m_val_idr').val(val_idr.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})).data('raw', val_idr);
            $('#m_val_usd').val(val_usd.toLocaleString('en-US', {minimumFractionDigits: 4, maximumFractionDigits: 4})).data('raw', val_usd);
            $('#m_price').data('raw_idr', px_idr).data('raw_usd', px_usd);
        }
    }

    function check_duplicate(category_id, item_id, setId = '', exclude_row_id = null) {
        let is_duplicate = false;

        $('.row-costing[data-category="'+category_id+'"]').each(function() {
            let row_item = $(this).data('item') ? $(this).data('item').toString() : '';
            let row_set = $(this).data('set') ? $(this).data('set').toString() : '';
            let row_id = $(this).attr('id');

            if (exclude_row_id && row_id === exclude_row_id) {
                return true;
            }

            if (category_id === 'Other Cost') {
                if (row_item === item_id.toString()) {
                    is_duplicate = true;
                    return false;
                }
            } else {
                let inputSetId = setId ? setId.toString() : '';

                if (row_item === item_id.toString() && row_set === inputSetId) {
                    is_duplicate = true;
                    return false;
                }
            }
        });

        return is_duplicate;
    }

    function saveDetail() {
        let id_costing = '{{ $costing->id ?? "" }}';
        let cat = $('#category').val();
        let item_id = $('#txt_item').val();
        let item_text = $('#txt_item option:selected').text();
        let desc = $('#txt_desc').val();
        let supplier_id = $('#txt_supplier').val() || '';
        let supplier_text = supplier_id ? $('#txt_supplier option:selected').text() : '';
        let curr = $('#txt_curr').val();
        let price = $('#txt_price').val() || 0;
        let cons = $('#txt_cons').val() || 0;
        let unit = $('#txt_unit').val();
        let allow = $('#txt_allowance').val() || 0;

        let px_idr_text = $('#txt_px_idr').val();
        let px_usd_text = $('#txt_px_usd').val();

        let px_idr_raw = $('#txt_px_idr').data('raw') || 0;
        let px_usd_raw = $('#txt_px_usd').data('raw') || 0;
        let val_idr_raw = $('#txt_val_idr').data('raw') || 0;
        let val_usd_raw = $('#txt_val_usd').data('raw') || 0;

        let set_val = $('#txt_set').val() || '';
        let set_text = set_val ? $('#txt_set option:selected').text() : '';


        let order_qty = parseFloat($('#qty').val().replace(/,/g, '')) || 0;
        let qty_bom_js = Math.ceil((1 + (allow / 100)) * order_qty * cons);
        let tot_val_js = qty_bom_js * px_idr_raw;

        if (!cat) {
            Swal.fire('Peringatan', 'Silakan Pilih Kategori di atas!', 'warning');
            return;
        }

        if (!item_id) {
            Swal.fire('Peringatan', 'Silakan Pilih Item pada form!', 'warning');
            return;
        }

        let header_type = $('#type').val();
        if (header_type === 'multiple' && set_val === '') {
            Swal.fire('Peringatan', 'Tipe Costing MULTIPLE, silakan Pilih Set terlebih dahulu!', 'warning');
            return;
        }

        Swal.fire({ title: 'Menyimpan...', allowOutsideClick: false, didOpen: () => { Swal.showLoading(); } });

        $.ajax({
            url: "{{ route('store-costing-detail') }}",
            type: "POST",
            data: {
                _token: "{{ csrf_token() }}",
                id_costing: id_costing,
                category: cat,
                item: item_id,
                desc: desc,
                supplier: supplier_id,
                curr: curr,
                price: price,
                cons: cons,
                unit: unit,
                price_px_idr: px_idr_raw,
                price_px_usd: px_usd_raw,
                allowance: allow,
                set: set_val,
                value_idr: val_idr_raw,
                value_usd: val_usd_raw
            },
            success: function(res) {
                if(res.status == 200) {
                    Swal.close();
                    let val_idr_text = val_idr_raw.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
                    let val_usd_text = val_usd_raw.toLocaleString('en-US', {minimumFractionDigits: 4, maximumFractionDigits: 4});

                    let qty_bom_text = qty_bom_js.toLocaleString('en-US');
                    let tot_val_text = tot_val_js.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});

                    let target_tbody = '';
                    if(cat === 'Fabric') { target_tbody = '#tbody-fabric'; }
                    else if (cat === 'Accessories Sewing') { target_tbody = '#tbody-acc-sewing'; }
                    else if (cat === 'Accessories Packing') { target_tbody = '#tbody-acc-packing'; }
                    else if (cat === 'Manufacturing') { target_tbody = '#tbody-manufacturing'; }
                    else if (cat === 'Other Cost') { target_tbody = '#tbody-other-cost'; }

                    let rowCount = $(target_tbody + ' tr').length + 1;
                    let tr = '';

                    if (cat === 'Other Cost') {
                        tr = `
                            <tr class="row-costing" data-category="${cat}" data-item="${item_id}" id="row-${res.insert_id}">
                                <td class="text-center fw-bold row-number">${rowCount}</td>
                                <td class="item-name">${item_text}</td>
                                <td class="text-center allow-td" data-val="${allow}">${allow}%</td>
                                <td class="text-right val-idr-td fw-bold" data-val="${val_idr_raw}">${val_idr_text}</td>
                                <td class="text-right val-usd-td fw-bold" data-val="${val_usd_raw}">${val_usd_text}</td>
                                <td class="text-center pct-td fw-bold">0%</td>
                                <td class="text-center align-middle">
                                    <button type="button" class="btn btn-sm btn-primary py-0 px-2 mr-1" onclick="editRowModal(this, ${res.insert_id}, '${cat}')"><i class="fas fa-edit"></i></button>
                                    <button type="button" class="btn btn-sm btn-danger py-0 px-2" onclick="removeRow(this, ${res.insert_id})"><i class="fas fa-trash"></i></button>
                                </td>
                            </tr>
                        `;
                    } else {
                        tr = `
                            <tr class="row-costing" data-category="${cat}" data-item="${item_id}" data-set="${set_val}" id="row-${res.insert_id}">
                                <td class="text-center fw-bold row-number">${rowCount}</td>
                                <td class="item-name">${item_text}</td>
                                <td class="set-td">${set_text}</td>
                                <td>${desc}</td>
                                <td>${supplier_text}</td>
                                <td class="text-right px-idr-td" data-val="${px_idr_raw}">${px_idr_text}</td>
                                <td class="text-right px-usd-td" data-val="${px_usd_raw}">${px_usd_text}</td>
                                <td class="text-center cons-td" data-val="${cons}">${cons}</td><td class="text-center">${unit}</td>
                                <td class="text-center allow-td" data-val="${allow}">${allow}%</td>
                                <td class="text-right val-idr-td fw-bold" data-val="${val_idr_raw}">${val_idr_text}</td>
                                <td class="text-right val-usd-td fw-bold" data-val="${val_usd_raw}">${val_usd_text}</td>
                                <td class="text-center pct-td fw-bold">0%</td>
                                <td class="text-right qty-bom-td fw-bold" data-val="${qty_bom_js}">${qty_bom_text}</td>
                                <td class="text-right tot-val-td fw-bold" data-val="${tot_val_js}">${tot_val_text}</td>
                                <td class="text-center align-middle">
                                    <button type="button" class="btn btn-sm btn-primary py-0 px-2 mr-1" onclick="editRowModal(this, ${res.insert_id}, '${cat}')"><i class="fas fa-edit"></i></button>
                                    <button type="button" class="btn btn-sm btn-danger py-0 px-2" onclick="removeRow(this, ${res.insert_id})"><i class="fas fa-trash"></i></button>
                                </td>
                            </tr>
                        `;
                    }

                    $(target_tbody).append(tr);

                    $('#txt_item').val('').trigger('change');
                    $('#txt_set').val('').trigger('change');
                    $('#txt_desc').val('');
                    $('#txt_supplier').val('').trigger('change');
                    $('#txt_price').val('0');
                    $('#txt_cons').val('0');
                    $('#txt_allowance').val('0').prop('readonly', false);

                    calculate_template();
                    calculate_summary();
                } else {
                    Swal.fire('Error', res.message, 'error');
                }
            }
        });
    }

    function editRowModal(btn, id, cat) {
        Swal.fire({ title: 'Memuat data...', allowOutsideClick: false, didOpen: () => { Swal.showLoading(); } });

        let urlTarget = "{{ route('get-detail-row-costing', ':id') }}".replace(':id', id);

        $.get(urlTarget, function(res) {
            if(res.status == 200) {
                let d = res.data;

                $('#m_id_detail').val(d.id);
                $('#m_category').val(cat);

                let cat_for_query = cat;

                $.ajax({
                    url: "{{ route('get-item-contents') }}",
                    type: "GET",
                    data: { kategori: cat_for_query },
                    success: function(htmlOptions) {
                        Swal.close();

                        if ($('#m_item').data('select2')) {
                            $('#m_item').select2('destroy');
                        }

                        $('#m_item').html(htmlOptions).select2({ theme: 'bootstrap4', dropdownParent: $('#modal_edit_costing') });
                        $('#m_item').val(d.item_id).trigger('change');

                        if(cat === 'Other Cost') { $('.m-not-other').hide(); }
                        else { $('.m-not-other').show(); }

                        $('#m_desc').val(d.item_desc);
                        $('#m_supplier').val(d.supplier_id || d.supplier).trigger('change');
                        $('#m_curr').val(d.curr || 'IDR');
                        $('#m_price').val(d.price || 0);
                        $('#m_cons').val(d.cons || 0);
                        $('#m_unit').val(d.unit).trigger('change');
                        $('#m_allowance').val(d.allowance || 0);
                        $('#m_set').val(d.set).trigger('change');

                        calculate_modal();

                        $('.select2modal').not('#m_item').each(function() {
                            if ($(this).data('select2')) { $(this).select2('destroy'); }
                        });
                        $('.select2modal').not('#m_item').select2({ theme: 'bootstrap4', dropdownParent: $('#modal_edit_costing') });

                        $('#modal_edit_costing').modal('show');
                    },
                    error: function() {
                        Swal.close();
                        Swal.fire('Error', 'Gagal mengambil list item', 'error');
                    }
                });

            } else {
                Swal.close();
                Swal.fire('Error', 'Data detail tidak ditemukan.', 'error');
            }
        }).fail(function() {
            Swal.close();
            Swal.fire('Error', 'Gagal menghubungi server (404/500)', 'error');
        });
    }

    function updateDetailModal() {
        let id_detail = $('#m_id_detail').val();
        let cat = $('#m_category').val();

        let item_id = $('#m_item').val();
        let item_text = $('#m_item option:selected').text();
        let desc = $('#m_desc').val();
        let supplier_id = $('#m_supplier').val() || '';
        let supplier_text = supplier_id ? $('#m_supplier option:selected').text() : '';
        let curr = $('#m_curr').val();
        let price = $('#m_price').val() || 0;
        let cons = $('#m_cons').val() || 0;
        let unit = $('#m_unit').val();
        let allow = $('#m_allowance').val() || 0;

        let px_idr_raw = $('#m_price').data('raw_idr') || 0;
        let px_usd_raw = $('#m_price').data('raw_usd') || 0;
        let val_idr_raw = $('#m_val_idr').data('raw') || 0;
        let val_usd_raw = $('#m_val_usd').data('raw') || 0;

        let set_val = $('#m_set').val() || '';
        let set_text = set_val ? $('#m_set option:selected').text() : '';

        let order_qty = parseFloat($('#qty').val().replace(/,/g, '')) || 0;
        let qty_bom_js = Math.ceil((1 + (allow / 100)) * order_qty * cons);
        let tot_val_js = qty_bom_js * px_idr_raw;

        if (!item_id) { Swal.fire('Peringatan', 'Pilih Item terlebih dahulu!', 'warning'); return; }

        Swal.fire({ title: 'Menyimpan...', allowOutsideClick: false, didOpen: () => { Swal.showLoading(); } });

        $.ajax({
            url: "{{ route('update-detail-costing') }}",
            type: "POST",
            data: {
                _token: "{{ csrf_token() }}",
                id_detail: id_detail,
                category: cat,
                item: item_id,
                desc: desc,
                supplier: supplier_id,
                curr: curr,
                price: price,
                cons: cons,
                unit: unit,
                price_px_idr: px_idr_raw,
                price_px_usd: px_usd_raw,
                allowance: allow,
                set: set_val,
                value_idr: val_idr_raw,
                value_usd: val_usd_raw
            },
            success: function(res) {
                if(res.status == 200) {
                    Swal.close();
                    $('#modal_edit_costing').modal('hide');

                    let val_idr_text = val_idr_raw.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
                    let val_usd_text = val_usd_raw.toLocaleString('en-US', {minimumFractionDigits: 4, maximumFractionDigits: 4});
                    let px_idr_text = px_idr_raw.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
                    let px_usd_text = px_usd_raw.toLocaleString('en-US', {minimumFractionDigits: 4, maximumFractionDigits: 4});

                    let qty_bom_text = qty_bom_js.toLocaleString('en-US');
                    let tot_val_text = tot_val_js.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});

                    let tr = '';
                    let rowCount = $('#row-' + id_detail).find('.row-number').text();

                    if (cat === 'Other Cost') {
                        tr = `
                            <tr class="row-costing" data-category="${cat}" data-item="${item_id}" id="row-${id_detail}">
                                <td class="text-center fw-bold row-number">${rowCount}</td>
                                <td class="item-name">${item_text}</td>
                                <td class="text-center allow-td" data-val="${allow}">${allow}%</td>
                                <td class="text-right val-idr-td fw-bold" data-val="${val_idr_raw}">${val_idr_text}</td>
                                <td class="text-right val-usd-td fw-bold" data-val="${val_usd_raw}">${val_usd_text}</td>
                                <td class="text-center pct-td fw-bold">0%</td>
                                <td class="text-center align-middle">
                                    <button type="button" class="btn btn-sm btn-primary py-0 px-2 mr-1" onclick="editRowModal(this, ${id_detail}, '${cat}')"><i class="fas fa-edit"></i></button>
                                    <button type="button" class="btn btn-sm btn-danger py-0 px-2" onclick="removeRow(this, ${id_detail})"><i class="fas fa-trash"></i></button>
                                </td>
                            </tr>
                        `;
                    } else {
                        tr = `
                            <tr class="row-costing" data-category="${cat}" data-item="${item_id}" data-set="${set_val}" id="row-${res.insert_id}">
                                <td class="text-center fw-bold row-number">${rowCount}</td>
                                <td class="item-name">${item_text}</td>
                                <td class="set-td">${set_text}</td>
                                <td>${desc}</td>
                                <td>${supplier_text}</td>
                                <td class="text-right px-idr-td" data-val="${px_idr_raw}">${px_idr_text}</td>
                                <td class="text-right px-usd-td" data-val="${px_usd_raw}">${px_usd_text}</td>
                                <td class="text-center cons-td" data-val="${cons}">${cons}</td><td class="text-center">${unit}</td>
                                <td class="text-center allow-td" data-val="${allow}">${allow}%</td>
                                <td class="text-right val-idr-td fw-bold" data-val="${val_idr_raw}">${val_idr_text}</td>
                                <td class="text-right val-usd-td fw-bold" data-val="${val_usd_raw}">${val_usd_text}</td>
                                <td class="text-center pct-td fw-bold">0%</td>
                                <td class="text-right qty-bom-td fw-bold" data-val="${qty_bom_js}">${qty_bom_text}</td>
                                <td class="text-right tot-val-td fw-bold" data-val="${tot_val_js}">${tot_val_text}</td>
                                <td class="text-center align-middle">
                                    <button type="button" class="btn btn-sm btn-primary py-0 px-2 mr-1" onclick="editRowModal(this, ${id_detail}, '${cat}')"><i class="fas fa-edit"></i></button>
                                    <button type="button" class="btn btn-sm btn-danger py-0 px-2" onclick="removeRow(this, ${id_detail})"><i class="fas fa-trash"></i></button>
                                </td>
                            </tr>
                        `;
                    }

                    $('#row-' + id_detail).replaceWith(tr);
                    calculate_summary();
                } else { Swal.fire('Error', res.message, 'error'); }
            }
        });
    }

    function removeRow(btn, id) {
        Swal.fire({
            title: 'Hapus Item?',
            text: "Data akan dihapus secara permanen dari sistem!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({ title: 'Menghapus...', allowOutsideClick: false, didOpen: () => { Swal.showLoading(); } });

                let deleteUrl = "{{ route('delete-costing-detail', ':id') }}".replace(':id', id);

                $.ajax({
                    url: deleteUrl,
                    type: "POST",
                    data: { _method: "DELETE", _token: "{{ csrf_token() }}" },
                    success: function(res) {
                        if(res.status == 200) {
                            Swal.close();
                            let tbody = $(btn).closest('tbody');
                            $(btn).closest('tr').remove();

                            tbody.find('tr').each(function(index) {
                                $(this).find('.row-number').text(index + 1);
                            });

                            calculate_summary();
                        } else {
                            Swal.fire('Error', res.message || 'Gagal menghapus data', 'error');
                        }
                    },
                    error: function(xhr) {
                        Swal.fire('Error', 'Terjadi kesalahan pada server.', 'error');
                    }
                });
            }
        });
    }

    // function calculate_summary() {
    //     let sum_fab_idr = 0, sum_fab_usd = 0;
    //     let sum_sew_idr = 0, sum_sew_usd = 0;
    //     let sum_pack_idr = 0, sum_pack_usd = 0;
    //     let sum_mfg_idr = 0, sum_mfg_usd = 0;
    //     let sum_oth_norm_idr = 0, sum_oth_norm_usd = 0;

    //     let order_qty = parseFloat($('#qty').val().replace(/,/g, '')) || 0;
    //     let sum_val_fab = 0, sum_val_sew = 0, sum_val_pack = 0, sum_val_mfg = 0;
    //     let grand_total_value = 0;

    //     $('.row-costing').each(function() {
    //         let cat = $(this).data('category');
    //         let val_idr = parseFloat($(this).find('.val-idr-td').data('val')) || 0;
    //         let val_usd = parseFloat($(this).find('.val-usd-td').data('val')) || 0;

    //         if(cat === 'Fabric') { sum_fab_idr += val_idr; sum_fab_usd += val_usd; }
    //         else if(cat === 'Accessories Sewing') { sum_sew_idr += val_idr; sum_sew_usd += val_usd; }
    //         else if(cat === 'Accessories Packing') { sum_pack_idr += val_idr; sum_pack_usd += val_usd; }
    //         else if(cat === 'Manufacturing') { sum_mfg_idr += val_idr; sum_mfg_usd += val_usd; }

    //         if (cat !== 'Other Cost') {
    //             let cons = parseFloat($(this).find('.cons-td').data('val')) || 0;
    //             let allow = parseFloat($(this).find('.allow-td').data('val')) || 0;
    //             let px_idr = parseFloat($(this).find('.px-idr-td').data('val')) || 0;

    //             let qty_bom = Math.ceil((1 + (allow / 100)) * order_qty * cons);
    //             let tot_val = qty_bom * px_idr;

    //             $(this).find('.qty-bom-td').data('val', qty_bom).text(qty_bom.toLocaleString('en-US', {minimumFractionDigits: 0, maximumFractionDigits: 0}));
    //             $(this).find('.tot-val-td').data('val', tot_val).text(tot_val.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}));

    //             if(cat === 'Fabric') sum_val_fab += tot_val;
    //             else if(cat === 'Accessories Sewing') sum_val_sew += tot_val;
    //             else if(cat === 'Accessories Packing') sum_val_pack += tot_val;
    //             else if(cat === 'Manufacturing') sum_val_mfg += tot_val;

    //             grand_total_value += tot_val;
    //         }
    //     });

    //     let overhead_row = null;

    //     $('#tbody-other-cost tr').each(function() {
    //         let name = $(this).find('.item-name').text().toUpperCase();
    //         if (name.includes('OVERHEAD')){
    //             overhead_row = $(this);
    //         } else {
    //             sum_oth_norm_idr += parseFloat($(this).find('.val-idr-td').data('val')) || 0;
    //             sum_oth_norm_usd += parseFloat($(this).find('.val-usd-td').data('val')) || 0;
    //         }
    //     });

    //     let base_overhead_idr = sum_fab_idr + sum_sew_idr + sum_pack_idr + sum_mfg_idr + sum_oth_norm_idr;
    //     let base_overhead_usd = sum_fab_usd + sum_sew_usd + sum_pack_usd + sum_mfg_usd + sum_oth_norm_usd;
    //     let overhead_idr = 0, overhead_usd = 0;

    //     if(overhead_row) {
    //         let oh_allow = parseFloat(overhead_row.find('.allow-td').data('val')) || 6;
    //         overhead_idr = base_overhead_idr * (oh_allow / 100);
    //         overhead_usd = base_overhead_usd * (oh_allow / 100);

    //         overhead_row.find('.val-idr-td').data('val', overhead_idr).text(overhead_idr.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
    //         overhead_row.find('.val-usd-td').data('val', overhead_usd).text(overhead_usd.toLocaleString('en-US', {minimumFractionDigits: 4, maximumFractionDigits: 4}));
    //     }

    //     let tot_other_idr = sum_oth_norm_idr + overhead_idr;
    //     let tot_other_usd = sum_oth_norm_usd + overhead_usd;

    //     $('#tot-fab-idr').text(sum_fab_idr.toLocaleString('en-US', {minimumFractionDigits: 2}));
    //     $('#tot-fab-usd').text(sum_fab_usd.toLocaleString('en-US', {minimumFractionDigits: 4}));
    //     $('#tot-sew-idr').text(sum_sew_idr.toLocaleString('en-US', {minimumFractionDigits: 2}));
    //     $('#tot-sew-usd').text(sum_sew_usd.toLocaleString('en-US', {minimumFractionDigits: 4}));
    //     $('#tot-pack-idr').text(sum_pack_idr.toLocaleString('en-US', {minimumFractionDigits: 2}));
    //     $('#tot-pack-usd').text(sum_pack_usd.toLocaleString('en-US', {minimumFractionDigits: 4}));
    //     $('#tot-mfg-idr').text(sum_mfg_idr.toLocaleString('en-US', {minimumFractionDigits: 2}));
    //     $('#tot-mfg-usd').text(sum_mfg_usd.toLocaleString('en-US', {minimumFractionDigits: 4}));

    //     $('#tot-other-idr').text(tot_other_idr.toLocaleString('en-US', {minimumFractionDigits: 2}));
    //     $('#tot-other-usd').text(tot_other_usd.toLocaleString('en-US', {minimumFractionDigits: 4}));

    //     let base_ga_idr = sum_fab_idr + sum_sew_idr + sum_pack_idr + sum_mfg_idr + tot_other_idr;
    //     let base_ga_usd = sum_fab_usd + sum_sew_usd + sum_pack_usd + sum_mfg_usd + tot_other_usd;

    //     let ga_idr = base_ga_idr * 0.03;
    //     let ga_usd = base_ga_usd * 0.03;

    //     $('#val-ga-idr').text(ga_idr.toLocaleString('en-US', {minimumFractionDigits: 2}));
    //     $('#val-ga-usd').text(ga_usd.toLocaleString('en-US', {minimumFractionDigits: 4}));

    //     let grand_idr = base_ga_idr + ga_idr;
    //     let rate_from_idr = parseFloat($('#rate_from_idr').val()) || 15000;
    //     let grand_usd = grand_idr / rate_from_idr;

    //     let ga_pct = (grand_idr > 0) ? (ga_idr / grand_idr) * 100 : 0;
    //     $('.pct-ga').text(ga_pct.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}) + '%');


    //     $('.row-costing').each(function() {
    //         let val_idr = parseFloat($(this).find('.val-idr-td').data('val')) || 0;
    //         let pct = (grand_idr > 0) ? (val_idr / grand_idr) * 100 : 0;
    //         $(this).find('.pct-td').text(pct.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}) + '%');
    //     });

    //     let pct_fab = (grand_idr > 0) ? (sum_fab_idr / grand_idr) * 100 : 0;
    //     let pct_sew = (grand_idr > 0) ? (sum_sew_idr / grand_idr) * 100 : 0;
    //     let pct_pack = (grand_idr > 0) ? (sum_pack_idr / grand_idr) * 100 : 0;
    //     let pct_mfg = (grand_idr > 0) ? (sum_mfg_idr / grand_idr) * 100 : 0;
    //     let pct_other = (grand_idr > 0) ? (tot_other_idr / grand_idr) * 100 : 0;

    //     $('#tot-fab-pct').text(pct_fab.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}) + '%');
    //     $('#tot-sew-pct').text(pct_sew.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}) + '%');
    //     $('#tot-pack-pct').text(pct_pack.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}) + '%');
    //     $('#tot-mfg-pct').text(pct_mfg.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}) + '%');
    //     $('#tot-other-pct').text(pct_other.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}) + '%');


    //     $('#tot-fab-val').text(sum_val_fab.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
    //     $('#tot-sew-val').text(sum_val_sew.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
    //     $('#tot-pack-val').text(sum_val_pack.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
    //     $('#tot-mfg-val').text(sum_val_mfg.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}));

    //     $('#grand_total_value').text(grand_total_value.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}));

    //     let input_vat = parseFloat($('input[name="vat"]').val()) || 0;
    //     let shipment_type = $('#shipment_type').val();
    //     let actual_vat_pct = (shipment_type === 'export') ? 0 : input_vat;
    //     $('#vat-label-pct').text(actual_vat_pct + '%');

    //     let vat_multiplier = 1 + (actual_vat_pct / 100);
    //     let vat_idr = grand_idr * vat_multiplier;
    //     let vat_usd = grand_usd * vat_multiplier;
    //     let profit_idr = vat_idr * 1.06;
    //     let profit_usd = vat_usd * 1.06;

    //     $('#grand-tot-idr').text(grand_idr.toLocaleString('en-US', {minimumFractionDigits: 2}));
    //     $('#grand-tot-usd').text(grand_usd.toLocaleString('en-US', {minimumFractionDigits: 4}));
    //     $('#val-vat-idr').text(vat_idr.toLocaleString('en-US', {minimumFractionDigits: 2}));
    //     $('#val-vat-usd').text(vat_usd.toLocaleString('en-US', {minimumFractionDigits: 4}));
    //     $('#val-profit-idr').text(profit_idr.toLocaleString('en-US', {minimumFractionDigits: 2}));
    //     $('#val-profit-usd').text(profit_usd.toLocaleString('en-US', {minimumFractionDigits: 4}));

    //     check_type();
    // }

    function calculate_summary() {
        let sum_fab_idr = 0, sum_fab_usd = 0, sum_val_fab = 0;
        let sum_sew_idr = 0, sum_sew_usd = 0, sum_val_sew = 0;
        let sum_pack_idr = 0, sum_pack_usd = 0, sum_val_pack = 0;
        let sum_mfg_idr = 0, sum_mfg_usd = 0, sum_val_mfg = 0;

        let sum_oth_norm_idr = 0, sum_oth_norm_usd = 0;

        let grand_total_value = 0;
        // VARIABEL BARU UNTUK TOP & BOTTOM
        let grand_total_top = 0;
        let grand_total_bottom = 0;

        let order_qty = parseFloat($('#qty').val().replace(/,/g, '')) || 0;

        // 1. HITUNG SEMUA MATERIAL UTAMA
        $('.row-costing').each(function() {
            let cat = $(this).data('category');

            if (cat !== 'Other Cost') {
                let val_idr = parseFloat($(this).find('.val-idr-td').data('val')) || 0;
                let val_usd = parseFloat($(this).find('.val-usd-td').data('val')) || 0;
                let cons = parseFloat($(this).find('.cons-td').data('val')) || 0;
                let allow = parseFloat($(this).find('.allow-td').data('val')) || 0;
                let px_idr = parseFloat($(this).find('.px-idr-td').data('val')) || 0;

                let qty_bom = Math.ceil((1 + (allow / 100)) * order_qty * cons);
                let tot_val = qty_bom * px_idr;

                // CEK KOLOM SET (Kolom ke-3 / index 2)
                let set_name = $(this).find('td:eq(2)').text().toUpperCase().trim();
                if (set_name.includes('TOP')) {
                    grand_total_top += tot_val;
                } else if (set_name.includes('BOTTOM')) {
                    grand_total_bottom += tot_val;
                }

                $(this).find('.qty-bom-td').data('val', qty_bom).text(qty_bom.toLocaleString('en-US', {minimumFractionDigits: 0, maximumFractionDigits: 0}));
                $(this).find('.tot-val-td').data('val', tot_val).text(tot_val.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}));

                if (cat === 'Fabric') { sum_fab_idr += val_idr; sum_fab_usd += val_usd; sum_val_fab += tot_val; }
                else if (cat === 'Accessories Sewing') { sum_sew_idr += val_idr; sum_sew_usd += val_usd; sum_val_sew += tot_val; }
                else if (cat === 'Accessories Packing') { sum_pack_idr += val_idr; sum_pack_usd += val_usd; sum_val_pack += tot_val; }
                else if (cat === 'Manufacturing') { sum_mfg_idr += val_idr; sum_mfg_usd += val_usd; sum_val_mfg += tot_val; }

                grand_total_value += tot_val;
            }
        });

        // 2. DASAR PERHITUNGAN UNTUK OTHER COST (TANPA MFG)
        let base_material_idr = sum_fab_idr + sum_sew_idr + sum_pack_idr;
        let base_material_usd = sum_fab_usd + sum_sew_usd + sum_pack_usd;
        let overhead_row = null;

        // 3. HITUNG OTHER COST NORMAL
        $('#tbody-other-cost tr').each(function() {
            let name = $(this).find('.item-name').text().toUpperCase();
            let allow_pct = parseFloat($(this).find('.allow-td').data('val')) || 0;

            if (name.includes('OVERHEAD')) {
                overhead_row = $(this);
            } else {
                let val_idr = parseFloat($(this).find('.val-idr-td').data('val')) || 0;
                let val_usd = parseFloat($(this).find('.val-usd-td').data('val')) || 0;

                if (allow_pct > 0) {
                    val_idr = base_material_idr * (allow_pct / 100);
                    val_usd = base_material_usd * (allow_pct / 100);

                    $(this).find('.val-idr-td').data('val', val_idr).text(val_idr.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
                    $(this).find('.val-usd-td').data('val', val_usd).text(val_usd.toLocaleString('en-US', {minimumFractionDigits: 4, maximumFractionDigits: 4}));
                }

                sum_oth_norm_idr += val_idr;
                sum_oth_norm_usd += val_usd;
            }
        });

        // 4. HITUNG OVERHEAD (Base Material + Other Cost Normal)
        let base_overhead_idr = base_material_idr + sum_oth_norm_idr;
        let base_overhead_usd = base_material_usd + sum_oth_norm_usd;
        let overhead_idr = 0, overhead_usd = 0;

        if (overhead_row) {
            let oh_allow = parseFloat(overhead_row.find('.allow-td').data('val')) || 6;
            overhead_idr = base_overhead_idr * (oh_allow / 100);
            overhead_usd = base_overhead_usd * (oh_allow / 100);

            overhead_row.find('.val-idr-td').data('val', overhead_idr).text(overhead_idr.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
            overhead_row.find('.val-usd-td').data('val', overhead_usd).text(overhead_usd.toLocaleString('en-US', {minimumFractionDigits: 4, maximumFractionDigits: 4}));
        }

        // 5. KALKULASI GRAND TOTAL KESELURUHAN
        let tot_other_idr = sum_oth_norm_idr + overhead_idr;
        let tot_other_usd = sum_oth_norm_usd + overhead_usd;

        let base_ga_idr = sum_fab_idr + sum_sew_idr + sum_pack_idr + sum_mfg_idr + tot_other_idr;
        let base_ga_usd = sum_fab_usd + sum_sew_usd + sum_pack_usd + sum_mfg_usd + tot_other_usd;

        let ga_idr = base_ga_idr * 0.03;
        let ga_usd = base_ga_usd * 0.03;

        let grand_idr = base_ga_idr + ga_idr;
        let rate_from_idr = parseFloat($('#rate_from_idr').val()) || 15000;
        let grand_usd = grand_idr / rate_from_idr;

        // 6. UPDATE TEXT SUB-TOTAL DI LAYAR
        $('#tot-fab-idr').text(sum_fab_idr.toLocaleString('en-US', {minimumFractionDigits: 2}));
        $('#tot-fab-usd').text(sum_fab_usd.toLocaleString('en-US', {minimumFractionDigits: 4}));
        $('#tot-sew-idr').text(sum_sew_idr.toLocaleString('en-US', {minimumFractionDigits: 2}));
        $('#tot-sew-usd').text(sum_sew_usd.toLocaleString('en-US', {minimumFractionDigits: 4}));
        $('#tot-pack-idr').text(sum_pack_idr.toLocaleString('en-US', {minimumFractionDigits: 2}));
        $('#tot-pack-usd').text(sum_pack_usd.toLocaleString('en-US', {minimumFractionDigits: 4}));
        $('#tot-mfg-idr').text(sum_mfg_idr.toLocaleString('en-US', {minimumFractionDigits: 2}));
        $('#tot-mfg-usd').text(sum_mfg_usd.toLocaleString('en-US', {minimumFractionDigits: 4}));
        $('#tot-other-idr').text(tot_other_idr.toLocaleString('en-US', {minimumFractionDigits: 2}));
        $('#tot-other-usd').text(tot_other_usd.toLocaleString('en-US', {minimumFractionDigits: 4}));

        $('#val-ga-idr').text(ga_idr.toLocaleString('en-US', {minimumFractionDigits: 2}));
        $('#val-ga-usd').text(ga_usd.toLocaleString('en-US', {minimumFractionDigits: 4}));

        // 7. HITUNG PERSENTASE (%) UNTUK MASING-MASING BARIS & KATEGORI
        let ga_pct = (grand_idr > 0) ? (ga_idr / grand_idr) * 100 : 0;
        $('.pct-ga').text(ga_pct.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}) + '%');

        $('.row-costing').each(function() {
            let val_idr = parseFloat($(this).find('.val-idr-td').data('val')) || 0;
            let pct = (grand_idr > 0) ? (val_idr / grand_idr) * 100 : 0;
            $(this).find('.pct-td').text(pct.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}) + '%');
        });

        let pct_fab = (grand_idr > 0) ? (sum_fab_idr / grand_idr) * 100 : 0;
        let pct_sew = (grand_idr > 0) ? (sum_sew_idr / grand_idr) * 100 : 0;
        let pct_pack = (grand_idr > 0) ? (sum_pack_idr / grand_idr) * 100 : 0;
        let pct_mfg = (grand_idr > 0) ? (sum_mfg_idr / grand_idr) * 100 : 0;
        let pct_other = (grand_idr > 0) ? (tot_other_idr / grand_idr) * 100 : 0;

        $('#tot-fab-pct').text(pct_fab.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}) + '%');
        $('#tot-sew-pct').text(pct_sew.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}) + '%');
        $('#tot-pack-pct').text(pct_pack.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}) + '%');
        $('#tot-mfg-pct').text(pct_mfg.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}) + '%');
        $('#tot-other-pct').text(pct_other.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}) + '%');

        $('#tot-fab-val').text(sum_val_fab.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
        $('#tot-sew-val').text(sum_val_sew.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
        $('#tot-pack-val').text(sum_val_pack.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
        $('#tot-mfg-val').text(sum_val_mfg.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}));

        // TAMPILKAN ANGKA KE HTML
        $('#grand_total_value').text(grand_total_value.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
        $('#grand_total_top').text(grand_total_top.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
        $('#grand_total_bottom').text(grand_total_bottom.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}));

        // 8. HITUNG VAT & PROFIT
        let input_vat = parseFloat($('input[name="vat"]').val()) || 0;
        let shipment_type = $('#shipment_type').val();
        let actual_vat_pct = (shipment_type === 'export') ? 0 : input_vat;
        $('#vat-label-pct').text(actual_vat_pct + '%');

        let vat_multiplier = 1 + (actual_vat_pct / 100);
        let vat_idr = grand_idr * vat_multiplier;
        let vat_usd = grand_usd * vat_multiplier;
        let profit_idr = vat_idr * 1.06;
        let profit_usd = vat_usd * 1.06;

        $('#grand-tot-idr').text(grand_idr.toLocaleString('en-US', {minimumFractionDigits: 2}));
        $('#grand-tot-usd').text(grand_usd.toLocaleString('en-US', {minimumFractionDigits: 4}));
        $('#val-vat-idr').text(vat_idr.toLocaleString('en-US', {minimumFractionDigits: 2}));
        $('#val-vat-usd').text(vat_usd.toLocaleString('en-US', {minimumFractionDigits: 4}));
        $('#val-profit-idr').text(profit_idr.toLocaleString('en-US', {minimumFractionDigits: 2}));
        $('#val-profit-usd').text(profit_usd.toLocaleString('en-US', {minimumFractionDigits: 4}));

        check_type();
    }

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
            let image_exist = "{{ isset($costing->foto) && $costing->foto != '' ? 'yes' : 'no' }}";
            if(image_exist === 'no') {
                $('#img_preview').hide().attr('src', '');
                $('#no_img_text').show();
            } else {
                $('#img_preview').attr('src', "{{ isset($costing->foto) ? asset('uploads/costing/' . $costing->foto) : '' }}").show();
            }
        }
    });

    function check_type() {
        let set_type = $('#type').val();

        if (set_type === 'single') {
            $('#txt_set, #m_set').val('').trigger('change').prop('disabled', true);
        } else {
            $('#txt_set, #m_set').prop('disabled', false);
        }
    }

    $('#type').on('change', function() {
        check_type();
    });

    check_type();

    $('#txt_item, #txt_set').on('change', function() {
        let cat = $('#category').val();
        let selected_item = $('#txt_item').val();
        let selected_set = $('#txt_set').val() || '';
        if (cat && selected_item) {
            if (check_duplicate(cat, selected_item, selected_set)) {
                Swal.fire('Peringatan!', 'Item dan Set ini <b>sudah ada</b> di tabel. Silakan pilih yang berbeda.', 'error');
                $(this).val('').trigger('change');
            }
        }
    });

    $('#m_item, #m_set').on('change', function() {
        let cat = $('#m_category').val();
        let selected_item = $('#m_item').val();
        let selected_set = $('#m_set').val() || '';
        let current_row_id = 'row-' + $('#m_id_detail').val();

        if (cat && selected_item) {
            if (check_duplicate(cat, selected_item, selected_set, current_row_id)) {
                Swal.fire('Peringatan!', 'Item dan Set ini <b>sudah ada</b> di baris lain. Silakan pilih yang berbeda.', 'error');
                $(this).val('').trigger('change');
            }
        }
    });

    function check_type() {
        let jumlah = $('.row-costing').not('[data-category="Other Cost"]').length;

        if (jumlah > 0) {
            $('#type').prop('disabled', true);
        } else {
            $('#type').prop('disabled', false);
        }
    }
</script>
@endsection
