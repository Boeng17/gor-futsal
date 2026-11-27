@extends('layouts.admin')

@section('title', 'CMS Lapangan & QRIS — Admin GOR Futsal Nusantara')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <p class="font-mono text-xs uppercase tracking-widest text-accent font-bold">PENGATURAN ARENA & PEMBAYARAN</p>
            <h1 class="text-2xl font-extrabold text-ink mt-1">CMS Lapangan & Konfigurasi QRIS</h1>
            <p class="text-xs text-ink/75 mt-1">Atur data lapangan, tarif per jam, serta payload QRIS dinamis merchant.</p>
        </div>
        <a href="{{ route('admin.banners.index') }}" class="wf-btn inline-flex items-center justify-center px-4 py-2 rounded-xl bg-paper border border-ink/25 text-xs font-bold text-ink">
            ← Kelola Banner Beranda
        </a>
    </div>

    @if(session('success'))
        <div class="p-4 rounded-2xl bg-emerald-100 border border-emerald-700/30 text-emerald-950 text-xs font-semibold">
            {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div class="p-4 rounded-2xl bg-rose-100 border border-rose-700/30 text-rose-950 text-xs space-y-1">
            <p class="font-bold">Periksa kembali input berikut:</p>
            <ul class="list-disc list-inside">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="wf-box rounded-2xl p-6 space-y-4">
        <div class="flex flex-wrap items-center justify-between gap-2">
            <div>
                <h2 class="text-base font-bold text-ink">Konfigurasi QRIS Dinamis</h2>
                <p class="text-xs text-ink/70">Nominal tagihan DP 50% dan Lunas otomatis disisipkan ke payload QRIS saat pelanggan membayar.</p>
            </div>
            <span class="px-3 py-1 rounded-full text-xs font-bold {{ $qrisConfig['is_custom'] ? 'bg-emerald-100 text-emerald-950' : 'bg-amber-100 text-amber-950' }}">
                {{ $qrisConfig['is_custom'] ? 'Merchant Aktif: ' . $qrisConfig['merchant_name'] . ' (' . $qrisConfig['merchant_city'] . ')' : 'Belum Ada Merchant Kustom' }}
            </span>
        </div>

        <form action="{{ route('admin.qris.update') }}" method="POST" class="grid grid-cols-1 lg:grid-cols-12 gap-4 text-xs">
            @csrf
            <div class="lg:col-span-6 space-y-1">
                <label class="font-bold text-ink">Nama Merchant (Tag 59) <span class="text-rose-700">* (wajib diisi)</span></label>
                <input type="text" name="merchant_name" value="{{ old('merchant_name', $qrisConfig['merchant_name']) }}" required maxlength="100"
                    class="w-full rounded-xl border border-ink/25 bg-paper px-3 py-2 text-ink">
            </div>
            <div class="lg:col-span-6 space-y-1">
                <label class="font-bold text-ink">Kota Merchant (Tag 60) <span class="text-rose-700">* (wajib diisi)</span></label>
                <input type="text" name="merchant_city" value="{{ old('merchant_city', $qrisConfig['merchant_city']) }}" required maxlength="100"
                    class="w-full rounded-xl border border-ink/25 bg-paper px-3 py-2 text-ink">
            </div>
            <div class="lg:col-span-12 space-y-1">
                <label class="font-bold text-ink">Payload String EMVCo QRIS <span class="text-rose-700">* (wajib diisi)</span></label>
                <textarea name="raw_qris" rows="3" required
                    class="w-full rounded-xl border border-ink/25 bg-paper px-3 py-2 font-mono text-[11px] text-ink">{{ old('raw_qris', $qrisConfig['raw_qris']) }}</textarea>
            </div>
            <div class="lg:col-span-12 flex justify-end">
                <button type="submit" class="wf-btn px-5 py-2.5 rounded-xl bg-accent hover:bg-accentDark text-white font-bold">
                    Simpan Konfigurasi QRIS Dinamis
                </button>
            </div>
        </form>
    </div>

    <div class="wf-box rounded-2xl p-6 space-y-4">
        <h2 class="text-base font-bold text-ink">Tambah Lapangan Baru</h2>
        <form action="{{ route('admin.fields.store') }}" method="POST" enctype="multipart/form-data" class="grid grid-cols-1 md:grid-cols-12 gap-4 text-xs">
            @csrf
            <div class="md:col-span-4 space-y-1">
                <label class="font-bold text-ink">Nama Lapangan <span class="text-rose-700">* (wajib diisi)</span></label>
                <input type="text" name="name" value="{{ old('name') }}" required maxlength="100"
                    placeholder="Contoh: Lapangan A (Sintetis)"
                    class="w-full rounded-xl border border-ink/25 bg-paper px-3 py-2 text-ink">
            </div>
            <div class="md:col-span-3 space-y-1">
                <label class="font-bold text-ink">Tarif per Jam (Rp) <span class="text-rose-700">* (wajib diisi)</span></label>
                <input type="number" name="price_per_hour" value="{{ old('price_per_hour', 150000) }}" required min="0"
                    class="w-full rounded-xl border border-ink/25 bg-paper px-3 py-2 text-ink">
            </div>
            <div class="md:col-span-2 space-y-1">
                <label class="font-bold text-ink">Status <span class="text-rose-700">* (wajib diisi)</span></label>
                <select name="status" required class="w-full rounded-xl border border-ink/25 bg-paper px-3 py-2 text-ink">
                    <option value="active">Aktif</option>
                    <option value="maintenance">Maintenance</option>
                </select>
            </div>
            <div class="md:col-span-3 space-y-1">
                <label class="font-bold text-ink">Foto Lapangan (Opsional)</label>
                <input type="file" name="image" accept="image/jpeg,image/png,image/webp"
                    class="w-full rounded-xl border border-ink/25 bg-paper px-2.5 py-1.5 text-ink">
                <p class="text-[11px] text-ink/65" data-compress-status></p>
            </div>
            <div class="md:col-span-12 space-y-1">
                <label class="font-bold text-ink">Deskripsi & Spesifikasi Lapangan</label>
                <textarea name="description" rows="2" maxlength="500"
                    placeholder="Jenis rumput/lantai, ukuran lapangan, pencahayaan..."
                    class="w-full rounded-xl border border-ink/25 bg-paper px-3 py-2 text-ink">{{ old('description') }}</textarea>
            </div>
            <div class="md:col-span-12 flex justify-end">
                <button type="submit" class="wf-btn px-5 py-2.5 rounded-xl bg-accent hover:bg-accentDark text-white font-bold">
                    Tambah Lapangan
                </button>
            </div>
        </form>
    </div>

    <div class="space-y-4">
        <h2 class="text-base font-bold text-ink">Daftar Lapangan ({{ $fields->count() }})</h2>
        @foreach($fields as $field)
            <div class="wf-box rounded-2xl p-5 space-y-4">
                <div class="flex flex-col md:flex-row gap-5">
                    <div class="w-full md:w-64 h-36 rounded-xl overflow-hidden border border-ink/20 bg-sand/40 shrink-0">
                        @if(\App\Helpers\ImageHelper::resolveUrl($field->image))
                            <img src="{{ \App\Helpers\ImageHelper::resolveUrl($field->image) }}" alt="{{ $field->name }}" class="w-full h-full object-cover">
                        @else
                            <div class="w-full h-full flex items-center justify-center text-xs text-ink/50 font-mono">Belum ada foto</div>
                        @endif
                    </div>

                    <form action="{{ route('admin.fields.update', $field) }}" method="POST" enctype="multipart/form-data" class="flex-1 grid grid-cols-1 md:grid-cols-12 gap-3 text-xs">
                        @csrf
                        @method('PUT')
                        <div class="md:col-span-4 space-y-1">
                            <label class="font-bold text-ink">Nama Lapangan <span class="text-rose-700">* (wajib diisi)</span></label>
                            <input type="text" name="name" value="{{ $field->name }}" required maxlength="100"
                                class="w-full rounded-xl border border-ink/25 bg-paper px-3 py-2 text-ink">
                        </div>
                        <div class="md:col-span-3 space-y-1">
                            <label class="font-bold text-ink">Tarif / Jam (Rp) <span class="text-rose-700">* (wajib diisi)</span></label>
                            <input type="number" name="price_per_hour" value="{{ $field->price_per_hour }}" required min="0"
                                class="w-full rounded-xl border border-ink/25 bg-paper px-3 py-2 text-ink">
                        </div>
                        <div class="md:col-span-2 space-y-1">
                            <label class="font-bold text-ink">Status <span class="text-rose-700">* (wajib diisi)</span></label>
                            <select name="status" required class="w-full rounded-xl border border-ink/25 bg-paper px-3 py-2 text-ink">
                                <option value="active" {{ $field->status === 'active' ? 'selected' : '' }}>Aktif</option>
                                <option value="maintenance" {{ $field->status === 'maintenance' ? 'selected' : '' }}>Maintenance</option>
                            </select>
                        </div>
                        <div class="md:col-span-3 space-y-1">
                            <label class="font-bold text-ink">Ganti Foto (Opsional)</label>
                            <input type="file" name="image" accept="image/jpeg,image/png,image/webp"
                                class="w-full rounded-xl border border-ink/25 bg-paper px-2.5 py-1.5 text-ink">
                            <p class="text-[11px] text-ink/65" data-compress-status></p>
                        </div>
                        <div class="md:col-span-12 space-y-1">
                            <label class="font-bold text-ink">Deskripsi</label>
                            <textarea name="description" rows="2" maxlength="500"
                                class="w-full rounded-xl border border-ink/25 bg-paper px-3 py-2 text-ink">{{ $field->description }}</textarea>
                        </div>
                        <div class="md:col-span-12 flex justify-end">
                            <button type="submit" class="wf-btn px-4 py-2 rounded-xl bg-accent text-white font-bold">
                                Simpan Perubahan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        @endforeach
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
