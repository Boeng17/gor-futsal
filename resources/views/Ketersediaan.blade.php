@extends('layouts.app')

@section('title', 'Kalender Ketersediaan Jadwal XXI — GOR Futsal Nusantara')

@section('content')
@php
    $courts = [
        ['id' => 'lap-a', 'name' => 'Lapangan A (Sintetis Pro)', 'floor' => 'RUMPUT SINTETIS FIFA', 'price_day' => 140000, 'price_night' => 180000],
        ['id' => 'lap-b', 'name' => 'Lapangan B (Sintetis Arena)', 'floor' => 'RUMPUT SINTETIS', 'price_day' => 130000, 'price_night' => 170000],
        ['id' => 'lap-c', 'name' => 'Lapangan C (Interlock Match)', 'floor' => 'INTERLOCK MATRAS PRO', 'price_day' => 120000, 'price_night' => 160000],
    ];
    $selectedCourtId = request('lapangan', 'lap-a');
    $dates = [
        ['date' => '2026-11-17', 'day' => 'Selasa', 'label' => '17 Nov'],
        ['date' => '2026-11-18', 'day' => 'Rabu', 'label' => '18 Nov'],
        ['date' => '2026-11-19', 'day' => 'Kamis', 'label' => '19 Nov'],
        ['date' => '2026-11-20', 'day' => 'Jumat', 'label' => '20 Nov'],
        ['date' => '2026-11-21', 'day' => 'Sabtu', 'label' => '21 Nov'],
        ['date' => '2026-11-22', 'day' => 'Minggu', 'label' => '22 Nov'],
        ['date' => '2026-11-23', 'day' => 'Senin', 'label' => '23 Nov'],
    ];
    $slots = [
        ['jam' => '08:00 - 09:00', 'sesi' => 'Siang', 'status' => 'tersedia'],
        ['jam' => '09:00 - 10:00', 'sesi' => 'Siang', 'status' => 'tersedia'],
        ['jam' => '10:00 - 11:00', 'sesi' => 'Siang', 'status' => 'terbooking'],
        ['jam' => '11:00 - 12:00', 'sesi' => 'Siang', 'status' => 'tersedia'],
        ['jam' => '12:00 - 13:00', 'sesi' => 'Siang', 'status' => 'maintenance'],
        ['jam' => '13:00 - 14:00', 'sesi' => 'Siang', 'status' => 'tersedia'],
        ['jam' => '14:00 - 15:00', 'sesi' => 'Siang', 'status' => 'tersedia'],
        ['jam' => '15:00 - 16:00', 'sesi' => 'Siang', 'status' => 'terbooking'],
        ['jam' => '16:00 - 17:00', 'sesi' => 'Siang', 'status' => 'tersedia'],
        ['jam' => '17:00 - 18:00', 'sesi' => 'Siang', 'status' => 'tersedia'],
        ['jam' => '18:00 - 19:00', 'sesi' => 'Malam', 'status' => 'terbooking'],
        ['jam' => '19:00 - 20:00', 'sesi' => 'Malam', 'status' => 'terbooking'],
        ['jam' => '20:00 - 21:00', 'sesi' => 'Malam', 'status' => 'tersedia'],
        ['jam' => '21:00 - 22:00', 'sesi' => 'Malam', 'status' => 'tersedia'],
        ['jam' => '22:00 - 23:00', 'sesi' => 'Malam', 'status' => 'tersedia'],
    ];
@endphp

<div x-data="{ activeCourt: '{{ $selectedCourtId }}', activeDate: '2026-11-17' }" class="max-w-7xl mx-auto px-4 sm:px-8 py-8 space-y-6">
    <!-- Header & Court Switcher -->
    <div class="wf-box rounded-2xl p-5 sm:p-6 flex flex-col lg:flex-row lg:items-center justify-between gap-4">
        <div class="space-y-1">
            <span class="px-2.5 py-0.5 rounded-full bg-ink text-paper font-mono text-[10px] font-bold">LAYAR 02 • KALENDER JADWAL XXI</span>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-ink">Cek Ketersediaan Jadwal Lapangan</h1>
            <p class="text-xs sm:text-sm text-ink/70">Pantau slot kosong secara real-time layaknya memilih kursi bioskop, lalu lanjutkan ke form pemesanan.</p>
        </div>

        <div class="flex flex-wrap gap-2">
            @foreach($courts as $c)
                <button @click="activeCourt = '{{ $c['id'] }}'"
                        :class="activeCourt === '{{ $c['id'] }}' ? 'bg-ink text-paper border-ink' : 'bg-cream/60 text-ink border-ink/25 hover:border-ink'"
                        class="px-3.5 py-2 rounded-xl border text-xs font-bold transition">
                    {{ $c['name'] }}
                </button>
            @endforeach
        </div>
    </div>

    <!-- Strip 7 Hari -->
    <div>
        <div class="font-mono text-[11px] font-bold text-ink/65 uppercase mb-2">1. PILIH TANGGAL MAIN</div>
        <div class="grid grid-cols-3 sm:grid-cols-7 gap-2.5">
            @foreach($dates as $d)
                <button @click="activeDate = '{{ $d['date'] }}'"
                        :class="activeDate === '{{ $d['date'] }}' ? 'bg-ink text-paper border-ink shadow-md' : 'bg-paper text-ink border-ink/25 hover:border-ink'"
                        class="p-3 rounded-xl border text-center transition">
                    <div :class="activeDate === '{{ $d['date'] }}' ? 'text-amber-300' : 'text-ink/60'" class="text-[10px] font-mono uppercase">{{ $d['day'] }}</div>
                    <div class="font-extrabold text-sm mt-0.5">{{ $d['label'] }}</div>
                </button>
            @endforeach
        </div>
    </div>

    <!-- Papan Matriks Jam Ala XXI -->
    <div class="wf-box rounded-2xl p-5 sm:p-6 space-y-5">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-4 border-b border-ink/15">
            <div>
                <div class="font-mono text-[10px] font-bold text-accent uppercase">2. MATRIKS SLOT JAM OPERASIONAL (08:00 – 23:00 WIB)</div>
                <h2 class="text-lg font-extrabold text-ink mt-0.5">Status Jadwal Tanggal <span x-text="activeDate"></span></h2>
            </div>

            <!-- Legenda -->
            <div class="flex flex-wrap items-center gap-3 text-xs font-semibold">
                <span class="flex items-center gap-1.5">
                    <span class="w-3.5 h-3.5 rounded border-2 border-emerald-700 bg-emerald-50 inline-block"></span> Tersedia
                </span>
                <span class="flex items-center gap-1.5">
                    <span class="w-3.5 h-3.5 rounded border border-rose-700 bg-rose-100 inline-block"></span> Terbooking
                </span>
                <span class="flex items-center gap-1.5">
                    <span class="w-3.5 h-3.5 rounded border border-ink/40 bg-maint inline-block"></span> Maintenance
                </span>
            </div>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-5 gap-3">
            @foreach($slots as $s)
                @if($s['status'] === 'tersedia')
                    <a :href="'{{ route('booking.create') }}?lapangan=' + activeCourt + '&tanggal=' + activeDate + '&jam={{ urlencode($s['jam']) }}'"
                       class="p-3.5 rounded-xl border-2 border-emerald-700 bg-emerald-50/90 hover:bg-emerald-100 transition text-left group">
                        <div class="flex items-center justify-between">
                            <span class="font-mono text-[10px] font-bold text-emerald-800 uppercase">{{ $s['sesi'] }}</span>
                            <span class="w-2 h-2 rounded-full bg-emerald-600"></span>
                        </div>
                        <div class="font-mono font-extrabold text-sm text-ink mt-1">{{ $s['jam'] }}</div>
                        <div class="text-[11px] font-bold text-emerald-800 mt-1.5 group-hover:underline">Tersedia • Pilih →</div>
                    </a>
                @elseif($s['status'] === 'terbooking')
                    <div class="p-3.5 rounded-xl border border-rose-300 bg-rose-100/70 opacity-80 cursor-not-allowed">
                        <div class="flex items-center justify-between">
                            <span class="font-mono text-[10px] font-bold text-rose-800 uppercase">{{ $s['sesi'] }}</span>
                            <span class="text-[10px] font-mono font-bold text-rose-800">LOCKED</span>
                        </div>
                        <div class="font-mono font-extrabold text-sm text-rose-950 mt-1">{{ $s['jam'] }}</div>
                        <div class="text-[11px] font-semibold text-rose-800 mt-1.5">Sudah Dibooking</div>
                    </div>
                @else
                    <div class="p-3.5 rounded-xl border border-ink/30 bg-maint/80 cursor-not-allowed">
                        <div class="flex items-center justify-between">
                            <span class="font-mono text-[10px] font-bold text-ink/70 uppercase">{{ $s['sesi'] }}</span>
                            <span class="text-[10px] font-mono font-bold text-ink">MAINT</span>
                        </div>
                        <div class="font-mono font-extrabold text-sm text-ink mt-1">{{ $s['jam'] }}</div>
                        <div class="text-[11px] font-semibold text-ink/80 mt-1.5">🛠️ Perawatan Lapangan</div>
                    </div>
                @endif
            @endforeach
        </div>

        <div class="pt-3 border-t border-ink/15 flex flex-col sm:flex-row items-center justify-between gap-3">
            <p class="text-xs text-ink/70">Ingin memesan lebih dari 1 jam sekaligus atau lintas hari (misal Selasa & Rabu)? Gunakan form multi-jadwal.</p>
            <a :href="'{{ route('booking.create') }}?lapangan=' + activeCourt + '&tanggal=' + activeDate"
               class="w-full sm:w-auto px-6 py-3 rounded-xl bg-accent hover:bg-accentDark text-white text-xs font-extrabold text-center wf-btn">
                Lanjut ke Form Multi-Booking →
            </a>
        </div>
    </div>
</div>
@endsection
