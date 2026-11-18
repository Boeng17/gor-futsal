@extends('layouts.internal')

@section('title', 'Dashboard Ringkasan — GOR Futsal Nusantara')

@section('content')
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
    <div>
        <span class="block text-xs font-bold uppercase tracking-wider text-accent">
            RINGKASAN PERFORMA ARENA
        </span>
        <h1 class="text-2xl sm:text-3xl font-bold text-ink mt-0.5">
            Dashboard Operasional & Statistik
        </h1>
    </div>
    <div class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-paper border border-ink/20 text-xs font-semibold text-ink">
        <span>Periode: {{ $periodLabel }}</span>
    </div>
</div>

<div class="grid grid-cols-1 md:grid-cols-3 gap-5">
    
    <div class="wf-box rounded-2xl p-6 flex flex-col justify-between">
        <div class="flex items-center justify-between gap-2 pb-3 border-b border-ink/10">
            <span class="text-sm font-bold text-ink">Total Pendapatan</span>
            <span class="text-[11px] font-medium text-ink/60">{{ $periodLabel }}</span>
        </div>
        <div class="pt-4">
            <div class="text-2xl sm:text-3xl font-extrabold text-ink tracking-tight">
                IDR {{ number_format($currentRevenue, 0, ',', '.') }}
            </div>
            <div class="mt-2 flex items-center gap-1.5 text-xs font-semibold">
                @if($revenueGrowth >= 0)
                    <span class="text-emerald-700">↑ {{ number_format($revenueGrowth, 2) }}%</span>
                @else
                    <span class="text-rose-700">↓ {{ number_format(abs($revenueGrowth), 2) }}%</span>
                @endif
                <span class="text-ink/60 font-normal">dari minggu lalu</span>
            </div>
        </div>
    </div>

    
    <div class="wf-box rounded-2xl p-6 flex flex-col justify-between">
        <div class="flex items-center justify-between gap-2 pb-3 border-b border-ink/10">
            <span class="text-sm font-bold text-ink">Total Jam Terbooking</span>
            <span class="text-[11px] font-medium text-ink/60">{{ $periodLabel }}</span>
        </div>
        <div class="pt-4">
            <div class="text-2xl sm:text-3xl font-extrabold text-ink tracking-tight">
                {{ number_format($currentSlots, 0, ',', '.') }} Jam
            </div>
            <div class="mt-2 flex items-center gap-1.5 text-xs font-semibold">
                @if($slotsGrowth >= 0)
                    <span class="text-emerald-700">↑ {{ number_format($slotsGrowth, 2) }}%</span>
                @else
                    <span class="text-rose-700">↓ {{ number_format(abs($slotsGrowth), 2) }}%</span>
                @endif
                <span class="text-ink/60 font-normal">dari minggu lalu</span>
            </div>
        </div>
    </div>

    
    <div class="wf-box rounded-2xl p-6 flex flex-col justify-between">
        <div class="flex items-center justify-between gap-2 pb-3 border-b border-ink/10">
            <span class="text-sm font-bold text-ink">Total Transaksi Booking</span>
            <span class="text-[11px] font-medium text-ink/60">{{ $periodLabel }}</span>
        </div>
        <div class="pt-4">
            <div class="text-2xl sm:text-3xl font-extrabold text-ink tracking-tight">
                {{ number_format($currentBookings, 0, ',', '.') }} Pesanan
            </div>
            <div class="mt-2 flex items-center gap-1.5 text-xs font-semibold">
                @if($bookingsGrowth >= 0)
                    <span class="text-emerald-700">↑ {{ number_format($bookingsGrowth, 2) }}%</span>
                @else
                    <span class="text-rose-700">↓ {{ number_format(abs($bookingsGrowth), 2) }}%</span>
                @endif
                <span class="text-ink/60 font-normal">dari minggu lalu</span>
            </div>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-stretch">

    
    <div class="lg:col-span-6 wf-box rounded-2xl p-6 flex flex-col justify-between"
         x-data="{ chartMode: 'revenue' }">
        <div class="flex flex-wrap items-start justify-between gap-3 mb-6">
            <div>
                <h2 class="text-lg font-bold text-ink">Statistik Laporan</h2>
                <p class="text-xs text-ink/65 mt-0.5">Periode: {{ $periodLabel }}</p>
            </div>

            <div class="inline-flex rounded-xl bg-cream p-1 border border-ink/15 text-xs font-bold">
                <button type="button" @click="chartMode = 'bookings'"
                        :class="chartMode === 'bookings' ? 'bg-paper text-ink shadow-sm' : 'text-ink/65 hover:text-ink'"
                        class="px-3 py-1.5 rounded-lg transition">
                    Jumlah Booking
                </button>
                <button type="button" @click="chartMode = 'revenue'"
                        :class="chartMode === 'revenue' ? 'bg-paper text-ink shadow-sm' : 'text-ink/65 hover:text-ink'"
                        class="px-3 py-1.5 rounded-lg transition">
                    Pendapatan
                </button>
            </div>
        </div>

        <div class="flex-1 flex items-end gap-3 pt-6 pb-2 min-h-[240px]">
            
            <div class="h-48 flex flex-col justify-between text-[11px] text-ink/55 font-medium pr-2 select-none">
                <span x-text="chartMode === 'revenue' ? 'Rp {{ number_format($maxRevenue / 1000, 0) }}k' : '{{ $maxBookings }}'"></span>
                <span x-text="chartMode === 'revenue' ? 'Rp {{ number_format(($maxRevenue * 0.66) / 1000, 0) }}k' : '{{ max(1, (int) round($maxBookings * 0.66)) }}'"></span>
                <span x-text="chartMode === 'revenue' ? 'Rp {{ number_format(($maxRevenue * 0.33) / 1000, 0) }}k' : '{{ max(1, (int) round($maxBookings * 0.33)) }}'"></span>
                <span>0</span>
            </div>

            
            <div class="flex-1 grid grid-cols-7 gap-3 items-end border-b border-ink/15 pb-2">
                @foreach($dailyStats as $stat)
                    <div class="flex flex-col items-center group relative">
                        <div class="w-full h-48 flex items-end justify-center">
                            <div x-show="chartMode === 'revenue'"
                                 style="height: {{ $stat['revenue_pct'] }}%"
                                 title="{{ $stat['full_date'] }}: Rp {{ number_format($stat['revenue'], 0, ',', '.') }}"
                                 class="w-3.5 sm:w-4 rounded-full bg-accent group-hover:bg-ink transition-all duration-300"></div>

                            <div x-show="chartMode === 'bookings'" x-cloak
                                 style="height: {{ $stat['bookings_pct'] }}%"
                                 title="{{ $stat['full_date'] }}: {{ $stat['bookings_count'] }} Booking"
                                 class="w-3.5 sm:w-4 rounded-full bg-ink group-hover:bg-accent transition-all duration-300"></div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        
        <div class="flex items-center gap-3 pl-12 pt-1">
            <div class="flex-1 grid grid-cols-7 gap-3 text-center text-[11px] font-medium text-ink/65">
                @foreach($dailyStats as $stat)
                    <span>{{ $stat['label'] }}</span>
                @endforeach
            </div>
        </div>
    </div>

    
    <div class="lg:col-span-6 wf-box rounded-2xl p-6 flex flex-col justify-between">
        <div>
            <div class="flex items-center justify-between gap-3 mb-5">
                <h2 class="text-lg font-bold text-ink">Transaksi Terbaru</h2>
                <a href="{{ route($reportRoute ?? 'admin.reports.index') }}"
                   class="px-3.5 py-1.5 rounded-xl bg-cream hover:bg-sand border border-ink/15 text-xs font-bold text-ink transition">
                    Lihat Semua Transaksi
                </a>
            </div>

            <div class="divide-y divide-ink/10">
                @forelse($latestBookings as $tx)
                    @php
                        $initials = collect(explode(' ', trim($tx->customer_name)))
                            ->filter()
                            ->take(2)
                            ->map(fn ($w) => strtoupper(mb_substr($w, 0, 1)))
                            ->implode('');
                    @endphp
                    <div class="py-3.5 first:pt-1 last:pb-1 flex items-center justify-between gap-3">
                        <div class="flex items-center gap-3 min-w-0">
                            <div class="w-10 h-10 rounded-full bg-cream border border-ink/20 flex items-center justify-center text-xs font-extrabold text-ink shrink-0">
                                {{ $initials ?: 'GF' }}
                            </div>
                            <div class="min-w-0">
                                <p class="text-sm font-bold text-ink truncate">
                                    {{ $tx->customer_name }}
                                </p>
                                <p class="text-xs text-ink/60">
                                    pada {{ $tx->created_at->translatedFormat('d M Y, H:i') }} WIB • <span class="font-mono font-semibold text-ink/80">{{ $tx->booking_code }}</span>
                                </p>
                            </div>
                        </div>

                        <div class="flex items-center gap-4 shrink-0">
                            <span class="text-xs sm:text-sm font-extrabold text-ink">
                                IDR {{ number_format($tx->total_amount, 0, ',', '.') }}
                            </span>
                            <span class="inline-flex items-center gap-1.5 text-xs font-semibold text-ink/80 min-w-[110px] justify-end">
                                <span class="w-2.5 h-2.5 rounded-full {{ $tx->display['dot_class'] }}"></span>
                                <span>{{ $tx->display['label'] }}</span>
                            </span>
                        </div>
                    </div>
                @empty
                    <div class="py-12 text-center text-xs text-ink/65">
                        Belum ada data transaksi pemesanan di database.
                    </div>
                @endforelse
            </div>
        </div>
    </div>

</div>
@endsection
