@php
    $isOnPaymentOrInvoice = request()->routeIs('booking.show') || request()->routeIs('booking.invoice');
    $serverBannerData = null;

    if (! $isOnPaymentOrInvoice) {
        $activeCode = session('active_pending_booking_code');
        if ($activeCode) {
            $candidate = \App\Models\Booking::with('items.field')
                ->where('booking_code', $activeCode)
                ->first();

            $stillActive = $candidate && (
                $candidate->status === 'rejected'
                || ($candidate->status === 'pending_payment' && (! $candidate->expired_at || $candidate->expired_at->isFuture()))
            );

            if ($stillActive) {
                $firstField = $candidate->items->first()?->field?->name ?? 'Lapangan Futsal';
                $payable = $candidate->payment_scheme === 'dp'
                    ? (int) round($candidate->total_amount * 0.5)
                    : (int) $candidate->total_amount;
                $scheduleText = $candidate->items->map(function ($it) {
                    return \Carbon\Carbon::parse($it->booking_date)->translatedFormat('d M')
                        . ' (' . \Carbon\Carbon::parse($it->start_time)->format('H:i')
                        . '-' . \Carbon\Carbon::parse($it->end_time)->format('H:i') . ')';
                })->implode(', ');

                $serverBannerData = [
                    'code'              => $candidate->booking_code,
                    'status'            => $candidate->status,
                    'field_name'        => $firstField,
                    'schedule_text'     => $scheduleText,
                    'payable_formatted' => 'Rp ' . number_format($payable, 0, ',', '.'),
                    'scheme_label'      => $candidate->payment_scheme === 'dp' ? 'DP 50%' : 'Lunas 100%',
                    'expires_at'        => $candidate->expired_at ? $candidate->expired_at->format('H:i:s') . ' WIB' : null,
                    'expires_at_ms'     => $candidate->expired_at ? $candidate->expired_at->getTimestampMs() : null,
                    'show_url'          => route('booking.show', $candidate->booking_code),
                    'cancel_url'        => route('booking.cancel', $candidate->booking_code),
                ];
            } else {
                session()->forget('active_pending_booking_code');
            }
        }
    }
@endphp

@if(! $isOnPaymentOrInvoice)
    <div x-data="pendingBookingBanner({
            initialData: {{ Js::from($serverBannerData) }},
            checkUrl: '{{ route('api.activeBooking') }}'
         })"
         x-show="booking !== null"
         x-cloak
         class="bg-amber-100/95 border-b-2 border-amber-700/35 text-ink no-print">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-3">
            <template x-if="booking">
                <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-3">
                    <div class="flex items-start sm:items-center gap-3">
                        <span class="mt-0.5 sm:mt-0 inline-flex items-center justify-center w-8 h-8 rounded-xl bg-amber-300 border border-ink/20 text-ink font-extrabold text-xs shrink-0">
                            !
                        </span>
                        <div class="text-xs leading-relaxed">
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="font-extrabold uppercase tracking-wider text-amber-950"
                                      x-text="booking.status === 'rejected' ? 'Bukti Pembayaran Ditolak — Unggah Ulang' : 'Tagihan QRIS Belum Diselesaikan'"></span>
                                <span class="px-2 py-0.5 rounded bg-ink text-paper font-mono font-bold text-[11px]" x-text="booking.code"></span>
                                <span x-show="booking.expires_at && booking.status === 'pending_payment'"
                                      class="px-2 py-0.5 rounded-full bg-amber-200 border border-amber-800/30 text-[11px] font-bold text-amber-950"
                                      x-text="'Batas bayar: ' + booking.expires_at"></span>
                            </div>
                            <p class="text-ink/85 mt-0.5">
                                <strong x-text="booking.field_name"></strong>
                                <span x-show="booking.schedule_text" x-text="' • ' + booking.schedule_text"></span>
                                <span> • Tagihan <strong x-text="booking.scheme_label"></strong>: </span>
                                <strong class="text-accent" x-text="booking.payable_formatted"></strong>
                            </p>
                        </div>
                    </div>

                    <div class="flex flex-wrap items-center gap-2 shrink-0">
                        <a :href="booking.show_url"
                           class="wf-btn px-4 py-2 rounded-xl bg-accent hover:bg-accentDark text-paper text-xs font-bold">
                            Lanjutkan Pembayaran QRIS →
                        </a>

                        <form x-show="booking.status === 'pending_payment'"
                              :action="booking.cancel_url"
                              method="POST"
                              @submit="if (!confirm('Batalkan pesanan ' + booking.code + ' dan lepas kembali slot jadwalnya?')) { $event.preventDefault(); } else { localStorage.removeItem('gor_active_pending_booking'); }">
                            @csrf
                            <button type="submit"
                                    class="px-3.5 py-2 rounded-xl bg-paper hover:bg-rose-50 border border-rose-700/30 text-rose-800 text-xs font-bold transition">
                                Batalkan & Lepas Slot
                            </button>
                        </form>
                    </div>
                </div>
            </template>
        </div>
    </div>

    <script>
        function pendingBookingBanner({ initialData, checkUrl }) {
            return {
                booking: initialData,
                startExpiryWatcher() {
                    if (!this.booking || this.booking.status !== 'pending_payment' || !this.booking.expires_at_ms) return;
                    const timer = setInterval(() => {
                        if (!this.booking || Date.now() >= this.booking.expires_at_ms) {
                            this.booking = null;
                            try {
                                localStorage.removeItem('gor_active_pending_booking');
                            } catch (e) {}
                            clearInterval(timer);
                        }
                    }, 1000);
                },
                init() {
                    if (this.booking && this.booking.code) {
                        try {
                            localStorage.setItem('gor_active_pending_booking', this.booking.code);
                        } catch (e) {}
                        this.startExpiryWatcher();
                        return;
                    }

                    let storedCode = '';
                    try {
                        storedCode = localStorage.getItem('gor_active_pending_booking') || '';
                    } catch (e) {}

                    if (!storedCode) return;

                    fetch(checkUrl + '?booking_code=' + encodeURIComponent(storedCode), {
                        credentials: 'same-origin',
                        headers: { 'Accept': 'application/json' }
                    })
                    .then(res => res.ok ? res.json() : null)
                    .then(data => {
                        if (data && data.active && data.booking) {
                            this.booking = data.booking;
                            this.startExpiryWatcher();
                        } else {
                            try {
                                localStorage.removeItem('gor_active_pending_booking');
                            } catch (e) {}
                        }
                    })
                    .catch(() => {});
                }
            };
        }
    </script>
@endif
