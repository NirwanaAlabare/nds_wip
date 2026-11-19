{{-- User Guide halaman Edit Costing. Dibuka dari tombol "Panduan" di judul (data-guide-open="guide-edit-costing"). --}}
@php
    $G = \App\Services\Marketing\MarketingGuide::class;
    $guideMap = fn ($active) => $G::map([
        'alert' => [6, 6, 148, 10, 'Status & Pengingat'],
        'buyer' => [6, 20, 96, 22, 'Information Buyer'],
        'product' => [6, 45, 96, 22, 'Product & Order'],
        'image' => [106, 20, 48, 47, 'Upload Image'],
        'param' => [6, 70, 148, 10, 'Parameter & Price'],
        'tabs' => [6, 84, 148, 9, 'Tab Kategori + Add Content'],
        'table' => [6, 96, 148, 28, 'Tabel Rincian Biaya'],
        'summary' => [6, 127, 148, 14, 'Ringkasan Total'],
        'next' => [86, 145, 32, 9, 'Next', 'button', '#f59e0b'],
        'update' => [122, 145, 32, 9, 'Update', 'button', '#2563eb'],
    ], $active, 'Peta halaman Edit Costing', 160, 158);
    $f = fn ($label, $value, $opt = '', $n = null) => $G::field($label, $value, $opt, $n);
    $num = fn ($n) => $G::num($n);
    $dot = fn ($c) => '<span style="display:inline-block;width:9px;height:9px;border-radius:50%;background:' . $c . ';margin-right:4px;vertical-align:0"></span>';
    $tbl = function (array $head, array $rows) {
        $h = '<table class="gm-table"><thead><tr>';
        foreach ($head as $c) {
            $h .= '<th>' . $c . '</th>';
        }
        $h .= '</tr></thead><tbody>';
        foreach ($rows as $r) {
            $h .= '<tr>';
            foreach ($r as $c) {
                $h .= is_array($c) ? '<td class="' . $c[1] . '">' . $c[0] . '</td>' : '<td>' . $c . '</td>';
            }
            $h .= '</tr>';
        }

        return $h . '</tbody></table>';
    };

    $steps = [
        [
            'title' => 'Kenalan dengan halaman ini',
            'map' => $guideMap('all'),
            'html' => '<p>Halaman <b>Edit Costing</b> dipakai untuk <b>melengkapi dan mengubah Costing</b> yang sudah dibuat: data utama, rincian material, biaya jahit (CMT), dan biaya lain sampai ketemu total biaya dan profit.</p>'
                . '<div class="mkt-guide-flow"><span>Create Costing</span><i class="fas fa-chevron-right"></i><span class="on">Edit Costing</span><i class="fas fa-chevron-right"></i><span>Approval</span><i class="fas fa-chevron-right"></i><span>Sales Order</span></div>'
                . '<p>Urutan kerjanya dari atas ke bawah: cek pengingat, perbaiki data utama bila perlu, isi tabel per kategori, lihat ringkasan, lalu klik <b>Update</b>.</p>',
        ],
        [
            'title' => 'Status & pengingat',
            'map' => $guideMap('alert'),
            'mock' => '<div class="gm-alert warn"><i class="fas fa-exclamation-triangle"></i><div><b>Harga CMT Belum Lengkap</b><span>Sales order dapat dibuat setelah seluruh product item memiliki harga CMT.</span></div><span class="gm-btn navy">+ Add CMT Price</span><span class="gm-n" style="position:static">1</span></div>'
                . '<div class="gm-alert lock"><i class="fas fa-lock"></i><div><span>Dokumen sudah di-approve dan tidak bisa diedit.</span></div><span class="gm-btn navy">Request Unlock</span><span class="gm-n" style="position:static">2</span></div>'
                . '<div class="gm-alert info"><i class="fas fa-info-circle"></i><div><b>Costing ini sudah punya Sales Order</b><span>Kalau qty, harga, atau CMT diubah, cek lagi Sales Order-nya.</span></div><span class="gm-n" style="position:static">3</span></div>',
            'html' => '<p>Kotak di bagian atas memberi tahu kondisi Costing. Yang muncul tergantung keadaannya:</p><ul>'
                . '<li>' . $num(1) . '<b>Harga CMT belum lengkap</b>: klik <b>Add CMT Price</b> untuk langsung ke baris CMT di tab Manufacturing.</li>'
                . '<li>' . $num(2) . '<b>Terkunci</b>: Costing yang sudah di-approve tidak bisa diubah. Klik <b>Request Unlock</b> untuk minta izin ke Marketing Manager.</li>'
                . '<li>' . $num(3) . '<b>Sudah punya Sales Order</b>: perubahan qty, harga, atau CMT perlu dicek juga di Sales Order.</li></ul>'
                . '<div class="mkt-guide-tip"><i class="fas fa-lightbulb"></i><span>Setelah di-unlock, hanya peminta yang bisa mengedit. Begitu klik Update, Costing terkunci lagi dan menunggu approval ulang.</span></div>',
        ],
        [
            'title' => 'Data utama',
            'map' => $guideMap(['buyer', 'product', 'image', 'param']),
            'mock' => '<div class="gm-grid">'
                . $f('Buyer Name', 'H&amp;M', 'sel')
                . $f('Style', 'HM56239852', '')
                . $f('Product Item', '<span class="gm-chip">Polo Shirt (TOP)</span>', 'span')
                . $f('CM Price TOP', '2,35 (dari tab Manufacturing)', 'ro span', 1)
                . $f('QTY Planned (PCS)', '5.000', 'hl', 2)
                . $f('Confirm Price', '4,25', 'hl', 2)
                . '</div>',
            'html' => '<p>Bagian atas berisi data yang sama dengan Create Costing (buyer, produk, gambar, parameter, harga) dan bisa diubah bila perlu.</p><ul>'
                . '<li>' . $num(1) . '<b>CM Price</b> per set terisi sendiri dari baris CMT di tab Manufacturing, tidak perlu diketik.</li>'
                . '<li>' . $num(2) . '<b>QTY Planned</b>, <b>SMV</b>, <b>VAT</b>, dan <b>Confirm Price</b> langsung mempengaruhi angka di tabel dan ringkasan.</li></ul>'
                . '<div class="mkt-guide-tip"><i class="fas fa-lightbulb"></i><span>Penjelasan tiap isian ada di <b>Panduan Create Costing</b>.</span></div>',
        ],
        [
            'title' => 'Tab kategori & Add Content',
            'map' => $guideMap('tabs'),
            'mock' => '<div class="gm-tabs"><span class="on">Fabric</span><span>Accessories Sewing</span><span>Accessories Packing</span><span>Manufacturing</span><span>Other Cost</span><span class="docs">View All Content</span><span class="gm-n" style="position:static">1</span></div>'
                . '<div class="gm-grid" style="grid-template-columns:minmax(0,1fr) auto;align-items:end;margin-top:12px">'
                . $f('Content', 'COTTON SINGLE JERSEY', 'sel hl', 2)
                . '<div class="gm-f"><span class="gm-btn">Add Content</span><span class="gm-n">3</span></div></div>',
            'html' => '<p>Rincian biaya dibagi per kategori.</p><ul>'
                . '<li>' . $num(1) . 'Klik tab untuk pindah kategori: <b>Fabric</b>, <b>Accessories Sewing</b>, <b>Accessories Packing</b>, <b>Manufacturing</b>, <b>Other Cost</b>. <b>View All Content</b> menampilkan semua kategori sekaligus.</li>'
                . '<li>' . $num(2) . 'Pilih <b>Content</b> (bahan / biaya) yang mau ditambahkan.</li>'
                . '<li>' . $num(3) . 'Klik <b>Add Content</b>. Baris baru muncul di tabel kategori itu, satu baris per set produk.</li></ul>'
                . '<div class="mkt-guide-tip"><i class="fas fa-lightbulb"></i><span>Tombol <b>Next</b> di bawah halaman membawa ke tab kategori berikutnya.</span></div>',
        ],
        [
            'title' => 'Mengisi tabel (seperti Excel)',
            'map' => $guideMap('table'),
            'mock' => $tbl(
                ['CONTENT', $dot('#f97316') . 'PRICE', $dot('#f97316') . 'CONS/PC', $dot('#f97316') . 'ALLOW', $dot('#94a3b8') . 'VALUE'],
                [
                    ['COTTON JERSEY', ['3,10', 'in'], ['1,250', 'in sel'], ['3%', 'in'], ['3,99', 'auto']],
                    ['RIB 1X1', ['2,80', 'in pasted'], ['0,120', 'in'], ['3%', 'in'], ['0,35', 'auto']],
                    ['INTERLINING', ['0,90', 'in bad'], ['0,050', 'in'], ['5%', 'in'], ['0,05', 'auto']],
                ]
            ) . '<div class="gm-legend"><span>' . $dot('#f97316') . 'Input (bisa diisi)</span><span>' . $dot('#94a3b8') . 'Auto (dihitung)</span><span><i class="gm-sw sel"></i>Sel terpilih</span><span><i class="gm-sw pasted"></i>Baru di-paste</span><span><i class="gm-sw bad"></i>Belum valid</span></div>',
            'html' => '<p>Tabel bisa diisi seperti Excel.</p><ul>'
                . '<li>Kolom bertitik <b style="color:#f97316">oranye</b> (ikon pensil) bisa diisi. Kolom bertitik <b>abu</b> dihitung otomatis.</li>'
                . '<li>Klik sel lalu <b>langsung ketik</b>, atau <b>dobel klik</b> / tekan <b>F2</b>. <b>Enter</b>, <b>Tab</b>, atau panah untuk pindah sel.</li>'
                . '<li>Bisa <b>Copy &amp; Paste</b> dari Excel, dan tarik kotak kecil di pojok sel untuk mengisi ke bawah.</li>'
                . '<li>Sel <b style="color:#dc2626">merah</b> berarti nilainya belum valid. Baris yang sudah diubah tapi belum disimpan ditandai <b style="color:#d97706">*</b> di nomornya.</li>'
                . '<li>Hapus satu baris dengan ikon <i class="fas fa-trash"></i>, atau centang beberapa baris lalu klik <b>Hapus</b>.</li></ul>'
                . '<div class="mkt-guide-tip"><i class="fas fa-lightbulb"></i><span>Di tab <b>Fabric</b> ada tombol <b>+</b> untuk menambah <b>Shell</b> dan <b>Part</b> pada bahan.</span></div>',
        ],
        [
            'title' => 'Manufacturing (CMT) & Other Cost',
            'map' => $guideMap('table'),
            'mock' => '<b class="gm-cap">Manufacturing</b>' . $tbl(['CONTENT', 'SET', $dot('#f97316') . 'PRICE'], [['CMT', 'TOP', ['2,35', 'in sel']], ['CMT', 'BOTTOM', ['', 'in bad']]])
                . '<b class="gm-cap" style="margin-top:12px">Other Cost</b>' . $tbl(['CONTENT', $dot('#f97316') . 'ALLOW', $dot('#f97316') . 'VALUE IDR'], [['OVERHEAD', ['5%', 'in'], ['-', 'auto']], ['TESTING', ['0%', 'in'], ['250.000', 'in']], [['<b>G&amp;A</b>', ''], ['3%', 'in'], ['otomatis', 'auto']]]),
            'html' => '<ul>'
                . '<li><b>Manufacturing</b>: isi <b>Price CMT</b> (ongkos jahit) untuk <b>setiap set</b> produk. Ini <b>wajib</b>: tanpa harga CMT, Costing tidak bisa di-Update dan Sales Order belum bisa dibuat.</li>'
                . '<li><b>Other Cost</b>: biaya tambahan seperti overhead atau testing, diisi dalam persen (Allow) atau nilai Rupiah.</li>'
                . '<li><b>G&amp;A</b> di bawah tabel Other Cost adalah biaya umum dalam persen (bawaan 3%).</li></ul>',
        ],
        [
            'title' => 'Ringkasan total',
            'map' => $guideMap('summary'),
            'mock' => $tbl(['', 'VALUE IDR', 'VALUE USD'], [
                [['<b>TOTAL COST</b>', ''], ['58.230,00', 'auto'], ['3,57', 'auto']],
                ['BALANCE OTHER COST', ['10.950,00', 'auto'], ['0,68', 'auto']],
                ['VAT 11%', ['6.405,30', 'auto'], ['0,39', 'auto']],
                ['PROFIT 6%', ['3.493,80', 'auto'], ['0,21', 'auto']],
            ]),
            'html' => '<p>Ringkasan dihitung otomatis setiap ada perubahan di tabel.</p><ul>'
                . '<li><b>Total Cost</b>: jumlah semua biaya (material, CMT, other cost, G&amp;A).</li>'
                . '<li><b>Balance Other Cost</b>: sisa antara Confirm Price dan total biaya.</li>'
                . '<li><b>VAT</b> dan <b>Profit</b> dihitung dari persentase yang berlaku.</li></ul>'
                . '<div class="mkt-guide-tip"><i class="fas fa-lightbulb"></i><span>Kalau Balance minus, berarti biaya lebih besar dari harga jual. Cek lagi harga atau cons material.</span></div>',
        ],
        [
            'title' => 'Simpan dengan Update',
            'map' => $guideMap(['next', 'update']),
            'mock' => '<div class="gm-btns" style="justify-content:center;margin:6px 0 12px"><span class="gm-btn orange" style="padding:8px 26px">Next</span><span class="gm-btn blue" style="padding:8px 26px">Update</span></div>'
                . '<div class="mkt-guide-flow" style="justify-content:center"><span class="on">Update</span><i class="fas fa-chevron-right"></i><span>Menunggu Approval</span><i class="fas fa-chevron-right"></i><span>Approved</span></div>',
            'html' => '<ul>'
                . '<li><b>Next</b>: pindah ke tab kategori berikutnya.</li>'
                . '<li><b>Update</b>: menyimpan semua baris yang diubah dan data utama, lalu kembali ke Marketing List.</li>'
                . '<li>Sebelum menyimpan, sistem mengecek <b>harga CMT</b> dan <b>sel merah</b>. Kalau ada yang kurang, kamu langsung dibawa ke sel tersebut.</li>'
                . '<li>Setelah Update, Costing masuk antrean <b>Approval</b> Marketing Manager.</li></ul>'
                . '<div class="mkt-guide-tip"><i class="fas fa-lightbulb"></i><span>Jangan tutup halaman sebelum klik Update kalau masih ada baris bertanda <b style="color:#d97706">*</b>, supaya perubahannya tidak hilang.</span></div>',
        ],
    ];
@endphp
@include('marketing.partials.user_guide', ['guideId' => 'guide-edit-costing', 'guideTitle' => 'Panduan Edit Costing', 'steps' => $steps])
