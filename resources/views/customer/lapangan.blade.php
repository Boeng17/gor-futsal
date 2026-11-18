@extends('layouts.app')

@section('title', 'Spesifikasi Lapangan — GOR Futsal Nusantara')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 space-y-8">
    <div>
        <span class="block text-xs uppercase tracking-widest text-accent font-bold mb-1">
            SPESIFIKASI ARENA & TARIF SEWA
        </span>
        <h1 class="text-3xl sm:text-4xl font-bold text-ink">Fasilitas & Daftar Lapangan</h1>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @foreach($fields as $field)
            <x-court-card :field="$field" />
        @endforeach
    </div>
</div>
@endsection
