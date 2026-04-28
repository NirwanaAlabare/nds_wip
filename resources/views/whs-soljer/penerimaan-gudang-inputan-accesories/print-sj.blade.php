<!DOCTYPE html>
<html>
<head>
    <title>Penerimaan Gudang Inputan (ACCESORIES)</title>
    <style>
        @page { margin: 15px; }

        body { margin: 15px;
        font-family: sans-serif;}

        td {
        font-family: Helvetica, Arial, sans-serif;
        }

        tr {
        font-family: Helvetica, Arial, sans-serif;
        }

        /** {
            font-size: 13px;
        }

        img {
            width: 69px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        table td, table th{
            text-align: left;
            vertical-align: middle;
            padding: 1px 1px;
            border: 0px solid;
            width: auto;
        }*/
    </style>
</head>
<body>
    <table width="100%" style="border:none;font-size: 9px;">
        {{-- @foreach ($dataHead as $dhead) --}}
        <tr>
            <td width="400px" style="margin-right:-5px;border:none;font-size: 11px;" align="left">Dicetak : {{ date('d-m-Y H:i:s') }}</td>
            <td width="240px" style="margin-right:-5px;border:none;" align="left"></td>
        </tr>
        <tr>
            <td width="400px" style="margin-right:-5px;border:none;font-size: 12px;" align="left"><b>PT. Nirwana Alabare Garment </b></td>
            <td width="240px" style="margin-right:-5px;border:none;" align="left">Bandung, {{  $dataHeader->tgl_bpb }}</td>
        </tr>
        <tr>
            <td width="400px" style="margin-right:-5px;border:none;" align="left">Jl. Raya Rancaekek - Majalaya No. 289</td>
        </tr>
        <tr>
            <td width="400px" style="margin-right:-5px;border:none;" align="left">Desa. Solokan Jeruk</td>
        </tr>
        <tr>
            <td width="400px" style="margin-right:-5px;border:none;" align="left">Kec. Solokan Jeruk Bandung Jawa Barat</td>
            <td width="240px" style="margin-right:-5px;border:none;" align="left"></td>
        </tr>
        <tr>
            <td width="400px" style="margin-right:-5px;border:none;" align="left">Telp.</td>
            <td width="240px" style="margin-right:-5px;border:none;" align="left"></td>
        </tr>
        {{-- @endforeach --}}
    </table>
    <table width="100%" style="border:none;">
        <tr style="line-height: 8px;">
            <td align="center" style="border:none;"><h3>Bukti Penerimaan Gudang Inputan (ACCESORIES)</h3></td>
        </tr>
        <tr style="line-height: 8px;">
            <td align="center" style="border:none; font-size:14pt;">{{ $dataHeader->no_bpb }}</td>
        </tr>
    </table>
    <br>
    <table width="100%" style="border:none; font-size:9pt">
        <tr>
            <td width="70px;">No BPB</td>
            <td> : {{ $dataHeader->no_bpb }}</td>
            {{-- <td>Tgl. BPB</td>
            <td> : {{ $dataHeader->no_bpb }}</td> --}}
        </tr>
        <tr>
            <td>Tgl BPB</td>
            <td> : {{ $dataHeader->tgl_bpb }}</td>
            {{-- <td>Tgl. Dok BC</td>
            <td> : {{ $dataHeader->no_bpb }}</td> --}}
        </tr>
    </table>

    <br>
    <table class="main" repeat_header="1" border="1" cellspacing="0" width="100%" style="border-collapse: collapse; width:100%; font-size: 11px;">
        <thead>
            <tr class="head">
                <td align="center">Buyer</td>
                <td align="center">Worksheet</td>
                <td align="center">Nama Barang</td>
                <td align="center">Kode</td>
                <td align="center">Warna</td>
                <td align="center">Size</td>
                <td align="center">Qty</td>
                <td align="center">Satuan</td>
                <td align="center">Qty KGM</td>
                <td align="center">Keterangan</td>
                <td align="center">Lokasi</td>
            </tr>
        </thead>
        <tbody>
        @php
            $totalQty = 0;
            $totalQtyKgm = 0;
        @endphp
        @foreach ($dataDetail as $row)
            @php
                $totalQty += $row->qty;
                $totalQtyKgm += $row->qty_kgm;
            @endphp

            <tr>
                <td align="left">{{ $row->buyer }}</td>
                <td align="left">{{ $row->worksheet }}</td>
                <td align="left">{{ $row->nama_barang }}</td>
                <td align="left">{{ $row->kode }}</td>
                <td align="left">{{ $row->warna }}</td>
                <td align="left">{{ $row->size }}</td>
                <td align="right">{{ number_format($row->qty, 2) }}</td>
                <td align="left">{{ $row->satuan }}</td>
                <td align="right">{{ number_format($row->qty_kgm, 2) }}</td>
                <td align="left">{{ $row->keterangan }}</td>
                <td align="left">{{ $row->lokasi }}</td>
            </tr>
        @endforeach
        </tbody>
        <tfoot>
            <tr>
                <th colspan="6" style="text-align:center;">TOTAL</th>
                <th style="text-align:right;">
                    {{ number_format($totalQty, 2) }}
                </th>
                <th></th>
                <th style="text-align:right;">
                    {{ number_format($totalQtyKgm, 2) }}
                </th>
                <th colspan="2"></th>
            </tr>
    </tfoot>
    </table>

    <br>

    <table width="70%" style="border:none;font-size: 11px;">
        <tr>
            <td width="240px" style="margin-right:-5px;border:none;" align="left">Created By.</td>
            <td width="240px" style="margin-right:-5px;border:none;" align="left">Approved By.</td>
            <td width="240px" style="margin-right:-5px;border:none;" align="left">Received By.</td>
        </tr>
        <br>
        <br>
        <tr>
            <td width="240px" style="margin-right:-5px;border:none;" align="left">{{ $dataHeader->created_by_username }}</td>
            {{-- <td width="240px" style="margin-right:-5px;border:none;" align="left">{{ $dataHeader->created_by_username }}</td> --}}
            <td width="240px" style="margin-right:-5px;border:none;" align="left"></td>
            <td width="240px" style="margin-right:-5px;border:none;" align="left"></td>
        </tr>
        <tr>
            <td width="240px" style="margin-right:-5px;border:none;" align="left">{{ $dataHeader->created_at }}</td>
            {{-- <td width="240px" style="margin-right:-5px;border:none;" align="left">{{ $dataHeader->created_at }}</td> --}}
            <td width="240px" style="margin-right:-5px;border:none;" align="left"></td>
            <td width="240px" style="margin-right:-5px;border:none;" align="left"></td>
        </tr>
    </table>
</body>
</html>
