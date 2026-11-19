<!DOCTYPE html>
{{-- Login NDS tampilan baru (dari prototype proto/login.html), HTML + JS biasa tanpa React. Versi lama: auth/login_old.blade.php --}}
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <title>Log in</title>
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('dist/img/tabicon.png') }}">
    <script>try{var t=localStorage.getItem("nds-theme");if(t==="dark"||t==="light")document.documentElement.setAttribute("data-theme",t)}catch(e){}</script>
    <link rel="stylesheet" href="{{ asset('dist/css/login-nds.css') }}?v={{ @filemtime(public_path('assets/dist/css/login-nds.css')) }}">
</head>

<body>
    <div class="page">
        <section class="art">
            <div class="aurora" aria-hidden="true"><i></i><i></i><i></i><i></i></div>
            <div class="dots" aria-hidden="true"></div>
            <div class="meta top"><span id="login-clock"></span></div>
            @include('auth.partials.login_mark')
            <div>
                <h1>Satu sistem untuk <em>seluruh proses bisnis.</em></h1>
                <p>Nirwana Digital Solution menghubungkan setiap departemen PT Nirwana Alabare Garment dalam satu aplikasi.</p>
            </div>
        </section>

        <section class="side">
            <div class="box">
                <small class="k">/ LOGIN</small>
                <h2>Selamat Datang</h2>
                <p class="sub">Silakan masuk menggunakan akun NDS Anda.</p>

                <form id="login-form" action="{{ route('login') }}" method="post" novalidate>
                    @csrf
                    <label class="f">Username
                        <div class="in" id="in-username">
                            <svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><circle cx="12" cy="8" r="4"/><path d="M4 21c0-4 4-6 8-6s8 2 8 6"/></svg>
                            <input type="text" name="username" id="username" value="{{ old('username') }}" placeholder="Masukkan username" autocomplete="username" autofocus>
                        </div>
                        <span class="hint" id="hint-username" hidden>Username wajib diisi.</span>
                    </label>

                    <label class="f">Password
                        <div class="in" id="in-password">
                            <svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><rect x="4" y="10" width="16" height="11" rx="2.5"/><path d="M8 10V7a4 4 0 0 1 8 0v3"/></svg>
                            <input type="password" name="password" id="password" placeholder="Masukkan password" autocomplete="current-password">
                            <button type="button" class="eye" id="toggle-password" aria-label="Tampilkan password">
                                <svg class="ic-eye" width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12z"/><circle cx="12" cy="12" r="3"/></svg>
                                <svg class="ic-eye-off" hidden width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M3 3l18 18M10.6 5.1A10.8 10.8 0 0 1 12 5c6.5 0 10 7 10 7a17 17 0 0 1-3.2 4.2M6.6 6.6C3.9 8.4 2 12 2 12s3.5 7 10 7c1.8 0 3.3-.5 4.6-1.2"/><path d="M9.9 9.9a3 3 0 0 0 4.2 4.2"/></svg>
                            </button>
                        </div>
                        <span class="hint" id="hint-password" hidden>Password wajib diisi.</span>
                        <span class="caps" id="caps-warning" hidden>Caps Lock sedang aktif.</span>
                    </label>

                    <div class="row">
                        <label class="chk"><input type="checkbox" name="remember" {{ old('remember', true) ? 'checked' : '' }}>Ingat saya</label>
                        <button type="button" class="link" id="toggle-help">Lupa password?</button>
                    </div>

                    @if ($errors->any())
                        <div class="alert e" role="status">{{ $errors->first('username') ?: $errors->first() }}</div>
                    @endif

                    <button class="go" type="submit" id="login-submit">
                        Masuk <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
                    </button>
                </form>

                <div class="help" id="login-help" hidden>Reset password dilakukan oleh tim IT. Buat tiket di Helpdesk atau hubungi IT.</div>

                <div class="foot"><span>© {{ date('Y') }} PT Nirwana Alabare Garment</span><span>Nirwana Digital Solution · v2</span></div>
            </div>
        </section>
    </div>

    <button type="button" class="thm thm-fix" aria-label="Ganti tema terang atau gelap" title="Ganti tema"><svg class="moon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 12.8A9 9 0 1 1 11.2 3a7 7 0 0 0 9.8 9.8z"/></svg><svg class="sun" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="4"/><path d="M12 2v2M12 20v2M4.9 4.9l1.4 1.4M17.7 17.7l1.4 1.4M2 12h2M20 12h2M4.9 19.1l1.4-1.4M17.7 6.3l1.4-1.4"/></svg></button>

    <script>
        (function () {
            var $ = function (id) { return document.getElementById(id); };

            // Jam di panel kiri, diperbarui tiap 15 detik.
            function tick() {
                var d = new Date();
                $('login-clock').textContent = d.toLocaleDateString('id-ID', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' }).toUpperCase()
                    + ' · ' + d.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' }).replace('.', ':');
            }
            tick();
            setInterval(tick, 15000);

            var user = $('username'), pass = $('password'), form = $('login-form'), go = $('login-submit');
            if (user.value) pass.focus();

            // Tampilkan / sembunyikan password.
            $('toggle-password').addEventListener('click', function () {
                var show = pass.type === 'password';
                pass.type = show ? 'text' : 'password';
                this.querySelector('.ic-eye').hidden = show;
                this.querySelector('.ic-eye-off').hidden = !show;
                this.setAttribute('aria-label', show ? 'Sembunyikan password' : 'Tampilkan password');
            });

            // Peringatan Caps Lock.
            ['keydown', 'keyup'].forEach(function (ev) {
                pass.addEventListener(ev, function (e) { $('caps-warning').hidden = !(e.getModifierState && e.getModifierState('CapsLock')); });
            });

            $('toggle-help').addEventListener('click', function () { $('login-help').hidden = !$('login-help').hidden; });

            // Validasi wajib isi di browser, lalu kirim form biasa ke server.
            function check(input, key) {
                var bad = key === 'username' ? !input.value.trim() : !input.value;
                $('in-' + key).classList.toggle('bad', bad);
                $('hint-' + key).hidden = !bad;
                return !bad;
            }
            user.addEventListener('input', function () { if ($('in-username').classList.contains('bad')) check(user, 'username'); });
            pass.addEventListener('input', function () { if ($('in-password').classList.contains('bad')) check(pass, 'password'); });

            form.addEventListener('submit', function (e) {
                var ok = check(user, 'username') & check(pass, 'password');
                if (!ok) { e.preventDefault(); return; }
                go.disabled = true;
                go.innerHTML = '<span class="spin"></span>Memeriksa…';
            });

            // Ganti tema terang / gelap.
            document.addEventListener('click', function (e) {
                if (!e.target.closest('.thm')) return;
                var r = document.documentElement;
                var cur = r.getAttribute('data-theme') || (matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
                var n = cur === 'dark' ? 'light' : 'dark';
                r.setAttribute('data-theme', n);
                try { localStorage.setItem('nds-theme', n); } catch (err) {}
            });
        })();
    </script>
</body>

</html>
