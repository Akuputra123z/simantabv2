<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Pastikan cache spatie dibersihkan
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // Daftarkan role irban
        $irbanRole = Role::firstOrCreate([
            'name' => User::ROLE_IRBAN,
            'guard_name' => 'web',
        ]);

        // Copy permissions dari pengendali_teknis jika ada
        $pengendaliTeknis = Role::where('name', User::ROLE_PENGENDALI_TEKNIS)
            ->where('guard_name', 'web')
            ->first();

        if ($pengendaliTeknis) {
            $irbanRole->syncPermissions($pengendaliTeknis->permissions);
        } else {
            $irbanPermissions = [
                'lhp.view',
                'temuan.view',
                'rekomendasi.view',
                'tindak-lanjut.view',
                'tindak-lanjut.edit',
                'cicilan.view',
                'cicilan.verifikasi',
                'audit-assignment.view',
                'laporan.view',
                'lhr.view',
                'lhr.review',
                'lhr.download-final',
                'dataset.import',
                'comparison.run',
                'lhtl.view',
                'lhtl.review',
                'tl.approve-tidak-dapat',
                'ba.confirm',
            ];

            foreach ($irbanPermissions as $permName) {
                Permission::firstOrCreate([
                    'name' => $permName,
                    'guard_name' => 'web',
                ]);
            }

            $irbanRole->syncPermissions($irbanPermissions);
        }

        app()[PermissionRegistrar::class]->forgetCachedPermissions();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Rollback tidak menghapus jika ada user yang memakainya
        $irbanRole = Role::where('name', User::ROLE_IRBAN)
            ->where('guard_name', 'web')
            ->first();

        if ($irbanRole && $irbanRole->users()->count() === 0) {
            $irbanRole->delete();
        }

        app()[PermissionRegistrar::class]->forgetCachedPermissions();
    }
};
