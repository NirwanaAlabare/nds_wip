@extends('layouts.index')

@section('custom-link')
{{-- Skin yang sama dengan daftar Master Data (font, warna dasar) --}}
@include('export-import.invoice._skin')
<style>
/* =====================================================================
   Dashboard Master Data
   Banner (sapaan, total, pencarian cepat) + kartu donut per daftar.
   Animasi sengaja tidak dibatasi prefers-reduced-motion (PC kantor
   mematikan animation effects, tapi animasi tetap diminta tampil).
   ===================================================================== */
.md-dash { max-width: 1200px; margin: 0 auto; padding-bottom: 24px; }

/* ---------- Banner ---------- */
.md-hero {
  position: relative;
  overflow: hidden;
  padding: 26px 28px;
  margin-bottom: 22px;
  border-radius: 20px;
  color: #fff;
  background: linear-gradient(120deg, #0f2942 0%, #1e3a5f 45%, #2c5f94 100%);
  box-shadow: 0 18px 40px -22px rgba(15, 41, 66, .7);
  animation: md-muncul .5s ease both;
}
/* Lingkaran dekorasi yang bergerak pelan */
.md-hero::before,
.md-hero::after {
  content: "";
  position: absolute;
  border-radius: 50%;
  pointer-events: none;
  background: radial-gradient(circle, rgba(255, 255, 255, .16), rgba(255, 255, 255, 0) 70%);
}
.md-hero::before { width: 340px; height: 340px; top: -150px; right: -60px; animation: md-melayang 9s ease-in-out infinite; }
.md-hero::after  { width: 220px; height: 220px; bottom: -120px; right: 260px; animation: md-melayang 11s ease-in-out infinite reverse; }
@keyframes md-melayang {
  0%, 100% { transform: translate(0, 0); }
  50%      { transform: translate(-24px, 16px); }
}

.md-hero-isi {
  position: relative;
  z-index: 1;
  display: grid;
  grid-template-columns: minmax(0, 1fr) minmax(0, 440px);
  gap: 24px;
  align-items: center;
}
@media (max-width: 991.98px) { .md-hero-isi { grid-template-columns: 1fr; } }

.md-hero-sapa { margin: 0 0 4px; font-size: 13px; color: #bcd0e8; }
.md-hero-judul { margin: 0; font-size: 24px; font-weight: 700; letter-spacing: -.3px; line-height: 1.2; }
.md-hero-ringkas { display: flex; flex-wrap: wrap; gap: 10px; margin-top: 16px; }
.md-hero-chip {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 7px 12px;
  border-radius: 12px;
  background: rgba(255, 255, 255, .1);
  border: 1px solid rgba(255, 255, 255, .14);
  font-size: 12px;
  color: #dbe7f5;
  backdrop-filter: blur(4px);
}
.md-hero-chip b { font-size: 15px; color: #fff; font-variant-numeric: tabular-nums; }

/* Pencarian cepat */
.md-cari {
  padding: 14px;
  border-radius: 16px;
  background: rgba(255, 255, 255, .1);
  border: 1px solid rgba(255, 255, 255, .16);
  backdrop-filter: blur(6px);
}
.md-cari-label { display: block; margin-bottom: 8px; font-size: 12px; color: #bcd0e8; }
.md-cari-kotak {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 4px 4px 4px 12px;
  border-radius: 12px;
  background: #fff;
  box-shadow: 0 6px 16px -10px rgba(0, 0, 0, .5);
  transition: box-shadow .2s ease;
}
.md-cari-kotak:focus-within { box-shadow: 0 0 0 3px rgba(147, 197, 253, .55), 0 6px 16px -10px rgba(0, 0, 0, .5); }
.md-cari-kotak > i { color: #94a3b8; font-size: 13px; }
.md-cari-kotak input {
  flex: 1 1 auto;
  min-width: 0;
  border: 0;
  outline: 0;
  padding: 7px 0;
  font-size: 13.5px;
  color: #0f2942;
  background: transparent;
}
.md-cari-kunci {
  padding: 2px 7px;
  border-radius: 6px;
  border: 1px solid #e2e8f0;
  background: #f8fafc;
  color: #94a3b8;
  font-size: 11px;
  font-family: inherit;
}
.md-cari-tombol {
  border: 0;
  padding: 7px 14px;
  border-radius: 9px;
  background: #1e3a5f;
  color: #fff;
  font-size: 12.5px;
  font-weight: 600;
  transition: background .15s ease, transform .15s ease;
}
.md-cari-tombol:hover { background: #2c5f94; }
.md-cari-tombol:active { transform: scale(.96); }

/* Pilihan daftar tujuan: segmen dengan penanda yang bergeser */
.md-cakupan {
  position: relative;
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  margin-top: 10px;
  padding: 3px;
  border-radius: 10px;
  background: rgba(15, 41, 66, .35);
}
.md-cakupan-penanda {
  position: absolute;
  top: 3px;
  bottom: 3px;
  left: 3px;
  width: calc((100% - 6px) / 3);
  border-radius: 8px;
  background: rgba(255, 255, 255, .95);
  transition: transform .3s cubic-bezier(.22, 1, .36, 1);
}
.md-cakupan button {
  position: relative;
  z-index: 1;
  border: 0;
  padding: 6px 4px;
  background: transparent;
  color: #dbe7f5;
  font-size: 12px;
  font-weight: 600;
  transition: color .2s ease;
}
.md-cakupan button i { margin-right: 4px; font-size: 11px; }
.md-cakupan button.is-pilih { color: #1e3a5f; }

/* ---------- Kartu ---------- */
.md-dash-grid {
  display: grid;
  grid-template-columns: repeat(3, minmax(0, 1fr));
  gap: 18px;
}
@media (max-width: 991.98px) { .md-dash-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
@media (max-width: 575.98px) {
  .md-dash-grid { grid-template-columns: 1fr; }
  .md-hero { padding: 20px 18px; }
}

.md-kartu { --aksen: #1e3a5f; --aksen-muda: #e8eef6; --mx: 50%; --my: 0%; }
.md-kartu.is-customer { --aksen: #2563eb; --aksen-muda: #e7efff; }
.md-kartu.is-supplier { --aksen: #0d9488; --aksen-muda: #e3f5f2; }
.md-kartu.is-item     { --aksen: #7c3aed; --aksen-muda: #f0e9ff; }

.md-kartu {
  position: relative;
  display: flex;
  flex-direction: column;
  gap: 16px;
  padding: 20px;
  background: #fff;
  border: 1px solid #e5eaf1;
  border-radius: 18px;
  box-shadow: 0 1px 2px rgba(15, 41, 66, .04);
  overflow: hidden;
  cursor: pointer;
  transition: transform .2s ease, box-shadow .2s ease, border-color .2s ease;
  animation: md-muncul .5s ease both;
}
.md-kartu:nth-child(1) { animation-delay: .08s; }
.md-kartu:nth-child(2) { animation-delay: .16s; }
.md-kartu:nth-child(3) { animation-delay: .24s; }
.md-kartu::before {
  content: "";
  position: absolute;
  inset: 0 0 auto 0;
  height: 3px;
  background: var(--aksen);
}
/* Sorotan lembut yang mengikuti kursor */
.md-kartu::after {
  content: "";
  position: absolute;
  inset: 0;
  pointer-events: none;
  opacity: 0;
  background: radial-gradient(360px circle at var(--mx) var(--my), color-mix(in srgb, var(--aksen) 9%, transparent), transparent 60%);
  transition: opacity .25s ease;
}
.md-kartu:hover { transform: translateY(-4px); box-shadow: 0 18px 36px -18px rgba(15, 41, 66, .32); border-color: color-mix(in srgb, var(--aksen) 30%, #e5eaf1); }
.md-kartu:hover::after { opacity: 1; }
.md-kartu:focus-visible { outline: 3px solid color-mix(in srgb, var(--aksen) 40%, transparent); outline-offset: 2px; }
.md-kartu > * { position: relative; z-index: 1; }
@keyframes md-muncul {
  from { opacity: 0; transform: translateY(12px); }
  to   { opacity: 1; transform: none; }
}

.md-kartu-atas { display: flex; align-items: center; gap: 12px; }
.md-kartu-ikon {
  flex: 0 0 auto;
  width: 44px;
  height: 44px;
  border-radius: 12px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  background: var(--aksen-muda);
  color: var(--aksen);
  font-size: 18px;
  transition: transform .3s cubic-bezier(.34, 1.56, .64, 1);
}
.md-kartu:hover .md-kartu-ikon { transform: rotate(-8deg) scale(1.08); }
.md-kartu-judul { margin: 0; font-size: 15px; font-weight: 700; color: #0f2942; line-height: 1.2; }
.md-kartu-sub   { margin: 2px 0 0; font-size: 12px; color: #64748b; }
.md-kartu-persen-aktif {
  margin-left: auto;
  padding: 3px 9px;
  border-radius: 999px;
  background: #ecfdf3;
  color: #15803d;
  font-size: 11.5px;
  font-weight: 700;
  white-space: nowrap;
}

/* Donut + legenda */
.md-kartu-tengah { display: flex; align-items: center; gap: 18px; }
.md-donut { position: relative; flex: 0 0 auto; width: 128px; height: 128px; }
.md-donut svg { width: 100%; height: 100%; transform: rotate(-90deg); overflow: visible; }
.md-donut .md-jalur { fill: none; stroke: #eef2f7; stroke-width: 12; }
.md-donut .md-ruas {
  fill: none;
  stroke-width: 12;
  stroke-linecap: butt;
  cursor: pointer;
  transition: stroke-dasharray 1s cubic-bezier(.22, 1, .36, 1), stroke-width .2s ease, opacity .2s ease;
}
.md-donut .md-ruas.is-aktif    { stroke: #16a34a; }
.md-donut .md-ruas.is-nonaktif { stroke: #cbd5e1; }
.md-donut .md-ruas.is-blokir   { stroke: #334155; }
.md-kartu[data-fokus] .md-ruas { opacity: .3; }
.md-kartu[data-fokus="aktif"] .md-ruas.is-aktif,
.md-kartu[data-fokus="nonaktif"] .md-ruas.is-nonaktif,
.md-kartu[data-fokus="blokir"] .md-ruas.is-blokir { opacity: 1; stroke-width: 16; }

.md-donut-tengah {
  position: absolute;
  inset: 0;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  text-align: center;
  pointer-events: none;
}
.md-donut-angka {
  font-size: 22px;
  font-weight: 700;
  letter-spacing: -.6px;
  color: #0f2942;
  line-height: 1.1;
  font-variant-numeric: tabular-nums;
}
.md-donut-label { font-size: 11px; color: #64748b; margin-top: 2px; transition: color .2s ease; }
.md-donut-tengah.is-ganti { animation: md-ganti .25s ease; }
@keyframes md-ganti {
  from { opacity: .2; transform: scale(.92); }
  to   { opacity: 1; transform: none; }
}

.md-rinci { flex: 1 1 auto; min-width: 0; display: flex; flex-direction: column; gap: 2px; margin: 0; padding: 0; list-style: none; }
.md-rinci a {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 7px 9px;
  border-radius: 9px;
  font-size: 13px;
  color: #334155;
  text-decoration: none;
  transition: background .15s ease, transform .15s ease;
}
.md-rinci a:hover,
.md-rinci a.is-fokus { background: #f4f7fb; transform: translateX(3px); }
.md-titik { width: 9px; height: 9px; border-radius: 3px; flex: 0 0 auto; }
.md-titik.is-aktif    { background: #16a34a; }
.md-titik.is-nonaktif { background: #cbd5e1; }
.md-titik.is-blokir   { background: #334155; }
.md-rinci-nilai { margin-left: auto; font-weight: 700; color: #0f2942; font-variant-numeric: tabular-nums; }
.md-rinci-panah { font-size: 10px; color: #cbd5e1; transition: color .15s ease, transform .15s ease; }
.md-rinci a:hover .md-rinci-panah { color: var(--aksen); transform: translateX(2px); }

/* Aksi bawah */
.md-kartu-bawah {
  display: flex;
  align-items: center;
  gap: 8px;
  padding-top: 14px;
  border-top: 1px dashed #e5eaf1;
}
.md-tautan {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  font-size: 12.5px;
  font-weight: 600;
  color: var(--aksen);
  text-decoration: none;
}
.md-tautan i { transition: transform .2s ease; }
.md-kartu:hover .md-tautan i { transform: translateX(4px); }
.md-tombol-baru {
  margin-left: auto;
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 6px 12px;
  border-radius: 9px;
  background: var(--aksen-muda);
  color: var(--aksen);
  font-size: 12px;
  font-weight: 600;
  text-decoration: none;
  transition: background .15s ease, color .15s ease, transform .15s ease;
}
.md-tombol-baru:hover { background: var(--aksen); color: #fff; }
.md-tombol-baru:active { transform: scale(.95); }

/* Tooltip ruas donut */
.md-tip {
  position: fixed;
  z-index: 2000;
  padding: 6px 10px;
  border-radius: 8px;
  background: #0f2942;
  color: #fff;
  font-size: 12px;
  pointer-events: none;
  white-space: nowrap;
  opacity: 0;
  transform: translate(-50%, calc(-100% - 10px));
  transition: opacity .12s ease;
  box-shadow: 0 8px 20px -8px rgba(0, 0, 0, .45);
}
.md-tip.is-tampil { opacity: 1; }
.md-tip b { font-variant-numeric: tabular-nums; }
</style>
@endsection

@section('content')
{{-- ===========================================================================
     Dashboard Master Data.
     - Banner: sapaan, total semua data, pencarian cepat ke daftar Customer / Supplier / Item
       (dibuka dengan ?cari=, daftar memakai nilai itu sebagai pencarian awal). Tombol "/" = fokus cari.
     - Kartu per daftar: donut Active / Inactive (/ Blocked). Sorot ruas atau baris legenda untuk
       melihat angkanya di tengah donut; klik baris membuka daftar dengan filter status itu.
     =========================================================================== --}}
@php
    $semua = collect($kartu)->sum(fn ($k) => $k['angka']['total']);
    $aktifSemua = collect($kartu)->sum(fn ($k) => $k['angka']['aktif']);
    $persenAktifSemua = $semua > 0 ? round($aktifSemua / $semua * 100, 1) : 0;
@endphp
<div class="nag-skin">
    <div class="md-dash">

        {{-- ===== Banner ===== --}}
        <section class="md-hero">
            <div class="md-hero-isi">
                <div>
                    <p class="md-hero-sapa"><span id="md-sapa">Hello</span>{{ $nama !== '' ? ', ' . $nama : '' }}</p>
                    <h1 class="md-hero-judul">Master Data</h1>
                    <div class="md-hero-ringkas">
                        <span class="md-hero-chip"><i class="fa-solid fa-layer-group"></i> <b data-angka="{{ $semua }}">{{ number_format($semua) }}</b> total records</span>
                        <span class="md-hero-chip"><i class="fa-solid fa-circle-check"></i> <b>{{ $persenAktifSemua }}%</b> active</span>
                    </div>
                </div>

                <form class="md-cari" id="md-cari" autocomplete="off">
                    <label class="md-cari-label" for="md-cari-input">Quick search</label>
                    <div class="md-cari-kotak">
                        <i class="fa-solid fa-magnifying-glass"></i>
                        <input type="text" id="md-cari-input" maxlength="100" placeholder="Code, name, NPWP…">
                        <kbd class="md-cari-kunci" title="Press / to search">/</kbd>
                        <button type="submit" class="md-cari-tombol">Search</button>
                    </div>
                    <div class="md-cakupan" id="md-cakupan" role="tablist">
                        <span class="md-cakupan-penanda"></span>
                        @foreach ($kartu as $i => $k)
                            <button type="button" role="tab" data-href="{{ route($k['rute']) }}" class="{{ $i === 0 ? 'is-pilih' : '' }}" aria-selected="{{ $i === 0 ? 'true' : 'false' }}">
                                <i class="fa-solid {{ $k['ikon'] }}"></i>{{ $k['judul'] }}
                            </button>
                        @endforeach
                    </div>
                </form>
            </div>
        </section>

        {{-- ===== Kartu per daftar ===== --}}
        <div class="md-dash-grid">
            @foreach ($kartu as $k)
                @php
                    $a = $k['angka'];
                    $persen = fn ($n) => $a['total'] > 0 ? round($n / $a['total'] * 100, 1) : 0;
                    $status = [
                        ['kelas' => 'aktif', 'label' => 'Active', 'nilai' => $a['aktif']],
                        ['kelas' => 'nonaktif', 'label' => 'Inactive', 'nilai' => $a['nonaktif']],
                    ];
                    if ($a['adaBlokir']) {
                        $status[] = ['kelas' => 'blokir', 'label' => 'Blocked', 'nilai' => $a['blokir']];
                    }
                @endphp
                <div class="md-kartu is-{{ $k['kunci'] }}" role="link" tabindex="0" data-href="{{ route($k['rute']) }}" data-total="{{ $a['total'] }}">
                    <div class="md-kartu-atas">
                        <span class="md-kartu-ikon"><i class="fa-solid {{ $k['ikon'] }}"></i></span>
                        <div>
                            <h2 class="md-kartu-judul">{{ $k['judul'] }}</h2>
                            <p class="md-kartu-sub">{{ $k['sub'] }}</p>
                        </div>
                        <span class="md-kartu-persen-aktif" title="Active rate">{{ $persen($a['aktif']) }}% active</span>
                    </div>

                    <div class="md-kartu-tengah">
                        <div class="md-donut">
                            <svg viewBox="0 0 120 120" aria-hidden="true">
                                <circle class="md-jalur" cx="60" cy="60" r="46"></circle>
                                @foreach ($status as $s)
                                    @if ($s['nilai'] > 0)
                                        <circle class="md-ruas is-{{ $s['kelas'] }}" cx="60" cy="60" r="46"
                                            data-status="{{ $s['kelas'] }}" data-label="{{ $s['label'] }}"
                                            data-nilai="{{ $s['nilai'] }}" data-persen="{{ $persen($s['nilai']) }}"></circle>
                                    @endif
                                @endforeach
                            </svg>
                            <div class="md-donut-tengah">
                                <span class="md-donut-angka" data-angka="{{ $a['total'] }}">{{ number_format($a['total']) }}</span>
                                <span class="md-donut-label">records</span>
                            </div>
                        </div>

                        <ul class="md-rinci">
                            @foreach ($status as $s)
                                <li>
                                    <a href="{{ route($k['rute'], ['status' => $s['kelas']]) }}"
                                        data-status="{{ $s['kelas'] }}" data-label="{{ $s['label'] }}"
                                        data-nilai="{{ $s['nilai'] }}" data-persen="{{ $persen($s['nilai']) }}"
                                        title="Open {{ $k['judul'] }} list: {{ $s['label'] }}">
                                        <span class="md-titik is-{{ $s['kelas'] }}"></span>
                                        {{ $s['label'] }}
                                        <span class="md-rinci-nilai">{{ number_format($s['nilai']) }}</span>
                                        <i class="fa-solid fa-chevron-right md-rinci-panah"></i>
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </div>

                    <div class="md-kartu-bawah">
                        <a href="{{ route($k['rute']) }}" class="md-tautan">View list <i class="fa-solid fa-arrow-right"></i></a>
                        <a href="{{ $k['buat'] }}" class="md-tombol-baru"><i class="fa-solid fa-plus"></i> New</a>
                    </div>
                </div>
            @endforeach
        </div>

    </div>
</div>
<div class="md-tip" id="md-tip"></div>
@endsection

@push('scripts')
<script>
(function () {
    var format = new Intl.NumberFormat('en-US');

    // ---- Sapaan sesuai jam di komputer pengguna ----
    var jam = new Date().getHours();
    document.getElementById('md-sapa').textContent =
        jam < 11 ? 'Good morning' : jam < 15 ? 'Good afternoon' : jam < 19 ? 'Good evening' : 'Good night';

    // ---- Hitung naik untuk semua angka ber-data-angka ----
    function hitungNaik(el, akhir, lama) {
        if (!akhir) { el.textContent = '0'; return; }
        var mulai = null;
        el.textContent = '0';
        function langkah(t) {
            if (mulai === null) mulai = t;
            var p = Math.min((t - mulai) / lama, 1);
            el.textContent = format.format(Math.round(akhir * (1 - Math.pow(1 - p, 3))));
            if (p < 1) requestAnimationFrame(langkah);
        }
        requestAnimationFrame(langkah);
    }
    document.querySelectorAll('[data-angka]').forEach(function (el) {
        hitungNaik(el, parseInt(el.dataset.angka, 10) || 0, 1000);
    });

    // ---- Donut: ruas digambar dengan stroke-dasharray, lalu dianimasikan dari 0 ----
    var KELILING = 2 * Math.PI * 46;
    document.querySelectorAll('.md-donut svg').forEach(function (svg) {
        var ruas = svg.querySelectorAll('.md-ruas');
        var celah = ruas.length > 1 ? 2.5 : 0;
        var total = 0;
        ruas.forEach(function (r) { total += parseInt(r.dataset.nilai, 10); });
        // Ruas kecil tetap terlihat (minimal 2% lingkaran); angka di legenda/tooltip tetap angka asli
        var bobot = [];
        ruas.forEach(function (r) { bobot.push(Math.max(parseInt(r.dataset.nilai, 10) / total, ruas.length > 1 ? 0.02 : 0)); });
        var jumlahBobot = bobot.reduce(function (a, b) { return a + b; }, 0);
        var posisi = 0;
        ruas.forEach(function (r, i) {
            var panjang = Math.max(bobot[i] / jumlahBobot * KELILING - celah, 0.5);
            r.style.strokeDasharray = '0 ' + KELILING;
            r.style.strokeDashoffset = -posisi;
            r.dataset.panjang = panjang;
            posisi += panjang + celah;
        });
    });
    requestAnimationFrame(function () {
        requestAnimationFrame(function () {
            document.querySelectorAll('.md-ruas').forEach(function (r) {
                r.style.strokeDasharray = r.dataset.panjang + ' ' + KELILING;
            });
        });
    });

    // ---- Fokus status: sorot ruas + baris legenda, angka di tengah donut berganti ----
    function setFokus(kartu, el) {
        var tengah = kartu.querySelector('.md-donut-tengah');
        var angka = tengah.querySelector('.md-donut-angka');
        var label = tengah.querySelector('.md-donut-label');
        var status = el ? el.dataset.status : null;
        if ((kartu.dataset.fokus || null) === status) return;

        kartu.querySelectorAll('.md-rinci a').forEach(function (a) { a.classList.toggle('is-fokus', a.dataset.status === status); });
        if (status) {
            kartu.dataset.fokus = status;
            angka.textContent = format.format(el.dataset.nilai);
            label.textContent = el.dataset.label + ' · ' + el.dataset.persen + '%';
        } else {
            delete kartu.dataset.fokus;
            angka.textContent = format.format(kartu.dataset.total);
            label.textContent = 'records';
        }
        tengah.classList.remove('is-ganti');
        void tengah.offsetWidth;
        tengah.classList.add('is-ganti');
    }

    var tip = document.getElementById('md-tip');
    document.querySelectorAll('.md-kartu').forEach(function (kartu) {
        // Sorotan mengikuti kursor
        kartu.addEventListener('mousemove', function (e) {
            var kotak = kartu.getBoundingClientRect();
            kartu.style.setProperty('--mx', (e.clientX - kotak.left) + 'px');
            kartu.style.setProperty('--my', (e.clientY - kotak.top) + 'px');
        });

        kartu.querySelectorAll('.md-rinci a, .md-ruas').forEach(function (el) {
            el.addEventListener('mouseenter', function () { setFokus(kartu, el); });
            el.addEventListener('focus', function () { setFokus(kartu, el); });
        });
        kartu.querySelector('.md-kartu-tengah').addEventListener('mouseleave', function () { setFokus(kartu, null); });
        kartu.querySelectorAll('.md-rinci a').forEach(function (a) {
            a.addEventListener('blur', function () { setFokus(kartu, null); });
        });

        // Tooltip & klik pada ruas donut
        kartu.querySelectorAll('.md-ruas').forEach(function (r) {
            r.addEventListener('mousemove', function (e) {
                tip.innerHTML = r.dataset.label + ': <b>' + format.format(r.dataset.nilai) + '</b> (' + r.dataset.persen + '%)';
                tip.style.left = e.clientX + 'px';
                tip.style.top = e.clientY + 'px';
                tip.classList.add('is-tampil');
            });
            r.addEventListener('mouseleave', function () { tip.classList.remove('is-tampil'); });
            r.addEventListener('click', function (e) {
                e.stopPropagation();
                var a = kartu.querySelector('.md-rinci a[data-status="' + r.dataset.status + '"]');
                if (a) window.location.href = a.href;
            });
        });

        // Kartu bisa diklik (bukan <a> karena di dalamnya ada link lain)
        kartu.addEventListener('click', function (e) {
            if (e.target.closest('a')) return;
            window.location.href = kartu.dataset.href;
        });
        kartu.addEventListener('keydown', function (e) {
            if (e.key === 'Enter' && e.target === kartu) window.location.href = kartu.dataset.href;
        });
    });

    // ---- Pencarian cepat ----
    var cakupan = document.getElementById('md-cakupan');
    var tombolCakupan = cakupan.querySelectorAll('button');
    var penanda = cakupan.querySelector('.md-cakupan-penanda');
    var input = document.getElementById('md-cari-input');

    function pilihCakupan(i) {
        tombolCakupan.forEach(function (b, j) {
            b.classList.toggle('is-pilih', i === j);
            b.setAttribute('aria-selected', i === j ? 'true' : 'false');
        });
        penanda.style.transform = 'translateX(' + (i * 100) + '%)';
        try { localStorage.setItem('md-dash-cakupan', i); } catch (e) {}
    }
    tombolCakupan.forEach(function (b, i) {
        b.addEventListener('click', function () { pilihCakupan(i); input.focus(); });
    });
    try {
        var simpan = parseInt(localStorage.getItem('md-dash-cakupan'), 10);
        if (simpan >= 0 && simpan < tombolCakupan.length) pilihCakupan(simpan);
    } catch (e) {}

    document.getElementById('md-cari').addEventListener('submit', function (e) {
        e.preventDefault();
        var tujuan = cakupan.querySelector('button.is-pilih').dataset.href;
        var kata = input.value.trim();
        window.location.href = kata ? tujuan + '?cari=' + encodeURIComponent(kata) : tujuan;
    });

    // "/" = fokus ke pencarian; Tab / panah kiri-kanan saat di kotak cari tidak diganggu
    document.addEventListener('keydown', function (e) {
        if (e.key === '/' && !/^(INPUT|TEXTAREA|SELECT)$/.test(document.activeElement.tagName)) {
            e.preventDefault();
            input.focus();
        }
    });
})();
</script>
@endpush
