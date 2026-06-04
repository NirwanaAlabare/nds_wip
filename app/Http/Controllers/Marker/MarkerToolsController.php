<?php

namespace App\Http\Controllers\Marker;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use Yajra\DataTables\Facades\DataTables;
use \avadim\FastExcelLaravel\Excel as FastExcel;
use DB;
use PDF;
use Carbon\Carbon;

class MarkerToolsController extends Controller
{
    public function index()
    {
        $orders = DB::connection('mysql_sb')->table('act_costing')->select('id', 'kpno', 'styleno')->where('status', '!=', 'CANCEL')->where('cost_date', '>=', '2023-01-01')->where('type_ws', 'STD')->orderBy('cost_date', 'desc')->orderBy('kpno', 'asc')->groupBy('kpno')->get();

        return view('marker.tools.tools', [
            "page" => "dashboard-marker",
            "orders" => $orders
        ]);
    }

    public function getMarkerActivityLog(Request $request)
    {
        $markerModels = [
            'App\\Models\\Marker\\Marker',
            'App\\Models\\Marker\\MarkerDetail',
        ];

        $query = DB::table('activity_log')->
            select([
                'activity_log.id',
                'activity_log.description',
                'activity_log.subject_type',
                'activity_log.subject_id',
                'activity_log.causer_id',
                'activity_log.properties',
                'activity_log.created_at',
                'users.name as causer_name',
                'users.username as causer_username',
            ])
            ->leftJoin('users', 'users.id', '=', 'activity_log.causer_id')
            ->whereIn('activity_log.subject_type', $markerModels);

        if ($request->dateFrom) {
            $query->where('activity_log.created_at', '>=', $request->dateFrom." 00:00:00");
        }
        if ($request->dateTo) {
            $query->where('activity_log.created_at', '<=', $request->dateTo." 23:59:59");
        }
        if ($request->event) {
            $query->where('activity_log.description', $request->event);
        }
        if ($request->model) {
            $query->where('activity_log.subject_type', $request->model);
        }

        $data = $query->orderByDesc('activity_log.created_at')->get();

        return DataTables::of($data)
            ->addColumn('model_name', function ($row) {
                return str_contains($row->subject_type, 'MarkerDetail') ? 'Marker Detail' : 'Marker';
            })
            ->addColumn('request_info', function ($row) {
                $props  = json_decode($row->properties, true);
                $route  = $props['route']  ?? null;
                $action = $props['action'] ?? null;
                $url    = $props['url']    ?? null;

                if (!$route && !$action && !$url) return '-';

                $html = '';
                if ($route)  $html .= "<span class='badge badge-light border d-block mb-1'>Route : {$route}</span>";
                if ($url)    $html .= "<span class='badge badge-light border d-block mb-1'>URL : " . e(parse_url($url, PHP_URL_PATH)) . "</span>";
                if ($action) $html .= "<span class='badge badge-light border d-block mb-1'>Action : {$action}</span>";

                return $html;
            })
            ->addColumn('properties_formatted', function ($row) {
                $props = json_decode($row->properties, true);
                if (!$props) return '-';

                $attributes = $props['attributes'] ?? [];
                $old        = $props['old']        ?? [];

                $lines = [];
                $keys  = array_unique(array_merge(array_keys($attributes), array_keys($old)));
                foreach ($keys as $key) {
                    $newVal = $attributes[$key] ?? null;
                    $oldVal = $old[$key]        ?? null;
                    if ($oldVal !== null && $newVal !== null && $oldVal != $newVal) {
                        $lines[] = "<b>{$key}</b>: <span class='text-muted'>" . e($oldVal) . "</span> → <span class='text-primary'>" . e($newVal) . "</span>";
                    } elseif ($newVal !== null && $oldVal === null) {
                        $lines[] = "<b>{$key}</b>: <span class='text-success'>" . e($newVal) . "</span>";
                    }
                }

                return implode('<br>', $lines) ?: '-';
            })
            ->rawColumns(['model_name', 'request_info', 'properties_formatted'])
            ->toJson();
    }

    public function exportMarkerActivityLog(Request $request)
    {
        $markerModels = [
            'App\\Models\\Marker\\Marker',
            'App\\Models\\Marker\\MarkerDetail',
        ];

        $query = DB::table('activity_log')
            ->select([
                'activity_log.id',
                'activity_log.description',
                'activity_log.subject_type',
                'activity_log.subject_id',
                'activity_log.properties',
                'activity_log.created_at',
                'users.name as causer_name',
                'users.username as causer_username',
            ])
            ->leftJoin('users', 'users.id', '=', 'activity_log.causer_id')
            ->whereIn('activity_log.subject_type', $markerModels);

        if ($request->dateFrom) {
            $query->where('activity_log.created_at', '>=', $request->dateFrom . ' 00:00:00');
        }
        if ($request->dateTo) {
            $query->where('activity_log.created_at', '<=', $request->dateTo . ' 23:59:59');
        }
        if ($request->event) {
            $query->where('activity_log.description', $request->event);
        }
        if ($request->model) {
            $query->where('activity_log.subject_type', $request->model);
        }

        $data = $query->orderByDesc('activity_log.created_at')->get();

        $excel = FastExcel::create('Marker Activity Log');
        $sheet = $excel->getSheet();

        $dateFrom = $request->dateFrom ?? '-';
        $dateTo   = $request->dateTo   ?? '-';

        $sheet->writeTo('A1', 'Marker Activity Log', ['font-size' => 14, 'font-bold' => true]);
        $sheet->mergeCells('A1:H1');
        $sheet->writeTo('A2', 'Periode : ' . $dateFrom . ' s/d ' . $dateTo);
        $sheet->mergeCells('A2:H2');

        $headers = ['A3' => 'No', 'B3' => 'Tanggal', 'C3' => 'Subject ID', 'D3' => 'Event',
                    'E3' => 'Model', 'F3' => 'Route', 'G3' => 'Action', 'H3' => 'URL',
                    'I3' => 'Perubahan', 'J3' => 'Oleh'];

        foreach ($headers as $cell => $label) {
            $sheet->writeTo($cell, $label)->applyFontStyleBold()->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        }

        $sheet->writeAreas();

        $no = 1;
        foreach ($data as $row) {
            $props      = json_decode($row->properties, true) ?? [];
            $attributes = $props['attributes'] ?? [];
            $old        = $props['old']        ?? [];
            $route      = $props['route']      ?? '-';
            $action     = $props['action']     ?? '-';
            $url        = $props['url']        ?? '-';

            // Build diff text
            $lines = [];
            $keys  = array_unique(array_merge(array_keys($attributes), array_keys($old)));
            foreach ($keys as $key) {
                $newVal = $attributes[$key] ?? null;
                $oldVal = $old[$key]        ?? null;
                if ($oldVal !== null && $newVal !== null && $oldVal != $newVal) {
                    $lines[] = "{$key}: {$oldVal} → {$newVal}";
                } elseif ($newVal !== null && $oldVal === null) {
                    $lines[] = "{$key}: {$newVal}";
                }
            }

            $modelName = str_contains($row->subject_type, 'MarkerDetail') ? 'Marker Detail' : 'Marker';

            $sheet->writeRow([
                $no++,
                $row->created_at  ?? '-',
                $row->subject_id  ?? '-',
                $row->description ?? '-',
                $modelName,
                $route,
                $action,
                $url,
                implode("\n", $lines) ?: '-',
                $row->causer_name ?? 'System',
            ])->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        }

        $filename = 'Marker_Activity_Log_' . $dateFrom . '_sd_' . $dateTo . '.xlsx';

        return $excel->download($filename);
    }
}
