<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Services\RankingService;

class RankingServiceTest extends TestCase
{
    public function test_calculate_score()
    {
        // Kv=1, Kb=0.5, Ko=0.3, Ku=0.2
        $service = new RankingService(1.0, 0.5, 0.3, 0.2);

        $votes = 100;
        $boost = 200;
        $online = 50.0; // 50%
        $uptime = 90.0; // 90%

        // 100*1 + 200*0.5 + 50*0.3 + 90*0.2
        // 100 + 100 + 15 + 18 = 233
        $expected = 233.0;

        $this->assertEquals($expected, $service->calculateScore($votes, $boost, $online, $uptime));
    }

    public function test_custom_coefficients()
    {
        // Kv=2, Kb=1, Ko=0, Ku=0
        $service = new RankingService(2.0, 1.0, 0.0, 0.0);

        $this->assertEquals(400.0, $service->calculateScore(100, 200, 50, 90));
    }
}
