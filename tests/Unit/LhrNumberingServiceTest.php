<?php

namespace Tests\Unit;

use App\Models\NomorSequence;
use App\Services\Lhr\LhrNumberingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LhrNumberingServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_allocate_sequential_lhr_numbers(): void
    {
        $service = new LhrNumberingService;

        $res1 = $service->allocate(LhrNumberingService::JENIS_LHR, 2026);
        $this->assertEquals(1, $res1['nomor_urut']);
        $this->assertEquals('700/001/REVIU/INSP/2026', $res1['nomor']);

        $res2 = $service->allocate(LhrNumberingService::JENIS_LHR, 2026);
        $this->assertEquals(2, $res2['nomor_urut']);
        $this->assertEquals('700/002/REVIU/INSP/2026', $res2['nomor']);

        // Next year starts with 1
        $res2027 = $service->allocate(LhrNumberingService::JENIS_LHR, 2027);
        $this->assertEquals(1, $res2027['nomor_urut']);
        $this->assertEquals('700/001/REVIU/INSP/2027', $res2027['nomor']);
    }

    public function test_can_allocate_lhr_number_with_suffix(): void
    {
        $service = new LhrNumberingService;

        // Seed sequence up to 92
        NomorSequence::create([
            'jenis' => LhrNumberingService::JENIS_LHR,
            'tahun' => 2025,
            'nomor_terakhir' => 92,
        ]);

        $res = $service->allocate(LhrNumberingService::JENIS_LHR, 2025, 'a');
        $this->assertEquals(93, $res['nomor_urut']);
        $this->assertEquals('700/093.a/REVIU/INSP/2025', $res['nomor']);
    }

    public function test_can_allocate_spt_reviu_format(): void
    {
        $service = new LhrNumberingService;
        $res = $service->allocate('spt_reviu', 2026);

        $this->assertEquals('000.1.2.3/001/REVIU/INSP/2026', $res['nomor']);
    }
}
