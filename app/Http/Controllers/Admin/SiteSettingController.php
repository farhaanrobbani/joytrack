<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SiteSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class SiteSettingController extends Controller
{
    public function edit(): View
    {
        $settings = [
            'site_name' => SiteSetting::get('site_name', config('app.name', 'JoyTrack')),
            'hero_title' => SiteSetting::get('hero_title', 'Kelola Keuangan & Kendaraan dalam Satu Tempat'),
            'hero_subtitle' => SiteSetting::get('hero_subtitle', 'Catat transaksi, pantau saldo, kelola BBM & servis, dapatkan laporan keuangan & kendaraan — semua dengan JoyTrack.'),
            'site_icon' => SiteSetting::get('site_icon'),
        ];

        return view('admin.settings.edit', compact('settings'));
    }

    public function update(Request $request): RedirectResponse
    {
        $request->validate([
            'site_name' => ['required', 'string', 'max:100'],
            'hero_title' => ['required', 'string', 'max:255'],
            'hero_subtitle' => ['required', 'string', 'max:500'],
            'site_icon' => ['nullable', 'image', 'max:2048', 'mimes:jpg,jpeg,png,webp', 'dimensions:ratio=1/1'],
            'remove_site_icon' => ['sometimes', 'boolean'],
        ]);

        SiteSetting::set('site_name', $request->input('site_name'));
        SiteSetting::set('hero_title', $request->input('hero_title'));
        SiteSetting::set('hero_subtitle', $request->input('hero_subtitle'));

        if ($request->boolean('remove_site_icon')) {
            $old = SiteSetting::get('site_icon');
            if ($old) Storage::disk('public')->delete($old);
            // also delete generated icons
            Storage::disk('public')->delete('icons/icon-192x192.png');
            Storage::disk('public')->delete('icons/icon-512x512.png');
            foreach (['public/icons/icon-192x192.png', 'public/icons/icon-512x512.png'] as $p) {
                @unlink(public_path($p));
            }
            SiteSetting::set('site_icon', null);
        } elseif ($request->hasFile('site_icon')) {
            $old = SiteSetting::get('site_icon');
            if ($old) Storage::disk('public')->delete($old);
            $path = $request->file('site_icon')->store('settings', 'public');
            SiteSetting::set('site_icon', $path);

            // Generate PWA icons 192 and 512 from uploaded icon
            $this->generateIcons(Storage::disk('public')->path($path));
        }

        return back()->with('status', __('Pengaturan berhasil diperbarui.'));
    }

    private function generateIcons(string $sourcePath): void
    {
        if (! extension_loaded('gd')) return;

        foreach ([192, 512] as $size) {
            $src = @imagecreatefromstring(file_get_contents($sourcePath));
            if (! $src) continue;
            $dst = imagecreatetruecolor($size, $size);
            imagealphablending($dst, false);
            imagesavealpha($dst, true);
            $transparent = imagecolorallocatealpha($dst, 0, 0, 0, 127);
            imagefill($dst, 0, 0, $transparent);
            $srcW = imagesx($src);
            $srcH = imagesy($src);
            imagecopyresampled($dst, $src, 0, 0, 0, 0, $size, $size, $srcW, $srcH);
            $destDir = public_path('icons');
            if (! is_dir($destDir)) mkdir($destDir, 0755, true);
            imagepng($dst, $destDir . "/icon-{$size}x{$size}.png");
            // also store to storage for consistency
            $storageDir = storage_path('app/public/icons');
            if (! is_dir($storageDir)) mkdir($storageDir, 0755, true);
            imagepng($dst, $storageDir . "/icon-{$size}x{$size}.png");
            imagedestroy($src);
            imagedestroy($dst);
        }
    }
}
