<!DOCTYPE html>
<html lang="en">
<table class="table-bordered">
    <thead>
        <tr>
            <th rowspan="2" style="border:1px solid black;font-weight:bold;text-align: center">PO
            </th>
            <th rowspan="2" colspan="3" style="border:1px solid black;font-weight:bold;text-align: center">CTN NO
            </th>
            <th rowspan="2" style="border:1px solid black;font-weight:bold;text-align: center">QTN QTY</th>
            <th rowspan="2" style="border:1px solid black;font-weight:bold;text-align: center">COLOR</th>
            <th colspan="3" style="border:1px solid black;font-weight:bold;text-align: center">PREPACK (PCS/POLY)
            </th>
            <th rowspan="2" style="border:1px solid black;font-weight:bold;text-align: center">TOTAL QTY</th>
            <th rowspan="2" style="border:1px solid black;font-weight:bold;text-align: center">CARTON MEASUREMENT
            </th>
            <th colspan="9" style="border:1px solid black;font-weight:bold;text-align: center">ASSORTMENT</th>
        </tr>
        <tr>
            <th style="border:1px solid black;font-weight:bold;text-align: center">POLYBAG/CTN</th>
            <th style="border:1px solid black;font-weight:bold;text-align: center">QTY PCS/CARTON</th>
            <th style="border:1px solid black;font-weight:bold;text-align: center">QTY</th>
            <th style="border:1px solid black;font-weight:bold;text-align: center">XS</th>
            <th style="border:1px solid black;font-weight:bold;text-align: center">S</th>
            <th style="border:1px solid black;font-weight:bold;text-align: center">M</th>
            <th style="border:1px solid black;font-weight:bold;text-align: center">L</th>
            <th style="border:1px solid black;font-weight:bold;text-align: center">XL</th>
            <th style="border:1px solid black;font-weight:bold;text-align: center">XXL</th>
            <th style="border:1px solid black;font-weight:bold;text-align: center">L+</th>
            <th style="border:1px solid black;font-weight:bold;text-align: center">XL+</th>
            <th style="border:1px solid black;font-weight:bold;text-align: center">XXL+</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>{{ $po }}</td>
            <td>1</td>
            <td>-</td>
            <td>1</td>
            <td>1</td>
            <td>PEACHY TREAT</td>
            <td>40</td>
            <td>1</td>
            <td>40</td>
            <td>40</td>
            <td>587 X 385 X 99 MM (GID 4)</td>
            <td></td>
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
            <td>2</td>
            <td>-</td>
            <td>2</td>
            <td>1</td>
            <td>PEACHY TREAT</td>
            <td>35</td>
            <td>1</td>
            <td>35</td>
            <td>35</td>
            <td>587 X 385 X 99 MM (GID 4)</td>
            <td></td>
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
            <td>3</td>
            <td>-</td>
            <td>3</td>
            <td>1</td>
            <td>PEACHY TREAT</td>
            <td>30</td>
            <td>1</td>
            <td>30</td>
            <td>30</td>
            <td>587 X 385 X 99 MM (GID 4)</td>
            <td></td>
            <td></td>
            <td></td>
            <td>30</td>
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
            <td>4</td>
            <td>-</td>
            <td>4</td>
            <td>1</td>
            <td>PEACHY TREAT</td>
            <td>25</td>
            <td>1</td>
            <td>25</td>
            <td>25</td>
            <td>587 X 385 X 99 MM (GID 4)</td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td>25</td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
        </tr>
        <tr>
            <td>{{ $po }}</td>
            <td>5</td>
            <td>-</td>
            <td>5</td>
            <td>1</td>
            <td>PEACHY TREAT</td>
            <td>104</td>
            <td>1</td>
            <td>104</td>
            <td>104</td>
            <td>587 X 385 X 297 MM (GID 1)</td>
            <td>18</td>
            <td>11</td>
            <td>20</td>
            <td>19</td>
            <td>7</td>
            <td>12</td>
            <td>5</td>
            <td>5</td>
            <td>7</td>
            <td></td>
            <td></td>
        </tr>
        <tr>
            <td>{{ $po }}</td>
            <td>6</td>
            <td>-</td>
            <td>10</td>
            <td>5</td>
            <td>PEACHY TREAT</td>
            <td>15</td>
            <td>1</td>
            <td>15</td>
            <td>75</td>
            <td>587 X 385 X 297 MM (GID 1)</td>
            <td></td>
            <td></td>
            <td>10</td>
            <td>5</td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
        </tr>
        {{-- @foreach ($data as $item)
            <tr>
                <td>{{ $item->id }}</td>
                <td>{{ $item->po }}</td>
                <td>{{ $item->no_carton }}</td>
                <td>{{ $item->notes }}</td>
                <td>{{ $item->qty_isi }}</td>
            </tr>
        @endforeach --}}
    </tbody>

</table>

</html>
