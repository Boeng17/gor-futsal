<!-- Mobile Top Bar Admin -->
<div class="lg:hidden sticky top-0 z-30 bg-ink text-paper border-b border-ink px-4 py-3 space-y-2.5">
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-2">
            <span class="px-2 py-0.5 rounded bg-accent text-white font-mono text-[10px] font-bold">ADMIN</span>
            <span class="font-extrabold text-xs">GOR Backoffice Panel</span>
        </div>
        <a href="{{ route('home') }}" class="px-2.5 py-1 rounded-lg border border-cream/30 text-amber-300 text-[10px] font-mono">
            ← Portal Web
        </a>
    </div>
    <div class="grid grid-cols-3 gap-1.5 text-[11px] font-bold">
        <a href="{{ route('admin.laporan.index') }}"
           class="py-1.5 px-2 rounded-lg text-center {{ request()->routeIs('admin.laporan.*') ? 'bg-paper text-ink' : 'bg-white/10 text-cream' }}">
            06. Laporan
        </a>
        <a href="{{ route('admin.kasir.index') }}"
           class="py-1.5 px-2 rounded-lg text-center {{ request()->routeIs('admin.kasir.*') ? 'bg-paper text-ink' : 'bg-white/10 text-cream' }}">
            07. Kasir
        </a>
        <a href="{{ route('admin.cms.index') }}"
           class="py-1.5 px-2 rounded-xl text-center {{ request()->routeIs('admin.cms.*') ? 'bg-paper text-ink' : 'bg-white/10 text-cream' }}">
            08. CMS
        </a>
    </div>
</div>

<!-- Desktop Sidebar Admin -->
<aside class="hidden lg:flex w-64 shrink-0 bg-ink text-paper border-r border-ink flex-col justify-between p-5">
    <div class="space-y-6">
        <div class="pb-4 border-b border-cream/15">
            <div class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded bg-accent text-white font-mono text-[10px] font-bold">
                BACKOFFICE PANEL
            </div>
            <div class="font-extrabold text-base mt-2.5 tracking-tight">GOR Futsal Nusantara</div>
            <div class="text-[11px] text-cream/65 font-mono mt-0.5">Role: Admin & Kasir GOR</div>
        </div>

        <nav class="space-y-1.5 text-xs font-bold">
            <div class="text-[10px] font-mono uppercase tracking-wider text-cream/50 px-2 pb-1">Menu Operasional</div>
            <a href="{{ route('admin.laporan.index') }}"
               class="w-full px-3.5 py-2.5 rounded-xl flex items-center gap-2.5 transition {{ request()->routeIs('admin.laporan.*') ? 'bg-accent text-white shadow-sm' : 'text-cream/80 hover:bg-white/10' }}">
                <span>📅</span>
                <span>06. Kalender & Laporan</span>
            </a>
            <a href="{{ route('admin.kasir.index') }}"
               class="w-full px-3.5 py-2.5 rounded-xl flex items-center gap-2.5 transition {{ request()->routeIs('admin.kasir.*') ? 'bg-accent text-white shadow-sm' : 'text-cream/80 hover:bg-white/10' }}">
                <span>💵</span>
                <span>07. Kasir & Operasional</span>
            </a>
            <a href="{{ route('admin.cms.index') }}"
               class="w-full px-3.5 py-2.5 rounded-xl flex items-center gap-2.5 transition {{ request()->routeIs('admin.cms.*') ? 'bg-accent text-white shadow-sm' : 'text-cream/80 hover:bg-white/10' }}">
                <span>⚙️</span>
                <span>08. CMS Lapangan & QRIS</span>
            </a>
        </nav>
    </div>

    <div class="pt-4 border-t border-cream/15 space-y-2">
        <div class="p-3 rounded-xl bg-white/5 text-[11px] text-cream/75 leading-relaxed">
            Pastikan setiap bukti transfer QRIS dicek mutasinya sebelum klik <strong>Terima Verifikasi</strong>.
        </div>
        <a href="{{ route('home') }}"
           class="w-full py-2.5 px-3 rounded-xl border border-cream/30 hover:bg-white/10 text-amber-300 text-xs font-mono font-bold flex items-center justify-center gap-2 transition">
            <span>← Kembali ke Web Pelanggan</span>
        </a>
    </div>
</aside>
