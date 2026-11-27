{{--
    Isi 1 sel tabel daftar General Request (dirender server untuk paging server-side).
    $req = data request (format ambilRequest), $i = index baris di halaman, $kolom = nama kolom
--}}
@php
    $aktif = !in_array($req['status'], ['Selesai', 'Ditolak', 'Dibatalkan']);
    $tglId = function ($tgl) {
        $bln = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
        $t = strtotime($tgl);
        return date('d', $t) . ' ' . $bln[date('n', $t) - 1] . ' ' . date('Y', $t);
    };
@endphp
@switch($kolom)
    @case('caret')
        <i class="fas fa-chevron-right gr-caret"></i>
    @break

    @case('request')
        <div class="gr-no">{{ $req['no_request'] }}</div>
        <div class="gr-sub">
            {{ $tglId($req['tgl_request']) }}
            @if ($req['status'] == 'Dibatalkan')
                <span class="gr-stempel-batal"><i class="fas fa-ban"></i> BATAL</span>
            @endif
        </div>
    @break

    @case('dept')
        <div class="gr-main">{{ $req['departemen'] }}</div>
        <div class="gr-sub">oleh {{ $req['pemohon'] }}</div>
    @break

    @case('notes')
        @if ($req['catatan'])
            <div class="gr-catatan" title="{{ $req['catatan'] }}">{{ $req['catatan'] }}</div>
        @else
            <span class="gr-sub">&mdash;</span>
        @endif
    @break

    @case('item')
        {{ collect($req['items'])->unique(fn($it) => $it['item_id'] . '|' . $it['unit_id'] . '|' . $it['tgl_butuh'])->count() }}
    @break

    @case('butuh')
        @if ($req['tgl_butuh'])
            @php $selisih = (int) round((strtotime($req['tgl_butuh']) - strtotime(date('Y-m-d'))) / 86400); @endphp
            <div class="gr-main">{{ $tglId($req['tgl_butuh']) }}</div>
            @if ($aktif)
                @if ($selisih < 0)
                    <div class="gr-sub gr-due-late">Lewat {{ abs($selisih) }} hari</div>
                @elseif ($selisih <= 7)
                    <div class="gr-sub gr-due-soon">{{ $selisih == 0 ? 'Hari ini' : $selisih . ' hari lagi' }}</div>
                @else
                    <div class="gr-sub">{{ $selisih }} hari lagi</div>
                @endif
            @endif
        @else
            <span class="gr-sub">&mdash;</span>
        @endif
    @break

    @case('status')
        @php
            $totalQty = collect($req['items'])->sum('qty');
            $totalTerima = collect($req['items'])->sum('qty_terima');
            $persen = $totalQty > 0 ? round(($totalTerima / $totalQty) * 100) : 0;
            $adaPo = collect($req['items'])->sum('qty_po') > 0;
            $belumPutus = collect($req['approvers'])->whereNull('hasil')->pluck('nama')->implode(', ');
            // Langkah seperti lacak paket: posisi sekarang (Selesai = semua lewat, Ditolak = berhenti di Approval)
            $langkah = ['Draft', 'Approval', 'PO', 'Diterima'];
            $posisi = [
                'Draft' => 0,
                'Menunggu Approval' => 1,
                'Ditolak' => 1,
                'Proses PO' => 2,
                'Diterima Sebagian' => 3,
                'Selesai' => 4,
            ][$req['status']] ?? null;
            // "d-m-Y H:i" -> "24 Nov 17:04"
            $jam = function ($w) {
                $d = $w ? DateTime::createFromFormat('d-m-Y H:i', $w) : false;
                if (!$d) return null;
                $bln = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
                return $d->format('d') . ' ' . $bln[$d->format('n') - 1] . ' ' . $d->format('H:i');
            };
            $disetujui = collect($req['approvers'])->whereNotNull('waktu')->sortBy(fn($a) => DateTime::createFromFormat('d-m-Y H:i', $a['waktu']))->last();
        @endphp
        <span class="gr-chip {{ $aktif && $req['status'] != 'Draft' ? 'pulse' : '' }}">{{ $req['status'] }}</span>
        @if ($posisi !== null)
            <div class="gr-langkah {{ $req['status'] == 'Ditolak' ? 'tolak' : '' }}">
                @foreach ($langkah as $k => $nm)
                    @if ($k > 0)
                        <i class="{{ $k <= $posisi ? 'lewat' : '' }}"></i>
                    @endif
                    <span class="{{ $k < $posisi ? 'lewat' : ($k == $posisi ? 'kini' : '') }}"></span>
                @endforeach
                {{-- Riwayat langkah: ditampilkan melayang saat kursor di atas titik (JS) --}}
                <template class="gr-riwayat-isi">
                    <div class="r {{ $posisi > 0 ? 'lewat' : 'kini' }}">
                        <b>Draft</b>
                        <div>Dibuat {{ $jam($req['dibuat']) ?? '-' }} &middot; oleh {{ $req['pemohon'] }}</div>
                    </div>
                    <div class="r {{ $posisi > 1 ? 'lewat' : ($posisi == 1 ? 'kini' : '') }} {{ $req['status'] == 'Ditolak' ? 'tolak' : '' }}">
                        <b>Approval</b>
                        @if ($posisi == 0)
                            <div>Belum diajukan</div>
                        @elseif ($req['status'] == 'Ditolak')
                            <div>Ditolak</div>
                        @endif
                        @if ($posisi > 0)
                            @forelse ($req['approvers'] as $ap)
                                <div>
                                    @if ($ap['waktu'])
                                        <i class="fas fa-check-circle ok"></i> {{ $ap['nama'] }} &middot; {{ $jam($ap['waktu']) }}
                                    @elseif ($req['status'] != 'Ditolak')
                                        <i class="fas fa-hourglass-half"></i> {{ $ap['nama'] }} &middot; menunggu
                                    @else
                                        <i class="fas fa-minus-circle"></i> {{ $ap['nama'] }}
                                    @endif
                                </div>
                            @empty
                                <div>
                                    @if ($posisi > 1 && $req['status'] != 'Ditolak')
                                        <i class="fas fa-check-circle ok"></i> Approver tidak tercatat
                                    @else
                                        Belum ada approver
                                    @endif
                                </div>
                            @endforelse
                        @endif
                    </div>
                    <div class="r {{ $posisi > 2 ? 'lewat' : ($posisi == 2 ? 'kini' : '') }}">
                        <b>PO</b>
                        <div>{{ $posisi < 2 ? 'Setelah disetujui' : ($adaPo ? 'PO sudah dibuat' : 'Menunggu dibuatkan PO oleh Purchasing') }}</div>
                    </div>
                    <div class="r {{ $posisi > 3 ? 'lewat' : ($posisi == 3 ? 'kini' : '') }}">
                        <b>Diterima</b>
                        <div>{{ $posisi < 3 ? 'Setelah barang datang' : "Diterima {$totalTerima} / {$totalQty} ({$persen}%)" }}</div>
                    </div>
                </template>
            </div>
        @endif
        @if ($req['status'] == 'Dibatalkan')
            <div class="gr-sub">Dibatalkan {{ $req['diubah'] }}</div>
        @elseif ($req['status'] == 'Draft')
            <div class="gr-sub">Dibuat {{ $jam($req['dibuat']) ?? '-' }}</div>
        @elseif ($req['status'] == 'Menunggu Approval')
            <div class="gr-sub" title="Approver yang belum memutuskan">Menunggu: {{ $belumPutus ?: '-' }}</div>
        @elseif ($req['status'] == 'Ditolak')
            <div class="gr-sub">Tidak diproses</div>
        @elseif (!$adaPo)
            <div class="gr-sub">{{ $disetujui ? 'Disetujui ' . $jam($disetujui['waktu']) : 'Belum ada PO' }}</div>
        @else
            <div class="gr-sub gr-terima" title="Diterima {{ $totalTerima }} dari {{ $totalQty }}">
                <span class="progress gr-progress">
                    <span class="progress-bar {{ $persen >= 100 ? 'gr-bar-sage' : 'gr-bar-peach' }}" data-width="{{ $persen }}"></span>
                </span>
                Diterima {{ $persen }}%
            </div>
        @endif
    @break

    @case('aksi')
        <button class="gr-act-btn" style="--c-bg:#e9f0fa; --c-fg:#4a6fa5" onclick="showDetail({{ $i }}, event)"
            title="Tracking"><i class="fas fa-search-location"></i></button>
        {{-- Cetak PDF: semua status kecuali Dibatalkan --}}
        @if ($req['status'] != 'Dibatalkan')
            <a class="gr-act-btn" style="--c-bg:#eef1f5; --c-fg:#4b5563" href="{{ route('general-request-pdf', $req['id']) }}"
                target="_blank" onclick="event.stopPropagation()" title="Cetak PDF"><i class="fas fa-file-pdf"></i></a>
        @endif
        {{-- Ubah / Ajukan / Batalkan hanya untuk pemohonnya sendiri --}}
        @if ($req['status'] == 'Draft' && $req['milik'])
            <button class="gr-act-btn" style="--c-bg:#fbf3df; --c-fg:#b98a2e"
                onclick="event.stopPropagation(); bukaFormRequest({{ $i }}, 'ubah')" title="Ubah Draft"><i
                    class="fas fa-pencil-alt"></i></button>
            <button class="gr-act-btn" style="--c-bg:#e6f3eb; --c-fg:#4a8c68" onclick="aksiRequest('ajukan', {{ $i }}, event)"
                title="Ajukan Approval"><i class="fas fa-paper-plane"></i></button>
        @endif
        @if (in_array($req['status'], ['Draft', 'Menunggu Approval']) && $req['milik'])
            <button class="gr-act-btn" style="--c-bg:#f9e8ea; --c-fg:#c0505f" onclick="aksiRequest('batal', {{ $i }}, event)"
                title="Batalkan"><i class="fas fa-ban"></i></button>
        @elseif (in_array($req['status'], ['Selesai', 'Ditolak', 'Dibatalkan']))
            <button class="gr-act-btn" style="--c-bg:#eef1f5; --c-fg:#6b7687"
                onclick="event.stopPropagation(); bukaFormRequest({{ $i }}, 'ulang')" title="Request ulang dengan item yang sama"><i
                    class="fas fa-redo"></i></button>
        @endif
    @break
@endswitch
