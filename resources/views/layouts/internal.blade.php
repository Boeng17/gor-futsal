<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Backoffice Panel — GOR Futsal Nusantara')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    @stack('styles')
</head>
<body class="bg-cream text-ink min-h-screen antialiased selection:bg-accent selection:text-white"
      x-data="{ sidebarOpen: false }"
      :class="{ 'overflow-hidden lg:overflow-auto': sidebarOpen }">

    <div class="min-h-screen flex flex-col lg:flex-row">
        <div class="lg:hidden bg-ink text-paper px-4 py-3.5 flex items-center justify-between border-b border-paper/15 sticky top-0 z-40">
            <div class="flex items-center gap-2.5">
                <span class="px-2 py-0.5 rounded bg-accent text-paper text-[10px] font-bold tracking-wider uppercase">
                    BACKOFFICE
                </span>
                <span class="font-bold text-sm">GOR Futsal Nusantara</span>
            </div>
            <button @click="sidebarOpen = !sidebarOpen" type="button"
                    class="p-2 rounded-lg bg-paper/10 text-paper hover:bg-paper/20">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path x-show="!sidebarOpen" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                    <path x-show="sidebarOpen" x-cloak stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        @include('Component.internal-sidebar')

        <div class="flex-1 min-w-0 flex flex-col justify-between">
            <main class="flex-1 w-full max-w-[1400px] mx-auto px-4 sm:px-6 lg:px-8 py-7 space-y-6">
                @if(session('success'))
                    <div class="bg-emerald-50 border border-emerald-700/30 text-emerald-950 px-4 py-3.5 rounded-xl text-xs font-semibold flex items-center gap-2.5 shadow-sm">
                        <span class="w-5 h-5 rounded-full bg-emerald-700 text-white inline-flex items-center justify-center text-[11px] font-bold shrink-0">✓</span>
                        <span>{{ session('success') }}</span>
                    </div>
                @endif

                @if($errors->any())
                    <div class="bg-rose-50 border border-rose-700/30 text-rose-950 px-4 py-3.5 rounded-xl text-xs shadow-sm">
                        <p class="font-bold mb-1">Periksa kembali input berikut:</p>
                        <ul class="list-disc list-inside space-y-0.5">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @yield('content')
            </main>
        </div>
    </div>

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
