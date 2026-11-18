<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Cetak Dokumen — GOR Futsal Nusantara')</title>
    <link href="https://fonts.googleapis.com/css2?family=DM+Mono:wght@400;500&family=Inter:wght@400;600;700;800&family=Lora:wght@600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body { font-family: 'Inter', sans-serif; color: #232B20; background: #F5F2EB; }
        h1, h2, .font-display { font-family: 'Lora', Georgia, serif; }
        .font-mono { font-family: 'DM Mono', monospace; }
        @media print {
            body { background: #FFFFFF; }
            .no-print { display: none !important; }
            .print-sheet { box-shadow: none !important; border: 1px solid #232B20 !important; margin: 0 !important; max-width: 100% !important; }
        }
    </style>
</head>
<body class="p-4 sm:p-8">
    @yield('content')
</body>
</html>
