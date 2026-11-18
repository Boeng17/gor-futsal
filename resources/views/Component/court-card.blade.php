@props(['court'])

<article class="wf-box rounded-2xl overflow-hidden flex flex-col justify-between">
    <div>
        <!-- Visual Ilustrasi Lapangan -->
        <div class="relative w-full h-40 border-b border-ink/25 overflow-hidden {{ $court['maintenance'] ? 'slot-maint-hatch' : 'wf-hatch' }}">
            <svg viewBox="0 0 400 160" class="w-full h-full block">
                <rect x="28" y="18" width="344" height="124" rx="8"
                      fill="{{ $court['style'] === 'turf' ? '#7E9965' : '#6A8396' }}"
                      stroke="#232B20" stroke-width="2"/>
                <line x1="200" y1="18" x2="200" y2="142" stroke="#FBF7F0" stroke-width="2"/>
                <circle cx="200" cy="80" r="24" fill="none" stroke="#FBF7F0" stroke-width="2"/>
                <rect x="28" y="48" width="44" height="64" fill="none" stroke="#FBF7F0" stroke-width="2"/>
                <rect x="328" y="48" width="44" height="64" fill="none" stroke="#FBF7F0" stroke-width="2"/>
            </svg>

            <div class="absolute top-2.5 left-2.5 flex items-center gap-1.5">
                <span class="px-2.5 py-0.5 rounded-full bg-ink text-paper font-mono text-[10px] font-bold">
                    {{ $court['floor'] }}
                </span>
            </div>

            @if($court['maintenance'])
                <div class="absolute bottom-2 left-2.5 right-2.5 px-2.5 py-1 rounded-lg bg-amber-950/90 text-amber-200 font-mono text-[10px] text-center font-bold">
                    🛠️ PERAWATAN BERKALA (SEBAGIAN JAM DIBLOKIR)
                </div>
            @endif
        </div>

        <!-- Info Lapangan -->
        <div class="p-5 space-y-3">
            <div class="flex items-start justify-between gap-2">
                <div>
                    <h3 class="text-lg font-extrabold text-ink">{{ $court['name'] }}</h3>
                    <p class="text-xs text-ink/70">{{ $court['detail'] }} • {{ $court['size'] }}</p>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-2 pt-1 text-xs">
                <div class="p-2.5 rounded-xl bg-cream/55 border border-ink/15">
                    <div class="text-[10px] font-mono text-ink/60">SIANG (08–17)</div>
                    <div class="font-extrabold font-mono text-ink">Rp {{ number_format($court['price_day'], 0, ',', '.') }}/jam</div>
                </div>
                <div class="p-2.5 rounded-xl bg-cream/55 border border-ink/15">
                    <div class="text-[10px] font-mono text-ink/60">MALAM (18–23)</div>
                    <div class="font-extrabold font-mono text-ink">Rp {{ number_format($court['price_night'], 0, ',', '.') }}/jam</div>
                </div>
            </div>
        </div>
    </div>

    <div class="px-5 pb-5 pt-2 flex items-center gap-2">
        <a href="{{ route('ketersediaan', ['lapangan' => $court['id']]) }}"
           class="flex-1 py-2.5 px-4 rounded-xl bg-accent hover:bg-accentDark text-white text-xs font-extrabold text-center wf-btn">
            Lihat Jadwal & Booking →
        </a>
        <a href="{{ route('lapangan.index') }}#{{ $court['id'] }}"
           class="py-2.5 px-3 rounded-xl border border-ink/30 hover:bg-cream/60 text-xs font-bold text-ink">
            Spek
        </a>
    </div>
</article>
