<?php

namespace App\Traits;

use App\Models\Booking;
use Carbon\Carbon;

trait HandlesBookingLifecycle
{
    protected function syncExpiredBookings(): void
    {
        Booking::where('status', 'pending_payment')
            ->whereNotNull('expired_at')
            ->where('expired_at', '<=', now())
            ->update(['status' => 'expired']);

        $playingCandidates = Booking::with('items')
            ->where('status', 'playing')
            ->get();

        foreach ($playingCandidates as $booking) {
            $latestEnd = $this->resolveLatestEnd($booking);
            if ($latestEnd && $latestEnd->lte(now())) {
                $booking->update(['status' => 'paid']);
            }
        }

        $dpCandidates = Booking::with('items')
            ->where('status', 'dp_paid')
            ->get();

        foreach ($dpCandidates as $booking) {
            $latestEnd = $this->resolveLatestEnd($booking);

            if (
                $latestEnd
                && $latestEnd->lt(now())
                && $booking->updated_at
                && $booking->updated_at->lt($latestEnd)
            ) {
                $booking->update(['status' => 'expired']);
            }
        }
    }

    protected function resolveLatestEnd(Booking $booking): ?Carbon
    {
        return $booking->items->map(function ($item) {
            $dateStr = Carbon::parse($item->booking_date)->toDateString();
            $endStr = Carbon::parse($item->end_time)->format('H:i:s');
            if ($endStr === '00:00:00') {
                return Carbon::parse($dateStr . ' 23:59:59');
            }
            return Carbon::parse($dateStr . ' ' . $endStr);
        })->max();
    }

    protected function resolveBookingDisplay(Booking $booking): array
    {
        $verifiedPaid = (int) $booking->payments->where('status', 'verified')->sum('amount');
        $remaining = max(0, (int) $booking->total_amount - $verifiedPaid);

        $latestEnd = $this->resolveLatestEnd($booking);
        $isPastDue = $latestEnd && $latestEnd->lt(now());

        if ($booking->status === 'expired' || ($booking->status === 'pending_payment' && $booking->expired_at && $booking->expired_at->lte(now()))) {
            return [
                'key'         => 'expired',
                'label'       => 'Expired',
                'badge_class' => 'bg-rose-100 text-rose-900 border-rose-300',
                'dot_class'   => 'bg-rose-600',
                'paid'        => $verifiedPaid,
                'remaining'   => $remaining,
            ];
        }

        if ($booking->status === 'cancelled') {
            return [
                'key'         => 'cancelled',
                'label'       => 'Dibatalkan',
                'badge_class' => 'bg-stone-200 text-stone-800 border-stone-400',
                'dot_class'   => 'bg-rose-500',
                'paid'        => $verifiedPaid,
                'remaining'   => $remaining,
            ];
        }

        if ($booking->status === 'rejected') {
            return [
                'key'         => 'rejected',
                'label'       => 'Bukti Ditolak',
                'badge_class' => 'bg-rose-100 text-rose-900 border-rose-300',
                'dot_class'   => 'bg-rose-600',
                'paid'        => $verifiedPaid,
                'remaining'   => $remaining,
            ];
        }

        if ($booking->status === 'pending_verification') {
            return [
                'key'         => 'pending_verification',
                'label'       => 'Verifikasi QRIS',
                'badge_class' => 'bg-amber-100 text-amber-950 border-amber-400',
                'dot_class'   => 'bg-amber-500',
                'paid'        => $verifiedPaid,
                'remaining'   => $remaining,
            ];
        }

        if ($booking->status === 'pending_payment') {
            return [
                'key'         => 'pending_payment',
                'label'       => 'Menunggu Bayar',
                'badge_class' => 'bg-amber-50 text-amber-900 border-amber-300',
                'dot_class'   => 'bg-amber-500',
                'paid'        => $verifiedPaid,
                'remaining'   => $remaining,
            ];
        }

        if ($booking->status === 'playing') {
            if ($isPastDue) {
                return [
                    'key'         => 'on_going',
                    'label'       => 'Lunas • Selesai',
                    'badge_class' => 'bg-emerald-100 text-emerald-950 border-emerald-400',
                    'dot_class'   => 'bg-emerald-600',
                    'paid'        => $verifiedPaid,
                    'remaining'   => $remaining,
                ];
            }

            return [
                'key'         => 'playing',
                'label'       => 'Sedang Main',
                'badge_class' => 'bg-purple-100 text-purple-950 border-purple-300',
                'dot_class'   => 'bg-emerald-600',
                'paid'        => $verifiedPaid,
                'remaining'   => $remaining,
            ];
        }

        if ($booking->status === 'dp_paid') {
            if ($isPastDue && $booking->updated_at && $booking->updated_at->lt($latestEnd)) {
                return [
                    'key'         => 'expired',
                    'label'       => 'Expired',
                    'badge_class' => 'bg-rose-100 text-rose-900 border-rose-300',
                    'dot_class'   => 'bg-rose-600',
                    'paid'        => $verifiedPaid,
                    'remaining'   => $remaining,
                ];
            }
            return [
                'key'         => 'dp_paid',
                'label'       => 'Pelunasan Pending',
                'badge_class' => 'bg-amber-100 text-amber-950 border-amber-400',
                'dot_class'   => 'bg-amber-500',
                'paid'        => $verifiedPaid,
                'remaining'   => $remaining,
            ];
        }

        if ($booking->status === 'paid') {
            return [
                'key'         => 'on_going',
                'label'       => $isPastDue ? 'Lunas • Selesai' : 'On Going • Lunas',
                'badge_class' => 'bg-emerald-100 text-emerald-950 border-emerald-400',
                'dot_class'   => 'bg-emerald-600',
                'paid'        => $verifiedPaid,
                'remaining'   => 0,
            ];
        }

        return [
            'key'         => $booking->status,
            'label'       => ucfirst(str_replace('_', ' ', $booking->status)),
            'badge_class' => 'bg-cream text-ink border-ink/20',
            'dot_class'   => 'bg-ink',
            'paid'        => $verifiedPaid,
            'remaining'   => $remaining,
        ];
    }
}
