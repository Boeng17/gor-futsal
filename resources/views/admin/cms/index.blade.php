@extends('layouts.admin')

@section('title', 'CMS Lapangan, Banner Event & QRIS — Backoffice GOR Futsal')

@section('content')
<div x-data="{ tab: 'lapangan' }" class="space-y-6">
    <div class="pb-4 border-b border-ink/25">
        <div class="font-mono text-[11px] font-bold text-accent uppercase">LAYAR 08 • PENGATURAN KONTEN & TARIF</div>
        <h1 class="text-2xl font-extrabold text-ink">CMS Lapangan, Banner Event & Gambar QRIS Statis</h1>
    </div>

    <!-- Tab Switcher -->
    <div class="flex flex-wrap gap-2 text-xs font-bold">
        <button @click="tab = 'lapangan'" :class="tab === 'lapangan' ? 'bg-ink text-paper' : 'bg-paper text-ink border border-ink/25'" class="px-4 py-2.5 rounded-xl">
            🏟️ Data & Tarif Lapangan (08B)
        </button>
        <button @click="tab = 'banner'" :class="tab === 'banner' ? 'bg-ink text-paper' : 'bg-paper text-ink border border-ink/25'" class="px-4 py-2.5 rounded-xl">
            🎉 Banner Event & Promo (08C)
        </button>
        <button @click="tab = 'qris'" :class="tab === 'qris' ? 'bg-ink text-paper' : 'bg-paper text-ink border border-ink/25'" class="px-4 py-2.5 rounded-xl">
            💳 Gambar QRIS Statis GOR
        </button>
    </div>

    <!-- Tab 1: Data & Tarif Lapangan (08B) -->
    <div x-show="tab === 'lapangan'" class="grid grid-cols-1 md:grid-cols-3 gap-4">
        @foreach(['Lapangan A (Sintetis Pro)', 'Lapangan B (Sintetis Arena)', 'Lapangan C (Interlock Match)'] as $idx => $lap)
            <div class="wf-box rounded-2xl p-5 space-y-3 text-xs">
                <h2 class="font-extrabold text-base text-ink">{{ $lap }}</h2>
                <div>
                    <label class="block font-bold mb-1">Tarif Siang / Jam</label>
                    <input type="number" value="{{ 140000 - ($idx * 10000) }}" class="w-full rounded-xl border-ink/30 bg-paper px-3 py-2 font-mono">
                </div>
                <div>
                    <label class="block font-bold mb-1">Tarif Malam / Jam</label>
                    <input type="number" value="{{ 180000 - ($idx * 10000) }}" class="w-full rounded-xl border-ink/30 bg-paper px-3 py-2 font-mono">
                </div>
                <div class="pt-1 flex gap-2">
                    <button class="flex-1 py-2 rounded-xl bg-accent text-white font-bold">Simpan Tarif</button>
                    <a href="{{ route('admin.cms.maintenance', 'lap-' . chr(97+$idx)) }}" class="py-2 px-3 rounded-xl bg-maint border border-ink font-bold">
                        🛠️ Foto Maint (08D)
                    </a>
                </div>
            </div>
        @endforeach
    </div>

    <!-- Tab 2: Banner Event (08C) -->
    <div x-show="tab === 'banner'" class="wf-box rounded-2xl p-6 space-y-4 text-xs max-w-2xl">
        <h2 class="text-lg font-extrabold text-ink">Tambah / Edit Banner Hero Slider (08C)</h2>
        <div>
            <label class="block font-bold mb-1">Judul Event / Promo</label>
            <input type="text" value="Nusantara Futsal Cup 2026 — Total Hadiah Rp 15.000.000" class="w-full rounded-xl border-ink/30 bg-paper p-2.5">
        </div>
        <div>
            <label class="block font-bold mb-1">Deskripsi Singkat</label>
            <textarea rows="3" class="w-full rounded-xl border-ink/30 bg-paper p-2.5">Pendaftaran tim dibuka untuk kategori Umum & Mahasiswa.</textarea>
        </div>
        <div>
            <label class="block font-bold mb-1">Upload Gambar Banner Full-Width</label>
            <input type="file" accept="image/*" class="w-full rounded-xl border-ink/30 bg-paper p-2">
        </div>
        <button class="px-5 py-2.5 rounded-xl bg-accent text-white font-extrabold wf-btn">Publikasikan Banner</button>
    </div>

    <!-- Tab 3: QRIS Statis -->
    <div x-show="tab === 'qris'" class="wf-box rounded-2xl p-6 space-y-4 text-xs max-w-xl">
        <h2 class="text-lg font-extrabold text-ink">Pengaturan Gambar QRIS Statis GOR</h2>
        <div>
            <label class="block font-bold mb-1">Nama Merchant QRIS</label>
            <input type="text" value="GOR FUTSAL NUSANTARA" class="w-full rounded-xl border-ink/30 bg-paper p-2.5">
        </div>
        <div>
            <label class="block font-bold mb-1">Upload Gambar QRIS Resmi (PNG/JPG)</label>
            <input type="file" accept="image/*" class="w-full rounded-xl border-ink/30 bg-paper p-2">
        </div>
        <button class="px-5 py-2.5 rounded-xl bg-ink text-paper font-extrabold wf-btn">Simpan Konfigurasi QRIS</button>
    </div>
</div>
@endsection
