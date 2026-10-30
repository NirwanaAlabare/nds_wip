<style>
    .sticky-header-wrapper {
        position: sticky;
        /* Nilai top disesuaikan dengan tinggi navbar utama agar tidak tertutup */
        top: var(--navbar-height, 56px); /* Ubah angka ini (misal: 50px, 56px, atau 60px) sesuai tinggi navbar Anda */

        z-index: 1010; /* Di bawah z-index navbar (biasanya navbar = 1020/1030) */
        background-color: #ffffff;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        border-radius: 0 0 8px 8px;
        margin-top: -10px; /* Merapikan jarak atas */
    }
</style>
