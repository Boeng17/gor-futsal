@extends('layouts.app')

@section('title', 'Batalkan Pesanan — GOR Futsal Nusantara')

@section('content')
@php $kode = request()->route('kode') ?? 'GOR-2026-092'; @endphp
<div class="max-w-xl mx-auto px-4 sm:px-8 py-8 space-y-6">
    <div class="wf-box rounded-2xl p-6 space-y-4">
        <span class="px-2.5 py-0.5 rounded bg-rose-100 text-rose-900 border border-rose-700 font-mono text-[10px] font-bold">LAYAR 04D • PEMBATALAN PESANAN</span>
        <h1 class="text-xl font-extrabold text-ink">Batalkan Reservasi {{ $kode }}?</h1>
        <p class="text-xs text-ink/75 leading-relaxed">
            Slot jadwal yang dibatalkan akan langsung dibuka kembali untuk pelanggan lain di Kalender Ketersediaan XXI.
        </p>

        <div class="text-xs space-y-1">
            <label class="block font-bold">Alasan Pembatalan</label>
            <select class="w-full rounded-xl border-ink/30 bg-paper px-3 py-2.5">
                <option>Ingin mengubah jam / tanggal main</option>
                <option>Anggota tim berhalangan hadir</option>
                <option>Salah memilih jenis lapangan</option>
            </select>
        </div>

        <div class="flex items-center gap-3 pt-2">
            <a href="{{ route('booking.payment', $kode) }}" class="flex-1 py-2.5 rounded-xl border border-ink text-xs font-bold text-center">
                Kembali
            </a>
            <a href="{{ route('ketersediaan') }}" class="flex-1 py-2.5 rounded-xl bg-rose-800 hover:bg-rose-900 text-white text-xs font-extrabold text-center wf-btn">
                Ya, Batalkan Pesanan
            </a>
        </div>
    </div>
</div>
@endsection
