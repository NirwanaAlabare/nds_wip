<?php

namespace App\Http\Controllers\Marker;

use App\Http\Controllers\Controller;
use App\Models\Marker\MarkerDetail;
use App\Models\Marker\SubSize;
use App\Models\MasterSbWs;
use App\Imports\ImportSubSize;
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
            'App\\Models\\Part\\Part',
            'App\\Models\\Part\\PartDetail',
            'App\\Models\\Part\\PartDetailSecondary',
            'App\\Models\\Part\\PartForm',
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
                return $row->subject_type;
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

            $modelName = strpos($row->subject_type, 'MarkerDetail') !== false ? 'Marker Detail' : 'Marker';

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

    public function getSubSizeList(Request $request)
    {
        $data = SubSize::selectRaw("
                sub_size.id,
                sub_size.so_det_id,
                master_sb_ws.ws no_ws,
                master_sb_ws.color,
                master_sb_ws.size original_size,
                sub_size.size sub_size,
                sub_size.qty,
                sub_size.created_by_username,
                sub_size.created_at
            ")->
            leftJoin("master_sb_ws", "master_sb_ws.id_so_det", "=", "sub_size.so_det_id")->
            orderByDesc("sub_size.created_at")->
            get();

        return DataTables::of($data)->toJson();
    }

    public function addSubSize(Request $request)
    {
        $request->validate([
            'so_det_id'    => 'required',
            'sub_size'     => 'required|array|min:1',
            'sub_size.*'   => 'required|string|max:20',
            'qty'          => 'required|array|size:' . count((array) $request->sub_size),
            'qty.*'        => 'required|integer|min:1',
        ], [
            'sub_size.*.max' => 'Sub size maksimal 20 karakter.',
            'qty.*.required' => 'Qty wajib diisi.',
            'qty.*.integer'  => 'Qty harus berupa angka bulat.',
            'qty.*.min'      => 'Qty minimal 1.',
        ]);

        $qtys = array_values($request->qty);
        $subSizes = collect(array_values($request->sub_size))->map(function ($size, $i) use ($request, $qtys) {
            return SubSize::create([
                'so_det_id'           => $request->so_det_id,
                'size'                => $size,
                'qty'                 => (int) $qtys[$i],
                'created_by'          => Auth::id(),
                'created_by_username' => Auth::user()->username,
            ]);
        });

        return response()->json([
            'success' => true,
            'message' => 'Sub size berhasil ditambahkan',
            'data'    => $subSizes,
        ]);
    }

    public function removeSubSize(Request $request, $id = null)
    {
        $subSize = SubSize::find($id);

        $dataMessage = '';
        if ($subSize) {
            // Marker Validation
            // $markerDetail = MarkerDetail::where("so_det_id", $subSize->so_det_id)->first();
            // $marker = $markerDetail ? $markerDetail->marker : null;
            // $forms = $marker ? $marker->formCutInputs : null;
            // foreach ($forms as $form) {
            //     if (checkClosingDate($form->waktu_selesai)) {
            //         return array(
            //             "status" => 400,
            //             "message" => "Data tidak dapat dihapus karena periode sudah ditutup.",
            //             "additional" => "Closing"
            //         );
            //     }
            // }

            // Order Info
            $masterSbWs = MasterSbWs::where("id_so_det", $subSize->so_det_id)->first();
            $ws = $masterSbWs ? $masterSbWs->ws : "-";
            $color = $masterSbWs ? $masterSbWs->color : "-";
            $size = $masterSbWs ? $masterSbWs->size : "-";
            // Data Message
            $dataMessage = $subSize->id . " - " . $ws . " / Color : " . $color . " / Size : " . $size . " / Sub : " . $subSize->size;
            
            // Delete Sub Size
            $deleteSubSize = $subSize->delete();
            if ($deleteSubSize) {
                return  response()->json([
                    'status'  => 200,
                    'success' => true,
                    'message' => "Sub size '".$dataMessage."' berhasil dihapus",
                    'table'   => "sub-size-table"
                ]);
            }
        }

        return response()->json([
            'status'  => 400,
            'success' => false,
            'message' => "Sub size '".$dataMessage."' gagal dihapus",
            'table'   => "sub-size-table"
        ]);
    }

    public function massRemoveSubSize(Request $request)
    {
        $request->validate([
            'ids'   => 'required|array|min:1',
            'ids.*' => 'required|integer',
        ]);

        $ids = collect($request->ids)->map(fn ($id) => (int) $id)->unique()->values();

        DB::beginTransaction();
        try {
            $subSizes = SubSize::whereIn('id', $ids)->lockForUpdate()->get();

            $missing = $ids->diff($subSizes->pluck('id'));
            if ($missing->isNotEmpty()) {
                throw new \Exception("Sub size dengan ID " . $missing->implode(', ') . " tidak ditemukan (mungkin sudah dihapus).");
            }

            foreach ($subSizes as $subSize) {
                if (!$subSize->delete()) {
                    throw new \Exception("Sub size ID " . $subSize->id . " (" . $subSize->size . ") gagal dihapus.");
                }
            }

            DB::commit();

            return response()->json([
                'status'  => 200,
                'success' => true,
                'message' => $subSizes->count() . " sub size berhasil dihapus",
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Mass delete sub size gagal: ' . $e->getMessage());

            return response()->json([
                'status'  => 400,
                'success' => false,
                'message' => "Hapus massal dibatalkan, tidak ada data yang dihapus. " . $e->getMessage(),
            ], 400);
        }
    }

    public function importSubSize(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv',
        ]);

        try {
            $rows = Excel::toCollection(new ImportSubSize, $request->file('file'))->first() ?? collect();
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'File tidak dapat dibaca: ' . $e->getMessage(),
            ], 400);
        }

        // Normalisasi baris; nomor baris Excel = index + 2 (baris 1 = header)
        $rows = $rows->map(function ($row, $i) {
            return [
                'line'     => $i + 2,
                'ws'       => trim((string) ($row['ws'] ?? '')),
                'color'    => trim((string) ($row['color'] ?? '')),
                'size'     => trim((string) ($row['size'] ?? '')),
                'sub_size' => trim((string) ($row['sub_size'] ?? '')),
                'qty'      => trim((string) ($row['qty'] ?? '')),
                // Opsional: dipakai bila ws/color/size punya lebih dari satu so_det (beda dest)
                'dest'     => trim((string) ($row['dest'] ?? '')),
            ];
        })->filter(function ($row) {
            // Lewati baris yang benar-benar kosong
            return $row['ws'] !== '' || $row['color'] !== '' || $row['size'] !== '' || $row['sub_size'] !== '' || $row['qty'] !== '';
        })->values();

        if ($rows->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'File kosong atau header tidak sesuai. Header wajib: ws, color, size, sub_size, qty.',
            ], 400);
        }

        $errors = [];
        $key = fn ($ws, $color, $size) => strtolower($ws) . '|' . strtolower($color) . '|' . strtolower($size);

        // Ambil master untuk semua WS di file sekaligus
        // so_det yang sudah cancel tidak ikut dicocokkan
        $masters = MasterSbWs::select('id_so_det', 'ws', 'color', 'size', 'dest')
            ->whereIn('ws', $rows->pluck('ws')->filter()->unique()->values())
            ->whereRaw("(cancel IS NULL OR cancel != 'Y')")
            ->get()
            ->groupBy(fn ($m) => $key(trim($m->ws), trim($m->color), trim($m->size)));

        $seen = [];
        $rows = $rows->map(function ($row) use (&$errors, &$seen, $masters, $key) {
            $emptyCols = collect(['ws', 'color', 'size', 'sub_size', 'qty'])->filter(fn ($c) => $row[$c] === '');
            if ($emptyCols->isNotEmpty()) {
                $errors[] = "Baris {$row['line']}: kolom " . $emptyCols->implode(', ') . " kosong";
                return $row;
            }

            $invalid = false;
            if (mb_strlen($row['sub_size']) > 20) {
                $errors[] = "Baris {$row['line']}: sub_size '{$row['sub_size']}' lebih dari 20 karakter";
                $invalid = true;
            }
            if (!preg_match('/^\d+(\.0+)?$/', $row['qty']) || (int) $row['qty'] < 1) {
                $errors[] = "Baris {$row['line']}: qty '{$row['qty']}' harus angka bulat minimal 1";
                $invalid = true;
            }
            if ($invalid) {
                return $row;
            }
            $row['qty'] = (int) $row['qty'];

            $match = $masters->get($key($row['ws'], $row['color'], $row['size']));
            if (!$match) {
                $errors[] = "Baris {$row['line']}: id_so_det untuk WS '{$row['ws']}' / Color '{$row['color']}' / Size '{$row['size']}' tidak ditemukan";
                return $row;
            }

            // Bedakan berdasarkan dest bila diisi ('-' / kosong dianggap sama)
            $normDest = fn ($d) => in_array(strtolower(trim((string) $d)), ['', '-'], true) ? '' : strtolower(trim((string) $d));
            if ($row['dest'] !== '') {
                $match = $match->filter(fn ($m) => $normDest($m->dest) === $normDest($row['dest']))->values();
                if ($match->isEmpty()) {
                    $errors[] = "Baris {$row['line']}: dest '{$row['dest']}' tidak ditemukan untuk WS '{$row['ws']}' / Color '{$row['color']}' / Size '{$row['size']}'";
                    return $row;
                }
            }

            if ($match->pluck('id_so_det')->unique()->count() > 1) {
                $dests = $match->map(fn ($m) => $normDest($m->dest) === '' ? '-' : trim($m->dest))->unique()->implode(', ');
                $errors[] = "Baris {$row['line']}: WS '{$row['ws']}' / Color '{$row['color']}' / Size '{$row['size']}' punya lebih dari satu so_det (dest: {$dests}). Isi kolom dest.";
                return $row;
            }

            $row['so_det_id'] = $match->first()->id_so_det;

            // Duplikat di dalam file
            $dupKey = $row['so_det_id'] . '|' . strtolower($row['sub_size']);
            if (isset($seen[$dupKey])) {
                $errors[] = "Baris {$row['line']}: duplikat dengan baris {$seen[$dupKey]} ({$row['ws']} / {$row['color']} / {$row['size']} / {$row['sub_size']})";
            } else {
                $seen[$dupKey] = $row['line'];
            }

            return $row;
        });

        if (count($errors) > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Import dibatalkan, tidak ada data yang disimpan.',
                'errors'  => $errors,
            ], 422);
        }

        DB::beginTransaction();
        try {
            $inserted = 0;
            $updated  = 0;
            $skipped  = [];

            foreach ($rows as $row) {
                $existing = SubSize::where('so_det_id', $row['so_det_id'])->where('size', $row['sub_size'])->first();
                if ($existing) {
                    // Sub size sudah ada: perbarui qty-nya (termasuk data lama yang qty-nya masih NULL)
                    if ((int) $existing->qty !== (int) $row['qty'] || $existing->qty === null) {
                        $existing->update(['qty' => $row['qty']]);
                        $updated++;
                    } else {
                        $skipped[] = "Baris {$row['line']}: sub size '{$row['sub_size']}' sudah ada dengan qty yang sama untuk {$row['ws']} / {$row['color']} / {$row['size']}";
                    }
                    continue;
                }

                SubSize::create([
                    'so_det_id'           => $row['so_det_id'],
                    'size'                => $row['sub_size'],
                    'qty'                 => $row['qty'],
                    'created_by'          => Auth::id(),
                    'created_by_username' => Auth::user()->username,
                ]);
                $inserted++;
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => $inserted . ' sub size berhasil diimport' . ($updated ? ', ' . $updated . ' qty sub size diperbarui' : '') . (count($skipped) ? ', ' . count($skipped) . ' baris dilewati karena sudah ada dengan qty sama' : ''),
                'skipped' => $skipped,
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Import sub size gagal: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Import dibatalkan, tidak ada data yang disimpan. ' . $e->getMessage(),
            ], 500);
        }
    }

    public function templateSubSize()
    {
        $excel = FastExcel::create('Sub Size');
        $sheet = $excel->getSheet();

        $sheet->writeRow(['ws', 'color', 'size', 'sub_size', 'qty', 'dest'])->applyFontStyleBold();
        $sheet->writeRow(['CONTOH-WS-001', 'BLACK', 'XL', 'XL-A', 100, '']);
        $sheet->writeRow(['CONTOH-WS-001', 'BLACK', 'XL', 'XL-B', 50, '']);

        return $excel->download('Template_Import_Sub_Size.xlsx');
    }
}
