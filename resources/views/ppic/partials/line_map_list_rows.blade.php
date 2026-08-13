@forelse ($lineMap as $row)
    <tr>
        <td>{{ $lineNameByUsername[$row->line] ?? $row->line }}</td>
        <td>{{ $row->tgl_start ? date('d-m-Y', strtotime($row->tgl_start)) : '-' }}</td>
        <td>{{ $row->tgl_end ? date('d-m-Y', strtotime($row->tgl_end)) : '-' }}</td>
        <td>{{ $row->style }}</td>
        <td>{{ $row->product_group }}</td>
        <td>{{ $row->buyer }}</td>
        <td>{{ $row->smv }}</td>
        <td>{{ $row->efficiency !== null ? number_format($row->efficiency, 0) . '%' : '-' }}
        </td>
        <td>{{ $row->qty_order !== null ? number_format($row->qty_order, 0, ',', '.') : '-' }}</td>
        <td>{{ $row->tot_days_rounded }} hari</td>
        <td>
            @if (count($row->ramp_up_efficiency))
                {{ count($row->ramp_up_efficiency) }} hari
            @else
                -
            @endif
        </td>
        <td>{{ $row->created_by }}</td>
        <td>{{ $row->updated_at ? date('d-m-Y H:i:s', strtotime($row->updated_at)) : '-' }}</td>
        <td class="text-nowrap">
            @if ($canEditLineMap)
                <button type="button" class="btn btn-outline-warning btn-sm"
                    data-bs-toggle="modal" data-bs-target="#newLineMapModal"
                    onclick='openEditLineMap(@json($row->edit_payload))'>
                    <i class="fas fa-pen"></i> Edit
                </button>
                <button type="button" class="btn btn-outline-danger btn-sm"
                    onclick="cancelLineMap({{ $row->id }})">
                    <i class="fas fa-trash"></i> Hapus
                </button>
            @else
                <span class="text-muted">-</span>
            @endif
        </td>
    </tr>
@empty
    <tr>
        <td colspan="14" class="text-center text-muted">Belum ada data</td>
    </tr>
@endforelse