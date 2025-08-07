<?php

namespace App\Exports\Sewing;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use DB;

class CheckOutputDetailListExport implements FromView, ShouldAutoSize
{
    protected $query;

    function __construct($query, $buyer, $ws, $style, $color, $size, $kode, $tanggal_loading, $line_loading, $tanggal_plan, $tanggal_output, $tanggal_packing, $line_output, $status_output, $defect_output, $allocation_output, $line_packing, $status_packing, $defect_packing, $allocation_packing, $crossline_loading, $crossline_output, $missmatch_code, $missmatch_code_packing, $back_date, $back_date_packing) {
        $this->query = $query;
        $this->buyer = $buyer;
        $this->ws = $ws;
        $this->style = $style;
        $this->color = $color;
        $this->size = $size;
        $this->kode = $kode;
        $this->tanggal_loading = $tanggal_loading;
        $this->line_loading = $line_loading;
        $this->tanggal_plan = $tanggal_plan;
        $this->tanggal_output = $tanggal_output;
        $this->tanggal_packing = $tanggal_packing;
        $this->line_output = $line_output;
        $this->status_output = $status_output;
        $this->defect_output = $defect_output;
        $this->allocation_output = $allocation_output;
        $this->line_packing = $line_packing;
        $this->status_packing = $status_packing;
        $this->defect_packing = $defect_packing;
        $this->allocation_packing = $allocation_packing;
        $this->crossline_loading = $crossline_loading;
        $this->crossline_output = $crossline_output;
        $this->missmatch_code = $missmatch_code;
        $this->missmatch_code_packing = $missmatch_code_packing;
        $this->back_date = $back_date;
        $this->back_date_packing = $back_date_packing;
    }

    public function view(): View
    {
        $data = collect(DB::connection("mysql_sb")->select($this->query));

        $this->rowCount = $data->count();

        return view('sewing.tools.export.check-output-detail-export', [
            'rowCount' => $this->rowCount,
            'data' => $data,
            'buyer' => $this->buyer,
            'ws' => $this->ws,
            'style' => $this->style,
            'color' => $this->color,
            'size' => $this->size,
            'kode' => $this->kode,
            'tanggal_loading' => $this->tanggal_loading,
            'line_loading' => $this->line_loading,
            'tanggal_plan' => $this->tanggal_plan,
            'tanggal_output' => $this->tanggal_output,
            'tanggal_packing' => $this->tanggal_packing,
            'line_output' => $this->line_output,
            'status_output' => $this->status_output,
            'defect_output' => $this->defect_output,
            'allocation_output' => $this->allocation_output,
            'line_packing' => $this->line_packing,
            'status_packing' => $this->status_packing,
            'defect_packing' => $this->defect_packing,
            'allocation_packing' => $this->allocation_packing,
            'crossline_loading' => $this->crossline_loading,
            'crossline_output' => $this->crossline_output,
            'missmatch_code' => $this->missmatch_code,
            'missmatch_code_packing' => $this->missmatch_code_packing,
            'back_date' => $this->back_date,
            'back_date_packing' => $this->back_date_packing
        ]);
    }
}
