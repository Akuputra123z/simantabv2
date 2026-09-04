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
        $user = auth()->user();
        $items = [];

        $isSuperAdmin = $user?->hasRole('super_admin');
        $isKepala = $user?->hasRole('kepala_inspektorat');
        $isAuditor = $user?->hasRole('ketua_tim') || $user?->hasRole('anggota') || $user?->hasRole('staff_inspektorat');

        $canMasterData = $isSuperAdmin || $isKepala;
        $canAudit = $isSuperAdmin || $isKepala || $isAuditor;
        $canUserMgmt = $isSuperAdmin;

        // ================= AUDIT =================
        if ($canAudit) {
            $items[] = [
                'icon' => 'task',
                'name' => 'Program Pengawasan',
                'subItems' => [
                    [
                        'name' => 'Program Audit',
                        'path' => route('audit-program.index', absolute: false),
                    ],
                    [
                        'name' => 'Penugasan Audit',
                        'path' => route('audit-assignment.index', absolute: false),
                    ],
                   
                ],
            ];
        }

        // ================= HASIL AUDIT =================
        if ($canAudit) {
            $items[] = [
                'icon' => 'pages',
                'name' => 'LHP',
                'subItems' => [
                    [
                        'name' => 'LHP',
                        'path' => route('lhps.index', absolute: false),
                    ],
                    // [
                    //     'name' => 'Rekomendasi Audit',
                    //     'path' => route('recommendations.index', absolute: false),
                    // ],
                ],
            ];
        }

        // ================= TINDAK LANJUT =================
        if ($canAudit) {
            $items[] = [
                'icon' => 'task',
                'name' => 'Tindak Lanjut',
                'subItems' => [
                    [
                        'name' => 'Monitoring TL',
                        'path' => route('tindak-lanjuts.index', absolute: false),
                    ],
                ],
            ];
        }

        // ================= LAPORAN =================
        if ($canAudit) {
            $items[] = [
                'icon' => 'charts',
                'name' => 'Laporan',
                'subItems' => [
                    [
                        'name' => 'Laporan Rekapitulasi',
                        'path' => route('laporan.index', absolute: false),
                    ],
                ],
            ];
        }

        // ================= MASTER DATA =================
        if ($canMasterData) {
            $masterSub = [
                [
                    'name' => 'Kode Temuan',
                    'path' => route('kode-temuan.index', absolute: false),
                ],
                [
                    'name' => 'Kode Rekomendasi',
                    'path' => route('kode-rekomendasi.index', absolute: false),
                ],
                [
                    'name' => 'Objek Pemeriksaan',
                    'path' => route('unit-diperiksa.index', absolute: false),
                ],
            ];

            $items[] = [
                'icon' => 'database',
                'name' => 'Master Data',
                'subItems' => $masterSub,
            ];
        }

        // ================= SISTEM =================
        if ($canUserMgmt) {
            $sistemSub = [
                [
                    'name' => 'Pegawai Inspektorat',
                    'path' => route('pegawai.inspektorat.index', absolute: false),
                ],
                [
                    'name' => 'User Instansi/OPD',
                    'path' => route('pegawai.opd.index', absolute: false),
                ],
                [
                    'name' => 'Role & Permission',
                    'path' => route('permissions.index', absolute: false),
                ],
            ];

            $items[] = [
                'icon' => 'user-profile',
                'name' => 'Sistem & Pengguna',
                'subItems' => $sistemSub,
            ];
        }

        return $items;
    }

    public static function getMenuGroups(): array
    {
        $user = auth()->user();

        if ($user && $user->hasRole('opd')) {
            return [
                [
                    'title' => 'DASHBOARD',
                    'items' => self::getMainNavItems(),
                ],
                [
                    'title' => 'TINDAK LANJUT',
                    'items' => self::getOpdItems(),
                ],
            ];
        }

        return [
            [
                'title' => 'MAIN MENU',
                'items' => self::getMainNavItems(),
            ],
            [
                'title' => 'E-AUDIT MANAGEMENT',
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
                        'name' => 'Monitoring TL Saya',
                        'path' => route('opd.tindak-lanjut.index', absolute: false),
                    ],
                ],
            ],
            [
                'icon' => 'user-profile',
                'name' => 'Pengaturan Akun',
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