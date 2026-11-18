@extends('layouts.app')

@section('title', 'Cek Status Pesanan — GOR Futsal Nusantara')

@section('content')
@php
    $statusLabels = [
        'pending_payment'      => ['label' => 'MENUNGGU PEMBAYARAN QRIS', 'class' => 'bg-amber-200 text-amber-950 border-amber-700/30'],
        'pending_verification' => ['label' => 'MENUNGGU VERIFIKASI KASIR', 'class' => 'bg-sky-200 text-sky-950 border-sky-700/30'],
        'dp_paid'              => ['label' => 'DP 50% TERVERIFIKASI', 'class' => 'bg-emerald-200 text-emerald-950 border-emerald-700/30'],
        'paid'                 => ['label' => 'LUNAS 100%', 'class' => 'bg-emerald-200 text-emerald-950 border-emerald-700/30'],
        'playing'              => ['label' => 'SEDANG BERTANDING', 'class' => 'bg-purple-200 text-purple-950 border-purple-700/30'],
        'expired'              => ['label' => 'KEDALUWARSA', 'class' => 'bg-rose-200 text-rose-950 border-rose-700/30'],
        'rejected'             => ['label' => 'BUKTI DITOLAK — UNGGAH ULANG', 'class' => 'bg-rose-200 text-rose-950 border-rose-700/30'],
        'cancelled'            => ['label' => 'DIBATALKAN', 'class' => 'bg-stone-300 text-stone-800 border-stone-500/30'],
    ];
@endphp

<div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 py-12 sm:py-14 space-y-6">
    <div class="wf-box rounded-3xl p-6 sm:p-8">
        <span class="block text-xs uppercase tracking-widest text-accent font-bold mb-1">
            LACAK RESERVASI & E-INVOICE
        </span>
        <h1 class="text-2xl sm:text-3xl font-bold text-ink mb-2">Cek Status Pesanan</h1>
        <p class="text-xs text-ink/75 mb-6 leading-relaxed">
            Masukkan <strong>Kode Booking</strong> (contoh: <span class="font-mono font-bold">GOR-8K29LP</span>) atau <strong>Nomor WhatsApp</strong> yang digunakan saat memesan (contoh: <span class="font-mono font-bold">0812xxxxxxxx</span>) untuk kembali ke halaman pembayaran QRIS, mengunggah bukti transfer, atau melihat E-Invoice.
        </p>

        <form action="{{ route('booking.track') }}" method="GET" class="space-y-4">
            <div>
                <label for="query" class="block text-xs font-bold text-ink mb-1.5">
                    Kode Booking atau Nomor WhatsApp <span class="text-rose-600">*</span>
                </label>
                <input type="text" id="query" name="query" required
                       value="{{ old('query', $searchQuery ?? request('query', request('booking_code'))) }}"
                       placeholder="Contoh: GOR-8K29LP atau 081234567890"
                       class="w-full rounded-xl border-ink/25 bg-paper text-sm font-semibold text-ink placeholder:text-ink/40 focus:border-accent focus:ring-accent">
            </div>

            <button type="submit" class="wf-btn w-full py-3 rounded-xl bg-accent hover:bg-accentDark text-paper text-xs sm:text-sm font-bold">
                Cari Pesanan Saya →
            </button>
        </form>
    </div>

    @if(isset($matchedBookings) && $matchedBookings->count() > 0)
        <div class="wf-box rounded-3xl p-6 sm:p-8 space-y-4">
            <div class="flex items-center justify-between gap-2 pb-3 border-b border-ink/15">
                <div>
                    <span class="block text-[11px] uppercase tracking-wider text-accent font-bold">HASIL PENCARIAN NOMOR WHATSAPP</span>
                    <h2 class="text-lg font-bold text-ink">Ditemukan {{ $matchedBookings->count() }} Riwayat Pesanan</h2>
                </div>
            </div>

            <div class="space-y-3">
                @foreach($matchedBookings as $itemBooking)
                    @php
                        $isExpiredPending = $itemBooking->status === 'pending_payment' && $itemBooking->expired_at && $itemBooking->expired_at->lte(now());
                        $effectiveStatus = $isExpiredPending ? 'expired' : $itemBooking->status;
                        $badge = $statusLabels[$effectiveStatus] ?? ['label' => strtoupper($effectiveStatus), 'class' => 'bg-cream text-ink border-ink/20'];
                        $fieldTitle = $itemBooking->items->first()?->field?->name ?? 'Lapangan Futsal';
                        $payableAmount = $itemBooking->payment_scheme === 'dp'
                            ? (int) round($itemBooking->total_amount * 0.5)
                            : (int) $itemBooking->total_amount;
                        $needsPayment = in_array($effectiveStatus, ['pending_payment', 'rejected'], true);
                    @endphp

                    <div class="p-4 rounded-2xl border {{ $needsPayment ? 'border-amber-700/40 bg-amber-50/70' : 'border-ink/15 bg-paper' }} flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                        <div class="space-y-1">
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="px-2.5 py-0.5 rounded bg-ink text-paper font-mono font-bold text-xs">
                                    {{ $itemBooking->booking_code }}
                                </span>
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold border {{ $badge['class'] }}">
                                    {{ $badge['label'] }}
                                </span>
                            </div>

                            <p class="text-sm font-bold text-ink pt-0.5">
                                {{ $fieldTitle }} — {{ $itemBooking->customer_name }}
                            </p>

                            <ul class="text-xs text-ink/75 space-y-0.5">
                                @foreach($itemBooking->items as $slotItem)
                                    <li>
                                        • {{ \Carbon\Carbon::parse($slotItem->booking_date)->translatedFormat('d M Y') }}
                                        ({{ \Carbon\Carbon::parse($slotItem->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($slotItem->end_time)->format('H:i') }} WIB)
                                    </li>
                                @endforeach
                            </ul>

                            <p class="text-xs text-ink/80 pt-0.5">
                                Tagihan {{ $itemBooking->payment_scheme === 'dp' ? 'DP 50%' : 'Lunas 100%' }}:
                                <strong class="text-accent">Rp {{ number_format($payableAmount, 0, ',', '.') }}</strong>
                            </p>
                        </div>

                        <div class="shrink-0">
                            <a href="{{ route('booking.show', $itemBooking->booking_code) }}"
                               class="wf-btn inline-flex items-center justify-center w-full sm:w-auto px-4 py-2.5 rounded-xl {{ $needsPayment ? 'bg-accent hover:bg-accentDark text-paper' : 'bg-ink hover:bg-ink/90 text-paper' }} text-xs font-bold">
                                {{ $needsPayment ? 'Lanjutkan Bayar QRIS →' : 'Lihat Detail & Invoice →' }}
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</div>
@endsection
