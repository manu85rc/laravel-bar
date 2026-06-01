<?php

namespace App\Http\Controllers;

use App\Services\ImageService;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    public function edit()
    {
        return view('profile.edit');
    }

    public function updateTheme(Request $request)
    {
        $request->validate(['theme' => 'required|in:light,dark,system']);
        auth()->user()->update(['theme' => $request->theme]);

        return response()->json(['success' => true]);
    }

    public function update(Request $request, ImageService $image)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'foto' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
            'theme' => 'required|in:light,dark,system',
        ]);

        $user = auth()->user();
        $user->name = $request->name;
        $user->theme = $request->theme;

        if ($request->hasFile('foto')) {
            $image->deleteImage($user->profile_photo_path);
            $user->profile_photo_path = $image->optimizeAndStore(
                $request->file('foto'), 'profiles', 300, 300, quality: 80
            );
        }

        if ($request->has('eliminar_foto') && $user->profile_photo_path) {
            $image->deleteImage($user->profile_photo_path);
            $user->profile_photo_path = null;
        }

        $user->save();

        return redirect()->route('profile.edit')
            ->with('success', 'Perfil actualizado exitosamente.');
    }
}
