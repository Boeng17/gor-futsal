@props([
    'kode' => 'GOR-2026-089',
    'nominal' => 150000,
    'merchant' => 'GOR FUTSAL NUSANTARA',
    'nmid' => 'ID1020304050607'
])

<div x-data="{
        seconds: 900,
        init() {
            setInterval(() => { if (this.seconds > 0) this.seconds--; }, 1000);
        },
        get formatted() {
            const m = String(Math.floor(this.seconds / 60)).padStart(2, '0');
            const s = String(this.seconds % 60).padStart(2, '0');
            return m + ':' + s;
        }
    }"
    class="p-5 rounded-2xl border-2 border-ink bg-paper text-center space-y-3">
    <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-amber-100 border border-amber-700 text-amber-950 font-mono text-[11px] font-bold">
        <span>⏳ BATAS WAKTU BAYAR:</span>
        <span x-text="formatted">15:00</span>
    </div>

    <div>
        <div class="font-extrabold text-xs tracking-wider uppercase text-ink">{{ $merchant }}</div>
        <div class="font-mono text-[10px] text-ink/60">NMID: {{ $nmid }} • TANGERANG</div>
    </div>

    <!-- QRIS Vector Frame -->
    <div class="w-48 h-48 mx-auto p-3 rounded-xl border-2 border-ink bg-white flex items-center justify-center">
        <svg viewBox="0 0 120 120" class="w-full h-full">
            <rect x="6" y="6" width="32" height="32" fill="none" stroke="#232B20" stroke-width="5"/>
            <rect x="14" y="14" width="16" height="16" fill="#232B20"/>
            <rect x="82" y="6" width="32" height="32" fill="none" stroke="#232B20" stroke-width="5"/>
            <rect x="90" y="14" width="16" height="16" fill="#232B20"/>
            <rect x="6" y="82" width="32" height="32" fill="none" stroke="#232B20" stroke-width="5"/>
            <rect x="14" y="90" width="16" height="16" fill="#232B20"/>
            <rect x="48" y="10" width="8" height="8" fill="#232B20"/>
            <rect x="64" y="18" width="8" height="16" fill="#232B20"/>
            <rect x="46" y="46" width="28" height="28" rx="4" fill="#6C4830"/>
            <text x="60" y="63" text-anchor="middle" fill="#FFF" font-size="8" font-family="monospace" font-weight="bold">QRIS</text>
            <rect x="10" y="50" width="16" height="8" fill="#232B20"/>
            <rect x="86" y="52" width="24" height="8" fill="#232B20"/>
            <rect x="50" y="86" width="12" height="24" fill="#232B20"/>
            <rect x="74" y="84" width="16" height="12" fill="#232B20"/>
            <rect x="98" y="96" width="14" height="14" fill="#232B20"/>
        </svg>
    </div>

    <div class="p-3 rounded-xl bg-cream/70 border border-ink/20">
        <div class="text-[10px] font-mono text-ink/65">NOMINAL TRANSFER ({{ $kode }})</div>
        <div class="text-xl font-extrabold font-mono text-accent mt-0.5">
            Rp {{ number_format($nominal, 0, ',', '.') }}
        </div>
    </div>
    <p class="text-[11px] text-ink/70 leading-relaxed">
        Scan menggunakan m-Banking, GoPay, OVO, DANA, atau ShopeePay lalu unggah bukti transfer pada langkah berikutnya.
    </p>
</div>
