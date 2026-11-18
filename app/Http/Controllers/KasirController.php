<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\BookingItem;
use App\Models\Field;
use App\Models\FieldMaintenanceBlock;
use App\Models\Payment;
use App\Traits\HandlesBookingLifecycle;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class KasirController extends Controller
{
    use HandlesBookingLifecycle;

    private function currentPortalUser(Request $request)
    {
        if ($request->is('admin*')) {
            return Auth::guard('admin')->user();
        }
        if ($request->is('kasir*')) {
            return Auth::guard('kasir')->user();
        }
        return Auth::user();
    }

    public function index(Request $request)
    {
        $this->syncExpiredBookings();

        $user = $this->currentPortalUser($request);
        $isAdmin = $user && $user->role === 'admin';
        $routePrefix = $request->is('admin*') ? 'admin' : 'kasir';
        $search = trim((string) $request->input('search', ''));
        $statusFilter = $request->input('status', 'all');

        $query = Booking::with(['items.field', 'payments.verifier'])
            ->latest();

        if (! $isAdmin) {
            $query->where(function ($q) {
                $q->whereDate('created_at', today())
                    ->orWhereIn('status', ['pending_payment', 'pending_verification', 'dp_paid', 'playing'])
                    ->orWhereHas('items', fn ($iq) => $iq->whereDate('booking_date', '>=', today()));
            });
        }

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('booking_code', 'like', "%{$search}%")
                    ->orWhere('customer_name', 'like', "%{$search}%")
                    ->orWhere('customer_phone', 'like', "%{$search}%");
            });
        }

        $bookings = $query->get();
        foreach ($bookings as $booking) {
            if ($booking instanceof Booking) {
                $booking->display = $this->resolveBookingDisplay($booking);
            }
        }

        if ($statusFilter !== 'all') {
            $bookings = $bookings->filter(fn ($b) => $b->display['key'] === $statusFilter)->values();
        }

        $activeFields = Field::orderBy('id')->get();

        $maintenanceBlocks = collect();
        if (Schema::hasTable('field_maintenance_blocks')) {
            $maintenanceBlocks = FieldMaintenanceBlock::with('field')
                ->whereDate('block_date', '>=', today())
                ->orderBy('block_date')
                ->orderBy('start_time')
                ->get();
        }

        return view('kasir.dashboard', compact(
            'bookings',
            'activeFields',
            'maintenanceBlocks',
            'isAdmin',
            'routePrefix',
            'search',
            'statusFilter'
        ));
    }

    public function verifyPayment(Request $request, Payment $payment)
    {
        $staffId = $this->currentPortalUser($request)?->id;

        DB::transaction(function () use ($payment, $staffId) {
            $payment->update([
                'status'           => 'verified',
                'verified_by'      => $staffId,
                'rejection_reason' => null,
            ]);

            $booking = $payment->booking;
            $verifiedTotal = $booking->payments()->where('status', 'verified')->sum('amount');
            $newStatus = $verifiedTotal >= $booking->total_amount ? 'paid' : 'dp_paid';

            $booking->update([
                'status'     => $newStatus,
                'expired_at' => null,
            ]);
            $booking->touch();
        });

        return back()->with('success', 'Pembayaran berhasil diverifikasi dan jadwal telah dikunci.');
    }

    public function rejectPayment(Request $request, Payment $payment)
    {
        $request->validate([
            'rejection_reason' => 'required|string|max:255',
        ]);

        $staffId = $this->currentPortalUser($request)?->id;

        DB::transaction(function () use ($request, $payment, $staffId) {
            $payment->update([
                'status'           => 'rejected',
                'verified_by'      => $staffId,
                'rejection_reason' => $request->rejection_reason,
            ]);

            $payment->booking->update([
                'status' => 'rejected',
            ]);
        });

        return back()->with('success', 'Bukti pembayaran ditolak dengan catatan untuk penyewa.');
    }

    public function settleRemaining(Request $request, Booking $booking)
    {
        $request->validate([
            'method' => 'required|in:cash,qris',
            'amount' => 'nullable|integer|min:1',
        ]);

        $staffId = $this->currentPortalUser($request)?->id;

        DB::transaction(function () use ($request, $booking, $staffId) {
            $verifiedSum = (int) $booking->payments()->where('status', 'verified')->sum('amount');
            $remaining = max(0, (int) $booking->total_amount - $verifiedSum);
            $payAmount = $request->filled('amount') ? min($remaining, (int) $request->input('amount')) : $remaining;

            if ($payAmount > 0) {
                Payment::create([
                    'booking_id'    => $booking->id,
                    'verified_by'   => $staffId,
                    'payment_stage' => 'settlement',
                    'method'        => $request->input('method'),
                    'amount'        => $payAmount,
                    'status'        => 'verified',
                ]);
            }

            $newVerified = $verifiedSum + $payAmount;
            $newStatus = $newVerified >= $booking->total_amount ? 'paid' : 'dp_paid';

            $booking->update([
                'status'     => $booking->status === 'playing' ? 'playing' : $newStatus,
                'expired_at' => null,
            ]);
        });

        return back()->with('success', 'Pembayaran pelunasan berhasil dicatat.');
    }

    public function startMatch(Request $request, Booking $booking)
    {
        $user = $this->currentPortalUser($request);
        abort_unless($user && $user->role === 'admin', 403, 'Hanya Admin yang dapat menandai status Sedang Main.');

        if (! in_array($booking->status, ['dp_paid', 'paid'], true)) {
            return back()->withErrors(['match' => 'Hanya pesanan berstatus On Going (terverifikasi) yang dapat dimulai.']);
        }

        $updateData = ['status' => 'playing'];
        if (Schema::hasColumn('bookings', 'started_at')) {
            $updateData['started_at'] = now();
        }

        $booking->update($updateData);

        return back()->with('success', "Tim {$booking->customer_name} ({$booking->booking_code}) ditandai SEDANG MAIN.");
    }

    public function reschedule(Request $request, Booking $booking)
    {
        $validated = $request->validate([
            'field_id'     => 'required|exists:fields,id',
            'booking_date' => 'required|date',
            'start_hour'   => 'required|date_format:H:i',
        ]);

        $field = Field::findOrFail($validated['field_id']);
        $slotCount = max(1, $booking->items->count());
        $startHourInt = (int) explode(':', $validated['start_hour'])[0];

        if ($startHourInt < $field->open_hour_value || ($startHourInt + $slotCount) > $field->close_hour_value) {
            return back()->withErrors(['reschedule' => 'Jadwal baru berada di luar jam operasional lapangan.']);
        }

        DB::transaction(function () use ($validated, $booking, $field, $slotCount, $startHourInt) {
            $newHours = [];
            for ($i = 0; $i < $slotCount; $i++) {
                $newHours[] = str_pad((string) ($startHourInt + $i), 2, '0', STR_PAD_LEFT) . ':00:00';
            }

            $conflict = BookingItem::where('field_id', $field->id)
                ->where('booking_id', '!=', $booking->id)
                ->whereDate('booking_date', $validated['booking_date'])
                ->whereIn('start_time', $newHours)
                ->whereHas('booking', function ($query) {
                    $query->whereIn('status', ['pending_payment', 'pending_verification', 'dp_paid', 'paid', 'playing', 'rejected'])
                        ->where(function ($q) {
                            $q->where('status', '!=', 'pending_payment')
                                ->orWhere('expired_at', '>', now());
                        });
                })
                ->lockForUpdate()
                ->exists();

            if ($conflict) {
                throw ValidationException::withMessages([
                    'reschedule' => 'Jadwal tujuan bentrok dengan pesanan lain yang sudah terkunci.',
                ]);
            }

            if (Schema::hasTable('field_maintenance_blocks')) {
                $maintConflict = FieldMaintenanceBlock::where('field_id', $field->id)
                    ->whereDate('block_date', $validated['booking_date'])
                    ->whereIn('start_time', $newHours)
                    ->exists();

                if ($maintConflict) {
                    throw ValidationException::withMessages([
                        'reschedule' => 'Jadwal tujuan sedang diblokir untuk maintenance lapangan.',
                    ]);
                }
            }

            $booking->items()->delete();

            for ($i = 0; $i < $slotCount; $i++) {
                $start = Carbon::createFromFormat('H:i', str_pad((string) ($startHourInt + $i), 2, '0', STR_PAD_LEFT) . ':00');
                BookingItem::create([
                    'booking_id'   => $booking->id,
                    'field_id'     => $field->id,
                    'booking_date' => $validated['booking_date'],
                    'start_time'   => $start->format('H:i:s'),
                    'end_time'     => $start->copy()->addHour()->format('H:i:s'),
                    'subtotal'     => $field->price_per_hour,
                ]);
            }

            $booking->update([
                'total_amount' => $slotCount * $field->price_per_hour,
            ]);
        });

        return back()->with('success', "Jadwal booking {$booking->booking_code} berhasil di-reschedule.");
    }

    public function storeWalkIn(Request $request)
    {
        $this->syncExpiredBookings();

        $validated = $request->validate([
            'field_id'       => 'required|exists:fields,id',
            'booking_date'   => 'required|date|after_or_equal:today',
            'hours'          => 'required|array|min:1',
            'hours.*'        => 'required|date_format:H:i',
            'customer_name'  => 'required|string|max:100',
            'customer_phone' => 'required|string|max:20',
            'payment_scheme' => 'required|in:full,dp',
            'method'         => 'required|in:cash,qris',
        ], [
            'hours.required'          => 'Pilih minimal 1 slot jam bermain terlebih dahulu.',
            'hours.min'               => 'Pilih minimal 1 slot jam bermain terlebih dahulu.',
            'customer_name.required'  => 'Nama pemesan atau tim wajib diisi.',
            'customer_phone.required' => 'Nomor WhatsApp aktif wajib diisi.',
        ]);

        $field = Field::findOrFail($validated['field_id']);

        if ($field->status !== 'active') {
            throw ValidationException::withMessages([
                'field_id' => "Lapangan {$field->name} sedang dalam status maintenance.",
            ]);
        }

        $selectedHours = collect($validated['hours'])->unique()->sort()->values();
        $openHour = $field->open_hour_value;
        $closeHour = $field->close_hour_value;

        foreach ($selectedHours as $hourStr) {
            $hInt = (int) explode(':', $hourStr)[0];
            if ($hInt < $openHour || $hInt >= $closeHour) {
                throw ValidationException::withMessages([
                    'hours' => "Jam {$hourStr} berada di luar jam operasional lapangan.",
                ]);
            }
        }

        $staffId = $this->currentPortalUser($request)?->id;

        $booking = DB::transaction(function () use ($validated, $field, $selectedHours, $staffId) {
            $hourTimes = $selectedHours->map(fn ($h) => $h.':00');

            $conflictingItems = BookingItem::where('field_id', $field->id)
                ->whereDate('booking_date', $validated['booking_date'])
                ->whereIn('start_time', $hourTimes)
                ->whereHas('booking', function ($query) {
                    $query->whereIn('status', ['pending_payment', 'pending_verification', 'dp_paid', 'paid', 'playing', 'rejected'])
                        ->where(function ($q) {
                            $q->where('status', '!=', 'pending_payment')
                                ->orWhere('expired_at', '>', now());
                        });
                })
                ->lockForUpdate()
                ->pluck('start_time')
                ->map(function ($t) {
                    $start = Carbon::parse($t);
                    return $start->format('H:i').' - '.$start->copy()->addHour()->format('H:i');
                })
                ->unique()
                ->values();

            if ($conflictingItems->isNotEmpty()) {
                $slotLabels = $conflictingItems->implode(', ');
                throw ValidationException::withMessages([
                    'hours' => "Slot jam {$slotLabels} sudah terisi / dibooking pada jadwal lapangan. Silakan pilih jam lain yang tersedia.",
                ]);
            }

            if (Schema::hasTable('field_maintenance_blocks')) {
                $maintConflicts = FieldMaintenanceBlock::where('field_id', $field->id)
                    ->whereDate('block_date', $validated['booking_date'])
                    ->whereIn('start_time', $hourTimes)
                    ->pluck('start_time')
                    ->map(function ($t) {
                        $start = Carbon::parse($t);
                        return $start->format('H:i').' - '.$start->copy()->addHour()->format('H:i');
                    })
                    ->unique()
                    ->values();

                if ($maintConflicts->isNotEmpty()) {
                    $maintLabels = $maintConflicts->implode(', ');
                    throw ValidationException::withMessages([
                        'hours' => "Slot jam {$maintLabels} sedang diblokir untuk maintenance lapangan.",
                    ]);
                }
            }

            $totalAmount = $selectedHours->count() * $field->price_per_hour;
            $paidNow = $validated['payment_scheme'] === 'dp'
                ? (int) round($totalAmount * 0.5)
                : $totalAmount;

            do {
                $bookingCode = 'GOR-'.strtoupper(Str::random(6));
            } while (Booking::where('booking_code', $bookingCode)->exists());

            $newBooking = Booking::create([
                'booking_code'   => $bookingCode,
                'customer_name'  => $validated['customer_name'],
                'customer_phone' => $validated['customer_phone'],
                'total_amount'   => $totalAmount,
                'payment_scheme' => $validated['payment_scheme'],
                'status'         => $validated['payment_scheme'] === 'dp' ? 'dp_paid' : 'paid',
                'expired_at'     => null,
            ]);

            foreach ($selectedHours as $hour) {
                $start = Carbon::createFromFormat('H:i', $hour);
                BookingItem::create([
                    'booking_id'   => $newBooking->id,
                    'field_id'     => $field->id,
                    'booking_date' => $validated['booking_date'],
                    'start_time'   => $start->format('H:i:s'),
                    'end_time'     => $start->copy()->addHour()->format('H:i:s'),
                    'subtotal'     => $field->price_per_hour,
                ]);
            }

            Payment::create([
                'booking_id'    => $newBooking->id,
                'verified_by'   => $staffId,
                'payment_stage' => $validated['payment_scheme'],
                'method'        => $validated['method'],
                'amount'        => $paidNow,
                'status'        => 'verified',
            ]);

            return $newBooking;
        });

        return back()->with('success', 'Booking walk-in berhasil dicatat dengan kode: '.$booking->booking_code);
    }

    public function storeMaintenanceBlock(Request $request)
    {
        if (! Schema::hasTable('field_maintenance_blocks')) {
            return back()->withErrors(['maintenance' => 'Jalankan php artisan migrate terlebih dahulu untuk mengaktifkan blokir maintenance per jam.']);
        }

        $validated = $request->validate([
            'field_id'   => 'required|exists:fields,id',
            'block_date' => 'required|date|after_or_equal:today',
            'start_hour' => 'required|date_format:H:i',
            'reason'     => 'required|string|max:255',
        ]);

        $start = Carbon::createFromFormat('H:i', $validated['start_hour']);

        $hasBooking = BookingItem::where('field_id', $validated['field_id'])
            ->whereDate('booking_date', $validated['block_date'])
            ->where('start_time', $start->format('H:i:s'))
            ->whereHas('booking', fn ($q) => $q->whereIn('status', ['pending_payment', 'pending_verification', 'dp_paid', 'paid', 'playing']))
            ->exists();

        if ($hasBooking) {
            return back()->withErrors(['maintenance' => 'Jam tersebut sudah memiliki reservasi pelanggan aktif. Reschedule pesanan terlebih dahulu sebelum memblokir.']);
        }

        $staffId = $this->currentPortalUser($request)?->id;

        FieldMaintenanceBlock::firstOrCreate(
            [
                'field_id'   => $validated['field_id'],
                'block_date' => $validated['block_date'],
                'start_time' => $start->format('H:i:s'),
            ],
            [
                'end_time'   => $start->copy()->addHour()->format('H:i:s'),
                'reason'     => $validated['reason'],
                'created_by' => $staffId,
            ]
        );

        return back()->with('success', 'Blokir jam maintenance lapangan berhasil diaktifkan.');
    }

    public function destroyMaintenanceBlock(FieldMaintenanceBlock $block)
    {
        $block->delete();

        return back()->with('success', 'Blokir maintenance per jam berhasil dihapus dan slot dibuka kembali.');
    }

    public function cancelExpired(Booking $booking)
    {
        if (in_array($booking->status, ['pending_payment', 'expired'], true)) {
            $booking->update(['status' => 'cancelled']);
        }

        return back()->with('success', 'Booking kedaluwarsa telah dibatalkan dan slot jam kembali tersedia.');
    }
}
