{{-- ===========================================================================
     Kepala halaman + tab New / Old Master Item General.
     Dua tab = dua halaman (controller, query & database terpisah):
       New -> MasterItemNewController (database nds)
       Old -> MasterItemController   (masteritem di SB)
     Parameter: $tabAktif = 'new' | 'old'
     Tiap tab berupa kartu sendiri dengan warna sendiri (New hijau, Old oranye),
     tab yang terbuka diberi garis tebal + bayangan, yang lain dibuat redup.
     =========================================================================== --}}
<style>
.nag-skin .mi-tab-bar {
  display: flex;
  flex-wrap: wrap;
  gap: 12px;
  margin: -2px 0 18px;
}
.nag-skin .mi-tab {
  --mi-warna: #1e3a5f;
  --mi-lembut: #eef3f9;
  position: relative;
  flex: 0 1 280px;
  min-width: 0;
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 12px 16px;
  background: #f8fafc;
  border: 1px solid #dfe5ee;
  border-radius: 14px;
  color: #64748b;
  text-decoration: none;
  opacity: .78;
  transition: opacity .15s, box-shadow .15s, transform .15s, border-color .15s, background .15s;
}
.nag-skin .mi-tab.is-new { --mi-warna: #15803d; --mi-lembut: #ecfdf3; }
.nag-skin .mi-tab.is-old { --mi-warna: #c2410c; --mi-lembut: #fff4ec; }
.nag-skin .mi-tab:hover {
  opacity: 1;
  color: #334155;
  border-color: var(--mi-warna);
  transform: translateY(-1px);
  text-decoration: none;
}
.nag-skin .mi-tab.is-aktif {
  opacity: 1;
  background: var(--mi-lembut);
  border: 2px solid var(--mi-warna);
  padding: 11px 15px;
  color: #0f2942;
  box-shadow: 0 10px 22px -12px var(--mi-warna);
  transform: none;
}
/* Penanda tab yang terbuka: segitiga kecil di bawah kartu */
.nag-skin .mi-tab.is-aktif::after {
  content: '';
  position: absolute;
  left: 30px;
  bottom: -8px;
  width: 14px;
  height: 14px;
  background: var(--mi-lembut);
  border-right: 2px solid var(--mi-warna);
  border-bottom: 2px solid var(--mi-warna);
  transform: rotate(45deg);
}
.nag-skin .mi-tab-ikon {
  flex: 0 0 auto;
  width: 38px;
  height: 38px;
  border-radius: 11px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  background: #e2e8f0;
  color: #64748b;
  font-size: 15px;
}
.nag-skin .mi-tab.is-aktif .mi-tab-ikon { background: var(--mi-warna); color: #fff; }
.nag-skin .mi-tab-teks { min-width: 0; display: flex; flex-direction: column; gap: 2px; }
.nag-skin .mi-tab-judul { display: flex; align-items: center; gap: 8px; font-size: 14px; font-weight: 700; line-height: 1.2; }
.nag-skin .mi-tab-ket { font-size: 11.5px; color: #64748b; line-height: 1.3; }
.nag-skin .mi-tab-label {
  padding: 2px 8px;
  border-radius: 999px;
  font-size: 10px;
  font-weight: 800;
  letter-spacing: .4px;
  text-transform: uppercase;
  background: #e2e8f0;
  color: #64748b;
}
.nag-skin .mi-tab.is-aktif .mi-tab-label { background: var(--mi-warna); color: #fff; }
.nag-skin .mi-tab-cek { margin-left: auto; color: var(--mi-warna); font-size: 16px; display: none; }
.nag-skin .mi-tab.is-aktif .mi-tab-cek { display: block; }

@media (max-width: 575px) {
  .nag-skin .mi-tab { flex: 1 1 100%; }
}
</style>

<div class="dn-kepala-halaman">
    <div class="dn-kepala-kiri">
        <span class="dn-kepala-ikon"><i class="fas fa-boxes-stacked"></i></span>
        <div>
            <h1 class="dn-kepala-judul">Master Item General</h1>
            <p class="dn-kepala-sub">List of general (non-production) items</p>
        </div>
    </div>
</div>

<nav class="mi-tab-bar" aria-label="Master Item General version">
    <a href="{{ route('master-data-item-new') }}" class="mi-tab is-new {{ $tabAktif === 'new' ? 'is-aktif' : '' }}"
        @if ($tabAktif === 'new') aria-current="page" @endif>
        <span class="mi-tab-ikon"><i class="fas fa-layer-group"></i></span>
        <span class="mi-tab-teks">
            <span class="mi-tab-judul">Item General <span class="mi-tab-label">New</span></span>
            <span class="mi-tab-ket">New structure - database NDS</span>
        </span>
        <i class="fas fa-circle-check mi-tab-cek"></i>
    </a>
    <a href="{{ route('master-data-item') }}" class="mi-tab is-old {{ $tabAktif === 'old' ? 'is-aktif' : '' }}"
        @if ($tabAktif === 'old') aria-current="page" @endif>
        <span class="mi-tab-ikon"><i class="fas fa-box-archive"></i></span>
        <span class="mi-tab-teks">
            <span class="mi-tab-judul">Item General <span class="mi-tab-label">Old</span></span>
            <span class="mi-tab-ket">Old structure - Signalbit</span>
        </span>
        <i class="fas fa-circle-check mi-tab-cek"></i>
    </a>
</nav>
