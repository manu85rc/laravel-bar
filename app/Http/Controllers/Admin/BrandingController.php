<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Branding;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class BrandingController extends Controller
{
    /**
     * Show the form for editing the branding.
     */
    public function edit()
    {
        $branding = Branding::getInstance();
        return view('admin.branding.edit', compact('branding'));
    }

    /**
     * Update the branding in storage.
     */
    public function update(Request $request)
    {
        $request->validate([
            'app_name' => 'required|string|max:255',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'favicon' => 'nullable|image|mimes:ico,png,jpg,gif,svg|max:2048',
        ]);

        $branding = Branding::getInstance();

        $data = $request->only('app_name');

        if ($request->hasFile('logo')) {
            // Delete old logo if exists
            if ($branding->logo_path) {
                Storage::delete('public/' . $branding->logo_path);
            }
            $path = $request->file('logo')->store('logos', 'public');
            $data['logo_path'] = $path;
        }

        if ($request->hasFile('favicon')) {
            // Delete old favicon if exists
            if ($branding->favicon_path) {
                Storage::delete('public/' . $branding->favicon_path);
            }
            $path = $request->file('favicon')->store('favicons', 'public');
            $data['favicon_path'] = $path;
        }

        $branding->update($data);

        return redirect()->route('admin.branding.edit')
            ->with('success', 'Branding actualizado exitosamente.');
    }
}
