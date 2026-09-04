<?php

namespace App\Http\Controllers;

use App\Models\TindakLanjut;
use App\Models\Lhp;
use App\Models\LhpStatistik;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class OpdDashboardController extends Controller
{
    public function index(Request $request): View
    {
        $user = auth()->user();

        // ── Date Filter Parsing ──────────────────────────────────────────────
        $preset = $request->input('preset', 'this_year');
        $startDateInput = $request->input('start_date');
        $endDateInput   = $request->input('end_date');

        $now = Carbon::now();

        switch ($preset) {
            case 'today':
                $startDate = $now->copy()->startOfDay();
                $endDate   = $now->copy()->endOfDay();
                break;
            case '7days':
                $startDate = $now->copy()->subDays(6)->startOfDay();
                $endDate   = $now->copy()->endOfDay();
                break;
            case '30days':
                $startDate = $now->copy()->subDays(29)->startOfDay();
                $endDate   = $now->copy()->endOfDay();
                break;
            case 'this_month':
                $startDate = $now->copy()->startOfMonth();
                $endDate   = $now->copy()->endOfMonth();
                break;
            case 'custom':
                $startDate = $startDateInput ? Carbon::parse($startDateInput)->startOfDay() : $now->copy()->startOfYear();
                $endDate   = $endDateInput ? Carbon::parse($endDateInput)->endOfDay() : $now->copy()->endOfYear();
                break;
            case 'this_year':
            default:
                $preset    = 'this_year';
                $startDate = $now->copy()->startOfYear();
                $endDate   = $now->copy()->endOfYear();
                break;
        }

        $cacheKey = 'opd_dashboard:user:' . $user->id . ':' . $preset . ':' . $startDate->format('Y-m-d') . ':' . $endDate->format('Y-m-d') . ':' . $user->updated_at->timestamp;

        $data = Cache::remember($cacheKey, 300, function () use ($user, $startDate, $endDate) {
            $unitIds = $user->opdUnits()->pluck('unit_diperiksas.id');
            $lhpIds  = Lhp::whereIn('unit_diperiksa_id', $unitIds)
                ->whereBetween('tanggal_lhp', [$startDate, $endDate])
                ->pluck('id');

            $tlQuery = TindakLanjut::query()
                ->forOpd($user)
                ->whereHas('recommendation.temuan', fn($q) => $q->whereIn('lhp_id', $lhpIds));

            $opdStats = (clone $tlQuery)->selectRaw("
                SUM(CASE WHEN status_opd IS NULL THEN 1 ELSE 0 END) AS belum_upload,
                SUM(CASE WHEN status_opd = 'draft' THEN 1 ELSE 0 END) AS draft,
                SUM(CASE WHEN status_opd = 'dikirim' THEN 1 ELSE 0 END) AS dikirim,
                SUM(CASE WHEN alasan_tolak_opd IS NOT NULL THEN 1 ELSE 0 END) AS ditolak
            ")->first();

            $verifikasiStats = (clone $tlQuery)->selectRaw("
                SUM(CASE WHEN status_verifikasi = 'lunas' THEN 1 ELSE 0 END) AS lunas,
                SUM(CASE WHEN status_verifikasi = 'berjalan' THEN 1 ELSE 0 END) AS berjalan,
                SUM(CASE WHEN status_verifikasi = 'menunggu_verifikasi' THEN 1 ELSE 0 END) AS menunggu
            ")->first();

            $overdue = (clone $tlQuery)
                ->whereNotNull('tanggal_jatuh_tempo')
                ->where('tanggal_jatuh_tempo', '<=', now()->addDays(7))
                ->with(['recommendation.temuan.lhp.unitDiperiksa', 'recommendation.kodeRekomendasi'])
                ->orderBy('tanggal_jatuh_tempo')
                ->limit(10)
                ->get();

            $recent = (clone $tlQuery)
                ->whereNotNull('status_opd')
                ->with(['uploadOpdOleh', 'recommendation.temuan.lhp.unitDiperiksa', 'recommendation.kodeRekomendasi'])
                ->latest('updated_at')
                ->limit(10)
                ->get();

            $rekapitulasi = null;
            if ($lhpIds->isNotEmpty()) {
                $rekapitulasi = LhpStatistik::whereIn('lhp_id', $lhpIds)
                    ->selectRaw("
                        COALESCE(SUM(total_rekomendasi), 0) AS total_rekom,
                        COALESCE(SUM(rekom_selesai), 0) AS rekom_selesai,
                        COALESCE(SUM(total_kerugian), 0) AS total_kerugian,
                        COALESCE(SUM(total_nilai_tl_selesai), 0) AS total_tl_selesai
                    ")->first();
            }

            return compact(
                'opdStats',
                'verifikasiStats',
                'overdue',
                'recent',
                'rekapitulasi'
            );
        });

        return view('pages.opd.dashboard', array_merge($data, [
            'preset' => $preset,
            'startDate' => $startDate->format('Y-m-d'),
            'endDate' => $endDate->format('Y-m-d'),
            'startDateFormatted' => $startDate->translatedFormat('d M Y'),
            'endDateFormatted' => $endDate->translatedFormat('d M Y'),
        ]));
    }
}
