@extends('layouts.index')

@section('custom-link')
    @include('marketing.partials.nds_doc_style')
@endsection

@section('content')
@php
    $h = $doc['header'];
    $bd = $doc['breakdown'];
    $v = fn ($x) => $x === null || $x === '' ? '-' : $x;
    $n = fn ($x) => number_format((float) $x, 0, ',', '.');
    $one = ['line' => $line->id];
@endphp
<div class="ndoc">
    <div class="ndoc-card">
        <div class="ndoc-crumb"><a href="{{ route('home') }}">Home</a> &nbsp;›&nbsp; <a href="{{ route('dashboard-marketing') }}">Marketing</a> &nbsp;›&nbsp; <a href="{{ route('master-costing') }}">Process</a> &nbsp;›&nbsp; Bill of Material &nbsp;›&nbsp; <strong>Detail BOM</strong></div>
        <div class="ndoc-head">
            <div>
                <h1>Detail Bill of Material <i class="fas fa-info-circle"></i></h1>
                <p><i class="fas fa-database"></i> {{ $h['bom_no'] ? 'Untuk Melihat Detail Bill of Material' : 'BOM belum disimpan — isi di bawah masih isian awal dari Costing' }}</p>
            </div>
            <div class="ndoc-actions">
                @if ($editLineId)
                    <a href="{{ route('bom-nds-edit', $soId) }}?line={{ $editLineId }}" class="mkt-btn mkt-btn--navy mkt-btn--sm"><i class="fas fa-edit"></i> Edit</a>
                @endif
                <a href="{{ route('bom-nds-pdf', ['soId' => $soId] + $one) }}" target="_blank" class="mkt-btn mkt-btn--red mkt-btn--sm"><i class="fas fa-file-pdf"></i> PDF</a>
                <a href="{{ route('bom-nds-excel', ['soId' => $soId] + $one) }}" class="mkt-btn mkt-btn--green mkt-btn--sm"><i class="fas fa-file-excel"></i> Excel</a>
            </div>
        </div>

        @if ($lines->count() > 1)
            <div class="ndoc-ws">Worksheet:
                @foreach ($lines as $ws)
                    <a href="{{ route('bom-nds-detail', $soId) }}?line={{ $ws->id }}" class="mkt-btn mkt-btn--sm {{ $ws->id == $line->id ? 'mkt-btn--navy' : 'mkt-btn--gray' }}">{{ $ws->no_ws }} ({{ strtoupper(trim((string) $ws->product_set)) }})</a>
                @endforeach
            </div>
        @endif

        @if ($doc['stale']['changes'])
            <div class="ndoc-alert"><b><i class="fas fa-exclamation-triangle"></i> BOM perlu diperbarui:</b> Sales Order / Costing berubah setelah BOM disimpan. Buka Edit lalu Save.
                <ul>@foreach ($doc['stale']['changes'] as $change)<li>{{ $change }}</li>@endforeach</ul>
            </div>
        @endif

        <div class="ndoc-cards">
            <div><span>No BOM</span><strong>{{ $v($h['bom_no']) }}</strong></div>
            <div><span>No Worksheet</span><strong>{{ $v($h['no_ws']) }}</strong></div>
            <div><span>No Sales Order</span><strong>{{ $v($h['so_no']) }}</strong></div>
            <div><span>No Costing</span><strong>{{ $v($h['costing_no']) }}</strong></div>
        </div>

        <div class="ndoc-grid">
            <section class="ndoc-box">
                <h2>Information</h2>
                <div class="ndoc-fields">
                    <div><span>Buyer</span><strong>{{ $v($h['buyer']) }}</strong></div>
                    <div><span>Style</span><strong>{{ $v($h['style']) }}</strong></div>
                    <div><span>Brand / Season</span><strong>{{ $v($h['brand']) }} / {{ $v($h['season']) }}</strong></div>
                    <div><span>Product</span><strong>{{ $v(trim(($h['product_item'] ?? '') . ' ' . ($h['product_set'] ?? ''))) }}</strong></div>
                    <div><span>Ship Mode</span><strong>{{ $v($h['ship_mode']) }}</strong></div>
                    <div><span>Destination</span><strong>{{ $v($h['destinations']) }}</strong></div>
                    <div><span>Qty WS</span><strong>{{ $n($h['ws_qty']) }}</strong></div>
                    <div><span>Total Value</span><strong>{{ number_format($doc['total_value'], 2, ',', '.') }}</strong></div>
                </div>
            </section>
            <section class="ndoc-box">
                <h2>Breakdown Order</h2>
                <div class="ndoc-scroll">
                    <table class="ndoc-table ndoc-bd">
                        <thead><tr><th>No</th><th>Destination</th><th>Color</th>@foreach ($bd['sizes'] as $size)<th class="n">{{ $size['name'] }}</th>@endforeach<th class="n">Total</th></tr></thead>
                        <tbody>
                            {{-- Satu baris per destinasi x color (yang ada qty-nya). --}}
                            @php($col = array_fill(0, count($bd['sizes']), 0))
                            @php($no = 0)
                            @foreach ($bd['dests'] as $dest)
                                @foreach ($bd['colors'] as $color)
                                    @php($qty = $bd['qty'][$dest['id']][$color['id']] ?? [])
                                    @continue(!array_sum($qty))
                                    <tr><td>{{ ++$no }}</td><td>{{ $dest['name'] ?: '-' }}</td><td>{{ $color['name'] }}</td>
                                        @foreach ($bd['sizes'] as $i => $size)
                                            @php($q = (int) ($qty[$i] ?? 0))
                                            @php($col[$i] += $q)
                                            <td class="n">{{ $q ? $n($q) : '-' }}</td>
                                        @endforeach
                                        <td class="n t">{{ $n(array_sum($qty)) }}</td></tr>
                                @endforeach
                            @endforeach
                            <tr class="tot"><td colspan="3">TOTAL</td>@foreach ($col as $q)<td class="n">{{ $n($q) }}</td>@endforeach<td class="n">{{ $n(array_sum($col)) }}</td></tr>
                        </tbody>
                    </table>
                </div>
            </section>
        </div>

        @forelse ($doc['sections'] as $section)
            <h3 class="ndoc-title">{{ $section['label'] }}</h3>
            <div class="ndoc-scroll">@include('marketing.bom_nds._table', ['section' => $section, 'cls' => 'ndoc-table ndoc-center'])</div>
        @empty
            <p class="text-muted">Belum ada material atau charge di Costing untuk WS ini.</p>
        @endforelse
        <div class="ndoc-grand">TOTAL VALUE: {{ number_format($doc['total_value'], 2, ',', '.') }}</div>
    </div>
</div>
@endsection
