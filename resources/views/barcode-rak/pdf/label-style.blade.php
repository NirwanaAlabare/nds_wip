{{-- CSS label Barcode per Rak (mPDF). Ukuran diambil dari PDF label BPB ERP (pdfBarcode_whs.php):
     kertas 100 x 80 mm, DejaVu Sans Condensed Bold 6 pt, garis 0,2 mm. --}}
body { font-family: dejavusanscondensed; font-weight: bold; font-size: 6pt; color: #000; }
table { border-collapse: collapse; }
td, th { font-size: 6pt; font-weight: bold; }

table.info { width: 94.74mm; }
/* Lebar sel mPDF sudah termasuk padding */
table.info td { padding: 0.47mm 0.57mm; vertical-align: top; }
table.info td.k { width: 19.1mm; }
table.info td.t { width: 6.1mm; }
table.info td.g { width: 30.66mm; padding: 0.41mm 0.53mm 0.41mm 0.5mm; vertical-align: top; }

table.grp { width: 29.63mm; }
table.grp th { border: 0.2mm solid #000; height: 3.23mm; padding: 0; text-align: center; }
table.grp td { border: 0.2mm solid #000; height: 14.55mm; padding: 0; }

/* Lebar kolom = ERP (94,74 mm). Garis tepinya memakai sisa 0,2 mm margin kanan; kalau tabel lebih lebar
   dari area cetak, mPDF mengecilkan tabel & fontnya. */
table.roll { margin-top: 5.02mm; }
table.roll th, table.roll td { border: 0.2mm solid #000; height: 3.23mm; padding: 0; text-align: center; vertical-align: middle; }

/* Bingkai digambar di sel: mPDF tidak menggambar border tabel ber-border-collapse */
table.bawah { margin-top: 2.81mm; }
/* Barcode menempel di bawah kotak, sama dengan ERP */
table.bawah td.bc { width: 56.78mm; height: 22.96mm; padding: 0 0 0.3mm; text-align: center; vertical-align: bottom; border: 0.2mm solid #000; }
table.bawah td.rlx { padding: 0; vertical-align: top; }

table.relax { width: 37.96mm; }
table.relax th, table.relax td { border: 0.2mm solid #000; padding: 0; text-align: center; vertical-align: middle; }
table.relax tr.h th { height: 4.95mm; }
table.relax td { height: 8.11mm; }
