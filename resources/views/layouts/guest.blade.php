@props(['title' => null])
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'GOR Futsal Nusantara' }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-cream text-ink min-h-screen flex flex-col items-center justify-center px-4 py-10 selection:bg-accent selection:text-white">
    <div class="mb-6 text-center">
        <a href="{{ route('home') }}" class="inline-flex items-center gap-3 group">
            <span class="w-11 h-11 rounded-xl bg-ink text-paper flex items-center justify-center font-bold text-sm tracking-wider shadow">
                GOR
            </span>
            <div class="text-left">
                <span class="block text-sm font-extrabold tracking-tight text-ink">GOR FUTSAL NUSANTARA</span>
                <span class="block text-[11px] text-ink/70">PORTAL AKSES INTERNAL</span>
            </div>
        </a>
    </div>

    <div class="w-full max-w-md wf-box rounded-2xl p-6 sm:p-8">
        {{ $slot }}
    </div>

    <div class="mt-6">
        <a href="{{ route('home') }}" class="text-xs font-medium text-ink/75 hover:text-accent transition">
            ← Kembali ke Halaman Utama Penyewa
        </a>
    </div>
    <script>
        (function () {
            function syncCsrfToken() {
                fetch('/csrf-token', {
                    credentials: 'same-origin',
                    headers: { 'Accept': 'application/json' }
                })
                .then(function (res) { return res.ok ? res.json() : null; })
                .then(function (data) {
                    if (!data || !data.token) return;
                    var meta = document.querySelector('meta[name="csrf-token"]');
                    if (meta) meta.setAttribute('content', data.token);
                    document.querySelectorAll('input[name="_token"]').forEach(function (input) {
                        input.value = data.token;
                    });
                })
                .catch(function () {});
            }
            document.addEventListener('visibilitychange', function () {
                if (document.visibilityState === 'visible') syncCsrfToken();
            });
            window.addEventListener('pageshow', function (event) {
                if (event.persisted) syncCsrfToken();
            });
        })();
    </script>
</body>
</html>
