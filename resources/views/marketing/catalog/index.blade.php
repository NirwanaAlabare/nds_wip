@extends('layouts.index')

@section('custom-link')
    <style>
    @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap');

    :root {
      --bg-main: #ffffff;
      --bg-surface: #f8f8f8;
      --border-color: #e5e5e5;
      --text-dark: #111111;
      --text-muted: #666666;
      --text-light: #a0a0a0;
      --font-stack: 'Plus Jakarta Sans', sans-serif;
    }

    /* Container Utama */
    .catalog-container {
      font-family: var(--font-stack);
      max-width: 1400px;
      margin: 0 auto;
      padding: 20px 15px;
      color: var(--text-dark);
      background-color: var(--bg-main);
    }

    /* Header Judul Halaman */
    .page-header {
      font-size: 0.8rem;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: 0.15em;
      margin-bottom: 32px;
      border-bottom: 1px solid var(--border-color);
      padding-bottom: 12px;
    }

    /* Minimalist Search Bar (Tanpa Tombol Cari Tebal) */
    .search-wrapper {
      position: relative;
      margin-bottom: 48px;
      max-width: 400px;
    }

    .search-input {
      width: 100%;
      padding: 12px 12px 12px 36px;
      font-family: var(--font-stack);
      font-size: 0.85rem;
      color: var(--text-dark);
      background-color: transparent;
      border: none;
      border-bottom: 1px solid var(--border-color);
      outline: none;
      transition: border-color 0.3s ease;
    }

    .search-input:focus {
      border-color: var(--text-dark);
    }

    .search-icon {
      position: absolute;
      left: 8px;
      top: 50%;
      transform: translateY(-50%);
      width: 16px;
      height: 16px;
      color: var(--text-light);
      pointer-events: none;
    }

    /* Grid Tanpa Card (Borderless Grid) */
    .catalog-grid {
      display: grid;
      grid-template-columns: repeat(6, 1fr);
      gap: 40px 24px;
    }

    /* Item Produk Individu */
    .product-item {
      display: flex;
      flex-direction: column;
      text-decoration: none !important;
      color: inherit;
      cursor: pointer;
      padding: 12px;
      margin: -12px; /* Offset padding agar grid tetap sejajar */
      border: 1px solid transparent;
      transition: all 0.3s ease;
      border-radius: 6px;
      min-width: 0;
    }

    .product-item:hover {
      border-color: var(--text-dark);
    }

    /* Wadah Gambar / Placeholder */
    .image-frame {
      position: relative;
      width: 100%;
      aspect-ratio: 3 / 4;
      overflow: hidden;
      background: linear-gradient(135deg, #e0e7ff 0%, #f3e8ff 50%, #fce7f3 100%);
      display: flex;
      justify-content: center;
      align-items: center;
      margin-bottom: 16px;
      transition: opacity 0.3s ease;
      border-radius: 4px;
    }

    .product-item:hover .image-frame {
      opacity: 0.85;
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

    /* Informasi Produk di Bawah Gambar */
    .product-info {
      display: flex;
      flex-direction: column;
      gap: 4px;
      min-width: 0; /* Mencegah flex/grid blowout */
    }

    .product-title {
      font-size: 0.85rem;
      font-weight: 500;
      letter-spacing: -0.01em;
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
      min-width: 0;
    }

    .product-id {
      font-size: 0.7rem;
      color: var(--text-light);
      font-weight: 500;
      letter-spacing: 0.05em;
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
      min-width: 0;
    }

    /* Spesifikasi Kecil (Color & Size) */
    .product-spec {
      margin-top: 4px;
      display: flex;
      flex-direction: column;
      gap: 2px;
      border-top: 1px dashed #f0f0f0;
      padding-top: 6px;
    }

    .spec-line {
      font-size: 0.7rem;
      color: var(--text-muted);
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
    }

    .spec-line span {
      color: var(--text-light);
      font-weight: 600;
      text-transform: uppercase;
      font-size: 0.65rem;
      margin-right: 4px;
    }

    /* Responsif untuk Layar yang Lebih Kecil */
    @media (max-width: 1200px) {
      .catalog-grid { grid-template-columns: repeat(4, 1fr); }
    }
    @media (max-width: 768px) {
      .catalog-grid { grid-template-columns: repeat(2, 1fr); gap: 32px 16px; }
    }
    </style>
@endsection

@section('content')
    <div class="container-fluid" style="background: #fff; min-height: calc(100vh - 60px);">
        <div class="catalog-container">
            <!-- HEADER -->
            <header class="page-header">Katalog Style</header>

            <!-- PENCARIAN MINIMALIS -->
            <form action="{{ route('master-marketing-catalog') }}" method="GET" class="search-wrapper">
              <svg class="search-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
              </svg>
              <input type="text" name="search" class="search-input" placeholder="Cari nama style..." value="{{ request('search') }}">
            </form>

            <!-- GRID KATALOG BERGAYA EDITORIAL -->
            <main class="catalog-grid">
              @forelse($styles as $s)
                  <a href="{{ route('master-marketing-catalog-detail', ['id_item' => $s->id_item]) }}" class="product-item">
                    <div class="image-frame">
                        <div class="no-image-placeholder">
                            <svg class="icon-svg" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 0 0 1.5-1.5V6a1.5 1.5 0 0 0-1.5-1.5H3.75A1.5 1.5 0 0 0 2.25 6v12a1.5 1.5 0 0 0 1.5 1.5Zm10.5-11.25h.008v.008h-.008V8.25Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z"></path>
                            </svg>
                            <span class="placeholder-text">No Image</span>
                        </div>
                    </div>


                    <div class="product-info">
                      <h2 class="product-title" title="{{ $s->Styleno }}">{{ $s->Styleno ?: 'Unknown Style' }}</h2>
                      <span class="product-id" title="{{ $s->itemname }}">{{ $s->itemname ?: '-' }}</span>
                      <div class="product-spec">
                        <div class="spec-line" title="{{ $s->colors }}">
                            <span>CLR:</span>
                            @if($s->colors)
                                @php
                                    $colorsArr = array_filter(array_map('trim', explode(',', $s->colors)));
                                    $topColors = array_slice($colorsArr, 0, 2);
                                    $colorText = implode(', ', $topColors);
                                    if(count($colorsArr) > 2) $colorText .= ' +'.(count($colorsArr)-2);
                                @endphp
                                {{ $colorText }}
                            @else
                                -
                            @endif
                        </div>
                        <div class="spec-line" title="{{ $s->sizes }}">
                            <span>SIZE:</span>
                            @if($s->sizes)
                                @php
                                    $sizesArr = array_filter(array_map('trim', explode(',', $s->sizes)));
                                    $topSizes = array_slice($sizesArr, 0, 4);
                                    $sizeText = implode(', ', $topSizes);
                                    if(count($sizesArr) > 4) $sizeText .= ' ...';
                                @endphp
                                {{ $sizeText }}
                            @else
                                -
                            @endif
                        </div>
                      </div>
                    </div>
                  </a>
              @empty
                  <div style="grid-column: 1 / -1; text-align: center; padding: 50px 0; color: #999;">
                      <i class="fas fa-box-open fa-3x mb-3"></i>
                      <h5>Belum Ada Data Master Style</h5>
                      <p>Silakan buat Sales Order atau daftar Master Style untuk memunculkan Katalog.</p>
                  </div>
              @endforelse
            </main>

            <div class="d-flex justify-content-center mt-5">
                {{ $styles->appends(request()->query())->links('pagination::bootstrap-4') }}
            </div>
        </div>
    </div>
@endsection

@section('custom-script')
    <script>
    </script>
@endsection
