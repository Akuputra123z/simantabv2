<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UserService
{
    public function rules(?User $ignore = null): array
    {
        $uniqueEmail = Rule::unique('users', 'email');
        $uniqueNip  = Rule::unique('users', 'nip');

        if ($ignore) {
            $uniqueEmail = $uniqueEmail->ignore($ignore->id);
            $uniqueNip   = $uniqueNip->ignore($ignore->id);
        }

        return [
            'name'                => ['required', 'string', 'max:255'],
            'email'               => ['required', 'email', 'max:255', $uniqueEmail],
            'nip'                 => ['nullable', 'string', 'max:30', $uniqueNip],
            'jabatan'             => ['nullable', 'string', Rule::in(User::JABATAN_OPTIONS)],
            'phone'               => ['nullable', 'string', 'max:20'],
            'role'                => ['required', 'string', 'exists:roles,name'],
            'is_active'           => ['boolean'],
            'unit_kerja'          => ['nullable', 'string', Rule::in(User::UNIT_KERJA_OPTIONS)],
            'jenis_kelamin'       => ['nullable', 'string', Rule::in(['L', 'P'])],
            'pendidikan_terakhir' => ['nullable', 'string', 'max:100'],
            'pangkat_gol'         => ['nullable', 'string', 'max:50'],
        ];
    }

    public function passwordRules(bool $required = true): array
    {
        $rules = [Password::min(8)->letters()->numbers()];
        if ($required) {
            array_unshift($rules, 'required');
        } else {
            array_unshift($rules, 'nullable');
        }
        return $rules;
    }

    public function create(array $data, string $role, ?array $opdUnitIds = null): User
    {
        $user = User::create([
            'name'                => $data['name'],
            'email'               => $data['email'],
            'nip'                 => $data['nip'] ?? null,
            'jabatan'             => $data['jabatan'] ?? null,
            'unit_kerja'          => $data['unit_kerja'] ?? null,
            'phone'               => $data['phone'] ?? null,
            'password'            => Hash::make($data['password']),
            'is_active'           => $data['is_active'] ?? true,
            'jenis_kelamin'       => $data['jenis_kelamin'] ?? null,
            'pendidikan_terakhir' => $data['pendidikan_terakhir'] ?? null,
            'pangkat_gol'         => $data['pangkat_gol'] ?? null,
        ]);

        $user->assignRole($role);

        if ($role === User::ROLE_OPD && $opdUnitIds) {
            $user->opdUnits()->sync($opdUnitIds);
        }

        return $user;
    }

    public function update(User $user, array $data, string $role, ?array $opdUnitIds = null): User
    {
        $user->update([
            'name'                => $data['name'],
            'email'               => $data['email'],
            'nip'                 => $data['nip'] ?? null,
            'jabatan'             => $data['jabatan'] ?? null,
            'unit_kerja'          => $data['unit_kerja'] ?? null,
            'phone'               => $data['phone'] ?? null,
            'is_active'           => $data['is_active'] ?? true,
            'jenis_kelamin'       => $data['jenis_kelamin'] ?? null,
            'pendidikan_terakhir' => $data['pendidikan_terakhir'] ?? null,
            'pangkat_gol'         => $data['pangkat_gol'] ?? null,
        ]);

        if (! empty($data['password'])) {
            $user->update(['password' => Hash::make($data['password'])]);
        }

        $user->syncRoles([$role]);

        if ($role === User::ROLE_OPD && $opdUnitIds) {
            $user->opdUnits()->sync($opdUnitIds);
        } elseif ($user->hasRole(User::ROLE_OPD)) {
            $user->opdUnits()->sync([]);
        }

        return $user;
    }
}
