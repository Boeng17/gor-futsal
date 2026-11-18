@extends('layouts.app')

@section('title', 'Pembayaran QRIS & E-Invoice ' . $booking->booking_code)

@section('content')
@php
    $firstItem = $booking->items->first();
    $fieldName = $firstItem?->field?->name ?? 'Lapangan Futsal';
    $isFull = $booking->payment_scheme === 'full';
    $remainingAmount = max(0, $booking->total_amount - $amountToPay);
    $latestPayment = $booking->payments->first();
    $statusMap = [
        'pending_payment'      => ['label' => 'MENUNGGU PEMBAYARAN QRIS', 'class' => 'bg-amber-200 text-amber-950 border-amber-700/30'],
        'pending_verification' => ['label' => 'MENUNGGU VERIFIKASI KASIR', 'class' => 'bg-sky-200 text-sky-950 border-sky-700/30'],
        'dp_paid'              => ['label' => 'DP 50% TERVERIFIKASI (JADWAL TERKUNCI)', 'class' => 'bg-emerald-200 text-emerald-950 border-emerald-700/30'],
        'paid'                 => ['label' => 'LUNAS 100% (JADWAL TERKUNCI)', 'class' => 'bg-emerald-200 text-emerald-950 border-emerald-700/30'],
        'playing'              => ['label' => 'SEDANG BERTANDING', 'class' => 'bg-purple-200 text-purple-950 border-purple-700/30'],
        'expired'              => ['label' => 'KEDALUWARSA (EXPIRED)', 'class' => 'bg-rose-200 text-rose-950 border-rose-700/30'],
        'rejected'             => ['label' => 'BUKTI DITOLAK — UNGGAH ULANG', 'class' => 'bg-rose-200 text-rose-950 border-rose-700/30'],
        'cancelled'            => ['label' => 'PESANAN DIBATALKAN', 'class' => 'bg-stone-300 text-stone-800 border-stone-500/30'],
    ];
    $badge = $statusMap[$booking->status] ?? ['label' => strtoupper($booking->status), 'class' => 'bg-cream text-ink'];
@endphp

<div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-10 space-y-8">

    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-5 border-b border-ink/15">
        <div>
            <span class="block text-xs uppercase tracking-wider text-accent font-bold mb-1">
                LANGKAH 2 & 3 • KONFIRMASI TAGIHAN & QRIS OTOMATIS
            </span>
            <h1 class="text-3xl sm:text-4xl font-bold text-ink">
                Tagihan & Pembayaran QRIS
            </h1>
        </div>

        <div class="flex flex-col sm:items-end gap-1.5">
            <span class="px-4 py-1.5 rounded-xl bg-ink text-paper font-mono font-bold text-sm tracking-wider">
                {{ $booking->booking_code }}
            </span>
            <span class="px-3 py-1 rounded-full text-[11px] font-bold border {{ $badge['class'] }}">
                {{ $badge['label'] }}
            </span>
        </div>
    </div>

    <div class="wf-box rounded-2xl p-6 sm:p-8 space-y-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="p-4 rounded-xl bg-cream/70 border border-ink/15">
                <span class="block text-[11px] font-bold uppercase tracking-wider text-ink/65 mb-1">LAPANGAN & JADWAL DIPESAN</span>
                <p class="text-base font-extrabold text-ink mb-2">{{ $fieldName }}</p>
                <ul class="space-y-1 text-xs text-ink/85">
                    @foreach($booking->items as $item)
                        <li>
                            • {{ \Carbon\Carbon::parse($item->booking_date)->translatedFormat('d M Y') }}
                            ({{ \Carbon\Carbon::parse($item->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($item->end_time)->format('H:i') }} WIB)
                        </li>
                    @endforeach
                </ul>
            </div>

            <div class="p-4 rounded-xl bg-cream/70 border border-ink/15">
                <span class="block text-[11px] font-bold uppercase tracking-wider text-ink/65 mb-1">SKEMA PEMBAYARAN DIPILIH</span>
                <p class="text-lg font-extrabold text-accent">
                    @if($isFull)
                        Bayar Lunas 100% (Rp {{ number_format($amountToPay, 0, ',', '.') }})
                    @else
                        DP 50% (Rp {{ number_format($amountToPay, 0, ',', '.') }})
                    @endif
                </p>
                <p class="text-xs text-ink/80 mt-1">
                    @if($isFull)
                        Pembayaran penuh — tidak ada sisa tagihan di kasir GOR.
                    @else
                        Total sewa Rp {{ number_format($booking->total_amount, 0, ',', '.') }}. Sisa <strong>Rp {{ number_format($remainingAmount, 0, ',', '.') }}</strong> dibayar tunai/QRIS di kasir GOR sebelum bermain.
                    @endif
                </p>
                <p class="text-xs text-ink/70 mt-2">
                    Pemesan: <strong>{{ $booking->customer_name }}</strong> ({{ $booking->customer_phone }})
                </p>
            </div>
        </div>

        @if($booking->status === 'rejected' && $latestPayment?->rejection_reason)
            <div class="p-4 rounded-xl bg-rose-50 border border-rose-400 text-rose-950 text-xs">
                <strong>Catatan Penolakan Kasir:</strong> {{ $latestPayment->rejection_reason }}. Silakan unggah ulang bukti transfer yang valid di bawah ini.
            </div>
        @endif

        @if(in_array($booking->status, ['pending_payment', 'rejected']))
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 pt-2 items-start">

                
                <div class="lg:col-span-5 p-5 rounded-2xl border-2 border-ink/25 bg-paper text-center space-y-3">
                    @if($booking->expired_at && $booking->status === 'pending_payment')
                        <div class="space-y-1">
                            <div class="inline-block px-3.5 py-1 rounded-full bg-amber-100 border border-amber-700/30 text-amber-950 text-[11px] font-bold">
                                ⏳ BATAS BAYAR 5 MENIT: {{ $booking->expired_at->format('H:i:s') }} WIB
                            </div>
                            <div id="qris-countdown-box" class="text-[11px] font-extrabold text-rose-800"></div>
                        </div>
                    @endif

                    <div>
                        <p class="text-xs font-extrabold tracking-wide text-ink">{{ $qrisConfig['merchant_name'] }}</p>
                        <p class="text-[11px] font-bold text-emerald-800">QRIS DINAMIS • NOMINAL OTOMATIS</p>
                    </div>

                    <div class="p-4 rounded-xl bg-white border border-ink/15 flex flex-col items-center justify-center">
                        <div id="dynamic-qris-box" class="flex items-center justify-center"></div>
                        <span class="mt-2 text-[10px] font-semibold text-ink/60">
                            Scan dengan GoPay, OVO, DANA, ShopeePay, BCA, Mandiri, BRI, atau m-Banking lainnya
                        </span>
                    </div>

                    <div class="p-3 rounded-xl bg-cream border border-ink/15">
                        <span class="block text-[11px] font-bold uppercase text-ink/70">
                            NOMINAL OTOMATIS (<span class="font-mono">{{ $booking->booking_code }}</span>)
                        </span>
                        <span class="text-xl font-extrabold text-accent">
                            Rp {{ number_format($amountToPay, 0, ',', '.') }}
                        </span>
                    </div>
                </div>

                <div class="lg:col-span-7 space-y-5">
                    <div class="p-5 rounded-2xl bg-cream/60 border border-ink/15 space-y-2">
                        <span class="inline-block px-2.5 py-0.5 rounded bg-ink text-paper text-[11px] font-bold uppercase">
                            INSTRUKSI PEMBAYARAN
                        </span>
                        <h3 class="text-lg font-bold text-ink">Upload Bukti Transfer QRIS</h3>
                        <ol class="list-decimal list-inside text-xs text-ink/85 space-y-1">
                            <li>Scan kode QRIS dinamis di samping menggunakan m-Banking atau e-Wallet Anda (nominal <strong>Rp {{ number_format($amountToPay, 0, ',', '.') }}</strong> akan muncul otomatis).</li>
                            <li>Selesaikan pembayaran pada aplikasi bank / e-Wallet Anda.</li>
                            <li>Isi nama pengirim dan unggah screenshot bukti transfer pada form di bawah ini.</li>
                        </ol>
                    </div>

                    <form id="qris-proof-form"
                          action="{{ route('booking.uploadProof', $booking->booking_code) }}"
                          method="POST"
                          enctype="multipart/form-data"
                          class="p-5 rounded-2xl bg-paper border border-ink/20 space-y-4">
                        @csrf

                        <div>
                            <label for="sender_name" class="block text-xs font-bold text-ink mb-1.5">
                                Nama Pengirim di Rekening / E-Wallet <span class="text-rose-600">*</span>
                            </label>
                            <input type="text" id="sender_name" name="sender_name" required
                                   value="{{ old('sender_name') }}"
                                   placeholder="Wajib diisi"
                                   class="w-full rounded-xl border-ink/25 bg-cream/30 text-sm text-ink placeholder:text-ink/40 focus:border-accent focus:ring-accent">
                        </div>

                        <div>
                            <label for="proof_image" class="block text-xs font-bold text-ink mb-1.5">
                                File Foto / Screenshot Bukti Transfer (JPG/PNG/WEBP, Maks 2MB) <span class="text-rose-600">*</span>
                            </label>
                            <input type="file" id="proof_image" name="proof_image" required
                                   accept="image/jpeg,image/png,image/webp"
                                   class="w-full text-xs text-ink file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-ink file:text-paper hover:file:bg-ink/90 cursor-pointer border border-ink/20 rounded-xl p-1.5 bg-cream/30">
                        </div>

                        <div class="pt-2 flex flex-wrap items-center justify-between gap-3">
                            <button type="submit"
                                    class="wf-btn flex-1 py-3 px-5 rounded-xl bg-accent hover:bg-accentDark text-paper text-xs sm:text-sm font-bold">
                                ✓ Kirim Bukti Pembayaran Sekarang →
                            </button>
                        </div>
                    </form>

                    @if($booking->status === 'pending_payment')
                        <form id="qris-cancel-form" action="{{ route('booking.cancel', $booking->booking_code) }}" method="POST"
                              onsubmit="return confirm('Yakin ingin membatalkan pesanan ini? Slot jam akan dilepas kembali.')">
                            @csrf
                            <button type="submit" class="text-xs font-bold text-rose-700 hover:underline">
                                Batalkan Pesanan Ini
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        @else
            <div class="p-6 rounded-2xl bg-emerald-50/80 border border-emerald-700/25 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h3 class="text-base font-bold text-emerald-950">
                        @if($booking->status === 'pending_verification')
                            Bukti Pembayaran Sedang Diverifikasi Kasir
                        @elseif(in_array($booking->status, ['dp_paid', 'paid', 'playing']))
                            Jadwal Anda Sudah Terkunci Resmi!
                        @else
                            Status Pesanan: {{ strtoupper($booking->status) }}
                        @endif
                    </h3>
                    <p class="text-xs text-emerald-900 mt-1">
                        Simpan kode booking <strong class="font-mono">{{ $booking->booking_code }}</strong> atau cetak E-Invoice untuk ditunjukkan saat datang ke GOR.
                    </p>
                </div>

                <div class="flex flex-wrap gap-2.5 shrink-0">
                    <a href="{{ route('booking.invoice', $booking->booking_code) }}" target="_blank" rel="noopener noreferrer"
                       class="wf-btn px-4 py-2.5 rounded-xl bg-ink text-paper text-xs font-bold">
                        Cetak E-Invoice
                    </a>
                    <a href="{{ route('home') }}"
                       class="px-4 py-2.5 rounded-xl border border-ink/25 bg-paper text-ink text-xs font-bold">
                        Kembali ke Beranda
                    </a>
                </div>
            </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/qrcodejs@1.0.0/qrcode.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const container = document.getElementById('dynamic-qris-box');
    const payload = @json($dynamicQrisPayload);
    if (container && payload && window.QRCode) {
        new QRCode(container, {
            text: payload,
            width: 220,
            height: 220,
            colorDark: '#232B20',
            colorLight: '#FFFFFF',
            correctLevel: QRCode.CorrectLevel.M
        });
    }

    @php
        $jsBookingCode = $booking->booking_code;
        $jsIsUnpaidActive = in_array($booking->status, ['pending_payment', 'rejected'], true) && (! $booking->expired_at || $booking->expired_at->isFuture());
        $jsExpiresLabel = $booking->expired_at ? $booking->expired_at->format('H:i:s') . ' WIB' : '';
        $jsExpiresAtMs = ($booking->expired_at && $booking->status === 'pending_payment') ? $booking->expired_at->getTimestampMs() : null;
    @endphp
    const bookingCode = {{ Js::from($jsBookingCode) }};
    const isUnpaidActive = {{ Js::from($jsIsUnpaidActive) }};
    const expiresLabel = {{ Js::from($jsExpiresLabel) }};
    const expiresAtMs = {{ Js::from($jsExpiresAtMs) }};

    const countdownEl = document.getElementById('qris-countdown-box');
    if (countdownEl && expiresAtMs) {
        const updateCountdown = function () {
            const diff = expiresAtMs - Date.now();
            if (diff <= 0) {
                countdownEl.textContent = 'Waktu pembayaran 5 menit telah habis. Slot jadwal dilepas kembali.';
                try {
                    localStorage.removeItem('gor_active_pending_booking');
                } catch (e) {}
                return;
            }
            const totalSec = Math.floor(diff / 1000);
            const mins = String(Math.floor(totalSec / 60)).padStart(2, '0');
            const secs = String(totalSec % 60).padStart(2, '0');
            countdownEl.textContent = `Sisa waktu kunci jadwal: ${mins}:${secs}`;
        };
        updateCountdown();
        setInterval(updateCountdown, 1000);
    }

    try {
        if (isUnpaidActive) {
            localStorage.setItem('gor_active_pending_booking', bookingCode);
        } else {
            localStorage.removeItem('gor_active_pending_booking');
        }
    } catch (e) {}

    if (isUnpaidActive) {
        let isSafeNavigation = false;

        const proofForm = document.getElementById('qris-proof-form');
        if (proofForm) {
            proofForm.addEventListener('submit', function () {
                isSafeNavigation = true;
            });
        }

        const cancelForm = document.getElementById('qris-cancel-form');
        if (cancelForm) {
            cancelForm.addEventListener('submit', function () {
                isSafeNavigation = true;
                try {
                    localStorage.removeItem('gor_active_pending_booking');
                } catch (e) {}
            });
        }

        document.addEventListener('click', function (e) {
            const link = e.target.closest('a[href]');
            if (!link) return;

            const rawHref = link.getAttribute('href') || '';
            if (
                link.target === '_blank' ||
                rawHref === '' ||
                rawHref.startsWith('#') ||
                rawHref.startsWith('javascript:') ||
                link.hasAttribute('download') ||
                link.href === window.location.href
            ) {
                return;
            }

            const timeNote = expiresLabel ? ` hingga ${expiresLabel}` : '';
            const ok = window.confirm(
                `Pembayaran QRIS untuk pesanan ${bookingCode} belum selesai dan jadwal Anda sedang dikunci sementara${timeNote}.\n\nYakin ingin keluar dari halaman ini? (Anda tetap bisa kembali lewat banner di atas atau menu Cek Pesanan).`
            );

            if (!ok) {
                e.preventDefault();
                e.stopImmediatePropagation();
            } else {
                isSafeNavigation = true;
            }
        }, true);

        window.addEventListener('beforeunload', function (e) {
            if (isSafeNavigation) return;
            e.preventDefault();
            e.returnValue = '';
        });
    }
});
</script>
@endpush
