<!DOCTYPE html>
<html lang="en">

<table class="table">
    <tr>
        <td>
            Laporan Pemakaian Roll
        </td>
    </tr>
    <tr>
        <td>
            {{ $from. " - " .$to}}
        </td>
    </tr>
    <thead>
        <tr>
            <td>No</td>
            <td>Tgl. Input</td>
            <td>No. Form</td>
            <td>No. Meja</td>
            <td>WS</td>
            <td>Buyer</td>
            <td>Style</td>
            <td>Color</td>
            <td>Color Actual</td>
            <td>Bulan</td>
            <td>Qty Order</td>
            <td>ID Roll</td>
            <td>Nama Item</td>
            <td>Roll Number</td>
            <td>Lot</td>
            <td>Cons WS</td>
            <td>Cons Marker</td>
            <td>Cons Piping</td>
            <td>Cons Ampar</td>
            <td>Cons Act</td>
            <td>Panel</td>
            <td>Qty</td>
            <td>Unit</td>
            <td>Sisa Kain</td>
            <td>Lembar Gelaran</td>
            <td>Total Ratio</td>
            <td>Pjg Marker</td>
            <td>Pjg Act</td>
            <td>Unit Act</td>
            <td>Lbr Marker</td>
            <td>Lbr Act</td>
            <td>Unit Lbr Act</td>
            <td>Total Pemakaian</td>
            <td>Sisa Kain</td>
            <td>Sisa Gelar</td>
            <td>Sambungan</td>
            <td>Est Amparan</td>
            <td>Average Time</td>
            <td>Kepala Kain</td>
            <td>Sisa Tidak Bisa</td>
            <td>Reject</td>
            <td>Piping</td>
            <td>Short Roll</td>
            <td>Remark</td>
            <td>Operator</td>
        </tr>
    </thead>
    <tbody>
        @php
            $no = 1;
        @endphp
        @foreach ($data as $item)
            <tr>
                <td>{{ $no++ }}.</td>
                <td>{{ $item->tgl_input }}</td>
                <td>{{ $item->no_form }}</td>
                <td>{{ $item->no_meja }}</td>
                <td>{{ $item->act_costing_ws }}</td>
                <td>{{ $item->buyer }}</td>
                <td>{{ $item->style }}</td>
                <td>{{ $item->color }}</td>
                <td>{{ $item->color_act }}</td>
                <td>{{ $item->bulan }}</td>
                <td>{{ $item->qty_order }}</td>
                <td>{{ $item->id_roll }}</td>
                <td>{{ $item->detail_item }}</td>
                <td>{{ $item->roll_number }}</td>
                <td>{{ $item->lot }}</td>
                <td>{{ $item->cons_ws }}</td>
                <td>{{ $item->cons_marker }}</td>
                <td>{{ $item->cons_pipping }}</td>
                <td>{{ $item->cons_ampar }}</td>
                <td>{{ $item->cons_act }}</td>
                <td>{{ $item->panel }}</td>
                <td>{{ $item->qty }}</td>
                <td>{{ $item->unit }}</td>
                <td>{{ $item->sisa_kain }}</td>
                <td>{{ $item->lembar_gelaran }}</td>
                <td>{{ $item->tot_ratio }}</td>
                <td>{{ $item->p_marker }}</td>
                <td>{{ $item->p_act }}</td>
                <td>{{ $item->unit_p_act }}</td>
                <td>{{ $item->lebar_marker }}</td>
                <td>{{ $item->l_act }}</td>
                <td>{{ $item->unit_l_act }}</td>
                <td>{{ $item->total_pemakaian_roll }}</td>
                <td>{{ $item->sisa_kain }}</td>
                <td>{{ $item->sisa_gelaran }}</td>
                <td>{{ $item->sambungan }}</td>
                <td>{{ $item->est_amparan }}</td>
                <td>{{ $item->average_time }}</td>
                <td>{{ $item->kepala_kain }}</td>
                <td>{{ $item->sisa_tidak_bisa }}</td>
                <td>{{ $item->reject }}</td>
                <td>{{ $item->piping }}</td>
                <td>{{ $item->short_roll }}</td>
                <td>{{ $item->remark }}</td>
                <td>{{ $item->operator }}</td>

            </tr>
        @endforeach
    </tbody>

</table>

</html>
