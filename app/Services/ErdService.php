<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

// ERD Designer: diagram per koneksi (dipilih dari config/database.php). Struktur tabel yang sudah ada selalu dibaca dari database (sumber kebenaran),
// sedangkan layout (posisi, warna, catatan), tabel draft, dan relasi diagram disimpan di erd_diagrams.layout.
class ErdService
{
    const COLORS = ['#082149', '#1565c0', '#00897b', '#6a1b9a', '#c62828', '#ef6c00', '#455a64', '#2e7d32'];
    const CARDS = ['1:1', '1:N', 'N:1', 'N:N'];
    const IDENT = '/^[A-Za-z_][A-Za-z0-9_]{0,63}$/';
    const TYPE = "/^(?:(?:TINYINT|SMALLINT|MEDIUMINT|INT|INTEGER|BIGINT)(?:\(\d{1,3}\))?(?: UNSIGNED)?(?: ZEROFILL)?|(?:VARCHAR|CHAR|BINARY|VARBINARY|BIT)\(\d{1,5}\)|(?:DECIMAL|NUMERIC|DOUBLE|FLOAT)(?:\(\d{1,2}, ?\d{1,2}\))?(?: UNSIGNED)?(?: ZEROFILL)?|(?:DATETIME|TIMESTAMP|TIME)(?:\([0-6]\))?|BOOLEAN|BOOL|DATE|YEAR(?:\(4\))?|TINYTEXT|TEXT|MEDIUMTEXT|LONGTEXT|JSON|TINYBLOB|BLOB|MEDIUMBLOB|LONGBLOB|(?:ENUM|SET)\('[^'\\\\]*'(?:, ?'[^'\\\\]*')*\))$/i";
    const ONLINE_SECONDS = 60;
    const UNDO_LIMIT = 30;       // aksi terakhir per user yang bisa dibatalkan
    const UNDO_HOURS = 12;

    // ===== Koneksi =====

    // Semua koneksi mysql di config/database.php; user sendiri yang memilih koneksi untuk diagramnya
    public function connections()
    {
        $used = DB::table('erd_diagrams')->pluck('id', 'connection');

        return collect(config('database.connections'))
            ->filter(fn ($config) => ($config['driver'] ?? null) === 'mysql')
            ->map(fn ($config, $name) => [
                'name'       => $name,
                'host'       => $config['host'] ?? null,
                'database'   => $config['database'] ?? null,
                'diagram_id' => $used[$name] ?? null,
            ])
            ->values();
    }

    public function createDiagram($connection, $name, $user)
    {
        if (!$this->connections()->contains('name', $connection)) {
            $this->fail("Koneksi `$connection` tidak ditemukan di config/database.php");
        }
        if (DB::table('erd_diagrams')->where('connection', $connection)->exists()) {
            $this->fail('Koneksi ini sudah punya diagram');
        }

        $schema = $this->readSchema($connection);

        $id = DB::table('erd_diagrams')->insertGetId([
            'connection'      => $connection,
            'name'            => $name,
            'layout'          => json_encode(['tables' => (object) [], 'relations' => []]),
            'schema_snapshot' => json_encode($schema),
            'schema_hash'     => md5(json_encode($schema)),
            'created_by'      => $user->username,
            'updated_by'      => $user->username,
            'created_at'      => now(),
            'updated_at'      => now(),
        ]);

        $this->log($id, $user, 'diagram', "membuat diagram `$name` untuk koneksi `$connection`");

        return $id;
    }

    public function deleteDiagram($id, $user)
    {
        DB::table('erd_activities')->where('erd_diagram_id', $id)->delete();
        DB::table('erd_diagrams')->where('id', $id)->delete();
    }

    // ===== Baca struktur database =====

    public function readSchema($connection)
    {
        try {
            $db = DB::connection($connection);
            $database = $db->getDatabaseName();

            // COLUMNS dan TABLES di-query terpisah: JOIN antar information_schema di MariaDB bisa ratusan ms
            $baseTables = array_flip(array_column($db->select("
                SELECT TABLE_NAME FROM information_schema.TABLES WHERE TABLE_SCHEMA = ? AND TABLE_TYPE = 'BASE TABLE'
            ", [$database]), 'TABLE_NAME'));

            $columns = array_filter($db->select("
                SELECT TABLE_NAME, COLUMN_NAME, COLUMN_TYPE, IS_NULLABLE, COLUMN_KEY, COLUMN_DEFAULT, COLUMN_COMMENT, EXTRA
                FROM information_schema.COLUMNS
                WHERE TABLE_SCHEMA = ?
                ORDER BY TABLE_NAME, ORDINAL_POSITION
            ", [$database]), fn ($c) => isset($baseTables[$c->TABLE_NAME]));

            $fks = $db->select("
                SELECT CONSTRAINT_NAME, TABLE_NAME, COLUMN_NAME, REFERENCED_TABLE_NAME, REFERENCED_COLUMN_NAME
                FROM information_schema.KEY_COLUMN_USAGE
                WHERE TABLE_SCHEMA = ? AND REFERENCED_TABLE_NAME IS NOT NULL
                ORDER BY TABLE_NAME, CONSTRAINT_NAME, ORDINAL_POSITION
            ", [$database]);
        } catch (\Throwable $e) {
            $this->fail("Gagal membaca database koneksi `$connection`: " . $e->getMessage(), 503);
        }

        $tables = [];
        foreach ($columns as $c) {
            $tables[$c->TABLE_NAME][] = [
                'name' => $c->COLUMN_NAME,
                'type' => $this->normalizeType($c->COLUMN_TYPE),
                'pk'   => $c->COLUMN_KEY === 'PRI',
                'nn'   => $c->IS_NULLABLE === 'NO',
                'uq'   => $c->COLUMN_KEY === 'UNI',
                'ix'   => $c->COLUMN_KEY === 'MUL',
                'ai'   => stripos($c->EXTRA, 'auto_increment') !== false,
                'def'  => $this->normalizeDefault($c->COLUMN_DEFAULT),
                'comment' => (string) $c->COLUMN_COMMENT,
            ];
        }

        return [
            'tables' => $tables,
            'fks'    => array_map(fn ($f) => [
                'constraint' => $f->CONSTRAINT_NAME,
                'table'      => $f->TABLE_NAME,
                'column'     => $f->COLUMN_NAME,
                'ref_table'  => $f->REFERENCED_TABLE_NAME,
                'ref_column' => $f->REFERENCED_COLUMN_NAME,
            ], $fks),
        ];
    }

    // Huruf besar kecuali isi tanda kutip (nilai ENUM tetap apa adanya)
    public function normalizeType($type)
    {
        $type = preg_replace_callback("/'[^']*'|[^']+/", fn ($m) => $m[0][0] === "'" ? $m[0] : strtoupper($m[0]), trim($type));

        return preg_replace('/\s+/', ' ', $type);
    }

    // MariaDB mengembalikan default string dengan kutip ('aktif') dan "NULL" sebagai teks; MySQL tanpa kutip
    private function normalizeDefault($value)
    {
        if ($value === null || strtoupper($value) === 'NULL') {
            return null;
        }
        if (preg_match("/^'(.*)'$/s", $value, $m)) {
            return str_replace("''", "'", $m[1]);
        }
        if (preg_match('/^current_timestamp(\(\))?$/i', $value)) {
            return 'CURRENT_TIMESTAMP';
        }

        return $value;
    }

    // ===== State (dipanggil saat buka, polling, dan setelah setiap perubahan) =====

    // $revision = revisi yang sudah dimiliki browser; kalau masih sama, diagram tidak dikirim ulang.
    // $afterActivity = id riwayat terakhir di browser; hanya riwayat yang lebih baru yang dikirim.
    public function state($id, $user, $schema = null, $revision = null, $afterActivity = 0)
    {
        $diagram = DB::table('erd_diagrams')->find($id);
        if (!$diagram) {
            $this->fail('Diagram tidak ditemukan', 404);
        }

        $schema = $schema ?? $this->readSchema($diagram->connection);
        $hash = md5(json_encode($schema));

        // Jalur cepat (polling biasa): struktur DB sama dan layout tidak perlu disesuaikan -> tanpa lock & tanpa tulis
        $diagram->layout = $this->layout($diagram);
        [$layout, $changed] = $diagram->schema_hash === $hash ? $this->reconcile($diagram->layout, $schema) : [null, true];
        if ($changed) {
            $diagram = $this->syncWithDatabase($id, $schema, $hash);
        }

        $currentRevision = $diagram->version . '-' . $hash;
        $activities = DB::table('erd_activities')->where('erd_diagram_id', $id)->where('id', '>', (int) $afterActivity)
            ->orderByDesc('id')->limit(100)->get(['id', 'username', 'action', 'description', 'created_at']);

        return [
            'revision'   => $currentRevision,
            'diagram'    => $revision === $currentRevision ? null : $this->merge($diagram, $schema),
            'online'     => $this->touchOnline($id, $user),
            'activities' => $activities,
            'undo'       => $this->undoInfo($id, $user),
        ];
    }

    // Catat perubahan langsung di database ke riwayat, lalu sesuaikan layout dengan struktur terbaru
    private function syncWithDatabase($id, $schema, $hash)
    {
        return DB::transaction(function () use ($id, $schema, $hash) {
            $d = DB::table('erd_diagrams')->where('id', $id)->lockForUpdate()->first();

            // Perubahan yang dilakukan langsung di database (di luar ERD) dicatat ke riwayat
            if ($d->schema_hash !== $hash) {
                if ($d->schema_snapshot) {
                    foreach ($this->diffSchema(json_decode($d->schema_snapshot, true), $schema) as $message) {
                        $this->log($d->id, null, 'database', $message);
                    }
                }
                DB::table('erd_diagrams')->where('id', $d->id)->update(['schema_snapshot' => json_encode($schema), 'schema_hash' => $hash]);
            }

            [$layout, $changed] = $this->reconcile($this->layout($d), $schema);
            if ($changed) {
                $d->version++;
                DB::table('erd_diagrams')->where('id', $d->id)->update(['layout' => json_encode($layout), 'version' => $d->version]);
            }
            $d->layout = $layout;

            return $d;
        });
    }

    private function layout($diagram)
    {
        $layout = json_decode($diagram->layout ?: '{}', true) ?: [];

        return ['tables' => $layout['tables'] ?? [], 'relations' => $layout['relations'] ?? []];
    }

    // Samakan layout dengan database: tabel baru di DB masuk diagram, tabel yang dihapus di DB keluar,
    // draft yang ternyata sudah dibuat di DB jadi tabel biasa, relasi yang ujungnya hilang / sudah jadi FK dibuang
    private function reconcile($layout, $schema)
    {
        $tables = $layout['tables'];
        $changed = false;

        foreach ($tables as $name => $t) {
            $inDb = isset($schema['tables'][$name]);
            if (!empty($t['draft']) && $inDb) {
                unset($tables[$name]['draft'], $tables[$name]['columns']);
                $changed = true;
            } elseif (empty($t['draft']) && !$inDb) {
                unset($tables[$name]);
                $changed = true;
            }
        }

        $baseY = 40;
        foreach ($tables as $name => $t) {
            $count = isset($schema['tables'][$name]) ? count($schema['tables'][$name]) : count($t['columns'] ?? []);
            $baseY = max($baseY, ($t['y'] ?? 0) + 32 + $count * 24 + 80);
        }
        $i = 0;
        foreach ($schema['tables'] as $name => $columns) {
            if (!isset($tables[$name])) {
                $tables[$name] = ['x' => 40 + ($i % 4) * 300, 'y' => $baseY + intdiv($i, 4) * 300, 'color' => self::COLORS[count($tables) % count(self::COLORS)], 'note' => ''];
                $i++;
                $changed = true;
            }
        }

        $fkKeys = [];
        foreach ($schema['fks'] as $f) {
            $fkKeys["{$f['ref_table']}.{$f['ref_column']}>{$f['table']}.{$f['column']}"] = true;
        }
        $relations = array_values(array_filter($layout['relations'], function ($r) use ($tables, $schema, $fkKeys) {
            [$parent, $child] = $this->parentChild($r);

            return $this->columnExists($tables, $schema, $r['from']) && $this->columnExists($tables, $schema, $r['to'])
                && !isset($fkKeys["{$parent['t']}.{$parent['c']}>{$child['t']}.{$child['c']}"]);
        }));
        if (count($relations) !== count($layout['relations'])) {
            $changed = true;
        }

        return [['tables' => $tables, 'relations' => $relations], $changed];
    }

    private function columnsOf($tables, $schema, $table)
    {
        if (isset($schema['tables'][$table])) {
            return $schema['tables'][$table];
        }

        return $tables[$table]['columns'] ?? [];
    }

    private function columnExists($tables, $schema, $end)
    {
        return isset($tables[$end['t']]) && in_array($end['c'], array_column($this->columnsOf($tables, $schema, $end['t']), 'name'), true);
    }

    // Parent = sisi "1", child = sisi "N" (pemilik kolom FK)
    private function parentChild($r)
    {
        return ($r['card'] ?? '1:N') === 'N:1' ? [$r['to'], $r['from']] : [$r['from'], $r['to']];
    }

    // Gabungkan layout + struktur DB menjadi data yang digambar di kanvas
    private function merge($diagram, $schema)
    {
        $tables = [];
        foreach ($diagram->layout['tables'] as $name => $t) {
            $draft = !empty($t['draft']);
            $tables[] = [
                'id'      => $name,
                'name'    => $name,
                'draft'   => $draft,
                'x'       => $t['x'] ?? 40,
                'y'       => $t['y'] ?? 40,
                'color'   => $t['color'] ?? self::COLORS[0],
                'note'    => $t['note'] ?? '',
                'columns' => array_map(fn ($c) => ['id' => $c['name']] + $c, $draft ? ($t['columns'] ?? []) : ($schema['tables'][$name] ?? [])),
            ];
        }

        $relations = array_map(fn ($r) => $r + ['source' => 'diagram'], $diagram->layout['relations']);
        foreach ($schema['fks'] as $f) {
            if (!isset($diagram->layout['tables'][$f['table']], $diagram->layout['tables'][$f['ref_table']])) {
                continue;
            }
            $child = collect($schema['tables'][$f['table']] ?? [])->firstWhere('name', $f['column']);
            $relations[] = [
                'id'         => "fk:{$f['constraint']}:{$f['column']}",
                'from'       => ['t' => $f['ref_table'], 'c' => $f['ref_column']],
                'to'         => ['t' => $f['table'], 'c' => $f['column']],
                'card'       => !empty($child['uq']) ? '1:1' : '1:N',
                'label'      => '',
                'optional'   => empty($child['nn']),
                'source'     => 'db',
                'constraint' => $f['constraint'],
            ];
        }

        return [
            'id'         => $diagram->id,
            'name'       => $diagram->name,
            'connection' => $diagram->connection,
            'database'   => config("database.connections.{$diagram->connection}.database"),
            'version'    => $diagram->version,
            'tables'     => $tables,
            'relations'  => $relations,
        ];
    }

    // ===== Perubahan dari ERD (satu aksi per request) =====

    public function op($id, $user, array $op, $afterActivity = 0)
    {
        $diagram = DB::table('erd_diagrams')->find($id);
        if (!$diagram) {
            $this->fail('Diagram tidak ditemukan', 404);
        }
        $schema = $this->readSchema($diagram->connection);

        DB::transaction(function () use ($id, $user, $op, $schema) {
            $d = DB::table('erd_diagrams')->where('id', $id)->lockForUpdate()->first();
            $layout = $this->layout($d);
            $before = $layout;
            $tables = &$layout['tables'];
            $relations = &$layout['relations'];
            $log = null;
            $update = [];

            switch ($op['type'] ?? '') {
                case 'move':
                    foreach ($op['tables'] ?? [] as $m) {
                        if (isset($tables[$m['name'] ?? ''])) {
                            $tables[$m['name']]['x'] = (int) $m['x'];
                            $tables[$m['name']]['y'] = (int) $m['y'];
                        }
                    }
                    break;

                case 'table_meta':
                    $name = $this->requireTable($tables, $op['table'] ?? '');
                    if (isset($op['color']) && preg_match('/^#[0-9a-f]{6}$/i', $op['color'])) {
                        $tables[$name]['color'] = $op['color'];
                    }
                    if (array_key_exists('note', $op)) {
                        $tables[$name]['note'] = Str::limit((string) $op['note'], 1000, '');
                        $log = ['table', "mengubah catatan tabel `$name`"];
                    }
                    break;

                case 'table_create':
                    $name = $this->checkTableName($op['name'] ?? '', $tables, $schema);
                    $columns = $this->cleanColumns($op['columns'] ?? []);
                    $tables[$name] = [
                        'x' => (int) ($op['x'] ?? 40), 'y' => (int) ($op['y'] ?? 40),
                        'color' => preg_match('/^#[0-9a-f]{6}$/i', $op['color'] ?? '') ? $op['color'] : self::COLORS[0],
                        'note' => '', 'draft' => true, 'columns' => $columns,
                    ];
                    $log = ['table', "membuat tabel draft `$name` (" . implode(', ', array_column($columns, 'name')) . ')'];
                    break;

                case 'table_update':
                    $name = $this->requireDraft($tables, $op['table'] ?? '');
                    $changes = [];

                    if (isset($op['name']) && $op['name'] !== $name) {
                        $new = $this->checkTableName($op['name'], $tables, $schema);
                        $tables = $this->renameKey($tables, $name, $new);
                        foreach ($relations as &$r) {
                            foreach (['from', 'to'] as $end) {
                                if ($r[$end]['t'] === $name) $r[$end]['t'] = $new;
                            }
                        }
                        unset($r);
                        $changes[] = "nama `$name` → `$new`";
                        $name = $new;
                    }

                    if (isset($op['columns'])) {
                        $old = collect($tables[$name]['columns'])->keyBy('name');
                        $renames = array_filter((array) ($op['renames'] ?? []), fn ($to, $from) => is_string($from) && is_string($to), ARRAY_FILTER_USE_BOTH);
                        foreach ($relations as &$r) {
                            foreach (['from', 'to'] as $end) {
                                if ($r[$end]['t'] === $name && isset($renames[$r[$end]['c']])) $r[$end]['c'] = $renames[$r[$end]['c']];
                            }
                        }
                        unset($r);

                        $columns = $this->cleanColumns($op['columns']);
                        $tables[$name]['columns'] = $columns;

                        $renamedFrom = array_flip($renames);
                        foreach ($columns as $c) {
                            $prev = $old[$renamedFrom[$c['name']] ?? $c['name']] ?? null;
                            if (!$prev) {
                                $changes[] = "+`{$c['name']}` {$c['type']}";
                            } elseif ($prev['name'] !== $c['name']) {
                                $changes[] = "`{$prev['name']}` → `{$c['name']}`";
                            } elseif ($this->columnText($prev) !== $this->columnText($c)) {
                                $changes[] = "~`{$c['name']}` ({$this->columnText($prev)} → {$this->columnText($c)})";
                            }
                        }
                        $kept = array_map(fn ($c) => $renamedFrom[$c['name']] ?? $c['name'], $columns);
                        foreach ($old as $oldName => $c) {
                            if (!in_array($oldName, $kept, true)) $changes[] = "-`$oldName`";
                        }
                    }

                    if ($changes) {
                        $log = ['table', "mengubah tabel draft `$name`: " . implode(', ', $changes)];
                    }
                    break;

                case 'table_delete':
                    $name = $this->requireDraft($tables, $op['table'] ?? '');
                    unset($tables[$name]);
                    $relations = array_values(array_filter($relations, fn ($r) => $r['from']['t'] !== $name && $r['to']['t'] !== $name));
                    $log = ['table', "menghapus tabel draft `$name`"];
                    break;

                case 'relation_create':
                    $r = $this->cleanRelation($op, $tables, $schema);
                    if (!preg_match('/^r[a-z0-9]{4,20}$/i', $op['id'] ?? '') || collect($relations)->contains('id', $op['id'])) {
                        $op['id'] = 'r' . Str::random(10);
                    }
                    $relations[] = ['id' => $op['id']] + $r;
                    $log = ['relation', "membuat relasi {$this->relationText($r)}"];
                    break;

                case 'relation_update':
                    $index = collect($relations)->search(fn ($r) => $r['id'] === ($op['id'] ?? null));
                    if ($index === false) {
                        $this->fail('Relasi tidak ditemukan, mungkin sudah dihapus user lain');
                    }
                    $r = $this->cleanRelation($op, $tables, $schema);
                    $before = $this->relationText($relations[$index]);
                    $relations[$index] = ['id' => $op['id']] + $r;
                    if ($before !== $this->relationText($r)) {
                        $log = ['relation', "mengubah relasi $before menjadi {$this->relationText($r)}"];
                    }
                    break;

                case 'relation_delete':
                    $index = collect($relations)->search(fn ($r) => $r['id'] === ($op['id'] ?? null));
                    if ($index !== false) {
                        $log = ['relation', "menghapus relasi {$this->relationText($relations[$index])}"];
                        array_splice($relations, $index, 1);
                    }
                    break;

                case 'diagram_rename':
                    $name = trim(Str::limit((string) ($op['name'] ?? ''), 150, ''));
                    if ($name !== '' && $name !== $d->name) {
                        $update['name'] = $name;
                        $log = ['diagram', "mengganti nama diagram `{$d->name}` → `$name`"];
                    }
                    break;

                default:
                    $this->fail('Aksi tidak dikenal');
            }

            unset($tables, $relations);
            DB::table('erd_diagrams')->where('id', $id)->update($update + [
                'layout'     => json_encode(['tables' => (object) $layout['tables'], 'relations' => $layout['relations']]),
                'version'    => $d->version + 1,
                'updated_by' => $user->username,
                'updated_at' => now(),
            ]);

            $activityId = $log ? $this->log($id, $user, $log[0], $log[1]) : null;
            $label = $log ? $log[1] : (($op['type'] ?? '') === 'move' ? 'menggeser tabel ' . implode(', ', array_map(fn ($m) => "`{$m['name']}`", $op['tables'] ?? [])) : 'mengubah warna tabel');
            $this->pushUndo($id, $user, $before, $layout, $d->name, $update['name'] ?? $d->name, $activityId, $label);
        });

        return $this->state($id, $user, $schema, null, $afterActivity);
    }

    // ===== Undo: membatalkan aksi milik user sendiri =====
    // Disimpan di cache: bagian layout yang berubah (sebelum & sesudah). Undo ditolak kalau bagian itu
    // sudah berubah lagi sejak aksi tersebut (diubah user lain, aksi berikutnya, atau sudah dibuat di database).

    private function undoKey($id, $user)
    {
        return "erd_undo_{$id}_{$user->id}";
    }

    private function pushUndo($id, $user, $before, $after, $nameBefore, $nameAfter, $activityId, $label)
    {
        $diff = ['tables' => [], 'relations' => [], 'name' => null];
        foreach (array_unique(array_merge(array_keys($before['tables']), array_keys($after['tables']))) as $key) {
            $b = $before['tables'][$key] ?? null;
            $a = $after['tables'][$key] ?? null;
            if (json_encode($b) !== json_encode($a)) $diff['tables'][$key] = ['before' => $b, 'after' => $a];
        }
        $rb = collect($before['relations'])->keyBy('id')->all();
        $ra = collect($after['relations'])->keyBy('id')->all();
        foreach (array_unique(array_merge(array_keys($rb), array_keys($ra))) as $key) {
            $b = $rb[$key] ?? null;
            $a = $ra[$key] ?? null;
            if (json_encode($b) !== json_encode($a)) $diff['relations'][$key] = ['before' => $b, 'after' => $a];
        }
        if ($nameBefore !== $nameAfter) {
            $diff['name'] = ['before' => $nameBefore, 'after' => $nameAfter];
        }
        if (!$diff['tables'] && !$diff['relations'] && !$diff['name']) {
            return;
        }

        $stack = Cache::get($this->undoKey($id, $user), []);
        $stack[] = ['activity_id' => $activityId, 'label' => $label, 'diff' => $diff];
        Cache::put($this->undoKey($id, $user), array_slice($stack, -self::UNDO_LIMIT), now()->addHours(self::UNDO_HOURS));
    }

    private function undoInfo($id, $user)
    {
        if (!$user) {
            return ['last' => null, 'activities' => []];
        }
        $stack = Cache::get($this->undoKey($id, $user), []);
        $last = end($stack);

        return [
            'last'       => $last ? $last['label'] : null,
            'activities' => array_values(array_filter(array_column($stack, 'activity_id'))),
        ];
    }

    // $activityId = batalkan aksi tertentu dari Riwayat; null = aksi terakhir (Ctrl+Z)
    public function undo($id, $user, $activityId = null, $afterActivity = 0)
    {
        $diagram = DB::table('erd_diagrams')->find($id);
        if (!$diagram) {
            $this->fail('Diagram tidak ditemukan', 404);
        }
        $schema = $this->readSchema($diagram->connection);

        $stack = Cache::get($this->undoKey($id, $user), []);
        $index = $activityId ? array_search((int) $activityId, array_column($stack, 'activity_id'), true) : array_key_last($stack);
        if ($index === false || $index === null) {
            $this->fail('Tidak ada aksi Anda yang bisa dibatalkan');
        }
        $entry = $stack[$index];

        DB::transaction(function () use ($id, $user, $entry) {
            $d = DB::table('erd_diagrams')->where('id', $id)->lockForUpdate()->first();
            $layout = $this->layout($d);
            $relations = collect($layout['relations'])->keyBy('id')->all();
            $changed = 'Tidak bisa dibatalkan: %s sudah berubah setelah aksi ini (diubah user lain, ada aksi berikutnya, atau sudah dibuat di database).';

            foreach ($entry['diff']['tables'] as $key => $c) {
                if (json_encode($layout['tables'][$key] ?? null) !== json_encode($c['after'])) $this->fail(sprintf($changed, "tabel `$key`"));
            }
            foreach ($entry['diff']['relations'] as $key => $c) {
                if (json_encode($relations[$key] ?? null) !== json_encode($c['after'])) $this->fail(sprintf($changed, 'relasi'));
            }
            if ($entry['diff']['name'] && $d->name !== $entry['diff']['name']['after']) {
                $this->fail(sprintf($changed, 'nama diagram'));
            }

            foreach ($entry['diff']['tables'] as $key => $c) {
                if ($c['before'] === null) unset($layout['tables'][$key]);
                else $layout['tables'][$key] = $c['before'];
            }
            foreach ($entry['diff']['relations'] as $key => $c) {
                if ($c['before'] === null) unset($relations[$key]);
                else $relations[$key] = $c['before'];
            }

            DB::table('erd_diagrams')->where('id', $id)->update(($entry['diff']['name'] ? ['name' => $entry['diff']['name']['before']] : []) + [
                'layout'     => json_encode(['tables' => (object) $layout['tables'], 'relations' => array_values($relations)]),
                'version'    => $d->version + 1,
                'updated_by' => $user->username,
                'updated_at' => now(),
            ]);
            $this->log($id, $user, 'undo', "membatalkan: {$entry['label']}");
        });

        array_splice($stack, $index, 1);
        Cache::put($this->undoKey($id, $user), $stack, now()->addHours(self::UNDO_HOURS));

        return ['message' => "Dibatalkan: {$entry['label']}"] + $this->state($id, $user, $schema, null, $afterActivity);
    }

    private function requireTable($tables, $name)
    {
        if (!isset($tables[$name])) {
            $this->fail("Tabel `$name` tidak ditemukan, mungkin sudah diubah user lain");
        }

        return $name;
    }

    private function requireDraft($tables, $name)
    {
        $this->requireTable($tables, $name);
        if (empty($tables[$name]['draft'])) {
            $this->fail("Tabel `$name` sudah ada di database. Ubah strukturnya langsung di database.");
        }

        return $name;
    }

    private function checkTableName($name, $tables, $schema)
    {
        $name = trim((string) $name);
        if (!preg_match(self::IDENT, $name)) {
            $this->fail("Nama tabel `$name` tidak valid (huruf, angka, underscore; tidak diawali angka)");
        }
        $taken = array_map('strtolower', array_merge(array_keys($tables), array_keys($schema['tables'])));
        if (in_array(strtolower($name), $taken, true)) {
            $this->fail("Tabel `$name` sudah ada");
        }

        return $name;
    }

    private function cleanColumns($columns)
    {
        $clean = [];
        $names = [];
        foreach ((array) $columns as $c) {
            $name = trim((string) ($c['name'] ?? ''));
            $type = $this->normalizeType((string) ($c['type'] ?? ''));
            if (!preg_match(self::IDENT, $name)) {
                $this->fail("Nama kolom `$name` tidak valid");
            }
            if (isset($names[strtolower($name)])) {
                $this->fail("Kolom `$name` dobel");
            }
            if (!preg_match(self::TYPE, $type)) {
                $this->fail("Tipe `$type` untuk kolom `$name` tidak dikenal. Contoh: INT, BIGINT UNSIGNED, VARCHAR(100), DECIMAL(15,2), DATE, TEXT, ENUM('a','b')");
            }
            $names[strtolower($name)] = true;
            $pk = !empty($c['pk']);
            $def = isset($c['def']) && $c['def'] !== '' ? Str::limit((string) $c['def'], 255, '') : null;
            if ($def !== null && preg_match('/^(now|now\(\)|current_timestamp(\(\))?)$/i', $def)) {
                $def = 'CURRENT_TIMESTAMP';
            }
            $clean[] = [
                'name' => $name, 'type' => $type, 'pk' => $pk, 'nn' => $pk || !empty($c['nn']), 'uq' => !empty($c['uq']),
                'ix' => !empty($c['ix']), 'ai' => !empty($c['ai']), 'def' => $def, 'comment' => Str::limit((string) ($c['comment'] ?? ''), 255, ''),
            ];
        }

        $ai = array_filter($clean, fn ($c) => $c['ai']);
        if (count($ai) > 1) {
            $this->fail('Auto increment hanya boleh satu kolom per tabel');
        }
        foreach ($ai as $c) {
            if (!$c['pk'] && !$c['uq']) {
                $this->fail("Kolom auto increment `{$c['name']}` harus PK atau UNIQUE");
            }
        }

        return $clean;
    }

    private function cleanRelation($op, $tables, $schema)
    {
        $r = [
            'from'     => ['t' => (string) ($op['from']['t'] ?? ''), 'c' => (string) ($op['from']['c'] ?? '')],
            'to'       => ['t' => (string) ($op['to']['t'] ?? ''), 'c' => (string) ($op['to']['c'] ?? '')],
            'card'     => in_array($op['card'] ?? '', self::CARDS, true) ? $op['card'] : '1:N',
            'label'    => Str::limit((string) ($op['label'] ?? ''), 60, ''),
            'optional' => !empty($op['optional']),
        ];
        foreach (['from', 'to'] as $end) {
            if (!$this->columnExists($tables, $schema, $r[$end])) {
                $this->fail("Kolom `{$r[$end]['t']}.{$r[$end]['c']}` tidak ditemukan");
            }
        }

        return $r;
    }

    private function renameKey($array, $from, $to)
    {
        $result = [];
        foreach ($array as $key => $value) {
            $result[$key === $from ? $to : $key] = $value;
        }

        return $result;
    }

    // $full = false untuk snapshot lama yang belum menyimpan default/comment/index
    private function columnText($c, $full = true)
    {
        $text = $c['type'] . ($c['pk'] ? ' PK' : '') . ($c['nn'] && !$c['pk'] ? ' NN' : '') . ($c['uq'] ? ' UQ' : '') . ($c['ai'] ? ' AI' : '');
        if ($full) {
            $text .= (!empty($c['ix']) && !$c['pk'] && !$c['uq'] ? ' IX' : '')
                . (($c['def'] ?? null) !== null ? " ={$c['def']}" : '')
                . (($c['comment'] ?? '') !== '' ? " \"{$c['comment']}\"" : '');
        }

        return trim($text);
    }

    private function relationText($r)
    {
        return "`{$r['from']['t']}.{$r['from']['c']}` {$r['card']} `{$r['to']['t']}.{$r['to']['c']}`";
    }

    // ===== Terapkan tabel draft ke database (CREATE TABLE saja) =====

    // $only = nama tabel draft yang dipilih (null = semua draft). Parent yang masih draft ikut otomatis.
    public function preview($id, $only = null)
    {
        $diagram = DB::table('erd_diagrams')->find($id);
        if (!$diagram) {
            $this->fail('Diagram tidak ditemukan', 404);
        }
        $schema = $this->readSchema($diagram->connection);
        $layout = $this->layout($diagram);
        $tables = $layout['tables'];
        $drafts = array_values(array_filter(array_keys($tables), fn ($name) => !empty($tables[$name]['draft']) && !isset($schema['tables'][$name])));

        $errors = [];
        $notes = [];
        $fksByTable = [];
        $dependsOn = [];

        foreach ($layout['relations'] as $r) {
            [$parent, $child] = $this->parentChild($r);
            $childDraft = in_array($child['t'], $drafts, true);
            if (!$childDraft) {
                if (in_array($parent['t'], $drafts, true)) {
                    $notes[] = "Relasi {$this->relationText($r)} hanya dokumentasi: tabel `{$child['t']}` sudah ada di database (FK tidak dibuat).";
                }
                continue;
            }
            if ($r['card'] === 'N:N') {
                $notes[] = "Relasi {$this->relationText($r)} N:N tidak dibuat sebagai FK, perlu tabel pivot.";
                continue;
            }
            if ($parent['t'] === $child['t'] && $parent['c'] === $child['c']) {
                continue;
            }

            $parentCol = collect($this->columnsOf($tables, $schema, $parent['t']))->firstWhere('name', $parent['c']);
            $childCol = collect($tables[$child['t']]['columns'])->firstWhere('name', $child['c']);
            if (!$parentCol || !$childCol) {
                continue;
            }
            if (!$parentCol['pk'] && !$parentCol['uq']) {
                $errors[] = "FK `{$child['t']}.{$child['c']}` → `{$parent['t']}.{$parent['c']}`: kolom `{$parent['t']}.{$parent['c']}` harus PK atau UNIQUE.";
            }
            if ($this->baseType($parentCol['type']) !== $this->baseType($childCol['type'])) {
                $errors[] = "FK `{$child['t']}.{$child['c']}` → `{$parent['t']}.{$parent['c']}`: tipe harus sama ({$childCol['type']} vs {$parentCol['type']}).";
            }
            $fksByTable[$child['t']][] = ['column' => $child['c'], 'ref_table' => $parent['t'], 'ref_column' => $parent['c']];
            if ($parent['t'] !== $child['t'] && in_array($parent['t'], $drafts, true)) {
                $dependsOn[$child['t']][] = $parent['t'];
            }
        }

        // Tabel yang dipilih + parent draft-nya
        $selected = $only ? array_values(array_intersect($drafts, (array) $only)) : $drafts;
        for ($i = 0; $i < count($selected); $i++) {
            foreach ($dependsOn[$selected[$i]] ?? [] as $parent) {
                if (!in_array($parent, $selected, true)) {
                    $selected[] = $parent;
                    $notes[] = "Tabel `$parent` ikut diterapkan karena menjadi parent `{$selected[$i]}`.";
                }
            }
        }

        // Urutkan: parent dibuat lebih dulu
        $ordered = [];
        $visiting = [];
        $visit = function ($name) use (&$visit, &$ordered, &$visiting, &$errors, $dependsOn, $selected) {
            if (in_array($name, $ordered, true)) return;
            if (isset($visiting[$name])) {
                $errors[] = "Relasi melingkar di sekitar tabel `$name`, tidak bisa ditentukan urutan pembuatannya.";
                return;
            }
            $visiting[$name] = true;
            foreach ($dependsOn[$name] ?? [] as $parent) {
                if (in_array($parent, $selected, true) && $parent !== $name) $visit($parent);
            }
            unset($visiting[$name]);
            $ordered[] = $name;
        };
        foreach ($selected as $name) {
            $visit($name);
        }

        $statements = [];
        foreach ($ordered as $name) {
            $columns = $tables[$name]['columns'] ?? [];
            if (!array_filter($columns, fn ($c) => $c['pk'])) {
                $notes[] = "Tabel `$name` tidak punya primary key.";
            }
            $statements[] = [
                'table'   => $name,
                'sql'     => $this->createSql($name, $columns, $fksByTable[$name] ?? []),
                'columns' => $columns,
                'fks'     => $fksByTable[$name] ?? [],
            ];
        }

        return ['statements' => $statements, 'errors' => array_values(array_unique($errors)), 'notes' => array_values(array_unique($notes))];
    }

    // BIGINT(20) UNSIGNED dan BIGINT UNSIGNED dianggap sama
    private function baseType($type)
    {
        return preg_replace('/^(TINYINT|SMALLINT|MEDIUMINT|INT|INTEGER|BIGINT)\(\d+\)/', '$1', str_replace('INTEGER', 'INT', $this->normalizeType($type)));
    }

    private function createSql($table, $columns, $fks)
    {
        $q = fn ($s) => "`$s`";
        $lines = [];
        foreach ($columns as $c) {
            $lines[] = '  ' . $q($c['name']) . ' ' . $c['type'] . ($c['nn'] ? ' NOT NULL' : ' NULL') . $this->defaultSql($c)
                . ($c['ai'] ? ' AUTO_INCREMENT' : '') . (($c['comment'] ?? '') !== '' ? ' COMMENT ' . $this->quote($c['comment']) : '');
        }
        $pks = array_map($q, array_column(array_filter($columns, fn ($c) => $c['pk']), 'name'));
        if ($pks) {
            $lines[] = '  PRIMARY KEY (' . implode(', ', $pks) . ')';
        }
        foreach ($columns as $c) {
            if ($c['uq'] && !$c['pk']) {
                $lines[] = '  UNIQUE KEY ' . $q(substr("uq_{$table}_{$c['name']}", 0, 64)) . ' (' . $q($c['name']) . ')';
            } elseif (!empty($c['ix']) && !$c['pk']) {
                $lines[] = '  KEY ' . $q(substr("ix_{$table}_{$c['name']}", 0, 64)) . ' (' . $q($c['name']) . ')';
            }
        }
        foreach ($fks as $f) {
            $lines[] = '  CONSTRAINT ' . $q(substr("fk_{$table}_{$f['column']}", 0, 64)) . ' FOREIGN KEY (' . $q($f['column']) . ') REFERENCES ' . $q($f['ref_table']) . ' (' . $q($f['ref_column']) . ')';
        }

        return 'CREATE TABLE ' . $q($table) . " (\n" . implode(",\n", $lines) . "\n) ENGINE=InnoDB;";
    }

    // Angka untuk kolom numerik apa adanya, CURRENT_TIMESTAMP / NULL sebagai kata kunci, selain itu string berkutip
    private function defaultSql($c)
    {
        $def = $c['def'] ?? null;
        if ($def === null) {
            return '';
        }
        if (strtoupper($def) === 'NULL') {
            return ' DEFAULT NULL';
        }
        if (strtoupper($def) === 'CURRENT_TIMESTAMP') {
            return ' DEFAULT CURRENT_TIMESTAMP';
        }
        if (is_numeric($def) && preg_match('/INT|DECIMAL|NUMERIC|DOUBLE|FLOAT|BOOL|BIT|YEAR/', $c['type'])) {
            return " DEFAULT $def";
        }

        return ' DEFAULT ' . $this->quote($def);
    }

    private function quote($value)
    {
        return "'" . str_replace(['\\', "'"], ['\\\\', "''"], $value) . "'";
    }

    public function apply($id, $user, $only = null, $afterActivity = 0)
    {
        $diagram = DB::table('erd_diagrams')->find($id);
        $preview = $this->preview($id, $only);
        if ($preview['errors']) {
            $this->fail(implode("\n", $preview['errors']));
        }
        if (!$preview['statements']) {
            $this->fail('Tidak ada tabel draft untuk diterapkan');
        }

        // DDL MySQL tidak bisa di-rollback, jadi dijalankan satu per satu dan berhenti di error pertama
        $applied = [];
        $error = null;
        foreach ($preview['statements'] as $s) {
            try {
                DB::connection($diagram->connection)->statement($s['sql']);
                $applied[] = $s['table'];
                $this->log($id, $user, 'apply', "menerapkan tabel `{$s['table']}` ke database `{$diagram->connection}`");
            } catch (\Throwable $e) {
                $error = "Gagal membuat tabel `{$s['table']}`: " . $e->getMessage();
                break;
            }
        }

        // Simpan snapshot sekarang supaya tabel yang baru dibuat tidak tercatat sebagai "perubahan langsung di database"
        $schema = $this->readSchema($diagram->connection);
        DB::table('erd_diagrams')->where('id', $id)->update(['schema_snapshot' => json_encode($schema), 'schema_hash' => md5(json_encode($schema))]);

        // Kolom hasil baca ulang dari database, untuk ditampilkan apa yang benar-benar tersimpan
        $created = [];
        foreach ($applied as $table) {
            $created[$table] = $schema['tables'][$table] ?? [];
        }

        return ['applied' => $applied, 'created' => $created, 'error' => $error] + $this->state($id, $user, $schema, null, $afterActivity);
    }

    // ===== Riwayat & online =====

    private function log($diagramId, $user, $action, $description)
    {
        return DB::table('erd_activities')->insertGetId([
            'erd_diagram_id' => $diagramId,
            'user_id'        => $user->id ?? null,
            'username'       => $user->username ?? null,
            'action'         => $action,
            'description'    => $description,
            'created_at'     => now(),
        ]);
    }

    // Online = user yang memanggil state/aksi dalam ONLINE_SECONDS terakhir (ikut polling, tanpa broadcast)
    private function touchOnline($diagramId, $user)
    {
        $key = "erd_online_$diagramId";
        $online = array_filter(Cache::get($key, []), fn ($u) => $u['at'] > time() - self::ONLINE_SECONDS);
        if ($user) {
            $online[$user->id] = ['username' => $user->username, 'at' => time()];
        }
        Cache::put($key, $online, 300);

        return array_values(array_map(fn ($u) => $u['username'], $online));
    }

    private function diffSchema($old, $new)
    {
        $messages = [];
        $oldTables = $old['tables'] ?? [];
        $newTables = $new['tables'] ?? [];

        foreach ($newTables as $name => $columns) {
            if (!isset($oldTables[$name])) {
                $messages[] = "Tabel `$name` dibuat langsung di database (" . implode(', ', array_column($columns, 'name')) . ')';
                continue;
            }
            $before = collect($oldTables[$name])->keyBy('name');
            $after = collect($columns)->keyBy('name');
            $changes = [];
            foreach ($after as $col => $c) {
                if (!isset($before[$col])) {
                    $changes[] = "+`$col` {$c['type']}";
                } else {
                    $full = array_key_exists('def', $before[$col]);
                    if ($this->columnText($before[$col], $full) !== $this->columnText($c, $full)) {
                        $changes[] = "~`$col` ({$this->columnText($before[$col], $full)} → {$this->columnText($c, $full)})";
                    }
                }
            }
            foreach ($before as $col => $c) {
                if (!isset($after[$col])) $changes[] = "-`$col`";
            }
            if ($changes) {
                $messages[] = "Tabel `$name` diubah langsung di database: " . implode(', ', $changes);
            }
        }
        foreach ($oldTables as $name => $columns) {
            if (!isset($newTables[$name])) {
                $messages[] = "Tabel `$name` dihapus langsung di database";
            }
        }

        $key = fn ($f) => "{$f['table']}.{$f['column']}>{$f['ref_table']}.{$f['ref_column']}";
        $oldFks = collect($old['fks'] ?? [])->keyBy($key);
        $newFks = collect($new['fks'] ?? [])->keyBy($key);
        foreach ($newFks as $k => $f) {
            if (!isset($oldFks[$k])) $messages[] = "FK `{$f['table']}.{$f['column']}` → `{$f['ref_table']}.{$f['ref_column']}` dibuat langsung di database";
        }
        foreach ($oldFks as $k => $f) {
            if (!isset($newFks[$k])) $messages[] = "FK `{$f['table']}.{$f['column']}` → `{$f['ref_table']}.{$f['ref_column']}` dihapus langsung di database";
        }

        return $messages;
    }

    private function fail($message, $status = 422)
    {
        abort(response()->json(['message' => $message], $status));
    }
}
