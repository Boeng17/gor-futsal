<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Backoffice Admin & Kasir — GOR Futsal Nusantara')</title>

    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @else
        <link href="https://fonts.googleapis.com/css2?family=DM+Mono:wght@400;500&family=Inter:wght@400;500;600;700;800&family=Lora:wght@500;600;700&display=swap" rel="stylesheet">
        <script src="https://cdn.tailwindcss.com"></script>
        <script>
            tailwind.config = {
                theme: {
                    extend: {
                        colors: {
                            cream: '#E2D6C6',
                            paper: '#FBF7F0',
                            ink: '#232B20',
                            accent: '#6C4830',
                            accentDark: '#543724',
                            sand: '#D6C8B3',
                            maint: '#E8D6AE',
                        },
                        fontFamily: {
                            sans: ['Inter', 'sans-serif'],
                            display: ['Lora', 'Georgia', 'serif'],
                            mono: ['DM Mono', 'monospace'],
                        }
                    }
                }
            }
        </script>
        <style>
            body { background-color: #E2D6C6; color: #232B20; font-family: 'Inter', sans-serif; }
            h1, h2, h3, .font-display { font-family: 'Lora', Georgia, serif; }
            .wf-box { background: #FBF7F0; border: 1px solid rgba(35,43,32,0.22); box-shadow: 0 6px 18px rgba(35,43,32,0.10); }
            .wf-card-flat { background: #FBF7F0; border: 1px solid rgba(35,43,32,0.22); box-shadow: 0 2px 8px rgba(35,43,32,0.06); }
            .wf-btn { transition: transform .12s ease, box-shadow .12s ease; box-shadow: 0 2px 6px rgba(35,43,32,0.18); }
        </style>
    @endif
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="bg-cream text-ink min-h-screen">
    <div class="flex flex-col lg:flex-row min-h-screen">
        @include('Component.admin-sidebar')

        <main class="flex-1 p-4 sm:p-6 lg:p-8 space-y-6">
            @yield('content')
        </main>
    </div>
    @stack('scripts')
</body>
</html>
