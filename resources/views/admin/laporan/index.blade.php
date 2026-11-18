@extends('layouts.admin')

@section('title', 'Kalender & Laporan 5 Filter — Backoffice GOR Futsal')

@section('content')
<div x-data="{ filterStatus: 'ALL' }" class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-4 border-b border-ink/25">
        <div>
            <div class="font-mono text-[11px] font-bold text-accent uppercase">LAYAR 06 • REKAPITULASI & FILTER STATUS</div>
            <h1 class="text-2xl font-extrabold text-ink">Kalender Jadwal & Laporan Transaksi</h1>
        </div>
        <a href="{{ route('admin.laporan.pdf') }}" target="_blank"
           class="px-4 py-2.5 rounded-xl bg-ink hover:bg-accent text-paper text-xs font-extrabold wf-btn self-start">
            📄 Export PDF Laporan (Layar 06B)
        </a>
    </div>

    <!-- Ringkasan Omzet -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="wf-box rounded-2xl p-4">
            <div class="font-mono text-[10px] text-ink/60">PENDAPATAN MASUK (BULAN INI)</div>
            <div class="text-xl font-extrabold font-mono text-emerald-800 mt-1">Rp 14.850.000</div>
        </div>
        <div class="wf-box rounded-2xl p-4">
            <div class="font-mono text-[10px] text-ink/60">PIUTANG SISA DP 50%</div>
            <div class="text-xl font-extrabold font-mono text-amber-800 mt-1">Rp 1.420.000</div>
        </div>
        <div class="wf-box rounded-2xl p-4">
            <div class="font-mono text-[10px] text-ink/60">MENUNGGU VERIFIKASI QRIS</div>
            <div class="text-xl font-extrabold font-mono text-sky-800 mt-1">3 Transaksi</div>
        </div>
        <div class="wf-box rounded-2xl p-4">
            <div class="font-mono text-[10px] text-ink/60">OKUPANSI LAPANGAN</div>
            <div class="text-xl font-extrabold font-mono text-ink mt-1">78% Terisi</div>
        </div>
    </div>

    <!-- 5 Filter Status -->
    <div class="wf-box rounded-2xl p-5 space-y-4">
        <div class="flex flex-wrap items-center gap-2 text-xs font-bold">
            @foreach([
                ['key' => 'ALL', 'label' => 'Semua (12)'],
                ['key' => 'LUNAS', 'label' => '1. Lunas'],
                ['key' => 'DP_50', 'label' => '2. DP 50%'],
                ['key' => 'MENUNGGU_VERIFIKASI', 'label' => '3. Verifikasi QRIS'],
                ['key' => 'DIBATALKAN', 'label' => '4. Dibatalkan'],
                ['key' => 'MAINTENANCE', 'label' => '5. Blokir Maintenance'],
            ] as $f)
                <button @click="filterStatus = '{{ $f['key'] }}'"
                        :class="filterStatus === '{{ $f['key'] }}' ? 'bg-ink text-paper' : 'bg-cream/60 text-ink hover:bg-cream'"
                        class="px-3.5 py-2 rounded-xl border border-ink/20 transition">
                    {{ $f['label'] }}
                </button>
            @endforeach
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-xs text-left border-collapse">
                <thead>
                    <tr class="border-b border-ink/25 font-mono text-[11px] text-ink/70">
                        <th class="py-3 px-3">Kode</th>
                        <th class="py-3 px-3">Pelanggan</th>
                        <th class="py-3 px-3">Lapangan & Jadwal</th>
                        <th class="py-3 px-3">Total / Masuk</th>
                        <th class="py-3 px-3">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-ink/15">
                    @foreach([
                        ['code' => 'GOR-2026-089', 'name' => 'Fajar Apriliansyah', 'court' => 'Lapangan A • 17 Nov (19:00-21:00)', 'total' => 'Rp 360.000 / Rp 180.000', 'status' => 'DP_50'],
                        ['code' => 'GOR-2026-091', 'name' => 'Rizky Pratama', 'court' => 'Lapangan B • 19 Nov (16:00-18:00)', 'total' => 'Rp 260.000 / Rp 260.000', 'status' => 'MENUNGGU_VERIFIKASI'],
                        ['code' => 'GOR-2026-084', 'name' => 'Dimas Saputra', 'court' => 'Lapangan C • 13 Nov (20:00-21:00)', 'total' => 'Rp 160.000 / Rp 160.000', 'status' => 'LUNAS'],
                        ['code' => 'MAINT-014', 'name' => 'Internal GOR (Teknisi)', 'court' => 'Lapangan B • 17 Nov (12:00-13:00)', 'total' => 'Blokir Perawatan Rumput', 'status' => 'MAINTENANCE'],
                    ] as $row)
                        <tr x-show="filterStatus === 'ALL' || filterStatus === '{{ $row['status'] }}'" class="hover:bg-cream/35">
                            <td class="py-3 px-3 font-mono font-bold">{{ $row['code'] }}</td>
                            <td class="py-3 px-3 font-semibold">{{ $row['name'] }}</td>
                            <td class="py-3 px-3 font-mono text-[11px]">{{ $row['court'] }}</td>
                            <td class="py-3 px-3 font-mono font-bold">{{ $row['total'] }}</td>
                            <td class="py-3 px-3"><x-status-badge :status="$row['status']" /></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
