@extends('layouts.index', ['page' => 'dashboard-report-bc', 'containerFluid' => true])

@section('title', 'Dashboard Report BC')

@section('custom-link')
    <link rel="stylesheet" href="{{ asset('plugins/select2/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}">
@endsection

<style>
    #full-page-loader {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(255, 255, 255, 0.9);
        z-index: 9999;
        display: none;
        flex-direction: column;
        justify-content: center;
        align-items: center;
    }

    .modern-loader-popup {
        border-radius: 20px !important;
        box-shadow: 0 20px 60px rgba(0,0,0,0.15) !important;
        padding: 2rem 1.5rem !important;
    }

    .modern-loader {
        display: flex;
        flex-direction: column;
        align-items: center;
        padding: 8px 0 4px;
    }

    .modern-loader-icon {
        width: 68px;
        height: 68px;
        border-radius: 50%;
        background: linear-gradient(135deg, #3085d6, #5aa9f0);
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 18px;
        animation: modern-loader-float 2s ease-in-out infinite;
        box-shadow: 0 8px 24px rgba(48, 133, 214, 0.35);
    }

    .modern-loader-icon i {
        font-size: 26px;
        color: #fff;
        animation: modern-loader-pulse-icon 1.8s ease-in-out infinite;
    }

    @keyframes modern-loader-float {
        0%, 100% { transform: translateY(0); }
        50% { transform: translateY(-6px); }
    }

    @keyframes modern-loader-pulse-icon {
        0%, 100% { transform: scale(1); opacity: 1; }
        50% { transform: scale(0.9); opacity: 0.85; }
    }

    .modern-loader-title {
        font-size: 17px;
        font-weight: 600;
        color: #1e293b;
        margin: 0 0 4px;
    }

    .modern-loader-subtitle {
        font-size: 13.5px;
        color: #94a3b8;
        margin: 0 0 20px;
    }

    .modern-loader-dots {
        display: flex;
        gap: 6px;
    }

    .modern-loader-dots span {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background: #3085d6;
        animation: modern-loader-bounce 1.4s ease-in-out infinite both;
    }

    .modern-loader-dots span:nth-child(1) { animation-delay: -0.32s; }
    .modern-loader-dots span:nth-child(2) { animation-delay: -0.16s; }
    .modern-loader-dots span:nth-child(3) { animation-delay: 0s; }

    @keyframes modern-loader-bounce {
        0%, 80%, 100% { transform: scale(0.6); opacity: 0.5; }
        40% { transform: scale(1); opacity: 1; }
    }

    /* ============ MODERN CARD STYLING ============ */
    .report-bc-card {
        border: none;
        border-radius: 18px;
        overflow: hidden;
        box-shadow: 0 10px 40px rgba(30, 41, 59, 0.08);
    }

    .report-bc-card .card-header {
        background: linear-gradient(135deg, #1e3a8a 0%, #3085d6 55%, #5aa9f0 100%);
        border: none;
        padding: 1.5rem 1.75rem;
        position: relative;
        overflow: hidden;
    }

    .report-bc-card .card-header::before {
        content: "";
        position: absolute;
        top: -50%;
        right: -5%;
        width: 220px;
        height: 220px;
        background: rgba(255, 255, 255, 0.08);
        border-radius: 50%;
    }

    .report-bc-card .card-header::after {
        content: "";
        position: absolute;
        bottom: -60%;
        right: 15%;
        width: 140px;
        height: 140px;
        background: rgba(255, 255, 255, 0.06);
        border-radius: 50%;
    }

    .report-bc-card .card-title {
        font-size: 1.25rem;
        font-weight: 700;
        letter-spacing: 0.2px;
        display: flex;
        align-items: center;
        position: relative;
        z-index: 1;
        color:white;
    }

    .report-bc-card .card-title .icon-badge {
        width: 42px;
        height: 42px;
        border-radius: 12px;
        background: rgba(255, 255, 255, 0.18);
        display: inline-flex;
        align-items: center;
        justify-content: center;
        margin-right: 14px;
        backdrop-filter: blur(4px);
    }

    .report-bc-card .card-subtitle {
        font-size: 0.82rem;
        color: rgba(255, 255, 255, 0.8);
        margin: 4px 0 0 56px;
        position: relative;
        z-index: 1;
    }

    .report-bc-card .card-body {
        background: #fbfcff;
        padding: 2rem 1.75rem;
    }

    .rb-field-label {
        font-weight: 600;
        font-size: 0.83rem;
        color: #334155;
        text-transform: uppercase;
        letter-spacing: 0.4px;
        margin-bottom: 8px;
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .rb-field-label i {
        color: #3085d6;
        font-size: 0.78rem;
    }

    .report-bc-card .form-control,
    .report-bc-card .select2-container--bootstrap4 .select2-selection {
        border-radius: 10px;
        border: 1.5px solid #e2e8f0;
        height: 44px;
        transition: all 0.2s ease;
    }

    .report-bc-card .form-control:focus,
    .report-bc-card .select2-container--bootstrap4.select2-container--focus .select2-selection {
        border-color: #3085d6;
        box-shadow: 0 0 0 3px rgba(48, 133, 214, 0.12);
    }

    .report-bc-card .input-group-text {
        border-radius: 0;
        background: #f8fafc;
        border-color: #e2e8f0;
        color: #64748b;
        font-size: 0.8rem;
        font-weight: 600;
    }

    .report-bc-card .input-group .form-control:first-child {
        border-top-right-radius: 0;
        border-bottom-right-radius: 0;
    }

    .report-bc-card .input-group .form-control:last-child {
        border-top-left-radius: 0;
        border-bottom-left-radius: 0;
    }

    #btn-tampilkan {
        height: 44px;
        border: none;
        border-radius: 10px;
        background: linear-gradient(135deg, #3085d6, #1e3a8a);
        font-weight: 600;
        letter-spacing: 0.3px;
        box-shadow: 0 6px 16px rgba(48, 133, 214, 0.3);
        transition: all 0.2s ease;
    }

    #btn-tampilkan:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 22px rgba(48, 133, 214, 0.4);
        background: linear-gradient(135deg, #2b76bf, #182f6e);
    }

    #btn-tampilkan:active {
        transform: translateY(0);
    }
</style>

@section('content')
    <div class="card report-bc-card">
        <div class="card-header">
            <h3 class="card-title mb-0">
                <span class="icon-badge">
                    <i class="fas fa-file-invoice text-white"></i>
                </span>
                Report BC
            </h3>
        </div>

        <div class="card-body">
            <form id="form-report-bc">
                <div class="row align-items-end mb-3">

                    <div class="col-md-3 mb-3 mb-md-0">
                        <div class="form-group mb-0">
                            <label for="jenis_laporan" class="rb-field-label">
                                <i class="fas fa-layer-group"></i> Jenis Laporan <span class="text-danger">*</span>
                            </label>
                            <select class="form-control select2" id="jenis_laporan" name="jenis_laporan" required>
                                <option value="" selected disabled>-- Pilih Laporan --</option>
                                <option value="pemasukan">Laporan Pemasukan</option>
                                <option value="pengeluaran">Laporan Pengeluaran</option>
                                <option value="mutasi_bahan_baku">Laporan Mutasi Bahan Baku dan Penolong</option>
                                <option value="mutasi_barang_jadi">Laporan Mutasi Barang Jadi</option>
                                {{-- <option value="mutasi_wip">Laporan Mutasi WIP</option> --}}
                                <option value="mutasi_mesin_sparepart">Laporan Mutasi Mesin & Sparepart</option>
                                <option value="mutasi_barang_sisa">Laporan Mutasi Barang Sisa / Scrap</option>
                            </select>
                        </div>
                    </div>

                    <div class="col-md-2 mb-3 mb-md-0">
                        <div class="form-group mb-0">
                            <label for="kategori_dokumen" class="rb-field-label">
                                <i class="fas fa-folder-open"></i> Kategori Dokumen <span class="text-danger">*</span>
                            </label>
                            <select class="form-control select2" id="kategori_dokumen" name="kategori_dokumen" required disabled>
                                <option value="" selected disabled>-- Pilih Kategori  --</option>
                            </select>
                        </div>
                    </div>

                    <div class="col-md-2 mb-3 mb-md-0">
                        <div class="form-group mb-0">
                            <label for="kategori_barang" class="rb-field-label">
                                <i class="fas fa-box"></i> Kategori Barang <span class="text-danger">*</span>
                            </label>
                            <select class="form-control select2" id="kategori_barang" name="kategori_barang" required >
                                <option value="" selected disabled>-- Pilih Kategori  --</option>
                                <option value="all">Semua Kategori</option>
                                <option value="fabric">Fabric</option>
                                <option value="accesories">Accessories</option>
                                <option value="barang_jadi">Barang Jadi</option>
                            </select>
                        </div>
                    </div>

                    <div class="col-md-3 mb-3 mb-md-0">
                        <div class="form-group mb-0">
                            <label class="rb-field-label">
                                <i class="fas fa-calendar-alt"></i> Range Tanggal <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <input type="date" class="form-control" id="from_date" name="from_date" required>
                                <div class="input-group-prepend input-group-append">
                                    <span class="input-group-text border-left-0 border-right-0">s/d</span>
                                </div>
                                <input type="date" class="form-control" id="to_date" name="to_date" required>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100 text-white" id="btn-tampilkan">
                            <i class="fas fa-search mr-1"></i> Tampilkan
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('custom-script')
    <script src="{{ asset('plugins/select2/js/select2.full.min.js') }}"></script>

    <script>
        $(document).ready(function() {

            $('.select2').select2({
                theme: 'bootstrap4',
                width: '100%'
            });

            const docOptions = {
                'pemasukan': [
                    { id: 'rekap', text: 'Rekap All Pemasukan' },
                    { id: 'bc-23', text: 'BC 2.3' },
                    { id: 'bc-262', text: 'BC 2.6.2' },
                    { id: 'bc-40', text: 'BC 4.0' },
                    { id: 'bc-27', text: 'BC 2.7' }
                ],
                'pengeluaran': [
                    { id: 'rekap', text: 'Rekap All Pengeluaran' },
                    { id: 'bc-33', text: 'BC 3.3' },
                    { id: 'bc-30', text: 'BC 3.0' },
                    { id: 'bc-261', text: 'BC 2.6.1' },
                    { id: 'bc-27', text: 'BC 2.7' },
                    { id: 'bc-25', text: 'BC 2.5' },
                    { id: 'bc-41', text: 'BC 4.1' }
                ]
            };


            $('#jenis_laporan').on('change', function() {
                let jenis = $(this).val();

                // Target Elemen
                let $dokumen = $('#kategori_dokumen');
                let divKategoriDokumen = $dokumen.closest('.col-md-2');
                let $kategoriBarang = $('#kategori_barang');
                let divKategoriBarang = $kategoriBarang.closest('.col-md-2');

                // Reset Dokumen
                $dokumen.empty();
                $dokumen.append(new Option('-- Pilih Dokumen --', '', true, true));

                // Ambil semua opsi kategori barang
                let $optGarment = $kategoriBarang.find('option[value="garment"]');
                let $optSample = $kategoriBarang.find('option[value="sample"]');
                let $optKain = $kategoriBarang.find('option[value="kain"]');

                let $optFabric = $kategoriBarang.find('option[value="fabric"]');
                let $optAccessories = $kategoriBarang.find('option[value="accesories"]');
                let $optBarangJadi = $kategoriBarang.find('option[value="barang_jadi"]');

                if (jenis === 'mutasi_barang_jadi') {
                    divKategoriDokumen.slideUp();
                    $dokumen.removeAttr('required');

                    divKategoriBarang.slideUp();
                    $kategoriBarang.removeAttr('required');

                    // $optGarment.prop('disabled', false).show();
                    // $optSample.prop('disabled', false).show();
                    // $optKain.prop('disabled', false).show();

                    // $optFabric.prop('disabled', true).hide();
                    // $optAccessories.prop('disabled', true).hide();
                    // $optBarangJadi.prop('disabled', true).hide();

                    // // Reset pilihan jika sebelumnya terlanjur memilih opsi yang sekarang di-hide
                    // if(['fabric', 'accesories', 'barang_jadi'].includes($kategoriBarang.val())) {
                    //     $kategoriBarang.val('').trigger('change');
                    // }

                } else if (jenis === 'mutasi_bahan_baku') {
                    // Sembunyikan Dokumen BC
                    divKategoriDokumen.slideUp();
                    $dokumen.removeAttr('required');

                    divKategoriBarang.slideDown();
                    $kategoriBarang.attr('required', true).prop('disabled', false);

                    // Mutasi Bahan Baku: Sembunyikan Garment, Sample, Kain. Tampilkan Fabric & Accessories
                    $optGarment.prop('disabled', true).hide();
                    $optSample.prop('disabled', true).hide();
                    $optKain.prop('disabled', true).hide();

                    $optFabric.prop('disabled', false).show();
                    $optAccessories.prop('disabled', false).show();
                    $optBarangJadi.prop('disabled', true).hide();

                    $kategoriBarang.empty();
                    $kategoriBarang.append(new Option('-- Pilih Kategori --', '', true, true));
                    $kategoriBarang.append(new Option('Semua Kategori', 'all'));
                    $kategoriBarang.append(new Option('Fabric', 'fabric'));
                    $kategoriBarang.append(new Option('Accessories', 'accesories'));

                }else if (jenis === 'mutasi_mesin_sparepart') {
                    divKategoriDokumen.slideUp();
                    $dokumen.removeAttr('required');

                    divKategoriBarang.slideDown();
                    $kategoriBarang.attr('required', true);
                    kategori = 'mutasi';

                    $kategoriBarang.empty();
                    $kategoriBarang.append(new Option('-- Pilih Kategori --', '', true, true));
                    $kategoriBarang.append(new Option('Sparepart', 'sparepart'));
                    $kategoriBarang.append(new Option('Mesin & Peralatan Kantor', 'mesin'));
                }else if (jenis === 'mutasi_barang_sisa') {
                    divKategoriDokumen.slideUp();
                    $dokumen.removeAttr('required');

                    divKategoriBarang.slideDown();
                    $kategoriBarang.attr('required', true);

                    $kategoriBarang.empty();
                    $kategoriBarang.append(new Option('-- Pilih Kategori Scrap --', '', true, true));
                    $kategoriBarang.append(new Option('Semua (Import & Lokal)', 'all'));
                    $kategoriBarang.append(new Option('Scrap Import', 'import'));
                    $kategoriBarang.append(new Option('Scrap Lokal', 'lokal'));

                }else {
                    // Untuk Pemasukan / Pengeluaran
                    divKategoriDokumen.slideDown();
                    $dokumen.attr('required', true).prop('disabled', false);

                    // Isi opsi dokumen berdasarkan Jenis Laporan (Pemasukan/Pengeluaran)
                    if (docOptions[jenis]) {
                        docOptions[jenis].forEach(function(doc) {
                            $dokumen.append(new Option(doc.text, doc.id));
                        });
                    }

                    // Tampilkan kembali standar Pemasukan/Pengeluaran, sembunyikan Garment/Sample/Kain khusus mutasi
                    $optGarment.prop('disabled', true).hide();
                    $optSample.prop('disabled', true).hide();
                    $optKain.prop('disabled', true).hide();

                    $optFabric.prop('disabled', false).show();
                    $optAccessories.prop('disabled', false).show();
                    $optBarangJadi.prop('disabled', false).show();

                    $kategoriBarang.empty();
                    $kategoriBarang.append(new Option('-- Pilih Kategori --', '', true, true));
                    $kategoriBarang.append(new Option('Semua Kategori', 'all'));
                    $kategoriBarang.append(new Option('Fabric', 'fabric'));
                    $kategoriBarang.append(new Option('Accessories', 'accesories'));
                    $kategoriBarang.append(new Option('Barang Jadi', 'barang_jadi'));
                }

                // Refresh tampilan Select2 agar elemen yang di-hide/show langsung ter-update di UI
                $kategoriBarang.trigger('change');
                $dokumen.trigger('change');
            });


            $('#jenis_laporan').trigger('change');


            $('#form-report-bc').on('submit', function(e) {
                e.preventDefault();

                let jenis = $('#jenis_laporan').val();
                let kategori = $('#kategori_dokumen').val();
                let fromDate = $('#from_date').val();
                let toDate = $('#to_date').val();
                let kategoriBarang = $('#kategori_barang').val();

                if (!jenis) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Data Tidak Lengkap',
                        text: 'Silakan pilih jenis laporan terlebih dahulu',
                        confirmButtonColor: '#3085d6'
                    });
                    return;
                }

                if (jenis === 'mutasi_barang_jadi') {
                    kategori = 'mutasi';
                    kategoriBarang = 'all';
                }
                else if (jenis === 'mutasi_bahan_baku' || jenis === 'mutasi_mesin_sparepart' || jenis === 'mutasi_barang_sisa') {
                    kategori = 'mutasi';
                    if (!kategoriBarang) {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Data Tidak Lengkap',
                            text: 'Lengkapi pilihan kategori barang terlebih dahulu',
                            confirmButtonColor: '#3085d6'
                        });
                        return;
                    }
                }
                else {
                    if (!kategori || !kategoriBarang) {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Data Tidak Lengkap',
                            text: 'Lengkapi pilihan kategori dan dokumen terlebih dahulu',
                            confirmButtonColor: '#3085d6'
                        });
                        return;
                    }
                }

                Swal.fire({
                    html: `
                        <div class="modern-loader">
                            <div class="modern-loader-icon"><i class="fas fa-file-invoice"></i></div>
                            <h5 class="modern-loader-title">Menarik Data Laporan</h5>
                            <p class="modern-loader-subtitle">Mohon tunggu, data sedang diproses</p>
                            <div class="modern-loader-dots"><span></span><span></span><span></span></div>
                        </div>
                    `,
                    showConfirmButton: false, allowEscapeKey: false, allowOutsideClick: false,
                    customClass: { popup: 'modern-loader-popup' },
                    background: '#fff', backdrop: 'rgba(255, 255, 255, 0.75)'
                });

                let targetUrl = `{{ route('report-bc.show', ['jenis' => '__JENIS__', 'kategori' => '__KATEGORI__', 'kategoribarang' => '___KATEGORI_BARANG___']) }}`
                                    .replace('__JENIS__', jenis)
                                    .replace('__KATEGORI__', kategori)
                                    .replace('___KATEGORI_BARANG___', kategoriBarang)
                                    + `?from=${fromDate}&to=${toDate}&filter_by=dokumen`;

                window.location.href = targetUrl;
            });

            $(window).on('pageshow', function(event) {
                if (event.originalEvent.persisted) {
                    Swal.close();
                }
            });
        });
    </script>
@endsection
