@extends('layouts.app')

@section('title', 'Upload Bukti Pembayaran — GOR Futsal Nusantara')

@section('content')
@php $kode = request()->route('kode') ?? 'GOR-2026-092'; @endphp
<div x-data="{ previewUrl: null }" class="max-w-2xl mx-auto px-4 sm:px-8 py-8 space-y-6">
    <div class="pb-4 border-b border-ink/25">
        <div class="font-mono text-[11px] font-bold text-accent uppercase">LAYAR 04C • KONFIRMASI TRANSFER</div>
        <h1 class="text-2xl font-extrabold text-ink">Upload Bukti Pembayaran ({{ $kode }})</h1>
    </div>

    <form action="{{ route('booking.check') }}" method="GET" class="wf-box rounded-2xl p-6 space-y-4">
        <div class="text-xs space-y-1">
            <label class="block font-bold text-ink">Nama Pengirim di Rekening / E-Wallet *</label>
            <input type="text" required placeholder="Contoh: Fajar Apriliansyah" class="w-full rounded-xl border-ink/30 bg-paper px-3.5 py-2.5">
        </div>

        <div class="text-xs space-y-1.5">
            <label class="block font-bold text-ink">File Foto / Screenshot Bukti Transfer (JPG/PNG) *</label>
            <input type="file" accept="image/*" required
                   @change="previewUrl = URL.createObjectURL($event.target.files[0])"
                   class="w-full rounded-xl border-ink/30 bg-paper p-2.5 text-xs">
        </div>

        <template x-if="previewUrl">
            <div class="p-3 rounded-xl border border-ink/25 bg-cream/40 text-center">
                <img :src="previewUrl" alt="Preview Bukti" class="max-h-56 mx-auto rounded-lg border border-ink/20">
            </div>
        </template>

        <button type="submit" class="w-full py-3 rounded-xl bg-accent hover:bg-accentDark text-white text-xs font-extrabold wf-btn">
            ✅ Kirim Bukti Pembayaran & Cek Status E-Invoice →
        </button>
    </form>
</div>
@endsection
