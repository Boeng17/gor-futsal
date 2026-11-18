@extends('layouts.app')

@section('title', 'Konfirmasi Pembayaran — GOR Futsal Nusantara')

@section('content')
@php $kode = request()->route('kode') ?? 'GOR-2026-092'; @endphp
<div class="max-w-3xl mx-auto px-4 sm:px-8 py-8 space-y-6">
    <div class="pb-4 border-b border-ink/25 flex items-center justify-between">
        <div>
            <div class="font-mono text-[11px] font-bold text-accent uppercase">LAYAR 04A • LANGKAH 2 DARI 3</div>
            <h1 class="text-2xl font-extrabold text-ink">Konfirmasi Tagihan & Metode QRIS</h1>
        </div>
        <span class="px-3 py-1 rounded-lg bg-ink text-paper font-mono text-xs font-bold">{{ $kode }}</span>
    </div>

    <div class="wf-box rounded-2xl p-6 space-y-5">
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
            <div class="p-3.5 rounded-xl bg-cream/55 border border-ink/15">
                <div class="text-ink/60 font-mono text-[10px]">LAPANGAN & JADWAL</div>
                <div class="font-extrabold text-sm mt-0.5">Lapangan A (Sintetis Pro)</div>
                <div class="font-mono mt-1">Sel, 17 Nov 2026 • 19:00 - 21:00 (2 Jam)</div>
            </div>
            <div class="p-3.5 rounded-xl bg-cream/55 border border-ink/15">
                <div class="text-ink/60 font-mono text-[10px]">SKEMA PEMBAYARAN</div>
                <div class="font-extrabold text-sm text-accent mt-0.5">DP 50% (Rp 180.000)</div>
                <div class="mt-1 text-ink/75">Sisa Rp 180.000 dibayar tunai/QRIS di kasir GOR</div>
            </div>
        </div>

        <div class="p-4 rounded-xl bg-amber-50 border border-amber-700/40 text-xs text-amber-950 leading-relaxed">
            <strong>Penting:</strong> Setelah menekan tombol di bawah, kode QRIS Statis GOR akan tampil selama <strong>15 menit</strong>. Harap segera selesaikan transfer dan unggah bukti screenshot transaksi agar slot tidak terlepas otomatis.
        </div>

        <div class="flex flex-col sm:flex-row items-center justify-between gap-3 pt-2">
            <a href="{{ route('booking.cancel', $kode) }}" class="text-xs font-bold text-rose-700 hover:underline">
                Batalkan Pesanan Ini
            </a>
            <a href="{{ route('booking.qris', $kode) }}" class="w-full sm:w-auto px-6 py-3 rounded-xl bg-accent hover:bg-accentDark text-white text-xs font-extrabold text-center wf-btn">
                Tampilkan Kode QRIS Statis →
            </a>
        </div>
    </div>
</div>
@endsection
