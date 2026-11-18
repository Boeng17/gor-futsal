@props(['status' => 'LUNAS'])

@php
    $normalized = strtoupper(str_replace(' ', '_', $status));
    $map = [
        'LUNAS' => ['label' => 'LUNAS', 'classes' => 'bg-emerald-100 text-emerald-950 border-emerald-700'],
        'DP_50' => ['label' => 'DP 50% (SISA DI GOR)', 'classes' => 'bg-amber-100 text-amber-950 border-amber-700'],
        'MENUNGGU_VERIFIKASI' => ['label' => 'MENUNGGU VERIFIKASI QRIS', 'classes' => 'bg-sky-100 text-sky-950 border-sky-700'],
        'BELUM_BAYAR' => ['label' => 'MENUNGGU PEMBAYARAN', 'classes' => 'bg-orange-100 text-orange-950 border-orange-700'],
        'DIBATALKAN' => ['label' => 'DIBATALKAN', 'classes' => 'bg-rose-100 text-rose-950 border-rose-700'],
        'MAINTENANCE' => ['label' => 'BLOKIR MAINTENANCE', 'classes' => 'bg-maint text-ink border-ink/50'],
    ];
    $item = $map[$normalized] ?? ['label' => $status, 'classes' => 'bg-cream text-ink border-ink/40'];
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex items-center px-2.5 py-0.5 rounded-full border font-mono text-[10px] font-bold tracking-tight ' . $item['classes']]) }}>
    {{ $item['label'] }}
</span>
