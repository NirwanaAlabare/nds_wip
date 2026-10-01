{{--
    Panel riwayat invoice, dipakai modal rincian Local & Export.

    Baris detail invoice ditulis ulang setiap kali di-update, jadi keadaan
    sebelumnya hanya ada di tabel riwayat. Panel ini menampilkannya berurut:
    saat dibuat, edit pertama, edit kedua, dan seterusnya - tiap langkah
    menyebut apa yang berubah, dan potret lengkapnya dibuka kalau perlu.

    Isinya dimuat waktu panelnya pertama kali dibuka, bukan bersamaan dengan
    modalnya: daftarnya bisa panjang dan tidak selalu dilihat.
--}}
<div class="dn-riw" id="riw-wadah">
    <div class="dn-riw-muat" id="riw-muat" hidden>
        <span class="dn-loader-putar"></span> Loading history...
    </div>

    <div class="dn-riw-kosong" id="riw-kosong" hidden>
        <i class="fas fa-clock-rotate-left"></i>
        <span id="riw-kosong-teks">No history recorded for this invoice yet.</span>
    </div>

    <div class="dn-riw-alur" id="riw-alur"></div>
</div>
