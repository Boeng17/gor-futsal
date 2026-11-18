@extends('layouts.internal')

@section('title', 'Laporan Keuangan — GOR Futsal Nusantara')

@section('content')
<div x-data="{ detailModal: null }">

    
    <div class="flex flex-col lg:flex-row lg:items-end justify-between gap-4 mb-6">
        <div>
            <span class="block text-xs font-bold uppercase tracking-wider text-accent">
                {{ $isKasir ? 'REKAP FINANSIAL HARI INI (TERKUNCI)' : 'AUDIT & LAPORAN KEUANGAN' }}
            </span>
            <h1 class="text-2xl sm:text-3xl font-bold text-ink mt-0.5">
                Laporan Keuangan & Status Transaksi
            </h1>
        </div>

        <div class="flex flex-wrap items-center gap-2.5">
            <a href="{{ route($reportRoute ?? 'admin.reports.index', array_merge(request()->query(), ['export' => 'csv'])) }}"
               class="wf-btn inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-ink hover:bg-ink/90 text-paper text-xs font-bold">
                <span>↓ Export Excel (CSV)</span>
            </a>
        </div>
    </div>

    
    <div class="wf-box rounded-2xl p-5 mb-6">
        <form method="GET" action="{{ route($reportRoute ?? 'admin.reports.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-3 items-end text-xs">
            <div class="lg:col-span-3">
                <label class="block font-bold text-ink mb-1">Filter Status Transaksi</label>
                <select name="status" onchange="this.form.submit()"
                        class="w-full rounded-xl border-ink/25 bg-paper text-xs font-semibold text-ink focus:border-accent focus:ring-accent">
                    <option value="all" {{ $statusFilter === 'all' ? 'selected' : '' }}>Semua Status ({{ $totalTransactions }})</option>
                    <option value="pending_verification" {{ $statusFilter === 'pending_verification' ? 'selected' : '' }}>Verifikasi QRIS (Pending)</option>
                    <option value="pending_payment" {{ $statusFilter === 'pending_payment' ? 'selected' : '' }}>Menunggu Pembayaran</option>
                    <option value="on_going" {{ $statusFilter === 'on_going' ? 'selected' : '' }}>On Going (Jadwal Terkunci)</option>
                    <option value="dp_paid" {{ $statusFilter === 'dp_paid' ? 'selected' : '' }}>Pelunasan Pending (DP 50%)</option>
                    <option value="playing" {{ $statusFilter === 'playing' ? 'selected' : '' }}>Sedang Main</option>
                    <option value="paid" {{ $statusFilter === 'paid' ? 'selected' : '' }}>Lunas 100%</option>
                    <option value="expired" {{ $statusFilter === 'expired' ? 'selected' : '' }}>Expired</option>
                    <option value="cancelled_rejected" {{ $statusFilter === 'cancelled_rejected' ? 'selected' : '' }}>Dibatalkan / Ditolak</option>
                </select>
            </div>

            <div class="lg:col-span-3">
                <label class="block font-bold text-ink mb-1">Cari Kode / Pemesan</label>
                <input type="text" name="search" value="{{ $search }}" placeholder="Kode GOR-..., nama, atau HP..."
                       class="w-full rounded-xl border-ink/25 bg-paper text-xs text-ink focus:border-accent focus:ring-accent">
            </div>

            @if($isKasir)
                <div class="lg:col-span-4">
                    <label class="block font-bold text-ink mb-1">Tanggal Laporan (Akses Kasir)</label>
                    <div class="px-3.5 py-2.5 rounded-xl bg-amber-100/80 border border-amber-700/30 text-amber-950 font-semibold">
                        Khusus Hari Ini: {{ now()->translatedFormat('d F Y') }}
                    </div>
                </div>
            @else
                <div class="lg:col-span-2">
                    <label class="block font-bold text-ink mb-1">Dari Tanggal</label>
                    <input type="date" name="start_date" value="{{ $startDate }}"
                           class="w-full rounded-xl border-ink/25 bg-paper text-xs text-ink">
                </div>
                <div class="lg:col-span-2">
                    <label class="block font-bold text-ink mb-1">Sampai Tanggal</label>
                    <input type="date" name="end_date" value="{{ $endDate }}"
                           class="w-full rounded-xl border-ink/25 bg-paper text-xs text-ink">
                </div>
            @endif

            <div class="lg:col-span-2 flex gap-2">
                <button type="submit" class="wf-btn flex-1 py-2.5 px-4 rounded-xl bg-accent hover:bg-accentDark text-paper font-bold">
                    Terapkan
                </button>
                @if(! $isKasir)
                    <a href="{{ route($reportRoute ?? 'admin.reports.index', ['preset' => 'all', 'status' => $statusFilter]) }}"
                       title="Tampilkan Semua Data di Database"
                       class="py-2.5 px-3 rounded-xl bg-cream hover:bg-sand border border-ink/20 text-ink font-bold">
                        Semua
                    </a>
                @endif
            </div>
        </form>
    </div>

    
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="wf-box rounded-2xl p-5">
            <span class="text-xs font-semibold text-ink/70 block">Pemasukan Terverifikasi</span>
            <span class="text-xl font-extrabold text-emerald-800 mt-1 block">Rp {{ number_format($totalRevenue, 0, ',', '.') }}</span>
            <span class="text-[11px] text-ink/60 mt-1 block">QRIS: Rp {{ number_format($qrisRevenue, 0, ',', '.') }} • Tunai: Rp {{ number_format($cashRevenue, 0, ',', '.') }}</span>
        </div>

        <div class="wf-box rounded-2xl p-5">
            <span class="text-xs font-semibold text-ink/70 block">Sisa Tagihan / Belum Terverifikasi</span>
            <span class="text-xl font-extrabold text-amber-800 mt-1 block">Rp {{ number_format($pendingReceivable, 0, ',', '.') }}</span>
            <span class="text-[11px] text-ink/60 mt-1 block">Sisa DP 50% & antrean QRIS</span>
        </div>

        <div class="wf-box rounded-2xl p-5">
            <span class="text-xs font-semibold text-ink/70 block">Total Data Booking</span>
            <span class="text-xl font-extrabold text-ink mt-1 block">{{ $totalTransactions }} Pesanan</span>
            <span class="text-[11px] text-ink/60 mt-1 block">Sinkron langsung dari tabel bookings</span>
        </div>

        <div class="wf-box rounded-2xl p-5">
            <span class="text-xs font-semibold text-ink/70 block">Zona Waktu Pencatatan</span>
            <span class="text-xl font-extrabold text-accent mt-1 block">WIB (Asia/Jakarta)</span>
            <span class="text-[11px] text-ink/60 mt-1 block">{{ now()->format('d M Y, H:i') }} WIB</span>
        </div>
    </div>

    
    <div class="wf-box rounded-2xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-xs text-left border-collapse">
                <thead>
                    <tr class="bg-sand/65 border-b border-ink/15 text-ink font-bold">
                        <th class="py-3.5 px-4">Kode</th>
                        <th class="py-3.5 px-4">Pemesan & Lapangan</th>
                        <th class="py-3.5 px-4">Jadwal Main</th>
                        <th class="py-3.5 px-4 text-right">Terbayar / Sisa</th>
                        <th class="py-3.5 px-4">Status</th>
                        <th class="py-3.5 px-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-ink/10 bg-paper">
                    @forelse($bookings as $b)
                        @php
                            $firstField = $b->items->first()?->field?->name ?? 'Lapangan Futsal';
                            $detailPayload = [
                                'code'      => $b->booking_code,
                                'customer'  => $b->customer_name,
                                'phone'     => $b->customer_phone,
                                'created'   => $b->created_at->translatedFormat('d M Y, H:i') . ' WIB',
                                'status'    => $b->display['label'],
                                'total'     => number_format($b->total_amount, 0, ',', '.'),
                                'paid'      => number_format($b->display['paid'], 0, ',', '.'),
                                'remaining' => number_format($b->display['remaining'], 0, ',', '.'),
                                'items'     => $b->items->map(fn ($it) => [
                                    'field' => $it->field?->name ?? 'Lapangan',
                                    'date'  => \Carbon\Carbon::parse($it->booking_date)->translatedFormat('l, d M Y'),
                                    'time'  => \Carbon\Carbon::parse($it->start_time)->format('H:i') . ' - ' . \Carbon\Carbon::parse($it->end_time)->format('H:i'),
                                ])->values(),
                                'payments'  => $b->payments->map(fn ($p) => [
                                    'stage'    => strtoupper($p->payment_stage),
                                    'method'   => strtoupper($p->method),
                                    'amount'   => number_format($p->amount, 0, ',', '.'),
                                    'status'   => strtoupper($p->status),
                                    'verifier' => $p->verifier?->name ?? '-',
                                    'time'     => $p->created_at->format('d/m/Y H:i'),
                                ])->values(),
                            ];
                        @endphp
                        <tr class="hover:bg-cream/40 transition align-top">
                            <td class="py-4 px-4">
                                <a href="{{ route('booking.invoice', $b->booking_code) }}" target="_blank" rel="noopener noreferrer"
                                   class="font-mono font-bold text-ink underline hover:text-accent">
                                    {{ $b->booking_code }}
                                </a>
                            </td>

                            <td class="py-4 px-4">
                                <div class="font-bold text-ink text-sm">{{ $b->customer_name }}</div>
                                <div class="text-ink/70 mt-0.5">{{ $firstField }} • {{ $b->customer_phone }}</div>
                            </td>

                            <td class="py-4 px-4 space-y-1">
                                @forelse($b->items as $item)
                                    <div class="text-ink/85">
                                        {{ \Carbon\Carbon::parse($item->booking_date)->translatedFormat('D, d M') }}
                                        ({{ \Carbon\Carbon::parse($item->start_time)->format('H:i') }}-{{ \Carbon\Carbon::parse($item->end_time)->format('H:i') }})
                                    </div>
                                @empty
                                    <span class="text-ink/50">-</span>
                                @endforelse
                            </td>

                            <td class="py-4 px-4 text-right">
                                <div class="font-extrabold text-ink">Rp {{ number_format($b->display['paid'], 0, ',', '.') }}</div>
                                <div class="text-[11px] text-accent font-semibold mt-0.5">
                                    Sisa: Rp {{ number_format($b->display['remaining'], 0, ',', '.') }}
                                </div>
                            </td>

                            <td class="py-4 px-4">
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-[11px] font-bold border {{ $b->display['badge_class'] }}">
                                    {{ $b->display['label'] }}
                                </span>
                            </td>

                            <td class="py-4 px-4 text-right">
                                <div class="inline-flex flex-wrap justify-end gap-1.5">
                                    <button type="button"
                                            @click='detailModal = @json($detailPayload)'
                                            class="px-3 py-1.5 rounded-full bg-cream hover:bg-sand border border-ink/20 text-ink font-bold transition">
                                        Detail
                                    </button>
                                    <a href="{{ route('booking.invoice', $b->booking_code) }}" target="_blank" rel="noopener noreferrer"
                                       class="px-3 py-1.5 rounded-full bg-ink hover:bg-ink/90 text-paper font-bold transition">
                                        E-Invoice
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-12 px-4 text-center text-ink/65">
                                Tidak ada data transaksi yang sesuai dengan filter status atau tanggal saat ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    
    <div x-show="detailModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50">
        <div @click.away="detailModal = null" class="wf-box rounded-2xl max-w-lg w-full p-6 space-y-4 max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between border-b border-ink/15 pb-3">
                <div>
                    <span class="text-[11px] font-bold uppercase text-accent">RINCIAN TRANSAKSI</span>
                    <h3 class="text-xl font-bold text-ink font-mono" x-text="detailModal?.code"></h3>
                </div>
                <button type="button" @click="detailModal = null" class="p-1.5 rounded-lg bg-cream text-ink font-bold">✕</button>
            </div>

            <template x-if="detailModal">
                <div class="space-y-4 text-xs">
                    <div class="p-3.5 rounded-xl bg-cream/70 border border-ink/15 space-y-1">
                        <div class="font-bold text-sm text-ink" x-text="detailModal.customer"></div>
                        <div class="text-ink/75">WhatsApp: <strong x-text="detailModal.phone"></strong></div>
                        <div class="text-ink/75">Waktu Input: <span x-text="detailModal.created"></span></div>
                        <div class="text-ink/75">Status: <strong x-text="detailModal.status"></strong></div>
                    </div>

                    <div>
                        <span class="block font-bold text-ink mb-1.5">Jadwal Lapangan:</span>
                        <div class="space-y-1">
                            <template x-for="(it, i) in detailModal.items" :key="i">
                                <div class="px-3 py-2 rounded-lg bg-paper border border-ink/15 flex justify-between">
                                    <span class="font-semibold" x-text="it.field"></span>
                                    <span x-text="it.date + ' • ' + it.time"></span>
                                </div>
                            </template>
                        </div>
                    </div>

                    <div>
                        <span class="block font-bold text-ink mb-1.5">Riwayat Pembayaran:</span>
                        <template x-if="detailModal.payments.length === 0">
                            <p class="text-ink/60 italic">Belum ada pembayaran tercatat.</p>
                        </template>
                        <div class="space-y-1.5">
                            <template x-for="(pay, idx) in detailModal.payments" :key="idx">
                                <div class="px-3 py-2 rounded-lg bg-paper border border-ink/15 flex items-center justify-between">
                                    <div>
                                        <span class="font-bold" x-text="pay.stage + ' (' + pay.method + ')'"></span>
                                        <span class="block text-[11px] text-ink/65" x-text="pay.time + ' • Verifikator: ' + pay.verifier"></span>
                                    </div>
                                    <div class="text-right">
                                        <span class="font-extrabold block" x-text="'Rp ' + pay.amount"></span>
                                        <span class="text-[10px] font-bold text-accent" x-text="pay.status"></span>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>

                    <div class="p-3.5 rounded-xl bg-cream border border-ink/20 flex justify-between font-bold">
                        <span>Terbayar: Rp <span x-text="detailModal.paid"></span></span>
                        <span class="text-accent">Sisa: Rp <span x-text="detailModal.remaining"></span></span>
                    </div>
                </div>
            </template>
        </div>
    </div>

</div>
@endsection
