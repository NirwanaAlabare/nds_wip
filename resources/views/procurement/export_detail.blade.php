<!DOCTYPE html>
<html lang="en">

<table class="table">
    <tr>
        <td colspan='36' style="font-size: 16px;"><b>List Return Detail</b></td>
    </tr>
    <tr>
        <td colspan='36' style="font-size: 12px;">Periode {{ date('d-M-Y', strtotime($from)) }} s/d {{ date('d-M-Y', strtotime($to)) }}
        </td>
    </tr>
    <thead>
        <tr>
            <th>No</th>
            <th>No Return</th>
            <th>Tgl Return</th>
            <th>Tipe Return</th>
            <th>Style</th>
            <th>No WS</th>
            <th>Penerima</th>
            <th>ID JO</th>
            <th>ID Item</th>
            <th>Kode Item</th>
            <th>Item Desc</th>
            <th>Qty</th>
            <th>Unit</th>
            <th>Price</th>
            <th>No Invoice</th>
            <th>Jenis BC</th>
            <th>No Dokumen</th>
            <th>Tgl Dokumen</th>
            <th>Status</th>
            <th>Created By</th>
            <th>Created Date</th>
        </tr>
    </thead>
    <tbody>
        @php
        $no = 1;
        @endphp
        @foreach ($data as $item)
        <tr>
            <td>{{ $no++ }}.</td>
            <td>{{ $item->bppbno_int }}</td>
            <td>{{ $item->bppbdate }}</td>
            <td>{{ $item->status_return }}</td>
            <td>{{ $item->stylenya }}</td>
            <td>{{ $item->wsno }}</td>
            <td>{{ $item->supplier }}</td>
            <td>{{ $item->id_jo }}</td>
            <td>{{ $item->id_item }}</td>
            <td>{{ $item->goods_code }}</td>
            <td>{{ $item->itemdesc }}</td>
            <td>{{ $item->qty }}</td>
            <td>{{ $item->unit }}</td>
            <td>{{ $item->price }}</td>
            <td>{{ $item->invno }}</td>
            <td>{{ $item->jenis_dok }}</td>
            <td>{{ $item->nomor_aju }}</td>
            <td>{{ $item->tanggal_aju }}</td>
            <td>{{ $item->status }}</td>
            <td>{{ $item->created_by }}</td>
            <td>{{ $item->dateinput }}</td>
        </tr>
        @endforeach
    </tbody>

</table>

</html>
