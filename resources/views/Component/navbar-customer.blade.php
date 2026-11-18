@php
    $activeEventsCount = \App\Models\Banner::where('is_active', true)->count();
@endphp

<header x-data="{ open: false }" class="sticky top-0 z-40 bg-paper/95 backdrop-blur border-b border-ink/15 no-print">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-20 gap-4">
            <a href="{{ route('home') }}" class="flex items-center gap-3 shrink-0">
                <div class="w-10 h-10 rounded-xl bg-ink text-paper flex items-center justify-center font-extrabold text-xs tracking-wider shadow-sm">
                    GOR
                </div>
                <div>
                    <div class="text-sm sm:text-base font-extrabold tracking-tight text-ink leading-none">
                        GOR FUTSAL NUSANTARA
                    </div>
                    <div class="text-[11px] font-medium tracking-wide text-ink/65 mt-1 uppercase">
                        Sistem Reservasi & Penjadwalan
                    </div>
                </div>
            </a>

            <nav class="hidden lg:flex items-center gap-1 xl:gap-2">
                <a href="{{ route('home') }}"
                   class="px-3.5 py-2 rounded-full text-xs font-semibold transition {{ request()->routeIs('home') ? 'bg-ink text-paper' : 'text-ink/80 hover:bg-cream/70 hover:text-ink' }}">
                    Home
                </a>

                <a href="{{ route('lapangan.index') }}"
                   class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-full text-xs font-semibold transition {{ request()->routeIs('lapangan.*') ? 'bg-ink text-paper' : 'text-ink/80 hover:bg-cream/70 hover:text-ink' }}">
                    <span>Lapangan</span>
                    <span class="px-1.5 py-0.5 text-[10px] rounded bg-sand text-ink font-bold">SPEK</span>
                </a>

                <a href="{{ route('our-story') }}"
                   class="px-3.5 py-2 rounded-full text-xs font-semibold transition {{ request()->routeIs('our-story') ? 'bg-ink text-paper' : 'text-ink/80 hover:bg-cream/70 hover:text-ink' }}">
                    Our Story
                </a>

                <a href="{{ route('event.index') }}"
                   class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-full text-xs font-semibold transition {{ request()->routeIs('event.*') || request()->routeIs('banners.*') ? 'bg-ink text-paper' : 'text-ink/80 hover:bg-cream/70 hover:text-ink' }}">
                    <span>Event</span>
                    @if($activeEventsCount > 0)
                        <span class="px-1.5 py-0.5 text-[10px] rounded-full bg-amber-300 text-ink font-bold">
                            {{ $activeEventsCount }}
                        </span>
                    @endif
                </a>

                <a href="{{ route('bantuan') }}"
                   class="px-3.5 py-2 rounded-full text-xs font-semibold transition {{ request()->routeIs('bantuan') ? 'bg-ink text-paper' : 'text-ink/80 hover:bg-cream/70 hover:text-ink' }}">
                    Bantuan & Kontak
                </a>
            </nav>

            <div class="hidden sm:flex items-center gap-2.5 shrink-0">
                <a href="{{ route('booking.track') }}"
                   class="wf-btn px-4 py-2 rounded-full bg-amber-300 hover:bg-amber-400 text-ink border border-ink/20 text-xs font-bold">
                    Cek Pesanan
                </a>

                <a href="{{ route('booking.create') }}"
                   class="wf-btn px-4 py-2 rounded-full bg-accent hover:bg-accentDark text-paper text-xs font-semibold">
                    Booking Jadwal
                </a>
            </div>

            <button @click="open = !open" type="button"
                    class="lg:hidden p-2 rounded-xl border border-ink/20 bg-cream/60 text-ink focus:outline-none">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path x-show="!open" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                    <path x-show="open" x-cloak stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        <div x-show="open" x-cloak class="lg:hidden pb-4 pt-2 border-t border-ink/10 space-y-1.5">
            <a href="{{ route('home') }}" class="block px-3 py-2 rounded-lg text-xs font-semibold {{ request()->routeIs('home') ? 'bg-ink text-paper' : 'text-ink' }}">Home</a>
            <a href="{{ route('lapangan.index') }}" class="block px-3 py-2 rounded-lg text-xs font-semibold {{ request()->routeIs('lapangan.*') ? 'bg-ink text-paper' : 'text-ink' }}">Lapangan (Spek)</a>
            <a href="{{ route('our-story') }}" class="block px-3 py-2 rounded-lg text-xs font-semibold {{ request()->routeIs('our-story') ? 'bg-ink text-paper' : 'text-ink' }}">Our Story</a>
            <a href="{{ route('event.index') }}" class="flex items-center justify-between px-3 py-2 rounded-lg text-xs font-semibold {{ request()->routeIs('event.*') ? 'bg-ink text-paper' : 'text-ink' }}">
                <span>Event & Turnamen</span>
                @if($activeEventsCount > 0)
                    <span class="px-2 py-0.5 text-[10px] rounded-full bg-amber-300 text-ink font-bold">{{ $activeEventsCount }}</span>
                @endif
            </a>
            <a href="{{ route('bantuan') }}" class="block px-3 py-2 rounded-lg text-xs font-semibold {{ request()->routeIs('bantuan') ? 'bg-ink text-paper' : 'text-ink' }}">Bantuan & Kontak</a>
            <div class="pt-2 flex flex-wrap gap-2">
                <a href="{{ route('booking.track') }}" class="flex-1 text-center px-3 py-2 rounded-full bg-amber-300 text-ink text-xs font-bold border border-ink/20">Cek Pesanan</a>
                <a href="{{ route('booking.create') }}" class="flex-1 text-center px-3 py-2 rounded-full bg-accent text-paper text-xs font-semibold">Booking Jadwal</a>
            </div>
        </div>
    </div>
</header>
