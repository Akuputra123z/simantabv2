<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class OpdProfileController extends Controller
{
    public function edit(Request $request): View
    {
        return view('pages.opd.profile', [
            'user' => $request->user(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'name'                 => ['required', 'string', 'max:255'],
            'email'                => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique(User::class)->ignore($user->id)],
            'nip'                  => ['nullable', 'string', 'max:50'],
            'pangkat_gol'          => ['nullable', 'string', 'max:50'],
            'jabatan'              => ['nullable', 'string', 'max:255'],
            'pendidikan_terakhir'  => ['nullable', 'string', 'max:100'],
            'jenis_kelamin'        => ['nullable', 'string', 'in:L,P'],
            'unit_kerja'           => ['nullable', 'string', 'max:255'],
            'phone'                => ['nullable', 'string', 'max:20'],
            'avatar'               => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        if ($request->hasFile('avatar')) {
            if ($user->avatar) {
                Storage::disk('public')->delete($user->avatar);
            }
            $validated['avatar'] = $request->file('avatar')->store('avatars', 'public');
        }

        $user->fill($validated);

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        return to_route('opd.profile.edit')->with('success', 'Profil berhasil diperbarui.');
    }
}
