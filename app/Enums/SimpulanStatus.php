<?php

namespace App\Enums;

enum SimpulanStatus: string
{
    case BelumDinilai = 'belum_dinilai';
    case Sesuai = 'sesuai';
    case SebagianSesuai = 'sebagian_sesuai';
    case BelumSesuai = 'belum_sesuai';
    case TidakBerlaku = 'tidak_berlaku';

    public function label(): string
    {
        return match ($this) {
            self::BelumDinilai => 'Belum Dinilai',
            self::Sesuai => 'Sesuai',
            self::SebagianSesuai => 'Sebagian Sesuai',
            self::BelumSesuai => 'Belum Sesuai',
            self::TidakBerlaku => 'Tidak Berlaku',
        };
    }

    public function badgeColor(): string
    {
        return match ($this) {
            self::BelumDinilai => 'gray',
            self::Sesuai => 'green',
            self::SebagianSesuai => 'yellow',
            self::BelumSesuai => 'red',
            self::TidakBerlaku => 'slate',
        };
    }
}
