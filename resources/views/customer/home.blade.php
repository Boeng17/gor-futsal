@extends('layouts.app')

@section('title', 'Beranda — GOR Futsal Nusantara')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-10 space-y-14">

    @if($banners->count() > 0)
        <section x-data="{
            active: 0,
            total: {{ $banners->count() }},
            next() { this.active = (this.active + 1) % this.total },
            prev() { this.active = (this.active - 1 + this.total) % this.total }
        }" class="wf-box rounded-3xl p-6 sm:p-8">
            @foreach($banners as $idx => $banner)
                <div x-show="active === {{ $idx }}" x-transition.opacity.duration.300ms>
                    <div class="flex flex-wrap items-center justify-between gap-2 text-xs text-ink/75 mb-2">
                        <span class="uppercase tracking-wider font-bold text-accent">
                            ★ INFO EVENT & TURNAMEN • {{ $banner->created_at->translatedFormat('d F Y') }}
                        </span>
                        <span class="px-2.5 py-0.5 rounded-full bg-cream border border-ink/15 font-semibold">
                            EVENT {{ $idx + 1 }} / {{ $banners->count() }}
                        </span>
                    </div>

                    <h1 class="text-2xl sm:text-3xl lg:text-4xl font-bold text-ink mb-5 leading-tight">
                        {{ $banner->title }}
                    </h1>

                    <div class="relative rounded-2xl overflow-hidden border border-ink/20 bg-sand/50 h-64 sm:h-80 lg:h-96 mb-5">
                        @if(\App\Helpers\ImageHelper::resolveUrl($banner->image))
                            <img src="{{ \App\Helpers\ImageHelper::resolveUrl($banner->image) }}" alt="{{ $banner->title }}" class="w-full h-full object-cover">
                        @else
                            <div class="w-full h-full flex flex-col items-center justify-center p-6 bg-gradient-to-b from-[#DFD1BC] to-[#CFC0A8]">
                                <svg viewBox="0 0 600 220" class="w-full max-w-2xl h-48 text-ink" fill="none" stroke="currentColor" stroke-width="2.2">
                                    <path d="M20 45 Q300 10 580 45" />
                                    <path d="M20 60 Q300 25 580 60" stroke-dasharray="6 6" />
                                    <polygon points="140,105 460,105 510,205 90,205" fill="#95A87E" stroke="#232B20" stroke-width="2.5"/>
                                    <line x1="300" y1="105" x2="300" y2="205"/>
                                    <circle cx="300" cy="152" r="26"/>
                                    <circle cx="300" cy="152" r="3" fill="#232B20"/>
                                    <polygon points="118,135 142,130 142,168 118,175" fill="#FBF7F0" fill-opacity="0.7"/>
                                    <polygon points="482,135 458,130 458,168 482,175" fill="#FBF7F0" fill-opacity="0.7"/>
                                </svg>
                                <span class="text-xs tracking-wider uppercase text-ink/75 mt-2">
                                    GOR Futsal Nusantara — Arena Resmi Turnamen & Komunitas
                                </span>
                            </div>
                        @endif
                    </div>

                    <p class="text-sm text-ink/85 leading-relaxed max-w-4xl mb-6">
                        {{ Str::limit($banner->content, 240) }}
                    </p>

                    <div class="flex flex-wrap items-center justify-between gap-4">
                        <div class="flex flex-wrap items-center gap-3">
                            <a href="{{ route('banners.show', $banner) }}"
                               class="wf-btn px-5 py-2.5 rounded-full bg-accent hover:bg-accentDark text-paper text-xs font-bold">
                                Baca Detail Event & Promo →
                            </a>
                            <a href="#daftar-lapangan"
                               class="wf-btn px-5 py-2.5 rounded-full bg-ink hover:bg-ink/90 text-paper text-xs font-bold">
                                Pilih Lapangan & Booking ↓
                            </a>
                        </div>

                        @if($banners->count() > 1)
                            <div class="flex items-center gap-2">
                                <button @click="prev()" type="button"
                                        class="px-3.5 py-1.5 rounded-full bg-cream hover:bg-sand border border-ink/20 text-xs font-semibold">
                                    ← Prev
                                </button>
                                <button @click="next()" type="button"
                                        class="px-3.5 py-1.5 rounded-full bg-cream hover:bg-sand border border-ink/20 text-xs font-semibold">
                                    Next →
                                </button>
                            </div>
                        @endif
                    </div>
                </div>
            @endforeach
        </section>
    @endif

    <section id="daftar-lapangan" class="scroll-mt-24">
        <div class="flex flex-col md:flex-row md:items-end justify-between gap-4 mb-8">
            <div>
                <span class="block text-xs uppercase tracking-widest text-accent font-bold mb-1">
                    PILIH ARENA BERTANDING
                </span>
                <h2 class="text-3xl sm:text-4xl font-bold text-ink">
                    Daftar Lapangan & Tarif Sewa
                </h2>
            </div>

            <a href="{{ route('booking.create') }}"
               class="wf-btn self-start md:self-auto px-5 py-2.5 rounded-full bg-paper hover:bg-cream border border-ink/25 text-ink text-xs font-bold">
                Buka Kalender Tahunan & Multi-Booking →
            </a>
        </div>

        @if($fields->count() > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($fields as $field)
                    <x-court-card :field="$field" />
                @endforeach
            </div>
        @else
            <div class="wf-box rounded-2xl p-10 text-center">
                <p class="text-base font-bold text-ink mb-1">Belum ada data lapangan tersedia.</p>
                <p class="text-xs text-ink/70">Admin dapat menambahkan data lapangan melalui panel manajemen lapangan.</p>
            </div>
        @endif
    </section>

    <section class="wf-box rounded-3xl p-6 sm:p-10">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8 divide-y md:divide-y-0 md:divide-x divide-ink/15">
            <div class="pt-4 md:pt-0 md:pr-6 first:pt-0">
                <span class="block text-[11px] uppercase tracking-wider text-accent font-bold mb-2">
                    01. JADWAL ANTI BENTROK
                </span>
                <h3 class="text-xl font-bold text-ink mb-2">Kalender Slot Real-Time</h3>
                <p class="text-xs text-ink/80 leading-relaxed">
                    Sistem mengunci otomatis slot yang sudah dibooking atau sedang dalam perawatan berkala langsung dari database.
                </p>
            </div>

            <div class="pt-6 md:pt-0 md:px-6">
                <span class="block text-[11px] uppercase tracking-wider text-accent font-bold mb-2">
                    02. MULTI-JAM & LINTAS HARI
                </span>
                <h3 class="text-xl font-bold text-ink mb-2">Fleksibel untuk Sparring & Event</h3>
                <p class="text-xs text-ink/80 leading-relaxed">
                    Pesan beberapa jam sekaligus atau beberapa tanggal berbeda dalam kalender tahunan untuk kebutuhan turnamen komunitas.
                </p>
            </div>

            <div class="pt-6 md:pt-0 md:pl-6">
                <span class="block text-[11px] uppercase tracking-wider text-accent font-bold mb-2">
                    03. QRIS DP 50% ATAU LUNAS
                </span>
                <h3 class="text-xl font-bold text-ink mb-2">E-Invoice Digital Instan</h3>
                <p class="text-xs text-ink/80 leading-relaxed">
                    Bayar DP 50% untuk mengamankan jadwal atau lunasi 100% via QRIS dengan kode E-Invoice otomatis.
                </p>
            </div>
        </div>
    </section>

</div>
@endsection
