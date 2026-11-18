<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class BannerController extends Controller
{
    public function index()
    {
        $banners = Banner::with('creator')->latest()->get();

        return view('admin.banners.index', compact('banners'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title'     => 'required|string|max:150',
            'content'   => 'required|string',
            'image'     => 'required|image|mimes:jpg,jpeg,png,webp|max:2048',
            'is_active' => 'nullable|boolean',
        ]);

        $path = $request->file('image')->store('banners', 'public');

        Banner::create([
            'created_by' => Auth::guard('admin')->id(),
            'title'      => $validated['title'],
            'content'    => $validated['content'],
            'image'      => $path,
            'is_active'  => $request->boolean('is_active', true),
        ]);

        return back()->with('success', 'Banner event/promo berhasil dipublikasikan.');
    }

    public function update(Request $request, Banner $banner)
    {
        $validated = $request->validate([
            'title'     => 'required|string|max:150',
            'content'   => 'required|string',
            'image'     => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'is_active' => 'nullable|boolean',
        ]);

        $data = [
            'title'     => $validated['title'],
            'content'   => $validated['content'],
            'is_active' => $request->boolean('is_active', $banner->is_active),
        ];

        if ($request->hasFile('image')) {
            if ($banner->image && Storage::disk('public')->exists($banner->image)) {
                Storage::disk('public')->delete($banner->image);
            }
            $data['image'] = $request->file('image')->store('banners', 'public');
        }

        $banner->update($data);

        return back()->with('success', 'Banner event berhasil diperbarui.');
    }

    public function toggleActive(Banner $banner)
    {
        $banner->update(['is_active' => ! $banner->is_active]);

        return back()->with('success', 'Status tampilan banner berhasil diubah.');
    }

    public function destroy(Banner $banner)
    {
        if ($banner->image && Storage::disk('public')->exists($banner->image)) {
            Storage::disk('public')->delete($banner->image);
        }

        $banner->delete();

        return back()->with('success', 'Banner berhasil dihapus.');
    }
}
