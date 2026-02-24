<?php

require_once __DIR__ . '/../../vendor/autoload.php';

use App\Core\Env;
use App\Core\Database;

Env::load(__DIR__ . '/../../.env');

$db = Database::getInstance();

$db->exec("TRUNCATE TABLE achievements");

$achievements = [
    // Voting
    ['first_vote', 'First Blood', 'Voted for a server for the first time.', 'fa-solid fa-hand-pointer', '#10b981'],
    ['voter_10', 'Voter (10)', 'Voted for servers 10 times.', 'fa-solid fa-check-to-slot', '#34d399'],
    ['voter_50', 'Collector (50)', 'Given 50 votes to various servers.', 'fa-solid fa-box-open', '#059669'],
    ['voter_100', 'Voting Veteran (100)', 'Reached the milestone of 100 votes!', 'fa-solid fa-award', '#065f46'],
    ['voter_500', 'Voice of the People (500)', 'An incredible 500 votes!', 'fa-solid fa-bullhorn', '#f59e0b'],
    
    // Server Owners
    ['server_owner', 'Server Owner', 'Added your first server to the monitoring list.', 'fa-solid fa-server', '#3b82f6'],
    ['server_popular', 'Popular Project', 'Your server reached 1000 votes.', 'fa-solid fa-fire', '#ef4444'],
    ['server_elite', 'Elite Server', 'Your server reached the Top 3.', 'fa-solid fa-crown', '#eab308'],
    
    // Reviews
    ['first_review', 'Novice Critic', 'Left your first review.', 'fa-solid fa-comment-dots', '#8b5cf6'],
    ['reviewer_10', 'Reviewer', 'Wrote 10 honest reviews.', 'fa-solid fa-pen-nib', '#7c3aed'],
    ['reviewer_50', 'Opinion Leader', 'Authored 50 detailed reviews.', 'fa-solid fa-bullseye', '#5b21b6'],
    
    // Purchases & Boosts
    ['supporter', 'Supporter', 'Purchased a Boost package (Supported the project).', 'fa-solid fa-heart', '#ef4444'],
    ['whale', 'Whale', 'Purchased a Boost package 10 times.', 'fa-solid fa-gem', '#06b6d4'],
    
    // Daily Logins
    ['daily_3', 'Consistency (3)', 'Logged into the site 3 days in a row.', 'fa-solid fa-calendar-day', '#f97316'],
    ['daily_7', 'A Week With Us', 'Logged into the site 7 days in a row.', 'fa-solid fa-calendar-week', '#ea580c'],
    ['daily_30', 'A Whole Month', 'Visited the monitoring every day for a month!', 'fa-solid fa-calendar-check', '#9a3412'],
    
    // Reputation / Rank Level-Ups
    ['rank_silver', 'Silver Rank', 'Reached Silver Rank.', 'fa-solid fa-medal', '#94a3b8'],
    ['rank_gold', 'Gold Rank', 'Reached Gold Rank.', 'fa-solid fa-medal', '#eab308'],
    ['rank_diamond', 'Diamond Rank', 'Reached Diamond Rank.', 'fa-solid fa-gem', '#38bdf8'],
    ['rank_legendary', 'Legendary', 'Reached Legendary Rank.', 'fa-solid fa-trophy', '#b91c1c'],

    // Special
    ['bug_hunter', 'Bug Hunter', 'Reported a critical bug.', 'fa-solid fa-bug', '#84cc16'],
    ['early_bird', 'Early Bird', 'Registered during the first week of the monitoring.', 'fa-solid fa-egg', '#d946ef']
];

$stmt = $db->prepare("
    INSERT IGNORE INTO achievements (achievement_key, name, description, icon, color) 
    VALUES (?, ?, ?, ?, ?)
");

echo "Seeding Achievements...\n";
foreach ($achievements as $ach) {
    if ($stmt->execute($ach)) {
        echo "Created: {$ach[1]}\n";
    }
}
echo "Done.\n";
