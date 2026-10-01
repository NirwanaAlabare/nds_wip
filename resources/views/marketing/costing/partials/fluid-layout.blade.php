{{-- Baris field yang menyesuaikan lebar layar (ikut rapi saat browser di-zoom in / zoom out) --}}
<style>
    .fluid-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(var(--fluid-min, 200px), 1fr));
        column-gap: 1rem;
        margin-left: 0;
        margin-right: 0;
    }

    /* Field angka cukup sempit */
    .fluid-grid.fluid-sm { --fluid-min: 120px; }

    .fluid-grid > [class*="col"],
    .fluid-grid > .form-group {
        width: auto;
        max-width: none;
        flex: none;
        min-width: 0;
        padding-left: 0;
        padding-right: 0;
    }

    /* Field yang isinya panjang mengambil 2 kolom */
    .fluid-grid > .span-2 { grid-column: span 2; }

    /* Layar sempit / zoom besar: satu field per baris */
    @media (max-width: 575.98px) {
        .fluid-grid { grid-template-columns: 1fr; }
        .fluid-grid > .span-2 { grid-column: auto; }
    }

    /* Select2 ikut lebar sel grid */
    .fluid-grid .select2-container { width: 100% !important; }
</style>
