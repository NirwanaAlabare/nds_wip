<!DOCTYPE html>
<html lang="en">
<table class="table">
    <thead>
        <tr>
            <th style="background-color: lightblue;border:1px solid black;font-weight:bold">ID</th>
            <th style="background-color: lightblue;border:1px solid black;font-weight:bold">PO</th>
            <th style="background-color: lightblue;border:1px solid black;font-weight:bold">No. Carton</th>
            <th style="background-color: lightblue;border:1px solid black;font-weight:bold">Notes</th>
            <th style="background-color: lightblue;border:1px solid black;font-weight:bold">Qty Isi</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($data as $item)
            <tr>
                <td>{{ $item->id }}</td>
                <td>{{ $item->po }}</td>
                <td>{{ $item->no_carton }}</td>
                <td>{{ $item->notes }}</td>
                <td>{{ $item->qty_isi }}</td>
            </tr>
        @endforeach
    </tbody>

</table>

</html>
