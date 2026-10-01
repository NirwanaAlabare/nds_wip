{{-- Bunyi umpan balik scan RFID (Web Audio, tanpa file suara). Pilihan nyala/mati disimpan per browser. --}}
<script>
    const tabSound = (function () {
        const KEY = 'asset_tab_sound';
        let ctx = null;
        let enabled = true;

        try {
            enabled = localStorage.getItem(KEY) !== 'off';
        } catch (e) {}

        function tone(freq, start, duration, type) {
            let osc = ctx.createOscillator();
            let gain = ctx.createGain();
            let t = ctx.currentTime + start;

            osc.type = type || 'sine';
            osc.frequency.value = freq;
            gain.gain.setValueAtTime(0.0001, t);
            gain.gain.exponentialRampToValueAtTime(0.25, t + 0.01);
            gain.gain.exponentialRampToValueAtTime(0.0001, t + duration);
            osc.connect(gain);
            gain.connect(ctx.destination);
            osc.start(t);
            osc.stop(t + duration + 0.02);
        }

        // notes: [[frekuensi, mulai (detik), durasi (detik), tipe gelombang]]
        function play(notes) {
            if (!enabled) return;

            try {
                ctx = ctx || new (window.AudioContext || window.webkitAudioContext)();
                if (ctx.state === 'suspended') ctx.resume();
                notes.forEach((n) => tone(n[0], n[1], n[2], n[3]));
            } catch (e) {}
        }

        return {
            ok: () => play([[1046, 0, 0.08]]),
            error: () => {
                play([[220, 0, 0.14, 'square'], [220, 0.2, 0.14, 'square']]);
                if (enabled && navigator.vibrate) navigator.vibrate([120, 60, 120]);
            },
            done: () => play([[784, 0, 0.1], [1046, 0.11, 0.1], [1318, 0.22, 0.18]]),
            isOn: () => enabled,
            toggle() {
                enabled = !enabled;
                try {
                    localStorage.setItem(KEY, enabled ? 'on' : 'off');
                } catch (e) {}
                if (enabled) this.ok();
                return enabled;
            },
        };
    })();

    // Tombol speaker di header: ikon mengikuti status bunyi
    function refreshSoundButton() {
        let on = tabSound.isOn();
        $('#btnSound').toggleClass('is-off', !on).attr('title', on ? 'Bunyi scan: nyala' : 'Bunyi scan: mati')
            .find('i').attr('class', 'fa-solid ' + (on ? 'fa-volume-high' : 'fa-volume-xmark'));
    }

    function toggleSound() {
        tabSound.toggle();
        refreshSoundButton();
    }

    $(refreshSoundButton);
</script>
