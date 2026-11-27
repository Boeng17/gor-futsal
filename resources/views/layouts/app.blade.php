<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'GOR Futsal Nusantara — Sistem Reservasi & Penjadwalan')</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
        <style>
        html { scroll-behavior: smooth; }
        @keyframes gfnPageEnter {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        main.gfn-page-shell {
            animation: gfnPageEnter 0.36s cubic-bezier(0.22, 1, 0.36, 1) both;
            transition: opacity 0.2s ease, transform 0.2s ease;
        }
        main.gfn-page-shell.gfn-leaving {
            opacity: 0;
            transform: translateY(-6px);
        }
        .gfn-reveal {
            opacity: 0;
            transform: translateY(18px);
            transition: opacity 0.55s cubic-bezier(0.22, 1, 0.36, 1), transform 0.55s cubic-bezier(0.22, 1, 0.36, 1);
            will-change: opacity, transform;
        }
        .gfn-reveal.is-visible {
            opacity: 1;
            transform: translateY(0);
        }
        .wf-box {
            transition: transform 0.28s cubic-bezier(0.22, 1, 0.36, 1), box-shadow 0.28s cubic-bezier(0.22, 1, 0.36, 1), border-color 0.28s ease;
        }
        .wf-btn {
            transition: transform 0.22s cubic-bezier(0.22, 1, 0.36, 1), background-color 0.22s ease, box-shadow 0.22s ease, color 0.22s ease;
        }
        .wf-btn:hover { transform: translateY(-1.5px); }
        .wf-btn:active { transform: translateY(0) scale(0.98); }
        @media (prefers-reduced-motion: reduce) {
            html { scroll-behavior: auto; }
            main.gfn-page-shell, .gfn-reveal, .wf-box, .wf-btn {
                animation: none !important;
                transition: none !important;
                opacity: 1 !important;
                transform: none !important;
            }
        }
    </style>
    @stack('styles')
</head>
<body class="bg-cream text-ink min-h-screen flex flex-col justify-between selection:bg-accent selection:text-white">
    @include('Component.navbar-customer')
    @include('Component.pending-payment-banner')

    <main class="flex-1 gfn-page-shell">
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

        <script>
        (function () {
            var mainEl = document.querySelector('main.gfn-page-shell');
            var revealTargets = document.querySelectorAll('.gfn-reveal');

            if ('IntersectionObserver' in window && revealTargets.length > 0) {
                var observer = new IntersectionObserver(function (entries) {
                    entries.forEach(function (entry) {
                        if (entry.isIntersecting) {
                            entry.target.classList.add('is-visible');
                            observer.unobserve(entry.target);
                        }
                    });
                }, { threshold: 0.08, rootMargin: '0px 0px -30px 0px' });

                revealTargets.forEach(function (el, idx) {
                    var delay = Math.min((idx % 4) * 70, 210);
                    el.style.transitionDelay = delay + 'ms';
                    observer.observe(el);
                });
            } else {
                revealTargets.forEach(function (el) {
                    el.classList.add('is-visible');
                });
            }

            document.addEventListener('click', function (e) {
                var link = e.target.closest('a[href]');
                if (!link || !mainEl) return;
                if (e.defaultPrevented || e.button !== 0 || e.metaKey || e.ctrlKey || e.shiftKey || e.altKey) return;
                if (link.target && link.target !== '_self') return;
                if (link.hasAttribute('download')) return;

                var href = link.getAttribute('href') || '';
                if (!href || href.startsWith('#') || href.startsWith('mailto:') || href.startsWith('tel:') || href.startsWith('javascript:')) return;

                var url;
                try {
                    url = new URL(link.href, window.location.origin);
                } catch (_) {
                    return;
                }
                if (url.origin !== window.location.origin) return;
                if (url.pathname === window.location.pathname && url.search === window.location.search && url.hash) return;

                e.preventDefault();
                mainEl.classList.add('gfn-leaving');
                setTimeout(function () {
                    window.location.href = url.href;
                }, 170);
            });

            window.addEventListener('pageshow', function (event) {
                if (event.persisted && mainEl) {
                    mainEl.classList.remove('gfn-leaving');
                }
            });
        })();
    </script>
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
