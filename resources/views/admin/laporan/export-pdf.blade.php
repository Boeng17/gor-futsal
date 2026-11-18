@extends('layouts.print')

@section('title', 'Export PDF Laporan Pendapatan — GOR Futsal Nusantara')

@section('content')
<div class="max-w-4xl mx-auto space-y-4">
    <div class="no-print flex items-center justify-between bg-white p-3 rounded-xl border border-zinc-300">
        <a href="{{ route('admin.laporan.index') }}" class="text-xs font-bold underline">← Kembali ke Laporan</a>
        <button onclick="window.print()" class="px-4 py-2 rounded-lg bg-[#6C4830] text-white text-xs font-bold">🖨️ Cetak / Simpan PDF</button>
    </div>

    <div class="print-sheet bg-white p-8 rounded-2xl border-2 border-[#232B20] space-y-6">
        <div class="border-b-2 border-[#232B20] pb-4 flex justify-between items-end">
            <div>
                <div class="font-mono text-xs font-bold text-[#6C4830]">LAPORAN REKAPITULASI RESERVASI & KEUANGAN</div>
                <h1 class="text-2xl font-extrabold mt-1">GOR FUTSAL NUSANTARA</h1>
            </div>
            <div class="font-mono text-xs text-right">Periode: November 2026</div>
        </div>

        <table class="w-full text-xs border-collapse border border-[#232B20]">
            <thead>
                <tr class="bg-zinc-100">
                    <th class="border border-[#232B20] p-2 text-left">Kode</th>
                    <th class="border border-[#232B20] p-2 text-left">Pemesan</th>
                    <th class="border border-[#232B20] p-2 text-left">Jadwal</th>
                    <th class="border border-[#232B20] p-2 text-right">Nominal Masuk</th>
                    <th class="border border-[#232B20] p-2 text-center">Status</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td class="border border-[#232B20] p-2 font-mono">GOR-2026-089</td>
                    <td class="border border-[#232B20] p-2">Fajar Apriliansyah</td>
                    <td class="border border-[#232B20] p-2 font-mono">Lap A • 17 Nov (19:00-21:00)</td>
                    <td class="border border-[#232B20] p-2 text-right font-mono">Rp 180.000</td>
                    <td class="border border-[#232B20] p-2 text-center font-mono">DP 50%</td>
                </tr>
                <tr>
                    <td class="border border-[#232B20] p-2 font-mono">GOR-2026-084</td>
                    <td class="border border-[#232B20] p-2">Dimas Saputra</td>
                    <td class="border border-[#232B20] p-2 font-mono">Lap C • 13 Nov (20:00-21:00)</td>
                    <td class="border border-[#232B20] p-2 text-right font-mono">Rp 160.000</td>
                    <td class="border border-[#232B20] p-2 text-center font-mono">LUNAS</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
@endsection
