<table>
    <thead>
        <tr>
            <th colspan="11" style="font-weight: bold; text-align: center; font-size: 16px;">
                NIRWANA ALABARE GARMENT
            </th>
        </tr>
        <tr>
            <th colspan="11" style="font-weight: bold; text-align: center; font-size: 14px;">
                Laporan Booking Stock
            </th>
        </tr>
        <tr>
            <th colspan="11" style="text-align: center;">
                Periode: {{ $tgl_awal ? date('d-M-Y', strtotime($tgl_awal)) : '-' }} s/d {{ $tgl_akhir ? date('d-M-Y', strtotime($tgl_akhir)) : '-' }}
            </th>
        </tr>

        <tr>
            <th colspan="11"></th>
        </tr>

        <tr>
            <th style="font-weight: bold; text-align: center; background-color: #2E4D68; color: white;">No</th>
            <th style="font-weight: bold; text-align: center; background-color: #2E4D68; color: white;">Tgl Booking</th>
            <th style="font-weight: bold; text-align: center; background-color: #2E4D68; color: white;">No Booking</th>
            <th style="font-weight: bold; text-align: center; background-color: #2E4D68; color: white;">Jenis</th>
            <th style="font-weight: bold; text-align: center; background-color: #2E4D68; color: white;">Nama Barang</th>
            <th style="font-weight: bold; text-align: center; background-color: #2E4D68; color: white;">Qty</th>
            <th style="font-weight: bold; text-align: center; background-color: #2E4D68; color: white;">Satuan</th>
            <th style="font-weight: bold; text-align: center; background-color: #2E4D68; color: white;">WS</th>
            <th style="font-weight: bold; text-align: center; background-color: #2E4D68; color: white;">Keterangan</th>
            <th style="font-weight: bold; text-align: center; background-color: #2E4D68; color: white;">Pembuat</th>
            <th style="font-weight: bold; text-align: center; background-color: #2E4D68; color: white;">Status</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($data as $d)
            <tr>
                <td style="text-align: center;">{{ $loop->iteration }}</td>
                <td style="text-align: center;">{{ date('d-m-Y', strtotime($d->tanggal_booking)) }}</td>
                <td>{{ $d->no_booking }}</td>
                <td style="text-align: center;">
                    @if($d->jenis == 'fabric') Fabric
                    @elseif($d->jenis == 'accessoris') Accessoris
                    @else {{ $d->jenis }}
                    @endif
                </td>
                <td>{{ $d->nama_barang }}</td>
                <td style="text-align: right;">{{ floatval($d->qty) }}</td>
                <td style="text-align: center;">{{ $d->satuan }}</td>
                <td style="text-align: center;">{{ $d->ws }}</td>
                <td>{{ $d->keterangan }}</td>
                <td>{{ $d->created_by }}</td>
                <td style="text-align: center;">{{ $d->status }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="11" style="text-align: center;">Tidak ada data pada periode dan filter tersebut.</td>
            </tr>
        @endforelse
    </tbody>
</table>
