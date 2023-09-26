<!DOCTYPE html>
<html lang="en">

<table border="1" class="table table-bordered table-striped">
    <tr>
        <td colspan='32'>Laporan Pemakaian Cutting</td>
    </tr>
    <tr>
        <td colspan='32'>{{ date('d-M-Y', strtotime($from)) }} - {{ date('d-M-Y', strtotime($to)) }}
        </td>
    </tr>
    <thead>
        <tr>
            <td>No</td>
            <td>WS</td>
            <td>Color</td>
            <td>roll</td>
            <td>lot</td>
            <td>cons_marker</td>
            <td>cons_pipping</td>
            <td>cons_ampar</td>
            <td>cons_act</td>
            <td>panel</td>
            <td>unit</td>
            <td>lembar_gelaran</td>
            <td>tot_ratio</td>
            <td>panjang_marker</td>
            <td>unit_panjang_marker</td>
            <td>panjang_act</td>
            <td>unit_p_act</td>
            <td>lebar_marker</td>
            <td>unit_lebar_marker</td>
            <td>l_act</td>
            <td>unit_l_act</td>
            <td>qty_potong</td>
            <td>actual_gelar_kain</td>
            <td>kain_terpakai</td>
            <td>sisa_kain</td>
            <td>sisa_tidak_bisa</td>
            <td>sambungan</td>
            <td>piping</td>
            <td>kepala_kain</td>
            <td>reject</td>
            <td>total_aktual_kain</td>
            <td>cons</td>
            <td>created_at</td>
        </tr>
    </thead>
    <tbody>
        @php
            $no = 1;
        @endphp
        @foreach ($data as $item)
            <tr>
                <td>{{ $no++ }}.</td>
                <td>{{ $item->act_costing_ws }}</td>
                <td>{{ $item->color }}</td>
                <td>{{ $item->roll }}</td>
                <td>{{ $item->lot }}</td>
                <td>{{ $item->cons_marker }}</td>
                <td>{{ $item->cons_pipping }}</td>
                <td>{{ $item->cons_ampar }}</td>
                <td>{{ $item->cons_act }}</td>
                <td>{{ $item->panel }}</td>
                <td>{{ $item->unit }}</td>
                <td>{{ $item->lembar_gelaran }}</td>
                <td>{{ $item->tot_ratio }}</td>
                <td>{{ $item->panjang_marker }}</td>
                <td>{{ $item->unit_panjang_marker }}</td>
                <td>{{ $item->panjang_act }}</td>
                <td>{{ $item->unit_p_act }}</td>
                <td>{{ $item->lebar_marker }}</td>
                <td>{{ $item->unit_lebar_marker }}</td>
                <td>{{ $item->l_act }}</td>
                <td>{{ $item->unit_l_act }}</td>
                <td>{{ $item->qty_potong }}</td>
                <td>{{ $item->actual_gelar_kain }}</td>
                <td>{{ $item->kain_terpakai }}</td>
                <td>{{ $item->sisa_kain }}</td>
                <td>{{ $item->sisa_tidak_bisa }}</td>
                <td>{{ $item->sambungan }}</td>
                <td>{{ $item->piping }}</td>
                <td>{{ $item->kepala_kain }}</td>
                <td>{{ $item->reject }}</td>
                <td>{{ $item->total_aktual_kain }}</td>
                <td>{{ $item->cons }}</td>
                <td>{{ $item->created_at }}</td>

            </tr>
        @endforeach
    </tbody>

</table>

</html>
