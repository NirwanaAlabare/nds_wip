{{--
    Panduan halaman Alokasi Trolley.
    Parameter : $variant = 'trolley' (allocate-trolley : pilih trolley dulu) | 'this' (allocate-this-trolley : trolley sudah tertentu)
    Langkah tur : lihat GUIDE_STEPS di halaman.
--}}
@php
    $isPick = ($variant ?? 'this') === 'trolley';
@endphp
<div class="modal fade pg-guide" id="pgGuide" tabindex="-1" aria-labelledby="pgGuideTitle" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable modal-fullscreen-lg-down">
        <div class="modal-content">
            <div class="modal-header bg-sb text-light">
                <div>
                    <span class="lbl">Panduan</span>
                    <h5 class="modal-title fw-bold" id="pgGuideTitle">{{ $isPick ? 'Alokasi Trolley' : 'Alokasi Stok Trolley' }}</h5>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <div class="modal-body pg-g">
                <nav class="pg-g-nav">
                    <a href="#pga-peta">Peta halaman</a><a href="#pga-alur">Alur singkat</a><a href="#pga-atas">Tombol atas</a>
                    @if ($isPick)<a href="#pga-trolley">Pilih trolley</a>@endif
                    <a href="#pga-scan">Scan stocker</a><a href="#pga-cari">Cari &amp; filter</a><a href="#pga-tabel">Tabel stok</a>
                    <a href="#pga-hapus">Keluarkan stok</a><a href="#pga-detail">Detail stocker</a><a href="#pga-ringkas">Ringkasan</a>
                    <a href="#pga-layar">Tablet &amp; HP</a><a href="#pga-tips">Tips &amp; FAQ</a>
                </nav>

                <section id="pga-peta">
                    <h6><span>Peta halaman</span> <button type="button" class="pg-g-show pri" data-tour="0"><i class="fa fa-play"></i> Mulai tur</button></h6>
                    <p class="pg-g-note">Klik <b>nomor</b> untuk membaca penjelasannya, atau klik <b>kotaknya</b> untuk melihat langsung di halaman.</p>
                    <div class="pg-map">
                        <div class="pg-map-head">
                            <div class="t"><b>{{ $isPick ? 'Alokasi Trolley' : 'Nama trolley' }}</b><span>Scan stocker masuk ke trolley</span></div>
                            <div class="pg-map-z r" data-spot="pga-atas"><a href="#pga-atas" class="pg-map-n tip-l" aria-label="Tombol atas">2<span class="pg-map-tip" role="tooltip"><b>Tombol di kanan atas</b>Panduan, Tampilan lama, Kembali{{ $isPick ? '' : ', dan Kirim Stok Trolley' }}.<small>Klik nomor untuk penjelasan lengkap</small></span></a><i></i><i class="p"></i>@if (!$isPick)<i class="s"></i>@endif</div>
                        </div>
                        <div class="pg-map-stack">
                            @if ($isPick)
                                <div class="pg-map-z pg-map-card" data-spot="pga-trolley">
                                    <a href="#pga-trolley" class="pg-map-n" aria-label="Pilih trolley">3<span class="pg-map-tip" role="tooltip"><b>Pilih trolley</b>Trolley tujuan alokasi. Tabel di bawah ikut menampilkan isinya.<small>Klik nomor untuk penjelasan lengkap</small></span></a>
                                    <div class="h">Scan Trolley</div>
                                    <div class="inp"><span>List Trolley</span><i class="c">Scan</i></div>
                                </div>
                            @endif
                            <div class="pg-map-z pg-map-card" data-spot="pga-scan">
                                <a href="#pga-scan" class="pg-map-n" aria-label="Scan stocker">{{ $isPick ? 4 : 3 }}<span class="pg-map-tip" role="tooltip"><b>Scan stocker</b>Scan / ketik No. Stocker, data terisi lalu langsung tersimpan ke trolley.<small>Klik nomor untuk penjelasan lengkap</small></span></a>
                                <div class="h">{{ $isPick ? 'Scan Stocker' : 'Alokasi Trolley' }}</div>
                                <div class="inp"><span>Stocker</span><i class="g">Get</i><i class="c">Scan</i></div>
                                <div class="fl"><i></i><i></i><i></i><i></i><i></i><i></i><i></i><i></i></div>
                                <div class="save"></div>
                            </div>
                            <div class="pg-map-card">
                                <div class="h">Stock Trolley</div>
                                <div class="pg-map-tools">
                                    <span class="pg-map-z btn-f" data-spot="pga-cari"><a href="#pga-cari" class="pg-map-n sm" aria-label="Cari dan filter">{{ $isPick ? 5 : 4 }}<span class="pg-map-tip" role="tooltip"><b>Cari &amp; filter</b>Cari No. Stocker langsung saat mengetik, atau saring per WS, Style, Color, Size, dan lainnya.<small>Klik nomor untuk penjelasan lengkap</small></span></a>Filter</span>
                                    <span style="width:70px">Cari No. Stocker</span>
                                    <span class="pg-map-z sum" data-spot="pga-ringkas"><a href="#pga-ringkas" class="pg-map-n sm" aria-label="Ringkasan">{{ $isPick ? 6 : 5 }}<span class="pg-map-tip" role="tooltip"><b>Ringkasan</b>Jumlah stok dan Total Qty dari baris yang tampil.<small>Klik nomor untuk penjelasan lengkap</small></span></a>12 stok · Total Qty 480</span>
                                </div>
                                <div class="pg-map-z pg-map-tbl" data-spot="pga-tabel">
                                    <a href="#pga-tabel" class="pg-map-n sm" aria-label="Tabel stok">{{ $isPick ? 7 : 6 }}<span class="pg-map-tip" role="tooltip"><b>Tabel stok</b>Satu baris = satu grup stocker. Qty = qty terkecil di grup.<small>Klik nomor untuk penjelasan lengkap</small></span></a>
                                    <i class="hd"></i><i class="hd"></i><i class="hd"></i><i class="hd"></i><i class="hd"></i><i class="hd"></i>
                                    <i class="del pg-map-z" data-spot="pga-hapus"></i><i class="lk pg-map-z" data-spot="pga-detail"></i><i></i><i></i><i></i><i></i>
                                    <i class="del"></i><i class="lk"></i><i></i><i></i><i></i><i></i>
                                </div>
                                <div class="pg-map-z pg-map-bar" data-spot="pga-ringkas">
                                    <span>8 stok · Total Qty 320</span><span>Size: L ×</span><span class="sp"></span><span>Reset filter</span>
                                </div>
                            </div>
                        </div>
                        <div class="d-flex gap-3 mt-3 small">
                            <span class="pg-map-z" data-spot="pga-hapus"><a href="#pga-hapus" class="pg-map-n sm" style="position:static;display:inline-grid" aria-label="Keluarkan stok">{{ $isPick ? 8 : 7 }}</a> Tombol hapus (Act)</span>
                            <span class="pg-map-z" data-spot="pga-detail"><a href="#pga-detail" class="pg-map-n sm" style="position:static;display:inline-grid" aria-label="Detail stocker">{{ $isPick ? 9 : 8 }}</a> Detail stocker</span>
                        </div>
                    </div>
                </section>

                <section id="pga-alur">
                    <h6>1. Alur singkat</h6>
                    <div class="pg-g-flow">
                        @if ($isPick)<div><b>Pilih trolley</b><span>dari daftar atau scan QR</span></div><i>→</i>@endif
                        <div><b>Scan stocker</b><span>scanner, ketik + Enter, atau kamera</span></div><i>→</i>
                        <div><b>Dicek</b><span>sudah di line / trolley? proses lengkap?</span></div><i>→</i>
                        <div class="ok"><b>Langsung tersimpan</b><span>satu bundle masuk trolley</span></div>
                    </div>
                    <ul>
                        <li>Scan <b>satu stocker</b> = <b>seluruh bundle</b>-nya (form, size, group, ratio yang sama) ikut dialokasikan ke trolley.</li>
                        <li>Kalau sebagian bundle sudah ada di trolley lain, bundle masuk ke <b>trolley itu</b>. Pesannya: <i>berhasil dialokasi ke [trolley] (berdasarkan alokasi STK-…)</i>.</li>
                        <li>Semua stocker di bundle harus sudah <b>DC In</b>, dan yang secondary sudah <b>Secondary In</b>.</li>
                        <li>Qty di tabel = qty stocker <b>terkecil</b> di grup, sama dengan qty yang dipakai saat kirim ke line.</li>
                    </ul>
                </section>

                <section id="pga-atas">
                    <h6><span>2. Tombol di kanan atas</span> <button type="button" class="pg-g-show" data-spot="pga-atas"><i class="fa fa-crosshairs"></i> Tunjukkan di halaman</button></h6>
                    <table class="table table-sm pg-g-t">
                        <tr><td><span class="pg-g-btn"><i class="fa fa-book-open"></i> Panduan</span></td><td>Membuka halaman ini.</td></tr>
                        <tr><td><span class="pg-g-btn"><i class="fas fa-history"></i> Tampilan lama</span></td><td>Membuka tampilan sebelumnya (tabel kolom lengkap, filter di bawah judul kolom). Qty-nya tetap memakai perhitungan terbaru. Di tampilan lama ada tombol <b>Tampilan baru</b> untuk kembali.</td></tr>
                        <tr><td><span class="pg-g-btn">Kembali</span></td><td>Kembali ke daftar stok trolley.</td></tr>
                        @if (!$isPick)
                            <tr><td><span class="pg-g-btn">Kirim Stok Trolley</span></td><td>Membuka halaman kirim untuk trolley ini (kirim ke line atau trolley lain).</td></tr>
                        @endif
                    </table>
                </section>

                @if ($isPick)
                    <section id="pga-trolley">
                        <h6><span>3. Pilih trolley</span> <button type="button" class="pg-g-show" data-spot="pga-trolley"><i class="fa fa-crosshairs"></i> Tunjukkan di halaman</button></h6>
                        <ul>
                            <li>Pilih trolley di <b>List Trolley</b>, atau tekan <b>Scan</b> untuk scan QR trolley pakai kamera.</li>
                            <li>Tabel <i>Stock Trolley</i> langsung menampilkan isi trolley terpilih. Tabel kosong selama belum ada trolley yang dipilih.</li>
                            <li>Stocker yang di-scan masuk ke trolley ini, kecuali bundle-nya sudah ada di trolley lain (lihat Alur singkat).</li>
                        </ul>
                    </section>
                @endif

                <section id="pga-scan">
                    <h6><span>{{ $isPick ? 4 : 3 }}. Scan stocker</span> <button type="button" class="pg-g-show" data-spot="pga-scan"><i class="fa fa-crosshairs"></i> Tunjukkan di halaman</button></h6>
                    <table class="table table-sm pg-g-t">
                        <tr><td><b>Kolom Stocker</b></td><td>Scanner otomatis menekan Enter. Kalau mengetik manual, tekan <kbd>Enter</kbd> atau tombol <b>Get</b>.</td></tr>
                        <tr><td><b>Scan (kamera)</b></td><td>Scan QR stocker pakai kamera belakang. Klik area kamera untuk mematikannya.</td></tr>
                        <tr><td><b>Data stocker</b></td><td>No. WS, Buyer, Style, Color, No. Stocker, No. Cut, Size, dan Qty terisi otomatis, lalu <b>langsung tersimpan</b> tanpa menekan Simpan.</td></tr>
                        <tr><td><span class="pg-g-btn">Simpan</span></td><td>Hanya perlu ditekan kalau penyimpanan otomatis gagal dan ingin mencoba lagi.</td></tr>
                    </table>
                </section>

                <section id="pga-cari">
                    <h6><span>{{ $isPick ? 5 : 4 }}. Cari &amp; filter</span> <button type="button" class="pg-g-show" data-spot="pga-cari"><i class="fa fa-crosshairs"></i> Tunjukkan di halaman</button></h6>
                    <table class="table table-sm pg-g-t">
                        <tr><td><b><i class="fas fa-search"></i> Cari No. Stocker</b></td><td>Tabel langsung tersaring saat mengetik. Tekan × di kolom untuk mengosongkan.</td></tr>
                        <tr><td><span class="pg-g-btn"><i class="fas fa-filter"></i> Filter</span></td><td>Membuka jendela filter: <b>No. WS, No. Cut, Style, Color, Panel, Size, Part</b>. Pilihannya diambil dari isi trolley dan ikut diperbarui setiap tabel dimuat ulang. Pilih lalu tekan <b>Terapkan</b>.</td></tr>
                        <tr><td><b>Chip filter</b></td><td>Filter aktif tampil di bar bawah, misalnya <span class="pg-g-chip">Size: <b>L</b> ×</span>. Tekan <b>×</b> untuk menghapus satu filter, atau <b>Reset filter</b> untuk semuanya.</td></tr>
                        <tr><td><b>Pencocokan</b></td><td>WS, No. Cut, Style, Color, Size harus <b>sama persis</b> (Size <i>L</i> tidak ikut <i>XL</i>). Panel dan Part cukup <b>mengandung</b> nilai itu.</td></tr>
                    </table>
                </section>

                <section id="pga-tabel">
                    <h6><span>{{ $isPick ? 6 : 5 }}. Tabel stok</span> <button type="button" class="pg-g-show" data-spot="pga-tabel"><i class="fa fa-crosshairs"></i> Tunjukkan di halaman</button></h6>
                    <table class="table table-sm pg-g-t">
                        <tr><td><b>No. Stocker</b></td><td>Semua stocker di grup, satu per baris. Klik untuk melihat detail transaksinya.</td></tr>
                        <tr><td><b>WS / Style / Color</b></td><td>WS (tebal), Style (abu-abu), dan Color (label kuning). Titik di depan color = perkiraan warnanya dari nama color. <span class="pg-g-dot unknown"></span> titik abu putus-putus = nama color tidak dikenali.</td></tr>
                        <tr><td><b>Size / Qty</b></td><td>Size di atas, Qty (tebal) di bawah. Qty = qty terkecil di grup.</td></tr>
                        <tr><td><b>Range</b></td><td>Range nomor stocker. Angka merah dalam kurung, misalnya <span class="text-danger fw-semibold">(-1)</span>, = selisih qty terkecil dengan qty part main terbesar.</td></tr>
                        <tr><td><span class="pg-g-line" style="background:#f1aeb5"></span> <b>Garis merah tipis</b></td><td>Di sisi kiri No. Stocker = grup ini punya selisih qty minus. Cek lewat detail stocker.</td></tr>
                        <tr><td><b>Lainnya</b></td><td>No. Cut, Panel, Part, dan <b>By</b> (yang memasukkan ke trolley + waktunya). Stok terbaru tampil paling atas.</td></tr>
                    </table>
                </section>

                <section id="pga-hapus">
                    <h6><span>{{ $isPick ? 7 : 6 }}. Mengeluarkan stok dari trolley</span> <button type="button" class="pg-g-show" data-spot="pga-hapus"><i class="fa fa-crosshairs"></i> Tunjukkan di halaman</button></h6>
                    <ul>
                        <li>Tekan tombol <span class="btn btn-danger btn-sm py-0 px-1"><i class="fa fa-trash fa-xs"></i></span> di kolom <b>Act</b>, lalu konfirmasi.</li>
                        <li><b>Seluruh grup</b> di baris itu keluar dari trolley. Stocker kembali berstatus <i>idle</i> dan posisinya di rak aktif lagi.</li>
                    </ul>
                </section>

                <section id="pga-detail">
                    <h6><span>{{ $isPick ? 8 : 7 }}. Detail transaksi stocker</span> <button type="button" class="pg-g-show" data-spot="pga-detail"><i class="fa fa-crosshairs"></i> Tunjukkan di halaman</button></h6>
                    <ul>
                        <li>Klik <span class="pg-g-link">No. Stocker</span> di tabel.</li>
                        <li><b>Stocker dalam grup ini</b>: semua stocker satu bundle beserta <b>qty terakhirnya</b>. Angka <span class="badge bg-danger">merah</span> = qty terkecil, yang menjadi qty grup. Klik untuk berpindah stocker.</li>
                        <li>Ringkasan <b>DC IN, Sec. Dalam, Sec. Masuk, Sec. Update</b> (awal, reject, replace, qty terakhir), lalu riwayat per tanggal di tab masing-masing.</li>
                    </ul>
                </section>

                <section id="pga-ringkas">
                    <h6><span>{{ $isPick ? 9 : 8 }}. Ringkasan &amp; bar bawah</span> <button type="button" class="pg-g-show" data-spot="pga-ringkas"><i class="fa fa-crosshairs"></i> Tunjukkan di halaman</button></h6>
                    <ul>
                        <li><b>N stok · Total Qty</b> = jumlah baris dan total qty yang <b>sedang tampil</b>, mengikuti filter dan pencarian.</li>
                        <li>Saat ada <b>filter aktif</b>, ringkasan pindah ke <b>bar bawah</b> bersama chip filter dan Reset filter, supaya hasil filter langsung terlihat.</li>
                    </ul>
                </section>

                <section id="pga-layar">
                    <h6>{{ $isPick ? 10 : 9 }}. Di tablet &amp; HP</h6>
                    <ul>
                        <li><b>Tablet</b>: kolom Panel, Range, dan By disembunyikan.</li>
                        <li><b>HP</b>: Part juga disembunyikan. Yang tampil: Act, No. Stocker, WS / Style / Color, Size / Qty, No. Cut.</li>
                        <li>Detail yang tersembunyi tetap bisa dilihat lewat klik No. Stocker.</li>
                    </ul>
                </section>

                <section id="pga-tips">
                    <h6>{{ $isPick ? 11 : 10 }}. Tips &amp; FAQ</h6>
                    <dl class="pg-g-faq">
                        <dt>"Stocker tidak ditemukan"?</dt>
                        <dd>No. Stocker salah ketik, atau label tidak terbaca sempurna. Scan ulang atau cek labelnya.</dd>
                        <dt>"Stocker sudah ada di sebuah troli" / "sudah ada di sebuah line"?</dt>
                        <dd>Stocker itu sudah pernah dialokasikan ke trolley atau sudah di-loading ke line. Cek posisinya di halaman Cek Proses Stocker.</dd>
                        <dt>"Stocker tidak bisa dialokasikan"?</dt>
                        <dd>Ada stocker di bundle yang belum DC In atau belum Secondary In. Nomornya tertera di pesan, beserta link ke halamannya.</dd>
                        <dt>"Stocker sudah di loading ke line"?</dt>
                        <dd>Semua stocker di bundle itu sudah dikirim ke line, jadi tidak perlu dialokasikan lagi.</dd>
                        <dt>Ingin melihat tur lagi?</dt>
                        <dd>Buka <b>Panduan</b> lalu tekan <b>Mulai tur</b>.</dd>
                    </dl>
                </section>
            </div>
        </div>
    </div>
</div>
