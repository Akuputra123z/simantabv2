<?php

namespace App\Helpers;

class MenuHelper
{
    public static function getMainNavItems(): array
    {
        $user = auth()->user();
        $dashboardRoute = $user?->hasRole('opd')
            ? route('opd.dashboard', absolute: false)
            : route('dashboard', absolute: false);

        return [
            [
                'icon' => 'dashboard',
                'name' => 'Dashboard',
                'path' => $dashboardRoute,
            ],
        ];
    }

    public static function getAdministrationItems(): array
    {
        return [

            // ================= MASTER DATA =================
            [
                'icon' => 'database',
                'name' => 'Master Data',
                'subItems' => [
                    [
                        'name' => 'Kode Rekomendasi',
                        'path' => route('kode-rekomendasi.index', absolute: false),
                    ],
                    [
                        'name' => 'Kode Temuan',
                        'path' => route('kode-temuan.index', absolute: false),
                    ],
                    [
                        'name' => 'Objek Pemeriksaan',
                        'path' => route('unit-diperiksa.index', absolute: false),
                    ],
                    [
                        'name' => 'Pegawai Inspektorat',
                        'path' => route('pegawai.inspektorat.index', absolute: false),
                    ],
                    [
                        'name' => 'Pegawai Per Instansi/OPD',
                        'path' => route('pegawai.opd.index', absolute: false),
                    ],
                ],
            ],

            // ================= AUDIT =================
            [
                'icon' => 'task',
                'name' => 'Program Pengawasan',
                'subItems' => [
                    [
                        'name' => 'Program Kegiatan',
                        'path' => route('audit-program.index', absolute: false),
                    ],
                    [
                        'name' => 'Penugasan Audit',
                        'path' => route('audit-assignment.index', absolute: false),
                    ],
                ],
            ],

            // ================= LHP =================
            [
                'icon' => 'pages',
                'name' => 'LHP & Tindak Lanjut',
                'subItems' => [
                    [
                        'name' => 'LHP',
                        'path' => route('lhps.index', absolute: false),
                    ],
                    [
                        'name' => 'Rekomendasi',
                        'path' => route('recommendations.index', absolute: false),
                    ],
                    [
                        'name' => 'Tindak Lanjut',
                        'path' => route('tindak-lanjuts.index', absolute: false),
                    ],
                ],
            ],

            // ================= LAPORAN =================
            [
                'icon' => 'charts',
                'name' => 'Laporan',
                'subItems' => [
                    [
                        'name' => 'Laporan',
                        'path' => route('laporan.index', absolute: false),
                    ],
                ],
            ],

            // ================= USER =================
            [
                'icon' => 'user-profile',
                'name' => 'Manajemen Pengguna',
                'subItems' => [
                    [
                        'name' => 'Hak Akses',
                        'path' => route('permissions.index', absolute: false),
                    ],
                ],
            ],

            // ================= AKUN =================
            [
                'icon' => 'user-profile',
                'name' => 'Akun Saya',
                'subItems' => [
                    [
                        'name' => 'Profil Saya',
                        'path' => route('settings.profile.edit', absolute: false),
                    ],
                ],
            ],
        ];
    }

    public static function getMenuGroups(): array
    {
        $user = auth()->user();

        if ($user && $user->hasRole('opd')) {
            return [
                [
                    'title' => 'Menu',
                    'items' => self::getMainNavItems(),
                ],
                [
                    'title' => 'Tindak Lanjut',
                    'items' => self::getOpdItems(),
                ],
            ];
        }

        return [
            [
                'title' => 'Menu',
                'items' => self::getMainNavItems(),
            ],
            [
                'title' => 'Administration',
                'items' => self::getAdministrationItems(),
            ],
        ];
    }

    public static function getOpdItems(): array
    {
        return [
            [
                'icon' => 'task',
                'name' => 'Tindak Lanjut',
                'subItems' => [
                    [
                        'name' => 'Tindak Lanjut Saya',
                        'path' => route('opd.tindak-lanjut.index', absolute: false),
                    ],
                ],
            ],
            [
                'icon' => 'user-profile',
                'name' => 'Akun',
                'subItems' => [
                    [
                        'name' => 'Profil Saya',
                        'path' => route('opd.profile.edit', absolute: false),
                    ],
                ],
            ],
        ];
    }

    public static function isActive($path): bool
    {
        return request()->is(ltrim($path, '/'));
    }

    public static function icon(string $name, string $class = ''): string
    {
        $map = [
            'dashboard' => 'heroicon-o-squares-2x2',
            'database'  => 'heroicon-o-circle-stack',
            'task'      => 'heroicon-o-clipboard-document-list',
            'pages'     => 'heroicon-o-document-text',
            'charts'    => 'heroicon-o-chart-bar',
            'user-profile' => 'heroicon-o-user',
            'forms'     => 'heroicon-o-view-columns',
            'tables'    => 'heroicon-o-table-cells',
            'ai-assistant' => 'heroicon-o-sparkles',
            'ecommerce' => 'heroicon-o-shopping-cart',
            'calendar'  => 'heroicon-o-calendar',
            'ui-elements' => 'heroicon-o-cube',
            'authentication' => 'heroicon-o-shield-check',
            'chat'      => 'heroicon-o-chat-bubble-left-right',
            'support-ticket' => 'heroicon-o-lifebuoy',
            'email'     => 'heroicon-o-envelope',
        ];

        $heroicon = $map[$name] ?? 'heroicon-o-question-mark-circle';

        return svg($heroicon, $class)->toHtml();
    }
}