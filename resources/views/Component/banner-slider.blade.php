@php
    $banners = [
        [
            'badge' => 'TURNAMEN ANTAR KOMUNITAS',
            'period' => '28 – 30 NOVEMBER 2026',
            'title' => 'Nusantara Futsal Cup 2026 — Total Hadiah Rp 15.000.000',
            'subtitle' => 'Pendaftaran tim dibuka untuk kategori Umum & Mahasiswa. Pertandingan digelar di Lapangan A (Rumput Sintetis FIFA Quality Pro) dan Lapangan B.',
        ],
        [
            'badge' => 'PROMO SPARRING PAGI & SIANG',
            'period' => 'SENIN – JUMAT • 08:00 – 15:00 WIB',
            'title' => 'Diskon 20% Booking Paket 2 Jam Berturut-Turut',
            'subtitle' => 'Cocok untuk latihan rutin kampus dan komunitas. Berlaku otomatis untuk pemesanan minimal 2 jam pada sesi siang melalui sistem reservasi online.',
        ],
        [
            'badge' => 'FASILITAS BARU ARENA',
            'period' => 'UPDATE NOVEMBER 2026',
            'title' => 'Upgrade Pencahayaan LED Anti-Silau & Lantai Interlock Baru',
            'subtitle' => 'Kenyamanan bertanding malam hari kini maksimal dengan standar pencahayaan 500 lux serta ruang ganti ber-AC dan loker gratis.',
        ],
    ];
@endphp

<section x-data="{
        active: 0,
        banners: @js($banners),
        next() { this.active = (this.active + 1) % this.banners.length },
        prev() { this.active = (this.active - 1 + this.banners.length) % this.banners.length }
    }"
    class="max-w-7xl mx-auto px-4 sm:px-8 pt-6 sm:pt-8">
    <div class="wf-box rounded-2xl overflow-hidden bg-paper">
        <!-- 1. TOP HEADER: JUDUL & PERIODE EVENT -->
        <div class="px-5 sm:px-8 pt-5 pb-4 space-y-2.5">
            <div class="flex flex-wrap items-center justify-between gap-2">
                <div class="inline-flex flex-wrap items-center gap-1.5 px-3 py-1 rounded-full bg-ink text-paper font-mono text-[10px] font-bold">
                    <span x-text="'★ ' + banners[active].badge"></span>
                    <span class="text-cream/40">|</span>
                    <span class="text-amber-300" x-text="banners[active].period"></span>
                </div>
                <span class="font-mono text-[11px] font-bold text-ink/60" x-text="'EVENT ' + (active + 1) + ' / ' + banners.length"></span>
            </div>

            <h1 class="text-xl sm:text-3xl font-extrabold tracking-tight text-ink leading-snug" x-text="banners[active].title"></h1>
        </div>

        <!-- 2. FULL-WIDTH VISUAL ARENA (TANPA TEKS MENUTUPI OBJEK FOTO) -->
        <div class="w-full h-52 sm:h-72 wf-hatch border-y border-ink/25 overflow-hidden relative flex items-center justify-center">
            <svg viewBox="0 0 1120 280" class="w-full h-full block" preserveAspectRatio="xMidYMid slice">
                <defs>
                    <linearGradient id="skyGrad" x1="0" y1="0" x2="0" y2="1">
                        <stop offset="0%" stop-color="#EFE4D2"/>
                        <stop offset="100%" stop-color="#D5C3A6"/>
                    </linearGradient>
                </defs>
                <rect width="1120" height="280" fill="url(#skyGrad)"/>
                <path d="M 0 55 Q 560 10 1120 55" fill="none" stroke="#232B20" stroke-width="2.5"/>
                <path d="M 0 70 Q 560 25 1120 70" fill="none" stroke="#232B20" stroke-width="1.2" stroke-dasharray="6 6"/>
                <rect x="140" y="34" width="44" height="14" rx="3" fill="#FBF7F0" stroke="#232B20" stroke-width="1.5"/>
                <rect x="538" y="20" width="44" height="14" rx="3" fill="#FBF7F0" stroke="#232B20" stroke-width="1.5"/>
                <rect x="936" y="34" width="44" height="14" rx="3" fill="#FBF7F0" stroke="#232B20" stroke-width="1.5"/>
                <polygon points="120,255 240,125 880,125 1000,255" fill="#A9B98A" stroke="#232B20" stroke-width="2.5"/>
                <line x1="560" y1="125" x2="560" y2="255" stroke="#232B20" stroke-width="2"/>
                <ellipse cx="560" cy="188" rx="68" ry="26" fill="none" stroke="#232B20" stroke-width="2"/>
                <circle cx="560" cy="188" r="4" fill="#232B20"/>
                <polygon points="165,175 200,152 200,192 165,215" fill="#FBF7F0" fill-opacity="0.7" stroke="#232B20" stroke-width="2"/>
                <polygon points="955,175 920,152 920,192 955,215" fill="#FBF7F0" fill-opacity="0.7" stroke="#232B20" stroke-width="2"/>
                <circle cx="560" cy="105" r="24" fill="#FBF7F0" stroke="#232B20" stroke-width="2"/>
                <text x="560" y="113" text-anchor="middle" font-size="22">⚽</text>
            </svg>
        </div>

        <!-- 3. BOTTOM DESCRIPTION, CTA & SLIDER CONTROLS -->
        <div class="px-5 sm:px-8 py-5 space-y-4">
            <p class="text-xs sm:text-sm text-ink/80 leading-relaxed max-w-4xl" x-text="banners[active].subtitle"></p>

            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pt-3 border-t border-ink/15">
                <div class="flex flex-wrap items-center gap-2.5">
                    <a href="{{ route('event.index') }}" class="px-5 py-2.5 rounded-xl bg-accent hover:bg-accentDark text-white text-xs font-extrabold wf-btn text-center">
                        🎉 Baca Detail Event & Promo →
                    </a>
                    <a href="#home-court-grid" class="px-5 py-2.5 rounded-xl bg-ink hover:bg-ink/90 text-paper text-xs font-bold wf-btn text-center">
                        Pilih Lapangan & Booking ↓
                    </a>
                </div>

                <div class="flex items-center justify-between sm:justify-end gap-3">
                    <div class="flex items-center gap-1.5">
                        <template x-for="(b, idx) in banners" :key="idx">
                            <button @click="active = idx"
                                    :class="active === idx ? 'w-7 bg-ink' : 'w-2.5 bg-ink/25 hover:bg-ink/40'"
                                    class="h-2.5 rounded-full transition-all"></button>
                        </template>
                    </div>
                    <div class="flex items-center gap-1.5">
                        <button @click="prev()" class="px-3 py-1.5 rounded-lg border border-ink text-xs font-bold hover:bg-cream/60">← Prev</button>
                        <button @click="next()" class="px-3 py-1.5 rounded-lg border border-ink text-xs font-bold hover:bg-cream/60">Next →</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
