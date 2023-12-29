<!DOCTYPE html>
<html>

        @foreach ($data_header as $dheader)
        <table width="100%">
            <tr>
                <td><b>Inspection Number :</b> {{ $dheader->no_insp }}</td>
                <td><b>ID Item :</b> {{ $dheader->id_item }}</td>
                <td><b>Style :</b> {{ $dheader->no_style }}</td>
            </tr>
            <tr>
                <td><b>Inspection Date :</b> {{ $dheader->tgl_insp }}</td>
                <td><b>Fabric Name :</b> {{ $dheader->fabric_name }}</td>
                <td><b>Lot :</b> {{ $dheader->no_lot }}</td>
            </tr>
            <tr>
                <td><b>Color:</b> {{ $dheader->color }}</td>
                 @foreach ($avg_poin as $avgpoin)
                <td><b>Average Actual Point: {{ $avgpoin->avg_poin }}</b></td>
                @endforeach
                <td><b>Status: {{ $dheader->status }}</b></td>
            </tr>
        </table>
        @endforeach
        

        @foreach ($data_detail as $ddetail)
        <table width="100%">
            <tr>
                <td><b>Form Number:</b> {{ $ddetail->no_form }}</td>
                <td><b>Width:</b> {{ $ddetail->width_fabric }}</td>
                <td><b>Weight:</b> {{ $ddetail->weight_fabric }}</td>
            </tr>
            <tr>
                <td><b>Date:</b> {{ $ddetail->tgl_form }}</td>
                <td><b>Gramage:</b> {{ $ddetail->gramage }}</td>
                <td><b>Inspector:</b> {{ $ddetail->inspektor }}</td>
            </tr>
            <tr>
                <td><b>Fabric Supplier:</b> {{ $ddetail->fabric_supp }}</td>
                <td><b>Roll:</b> {{ $ddetail->no_roll }}</td>
                <td><b>Machine No:</b> {{ $ddetail->no_mesin }}</td>
            </tr>
        </table>
        
        <table >
                <thead>
                    <tr>
                        <th>Length</th>
                        <th>Code</th>
                        <th>Up To 3"</th>
                        <th>Over 3" - 6"</th>
                        <th>Over 6" - 9"</th>
                        <th>Over 9"</th>
                        <th>Width</th>
                    </tr>
                </thead>
                <tbody>
        @foreach ($data_temuan as $dtemuan)
        @if( $dtemuan->no_form == $ddetail->no_form)
                    <tr>
                        <td>{{ $dtemuan->lenght_fabric }}</td>
                        <td>{{ $dtemuan->kode_def }}</td>
                        <td>{{ $dtemuan->upto3 }}</td>
                        <td>{{ $dtemuan->over3 }}</td>
                        <td>{{ $dtemuan->over6 }}</td>
                        <td>{{ $dtemuan->over9 }}</td>
                        <td>{{ $dtemuan->width_det }}</td>
                    </tr>
        @endif
        @endforeach
                </tbody>
            </table>
            
        <table width="100%" >
            <tr>
                <td><b>Barcode Length:</b> {{ $ddetail->lenght_barcode }}</td>
                <td><b>Remark:</b> {{ $ddetail->catatan }}</td>
            </tr>
            <tr>
                <td><b>Actual Length:</b> {{ $ddetail->lenght_actual }}</td>
                @foreach ($data_sum as $dsum2)
                @if( $dsum2->no_form == $ddetail->no_form)
                @if( $dsum2->akt_poin > 20)
                <td><b>Status: REJECT</b></td>
                @else
                <td><b>Status: PASS</b> </td>
                @endif
                @endif
                @endforeach
            </tr>
        </table>
        
        <table >
                <thead>
                    <tr>
                        <th>Up To 3"</th>
                        <th>Over 3" - 6"</th>
                        <th>Over 6" - 9"</th>
                        <th>Over 9"</th>
                        <th>Width</th>
                        <th>Total Point</th>
                        <th>Actual Point</th>
                    </tr>
                </thead>
                <tbody>
        @foreach ($data_sum as $dsum)
        @if( $dsum->no_form == $ddetail->no_form)
                    <tr>
                        <td>{{ $dsum->upto3 }}</td>
                        <td>{{ $dsum->over3 }}</td>
                        <td>{{ $dsum->over6 }}</td>
                        <td>{{ $dsum->over9 }}</td>
                        <td>{{ $dsum->width_fabric }}</td>
                        <td>{{ $dsum->ttl_poin }}</td>
                        <td>{{ $dsum->akt_poin }}</td>
                    </tr>
        @endif
        @endforeach
                </tbody>
            </table>
        
        @endforeach
</html>

<!-- 
 <!DOCTYPE html>
 <html>

    <table>
        <tr>
            <td>tes</td>
        </tr>
    </table>
 
 </html> -->