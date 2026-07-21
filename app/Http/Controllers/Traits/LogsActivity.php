<?php

namespace App\Http\Controllers\Traits;

use DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

trait LogsActivity
{
    /**
     * Catat aktivitas ke whs_log_activity memakai query log Laravel (enableQueryLog/getQueryLog)
     * yang dipanggil di sekitar operasi terkait. Hanya statement INSERT/UPDATE/DELETE yang
     * disimpan (SELECT dibuang) dengan binding disubstitusi langsung supaya siap di-copy-paste.
     */
    private function logRawQueryActivity(string $activity, $noDok, array $queryLog)
    {
        $lines = [];
        foreach ($queryLog as $q) {
            $sql = $q['query'];
            if (!preg_match('/^\s*(insert|update|delete)/i', $sql)) {
                continue;
            }
            foreach ($q['bindings'] as $binding) {
                if ($binding === null) {
                    $value = 'NULL';
                } elseif (is_numeric($binding)) {
                    $value = $binding;
                } elseif ($binding instanceof \DateTimeInterface) {
                    $value = "'" . $binding->format('Y-m-d H:i:s') . "'";
                } else {
                    $value = "'" . addslashes($binding) . "'";
                }
                $sql = preg_replace('/\?/', $value, $sql, 1);
            }
            $lines[] = $sql . ';';
        }

        if (empty($lines)) {
            return;
        }

        DB::connection('mysql_sb')->table('whs_log_activity')->insert([
            'activity' => $activity,
            'no_dok' => $noDok,
            'user' => Auth::user()->name,
            'query' => implode("\n", $lines),
            'created_at' => Carbon::now(),
        ]);
    }
}
