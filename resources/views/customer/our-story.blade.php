@extends('layouts.app')

@section('title', 'Our Story — Kisah GOR Futsal Nusantara')

@section('content')
<div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-10 sm:py-14 space-y-10">
    <div class="gfn-reveal wf-box rounded-3xl p-8 sm:p-12 space-y-6">
        <span class="inline-block px-3 py-1 rounded-full bg-cream border border-ink/20 text-[11px] uppercase tracking-widest text-accent font-bold">
            OUR STORY • VISI & DEDIKASI ARENA
        </span>

        <h1 class="text-3xl sm:text-5xl font-bold text-ink leading-tight">
            Berawal dari Kecintaan pada Pertandingan yang Adil & Tertata.
        </h1>

        <p class="text-sm sm:text-base text-ink/85 leading-relaxed">
            GOR Futsal Nusantara didirikan dari pengalaman nyata para pemain komunitas, kapten tim kampus, hingga penyelenggara turnamen indie yang sering menghadapi kendala klasik saat ingin bertanding: jadwal lapangan yang bentrok karena pencatatan manual, informasi ketersediaan jam yang tidak transparan, hingga lantai arena yang kurang terawat.
        </p>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 pt-2">
            <div class="gfn-reveal p-6 rounded-2xl bg-cream/60 border border-ink/15 space-y-2 transition-transform duration-300 hover:-translate-y-0.5">
                <span class="text-xs font-bold text-accent">01 • STANDAR ARENA KOMPETISI</span>
                <h3 class="text-xl font-bold text-ink">Kualitas Lantai & Pencahayaan</h3>
                <p class="text-xs text-ink/80 leading-relaxed">
                    Setiap lapangan—mulai dari Vinyl Interlock berdaya cengkeram tinggi hingga Rumput Sintetis generasi terbaru—dirawat secara berkala dengan jadwal maintenance yang diumumkan secara terbuka kepada pemain.
                </p>
            </div>

            <div class="gfn-reveal p-6 rounded-2xl bg-cream/60 border border-ink/15 space-y-2 transition-transform duration-300 hover:-translate-y-0.5">
                <span class="text-xs font-bold text-accent">02 • RUMAH BAGI EVENT INDIE</span>
                <h3 class="text-xl font-bold text-ink">Dukungan Penuh Komunitas</h3>
                <p class="text-xs text-ink/80 leading-relaxed">
                    Melalui kalender reservasi tahunan, penyelenggara turnamen antar-pelajar, mahasiswa, maupun komunitas lokal dapat merencanakan dan mengunci slot pertandingan lintas hari dengan mudah dan terukur.
                </p>
            </div>
        </div>

        <div class="pt-4 border-t border-ink/15 flex flex-wrap items-center justify-between gap-4">
            <div>
                <p class="text-xs font-bold text-ink">Siap merumput bersama tim Anda minggu ini?</p>
                <p class="text-xs text-ink/70">Cek slot kosong secara langsung dan kunci jadwal Anda dalam hitungan menit.</p>
            </div>
            <a href="{{ route('booking.create') }}"
               class="wf-btn px-6 py-3 rounded-full bg-accent hover:bg-accentDark text-paper text-xs font-bold">
                Lihat Kalender & Booking Lapangan →
            </a>
        </div>
    </div>
</div>
@endsection
