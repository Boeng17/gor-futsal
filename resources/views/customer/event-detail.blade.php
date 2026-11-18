@extends('layouts.app')

@section('title', $banner->title . ' — GOR Futsal Nusantara')

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-10 space-y-6">
    <a href="{{ route('event.index') }}" class="inline-block text-xs font-bold text-ink hover:text-accent">
        ← Kembali ke Daftar Event
    </a>

    <article class="wf-box rounded-3xl overflow-hidden">
        @if(\App\Helpers\ImageHelper::resolveUrl($banner->image))
            <div class="h-72 sm:h-96 bg-sand border-b border-ink/15">
                <img src="{{ \App\Helpers\ImageHelper::resolveUrl($banner->image) }}" alt="{{ $banner->title }}" class="w-full h-full object-cover">
            </div>
        @endif

        <div class="p-6 sm:p-10 space-y-4">
            <span class="text-xs text-accent font-bold">
                DIPUBLIKASIKAN {{ $banner->created_at->translatedFormat('d F Y') }}
            </span>
            <h1 class="text-2xl sm:text-4xl font-bold text-ink">{{ $banner->title }}</h1>
            <div class="text-sm text-ink/85 leading-relaxed whitespace-pre-line">
                {{ $banner->content }}
            </div>

            <div class="pt-6 border-t border-ink/15">
                <a href="{{ route('booking.create') }}" class="wf-btn inline-block px-6 py-3 rounded-full bg-accent text-paper text-xs font-bold">
                    Booking Jadwal Lapangan Sekarang →
                </a>
            </div>
        </div>
    </article>
</div>
@endsection
