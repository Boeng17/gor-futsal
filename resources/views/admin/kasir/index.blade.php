@extends('layouts.admin')

@section('title', 'Kasir & Operasional Transaksi — Backoffice GOR Futsal')

@section('content')
<div x-data="{ activeModal: null, selectedCode: 'GOR-2026-091' }" class="space-y-6">
    <!-- Header & Quick Action Buttons (07E Walk-In & 07G Blokir Maintenance) -->
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 pb-4 border-b border-ink/25">
        <div>
            <div class="font-mono text-[11px] font-bold text-accent uppercase">LAYAR 07 • OPERASIONAL KASIR HARIAN</div>
            <h1 class="text-2xl font-extrabold text-ink">Antrean Verifikasi QRIS, Pelunasan DP & Walk-In</h1>
        </div>

        <div class="flex flex-wrap gap-2">
            <button @click="activeModal = 'walkin'" class="px-4 py-2.5 rounded-xl bg-accent hover:bg-accentDark text-white text-xs font-extrabold wf-btn">
                + Input Walk-In (07E)
            </button>
            <button @click="activeModal = 'maintenance'" class="px-4 py-2.5 rounded-xl bg-maint border border-ink text-ink text-xs font-extrabold wf-btn">
                🛠️ Blokir Maintenance Jam (07G)
            </button>
        </div>
    </div>

    <!-- Daftar Transaksi Operasional -->
    <div class="space-y-3">
        @foreach([
            ['code' => 'GOR-2026-091', 'name' => 'Rizky Pratama', 'wa' => '0813-9988-7766', 'court' => 'Lapangan B • Kam, 19 Nov (16:00-18:00)', 'status' => 'MENUNGGU_VERIFIKASI', 'sisa' => 0],
            ['code' => 'GOR-2026-089', 'name' => 'Fajar Apriliansyah', 'wa' => '0812-3456-7890', 'court' => 'Lapangan A • Sel, 17 Nov (19:00-21:00)', 'status' => 'DP_50', 'sisa' => 180000],
        ] as $trx)
            <div class="wf-box rounded-2xl p-5 flex flex-col lg:flex-row lg:items-center justify-between gap-4">
                <div class="space-y-1">
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="px-2.5 py-0.5 rounded bg-ink text-paper font-mono text-xs font-bold">{{ $trx['code'] }}</span>
                        <x-status-badge :status="$trx['status']" />
                    </div>
                    <div class="font-extrabold text-base text-ink pt-1">{{ $trx['name'] }} <span class="font-mono text-xs font-normal text-ink/70">({{ $trx['wa'] }})</span></div>
                    <div class="font-mono text-xs text-ink/75">{{ $trx['court'] }}</div>
                </div>

                <div class="flex flex-wrap items-center gap-2 text-xs font-bold">
                    <button @click="selectedCode = '{{ $trx['code'] }}'; activeModal = 'detail'" class="px-3 py-2 rounded-xl border border-ink/30 bg-cream/60 hover:bg-cream">
                        👤 Detail (07B)
                    </button>

                    @if($trx['status'] === 'MENUNGGU_VERIFIKASI')
                        <button @click="selectedCode = '{{ $trx['code'] }}'; activeModal = 'verifikasi'" class="px-3.5 py-2 rounded-xl bg-sky-800 text-white wf-btn">
                            🔍 Verifikasi QRIS (07C)
                        </button>
                    @endif

                    @if($trx['status'] === 'DP_50')
                        <button @click="selectedCode = '{{ $trx['code'] }}'; activeModal = 'lunasi'" class="px-3.5 py-2 rounded-xl bg-emerald-800 text-white wf-btn">
                            💵 Lunasi Sisa Rp {{ number_format($trx['sisa'], 0, ',', '.') }} (07D)
                        </button>
                    @endif

                    <button @click="selectedCode = '{{ $trx['code'] }}'; activeModal = 'reschedule'" class="px-3 py-2 rounded-xl border border-ink/30 bg-paper hover:bg-cream">
                        🔄 Reschedule (07F)
                    </button>

                    <a href="{{ route('admin.kasir.nota-wa', $trx['code']) }}" class="px-3 py-2 rounded-xl bg-emerald-700 text-white">
                        💬 Nota WA (07H)
                    </a>
                </div>
            </div>
        @endforeach
    </div>

    <!-- Modal Dinamis untuk Layar 07B - 07G -->
    <div x-show="activeModal !== null" x-cloak class="fixed inset-0 z-50 bg-ink/60 backdrop-blur-xs flex items-center justify-center p-4">
        <div @click.away="activeModal = null" class="wf-box rounded-2xl max-w-lg w-full p-6 space-y-4">
            <div class="flex items-center justify-between border-b border-ink/20 pb-3">
                <h2 class="text-lg font-extrabold text-ink" x-text="{
                    detail: '07B • Detail Pelanggan (' + selectedCode + ')',
                    verifikasi: '07C • Verifikasi Bukti Transfer QRIS (' + selectedCode + ')',
                    lunasi: '07D • Pelunasan Sisa DP di Kasir (' + selectedCode + ')',
                    walkin: '07E • Input Booking Walk-In (Datang Langsung)',
                    reschedule: '07F • Reschedule Jadwal (' + selectedCode + ')',
                    maintenance: '07G • Blokir Slot Maintenance Per Jam'
                }[activeModal]"></h2>
                <button @click="activeModal = null" class="font-bold text-sm px-2 py-1 rounded-lg hover:bg-cream">✕</button>
            </div>

            <!-- Konten Modal Verifikasi QRIS (07C) -->
            <div x-show="activeModal === 'verifikasi'" class="space-y-3 text-xs">
                <div class="p-4 rounded-xl wf-hatch border border-ink/25 text-center font-mono">
                    [Preview Bukti Transfer QRIS Pelanggan — Nominal Rp 260.000]
                </div>
                <div class="flex gap-2">
                    <button @click="activeModal = null" class="flex-1 py-2.5 rounded-xl bg-emerald-800 text-white font-extrabold">✅ Terima & Validasi</button>
                    <button @click="activeModal = null" class="flex-1 py-2.5 rounded-xl bg-rose-800 text-white font-extrabold">✕ Tolak Bukti</button>
                </div>
            </div>

            <!-- Konten Modal Pelunasan DP (07D) -->
            <div x-show="activeModal === 'lunasi'" class="space-y-3 text-xs">
                <p>Konfirmasi pelunasan sisa tagihan sebesar <strong>Rp 180.000</strong> untuk kode <strong x-text="selectedCode"></strong>?</p>
                <select class="w-full rounded-xl border-ink/30 bg-paper p-2.5">
                    <option>Metode Pelunasan: Tunai di Kasir</option>
                    <option>Metode Pelunasan: QRIS Kasir GOR</option>
                </select>
                <button @click="activeModal = null" class="w-full py-2.5 rounded-xl bg-emerald-800 text-white font-extrabold">💵 Tandai LUNAS Sekarang</button>
            </div>

            <!-- Konten Modal Walk-In (07E) -->
            <div x-show="activeModal === 'walkin'" class="space-y-3 text-xs">
                <input type="text" placeholder="Nama Pelanggan Walk-In" class="w-full rounded-xl border-ink/30 bg-paper p-2.5">
                <div class="grid grid-cols-2 gap-2">
                    <select class="rounded-xl border-ink/30 bg-paper p-2.5">
                        <option>Lapangan A</option><option>Lapangan B</option><option>Lapangan C</option>
                    </select>
                    <input type="text" value="14:00 - 15:00" class="rounded-xl border-ink/30 bg-paper p-2.5 font-mono">
                </div>
                <button @click="activeModal = null" class="w-full py-2.5 rounded-xl bg-accent text-white font-extrabold">Simpan Transaksi Walk-In</button>
            </div>

            <!-- Konten Modal Reschedule (07F) -->
            <div x-show="activeModal === 'reschedule'" class="space-y-3 text-xs">
                <input type="date" value="2026-11-20" class="w-full rounded-xl border-ink/30 bg-paper p-2.5 font-mono">
                <input type="text" value="20:00 - 22:00" class="w-full rounded-xl border-ink/30 bg-paper p-2.5 font-mono">
                <button @click="activeModal = null" class="w-full py-2.5 rounded-xl bg-ink text-paper font-extrabold">Simpan Perubahan Jadwal</button>
            </div>

            <!-- Konten Modal Blokir Maintenance (07G) -->
            <div x-show="activeModal === 'maintenance'" class="space-y-3 text-xs">
                <select class="w-full rounded-xl border-ink/30 bg-paper p-2.5">
                    <option>Lapangan A (Sintetis Pro)</option><option>Lapangan B (Sintetis Arena)</option><option>Lapangan C (Interlock Match)</option>
                </select>
                <input type="text" placeholder="Jam Blokir (Misal: 12:00 - 14:00)" class="w-full rounded-xl border-ink/30 bg-paper p-2.5 font-mono">
                <input type="text" placeholder="Keterangan (Penyisiran rumput / perbaikan jaring)" class="w-full rounded-xl border-ink/30 bg-paper p-2.5">
                <button @click="activeModal = null" class="w-full py-2.5 rounded-xl bg-maint border border-ink font-extrabold">🛠️ Kunci Slot Maintenance</button>
            </div>

            <!-- Konten Modal Detail Pelanggan (07B) -->
            <div x-show="activeModal === 'detail'" class="space-y-2 text-xs">
                <div class="p-3 rounded-xl bg-cream/60 font-mono">
                    Kode: <strong x-text="selectedCode"></strong><br>
                    Riwayat Booking di GOR: 4x Bermain (Pelanggan Aktif)
                </div>
                <button @click="activeModal = null" class="w-full py-2 rounded-xl border border-ink font-bold">Tutup</button>
            </div>
        </div>
    </div>
</div>
@endsection
