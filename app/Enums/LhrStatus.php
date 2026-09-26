<?php

namespace App\Enums;

enum LhrStatus: string
{
    case Draft = 'draft';
    case MenungguTelaah = 'menunggu_telaah';
    case PerluPerbaikan = 'perlu_perbaikan';
    case MenungguPersetujuan = 'menunggu_persetujuan';
    case Terbit = 'terbit';
    case TindakLanjut = 'tindak_lanjut';
    case Selesai = 'selesai';
    case Dibatalkan = 'dibatalkan';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Draft / Disusun',
            self::MenungguTelaah => 'Menunggu Telaah Dalnis (Irban)',
            self::PerluPerbaikan => 'Perlu Perbaikan',
            self::MenungguPersetujuan => 'Menunggu Persetujuan Inspektur',
            self::Terbit => 'Terbit Resmi',
            self::TindakLanjut => 'Dalam Tindak Lanjut',
            self::Selesai => 'Selesai',
            self::Dibatalkan => 'Dibatalkan',
        };
    }

    public function badgeColor(): string
    {
        return match ($this) {
            self::Draft => 'gray',
            self::MenungguTelaah => 'blue',
            self::PerluPerbaikan => 'yellow',
            self::MenungguPersetujuan => 'purple',
            self::Terbit => 'indigo',
            self::TindakLanjut => 'amber',
            self::Selesai => 'green',
            self::Dibatalkan => 'red',
        };
    }

    public function canEdit(): bool
    {
        return in_array($this, [self::Draft, self::PerluPerbaikan], true);
    }
}
