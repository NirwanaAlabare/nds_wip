{{-- User Guide halaman Create Costing. Dibuka dari tombol "Panduan" di judul (data-guide-open="guide-create-costing"). --}}
@php
    // Peta halaman (SVG): bagian yang dibahas diberi warna oranye, bagian lain abu. Helper: App\Services\Marketing\MarketingGuide.
    $G = \App\Services\Marketing\MarketingGuide::class;
    $guideMap = fn ($active) => $G::map([
        'buyer' => [6, 6, 96, 32, 'Information Buyer'],
        'product' => [6, 42, 96, 40, 'Product & Order'],
        'image' => [106, 6, 48, 76, 'Upload Image'],
        'param' => [6, 86, 148, 13, 'Parameter Costing'],
        'price' => [6, 103, 148, 13, 'Price & Currency'],
        'save' => [124, 120, 30, 9, 'Save', 'button'],
    ], $active, 'Peta halaman Create Costing');
    $f = fn ($label, $value, $opt = '', $n = null) => $G::field($label, $value, $opt, $n);
    $num = fn ($n) => $G::num($n);

    $steps = [
        [
            'title' => 'Kenalan dengan halaman ini',
            'map' => $guideMap('all'),
            'html' => '<p>Halaman <b>Create Costing</b> dipakai untuk membuat <b>Costing baru</b>: perhitungan harga satu style dari buyer, sebelum ada Sales Order.</p>'
                . '<p>Costing adalah langkah pertama di alur Marketing:</p>'
                . '<div class="mkt-guide-flow"><span class="on">Costing</span><i class="fas fa-chevron-right"></i><span>Approval</span><i class="fas fa-chevron-right"></i><span>Sales Order</span><i class="fas fa-chevron-right"></i><span>BOM</span><i class="fas fa-chevron-right"></i><span>Worksheet</span></div>'
                . '<p>Di halaman ini kamu cukup mengisi <b>data utama</b> (buyer, produk, gambar, parameter, dan harga). Rincian material dan biaya produksi diisi setelah disimpan.</p>'
                . '<div class="mkt-guide-tip"><i class="fas fa-lightbulb"></i><span>Bagian bertanda <b style="color:#dc2626">*</b> wajib diisi. Ikuti langkah di panduan ini dari atas ke bawah.</span></div>',
        ],
        [
            'title' => 'Information Buyer',
            'map' => $guideMap('buyer'),
            'mock' => '<div class="gm-grid">'
                . $f('Buyer Name', 'H&amp;M', 'sel hl', 1)
                . $f('Kode Buyer', 'terisi otomatis', 'ro', 2)
                . '<div class="gm-f"><label>Brand</label><div class="gm-in sel hl">Divid</div><div class="gm-menu"><div>H&amp;M</div><div class="add"><i class="fas fa-plus-square"></i> Add Brand Divid</div></div><span class="gm-n">3</span></div>'
                . $f('Season', 'SS26 - Spring Summer 2026', 'sel', 4)
                . $f('Style', 'HM56239852', 'span', 5)
                . '</div>',
            'html' => '<p>Isi data pemilik order.</p><ul>'
                . '<li>' . $num(1) . '<b>Buyer Name</b>: pilih buyer. Bisa diketik untuk mencari.</li>'
                . '<li>' . $num(2) . '<b>Kode Buyer</b>: terisi otomatis setelah buyer dipilih, tidak perlu diisi.</li>'
                . '<li>' . $num(3) . '<b>Brand</b>: pilihannya mengikuti buyer. Kalau brand belum ada, ketik namanya lalu pilih <b>Add Brand …</b> untuk menambah brand baru.</li>'
                . '<li>' . $num(4) . '<b>Season</b>: musim koleksi.</li>'
                . '<li>' . $num(5) . '<b>Style</b>: kode style dari buyer.</li></ul>',
        ],
        [
            'title' => 'Product & Order Information',
            'map' => $guideMap('product'),
            'mock' => '<div class="gm-grid">'
                . $f('Product Item', '<span class="gm-chip">Polo Shirt (TOP)</span><span class="gm-chip">Long Pants (BOTTOM)</span>', 'span hl', 1)
                . $f('CM Price TOP', 'otomatis', 'ro', 2)
                . $f('CM Price BOTTOM', 'otomatis', 'ro')
                . $f('Unit', 'PCS', 'sel', 3)
                . $f('Marketing Order', 'REGULAR', 'sel', 4)
                . $f('Status', 'CONFIRM', 'sel', 5)
                . $f('Notes', 'Catatan tambahan (boleh kosong)', 'ro', 6)
                . '</div>',
            'html' => '<p>Isi produk yang dihitung dan informasi order.</p><ul>'
                . '<li>' . $num(1) . '<b>Product Item</b>: pilih satu atau lebih item. Untuk set (atasan + bawahan) pilih keduanya.</li>'
                . '<li>' . $num(2) . '<b>CM Price</b>: muncul sendiri per set (TOP / BOTTOM) dan hanya untuk dilihat. Nilainya dari biaya jahit (CMT) yang diisi setelah Costing disimpan.</li>'
                . '<li>' . $num(3) . '<b>Unit</b>: satuan order, biasanya <b>PCS</b> (sudah terpilih).</li>'
                . '<li>' . $num(4) . '<b>Marketing Order</b>: jenis order.</li>'
                . '<li>' . $num(5) . '<b>Status</b>: <b>CONFIRM</b> (order pasti), <b>SAMPLE</b> (contoh), atau <b>CANCEL</b>.</li>'
                . '<li>' . $num(6) . '<b>Notes</b>: catatan bebas, boleh dikosongkan.</li></ul>',
        ],
        [
            'title' => 'Upload Image',
            'map' => $guideMap('image'),
            'mock' => '<div class="gm-drop"><i class="fas fa-file-alt" style="font-size:20px"></i><span>Drag n Drop here Or <b>Browse</b></span><span class="gm-n" style="position:static;margin-top:4px">1</span></div>'
                . '<div class="gm-thumbs"><span></span><span></span><span></span></div>'
                . '<div class="gm-btns"><span class="gm-btn red">Clear</span><span class="mkt-guide-num" style="align-self:center">3</span></div>'
                . '<p style="margin:8px 0 0;font-size:12px;color:var(--g-muted);text-align:center"><span class="mkt-guide-num">2</span>Pratinjau bisa digeser dengan tombol &lsaquo; &rsaquo;</p>',
            'html' => '<p>Tambahkan foto atau sketsa style supaya semua orang melihat produk yang sama.</p><ul>'
                . '<li>' . $num(1) . 'Tarik file gambar ke kotak, atau klik <b>Browse</b> untuk memilih file. Bisa lebih dari satu gambar sekaligus.</li>'
                . '<li>' . $num(2) . 'Gambar yang dipilih tampil sebagai pratinjau. Pakai tombol panah untuk melihat gambar lain.</li>'
                . '<li>' . $num(3) . '<b>Clear</b> menghapus semua gambar yang sudah dipilih.</li></ul>'
                . '<div class="mkt-guide-tip"><i class="fas fa-lightbulb"></i><span>Format gambar biasa (JPG / PNG), maksimal <b>10 MB</b> per gambar. Gambar juga bisa ditambahkan nanti.</span></div>',
        ],
        [
            'title' => 'Parameter Costing',
            'map' => $guideMap('param'),
            'mock' => '<div class="gm-grid c4" style="grid-template-columns:repeat(3,minmax(0,1fr))">'
                . $f('QTY Planned (PCS)', '5.000', 'hl', 1)
                . $f('SMV (Min)', '12,500', 'hl', 2)
                . $f('VAT (%)', '11', 'hl', 3)
                . '</div>',
            'html' => '<p>Angka dasar untuk perhitungan biaya.</p><ul>'
                . '<li>' . $num(1) . '<b>QTY Planned</b>: perkiraan jumlah order dalam PCS.</li>'
                . '<li>' . $num(2) . '<b>SMV</b>: waktu jahit standar satu pcs dalam <b>menit</b> (maksimal 3 angka di belakang koma).</li>'
                . '<li>' . $num(3) . '<b>VAT</b>: pajak dalam persen.</li></ul>'
                . '<div class="mkt-guide-tip"><i class="fas fa-lightbulb"></i><span>Angka desimal ditulis dengan koma, contoh <b>12,5</b>. Pemisah ribuan muncul sendiri.</span></div>',
        ],
        [
            'title' => 'Price & Currency',
            'map' => $guideMap('price'),
            'mock' => '<div class="gm-grid c4">'
                . $f('Currency', 'USD', 'sel hl', 1)
                . $f('Confirm Price', '4,25', 'hl', 2)
                . $f('Rate to IDR', '16.295', 'hl', 3)
                . $f('Rate from IDR', '0,0000614', '', 4)
                . '</div>',
            'html' => '<p>Harga yang disepakati dengan buyer.</p><ul>'
                . '<li>' . $num(1) . '<b>Currency</b>: mata uang harga (mis. USD atau IDR).</li>'
                . '<li>' . $num(2) . '<b>Confirm Price</b>: harga jual per pcs yang sudah disepakati buyer.</li>'
                . '<li>' . $num(3) . '<b>Rate to IDR</b>: kurs mata uang itu ke Rupiah.</li>'
                . '<li>' . $num(4) . '<b>Rate from IDR</b>: kurs kebalikannya (Rupiah ke mata uang itu).</li></ul>',
        ],
        [
            'title' => 'Simpan & langkah berikutnya',
            'map' => $guideMap('save'),
            'mock' => '<div class="gm-btns" style="justify-content:center;margin:6px 0 12px"><span class="gm-btn" style="padding:8px 34px;font-size:14px">Save</span></div>'
                . '<div class="mkt-guide-flow" style="justify-content:center"><span class="on">Create Costing</span><i class="fas fa-chevron-right"></i><span>Edit Costing</span><i class="fas fa-chevron-right"></i><span>Approval</span></div>',
            'html' => '<p>Kalau semua bagian bertanda <b style="color:#dc2626">*</b> sudah terisi, klik <b>Save</b>.</p><ul>'
                . '<li>Nomor Costing (contoh <b>CST/1126/00250</b>) dibuat otomatis.</li>'
                . '<li>Setelah tersimpan, kamu langsung dibawa ke halaman <b>Edit Costing</b> untuk mengisi rincian material, biaya jahit (CMT), dan biaya lain.</li>'
                . '<li>Kalau ada isian yang kurang, muncul pesan merah di atas halaman. Lengkapi lalu klik Save lagi.</li></ul>'
                . '<div class="mkt-guide-tip"><i class="fas fa-lightbulb"></i><span>Costing yang sudah lengkap diajukan ke <b>Approval</b>. Setelah disetujui, Costing bisa dibuatkan Sales Order.</span></div>',
        ],
    ];
@endphp
@include('marketing.partials.user_guide', ['guideId' => 'guide-create-costing', 'guideTitle' => 'Panduan Create Costing', 'steps' => $steps])
