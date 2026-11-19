{{-- Modal User Guide marketing (dipakai ulang di halaman mana pun).
     Pakai: tombol <button type="button" class="mkt-guide-btn" data-guide-open="ID">…</button> lalu
     @include('marketing.partials.user_guide', ['guideId' => 'ID', 'guideTitle' => '…', 'steps' => [[title, html, map (opsional), mock (opsional)], …]])
     map = nama bagian yang disorot di peta halaman (lihat $guideMap di view guide), mock = HTML contoh tampilan bagian itu.
     CSS: dist/css/marketing-guide.css, JS: dist/js/marketing-guide.js (dimuat di halaman pemakai). --}}
<div class="mkt-guide" id="{{ $guideId }}" hidden role="dialog" aria-modal="true" aria-labelledby="{{ $guideId }}-title">
    <div class="mkt-guide-box">
        <div class="mkt-guide-head">
            <div>
                <small><i class="fas fa-book-open"></i> User Guide</small>
                <h2 id="{{ $guideId }}-title">{{ $guideTitle }}</h2>
            </div>
            <button type="button" class="mkt-guide-x" data-guide-close aria-label="Tutup">&times;</button>
        </div>
        <ol class="mkt-guide-nav">
            @foreach ($steps as $i => $step)
                <li><button type="button" data-guide-go="{{ $i }}"><span>{{ $i + 1 }}</span>{{ $step['title'] }}</button></li>
            @endforeach
        </ol>
        <div class="mkt-guide-body">
            @foreach ($steps as $i => $step)
                <section class="mkt-guide-step" data-guide-step="{{ $i }}" hidden>
                    <div class="mkt-guide-visual">
                        @if (!empty($step['map']))
                            <figure class="mkt-guide-map">{!! $step['map'] !!}<figcaption>Posisi di halaman</figcaption></figure>
                        @endif
                        @if (!empty($step['mock']))
                            <figure class="mkt-guide-mock">{!! $step['mock'] !!}<figcaption>Contoh isian</figcaption></figure>
                        @endif
                    </div>
                    <div class="mkt-guide-text">
                        <h3><span>{{ $i + 1 }}</span>{{ $step['title'] }}</h3>
                        {!! $step['html'] !!}
                    </div>
                </section>
            @endforeach
        </div>
        <div class="mkt-guide-foot">
            <span class="mkt-guide-count" data-guide-count></span>
            <span class="mkt-guide-sp"></span>
            <button type="button" class="mkt-btn mkt-btn--gray mkt-btn--sm" data-guide-prev>&larr; Sebelumnya</button>
            <button type="button" class="mkt-btn mkt-btn--navy mkt-btn--sm" data-guide-next>Berikutnya &rarr;</button>
        </div>
    </div>
</div>
