<?php

namespace App\Services;

use App\Models\Setting;

class RankingService
{
    private float $kv; // Votes coefficient
    private float $kb; // Boost coefficient
    private float $ko; // Online coefficient
    private float $ku; // Uptime coefficient

    public function __construct(float $kv = 1.0, float $kb = 0.5, float $ko = 0.3, float $ku = 0.2)
    {
        $this->kv = $kv;
        $this->kb = $kb;
        $this->ko = $ko;
        $this->ku = $ku;
    }

    public static function createFromSettings(): self
    {
        return new self(
            (float) Setting::get('rank_kv', '1.0'),
            (float) Setting::get('rank_kb', '0.5'),
            (float) Setting::get('rank_ko', '0.3'),
            (float) Setting::get('rank_ku', '0.2')
        );
    }

    public function calculateScore(int $votes, int $boostPoints, float $normalizedOnline, float $uptimePercent): float
    {
        // Formula: (Votes * Kv) + (Boost * Kb) + (Online% * Ko) + (Uptime% * Ku)
        return ($votes * $this->kv) + 
               ($boostPoints * $this->kb) + 
               ($normalizedOnline * $this->ko) + 
               ($uptimePercent * $this->ku);
    }
}
