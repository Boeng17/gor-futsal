<?php

namespace App\Http\Controllers;

use App\Models\Banner;
use App\Models\Booking;
use App\Models\BookingItem;
use App\Models\Field;
use App\Models\FieldMaintenanceBlock;
use App\Models\Payment;
use App\Services\QrisService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class BookingController extends Controller
{
    public function index()
    {
        $banners = Banner::where('is_active', true)->latest()->get();
        $fields = Field::orderBy('id')->get();

        return view('customer.home', compact('banners', 'fields'));
    }

    public function lapangan()
    {
        $fields = Field::orderBy('id')->get();

        return view('customer.lapangan', compact('fields'));
    }

    public function events()
    {
        $banners = Banner::where('is_active', true)->latest()->get();

        return view('customer.event', compact('banners'));
    }

    public function showBanner(Banner $banner)
    {
        abort_unless($banner->is_active, 404);
        $banners = Banner::where('is_active', true)->latest()->get();

        return view('customer.event-detail', compact('banner', 'banners'));
    }

    public function create(Request $request)
    {
        $fields = Field::orderBy('id')->get();
        $selectedFieldId = $request->query('field_id', $fields->firstWhere('status', 'active')?->id ?? $fields->first()?->id);
        $requestedDate = $request->query('date');
        $todayDate = now()->toDateString();
        $selectedDate = ($requestedDate && $requestedDate >= $todayDate) ? $requestedDate : $todayDate;
        $serverNow = now()->format('Y-m-d H:i:s');

        return view('customer.booking.create', compact('fields', 'selectedFieldId', 'selectedDate', 'serverNow'));
    }

    public function getSlots(Request $request)
    {
        $request->validate([
            'field_id' => 'required|exists:fields,id',
            'date'     => 'required|date',
        ]);

        $field = Field::findOrFail($request->field_id);
        $openHour = $field->open_hour_value;
        $closeHour = $field->close_hour_value;

        if ($field->status === 'maintenance') {
            return response()->json([
                'status'            => 'maintenance',
                'price_per_hour'    => $field->price_per_hour,
                'open_hour'         => $openHour,
                'close_hour'        => $closeHour,
                'booked_hours'      => [],
                'booking_hours'     => [],
                'maintenance_hours' => [],
            ]);
        }

        $bookingOnlyHours = BookingItem::where('field_id', $field->id)
            ->whereDate('booking_date', $request->date)
            ->whereHas('booking', function ($query) {
                $query->whereIn('status', ['pending_payment', 'pending_verification', 'dp_paid', 'paid', 'playing', 'rejected'])
                    ->where(function ($q) {
                        $q->where('status', '!=', 'pending_payment')
                            ->orWhere('expired_at', '>', now());
                    });
            })
            ->pluck('start_time')
            ->map(fn ($time) => Carbon::parse($time)->format('H:i'))
            ->unique()
            ->values();

        $maintenanceHours = collect();
        if (Schema::hasTable('field_maintenance_blocks')) {
            $maintenanceHours = FieldMaintenanceBlock::where('field_id', $field->id)
                ->whereDate('block_date', $request->date)
                ->pluck('start_time')
                ->map(fn ($time) => Carbon::parse($time)->format('H:i'))
                ->unique()
                ->values();
        }

        $bookedHours = $bookingOnlyHours->merge($maintenanceHours)->unique()->values();

        $pastHours = collect();
        $now = now();
        if (Carbon::parse($request->date)->isSameDay($now)) {
            $currentHour = (int) $now->format('G');
            $pastHours = collect(range(0, $currentHour))
                ->map(fn ($h) => str_pad((string) $h, 2, '0', STR_PAD_LEFT).':00')
                ->values();
        }

        return response()->json([
            'status'            => 'active',
            'price_per_hour'    => $field->price_per_hour,
            'open_hour'         => $openHour,
            'close_hour'        => $closeHour,
            'booked_hours'      => $bookedHours,
            'booking_hours'     => $bookingOnlyHours,
            'maintenance_hours' => $maintenanceHours,
            'past_hours'        => $pastHours,
            'server_now'        => $now->format('Y-m-d H:i:s'),
            'server_date'       => $now->toDateString(),
            'server_hour'       => (int) $now->format('G'),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'field_id'       => 'required|exists:fields,id',
            'slots'          => 'required|array|min:1',
            'slots.*'        => 'required|string',
            'customer_name'  => 'required|string|max:100',
            'customer_phone' => 'required|string|max:20',
            'team_name'      => 'nullable|string|max:100',
            'payment_scheme' => 'required|in:full,dp',
        ], [
            'slots.required'          => 'Pilih minimal 1 slot jam bermain terlebih dahulu.',
            'slots.min'               => 'Pilih minimal 1 slot jam bermain terlebih dahulu.',
            'customer_name.required'  => 'Nama lengkap pemesan wajib diisi.',
            'customer_phone.required' => 'Nomor WhatsApp aktif wajib diisi.',
        ]);

        $field = Field::findOrFail($validated['field_id']);

        if ($field->status !== 'active') {
            return back()->withErrors(['field_id' => 'Lapangan sedang dalam perbaikan (maintenance).'])->withInput();
        }

        $parsedSlots = collect($validated['slots'])
            ->unique()
            ->map(function ($item) {
                [$date, $hour] = explode('|', $item);
                return [
                    'date' => Carbon::parse($date)->toDateString(),
                    'hour' => Carbon::createFromFormat('H:i', trim($hour))->format('H:i'),
                ];
            })
            ->sortBy(fn ($s) => $s['date'].' '.$s['hour'])
            ->values();

        $openHour = $field->open_hour_value;
        $closeHour = $field->close_hour_value;

        $now = now();
        foreach ($parsedSlots as $slot) {
            if ($slot['date'] < $now->toDateString()) {
                return back()->withErrors(['slots' => 'Tidak dapat memesan tanggal yang sudah berlalu.'])->withInput();
            }

            $hourInt = (int) explode(':', $slot['hour'])[0];
            if ($hourInt < $openHour || $hourInt >= $closeHour) {
                return back()->withErrors(['slots' => "Jam {$slot['hour']} berada di luar jam operasional lapangan."])->withInput();
            }

            $slotStart = Carbon::createFromFormat('Y-m-d H:i', $slot['date'].' '.$slot['hour']);
            if ($slotStart->lte($now)) {
                return back()->withErrors([
                    'slots' => "Slot tanggal {$slot['date']} jam {$slot['hour']} sudah terlewat dari waktu saat ini ({$now->format('H:i')} WIB) dan tidak dapat dipesan.",
                ])->withInput();
            }
        }

        $customerLabel = $validated['customer_name'];
        if (! empty($validated['team_name'])) {
            $customerLabel .= ' ('.$validated['team_name'].')';
        }

        $booking = DB::transaction(function () use ($validated, $field, $parsedSlots, $customerLabel) {
            foreach ($parsedSlots as $slot) {
                $conflict = BookingItem::where('field_id', $field->id)
                    ->whereDate('booking_date', $slot['date'])
                    ->where('start_time', $slot['hour'].':00')
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
                        'slots' => "Slot tanggal {$slot['date']} jam {$slot['hour']} baru saja dipesan orang lain.",
                    ]);
                }

                if (Schema::hasTable('field_maintenance_blocks')) {
                    $maintBlocked = FieldMaintenanceBlock::where('field_id', $field->id)
                        ->whereDate('block_date', $slot['date'])
                        ->where('start_time', $slot['hour'].':00')
                        ->exists();

                    if ($maintBlocked) {
                        throw ValidationException::withMessages([
                            'slots' => "Slot tanggal {$slot['date']} jam {$slot['hour']} sedang diblokir untuk maintenance.",
                        ]);
                    }
                }
            }

            $totalAmount = $parsedSlots->count() * $field->price_per_hour;

            do {
                $bookingCode = 'GOR-'.strtoupper(Str::random(6));
            } while (Booking::where('booking_code', $bookingCode)->exists());

            $newBooking = Booking::create([
                'booking_code'   => $bookingCode,
                'customer_name'  => Str::limit($customerLabel, 100, ''),
                'customer_phone' => $validated['customer_phone'],
                'total_amount'   => $totalAmount,
                'payment_scheme' => $validated['payment_scheme'],
                'status'         => 'pending_payment',
                'expired_at'     => now()->addMinutes(5),
            ]);

            foreach ($parsedSlots as $slot) {
                $start = Carbon::createFromFormat('H:i', $slot['hour']);
                $end = $start->copy()->addHour();

                BookingItem::create([
                    'booking_id'   => $newBooking->id,
                    'field_id'     => $field->id,
                    'booking_date' => $slot['date'],
                    'start_time'   => $start->format('H:i:s'),
                    'end_time'     => $end->format('H:i:s'),
                    'subtotal'     => $field->price_per_hour,
                ]);
            }

            return $newBooking;
        });

        session(['active_pending_booking_code' => $booking->booking_code]);

        return redirect()->route('booking.show', $booking->booking_code)
            ->with('success', 'Pesanan berhasil dibuat! Selesaikan pembayaran QRIS sebelum batas waktu berakhir.');
    }

    public function activeBooking(Request $request)
    {
        $code = strtoupper(trim((string) ($request->query('booking_code') ?? session('active_pending_booking_code') ?? '')));

        if ($code === '') {
            return response()->json(['active' => false]);
        }

        $booking = Booking::with('items.field')
            ->where('booking_code', $code)
            ->first();

        if (! $booking) {
            if (session('active_pending_booking_code') === $code) {
                session()->forget('active_pending_booking_code');
            }
            return response()->json(['active' => false]);
        }

        $isUnpaidActive = $booking->status === 'rejected'
            || ($booking->status === 'pending_payment' && (! $booking->expired_at || $booking->expired_at->isFuture()));

        if (! $isUnpaidActive) {
            if (session('active_pending_booking_code') === $code) {
                session()->forget('active_pending_booking_code');
            }
            return response()->json(['active' => false]);
        }

        session(['active_pending_booking_code' => $booking->booking_code]);

        $firstItem = $booking->items->first();
        $fieldName = $firstItem?->field?->name ?? 'Lapangan Futsal';
        $payable = $booking->payment_scheme === 'dp'
            ? (int) round($booking->total_amount * 0.5)
            : (int) $booking->total_amount;

        $scheduleParts = $booking->items->map(function ($it) {
            return Carbon::parse($it->booking_date)->translatedFormat('d M')
                . ' (' . Carbon::parse($it->start_time)->format('H:i')
                . '-' . Carbon::parse($it->end_time)->format('H:i') . ')';
        })->implode(', ');

        return response()->json([
            'active'  => true,
            'booking' => [
                'code'              => $booking->booking_code,
                'status'            => $booking->status,
                'field_name'        => $fieldName,
                'schedule_text'     => $scheduleParts,
                'payable_formatted' => 'Rp ' . number_format($payable, 0, ',', '.'),
                'scheme_label'      => $booking->payment_scheme === 'dp' ? 'DP 50%' : 'Lunas 100%',
                'expires_at'        => $booking->expired_at ? $booking->expired_at->format('H:i:s') . ' WIB' : null,
                'expires_at_ms'     => $booking->expired_at ? $booking->expired_at->getTimestampMs() : null,
                'show_url'          => route('booking.show', $booking->booking_code),
                'cancel_url'        => route('booking.cancel', $booking->booking_code),
            ],
        ]);
    }

    public function track(Request $request)
    {
        $keyword = trim((string) ($request->input('query') ?? $request->input('booking_code') ?? ''));

        if ($keyword !== '') {
            $normalizedCode = strtoupper(preg_replace('/\s+/', '', $keyword));
            $exactBooking = Booking::where('booking_code', $normalizedCode)->first();

            if ($exactBooking) {
                return redirect()->route('booking.show', $exactBooking->booking_code);
            }

            $digits = preg_replace('/\D+/', '', $keyword);
            if (strlen($digits) >= 8) {
                $coreDigits = $digits;
                if (str_starts_with($coreDigits, '62')) {
                    $coreDigits = substr($coreDigits, 2);
                } elseif (str_starts_with($coreDigits, '0')) {
                    $coreDigits = substr($coreDigits, 1);
                }

                $candidates = Booking::with(['items.field', 'payments'])
                    ->where(function ($q) use ($digits, $coreDigits) {
                        $q->where('customer_phone', 'like', "%{$digits}%")
                            ->orWhere('customer_phone', 'like', "%{$coreDigits}%");
                    })
                    ->latest()
                    ->get()
                    ->filter(function ($b) use ($coreDigits) {
                        $phoneDigits = preg_replace('/\D+/', '', (string) $b->customer_phone);
                        return $phoneDigits !== '' && str_ends_with($phoneDigits, $coreDigits);
                    })
                    ->sortBy(function ($b) {
                        $isUnpaid = $b->status === 'rejected'
                            || ($b->status === 'pending_payment' && (! $b->expired_at || $b->expired_at->isFuture()));
                        if ($isUnpaid) {
                            return 0;
                        }
                        if (in_array($b->status, ['pending_verification', 'dp_paid', 'paid', 'playing'], true)) {
                            return 1;
                        }
                        return 2;
                    })
                    ->values();

                if ($candidates->count() === 1) {
                    return redirect()->route('booking.show', $candidates->first()->booking_code)
                        ->with('success', 'Pesanan ditemukan berdasarkan nomor WhatsApp Anda.');
                }

                if ($candidates->count() > 1) {
                    return view('customer.booking.cek-pesanan', [
                        'matchedBookings' => $candidates,
                        'searchQuery'     => $keyword,
                    ]);
                }
            }

            return back()
                ->withErrors(['query' => 'Kode booking atau nomor WhatsApp tidak ditemukan. Pastikan kode (contoh: GOR-8K29LP) atau nomor HP yang dimasukkan sesuai saat memesan.'])
                ->withInput();
        }

        return view('customer.booking.cek-pesanan');
    }

    public function show(string $booking_code, QrisService $qrisService)
    {
        $booking = Booking::with(['items.field', 'payments' => fn ($q) => $q->latest()])
            ->where('booking_code', $booking_code)
            ->firstOrFail();

        $isUnpaidActive = $booking->status === 'rejected'
            || ($booking->status === 'pending_payment' && (! $booking->expired_at || $booking->expired_at->isFuture()));

        if ($isUnpaidActive) {
            session(['active_pending_booking_code' => $booking->booking_code]);
        } elseif (session('active_pending_booking_code') === $booking->booking_code) {
            session()->forget('active_pending_booking_code');
        }

        $amountToPay = $booking->payment_scheme === 'dp'
            ? (int) round($booking->total_amount * 0.5)
            : (int) $booking->total_amount;

        $qrisConfig = $qrisService->getConfig();
        $dynamicQrisPayload = $qrisService->generateDynamicPayload($amountToPay, $booking->booking_code);

        return view('customer.booking.qris', compact('booking', 'amountToPay', 'qrisConfig', 'dynamicQrisPayload'));
    }

    public function uploadProof(Request $request, string $booking_code)
    {
        $booking = Booking::where('booking_code', $booking_code)->firstOrFail();

        if (! in_array($booking->status, ['pending_payment', 'rejected'])) {
            return back()->withErrors(['proof_image' => 'Status pesanan ini tidak memerlukan unggah bukti pembayaran lagi.']);
        }

        $request->validate([
            'sender_name' => 'required|string|max:100',
            'proof_image' => 'required|image|mimes:jpg,jpeg,png,webp|max:2048',
        ], [
            'sender_name.required' => 'Nama pengirim di rekening / e-wallet wajib diisi.',
            'proof_image.required' => 'Foto / screenshot bukti transfer wajib diunggah.',
        ]);

        $path = $request->file('proof_image')->store('proofs', 'public');

        $amount = $booking->payment_scheme === 'dp'
            ? (int) round($booking->total_amount * 0.5)
            : $booking->total_amount;

        Payment::create([
            'booking_id'    => $booking->id,
            'payment_stage' => $booking->payment_scheme,
            'method'        => 'qris',
            'amount'        => $amount,
            'proof_image'   => $path,
            'status'        => 'pending',
        ]);

        $booking->update([
            'status' => 'pending_verification',
        ]);

        if (session('active_pending_booking_code') === $booking->booking_code) {
            session()->forget('active_pending_booking_code');
        }

        return back()->with('success', 'Bukti transfer atas nama '.$request->sender_name.' berhasil dikirim dan sedang menunggu verifikasi kasir.');
    }

    public function cancel(string $booking_code)
    {
        $booking = Booking::where('booking_code', $booking_code)->firstOrFail();

        if ($booking->status === 'pending_payment') {
            $booking->update([
                'status' => 'cancelled',
            ]);

            if (session('active_pending_booking_code') === $booking->booking_code) {
                session()->forget('active_pending_booking_code');
            }

            return redirect()->route('home')->with('success', "Pesanan {$booking->booking_code} berhasil dibatalkan dan slot jam kembali tersedia.");
        }

        return back()->withErrors(['cancel' => 'Pesanan yang sudah dibayar atau diverifikasi tidak dapat dibatalkan mandiri.']);
    }

    public function invoice(string $booking_code)
    {
        $booking = Booking::with(['items.field', 'payments' => fn ($q) => $q->latest()])
            ->where('booking_code', $booking_code)
            ->firstOrFail();

        return view('customer.booking.invoice-print', compact('booking'));
    }
}
