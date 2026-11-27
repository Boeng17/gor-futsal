@extends('layouts.internal')

@section('title', 'Banner Event — Admin GOR Futsal Nusantara')

@section('content')
<div x-data="{
    createPreview: null,
    editBanner: null,
    editPreview: null,
    previewFile(event, target) {
        const file = event.target.files[0];
        if (!file) return;
        const reader = new FileReader();
        reader.onload = e => {
            if (target === 'create') this.createPreview = e.target.result;
            if (target === 'edit') this.editPreview = e.target.result;
        };
        reader.readAsDataURL(file);
    }
}">

    <div class="mb-6">
        <span class="block text-xs font-bold uppercase tracking-wider text-accent">PROMO & TURNAMEN</span>
        <h1 class="text-2xl sm:text-3xl font-bold text-ink mt-0.5">Manajemen Banner Event</h1>
    </div>


    <div class="wf-box rounded-2xl p-6 mb-6">
        <h2 class="text-lg font-bold text-ink mb-4">+ Publikasikan Event / Promo Baru</h2>
        <form action="{{ route('admin.banners.store') }}" method="POST" enctype="multipart/form-data" class="grid grid-cols-1 lg:grid-cols-12 gap-5 text-xs">
            @csrf
            <div class="lg:col-span-7 space-y-4">
                <div>
                    <label class="block font-bold mb-1">Judul Event *</label>
                    <input type="text" name="title" required placeholder="Contoh: Turnamen Futsal Antar Mahasiswa 2026"
                           class="w-full rounded-xl border-ink/25 bg-paper text-xs">
                </div>
                <div>
                    <label class="block font-bold mb-1">Poster / Banner Gambar (JPG/PNG/WEBP, Maks 2MB) *</label>
                    <input type="file" name="image" required accept="image/*" @change="previewFile($event, 'create')"
                           class="w-full text-xs border border-ink/20 rounded-xl p-1.5 bg-paper">
                </div>
                <div>
                    <label class="block font-bold mb-1">Deskripsi Lengkap Event *</label>
                    <textarea name="content" rows="3" required placeholder="Tulis detail syarat pendaftaran, total hadiah, dan jadwal pelaksanaan..."
                              class="w-full rounded-xl border-ink/25 bg-paper text-xs"></textarea>
                </div>
                <div>
                    <button type="submit" class="wf-btn px-5 py-2.5 rounded-xl bg-accent hover:bg-accentDark text-paper font-bold">
                        Publikasikan Event
                    </button>
                </div>
            </div>

            <div class="lg:col-span-5">
                <label class="block font-bold mb-1">Preview Poster Banner</label>
                <div class="h-52 rounded-2xl border-2 border-dashed border-ink/25 bg-cream/60 overflow-hidden flex items-center justify-center">
                    <template x-if="createPreview">
                        <img :src="createPreview" alt="Preview Banner" class="w-full h-full object-cover">
                    </template>
                    <template x-if="!createPreview">
                        <span class="text-xs text-ink/55 text-center px-4">
                            Pilih file gambar poster untuk melihat preview tampilan di beranda.
                        </span>
                    </template>
                </div>
            </div>
        </form>
    </div>


    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
        @foreach($banners as $banner)
            @php
                $imgUrl = \App\Helpers\ImageHelper::resolveUrl($banner->image, 'banner', $banner->id);
                $editPayload = [
                    'id'         => $banner->id,
                    'title'      => $banner->title,
                    'content'    => $banner->content,
                    'is_active'  => (bool) $banner->is_active,
                    'image_url'  => $imgUrl,
                    'update_url' => route('admin.banners.update', $banner),
                ];
            @endphp
            <div class="wf-box rounded-2xl overflow-hidden flex flex-col justify-between">
                <div>
                    <div class="h-44 bg-sand/50 border-b border-ink/15 overflow-hidden relative">
                        @if($imgUrl)
                            <img src="{{ $imgUrl }}" alt="{{ $banner->title }}" class="w-full h-full object-cover">
                        @else
                            <div class="w-full h-full flex items-center justify-center text-xs text-ink/60 font-semibold">
                                Tanpa Gambar Poster
                            </div>
                        @endif
                        <span class="absolute top-3 left-3 px-2.5 py-1 rounded-full text-[10px] font-bold {{ $banner->is_active ? 'bg-emerald-200 text-emerald-950' : 'bg-stone-300 text-stone-800' }}">
                            {{ $banner->is_active ? 'AKTIF DI BERANDA' : 'NONAKTIF' }}
                        </span>
                    </div>

                    <div class="p-5">
                        <span class="text-[11px] text-ink/60 font-medium">{{ $banner->created_at->translatedFormat('d M Y') }}</span>
                        <h3 class="text-lg font-bold text-ink mt-0.5">{{ $banner->title }}</h3>
                        <p class="text-xs text-ink/80 mt-1.5 leading-relaxed">{{ Str::limit($banner->content, 160) }}</p>
                    </div>
                </div>

                <div class="px-5 py-3.5 bg-cream/40 border-t border-ink/10 flex items-center justify-between text-xs">
                    <div class="flex items-center gap-3">
                        <button type="button"
                                @click='editBanner = @json($editPayload); editPreview = @json($imgUrl)'
                                class="px-3 py-1 rounded-lg bg-ink text-paper font-bold">
                            Edit Banner
                        </button>

                        <form action="{{ route('admin.banners.toggle', $banner) }}" method="POST">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="font-bold text-accent hover:underline">
                                {{ $banner->is_active ? 'Sembunyikan' : 'Tampilkan' }}
                            </button>
                        </form>
                    </div>

                    <form action="{{ route('admin.banners.destroy', $banner) }}" method="POST" onsubmit="return confirm('Hapus banner event ini?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="font-bold text-rose-700 hover:underline">Hapus</button>
                    </form>
                </div>
            </div>
        @endforeach
    </div>


    <div x-show="editBanner" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50">
        <div @click.away="editBanner = null" class="wf-box rounded-2xl max-w-lg w-full p-6 space-y-4 max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between border-b border-ink/15 pb-3">
                <h3 class="text-xl font-bold text-ink">Edit Banner Event</h3>
                <button type="button" @click="editBanner = null" class="p-1.5 rounded-lg bg-cream font-bold">✕</button>
            </div>
            <template x-if="editBanner">
                <form :action="editBanner.update_url" method="POST" enctype="multipart/form-data" class="space-y-3 text-xs">
                    @csrf
                    @method('PUT')
                    <div>
                        <label class="block font-bold mb-1">Judul Event *</label>
                        <input type="text" name="title" x-model="editBanner.title" required class="w-full rounded-xl border-ink/25 text-xs">
                    </div>
                    <div>
                        <label class="block font-bold mb-1">Ganti Poster Banner (Opsional)</label>
                        <input type="file" name="image" accept="image/*" @change="previewFile($event, 'edit')"
                               class="w-full text-xs border border-ink/20 rounded-xl p-1.5 bg-paper">
                    </div>
                    <div class="h-40 rounded-xl border border-ink/20 bg-cream/60 overflow-hidden flex items-center justify-center">
                        <template x-if="editPreview">
                            <img :src="editPreview" class="w-full h-full object-cover">
                        </template>
                        <template x-if="!editPreview">
                            <span class="text-ink/55">Belum ada poster</span>
                        </template>
                    </div>
                    <div>
                        <label class="block font-bold mb-1">Deskripsi Event *</label>
                        <textarea name="content" rows="4" x-model="editBanner.content" required class="w-full rounded-xl border-ink/25 text-xs"></textarea>
                    </div>
                    <div class="flex items-center gap-2">
                        <input type="hidden" name="is_active" value="0">
                        <input type="checkbox" id="edit_is_active" name="is_active" value="1" x-model="editBanner.is_active" class="rounded border-ink/30 text-accent">
                        <label for="edit_is_active" class="font-bold text-ink">Tampilkan aktif di halaman beranda</label>
                    </div>
                    <div class="flex justify-end gap-2 pt-2">
                        <button type="button" @click="editBanner = null" class="px-4 py-2 rounded-xl bg-cream border border-ink/20 font-bold">Batal</button>
                        <button type="submit" class="wf-btn px-5 py-2 rounded-xl bg-accent text-paper font-bold">Simpan Perubahan</button>
                    </div>
                </form>
            </template>
        </div>
    </div>

</div>
<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('input[type="file"][name="image"]').forEach(function (input) {
        input.addEventListener('change', function (e) {
            const file = e.target.files[0];
            if (!file || !file.type.startsWith('image/')) return;

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
