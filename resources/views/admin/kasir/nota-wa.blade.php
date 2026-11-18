@extends('layouts.admin')

@section('title', 'Kirim Nota WhatsApp — Backoffice GOR Futsal')

@section('content')
@php
    $kode = request()->route('kode') ?? 'GOR-2026-089';
    $pesan = "Halo *Fajar Apriliansyah*,

Reservasi Anda di *GOR FUTSAL NUSANTARA* telah tercatat:
• Kode Booking: *{$kode}*
• Lapangan: Lapangan A (Sintetis Pro)
• Jadwal: Selasa, 17 Nov 2026 (19:00 - 21:00 WIB)
• Status: *DP 50% TERVERIFIKASI (Rp 180.000)*
• Sisa Pelunasan di Kasir: Rp 180.000

Terima kasih!";
@endphp
<div class="max-w-2xl mx-auto space-y-5">
    <div class="flex items-center justify-between pb-3 border-b border-ink/25">
        <div>
            <div class="font-mono text-[11px] font-bold text-emerald-800 uppercase">LAYAR 07H • NOTIFIKASI WHATSAPP</div>
            <h1 class="text-xl font-extrabold text-ink">Kirim E-Nota ke WhatsApp Pelanggan</h1>
        </div>
        <a href="{{ route('admin.kasir.index') }}" class="px-3.5 py-2 rounded-xl border border-ink bg-paper text-xs font-bold">← Kembali</a>
    </div>

    <div class="wf-box rounded-2xl p-6 space-y-4">
        <div class="text-xs space-y-1">
            <label class="block font-bold">Nomor Tujuan WhatsApp</label>
            <input type="text" value="6281234567890" class="w-full rounded-xl border-ink/30 bg-paper px-3.5 py-2.5 font-mono">
        </div>

        <div class="text-xs space-y-1">
            <label class="block font-bold">Isi Pesan Nota Otomatis</label>
            <textarea rows="8" class="w-full rounded-xl border-ink/30 bg-paper p-3.5 font-mono text-xs">{{ $pesan }}</textarea>
        </div>

        <a href="https://wa.me/6281234567890?text={{ urlencode($pesan) }}" target="_blank"
           class="block w-full py-3 rounded-xl bg-emerald-800 hover:bg-emerald-900 text-white text-xs font-extrabold text-center wf-btn">
            📲 Buka WhatsApp & Kirim Nota Sekarang →
        </a>
    </div>
</div>
@endsection
