<?php

// Recalculate server rankings
// Cron: every 15 minutes

require_once __DIR__ . '/../vendor/autoload.php';

use App\Core\Env;
use App\Core\Database;
use App\Models\Setting;

Env::load(__DIR__ . '/../.env');

try {
    $db = Database::getInstance();

    // Get ranking coefficients
    $kv = (float) Setting::get('rank_kv', '1.0');
    $kb = (float) Setting::get('rank_kb', '0.5');
    $ko = (float) Setting::get('rank_ko', '0.3');
    $ku = (float) Setting::get('rank_ku', '0.2');

    // Get all active approved servers
    $servers = $db->query("SELECT id FROM servers WHERE is_active = 1 AND is_approved = 1")->fetchAll();

    $count = 0;
    foreach ($servers as $server) {
        $sid = $server['id'];

        // Votes last 30 days
        $stmt = $db->prepare("SELECT COUNT(*) FROM votes WHERE server_id = ? AND voted_at > DATE_SUB(NOW(), INTERVAL 30 DAY)");
        $stmt->execute([$sid]);
        $votes = (int) $stmt->fetchColumn();

        // Active boost points
        $stmt = $db->prepare("SELECT COALESCE(SUM(points), 0) FROM boost_purchases WHERE server_id = ? AND expires_at > NOW()");
        $stmt->execute([$sid]);
        $boostPoints = (int) $stmt->fetchColumn();

        // Average online last 7 days (normalized 0-100)
        $stmt = $db->prepare(
            "SELECT AVG(players_online) as avg_online, MAX(players_max) as max_slots
             FROM server_stats
             WHERE server_id = ? AND checked_at > DATE_SUB(NOW(), INTERVAL 7 DAY)"
        );
        $stmt->execute([$sid]);
        $row = $stmt->fetch();
        $avgOnline = (float) ($row['avg_online'] ?? 0);
        $maxSlots = (int) ($row['max_slots'] ?? 1);
        $normalizedOnline = $maxSlots > 0 ? min(100, ($avgOnline / $maxSlots) * 100) : 0;

        // Uptime last 7 days (%)
        $stmt = $db->prepare(
            "SELECT COUNT(*) as total, SUM(is_online) as online_count
             FROM server_stats
             WHERE server_id = ? AND checked_at > DATE_SUB(NOW(), INTERVAL 7 DAY)"
        );
        $stmt->execute([$sid]);
        $row = $stmt->fetch();
        $totalChecks = (int) ($row['total'] ?? 0);
        $onlineChecks = (int) ($row['online_count'] ?? 0);
        $uptime = $totalChecks > 0 ? ($onlineChecks / $totalChecks) * 100 : 0;

        // Calculate rank score
        $score = ($votes * $kv) + ($boostPoints * $kb) + ($normalizedOnline * $ko) + ($uptime * $ku);

        // Upsert ranking
        $stmt = $db->prepare(
            "INSERT INTO server_rankings (server_id, rank_score, vote_count, boost_points, avg_online, uptime_percent, calculated_at)
             VALUES (?, ?, ?, ?, ?, ?, NOW())
             ON DUPLICATE KEY UPDATE
                rank_score = VALUES(rank_score),
                vote_count = VALUES(vote_count),
                boost_points = VALUES(boost_points),
                avg_online = VALUES(avg_online),
                uptime_percent = VALUES(uptime_percent),
                calculated_at = NOW()"
        );
        $stmt->execute([$sid, round($score, 4), $votes, $boostPoints, round($normalizedOnline, 2), round($uptime, 2)]);

        $count++;
    }

    echo "Rankings updated for {$count} servers.\n";
    logger()->info("Rankings recalculated for {$count} servers");

} catch (\Throwable $e) {
    echo "Error: " . $e->getMessage() . "\n";
    logger()->error('Rankings cron failed: ' . $e->getMessage());
    exit(1);
}
