<footer class="bg-paper border-t border-ink/15 mt-16 no-print">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <div>
                <div class="flex items-center gap-2.5 mb-3">
                    <span class="w-8 h-8 rounded-lg bg-ink text-paper flex items-center justify-center font-bold text-xs">GOR</span>
                    <span class="font-extrabold text-sm tracking-tight text-ink">GOR FUTSAL NUSANTARA</span>
                </div>
                <p class="text-xs text-ink/75 leading-relaxed">
                    Arena futsal berstandar kompetisi dengan sistem pemesanan jadwal real-time anti-bentrok, dukungan reservasi acara komunitas, dan konfirmasi QRIS instan.
                </p>
            </div>

            <div>
                <h4 class="text-xs uppercase tracking-wider text-ink/60 font-bold mb-3">Navigasi Cepat</h4>
                <ul class="space-y-2 text-xs font-medium text-ink/85">
                    <li><a href="{{ route('home') }}" class="hover:text-accent">Beranda & Daftar Lapangan</a></li>
                    <li><a href="{{ route('booking.create') }}" class="hover:text-accent">Kalender & Booking Multi-Jam</a></li>
                    <li><a href="{{ route('our-story') }}" class="hover:text-accent">Our Story — Kisah GOR Nusantara</a></li>
                    <li><a href="{{ route('event.index') }}" class="hover:text-accent">Info Event & Turnamen</a></li>
                    <li><a href="{{ route('booking.track') }}" class="hover:text-accent">Cek Status E-Invoice</a></li>
                </ul>
            </div>

            <div>
                <h4 class="text-xs uppercase tracking-wider text-ink/60 font-bold mb-3">Informasi Arena</h4>
                <ul class="space-y-2 text-xs text-ink/80">
                    <li><span class="font-semibold text-ink">Jam Operasional:</span> Sesuai jadwal masing-masing lapangan (hingga 24 Jam)</li>
                    <li><span class="font-semibold text-ink">Pembayaran:</span> QRIS Dinamis Otomatis (DP 50% atau Lunas 100%) & Tunai di Kasir</li>
                    <li><span class="font-semibold text-ink">Lokasi:</span> Jl. Raya Sport Center No. 88, Kelapa Dua, Tangerang, Banten 15810</li>
                </ul>
            </div>
        </div>

        <div class="mt-8 pt-6 border-t border-ink/10 flex flex-col sm:flex-row items-center justify-between gap-3 text-[11px] text-ink/65">
            <p>&copy; {{ date('Y') }} GOR Futsal Nusantara. Seluruh hak cipta dilindungi.</p>
            <p class="font-mono">Sistem Reservasi Lapangan & Manajemen Jadwal</p>
        </div>
    </div>
</footer>
