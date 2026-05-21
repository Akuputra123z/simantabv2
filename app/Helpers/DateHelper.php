<?php

namespace App\Helpers;

use Carbon\Carbon;

class DateHelper
{
    public static function formatJadwal(?string $value): string
    {
        if (!$value) return 'Tentatif';

        try {
            $date = Carbon::createFromFormat('d/m/Y', $value);
        } catch (\Exception $e) {
            try {
                $date = Carbon::parse($value);
            } catch (\Exception $e) {
                return $value;
            }
        }

        $months = [
            'January' => 'Januari', 'February' => 'Februari', 'March' => 'Maret',
            'April' => 'April', 'May' => 'Mei', 'June' => 'Juni',
            'July' => 'Juli', 'August' => 'Agustus', 'September' => 'September',
            'October' => 'Oktober', 'November' => 'November', 'December' => 'Desember',
        ];

        $englishMonth = $date->format('F');
        $indonesianMonth = $months[$englishMonth] ?? $englishMonth;

        return $indonesianMonth . ' ' . $date->format('Y');
    }

    public static function toInputDate(?string $value): string
    {
        if (!$value) return '';

        try {
            return Carbon::createFromFormat('d/m/Y', $value)->format('Y-m-d');
        } catch (\Exception $e) {
            try {
                return Carbon::parse($value)->format('Y-m-d');
            } catch (\Exception $e) {
                return '';
            }
        }
    }

    public static function toStorage(string $value): string
    {
        try {
            return Carbon::parse($value)->format('d/m/Y');
        } catch (\Exception $e) {
            return $value;
        }
    }
}
