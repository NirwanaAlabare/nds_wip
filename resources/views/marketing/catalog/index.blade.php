@extends('layouts.index')

@section('custom-link')
    <style>
    @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap');

    /* Transisi fade antar halaman — jalan di SEMUA browser (murni CSS/JS) */
    body { animation: page-enter .3s ease; }               /* fade-in saat halaman dibuka */
    body.page-leaving { opacity: 0; transition: opacity .28s ease; } /* fade-out saat klik item */
    @keyframes page-enter { from { opacity: 0; } to { opacity: 1; } }

    :root {
      --bg-main: #ffffff;
      --bg-surface: #f8f8f8;
      --border-color: #e5e5e5;
      --text-dark: #111111;
      --text-muted: #666666;
      --text-light: #a0a0a0;
      --font-stack: 'Plus Jakarta Sans', sans-serif;
    }


    .catalog-container {
      font-family: var(--font-stack);
      max-width: 1400px;
      margin: 0 auto;
      padding: 20px 15px;
      color: var(--text-dark);
      background-color: var(--bg-main);
    }


    .page-header {
      font-size: 0.8rem;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: 0.15em;
      margin-bottom: 32px;
      border-bottom: 1px solid var(--border-color);
      padding-bottom: 12px;
    }


    .search-wrapper {
      position: relative;
      margin-bottom: 48px;
      max-width: 350px;
    }

    .search-input {
      width: 100%;
      padding: 12px 64px 12px 0;
      font-family: var(--font-stack);
      font-size: 0.95rem;
      color: var(--text-dark);
      background-color: transparent;
      border: none;
      border-bottom: 2px solid var(--border-color);
      outline: none;
      transition: border-color 0.3s ease;
    }

    .search-input:focus {
      border-color: var(--text-dark);
    }

    .search-icon-btn {
      position: absolute;
      right: 0;
      left: auto;
      top: 50%;
      transform: translateY(-50%);
      background: none;
      border: none;
      padding: 4px;
      cursor: pointer;
      display: flex;
      align-items: center;
      justify-content: center;
      color: var(--text-muted);
      transition: color 0.3s ease;
    }

    .search-icon-btn:hover {
      color: var(--text-dark);
    }

    .search-icon {
      width: 20px;
      height: 20px;
      pointer-events: none;
    }

    .search-reset {
      position: absolute;
      right: 36px;
      top: 50%;
      transform: translateY(-50%);
      width: 20px;
      height: 20px;
      color: var(--text-muted);
      cursor: pointer;
      text-decoration: none;
      transition: color 0.3s ease;
      display: flex;
      align-items: center;
      justify-content: center;
    }

    .search-reset:hover {
      color: var(--text-dark);
    }


    .catalog-grid {
      display: grid;
      grid-template-columns: repeat(6, 1fr);
      gap: 40px 24px;
    }


    .product-item {
      display: flex;
      flex-direction: column;
      text-decoration: none !important;
      color: inherit;
      cursor: pointer;
      padding: 12px;
      margin: -12px;
      border: 1px solid transparent;
      transition: all 0.3s ease;
      border-radius: 6px;
      min-width: 0;
    }

    .product-item:hover {
      border-color: var(--text-dark);
    }


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
        justify-content: center;
        width: 100%;
        height: 100%;
        position: relative;
        overflow: hidden;
        background:
            repeating-linear-gradient(
                45deg,
                rgba(9, 37, 82, 0.06) 0px,
                rgba(9, 37, 82, 0.06) 1px,
                transparent 1px,
                transparent 12px
            ),
            linear-gradient(135deg, #f0f4f8 0%, #d9e2ec 100%);
    }

    /* Wrapper untuk animasi saat hover */
    .doodle-wrapper {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 16px;
        opacity: 0.6;
        transition: all 0.35s ease;
    }

    .image-wrapper:hover .doodle-wrapper {
        opacity: 1;
        transform: scale(1.08) rotate(-2deg); /* Sedikit miring saat di-hover biar tambah kesan doodle */
    }

    /* Frame luar bergaya coretan tangan */
    .doodle-frame {
        position: relative;
        width: 80px;
        height: 60px;
        border: 2.5px solid #092552;
        /* Trik border-radius asimetris untuk efek digambar tangan */
        border-radius: 255px 15px 225px 15px / 15px 225px 15px 255px;
        background: rgba(255, 255, 255, 0.3);
        overflow: hidden;
        box-shadow: 4px 4px 0px rgba(9, 37, 82, 0.1);
    }

    /* Matahari doodle */
    .doodle-sun {
        position: absolute;
        top: 8px;
        right: 14px;
        width: 14px;
        height: 14px;
        border: 2px solid #092552;
        /* Lingkaran yang tidak sempurna */
        border-radius: 45% 55% 45% 50%;
    }

    /* Gunung (dibuat dari kotak yang diputar 45 derajat) */
    .doodle-mountain {
        position: absolute;
        border: 2.5px solid #092552;
        background: transparent;
        transform: rotate(45deg);
        border-radius: 4px; /* Ujung gunung sedikit tumpul */
    }

    .doodle-mountain.left {
        bottom: -22px;
        left: -8px;
        width: 35px;
        height: 35px;
    }

    .doodle-mountain.right {
        bottom: -32px;
        right: -5px;
        width: 50px;
        height: 50px;
        /* Garis ini menimpa gunung sebelah kiri agar terlihat 3D bertumpuk */
        background: #f0f4f8;
    }

    /* lingkaran soft di belakang icon biar ga plain */
    .no-image-placeholder::before {
        content: "";
        position: absolute;
        width: 70px;
        height: 70px;
        border-radius: 50%;
        background: radial-gradient(circle, rgba(100,116,139,0.10) 0%, rgba(100,116,139,0) 70%);
        z-index: 0;
    }

    .no-image-placeholder .icon-svg {
        width: 32px;
        height: 32px;
        position: relative;
        z-index: 1;
        color: #94a3b8; /* slate-400 */
        transition: transform 0.25s ease, color 0.25s ease;
    }

    .image-wrapper:hover .no-image-placeholder .icon-svg {
        transform: scale(1.08);
        color: #64748b;
    }

    .no-image-placeholder .placeholder-text {
        position: relative;
        z-index: 1;
        font-size: 11px;
        font-weight: 500;
        letter-spacing: 0.05em;
        text-transform: uppercase;
        color: #94a3b8;
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


    .product-info {
      display: flex;
      flex-direction: column;
      gap: 4px;
      min-width: 0;
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


    /* ===== SKELETON LOADING ===== */
    @keyframes skeleton-shimmer {
      0%   { background-position: -400px 0; }
      100% { background-position: 400px 0; }
    }

    .skeleton {
      background-color: #eeeeee;
      background-image: linear-gradient(90deg, #eeeeee 0px, #f5f5f5 40px, #eeeeee 80px);
      background-size: 600px 100%;
      background-repeat: no-repeat;
      animation: skeleton-shimmer 1.3s infinite linear;
      border-radius: 4px;
    }

    .skeleton-item {
      display: flex;
      flex-direction: column;
      padding: 12px;
      margin: -12px;
    }

    .skel-img {
      width: 100%;
      aspect-ratio: 3 / 4;
      margin-bottom: 16px;
    }

    .skel-line {
      height: 10px;
      margin-bottom: 8px;
    }

    .skel-title { width: 70%; height: 12px; }
    .skel-sub   { width: 45%; }
    .skel-spec  { width: 90%; }
    .skel-spec.short { width: 60%; }

    /* Kontrol tampil/sembunyi skeleton vs konten asli */
    #catalog-real { display: none; }
    .content-loaded #catalog-skeleton { display: none; }
    .content-loaded #catalog-real {
      display: block;
      animation: fade-in .4s ease;
    }

    @keyframes fade-in {
      from { opacity: 0; transform: translateY(6px); }
      to   { opacity: 1; transform: translateY(0); }
    }


    /* ===== HOVER PREVIEW (modal besar di tengah, muncul saat hover lama, tanpa klik) ===== */
    .hover-backdrop {
      position: fixed;
      inset: 0;
      z-index: 1070;
      background: rgba(0,0,0,.45);
      opacity: 0;
      pointer-events: none;           /* biar hover ke item tetap kebaca */
      transition: opacity .2s ease;
    }
    .hover-backdrop.show { opacity: 1; }

    .hover-preview {
      position: fixed;
      top: 50%;
      left: 50%;
      transform: translate(-50%, -50%) scale(.96);
      z-index: 1080;
      width: min(760px, 92vw);
      display: flex;
      gap: 28px;
      background: #ffffff;
      border-radius: 16px;
      box-shadow: 0 30px 80px rgba(0,0,0,.35);
      padding: 28px;
      opacity: 0;
      pointer-events: none;           /* biar nggak ganggu hover/klik item */
      transition: opacity .2s ease, transform .2s ease;
      font-family: var(--font-stack);
    }
    .hover-preview.show { opacity: 1; transform: translate(-50%, -50%) scale(1); }

    .hp-img {
        position: relative;
        width: 280px;
        flex-shrink: 0;
        aspect-ratio: 3 / 4;
        border-radius: 10px;
        overflow: hidden;
        /* Background baru dengan pola garis navy dan gradasi senada */
        background:
            repeating-linear-gradient(
            45deg,
            rgba(9, 37, 82, 0.1) 0px,
            rgba(9, 37, 82, 0.1) 1px,
            transparent 1px,
            transparent 12px
            ),
            linear-gradient(135deg, #eef2f8 0%, #dce4f0 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        }

        .hp-img img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        }

        .hp-noimg {
        font-size: .8rem;
        font-weight: 600;
        letter-spacing: .1em;
        text-transform: uppercase;
        /* Warna teks disesuaikan agar serasi dengan navy */
        color: #092552;
        opacity: .7;
        }

    .hp-body {
      flex: 1;
      min-width: 0;
      display: flex;
      flex-direction: column;
      justify-content: center;
    }
    .hp-title { font-size: 1.5rem; font-weight: 600; color: var(--text-dark); margin-bottom: 20px; letter-spacing: -0.01em; }
    .hp-row { font-size: .85rem; color: var(--text-dark); margin-bottom: 14px; line-height: 1.45; }
    .hp-row:last-child { margin-bottom: 0; }
    .hp-row b { display: block; color: var(--text-light); text-transform: uppercase; font-size: .65rem; font-weight: 700; letter-spacing: .06em; margin-bottom: 3px; }

    @media (max-width: 768px) {
      .hover-backdrop, .hover-preview { display: none; }  /* di layar kecil pakai tap ke detail aja */
    }

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
            <header class="page-header">Katalog Style</header>

            <form action="{{ route('master-marketing-catalog') }}" method="GET" class="search-wrapper">
              <button type="submit" class="search-icon-btn" title="Cari">
                <svg class="search-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
              </button>
              <input type="text" name="search" class="search-input" placeholder="Cari nama style..." value="{{ request('search') }}">
              @if(request('search'))
                  <a href="{{ route('master-marketing-catalog') }}" class="search-reset" title="Reset Pencarian">
                      <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                      </svg>
                  </a>
              @endif
            </form>

            {{-- SKELETON: tampil dulu sampai semua gambar/konten selesai load --}}
            <div id="catalog-skeleton" class="catalog-grid" aria-hidden="true">
              @for($i = 0; $i < ($styles->count() ?: 12); $i++)
                  <div class="skeleton-item">
                    <div class="skeleton skel-img"></div>
                    <div class="skeleton skel-line skel-title"></div>
                    <div class="skeleton skel-line skel-sub"></div>
                    <div class="skeleton skel-line skel-spec"></div>
                    <div class="skeleton skel-line skel-spec short"></div>
                  </div>
              @endfor
            </div>

            {{-- KONTEN ASLI: disembunyikan dulu, ditampilkan setelah load --}}
            <div id="catalog-real">
              <main class="catalog-grid">
                @forelse($styles as $s)
                    <a href="{{ route('master-marketing-catalog-detail', ['styleno' => urlencode($s->styleno)]) }}" class="product-item"
                   data-styleno="{{ $s->styleno }}"
                   data-image="{{ $s->image }}"
                   data-colors="{{ $s->colors }}"
                   data-sizes="{{ $s->sizes }}"
                   data-dest="{{ $s->destinations }}"
                   data-buyer="{{ $s->buyer_name }}">
                      <div class="image-frame">
    @if(!empty($s->image))
        <img src="/nds_wip/public/uploads/costing/{{ $s->image }}"
             alt="{{ $s->styleno }}"
             style="width:100%;height:100%;object-fit:cover;"
             onerror="this.style.display='none';this.nextElementSibling.style.display='flex';">
        <div class="no-image-placeholder" style="display:none;">
    @else
        <div class="no-image-placeholder">
    @endif
            <!-- Doodle Content -->
            <div class="doodle-frame">
                <div class="doodle-sun"></div>
                <div class="doodle-mountain left"></div>
                <div class="doodle-mountain right"></div>
            </div>
            <span class="placeholder-text">NO IMAGE</span>
        </div>
</div>

                      <div class="product-info">
                        <h2 class="product-title" title="{{ $s->styleno }}">{{ $s->styleno ?: 'Unknown Style' }}</h2>
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
                          <div class="spec-line" title="{{ $s->destinations }}">
                              <span>DEST:</span>
                              @if($s->destinations)
                                  @php
                                      $destArr = array_filter(array_map('trim', explode(',', $s->destinations)));
                                      $topDest = array_slice($destArr, 0, 2);
                                      $destText = implode(', ', $topDest);
                                      if(count($destArr) > 2) $destText .= ' +'.(count($destArr)-2);
                                  @endphp
                                  {{ strtoupper($destText) }}
                              @else
                                  -
                              @endif
                          </div>
                          <div class="spec-line" title="{{ $s->buyer_name }}">
                              <span>BUYER:</span>
                              {{ strtoupper($s->buyer_name) ?: '-' }}
                          </div>
                        </div>
                      </div>
                    </a>
                @empty
                    <div style="grid-column: 1 / -1; text-align: center; padding: 50px 0; color: #999;">
                        <i class="fas fa-box-open fa-3x mb-3"></i>
                        <h5>Belum Ada Data Master Style</h5>
                    </div>
                @endforelse
              </main>

              <div class="d-flex justify-content-center mt-5">
                  {{ $styles->appends(request()->query())->links('pagination::bootstrap-4') }}
              </div>
            </div>
        </div>
    </div>

    {{-- Modal hover preview (dipakai ulang untuk semua item) --}}
    <div id="hover-backdrop" class="hover-backdrop"></div>
    <div id="hover-preview" class="hover-preview" aria-hidden="true"></div>

    {{-- Fallback tanpa JS: kalau JS mati, langsung tampilkan konten asli & sembunyikan skeleton --}}
    <noscript>
      <style>
        #catalog-skeleton { display: none !important; }
        #catalog-real { display: block !important; }
      </style>
    </noscript>
@endsection

@section('custom-script')
    <script>
        (function () {
            function revealContent() {
                document.body.classList.add('content-loaded');
                hidePreview();
            }

            function hidePreview() {
                document.getElementById('hover-preview').classList.remove('show');
                document.getElementById('hover-backdrop').classList.remove('show');
            }

            window.addEventListener('load', revealContent);

            setTimeout(revealContent, 4000);

            window.addEventListener('pageshow', function () {
                document.body.classList.remove('page-leaving');
                document.body.classList.add('content-loaded');
            });

            document.querySelectorAll('.product-item').forEach(function (a) {
                a.addEventListener('click', function (e) {
                    if (e.metaKey || e.ctrlKey || e.shiftKey || e.button === 1) return;
                    e.preventDefault(); // Prevent default navigation
                    var href = this.href;
                    hidePreview();
                    document.body.classList.add('page-leaving');
                    setTimeout(function () { window.location = href; }, 280);
                });
            });

            var HOVER_DELAY = 1700;
            var IMG_BASE = '/nds_wip/public/uploads/costing/';
            var preview = document.getElementById('hover-preview');
            var backdrop = document.getElementById('hover-backdrop');
            var hoverTimer = null;

            function esc(v) {
                return (v == null ? '' : String(v))
                    .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;')
                    .replace(/"/g, '&quot;');
            }

            function specRow(label, val) {
                var text = (val && val.trim()) ? esc(val) : '-';
                return '<div class="hp-row"><b>' + label + '</b>' + text + '</div>';
            }

            function buildImg(image, styleno) {
                if (image && image.trim()) {
                    return '<div class="hp-img"><img src="' + IMG_BASE + encodeURI(image) + '" alt="' + esc(styleno) + '"'
                        + ' onerror="this.parentNode.innerHTML=&quot;<span class=\'hp-noimg\'>No Image</span>&quot;"></div>';
                }
                return '<div class="hp-img"><span class="hp-noimg">No Image</span></div>';
            }

            function showPreview(item) {
                var d = item.dataset;
                preview.innerHTML =
                    buildImg(d.image, d.styleno) +
                    '<div class="hp-body">' +
                        '<div class="hp-title">' + (d.styleno ? esc(d.styleno) : 'Unknown Style') + '</div>' +
                        specRow('Colors', d.colors) +
                        specRow('Sizes', d.sizes) +
                        specRow('Destinations', d.dest) +
                        specRow('Buyer', d.buyer) +
                    '</div>';
                backdrop.classList.add('show');
                preview.classList.add('show');
            }

            function hidePreview() {
                clearTimeout(hoverTimer);
                preview.classList.remove('show');
                backdrop.classList.remove('show');
            }

            document.querySelectorAll('.product-item').forEach(function (item) {
                item.addEventListener('mouseenter', function () {
                    clearTimeout(hoverTimer);
                    hoverTimer = setTimeout(function () { showPreview(item); }, HOVER_DELAY);
                });
                item.addEventListener('mouseleave', hidePreview);
            });
            window.addEventListener('scroll', hidePreview, { passive: true });
        })();
    </script>
@endsection
