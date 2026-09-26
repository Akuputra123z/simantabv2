<?php

namespace App\Actions\Lhr;

use App\Enums\LhrStatus;
use App\Models\Lhr\Lhr;
use App\Services\Lhr\LhrLinter;
use Illuminate\Validation\ValidationException;

class SubmitLhr
{
    public function __construct(
        protected LhrLinter $linter
    ) {}

    /**
     * Ajukan LHR ke Pengendali Teknis untuk penelaahan setelah lolos validasi linter.
     */
    public function execute(Lhr $lhr): Lhr
    {
        if (! $lhr->canEdit()) {
            throw new \DomainException("Dokumen LHR dengan status '{$lhr->status->label()}' tidak dapat diajukan.");
        }

        // Jalankan Quality Gate (Linter)
        $lintResult = $this->linter->lint($lhr);

        if ($lintResult->hasErrors()) {
            $messages = array_map(fn ($e) => "{$e['code']}: {$e['message']}", $lintResult->errors);
            throw ValidationException::withMessages([
                'linter' => $messages,
            ]);
        }

        $lhr->update([
            'status' => LhrStatus::MenungguTelaah,
        ]);

        return $lhr;
    }
}
