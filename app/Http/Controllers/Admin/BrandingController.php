<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Branding;
use App\Services\ImageService;
use Illuminate\Http\Request;

class BrandingController extends Controller
{
    public function edit()
    {
        $branding = Branding::getInstance();

        return view('admin.branding.edit', compact('branding'));
    }

    public function update(Request $request, ImageService $image)
    {
        $request->validate([
            'app_name' => 'required|string|max:255',
            'tagline' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:50',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
            'favicon' => 'nullable|file|mimes:ico,png,jpg,gif,webp|max:5120',
            'font_family' => 'nullable|string|max:255',
            'primary_color' => 'nullable|string|max:20',
            'background_color' => 'nullable|string|max:20',
            'background_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
        ]);

        $branding = Branding::getInstance();

        $data = $request->only('app_name', 'tagline', 'address', 'phone', 'font_family', 'primary_color', 'background_color');

        if ($request->hasFile('logo')) {
            $image->deleteImage($branding->logo_path);
            $data['logo_path'] = $image->optimizeAndStore(
                $request->file('logo'), 'logos', 400, 200, quality: 85
            );
        }

        if ($request->hasFile('favicon')) {
            $image->deleteImage($branding->favicon_path);
            $data['favicon_path'] = $image->optimizeAndStore(
                $request->file('favicon'), 'favicons', 64, 64, convertToWebp: false, quality: 90
            );
        }

        if ($request->hasFile('background_image')) {
            $image->deleteImage($branding->background_image_path);
            $data['background_image_path'] = $image->optimizeAndStore(
                $request->file('background_image'), 'backgrounds', 1920, 1080, quality: 80
            );
        }

        if ($request->has('eliminar_background') && $branding->background_image_path) {
            $image->deleteImage($branding->background_image_path);
            $data['background_image_path'] = null;
        }

        $branding->update($data);

        return redirect()->route('admin.branding.edit')
            ->with('success', 'Branding actualizado exitosamente.');
    }
}
