<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Payment;
use App\Traits\HandlesBookingLifecycle;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReportController extends Controller
{
    use HandlesBookingLifecycle;

    public function index(Request $request)
    {
        $this->syncExpiredBookings();

        $user = $request->is('kasir*')
            ? Auth::guard('kasir')->user()
            : Auth::guard('admin')->user();
        $isKasir = ($user && $user->role === 'kasir') || $request->is('kasir*');
        $reportRoute = $isKasir ? 'kasir.reports.index' : 'admin.reports.index';

        if ($isKasir) {
            $startDate = today()->toDateString();
            $endDate = today()->toDateString();
            $preset = 'today';
        } else {
            $preset = $request->input('preset', 'custom');
            if ($preset === 'today') {
                $startDate = today()->toDateString();
                $endDate = today()->toDateString();
            } elseif ($preset === '7days') {
                $startDate = now()->subDays(6)->toDateString();
                $endDate = today()->toDateString();
            } elseif ($preset === 'all') {
                $startDate = '';
                $endDate = '';
            } else {
                $startDate = $request->input('start_date', now()->startOfMonth()->toDateString());
                $endDate = $request->input('end_date', today()->toDateString());
            }
        }

        $statusFilter = $request->input('status', 'all');
        $search = trim((string) $request->input('search', ''));

        $bookings = $this->queryBookings($startDate, $endDate, $statusFilter, $search);

        $paymentQuery = Payment::where('status', 'verified');
        if ($startDate !== '') {
            $paymentQuery->whereDate('created_at', '>=', $startDate);
        }
        if ($endDate !== '') {
            $paymentQuery->whereDate('created_at', '<=', $endDate);
        }
        $verifiedPayments = $paymentQuery->get();

        $totalRevenue = (int) $verifiedPayments->sum('amount');
        $qrisRevenue = (int) $verifiedPayments->where('method', 'qris')->sum('amount');
        $cashRevenue = (int) $verifiedPayments->where('method', 'cash')->sum('amount');
        $pendingReceivable = (int) $bookings->sum(fn ($b) => $b->display['remaining']);
        $totalTransactions = $bookings->count();

        if ($request->input('export') === 'csv') {
            return $this->streamCsv($bookings, $startDate, $endDate, $isKasir);
        }

        return view('admin.reports.index', compact(
            'bookings',
            'startDate',
            'endDate',
            'preset',
            'statusFilter',
            'search',
            'isKasir',
            'reportRoute',
            'totalRevenue',
            'qrisRevenue',
            'cashRevenue',
            'pendingReceivable',
            'totalTransactions'
        ));
    }

    private function queryBookings(string $startDate, string $endDate, string $statusFilter, string $search)
    {
        $query = Booking::with(['items.field', 'payments.verifier'])
            ->latest();

        if ($startDate !== '' && $endDate !== '') {
            $query->where(function ($q) use ($startDate, $endDate) {
                $q->whereBetween('created_at', [
                    Carbon::parse($startDate)->startOfDay(),
                    Carbon::parse($endDate)->endOfDay(),
                ])->orWhereHas('items', function ($iq) use ($startDate, $endDate) {
                    $iq->whereDate('booking_date', '>=', $startDate)
                        ->whereDate('booking_date', '<=', $endDate);
                });
            });
        }

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('booking_code', 'like', "%{$search}%")
                    ->orWhere('customer_name', 'like', "%{$search}%")
                    ->orWhere('customer_phone', 'like', "%{$search}%");
            });
        }

        $collection = $query->get();
        foreach ($collection as $booking) {
            if ($booking instanceof Booking) {
                $booking->display = $this->resolveBookingDisplay($booking);
            }
        }

        if ($statusFilter !== 'all' && $statusFilter !== '') {
            $collection = $collection->filter(function ($booking) use ($statusFilter) {
                $key = $booking->display['key'];
                if ($statusFilter === 'on_going') {
                    return in_array($key, ['on_going', 'dp_paid'], true);
                }
                if ($statusFilter === 'paid') {
                    return $booking->status === 'paid' && $key !== 'expired';
                }
                if ($statusFilter === 'cancelled_rejected') {
                    return in_array($key, ['cancelled', 'rejected'], true);
                }
                return $key === $statusFilter;
            })->values();
        }

        return $collection;
    }

    private function streamCsv(\Illuminate\Support\Collection $bookings, string $startDate, string $endDate, bool $isKasir)
    {
        $label = $isKasir ? today()->toDateString() : (($startDate ?: 'awal') . '_sd_' . ($endDate ?: 'sekarang'));
        $filename = "laporan-keuangan-gor-{$label}.csv";

        return response()->streamDownload(function () use ($bookings) {
            $handle = fopen('php://output', 'w');
            fwrite($handle, "\xEF\xBB\xBF");

            fputcsv($handle, [
                'Kode Booking',
                'Tanggal Input',
                'Nama Pemesan / Tim',
                'No WhatsApp',
                'Lapangan & Jadwal Main',
                'Skema',
                'Total Biaya (Rp)',
                'Sudah Terbayar (Rp)',
                'Sisa Tagihan (Rp)',
                'Status',
                'Verifikator',
            ]);

            foreach ($bookings as $b) {
                $schedules = $b->items->map(function ($item) {
                    return ($item->field?->name ?? 'Lapangan') . ' ' .
                        Carbon::parse($item->booking_date)->format('d/m/Y') . ' ' .
                        Carbon::parse($item->start_time)->format('H:i') . '-' .
                        Carbon::parse($item->end_time)->format('H:i');
                })->implode(' | ');

                $verifiers = $b->payments->pluck('verifier.name')->filter()->unique()->implode(', ');

                fputcsv($handle, [
                    $b->booking_code,
                    $b->created_at->format('d/m/Y H:i'),
                    $b->customer_name,
                    $b->customer_phone,
                    $schedules,
                    strtoupper($b->payment_scheme),
                    $b->total_amount,
                    $b->display['paid'],
                    $b->display['remaining'],
                    $b->display['label'],
                    $verifiers ?: '-',
                ]);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }
}
