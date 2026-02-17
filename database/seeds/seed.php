<?php

/**
 * Seed script — creates test data
 * Usage: php database/seeds/seed.php
 */

require_once __DIR__ . '/../../vendor/autoload.php';

use App\Core\Env;
use App\Core\Database;
use App\Models\User;
use App\Models\Server;
use App\Models\Setting;

Env::load(__DIR__ . '/../../.env');

try {
    $db = Database::getInstance();

    // Create admin user
    echo "Creating admin user...\n";
    $adminId = User::register('admin', 'admin@monitoring.local', 'admin123');
    $db->prepare("UPDATE users SET role = 'admin' WHERE id = ?")->execute([$adminId]);

    // Create test user
    echo "Creating test user...\n";
    $userId = User::register('player1', 'player1@example.com', 'password123');

    // Create test servers
    echo "Creating test servers...\n";
    $servers = [
        ['name' => 'Hypixel', 'ip' => 'mc.hypixel.net', 'port' => 25565, 'description' => 'The largest Minecraft server network. Featuring SkyWars, BedWars, SkyBlock, and more!', 'tags' => '["minigames","pvp","skyblock","bedwars"]'],
        ['name' => 'CubeCraft', 'ip' => 'play.cubecraft.net', 'port' => 25565, 'description' => 'Unique Minecraft minigames including EggWars, Tower Defence, and SkyWars.', 'tags' => '["minigames","eggwars","skywars"]'],
        ['name' => 'Mineplex', 'ip' => 'us.mineplex.com', 'port' => 25565, 'description' => 'One of the largest Minecraft servers with a variety of minigames.', 'tags' => '["minigames","survival","creative"]'],
        ['name' => 'MC Central', 'ip' => 'mccentral.org', 'port' => 25565, 'description' => 'Minecraft server with Prison, Factions, Survival and more.', 'tags' => '["prison","factions","survival"]'],
        ['name' => '2b2t', 'ip' => '2b2t.org', 'port' => 25565, 'description' => 'The oldest anarchy server in Minecraft.', 'tags' => '["anarchy","survival","vanilla"]'],
    ];

    foreach ($servers as $s) {
        $id = Server::create([
            'user_id' => $adminId,
            'name' => $s['name'],
            'ip' => $s['ip'],
            'port' => $s['port'],
            'description' => $s['description'],
            'tags' => $s['tags'],
            'is_approved' => 1,
        ]);

        // Create initial ranking
        $db->prepare(
            "INSERT INTO server_rankings (server_id, rank_score, vote_count) VALUES (?, ?, 0)"
        )->execute([$id, rand(10, 100)]);

        // Create status cache placeholder
        $db->prepare(
            "INSERT INTO server_status_cache (server_id, is_online, players_online, players_max, last_checked_at) VALUES (?, 0, 0, 0, NOW())"
        )->execute([$id]);

        echo "  Created: {$s['name']}\n";
    }

    // Create boost packages
    echo "Creating boost packages...\n";
    $packages = [
        ['name' => 'Bronze Boost', 'points' => 50, 'price' => 2.99, 'duration_days' => 7],
        ['name' => 'Silver Boost', 'points' => 150, 'price' => 6.99, 'duration_days' => 14],
        ['name' => 'Gold Boost', 'points' => 400, 'price' => 14.99, 'duration_days' => 30],
        ['name' => 'Diamond Boost', 'points' => 1000, 'price' => 29.99, 'duration_days' => 30],
    ];

    foreach ($packages as $p) {
        $db->prepare(
            "INSERT INTO boost_packages (name, points, price, duration_days) VALUES (?, ?, ?, ?)"
        )->execute([$p['name'], $p['points'], $p['price'], $p['duration_days']]);
    }

    // Create a sample post
    echo "Creating sample post...\n";
    $db->prepare(
        "INSERT INTO posts (author_id, title, slug, content, category, is_published, published_at) VALUES (?, ?, ?, ?, ?, 1, NOW())"
    )->execute([
        $adminId,
        'Welcome to MC Monitor!',
        'welcome-to-mc-monitor',
        "Welcome to the MC Monitor platform!\n\nHere you can discover and track the best Minecraft servers, vote for your favorites, and monitor real-time statistics.\n\nFeatures:\n- Real-time server monitoring\n- Player count tracking\n- Voting system\n- Server rankings\n- Boost system\n\nAdd your server today and start growing your community!",
        'news',
    ]);

    echo "\nSeed completed successfully!\n";
    echo "Admin login: admin / admin123\n";
    echo "User login: player1 / password123\n";

} catch (\Throwable $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}
