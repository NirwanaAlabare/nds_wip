@extends('layouts.index')

@section('custom-link')
    @include('marketing.partials.nds_doc_style')
@endsection

@section('content')
@php
    $h = $doc['header'];
    $v = fn ($x) => $x === null || $x === '' ? '-' : $x;
    $n = fn ($x) => number_format((float) $x, 0, ',', '.');
@endphp
<div class="ndoc">
    <div class="ndoc-crumb"><a href="{{ route('home') }}">Home</a> &nbsp;›&nbsp; <a href="{{ route('dashboard-marketing') }}">Marketing</a> &nbsp;›&nbsp; <a href="{{ route('master-costing') }}">Process</a> &nbsp;›&nbsp; Sales Order &nbsp;›&nbsp; <strong>Detail SO</strong></div>
    <div class="ndoc-card">
        <div class="ndoc-head">
            <div>
                <h1>Detail Sales Order</h1>
                <p>{{ $h['so_no'] }}</p>
            </div>
            <div class="ndoc-actions">
                {{-- SO tanpa approval: bebas diedit, kecuali WS-nya sudah dipakai di Marker (pop-up hubungi IT). --}}
                @if ($markerMessage)
                    <button type="button" class="mkt-btn mkt-btn--navy mkt-btn--sm" id="so-edit-marker"><i class="fas fa-edit"></i> Edit</button>
                @else
                    <a href="{{ route('so-nds-edit', $doc['so']->id) }}" class="mkt-btn mkt-btn--navy mkt-btn--sm"><i class="fas fa-edit"></i> Edit</a>
                @endif
                <a href="{{ route('so-nds-pdf', $doc['so']->id) }}" target="_blank" class="mkt-btn mkt-btn--red mkt-btn--sm"><i class="fas fa-file-pdf"></i> PDF</a>
                <a href="{{ route('so-nds-excel', $doc['so']->id) }}" class="mkt-btn mkt-btn--green mkt-btn--sm"><i class="fas fa-file-excel"></i> Excel</a>
            </div>
        </div>

        <div id="so-lock-banner"></div>
        <div class="ndoc-cards">
            <div><span>No Sales Order</span><strong>{{ $v($h['so_no']) }}</strong></div>
            <div><span>No Costing</span><strong>{{ $v($h['costing_no']) }}</strong></div>
            <div><span>SO Date</span><strong>{{ $v($h['so_date']) }}</strong></div>
            <div><span>Delivery Date</span><strong title="{{ $h['delivery'] }}">{{ $v($h['delivery']) }}</strong></div>
            <div><span>Total Qty</span><strong>{{ $n($h['total_qty']) }}</strong></div>
        </div>

        <div class="ndoc-grid">
            <section class="ndoc-box">
                <h2>Information Buyer</h2>
                <div class="ndoc-fields">
                    <div><span>Buyer</span><strong>{{ $v($h['buyer']) }}</strong></div>
                    <div><span>Brand</span><strong>{{ $v($h['brand']) }}</strong></div>
                    <div><span>Season</span><strong>{{ $v($h['season']) }}</strong></div>
                    <div><span>Style</span><strong>{{ $v($h['style']) }}</strong></div>
                    <div><span>Currency</span><strong>{{ $v($h['currency']) }}</strong></div>
                    <div><span>Payment Terms</span><strong>{{ $v($h['payment']) }}</strong></div>
                </div>
            </section>
            <section class="ndoc-box">
                <h2>Shipping &amp; Worksheet</h2>
                <div class="ndoc-fields">
                    <div><span>Worksheet</span><strong>{{ $v($h['worksheets']) }}</strong></div>
                    <div><span>Ship Mode</span><strong>{{ $v($h['ship_mode']) }}</strong></div>
                    <div><span>Destination</span><strong>{{ $v($h['destinations']) }}</strong></div>
                    <div><span>Buyer PO</span><strong>{{ $v($h['po']) }}</strong></div>
                    <div style="grid-column: 1 / -1"><span>Notes</span><strong>{{ $v($h['notes']) }}</strong></div>
                </div>
            </section>
        </div>

        <h3 class="ndoc-title">Breakdown Order</h3>
        @forelse ($doc['worksheets'] as $ws)
            <section class="ndoc-box" style="margin-bottom: 14px">
                <h2>{{ $ws['no_ws'] }}{{ $ws['item'] ? ' · ' . $ws['item'] : '' }}</h2>
                <div class="ndoc-scroll">
                    <table class="ndoc-table">
                        <thead><tr><th>No</th><th>Color</th>@foreach ($ws['sizes'] as $size)<th class="n">{{ $size }}</th>@endforeach<th class="n">Total</th></tr></thead>
                        <tbody>
                            @foreach ($ws['colors'] as $i => $color)
                                <tr><td>{{ $i + 1 }}</td><td>{{ $color['name'] }}</td>@foreach ($color['qty'] as $q)<td class="n">{{ $q ? $n($q) : '-' }}</td>@endforeach<td class="n t">{{ $n($color['total']) }}</td></tr>
                            @endforeach
                            <tr class="tot"><td colspan="2">TOTAL</td>@foreach ($ws['size_totals'] as $q)<td class="n">{{ $n($q) }}</td>@endforeach<td class="n">{{ $n($ws['total']) }}</td></tr>
                        </tbody>
                    </table>
                </div>
            </section>
        @empty
            <p class="text-muted">Belum ada detail di Sales Order ini.</p>
        @endforelse

        <h3 class="ndoc-title">Sales Order Detail</h3>
        <div class="ndoc-scroll">
            <table class="ndoc-table">
                <thead><tr><th>No</th><th>Worksheet</th><th>Buyer PO</th><th>PO Date</th><th>Ex FTY</th><th>Ship Mode</th><th>Destination</th><th>Color</th><th>Size</th><th>SKU</th><th>Barcode</th><th class="n">Qty</th></tr></thead>
                <tbody>
                    @foreach ($doc['rows'] as $i => $r)
                        <tr>
                            <td>{{ $i + 1 }}</td><td>{{ $r['no_ws'] }}</td><td>{{ $v($r['po']) }}</td><td>{{ $v($r['po_date']) }}</td>
                            <td>{{ $v($r['ex_fty']) }}</td><td>{{ $v($r['ship_mode']) }}</td><td>{{ $v($r['destination']) }}</td><td>{{ $r['color'] }}</td><td>{{ $r['size'] }}</td>
                            <td>{{ $v($r['sku']) }}</td><td>{{ $v($r['barcode']) }}</td><td class="n">{{ $n($r['qty']) }}</td>
                        </tr>
                    @endforeach
                    <tr class="tot"><td colspan="11">TOTAL</td><td class="n">{{ $n($h['total_qty']) }}</td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@section('custom-script')
<style>
    #so-lock-banner:not(:empty) { margin-bottom: 14px; }
</style>
<script>
    $(function () {
        // Dibuka dari Edit SO yang WS-nya sudah dipakai Marker (redirect + pesan).
        const warning = @json(session('warning'));
        if (warning) {
            mktBanner('#so-lock-banner', 'warn', 'Sales Order sudah ada di Marker', warning);
        }
        $('#so-edit-marker').on('click', function () {
            Swal.fire({ icon: 'warning', title: 'Sales Order sudah ada di Marker', text: @json($markerMessage), confirmButtonText: 'Tutup' });
        });
    });
</script>
@endsection
