{{-- =====================================================================
     Ketentuan SJ yang bisa ditarik di modal Add SJ.

     Dipakai Invoice Local & Export - satu berkas saja, supaya isinya tidak
     mungkin berbeda antara kedua layar.

     Sengaja Bahasa Indonesia walaupun isian lain di layar ini Inggris: ini
     keterangan panjang yang dibaca tim AR & gudang, bukan label isian.

     Isinya HARUS sama dengan penyaring di InvoiceEximController:
       garment  -> sjGarment()   (bppb, MySQL)
       knitting -> sjKnitting()  (official_out_h, PostgreSQL)
       keduanya -> saringTerpakai()
     Kalau penyaringnya diubah, keterangan di sini ikut diubah - kalau tidak,
     user membaca aturan yang sudah tidak berlaku.
     ===================================================================== --}}
<div class="card dn-ketentuan">
    <div class="card-header">
        <h5 class="card-title"><i class="fas fa-circle-info"></i> SJ yang bisa ditarik</h5>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6 dn-ket-kolom">
                <div class="dn-ket-judul">Garment &mdash; NAG</div>
                <ul class="dn-ket">
                    <li>Sudah <b>di-confirm</b>, dan tidak dibatalkan.</li>
                    <li>Belum pernah di-invoice.</li>
                    <li>Tipe pengeluaran:
                        <b>FG/OUT</b>, <b>GK/OUT</b>, <b>GEN/OUT</b>, <b>WIP/OUT</b>,
                        <b>GACC/OUT</b>, <b>SCR/OUT</b>, <b>SPCK/OUT</b>.</li>
                    {{-- Pengecualian FG/OUT bertanggal sebelum 1 Agu 2026 memang
                         masih berlaku di sjGarment(), tapi sengaja TIDAK
                         ditampilkan: itu aturan peralihan untuk data lama, dan
                         menampilkannya cuma bikin bingung user yang menagih
                         SJ hari ini. --}}
                    <li>Jenis transaksi: <b>Penjualan</b>,
                        <b>Pengiriman ke Subkontraktor CMT</b>, atau
                        <b>Pengiriman Sample</b>.</li>
                    <li>Khusus FG/OUT: penerimanya harus customer.</li>
                </ul>
            </div>
            <div class="col-md-6 dn-ket-kolom">
                <div class="dn-ket-judul">Knitting &mdash; NAK</div>
                <ul class="dn-ket">
                    <li>Belum pernah di-invoice.</li>
                    <li>Tipe pengeluaran: <b>Penjualan</b>, <b>Sample</b>, atau
                        <b>Pengiriman ke Subkontraktor</b>.</li>
                </ul>
                <div class="dn-ket-judul dn-ket-judul-2">Keduanya</div>
                <ul class="dn-ket">
                    <li>Tanggal SJ harus masuk rentang tanggal di atas.</li>
                    <li>Belum dipakai invoice lain. SJ bebas lagi begitu invoice
                        itu dibatalkan.</li>
                </ul>
            </div>
        </div>
    </div>
</div>
