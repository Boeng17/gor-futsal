@extends('layouts.admin')

@section('title', 'Foto & Info Maintenance Lapangan — Backoffice GOR Futsal')

@section('content')
@php $lap = request()->route('lapangan') ?? 'lap-b'; @endphp
<div class="max-w-xl mx-auto space-y-5">
    <div class="flex items-center justify-between pb-3 border-b border-ink/25">
        <div>
            <div class="font-mono text-[11px] font-bold text-accent uppercase">LAYAR 08D • TRANSPARANSI MAINTENANCE</div>
            <h1 class="text-xl font-extrabold text-ink">Upload Foto & Catatan Maintenance ({{ strtoupper($lap) }})</h1>
        </div>
        <a href="{{ route('admin.cms.index') }}" class="px-3.5 py-2 rounded-xl border border-ink bg-paper text-xs font-bold">← Kembali</a>
    </div>

    <div class="wf-box rounded-2xl p-6 space-y-4 text-xs">
        <div>
            <label class="block font-bold mb-1">Status Perawatan</label>
            <select class="w-full rounded-xl border-ink/30 bg-paper p-2.5">
                <option>Aktif Maintenance (Tampilkan Foto Kondisi ke Pelanggan)</option>
                <option>Normal / Selesai Perawatan</option>
            </select>
        </div>
        <div>
            <label class="block font-bold mb-1">Foto Kondisi Lapangan</label>
            <input type="file" accept="image/*" class="w-full rounded-xl border-ink/30 bg-paper p-2">
        </div>
        <div>
            <label class="block font-bold mb-1">Catatan untuk Pelanggan</label>
            <textarea rows="3" class="w-full rounded-xl border-ink/30 bg-paper p-2.5">Penyisiran ulang rubber granule dan pengecekan jaring gawang sisi utara.</textarea>
        </div>
        <button class="w-full py-3 rounded-xl bg-accent text-white font-extrabold wf-btn">Simpan Info Maintenance</button>
    </div>
</div>
@endsection
