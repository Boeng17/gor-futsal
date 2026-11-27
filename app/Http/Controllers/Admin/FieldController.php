<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\ImageHelper;
use App\Http\Controllers\Controller;
use App\Models\Field;
use App\Services\QrisService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class FieldController extends Controller
{
    public function index(QrisService $qrisService)
    {
        $fields = Field::orderBy('id')->get();
        $qrisConfig = $qrisService->getConfig();

        return view('admin.fields.index', compact('fields', 'qrisConfig'));
    }

    public function updateQris(Request $request, QrisService $qrisService)
    {
        $validated = $request->validate([
            'raw_qris'      => 'required|string|min:30',
            'merchant_name' => 'required|string|max:100',
            'merchant_city' => 'required|string|max:100',
        ], [
            'raw_qris.required' => 'Payload EMVCo QRIS wajib diisi atau di-scan dari gambar QRIS statis Anda.',
        ]);

        try {
            $qrisService->saveMerchantConfig(
                $validated['raw_qris'],
                $validated['merchant_name'] ?? null,
                $validated['merchant_city'] ?? null
            );
        } catch (\InvalidArgumentException $e) {
            return back()->withErrors(['raw_qris' => $e->getMessage()]);
        }

        return back()->with('success', 'Konfigurasi QRIS Merchant berhasil diperbarui! Nominal DP & Lunas kini otomatis terisi saat pelanggan scan.');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'           => 'required|string|max:100',
            'description'    => 'nullable|string|max:500',
            'price_per_hour' => 'required|integer|min:0',
            'open_hour'      => 'nullable|integer|min:0|max:23',
            'close_hour'     => 'nullable|integer|min:1|max:24|gt:open_hour',
            'status'         => 'required|in:active,maintenance',
            'image'          => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        if (! Schema::hasColumn('fields', 'open_hour')) {
            unset($validated['open_hour'], $validated['close_hour']);
        } else {
            $validated['open_hour'] = $validated['open_hour'] ?? 8;
            $validated['close_hour'] = $validated['close_hour'] ?? 23;
        }

        if ($request->hasFile('image')) {
            $validated['image'] = ImageHelper::toCompressedBase64($request->file('image'));
        }

        Field::create($validated);

        return back()->with('success', 'Data lapangan baru berhasil ditambahkan.');
    }

    public function update(Request $request, Field $field)
    {
        $validated = $request->validate([
            'name'           => 'required|string|max:100',
            'description'    => 'nullable|string|max:500',
            'price_per_hour' => 'required|integer|min:0',
            'open_hour'      => 'nullable|integer|min:0|max:23',
            'close_hour'     => 'nullable|integer|min:1|max:24|gt:open_hour',
            'status'         => 'required|in:active,maintenance',
            'image'          => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        if (! Schema::hasColumn('fields', 'open_hour')) {
            unset($validated['open_hour'], $validated['close_hour']);
        } else {
            $validated['open_hour'] = $validated['open_hour'] ?? $field->open_hour_value;
            $validated['close_hour'] = $validated['close_hour'] ?? $field->close_hour_value;
        }

        if ($request->hasFile('image')) {
            $validated['image'] = ImageHelper::toCompressedBase64($request->file('image'));
        }

        $field->update($validated);

        return back()->with('success', 'Data lapangan berhasil diperbarui.');
    }

    public function toggleStatus(Field $field)
    {
        $newStatus = $field->status === 'active' ? 'maintenance' : 'active';
        $field->update(['status' => $newStatus]);

        $label = $newStatus === 'active' ? 'AKTIF' : 'MAINTENANCE';

        return back()->with('success', "Status {$field->name} diubah menjadi {$label}.");
    }

    public function destroy(Field $field)
    {
        if ($field->bookingItems()->exists()) {
            return back()->withErrors([
                'field' => 'Lapangan tidak dapat dihapus karena sudah memiliki riwayat pemesanan. Ubah status ke Maintenance jika tidak digunakan.',
            ]);
        }

        $field->delete();

        return back()->with('success', 'Data lapangan berhasil dihapus.');
    }
}
