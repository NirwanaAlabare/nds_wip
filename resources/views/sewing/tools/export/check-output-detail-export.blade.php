<table class="table table-bordered table-sm" id="list-table">
    <tr>
        <td colspan="19">
            OUTPUT DETAIL
            {{ ($buyer ? " | buyer : ".$buyer : "") }}
            {{ ($ws ? " | ws : ".$ws : "") }}
            {{ ($style ? " | style : ".$style : "") }}
            {{ ($color ? " | color : ".$color : "") }}
            {{ ($size ? " | size : ".$size : "") }}
            {{ ($kode ? " | kode : ".$kode : "") }}
            {{ ($tanggal_loading ? " | tanggal_loading : ".$tanggal_loading : "") }}
            {{ ($line_loading ? " | line_loading : ".$line_loading : "") }}
            {{ ($tanggal_plan ? " | tanggal_plan : ".$tanggal_plan : "") }}
            {{ ($tanggal_output ? " | tanggal_output : ".$tanggal_output : "") }}
            {{ ($tanggal_packing ? " | tanggal_packing : ".$tanggal_packing : "") }}
            {{ ($line_output ? " | line_output : ".$line_output : "") }}
            {{ ($status_output ? " | status_output : ".$status_output : "") }}
            {{ ($defect_output ? " | defect_output : ".$defect_output : "") }}
            {{ ($allocation_output ? " | allocation_output : ".$allocation_output : "") }}
            {{ ($line_packing ? " | line_packing : ".$line_packing : "") }}
            {{ ($status_packing ? " | status_packing : ".$status_packing : "") }}
            {{ ($defect_packing ? " | defect_packing : ".$defect_packing : "") }}
            {{ ($allocation_packing ? " | allocation_packing : ".$allocation_packing : "") }}
            {{ ($crossline_loading ? " | crossline_loading : ".$crossline_loading : "") }}
            {{ ($crossline_output ? " | crossline_output : ".$crossline_output : "") }}
            {{ ($missmatch_code ? " | missmatch_code : ".$missmatch_code : "") }}
            {{ ($missmatch_code_packing ? " | missmatch_code_packing : ".$missmatch_code_packing : "") }}
            {{ ($back_date ? " | back_date : ".$back_date : "") }}
            {{ ($back_date_packing ? " | back_date_packing : ".$back_date_packing : "") }}
        </td>
    </tr>
    <tr>
        <th style="border: 1px solid black;">Kode</th>
        <th style="border: 1px solid black;">Buyer</th>
        <th style="border: 1px solid black;">WS</th>
        <th style="border: 1px solid black;">Style</th>
        <th style="border: 1px solid black;">Color</th>
        <th style="border: 1px solid black;">Size</th>
        <th style="border: 1px solid black;">Tanggal Loading</th>
        <th style="border: 1px solid black;">Line Loading</th>
        <th style="border: 1px solid black;">Tanggal Plan</th>
        <th style="border: 1px solid black;">Tanggal Sewing</th>
        <th style="border: 1px solid black;">Line Sewing</th>
        <th style="border: 1px solid black;">Status Sewing</th>
        <th style="border: 1px solid black;">Defect Sewing</th>
        <th style="border: 1px solid black;">Alokasi Sewing</th>
        <th style="border: 1px solid black;">Tanggal Finishing</th>
        <th style="border: 1px solid black;">Line Finishing</th>
        <th style="border: 1px solid black;">Status Finishing</th>
        <th style="border: 1px solid black;">Defect Finishing</th>
        <th style="border: 1px solid black;">Alokasi Finishing</th>
    </tr>
    @foreach ($data as $d)
        <tr>
            <td style="border: 1px solid black;">{{ $d->kode }}</td>
            <td style="border: 1px solid black;">{{ $d->buyer }}</td>
            <td style="border: 1px solid black;">{{ $d->ws }}</td>
            <td style="border: 1px solid black;">{{ $d->style }}</td>
            <td style="border: 1px solid black;">{{ $d->color }}</td>
            <td style="border: 1px solid black;">{{ $d->size }}</td>
            <td style="border: 1px solid black;">{{ $d->tanggal_loading }}</td>
            <td style="border: 1px solid black;">{{ $d->line_loading }}</td>
            <td style="border: 1px solid black;">{{ $d->tanggal_plan }}</td>
            <td style="border: 1px solid black;">{{ $d->tanggal_output }}</td>
            <td style="border: 1px solid black;">{{ $d->line_output }}</td>
            <td style="border: 1px solid black;">{{ $d->status_output }}</td>
            <td style="border: 1px solid black;">{{ $d->defect_output }}</td>
            <td style="border: 1px solid black;">{{ $d->allocation_output }}</td>
            <td style="border: 1px solid black;">{{ $d->tanggal_output_packing }}</td>
            <td style="border: 1px solid black;">{{ $d->line_output_packing }}</td>
            <td style="border: 1px solid black;">{{ $d->status_output_packing }}</td>
            <td style="border: 1px solid black;">{{ $d->defect_output_packing }}</td>
            <td style="border: 1px solid black;">{{ $d->allocation_output_packing }}</td>
        </tr>
    @endforeach
</table>
