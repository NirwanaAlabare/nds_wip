@extends('layouts.index')

@section('custom-link')
    <style>
    @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap');

    /* Transisi fade antar halaman */
    body { animation: page-enter .3s ease; }
    body.page-leaving { opacity: 0; transition: opacity .28s ease; }
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

    /* ===== SEARCH BAR ===== */
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

    .search-input:focus { border-color: var(--text-dark); }

    .search-icon-btn {
      position: absolute;
      right: 0;
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

    .search-icon-btn:hover { color: var(--text-dark); }
    .search-icon { width: 20px; height: 20px; pointer-events: none; }

    .search-reset {
      position: absolute;
      right: 36px;
      top: 50%;
      transform: translateY(-50%);
      width: 20px;
      height: 20px;
      color: var(--text-muted);
      cursor: pointer;
      display: flex;
      align-items: center;
      justify-content: center;
      transition: color 0.3s ease;
    }
    .search-reset:hover { color: var(--text-dark); }

    /* ===== GRID & CARD ===== */
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

    .product-item:hover { border-color: var(--text-dark); }

    .image-frame {
      position: relative;
      width: 100%;
      aspect-ratio: 3 / 4;
      overflow: hidden;
      background: var(--bg-surface);
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

    /* =========================================================
       ===== NO IMAGE PLACEHOLDER (FULL DETAIL TEMA DOODLE) =====
       ========================================================= */
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

    /* Wrapper doodle + animasi hover (Ukuran di-scale 0.85 agar pas di grid index) */
    .doodle-wrapper {
      display: flex;
      flex-direction: column;
      align-items: center;
      gap: 20px;
      opacity: 0.7;
      transition: all 0.35s ease;
      z-index: 1;
      transform: scale(0.85);
    }

    /* Hover trigger dari .product-item (kartu terluar) */
    .product-item:hover .doodle-wrapper {
      opacity: 1;
      transform: scale(0.92) rotate(-1.5deg);
    }

    /* Kartu doodle (Ukuran dan desain asli sesuai detail) */
    .doodle-frame {
      position: relative;
      width: 140px;
      height: 105px;
      border: 3px solid #092552;
      border-radius: 14px 20px 16px 22px / 20px 14px 22px 16px;
      background: rgba(255, 255, 255, 0.55);
      overflow: hidden;
      box-shadow: 5px 5px 0px rgba(9, 37, 82, 0.12);
    }

    .doodle-frame::before {
      content: "";
      position: absolute;
      inset: 5px;
      border: 1.5px dashed #092552;
      border-radius: 11px 17px 13px 19px / 17px 11px 19px 13px;
      opacity: 0.4;
      pointer-events: none;
    }

    /* Matahari doodle (Lengkap dengan pancaran sinar/shadow) */
    .doodle-sun {
      position: absolute;
      top: 16px;
      right: 20px;
      width: 20px;
      height: 20px;
      background: #ffd93d;
      border: 2px solid #092552;
      border-radius: 45% 55% 48% 50%;
      box-shadow:
        0 -10px 0 -4px #092552,
        0 10px 0 -4px #092552,
        -10px 0 0 -4px #092552,
        10px 0 0 -4px #092552,
        -7px -7px 0 -5px #092552,
        7px -7px 0 -5px #092552,
        -7px 7px 0 -5px #092552,
        7px 7px 0 -5px #092552;
    }

    /* Gunung doodle */
    .doodle-mountain {
      position: absolute;
      bottom: -6%;
      width: 60%;
      aspect-ratio: 1.4 / 1;
      background: #cfe3d8;
      border: 2px solid #092552;
      clip-path: polygon(0% 100%, 42% 20%, 58% 42%, 78% 8%, 100% 100%);
    }

    .doodle-mountain.left {
      left: -10%;
      z-index: 1;
    }

    .doodle-mountain.right {
      right: -14%;
      background: #b8d4c4;
      z-index: 0;
      opacity: 0.9;
    }

    /* Label NO IMAGE (Pill style lengkap dengan outline dan shadow) */
    .placeholder-text {
      position: relative;
      z-index: 2;
      font-size: 12px;
      font-weight: 700;
      letter-spacing: 0.14em;
      text-transform: uppercase;
      color: #092552;
      font-family: 'Comic Sans MS', 'Plus Jakarta Sans', sans-serif;
      background: rgba(255, 255, 255, 0.85);
      padding: 4px 12px;
      border: 2px solid #092552;
      border-radius: 8px 12px 8px 12px / 12px 8px 12px 8px;
      transform: rotate(-1.5deg);
      box-shadow: 2px 2px 0px rgba(9, 37, 82, 0.15);
    }
    /* ================= END DOODLE ================= */

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

    .skeleton-item { display: flex; flex-direction: column; padding: 12px; margin: -12px; }
    .skel-img { width: 100%; aspect-ratio: 3 / 4; margin-bottom: 16px; }
    .skel-line { height: 10px; margin-bottom: 8px; }
    .skel-title { width: 70%; height: 12px; }
    .skel-sub   { width: 45%; }
    .skel-spec  { width: 90%; }
    .skel-spec.short { width: 60%; }

    #catalog-real { display: none; }
    .content-loaded #catalog-skeleton { display: none; }
    .content-loaded #catalog-real { display: block; animation: fade-in .4s ease; }
    @keyframes fade-in {
      from { opacity: 0; transform: translateY(6px); }
      to   { opacity: 1; transform: translateY(0); }
    }

    /* ===== HOVER PREVIEW (MODAL) ===== */
    .hover-backdrop {
      position: fixed; inset: 0; z-index: 1070; background: rgba(0,0,0,.45);
      opacity: 0; pointer-events: none; transition: opacity .2s ease;
    }
    .hover-backdrop.show { opacity: 1; }

    .hover-preview {
      position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%) scale(.96);
      z-index: 1080; width: min(760px, 92vw); display: flex; gap: 28px;
      background: #ffffff; border-radius: 16px; box-shadow: 0 30px 80px rgba(0,0,0,.35);
      padding: 28px; opacity: 0; pointer-events: none;
      transition: opacity .2s ease, transform .2s ease; font-family: var(--font-stack);
    }
    .hover-preview.show { opacity: 1; transform: translate(-50%, -50%) scale(1); }

    .hp-img {
      position: relative; width: 280px; flex-shrink: 0; aspect-ratio: 3 / 4;
      border-radius: 10px; overflow: hidden;
    }
    .hp-img img { width: 100%; height: 100%; object-fit: cover; }

    .hp-body { flex: 1; min-width: 0; display: flex; flex-direction: column; justify-content: center; }
    .hp-title { font-size: 1.5rem; font-weight: 600; color: var(--text-dark); margin-bottom: 20px; letter-spacing: -0.01em; }
    .hp-row { font-size: .85rem; color: var(--text-dark); margin-bottom: 14px; line-height: 1.45; }
    .hp-row:last-child { margin-bottom: 0; }
    .hp-row b { display: block; color: var(--text-light); text-transform: uppercase; font-size: .65rem; font-weight: 700; letter-spacing: .06em; margin-bottom: 3px; }

    /* Responsif Grid */
    @media (max-width: 1200px) { .catalog-grid { grid-template-columns: repeat(4, 1fr); } }
    @media (max-width: 768px) {
      .catalog-grid { grid-template-columns: repeat(2, 1fr); gap: 32px 16px; }
      .hover-backdrop, .hover-preview { display: none; }
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

            {{-- SKELETON: tampil dulu sampai konten load --}}
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

            {{-- KONTEN ASLI --}}
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
                                <!-- HTML Placeholder sesuai dengan struktur CSS detail -->
                                <div class="doodle-wrapper">
                                    <div class="doodle-frame">
                                        <div class="doodle-sun"></div>
                                        <div class="doodle-mountain left"></div>
                                        <div class="doodle-mountain right"></div>
                                    </div>
                                    <span class="placeholder-text">NO IMAGE</span>
                                </div>
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

    {{-- Modal hover preview --}}
    <div id="hover-backdrop" class="hover-backdrop"></div>
    <div id="hover-preview" class="hover-preview" aria-hidden="true"></div>

    {{-- Fallback tanpa JS --}}
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
                    e.preventDefault();
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

            // Template HTML untuk JS Hover Preview Modal agar estetikanya 100% konsisten
            var doodleHtml =
                '<div class="no-image-placeholder">' +
                    '<div class="doodle-wrapper" style="opacity:1; transform: scale(1.1);">' +
                        '<div class="doodle-frame">' +
                            '<div class="doodle-sun"></div>' +
                            '<div class="doodle-mountain left"></div>' +
                            '<div class="doodle-mountain right"></div>' +
                        '</div>' +
                        '<span class="placeholder-text">NO IMAGE</span>' +
                    '</div>' +
                '</div>';

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
                        + ' onerror="this.parentNode.innerHTML=doodleHtml;"></div>';
                }
                return '<div class="hp-img">' + doodleHtml + '</div>';
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

            // document.querySelectorAll('.product-item').forEach(function (item) {
            //     item.addEventListener('mouseenter', function () {
            //         clearTimeout(hoverTimer);
            //         hoverTimer = setTimeout(function () { showPreview(item); }, HOVER_DELAY);
            //     });
            //     item.addEventListener('mouseleave', hidePreview);
            // });
            // window.addEventListener('scroll', hidePreview, { passive: true });
        })();
    </script>
@endsection
