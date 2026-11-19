<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Undo Switch Transaction SO Det
 *
 * Membalik satu eksekusi switch berdasarkan switch_so_det_log_details:
 * - baris yang di-update dikembalikan ke nilai `before`
 * - baris yang di-insert dihapus (part / loading plan / master plan hanya bila sudah tidak dipakai)
 *
 * Ditolak bila data hasil switch sudah berubah / dipakai transaksi lain setelah switch
 * (baris berubah lagi, output baru di so_det tujuan, packing PO, stocker / roll baru di form baru, closing).
 */
class SwitchSoDetUndoService
{
    private const CHUNK = 1000;
    private const MAX_SAMPLE = 10;

    // Urutan hapus data hasil insert (anak dulu)
    private const DELETE_ORDER = [
        'form_cut_input_detail_output_logs',
        'form_cut_input_detail_output',
        'part_form',
        'loading_line_plan',
        'master_plan',
        'part_detail',
        'part',
        'form_cut_input',
        'marker_input_detail',
        'marker_input',
    ];

    private $db;
    private $sb;
    private $columnCache = [];

    public function __construct()
    {
        $this->db = DB::connection('mysql');
        $this->sb = config('database.connections.mysql_sb.database');
    }

    /* =====================================================================
     * PREVIEW
     * ===================================================================== */

    public function preview($logId)
    {
        $result = [
            'log' => null,
            'blocks' => [],
            'warnings' => [],
            'revert' => [],
            'delete' => [],
        ];

        $log = $this->db->table('switch_so_det_logs')->where('id', $logId)->first();
        $result['log'] = $log;

        if (!$log) {
            $result['blocks'][] = 'Log switch tidak ditemukan.';
            return $result;
        }
        if ($log->status != 'success') {
            $result['blocks'][] = 'Hanya switch berstatus success yang bisa di-undo (status: ' . $log->status . ').';
            return $result;
        }

        // Switch yang lebih baru untuk so_det yang sama harus di-undo dulu
        $newer = $this->db->table('switch_so_det_logs')
            ->where('id', '>', $log->id)
            ->where('status', 'success')
            ->where(function ($q) use ($log) {
                $q->whereIn('so_det_id_asal', [$log->so_det_id_asal, $log->so_det_id_tujuan])
                    ->orWhereIn('so_det_id_tujuan', [$log->so_det_id_asal, $log->so_det_id_tujuan]);
            })
            ->pluck('id');
        if ($newer->count() > 0) {
            $result['blocks'][] = 'Ada switch yang lebih baru untuk so_det yang sama (log #' . $newer->implode(', #') . '), undo itu dulu.';
        }

        $stats = $this->db->table('switch_so_det_log_details')
            ->selectRaw('table_name, action, COUNT(*) total')
            ->where('switch_so_det_log_id', $log->id)
            ->groupBy('table_name', 'action')
            ->get();

        foreach ($stats as $stat) {
            $result[$stat->action == 'update' ? 'revert' : 'delete'][$stat->table_name] = (int) $stat->total;
        }

        // 1. Baris yang di-update tidak boleh berubah lagi setelah switch
        foreach ($stats->where('action', 'update') as $stat) {
            $this->checkUpdatedRows($log->id, $stat->table_name, $result);
        }

        // 2. Form / marker baru tidak boleh dipakai transaksi lain
        $this->checkNewForms($log->id, $result);

        // 3. Output / loading baru setelah switch untuk stocker & numbering yang dipindah
        $this->checkNewOutputs($log, $result);
        $this->checkNewLoadings($log->id, $result);

        return $result;
    }

    /* =====================================================================
     * EXECUTE
     * ===================================================================== */

    public function execute($logId, $notes = null)
    {
        $preview = $this->preview($logId);
        if (count($preview['blocks']) > 0) {
            return [
                'status' => 400,
                'message' => implode('<br>', $preview['blocks']),
            ];
        }

        $log = $preview['log'];
        $summary = ['reverted' => [], 'deleted' => [], 'kept' => []];

        $this->db->beginTransaction();
        try {
            // Kunci header agar tidak di-undo dua kali bersamaan
            $locked = $this->db->table('switch_so_det_logs')->where('id', $log->id)->lockForUpdate()->first();
            if (!$locked || $locked->status != 'success') {
                throw new \Exception('Status log sudah berubah, muat ulang halaman.');
            }

            // 1. Kembalikan semua baris yang di-update
            foreach (array_keys($preview['revert']) as $table) {
                $summary['reverted'][$table] = $this->revertTable($log->id, $table);
            }

            // 2. Hapus baris hasil insert
            $inserted = $this->insertedIdsByTable($log->id);
            foreach (self::DELETE_ORDER as $baseTable) {
                foreach ($inserted as $table => $ids) {
                    if ($this->baseTable($table) == $baseTable && count($ids) > 0) {
                        $this->deleteInserted($table, $ids, $summary);
                    }
                }
            }

            $this->db->table('switch_so_det_logs')->where('id', $log->id)->update([
                'status' => 'undone',
                'undone_at' => Carbon::now(),
                'undone_by' => Auth::id(),
                'undone_by_username' => Auth::user() ? Auth::user()->username : null,
                'undo_notes' => $notes,
                'undo_summary' => json_encode($summary),
                'updated_at' => Carbon::now(),
            ]);

            $this->db->commit();

            return [
                'status' => 200,
                'message' => 'Undo switch so_det ' . $log->so_det_id_tujuan . ' -> ' . $log->so_det_id_asal . ' berhasil.',
                'summary' => $summary,
            ];
        } catch (\Throwable $th) {
            $this->db->rollBack();

            return [
                'status' => 500,
                'message' => 'Terjadi kesalahan: ' . $th->getMessage(),
            ];
        }
    }

    /* =====================================================================
     * CONFLICT CHECKS
     * ===================================================================== */

    private function checkUpdatedRows($logId, $table, &$result)
    {
        $mismatch = 0;
        $missing = 0;
        $samples = [];
        $closedAt = null;
        $hasCreatedAt = $this->hasColumn($table, 'created_at') && $this->isOutputTable($table);

        $this->detailQuery($logId, $table, 'update')
            ->select('id', 'row_id', 'after')
            ->chunkById(self::CHUNK, function ($details) use ($table, $hasCreatedAt, &$mismatch, &$missing, &$samples, &$closedAt) {
                $current = $this->db->table($table)->whereIn('id', $details->pluck('row_id')->all())->get()->keyBy('id');

                foreach ($details as $detail) {
                    $row = $current->get($detail->row_id);
                    if (!$row) {
                        $missing++;
                        continue;
                    }

                    foreach (json_decode($detail->after, true) as $column => $value) {
                        if (!$this->sameValue($row->{$column} ?? null, $value)) {
                            $mismatch++;
                            if (count($samples) < self::MAX_SAMPLE) {
                                $samples[] = $detail->row_id . '.' . $column . ' = ' . var_export($row->{$column} ?? null, true) . ' (harusnya ' . var_export($value, true) . ')';
                            }
                            break;
                        }
                    }

                    if ($hasCreatedAt && $row->created_at && checkClosingDate($row->created_at)) {
                        $closedAt = $closedAt ? min($closedAt, $row->created_at) : $row->created_at;
                    }
                }
            });

        if ($missing > 0) {
            $result['blocks'][] = $table . ': ' . number_format($missing) . ' baris hasil switch sudah dihapus.';
        }
        if ($mismatch > 0) {
            $result['blocks'][] = $table . ': ' . number_format($mismatch) . ' baris sudah berubah setelah switch. Contoh: ' . implode('; ', $samples);
        }
        if ($closedAt) {
            $result['blocks'][] = $table . ': ada data di periode yang sudah ditutup (' . $closedAt . ').';
        }
    }

    private function checkNewForms($logId, &$result)
    {
        $newFormIds = $this->insertedIds($logId, 'form_cut_input');
        $newMarkerIds = $this->insertedIds($logId, 'marker_input');

        if (count($newFormIds) > 0) {
            $forms = $this->db->table('form_cut_input')->whereIn('id', $newFormIds)->get(['id', 'no_form', 'waktu_selesai']);

            $closed = $forms->filter(function ($form) {
                return checkClosingDate($form->waktu_selesai);
            });
            if ($closed->count() > 0) {
                $result['blocks'][] = 'Form berada di periode yang sudah ditutup: ' . $closed->pluck('no_form')->implode(', ') . '.';
            }

            // [tabel, kolom form, tabel log yang dianggap milik switch]
            $references = [
                ['stocker_input', 'form_cut_id', 'stocker_input', 'update'],
                ['year_sequence', 'form_cut_id', 'year_sequence', 'update'],
                ['stocker_numbering', 'form_cut_id', 'stocker_numbering', 'update'],
                ['stocker_separate', 'form_cut_id', 'stocker_separate', 'update'],
                ['modify_size_qty', 'form_cut_id', 'modify_size_qty', 'update'],
                ['part_form', 'form_id', 'part_form', 'insert'],
                ['form_cut_input_detail_output', 'form_cut_input_id', 'form_cut_input_detail_output', 'insert'],
                ['form_cut_input_detail_output_logs', 'form_cut_input_id_tujuan', 'form_cut_input_detail_output_logs', 'insert'],
                ['form_cut_input_detail_output_logs', 'form_cut_input_id_asal', 'form_cut_input_detail_output_logs', 'insert'],
                ['form_cut_input_detail', 'form_cut_id', null, null],
                ['stocker_ws_additional', 'form_cut_id', null, null],
            ];

            foreach ($references as [$table, $column, $logTable, $logAction]) {
                if (!$this->hasColumn($table, $column)) {
                    continue;
                }

                $ids = $this->db->table($table)->whereIn($column, $newFormIds)->pluck('id')->all();
                $owned = $logTable ? array_flip($this->loggedIds($logId, $logTable, $logAction)) : [];
                $foreign = array_filter($ids, function ($id) use ($owned) {
                    return !isset($owned[$id]);
                });

                if (count($foreign) > 0) {
                    $result['blocks'][] = 'Form baru sudah dipakai transaksi lain: ' . number_format(count($foreign)) . ' baris di ' . $table . '.';
                }
            }
        }

        if (count($newMarkerIds) > 0) {
            // Cocokkan lewat kode marker (id_marker); marker_id hanya bila id_marker kosong
            $newMarkerKodes = $this->db->table('marker_input')->whereIn('id', $newMarkerIds)->pluck('kode')->all();
            $otherForms = $this->db->table('form_cut_input')
                ->where(function ($q) use ($newMarkerIds, $newMarkerKodes) {
                    $q->whereIn('id_marker', $newMarkerKodes ?: [''])
                        ->orWhere(function ($q2) use ($newMarkerIds) {
                            $q2->whereIn('marker_id', $newMarkerIds)->where(function ($q3) {
                                $q3->whereNull('id_marker')->orWhere('id_marker', '');
                            });
                        });
                })
                ->whereNotIn('id', $newFormIds ?: [0])
                ->pluck('no_form');
            if ($otherForms->count() > 0) {
                $result['blocks'][] = 'Marker baru sudah dipakai form lain: ' . $otherForms->implode(', ') . '.';
            }
        }
    }

    /**
     * Output dengan kode_numbering hasil switch yang dibuat SETELAH switch (tidak ada di log),
     * kalau di-undo akan tertinggal di so_det tujuan.
     */
    private function checkNewOutputs($log, &$result)
    {
        $yearSequenceIds = $this->loggedIds($log->id, 'year_sequence', 'update');
        if (count($yearSequenceIds) < 1) {
            return;
        }

        $tables = array_merge(
            SwitchSoDetService::SB_OUTPUT_PLAN_TABLES,
            SwitchSoDetService::SB_OUTPUT_PLAIN_TABLES,
            array_keys(SwitchSoDetService::SB_BLOCKING_TABLES)
        );

        $owned = [];
        foreach ($tables as $table) {
            $owned[$table] = array_flip($this->loggedIds($log->id, $this->sb . '.' . $table, 'update'));
        }

        $foreign = [];
        foreach (array_chunk($yearSequenceIds, self::CHUNK) as $ids) {
            $kodes = $this->db->table('year_sequence')->whereIn('id', $ids)->pluck('id_year_sequence')->filter()->values()->all();
            if (count($kodes) < 1) {
                continue;
            }

            foreach ($tables as $table) {
                $fullTable = $this->sb . '.' . $table;
                if (!$this->hasColumn($fullTable, 'kode_numbering') || !$this->hasColumn($fullTable, 'so_det_id')) {
                    continue;
                }

                $rowIds = $this->db->table($fullTable)
                    ->where('so_det_id', $log->so_det_id_tujuan)
                    ->whereIn('kode_numbering', $kodes)
                    ->pluck('id');

                foreach ($rowIds as $rowId) {
                    if (!isset($owned[$table][$rowId])) {
                        $foreign[$table] = ($foreign[$table] ?? 0) + 1;
                    }
                }
            }
        }

        foreach ($foreign as $table => $count) {
            $result['blocks'][] = 'Ada ' . number_format($count) . ' output baru di ' . $table . ' setelah switch (kode numbering hasil switch).';
        }
    }

    private function checkNewLoadings($logId, &$result)
    {
        $stockerIds = $this->loggedIds($logId, 'stocker_input', 'update');
        if (count($stockerIds) < 1) {
            return;
        }

        $owned = array_flip($this->loggedIds($logId, 'loading_line', 'update'));
        $foreign = 0;
        foreach (array_chunk($stockerIds, self::CHUNK) as $ids) {
            foreach ($this->db->table('loading_line')->whereIn('stocker_id', $ids)->pluck('id') as $loadingId) {
                if (!isset($owned[$loadingId])) {
                    $foreign++;
                }
            }
        }

        if ($foreign > 0) {
            $result['blocks'][] = 'Ada ' . number_format($foreign) . ' loading baru untuk stocker hasil switch.';
        }
    }

    /* =====================================================================
     * REVERT & DELETE
     * ===================================================================== */

    private function revertTable($logId, $table)
    {
        $count = 0;

        $this->detailQuery($logId, $table, 'update')
            ->select('id', 'row_id', 'before')
            ->chunkById(self::CHUNK, function ($details) use ($table, &$count) {
                // Kelompokkan per nilai before agar update massal
                foreach ($details->groupBy('before') as $before => $group) {
                    $values = json_decode($before, true);
                    if (!$values) {
                        continue;
                    }

                    $this->db->table($table)->whereIn('id', $group->pluck('row_id')->all())->update($values);
                    $count += $group->count();
                }
            });

        return $count;
    }

    private function deleteInserted($table, array $ids, &$summary)
    {
        $baseTable = $this->baseTable($table);
        $deleteIds = $ids;

        switch ($baseTable) {
            case 'loading_line_plan':
                $used = $this->db->table('loading_line')->whereIn('loading_plan_id', $ids)->distinct()->pluck('loading_plan_id')->all();
                $deleteIds = array_values(array_diff($ids, $used));
                break;

            case 'master_plan':
                $used = [];
                foreach ($this->masterPlanReferenceTables() as $refTable) {
                    $used = array_merge($used, $this->db->table($refTable)->whereIn('master_plan_id', $ids)->distinct()->pluck('master_plan_id')->all());
                }
                $deleteIds = array_values(array_diff($ids, $used));
                break;

            case 'part_detail':
                // Pertahankan part detail yang masih dipakai stocker / part detail lain (complement)
                $keep = $this->db->table('stocker_input')->whereIn('part_detail_id', $ids)->distinct()->pluck('part_detail_id')->all();
                for ($i = 0; $i < 3; $i++) {
                    $candidates = array_diff($ids, $keep);
                    $referencedByKept = $this->db->table('part_detail')
                        ->whereIn('from_part_detail', $ids)
                        ->where(function ($q) use ($candidates) {
                            $q->whereNotIn('id', $candidates ?: [0]);
                        })
                        ->pluck('from_part_detail')->all();
                    $newKeep = array_unique(array_merge($keep, $referencedByKept));
                    if (count($newKeep) == count($keep)) {
                        break;
                    }
                    $keep = $newKeep;
                }
                $deleteIds = array_values(array_diff($ids, $keep));

                if (count($deleteIds) > 0) {
                    $this->db->table('part_detail_secondary')->whereIn('part_detail_id', $deleteIds)->delete();
                    $this->db->table('part_detail_item')->whereIn('part_detail_id', $deleteIds)->delete();
                }
                break;

            case 'part':
                $usedByDetail = $this->db->table('part_detail')->whereIn('part_id', $ids)->distinct()->pluck('part_id')->all();
                $usedByForm = $this->db->table('part_form')->whereIn('part_id', $ids)->distinct()->pluck('part_id')->all();
                $deleteIds = array_values(array_diff($ids, $usedByDetail, $usedByForm));
                break;
        }

        foreach (array_chunk($deleteIds, self::CHUNK) as $chunk) {
            $this->db->table($table)->whereIn('id', $chunk)->delete();
        }

        $summary['deleted'][$table] = count($deleteIds);
        if (count($deleteIds) < count($ids)) {
            $summary['kept'][$table] = array_values(array_diff($ids, $deleteIds));
        }
    }

    private function masterPlanReferenceTables()
    {
        $tables = array_merge(
            SwitchSoDetService::SB_OUTPUT_PLAN_TABLES,
            array_keys(SwitchSoDetService::SB_BLOCKING_TABLES),
            [
                'output_rfts_summary', 'output_defects_summary', 'output_rejects_summary',
                'output_rfts_packing_summary', 'output_defects_packing_summary', 'output_rejects_packing_summary',
            ]
        );

        return collect($tables)
            ->map(function ($table) {
                return $this->sb . '.' . $table;
            })
            ->filter(function ($table) {
                return $this->hasColumn($table, 'master_plan_id');
            })
            ->values()
            ->all();
    }

    /* =====================================================================
     * HELPERS
     * ===================================================================== */

    private function detailQuery($logId, $table, $action)
    {
        return $this->db->table('switch_so_det_log_details')
            ->where('switch_so_det_log_id', $logId)
            ->where('table_name', $table)
            ->where('action', $action);
    }

    private function loggedIds($logId, $table, $action)
    {
        return $this->detailQuery($logId, $table, $action)->pluck('row_id')->map(function ($id) {
            return (int) $id;
        })->all();
    }

    private function insertedIds($logId, $table)
    {
        return $this->loggedIds($logId, $table, 'insert');
    }

    private function insertedIdsByTable($logId)
    {
        return $this->db->table('switch_so_det_log_details')
            ->where('switch_so_det_log_id', $logId)
            ->where('action', 'insert')
            ->get(['table_name', 'row_id'])
            ->groupBy('table_name')
            ->map(function ($rows) {
                return $rows->pluck('row_id')->map(function ($id) {
                    return (int) $id;
                })->unique()->values()->all();
            })
            ->all();
    }

    private function baseTable($table)
    {
        $parts = explode('.', $table);

        return end($parts);
    }

    private function isOutputTable($table)
    {
        return strpos($this->baseTable($table), 'output_') === 0;
    }

    private function sameValue($current, $expected)
    {
        if ($current === null || $expected === null) {
            return $current === $expected;
        }

        if (is_numeric($current) && is_numeric($expected)) {
            return (float) $current == (float) $expected;
        }

        return trim((string) $current) === trim((string) $expected);
    }

    /**
     * $table boleh "nama_tabel" (NDS) atau "schema.nama_tabel".
     */
    private function hasColumn($table, $column)
    {
        if (strpos($table, '.') !== false) {
            [$schema, $name] = explode('.', $table, 2);
        } else {
            $schema = config('database.connections.mysql.database');
            $name = $table;
        }

        $key = $schema . '.' . $name;
        if (!isset($this->columnCache[$key])) {
            $this->columnCache[$key] = collect($this->db->select(
                'SELECT column_name AS name FROM information_schema.columns WHERE table_schema = ? AND table_name = ?',
                [$schema, $name]
            ))->pluck('name')->all();
        }

        return in_array($column, $this->columnCache[$key]);
    }
}
