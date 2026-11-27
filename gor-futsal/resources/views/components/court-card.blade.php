@props(['field'])

@php
    $isMaint = $field->status === 'maintenance';
@endphp

<article class="wf-box rounded-2xl overflow-hidden flex flex-col justify-between transition duration-200 hover:-translate-y-0.5">
    <div>
        <div class="relative h-52 bg-sand/60 border-b border-ink/15 overflow-hidden">
            @if($field->image)
                <img src="{{ \App\Helpers\ImageHelper::resolveUrl($field->image, 'field', $field->id) }}"
                     alt="{{ $field->name }}"
                     class="w-full h-full object-cover">
            @else
                <div class="w-full h-full flex flex-col items-center justify-center p-6 bg-gradient-to-br from-[#D6C8B3] to-[#E2D6C6]">
                    <svg viewBox="0 0 240 120" class="w-48 h-24 text-ink/75" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="10" y="10" width="220" height="100" rx="4" fill="#9CAF88" fill-opacity="0.45"/>
                        <line x1="120" y1="10" x2="120" y2="110"/>
                        <circle cx="120" cy="60" r="18"/>
                        <circle cx="120" cy="60" r="2.5" fill="currentColor"/>
                        <path d="M10 35 H38 V85 H10"/>
                        <path d="M230 35 H202 V85 H230"/>
                    </svg>
                    <span class="mt-2 text-[10px] uppercase tracking-widest text-ink/70">
                        Arena Resmi GOR Nusantara
                    </span>
                </div>
            @endif

            <div class="absolute top-3 left-3">
                @if($isMaint)
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[11px] font-bold bg-amber-200 text-amber-950 border border-amber-800/30 shadow-sm">
                        <span class="w-2 h-2 rounded-full bg-amber-600"></span>
                        MAINTENANCE
                    </span>
                @else
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[11px] font-bold bg-emerald-100 text-emerald-950 border border-emerald-800/30 shadow-sm">
                        <span class="w-2 h-2 rounded-full bg-emerald-600"></span>
                        AKTIF & SIAP MAIN
                    </span>
                @endif
            </div>

            <div class="absolute bottom-3 right-3">
                <span class="px-2.5 py-1 rounded-lg text-[10px] font-semibold bg-ink/90 text-paper">
                    {{ str_pad((string)$field->open_hour_value, 2, '0', STR_PAD_LEFT) }}:00 - {{ str_pad((string)$field->close_hour_value, 2, '0', STR_PAD_LEFT) }}:00 WIB
                </span>
            </div>
        </div>

        <div class="p-6">
            <div class="flex items-start justify-between gap-2 mb-2">
                <h3 class="text-xl font-bold text-ink leading-snug">
                    {{ $field->name }}
                </h3>
            </div>

            <p class="text-xs text-ink/75 leading-relaxed mb-5 min-h-[3rem]">
                {{ $field->description ?: 'Lapangan futsal standar turnamen dengan pencahayaan merata dan sirkulasi udara optimal.' }}
            </p>

            <div class="p-3.5 rounded-xl bg-cream/70 border border-ink/15 flex items-baseline justify-between">
                <span class="text-[11px] uppercase tracking-wider text-ink/70">Tarif Sewa</span>
                <div>
                    <span class="text-lg font-extrabold text-ink">Rp {{ number_format($field->price_per_hour, 0, ',', '.') }}</span>
                    <span class="text-xs text-ink/70">/ jam</span>
                </div>
            </div>
        </div>
    </div>

    <div class="px-6 pb-6 pt-1 grid grid-cols-1 sm:grid-cols-2 gap-2.5">
        <a href="{{ route('booking.create', ['field_id' => $field->id]) }}"
           class="wf-btn text-center px-3.5 py-2.5 rounded-xl border border-ink/25 bg-cream hover:bg-sand/70 text-ink text-xs font-bold">
            Lihat Ketersediaan Jam
        </a>

        @if($isMaint)
            <button type="button" disabled
                    class="px-3.5 py-2.5 rounded-xl bg-ink/20 text-ink/50 text-xs font-bold cursor-not-allowed">
                Sedang Perawatan
            </button>
        @else
            <a href="{{ route('booking.create', ['field_id' => $field->id, 'booking' => 1]) }}"
               class="wf-btn text-center px-3.5 py-2.5 rounded-xl bg-accent hover:bg-accentDark text-paper text-xs font-bold">
                Booking Sekarang →
            </a>
        @endif
    </div>
</article>
