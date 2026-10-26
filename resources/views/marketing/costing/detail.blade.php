@extends('layouts.index')

@section('custom-link')
    <link rel="stylesheet" href="{{ asset('dist/css/marketing-buttons.css') }}?v={{ @filemtime(public_path('assets/dist/css/marketing-buttons.css')) }}">
    <style>
        .costing-detail-page { min-height: calc(100vh - 56px); padding: 10px 14px 24px; background: #f5f5f5; color: #263746; font-family: 'Poppins', sans-serif; }
        .costing-detail-card { margin: 0 auto; border: 0; border-radius: 10px; box-shadow: 0 5px 12px -3px rgba(30, 41, 59, .18), 0 2px 4px rgba(30, 41, 59, .07); }
        .costing-detail-card > .card-body { padding: 20px 24px 28px; }
        .costing-crumb { margin-bottom: 14px; padding: 8px 10px; background: #284A63; color: #b4b4b4; font-size: 16px; font-weight: 500; }
        .costing-crumb a { color: #b4b4b4; text-decoration: none; }
        .costing-crumb a:hover { color: #fff; text-decoration: underline; }
        .costing-crumb b { color: #fff; font-weight: 500; }
        .costing-detail-heading { display: flex; flex-wrap: wrap; align-items: flex-start; justify-content: space-between; gap: 12px; margin-bottom: 14px; }
        .costing-detail-title { margin: 0; color: #284a63; font-size: 32px; font-weight: 700; }
        .costing-detail-title i { color: #64748b; font-size: 12px; vertical-align: super; }
        .costing-detail-subtitle { margin: 4px 0 0; color: #284a63; font-size: 12px; font-weight: 600; }
        .costing-detail-subtitle i { margin-right: 4px; color: #a3b2c7; }
        .costing-detail-actions { display: flex; flex-wrap: wrap; gap: 10px; }
        .costing-detail-alert { display: flex; align-items: center; gap: 14px; margin-bottom: 16px; padding: 8px 12px; border-radius: 4px; background: #ff5f5f; color: #fff; font-size: 14px; }
        .costing-detail-alert > span { flex: 1; }
        .costing-detail-alert > span i { margin-right: 6px; }
        .costing-detail-alert form { margin: 0; }

        .costing-detail-summary { display: grid; grid-template-columns: repeat(6, minmax(0, 1fr)); gap: 12px; margin-bottom: 16px; }
        .costing-detail-stat { min-width: 0; min-height: 76px; padding: 10px 14px; border-radius: 10px; background: #6c8798; color: #fff; box-shadow: 0 3px 8px rgba(30, 41, 59, .10); }
        .costing-detail-stat span { display: block; margin-bottom: 4px; color: #e7eef2; font-family: 'Poppins', sans-serif; font-size: 20px; font-weight: 500; letter-spacing: 0.2px; line-height: 1.25; }
        .costing-detail-stat strong { display: block; overflow: hidden; color: #fff; font-family: 'Poppins', sans-serif; font-size: 24px; font-weight: 700; letter-spacing: 0.05em; line-height: 1.25; text-overflow: ellipsis; white-space: nowrap; }
        .costing-detail-stat:nth-child(n+4) strong { text-align: center; }
        .costing-detail-stat.is-light { border: 1px solid #e1e7ef; background: #fff; }
        .costing-detail-stat.is-light span { color: #9e9e9e; }
        .costing-detail-stat.is-light strong { color: #284a63; }

        .costing-detail-overview { display: grid; grid-template-columns: minmax(0, 1fr) minmax(0, 1fr); gap: 16px; }
        .costing-detail-overview-left { display: grid; gap: 16px; align-content: start; }
        .costing-detail-panel { min-width: 0; border: 1px solid #e1e7ef; border-radius: 10px; background: #fff; box-shadow: 0 3px 8px rgba(30, 41, 59, .08); overflow: hidden; }
        .costing-detail-panel h2 { margin: 0 12px; padding: 9px 0 7px; border-bottom: 1px solid #dce4ec; color: #616161; font-family: 'Poppins', sans-serif; font-size: 16px; font-weight: 700; letter-spacing: 0.05em; }
        .costing-detail-fields { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 0 18px; padding: 4px 12px 10px; }
        .costing-detail-field { display: flex; min-width: 0; flex-direction: column; justify-content: center; gap: 2px; padding: 6px 0; border-bottom: 1px solid #e7e7e7; }
        .costing-detail-field span { color: #9e9e9e; font-family: 'Poppins', sans-serif; font-size: 18px; font-weight: 500; letter-spacing: 0.2px; }
        .costing-detail-field strong { min-width: 0; overflow-wrap: anywhere; color: #284a63; font-family: 'Poppins', sans-serif; font-size: 18px; font-weight: 700; letter-spacing: 0.05em; }
        .costing-detail-gallery-panel { min-height: 100%; }
        .costing-detail-gallery { display: flex; min-height: 320px; flex-direction: column; justify-content: center; padding: 12px 16px; }
        /* Gambar aktif 182x256, samping 90x192 (sejajar tengah, celah 59px), sama dengan carousel halaman lain. */
        .costing-detail-image-stage { display: flex; height: 288px; align-items: center; justify-content: center; gap: 14px; }
        .costing-detail-image { display: block; width: 182px; max-width: 46%; height: 256px; object-fit: contain; }
        .costing-detail-peek { display: block; width: 90px; max-width: 22%; height: 192px; margin-left: 45px; object-fit: contain; cursor: pointer; }
        .costing-detail-dots { display: flex; justify-content: center; gap: 6px; margin-top: 10px; }
        .costing-detail-dots button { width: 8px; height: 6px; padding: 0; border: 0; border-radius: 3px; background: #d6dde5; transition: width .15s ease; }
        .costing-detail-dots button.active { width: 22px; background: #1d6fe0; }
        .costing-detail-image[hidden] { display: none; }
        .costing-detail-image-nav { flex: 0 0 auto; width: 34px; height: 34px; padding: 0; border: 0; border-radius: 50%; background: #f1f3f5; color: #7b8791; font-size: 13px; }
        .costing-detail-thumbs { display: flex; flex-wrap: wrap; justify-content: center; gap: 10px; margin-top: 12px; }
        .costing-detail-thumb { width: 76px; height: 97px; padding: 3px; border: 1px solid #dce4ec; border-radius: 6px; background: #fff; cursor: pointer; }
        .costing-detail-thumb.active { border: 2px solid #5287f5; }
        .costing-detail-thumb img { width: 100%; height: 100%; object-fit: contain; }
        .costing-detail-image-count { margin-top: 6px; color: #9aa5ad; font-size: 12px; text-align: center; }
        .costing-detail-no-image { display: grid; min-height: 280px; place-items: center; color: #94a3b8; font-size: 14px; }
        .costing-detail-lower-overview { display: grid; grid-template-columns: minmax(0, 1fr) minmax(0, 1fr); gap: 16px; margin-top: 16px; }
        .costing-detail-lower-right { display: grid; grid-template-columns: minmax(0, 1fr) minmax(0, 1.6fr); gap: 16px; align-content: start; }
        .costing-detail-lower-right .costing-detail-fields { grid-template-columns: 1fr; }
        .costing-detail-lower-right .is-price .costing-detail-fields { grid-template-columns: repeat(2, minmax(0, 1fr)); }

        .costing-detail-category { margin-top: 18px; }
        .costing-detail-category-toggle { display: flex; width: 100%; align-items: center; justify-content: space-between; gap: 8px; margin: 0 0 8px; padding: 6px 12px; border: 1px solid #94a3b8; border-radius: 6px; background: #fff; color: #284a63; text-align: left; font-family: 'Poppins', sans-serif; font-size: 25px; font-weight: 700; }
        .costing-detail-category-toggle:focus { outline: 2px solid #8caec2; outline-offset: -2px; }
        .costing-detail-category-toggle i { display: grid; flex: 0 0 auto; width: 20px; height: 20px; place-items: center; border-radius: 4px; background: #a3b2c7; color: #fff; font-size: 10px; transition: transform .15s ease; }
        .costing-detail-category-toggle[aria-expanded="false"] i { transform: rotate(180deg); }
        .costing-detail-category-body { padding: 10px 12px 4px; border-radius: 6px; background: #fff; box-shadow: 0 3px 10px rgba(15, 23, 42, .10); }
        .costing-detail-items-count { margin: 0 0 6px; color: #8c989f; font-family: 'Poppins', sans-serif; font-size: 12px; }
        .costing-detail-table-wrap { width: 100%; overflow-x: auto; -webkit-overflow-scrolling: touch; }
        .costing-detail-table-wrap::-webkit-scrollbar { height: 8px; }
        .costing-detail-table-wrap::-webkit-scrollbar-track { background: #e2e8f0; border-radius: 4px; }
        .costing-detail-table-wrap::-webkit-scrollbar-thumb { background: #284a63; border-radius: 4px; }
        .costing-detail-table { width: 100%; min-width: 1400px; margin: 0; font-family: 'Poppins', sans-serif; font-size: 13px; }
        .costing-detail-table th { padding: 10px 8px; border: 0; background: #cfe2ff; color: #1e293b; font-family: 'Poppins', sans-serif; font-size: 12px !important; font-weight: 500; letter-spacing: 0.03em; text-align: center; text-transform: uppercase; vertical-align: middle; white-space: nowrap; }
        .costing-detail-table tbody td { padding: 9px 8px; border: 0; border-bottom: 1px solid #edf1f5; color: #1e293b; font-family: 'Poppins', sans-serif; font-size: 13px !important; font-weight: 500; letter-spacing: 0; vertical-align: middle; white-space: nowrap; }
        .costing-detail-table tfoot td { height: 36px; border: 0; background: #ffd6dc; color: #1e293b; font-size: 14px !important; font-weight: 700; }
        .costing-detail-table .empty-row td { padding: 16px; color: #8391a0; text-align: center; }
        .costing-detail-table .row-check { width: 14px; height: 14px; margin: 0; vertical-align: middle; }
        .costing-detail-other { min-width: 900px; }
        .costing-detail-pagebar { display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 10px; padding: 12px 4px; color: #1e293b; font-family: 'Poppins', sans-serif; font-size: 12px; }
        .costing-detail-page-size { display: inline-flex; align-items: center; gap: 10px; margin: 0; font-weight: 400; }
        .costing-detail-page-size select { height: 32px; min-width: 64px; padding: 0 8px; border: 1px solid #94a3b8; border-radius: 6px; color: #1e293b; background: #fff; font-size: 13px; }
        .costing-detail-pages { display: inline-flex; flex-wrap: wrap; align-items: center; gap: 6px; }
        .costing-detail-pages button { min-width: 28px; height: 28px; padding: 0 5px; border: 0; border-radius: 4px; background: transparent; color: #64748b; font-size: 13px; }
        .costing-detail-pages button[aria-current="page"] { background: #274b63; color: #fff; font-weight: 700; }
        .costing-detail-pages button:disabled { opacity: .35; }
        .costing-detail-auto { display: flex; align-items: center; gap: 6px; margin: 8px 0 0; color: #75838c; font-family: 'Poppins', sans-serif; font-size: 12px; }
        .costing-detail-auto::before { content: ''; width: 12px; height: 12px; border: 1px solid #e2e8f0; border-radius: 2px; background: #f1f4f8; }
        .costing-detail-summary-wrap { display: flex; justify-content: flex-end; margin-top: 28px; }
        .costing-detail-summary-card { width: min(100%, 520px); overflow-x: auto; border-radius: 6px; background: #fff; box-shadow: 0 3px 14px rgba(30, 41, 59, .13); }
        .costing-detail-summary-table { width: 100%; border-collapse: collapse; font-family: 'Poppins', sans-serif; font-size: 14px; }
        .costing-detail-summary-table th { padding: 10px 12px; background: #274b63; color: #fff; white-space: nowrap; font-size: 13px !important; font-weight: 600; text-align: right; }
        .costing-detail-summary-table th:first-child { text-align: left; }
        .costing-detail-summary-table td { padding: 9px 12px; font-size: 14px !important; border: 0; background: #fff; color: #17212b; font-weight: 700; text-align: right; white-space: nowrap; }
        .costing-detail-summary-table td:first-child { min-width: 150px; text-align: left; white-space: normal; }
        .costing-detail-summary-table td:first-child span { float: right; margin-left: 8px; }
        .costing-detail-summary-table tbody tr:nth-child(2) td { background: #fff7d9; }
        .costing-detail-summary-table tbody tr:nth-child(3) td { background: #ffe6b3; }
        .costing-detail-summary-table tbody tr:nth-child(4) td { background: #f26b6b; }

        /* ===== Responsif (skala sama dengan Detail SO) ===== */
        @media (max-width: 1599.98px) {
            .costing-detail-stat span { font-size: 16px; }
            .costing-detail-stat strong { font-size: 18px; }
        }
        @media (max-width: 1199.98px) {
            .costing-detail-summary { grid-template-columns: repeat(3, minmax(0, 1fr)); }
            .costing-detail-stat span { font-size: 16px; }
            .costing-detail-stat strong { font-size: 18px; }
            .costing-detail-field span, .costing-detail-field strong { font-size: 16px; }
        }
        @media (max-width: 991.98px) {
            .costing-detail-title { font-size: 28px; }
            .costing-detail-stat { min-height: 0; }
            .costing-detail-stat span { font-size: 14px; }
            .costing-detail-stat strong { font-size: 18px; white-space: normal; overflow-wrap: anywhere; }
            .costing-detail-overview, .costing-detail-lower-overview { grid-template-columns: minmax(0, 1fr); }
            .costing-detail-gallery-panel { min-height: 0; }
            .costing-detail-gallery { min-height: 0; }
            .costing-detail-image-stage { height: 220px; }
            .costing-detail-image { max-height: 210px; }
            .costing-detail-field span { font-size: 14px; }
            .costing-detail-field strong { font-size: 15px; }
        }
        @media (max-width: 767.98px) {
            .costing-detail-page { padding: 6px 6px 18px; }
            .costing-detail-card > .card-body { padding: 14px 12px 20px; }
            .costing-detail-title { font-size: 26px; }
            .costing-detail-alert { flex-wrap: wrap; }
            .costing-detail-alert > span { flex-basis: 100%; }
            .costing-detail-pagebar { justify-content: center; }
        }
        @media (max-width: 575.98px) {
            .costing-detail-title { font-size: 24px; }
            .costing-crumb { font-size: 13px; }
            .costing-detail-summary { grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 8px; }
            .costing-detail-stat { padding: 8px 10px; }
            .costing-detail-stat span { font-size: 13px; }
            .costing-detail-stat strong { font-size: 16px; }
            .costing-detail-field span { font-size: 13px; }
            .costing-detail-field strong { font-size: 14px; }
            .costing-detail-image-stage { height: 180px; }
            .costing-detail-image { max-height: 170px; }
            .costing-detail-thumb { width: 60px; height: 77px; }
            .costing-detail-summary-wrap { margin-top: 18px; }
            .costing-detail-summary-card { width: 100%; }
            .costing-detail-summary-table th { padding: 8px 4px; font-size: 11px !important; }
            .costing-detail-summary-table td { padding: 8px 4px; font-size: 12px !important; }
            .costing-detail-summary-table td:first-child { min-width: 72px; padding-left: 8px; }
            .costing-detail-summary-table td:first-child span { float: none; display: block; margin: 0; }
            .costing-detail-lower-right .costing-detail-fields { padding: 4px 10px 8px; }
            .costing-detail-lower-right { grid-template-columns: repeat(2, minmax(0, 1fr)); }
            .costing-detail-lower-right .is-price .costing-detail-fields { grid-template-columns: 1fr; }
            .costing-detail-peek { max-height: 90px; }
        }
        @media (max-width: 399.98px) {
            .costing-detail-summary-table td { font-size: 11px !important; }
        }
        @media (max-width: 359.98px) {
            .costing-detail-summary, .costing-detail-fields { grid-template-columns: minmax(0, 1fr); }
        }
        /* QA: semua teks Poppins (tombol juga), legend "Auto" di atas tabel, titik abu di judul kolom (mode lihat). */
        .costing-detail-page .mkt-btn, .costing-detail-page .mkt-icon-btn { font-family: 'Poppins', sans-serif; font-weight: 600; }
        .costing-detail-legend-top { display: flex; align-items: center; gap: 5px; margin: 0 0 8px; color: #64748b; font-family: 'Poppins', sans-serif; font-size: 11px; font-weight: 500; }
        .costing-detail-dot { display: inline-block; width: 6px; height: 6px; border-radius: 50%; background: #94a3b8; }
        .costing-detail-table thead th + th::before { content: ''; display: inline-block; width: 5px; height: 5px; margin: 0 5px 1px 0; border-radius: 50%; background: #94a3b8; vertical-align: middle; }
        .costing-detail-table tfoot td { font-weight: 600 !important; }
        /* QA ketebalan: teks biasa Medium 500 (sebelumnya campur Regular 400 / Bold 700). */
        .costing-detail-alert > span, .costing-detail-image-count, .costing-detail-auto, .costing-detail-pages button, .costing-detail-page-size span { font-weight: 500; }
        .costing-detail-page .costing-detail-page-size { font-weight: 500 !important; }
        .costing-detail-pages button[aria-current="page"] { font-weight: 600; }
    </style>
@endsection

@section('content')
@php
    $unlockState = $unlock['state'] ?? '';
    $requestedAtValue = $unlock['request']->dateinput ?? null;
    $requestedAt = $requestedAtValue ? \Carbon\Carbon::parse($requestedAtValue) : null;
    $unlockedAtValue = $unlock['unlock']->approved_at ?? $unlock['unlock']->dateinput ?? null;
    $unlockedAt = $unlockedAtValue ? \Carbon\Carbon::parse($unlockedAtValue) : null;
    $savedProductSets = array_filter(array_map('trim', explode(',', (string) ($costing->product_set ?? ''))));
    $productSetNames = collect($master_set)->filter(function ($set) use ($savedProductSets) {
        return in_array((string) $set->id, $savedProductSets, true);
    })->pluck('nama')->filter()->implode(', ');
    $season = collect($seasons)->firstWhere('id_season', $costing->season_id);
    $currency = collect($currencies)->firstWhere('id', $costing->curr);
    $shipMode = collect($shipmodes)->firstWhere('id', $costing->ship_mode);
    $productSets = collect($master_set)->keyBy('id');
    $rateToIdr = (float) ($costing->rate_to_idr ?? 0);
    $orderQty = (float) ($costing->qty ?? 0);
    $costingTotals = ['idr' => 0, 'usd' => 0, 'bom' => 0, 'value' => 0];
    $otherTotals = ['idr' => 0, 'usd' => 0];
    $overheadAllowance = 0;
    foreach (['Fabric', 'Accessories Sewing', 'Accessories Packing', 'Manufacturing'] as $categoryName) {
        foreach (($details[$categoryName] ?? collect()) as $detailRow) {
            $allowance = max(0, (float) ($detailRow->allowance ?? 0));
            $bomQty = (int) ceil((1 + ($allowance / 100)) * $orderQty * (float) ($detailRow->cons ?? 0));
            $costingTotals['idr'] += (float) ($detailRow->value_idr ?? 0);
            $costingTotals['usd'] += (float) ($detailRow->value_usd ?? 0);
            $costingTotals['bom'] += $bomQty;
            $costingTotals['value'] += $bomQty * (float) ($detailRow->price_px_idr ?? 0);
        }
    }
    foreach (($details['Other Cost'] ?? collect()) as $detailRow) {
        $otherTotals['idr'] += (float) ($detailRow->value_idr ?? 0);
        $otherTotals['usd'] += $rateToIdr > 0 ? (float) ($detailRow->value_idr ?? 0) / $rateToIdr : 0;
        if (str_contains(strtoupper((string) ($detailRow->nama_item ?? '')), 'OVERHEAD')) {
            $overheadAllowance = (float) ($detailRow->allowance ?? 0);
        }
    }
    $gaPercent = (float) ($costing->ga_percent ?? 3);
    $baseCostIdr = $costingTotals['idr'] + $otherTotals['idr'];
    $gaIdr = $baseCostIdr * ($gaPercent / 100);
    $totalCostIdr = $baseCostIdr + $gaIdr;
    $totalCostUsd = $rateToIdr > 0 ? $totalCostIdr / $rateToIdr : 0;
    $confirmPrice = (float) ($costing->confirm_price ?? 0);
    $confirmPriceIdr = strtoupper((string) ($currency->nama_pilihan ?? '')) === 'USD' ? $confirmPrice * $rateToIdr : $confirmPrice;
    $balanceOtherIdr = $confirmPrice !== 0
        ? ($confirmPriceIdr - $totalCostIdr) / ((1 + ($overheadAllowance / 100)) * 1.03)
        : 0;
    $balanceOtherUsd = $rateToIdr > 0 ? $balanceOtherIdr / $rateToIdr : 0;
    $vatPercent = strtolower((string) ($costing->shipment_type ?? '')) === 'export' ? 0 : (float) ($costing->vat ?? 0);
    $vatIdr = round($totalCostIdr * (1 + ($vatPercent / 100)), 2);
    $vatUsd = $rateToIdr > 0 ? round(($totalCostUsd * (1 + ($vatPercent / 100))) * 10000000) / 10000000 : 0;
    $profitIdr = round($vatIdr * 1.06, 2);
    $profitUsd = round(($vatUsd * 1.06) * 10000000) / 10000000;
    $pictures = collect($pictures);
    $summaryCards = [
        ['No Worksheet', $costing->kpno ?: '-'],
        ['No Costing', $costing->no_costing ?: '-'],
        ['DELIVERY DATE', $costing->deldate ? \Carbon\Carbon::parse($costing->deldate)->format('d/m/Y') : '-'],
        ['Qty Planned', $costing->qty_planned?: 0],
        ['SMV (MINUTES)', number_format((float) ($costing->smv ?? 0), 0, ',', '.')],
        ['CONFIRM PRICE', number_format($confirmPrice, 2, ',', '.')],
    ];
@endphp
<div class="costing-detail-page">
    <div class="card costing-detail-card">
        <div class="card-body">
            <div class="costing-crumb"><a href="{{ route('master-costing') }}">Marketing &nbsp;›&nbsp; Proses &nbsp;›&nbsp; Costing</a> &nbsp;›&nbsp; <b>Detail Costing</b></div>
            <div class="costing-detail-heading">
                <div>
                    <h1 class="costing-detail-title">Detail Costing <i class="fas fa-info-circle"></i></h1>
                    <p class="costing-detail-subtitle"><i class="fas fa-database"></i> Untuk Melihat Detail Costing</p>
                </div>
                {{-- <div class="costing-detail-actions">
                    @if ($unlock['can_edit'] ?? false)
                    <a href="{{ route('edit-costing', $costing->id) }}" class="mkt-btn mkt-btn--navy mkt-btn--sm"><i class="fas fa-pen"></i> Edit Costing</a>
                    @endif
                    <a href="{{ route('master-costing') }}" class="mkt-btn mkt-btn--gray mkt-btn--sm">Kembali</a>
                </div> --}}
            </div>

            @if ($unlockState === 'locked')
            <div class="costing-detail-alert">
                <span><i class="fas fa-lock"></i> This document is approved and can't be edited. Request an unlock from the marketing manager if you need to change it.</span>
                <button type="button" id="btn-open-unlock" class="mkt-btn mkt-btn--navy mkt-btn--sm">Request Unlock</button>
            </div>
            @elseif ($unlockState === 'requested')
            <div class="costing-detail-alert">
                <span><i class="far fa-clock"></i> Request unlock sent{{ $requestedAt ? ' on '.$requestedAt->format('d M Y, H:i') : '' }}.</span>
                <form method="POST" action="{{ route('costing-cancel-unlock-request', $costing->id) }}">
                    @csrf
                    <button type="submit" class="mkt-btn mkt-btn--red mkt-btn--sm">Cancel Request</button>
                </form>
            </div>
            @elseif ($unlockState === 'unlocked')
            <div class="costing-detail-alert">
                <span><i class="fas fa-lock-open"></i> Costing is unlocked{{ $unlockedAt ? ' since '.$unlockedAt->format('d M Y, H:i') : '' }}.</span>
            </div>
            @endif

            <div class="costing-detail-summary">
                @foreach ($summaryCards as [$label, $value])
                <div class="costing-detail-stat{{ $loop->index < 3 ? ' is-light' : '' }}"><span>{{ $label }}</span><strong>{{ $value }}</strong></div>
                @endforeach
            </div>

            <div class="costing-detail-overview">
                <div class="costing-detail-overview-left">
                    <section class="costing-detail-panel">
                        <h2>Information Buyer</h2>
                        <div class="costing-detail-fields">
                            <div class="costing-detail-field"><span>Buyer Name</span><strong class="mkt-tc">{{ $buyerName ?: '-' }}</strong></div>
                            <div class="costing-detail-field"><span>Kode Buyer</span><strong>{{ $costing->buyer_code ?: '-' }}</strong></div>
                            <div class="costing-detail-field"><span>Brand</span><strong class="mkt-tc">{{ $costing->brand ?: '-' }}</strong></div>
                            <div class="costing-detail-field"><span>Season</span><strong class="mkt-tc">{{ $season ? trim($season->season.' '.$season->season_desc) : '-' }}</strong></div>
                            <div class="costing-detail-field"><span>Style</span><strong>{{ $costing->style ?: '-' }}</strong></div>
                        </div>
                    </section>
                    <section class="costing-detail-panel">
                        <h2>Product &amp; Type</h2>
                        <div class="costing-detail-fields">
                            <div class="costing-detail-field"><span>Product Group</span><strong class="mkt-tc">{{ $costing->product_group ?: '-' }}</strong></div>
                            <div class="costing-detail-field"><span>Product Item</span><strong class="mkt-tc">{{ $costing->product_item_name ?: '-' }}</strong></div>
                            <div class="costing-detail-field"><span>Product Type</span><strong>{{ $productSetNames ?: '-' }}</strong></div>
                            <div class="costing-detail-field"><span>Costing Type</span><strong>{{ strtoupper($costing->type ?: '-') }}</strong></div>
                            <div class="costing-detail-field"><span>Unit</span><strong>{{ $costing->unit ?: '-' }}</strong></div>
                            <div class="costing-detail-field"><span>Worksheet Type</span><strong>{{ $costing->tipe_ws ?: '-' }}</strong></div>
                        </div>
                    </section>
                </div>
                <section class="costing-detail-panel costing-detail-gallery-panel">
                    <h2>Review Image</h2>
                    <div class="costing-detail-gallery">
                        @if ($pictures->isNotEmpty())
                            <div class="costing-detail-image-stage">
                                @if ($pictures->count() > 1)
                                <button type="button" class="costing-detail-image-nav" data-image-step="-1" aria-label="Gambar sebelumnya"><i class="fas fa-chevron-left"></i></button>
                                @endif
                                @foreach ($pictures as $picture)
                                <img class="costing-detail-image" src="/nds_wip/public/uploads/costing/{{ rawurlencode($picture->file_name) }}" alt="{{ $picture->original_name ?? $picture->file_name }}" @if(!$loop->first) hidden @endif>
                                @endforeach
                                @if ($pictures->count() > 1)
                                <img class="costing-detail-peek" src="/nds_wip/public/uploads/costing/{{ rawurlencode($pictures[1]->file_name) }}" alt="" aria-hidden="true">
                                <button type="button" class="costing-detail-image-nav" data-image-step="1" aria-label="Gambar berikutnya"><i class="fas fa-chevron-right"></i></button>
                                @endif
                            </div>
                            @if ($pictures->count() > 1)
                            <div class="costing-detail-dots">
                                @foreach ($pictures as $picture)
                                <button type="button" class="{{ $loop->first ? 'active' : '' }}" data-image-index="{{ $loop->index }}" aria-label="Gambar {{ $loop->iteration }}"></button>
                                @endforeach
                            </div>
                            @endif
                            @if ($pictures->count() > 1)
                            <div class="costing-detail-thumbs">
                                @foreach ($pictures as $picture)
                                <button type="button" class="costing-detail-thumb{{ $loop->first ? ' active' : '' }}" aria-label="Tampilkan gambar {{ $loop->iteration }}" data-image-index="{{ $loop->index }}">
                                    <img src="/nds_wip/public/uploads/costing/{{ rawurlencode($picture->file_name) }}" alt="">
                                </button>
                                @endforeach
                            </div>
                            @endif
                            <div class="costing-detail-image-count">Image {{ $pictures->count() ? '1 of '.$pictures->count() : '' }}</div>
                        @else
                            <div class="costing-detail-no-image">Tidak ada gambar Costing.</div>
                        @endif
                    </div>
                </section>
            </div>

            <div class="costing-detail-lower-overview">
                <section class="costing-detail-panel">
                    <h2>Shipping &amp; Destinations</h2>
                    <div class="costing-detail-fields">
                        <div class="costing-detail-field"><span>Ship Mode</span><strong>{{ $shipMode->shipmode ?? '-' }}</strong></div>
                        <div class="costing-detail-field"><span>Shipment Type</span><strong class="mkt-tc">{{ strtoupper($costing->shipment_type ?: '-') }}</strong></div>
                        <div class="costing-detail-field"><span>Marketing Order</span><strong class="mkt-tc">{{ $costing->mkt_order ?: '-' }}</strong></div>
                        <div class="costing-detail-field"><span>Notes</span><strong>{{ $costing->notes ?: '-' }}</strong></div>
                    </div>
                </section>
                <div class="costing-detail-lower-right">
                    <section class="costing-detail-panel">
                        <h2>Parameter Costing</h2>
                        <div class="costing-detail-fields">
                            <div class="costing-detail-field"><span>Qty (PCS)</span><strong>{{ $orderQty }}</strong></div>
                            {{-- <div class="costing-detail-field"><span>Unit</span><strong>{{ $costing->unit ?: '-' }}</strong></div> --}}
                            <div class="costing-detail-field"><span>VAT</span><strong>{{ number_format((float) ($costing->vat ?? 0), 2, ',', '.') }}%</strong></div>
                        </div>
                    </section>
                    <section class="costing-detail-panel is-price">
                        <h2>Price &amp; Currency</h2>
                        <div class="costing-detail-fields">
                            <div class="costing-detail-field"><span>Currency</span><strong>{{ $currency->nama_pilihan ?? '-' }}</strong></div>
                            <div class="costing-detail-field"><span>Confirm Price</span><strong>{{ number_format($confirmPrice, 2, ',', '.') }}</strong></div>
                            <div class="costing-detail-field"><span>Rate to IDR</span><strong>{{ number_format($rateToIdr, 2, ',', '.') }}</strong></div>
                            <div class="costing-detail-field"><span>Rate from IDR</span><strong>{{ number_format((float) ($costing->rate_from_idr ?? 0), 2, ',', '.') }}</strong></div>
                        </div>
                    </section>
                </div>
            </div>

            @foreach (['Fabric', 'Accessories Sewing', 'Accessories Packing', 'Manufacturing'] as $category)
                @php
                    $categoryDetails = collect($details[$category] ?? []);
                    $categoryTotals = ['idr' => 0, 'usd' => 0, 'qty' => 0, 'value' => 0];
                    $categoryId = 'costing-detail-'.\Illuminate\Support\Str::slug($category);
                @endphp
                <section class="costing-detail-category" data-category-table>
                    <button type="button" class="costing-detail-category-toggle" aria-expanded="true" aria-controls="{{ $categoryId }}">
                        <span>{{ $category }}</span><i class="fas fa-chevron-down"></i>
                    </button>
                    <div class="costing-detail-category-body" id="{{ $categoryId }}">
                        <div class="costing-detail-legend-top"><span class="costing-detail-dot"></span>Auto</div>
                        <div class="costing-detail-table-wrap">
                            <table class="table table-sm costing-detail-table">
                                <thead>
                                    <tr>
                                        {{-- <th><input type="checkbox" class="row-check" aria-label="Pilih semua" disabled></th> --}}
                                        <th>No</th>
                                        <th>Content</th>
                                        <th>Set</th>
                                        <th>Description</th>
                                        <th>Supplier</th>
                                        <th>Curr</th>
                                        <th>Price</th>
                                        <th>Price PX IDR</th>
                                        <th>Price PX USD</th>
                                        <th>Cons/Pc</th>
                                        <th>Unit</th>
                                        <th>Allow (%)</th>
                                        <th>Value IDR</th>
                                        <th>Value USD</th>
                                        <th>%</th>
                                        <th>Qty BOM</th>
                                        <th>Value</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($categoryDetails as $det)
                                        @php
                                            $allowance = max(0, (float) ($det->allowance ?? 0));
                                            $qtyBom = (int) ceil((1 + ($allowance / 100)) * $orderQty * (float) ($det->cons ?? 0));
                                            $totalValue = $qtyBom * (float) ($det->price_px_idr ?? 0);
                                            $categoryTotals['idr'] += (float) ($det->value_idr ?? 0);
                                            $categoryTotals['usd'] += (float) ($det->value_usd ?? 0);
                                            $categoryTotals['qty'] += $qtyBom;
                                            $categoryTotals['value'] += $totalValue;
                                            $valuePercent = $totalCostIdr > 0 ? ((float) ($det->value_idr ?? 0) / $totalCostIdr) * 100 : 0;
                                        @endphp
                                        <tr>
                                            {{-- <td class="text-center"><input type="checkbox" class="row-check" aria-label="Pilih item {{ $loop->iteration }}" disabled></td> --}}
                                            <td class="text-center">{{ $loop->iteration }}</td>
                                            <td class="mkt-tc">{{ $det->nama_item ?: '-' }}</td>
                                            <td>{{ $det->nama_set ?? ($productSets[$det->set]->nama ?? '-') }}</td>
                                            <td class="mkt-tc">{{ $det->item_desc ?: '-' }}</td>
                                            <td class="mkt-tc">{{ $det->nama_supplier ?: '-' }}</td>
                                            <td class="text-center">{{ $det->curr ?: '-' }}</td>
                                            <td class="text-right">{{ number_format((float) ($det->price ?? 0), 2, ',', '.') }}</td>
                                            <td class="text-right">{{ number_format((float) ($det->price_px_idr ?? 0), 2, ',', '.') }}</td>
                                            <td class="text-right">{{ number_format((float) ($det->price_px_usd ?? 0), 4, ',', '.') }}</td>
                                            <td class="text-right">{{ number_format((float) ($det->cons ?? 0), 4, ',', '.') }}</td>
                                            <td class="text-center">{{ $det->unit ?: '-' }}</td>
                                            <td class="text-right">{{ number_format($allowance, 2, ',', '.') }}%</td>
                                            <td class="text-right">{{ number_format((float) ($det->value_idr ?? 0), 2, ',', '.') }}</td>
                                            <td class="text-right">{{ number_format((float) ($det->value_usd ?? 0), 4, ',', '.') }}</td>
                                            <td class="text-right">{{ number_format($valuePercent, 2, ',', '.') }}%</td>
                                            <td class="text-right">{{ number_format($qtyBom, 0, ',', '.') }}</td>
                                            <td class="text-right">{{ number_format($totalValue, 2, ',', '.') }}</td>
                                        </tr>
                                    @empty
                                        <tr class="empty-row"><td colspan="18">Belum ada detail {{ $category }}.</td></tr>
                                    @endforelse
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <td colspan="12" class="text-center">TOTAL</td>
                                        <td class="text-right">{{ number_format($categoryTotals['idr'], 2, ',', '.') }}</td>
                                        <td class="text-right">{{ number_format($categoryTotals['usd'], 4, ',', '.') }}</td>
                                        <td></td>
                                        <td class="text-right">{{ number_format($categoryTotals['qty'], 0, ',', '.') }}</td>
                                        <td class="text-right">{{ number_format($categoryTotals['value'], 2, ',', '.') }}</td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                        <div class="costing-detail-pagebar">
                            <label class="costing-detail-page-size">Item Per Page
                                <select aria-label="Item per halaman">
                                    <option>4</option><option>10</option><option>25</option><option>50</option>
                                </select>
                                <span>of {{ $categoryDetails->count() }}</span>
                            </label>
                            <nav class="costing-detail-pages" aria-label="Pagination"></nav>
                        </div>
                        <div class="costing-detail-auto">Auto calculated</div>
                    </div>
                </section>
            @endforeach

            @php
                $otherDetails = collect($details['Other Cost'] ?? []);
                $otherCategoryId = 'costing-detail-other-cost';
                $otherPercent = $totalCostIdr > 0 ? ($otherTotals['idr'] / $totalCostIdr) * 100 : 0;
                $gaPercentOfTotal = $totalCostIdr > 0 ? ($gaIdr / $totalCostIdr) * 100 : 0;
            @endphp
            <section class="costing-detail-category" data-category-table>
                <button type="button" class="costing-detail-category-toggle" aria-expanded="true" aria-controls="{{ $otherCategoryId }}">
                    <span>Other Cost</span><i class="fas fa-chevron-down"></i>
                </button>
                <div class="costing-detail-category-body" id="{{ $otherCategoryId }}">
                    <div class="costing-detail-legend-top"><span class="costing-detail-dot"></span>Auto</div>
                    <div class="costing-detail-table-wrap">
                        <table class="table table-sm costing-detail-table costing-detail-other">
                            <thead>
                                <tr>
                                    {{-- <th><input type="checkbox" class="row-check" aria-label="Pilih semua" disabled></th> --}}
                                    <th>No</th>
                                    <th>Content</th>
                                    <th>Cons</th>
                                    <th>Unit</th>
                                    <th>Allowance</th>
                                    <th>Value IDR</th>
                                    <th>Value USD</th>
                                    <th>%</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($otherDetails as $det)
                                    @php $itemPercent = $totalCostIdr > 0 ? ((float) ($det->value_idr ?? 0) / $totalCostIdr) * 100 : 0; @endphp
                                    <tr>
                                        {{-- <td class="text-center"><input type="checkbox" class="row-check" aria-label="Pilih item {{ $loop->iteration }}" disabled></td> --}}
                                        <td class="text-center">{{ $loop->iteration }}</td>
                                        <td class="mkt-tc">{{ $det->nama_item ?: '-' }}</td>
                                        <td class="mkt-tc">{{ $det->item_desc ?: '-' }}</td>
                                        <td class="text-center">{{ $det->unit ?: '-' }}</td>
                                        <td class="text-right">{{ number_format((float) ($det->allowance ?? 0), 2, ',', '.') }}%</td>
                                        <td class="text-right">{{ number_format((float) ($det->value_idr ?? 0), 7, ',', '.') }}</td>
                                        <td class="text-right">{{ number_format($rateToIdr > 0 ? (float) ($det->value_idr ?? 0) / $rateToIdr : 0, 7, ',', '.') }}</td>
                                        <td class="text-right">{{ number_format($itemPercent, 2, ',', '.') }}%</td>
                                    </tr>
                                @empty
                                    <tr class="empty-row"><td colspan="9">Belum ada detail Other Cost.</td></tr>
                                @endforelse
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="5">TOTAL</td>
                                    <td class="text-right">{{ number_format($otherTotals['idr'], 7, ',', '.') }}</td>
                                    <td class="text-right">{{ number_format($otherTotals['usd'], 7, ',', '.') }}</td>
                                    <td class="text-right">{{ number_format($otherPercent, 2, ',', '.') }}%</td>
                                </tr>
                                <tr>
                                    <td colspan="5">G&amp;A</td>
                                    <td class="text-right">{{ number_format($gaIdr, 7, ',', '.') }}</td>
                                    <td class="text-right">{{ number_format($rateToIdr > 0 ? $gaIdr / $rateToIdr : 0, 7, ',', '.') }}</td>
                                    <td class="text-right">{{ number_format($gaPercentOfTotal, 2, ',', '.') }}%</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                    <div class="costing-detail-pagebar">
                        <label class="costing-detail-page-size">Item Per Page
                            <select aria-label="Item per halaman">
                                <option>4</option><option>10</option><option>25</option><option>50</option>
                            </select>
                            <span>of {{ $otherDetails->count() }}</span>
                        </label>
                        <nav class="costing-detail-pages" aria-label="Pagination"></nav>
                    </div>
                    <div class="costing-detail-auto">Auto calculated</div>
                </div>
            </section>

            <div class="costing-detail-summary-wrap">
                <div class="costing-detail-summary-card">
                <table class="costing-detail-summary-table">
                    <thead>
                        <tr>
                            <th></th>
                            <th>VALUE IDR</th>
                            <th>VALUE USD</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>TOTAL COST</td>
                            <td>{{ number_format($totalCostIdr, 7, ',', '.') }}</td>
                            <td>{{ number_format($totalCostUsd, 7, ',', '.') }}</td>
                        </tr>
                        <tr>
                            <td>BALANCE OTHER COST</td>
                            <td>{{ number_format($balanceOtherIdr, 7, ',', '.') }}</td>
                            <td>{{ number_format($balanceOtherUsd, 7, ',', '.') }}</td>
                        </tr>
                        <tr>
                            <td>VAT <span>{{ number_format($vatPercent, 2, ',', '.') }}%</span></td>
                            <td>{{ number_format($vatIdr, 7, ',', '.') }}</td>
                            <td>{{ number_format($vatUsd, 7, ',', '.') }}</td>
                        </tr>
                        <tr>
                            <td>PROFIT <span>6%</span></td>
                            <td>{{ number_format($profitIdr, 7, ',', '.') }}</td>
                            <td>{{ number_format($profitUsd, 7, ',', '.') }}</td>
                        </tr>
                    </tbody>
                </table>
                </div>
            </div>
        </div>
    </div>
</div>

@if ($unlockState === 'locked')
    @include('marketing.costing.unlock_request_modal')
    @push('scripts')
    <script>
        $('#btn-open-unlock').on('click', function () {
            openUnlockRequest({
                type: 'costing',
                id: {{ (int) $costing->id }},
                number: @json($costing->cost_no),
                buyer: @json($buyerName),
                onSuccess: () => window.location.reload()
            });
        });
    </script>
    @endpush
@endif

@push('scripts')
<script src="{{ asset('dist/js/marketing-title-case.js') }}?v={{ @filemtime(public_path('assets/dist/js/marketing-title-case.js')) }}"></script>
<script>
    // Teks nama (buyer, brand, season, product, item, supplier) tampil Title Case; singkatan PT/CV/LTD tetap kapital.
    document.querySelectorAll('.mkt-tc').forEach(function (el) {
        if (el.children.length === 0) el.textContent = window.mktTitleCase(el.textContent);
    });

    (function () {
        document.querySelectorAll('[data-category-table]').forEach(function (section) {
            const body = section.querySelector('.costing-detail-category-body');
            const toggle = section.querySelector('.costing-detail-category-toggle');
            const table = section.querySelector('.costing-detail-table');
            const pager = section.querySelector('.costing-detail-pages');
            const pageSizeSelect = section.querySelector('.costing-detail-page-size select');
            const countLabel = section.querySelector('.costing-detail-items-count');   // tidak dipakai lagi (diganti legend "Auto")
            const rows = Array.from(table.tBodies[0].querySelectorAll('tr:not(.empty-row)'));
            let currentPage = 1;

            toggle.addEventListener('click', function () {
                const expanded = toggle.getAttribute('aria-expanded') === 'true';
                toggle.setAttribute('aria-expanded', String(!expanded));
                body.hidden = expanded;
            });
            toggle.addEventListener('keydown', function (event) {
                if (event.key === 'Enter' || event.key === ' ') {
                    event.preventDefault();
                    toggle.click();
                }
            });

            function renderPage() {
                const pageSize = Number(pageSizeSelect.value);
                const pageCount = Math.max(1, Math.ceil(rows.length / pageSize));
                currentPage = Math.min(currentPage, pageCount);
                rows.forEach(function (row, index) {
                    row.hidden = index < (currentPage - 1) * pageSize || index >= currentPage * pageSize;
                });
                if (countLabel) countLabel.textContent = rows.length ? '# '+((currentPage - 1) * pageSize + 1)+' - '+Math.min(currentPage * pageSize, rows.length) : '# 0';
                pager.replaceChildren();
                const addPageButton = function (label, page, disabled, current) {
                    const button = document.createElement('button');
                    button.type = 'button';
                    button.textContent = label;
                    button.disabled = disabled;
                    if (current) button.setAttribute('aria-current', 'page');
                    button.addEventListener('click', function () {
                        currentPage = page;
                        renderPage();
                    });
                    pager.appendChild(button);
                };
                addPageButton('‹', currentPage - 1, currentPage === 1, false);
                for (let page = 1; page <= pageCount; page++) {
                    addPageButton(String(page), page, false, page === currentPage);
                }
                addPageButton('›', currentPage + 1, currentPage === pageCount, false);
            }

            pageSizeSelect.addEventListener('change', function () {
                currentPage = 1;
                renderPage();
            });
            renderPage();
        });

        const images = Array.from(document.querySelectorAll('.costing-detail-image'));
        const thumbs = Array.from(document.querySelectorAll('.costing-detail-thumb, .costing-detail-dots button'));
        const peek = document.querySelector('.costing-detail-peek');
        if (images.length > 1) {
            let activeImage = 0;
            const count = document.querySelector('.costing-detail-image-count');
            function showImage(index) {
                activeImage = (index + images.length) % images.length;
                images.forEach(function (image, imageIndex) {
                    image.hidden = imageIndex !== activeImage;
                });
                thumbs.forEach(function (thumb, thumbIndex) {
                    thumb.classList.toggle('active', thumbIndex === activeImage);
                });
                count.textContent = 'Image '+(activeImage + 1)+' of '+images.length;
                if (peek) peek.src = images[(activeImage + 1) % images.length].src;
            }
            if (peek) peek.addEventListener('click', function () { showImage(activeImage + 1); });
            thumbs.forEach(function (thumb) {
                thumb.addEventListener('click', function () {
                    showImage(Number(thumb.dataset.imageIndex));
                });
            });
            document.querySelectorAll('.costing-detail-image-nav').forEach(function (button) {
                button.addEventListener('click', function () {
                    showImage(activeImage + Number(button.dataset.imageStep));
                });
            });
        }
    })();
</script>
@endpush
@endsection
