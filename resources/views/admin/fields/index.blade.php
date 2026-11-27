@extends('layouts.internal')

@section('title', 'Kelola Lapangan & QRIS Dinamis — Admin GOR')

@section('content')
<div class="space-y-6">
    <div>
        <span class="block text-xs font-bold uppercase tracking-wider text-accent">MANAJEMEN ARENA & PEMBAYARAN</span>
        <h1 class="text-2xl sm:text-3xl font-bold text-ink mt-0.5">Kelola Lapangan, Jam Buka & QRIS Dinamis</h1>
    </div>

    <div class="wf-box rounded-2xl p-6" x-data="qrisAdminConfig()">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 mb-4">
            <div>
                <h2 class="text-lg font-bold text-ink">Konfigurasi QRIS Dinamis (Nominal Otomatis EMVCo CRC-16)</h2>
                <p class="text-xs text-ink/70 mt-0.5">
                    Unggah gambar QRIS statis merchant Anda agar sistem otomatis menyuntikkan nominal <strong>DP 50%</strong> atau <strong>Lunas 100%</strong> tepat sebelum tag <code>5802ID</code> dan menghitung ulang CRC-16.
                </p>
            </div>
            <span class="px-3 py-1 rounded-full text-xs font-bold {{ $qrisConfig['is_custom'] ? 'bg-emerald-100 text-emerald-950' : 'bg-amber-100 text-amber-950' }}">
                {{ $qrisConfig['is_custom'] ? 'Merchant Aktif: ' . $qrisConfig['merchant_name'] . ' (' . $qrisConfig['merchant_city'] . ')' : 'Belum Ada Merchant Kustom' }}
            </span>
        </div>

        <form action="{{ route('admin.qris.update') }}" method="POST" class="grid grid-cols-1 lg:grid-cols-12 gap-4 text-xs">
            @csrf
            <div class="lg:col-span-5 space-y-3">
                <div>
                    <label class="block font-bold mb-1">Upload Gambar QRIS Statis (Auto-Extract Payload)</label>
                    <input type="file" accept="image/*" @change="decodeQrisImage($event)"
                           class="w-full text-xs border border-ink/20 rounded-xl p-1.5 bg-paper">
                    <p x-show="scanStatus" x-text="scanStatus" class="mt-1.5 font-semibold text-accent"></p>
                </div>
                <div class="grid grid-cols-2 gap-2.5">
                    <div>
                        <label class="block font-bold mb-1">Nama Merchant</label>
                        <input type="text" name="merchant_name" x-model="merchantName" required placeholder="Wajib diisi" class="w-full rounded-xl border-ink/25 text-xs">
                    </div>
                    <div>
                        <label class="block font-bold mb-1">Kota Merchant</label>
                        <input type="text" name="merchant_city" x-model="merchantCity" required placeholder="Wajib diisi" class="w-full rounded-xl border-ink/25 text-xs">
                    </div>
                </div>
            </div>

            <div class="lg:col-span-7 flex flex-col justify-between space-y-3">
                <div>
                    <div class="flex items-center justify-between mb-1">
                        <label class="block font-bold">Payload String EMVCo QRIS *</label>
                        <span x-text="tagSummary" class="text-[11px] font-semibold text-emerald-800"></span>
                    </div>
                    <textarea name="raw_qris" rows="3" x-model="rawQris" @input="inspectTags()" required placeholder="Wajib diisi (tempel payload QRIS EMVCo atau unggah gambar QRIS di samping)"
                              class="w-full rounded-xl border-ink/25 bg-paper text-xs text-ink font-mono"></textarea>
                </div>
                <div class="flex justify-end">
                    <button type="submit" class="wf-btn px-5 py-2.5 rounded-xl bg-ink text-paper font-bold">
                        Simpan Konfigurasi QRIS Dinamis
                    </button>
                </div>
            </div>
        </form>
    </div>

    <div class="wf-box rounded-2xl p-6">
        <h2 class="text-lg font-bold text-ink mb-4">+ Tambah Lapangan Baru</h2>
        <form action="{{ route('admin.fields.store') }}" method="POST" enctype="multipart/form-data" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 text-xs">
            @csrf
            <div>
                <label class="block font-bold mb-1">Nama Lapangan *</label>
                <input type="text" name="name" required placeholder="Contoh: Lapangan 4 (Vinyl Pro)" class="w-full rounded-xl border-ink/25 text-xs">
            </div>
            <div>
                <label class="block font-bold mb-1">Tarif Sewa / Jam (Rp) *</label>
                <input type="number" name="price_per_hour" required min="0" placeholder="120000" class="w-full rounded-xl border-ink/25 text-xs">
            </div>
            <div>
                <label class="block font-bold mb-1">Status Awal *</label>
                <select name="status" class="w-full rounded-xl border-ink/25 text-xs">
                    <option value="active">Aktif (Bisa Dibooking)</option>
                    <option value="maintenance">Maintenance (Perawatan)</option>
                </select>
            </div>
            <div>
                <label class="block font-bold mb-1">Jam Buka (00 - 23)</label>
                <select name="open_hour" class="w-full rounded-xl border-ink/25 text-xs">
                    @for($h = 0; $h <= 23; $h++)
                        <option value="{{ $h }}" {{ $h === 8 ? 'selected' : '' }}>{{ str_pad((string)$h, 2, '0', STR_PAD_LEFT) }}:00 WIB</option>
                    @endfor
                </select>
            </div>
            <div>
                <label class="block font-bold mb-1">Jam Tutup (01 - 24)</label>
                <select name="close_hour" class="w-full rounded-xl border-ink/25 text-xs">
                    @for($h = 1; $h <= 24; $h++)
                        <option value="{{ $h }}" {{ $h === 23 ? 'selected' : '' }}>{{ str_pad((string)$h, 2, '0', STR_PAD_LEFT) }}:00 WIB</option>
                    @endfor
                </select>
            </div>
            <div>
                <label class="block font-bold mb-1">Foto Lapangan (Opsional)</label>
                <input type="file" name="image" accept="image/*" class="w-full text-xs border border-ink/20 rounded-xl p-1.5 bg-paper">
            </div>
            <div class="sm:col-span-2 lg:col-span-3">
                <label class="block font-bold mb-1">Deskripsi Fasilitas Lapangan</label>
                <input type="text" name="description" placeholder="Jelaskan jenis lantai, pencahayaan, dan fasilitas lapangan..." class="w-full rounded-xl border-ink/25 text-xs">
            </div>
            <div class="sm:col-span-2 lg:col-span-3">
                <button type="submit" class="wf-btn px-5 py-2.5 rounded-xl bg-accent text-paper font-bold">Simpan Lapangan Baru</button>
            </div>
        </form>
    </div>

    <div class="space-y-4">
        @foreach($fields as $field)
            <div class="wf-box rounded-2xl p-6">
                <form action="{{ route('admin.fields.update', $field) }}" method="POST" enctype="multipart/form-data" class="grid grid-cols-1 lg:grid-cols-12 gap-4 items-end text-xs">
                    @csrf
                    @method('PUT')

                    <div class="lg:col-span-2">
                        <div class="h-24 rounded-xl bg-sand/50 border border-ink/15 overflow-hidden mb-2">
                            @if($field->image)
                                <img src="{{ \App\Helpers\ImageHelper::resolveUrl($field->image, 'field', $field->id) }}" class="w-full h-full object-cover">
                            @else
                                <div class="w-full h-full flex items-center justify-center text-[11px] text-ink/60">Tanpa Foto</div>
                            @endif
                        </div>
                        <input type="file" name="image" accept="image/*" class="w-full text-[11px]">
                    </div>

                    <div class="lg:col-span-10 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                        <div>
                            <label class="block font-bold mb-1">Nama Lapangan</label>
                            <input type="text" name="name" value="{{ $field->name }}" required class="w-full rounded-xl border-ink/25 text-xs">
                        </div>
                        <div>
                            <label class="block font-bold mb-1">Tarif / Jam (Rp)</label>
                            <input type="number" name="price_per_hour" value="{{ $field->price_per_hour }}" required class="w-full rounded-xl border-ink/25 text-xs">
                        </div>
                        <div>
                            <label class="block font-bold mb-1">Jam Operasional</label>
                            <div class="flex gap-1.5">
                                <select name="open_hour" class="w-1/2 rounded-xl border-ink/25 text-xs">
                                    @for($h = 0; $h <= 23; $h++)
                                        <option value="{{ $h }}" {{ $field->open_hour_value === $h ? 'selected' : '' }}>{{ str_pad((string)$h, 2, '0', STR_PAD_LEFT) }}:00</option>
                                    @endfor
                                </select>
                                <select name="close_hour" class="w-1/2 rounded-xl border-ink/25 text-xs">
                                    @for($h = 1; $h <= 24; $h++)
                                        <option value="{{ $h }}" {{ $field->close_hour_value === $h ? 'selected' : '' }}>{{ str_pad((string)$h, 2, '0', STR_PAD_LEFT) }}:00</option>
                                    @endfor
                                </select>
                            </div>
                        </div>
                        <div>
                            <label class="block font-bold mb-1">Status</label>
                            <select name="status" class="w-full rounded-xl border-ink/25 text-xs">
                                <option value="active" {{ $field->status === 'active' ? 'selected' : '' }}>Aktif</option>
                                <option value="maintenance" {{ $field->status === 'maintenance' ? 'selected' : '' }}>Maintenance</option>
                            </select>
                        </div>
                        <div class="sm:col-span-2 lg:col-span-3">
                            <label class="block font-bold mb-1">Deskripsi</label>
                            <input type="text" name="description" value="{{ $field->description }}" class="w-full rounded-xl border-ink/25 text-xs">
                        </div>
                        <div class="flex items-end gap-2">
                            <button type="submit" class="wf-btn flex-1 py-2.5 px-3 rounded-xl bg-ink text-paper font-bold">Simpan Perubahan</button>
                        </div>
                    </div>
                </form>

                <div class="mt-3 pt-3 border-t border-ink/10 flex flex-wrap items-center justify-between gap-2 text-xs">
                    <span class="text-ink/70">Durasi buka saat ini: <strong>{{ $field->operating_hours_label }}</strong></span>
                    <div class="flex items-center gap-3">
                        <form action="{{ route('admin.fields.toggleStatus', $field) }}" method="POST">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="font-bold text-accent hover:underline">
                                {{ $field->status === 'active' ? 'Set ke Maintenance' : 'Aktifkan Lapangan' }}
                            </button>
                        </form>

                        <form action="{{ route('admin.fields.destroy', $field) }}" method="POST" onsubmit="return confirm('Hapus lapangan ini?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="font-bold text-rose-700 hover:underline">Hapus Lapangan</button>
                        </form>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/jsqr@1.4.0/dist/jsQR.min.js"></script>
<script>
function parseEmvTags(payload) {
    const map = {};
    let pos = 0;
    const clean = (payload || '').replace(/[\r\n]/g, '').trim();
    while (pos + 4 <= clean.length) {
        const tag = clean.slice(pos, pos + 2);
        const lenStr = clean.slice(pos + 2, pos + 4);
        if (!/^\d{2}$/.test(lenStr)) break;
        const len = parseInt(lenStr, 10);
        if (pos + 4 + len > clean.length) break;
        map[tag] = clean.slice(pos + 4, pos + 4 + len);
        pos += 4 + len;
        if (tag === '63') break;
    }
    return { map, validLength: pos === clean.length && clean.length > 0 };
}

window.qrisAdminConfig = function () {
    return {
        rawQris: '',
        merchantName: '',
        merchantCity: '',
        scanStatus: '',
        tagSummary: '',
        init() {
            this.inspectTags();
        },
        inspectTags() {
            const { map, validLength } = parseEmvTags(this.rawQris);
            if (validLength && map['00'] === '01' && map['58'] === 'ID') {
                this.tagSummary = 'Format EMVCo Valid • Tag 5802ID & CRC-16 Terdeteksi';
            } else {
                this.tagSummary = '';
            }
        },
        decodeQrisImage(event) {
            const file = event.target.files[0];
            if (!file) return;
            this.scanStatus = 'Membaca kode QRIS dari gambar...';
            const reader = new FileReader();
            reader.onload = (e) => {
                const img = new Image();
                img.onload = () => {
                    const canvas = document.createElement('canvas');
                    canvas.width = img.width;
                    canvas.height = img.height;
                    const ctx = canvas.getContext('2d');
                    ctx.drawImage(img, 0, 0);
                    const imageData = ctx.getImageData(0, 0, canvas.width, canvas.height);
                    if (window.jsQR) {
                        const code = window.jsQR(imageData.data, imageData.width, imageData.height);
                        if (code && code.data) {
                            this.rawQris = code.data.replace(/[\r\n]/g, '').trim();
                            const { map } = parseEmvTags(this.rawQris);
                            if (map['59']) this.merchantName = map['59'].toUpperCase();
                            if (map['60']) this.merchantCity = map['60'].toUpperCase();
                            this.inspectTags();
                            this.scanStatus = '✓ Payload EMVCo QRIS berhasil diekstrak! Klik Simpan Konfigurasi QRIS.';
                        } else {
                            this.scanStatus = '✕ Kode QR tidak terbaca dari gambar. Pastikan gambar jelas atau tempel string QRIS secara manual.';
                        }
                    }
                };
                img.src = e.target.result;
            };
            reader.readAsDataURL(file);
        }
    };
};
</script>
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
@endpush
