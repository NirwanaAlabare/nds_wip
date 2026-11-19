<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Switch Transaction SO Det
 *
 * Memindahkan seluruh transaksi so_det asal ke so_det tujuan (beda WS) mulai dari
 * marker -> cutting -> part -> stocker -> year sequence -> dc loading -> output sewing/packing.
 *
 * Prinsip:
 * - Form & marker lama TIDAK diubah rasionya. Output cutting dipindah lewat form_cut_input_detail_output
 *   + form_cut_input_detail_output_logs (pola yang sama dengan Cutting Switching), supaya laporan
 *   proses output (rasio x gelaran - switching out + switching in) tetap seimbang.
 * - Form baru dibuat tanpa pemakaian (tanpa form_cut_input_detail / roll).
 * - Semua update berjalan dalam SATU transaksi di koneksi `mysql`; tabel signalbit diakses dengan
 *   nama database lengkap (host keduanya harus sama).
 * - Ditolak bila sudah ada output packing PO / packing NDS.
 */
class SwitchSoDetService
{
    // Output signalbit yang memiliki master_plan_id (so_det_id + master_plan_id ikut dipindah)
    public const SB_OUTPUT_PLAN_TABLES = [
        'output_rfts',
        'output_defects',
        'output_rejects',
        'output_reject_in',
        'output_check_finishing',
        'output_rfts_finish',
        'output_defects_finish',
        'output_rejects_finish',
        'output_rfts_packing',
        'output_defects_packing',
        'output_rejects_packing',
        'output_undo',
        'output_undo_packing',
        'output_undo_packing_po',
    ];

    // Output signalbit tanpa master_plan_id (hanya so_det_id)
    public const SB_OUTPUT_PLAIN_TABLES = [
        'output_gudang_stok',
    ];

    // Bila ada data di tabel ini untuk so_det asal, switch ditolak
    public const SB_BLOCKING_TABLES = [
        'output_rfts_packing_po' => 'so_det_id',
        'output_rejects_packing_po' => 'so_det_id',
    ];

    public const NDS_BLOCKING_TABLES = [
        'packing_packing_in' => 'id_so_det',
        'packing_trf_garment' => 'id_so_det',
        'fg_stok_bpb' => 'id_so_det',
        'stocker_ws_additional_detail' => 'so_det_id',
    ];

    private const CHUNK = 1000;

    private $db;
    private $sb;

    private $columnCache = [];

    // Cache selama eksekusi
    private $partMap = [];
    private $partDetailMap = [];
    private $loadingPlanMap = [];
    private $masterPlanMap = [];

    // Buffer log detail
    private $logDetails = [];
    private $logId = null;

    public function __construct()
    {
        $this->db = DB::connection('mysql');
        $this->sb = config('database.connections.mysql_sb.database');
    }

    /* =====================================================================
     * PUBLIC API
     * ===================================================================== */

    /**
     * Cek kelayakan + ringkasan data yang akan dipindah (tanpa mengubah data).
     */
    public function preview($asalId, $tujuanId)
    {
        $result = [
            'asal' => null,
            'tujuan' => null,
            'blocks' => [],
            'warnings' => [],
            'forms' => [],
            'counts' => [],
            'master_plans' => [],
            'parts' => [],
        ];

        if (!$this->sameHost()) {
            $result['blocks'][] = 'Koneksi mysql dan mysql_sb berada di host berbeda, transaksi tidak bisa dijalankan atomik.';
        }

        $asal = $this->getSoDet($asalId);
        $tujuan = $this->getSoDet($tujuanId);
        $result['asal'] = $asal;
        $result['tujuan'] = $tujuan;

        if (!$asal) {
            $result['blocks'][] = 'SO Det asal tidak ditemukan di master_sb_ws.';
        }
        if (!$tujuan) {
            $result['blocks'][] = 'SO Det tujuan tidak ditemukan di master_sb_ws.';
        }
        if (!$asal || !$tujuan) {
            return $result;
        }

        if ($asal->id_so_det == $tujuan->id_so_det) {
            $result['blocks'][] = 'SO Det asal dan tujuan sama.';
            return $result;
        }

        if ($asal->id_act_cost == $tujuan->id_act_cost && trim($asal->color) == trim($tujuan->color)) {
            $result['warnings'][] = 'WS & color asal dan tujuan sama. Modul ini dirancang untuk beda WS, marker/part/plan yang ada akan dipakai ulang bila ditemukan.';
        }
        if (trim($asal->size) != trim($tujuan->size)) {
            $result['warnings'][] = 'Size asal (' . $asal->size . ') berbeda dengan size tujuan (' . $tujuan->size . '). Label stocker perlu dicetak ulang.';
        }

        // --- Blocking tables
        foreach (self::SB_BLOCKING_TABLES as $table => $column) {
            if (!$this->tableExists($this->sb, $table)) {
                continue;
            }
            $count = $this->db->table($this->sbTable($table))->where($column, $asalId)->count();
            if ($count > 0) {
                $result['blocks'][] = 'Sudah ada ' . number_format($count) . ' data di ' . $table . ' (packing PO).';
            }
        }
        foreach (self::NDS_BLOCKING_TABLES as $table => $column) {
            if (!$this->hasColumn(null, $table, $column)) {
                continue;
            }
            $count = $this->db->table($table)->where($column, $asalId)->count();
            if ($count > 0) {
                $result['blocks'][] = 'Sudah ada ' . number_format($count) . ' data di ' . $table . '.';
            }
        }

        // --- Stocker di luar form cut normal
        $otherStocker = $this->db->table('stocker_input')
            ->where('so_det_id', $asalId)
            ->where(function ($q) {
                $q->whereNotNull('form_piece_id')->orWhereNotNull('form_reject_id')->orWhereNull('form_cut_id');
            })
            ->count();
        if ($otherStocker > 0) {
            $result['blocks'][] = 'Ada ' . number_format($otherStocker) . ' stocker so_det asal dari form piece / form reject / tanpa form. Belum didukung modul ini.';
        }

        // --- Forms
        $allForms = $this->getForms($asalId);

        // Form yang belum pernah dimulai (tanpa waktu mulai) tidak punya output -> tidak dipindah
        $notStarted = $allForms->filter(function ($form) {
            return $form->status != 'SELESAI PENGERJAAN' && !$form->waktu_mulai;
        });
        if ($notStarted->count() > 0) {
            $result['warnings'][] = 'Form belum dimulai (tidak dipindah, sebaiknya dibatalkan / marker diganti agar tidak menghasilkan output so_det asal): ' . $notStarted->pluck('no_form')->implode(', ') . '.';
        }

        // Form yang sedang dikerjakan -> tolak
        $inProgress = $allForms->filter(function ($form) {
            return $form->status != 'SELESAI PENGERJAAN' && $form->waktu_mulai;
        });
        if ($inProgress->count() > 0) {
            $result['blocks'][] = 'Form sedang dikerjakan, selesaikan dulu: ' . $inProgress->pluck('no_form')->implode(', ') . '.';
        }

        $forms = $allForms->where('status', 'SELESAI PENGERJAAN')->values();
        $result['forms'] = $forms;

        $closedForms = $forms->filter(function ($form) {
            return checkClosingDate($form->waktu_selesai);
        });
        if ($closedForms->count() > 0) {
            $result['blocks'][] = 'Form berada di periode yang sudah ditutup: ' . $closedForms->pluck('no_form')->implode(', ') . '.';
        }

        $strayStocker = $this->db->table('stocker_input')
            ->where('so_det_id', $asalId)
            ->whereNotNull('form_cut_id')
            ->whereNotIn('form_cut_id', $forms->pluck('id')->all() ?: [0])
            ->whereNull('form_piece_id')
            ->whereNull('form_reject_id')
            ->count();
        if ($strayStocker > 0) {
            $result['blocks'][] = 'Ada ' . number_format($strayStocker) . ' stocker so_det asal yang form-nya tidak ditemukan / batal / belum selesai.';
        }

        $formIds = $forms->pluck('id')->all();

        // --- Counts NDS
        $stockerIds = $this->db->table('stocker_input')->where('so_det_id', $asalId)->whereIn('form_cut_id', $formIds ?: [0])->pluck('id');
        $result['counts']['stocker_input'] = $stockerIds->count();
        $result['counts']['year_sequence'] = $this->db->table('year_sequence')->where('so_det_id', $asalId)->count();
        $result['counts']['stocker_numbering'] = $this->db->table('stocker_numbering')->where('so_det_id', $asalId)->count();
        $result['counts']['stocker_separate'] = $this->db->table('stocker_separate')->where('so_det_id', $asalId)->whereIn('form_cut_id', $formIds ?: [0])->count();
        $result['counts']['modify_size_qty'] = $this->db->table('modify_size_qty')->where('so_det_id', $asalId)->whereIn('form_cut_id', $formIds ?: [0])->count();
        $result['counts']['form_cut_input_detail_output'] = $this->db->table('form_cut_input_detail_output')
            ->whereIn('form_cut_input_id', $formIds ?: [0])
            ->whereIn('marker_input_detail_id', $forms->pluck('marker_detail_ids')->flatten()->all() ?: [0])
            ->count();
        $result['counts']['loading_line'] = $stockerIds->count() > 0
            ? $stockerIds->chunk(self::CHUNK)->sum(function ($ids) {
                return $this->db->table('loading_line')->whereIn('stocker_id', $ids->values()->all())->count();
            })
            : 0;

        // --- Counts output signalbit + closing check
        $planIds = collect();
        foreach (array_merge(self::SB_OUTPUT_PLAN_TABLES, self::SB_OUTPUT_PLAIN_TABLES) as $table) {
            if (!$this->hasColumn($this->sb, $table, 'so_det_id')) {
                continue;
            }

            $query = $this->db->table($this->sbTable($table))->where('so_det_id', $asalId);
            $stat = (clone $query)->selectRaw('COUNT(*) total, MIN(created_at) first_at')->first();
            $result['counts'][$table] = (int) $stat->total;

            if ($stat->total > 0 && checkClosingDate($stat->first_at)) {
                $result['blocks'][] = $table . ' memiliki data di periode yang sudah ditutup (' . $stat->first_at . ').';
            }

            if ($stat->total > 0 && $this->hasColumn($this->sb, $table, 'master_plan_id')) {
                $planIds = $planIds->merge((clone $query)->whereNotNull('master_plan_id')->distinct()->pluck('master_plan_id'));
            }
        }

        // --- Master plan mapping preview
        $planIds = $planIds->unique()->values();
        if ($planIds->count() > 0) {
            $oldPlans = $this->db->table($this->sbTable('master_plan'))->whereIn('id', $planIds->all())->get()->keyBy('id');
            foreach ($planIds as $planId) {
                $oldPlan = $oldPlans->get($planId);
                if (!$oldPlan) {
                    $result['warnings'][] = 'Master plan ' . $planId . ' tidak ditemukan, master_plan_id output terkait tidak diubah.';
                    continue;
                }
                $existing = $this->findMasterPlan($tujuan, $oldPlan);
                $result['master_plans'][] = [
                    'old_id' => $oldPlan->id,
                    'sewing_line' => $oldPlan->sewing_line,
                    'tgl_plan' => $oldPlan->tgl_plan,
                    'new_id' => $existing ? $existing->id : null,
                    'action' => $existing ? 'pakai yang ada' : 'buat baru (jam kerja/target 0)',
                ];
            }
        }

        // --- Part preview
        $oldPartIds = $this->db->table('part_form')->whereIn('form_id', $formIds ?: [0])->distinct()->pluck('part_id');
        foreach ($this->db->table('part')->whereIn('id', $oldPartIds->all() ?: [0])->get() as $oldPart) {
            $existing = $this->findPart($tujuan, $oldPart);
            $result['parts'][] = [
                'old_kode' => $oldPart->kode,
                'panel' => $oldPart->panel,
                'new_kode' => $existing ? $existing->kode : null,
                'action' => $existing ? 'pakai yang ada' : 'buat baru (copy part detail)',
            ];
        }

        if ($forms->count() < 1 && collect($result['counts'])->sum() < 1) {
            $result['blocks'][] = 'Tidak ada transaksi untuk so_det asal.';
        }

        return $result;
    }

    /**
     * Jalankan switch. Return ['status' => 200|400|500, 'message' => ..., 'log_id' => ...]
     */
    public function execute($asalId, $tujuanId, $notes = null)
    {
        $preview = $this->preview($asalId, $tujuanId);
        if (count($preview['blocks']) > 0) {
            return [
                'status' => 400,
                'message' => implode('<br>', $preview['blocks']),
            ];
        }

        $asal = $preview['asal'];
        $tujuan = $preview['tujuan'];
        $summary = [];

        $this->db->beginTransaction();
        try {
            $this->logId = $this->db->table('switch_so_det_logs')->insertGetId([
                'so_det_id_asal' => $asal->id_so_det,
                'so_det_id_tujuan' => $tujuan->id_so_det,
                'ws_asal' => $asal->ws,
                'ws_tujuan' => $tujuan->ws,
                'style_asal' => $asal->styleno,
                'style_tujuan' => $tujuan->styleno,
                'color_asal' => $asal->color,
                'color_tujuan' => $tujuan->color,
                'size_asal' => $asal->size,
                'size_tujuan' => $tujuan->size,
                'status' => 'success',
                'notes' => $notes,
                'created_by' => Auth::id(),
                'created_by_username' => Auth::user() ? Auth::user()->username : null,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);

            // 1-4. Marker, form, output cutting, part & part form, stocker dkk per form
            $formMap = $this->switchCutting($asal, $tujuan, $preview['forms'], $summary);

            // 5. Year sequence & stocker numbering
            $this->switchNumbering($asal, $tujuan, $formMap, $summary);

            // 6. Output sewing / packing (+ master plan)
            $this->switchOutputs($asal, $tujuan, $summary);

            $this->db->table('switch_so_det_logs')->where('id', $this->logId)->update([
                'summary' => json_encode($summary),
                'updated_at' => Carbon::now(),
            ]);
            $this->flushLog();

            $this->db->commit();

            return [
                'status' => 200,
                'message' => 'Switch so_det ' . $asal->id_so_det . ' -> ' . $tujuan->id_so_det . ' berhasil.',
                'log_id' => $this->logId,
                'summary' => $summary,
            ];
        } catch (\Throwable $th) {
            $this->db->rollBack();

            // Catat kegagalan di luar transaksi
            try {
                $this->db->table('switch_so_det_logs')->insert([
                    'so_det_id_asal' => $asal->id_so_det,
                    'so_det_id_tujuan' => $tujuan->id_so_det,
                    'ws_asal' => $asal->ws,
                    'ws_tujuan' => $tujuan->ws,
                    'size_asal' => $asal->size,
                    'size_tujuan' => $tujuan->size,
                    'status' => 'failed',
                    'notes' => $notes,
                    'error' => $th->getMessage() . ' @ ' . basename($th->getFile()) . ':' . $th->getLine(),
                    'created_by' => Auth::id(),
                    'created_by_username' => Auth::user() ? Auth::user()->username : null,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ]);
            } catch (\Throwable $logError) {
                \Log::error('SwitchSoDet log failed: ' . $logError->getMessage());
            }

            return [
                'status' => 500,
                'message' => 'Terjadi kesalahan: ' . $th->getMessage(),
            ];
        }
    }

    /* =====================================================================
     * STEP: CUTTING (marker, form, output, part, stocker, loading)
     * ===================================================================== */

    private function switchCutting($asal, $tujuan, $forms, &$summary)
    {
        $formMap = [];
        $markerMap = [];
        $markerDetailMap = [];
        $noCutCounter = [];

        $now = Carbon::now();
        $user = Auth::user();

        $nextMarkerNumber = $this->nextMarkerNumber();

        foreach ($forms->sortBy('waktu_selesai') as $form) {
            $oldMarker = $this->db->table('marker_input')->where('id', $form->marker_id)->first();

            // ---------- 1. Marker baru (sekali per marker lama)
            if (!isset($markerMap[$oldMarker->id])) {
                $markerCode = 'MRK/' . date('ym') . '/' . sprintf('%05s', $nextMarkerNumber++);
                $gelarQty = $forms->where('marker_id', $oldMarker->id)->sum(function ($f) {
                    return intval($f->total_lembar ?: $f->qty_ply);
                });

                $newMarker = (array) $oldMarker;
                unset($newMarker['id']);
                $newMarker = array_merge($newMarker, [
                    'kode' => $markerCode,
                    'act_costing_id' => $tujuan->id_act_cost,
                    'act_costing_ws' => $tujuan->ws,
                    'buyer' => $tujuan->buyer,
                    'style' => $tujuan->styleno,
                    'color' => $tujuan->color,
                    'gelar_qty' => $gelarQty,
                    'gelar_qty_balance' => 0,
                    'cancel' => 'N',
                    'notes' => trim('SWITCH SO DET dari ' . $oldMarker->kode . ' (' . $oldMarker->act_costing_ws . '). ' . ($oldMarker->notes ?? '')),
                    'created_by' => $user->id,
                    'created_by_username' => $user->username,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
                $newMarker = $this->onlyColumns('marker_input', $newMarker);
                $newMarkerId = $this->db->table('marker_input')->insertGetId($newMarker);
                $this->log('marker_input', $newMarkerId, 'insert', null, ['kode' => $markerCode, 'from_marker_id' => $oldMarker->id]);

                $markerMap[$oldMarker->id] = (object) ['id' => $newMarkerId, 'kode' => $markerCode];

                $oldDetails = $this->db->table('marker_input_detail')->where('marker_id', $oldMarker->id)->where('so_det_id', $asal->id_so_det)->get();
                if ($oldDetails->count() < 1) {
                    // Form masuk karena punya stocker so_det asal, tapi marker-nya tidak memuat so_det asal
                    $oldDetails = collect([(object) ['id' => 'none-' . $oldMarker->id, 'ratio' => 0, 'cut_qty' => 0]]);
                }
                foreach ($oldDetails as $oldDetail) {
                    $newDetailId = $this->db->table('marker_input_detail')->insertGetId([
                        'marker_id' => $newMarkerId,
                        'so_det_id' => $tujuan->id_so_det,
                        'size' => $tujuan->size,
                        'ratio' => $oldDetail->ratio,
                        'cut_qty' => $oldDetail->cut_qty,
                        'cancel' => 'N',
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);
                    $this->log('marker_input_detail', $newDetailId, 'insert', null, ['from_marker_detail_id' => $oldDetail->id]);
                    $markerDetailMap[$oldDetail->id] = (object) ['id' => $newDetailId, 'size' => $tujuan->size, 'ratio' => $oldDetail->ratio];
                }
            }
            $newMarker = $markerMap[$oldMarker->id];

            // ---------- 2. Form baru tanpa pemakaian
            $oldForm = $this->db->table('form_cut_input')->where('id', $form->id)->first();

            $noCutKey = $tujuan->ws . '|' . $tujuan->color . '|' . $oldMarker->panel;
            if (!isset($noCutCounter[$noCutKey])) {
                $noCutCounter[$noCutKey] = intval($this->db->table('form_cut_input')
                    ->join('marker_input', 'marker_input.id', '=', 'form_cut_input.marker_id')
                    ->where('marker_input.act_costing_ws', $tujuan->ws)
                    ->where('marker_input.color', $tujuan->color)
                    ->where('marker_input.panel', $oldMarker->panel)
                    ->where('form_cut_input.status', 'SELESAI PENGERJAAN')
                    ->max(DB::raw('CAST(form_cut_input.no_cut AS UNSIGNED)')));
            }
            $noCutCounter[$noCutKey]++;

            $noForm = $this->nextNoForm();
            $newForm = $this->onlyColumns('form_cut_input', [
                'marker_id' => $newMarker->id,
                'id_marker' => $newMarker->kode,
                'no_form' => $noForm,
                'tgl_form_cut' => $oldForm->tgl_form_cut,
                'no_meja' => $oldForm->no_meja,
                'meja' => $oldForm->meja ?? null,
                'status' => 'SELESAI PENGERJAAN',
                'user' => $user->username,
                'cancel' => 'N',
                'tgl_input' => $now,
                'shell' => $oldForm->shell,
                'group_stocker' => $oldForm->group_stocker,
                'waktu_mulai' => $oldForm->waktu_mulai,
                'waktu_selesai' => $oldForm->waktu_selesai,
                'qty_ply' => $oldForm->qty_ply,
                'total_lembar' => $oldForm->total_lembar,
                'tipe_form_cut' => $oldForm->tipe_form_cut,
                'generated' => $oldForm->generated ?? null,
                'operator' => $oldForm->operator ?? null,
                'no_cut' => $noCutCounter[$noCutKey],
                'notes' => 'SWITCH SO DET dari form ' . $oldForm->no_form . ' (' . $oldMarker->act_costing_ws . ')',
                'created_by' => $user->id,
                'created_by_username' => $user->username,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
            $newFormId = $this->db->table('form_cut_input')->insertGetId($newForm);
            $this->log('form_cut_input', $newFormId, 'insert', null, ['no_form' => $noForm, 'from_form_id' => $oldForm->id]);

            $formMap[$oldForm->id] = (object) ['id' => $newFormId, 'no_form' => $noForm];

            // ---------- 3. Pindah output cutting (form_cut_input_detail_output + log switching)
            $this->transferCuttingOutput($oldForm, $newFormId, $markerDetailMap, $asal, $tujuan);

            // ---------- 4. Part & part form
            $oldPartForm = $this->db->table('part_form')->where('form_id', $oldForm->id)->first();
            if ($oldPartForm) {
                $newPart = $this->resolveNewPart($oldPartForm->part_id, $tujuan);

                $partFormId = $this->db->table('part_form')->insertGetId([
                    'kode' => $this->nextKode('part_form', 'PFM'),
                    'part_id' => $newPart->id,
                    'form_id' => $newFormId,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
                $this->log('part_form', $partFormId, 'insert', null, ['part_id' => $newPart->id, 'form_id' => $newFormId]);
            }

            // ---------- 5. Stocker, separate, modify size qty, loading
            $this->switchStocker($oldForm, $formMap[$oldForm->id], $asal, $tujuan, $summary);

            $summary['forms'][] = [
                'form_asal' => $oldForm->no_form,
                'form_baru' => $noForm,
                'marker_asal' => $oldMarker->kode,
                'marker_baru' => $newMarker->kode,
            ];
        }

        return $formMap;
    }

    private function transferCuttingOutput($oldForm, $newFormId, $markerDetailMap, $asal, $tujuan)
    {
        $now = Carbon::now();
        $user = Auth::user();

        $outputs = $this->db->table('form_cut_input_detail_output')
            ->where('form_cut_input_id', $oldForm->id)
            ->whereIn('marker_input_detail_id', array_keys($markerDetailMap) ?: [0])
            ->get();

        $logHasSoDet = $this->hasColumn(null, 'form_cut_input_detail_output_logs', 'so_det_id_asal');

        foreach ($outputs as $output) {
            $newDetail = $markerDetailMap[$output->marker_input_detail_id];
            $qty = intval($output->qty_output_aktual);

            $newOutputId = $this->db->table('form_cut_input_detail_output')->insertGetId([
                'form_cut_input_id' => $newFormId,
                'group_roll' => $output->group_roll,
                'marker_input_detail_id' => $newDetail->id,
                'size_asal' => $newDetail->size,
                'ratio' => $newDetail->ratio,
                'total_lembar_gelaran' => 0,
                'qty_output_original' => 0,
                'qty_output_aktual' => $qty,
                'is_active' => 1,
                'created_by' => $user->username,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
            $this->log('form_cut_input_detail_output', $newOutputId, 'insert', null, ['qty_output_aktual' => $qty]);

            if ($qty == 0) {
                continue;
            }

            $this->db->table('form_cut_input_detail_output')->where('id', $output->id)->update([
                'qty_output_aktual' => 0,
                'updated_at' => $now,
            ]);
            $this->log('form_cut_input_detail_output', $output->id, 'update', ['qty_output_aktual' => $qty], ['qty_output_aktual' => 0]);

            $logRow = [
                'form_cut_input_id_asal' => $oldForm->id,
                'form_cut_input_id_tujuan' => $newFormId,
                'group_roll_asal' => $output->group_roll,
                'size_asal' => $output->size_asal,
                'size_tujuan' => $newDetail->size,
                'qty_transfer' => $qty,
                'is_active' => 1,
                'created_by' => $user->username,
                'created_at' => $now,
                'updated_at' => $now,
            ];
            if ($logHasSoDet) {
                $logRow['so_det_id_asal'] = $asal->id_so_det;
                $logRow['so_det_id_tujuan'] = $tujuan->id_so_det;
            }
            $outputLogId = $this->db->table('form_cut_input_detail_output_logs')->insertGetId($logRow);
            $this->log('form_cut_input_detail_output_logs', $outputLogId, 'insert', null, $logRow);
        }
    }

    private function switchStocker($oldForm, $newForm, $asal, $tujuan, &$summary)
    {
        $now = Carbon::now();

        $stockers = $this->db->table('stocker_input')
            ->where('form_cut_id', $oldForm->id)
            ->where('so_det_id', $asal->id_so_det)
            ->get();

        foreach ($stockers as $stocker) {
            $after = [
                'form_cut_id' => $newForm->id,
                'so_det_id' => $tujuan->id_so_det,
                'part_detail_id' => $stocker->part_detail_id ? $this->resolveNewPartDetail($stocker->part_detail_id, $tujuan) : null,
                'act_costing_ws' => $tujuan->ws,
                'color' => $tujuan->color,
                'size' => $tujuan->size,
            ];
            $before = array_intersect_key((array) $stocker, $after);

            $this->db->table('stocker_input')->where('id', $stocker->id)->update($after);
            $this->log('stocker_input', $stocker->id, 'update', $before, $after);
        }
        $summary['counts']['stocker_input'] = ($summary['counts']['stocker_input'] ?? 0) + $stockers->count();

        // Stocker separate & modify size qty
        foreach (['stocker_separate', 'modify_size_qty'] as $table) {
            $rows = $this->db->table($table)->where('form_cut_id', $oldForm->id)->where('so_det_id', $asal->id_so_det)->get();
            foreach ($rows as $row) {
                $after = ['form_cut_id' => $newForm->id, 'so_det_id' => $tujuan->id_so_det, 'no_form' => $newForm->no_form];
                $this->db->table($table)->where('id', $row->id)->update($after + ['updated_at' => $now]);
                $this->log($table, $row->id, 'update', array_intersect_key((array) $row, $after), $after);
            }
            $summary['counts'][$table] = ($summary['counts'][$table] ?? 0) + $rows->count();
        }

        // DC loading
        $stockerIds = $stockers->pluck('id');
        $loadingHasSoDet = $this->hasColumn(null, 'loading_line', 'so_det_id');
        $loadingCount = 0;
        foreach ($stockerIds->chunk(self::CHUNK) as $ids) {
            $loadings = $this->db->table('loading_line')->whereIn('stocker_id', $ids->values()->all())->get();
            foreach ($loadings as $loading) {
                $after = [];
                if ($loading->loading_plan_id) {
                    $after['loading_plan_id'] = $this->resolveLoadingPlan($loading->loading_plan_id, $tujuan);
                }
                if ($loadingHasSoDet) {
                    $after['so_det_id'] = $tujuan->id_so_det;
                }
                if (count($after) < 1) {
                    continue;
                }
                $this->db->table('loading_line')->where('id', $loading->id)->update($after);
                $this->log('loading_line', $loading->id, 'update', array_intersect_key((array) $loading, $after), $after);
                $loadingCount++;
            }
        }
        $summary['counts']['loading_line'] = ($summary['counts']['loading_line'] ?? 0) + $loadingCount;
    }

    /* =====================================================================
     * STEP: YEAR SEQUENCE & STOCKER NUMBERING
     * ===================================================================== */

    private function switchNumbering($asal, $tujuan, $formMap, &$summary)
    {
        $tables = [
            'year_sequence' => ['so_det_id' => $tujuan->id_so_det, 'size' => $tujuan->size],
            'stocker_numbering' => ['so_det_id' => $tujuan->id_so_det, 'size' => $tujuan->size, 'act_costing_ws' => $tujuan->ws, 'color' => $tujuan->color],
        ];

        foreach ($tables as $table => $baseAfter) {
            $count = 0;
            $this->db->table($table)
                ->select(array_merge(['id', 'form_cut_id'], array_keys($baseAfter)))
                ->where('so_det_id', $asal->id_so_det)
                ->chunkById(self::CHUNK, function ($rows) use ($table, $baseAfter, $formMap, &$count) {
                    // Kelompokkan per form tujuan supaya update bisa massal
                    $groups = [];
                    foreach ($rows as $row) {
                        $newFormId = ($row->form_cut_id && isset($formMap[$row->form_cut_id])) ? $formMap[$row->form_cut_id]->id : null;
                        $groups[$newFormId ?? 'same'][] = $row;
                    }

                    foreach ($groups as $newFormId => $groupRows) {
                        $after = $baseAfter;
                        if ($newFormId !== 'same') {
                            $after['form_cut_id'] = $newFormId;
                        }

                        $this->db->table($table)->whereIn('id', array_column($groupRows, 'id'))->update($after);

                        foreach ($groupRows as $row) {
                            $this->log($table, $row->id, 'update', array_intersect_key((array) $row, $after), $after);
                        }
                        $count += count($groupRows);
                    }
                });

            $summary['counts'][$table] = $count;
        }
    }

    /* =====================================================================
     * STEP: OUTPUT SEWING / PACKING (signalbit)
     * ===================================================================== */

    private function switchOutputs($asal, $tujuan, &$summary)
    {
        foreach (array_merge(self::SB_OUTPUT_PLAN_TABLES, self::SB_OUTPUT_PLAIN_TABLES) as $table) {
            if (!$this->hasColumn($this->sb, $table, 'so_det_id')) {
                continue;
            }

            $hasPlan = $this->hasColumn($this->sb, $table, 'master_plan_id');
            $count = 0;

            $this->db->table($this->sbTable($table))
                ->select($hasPlan ? ['id', 'master_plan_id'] : ['id'])
                ->where('so_det_id', $asal->id_so_det)
                ->chunkById(self::CHUNK, function ($rows) use ($table, $hasPlan, $asal, $tujuan, &$count) {
                    $groups = [];
                    foreach ($rows as $row) {
                        $key = $hasPlan && $row->master_plan_id ? $row->master_plan_id : 'none';
                        $groups[$key][] = $row;
                    }

                    foreach ($groups as $oldPlanId => $groupRows) {
                        $after = ['so_det_id' => $tujuan->id_so_det];
                        if ($oldPlanId !== 'none') {
                            $newPlanId = $this->resolveMasterPlan($oldPlanId, $tujuan);
                            if ($newPlanId) {
                                $after['master_plan_id'] = $newPlanId;
                            }
                        }

                        // Query builder tidak menyentuh updated_at, timestamp output tetap
                        $this->db->table($this->sbTable($table))->whereIn('id', array_column($groupRows, 'id'))->update($after);

                        foreach ($groupRows as $row) {
                            $before = ['so_det_id' => $asal->id_so_det];
                            if (isset($after['master_plan_id'])) {
                                $before['master_plan_id'] = $row->master_plan_id;
                            }
                            $this->log($this->sb . '.' . $table, $row->id, 'update', $before, $after);
                        }
                        $count += count($groupRows);
                    }
                }, 'id');

            $summary['counts'][$table] = $count;
        }

        $summary['master_plans'] = $this->masterPlanMap;
    }

    /* =====================================================================
     * RESOLVERS
     * ===================================================================== */

    private function findPart($tujuan, $oldPart)
    {
        return $this->db->table('part')
            ->where('act_costing_id', $tujuan->id_act_cost)
            ->where('panel', $oldPart->panel)
            ->orderBy('id')
            ->first();
    }

    /**
     * Part tujuan (WS tujuan + panel yang sama). Dibuat bila belum ada, dengan part detail yang sama.
     */
    private function resolveNewPart($oldPartId, $tujuan, $depth = 0)
    {
        if (isset($this->partMap[$oldPartId])) {
            return $this->partMap[$oldPartId];
        }

        $oldPart = $this->db->table('part')->where('id', $oldPartId)->first();
        $existing = $this->findPart($tujuan, $oldPart);
        if ($existing) {
            return $this->partMap[$oldPartId] = $existing;
        }

        $now = Carbon::now();
        $user = Auth::user();

        $kode = $this->nextKode('part', 'PRT');
        $newPartId = $this->db->table('part')->insertGetId([
            'kode' => $kode,
            'act_costing_id' => $tujuan->id_act_cost,
            'act_costing_ws' => $tujuan->ws,
            'buyer' => $tujuan->buyer,
            'style' => $tujuan->styleno,
            'color' => $tujuan->color,
            'panel_id' => $oldPart->panel_id,
            'panel' => $oldPart->panel,
            'panel_status' => $oldPart->panel_status,
            'created_by' => $user->id,
            'created_by_username' => $user->username,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        $this->log('part', $newPartId, 'insert', null, ['kode' => $kode, 'from_part_id' => $oldPartId]);

        $newPart = $this->db->table('part')->where('id', $newPartId)->first();
        $this->partMap[$oldPartId] = $newPart;

        // Copy semua part detail (main/regular dulu, complement belakangan karena butuh from_part_detail)
        $oldDetails = $this->db->table('part_detail')->where('part_id', $oldPartId)
            ->orderByRaw("FIELD(part_status, 'main', 'regular', 'complement')")
            ->orderBy('id')
            ->get();
        foreach ($oldDetails as $oldDetail) {
            if (!isset($this->partDetailMap[$oldDetail->id])) {
                $this->partDetailMap[$oldDetail->id] = $this->copyPartDetail($oldDetail, $newPartId, $tujuan, $depth);
            }
        }

        return $newPart;
    }

    private function resolveNewPartDetail($oldPartDetailId, $tujuan, $depth = 0)
    {
        if (isset($this->partDetailMap[$oldPartDetailId])) {
            return $this->partDetailMap[$oldPartDetailId];
        }

        $oldDetail = $this->db->table('part_detail')->where('id', $oldPartDetailId)->first();
        if (!$oldDetail) {
            return $this->partDetailMap[$oldPartDetailId] = null;
        }

        $newPart = $this->resolveNewPart($oldDetail->part_id, $tujuan, $depth);

        // resolveNewPart bisa saja sudah mengisi map saat membuat part baru
        if (isset($this->partDetailMap[$oldPartDetailId])) {
            return $this->partDetailMap[$oldPartDetailId];
        }

        $existing = $this->db->table('part_detail')
            ->where('part_id', $newPart->id)
            ->where('master_part_id', $oldDetail->master_part_id)
            ->orderByRaw("CASE WHEN part_status = ? THEN 0 ELSE 1 END", [$oldDetail->part_status])
            ->orderByRaw("CASE WHEN status = 'inactive' THEN 1 ELSE 0 END")
            ->orderBy('id')
            ->first();

        return $this->partDetailMap[$oldPartDetailId] = $existing
            ? $existing->id
            : $this->copyPartDetail($oldDetail, $newPart->id, $tujuan, $depth);
    }

    private function copyPartDetail($oldDetail, $newPartId, $tujuan, $depth)
    {
        $now = Carbon::now();
        $user = Auth::user();

        $fromPartDetail = null;
        if ($oldDetail->from_part_detail && $depth < 3) {
            $fromPartDetail = $this->resolveNewPartDetail($oldDetail->from_part_detail, $tujuan, $depth + 1);
        }

        $row = $this->onlyColumns('part_detail', [
            'part_id' => $newPartId,
            'master_part_id' => $oldDetail->master_part_id,
            'master_secondary_id' => $oldDetail->master_secondary_id,
            'cons' => $oldDetail->cons,
            'unit' => $oldDetail->unit,
            'from_part_detail' => $fromPartDetail,
            'tujuan' => $oldDetail->tujuan,
            'part_status' => $oldDetail->part_status,
            'part_status_notes' => $oldDetail->part_status_notes,
            'batch' => $oldDetail->batch,
            'status' => $oldDetail->status ?? null,
            'notes' => $oldDetail->notes ?? null,
            'ignore_part' => $oldDetail->ignore_part ?? null,
            'created_by' => $user->id,
            'created_by_username' => $user->username,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        $newDetailId = $this->db->table('part_detail')->insertGetId($row);
        $this->log('part_detail', $newDetailId, 'insert', null, ['from_part_detail_id' => $oldDetail->id]);

        $secondaries = $this->db->table('part_detail_secondary')->where('part_detail_id', $oldDetail->id)->orderBy('urutan')->get();
        foreach ($secondaries as $secondary) {
            $this->db->table('part_detail_secondary')->insert([
                'part_detail_id' => $newDetailId,
                'master_secondary_id' => $secondary->master_secondary_id,
                'urutan' => $secondary->urutan,
                'batch' => $secondary->batch,
                'created_by' => $user->id,
                'created_by_username' => $user->username,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        return $newDetailId;
    }

    private function resolveLoadingPlan($oldPlanId, $tujuan)
    {
        if (isset($this->loadingPlanMap[$oldPlanId])) {
            return $this->loadingPlanMap[$oldPlanId];
        }

        $oldPlan = $this->db->table('loading_line_plan')->where('id', $oldPlanId)->first();
        if (!$oldPlan) {
            return $this->loadingPlanMap[$oldPlanId] = $oldPlanId;
        }

        $existing = $this->db->table('loading_line_plan')
            ->where('line_id', $oldPlan->line_id)
            ->where('act_costing_id', $tujuan->id_act_cost)
            ->where('color', $tujuan->color)
            ->where('tanggal', $oldPlan->tanggal)
            ->first();
        if ($existing) {
            return $this->loadingPlanMap[$oldPlanId] = $existing->id;
        }

        $now = Carbon::now();
        $kode = $this->nextKode('loading_line_plan', 'LLP');
        $newPlanId = $this->db->table('loading_line_plan')->insertGetId([
            'line_id' => $oldPlan->line_id,
            'kode' => $kode,
            'act_costing_id' => $tujuan->id_act_cost,
            'act_costing_ws' => $tujuan->ws,
            'buyer' => $tujuan->buyer,
            'style' => $tujuan->styleno,
            'color' => $tujuan->color,
            'target_sewing' => 0,
            'target_loading' => 0,
            'tanggal' => $oldPlan->tanggal,
            'created_by' => Auth::id(),
            'created_by_username' => Auth::user()->username,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        $this->log('loading_line_plan', $newPlanId, 'insert', null, ['kode' => $kode, 'from_loading_plan_id' => $oldPlanId]);

        return $this->loadingPlanMap[$oldPlanId] = $newPlanId;
    }

    private function findMasterPlan($tujuan, $oldPlan)
    {
        return $this->db->table($this->sbTable('master_plan'))
            ->where('id_ws', $tujuan->id_act_cost)
            ->where('color', $tujuan->color)
            ->where('sewing_line', $oldPlan->sewing_line)
            ->where('tgl_plan', $oldPlan->tgl_plan)
            ->where(function ($q) {
                $q->whereNull('cancel')->orWhere('cancel', '!=', 'Y');
            })
            ->orderBy('id')
            ->first();
    }

    /**
     * Master plan tujuan: line & tanggal sama dengan plan lama, WS/color tujuan.
     * Bila belum ada dibuat dengan jam kerja / smv / target 0 (pola SewingToolsController).
     */
    private function resolveMasterPlan($oldPlanId, $tujuan)
    {
        if (array_key_exists($oldPlanId, $this->masterPlanMap)) {
            return $this->masterPlanMap[$oldPlanId];
        }

        $oldPlan = $this->db->table($this->sbTable('master_plan'))->where('id', $oldPlanId)->first();
        if (!$oldPlan) {
            return $this->masterPlanMap[$oldPlanId] = null;
        }

        $existing = $this->findMasterPlan($tujuan, $oldPlan);
        if ($existing) {
            return $this->masterPlanMap[$oldPlanId] = $existing->id;
        }

        $newPlanId = $this->db->table($this->sbTable('master_plan'))->insertGetId([
            'id_plan' => str_replace('-', '', $oldPlan->tgl_plan),
            'id_ws' => $tujuan->id_act_cost,
            'color' => $tujuan->color,
            'sewing_line' => $oldPlan->sewing_line,
            'tgl_plan' => $oldPlan->tgl_plan,
            'tgl_input' => Carbon::now(),
            'jam_kerja' => 0,
            'smv' => 0,
            'man_power' => 0,
            'plan_target' => 0,
            'target_effy' => 0,
            'create_by' => Auth::user()->username,
            'cancel' => 'N',
        ]);
        $this->log($this->sb . '.master_plan', $newPlanId, 'insert', null, ['from_master_plan_id' => $oldPlanId]);

        return $this->masterPlanMap[$oldPlanId] = $newPlanId;
    }

    /* =====================================================================
     * HELPERS
     * ===================================================================== */

    public function getSoDet($id)
    {
        if (!$id) {
            return null;
        }

        return $this->db->table('master_sb_ws')
            ->select('id_so_det', 'id_act_cost', 'ws', 'styleno', 'buyer', 'color', 'size', 'dest')
            ->where('id_so_det', $id)
            ->first();
    }

    /**
     * Semua form cut (normal) yang marker-nya memuat so_det asal dengan rasio > 0,
     * ditambah form yang memiliki stocker so_det asal.
     */
    private function getForms($asalId)
    {
        $markerDetails = $this->db->table('marker_input_detail')
            ->join('marker_input', 'marker_input.id', '=', 'marker_input_detail.marker_id')
            ->where('marker_input_detail.so_det_id', $asalId)
            ->where(function ($q) {
                $q->whereNull('marker_input.cancel')->orWhere('marker_input.cancel', '!=', 'Y');
            })
            ->select('marker_input_detail.id', 'marker_input_detail.marker_id', 'marker_input_detail.ratio')
            ->get();

        $markerIdsWithRatio = $markerDetails->where('ratio', '>', 0)->pluck('marker_id')->unique()->values()->all();
        $stockerFormIds = $this->db->table('stocker_input')->where('so_det_id', $asalId)->whereNotNull('form_cut_id')->distinct()->pluck('form_cut_id')->all();

        $forms = $this->db->table('form_cut_input')
            ->leftJoin('marker_input', 'marker_input.id', '=', 'form_cut_input.marker_id')
            ->where(function ($q) use ($markerIdsWithRatio, $stockerFormIds) {
                $q->whereIn('form_cut_input.marker_id', $markerIdsWithRatio ?: [0])
                    ->orWhereIn('form_cut_input.id', $stockerFormIds ?: [0]);
            })
            ->where(function ($q) {
                $q->whereNull('form_cut_input.cancel')->orWhere('form_cut_input.cancel', '!=', 'Y');
            })
            ->select(
                'form_cut_input.id',
                'form_cut_input.no_form',
                'form_cut_input.no_cut',
                'form_cut_input.status',
                'form_cut_input.waktu_mulai',
                'form_cut_input.waktu_selesai',
                'form_cut_input.qty_ply',
                'form_cut_input.total_lembar',
                'form_cut_input.marker_id',
                'marker_input.kode as marker_kode',
                'marker_input.act_costing_ws',
                'marker_input.color',
                'marker_input.panel'
            )
            ->orderBy('form_cut_input.waktu_selesai')
            ->get();

        $detailsByMarker = $markerDetails->groupBy('marker_id');

        return $forms->map(function ($form) use ($detailsByMarker) {
            $details = $detailsByMarker->get($form->marker_id, collect());
            $form->marker_detail_ids = $details->pluck('id')->all();
            $form->ratio = $details->sum('ratio');
            return $form;
        });
    }

    private function nextMarkerNumber()
    {
        $latest = $this->db->table('marker_input')
            ->whereRaw("kode LIKE 'MRK/" . date('ym') . "/%'")
            ->max('kode');

        return intval(substr((string) $latest, -5)) + 1;
    }

    private $noFormCounter = null;

    private function nextNoForm()
    {
        $hari = date('d');
        $bulan = date('m');
        $prefix = $hari . '-' . $bulan . '-';

        if ($this->noFormCounter === null) {
            $lastForm = $this->db->table('form_cut_input')
                ->select('no_form')
                ->where('no_form', 'like', $prefix . '%')
                ->orderByRaw('CAST(SUBSTR(no_form, 7) AS UNSIGNED) DESC')
                ->lockForUpdate()
                ->first();

            $this->noFormCounter = $lastForm ? intval(str_replace($prefix, '', $lastForm->no_form)) : 0;
        }

        return $prefix . (++$this->noFormCounter);
    }

    private function nextKode($table, $prefix)
    {
        $latest = $this->db->table($table)->where('kode', 'like', $prefix . '%')->max('kode');

        return $prefix . sprintf('%05s', intval(substr((string) $latest, -5)) + 1);
    }

    private function sbTable($table)
    {
        return $this->sb . '.' . $table;
    }

    private function sameHost()
    {
        $nds = config('database.connections.mysql');
        $sb = config('database.connections.mysql_sb');

        $normalize = function ($host) {
            return in_array($host, ['localhost', '127.0.0.1', '::1']) ? 'local' : $host;
        };

        return $normalize($nds['host']) == $normalize($sb['host']) && $nds['port'] == $sb['port'];
    }

    private function tableExists($schema, $table)
    {
        return count($this->columns($schema, $table)) > 0;
    }

    private function hasColumn($schema, $table, $column)
    {
        return in_array($column, $this->columns($schema, $table));
    }

    private function columns($schema, $table)
    {
        $schema = $schema ?: config('database.connections.mysql.database');
        $key = $schema . '.' . $table;

        if (!isset($this->columnCache[$key])) {
            $this->columnCache[$key] = collect($this->db->select(
                'SELECT column_name AS name FROM information_schema.columns WHERE table_schema = ? AND table_name = ?',
                [$schema, $table]
            ))->pluck('name')->all();
        }

        return $this->columnCache[$key];
    }

    private function onlyColumns($table, array $data)
    {
        return array_intersect_key($data, array_flip($this->columns(null, $table)));
    }

    private function log($table, $rowId, $action, $before, $after)
    {
        $this->logDetails[] = [
            'switch_so_det_log_id' => $this->logId,
            'table_name' => $table,
            'row_id' => $rowId,
            'action' => $action,
            'before' => $before !== null ? json_encode($before) : null,
            'after' => $after !== null ? json_encode($after) : null,
            'created_at' => Carbon::now(),
        ];

        if (count($this->logDetails) >= self::CHUNK) {
            $this->flushLog();
        }
    }

    private function flushLog()
    {
        if (count($this->logDetails) > 0) {
            $this->db->table('switch_so_det_log_details')->insert($this->logDetails);
            $this->logDetails = [];
        }
    }
}
