<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TimeSyncService
{
    /**
     * Daftar NTP pool server prioritas Indonesia dan global.
     */
    protected array $ntpServers = [
        'id.pool.ntp.org',
        '0.id.pool.ntp.org',
        'time.google.com',
        'pool.ntp.org',
    ];

    /**
     * Ambang batas toleransi perbedaan waktu (time drift) dalam detik.
     */
    public const MAX_TOLERATED_DRIFT_SECONDS = 1.0;

    /**
     * Memeriksa status sinkronisasi waktu antara server lokal, database, dan NTP server.
     */
    public function checkSyncStatus(): array
    {
        $appTimezone = config('app.timezone', 'Asia/Jakarta');
        $localTime = Carbon::now($appTimezone);

        $dbInfo = $this->getDatabaseTimeInfo();
        $ntpResult = $this->queryNtpServer();

        $driftSeconds = $ntpResult['drift'] ?? 0.0;
        $isSynced = $ntpResult['success'] && (abs($driftSeconds) <= self::MAX_TOLERATED_DRIFT_SECONDS);

        return [
            'is_synced' => $isSynced,
            'app_timezone' => $appTimezone,
            'local_time' => $localTime->format('Y-m-d H:i:s.u P'),
            'db_time' => $dbInfo['time'] ?? 'N/A',
            'db_timezone' => $dbInfo['timezone'] ?? 'N/A',
            'ntp_server' => $ntpResult['server'] ?? 'N/A',
            'ntp_time' => $ntpResult['time'] ? $ntpResult['time']->format('Y-m-d H:i:s.u P') : 'N/A',
            'drift_seconds' => round($driftSeconds, 4),
            'ntp_reachable' => $ntpResult['success'],
            'message' => $isSynced 
                ? 'Sumber waktu tersinkronisasi dengan benar (NTP Active).'
                : ($ntpResult['success'] 
                    ? "Peringatan: Selisih waktu terdeteksi sebesar {$driftSeconds} detik melampaui ambang batas." 
                    : 'Peringatan: Tidak dapat menghubungi server NTP luar. Menggunakan waktu RTC lokal.'),
        ];
    }

    /**
     * Mengambil informasi waktu dan timezone dari koneksi database aktif.
     */
    public function getDatabaseTimeInfo(): array
    {
        try {
            $driver = DB::connection()->getDriverName();

            if ($driver === 'mysql') {
                $row = DB::selectOne("SELECT NOW() as db_time, @@session.time_zone as session_tz, @@global.time_zone as global_tz");
                return [
                    'time' => $row->db_time,
                    'timezone' => $row->session_tz === 'SYSTEM' ? ($row->global_tz . ' (SYSTEM)') : $row->session_tz,
                ];
            }

            if ($driver === 'sqlite') {
                $row = DB::selectOne("SELECT datetime('now', 'localtime') as db_time");
                return [
                    'time' => $row->db_time ?? 'N/A',
                    'timezone' => 'SQLite Localtime',
                ];
            }

            return ['time' => now()->toDateTimeString(), 'timezone' => 'Default'];
        } catch (\Throwable $e) {
            return ['time' => 'Database Offline', 'timezone' => 'Error: ' . $e->getMessage()];
        }
    }

    /**
     * Mengirim query UDP port 123 ke server NTP pool dan menghitung drift.
     */
    public function queryNtpServer(): array
    {
        foreach ($this->ntpServers as $server) {
            try {
                $socket = @fsockopen('udp://' . $server, 123, $errCode, $errStr, 2);
                if (! $socket) {
                    continue;
                }

                stream_set_timeout($socket, 2);

                // Format paket RFC 5905 NTPv4 request (48 bytes: 0x23 / 0x1b client mode)
                $packet = "\x1b" . str_repeat("\0", 47);
                fwrite($socket, $packet);

                $response = fread($socket, 48);
                fclose($socket);

                if (strlen($response) === 48) {
                    // Transmit timestamp berada di offset 40 (4 byte integer seconds, 4 byte fraction)
                    $secondsData = substr($response, 40, 4);
                    $fractionData = substr($response, 44, 4);

                    $seconds = unpack('N', $secondsData)[1];
                    $fraction = unpack('N', $fractionData)[1];

                    // NTP epoch dimulai 1 Jan 1900, UNIX epoch dimulai 1 Jan 1970 (selisih 2,208,988,800 detik)
                    $unixSeconds = $seconds - 2208988800;
                    $unixMicro = (float) $fraction / (float) 0xFFFFFFFF;
                    $ntpTimestamp = (float) $unixSeconds + $unixMicro;

                    $currentLocalTimestamp = microtime(true);
                    $drift = $currentLocalTimestamp - $ntpTimestamp;

                    $ntpCarbon = Carbon::createFromTimestampUTC((int) $unixSeconds)
                        ->setTimezone(config('app.timezone', 'Asia/Jakarta'));

                    return [
                        'success' => true,
                        'server' => $server,
                        'time' => $ntpCarbon,
                        'drift' => $drift,
                    ];
                }
            } catch (\Throwable $e) {
                // Lanjut coba server berikutnya
            }
        }

        // Jika seluruh UDP port 123 terhalang (misal sandbox firewall), fallback
        return [
            'success' => false,
            'server' => 'N/A (Offline/Firewalled)',
            'time' => null,
            'drift' => 0.0,
        ];
    }
}
