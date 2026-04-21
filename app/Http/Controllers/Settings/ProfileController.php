<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage; // Tambahkan ini
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function edit(Request $request): View
    {
        return view('pages.auth.settings.profile', [
            'user' => $request->user(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                Rule::unique(User::class)->ignore($user->id),
            ],
            'avatar' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'], // Validasi Avatar
        ]);

        // Logika Upload Avatar
        if ($request->hasFile('avatar')) {
            // 1. Hapus avatar lama jika ada di storage
            if ($user->avatar) {
                Storage::disk('public')->delete($user->avatar);
            }

            // 2. Simpan file baru ke folder 'avatars' di disk 'public'
            $path = $request->file('avatar')->store('avatars', 'public');
            
            // 3. Masukkan path file ke array validated untuk disimpan ke DB
            $validated['avatar'] = $path;
        }

        $user->fill($validated);

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        return to_route('settings.profile.edit')->with('status', __('Profile updated successfully'));
    }

    public function destroy(Request $request): RedirectResponse
    {
        $user = $request->user();

        // Opsional: Hapus avatar dari storage saat akun dihapus
        if ($user->avatar) {
            Storage::disk('public')->delete($user->avatar);
        }

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return to_route('home');
    }
}