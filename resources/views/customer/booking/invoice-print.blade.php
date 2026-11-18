@extends('layouts.app')

@section('title', 'E-Invoice ' . $booking->booking_code)

@section('content')
@php
    $verifiedPaid = $booking->payments->where('status', 'verified')->sum('amount');
    $remaining = max(0, $booking->total_amount - $verifiedPaid);
@endphp
<div class="max-w-3xl mx-auto px-4 sm:px-6 py-10">
    <div class="mb-4 flex items-center justify-between no-print">
        <a href="{{ route('booking.show', $booking->booking_code) }}" class="text-xs font-bold text-ink hover:text-accent">
            ← Kembali ke Detail Pesanan
        </a>
        <button onclick="window.print()" class="wf-btn px-4 py-2 rounded-xl bg-ink text-paper text-xs font-bold">
            Cetak / Simpan PDF
        </button>
    </div>

    <div class="bg-white border border-ink/25 rounded-2xl p-8 shadow-sm space-y-6">
        <div class="flex items-start justify-between border-b border-ink/15 pb-5">
            <div>
                <h1 class="text-2xl font-bold text-ink">E-INVOICE RESERVASI</h1>
                <p class="text-xs text-ink/70 mt-0.5">GOR FUTSAL NUSANTARA • Sistem Penjadwalan Resmi</p>
            </div>
            <div class="text-right">
                <span class="font-bold text-base text-ink block"><span class="font-mono">{{ $booking->booking_code }}</span></span>
                <span class="text-xs uppercase text-accent font-bold">{{ strtoupper($booking->status) }}</span>
            </div>
        </div>

        <div class="grid grid-cols-2 gap-4 text-xs">
            <div>
                <span class="text-ink/60 block">Pemesan / Tim:</span>
                <strong class="text-ink text-sm">{{ $booking->customer_name }}</strong>
                <span class="block text-ink/75">{{ $booking->customer_phone }}</span>
            </div>
            <div class="text-right">
                <span class="text-ink/60 block">Tanggal Transaksi:</span>
                <strong class="text-ink">{{ $booking->created_at->translatedFormat('d F Y, H:i') }} WIB</strong>
            </div>
        </div>

        <table class="w-full text-xs border-collapse">
            <thead>
                <tr class="border-y border-ink/20 bg-cream/40 text-left">
                    <th class="py-2.5 px-3">Lapangan</th>
                    <th class="py-2.5 px-3">Tanggal</th>
                    <th class="py-2.5 px-3">Jam</th>
                    <th class="py-2.5 px-3 text-right">Subtotal</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-ink/10">
                @foreach($booking->items as $item)
                    <tr>
                        <td class="py-2.5 px-3 font-semibold">{{ $item->field?->name }}</td>
                        <td class="py-2.5 px-3">{{ \Carbon\Carbon::parse($item->booking_date)->translatedFormat('d M Y') }}</td>
                        <td class="py-2.5 px-3">{{ \Carbon\Carbon::parse($item->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($item->end_time)->format('H:i') }}</td>
                        <td class="py-2.5 px-3 text-right">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="border-t border-ink/20 pt-4 space-y-1.5 text-xs">
            <div class="flex justify-between">
                <span>Total Biaya Lapangan:</span>
                <span class="font-bold">Rp {{ number_format($booking->total_amount, 0, ',', '.') }}</span>
            </div>
            <div class="flex justify-between text-emerald-800">
                <span>Sudah Terverifikasi:</span>
                <span class="font-bold">Rp {{ number_format($verifiedPaid, 0, ',', '.') }}</span>
            </div>
            <div class="flex justify-between text-sm font-bold pt-1 border-t border-ink/10">
                <span>Sisa Tagihan di Kasir GOR:</span>
                <span class="font-mono">Rp {{ number_format($remaining, 0, ',', '.') }}</span>
            </div>
        </div>
    </div>
</div>
@endsection
