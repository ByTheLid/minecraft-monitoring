<?php

// Recalculate server rankings
// Cron: every 15 minutes

require_once __DIR__ . '/../vendor/autoload.php';

use App\Core\Env;
use App\Core\Database;
use App\Services\RankingService;

Env::load(__DIR__ . '/../.env');

try {
    $db = Database::getInstance();
    $rankingService = RankingService::createFromSettings();

    // Get all active approved servers
    $servers = $db->query("SELECT id FROM servers WHERE is_active = 1 AND is_approved = 1")->fetchAll();

    $count = 0;
    foreach ($servers as $server) {
        $sid = $server['id'];

        // Votes last 30 days
        $stmt = $db->prepare("SELECT COUNT(*) FROM votes WHERE server_id = ? AND voted_at > DATE_SUB(NOW(), INTERVAL 30 DAY)");
        $stmt->execute([$sid]);
        $votes = (int) $stmt->fetchColumn();

        // Active boost points and perks
        $stmt = $db->prepare("
            SELECT 
                COALESCE(SUM(bp.points), 0) as total_points,
                MAX(pkg.stars) as max_stars,
                MAX(pkg.has_border) as max_border,
                MAX(pkg.has_bg_color) as max_bg,
                MAX(pkg.color) as highlight_color
            FROM boost_purchases bp
            JOIN boost_packages pkg ON bp.package_id = pkg.id
            WHERE bp.server_id = ? AND bp.expires_at > NOW()
        ");
        $stmt->execute([$sid]);
        $boostData = $stmt->fetch();
        
        $boostPoints = (int) ($boostData['total_points'] ?? 0);
        $stars = (int) ($boostData['max_stars'] ?? 0);
        $hasBorder = (int) ($boostData['max_border'] ?? 0);
        $hasBgColor = (int) ($boostData['max_bg'] ?? 0);
        $highlightColor = $boostData['highlight_color'] ?? null;

        // Custom boosts might not have a package_id, so we still need their points
        $stmtCustom = $db->prepare("SELECT COALESCE(SUM(points), 0) FROM boost_purchases WHERE server_id = ? AND package_id IS NULL AND expires_at > NOW()");
        $stmtCustom->execute([$sid]);
        $customBoostPoints = (int) $stmtCustom->fetchColumn();
        
        $boostPoints += $customBoostPoints;

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
        $score = $rankingService->calculateScore($votes, $boostPoints, $normalizedOnline, $uptime);

        // Upsert ranking
        $stmt = $db->prepare(
            "INSERT INTO server_rankings (server_id, rank_score, vote_count, boost_points, avg_online, uptime_percent, calculated_at, stars, has_border, has_bg_color, highlight_color)
             VALUES (?, ?, ?, ?, ?, ?, NOW(), ?, ?, ?, ?)
             ON DUPLICATE KEY UPDATE
                rank_score = VALUES(rank_score),
                vote_count = VALUES(vote_count),
                boost_points = VALUES(boost_points),
                avg_online = VALUES(avg_online),
                uptime_percent = VALUES(uptime_percent),
                stars = VALUES(stars),
                has_border = VALUES(has_border),
                has_bg_color = VALUES(has_bg_color),
                highlight_color = VALUES(highlight_color),
                calculated_at = NOW()"
        );
        $stmt->execute([
            $sid, 
            round($score, 4), 
            $votes, 
            $boostPoints, 
            round($normalizedOnline, 2), 
            round($uptime, 2),
            $stars,
            $hasBorder,
            $hasBgColor,
            $highlightColor
        ]);

        $count++;
    }

    echo "Rankings updated for {$count} servers.\n";
    logger()->info("Rankings recalculated for {$count} servers");

} catch (\Throwable $e) {
    echo "Error: " . $e->getMessage() . "\n";
    logger()->error('Rankings cron failed: ' . $e->getMessage());
    exit(1);
}
