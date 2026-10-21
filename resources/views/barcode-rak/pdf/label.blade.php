{{-- Satu label Barcode per Rak (mPDF, satu halaman 100 x 80 mm). Susunan sama dengan label BPB di ERP
     (pdfBarcode_whs.php): info item + kotak Grouping, tabel roll, lalu kotak barcode + Relaxation.
     Bedanya: Qty = stok terakhir di rak, Nama Rak = rak saat ini, dan label yang isinya panjang
     ($d->rapat) dirapatkan jarak antar bloknya supaya kotak barcode tetap di dalam halaman. --}}
<table class="info">
    <tr><td class="k">Product</td><td class="t">:</td><td colspan="2">{{ $d->item_desc }}</td></tr>
    <tr><td class="k">Kode Barang</td><td class="t">:</td><td colspan="2">{{ $d->kode_item }}</td></tr>
    <tr><td class="k">ID Item</td><td class="t">:</td><td colspan="2">{{ $d->id_item }}</td></tr>
    <tr><td class="k">Supplier</td><td class="t">:</td><td colspan="2">{{ $d->supplier }}</td></tr>
    <tr>
        <td class="k">No BPB</td><td class="t">:</td><td>{{ $d->no_dok }}</td>
        <td class="g" rowspan="5">
            <table class="grp"><tr><th>Grouping</th></tr><tr><td></td></tr></table>
        </td>
    </tr>
    <tr><td class="k">No SJ</td><td class="t">:</td><td>{{ $d->no_sj }}</td></tr>
    <tr><td class="k">No PO</td><td class="t">:</td><td>{{ $d->no_po }}</td></tr>
    <tr><td class="k">No WS</td><td class="t">:</td><td>{{ $d->no_ws }}</td></tr>
    <tr><td class="k">Style</td><td class="t">:</td><td>{{ $d->styleno }}</td></tr>
</table>

<table class="roll"{!! $d->rapat ? ' style="margin-top: 1.5mm;"' : '' !!}>
    <tr>
        <th style="width: 11.37mm;">No Roll</th>
        <th style="width: 17.05mm;">No Roll Buyer</th>
        <th style="width: 15.51mm;">Lot</th>
        <th style="width: 16.74mm;">Qty</th>
        <th style="width: 20.22mm;">Nama Rak</th>
        <th style="width: 13.85mm;">Unit</th>
    </tr>
    <tr>
        <td>{{ $d->roll }}</td>
        <td>{{ $d->no_roll_buyer }}</td>
        <td>{{ $d->no_lot }}</td>
        <td>{{ $d->qty }}</td>
        <td>{{ $d->kode_lok }}</td>
        <td>{{ $d->satuan }}</td>
    </tr>
</table>

<table class="bawah"{!! $d->rapat ? ' style="margin-top: 1.5mm;"' : '' !!}>
    <tr>
        <td class="bc"><img src="data:image/png;base64,{{ $d->barcode_png }}" style="width: {{ $d->barcode_lebar }}mm; height: 18.52mm;"></td>
        <td class="rlx">
            <table class="relax">
                <tr class="h"><th colspan="4">Relaxation</th></tr>
                <tr class="h"><th colspan="2">Start</th><th colspan="2">Finish</th></tr>
                <tr class="h"><th style="width: 9.45mm;">Date</th><th style="width: 9.48mm;">Time</th><th style="width: 9.41mm;">Date</th><th style="width: 9.62mm;">Time</th></tr>
                <tr><td></td><td></td><td></td><td></td></tr>
            </table>
        </td>
    </tr>
</table>
