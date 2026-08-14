<div class="line-map-temp-card">
    <div class="line-map-temp-title">
        <i class="fas fa-inbox"></i> Area Temporary
        <span class="text-muted fw-normal">(maks {{ $tempHoldingCapacity }} plan, belum ditugaskan ke line &amp; tanggal)</span>
    </div>
    <div class="line-map-temp-slots">
        @for ($i = 0; $i < $tempHoldingCapacity; $i++)
            @php($entry = $tempHolding->get($i))
            <div class="line-map-temp-slot @if (!$entry && $canEditLineMap) line-map-temp-dropzone @endif"
                @if (!$entry && $canEditLineMap) data-temp-slot="{{ $i }}" @endif>
                @if ($entry)
                    <div class="line-map-box line-map-box-plan @if (!$canEditLineMap) line-map-box-plan-readonly @endif"
                        draggable="{{ $canEditLineMap ? 'true' : 'false' }}"
                        style="--dot-color: {{ $entry->style_color }}; --font-color: {{ $entry->font_color }};"
                        data-id="{{ $entry->id }}" data-line="" data-date="" data-style="{{ $entry->style }}"
                        data-product-group="{{ $entry->product_group }}"
                        title="{{ $entry->style }} - {{ $entry->buyer }}"
                        @if ($canEditLineMap)
                            data-bs-toggle="modal" data-bs-target="#newLineMapModal"
                            onclick='openEditLineMap(@json($entry->edit_payload))'
                        @endif>
                        <div class="line-map-box-header">
                            <span class="box-buyer">{{ $entry->buyer ?: '-' }}</span>
                            <span>Plan</span>
                        </div>
                        <div class="line-map-box-row">
                            <span class="row-label">{{ $entry->style }}</span>
                            <span class="row-qty">{{ number_format($entry->qty_order, 0, ',', '.') }}</span>
                        </div>
                        @if ($entry->product_group)
                            <div class="line-map-box-row">
                                <span class="row-label fst-italic">{{ $entry->product_group }}</span>
                            </div>
                        @endif
                    </div>
                @else
                    <div class="line-map-temp-empty text-muted">
                        <i class="fas fa-arrow-down"></i> Drop di sini
                    </div>
                @endif
            </div>
        @endfor
    </div>
</div>
