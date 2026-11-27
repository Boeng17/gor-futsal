@extends('layouts.app')

@section('title', 'Kelola Banner & Event — Admin GOR Futsal Nusantara')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <p class="font-mono text-xs uppercase tracking-widest text-accent font-bold">CMS PROMO & TURNAMEN</p>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-ink mt-1">Kelola Banner Halaman Beranda</h1>
            <p class="text-xs sm:text-sm text-ink/75 mt-1">Unggah banner promo atau turnamen. Gambar otomatis dioptimalkan ke lebar 1080px agar ringan dan tajam.</p>
        </div>
        <a href="{{ route('admin.fields.index') }}" class="wf-btn inline-flex items-center justify-center px-4 py-2.5 rounded-xl bg-paper border border-ink/25 text-xs font-bold text-ink">
            Kelola Lapangan & QRIS →
        </a>
    </div>

    @if(session('success'))
        <div class="p-4 rounded-2xl bg-emerald-100 border border-emerald-700/30 text-emerald-950 text-xs sm:text-sm font-semibold">
            {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div class="p-4 rounded-2xl bg-rose-100 border border-rose-700/30 text-rose-950 text-xs sm:text-sm space-y-1">
            <p class="font-bold">Periksa kembali input berikut:</p>
            <ul class="list-disc list-inside">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="wf-box rounded-2xl p-6">
        <h2 class="text-lg font-bold text-ink mb-4">Tambah Banner Baru</h2>
        <form action="{{ route('admin.banners.store') }}" method="POST" enctype="multipart/form-data" class="grid grid-cols-1 lg:grid-cols-12 gap-4 text-xs">
            @csrf
            <div class="lg:col-span-6 space-y-1.5">
                <label class="font-bold text-ink">Judul Event / Promo <span class="text-rose-700">* (wajib diisi)</span></label>
                <input type="text" name="title" value="{{ old('title') }}" required maxlength="150"
                    placeholder="Contoh: Nusantara Futsal Cup 2026 — Total Hadiah Rp 15.000.000"
                    class="w-full rounded-xl border border-ink/25 bg-paper px-3.5 py-2.5 text-ink">
            </div>

            <div class="lg:col-span-6 space-y-1.5">
                <label class="font-bold text-ink">Gambar Banner (Rasio Lebar 21:9 / 16:9) <span class="text-rose-700">* (wajib diisi)</span></label>
                <input type="file" name="image" accept="image/jpeg,image/png,image/webp" required
                    class="w-full rounded-xl border border-ink/25 bg-paper px-3 py-2 text-ink file:mr-3 file:py-1 file:px-3 file:rounded-lg file:border-0 file:bg-accent file:text-white file:font-bold">
                <p class="text-[11px] text-ink/65" data-compress-status>Otomatis dikompres ke resolusi 1080px JPEG agar lolos limit server Vercel.</p>
            </div>

            <div class="lg:col-span-12 space-y-1.5">
                <label class="font-bold text-ink">Deskripsi Singkat Event / Promo <span class="text-rose-700">* (wajib diisi)</span></label>
                <textarea name="content" rows="3" required
                    placeholder="Tuliskan detail promo, jadwal pertandingan, atau fasilitas unggulan..."
                    class="w-full rounded-xl border border-ink/25 bg-paper px-3.5 py-2.5 text-ink">{{ old('content') }}</textarea>
            </div>

            <div class="lg:col-span-12 flex flex-wrap items-center justify-between gap-3 pt-1">
                <label class="inline-flex items-center gap-2 font-semibold text-ink cursor-pointer">
                    <input type="checkbox" name="is_active" value="1" checked class="rounded border-ink/30 text-accent">
                    <span>Tampilkan langsung di halaman beranda</span>
                </label>
                <button type="submit" class="wf-btn px-5 py-2.5 rounded-xl bg-accent hover:bg-accentDark text-white font-bold">
                    Publikasikan Banner
                </button>
            </div>
        </form>
    </div>

    <div class="space-y-4">
        <h2 class="text-lg font-bold text-ink">Daftar Banner Tersimpan ({{ $banners->count() }})</h2>
        @forelse($banners as $banner)
            <div class="wf-box rounded-2xl p-5 space-y-4">
                <div class="flex flex-col md:flex-row gap-5">
                    <div class="w-full md:w-72 h-36 rounded-xl overflow-hidden border border-ink/20 bg-sand/40 shrink-0">
                        @if(\App\Helpers\ImageHelper::resolveUrl($banner->image))
                            <img src="{{ \App\Helpers\ImageHelper::resolveUrl($banner->image) }}" alt="{{ $banner->title }}" class="w-full h-full object-cover">
                        @else
                            <div class="w-full h-full flex items-center justify-center text-xs text-ink/50 font-mono">Belum ada gambar</div>
                        @endif
                    </div>

                    <form action="{{ route('admin.banners.update', $banner) }}" method="POST" enctype="multipart/form-data" class="flex-1 grid grid-cols-1 md:grid-cols-12 gap-3 text-xs">
                        @csrf
                        @method('PUT')
                        <div class="md:col-span-7 space-y-1">
                            <label class="font-bold text-ink">Judul Banner <span class="text-rose-700">* (wajib diisi)</span></label>
                            <input type="text" name="title" value="{{ $banner->title }}" required maxlength="150"
                                class="w-full rounded-xl border border-ink/25 bg-paper px-3 py-2 text-ink">
                        </div>

                        <div class="md:col-span-5 space-y-1">
                            <label class="font-bold text-ink">Ganti Gambar (Opsional)</label>
                            <input type="file" name="image" accept="image/jpeg,image/png,image/webp"
                                class="w-full rounded-xl border border-ink/25 bg-paper px-2.5 py-1.5 text-ink">
                            <p class="text-[11px] text-ink/65" data-compress-status></p>
                        </div>

                        <div class="md:col-span-12 space-y-1">
                            <label class="font-bold text-ink">Deskripsi <span class="text-rose-700">* (wajib diisi)</span></label>
                            <textarea name="content" rows="2" required
                                class="w-full rounded-xl border border-ink/25 bg-paper px-3 py-2 text-ink">{{ $banner->content }}</textarea>
                        </div>

                        <div class="md:col-span-12 flex flex-wrap items-center justify-between gap-2 pt-1">
                            <span class="px-2.5 py-1 rounded-full text-[11px] font-bold {{ $banner->is_active ? 'bg-emerald-100 text-emerald-950' : 'bg-amber-100 text-amber-950' }}">
                                {{ $banner->is_active ? 'AKTIF DI BERANDA' : 'NONAKTIF' }}
                            </span>
                            <div class="flex flex-wrap items-center gap-2">
                                <button type="submit" class="wf-btn px-4 py-2 rounded-xl bg-accent text-white font-bold">
                                    Simpan Perubahan
                                </button>
                            </div>
                        </div>
                    </form>
                </div>

                <div class="flex flex-wrap items-center justify-end gap-2 pt-2 border-t border-ink/15 text-xs">
                    <form action="{{ route('admin.banners.toggle', $banner) }}" method="POST">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="px-3.5 py-1.5 rounded-xl border border-ink/25 bg-paper font-bold text-ink">
                            {{ $banner->is_active ? 'Sembunyikan' : 'Tampilkan' }}
                        </button>
                    </form>
                    <form action="{{ route('admin.banners.destroy', $banner) }}" method="POST" onsubmit="return confirm('Hapus banner ini?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="px-3.5 py-1.5 rounded-xl bg-rose-700 text-white font-bold">
                            Hapus
                        </button>
                    </form>
                </div>
            </div>
        @empty
            <div class="wf-box rounded-2xl p-8 text-center text-xs text-ink/70">
                Belum ada banner promo atau turnamen yang tersimpan.
            </div>
        @endforelse
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('input[type="file"][name="image"]').forEach(function (input) {
        input.addEventListener('change', function (e) {
            const file = e.target.files[0];
            if (!file || !file.type.startsWith('image/')) return;

            const statusEl = input.parentElement ? input.parentElement.querySelector('[data-compress-status]') : null;
            if (statusEl) statusEl.textContent = 'Mengoptimalkan resolusi gambar...';

            const reader = new FileReader();
            reader.onload = function (ev) {
                const img = new Image();
                img.onload = function () {
                    const maxW = 1080;
                    let w = img.width;
                    let h = img.height;
                    if (w > maxW) {
                        h = Math.round((h / w) * maxW);
                        w = maxW;
                    }
                    const canvas = document.createElement('canvas');
                    canvas.width = w;
                    canvas.height = h;
                    const ctx = canvas.getContext('2d');
                    ctx.fillStyle = '#FFFFFF';
                    ctx.fillRect(0, 0, w, h);
                    ctx.drawImage(img, 0, 0, w, h);

                    canvas.toBlob(function (blob) {
                        if (!blob) return;
                        const optimized = new File([blob], file.name.replace(/\.[^.]+$/, '.jpg'), {
                            type: 'image/jpeg',
                            lastModified: Date.now()
                        });
                        const dt = new DataTransfer();
                        dt.items.add(optimized);
                        input.files = dt.files;

                        if (statusEl) {
                            const kb = Math.round(optimized.size / 1024);
                            statusEl.textContent = `Siap diunggah: ${w}x${h}px (${kb} KB)`;
                        }
                    }, 'image/jpeg', 0.78);
                };
                img.src = ev.target.result;
            };
            reader.readAsDataURL(file);
        });
    });
});
</script>
@endsection
