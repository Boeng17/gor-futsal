<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'GOR Futsal Nusantara — Sistem Reservasi & Penjadwalan')</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    @stack('styles')
</head>
<body class="bg-cream text-ink min-h-screen flex flex-col justify-between selection:bg-accent selection:text-white">
    @include('Component.navbar-customer')
    @include('Component.pending-payment-banner')

    <main class="flex-1">
        @if(session('success'))
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-6 no-print">
                <div class="bg-emerald-50 border border-emerald-700/30 text-emerald-950 px-5 py-3.5 rounded-xl flex items-center justify-between gap-4 shadow-sm">
                    <div class="flex items-center gap-3 text-sm font-medium">
                        <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-emerald-700 text-white text-xs font-bold">✓</span>
                        <span>{{ session('success') }}</span>
                    </div>
                </div>
            </div>
        @endif

        @if($errors->any())
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-6 no-print">
                <div class="bg-rose-50 border border-rose-700/30 text-rose-950 px-5 py-3.5 rounded-xl shadow-sm">
                    <p class="text-sm font-semibold mb-1">Periksa kembali input Anda:</p>
                    <ul class="list-disc list-inside text-xs space-y-0.5">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endif

        @yield('content')
    </main>

    @include('Component.footer-customer')

    @stack('scripts')
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
