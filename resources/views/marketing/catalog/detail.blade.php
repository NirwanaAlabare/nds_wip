@extends('layouts.index')

@section('custom-link')
    <link rel="stylesheet" href="{{ asset('plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/datatables-responsive/css/responsive.bootstrap4.min.css') }}">
    <style>
    @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap');

    .catalog-wrapper {
      width: 100%;
      background: #ffffff;
      border: 1px solid #e8e8e8;
      display: flex;
      flex-direction: column;
      font-family: 'Plus Jakarta Sans', sans-serif;
      border-radius: 6px;
      overflow: hidden;
    }

    /* SECTION 1: DETAIL PRODUK UTAMA (T-SHIRT) */
    .detail-grid {
      display: grid;
      grid-template-columns: 1fr 2.5fr;
      gap: 48px;
      padding: 40px;
      border-bottom: 1px solid #e8e8e8;
    }

    .image-wrapper {
      position: relative;
      width: 100%;
      background: linear-gradient(135deg, #e0e7ff 0%, #f3e8ff 50%, #fce7f3 100%);
      display: flex;
      justify-content: center;
      align-items: center;
      aspect-ratio: 1 / 1;
      overflow: hidden;
      max-width: 500px;
      margin: 0 auto;
    }

    .no-image-placeholder {
      display: flex;
      flex-direction: column;
      align-items: center;
      gap: 8px;
      color: #a855f7;
      opacity: 0.7;
    }
    
    .icon-svg {
      width: 44px;
      height: 44px;
    }
    
    .placeholder-text {
      font-size: 11px;
      font-weight: 600;
      text-transform: uppercase;
      letter-spacing: 0.1em;
    }

    .info-wrapper {
      display: flex;
      flex-direction: column;
      justify-content: center;
    }

    .style-code {
      font-size: 0.75rem;
      font-weight: 600;
      color: #999999;
      letter-spacing: 0.08em;
      text-transform: uppercase;
      margin-bottom: 8px;
    }

    .product-title {
      font-size: 1.8rem;
      font-weight: 400;
      color: #111111;
      letter-spacing: -0.02em;
      margin-bottom: 32px;
    }

    .variant-group {
      margin-bottom: 24px;
    }

    .variant-label {
      font-size: 0.7rem;
      font-weight: 600;
      text-transform: uppercase;
      letter-spacing: 0.08em;
      color: #666666;
      margin-bottom: 10px;
    }

    .color-pill-container {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
    }

    .color-pill {
      font-size: 0.75rem;
      font-weight: 500;
      color: #111111;
      background: #f9f9f9;
      border: 1px solid #e8e8e8;
      padding: 8px 16px;
      display: inline-flex;
      align-items: center;
      gap: 8px;
      border-radius: 4px;
    }

    .color-indicator {
      width: 10px;
      height: 10px;
      border-radius: 50%;
      border: 1px solid rgba(0,0,0,0.05);
    }

    .size-box-container {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
    }

    .size-box {
      font-size: 0.8rem;
      font-weight: 500;
      color: #ffffff;
      background: #111111;
      min-width: 38px;
      padding: 0 8px;
      height: 38px;
      display: flex;
      align-items: center;
      justify-content: center;
      border-radius: 4px;
    }

    /* SECTION 2: SISTEM TAB DATA (BOM & PO) */
    .tab-section {
      padding: 40px;
      background-color: #fcfcfc;
    }

    /* Header Navigasi Tab */
    .tab-navigation {
      display: flex;
      gap: 24px;
      border-bottom: 1px solid #e8e8e8;
      margin-bottom: 24px;
    }

    .tab-button {
      background: none;
      border: none;
      font-family: 'Plus Jakarta Sans', sans-serif;
      font-size: 0.85rem;
      font-weight: 500;
      color: #999999;
      padding-bottom: 12px;
      cursor: pointer;
      position: relative;
      display: flex;
      align-items: center;
      gap: 8px;
      transition: color 0.2s ease;
      outline: none;
    }

    .tab-button svg {
      width: 16px;
      height: 16px;
    }

    .tab-button:hover {
      color: #111111;
    }

    .tab-button.active {
      color: #111111;
      font-weight: 600;
    }

    .tab-button.active::after {
      content: '';
      position: absolute;
      bottom: -1px;
      left: 0;
      width: 100%;
      height: 2px;
      background-color: #111111;
    }

    .tab-content {
      display: none;
    }

    .tab-content.active {
      display: block;
    }

    .aesthetic-table {
      width: 100%;
      border-collapse: collapse;
      font-size: 0.85rem;
      text-align: left;
    }

    .aesthetic-table th {
      font-size: 0.7rem;
      font-weight: 600;
      text-transform: uppercase;
      letter-spacing: 0.08em;
      color: #999999;
      padding: 12px 16px;
      border-bottom: 1px solid #111111;
      background-color: transparent;
    }

    .aesthetic-table td {
      padding: 16px;
      color: #111111;
      border-bottom: 1px solid #e8e8e8;
      vertical-align: middle;
    }

    .category-badge {
      display: inline-block;
      font-size: 0.65rem;
      font-weight: 600;
      text-transform: uppercase;
      letter-spacing: 0.05em;
      color: #0d9488;
      background-color: #f0fdfa;
      padding: 4px 8px;
      border: 1px solid #ccfbf1;
      border-radius: 4px;
    }

    .category-badge.fabric {
        color: #4f46e5;
        background-color: #e0e7ff;
        border-color: #c7d2fe;
    }

    .category-badge.other {
        color: #475569;
        background-color: #f1f5f9;
        border-color: #e2e8f0;
    }

    /* Responsif Layout */
    @media (max-width: 768px) {
      .detail-grid {
        grid-template-columns: 1fr;
        gap: 32px;
        padding: 24px;
      }
      .tab-section {
        padding: 24px;
      }
      .tab-navigation {
        gap: 16px;
      }
      .aesthetic-table th, .aesthetic-table td {
        padding: 10px 8px;
        font-size: 0.75rem;
      }
    }
    </style>
@endsection

@section('content')
    <div class="container-fluid">
        <div class="mb-4">
            <a href="{{ route('master-marketing-catalog') }}" class="btn btn-sm btn-outline-dark font-weight-bold" style="border-radius: 4px; border-color: #e8e8e8; background: #fff;">
                <i class="fas fa-arrow-left"></i> Kembali ke Katalog
            </a>
        </div>

        <div class="catalog-wrapper">
            <div class="detail-grid">
              <section class="image-wrapper">
                  <div class="no-image-placeholder">
                      <svg class="icon-svg" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                          <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 0 0 1.5-1.5V6a1.5 1.5 0 0 0-1.5-1.5H3.75A1.5 1.5 0 0 0 2.25 6v12a1.5 1.5 0 0 0 1.5 1.5Zm10.5-11.25h.008v.008h-.008V8.25Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z"></path>
                      </svg>
                      <span class="placeholder-text">No Image</span>
                  </div>
              </section>

              <section class="info-wrapper">
                <div class="style-code">{{ $styleData->itemname ?: '-' }}</div>
                <h1 class="product-title">{{ $styleData->Styleno ?: '-' }}</h1>

                <div class="variant-group">
                  <div class="variant-label">Master Colors</div>
                  <div class="color-pill-container" style="font-size: 0.85rem; color: #111;">
                    @php
                        $colors = array_filter(array_map('trim', explode(',', $styleData->colors ?? '')));
                    @endphp
                    @if(count($colors) > 0)
                        {{ implode(', ', $colors) }}
                    @else
                        <em class="text-muted" style="font-size: 0.8rem;">Belum ada warna yang didaftarkan</em>
                    @endif
                  </div>
                </div>

                <div class="variant-group">
                  <div class="variant-label">Master Sizes</div>
                  <div class="size-box-container">
                    @php
                        $sizes = array_filter(array_map('trim', explode(',', $styleData->sizes ?? '')));
                    @endphp
                    @forelse($sizes as $s)
                    <div class="size-box" title="{{ $s }}">{{ $s }}</div>
                    @empty
                        <em class="text-muted" style="font-size: 0.8rem;">Belum ada ukuran yang didaftarkan</em>
                    @endforelse
                  </div>
                </div>
              </section>
            </div>

            <div class="tab-section">
              <nav class="tab-navigation">
                <button class="tab-button active" onclick="switchTab(event, 'bom-tab')">
                  <svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
                  Rincian Material (BOM)
                </button>
                <button class="tab-button" onclick="switchTab(event, 'so-tab')">
                  <svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                  Histori Sales Order
                </button>
              </nav>

              <div id="bom-tab" class="tab-content active">
                <div class="table-responsive">
                    <table class="aesthetic-table" id="table-bom">
                      <thead>
                        <tr>
                          <th style="width: 20%;">Category</th>
                          <th style="width: 50%;">Item Desc</th>
                          <th style="width: 15%;" class="text-right">Cons</th>
                          <th style="width: 15%;">Unit</th>
                        </tr>
                      </thead>
                      <tbody>
                        @forelse($bomData as $bom)
                        <tr>
                          <td>
                            @if($bom->mattype == 'F')
                                <span class="category-badge fabric">Fabric</span>
                            @elseif($bom->mattype == 'A')
                                <span class="category-badge">Accessories</span>
                            @else
                                <span class="category-badge other">{{ $bom->mattype ?? 'Other' }}</span>
                            @endif
                          </td>
                          <td>{{ $bom->itemdesc ?? '-' }}</td>
                          <td class="text-right" style="font-weight: 600;">{{ $bom->cons ?? 0 }}</td>
                          <td style="color: #666666;">{{ $bom->unit ?? '-' }}</td>
                        </tr>
                        @empty
                        <tr>
                          <td colspan="4" class="text-center" style="color: #999; padding: 30px;">Belum ada rincian material (BOM) untuk Style ini.</td>
                        </tr>
                        @endforelse
                      </tbody>
                    </table>
                </div>
              </div>

              <div id="so-tab" class="tab-content">
                <div class="table-responsive">
                    <table class="aesthetic-table" id="table-so">
                      <thead>
                        <tr>
                          <th>Tanggal SO</th>
                          <th>Nomor SO</th>
                          <th>Nama Buyer</th>
                          <th>Destinasi (Tujuan)</th>
                          <th class="text-right">Qty Pesanan</th>
                          <th>Status</th>
                        </tr>
                      </thead>
                      <tbody>
                        @forelse($soHistory as $so)
                        <tr>
                          <td style="color: #666;">{{ $so->so_date ? \Carbon\Carbon::parse($so->so_date)->format('d M Y') : '-' }}</td>
                          <td style="font-weight: 600;">{{ $so->so_no ?? '-' }}</td>
                          <td>{{ $so->buyer_name ?? '-' }}</td>
                          <td>{{ $so->dest ?? '-' }}</td>
                          <td class="text-right" style="font-weight: 600;">{{ number_format($so->qty ?? 0) }}</td>
                          <td>
                            @if($so->cancel_h == 'Y')
                                <span class="category-badge" style="color: #b91c1c; background-color: #fef2f2; border-color: #fecaca;">Cancelled</span>
                            @else
                                <span class="category-badge" style="color: #15803d; background-color: #f0fdf4; border-color: #bbf7d0;">Active</span>
                            @endif
                          </td>
                        </tr>
                        @empty
                        <tr>
                          <td colspan="6" class="text-center" style="color: #999; padding: 30px;">Belum ada Sales Order yang terdaftar menggunakan Style ini.</td>
                        </tr>
                        @endforelse
                      </tbody>
                    </table>
                </div>
              </div>
            </div>
        </div>
    </div>
@endsection

@section('custom-script')
    <script src="{{ asset('plugins/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
    <script>
        function switchTab(evt, tabId) {
            var i, tabcontent, tablinks;
            tabcontent = document.getElementsByClassName("tab-content");
            for (i = 0; i < tabcontent.length; i++) {
                tabcontent[i].classList.remove("active");
            }
            tablinks = document.getElementsByClassName("tab-button");
            for (i = 0; i < tablinks.length; i++) {
                tablinks[i].classList.remove("active");
            }
            document.getElementById(tabId).classList.add("active");
            evt.currentTarget.classList.add("active");
        }

        $(document).ready(function() {
            $('#table-so').DataTable({
                "responsive": true,
                "lengthChange": false,
                "autoWidth": false,
                "info": false,
                "searching": false,
                "paging": true,
                "pageLength": 10,
                "order": [[0, "desc"]]
            });


            $('.dataTables_wrapper .row').css('margin-bottom', '1rem');
        });
    </script>
@endsection
