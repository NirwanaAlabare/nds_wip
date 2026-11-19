{{--
    Langkah tur panduan halaman Alokasi (partials/guide-allocate).
    Parameter : $variant = 'trolley' | 'this', $tableVar = nama variabel datatable di halaman
    Butuh : partials/page-guide-script sudah di-include sebelumnya.
--}}
@php
    $isPick = ($variant ?? 'this') === 'trolley';
@endphp
<script>
    (function () {
        const emptyTable = '{{ $isPick ? 'Pilih trolley dulu, lalu isi trolley tampil di tabel.' : 'Trolley ini belum berisi stok.' }}';

        const steps = [
            { sec: 'pga-atas', sel: '.st-head-r', lbl: 'Tombol di kanan atas',
              d: '<b>Panduan</b> membuka panduan ini · <b>Tampilan lama</b> membuka tampilan sebelumnya · <b>Kembali</b> ke daftar stok trolley{!! $isPick ? '' : ' · <b>Kirim Stok Trolley</b> untuk mengirim isi trolley ini ke line' !!}.' },
            @if ($isPick)
                { sec: 'pga-trolley', sel: '#pg-card-trolley', lbl: 'Pilih trolley',
                  d: 'Pilih trolley di <b>List Trolley</b> atau tekan <b>Scan</b> untuk scan QR trolley. Tabel di bawah menampilkan isinya.' },
            @endif
            { sec: 'pga-scan', sel: '#pg-card-stocker', lbl: 'Scan stocker',
              d: 'Scan atau ketik No. Stocker lalu <kbd>Enter</kbd>. Data terisi dan <b>langsung tersimpan</b>, seluruh bundle-nya ikut masuk trolley.' },
            { sec: 'pga-cari', sel: '#stock-toolbar', lbl: 'Cari & filter',
              d: '<b>Cari No. Stocker</b> langsung menyaring saat mengetik. <b>Filter</b> untuk WS, No. Cut, Style, Color, Panel, Size, Part.' },
            { sec: 'pga-tabel', sel: '#trolley-stock-datatable thead', lbl: 'Kolom tabel stok',
              d: 'Satu baris = satu grup stocker. <b>Qty</b> = qty terkecil di grup. Garis merah tipis di No. Stocker = ada selisih qty minus.' },
            { sec: 'pga-hapus', sel: '#trolley-stock-datatable tbody .btn-danger', lbl: 'Keluarkan stok dari trolley',
              d: 'Tombol hapus mengeluarkan <b>seluruh grup</b> di baris itu dari trolley, setelah konfirmasi.', missing: emptyTable },
            { sec: 'pga-detail', sel: '#trolley-stock-datatable .stocker-detail-link', lbl: 'Detail transaksi stocker',
              d: 'Klik No. Stocker untuk melihat riwayat DC In sampai Secondary dan <b>qty terakhir</b> tiap stocker di grupnya.', missing: emptyTable },
            { sec: 'pga-ringkas', sel: '#stock-summary', lbl: 'Ringkasan',
              d: 'Jumlah stok dan <b>Total Qty</b> dari baris yang sedang tampil. Saat filter aktif, ringkasan pindah ke bar bawah bersama chip filter.' },
        ];

        const guide = initPageGuide({
            modal: '#pgGuide',
            button: '#pg-guide-btn',
            tourKey: 'pg_tour_{{ $isPick ? 'allocate_trolley' : 'allocate_this_trolley' }}_{{ Auth::id() }}',
            steps: steps,
        });

        // Tur otomatis setelah data tabel pertama kali tampil
        // (dirujuk langsung : variabel 'let' di skrip halaman tidak ada di window)
        let table = (typeof {{ $tableVar }} !== 'undefined') ? {{ $tableVar }} : null;

        if (table && table.ajax && table.ajax.json()) {
            guide.autoTour();
        } else if (table) {
            table.one('draw.dt', () => guide.autoTour());
        } else if (document.readyState === 'complete') {
            guide.autoTour();
        } else {
            $(window).on('load', () => guide.autoTour());
        }
    })();
</script>
