{{-- Panduan halaman Kirim Stok Trolley (send-stock-trolley). Langkah tur : lihat GUIDE_STEPS di halaman. --}}
<div class="modal fade pg-guide" id="pgGuide" tabindex="-1" aria-labelledby="pgGuideTitle" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable modal-fullscreen-lg-down">
        <div class="modal-content">
            <div class="modal-header bg-sb text-light">
                <div>
                    <span class="lbl">Panduan</span>
                    <h5 class="modal-title fw-bold" id="pgGuideTitle">Kirim Stok Trolley</h5>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <div class="modal-body pg-g">
                <nav class="pg-g-nav">
                    <a href="#pgs-peta">Peta halaman</a><a href="#pgs-alur">Alur singkat</a><a href="#pgs-atas">Tombol atas</a><a href="#pgs-tujuan">Tujuan kirim</a>
                    <a href="#pgs-cari">Cari &amp; filter</a><a href="#pgs-tabel">Tabel stok</a><a href="#pgs-pilih">Memilih stocker</a><a href="#pgs-detail">Detail stocker</a>
                    <a href="#pgs-kirim">Kirim</a><a href="#pgs-layar">Tablet &amp; HP</a><a href="#pgs-tips">Tips &amp; FAQ</a>
                </nav>

                <section id="pgs-peta">
                    <h6><span>Peta halaman</span> <button type="button" class="pg-g-show pri" data-tour="0"><i class="fa fa-play"></i> Mulai tur</button></h6>
                    <p class="pg-g-note">Klik <b>nomor</b> untuk membaca penjelasannya, atau klik <b>kotaknya</b> untuk melihat langsung di halaman.</p>
                    <div class="pg-map">
                        <div class="pg-map-head">
                            <div class="t"><b>Nama trolley</b><span>Kirim stok trolley ke line atau trolley lain</span></div>
                            <div class="pg-map-z r" data-spot="pgs-atas"><a href="#pgs-atas" class="pg-map-n tip-l" aria-label="Tombol atas">2<span class="pg-map-tip" role="tooltip"><b>Tombol di kanan atas</b>Panduan, Tampilan lama, Kembali, dan Alokasi Stok Trolley.<small>Klik nomor untuk penjelasan lengkap</small></span></a><i></i><i class="p"></i><i class="s"></i></div>
                        </div>
                        <div class="pg-map-stack">
                            <div class="pg-map-z pg-map-card" data-spot="pgs-tujuan">
                                <a href="#pgs-tujuan" class="pg-map-n" aria-label="Tujuan kirim">3<span class="pg-map-tip" role="tooltip"><b>Tujuan kirim</b>Geser untuk memilih Line atau Trolley, lalu pilih line / trolley tujuannya.<small>Klik nomor untuk penjelasan lengkap</small></span></a>
                                <div class="pg-map-switch"><span>Line</span></div>
                                <div class="h">Kirim ke Line</div>
                                <div class="inp"><span>Pilih line</span><i class="g">Get</i><i class="c">Scan</i></div>
                            </div>
                            <div class="pg-map-card">
                                <div class="h b">Kirim Stocker</div>
                                <div class="pg-map-tools">
                                    <span class="pg-map-z btn-f" data-spot="pgs-cari"><a href="#pgs-cari" class="pg-map-n sm" aria-label="Cari dan filter">4<span class="pg-map-tip" role="tooltip"><b>Cari &amp; filter</b>Cari No. Stocker langsung saat mengetik, atau saring per WS, Style, Color, Size, dan lainnya.<small>Klik nomor untuk penjelasan lengkap</small></span></a>Filter</span>
                                    <span style="width:70px">Cari No. Stocker</span>
                                    <span class="sp" style="border:0;background:none"></span>
                                    <span class="pg-map-z bon" data-spot="pgs-kirim"><a href="#pgs-kirim" class="pg-map-n sm tip-l" aria-label="Kirim">8<span class="pg-map-tip" role="tooltip"><b>No. BON &amp; Kirim</b>Isi No. BON lalu tekan Kirim. Pindah ke bar bawah saat ada stocker dipilih.<small>Klik nomor untuk penjelasan lengkap</small></span></a>No. BON</span>
                                    <span class="kirim">Kirim</span>
                                </div>
                                <div class="pg-map-z pg-map-tbl" data-spot="pgs-tabel">
                                    <a href="#pgs-tabel" class="pg-map-n sm" aria-label="Tabel stok">5<span class="pg-map-tip" role="tooltip"><b>Tabel stok</b>Satu baris = satu grup stocker. Qty = qty terkecil di grup.<small>Klik nomor untuk penjelasan lengkap</small></span></a>
                                    <i class="hd"></i><i class="hd"></i><i class="hd"></i><i class="hd"></i><i class="hd"></i><i class="hd"></i>
                                    <i class="ck pg-map-z" data-spot="pgs-pilih"></i><i class="lk"></i><i></i><i></i><i></i><i></i>
                                    <i class="ck"></i><i class="lk pg-map-z" data-spot="pgs-detail"></i><i class="sel"></i><i class="sel"></i><i class="sel"></i><i class="sel"></i>
                                    <i class="ck"></i><i class="lk del"></i><i></i><i></i><i></i><i></i>
                                </div>
                                <div class="pg-map-z pg-map-bar" data-spot="pgs-kirim">
                                    <a href="#pgs-kirim" class="pg-map-n sm" aria-label="Action bar">9<span class="pg-map-tip" role="tooltip"><b>Bar bawah</b>Jumlah dipilih, Total Qty, Batal pilih, filter aktif, No. BON &amp; Kirim.<small>Klik nomor untuk penjelasan lengkap</small></span></a>
                                    <span>3 stocker dipilih · Total Qty 120</span><span>Color: 001 BLACK ×</span><span class="sp"></span><span>No. BON · Kirim</span>
                                </div>
                            </div>
                        </div>
                        <div class="d-flex gap-3 mt-3 small">
                            <span class="pg-map-z" data-spot="pgs-pilih"><a href="#pgs-pilih" class="pg-map-n sm" style="position:static;display:inline-grid" aria-label="Memilih stocker">6</a> Memilih stocker</span>
                            <span class="pg-map-z" data-spot="pgs-detail"><a href="#pgs-detail" class="pg-map-n sm" style="position:static;display:inline-grid" aria-label="Detail stocker">7</a> Detail stocker</span>
                        </div>
                    </div>
                </section>

                <section id="pgs-alur">
                    <h6>1. Alur singkat</h6>
                    <div class="pg-g-flow">
                        <div><b>Pilih tujuan</b><span>Line atau trolley lain</span></div><i>→</i>
                        <div><b>Pilih stocker</b><span>centang atau klik baris</span></div><i>→</i>
                        <div><b>Isi No. BON</b><span>wajib diisi</span></div><i>→</i>
                        <div class="ok"><b>Kirim</b><span>hasil per stocker langsung tampil</span></div>
                    </div>
                    <ul>
                        <li><b>Satu baris = satu grup stocker</b> (satu bundle: form, size, group, ratio yang sama). Mengirim satu baris berarti mengirim semua stocker di dalamnya.</li>
                        <li><b>Qty yang dikirim</b> = qty stocker <b>terkecil</b> di grup itu, sama dengan angka di kolom <i>Size / Qty</i>.</li>
                        <li>Ke <b>Line</b>: semua stocker di grup harus sudah lengkap prosesnya (DC In / Secondary In). Kalau belum, pengiriman ditolak dan muncul daftar stocker yang belum masuk beserta link ke halamannya.</li>
                        <li>Tanggal loading otomatis <b>hari ini</b>. Setelah berhasil, stocker hilang dari trolley ini.</li>
                    </ul>
                </section>

                <section id="pgs-atas">
                    <h6><span>2. Tombol di kanan atas</span> <button type="button" class="pg-g-show" data-spot="pgs-atas"><i class="fa fa-crosshairs"></i> Tunjukkan di halaman</button></h6>
                    <table class="table table-sm pg-g-t">
                        <tr><td><span class="pg-g-btn"><i class="fa fa-book-open"></i> Panduan</span></td><td>Membuka halaman ini.</td></tr>
                        <tr><td><span class="pg-g-btn"><i class="fas fa-history"></i> Tampilan lama</span></td><td>Membuka tampilan sebelumnya (tabel kolom lengkap, filter di bawah judul kolom). Qty-nya tetap memakai perhitungan terbaru. Di tampilan lama ada tombol <b>Tampilan baru</b> untuk kembali.</td></tr>
                        <tr><td><span class="pg-g-btn">Kembali</span></td><td>Kembali ke daftar stok trolley.</td></tr>
                        <tr><td><span class="pg-g-btn">Alokasi Stok Trolley</span></td><td>Membuka halaman alokasi untuk trolley ini (scan stocker masuk ke trolley).</td></tr>
                    </table>
                </section>

                <section id="pgs-tujuan">
                    <h6><span>3. Tujuan kirim</span> <button type="button" class="pg-g-show" data-spot="pgs-tujuan"><i class="fa fa-crosshairs"></i> Tunjukkan di halaman</button></h6>
                    <table class="table table-sm pg-g-t">
                        <tr><td><b>Tombol geser Line / Trolley</b></td><td>Menyala = kirim ke <b>Line</b>. Mati = pindah ke <b>Trolley</b> lain. Kartu di bawahnya ikut berganti.</td></tr>
                        <tr><td><b>Kirim ke Line</b></td><td>Pilih line tujuan dari daftar. Line yang terkunci tidak muncul.</td></tr>
                        <tr><td><b>Kirim ke Trolley</b></td><td>Pilih trolley tujuan. Stocker hanya dipindah ke trolley itu, tidak masuk line.</td></tr>
                        <tr><td><b>Scan</b></td><td>Scan QR line / trolley pakai kamera. Klik area kamera untuk mematikannya.</td></tr>
                    </table>
                </section>

                <section id="pgs-cari">
                    <h6><span>4. Cari &amp; filter</span> <button type="button" class="pg-g-show" data-spot="pgs-cari"><i class="fa fa-crosshairs"></i> Tunjukkan di halaman</button></h6>
                    <table class="table table-sm pg-g-t">
                        <tr><td><b><i class="fas fa-search"></i> Cari No. Stocker</b></td><td>Tabel langsung tersaring saat mengetik. Tekan × di kolom untuk mengosongkan.</td></tr>
                        <tr><td><span class="pg-g-btn"><i class="fas fa-filter"></i> Filter</span></td><td>Membuka jendela filter: <b>No. WS, No. Cut, Style, Color, Panel, Size, Part</b>. Pilihannya hanya yang ada di trolley ini. Pilih lalu tekan <b>Terapkan</b>. Angka di tombol = jumlah filter aktif.</td></tr>
                        <tr><td><b>Chip filter</b></td><td>Filter aktif tampil di bar bawah, misalnya <span class="pg-g-chip">Color: <b>001 BLACK</b> ×</span>. Tekan <b>×</b> untuk menghapus satu filter, atau <b>Reset filter</b> untuk semuanya.</td></tr>
                        <tr><td><b>Pencocokan</b></td><td>WS, No. Cut, Style, Color, Size harus <b>sama persis</b> (Size <i>L</i> tidak ikut <i>XL</i>). Panel dan Part cukup <b>mengandung</b> nilai itu.</td></tr>
                    </table>
                </section>

                <section id="pgs-tabel">
                    <h6><span>5. Tabel stok</span> <button type="button" class="pg-g-show" data-spot="pgs-tabel"><i class="fa fa-crosshairs"></i> Tunjukkan di halaman</button></h6>
                    <table class="table table-sm pg-g-t">
                        <tr><td><b>No. Stocker</b></td><td>Semua stocker di grup, satu per baris. Klik untuk melihat detail transaksinya (lihat bagian 7).</td></tr>
                        <tr><td><b>WS / Style / Color</b></td><td>WS (tebal), Style (abu-abu), dan Color (label kuning). Titik di depan color = perkiraan warnanya dari nama color. <span class="pg-g-dot unknown"></span> titik abu putus-putus = nama color tidak dikenali.</td></tr>
                        <tr><td><b>Size / Qty</b></td><td>Size di atas, Qty (tebal) di bawah. Qty = qty terkecil di grup, yang akan dikirim.</td></tr>
                        <tr><td><b>Range</b></td><td>Range nomor stocker. Angka merah dalam kurung, misalnya <span class="text-danger fw-semibold">(-1)</span>, = selisih qty terkecil dengan qty part main terbesar.</td></tr>
                        <tr><td><span class="pg-g-line" style="background:#f1aeb5"></span> <b>Garis merah tipis</b></td><td>Di sisi kiri No. Stocker = grup ini punya selisih qty minus. Ada stocker yang qty-nya kurang. Cek lewat detail stocker.</td></tr>
                        <tr><td><b>Lainnya</b></td><td>No. Cut, Panel, Part, dan <b>By</b> (yang memasukkan ke trolley + waktunya).</td></tr>
                    </table>
                </section>

                <section id="pgs-pilih">
                    <h6><span>6. Memilih stocker</span> <button type="button" class="pg-g-show" data-spot="pgs-pilih"><i class="fa fa-crosshairs"></i> Tunjukkan di halaman</button></h6>
                    <ul>
                        <li><b>Centang</b> kotak di kolom Check, atau <b>klik di mana saja pada baris</b>. Klik lagi untuk melepas.</li>
                        <li><b>Centang di judul kolom</b> = pilih semua baris yang sedang tampil sesuai filter / pencarian, termasuk di halaman tabel lain.</li>
                        <li>Baris terpilih berwarna <b>sesuai color</b> stoknya, dengan garis di sisi kiri. Color yang tidak dikenali memakai biru.</li>
                        <li><b>Batal pilih</b> di bar bawah melepas semua pilihan sekaligus.</li>
                    </ul>
                </section>

                <section id="pgs-detail">
                    <h6><span>7. Detail transaksi stocker</span> <button type="button" class="pg-g-show" data-spot="pgs-detail"><i class="fa fa-crosshairs"></i> Tunjukkan di halaman</button></h6>
                    <ul>
                        <li>Klik <span class="pg-g-link">No. Stocker</span> di tabel. Klik ini tidak ikut memilih baris.</li>
                        <li><b>Stocker dalam grup ini</b>: semua stocker satu bundle beserta <b>qty terakhirnya</b>. Angka <span class="badge bg-danger">merah</span> = qty terkecil, yang membatasi qty kirim grup. Klik untuk berpindah stocker.</li>
                        <li>Ringkasan <b>DC IN, Sec. Dalam, Sec. Masuk, Sec. Update</b> (awal, reject, replace, qty terakhir), lalu riwayat per tanggal di tab masing-masing.</li>
                    </ul>
                </section>

                <section id="pgs-kirim">
                    <h6><span>8. No. BON, Kirim &amp; bar bawah</span> <button type="button" class="pg-g-show" data-spot="pgs-kirim"><i class="fa fa-crosshairs"></i> Tunjukkan di halaman</button></h6>
                    <ul>
                        <li><b>Bar bawah</b> muncul saat ada stocker dipilih atau filter aktif: jumlah stocker dipilih, <b>Total Qty</b>, Batal pilih, chip filter, dan Reset filter.</li>
                        <li><b>No. BON &amp; Kirim</b> ada di kanan atas tabel, lalu <b>pindah ke bar bawah</b> begitu ada stocker dipilih. Isian No. BON tidak hilang saat pindah.</li>
                        <li><b>No. BON wajib diisi</b>, juga saat pindah ke trolley lain.</li>
                        <li>Setelah Kirim muncul <b>Hasil Transfer</b> per stocker: <i>Berhasil</i>, <i>Gagal</i>, atau <i>Sudah Ada di [line]</i> (sudah pernah di-loading, lengkap dengan BON, waktu, dan pengirimnya). Halaman lalu dimuat ulang.</li>
                    </ul>
                </section>

                <section id="pgs-layar">
                    <h6>9. Di tablet &amp; HP</h6>
                    <ul>
                        <li><b>Tablet</b>: kolom Panel, Range, dan By disembunyikan.</li>
                        <li><b>HP</b>: Part juga disembunyikan. Yang tampil: Check, No. Stocker, WS / Style / Color, Size / Qty, No. Cut.</li>
                        <li>Detail yang tersembunyi tetap bisa dilihat lewat klik No. Stocker. Garis merah selisih qty tetap tampil.</li>
                    </ul>
                </section>

                <section id="pgs-tips">
                    <h6>10. Tips &amp; FAQ</h6>
                    <dl class="pg-g-faq">
                        <dt>Muncul "Stocker tidak bisa dialokasikan"?</dt>
                        <dd>Ada stocker di grup itu yang belum DC In atau belum Secondary In. Nomornya tertera di pesan, beserta link ke halaman DC In / Secondary In.</dd>
                        <dt>Qty kirim lebih kecil dari dugaan?</dt>
                        <dd>Qty grup mengikuti stocker dengan qty terkecil. Klik No. Stocker untuk melihat stocker mana yang qty-nya kurang (angka merah).</dd>
                        <dt>Pilih semua tidak memilih semuanya?</dt>
                        <dd>Pilih semua hanya memilih baris yang sedang tampil sesuai filter dan pencarian. Kosongkan filter dulu bila ingin semuanya.</dd>
                        <dt>Ingin melihat tur lagi?</dt>
                        <dd>Buka <b>Panduan</b> lalu tekan <b>Mulai tur</b>.</dd>
                    </dl>
                </section>
            </div>
        </div>
    </div>
</div>
