@extends('layouts.app')

@section('title', 'Bantuan & Kontak — GOR Futsal Nusantara')

@section('content')
<div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-10 space-y-10">
    <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4 pb-6 border-b border-ink/15">
        <div>
            <span class="block text-xs uppercase tracking-widest text-accent font-bold mb-1">
                CONTACT
            </span>
            <h1 class="text-3xl sm:text-4xl font-bold text-ink">Hubungi Kami & Lokasi GOR</h1>
        </div>

        <a href="{{ route('home') }}"
           class="self-start sm:self-auto inline-flex items-center gap-2 px-5 py-2.5 rounded-full bg-paper hover:bg-cream border border-ink/20 text-xs font-bold text-ink shadow-sm transition">
            <span>←</span>
            <span>Kembali ke Home</span>
        </a>
    </div>

    <div class="gfn-reveal grid grid-cols-1 lg:grid-cols-12 gap-8 items-center">
        <div class="lg:col-span-6 space-y-4">
            <div>
                <span class="block text-[11px] uppercase tracking-widest text-accent font-bold mb-1">
                    CONTACT & LOKASI
                </span>
                <h2 class="text-2xl sm:text-3xl font-bold text-ink">
                    Hubungi Admin & Lokasi GOR
                </h2>
            </div>

            <div class="space-y-3 pt-1">
                <div class="rounded-2xl sm:rounded-full px-6 py-4 bg-paper/75 border border-ink/20 text-xs sm:text-sm text-ink shadow-sm">
                    <span class="font-bold text-ink">Alamat Arena:</span> Jl. Raya Sport Center No. 88, Kelapa Dua, Tangerang, Banten 15810
                </div>

                <div class="rounded-2xl sm:rounded-full px-6 py-4 bg-paper/75 border border-ink/20 text-xs sm:text-sm text-ink shadow-sm">
                    <span class="font-bold text-ink">WhatsApp Admin / Kasir:</span>
                    <a href="https://wa.me/6281288009900" target="_blank" rel="noopener noreferrer"
                       class="font-medium text-ink hover:text-accent underline decoration-ink/30">
                        0812-8800-9900
                    </a>
                    <span class="text-ink/85">(Fast Response 08:00 – 23:00 WIB)</span>
                </div>

                <div class="rounded-2xl sm:rounded-full px-6 py-4 bg-paper/75 border border-ink/20 text-xs sm:text-sm text-ink shadow-sm">
                    <span class="font-bold text-ink">Email Resmi:</span>
                    <a href="mailto:reservasi@gorfutsalnusantara.id"
                       class="font-medium text-ink hover:text-accent underline decoration-ink/30">
                        reservasi@gorfutsalnusantara.id
                    </a>
                </div>
            </div>
        </div>

        <div class="lg:col-span-6">
            <div class="rounded-3xl bg-sand/75 border border-ink/25 p-8 sm:p-10 min-h-[250px] flex flex-col items-center justify-center text-center shadow-sm">
                <div class="w-14 h-14 rounded-full bg-[#8B3A2B] text-white flex items-center justify-center shadow-md mb-4">
                    <svg class="w-6 h-6" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                        <path d="M12 2C8.686 2 6 4.686 6 8c0 4.418 6 12 6 12s6-7.582 6-12c0-3.314-2.686-6-6-6zm0 8.5a2.5 2.5 0 1 1 0-5 2.5 2.5 0 0 1 0 5z"/>
                    </svg>
                </div>

                <h3 class="font-sans text-xs sm:text-sm font-extrabold uppercase tracking-wider text-ink">
                    PIN LOKASI: GOR FUTSAL NUSANTARA
                </h3>

                <p class="font-mono text-xs text-ink/75 mt-1.5">
                    -6.2415° S, 106.6289° E • Tangerang
                </p>

                <a href="https://www.google.com/maps/search/?api=1&query=-6.2415,106.6289" target="_blank" rel="noopener noreferrer"
                   class="mt-5 inline-flex items-center gap-1.5 px-6 py-2.5 rounded-full bg-paper hover:bg-cream border border-ink/20 text-xs font-bold text-ink shadow-sm transition">
                    <span>Buka di Google Maps</span>
                    <span>↗</span>
                </a>
            </div>
        </div>
    </div>

    <div class="gfn-reveal space-y-4 pt-2">
        <div>
            <span class="block text-xs uppercase tracking-widest text-accent font-bold mb-1">
                PUSAT INFORMASI PENYEWA
            </span>
            <h2 class="text-2xl sm:text-3xl font-bold text-ink">Bantuan & Tata Cara Reservasi</h2>
        </div>

        <div class="wf-box rounded-2xl p-6 sm:p-8 space-y-5">
            <div class="border-b border-ink/15 pb-4">
                <h3 class="text-base font-bold text-ink">1. Bagaimana cara memesan lapangan?</h3>
                <p class="text-xs text-ink/80 mt-1 leading-relaxed">
                    Pilih lapangan di halaman utama, buka kalender tahunan untuk memilih tanggal dan slot jam (bisa multi-jam maupun lintas hari), isi data pemesan, lalu pilih skema pembayaran DP 50% atau Lunas 100%.
                </p>
            </div>
            <div class="border-b border-ink/15 pb-4">
                <h3 class="text-base font-bold text-ink">2. Berapa lama batas waktu pembayaran QRIS?</h3>
                <p class="text-xs text-ink/80 mt-1 leading-relaxed">
                    Setelah kode booking terbit, Anda memiliki waktu 30 menit untuk melakukan transfer via QRIS dan mengunggah bukti pembayaran agar slot tidak terbuka kembali secara otomatis.
                </p>
            </div>
            <div>
                <h3 class="text-base font-bold text-ink">3. Bagaimana pelunasan jika memilih DP 50%?</h3>
                <p class="text-xs text-ink/80 mt-1 leading-relaxed">
                    Sisa 50% dibayarkan langsung di meja kasir GOR Futsal Nusantara (tunai atau QRIS) sebelum jadwal pertandingan dimulai dengan menunjukkan kode E-Invoice Anda.
                </p>
            </div>
        </div>
    </div>

    <div class="gfn-reveal rounded-3xl sm:rounded-full border border-dashed border-ink/30 bg-paper/60 px-6 sm:px-8 py-4 sm:py-5 flex flex-col sm:flex-row items-center justify-between gap-4">
        <p class="text-xs sm:text-sm text-ink/85 text-center sm:text-left">
            Sudah tahu jadwal yang diinginkan? Langsung pesan lapangan dari halaman Home.
        </p>

        <a href="{{ route('booking.create') }}"
           class="wf-btn inline-flex items-center gap-2 px-6 py-3 rounded-full bg-ink hover:bg-ink/90 text-paper text-xs font-bold shrink-0">
            <span>Pilih Lapangan & Booking</span>
            <span>→</span>
        </a>
    </div>
</div>
@endsection
