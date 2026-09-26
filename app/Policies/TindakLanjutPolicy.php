<?php

namespace App\Policies;

use App\Models\TindakLanjut;
use App\Models\User;

class TindakLanjutPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, TindakLanjut $tindakLanjut): bool
    {
        if ($user->hasRole('super_admin')) {
            return true;
        }

        if ($user->hasRole('opd')) {
            $unitIds = $user->opdUnits()->pluck('unit_diperiksas.id');
            $targetUnitId = $tindakLanjut->recommendation?->temuan?->lhp?->unit_diperiksa_id;

            return $targetUnitId ? $unitIds->contains($targetUnitId) : false;
        }

        $assignment = $tindakLanjut->recommendation?->temuan?->lhp?->auditAssignment;
        if (! $assignment) {
            return false;
        }

        return $assignment->ketua_tim_id === $user->id
            || $assignment->members()->where('user_id', $user->id)->exists();
    }

    public function uploadOpd(User $user, TindakLanjut $tindakLanjut): bool
    {
        if (! $user->hasRole('opd')) {
            return false;
        }

        $unitIds = $user->opdUnits()->pluck('unit_diperiksas.id');

        if ($unitIds->isEmpty()) {
            return false;
        }

        return $unitIds->contains(
            $tindakLanjut->recommendation?->temuan?->lhp?->unit_diperiksa_id
        );
    }

    public function kirim(User $user, TindakLanjut $tindakLanjut): bool
    {
        if (! $user->hasRole('opd')) {
            return false;
        }

        if ($tindakLanjut->status_opd === 'dikirim') {
            return false;
        }

        $unitIds = $user->opdUnits()->pluck('unit_diperiksas.id');

        if ($unitIds->isEmpty()) {
            return false;
        }

        return $unitIds->contains(
            $tindakLanjut->recommendation?->temuan?->lhp?->unit_diperiksa_id
        );
    }

    public function bukaKunciOpd(User $user, TindakLanjut $tindakLanjut): bool
    {
        if ($user->hasRole(User::ROLE_SUPER_ADMIN)) {
            return true;
        }

        if ($user->hasRole(User::ROLE_KEPALA_INSPEKTORAT)) {
            return true;
        }

        return $user->can('tindak-lanjut.buka-kunci-opd');
    }

    public function tolakOpd(User $user, TindakLanjut $tindakLanjut): bool
    {
        if ($user->hasRole(User::ROLE_SUPER_ADMIN)) {
            return true;
        }

        if ($user->hasRole(User::ROLE_KEPALA_INSPEKTORAT)) {
            return true;
        }

        return $user->can('tindak-lanjut.tolak-opd');
    }

    public function verifikasi(User $user, TindakLanjut $tindakLanjut): bool
    {
        if ($user->hasRole(User::ROLE_SUPER_ADMIN) || $user->hasRole(User::ROLE_KEPALA_INSPEKTORAT)) {
            return true;
        }

        if ($user->hasRole(['admin', 'admin_inspektorat'])) {
            return true;
        }

        return $user->can('tindak-lanjut.verifikasi');
    }
}
