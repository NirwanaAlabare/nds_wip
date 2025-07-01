<div>
    <div class="form-group row">
        <div class="col-md-6">
            <h6><strong>No Maintain:</strong> {{ $data->no_maintain }}</h6>
        </div>
        <div class="col-md-6">
            <h6><strong>Tanggal:</strong> {{ date('d-M-Y', strtotime($data->tgl_maintain)) }}</h6>
        </div>
        <div class="col-md-6">
            <h6><strong>Status:</strong> {{ $data->status }}</h6>
        </div>
    </div>
    <hr>

<table class="table table-bordered table-sm" id="detailTable">
        <thead>
            <tr>
                <th style="width: 4%">No</th>
                <th style="width: 34%">Nama Supplier</th>
                <th style="width: 19%">No BPB</th>
                <th style="width: 10%">Tgl BPB</th>
                <th style="width: 15%">No PO</th>
                <th style="width: 20%">Keterangan</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($detail as $i => $item)
            <tr>
                <td>{{ $i+1 }}</td>
                <td>{{ $item->nama_supp }}</td>
                <td>{{ $item->no_bpb }}</td>
                <td>{{ $item->tgl_bpb }}</td>
                <td>{{ $item->no_po }}</td>
                <td>{{ $item->keterangan }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
