<!DOCTYPE html>
<html lang="en">
<?php
$size = DB::select("SELECT
distinct(m.size) size, a.dest from ppic_master_so a
inner join master_sb_ws m on a.id_so_det = m.id_so_det
left join master_size_new msn on m.size = msn.size
where po = '$po' and a.dest = '$dest'
group by m.size
order by urutan asc");
$data_size = $size;
$count = count($data_size);
?>
<table class="table-bordered">
    <thead>
        <tr>
            <th rowspan="2" style="border:1px solid black;font-weight:bold;text-align: center">PO
            </th>
            <th rowspan="2" style="border:1px solid black;font-weight:bold;text-align: center">Dest
            </th>
            <th rowspan="2" colspan="3" style="border:1px solid black;font-weight:bold;text-align: center">CTN NO
            </th>
            <th rowspan="2" style="border:1px solid black;font-weight:bold;text-align: center">CTN QTY</th>
            <th rowspan="2" style="border:1px solid black;font-weight:bold;text-align: center">COLOR</th>
            <th rowspan="2" style="border:1px solid black;font-weight:bold;text-align: center">PREPACK CODE</th>
            <th colspan="{{ $count }}" style="border:1px solid black;font-weight:bold;text-align: center">
                ASSORTMENT</th>
        </tr>
        <tr>
            <?php
            foreach ($data_size as $s) {
                echo '<th style="border:1px solid black; font-weight:bold; text-align:center;">' . htmlspecialchars($s->size) . '</th>';
            }
            ?>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>{{ $po }}</td>
            <td>{{ $dest }}</td>
            <td>1</td>
            <td>-</td>
            <td>1</td>
            <td>1</td>
            <td>PEACHY TREAT</td>
            <td>SINGLE</td>
            <td>40</td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
        </tr>
        <tr>
            <td>{{ $po }}</td>
            <td>{{ $dest }}</td>
            <td>2</td>
            <td>-</td>
            <td>2</td>
            <td>1</td>
            <td>PEACHY TREAT</td>
            <td>SINGLE</td>
            <td></td>
            <td>35</td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
        </tr>
        <tr>
            <td>{{ $po }}</td>
            <td>{{ $dest }}</td>
            <td>3</td>
            <td>-</td>
            <td>7</td>
            <td>5</td>
            <td>PEACHY TREAT</td>
            <td>RATIO</td>
            <td></td>
            <td>100</td>
            <td>25</td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
        </tr>
    </tbody>
</table>

</html>
