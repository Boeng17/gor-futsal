@extends('layouts.app')

@section('title', 'Kalender & Form Reservasi — GOR Futsal Nusantara')

@section('content')
@php
    $fieldsJson = $fields->map(fn ($f) => [
        'id'             => $f->id,
        'name'           => $f->name,
        'price_per_hour' => (int) $f->price_per_hour,
        'open_hour'      => $f->open_hour_value,
        'close_hour'     => $f->close_hour_value,
        'status'         => $f->status,
        'description'    => $f->description,
    ])->values();
@endphp

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-10"
     x-data="bookingApp({
        fields: {{ Js::from($fieldsJson) }},
        initialFieldId: {{ (int) ($selectedFieldId ?? 0) }},
        initialDate: '{{ $selectedDate }}',
        serverNow: '{{ $serverNow ?? now()->format('Y-m-d H:i:s') }}'
     })">

    <div class="mb-8">
        <span class="block text-xs uppercase tracking-widest text-accent font-bold mb-1">
            LANGKAH 1 DARI 3 • PILIH JADWAL & DATA PEMESAN
        </span>
        <h1 class="text-3xl sm:text-4xl font-bold text-ink">
            Kalender Ketersediaan & Multi-Booking
        </h1>
        <p class="text-xs sm:text-sm text-ink/75 mt-1">
            Pilih lapangan, tentukan tanggal pada kalender tahunan, lalu klik slot jam yang tersedia (mendukung multi-jam & lintas tanggal).
        </p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">

        <div class="lg:col-span-8 space-y-6">

            <div class="wf-box rounded-2xl p-6">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-5 border-b border-ink/15">
                    <div>
                        <span class="block text-[11px] uppercase tracking-wider text-accent font-bold">
                            1. PILIH ARENA & TANGGAL MAIN
                        </span>
                        <h2 class="text-xl font-bold text-ink mt-0.5">Kalender Ketersediaan Tahunan</h2>
                    </div>

                    <div class="w-full sm:w-72">
                        <label for="field_selector" class="block text-[11px] uppercase text-ink/70 mb-1">Lapangan Dipilih</label>
                        <select id="field_selector" x-model.number="selectedFieldId" @change="onFieldChange()"
                                class="w-full rounded-xl border-ink/25 bg-cream/60 text-sm font-semibold text-ink focus:border-accent focus:ring-accent">
                            <template x-for="f in fields" :key="f.id">
                                <option :value="f.id" x-text="f.name + (f.status === 'maintenance' ? ' (Maintenance)' : ' — Rp ' + formatNumber(f.price_per_hour) + '/jam')"></option>
                            </template>
                        </select>
                    </div>
                </div>

                <div class="mt-5 flex flex-wrap items-center justify-between gap-3">
                    <div class="flex items-center gap-2">
                        <button type="button" @click="prevMonth()"
                                class="px-3 py-1.5 rounded-lg border border-ink/20 bg-cream hover:bg-sand text-xs font-bold">
                            ← Bulan Lalu
                        </button>
                        <button type="button" @click="nextMonth()"
                                class="px-3 py-1.5 rounded-lg border border-ink/20 bg-cream hover:bg-sand text-xs font-bold">
                            Bulan Depan →
                        </button>
                    </div>

                    <div class="flex items-center gap-2">
                        <select x-model.number="viewMonth" @change="buildCalendar()"
                                class="rounded-lg border-ink/20 bg-paper text-xs font-bold text-ink focus:border-accent focus:ring-accent">
                            <template x-for="(mName, mIdx) in monthNames" :key="mIdx">
                                <option :value="mIdx" x-text="mName"></option>
                            </template>
                        </select>

                        <select x-model.number="viewYear" @change="buildCalendar()"
                                class="rounded-lg border-ink/20 bg-paper text-xs font-bold text-ink focus:border-accent focus:ring-accent">
                            <template x-for="yr in availableYears" :key="yr">
                                <option :value="yr" x-text="yr"></option>
                            </template>
                        </select>

                        <button type="button" @click="goToday()"
                                class="px-3 py-1.5 rounded-lg bg-ink text-paper text-xs font-semibold">
                            Hari Ini
                        </button>
                    </div>
                </div>

                <div class="mt-4">
                    <div class="grid grid-cols-7 gap-1.5 text-center text-[11px] font-bold text-ink/65 mb-1.5">
                        <div>Min</div>
                        <div>Sen</div>
                        <div>Sel</div>
                        <div>Rab</div>
                        <div>Kam</div>
                        <div>Jum</div>
                        <div>Sab</div>
                    </div>

                    <div class="grid grid-cols-7 gap-1.5">
                        <template x-for="(cell, index) in calendarCells" :key="index">
                            <div>
                                <template x-if="!cell.dateStr">
                                    <div class="h-11 rounded-xl bg-cream/25"></div>
                                </template>

                                <template x-if="cell.dateStr">
                                    <button type="button"
                                            :disabled="cell.isPast"
                                            @click="selectDate(cell.dateStr)"
                                            :class="{
                                                'bg-ink text-paper border-ink shadow-sm': selectedDate === cell.dateStr,
                                                'bg-cream/50 text-ink/35 border-ink/10 cursor-not-allowed': cell.isPast,
                                                'bg-paper hover:bg-cream text-ink border-ink/20': !cell.isPast && selectedDate !== cell.dateStr
                                            }"
                                            class="w-full h-11 rounded-xl border flex flex-col items-center justify-center relative transition">
                                        <span class="text-xs font-bold" x-text="cell.day"></span>
                                        <span x-show="countSelectedOnDate(cell.dateStr) > 0"
                                              class="mt-0.5 px-1.5 rounded-full text-[9px] font-bold"
                                              :class="selectedDate === cell.dateStr ? 'bg-amber-300 text-ink' : 'bg-accent text-paper'"
                                              x-text="countSelectedOnDate(cell.dateStr) + ' jam'"></span>
                                    </button>
                                </template>
                            </div>
                        </template>
                    </div>
                </div>
            </div>

            <div class="wf-box rounded-2xl p-6">
                <div class="flex flex-wrap items-center justify-between gap-3 pb-4 border-b border-ink/15">
                    <div>
                        <span class="block text-[11px] uppercase tracking-wider text-accent font-bold">
                            2. MATRIKS SLOT JAM (24 JAM / JAM OPERASIONAL: <span x-text="formatHour(currentField.open_hour) + ' - ' + formatHour(currentField.close_hour) + ' WIB'"></span>)
                        </span>
                        <h2 class="text-xl font-bold text-ink mt-0.5">
                            Jadwal Tanggal <span x-text="formatDateIndo(selectedDate)"></span>
                        </h2>
                    </div>

                    <div class="flex flex-wrap items-center gap-3 text-[11px] font-semibold">
                        <span class="inline-flex items-center gap-1.5">
                            <span style="background-color: #ECFDF5; border-color: #047857;" class="w-3.5 h-3.5 rounded border"></span> Tersedia
                        </span>
                        <span class="inline-flex items-center gap-1.5">
                            <span style="background-color: #6C4830; border-color: #6C4830;" class="w-3.5 h-3.5 rounded border"></span> Dipilih
                        </span>
                        <span class="inline-flex items-center gap-1.5">
                            <span style="background-color: #FEE2E2; border-color: #DC2626;" class="w-3.5 h-3.5 rounded border-2"></span> Dibooking
                        </span>
                        <span class="inline-flex items-center gap-1.5">
                            <span style="background-color: #E7E5E4; border-color: #A8A29E;" class="w-3.5 h-3.5 rounded border"></span> Terlewat
                        </span>
                        <span class="inline-flex items-center gap-1.5">
                            <span class="w-3.5 h-3.5 rounded border border-ink/25 bg-sand/60"></span> Tutup / Maint
                        </span>
                    </div>
                </div>

                <div x-show="selectedDate === todayStr && !loadingSlots && fieldStatus !== 'maintenance'"
                     class="mt-4 p-3.5 rounded-xl bg-cream/80 border border-ink/15 flex flex-wrap items-center justify-between gap-2 text-xs text-ink/85">
                    <div>
                        <span class="font-bold text-ink">Waktu Real-Time: <span x-text="currentTimeLabel + ' WIB'"></span>.</span>
                        <span x-show="!allTodaySlotsPassed">
                            Slot sebelum jam <strong x-text="nextAvailableHourLabel"></strong> ditandai <span class="px-1.5 py-0.5 rounded bg-stone-200 border border-stone-400/60 text-[10px] font-bold text-stone-700">TERLEWAT</span> dan tidak dapat dipilih.
                        </span>
                        <span x-show="allTodaySlotsPassed" class="text-amber-900 font-semibold">
                            Seluruh jam operasional hari ini sudah terlewat.
                        </span>
                    </div>
                    <button x-show="allTodaySlotsPassed" type="button" @click="goTomorrow()"
                            class="px-3 py-1.5 rounded-lg bg-accent hover:bg-accentDark text-paper text-[11px] font-bold shrink-0">
                        Pilih Jadwal Besok →
                    </button>
                </div>

                <div x-show="slotNotice" x-transition
                     class="mt-3 p-3 rounded-xl bg-amber-100/90 border border-amber-700/30 text-amber-950 text-xs font-semibold flex items-center justify-between gap-2">
                    <span x-text="slotNotice"></span>
                    <button type="button" @click="slotNotice = ''" class="text-amber-950/70 hover:text-amber-950 font-bold px-1.5">✕</button>
                </div>

                <div x-show="loadingSlots" class="py-10 text-center text-xs text-ink/70">
                    Memuat ketersediaan jadwal real-time dari server...
                </div>

                <div x-show="!loadingSlots && fieldStatus === 'maintenance'"
                     class="mt-5 p-5 rounded-xl bg-amber-100 border border-amber-700/30 text-amber-950 text-xs font-medium">
                    Lapangan ini sedang dalam status <strong>MAINTENANCE (Perawatan Berkala)</strong> sehingga tidak dapat dipesan sementara waktu. Silakan pilih lapangan lain.
                </div>

                <div x-show="!loadingSlots && fieldStatus !== 'maintenance'" class="mt-5 grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3">
                    <template x-for="hour in allHours" :key="hour">
                        <div>
                            <template x-if="!isOperationalHour(hour)">
                                <div class="p-3.5 rounded-xl border border-ink/15 bg-sand/40 text-ink/45 select-none cursor-not-allowed">
                                    <div class="flex items-center justify-between text-[10px] mb-1">
                                        <span x-text="getTimePeriod(hour)"></span>
                                        <span>TUTUP</span>
                                    </div>
                                    <div class="text-sm font-bold" x-text="formatHour(hour) + ' - ' + formatHour(hour + 1)"></div>
                                    <div class="text-[11px] mt-1">Diluar jam buka</div>
                                </div>
                            </template>

                            <template x-if="isOperationalHour(hour) && isPastHour(hour)">
                                <div @click="notifyPastSlot(hour)"
                                     title="Jam bermain ini sudah terlewat dari waktu saat ini dan tidak dapat dipilih"
                                     style="background-color: #E7E5E4; border-color: #A8A29E; color: #57534E;"
                                     class="p-3.5 rounded-xl border select-none cursor-not-allowed opacity-90">
                                    <div class="flex items-center justify-between text-[10px] font-bold mb-1">
                                        <span x-text="getTimePeriod(hour)"></span>
                                        <span style="background-color: #D6D3D1; color: #44403C;" class="px-1.5 py-0.5 rounded">TERLEWAT</span>
                                    </div>
                                    <div class="text-sm font-bold line-through" x-text="formatHour(hour) + ' - ' + formatHour(hour + 1)"></div>
                                    <div class="text-[11px] font-medium mt-1">Waktu sudah lewat</div>
                                </div>
                            </template>

                            <template x-if="isOperationalHour(hour) && !isPastHour(hour) && isBooked(hour)">
                                <div @click="slotNotice = 'Slot pukul ' + formatHour(hour) + ' - ' + formatHour(hour + 1) + ' WIB sudah dibooking / dikunci penyewa lain. Silakan pilih slot hijau yang masih tersedia.'"
                                     title="Jadwal ini sudah dibooking atau sedang dikunci menunggu pembayaran"
                                     style="background-color: #FEE2E2; border-color: #DC2626; color: #7F1D1D;"
                                     class="p-3.5 rounded-xl border-2 select-none cursor-not-allowed shadow-sm">
                                    <div class="flex items-center justify-between text-[10px] font-extrabold mb-1">
                                        <span x-text="getTimePeriod(hour)"></span>
                                        <span style="background-color: #DC2626; color: #FFFFFF;" class="px-1.5 py-0.5 rounded tracking-wide">DIBOOKING</span>
                                    </div>
                                    <div class="text-sm font-extrabold" x-text="formatHour(hour) + ' - ' + formatHour(hour + 1)"></div>
                                    <div style="color: #991B1B;" class="text-[11px] font-bold mt-1">Sudah Dibooking</div>
                                </div>
                            </template>

                            <template x-if="isOperationalHour(hour) && !isPastHour(hour) && !isBooked(hour)">
                                <button type="button"
                                        @click="toggleSlot(hour)"
                                        :style="isSelected(hour)
                                            ? 'background-color: #6C4830; border-color: #543724; color: #FBF7F0;'
                                            : 'background-color: #ECFDF5; border-color: #047857; color: #232B20;'"
                                        :class="isSelected(hour)
                                            ? 'shadow-md ring-2 ring-accent/30'
                                            : 'hover:brightness-95'"
                                        class="w-full text-left p-3.5 rounded-xl border transition">
                                    <div class="flex items-center justify-between text-[10px] font-bold mb-1">
                                        <span x-text="getTimePeriod(hour)"></span>
                                        <span :style="isSelected(hour) ? 'color: #FBF7F0;' : 'color: #047857;'"
                                              x-text="isSelected(hour) ? '✓ DIPILIH' : '● OPEN'"></span>
                                    </div>
                                    <div class="text-sm font-bold" x-text="formatHour(hour) + ' - ' + formatHour(hour + 1)"></div>
                                    <div class="text-[11px] mt-1 font-semibold"
                                         :style="isSelected(hour) ? 'color: #FBF7F0;' : 'color: #065F46;'"
                                         x-text="isSelected(hour) ? 'Klik untuk batal' : 'Rp ' + formatNumber(currentField.price_per_hour)"></div>
                                </button>
                            </template>
                        </div>
                    </template>
                </div>
            </div>

            <div class="wf-box rounded-2xl p-6">
                <span class="block text-[11px] uppercase tracking-wider text-accent font-bold">
                    3. IDENTITAS PEMESAN (WAJIB DIISI)
                </span>
                <h2 class="text-xl font-bold text-ink mt-0.5 mb-5">Data Penanggung Jawab Tim</h2>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="customer_name" class="block text-xs font-bold text-ink mb-1.5">
                            Nama Lengkap Pemesan <span class="text-rose-600">*</span>
                        </label>
                        <input type="text" id="customer_name" form="bookingForm" name="customer_name"
                               x-model="customerName" required
                               value="{{ old('customer_name') }}"
                               placeholder="Wajib diisi"
                               class="w-full rounded-xl border-ink/25 bg-paper text-sm text-ink placeholder:text-ink/40 focus:border-accent focus:ring-accent">
                    </div>

                    <div>
                        <label for="customer_phone" class="block text-xs font-bold text-ink mb-1.5">
                            Nomor WhatsApp Aktif <span class="text-rose-600">*</span>
                        </label>
                        <input type="tel" id="customer_phone" form="bookingForm" name="customer_phone"
                               x-model="customerPhone" required
                               value="{{ old('customer_phone') }}"
                               placeholder="Wajib diisi"
                               class="w-full rounded-xl border-ink/25 bg-paper text-sm text-ink placeholder:text-ink/40 focus:border-accent focus:ring-accent">
                    </div>

                    <div class="sm:col-span-2">
                        <label for="team_name" class="block text-xs font-bold text-ink mb-1.5">
                            Nama Tim / Komunitas / Acara (Opsional)
                        </label>
                        <input type="text" id="team_name" form="bookingForm" name="team_name"
                               value="{{ old('team_name') }}"
                               placeholder="Wajib diisi"
                               class="w-full rounded-xl border-ink/25 bg-paper text-sm text-ink placeholder:text-ink/40 focus:border-accent focus:ring-accent">
                    </div>
                </div>
            </div>

        </div>

        <div class="lg:col-span-4 lg:sticky lg:top-24">
            <form id="bookingForm" action="{{ route('booking.store') }}" method="POST" @submit="validateBeforeSubmit($event)"
                  class="wf-box rounded-2xl p-6 space-y-5">
                @csrf
                <input type="hidden" name="field_id" :value="selectedFieldId">
                <input type="hidden" name="payment_scheme" :value="paymentScheme">

                <template x-for="slotKey in selectedSlots" :key="slotKey">
                    <input type="hidden" name="slots[]" :value="slotKey">
                </template>

                <div>
                    <h2 class="text-xl font-bold text-ink">Ringkasan Pesanan</h2>
                    <p class="text-xs text-ink/70 mt-0.5" x-text="currentField.name"></p>
                </div>

                <div class="p-4 rounded-xl bg-cream/70 border border-ink/15 min-h-[5rem]">
                    <template x-if="selectedSlots.length === 0">
                        <p class="text-xs text-ink/60 text-center py-3">
                            Belum ada slot jam yang dipilih.<br>Silakan klik slot hijau pada jadwal.
                        </p>
                    </template>

                    <template x-if="selectedSlots.length > 0">
                        <div class="space-y-2 max-h-52 overflow-y-auto pr-1">
                            <template x-for="slotKey in selectedSlots" :key="slotKey">
                                <div class="flex items-center justify-between text-xs bg-paper px-3 py-2 rounded-lg border border-ink/15">
                                    <div>
                                        <span class="font-bold text-ink" x-text="formatSlotLabel(slotKey)"></span>
                                        <span class="block text-[11px] text-ink/70" x-text="'Rp ' + formatNumber(currentField.price_per_hour)"></span>
                                    </div>
                                    <button type="button" @click="removeSlot(slotKey)" class="text-rose-700 hover:text-rose-900 font-bold px-1.5">
                                        ✕
                                    </button>
                                </div>
                            </template>
                        </div>
                    </template>
                </div>

                <div>
                    <span class="block text-[11px] uppercase tracking-wider text-ink/70 font-bold mb-2">
                        SKEMA PEMBAYARAN QRIS
                    </span>
                    <div class="grid grid-cols-2 gap-2.5">
                        <button type="button" @click="paymentScheme = 'dp'"
                                :class="paymentScheme === 'dp' ? 'border-accent bg-accent/10 ring-2 ring-accent/30' : 'border-ink/20 bg-paper hover:bg-cream/50'"
                                class="p-3 rounded-xl border text-left transition">
                            <span class="block text-xs font-extrabold text-ink">DP 50% Dulu</span>
                            <span class="block text-[10px] text-ink/70 mt-0.5">Sisa dilunasi di GOR</span>
                        </button>

                        <button type="button" @click="paymentScheme = 'full'"
                                :class="paymentScheme === 'full' ? 'border-accent bg-accent/10 ring-2 ring-accent/30' : 'border-ink/20 bg-paper hover:bg-cream/50'"
                                class="p-3 rounded-xl border text-left transition">
                            <span class="block text-xs font-extrabold text-ink">Bayar Lunas 100%</span>
                            <span class="block text-[10px] text-ink/70 mt-0.5">Langsung beres tanpa antre</span>
                        </button>
                    </div>
                </div>

                <div class="p-4 rounded-xl bg-cream border border-ink/20 space-y-2">
                    <div class="flex items-center justify-between text-xs text-ink/80">
                        <span>Total Biaya (<span x-text="selectedSlots.length"></span> Jam)</span>
                        <span class="font-bold" x-text="'Rp ' + formatNumber(totalAmount)"></span>
                    </div>

                    <div class="pt-2 border-t border-ink/15 flex items-center justify-between">
                        <span class="text-xs font-extrabold text-ink">
                            Wajib Transfer (<span x-text="paymentScheme === 'dp' ? 'DP 50%' : 'Lunas 100%'"></span>)
                        </span>
                        <span class="text-base font-extrabold text-accent" x-text="'Rp ' + formatNumber(payableNow)"></span>
                    </div>
                </div>

                <p x-show="formError" x-text="formError" class="text-xs font-semibold text-rose-700 bg-rose-50 border border-rose-300 rounded-lg px-3 py-2"></p>

                <button type="submit"
                        class="wf-btn w-full py-3.5 px-5 rounded-xl bg-accent hover:bg-accentDark text-paper text-xs sm:text-sm font-bold">
                    Lanjut ke Konfirmasi & QRIS →
                </button>
            </form>
        </div>

    </div>
</div>
@endsection

@push('scripts')
<script>
function bookingApp({ fields, initialFieldId, initialDate, serverNow }) {
    const parseServerDate = (str) => {
        if (!str) return new Date();
        const normalized = str.replace(' ', 'T');
        const parsed = new Date(normalized);
        return isNaN(parsed.getTime()) ? new Date() : parsed;
    };

    const initialServerObj = parseServerDate(serverNow);
    const serverOffsetMs = initialServerObj.getTime() - Date.now();
    const getSyncedNow = () => new Date(Date.now() + serverOffsetMs);

    const formatYmd = (d) => d.getFullYear() + '-' + String(d.getMonth() + 1).padStart(2, '0') + '-' + String(d.getDate()).padStart(2, '0');
    const formatHm = (d) => String(d.getHours()).padStart(2, '0') + ':' + String(d.getMinutes()).padStart(2, '0');

    const nowInit = getSyncedNow();
    const todayStr = formatYmd(nowInit);
    const validInitDate = (initialDate && initialDate >= todayStr) ? initialDate : todayStr;
    const initParts = validInitDate.split('-');

    return {
        fields: fields,
        selectedFieldId: initialFieldId || (fields[0] ? fields[0].id : 0),
        selectedDate: validInitDate,
        todayStr: todayStr,
        currentHour: nowInit.getHours(),
        currentTimeLabel: formatHm(nowInit),
        viewYear: parseInt(initParts[0], 10),
        viewMonth: parseInt(initParts[1], 10) - 1,
        availableYears: [nowInit.getFullYear(), nowInit.getFullYear() + 1, nowInit.getFullYear() + 2],
        monthNames: ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'],
        calendarCells: [],
        allHours: Array.from({ length: 24 }, (_, i) => i),
        bookedHours: [],
        pastHours: [],
        fieldStatus: 'active',
        loadingSlots: false,
        selectedSlots: [],
        paymentScheme: 'dp',
        customerName: @json(old('customer_name', '')),
        customerPhone: @json(old('customer_phone', '')),
        formError: '',
        slotNotice: '',
        noticeTimer: null,

        get currentField() {
            return this.fields.find(f => f.id === this.selectedFieldId) || {
                id: 0, name: 'Lapangan', price_per_hour: 0, open_hour: 8, close_hour: 23, status: 'active'
            };
        },

        get nextAvailableHourLabel() {
            const nextH = Math.max(this.currentField.open_hour, this.currentHour + 1);
            return this.formatHour(Math.min(nextH, 24));
        },

        get allTodaySlotsPassed() {
            if (this.selectedDate !== this.todayStr) return false;
            return (this.currentHour + 1) >= this.currentField.close_hour;
        },

        get totalAmount() {
            return this.selectedSlots.length * (this.currentField.price_per_hour || 0);
        },

        get payableNow() {
            return this.paymentScheme === 'dp' ? Math.round(this.totalAmount * 0.5) : this.totalAmount;
        },

        init() {
            this.buildCalendar();
            this.fetchSlots();
            setInterval(() => this.syncRealtimeClock(), 15000);
        },

        syncRealtimeClock() {
            const now = getSyncedNow();
            const newToday = formatYmd(now);
            const newHour = now.getHours();
            this.currentTimeLabel = formatHm(now);

            const dateChanged = newToday !== this.todayStr;
            const hourChanged = newHour !== this.currentHour;

            if (dateChanged) {
                this.todayStr = newToday;
                if (this.selectedDate < this.todayStr) {
                    this.selectedDate = this.todayStr;
                    this.fetchSlots();
                }
                this.buildCalendar();
            }

            if (hourChanged || dateChanged) {
                this.currentHour = newHour;
                this.pruneExpiredSelections();
            }
        },

        pruneExpiredSelections() {
            const beforeCount = this.selectedSlots.length;
            this.selectedSlots = this.selectedSlots.filter(key => {
                const [dStr, hStr] = key.split('|');
                if (dStr < this.todayStr) return false;
                if (dStr === this.todayStr) {
                    const h = parseInt(hStr.split(':')[0], 10);
                    if (h <= this.currentHour) return false;
                }
                return true;
            });

            if (this.selectedSlots.length < beforeCount) {
                this.showNotice(`Slot yang waktunya baru saja terlewat (${this.currentTimeLabel} WIB) otomatis dilepas dari pilihan.`);
            }
        },

        showNotice(msg) {
            this.slotNotice = msg;
            if (this.noticeTimer) clearTimeout(this.noticeTimer);
            this.noticeTimer = setTimeout(() => {
                this.slotNotice = '';
            }, 5000);
        },

        buildCalendar() {
            const firstDay = new Date(this.viewYear, this.viewMonth, 1).getDay();
            const daysInMonth = new Date(this.viewYear, this.viewMonth + 1, 0).getDate();
            const cells = [];

            for (let i = 0; i < firstDay; i++) {
                cells.push({ day: null, dateStr: null, isPast: false });
            }

            for (let d = 1; d <= daysInMonth; d++) {
                const dateStr = `${this.viewYear}-${String(this.viewMonth + 1).padStart(2, '0')}-${String(d).padStart(2, '0')}`;
                cells.push({
                    day: d,
                    dateStr: dateStr,
                    isPast: dateStr < this.todayStr
                });
            }

            this.calendarCells = cells;
        },

        prevMonth() {
            if (this.viewMonth === 0) {
                if (this.availableYears.includes(this.viewYear - 1)) {
                    this.viewYear--;
                    this.viewMonth = 11;
                }
            } else {
                this.viewMonth--;
            }
            this.buildCalendar();
        },

        nextMonth() {
            if (this.viewMonth === 11) {
                if (this.availableYears.includes(this.viewYear + 1)) {
                    this.viewYear++;
                    this.viewMonth = 0;
                }
            } else {
                this.viewMonth++;
            }
            this.buildCalendar();
        },

        goToday() {
            const now = getSyncedNow();
            this.todayStr = formatYmd(now);
            this.currentHour = now.getHours();
            this.currentTimeLabel = formatHm(now);
            this.viewYear = now.getFullYear();
            this.viewMonth = now.getMonth();
            this.selectedDate = this.todayStr;
            this.slotNotice = '';
            this.buildCalendar();
            this.fetchSlots();
        },

        goTomorrow() {
            const now = getSyncedNow();
            const tomorrow = new Date(now.getFullYear(), now.getMonth(), now.getDate() + 1);
            this.viewYear = tomorrow.getFullYear();
            this.viewMonth = tomorrow.getMonth();
            this.selectedDate = formatYmd(tomorrow);
            this.slotNotice = '';
            this.buildCalendar();
            this.fetchSlots();
        },

        selectDate(dateStr) {
            if (dateStr < this.todayStr) return;
            this.selectedDate = dateStr;
            this.slotNotice = '';
            this.fetchSlots();
        },

        onFieldChange() {
            this.selectedSlots = [];
            this.slotNotice = '';
            this.fetchSlots();
        },

        async fetchSlots() {
            if (!this.selectedFieldId) return;
            this.loadingSlots = true;
            try {
                const res = await fetch(`{{ route('api.slots') }}?field_id=${this.selectedFieldId}&date=${this.selectedDate}`);
                const data = await res.json();
                this.fieldStatus = data.status || 'active';
                this.bookedHours = data.booked_hours || [];
                this.pastHours = data.past_hours || [];
                if (data.server_date) {
                    this.todayStr = data.server_date;
                }
                if (typeof data.server_hour === 'number') {
                    this.currentHour = data.server_hour;
                }
                this.pruneExpiredSelections();
            } catch (e) {
                this.bookedHours = [];
                this.pastHours = [];
            } finally {
                this.loadingSlots = false;
            }
        },

        isOperationalHour(hour) {
            return hour >= this.currentField.open_hour && hour < this.currentField.close_hour;
        },

        isPastHour(hour) {
            if (this.selectedDate < this.todayStr) return true;
            if (this.selectedDate > this.todayStr) return false;
            const hStr = this.formatHour(hour);
            return hour <= this.currentHour || this.pastHours.includes(hStr);
        },

        notifyPastSlot(hour) {
            this.showNotice(`Jam ${this.formatHour(hour)} - ${this.formatHour(hour + 1)} sudah terlewat dari waktu saat ini (${this.currentTimeLabel} WIB) dan tidak dapat dipesan.`);
        },

        isBooked(hour) {
            const hStr = this.formatHour(hour);
            return this.bookedHours.includes(hStr);
        },

        slotKey(hour) {
            return `${this.selectedDate}|${this.formatHour(hour)}`;
        },

        isSelected(hour) {
            return this.selectedSlots.includes(this.slotKey(hour));
        },

        toggleSlot(hour) {
            if (this.isPastHour(hour)) {
                this.notifyPastSlot(hour);
                return;
            }
            const key = this.slotKey(hour);
            if (this.selectedSlots.includes(key)) {
                this.selectedSlots = this.selectedSlots.filter(s => s !== key);
            } else {
                this.selectedSlots = [...this.selectedSlots, key].sort();
            }
            this.formError = '';
            this.slotNotice = '';
        },

        removeSlot(key) {
            this.selectedSlots = this.selectedSlots.filter(s => s !== key);
        },

        countSelectedOnDate(dateStr) {
            return this.selectedSlots.filter(s => s.startsWith(dateStr + '|')).length;
        },

        formatHour(hour) {
            return String(hour).padStart(2, '0') + ':00';
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

        formatSlotLabel(key) {
            const [dStr, hStr] = key.split('|');
            const h = parseInt(hStr.split(':')[0], 10);
            return `${this.formatDateIndo(dStr)} • ${this.formatHour(h)} - ${this.formatHour(h + 1)}`;
        },

        formatNumber(num) {
            return new Intl.NumberFormat('id-ID').format(num || 0);
        },

        validateBeforeSubmit(e) {
            this.syncRealtimeClock();
            if (this.selectedSlots.length === 0) {
                e.preventDefault();
                this.formError = 'Pilih minimal 1 slot jam yang masih tersedia sebelum melanjutkan.';
                return false;
            }
            if (!this.customerName.trim() || !this.customerPhone.trim()) {
                e.preventDefault();
                this.formError = 'Nama lengkap pemesan dan nomor WhatsApp aktif wajib diisi.';
                return false;
            }
            this.formError = '';
            return true;
        }
    };
}
</script>
@endpush
