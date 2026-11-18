@extends('layouts.app')

@section('title', 'Event & Turnamen — GOR Futsal Nusantara')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 space-y-8">
    <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4">
        <div>
            <span class="block text-xs uppercase tracking-widest text-accent font-bold mb-1">
                AGENDA KOMPETISI & PROMO
            </span>
            <h1 class="text-3xl sm:text-4xl font-bold text-ink">Event & Turnamen Aktif</h1>
        </div>
        <a href="{{ route('booking.create') }}" class="wf-btn px-5 py-2.5 rounded-full bg-accent text-paper text-xs font-bold">
            Booking Jadwal untuk Event Indie →
        </a>
    </div>

    @if($banners->count() > 0)
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            @foreach($banners as $banner)
                <article class="wf-box rounded-2xl overflow-hidden flex flex-col justify-between">
                    <div>
                        <div class="h-56 bg-sand/50 border-b border-ink/15 overflow-hidden">
                            @if(\App\Helpers\ImageHelper::resolveUrl($banner->image))
                                <img src="{{ \App\Helpers\ImageHelper::resolveUrl($banner->image) }}" alt="{{ $banner->title }}" class="w-full h-full object-cover">
                            @else
                                <div class="w-full h-full flex items-center justify-center bg-cream text-xs text-ink/60">
                                    BANNER EVENT GOR NUSANTARA
                                </div>
                            @endif
                        </div>
                        <div class="p-6">
                            <span class="text-[11px] text-accent font-bold">
                                {{ $banner->created_at->translatedFormat('d F Y') }}
                            </span>
                            <h2 class="text-xl font-bold text-ink mt-1 mb-2">{{ $banner->title }}</h2>
                            <p class="text-xs text-ink/80 leading-relaxed">{{ Str::limit($banner->content, 180) }}</p>
                        </div>
                    </div>
                    <div class="px-6 pb-6">
                        <a href="{{ route('banners.show', $banner) }}" class="wf-btn inline-block px-4 py-2 rounded-xl bg-ink text-paper text-xs font-bold">
                            Baca Selengkapnya →
                        </a>
                    </div>
                </article>
            @endforeach
        </div>
    @else
        <div class="wf-box rounded-2xl p-10 text-center">
            <p class="text-base font-bold text-ink">Belum ada event aktif saat ini.</p>
            <p class="text-xs text-ink/70 mt-1">Ingin menggelar turnamen komunitas di GOR Nusantara? Silakan cek ketersediaan tanggal di kalender reservasi.</p>
        </div>
    @endif
</div>
@endsection
