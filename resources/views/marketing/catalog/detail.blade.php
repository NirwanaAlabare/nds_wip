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
      border-radius: 12px;
      overflow: hidden;
      box-shadow: 0 1px 3px rgba(0,0,0,.04), 0 10px 30px rgba(0,0,0,.05);
    }

    /* SECTION 1: HERO PRODUK — foto besar kiri, info kanan */
    .detail-grid {
      display: grid;
      grid-template-columns: minmax(0, 1.05fr) minmax(0, 1fr);
      gap: 64px;
      padding: 56px;
      border-bottom: 1px solid #e8e8e8;
      align-items: start;   /* info nempel ke atas, foto bisa sticky */
    }

    /* Foto produk besar; tinggi dibatasi biar section nggak kepanjangan. */
    .image-wrapper {
      position: sticky;
      top: 24px;
      width: 100%;
      height: 560px;
      display: flex;
      justify-content: center;
      align-items: center;
      overflow: hidden;
      border-radius: 16px;
      box-shadow: 0 16px 44px rgba(0,0,0,.12);
    }

    .image-wrapper img {
      display: block;
      width: 100%;
      height: 100%;
      object-fit: cover;
      object-position: center 15%;   /* fokus ke atas (bagian baju) */
      transition: transform .45s ease;
    }

    .image-wrapper:hover img {
      transform: scale(1.05);
    }

    /* ===== NO IMAGE PLACEHOLDER (tema doodle) ===== */
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

    /* Wrapper doodle + animasi hover */
    .doodle-wrapper {
      display: flex;
      flex-direction: column;
      align-items: center;
      gap: 20px;
      opacity: 0.7;
      transition: all 0.35s ease;
      z-index: 1;
    }

    .image-wrapper:hover .doodle-wrapper {
      opacity: 1;
      transform: scale(1.05) rotate(-1.5deg);
    }

    /* Kartu doodle */
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

    /* Matahari doodle */
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

    /* Teks placeholder */
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
    /* ===== END NO IMAGE PLACEHOLDER ===== */

    .info-wrapper {
      display: flex;
      flex-direction: column;
      justify-content: flex-start;
      padding-top: 8px;
    }

    .style-code {
      font-size: 0.75rem;
      font-weight: 600;
      letter-spacing: 0.08em;
      text-transform: uppercase;
      margin-bottom: 8px;
    }

    .product-title {
      font-size: 1.9rem;
      font-weight: 400;
      color: #111111;
      letter-spacing: -0.02em;
      margin-bottom: 28px;
      position: relative;
      padding-bottom: 18px;
    }

    .product-title::after {
      content: '';
      position: absolute;
      left: 0;
      bottom: 0;
      width: 48px;
      height: 3px;
      background: #111111;
      border-radius: 2px;
    }

    .variant-group {
      padding-bottom: 22px;
      margin-bottom: 22px;
      border-bottom: 1px dashed #ececec;
    }

    .variant-group:last-child {
      border-bottom: none;
      padding-bottom: 0;
      margin-bottom: 0;
    }

    .variant-label {
      font-size: 0.7rem;
      font-weight: 600;
      text-transform: uppercase;
      letter-spacing: 0.08em;
      margin-bottom: 12px;
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
      border-radius: 999px;
      transition: all .18s ease;
    }

    .color-pill:hover {
      border-color: #111111;
      transform: translateY(-1px);
      box-shadow: 0 3px 8px rgba(0,0,0,.07);
    }

    .color-indicator {
      width: 12px;
      height: 12px;
      border-radius: 50%;
      border: 1px solid rgba(0,0,0,0.12);
      flex-shrink: 0;
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
      border-radius: 6px;
      transition: all .18s ease;
    }

    .size-box:hover {
      background: #333333;
      transform: translateY(-1px);
    }

    /* SECTION 2: SISTEM TAB DATA (BOM & PO) */
    .tab-section {
      margin: 28px;
      padding: 32px;
      background-color: #ffffff;
      border: 1px solid #ececec;
      border-radius: 12px;
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

    .tab-count {
      font-size: 0.65rem;
      font-weight: 700;
      background: #e8e8e8;
      color: #666666;
      border-radius: 999px;
      padding: 1px 8px;
      transition: all .2s ease;
    }

    .tab-button.active .tab-count {
      background: #111111;
      color: #ffffff;
    }

    .tab-content {
      display: none;
    }

    .tab-content.active {
      display: block;
    }

    .table-responsive {
      border: 1px solid #ececec;
      border-radius: 10px;
      overflow: hidden;
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
      padding: 14px 16px;
      border-bottom: 1px solid #111111;
      background-color: #fafafa;
    }

    .aesthetic-table td {
      padding: 16px;
      color: #111111;
      border-bottom: 1px solid #f0f0f0;
      vertical-align: middle;
    }

    .aesthetic-table tbody tr {
      transition: background .15s ease;
    }

    .aesthetic-table tbody tr:nth-child(even) {
      background: #fbfbfb;
    }

    .aesthetic-table tbody tr:hover {
      background: #f4f4f5;
    }

    .aesthetic-table tbody tr:last-child td {
      border-bottom: none;
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

    .empty-state {
      text-align: center;
      color: #999999;
      padding: 40px 20px;
      font-size: 0.85rem;
    }

    /* Responsif Layout */
    @media (max-width: 768px) {
      .detail-grid {
        grid-template-columns: 1fr;
        gap: 32px;
        padding: 24px;
      }
      .image-wrapper {
        position: static;   /* jangan sticky di layar kecil */
        height: 420px;
      }
      .tab-section {
        margin: 16px;
        padding: 20px;
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

        @php
            // Warna → hex, biar titik indikator akurat. Nama tak dikenal jatuh ke abu netral.
            // Mendukung nama EN & ID (data suka campur: "black"/"hitam", dll).
            // Key ditulis tanpa spasi/tanda karena $colorHex membuang non-huruf.
            $colorMap = [
            // --- WARNA BAWAAN ANDA ---
            'abu' => '#808080', 'abuabu' => '#808080', 'abuabumuda' => '#D1D5DB', 'abuabutua' => '#4B5563',
            'aliceblue' => '#F0F8FF', 'anggur' => '#722F37', 'antiquewhite' => '#FAEBD7', 'aprikot' => '#FBCEB1',
            'aqua' => '#00FFFF', 'aquamarine' => '#7FFFD4', 'arang' => '#36454F', 'army' => '#4B5320',
            'azure' => '#F0FFFF', 'babyblue' => '#89CFF0', 'babypink' => '#F4C2C2', 'bata' => '#B22222',
            'beige' => '#F5F5DC', 'bening' => '#00000000', 'biru' => '#0000FF', 'birudongker' => '#1E3A5F',
            'biruelektrik' => '#7DF9FF', 'birulangit' => '#87CEEB', 'birulaut' => '#006994', 'birumuda' => '#ADD8E6',
            'birunavy' => '#000080', 'birutua' => '#00008B', 'bisque' => '#FFE4C4', 'black' => '#000000',
            'blanchedalmond' => '#FFEBCD', 'blue' => '#0000FF', 'blueviolet' => '#8A2BE2', 'blush' => '#DE5D83',
            'brick' => '#B22222', 'brokenwhite' => '#FAF9F6', 'brown' => '#A52A2A', 'burgundy' => '#800020',
            'burgundywine' => '#722F37', 'burlywood' => '#DEB887', 'burntorange' => '#CC5500', 'cadetblue' => '#5F9EA0',
            'camel' => '#C19A6B', 'champagne' => '#F7E7CE', 'charcoal' => '#36454F', 'charcoalgrey' => '#36454F',
            'chartreuse' => '#7FFF00', 'chocolate' => '#D2691E', 'clay' => '#B66A50', 'cobalt' => '#0047AB',
            'cokelat' => '#8B4513', 'coklat' => '#8B4513', 'coklatmuda' => '#C4A484', 'coklattua' => '#5C4033',
            'coksu' => '#5C4033', 'coral' => '#FF7F50', 'cornflowerblue' => '#6495ED', 'cornsilk' => '#FFF8DC',
            'cream' => '#FFFDD0', 'crimson' => '#DC143C', 'cyan' => '#00FFFF', 'darkblue' => '#00008B',
            'darkcyan' => '#008B8B', 'darkgoldenrod' => '#B8860B', 'darkgray' => '#A9A9A9', 'darkgreen' => '#006400',
            'darkgrey' => '#A9A9A9', 'darkkhaki' => '#BDB76B', 'darkmagenta' => '#8B008B', 'darkolivegreen' => '#556B2F',
            'darkorange' => '#FF8C00', 'darkorchid' => '#9932CC', 'darkred' => '#8B0000', 'darksalmon' => '#E9967A',
            'darkseagreen' => '#8FBC8F', 'darkslateblue' => '#483D8B', 'darkslategray' => '#2F4F4F', 'darkslategrey' => '#2F4F4F',
            'darkturquoise' => '#00CED1', 'darkviolet' => '#9400D3', 'deeppink' => '#FF1493', 'deepskyblue' => '#00BFFF',
            'denim' => '#1560BD', 'dimgray' => '#696969', 'dimgrey' => '#696969', 'dodgerblue' => '#1E90FF',
            'dongker' => '#1E3A5F', 'eggshell' => '#F0EAD6', 'emas' => '#FFD700', 'emerald' => '#50C878',
            'firebrick' => '#B22222', 'floralwhite' => '#FFFAF0', 'forestgreen' => '#228B22', 'fuchsia' => '#FF00FF',
            'fuschia' => '#FF00FF', 'gading' => '#FFFFF0', 'gainsboro' => '#DCDCDC', 'gelap' => '#1F2937',
            'ghostwhite' => '#F8F8FF', 'gold' => '#FFD700', 'goldenrod' => '#DAA520', 'graphite' => '#383838',
            'gray' => '#808080', 'green' => '#008000', 'greenyellow' => '#ADFF2F', 'grey' => '#808080',
            'hijau' => '#008000', 'hijauarmy' => '#4B5320', 'hijaubotol' => '#006A4E', 'hijaudaun' => '#4CAF50',
            'hijaulumut' => '#4A5D23', 'hijaumint' => '#98FF98', 'hijaumuda' => '#90EE90', 'hijaustabilo' => '#39FF14',
            'hijautosca' => '#008080', 'hijautua' => '#006400', 'hitam' => '#000000', 'honeydew' => '#F0FFF0',
            'hotpink' => '#FF69B4', 'indianred' => '#CD5C5C', 'indigo' => '#4B0082', 'ivory' => '#FFFFF0',
            'jambon' => '#FFC0CB', 'jeans' => '#1560BD', 'jingga' => '#FFA500', 'karat' => '#B7410E',
            'keemasan' => '#FFD700', 'khaki' => '#C3B091', 'kobalt' => '#0047AB', 'kopi' => '#6F4E37',
            'koral' => '#FF7F50', 'krem' => '#FFFDD0', 'kuning' => '#FFFF00', 'kuningemas' => '#FFD700',
            'kuningmuda' => '#FFFACD', 'kuningtua' => '#F0C000', 'lavender' => '#E6E6FA', 'lavenderblush' => '#FFF0F5',
            'lawngreen' => '#7CFC00', 'lemonchiffon' => '#FFFACD', 'lightblue' => '#ADD8E6', 'lightcoral' => '#F08080',
            'lightcyan' => '#E0FFFF', 'lightgoldenrodyellow' => '#FAFAD2', 'lightgray' => '#D3D3D3', 'lightgreen' => '#90EE90',
            'lightgrey' => '#D3D3D3', 'lightpink' => '#FFB6C1', 'lightsalmon' => '#FFA07A', 'lightseagreen' => '#20B2AA',
            'lightskyblue' => '#87CEFA', 'lightslategray' => '#778899', 'lightslategrey' => '#778899', 'lightsteelblue' => '#B0C4DE',
            'lightyellow' => '#FFFFE0', 'lila' => '#C8A2C8', 'lime' => '#00FF00', 'limegreen' => '#32CD32',
            'linen' => '#FAF0E6', 'lumut' => '#4A5D23', 'magenta' => '#FF00FF', 'maron' => '#800000',
            'maroon' => '#800000', 'marronn' => '#800000', 'marun' => '#800000', 'mauve' => '#E0B0FF',
            'mediumaquamarine' => '#66CDAA', 'mediumblue' => '#0000CD', 'mediumorchid' => '#BA55D3', 'mediumpurple' => '#9370DB',
            'mediumseagreen' => '#3CB371', 'mediumslateblue' => '#7B68EE', 'mediumspringgreen' => '#00FA9A', 'mediumturquoise' => '#48D1CC',
            'mediumvioletred' => '#C71585', 'merah' => '#FF0000', 'merahbata' => '#B22222', 'merahmarun' => '#800000',
            'merahmuda' => '#FFC0CB', 'merahtua' => '#8B0000', 'midnightblue' => '#191970', 'mint' => '#98FF98',
            'mintcream' => '#F5FFFA', 'mistyrose' => '#FFE4E1', 'moccasin' => '#FFE4B5', 'mocha' => '#967969',
            'moka' => '#967969', 'mustard' => '#FFDB58', 'mustardyellow' => '#FFDB58', 'navajowhite' => '#FFDEAD',
            'navy' => '#000080', 'neon' => '#39FF14', 'neongreen' => '#39FF14', 'neonpink' => '#FF6EC7',
            'netral' => '#D4D4D8', 'nila' => '#4B0082', 'nude' => '#E3BC9A', 'ochre' => '#CC7722',
            'offwhite' => '#FAF9F6', 'oker' => '#CC7722', 'oldlace' => '#FDF5E6', 'olive' => '#808000',
            'olivedrab' => '#6B8E23', 'orange' => '#FFA500', 'orangered' => '#FF4500', 'oranye' => '#FFA500',
            'oranyetua' => '#FF8C00', 'orchid' => '#DA70D6', 'oren' => '#FFA500', 'palegoldenrod' => '#EEE8AA',
            'palegreen' => '#98FB98', 'paleturquoise' => '#AFEEEE', 'palevioletred' => '#DB7093', 'papayawhip' => '#FFEFD5',
            'pasir' => '#C2B280', 'peach' => '#FFE5B4', 'peachpuff' => '#FFDAB9', 'pearl' => '#EAE0C8',
            'perak' => '#C0C0C0', 'persik' => '#FFE5B4', 'peru' => '#CD853F', 'pink' => '#FFC0CB',
            'pinkmuda' => '#FFE4E1', 'pinktua' => '#FF1493', 'plum' => '#8E4585', 'powderblue' => '#B0E0E6',
            'prune' => '#701C1C', 'purple' => '#800080', 'putih' => '#FFFFFF', 'rebeccapurple' => '#663399',
            'red' => '#FF0000', 'rosybrown' => '#BC8F8F', 'royalblue' => '#4169E1', 'rust' => '#B7410E',
            'saddlebrown' => '#8B4513', 'sage' => '#9CAF88', 'sagegreen' => '#9CAF88', 'salem' => '#FA8072',
            'salmon' => '#FA8072', 'sand' => '#C2B280', 'sandybrown' => '#F4A460', 'seagreen' => '#2E8B57',
            'seashell' => '#FFF5EE', 'sienna' => '#A0522D', 'silver' => '#C0C0C0', 'skyblue' => '#87CEEB',
            'slate' => '#708090', 'slateblue' => '#6A5ACD', 'slategray' => '#708090', 'slategrey' => '#708090',
            'snow' => '#FFFAFA', 'springgreen' => '#00FF7F', 'steelblue' => '#4682B4', 'stone' => '#928E85',
            'tan' => '#D2B48C', 'taupe' => '#483C32', 'teal' => '#008080', 'terakota' => '#E2725B',
            'terracotta' => '#E2725B', 'thistle' => '#D8BFD8', 'tomato' => '#FF6347', 'tosca' => '#008080',
            'toska' => '#008080', 'transparan' => '#00000000', 'turkis' => '#40E0D0', 'turquoise' => '#40E0D0',
            'ungu' => '#800080', 'ungumuda' => '#E6E6FA', 'ungutua' => '#4B0082', 'unta' => '#C19A6B',
            'violet' => '#EE82EE', 'wheat' => '#F5DEB3', 'white' => '#FFFFFF', 'whitesmoke' => '#F5F5F5',
            'wine' => '#722F37', 'yellow' => '#FFFF00', 'yellowgreen' => '#9ACD32', 'zaitun' => '#808000',
            'zamrud' => '#50C878',

            // --- TAMBAHAN DARI MASTERCOLOR (Top 100+ Warna Paling Sering Muncul) ---
            'misty' => '#D1D5DB', 'coolgrey' => '#9CA3AF', 'blackjack' => '#111111', 'nocturnalnavy' => '#0B2046', 'navyblue' => '#000080',
            'brightwhite' => '#F8F9FA', 'callalily' => '#F8F9FA', 'inthenavy' => '#0B2046', 'darksable' => '#111111',
            'silverpink' => '#F472B6', 'collegiatenavy' => '#0B2046', 'stonegreen' => '#10B981', 'stonelake' => '#78716C',
            'dijon' => '#FFDB58', 'rainyday' => '#9CA3AF', 'darknavy' => '#051024', 'sycamore' => '#90A959',
            'sqknavy' => '#0B2046', 'cocamocha' => '#5C4033', 'blackwhite' => '#111111', 'industrialblue' => '#3B82F6',
            'lilac' => '#A855F7', 'ancientfossil' => '#E5E5CB', 'teakbrown' => '#78350F', 'deepblack' => '#000000',
            'peacoat' => '#1E3A8A', 'whitewithblacklittering' => '#F8F9FA', 'mistym' => '#D1D5DB', 'combocontrasbase' => '#D4D4D8',
            'redoxide' => '#EF4444', 'pastelgreen' => '#10B981', 'clear' => '#00000000', 'heathergrey' => '#9CA3AF',
            'darkbottle' => '#006A4E', 'desertsky' => '#E5E5CB', 'whiteblack' => '#F8F9FA', 'lavenderpearl' => '#A855F7',
            'melonade' => '#F97316', 'cabernet' => '#722F37', 'navywhite' => '#0B2046', 'vintageblue' => '#3B82F6',
            'royal' => '#1E3A8A', 'primaryred' => '#EF4444', 'gulfstream' => '#3B82F6', 'classicblue' => '#3B82F6',
            'monetblue' => '#3B82F6', 'lightheathergrey' => '#D1D5DB', 'midnight' => '#191970', 'flame' => '#F97316',
            'bluewingteal' => '#3B82F6', 'greige' => '#B5B3AA', 'egret' => '#F3F4F6', 'truenavy' => '#0B2046',
            'taupemarl' => '#483C32', 'pomegranate' => '#EF4444', 'silvergrey' => '#C0C0C0', 'bluehaze' => '#3B82F6',
            'zephyr' => '#D4D4D8', 'darkslate' => '#2F4F4F', 'pinkhaze' => '#F472B6', 'bunting' => '#3B82F6',
            'darkburgundy' => '#722F37', 'darkgreyheather' => '#4B5563', 'bluewash' => '#3B82F6', 'mineralred' => '#EF4444',
            'turtledove' => '#E5E5CB', 'bleachedaqua' => '#3B82F6', 'greyheather' => '#9CA3AF', 'darkpebbles' => '#78716C',
            'frenchblue' => '#3B82F6', 'chateaurose' => '#F472B6', 'tarmac' => '#111111', 'bikingred' => '#EF4444',
            'blackheather' => '#111111', 'mgrey' => '#9CA3AF', 'blackcombocontras' => '#111111', 'htrcharcoal' => '#36454F',
            'snowwhite' => '#FFFAFA', 'tomatopuree' => '#EF4444', 'storm' => '#9CA3AF', 'burntolive' => '#78350F',
            'fossil' => '#E5E5CB', 'sprout' => '#10B981', 'captainsblue' => '#3B82F6', 'rosin' => '#111111',
            'lightgreyheather' => '#D1D5DB', 'oceanblue' => '#3B82F6', 'winetasting' => '#722F37', 'mineralblue' => '#3B82F6',
            'coralsand' => '#E5E5CB', 'blackbeauty' => '#000000', 'blushingbride' => '#F472B6', 'tradewinds' => '#D4D4D8',
            'witheredrose' => '#F472B6', 'marshmallow' => '#F8F9FA', 'moonstruck' => '#D4D4D8', 'darkforest' => '#111111',
            'whitecombocontras' => '#F8F9FA', 'bluecoral' => '#3B82F6', 'nugget' => '#FBBF24', 'phantom' => '#111111'
        ];

            $colorHex = function ($name) use ($colorMap) {
                $key = strtolower(preg_replace('/[^a-zA-Z]/', '', $name));
                return $colorMap[$key] ?? '#d4d4d8';
            };

            $colors       = array_filter(array_map('trim', explode(',', $styleData->colors ?? '')));
            $sizes        = array_filter(array_map('trim', explode(',', $styleData->sizes ?? '')));
            $destinations = array_filter(array_map('trim', explode(',', $styleData->destinations ?? '')));
        @endphp

        <div class="catalog-wrapper">
            <div class="detail-grid">
                <section class="image-wrapper">
                    @if(!empty($styleData->image))
                        <img src="/nds_wip/public/uploads/costing/{{ $styleData->image }}"
                            alt="{{ $styleData->styleno }}"
                            style="width:100%;height:100%;object-fit:contain;object-position:center 15%;display:block;"
                            onerror="this.style.display='none';this.nextElementSibling.style.display='flex';">
                        <div class="no-image-placeholder" style="display:none;">
                            <div class="doodle-wrapper">
                                <div class="doodle-frame">
                                    <div class="doodle-sun"></div>
                                    <div class="doodle-mountain left"></div>
                                    <div class="doodle-mountain right"></div>
                                </div>
                                <span class="placeholder-text">No Image</span>
                            </div>
                        </div>
                    @else
                        <div class="no-image-placeholder">
                            <div class="doodle-wrapper">
                                <div class="doodle-frame">
                                    <div class="doodle-sun"></div>
                                    <div class="doodle-mountain left"></div>
                                    <div class="doodle-mountain right"></div>
                                </div>
                                <span class="placeholder-text">No Image</span>
                            </div>
                        </div>
                    @endif
                </section>

              <section class="info-wrapper">
                <div class="style-code">Style</div>
                <h1 class="product-title">{{ $styleData->styleno ?: '-' }}</h1>

                <div class="variant-group">
                  <div class="variant-label"><i class="fas fa-palette"></i>&nbsp; &nbsp;Colors</div>
                  @if(count($colors) > 0)
                      <div class="color-pill-container">
                          @foreach($colors as $color)
                              <span class="color-pill">
                                  <span class="color-indicator" style="background: {{ $colorHex($color) }};"></span>
                                  {{ $color }}
                              </span>
                          @endforeach
                      </div>
                  @else
                      <em class="text-muted" style="font-size: 0.8rem;">Belum ada warna yang didaftarkan</em>
                  @endif
                </div>

                <div class="variant-group">
                  <div class="variant-label"><i class="fas fa-ruler-vertical"></i>&nbsp;&nbsp;Sizes</div>
                  <div class="size-box-container">
                    @forelse($sizes as $s)
                    <div class="size-box" title="{{ $s }}">{{ $s }}</div>
                    @empty
                        <em class="text-muted" style="font-size: 0.8rem;">Belum ada ukuran yang didaftarkan</em>
                    @endforelse
                  </div>
                </div>

                <div class="variant-group">
                  <div class="variant-label"><i class="fas fa-map-marker-alt"></i> &nbsp; &nbsp;Destinations</div>
                  @if(count($destinations) > 0)
                      <div class="color-pill-container">
                          @foreach($destinations as $dest)
                              <span class="color-pill">{{ $dest }}</span>
                          @endforeach
                      </div>
                  @else
                      <em class="text-muted" style="font-size: 0.8rem;">Belum ada destinasi yang didaftarkan</em>
                  @endif
                </div>

                <div class="variant-group">
                  <div class="variant-label"><i class="fas fa-user"></i> &nbsp; &nbsp;Buyer</div>
                  <div style="font-size: 0.85rem; color: #111;">
                    {{ $styleData->buyer_name ?: '-' }}
                  </div>
                </div>
              </section>
            </div>

            <div class="tab-section">
              <nav class="tab-navigation">
                <button class="tab-button active" onclick="switchTab(event, 'bom-tab')">
                  <svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
                  Rincian Material (BOM)
                  <span class="tab-count">{{ count($bomData) }}</span>
                </button>
                <button class="tab-button" onclick="switchTab(event, 'so-tab')">
                  <svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                  Histori Sales Order
                  <span class="tab-count">{{ count($soHistory) }}</span>
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
                          <td colspan="4" class="empty-state">Belum ada rincian material (BOM) untuk Style ini.</td>
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
                          <td colspan="6" class="empty-state">Belum ada Sales Order yang terdaftar menggunakan Style ini.</td>
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
