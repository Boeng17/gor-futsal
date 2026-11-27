@extends('layouts.internal')

@section('title', 'Kasir & Verified Payment — GOR Futsal Nusantara')

@section('content')
@php
    $walkInFieldsJson = $activeFields->map(fn ($f) => [
        'id'             => $f->id,
        'name'           => $f->name,
        'price_per_hour' => (int) $f->price_per_hour,
        'open_hour'      => $f->open_hour_value,
        'close_hour'     => $f->close_hour_value,
        'status'         => $f->status,
    ])->values();
    $defaultWalkInFieldId = (int) old('field_id', $activeFields->firstWhere('status', 'active')?->id ?? $activeFields->first()?->id ?? 0);
    $defaultWalkInDate = old('booking_date', date('Y-m-d'));
@endphp

<div x-data="kasirApp({
    fields: {{ Js::from($walkInFieldsJson) }},
    initialFieldId: {{ $defaultWalkInFieldId }},
    initialDate: '{{ $defaultWalkInDate }}',
    initialModalOpen: {{ old('_modal') === 'walk_in' ? 'true' : 'false' }},
    initialSelectedHours: {{ Js::from(old('hours', [])) }},
    initialScheme: '{{ old('payment_scheme', 'full') }}',
    initialMethod: '{{ old('method', 'cash') }}',
    initialCustomerName: {{ Js::from(old('customer_name', '')) }},
    initialCustomerPhone: {{ Js::from(old('customer_phone', '')) }}
})">

    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 mb-6">
        <div>
            <span class="block text-xs font-bold uppercase tracking-wider text-accent">
                FRONT-DESK KASIR GOR
            </span>
            <h1 class="text-2xl sm:text-3xl font-bold text-ink mt-0.5">
                Operasional Kasir, Verifikasi QRIS & Pelunasan DP
            </h1>
        </div>

        <div class="flex flex-wrap items-center gap-2.5">
            <button type="button" @click="openWalkInModal()"
                    class="wf-btn px-4 py-2.5 rounded-full bg-ink hover:bg-ink/90 text-paper text-xs font-bold">
                + Walk-In Booking
            </button>
            <button type="button" @click="maintModal = true"
                    class="wf-btn px-4 py-2.5 rounded-full bg-maint hover:bg-amber-300 text-ink border border-ink/20 text-xs font-bold">
                Blokir Jam Maintenance
            </button>
        </div>
    </div>

    <div class="wf-box rounded-2xl p-4 mb-6">
        <form method="GET" action="{{ route(($routePrefix ?? 'kasir') === 'admin' ? 'admin.operasional.index' : 'kasir.dashboard') }}" class="flex flex-wrap items-center gap-3 text-xs">
            <input type="text" name="search" value="{{ $search }}" placeholder="Cari kode booking, nama pemesan, atau nomor WA..."
                   class="flex-1 min-w-[220px] rounded-xl border-ink/25 bg-paper text-xs text-ink">
            <select name="status" onchange="this.form.submit()" class="rounded-xl border-ink/25 bg-paper text-xs font-semibold text-ink">
                <option value="all" {{ $statusFilter === 'all' ? 'selected' : '' }}>Semua Status Operasional</option>
                <option value="pending_verification" {{ $statusFilter === 'pending_verification' ? 'selected' : '' }}>Verifikasi QRIS</option>
                <option value="on_going" {{ $statusFilter === 'on_going' ? 'selected' : '' }}>On Going (Lunas)</option>
                <option value="dp_paid" {{ $statusFilter === 'dp_paid' ? 'selected' : '' }}>Pelunasan Pending (DP)</option>
                <option value="playing" {{ $statusFilter === 'playing' ? 'selected' : '' }}>Sedang Main</option>
                <option value="expired" {{ $statusFilter === 'expired' ? 'selected' : '' }}>Expired</option>
            </select>
            <button type="submit" class="px-4 py-2 rounded-xl bg-accent text-paper font-bold">Cari</button>
            @if($search !== '' || $statusFilter !== 'all')
                <a href="{{ route(($routePrefix ?? 'kasir') === 'admin' ? 'admin.operasional.index' : 'kasir.dashboard') }}" class="px-3 py-2 rounded-xl bg-cream border border-ink/20 font-bold text-ink">Reset</a>
            @endif
        </form>
    </div>

    <div class="wf-box rounded-2xl overflow-hidden mb-6">
        <div class="overflow-x-auto">
            <table class="w-full text-xs text-left border-collapse">
                <thead>
                    <tr class="bg-sand/65 border-b border-ink/15 text-ink font-bold">
                        <th class="py-3.5 px-4">Kode</th>
                        <th class="py-3.5 px-4">Pemesan & Lapangan</th>
                        <th class="py-3.5 px-4">Jadwal Main</th>
                        <th class="py-3.5 px-4 text-right">Terbayar / Sisa</th>
                        <th class="py-3.5 px-4">Status</th>
                        <th class="py-3.5 px-4 text-right">Aksi Kasir</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-ink/10 bg-paper">
                    @forelse($bookings as $b)
                        @php
                            $firstItem = $b->items->first();
                            $firstField = $firstItem?->field?->name ?? 'Lapangan Futsal';
                            $pendingPay = $b->payments->firstWhere('status', 'pending') ?? $b->payments->first();
                            $waDigits = preg_replace('/[^0-9]/', '', $b->customer_phone);
                            if (str_starts_with($waDigits, '0')) {
                                $waDigits = '62' . substr($waDigits, 1);
                            }

                            $scheduleText = $b->items->map(fn ($it) =>
                                ($it->field?->name ?? 'Lapangan') . ' • ' .
                                \Carbon\Carbon::parse($it->booking_date)->translatedFormat('d M Y') . ' (' .
                                \Carbon\Carbon::parse($it->start_time)->format('H:i') . '-' .
                                \Carbon\Carbon::parse($it->end_time)->format('H:i') . ')'
                            )->implode("
");

                            $notaText = "*NOTA RESERVASI GOR FUTSAL NUSANTARA*
" .
                                "Kode Booking: *{$b->booking_code}*
" .
                                "Pemesan: {$b->customer_name}
" .
                                "Jadwal:
{$scheduleText}
" .
                                "Total Biaya: Rp " . number_format($b->total_amount, 0, ',', '.') . "
" .
                                "Sudah Dibayar: Rp " . number_format($b->display['paid'], 0, ',', '.') . "
" .
                                "Sisa Tagihan: Rp " . number_format($b->display['remaining'], 0, ',', '.') . "
" .
                                "Status: *{$b->display['label']}*
" .
                                "Cek E-Invoice: " . route('booking.invoice', $b->booking_code);

                            $detailJson = [
                                'code'      => $b->booking_code,
                                'customer'  => $b->customer_name,
                                'phone'     => $b->customer_phone,
                                'status'    => $b->display['label'],
                                'total'     => number_format($b->total_amount, 0, ',', '.'),
                                'paid'      => number_format($b->display['paid'], 0, ',', '.'),
                                'remaining' => number_format($b->display['remaining'], 0, ',', '.'),
                                'items'     => $b->items->map(fn ($it) => [
                                    'field' => $it->field?->name ?? 'Lapangan',
                                    'date'  => \Carbon\Carbon::parse($it->booking_date)->translatedFormat('l, d M Y'),
                                    'time'  => \Carbon\Carbon::parse($it->start_time)->format('H:i') . ' - ' . \Carbon\Carbon::parse($it->end_time)->format('H:i'),
                                ])->values(),
                            ];

                            $verifyJson = $pendingPay ? [
                                'id'         => $pendingPay->id,
                                'code'       => $b->booking_code,
                                'customer'   => $b->customer_name,
                                'stage'      => strtoupper($pendingPay->payment_stage),
                                'amount'     => number_format($pendingPay->amount, 0, ',', '.'),
                                'status'     => $pendingPay->status,
                                'proof_url'  => \App\Helpers\ImageHelper::resolveUrl($banner->image, 'banner', $banner->id),
                                'verify_url' => route(($routePrefix ?? 'kasir') . '.payments.verify', $pendingPay),
                                'reject_url' => route(($routePrefix ?? 'kasir') . '.payments.reject', $pendingPay),
                            ] : null;

                            $settleJson = [
                                'code'          => $b->booking_code,
                                'customer'      => $b->customer_name,
                                'total'         => number_format($b->total_amount, 0, ',', '.'),
                                'paid'          => number_format($b->display['paid'], 0, ',', '.'),
                                'remaining'     => $b->display['remaining'],
                                'remaining_fmt' => number_format($b->display['remaining'], 0, ',', '.'),
                                'settle_url'    => route(($routePrefix ?? 'kasir') . '.bookings.settle', $b),
                            ];

                            $rescheduleJson = [
                                'code'           => $b->booking_code,
                                'customer'       => $b->customer_name,
                                'field_id'       => $firstItem?->field_id ?? ($activeFields->first()?->id ?? 1),
                                'booking_date'   => $firstItem ? \Carbon\Carbon::parse($firstItem->booking_date)->toDateString() : date('Y-m-d'),
                                'start_hour'     => $firstItem ? \Carbon\Carbon::parse($firstItem->start_time)->format('H:i') : '18:00',
                                'slot_count'     => max(1, $b->items->count()),
                                'reschedule_url' => route(($routePrefix ?? 'kasir') . '.bookings.reschedule', $b),
                            ];

                            $notaJson = [
                                'code'        => $b->booking_code,
                                'customer'    => $b->customer_name,
                                'text'        => $notaText,
                                'wa_url'      => "https://wa.me/{$waDigits}?text=" . rawurlencode($notaText),
                                'invoice_url' => route('booking.invoice', $b->booking_code),
                            ];

                            $latestSlotEnd = $b->items->map(function ($it) {
                                $dStr = \Carbon\Carbon::parse($it->booking_date)->toDateString();
                                $eStr = \Carbon\Carbon::parse($it->end_time)->format('H:i:s');
                                return $eStr === '00:00:00'
                                    ? \Carbon\Carbon::parse($dStr . ' 23:59:59')
                                    : \Carbon\Carbon::parse($dStr . ' ' . $eStr);
                            })->max();
                            $isMatchEnded = $latestSlotEnd && $latestSlotEnd->lte(now());
                            $canStartMatch = $isAdmin && ! $isMatchEnded && in_array($b->display['key'], ['on_going', 'dp_paid'], true);
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
                                <div class="flex flex-col items-end gap-1.5">
                                    <div class="flex flex-wrap justify-end gap-1.5">
                                        <button type="button" @click='detailData = @json($detailJson)'
                                                class="px-3 py-1 rounded-full bg-cream hover:bg-sand border border-ink/20 text-ink font-bold">
                                            Detail
                                        </button>

                                        @if($verifyJson)
                                            <button type="button" @click='verifyData = @json($verifyJson)'
                                                    class="px-3 py-1 rounded-full border font-bold {{ $b->status === 'pending_verification' ? 'bg-sky-200 hover:bg-sky-300 text-sky-950 border-sky-500 ring-2 ring-sky-400/40' : 'bg-sky-50 hover:bg-sky-100 text-sky-900 border-sky-300' }}">
                                                Verifikasi
                                            </button>
                                        @endif

                                        @if($b->display['remaining'] > 0 && ! in_array($b->display['key'], ['expired', 'cancelled'], true))
                                            <button type="button" @click='settleData = @json($settleJson)'
                                                    class="px-3 py-1 rounded-full bg-maint hover:bg-amber-300 border border-amber-700/30 text-ink font-bold">
                                                Edit / Lunasi
                                            </button>
                                        @endif
                                    </div>

                                    <div class="flex flex-wrap justify-end gap-1.5">
                                        @if($canStartMatch)
                                            <form action="{{ route(($routePrefix ?? 'kasir') . '.bookings.startMatch', $b) }}" method="POST"
                                                  onsubmit="return confirm('Tandai tim {{ $b->customer_name }} sudah hadir dan SEDANG MAIN?')">
                                                @csrf
                                                <button type="submit"
                                                        class="px-3 py-1 rounded-full bg-purple-700 hover:bg-purple-800 text-white font-bold">
                                                    ▶ Sedang Main
                                                </button>
                                            </form>
                                        @endif

                                        @if(! in_array($b->display['key'], ['expired', 'cancelled'], true))
                                            <button type="button" @click='rescheduleData = @json($rescheduleJson)'
                                                    class="px-3 py-1 rounded-full bg-paper hover:bg-cream border border-ink/20 text-ink font-bold">
                                                Reschedule
                                            </button>
                                        @endif

                                        <button type="button" @click='notaData = @json($notaJson)'
                                                class="px-3 py-1 rounded-full bg-emerald-700 hover:bg-emerald-800 text-white font-bold">
                                            Nota WA
                                        </button>

                                        @if($b->display['key'] === 'expired' && $b->status !== 'cancelled')
                                            <form action="{{ route(($routePrefix ?? 'kasir') . '.bookings.cancel', $b) }}" method="POST">
                                                @csrf
                                                <button type="submit"
                                                        class="px-3 py-1 rounded-full bg-rose-700 hover:bg-rose-800 text-white font-bold">
                                                    Lepas Slot
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-12 px-4 text-center text-ink/65">
                                Belum ada transaksi pada antrean operasional saat ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="wf-box rounded-2xl p-5 space-y-3">
        <div class="flex items-center justify-between">
            <h2 class="font-sans text-xs font-extrabold uppercase tracking-wider text-ink">
                BLOKIR MAINTENANCE PER JAM AKTIF ({{ $maintenanceBlocks->count() }})
            </h2>
            <button type="button" @click="maintModal = true" class="text-xs font-bold text-accent hover:underline">
                + Tambah Blokir Jam
            </button>
        </div>

        @forelse($maintenanceBlocks as $block)
            <div class="px-4 py-3 rounded-xl bg-cream/60 border border-ink/15 flex flex-wrap items-center justify-between gap-3 text-xs">
                <div class="text-ink">
                    <strong>{{ $block->field?->name }}</strong> •
                    {{ \Carbon\Carbon::parse($block->block_date)->format('Y-m-d') }}
                    Jam {{ \Carbon\Carbon::parse($block->start_time)->format('H:i') }} —
                    <span class="text-ink/80">{{ $block->reason }}</span>
                </div>
                <form action="{{ route(($routePrefix ?? 'kasir') . '.maintenance.destroy', $block) }}" method="POST"
                      onsubmit="return confirm('Hapus blokir maintenance ini dan buka kembali slotnya?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit"
                            class="px-3 py-1 rounded-lg bg-rose-100 hover:bg-rose-200 text-rose-800 border border-rose-300 font-bold">
                        Hapus
                    </button>
                </form>
            </div>
        @empty
            <p class="text-xs text-ink/65">Tidak ada blokir maintenance per jam yang aktif.</p>
        @endforelse
    </div>

    <div x-show="walkInModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-5 bg-black/55">
        <div @click.away="walkInModal = false" class="wf-box rounded-2xl max-w-4xl w-full p-5 sm:p-6 space-y-5 max-h-[92vh] overflow-y-auto">
            <div class="flex items-start justify-between gap-3 border-b border-ink/15 pb-3.5">
                <div>
                    <span class="block text-[11px] uppercase tracking-wider text-accent font-bold">
                        RESERVASI ON-SITE KASIR • CEK JADWAL REAL-TIME
                    </span>
                    <h3 class="text-xl sm:text-2xl font-bold text-ink mt-0.5">+ Input Walk-In Booking</h3>
                </div>
                <button type="button" @click="walkInModal = false" class="p-2 rounded-xl bg-cream hover:bg-sand border border-ink/15 font-bold text-ink">✕</button>
            </div>

            @if($errors->any() && old('_modal') === 'walk_in')
                <div class="p-3.5 rounded-xl bg-rose-50 border border-rose-400 text-rose-950 text-xs space-y-1">
                    <div class="font-bold flex items-center gap-1.5">
                        <span>⚠</span>
                        <span>Gagal Menyimpan Walk-In Booking:</span>
                    </div>
                    <ul class="list-disc list-inside space-y-0.5 font-medium">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div x-show="walkInFormError" x-cloak class="p-3.5 rounded-xl bg-rose-50 border border-rose-400 text-rose-950 text-xs font-semibold flex items-center gap-2">
                <span>⚠</span>
                <span x-text="walkInFormError"></span>
            </div>

            <form action="{{ route(($routePrefix ?? 'kasir') . '.walkIn.store') }}" method="POST" @submit="validateWalkInSubmit($event)" class="space-y-5 text-xs">
                @csrf
                <input type="hidden" name="_modal" value="walk_in">
                <input type="hidden" name="field_id" :value="walkInFieldId">
                <input type="hidden" name="booking_date" :value="walkInDate">
                <template x-for="hStr in walkInSelectedHours" :key="hStr">
                    <input type="hidden" name="hours[]" :value="hStr">
                </template>

                <div class="grid grid-cols-1 lg:grid-cols-12 gap-5">
                    <div class="lg:col-span-5 space-y-4">
                        <div class="p-4 rounded-xl bg-cream/60 border border-ink/15 space-y-3">
                            <span class="block text-[11px] uppercase tracking-wider text-accent font-bold">
                                1. PILIH LAPANGAN & TANGGAL MAIN
                            </span>

                            <div>
                                <label class="block font-bold text-ink mb-1">Lapangan *</label>
                                <select x-model.number="walkInFieldId" @change="onWalkInFieldChange()"
                                        class="w-full rounded-xl border-ink/25 bg-paper text-xs font-semibold text-ink focus:border-accent focus:ring-accent">
                                    <template x-for="f in walkInFields" :key="f.id">
                                        <option :value="f.id" x-text="f.name + (f.status === 'maintenance' ? ' (Maintenance)' : ' — Rp ' + formatNumber(f.price_per_hour) + '/jam')"></option>
                                    </template>
                                </select>
                            </div>

                            <div class="pt-1">
                                <div class="flex items-center justify-between gap-2 mb-2">
                                    <button type="button" @click="walkInPrevMonth()"
                                            class="px-2.5 py-1 rounded-lg border border-ink/20 bg-paper hover:bg-sand text-[11px] font-bold">
                                        ←
                                    </button>
                                    <div class="flex items-center gap-1.5">
                                        <select x-model.number="walkInViewMonth" @change="buildWalkInCalendar()"
                                                class="rounded-lg border-ink/20 bg-paper text-[11px] font-bold text-ink py-1 px-2">
                                            <template x-for="(mName, mIdx) in monthNames" :key="mIdx">
                                                <option :value="mIdx" x-text="mName"></option>
                                            </template>
                                        </select>
                                        <select x-model.number="walkInViewYear" @change="buildWalkInCalendar()"
                                                class="rounded-lg border-ink/20 bg-paper text-[11px] font-bold text-ink py-1 px-2">
                                            <template x-for="yr in availableYears" :key="yr">
                                                <option :value="yr" x-text="yr"></option>
                                            </template>
                                        </select>
                                    </div>
                                    <div class="flex items-center gap-1">
                                        <button type="button" @click="walkInGoToday()"
                                                class="px-2 py-1 rounded-lg bg-ink text-paper text-[10px] font-bold">
                                            Hari Ini
                                        </button>
                                        <button type="button" @click="walkInNextMonth()"
                                                class="px-2.5 py-1 rounded-lg border border-ink/20 bg-paper hover:bg-sand text-[11px] font-bold">
                                            →
                                        </button>
                                    </div>
                                </div>

                                <div class="grid grid-cols-7 gap-1 text-center text-[10px] font-bold text-ink/65 mb-1">
                                    <div>Min</div>
                                    <div>Sen</div>
                                    <div>Sel</div>
                                    <div>Rab</div>
                                    <div>Kam</div>
                                    <div>Jum</div>
                                    <div>Sab</div>
                                </div>

                                <div class="grid grid-cols-7 gap-1">
                                    <template x-for="(cell, index) in walkInCalendarCells" :key="index">
                                        <div>
                                            <template x-if="!cell.dateStr">
                                                <div class="h-9 rounded-lg bg-paper/40"></div>
                                            </template>
                                            <template x-if="cell.dateStr">
                                                <button type="button"
                                                        :disabled="cell.isPast"
                                                        @click="selectWalkInDate(cell.dateStr)"
                                                        :class="{
                                                            'bg-ink text-paper border-ink shadow-sm': walkInDate === cell.dateStr,
                                                            'bg-paper/50 text-ink/35 border-ink/10 cursor-not-allowed': cell.isPast,
                                                            'bg-paper hover:bg-sand/70 text-ink border-ink/20': !cell.isPast && walkInDate !== cell.dateStr
                                                        }"
                                                        class="w-full h-9 rounded-lg border flex flex-col items-center justify-center transition">
                                                    <span class="text-[11px] font-bold leading-none" x-text="cell.day"></span>
                                                    <span x-show="walkInDate === cell.dateStr && walkInSelectedHours.length > 0"
                                                          class="mt-0.5 px-1 rounded-full text-[8px] font-extrabold bg-amber-300 text-ink leading-tight"
                                                          x-text="walkInSelectedHours.length + 'j'"></span>
                                                </button>
                                            </template>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </div>

                        <div class="p-4 rounded-xl bg-cream/60 border border-ink/15 space-y-3">
                            <span class="block text-[11px] uppercase tracking-wider text-accent font-bold">
                                3. DATA PENYEWA & PEMBAYARAN
                            </span>

                            <div>
                                <label class="block font-bold text-ink mb-1">Nama Pemesan / Tim *</label>
                                <input type="text" name="customer_name" x-model="walkInCustomerName" required
                                       placeholder="Contoh: Bagas Pratama / FC Nusantara"
                                       class="w-full rounded-xl border-ink/25 bg-paper text-xs text-ink">
                            </div>

                            <div>
                                <label class="block font-bold text-ink mb-1">Nomor WhatsApp *</label>
                                <input type="text" name="customer_phone" x-model="walkInCustomerPhone" required
                                       placeholder="0812xxxxxxxx"
                                       class="w-full rounded-xl border-ink/25 bg-paper text-xs text-ink">
                            </div>

                            <div class="grid grid-cols-2 gap-2.5">
                                <div>
                                    <label class="block font-bold text-ink mb-1">Skema Bayar *</label>
                                    <select name="payment_scheme" x-model="walkInScheme" class="w-full rounded-xl border-ink/25 bg-paper text-xs font-semibold">
                                        <option value="full">Lunas 100%</option>
                                        <option value="dp">DP 50%</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block font-bold text-ink mb-1">Metode Bayar *</label>
                                    <select name="method" x-model="walkInMethod" class="w-full rounded-xl border-ink/25 bg-paper text-xs font-semibold">
                                        <option value="cash">Tunai (Cash)</option>
                                        <option value="qris">QRIS Kasir</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="lg:col-span-7 space-y-4">
                        <div class="p-4 rounded-xl bg-paper border border-ink/15 space-y-3">
                            <div class="flex flex-wrap items-center justify-between gap-2 pb-3 border-b border-ink/15">
                                <div>
                                    <span class="block text-[11px] uppercase tracking-wider text-accent font-bold">
                                        2. PILIH SLOT JAM (<span x-text="formatHour(currentWalkInField.open_hour) + ' - ' + formatHour(currentWalkInField.close_hour) + ' WIB'"></span>)
                                    </span>
                                    <h4 class="text-sm font-bold text-ink mt-0.5">
                                        Jadwal <span x-text="currentWalkInField.name"></span> • <span x-text="formatDateIndo(walkInDate)"></span>
                                    </h4>
                                </div>

                                <div class="flex flex-wrap items-center gap-2.5 text-[10px] font-semibold">
                                    <span class="inline-flex items-center gap-1">
                                        <span class="w-2.5 h-2.5 rounded border border-emerald-700 bg-emerald-50"></span> Tersedia
                                    </span>
                                    <span class="inline-flex items-center gap-1">
                                        <span class="w-2.5 h-2.5 rounded border border-accent bg-accent"></span> Dipilih
                                    </span>
                                    <span class="inline-flex items-center gap-1">
                                        <span class="w-2.5 h-2.5 rounded border border-rose-400 bg-rose-100"></span> Dibooking
                                    </span>
                                    <span class="inline-flex items-center gap-1">
                                        <span class="w-2.5 h-2.5 rounded border border-amber-500 bg-amber-100"></span> Maint / Tutup
                                    </span>
                                </div>
                            </div>

                            <div x-show="walkInLoadingSlots" class="py-10 text-center text-xs text-ink/70">
                                Memuat status ketersediaan slot jam...
                            </div>

                            <div x-show="!walkInLoadingSlots && walkInFieldStatus === 'maintenance'"
                                 class="p-4 rounded-xl bg-amber-100 border border-amber-700/30 text-amber-950 text-xs font-medium">
                                Lapangan ini sedang dalam status <strong>MAINTENANCE</strong>. Silakan pilih lapangan lain.
                            </div>

                            <div x-show="!walkInLoadingSlots && walkInFieldStatus !== 'maintenance'"
                                 class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-2 max-h-[310px] overflow-y-auto pr-1">
                                <template x-for="hour in allHours" :key="hour">
                                    <div>
                                        <template x-if="!isWalkInOperationalHour(hour)">
                                            <div class="p-2.5 rounded-xl border border-ink/15 bg-sand/40 text-ink/45 select-none">
                                                <div class="flex items-center justify-between text-[9px] font-bold mb-0.5">
                                                    <span x-text="getTimePeriod(hour)"></span>
                                                    <span>TUTUP</span>
                                                </div>
                                                <div class="text-xs font-bold" x-text="formatHour(hour) + ' - ' + formatHour(hour + 1)"></div>
                                                <div class="text-[10px] mt-0.5">Diluar jam buka</div>
                                            </div>
                                        </template>

                                        <template x-if="isWalkInOperationalHour(hour) && isWalkInPastHour(hour)">
                                            <div class="p-2.5 rounded-xl border border-ink/15 bg-sand/40 text-ink/45 select-none">
                                                <div class="flex items-center justify-between text-[9px] font-bold mb-0.5">
                                                    <span x-text="getTimePeriod(hour)"></span>
                                                    <span>LEWAT</span>
                                                </div>
                                                <div class="text-xs font-bold" x-text="formatHour(hour) + ' - ' + formatHour(hour + 1)"></div>
                                                <div class="text-[10px] mt-0.5">Jam sudah lewat</div>
                                            </div>
                                        </template>

                                        <template x-if="isWalkInOperationalHour(hour) && !isWalkInPastHour(hour) && isWalkInMaintenanceHour(hour)">
                                            <div @click="notifyUnavailableSlot(hour, 'maintenance')" class="p-2.5 rounded-xl border border-amber-400 bg-amber-100/80 text-amber-950 select-none cursor-not-allowed">
                                                <div class="flex items-center justify-between text-[9px] font-bold mb-0.5">
                                                    <span x-text="getTimePeriod(hour)"></span>
                                                    <span>MAINT</span>
                                                </div>
                                                <div class="text-xs font-bold" x-text="formatHour(hour) + ' - ' + formatHour(hour + 1)"></div>
                                                <div class="text-[10px] font-semibold mt-0.5">Blokir Maint</div>
                                            </div>
                                        </template>

                                        <template x-if="isWalkInOperationalHour(hour) && !isWalkInPastHour(hour) && !isWalkInMaintenanceHour(hour) && isWalkInBookedHour(hour)">
                                            <div @click="notifyUnavailableSlot(hour, 'booked')"
                                                 style="background-color: #FEE2E2; border-color: #DC2626; color: #7F1D1D;"
                                                 class="p-2.5 rounded-xl border-2 select-none cursor-not-allowed">
                                                <div class="flex items-center justify-between text-[9px] font-extrabold mb-0.5">
                                                    <span x-text="getTimePeriod(hour)"></span>
                                                    <span style="background-color: #DC2626; color: #FFFFFF;" class="px-1.5 py-0.5 rounded">DIBOOKING</span>
                                                </div>
                                                <div class="text-xs font-extrabold" x-text="formatHour(hour) + ' - ' + formatHour(hour + 1)"></div>
                                                <div style="color: #991B1B;" class="text-[10px] font-bold mt-0.5">Sudah Dibooking</div>
                                            </div>
                                        </template>

                                        <template x-if="isWalkInOperationalHour(hour) && !isWalkInPastHour(hour) && !isWalkInMaintenanceHour(hour) && !isWalkInBookedHour(hour)">
                                            <button type="button"
                                                    @click="toggleWalkInHour(hour)"
                                                    :class="isWalkInSelectedHour(hour)
                                                        ? 'bg-accent text-paper border-accent shadow-sm ring-2 ring-accent/30'
                                                        : 'bg-emerald-50/70 hover:bg-emerald-100 text-ink border-emerald-700/40'"
                                                    class="w-full text-left p-2.5 rounded-xl border transition">
                                                <div class="flex items-center justify-between text-[9px] font-bold mb-0.5">
                                                    <span x-text="getTimePeriod(hour)"></span>
                                                    <span x-text="isWalkInSelectedHour(hour) ? '✓ DIPILIH' : '● OPEN'"></span>
                                                </div>
                                                <div class="text-xs font-bold" x-text="formatHour(hour) + ' - ' + formatHour(hour + 1)"></div>
                                                <div class="text-[10px] mt-0.5 font-medium"
                                                     :class="isWalkInSelectedHour(hour) ? 'text-paper/90' : 'text-emerald-800'"
                                                     x-text="isWalkInSelectedHour(hour) ? 'Klik untuk batal' : 'Rp ' + formatNumber(currentWalkInField.price_per_hour)"></div>
                                            </button>
                                        </template>
                                    </div>
                                </template>
                            </div>
                        </div>

                        <div class="p-4 rounded-xl bg-cream border border-ink/20 space-y-2.5">
                            <div class="flex items-center justify-between">
                                <span class="text-[11px] font-extrabold uppercase tracking-wider text-ink">
                                    RINGKASAN SLOT DIPILIH (<span x-text="walkInSelectedHours.length"></span> JAM)
                                </span>
                                <button type="button" x-show="walkInSelectedHours.length > 0" @click="walkInSelectedHours = []"
                                        class="text-[11px] font-bold text-rose-700 hover:underline">
                                    Reset Pilihan
                                </button>
                            </div>

                            <template x-if="walkInSelectedHours.length === 0">
                                <p class="text-[11px] text-ink/60">
                                    Belum ada slot jam dipilih. Klik kotak hijau yang berstatus <strong>OPEN</strong> di atas.
                                </p>
                            </template>

                            <template x-if="walkInSelectedHours.length > 0">
                                <div class="flex flex-wrap gap-1.5">
                                    <template x-for="hStr in walkInSelectedHours" :key="hStr">
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-paper border border-ink/20 text-[11px] font-bold text-ink">
                                            <span x-text="formatHourRange(hStr)"></span>
                                            <button type="button" @click="removeWalkInHour(hStr)" class="text-rose-700 hover:text-rose-900 font-extrabold">✕</button>
                                        </span>
                                    </template>
                                </div>
                            </template>

                            <div class="pt-2 border-t border-ink/15 flex flex-wrap items-center justify-between gap-2">
                                <div>
                                    <span class="block text-[11px] text-ink/70">Total Biaya Sewa</span>
                                    <span class="text-sm font-extrabold text-ink" x-text="'Rp ' + formatNumber(walkInTotalAmount)"></span>
                                </div>
                                <div class="text-right">
                                    <span class="block text-[11px] text-ink/70">
                                        Dibayar Sekarang (<span x-text="walkInScheme === 'dp' ? 'DP 50%' : 'Lunas 100%'"></span>)
                                    </span>
                                    <span class="text-base font-extrabold text-accent" x-text="'Rp ' + formatNumber(walkInPayableNow)"></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end gap-2.5 pt-3 border-t border-ink/15">
                    <button type="button" @click="walkInModal = false" class="px-4 py-2.5 rounded-xl bg-cream hover:bg-sand border border-ink/20 font-bold text-ink">
                        Batal
                    </button>
                    <button type="submit" class="wf-btn px-6 py-2.5 rounded-xl bg-ink hover:bg-ink/90 text-paper font-bold">
                        Simpan & Kunci Jadwal
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div x-show="maintModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50">
        <div @click.away="maintModal = false" class="wf-box rounded-2xl max-w-md w-full p-6 space-y-4">
            <div class="flex items-center justify-between border-b border-ink/15 pb-3">
                <h3 class="text-xl font-bold text-ink">Blokir Jam Maintenance</h3>
                <button type="button" @click="maintModal = false" class="p-1.5 rounded-lg bg-cream font-bold">✕</button>
            </div>
            <form action="{{ route(($routePrefix ?? 'kasir') . '.maintenance.store') }}" method="POST" class="space-y-3 text-xs">
                @csrf
                <div>
                    <label class="block font-bold mb-1">Pilih Lapangan *</label>
                    <select name="field_id" required class="w-full rounded-xl border-ink/25 text-xs">
                        @foreach($activeFields as $f)
                            <option value="{{ $f->id }}">{{ $f->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-bold mb-1">Tanggal *</label>
                        <input type="date" name="block_date" value="{{ date('Y-m-d') }}" min="{{ date('Y-m-d') }}" required class="w-full rounded-xl border-ink/25 text-xs">
                    </div>
                    <div>
                        <label class="block font-bold mb-1">Jam Mulai *</label>
                        <select name="start_hour" required class="w-full rounded-xl border-ink/25 text-xs">
                            @for($h = 0; $h < 24; $h++)
                                @php $hh = str_pad((string)$h, 2, '0', STR_PAD_LEFT).':00'; @endphp
                                <option value="{{ $hh }}">{{ $hh }} WIB</option>
                            @endfor
                        </select>
                    </div>
                </div>
                <div>
                    <label class="block font-bold mb-1">Keterangan Perawatan *</label>
                    <input type="text" name="reason" required placeholder="Contoh: Pengecekan lampu LED tribun & pembersihan interlock" class="w-full rounded-xl border-ink/25 text-xs">
                </div>
                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" @click="maintModal = false" class="px-4 py-2 rounded-xl bg-cream border border-ink/20 font-bold">Batal</button>
                    <button type="submit" class="wf-btn px-5 py-2 rounded-xl bg-accent text-paper font-bold">Aktifkan Blokir</button>
                </div>
            </form>
        </div>
    </div>

    <div x-show="detailData" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50">
        <div @click.away="detailData = null" class="wf-box rounded-2xl max-w-md w-full p-6 space-y-4">
            <div class="flex items-center justify-between border-b border-ink/15 pb-3">
                <h3 class="text-xl font-bold text-ink font-mono" x-text="detailData?.code"></h3>
                <button type="button" @click="detailData = null" class="p-1.5 rounded-lg bg-cream font-bold">✕</button>
            </div>
            <template x-if="detailData">
                <div class="space-y-3 text-xs">
                    <div class="p-3.5 rounded-xl bg-cream/70 border border-ink/15 space-y-1">
                        <div class="font-bold text-sm" x-text="detailData.customer"></div>
                        <div>WhatsApp: <strong x-text="detailData.phone"></strong></div>
                        <div>Status: <strong x-text="detailData.status"></strong></div>
                    </div>
                    <div class="space-y-1">
                        <span class="font-bold block">Slot Terpesan:</span>
                        <template x-for="(it, idx) in detailData.items" :key="idx">
                            <div class="p-2.5 rounded-lg bg-paper border border-ink/15 flex justify-between">
                                <span class="font-semibold" x-text="it.field"></span>
                                <span x-text="it.date + ' • ' + it.time"></span>
                            </div>
                        </template>
                    </div>
                    <div class="p-3 rounded-xl bg-cream border border-ink/20 flex justify-between font-bold">
                        <span>Terbayar: Rp <span x-text="detailData.paid"></span></span>
                        <span class="text-accent">Sisa: Rp <span x-text="detailData.remaining"></span></span>
                    </div>
                </div>
            </template>
        </div>
    </div>

    <div x-show="verifyData" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50">
        <div @click.away="verifyData = null" class="wf-box rounded-2xl max-w-lg w-full p-6 space-y-4 max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between border-b border-ink/15 pb-3">
                <div>
                    <span class="text-[11px] font-bold uppercase text-accent">VERIFIKASI PEMBAYARAN QRIS</span>
                    <h3 class="text-xl font-bold text-ink font-mono" x-text="verifyData?.code"></h3>
                </div>
                <button type="button" @click="verifyData = null" class="p-1.5 rounded-lg bg-cream font-bold">✕</button>
            </div>
            <template x-if="verifyData">
                <div class="space-y-4 text-xs">
                    <div class="p-3.5 rounded-xl bg-cream/70 border border-ink/15 flex justify-between items-center">
                        <div>
                            <div class="font-bold text-sm" x-text="verifyData.customer"></div>
                            <div class="text-ink/70">Tahap: <strong x-text="verifyData.stage"></strong></div>
                        </div>
                        <div class="text-right">
                            <span class="block text-[11px] text-ink/60">Nominal Transfer</span>
                            <span class="text-lg font-extrabold text-accent" x-text="'Rp ' + verifyData.amount"></span>
                        </div>
                    </div>

                    <div class="rounded-xl border border-ink/20 bg-white p-3 text-center">
                        <template x-if="verifyData.proof_url">
                            <a :href="verifyData.proof_url" target="_blank" rel="noopener noreferrer">
                                <img :src="verifyData.proof_url" alt="Bukti Transfer QRIS" class="max-h-72 mx-auto rounded-lg object-contain">
                                <span class="block text-[11px] text-accent font-bold mt-2 underline">Klik untuk buka ukuran penuh ↗</span>
                            </a>
                        </template>
                        <template x-if="!verifyData.proof_url">
                            <p class="py-8 text-ink/60">Transaksi tunai / walk-in tanpa unggahan foto bukti transfer.</p>
                        </template>
                    </div>

                    <template x-if="verifyData.status === 'pending'">
                        <div class="space-y-3 pt-2 border-t border-ink/15">
                            <form :action="verifyData.verify_url" method="POST">
                                @csrf
                                <button type="submit" class="wf-btn w-full py-2.5 px-4 rounded-xl bg-emerald-700 hover:bg-emerald-800 text-white font-bold">
                                    ✓ Terima Pembayaran & Kunci Jadwal
                                </button>
                            </form>

                            <form :action="verifyData.reject_url" method="POST" class="flex gap-2">
                                @csrf
                                <input type="text" name="rejection_reason" required placeholder="Tulis alasan penolakan bukti..."
                                       class="flex-1 rounded-xl border-ink/25 text-xs">
                                <button type="submit" class="px-4 py-2 rounded-xl bg-rose-700 hover:bg-rose-800 text-white font-bold">
                                    Tolak Bukti
                                </button>
                            </form>
                        </div>
                    </template>
                </div>
            </template>
        </div>
    </div>

    <div x-show="settleData" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50">
        <div @click.away="settleData = null" class="wf-box rounded-2xl max-w-md w-full p-6 space-y-4">
            <div class="flex items-center justify-between border-b border-ink/15 pb-3">
                <h3 class="text-xl font-bold text-ink">Edit / Pelunasan Pembayaran</h3>
                <button type="button" @click="settleData = null" class="p-1.5 rounded-lg bg-cream font-bold">✕</button>
            </div>
            <template x-if="settleData">
                <form :action="settleData.settle_url" method="POST" class="space-y-3 text-xs">
                    @csrf
                    <div class="p-3.5 rounded-xl bg-cream/70 border border-ink/15 space-y-1">
                        <div class="font-bold text-sm" x-text="settleData.customer + ' (' + settleData.code + ')'"></div>
                        <div>Total Sewa: <strong>Rp <span x-text="settleData.total"></span></strong></div>
                        <div>Sudah Terbayar: <strong>Rp <span x-text="settleData.paid"></span></strong></div>
                        <div class="text-rose-700 font-bold">Sisa Tagihan: Rp <span x-text="settleData.remaining_fmt"></span></div>
                    </div>

                    <div>
                        <label class="block font-bold mb-1">Nominal Pembayaran Diterima (Rp) *</label>
                        <input type="number" name="amount" :value="settleData.remaining" :max="settleData.remaining" min="1" required
                               class="w-full rounded-xl border-ink/25 text-xs font-bold">
                    </div>

                    <div>
                        <label class="block font-bold mb-1">Metode Pelunasan *</label>
                        <select name="method" class="w-full rounded-xl border-ink/25 text-xs">
                            <option value="cash">Tunai (Cash) di Kasir</option>
                            <option value="qris">QRIS Kasir</option>
                        </select>
                    </div>

                    <div class="flex justify-end gap-2 pt-2">
                        <button type="button" @click="settleData = null" class="px-4 py-2 rounded-xl bg-cream border border-ink/20 font-bold">Batal</button>
                        <button type="submit" class="wf-btn px-5 py-2 rounded-xl bg-ink text-paper font-bold">Simpan & Tandai Lunas</button>
                    </div>
                </form>
            </template>
        </div>
    </div>

    <div x-show="rescheduleData" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50">
        <div @click.away="rescheduleData = null" class="wf-box rounded-2xl max-w-md w-full p-6 space-y-4">
            <div class="flex items-center justify-between border-b border-ink/15 pb-3">
                <h3 class="text-xl font-bold text-ink">Reschedule Jadwal Main</h3>
                <button type="button" @click="rescheduleData = null" class="p-1.5 rounded-lg bg-cream font-bold">✕</button>
            </div>
            <template x-if="rescheduleData">
                <form :action="rescheduleData.reschedule_url" method="POST" class="space-y-3 text-xs">
                    @csrf
                    <p class="text-ink/75">
                        Pindahkan jadwal <strong x-text="rescheduleData.customer"></strong> (<span class="font-mono font-bold" x-text="rescheduleData.code"></span>) — durasi <strong x-text="rescheduleData.slot_count + ' jam'"></strong>.
                    </p>
                    <div>
                        <label class="block font-bold mb-1">Lapangan Tujuan *</label>
                        <select name="field_id" x-model="rescheduleData.field_id" class="w-full rounded-xl border-ink/25 text-xs">
                            @foreach($activeFields->where('status', 'active') as $f)
                                <option value="{{ $f->id }}">{{ $f->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block font-bold mb-1">Tanggal Baru *</label>
                            <input type="date" name="booking_date" x-model="rescheduleData.booking_date" required class="w-full rounded-xl border-ink/25 text-xs">
                        </div>
                        <div>
                            <label class="block font-bold mb-1">Jam Mulai Baru *</label>
                            <select name="start_hour" x-model="rescheduleData.start_hour" class="w-full rounded-xl border-ink/25 text-xs">
                                @for($h = 0; $h < 24; $h++)
                                    @php $hh = str_pad((string)$h, 2, '0', STR_PAD_LEFT).':00'; @endphp
                                    <option value="{{ $hh }}">{{ $hh }} WIB</option>
                                @endfor
                            </select>
                        </div>
                    </div>
                    <div class="flex justify-end gap-2 pt-2">
                        <button type="button" @click="rescheduleData = null" class="px-4 py-2 rounded-xl bg-cream border border-ink/20 font-bold">Batal</button>
                        <button type="submit" class="wf-btn px-5 py-2 rounded-xl bg-accent text-paper font-bold">Simpan Jadwal Baru</button>
                    </div>
                </form>
            </template>
        </div>
    </div>

    <div x-show="notaData" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50">
        <div @click.away="notaData = null" class="wf-box rounded-2xl max-w-md w-full p-6 space-y-4">
            <div class="flex items-center justify-between border-b border-ink/15 pb-3">
                <h3 class="text-xl font-bold text-ink">Nota WhatsApp & E-Invoice</h3>
                <button type="button" @click="notaData = null" class="p-1.5 rounded-lg bg-cream font-bold">✕</button>
            </div>
            <template x-if="notaData">
                <div class="space-y-3 text-xs">
                    <textarea readonly rows="8" x-text="notaData.text"
                              class="w-full rounded-xl border-ink/20 bg-cream/60 text-xs text-ink p-3"></textarea>
                    <div class="flex flex-wrap gap-2">
                        <a :href="notaData.wa_url" target="_blank" rel="noopener noreferrer"
                           class="wf-btn flex-1 text-center py-2.5 px-4 rounded-xl bg-emerald-700 hover:bg-emerald-800 text-white font-bold">
                            Kirim via WhatsApp ↗
                        </a>
                        <button type="button" @click="copyNotaText(notaData.text)"
                                class="px-4 py-2.5 rounded-xl bg-cream hover:bg-sand border border-ink/20 font-bold text-ink">
                            <span x-text="copiedNota ? '✓ Tersalin' : 'Salin Teks'"></span>
                        </button>
                        <a :href="notaData.invoice_url" target="_blank" rel="noopener noreferrer"
                           class="px-4 py-2.5 rounded-xl bg-ink text-paper font-bold">
                            Cetak
                        </a>
                    </div>
                </div>
            </template>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
function kasirApp({
    fields,
    initialFieldId,
    initialDate,
    initialModalOpen,
    initialSelectedHours,
    initialScheme,
    initialMethod,
    initialCustomerName,
    initialCustomerPhone
}) {
    const todayObj = new Date();
    const todayStr = todayObj.getFullYear() + '-' + String(todayObj.getMonth() + 1).padStart(2, '0') + '-' + String(todayObj.getDate()).padStart(2, '0');
    const initParts = (initialDate || todayStr).split('-');

    return {
        walkInModal: Boolean(initialModalOpen),
        maintModal: false,
        detailData: null,
        verifyData: null,
        settleData: null,
        rescheduleData: null,
        notaData: null,
        copiedNota: false,

        walkInFields: fields,
        walkInFieldId: initialFieldId || (fields[0] ? fields[0].id : 0),
        walkInDate: initialDate || todayStr,
        todayStr: todayStr,
        walkInViewYear: parseInt(initParts[0], 10),
        walkInViewMonth: parseInt(initParts[1], 10) - 1,
        availableYears: [todayObj.getFullYear(), todayObj.getFullYear() + 1, todayObj.getFullYear() + 2],
        monthNames: ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'],
        walkInCalendarCells: [],
        allHours: Array.from({ length: 24 }, (_, i) => i),
        walkInBookedHours: [],
        walkInMaintenanceHours: [],
        walkInFieldStatus: 'active',
        walkInLoadingSlots: false,
        walkInSelectedHours: Array.isArray(initialSelectedHours) ? [...initialSelectedHours].sort() : [],
        walkInScheme: initialScheme || 'full',
        walkInMethod: initialMethod || 'cash',
        walkInCustomerName: initialCustomerName || '',
        walkInCustomerPhone: initialCustomerPhone || '',
        walkInFormError: '',

        get currentWalkInField() {
            return this.walkInFields.find(f => f.id === this.walkInFieldId) || {
                id: 0, name: 'Lapangan', price_per_hour: 0, open_hour: 8, close_hour: 23, status: 'active'
            };
        },

        get walkInTotalAmount() {
            return this.walkInSelectedHours.length * (this.currentWalkInField.price_per_hour || 0);
        },

        get walkInPayableNow() {
            return this.walkInScheme === 'dp' ? Math.round(this.walkInTotalAmount * 0.5) : this.walkInTotalAmount;
        },

        init() {
            this.buildWalkInCalendar();
            this.fetchWalkInSlots();
        },

        openWalkInModal() {
            this.walkInFormError = '';
            this.walkInModal = true;
            this.buildWalkInCalendar();
            this.fetchWalkInSlots();
        },

        copyNotaText(text) {
            navigator.clipboard.writeText(text);
            this.copiedNota = true;
            setTimeout(() => this.copiedNota = false, 2000);
        },

        buildWalkInCalendar() {
            const firstDay = new Date(this.walkInViewYear, this.walkInViewMonth, 1).getDay();
            const daysInMonth = new Date(this.walkInViewYear, this.walkInViewMonth + 1, 0).getDate();
            const cells = [];

            for (let i = 0; i < firstDay; i++) {
                cells.push({ day: null, dateStr: null, isPast: false });
            }

            for (let d = 1; d <= daysInMonth; d++) {
                const dateStr = `${this.walkInViewYear}-${String(this.walkInViewMonth + 1).padStart(2, '0')}-${String(d).padStart(2, '0')}`;
                cells.push({
                    day: d,
                    dateStr: dateStr,
                    isPast: dateStr < this.todayStr
                });
            }

            this.walkInCalendarCells = cells;
        },

        walkInPrevMonth() {
            if (this.walkInViewMonth === 0) {
                if (this.availableYears.includes(this.walkInViewYear - 1)) {
                    this.walkInViewYear--;
                    this.walkInViewMonth = 11;
                }
            } else {
                this.walkInViewMonth--;
            }
            this.buildWalkInCalendar();
        },

        walkInNextMonth() {
            if (this.walkInViewMonth === 11) {
                if (this.availableYears.includes(this.walkInViewYear + 1)) {
                    this.walkInViewYear++;
                    this.walkInViewMonth = 0;
                }
            } else {
                this.walkInViewMonth++;
            }
            this.buildWalkInCalendar();
        },

        walkInGoToday() {
            const now = new Date();
            this.walkInViewYear = now.getFullYear();
            this.walkInViewMonth = now.getMonth();
            this.walkInDate = this.todayStr;
            this.walkInSelectedHours = [];
            this.buildWalkInCalendar();
            this.fetchWalkInSlots();
        },

        selectWalkInDate(dateStr) {
            this.walkInDate = dateStr;
            this.walkInSelectedHours = [];
            this.walkInFormError = '';
            this.fetchWalkInSlots();
        },

        onWalkInFieldChange() {
            this.walkInSelectedHours = [];
            this.walkInFormError = '';
            this.fetchWalkInSlots();
        },

        async fetchWalkInSlots() {
            if (!this.walkInFieldId) return;
            this.walkInLoadingSlots = true;
            try {
                const res = await fetch(`{{ route('api.slots') }}?field_id=${this.walkInFieldId}&date=${this.walkInDate}`);
                const data = await res.json();
                this.walkInFieldStatus = data.status || 'active';
                this.walkInBookedHours = data.booking_hours || data.booked_hours || [];
                this.walkInMaintenanceHours = data.maintenance_hours || [];
                this.walkInSelectedHours = this.walkInSelectedHours.filter(
                    h => !this.walkInBookedHours.includes(h) && !this.walkInMaintenanceHours.includes(h)
                );
            } catch (e) {
                this.walkInBookedHours = [];
                this.walkInMaintenanceHours = [];
            } finally {
                this.walkInLoadingSlots = false;
            }
        },

        isWalkInOperationalHour(hour) {
            return hour >= this.currentWalkInField.open_hour && hour < this.currentWalkInField.close_hour;
        },

        isWalkInPastHour(hour) {
            if (this.walkInDate !== this.todayStr) return false;
            const nowHour = new Date().getHours();
            return hour < nowHour;
        },

        isWalkInMaintenanceHour(hour) {
            return this.walkInMaintenanceHours.includes(this.formatHour(hour));
        },

        isWalkInBookedHour(hour) {
            return this.walkInBookedHours.includes(this.formatHour(hour));
        },

        isWalkInSelectedHour(hour) {
            return this.walkInSelectedHours.includes(this.formatHour(hour));
        },

        notifyUnavailableSlot(hour, reason) {
            const range = this.formatHourRange(this.formatHour(hour));
            if (reason === 'maintenance') {
                this.walkInFormError = `Slot jam ${range} sedang diblokir untuk maintenance lapangan.`;
            } else {
                this.walkInFormError = `Slot jam ${range} sudah dibooking / terisi pada jadwal lapangan.`;
            }
        },

        toggleWalkInHour(hour) {
            const hStr = this.formatHour(hour);
            if (this.isWalkInBookedHour(hour) || this.isWalkInMaintenanceHour(hour) || this.isWalkInPastHour(hour)) {
                return;
            }
            if (this.walkInSelectedHours.includes(hStr)) {
                this.walkInSelectedHours = this.walkInSelectedHours.filter(s => s !== hStr);
            } else {
                this.walkInSelectedHours = [...this.walkInSelectedHours, hStr].sort();
            }
            this.walkInFormError = '';
        },

        removeWalkInHour(hStr) {
            this.walkInSelectedHours = this.walkInSelectedHours.filter(s => s !== hStr);
        },

        formatHour(hour) {
            return String(hour).padStart(2, '0') + ':00';
        },

        formatHourRange(hStr) {
            const h = parseInt(hStr.split(':')[0], 10);
            return `${this.formatHour(h)} - ${this.formatHour(h + 1)}`;
        },

        getTimePeriod(hour) {
            if (hour < 6) return 'DINI HARI';
            if (hour < 12) return 'PAGI';
            if (hour < 18) return 'SIANG/SORE';
            return 'MALAM';
        },

        formatDateIndo(dateStr) {
            if (!dateStr) return '';
            const [y, m, d] = dateStr.split('-');
            return `${d} ${this.monthNames[parseInt(m, 10) - 1]} ${y}`;
        },

        formatNumber(num) {
            return new Intl.NumberFormat('id-ID').format(num || 0);
        },

        validateWalkInSubmit(e) {
            if (this.walkInFieldStatus === 'maintenance') {
                e.preventDefault();
                this.walkInFormError = 'Lapangan yang dipilih sedang maintenance. Silakan pilih lapangan lain.';
                return false;
            }
            if (this.walkInSelectedHours.length === 0) {
                e.preventDefault();
                this.walkInFormError = 'Pilih minimal 1 slot jam yang tersedia sebelum menyimpan.';
                return false;
            }
            if (!this.walkInCustomerName.trim() || !this.walkInCustomerPhone.trim()) {
                e.preventDefault();
                this.walkInFormError = 'Nama pemesan/tim dan nomor WhatsApp aktif wajib diisi.';
                return false;
            }
            this.walkInFormError = '';
            return true;
        }
    };
}
</script>
@endpush
