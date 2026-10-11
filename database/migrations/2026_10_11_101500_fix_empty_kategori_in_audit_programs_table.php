<?php

use App\Models\AuditProgram;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Perbaiki data audit_programs yang memiliki kategori kosong atau null
        $emptyPrograms = DB::table('audit_programs')
            ->whereNull('kategori')
            ->orWhere('kategori', '')
            ->get();

        foreach ($emptyPrograms as $prog) {
            $nama = strtoupper((string) $prog->nama_program);
            $detected = 'PKPT'; // Standar default program pengawasan tahunan

            foreach (AuditProgram::KATEGORI as $kat) {
                if ($kat !== 'LAINNYA' && str_contains($nama, $kat)) {
                    $detected = $kat;
                    break;
                }
            }

            DB::table('audit_programs')
                ->where('id', $prog->id)
                ->update(['kategori' => $detected]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No reverse needed as we are normalizing data
    }
};
