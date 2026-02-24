<?php

namespace App\Services;

use App\Core\Database;
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
        return ($votes * $this->kv) + 
               ($boostPoints * $this->kb) + 
               ($normalizedOnline * $this->ko) + 
               ($uptimePercent * $this->ku);
    }

    /**
     * Recalculate rank_score for ALL servers.
     * Also updates avg_online and uptime_percent from recent stats.
     */
    public function recalculateAll(): int
    {
        $db = Database::getInstance();
        $count = 0;

        // Step 1: Update avg_online and uptime_percent from last 24h stats
        $db->exec("
            UPDATE server_rankings sr
            SET 
                avg_online = COALESCE((
                    SELECT AVG(ss.players_online)
                    FROM server_stats ss
                    WHERE ss.server_id = sr.server_id
                      AND ss.checked_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
                ), 0),
                uptime_percent = COALESCE((
                    SELECT ROUND(AVG(ss.is_online) * 100, 2)
                    FROM server_stats ss
                    WHERE ss.server_id = sr.server_id
                      AND ss.checked_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
                ), 0)
        ");

        // Step 2: Recalculate rank_score for each server
        $rows = $db->query("
            SELECT server_id, vote_count, boost_points, avg_online, uptime_percent
            FROM server_rankings
        ")->fetchAll(\PDO::FETCH_ASSOC);

        if (empty($rows)) {
            return 0;
        }

        // Find max avg_online for normalization
        $maxOnline = max(1, max(array_column($rows, 'avg_online')));

        $stmt = $db->prepare("UPDATE server_rankings SET rank_score = ?, calculated_at = NOW() WHERE server_id = ?");

        foreach ($rows as $row) {
            $normalizedOnline = ($row['avg_online'] / $maxOnline) * 100;
            $score = $this->calculateScore(
                (int) $row['vote_count'],
                (int) $row['boost_points'],
                $normalizedOnline,
                (float) $row['uptime_percent']
            );
            $stmt->execute([round($score, 4), $row['server_id']]);
            $count++;
        }

        return $count;
    }

    /**
     * Recalculate rank_score for a SINGLE server (after vote, boost, etc.)
     */
    public function recalculateServer(int $serverId): void
    {
        $db = Database::getInstance();

        $row = $db->prepare("SELECT vote_count, boost_points, avg_online, uptime_percent FROM server_rankings WHERE server_id = ?");
        $row->execute([$serverId]);
        $data = $row->fetch(\PDO::FETCH_ASSOC);

        if (!$data) {
            return;
        }

        // Get max avg_online for normalization
        $maxOnline = max(1, (float) $db->query("SELECT MAX(avg_online) FROM server_rankings")->fetchColumn());

        $normalizedOnline = ($data['avg_online'] / $maxOnline) * 100;
        $score = $this->calculateScore(
            (int) $data['vote_count'],
            (int) $data['boost_points'],
            $normalizedOnline,
            (float) $data['uptime_percent']
        );

        $db->prepare("UPDATE server_rankings SET rank_score = ?, calculated_at = NOW() WHERE server_id = ?")
           ->execute([round($score, 4), $serverId]);
    }
}

