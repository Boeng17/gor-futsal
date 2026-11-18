<?php

namespace App\Http\Controllers\Backoffice;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\BookingItem;
use App\Models\Payment;
use App\Traits\HandlesBookingLifecycle;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    use HandlesBookingLifecycle;

    public function index(Request $request)
    {
        $this->syncExpiredBookings();

        $reportRoute = $request->is('kasir*') ? 'kasir.reports.index' : 'admin.reports.index';

        $endCurrent = now()->endOfDay();
        $startCurrent = now()->subDays(6)->startOfDay();

        $endPrev = $startCurrent->copy()->subSecond();
        $startPrev = $startCurrent->copy()->subDays(7)->startOfDay();

        $periodLabel = $startCurrent->translatedFormat('d M Y') . ' - ' . $endCurrent->translatedFormat('d M Y');

        $currentRevenue = (int) Payment::where('status', 'verified')
            ->whereBetween('created_at', [$startCurrent, $endCurrent])
            ->sum('amount');

        $prevRevenue = (int) Payment::where('status', 'verified')
            ->whereBetween('created_at', [$startPrev, $endPrev])
            ->sum('amount');

        $revenueGrowth = $this->calcGrowth($currentRevenue, $prevRevenue);

        $currentSlots = BookingItem::whereHas('booking', function ($q) use ($startCurrent, $endCurrent) {
            $q->whereNotIn('status', ['cancelled', 'expired'])
                ->whereBetween('created_at', [$startCurrent, $endCurrent]);
        })->count();

        $prevSlots = BookingItem::whereHas('booking', function ($q) use ($startPrev, $endPrev) {
            $q->whereNotIn('status', ['cancelled', 'expired'])
                ->whereBetween('created_at', [$startPrev, $endPrev]);
        })->count();

        $slotsGrowth = $this->calcGrowth($currentSlots, $prevSlots);

        $currentBookings = Booking::whereNotIn('status', ['cancelled'])
            ->whereBetween('created_at', [$startCurrent, $endCurrent])
            ->count();

        $prevBookings = Booking::whereNotIn('status', ['cancelled'])
            ->whereBetween('created_at', [$startPrev, $endPrev])
            ->count();

        $bookingsGrowth = $this->calcGrowth($currentBookings, $prevBookings);

        $dailyStats = [];
        $maxRevenue = 1;
        $maxBookings = 1;

        for ($i = 6; $i >= 0; $i--) {
            $day = now()->subDays($i);
            $dayDate = $day->toDateString();

            $dayRev = (int) Payment::where('status', 'verified')
                ->whereDate('created_at', $dayDate)
                ->sum('amount');

            $dayCount = Booking::whereDate('created_at', $dayDate)->count();

            if ($dayRev > $maxRevenue) {
                $maxRevenue = $dayRev;
            }
            if ($dayCount > $maxBookings) {
                $maxBookings = $dayCount;
            }

            $dailyStats[] = [
                'label'          => $day->format('d/m'),
                'full_date'      => $day->translatedFormat('d M Y'),
                'revenue'        => $dayRev,
                'bookings_count' => $dayCount,
            ];
        }

        foreach ($dailyStats as &$stat) {
            $stat['revenue_pct'] = $stat['revenue'] > 0 ? max(12, (int) round(($stat['revenue'] / $maxRevenue) * 100)) : 6;
            $stat['bookings_pct'] = $stat['bookings_count'] > 0 ? max(12, (int) round(($stat['bookings_count'] / $maxBookings) * 100)) : 6;
        }
        unset($stat);

        $latestBookings = Booking::with(['items.field', 'payments'])
            ->latest()
            ->take(6)
            ->get();

        foreach ($latestBookings as $booking) {
            if ($booking instanceof Booking) {
                $booking->display = $this->resolveBookingDisplay($booking);
            }
        }

        return view('backoffice.dashboard', compact(
            'reportRoute',
            'periodLabel',
            'currentRevenue',
            'revenueGrowth',
            'currentSlots',
            'slotsGrowth',
            'currentBookings',
            'bookingsGrowth',
            'dailyStats',
            'maxRevenue',
            'maxBookings',
            'latestBookings'
        ));
    }

    private function calcGrowth(int $current, int $previous): float
    {
        if ($previous <= 0) {
            return $current > 0 ? 100.0 : 0.0;
        }

        return round((($current - $previous) / $previous) * 100, 2);
    }
}
